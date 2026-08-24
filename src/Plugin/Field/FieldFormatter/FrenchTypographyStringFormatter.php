<?php

namespace Drupal\french_typography_filter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Zigazou\FrenchTypography\Correcteur;

/**
 * Provides a formatter for French typography in string fields.
 *
 * @FieldFormatter(
 *   id = "french_typography_string",
 *   label = @Translation("French typography"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class FrenchTypographyStringFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $text = Correcteur::corriger($item->value, TRUE);

      // N'autoriser que les balises réellement produites/nécessaires.
      $text = Xss::filter($text, ['sup', 'em', 'strong', 'a', 'span', 'br']);
      $elements[$delta] = ['#markup' => $text];
    }

    return $elements;
  }

}
