<?php

$sm = new DdStructuresManager;
$sm->deleteByName('a');
$sm->create([
  'title' => 'a',
  'name' => 'a'
]);
$fm = new DdFieldsManager('a');
$id = $fm->create([
  'name'  => 'b',
  'title' => 'b',
  'type'  => 'text'
]);
