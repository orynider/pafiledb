<?php
/**
 *
* @package phpBB2 Mod - pafileDB
* @version $Id: pa_install.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MXP Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
 *
 */
 
/**#@+
* @ignore
*/
namespace orynider\pafiledb\migrations\v09x;

use \phpbb\db\migration\container_aware_migration;
/**#@-*/
class pa_install extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * Assign migration file dependencies for this migration
	 *
	 * @return void
	 * @access public
	 */
	static public function depends_on()
	{
		//return array('\phpbb\db\migration\data\v31x\v314');
		return array('\phpbb\db\migration\data\v320\v320');
	}

	/**
	 * Add the pafiledb table schema to the database
	 *
	 * @return void
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_files'			
				$this->table_prefix . 'pa_files'	=> array(
					'COLUMNS'	=> array(
						'file_id'			=> array('UINT:8', null, 'auto_increment'),
						'file_count'		=> array('UINT:8', 0),
						'file_name'			=> array('VCHAR:255', ''),
						'file_desc'			=> array('MTEXT_UNI',	''),
						'file_longdesc'		=> array('MTEXT_UNI',	''),						
						'file_catid'		=> array('UINT:8', 0),						
						'file_approved'		=> array('TINT:1', 0),
						'file_size'			=> array('INT:11', 0),
						'unique_name'		=> array('VCHAR:255', ''),
						'real_name'			=> array('VCHAR:255', ''),
						'file_dir'			=> array('VCHAR:255', ''),
						'file_creator'		=> array('MTEXT_UNI', ''),						
						'file_version'		=> array('MTEXT_UNI', ''),
						'file_ssurl'		=> array('MTEXT_UNI', ''),						
						'file_sshot_link'	=> array('TINT:1', 0),						
						'file_dlurl'		=> array('MTEXT_UNI', ''),
						'file_license'		=> array('MTEXT_UNI', ''),						
						'file_docsurl'		=> array('MTEXT_UNI', ''),						
						'file_posticon'		=> array('MTEXT_UNI', ''),						
						'file_time'			=> array('UINT:8', 0),
						'user_id'			=> array('INT:8', 0),						
						'cost_per_dl'		=> array('DECIMAL:10', 0.00),
						'poster_ip'			=> array('VCHAR:8', ''),
						'file_update_time'	=> array('INT:50', 0),
						'file_last' 		=> array('INT:50', 0),
						'file_pin' 			=> array('TINT:2', 0),
						'file_disable' 		=> array('TINT:1', 0),
						'disable_msg' 		=> array('MTEXT_UNI', ''),
						'file_broken' 		=> array('TINT:1', 0),
				 		'topic_id' 			=> array('INT:8', 0),
						'file_dls' 			=> array('INT:11', 0),						
						'bbcode_bitfield'	=> array('VCHAR:255', ''),
						'bbcode_uid'		=> array('VCHAR:8', ''),
						'bbcode_options'	=> array('VCHAR:255', ''),						
					),
					'PRIMARY_KEY'	=> 'file_id',
				),
				
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_cat'				
				$this->table_prefix . 'pa_cat'	=> array(
					'COLUMNS'	=> array(
						'cat_id'				=> array('UINT:8', null, 'auto_increment'),
						'cat_name'				=> array('VCHAR', 0),
						'cat_desc'				=> array('MTEXT_UNI', 0),
						'cat_sub_dir'			=> array('VCHAR:255', ''),						
						'cat_parent'			=> array('UINT:8', 0),
						'parents_data'			=> array('MTEXT_UNI',	''),						
						'left_id'				=> array('UINT:8', 0),
						'right_id'				=> array('UINT:8', 0),
						'cat_name_show'			=> array('TINT:1', 0),
						'cat_desc_uid'			=> array('VCHAR:8', ''),
						'cat_desc_bitfield'		=> array('VCHAR:8', 0),
						'cat_desc_options'		=> array('UINT:8', 0),
						'cat_order'				=> array('INT:50' , 0),
						'cat_allow_file'		=> array('TINT:1', '0'),
						'cat_allow_ratings'		=> array('TINT:1', '-1'),
						'cat_allow_comments'	=> array('TINT:1', '-1'),
						'cat_files'				=> array('INT:8', '-1'),
						'cat_last_file_id'		=> array('INT:8', '0'),
						'cat_last_file_name'	=> array('VCHAR:255', ''),
						'cat_last_file_time'	=> array('INT:50', '0'),
						'auth_view'				=> array('TINT:1', '0'),
						'auth_read'				=> array('TINT:1', '0'),
						'auth_view_file'		=> array('TINT:1', '0'),
						'auth_edit_file'		=> array('TINT:1', '0'),
						'auth_delete_file'		=> array('TINT:1', '2'),
						'auth_upload'			=> array('TINT:1', '0'),
						'auth_download'			=> array('TINT:1', '0'),
						'auth_rate'				=> array('TINT:1', '0'),
						'auth_email'			=> array('TINT:1', '0'),
						'auth_view_comment'		=> array('TINT:1', '0'),
						'auth_post_comment'		=> array('TINT:1', '0'),
						'auth_edit_comment'		=> array('TINT:1', '0'),
						'auth_delete_comment'	=> array('TINT:1', '0'),
						'auth_approval'			=> array('TINT:1', '0'),
						'internal_comments'		=> array('TINT:1', '-1'),
						'autogenerate_comments'	=> array('TINT:1', '-1'),
						'comments_forum_id'		=> array('INT:8', '-1'),
						'show_pretext'			=> array('TINT:1', '-1'),
						'notify'				=> array('TINT:1', '-1'),
						'notify_group'			=> array('INT:8', '-1'),
						'auth_approval_edit'	=> array('TINT:1', '0'),						
					),
					'PRIMARY_KEY'	=> 'cat_id',
				),
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_config'				
				$this->table_prefix . 'pa_config' => array(
					'COLUMNS' => array(
						'config_name'	=> array('VCHAR:255', ''),
						'config_value'	=> array('VCHAR_UNI', ''),
						'is_dynamic'	=> array('BOOL', 0),						
					),
					'PRIMARY_KEY'	=> 'config_name',
				),
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_comments'
				/* **/
				$this->table_prefix . 'pa_comments'	=> array(
					'COLUMNS'	=> array(
						'comments_id'			=> array('UINT:8', null, 'auto_increment'),
						'file_id' 				=> array('UINT:8', 0),
						'comments_text' 		=> array('MTEXT_UNI',	''),
						'comments_title' 		=> array('MTEXT_UNI',	''),
						'comments_time' 		=> array('INT:50', 0),
						'comment_bbcode_uid' 	=> array('VCHAR:10', 0),
						'poster_id' 			=> array('INT:8', 0),					
					),
					'PRIMARY_KEY'	=> 'comments_id',
					'FULLTEXT_KEY'	=> 'comment_bbcode_uid',					
				),				
				/* **/
				
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_custom'
				$this->table_prefix . 'pa_custom'	=> array(
					'COLUMNS'	=> array(	
						'custom_id'				=> array('UINT:8', null, 'auto_increment'),
						'custom_name'  			=> array('TEXT',	''),
						'custom_description'  	=> array('TEXT',	''),
						'data'  				=> array('TEXT',	''),
						'field_order' 			=> array('INT:20', 0),
						'field_type' 			=> array('TINT:2', 0),
						'regex' 				=> array('VCHAR:255', ''),
					),					  
					'PRIMARY_KEY'	=> 'custom_id'
				),
		
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_customdata'
				$this->table_prefix . 'pa_customdata'	=> array(
					'COLUMNS'	=> array(				
					  'customdata_file' 		=> array('INT:50', 0),
					  'customdata_custom' 		=> array('INT:50', 0),
					  'data' 					=> array('TEXT',	''),
					),
				),

				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_download_info'
				$this->table_prefix . 'pa_download_info'	=> array(
					'COLUMNS'	=> array(				
					  'file_id' 				=> array('UINT:8', 0),
					  'user_id' 				=> array('UINT:8', 0),
					  'downloader_ip'			=> array('VCHAR:10', ''),
					  'downloader_os' 			=> array('VCHAR:255', ''),
					  'downloader_browser' 		=> array('VCHAR:255', ''),
					  'browser_version' 		=> array('VCHAR:255', ''),
					),
				),

				// --------------------------------------------------------
				/* **/
				$this->table_prefix . 'pa_mirrors'	=> array(
					'COLUMNS'	=> array(
						'mirror_id'			=> array('UINT:8', null, 'auto_increment'),
						'file_id' 			=> array('UINT:8', 0),
						'unique_name' 		=> array('VCHAR:255', ''),
						'file_dir' 			=> array('VCHAR:255', ''),
						'file_dlurl' 		=> array('VCHAR:255', ''),
						'mirror_location' 	=> array('VCHAR:255', ''),					
					),
					'PRIMARY_KEY'	=> 'mirror_id',
				),				
				/** **/
				// Table structure for table 'phpbb_pa_license'
				$this->table_prefix . 'pa_license'	=> array(
					'COLUMNS'	=> array(
						'license_id'		=> array('UINT:8', null, 'auto_increment'),
						'license_name'		=> array('TEXT_UNI',	''),						
						'license_text'		=> array('TEXT_UNI', 0),					
					),
					'PRIMARY_KEY'	=> 'license_id',
				),
				/** **/		
				// Table structure for table `pa_auth`
				$this->table_prefix . 'pa_auth'	=> array(
					'COLUMNS'	=> array(		
						'group_id' 			=> array('INT:8', 0),
						'cat_id' 			=> array('INT:5', 0),
						'auth_view' 		=> array('TINT:1', 0),
						'auth_read' 		=> array('TINT:1', 0),
						'auth_view_file' 	=> array('TINT:1', 0),
						'auth_edit_file' 	=> array('TINT:1', 0),
						'auth_delete_file' 	=> array('TINT:1', 0),
						'auth_upload' 		=> array('TINT:1', 0),
						'auth_download' 	=> array('TINT:1', 0),
						'auth_rate' 		=> array('TINT:1', 0),
						'auth_email' 		=> array('TINT:1', 0),
						'auth_view_comment' => array('TINT:1', 0),
						'auth_post_comment' => array('TINT:1', 0),
						'auth_edit_comment' => array('TINT:1', 0),
						'auth_delete_comment' => array('TINT:1', 0),
						'auth_approval' 	=> array('TINT:1', 0),
						'auth_approval_edit' => array('TINT:1', 0),
						'auth_mod' 			=> array('TINT:1', 1),
						'auth_search' 		=> array('TINT:1', 1),
						'auth_stats' 		=> array('TINT:1', 1),
						'auth_toplist' 		=> array('TINT:1', 1),
						'auth_viewall' 		=> array('TINT:1', 1),
					),
					'KEY'	=> 'group_id',
					'KEY'	=> 'cat_id',					
				),			
				// --------------------------------------------------------
				// Table structure for table 'phpbb_pa_votes'
				$this->table_prefix . 'pa_votes'	=> array(
					'COLUMNS'	=> array(				
					  'user_id' 			=> array('UINT:8', 0),
					  'votes_ip' 			=> array('VCHAR:10', ''),
					  'votes_file' 			=> array('INT:50', 0),
					  'rate_point' 			=> array('TINT:3', 0),
					  'voter_os' 			=> array('VCHAR:255', ''),
					  'voter_browser' 		=> array('VCHAR:255', ''),
					  'browser_version' 	=> array('VCHAR:8',  0),
					),					  
					'KEY' 	=> 'user_id',
					'KEY' 	=> 'votes_file',
					'KEY' 	=> 'votes_ip',
					'KEY' 	=> 'voter_os',
					'KEY' 	=> 'voter_browser',
					'KEY' 	=> 'browser_version',
					'KEY' 	=> 'rate_point'
				),				
			),
		);
	}

	/**
	 * Add or update data in the database
	 *
	 * @return void
	 * @access public
	 */
	public function update_data()
	{
		return array(				
			
			// Add permissions
			array('permission.add', array('u_pa_files_use', true)),
			array('permission.add', array('u_pa_files_download', true)),
			array('permission.add', array('a_pa_files', true)),

			 // Set permissions
			array('permission.permission_set', array('REGISTERED', 'u_pa_files_use', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_pa_files_download', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'a_pa_files', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_pa_files_use', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_pa_files_download', 'group')),	
		
			// Insert sample pafildb data
			array('custom', array(array($this, 'insert_sample_data'))),

			// Insert sample pafildb config settings   
			array('custom', array(array(&$this, 'install_config'))),			


			// Add permission
			array('permission.add', array('u_pa_files_upload', true)),
			// Set permission
			array('permission.permission_set', array('ADMINISTRATORS', 'u_pa_files_upload', 'group')),	
			
			// Add module to acp
			array('module.add', array(
				'acp', 
				'ACP_CAT_DOT_MODS', 
				'ACP_PA_FILES',
				array(
					'module_enabled'  => 1,
					'module_display'  => 1,
					'module_langname' => 'ACP_PA_FILES',
					'module_auth'     => 'ext_orynider/pafiledb && acl_a_pa_files',
				)				
			)),
			array('module.add', array(
				'acp', 
				'ACP_PA_FILES',
				array(
					'module_basename' => '\orynider\pafiledb\acp\pafiledb_module',
					'modes' => array('config', 'categories', 'downloads'),
				),
			)),
		);	
	}

	/**
	 * Drop the pafiledb table schema from the database
	 *
	 * @return void
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'pa_cat',
				$this->table_prefix . 'pa_auth',				
				$this->table_prefix . 'pa_comments',				
				$this->table_prefix . 'pa_config',
				$this->table_prefix . 'pa_custom',
				$this->table_prefix . 'pa_customdata',
				$this->table_prefix . 'pa_download_info',
				$this->table_prefix . 'pa_license',
				$this->table_prefix . 'pa_votes',				
				$this->table_prefix . 'pa_mirrors',
				$this->table_prefix . 'pa_files',				
			),
		);
	}

	/**
	 * Custom function query permission roles
	 *
	 * @return void
	 * @access public
	 */
	private function role_exists($role)
	{
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = '" . $this->db->sql_escape($role) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return $role_id;
	}
	
	/**
	* Set config value. Creates missing config entry.
	* Only use this if your config value might exceed 255 characters, otherwise please use set_config
	*
	* @param string $config_name Name of config entry to add or update
	* @param mixed $config_value Value of config entry to add or update
	*/
	private function set_pafiledb_config($config_name, $config_value, $use_cache = true)
	{
		// Read out config values
		$pafiledb_config = $this->config_values();

		$sql = 'UPDATE ' . $this->table_prefix . "pa_config
			SET config_value = '" . $this->db->sql_escape($config_value) . "'
			WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows() && !isset($pafiledb_config[$config_name]))
		{
			$sql = 'INSERT INTO ' . $this->table_prefix . 'pa_config ' . $this->db->sql_build_array('INSERT', array(
				'config_name'	=> $config_name,
				'config_value'	=> $config_value));
			$this->db->sql_query($sql);
		}

		$this->pafiledb_config[$config_name] = $config_value;
	}
	
	/**
	* install config values. 	
	*/	
	public function install_config()
	{
		// Read out config values
		$pafiledb_config = $this->config_values();		
		$this->pa_config_table = $this->table_prefix . 'pa_config';
		foreach (self::$configs as $key => $new_value)
		{
			
			// Read out old config db values			
			$old_value = !isset($pafiledb_config[$key]) ? $pafiledb_config[$key] : false;		
			// We keep out old config db values			
			//$new_value = !isset($pafiledb_config[$key]) ? $pafiledb_config[$key] : $new_value;		
						
			if ($old_value !== false)
			{
				$sql .= " AND config_value = '" . $this->db->sql_escape($old_value) . "'";
			}		
			
			if (isset(self::$is_dynamic[$config_name]))
			{
				$use_cache  = true;
			}
			else
			{
				$use_cache  = false;
			}				

			if (isset($this->pafiledb_config[$key]))
			{
				$sql = 'UPDATE ' . $this->pa_config_table . "
					SET config_value = '" . $this->db->sql_escape($new_value) . "'
					WHERE config_name = '" . $this->db->sql_escape($key) . "'";
				$this->db->sql_query($sql);
			}
			else
			{
				$sql = 'INSERT INTO ' . $this->pa_config_table . ' ' . $this->db->sql_build_array('INSERT', array(
					'config_name'	=> $key,
					'config_value'	=> $new_value,
					'is_dynamic'	=> ($use_cache) ? 0 : 1));
				$this->db->sql_query($sql);
			}			
			
			$this->pafiledb_config[$key] = $pafiledb_config[$key] = $new_value;			
		}
		return true;
	}

	/**
	* Obtain pafiledb config values
	*/
	public function config_values()
	{	
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'pa_config'))
		{
			$sql = 'SELECT *
				FROM ' . $this->table_prefix . 'pa_config';
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!empty($row))
			{
				$pafiledb_config[$row['config_name']] = $row['config_value'];
				return $pafiledb_config;
			}
		}
		else
		{
			return array();
		}			
	}	
	
	static public $is_dynamic = array(
		'comments_pagination',
		'pagination',
	);

	static public $configs = array(
	
		//
		// Config values
		//

		// General
		'enable_module' => '1', // settings_disable
		'module_name' => 'Download Database', // settings_dbname
		'wysiwyg_path' => 'modules/mx_shared/',
		'upload_dir' => 'pafiledb/uploads/',
		'screenshots_dir' => 'pafiledb/images/screenshots/',
		'costs_per_dl'	=> '0.00',
		
		// Files
		'max_file_size' => '10485760',
		'forbidden_extensions' => 'php, php3, php4, phtml, pl, asp, aspx, cgi',
		'hotlink_prevent' => '1',
		'hotlink_allowed' => '',
		'tpl_php' => '0',

		// Appearance
		'sort_method' => 'file_time',
		'sort_order' => 'DESC',
		'pagination' => '20', // art_pagination & settings_file_page

		'settings_stats' => '',
		'settings_viewall' => '1',
		'settings_dbdescription' => '',
		'settings_topnumber' => '10',

		'use_simple_navigation' => '1',
		'cat_col' => '2',
		'settings_newdays' => '1',

		// Comments
		'use_comments' => '0', // comments_show		
		'internal_comments' => '1', // NEW
		'formatting_comment_wordwrap' => '1', // formatting_comment_fixup
		'formatting_comment_image_resize' => '300', // NEW
		'formatting_comment_truncate_links' => '1', // NEW
		'max_comment_subject_chars' => '50', // NEW
		'max_comment_chars' => '5000',
		'allow_comment_wysiwyg' => '0', // allow_wysiwyg_comments & allow_wysiwyg
		'allow_comment_html' => '1',
		'allow_comment_bbcode' => '1',
		'allow_comment_smilies' => '1',
		'allow_comment_links' => '1',
		'allow_comment_images' => '0',
		'no_comment_image_message' => '[No image please]',
		'no_comment_link_message' => '[No links please]',
		'allowed_comment_html_tags' => 'b,i,u,a', // NEW
		'del_topic' => '1', // NEW			
		'autogenerate_comments' => '1',	// NEW
		'comments_pagination' => '5',
		'num_comments' => '5',		
		'comments_forum_id' => '0', // New					
		'comments_lock_enable'	=> '0',		

		// Ratings
		'use_ratings' => '0',
		'votes_check_userid' => '1',
		'votes_check_ip' => '1',

		// Instructions
		'show_pretext' => '0', // NEW
		'pt_header' => 'File Submission Instructions', // NEW
		'pt_body' => 'Please check your references and include as much information as you can.', // NEW

		// Notifications
		'notify' => '0', // pm_notify
		'notify_group' => '0',	// NEW

		// Permissions
		'auth_search' => '0',
		'auth_stats' => '0',
		'auth_toplist' => '0',
		'auth_viewall' => '0',
		
		//Screen Shots Settings  (Go Here)
		'resize_ss', '0', //To do (for security reasons to filter uploaded files using php GD extension)		
		
		// ACP	
		'pagination_acp' 		=> '5',
		'pagination_user' 		=> '3',
		'pagination_downloads' 	=> '25',					
	
		//Version
		'pa_module_version'				=> '0.9.0',
	);	
	
	/**
	 * Custom function to add sample data to the database
	 *
	 * @return void
	 * @access public
	 */
	public function insert_sample_data()
	{
		$user = $this->container->get('user');
		
		add_log('admin', 'Download Manger mod Install/Upgrade', 'Version 0.9.0 Alfa');
		
		// Define sample category data
		$sample_data_cat_id_eq2 = array(
			array(
				'cat_id'				=> 2,
				'cat_name'				=> 'My Example Category #2',
				'cat_desc'				=> 'My Example Category Description.',
				'cat_sub_dir'			=> '',						
				'cat_parent'			=> 0,
				'parents_data'			=> '0',						
				'left_id'				=> 1,
				'right_id'				=> 3,
				'cat_name_show'			=> 1,	
				'cat_desc_uid'			=> 'a9fmpm6m',
				'cat_desc_bitfield'		=> 'QQ==',
				'cat_desc_options'		=> 7,
				'cat_order'				=> 0,
				'cat_allow_file'		=> '1',
				'cat_allow_ratings'		=> '-1',
				'cat_allow_comments'	=> '-1',
				'cat_files'				=> '-1',
				'cat_last_file_id'		=> '0',
				'cat_last_file_name'	=> '',
				'cat_last_file_time'	=> '0',
				'auth_view'				=> '0',
				'auth_read'				=> '0',
				'auth_view_file'		=> '0',
				'auth_edit_file'		=> '0',
				'auth_delete_file'		=> '2',
				'auth_upload'			=> '0',
				'auth_download'			=> '0',
				'auth_rate'				=> '0',
				'auth_email'			=> '0',
				'auth_view_comment'		=> '0',
				'auth_post_comment'		=> '0',
				'auth_edit_comment'		=> '0',
				'auth_delete_comment'	=> '0',
				'auth_approval'			=> '0',
				'internal_comments'		=> '-1',
				'autogenerate_comments'	=> '-1',
				'comments_forum_id'		=> '-1',
				'show_pretext'			=> '-1',
				'notify'				=> '-1',
				'notify_group'			=> '-1',
				'auth_approval_edit'	=> '0',						
			),		
		);	
		
		// Define sample category data
		$sample_data_cat_id_eq1 = array(
			array(
				'cat_id'				=> 1,
				'cat_name'				=> 'Just a test cagegory #1',
				'cat_desc'				=> 'Just a test category description.',
				'cat_sub_dir'			=> '',						
				'cat_parent'			=> 0,
				'parents_data'			=> '0',						
				'left_id'				=> 1,
				'right_id'				=> 2,
				'cat_name_show'			=> 1,	
				'cat_desc_uid'			=> 'a9fmpm6m',
				'cat_desc_bitfield'		=> 'QQ==',
				'cat_desc_options'		=> 7,
				'cat_order'				=> 0,
				'cat_allow_file'		=> '1',
				'cat_allow_ratings'		=> '-1',
				'cat_allow_comments'	=> '-1',
				'cat_files'				=> '-1',
				'cat_last_file_id'		=> '0',
				'cat_last_file_name'	=> '',
				'cat_last_file_time'	=> '0',
				'auth_view'				=> '0',
				'auth_read'				=> '0',
				'auth_view_file'		=> '0',
				'auth_edit_file'		=> '0',
				'auth_delete_file'		=> '2',
				'auth_upload'			=> '0',
				'auth_download'			=> '0',
				'auth_rate'				=> '0',
				'auth_email'			=> '0',
				'auth_view_comment'		=> '0',
				'auth_post_comment'		=> '0',
				'auth_edit_comment'		=> '0',
				'auth_delete_comment'	=> '0',
				'auth_approval'			=> '0',
				'internal_comments'		=> '-1',
				'autogenerate_comments'	=> '-1',
				'comments_forum_id'		=> '-1',
				'show_pretext'			=> '-1',
				'notify'				=> '-1',
				'notify_group'			=> '-1',
				'auth_approval_edit'	=> '0',						
			),		
		);			
		
		// Define sample article data
		$sample_data_files = array(
			array(
				'file_id'			=> 1,
				'file_count'		=> 1,
				'file_name'			=> 'Test File #1',	
				'file_desc'			=> 'A test file for the pafileDB extension.',
				'file_longdesc'		=> 'Sample file description: Test file for the pafileDB extension.',						
				'file_catid'		=> 2,						
				'file_approved'		=> 1,
				'file_size'			=> 3,
				'unique_name'		=> '',
				'real_name'			=> 'forum.gif',
				'file_dir'			=> '',
				'file_creator'		=> 'phpBB Team',						
				'file_version'		=> '1.0',
				'file_ssurl'		=> '',						
				'file_sshot_link'	=> 0,						
				'file_dlurl'		=> '',
				'file_license'		=> 'GNU GPL-2',						
				'file_docsurl'		=> '',						
				'file_posticon'		=> '',						
				'file_time'			=> time(),
				'user_id'			=> $user->data['user_id'],
				//'poster_name'		=> $user->data['username'],
				//'poster_colour'		=> $user->data['user_colour'],				
				'poster_ip'			=> '',
				'cost_per_dl'		=> '0.00',				
				'file_update_time'	=> 0,
				'file_last' 		=> 0,
				'file_pin' 			=> 0,
				'file_disable' 		=> 0,
				'disable_msg' 		=> '',
				//''enable_bbcode'				=> 1,
				//''enable_smilies'			=> 1,
				//''enable_magic_url'			=> 1,				
				'file_broken' 		=> 0,
				'topic_id' 			=> 0,
				'file_dls' 			=> 0,						
				'bbcode_bitfield'	=> 'QQ==',
				'bbcode_uid'		=> '2p5lkzzx',
				'bbcode_options'	=> '',			
			),
		);
			
		// Insert sample data
		$this->db->sql_multi_insert($this->table_prefix . 'pa_cat', $sample_data_cat_id_eq1);
		$this->db->sql_multi_insert($this->table_prefix . 'pa_cat', $sample_data_cat_id_eq2);		
		$this->db->sql_multi_insert($this->table_prefix . 'pa_files', $sample_data_files);
	}
}
