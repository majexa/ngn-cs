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
      $class = trim($class);
      $this->frontend->addClass($class);
    }
    $this->frontend->store();
    return $this;
  }

  function processHtmlAndStore($html, $fileName, $locale = 'en-US') {
    SflmCache::clean();
    $this->frontend = new SflmFrontendJsBuild(new SflmJs($fileName), $fileName, [
      'locale' => $locale,
      'jsClassesClass' => 'SflmJsClassesTree',
      'mtDependenciesClass' => 'SflmMtDependenciesTree'
    ]);
    $this->frontend->addPath('i/js/ngn/Ngn.js');
    $this->frontend->processHtml($html, 'builder');
    $this->frontend->store();
    return $this;
  }


  function report() {
    if (!$this->frontend->classes->rootNodes) {
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
    foreach ($this->frontend->classes->rootNodes as $rootNode) {
      $yield = $rootNode->accept($visitor);
      foreach ($yield as $node) {
        /* @var SflmClassNode $node */
        $tree .= str_repeat('- ', $node->getDepth()).$node->getValue()."\n";
      }
    }
    // $yield = $this->frontend->classes->rootNode->accept($visitor);
    $report['dependencies']['ngn'] = $tree;
    return $report;
  }

}