<?php

use Tree\Visitor\PreOrderVisitor;

class JsCssDependencies extends ArrayAccesseble {

  static function cssLib($jsClass) {
    return str_replace('.', '_', strtolower(Misc::removePrefix('Ngn.', $jsClass)));
  }

  public $css;

  function __construct($jsClass) {
    $css = $this->css = new SflmCss;
    //
    $fronendName = 'bld_'.self::cssLib($jsClass);
    $frontend = Sflm::frontend('js', $fronendName, [
      'jsClassesClass' => 'SflmJsClassesBuilder',
    ]);
    $frontend->cleanPathsCache();
    $frontend->classes->frontendClasses->clean();
    if ($frontend->classes->frontendClasses->exists($jsClass)) {
      throw new Exception('clean cache does not work');
    }
    $frontend->addClass($jsClass);
    //
    $paths = [];
    $paths['common'] = $css->getPaths('common');
    //
    $visitor = new PreOrderVisitor;
    $yield = $frontend->classes->rootNode->accept($visitor);
    foreach ($yield as $node) {
      $class = $node->getValue();
      $lib = JsCssDependencies::cssLib($class);
      if ($_paths = $css->getPaths($lib)) {
        $paths[$lib] = $_paths;
      }
    }
    //
    $this->r = $paths;
  }

}