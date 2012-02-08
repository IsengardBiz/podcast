<?php
/**
 * Common file of the module included on all pages of the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

if (!defined("PODCAST_DIRNAME")) define("PODCAST_DIRNAME",
	$modversion['dirname'] = basename(dirname(dirname(__FILE__))));
if (!defined("PODCAST_URL")) define("PODCAST_URL", ICMS_URL . '/modules/' . PODCAST_DIRNAME . '/');
if (!defined("PODCAST_ROOT_PATH")) define("PODCAST_ROOT_PATH", ICMS_ROOT_PATH .'/modules/'
	. PODCAST_DIRNAME .'/');
if (!defined("PODCAST_IMAGES_URL")) define("PODCAST_IMAGES_URL", PODCAST_URL . 'images/');
if (!defined("PODCAST_ADMIN_URL")) define("PODCAST_ADMIN_URL", PODCAST_URL . 'admin/');

// Include the common language file of the module
icms_loadLanguageFile('podcast', 'common');

include_once(PODCAST_ROOT_PATH . "include/functions.php");

// Creating the module object to make it available throughout the module
$podcastModule = icms_getModuleInfo(PODCAST_DIRNAME);
if (is_object($podcastModule)) {
	$podcast_moduleName = $podcastModule->getVar('name');
}

// Find if the user is admin of the module and make this info available throughout the module
$podcast_isAdmin = icms_userIsAdmin(PODCAST_DIRNAME);

// Creating the module config array to make it available throughout the module
$podcastConfig = icms_getModuleConfig(PODCAST_DIRNAME);