<?php

require __DIR__.'/vendor/autoload.php';

if (file_exists(__DIR__.'/config.php')) {
  require __DIR__.'/config.php';
}
setConstant('REPORT_COMPACT', false);

Ngn::addBasePath(__DIR__, 1);