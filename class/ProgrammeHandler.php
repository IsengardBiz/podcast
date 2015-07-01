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
	
	/*
	 * Counts the number of (online) programmes for a tag to support pagination controls
	 */
	public function getProgrammeCountForTag($tag_id)
	{
		// Sanitise the parameter
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		
		$podcastModule = $this->getModuleInfo();
		
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		$group_query = "SELECT count(*) FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `programme_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
				. " AND `item` = 'programme'";
		$result = icms::$xoopsDB->query($group_query);
		if (!$result) {
			echo 'Error';
			exit;
		}
		else {
			while ($row = icms::$xoopsDB->fetchArray($result)) {
				foreach ($row as $key => $count) {
					$programme_count = $count;
				}
			}
			return $programme_count;
		}
	}
	
	/*
	 * Retrieves a list of programmes for a given tag, formatted for user-side display
	 * 
	 * @return array programmes
	 */
	public function getProgrammesForTag($tag_id, $count, $start)
	{
		// Sanitise the parameters
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		$programme_count = isset($count) ? (int)$count : 0 ;
		$clean_start = isset($start) ? (int)$start : 0 ;
			
		$podcast_programme_summaries = array();
		$podcastModule = $this->getModuleInfo();
		
		$query = $rows = '';
		$linked_programme_ids = array();
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		
		// Build the query
		$query = "SELECT * FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `programme_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
				. " AND `item` = 'programme'";
		switch ($podcastModule->config['programmes_sort_preference']) {
			case "0": // sort programmes by title
				$query .= " ORDER BY `title` ASC";
				break;
			case "1": // sort programmes by submission date (ascending)
				$query .= " ORDER BY `submission_time` ASC";
				break;
			case "2": // sort programmes by submission date (descending)
				$query .= " ORDER BY `submission_time` DESC";
				break;
		}
		
		// Execute thequery and process
		$query .= " LIMIT " . $clean_start . ", " . $podcastModule->config['number_programmes_per_page'];
		$result = icms::$xoopsDB->query($query);
		if (!$result) {
			echo 'Error';
			exit;
		} else {
			// Retrieve programmes as objects, with id as key, and prepare for display
			$rows = $this->convertResultSet($result, TRUE, TRUE);
			foreach ($rows as $programme) {
				$podcast_programme_summaries[$programme->getVar('programme_id')] = $programme;
			}
			return $podcast_programme_summaries;
		}
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
	 * Sends notifications to subscribers, triggered after programme is inserted or updated, handles
	 * taglinks
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
		
		// Handle taglinks. Only update the taglinks if the object is being updated from the 
		// add/edit form (POST). Database updates are not permitted from GET requests and will 
		// trigger an error
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = '';
			 $sprockets_taglink_handler = icms_getModuleHandler('taglink',
					 $sprocketsModule->getVar('dirname'), $sprocketsModule->getVar('dirname'), 
					 'sprockets');
			 // Store tags
			 $sprockets_taglink_handler->storeTagsForObject($obj, 'tag', '0');
		}
		
		// Clear cache
		$this->clear_cache($obj);	
		
		return true;
	}

	/**
	 * Deletes taglinks and notification subscriptions after an object is deleted
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
		
		// Delete taglinks
		$sprocketsModule = $notification_handler = $module_handler = $module = $module_id
                    = $tag = $item_id = '';
		$sprocketsModule = icms_getModuleInfo('sprockets');

		// Delete taglinks
		if (icms_get_module_status("sprockets")) {
			 $sprockets_taglink_handler = icms_getModuleHandler('taglink',
					   $sprocketsModule->getVar('dirname'), 'sprockets');
			 $sprockets_taglink_handler->deleteAllForObject($obj);
		}

		// delete programme bookmarks
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);
		
		// Clear cache
		$this->clear_cache($obj);	

		return true;
	}
}