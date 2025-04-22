<?php

namespace Drupal\paragraphs_fix_lang_chain\ParagraphsFixLangChain;

use Drupal\paragraphs_fix_lang_chain\Utilites\DependencyInjectionTrait;

/**
 * The service for this module.
 */
class ParagraphsFixLangChain {

  use DependencyInjectionTrait;

  public function info(string $type, string $id, array $fields = []) {
    print_r($this->getInfo($type, $id, $fields));
  }

  public function getInfo(string $type, string $id, $fields) : array {
    return $this->entityAnalyzerFactory()
      ->analyzer($type, $id, $fields)
      ->toArray();
  }

}
