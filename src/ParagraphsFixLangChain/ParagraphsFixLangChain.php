<?php

namespace Drupal\paragraphs_fix_lang_chain\ParagraphsFixLangChain;

use Drupal\paragraphs_fix_lang_chain\Utilites\DependencyInjectionTrait;

/**
 * The service for this module.
 */
class ParagraphsFixLangChain {

  use DependencyInjectionTrait;

  public function info(string $type, string $id) {
    print_r($this->getInfo($type, $id));
  }

  public function getInfo(string $type, string $id) {
    $info = [];
    $errors = [];
    try {
      $info = $this->getInfoNoErrorManagement($type, $id);
    }
    catch (\Throwable $t) {
      $errors[] = [
        $line = $t->getLine(),
        $file = $t->getFile(),
        $message = $t->getMessage(),
      ];
    }
    return [
      'errors' => $errors,
      'info' => $info,
    ];
  }

  public function getInfoNoErrorManagement(string $type, string $id) {
    $entity = $this->entityTypeManager()->getStorage($type)->load($id);

    if (!$entity) {
      throw new \Exception('Invalid entity ' . $id . ' of type '  . $type . '.');
    }

    $info = $this->entityInfoAllLanguages($entity);

    return $info;
  }

  public function entityInfoAllLanguages($entity) {
    $ret = [];
    $languages = array_keys($entity->getTranslationLanguages());
    foreach ($languages as $lg) {
      $ret[$lg] = $this->entityInfo($entity->getTranslation($lg));
    }
    return $ret;
  }

  public function entityInfo($entity, $loadTranslations = TRUE) {
    $type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $langcode = $entity->language()->getId();
    $fields = $this->getFields($entity);

    $info = [];
    $info['id'] = $entity->id();
    $info['type'] = $type;
    $info['bundle'] = $bundle;
    $info['langcode'] = $langcode;
    $info['revision_id'] = $entity->getRevisionId();
    $info['paragraph_fields'] = $fields;

    return $info;
  }

  public function getFields($entity) : array {
    $langcode = $entity->language()->getId();
    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    $fields = $this->filterFields($this->getRawFields($entity_type, $bundle));

    foreach ($fields as $field_name => $field_info) {
      $fields[$field_name]['value'] = $this->getParagraphFieldValue($entity, $field_name, $langcode);
    }

    return $fields;
  }

  public function getParagraphFieldValue($entity, $field_name, $langcode) : array {
    $ret = [];
    $rawParaValues = $entity->get($field_name)->getValue();
    foreach ($rawParaValues as $val) {
      $ret[] = $this->formatParaVal($val, $langcode);
    }
    return $ret;
  }

  public function formatParaVal($val, $langcode) {
    $ret = [];
    if (isset($val['target_id'])) {
      $ret = [
        'info' => $this->getInfo('paragraph', $val['target_id']),
        'langcode' => $langcode,
      ];
    }
    return $ret;
  }


  public function filterFields(array $fields) : array {
    $filtered = [];
    foreach ($fields as $field_name => $field_definition) {
      $type = $field_definition->getType();
      $settings = $field_definition->getSettings();

      if ($this->fieldIsRelevant($type, $settings)) {
        $filtered[$field_name] = [
          'is_translatable' => $field_definition->isTranslatable(),
        ];
      }
    }
    return $filtered;
  }

  public function fieldIsRelevant(string $type, array $settings) : bool {
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

  public function getRawFields(string $entity_type, string $bundle) : array {
    $entityFieldManager = $this->entityFieldManager();
    $fields = $entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    return $fields;
  }

}
