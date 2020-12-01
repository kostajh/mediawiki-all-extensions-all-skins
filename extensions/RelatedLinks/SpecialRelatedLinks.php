<?php

class SpecialRelatedLinks extends SpecialPage
{
    public function __construct()
    {
        parent::__construct('RelatedLinks', 'editinterface');
    }

    public function execute($par)
    {
        $wgRequest = $this -> getRequest();
        $wgOutput = $this -> getOutput();
        $wgUser = $this -> getUser();

        $wgOutput->addModules('ext.relatedLinks');
        $this -> setHeaders();

        if (in_array("sysop", $wgUser -> getGroups()) == false) {
            $wgOutput -> addHTML(wfMessage('please_login'));
            return false;
        }

        $link_id = filter_var($wgRequest->getVal('link_id'), FILTER_SANITIZE_STRING);

        if ($wgRequest->wasPosted()) {
            $link_id = filter_var($_POST['link_id'], FILTER_SANITIZE_STRING);
            $return_url = SkinTemplate::makeSpecialUrl('RelatedLinks', 'link_id=' . $link_id);

            switch ($wgRequest->getVal('submit_type')) {
                case 'insert':
                    $new_subject = filter_var($wgRequest->getVal('subject'), FILTER_SANITIZE_STRING);
                    $new_url = filter_var($wgRequest->getVal('url'), FILTER_VALIDATE_URL);

                    if (empty($new_url)) {
                        throw new Exception("url not validate", 1);
                    }

                    if (empty($wgRequest->getVal('enable'))) {
                        $new_enable=0;
                    } else {
                        $new_enable=1;
                    }

                    $dbw = wfGetDB(DB_MASTER);

                    if ($dbw -> tableExists('related_links')) {
                        $tbl_links = $dbw -> tableName('related_links');
                    } else {
                        $result = RelatedLinksHooks::initiateSettingsForRelatedLinks();
                        if (!$result) {
                            die('Failed to create table Related Links');
                        }
                        $tbl_links = $dbw -> tableName('related_links');
                    }

                    $output = '<strong>' . $link_id . '</strong>';

                    // insert new related site
                    $order = $dbw -> selectField($tbl_links, 'MAX( links_order )', array('links_id' => $link_id));
                    $order = ($order) ? $order + 1 : 1;
                    $data = array('user_id' => $wgUser->getId(),'links_order' => $order, 'links_subject' => $new_subject, 'links_url' => $new_url, 'links_enable' => $new_enable, 'links_datetime' => date("Y-m-d H:i:s"), 'links_id' => $link_id);
                    $result = $dbw -> insert($tbl_links, $data);
                    $output .= '<h4>'.wfMessage('insert_related_links').'</h4>' . '<ul><li>' .
                        htmlspecialchars($wgRequest->getVal('subject')) . ' (' .
                        htmlspecialchars($new_url) . ')</li></ul>';

                    // return link
                    $output .= '<br /><a href="' . htmlspecialchars($return_url) . '">'.
                        wfMessage('return_page').'</a>';

                    break;
                case 'DELETE':
                    $dbw = wfGetDB(DB_MASTER);
                    $tbl_links = $dbw -> tableName('related_links');

                    $output = '<strong>' . $link_id . '</strong>';

                    // delete sidebar
                    if ($wgRequest->getVal('check')) {
                        $result = $dbw -> delete($tbl_links, array('id' => $wgRequest->getVal('check')));
                        $output .= '<h4>'.wfMessage('delete_related_links').'</h4>';
                    }

                    // return link
                    $output .= '<br /><a href="' . htmlspecialchars($return_url) . '">'.
                        wfMessage('return_page').'</a>';
                    break;
                default:
                    $dbw = wfGetDB(DB_MASTER);
                    $tbl_links = $dbw -> tableName('related_links');

                    $output = '<strong>' . $link_id . '</strong>';

                    // update sidebars
                    $output .= '<h4>'.wfMessage('update_related_links').'</h4>' . '<ul>';

                    foreach ($wgRequest->getVal('order') as $index => $order) {
                        $links_enable=0;
                        $subject = filter_var($wgRequest->getVal('subject')[$index], FILTER_SANITIZE_STRING);
                        $url = filter_var($wgRequest->getVal('url')[$index], FILTER_VALIDATE_URL);

                        if (isset($wgRequest->getVal('enable')[$index])) {
                            $links_enable=1;
                        }

                        $data = array('links_order' => $order, 'links_subject' => $subject, 'links_url' => $url, 'links_enable' => $links_enable);
                        $dbw -> update($tbl_links, $data, array('id' => $index));
                        $output .= '<li>' . $subject . ' (' . htmlspecialchars($url) . ')' .
                            ($links_enable ? ' : '.wfMessage('enable') : ' : '.wfMessage('disable')) . '</li>';
                    }

                    $output .= '</ul>';

                    // return link
                    $output .= '<br /><a href="' . htmlspecialchars($return_url) . '">'.
                        wfMessage('return_page').'</a>';
            }
        } else {
            $dbr = wfGetDB(defined('DB_SLAVE') ? DB_SLAVE : DB_REPLICA);

            if ($dbr -> tableExists('related_links')) {
                $tbl_links = $dbr -> tableName('related_links');
            } else {
                $result = RelatedLinksHooks::initiateSettingsForRelatedLinks();
                if (!$result) {
                    die('Failed to create table Related Links');
                }
                $tbl_links = $dbr -> tableName('related_links');
            }

            $fields = array('id', 'links_order', 'links_subject', 'links_url', 'links_enable');
            $conds = array('links_id' => $link_id);
            $opts = array('ORDER BY' => 'links_order');
            $result = $dbr -> select($tbl_links, $fields, $conds, 'Database::select', $opts);
            $rows = $dbr -> numRows($result);

            ob_start(); ?>
					<form id="related_links_form" method="post" action="">
						<fieldset>
							<legend><?php echo wfMessage('edit') ?>,<?php echo wfMessage('delete') ?></legend>
							<?php if (!$rows): ?>
							<h4><?php echo wfMessage('no_data') ?></h4>
              <?php else: ?>
							<input type="hidden" id="submit_type" name="submit_type" value="">
							<input type="hidden" id="submit_id" name="link_id" value="<?=$link_id ?>">
							<table class="lieTable" style="width: 100%;">
                <colgroup>
                  <col style="width:10%" />
                  <col style="width:15%" />
                  <col />
                  <col style="width:10%" />
                  <col style="width:10%" />
                </colgroup>
							<thead>
							<tr>
								<th><?php echo wfMessage('order_no') ?></th>
								<th><?php echo wfMessage('subject') ?></th>
								<th><?php echo wfMessage('rel_url') ?></th>
								<th><?php echo wfMessage('enable') ?></th>
								<th><label><?php echo wfMessage('delete_select') ?><input id="checkall" type="checkbox" /></label></th>
							<tr>
							</thead>
							<tbody id="links_list">
							<?php while ($row = $dbr->fetchObject($result)) {
                ?>
							<tr>
								<td><input type="number" name="order[<?=htmlspecialchars($row -> id) ?>]" value="<?=htmlspecialchars($row -> links_order) ?>" min="1" class="s_order" /></td>
								<td><input type="text" name="subject[<?=htmlspecialchars($row -> id) ?>]" value="<?=htmlspecialchars($row -> links_subject) ?>" style="width: 92%;" /></td>
								<td><input type="url" name="url[<?=htmlspecialchars($row -> id) ?>]" value="<?=htmlspecialchars($row -> links_url) ?>" style="width: 92%;" /></td>
								<td><input type="checkbox" name="enable[<?=htmlspecialchars($row -> id) ?>]" value="1"<?=($row -> links_enable == '1' ? ' checked' : '') ?> /></td>
								<td><input type="checkbox" class="checkitem" name="check[<?=htmlspecialchars($row -> id) ?>]" value="<?=htmlspecialchars($row -> id) ?>" /></td>
							</tr>
							<?php

            }
            $dbr->freeResult($result); ?>
							</tbody>
              <tfoot>
                <tr>
                  <td><input type="submit" value="<?php echo wfMessage('modify_all') ?>" /></td>
                  <td colspan="3">&nbsp;</td>
                  <td><input type="button" id="btn_delete-sidebar" value="<?php echo wfMessage('delete') ?>" /></td>
                </tr>
              </tfoot>
							</table>
					    <?php endif ?>
						</fieldset>
					</form>
					<form id="related_links_insert_form" method="post" action="">
            <fieldset>
							<legend><?php echo wfMessage('insert') ?></legend>
						<input type="hidden" name="link_id" value="<?=htmlspecialchars($wgRequest->getVal('link_id')) ?>" />
						<input type="hidden" name="submit_type" value="insert" />
						<table class="lieTable" style="width: 100%;">
              <colgroup>
                <col style="width:10%" />
                <col style="width:15%" />
                <col />
                <col style="width:10%" />
                <col style="width:10%" />
              </colgroup>
              <thead>
							<tr>
								<th><?php echo wfMessage('order_no') ?></th>
								<th><?php echo wfMessage('subject') ?></th>
								<th><?php echo wfMessage('rel_url') ?></th>
								<th><?php echo wfMessage('enable') ?></th>
								<th>&nbsp;</th>
							<tr>
							</thead>
              <tbody>
            <tr>
							<td><input type="number" name="order" value="1" min="1" class="s_order" /></td>
              <td><input type="text" name="subject" style="width: 92%" required="required" /></td>
              <td><input type="url" name="url" style="width: 92%" required="required" /></td>
							<td><input type="checkbox" name="enable" value="1" checked="checked" /></td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tfoot>
            <td><input type="submit" value="<?php echo wfMessage('insert') ?>" /></td>
            <td colspan="5">&nbsp;</td>
          </tfoot>
          </table>
						</fieldset>
          </form>

				<?php
                $output = ob_get_contents();
            ob_end_clean();
        }
        $wgOutput -> addHTML($output);
    }
}
