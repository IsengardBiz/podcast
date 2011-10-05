<?php
/**
 * Common functions used by the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/**
 * Notification lookup function
 *
 * This function is called by the notification process to get an array contaning information
 * about the item for which there is a notification
 *
 * @param string $category category of the notification
 * @param int $item_id id of the item related to this notification
 *
 * @return array containing 'name' and 'url' of the related item
 */
function podcast_notify_iteminfo($category, $item_id) {
	global $icmsModule, $icmsModuleConfig, $icmsConfig;
	if ($category == 'global') {
		$item['name'] = '';
		$item['url'] = '';
		return $item;
	}

	if ($category == 'programme') {

		$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(dirname(__FILE__))), 'podcast');
		$programmeObj = $podcast_programme_handler->get($item_id);
		if ($programmeObj) {
			$item['name'] = $programmeObj->title();
			$item['url'] = ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/programme.php?programme_id=' . intval($item_id);
			return $item;
		} else {
			return null;
		}
	}

	if ($category == 'soundtrack') {

		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
			basename(dirname(dirname(__FILE__))), 'podcast');
		$soundtrackObj = $podcast_soundtrack_handler->get($item_id);
		if ($soundtrackObj) {
			$item['name'] = $soundtrackObj->title();
			$item['url'] = ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/soundtrack.php?soundtrack_id=' . intval($item_id);
			return $item;
		} else {
			return null;
		}
	}
}