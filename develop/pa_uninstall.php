<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_uninstall.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( file_exists( './viewtopic.php' ) )
{
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
}
elseif ( file_exists( './../viewtopic.php' ) )
{
	$phpbb_root_path = './../';
}
else
{
	die('Copy this file in phpbb_root were is your viewtopic.php file!!!');
}

define('MXBB_MODULE', false);
define('IN_PHPBB', true);
define('IN_INSTALL', true);

$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

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
if (!$auth->acl_get('a_'))
{
	trigger_error('Access to the pafiledb instalaller is not allowed as you do not have administrative permissions.');
}

//
// Check if mx_common Mod is prezent
//
function mx_do_install_upgrade( $sql = '', $main_install = false )
{
	global $table_prefix, $table_prefix, $userdata, $phpEx, $template, $lang, $db, $board_config, $HTTP_POST_VARS;

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
// THE END

$page_title = 'Uninstall pafileDB';

$sql = array(
	"DROP TABLE " . $table_prefix . "pa_cat ",
	"DROP TABLE " . $table_prefix . "pa_auth ",
	"DROP TABLE " . $table_prefix . "pa_comments ",
	"DROP TABLE " . $table_prefix . "pa_config ",
	"DROP TABLE " . $table_prefix . "pa_custom ",
	"DROP TABLE " . $table_prefix . "pa_customdata ",
	"DROP TABLE " . $table_prefix . "pa_download_info ",
	"DROP TABLE " . $table_prefix . "pa_license ",
	"DROP TABLE " . $table_prefix . "pa_votes ",
	"DROP TABLE " . $table_prefix . "pa_mirrors ",
	"DROP TABLE " . $table_prefix . "pa_files ",
	"DELETE from " . $table_prefix . "acl_options WHERE auth_option = 'a_pafiledb'",
	"DELETE from " . $table_prefix . "modules WHERE module_langname = 'ACP_PAFILEDB_MANAGEMENT'",
	"DELETE from " . $table_prefix . "modules WHERE module_langname = 'ACP_MANAGE_PAFILEDB'",
	"DELETE from " . $table_prefix . "modules WHERE module_basename = 'pafiledb'"
	);

$template->assign_vars(array(
	'MESSAGE_TITLE'		=> 'Mod Installation/Upgrading/Uninstalling Information - mod specific db tables',
	'MESSAGE_TEXT'		=> nl2br(mx_do_install_upgrade($sql)))
);

page_header($page_title);

$template->set_filenames(array(
	'body' => 'message_body.html')
);

page_footer();

?>