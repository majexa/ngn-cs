<?php

use Tree\Node\Node;

class SflmMtDependenciesTree extends SflmMtDependencies {

  public $rootNode;

  function __construct() {
    $this->rootNode = new Node('root');
    parent::__construct();
  }

  function parseContentsR($name, $source = 'root', Node $parentNode = null) {
    $r = '';
    $package = $this->find($name);
    if (in_array($package['package'], $this->parsedPackages)) {
      return '';
    }
    $this->parsedPackages[] = $package['package'];
    // -- Node Logic
    $childNode = new Node($name);
    if (!$parentNode) {
      $this->rootNode->addChild($childNode);
    }
    else {
      $parentNode->addChild($childNode);
    }
    // --
    if (!empty($package['requires'])) {
      foreach ($package['requires'] as $_name) {
        $r .= $this->parseContentsR($_name, $package['package'], $childNode);
      }
    }
    Sflm::log('Mt: adding "'.$package['package'].'", src: '.$source);
    $r .= $this->getContents($package['file']);
    return $r;
  }

}
