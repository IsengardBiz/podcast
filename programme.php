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

// use a naming convention that indicates the source of the content of the variable
$clean_programme_id = isset($_GET['programme_id']) ? intval($_GET['programme_id']) : 0;
$clean_m3u_flag = isset($_GET['m3u_flag']) ? intval($_GET['m3u_flag']) : 0;

$programmeObj = $podcast_programme_handler->get($clean_programme_id);

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

		// buffer some data to avoid repetitive queries
		$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
		$mimetypeObjArray = $system_mimetype_handler->getObjects(null, true);
		$podcast_rights_handler = icms_getModuleHandler('rights', basename(dirname(__FILE__)),
			'podcast');
		$rightsObjArray = $podcast_rights_handler->getObjects(null, true);

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
			$track['rights'] = $rightsObjArray[$track['rights']]->getItemLink();

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
			$rightsObj = $rightsObjArray[$soundtrack->getVar('rights', 'e')];
			$rights = $rightsObj->toArray();
			$track['rights'] = $rights['itemLink'];

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

		// pagination
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('source', $programmeObj->id()));
		$criteria->add(new icms_db_criteria_Item('online_status', true));
		$soundtrack_count = $podcast_soundtrack_handler->getCount($criteria);
		$extra_arg = 'programme_id=' . $programmeObj->id();
		$pagenav = new icms_view_PageNav($soundtrack_count,
			$podcastConfig['number_soundtracks_per_page'], $clean_start, 'start', $extra_arg);
		$icmsTpl->assign('podcast_navbar', $pagenav->renderNav());
	}
} else {
	
	/////////////////////////////////////////////
	////////// DISPLAY PROGRAMME INDEX //////////
	/////////////////////////////////////////////
	
	$icmsTpl->assign('podcast_title', _MD_PODCAST_ALL_PROGRAMMES);
	$icmsTpl->assign('podcast_programme_view', 'multiple');

	// get a list of programmes considering pagination and preference requirements
	$programmeArray = $programmeObjectArray = array();
	$podcast_programme_handler = icms_getModuleHandler('programme',
		basename(dirname(__FILE__)), 'podcast');
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(__FILE__)), 'podcast');
	$criteria = new icms_db_criteria_Compo();
	$criteria->setStart($clean_start);
	$criteria->setLimit($podcastConfig['number_programmes_per_page']); // important for pagination

	switch ($podcastConfig['programmes_sort_preference']) {
		case "0": // sort programmes by title
			$criteria->setSort('title');
			$criteria->setOrder('ASC');
			break;

		case "1": // sort programmes by submission date (ascending)
			$criteria->setSort('submission_time');
			$criteria->setOrder('ASC');
			break;

		case "2": // sort programmes by submission date (descending)
			$criteria->setSort('submission_time');
			$criteria->setOrder('DESC');
			break;
	}

	$programmeObjectArray = $podcast_programme_handler->getObjects($criteria);
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
$icmsTpl->assign('podcast_category_path', _CO_PODCAST_PROGRAMME_PROGRAMMES);

include_once 'footer.php';