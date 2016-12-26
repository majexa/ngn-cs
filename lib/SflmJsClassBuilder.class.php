<?php

use Tree\Visitor\PreOrderVisitor;
use Tree\Node\Node;

class SflmJsClassBuilder {

  protected $frontend;

  /**
   * @param string $classes JS classes separated by quote
   * @param null|string $fileName Name of resulting file
   * @param string $locale Locale
   * @return $this
   */
  function storeClass($classes, $fileName = null, $locale = 'en-US') {
    SflmCache::clean();
    if (!$fileName) $fileName = 'f_'.str_replace('.', '_', $classes);
    $this->frontend = new SflmFrontendJsBuild(new SflmJs($fileName), $fileName, [
      'locale' => $locale,
      'jsClassesClass' => 'SflmJsClassesTree',
      'mtDependenciesClass' => 'SflmMtDependenciesTree'
    ]);
    $this->frontend->addPath('i/js/ngn/Ngn.js');
    foreach (explode(',', $classes) as $class) {
      //print "adding class '$class'\n";
      $class = trim($class);
      $this->frontend->addClass($class);
    }
    $this->frontend->store();
    return $this;
  }

  function report() {
    if (!$this->frontend->classes->rootNode) {
      return 'no changes. clear cache';
    }
    // MooTools dependencies tree
    $visitor = new PreOrderVisitor;
    $yield = $this->frontend->mtDependencies()->rootNode->accept($visitor);
    $tree = '';
    foreach ($yield as $node) {
      /* @var Node $node */
      $tree .= str_repeat('- ', $node->getDepth()).$node->getValue()."\n";
    }
    $report = [];
    $report['dependencies']['mt'] = $tree;
    // Ngn dependencies tree
    $tree = '';
    $visitor = new PreOrderVisitor;
    $yield = $this->frontend->classes->rootNode->accept($visitor);
    foreach ($yield as $node) {
      /* @var SflmClassNode $node */
      $tree .= str_repeat('- ', $node->getDepth()).$node->getValue()."\n";
    }
    $report['dependencies']['ngn'] = $tree;
    return $report;
  }

}