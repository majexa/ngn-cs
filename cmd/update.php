<?php

$ngnEnvPath = NGN_ENV_PATH;
$ngnBasePaths = json_encode(Ngn::$basePaths);
$projects = json_encode(Arr::get(require NGN_ENV_PATH.'/config/projects.php', 'domain', 'name'));
file_put_contents(dirname(__DIR__).'/casper/config.js', <<<JS
module.exports = {
  projectsDir: '/home/user/ngn-env/projects',
  disableAfterCaptureCmd: 1,
  rumaxFolder: '$ngnEnvPath/rumax/web/captures',
  ngnPath: '$ngnEnvPath/ngn',
  ngnBasePaths: $ngnBasePaths,
  projects: $projects
};
JS
);

foreach (glob(NGN_ENV_PATH.'/projects/*', GLOB_ONLYDIR) as $f) {
  $name = basename($f);
  $ngnBasePaths = `run site $name "print json_encode(Ngn::\\\$basePaths);"`;
  Dir::make($f.'/site/casper');
  file_put_contents($f.'/site/casper/config.js', <<<JS
module.exports = {
  ngnBasePaths: $ngnBasePaths
};
JS
  );
}