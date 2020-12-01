<?php
/**
 * Hooks for RelatedLinks extension
 *
 * @file
 * @ingroup Extensions
 */

class RelatedLinksHooks
{

    public static function initiateSettingsForRelatedLinks()
    {
        $dbw = wfGetDB(DB_MASTER);
        $query    = "CREATE TABLE " . $dbw->tableName('related_links') . " ( " .
            "	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, " .
            "	`user_id` INT(10) UNSIGNED NOT NULL, " .
            "	`links_id` VARCHAR(60) NOT NULL DEFAULT '', " .
            "	`links_order` TINYINT(3) NOT NULL DEFAULT 0, " .
            "	`links_subject` VARCHAR(60) NOT NULL, " .
            "	`links_url` VARCHAR(255) NOT NULL, " .
            "	`links_enable` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0, " .
            "	`links_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', " .
            "	`links_import_domain` VARCHAR(50) NOT NULL DEFAULT '', " .
            "	PRIMARY KEY (`id`), " .
            "	UNIQUE KEY (`user_id`, `links_id`, `links_url`), " .
            "	KEY index_sort (`links_id`, `links_order`)" .
            ")";
        return $dbw->query($query);
    }

    /*
      * Draw Related Links Management
      */
    public static function SkinTemplateToolboxEnd($sk)
    {
        global $wgRequest;
        $dbr = wfGetDB(defined('DB_SLAVE') ? DB_SLAVE : DB_REPLICA);

        if ($dbr->tableExists('related_links')) {
            $tbl_links = $dbr->tableName('related_links');
        } else {
            $result = initiateSettingsForRelatedLinks();

            if (!$result) {
                die('Failed to create table Related Links');
            }
            $tbl_links = $dbr->tableName('related_links');
        }

        $links_id = filter_var($wgRequest->getVal('title'), FILTER_SANITIZE_STRING);

        $fields    = array( 'links_subject', 'links_url' );
        $conds    = array('links_enable'    => 1,'links_id'=> $links_id);
        $opts    = array( 'ORDER BY'        => 'links_order' );
        $result    = $dbr->select($tbl_links, $fields, $conds, 'Database::select', $opts);
        $rows    = $dbr->numRows($result); ?>
</ul>
</div>
</div>
<div class="portlet" id="p-relatedlinks">
<h3>
<?php echo wfMessage('relatedlinks') ?>
[<a href="<?php echo(SkinTemplate::makeSpecialUrl('relatedlinks', 'link_id=' . $links_id))?>"><?php echo wfMessage('edit') ?></a>]
</h3>
<div class="pBody">
<ul>
<?php while ($row = $dbr->fetchObject($result)) {
            ?>
<li id="related-<?php echo $links_id?>"><a href="<?php echo htmlspecialchars($row->links_url)?>" target="_blank"><?php echo $row->links_subject?></a></li>
<?php

        } ?>
<?php
if (!$rows) {
            ?>
<li id="related-<?php echo $links_id?>"><?php echo wfMessage('no_link') ?></li>
<?php

        }

        return true;
    }
}
