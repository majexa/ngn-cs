<?
$form = new Form([
  [
    'name'  => 'Sample',
    'title' => 'sample'
  ]
]);
$form->action = '/js/json_formAjaxSubmit';
?>

<div class="apeform">
  <?= $form->html() ?>
</div>

<div id="result"></div>

<script>
  new Ngn.Form(document.getElement('.apeform form'), {
    ajaxSubmit: true,
    onComplete: function(result) {
      $('result').set('html', JSON.encode(result));
    }
  });
</script>