<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$OSCOM_Language->load_definitions('reviews');
$breadcrumb->add(OSCOM::get_def('navbar_title'), OSCOM::link('reviews.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<div class="contentContainer">

<?php 
$Qreviews = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS r.reviews_id, left(rd.reviews_text, 100) as reviews_text, r.reviews_rating, r.date_added, p.products_id, pd.products_name, p.products_image, r.customers_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where p.products_id = r.products_id and p.products_status = 1 and r.reviews_status = 1 and r.reviews_id = rd.reviews_id and p.products_id = pd.products_id and pd.language_id = :language_id and pd.language_id = rd.languages_id order by r.reviews_id desc limit :page_set_offset, :page_set_max_results');
$Qreviews->bind_int(':language_id', $OSCOM_Language->get_id());
$Qreviews->set_page_set(MAX_DISPLAY_NEW_REVIEWS);
$Qreviews->execute();
if ($Qreviews->get_page_set_total_rows() > 0) {
    if (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') {
        ?>

  <div class="row">
    <div class="col-sm-6 pagenumber hidden-xs">
      <?php 
        echo $Qreviews->get_page_set_label(OSCOM::get_def('text_display_number_of_reviews'));
        ?>
    </div>
    <div class="col-sm-6">
      <span class="pull-right pagenav"><?php 
        echo $Qreviews->get_page_set_links(tep_get_all_get_params(['page', 'info']));
        ?></span>
      <span class="pull-right"><?php 
        echo OSCOM::get_def('text_result_page');
        ?></span>
    </div>
  </div>
<?php 
    }
    ?>
    <div class="contentText">
      <div class="reviews">
<?php 
    while ($Qreviews->fetch()) {
        echo '<blockquote class="col-sm-6">';
        echo '  <p><span class="pull-left">' . HTML::image(OSCOM::link_image($Qreviews->value('products_image')), $Qreviews->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</span>' . $Qreviews->value_protected('reviews_text') . ' ... </p><div class="clearfix"></div>';
        $review_name = $Qreviews->value_protected('customers_name');
        echo '<footer>';
        $review_name = $Qreviews->value_protected('customers_name');
        echo OSCOM::get_def('reviews_text_rated', ['reviews_rating' => HTML::stars($Qreviews->value('reviews_rating')), 'review_name' => $review_name]);
        echo '<a href="' . OSCOM::link('product_reviews.php', 'products_id=' . $Qreviews->value_int('products_id')) . '">';
        echo '<span class="pull-right label label-info">' . OSCOM::get_def('reviews_text_read_more') . '</span>';
        echo '</a>';
        echo '</footer>';
        echo '</blockquote>';
    }
    ?>
      </div>
      <div class="clearfix"></div>
    </div>
<?php 
} else {
    ?>

  <div class="alert alert-info">
    <?php 
    echo OSCOM::get_def('text_no_reviews');
    ?>
  </div>

<?php 
}
if ($Qreviews->get_page_set_total_rows() > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
    ?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php 
    echo $Qreviews->get_page_set_label(OSCOM::get_def('text_display_number_of_reviews'));
    ?>
  </div>
  <div class="col-sm-6">
    <span class="pull-right pagenav"><?php 
    echo $Qreviews->get_page_set_links(tep_get_all_get_params(['page', 'info']));
    ?></span>
    <span class="pull-right"><?php 
    echo OSCOM::get_def('text_result_page');
    ?></span>
  </div>
</div>
<?php 
}
?>

</div>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';