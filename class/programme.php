<?php

/**
 * Classes responsible for managing Podcast programme objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// including the IcmsPersistabelSeoObject
include_once ICMS_ROOT_PATH . '/kernel/icmspersistableseoobject.php';
include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/include/functions.php');

class PodcastProgramme extends IcmsPersistableSeoObject {

	/**
	 * Constructor
	 *
	 * @param object $handler PodcastPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('programme_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('publisher', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('date', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('compact_view', XOBJ_DTYPE_INT, true, false, false, 0);
		$this->quickInitVar('sort_order', XOBJ_DTYPE_TXTBOX, true, false, false, 0);
		$this->quickInitVar('cover', XOBJ_DTYPE_IMAGE, false);
		$this->quickInitVar('submission_time', XOBJ_DTYPE_LTIME, true);
		$this->quickInitVar('oai_identifier', XOBJ_DTYPE_TXTBOX, true, false, false,
		$this->handler->setOaiId());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml', false, 1);
		$this->initCommonVar('dobr');
		$this->initCommonVar('doimage');
		$this->quickInitVar ('programme_notification_sent', XOBJ_DTYPE_INT);

		$this->IcmsPersistableSeoObject();

		$this->setControl('description', 'dhtmltextarea');

		$this->setControl('cover', array('name' => 'image'));
		$url = ICMS_URL . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$this->setImageDir($url, $path);

		$this->setControl('compact_view', 'yesno');
		$this->doMakeFieldreadOnly('oai_identifier');

		$this->setControl('sort_order', array(
			'name' => 'select',
			'itemHandler' => 'programme',
			'method' => 'getSortOptions',
			'module' => 'podcast'));

		// force html and do not allow user to change, necessary for integrity of rss feeds
		$this->doMakeFieldreadOnly('dohtml');
		$this->doHideFieldFromForm('dohtml');

		// hide the notification status field, its for internal use only
		$this->hideFieldFromForm ('programme_notification_sent');
		$this->hideFieldFromSingleView ('programme_notification_sent');
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
		if ($format == 's' && in_array($key, array ('compact_view', 'sort_order'))) {
			return call_user_func(array ($this, $key));
		}
		return parent :: getVar($key, $format);
	}

	/**
	 * Converts the programme compact view switch to a human readable value
	 *
	 * @return bool
	 */
	public function compact_view() {
		if ($this->getVar('compact_view', 'e') == 0) {
			return 'No';
		} else {
			return 'Yes';
		}
	}


	/**
	 * Converts programme sort_order into a human readable value
	 * 
	 * @return string
	 */

	public function sort_order() {
		$sort_order = $this->getVar('sort_order', 'e');
		$sort_options = $this->handler->getSortOptions();
		return $sort_options[$sort_order];
	}

	/**
	 * Returns a html snippet for inserting an RSS feed button/link into a smarty template variable
	 *
	 * @return string
	 */
	public function get_rss_button() {
		return '<a href="./rss.php?programme_id=' . $this->id()
			. '" title="' . _CO_PODCAST_PROGRAMME_ENCLOSURES . '">'
			. '<img src="' . './images/rss.png" alt="RSS"' . ' /></a>';
	}

	/**
	 * Returns a html snippet for inserting a programme streaming button into a smarty variable
	 *
	 * @return string
	 */
	public function get_play_all_button() {
		return '<a href="./programme.php?programme_id='
			. $this->id() . '&amp;m3u_flag=1" title="' . _CO_PODCAST_PROGRAMME_PLAY_ALL . '">'
			. '<img src="' . './images/stream.png" alt="Listen online"' . ' /></a>';
	}

	/**
	 * Sends programme notifications to subscribers, called in afterSave()
	 */
	public function sendNotifProgrammePublished() {
		$item_id = $this->id();
		$module_handler = xoops_getHandler('module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$notification_handler = xoops_getHandler ('notification');

		$tags = array();
		$tags['ITEM_TITLE'] = $this->title();
		$tags['ITEM_URL'] = $this->getItemLink(true);

		// global notification
		$notification_handler->triggerEvent('global', 0, 'programme_published', $tags,
			array(), $module_id, 0);
	}
}

class PodcastProgrammeHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'programme', 'programme_id', 'title',
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