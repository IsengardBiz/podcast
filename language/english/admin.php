<?php
/**
 * English language constants used in admin section of the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */
if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// Requirements
define("_AM_PODCAST_REQUIREMENTS", "Podcast Requirements");
define("_AM_PODCAST_REQUIREMENTS_INFO", "We've reviewed your system, unfortunately it doesn't meet
    all the requirements needed for Podcast to function. Below are the requirements needed.");
define("_AM_PODCAST_REQUIREMENTS_ICMS_BUILD", "This version of Podcast requires at least ImpressCMS 1.2.x.");
define("_AM_PODCAST_REQUIREMENTS_SUPPORT", "Should you have any question or concerns, please visit
    our forums at <a href='http://community.impresscms.org'>http://community.impresscms.org</a>.");

// Soundtrack
define("_AM_PODCAST_SOUNDTRACKS", "Soundtracks");
define("_AM_PODCAST_SOUNDTRACKS_DSC", "All soundtracks in the module");
define("_AM_PODCAST_SOUNDTRACK_CREATE", "Add a soundtrack");
define("_AM_PODCAST_SOUNDTRACK", "Soundtrack");
define("_AM_PODCAST_SOUNDTRACK_CREATE_INFO", "Fill-out the following form to create a new
    soundtrack.");
define("_AM_PODCAST_SOUNDTRACK_EDIT", "Edit this soundtrack");
define("_AM_PODCAST_SOUNDTRACK_EDIT_INFO", "Fill-out the following form in order to edit this
    soundtrack.");
define("_AM_PODCAST_SOUNDTRACK_MODIFIED", "The soundtrack was successfully modified.");
define("_AM_PODCAST_SOUNDTRACK_CREATED", "The soundtrack has been successfully created.");
define("_AM_PODCAST_SOUNDTRACK_VIEW", "Soundtrack info");
define("_AM_PODCAST_SOUNDTRACK_VIEW_DSC", "Here is the info about this soundtrack.");
define("_AM_PODCAST_SOUNDTRACK_ONLINE", "Soundtrack switched online.");
define("_AM_PODCAST_SOUNDTRACK_OFFLINE", "Soundtrack switched offline.");
define("_AM_PODCAST_SOUNDTRACK_NOT_FEDERATED", "Soundtrack is no longer federated.");
define("_AM_PODCAST_SOUNDTRACK_FEDERATED", "Soundtrack is now federated.");

// Programme
define("_AM_PODCAST_PROGRAMMES", "Programmes");
define("_AM_PODCAST_PROGRAMMES_DSC", "All programmes in the module");
define("_AM_PODCAST_PROGRAMME_CREATE", "Add a programme");
define("_AM_PODCAST_PROGRAMME", "Programme");
define("_AM_PODCAST_PROGRAMME_CREATE_INFO", "Fill-out the following form to create a new programme.");
define("_AM_PODCAST_PROGRAMME_EDIT", "Edit this programme");
define("_AM_PODCAST_PROGRAMME_EDIT_INFO", "Fill-out the following form in order to edit this
    programme.");
define("_AM_PODCAST_PROGRAMME_MODIFIED", "The programme was successfully modified.");
define("_AM_PODCAST_PROGRAMME_CREATED", "The programme has been successfully created.");
define("_AM_PODCAST_PROGRAMME_VIEW", "Programme info");
define("_AM_PODCAST_PROGRAMME_VIEW_DSC", "Here is the info about this programme.");

define("_AM_PODCAST_NO_UPLOAD_DIRECTORY", "<p><strong>Warning</strong>: The directory 
    <strong>/uploads/podcast</strong> does not exist. Please create it manually to allow programme
    logos and cover art to be stored.</p>");
define("_AM_PODCAST_UPLOAD_NOT_WRITABLE", "<p><strong>Warning</strong>: The directory
    /uploads/podcast</strong> is not writeable by the server. Please change the permissions
    (chmod) on this directory to 777, otherwise you will not be able to upload programme logos or
    cover art.</p>");
define("_AM_PODCAST_MUST_CREATE_PROGRAMME", "<p><strong>Warning</strong>: No programmes currently
    exist. You must create at least one programme before you can add soundtracks as every
    soundtrack must be assigned to a programme. Submission of soundtracks will fail if you
    ignore this warning.</p>");
define("_AM_PODCAST_MUST_AUTHORISE_MIMETYPES", "<p><strong>Warning</strong>: You must authorise
    Podcast to use at least one audio file type (mimetype) before you can upload soundtracks. Visit
    System => Mimetypes. Click the edit button on relevant entries (eg. MP3, WMA) and add Podcast
    to the list of modules allowed to use them.</p>");

// Instructions

define("_AM_PODCAST_INSTRUCTIONS_DSC",
"<h1>Read the manual!</h1>
<p>An <a href=\"" . ICMS_URL . "/modules/" . basename(dirname(dirname(dirname(__FILE__)))) // EDITED IN 1.3.3
	. "/extras/podcast_manual.pdf\">instruction manual</a> is available in the extras folder of
the podcast module. Please read it.<p>
<h2>Purpose</h2>
<p>The Podcast module allows you to publish audio and video files via RSS, streaming, download and 
inline via JW Player (installed separately). You can use it to publish:</p>
<ul><li>Podcast programmes (both audio and video).</li>
<li>Music albums.</li>
<li>Talks from one-off events such as conferences.</li>
<li>Other collections of sound or video files.</li></ul>
<h2>Features</h2>
<ul><li>Publication of multiple podcasts, albums or collections of sound/video.</li>
<li>Streaming of audio and video files, including entire programmes/albums.</li>
<li>Individual RSS feeds with media enclosures for each programme (W3C validated).</li>
<li>Configurable compact/extended views for albums/podcasts.</li>
<li>Optional inline video/audio player via JW Player (dual support for HTML 5 and Flash).</li>
<li>Configurable rights (license) management system and per-track rights control.</li>
<li>Configurable user-side metadata display - choose what fields you want to show.</li>
<li>Two blocks - recent soundtracks and list of programmes.</li>
<li>Provides a minimal implementation of the Open Archives Initiative Protocol for Metadata
Harvesting - the module can participate in distributed digital library systems and cross-site search.</li>
<li>Use of standard Unqualified Dublin Core fields for object description.</li>
<li>Dynamic image resizing using Nachenko's excellent resized_image Smarty plugin (configured in
module preferences).</li>
<li>It's a native IPF module.</li></ul>
<h2>Legal responsibilities</h2>
<p>Please respect the copyright and intellectual property rights of others. Enough said?</p>
<h2>Support</h2>
<p>Please direct support questions to the <a href=\"http://community.impresscms.org\">ImpressCMS
Community Forums</a>.</p>
<h2>Copyright notice</h3>
<p>This software is Copyright 2011 by Madfish (Simon Wilkinson), who is the author and rights
holder. The software is distributed free of charge under the
<a href=\"http://www.gnu.org/licenses/old-licenses/gpl-2.0.html\">
GNU General Public License (GPL) Version 2</a>, with provision to use the code in derivative works
under any later version of the GPL.</p>
<h2>Acknowledgements</h2>
<p>This module was developed using the excellent ImBuilding module. It was written while the
author was stranded on Koh Tao in the Gulf of Thailand, during nitrogen desaturation breaks :)</p>
");

// Test OAIPMH responses
define("_AM_PODCAST_TEST_IDENTIFY", "Identify");
define("_AM_PODCAST_TEST_IDENTIFY_DSC", " (provides information about the archive).");
define("_AM_PODCAST_TEST_GET_RECORD", "GetRecord");
define("_AM_PODCAST_TEST_GET_RECORD_DSC", " (retrieves a single specified record).");
define("_AM_PODCAST_TEST_LIST_IDENTIFIERS", "ListIdentifiers");
define("_AM_PODCAST_TEST_LIST_IDENTIFIERS_DSC", " (retrieves headers of multiple records).");
define("_AM_PODCAST_TEST_LIST_METADATA_FORMATS", "ListMetadataFormats");
define("_AM_PODCAST_TEST_LIST_METADATA_FORMATS_DSC", " (displays the available formats metadata can
    be requested in).");
define("_AM_PODCAST_TEST_LIST_RECORDS", "ListRecords");
define("_AM_PODCAST_TEST_LIST_RECORDS_DSC", " (retrieves multiple full records, possibly everything).");
define("_AM_PODCAST_TEST_LIST_SETS", "ListSets");
define("_AM_PODCAST_TEST_LIST_SETS_DSC", " (displays available sets, currently not supported).");

// Warnings on test-oaipmh response page

define("_AM_PODCAST_ENTER_RECORDS", "You must enter some soundtracks AND set them as FEDERATED 
    before using the test links below!");

define("_AM_PODCAST_ARCHIVE_DISABLED", "Open Archive functionality is currently disabled, you must
    turn it on (module preferences) to use the test links!");

// Info

define("_AM_PODCAST_TEST_OAIPMH",
		"<h1>Testing OAIPMH responses</h1>
<p>The OAIPMH protocol specifies a number of requests that external metadata harvesters can use
to retrieve metadata from your site. The links below allow you to see the XML response of the
Podcast module to incoming OAIPMH requests. This is best viewed in Firefox or Chrome, which will
show you the document tree.</p>
");

define("_AM_PODCAST_TEST_MORE_INFO",
		"<p>These will only work if:</p>
<ul><li>The archive functionality is enabled (view the archive object, it is the first setting.)</li>
<li>You have some soundtracks entered into the database.</li>
<li>At least one soundtrack has federation enabled (this exposes the soundtrack via the OAIPMH
service) AND is set as online.</li></ul>
<p>Please note that this is a minimal implementation that does not support sets or resumption
tokens. It does support selective harvesting of records based on time ranges delineated in the
query (see the spec for details about 'from' and 'until' arguments).</p>
<h2>About the Open Archives Initiative Protocol for Metadata Harvesting</h2>
<p>For more information about the OAIPMH, what it does and how to use it, please visit the 
<a href=\"http://www.openarchives.org/OAI/openarchivesprotocol.html\">Open Archives Initiative
website</a>. Detailed specifications are provided there. Of particular interest are the:</p>
<ul><li><a href=\"http://www.openarchives.org/OAI/2.0/guidelines.htm\">
Implementation guidelines</a></li>
<li><a href=\"http://www.openarchives.org/OAI/2.0/guidelines-repository.htm\">
Guidelines for repository implementers</a></li>
<li><a href=\"http://dublincore.org/documents/dces/\">
Dublin Core Metadata Element Set Version 1.1</a></li></ul>
");

// Added in V1.34
define("_AM_PODCAST_PROGRAMME_ALL_PROGRAMMES", "-- All programmes --");
define("_AM_PODCAST_PROGRAMME_FILTER_BY_TAG", "Filter by tag");
define("_AM_PODCAST_SOUNDTRACK_ALL_SOUNDTRACKS", "-- All soundtracks --");