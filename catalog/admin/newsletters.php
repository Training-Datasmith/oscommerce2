<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'lock':
        case 'unlock':
            $newsletter_id = HTML::sanitize($_GET['nID']);
            $status = $action == 'lock' ? '1' : '0';
            $OSCOM_Db->save('newsletters', ['locked' => $status], ['newsletters_id' => (int) $newsletter_id]);
            OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']);
            break;
        case 'insert':
        case 'update':
            if (isset($_POST['newsletter_id'])) {
                $newsletter_id = HTML::sanitize($_POST['newsletter_id']);
            }
            $newsletter_module = HTML::sanitize($_POST['module']);
            $allowed = array_map(fn($v) => basename((string) $v, '.php'), glob('includes/modules/newsletters/*.php'));
            if (!in_array($newsletter_module, $allowed)) {
                $oscom_message_stack->add(OSCOM::get_def('error_newsletter_module_not_exists'), 'error');
                $newsletter_error = true;
            }
            $title = HTML::sanitize($_POST['title']);
            $content = $_POST['content'];
            $content_html = $_POST['content_html'];
            $newsletter_error = false;
            if (empty($title)) {
                $oscom_message_stack->add(OSCOM::get_def('error_newsletter_title'), 'error');
                $newsletter_error = true;
            }
            if (empty($newsletter_module)) {
                $oscom_message_stack->add(OSCOM::get_def('error_newsletter_module'), 'error');
                $newsletter_error = true;
            }
            if ($newsletter_error == false) {
                $sql_data_array = ['title' => $title, 'content' => $content, 'content_html' => $content_html, 'module' => $newsletter_module];
                if ($action == 'insert') {
                    $sql_data_array['date_added'] = 'now()';
                    $sql_data_array['status'] = '0';
                    $sql_data_array['locked'] = '0';
                    $OSCOM_Db->save('newsletters', $sql_data_array);
                    $newsletter_id = $OSCOM_Db->last_insert_id();
                } elseif ($action == 'update') {
                    $OSCOM_Db->save('newsletters', $sql_data_array, ['newsletters_id' => (int) $newsletter_id]);
                }
                OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletter_id);
            } else {
                $action = 'new';
            }
            break;
        case 'deleteconfirm':
            $newsletter_id = HTML::sanitize($_GET['nID']);
            $OSCOM_Db->delete('newsletters', ['newsletters_id' => (int) $newsletter_id]);
            OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page']);
            break;
        case 'delete':
        case 'new':
            if (!isset($_GET['nID'])) {
                break;
            }
        // no break
        case 'send':
        case 'confirm_send':
            $newsletter_id = HTML::sanitize($_GET['nID']);
            $Qcheck = $OSCOM_Db->get('newsletters', 'locked', ['newsletters_id' => (int) $newsletter_id]);
            if ($Qcheck->fetch() !== false) {
                if ($Qcheck->value_int('locked') < 1) {
                    switch ($action) {
                        case 'delete':
                            $error = OSCOM::get_def('error_remove_unlocked_newsletter');
                            break;
                        case 'new':
                            $error = OSCOM::get_def('error_edit_unlocked_newsletter');
                            break;
                        case 'send':
                        case 'confirm_send':
                            $error = OSCOM::get_def('error_send_unlocked_newsletter');
                            break;
                    }
                    $oscom_message_stack->add($error, 'error');
                    OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']);
                }
            }
            break;
    }
}
require $osc_template->get_file('template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php 
echo OSCOM::get_def('heading_title');
?></td>
          </tr>
        </table></td>
      </tr>
<?php 
if ($action == 'new') {
    $form_action = 'insert';
    $parameters = ['title' => '', 'content' => '', 'content_html' => '', 'module' => ''];
    $n_info = new Object_Info($parameters);
    if (isset($_GET['nID'])) {
        $form_action = 'update';
        $n_id = HTML::sanitize($_GET['nID']);
        $Qnewsletter = $OSCOM_Db->get('newsletters', ['title', 'content', 'content_html', 'module'], ['newsletters_id' => (int) $n_id]);
        $n_info->object_info($Qnewsletter->to_array());
    } elseif ($_POST) {
        $n_info->object_info($_POST);
    }
    $file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
    $directory_array = [];
    if ($dir = dir('includes/modules/newsletters/')) {
        while ($file = $dir->read()) {
            if (!is_dir('includes/modules/newsletters/' . $file)) {
                if (substr($file, strrpos($file, '.')) == $file_extension) {
                    $directory_array[] = $file;
                }
            }
        }
        sort($directory_array);
        $dir->close();
    }
    for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
        $modules_array[] = ['id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')), 'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.'))];
    }
    ?>
      <tr><?php 
    echo HTML::form('newsletter', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&action=' . $form_action));
    if ($form_action == 'update') {
        echo HTML::hidden_field('newsletter_id', $n_id);
    }
    ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('text_newsletter_module');
    ?></td>
            <td class="main"><?php 
    echo HTML::select_field('module', $modules_array, $n_info->module);
    ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('text_newsletter_title');
    ?></td>
            <td class="main"><?php 
    echo HTML::input_field('title', $n_info->title) . OSCOM::get_def('text_field_required');
    ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php 
    echo OSCOM::get_def('text_newsletter_content');
    ?></td>
            <td class="main">
              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#html_email" aria-controls="html_email" role="tab" data-toggle="tab"><?php 
    echo OSCOM::get_def('email_type_html');
    ?></a></li>
                <li role="presentation"><a href="#plain_email" aria-controls="plain_email" role="tab" data-toggle="tab"><?php 
    echo OSCOM::get_def('email_type_plain');
    ?></a></li>
              </ul>

              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="html_email">
                  <?php 
    echo HTML::textarea_field('content_html', '60', '15', $n_info->content_html);
    ?>
                </div>

                <div role="tabpanel" class="tab-pane" id="plain_email">
                  <?php 
    echo HTML::textarea_field('content', '60', '15', $n_info->content);
    ?>
                </div>
              </div>
            </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" align="right"><?php 
    echo HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&' . (isset($_GET['nID']) ? 'nID=' . $_GET['nID'] : '')));
    ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php 
} elseif ($action == 'preview') {
    $n_id = HTML::sanitize($_GET['nID']);
    $Qnewsletter = $OSCOM_Db->get('newsletters', ['title', 'content', 'content_html', 'module'], ['newsletters_id' => (int) $n_id]);
    $n_info = new Object_Info($Qnewsletter->to_array());
    ?>
      <tr>
        <td class="smallText" align="right"><?php 
    echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
    ?></td>
      </tr>
      <tr>
        <td>
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#html_preview" aria-controls="html_preview" role="tab" data-toggle="tab"><?php 
    echo OSCOM::get_def('email_type_html');
    ?></a></li>
            <li role="presentation"><a href="#plain_preview" aria-controls="plain_preview" role="tab" data-toggle="tab"><?php 
    echo OSCOM::get_def('email_type_plain');
    ?></a></li>
          </ul>

          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="html_preview">
              <iframe id="newsletterHtmlPreviewContent" style="width: 100%; height: 400px; border: 0;"></iframe>

              <script id="newsletterHtmlPreview" type="x-tmpl-mustache">
                <?php 
    echo HTML::output_protected($n_info->content_html);
    ?>
              </script>

              <script>
                $(function() {
                  var content = $('<div />').html($('#newsletterHtmlPreview').html()).text();
                  $('#newsletterHtmlPreviewContent').contents().find('html').html(content);
                });
              </script>
            </div>

            <div role="tabpanel" class="tab-pane" id="plain_preview">
              <?php 
    echo nl2br((string) HTML::output_protected($n_info->content));
    ?>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php 
    echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
    ?></td>
      </tr>
<?php 
} elseif ($action == 'send') {
    $n_id = HTML::sanitize($_GET['nID']);
    $Qnewsletter = $OSCOM_Db->get('newsletters', ['title', 'content', 'content_html', 'module'], ['newsletters_id' => (int) $n_id]);
    $n_info = new Object_Info($Qnewsletter->to_array());
    $OSCOM_Language->load_definitions('modules/newsletters/' . $n_info->module);
    include 'includes/modules/newsletters/' . $n_info->module . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
    $module_name = $n_info->module;
    $module = new $module_name($n_info->title, $n_info->content, $n_info->content_html);
    ?>
      <tr>
        <td><?php 
    if ($module->show_choose_audience) {
        echo $module->choose_audience();
    } else {
        echo $module->confirm();
    }
    ?></td>
      </tr>
<?php 
} elseif ($action == 'confirm') {
    $n_id = HTML::sanitize($_GET['nID']);
    $Qnewsletter = $OSCOM_Db->get('newsletters', ['title', 'content', 'content_html', 'module'], ['newsletters_id' => (int) $n_id]);
    $n_info = new Object_Info($Qnewsletter->to_array());
    $OSCOM_Language->load_definitions('modules/newsletters/' . $n_info->module);
    include 'includes/modules/newsletters/' . $n_info->module . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
    $module_name = $n_info->module;
    $module = new $module_name($n_info->title, $n_info->content, $n_info->content_html);
    ?>
      <tr>
        <td><?php 
    echo $module->confirm();
    ?></td>
      </tr>
<?php 
} elseif ($action == 'confirm_send') {
    $n_id = HTML::sanitize($_GET['nID']);
    $Qnewsletter = $OSCOM_Db->get('newsletters', ['newsletters_id', 'title', 'content', 'content_html', 'module'], ['newsletters_id' => (int) $n_id]);
    $n_info = new Object_Info($Qnewsletter->to_array());
    $OSCOM_Language->load_definitions('modules/newsletters/' . $n_info->module);
    include 'includes/modules/newsletters/' . $n_info->module . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
    $module_name = $n_info->module;
    $module = new $module_name($n_info->title, $n_info->content, $n_info->content_html);
    ?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" valign="middle"><?php 
    echo HTML::image(OSCOM::link_image('ani_send_email.gif'), OSCOM::get_def('image_ani_send_email'));
    ?></td>
            <td class="main" valign="middle"><strong><?php 
    echo OSCOM::get_def('text_please_wait');
    ?></strong></td>
          </tr>
        </table></td>
      </tr>
<?php 
    tep_set_time_limit(0);
    flush();
    $module->send($n_info->newsletters_id);
    ?>
      <tr>
        <td class="main"><font color="#ff0000"><strong><?php 
    echo OSCOM::get_def('text_finished_sending_emails');
    ?></strong></font></td>
      </tr>
      <tr>
        <td class="smallText"><?php 
    echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
    ?></td>
      </tr>
<?php 
} else {
    ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
    echo OSCOM::get_def('table_heading_newsletters');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_size');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_module');
    ?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
    echo OSCOM::get_def('table_heading_sent');
    ?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
    echo OSCOM::get_def('table_heading_status');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_action');
    ?>&nbsp;</td>
              </tr>
<?php 
    $Qnewsletters = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS newsletters_id, title, length(content) as content_length, module, date_added, date_sent, status, locked from :table_newsletters order by date_added desc limit :page_set_offset, :page_set_max_results');
    $Qnewsletters->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qnewsletters->execute();
    while ($Qnewsletters->fetch()) {
        if ((!isset($_GET['nID']) || isset($_GET['nID']) && (int) $_GET['nID'] === $Qnewsletters->value_int('newsletters_id')) && !isset($n_info) && !str_starts_with($action, 'new')) {
            $n_info = new Object_Info($Qnewsletters->to_array());
        }
        if (isset($n_info) && is_object($n_info) && $Qnewsletters->value_int('newsletters_id') === (int) $n_info->newsletters_id) {
            echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=preview') . '\'">' . "\n";
        } else {
            echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->value_int('newsletters_id')) . '\'">' . "\n";
        }
        ?>
                <td class="dataTableContent"><?php 
        echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->value_int('newsletters_id') . '&action=preview') . '">' . HTML::image(OSCOM::link_image('icons/preview.gif'), OSCOM::get_def('icon_preview')) . '</a>&nbsp;' . $Qnewsletters->value('title');
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        echo number_format($Qnewsletters->value_int('content_length')) . ' bytes';
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        echo $Qnewsletters->value('module');
        ?></td>
                <td class="dataTableContent" align="center"><?php 
        if ($Qnewsletters->value_int('status') === 1) {
            echo HTML::image(OSCOM::link_image('icons/tick.gif'), OSCOM::get_def('icon_tick'));
        } else {
            echo HTML::image(OSCOM::link_image('icons/cross.gif'), OSCOM::get_def('icon_cross'));
        }
        ?></td>
                <td class="dataTableContent" align="center"><?php 
        if ($Qnewsletters->value_int('locked') > 0) {
            echo HTML::image(OSCOM::link_image('icons/locked.gif'), OSCOM::get_def('icon_locked'));
        } else {
            echo HTML::image(OSCOM::link_image('icons/unlocked.gif'), OSCOM::get_def('icon_unlocked'));
        }
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (isset($n_info) && is_object($n_info) && $Qnewsletters->value_int('newsletters_id') === (int) $n_info->newsletters_id) {
            echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'), '');
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->value_int('newsletters_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
        }
        ?>&nbsp;</td>
              </tr>
<?php 
    }
    ?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
    echo $Qnewsletters->get_page_set_label(OSCOM::get_def('text_display_number_of_newsletters'));
    ?></td>
                    <td class="smallText" align="right"><?php 
    echo $Qnewsletters->get_page_set_links();
    ?></td>
                  </tr>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php 
    echo HTML::button(OSCOM::get_def('image_new_newsletter'), 'fa fa-plus', OSCOM::link(FILENAME_NEWSLETTERS, 'action=new'));
    ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php 
    $heading = [];
    $contents = [];
    switch ($action) {
        case 'delete':
            $heading[] = ['text' => '<strong>' . $n_info->title . '</strong>'];
            $contents = ['form' => HTML::form('newsletters', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=deleteconfirm'))];
            $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
            $contents[] = ['text' => '<br /><strong>' . $n_info->title . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']))];
            break;
        default:
            if (isset($n_info) && is_object($n_info)) {
                $heading[] = ['text' => '<strong>' . $n_info->title . '</strong>'];
                if ($n_info->locked > 0) {
                    $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=new')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=delete')) . HTML::button(OSCOM::get_def('image_preview'), 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=preview')) . HTML::button(OSCOM::get_def('image_send'), 'fa fa-envelope', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=send')) . HTML::button(OSCOM::get_def('image_unlock'), 'fa fa-unlock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=unlock'))];
                } else {
                    $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_preview'), 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=preview')) . HTML::button(OSCOM::get_def('image_lock'), 'fa fa-lock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $n_info->newsletters_id . '&action=lock'))];
                }
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_newsletter_date_added') . ' ' . DateTime::to_short($n_info->date_added)];
                if ($n_info->status == '1') {
                    $contents[] = ['text' => OSCOM::get_def('text_newsletter_date_sent') . ' ' . DateTime::to_short($n_info->date_sent)];
                }
            }
            break;
    }
    if (tep_not_null($heading) && tep_not_null($contents)) {
        echo '            <td width="25%" valign="top">' . "\n";
        $box = new box();
        echo $box->info_box($heading, $contents);
        echo '            </td>' . "\n";
    }
    ?>
          </tr>
        </table></td>
      </tr>
<?php 
}
?>
    </table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';