<?php
/**
 * Soundtrack index page - display, download or stream a single soundtrack, or a table of all soundtracks
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once 'header.php';

$xoopsOption['template_main'] = 'podcast_soundtrack.html';
include_once ICMS_ROOT_PATH . '/header.php';

$clean_m3u_flag = '';
$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
	basename(dirname(__FILE__)), 'podcast');

/** Use a naming convention that indicates the source of the content of the variable */
$clean_soundtrack_id = isset($_GET['soundtrack_id']) ? intval($_GET['soundtrack_id']) : 0 ;
$clean_m3u_flag = isset($_GET['m3u_flag']) ? intval($_GET['m3u_flag']) : 0;

$soundtrackObj = $podcast_soundtrack_handler->get($clean_soundtrack_id);

// check if the soundtrack status is set as offline and torch it if so
if ($soundtrackObj->getVar('status') == false) {
	unset($soundtrackObj);
}

// display or stream a single soundtrack
if ($soundtrackObj && !$soundtrackObj->isNew()) {
	// stream the soundtrack by offering the user's browser an m3u playlist file
	if ($clean_m3u_flag == 1) {
		$identifier = '';
		$identifier = $soundtrackObj->getVar('identifier');
		if (!empty ($identifier)) {

			// send playlist headers to the browser, followed by the audio file URL as contents
			// the iso-8859-1 charset is standard for m3u
			header('Content-Type: audio/x-mpegurl audio/mpeg-url application/x-winamp-playlist audio/scpls audio/x-scpls; charset=iso-8859-1');
			header("Content-Disposition:inline;filename=stream_soundtrack.m3u");

			// less widely recognised m3u8 alternative playlist format for utf-8:
			// header ('Content-Type: audio/x-mpegurl audio/mpeg-url application/x-winamp-playlist audio/scpls audio/x-scpls; charset=utf-8');
			// header("Content-Disposition:inline;filename=stream_soundtrack.m3u8");

			echo $identifier;
			exit();
		}
	} else { // display a single soundtrack
		
		// Update hit counter
		if (!icms_userIsAdmin(icms::$module->getVar('dirname')))
		{
			$podcast_soundtrack_handler->updateCounter($soundtrackObj);
		}

		// prepare soundtrack data for template
		$soundtrackArray = $soundtrackObj->toArray();
		$soundtrackArray['counter'] = $soundtrackArray['counter'] +1;
		$icmsTpl->assign('podcast_soundtrack_view', 'single');

		// add download link
		$soundtrackArray['download'] = '<a href="' . $soundtrackArray['identifier'] . '" title="'
			. _CO_PODCAST_SOUNDTRACK_DOWNLOAD . '"><img src="' . PODCAST_IMAGES_URL
			. 'download.png" alt="Download soundtrack" /></a>';

		// add streaming link
		$soundtrackArray['streaming'] = '<a href="'
			. $soundtrackObj->get_m3u($soundtrackArray['itemUrl'])
			. '" title="' . _CO_PODCAST_SOUNDTRACK_PLAY . '">'
			. '<img src="' . PODCAST_IMAGES_URL . 'stream.png" alt="Stream soundtrack" /></a>';

		// get relative path to document root for this ICMS install
		$directory_name = basename(dirname(__FILE__));
		$script_name = getenv("SCRIPT_NAME");
		$document_root = str_replace('modules/' . $directory_name . '/soundtrack.php', '',
			$script_name);

		// prepare cover for display, argument toggles image width - screenshot vs thumbnail
		$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(__FILE__)), 'podcast');
		$programme_id = $soundtrackObj->getvar('source', false);
		$programmeObj = $podcast_programme_handler->get($programme_id);
		$programme = $programmeObj->toArray();
		$programme_cover = $programmeObj->getVar('cover');
		if (!empty($programme_cover)) {
			$soundtrackArray['cover_path'] = $document_root . 'uploads/' . $directory_name . '/'
				. $programmeObj->getVar('cover');
			$soundtrackArray['cover_width'] = $podcastConfig['screenshot_width'];
			$soundtrackArray['cover_link'] = $programme['itemUrl'];
		}

		// change some fields to human readable
		$soundtrackArray['language'] = $soundtrackObj->getVar('language', 's');
		$soundtrackArray['format'] = $soundtrackObj->format();
		$soundtrackArray['rights'] = $soundtrackObj->rights();
		$soundtrackArray['source'] = $programme['itemLink'];

		// unset unwanted / uneeded fields
		$soundtrackArray = podcast_soundtrack_display_preferences($soundtrackArray);

		// assign to template
		$icmsTpl->assign('podcast_soundtrack', $soundtrackArray);

		// comments
		if ($podcastConfig['com_rule']) {
			$icmsTpl->assign('podcast_soundtrack_comment', true);
			include_once ICMS_ROOT_PATH . '/include/comment_view.php';
		}
		// useless assignment

		// generating meta information for this page
		$icms_metagen = new icms_ipf_Metagen($soundtrackObj->getVar('title'),
			$soundtrackObj->getVar('meta_keywords','n'),
			$soundtrackObj->getVar('meta_description', 'n'));
		$icms_metagen->createMetaTags();
	}
} else {
	// list soundtracks
	// prepare buffers to minimise queries
	$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(__FILE__)), 'podcast');
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	$sources = $podcast_programme_handler->getObjects(null, true);
	$formats = $system_mimetype_handler->getObjects(null, true);

	$icmsTpl->assign('podcast_title', _MD_PODCAST_ALL_SOUNDTRACKS);

	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('status', true));
	$objectTable = new icms_ipf_view_Table($podcast_soundtrack_handler, $criteria, array(), true);
	$objectTable->isForUserSide();
	$objectTable->addColumn(new icms_ipf_view_Column('date'));
	$objectTable->addColumn(new icms_ipf_view_Column('title'));
	$objectTable->addColumn(new icms_ipf_view_Column('source', _GLOBAL_LEFT, false,
		'source', $sources));
	$objectTable->addColumn(new icms_ipf_view_Column('format', _GLOBAL_LEFT, false,
		'format', $formats));
	$objectTable->addFilter('source', 'source_filter');
	$objectTable->addFilter('rights', 'rights_filter');
	$objectTable->setDefaultSort('date');
	$objectTable->setDefaultOrder('DESC');
	$objectTable->addQuickSearch('title');
	$icmsTpl->assign('podcast_soundtrack_table', $objectTable->fetch());
}

$icmsTpl->assign('podcast_module_home', podcast_getModuleName(true, true));
$icmsTpl->assign('podcast_display_breadcrumb', $podcastConfig['display_breadcrumb']);
$icmsTpl->assign('podcast_category_path', _CO_PODCAST_PROGRAMME_SOUNDTRACKS);

include_once 'footer.php';