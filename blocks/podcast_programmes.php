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
	$untagged_content = FALSE;
	
	// Check for dynamic tag filtering
	if ($options[2] == 1 && isset($_GET['tag_id'])) {
		$untagged_content = ($_GET['tag_id'] == 'untagged') ? TRUE : FALSE;
		$options[1] = (int)trim($_GET['tag_id']);
	}
	
	$programmeObjects = array();
	$podcastModule = icms::handler("icms_module")->getByDirname('podcast');
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
	include_once(ICMS_ROOT_PATH . '/modules/' . $podcastModule->getVar('dirname') . '/include/common.php');
	$podcast_programme_handler = icms_getModuleHandler('programme', $podcastModule->getVar('dirname'), 'podcast');
	
	if (icms_get_module_status("sprockets"))
	{
		icms_loadLanguageFile("sprockets", "common");
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	}
	
	$programmeList = $programmes = array();
	$criteria = new icms_db_criteria_Compo();
	
	// Sanitise the options as a precaution, since they are used in a manual query string
	$clean_limit = isset($options[0]) ? (int)$options[0] : 0;
	$clean_tag_id = isset($options[1]) ? (int)$options[1] : 0 ;

	// Get a list of programmes filtered by tag
	if (icms_get_module_status("sprockets") && $clean_tag_id || $untagged_content)
	{
		$query = "SELECT * FROM " . $podcast_programme_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `programme_id` = `iid`";
		if ($untagged_content) {
			$clean_tag_id = 0;
		}
		$query .= " AND `tid` = '" . $clean_tag_id . "'"
			. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
			. " AND `item` = 'programme'"
			. " AND `online_status` = '1'"
			. " ORDER BY `date` DESC"
			. " LIMIT 0," . $clean_limit;

		$result = icms::$xoopsDB->query($query);

		if (!$result) 
		{
			echo 'Error: Recent programmes block';
			exit;
		}
		else
		{
			$rows = $podcast_programme_handler->convertResultSet($result, TRUE, TRUE);
			foreach ($rows as $key => $row) 
			{
				$programmeObjects[$key] = $row;
			}
		}
	}
	// Otherwise just get a list of all programmes
	
	else 
	{
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		$criteria->setLimit($clean_limit);
		$programmeObjects = $podcast_programme_handler->getObjects($criteria, TRUE, TRUE);
	}

	// Prepare programme for display
	$programme_list = array();
	foreach ($programmeObjects as $key => $object) {
		$programme = array();
		$programme['title'] = $object->getVar('title');
		$programme['date'] = date(icms_getConfig('date_format', 'podcast'), $object->getVar('date', 'e'));
		
		// Add SEO friendly string to URL
		$programme['itemUrl'] = $object->getItemLink(TRUE);
		$short_url = $object->getVar('short_url');
		if (!empty($short_url))
		{
			$programme['itemUrl'] .= '&amp;title=' . $short_url;
		}
		$programme['itemLink'] = '<a href="' . $programme['itemUrl'] . '">' . $programme['title'] . '</a>';
		$programme_list[$object->getVar('programme_id')] = $programme;
		unset($short_url);
	}
	
	// Assign to template, or unset the block if its empty (prevents block title being displayed)
	if(!empty($programme_list)) {
		$block['programmes'] = $programme_list;
	} else {
		unset($block);
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
	$form .= '<td>' . '<input type="text" name="options[]" value="' . $options[0] . '"/></td></tr>';
	
	// Optional tagging support - filter block by tag, or enable dynamic tagging
	if (icms_get_module_status("sprockets")) {
		$podcastModule = icms::handler("icms_module")->getByDirname('podcast');
		$sprocketsModule = icms::handler("icms_module")->getByDirname('sprockets');
		include_once(ICMS_ROOT_PATH . '/modules/' . $podcastModule->getVar('dirname') . '/include/common.php');
		$podcast_programme_handler = icms_getModuleHandler('programme', $podcastModule->getVar('dirname'), 'podcast');
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
		$form_select = new icms_form_elements_Select('', 'options[1]', $options[1], '1', FALSE);
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
		// Dynamic tag filtering - overrides the tag filter
		$form .= '<tr><td>' . _MB_PODCAST_PROGRAMME_DYNAMIC_TAG . '</td>';			
		$form .= '<td><input type="radio" name="options[2]" value="1"';
		if ($options[2] == 1) {
			$form .= ' checked="checked"';
		}
		$form .= '/>' . _MB_PODCAST_PROGRAMME_YES;
		$form .= '<input type="radio" name="options[2]" value="0"';
		if ($options[2] == 0) {
			$form .= 'checked="checked"';
		}
		$form .= '/>' . _MB_PODCAST_PROGRAMME_NO . '</td></tr>';
	}
		
	$form .= '</table>';
	return $form;
}