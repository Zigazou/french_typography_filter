<?php

namespace Drupal\french_typography_filter\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use Zigazou\FrenchTypography\Correcteur;

/**
 * Provides a Twig filter for French typography.
 */
final class FrenchTypographyTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter(
        'french_typography',
        [$this, 'frenchTypography'],
        [
          'pre_escape' => 'html',
          'is_safe' => ['html'],
        ],
      ),
    ];
  }

  /**
   * Applies French typography rules to the given string.
   *
   * @param string $value
   *   The input string to be processed.
   *
   * @return string
   *   The processed string with French typography applied.
   */
  public function frenchTypography(string $value): string {
    return Correcteur::corriger($value, TRUE);
  }

}
