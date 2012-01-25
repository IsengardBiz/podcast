<?php
/**
 * Rights index page - displays details of a single rights object, or a table listing all of them
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once 'header.php';

$xoopsOption['template_main'] = 'podcast_rights.html';
include_once ICMS_ROOT_PATH . '/header.php';

$podcast_rights_handler = icms_getModuleHandler('rights',
	basename(dirname(__FILE__)), 'podcast');

/** Use a naming convention that indicates the source of the content of the variable */
$clean_rights_id = isset($_GET['rights_id']) ? intval($_GET['rights_id']) : 0 ;
$rightsObj = $podcast_rights_handler->get($clean_rights_id);

// display one rights object
if ($rightsObj && !$rightsObj->isNew()) {
	$icmsTpl->assign('podcast_rights', $rightsObj->toArray());

	// generating meta information for this page
	$icms_metagen = new icms_ipf_Metagen($rightsObj->getVar('title'),
		$rightsObj->getVar('meta_keywords','n'),
		$rightsObj->getVar('meta_description', 'n'));
	$icms_metagen->createMetaTags();
} else {
	// display a table listing all rights

	$icmsTpl->assign('podcast_title', _MD_PODCAST_ALL_RIGHTSS);

	$objectTable = new icms_ipf_view_Table($podcast_rights_handler, $criteria = null, array(), true);
	$objectTable->isForUserSide();
	$objectTable->addColumn(new icms_ipf_view_Column('title'));
	$icmsTpl->assign('podcast_rights_table', $objectTable->fetch());
}

$icmsTpl->assign('podcast_module_home', podcast_getModuleName(true, true));
$icmsTpl->assign('podcast_display_breadcrumb', $podcastConfig['display_breadcrumb']);

include_once 'footer.php';