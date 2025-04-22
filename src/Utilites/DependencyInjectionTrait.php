<?php

namespace Drupal\paragraphs_fix_lang_chain\Utilites;

/**
 * Can be used to inject dependencies into other classes.
 */
trait DependencyInjectionTrait {

  public function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  public function contentTranslationManager() {
    return \Drupal::service('content_translation.manager');
  }

  public function entityFieldManager() {
    return \Drupal::service('entity_field.manager');
  }

  public function app() {
    return \Drupal::service('paragraphs_fix_lang_chain');
  }

  public function entityAnalyzerFactory() {
    return \Drupal::service('paragraphs_fix_lang_chain.entityAnalyzerFactory');
  }

}
