<?php
/**
 * English language constants related to module information
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// Module Info
// The name of this module

define("_MI_PODCAST_MD_NAME", "Podcast");
define("_MI_PODCAST_MD_DESC", "Publish podcasts and albums and stream audio recordings for 
	ImpressCMS versions 1.2.x");
define("_MI_PODCAST_SOUNDTRACKS", "Soundtracks");
define("_MI_PODCAST_PROGRAMMES", "Programmes");
define("_MI_PODCAST_ARCHIVES", "Open Archive");
define("_MI_PODCAST_START_PAGE", "Start page");
define("_MI_PODCAST_START_PAGE_DSC", "What page do you want to use as the home page for this
    module?");
define("_MI_PODCAST_DEFAULT_LANGUAGE", "Default language");
define("_MI_PODCAST_DEFAULT_LANGUAGE_DSC", "Used as the default option in the soundtrack submission
    form to save you time");
define("_MI_PODCAST_ENABLE_ARCHIVE", "Enable Open Archive functionality?");
define("_MI_PODCAST_ENABLE_ARCHIVE_DSC", "If enabled, the module will respond to incoming OAIPMH
    requests against its base URL. This allows specialised search engines to import your records
    for indexing.");
define("_MI_PODCAST_FEDERATE", "Federate soundtracks by default?");
define("_MI_PODCAST_FEDERATE_DSC", "Sets the default value for the federation setting in the add
    soundtrack form. You can override it, its just a convenience.");
define("_MI_PODCAST_INSTRUCTIONS", "Instructions");
define("_MI_PODCAST_RECENT", "Recent podcasts");
define("_MI_PODCAST_RECENTDSC", "Displays a list of the most recent soundtracks");
define("_MI_PODCAST_PROGRAMMESDSC", "Displays a list of podcast programmes");
define("_MI_PODCAST_NEW_ITEMS", "Number of soundtracks to display on the 'new' page and in RSS feeds");
define("_MI_PODCAST_NEW_VIEW_MODE", "Display new podcasts page in compact view?");
define("_MI_PODCAST_NEW_VIEW_MODEDSC", "Compact view does not show soundtrack descriptions, it is
    best for albums. If your tracks have descriptions, choose extended view");
define("_MI_PODCAST_SCREENSHOT_WIDTH", "Screenshot width (in pixels)");
define("_MI_PODCAST_SCREENSHOT_WIDTHDSC", "This value is used to scale programme logos / album
    cover art, height will be scaled proportionately.");
define("_MI_PODCAST_THUMBNAIL_WIDTH", "Thumbnail width (in pixels)");
define("_MI_PODCAST_THUMBNAIL_WIDTHDSC", "This value is used to scale smaller versions of programme
    logos / album cover art, height will be scaled proportionately");
define("_MI_PODCAST_NUMBER_SOUNDTRACKS", "Number of soundtracks to display per page");
define("_MI_PODCAST_NUMBER_SOUNDTRACKSDSC", "When looking at the contents of a single programme");
define("_MI_PODCAST_NUMBER_PROGRAMMES", "Number of programmes to display per page");
define("_MI_PODCAST_PROGRAMMES_SORT_PREFERENCE", "Sort programmes by:");
define("_MI_PODCAST_PROGRAMMES_SORT_PREFERENCEDSC", "Affects the order programmes are listed on the
    programmes index page.");
define("_MI_PODCAST_NUMBER_PROGRAMMESDSC", "When looking at the programme index page");
define("_MI_PODCAST_NEW", "New");

// display preferences
define("_MI_PODCAST_DISPLAY_BREADCRUMB", "Display breadcrumb navigation?");
define("_MI_PODCAST_DISPLAY_BREADCRUMBDSC", "The breadcrumb is the link trail that displays at the
	top of the module.");
define("_MI_PODCAST_DISPLAY_RELEASED", "Display programme release date");
define("_MI_PODCAST_DISPLAY_RELEASEDDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_PROGRAMME_PUBLISHER", "Display programme publisher field");
define("_MI_PODCAST_DISPLAY_PROGRAMME_PUBLISHERDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_TRACKCOUNT", "Display programme soundtrack counter field");
define("_MI_PODCAST_DISPLAY_TRACKCOUNTDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_COUNTER", "Display programme/soundtrack views counter field");
define("_MI_PODCAST_DISPLAY_COUNTERDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_CREATOR", "Display soundtrack author field");
define("_MI_PODCAST_DISPLAY_CREATORDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_DATE", "Display soundtrack date field");
define("_MI_PODCAST_DISPLAY_DATEDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_FORMAT", "Display soundtrack format / file size field");
define("_MI_PODCAST_DISPLAY_FORMATDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_SOUNDTRACK_PUBLISHER", "Display soundtrack publisher field");
define("_MI_PODCAST_DISPLAY_SOUNDTRACK_PUBLISHERDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_LANGUAGE", "Display soundtrack language field");
define("_MI_PODCAST_DISPLAY_LANGUAGEDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_RIGHTS", "Display soundtrack rights field");
define("_MI_PODCAST_DISPLAY_RIGHTSDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_DOWNLOAD_BUTTON", "Display download button");
define("_MI_PODCAST_DISPLAY_DOWNLOAD_BUTTONDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_STREAMING_BUTTON", "Display play (streaming) button");
define("_MI_PODCAST_DISPLAY_STREAMING_BUTTONDSC", "Toggles visibility in user-side templates");
define("_MI_PODCAST_DISPLAY_SOUNDTRACK_SOURCE", "Display soundtrack programme (source) field");
define("_MI_PODCAST_DISPLAY_SOUNDTRACK_SOURCEDSC", "Toggles visibility in user-side templates");

// additional admin menu items
define("_MI_PODCAST_TEMPLATES", "Templates");
define("_MI_PODCAST_TEST_OAIPMH", "Test OAIPMH responses");

// notifications - categories
define("_MI_PODCAST_GLOBAL_NOTIFY", "All content");
define("_MI_PODCAST_GLOBAL_NOTIFY_DSC", "Notifications related to all programmes and soundtracks
    in this module");

define("_MI_PODCAST_PROGRAMME_NOTIFY", "Programme");
define("_MI_PODCAST_PROGRAMME_NOTIFY_DSC", "Notifications related to all soundtracks in this
    programme");

define("_MI_PODCAST_SOUNDTRACK_NOTIFY", "Soundtrack");
define("_MI_PODCAST_SOUNDTRACK_NOTIFY_DSC", "Notifications related to individual soundtracks");

// notifications - events
define("_MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY", "New soundtrack published");
define("_MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_CAP", "Notify me when a new soundtrack
    is published in any programme.");
define("_MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_DSC", "Receive notification when a new
    soundtrack is published.");
define("_MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_SBJ",
		"New soundtrack published at {X_SITENAME}");

define("_MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY", "New programme published");
define("_MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_CAP",
		"Notify me when a new programme is published.");
define("_MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_DSC", "Receive notification when a new
    programme is published.");
define("_MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_SBJ",
		"New audio programme published at {X_SITENAME}");

define("_MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY", "New soundtrack published");
define("_MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_CAP", "Notify me when a new soundtrack is
    published in this programme.");
define("_MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_DSC", "Receive notification when a new
    soundtrack is published in this programme.");
define("_MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_SBJ",
		" New soundtrack published at {X_SITENAME}");

// other stuff

define("_MI_PODCAST_FINAL", "Use this module at your own risk. And read the manual (in the extras "
	. "folder) before you play with it.");