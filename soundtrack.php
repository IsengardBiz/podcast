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
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

$soundtrackObj = $podcast_soundtrack_handler->get($clean_soundtrack_id);

// check if the soundtrack status is set as offline and torch it if so
if ($soundtrackObj->getVar('online_status', 'e') == false) {
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
		
		// Optional tagging support (only if Sprockets installed)
		if (icms_get_module_status("sprockets")) {
			$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
			$sprockets_tag_buffer = $sprockets_tag_handler->getTagBuffer(TRUE);
			$tag_ids = $sprockets_taglink_handler->getTagsForObject($soundtrackArray['soundtrack_id'], 
					$podcast_soundtrack_handler);
			foreach ($tag_ids as $tag) {
				$tag_id = $tag;
				$short_url = $sprockets_tag_buffer[$tag_id]->getVar('short_url');
				$tag = '<a href="' . PODCAST_URL . 'soundtrack.php?tag_id=' . $tag_id;
				if (!empty($short_url)) {
					$tag .= '&amp;title=' . $short_url;
				}
				$tag .= '">' . $sprockets_tag_buffer[$tag_id]->getVar('title') . '</a>';
				$soundtrackArray['tags'][] = $tag;
				unset($tag_id);
			}
			$soundtrackArray['tags'] = implode(', ', $soundtrackArray['tags']);
		}
echo $soundtrackArray['tags'];
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
		if (icms_get_module_status("sprockets"))
		{
			$soundtrackArray['rights'] = $soundtrackObj->rights();
		} else {
			unset($soundtrackArray['rights']);
		}
		$soundtrackArray['source'] = $programme['itemLink'];
		
		////////////////////////////////////////////////////////////////////
		//////////////////// OPTIONAL JW PLAYER SUPPORT ////////////////////
		////////////////////////////////////////////////////////////////////
		
		// JW Player is an inline media player, which must be downloaded and installed separately. 
		// See the Podcast manual for details (it's in the extras folder).
		$media = $jw_player = $jw_player_enabled = $player_width = $player_height = $image = $valid_mimetype = FALSE;
		
		// Use secondary media as the 'playable' one, but drop back to the primary if there is only 
		// one media resource.
		$media = $soundtrackObj->getVar('inline_identifier', 'e');
		if (!$media) {
			$media = $soundtrackObj->getVar('identifier', 'e');
		}
		
		// Check the media file is compatible with JW Player. Acceptable mimetypes are:
		// Video: MP4 (H.264/AAC), FLV (VP6/MP3), WebM (VP8/Vorbis)
		// Audio: AAC, MP3, Vorbis
		// This is a simple check based on extension. It would be more robust to use fileinfo(), 
		// but that would create problems (or at least, delays) with remotely hosted files.
		$valid_extensions = array('mp4', 'flv', 'webm', 'aac', 'mp3', 'ogg');
		$extension = substr($media, strrpos($media, '.')+1);
		if (in_array($extension, $valid_extensions)) {
			$valid_mimetype = TRUE;
		}
		
		$jw_player = is_dir(XOOPS_ROOT_PATH . '/jwplayer');
		$jw_player_enabled = icms_getConfig('enable_jw_player', 'podcast');
		$player_width = icms_getConfig('jw_player_width', 'podcast');
		$player_height = icms_getConfig('jw_player_height', 'podcast');
		$image = $soundtrackObj->getVar('poster_image', 'e');
		$poster = $soundtrackObj->getImageDir(FALSE) . $image;
		
		if ($media && $valid_mimetype && $jw_player && $jw_player_enabled)
		{
			// Add JW Player script to module header. You should also create key.js, containing
			// your JWPlayer key, eg. <script>jwplayer.key="yourKeyGoesHere"</script> and put it
			// in the jwplayer directory referenced below
			global $xoTheme;
			$xoTheme->addScript(ICMS_URL . '/jwplayer/jwplayer.js');
			$xoTheme->addScript(ICMS_URL . '/jwplayer/key.js');
			
			// If there is no poster image AND the file is audio only, display player in audio mode
			// This prevents a huge ugly black box being displayed for which there is no content.
			if (!$image) {
				if ($extension == 'aac' || $extension == 'mp3' || $extension == 'ogg') {
					$player_width = 320;
					$player_height = 30;
				}
			}
			
			// Add player code to template
			$soundtrackArray['jw_player'] = "<div id='my-video'></div>
					<script type='text/javascript'>
						jwplayer('my-video').setup({
							file: '" . $media . "',
							width: '" . $player_width . "',
							height: '" . $player_height . "'";
			if ($image) {
				$soundtrackArray['jw_player'] .= ", image:'" . $poster . "'";
			}
			$soundtrackArray['jw_player'] .= "});
				</script>";
			
			// Flag player enabled in template
			$soundtrackArray['video_enabled'] = TRUE;
		} else {
			$soundtrackArray['video_enabled'] = FALSE;
		}
		
		/////////////////////////////////////////////////////////////////////
		//////////////////// End JW Player configuration ////////////////////
		/////////////////////////////////////////////////////////////////////

		// unset unwanted / unneeded fields
		$soundtrackArray = podcast_soundtrack_display_preferences($soundtrackArray);

		// assign to template
		$icmsTpl->assign('podcast_soundtrack', $soundtrackArray);

		// comments
		if ($podcastConfig['com_rule']) {
			$icmsTpl->assign('podcast_soundtrack_comment', true);
			include_once ICMS_ROOT_PATH . '/include/comment_view.php';
		}

		// generating meta information for this page
		$icms_metagen = new icms_ipf_Metagen($soundtrackObj->getVar('title'),
			$soundtrackObj->getVar('meta_keywords','n'),
			$soundtrackObj->getVar('meta_description', 'n'));
		$icms_metagen->createMetaTags();
	}
} else {
	// List soundtracks

	// Optional tagging support (only if Sprockets module installed)
	if (icms_get_module_status("sprockets")) {
		icms_loadLanguageFile("sprockets", "common");
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
				'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
				$sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_tag_buffer = $sprockets_tag_handler->getTagBuffer(TRUE);
		
		// Display a tag select box
		if (icms::$module->config['podcast_select_box'] == TRUE) {
			if ($untagged_content) {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('soundtrack.php', 
						'untagged', _CO_PODCAST_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'soundtrack', TRUE);
			} else {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('soundtrack.php', 
						$clean_tag_id, _CO_PODCAST_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'soundtrack', TRUE);
			}
			
			// Append the tag to the breadcrumb title
			if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0)) {
				$podcast_tag_name = $sprockets_tag_buffer[$clean_tag_id]->getVar('title');
				$icmsTpl->assign('podcast_tag_name', $podcast_tag_name);
				$icmsTpl->assign('podcast_category_path', '<a href="soundtrack.php">' 
						. _CO_PODCAST_PROGRAMME_SOUNDTRACKS . '</a> &gt; ' 
						. $sprockets_tag_buffer[$clean_tag_id]->getVar('title'));
			} elseif ($untagged_content) {
				$icmsTpl->assign('podcast_tag_name', _CO_PODCAST_PROGRAMME_UNTAGGED);
				$icmsTpl->assign('podcast_category_path', '<a href="soundtrack.php">' 
						. _CO_PODCAST_PROGRAMME_SOUNDTRACKS . '</a> &gt; ' 
						. _CO_PODCAST_PROGRAMME_UNTAGGED);
			}
			
			$icmsTpl->assign('podcast_select_box', $tag_select_box);
		}
	}
	
	// Optionally filter soundtracks by tag
	if (icms_get_module_status("sprockets") && ($clean_tag_id || $untagged_content)) {
		$podcastModule = icms::handler("icms_module")->getByDirname("podcast");
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
		$criteria->add(new icms_db_criteria_Item('mid', $podcastModule->getVar('mid')));
		$criteria->add(new icms_db_criteria_Item('item', 'soundtrack'));
		// Is this scalable to thousands of records? A manual query would use less resources
		$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
		foreach ($taglink_array as $taglink) {
			 $tagged_soundtrack_list[] = $taglink->getVar('iid');
		}
		unset($criteria);
		
		// Use the tagged message IDs as a filter criteria
		$tagged_soundtrack_list = "('" . implode("','", $tagged_soundtrack_list) . "')";
	}

	// prepare buffers to minimise queries
	$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(__FILE__)), 'podcast');
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	$sources = $podcast_programme_handler->getObjects(null, true);
	$formats = $system_mimetype_handler->getObjects(null, true);
	
	// Retrieve soundtracks
	$criteria = new icms_db_criteria_Compo();
	if (!empty($tagged_soundtrack_list)) {
		$criteria->add(new icms_db_criteria_Item('soundtrack_id', $tagged_soundtrack_list, 'IN'));
	}
	$criteria->add(new icms_db_criteria_Item('online_status', true));
	$objectTable = new icms_ipf_view_Table($podcast_soundtrack_handler, $criteria, array(), true);
	$objectTable->isForUserSide();
	$objectTable->addColumn(new icms_ipf_view_Column('date'));
	$objectTable->addColumn(new icms_ipf_view_Column('title'));
	$objectTable->addColumn(new icms_ipf_view_Column('source', _GLOBAL_LEFT, false,
		'source', $sources));
	$objectTable->addColumn(new icms_ipf_view_Column('format', _GLOBAL_LEFT, false,
		'format', $formats));
	$objectTable->addFilter('source', 'source_filter');
	if (icms_get_module_status("sprockets"))
	{
		$objectTable->addFilter('rights', 'rights_filter');
	}
	$objectTable->setDefaultSort('date');
	$objectTable->setDefaultOrder('DESC');
	$objectTable->addQuickSearch('title');
	$icmsTpl->assign('podcast_soundtrack_table', $objectTable->fetch());
}

$icmsTpl->assign('podcast_title', _MD_PODCAST_ALL_SOUNDTRACKS);
$icmsTpl->assign('podcast_module_home', podcast_getModuleName(true, true));
$icmsTpl->assign('podcast_display_breadcrumb', $podcastConfig['display_breadcrumb']);
if(!$clean_tag_id && !$untagged_content) {
	$icmsTpl->assign('podcast_category_path', '<a href="soundtrack.php">' 
			. _CO_PODCAST_PROGRAMME_SOUNDTRACKS . '</a>');
}

include_once 'footer.php';