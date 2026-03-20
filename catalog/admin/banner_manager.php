<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\File_System;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
$banner_extension = tep_banner_image_extension();
if (tep_not_null($action)) {
    switch ($action) {
        case 'fetchStats':
            $result = [];
            if (isset($_GET['banners_id']) && is_numeric($_GET['banners_id'])) {
                $Qbanner = $OSCOM_Db->prepare('select banners_title from :table_banners where banners_id = :banners_id');
                $Qbanner->bind_int(':banners_id', $_GET['banners_id']);
                $Qbanner->execute();
                if ($Qbanner->fetch() !== false) {
                    $days_shown = $days_clicked = [];
                    for ($i = 0; $i < 7; $i++) {
                        $date = date('m-d', strtotime('-' . $i . ' days'));
                        $days_shown[$date] = $days_clicked[$date] = 0;
                    }
                    $Qstats = $OSCOM_Db->prepare('select date_format(banners_history_date, "%m-%d") as date_day, banners_shown, banners_clicked from :table_banners_history where banners_id = :banners_id and banners_history_date >= date_sub(now(), interval 7 day)');
                    $Qstats->bind_int(':banners_id', $_GET['banners_id']);
                    $Qstats->execute();
                    while ($Qstats->fetch()) {
                        $days_shown[$Qstats->value('date_day')] = $Qstats->value_int('banners_shown');
                        $days_clicked[$Qstats->value('date_day')] = $Qstats->value_int('banners_clicked');
                    }
                    $result['labels'] = array_reverse(array_keys($days_shown));
                    $result['days'] = array_reverse(array_values($days_shown));
                    $result['clicks'] = array_reverse(array_values($days_clicked));
                    $result['title'] = $Qbanner->value_protected('banners_title');
                }
            }
            echo json_encode($result);
            exit;
        case 'setflag':
            if ($_GET['flag'] == '0' || $_GET['flag'] == '1') {
                tep_set_banner_status($_GET['bID'], $_GET['flag']);
                $oscom_message_stack->add(OSCOM::get_def('success_banner_status_updated'), 'success');
            } else {
                $oscom_message_stack->add(OSCOM::get_def('error_unknown_status_flag'), 'error');
            }
            OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
            break;
        case 'insert':
        case 'update':
            if (isset($_POST['banners_id'])) {
                $banners_id = HTML::sanitize($_POST['banners_id']);
            }
            $banners_title = HTML::sanitize($_POST['banners_title']);
            $banners_url = HTML::sanitize($_POST['banners_url']);
            $new_banners_group = HTML::sanitize($_POST['new_banners_group']);
            $banners_group = empty($new_banners_group) ? HTML::sanitize($_POST['banners_group']) : $new_banners_group;
            $banners_html_text = $_POST['banners_html_text'];
            $banners_image_local = HTML::sanitize($_POST['banners_image_local']);
            $banners_image_target = HTML::sanitize($_POST['banners_image_target']);
            $db_image_location = '';
            $expires_date = HTML::sanitize($_POST['expires_date']);
            $expires_impressions = HTML::sanitize($_POST['expires_impressions']);
            $date_scheduled = HTML::sanitize($_POST['date_scheduled']);
            $banner_error = false;
            if (empty($banners_title)) {
                $oscom_message_stack->add(OSCOM::get_def('error_banner_title_required'), 'error');
                $banner_error = true;
            }
            if (empty($banners_group)) {
                $oscom_message_stack->add(OSCOM::get_def('error_banner_group_required'), 'error');
                $banner_error = true;
            }
            if (empty($banners_html_text)) {
                if (empty($banners_image_local)) {
                    $banners_image = new upload('banners_image');
                    $banners_image->set_destination(OSCOM::get_config('dir_root', 'Shop') . 'images/' . $banners_image_target);
                    if ($banners_image->parse() == false || $banners_image->save() == false) {
                        $banner_error = true;
                    }
                }
            }
            if ($banner_error == false) {
                $db_image_location = tep_not_null($banners_image_local) ? $banners_image_local : $banners_image_target . $banners_image->filename;
                $sql_data_array = ['banners_title' => $banners_title, 'banners_url' => $banners_url, 'banners_image' => $db_image_location, 'banners_group' => $banners_group, 'banners_html_text' => $banners_html_text, 'expires_date' => 'null', 'expires_impressions' => 0, 'date_scheduled' => 'null'];
                if ($action == 'insert') {
                    $insert_sql_data = ['date_added' => 'now()', 'status' => '1'];
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                    $OSCOM_Db->save('banners', $sql_data_array);
                    $banners_id = $OSCOM_Db->last_insert_id();
                    $oscom_message_stack->add(OSCOM::get_def('success_banner_inserted'), 'success');
                } elseif ($action == 'update') {
                    $OSCOM_Db->save('banners', $sql_data_array, ['banners_id' => (int) $banners_id]);
                    $oscom_message_stack->add(OSCOM::get_def('success_banner_updated'), 'success');
                }
                if (tep_not_null($expires_date)) {
                    $expires_date = substr((string) $expires_date, 0, 4) . substr((string) $expires_date, 5, 2) . substr((string) $expires_date, 8, 2);
                    $OSCOM_Db->save('banners', ['expires_date' => $expires_date, 'expires_impressions' => 'null'], ['banners_id' => (int) $banners_id]);
                } elseif (tep_not_null($expires_impressions)) {
                    $OSCOM_Db->save('banners', ['expires_impressions' => $expires_impressions, 'expires_date' => 'null'], ['banners_id' => (int) $banners_id]);
                }
                if (tep_not_null($date_scheduled)) {
                    $date_scheduled = substr((string) $date_scheduled, 0, 4) . substr((string) $date_scheduled, 5, 2) . substr((string) $date_scheduled, 8, 2);
                    $OSCOM_Db->save('banners', ['status' => '0', 'date_scheduled' => $date_scheduled], ['banners_id' => (int) $banners_id]);
                }
                OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
            } else {
                $action = 'new';
            }
            break;
        case 'deleteconfirm':
            $banners_id = HTML::sanitize($_GET['bID']);
            if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
                $Qbanner = $OSCOM_Db->get('banners', 'banners_image', ['banners_id' => (int) $banners_id]);
                if (tep_not_null($Qbanner->value('banners_image')) && is_file(OSCOM::get_config('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'))) {
                    if (File_System::is_writable(OSCOM::get_config('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'))) {
                        unlink(OSCOM::get_config('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'));
                    } else {
                        $oscom_message_stack->add(OSCOM::get_def('error_image_is_not_writeable'), 'error');
                    }
                } else {
                    $oscom_message_stack->add(OSCOM::get_def('error_image_does_not_exist'), 'error');
                }
            }
            $OSCOM_Db->delete('banners', ['banners_id' => (int) $banners_id]);
            $OSCOM_Db->delete('banners_history', ['banners_id' => (int) $banners_id]);
            $oscom_message_stack->add(OSCOM::get_def('success_banner_removed'), 'success');
            OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
            break;
        case 'preview':
            $banners_id = HTML::sanitize($_GET['banner']);
            $Qbanner = $OSCOM_Db->get('banners', ['banners_title', 'banners_image', 'banners_html_text'], ['banners_id' => (int) $banners_id]);
            if ($Qbanner->check()) {
                echo '<h1>' . $Qbanner->value_protected('banners_title') . '</h1>';
                if (tep_not_null($Qbanner->value('banners_html_text'))) {
                    echo $Qbanner->value('banners_html_text');
                } elseif (tep_not_null($Qbanner->value('banners_image'))) {
                    echo HTML::image(OSCOM::link_image('Shop/' . $Qbanner->value('banners_image')), $Qbanner->value('banners_title'));
                }
                exit;
            }
            break;
    }
}
$show_listing = true;
require $osc_template->get_file('template_top.php');
if (empty($action)) {
    ?>

<div class="pull-right">
  <?php 
    echo HTML::button(OSCOM::get_def('image_new_banner'), 'fa fa-plus', OSCOM::link('banner_manager.php', 'action=new'), null, 'btn-info');
    ?>
</div>

<?php 
}
?>

<h2><i class="fa fa-picture-o"></i> <a href="<?php 
echo OSCOM::link('banner_manager.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<?php 
if (!empty($action)) {
    if ($action == 'new') {
        $show_listing = false;
        $form_action = 'insert';
        $parameters = ['expires_date' => '', 'date_scheduled' => '', 'banners_title' => '', 'banners_url' => '', 'banners_group' => '', 'banners_image' => '', 'banners_html_text' => '', 'expires_impressions' => ''];
        $b_info = new Object_Info($parameters);
        if (isset($_GET['bID'])) {
            $form_action = 'update';
            $b_id = HTML::sanitize($_GET['bID']);
            $Qbanner = $OSCOM_Db->get('banners', ['banners_title', 'banners_url', 'banners_image', 'banners_group', 'banners_html_text', 'status', 'date_format(date_scheduled, "%Y-%m-%d") as date_scheduled', 'date_format(expires_date, "%Y-%m-%d") as expires_date', 'expires_impressions', 'date_status_change'], ['banners_id' => (int) $b_id]);
            $b_info->object_info($Qbanner->to_array());
        } elseif (tep_not_null($_POST)) {
            $b_info->object_info($_POST);
        }
        $groups_array = [];
        $Qgroups = $OSCOM_Db->get('banners', 'distinct banners_group', null, 'banners_group');
        while ($Qgroups->fetch()) {
            $groups_array[] = ['id' => $Qgroups->value('banners_group'), 'text' => $Qgroups->value('banners_group')];
        }
        ?>

<?php 
        echo HTML::form('new_banner', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=' . $form_action), 'post', 'enctype="multipart/form-data"') . ($form_action == 'update' ? HTML::hidden_field('banners_id', $b_id) : '');
        ?>

<div class="panel panel-info">
  <div class="panel-body">
    <div class="form-group">
      <label for="banners_title"><?php 
        echo OSCOM::get_def('text_banners_title') . OSCOM::get_def('text_field_required');
        ?></label>
      <?php 
        echo HTML::input_field('banners_title', $b_info->banners_title);
        ?>
    </div>

    <div class="form-group">
      <label for="banners_url"><?php 
        echo OSCOM::get_def('text_banners_url');
        ?></label>
      <?php 
        echo HTML::input_field('banners_url', $b_info->banners_url);
        ?>
    </div>

    <div class="form-group">
      <label for="banners_group"><?php 
        echo OSCOM::get_def('text_banners_group');
        ?></label>
      <?php 
        echo HTML::select_field('banners_group', $groups_array, $b_info->banners_group);
        ?>

      <label for="new_banners_group"><?php 
        echo OSCOM::get_def('text_banners_new_group') . (sizeof($groups_array) > 0 ? '' : OSCOM::get_def('text_field_required'));
        ?></label>
      <?php 
        echo HTML::input_field('new_banners_group');
        ?>
    </div>

    <div class="form-group">
      <label for="banners_image"><?php 
        echo OSCOM::get_def('text_banners_image');
        ?></label>
      <?php 
        echo HTML::file_field('banners_image');
        ?>

      <label for="banners_image_local"><?php 
        echo OSCOM::get_def('text_banners_image_local');
        ?></label>
      <div class="input-group">
        <div class="input-group-addon"><?php 
        echo OSCOM::get_config('dir_root', 'Shop') . 'images/';
        ?></div>
        <?php 
        echo HTML::input_field('banners_image_local', $b_info->banners_image ?? '');
        ?>
      </div>
    </div>

    <div class="form-group">
      <label for="banners_image_target"><?php 
        echo OSCOM::get_def('text_banners_image_target');
        ?></label>
      <div class="input-group">
        <div class="input-group-addon"><?php 
        echo OSCOM::get_config('dir_root', 'Shop') . 'images/';
        ?></div>
        <?php 
        echo HTML::input_field('banners_image_target');
        ?>
      </div>
    </div>

    <div class="form-group">
      <label for="banners_html_text"><?php 
        echo OSCOM::get_def('text_banners_html_text');
        ?></label>
      <?php 
        echo HTML::textarea_field('banners_html_text', '60', '5', $b_info->banners_html_text);
        ?>
    </div>

    <div class="form-group">
      <label for="date_scheduled"><?php 
        echo OSCOM::get_def('text_banners_scheduled_at');
        ?></label>
      <?php 
        echo HTML::input_field('date_scheduled', $b_info->date_scheduled, 'id="date_scheduled"', 'date');
        ?>
    </div>

    <div class="form-group">
      <label for="expires_date"><?php 
        echo OSCOM::get_def('text_banners_expires_on');
        ?></label>
      <?php 
        echo HTML::input_field('expires_date', $b_info->expires_date, 'id="expires_date"', 'date');
        ?>

      <label for="expires_impressions"><?php 
        echo OSCOM::get_def('text_banners_or_at');
        ?></label>
      <?php 
        echo HTML::input_field('expires_impressions', $b_info->expires_impressions, 'maxlength="7" size="7"');
        ?>
      <p class="help-block"><?php 
        echo OSCOM::get_def('text_banners_impressions');
        ?></p>
    </div>

    <?php 
        echo HTML::button(OSCOM::get_def('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']), null, 'btn-link');
        ?>
  </div>
</div>

</form>

<p>
  <?php 
        echo OSCOM::get_def('text_banners_banner_note') . '<br />' . OSCOM::get_def('text_banners_insert_note') . '<br />' . OSCOM::get_def('text_banners_expircy_note') . '<br />' . OSCOM::get_def('text_banners_schedule_note');
        ?>
</p>

<?php 
    } else {
        $heading = $contents = [];
        if (isset($_GET['bID'])) {
            $Qbanner = $OSCOM_Db->get('banners', '*', ['banners_id' => (int) $_GET['bID']]);
            if ($Qbanner->fetch() !== false) {
                $b_info = new Object_Info($Qbanner->to_array());
                if ($action == 'delete') {
                    $heading[] = ['text' => $b_info->banners_title];
                    $contents = ['form' => HTML::form('banners', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $b_info->banners_id . '&action=deleteconfirm'))];
                    $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
                    $contents[] = ['text' => '<strong>' . $b_info->banners_title . '</strong>'];
                    if ($b_info->banners_image) {
                        $contents[] = ['text' => HTML::checkbox_field('delete_image', 'on', true) . ' ' . OSCOM::get_def('text_info_delete_image')];
                    }
                    $contents[] = ['text' => HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']), null, 'btn-link')];
                }
            }
        }
        if (tep_not_null($heading) && tep_not_null($contents)) {
            $show_listing = false;
            echo HTML::panel($heading, $contents, ['type' => 'info']);
        }
    }
}
if ($show_listing === true) {
    ?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
    echo OSCOM::get_def('table_heading_banners');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_groups');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_statistics');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_status');
    ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
    $Qbanners = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS banners_id, banners_title, banners_group, status from :table_banners order by banners_title, banners_group limit :page_set_offset, :page_set_max_results');
    $Qbanners->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qbanners->execute();
    while ($Qbanners->fetch()) {
        $Qinfo = $OSCOM_Db->get('banners_history', ['sum(banners_shown) as banners_shown', 'sum(banners_clicked) as banners_clicked'], ['banners_id' => $Qbanners->value_int('banners_id')]);
        ?>

    <tr>
      <td><?php 
        echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'action=preview&banner=' . $Qbanners->value_int('banners_id')) . '" target="_blank"><i class="fa fa-external-link" title="View Banner"></i></a>&nbsp;' . $Qbanners->value('banners_title');
        ?></td>
      <td class="text-right"><?php 
        echo $Qbanners->value('banners_group');
        ?></td>
      <td class="text-right"><?php 
        echo $Qinfo->value_int('banners_shown') . ' / ' . $Qinfo->value_int('banners_clicked');
        ?></td>
      <td class="text-right">

<?php 
        if ($Qbanners->value_int('status') === 1) {
            echo '<i class="fa fa-circle text-success" title="' . OSCOM::get_def('image_icon_status_green') . '"></i>&nbsp;<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->value_int('banners_id') . '&action=setflag&flag=0') . '"><i class="fa fa-circle-o text-danger" title="' . OSCOM::get_def('image_icon_status_red_light') . '"></i></a>';
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->value_int('banners_id') . '&action=setflag&flag=1') . '"><i class="fa fa-circle-o text-success" title="' . OSCOM::get_def('image_icon_status_green_light') . '"></i></a>&nbsp;<i class="fa fa-circle text-danger" title="' . OSCOM::get_def('image_icon_status_red') . '"></i>';
        }
        ?>

      </td>
      <td class="action">
        <?php 
        echo '<a data-banner-id="' . $Qbanners->value_int('banners_id') . '" data-toggle="modal" data-target="#statsModal"><i class="fa fa-line-chart" title="' . OSCOM::get_def('icon_statistics') . '"></i></a>';
        ?>
        <?php 
        echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->value_int('banners_id') . '&action=new') . '"><i class="fa fa-pencil" title="' . OSCOM::get_def('image_edit') . '"></i></a>';
        ?>
        <?php 
        echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->value_int('banners_id') . '&action=delete') . '"><i class="fa fa-trash" title="' . OSCOM::get_def('image_delete') . '"></i></a>';
        ?>
      </td>
    </tr>

<?php 
    }
    ?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?php 
    echo $Qbanners->get_page_set_links();
    ?></span>
  <?php 
    echo $Qbanners->get_page_set_label(OSCOM::get_def('text_display_number_of_banners'));
    ?>
</div>

<div id="statsModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <div class="statsModalContent">
          <i class="fa fa-spinner fa-spin fa-fw"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  var fetchStatsUrl = '<?php 
    echo addslashes((string) OSCOM::link('banner_manager.php', 'action=fetchStats&banners_id={{id}}'));
    ?>';

  $('#statsModal').on('shown.bs.modal', function (e) {
    var json = $.getJSON(Mustache.render(fetchStatsUrl, {id: $(e.relatedTarget).data('banner-id')}), function(data) {
      if (typeof data.labels !== 'undefined') {
        $('#statsModal .statsModalContent').html('<h4 class="modal-title">' + data.title + '</h4><div id="banner_statistics"></div><div class="text-right"><span class="label label-info">Views</span><span class="label label-danger">Clicks</span></div>');

        var data = {
          labels: data.labels,
          series: [
            {
              name: 'shown',
              data: data.days
            },
            {
              name: 'clicked',
              data: data.clicks
            }
          ]
        };

        var options = {
          fullWidth: true,
          series: {
            'shown': {
              showPoint: false,
              showArea: true
            },
            'clicked': {
              showPoint: false,
              showArea: true
            }
          },
          height: '400px',
          axisY: {
            labelInterpolationFnc: function skipLabels(value, index) {
              return index % 2  === 0 ? value : null;
            }
          }
        }

        var chart = new Chartist.Line('#banner_statistics', data, options);

        chart.on('draw', function(context) {
          if ((typeof context.series !== 'undefined') && (typeof context.series.name !== 'undefined')) {
            if (context.series.name == 'shown') {
              if (context.type === 'line') {
                context.element.attr({
                  style: 'stroke: skyblue;'
                });
              } else if (context.type === 'area') {
                context.element.attr({
                  style: 'fill: skyblue;'
                });
              }
            } else if (context.series.name == 'clicked') {
              if (context.type === 'line') {
                context.element.attr({
                  style: 'stroke: salmon;'
                });
              } else if (context.type === 'area') {
                context.element.attr({
                  style: 'fill: salmon;'
                });
              }
            }
          }
        });

//        chart.update();
      } else {
        $('#statsModal .statsModalContent').html('<div class="alert alert-danger">Could not find banner statistics.</div>');
      }
    }).fail(function() {
        $('#statsModal .statsModalContent').html('<div class="alert alert-danger">Could not fetch banner statistics.</div>');
    });
  });
});
</script>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';