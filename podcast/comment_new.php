<?php
/**
 * New comment form
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once 'header.php';
$com_itemid = isset($_GET['com_itemid']) ? intval($_GET['com_itemid']) : 0;
if ($com_itemid > 0) {
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack', basename(dirname(__FILE__)), 'podcast');
	$soundtrackObj = $podcast_soundtrack_handler->get($com_itemid);
	if ($soundtrackObj && !$soundtrackObj->isNew()) {
		$bodytext = $soundtrackObj->getVar('description');
		if ($bodytext != '') {
			$com_replytext = $bodytext;
		}
		$com_replytitle = $soundtrackObj->getVar('title');
		include_once ICMS_ROOT_PATH .'/include/comment_new.php';
	}
}