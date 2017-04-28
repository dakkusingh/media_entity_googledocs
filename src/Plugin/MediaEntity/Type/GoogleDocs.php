<?php

namespace Drupal\media_entity_googledocs\Plugin\MediaEntity\Type;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides media type plugin for GoogleDocs.
 *
 * @MediaType(
 *   id = "googledocs",
 *   label = @Translation("GoogleDocs"),
 *   description = @Translation("Provides business logic and metadata for GoogleDocs.")
 * )
 */
class GoogleDocs extends MediaTypeBase {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $config_factory->get('media_entity.settings'));
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * List of validation regular expressions.
   *
   * @var array
   */
  public static $validationRegexp = [
    '@(?<shortcode>((http|https):){0,1}//(www\.){0,1}docs\.google\.com/.*(?<type>(spreadsheets|presentation|document|forms)+)/d/(e/)?(?<id>[a-zA-Z0-9_-]+)/(pubhtml|pub\?embedded=true|embed|viewform\?embedded=true)[^\"]*+)@i' => 'shortcode',
    '@<iframe src="(?<shortcode>((http|https):){0,1}//(www\.){0,1}docs\.google\.com/.*(?<type>(spreadsheets|presentation|document|forms)+)/d/(e/)?(?<id>[a-zA-Z0-9_-]+)/(pubhtml|pub\?embedded=true|embed|viewform\?embedded=true)[^\"]*+)"(.*)>(.*)</iframe>@i' => 'shortcode',
  ];

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    $fields = [
      'shortcode' => $this->t('GoogleDocs shortcode'),
      'type' => $this->t('Document type (document, presentation, ...)'),
      'id' => $this->t('Document ID'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    $matches = $this->matchRegexp($media);

    if (!$matches['shortcode']) {
      return FALSE;
    }

    if (!empty($matches[$name])) {
      return $matches[$name];
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $bundle = $form_state->getFormObject()->getEntity();
    $allowed_field_types = ['string', 'string_long', 'link'];

    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field with source information'),
      '#description' => $this->t('Field on media entity that stores GoogleDocs embed code or URL. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachConstraints(MediaInterface $media) {
    parent::attachConstraints($media);

    if (isset($this->configuration['source_field'])) {
      $source_field_name = $this->configuration['source_field'];
      if ($media->hasField($source_field_name)) {
        foreach ($media->get($source_field_name) as &$embed_code) {
          /** @var \Drupal\Core\TypedData\DataDefinitionInterface $typed_data */
          $typed_data = $embed_code->getDataDefinition();
          $typed_data->addConstraint('GoogleDocsEmbedCode');
        }
      }
    }
  }

  /**
   * Runs preg_match on embed code/URL.
   *
   * @param MediaInterface $media
   *   Media object.
   *
   * @return array|bool
   *   Array of preg matches or FALSE if no match.
   *
   * @see preg_match()
   */
  protected function matchRegexp(MediaInterface $media) {
    $matches = [];
    if (isset($this->configuration['source_field'])) {
      $source_field = $this->configuration['source_field'];
      if ($media->hasField($source_field)) {
        $property_name = $media->{$source_field}->first()->mainPropertyName();
        foreach (static::$validationRegexp as $pattern => $key) {
          if (preg_match($pattern, $media->{$source_field}->{$property_name}, $matches)) {
            return $matches;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return $this->config->get('icon_base') . '/googledocs_generic.png';
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    $type = $this->getField($media, 'type');
    if (in_array($type, ['document', 'spreadsheets', 'presentation', 'forms'])) {
      return $this->config->get('icon_base') . '/googledocs_' . $type . '.png';
    }

    return $this->getDefaultThumbnail();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultName(MediaInterface $media) {
    $type = $this->getField($media, 'type');
    $id = $this->getField($media, 'id');
    if ($type && $id) {
      return $type . ' - ' . $id;
    }
    else {
      $code = $this->getField($media, 'shortcode');
      if (!empty($code)) {
        return $code;
      }
    }

    return parent::getDefaultName($media);
  }

}
