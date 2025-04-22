<?php

namespace Drupal\paragraphs_fix_lang_chain\EntityAnalyzer;

use Drupal\paragraphs_fix_lang_chain\Utilites\DependencyInjectionTrait;

class EntityAnalyzerFactory {

  use DependencyInjectionTrait;

  public function analyzer(string $type, string $id, array $fields = []) {
    $entity = $this->entityTypeManager()->getStorage($type)->load($id);

    if (!$entity) {
      return new EntityAnalyzerDoesNotExist($type, $id);
    }

    return new EntityAnalyzerExists($entity, $fields);
  }

}
