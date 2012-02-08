<?php
/**
 * Handles incoming OAIPMH requests for the Podcast module as per the OAIPMH specification.
 *
 * External metadata harversters submit OAIPMH queries against this file for processing. The OAIPMH
 * specification outlines a standard vocabulary for requests and responses are defined by the spec's
 * XML schema, handled by Archive object. If you don't want to enable the OAIPMH functionality of
 * this module you can safely remove this file, it will not affect the other functions. But it is
 * probably easier just to turn off OAIPMH functionality in the module preferences (there is a
 * kill switch). XML responses are assembled in a buffer and then flushed in a compressed stream.
 *
 * @copyright	Copyright Isengard.biz 2010, distributed under GNU GPL V2 or any later version
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.0
 * @author	Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package	archive
 * @version	$Id$
 */

include_once 'header.php';
$xoopsOption['template_main'] = 'podcast_soundtrack.html';
include_once ICMS_ROOT_PATH . '/header.php';

// check if Open Archive functionality is enabled - and kill script if it isn't

if ($podcastConfig['podcast_enable_archive'] == 0) {
	echo _CO_PODCAST_ARCHIVE_NOT_CONFIGURED;
	exit;
} else {

	// initialise
	$dirty_vars = $allowed_vars = $clean_vars = array();
	$verb = $identifier = $identification = $metadataPrefix = $from = $until = $set
			= $resumptionToken = $identification = $getRecord = $listMetadataFormats = $listSets
			= $listRecords = $badVerb = '';

	////////// BEGIN INPUT SANITISATION //////////

	// whitelist acceptable variables
	$allowed_vars = array('verb' => 'plaintext', 'identifier' => 'plaintext',
		'metadataPrefix' => 'plaintext', 'from' => 'plaintext', 'until' => 'plaintext',
		'set' => 'plaintext', 'resumptionToken' => 'plaintext');

	// OAIPMH spec requires support for both GET and POST requests
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		$dirty_vars = $_GET;
	} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$dirty_vars = $_POST;
	}

	// channel whitelisted variables through the validator
	$clean_vars = podcast_validate($dirty_vars, $allowed_vars);

	// extract the sanitised variables
	extract($clean_vars);

	////////// END INPUT SANITISATION //////////
	// set up the relevant archive and handlers relevant to target object
	$podcast_archive_handler = icms_getModuleHandler('archive',
		basename(dirname(__FILE__)), 'podcast');
	$archiveObjects = $podcast_archive_handler->getObjects();

	// there should only be one archive object but the id may vary if deleted / recreated
	// so just pull the first one from the array
	$archiveObj = array_shift($archiveObjects);

	// if no archive object has been created, issue a warning
	if (!$archiveObj) {
		echo _CO_PODCAST_ARCHIVE_MUST_CREATE;
	}

	// IMPORTANT: need to disable the logger because it breaks XML responses
	icms::$logger->disableLogger();

	// directs response to incoming requests
	switch ($verb) {

		// retrieve information about the archive
		case "Identify":
			$identify_response = $archiveObj->identify();
			$identification = simplexml_load_string($identify_response);
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $identification->asXML();
			ob_end_flush();
			exit();
			break;

		// retrieve one record
		case "GetRecord":
			$getRecord = simplexml_load_string($archiveObj->getRecord($identifier,
				$metadataPrefix));
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $getRecord->asXML();
			ob_end_flush();
			exit();
			break;

		// retrieves record headers, rather than full records
		case "ListIdentifiers":
			if (!empty($from)) {
				if (strlen($from) == 10) {
					$from .= 'T00:00:00Z'; // if granularity is day level, add time to avoid breaking code
				}
			}
			if (!empty($until)) {
				if (strlen($until) == 10) {
					$until .= 'T23:59:59Z'; // if granularity is day level, add time to avoid breaking code
				}
			}
			$listIdentifiers = simplexml_load_string($archiveObj->listIdentifiers($metadataPrefix,
				$from, $until, $set, $resumptionToken));
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $listIdentifiers->asXML();
			ob_end_flush();
			exit();
			break;

		// list the metadata formats available from this archive
		case "ListMetadataFormats":
			$listMetadataFormats = simplexml_load_string($archiveObj->listMetadataFormats($identifier));
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $listMetadataFormats->asXML();
			ob_end_flush();
			exit();
			break;

		// retrieve multiple records from the repository
		case "ListRecords":
			if (!empty($from)) {
				if (strlen($from) == 10) {
					$from .= 'T00:00:00Z'; // if granularity is day level, add time to avoid breaking code
				}
			}
			if (!empty($until)) {
				if (strlen($until) == 10) {
					$until .= 'T23:59:59Z'; // if granularity is day level, add time to avoid breaking code
				}
			}
			
			$listRecords = simplexml_load_string($archiveObj->listRecords($metadataPrefix, $from,
				$until, $set, $resumptionToken));
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $listRecords->asXML();
			ob_end_flush();
			exit();
			break;

		// retrieve the set structure of this archive (sets are not implemented yet)
		case "ListSets":
			$listSets = simplexml_load_string($archiveObj->listSets($resumptionToken));
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $listSets->asXML();
			ob_end_flush();
			exit();
			break;

		// if we don't know what's going on, throw badVerb error, request is illegal
		default:
			$badVerb = simplexml_load_string($archiveObj->BadVerb());
			ob_start("ob_gzhandler");
			header('Content-Type: text/xml');
			print $badVerb->asXML();
			ob_end_flush();
			exit();
			break;
	}
}

$icmsTpl->assign('archive_module_home', archive_getModuleName(true, true));

include_once 'footer.php';