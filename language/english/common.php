<?php
/**
 * English language constants commonly used in the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// soundtrack
define("_CO_PODCAST_SOUNDTRACK_OAI_IDENTIFIER", "OAI Identifier");
define("_CO_PODCAST_SOUNDTRACK_OAI_IDENTIFIER_DSC", "Used to uniquely identify this record across
    federated sites, and prevents records being duplicated or imported multiple times. Should never
    be changed under any circumstance. Complies with the
    <a href=\"http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm\">OAI Identifier
    Format specification</a>.");
define("_CO_PODCAST_SOUNDTRACK_TITLE", "Title");
define("_CO_PODCAST_SOUNDTRACK_TITLE_DSC", " Title of the soundtrack.");
define("_CO_PODCAST_SOUNDTRACK_CREATOR", "Author");
define("_CO_PODCAST_SOUNDTRACK_CREATOR_DSC", " Separate multiple authors with a pipe '|' character.
    Use a convention for consistency, eg. John Smith|Jane Doe.");
define("_CO_PODCAST_SOUNDTRACK_DESCRIPTION", "Description");
define("_CO_PODCAST_SOUNDTRACK_DESCRIPTION_DSC", " Description of the soundtrack. This is of most
    use in podcasts where it is common to provide a summary of the content, but for music albums
    you can leave it blank, in which case you should also set the programme (album) to use compact
    view. This will keep the layout looking good.");
define("_CO_PODCAST_SOUNDTRACK_PUBLISHER", "Publisher");
define("_CO_PODCAST_SOUNDTRACK_PUBLISHER_DSC", " Publisher of the soundtrack.");
define("_CO_PODCAST_SOUNDTRACK_DATE", "Date");
define("_CO_PODCAST_SOUNDTRACK_DATE_DSC", " ");
define("_CO_PODCAST_SOUNDTRACK_FORMAT", "Format");
define("_CO_PODCAST_SOUNDTRACK_FILE_SIZE", "File size");
define("_CO_PODCAST_SOUNDTRACK_FILE_SIZE_DSC", "Enter in BYTES, it will be converted to human
    readable automatically.");
define("_CO_PODCAST_SOUNDTRACK_SOURCE", "Programme");
define("_CO_PODCAST_SOUNDTRACK_SOURCE_DSC", " Name of the album, conference or event at which the
    recording was made.");
define("_CO_PODCAST_SOUNDTRACK_LANGUAGE", "Language");
define("_CO_PODCAST_SOUNDTRACK_LANGUAGE_DSC", " Language of the recording, if any.");
define("_CO_PODCAST_SOUNDTRACK_RIGHTS", "Rights");
define("_CO_PODCAST_SOUNDTRACK_RIGHTS_DSC", " The license under which this recording is published. 
    In most countries, artistic works are copyright (even if you don't declare it) unless you
    specify another license.");
define("_CO_PODCAST_SOUNDTRACK_ONLINE_STATUS", "Online status");
define("_CO_PODCAST_SOUNDTRACK_ONLINE_STATUS_DSC", "Toggle this soundtrack online or offline.");
define("_CO_PODCAST_SOUNDTRACK_FEDERATED", "Federated");
define("_CO_PODCAST_SOUNDTRACK_FEDERATED_DSC", "Syndicate this soundtrack's metadata with other
    sites (cross site search) via the Open Archives Initiative Protocol for Metadata Harvesting.");
define("_CO_PODCAST_SOUNDTRACK_SUBMISSION_TIME", "Submission time");
define("_CO_PODCAST_SOUNDTRACK_SUBMITTER", "Submitter");
define("_CO_PODCAST_SOUNDTRACK_YES", "Yes");
define("_CO_PODCAST_SOUNDTRACK_NO", "No");
define("_CO_PODCAST_SOUNDTRACK_ONLINE", "Online");
define("_CO_PODCAST_SOUNDTRACK_OFFLINE", "Offline");

// additional items
define("_CO_PODCAST_SOUNDTRACK_PUBLISHED_ON", "Published");
define("_CO_PODCAST_SOUNDTRACK_BY", "By");
define("_CO_PODCAST_SOUNDTRACK_SOUNDTRACK", "soundtrack");
define("_CO_PODCAST_SOUNDTRACK_COUNTER", "view");
define("_CO_PODCAST_SOUNDTRACK_DOWNLOAD", "Download");
define("_CO_PODCAST_SOUNDTRACK_PLAY", "Play");
define("_CO_PODCAST_PROGRAMME_ENCLOSURES", "Feed includes audio enclosures");

// programme
define("_CO_PODCAST_PROGRAMME_TITLE", "Title");
define("_CO_PODCAST_PROGRAMME_TITLE_DSC", " Name of the album or podcast programme.");
define("_CO_PODCAST_PROGRAMME_OAI_IDENTIFIER", "OAI Identifier");
define("_CO_PODCAST_PROGRAMME_OAI_IDENTIFIER_DSC", "Used to uniquely identify this record across 
    federated sites, and prevents records being duplicated or imported multiple times. Should never
    be changed under any circumstance.");
define("_CO_PODCAST_PROGRAMME_PUBLISHER", "Published by");
define("_CO_PODCAST_PROGRAMME_PUBLISHER_DSC", " Publisher of this album or podcast programme (the
    band, or company responsible).");
define("_CO_PODCAST_PROGRAMME_DATE", "Released");
define("_CO_PODCAST_PROGRAMME_DATE_DSC", " The year the album was published or the event was held. 
    For ongoing podcast programmes, leave this blank (you can enter the publication date of each
    episode independently).");
define("_CO_PODCAST_PROGRAMME_DESCRIPTION", "Description");
define("_CO_PODCAST_PROGRAMME_DESCRIPTION_DSC", " A description or synopsis of the album /
    programme.");
define("_CO_PODCAST_PROGRAMME_COMPACT_VIEW", "Compact view");
define("_CO_PODCAST_PROGRAMME_COMPACT_VIEW_DSC", "Do you want to display this programme in compact 
    form (a simple list of contents, best for albums) or in expanded view with descriptions for
    each soundtrack (best for discussions?)");
define("_CO_PODCAST_PROGRAMME_SORT_ORDER", "Sort order for soundtracks");
define("_CO_PODCAST_PROGRAMME_SORT_ORDER_DSC", "CAUTION: Sort podcast programmes DESCENDING (most "
    .	"recent soundtracks first). You should only use ascending sort for static collections of sound "
    .	"such as albums. The podcast feed and programme streaming links will also display content in "
	.	"this order.");
define("_CO_PODCAST_PROGRAMME_COVER", "Cover");
define("_CO_PODCAST_PROGRAMME_COVER_DSC", "Upload a programme logo or album cover.");
define("_CO_PODCAST_PROGRAMME_SUBMISSION_TIME", "Submission time");
define("_CO_PODCAST_PROGRAMME_PLAY_ALL", "Play all tracks in this programme");
define("_CO_PODCAST_PROGRAMME_RSS_URL", "/modules/podcast/rss.php?programme_id=");
define("_CO_PODCAST_PROGRAMME_RSS_BUTTON", "/modules/podcast/img/rss.png");
define("_CO_PODCAST_PROGRAMME_TRACKS", "soundtrack");
define("_CO_PODCAST_NEW", "Recent podcasts");
define("_CO_PODCAST_NEW_DSC", "The most recent soundtracks across all podcasts from ");
define("_CO_PODCAST_PROGRAMME_COUNTER", "view");
define("_CO_PODCAST_PROGRAMME_DOWNLOAD", "Download");
define("_CO_PODCAST_PROGRAMME_STREAM", "Play");
define("_CO_PODCAST_PROGRAMME_SOUNDTRACKS", "Soundtracks");
define("_CO_PODCAST_PROGRAMME_PROGRAMMES", "Programmes");

// new
define("_CO_PODCAST_NEW_ITEMS", "Latest release");

// Edited in version 1.32
define("_CO_PODCAST_SOUNDTRACK_IDENTIFIER", "Primary media URL");
define("_CO_PODCAST_SOUNDTRACK_IDENTIFIER_DSC", " The URL for your <strong>primary</strong> audio or 
	video file. This file will be the target of download and streaming links and attached in RSS 
	feeds. Make sure you test it.");
define("_CO_PODCAST_SOUNDTRACK_FORMAT_DSC", " You can add more audio or video formats to this list by 
    authorising Podcast to use them in <a href=\"" . ICMS_URL
		. "/modules/system/admin.php?fct=mimetype\">System => Mimetypes</a>. <strong>Note</strong>: 
			The MP4 mimetype is NOT enabled by default! If you want to play MP4 videos you need 
			to add it and authorise Podcast to use it.");

// Added in version 1.32
define("_CO_PODCAST_SOUNDTRACK_INLINE_IDENTIFIER", "Secondary media URL");
define("_CO_PODCAST_SOUNDTRACK_INLINE_IDENTIFIER_DSC", " Optional. You can enter the URL of a 
	<strong>secondary</strong> audio or video file here, which can be played inline if you have 
	installed and enabled JW Player. You could, for example, use an audio recording as your main 
	 podcast / downloadable resource, but make a video recording of the same event available for 
	 viewing inline on your site. Or vice versa!");
define("_CO_PODCAST_SOUNDTRACK_POSTER_IMAGE", "Poster image");
define("_CO_PODCAST_SOUNDTRACK_POSTER_IMAGE_DSC", "The poster image is displayed on the JW Player 
	player box when the page loads. Take a nice screenshot of your video and crop it down to the 
	<strong>same size as the player</strong> (as set in module preferences). If you do not upload a 
	poster image, the player will display a black, empty box by default.");

// Added in version 1.33
define("_CO_PODCAST_SOUNDTRACK_TYPE", "Type");
define("_CO_PODCAST_SOUNDTRACK_TYPE_DSC", "Specify whether this is an audio or video file.");
define("_CO_PODCAST_SOUNDTRACK_TAG", "Tags");
define("_CO_PODCAST_SOUNDTRACK_TAG_DSC", "Select the tags (subjects) you wish to label this soundtrack with.");
define("_CO_PODCAST_PROGRAMME_TAG", "Tags");
define("_CO_PODCAST_PROGRAMME_TAG_DSC", "Select the tags (subjects) you wish to label this programme with.");
define("_CO_PODCAST_PROGRAMME_CREATOR", "Creator");
define("_CO_PODCAST_PROGRAMME_CREATOR_DSC", "The author of this work, if any.");
define("_CO_PODCAST_ALL_TAGS", "-- All tags --");
define("_CO_PODCAST_PROGRAMME_UNTAGGED", "Untagged");
define("_CO_PODCAST_SOUNDTRACK_NOTHING", "Nothing to display.");