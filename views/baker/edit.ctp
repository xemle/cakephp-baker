<h1>Edit: <?php echo $fs->pathLink($path); ?><h1>

<?php $session->flash(); ?>

<?php echo $javascript->link('http://code.jquery.com/jquery-latest.js'); ?>
<?php echo $javascript->link('jquery.textarearesizer.compressed.js'); ?>

<?php echo '<style type="text/css">div.grippie {
  background:#EEEEEE url(grippie.png) no-repeat scroll center 2px;
  border-color:#DDDDDD;
  border-style:solid;
  border-width:0pt 1px 1px;
  cursor:s-resize;
  height:4px;
  overflow:hidden;
}</style>'; 
echo $javascript->codeblock('$(document).ready(function() {
$(\'textarea.resizable:not(.processed)\').TextAreaResizer();
});'); ?>

<?php echo $form->create(null, array('action' => 'edit/' . $path)); ?>
<?php echo $javascript->codeblock("
var handleTab = function(e, t) {
  var kc = e.which ? e.which : e.keyCode;
  var isSafari = navigator.userAgent.toLowerCase().indexOf('safari') != -1;
  var tabSize = 2;
  if ((kc == 9 || (isSafari && kc == 25)) && !e.shiftKey) {
    e.preventDefault();
    var tab = '';
    var ss = t.selectionStart;
    var se = t.selectionEnd;
    if (tabSize == 0) {
      tab = \"\t\";
    } else {
      var pos = ss;
      while (pos >= 0 && t.value.charCodeAt(pos) != 10) {
        pos--;
      }
      var l = tabSize - (ss - 1 - pos) % tabSize;
      for(var i = 0; i < l; i++) {
        tab += ' ';
      }
    }
    t.value = t.value.slice(0,ss).concat(tab).concat(t.value.slice(ss,t.value.length)); 
    if (ss == se) {
      t.selectionStart = t.selectionEnd = ss + tab.length;
    } else {
      t.selectionStart = ss + tab.length;
      t.selectionEnd = se + tab.length;
    }
    t.focus();
    return false;
  }
}"); ?>
<fieldset>
<legend><?php echo $path; ?></legend>
<?php echo $form->hidden('Fs.path', array('value' => $path)); ?>
<?php echo $form->input('Fs.content', array('value' => $content, 'type' => 'textarea', 'label' => false, 'class' => 'fileEdit resizable', 'rows' => 24, 'onkeydown' => 'handleTab(event, this);')); ?>
</fieldset>
<?php echo $form->end("Save File"); ?>
