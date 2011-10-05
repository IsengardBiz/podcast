<?php
/**
 * Footer page included at the end of each page on user side of the mdoule
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

$icmsTpl->assign("podcast_adminpage", podcast_getModuleAdminLink());
$icmsTpl->assign("podcast_is_admin", $podcast_isAdmin);
$icmsTpl->assign('podcast_url', PODCAST_URL);
$icmsTpl->assign('podcast_images_url', PODCAST_IMAGES_URL);

$xoTheme->addStylesheet(PODCAST_URL . 'module'.((defined("_ADM_USE_RTL")
	&& _ADM_USE_RTL)?'_rtl':'').'.css');

include_once(ICMS_ROOT_PATH . '/footer.php');