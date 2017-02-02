<?php

class CssBuildTool {

  function classes() {
    throw new Exception('not implemented');
  }

  function html($file, $buildFolder, $buildFileName) {
    Sflm::$webPath = $buildFolder;
    $builder = new SflmCssBuilder();
    $paths = $builder->processHtml(file_get_contents($file), $buildFileName);
    $storedFile = $builder->store($paths, $buildFileName, $buildFolder);
    print "\nCSS:\n";
    foreach ($paths as $lib => $_paths) {
      print $lib."\n";
      foreach ($_paths as $path) {
        print "- $path\n";
      }
    }
    print "====\nstored to: $storedFile\n";
  }

}