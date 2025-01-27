<?php

namespace Drupal\french_typography_filter\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Zigazou\FrenchTypography\Correcteur;

/**
 * Provides a filter to apply french typography.
 *
 * @Filter(
 *   id = "filter_french_typography",
 *   title = @Translation("Apply french typography"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE
 * )
 */
class FrenchTypographyFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult(Correcteur::corriger($text, TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Apply french typography to the text.');
  }

}
