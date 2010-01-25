<h1>View: <?php echo $fs->pathLink($path); ?><h1>

<pre><code><?php echo h($content); ?>
</code></pre>

<?php if ($attrs['writeable']) {
  echo $html->link('edit this file', 'edit/' . $path);
} ?>