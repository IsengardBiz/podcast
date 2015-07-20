<?php
/**
 * Programme index page - display or stream a single programme (all its soundtracks), or a list of all programmes
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once 'header.php';

$xoopsOption['template_main'] = 'podcast_programme.html';

include_once ICMS_ROOT_PATH . '/header.php'; // why is including this a problem?
$podcast_programme_handler = icms_getModuleHandler('programme', 
	basename(dirname(__FILE__)), 'podcast');

// initialise
$clean_programme = $clean_short_url = $sort_order = '';

$clean_programme_id = isset($_GET['programme_id']) ? intval($_GET['programme_id']) : 0;
$clean_m3u_flag = isset($_GET['m3u_flag']) ? intval($_GET['m3u_flag']) : 0;
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

$programmeObj = $podcast_programme_handler->get($clean_programme_id);

// Prepare buffers to reduce query load
$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
$mimetypeObjArray = $system_mimetype_handler->getObjects(null, true);
if (icms_get_module_status("sprockets")) {
	
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
	icms_loadLanguageFile("sprockets", "common");

	// Prepare rights
	$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');
	$rightsObjArray = $sprockets_rights_handler->getObjects(null, true);

	// Prepare tags
	$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
	$sprockets_tag_buffer = $sprockets_tag_handler->getTagBuffer(TRUE);
}

// check pagination
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : 0;

/////////////////////////////////////////////////////
////////// DISPLAY OR STREAM ONE PROGRAMME //////////
/////////////////////////////////////////////////////

if ($programmeObj && !$programmeObj->isNew()) {
	
	///////////////////////////////////////////////////////////////////////////////////////////////
	////////// Stream the soundtrack by offering the user's browser an m3u playlist file //////////
	///////////////////////////////////////////////////////////////////////////////////////////////
	
	if ($clean_m3u_flag == 1) {
		$playlist = '';
		$soundtrackArray = '';

		// get a list of soundtracks in this programme
		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
			basename(dirname(__FILE__)), 'podcast');
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('source', $programmeObj->id()));
		$criteria->add(new icms_db_criteria_Item('online_status', true));

		// set the soundtrack sort order for this programme
		$sort_order = $programmeObj->getVar('sort_order', 'e');
		switch ($sort_order) {
			case "0":
				$criteria->setSort('date');
				$criteria->setOrder('DESC');
				break;
			
			case "1":
				$criteria->setSort('date');
				$criteria->setOrder('ASC');
				break;
			
			case "2":
				$criteria->setSort('title');
				$criteria->setOrder('ASC');
				break;
			
			case "3":
				$criteria->setSort('title');
				$criteria->setOrder('DESC');
				break;
		}

		$soundtrackArray = $podcast_soundtrack_handler->getObjects($criteria, false, false);

		// build a playlist from their identifiers
		foreach($soundtrackArray as $soundtrack) {
			$playlist .= $soundtrack['identifier'] . "\r\n";
		}

		// send the playlist headers to the browser, followed by soundtrack URLs
		if (!empty ($playlist)) {
			// the iso-8859-1 charset is standard for m3u
			// do NOT break this line!
			header('Content-Type: audio/x-mpegurl audio/mpeg-url application/x-winamp-playlist audio/scpls audio/x-scpls; charset=iso-8859-1');
			header("Content-Disposition:inline;filename=stream_soundtrack.m3u");

			// less widely recognised m3u8 alternative playlist for utf-8:
			// header ('Content-Type: audio/x-mpegurl audio/mpeg-url application/x-winamp-playlist audio/scpls audio/x-scpls; charset=utf-8');
			// header("Content-Disposition:inline;filename=stream_soundtrack.m3u8");
			echo $playlist;
			exit();
		}
	} else {
		
		////////////////////////////////////////////////
		////////// Display a single programme //////////
		////////////////////////////////////////////////
		
		// Update hit counter
		if (!icms_userIsAdmin(icms::$module->getVar('dirname')))
		{
			$podcast_programme_handler->updateCounter($programmeObj);
		}

		$programme = $programmeObj->toArray();
		$programme['counter'] = $programme['counter'] + 1; // for accuracy of user-side data

		$icmsTpl->assign('podcast_programme_view', 'single');
		$icmsTpl->assign('podcast_soundtrack_view', 'multiple');

		// set the programme view mode (compact vs extended)
		$programme['compact_view'] = $programmeObj->getVar('compact_view', 'e');

		// get relative path to document root for this ICMS install
		$directory_name = basename(dirname(__FILE__));
		$script_name = getenv("SCRIPT_NAME");
		$document_root = str_replace('modules/' . $directory_name . '/programme.php', '',
			$script_name);

		// prepare cover for display, dynamically resized by smarty plugin according to
		// screenshot_size preference
		$programme_cover = '';

		$programme_cover = $programmeObj->getVar('cover');
		if (!empty($programme_cover)) {
			$programme['cover_path'] = $document_root . 'uploads/' . $directory_name . '/'
				. $programmeObj->getVar('cover');
			$programme['cover_width'] = $podcastConfig['screenshot_width'];
		}

		// prepare the RSS autodiscovery link, which is inserted in the module header
		global $xoTheme;
		$rss_link = PODCAST_URL . 'rss.php?programme_id=' . $programmeObj->id();
		$rss_attributes = array('type' => 'application/rss+xml', 'title' => 'RSS');
		$xoTheme->addLink('alternate', $rss_link, $rss_attributes);

		// generating meta information for this page
		$icms_metagen = new icms_ipf_Metagen($programmeObj->getVar('title'),
			$programmeObj->getVar('meta_keywords','n'), $programmeObj->getVar('meta_description', 'n'));
		$icms_metagen->createMetaTags();

		// display a list of soundtracks in this programme, considering pagination and preferences
		$soundtrack_array = array();
		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
			basename(dirname(__FILE__)), 'podcast');
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('source', $programmeObj->id()));
		$criteria->add(new icms_db_criteria_Item('online_status', true));

		// count the soundtracks before proceed
		$soundtrack_count = $podcast_soundtrack_handler->getCount($criteria);
		$programme['track_count'] = $soundtrack_count;

		// check display preferences and unset unwanted fields
		$programme = podcast_programme_display_preferences($programme);
		
		// Optional tagging support
		if (icms_get_module_status("sprockets")) {
			$programme_tag_array = $sprockets_taglink_handler->getTagsForObject($programme['programme_id'],
					$podcast_programme_handler, $label_type = '0');
			foreach ($programme_tag_array as $key => $value) {
				$programme['tags'][$value] = '<a href="' . PODCAST_URL . 'programme.php?tag_id='
						. $value . '">' . $sprockets_tag_buffer[$value]->getVar('title') . '</a>';
			}
			$programme['tags'] = implode(', ', $programme['tags']);
		}

		// retrieve the soundtracks
		$playlist = '';
		$criteria->setStart($clean_start);
		$criteria->setLimit($podcastConfig['number_soundtracks_per_page']);
		
		// set the sort order for this programme
		$sort_order = $programmeObj->getVar('sort_order', 'e');
		switch ($sort_order) {
			case "0":
				$criteria->setSort('date');
				$criteria->setOrder('DESC');
				break;
			
			case "1":
				$criteria->setSort('date');
				$criteria->setOrder('ASC');
				break;
			
			case "2":
				$criteria->setSort('title');
				$criteria->setOrder('ASC');
				break;
			
			case "3":
				$criteria->setSort('title');
				$criteria->setOrder('DESC');
				break;
		}
		
		$soundtrack_objects = $podcast_soundtrack_handler->getObjects($criteria);
		unset($criteria);
		
		// prepare the play all button (only if there are some soundtracks)
		if (!empty($soundtrack_objects))
		{
			$programme['play_all_button'] = $programmeObj->get_play_all_button();
		}

		// prepare this programme's soundtracks for display
		foreach($soundtrack_objects as $soundtrack) {

			// convert soundtrack to array for easy assignment of data to templates
			// but draw values from buffers to reduce DB queries associated with getVar() overrides
			$track = '';
			$track = $soundtrack->toArrayWithoutOverrides();
			$track['source'] = $programme['itemLink'];
			$track['format'] = $mimetypeObjArray[$track['format']]->getVar('extension');
			if (icms_get_module_status("sprockets"))
			{
				$track['rights'] = $rightsObjArray[$track['rights']]->getItemLink();
			} else {
				unset($track['rights']);
			}

			// add download link
			$track['download'] = '<a href="' . $track['identifier'] . '" title="'
				. _CO_PODCAST_SOUNDTRACK_DOWNLOAD . '">' .
				'<img src="' . PODCAST_IMAGES_URL . 'download.png" alt="Download soundtrack" /></a>';

			// add streaming link
			$track['streaming'] = '<a href="' . $soundtrack->get_m3u($track['itemUrl'])
				. '" title="' . _CO_PODCAST_SOUNDTRACK_PLAY . '">' .
				'<img src="' . PODCAST_IMAGES_URL . 'stream.png" alt="Stream soundtrack" /></a>';

			// convert to format to human readable
			$mimetypeObj = $mimetypeObjArray[$soundtrack->getVar('format', 'e')];
			$track['format'] = '.' . $mimetypeObj->getVar('extension');

			// convert rights to human readable
			if (icms_get_module_status("sprockets"))
			{
				$rightsObj = $rightsObjArray[$soundtrack->getVar('rights', 'e')];
				$rights = $rightsObj->toArray();
				$track['rights'] = $rights['itemLink'];
			} else {
				unset($track['rights']);
			}

			// unset unwanted / uneeded fields
			unset($track['source']);
			$track = podcast_soundtrack_display_preferences($track);

			// add to an array for user side display
			$soundtrack_array[] = $track;
		}

		// assign programme and soundtracks to template
		$icmsTpl->assign('podcast_programme', $programme);
		$icmsTpl->assign('podcast_programme_rss_button', $programmeObj->get_rss_button());
		$icmsTpl->assign('podcast_programme_soundtracks', $soundtrack_array);
		$icmsTpl->assign('podcast_programme_tags', $programme['tags']);

		// pagination
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('source', $programmeObj->id()));
		$criteria->add(new icms_db_criteria_Item('online_status', true));
		$soundtrack_count = $podcast_soundtrack_handler->getCount($criteria);
		$extra_arg = 'programme_id=' . $programmeObj->id();
		$pagenav = new icms_view_PageNav($soundtrack_count,
			$podcastConfig['number_soundtracks_per_page'], $clean_start, 'start', $extra_arg);
		$icmsTpl->assign('podcast_navbar', $pagenav->renderNav());
		
		// Breadcrumb
		$icmsTpl->assign('podcast_category_path', '<a href="programme.php">' 
					. _CO_PODCAST_PROGRAMME_PROGRAMMES . '</a>');
	}
} else {
	
	/////////////////////////////////////////////
	////////// DISPLAY PROGRAMME INDEX //////////
	/////////////////////////////////////////////
	
	$icmsTpl->assign('podcast_title', _MD_PODCAST_ALL_PROGRAMMES);
	$icmsTpl->assign('podcast_programme_view', 'multiple');
	
	// Initialise
	$programmeArray = $programmeObjectArray = array();
	$podcast_programme_handler = icms_getModuleHandler('programme', basename(dirname(__FILE__)),
			'podcast');
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack', basename(dirname(__FILE__)),
			'podcast');
	$podcastModule = icms::handler("icms_module")->getByDirname("podcast");
	
	// Optional tagging support (only if Sprockets module installed)
	if (icms_get_module_status("sprockets")) {

		// Append the tag to the breadcrumb title
		if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0)) {
			$podcast_tag_name = $sprockets_tag_buffer[$clean_tag_id]->getVar('title');
			$icmsTpl->assign('podcast_tag_name', $podcast_tag_name);
			$icmsTpl->assign('podcast_category_path', '<a href="programme.php">' 
					. _CO_PODCAST_PROGRAMME_PROGRAMMES . '</a> &gt; ' 
					. $sprockets_tag_buffer[$clean_tag_id]->getVar('title'));
		} elseif ($untagged_content) {
			$icmsTpl->assign('podcast_tag_name', _CO_PODCAST_PROGRAMME_UNTAGGED);
			$icmsTpl->assign('podcast_category_path', '<a href="programme.php">' 
					. _CO_PODCAST_PROGRAMME_PROGRAMMES . '</a> &gt; ' 
					. _CO_PODCAST_PROGRAMME_UNTAGGED);
		}
		
		// Prepare a tag select box
		if (icms::$module->config['podcast_select_box'] == TRUE) {
			if ($untagged_content) {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('programme.php', 
						'untagged', _CO_PODCAST_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'programme', TRUE);
			} else {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('programme.php', 
						$clean_tag_id, _CO_PODCAST_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'programme', TRUE);
			}
			$icmsTpl->assign('podcast_select_box', $tag_select_box);
		}
	}
	
	// Retrieve programmes for a given tag
	if (($clean_tag_id || $untagged_content) && icms_get_module_status("sprockets")) {
		// Get a count for pagination purposes
		$programme_count = $podcast_programme_handler->getProgrammeCountForTag($clean_tag_id);

		// Retrieve the objects
		$programmeObjectArray = $podcast_programme_handler->getProgrammesForTag($clean_tag_id, 
				$programme_count, $clean_start);
		$icmsTpl->assign('podcast_programme_array', $programmeObjectArray);

		// Pagination control - adust for tag (and label_type), if present
		if ($clean_tag_id) {
			$extra_arg = 'tag_id=' . $clean_tag_id;
		}
		else {
			$extra_arg = 'tag_id=' . 'untagged';
		}
		$pagenav = new icms_view_PageNav($programme_count, 
			icms::$module->config['number_programmes_per_page'], $clean_start, 'start', $extra_arg);
		$icmsTpl->assign('podcast_navbar', $pagenav->renderNav());
	} else {
		// Get an untagged list of programmes considering pagination and preference requirements
		$criteria = new icms_db_criteria_Compo();
		$criteria->setStart($clean_start);
		$criteria->setLimit($podcastConfig['number_programmes_per_page']); // important for pagination
		switch ($podcastConfig['programmes_sort_preference']) {
			case "0": // sort programmes by title
				$criteria->setSort('title');
				$criteria->setOrder('ASC');
				break;

			case "1": // sort programmes by date (ascending)
				$criteria->setSort('date');
				$criteria->setOrder('ASC');
				break;

			case "2": // sort programmes by date (descending)
				$criteria->setSort('date');
				$criteria->setOrder('DESC');
				break;
		}
		$programmeObjectArray = $podcast_programme_handler->getObjects($criteria);
		$icmsTpl->assign('podcast_category_path', _CO_PODCAST_PROGRAMME_PROGRAMMES);
	}
	
	if (icms_get_module_status("sprockets")) {
		$programme_ids = array();
		foreach ($programmeObjectArray as $progObj) {
			$programme_ids[] = $progObj->getVar('programme_id');
			$programme_tag_array = $sprockets_taglink_handler->getTagsForObjects($programme_ids, 'programme');
		}
	}
	
	$podcastModule = icms_getModuleInfo(basename(dirname(__FILE__)));
	foreach($programmeObjectArray as $programmeObject) {
		// convert object to array for easy template assignment
		$programme = $programmeObject->toArray();

		// prepare cover for display, dynamically resized by smarty plugin according to
		// screenshot_size preference - NB: requires absolute path from *web root* folder
		// (not the full absolute path)
		$programme_cover = '';

		$programme_cover = $programmeObject->getVar('cover');
		if (!empty($programme_cover)) {
			$programme['cover_path'] = '/uploads/' . $podcastModule->getVar('dirname') . '/'
				. $programmeObject->getVar('cover');
			$programme['cover_width'] = $podcastConfig['thumbnail_width'];
			$programme['cover_link'] = PODCAST_URL . 'programme.php?programme_id='
				. $programmeObject->id();
		}

		// prepare the RSS button
		$programme['rss_button'] = $programmeObject->get_rss_button();

		// calculate the number of soundtracks in the podcast
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('source', $programmeObject->id()));
		$criteria->add(new icms_db_criteria_Item('online_status', true));
		$programme['track_count'] = $podcast_soundtrack_handler->getCount($criteria);
		
		// prepare the play all button (only if there are some soundtracks)
		if ($programme['track_count'] > 0)
		{
			$programme['play_all_button'] = $programmeObject->get_play_all_button();
		}

		// check display preferences and unset unwanted fields
		$programme = podcast_programme_display_preferences($programme);
		
		// Prepare the tags, need an array of programme iids
		if (icms_get_module_status("sprockets")) {
			$programme['tags'] = $programme_tag_array[$programme['programme_id']];
			foreach ($programme['tags'] as $key => &$tag) {
				$tag_id = $tag;
				$tag = '<a href="' . PODCAST_URL . 'programme.php?tag_id='
					. $tag_id;
				if ($sprockets_tag_buffer[$tag_id]->getVar('short_url')) {
					$tag .= '&amp;title=' . $sprockets_tag_buffer[$tag_id]->getVar('short_url');
				}
				$tag .= '">' . $sprockets_tag_buffer[$tag_id]->getVar('title') . '</a>';
			}
			$programme['tags'] = implode(', ', $programme['tags']);
		}

		// assign the programme to an array for user side display
		$programmeArray[] = $programme;

		// pagination
		$programme_count = $podcast_programme_handler->getCount();
		$pagenav = new icms_view_PageNav($programme_count, $podcastConfig['number_programmes_per_page'],
			$clean_start, 'start');
		$icmsTpl->assign('podcast_navbar', $pagenav->renderNav());
	}
	$icmsTpl->assign('podcast_programme_array', $programmeArray);
}

$icmsTpl->assign('podcast_module_home', podcast_getModuleName(true, true));
$icmsTpl->assign('podcast_display_breadcrumb', $podcastConfig['display_breadcrumb']);

include_once 'footer.php';