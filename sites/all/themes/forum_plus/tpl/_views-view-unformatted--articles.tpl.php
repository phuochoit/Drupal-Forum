<?php

/**
 * @file
 * Default simple view template to display a list of rows.
 *
 * @ingroup views_templates
 */

$marks = array(1,2,2,1,1,1,1);
$len   = count($marks);
$defaultClasses = array('wp_article_one_col', 'wp_article_two_col');
?>
<?php if (!empty($title)): ?>
  <h3><?php print $title; ?></h3>
<?php endif; ?>
<?php foreach ($rows as $id => $row): ?>
  <?php $defaultClass = $defaultClasses[ $marks[$id % $len] - 1 ]; ?>
  <div class="<?php print $defaultClass . ($classes_array[$id] ? ' ' . $classes_array[$id] : ''); ?>">
    <?php print $row; ?>
  </div>
<?php endforeach; ?>
