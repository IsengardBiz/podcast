<?php

// Common functions used by the module

/**
 * @package SimplyWiki
 * @author Wiwimod: Xavier JIMENEZ
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @version $Id
 */

/**
 * Basic validation and sanitation of user input but NOT range checking (calling method should do)
 *
 * This can be expanded for all different types of input: email, URL, filenames, media/mimetypes
 *
 * @param array $input_var Array of user input, gathered from $_GET, $_POST or $_SESSION
 * @param array $valid_vars Array of valid variables and data type (integer, boolean, string,)
 * @return array Array of validated and sanitized variables
 */

function podcast_validate($input_var, $valid_vars) {
	$clean_var = array();

	foreach ($valid_vars as $key => $type) {
		if (empty($input_var[$key])) {
			$input_var[$key] = NULL;
			continue;
		}
		switch ($type) {
			case 'int':
			case 'integer':
				$clean_var[$key] = $dirty_int = $clean_int = 0;
				if (filter_var($input_var[$key], FILTER_VALIDATE_INT) == true) {
					$dirty_int = filter_var($input_var[$key], FILTER_SANITIZE_NUMBER_INT);
					$clean_int = mysql_real_escape_string($dirty_int);
					$clean_var[$key] = (int)$clean_int;
				}
				break;

			case 'html': // tolerate (but encode) html tags and entities
			// initialise
				$dirty_html = $clean_html = $clean_var[$key] = '';
				// test for string
				if (is_string($input_var[$key])) {
					// trim fore and aft whitespace
					$dirty_html = trim($input_var[$key]);
					// keep html tags but encode entities and special characters
					$dirty_html = filter_var($dirty_html, FILTER_SANITIZE_SPECIAL_CHARS);
					$clean_html = mysql_real_escape_string($dirty_html);
					$clean_var[$key] = (string)$clean_html;
				}
				break;

			case 'plaintext': // stripped down plaintext with tags removed
			// initialise
				$dirty_text = $clean_text = $clean_var[$key] = '';
				// test for string (in PHP, what isn't??)
				if (is_string($input_var[$key])) {
					// trim fore and aft whitespace
					$dirty_text = trim($input_var[$key]);
					// strip html tags, encode quotes and special characters
					$dirty_text = filter_var($dirty_text, FILTER_SANITIZE_STRING);
					$clean_text = mysql_real_escape_string($dirty_text);
					$clean_var[$key] = (string)$clean_text;
				}
				break;

			case 'name':
			// initialise
				$clean_var[$key] = $clean_name = $dirty_name = '';
				$pattern = '^[a-zA-Z\-\']{1,60}$';
				// test for string + alphanumeric
				if (is_string($input_var[$key]) && preg_match($pattern, $input_var[$key])) {
					// trim fore and aft whitespace
					$dirty_name = trim($input_var[$key]);
					// strip html tags, encode quotes and special characters
					$dirty_name = filter_var($dirty_name, FILTER_SANITIZE_STRING);
					$clean_name = mysql_real_escape_string($dirty_name);
					$clean_var[$key] = (string)$clean_name;
				}
				break;

			case 'email':
				$clean_var[$key] = $dirty_email = $clean_email = '';
				if (filter_var($input_var[$key], FILTER_VALIDATE_EMAIL) == true) {
					$dirty_email = filter_var($input_var[$key], FILTER_SANITIZE_EMAIL);
					$clean_email = mysql_real_escape_string($dirty_email);
					$clean_var[$key] = (string)$clean_email;
				}
				break;

			case 'url':
			// initialise
				$clean_var[$key] = $dirty_url = $clean_url = '';
				// validate and sanitise URL
				if (filter_var($input_var[$key], FILTER_VALIDATE_URL) == true) {
					$dirty_url = filter_var($input_var[$key], FILTER_SANITIZE_URL);
					$clean_url = mysql_real_escape_string($dirty_url);
					$clean_var[$key] = $clean_url;
				}

			case 'float':
			case 'double':
			case 'real':
			// initialise
				$clean_var[$key] = $clean_float = 0;
				// validate and sanitise float
				if (filter_var($input_var[$key], FILTER_VALIDATE_FLOAT) == true) {
					$clean_float = filter_var($input_var[$key], FILTER_SANITIZE_NUMBER_FLOAT);
					$clean_var[$key] = (float)$clean_float;
				}
				break;

			case 'bool':
			case 'boolean':
				$clean_var[$key] = false;
				if (is_bool($input_var[$key])) {
					$clean_var[$key] = (bool) $input_var[$key];
				}
				break;

			case 'binary':/* only PHP6 - for now
				if (is_string($input_var[$key])) {
				$clean_var[$key] = htmlspecialchars(trim($input_var[$key]));
				}*/
				break;

			case 'array': // note: doesn't inspect array *contents*, must be separately sanitised
				if (is_array($input_var[$key]) && !empty($input_var[$key])) {
					$clean_var[$key] = $input_var[$key];
				} else {
					$clean_var[$key] = $input_var[$key];
				}
				break;

			case 'object': // note: doesn't inspect object *properties*, must be treated separately
				if (is_object($input_var[$key])) {
					$clean_var[$key] = (object)$input_var[$key];
				}
				break;
		}
	}
	return $clean_var;
}

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

/*
 * Unsets unwanted programme fields so they are not displayed on the user side
 *
 * Fields can be set to display/hide in the module preferences.
 *
 * @param obj $programme PodcastProgramme object
*/
function podcast_programme_display_preferences($programme) {
	global $podcastConfig;

	if ($podcastConfig['display_released_field'] == 0) {
		unset($programme['date']);
	}

	if ($podcastConfig['display_programme_publisher_field'] == 0) {
		unset($programme['publisher']);
	}

	if ($podcastConfig['display_trackcount_field'] == 0) {
		unset($programme['track_count']);
	}

	if ($podcastConfig['display_counter_field'] == 0) {
		unset($programme['counter']);
	}

	if ($podcastConfig['display_streaming_button'] == 0) {
		unset($programme['play_all_button']);
	}

	return $programme;
}

/*
 * Unsets unwanted soundtrack fields so they are not displayed on the user side
 *
 * Fields can be set to display/hide in the module preferences.
 *
 * @param object $soundtrack PodcastSoundtrack object
*/

function podcast_soundtrack_display_preferences($soundtrack) {
	global $podcastConfig;

	if ($podcastConfig['display_creator_field'] == 0) {
		unset($soundtrack['creator']);
	}

	if ($podcastConfig['display_date_field'] == 0) {
		unset($soundtrack['date']);
	}

	if ($podcastConfig['display_format_field'] == 0) {
		unset($soundtrack['format']);
		unset($soundtrack['file_size']);
	}

	if ($podcastConfig['display_soundtrack_publisher_field'] == 0) {
		unset($soundtrack['publisher']);
	}

	if ($podcastConfig['display_language_field'] == 0) {
		unset($soundtrack['language']);
	}

	if ($podcastConfig['display_rights_field'] == 0) {
		unset($soundtrack['rights']);
	}

	if ($podcastConfig['display_counter_field'] == 0) {
		unset($soundtrack['counter']);
	}

	if ($podcastConfig['display_soundtrack_source_field'] == 0) {
		unset($soundtrack['source']);
	}

	if ($podcastConfig['display_download_button'] == 0) {
		unset($soundtrack['download']);
	}

	if ($podcastConfig['display_streaming_button'] == 0) {
		unset($soundtrack['streaming']);
	}

	return $soundtrack;
}

/**
 * Determines if required directories exist etc and complains if not
 *
 * @return string $warnings
 */
function podcast_check_module_configuration() {
	$path = ICMS_ROOT_PATH . '/uploads/podcast';
	$directory_exists = $writeable = true;
	$warnings = '';

	// check if directory exists
	if (!is_dir($path)) {
		$directory_exists = false;
	} else { // if it exists, check that it is writeable
		if (!is_writeable($path)) {
			$writeable = false;
		}
	}
	// echo warnings if attempts to remedy the situation failed
	if (!$directory_exists) {
		$warnings = _AM_PODCAST_NO_UPLOAD_DIRECTORY;
	}
	if ($directory_exists && !$writeable) {
		$warnings .= _AM_PODCAST_UPLOAD_NOT_WRITABLE;
	}

	// check to see if there are no programmes, if not, warn the user to create some
	$podcast_programme_handler = icms_getModuleHandler('programme',
			basename(dirname(dirname(__FILE__))), 'podcast');
	$programmes = '';
	$programmes = $podcast_programme_handler->getList();
	if (empty($programmes)) {
		$warnings .= _AM_PODCAST_MUST_CREATE_PROGRAMME;
	}

	// check to see if the module has been authorised to use at least one mimetype
	$podcast_soundtrack_handler = icms_getModuleHandler('soundtrack',
			basename(dirname(dirname(__FILE__))), 'podcast');
	$authorised_mimetypes = count($podcast_soundtrack_handler->getModuleMimeTypes());
	if ($authorised_mimetypes == 0) {
		$warnings .= _AM_PODCAST_MUST_AUTHORISE_MIMETYPES;
	}
	return $warnings;
}

/**
 * Get module admin link
 *
 * @todo to be move in icms core
 *
 * @param string $moduleName dirname of the moodule
 * @return string URL of the admin side of the module
 */
function podcast_getModuleAdminLink($moduleName='podcast') {
	$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
	if (!$moduleName && (isset ($podcastModule) && is_object($podcastModule))) {
		$moduleName = $podcastModule->dirname();
	}
	$ret = '';
	if ($moduleName) {
		$ret = "<a href='" . ICMS_URL . "/modules/$moduleName/admin/index.php'>"
			. _MD_PODCAST_ADMIN_PAGE . "</a>";
	}
	return $ret;
}

/**
 * Returns module name optionally with link
 *
 * @param bool $withLink
 * @param bool $forBreadCrumb
 * @param string $moduleName
 * @return string
 */
function podcast_getModuleName($withLink = true, $forBreadCrumb = false, $moduleName = false) {
	if (!$moduleName) {
		$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
		$moduleName = $podcastModule->dirname();
	}
	$icmsModuleConfig = icms_getModuleConfig($moduleName);
	if (!isset ($podcastModule)) {
		return '';
	}

	if (!$withLink) {
		return $podcastModule->getVar('name');
	} else {
		$ret = ICMS_URL . '/modules/' . $moduleName . '/';
		return '<a href="' . $ret . '">' . $podcastModule->getVar('name') . '</a>';
	}
}

/**
 * Return a linked username or full name for a specific $userid
 *
 * @todo this function is fixing a ucwords bug in icms_getLinkedUnameFromId so we will update this
 * in icms 1.2
 *
 * @param integer $userid uid of the related user
 * @param bool $name true to return the fullname, false to use the username; if true and the user
 * does not have fullname, username will be used instead
 * @param array $users array already containing XoopsUser objects in which case we will save a query
 * @param bool $withContact true if we want contact details to be added in the value returned
 * (PM and email links)
 * 
 * @return string name of user with a link on his profile
 */
function podcast_getLinkedUnameFromId($userid, $name = false, $users = array (), 
		$withContact = false) {
	if (!is_numeric($userid)) {
		return $userid;
	}
	$userid = intval($userid);
	if ($userid > 0) {
		if ($users == array()) {
			//fetching users
			$member_handler = & xoops_gethandler('member');
			$user = & $member_handler->getUser($userid);
		} else {
			if (!isset($users[$userid])) {
				return $GLOBALS['icmsConfig']['anonymous'];
			}
			$user = & $users[$userid];
		}
		if (is_object($user)) {
			$ts = & MyTextSanitizer::getInstance();
			$username = $user->getVar('uname');
			$fullname = '';
			$fullname2 = $user->getVar('name');
			if (($name) && !empty($fullname2)) {
				$fullname = $user->getVar('name');
			}
			if (!empty ($fullname)) {
				$linkeduser = "$fullname [<a href='"
					. ICMS_URL."/userinfo.php?uid=".$userid."'>"
					. $ts->htmlSpecialChars($username)."</a>]";
			} else {
				$linkeduser = "<a href='".ICMS_URL."/userinfo.php?uid=".$userid."'>"
						. $ts->htmlSpecialChars($username)."</a>";
			}
			// add contact info : email + PM
			if ($withContact) {
				$linkeduser .= '<a href="mailto:'.$user->getVar('email')
					. '"><img style="vertical-align: middle;" src="'
					. ICMS_URL.'/images/icons/email.gif'.'" alt="'._US_SEND_MAIL.'" title="'
					. _US_SEND_MAIL.'"/></a>';
				$js = "javascript:openWithSelfMain('".ICMS_URL.'/pmlite.php?send2=1&to_userid='
					. $userid."', 'pmlite',450,370);";
				$linkeduser .= '<a href="'.$js.'"><img style="vertical-align: middle;" src="'
					. ICMS_URL.'/images/icons/pm.gif'.'" alt="'._US_SEND_PM.'" title="'
					. _US_SEND_PM.'"/></a>';
			}
			return $linkeduser;
		}
	}
	return $GLOBALS['icmsConfig']['anonymous'];
}