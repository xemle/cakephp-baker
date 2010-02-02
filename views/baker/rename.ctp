<h1>Rename <?php echo $fs->pathLink($path); ?></h1>
<?php $session->flash(); ?>
<?php echo $form->create(null, array('action' => 'rename/' . $path)); ?>

<fieldset><legend><?php echo h($from); ?></legend>
<?php
  echo $form->hidden('Fs.from', array('value' => $from));
  echo $form->input('Fs.to', array('value' => $from, 'label' => 'New name')); 
?>
</fieldset>
<?php echo $form->end("Rename"); ?>