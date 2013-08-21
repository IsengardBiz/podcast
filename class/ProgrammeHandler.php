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
	 * Flush the cache for the Podcast module after adding, editing or deleting a PROGRAMME.
	 * 
	 * Ensures that the index/block/single view cache is kept updated if caching is enabled.
	 * 
	 * @global array $icmsConfig
	 * @param type $obj 
	 */
	protected function clear_cache(& $obj)
	{
		global $icmsConfig;
		$cache_status = $icmsConfig['module_cache'];
		$module = icms::handler("icms_module")->getByDirname("podcast", TRUE);
		$module_id = $module->getVar("mid");
			
		// Check if caching is enabled for this module. The cache time is stored in a serialised 
		// string in config table (module_cache), and is indicated in seconds. Uncached = 0.
		if ($cache_status[$module_id] > 0)
		{			
			// As PHP's exec() function is often disabled for security reasons
			try 
			{	
				// Programme index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php^* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fstart* -delete &");
				
				// New index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fnew.php^* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fnew.php%3F* -delete &");
				
				// Blocks
				exec("find " . ICMS_CACHE_PATH . "/" . "blk_podcast* -delete &");
				
				// Individual programme page
				if (!$obj->isNew())
				{
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fprogramme_id%3D" 
							. $obj->getVar('programme_id', 'e') . "%26* -delete &");
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fprogramme_id%3D" 
							. $obj->getVar('programme_id', 'e') . "^* -delete &");
				}				
			}
			catch(Exception $e)
			{
				$obj->setErrors($e->getMessage());
			}
		}		
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
		
		// Clear cache
		$this->clear_cache(& $obj);	
		
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
		$notification_handler = icms::handler('icms_data_notification');
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$category = 'programme';
		$item_id = $obj->id();

		// delete programme bookmarks
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);
		
		// Clear cache
		$this->clear_cache(& $obj);	

		return true;
	}
}