<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\controller;

// Auth settings (blockCP)
!defined('AUTH_LIST_ALL') ? define('AUTH_LIST_ALL', 0) : false;
!defined('AUTH_ALL') ? define('AUTH_ALL', 0) : false;
!defined('AUTH_REG') ? define('AUTH_REG', 1) : false;
!defined('AUTH_ACL') ? define('AUTH_ACL', 2) : false;
!defined('AUTH_MOD') ? define('AUTH_MOD', 3) : false;
!defined('AUTH_ADMIN') ? define('AUTH_ADMIN', 5) : false;
!defined('AUTH_ANONYMOUS') ? define('AUTH_ANONYMOUS', 9) : false;

!defined('AUTH_VIEW') ? define('AUTH_VIEW', 1) : false;
!defined('AUTH_READ') ? define('AUTH_READ', 2) : false;
!defined('AUTH_POST') ? define('AUTH_POST', 3) : false;
!defined('AUTH_REPLY') ? define('AUTH_REPLY', 4) : false;
!defined('AUTH_EDIT') ? define('AUTH_EDIT', 5) : false;
!defined('AUTH_DELETE') ? define('AUTH_DELETE', 6) : false;
!defined('AUTH_ANNOUNCE') ? define('AUTH_ANNOUNCE', 7) : false;
!defined('AUTH_STICKY') ? define('AUTH_STICKY', 8) : false;
!defined('AUTH_POLLCREATE') ? define('AUTH_POLLCREATE', 9) : false;
!defined('AUTH_VOTE') ? define('AUTH_VOTE', 10) : false;
!defined('AUTH_ATTACH') ? define('AUTH_ATTACH', 11) : false;

class admin_controller extends \orynider\pafiledb\core\pafiledb_auth
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\cache\cache */
	protected $cache;	

	/** @var \orynider\pafiledb\core\functions_cache */
	protected $functions_cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;
	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var ContainerBuilder */
	protected $phpbb_container;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string */
	protected $php_ext;

	/** @var string phpBB root path */
	protected $root_path;
	
	/** @var string */	
	protected $cat_rowset;
	
	/** @var string */	
	protected $subcat_rowset;
	
	/** @var string */	
	protected $comments;
	
	/** @var string */	
	protected $ratings;
	
	/** @var string */	
	protected $information;
	
	/** @var string */	
	protected $notification;	

	/**
	* The database tables
	*
	* @var string
	*/
	protected $pa_files_table;

	protected $pa_cat_table;

	protected $pa_config_table;
	
	protected $pa_auth_access_table;

	/** @var \phpbb\files\factory */
	protected $files_factory;

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions						$functions
	* @param \phpbb\template\template		 					$this->template
	* @param \phpbb\user									$user
	* @param \phpbb\log									$log
	* @param \phpbb\cache\service								$cache
	* @param \orynider\pafiledb\core\functions_cache					$functions_cache		
	* @param \phpbb\db\driver\driver_interface					$this->db
	* @param \phpbb\request\request		 					$request
	* @param \phpbb\pagination								$pagination
	* @param \phpbb\extension\manager							$ext_manager
	* @param \phpbb\path_helper								$path_helper
	* @param string 										$php_ext
	* @param string 										$root_path
	* @param string 										$pa_files_table
	* @param string 										$pa_cat_table
	* @param string 										$pa_config_table
	* @param \phpbb\files\factory								$files_factory
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\log\log $log,
		\phpbb\cache\service $cache,
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,			
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\pagination $pagination,
		\phpbb\extension\manager $ext_manager,
		\phpbb\path_helper $path_helper,
		$php_ext, $root_path,
		$pa_files_table,
		$pa_cat_table,
		$pa_config_table,
		$pa_auth_access_table,
		\phpbb\files\factory $files_factory = null)
	{
		$this->functions 			= $functions;
		$this->template 			= $template;
		$this->user 				= $user;
		$this->log 					= $log;
		$this->cache 				= $cache;
		$this->db 					= $db;
		$this->request 				= $request;
		$this->pagination 			= $pagination;
		$this->ext_manager	 		= $ext_manager;
		$this->path_helper	 		= $path_helper;
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_config_table 		= $pa_config_table;
		$this->pa_auth_access_table = $pa_auth_access_table;
		$this->files_factory 		= $files_factory;
		
		$this->ext_name 			= $this->request->variable('ext_name', 'orynider/pafiledb');
		$this->module_root_path		= $this->ext_path = $this->ext_manager->get_extension_path($this->ext_name, true);
		$this->ext_path_web			= $this->path_helper->update_web_root_path($this->module_root_path);
		
		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
		
		global $debug;
		
		$this->auth_fields = array( 'auth_view', 'auth_read', 'auth_view_file', 'auth_edit_file', 'auth_delete_file', 'auth_upload', 'auth_download', 'auth_rate', 'auth_email', 'auth_view_comment', 'auth_post_comment', 'auth_edit_comment', 'auth_delete_comment', 'auth_approval', 'auth_approval_edit' );
		$this->auth_fields_global = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );

		$this->cat_rowset = $this->functions->cat_rowset;
		$this->subcat_rowset = $this->functions->subcat_rowset;
		$this->comments = $this->functions->comments;
		$this->ratings = $this->functions->ratings;
		$this->information = $this->functions->information;
		$this->notification = $this->functions->notification;
		
		// Read out config values
		$pafiledb_config = $this->functions->config_values();
		$this->backend = $this->functions->confirm_backend();
		
		//print_r($this->cat_rowset);						
	}

	public function display_config()
	{
		$form_action = $this->u_action . '&amp;action=add';

		// Read out config values
		$pa_config = $this->functions->config_values();

		$submit = ( $this->request->is_set('submit') ) ? true : false;
		$size = ( $this->request->is_set('max_file_size') ) ? $this->request->variable('max_file_size', @ini_get('upload_max_filesizefilesize(')) : '';		
		$set_pagination = $this->request->is_set('action_set_pagination') ? true : false;

		foreach ($pa_config as $config_name => $config_value)
		{			
			// Values for pa_config
			$pa_config[$config_name] = $config_value;

			$new[$config_name] = ($this->request->is_set($config_name)) ? $this->request->variable($config_name, $pa_config[$config_name]) : $pa_config[$config_name];

			if ( ( empty( $size ) ) && ( !$submit ) && ( $config_name == 'max_file_size' ) )
			{
				$size = ( intval( $pa_config[$config_name] ) >= 1048576 ) ? 'mb' : ( ( intval( $pa_config[$config_name] ) >= 1024 ) ? 'kb' : 'b' );
			}

			if ( ( !$submit ) && ( $config_name == 'max_file_size' ) )
			{
				if ( $new[$config_name] >= 1048576 )
				{
					$new[$config_name] = round( $new[$config_name] / 1048576 * 100 ) / 100;
				}
				else if ( $new[$config_name] >= 1024 )
				{
					$new[$config_name] = round( $new[$config_name] / 1024 * 100 ) / 100;
				}
			}

			if ( $submit )
			{
				if ( $config_name == 'max_file_size' )
				{
					$new[$config_name] = ( $size == 'kb' ) ? round( $new[$config_name] * 1024 ) : ( ( $size == 'mb' ) ? round( $new[$config_name] * 1048576 ) : $new[$config_name] );
				}

				if ( $config_name == 'tpl_php' && ($this->request->is_set($config_name)) && $new[$config_name] != $pa_config[$config_name] )
				{
					$this->template->compile_cache_clear();
				}

				$this->functions->set_config($config_name, $new[$config_name]);
			}
		}			
			
		if ( $set_pagination === true )
		{			
			// pagination_acp			
			$configs = array (
				'pagination_acp'		=> $this->request->variable('pagination_acp', 0),
				'pagination_user'		=> $this->request->variable('pagination_user', 0),
				'use_comments'			=> $this->request->variable('use_comments', 0),
				'comments_forum_id'		=> $this->request->variable('comments_forum_id', 0),
				'comments_lock_enable'	=> $this->request->variable('comments_lock_enable', 0),
				'pagination_downloads'	=> $this->request->variable('pagination_downloads', 0),
			);

			// Check if pagination_acp is at least 5
			$check_acp = $this->request->variable('pagination_acp', 0);
			if ($check_acp < 5)
			{
				trigger_error($this->user->lang['ACP_PAGINATION_ERROR_ACP'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Check if pagination_user is at least 3
			$check_user = $this->request->variable('pagination_user', 0);
			if ($check_user < 3)
			{
				trigger_error($this->user->lang['ACP_PAGINATION_ERROR_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Check if pagination_downloads is at least 10
			$check_user = $this->request->variable('pagination_downloads', 0);
			if ($check_user < 10)
			{
				trigger_error($this->user->lang['ACP_PAGINATION_ERROR_DOWNLOADS'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Check if announce forum id exists
			if ($this->request->variable('use_comments', 0) == 1)
			{
				$check_forum_id = $this->request->variable('comments_forum_id', 0);
				$sql = 'SELECT *
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $check_forum_id;
				$result = $this->db->sql_query($sql);
				$check_id = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ( empty($check_id) )
				{
					trigger_error($this->user->lang['ACP_FORUM_ID_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
			
			foreach ($configs as $key => $new_value)
			{
				// Update config values
				$this->functions->set_config($key, $new_value);
			}
			
			// Log message
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_UPDATED');

			trigger_error($this->user->lang['ACP_CONFIG_SUCCESS'] . adm_back_link($this->u_action));
		}
		else
		{			
			
			//$this->template->set_filenames(array('admin' => 'admin/pa_acp_settings.html'));
			$cat_auth_levels = array( 'ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN' );
			$cat_auth_const = array( AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN );
			$global_auth = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );
			$auth_select = array();

			foreach( $global_auth as $auth )
			{
				$auth_select[$auth] = '&nbsp;<select name="' . $auth . '">';
				for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
				{
					$selected = ( $new[$auth] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
					$auth_select[$auth] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $this->user->lang['Category_' . $cat_auth_levels[$k]] . '</option>';
				}
				$auth_select[$auth] .= '</select>&nbsp;';
			}

			//
			// General Settings
			//
			$module_name = $new['module_name'];

			$enable_module_yes = ( $new['enable_module'] ) ? "checked=\"checked\"" : "";
			$enable_module_no = ( !$new['enable_module'] ) ? "checked=\"checked\"" : "";

			$wysiwyg_path = $new['wysiwyg_path'];
			$upload_dir = $new['upload_dir'];
			$screenshots_dir = $new['screenshots_dir'];

			//
			// File
			//
			$hotlink_prevent_yes = ( $new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";
			$hotlink_prevent_no = ( !$new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";

			$hotlink_allowed = $new['hotlink_allowed'];

			$php_template_yes = ( $new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";
			$php_template_no = ( !$new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";

			$max_file_size = $new['max_file_size'];

			$forbidden_extensions = $new['forbidden_extensions'];

			//
			// Appearance
			//
			$pagination = $new['pagination'];

			$sort_method_options = array();
			$sort_method_options = array( "file_name", "file_time", "file_rating", "file_dls", "file_update_time" );

			$sort_method_list = '<select name="sort_method">';
			for( $j = 0; $j < count( $sort_method_options ); $j++ )
			{
				if ( $new['sort_method'] == $sort_method_options[$j] )
				{
					$status = "selected";
				}
				else
				{
					$status = '';
				}
				$sort_method_list .= '<option value="' . $sort_method_options[$j] . '" ' . $status . '>' . $sort_method_options[$j] . '</option>';
			}
			$sort_method_list .= '</select>';

			$sort_order_options = array();
			$sort_order_options = array( "DESC", "ASC" );

			$sort_order_list = '<select name="sort_order">';

			for( $j = 0; $j < count( $sort_order_options ); $j++ )
			{
				if ( $new['sort_order'] == $sort_order_options[$j] )
				{
					$status = "selected";
				}
				else
				{
					$status = '';
				}
				$sort_order_list .= '<option value="' . $sort_order_options[$j] . '" ' . $status . '>' . $sort_order_options[$j] . '</option>';
			}
			$sort_order_list .= '</select>';

			$settings_topnumber = $new['settings_topnumber'];

			$view_all_yes = ( $new['settings_viewall'] ) ? "checked=\"checked\"" : "";
			$view_all_no = ( !$new['settings_viewall'] ) ? "checked=\"checked\"" : "";

			$settings_newdays = $new['settings_newdays'];
			$cat_col = $new['cat_col'];

			$use_simple_navigation_yes = ( $new['use_simple_navigation'] ) ? "checked=\"checked\"" : "";
			$use_simple_navigation_no = ( !$new['use_simple_navigation'] ) ? "checked=\"checked\"" : "";

			//
			// Instructions
			//
			$pretext_show = ( $new['show_pretext'] ) ? "checked=\"checked\"" : "";
			$pretext_hide = ( !$new['show_pretext'] ) ? "checked=\"checked\"" : "";

			$pt_header = $new['pt_header'];
			$pt_body = $new['pt_body'];


			//
			// Comments (default settings)
			//
			$use_comments_yes = ( $new['use_comments'] ) ? "checked=\"checked\"" : "";
			$use_comments_no = ( !$new['use_comments'] ) ? "checked=\"checked\"" : "";

			switch ($portal_config['portal_backend'])
			{
				case 'internal':
					$internal_comments_internal = "checked=\"checked\"";
					$internal_comments_phpbb = "";
					$comments_forum_id = 0;

					$del_topic_yes = "";
					$del_topic_no = "checked=\"checked\"";

					$autogenerate_comments_yes = "";
					$autogenerate_comments_no = "checked=\"checked\"";

					$this->template->assign_vars( array(
						'S_READONLY' => "disabled=\"disabled\"" )
					);
				break;

				default:
					$internal_comments_internal = ( $new['internal_comments'] ) ? "checked=\"checked\"" : "";
					$internal_comments_phpbb = ( !$new['internal_comments'] ) ? "checked=\"checked\"" : "";
					$comments_forum_id = $new['comments_forum_id'];

					$del_topic_yes = ( $new['del_topic'] ) ? "checked=\"checked\"" : "";
					$del_topic_no = ( !$new['del_topic'] ) ? "checked=\"checked\"" : "";

					$autogenerate_comments_yes = ( $new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";
					$autogenerate_comments_no = ( !$new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";
					$this->template->assign_vars( array(
						'S_READONLY' => "" )
					);
				break;
			}

			$allow_comment_wysiwyg_yes = ( $new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";
			$allow_comment_wysiwyg_no = ( !$new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";

			$allow_comment_html_yes = ( $new['allow_comment_html'] ) ? "checked=\"checked\"" : "";
			$allow_comment_html_no = ( !$new['allow_comment_html'] ) ? "checked=\"checked\"" : "";

			$allowed_comment_html_tags = $new['allowed_comment_html_tags'];

			$allow_comment_bbcode_yes = ( $new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";
			$allow_comment_bbcode_no = ( !$new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";

			$allow_comment_smilies_yes = ( $new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";
			$allow_comment_smilies_no = ( !$new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";

			$allow_comment_links_yes = ( $new['allow_comment_links'] ) ? "checked=\"checked\"" : "";
			$allow_comment_links_no = ( !$new['allow_comment_links'] ) ? "checked=\"checked\"" : "";

			$allow_comment_images_yes = ( $new['allow_comment_images'] ) ? "checked=\"checked\"" : "";
			$allow_comment_images_no = ( !$new['allow_comment_images'] ) ? "checked=\"checked\"" : "";

			$no_comment_link_message = $new['no_comment_link_message'];
			$no_comment_image_message = $new['no_comment_image_message'];

			$max_comment_chars = $new['max_comment_chars'];
			$max_comment_subject_chars = $new['max_comment_subject_chars'];

			$format_comment_truncate_links_yes = ( $new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";
			$format_comment_truncate_links_no = ( !$new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";

			$format_comment_image_resize = $new['formatting_comment_image_resize'];

			$format_comment_wordwrap_yes = ( $new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";
			$format_comment_wordwrap_no = ( !$new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";

			$comments_pag = $new['comments_pagination'];

			//
			// Ratings (default settings)
			//
			$use_ratings_yes = ( $new['use_ratings'] ) ? "checked=\"checked\"" : "";
			$use_ratings_no = ( !$new['use_ratings'] ) ? "checked=\"checked\"" : "";

			$votes_check_ip_yes = ( $new['votes_check_ip'] ) ? "checked=\"checked\"" : "";
			$votes_check_ip_no = ( !$new['votes_check_ip'] ) ? "checked=\"checked\"" : "";

			$votes_check_userid_yes = ( $new['votes_check_userid'] ) ? "checked=\"checked\"" : "";
			$votes_check_userid_no = ( !$new['votes_check_userid'] ) ? "checked=\"checked\"" : "";

			//
			// Notifications
			//
			$notify_none = ( $new['notify'] == 0 ) ? "checked=\"checked\"" : "";
			$notify_pm = ( $new['notify'] == 1 ) ? "checked=\"checked\"" : "";
			$notify_email = ( $new['notify'] == 2 ) ? "checked=\"checked\"" : "";

			$notify_group_list = $this->functions->get_groups($new['notify_group'], 'notify_group');			
			
			
			// If they've specified an extension, let's load the metadata manager and validate it.
			if ($this->ext_name)
			{
				$md_manager = $this->ext_manager->create_extension_metadata_manager($this->ext_name);

				try
				{
					$md_manager->get_metadata('all');
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}			
			
			// Output it to the template
			$metadata = $md_manager->get_metadata('all');
			
			$this->template->assign_vars(array(
				'META_NAME'			=> $metadata['name'],
				'META_TYPE'			=> $metadata['type'],
				'META_DESCRIPTION'	=> (isset($metadata['description'])) ? $metadata['description'] : '',
				'META_HOMEPAGE'		=> (isset($metadata['homepage'])) ? $metadata['homepage'] : '',
				'META_VERSION'		=> $metadata['version'],
				'META_TIME'			=> (isset($metadata['time'])) ? $metadata['time'] : '',
				'META_LICENSE'		=> $metadata['license'],

				'META_REQUIRE_PHP'		=> (isset($metadata['require']['php'])) ? $metadata['require']['php'] : '',
				'META_REQUIRE_PHP_FAIL'	=> (isset($metadata['require']['php'])) ? false : true,

				'META_REQUIRE_PHPBB'		=> (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? $metadata['extra']['soft-require']['phpbb/phpbb'] : '',
				'META_REQUIRE_PHPBB_FAIL'	=> (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? false : true,

				'META_DISPLAY_NAME'	=> (isset($metadata['extra']['display-name'])) ? $metadata['extra']['display-name'] : '',
			));

			foreach ($metadata['authors'] as $author)
			{
				$this->template->assign_block_vars('meta_authors', array(
					'AUTHOR_NAME'		=> $author['name'],
					'AUTHOR_EMAIL'		=> (isset($author['email'])) ? $author['email'] : '',
					'AUTHOR_HOMEPAGE'	=> (isset($author['homepage'])) ? $author['homepage'] : '',
					'AUTHOR_ROLE'		=> (isset($author['role'])) ? $author['role'] : '',
				));
			}			

			if (isset($meta['extra']['version-check']))
			{
				try
				{
					$updates_available = array(); // $this->ext_manager->version_check($md_manager, false, false, 'unstable');

					$this->template->assign_vars(array(
						'S_UP_TO_DATE' => empty($updates_available),
						'UP_TO_DATE_MSG' => $this->user->lang(empty($updates_available) ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
					));

					$this->template->assign_block_vars('updates_available', $updates_available);
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));

					$this->template->assign_vars(array(
						'S_VERSIONCHECK_FAIL' => true,
						'VERSIONCHECK_FAIL_REASON' => ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $message : '',
					));
				}
				$this->template->assign_var('S_VERSIONCHECK', true);
			}
			else
			{
				$this->template->assign_var('S_VERSIONCHECK', false);
			}

			$this->template->assign_vars(array(
				'U_BACK'				=> $this->u_action . '&amp;action=list',
				'U_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name')),
			));			
			
			$this->template->assign_block_vars('ext_update', array(
				'FILES_CURRENT_VERSION' => $pa_config['pa_module_version'],
				'FILES_LATEST_VERSION' => $metadata['version'],
				'FILES_AUTHOR' => $author['name'],
				'FILES_S_UP_TO_DATE' => $s_up_to_date,
				'FILES_DOWNLOAD' => $download, 
				'FILES_TITLE'  => $title,
				'FILES_ANNOUNCEMENT' => $metadata['homepage']
			));			
			
			$this->template->assign_vars(array(
				'U_BACK'					=> $this->u_action,
				'U_ACTION'					=> $form_action,
				
				'S_SETTINGS_ACTION'			=> append_sid("$this->u_action&amp;action=settings"),

				'L_CONFIGURATION_TITLE'		=> $this->user->lang['Panel_config_title'],
				'L_CONFIGURATION_EXPLAIN' 	=> $this->user->lang['Panel_config_explain'],

				'L_RESET' 		=> $this->user->lang['Reset'],
				'L_SUBMIT' 		=> $this->user->lang['Submit'],
				'L_YES' 		=> $this->user->lang['Yes'],
				'L_NO' 			=> $this->user->lang['No'],
				'L_NONE' 		=> $this->user->lang['Acc_None'],

				//
				// General
				//
				'L_GENERAL_TITLE' 		=> $this->user->lang['General_title'],

				'L_MODULE_NAME' 		=> $this->user->lang['Module_name'],
				'L_MODULE_NAME_EXPLAIN' => $this->user->lang['Module_name_explain'],
				'MODULE_NAME' 			=> $module_name,

				'L_ENABLE_MODULE' 		=> $this->user->lang['Enable_module'],
				'L_ENABLE_MODULE_EXPLAIN' => $this->user->lang['Enable_module_explain'],
				'S_ENABLE_MODULE_YES' 	=> $enable_module_yes,
				'S_ENABLE_MODULE_NO' 	=> $enable_module_no,

				'L_WYSIWYG_PATH' 		=> $this->user->lang['Wysiwyg_path'],
				'L_WYSIWYG_PATH_EXPLAIN' => $this->user->lang['Wysiwyg_path_explain'],
				'WYSIWYG_PATH' 			=> $wysiwyg_path,

				'L_UPLOAD_DIR' 			=> $this->user->lang['Upload_directory'],
				'L_UPLOAD_DIR_EXPLAIN' => $this->user->lang['Upload_directory_explain'],
				'UPLOAD_DIR' 			=> $upload_dir,

				'L_SCREENSHOT_DIR' 			=> $this->user->lang['Screenshots_directory'],
				'L_SCREENSHOT_DIR_EXPLAIN' => $this->user->lang['Screenshots_directory_explain'],
				'SCREENSHOT_DIR' 			=> $screenshots_dir,
				
				//
				// A copy of Handyman` s MOD version check, to view it on pafiledb overview
				//'L_VERSION_CHECK' 		=> $this->user->lang['VERSION_CHECK'],
				
				//
				// FILE
				//
				'L_FILE_TITLE' 		=> $this->user->lang['File_title'],

				'L_HOTLINK' 		=> $this->user->lang['Hotlink_prevent'],
				'L_HOTLINK_INFO' 	=> $this->user->lang['Hotlinl_prevent_info'],
				'S_HOTLINK_YES' 	=> $hotlink_prevent_yes,
				'S_HOTLINK_NO' 		=> $hotlink_prevent_no,

				'L_HOTLINK_ALLOWED' 	=> $this->user->lang['Hotlink_allowed'],
				'L_HOTLINK_ALLOWED_INFO' => $this->user->lang['Hotlink_allowed_info'],
				'HOTLINK_ALLOWED' 		=> $hotlink_allowed,

				'L_PHP_TPL' 			=> $this->user->lang['Php_template'],
				'L_PHP_TPL_INFO' 		=> $this->user->lang['Php_template_info'],
				'S_PHP_TPL_YES' 		=> $php_template_yes,
				'S_PHP_TPL_NO' 			=> $php_template_no,

				'L_MAX_FILE_SIZE' 		=> $this->user->lang['Max_file_size'],
				'L_MAX_FILE_SIZE_INFO' 	=> $this->user->lang['Max_file_size_explain'],
				'MAX_FILE_SIZE' 		=> $max_file_size,
				'S_FILESIZE' 			=> $this->pa_size_select('max_file_size', $size),

				'L_FORBIDDEN_EXTENSIONS' 		=> $this->user->lang['Forbidden_extensions'],
				'L_FORBIDDEN_EXTENSIONS_EXPLAIN' => $this->user->lang['Forbidden_extensions_explain'],
				'FORBIDDEN_EXTENSIONS' 			=> $forbidden_extensions,


				//
				// Appearance
				//
				'L_APPEARANCE_TITLE' => $this->user->lang['Appearance_title'],

				'L_PAGINATION' => $this->user->lang['File_pagination'],
				'L_PAGINATION_EXPLAIN' => $this->user->lang['File_pagination_explain'],
				'PAGINATION' => $pagination,

				'L_SORT_METHOD' => $this->user->lang['Sort_method'],
				'L_SORT_METHOD_EXPLAIN' => $this->user->lang['Sort_method_explain'],
				'SORT_METHOD' => $sort_method_list,

				'L_SORT_ORDER' => $this->user->lang['Sort_order'],
				'L_SORT_ORDER_EXPLAIN' => $this->user->lang['Sort_order_explain'],
				'SORT_ORDER' => $sort_order_list,

				'L_TOPNUM' => $this->user->lang['Topnum'],
				'L_TOPNUMINFO' => $this->user->lang['Topnuminfo'],
				'SETTINGS_TOPNUMBER' => $settings_topnumber,

				'CAT_COL' => $cat_col,
				'L_CAT_COL' => $this->user->lang['Cat_col'],

				'S_USE_SIMPLE_NAVIGATION_YES' => $use_simple_navigation_yes,
				'S_USE_SIMPLE_NAVIGATION_NO' => $use_simple_navigation_no,
				'L_USE_SIMPLE_NAVIGATION' => $this->user->lang['Use_simple_navigation'],
				'L_USE_SIMPLE_NAVIGATION_EXPLAIN' => $this->user->lang['Use_simple_navigation_explain'],

				'L_NFDAYS' => $this->user->lang['Nfdays'],
				'L_NFDAYSINFO' => $this->user->lang['Nfdaysinfo'],
				'SETTINGS_NEWDAYS' => $settings_newdays,

				'L_SHOW_VIEWALL' => $this->user->lang['Showva'],
				'L_VIEWALL_INFO' => $this->user->lang['Showvainfo'],
				'S_VIEW_ALL_YES' => $view_all_yes,
				'S_VIEW_ALL_NO' => $view_all_no,

				//
				// Comments
				//
				'L_COMMENTS_TITLE' => $this->user->lang['Comments_title'],
				'L_COMMENTS_TITLE_EXPLAIN' => $this->user->lang['Comments_title_explain'],

				'L_USE_COMMENTS' => $this->user->lang['Use_comments'],
				'L_USE_COMMENTS_EXPLAIN' => $this->user->lang['Use_comments_explain'],
				'S_USE_COMMENTS_YES' => $use_comments_yes,
				'S_USE_COMMENTS_NO' => $use_comments_no,

				'L_INTERNAL_COMMENTS' => $this->user->lang['Internal_comments'],
				'L_INTERNAL_COMMENTS_EXPLAIN' => $this->user->lang['Internal_comments_explain'],
				'S_INTERNAL_COMMENTS_INTERNAL' => $internal_comments_internal,
				'S_INTERNAL_COMMENTS_PHPBB' => $internal_comments_phpbb,
				'L_INTERNAL_COMMENTS_INTERNAL' => $this->user->lang['Internal_comments_internal'],
				'L_INTERNAL_COMMENTS_PHPBB' => $this->user->lang['Internal_comments_phpBB'],

				'L_FORUM_ID' => $this->user->lang['Forum_id'],
				'L_FORUM_ID_EXPLAIN' => $this->user->lang['Forum_id_explain'],
				//'FORUM_LIST' => $this->get_forums( $comments_forum_id, false, 'comments_forum_id' ),
				'FORUM_LIST' => $portal_config['portal_backend'] != 'internal' ? $this->get_forums( $comments_forum_id, false, 'comments_forum_id' ) : 'not available',

				'ANNOUNCE_ENABLE'		=> $pafiledb_config['use_comments'],
				'ANNOUNCE_FORUM'		=> $pafiledb_config['comments_forum_id'],
				'ANNOUNCE_LOCK'			=> $pafiledb_config['comments_lock_enable'],				
				
				'L_AUTOGENERATE_COMMENTS' => $this->user->lang['Autogenerate_comments'],
				'L_AUTOGENERATE_COMMENTS_EXPLAIN' => $this->user->lang['Autogenerate_comments_explain'],
				'S_AUTOGENERATE_COMMENTS_YES' => $autogenerate_comments_yes,
				'S_AUTOGENERATE_COMMENTS_NO' => $autogenerate_comments_no,

				'L_ALLOW_COMMENT_WYSIWYG' => $this->user->lang['Allow_Wysiwyg'],
				'L_ALLOW_COMMENT_WYSIWYG_EXPLAIN' => $this->user->lang['Allow_Wysiwyg_explain'],
				'S_ALLOW_COMMENT_WYSIWYG_YES' => $allow_comment_wysiwyg_yes,
				'S_ALLOW_COMMENT_WYSIWYG_NO' => $allow_comment_wysiwyg_no,

				'L_ALLOW_COMMENT_HTML' => $this->user->lang['Allow_HTML'],
				'L_ALLOW_COMMENT_HTML_EXPLAIN' => $this->user->lang['Allow_html_explain'],
				'S_ALLOW_COMMENT_HTML_YES' => $allow_comment_html_yes,
				'S_ALLOW_COMMENT_HTML_NO' => $allow_comment_html_no,

				'L_ALLOW_COMMENT_BBCODE' => $this->user->lang['Allow_BBCode'],
				'L_ALLOW_COMMENT_BBCODE_EXPLAIN' => $this->user->lang['Allow_bbcode_explain'],
				'S_ALLOW_COMMENT_BBCODE_YES' => $allow_comment_bbcode_yes,
				'S_ALLOW_COMMENT_BBCODE_NO' => $allow_comment_bbcode_no,

				'L_ALLOW_COMMENT_SMILIES' => $this->user->lang['Allow_smilies'],
				'L_ALLOW_COMMENT_SMILIES_EXPLAIN' => $this->user->lang['Allow_smilies_explain'],
				'S_ALLOW_COMMENT_SMILIES_YES' => $allow_comment_smilies_yes,
				'S_ALLOW_COMMENT_SMILIES_NO' => $allow_comment_smilies_no,

				'L_ALLOWED_COMMENT_HTML_TAGS' => $this->user->lang['Allowed_tags'],
				'L_ALLOWED_COMMENT_HTML_TAGS_EXPLAIN' => $this->user->lang['Allowed_tags_explain'],
				'ALLOWED_COMMENT_HTML_TAGS' => $allowed_comment_html_tags,

				'L_ALLOW_COMMENT_IMAGES' => $this->user->lang['Allow_images'],
				'L_ALLOW_COMMENT_IMAGES_EXPLAIN' => $this->user->lang['Allow_images_explain'],
				'S_ALLOW_COMMENT_IMAGES_YES' => $allow_comment_images_yes,
				'S_ALLOW_COMMENT_IMAGES_NO' => $allow_comment_images_no,

				'L_ALLOW_COMMENT_LINKS' => $this->user->lang['Allow_links'],
				'L_ALLOW_COMMENT_LINKS_EXPLAIN' => $this->user->lang['Allow_links_explain'],
				'S_ALLOW_COMMENT_LINKS_YES' => $allow_comment_links_yes,
				'S_ALLOW_COMMENT_LINKS_NO' => $allow_comment_links_no,

				'L_COMMENT_LINKS_MESSAGE' => $this->user->lang['Allow_links_message'],
				'L_COMMENT_LINKS_MESSAGE_EXPLAIN' => $this->user->lang['Allow_links_message_explain'],
				'COMMENT_MESSAGE_LINK' => $no_comment_link_message,

				'L_COMMENT_IMAGES_MESSAGE' => $this->user->lang['Allow_images_message'],
				'L_COMMENT_IMAGES_MESSAGE_EXPLAIN' => $this->user->lang['Allow_images_message_explain'],
				'COMMENT_MESSAGE_IMAGE' => $no_comment_image_message,

				'L_COMMENT_MAX_SUBJECT_CHAR' => $this->user->lang['Max_subject_char'],
				'L_COMMENT_MAX_SUBJECT_CHAR_EXPLAIN' => $this->user->lang['Max_subject_char_explain'],
				'COMMENT_MAX_SUBJECT_CHAR' => $max_comment_subject_chars,

				'L_COMMENT_MAX_CHAR' => $this->user->lang['Max_char'],
				'L_COMMENT_MAX_CHAR_EXPLAIN' => $this->user->lang['Max_char_explain'],
				'COMMENT_MAX_CHAR' => $max_comment_chars,

				'L_COMMENT_FORMAT_WORDWRAP' => $this->user->lang['Format_wordwrap'],
				'L_COMMENT_FORMAT_WORDWRAP_EXPLAIN' => $this->user->lang['Format_wordwrap_explain'],
				'S_COMMENT_FORMAT_WORDWRAP_YES' => $format_comment_wordwrap_yes,
				'S_COMMENT_FORMAT_WORDWRAP_NO' => $format_comment_wordwrap_no,

				'L_COMMENT_FORMAT_IMAGE_RESIZE' => $this->user->lang['Format_image_resize'],
				'L_COMMENT_FORMAT_IMAGE_RESIZE_EXPLAIN' => $this->user->lang['Format_image_resize_explain'],
				'COMMENT_FORMAT_IMAGE_RESIZE' => $format_comment_image_resize,

				'L_COMMENT_FORMAT_TRUNCATE_LINKS' => $this->user->lang['Format_truncate_links'],
				'L_COMMENT_FORMAT_TRUNCATE_LINKS_EXPLAIN' => $this->user->lang['Format_truncate_links_explain'],
				'S_COMMENT_FORMAT_TRUNCATE_LINKS_YES' => $format_comment_truncate_links_yes,
				'S_COMMENT_FORMAT_TRUNCATE_LINKS_NO' => $format_comment_truncate_links_no,

				'L_COMMENTS_PAG' => $this->user->lang['Comments_pag'],
				'L_COMMENTS_PAG_EXPLAIN' => $this->user->lang['Comments_pag_explain'],
				'COMMENTS_PAG' => $comments_pag,

				'L_DEL_TOPIC' => $this->user->lang['Del_topic'],
				'L_DEL_TOPIC_EXPLAIN' => $this->user->lang['Del_topic_explain'],
				'S_DEL_TOPIC_YES' => $del_topic_yes,
				'S_DEL_TOPIC_NO' => $del_topic_no,

				//
				// Ratings
				//
				'L_RATINGS_TITLE' => $this->user->lang['Ratings_title'],
				'L_RATINGS_TITLE_EXPLAIN' => $this->user->lang['Ratings_title_explain'],

				'L_USE_RATINGS' => $this->user->lang['Use_ratings'],
				'L_USE_RATINGS_EXPLAIN' => $this->user->lang['Use_ratings_explain'],
				'S_USE_RATINGS_YES' => $use_ratings_yes,
				'S_USE_RATINGS_NO' => $use_ratings_no,

				'L_VOTES_CHECK_IP' => $this->user->lang['Votes_check_ip'],
				'L_VOTES_CHECK_IP_EXPLAIN' => $this->user->lang['Votes_check_ip_explain'],
				'S_VOTES_CHECK_IP_YES' => $votes_check_ip_yes,
				'S_VOTES_CHECK_IP_NO' => $votes_check_ip_no,

				'L_VOTES_CHECK_USERID' => $this->user->lang['Votes_check_userid'],
				'L_VOTES_CHECK_USERID_EXPLAIN' => $this->user->lang['Votes_check_userid_explain'],
				'S_VOTES_CHECK_USERID_YES' => $votes_check_userid_yes,
				'S_VOTES_CHECK_USERID_NO' => $votes_check_userid_no,

				//
				// Instructions
				//
				'L_INSTRUCTIONS_TITLE' => $this->user->lang['Instructions_title'],

				'L_SHOW' => $this->user->lang['Show'],
				'L_HIDE' => $this->user->lang['Hide'],
				'L_PRE_TEXT_NAME' => $this->user->lang['Pre_text_name'],
				'L_PRE_TEXT_HEADER' => $this->user->lang['Pre_text_header'],
				'L_PRE_TEXT_BODY' => $this->user->lang['Pre_text_body'],
				'L_PRE_TEXT_EXPLAIN' => $this->user->lang['Pre_text_explain'],
				'S_SHOW_PRETEXT' => $pretext_show,
				'S_HIDE_PRETEXT' => $pretext_hide,
				'L_PT_HEADER' => $pt_header,
				'L_PT_BODY' => $pt_body,

				//
				// Notifications
				//
				'L_NOTIFICATIONS_TITLE' => $this->user->lang['Notifications_title'],

				'L_NOTIFY' => $this->user->lang['Notify'],
				'L_NOTIFY_EXPLAIN' => $this->user->lang['Notify_explain'],
				'L_EMAIL' => $this->user->lang['Email'],
				'L_PM' => $this->user->lang['PM'],
				'S_NOTIFY_NONE' => $notify_none,
				'S_NOTIFY_EMAIL' => $notify_email,
				'S_NOTIFY_PM' => $notify_pm,

				'L_NOTIFY_GROUP' => $this->user->lang['Notify_group'],
				'L_NOTIFY_GROUP_EXPLAIN' => $this->user->lang['Notify_group_explain'],
				'NOTIFY_GROUP' => $notify_group_list,

				//
				// Permissions
				//
				'L_PERMISSION_SETTINGS' => $this->user->lang['Permission_settings'],

				'L_ATUH_SEARCH' => $this->user->lang['Auth_search'],
				'L_ATUH_SEARCH_INFO' => $this->user->lang['Auth_search_explain'],
				'S_ATUH_SEARCH' => $auth_select['auth_search'],

				'L_ATUH_STATS' => $this->user->lang['Auth_stats'],
				'L_ATUH_STATS_INFO' => $this->user->lang['Auth_stats_explain'],
				'S_ATUH_STATS' => $auth_select['auth_stats'],

				'L_ATUH_TOPLIST' => $this->user->lang['Auth_toplist'],
				'S_ATUH_TOPLIST' => $auth_select['auth_toplist'],
				'L_ATUH_TOPLIST_INFO' => $this->user->lang['Auth_toplist_explain'],

				'L_ATUH_VIEWALL' => $this->user->lang['Auth_viewall'],
				'L_ATUH_VIEWALL_INFO' => $this->user->lang['Auth_viewall_explain'],
				'S_ATUH_VIEWALL' => $auth_select['auth_viewall'],
				
				//
				// Pagination
				//				
				'PAGINATION_ACP'		=> $pa_config['pagination_acp'],
				'PAGINATION_USER'		=> $pa_config['pagination_user'],

				'PAGINATION_DOWNLOADS'	=> $pa_config['pagination_downloads'],				
			));						
		}
	}

	public function new_download()
	{
		// Read out config values
		$pafiledb_config = $this->functions->config_values();

		$form_action 			= $this->u_action. '&amp;action=add_new';
		$this->user->lang_mode 	= $this->user->lang['ACP_NEW_DOWNLOAD'];
		$action 				= $this->request->variable('action', '');
		$action 				= ($this->request->is_set('submit') && !$this->request->is_set('file_id')) ? 'add' : $action;
		$cat_id					= $this->request->variable('cat_id', 0);
		$title					= $this->request->variable('title', '', true);
		$filename				= $this->request->variable('filename', '', true);
		$desc					= $this->request->variable('desc', '', true);
		$file_version			= $this->request->variable('file_version', '', true);
		$costs_dl				= $this->request->variable('cost_per_dl', 0.00);
		$ftp_upload 			= $this->request->variable('ftp_upload', '', true);

		// Check if categories exists
		$sql = 'SELECT COUNT(cat_id) AS total_cats
			FROM ' . $this->pa_cat_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_cats = $row['total_cats'];
		$this->db->sql_freeresult($result);

		if ($total_cats <= 0)
		{
			trigger_error($this->user->lang['ACP_NO_CAT'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY LOWER(cat_name)';
		$result = $this->db->sql_query($sql);
		$cats = array();
		while ($row2 = $this->db->sql_fetchrow($result))
		{
			$cats[$row2['cat_id']] = array(
				'cat_title'	=> $row2['cat_name'],
				'cat_id'	=> $row2['cat_id'],
			);
		}
		$this->db->sql_freeresult($result);

		$cat_options = '';
		foreach ($cats as $key => $value)
		{
			if ($key == $row2['file_catid'])
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '" selected="selected">' . $value['cat_title'] . '</option>';
			}
			else
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '">' . $value['cat_title'] . '</option>';
			}
		}

		$max_file_size = @ini_get('upload_max_filesize');
		$unit = 'MB';

		if (!empty($max_file_size))
		{
			$unit = strtolower(substr($max_file_size, -1, 1));
			$max_file_size = (int) $max_file_size;

			$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
		}

		$this->template->assign_vars(array(
			'ID'				=> $file_id,
			'TITLE'				=> $title,
			'DESC'				=> $desc,
			'FILENAME'			=> $filename,
			'DL_VERSION'		=> $file_version,
			'FTP_UPLOAD'		=> $ftp_upload,
			'PARENT_OPTIONS'	=> $cat_options,
			'ALLOWED_SIZE'		=> sprintf($this->user->lang['ACP_NEW_DOWNLOAD_SIZE'], $max_file_size, $unit),
			'U_BACK'			=> $this->u_action,
			'U_ACTION'			=> $form_action,
			'L_MODE_TITLE'		=> $this->user->lang_mode,
		));
	}

	public function copy_new()
	{
		// Read out config values
		$pafiledb_config = $this->functions->config_values();

		$form_action = $this->u_action. '&amp;action=add_new';
		$this->user->lang_mode = $this->user->lang['ACP_NEW_DOWNLOAD'];

		$action = $this->request->variable('action', '');
		$action = ($this->request->is_set('submit') && !$this->request->is_set('file_id')) ? 'add' : $action;

		$this->user->add_lang('posting');

		$file_id	= $this->request->variable('file_id', 0);

		$sql = 'SELECT *
			FROM ' . $this->pa_files_table . '
			WHERE file_id = ' . (int) $file_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		decode_message($row['file_desc'], $row['bbcode_uid']);
		$copy_title = $row['file_name'];
		$copy_version = $row['file_version'];
		$copy_desc = $row['file_desc'];
		$copy_costs_dl = $row['cost_per_dl'];
		$this->db->sql_freeresult($result);

		$cat_id			= $this->request->variable('cat_id', 0);
		$title			= $this->request->variable('title', '', true);
		$filename		= $this->request->variable('filename', '', true);
		$desc			= $this->request->variable('desc', '', true);
		$file_version	= $this->request->variable('file_version', '', true);
		$costs_dl		= $this->request->variable('cost_per_dl', 0.00);
		$ftp_upload 	= $this->request->variable('ftp_upload', '', true);

		// Check if categories exists
		$sql = 'SELECT COUNT(cat_id) AS total_cats
			FROM ' . $this->pa_cat_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_cats = $row['total_cats'];
		$this->db->sql_freeresult($result);

		if ($total_cats <= 0)
		{
			trigger_error($this->user->lang['ACP_NO_CAT'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY LOWER(cat_name)';
		$result = $this->db->sql_query($sql);
		$cats = array();
		while ($row2 = $this->db->sql_fetchrow($result))
		{
			$cats[$row2['cat_id']] = array(
				'cat_title'	=> $row2['cat_name'],
				'cat_id'	=> $row2['cat_id'],
			);
		}
		$this->db->sql_freeresult($result);

		$cat_options = '';
		foreach ($cats as $key => $value)
		{
			if ($key == $row2['file_catid'])
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '" selected="selected">' . $value['cat_title'] . '</option>';
			}
			else
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '">' . $value['cat_title'] . '</option>';
			}
		}

		$max_file_size = @ini_get('upload_max_filesize');
		$unit = 'MB';

		if (!empty($max_file_size))
		{
			$unit = strtolower(substr($max_file_size, -1, 1));
			$max_file_size = (int) $max_file_size;

			$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
		}

		$this->template->assign_vars(array(
			'ID'				=> $file_id,
			'TITLE'				=> $copy_title,
			'DESC'				=> $copy_desc,
			'FILENAME'			=> $filename,
			'FTP_UPLOAD'		=> $ftp_upload,
			'DL_VERSION'		=> $copy_version,
			'PARENT_OPTIONS'	=> $cat_options,
			'ALLOWED_SIZE'		=> sprintf($this->user->lang['ACP_NEW_DOWNLOAD_SIZE'], $max_file_size, $unit),
			'U_BACK'			=> $this->u_action,
			'U_ACTION'			=> $form_action,
			'L_MODE_TITLE'		=> $this->user->lang_mode,
		));
	}

	public function edit()
	{
		// Edit an existing download
		$form_action = $this->u_action. '&amp;action=update';
		$this->user->lang_mode = $this->user->lang['ACP_EDIT_DOWNLOADS'];

		$action = $this->request->variable('action', '');
		$action = ($this->request->is_set('submit') && !$this->request->is_set('file_id')) ? 'add' : $action;

		$file_id = $this->request->variable('file_id', '');

		$sql = 'SELECT d.*, c.*
			FROM ' . $this->pa_files_table . ' d
				LEFT JOIN ' . $this->pa_cat_table . ' c
				ON d.file_catid = c.cat_id
			WHERE file_id = ' . (int) $file_id;
		$result = $this->db->sql_query_limit($sql,1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		decode_message($row['file_desc'], $row['bbcode_uid']);
		$file_id = $row['file_id'];
		$file_version = $row['file_version'];

		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY LOWER(cat_name)';
		$result = $this->db->sql_query($sql);
		$cats = array();
		while ($row2 = $this->db->sql_fetchrow($result))
		{
			$cats[$row2['cat_id']] = array(
				'cat_title'	=> $row2['cat_name'],
				'cat_id'	=> $row2['cat_id'],
			);
		}
		$this->db->sql_freeresult($result);

		$cat_options = '';
		foreach ($cats as $key => $value)
		{
			if ($key == $row['file_catid'])
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '" selected="selected">' . $value['cat_title'] . '</option>';
			}
			else
			{
				$cat_options .= '<option value="' . $value['cat_id'] . '">' . $value['cat_title'] . '</option>';
			}
		}

		$max_file_size = @ini_get('upload_max_filesize');
		$unit = 'MB';

		if (!empty($max_file_size))
		{
			$unit = strtolower(substr($max_file_size, -1, 1));
			$max_file_size = (int) $max_file_size;

			$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
		}

		$this->template->assign_vars(array(
			'ID'				=> $file_id,
			'TITLE'				=> $row['file_name'],
			'DESC'				=> $row['file_desc'],
			'FILENAME'			=> $row['real_name'],
			'CATNAME'			=> $row['cat_name'],
			'DL_VERSION'		=> $file_version,
			'PARENT_OPTIONS'	=> $cat_options,
			'ALLOWED_SIZE'		=> sprintf($this->user->lang['ACP_NEW_DOWNLOAD_SIZE'], $max_file_size, $unit),
			'U_ACTION'			=> $form_action,
			'L_MODE_TITLE'		=> $this->user->lang_mode,
		));
	}

	public function add_new()
	{
		$filecheck = $multiplier = '';

		$this->user->add_lang('posting');

		// Read out config values
		$pafiledb_config = $this->functions->config_values();

		$cat_id				= $this->request->variable('cat_id', 0);
		$title				= $this->request->variable('title', '', true);
		$filename			= $this->request->variable('filename', '', true);
		$desc				= $this->request->variable('desc', '', true);
		$file_version		= $this->request->variable('file_version', '', true);
		$costs_dl			= $this->request->variable('cost_per_dl', 0.00, true);
		$cat_option 		= $this->request->variable('parent', '', true);
		$file_time 			= time();
		$file_update_time 	= time();
		$uid = $bitfield = $options = '';
		$allow_bbcode 		= $allow_urls = $allow_smilies = true;
		$ftp_upload			= $this->request->variable('ftp_upload', '', true);

		if (!$ftp_upload)
		{
			// Check max. allowed filesize from php.ini
			$max_file_size = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_file_size))
			{
				$unit = strtolower(substr($max_file_size, -1, 1));
				$max_file_size = (int) $max_file_size;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}
		}

		// Add allowed extensions
		$allowed_extensions = $this->functions->allowed_extensions();

		// Check if categories exists
		$sql = 'SELECT COUNT(cat_id) AS total_cats
			FROM ' . $this->pa_cat_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_cats = $row['total_cats'];
		$this->db->sql_freeresult($result);

		if ($total_cats <= 0)
		{
			trigger_error($this->user->lang['ACP_NO_CAT_UPLOAD'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($this->files_factory !== null)
		{
			$fileupload = $this->files_factory->get('upload')
				->set_allowed_extensions($allowed_extensions);
		}
		else
		{
			generate_text_for_storage($desc, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

			if (!class_exists('\fileupload'))
			{
				include($this->root_path . 'includes/functions_upload.' . $this->php_ext);
			}
			$fileupload = new \fileupload();
			$fileupload->fileupload('', $allowed_extensions);
		}

		$target_folder = $this->request->variable('parent', 0);
		$upload_name = $this->request->variable('filename', '');

		// Check if FTP upload and normal upload is entered
		if ($ftp_upload && $upload_name)
		{
			trigger_error($this->user->lang['ACP_FTP_UPLOAD'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT cat_sub_dir
			FROM ' . $this->pa_cat_table . '
			WHERE cat_id = ' . (int) $target_folder;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$target = $row['cat_sub_dir'];
		$this->db->sql_freeresult($result);

		$upload_dir = $this->module_root_path . 'uploads/' . $target;

		if (!$ftp_upload)
		{
			$upload_file = (isset($this->files_factory)) ? $fileupload->handle_upload('files.types.form', 'filename') : $fileupload->form_upload('filename');

			if (!$upload_file->get('uploadname'))
			{
				trigger_error($this->user->lang['ACP_NO_FILENAME'] . adm_back_link($this->u_action), E_USER_WARNING);//continue;
			}

			if (file_exists($upload_dir . '/' . $upload_file->get('uploadname')))
			{
				trigger_error($this->user->lang['ACP_UPLOAD_FILE_EXISTS'] . adm_back_link($this->u_action), E_USER_WARNING);//continue;
			}

			$upload_file->move_file($upload_dir, false, false, false);
			@chmod($this->ext_path_web . 'pafiledb/' . $upload_file->get('uploadname'), 0644);

			if (sizeof($upload_file->error) && $upload_file->get('uploadname'))
			{
				$upload_file->remove();
				trigger_error(implode('<br />', $upload_file->error));
			}
			
			// End the upload
			$file_size = @filesize($upload_dir . '/' . $upload_file->get('uploadname'));
			$sql_ary = array(
				'file_name'			=> $title,
				'file_desc'	 		=> $desc,
				'real_name'			=> $upload_file->get('uploadname'),
				'file_version'		=> $file_version,
				'file_catid'		=> $cat_option,
				'file_time'			=> $file_time,
				'cost_per_dl'		=> $costs_dl,
				'file_update_time'	=> $file_update_time,
				'bbcode_uid'		=> $uid,
				'bbcode_bitfield'	=> $bitfield,
				'bbcode_options'	=> $options,
				'file_size'			=> $file_size,
				'user_id'			=> $this->user->data['user_id'],
			);

			// Check, if filesize is greater than PHP ini allows
			if ($unit == 'MB')
			{
				$multiplier = 1048576;
			}
			else if ($unit == 'KB')
			{
				$multiplier = 1024;
			}

			if ($file_size	> ($max_file_size * $multiplier))
			{
				@unlink($upload_dir . '/' . $upload_file->get('uploadname'));
				trigger_error($this->user->lang['ACP_FILE_TOO_BIG'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		else
		{
			// check, if FTP upload file exists
			if (!file_exists($upload_dir . '/' . $ftp_upload))
			{
				trigger_error($this->user->lang['ACP_UPLOAD_FILE_NOT_EXISTS'] . adm_back_link($this->u_action), E_USER_WARNING);//continue;
			}

			$file_size = @file_size($upload_dir . '/' . $ftp_upload);
			$sql_ary = array(
				'file_name'			=> $title,
				'file_desc'	 		=> $desc,
				'real_name'			=> $ftp_upload,
				'file_version'		=> $file_version,
				'file_catid'		=> $cat_option,
				'file_time'			=> $file_time,
				'cost_per_dl'		=> $costs_dl,
				'file_update_time'	=> $file_update_time,
				'bbcode_uid'		=> $uid,
				'bbcode_bitfield'	=> $bitfield,
				'bbcode_options'	=> $options,
				'file_size'			=> $file_size,
				'user_id'			=> $this->user->data['user_id'],
			);
		}

		// Announce download, if enabled
		if ($pafiledb_config['use_comments'] == 1)
		{
			$sql = 'SELECT *
				FROM ' . $this->pa_cat_table . '
				WHERE cat_id = ' . (int) $cat_option;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$cat_name = $row['cat_name'];
			$this->db->sql_freeresult($result);

			if (empty($file_version))
			{
				$dl_title = $title;
			}
			else
			{
				$dl_title = $title . ' v' . $file_version;
			}

			$download_link = '[url=' . generate_board_url() . '/category?cat_id=' . $cat_option . ']' . $this->user->lang['ACP_CLICK'] . '[/url]';
			$download_subject = sprintf($this->user->lang['ACP_ANNOUNCE_TITLE'], $dl_title);

			if ($this->files_factory !== null)
			{
				$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_MSG'], $title, $desc, $cat_name, $download_link);
			}
			else
			{
				$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_MSG'], $title, generate_text_for_display($desc, $uid, $bitfield, $options), $cat_name, $download_link);

			}
			$this->functions->create_announcement($download_subject, $download_msg, $pafiledb_config['comments_forum_id']);
		}

		$this->db->sql_query('INSERT INTO ' . $this->pa_files_table .' ' . $this->db->sql_build_array('INSERT', $sql_ary));

		// Log message
		$this->log_message('LOG_DOWNLOAD_ADD', $title, 'ACP_NEW_ADDED');

	}

	public function update()
	{
		// Change an existing download
		$filecheck = $filecheck_current = $new_filename = '';

		// Read out config values
		$pafiledb_config = $this->functions->config_values();

		$this->user->add_lang('posting');

		$file_id = $this->request->variable('file_id', '');

		$sql = 'SELECT d.file_catid, d.real_name, d.file_size, c.cat_sub_dir
			FROM ' . $this->pa_files_table . ' d
			LEFT JOIN ' . $this->pa_cat_table . ' c
				ON d.file_catid = c.cat_id
			WHERE file_id = ' . (int) $file_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$current_cat_id = $row['file_catid'];
		$current_cat_name = $row['cat_sub_dir'];
		$current_filename = $row['real_name'];
		$current_file_size = $row['file_size'];
		$this->db->sql_freeresult($result);

		$title 				= $this->request->variable('title', '', true);
		$v_cat_id			= $this->request->variable('parent', '');
		$file_version		= $this->request->variable('file_version', '', true);
		$costs_dl			= $this->request->variable('cost_per_dl', 0.00);
		$file_update_time 	= time();
		$desc 				= $this->request->variable('desc', '', true);
		$announce_up 		= $this->request->variable('announce_up', '');
		$ftp_upload			= $this->request->variable('ftp_upload', '', true);

		$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
		$allow_bbcode = $allow_urls = $allow_smilies = true;

		// Add allowed extensions
		$allowed_extensions = $this->functions->allowed_extensions();

		if ($this->files_factory !== null)
		{
			$fileupload = $this->files_factory->get('upload')
				->set_allowed_extensions($allowed_extensions);
		}
		else
		{
			generate_text_for_storage($desc, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

			if (!class_exists('\fileupload'))
			{
				include($this->root_path . 'includes/functions_upload.' . $this->php_ext);
			}
			$fileupload = new \fileupload();
			$fileupload->fileupload('', $allowed_extensions);
		}

		$target_folder = $this->request->variable('parent', 0);
		$upload_name = $this->request->variable('filename', '');

		// Check if FTP upload and normal upload is entered
		if ($ftp_upload && $upload_name)
		{
			trigger_error($this->user->lang['ACP_FTP_UPLOAD'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT cat_sub_dir
			FROM ' . $this->pa_cat_table . '
			WHERE cat_id = ' . (int) $target_folder;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$target = $row['cat_sub_dir'];
		$this->db->sql_freeresult($result);

		$upload_dir = $this->module_root_path . 'uploads/' . $target;

		if (!$ftp_upload)
		{
			$upload_file = (isset($this->files_factory)) ? $fileupload->handle_upload('files.types.form', 'filename') : $fileupload->form_upload('filename');

			$new_filename = $upload_file->get('uploadname');

			if (!$upload_file->get('uploadname'))
			{
				$new_filename = $current_filename;
				$file_size = $current_file_size;
			}
			else
			{
				$delete_file = $this->ext_path_web . 'pafiledb/' . $current_cat_name .'/' . $current_filename;
				@unlink($delete_file);

				$upload_file->move_file($upload_dir, false, false, false);
				@chmod($this->ext_path_web . 'pafiledb/' . $upload_file->get('uploadname'), 0644);

				if (sizeof($upload_file->error) && $upload_file->get('uploadname'))
				{
					$upload_file->remove();
					trigger_error(implode('<br />', $upload_file->error));
				}

				$file_size = @file_size($upload_dir . '/' . $new_filename);
			}

			$sql_ary = array(
				'file_name'			=> $title,
				'file_version'		=> $file_version,
				'file_desc'			=> $desc,
				'real_name'			=> $new_filename,
				'file_catid'		=> $v_cat_id,
				'cost_per_dl'		=> $costs_dl,
				'file_update_time'	=> $file_update_time,
				'bbcode_uid'		=> $uid,
				'bbcode_bitfield'	=> $bitfield,
				'bbcode_options'	=> $options,
				'file_size'			=> $file_size,
				'user_id'			=> $this->user->data['user_id'],
			);

			// If the title is empty, return an error
			if ($title == '')
			{
				trigger_error($this->user->lang['ACP_NEED_DATA'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				// Check, if the file already is in the directory
				$sql = 'SELECT cat_sub_dir
					FROM ' . $this->pa_cat_table . '
					WHERE cat_id = ' . (int) $v_cat_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$cat_dir = $row['cat_sub_dir'];
				$this->db->sql_freeresult($result);

				$filecheck = $this->ext_path_web . 'pafiledb/' . $cat_dir .'/' . $new_filename;
				$filecheck_current = $this->ext_path_web . 'pafiledb/' . $current_cat_name .'/' . $new_filename;

				// If file should move to new category
				if ($current_cat_id != $v_cat_id)
				{
					// Check, if the file already exists in the cat, where to move
					if (file_exists($filecheck))
					{
						//trigger_error($this->user->lang['ACP_UPLOAD_FILE_EXISTS'] . adm_back_link($this->u_action), E_USER_WARNING);//continue;
					
						// Announce download, if enabled
						if ($pafiledb_config['use_comments'] == 1 && $announce_up != '')
						{
							$sql = 'SELECT *
								FROM ' . $this->pa_cat_table . '
								WHERE cat_id = ' . (int) $v_cat_id;
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);
							$cat_name = $row['cat_name'];
							$this->db->sql_freeresult($result);

							if (empty($file_version))
							{
								$dl_title = $title;
							}
							else
							{
								$dl_title = $title . ' v' . $file_version;
							}

							$download_link = '[url=' . generate_board_url() . '/category?cat_id=' . $v_cat_id . ']' . $this->user->lang['ACP_CLICK'] . '[/url]';
							$download_subject = sprintf($this->user->lang['ACP_ANNOUNCE_UP_TITLE'], $dl_title);

							if ($this->files_factory !== null)
							{
								$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, $desc, $cat_name, $download_link);
							}
							else
							{
								$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, generate_text_for_display($desc, $uid, $bitfield, $options), $cat_name, $download_link);

							}
							$this->functions->create_announcement($download_subject, $download_msg, $pafiledb_config['comments_forum_id']);
						}

						if (rename(($this->module_root_path . 'uploads/' . $current_cat_name . '/' .$new_filename), ($this->module_root_path . 'uploads/' . $cat_dir . '/' . $new_filename)))
						{
							$this->db->sql_query('UPDATE ' . $this->pa_files_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE file_id = ' . (int) $file_id);
							$this->cache->destroy('sql', $this->pa_files_table);
						}

						// Log message
						$this->log_message('LOG_DOWNLOAD_UPDATED', $title, 'ACP_DOWNLOAD_UPDATED');

						return;									
					}
					else
					{
						// Announce download, if enabled
						if ($pafiledb_config['use_comments'] == 1 && $announce_up != '')
						{
							$sql = 'SELECT *
								FROM ' . $this->pa_cat_table . '
								WHERE cat_id = ' . (int) $v_cat_id;
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);
							$cat_name = $row['cat_name'];
							$this->db->sql_freeresult($result);

							if (empty($file_version))
							{
								$dl_title = $title;
							}
							else
							{
								$dl_title = $title . ' v' . $file_version;
							}

							$download_link = '[url=' . generate_board_url() . '/category?cat_id=' . $v_cat_id . ']' . $this->user->lang['ACP_CLICK'] . '[/url]';
							$download_subject = sprintf($this->user->lang['ACP_ANNOUNCE_UP_TITLE'], $dl_title);

							if ($this->files_factory !== null)
							{
								$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, $desc, $cat_name, $download_link);
							}
							else
							{
								$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, generate_text_for_display($desc, $uid, $bitfield, $options), $cat_name, $download_link);

							}
							$this->functions->create_announcement($download_subject, $download_msg, $pafiledb_config['comments_forum_id']);
						}

						if (rename(($this->module_root_path . 'uploads/' . $current_cat_name . '/' .$new_filename), ($this->module_root_path . 'uploads/' . $cat_dir . '/' . $new_filename)))
						{
							$this->db->sql_query('UPDATE ' . $this->pa_files_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE file_id = ' . (int) $file_id);
							$this->cache->destroy('sql', $this->pa_files_table);
						}

						// Log message
						$this->log_message('LOG_DOWNLOAD_UPDATED', $title, 'ACP_DOWNLOAD_UPDATED');

						return;
					}
				}
				else // If only data changes and no new cat
				{
					// Announce download, if enabled
					if ($pafiledb_config['use_comments'] == 1 && $announce_up != '')
					{
						$sql = 'SELECT *
							FROM ' . $this->pa_cat_table . '
							WHERE cat_id = ' . (int) $v_cat_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$cat_name = $row['cat_name'];
						$this->db->sql_freeresult($result);

						if (empty($file_version))
						{
							$dl_title = $title;
						}
						else
						{
							$dl_title = $title . ' v' . $file_version;
						}

						$download_link = '[url=' . generate_board_url() . '/pafiledb_category?cat_id=' . $v_cat_id . ']' . $this->user->lang['ACP_CLICK'] . '[/url]';
						$download_subject = sprintf($this->user->lang['ACP_ANNOUNCE_UP_TITLE'], $dl_title);

						if ($this->files_factory !== null)
						{
							$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, $desc, $cat_name, $download_link);
						}
						else
						{
							$download_msg = sprintf($this->user->lang['ACP_ANNOUNCE_UP_MSG'], $title, generate_text_for_display($desc, $uid, $bitfield, $options), $cat_name, $download_link);

						}
						$this->functions->create_announcement($download_subject, $download_msg, $pafiledb_config['comments_forum_id']);
					}
					$this->db->sql_query('UPDATE ' . $this->pa_files_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE file_id = ' . (int) $file_id);
					$this->cache->destroy('sql', $this->pa_files_table);

					// Log message
					$this->log_message('LOG_DOWNLOAD_UPDATED', $title, 'ACP_DOWNLOAD_UPDATED');

					return;
				}
			}
		}
		else
		{
			// check, if FTP upload file exists
			if (!file_exists($upload_dir . '/' . $ftp_upload))
			{
				trigger_error($this->user->lang['ACP_UPLOAD_FILE_NOT_EXISTS'] . adm_back_link($this->u_action), E_USER_WARNING);//continue;
			}

			$file_size = @file_size($upload_dir . '/' . $ftp_upload);
			$sql_ary = array(
				'file_name'	=> $title,
				'file_desc'	 	=> $desc,
				'real_name'	=> $ftp_upload,
				'file_version'	=> $file_version,
				'file_catid'	=> $cat_option,
				'file_time'		=> $file_time,
				'cost_per_dl'		=> $costs_dl,
				'file_update_time'	=> $file_update_time,
				'bbcode_uid'		=> $uid,
				'bbcode_bitfield'	=> $bitfield,
				'bbcode_options'	=> $options,
				'file_size'			=> $file_size,
				'user_id'	=> $this->user->data['user_id'],
			);
		}
	}

	public function delete()
	{
		$file_id = $this->request->variable('file_id', '');

		// Delete an existing download
		if (confirm_box(true))
		{
			$sql = 'SELECT c.cat_sub_dir, d.real_name
				FROM ' . $this->pa_cat_table . ' c
				LEFT JOIN ' . $this->pa_files_table . ' d
					ON c.cat_id = d.file_catid
				WHERE d.file_id = ' . (int) $file_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$cat_dir = $row['cat_sub_dir'];
			$file_name = $row['real_name'];
			$this->db->sql_freeresult($result);

			$delete_file = $this->ext_path_web . 'pafiledb/' . $cat_dir .'/' . $file_name;
			@unlink($delete_file);

			$sql = 'DELETE FROM ' . $this->pa_files_table . '
				WHERE file_id = '. (int) $file_id;
			$this->db->sql_query($sql);

			// Log message
			$this->log_message('LOG_DOWNLOAD_DELETED', $file_name, 'ACP_DOWNLOAD_DELETED');
		}
		else
		{
			confirm_box(false, $this->user->lang['ACP_REALLY_DELETE'], build_hidden_fields(array(
				'file_id'	=> $file_id,
				'action'	=> 'delete',
				))
			);
		}
		redirect($this->u_action);
	}

	public function display_downloads()
	{
		$this->user->add_lang('posting');

		/* Define the tokens from the symbol table, just in case are not compiled in PHP5  */
		if(!defined('T_CONCAT_EQUAL'))
		{
			@define('T_CONCAT_EQUAL', 275);
			@define('T_STRING', 310);
			@define('T_OBJECT_OPERATOR', 363);
			@define('T_VARIABLE', 312);	
			@define('T_CONSTANT_ENCAPSED_STRING', 318);	
			@define('T_LNUMBER', 308);	
			@define('T_IF', 304);
			@define('T_ELSE', 306);
			@define('T_ELSEIF', 305);
			@define('T_WHITESPACE', 379);
			@define('T_FOR', 323);
			@define('T_FOREACH', 325);
			@define('T_WHILE', 321);
			@define('T_COMMENT', 374);
			@define('T_DOC_COMMENT', 375);				
		}		
		
		// Setup message parser
		$this->message_parser = new \parse_message();

		$action 		= $this->request->is_set_post('submit');
		$cat_id			= $this->request->variable('cat_id', 0);
		$form_action 	= $this->u_action. '&amp;action=add';
		$this->user->lang_mode 		= $this->user->lang['ACP_ADD'];

		// Read out config values
		$pafiledb_config = $this->functions->config_values();

		$start	= $this->request->variable('start', 0);
		$number	= $pafiledb_config['pagination_acp'];

		$this->template->assign_vars(array(
			'BASE'	=> $this->u_action,
		));

		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 'file_name');
		$sort_dir	= $this->request->variable('sd', 'ASC');
		$limit_days = array(0 => $this->user->lang['ACP_ALL_DOWNLOADS'], 1 => $this->user->lang['1_DAY'], 7 => $this->user->lang['7_DAYS'], 14 => $this->user->lang['2_WEEKS'], 30 => $this->user->lang['1_MONTH'], 90 => $this->user->lang['3_MONTHS'], 180 => $this->user->lang['6_MONTHS'], 365 => $this->user->lang['1_YEAR']);

		$sort_by_text = array('t' => $this->user->lang['ACP_SORT_TITLE'], 'c' => $this->user->lang['ACP_SORT_CAT']);
		$sort_by_sql = array('t' => 'file_name', 'c' => 'cat_name');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		// Total number of downloads
		$sql = 'SELECT COUNT(file_id) AS total_downloads
			FROM ' . $this->pa_files_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_downloads = $row['total_downloads'];
		$this->db->sql_freeresult($result);

		// List all downloads
		$sql = 'SELECT d.*, c.*
			FROM ' . $this->pa_files_table . ' d
			LEFT JOIN ' . $this->pa_cat_table . ' c
				ON d.file_catid = c.cat_id
			ORDER BY '. $sql_sort_order;
		$result = $this->db->sql_query_limit($sql, $number, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->message_parser->message = $row['file_desc'];
			$this->message_parser->bbcode_bitfield = $row['bbcode_bitfield'];
			$this->message_parser->bbcode_uid = $row['bbcode_uid'];
			$allow_bbcode = $allow_magic_url = $allow_smilies = true;
			$this->message_parser->format_display($allow_bbcode, $allow_magic_url, $allow_smilies);

			$this->template->assign_block_vars('downloads', array(
				'ICON_COPY'		=> '<img src="' . $this->root_path . 'adm/images/file_new.gif" alt="' . $this->user->lang['ACP_COPY_NEW'] . '" title="' . $this->user->lang['ACP_COPY_NEW'] . '" />',
				'TITLE'			=> $row['file_name'],
				'FILENAME'		=> $row['real_name'],
				'DESC'			=> $this->message_parser->message,
				'VERSION'		=> $row['file_version'],
				'DL_COST'		=> ($row['cost_per_dl'] == 0 ? $this->user->lang['ACP_COST_FREE'] : $row['cost_per_dl']),
				'SUB_DIR'		=> $row['cat_sub_dir'],
				'CATNAME'		=> $row['cat_name'],
				'U_COPY'		=> $this->u_action . '&amp;action=copy_new&amp;file_id=' .$row['file_id'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;file_id=' .$row['file_id'],
				'U_DEL'			=> $this->u_action . '&amp;action=delete&amp;file_id=' .$row['file_id'],
			));
		}
		$this->db->sql_freeresult($result);

		$base_url = $this->u_action;
		//Start pagination
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_downloads, $number, $start);

		$this->template->assign_vars(array(
			'S_DOWNLOAD_ACTION' => $this->u_action,
			'S_SELECT_SORT_DIR'	=> $s_sort_dir,
			'S_SELECT_SORT_KEY'	=> $s_sort_key,
			'TOTAL_DOWNLOADS'	=> ($total_downloads == 1) ? $this->user->lang['ACP_SINGLE_DOWNLOAD'] : sprintf($this->user->lang['ACP_MULTI_DOWNLOAD'], $total_downloads),
			'U_NEW_DOWNLOAD'	=> $this->u_action . '&amp;action=new_download',
			'L_MODE_TITLE'		=> $this->user->lang_mode,
			'U_EDIT_ACTION'		=> $this->u_action,
		));
	}

	/**
	* Function for managing categories
	*/
	public function manage_cats()
	{
		$catrow = array();
		$cat_parent = $this->request->variable('cat_parent', 0);
		$this->template->assign_vars(array(
			'S_MODE_MANAGE'	=> true,
			'S_ACTION'		=> $this->u_action . '&amp;action=create&amp;cat_parent=' . $cat_parent,
		));
		if (!$cat_parent)
		{
			$navigation = $this->user->lang['ACP_CAT_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $this->user->lang['ACP_CAT_INDEX'] . '</a>';
			$pa_files_nav = $this->functions->get_cat_branch($cat_parent, 'parents', 'descending');
			foreach ($pa_files_nav as $row)
			{
				if ($row['cat_id'] == $cat_parent)
				{
					$navigation .= ' -&gt; ' . $row['cat_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;cat_parent=' . $row['cat_id'] . '">' . $row['cat_name'] . '</a>';
				}
			}
		}
		$pa_files = array();
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$pa_files[] = $row;
		}

		for ($i = 0; $i < count($pa_files); $i++)
		{
			$folder_image = ($pa_files[$i]['left_id'] + 1 != $pa_files[$i]['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $this->user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $this->user->lang['FOLDER'] . '" />';
			$url = $this->u_action . "&amp;cat_parent=$cat_parent&amp;cat_id={$pa_files[$i]['cat_id']}";

			$this->template->assign_block_vars('catrow', array(
				'FOLDER_IMAGE'			=> $folder_image,
				'U_CAT'					=> $this->u_action . '&amp;cat_parent=' . $pa_files[$i]['cat_id'],
				'CAT_NAME'				=> $pa_files[$i]['cat_name'],
				'CAT_SUBS'				=> ($pa_files[$i]['left_id'] + 1 == $pa_files[$i]['right_id'] && !$pa_files[$i]['cat_id'] == $pa_files[$i]['cat_parent']) ? true : false,
				'CAT_SUBS_SHOW'			=> ($pa_files[$i]['left_id'] + 1 != $pa_files[$i]['right_id'] && $pa_files[$i]['cat_id'] != $cat_parent	|| $pa_files[$i]['cat_parent'] == 0) ? true : false,
				'CAT_NAME_SHOW'			=> ($pa_files[$i]['cat_name_show'] == 1) ? $this->user->lang['ACP_CAT_NAME_SHOW_YES'] : $this->user->lang['ACP_CAT_NAME_SHOW_NO'],
				'CAT_DESCRIPTION'		=> generate_text_for_display($pa_files[$i]['cat_desc'], $pa_files[$i]['cat_desc_uid'], $pa_files[$i]['cat_desc_bitfield'], $pa_files[$i]['cat_desc_options']),
				'U_MOVE_UP'				=> $this->u_action . '&amp;action=move&amp;move=move_up&amp;cat_id=' . $pa_files[$i]['cat_id'],
				'U_MOVE_DOWN'			=> $this->u_action . '&amp;action=move&amp;move=move_down&amp;cat_id=' . $pa_files[$i]['cat_id'],
				'U_EDIT'				=> $this->u_action . '&amp;action=edit&amp;cat_id=' . $pa_files[$i]['cat_id'],
				'U_DELETE'				=> $this->u_action . '&amp;action=delete&amp;cat_id=' . $pa_files[$i]['cat_id'],
			));
		}

		$this->template->assign_vars(array(
			'NAVIGATION'		=> $navigation,
			'S_PA_FILES'		=> $cat_parent,
			'U_EDIT'			=> ($cat_parent) ? $this->u_action . '&amp;action=edit&amp;cat_id=' . $cat_parent : '',
			'U_DELETE'			=> ($cat_parent) ? $this->u_action . '&amp;action=delete&amp;cat_id=' . $cat_parent : '',
		));
	}

	/**
	* Function for create a category
	*/
	public function create_cat()
	{
		if ($this->request->is_set('submit'))
		{
			$pa_files_data = array();
			$pa_files_data = array(
				'cat_name'			=> $this->request->variable('cat_name', '', true),
				'cat_sub_dir'		=> $this->request->variable('cat_sub_dir', ''),
				'cat_parent'		=> $this->request->variable('cat_parent', 0),
				'parents_data'		=> $this->request->variable('parents_data', 0),
				'cat_desc'			=> $this->request->variable('cat_desc', '', true),
				'cat_desc_options'	=> 7,
				'cat_name_show'		=> $this->request->variable('cat_name_show', 0),
			);

			generate_text_for_storage($pa_files_data['cat_desc'], $pa_files_data['cat_desc_uid'], $pa_files_data['cat_desc_bitfield'], $pa_files_data['cat_desc_options'], $this->request->variable('desc_parse_bbcode', false), $this->request->variable('desc_parse_urls', false), $this->request->variable('desc_parse_smilies', false));

			// Create variable for the cat_sub_dir name
			$cat_sub_dir_name = '';
			$cat_sub_dir_name = $pa_files_data['cat_sub_dir'];

			// Check, if sub-dir is filled
			if (empty($cat_sub_dir_name))
			{
				trigger_error($this->user->lang['ACP_CAT_NAME_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Do the check, if cat_sub_dir has valid characters only

			// Let's make an array of allowed characters
			$allowed = range('a', 'z'); //latin letters
			$allowed = array_merge($allowed, range(0, 9)); //numbers
			// Additional symbols (recommended only these two below)
			$allowed[] = '_';
			$allowed[] = '-';
			$allowed = implode($allowed);

			// Now split the new category name into single parts
			$new_dir_name = str_split($cat_sub_dir_name); //works only in PHP5!

			// Check each character if it's allowed
			foreach ($new_dir_name as $var)
			{
				if (stristr($allowed, $var) === false)
				{
					trigger_error($this->user->lang['ACP_WRONG_CHAR'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// Check if sub dir name already exists
			$sql = 'SELECT * FROM ' . $this->pa_cat_table . "
				WHERE cat_sub_dir LIKE '$cat_sub_dir_name'";
			$result= $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			if ($row)
			{
				trigger_error($this->user->lang['ACP_CAT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($pa_files_data['cat_parent'])
			{
				$sql = 'SELECT left_id, right_id
					FROM ' . $this->pa_cat_table . '
					WHERE cat_id = ' . $pa_files_data['cat_parent'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($this->user->lang['PARENT_NOT_EXIST'] . adm_back_link($this->u_action . '&amp;' . $this->cat_parent), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . $this->pa_cat_table . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->pa_cat_table . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$this->db->sql_query($sql);

				$pa_files_data['left_id'] = $row['right_id'];
				$pa_files_data['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->pa_cat_table;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$pa_files_data['left_id'] = $row['right_id'] + 1;
				$pa_files_data['right_id'] = $row['right_id'] + 2;
			}
			$this->db->sql_query('INSERT INTO ' . $this->pa_cat_table . ' ' . $this->db->sql_build_array('INSERT', $pa_files_data));
			$this->cache->destroy('sql', $this->pa_cat_table);

			// Log message
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CATEGORY_ADD', time(), array($cat_sub_dir_name));

			// Check if created foldername already exists
			if (is_dir($this->module_root_path . 'uploads/' . $cat_sub_dir_name))
			{
				trigger_error($this->user->lang['ACP_CAT_NEW_DONE'] . adm_back_link($this->u_action . '&amp;cat_parent=' . $pa_files_data['cat_parent']));
			}
			else if (mkdir(($this->module_root_path . 'uploads/' . $cat_sub_dir_name)))
			{
				if (copy(($this->module_root_path . 'uploads/' .'index.htm'), ($this->module_root_path . 'uploads/' . $cat_sub_dir_name . '/index.htm')))
				{
					trigger_error($this->user->lang['ACP_CAT_NEW_DONE'] . adm_back_link($this->u_action . '&amp;cat_parent=' . $pa_files_data['cat_parent']));
				}
			}
		}

		$parent_options = $this->functions->make_cat_select($this->request->variable('cat_parent', 0), false, false, false, false);
		$this->template->assign_vars(array(
			'S_MODE_CREATE'				=> true,
			'S_ACTION'					=> $this->u_action . '&amp;cat_parent=' . $this->request->variable('cat_parent', 0),
			'S_DESC_BBCODE_CHECKED'		=> true,
			'S_DESC_SMILIES_CHECKED'	=> true,
			'S_DESC_URLS_CHECKED'		=> true,
			'S_PARENT_OPTIONS'			=> $parent_options,
			'CAT_NAME_SHOW'				=> $this->request->variable('cat_name_show', 1),
			'CAT_NAME_NO_SHOW'			=> $this->user->lang['ACP_SUB_NO_CAT'],
		));
	}

	/**
	* Function for editing a category
	*/
	public function edit_cat()
	{
		if (!$cat_id = $this->request->variable('cat_id', 0))
		{
			trigger_error($this->user->lang['ACP_NO_CAT_ID'], E_USER_WARNING);
		}

		if ($this->request->is_set('submit'))
		{
			$pa_files_data = array();
			$pa_files_data = array(
				'cat_name'						=> $this->request->variable('cat_name', '', true),
				'cat_parent'					=> $this->request->variable('cat_parent', 0),
				'parents_data'					=> '',
				'cat_desc_options'				=> 7,
				'cat_desc'						=> $this->request->variable('cat_desc', '', true),
				'cat_name_show'					=> $this->request->variable('cat_name_show', 0),
			);
			
			$allow_bbcode = $this->request->variable('desc_parse_bbcode', false);
			$allow_urls = $this->request->variable('desc_parse_urls', false);
			$allow_smilies = $this->request->variable('desc_parse_smilies', false);			
			
			/* Define the tokens from the symbol table, just in case are not compiled in PHP5  */
			if(!defined('T_CONCAT_EQUAL'))
			{
				@define('T_CONCAT_EQUAL', 275);
				@define('T_STRING', 310);
				@define('T_OBJECT_OPERATOR', 363);
				@define('T_VARIABLE', 312);	
				@define('T_CONSTANT_ENCAPSED_STRING', 318);	
				@define('T_LNUMBER', 308);	
				@define('T_IF', 304);
				@define('T_ELSE', 306);
				@define('T_ELSEIF', 305);
				@define('T_WHITESPACE', 379);
				@define('T_FOR', 323);
				@define('T_FOREACH', 325);
				@define('T_WHILE', 321);
				@define('T_COMMENT', 374);
				@define('T_DOC_COMMENT', 375);				
			}			
			
			// Prepare text for storage			
			generate_text_for_storage($pa_files_data['cat_desc'], $pa_files_data['cat_desc_uid'], $pa_files_data['cat_desc_bitfield'], $pa_files_data['cat_desc_options'], $allow_bbcode, $allow_urls, $allow_smilies);		
			
			$row = $this->functions->get_cat_info($cat_id);

			if ($row['cat_parent'] != $pa_files_data['cat_parent'])
			{
				//how many do we have to move and how far
				$moving_ids = ($row['right_id'] - $row['left_id']) + 1;
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->pa_cat_table;
				$result = $this->db->sql_query($sql);
				$highest = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
				$moving_distance = ($highest['right_id'] - $row['left_id']) + 1;
				$stop_updating = $moving_distance + $row['left_id'];

				//update the moving download... move it to the end
				$sql = 'UPDATE ' . $this->pa_cat_table . '
					SET right_id = right_id + ' . $moving_distance . ',
						left_id = left_id + ' . $moving_distance . '
					WHERE left_id >= ' . $row['left_id'] . '
						AND right_id <= ' . $row['right_id'];
				$this->db->sql_query($sql);
				$new['left_id'] = $row['left_id'] + $moving_distance;
				$new['right_id'] = $row['right_id'] + $moving_distance;

				//close the gap, we got
				if ($pa_files_data['cat_parent'] == 0)
				{
					//we move to root
					//left_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE left_id >= ' . $row['left_id'];
					$this->db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE right_id >= ' . $row['left_id'];
					$this->db->sql_query($sql);
				}
				else
				{
					//close the gap
					//left_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE left_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$this->db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE right_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$this->db->sql_query($sql);

					//create new gap
					//need parent_information
					$parent = $this->functions->get_cat_info($pa_files_data['cat_parent']);
					//left_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET left_id = left_id + ' . $moving_ids . '
						WHERE left_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$this->db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET right_id = right_id + ' . $moving_ids . '
						WHERE right_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$this->db->sql_query($sql);

					//close the gap again
					//new parent right_id!!!
					$parent['right_id'] = $parent['right_id'] + $moving_ids;
					$move_back = ($new['right_id'] - $parent['right_id']) + 1;
					$sql = 'UPDATE ' . $this->pa_cat_table . '
						SET left_id = left_id - ' . $move_back . ',
							right_id = right_id - ' . $move_back . '
						WHERE left_id >= ' . $stop_updating;
					$this->db->sql_query($sql);
				}
			}

			if ($row['cat_name'] != $pa_files_data['cat_name'])
			{
				// the forum name has changed, clear the parents list of all forums (for safety)
				$sql = 'UPDATE ' . $this->pa_cat_table . "
					SET parents_data = ''";
				$this->db->sql_query($sql);
			}

			$sql = 'UPDATE ' . $this->pa_cat_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $pa_files_data) . '
					WHERE cat_id	= ' . (int) $cat_id;
			$this->db->sql_query($sql);
			$this->cache->destroy('sql', $this->pa_cat_table);

			// Log message
			$this->log_message('LOG_CATEGORY_UPDATED', $pa_files_data['cat_name'], 'ACP_CAT_EDIT_DONE');
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . "
			WHERE cat_id = '$cat_id'";
		$result = $this->db->sql_query($sql);

		if ($this->db->sql_affectedrows($result) == 0)
		{
			trigger_error($this->user->lang['ACP_CAT_NOT_EXIST'], E_USER_WARNING);
		}
		$pa_files_data = $this->db->sql_fetchrow($result);
		$pa_files_desc_data = generate_text_for_edit($pa_files_data['cat_desc'], $pa_files_data['cat_desc_uid'], $pa_files_data['cat_desc_options']);

		$parents_list = $this->functions->make_cat_select($pa_files_data['cat_parent'], $cat_id);

		// Has subcategories
		if (($pa_files_data['left_id'] + 1) != $pa_files_data['right_id'])
		{
			$subcategories = false;
		}
		else
		{
			$subcategories = true;
		}

		$this->template->assign_vars(array(
			'S_MODE_EDIT'				=> true,
			'S_ACTION'					=> $this->u_action . '&amp;action=edit&amp;cat_id=' . $cat_id,
			'S_PARENT_OPTIONS'			=> $parents_list,
			'CAT_NAME'					=> $pa_files_data['cat_name'],
			'CAT_DESC'					=> $pa_files_desc_data['text'],
			'CAT_SUB_DIR'				=> $pa_files_data['cat_sub_dir'],
			'S_DESC_BBCODE_CHECKED'		=> ($pa_files_desc_data['allow_bbcode']) ? true : false,
			'S_DESC_SMILIES_CHECKED'	=> ($pa_files_desc_data['allow_smilies']) ? true : false,
			'S_DESC_URLS_CHECKED'		=> ($pa_files_desc_data['allow_urls']) ? true : false,
			'S_HAS_SUBCATS'				=> $subcategories,
			'S_MODE'					=> 'edit',
			'CAT_NAME_SHOW'				=> $pa_files_data['cat_name_show'],
			'CAT_NAME_NO_SHOW'			=> $this->user->lang['ACP_SUB_NO_CAT'],
		));
	}

	/**
	* Function for deleting a category
	*/
	public function delete_cat()
	{
		if (!$cat_id = $this->request->variable('cat_id', 0))
		{
			trigger_error($this->user->lang['ACP_NO_CAT_ID'], E_USER_WARNING);
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->pa_cat_table . "
				WHERE cat_id = '$cat_id'";
			$result = $this->db->sql_query($sql);
			if ($this->db->sql_affectedrows($result) == 0)
			{
				trigger_error($this->user->lang['ACP_CAT_NOT_EXIST'], E_USER_WARNING);
			}
		}

		if ($this->request->is_set('submit'))
		{
			$pa_files = $this->functions->get_cat_info($cat_id);
			$handle_subs = $this->request->variable('handle_subs', 0);
			$handle_downloads = $this->request->variable('handle_downloads', 0);
			if (($pa_files['right_id'] - $pa_files['left_id']) > 2)
			{
				//handle subs if there
				//we have to learn how to delete or move the subs
				if ($handle_subs >= 0)
				{
					trigger_error($this->user->lang['ACP_DELETE_SUB_CATS'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// Get cat directory name
			$sql = 'SELECT cat_sub_dir, cat_name
				FROM ' . $this->pa_cat_table . '
				WHERE cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$sub_cat_dir = $row['cat_sub_dir'];
			$cat_name = $row['cat_name'];
			$this->db->sql_freeresult($result);

			// Check if category has files
			$sql = ' SELECT COUNT(file_id) AS has_downloads
				FROM ' . $this->pa_files_table . '
				WHERE file_catid = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$has_downloads = $row['has_downloads'];
			$this->db->sql_freeresult($result);

			if ($has_downloads > 0)
			{
				trigger_error($this->user->lang['ACP_DELETE_HAS_FILES'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			//reorder the other downloads
			//left_id
			$sql = 'UPDATE ' . $this->pa_cat_table . '
				SET left_id = left_id - 2
				WHERE left_id > ' . $pa_files['left_id'];
			$this->db->sql_query($sql);
			//right_id
			$sql = 'UPDATE ' . $this->pa_cat_table . '
				SET right_id = right_id - 2
				WHERE right_id > ' . $pa_files['left_id'];
			$this->db->sql_query($sql);
			$sql = 'DELETE FROM ' . $this->pa_cat_table . "
				WHERE cat_id = '$cat_id'";
			$result = $this->db->sql_query($sql);
			$this->cache->destroy('sql', $this->pa_cat_table);

			// Remove the folder and all of its content
			$this->remove_dir($sub_cat_dir);

			// Log message
			$this->log_message('LOG_CATEGORY_DELETED', $cat_name, 'ACP_CAT_DELETE_DONE');
		}

		$catname = '';
		$sql = 'SELECT ec.*, COUNT(ed.file_id) AS downloads
			FROM ' . $this->pa_cat_table . ' AS ec
			LEFT JOIN ' . $this->pa_files_table . ' AS ed
				ON ec.cat_id = ed.file_catid
			WHERE ec.cat_id = ' . (int) $cat_id . '
			GROUP BY ec.cat_id';
		$result = $this->db->sql_query($sql);

		$subs_found = false;
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['cat_id'] == $cat_id)
			{
				$thiseds = $row;
				$subs_found = true;
			}
			else
			{
				$edsrow[] = $row;
			}

			$catname = $row['cat_name'];
		}

		if (!$subs_found)
		{
			trigger_error($this->user->lang['ACP_CAT_NOT_EXIST'], E_USER_WARNING);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'S_MODE_DELETE'				=> true,
			'S_CAT_ACTION'				=> $this->u_action . '&amp;action=delete&amp;pa_files_id=' . $cat_id,
			'CAT_DELETE'				=> sprintf($this->user->lang['ACP_DEL_CAT'], $catname),
			'S_PARENT_OPTIONS'			=> $this->functions->make_cat_select($thiseds['cat_parent'], $cat_id),
			'S_HAS_CHILDREN'			=> ($thiseds['left_id'] + 1 != $thiseds['right_id']) ? true : false,
			'S_HAS_DOWNLOADS'			=> ($thiseds['downloads'] > 0) ? true : false,
			'CAT_NAME'					=> $catname,
			'CAT_DESC'					=> generate_text_for_display($thiseds['cat_desc'], $thiseds['cat_desc_uid'], $thiseds['cat_desc_bitfield'], $thiseds['cat_desc_options']),
			'S_MOVE_PA_FILES_OPTIONS'	=> $this->functions->make_cat_select(false, $cat_id),
			'S_MOVE_IMAGE_OPTIONS'		=> $this->functions->make_cat_select(false, $cat_id, true),
		));
	}
	/**
	 * Function for deleting a category
	 *
	 * @param unknown_type $cat_id
	 */
	function delete_category( $cat_id = false )
	{
		global $_POST;

		$file_to_cat_id = ( isset( $_POST['file_to_cat_id'] ) ) ? intval( $_POST['file_to_cat_id'] ) : '';
		$subcat_to_cat_id = ( isset( $_POST['subcat_to_cat_id'] ) ) ? intval( $_POST['subcat_to_cat_id'] ) : '';
		$file_mode = ( isset( $_POST['file_mode'] ) ) ? htmlspecialchars( $_POST['file_mode'] ) : 'move';
		$subcat_mode = ( isset( $_POST['subcat_mode'] ) ) ? htmlspecialchars( $_POST['subcat_mode'] ) : 'move';

		if ( empty( $cat_id ) )
		{
			$this->error[] = $this->user->lang['Cdelerror'];
		}
		else
		{
			if ( ( $file_to_cat_id == -1 || empty( $file_to_cat_id ) ) && $file_mode == 'move' )
			{
				$this->error[] = $this->user->lang['Cdelerror'];
			}

			if ( $subcat_mode == 'move' && empty( $subcat_to_cat_id ) )
			{
				$this->error[] = $this->user->lang['Cdelerror'];
			}

			if ( sizeof( $this->error ) )
			{
				return;
			}

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . "
				WHERE cat_id = $cat_id";

			if ( !( $this->db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );

			if ( $file_mode == 'delete' )
			{
				$this->delete_items( $cat_id, 'category' );
			}
			else
			{
				$this->move_items( $cat_id, $file_to_cat_id );
			}

			if ( $subcat_mode == 'delete' )
			{
				$this->delete_subcat( $cat_id, $file_mode, $file_to_cat_id );
			}
			else
			{
				$this->move_subcat( $cat_id, $subcat_to_cat_id );
			}
			$this->modified( true );
		}
	}

	/**
	 *  Function for moving an file from a category to another position
	 *
	 * @param unknown_type $from_cat
	 * @param unknown_type $to_cat
	 */
	function move_items( $from_cat, $to_cat )
	{
		$sql = 'UPDATE ' . PA_FILES_TABLE . "
			SET file_catid = $to_cat
			WHERE file_catid = $from_cat";

		if ( !( $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt move files', '', __LINE__, __FILE__, $sql );
		}

		$this->modified( true );
		return;
	}
	
	/**
	* Function for moving a category to another position
	*/
	function move_cat()
	{
		if (!$cat_id = $this->request->variable('cat_id', 0))
		{
			trigger_error($this->user->lang['ACP_NO_CAT_ID'], E_USER_WARNING);
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->pa_cat_table . "
				WHERE cat_id = '$cat_id'";
			$result = $this->db->sql_query($sql);
			if ($this->db->sql_affectedrows($result) == 0)
			{
				trigger_error($this->user->lang['ACP_CAT_NOT_EXIST'], E_USER_WARNING);
			}
		}
		$move = $this->request->variable('move', '', true);
		$moving = $this->functions->get_cat_info($cat_id);

		$sql = 'SELECT cat_id, left_id, right_id
			FROM ' . $this->pa_cat_table . "
			WHERE cat_parent = {$moving['cat_parent']}
				AND " . (($move == 'move_up') ? "right_id < {$moving['right_id']} ORDER BY right_id DESC" : "left_id > {$moving['left_id']} ORDER BY left_id ASC");
		$result = $this->db->sql_query_limit($sql, 1);

		$target = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The forum is already on top or bottom
			return false;
		}

		if ($move == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $moving['right_id'];

			$diff_up = $moving['left_id'] - $target['left_id'];
			$diff_down = $moving['right_id'] + 1 - $moving['left_id'];

			$move_up_left = $moving['left_id'];
			$move_up_right = $moving['right_id'];
		}
		else
		{
			$left_id = $moving['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $moving['right_id'] + 1 - $moving['left_id'];
			$diff_down = $target['right_id'] - $moving['right_id'];

			$move_up_left = $moving['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->pa_cat_table . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			parents_data = ''
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$this->db->sql_query($sql);
		$this->cache->destroy('sql', $this->pa_cat_table);
	//	redirect($this->u_action . '&amp;cat_parent=' . $moving['cat_parent']);
	}

	/**
	* Function for removing a category and all its content
	*/
	public function remove_dir($selected_dir)
	{
		$current_dir = $this->ext_path_web . 'pafiledb/' . $selected_dir;
		$empty_dir = $this->ext_path_web . 'pafiledb/' . $selected_dir . '/';

		if ($dir = @opendir($current_dir))
		{
			while (($f = readdir($dir)) !== false)
			{
				if ($f > '0' and filetype($empty_dir.$f) == "file")
				{
					@unlink($empty_dir.$f);
				}
				else if ($f > '0' and filetype($empty_dir.$f) == "dir")
				{
					remove_dir($current_dir.$f."\\");
				}
			}
			closedir($dir);
			rmdir($current_dir);
		}
	}

	/**
	 * Log Message
	 *
	 * @return message
	 * @access private
	*/
	private function log_message($log_message, $title, $user_message)
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_message, time(), array($title));

		trigger_error($this->user->lang[$user_message] . adm_back_link($this->u_action));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
	
	/**
	 * load admin module
	 *
	 * @param unknown_type $module_name send module name to load it
	 */
	function adminmodule($module_name)
	{
		if (!class_exists('pafiledb_' . $module_name) )
		{
			$this->module_name = $module_name;

			require_once( $this->module_root_path . 'acp/admin_' . $module_name . '.' . $this->php_ext );
			eval( '$this->modules[' . $module_name . '] = new pafiledb_' . $module_name . '();' );

			if ( method_exists( $this->modules[$module_name], 'init' ) )
			{
				$this->modules[$module_name]->init();
			}
			/*
			elseif ( method_exists( $this->modules[$module_name], $module_name ) )
			{
				$this->modules[$module_name]->$module_name();
			}
			*/
		}
	}

	function admin_display_cat_auth( $cat_parent = 0, $depth = 0 )
	{
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels;
		global $cat_auth_approval_fields, $cat_auth_approval_const, $cat_auth_approval_levels;

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
			{
				$this->template->assign_block_vars( 'cat_row', array(
					'CATEGORY_NAME' => $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' => $pre,
					'U_CAT' => append_sid( "admin_pafiledb.$this->php_ext?action=catauth_manage&cat_parent=$sub_cat_id" )
				));

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$custom_auth[$j] = '&nbsp;<select name="' . $cat_auth_fields[$j] . '[' . $sub_cat_id . ']' . '">';

					for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
					{
						$selected = ( $cat_data[$cat_auth_fields[$j]] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
						$custom_auth[$j] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $this->user->lang['Category_' . $cat_auth_levels[$k]] . '</option>';
					}
					$custom_auth[$j] .= '</select>&nbsp;';

					$this->template->assign_block_vars( 'cat_row.cat_auth_data', array( 'S_AUTH_LEVELS_SELECT' => $custom_auth[$j] ) );
				}

				for( $j = 0; $j < count( $cat_auth_approval_fields ); $j++ )
				{
					$custom_auth_approval[$j] = '&nbsp;<select name="' . $cat_auth_approval_fields[$j] . '[' . $sub_cat_id . ']' . '">';

					for( $k = 0; $k < count( $cat_auth_approval_levels ); $k++ )
					{
						$selected = ( $cat_data[$cat_auth_approval_fields[$j]] == $cat_auth_approval_const[$k] ) ? ' selected="selected"' : '';
						$custom_auth_approval[$j] .= '<option value="' . $cat_auth_approval_const[$k] . '"' . $selected . '>' . $this->user->lang['Category_' . $cat_auth_approval_levels[$k]] . '</option>';
					}
					$custom_auth_approval[$j] .= '</select>&nbsp;';

					$this->template->assign_block_vars( 'cat_row.cat_auth_data', array( 'S_AUTH_LEVELS_SELECT' => $custom_auth_approval[$j] ) );
				}

				$this->admin_display_cat_auth( $sub_cat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	function admin_display_cat_auth_ug( $cat_parent = 0, $depth = 0 )
	{
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels, $optionlist_mod, $optionlist_acl_adv;

		// Read out config values
		$pafiledb_config = $this->functions->config_values();
		
		$action = $this->request->variable('action', '');
		$form_action = $this->u_action. '&amp;action='.$action;
		
		$this->user->lang_mode = $this->user->lang['Cat_Permissions'];

		$cat_parent = $this->request->is_set('cat_parent') ? $this->request->variable('cat_parent') : '';

		$this->user->add_lang('posting');		
		
		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
			{
				$this->template->assign_block_vars( 'cat_row', array(
					'CAT_NAME' 		=> $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' 			=> $pre,
					'U_CAT' 		=> append_sid( "admin_pafiledb.$this->php_ext?action=catauth&cat_id=$sub_cat_id" ),
					'S_MOD_SELECT'	=> $optionlist_mod[$sub_cat_id]
				));

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$this->template->assign_block_vars( 'cat_row.aclvalues', array( 'S_ACL_SELECT' => $optionlist_acl_adv[$sub_cat_id][$j] ) );
				}
				$this->admin_display_cat_auth_ug( $sub_cat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	function admin_cat_main( $cat_parent = 0, $depth = 0 )
	{		
		// Read out config values
		$pafiledb_config = $this->functions->config_values();
		$this->tpl_name = 'pa_auth_cat_body';
		$action = $this->request->variable('action', '');
		$form_action = $this->u_action. '&amp;action='.$action;
		
		$this->user->lang_mode = $this->user->lang['Cat_Permissions'];

		$cat_parent = $this->request->is_set('cat_parent') ? $this->request->variable('cat_parent') : $cat_parent;

		$this->user->add_lang('posting');	
		
		$this->template->assign_vars(array(
			'BASE'	=> $this->u_action,
		));		
		
		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $subcat_id => $cat_data )
			{
				$this->template->assign_block_vars( 'cat_row', array(
					'IS_HIGHER_CAT' 	=> ( $cat_data['cat_allow_file'] == PA_CAT_ALLOW_FILE ) ? false : true,
					'U_CAT' 			=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&cat_id=$subcat_id" ),
					'U_CAT_EDIT' 		=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&mode=edit&amp;cat_id=$subcat_id" ),
					'U_CAT_DELETE' 		=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&mode=delete&amp;cat_id=$subcat_id" ),
					'U_CAT_MOVE_UP' 	=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&mode=cat_order&amp;move=-15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_MOVE_DOWN' 	=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&mode=cat_order&amp;move=15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_RESYNC' 		=> append_sid( "admin_pafiledb.$this->php_ext?action=cat_manage&mode=sync&amp;cat_id_other=$subcat_id" ),
					'CAT_NAME' 			=> $cat_data['cat_name'],
					'PRE' 				=> $pre
				));
				$this->admin_cat_main( $subcat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	function pa_auth_ug_select( $u_action )
	{		
		
		if ($request->is_set_post('submit') && (( $mode == 'user' && $user_id ) || ( $mode == 'group' && $group_id )) )
		{
			if ( $mode == 'user' )
			{
				switch (PORTAL_BACKEND)
				{
					case 'internal':
					case 'phpbb2':
						
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug
							WHERE ug.user_id = $user_id
								AND g.group_id = ug.group_id
								AND g.group_single_user = '1'";					
								
					break;

					case 'phpbb3':
						
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug
								LEFT JOIN " . USERS_TABLE . " u ON (ug.group_id = u.group_id)
							WHERE ug.user_id = $user_id
								AND g.group_id = ug.group_id";						
								
					break;
				}			
			
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}

			$change_mod_list = ( isset( $_POST['moderator'] ) ) ? $_POST['moderator'] : array();

			$change_acl_list = array();
			for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
			{
				$auth_field = $cat_auth_fields[$j];

				while ( list( $cat_id, $value ) = @each( $_POST['private_' . $auth_field] ) )
				{
					$change_acl_list[$cat_id][$auth_field] = $value;
				}
			}

			switch (PORTAL_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
					
					$sql = ( $mode == 'user' ) ? "SELECT aa.* FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id AND g.group_single_user = " . true : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id";					
							
				break;

				case 'phpbb3':
					
					$sql = ( $mode == 'user' ) ? "SELECT aa.* FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g LEFT JOIN " . USERS_TABLE . " u ON (ug.group_id = u.group_id) WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id" : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id";						
							
				break;
			}			
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access[$row['cat_id']] = $row;
			}
			$db->sql_freeresult( $result );

			$cat_auth_action = array();
			$update_acl_status = array();
			$update_mod_status = array();

			foreach( $this->cat_rowset as $cat_id => $cat_data )
			{
				if ( 	( isset( $auth_access[$cat_id]['auth_mod'] ) && $change_mod_list[$cat_id]['auth_mod'] != $auth_access[$cat_id]['auth_mod'] ) ||
						( !isset( $auth_access[$cat_id]['auth_mod'] ) && !empty( $change_mod_list[$cat_id]['auth_mod'] ) ) )
				{
					$update_mod_status[$cat_id] = $change_mod_list[$cat_id]['auth_mod'];

					if ( !$update_mod_status[$cat_id] )
					{
						$cat_auth_action[$cat_id] = 'delete';
					}
					else if ( !isset( $auth_access[$cat_id]['auth_mod'] ) )
					{
						$cat_auth_action[$cat_id] = 'insert';
					}
					else
					{
						$cat_auth_action[$cat_id] = 'update';
					}
				}

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$auth_field = $cat_auth_fields[$j];

					if ( $cat_data[$auth_field] == AUTH_ACL && isset( $change_acl_list[$cat_id][$auth_field] ) )
					{
						if ( ( empty( $auth_access[$cat_id]['auth_mod'] ) &&
									( isset( $auth_access[$cat_id][$auth_field] ) && $change_acl_list[$cat_id][$auth_field] != $auth_access[$cat_id][$auth_field] ) ||
									( !isset( $auth_access[$cat_id][$auth_field] ) && !empty( $change_acl_list[$cat_id][$auth_field] ) ) ) || !empty( $update_mod_status[$cat_id] )
								)
						{
							$update_acl_status[$cat_id][$auth_field] = ( !empty( $update_mod_status[$cat_id] ) ) ? 0 : $change_acl_list[$cat_id][$auth_field];

							if ( isset( $auth_access[$cat_id][$auth_field] ) && empty( $update_acl_status[$cat_id][$auth_field] ) && $cat_auth_action[$cat_id] != 'insert' && $cat_auth_action[$cat_id] != 'update' )
							{
								$cat_auth_action[$cat_id] = 'delete';
							}
							else if ( !isset( $auth_access[$cat_id][$auth_field] ) && !( $cat_auth_action[$cat_id] == 'delete' && empty( $update_acl_status[$cat_id][$auth_field] ) ) )
							{
								$cat_auth_action[$cat_id] = 'insert';
							}
							else if ( isset( $auth_access[$cat_id][$auth_field] ) && !empty( $update_acl_status[$cat_id][$auth_field] ) )
							{
								$cat_auth_action[$cat_id] = 'update';
							}
						}
						else if ( ( empty( $auth_access[$cat_id]['auth_mod'] ) &&
									( isset( $auth_access[$cat_id][$auth_field] ) && $change_acl_list[$cat_id][$auth_field] == $auth_access[$cat_id][$auth_field] ) ) && $cat_auth_action[$cat_id] == 'delete' )
						{
							$cat_auth_action[$cat_id] = 'update';
						}
					}
				}
			}

			// Checks complete, make updates to DB
			$delete_sql = '';
			while ( list( $cat_id, $u_action ) = @each( $cat_auth_action ) )
			{
				if ( $u_action == 'delete' )
				{
					$delete_sql .= ( ( $delete_sql != '' ) ? ', ' : '' ) . $cat_id;
				}
				else
				{
					if ( $u_action == 'insert' )
					{
						$sql_field = '';
						$sql_value = '';
						while ( list( $auth_type, $value ) = @each( $update_acl_status[$cat_id] ) )
						{
							$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . $auth_type;
							$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . $value;
						}
						$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . 'auth_mod';
						$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . ( ( !isset( $update_mod_status[$cat_id] ) ) ? 0 : $update_mod_status[$cat_id] );

						$sql = "INSERT INTO " . PA_AUTH_ACCESS_TABLE . " (cat_id, group_id, $sql_field)
									VALUES ($cat_id, $group_id, $sql_value)";
					}
					else
					{
						$sql_values = '';
						while ( list( $auth_type, $value ) = @each( $update_acl_status[$cat_id] ) )
						{
							$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . $auth_type . ' = ' . $value;
						}
						$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . 'auth_mod = ' . ( ( !isset( $update_mod_status[$cat_id] ) ) ? 0 : $update_mod_status[$cat_id] );

						$sql = "UPDATE " . PA_AUTH_ACCESS_TABLE . "
							SET $sql_values
							WHERE group_id = $group_id
							AND cat_id = $cat_id";
					}
					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						message_die( GENERAL_ERROR, "Couldn't update private forum permissions", "", __LINE__, __FILE__, $sql );
					}
				}
			}

			if ( $delete_sql != '' )
			{
				$sql = "DELETE FROM " . PA_AUTH_ACCESS_TABLE . "
					WHERE group_id = $group_id
					AND cat_id IN ($delete_sql)";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't delete permission entries", "", __LINE__, __FILE__, $sql );
				}
			}

			$l_auth_return = ( $mode == 'user' ) ? $lang['Click_return_userauth'] : $lang['Click_return_groupauth'];
			$message = $lang['Auth_updated'] . '<br /><br />' . sprintf( $l_auth_return, '<a href="' . append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=$mode" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );
			message_die( GENERAL_MESSAGE, $message );
		}
		elseif ( $request->is_set_post('submit') && ( ( $mode == 'global_user' && $user_id ) || ( $mode == 'global_group' && $group_id ) ) )
		{
			if ( $mode == 'global_user' )
			{		
				switch (PORTAL_BACKEND)
				{
					case 'internal':
					case 'phpbb2':
					
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug
							WHERE ug.user_id = $user_id
								AND g.group_id = ug.group_id
								AND g.group_single_user = '1'";						
							
					break;

					case 'phpbb3':
					
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g
								LEFT JOIN " . USER_GROUP_TABLE . " ug ON (ug.group_id = g.group_id)
							WHERE ug.user_id = " . $user_id . "
							ORDER BY g.group_type DESC, g.group_id DESC";						
							
					break;
				}								
					
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}

			$change_acl_list = array();
			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$auth_field = $global_auth_fields[$j];
				$change_acl_list[$auth_field] = $_POST['private_' . $auth_field];
			}

			$sql = ( $mode == 'global_user' ) ? "SELECT aa.* FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id AND g.group_single_user = " . true . " AND aa.cat_id = '0'" : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id AND cat_id = '0'";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = '';
			if ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access = $row;
			}
			$db->sql_freeresult( $result );

			$global_auth_action = array();
			$update_acl_status = array();

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$auth_field = $global_auth_fields[$j];

				if ( $pafiledb_config[$auth_field] == AUTH_ACL && isset( $change_acl_list[$auth_field] ) )
				{
					if ( ( !$this->is_moderator( $group_id ) &&
								( isset( $auth_access[$auth_field] ) && $change_acl_list[$auth_field] != $auth_access[$auth_field] ) ||
								( !isset( $auth_access[$cat_id][$auth_field] ) && !empty( $change_acl_list[$auth_field] ) ) )
							)
					{
						$update_acl_status[$auth_field] = $change_acl_list[$auth_field];

						if ( isset( $auth_access[$auth_field] ) && empty( $update_acl_status[$auth_field] ) && $global_auth_action != 'insert' && $global_auth_action != 'update' )
						{
							$global_auth_action = 'delete';
						}
						else if ( !isset( $auth_access[$auth_field] ) && !( $global_auth_action == 'delete' && empty( $update_acl_status[$auth_field] ) ) )
						{
							$global_auth_action = 'insert';
						}
						else if ( isset( $auth_access[$auth_field] ) && !empty( $update_acl_status[$auth_field] ) )
						{
							$global_auth_action = 'update';
						}
					}
					else if ( ( !$this->is_moderator( $auth_access['group_id'] ) &&
								( isset( $auth_access[$auth_field] ) && $change_acl_list[$auth_field] == $auth_access[$auth_field] ) ) && $global_auth_action == 'delete' )
					{
						$global_auth_action = 'update';
					}
				}
			}
			
			// Checks complete, make updates to DB
			$delete_sql = 0;

			if ( $global_auth_action == 'delete' )
			{
				$delete_sql = 1;
			}
			else
			{
				if ( $global_auth_action == 'insert' )
				{
					$sql_field = '';
					$sql_value = '';
					while ( list( $auth_type, $value ) = @each( $update_acl_status ) )
					{
						$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . $auth_type;
						$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . $value;
					}
					$sql = "INSERT INTO " . PA_AUTH_ACCESS_TABLE . " (cat_id, group_id, $sql_field)
								VALUES (0, $group_id, $sql_value)";
				}
				else
				{
					$sql_values = '';
					while ( list( $auth_type, $value ) = @each( $update_acl_status ) )
					{
						$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . $auth_type . ' = ' . $value;
					}
					$sql = "UPDATE " . PA_AUTH_ACCESS_TABLE . "
							SET $sql_values
							WHERE group_id = $group_id
							AND cat_id = 0";
				}
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't update private forum permissions", "", __LINE__, __FILE__, $sql );
				}
			}

			if ( $delete_sql )
			{
				$sql = "DELETE FROM " . PA_AUTH_ACCESS_TABLE . "
					WHERE group_id = $group_id
					AND cat_id = 0";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't delete permission entries", "", __LINE__, __FILE__, $sql );
				}
			}

			$l_auth_return = ( $mode == 'global_user' ) ? $lang['Click_return_userauth'] : $lang['Click_return_groupauth'];
			$message = $lang['Auth_updated'] . '<br /><br />' . sprintf( $l_auth_return, '<a href="' . append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=$mode" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );
			message_die( GENERAL_MESSAGE, $message );
		}
		elseif ( ( $mode == 'user' && ( $request->is_set_post('username') ) || $user_id ) || ( $mode == 'group' && $group_id ) )
		{
			if ( $request->is_set_post('username') )
			{
				$this_userdata = get_userdata( $request->variable('username', ''), true );
				if ( !is_array( $this_userdata ) )
				{
					message_die( GENERAL_MESSAGE, $lang['No_such_user'] );
				}
				$user_id = $this_userdata['user_id'];
			}

			// Front end

				
			switch (PORTAL_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
				
					$sql = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user 
						FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
						WHERE ";

					$sql .= ( $mode == 'user' ) ? "u.user_id = $user_id AND ug.user_id = u.user_id AND g.group_id = ug.group_id" : "g.group_id = $group_id AND ug.group_id = g.group_id AND u.user_id = ug.user_id";						
						
				break;

				case 'phpbb3':
				
					$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
						FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
						WHERE ";
						
					$sql .= ( $mode == 'user' ) ? "u.user_id = $user_id AND ug.user_id = u.user_id" : "ug.group_id = $group_id AND u.user_id = ug.user_id";
					
					$sql .= " ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";						
						
				break;
			}				
				
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
			}
			$ug_info = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$ug_info[] = $row;
			}
			$db->sql_freeresult( $result );
			
			switch (PORTAL_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
				
					$sql = ( $mode == 'user' ) ? "SELECT aa.*, g.group_single_user FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id AND g.group_single_user = 1" : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id";					
				break;

				case 'phpbb3':
				
					$sql_user = 'SELECT aa.*, g.group_name, g.group_id, g.group_type
						FROM ' . GROUPS_TABLE . ' g, ' . PA_AUTH_ACCESS_TABLE . ' aa
							LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
						WHERE ug.user_id = ' . $user_id . '
							AND g.group_id = aa.group_id
						ORDER BY g.group_type DESC, g.group_id DESC';

					$sql_group = 'SELECT * 
						FROM ' . PA_AUTH_ACCESS_TABLE . ' 
						WHERE group_id = ' . $group_id;				

					$sql = ( $mode == 'user' ) ? $sql_user : $sql_group;											
				break;
			}			

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = array();
			$auth_access_count = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access[$row['cat_id']][] = $row;
				$auth_access_count[$row['cat_id']]++;
			}
			$db->sql_freeresult( $result );

			$is_admin = ( $mode == 'user' ) ? ( ( $ug_info[0]['user_level'] == ADMIN && $ug_info[0]['user_id'] != ANONYMOUS ) ? 1 : 0 ) : 0;

			foreach( $this->cat_rowset as $cat_id => $cat_data )
			{
				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$key = $cat_auth_fields[$j];
					$value = $cat_data[$key];

					switch ( $value )
					{
						case AUTH_ALL:
						case AUTH_REG:
							$auth_ug[$cat_id][$key] = 1;
						break;

						case AUTH_ACL:
							$auth_ug[$cat_id][$key] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_ACL, $key, $auth_access[$cat_id], $is_admin ) : 0;
							$auth_field_acl[$cat_id][$key] = $auth_ug[$cat_id][$key];
						break;

						case AUTH_MOD:
							$auth_ug[$cat_id][$key] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_MOD, $key, $auth_access[$cat_id], $is_admin ) : 0;
						break;

						case AUTH_ADMIN:
							$auth_ug[$cat_id][$key] = $is_admin;
						break;

						default:
							$auth_ug[$cat_id][$key] = 0;
						break;
					}
				}

				// Is user a moderator?

				$auth_ug[$cat_id]['auth_mod'] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_MOD, 'auth_mod', $auth_access[$cat_id], 0 ) : 0;
			}

			$optionlist_acl_adv = array();
			$optionlist_mod = array();

			foreach( $auth_ug as $cat_id => $user_ary )
			{
				for( $k = 0; $k < count( $cat_auth_fields ); $k++ )
				{
					$field_name = $cat_auth_fields[$k];

					if ( $this->cat_rowset[$cat_id][$field_name] == AUTH_ACL )
					{
						$optionlist_acl_adv[$cat_id][$k] = '<select name="private_' . $field_name . '[' . $cat_id . ']">';

						if ( isset( $auth_field_acl[$cat_id][$field_name] ) && !( $is_admin || $user_ary['auth_mod'] ) )
						{
							if ( !$auth_field_acl[$cat_id][$field_name] )
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
							}
							else
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1" selected="selected">' . $lang['ON'] . '</option><option value="0">' . $lang['OFF'] . '</option>';
							}
						}
						else
						{
							if ( $is_admin || $user_ary['auth_mod'] )
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $lang['ON'] . '</option>';
							}
							else
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
							}
						}

						$optionlist_acl_adv[$cat_id][$k] .= '</select>';
					}
				}

				$optionlist_mod[$cat_id] = '<select name="moderator[' . $cat_id . ']">';
				$optionlist_mod[$cat_id] .= ( $user_ary['auth_mod'] ) ? '<option value="1" selected="selected">' . $lang['Is_Moderator'] . '</option><option value="0">' . $lang['Not_Moderator'] . '</option>' : '<option value="1">' . $lang['Is_Moderator'] . '</option><option value="0" selected="selected">' . $lang['Not_Moderator'] . '</option>';
				$optionlist_mod[$cat_id] .= '</select>';
			}
			$this->admin_display_cat_auth_ug();

			if ( $mode == 'user' )
			{
				$t_username = $ug_info[0]['username'];
			}
			else
			{
				$t_groupname = $ug_info[0]['group_name'];
			}

			$name = array();
			$id = array();
			for( $i = 0; $i < count( $ug_info ); $i++ )
			{
				if ( ( $mode == 'user' && !$ug_info[$i]['group_single_user'] ) || $mode == 'group' )
				{
					$name[] = ( $mode == 'user' ) ? $ug_info[$i]['group_name'] : $ug_info[$i]['username'];
					$id[] = ( $mode == 'user' ) ? intval( $ug_info[$i]['group_id'] ) : intval( $ug_info[$i]['user_id'] );
				}
			}

			if ( count( $name ) )
			{
				$t_usergroup_list = '';
				for( $i = 0; $i < count( $ug_info ); $i++ )
				{
					$ug = ( $mode == 'user' ) ? 'group&amp;' . POST_GROUPS_URL : 'user&amp;' . POST_USERS_URL;

					$t_usergroup_list .= ( ( $t_usergroup_list != '' ) ? ', ' : '' ) . '<a href="' . mx_append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=$ug=" . $id[$i] ) . '">' . $name[$i] . '</a>';
				}
			}
			else
			{
				$t_usergroup_list = $lang['None'];
			}

			for( $i = 0; $i < count( $cat_auth_fields ); $i++ )
			{
				$cell_title = $field_names[$cat_auth_fields[$i]];

				$template->assign_block_vars( 'acltype', array( 'L_UG_ACL_TYPE' => $cell_title ) );
				$s_column_span++;
			}

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';
			$s_hidden_fields .= ( $mode == 'user' ) ? '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />' : '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
			$this->tpl_name = 'pa_auth_ug_body';
			$template->set_filenames( array( 'body' => 'pa_auth_ug_body.html' ) );

			if ( $mode == 'user' )
			{
				$template->assign_vars( array(
					'USER' => true,
					'USERNAME' => $t_username,
					'USER_LEVEL' => $lang['User_Level'],
					'USER_GROUP_MEMBERSHIPS' => $lang['Group_memberships'] . ' : ' . $t_usergroup_list
				));
			}
			else
			{
				$template->assign_vars( array(
					'USER' => false,
					'USERNAME' => $t_groupname,
					'GROUP_MEMBERSHIP' => $lang['Usergroup_members'] . ' : ' . $t_usergroup_list
				));
			}

			$template->assign_vars( array(
				'SHOW_MOD' => true,
				'L_USER_OR_GROUPNAME' => ( $mode == 'user' ) ? $lang['Username'] : $lang['Group_name'],

				'L_AUTH_TITLE' => ( $mode == 'user' ) ? $lang['Auth_Control_User'] : $lang['Auth_Control_Group'],
				'L_AUTH_EXPLAIN' => ( $mode == 'user' ) ? $lang['User_auth_explain'] : $lang['Group_auth_explain'],
				'L_MODERATOR_STATUS' => $lang['Moderator_status'],
				'L_PERMISSIONS' => $lang['Permissions'],
				'L_SUBMIT' => $lang['Submit'],
				'L_RESET' => $lang['Reset'],
				'L_CAT' => $lang['Category'],

				'U_USER_OR_GROUP' => append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage" ),

				'S_COLUMN_SPAN' => $s_column_span + 2,
				'S_AUTH_ACTION' => append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage" ),
				'S_HIDDEN_FIELDS' => $s_hidden_fields
			));
		}
		elseif ( ( $mode == 'global_user' && ( $request->is_set_post('username') || $user_id ) ) || ( $mode == 'global_group' && $group_id ) )
		{
			if ( $request->is_set_post('username') )
			{
				$this_userdata = get_userdata( $request->variable('username', ''), true );
				if ( !is_array( $this_userdata ) )
				{
					message_die( GENERAL_MESSAGE, $lang['No_such_user'] );
				}
				$user_id = $this_userdata['user_id'];
			}

			// Front end
			if ( $mode == 'global_user' )
			{		
				switch (PORTAL_BACKEND)
				{
					case 'internal':

					case 'phpbb2':
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug
							WHERE ug.user_id = $user_id
								AND g.group_id = ug.group_id
								AND g.group_single_user = '1'";						
					break;

					case 'phpbb3':
					
						$sql = "SELECT g.group_id
							FROM " . GROUPS_TABLE . " g
								LEFT JOIN " . USER_GROUP_TABLE . " ug ON (ug.group_id = g.group_id)
							WHERE ug.user_id = " . $user_id . "
							ORDER BY g.group_type DESC, g.group_id DESC";						
					break;
				}								
					
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}

			switch (PORTAL_BACKEND)
			{
				case 'internal':

				case 'phpbb2':
					$sql_user = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user 
						FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
						WHERE u.user_id = $user_id 
							AND ug.user_id = u.user_id 
							AND g.group_id = ug.group_id";
					
					$sql_group = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user 
						FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
						WHERE g.group_id = $group_id 
							AND ug.group_id = g.group_id 
							AND u.user_id = ug.user_id"; 							

				break;

				case 'phpbb3':
					$sql_user = 'SELECT u.*, g.group_name, g.group_id, g.group_type
						FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g
							LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
						WHERE u.user_id = ' . $user_id . '
							AND ug.user_id = u.user_id 
						ORDER BY g.group_type DESC, g.group_id DESC';

					$sql_group = 'SELECT u.*, g.group_name, g.group_id, g.group_type
						FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g
							LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
						WHERE g.group_id = ' . $group_id . '
							AND ug.user_id = u.user_id 
						ORDER BY g.group_type DESC, g.group_id DESC';
				break;
			}				

			$sql = ( $mode == 'global_user' ) ? $sql_user : $sql_group;				
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
			}
			$ug_info = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$ug_info[] = $row;
			}
			$db->sql_freeresult( $result );

			switch (PORTAL_BACKEND)
			{
				case 'internal':

				case 'phpbb2':
					$sql_user = "SELECT aa.*, g.group_single_user 
						FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
						WHERE ug.user_id = $user_id 
							AND g.group_id = ug.group_id 
							AND aa.group_id = ug.group_id 
							AND g.group_single_user = 1 
							AND aa.cat_id = '0'" ;		

				break;

				case 'phpbb3':
					$sql_user = "SELECT aa.*, g.group_id 
						FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u 
						WHERE ug.user_id = $user_id 
							AND u.group_id = ug.group_id
							AND g.group_id = ug.group_id 
							AND aa.group_id = ug.group_id
							AND aa.cat_id = 0
						ORDER BY g.group_type DESC, g.group_id DESC";
				break;
			}
			
			$sql_group = 'SELECT * 
				FROM ' . PA_AUTH_ACCESS_TABLE . ' 
				WHERE group_id = ' . $group_id . '
					AND cat_id = 0';
					
			$sql = ($mode == 'global_user') ? $sql_user : $sql_group;					
			
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = array();
			$auth_access_count = 0;
			if ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access = $row;
				$auth_access_count++;
			}
			$db->sql_freeresult( $result );

			$is_admin = ( $mode == 'global_user' ) ? ( ( $ug_info[0]['user_level'] == ADMIN && $ug_info[0]['user_id'] != ANONYMOUS ) ? 1 : 0 ) : 0;

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$key = $global_auth_fields[$j];
				$value = $pafiledb_config[$key];

				switch ( $value )
				{
					case AUTH_ALL:
					case AUTH_REG:
						$auth_ug[$key] = 1;
					break;

					case AUTH_ACL:
						$auth_ug[$key] = ( !empty( $auth_access_count ) ) ? $this->global_auth_check_user( AUTH_ACL, $key, $auth_access, $is_admin ) : 0;
						$auth_field_acl[$key] = $auth_ug[$key];
					break;

					case AUTH_MOD:
						$auth_ug[$key] = ( !empty( $auth_access_count ) ) ? $this->global_auth_check_user( AUTH_MOD, $key, $auth_access, $is_admin ) : 0;
					break;

					case AUTH_ADMIN:
						$auth_ug[$key] = $is_admin;
					break;

					default:
						$auth_ug[$key] = 0;
					break;
				}
			}

			for( $k = 0; $k < count( $global_auth_fields ); $k++ )
			{
				$field_name = $global_auth_fields[$k];

				if ( $pafiledb_config[$field_name] == AUTH_ACL )
				{
					$optionlist_acl_adv[$k] = '<select name="private_' . $field_name . '">';

					if ( isset( $auth_field_acl[$field_name] ) && !( $is_admin || $this->is_moderator( $group_id ) ) )
					{
						if ( !$auth_field_acl[$field_name] )
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
						}
						else
						{
							$optionlist_acl_adv[$k] .= '<option value="1" selected="selected">' . $lang['ON'] . '</option><option value="0">' . $lang['OFF'] . '</option>';
						}
					}
					else
					{
						if ( $is_admin || $this->is_moderator( $group_id ) )
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $lang['ON'] . '</option>';
						}
						else
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
						}
					}

					$optionlist_acl_adv[$k] .= '</select>';
				}
			}

			$template->assign_block_vars( 'cat_row', array(
				'CAT_NAME' => ( $mode == 'global_user' ) ? $lang['User_Global_Permissions'] : $lang['Group_Global_Permissions'],
				'IS_HIGHER_CAT' => false,
				'PRE' => '',

				'U_CAT' => append_sid( "admin_pafiledb.$phpEx?action=settings" )
			));

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$template->assign_block_vars( 'cat_row.aclvalues', array( 'S_ACL_SELECT' => $optionlist_acl_adv[$j] ) );
			}

			if ( $mode == 'global_user' )
			{
				$t_username = $ug_info[0]['username'];
			}
			else
			{
				$t_groupname = $ug_info[0]['group_name'];
			}

			$name = array();
			$id = array();
			for( $i = 0; $i < count( $ug_info ); $i++ )
			{
				if ( ( $mode == 'global_user' && !$ug_info[$i]['group_single_user'] ) || $mode == 'global_group' )
				{
					$name[] = ( $mode == 'global_user' ) ? $ug_info[$i]['group_name'] : $ug_info[$i]['username'];
					$id[] = ( $mode == 'global_user' ) ? intval( $ug_info[$i]['group_id'] ) : intval( $ug_info[$i]['user_id'] );
				}
			}

			if ( count( $name ) )
			{
				$t_usergroup_list = '';
				for( $i = 0; $i < count( $ug_info ); $i++ )
				{
					$ug = ( $mode == 'global_user' ) ? 'global_group&amp;' . POST_GROUPS_URL : 'global_user&amp;' . POST_USERS_URL;

					$t_usergroup_list .= ( ( $t_usergroup_list != '' ) ? ', ' : '' ) . '<a href="' . mx_append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage&mode=$ug=" . $id[$i] ) . '">' . $name[$i] . '</a>';
				}
			}
			else
			{
				$t_usergroup_list = $lang['None'];
			}

			for( $i = 0; $i < count( $global_auth_fields ); $i++ )
			{
				$cell_title = $global_fields_names[$global_auth_fields[$i]];

				$template->assign_block_vars( 'acltype', array( 'L_UG_ACL_TYPE' => $cell_title ) );
				$s_column_span++;
			}

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';
			$s_hidden_fields .= ( $mode == 'global_user' ) ? '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />' : '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
			$this->tpl_name = 'pa_auth_ug_body';
			$template->set_filenames( array( 'body' => 'pa_auth_ug_body.html' ) );

			if ( $mode == 'global_user' )
			{
				$template->assign_vars( array(
					'USER' => true,
					'USERNAME' => $t_username,
					'USER_LEVEL' => $lang['User_Level'],
					'USER_GROUP_MEMBERSHIPS' => $lang['Group_memberships'] . ' : ' . $t_usergroup_list
				));
			}
			else
			{
				$template->assign_vars( array(
					'USER' => false,
					'USERNAME' => $t_groupname,
					'GROUP_MEMBERSHIP' => $lang['Usergroup_members'] . ' : ' . $t_usergroup_list
				));
			}

			$template->assign_vars( array(
				'SHOW_MOD' => false,

				'L_USER_OR_GROUPNAME' => ( $mode == 'global_user' ) ? $lang['Username'] : $lang['Group_name'],

				'L_AUTH_TITLE' => ( $mode == 'global_user' ) ? $lang['Auth_Control_User'] : $lang['Auth_Control_Group'],
				'L_AUTH_EXPLAIN' => ( $mode == 'global_user' ) ? $lang['User_auth_explain'] : $lang['Group_auth_explain'],
				'L_PERMISSIONS' => $lang['Permissions'],
				'L_SUBMIT' => $lang['Submit'],
				'L_RESET' => $lang['Reset'],
				'L_CAT' => ( $mode == 'global_user' ) ? $lang['User_Global_Permissions'] : $lang['Group_Global_Permissions'],

				'U_USER_OR_GROUP' => mx_append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage" ),

				'S_COLUMN_SPAN' => $s_column_span + 1,
				'S_AUTH_ACTION' => append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage" ),
				'S_HIDDEN_FIELDS' => $s_hidden_fields
			));
		}
		else
		{
			// Select a user/group
			$this->tpl_name = ( $mode == 'user' || $mode == 'global_user' ) ? 'user_select_body' : 'auth_select_body';
			$template->set_filenames( array( 'body' => ( $mode == 'user' || $mode == 'global_user' ) ? 'user_select_body.html' : 'auth_select_body.html' ) );
			
			if ( $mode == 'user' || $mode == 'global_user' )
			{
				$template->assign_vars( array(
					'L_FIND_USERNAME' => $lang['Find_username'],
					'U_SEARCH_USER' => append_sid( $phpbb_root_path . "search.$phpEx?mode=searchuser" )
				));
			}
			else
			{			
				switch (PORTAL_BACKEND)
				{
					case 'internal':

					case 'phpbb2':
					
						// Get us all the groups
						$sql = "SELECT group_id, group_name
							FROM " . GROUPS_TABLE . "
							WHERE group_single_user <> " . true;
						if ( !( $result = $db->sql_query( $sql ) ) )
						{
							message_die( GENERAL_ERROR, "Couldn't get group list", "", __LINE__, __FILE__, $sql );
						}

						if ( $row = $db->sql_fetchrow( $result ) )
						{
							$select_list = '<select name="' . POST_GROUPS_URL . '">';
							do
							{
								$select_list .= '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
							}
							while ( $row = $db->sql_fetchrow( $result ) );
							$select_list .= '</select>';
						}
					break;

					case 'phpbb3':

						// Get us all the groups
						$sql = 'SELECT g.group_id, g.group_name, g.group_type
							FROM ' . GROUPS_TABLE . ' g
							ORDER BY g.group_type ASC, g.group_name';
						if ( !( $result = $db->sql_query( $sql ) ) )
						{
							message_die( GENERAL_ERROR, "Couldn't get group list", "", __LINE__, __FILE__, $sql );
						}

						if ( $row = $db->sql_fetchrow( $result ) )
						{
							$select_list = '<select name="' . POST_GROUPS_URL . '">';
							do
							{
								$select_list .= ($row['group_type'] == GROUP_SPECIAL) ? '<option value="' . $row['group_id'] . '">' . $user->lang['G_' . $row['group_name']] . '</option>' : '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
							}
							while ( $row = $db->sql_fetchrow( $result ) );
							$select_list .= '</select>';
						}
					break;
				}
			

				$template->assign_vars( array( 'S_AUTH_SELECT' => $select_list ) );
			}

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

			$l_type = ( $mode == 'user' || $mode == 'global_user' ) ? 'USER' : 'AUTH';

			$template->assign_vars( array(
				'L_' . $l_type . '_TITLE' => ( $mode == 'user' || $mode == 'global_user' ) ? $lang['Auth_Control_User'] : $lang['Auth_Control_Group'],
				'L_' . $l_type . '_EXPLAIN' => ( $mode == 'user' || $mode == 'global_user' ) ? $lang['User_auth_explain'] : $lang['Group_auth_explain'],
				'L_' . $l_type . '_SELECT' => ( $mode == 'user' || $mode == 'global_user' ) ? $lang['Select_a_User'] : $lang['Select_a_Group'],
				'L_LOOK_UP' => ( $mode == 'user' || $mode == 'global_user' ) ? $lang['Look_up_User'] : $lang['Look_up_Group'],

				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_' . $l_type . '_ACTION' => append_sid( "admin_pafiledb.$phpEx?action=ug_auth_manage" )
			));
		}

		//$template->pparse( 'body' );

		//$this->_pafiledb();
		//$pafiledb_cache->unload();
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sel_id
	 * @param unknown_type $use_default_option
	 * @param unknown_type $select_name
	 * @return unknown
	 */
	function get_forums( $sel_id = 0, $use_default_option = false, $select_name = 'forum_id' )
	{
		$sql = "SELECT forum_id, forum_name
			FROM " . FORUMS_TABLE;

		if ( !$result = $this->db->sql_query( $sql ) )
		{
			$this->functions->message_die( GENERAL_ERROR, "Couldn't get list of forums", "", __LINE__, __FILE__, $sql );
		}

		$forumlist = '<select name="'.$select_name.'">';

		if ( $sel_id == 0 )
		{
			$forumlist .= '<option value="0" selected >'.$this->user->lang['Select_topic_id'].'</option>';
		}

		if ( $use_default_option )
		{
			$status = $sel_id == "-1" ? "selected" : "";
			$forumlist .= '<option value="-1" '.$status.' >::'.$this->user->lang['Use_default'].'::</option>';
		}

		while ( $row = $this->db->sql_fetchrow( $result ) )
		{
			if ( $sel_id == $row['forum_id'] )
			{
				$status = "selected";
			}
			else
			{
				$status = '';
			}
			$forumlist .= '<option value="' . $row['forum_id'] . '" ' . $status . '>' . $row['forum_name'] . '</option>';
		}

		$forumlist .= '</select>';

		return $forumlist;
	}

	function pa_size_select( $select_name, $size_compare )
	{
		$size_types_text = array( $this->user->lang['Bytes'], $this->user->lang['KB'], $this->user->lang['MB'] );
		$size_types = array( 'b', 'kb', 'mb' );

		$select_field = '<select name="' . $select_name . '">';

		for ( $i = 0; $i < count( $size_types_text ); $i++ )
		{
			$selected = ( $size_compare == $size_types[$i] ) ? ' selected="selected"' : '';

			$select_field .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
		}

		$select_field .= '</select>';

		return ( $select_field );
	}

	function global_auth_check_user( $type, $key, $global_u_access, $is_admin )
	{
		$auth_user = 0;

		if ( !empty( $global_u_access ) )
		{
			$result = 0;
			switch ( $type )
			{
				case AUTH_ACL:
					$result = $global_u_access[$key];

				case AUTH_MOD:
					$result = $result || is_moderator( $global_u_access['group_id'] );

				case AUTH_ADMIN:
					$result = $result || $is_admin;
				break;
			}

			$auth_user = $auth_user || $result;
		}
		else
		{
			$auth_user = $is_admin;
		}

		return $auth_user;
	}

	function is_moderator($group_id = 0)
	{
		if (!empty($this->auth_user) && ($group_id == 0))
		{
			foreach( $this->auth_user as $cat_id => $this->auth_fields )
			{
				if ( $this->auth_fileds['auth_mod'] )
				{
					return true;
				}
			}
			return false;
		}
		elseif ($group_id !== 0)
		{		
			$sql = "SELECT *
				FROM " . $this->pa_auth_access_table . "
				WHERE group_id = $group_id
				AND auth_mod = '1'";

			if ( !($result = $this->db->sql_query($sql)) )
			{
				$this->functions->message_die(GENERAL_ERROR, "Couldn't check for moderator $sql", "", __LINE__, __FILE__, $sql);
			}
			return ($is_mod = ($this->db->sql_fetchrow($result)) ? 1 : 0);			
		}
		return false;		
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @return unknown
	 */
	function update_add_cat( $cat_id = false )
	{
		global $_POST;

		$cat_name = ( isset( $_POST['cat_name'] ) ) ? htmlspecialchars( $_POST['cat_name'] ) : '';
		$cat_desc = ( isset( $_POST['cat_desc'] ) ) ? htmlspecialchars( $_POST['cat_desc'] ) : '';
		$cat_parent = ( isset( $_POST['cat_parent'] ) ) ? intval( $_POST['cat_parent'] ) : 0;
		$cat_allow_file = ( isset( $_POST['cat_allow_file'] ) ) ? intval( $_POST['cat_allow_file'] ) : 0;

		$cat_use_comments = ( isset( $_POST['cat_allow_comments'] ) ) ? intval( $_POST['cat_allow_comments'] ) : 0;
		$cat_internal_comments = ( isset( $_POST['internal_comments'] ) ) ? intval( $_POST['internal_comments'] ) : 0;
		$cat_autogenerate_comments = ( isset( $_POST['autogenerate_comments'] ) ) ? intval( $_POST['autogenerate_comments'] ) : 0;
		$comments_forum_id = intval( $_POST['comments_forum_id'] );

		$cat_show_pretext = ( isset( $_POST['show_pretext'] ) ) ? intval( $_POST['show_pretext'] ) : 0;

		$cat_use_ratings = ( isset( $_POST['cat_allow_ratings'] ) ) ? intval( $_POST['cat_allow_ratings'] ) : 0;

		$cat_notify = ( isset( $_POST['notify'] ) ) ? intval( $_POST['notify'] ) : 0;
		$cat_notify_group = ( isset( $_POST['notify_group'] ) ) ? intval( $_POST['notify_group'] ) : 0;

		if ( empty( $cat_name ) )
		{
			$this->error[] = $this->user->lang['Cat_name_missing'];
		}

		if ( $cat_parent )
		{
			if ( !$this->cat_rowset[$cat_parent]['cat_allow_file'] && !$cat_allow_file )
			{
				$this->error[] = $this->user->lang['Cat_conflict'];
			}
		}

		if ( sizeof( $this->error ) )
		{
			return;
		}

		$cat_name = str_replace( "\'", "''", $cat_name );
		$cat_desc = str_replace( "\'", "''", $cat_desc );

		if ( !$cat_id )
		{
			$cat_order = 0;
			if ( !empty( $this->subcat_rowset[$cat_parent] ) )
			{
				foreach( $this->subcat_rowset[$cat_parent] as $cat_data )
				{
					if ( $cat_order < $cat_data['cat_order'] )
					{
						$cat_order = $cat_data['cat_order'];
					}
				}
			}

			$cat_order += 10;

			$sql = "INSERT INTO " . PA_CATEGORY_TABLE . " (cat_name, cat_desc, cat_parent, parents_data, cat_order, cat_allow_file, cat_allow_ratings, cat_allow_comments, internal_comments, autogenerate_comments, comments_forum_id, show_pretext, notify, notify_group)
				VALUES('$cat_name', '$cat_desc', $cat_parent, '', $cat_order, $cat_allow_file, $cat_use_ratings, $cat_use_comments, $cat_internal_comments, $cat_autogenerate_comments, $comments_forum_id, $cat_show_pretext, $cat_notify, $cat_notify_group)";
				
			if ( !( $this->db->sql_query( $sql ) ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Couldn\'t add a new category', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_name = '$cat_name', cat_desc = '$cat_desc', cat_parent = $cat_parent, cat_allow_file = $cat_allow_file, cat_allow_ratings = $cat_use_ratings, cat_allow_comments = $cat_use_comments, internal_comments = $cat_internal_comments, autogenerate_comments = $cat_autogenerate_comments, comments_forum_id = $comments_forum_id, show_pretext = $cat_show_pretext, notify = $cat_notify, notify_group = $cat_notify_group
				WHERE cat_id = $cat_id";

			if ( !( $this->db->sql_query( $sql ) ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Couldn\'t Edit this category', '', __LINE__, __FILE__, $sql );
			}

			if ( $cat_parent != $this->cat_rowset[$cat_id]['cat_parent'] )
			{
				$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );
				$this->reorder_cat( $cat_parent );
			}
			$this->modified( true );
		}

		if ( $cat_id )
		{
			return $cat_id;
		}
		else
		{
			return $this->db->sql_nextid();
		}
	}



	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @param unknown_type $file_mode
	 * @param unknown_type $to_cat
	 */
	function delete_subcat( $cat_id, $file_mode = 'delete', $to_cat = false )
	{
		if ( empty( $this->subcat_rowset[$cat_id] ) || count( $this->subcat_rowset[$cat_id] ) <= 0 )
		{
			return;
		}

		foreach( $this->subcat_rowset[$cat_id] as $sub_cat_id => $subcat_data )
		{
			$this->delete_subcat( $sub_cat_id, $file_mode, $to_cat );

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . "
				WHERE cat_id = $sub_cat_id";

			if ( !( $this->db->sql_query( $sql ) ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			if ( $file_mode == 'delete' )
			{
				$this->delete_items( $sub_cat_id, 'category' );
			}
			else
			{
				$this->move_items( $sub_cat_id, $to_cat );
			}
		}
		$this->modified( true );
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $from_cat
	 * @param unknown_type $to_cat
	 */
	function move_subcat( $from_cat, $to_cat )
	{
		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
			SET cat_parent = $to_cat
			WHERE cat_parent = $from_cat";

		if ( !( $this->db->sql_query( $sql ) ) )
		{
			$this->functions->message_die( GENERAL_ERROR, 'Couldnt move Sub Category', '', __LINE__, __FILE__, $sql );
		}
		$this->modified( true );
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_parent
	 */
	function reorder_cat( $cat_parent )
	{
		$sql = 'SELECT cat_id, cat_order
			FROM ' . PA_CATEGORY_TABLE . "
			WHERE cat_parent = $cat_parent
			ORDER BY cat_order ASC";

		if ( !$result = $this->db->sql_query( $sql ) )
		{
			$this->functions->message_die( GENERAL_ERROR, 'Could not get list of Categories', '', __LINE__, __FILE__, $sql );
		}

		$i = 10;
		while ( $row = $this->db->sql_fetchrow( $result ) )
		{
			$cat_id = $row['cat_id'];

			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
					SET cat_order = $i
					WHERE cat_id = $cat_id";
			if ( !$this->db->sql_query( $sql ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Could not update order fields', '', __LINE__, __FILE__, $sql );
			}
			$i += 10;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 */
	function order_cat( $cat_id )
	{
		global $_GET;

		$move = ( isset( $_GET['move'] ) ) ? intval( $_GET['move'] ) : 15;
		$cat_parent = $this->cat_rowset[$cat_id]['cat_parent'];

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_order = cat_order + $move
				WHERE cat_id = $cat_id";

		if ( !$result = $this->db->sql_query( $sql ) )
		{
			$this->functions->message_die( GENERAL_ERROR, 'Could not change category order', '', __LINE__, __FILE__, $sql );
		}

		$this->reorder_cat( $cat_parent );
		$this->init();
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function file_mainenance()
	{
		return false;
	}	
}
