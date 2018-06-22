<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\controller;

use phpbb\exception\http_exception;

class pafiledb_download extends \orynider\pafiledb\core\pafiledb_public
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;
	
	/** @var \orynider\pafiledb\core\pafiledb_functions */
	protected $pafiledb_functions;
	
	/** @var \orynider\pafiledb\core\pafiledb_user_info */	
	protected $pafiledb_user_info;
	
	/** @var \phpbb\template\template */
	protected $template;	
	
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;		
	
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;	
	
	/**
	* The database tables
	*
	* @var string
	*/
	protected $pa_files_table;

	protected $pa_cat_table;

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions		$functions
	* @param \phpbb\template\template		 	$template
	* @param \phpbb\extension\manager			$ext_manager	
	* @param \phpbb\auth\auth				$auth
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\request\request		 	$request
	* @param string						$root_path
	
	* @param string						$pa_files_table
	* @param string						$pa_cat_table
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,
		\orynider\pafiledb\core\pafiledb_functions $pafiledb_functions,	
		\orynider\pafiledb\core\pafiledb_user_info $pafiledb_user_info,			
		\phpbb\template\template $template,
		\phpbb\extension\manager $ext_manager,	
		
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		$root_path,
		
		$php_ext,		
		
		$pa_files_table,
		$pa_cat_table)
	{
		$this->functions 			= $functions;
		$this->pafiledb_functions 	= $pafiledb_functions;
		$this->pafiledb_user 		= $pafiledb_user_info;		
		$this->template 			= $template;	
		$this->ext_manager	 		= $ext_manager;

		$this->auth 				= $auth;
		$this->db 					= $db;
		$this->request 				= $request;
		$this->root_path 			= $root_path;
		
		$this->php_ext 				= $php_ext;		
		
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		
		$this->ext_name 			= 'orynider/pafiledb';
		$this->module_root_path		= $this->ext_path = $this->ext_manager->get_extension_path($this->ext_name, true);		
	}

	public function handle_download()
	{		
		if ($this->request->is_set('file_id'))
		{
			$file_id = $this->request->variable('file_id', 0, true);
		}
		else
		{
			throw new http_exception(400, 'FILES_NO_ID');
		}		
		
		$mirror_id = $this->request->variable('mirror_id', 0, false);		
		
		$sql = 'SELECT f.*, c.*
			FROM ' . $this->pa_files_table . ' f
			LEFT JOIN ' . $this->pa_cat_table . ' c
				ON f.file_catid = c.cat_id
			WHERE f.file_id = ' . (int) $file_id;
		if (!($result = $this->db->sql_query($sql)))
		{
			$this->functions->message_die(GENERAL_ERROR, 'Couldnt select download', '', __LINE__, __FILE__, $sql);
		}
		
		// =========================================================================
		// Id doesn't match with any file in the database another nice error message
		// =========================================================================
		if (!$file_data = $this->db->sql_fetchrow($result))
		{
			$this->functions->message_die(GENERAL_MESSAGE, $lang['File_not_exist']);
		}
		$this->db->sql_freeresult($result);
		
		// =========================================================================
		// Check if the user is authorized to download the file
		// =========================================================================		
		if (!$this->auth->acl_get('u_pa_files_download'))
		{
			throw new http_exception(401, 'FILES_NO_DOWNLOAD');
		}

		if (!$file_data)
		{
			throw new http_exception(400, 'FILES_DL_NOEXISTS');
		}

		$download_sub_path = $file_data['cat_sub_dir'];
		$real_name = $file_data['real_name'];

		$url = $this->module_root_path . 'pafiledb/uploads/' . $download_sub_path . '/' . $real_name;

		// =========================================================================
		// Update download counter and the last downloaded date
		// =========================================================================
		$current_time = time();
		$file_dls = intval( $file_data['file_dls'] ) + 1;
		$sql = 'UPDATE ' . $this->pa_files_table . '
			SET file_dls = ' . $file_dls . ',
			file_count = file_count + 1,			
			file_last = ' . $current_time . '
			WHERE file_id =  ' . (int) $file_id;			
		if (!($result = $this->db->sql_query($sql)))
		{
			$this->functions->message_die(GENERAL_ERROR, 'Couldnt Update Files table', '', __LINE__, __FILE__, $sql);
		}		
		$this->db->sql_freeresult($result);

		// =========================================================================
		// Update downloader Info for the given file
		// =========================================================================
		$this->pafiledb_user->update_info($file_id);		
		
		header('Content-type: application/octet-stream');
		header("Content-disposition: attachment; filename=\"" . $real_name . "\"");
		header('Content-Length: ' . filesize($url));
		ob_end_flush();
		readfile($url);

	}
}
