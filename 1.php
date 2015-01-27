<?php

function runTest($name) {
  $rumaxFolder = "C:/www/refactor/ngn-env/doc/web/m/daily-ngn-cst/$name";
  print `php C:/www/refactor/ngn-env/projects/test/cmd.php preTest/$name`;
  print `casperjs C:/www/refactor/ngn-env/ngn/more/casper/test.js --projectDir=C:/www/refactor/ngn-env/projects/test --disableAfterCaptureCmd=1 --stepsFile=$name --rumaxFolder=$rumaxFolder --ngnPath=C:/www/refactor/ngn-env/ngn`;
}

foreach (glob(__DIR__.'/casper/test/*.json') as $file) {
  runTest(str_replace('.json', '', basename($file)));
}
