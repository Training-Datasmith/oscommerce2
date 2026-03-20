<?php

use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php 
echo OSCOM::get_def('module_boxes_order_history_box_title');
?></div>
  <div class="panel-body">
    <ul class="list-unstyled">
      <?php 
echo $customer_orders_string;
?>
    </ul>
  </div>
</div>
