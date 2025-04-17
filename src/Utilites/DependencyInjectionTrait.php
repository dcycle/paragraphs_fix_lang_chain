<?php

namespace Drupal\paragraphs_fix_lang_chain\Utilites;

/**
 * Can be used to inject dependencies into other classes.
 */
trait DependencyInjectionTrait {

  public function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  public function entityFieldManager() {
    return \Drupal::service('entity_field.manager');
  }

}
