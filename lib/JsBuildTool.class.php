<?php

class JsBuildTool {

  function classes($classes, $buildFolder, $buildFileName) {
    Sflm::$webPath = $buildFolder;
    $this->report((new SflmJsClassBuilder)->storeClass(
      $classes,
      $buildFileName
    )->report(), $buildFolder, $buildFileName);
  }

  function html($file, $buildFolder, $buildFileName) {
    Sflm::$webPath = $buildFolder;
    $this->report((new SflmJsBuilder)->processHtmlAndStore(
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