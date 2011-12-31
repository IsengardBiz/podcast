<?php

/**
 * Class representing Archive handler objects and responding to OAIPMH requests
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2010
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		archive
 * @version		$Id$
 */

class PodcastArchiveHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'archive', 'archive_id', 'repository_name',
			'base_url', 'podcast');
	}

	// INITIALISE DEFAULT ARCHIVE VALUES BECAUSE MOST OF THESE ARE FIXED

	/**
	 * Returns the only metadataprefix supported by this repository (oai_dc)
	 * @return string
	 */
	public function setMetadataPrefix() {
		return 'oai_dc';
	}

	/**
	 * One of several functions used to build a unique identifier for each record
	 * @return string
	 */
	public function setNamespace() {
		$namespace = ICMS_URL;
		$namespace = str_replace('http://', '', $namespace);
		$namespace = str_replace('https://', '', $namespace);
		$namespace = str_replace('www.', '', $namespace);
		return $namespace;
	}

	/**
	 * Returns the timestamp granularity supported by this repository in OAIPMH datetime format
	 *
	 * This implementation supports seconds-level granularity, which is the maximum.
	 *
	 * @return string
	 */
	public function setGranularity() {
		return 'YYYY-MM-DDThh:mm:ssZ';
	}

	/**
	 * Returns whether this repository supports deleted record tracking (no)
	 *
	 * @return string
	 */
	public function setDeletedRecord() {
		return 'no';
	}

	/**
	 * Sets the earliest datestamp attribute for this repository, using the Unix epoch as default
	 *
	 * If there are records in the repository, the oldest datestamp will be reported as that of
	 * the oldest record. For safety reasons, this will include offline and non-federated records
	 * so if a records online or federation status changes, nothing will be broken. If there are
	 * no records, the beginning of the Unix epoch will be used as the earliest datestamp value.
	 *
	 * @return string
	 */
	public function setEarliestDatestamp() {
		$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
			basename(dirname(dirname(__FILE__))), 'podcast');
		$criteria = new icms_db_criteria_Compo();
		$criteria->setSort('submission_time');
		$criteria->setOrder('ASC');
		$criteria->setLimit(1);
		$soundtrackObj = $podcast_soundtrack_handler->getObjects($criteria);
		$oldest_soundtrack = array_shift($soundtrackObj);
		if (!empty($oldest_soundtrack)) {
			$earliest_timestamp = $this->timestamp_to_oaipmh_time(
				$oldest_soundtrack->getVar('submission_time', 'e'));
			return $earliest_timestamp;
		} else {
			return '1970-01-01T00:00:00Z';
		}
	}

	/**
	 * Returns the repository's admin email address, as per the OAIPMH spec requirements
	 *
	 * @global mixed $icmsConfig
	 * @return string
	 */
	public function setAdminEmail() {
		global $icmsConfig;
		return $icmsConfig['adminmail'];
	}

	/**
	 * Returns the OAIPMH version in use by this repository (2.0, the current version)
	 * @return string
	 */
	public function setProtocolVersion() {
		return '2.0';
	}

	/**
	 * Returns the name of the repository, default value is the site name in global preferences.
	 *
	 * A different respository name can be set within the Archive object.
	 *
	 * @global mixed $icmsConfig
	 * @return string
	 */
	public function setRepositoryName() {
		global $icmsConfig;
		$repository_name = $icmsConfig['sitename'] . ' - ' . $icmsConfig['slogan'];
		return $repository_name;
	}

	/**
	 * Returns the base URL, which is the URL against which OAIPMH requests should be sent
	 *
	 * @global mixed $icmsConfig
	 * @global mixed $icmsModule
	 * @return string
	 */
	public function setBaseUrl() {
		global $icmsConfig;
		$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
		$base_url = ICMS_URL . '/modules/' . $podcastModule->getVar('dirname') . '/oaipmh_target.php';
		return $base_url;
	}

	/**
	 * Returns the compression scheme(s) supported by this repository (only gzip)
	 *
	 * @return string
	 */
	public function setCompression() {
		return 'gzip';
	}

	/**
	 * Converts a timestamp to the OAIPMH datetime format as per the spec
	 * @param string $timestamp
	 * @return string
	 */
	public function timestamp_to_oaipmh_time($timestamp) {
		$format = 'Y-m-d\TH:i:s\Z';
		$oai_date_time = date($format, $timestamp);
		return $oai_date_time;
	}
}

