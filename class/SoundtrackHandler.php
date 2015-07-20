<?php

/**
 * Class representing Podcast soundtrack hander objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

class PodcastSoundtrackHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'soundtrack', 'soundtrack_id', 'title', 'description', 'podcast');
	}

	/**
	 * Returns a list of soundtracks with ID as key
	 *
	 * @return array
	 */
	public function getSoundtracks() {
		return $this->getList();
	}
	
	/*
	 * Counts the number of (online) soundtracks for a tag to support pagination controls
	 * 
	 * @param int $tag_id 
	 * 
	 * @return int
	 */
	public function getSoundtrackCountForTag($tag_id)
	{
		// Sanitise the parameter
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		
		$podcastModule = $this->getModuleInfo();
		
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		$group_query = "SELECT count(*) FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `soundtrack_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
				. " AND `item` = 'soundtrack'";
		$result = icms::$xoopsDB->query($group_query);
		if (!$result) {
			echo 'Error';
			exit;
		}
		else {
			while ($row = icms::$xoopsDB->fetchArray($result)) {
				foreach ($row as $key => $count) {
					$soundtrack_count = $count;
				}
			}
			return $soundtrack_count;
		}
	}
	
	/*
	 * Retrieves a list of soundtracks for a given tag, formatted for user-side display
	 * 
	 * @param int $tag_id
	 * @param int $count
	 * @param int $start
	 * 
	 * @return array soundtracks
	 */
	public function getSoundtracksForTag($tag_id, $count, $start, $as_object = TRUE)
	{
		// Sanitise the parameters
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		$soundtrack_count = isset($count) ? (int)$count : 0 ;
		$clean_start = isset($start) ? (int)$start : 0 ;
			
		$podcast_soundtrack_summaries = array();
		$podcastModule = $this->getModuleInfo();
		
		$query = $rows = '';
		$linked_soundtrack_ids = array();
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		
		// Build the query
		$query = "SELECT * FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `soundtrack_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $podcastModule->getVar('mid') . "'"
				. " AND `item` = 'soundtrack'"
				. " ORDER BY `submission_time` DESC";
		
		// Execute the query and process
		$query .= " LIMIT " . $clean_start . ", " . $podcastModule->config['new_items'];
		$result = icms::$xoopsDB->query($query);
		if (!$result) {
			echo 'Error';
			exit;
		} else {
			// Retrieve soundtracks as objects, with id as key, and prepare for display
			if ($as_object) {
				$rows = $this->convertResultSet($result, TRUE, TRUE);
			} else {
				$rows = $this->convertResultSet($result, TRUE, FALSE);
			}
			
			return $rows;
		}
	}

	/**
     * Provides global search functionality for Podcast module, only searches soundtracks presently
     *
	 * @param int $queryarray
	 * @param string $andor
	 * @param int $limit
	 * @param int $offset
	 * @param int $userid
	 * 
     * @return array
	 */
	public function getSoundtracksForSearch($queryarray, $andor, $limit, $offset, $userid)
	{
		$count = $results = '';
		$criteria = new icms_db_criteria_Compo();

		if ($userid != 0) {
			$criteria->add(new icms_db_criteria_Item('submitter', $userid));
		}
		if ($queryarray) {
			$criteriaKeywords = new icms_db_criteria_Compo();
			for ($i = 0; $i < count($queryarray); $i++) {
				$criteriaKeyword = new icms_db_criteria_Compo();
				$criteriaKeyword->add(new icms_db_criteria_Item('title', '%' . $queryarray[$i] . '%',
					'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('description', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('publisher', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset ($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		$criteria->add(new icms_db_criteria_Item('online_status', true));
		
		/*
		 * Improving the efficiency of search
		 * 
		 * The general search function is not efficient, because it retrieves all matching records
		 * even when only a small subset is required, which is usually the case. The full records 
		 * are retrieved so that they can be counted, which is used to display the number of 
		 * search results and also to set up the pagination controls. The problem with this approach 
		 * is that a search generating a very large number of results (eg. > 650) will crash out. 
		 * Maybe its a memory allocation issue, I don't know.
		 * 
		 * A better approach is to run two queries: The first a getCount() to find out how many 
		 * records there are in total (without actually wasting resources to retrieve them), 
		 * followed by a getObjects() to retrieve the small subset that are actually needed. 
		 * Due to the way search works, the object array needs to be padded out 
		 * with the number of elements counted in order to preserve 'hits' information and to construct
		 * the pagination controls. So to minimise resources, we can just set their values to '1'.
		 * 
		 * In the long term it would be better to (say) pass the count back as element[0] of the 
		 * results array, but that will require modification to the core and will affect all modules.
		 * So for the moment, this hack is convenient.
		 */
		
		// Count the number of search results WITHOUT actually retrieving the objects
		$count = $this->getCount($criteria);
		
		$criteria->setStart($offset);
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		
		// Retrieve the subset of results that are actually required.
		// Problem: If show all results # < shallow search #, then the all results preference is 
		// used as a limit. This indicates that shallow search is not setting a limit! The largest 
		// of these two values should always be used
		if (!$limit) {
			global $icmsConfigSearch;
			$limit = $icmsConfigSearch['search_per_page'];
		}
		
		$criteria->setLimit($limit);
		$results = $this->getObjects($criteria, FALSE, TRUE);
		
		// Pad the results array out to the counted length to preserve 'hits' and pagination controls.
		// This approach is not ideal, but it greatly reduces the load for queries with large result sets
		$results = array_pad($results, $count, 1);
		
		return $results;
	}

	/**
	 * Returns a list of soundtracks in a programme
	 * 
	 * @param type $start
	 * @param type $limit
	 * @param type $programme_id
	 * @param type $sort_order
	 * 
	 * @return array soundtrack objects 
	 */
	public function getProgrammeSoundtracks($start = 0, $limit = 10, $programme_id = false,
		$sort_order = false) {
		$criteria = $this->getPodcastCriteria($start, $limit, $programme_id, $sort_order);
		$ret = $this->getObjects($criteria, true, true);
		return $ret;
	}

	/**
	 * Returns a list of mimetypes that Podcast is authorised to use, hiding certain image types
	 *
	 * Used to make a dropdown list of audio mimetypes for use in the soundtrack submission form.
	 * Due to limitations in core mimetype handling functionality, unwanted ones (eg image
	 * mimetypes required to upload album art) need to be manually removed. It's a bit ugly
	 * but it works, so long as the admin has made sensible choices about what mimetypes the
	 * module is authorised to use.
	 * 
	 * @return string 
	 */
	public function getModuleMimeTypes() {
		$moduleMimetypes = array();
		$hiddenMimetypes = array('png', 'gif', 'jpg', 'jpeg', 'm3u');
		$criteria = new  icms_db_criteria_Compo();
		$criteria->add(new  icms_db_criteria_Item('dirname', '%' . basename(dirname(dirname(__FILE__)))
			. '%', 'LIKE'));
		$sql = 'SELECT mimetypeid, dirname, extension FROM '
			. icms::$xoopsDB->prefix('system_mimetype');
		$rows = $this->query($sql, $criteria);
		if (count($rows) > 0) {
			foreach($rows as $row) {
				if (!in_array($row['extension'], $hiddenMimetypes)) {
					$moduleMimetypes[$row['mimetypeid']] = $row['extension'];
				}
			}
			asort($moduleMimetypes);
		} else {
			$moduleMimetypes[0] = '---';
		}
		return $moduleMimetypes;
	}

	/**
	 * Filters searches for soundtracks
	 * @param int $start
	 * @param int $limit
	 * @param int $programme_id
	 * @param int $sort_order
	 * 
	 * @return object $criteria 
	 */
	public function getPodcastCriteria($start = 0, $limit = 10, $programme_id = false,
		$sort_order = false) {

		$criteria = new icms_db_criteria_Compo();
		if ($start) {
			$criteria->setStart($start);
		}
		if ($limit) {
			$criteria->setLimit(intval($limit));
		}
		if ($programme_id) {
			$criteria->add(new icms_db_criteria_Item('source', $programme_id));
		}
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$criteria->setSort('date');
		if ($sort_order) {
			$criteria->setOrder('ASC');
		} else {
			$criteria->setOrder('DESC');
		}
		return $criteria;
	}

	/**
     * Used to assemble a unique oai_identifier for a record, as per the OAIPMH specs.
     *
     * The identifier is comprised of a metadata prefix, namespace (domain) and timestamp. It should
     * uniquely identify the record within a one-second resolution. You MUST NOT change the
     * oai_identifier once it is set, it is used to identify duplicate records that may be held
     * by multiple sites, and it prevents metadata harvesters from importing duplicates.
	 * @return string 
	 */
	public function getMetadataPrefix() {
		$metadataPrefix = 'oai';
		return $metadataPrefix;
	}

	/**
	 * Used to assemble a unique oai_identifier for a record, as per the OAIPMH specs.
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

	// METHODS FOR ADMIN TABLE FILTERS

	/**
	 * Returns an array of mimetype extensions for the admin side format filter, using the id as key
	 *
	 * @return array 
	 */
	public function format_filter() {
		// only display mimetypes actually in use
		$mimetype_id_string = $sql = $rows = '';
		$mimetypeArray = array();
		$criteria = null;

		$sql = 'SELECT DISTINCT `format` FROM ' . $this->table;
		$rows = $this->query($sql, $criteria);
		if (count($rows) > 0) {
			$mimetype_id_string = ' WHERE `mimetypeid` IN (';
			foreach($rows as $row) {
				$mimetype_id_string .= $row['format'] . ',';
			}
			$mimetype_id_string = rtrim($mimetype_id_string, ',');
			$mimetype_id_string .= ') ';
		}

		// use the distinct mimetype ids to get the relevant mimetype objects
		$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
		$criteria = new icms_db_criteria_Compo();
		$criteria->setSort('extension');
		$criteria->setOrder('ASC');
		$sql = 'SELECT * FROM ' . icms::$xoopsDB->prefix('system_mimetype') . $mimetype_id_string;
		$rows = $this->query($sql, $criteria);
		foreach($rows as $row) {
			$mimetypeArray[$row['mimetypeid']] = $row['extension'];
		}
		return $mimetypeArray;
	}

	/**
	 * Returns a list of programme names for the admin side programme (source) filter
	 *
	 * @return array 
	 */
	public function source_filter() {
		$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(dirname(__FILE__))),  'podcast');
		$programme_array = $podcast_programme_handler->getList();
		return $programme_array;
	}

	/**
	 * Returns a status filter for the admin side soundtrack table
	 *
	 * @return array
	 */
	public function status_filter() {
		return array(0 => _CO_PODCAST_SOUNDTRACK_OFFLINE, 1 => _CO_PODCAST_SOUNDTRACK_ONLINE);
	}

	/**
	 * Returns a federated filter for the admin side soundtrack table
	 *
	 * @return array
	 */
	public function federated_filter() {
		return array(0 => _CO_PODCAST_SOUNDTRACK_NO, 1 => _CO_PODCAST_SOUNDTRACK_YES);
	}

	/**
	 * Returns a rights filter for the admin side soundtrack table
	 *
	 * @return array 
	 */
	public function rights_filter() {
		$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');
		$rights_array = $sprockets_rights_handler->getList();
		return $rights_array;
	}

	/**
	 * Toggles the status or federation of a soundtrack
	 *
	 * @param int $soundtrack_id
	 * @param str $field
	 * @return int $visibility
	 */
	public function change_status($soundtrack_id, $field) {
		$visibility = '';
		$soundtrackObj = $this->get($soundtrack_id);
		if ($soundtrackObj->getVar($field, 'e') == true) {
			$soundtrackObj->setVar($field, 0);
			$visibility = 0;
		} else {
			$soundtrackObj->setVar($field, 1);
			$visibility = 1;
		}
		$this->insert($soundtrackObj, true);
		return $visibility;
	}
	
	/**
	 * Returns a list of document types (subset of the Dublin Core Type Vocabulary)
	 *
	 * @return array mixed
	 */
	public function getTypeOptions()
	{
		$options = array(
			'Sound' => 'Audio',
			'MovingImage' => 'Video'
		);
		
		return $options;
	}

	/**
     * Returns an array of languages using ISO 639-1 two-letter language codes as keys
     *
     * Accurate as of 29 September 2009.
	 *
	 * @return array
	 */
	public function getLanguage() {
		return array(
			0 => '---',
			"ab"=>"Abkhazian",
			"aa"=>"Afar",
			"af"=>"Afrikaans",
			"ak"=>"Akan",
			"sq"=>"Albanian",
			"am"=>"Amharic",
			"ar"=>"Arabic",
			"an"=>"Aragonese",
			"hy"=>"Armenian",
			"as"=>"Assamese",
			"av"=>"Avaric",
			"ae"=>"Avestan",
			"ay"=>"Aymara",
			"az"=>"Azerbaijani",
			"bm"=>"Bambara",
			"ba"=>"Bashkir",
			"eu"=>"Basque",
			"be"=>"Belarusian",
			"bn"=>"Bengali",
			"bh"=>"Bihari",
			"bi"=>"Bislama",
			"bs"=>"Bosnian",
			"br"=>"Breton",
			"bg"=>"Bulgarian",
			"my"=>"Burmese",
			"ca"=>"Catalan, Valencian",
			"km"=>"Central Khmer",
			"ch"=>"Chamorro",
			"ce"=>"Chechen",
			"ny"=>"Chichewa, Chewa, Nyanja",
			"zh"=>"Chinese",
			"cu"=>"Church Slavic, Old Slavonic, Church Slavonic, Old Bulgarian, Old Church Slavonic",
			"cv"=>"Chuvash",
			"kw"=>"Cornish",
			"co"=>"Corsican",
			"cr"=>"Cree",
			"hr"=>"Croatian",
			"cs"=>"Czech",
			"da"=>"Danish",
			"dv"=>"Divehi, Dhivehi, Maldivian",
			"nl"=>"Dutch, Flemish",
			"dz"=>"Dzongkha",
			"en"=>"English",
			"eo"=>"Esperanto",
			"et"=>"Estonian",
			"ee"=>"Ewe",
			"fo"=>"Faroese",
			"fj"=>"Fijian",
			"fi"=>"Finnish",
			"fr"=>"French",
			"ff"=>"Fulah",
			"gd"=>"Gaelic, Scottish Gaelic",
			"gl"=>"Galician",
			"lg"=>"Ganda",
			"ka"=>"Georgian",
			"de"=>"German",
			"gn"=>"Guaraní",
			"gu"=>"Gujarati",
			"ht"=>"Haitian, Haitian Creole",
			"ha"=>"Hausa",
			"hz"=>"Herero",
			"hi"=>"Hindi",
			"ho"=>"Hiri Motu",
			"hu"=>"Hungarian",
			"is"=>"Icelandic",
			"io"=>"Ido",
			"ig"=>"Igbo",
			"id"=>"Indonesian",
			"ia"=>"Interlingua (International Auxiliary Language Association)",
			"ie"=>"Interlingue, Occidental",
			"iu"=>"Inuktitut",
			"ik"=>"Inupiaq",
			"ga"=>"Irish",
			"it"=>"Italian",
			"ja"=>"Japanese",
			"jv"=>"Javanese",
			"kl"=>"Kalaallisut, Greenlandic",
			"kn"=>"Kannada",
			"kr"=>"Kanuri",
			"ks"=>"Kashmiri",
			"kk"=>"Kazakh",
			"ki"=>"Kikuyu, Gikuyu",
			"rw"=>"Kinyarwanda",
			"ky"=>"Kirghiz, Kyrgyz",
			"kv"=>"Komi",
			"kg"=>"Kongo",
			"ko"=>"Korean",
			"ku"=>"Kurdish",
			"kj"=>"Kwanyama, Kuanyama",
			"lo"=>"Lao",
			"la"=>"Latin",
			"lv"=>"Latvian",
			"li"=>"Limburgish, Limburgan, Limburger",
			"ln"=>"Lingala",
			"lt"=>"Lithuanian",
			"lu"=>"Luba-Katanga",
			"lb"=>"Luxembourgish, Letzeburgesch",
			"mi"=>"Ma-ori",
			"mk"=>"Macedonian",
			"mg"=>"Malagasy",
			"ms"=>"Malay",
			"ml"=>"Malayalam",
			"mt"=>"Maltese",
			"gv"=>"Manx",
			"mr"=>"Marathi",
			"mh"=>"Marshallese",
			"el"=>"Modern Greek",
			"he"=>"Modern Hebrew",
			"mn"=>"Mongolian",
			"na"=>"Nauru",
			"nv"=>"Navajo, Navaho",
			"ng"=>"Ndonga",
			"ne"=>"Nepali",
			"nd"=>"North Ndebele",
			"se"=>"Northern Sami",
			"no"=>"Norwegian",
			"nb"=>"Norwegian Bokmål",
			"nn"=>"Norwegian Nynorsk",
			"oc"=>"Occitan (after 1500)",
			"oj"=>"Ojibwa",
			"or"=>"Oriya",
			"om"=>"Oromo",
			"os"=>"Ossetian, Ossetic",
			"pi"=>"Pa-li",
			"pa"=>"Panjabi, Punjabi",
			"ps"=>"Pashto, Pushto",
			"fa"=>"Persian",
			"pl"=>"Polish",
			"pt"=>"Portuguese",
			"qu"=>"Quechua",
			"ro"=>"Romanian, Moldavian, Moldovan",
			"rm"=>"Romansh",
			"rn"=>"Rundi",
			"ru"=>"Russian",
			"sm"=>"Samoan",
			"sg"=>"Sango",
			"sa"=>"Sanskrit",
			"sc"=>"Sardinian",
			"sr"=>"Serbian",
			"sn"=>"Shona",
			"ii"=>"Sichuan Yi, Nuosu",
			"sd"=>"Sindhi",
			"si"=>"Sinhala, Sinhalese",
			"sk"=>"Slovak",
			"sl"=>"Slovenian",
			"so"=>"Somali",
			"nr"=>"South Ndebele",
			"st"=>"Southern Sotho",
			"es"=>"Spanish, Castilian",
			"su"=>"Sundanese",
			"sw"=>"Swahili",
			"ss"=>"Swati",
			"sv"=>"Swedish",
			"tl"=>"Tagalog",
			"ty"=>"Tahitian",
			"tg"=>"Tajik",
			"ta"=>"Tamil",
			"tt"=>"Tatar",
			"te"=>"Telugu",
			"th"=>"Thai",
			"bo"=>"Tibetan",
			"ti"=>"Tigrinya",
			"to"=>"Tonga (Tonga Islands)",
			"ts"=>"Tsonga",
			"tn"=>"Tswana",
			"tr"=>"Turkish",
			"tk"=>"Turkmen",
			"tw"=>"Twi",
			"ug"=>"Uighur, Uyghur",
			"uk"=>"Ukrainian",
			"ur"=>"Urdu",
			"uz"=>"Uzbek",
			"ve"=>"Venda",
			"vi"=>"Vietnamese",
			"vo"=>"Volapük",
			"wa"=>"Walloon",
			"cy"=>"Welsh",
			"fy"=>"Western Frisian",
			"wo"=>"Wolof",
			"xh"=>"Xhosa",
			"yi"=>"Yiddish",
			"yo"=>"Yoruba",
			"za"=>"Zhuang, Chuang",
			"zu"=>"Zulu");
	}

	/**
	 * Update comments
	 *
	 * @param int $soundtrack_id
	 * @param int $total_num 
	 */
	public function updateComments($soundtrack_id, $total_num) {
		$soundtrackObj = $this->get($soundtrack_id);
		if ($soundtrackObj && !$soundtrackObj->isNew()) {
			$soundtrackObj->setVar('post_comments', $total_num);
			$this->insert($soundtrackObj, true);
		}
	}
	
	/**
	 * Flush the cache for the Podcast module after adding, editing or deleting a SOUNDTRACK.
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
				// Soundtrack index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fsoundtrack.php^* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fsoundtrack.php%3Fsortsel* -delete &");
				
				// Programme index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php^* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fstart* -delete &");
				
				// New soundtracks index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fnew.php* -delete &");
				
				// Blocks
				exec("find " . ICMS_CACHE_PATH . "/" . "blk_podcast* -delete &");
				
				// Individual soundtrack (and related programme) page
				if (!$obj->isNew())
				{
					// Individual soundtrack
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fsoundtrack.php%3Fsoundtrack_id%3D" 
							. $obj->getVar('soundtrack_id', 'e') . "%26* -delete &");
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fsoundtrack.php%3Fsoundtrack_id%3D" 
							. $obj->getVar('soundtrack_id', 'e') . "^* -delete &");
					
					// Individual programme page this soundtrack is associated with
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fprogramme_id%3D" 
							. $obj->getVar('source', 'e') . "%26* -delete &");
					exec("find " . ICMS_CACHE_PATH . "/" . "podcast^%2Fmodules%2Fpodcast%2Fprogramme.php%3Fprogramme_id%3D" 
							. $obj->getVar('source', 'e') . "^* -delete &");
				}				
			}
			catch(Exception $e)
			{
				$obj->setErrors($e->getMessage());
			}
		}		
	}
	
	/**
	 * Adjust data before saving or updating
	 * @param object $obj 
	 */
	protected function beforeSave(& $obj)
	{		
		// Strip non-numerical characters out of the file size field, so can paste in from Windows directly
		$file_size = $obj->getVar('file_size', 'e');
		if ($file_size) {
			$file_size = preg_replace('/\D/', '', $file_size);
			$obj->setVar('file_size', $file_size);
		}
		
		return TRUE;
	}

	/**
	 * Triggers notifications, tracks tags, called when a soundtrack is inserted or updated
	 *
	 * @param object $obj PodcastSoundtrack object
	 * @return bool
	 */
	protected function afterSave(& $obj) {
		// Track tags
		$sprockets_taglink_handler = '';
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
		// Only update the taglinks if the object is being updated from the add/edit form (POST).
		// Database updates are not permitted from GET requests and will trigger an error
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					$sprocketsModule->getVar('dirname'), $sprocketsModule->getVar('dirname'), 'sprockets');
			
			// Store tags
			$sprockets_taglink_handler->storeTagsForObject($obj, 'tag', '0');
		}
		
		// triggers notification event for subscribers
		if (!$obj->getVar('soundtrack_notification_sent') && $obj->getVar ('online_status', 'e') == 1) {
			$obj->sendNotifSoundtrackPublished();
			$obj->setVar('soundtrack_notification_sent', true);
			$this->insert ($obj);
		}
		
		// Clear cache
		$this->clear_cache($obj);	
		
		return true;
	}

	/**
	 * Deletes notification subscriptions, called when a soundtrack is deleted
	 *
	 * @param object $obj PodcastSoundtrack object
	 * @return bool
	 */
	protected function afterDelete(& $obj) {
		
		$sprocketsModule = $notification_handler = $module_handler = $module = $module_id
				= $category = $item_id = '';		
		
		global $icmsModule;
		
		$sprocketsModule = icms_getModuleInfo('sprockets');
		$notification_handler = icms::handler('icms_data_notification');
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$category = 'global';
		$item_id = $obj->id();

		// delete programme soundtrack notifications
		$category = 'programme';
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);

		// delete soundtrack bookmarks
		$category = 'soundtrack';
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);
		
		// Delete taglinks
		if (icms_get_module_status("sprockets")) {
			$sprocketsModule = 
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->deleteAllForObject($obj);
		}
		
		// Clear cache
		$this->clear_cache($obj);	

		return true;
	}
}