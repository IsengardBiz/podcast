<?php
/**
 * Podcast version infomation
 *
 * This file holds the configuration information of this module
 *
 * @copyright	GPL 2.0 or later
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish <simon@isengard.biz>
 * @package		podcast
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**  General Information  */
$modversion = array(
	'name'=> _MI_PODCAST_MD_NAME,
	'version'=> 1.34,
	'description'=> _MI_PODCAST_MD_DESC,
	'author'=> "Madfish",
	'credits'=> "Thanks to Phoenyx and UnderDog for assistance rendered.",
	'help'=> "",
	'license'=> "GNU General Public License (GPL)",
	'official'=> 0,
	'dirname'=> basename(dirname(__FILE__)),

	/**  Images information  */
	'iconsmall'=> "images/icon_small.png",
	'iconbig'=> "images/icon_big.png",
	'image'=> "images/icon_big.png", /* for backward compatibility */

	/**  Development information */
	'status_version'=> "1.34",
	'status'=> "Beta",
	'date'=> "21/7/2015",
	'author_word'=> "Thanks to the makers of IPF and ImBuilding.",

	/** Contributors */
	'developer_website_url' => "https://www.isengard.biz",
	'developer_website_name' => "Isengard.biz",
	'developer_email' => "simon@isengard.biz");

$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] =
	'<a href="' . ICMS_URL . '/modules/' . basename(dirname(__FILE__))
	. '/extras/podcast_manual.pdf" target="_blank">English</a>';

$modversion['warning'] = _MI_PODCAST_FINAL;

/** Administrative information */
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

/** Database information */
$modversion['object_items'][1] = 'soundtrack';
$modversion['object_items'][] = 'programme';
$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Install and update informations */
$modversion['onInstall'] = "include/onupdate.inc.php";
$modversion['onUpdate'] = "include/onupdate.inc.php";

/** Search information */
$modversion['hasSearch'] = 1;
$modversion['search'] = array (
	'file' => "include/search.inc.php",
	'func' => "podcast_search");

/** Menu information */
$modversion['hasMain'] = 1;
$i = 1;
$modversion['sub'][$i]['name'] = _MI_PODCAST_NEW;
$modversion['sub'][$i]['url'] = "new.php";
$i++;
$modversion['sub'][$i]['name'] = _MI_PODCAST_PROGRAMMES;
$modversion['sub'][$i]['url'] = "programme.php";
$i++;
$modversion['sub'][$i]['name'] = _MI_PODCAST_SOUNDTRACKS;
$modversion['sub'][$i]['url'] = "soundtrack.php";
$i++;

/** Blocks information */

// displays recent soundtracks

$modversion['blocks'][1] = array(
	'file' => 'podcast_recent.php',
	'name' => _MI_PODCAST_RECENT,
	'description' => _MI_PODCAST_RECENTDSC,
	'show_func' => 'podcast_recent_show',
	'edit_func' => 'podcast_recent_edit',
	'options' => 'All|5',
	'template' => 'podcast_block_recent.html'
);

// displays a list of programmes

$modversion['blocks'][] = array(
	'file' => 'podcast_programmes.php',
	'name' => _MI_PODCAST_PROGRAMMES,
	'description' => _MI_PODCAST_PROGRAMMESDSC,
	'show_func' => 'podcast_programmes_show',
	'edit_func' => 'podcast_programmes_edit',
	'options' => '10',
	'template' => 'podcast_block_programme.html'
);

/** Templates information */
$modversion['templates'][1] = array(
	'file' => 'podcast_header.html',
	'description' => 'Module Header');

$modversion['templates'][] = array(
	'file' => 'podcast_footer.html',
	'description' => 'Module Footer');

$modversion['templates'][] = array(
	'file' => 'podcast_soundtrack_extended.html',
	'description' => 'Displays the full description of a single soundtrack, embedded component of container templates');

$modversion['templates'][] = array(
	'file' => 'podcast_soundtrack_compact.html',
	'description' => 'Displays a compact description of a single soundtrack, embedded component of container templates');

$modversion['templates'][] = array(
	'file' => 'podcast_programme_description.html',
	'description' => 'Displays the details of a single programme, embedded component of container templates');

$modversion['templates'][]= array(
	'file' => 'podcast_rss.html',
	'description' => 'Generating RSS feeds with media enclosures');

$modversion['templates'][]= array(
	'file' => 'podcast_admin_soundtrack.html',
	'description' => 'Soundtrack Admin Index');

$modversion['templates'][]= array(
	'file' => 'podcast_soundtrack.html',
	'description' => 'Soundtrack Index');

$modversion['templates'][]= array(
	'file' => 'podcast_admin_programme.html',
	'description' => 'Programme Admin Index');

$modversion['templates'][]= array(
	'file' => 'podcast_programme.html',
	'description' => 'Programme Index');

$modversion['templates'][]= array(
	'file' => 'podcast_archive.html',
	'description' => 'Archive Index');

$modversion['templates'][] = array(
	'file' => 'podcast_requirements.html',
	'description' => 'Displays warning messages if module requirements not met');

$modversion['templates'][]= array(
	'file' => 'podcast_new.html',
	'description' => 'Displays the latest podcast content');

/** Preferences information */

// prepare start page options
$start_options = array(0 => 'soundtrack.php', 1 => 'programme.php', 2 => 'new.php');
$start_options = array_flip($start_options);

// default start page for the module

$modversion['config'][1] = array(
	'name' => 'podcast_start_page',
	'title' => '_MI_PODCAST_START_PAGE',
	'description' => '_MI_PODCAST_START_PAGE_DSC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $start_options,
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'podcast_select_box',
	'title' => '_MI_PODCAST_SELECT_BOX',
	'description' => '_MI_PODCAST_SELECT_BOX_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'podcast_enable_archive',
	'title' => '_MI_PODCAST_ENABLE_ARCHIVE',
	'description' => '_MI_PODCAST_ENABLE_ARCHIVE_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'podcast_default_federation',
	'title' => '_MI_PODCAST_FEDERATE',
	'description' => '_MI_PODCAST_FEDERATE_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// Prepare language options - but only if module is installed, otherwise it will cause an error.
// Options use ISO 639-1 two-letter language codes as keys. Accurate as of 29 September 2009.

$language_options = array(
	0 => '---',
	"ab"=>"Abkhazian",
	"aa"=>"Afar",
	"af"=>"Afrikaans",
	"ak"=>"Akan",
	"sq"=>"Albanian",
	"am"=>"Amharic",
	"ar"=>"Arabic",
	"an"=>"Aragonese",
	"hy"=>"Armenian",
	"as"=>"Assamese",
	"av"=>"Avaric",
	"ae"=>"Avestan",
	"ay"=>"Aymara",
	"az"=>"Azerbaijani",
	"bm"=>"Bambara",
	"ba"=>"Bashkir",
	"eu"=>"Basque",
	"be"=>"Belarusian",
	"bn"=>"Bengali",
	"bh"=>"Bihari",
	"bi"=>"Bislama",
	"bs"=>"Bosnian",
	"br"=>"Breton",
	"bg"=>"Bulgarian",
	"my"=>"Burmese",
	"ca"=>"Catalan, Valencian",
	"km"=>"Central Khmer",
	"ch"=>"Chamorro",
	"ce"=>"Chechen",
	"ny"=>"Chichewa, Chewa, Nyanja",
	"zh"=>"Chinese",
	"cu"=>"Church Slavic, Old Slavonic, Church Slavonic, Old Bulgarian, Old Church Slavonic",
	"cv"=>"Chuvash",
	"kw"=>"Cornish",
	"co"=>"Corsican",
	"cr"=>"Cree",
	"hr"=>"Croatian",
	"cs"=>"Czech",
	"da"=>"Danish",
	"dv"=>"Divehi, Dhivehi, Maldivian",
	"nl"=>"Dutch, Flemish",
	"dz"=>"Dzongkha",
	"en"=>"English",
	"eo"=>"Esperanto",
	"et"=>"Estonian",
	"ee"=>"Ewe",
	"fo"=>"Faroese",
	"fj"=>"Fijian",
	"fi"=>"Finnish",
	"fr"=>"French",
	"ff"=>"Fulah",
	"gd"=>"Gaelic, Scottish Gaelic",
	"gl"=>"Galician",
	"lg"=>"Ganda",
	"ka"=>"Georgian",
	"de"=>"German",
	"gn"=>"GuaranÃ­",
	"gu"=>"Gujarati",
	"ht"=>"Haitian, Haitian Creole",
	"ha"=>"Hausa",
	"hz"=>"Herero",
	"hi"=>"Hindi",
	"ho"=>"Hiri Motu",
	"hu"=>"Hungarian",
	"is"=>"Icelandic",
	"io"=>"Ido",
	"ig"=>"Igbo",
	"id"=>"Indonesian",
	"ia"=>"Interlingua (International Auxiliary Language Association)",
	"ie"=>"Interlingue, Occidental",
	"iu"=>"Inuktitut",
	"ik"=>"Inupiaq",
	"ga"=>"Irish",
	"it"=>"Italian",
	"ja"=>"Japanese",
	"jv"=>"Javanese",
	"kl"=>"Kalaallisut, Greenlandic",
	"kn"=>"Kannada",
	"kr"=>"Kanuri",
	"ks"=>"Kashmiri",
	"kk"=>"Kazakh",
	"ki"=>"Kikuyu, Gikuyu",
	"rw"=>"Kinyarwanda",
	"ky"=>"Kirghiz, Kyrgyz",
	"kv"=>"Komi",
	"kg"=>"Kongo",
	"ko"=>"Korean",
	"ku"=>"Kurdish",
	"kj"=>"Kwanyama, Kuanyama",
	"lo"=>"Lao",
	"la"=>"Latin",
	"lv"=>"Latvian",
	"li"=>"Limburgish, Limburgan, Limburger",
	"ln"=>"Lingala",
	"lt"=>"Lithuanian",
	"lu"=>"Luba-Katanga",
	"lb"=>"Luxembourgish, Letzeburgesch",
	"mi"=>"Ma-ori",
	"mk"=>"Macedonian",
	"mg"=>"Malagasy",
	"ms"=>"Malay",
	"ml"=>"Malayalam",
	"mt"=>"Maltese",
	"gv"=>"Manx",
	"mr"=>"Marathi",
	"mh"=>"Marshallese",
	"el"=>"Modern Greek",
	"he"=>"Modern Hebrew",
	"mn"=>"Mongolian",
	"na"=>"Nauru",
	"nv"=>"Navajo, Navaho",
	"ng"=>"Ndonga",
	"ne"=>"Nepali",
	"nd"=>"North Ndebele",
	"se"=>"Northern Sami",
	"no"=>"Norwegian",
	"nb"=>"Norwegian BokmÃ¥l",
	"nn"=>"Norwegian Nynorsk",
	"oc"=>"Occitan (after 1500)",
	"oj"=>"Ojibwa",
	"or"=>"Oriya",
	"om"=>"Oromo",
	"os"=>"Ossetian, Ossetic",
	"pi"=>"Pa-li",
	"pa"=>"Panjabi, Punjabi",
	"ps"=>"Pashto, Pushto",
	"fa"=>"Persian",
	"pl"=>"Polish",
	"pt"=>"Portuguese",
	"qu"=>"Quechua",
	"ro"=>"Romanian, Moldavian, Moldovan",
	"rm"=>"Romansh",
	"rn"=>"Rundi",
	"ru"=>"Russian",
	"sm"=>"Samoan",
	"sg"=>"Sango",
	"sa"=>"Sanskrit",
	"sc"=>"Sardinian",
	"sr"=>"Serbian",
	"sn"=>"Shona",
	"ii"=>"Sichuan Yi, Nuosu",
	"sd"=>"Sindhi",
	"si"=>"Sinhala, Sinhalese",
	"sk"=>"Slovak",
	"sl"=>"Slovenian",
	"so"=>"Somali",
	"nr"=>"South Ndebele",
	"st"=>"Southern Sotho",
	"es"=>"Spanish, Castilian",
	"su"=>"Sundanese",
	"sw"=>"Swahili",
	"ss"=>"Swati",
	"sv"=>"Swedish",
	"tl"=>"Tagalog",
	"ty"=>"Tahitian",
	"tg"=>"Tajik",
	"ta"=>"Tamil",
	"tt"=>"Tatar",
	"te"=>"Telugu",
	"th"=>"Thai",
	"bo"=>"Tibetan",
	"ti"=>"Tigrinya",
	"to"=>"Tonga (Tonga Islands)",
	"ts"=>"Tsonga",
	"tn"=>"Tswana",
	"tr"=>"Turkish",
	"tk"=>"Turkmen",
	"tw"=>"Twi",
	"ug"=>"Uighur, Uyghur",
	"uk"=>"Ukrainian",
	"ur"=>"Urdu",
	"uz"=>"Uzbek",
	"ve"=>"Venda",
	"vi"=>"Vietnamese",
	"vo"=>"VolapÃ¼k",
	"wa"=>"Walloon",
	"cy"=>"Welsh",
	"fy"=>"Western Frisian",
	"wo"=>"Wolof",
	"xh"=>"Xhosa",
	"yi"=>"Yiddish",
	"yo"=>"Yoruba",
	"za"=>"Zhuang, Chuang",
	"zu"=>"Zulu");

$language_options = array_flip($language_options);

$modversion['config'][] = array(
	'name' => 'default_language',
	'title' => '_MI_PODCAST_DEFAULT_LANGUAGE',
	'description' => '_MI_PODCAST_DEFAULT_LANGUAGE_DSC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $language_options,
	'default' =>  'en');

$modversion['config'][] = array(
	'name' => 'screenshot_width',
	'title' => '_MI_PODCAST_SCREENSHOT_WIDTH',
	'description' => '_MI_PODCAST_SCREENSHOT_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '150');

$modversion['config'][] = array(
	'name' => 'thumbnail_width',
	'title' => '_MI_PODCAST_THUMBNAIL_WIDTH',
	'description' => '_MI_PODCAST_THUMBNAIL_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '50');

$modversion['config'][] = array(
	'name' => 'new_view_mode',
	'title' => '_MI_PODCAST_NEW_VIEW_MODE',
	'description' => '_MI_PODCAST_NEW_VIEW_MODEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'new_items',
	'title' => '_MI_PODCAST_NEW_ITEMS',
	'description' => '_MI_PODCAST_NEW_ITEMSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

$modversion['config'][] = array(
	'name' => 'number_soundtracks_per_page',
	'title' => '_MI_PODCAST_NUMBER_SOUNDTRACKS',
	'description' => '_MI_PODCAST_NUMBER_SOUNDTRACKSSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

$modversion['config'][] = array(
	'name' => 'number_programmes_per_page',
	'title' => '_MI_PODCAST_NUMBER_PROGRAMMES',
	'description' => '_MI_PODCAST_NUMBER_PROGRAMMESDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

$programme_sort_options = array('Title' => 0, 'Publication date (ascending)' => 1,
    'Publication date (descending)' => 2);

$modversion['config'][] = array(
	'name' => 'programmes_sort_preference',
	'title' => '_MI_PODCAST_PROGRAMMES_SORT_PREFERENCE',
	'description' => '_MI_PODCAST_PROGRAMMES_SORT_PREFERENCEDSC',
	'formtype' => 'select',
	'valuetype' => 'int',
    'options' => $programme_sort_options,
	'default' =>  '0');

//// Template switches - show or hide particular fields ////

$modversion['config'][] = array(
	'name' => 'display_breadcrumb',
	'title' => '_MI_PODCAST_DISPLAY_BREADCRUMB',
	'description' => '_MI_PODCAST_DISPLAY_BREADCRUMBDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// Programmes

$modversion['config'][] = array(
	'name' => 'display_released_field',
	'title' => '_MI_PODCAST_DISPLAY_RELEASED',
	'description' => '_MI_PODCAST_DISPLAY_RELEASEDDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_programme_publisher_field',
	'title' => '_MI_PODCAST_DISPLAY_PROGRAMME_PUBLISHER',
	'description' => '_MI_PODCAST_DISPLAY_PROGRAMME_PUBLISHERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_trackcount_field',
	'title' => '_MI_PODCAST_DISPLAY_TRACKCOUNT',
	'description' => '_MI_PODCAST_DISPLAY_TRACKCOUNTDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// affects both programme and soundtrack objects

$modversion['config'][] = array(
	'name' => 'display_counter_field',
	'title' => '_MI_PODCAST_DISPLAY_COUNTER',
	'description' => '_MI_PODCAST_DISPLAY_COUNTERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_download_button',
	'title' => '_MI_PODCAST_DISPLAY_DOWNLOAD_BUTTON',
	'description' => '_MI_PODCAST_DISPLAY_DOWNLOAD_BUTTONDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_streaming_button',
	'title' => '_MI_PODCAST_DISPLAY_STREAMING_BUTTON',
	'description' => '_MI_PODCAST_DISPLAY_STREAMING_BUTTONDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// Soundtracks

$modversion['config'][] = array(
	'name' => 'display_creator_field',
	'title' => '_MI_PODCAST_DISPLAY_CREATOR',
	'description' => '_MI_PODCAST_DISPLAY_CREATORDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_date_field',
	'title' => '_MI_PODCAST_DISPLAY_DATE',
	'description' => '_MI_PODCAST_DISPLAY_DATEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_format_field',
	'title' => '_MI_PODCAST_DISPLAY_FORMAT',
	'description' => '_MI_PODCAST_DISPLAY_FORMATDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_soundtrack_publisher_field',
	'title' => '_MI_PODCAST_DISPLAY_SOUNDTRACK_PUBLISHER',
	'description' => '_MI_PODCAST_DISPLAY_SOUNDTRACK_PUBLISHERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_soundtrack_source_field',
	'title' => '_MI_PODCAST_DISPLAY_SOUNDTRACK_SOURCE',
	'description' => '_MI_PODCAST_DISPLAY_SOUNDTRACK_SOURCEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_language_field',
	'title' => '_MI_PODCAST_DISPLAY_LANGUAGE',
	'description' => '_MI_PODCAST_DISPLAY_LANGUAGEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_rights_field',
	'title' => '_MI_PODCAST_DISPLAY_RIGHTS',
	'description' => '_MI_PODCAST_DISPLAY_RIGHTSDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// Optional support for JW-Player
$modversion['config'][] = array(
	'name' => 'enable_jw_player',
	'title' => '_MI_PODCAST_ENABLE_JWPLAYER',
	'description' => '_MI_PODCAST_ENABLE_JWPLAYERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'jw_player_key',
	'title' => '_MI_PODCAST_JWPLAYER_KEY',
	'description' => '_MI_PODCAST_JWPLAYER_KEYDSC',
	'formtype' => 'textbox',
	'valuetype' => 'text');

$modversion['config'][] = array(
	'name' => 'jw_player_width',
	'title' => '_MI_PODCAST_JWPLAYER_WIDTH',
	'description' => '_MI_PODCAST_JWPLAYER_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '480');

$modversion['config'][] = array(
	'name' => 'jw_player_height',
	'title' => '_MI_PODCAST_JWPLAYER_HEIGHT',
	'description' => '_MI_PODCAST_JWPLAYER_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '360');

// Date format, default is the way that civilised people do it, ie. day/month/year eg. 1/12/2015

$modversion['config'][] = array(
	'name' => 'date_format',
	'title' => '_MI_PODCAST_DATE_FORMAT',
	'description' => '_MI_DATE_FORMATDSC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => 'j/m/Y');

/** Comments information */
$modversion['hasComments'] = 1;

$modversion['comments'] = array(
	'itemName' => 'soundtrack_id',
	'pageName' => 'soundtrack.php',
	/* Comment callback functions */
	'callbackFile' => 'include/comment.inc.php',
	'callback' => array(
		'approve' => 'podcast_com_approve',
		'update' => 'podcast_com_update'
));

/** Notification information */

$modversion['hasNotification'] = 1;

$modversion['notification'] = array (
	'lookup_file' => 'include/notification.inc.php',
	'lookup_func' => 'podcast_notify_iteminfo');

// notification categories

$modversion['notification']['category'][1] = array (
	'name' => 'global',
	'title' => _MI_PODCAST_GLOBAL_NOTIFY,
	'description' => _MI_PODCAST_GLOBAL_NOTIFY_DSC,
	'subscribe_from' => array('soundtrack.php', 'programme.php', 'new.php'),
	'item_name' => '');

$modversion['notification']['category'][2] = array (
	'name' => 'programme',
	'title' => _MI_PODCAST_PROGRAMME_NOTIFY,
	'description' => _MI_PODCAST_PROGRAMME_NOTIFY_DSC,
	'subscribe_from' => array('programme.php'),
	'item_name' => 'programme_id',
	'allow_bookmark' => 1);

$modversion['notification']['category'][3] = array(
	'name' => 'soundtrack',
	'title' => _MI_PODCAST_SOUNDTRACK_NOTIFY,
	'description' => _MI_PODCAST_SOUNDTRACK_NOTIFY_DSC,
	'subscribe_from' => array('soundtrack.php'),
	'item_name' => 'soundtrack_id',
	'allow_bookmark' => 1);

// notification events - global

$modversion['notification']['event'][1] = array(
	'name' => 'soundtrack_published',
	'category'=> 'global',
	'title'=> _MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY,
	'caption'=> _MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_CAP,
	'description'=> _MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_DSC,
	'mail_template'=> 'global_soundtrack_published',
	'mail_subject'=> _MI_PODCAST_GLOBAL_SOUNDTRACK_PUBLISHED_NOTIFY_SBJ);

$modversion['notification']['event'][2] = array(
	'name' => 'programme_published',
	'category'=> 'global',
	'title'=> _MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY,
	'caption'=> _MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_CAP,
	'description'=> _MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_DSC,
	'mail_template'=> 'global_programme_published',
	'mail_subject'=> _MI_PODCAST_GLOBAL_PROGRAMME_PUBLISHED_NOTIFY_SBJ);

// notification events - programme

$modversion['notification']['event'][3] = array(
	'name' => 'programme_soundtrack_published',
	'category'=> 'programme',
	'title'=> _MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY,
	'caption'=> _MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_CAP,
	'description'=> _MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_DSC,
	'mail_template'=> 'programme_soundtrack_published',
	'mail_subject'=> _MI_PODCAST_PROGRAMME_SOUNDTRACK_PUBLISHED_NOTIFY_SBJ);