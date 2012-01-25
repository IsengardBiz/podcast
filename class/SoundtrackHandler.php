<?php

/**
 * Class representing Podcast soundtrack handler objects
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
		parent::__construct($db, 'soundtrack', 'soundtrack_id', 'title',
			'description', 'podcast');
	}

	/**
	 * Returns a list of soundtracks with ID as key
	 *
	 * @return array
	 */
	public function getSoundtracks() {
		return $this->getList();
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
	public function getSoundtracksForSearch($queryarray, $andor, $limit, $offset, $userid) {
		$criteria = new icms_db_criteria_Compo();
		$criteria->setStart($offset);
		$criteria->setLimit($limit);
		$criteria->setSort('date');
		$criteria->setOrder('DESC');

		if ($userid != 0) {
			$criteria->add(new Criteria('submitter', $userid));
		}
		if ($queryarray) {
			$criteriaKeywords = new CriteriaCompo();
			for ($i = 0; $i < count($queryarray); $i++) {
				$criteriaKeyword = new CriteriaCompo();
				$criteriaKeyword->add(new Criteria('title', '%' . $queryarray[$i] . '%',
					'LIKE'), 'OR');
				$criteriaKeyword->add(new Criteria('description', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset ($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		$criteria->add(new Criteria('status', true));
		return $this->getObjects($criteria, true, false);
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
		$ret = $this->getObjects($criteria, true, false);
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

	 * @global type $xoopsDB
	 * 
	 * @return string 
	 */
	public function getModuleMimeTypes() {
		global $xoopsDB;
		$moduleMimetypes = array();
		$hiddenMimetypes = array('png', 'gif', 'jpg', 'jpeg', 'm3u');
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('dirname', '%' . basename(dirname(dirname(__FILE__)))
			. '%', 'LIKE'));
		$sql = 'SELECT mimetypeid, dirname, extension FROM '
			. $xoopsDB->prefix('system_mimetype');
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
	 * @global int $icmsUser
	 * @param int $start
	 * @param int $limit
	 * @param int $programme_id
	 * @param int $sort_order
	 * 
	 * @return object $criteria 
	 */
	public function getPodcastCriteria($start = 0, $limit = 10, $programme_id = false,
		$sort_order = false) {
		global $icmsUser;

		$criteria = new CriteriaCompo();
		if ($start) {
			$criteria->setStart($start);
		}
		if ($limit) {
			$criteria->setLimit(intval($limit));
		}
		if ($programme_id) {
			$criteria->add(new Criteria('source', $programme_id));
		}
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
	 * @global object $xoopsDB
	 * @return array 
	 */
	public function format_filter() {
		// only display mimetypes actually in use
		$mimetype_id_string = $sql = $rows = '';
		$mimetypeArray = array();
		$criteria = null;

		global $xoopsDB;

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
		$criteria = new CriteriaCompo();
		$criteria->setSort('extension');
		$criteria->setOrder('ASC');
		$sql = 'SELECT * FROM ' . $xoopsDB->prefix('system_mimetype') . $mimetype_id_string;
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
		$podcast_rights_handler = icms_getModuleHandler('rights',
			basename(dirname(dirname(__FILE__))), 'podcast');
		$rights_array = $podcast_rights_handler->getList();
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
	 * Triggers notifications, called when a soundtrack is inserted or updated
	 *
	 * @param object $obj PodcastSoundtrack objectt
	 * @return bool
	 */
	protected function afterSave(& $obj) {
		// triggers notification event for subscribers
		if (!$obj->getVar('soundtrack_notification_sent') && $obj->getVar ('status', 'e') == 1) {
			$obj->sendNotifSoundtrackPublished();
			$obj->setVar('soundtrack_notification_sent', true);
			$this->insert ($obj);
		}
		return true;
	}

	/**
	 * Deletes notification subscriptions, called when a soundtrack is deleted
	 *
	 * @param object $obj PodcastSoundtrack object
	 * @return bool
	 */
	protected function afterDelete(& $obj) {
		global $icmsModule;
		$notification_handler =& xoops_gethandler('notification');
		$module_handler = xoops_getHandler('module');
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

		return true;
	}
}
