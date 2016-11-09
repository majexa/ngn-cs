<?php

class CtrlCommonTestForm extends CtrlCammon {

  protected function init() {
    $this->d['mainTpl'] = 'clearTpl';
  }

  function action_json_formAjaxSubmit() {
    $this->json['success'] = true;
  }

  function action_json_delayed() {
    usleep(300);
    $this->json['success'] = true;
  }

}