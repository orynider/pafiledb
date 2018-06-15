<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: admin_ug_auth_manage.php,v 1.11 2008/10/16 23:37:18 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\acp;

class ug_auth_manage_module
{
	public	$u_action;
	
	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;
		global $db, $images, $template, $template, $lang, $phpEx, $pafiledb_functions, $pafiledb_cache, $pafiledb_config, $phpbb_root_path, $module_root_path, $mx_root_path, $mx_request_vars;
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels, $global_auth_fields;
		global $optionlist_mod, $optionlist_acl_adv;
		
		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('orynider.pafiledb.controller.admin.controller');

		$params = array( 'mode' => 'mode', 'user_id' => POST_USERS_URL, 'group_id' => POST_GROUPS_URL );

		foreach( $params as $var => $param )
		{
			$$var = $request->variable($param, '');
		}
		
		// Requests
		$mode = ($request->is_set('modes')) ? $request->variable('modes', '') : $request->variable('mode', 'catauth_manage');
		$action = $request->variable('action', '');
		$cat_id = $request->variable('cat_id', 0);
		$file_id = $request->variable('file_id', 0);			
		
		$user_id = intval( $user_id );
		$group_id = intval( $group_id );

		$cat_auth_fields = array( 'auth_view', 'auth_read', 'auth_view_file', 'auth_edit_file', 'auth_delete_file', 'auth_upload', 'auth_download', 'auth_rate', 'auth_email', 'auth_view_comment', 'auth_post_comment', 'auth_edit_comment', 'auth_delete_comment' );
		$global_auth_fields = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );

		$global_fields_names = array(
			'auth_search' 	=> $lang['Auth_search'],
			'auth_stats' 	=> $lang['Auth_stats'],
			'auth_toplist' 	=> $lang['Auth_toplist'],
			'auth_viewall' 	=> $lang['Auth_viewall']
		);

		$field_names = array(
			'auth_view' 			=> $lang['View'],
			'auth_read' 			=> $lang['Read'],
			'auth_view_file' 		=> $lang['View_file'],
			'auth_edit_file' 		=> $lang['Edit_file'],
			'auth_delete_file' 		=> $lang['Delete_file'],
			'auth_upload' 			=> $lang['Upload'],
			'auth_download' 		=> $lang['Download_file'],
			'auth_rate' 			=> $lang['Rate'],
			'auth_email' 			=> $lang['Email'],
			'auth_view_comment'		=> $lang['View_comment'],
			'auth_post_comment' 	=> $lang['Post_comment'],
			'auth_edit_comment' 	=> $lang['Edit_comment'],
			'auth_delete_comment' 	=> $lang['Delete_comment']
		);
		
		if ($mode == 'catauth_manage')
		{
			$this->u_action = $action = $u_action = 'catauth_manage';
		}
		else
		{
			$this->u_action = $action = $u_action = 'ug_auth_manage';
		}
		
		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);			
		
		$permissions_menu = array(
			append_sid( "admin_pafiledb.$phpEx?action=catauth_manage&mode=catauth_manage" ) => $lang['Cat_Permissions'],
			append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=user" ) 			=> $lang['User_Permissions'],
			append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=group" ) 			=> $lang['Group_Permissions'],
			append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=global_user" ) 	=> $lang['User_Global_Permissions'],
			append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=global_group" ) 	=> $lang['Group_Global_Permissions']
		);

		foreach( $permissions_menu as $url => $l_name )
		{
			$template->assign_block_vars('pertype', array(
				'U_NAME' => $url,
				'L_NAME' => $l_name
			));
		}
		
		// Here we set the main switches to use within the ACP
		switch ($action)
		{
			case 'catauth_manage':
				$this->page_title = $user->lang['Cat_Permissions'];
				$this->tpl_name = 'pa_auth_cat_body';
				if (!extension_loaded("tokenizer")) print "tokenizer extension not loaded!";
				$admin_controller->admin_cat_main();
			break;
			case 'ug_auth_manage':
				$this->page_title = $user->lang['User_Permissions'];
				$this->tpl_name = 'acp_pa_ug_auth_manage';
				if (!extension_loaded("tokenizer")) print "tokenizer extension not loaded!";
				switch ($mode)
				{
					case 'user';
						$this->page_title = $user->lang['User_Permissions'];
						$this->tpl_name = 'pa_auth_ug_body';
						$admin_controller->pa_auth_ug_select();
						$admin_controller->admin_display_cat_auth();						
					break;

					case 'group';
						$this->page_title = $user->lang['Group_Permissions'];
						$this->tpl_name = 'ug_auth_manage_body';
						$admin_controller->ug_auth_manage();
					break;

					case 'global_user';
						$this->page_title = $user->lang['User_Global_Permissions'];
						$this->tpl_name = 'pa_auth_ug_body';
						$admin_controller->pa_auth_ug_select();
					break;

					case 'global_group';
						$this->page_title = $user->lang['Group_Global_Permissions'];
						$this->tpl_name = 'ug_auth_manage_body';						
						$admin_controller->ug_auth_manage();
					break;
				}
				$admin_controller->admin_display_cat_auth_ug();
			break;
		}		
	}
}
?>