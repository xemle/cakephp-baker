<h1>List <?php echo $fs->pathLink($path); ?></h1>
<?php $session->flash(); ?>
<?php echo $form->create(null, array('action' => 'index/' . $path)); ?>

<?php
  $headers = array('', 'Name', 'Size', 'Date', 'Actions');

  $cells = array();
  $cells = array();
  foreach($dirs as $dir) {
    $actions = array();
    $actions[] = $html->link('rename', 'rename/' . $path . $dir);
    $actions[] = "<span class='delete'>" . $html->link('delete', 'delete/' . $path . $dir, false, "Delete folder " . $path . $dir . '?') . "</span>";
    $cells[] = array(
      $form->input('Fs.file][', array('value' => $dir, 'type' => 'checkbox', 'label' => false, 'secure' => false)),
      $html->link($dir, 'index/' . $path . $dir),
      0,
      '',
      implode(' ', $actions)
      );
  }
  
  foreach($fileList as $file => $attr) {
    $actions = array();
      $actions[] = $html->link('rename', 'rename/' . $path . $file);
    if ($attr['type'] == 'text' && $attr['writeable']) {
      $actions[] = $html->link('edit', 'edit/' . $path . $file);
    }
    if ($attr['type'] == 'archive' && $attr['writeable']) {
      $actions[] = $html->link('unzip', 'unzip/' . $path . $file);
    }
    $actions[] = "<span class='delete'>" . $html->link('delete', 'delete/' . $path . $file, false, "Delete file " . $file . '?') . "</span>";
    $cells[] = array(
      $form->input('Fs.file][', array('value' => $file, 'type' => 'checkbox', 'label' => false, 'secure' => false)),
      $html->link($file, 'view/' . $path . $file),
      $number->toReadableSize($attr['size']),
      date('Y-m-d H:i:s', $attr['time']),
      implode(' ', $actions)
      );
  }
?>
<table>
<thead>
<?php echo $html->tableHeaders($headers); ?>
</thead>

<tbody>
<?php echo $html->tableCells($cells); ?>
</tbody>
</table>
<?php 
  $actionOptions = array(
    'none' => 'Select Action',
    'cd' => 'Change directory to ...',
    'move' => 'Move files to directory ...',
    'copy' => 'Copy files to directory ...',
    'delete' => 'Delete selected files'
    );
  echo $form->input('Fs.action', array('type' => 'select', 'options' => $actionOptions, 'label' => false, 'div' => false)); ?>
<?php 
  $treeOptions = $fs->treeToSelect($tree);
  $treeOptions = am(array('none' => 'Select Directory'), $treeOptions);
  echo $form->input('Fs.path', array('type' => 'select', 'options' => $treeOptions, 'label' => false, 'div' => false, 'escape' => false)); 
?>
<?php echo $form->submit('Go', array('div' => false)); ?>
<?php echo $form->end(); ?>

<?php if ($canCreateFile): ?>
<?php echo $form->create(null, array('action' => 'create/' . $path)); ?>
<fieldset><legend>Create File</legend>
<?php echo $form->input('Fs.name'); ?>
<?php echo $form->input('Fs.isDir', array('label' => 'Create Directory', 'type' => 'checkbox')); ?>
</fieldset>
<?php echo $form->end("Create File"); ?>

<?php echo $form->create(null, array('action' => 'upload/' . $path, 'type' => 'file')); ?>
<fieldset><legend>Upload File</legend>
<?php echo $form->file('Fs.file'); ?>
</fieldset>
<?php echo $form->end("Upload"); ?>

<?php endif; ?>
