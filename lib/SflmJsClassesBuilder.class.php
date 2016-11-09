<?php

use Tree\Node\Node;

class SflmClassNode extends Node {

  public $source, $parent;

  function __construct($class, $source, $parent) {
    $this->source = $source;
    $this->parent = $parent;
    parent::__construct($class);
  }

}

class SflmJsClassesBuilder extends SflmJsClasses {

  /**
   * @var Node
   */
  public $rootNode;

  /**
   * @var Node
   */
  public $parentNode;

  function addClass($class, $source, $parent, $strict = true) {
    if ($this->frontendClasses->exists($class)) {
      return;
    }
    if ($parent === 'root') {
      $node = $this->rootNode = $this->parentNode = new SflmClassNode($class, $source, $parent);
    } else {
      $node = new SflmClassNode($class, $source, $parent);
      $this->parentNode->addChild($node);
    }
    $parentNode = $this->parentNode; // save to restore after recursion
    $this->parentNode = $node; // save current parent node
    parent::addClass($class, $source, $parent, true);
    $this->parentNode = $parentNode; // restore after recursion
  }

}