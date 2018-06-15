<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\acp;

class pafiledb_module
{
	public	$u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('orynider.pafiledb.controller.admin.controller');

		// Requests
		$action = $request->variable('action', '');
		$cat_id = $request->variable('cat_id', 0);
		$file_id = $request->variable('file_id', 0);
		if ($request->is_set_post('add'))
		{
			$action = 'add';
		}

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Here we set the main switches to use within the ACP
		switch ($mode)
		{
			case 'config':
				$this->page_title = $user->lang['ACP_MANAGE_CONFIG'];
				$this->tpl_name = 'pa_acp_settings';
				$admin_controller->display_config();
			break;

			case 'categories':
				$this->page_title = $user->lang['ACP_MANAGE_CATEGORIES'];
				$this->tpl_name = 'pa_acp_cat_manage';
				if (!extension_loaded("tokenizer")) print "tokenizer extension not loaded!";
				switch ($action)
				{
					// Create a new category
					case 'create':
						$this->page_title = $user->lang['ACP_NEW_CAT'];
						$admin_controller->create_cat();

						// Return to stop execution of this script
						return;
					break;

					// Edit an existing category
					case 'edit':
						$this->page_title = $user->lang['ACP_EDIT_CAT'];
						$admin_controller->edit_cat();

						// Return to stop execution of this script
						return;
					break;
					// Delete an existing category
					case 'delete':
						$this->page_title = $user->lang['ACP_DEL_CAT'];
						$admin_controller->delete_cat();

						// Return to stop execution of this script
						return;
					break;

					// Move a category to another position
					case 'move':
						$admin_controller->move_cat();
					break;
				}
				$admin_controller->manage_cats();
			break;
			case 'downloads':
				$this->page_title = $user->lang['ACP_EDIT_DOWNLOADS'];
				$this->tpl_name = 'acp_pa_files_list';
				if (!extension_loaded("tokenizer")) print "tokenizer extension not loaded!";
				switch ($action)
				{
					case 'new_download';
						$this->page_title = $user->lang['ACP_NEW_DOWNLOAD'];
						$this->tpl_name = 'acp_pa_files_new';
						$admin_controller->new_download();
					break;

					case 'copy_new';
						$this->page_title = $user->lang['ACP_NEW_DOWNLOAD'];
						$this->tpl_name = 'acp_pa_files_new_copy';
						$admin_controller->copy_new();
					break;

					case 'edit';
						$this->page_title = $user->lang['ACP_EDIT_DOWNLOADS'];
						$this->tpl_name = 'acp_pa_files_edit';
						$admin_controller->edit();
					break;

					case 'add_new';
						$admin_controller->add_new();
					break;

					case 'update';
						$admin_controller->update();
					break;

					case 'delete';
						$admin_controller->delete();
					break;
				}
				$admin_controller->display_downloads();
			break;
		}
	}
}
?>