<?php

use Tree\Visitor\PreOrderVisitor;

class JsCssDependencies extends ArrayAccesseble {

  static function cssLib($jsClass) {
    return str_replace('.', '_', strtolower(Misc::removePrefix('Ngn.', $jsClass)));
  }

  /**
   * @var SflmCss
   */
  protected $css;

  protected $frontendName;

  function __construct($jsClass, $exclude = null) {
    $css = $this->css = new SflmCss;
    //
    $this->frontendName = self::cssLib($jsClass);
    $frontend = Sflm::frontend('js', $this->frontendName, [
      'jsClassesClass' => 'SflmJsClassesTree',
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
    if ($exclude) {
      foreach ($paths as $lib => $_paths) {
        if (preg_match('/'.$exclude.'/', $lib)) unset($paths[$lib]);
        foreach ($_paths as $path) {
          if (preg_match('/'.$exclude.'/', $path)) $_paths = Arr::drop($_paths, $path);
        }
      }
    }
    $this->r = $paths;
  }

  function store($folder) {
    $c = '';
    foreach ($this->r as $lib => $paths) {
      $c .= $this->css->extractCode($paths);
    }
    Dir::make($folder);
    file_put_contents($folder.'/'.$this->frontendName.'.css', $c);
  }

}