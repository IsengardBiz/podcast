<?php
/**
 * Generates individual RSS feeds for podcasts or albums
 *
 * Also allows the attachment of media enclosures to individual soundtracks,  thereby allowing
 * podcast clients to automatically retrieve audio files from the feeds. It uses a modified
 * icmsfeed.php and rss template - these have been built into the module in the interests of
 * a zero post-installation config.
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/** Include the module's header for all pages */
include_once 'header.php';
include_once ICMS_ROOT_PATH.'/header.php';

global $icmsConfig;

/**
 * Ensures that entities are encoded to ensure feed compliance with RSS specification
 *
 * @param string $field
 * @return string 
 */
function encode_entities($field) {
	$field = htmlspecialchars(html_entity_decode($field, ENT_QUOTES, 'UTF-8'), 
		ENT_NOQUOTES, 'UTF-8');
	return $field;
}

$clean_programme_id = $sort_order = '';
$podcastModule = icms_getModuleInfo(basename(dirname(__FILE__)));

$clean_programme_id = isset($_GET['programme_id']) ? intval($_GET['programme_id']) : false;
$clean_tag_id = !empty($_GET['tag_id']) ? intval($_GET['tag_id']) : FALSE;
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : false;
$clean_limit = isset($_GET['limit']) ? intval($_GET['limit']) : false;

include_once ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__))
	. '/class/CustomPodcastFeed.php';

$podcast_feed = new CustomPodcastFeed();
$podcast_soundtrack_handler = 
	icms_getModuleHandler('soundtrack', basename(dirname(__FILE__)), 'podcast');
$podcast_programme_handler = 
	icms_getModuleHandler('programme', basename(dirname(__FILE__)), 'podcast');

// Buffer a list of mimetypes
$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
$mimetype_buffer = $system_mimetype_handler->getObjects(null, TRUE);

// generates a feed of recent soundtracks across all programmes
if (empty($clean_programme_id) && empty($clean_tag_id)) {
	$programme_title = _CO_PODCAST_NEW;
	$site_name = encode_entities($icmsConfig['sitename']);

	$podcast_feed->title = $site_name . ' - ' . _CO_PODCAST_NEW;
	$podcast_feed->url = PODCAST_URL . 'new.php';
	$podcast_feed->description = _CO_PODCAST_NEW_DSC . $site_name . '.';
	$podcast_feed->language = icms::$module->config['default_language'];
	$podcast_feed->charset = _CHARSET;
	$podcast_feed->category = icms::$module->getVar('name');

	$url = ICMS_URL . '/images/logo.gif';
	$podcast_feed->image = array('title' => $podcast_feed->title, 'url' => $url,
			'link' => PODCAST_URL . 'new.php');
	$width = icms::$module->config['screenshot_width'];
	if ($width > 144) {
		$width = 144;
	}
	$podcast_feed->width = $width;
	$podcast_feed->atom_link = '"' . PODCAST_URL . 'rss.php"';
	
	$soundtrackArray = $podcast_soundtrack_handler->getProgrammeSoundtracks($clean_start,
	$clean_limit, $clean_programme_id, $sort_order);
} else { // generates a feed for a specific programme or tag
	
	// Generate a programme-specific feed
	if ($clean_programme_id) {
		// need to remove html tags and problematic characters to meet RSS spec
		$programmeObj = $podcast_programme_handler->get($clean_programme_id);
		$site_name = encode_entities($icmsConfig['sitename']);
		$programme_title = encode_entities($programmeObj->getVar('title'));
		$programme_description = strip_tags($programmeObj->getVar('description'));
		$programme_description = encode_entities($programme_description);
		$url = $programmeObj->getImageDir() . $programmeObj->getVar('cover');
		$url = encode_entities($url);

		$podcast_feed->title = $site_name . ' - ' . $programme_title;
		$podcast_feed->url = PODCAST_URL . 'programme.php?programme_id=' . $programmeObj->id();
		$podcast_feed->description = $programme_description;
		$podcast_feed->language = icms::$module->config['default_language'];
		$podcast_feed->charset = _CHARSET;
		$podcast_feed->category = $podcastModule->getVar('name');

		$url = $programmeObj->getImageDir() . $programmeObj->getVar('cover');
		$podcast_feed->image = array('title' => $podcast_feed->title, 'url' => $url,
				'link' => PODCAST_URL . 'programme.php?programme_id=' . $programmeObj->id());
		$width = icms::$module->config['screenshot_width'];
		if ($width > 144) {
			$width = 144;
		}
		$podcast_feed->width = $width;
		$podcast_feed->atom_link = '"' . PODCAST_URL . 'rss.php?programme_id=' . $programmeObj->id() . '"';
		
		$soundtrackArray = $podcast_soundtrack_handler->getProgrammeSoundtracks($clean_start,
		$clean_limit, $clean_programme_id, $sort_order);
	} else {
		// Generate a tag-specific feed
		if (icms_get_module_status("sprockets") && $clean_tag_id) {
			$sprocketsModule = icms_getModuleInfo('sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_tag_handler = icms_getModuleHandler('tag',
					$sprocketsModule->getVar('dirname'), 'sprockets');

			// Check that the tag exists and has RSS feeds enabled
			$tagObj = $sprockets_tag_handler->get($clean_tag_id);
			if (!empty($tagObj) && !$tagObj->isNew()) {
				if ($tagObj->getVar('rss', 'e') == 1) {

					// Need to remove html tags and problematic characters to meet RSS spec
					$tagObj = $sprockets_tag_handler->get($clean_tag_id);
					$site_name = encode_entities($icmsConfig['sitename']);
					$tag_title = encode_entities($tagObj->getVar('title'));
					$tag_description = strip_tags($tagObj->getVar('description'));
					$tag_description = encode_entities($tag_description);

					$podcast_feed->title = $site_name . ' - ' . $tag_title;
					$podcast_feed->url = LIBRARY_URL . 'news.php?tag_id=' . $tagObj->getVar('tag_id');
					$podcast_feed->description = $tag_description;
					$podcast_feed->language = _LANGCODE;
					$podcast_feed->charset = _CHARSET;
					$podcast_feed->category = $podcastModule->getVar('name');

					// If there's a tag icon, use it as the feed image
					if ($tagObj->getVar('icon', 'e')) {
						$url = $tagObj->getImageDir() . $tagObj->getVar('icon', 'e');
					} else {
						$url = ICMS_URL . 'images/logo.gif';
					}
					$podcast_feed->image = array('title' => $news_feed->title, 'url' => $url,
							'link' => PODCAST_URL . 'rss.php?tag_id='
							. $tagObj->getVar('tag_id'));
					$podcast_feed->width = 144;
					$podcast_feed->atom_link = '"' . PODCAST_URL . 'rss.php?tag_id=' 
							. $tagObj->getVar('tag_id') . '"';

					$soundtrackArray = $podcast_soundtrack_handler->getSoundtracksForTag($clean_tag_id, 
							$podcastModule->config['new_items'], 0, TRUE);
				} else {
					exit; // RSS not enabled for this tag
				}
			} else {
				exit; // Tag does not exist
			}
		}
	}
}

if ($programmeObj) {
	$sort_order = $programmeObj->getVar('sort_order', 'e');
}

if (empty($clean_limit)) {
	$clean_limit = icms::$module->config['new_items'];
}

// prepare an array of soundtracks associated with this programme
foreach($soundtrackArray as &$soundtrack) {
	$creator = explode('|', $soundtrack->getVar('creator', 'e'));
	foreach ($creator as &$individual) {
		$individual = encode_entities($individual);
	}
	$description = encode_entities($soundtrack->getVar('description'));
	$file_size = intval($soundtrack->getVar('file_size', 'e'));
	$title = encode_entities($soundtrack->getVar('title'));
	$identifier = encode_entities($soundtrack->getVar('identifier'));
	$link = PODCAST_URL . '/soundtrack.php?soundtrack_id=' . $soundtrack->getVar('soundtrack_id');
	$podcast_feed->feeds[] = array (
		'title' => $title,
		'link' => $link,
		'description' => $description,
		'author' => $creator,
		// pubdate must be a RFC822-date-time EXCEPT with 4-digit year or the feed won't validate
		'pubdate' => date(DATE_RSS, $soundtrack->getVar('date', 'e')),
		'guid' => $link,
		'category' => $programme_title,
		// added the possibility to include media enclosures in the feed & template
		'enclosure' => '<enclosure length="' . $file_size . '" type="'
			. $soundtrack->get_mimetype() . '" url="' . $identifier . '" />'
	);
}

// validation issue:
// single and double quotes in programme title generate no-html-recommended warnings
// (although feed is valid). It looks like the quotes are converted to html entities during
// template assignment which is downstream of this file - can this behaviour be overridden?
$podcast_feed->render();