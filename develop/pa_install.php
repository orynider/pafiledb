<?php
/**
*
* @package phpBB2 Mod - pafileDB
* @version $Id: pa_install.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] mxBB Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/


/**#@+
* @ignore
*/
define('MXBB_MODULE', false);
define('IN_PHPBB', true);
define('IN_INSTALL', true);
/**#@-*/

if ( file_exists( './viewtopic.php' ) )
{
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
}
elseif ( file_exists( './../viewtopic.php' ) )
{
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
}
else
{
	die('Copy this file in install folder!!!');
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
include ($phpbb_root_path . 'common.' . $phpEx);
include ($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
include ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include ($phpbb_root_path . 'install/functions.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Have they authenticated (again) as an admin for this session?
if (!$auth->acl_get('a_') && $user->data['is_registered'])
{
	login_box('pa_install.'.$phpEx, $user->lang['LOGIN_ADMIN_CONFIRM'], $user->lang['LOGIN_ADMIN_SUCCESS'], true, false);
}

// Is user any type of admin? No, then stop here, each script needs to
// check specific permissions but this is a catchall
if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('NO_FOUNDER');
}

//
// Check if mx_common Mod is prezent
//
function mx_do_install_upgrade( $sql = '', $main_install = false )
{
	global $table_prefix, $mx_table_prefix, $userdata, $phpEx, $template, $lang, $db, $board_config, $HTTP_POST_VARS;

	$inst_error = false;
	$n = 0;
	$message = "<b>This is the result list of the SQL queries needed for the install/upgrade</b><br /><br />";

	while ( $sql[$n] )
	{
		if ( !$result = @$db->sql_query( $sql[$n] ) )
		{
			$message .= '<b><font color=#FF0000>[Error or Already added]</font></b> line: ' . ( $n + 1 ) . ' , ' . $sql[$n] . '<br />';
			$inst_error = true;
		}
		else
		{
			$message .= '<b><font color=#0000fF>[Added/Updated]</font></b> line: ' . ( $n + 1 ) . ' , ' . $sql[$n] . '<br />';
		}
		$n++;
	}
	$message .= '<br /> If you get some Errors, Already Added or Updated messages, relax, this is normal when updating mods';

	return $message;
}

$page_title = 'Installing/Upgrading pafileDB';


if (!function_exists('get_available_dbms'))
{
	global $phpbb_root_path, $phpEx;
	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
}

$error = array();
$sql = array();

// If fresh install
if ( !$result = @$db->sql_query( "SELECT config_name from " . $table_prefix . "pa_config" ) )
{
	$message = "<b>This is a fresh install!</b><br/><br/>";

		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_cat ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_auth ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_comments ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_config ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_custom ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_customdata ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_download_info ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_license ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_votes ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_mirrors ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_files ";
		$sql[] = "DROP TABLE IF EXISTS " . $table_prefix . "pa_search_results ";

		// Table structure for table `pa_cat`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_cat (
		  `cat_id` int(10) NOT NULL auto_increment,
		  `cat_name` mediumtext,
		  `cat_desc` mediumtext,
		  `cat_parent` int(50) default NULL,
		  `parents_data` mediumtext,
		  `cat_order` int(50) default NULL,
		  `cat_allow_file` tinyint(2) NOT NULL default '0',
		  `cat_allow_ratings` tinyint(2) NOT NULL default '-1',
		  `cat_allow_comments` tinyint(2) NOT NULL default '-1',
		  `cat_files` mediumint(8) NOT NULL default '-1',
		  `cat_last_file_id` mediumint(8) unsigned NOT NULL default '0',
		  `cat_last_file_name` varchar(255) default NULL,
		  `cat_last_file_time` int(50) unsigned NOT NULL default '0',
		  `auth_view` tinyint(2) NOT NULL default '0',
		  `auth_read` tinyint(2) NOT NULL default '0',
		  `auth_view_file` tinyint(2) NOT NULL default '0',
		  `auth_edit_file` tinyint(2) NOT NULL default '0',
		  `auth_delete_file` tinyint(2) NOT NULL default '2',
		  `auth_upload` tinyint(2) NOT NULL default '0',
		  `auth_download` tinyint(2) NOT NULL default '0',
		  `auth_rate` tinyint(2) NOT NULL default '0',
		  `auth_email` tinyint(2) NOT NULL default '0',
		  `auth_view_comment` tinyint(2) NOT NULL default '0',
		  `auth_post_comment` tinyint(2) NOT NULL default '0',
		  `auth_edit_comment` tinyint(2) NOT NULL default '0',
		  `auth_delete_comment` tinyint(2) NOT NULL default '0',
		  `auth_approval` tinyint(2) NOT NULL default '0',
		  `internal_comments` tinyint(2) NOT NULL default '-1',
		  `autogenerate_comments` tinyint(2) NOT NULL default '-1',
		  `comments_forum_id` mediumint(8) NOT NULL default '-1',
		  `show_pretext` tinyint(2) NOT NULL default '-1',
		  `notify` tinyint(2) NOT NULL default '-1',
		  `notify_group` mediumint(8) NOT NULL default '-1',
		  `auth_approval_edit` tinyint(2) NOT NULL default '0',
		  PRIMARY KEY  (`cat_id`)
		) ENGINE=MyISAM"; 

		//
		// Insert
		//
		$sql[] = "INSERT INTO " . $table_prefix . "pa_cat (`cat_id`, `cat_name`, `cat_desc`, `cat_parent`, `parents_data`, `cat_order`, `cat_allow_file`, `cat_allow_ratings`, `cat_allow_comments`, `cat_files`, `cat_last_file_id`, `cat_last_file_name`, `cat_last_file_time`, `auth_view`, `auth_read`, `auth_view_file`, `auth_edit_file`, `auth_delete_file`, `auth_upload`, `auth_download`, `auth_rate`, `auth_email`, `auth_view_comment`, `auth_post_comment`, `auth_edit_comment`, `auth_delete_comment`, `auth_approval`, `internal_comments`, `autogenerate_comments`, `comments_forum_id`, `show_pretext`, `notify`, `notify_group`, `auth_approval_edit`) VALUES(1, 'My Category', '', 0, '', 0, 0, -1, -1, -1, 0, '-1', 0, -1, -1, -1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_cat (`cat_id`, `cat_name`, `cat_desc`, `cat_parent`, `parents_data`, `cat_order`, `cat_allow_file`, `cat_allow_ratings`, `cat_allow_comments`, `cat_files`, `cat_last_file_id`, `cat_last_file_name`, `cat_last_file_time`, `auth_view`, `auth_read`, `auth_view_file`, `auth_edit_file`, `auth_delete_file`, `auth_upload`, `auth_download`, `auth_rate`, `auth_email`, `auth_view_comment`, `auth_post_comment`, `auth_edit_comment`, `auth_delete_comment`, `auth_approval`, `internal_comments`, `autogenerate_comments`, `comments_forum_id`, `show_pretext`, `notify`, `notify_group`, `auth_approval_edit`) VALUES(2, 'Test Cagegory', 'Just a test category', 1, '', 0, 1, -1, -1, -1, 0, '-1', 0, -1, -1, -1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";		
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_files`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_files (
			  file_id int(10) NOT NULL auto_increment,
			  file_name text,
			  file_desc text,
			  file_longdesc text,
			  file_catid int(10) default NULL,
			  file_approved TINYINT(1) NOT NULL default '1',

			  file_size int(20) NOT NULL default '0',
			  unique_name varchar(255) NOT NULL default '',
			  real_name VARCHAR(255) NOT NULL,
			  file_dir VARCHAR(255) NOT NULL,
			  file_creator text,
			  file_version text,
			  file_ssurl text,
			  file_sshot_link tinyint(2) NOT NULL default '0',
			  file_dlurl text,
			  file_posticon text,
			  file_license int(10) default NULL,
			  file_docsurl text,

			  file_time int(50) default NULL,
			  user_id mediumint(8) NOT NULL default '0',
			  poster_ip varchar(8) NOT NULL default '',
			  file_update_time int(50) NOT NULL default '0',
			  file_last int(50) default NULL,
			  file_pin int(2) default NULL,
			  file_disable int(2) default '0',
			  disable_msg text,
			  file_broken TINYINT(1) DEFAULT '0' NOT NULL,
	 		  topic_id mediumint(8) unsigned NOT NULL default '0',
			  file_dls int(10) DEFAULT '0' NOT NULL,

			  PRIMARY KEY  (file_id)
		)";
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_config`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_config (
			  config_name varchar(255) NOT NULL default '',
			  config_value varchar(255) NOT NULL default '',
			  PRIMARY KEY  (config_name)
		)";

		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_comments`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_comments (
			  `comments_id` int(10) NOT NULL auto_increment,
			  `file_id` int(10) NOT NULL default '0',
			  `comments_text` mediumtext,
			  `comments_title` mediumtext,
			  `comments_time` int(50) NOT NULL default '0',
			  `comment_bbcode_uid` varchar(10) default NULL,
			  `poster_id` mediumint(8) NOT NULL default '0',
			  PRIMARY KEY  (`comments_id`),
			  KEY `comments_id` (`comments_id`),
			  FULLTEXT KEY `comment_bbcode_uid` (`comment_bbcode_uid`)
		) ENGINE=MyISAM";		

		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_custom`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_custom (
			  custom_id int(50) NOT NULL auto_increment,
			  custom_name text NOT NULL,
			  custom_description text NOT NULL,
			  data text NOT NULL,
			  field_order int(20) NOT NULL default '0',
			  field_type tinyint(2) NOT NULL default '0',
			  regex varchar(255) NOT NULL default '',
			  PRIMARY KEY  (custom_id)
		)";

		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_customdata`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_customdata (
			  customdata_file int(50) NOT NULL default '0',
			  customdata_custom int(50) NOT NULL default '0',
			  data text NOT NULL
		)";

		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_download_info`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_download_info (
			  file_id mediumint(8) NOT NULL default '0',
			  user_id mediumint(8) NOT NULL default '0',
			  downloader_ip varchar(8) NOT NULL default '',
			  downloader_os varchar(255) NOT NULL default '',
			  downloader_browser varchar(255) NOT NULL default '',
			  browser_version varchar(255) NOT NULL default ''
		)";

		// --------------------------------------------------------
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_mirrors (
			  mirror_id mediumint(8) NOT NULL auto_increment,
			  file_id int(10) NOT NULL,
			  unique_name varchar(255) NOT NULL default '',
			  file_dir VARCHAR(255) NOT NULL,
			  file_dlurl varchar(255) NOT NULL default '',
			  mirror_location VARCHAR(255) NOT NULL default '',
			  PRIMARY KEY  (mirror_id),
			  KEY file_id (file_id)
		)";

		// Table structure for table `phpbb_pa_license`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_license (
			  license_id int(10) NOT NULL auto_increment,
			  license_name text,
			  license_text text,
			  PRIMARY KEY  (license_id)
		)";

		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_votes`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_votes (
			  user_id mediumint(8) NOT NULL default '0',
			  votes_ip varchar(50) NOT NULL default '0',
			  votes_file int(50) NOT NULL default '0',
			  rate_point tinyint(3) unsigned NOT NULL default '0',
			  voter_os varchar(255) NOT NULL default '',
			  voter_browser varchar(255) NOT NULL default '',
			  browser_version varchar(8) NOT NULL default '',
			  KEY user_id (user_id),
			  KEY votes_file (votes_file),
			  KEY votes_ip (votes_ip),
			  KEY voter_os (voter_os),
			  KEY voter_browser (voter_browser),
			  KEY browser_version (browser_version),
			  KEY rate_point (rate_point)
		)";

		// Table structure for table `pa_auth`
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_auth (
			   group_id mediumint(8) DEFAULT '0' NOT NULL,
			   cat_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
			   auth_view tinyint(1) DEFAULT '0' NOT NULL,
			   auth_read tinyint(1) DEFAULT '0' NOT NULL,
			   auth_view_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_edit_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_delete_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_upload tinyint(1) DEFAULT '0' NOT NULL,
			   auth_download tinyint(1) DEFAULT '0' NOT NULL,
			   auth_rate tinyint(1) DEFAULT '0' NOT NULL,
			   auth_email tinyint(1) DEFAULT '0' NOT NULL,
			   auth_view_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_post_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_edit_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_delete_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_approval tinyint(1) DEFAULT '0' NOT NULL,
			   auth_approval_edit tinyint(1) DEFAULT '0' NOT NULL,
			   auth_mod tinyint(1) DEFAULT '1' NOT NULL,
			   auth_search tinyint(1) DEFAULT '1' NOT NULL,
			   auth_stats tinyint(1) DEFAULT '1' NOT NULL,
			   auth_toplist tinyint(1) DEFAULT '1' NOT NULL,
			   auth_viewall tinyint(1) DEFAULT '1' NOT NULL,
			   KEY group_id (group_id),
			   KEY cat_id (cat_id)
		)";
		

		$sql[] = "CREATE TABLE " . $table_prefix . "pa_search_results (
			search_id int(11) unsigned NOT NULL default '0',
			session_id varchar(32) NOT NULL default '',
			search_array mediumtext NOT NULL,
			search_time int(11) NOT NULL default '0',
			PRIMARY KEY  (search_id),
			KEY session_id (session_id)
		)";	

		//
		// Config values
		//

		// General
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('enable_module', '0')"; // settings_disable
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('module_name', 'Download Database')"; // settings_dbname
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('wysiwyg_path', 'mx_mod/mx_shared/')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('upload_dir','pafiledb/uploads/')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('screenshots_dir','pafiledb/images/screenshots/')";

		// Files
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('max_file_size','262144')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('forbidden_extensions','php, php3, php4, phtml, pl, asp, aspx, cgi')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('hotlink_prevent', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('hotlink_allowed', '')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('tpl_php', '0')";

		// Appearance
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('sort_method', 'file_time')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('sort_order', 'DESC')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('pagination', '20')"; // art_pagination & settings_file_page

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('settings_stats', '')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('settings_viewall', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('settings_dbdescription', '')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('settings_topnumber', '10')";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_simple_navigation', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('cat_col', '2')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('settings_newdays', '1')";

		// Comments
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_comments', '0')"; // comments_show
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('internal_comments', '1')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_wordwrap', '1')"; // formatting_comment_fixup
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_image_resize', '300')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_truncate_links', '1')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('max_comment_subject_chars', '50')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('max_comment_chars', '5000')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_wysiwyg', '0')"; // allow_wysiwyg_comments & allow_wysiwyg
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_html', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_bbcode', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_smilies', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_links', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_images', '0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('no_comment_image_message', '[No image please]')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('no_comment_link_message', '[No links please]')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allowed_comment_html_tags', 'b,i,u,a')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('del_topic', '1')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('autogenerate_comments', '1')";	// NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('comments_pagination', '5')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('comments_forum_id', '0')"; // New

		// Ratings
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_ratings', '0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('votes_check_userid', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('votes_check_ip', '1')";

		// Instructions
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('show_pretext', '0')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('pt_header', 'File Submission Instructions')"; // NEW
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config values ('pt_body', 'Please check your references and include as much information as you can.')"; // NEW

		// Notifications
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('notify', 'pm')"; // pm_notify
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('notify_group', '0')";	// NEW

		// Permissions
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('auth_search','0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('auth_stats','0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('auth_toplist','0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('auth_viewall','0')";

		//Olympus
		$sql[] = "INSERT INTO " . $table_prefix . "acl_options (auth_option, is_global, is_local, founder_only) VALUES('a_pafiledb', '1', '0', '0')";
	
	install_module('acp', 'acp_pafiledb', $error, 'ACP_PAFILEDB_MANAGEMENT');		
		
	$message .= mx_do_install_upgrade( $sql );
}
else
{

	// Upgrade checks
	$upgrade_103 = 0;
	$upgrade_201 = 0;
	$upgrade_280 = 0; // mxp 2.8 branch ->
	$upgrade_225 = 0;
	$upgrade_290 = 0; //Olympus version

	$message = "<b>Upgrading!</b><br/><br/>";
	// validate before 1.0.3
	if ( !$result = @$db->sql_query( "SELECT auth_edit_file from " . $table_prefix . "pa_cat" ) )
	{
		$upgrade_103 = 1;
		$message .= "<b>Upgrading to v. 1.0.3...</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 1.0.3...ok</b><br/><br/>";
	}

	// validate before 2.0.1
	if ( !$result = @$db->sql_query( "SELECT auth_approval from " . $table_prefix . "pa_cat" ) )
	{
		$upgrade_201 = 1;
		$message .= "<b>Validating v. 2.0.1...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.0.1...ok</b><br/><br/>";
	}

	// validate before 2.0.2
	if ( !$result = @$db->sql_query( "SELECT config_value from " . $table_prefix . "pa_config WHERE config_name = 'internal_comments'" ) )
	{
		$upgrade_202 = 1;
		$message .= "<b>Upgrading to v. 2.0.2...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.0.2...ok</b><br/><br/>";
	}

	// validate before 2.8.0
	if ( !$result = $db->sql_query( "SELECT config_value from " . $table_prefix . "pa_config WHERE config_name = 'comments_forum_id'" ) )
	{
		$upgrade_280 = 1;
		$message .= "<b>Upgrading to v. 2.8.0...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.8.0...ok</b><br/><br/>";
	}
	
	// validate before 2.2.5
	if ( !$result = $db->sql_query( "SELECT file_disable from " . $table_prefix . "pa_files" ) )
	{
		$upgrade_225 = 1;
		$message .= "<b>Upgrading v. 2.2.5...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.2.5...ok</b><br/><br/>";
	}

	// validate before 2.9.0
	if ( !$result = $db->sql_query( "SELECT auth_option_id from " . $table_prefix . "acl_options WHERE auth_option = 'a_pafiledb'" ) )
	{
		$upgrade_290 = 1;
		$message .= "<b>Upgrading to v. 2.9.0...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.9.0...ok</b><br/><br/>";
	}

	// ------------------------------------------------------------------------------------------------------
	if ( $upgrade_103 == 1 )
	{

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD auth_edit_file tinyint(2) NOT NULL default '0' AFTER auth_view_file ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD auth_delete_file tinyint(2) NOT NULL default '0' AFTER auth_edit_file ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD cat_allow_ratings tinyint(2) NOT NULL default '-1' AFTER cat_allow_file ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD cat_allow_comments tinyint(2) NOT NULL default '-1' AFTER cat_allow_ratings ";

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_auth ADD auth_edit_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_view_file ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_auth ADD auth_delete_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_edit_file ";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('pm_notify', '0')";
	}

	if ( $upgrade_201 == 1 )
	{

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD auth_approval tinyint(2) NOT NULL default '0' AFTER auth_delete_comment ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_auth ADD auth_approval tinyint(1) DEFAULT '0' NOT NULL AFTER auth_delete_comment ";

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY auth_edit_file tinyint(2) NOT NULL default '0' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY auth_delete_file tinyint(2) NOT NULL default '0' ";

		// Upgrade the config table to avoid duplicate entries
		/*
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config MODIFY config_name VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config MODIFY config_value VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config DROP PRIMARY KEY, ADD PRIMARY KEY (config_name) ";
		*/

	}

	if ( $upgrade_202 == 1 )
	{
		// Upgrade the config table to avoid duplicate entries
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config MODIFY config_name VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config MODIFY config_value VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_config DROP PRIMARY KEY, ADD PRIMARY KEY (config_name) ";

		// Configs
		$sql[] = "UPDATE " . $table_prefix . "pa_config" . " SET config_name = 'enable_module' WHERE config_name = 'settings_disable'";
		$sql[] = "UPDATE " . $table_prefix . "pa_config" . " SET config_name = 'module_name' WHERE config_name = 'settings_dbname'";
		$sql[] = "UPDATE " . $table_prefix . "pa_config" . " SET config_name = 'pagination' WHERE config_name = 'settings_file_page'";

		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'art_pagination'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'comments_show'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'pm_notify'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'allow_wysiwyg_comments'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'allow_wysiwyg'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'formatting_fixup'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'formatting_comment_fixup'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'need_validation'";
		$sql[] = "DELETE FROM " . $table_prefix . "pa_config" . " WHERE config_name = 'validator'";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('wysiwyg_path', 'mx_mod/mx_shared/')";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_comments', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('internal_comments', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_wordwrap', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_image_resize', '300')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('formatting_comment_truncate_links', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('max_comment_subject_chars', '50')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('max_comment_chars', '5000')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_wysiwyg', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_html', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_bbcode', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_smilies', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_links', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allow_comment_images', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('no_comment_image_message', '[No image please]')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('no_comment_link_message', '[No links please]')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('allowed_comment_html_tags', 'b,i,u,a')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('del_topic', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('autogenerate_comments', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('comments_pagination', '5')";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_ratings', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('votes_check_userid', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('votes_check_ip', '1')";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('notify', '0')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('notify_group', '0')";

		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('show_pretext', '1')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('pt_header', 'File Submission Instructions')";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('pt_body', 'Please check your references and include as much information as you can.')";


		// add fields to pa_category table
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD internal_comments tinyint(2) NOT NULL default '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD autogenerate_comments tinyint(2) NOT NULL default '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD comments_forum_id mediumint(8) NOT NULL default '-1' ";

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD show_pretext tinyint(2) NOT NULL default '-1' ";

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD notify tinyint(2) NOT NULL DEFAULT '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD notify_group mediumint(8) NOT NULL DEFAULT '-1' ";

		// auth
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat ADD auth_approval_groups tinyint(2) NOT NULL default '0' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_auth ADD auth_approval_groups tinyint(1) NOT NULL default '0' ";

		// add fields to pa_files table
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_files ADD topic_id mediumint(8) unsigned NOT NULL default '0'";

	}

	if ( $upgrade_280 == 1 )
	{
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('comments_forum_id', '0')";

		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY cat_allow_ratings tinyint(2) NOT NULL default '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY cat_allow_comments tinyint(2) NOT NULL default '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY notify_group mediumint(8) NOT NULL default '-1' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat MODIFY auth_delete_file tinyint(2) NOT NULL default '2' ";

		// Appearance
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('use_simple_navigation', '1') ";
		$sql[] = "INSERT INTO " . $table_prefix . "pa_config VALUES ('cat_col', '2') ";

		// Auth
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_cat CHANGE auth_approval_groups auth_approval_edit tinyint(2) NOT NULL default '0' ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_auth CHANGE auth_approval_groups auth_approval_edit tinyint(2) NOT NULL default '0' ";
	}
	
	if ( $upgrade_225 == 1 )
	{
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_files ADD file_disable int(2) default '0' AFTER file_pin ";
		$sql[] = "ALTER TABLE " . $table_prefix . "pa_files ADD disable_msg text AFTER file_disable ";
	}
	
	if ( $upgrade_290 == 1 )
	{
		$sql[] = "CREATE TABLE " . $table_prefix . "pa_search_results (
			search_id int(11) unsigned NOT NULL default '0',
			session_id varchar(32) NOT NULL default '',
			search_array mediumtext NOT NULL,
			search_time int(11) NOT NULL default '0',
			PRIMARY KEY  (search_id),
			KEY session_id (session_id)
		)";		
	
		$sql[] = "INSERT INTO " . $table_prefix . "acl_options (auth_option, is_global, is_local, founder_only) VALUES('a_pafiledb', '1', '0', '0')";
		install_module('acp', 'acp_pafiledb', $error, 'ACP_PAFILEDB_MANAGEMENT');			
	}
	else
	{
		$message .= "<b>Nothing to upgrade...</b><br/><br/>";
	}

	$message .= mx_do_install_upgrade( $sql );

}

add_log('admin', 'Download Manger mod Install/Upgrade', 'Version 2.9.0 Alfa');

$message .= "<br/><b>Mod install succefully...</b><br/><br/>";

$template->assign_vars(array(
	'MESSAGE_TITLE'		=> 'Mod Installation/Upgrading/Uninstalling Information - mod specific db tables',
	'MESSAGE_TEXT'		=> nl2br($message))
);

page_header($page_title);

$template->set_filenames(array(
	'body' => 'message_body.html')
);

page_footer();

?>