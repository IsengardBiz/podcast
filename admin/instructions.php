<?php

/**
 * Displays a brief overview of the functionality and usage of the Podcast module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

include_once("admin_header.php");
icms_cp_header();

$podcastModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

$podcastModule->displayAdminMenu(2, _AM_PODCAST_SOUNDTRACKS);

// This is a big chunk of text explaining what the module does, how to set it up and use it
// It could be improved

echo _AM_PODCAST_INSTRUCTIONS_DSC;

icms_cp_footer();