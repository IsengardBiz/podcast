<?php
/**
 * User index page of the module, can be configured to start on different pages (see preferences)
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once "../../mainfile.php";  
include_once ICMS_ROOT_PATH . "/header.php"; 

// Read module preferences to determine what to use as the start page
$start_options = array(0 => 'soundtrack.php', 1 => 'programme.php', 2 => 'new.php');
$location = $start_options[icms::$module->config['podcast_start_page']];
header('location: ' . $location);
exit;