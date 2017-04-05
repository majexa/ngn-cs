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

  protected function buildForm(SflmFrontendJsBuild $frontend, $jsonFieldsFile) {
    $fields = json_decode(file_get_contents($jsonFieldsFile), JSON_FORCE_OBJECT);
    $form = new FormMock(new Fields($fields), [
      'commonElementOptions' => [
        'sflmFrontendJs' => $frontend
      ]
    ]);
    $formName = Misc::removeSuffix('.json', basename($jsonFieldsFile));
    if (!REPORT_COMPACT) {
      print "\nRendering form '$formName' with such fields:\n";
      print St::enumSsss($fields, '✔ $name: $title', "\n");
    }
    $formHtml = $form->html();
    if (!REPORT_COMPACT) {
      print "\n";
      print "Form render result:\n";
      print $formHtml;
      print "\n";
    }
    $formHtml = str_replace("'", "\\'", $formHtml);
    $formHtml = str_replace("\n", "\\\n", $formHtml);
    $code = "Ngn.toObj('Ngn.formTmpl.$formName', '<div class=\"apeform\">$formHtml</div>');\n";
    $folder = Sflm::$absBasePaths['src'].'/formTmpl';
    Dir::make($folder);
    file_put_contents($folder.'/'.$formName.'.js', $code);
    output("Store form: ".$folder.'/'.$formName.'.js');
  }

  protected function buildForms(SflmFrontendJsBuild $frontend, $jsonFieldsFolder) {
    foreach (glob($jsonFieldsFolder.'/*.json') as $jsonFieldsFile) {
      $this->buildForm($frontend, $jsonFieldsFile);
    }
  }

  function processHtmlAndStore($html, $fileName, $jsonFieldsFolder = null) {
    SflmCache::clean();
    $frontend = $this->getSflmBuilderFrontend($fileName);
    $frontend->addPath('i/js/ngn/Ngn.js');
    if ($jsonFieldsFolder) $this->buildForms($frontend, $jsonFieldsFolder);
    $frontend->processHtml($html, 'builder');
    $frontend->store();
    $this->frontend = $frontend;
    return $this;
  }

  function report() {
    $report = [];
    if (!$this->frontend->classes->rootNodes) {
      $report['message'] = 'No changes in dependencies';
      return $report;
    }
    // MooTools dependencies tree
    $visitor = new PreOrderVisitor;
    $yield = $this->frontend->mtDependencies()->rootNode->accept($visitor);
    $tree = '';
    foreach ($yield as $node) {
      /* @var Node $node */
      if (REPORT_COMPACT) {
        $tree .= $node->getValue().", ";
      }
      else {
        $tree .= str_repeat('- ', $node->getDepth()).$node->getValue()."\n";
      }
    }
    if (REPORT_COMPACT) $tree = rtrim($tree, ', ');
    $report['dependencies']['mt'] = $tree.(REPORT_COMPACT ? "\n" : '');
    // Ngn dependencies tree
    $tree = '';
    $visitor = new PreOrderVisitor;
    foreach ($this->frontend->classes->rootNodes as $rootNode) {
      $yield = $rootNode->accept($visitor);
      foreach ($yield as $node) {
        /* @var SflmClassNode $node */
        if (REPORT_COMPACT) {
          $tree .= $node->getValue().", ";
        }
        else {
          $tree .= str_repeat('- ', $node->getDepth()).$node->getValue()."\n";
        }
      }
    }
    if (REPORT_COMPACT) $tree = rtrim($tree, ', ');
    // $yield = $this->frontend->classes->rootNode->accept($visitor);
    $report['dependencies']['ngn'] = $tree.(REPORT_COMPACT ? "\n" : '');
    return $report;
  }

}