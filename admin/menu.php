<?php
/**
 * Configuring the admin side menu for the module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

$i = 0;

$adminmenu[$i]['title'] = _MI_PODCAST_SOUNDTRACKS;
$adminmenu[$i]['link'] = 'admin/soundtrack.php';

$i++;
$adminmenu[$i]['title'] = _MI_PODCAST_PROGRAMMES;
$adminmenu[$i]['link'] = 'admin/programme.php';

$i++;
$adminmenu[$i]['title'] = _MI_PODCAST_INSTRUCTIONS;
$adminmenu[$i]['link'] = 'admin/instructions.php';

global $icmsConfig;

$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

if (icms_get_module_status("podcast")) {

	$i = 0;

	$headermenu[$i]['title'] = _CO_ICMS_GOTOMODULE;
	$headermenu[$i]['link'] = ICMS_URL . '/modules/' . $podcastModule->getVar('dirname');

	$i++;
	$headermenu[$i]['title'] = _PREFERENCES;
	$headermenu[$i]['link'] = '../../system/admin.php?fct=preferences&amp;op=showmod&amp;mod='
		. $podcastModule->getVar('mid');

	$i++;
	$headermenu[$i]['title'] = _MI_PODCAST_TEMPLATES;
	$headermenu[$i]['link'] = '../../system/admin.php?fct=tplsets&op=listtpl&tplset='
		. $icmsConfig['template_set'] . '&moddir=' . $podcastModule->getVar('dirname');

	$i++;
	$headermenu[$i]['title'] = _CO_ICMS_UPDATE_MODULE;
	$headermenu[$i]['link'] = ICMS_URL
		. '/modules/system/admin.php?fct=modulesadmin&op=update&module='
		. $podcastModule->getVar('dirname');

	$i++;
	$headermenu[$i]['title'] = _MI_PODCAST_TEST_OAIPMH;
	$headermenu[$i]['link'] = ICMS_URL . '/modules/' . $podcastModule->getVar('dirname')
		. '/admin/test_oaipmh.php';

	$i++;
	$headermenu[$i]['title'] = _MODABOUT_ABOUT;
	$headermenu[$i]['link'] = ICMS_URL . '/modules/' . $podcastModule->getVar('dirname')
		. '/admin/about.php';
	
	$i++;
	$headermenu[$i]['title'] = _MI_PODCAST_MANUAL;
	$headermenu[$i]['link'] = ICMS_URL . "/modules/" . $podcastModule->getVar("dirname") 
		. "/extras/podcast_manual.pdf";
}
