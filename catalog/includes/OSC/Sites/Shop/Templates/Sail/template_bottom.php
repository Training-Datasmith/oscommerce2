      </div> <!-- bodyContent //-->

<?php 
if ($osc_template->has_blocks('boxes_column_left')) {
    ?>

      <div id="columnLeft" class="col-md-<?php 
    echo $osc_template->get_grid_column_width();
    ?>  col-md-pull-<?php 
    echo $osc_template->get_grid_content_width();
    ?>">
        <?php 
    echo $osc_template->get_blocks('boxes_column_left');
    ?>
      </div>

<?php 
}
if ($osc_template->has_blocks('boxes_column_right')) {
    ?>

      <div id="columnRight" class="col-md-<?php 
    echo $osc_template->get_grid_column_width();
    ?>">
        <?php 
    echo $osc_template->get_blocks('boxes_column_right');
    ?>
      </div>

<?php 
}
?>

    </div> <!-- row -->

  </div> <!-- bodyWrapper //-->

  <?php 
require $osc_template->get_file('footer.php');
?>

<script src="ext/bootstrap/js/bootstrap.min.js"></script>
<?php 
echo $osc_template->get_blocks('footer_scripts');
?>

</body>
</html>
