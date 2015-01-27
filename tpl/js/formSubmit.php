<div id="form">
  <?
  $form = new Form([['name' => 'sample', 'title' => 'sample']]);
  print $form->html();
  if ($form->isSubmitted()) print_r($form->getData());
  ?>
</div>
<script>
  new Ngn.Form(document.getElement('#form form'));
</script>