<?php
/**
 * File containing onUpdate and onInstall functions for the module
 *
 * This file is included by the core in order to trigger onInstall or onUpdate functions when needed.
 * Of course, onUpdate function will be triggered when the module is updated, and onInstall when
 * the module is originally installed. The name of this file needs to be defined in the
 * icms_version.php
 *
 * <code>
 * $modversion['onInstall'] = "include/onupdate.inc.php";
 * $modversion['onUpdate'] = "include/onupdate.inc.php";
 * </code>
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// this needs to be the latest db version
define('PODCAST_DB_VERSION', 1);

/**
 * Update Podcast module
 *
 * @param object $module
 * @return boolean
 */
function icms_module_update_podcast($module) {
	/**
	 * Using the IcmsDatabaseUpdater to automaticallly manage the database upgrade dynamically
	 * according to the class defined in the module
	 */
	$icmsDatabaseUpdater = XoopsDatabaseFactory::getDatabaseUpdater();
	$icmsDatabaseUpdater->moduleUpgrade($module);
	return true;
}

/**
 * Authorises some common audio (and image) mimetypes on install
 *
 * Helps reduce the need for post-install configuration, its just a convenience for the end user.
 * It grants the module permission to use some common audio (and image) mimetypes that will
 * probably be needed for audio tracks and programme cover art.
 */
function authorise_mimetypes() {
	$dirname = basename(dirname(dirname(__FILE__)));
	$extension_list = array('mp3', 'wav', 'wma', 'png', 'gif', 'jpg');
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	foreach ($extension_list as $extension) {
		$allowed_modules = array();
		$mimetypeObj = '';

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('extension', $extension));
		$mimetypeObj = array_shift($system_mimetype_handler->getObjects($criteria));

		if ($mimetypeObj) {
			$allowed_modules = $mimetypeObj->getVar('dirname');
			if (empty($allowed_modules)) {
				$mimetypeObj->setVar('dirname', $dirname);
				$mimetypeObj->store();
			} else {
				if (!in_array($dirname, $allowed_modules)) {
					$allowed_modules[] = $dirname;
					$mimetypeObj->setVar('dirname', $allowed_modules);
					$mimetypeObj->store();
				}
			}
		}
	}
}

/**
 * Conducts optional tasks on module installation or update
 *
 * Modified to insert some common IP licenses and a default podcast programme to prepare the
 * module for immediate usage on installtion. Also checks that an upload directory is available
 * and authorises the module to use common mimetypes that will probably be required.
 *
 * @global object $xoopsDB
 * @param object $module
 * @return boolean
 */
function icms_module_install_podcast($module) {
	global $xoopsDB;

	// create an uploads directory for images
	$path = ICMS_ROOT_PATH . '/uploads/podcast';
	$directory_exists = $writeable = true;

	// check if upload directory exists, make one if not
	if (!is_dir($path)) {
		$directory_exists = mkdir($path, 0777);
	}

	// authorise some audio mimetypes for convenience
	authorise_mimetypes();

	// insert some licenses and a default programme so that it is ready for use on installation
	$queries = array();

	// set up a default category in the interests of the module being ready to go out of the box
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_programme') . " (`title`, "
        . "`publisher`, `date`, `description`, `compact_view`, `sort_order` ) values ('My podcast programme', 'Anonymous', '2011',"
        . "'This is an example programme. Each programme represents a stand-alone collection of audio soundtracks with its own RSS feed and media enclosures. You can use programmes to represent regular podcast shows, individual events such as a conference, or albums. Create programmes under the Admin => Programme tab, then add audio tracks under the Admin => Soundtracks tab, where you can choose which programme to assign them to.', '0', '0')";

	// some common licenses
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`) values ('Copyright, all rights reserved',
                'This work is subject to copyright and all rights are reserved. Contact the creators for permission if you wish to use or distribute this work.')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution',
                'This license lets others distribute, remix, tweak, and build upon a work, even commercially, as long as they credit the author for the original creation. This isthe most accommodating of licenses offered, in terms of what others can do with works licensed under Attribution.', 'http://creativecommons.org/licenses/by/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Share Alike', 'This license lets others remix, tweak, and build upon a work even for commercial reasons, as long as they credit the author and license their new creations under the identical terms. This license is often compared to open source software licenses. All new works based on the original will carry the same license, so any derivatives will also allow commercial use.',
                'http://creativecommons.org/licenses/by-sa/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution No Derivatives' , 'This license allows for redistribution of a work, commercial and non-commercial, as long as it is passed along unchanged and in whole, with credit to the author.', 'http://creativecommons.org/licenses/by-nd/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial', 'This license lets others remix, tweak, and build upon a work non-commercially, and although their new works must also acknowledge the author and be non-commercial, they don’t have to license their derivative works on the same terms.', 'http://creativecommons.org/licenses/by-nc/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial Share Alike', 'This license lets others remix, tweak, and build upon a work non-commercially, as long as they credit the author and license their new creations under the identical terms. Others can download and redistribute the work just like the by-nc-nd license, but they can also translate, make remixes, and produce new stories based on the work. All new work based on the original will carry the same license, so any derivatives will also be non-commercial in nature.',
                'http://creativecommons.org/licenses/by-nc-sa/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial No Derivatives', 'This license is the most restrictive Creative Commons license, allowing redistribution. This license is often called the free  advertising license because it allows others to download the works and share them with others as long as they mention and link back to the author, but they can’t change them in any way or use them commercially.',
                'http://creativecommons.org/licenses/by-nc-nd/3.0')";
	$queries[] = "INSERT into " . $xoopsDB->prefix('podcast_rights')
		. " (`title`, `description`) values ('Public domain', 'Works in the public domain are not subject to restrictions concerning their use or distribution.')";

	foreach($queries as $query) {
		$result = $xoopsDB->query($query);
	}
	return true;
}