<?

$form = new Form([
  [
    'name' => 'asd',
    'title' => 'asd'
  ]
], [
  'id' => 'asd'
]);
$form->action = 'json_requestLoaded';
print $form->html();

?>
<div id="result"></div>
<script>
  new Ngn.Form($('asd'), {
    ajaxSubmit: true,
    onComplete: function(r) {
      c(r);
      $('result').set('html', 'success');
    }
  });
</script>