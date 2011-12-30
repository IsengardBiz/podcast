<?php
/**
 * Comment include file
 *
 * File holding functions used by the module to hook with the comment system of ImpressCMS
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/**
 * Update comments
 *
 * @param int $item_id
 * @param int $total_num 
 */
function podcast_com_update($item_id, $total_num) {
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
		basename(dirname(dirname(__FILE__))), 'podcast');
	$podcast_soundtrack_handler->updateComments($item_id, $total_num);
}

/**
 * Approve comment
 *
 * @param object $comment 
 */
function podcast_com_approve(&$comment) {
	// notification mail here
}