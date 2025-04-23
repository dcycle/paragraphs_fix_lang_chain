<?php

namespace Drupal\paragraphs_fix_lang_chain\EntityAnalyzer;

use Drupal\paragraphs_fix_lang_chain\Utilites\DependencyInjectionTrait;

class EntityAnalyzerExists extends EntityAnalyzerBase {

  use DependencyInjectionTrait;

  protected $entity;

  protected $fields;

  /**
   * Constructor.
   *
   * @param $entity
   *   The entity which exists.
   */
  public function __construct($entity, $fields) {
    $this->entity = $entity;
    $this->fields = $fields;
  }

  public function getType() : string {
    return $this->entity->getType();
  }

  public function getId() : string {
    return $this->entity->id();
  }

  public function getParagraphFields() : array {
    $entity_type = $this->entity->getEntityTypeId();
    $bundle = $this->entity->bundle();
    return array_keys($this->filterFields($this->getRawFields($entity_type, $bundle)));
  }

  public function filterFields(array $fields) : array {
    $filtered = [];
    foreach ($fields as $field_name => $field_definition) {
      $type = $field_definition->getType();
      $settings = $field_definition->getSettings();

      if ($this->fieldIsRelevant($type, $settings, $field_name)) {
        $filtered[$field_name] = [
          'is_translatable' => $field_definition->isTranslatable(),
        ];
      }
    }
    return $filtered;
  }

  public function getRawFields(string $entity_type, string $bundle) : array {
    $entityFieldManager = $this->entityFieldManager();
    $fields = $entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    return $fields;
  }

  public function fieldIsRelevant(string $type, array $settings, string $field_name) : bool {
    if (in_array($field_name, $this->fields)) {
      return TRUE;
    }
    $ret = TRUE;
    if (!in_array($type, [
      'entity_reference_revisions',
    ])) {
      $ret = FALSE;
    }
    if (!isset($settings['handler']) || $settings['handler'] != 'default:paragraph') {
      $ret = FALSE;
    }
    return $ret;
  }

  public function langs() : array {
    $ret = [];
    $languages = array_keys($this->entity->getTranslationLanguages());
    foreach ($languages as $lg) {
      $translatedEntity = $this->entity->getTranslation($lg);
      $metadata = $this->contentTranslationManager()->getTranslationMetadata($translatedEntity);

      $source = $metadata->getSource();
      $ret[$lg] = [
        'source' => $source,
        'field_values' => $this->translatedEntityParagraphFieldValues(
          $translatedEntity,
          $lg,
        ),
      ];
    }
    return $ret;
  }

  public function getParagraphFieldValue($entity, $field_name) : array {
    $ret = [];
    $rawParaValues = $entity->get($field_name)->getValue();
    foreach ($rawParaValues as $val) {
      $ret[] = $this->formatParaVal($val);
    }
    return $ret;
  }

  public function formatParaVal($val) {
    $ret = [];
    if (isset($val['target_id'])) {
      $ret = [
        'paragraph_id' => $val['target_id'],
      ];
    }
    else {
      return $val;
    }
    return $ret;
  }

  public function translatedEntityParagraphFieldValues(
    $translatedEntity,
  ) {
    $ret = [];
    $fields = $this->getParagraphFields();
    foreach ($fields as $field) {
      $ret[$field] = $this->getParagraphFieldValue($translatedEntity, $field);
    }
    return $ret;
  }

}
