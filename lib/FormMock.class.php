<?php

class FormMock extends Form
{
  protected function defineOptions() {
    return array_merge(parent::defineOptions(), [
      'disableSubmit' => true
    ]);
  }

  protected function initDefaultHiddenFields() {
  }

}
