<?php

/**
 * Class representing Podcast rights objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class PodcastRights extends icms_ipf_seo_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler PodcastPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		parent::__construct($handler);

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
	
	/**
	 * Customise object URLs in IPF tables to append the SEO-friendly string.
	 */
	public function addSEOStringToItemUrl()
	{
		$short_url = $this->short_url();
		if (!empty($short_url))
		{
			$seo_url = '<a href="' . $this->getItemLink(TRUE) . '&amp;title=' . $this->short_url() 
					. '">' . $this->getVar('title', 'e') . '</a>';
		}
		else
		{
			$seo_url = $this->getItemLink(FALSE);
		}
		
		return $seo_url;
	}
}