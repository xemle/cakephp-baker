<h1>View: <?php echo $fs->pathLink($path); ?><h1>

<?php 
if ($lang) {
  echo $geshi->highlight("<pre lang=\"$lang\">" . h($content) . "</pre>"); 
} else {
  echo "<pre>" . h($content) . "</pre>"; 
}
?>

<?php if ($attrs['writeable']) {
  echo $html->link('edit this file', 'edit/' . $path);
} ?>