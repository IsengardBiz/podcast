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
		$programmeObj->loadTags();
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
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

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
			$controller = new icms_ipf_Controller($podcast_programme_handler);
			$controller->storeFromDefaultForm(_AM_PODCAST_PROGRAMME_CREATED,
				_AM_PODCAST_PROGRAMME_MODIFIED);

			break;

		case "del":
			$controller = new icms_ipf_Controller($podcast_programme_handler);
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
			
			// display a tag select filter (if the Sprockets module is installed)
			$sprocketsModule = icms_getModuleInfo('sprockets');

			if (icms_get_module_status("sprockets")) {
				$tag_select_box = '';
				$taglink_array = $tagged_programme_list = array();
				$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
					'sprockets');
				$sprockets_taglink_handler = icms_getModuleHandler('taglink',
						$sprocketsModule->getVar('dirname'), 'sprockets');
				if ($untagged_content) {
					$tag_select_box = $sprockets_tag_handler->getTagSelectBox('programme.php', 
							'untagged', _AM_PODCAST_PROGRAMME_ALL_PROGRAMMES, FALSE, 
							icms::$module->getVar('mid'), 'programme', TRUE);
				} else {
					$tag_select_box = $sprockets_tag_handler->getTagSelectBox('programme.php', 
							$clean_tag_id, _AM_PODCAST_PROGRAMME_ALL_PROGRAMMES, FALSE, 
							icms::$module->getVar('mid'), 'programme', TRUE);
				}
				if (!empty($tag_select_box)) {
					echo '<h3>' . _AM_PODCAST_PROGRAMME_FILTER_BY_TAG . '</h3>';
					echo $tag_select_box;
				}

				if ($untagged_content || $clean_tag_id) {

					// get a list of programme IDs belonging to this tag
					$criteria = new icms_db_criteria_Compo();
					if ($untagged_content) {
						$criteria->add(new icms_db_criteria_Item('tid', 0));
					} else {
						$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
					}
					$criteria->add(new icms_db_criteria_Item('mid', $podcastModule->getVar('mid')));
					$criteria->add(new icms_db_criteria_Item('item', 'programme'));
					$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
					foreach ($taglink_array as $taglink) {
						$tagged_programme_list[] = $taglink->getVar('iid');
					}
					$tagged_programme_list = "('" . implode("','", $tagged_programme_list) . "')";

					// use the list to filter the persistable table
					$criteria = new icms_db_criteria_Compo();
					$criteria->add(new icms_db_criteria_Item('programme_id', $tagged_programme_list, 'IN'));
				}
			}
			
			// Clear criteria associated with any taglist, if it is empty
			if (empty($criteria)) {
				$criteria = null;
			}

			// display a summary table
			$objectTable = new icms_ipf_view_Table($podcast_programme_handler, $criteria);
			$objectTable->addColumn(new icms_ipf_view_Column('title'));
			$objectTable->addColumn(new icms_ipf_view_Column('date'));
			$objectTable->addColumn(new icms_ipf_view_Column('publisher'));
			$objectTable->addColumn(new icms_ipf_view_Column('submission_time'));
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