<?php

class SflmJsClassBuilder {

  protected $frontend;

  /**
   * @param string $classes JS classes separated by quote
   * @param null|string $fileName Name of resulting file
   * @return null|string
   */
  function storeClass($classes, $fileName = null) {
    SflmCache::clean();
    if (!$fileName) $fileName = 'f_'.str_replace('.', '_', $classes);
    $this->frontend = new SflmFrontendJsBuild(new SflmJs($fileName), $fileName, [
      'jsClassesClass' => 'SflmJsClassesTree',
      'mtDependenciesClass' => 'SflmMtDependenciesTree'
    ]);
    foreach (explode(',', $classes) as $class) {
      $class = trim($class);
      $this->frontend->addClass($class);
    }
    $this->frontend->store();
    return $fileName;
  }

}