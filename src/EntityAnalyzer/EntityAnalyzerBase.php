<?php

namespace Drupal\paragraphs_fix_lang_chain\EntityAnalyzer;

abstract class EntityAnalyzerBase implements EntityAnalyzerInterface {

  public function toArray() : array {
    $ret = [
      'entity' => $this->getEntityTypeAndId(),
      'paragraph_fields' => [],
    ];
    $this->addKeyIfNotEmpty($ret, 'errors', $this->getErrors());
    $this->addKeyIfNotEmpty($ret, 'langs', $this->langs());
    $ret['paragraph_fields'] = $this->getParagraphFields();
    return $ret;
  }

  public function getEntityTypeAndId() : string {
    return $this->getType() . ':' . $this->getId();
  }

  public function getErrors() : array {
    return [];
  }

  public function getParagraphFields() : array {
    return [];
  }

  public function addKeyIfNotEmpty(
    &$array,
    $key,
    $value,
  ) {
    if (empty($value)) {
      return;
    }
    $array[$key] = $value;
  }

  public function langs() : array {
    return [];
  }

}
