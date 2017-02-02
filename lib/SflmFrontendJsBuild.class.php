<?php

class SflmFrontendJsBuild extends SflmFrontendJs {

  protected function defineOptions() {
    return array_merge(parent::defineOptions(), [
      'locale' => 'en-US'
    ]);
  }

  protected function preNgnCode() {
    return <<<JS
Locale.define('{$this->options['locale']}', 'Dummy', 'dummy', 'dummy');
Locale.use('{$this->options['locale']}');
JS;
  }

  protected function getStaticPaths() {
    return [];
  }

}