<?php

namespace Drupal\media_entity_googledocs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity\EmbedCodeValueTrait;
use Drupal\media_entity_googledocs\Plugin\MediaEntity\Type\GoogleDocs;

/**
 * Plugin implementation of the 'googledocs_embed_generic' formatter.
 *
 * @FieldFormatter(
 *   id = "googledocs_embed_generic",
 *   label = @Translation("GoogleDocs embed generic"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class GoogleDocsEmbedFormatter extends FormatterBase {

  use EmbedCodeValueTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();
    $settings = $this->getSettings();
    foreach ($items as $delta => $item) {
      foreach (GoogleDocs::$validationRegexp as $pattern => $key) {
        if (preg_match($pattern, $this->getEmbedCode($item), $matches)) {
          break;
        }
      }

      if (!empty($matches['shortcode'])) {
        $element[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'iframe',
          '#attributes' => [
            'allowtransparency' => 'true',
            'allowfullscreen' => $settings['fullscreen'],
            'mozallowfullscreen' => $settings['fullscreen'],
            'webkitallowfullscreen' => $settings['fullscreen'],
            'frameborder' => 0,
            'position' => 'absolute',
            'scrolling' => $settings['scrolling'],
            'src' => $matches['shortcode'],
            'width' => $settings['width'],
            'height' => $settings['height'],
          ],
        ];
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => 480,
      'height' => 299,
      'scrolling' => TRUE,
      'fullscreen' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['width'] = array(
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#default_value' => $this->getSetting('width'),
      '#min' => 1,
      '#description' => $this->t('Width of spreadsheet.'),
    );

    $elements['height'] = array(
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#default_value' => $this->getSetting('height'),
      '#min' => 1,
      '#description' => $this->t('Height of spreadsheet.'),
    );

    $elements['scrolling'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Scrolling'),
      '#default_value' => $this->getSetting('scrolling'),
      '#description' => $this->t('Add scrolling bar if the document is larger/longer than the given width/height.'),
    );

    $elements['fullscreen'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Fullscreen'),
      '#default_value' => $this->getSetting('fullscreen'),
      '#description' => $this->t('Allow fullscreen (this is not applicable to all GoogleDocs types).'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Width: @width px', [
      '@width' => $this->getSetting('width'),
    ]);
    $summary[] = $this->t('Height: @height px', [
      '@height' => $this->getSetting('height'),
    ]);
    $summary[] = $this->t('Fullscreen allowed: @fullscreen', [
      '@fullscreen' => $this->getSetting('fullscreen') ? $this->t('Yes') : $this->t('No'),
    ]);
    $summary[] = $this->t('Scrolling allowed: @scrolling', [
      '@scrolling' => $this->getSetting('scrolling') ? $this->t('Yes') : $this->t('No'),
    ]);

    return $summary;
  }

}
