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

  public function getInfo(string $type, string $id, $fields = []) : array {
    return $this->entityAnalyzerFactory()
      ->analyzer($type, $id, $fields)
      ->toArray();
  }

  public function simulate(string $id) {
    $this->fix(id: $id, simulate: TRUE);
  }

  public function fix(string $id, bool $simulate = FALSE) {
    print_r($this->doFix($id, $simulate));
  }

  public function doFix(string $id, bool $simulate) : array {
    try {
      $paragraph_info = $this->getInfo('paragraph', $id);
      $entity = $this->entityTypeManager()->getStorage('paragraph')->load($id);
      $parent = $entity->getParentEntity();
      return $this->fixChildParent($paragraph_info, $this->getInfo($parent->getEntityTypeId(), $parent->id()), $simulate);
    }
    catch (\Throwable $t) {
      return [
        'error' => $t->getMessage(),
        'file' => $t->getFile(),
        'line' => $t->getLine(),
        'id' => $id,
      ];

    }
  }

  public function fixChildParent(array $child, array $parent, bool $simulate) : array {
    $ret = [
      'child' => $child['entity'],
      'parent' => $parent['entity'],
      'parent_langchain' => $this->populateLangChain($parent),
      'child_langchain' => $this->populateLangChain($child),
    ];

    if ($ret['parent_langchain'] == $ret['child_langchain']) {
      $ret['conclusion'] = 'No changes needed';
    }
    else {
      $ret['conclusion'] = 'Changes needed';
      $ret['actions'] = $this->fixLangChain(
        $child,
        $parent,
        $simulate,
      );
    }

    return $ret;
  }

  public function fixLangChain(
    array $child,
    array $parent,
    bool $simulate
  ) : array {
    $ret = [];
    $parent_langchain = $this->populateLangChain($parent);
    $child_langchain = $this->populateLangChain($child);

    if (array_keys($parent_langchain) != array_keys($child_langchain)) {
      $ret[] = 'The parent and child langchains do have the same keys';
      $ret[] = 'parent is:';
      $ret[] = $parent_langchain;
      $ret[] = 'child is:';
      $ret[] = $child_langchain;
      $ret[] = 'We cannot currently fix this';
    }
    else {
      foreach ($parent_langchain as $plang => $psource) {
        if ($child_langchain[$plang] == $psource) {
          $ret[] = 'For ' . $plang . ', no change needed as the source is the same (' . $psource . ') for the child and parent';
        }
        else {
          $csource = $child_langchain[$plang];
          $ret[] = 'For ' . $plang . ', the parent source is ' . $psource . ' and the child (' . $child['entity'] . ') source is ' . $csource . '. This should be fixed.';
          $paragraph_id = explode(':', $child['entity'])[1];
          $ret = array_merge($ret, $this->set($paragraph_id, $plang, $psource, $simulate));
        }
      }
    }

    return $ret;
  }

  public function set(
    string $para_id,
    string $lang,
    string $source,
    bool $simulate = FALSE,
  ) : array {
    if ($simulate) {
      return ['SIMULATING changing the SOURCE lang of paragraph ' . $para_id . "'s " . $lang . ' to ' . $source];
    }
    $entity = $this->entityTypeManager()->getStorage('paragraph')->load($para_id);
    $translatedEntity = $entity->getTranslation($lang);
    $metadata = $this->contentTranslationManager()->getTranslationMetadata($translatedEntity);
    $metadata->setSource($source);
    $entity->save();
    return ['CHANGED the SOURCE lang of paragraph ' . $para_id . "'s " . $lang . ' to ' . $source];
  }

  public function populateLangChain($info) : array {
    $ret = [];
    foreach ($info['langs'] as $lang => $langinfo) {
      $ret[$lang] = $langinfo['source'];
    }
    return $ret;
  }

}
