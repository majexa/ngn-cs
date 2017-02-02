<?php

use Tree\Visitor\PreOrderVisitor;
use Tree\Node\Node;

class SflmJsBuilder {

  protected $frontend;

  protected function getSflmBuilderFrontend($frontendName) {
    return new SflmFrontendJsBuild(new SflmJs($frontendName), $frontendName, [
      'locale' => 'ru-RU',
      'jsClassesClass' => 'SflmJsClassesTree',
      'mtDependenciesClass' => 'SflmMtDependenciesTree'
    ]);
  }

  /**
   * @param string $classes JS classes separated by quote
   * @param null|string $fileName Name of resulting file
   * @return $this
   */
  function storeClass($classes, $fileName = null) {
    SflmCache::clean();
    if (!$fileName) $fileName = 'f_'.str_replace('.', '_', $classes);
    $frontend = $this->getSflmBuilderFrontend($fileName);
    $frontend->addPath('i/js/ngn/Ngn.js');
    foreach (explode(',', $classes) as $class) {
      $class = trim($class);
      $frontend->addClass($class);
    }
    $frontend->store();
    return $this;
  }

  function processHtmlAndStore($html, $fileName) {
    SflmCache::clean();
    $frontend = $this->getSflmBuilderFrontend($fileName);
    $frontend->addPath('i/js/ngn/Ngn.js');
    $frontend->processHtml($html, 'builder');
    $frontend->store();
    $this->frontend = $frontend;
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