<?php

class JsBuildTool {

  function classes($classes, $buildFolder, $buildFileName) {
    throw new Exception('Not realized');
  }

  function html($file, $buildFolder, $buildFileName) {
    Sflm::$webPath = $buildFolder;
    $this->report((new JsBuilder)->processHtmlAndStore(
      file_get_contents($file),
      $buildFileName
    )->report(), $buildFolder, $buildFileName);
  }

  protected function report($report, $buildFolder, $buildFileName) {
    print "===== MooTools dependencies:\n";
    print $report['dependencies']['mt'];
    print "\n===== Ngn dependencies:\n";
    print $report['dependencies']['ngn'];
    print "====\nstored to: ".$buildFolder.'/js/cache/'.$buildFileName.".js\n";
  }

}