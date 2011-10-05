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
    Use a convention for consistency, eg. John Smith|Jane Doe");
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
define("_CO_PODCAST_SOUNDTRACK_FORMAT_DSC", " You can add more audio formats to this list by 
    authorising Podcast to use them in <a href=\"" . ICMS_URL
		. "/modules/system/admin.php?fct=mimetype\">System => Mimetypes</a>.");
define("_CO_PODCAST_SOUNDTRACK_FILE_SIZE", "File size");
define("_CO_PODCAST_SOUNDTRACK_FILE_SIZE_DSC", "Enter in BYTES, it will be converted to human
    readable automatically");
define("_CO_PODCAST_SOUNDTRACK_IDENTIFIER", "URL");
define("_CO_PODCAST_SOUNDTRACK_IDENTIFIER_DSC", " The link to download the audio file, make sure
    you test it.");
define("_CO_PODCAST_SOUNDTRACK_SOURCE", "Programme");
define("_CO_PODCAST_SOUNDTRACK_SOURCE_DSC", " Name of the album, conference or event at which the
    recording was made.");
define("_CO_PODCAST_SOUNDTRACK_LANGUAGE", "Language");
define("_CO_PODCAST_SOUNDTRACK_LANGUAGE_DSC", " Language of the recording, if any.");
define("_CO_PODCAST_SOUNDTRACK_RIGHTS", "Rights");
define("_CO_PODCAST_SOUNDTRACK_RIGHTS_DSC", " The license under which this recording is published. 
    In most countries, artistic works are copyright (even if you don't declare it) unless you
    specify another license.");
define("_CO_PODCAST_SOUNDTRACK_STATUS", "Status");
define("_CO_PODCAST_SOUNDTRACK_STATUS_DSC", "Toggle this soundtrack online or offline");
define("_CO_PODCAST_SOUNDTRACK_FEDERATED", "Federated");
define("_CO_PODCAST_SOUNDTRACK_FEDERATED_DSC", "Syndicate this soundtrack's metadata with other
    sites (cross site search) via the Open Archives Initiative Protocol for Metadata Harvesting");
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

// rights
define("_CO_PODCAST_RIGHTS_TITLE", "Title");
define("_CO_PODCAST_RIGHTS_TITLE_DSC", " Name of the license.");
define("_CO_PODCAST_RIGHTS_DESCRIPTION", "Summary");
define("_CO_PODCAST_RIGHTS_DESCRIPTION_DSC", " A formal description of the license terms, eg. All 
    Rights Reserved. For other licenses (eg. Creative Commons) it is best to copy the terms from
    the official source.");
define("_CO_PODCAST_RIGHTS_IDENTIFIER", "License terms");
define("_CO_PODCAST_RIGHTS_IDENTIFIER_DSC", " Enter a link to the definitive license if available. 
    For example, the full GNU GPL and Creative Commons licenses are published online by their
    respective foundations.");
define("_CO_PODCAST_RIGHTS_FULL_TERMS", "View the full terms of this license.");
define("_CO_PODCAST_RIGHT_ABOUT_RIGHTS", "The works displayed on this site may be subject to
    different terms and conditions regarding their use and redistribution. The licenses supported
    by this site are listed below. Please check the licensing agreement for each work individually
    to see which applies.");

// archive
define("_CO_PODCAST_ARCHIVE_ENABLED", "Archive online");
define("_CO_PODCAST_ARCHIVE_ENABLED_DSC", "Toggle this archive online (yes) or offline (no).");
define("_CO_PODCAST_ARCHIVE_TARGET_MODULE", "Target module");
define("_CO_PODCAST_ARCHIVE_TARGET_MODULE_DSC", "Select the module you wish to enable the OAIPMH
    (federation) service for. Currently only the Podcast module is supported.");
define("_CO_PODCAST_ARCHIVE_METADATA_PREFIX", "Metadata prefix");
define("_CO_PODCAST_ARCHIVE_METADATA_PREFIX_DSC", " Indicates the XML metadata schemes supported
    by this archive. Presently only Dublin Core is supported (oai_dc).");
define("_CO_PODCAST_ARCHIVE_NAMESPACE", "Namespace");
define("_CO_PODCAST_ARCHIVE_NAMESPACE_DSC", "Used to construct unique identifiers for records. 
    Default is to use your domain name. Changing this is not recommended as it helps people
    identify your repository as the source of a record that has been shared with other archives.");
define("_CO_PODCAST_ARCHIVE_GRANULARITY", "Granularity");
define("_CO_PODCAST_ARCHIVE_GRANULARITY_DSC", " The granularity of datestamps. The OAIPMH permits 
    two levels of granularity, this implementation supports the most fine grained option
    (YYYY-MM-DDThh:mm:ssZ).");
define("_CO_PODCAST_ARCHIVE_DELETED_RECORD", "Deleted record support");
define("_CO_PODCAST_ARCHIVE_DELETED_RECORD_DSC", " Does the archive support tracking of deleted
    records? This implementation does not currently support deleted records.");
define("_CO_PODCAST_ARCHIVE_EARLIEST_DATE_STAMP", "Earliest date stamp");
define("_CO_PODCAST_ARCHIVE_EARLIEST_DATE_STAMP_DSC", " The datestamp for the oldest record in
    your archive.");
define("_CO_PODCAST_ARCHIVE_ADMIN_EMAIL", "Admin email");
define("_CO_PODCAST_ARCHIVE_ADMIN_EMAIL_DSC", " The email address for the administrator of this
    archive. Be aware that this address is reported in response to incoming OAIPMH requests.");
define("_CO_PODCAST_ARCHIVE_PROTOCOL_VERSION", "Protocol version");
define("_CO_PODCAST_ARCHIVE_PROTOCOL_VERSION_DSC", " The OAIPMH protocol version implemented by
    this repository. Currently only version 2.0 is supported.");
define("_CO_PODCAST_ARCHIVE_REPOSITORY_NAME", "Repository name");
define("_CO_PODCAST_ARCHIVE_REPOSITORY_NAME_DSC", " The name of your archive.");
define("_CO_PODCAST_ARCHIVE_BASE_URL", "Base URL");
define("_CO_PODCAST_ARCHIVE_BASE_URL_DSC", " The target URL to which incoming OAIPMH requests for
    your archive should be sent.");
define("_CO_PODCAST_ARCHIVE_COMPRESSION", "Compression");
define("_CO_PODCAST_ARCHIVE_COMPRESSION_DSC", " Indicates what types of compression are supported
    by this archive. Presently only gzip is supported.");
define("_CO_PODCAST_ARCHIVE_ABOUT_THIS_ARCHIVE", "Our soundtrack collection is an Open Archive");
define("_CO_PODCAST_ARCHIVE_OAIPMH_TARGET", "This website implements the 
    <a href=\"http://www.openarchives.org/pmh/\">Open Archives Initiative Protocol for Metadata
    Harvesting</a> (OAIPMH). Compliant harvesters can access our soundtrack metadata from the
    OAIPMH target below. OAIPMH queries should be directed to the Base URL specified below.");
define("_CO_PODCAST_ARCHIVE_NOT_AVAILABLE", "Sorry, Open Archive functionality is not enabled at
    this time.");
define("_CO_PODCAST_ARCHIVE_NOT_CONFIGURED", "Podcast is currently configured to refuse incoming
    OAIPMH requests, sorry");
define("_CO_PODCAST_ARCHIVE_MUST_CREATE", "Error: An archive object must be created before OAIPMH
    requests can be handled. Please create one via the Open Archive tab in Podcast administration.");

// new
define("_CO_PODCAST_NEW_ITEMS", "Latest release");