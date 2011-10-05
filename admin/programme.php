<?php
/**
 * Admin page to manage programmes
 *
 * List, add, edit and delete programme objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/**
 * Edit a Programme
 *
 * @param int $programme_id Programme id to be edited
 */
function editprogramme($programme_id = 0) {
	global $podcast_programme_handler, $icmsAdminTpl;

	$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$programmeObj = $podcast_programme_handler->get($programme_id);

	if (!$programmeObj->isNew()) {
		$podcastModule->displayAdminMenu(1, _AM_PODCAST_PROGRAMMES . " > " . _CO_ICMS_EDITING);
		$sform = $programmeObj->getForm(_AM_PODCAST_PROGRAMME_EDIT, 'addprogramme');
		$sform->assign($icmsAdminTpl);
	} else {
		$podcastModule->displayAdminMenu(1, _AM_PODCAST_PROGRAMMES . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $programmeObj->getForm(_AM_PODCAST_PROGRAMME_CREATE, 'addprogramme');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->display('db:podcast_admin_programme.html');
}

include_once("admin_header.php");

$podcast_programme_handler = icms_getModuleHandler('programme', 
	basename(dirname(dirname(__FILE__))),'podcast');

$clean_op = '';

/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ('mod','changedField','addprogramme','del','');

if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_programme_id = isset($_GET['programme_id']) ? (int) $_GET['programme_id'] : 0 ;

if (in_array($clean_op,$valid_op,true)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":

			icms_cp_header();

			editprogramme($clean_programme_id);
			break;
		case "addprogramme":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_programme_handler);
			$controller->storeFromDefaultForm(_AM_PODCAST_PROGRAMME_CREATED,
				_AM_PODCAST_PROGRAMME_MODIFIED);

			break;

		case "del":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_programme_handler);
			$controller->handleObjectDeletion();

			break;

		default:

			icms_cp_header();
			$podcastModule->displayAdminMenu(1, _AM_PODCAST_PROGRAMMES);

			// check to see if /uploads/podcast is writeable, if any programmes exist, if not, complain
			$warnings = '';
			$warnings = podcast_check_module_configuration();
			if (!empty($warnings)) {
				$icmsAdminTpl->assign('podcast_warnings', $warnings);
			}

			// if no op is set, but there is a (valid) programme_id, display a single object
			if ($clean_programme_id) {
				$programmeObj = $podcast_programme_handler->get($clean_programme_id);
				if ($programmeObj->id()) {
					$programmeObj->displaySingleObject();
				}
			}

			// display a summary table
			include_once ICMS_ROOT_PATH."/kernel/icmspersistabletable.php";
			$objectTable = new IcmsPersistableTable($podcast_programme_handler);
			$objectTable->addColumn(new IcmsPersistableColumn('title'));
			$objectTable->addColumn(new IcmsPersistableColumn('date'));
			$objectTable->addColumn(new IcmsPersistableColumn('publisher'));
			$objectTable->addColumn(new IcmsPersistableColumn('submission_time'));
			$objectTable->addIntroButton('addprogramme', 'programme.php?op=mod',
				_AM_PODCAST_PROGRAMME_CREATE);
			$icmsAdminTpl->assign('podcast_programme_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:podcast_admin_programme.html');
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */