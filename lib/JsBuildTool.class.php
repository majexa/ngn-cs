<?php

class JsBuildTool {

  function classes($classes, $buildFolder, $buildFileName) {
    throw new Exception('Not realized');
  }

  function html($file, $buildFolder, $buildFileName, $jsonFieldsFolder) {
    Sflm::$webPath = $buildFolder;
    $this->report((new JsBuilder)->processHtmlAndStore(
      file_get_contents($file),
      $buildFileName,
      $jsonFieldsFolder
    )->report(), $buildFolder, $buildFileName);
  }

  protected function report($report, $buildFolder, $buildFileName) {
    if (isset($report['message'])) {
      print $report['message']."\n";
      return;
    }
    if (empty($report['dependencies'])) {
      print "The are no MooTools dependencies. No NgnJS code?\n";
      exit(1);
    }
    print "===== MooTools dependencies:\n";
    print $report['dependencies']['mt'];
    print "\n===== Ngn dependencies:\n";
    print $report['dependencies']['ngn'];
    print "====\nstored to: ".$buildFolder.'/js/'.$buildFileName.".js\n";
  }

}