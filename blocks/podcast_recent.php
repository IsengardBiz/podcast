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
	
	$untagged_content = FALSE;
	$soundtrackObjects = array();
	
	// Check for dynamic tag filtering
	if ($options[3] == 1 && isset($_GET['tag_id'])) {
		$untagged_content = ($_GET['tag_id'] == 'untagged') ? TRUE : FALSE;
		$options[2] = (int)trim($_GET['tag_id']);
	}
	
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$podcastModule = icms::handler("icms_module")->getByDirname('podcast');
	
	// Get a list of soundtracks filtered by tag
	if (icms_get_module_status("sprockets") && $options[2] || $untagged_content) {
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		$query = "SELECT * FROM " . $podcast_soundtrack_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `soundtrack_id` = `iid`";
		if ($untagged_content) {
			$options[2] = 0;
		}
		$query .= " AND `tid` = '" . $options[2] . "'"
			. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
			. " AND `item` = 'soundtrack'"
			. " AND `online_status` = '1'";
		// Optionally filter by programme as well
		if (intval($options[0])) {
			$query .= " AND source = '" . $options[0] . "'";
		}
		$query .= " ORDER BY `date` DESC LIMIT 0," . $options[1];

		$result = icms::$xoopsDB->query($query);

		if (!$result) 
		{
			echo 'Error: Recent soundtracks block';
			exit;
		}
		else
		{
			$rows = $podcast_soundtrack_handler->convertResultSet($result, TRUE, TRUE);
			foreach ($rows as $key => $row) 
			{
				$soundtrackObjects[$key] = $row;
			}
		}
	}
	// Otherwise just get a list of all soundtracks
	else {
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		$criteria->setLimit($options[1]);
		// Optionally filter by programme
		if (intval($options[0])) {
			$criteria->add(new icms_db_criteria_Item('source', $options[0]));
		}
		$soundtrackObjects = $podcast_soundtrack_handler->getObjects($criteria, TRUE, TRUE);
	}

	// Prepare soundtracks for display
	$soundtrack_list = array();
	foreach ($soundtrackObjects as $key => $object) {
		$soundtrack = array();
		$soundtrack['title'] = $object->getVar('title');
		$soundtrack['date'] = $object->getVar('date');
		
		// Add SEO friendly string to URL
		$soundtrack['itemUrl'] = $object->getItemLink(TRUE);
		$short_url = $object->getVar('short_url');
		if (!empty($short_url))
		{
			$soundtrack['itemUrl'] .= '&amp;title=' . $short_url;
		}
		$soundtrack['itemLink'] = '<a href="' . $soundtrack['itemUrl'] . '">' . $soundtrack['title'] . '</a>';
		$soundtrack_list[$object->getVar('soundtrack_id')] = $soundtrack;
		unset($short_url);
	}
	
	// Assign to template, or unset the block if its empty (prevents block title being displayed)
	if (!empty($soundtrack_list)) {
		$block['soundtracks'] = $soundtrack_list;
	} else {
		unset($block);
	}

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
	$form .= '<td>' . '<input type="text" name="options[]" value="' . $options[1] . '"/></td></tr>';
	
	
	// Optional tagging support - filter block by tag, or enable dynamic tagging
	if (icms_get_module_status("sprockets")) {
		$podcastModule = icms::handler("icms_module")->getByDirname('podcast');
		$sprocketsModule = icms::handler("icms_module")->getByDirname('sprockets');
		include_once(ICMS_ROOT_PATH . '/modules/' . $podcastModule->getVar('dirname') . '/include/common.php');
		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack', $podcastModule->getVar('dirname'), 'podcast');
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
		
		// Get only those tags that contain content from this module
		$criteria = '';
		$relevant_tag_ids = array();
		$criteria = icms_buildCriteria(array('mid' => $podcastModule->getVar('mid')));
		$podcast_module_taglinks = $sprockets_taglink_handler->getObjects($criteria, TRUE, TRUE);
		foreach ($podcast_module_taglinks as $key => $value)
		{
			$relevant_tag_ids[] = $value->getVar('tid');
		}
		$relevant_tag_ids = array_unique($relevant_tag_ids);
		$relevant_tag_ids = '(' . implode(',', $relevant_tag_ids) . ')';
		unset($criteria);

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $relevant_tag_ids, 'IN'));
		$tagList = $sprockets_tag_handler->getList($criteria);

		$tagList = array(0 => _MB_PODCAST_RECENT_ALL) + $tagList;
		$form .= '<tr><td>' . _MB_PODCAST_RECENT_TAG . '</td>';
		// Parameters icms_form_elements_Select: ($caption, $name, $value = null, $size = 1, $multiple = TRUE)
		$form_select = new icms_form_elements_Select('', 'options[2]', $options[2], '1', FALSE);
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
		// Dynamic tag filtering - overrides the tag filter
		$form .= '<tr><td>' . _MB_PODCAST_SOUNDTRACK_DYNAMIC_TAG . '</td>';			
		$form .= '<td><input type="radio" name="options[3]" value="1"';
		if ($options[3] == 1) {
			$form .= ' checked="checked"';
		}
		$form .= '/>' . _MB_PODCAST_SOUNDTRACK_YES;
		$form .= '<input type="radio" name="options[3]" value="0"';
		if ($options[3] == 0) {
			$form .= 'checked="checked"';
		}
		$form .= '/>' . _MB_PODCAST_SOUNDTRACK_NO . '</td></tr>';
	}
	
	
	
	$form .= '</table>';
	return $form;
}