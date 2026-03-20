<?php

use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php 
echo OSCOM::get_def('module_boxes_categories_box_title');
?></div>
  <?php 
echo $category_tree;
?>
</div>
