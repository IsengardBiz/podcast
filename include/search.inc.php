<?php
/**
 * Search function for the podcast module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Provides search functionality for the Podcast module
 *
 * @param array $queryarray
 * @param string $andor
 * @param int $limit
 * @param int $offset
 * @param int $userid
 * @return array 
 */
function podcast_search($queryarray, $andor, $limit, $offset, $userid) {
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$soundtrackArray = $podcast_soundtrack_handler->getSoundtracksForSearch($queryarray, $andor,
		$limit, $offset, $userid);

	$ret = array();

	foreach ($soundtrackArray as $soundtrack) {
		$item['image'] = "images/soundtrack.png";
		$item['link'] = str_replace(PODCAST_URL, '', $soundtrack['itemUrl']);
		$item['title'] = $soundtrack['title'];
		$item['time'] = strtotime($soundtrack['submission_time']);
		$item['uid'] = $soundtrack['submitter'];
		$ret[] = $item;
		unset($item);
	}
	return $ret;
}