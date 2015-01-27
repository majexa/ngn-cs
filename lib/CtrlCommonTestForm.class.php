<?php

class CtrlCommonTestForm extends CtrlCammon {

  protected function init() {
    $this->d['mainTpl'] = 'clearTpl';
  }

  function action_requestLoaded() {
    $this->d['tpl'] = 'test/requestLoaded';
  }

  function action_json_requestLoaded() {
    usleep(300);
    $this->json['success'] = true;
  }

}