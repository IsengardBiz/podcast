<?php

/**
 * Recent programmes block file
 *
 * This file holds the functions needed for the recent programmes block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
 * Modified for use in the Podcast module by Madfish
 * @version		$Id$
 */

/**
 * Prepare programmes block for display
 *
 * @param array $options
 * @return array 
 */
function podcast_programmes_show($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');
	$podcast_programme_handler = icms_getModuleHandler('programme',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$criteria = new icms_db_criteria_Compo();
	$criteria->setStart(0);
	$criteria->setLimit($options[0]);
	$criteria->setSort('title');
	$criteria->setOrder('ASC');
	// would it be better to get objects as array or toArray() and make use of the itemLink?
	$block['programmes'] = $podcast_programme_handler->getObjects($criteria, true, false);

	// ids are the keys of the array;
	foreach($block['programmes'] as $key => &$value) {
		$url = PODCAST_URL . 'programme.php?programme_id=' . $key;
		if (!empty($value->getVar('short_url')))
		{
			$url .= "&amp;title=" . $value->getVar('short_url');
		}
		
		$value = '<a href="' . $url . '">' . $value->getVar('title', 'e') . '</a>';
	}
	// also need to consider item permissions and status
	return $block;
}

function podcast_programmes_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');

	// select number of recent soundtracks to display in the block
	$form = '<table><tr>';
	$form .= '<tr><td>' . _MB_PODCAST_PODCAST_PROGRAMME_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[]" value="' . $options[0] . '"/></td>';
	$form .= '</tr></table>';
	return $form;
}