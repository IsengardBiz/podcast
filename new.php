<?php
/**
 * Displays latest podcasts (soundtracks) across all programmes or albums.
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once 'header.php';

$xoopsOption['template_main'] = 'podcast_new.html';

include_once ICMS_ROOT_PATH . '/header.php';

// initialise
$criteria = $last_updated_programme = '';
$soundtrack_object_array = $soundtrack_array = array();
$podcast_programme_handler = icms_getModuleHandler('programme',
	basename(dirname(__FILE__)), 'podcast');
$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
	basename(dirname(__FILE__)), 'podcast');
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// get the most recent XX soundtracks as set in the module preferences
$criteria = new icms_db_criteria_Compo();
$criteria->setStart($clean_start);
$criteria->setLimit($podcastConfig['new_items']); // important for pagination
$criteria->setSort('date');
$criteria->setOrder('DESC');
$criteria->add(new icms_db_criteria_Item('status', true));
$soundtrack_object_array = $podcast_soundtrack_handler->getObjects($criteria);

if (empty($soundtrack_object_array)) {
	echo '<p>Nothing to display.</p>';
} else {
	// prepare buffers to avoid repetitive queries, keys match object ids
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	$mimetypeObjArray = $system_mimetype_handler->getObjects(null, true);
	$podcast_rights_handler = icms_getModuleHandler('rights', basename(dirname(__FILE__)),
			'podcast');
	$rightsObjArray = $podcast_rights_handler->getObjects(null, true);
	$programme_object_array = $podcast_programme_handler->getObjects(null, true);
	$programme_cover_array = array();
	
	// get the path to document root for this ICMS install
	$directory_name = basename(dirname(__FILE__));
	$script_name = getenv("SCRIPT_NAME");
	$document_root = str_replace('modules/' . $directory_name . '/new.php', '',
		$script_name);

	foreach($programme_object_array as $programmeObj) {
		$programme_cover_array[$programmeObj->id()] = $document_root . 'uploads/' . $directory_name
			. '/' . $programmeObj->getVar('cover');
	}

	// isolate the most recent soundtrack to use as the page highlight
	// if not starting from zero (pagination) do not show a feature item
	if ($clean_start == 0) {
		$feature_item_object = $soundtrack_object_array[0];
		$feature_item = $feature_item_object->toArrayWithoutOverrides();

		unset($soundtrack_object_array[0]);

		// prepare feature item cover for display if present
		$programme_cover = $programmeObj = '';
		$programmeObj = $programme_object_array[$feature_item_object->getVar('source', 'e')];
		$programme = $programmeObj->toArray();
		$programme_cover = $programmeObj->getVar('cover');
		if (!empty($programme_cover)) {
			$feature_item['cover_path'] = $programme_cover_array[$programmeObj->getVar('programme_id')];
			$feature_item['cover_width'] = $podcastConfig['screenshot_width'];
			$feature_item['cover_link'] = $programme['itemUrl'];
		}

		// convert rights to human readable, do lookup from buffer
		$feature_item['rights'] = $rightsObjArray[$feature_item['rights']]->getItemLink();

		// convert format to human readable, do lookup from buffer
		$feature_item['format'] = '.' . $mimetypeObjArray[$feature_item['format']]->getVar('extension');

		// convert source to human readable
		$feature_item['source'] = $programme['itemLink'];

		// prepare latest tracks RSS button
		$latest_release_rss_button = '<a href="' . PODCAST_URL . 'rss.php" title="'
			. _CO_PODCAST_PROGRAMME_ENCLOSURES . '">' .
			'<img src="' . PODCAST_IMAGES_URL . 'rss.png" alt="RSS"' . ' /></a>';
		$icmsTpl->assign('podcast_latest_release_rss_button', $latest_release_rss_button);

		// add download link
		$feature_item['download'] = '<a href="' . $feature_item['identifier'] . '" title="'
			. _CO_PODCAST_SOUNDTRACK_DOWNLOAD . '">' .
			'<img src="' . PODCAST_IMAGES_URL . 'download.png" alt="Download soundtrack" /></a>';

		// add streaming link
		$feature_item['streaming'] = '<a href="'
			. $feature_item_object->get_m3u($feature_item['itemUrl'])
			. '" title="' . _CO_PODCAST_SOUNDTRACK_PLAY . '">' .
			'<img src="' . PODCAST_IMAGES_URL . 'stream.png" alt="Stream soundtrack" /></a>';

		$feature_item['feature_item'] = true;

		// check display preferences and unset unwanted fields
		$feature_item = podcast_soundtrack_display_preferences($feature_item);

		$feature_item_array = array();

		// put it in an array to make the template work with single / multiple objects
		$feature_item_array[] = $feature_item;
		$icmsTpl->assign('podcast_soundtrack_view', 'multiple');
		$icmsTpl->assign('podcast_feature', $feature_item_array);
	}

	// convert soundtracks into an array for easy access to data in templates
	if (!empty($soundtrack_object_array)) {
		$icmsTpl->assign('podcast_soundtrack_view', 'multiple');
		
		foreach($soundtrack_object_array as $soundtrack_object) {
			$soundtrack = $soundtrack_object->toArrayWithoutOverrides();

			// convert format to human readable
			$mimetypeObj = $mimetypeObjArray[$soundtrack_object->getVar('format', 'e')];
			$soundtrack['format'] = '.' . $mimetypeObj->getVar('extension');

			// convert rights to human readable, lookup value from buffer
			$soundtrack['rights'] = $rightsObjArray[$soundtrack['rights']]->getItemLink();

			// convert source to human readable, lookup value from buffer
			$soundtrack['source'] = $programme_object_array[$soundtrack['source']]->getItemLink();

			// prepare cover - if you wanted to have big covers for these, change thumbnail_width
			// to screenshot_width
			if (!empty($programme_cover_array[$soundtrack_object->getVar('source', 'e')])) {
				$soundtrack['cover_path'] = $programme_cover_array[$soundtrack_object->getVar('source', 'e')];
				$soundtrack['cover_width'] = $podcastConfig['thumbnail_width'];
				$soundtrack['cover_link'] = $soundtrack['itemUrl'];
			}

			// add download link
			$soundtrack['download'] = '<a href="' . $soundtrack['identifier'] . '" title="'
				. _CO_PODCAST_SOUNDTRACK_DOWNLOAD . '">' .
				'<img src="' . PODCAST_IMAGES_URL . 'download.png" alt="Download soundtrack" /></a>';

			// add streaming link
			$soundtrack['streaming'] = '<a href="'
				. $soundtrack_object->get_m3u($soundtrack['itemUrl']) . '" title="'
				. _CO_PODCAST_SOUNDTRACK_PLAY . '">' .
				'<img src="' . PODCAST_IMAGES_URL . 'stream.png" alt="Stream soundtrack" /></a>';

			// check display preferences and unset unwanted fields
			$soundtrack = podcast_soundtrack_display_preferences($soundtrack);

			$soundtrack_array[] = $soundtrack;
		}
		$icmsTpl->assign('podcast_programme_soundtracks', $soundtrack_array);
	}

	// enable RSS feed autodiscovery
	global $xoTheme;
	$rss_link = PODCAST_URL . 'rss.php';
	$rss_attributes = array('type' => 'application/rss+xml', 'title' => 'RSS');
	$xoTheme->addLink('alternate', $rss_link, $rss_attributes);

	// pagination
	$soundtrack_count = $podcast_soundtrack_handler->getCount();
	$pagenav = new icms_view_PageNav($soundtrack_count, $podcastConfig['new_items'], $clean_start, 'start');
	$icmsTpl->assign('podcast_navbar', $pagenav->renderNav());
}

// assign data to templates;
$icmsTpl->assign('podcast_new_compact_view', $podcastConfig['new_view_mode']);
$icmsTpl->assign('podcast_module_home', podcast_getModuleName(true, true));
$icmsTpl->assign('podcast_display_breadcrumb', $podcastConfig['display_breadcrumb']);

include_once 'footer.php';