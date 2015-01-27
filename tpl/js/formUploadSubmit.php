<div id="form">
  <?
  $form = new Form([['name' => 'sample', 'title' => 'sample', 'type' => 'file']]);
  UploadTemp::extendFormOptions($form, '/js/json_formUpload');
  print $form->html();
  if ($_SESSION['files']) print_r($_SESSION['files']);
  ?>
</div>
<div id="result"></div>
<script>
  new Ngn.Form(document.getElement('#form form'), {
    onComplete: function(result) {
      $('result').set('html', JSON.encode(result));
    }
  });
</script>