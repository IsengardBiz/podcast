<?php

/**
 * Class representing Podcast programme objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class PodcastProgramme extends icms_ipf_seo_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler PodcastPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		parent::__construct($handler);

		$this->quickInitVar('programme_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('creator', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('publisher', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('date', XOBJ_DTYPE_LTIME, true);
		$this->initNonPersistableVar('tag', XOBJ_DTYPE_INT, 'tag', FALSE, FALSE, FALSE, TRUE);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('compact_view', XOBJ_DTYPE_INT, true, false, false, 0);
		$this->quickInitVar('sort_order', XOBJ_DTYPE_TXTBOX, true, false, false, 0);
		$this->quickInitVar('cover', XOBJ_DTYPE_IMAGE, false);
		$this->quickInitVar('type', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE, 'Text');
		$this->quickInitVar('submission_time', XOBJ_DTYPE_LTIME, true);
		$this->quickInitVar('online_status', XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 1);
		$this->quickInitVar('oai_identifier', XOBJ_DTYPE_TXTBOX, true, false, false,
		$this->handler->setOaiId());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml', false, 1);
		$this->initCommonVar('dobr');
		$this->initCommonVar('doimage');
		$this->quickInitVar ('programme_notification_sent', XOBJ_DTYPE_INT);

		$this->IcmsPersistableSeoObject();
		
		// Only display the tag fields if the sprockets module is installed
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets"))
		{
			 $this->setControl('tag', array(
			 'name' => 'selectmulti',
			 'itemHandler' => 'tag',
			 'method' => 'getTags',
			 'module' => 'sprockets'));
		}
		else
		{
			 $this->hideFieldFromForm('tag');
			 $this->hideFieldFromSingleView ('tag');
		}
		
		// Hide the online_status field, as it is always on for this object (at least for now)
		$this->hideFieldFromForm('online_status');
		$this->hideFieldFromSingleView('online_status');
		
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

		// hide the notification status and type fields, they are for internal use only
		$this->hideFieldFromForm('programme_notification_sent');
		$this->hideFieldFromSingleView ('programme_notification_sent');
		$this->hideFieldFromForm('type');
		$this->hideFieldFromSingleView('type');
		$this->doMakeFieldReadOnly('type');
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
     * Load tags linked to this programme
     *
     * @return void
     */
     public function loadTags() {
          
          $ret = array();
          $sprocketsModule = icms_getModuleInfo('sprockets');
          if (icms_get_module_status("sprockets")) {
               $sprockets_taglink_handler = icms_getModuleHandler('taglink',
                         $sprocketsModule->getVar('dirname'), 'sprockets');
               $ret = $sprockets_taglink_handler->getTagsForObject($this->id(), $this->handler, '0'); // label_type = 0 means only return tags
               $this->setVar('tag', $ret);
          }
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
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname(basename(dirname(dirname(__FILE__))));
		$module_id = $module->getVar('mid');
		$notification_handler = icms::handler('icms_data_notification');

		$tags = array();
		$tags['ITEM_TITLE'] = $this->title();
		$tags['ITEM_URL'] = $this->getItemLink(true);

		// global notification
		$notification_handler->triggerEvent('global', 0, 'programme_published', $tags,
			array(), $module_id, 0);
	}
}