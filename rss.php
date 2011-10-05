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

global $podcastConfig;
$clean_programme_id = $sort_order = '';
$podcastModule = icms_getModuleInfo(basename(dirname(__FILE__)));

$clean_programme_id = isset($_GET['programme_id']) ? intval($_GET['programme_id']) : false;
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : false;
$clean_limit = isset($_GET['limit']) ? intval($_GET['limit']) : false;

include_once ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__))
	. '/class/icmsfeed.php';
$podcast_feed = new IcmsFeed();
$podcast_soundtrack_handler = 
	icms_getModuleHandler('soundtrack', basename(dirname(__FILE__)), 'podcast');
$podcast_programme_handler = 
	icms_getModuleHandler('programme', basename(dirname(__FILE__)), 'podcast');

// generates a feed of recent soundtracks across all programmes
if (empty($clean_programme_id)) {
	$programme_title = _CO_PODCAST_NEW;
	$site_name = encode_entities($icmsConfig['sitename']);

	$podcast_feed->title = $site_name . ' - ' . _CO_PODCAST_NEW;
	$podcast_feed->url = PODCAST_URL . 'new.php';
	$podcast_feed->description = _CO_PODCAST_NEW_DSC . $site_name . '.';
	$podcast_feed->language = $podcastConfig['default_language'];
	$podcast_feed->charset = _CHARSET;
	$podcast_feed->category = $podcastModule->name();

	$url = ICMS_URL . '/images/logo.gif';
	$podcast_feed->image = array('title' => $podcast_feed->title, 'url' => $url,
			'link' => PODCAST_URL . 'new.php');
	$width = $podcastConfig['screenshot_width'];
	if ($width > 144) {
		$width = 144;
	}
	$podcast_feed->width = $width;
	$podcast_feed->atom_link = '"' . PODCAST_URL . 'rss.php"';

} else { // generates a feed for a specific programme

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
	$podcast_feed->language = $podcastConfig['default_language'];
	$podcast_feed->charset = _CHARSET;
	$podcast_feed->category = $podcastModule->name();

	$url = $programmeObj->getImageDir() . $programmeObj->getVar('cover');
	$podcast_feed->image = array('title' => $podcast_feed->title, 'url' => $url,
			'link' => PODCAST_URL . 'programme.php?programme_id=' . $programmeObj->id());
	$width = $podcastConfig['screenshot_width'];
	if ($width > 144) {
		$width = 144;
	}
	$podcast_feed->width = $width;
	$podcast_feed->atom_link = '"' . PODCAST_URL . 'rss.php?programme_id=' . $programmeObj->id() . '"';
}

if ($programmeObj) {
	$sort_order = $programmeObj->getVar('sort_order', 'e');
}

if (empty($clean_limit)) {
	$clean_limit = $podcastConfig['new_items'];
}

$soundtrackArray = $podcast_soundtrack_handler->getProgrammeSoundtracks($clean_start,
	$clean_limit, $clean_programme_id, $sort_order);

// prepare an array of soundtracks associated with this programme
foreach($soundtrackArray as $soundtrack) {
	$soundtrackObj = $podcast_soundtrack_handler->get($soundtrack['soundtrack_id']);
	$creator = $soundtrackObj->getVar('creator', 'e');
	$creator = explode('|', $creator);
	foreach ($creator as &$individual) {
		$individual = encode_entities($individual);
	}
	$description = encode_entities($soundtrack['description']);
	$file_size = $soundtrackObj->getVar('file_size', 'e');
	$title = encode_entities($soundtrack['title']);
	$identifier = encode_entities($soundtrack['identifier']);
	$link = encode_entities($soundtrack['itemUrl']);

	$podcast_feed->feeds[] = array (
		'title' => $title,
		'link' => $link,
		'description' => $description,
		'author' => $creator,
		// pubdate must be a RFC822-date-time EXCEPT with 4-digit year or the feed won't validate
		'pubdate' => date(DATE_RSS, $soundtrackObj->getVar('date', false)),
		'guid' => $link,
		'category' => $programme_title,
		// added the possibility to include media enclosures in the feed & template
		'enclosure' => '<enclosure length="' . $file_size . '" type="'
			. $soundtrackObj->get_mimetype() . '" url="' . $identifier . '" />'
	);
}

// validation issue:
// single and double quotes in programme title generate no-html-recommended warnings
// (although feed is valid). it looks like the quotes are converted to html entities during
// template assignment which is downstream of this file - can this behaviour be overridden?

$podcast_feed->render();