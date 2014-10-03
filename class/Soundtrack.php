<?php

/**
 * Class representing Podcast soundtrack objects
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

		parent::__construct($handler);

		$this->quickInitVar('soundtrack_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('identifier', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar("type", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar('format', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('file_size', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('inline_identifier', XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar('poster_image', XOBJ_DTYPE_IMAGE, FALSE);
		$this->quickInitVar('creator', XOBJ_DTYPE_TXTBOX, true);
		$this->initNonPersistableVar('tag', XOBJ_DTYPE_INT, 'tag', FALSE, FALSE, FALSE, TRUE);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('date', XOBJ_DTYPE_LTIME, false);
		$this->quickInitVar('publisher', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('source', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('language', XOBJ_DTYPE_TXTBOX, false, false, false,
			icms_getConfig('default_language', 'podcast'));
		$this->quickInitVar('rights', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('online_status', XOBJ_DTYPE_INT, true, false, false, 1);
		$this->quickInitVar('federated', XOBJ_DTYPE_INT, true, false, false,
			icms_getConfig('podcast_default_federation', 'podcast'));
		$this->quickInitVar('submission_time', XOBJ_DTYPE_LTIME, true);
		$this->quickInitVar('submitter', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('oai_identifier', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setOaiId());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml', false, 1);
		$this->initCommonVar('dobr');
		$this->quickInitVar ('soundtrack_notification_sent', XOBJ_DTYPE_INT);
		
		$url = ICMS_URL . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$this->setImageDir($url, $path);
		
		// Set controls		
		$this->setControl('type', array(
			'name' => 'select',
			'itemHandler' => 'soundtrack',
			'method' => 'getTypeOptions',
			'module' => 'podcast'));

		$this->setControl('description', 'dhtmltextarea');

		$this->setControl('format', array(
			'itemHandler' => 'soundtrack',
			'method' => 'getModuleMimeTypes',
			'module' => 'podcast'));
		
		$this->setControl('poster_image', array('name' => 'image'));
		
		// Only display the tag and rights fields if the sprockets module is installed
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets"))
		{
			$this->setControl('tag', array(
			'name' => 'selectmulti',
			'itemHandler' => 'tag',
			'method' => 'getTags',
			'module' => 'sprockets'));
			
			$this->setControl('rights', array(
			'itemHandler' => 'rights',
			'method' => 'getRights',
			'module' => 'sprockets'));			
		} else {
			$this->hideFieldFromForm('tag');
			$this->hideFieldFromSingleView ('tag');
			$this->hideFieldFromForm('rights');
			$this->hideFieldFromSingleView ('rights');
		}

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
		$this->setControl('online_status', 'yesno');
		$this->setControl('federated', 'yesno');

		// force html and don't allow user to change; necessary for RSS feed integrity
		$this->doMakeFieldreadOnly('dohtml');
		$this->doHideFieldFromForm('dohtml');

		// hide the notification status field, its for internal use only
		$this->hideFieldFromForm('soundtrack_notification_sent');
		$this->hideFieldFromSingleView('soundtrack_notification_sent');
		
		// Only display the secondary identifier (URL) field if JW Player is installed and enabled
		$has_video = $jw_player = $jw_player_enabled = $video_width = $video_height = FALSE;
		
		$jw_player = is_dir(XOOPS_ROOT_PATH . '/jwplayer');
		$jw_player_enabled = icms_getConfig('enable_jw_player', 'podcast');
		
		if (!$jw_player || !$jw_player_enabled) {
			$this->doHideFieldFromForm('inline_identifier');
		}

		// make the oai_identifier read only for OAIPMH archive integrity purposes
		// since external sites may harvest this data, the identifier has to remain
		// constant so that they can avoid duplicating records
		$this->doMakeFieldreadOnly('oai_identifier');
		
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
		if ($format == 's' && in_array($key, array ('creator', 'format', 'date', 'file_size', 'source',
			'language', 'rights', 'online_status', 'federated', 'submitter'))) {
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
	public function online_status() {
		$status = $this->getVar('online_status', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/soundtrack.php?soundtrack_id=' . $this->getVar('soundtrack_id')
				. '&amp;op=changeStatus">';
		if ($status == false) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' . _INVISIBLE . '"/></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' . _VISIBLE . '"/></a>';
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
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' . _INVISIBLE . '"/></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' . _VISIBLE . '"/></a>';
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
	 * Load tags linked to this soundtrack
	 *
	 * @return void
	 */
	public function loadTags() {
		
		$ret = array();
		
		// Retrieve the tags for this object
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$ret = $sprockets_taglink_handler->getTagsForObject($this->id(), $this->handler, '0'); // label_type = 0 means only return tags
			$this->setVar('tag', $ret);
		}
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
		$mimetype_list = icms_Utils::mimetypes();

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
		if (icms_get_module_status("sprockets"))
		{
			$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');		
			$rights_id = $this->getVar('rights', 'e');
			$rights_object = $sprockets_rights_handler->get($rights_id);
			$rights = $rights_object->toArray();
			return $rights['itemLink'];
		} else {
			return FALSE;
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