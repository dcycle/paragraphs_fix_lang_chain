<?php

namespace Drupal\paragraphs_fix_lang_chain\EntityAnalyzer;

class EntityAnalyzerDoesNotExist extends EntityAnalyzerBase {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $type;

  /**
   * The entity id.
   *
   * @var string
   */
  protected $id;

  /**
   * Constructor.
   *
   * @param string $type
   *   The entity type.
   * @param string $id
   *   The entity id.
   */
  public function __construct(string $type, string $id) {
    $this->type = $type;
    $this->id = $id;
  }

  public function getType() : string {
    return $this->type;
  }

  public function getId() : string {
    return $this->id;
  }

  public function getErrors() : array {
    return [
      'entity cannot be loaded',
    ];
  }

}
