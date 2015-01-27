<div id="form">
  <?
  $form = new Form([['name' => 'sample', 'title' => 'sample']]);
  $form->action = '/js/json_formAjaxSubmit';
  print $form->html();
  ?>
</div>
<div id="result"></div>
<script>
  new Ngn.Form(document.getElement('#form form'), {
    ajaxSubmit: true,
    onComplete: function(result) {
      $('result').set('html', JSON.encode(result));
    }
  });
</script>