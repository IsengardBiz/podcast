<?php

/**
 * Classes responsible for managing Podcast soundtrack objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class PodcastSoundtrack extends icms_ipf_seo_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler PodcastPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;
		global $podcastConfig;
		global $icmsUser;

		parent::__construct($handler);

		$this->quickInitVar('soundtrack_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('identifier', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('creator', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('format', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('file_size', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('date', XOBJ_DTYPE_LTIME, false);
		$this->quickInitVar('publisher', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('source', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('language', XOBJ_DTYPE_TXTBOX, false, false, false,
			$podcastConfig['default_language']);
		$this->quickInitVar('rights', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('status', XOBJ_DTYPE_INT, true, false, false, 1);
		$this->quickInitVar('federated', XOBJ_DTYPE_INT, true, false, false,
			$podcastConfig['podcast_default_federation']);
		$this->quickInitVar('submission_time', XOBJ_DTYPE_LTIME, true);
		$this->quickInitVar('submitter', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('oai_identifier', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setOaiId());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml', false, 1);
		$this->initCommonVar('dobr');
		$this->quickInitVar ('soundtrack_notification_sent', XOBJ_DTYPE_INT);

		$this->IcmsPersistableSeoObject();

		$this->setControl('description', 'dhtmltextarea');

		$this->setControl('format', array(
			'itemHandler' => 'soundtrack',
			'method' => 'getModuleMimeTypes',
			'module' => 'podcast'));

		$this->setControl('rights', array(
			'itemHandler' => 'rights',
			'method' => 'getRights',
			'module' => 'podcast'));

		$this->setControl('source', array(
			'itemHandler' => 'programme',
			'method' => 'getProgrammes',
			'module' => 'podcast'));

		$this->setControl('language', array(
			'name' => 'select',
			'itemHandler' => 'soundtrack',
			'method' => 'getLanguage',
			'module' => 'podcast'));

		$this->setControl('submitter', 'user');
		$this->setControl('status', 'yesno');
		$this->setControl('federated', 'yesno');

		// force html and don't allow user to change; necessary for RSS feed integrity
		$this->doMakeFieldreadOnly('dohtml');
		$this->doHideFieldFromForm('dohtml');

		// hide the notification status field, its for internal use only
		$this->hideFieldFromForm ('soundtrack_notification_sent');
		$this->hideFieldFromSingleView ('soundtrack_notification_sent');

		// make the oai_identifier read only for OAIPMH archive integrity purposes
		// since external sites may harvest this data, the identifier has to remain
		// constant so that they can avoid duplicating records
		$this->doMakeFieldreadOnly('oai_identifier');
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
		if ($format == 's' && in_array($key, array ('creator', 'format', 'date', 'file_size', 'source',
			'language', 'rights', 'status', 'federated', 'submitter'))) {
			return call_user_func(array ($this, $key));
		}
		return parent :: getVar($key, $format);
	}

	/**
	 * Duplicates the functionality of toArray() but does not execute getVar() overrides that require DB calls
	 *
	 * Use this function when parsing multiple objects for display. If a getVar() override executes
	 * a DB query (for example, to lookup a value in another table) then parsing multiple articles
	 * will trigger that query multiple times. If you are doing this for a multiple fields and a
	 * large number of articles, this can result in a huge number of queries. It is more efficient
	 * in such cases to build a reference buffer for each such field and then do the lookups in
	 * memory instead. However, you need to create a reference buffer for each value where you want
	 * to avoid a DB lookup and manually assign the value in your code
	 *
	 * @return array
	 */
	public function toArrayWithoutOverrides() {

		$vars = $this->getVars();
		$do_not_override = array(0 => 'format', 1 => 'source', 2 => 'rights');
		$ret = array();

		foreach ($vars as $key => $var) {
			if (in_array($key, $do_not_override)) {
				$value = $this->getVar($key, 'e');
			} else {
				$value = $this->getVar($key);
			}
			$ret[$key] = $value;
		}

		if ($this->handler->identifierName != "") {
			$controller = new icms_ipf_Controller($this->handler);
			$ret['itemLink'] = $controller->getItemLink($this);
			$ret['itemUrl'] = $controller->getItemLink($this, true);
			$ret['editItemLink'] = $controller->getEditItemLink($this, false, true);
			$ret['deleteItemLink'] = $controller->getDeleteItemLink($this, false, true);
			$ret['printAndMailLink'] = $controller->getPrintAndMailLink($this);
		}

		return $ret;
	}

	/**
	 * Converts status field to human readable value
	 *
	 * @return string 
	 */
	public function status() {
		$status = $this->getVar('status', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/soundtrack.php?soundtrack_id=' . $this->getVar('soundtrack_id')
				. '&amp;op=changeStatus">';
		if ($status == false) {
			$button .= '<img src="../images/button_cancel.png" alt="Offline" title="Offline" /></a>';
		} else {
			$button .= '<img src="../images/button_ok.png" alt="Online" title="Online" /></a>';
		}
		return $button;
	}

	/**
	 * Converts federated field to human readable value
	 *
	 * @return string 
	 */
	public function federated() {
		$federated = $this->getVar('federated', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/soundtrack.php?soundtrack_id=' . $this->getVar('soundtrack_id')
				. '&amp;op=changeFederated">';
		if ($federated == false) {
			$button .= '<img src="../images/button_cancel.png" alt="Offline" title="Not Federated" /></a>';
		} else {
			$button .= '<img src="../images/button_ok.png" alt="Online" title="Federated" /></a>';
		}
		return $button;
	}

	/**
	 * Converts pipe-delimited creator field to comma separated for user side presentation
	 *
	 * @return string 
	 */
	public function creator() {
		$creator = $this->getVar('creator', 'e');
		return str_replace("|", ", ",  $creator);
	}

	/**
	 * Formats the date in a sane (non-American) way
	 *
	 * @return string 
	 */
	public function date() {
		$date = $this->getVar('date', 'e');
		$date = date('j/m/Y', $date);
		return $date;
	}

	/*
     * Converts mimetype id to human readable value (extension)
	 *
	 * @param mixed $formats array of mimetype objects
	 * @return str format file extension
	*/
	public function format($formats = false) {
		if (!$formats ) {
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
			$mimetypeObj = $system_mimetype_handler->get($this->getVar('format', 'e'));
			$mimetype = '.' . $mimetypeObj->getVar('extension');
			return $mimetype;
		} else {
			$mimetypeObj = $formats[$this->getVar('format', 'e')];
			return $mimetypeObj->getVar('extension');
		}
	}

	/**
	 * Returns a mimetypes but using the ICMS include but NOT using the core mimetype handler
	 *
	 * @return string
	 */
	public function get_mimetype() {
		// there is a core file that has a nice list of mimetypes
		// however some podcast clients don't observe the standard
		$mimetype_list = include ICMS_ROOT_PATH . '/class/mimetypes.inc.php';

		// lookup the format extension using the system_mimetype id
		$format_extension = $this->format();

		// need to trim the damn dot off
		$format_extension = ltrim($format_extension, '.');

		// should probably handle exception where the mimetype isn't in the list
		// should be a rare event though

		$mimetype = $mimetype_list[$format_extension];
		if ($mimetype) {
			return $mimetype;
		} else {
			return; // null
		}
	}

	/**
	 * Converts the source (programme) id to a human readable title
	 * 
	 * @param type $sources
	 * 
	 * @return string 
	 */
	public function source($sources = false) {
		if (!$sources) {
			$source = $this->getVar('source', 'e');
			$podcast_programme_handler = icms_getModuleHandler('programme',
				basename(dirname(dirname(__FILE__))), 'podcast');
			$programme_object = $podcast_programme_handler->get($source);
			$source = $programme_object->title();
			$source_link = '<a href="./programme.php?programme_id='
				. $programme_object->id() . '">' . $source . '</a>';
			return $source_link;
		} else {
			$sourceObj = $sources[$this->getVar('source', 'e')];
			$source = $sourceObj->toArray();
			return $source['itemLink'];
		}
	}


	/**
	 * Converts the rights ID to a human readable title
	 *
	 * @param type $oaipmh_request
	 * 
	 * @return string
	 */
	public function rights($oaipmh_request = false) {
		$rights_id = $this->getVar('rights', 'e');
		$podcast_rights_handler = icms_getModuleHandler('rights',
			basename(dirname(dirname(__FILE__))), 'podcast');
		$rights_object = $podcast_rights_handler->get($rights_id);
		$rights = $rights_object->toArray();
		if ($oaipmh_request) {
			return $rights['title'];
		} else {
			return $rights['itemLink'];
		}
	}

	/**
	 * Converts the ISO language key to a human readable title
	 *
	 * @return type 
	 */
	public function language() {
		$language_key = $this->getVar('language', 'e');
		$language_list = $this->handler->getLanguage();
		return $language_list[$language_key];
	}

	/**
	 * Utility to convert bytes to a more readable form (KB, MB etc)
	 *
	 * @return string 
	 */
	public function file_size() {
		$unit = $value = $output = '';
		$bytes = $this->getVar('file_size', 'e');

		if ($bytes == 0 || $bytes < 1024) {
			$unit = ' bytes';
			$value = $bytes;
		} elseif ($bytes > 1023 && $bytes < 1048576) {
			$unit = ' KB';
			$value = ($bytes / 1024);
		} elseif ($bytes > 1048575 && $bytes < 1073741824) {
			$unit = ' MB';
			$value = ($bytes / 1048576);
		} else {
			$unit = ' GB';
			$value = ($bytes / 1073741824);
		}
		$value = round($value, 2);
		$output = $value . ' ' . $unit;
		return $output;
	}

	/**
	 * Returns a linked user name
	 *
	 * @return string
	 */
	public function submitter() {

		$user_link = '';
		
		$member_handler = icms::handler('icms_member');
		$user = & $member_handler->getUser($this->getVar('submitter', 'e'));

		return $user->getVar('uname');
	}


	/**
	 * Adds a parameter (m3u_flag = 1) to a soundtrack URL that will trigger the file to be streamed
	 *
	 * @param string $itemUrl
	 * @return string 
	 */
	public function get_m3u($itemUrl) {
		if (!empty($itemUrl)) {
			return $itemUrl . '&amp;m3u_flag=1';
		} else {
			return null;
		}
	}

	/**
	 * Sends notifications to subscribers when a new soundtrack is published, called by afterSave(
	 */
	public function sendNotifSoundtrackPublished() {
		$item_id = $this->id();
		$source_id = $this->getVar('source', 'e');
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$notification_handler = icms::handler('icms_data_notification');

		$tags = array();
		$tags['ITEM_TITLE'] = $this->title();
		$tags['ITEM_URL'] = $this->getItemLink(true);
		$tags['PROGRAMME_NAME'] = $this->getVar('source', 's');

		// global notification
		$notification_handler->triggerEvent('global', 0, 'soundtrack_published', $tags,
			array(), $module_id, 0);

		// programme-specific notification
		$notification_handler->triggerEvent('programme', $source_id,
			'programme_soundtrack_published', $tags, array(), $module_id, 0);
	}		
}

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
		$criteria = new CriteriaCompo();
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
		$criteria = new  icms_db_criteria_Compo();
		$criteria->add(new  icms_db_criteria_Item('dirname', '%' . basename(dirname(dirname(__FILE__)))
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

		return true;
	}
}