<?php

namespace Drupal\paragraphs_fix_lang_chain\EntityAnalyzer;

interface EntityAnalyzerInterface {

  public function toArray() : array;

  public function getType() : string;

  public function getId() : string;

}
