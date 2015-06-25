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
	global $podcast_soundtrack_handler, $icmsAdminTpl;

	$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$soundtrackObj = $podcast_soundtrack_handler->get($soundtrack_id);

	if (!$soundtrackObj->isNew()) {
		$soundtrackObj->loadTags();
		$podcastModule->displayAdminMenu(0, _AM_PODCAST_SOUNDTRACKS . " > " . _CO_ICMS_EDITING);
		$sform = $soundtrackObj->getForm(_AM_PODCAST_SOUNDTRACK_EDIT, 'addsoundtrack');
		$sform->assign($icmsAdminTpl);
	} else {
		$soundtrackObj->setVar("submitter", icms::$user->getVar('uid'));
		$podcastModule->displayAdminMenu(0, _AM_PODCAST_SOUNDTRACKS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $soundtrackObj->getForm(_AM_PODCAST_SOUNDTRACK_CREATE, 'addsoundtrack');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->display('db:podcast_admin_soundtrack.html');
}
include_once("admin_header.php");

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
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

if (in_array($clean_op,$valid_op,true)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":

			icms_cp_header();

			editsoundtrack($clean_soundtrack_id);
			break;
		case "addsoundtrack":
			$controller = new icms_ipf_Controller($podcast_soundtrack_handler);
			$controller->storeFromDefaultForm(_AM_PODCAST_SOUNDTRACK_CREATED,
				_AM_PODCAST_SOUNDTRACK_MODIFIED);

			break;

		case "del":
			$controller = new icms_ipf_Controller($podcast_soundtrack_handler);
			$controller->handleObjectDeletion();

			break;

		case "changeStatus":
			$status = $podcast_soundtrack_handler->change_status($clean_soundtrack_id, 'online_status');
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
			
			// display a tag select filter (if the Sprockets module is installed)
			$sprocketsModule = icms_getModuleInfo('sprockets');

			if (icms_get_module_status("sprockets")) {
				$tag_select_box = '';
				$taglink_array = $tagged_soundtrack_list = array();
				$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
					'sprockets');
				$sprockets_taglink_handler = icms_getModuleHandler('taglink',
						$sprocketsModule->getVar('dirname'), 'sprockets');
				if ($untagged_content) {
					$tag_select_box = $sprockets_tag_handler->getTagSelectBox('soundtrack.php', 
							'untagged', _AM_PODCAST_SOUNDTRACK_ALL_SOUNDTRACKS, FALSE, 
							icms::$module->getVar('mid'), 'soundtrack', TRUE);
				} else {
					$tag_select_box = $sprockets_tag_handler->getTagSelectBox('soundtrack.php', 
							$clean_tag_id, _AM_PODCAST_SOUNDTRACK_ALL_SOUNDTRACKS, FALSE, 
							icms::$module->getVar('mid'), 'soundtrack', TRUE);
				}
				if (!empty($tag_select_box)) {
					echo '<h3>' . _AM_PODCAST_PROGRAMME_FILTER_BY_TAG . '</h3>';
					echo $tag_select_box;
				}

				if ($untagged_content || $clean_tag_id) {

					// get a list of soundtrack IDs belonging to this tag
					$criteria = new icms_db_criteria_Compo();
					if ($untagged_content) {
						$criteria->add(new icms_db_criteria_Item('tid', 0));
					} else {
						$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
					}
					$criteria->add(new icms_db_criteria_Item('mid', $podcastModule->getVar('mid')));
					$criteria->add(new icms_db_criteria_Item('item', 'soundtrack'));
					$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
					foreach ($taglink_array as $taglink) {
						$tagged_soundtrack_list[] = $taglink->getVar('iid');
					}
					$tagged_soundtrack_list = "('" . implode("','", $tagged_soundtrack_list) . "')";

					// use the list to filter the persistable table
					$criteria = new icms_db_criteria_Compo();
					$criteria->add(new icms_db_criteria_Item('soundtrack_id', $tagged_soundtrack_list, 'IN'));
				}
			}
			
			// Clear criteria associated with any taglist, if it is empty
			if (empty($criteria)) {
				$criteria = null;
			}
			
			// Display a summary table
			$objectTable = new icms_ipf_view_Table($podcast_soundtrack_handler, $criteria);
			$objectTable->addColumn(new icms_ipf_view_Column('online_status', 'center', true));
			$objectTable->addColumn(new icms_ipf_view_Column('title'));
			$objectTable->addColumn(new icms_ipf_view_Column('format', _GLOBAL_LEFT, false,
				'format', $formats));
			$objectTable->addColumn(new icms_ipf_view_Column('source', _GLOBAL_LEFT, false,
				'source', $sources));
			$objectTable->addColumn(new icms_ipf_view_Column('submission_time'));
			$objectTable->addColumn(new icms_ipf_view_Column('date'));
			$objectTable->addColumn(new icms_ipf_view_Column('federated', 'center', true));
			$objectTable->addFilter('source', 'source_filter');
			$objectTable->addFilter('federated', 'federated_filter');
			if (icms_get_module_status("sprockets"))
			{
				$objectTable->addFilter('rights', 'rights_filter');
			}
			$objectTable->addFilter('online_status', 'status_filter');
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
