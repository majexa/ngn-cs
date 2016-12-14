<?php

class SflmFrontendJsBuild extends SflmFrontendJs {

  protected function preNgnCode() {
    return <<<JS
Locale.define('en-US', 'Dummy', 'dummy', 'dummy');
Locale.use('en-US');
JS;
  }

  protected function getStaticPaths() {
    return [];
  }

}