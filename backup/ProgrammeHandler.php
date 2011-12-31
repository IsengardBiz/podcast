<?php

/**
 * Class representing Podcast programme handler objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

class PodcastProgrammeHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'programme', 'programme_id', 'title',
			'description', 'podcast', 'cover');
	}

	/**
	 * Returns a list of programmes
	 *
	 * @return mixed
	 */
	public function getProgrammes() {
		return $this->getList();
	}

	/**
	* Returns options for programme sort field
	* 
	* @return mixed
	*/

	public function getSortOptions() {
		return array(
		0 => 'Date (descending)',
		1 => 'Date (ascending)',
		2 => 'Alphabetical (ascending)',
		3 => 'Alphabetical (descending)');
	}

	/*
     * Used to assemble a unique oai_identifier for a record, as per the OAIPMH specs.
     *
     * The identifier is comprised of a metadata prefix, namespace (domain) and timestamp. It should
     * uniquely identify the record within a one-second resolution. You MUST NOT change the
     * oai_identifier once it is set, it is used to identify duplicate records that may be held
     * by multiple sites, and it prevents metadata harvesters from importing duplicates.
     *
     * @return string
	*/
	public function getMetadataPrefix() {
		$metadataPrefix = 'oai';
		return $metadataPrefix;
	}

	/**
	 * Used to assemble a unique identifier for a record, as per the OAIPMH specs.
	 *
	 * @return string
	 */
	public function getNamespace() {
		$namespace = '';
		$namespace = ICMS_URL;
		$namespace = str_replace('http://', '', $namespace);
		$namespace = str_replace('https://', '', $namespace);
		$namespace = str_replace('www.', '', $namespace);
		return $namespace;
	}

	/**
	 * Used to assemble a unique identifier for a record, as per the OAIPMH specs
	 *
	 * @return string 
	 */
	public function setOaiId() {
		$id = '';
		$prefix = $this->getMetadataPrefix();
		$namespace = $this->getNamespace();
		$timestamp = time();
		$id = $prefix . ":" . $namespace . ":" . $timestamp;
		return $id;
	}

	/**
	 * Sends notifications to subscribers, triggered after programme is inserted or updated
	 *
	 * @param obj $obj PodcastProgramme object
	 * @return obj
	 */
	protected function afterSave(& $obj) {
		if (!$obj->getVar('programme_notification_sent')) {
			$obj->sendNotifProgrammePublished();
			$obj->setVar('programme_notification_sent', true);
			$this->insert ($obj);
		}
		return true;
	}

	/**
	 * Deletes notification subscriptions after an object is deleted
	 *
	 * @global mixed $icmsModule
	 * @param obj $obj PodcastProgramme object
	 * @return bool
	 */
	protected function afterDelete(& $obj) {
		global $icmsModule;
		$notification_handler =& xoops_gethandler('notification');
		$module_handler = xoops_getHandler('module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$category = 'programme';
		$item_id = $obj->id();

		// delete programme bookmarks
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);

		return true;
	}
}