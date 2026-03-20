<?php

use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php 
echo $content_width;
?>">
  <div class="footerbox generic-text">
    <h2><?php 
echo OSCOM::get_def('module_content_footer_text_heading_title');
?></h2>
    <?php 
echo OSCOM::get_def('module_content_footer_text_text');
?>
  </div>
</div>
