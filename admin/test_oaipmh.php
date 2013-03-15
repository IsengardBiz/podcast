<?php

/**
 * Provides links to test the Podcast module's response to incoming OAIPMH requests
 *
 * Exteneral metadata harvesters compliant with the Open Archives Initiative Protocol for Metadata
 * harvesting can submit queries requesting information about the Podcast repository or its records.
 * This file allows admins to test, visualise and debug the response if necessary. It is also
 * included for educational purposes :)
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once("admin_header.php");
icms_cp_header();

$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

$podcastModule->displayAdminMenu(3, _AM_PODCAST_SOUNDTRACKS);

$id = '';
$soundtrackObjects = array();
$url = PODCAST_URL . 'oaipmh_target.php?verb=';

// explanatory text - use the links below to test responses to incoming OAIPMH requests
echo _AM_PODCAST_TEST_OAIPMH . '<br />';

// check if any soundtracks have been entered, throw warnings if not
// if there is, take the identifier of the first records as an example
$criteria = new icms_db_criteria_Compo();
$criteria->add(new icms_db_criteria_Item('online_status', '1'));
$criteria->add(new icms_db_criteria_Item('federated', '1'));
$criteria->setLimit(1);
$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack', 
	basename(dirname(dirname(__FILE__))),'podcast');
$soundtrackObjects = $podcast_soundtrack_handler->getObjects($criteria);
if (!empty($soundtrackObjects)) {
	$first_soundtrack = array_shift($soundtrackObjects);
	$id = $first_soundtrack->getVar('oai_identifier');
} else {
	// warning to enter some records first
	echo '<p>&nbsp;</p><p><strong><span style="color:#red;">'
		. _AM_PODCAST_ENTER_RECORDS . '</span></strong></p><p>&nbsp;</p><br />';
}

// check if archive functionality is enabled
if ($podcastConfig['podcast_enable_archive'] == 0) {
	echo '<p><strong><span style="color:#red;">' . _AM_PODCAST_ARCHIVE_DISABLED
		. '</span></strong></p><br />';
}

// check archive object exists
$sprockets_archive_handler = icms_getModuleHandler('archive', 'sprockets', 'sprockets');
$mid = $podcastModule->getVar('mid');
$criteria = new icms_db_criteria_Compo();
$criteria->add(new icms_db_criteria_Item('module_id', $mid));
$archive_exists = $sprockets_archive_handler->getCount($criteria);
if ($archive_exists == 0) {
	echo '<p><strong><span style="color:#red;">' . _CO_PODCAST_ARCHIVE_MUST_CREATE
		. '</span></strong></p><br />';
}

// links to trigger OAIPMH requests
echo '<ul><li><a href="' . $url . 'Identify">' . _AM_PODCAST_TEST_IDENTIFY
	. '</a>' . _AM_PODCAST_TEST_IDENTIFY_DSC . '</li>
<li><a href="' . $url . 'GetRecord&amp;metadataPrefix=oai_dc&amp;identifier=' . $id . '">' 
	. _AM_PODCAST_TEST_GET_RECORD . '</a>' . _AM_PODCAST_TEST_GET_RECORD_DSC . '</li>
<li><a href="' . $url . 'ListIdentifiers&amp;metadataPrefix=oai_dc">' 
	. _AM_PODCAST_TEST_LIST_IDENTIFIERS . '</a>' . _AM_PODCAST_TEST_LIST_IDENTIFIERS_DSC . '</li>
<li><a href="' . $url . 'ListMetadataFormats">' ._AM_PODCAST_TEST_LIST_METADATA_FORMATS . '</a>'
	. _AM_PODCAST_TEST_LIST_METADATA_FORMATS_DSC . '</li>
<li><a href="' . $url . 'ListRecords&amp;metadataPrefix=oai_dc">' 
	. _AM_PODCAST_TEST_LIST_RECORDS . '</a>' . _AM_PODCAST_TEST_LIST_RECORDS_DSC . '</li>
<li><a href="' . $url . 'ListSets">' . _AM_PODCAST_TEST_LIST_SETS
	. '</a>' . _AM_PODCAST_TEST_LIST_SETS_DSC . '</li></ul>';

// more information
echo _AM_PODCAST_TEST_MORE_INFO;

icms_cp_footer();
