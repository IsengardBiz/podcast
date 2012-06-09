<?php
/**
 * Recent podcasts block file
 *
 * This file holds the functions needed for the recent podcasts block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
 * Modified for use in the Podcast module by Madfish
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepare recent podcasts block for display
 *
 * @param array $options
 * @return array 
 */
function podcast_recent_show($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$criteria = new icms_db_criteria_Compo();
	$criteria->setStart(0);
	$criteria->setLimit($options[1]);

	// only include soundtracks that are set online
	$criteria->add(new icms_db_criteria_Item('status', true));

	// optionally filter track listing by programme
	if (intval($options[0])) {
		$criteria->add(new icms_db_criteria_Item('source', $options[0]));
	}

	$criteria->setSort('submission_time');
	$criteria->setOrder('DESC');
	$block['soundtracks'] = $podcast_soundtrack_handler->getObjects($criteria, true, false);
	foreach($block['soundtracks'] as $key => &$value) {
		$value = $value['itemLink'] . ' (' . $value['date'] . ')';
	}
	// also need to consider permissions and status
	return $block;
}

/**
 * Edit recent podcasts block options
 *
 * @param array $options
 * @return string 
 */
function podcast_recent_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');
	$podcast_programme_handler = icms_getModuleHandler('programme',
		basename(dirname(dirname(__FILE__))), 'podcast');

	$form = '<table><tr>';
	// optionally display results from a single programme
	$form .= '<td>' . _MB_PODCAST_PODCAST_RECENT_PROGRAMME . '</td>';
	// Parameters icms_form_elements_Select: ($caption, $name, $value = null, $size = 1, $multiple = false)
	$form_select = new icms_form_elements_Select('', 'options[]', $options[0], '1', false);
	$programme_list = $podcast_programme_handler->getList();
	$programme_list = array(0 => 'All') + $programme_list;
	$form_select->addOptionArray($programme_list);
	$form .= '<td>' . $form_select->render() . '</td></tr>';
	// select number of recent soundtracks to display in the block
	$form .= '<tr><td>' . _MB_PODCAST_PODCAST_RECENT_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[]" value="' . $options[1] . '"/></td>';
	$form .= '</tr></table>';
	return $form;
}