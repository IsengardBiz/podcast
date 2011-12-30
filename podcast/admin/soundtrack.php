<?php
/**
 * Admin page to manage soundtracks
 *
 * List, add, edit and delete soundtrack objects
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

/**
 * Edit a Soundtrack
 *
 * @param int $soundtrack_id Soundtrackid to be edited
 */
function editsoundtrack($soundtrack_id = 0) {
	global $podcast_soundtrack_handler, $icmsUser, $icmsAdminTpl;

	$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$soundtrackObj = $podcast_soundtrack_handler->get($soundtrack_id);

	if (!$soundtrackObj->isNew()) {
		$podcastModule->displayAdminMenu(0, _AM_PODCAST_SOUNDTRACKS . " > " . _CO_ICMS_EDITING);
		$sform = $soundtrackObj->getForm(_AM_PODCAST_SOUNDTRACK_EDIT, 'addsoundtrack');
		$sform->assign($icmsAdminTpl);
	} else {
		$soundtrackObj->setVar("submitter", $icmsUser->getVar('uid'));
		$podcastModule->displayAdminMenu(0, _AM_PODCAST_SOUNDTRACKS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $soundtrackObj->getForm(_AM_PODCAST_SOUNDTRACK_CREATE, 'addsoundtrack');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->display('db:podcast_admin_soundtrack.html');
}
include_once("admin_header.php");

global $icmsUser;

$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack', 
	basename(dirname(dirname(__FILE__))), 'podcast');
/** Use a naming convention that indicates the source of the content of the variable */
$clean_op = '';
/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ('mod','changedField','addsoundtrack','del','view', 'changeStatus',
    'changeFederated', '');

if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_soundtrack_id = isset($_GET['soundtrack_id']) ? (int) $_GET['soundtrack_id'] : 0 ;

if (in_array($clean_op,$valid_op,true)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":

			icms_cp_header();

			editsoundtrack($clean_soundtrack_id);
			break;
		case "addsoundtrack":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_soundtrack_handler);
			$controller->storeFromDefaultForm(_AM_PODCAST_SOUNDTRACK_CREATED,
				_AM_PODCAST_SOUNDTRACK_MODIFIED);

			break;

		case "del":
			include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
			$controller = new IcmsPersistableController($podcast_soundtrack_handler);
			$controller->handleObjectDeletion();

			break;

		case "changeStatus":
			$status = $podcast_soundtrack_handler->change_status($clean_soundtrack_id, 'status');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/soundtrack.php';
			if ($status == 0) {
				redirect_header(ICMS_URL . $ret, 2, _AM_PODCAST_SOUNDTRACK_OFFLINE);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_PODCAST_SOUNDTRACK_ONLINE);
			}
			break;

        case "changeFederated":
			$federated = $podcast_soundtrack_handler->change_status($clean_soundtrack_id, 'federated');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/soundtrack.php';
			if ($federated == 0) {
				redirect_header(ICMS_URL . $ret, 2, _AM_PODCAST_SOUNDTRACK_NOT_FEDERATED);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_PODCAST_SOUNDTRACK_FEDERATED);
			}
			break;

		default:
			icms_cp_header();

			$podcastModule->displayAdminMenu(0, _AM_PODCAST_SOUNDTRACKS);

			include_once ICMS_ROOT_PATH."/kernel/icmspersistabletable.php";

			// check to see if /uploads/podcast is writeable, if not, complain
			$warnings = '';
			$warnings = podcast_check_module_configuration();
			if (!empty($warnings)) {
				$icmsAdminTpl->assign('podcast_warnings', $warnings);
			}

			// if no op is set, but there is a (valid) soundtrack_id, display a single object
			if ($clean_soundtrack_id) {
				$soundtrackObj = $podcast_soundtrack_handler->get($clean_soundtrack_id);
				if ($soundtrackObj->id()) {
					$soundtrackObj->displaySingleObject();
				}
			}

			// prepare buffers to minimise queries
			$podcast_programme_handler = icms_getModuleHandler('programme',
				basename(dirname(dirname(__FILE__))), 'podcast');
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
			$sources = $podcast_programme_handler->getObjects(null, true);
			$formats = $system_mimetype_handler->getObjects(null, true);

			$objectTable = new IcmsPersistableTable($podcast_soundtrack_handler);
			$objectTable->addColumn(new IcmsPersistableColumn('status', 'center', true));
			$objectTable->addColumn(new IcmsPersistableColumn('title'));
			$objectTable->addColumn(new IcmsPersistableColumn('format', _GLOBAL_LEFT, false,
				'format', $formats));
			$objectTable->addColumn(new IcmsPersistableColumn('source', _GLOBAL_LEFT, false,
				'source', $sources));
			$objectTable->addColumn(new IcmsPersistableColumn('submission_time'));
			$objectTable->addColumn(new IcmsPersistablecolumn('date'));
			$objectTable->addColumn(new IcmsPersistableColumn('federated', 'center', true));
			$objectTable->addFilter('source', 'source_filter');
			$objectTable->addFilter('federated', 'federated_filter');
			$objectTable->addFilter('rights', 'rights_filter');
			$objectTable->addFilter('status', 'status_filter');
			$objectTable->addQuickSearch('title');
			$objectTable->addIntroButton('addsoundtrack', 'soundtrack.php?op=mod',
				_AM_PODCAST_SOUNDTRACK_CREATE);
			$icmsAdminTpl->assign('podcast_soundtrack_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:podcast_admin_soundtrack.html');
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */
