<?php

use OSC\OM\OSCOM;
?>

</div>

<?php 
require $osc_template->get_file('footer.php');
?>

<script>
$(function() {
  var url = document.location.toString();

  if (url.match('#')) {
    if ($('.nav-tabs a[data-target="#' + url.split('#')[1] + '"]').length === 1) {
      $('.nav-tabs a[data-target="#' + url.split('#')[1] + '"]').tab('show');
    }
  }
});
</script>

<script src="<?php 
echo OSCOM::link('Shop/ext/bootstrap/js/bootstrap.min.js', '', false);
?>"></script>

<script src="<?php 
echo OSCOM::link('Shop/ext/smartmenus/jquery.smartmenus.min.js', '', false);
?>"></script>
<script src="<?php 
echo OSCOM::link('Shop/ext/smartmenus/jquery.smartmenus.bootstrap.min.js', '', false);
?>"></script>

<script src="<?php 
echo OSCOM::link('Shop/ext/mustache/mustache.min.js', '', false);
?>"></script>

<script src="<?php 
echo OSCOM::link('Shop/ext/sortable/sortable.min.js', '', false);
?>"></script>

<script src="<?php 
echo OSCOM::link('Shop/ext/chartist/chartist.min.js', '', false);
?>"></script>

</body>
</html>
