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
function podcast_search($queryarray, $andor, $limit, $offset = 0, $userid = 0)
{
	global $icmsConfigSearch;
	
	$soundtracksArray = $ret = array();
	$count = $number_to_process = $soundtracks_left = '';
	$podcastUrl = ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__))) . '/';
	
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$soundtracksArray = $podcast_soundtrack_handler->getSoundtracksForSearch($queryarray, $andor,
		$limit, $offset, $userid);
	
	// Count the number of records
	$count = count($soundtracksArray);
	
	// The number of records actually containing item objects is <= $limit, the rest are padding
	$soundtracks_left = ($count - ($offset + $icmsConfigSearch['search_per_page']));
	if ($soundtracks_left < 0) {
		$number_to_process = $icmsConfigSearch['search_per_page'] + $soundtracks_left; // $soundtracks_left is negative
	} else {
		$number_to_process = $icmsConfigSearch['search_per_page'];
	}
	
	// Process the actual soundtracks (not the padding)
	for ($i = 0; $i < $number_to_process; $i++) {
		if (is_object($soundtracksArray[$i])) { // Required to prevent crashing on profile view
			$item['image'] = "images/soundtrack.png";
			$item['link'] = $soundtracksArray[$i]->getItemLink(TRUE);
			$item['title'] = $soundtracksArray[$i]->getVar("title");
			$item['time'] = $soundtracksArray[$i]->getVar("submission_time", "e");
			$item['uid'] = $soundtracksArray[$i]->getVar('submitter', 'e');
			$ret[] = $item;
			unset($item);
		}
	}
	
	if ($limit == 0) {
		// Restore the padding (required for 'hits' information and pagination controls). The offset
		// must be padded to the left of the results, and the remainder to the right or else the search
		// pagination controls will display the wrong results (which will all be empty).
		// Left padding = -($limit + $offset)
		$ret = array_pad($ret, -($offset + $number_to_process), 1);

		// Right padding = $count
		$ret = array_pad($ret, $count, 1);
	}
	
	return $ret;
}