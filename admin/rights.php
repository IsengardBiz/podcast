<?php

/**
 * Admin page to manage rights
 *
 * List, add, edit and delete rights objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/**
 * Edit a Rights object
 *
 * @param int $rights_id Rights id to be edited
 */
function editrights($rights_id = 0) {
	global $podcast_rights_handler, $icmsAdminTpl;

	$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$rightsObj = $podcast_rights_handler->get($rights_id);

	if (!$rightsObj->isNew()) {
		$podcastModule->displayAdminMenu(2, _AM_PODCAST_RIGHTSS . " > " . _CO_ICMS_EDITING);
		$sform = $rightsObj->getForm(_AM_PODCAST_RIGHTS_EDIT, 'addrights');
		$sform->assign($icmsAdminTpl);
	} else {
		$podcastModule->displayAdminMenu(2, _AM_PODCAST_RIGHTSS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $rightsObj->getForm(_AM_PODCAST_RIGHTS_CREATE, 'addrights');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->display('db:podcast_admin_rights.html');
}

include_once("admin_header.php");

$podcast_rights_handler = icms_getModuleHandler('rights', basename(dirname(dirname(__FILE__))),
	'podcast');

$clean_op = '';

/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ('mod','changedField','addrights','del','');

if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_rights_id = isset($_GET['rights_id']) ? (int) $_GET['rights_id'] : 0 ;

if (in_array($clean_op,$valid_op,true)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":

			icms_cp_header();

			editrights($clean_rights_id);
			break;
		case "addrights":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_rights_handler);
			$controller->storeFromDefaultForm(_AM_PODCAST_RIGHTS_CREATED, _AM_PODCAST_RIGHTS_MODIFIED);

			break;

		case "del":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_rights_handler);
			$controller->handleObjectDeletion();

			break;

		default:

			icms_cp_header();

			$podcastModule->displayAdminMenu(2, _AM_PODCAST_RIGHTSS);

			// check to see if /uploads/podcast is writeable and at least one programme exists etc
			$warnings = '';
			$warnings = podcast_check_module_configuration();
			if (!empty($warnings)) {
				$icmsAdminTpl->assign('podcast_warnings', $warnings);
			}

			// if no op is set, but there is a (valid) rights_id, display a single object
			if ($clean_rights_id) {
				$rightsObj = $podcast_rights_handler->get($clean_rights_id);
				if ($rightsObj->id()) {
					$rightsObj->displaySingleObject();
				}
			}

			$objectTable = new icms_ipf_view_Table($podcast_rights_handler);
			$objectTable->addColumn(new icms_ipf_view_Column('title'));
			$objectTable->addIntroButton('addrights', 'rights.php?op=mod', _AM_PODCAST_RIGHTS_CREATE);
			$icmsAdminTpl->assign('podcast_rights_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:podcast_admin_rights.html');
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */