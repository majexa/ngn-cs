<?php

use Tree\Visitor\PreOrderVisitor;
use Tree\Node\Node;

class JsBuilder {

  protected $frontend;

  protected function getSflmBuilderFrontend($frontendName) {
    return new SflmFrontendJsBuild(new SflmJsForBuilder($frontendName), $frontendName, [
      'locale'              => 'ru-RU',
      'jsClassesClass'      => 'SflmJsClassesTree',
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

  function processHtmlAndStore($html, $fileName, $jsonFieldsFolder = null) {
    SflmCache::clean();
    $frontend = $this->getSflmBuilderFrontend($fileName);
    $frontend->addPath('i/js/ngn/Ngn.js');

    if ($jsonFieldsFolder) {
      foreach (glob($jsonFieldsFolder.'/*.json') as $jsonFieldsFile) {
        $fields = json_decode(file_get_contents($jsonFieldsFile), JSON_FORCE_OBJECT);
        $form = new FormMock(new Fields($fields), [
          'commonElementOptions' => [
            'sflmFrontendJs' => $frontend
          ]
        ]);
        $formName = Misc::removeSuffix('.json', basename($jsonFieldsFile));
        $formHtml = $form->html();
        $formHtml = str_replace("'", "\\'", $formHtml);
        $formHtml = str_replace("\n", "\\\n", $formHtml);
        $code = "Ngn.toObj('Ngn.formTmpl.$formName', $formHtml');\n";
        $folder = Sflm::$webPath.'/ggg/formTmpl';
        Dir::make($folder);
        file_put_contents($folder.'/'.$formName.'.js', $code);
      }
    }

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