<?php

/**
 * Class representing Archive objects and responding to OAIPMH requests
 *
 * A mimimal implementation of the Open Archives Initiative Protocol for Metadata Harvesting (OAIPMH)
 * Requests are received against the oaipmh_target.php file. Responses are XML streams as per the
 * OAIPMH specification, which defines a standard vocabulary and response format.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2010
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		archive
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class PodcastArchive extends icms_ipf_seo_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler ArchivePostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		parent::__construct($handler);

		$this->quickInitVar('archive_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('metadata_prefix', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setMetadataPrefix());
		$this->quickInitVar('namespace', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setNamespace());
		$this->quickInitVar('granularity', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setGranularity());
		$this->quickInitVar('deleted_record', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setDeletedRecord());
		$this->quickInitVar('earliest_date_stamp', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setEarliestDateStamp());
		$this->quickInitVar('admin_email', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setAdminEmail());
		$this->quickInitVar('protocol_version', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setProtocolVersion());
		$this->quickInitVar('repository_name', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setRepositoryName());
		$this->quickInitVar('base_url', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setBaseUrl());
		$this->quickInitVar('compression', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setCompression());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml');
		$this->initCommonVar('dobr');
		$this->initCommonVar('docxode');

		$this->doMakeFieldreadOnly('metadata_prefix');
		$this->doMakeFieldreadOnly('namespace');
		$this->doMakeFieldreadOnly('granularity');
		$this->doMakeFieldreadOnly('deleted_record');
		$this->doMakeFieldreadOnly('earliest_date_stamp');
		$this->doMakeFieldreadOnly('protocol_version');
		$this->doMakeFieldreadOnly('base_url');
		$this->doMakeFieldreadOnly('compression');

		$this->IcmsPersistableSeoObject();
	}

	/**
	 * Overriding the IcmsPersistableObject::getVar method to assign a custom method on some
	 * specific fields to handle the value before returning it
	 *
	 * @param str $key key of the field
	 * @param str $format format that is requested
	 * @return mixed value of the field that is requested
	 */
	public function getVar($key, $format = 's') {
		if ($format == 's' && in_array($key, array ('repository_name','base_url'))) {
			return call_user_func(array ($this,	$key));
		}
		return parent :: getVar($key, $format);
	}

	/**
	 * Ensure entities are escaped before sending to XML processor
	 */
	public function repository_name() {
		$repositoryName = htmlspecialchars(html_entity_decode($this->getVar('repository_name', 'e'),
			ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $repositoryName;
	}

	/**
	 * Ensure entities are escaped before sending to XML processor
	 */
	public function base_url() {
		$baseURL = htmlspecialchars(html_entity_decode($this->getVar('base_url', 'e'), ENT_QUOTES,
			'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $baseURL;
	}

	/**
	 * Generates a standard header for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_header() {
		$header = '';
		$timestamp = time();

		$timestamp = gmdate(DATE_ISO8601, $timestamp); // convert timestamp to UTC format
		$timestamp = str_replace('+0000', 'Z', $timestamp); // UTC designator 'Z' is OAI spec

		// build header

		$header .= '<?xml version="1.0" encoding="UTF-8" ?>';
		$header .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
            http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
		$header .= '<responseDate>' . $timestamp . '</responseDate>'; // must be UTC timestamp
		return $header;
	}

	/**
	 * Generates a standard footer for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_footer() {
		$footer ='</OAI-PMH>';
		return $footer;
	}

	////////// OPEN ARCHIVE INITIATIVE METHODS - MINIMAL IMPLEMENTATION AS PER THE GUIDELINES //////

	/**
	 * Returns basic information about the respository
	 *
	 * @return string
	 */
	public function identify() {
		// input validation: none required
		// throws: badArgument (how? no arguments are accepted so there is nothing to test for)
		$response = $deletedRecord = '';

		$response = $this->oai_header();
		$response .= '<request verb="Identify">' . $this->getVar('base_url') . '</request>' .
			'<Identify>' .
			'<repositoryName>' . $this->getVar('repository_name') . '</repositoryName>' .
			'<baseURL>' . $this->getVar('base_url') . '</baseURL>' .
			'<protocolVersion>' .  $this->getVar('protocol_version') . '</protocolVersion>' .
			'<adminEmail>' . $this->getVar('admin_email') . '</adminEmail>' .
			'<earliestDatestamp>' . $this->getVar('earliest_date_stamp') . '</earliestDatestamp>' .
			'<deletedRecord>' .  $this->getVar('deleted_record') . '</deletedRecord>' .
			'<granularity>' . $this->getVar('granularity') . '</granularity>' .
			'<compression>' . $this->getVar('compression') . '</compression>' .
			'</Identify>';
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (spec/XML requirement), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns information about the available metadata formats this repository supports (only oai_dc)
	 *
	 * @param string $identifier
	 * @return string
	 */
	public function listMetadataFormats($identifier = null) {

		// accepts an optional identifier to enquire about formats available for a particular record
		// throws badArgument (how? there are no required arguments; if identifier is wrong the
		// the appropriate error = idDoesNotExist
		// throws noMetadataFormats (not necessary to implement, as oai_dc is hardwired and native
		// for everything)

		$response = '';
		$valid = true;

		$response = $this->oai_header();
		$response .= '<request verb="ListMetadataFormats"';
		if (!empty($identifier)) {
			$response .= ' identifier="' . $identifier . '"';
		}

		$response .= '>' . $this->getVar('base_url') . '</request>';

		// check if optional identifier is set, if so this request is regarding a particular record

		if (empty($identifier)) {

			// This archive only supports unqualified Dublin Core as its native format
			$response .= '<ListMetadataFormats>';
			$response .= '<metadataFormat>';
			$response .= '<metadataPrefix>' . $this->getVar('metadata_prefix') . '</metadataPrefix>';
			$response .= '<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>';
			$response .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>';
			$response .= '</metadataFormat>';
			$response .= '</ListMetadataFormats>';
		} else { // an optional identifier has been provided, just check it exists

			// only search for soundtracks that are set as i) online and ii) federated
			$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
				basename(dirname(dirname(__FILE__))), 'podcast');
			$criteria = icms_buildCriteria(array('oai_identifier' => $identifier,
				'status' => '1', 'federated' => '1'));

			// this should return an array with only one publication object
			$soundtrack_array = $podcast_soundtrack_handler->getObjects($criteria);

			// extract the publication object
			$soundtrackObj = array_shift($soundtrack_array);

			// if an object was in fact returned proceed to process
			if (!empty($soundtrackObj)) {
				if ($soundtrackObj->getVar('oai_identifier') == $identifier) {

					// This archive only supports unqualified Dublin Core as its native format
					$response .= '<ListMetadataFormats>';
					$response .= '<metadataFormat>';
					$response .= '<metadataPrefix>' . $this->getVar('metadata_prefix')
						. '</metadataPrefix>';
					$response .= '<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>';
					$response .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>';
					$response .= '</metadataFormat>';
					$response .= '</ListMetadataFormats>';
				}
			} else {
				// otherwise throw idDoesNotExist (record doesn't exist, or is offline, or not federated)
				$response .= $this->throw_error('idDoesNotExist', 'Record identifier does not exist');
			}
		}
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns multiple records (headers only), supports selective harvesting based on time ranges
	 *
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * 
	 * @return string
	 */
	public function listIdentifiers($metadataPrefix = null, $from = null, $until = null,
		$set = null, $resumptionToken = null) {

		$haveResults = false; // flag if any records were returned by query
		$rows = array();

		$response = $this->oai_header();
		// also modifies adds to $response
		$rows = $this->lookupRecords('ListIdentifiers', $response, $metadataPrefix, $from,
				$until, $set, $resumptionToken);

		// if an object was in fact returned proceed to process
		if (!empty($rows)) {
			$records = $datestamp = '';
			$haveResults = true;

			// generate the headers and spit out the xml
			foreach($rows as $soundtrack) {
				$datestamp = $this->timestamp_to_oaipmh_time($soundtrack['submission_time']);
				$records .= '<header>';
				$records .= '<identifier>' . $soundtrack['oai_identifier'] . '</identifier>';
				$records .= '<datestamp>' . $datestamp . '</datestamp>';
				$records .= '</header>';
				unset($datestamp);
			}
		}
		if ($haveResults == true) {
			$response .= '<ListIdentifiers>' . $records . '</ListIdentifiers>';
		}

		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns the set structure of repository (sets are not supported in this implementation)
	 *
	 * @param string $resumptionToken
	 * @return string
	 */
	public function listSets($resumptionToken = null) {
		// accepts optional resumptionToken
		// throws badArgument (no need to implement, as resumption tokens are not accepted)

		$response = '';

		$response = $this->oai_header();
		$response .= '<request verb="ListSets">' . $this->getVar('base_url') . '</request>';

		// this archive does not support sets or resumption tokens so the response is fixed
		if (!empty($resumptionToken)) {
			// throws badResumptionToken
			$response .= $this->throw_error('badResumptionToken', 'This archive does not support '
				. 'resumption tokens, you get it all in one hit or not at all.');
		}
		// throws noSetHierarchy
		$response .= $this->throw_error('noSetHierarchy', 'This archive does not support sets.');
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns a single complete record based on its unique oai_identifier
	 *
	 * @param string $identifier
	 * @param strimg $metadataPrefix
	 * @return string
	 */
	public function getRecord($identifier = null, $metadataPrefix = null) {
		$record = $response = $dc_identifier = '';
		$valid = true;
		$schema = 'oai-identifier.xsd';
		$haveResult = false;
		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack' ,
			basename(dirname(dirname (__FILE__))), 'podcast');
		$podcast_programme_handler = icms_getModuleHandler('programme',
				basename(dirname(dirname (__FILE__))), 'podcast');
		$podcast_rights_handler = icms_getModuleHandler('rights',
			basename(dirname(dirname(__FILE__))), 'podcast');

		$response = $this->oai_header();
		$response .= '<request verb="GetRecord" identifier="' . $identifier
			. '" metadataPrefix="' . $metadataPrefix . '">' . $this->getVar('base_url')
			. '</request>';

		// input validation:
		if (empty($identifier) ) {
			// throws badArgument
			$valid = false;
			$response .= $this->throw_error('badArgument', 'Required argument missing: identifier');
		}

		if (empty($metadataPrefix)) {
			// throws badArgument
			$valid = false;
			$response .= $this->throw_error('badArgument',
				'Required arguments missing: metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				// throws cannotDisseminateFormat
				$valid = false;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// lookup record
		if ($valid == true) {

			// only select records that are marked as online AND federated
			$criteria = icms_buildCriteria(array('oai_identifier' => $identifier,
				'status' => '1', 'federated' => '1'));

			// this should return an array with only one publication object, because the
			// identifier is unique
			$soundtrack_array = $podcast_soundtrack_handler->getObjects($criteria);

			// extract the publication object
			$soundtrackObj = array_shift($soundtrack_array);

			// if an object was in fact returned proceed to process
			if (!empty($soundtrackObj)) {
				$haveResult = true;
				$soundtrack = $soundtrackObj->toArray();

				// lookup human readable equivalents of the keys
				// the dc_identifer must be a URL pointing at the source repository record
				// this is necessary to give credit to the source repository, and to encourage
				// sharing of records - anyone clicking on an identifier link in an external archive
				// will be bounced back to the source archive
				$soundtrack = $this->convert_shared_fields($soundtrack, $soundtrackObj);

				// format
				if ($soundtrack['format']) {
					$soundtrack['format'] = $soundtrackObj->get_mimetype();
				}

				// source (URL to source programme)
				if ($soundtrack['source']) {

					// lookup the source (programme) object
					$programmeObj = $podcast_programme_handler->get($soundtrackObj->getVar('source', 'e'));
					$programme = $programmeObj->toArray();
					$soundtrack['source'] = $programme['itemUrl'];
				}

				// relation - determined by looking for records with a common source
				if ($soundtrack['source']) {

					// search for soundtracks with the same source
					$criteria = new icms_db_criteria_Compo();
					$criteria->add(new icms_db_criteria_Item('source', $soundtrackObj->getVar('source', 'e')));
					$relatedList = $podcast_soundtrack_handler->getList($criteria);

					// delete the current soundtrack from the list to avoid duplicates
					unset($relatedList[$soundtrack['soundtrack_id']]);

					// prepare a list of related work URLs
					$related = array();
					foreach($relatedList as $key => $value) {
						$related[] = basename(dirname(dirname(__FILE__)))
							. 'soundtrack.php?soundtrack_id=' . $key;
					}

					$soundtrack['relation'] = $related;
					unset($related);
				}

				// rights
				if ($soundtrack['rights']) {
					$soundtrack['rights'] =	$soundtrackObj->rights(true);
				}

				$response .= '<GetRecord>';

				// this populates the record in oai_dc xml
				$response .= $this->record_to_xml($soundtrack);
				$response .= '</GetRecord>';
			}
			if ($haveResult == false) {
				// throws idDoesNotExist
				$response .= $this->throw_error('idDoesNotExist', 'Record ID does not exist, or '
					. 'has not been selected for federation');
			}
		}
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns multiple records (harvest entire repository, or within specified time range)
	 *
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * 
	 * @return string
	 */

	public function listRecords($metadataPrefix = null, $from = null, $until = null,
		$set = null, $resumptionToken = null) {

		$haveResults = false; // flags if any records were returned by query
		$soundtrackArray = array();
		
		$response = $this->oai_header();
		
		// also modifies adds to $response
		$soundtrackArray = $this->lookupRecords('ListRecords', $response, $metadataPrefix, $from,
				$until, $set, $resumptionToken);

		// if there are some soundtracks
		if (!empty($soundtrackArray)) {
			$records = $sql = $rows = '';
			$haveResults = true;
			$soundtrackObjArray = $rightsObjArray = $formatObjArray = $programmeObjArray = array();

			// prepare lookup arrays for converting soundtrack keys to human readable values
			// doing this outside of the main loop avoids massive numbers of redundant queries
			// objects use their ids as keys in the arrays for easy lookup

			$podcast_programme_handler = icms_getModuleHandler('programme',
				basename(dirname(dirname(__FILE__))), 'podcast');
			$programmeObjArray = $podcast_programme_handler->getObjects(null, true);

			$podcast_rights_handler = icms_getModuleHandler('rights',
				basename(dirname(dirname(__FILE__))), 'podcast');
			$rightsObjArray = $podcast_rights_handler->getObjects(null, true);

			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');			
			$mimetypeObjArray = $system_mimetype_handler->getObjects(null, true);

			// include the build in mimetype lookup list
			$mimetype_list = icms_Utils::mimetypes();

			// process each publication and generate XML output
			foreach($soundtrackArray as $soundtrackObj) {

				$soundtrack = $soundtrackObj->toArrayWithoutOverrides();

				// convert fields to human readable
				$soundtrack = $this->convert_shared_fields($soundtrack, $soundtrackObj);

				// source (URL to source programme)
				if (!empty($soundtrack['source'])) {
					$soundtrack['source'] = $programmeObjArray[$soundtrack['source']]->getItemLink(true);
				}

				// format
				if ($soundtrack['format']) {
					$soundtrack['format'] = $mimetypeObjArray[$soundtrack['format']]->getVar('extension', 'e');
					$soundtrack['format'] = $mimetype_list[$soundtrack['format']];
				}

				// rights
				if (!empty($soundtrack['rights'])) {
					$soundtrack['rights'] = $rightsObjArray[$soundtrack['rights']]->title();
				}

				// relation - determined by looking for records with a common source
				// DISABLED: Relation is VERY resource intensive to calculate if you have a large
				// collection. You can uncomment this code block to re-enable it, but don't try this
				// on shared web hosting, you need your own resources to run this efficiently.
				
				/*if ($soundtrack['source']) {
					$related_list = array();
					foreach ($soundtrackArray as $obj) {
						if($obj->getVar('source', 'e') == $soundtrackObj->getVar('source', 'e')) {
							$track = $obj->toArray();
							$related_list[$track['soundtrack_id']] = $track['itemUrl'];
						}
					}
					
					// delete the current soundtrack from the list to avoid duplicates
					unset($related_list[$soundtrack['soundtrack_id']]);

					$soundtrack['relation'] = $related_list;
					unset($related_list);
				}*/
				
				$records .= $this->record_to_xml($soundtrack);
			}
		}
		if ($haveResults == true) {
			$response .= '<ListRecords>' . $records . '</ListRecords>';
		} else {
			// if no publications are found, throw a noRecordsMatch error
			//$response .= $this->throw_error('noRecordsMatch', 'No records match the request '
				//. 'parameters');
		}
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		
		return $response;
	}

	/**
	 * Returns a fixed response (error message) to any non-recognised verb parameter
	 * @return string
	 */
	public function BadVerb() {
		$response = '';

		$response = $this->oai_header();
		$response .= '<request>' . $this->getVar('base_url') . '</request>';
		$response .= $this->throw_error('badVerb', 'Bad verb, request not compliant with '
			. 'OAIPMH specification');
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	////////// END OPEN ARCHIVES INITIATIVE API //////////

	// UTILITIES

	/**
	 * Retrieves soundtrack objects within search parameters, used byListIdentifiers() and ListRecords()
	 *
	 * @param string $requestVerb
	 * @param string $response
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * 
	 * @return array
	 */
	public function lookupRecords($requestVerb, &$response, $metadataPrefix = null, $from = null,
		$until = null, $set = null, $resumptionToken = null) {

		$valid = true; // if any part of the request is invalid, this will be set to false => exit
		$response .= '<request verb="' . $requestVerb . '" metadataPrefix="' . $metadataPrefix . '"';

		if (!empty($from)) {
			$response .= ' from="' . $from . '"';
		}

		if (!empty($until)) {
			$response .= ' until="' . $until . '"';
		}

		if (!empty($set)) {
			$response .= ' set="' . $set . '"';
		}

		if (!empty($resumptionToken)) {
			$response .= ' resumptionToken="' . $resumptionToken . '"';
		}
		$response .= '>' . $this->getVar('base_url') . '</request>';

		// VALIDATE INPUT

		// this archive does not support resumption tokens
		if (!empty($resumptionToken)) {
			// throws badResumptionToken
			$valid = false;
			$response .= $this->throw_error('badResumptionToken', 'This archive does not support '
				. 'resumption tokens, you get it all in one hit or not at all.');
		}
		if (!empty($set)) {
			// throws noSetHierarchy
			$valid = false;
			$response .= $this->throw_error('noSetHierarchy', 'This archive does not support sets');
		}

		if (empty($metadataPrefix)) {
			$valid = false;
			$response .= $this->throw_error('badArgument', 'Missing required argument: '
				. 'metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				$valid = false;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// validate from
		if (!empty($from)) {
			$valid_timestamp = '';
			$from = str_replace('Z', '', $from);
			$from = str_replace('T', ' ', $from);
			$valid_timestamp = $this->validate_datetime($from);

			if ($valid_timestamp == false) {
				$valid = $false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: from');
			} else {
				$valid_timestamp = $time = '';
				$time = $from;
				$valid_timestamp = $this->not_Before_Earliest_Datestamp($time);
				if ($valid_timestamp == false) {
					$valid = false;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: from '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// validate until
		if (!empty($until)) {
			$until = str_replace('Z', '', $until);
			$until = str_replace('T', ' ', $until);
			$valid_timestamp = $this->validate_datetime($until);
			if ($valid_timestamp == false) {
				$valid = $false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until');
			} else {
				$valid_timestamp = $time = '';
				$time = $until;
				$valid_timestamp = $this->not_Before_Earliest_Datestamp($time);
				if ($valid_timestamp == false) {
					$valid = false;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: until '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// check that from precedes until
		if (!empty($from) && !empty($until)) {
			$valid_timestamp = '';
			$valid_timestamp = $this->from_precedes_until($from, $until);
			if ($valid_timestamp == false) {
				$valid = false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until parameter '
					. 'precedes from parameter');
			}
		}

		// lookup all records within the specified time range
		if ($valid == true) {
			$from = strtotime($from);
			$until = strtotime($until);
			$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
					basename(dirname(dirname(__FILE__))), 'podcast');
			$sql = $rows = $fields = '';

			if ($requestVerb == 'ListRecords') {
				$fields = '*';
			} else {
				$fields = '`oai_identifier`,`submission_time`';
			}

			$sql = "SELECT " . $fields . " from " . icms::$xoopsDB->prefix('podcast_soundtrack') . " WHERE";
			if (!empty($from) || !empty($until)) {
				if (!empty($from)) {
					$sql .= " `submission_time` >= '" . $from . "'";
				}
				if (!empty($from) && !empty($until)) {
					$sql .= " AND";
				}
				if (!empty ($until)) {
					$sql .= " `submission_time` <= '" . $until . "'";
				}
				$sql .= " AND";
			}
			$sql .= " `federated` = '1' AND `status` = '1' ";

			$soundtrackArray = array();

			if ($requestVerb == 'ListRecords') {
				$soundtrackArray = $podcast_soundtrack_handler->getObjects(null, true, true, $sql);
			} else {
				$soundtrackArray = $this->handler->query($sql);
			}

			// if an object was in fact returned proceed to process
			if (empty($soundtrackArray)) {
				// throw noRecordsMatch
				$response .= $this->throw_error('noRecordsMatch', 'No records match the request '
					. 'parameters');
			}
			return $soundtrackArray;
		}
	}

	/**
	 * Converts common fields to human readable
	 *
	 * @param mixed array $soundtrack
	 * @param obj $soundtrackObj
	 * @return mixed Array $soundtrack
	 */
	public function convert_shared_fields($soundtrack, $soundtrackObj) {

		// dc_identifier - a URL back to the original resource / source archive
		$soundtrack['identifier'] = $soundtrack['itemUrl'];

		// timestamp
		$soundtrack['submission_time'] = strtotime($soundtrack['submission_time']);

		// type is fixed as per Dublin Core Type Vocabulary as all objects are sound
		$soundtrack['type'] = 'Sound';

		// creator
		if ($soundtrack['creator']) {
			$creators = $soundtrackObj->getVar('creator', 'e');
			$creators = explode('|', $creators);
			$soundtrack['creator'] = $creators;
		}

		// language - ISO 639-1 two letter codes
		if ($soundtrack['language']) {
			$soundtrack['language'] = $soundtrackObj->getVar('language', 'e');
		}

		return $soundtrack;
	}

	/**
	 * Utility function for displaying error messages to bad OAIPMH requests
	 *
	 * @param string $error
	 * @param string $message
	 * @return string
	 */
	public function throw_error($error, $message) {

		$response = '';

		switch ($error) {
			case "badArgument":
				$response = '<error code="badArgument">' . $message . '</error>';
				break;

			case "cannotDisseminateFormat":
				$response = '<error code="cannotDisseminateFormat">' . $message . '</error>';
				break;

			case "idDoesNotExist":
				$response = '<error code="idDoesNotExist">' . $message . '</error>';
				break;

			case "badResumptionToken":
				$response = '<error code="badResumptionToken">' . $message . '</error>';
				break;

			case "noSetHierarchy":
				$response = '<error code="noSetHierarchy">' . $message . '</error>';
				break;

			case "noMetadataFormats":
				$response = '<error code="noMetadataFormats">' . $message . '</error>';
				break;

			case "noRecordsMatch":
				$response = '<error code="noRecordsMatch">' . $message . '</error>';
				break;

			case "badVerb":
				$response = '<error code="badVerb">' . $message . '</error>';
				break;
		}
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Template for converting a single database record to OAIPMH spec XML
	 *
	 * Generates the output for each record.
	 * 
	 * @param string $record
	 * @return string $xml
	 */
	public function record_to_xml($record) {

		// initialise
		$xml = $datestamp = '';
		$dublin_core_fields = array(
			'title',
			'identifier',
			'creator',
			'date',
			'type',
			'format',
			'relation',
			'description',
			'subject',
			'language',
			'publisher',
			'coverage',
			'rights',
			'source');

		// adjust the datestamp to match the OAI spec
		$datestamp = $record['submission_time'];
		$datestamp = $this->timestamp_to_oaipmh_time($record['submission_time']);

		// add a trailing space before closing paragraph tags to separate sentences when tags removed
		$record['description'] = str_replace('.<', '. <', $record['description']);

		// remove any html tags from the description field of the record
		$record['description'] = trim(strip_tags($record['description']));

		// encode entities before sending to XML processing
		//foreach ($record as $key => &$value) {
		//			$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'),
		//				ENT_NOQUOTES, 'UTF-8');
			//	}
		// build and populate template
		$xml .= '<record>';
		$xml .= '<header>';
		$xml .= '<identifier>' . $record['oai_identifier'] . '</identifier>';
		$xml .= '<datestamp>' . $datestamp . '</datestamp>';
		$xml .= '</header>';
		$xml .= '<metadata>';
		$xml .= '<oai_dc:dc
			xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
			http://www.openarchives.org/OAI/2.0/oai_dc.xsd">';

		////////// iterate through optional and repeatable Dublic Core fields //////////

		foreach($dublin_core_fields as $dc_field) {
			$dc_value = '';
			$dc_value = $record[$dc_field];
			if (!empty($dc_value)) {
				if (is_array($dc_value)) {
					foreach($dc_value as $subvalue) {
						$subvalue = htmlspecialchars(html_entity_decode($subvalue, ENT_QUOTES,
							'UTF-8'), ENT_NOQUOTES, 'UTF-8');
						$xml .= '<dc:' . $dc_field . '>' . $subvalue . '</dc:' . $dc_field . '>';
					}
				} else {
					$dc_value = htmlspecialchars(html_entity_decode($dc_value, ENT_QUOTES, 'UTF-8'),
						ENT_NOQUOTES, 'UTF-8');
					$xml .= '<dc:' . $dc_field . '>' . $dc_value . '</dc:' . $dc_field . '>';
				}
			}
		}
		$xml .= '</oai_dc:dc>';
		$xml .= '</metadata>';
		$xml .= '</record>';
		return $xml;
	}

	/**
	 * Checks that a requested time range does not occur before the repository's earliest timestamp
	 *
	 * @param string $time
	 * @return bool
	 */

	public function not_Before_Earliest_Datestamp($time) {

		$request_date_stamp = $time;
		$earliest_date_stamp = $this->getEarliestDateStamp();

		$request_date_stamp = str_replace('Z', '', $request_date_stamp);
		$request_date_stamp = str_replace('T', ' ', $request_date_stamp);
		$request_date_stamp = strtotime($request_date_stamp);
		$earliest_date_stamp = str_replace('Z', '', $earliest_date_stamp);
		$earliest_date_stamp = str_replace('T', ' ', $earliest_date_stamp);
		$earliest_date_stamp = strtotime($earliest_date_stamp);

		if ($request_date_stamp >= $earliest_date_stamp) {
			$validity = true;
		} else {
			$validity = false;
		}
		return $validity;
	}

	/**
	 * Validates the datetime syntax, also checks that data does not exceed reasonable values
	 *
	 * @param string $time
	 * @return bool
	 */
	public function validate_datetime($time) {
		$valid = true;

		// DO NOT BREAK THIS LINE OR YOU WILL BREAK DATETIME VALIDATION
		if (preg_match("/^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/", $time)) {
			$valid = true;
		} else {
			$valid = false;
		}

		////////// EXPLANATION OF THE DATETIME VALIDATION REGEX //////////
		//
		// This is effectively the same as the readable expression:
		// (1000-3999)-(1-12)-(1-31) (00-24):(00-59):(00-59)
		//
		// Broken down:
		// Year: ([1-3][0-9]{3,3}) Matches 1000 to 3999, easily changed.
		// Month: (0?[1-9]|1[0-2]) Matches 1 to 12
		// Day: (0?[1-9]|[1-2][0-9]|3[0-1]) Matches 1 to 31
		// Hour: ([0-1][0-9]|2[0-4]) Matches 00 to 24
		// Minute: ([0-5][0-9]) Matches 00 to 59
		// Second: ([0-5][0-9]) Same as above.
		//
		// Notes:
		// The "?" allows for the preceding digit to be optional,
		// ie: "2008-1-22" and "2008-01-22" are both valid.
		// The "^" denies input before the year, so " 2008" or "x2008" is invalid.
		// The "$" works to deny ending input.
		//
		// From: http://www.webdeveloper.com/forum/showthread.php?t=178277
		//
		////////////////////////////////////////////////////////////////

		return $valid;
	}


	/**
	 * Checks that the OAIPMH $from parameter precedes the $until parameter
	 *
	 * Used by ListIdentifiers() and ListRecords()
	 *
	 * @param string $from
	 * @param string $until
	 * @return boolean
	 */
	public function from_precedes_until ($from, $until) {

		$valid = false;
		$from_datetime = $until_datetime = '';

		// convert to unix timestamps for easy comparison
		$from_datetime = strtotime($from);
		$until_datetime = strtotime($until);

		if ($from_datetime < $until_datetime) {
			$valid = true;
		}
		
		return $valid;
	}

	/**
	 * Forces the XML response to be sent in UTF8, converts it in some other character set.
	 *
	 * @param string $data
	 * @return string
	 */
	public function data_to_utf8($data) {
		$converted = '';

		if (_CHARSET !== 'utf-8') {
			$charset = strtoupper(_CHARSET);
			$converted = iconv($charset, 'UTF-8', $data);
		} else {
			return $data;
		}
	}

	/**
	 * Retrieves the earliest content object associated with this Archive
	 * 
	 * @return string
	 */
	public function getEarliestDateStamp() {
		$earliest_date_stamp = $this->getVar('earliest_date_stamp', 'e');
		$earliest_date_stamp = $this->timestamp_to_oaipmh_time($earliest_date_stamp);
		return $earliest_date_stamp;
	}
	
	/**
	 * Converts a timestamp into the OAIPMH datetime format
	 *
	 * @param string $timestamp
	 * @return string
	 */
	public function timestamp_to_oaipmh_time($timestamp) {
		$format = 'Y-m-d\TH:i:s\Z';
		$oai_date_time = date($format, $timestamp);
		return $oai_date_time;
	}
}