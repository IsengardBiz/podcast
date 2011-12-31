<?php
/**
 * About page of the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once("admin_header.php");

$aboutObj = new icms_ipf_About();
$aboutObj->render();