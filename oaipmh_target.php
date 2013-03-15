<?php
/**
 * Handles incoming OAIPMH requests for a Sprockets-compatible module as per the OAIPMH specification.
 *
 * External metadata harvesters submit OAIPMH queries against this file for processing. The OAIPMH
 * specification outlines a standard vocabulary for requests and responses are defined by the spec's
 * XML schema, handled by an Archive object in the optional Sprockets module. If you don't want to 
 * enable the OAIPMH functionality of this module you can safely remove this file. But it is 
 * probably easier just to turn off OAIPMH functionality in the Sprockets module (option 1: don't 
 * create an Archive object for this module, option 2: each archive object has a kill switch). 
 * XML responses are assembled in a buffer and then flushed in a gzip compressed stream.
 * 
 * For more information visit the Open Archives Initiative, http://www.openarchives.org
 *
 * @copyright	Copyright Isengard.biz 2010, distributed under GNU GPL V2 or any later version
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.0
 * @author	Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package	Library
 * @version	$Id$
 */

/**
 * Basic validation and sanitation of user input but NOT range checking (calling method should do)
 *
 * This can be expanded for all different types of input: email, URL, filenames, media/mimetypes
 *
 * @param array $input_var Array of user input, gathered from $_GET, $_POST or $_SESSION
 * @param array $valid_vars Array of valid variables and data type (integer, boolean, string,)
 * @return array Array of validated and sanitized variables
 */

function validate($input_var, $valid_vars) {
	
	$clean_var = array();

	foreach ($valid_vars as $key => $type) {
		if (empty($input_var[$key])) {
			$input_var[$key] = NULL;
			continue;
		}
		
		switch ($type) {
			case 'int':
			case 'integer':
				$clean_var[$key] = $dirty_int = $clean_int = 0;
				if (filter_var($input_var[$key], FILTER_VALIDATE_INT) == TRUE) {
					$dirty_int = filter_var($input_var[$key], FILTER_SANITIZE_NUMBER_INT);
					$clean_int = mysql_real_escape_string($dirty_int);
					$clean_var[$key] = (int)$clean_int;
				}
				break;

			case 'html': // Tolerate (but encode) html tags and entities
				// Initialise
				$dirty_html = $clean_html = $clean_var[$key] = '';
				// Test for string
				if (is_string($input_var[$key])) {
					// Trim fore and aft whitespace
					$dirty_html = trim($input_var[$key]);
					// Keep html tags but encode entities and special characters
					$dirty_html = filter_var($dirty_html, FILTER_SANITIZE_SPECIAL_CHARS);
					$clean_html = mysql_real_escape_string($dirty_html);
					$clean_var[$key] = (string)$clean_html;
				}
				break;

			case 'plaintext': // Stripped down plaintext with tags removed
				// Initialise
				$dirty_text = $clean_text = $clean_var[$key] = '';
				// Test for string (in PHP, what isn't??)
				if (is_string($input_var[$key])) {
					// Trim fore and aft whitespace
					$dirty_text = trim($input_var[$key]);
					// Strip html tags, encode quotes and special characters
					$dirty_text = filter_var($dirty_text, FILTER_SANITIZE_STRING);
					$clean_text = mysql_real_escape_string($dirty_text);
					$clean_var[$key] = (string)$clean_text;
				}
				break;

			case 'name':
				// Initialise
				$clean_var[$key] = $clean_name = $dirty_name = '';
				$pattern = '^[a-zA-Z\-\']{1,60}$';
				// Test for string + alphanumeric
				if (is_string($input_var[$key]) && preg_match($pattern, $input_var[$key])) {
					// Trim fore and aft whitespace
					$dirty_name = trim($input_var[$key]);
					// Strip html tags, encode quotes and special characters
					$dirty_name = filter_var($dirty_name, FILTER_SANITIZE_STRING);
					$clean_name = mysql_real_escape_string($dirty_name);
					$clean_var[$key] = (string)$clean_name;
				}
				break;

			case 'email':
				$clean_var[$key] = $dirty_email = $clean_email = '';
				if (filter_var($input_var[$key], FILTER_VALIDATE_EMAIL) == TRUE) {
					$dirty_email = filter_var($input_var[$key], FILTER_SANITIZE_EMAIL);
					$clean_email = mysql_real_escape_string($dirty_email);
					$clean_var[$key] = (string)$clean_email;
				}
				break;

			case 'url':
				// Initialise
				$clean_var[$key] = $dirty_url = $clean_url = '';
				// Validate and sanitise URL
				if (filter_var($input_var[$key], FILTER_VALIDATE_URL) == TRUE) {
					$dirty_url = filter_var($input_var[$key], FILTER_SANITIZE_URL);
					$clean_url = mysql_real_escape_string($dirty_url);
					$clean_var[$key] = $clean_url;
				}

			case 'float':
			case 'double':
			case 'real':
				// Initialise
				$clean_var[$key] = $clean_float = 0;
				// Validate and sanitise float
				if (filter_var($input_var[$key], FILTER_VALIDATE_FLOAT) == TRUE) {
					$clean_float = filter_var($input_var[$key], FILTER_SANITIZE_NUMBER_FLOAT);
					$clean_var[$key] = (float)$clean_float;
				}
				break;

			case 'bool':
			case 'boolean':
				$clean_var[$key] = FALSE;
				if (is_bool($input_var[$key])) {
					$clean_var[$key] = (bool) $input_var[$key];
				}
				break;

			case 'binary':/* Only PHP6 - for now
				if (is_string($input_var[$key])) {
				$clean_var[$key] = htmlspecialchars(trim($input_var[$key]));
				}*/
				break;

			case 'array': // Note: doesn't inspect array *contents*, each must be inspected separately
				if (is_array($input_var[$key]) && !empty($input_var[$key])) {
					$clean_var[$key] = $input_var[$key];
				} else {
					$clean_var[$key] = $input_var[$key];
				}
				break;

			case 'object': // Note: doesn't inspect object *properties*, each must be inspected separately
				if (is_object($input_var[$key])) {
					$clean_var[$key] = (object)$input_var[$key];
				}
				break;
		}
	}
	return $clean_var;
}

include_once 'header.php';
$xoopsOption['template_main'] = 'podcast_soundtrack.html';
include_once ICMS_ROOT_PATH . '/header.php';

// Initialise
$dirty_vars = $allowed_vars = $clean_vars = array();
$verb = $identifier = $identification = $metadataPrefix = $from = $until = $set
	= $resumptionToken = $identification = $getRecord = $listMetadataFormats = $listSets
	= $listRecords = $badVerb = '';

$cursor = 0; // Will be overriden if there is a valid $resumptionToken

//////////////////////////////////////////////
////////// BEGIN INPUT SANITISATION //////////
//////////////////////////////////////////////

// Whitelist acceptable variables
$allowed_vars = array('verb' => 'plaintext', 'identifier' => 'plaintext',
	'metadataPrefix' => 'plaintext', 'from' => 'plaintext', 'until' => 'plaintext',
	'set' => 'plaintext', 'resumptionToken' => 'plaintext', 'cursor' => 'int');

// OAIPMH spec *requires* support for both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$dirty_vars = $_GET;
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$dirty_vars = $_POST;
}

/*
 *  If there is a resumption token, restore state from that INSTEAD of from GET/POST variables.
 *  This will work so long as *all* the required state information is serialised in the 
 *  resumption token. State is set in the Sprockets module /class/archive/lookup_records()
 */

if (!empty($dirty_vars['resumptionToken']) && ($dirty_vars['verb'] == 'ListIdentifiers' 
		|| $dirty_vars['verb'] == 'ListRecords' || $dirty_vars['verb'] == 'ListSets')) {
	if(get_magic_quotes_gpc()) {
		$dirty_vars = unserialize(stripslashes(urldecode($dirty_vars['resumptionToken'])));
	} else {
		$dirty_vars = unserialize(urldecode($dirty_vars['resumptionToken']));
	}
	$dirty_vars['resumptionToken'] = TRUE;
}

// Channel whitelisted variables through the validator function
$clean_vars = validate($dirty_vars, $allowed_vars);

// Extract the sanitised variables
extract($clean_vars);

//////////////////////////////////////////////////////////
////////// END INPUT SANITISATION ////////////////////////
//////////////////////////////////////////////////////////

// Set up the relevant archive and handlers relevant to target object
$module = icms_getModuleInfo(basename(dirname(__FILE__)));
$sprocketsModule = icms_getModuleInfo('sprockets');

if (icms_get_module_status("sprockets"))
{
	$module_object_handler = icms_getModuleHandler('soundtrack', $module->getVar('dirname'),
			$module->getVar('dirname'));
	$sprockets_archive_handler = icms_getModuleHandler('archive', $sprocketsModule->getVar('dirname'),
		'sprockets');

	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('module_id', $module->getVar('mid')));

	$archive_array = $sprockets_archive_handler->getObjects($criteria);
	$archiveObj = array_shift($archive_array);

	// If no archive object has been created, issue a warning
	if (!$archiveObj) {

		echo _CO_PODCAST_ARCHIVE_MUST_CREATE;

	} else {

		// Check if this archive is enabled before processing any OAIPMH requests

		if ($archiveObj->getVar('enable_archive', 'e') == 1 ) {

			// IMPORTANT: need to disable the logger because it breaks XML responses	
			icms::$logger->disableLogger();

			////////////////////////////////////////////////////////
			////////// BEGIN OPEN ARCHIVES INITIATIVE API //////////
			////////////////////////////////////////////////////////

			switch ($verb) {

				// Retrieve basic information about the archive
				case "Identify":
					$identify_response = $archiveObj->identify();
					$identification = simplexml_load_string($identify_response);
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $identification->asXML();
					ob_end_flush();
					exit();
					break;

				// Retrieve one record specified by a unique identifier
				case "GetRecord":
					$getRecord = simplexml_load_string($archiveObj->getRecord($module_object_handler,
							$identifier, $metadataPrefix));
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $getRecord->asXML();
					ob_end_flush();
					exit();
					break;

				// Retrieves record headers rather than full records, time range can be specified
				case "ListIdentifiers":
					if (!empty($from)) {
						if (strlen($from) == 10) {
							$from .= 'T00:00:00Z'; // If granularity is day level, add time to avoid breaking code
						}
					}
					if (!empty($until)) {
						if (strlen($until) == 10) {
							$until .= 'T23:59:59Z'; // If granularity is day level, add time to avoid breaking code
						}
					}
					$listIdentifiers = simplexml_load_string($archiveObj->listIdentifiers($module_object_handler, 
						$metadataPrefix, $from, $until, $set, $resumptionToken, $cursor));
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $listIdentifiers->asXML();
					ob_end_flush();
					exit();
					break;

				// List the metadata formats available from this archive
				case "ListMetadataFormats":
					$listMetadataFormats = simplexml_load_string($archiveObj->listMetadataFormats($module_object_handler,
						$identifier));
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $listMetadataFormats->asXML();
					ob_end_flush();
					exit();
					break;

				// Retrieve multiple records from the repository, time range can be specified
				case "ListRecords":
					if (!empty($from)) {
						if (strlen($from) == 10) {
							$from .= 'T00:00:00Z'; // If granularity is day level, add time to avoid breaking code
						}
					}
					if (!empty($until)) {
						if (strlen($until) == 10) {
							$until .= 'T23:59:59Z'; // If granularity is day level, add time to avoid breaking code
						}
					}
					$listRecords = simplexml_load_string($archiveObj->listRecords($module_object_handler, 
						$metadataPrefix, $from,	$until, $set, $resumptionToken, $cursor));
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $listRecords->asXML();
					ob_end_flush();
					exit();
					break;

				// Retrieve the set structure of this archive (sets are not implemented)
				case "ListSets":
					$listSets = simplexml_load_string($archiveObj->listSets($resumptionToken, $cursor));
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $listSets->asXML();
					ob_end_flush();
					exit();
					break;

				// If we don't know what's going on, throw badVerb error, request is illegal
				default:
					$badVerb = simplexml_load_string($archiveObj->BadVerb());
					ob_start("ob_gzhandler");
					header('Content-Type: text/xml');
					print $badVerb->asXML();
					ob_end_flush();
					exit();
					break;
			}

			//////////////////////////////////////////////////////
			////////// END OPEN ARCHIVES INITIATIVE API //////////
			//////////////////////////////////////////////////////

		} else {
			// Archive is disabled. Can it, baby...
			exit;
		}
	}
	$icmsTpl->assign('archive_module_home', podcast_getModuleName(TRUE, TRUE));
}
else { // Exit if Sprockets module is not installed and active
	exit;
}

include_once 'footer.php';