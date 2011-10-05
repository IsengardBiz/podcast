<?php

/**
 * Classes responsible for managing Podcast rights objects
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
include_once(ICMS_ROOT_PATH . '/modules/'
				. basename(dirname(dirname(__FILE__))) . '/include/functions.php');

class PodcastRights extends IcmsPersistableSeoObject {

	/**
	 * Constructor
	 *
	 * @param object $handler PodcastPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('rights_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, true);
		$this->quickInitVar('identifier', XOBJ_DTYPE_TXTBOX, false);
		$this->initCommonVar('dohtml');
		$this->initCommonVar('dobr');
		$this->IcmsPersistableSeoObject();

        $this->setControl('description', 'dhtmltextarea');
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
		if ($format == 's' && in_array($key, array ())) {
			return call_user_func(array ($this, $key));
		}
		return parent :: getVar($key, $format);
	}
}
class PodcastRightsHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'rights', 'rights_id', 'title', 'description',
			'podcast');
	}

	/**
	 * Returns a list of Rights
	 *
	 * @return array
	 */
	public function getRights() {
		return $this->getList();
	}
}