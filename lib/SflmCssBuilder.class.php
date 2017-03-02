<?php

use Tree\Visitor\PreOrderVisitor;

class NoRootNodeError extends Exception {}

class SflmCssBuilder {

  protected $css;

  function __construct() {
    $this->css = new SflmCss;
  }

  function processHtml($html, $fileName, $exclude = null) {
    $frontend = new SflmFrontendJs(new SflmJs($fileName), $fileName, [
      'jsClassesClass' => 'SflmJsClassesTree',
    ]);
    $frontend->cleanPathsCache();
    $frontend->classes->frontendClasses->clean();
    $frontend->processHtml($html, 'builder');
    //
    $paths = [];
    $paths['common'] = $this->css->getPaths('common');
    //
    $visitor = new PreOrderVisitor;
    if (empty($frontend->classes->rootNodes)) {
      throw new NoRootNodeError();
    }
    foreach ($frontend->classes->rootNodes as $rootNode) {
      $yield = $rootNode->accept($visitor);
      foreach ($yield as $node) {
        $class = $node->getValue();
        $lib = JsCssDependencies::cssLib($class);
        if ($_paths = $this->css->getPaths($lib)) {
          $paths[$lib] = $_paths;
        } else {
          //output2("NONE: $lib");
        }
      }
    }
    if ($exclude) {
      foreach ($paths as $lib => &$_paths) {
        if (preg_match('/'.$exclude.'/', $lib)) unset($paths[$lib]);
        foreach ($_paths as $path) {
          if (preg_match('/'.$exclude.'/', $path)) {
            $_paths = Arr::drop($_paths, $path);
          }
        }
      }
    }
    return $paths;
  }

  function store($_paths, $fileName, $folder) {
    $c = '';
    foreach ($_paths as $lib => $paths) {
      $c .= $this->css->extractCode($paths);
    }
    Dir::make($folder.'/css');
    $f = $folder.'/css/'.$fileName.'.css';
    file_put_contents($f, $c);
    return $f;
  }

}