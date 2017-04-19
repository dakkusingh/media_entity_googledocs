<?php

namespace Drupal\media_entity_googledocs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\media_entity\EmbedCodeValueTrait;
use Drupal\media_entity_googledocs\Plugin\MediaEntity\Type\GoogleDocs;

/**
 * Plugin implementation of the 'googledocs_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "googledocs_embed",
 *   label = @Translation("GoogleDocs embed"),
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
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => $this->getEmbedCode($item),
        '#allowed_tags' => ['blockquote', 'a', 'script'],
      ];
    }
    return $element;
  }

}
