<?php

/**
 * Class representing Podcast rights handler objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

class PodcastRightsHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'rights', 'rights_id', 'title', 'description',
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