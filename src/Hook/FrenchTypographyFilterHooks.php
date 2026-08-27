<?php

declare(strict_types=1);

namespace Drupal\french_typography_filter\Hook;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Render\Markup;
use Drupal\Component\Utility\Xss;

use Zigazou\FrenchTypography\Correcteur;

/**
 * Hook implementations for the French Typography Filter module.
 */
final class FrenchTypographyFilterHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_field_formatter_settings_summary_alter().
   */
  #[Hook('field_formatter_settings_summary_alter')]
  public function fieldFormatterSettingsSummaryAlter(
    array &$summary,
    array $context,
  ): void {
    $enabled = $context['formatter']->getThirdPartySetting(
      'french_typography_filter',
      'french_typo',
    );
    if ($enabled) {
      $summary[] = $this->t('French typography filter');
    }
  }

  /**
   * Implements hook_field_formatter_third_party_settings_form().
   */
  #[Hook('field_formatter_third_party_settings_form')]
  public function fieldFormatterThirdPartySettingsForm(
    FormatterInterface $plugin,
    FieldDefinitionInterface $field_definition,
    string $view_mode,
    array $form,
    FormStateInterface $form_state,
  ): array {
    return [
      'french_typo' => [
        '#type' => 'checkbox',
        '#title' => $this->t('French typography filter'),
        '#default_value' => $plugin->getThirdPartySetting(
          'french_typography_filter',
          'french_typo',
        ),
      ],
    ];
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(array &$variables): void {
    $entity = $variables['element']['#object'];
    $view_mode = $variables['element']['#view_mode'];
    $field_name = $variables['element']['#field_name'];

    $entity_display = EntityViewDisplay::collectRenderDisplay(
      $entity,
      $view_mode,
    );
    $field_display = $entity_display->getComponent($field_name);

    $third_party_settings = $field_display['third_party_settings'] ?? [];
    $module_settings = $third_party_settings['french_typography_filter'] ?? [];
    $enabled = $module_settings['french_typo'] ?? NULL;
    if ($enabled !== '1') {
      return;
    }

    $plain_field_types = ['string', 'string_long'];
    $is_html = !in_array(
      $variables['element']['#field_type'],
      $plain_field_types,
      TRUE,
    );

    foreach ($variables['items'] as $key => $item) {
      if (!isset($item['content']['#context']['value'])) {
        continue;
      }

      $variables['items'][$key]['content']['#context']['value'] =
        Correcteur::corriger(
          $item['content']['#context']['value'],
          $is_html,
        );
    }
  }

  /**
   * Implements hook_preprocess_page_title().
   */
  #[Hook('preprocess_page_title')]
  public function preprocessPageTitle(array &$variables): void {
    // Check if the title is empty.
    if (empty($variables['title'])) {
      return;
    }

    // Check if the current language is french.
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($langcode !== 'fr') {
      return;
    }

    // If the title is a render array, do not apply the filter.
    if (is_array($variables['title'])) {
      return;
    }

    $title = (string) $variables['title'];

    // Apply french typography.
    $title = Correcteur::corriger($title, TRUE);

    // Only allow necessary HTML.
    $title = Xss::filter($title, [
      'sup', 'sub', 'em', 'strong', 'span',
    ]);

    // Prevent Twig from re-escaping the title.
    $variables['title'] = Markup::create($title);
  }

}
