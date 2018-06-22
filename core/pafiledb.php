<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

use Symfony\Component\DependencyInjection\ContainerInterface;

// SQL codes phpBB2
@define('BEGIN_TRANSACTION', 1);
@define('END_TRANSACTION', 2);

// Error codes (from phpbb2)
@define('GENERAL_MESSAGE', 200);
@define('GENERAL_ERROR', 202);
@define('CRITICAL_MESSAGE', 203);
@define('CRITICAL_ERROR', 204);

// Switches
@define( 'PAFILEDB_DEBUG', 1 ); // Pafiledb Mod Debugging on
@define( 'PAFILEDB_QUERY_DEBUG', 1 );
@define( 'PA_ROOT_CAT', 0 );
@define( 'PA_CAT_ALLOW_FILE', 1 );
@define( 'PA_AUTH_LIST_ALL', 0 );
@define( 'PA_AUTH_ALL', 0 );
@define( 'FILE_PINNED', 1 );
@define( 'PA_AUTH_VIEW', 1 );
@define( 'PA_AUTH_READ', 2 );
@define( 'PA_AUTH_VIEW_FILE', 3 );
@define( 'PA_AUTH_UPLOAD', 4 );
@define( 'PA_AUTH_DOWNLOAD', 5 );
@define( 'PA_AUTH_RATE', 6 );
@define( 'PA_AUTH_EMAIL', 7 );
@define( 'PA_AUTH_COMMENT_VIEW', 8 );
@define( 'PA_AUTH_COMMENT_POST', 9 );
@define( 'PA_AUTH_COMMENT_EDIT', 10 );
@define( 'PA_AUTH_COMMENT_DELETE', 11 );

@define('MXBB_MODULE', false); 
@define('MXBB_27x', false);

@define('MX_BUTTON_IMAGE'	, 10);
@define('MX_BUTTON_TEXT'	, 20);
@define('MX_BUTTON_GENERIC'	, 30);

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

class pafiledb extends \orynider\pafiledb\core\pafiledb_auth
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;
	
	/** @var \phpbb\auth\auth */
	protected $auth;	
	
	/** @var ContainerInterface */
	protected $container;	
	
	/** @var \phpbb\cache\cache */
	protected $cache;	

	/** @var \orynider\pafiledb\core\functions_cache */
	protected $functions_cache;
	
	/** @var \orynider\pafiledb\core\templates */
	protected $templates;	
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var string */
	protected $php_ext;

	/** @var string phpBB root path */
	protected $root_path;
	
	var $total_cat = 0;	
	
	/** @var string */	
	var $cat_rowset = array();
	
	/** @var string */	
	var $subcat_rowset = array();
	
	/** @var string */	
	var $comments = array();
	
	/** @var string */	
	var $ratings = array();
	
	/** @var string */	
	var $information = array();
	
	/** @var string */	
	var $notification = array();

	var $modified = false;
	var $error = array();

	var $page_title = '';
	var $jumpbox = '';
	var $auth_can_list = '';
	var $navigation = '';

	var $debug = false; // Toggle debug output on/off
	var $debug_msg = array();	

	/**
	* The database tables
	*
	* @var string
	*/
	protected $pa_files_table;

	protected $pa_cat_table;

	protected $pa_config_table;
	
	protected $pa_votes_table;
	
	protected $pa_comments_table;
	
	protected $pa_license_table;	
	
	/**
	* Constructor
	*
	* @param \phpbb\template\template		 		$template
	* @param \phpbb\user						$user
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\controller\helper		 		$helper
	* @param \phpbb\request\request		 		$request
	* @param \phpbb\auth\auth			 		$auth	
	* @param \phpbb\cache\service					$cache

	
	* @param \orynider\pafiledb\core\functions_cache		$functions_cache		
	* @param \phpbb\config\config					$config
	* @param ContainerInterface                    			$container		
	* @param \phpbb\pagination					$pagination
	* @param \phpbb\extension\manager 				$extension_manager
	* @param								$php_ext
	* @param								$root_path
	* @param								$pa_files_table
	* @param								$pa_cat_table
	* @param								$pa_config_table
	* @param								$pa_votes_table
	* @param								$pa_comments_table
	* @param								$pa_license_table	
	*
	*/
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\auth\auth $auth,			
		\phpbb\cache\driver\driver_interface $cache,
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,
		\orynider\pafiledb\core\pafiledb_templates $pafiledb_templates,		
		\phpbb\config\config $config,		
		\phpbb\pagination $pagination,
		\phpbb\extension\manager $extension_manager, ContainerInterface $container,
		$php_ext, 
		$root_path,
		$pa_files_table,
		$pa_cat_table,
		$pa_config_table, 
		$pa_votes_table,
		$pa_comments_table,
		$pa_license_table,
		$pa_auth_access_table)
	{
		$this->template 			= $template;
		$this->user 				= $user;
		$this->db 					= $db;
		$this->helper 				= $helper;
		$this->request 				= $request;
		$this->auth 				= $auth;		
		$this->container 			= $container;
		$this->pafiledb_cache 		= $pafiledb_cache;
		$this->templates 			= $pafiledb_templates;
		$this->cache 				= $cache;		
		$this->config 				= $config;	
		$this->pagination 			= $pagination;
		$this->extension_manager	= $extension_manager;
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		$this->mx_root_path 		= $root_path;		
		$this->phpbb_root_path 		= $root_path;		
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_config_table 		= $pa_config_table;
		$this->pa_votes_table 		= $pa_votes_table;
		$this->pa_comments_table 	= $pa_comments_table;		
		$this->pa_license_table 	= $pa_license_table;		
		$this->pa_auth_access_table = $pa_auth_access_table;		
		
		$this->ext_name 			= $this->request->variable('ext_name', 'orynider/pafiledb');
		$this->module_root_path		= $this->ext_path = $extension_manager->get_extension_path($this->ext_name, true);
		
		$this->auth_fields = array( 'auth_view', 'auth_read', 'auth_view_file', 'auth_edit_file', 'auth_delete_file', 'auth_upload', 'auth_download', 'auth_rate', 'auth_email', 'auth_view_comment', 'auth_post_comment', 'auth_edit_comment', 'auth_delete_comment', 'auth_approval', 'auth_approval_edit' );
		$this->auth_fields_global = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );
				
		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
		if (!defined('PHPBB_USE_BOARD_URL_PATH'))
		{
			define('PHPBB_USE_BOARD_URL_PATH', true);
		}
		
		global $debug;

		unset($this->cat_rowset);
		unset($this->subcat_rowset);
		unset($this->comments);
		unset($this->ratings);
		unset($this->information);
		unset($this->notification);

		// Read out config values
		$pafiledb_config = $this->config_values();
		$this->backend = $this->confirm_backend();		
		
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY cat_order ASC';

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die(GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql);
		}
		
		$cat_rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$this->auth($cat_rowset);
		//print_r($this->auth_user);
		for( $i = 0; $i < $cats = count($cat_rowset); $i++ )
		{
			//print_r($this->auth_user[$cat_rowset[$i]['cat_id']]);
			if ( $this->auth->acl_get('u_pa_files_download') || $this->auth_user[$cat_rowset[$i]['cat_id']]['auth_view'] )
			{
				$this->cat_rowset[$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->subcat_rowset[$cat_rowset[$i]['cat_parent']][$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->total_cat++;

				//
				// Comments
				// Note: some settings are category dependent, but may use default config settings
				//
				$this->comments[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_comments'] == -1 ? ($pafiledb_config['use_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_comments'] == 1 ? true : false );

				switch($this->backend)
				{
					case 'internal':
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = false; // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = 0; // phpBB target forum (only used for phpBB comments)
					break;

					default:
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = $cat_rowset[$i]['internal_comments'] == -1 ? ($pafiledb_config['internal_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['internal_comments'] == 1 ? true : false ); // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = $cat_rowset[$i]['autogenerate_comments'] == -1 ? ($pafiledb_config['autogenerate_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['autogenerate_comments'] == 1 ? true : false ); // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = $cat_rowset[$i]['comments_forum_id'] < 1 ? ( intval($pafiledb_config['comments_forum_id']) ) : ( intval($cat_rowset[$i]['comments_forum_id']) ); // phpBB target forum (only used for phpBB comments)
					break;
				}

				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
					$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // autocreate comments when updated
				}
				
				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
					$this->message_die(GENERAL_ERROR, 'Init Failure, phpBB comments with no target forum_id :( <br> Category: ' . $cat_rowset[$i]['cat_name'] . ' Forum_id: ' . $this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']);
				}
				
				//
				// Ratings
				//
				$this->ratings[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_ratings'] == -1 ? ($pafiledb_config['use_ratings'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_ratings'] == 1 ? true : false );

				//
				// Information
				//
				$this->information[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['show_pretext'] == -1 ? ($pafiledb_config['show_pretext'] == 1 ? true : false ) : ( $cat_rowset[$i]['show_pretext'] == 1 ? true : false ); // phpBB or internal ratings

				//
				// Notification
				//
				$this->notification[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['notify'] == -1 ? (intval($pafiledb_config['notify'])) : ( intval($cat_rowset[$i]['notify']) ); // -1, 0, 1, 2
				$this->notification[$cat_rowset[$i]['cat_id']]['notify_group'] = $cat_rowset[$i]['notify_group'] == -1 || $cat_rowset[$i]['notify_group'] == 0 ? (intval($pafiledb_config['notify_group'])) : ( intval($cat_rowset[$i]['notify_group']) ); // Group_id
			}
		}
		//print_r($this->cat_rowset);		
	}	
	
	public function pafiledb()
	{
		global $mx_cache, $pafiledb_cache, $mx_request_vars, $template, $mx_user, $db;  
		global $board_config, $phpEx, $phpbb_root_path, $mx_root_path, $module_root_path;		
			
		$this->template 			= $template;
		$this->user 				= $mx_user;
		$this->db 					= $db;
		$this->helper 				= $mx_cache;
		$this->request 				= $mx_request_vars;
		$this->container 			= $mx_cache;		
		$this->cache 				= $mx_cache;
		$this->pafiledb_cache 		= $pafiledb_cache;		
		$this->config 				= $config;
		$this->pagination 			= $mx_cache;
		$this->extension_manager	= $mx_cache;
		$this->php_ext 				= $phpEx;
		$this->root_path 			= $mx_root_path;
		$this->mx_root_path 		= $mx_root_path;
		$this->module_root_path 	= $module_root_path;		
		$this->phpbb_root_path 		= $phpbb_root_path;		
		$this->pa_files_table 		= PA_FILES_TABLE;
		$this->pa_cat_table 		= PA_CAT_TABLE;
		$this->pa_config_table 		= PA_CONFIG_TABLE;
		$this->pa_votes_table 		= PA_VOTES_TABLE;		
		$this->pa_comments_table 	= PA_COMMENTS_TABLE;
		$this->pa_license_table 	= PA_LICENSE_TABLE;
		$this->pa_auth_access_table = PA_AUTH_ACCESS_TABLE;			
		
		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
		if (!defined('PHPBB_USE_BOARD_URL_PATH'))
		{
			define('PHPBB_USE_BOARD_URL_PATH', true);
		}

		global $debug;

		unset($this->cat_rowset);
		unset($this->subcat_rowset);
		unset($this->comments);
		unset($this->ratings);
		unset($this->information);
		unset($this->notification);
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		$this->backend = $this->confirm_backend();		
		
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY cat_order ASC';

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die(GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql);
		}
		$cat_rowset = $this->db->sql_fetchrowset($result);

		$this->db->sql_freeresult($result);

		$this->auth($cat_rowset);

		for( $i = 0; $i < count( $cat_rowset ); $i++ )
		{
			if ( $this->auth_user[$cat_rowset[$i]['cat_id']]['auth_view'] )
			{
				$this->cat_rowset[$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->subcat_rowset[$cat_rowset[$i]['cat_parent']][$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->total_cat++;

				//
				// Comments
				// Note: some settings are category dependent, but may use default config settings
				//
				$this->comments[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_comments'] == -1 ? ($pafiledb_config['use_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_comments'] == 1 ? true : false );

				switch($portal_config['portal_backend'])
				{
					case 'internal':
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = false; // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = 0; // phpBB target forum (only used for phpBB comments)
					break;

					default:
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = $cat_rowset[$i]['internal_comments'] == -1 ? ($pafiledb_config['internal_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['internal_comments'] == 1 ? true : false ); // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = $cat_rowset[$i]['autogenerate_comments'] == -1 ? ($pafiledb_config['autogenerate_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['autogenerate_comments'] == 1 ? true : false ); // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = $cat_rowset[$i]['comments_forum_id'] < 1 ? ( intval($pafiledb_config['comments_forum_id']) ) : ( intval($cat_rowset[$i]['comments_forum_id']) ); // phpBB target forum (only used for phpBB comments)
					break;
				}

				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
					$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // autocreate comments when updated
				}
				
				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
					$this->message_die(GENERAL_ERROR, 'Init Failure, phpBB comments with no target forum_id :( <br> Category: ' . $cat_rowset[$i]['cat_name'] . ' Forum_id: ' . $this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']);
				}
				
				//
				// Ratings
				//
				$this->ratings[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_ratings'] == -1 ? ($pafiledb_config['use_ratings'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_ratings'] == 1 ? true : false );

				//
				// Information
				//
				$this->information[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['show_pretext'] == -1 ? ($pafiledb_config['show_pretext'] == 1 ? true : false ) : ( $cat_rowset[$i]['show_pretext'] == 1 ? true : false ); // phpBB or internal ratings

				//
				// Notification
				//
				$this->notification[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['notify'] == -1 ? (intval($pafiledb_config['notify'])) : ( intval($cat_rowset[$i]['notify']) ); // -1, 0, 1, 2
				$this->notification[$cat_rowset[$i]['cat_id']]['notify_group'] = $cat_rowset[$i]['notify_group'] == -1 || $cat_rowset[$i]['notify_group'] == 0 ? (intval($pafiledb_config['notify_group'])) : ( intval($cat_rowset[$i]['notify_group']) ); // Group_id
			}
		}
	}

	/**
	 * Prepare data.
	 *
	 */
	function init()
	{
		global $debug;
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		$this->backend = $this->confirm_backend();	
		
		unset( $this->cat_rowset );
		unset( $this->subcat_rowset );
		unset( $this->comments );
		unset( $this->ratings );
		unset( $this->information );
		unset( $this->notification );

		$sql = 'SELECT *
			FROM ' . $this->pa_category_table . '
			ORDER BY cat_order ASC';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}
		$cat_rowset = $this->db->sql_fetchrowset( $result );

		$this->db->sql_freeresult( $result );

		$this->auth($cat_rowset);
		//print_r($this->auth_user);
		for( $i = 0; $i < count( $cat_rowset ); $i++ )
		{
			if ( $this->auth_user[$cat_rowset[$i]['cat_id']]['auth_view'] )
			{
				$this->cat_rowset[$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->subcat_rowset[$cat_rowset[$i]['cat_parent']][$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->total_cat++;

				//
				// Comments
				// Note: some settings are category dependent, but may use default config settings
				//
				$this->comments[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_comments'] == -1 ? ($pafiledb_config['use_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_comments'] == 1 ? true : false );

				switch($portal_config['portal_backend'])
				{
					case 'internal':
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = false; // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = 0; // phpBB target forum (only used for phpBB comments)
					break;

					default:
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = $cat_rowset[$i]['internal_comments'] == -1 ? ($pafiledb_config['internal_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['internal_comments'] == 1 ? true : false ); // phpBB or internal comments
						$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = $cat_rowset[$i]['autogenerate_comments'] == -1 ? ($pafiledb_config['autogenerate_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['autogenerate_comments'] == 1 ? true : false ); // autocreate comments when updated
						$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = $cat_rowset[$i]['comments_forum_id'] < 1 ? ( intval($pafiledb_config['comments_forum_id']) ) : ( intval($cat_rowset[$i]['comments_forum_id']) ); // phpBB target forum (only used for phpBB comments)
					break;
				}

				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
						$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = true; // autocreate comments when updated
				}
				
				if ($this->comments[$cat_rowset[$i]['cat_id']]['activated'] && !$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
				{
					$this->message_die(GENERAL_ERROR, 'Init Failure, phpBB comments with no target forum_id :( <br> Category: ' . $cat_rowset[$i]['cat_name'] . ' Forum_id: ' . $this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']);
				}
				
				//
				// Ratings
				//
				$this->ratings[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_ratings'] == -1 ? ($pafiledb_config['use_ratings'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_ratings'] == 1 ? true : false );

				//
				// Information
				//
				$this->information[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['show_pretext'] == -1 ? ($pafiledb_config['show_pretext'] == 1 ? true : false ) : ( $cat_rowset[$i]['show_pretext'] == 1 ? true : false ); // phpBB or internal ratings

				//
				// Notification
				//
				$this->notification[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['notify'] == -1 ? (intval($pafiledb_config['notify'])) : ( intval($cat_rowset[$i]['notify']) ); // -1, 0, 1, 2
				$this->notification[$cat_rowset[$i]['cat_id']]['notify_group'] = $cat_rowset[$i]['notify_group'] == -1 || $cat_rowset[$i]['notify_group'] == 0 ? (intval($pafiledb_config['notify_group'])) : ( intval($cat_rowset[$i]['notify_group']) ); // Group_id
			}
		}
	}
	
	/**
	 * Clean up
	 *
	 */
	function _pafiledb()
	{
		if ( $this->modified )
		{
			$this->sync_all();
		}
	}
	
	/**
	 * Confirm Forum Backend Name
	 *
	* @return $backend
	 */
	function confirm_backend($backend_name = true)
	{
		if (isset($this->config['version'])) 
		{
			if ($this->config['version']  >= '4.0.0')
			{			
				$this->backend = 'phpbb4';
			}		
			if (($this->config['version']  >= '3.3.0') && ($this->config['version'] < '4.0.0'))
			{			
				$this->backend = 'proteus';
			}
			if (($this->config['version']  >= '3.2.0') && ($this->config['version'] < '3.3.0'))
			{			
				$this->backend = 'rhea';
			}
			if (($this->config['version']  >= '3.1.0') && ($this->config['version'] < '3.2.0'))
			{			
				$this->backend = 'ascraeus';
			}
			if (($this->config['version']  >= '3.0.0') && ($this->config['version'] < '3.1.0'))
			{			
				$this->backend = 'olympus';
			}
			if (($this->config['version']  >= '2.0.0') && ($this->config['version'] < '3.0.0'))
			{			
				$this->this->backend = 'phpbb2';
			}
			if (($this->config['version']  >= '1.0.0') && ($this->config['version'] < '2.0.0'))
			{			
				$this->backend = 'phpbb';
			}			
		}
		else if (isset($this->config['portal_backend']))
		{			
			$this->backend = $this->config['portal_backend'];
		}
		else
		{			
			$this->backend = 'internal';
		}
		
		$this->is_phpbb20	= phpbb_version_compare($this->config['version'], '2.0.0@dev', '>=') && phpbb_version_compare($this->config['version'], '3.0.0@dev', '<');		
		$this->is_phpbb30	= phpbb_version_compare($this->config['version'], '3.0.0@dev', '>=') && phpbb_version_compare($this->config['version'], '3.1.0@dev', '<');		
		$this->is_phpbb31	= phpbb_version_compare($this->config['version'], '3.1.0@dev', '>=') && phpbb_version_compare($this->config['version'], '3.2.0@dev', '<');
		$this->is_phpbb32	= phpbb_version_compare($this->config['version'], '3.2.0@dev', '>=') && phpbb_version_compare($this->config['version'], '3.3.0@dev', '<');		
		$this->is_phpbb33	= phpbb_version_compare($this->config['version'], '3.3.0@dev', '>=') && phpbb_version_compare($this->config['version'], '3.4.0@dev', '<');		
		
		$this->is_block = isset($this->config['portal_backend']) ? true : false;
		
		if ($this->config['version'] < '3.1.0')
		{			
			define('EXT_TABLE',	$table_prefix . 'ext');
		}		
		
		if ($backend_name == true)
		{			
			return $this->backend;
		}	
	}
	
	/**
	* Returns select group 
	* backend specific
	* This can be personalised 
	* for every version of forums
	*
	* @return $sql
	*/
	function generate_group_select_sql()
	{
		$this->backend = $this->confirm_backend(true);	
		
		switch ($this->backend)
		{
			case 'phpbb2':			
			case 'internal':
				$sql = "SELECT group_id, group_name
						FROM " . GROUPS_TABLE . "
						WHERE group_single_user <> " . TRUE . "
						ORDER BY group_name ASC";			
			break;			
			
			case 'phpbb3':
			case 'olympus':	
			case 'ascraeus':
			case 'proteus':
			default:
				// Get us all the groups exept bots and guests
				$sql = "SELECT g.group_id, g.group_name, g.group_type
					FROM " . GROUPS_TABLE . " g
					WHERE g.group_name NOT IN ('BOTS', 'GUESTS')
					ORDER BY g.group_type ASC, g.group_name";				
			break;			
		}		
		return $sql;				
	}
	
	/**
	 * Get groups
	 *
	 * @param unknown_type $sel_id
	 * @param unknown_type $field_entry
	 * @param unknown_type $group_rowset
	 * @return unknown
	 */
	function get_groups($sel_id, $field_entry = 'auth_view_group', $group_rowset = array())
	{
		$group_rowset = !empty($group_rowset) ? $group_rowset : $this->cache->get('_group_rowset_' . $this->user->data['user_id']);

		if (!isset($group_rowset))
		{
			$group_rowset = array();	 	
	 	}		
		
		if (empty($group_rowset))
	 	{
	 		$sql = $this->generate_group_select_sql();
			
			if( !($result = $this->db->sql_query($sql)) )
			{
				$this->message_die(GENERAL_ERROR, "Couldn't get list of groups", '', __LINE__, __FILE__, $sql);
			}
			
			$group_rowset = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
			
			// save data in cache for 60 seconds
			$this->cache->put('_group_rowset_' . $this->user->data['user_id'], $group_rowset, 60);			
	 	}

		$grouplist = '<select name="' . $field_entry . '">';
		$grouplist .= '<option value="0">' . $this->user->lang('Select_group') . '</option>';

		foreach($group_rowset as $key => $row)
		{
			$selected = ( $sel_id == $row['group_id'] ? ' selected="selected"' : '' );
			$grouplist .= '<option value="' . $row['group_id'] . '"' . $selected . '>' . $row['group_name'] . '</option>';
		}

		$grouplist .= '</select>';
		return $grouplist;
	}	
	
	/**
	* Create the select categories list
	*/
	public function make_cat_select($select_id = false, $ignore_id = false, $dm_video = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
	{
		// No permissions yet
		$acl = ($ignore_acl) ? '' : (($only_acl_post) ? 'f_post' : array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel'));

		// This query is the same as the jumpbox query
		$sql = 'SELECT cat_id, cat_name, cat_parent, left_id, right_id
			FROM ' . $this->pa_cat_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql, 600);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$forum_list = ($return_array) ? array() : '';

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['cat_parent']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['cat_parent']])) ? $padding_store[$row['cat_parent']] : '';
			}

			$right = $row['right_id'];
			$disabled = false;

			if (((is_array($ignore_id) && in_array($row['cat_id'], $ignore_id)) || $row['cat_id'] == $ignore_id) || ($row['cat_parent']))
			{
				$disabled = true;
			}

			if ($return_array)
			{
				$selected = (is_array($select_id)) ? ((in_array($row['cat_id'], $select_id)) ? true : false) : (($row['cat_id'] == $select_id) ? true : false);
				$forum_list[$row['cat_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['cat_id'], $select_id)) ? ' selected="selected"' : '') : (($row['cat_id'] == $select_id) ? ' selected="selected"' : '');
				$forum_list .= '<option value="' . $row['cat_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['cat_name'] . '</option>';
			}
		}
		$this->db->sql_freeresult($result);
		unset($padding_store);

		return $forum_list;
	}

	/**
	* Get the category details
	*/
	public function get_cat_infos($cat_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . "
			WHERE cat_id = $cat_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Cat #$cat_id does not exist", E_USER_ERROR);
		}

		return $row;
	}

	/**
	* Get the category branch
	*/
	public function get_cat_branch($cat_id, $type = 'all', $order = 'descending', $include_cat = true)
	{
		switch ($type)
		{
			case 'parents':
				$condition = 'a1.left_id BETWEEN a2.left_id AND a2.right_id';
			break;

			case 'children':
				$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id';
			break;

			default:
				$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id OR a1.left_id BETWEEN a2.left_id AND a2.right_id';
			break;
		}

		$rows = array();

		$sql = 'SELECT a2.*
			FROM ' . $this->pa_cat_table . ' a1
			LEFT JOIN ' . $this->pa_cat_table . " a2 ON ($condition)
			WHERE a1.cat_id = $cat_id
				ORDER BY a2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_cat && $row['cat_id'] == $cat_id)
			{
				continue;
			}

			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Returns cat parents as an array
	*/
	public function get_parents_data(&$cat_data)
	{
		$parents_data = array();
		if ($cat_data['cat_parent'] > 0)
		{
			if ($cat_data['parents_data'] == '')
			{
				$sql = 'SELECT cat_id, cat_name
					FROM ' . $this->pa_cat_table . '
					WHERE left_id < ' . $cat_data['left_id'] . '
						AND right_id > ' . $cat_data['right_id'] . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$row['cat_type'] = 1;
					$parents_data[$row['cat_id']] = array($row['cat_name'], (int) $row['cat_type']);
				}
				$this->db->sql_freeresult($result);
				$cat_data['parents_data'] = serialize($parents_data);
				$sql = 'UPDATE ' . $this->pa_cat_table . "
					SET parents_data = '" . $this->db->sql_escape($cat_data['parents_data']) . "'
					WHERE cat_parent = " . $cat_data['cat_parent'];
				$this->db->sql_query($sql);
			}
			else
			{
				return;
			}
		}
		return $parents_data;
	}

	public function get_cat_info($cat_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat_data = $row;
		}
		return $cat_data;
	}

	/**
	* Generate the navigation bar
	*/
	public function generate_cat_nav(&$cat_data)
	{
		$parent_cat_id = $this->request->variable('cat_id', $cat_data['cat_id']);

		// Get category parents
		$parents_data = $this->get_parents_data($cat_data);
		
		// Build navigation link
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang('FILES_DOWNLOADS'),
			'U_VIEW_FORUM'	=> $this->helper->route('orynider_pafiledb_controller'),
			'U_VIEW_CAT'	=> append_sid($this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $parent_cat_id)) )			
		));	
		
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $cat_data['cat_name'],
			'U_VIEW_FORUM'	=> $this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $parent_cat_id)),
		));
		
		if (!empty($parents_data))
		{
			foreach ($parents_data as $parent_cat_id => $parent_data)
			{
				list ($parent_name, $parent_type) = array_values($parent_data);
				$this->template->assign_block_vars('navlinks', array(
					'FORUM_NAME'	=> $parent_name,
					'U_VIEW_FORUM'	=> $this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $parent_cat_id)),
					'U_VIEW_CAT'	=> append_sid($this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $parent_cat_id)) )					
				));
			}
		}
		return;
	}

	/**
	* Generate the sub categories list
	*/
	public function generate_cat_list($cat_id)
	{
		$start = $this->request->variable('start', 0);

		// Setup message parser
		$this->message_parser = new \parse_message();

		// Read out config values
		$pafiledb_config = $this->config_values();

		$pagination_downloads = $pafiledb_config['pagination_downloads'];

		// pagination value for categories
		$dls = $pagination_downloads;

		// Total number of category
		$sql = 'SELECT COUNT(cat_id) AS total_cat
			FROM ' . $this->pa_cat_table . '
			WHERE cat_parent = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql, 60);
		$row = $this->db->sql_fetchrow($result);
		$total_cat = $row['total_cat'];
		$this->db->sql_freeresult($result);

		// Total number of subcategory
		$sql = 'SELECT COUNT(cat_id) AS total_sub_cat
			FROM ' . $this->pa_cat_table . '
			WHERE cat_parent > 0';
		$result = $this->db->sql_query($sql, 60);
		$row = $this->db->sql_fetchrow($result);
		$total_sub_cat = $row['total_sub_cat'];
		$this->db->sql_freeresult($result);

		// Select cat name
		$sql = 'SELECT cat_name
			FROM ' . $this->pa_cat_table. '
			WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql, 60);
		$row = $this->db->sql_fetchrow($result);
		$cat_name = $row['cat_name'];
		$this->db->sql_freeresult($result);

		// Check if there are downloads
		if ($total_cat == 0)
		{
			$this->template->assign_vars(array(
				'CAT_NAME'		=> $cat_name,
				'S_NO_CAT'		=> true,
				'MAIN_LINK'		=> $this->helper->route('orynider_pafiledb_controller'),
				'U_BACK'		=> append_sid("{$this->root_path}index.{$this->php_ext}"),
			));
		}
		else
		{
			$sql = 'SELECT bc.*, bd.*, COUNT(bd.file_id) AS number_downloads, MAX(bd.file_update_time) AS last_download
				FROM ' . $this->pa_cat_table . ' bc
				LEFT JOIN ' . $this->pa_cat_table . ' bc2
					ON ( bc2.left_id < bc.right_id
						AND bc2.left_id > bc.left_id
						AND bc2.cat_id = ' . (int) $cat_id . ' )
				LEFT JOIN ' . $this->pa_files_table . ' bd
					ON ( bd.file_catid = bc.cat_id
						OR bd.file_catid = bc2.cat_id	)
				WHERE bc.cat_parent = ' . (int) $cat_id . '
				GROUP BY bc.cat_id
				ORDER BY bc.left_id ASC';
			$result = $this->db->sql_query_limit($sql, $dls, $start, 60);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$row['last_download'] = ($row['last_download']) ? $row['last_download'] : 0;
				$subcats = $last_file_id = $last_file_name = $last_download_chg_time = $last_download = $downloads = $download = $last_download_name = $file_version = $file_name = $file_count = $file_time = $file_update_time = '';

				// Do we have sub categories?
				if (($row['left_id'] + 1) != $row['right_id'])
				{
					$sql2 = 'SELECT bc.*, bd.*, COUNT(bd.file_id) AS number_downloads_files
						FROM ' . $this->pa_cat_table . ' bc
						LEFT JOIN ' . $this->pa_files_table . ' bd
							ON ( bd.file_catid = bc.cat_id )
						WHERE bc.cat_parent = ' . $row['cat_id'] . '
						GROUP BY bc.cat_id
						ORDER BY bc.left_id ASC';
					$result2 = $this->db->sql_query($sql2, 60);

					while ($row2 = $this->db->sql_fetchrow($result2))
					{
						$number_downloads_files = ($row2['number_downloads_files'] == 1) ? $this->user->lang['FILES_SUBCAT_FILE'] : sprintf($this->user->lang['FILES_SUBCAT_FILES'], $row2['number_downloads_files']);

						$subcats .= ($subcats) ? ', ' : '';
						$subcats .= '<a class="subforum ' . (((isset($read_info[$row2['cat_id']]) ? $read_info[$row2['cat_id']] : 0) && ($this->user->data['user_id'] != ANONYMOUS)) ? 'unread' : 'read') . '" href="';
						$subcats .= $this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' =>	$row2['cat_id']));
						$subcats .= '">' . censor_text($row2['cat_name']) . '</a> <span class="small"><em>(' . $number_downloads_files . ')</em></span>';
					}
					$this->db->sql_freeresult($result2);

					$l_subcats = $this->user->lang['FILES_SUB_CAT'];
					if ($row['left_id'] + 3 != $row['right_id'])
					{
						$l_subcats = $this->user->lang['FILES_SUB_CATS'];
					}
				}
				else
				{
					$l_subcats = '';
				}
				
				$folder_image = (($row['left_id'] + 1) != $row['right_id']) ? $this->templates->img('pa_icon_subfolder', $this->user->lang['SUBFORUM'], false, '', 'full_tag') : $this->templates->img('pa_icon_folder', $this->user->lang['FOLDER'], false, '', 'full_tag');
				
				if ($row['last_download'])
				{
					$sql2 = 'SELECT *
						FROM ' . $this->pa_files_table . '
						WHERE file_update_time = ' . $row['last_download'];
					$result2 = $this->db->sql_query($sql2, 20);

					while ($row2 = $this->db->sql_fetchrow($result2))
					{
						$last_download_name = $row2['file_name'];
						$last_file_version = $row2['file_version'];
						$last_file_count = '<span style="font-weight: bold;">' . $row2['file_count'] . '</span>';
						$last_download_up_date = $row2['file_time'];
						$last_download_chg_time = $this->user->format_date($row2['file_update_time']);
					}

					if (!empty($last_file_version))
					{
						$downloads = $last_download_name . ' v' . $last_file_version;
					}
					else
					{
						$downloads = $last_download_name;
					}

					$last_download = sprintf($this->user->lang['FILES_LAST_DOWNLOAD'], $downloads, $last_file_count, $last_download_chg_time);
				}
				else
				{
					$last_download = $this->user->lang['FILES_NO_DOWNLOADS'];
				}

				$this->message_parser->message = $row['cat_desc'];
				$this->message_parser->bbcode_bitfield = $row['bbcode_bitfield'];
				$this->message_parser->bbcode_uid = $row['bbcode_uid'];
				$allow_bbcode = $allow_magic_url = $allow_smilies = true;
				$this->message_parser->format_display($allow_bbcode, $allow_magic_url, $allow_smilies);

				// Send the results to the template
				$this->template->assign_block_vars('catrow', array(
					'LAST_DOWNLOAD'			=> $last_download,
					'NUMBER_DOWNLOADS'		=> $row['number_downloads'],
					'CAT_NAME'				=> censor_text($row['cat_name']),
					'U_FILES_CAT'			=> $this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' =>	$row['cat_id'])),
					'CAT_DESC'				=> generate_text_for_display($row['cat_desc'], $row['cat_desc_uid'], $row['cat_desc_bitfield'], $row['cat_desc_options']),
					'CAT_FOLDER_IMG_SRC'	=> $folder_image,
					'SUBCATS'				=> ($subcats) ? $l_subcats . ': <span style="font-weight: bold;">' . $subcats . '</span>' : '',
				));
			}

			$this->db->sql_freeresult($result);

			$pagination_url = $this->helper->route('orynider_pafiledb_controller');

			//Start pagination
			$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_cat, $dls, $start);

			$this->template->assign_vars(array(
				'LAST_POST_IMG'			=> $this->templates->img('icon_topic_latest', 'VIEW_LATEST_POST'),
				'FILES_CATEGORIES'		=> ($total_cat == 1) ? sprintf($this->user->lang['FILES_CAT'], $total_cat) : sprintf($this->user->lang['FILES_CATS'], $total_cat),
				'FILES_SUB_CAT_SHOW'	=> ($total_sub_cat == 0) ? false : true,
				'FILES_SUB_CATEGORIES'	=> ($total_sub_cat == 1) ? sprintf($this->user->lang['FILES_SUB_CATEGORY'], $total_sub_cat) : sprintf($this->user->lang['FILES_SUB_CATEGORIES'], $total_sub_cat),
			));
		}
	}

	/**
	 * Sync All.
	 *
	 */
	function sync_all()
	{
		foreach( $this->cat_rowset as $cat_id => $void )
		{
			$this->sync( $cat_id, false );
		}
		$this->init();
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @param unknown_type $init
	 */
	function sync( $cat_id, $init = true )
	{
		$cat_nav = array();
		$this->category_nav( $this->cat_rowset[$cat_id]['cat_parent'], $cat_nav );

		$sql = 'UPDATE ' . $this->pa_cat_table . "
			SET parents_data = ''
			WHERE cat_parent = " . $this->cat_rowset[$cat_id]['cat_parent'];

		if ( !( $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}

		$sql = 'UPDATE ' . $this->pa_cat_table . "
				SET cat_files = '-1',
				cat_last_file_id = '0',
				cat_last_file_name = '',
				cat_last_file_time = '0'
				WHERE cat_id = '" . $cat_id . "'";

		if ( !( $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}
		if ( $init )
		{
			$this->init();
		}
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_parent
	 * @param unknown_type $cat_nav
	 */
	function category_nav( $cat_parent, $cat_nav )
	{
		if ( !empty( $this->cat_rowset[$cat_parent] ) )
		{
			$this->category_nav( $this->cat_rowset[$cat_parent]['cat_parent'], $cat_nav );
			$cat_nav[$cat_parent] = $this->cat_rowset[$cat_parent]['cat_name'];
		}
		return;
	}

	/**
	 * if there is no cat
	 *
	 * @return unknown
	 */
	function cat_empty()
	{
		return ( $this->total_cat == 0 ) ? true : false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $true_false
	 */
	function modified( $true_false = false )
	{
		$this->modified = $true_false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @return unknown
	 */
	function items_in_cat( $cat_id )
	{
		if ( $this->cat_rowset[$cat_id]['cat_files'] == -1 || $this->modified )
		{
			$sql = 'SELECT COUNT(file_id) as total_files
				FROM ' . $this->pa_files_table . "
				WHERE file_approved = '1'
				AND file_catid IN (" . $this->gen_cat_ids( $cat_id ) . ')
				ORDER BY file_time DESC';

			$this->db->sql_query($sql);

			$number_of_items = 0;
			if ( $row = $this->db->sql_fetchrow( $result ) )
			{
				$number_of_items = $row['total_files'];
			}

			$sql = 'UPDATE ' . $this->pa_cat_table . "
					SET cat_files = $number_of_items
					WHERE cat_id = $cat_id";

			if ( !( $result = $this->db->sql_query( $sql ) ) )
			{
				$this->message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$number_of_items = $this->cat_rowset[$cat_id]['cat_files'];
		}

		return $number_of_items;
	}

	/**
	 * Jump menu function.
	 *
	 * @param unknown_type $cat_id to handle parent cat_id
	 * @param unknown_type $depth related to function to generate tree
	 * @param unknown_type $default the cat you wanted to be selected
	 * @param unknown_type $for_file TRUE high category ids will be -1
	 * @param unknown_type $check_upload if true permission for upload will be checked
	 * @return unknown
	 */
	function generate_jumpbox( $cat_id = 0, $depth = 0, $default = '', $for_file = false, $check_upload = true, $auth = 'auth_view' )
	{
		global $page_id;
		//static $cat_rowset = false;

		if ( !is_array( $cat_rowset ) )
		{
			if ( $check_upload )
			{
				if ( !empty( $this->cat_rowset ) )
				{
					foreach( $this->cat_rowset as $row )
					{
						if ( $this->auth_user[$row['cat_id']][$auth] )
						{
							$cat_rowset[$row['cat_id']] = $row;
						}
					}
				}
			}
			else
			{
				$cat_rowset = $this->cat_rowset;
			}
		}

		$cat_list .= '';

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );

		$temp_cat_rowset = $cat_rowset;

		if ( !empty( $temp_cat_rowset ) )
		{
			foreach ( $temp_cat_rowset as $temp_cat_id => $cat )
			{
				if ( $cat['cat_parent'] == $cat_id )
				{
					if ( is_array( $default ) )
					{
						if ( isset( $default[$cat['cat_id']] ) )
						{
							$sel = ' selected="selected"';
						}
						else
						{
							$sel = '';
						}
					}
					$cat_pre = ( !$cat['cat_allow_file'] ) ? '+ ' : '- ';
					$sub_cat_id = ( $for_file ) ? ( ( !$cat['cat_allow_file'] ) ? -1 : $cat['cat_id'] ) : $cat['cat_id'];
					$cat_class = ( !$cat['cat_allow_file'] ) ? 'class="greyed"' : '';
					$cat_list .= '<option value="' . $sub_cat_id . '"' . $sel . ' ' . $cat_class . ' />' . $pre . $cat_pre . $cat['cat_name'] . '</option>';
					$cat_list .= $this->generate_jumpbox( $cat['cat_id'], $depth + 1, $default, $for_file, $check_upload );
				}
			}
			return $cat_list;
		}
		else
		{
			return;
		}
	}

	/**
	 * get_sub_cat.
	 *
	 * get all sub category in side certain category
	 * - used when listing pafiledb/articles/links etc
	 *
	 * @param unknown_type $cat_id
	 * @return unknown
	 */
	function get_sub_cat( $cat_id )
	{
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$cat_sub = '';
		if ( !empty( $this->subcat_rowset[$cat_id] ) )
		{
			$class = "gensmall";
			$init_link_max = ( count( $this->subcat_rowset[$cat_id] ) > 3 ) ? 3 : count( $this->subcat_rowset[$cat_id] );
			$truncate = false;
			$i = 0;
			foreach( $this->subcat_rowset[$cat_id] as $cat_id => $cat_row )
			{
				if ( $this->auth_user[$cat_row['cat_id']]['auth_view'] && ( $cat_row['cat_allow_file'] || !empty( $this->subcat_rowset[$cat_row['cat_id']] ) ) )
				{
					$i++;
					if ($i > $init_link_max)
					{
						$truncate = true;
						break;
					}
					$cat_sub .= (!empty($cat_sub) ? '<span class=' . $class . '>, </span>' : '') . '<a href="' . append_sid( $this->mxurl( 'action=category&cat_id=' . $cat_row['cat_id'] ) ) . '" class=' . $class . '>' . $cat_row['cat_name'] . '</a>';
				}
				/*
				else
				{
					if ( !empty( $this->subcat_rowset[$cat_row['cat_id']] ) )
					{
						foreach( $this->subcat_rowset[$cat_row['cat_id']] as $sub_cat_id => $sub_cat_row )
						{
							if ( $sub_cat_row['cat_allow_file'] )
							{
								$i++;
								if ($i > $init_link_max)
								{
									$truncate = true;
									break;
								}
								$cat_sub .= (!empty($cat_sub) ? '<span class=' . $class . '>, </span>' : '') . '<a href="' . append_sid( $this->mxurl( 'action=category&cat_id=' . $sub_cat_row['cat_id'] ) ) . '" class=' . $class . '>' . $sub_cat_row['cat_name'] . '</a>';
							}
						}
					}
				}
				*/
			}

			if ($truncate)
			{
				$cat_sub .= '<span class=' . $class . '>, ...</span>';
			}
		}
		return $cat_sub;
	}

	/**
	 * generate_navigation.
	 *
	 * @param unknown_type $cat_id
	 */
	function generate_navigation($cat_id)
	{
		if (empty($this->cat_rowset[$cat_id]))
		{
			//print_r('cat_rowset empty '.$this->cat_rowset[$cat_id].'</br>');
			$cat_parent = 0;
		}
		elseif (empty($this->cat_rowset[$cat_id]['parents_data']))		
		{
			$cat_parent = $this->cat_rowset[$cat_id]['cat_parent'];	
		}		
		
		if (empty($this->cat_rowset[$cat_id]['parents_data']))
		{
			$cat_nav = array();
			$this->category_nav( $this->cat_rowset[$cat_id]['cat_parent'], $cat_nav );

			$sql = 'UPDATE ' . $this->pa_cat_table . "
				SET parents_data = '" . addslashes( serialize( $cat_nav ) ) . "'
				WHERE cat_parent = " . $cat_parent;
			if (!( $this->db->sql_query($sql)))
			{
				$this->message_die(GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			$cat_nav = unserialize( stripslashes( $this->cat_rowset[$cat_id]['parents_data'] ) );
		}

		if ( !empty( $cat_nav ) )
		{
			foreach ( $cat_nav as $parent_cat_id => $parent_name )
			{
				$this->template->assign_block_vars( 'navlinks', array(
					'CAT_NAME' 		=> $parent_name,
					'FORUM_NAME'	=> $this->user->lang('FILES_DOWNLOADS'),
					'U_VIEW_FORUM'	=> append_sid($this->helper->route('orynider_pafiledb_controller')),					
					'U_VIEW_CAT'	=> append_sid($this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $parent_cat_id)) ),
					'U_BACK'		=> append_sid("{$this->root_path}index.{$this->php_ext}")					
				));
				
			}
		}

		$this->template->assign_block_vars( 'navlinks', array(
			'CAT_NAME' 		=> $this->cat_rowset[$cat_id]['cat_name'],
			'FORUM_NAME'	=> $this->user->lang('FILES_DOWNLOADS'),
			'U_VIEW_FORUM'	=> append_sid($this->helper->route('orynider_pafiledb_controller')),			
			'U_VIEW_CAT'	=> append_sid($this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => !empty($this->cat_rowset[$cat_id]['cat_id']) ? $this->cat_rowset[$cat_id]['cat_id'] : $cat_id)) ),			
			'U_BACK'		=> append_sid("{$this->root_path}index.{$this->php_ext}")			
		));

		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @return unknown
	 */
	function new_item_in_cat( $cat_id )
	{
		global $_COOKIE;
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		
		$cat_array = explode(', ', $this->gen_cat_ids( $cat_id ));

		$files_new = 0;
		$time = time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 );

		foreach ( $cat_array as $key => $cat_id )
		{
			if ( $this->auth_user[$cat_id]['auth_read'] && $this->cat_rowset[$cat_id]['cat_last_file_time'] > $time)
			{
				$files_new++;
			}
		}

		return $files_new;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @param unknown_type $file_info
	 */
	function last_item_in_cat( $cat_id, &$file_info )
	{
		if ( ( empty( $this->cat_rowset[$cat_id]['cat_last_file_id'] ) && empty( $this->cat_rowset[$cat_id]['cat_last_file_name'] ) && empty( $this->cat_rowset[$cat_id]['cat_last_file_time'] ) ) || $this->modified )
		{		
			$sql = 'SELECT file_time, file_id, file_name, file_catid
				FROM ' . $this->pa_files_table . "
				WHERE file_approved = '1'
				AND file_catid IN (" . $this->gen_cat_ids( $cat_id ) . ")
				ORDER BY file_time DESC";
			$this->db->sql_query( $sql, 300 );

			while ( $row = $this->db->sql_fetchrow( $result ) )
			{
				$temp_cat[] = $row;
			}

			$file_info = $temp_cat[0];
			if ( !empty( $file_info ) )
			{
				$sql = 'UPDATE ' . $this->pa_cat_table . "
					SET cat_last_file_id = " . intval( $file_info['file_id'] ) . ",
					cat_last_file_name = '" . addslashes( $file_info['file_name'] ) . "',
					cat_last_file_time = " . intval( $file_info['file_time'] ) . "
					WHERE cat_id = $cat_id";
				$this->db->sql_query($sql);
			}
		}
		else
		{
			$file_info['file_id'] = $this->cat_rowset[$cat_id]['cat_last_file_id'];
			$file_info['file_name'] = $this->cat_rowset[$cat_id]['cat_last_file_name'];
			$file_info['file_time'] = $this->cat_rowset[$cat_id]['cat_last_file_time'];
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @param unknown_type $cat_ids
	 * @return unknown
	 */
	function gen_cat_ids( $cat_id, $cat_ids = '' )
	{
		if ( !empty( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $cat_row )
			{
				$cat_ids = $this->gen_cat_ids( $subcat_id, $cat_ids );
			}
		}

		if ( !empty( $this->cat_rowset[$cat_id] ) )
		{
			$cat_ids .= ( ( $cat_ids != '' ) ? ', ' : '' ) . $cat_id;
		}
		return $cat_ids;
	}

	/**
	 * display_categories.
	 *
	 * @param unknown_type $cat_id
	 */
	function display_categories( $cat_id = PA_ROOT_CAT )
	{
		global $debug;
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		
		if ( $this->cat_empty() )
		{
			if ( !$this->user->data['is_registered'] )
			{
				$redirect = ( $cat_id != PA_ROOT_CAT ) ? $this->mxurl( "action=category&cat_id=$cat_id" ) : $this->mxurl();
				login_box($redirect, ((isset($this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)])) ? $this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)] : $this->user->lang['LOGIN_EXPLAIN_PAFILEDB']));
			}
			$this->message_die( GENERAL_ERROR, 'Either you are not allowed to view any category, or there is no category in the database' );
		}

		$this->template->assign_vars( array(
			'CAT_NAV_SIMPLE' => true,
			'L_SUB_CAT' => $this->user->lang['Sub_category'],
			'L_CATEGORY' => $this->user->lang['Category'],
			'L_LAST_FILE' => $this->user->lang['Last_file'],
			'L_FILES' => $this->user->lang['Files'] )
		);
		
		//
		// Output the categories
		//
		if ($this->subcat_rowset[$cat_id])
		{
			$catnum = count($this->subcat_rowset[$cat_id]);
			$catcol = $pafiledb_config['cat_col'] > 0 ? $pafiledb_config['cat_col'] : 1;
			$num_of_rows = intval( $catnum / $catcol );

			if ( $catnum % $catcol )
			{
				$num_of_rows++;
			}

			$this->template->assign_vars( array( 'WIDTH' => 100 / $catcol ) );
			$i = 0;
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				if ( $i == 0 || $i ==  $catcol)
				{
					$this->template->assign_block_vars('catcol', array(
						'S_IS_CAT'				=> true)
					);					
					$i = 0;
				}
				$i++;

				$last_file_info = array();
				$this->last_item_in_cat( $subcat_id, $last_file_info );

				if ( !empty( $last_file_info['file_id'] ) && $this->auth_user[$subcat_id]['auth_read'] )
				{
					$last_file_time = $this->create_date( $this->config['default_dateformat'], $last_file_info['file_time'], $this->config['board_timezone'] );
					$last_file = $last_file_time . '<br />';
					$last_file_name = ( strlen( stripslashes( $last_file_info['file_name'] ) ) > 20 ) ? substr( stripslashes( $last_file_info['file_name'] ), 0, 20 ) . '...' : stripslashes( $last_file_info['file_name'] );
					$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '" alt="' . stripslashes( $last_file_info['file_name'] ) . '" title="' . stripslashes( $last_file_info['file_name'] ) . '">' . $last_file_name . '</a> ';
					$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $this->templates->img('pa_icon_latest_reply', '', false, '', 'src') . '" border="0" alt="' . $this->user->lang['View_latest_file'] . '" title="' . $this->user->lang['View_latest_file'] . '" /></a>';
				}
				else
				{
					$last_file = $this->user->lang['No_file'];
				}

				$is_new = false;
				if ( $this->new_item_in_cat( $subcat_id ) )
				{
					$is_new = true;
				}

				$sub_cat = $this->get_sub_cat( $subcat_id );
				$this->template->assign_block_vars('catcol.no_cat_parent', array(
					'U_CAT' => append_sid( $this->mxurl( 'action=category&cat_id=' . $subcat_id ) ),
					'SUB_CAT' => ( !empty( $sub_cat ) ) ? "&nbsp;&nbsp;$sub_cat" : "",
					'CAT_IMAGE' => ( $is_new ) ? $this->templates->img('forum_unread', '', false, '', 'src') : $this->templates->img('forum_read', '', false, '', 'src'),
					'CAT_NAME' => $subcat_row['cat_name'],
					'FILECAT' => $this->items_in_cat( $subcat_id ) )
				);
			}
		}
	}

	/**
	 * display_categories - original.
	 *
	 * @param unknown_type $cat_id
	 */
	function display_categories_original( $cat_id = PA_ROOT_CAT, $action_name = 'action', $action_default = 'category', $map_xtra = '' )
	{
		global $debug;
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		
		if ( $this->cat_empty() )
		{
			if ( !$this->user->data['is_registered'] )
			{
				$redirect = ( $cat_id != PA_ROOT_CAT ) ? $this->mxurl( "$action_name=$action_default&cat_id=$cat_id" ) : $this->mxurl();
				login_box($redirect, ((isset($this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)])) ? $this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)] : $this->user->lang['LOGIN_EXPLAIN_PAFILEDB']));
			}
			$this->message_die( GENERAL_ERROR, 'Either you are not allowed to view any category, or there is no category in the database' );
		}

		$this->template->assign_vars( array(
			'CAT_NAV_STANDARD' => true,
			'L_SUB_CAT' => $this->user->lang['Sub_category'],
			'L_CATEGORY' => $this->user->lang['Category'],
			'L_LAST_FILE' => $this->user->lang['Last_file'],
			'L_FILES' => $this->user->lang['Files'] )
		);

		//
		// Category navigation for cat_id that allow files
		// - used in cat pages without files
		//
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				if ( ( $subcat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE ) )
				{
					$last_file_info = array();
					$this->last_item_in_cat( $subcat_id, $last_file_info );

					if ( !empty( $last_file_info['file_id'] ) && $this->auth_user[$subcat_id]['auth_read'] )
					{
						$last_file_time = $this->create_date( $this->config['default_dateformat'], $last_file_info['file_time'], $this->config['board_timezone'] );
						$last_file = $last_file_time . '<br />';
						$last_file_name = ( strlen( stripslashes( $last_file_info['file_name'] ) ) > 20 ) ? substr( stripslashes( $last_file_info['file_name'] ), 0, 20 ) . '...' : stripslashes( $last_file_info['file_name'] );
						$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '" alt="' . stripslashes( $last_file_info['file_name'] ) . '" title="' . stripslashes( $last_file_info['file_name'] ) . '">' . $last_file_name . '</a> ';
						$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $this->user->img['pa_icon_latest_reply'] . '" border="0" alt="' . $this->user->lang['View_latest_file'] . '" title="' . $this->user->lang['View_latest_file'] . '" /></a>';
					}
					else
					{
						$last_file = $this->user->lang['No_file'];
					}
					$is_new = false;

					if ( $this->new_item_in_cat( $subcat_id ) )
					{
						$is_new = true;
					}

					$sub_cat = $this->get_sub_cat( $subcat_id );

					$this->template->assign_block_vars( 'no_cat_parent', array(
						'IS_HIGHER_CAT' => false,
						'U_CAT' => append_sid( $this->mxurl( "$action_name=$action_default&cat_id=" . $subcat_id . $map_xtra ) ),
						'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<br /><b>' . $this->user->lang['Sub_category'] . ': </b>' . $sub_cat :  '',
						'CAT_IMAGE' => ( $is_new ) ? $this->templates->img('forum_unread', '', false, '', 'src') : $this->templates->img('forum_read', '', false, '', 'src'),
						'CAT_NEW_FILE' => ( $is_new ) ? $this->user->lang['New_file'] : $this->user->lang['No_new_file'],
						'CAT_NAME' => $subcat_row['cat_name'],
						'FILECAT' => $this->items_in_cat( $subcat_id ),
						'LAST_FILE' => $last_file,
						'CAT_DESC' => $subcat_row['cat_desc'] )
					);
				}
			}
		}

		//
		// Category navigation for cat_id that doesn't allow files
		// - used in cat pages with files
		//
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				$total_sub_cat = 0;
				if ( isset( $this->subcat_rowset[$subcat_id] ) )
				{
					foreach( $this->subcat_rowset[$subcat_id] as $sub_no_cat_id => $sub_no_cat_row )
					{
						if ( $sub_no_cat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE )
						{
							$sub_cat_rowset[$total_sub_cat] = $sub_no_cat_row;
							$total_sub_cat++;
						}
					}
				}

				//
				// This is a container category
				//
				if ( ( $subcat_row['cat_allow_file'] != PA_CAT_ALLOW_FILE ) )
				{
					if ( $total_sub_cat )
					{
						$this->template->assign_block_vars( 'no_cat_parent', array(
							'IS_HIGHER_CAT' => true,
							'U_CAT' => append_sid( $this->mxurl( "$action_name=$action_default&cat_id=" . $subcat_id . $map_xtra) ),
							'CAT_NAME' => $subcat_row['cat_name'] )
						);
					}

					for( $k = 0; $k < $total_sub_cat; $k++ )
					{
						$last_file_info = array();
						$this->last_item_in_cat( $sub_cat_rowset[$k]['cat_id'], $last_file_info );

						if ( $sub_cat_rowset[$k]['cat_parent'] == $subcat_id )
						{
							if ( !empty( $last_file_info['file_id'] ) && $this->auth_user[$sub_cat_rowset[$k]['cat_id']]['auth_read'] )
							{
								$last_file_time = $this->create_date( $this->config['default_dateformat'], $last_file_info['file_time'], $this->config['board_timezone'] );
								$last_file = $last_file_time . '<br />';
								$last_file_name = ( strlen( $last_file_info['file_name'] ) > 20 ) ? substr( $last_file_info['file_name'], 0, 20 ) . '...' : $last_file_info['file_name'];
								$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '">' . $last_file_name . '</a> ';
								$last_file .= '<a href="' . append_sid( $this->mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $this->user->img['pa_icon_latest_reply'] . '" border="0" alt="' . $this->user->lang['View_latest_file'] . '" title="' . $this->user->lang['View_latest_file'] . '" /></a>';
							}
							else
							{
								$last_file = $this->user->lang['No_file'];
							}

							$is_new = false;

							if ( $this->new_item_in_cat($sub_cat_rowset[$k]['cat_id']) )
							{
								$is_new = true;
							}

							$sub_cat = $this->get_sub_cat($sub_cat_rowset[$k]['cat_id']);

							$this->template->assign_block_vars('no_cat_parent', array(
								'IS_HIGHER_CAT' => false,
								'U_CAT' => append_sid( $this->mxurl( "$action_name=$action_default&cat_id=" . $sub_cat_rowset[$k]['cat_id'] . $map_xtra ) ),
								'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<br /><b>' . $this->user->lang['Sub_category'] . ': </b>' . $sub_cat : '',
								'CAT_IMAGE' => ( $is_new ) ? $this->templates->img('forum_unread', '', false, '', 'src') : $this->templates->img('forum_read', '', false, '', 'src'),
								'CAT_NEW_FILE' => ( $is_new ) ? $this->user->lang['New_file'] : $this->user->lang['No_new_file'],
								'CAT_NAME' => $sub_cat_rowset[$k]['cat_name'],
								'FILECAT' => $this->items_in_cat( $sub_cat_rowset[$k]['cat_id'] ),
								'LAST_FILE' => $last_file,
								'CAT_DESC' => $sub_cat_rowset[$k]['cat_desc'] )
							);
						} // Have a permission to view the category
					} // It is not parent category
				}
			}
		} //higher Category
	}

	/**
	 * display items.
	 *
	 * @param unknown_type $sort_method
	 * @param unknown_type $sort_order
	 * @param unknown_type $start
	 * @param unknown_type $cat_id
	 * @param unknown_type $show_file_message
	 * @param unknown_type $sort_options_list
	 * @param unknown_type $sql_xtra
	 * @param unknown_type $target_page_id
	 */
	function display_items( $sort_method, $sort_order, $start, $cat_id = false, $show_file_message = true, $sort_options_list = false, $sql_xtra = '', $target_page_id = false )
	{
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;
		
		// Read out config values
		$pafiledb_config = $this->config_values();
		
		$filelist = false;

		$file_rowset = array();
		$total_file = 0;

		//
		// Category SQL
		//
		if (!$cat_id)
		{
			$cat_where = "AND f1.file_catid IN (" . $this->gen_cat_ids( '0' ) . ")";
		}
		else if (is_array($cat_id))
		{
			$cat_where = "AND f1.file_catid IN (" . $this->gen_cat_ids( $cat_id['parent'] ) . ")";
			$cat_id = false;
		}
		else
		{
			$cat_where = "AND f1.file_catid = $cat_id";
		}

		//
		// This first query is needed to find pinned files
		//
		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.file_id) AS total_comments
					FROM " . $this->pa_files_table . " AS f1, " . $this->pa_votes_table . " AS r, " . USERS_TABLE . " AS u, " . $this->pa_comments_table . " AS c, " . $this->pa_cat_table . " AS cat
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_id = c.file_id(+)
					AND f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					AND f1.file_catid = cat.cat_id
					$cat_where
					$sql_xtra
					GROUP BY f1.file_id
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, IF(COUNT(r.rate_point)>0,AVG(r.rate_point),0) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.file_id) AS total_comments
					FROM " . $this->pa_files_table . " AS f1
						LEFT JOIN " . $this->pa_votes_table . " AS r ON f1.file_id = r.votes_file
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . $this->pa_comments_table . " AS c ON f1.file_id = c.file_id
						LEFT JOIN " . $this->pa_cat_table . " AS cat ON f1.file_catid = cat.cat_id
					WHERE f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					$sql_xtra
					GROUP BY f1.file_id
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		$file_rowset = array();
		$total_file = 0;

		while ( $row = $this->db->sql_fetchrow( $result ) )
		{
			if ( $this->auth_user[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$this->db->sql_freeresult( $result );

		//
		// Main query
		//
		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, u.user_colour
					FROM " . $this->pa_files_table . " AS f1, " . $this->pa_votes_table . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					AND f1.file_catid = cat.cat_id
					$cat_where
					$sql_xtra
					GROUP BY f1.file_id
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, u.user_colour
					FROM " . $this->pa_files_table . " AS f1
						LEFT JOIN " . $this->pa_votes_table . " AS r ON f1.file_id = r.votes_file
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . $this->pa_cat_table . " AS cat ON f1.file_catid = cat.cat_id
					WHERE f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					$sql_xtra
					GROUP BY f1.file_id
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $this->db->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $this->db->sql_fetchrow( $result ) )
		{
			if ( $this->auth_user[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$this->db->sql_freeresult( $result );

		$sql = "SELECT COUNT(f1.file_id) as total_file
			FROM " . $this->pa_files_table . " AS f1
			WHERE f1.file_approved='1'
			$cat_where
			$sql_xtra";

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldn\'t get number of file', '', __LINE__, __FILE__, $sql );
		}

		$row = $this->db->sql_fetchrow( $result );
		$this->db->sql_freeresult( $result );

		$total_file = $row['total_file'];
		unset( $row );

		//
		// Ratings
		//
		$pa_use_ratings = false;
		for ( $i = 0; $i < count( $file_rowset ); $i++ )
		{
			if ( $this->ratings[$file_rowset[$i]['file_catid']]['activated'] )
			{
				$pa_use_ratings = true;
				break;
			}
		}

		for ( $i = 0; $i < count( $file_rowset ); $i++ )
		{
			// ===================================================
			// Format the date for the given file
			// ===================================================
			$date = $this->create_date( $this->config['default_dateformat'], $file_rowset[$i]['file_time'], $this->config['board_timezone'] );
			$date_updated = $this->create_date( $this->config['default_dateformat'], $file_rowset[$i]['file_update_time'], $this->config['board_timezone'] );
			// ===================================================
			// Get rating and comments for the file and format it
			// ===================================================
			$rating = ( $file_rowset[$i]['rating'] != 0 ) ? round( $file_rowset[$i]['rating'], 2 ) . '/10' : $this->user->lang['Not_rated'];
			//$comments = ( $file_rowset[$i]['total_comments'] != 0 ) ? $file_rowset[$i]['total_comments'] : $this->user->lang['No_comments'];
			// ===================================================
			// If the file is new then put a new image in front of it
			// ===================================================
			$is_new = false;
			if ( (time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 )) < $file_rowset[$i]['file_time'] )
			{
				$is_new = true;
			}

			$cat_name = ( empty( $cat_id ) ) ? $this->cat_rowset[$file_rowset[$i]['file_catid']]['cat_name'] : '';
			$cat_url = append_sid( $this->mxurl( 'action=category&cat_id=' . $file_rowset[$i]['file_catid'] ) );
			// ===================================================
			// Get the post icon fot this file
			// ===================================================
			if ( $file_rowset[$i]['file_pin'] != FILE_PINNED )
			{
				if ( $file_rowset[$i]['file_posticon'] == 'none' || $file_rowset[$i]['file_posticon'] == 'none.gif' )
				{
					$posticon = $this->templates->img('mx_spacer', '', false, '', 'src');
				}
				else
				{
					$posticon = $this->module_root_path . ICONS_DIR . $file_rowset[$i]['file_posticon'];
				}
			}
			else
			{
				$posticon = $this->templates->img('sticky_read', '', false, '', 'src');
			}

			$save_as_icon = $this->module_root_path . ICONS_DIR . 'icon_download1.gif';

			//
			// Poster
			//
			$file_poster = get_username_string('full', $file_rowset[$i]['user_id'], $file_rowset[$i]['username'], $file_rowset[$i]['user_colour']);

			// ===================================================
			// Assign Vars
			// ===================================================
			if (!$file_rowset[$i]['file_disable'])
			{
				$dl_link_jump = append_sid( $this->mxurl( 'action=download&file_id=' . $file_rowset[$i]['file_id'], true, false ) );
				$dl_link_jump_save_as = append_sid( $this->mxurl( 'action=download&file_id=' . $file_rowset[$i]['file_id'] . '&save_as', true, false ) );
			}
			else
			{
				$dl_link_jump = $dl_link_jump_save_as = "javascript:disable_popup(".$file_rowset[$i]['file_id'].")";
			}

			$this->template->assign_block_vars( "file_rows", array(
				'L_NEW_FILE' => $this->user->lang['New_file'],
				'L_SAVE_AS' => $this->user->lang['Save_as'],
				'PIN_IMAGE' => $posticon,
				'SAVE_AS_IMAGE' => $save_as_icon,
				'FILE_NAME' => $file_rowset[$i]['file_name'],
				'FILE_DESC' => $file_rowset[$i]['file_desc'],
				'FILE_ID' => $file_rowset[$i]['file_id'],
				'DATE' => $date,
				'UPDATED' => $date_updated,
				'L_RATING' => $this->user->lang['DlRating'],
				'DO_RATE' => $this->auth_user[$cat_id]['auth_rate'] ? '<a href="' . append_sid( $this->mxurl( 'action=rate&amp;file_id=' . $file_rowset[$i]['file_id'] ) ) . '">' . $this->user->lang['Do_rate'] . '</a>' : '',
				'L_COMMENT' => '<a href="' . append_sid( $this->mxurl( 'action=post_comment&amp;item_id=' . $file_rowset[$i]['file_id'] . '&amp;cat_id=' . $file_rowset[$i]['file_catid'] ) ) . '">' . $this->user->lang['Comments'] . '</a>',
				'RATING' => $rating,
				'FILE_VOTES' => $file_rowset[$i]['total_votes'],
				'FILE_DLS' => $file_rowset[$i]['file_dls'],
				'CAT_NAME' => $cat_name,
				'IS_NEW_FILE' => $is_new,

				'U_CAT' => $cat_url,
				'SHOW_RATINGS' => ( $pa_use_ratings ?  true : false ),
				'U_FILE' => append_sid( $this->mxurl( 'action=file&file_id=' . $file_rowset[$i]['file_id'], false, false, $target_page_id ) ),
				'U_FILE_JUMP' => $dl_link_jump,
				'U_FILE_JUMP_SAVE_AS' => $dl_link_jump_save_as,
				'COLOR' => ( ( $i % 2 ) ? "row2" : "row1" ),
				'POSTER' => $file_poster,
				'FILE_DISABLE_MSG' => nl2br( $file_rowset[$i]['disable_msg'] ),
				
				'FILE_NEW_IMAGE' => $this->templates->img('icon_pa_file_new', '', false, '', 'src'),
				
				'HAS_SCREENSHOTS' => ( !empty( $file_rowset[$i]['file_ssurl'] ) ) ? true : false,
				'SS_AS_LINK' => ( $file_rowset[$i]['file_sshot_link'] ) ? true : false,
				'FILE_SCREENSHOT' => $file_rowset[$i]['file_ssurl'],
				'FILE_SCREENSHOT_URL' => $this->module_root_path . 'styles/all/images/lwin.gif',
			));

			//
			// Options (only used for the toplist block)
			//
			if ($sort_options_list)
			{
				foreach ($sort_options_list as $sort_option => $options_value)
				{
					switch ($sort_option)
					{
						case 'date':
							$this->template->assign_block_vars( "file_rows.display_date", array());
						break;
						case 'username':
							$this->template->assign_block_vars( "file_rows.display_username", array());
						break;
						case 'counter':
							$this->template->assign_block_vars( "file_rows.display_counter", array());
						break;
						case 'rate':
							$this->template->assign_block_vars( "file_rows.display_rate", array());
						break;
					}
				}
			}

			$filelist = true;
			$pa_use_ratings = $this->ratings[$file_rowset[$i]['file_catid']]['activated'];
		}

		if ( $filelist )
		{
			$action = ( empty( $cat_id ) ) ? 'viewall' : 'category&amp;cat_id=' . $cat_id;
			$this->template->assign_vars( array(
				'FILELIST' => $filelist,
				'ORIGINAL_STYLE' => false,

				'L_CATEGORY' => $this->user->lang['Category'],
				'L_VOTES' => $this->user->lang['Votes'],
				'L_DOWNLOADS' => $this->user->lang['Dls'],
				'L_SUBMITED_BY' => $this->user->lang['Submiter'],
				'L_DATE' => $this->user->lang['Date'],
				'L_NAME' => $this->user->lang['Name'],
				'L_FILE' => $this->user->lang['File'],
				'L_FILES' => $this->user->lang['Files'],
				'L_UPDATE_TIME' => $this->user->lang['Update_time'],
				'L_SCREENSHOTS' => $this->user->lang['Scrsht'],

				'L_SELECT_SORT_METHOD' => $this->user->lang['Select_sort_method'],
				
				'L_ORDER' => $this->user->lang['Order'],
				'L_SORT' => $this->user->lang['Sort'],

				'L_ASC' => $this->user->lang['Sort_Ascending'],
				'L_DESC' => $this->user->lang['Sort_Descending'],

				'SORT_NAME' => ( $sort_method == 'file_name' ) ? 'selected="selected"' : '',
				'SORT_TIME' => ( $sort_method == 'file_time' ) ? 'selected="selected"' : '',
				'SORT_RATING' => ( $sort_method == 'rating' ) ? 'selected="selected"' : '',
				'SORT_DOWNLOADS' => ( $sort_method == 'file_dls' ) ? 'selected="selected"' : '',
				'SORT_UPDATE_TIME' => ( $sort_method == 'file_update_time' ) ? 'selected="selected"' : '',

				'SORT_ASC' => ( $sort_order == 'ASC' ) ? 'selected="selected"' : '',
				'SORT_DESC' => ( $sort_order == 'DESC' ) ? 'selected="selected"' : '',
				'PAGINATION' => $this->generate_pagination( append_sid( $this->mxurl( "action=$action&amp;sort_method=$sort_method&amp;sort_order=$sort_order" ) ), $total_file, $pafiledb_config['pagination'], $start ),
				'PAGE_NUMBER' => sprintf( $this->user->lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_file / $pafiledb_config['pagination'] ) ),
				'ID' => $cat_id,
				'START' => $start,
				'SHOW_RATINGS' => ( $pa_use_ratings ) ? true : false,

				'S_ACTION_SORT' => append_sid( $this->mxurl( "action=$action" ) ) )
			);
		}
		else
		{
			$this->template->assign_vars( array(
				'L_CATEGORY' => $this->user->lang['Category'],
				'L_RATING' => $this->user->lang['DlRating'],
				'L_DOWNLOADS' => $this->user->lang['Dls'],
				'L_DATE' => $this->user->lang['Date'],
				'L_NAME' => $this->user->lang['Name'],
				'L_FILE' => $this->user->lang['File'],
				'L_UPDATE_TIME' => $this->user->lang['Update_time'],
				'L_SCREENSHOTS' => $this->user->lang['Scrsht'],
				'NO_FILE' => $show_file_message,
				'L_NO_FILES' => $this->user->lang['No_files'],
				'L_NO_FILES_CAT' => $this->user->lang['No_files_cat'] )
			);
		}

		return $total_file;
	}	
	
	/**
	* Assign authors
	*/
	public function assign_authors()
	{
		$md_manager = $this->extension_manager->create_extension_metadata_manager('orynider/pafiledb', $this->template);
		$meta = $md_manager->get_metadata();
		$author_names = array();
		$author_homepages = array();

		foreach (array_slice($meta['authors'], 0, 1) as $author)
		{
			$author_names[] = $author['name'];
			$author_homepages[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $author['homepage'], $author['name']);
		}
		$this->template->assign_vars(array(
			'PAFILEDB_DISPLAY_NAME'		=> $meta['extra']['display-name'],
			'PAFILEDB_AUTHOR_NAMES'		=> implode(' &amp; ', $author_names),
			'PAFILEDB_AUTHOR_HOMEPAGES'	=> implode(' &amp; ', $author_homepages),
		));

		return;
	}

	/**
	* Obtain pafiledb config values
	*/
	public function config_values()
	{
		if (($this->cache->get('pafiledb_config')) === false)
		{
			$pafiledb_config = $pafiledb_cached_config = array();

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . $this->pa_config_table;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$pafiledb_cached_config[$row['config_name']] = $row['config_value'];
				}				

				$pafiledb_config[$row['config_name']] = $row['config_value'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('pafiledb_config', $pafiledb_cached_config);
		}
		else
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . $this->pa_config_table . '
				WHERE is_dynamic = 1';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$pafiledb_config[$row['config_name']] = $row['config_value'];
			}
			$this->db->sql_freeresult($result);
		}		
		
		return $pafiledb_config;
	}
	
	/**
	* Set pafiledb config values
	 *
	 * @param unknown_type $config_name
	 * @param unknown_type $config_value
	 */
	function set_config($key, $new_value, $use_cache = false)
	{
		// Read out config values
		$pafiledb_config = $this->config_values();
		$old_value = !isset($pafiledb_config[$key]) ? $pafiledb_config[$key] : false;		
		$use_cache = (($key == 'comments_pagination') || ($key == 'pagination')) ? true : false;
			
		$sql = 'UPDATE ' . $this->pa_config_table . "
			SET config_value = '" . $this->db->sql_escape($new_value) . "'
			WHERE config_name = '" . $this->db->sql_escape($key) . "'";

		if ($old_value !== false)
		{
			$sql .= " AND config_value = '" . $this->db->sql_escape($old_value) . "'";
		}

		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows() && isset($pafiledb_config[$key]))
		{
			return false;
		}

		if (!isset($pafiledb_config[$key]))
		{
			$sql = 'INSERT INTO ' . $this->pa_config_table . ' ' . $this->db->sql_build_array('INSERT', array(
				'config_name'	=> $key,
				'config_value'	=> $new_value,
				'is_dynamic'	=> ($use_cache) ? 0 : 1));
			$this->db->sql_query($sql);
		}
		
		$pafiledb_config[$key] = $new_value;

		
		if ($use_cache)
		{
			$this->cache->destroy('config');
			$this->cache->put('config', $pafiledb_config);			
		}
		
		return true;		
	}

	/**
	* Obtain pafiledb config values
	 * decapritated
	 * @return unknown
	 */
	function pafiledb_config()
	{
		$pafiledb_config = array();		
		
		$sql = "SELECT *
			FROM " . $this->pa_config_table;

		if ( !( $result = $this->db->sql_query($sql) ) )
		{
			$this->message_die(GENERAL_ERROR, 'Couldnt query pafiledb configuration', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $this->db->sql_fetchrow( $result ) )
		{
			$pafiledb_config[$row['config_name']] = trim( $row['config_value'] );
		}

		$this->db->sql_freeresult($result);

		return ( $pafiledb_config );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $query
	 * @param unknown_type $total
	 * @param unknown_type $offset
	 * @return unknown
	 */
	function sql_query_limit( $query, $total, $offset = 0, $sql_cache = false )
	{
		$query .= ' LIMIT ' . ( ( !empty( $offset ) ) ? $offset . ', ' . $total : $total );
		return $sql_cache ? $this->db->sql_query( $query, $sql_cache ) : $this->db->sql_query( $query );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $file_rating
	 * @return unknown
	 */
	function get_rating( $file_id, $file_rating = '' )
	{
		$sql = "SELECT AVG(rate_point) AS rating
			FROM " . $this->pa_votes_table . "
			WHERE votes_file = '" . $file_id . "'";

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt rating info for the giving file', '', __LINE__, __FILE__, $sql );
		}

		$row = $this->db->sql_fetchrow( $result );
		$this->db->sql_freeresult( $result );
		$file_rating = $row['rating'];

		return ( $file_rating != 0 ) ? round( $file_rating, 2 ) . ' / 10' : $this->user->lang['Not_rated'];
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $page_title
	 */
	function page_header( $page_title )
	{
		// Read out config values
		$pafiledb_config = $this->config_values();		
		
		if ( $action != 'download' )
		{
			//page_header($page_title);
		}
		
		$cat_id = $this->request->variable('cat_id', 0);
		
		if ( $cat_id )
		{
			$upload_url 	= append_sid($this->helper->route('orynider_pafiledb_controller_upload', array('cat_id' => $cat_id)) );
			$mcp_url 		= append_sid($this->helper->route('orynider_pafiledb_controller_mcp', array('cat_id' => $cat_id)) );

			$upload_auth 	= $this->auth_user[$cat_id]['auth_upload'];
			$mcp_auth 		= $this->auth_user[$cat_id]['auth_mod'];
		}
		else
		{
			$upload_url = $this->helper->route('orynider_pafiledb_controller_upload');
			//append_sid( $this->mxurl( "action=user_upload" ) );
			
			// Generate the sub categories list 
			//$cat_list = $this->generate_cat_list(0, 0, '', true, true);
			$cat_list = $this->generate_jumpbox( 0, 0, '', true, true );
			// $upload_auth = (empty($cat_list)) ? FALSE : TRUE;
			
			$upload_auth = false;
			$mcp_auth = false;
			unset( $cat_list );
		}
			
		$this->template->assign_vars( array(
				'L_TITLE' => $title,
				'IS_AUTH_VIEWALL' => ( $pafiledb_config['settings_viewall'] ) ? ( ( $this->auth_global['auth_viewall'] ) ? true : false ) : false,
				'IS_AUTH_SEARCH' => ($this->auth->acl_get('u_pa_files_search')) ? true : false,
				'IS_AUTH_STATS' => ($this->auth->acl_get('u_pa_files_stats')) ? true : false,
				'IS_AUTH_TOPLIST' => ($this->auth->acl_get('u_pa_files_toplist')) ? true : false,

				'IS_AUTH_UPLOAD' => $upload_auth,
				'IS_ADMIN' => ( $this->user->data['user_level'] == ADMIN && $this->user->data['is_registred'] ) ? true : 0,
				'IS_MOD' => $this->auth_user[$cat_id]['auth_mod'],
				'IS_AUTH_MCP' => $mcp_auth,

				'L_OPTIONS' => $this->user->lang['Options'],
				'L_SEARCH' => $this->user->lang['Search'],
				'L_STATS' => $this->user->lang['Statistics'],
				'L_TOPLIST' => $this->user->lang['Toplist'],
				'L_UPLOAD' => $this->user->lang['User_upload'],
				'L_VIEW_ALL' => $this->user->lang['Viewall'],

				'SEARCH_IMG' => $this->templates->img('icon_pa_search', '', false, '', 'src'),
				'STATS_IMG' => $this->templates->img('icon_pa_stats', '', false, '', 'src'),
				'TOPLIST_IMG' => $this->templates->img('icon_pa_toplist', '', false, '', 'src'),
				'UPLOAD_IMG' => $this->templates->img('icon_pa_upload', '', false, '', 'src'),
				'VIEW_ALL_IMG' => $this->templates->img('icon_pa_viewall', '', false, '', 'src'),
				
				'MCP_LINK' => $this->user->lang['MCP_title'],
				'L_NEW_FILE' => 'New File',

				'U_TOPLIST' => append_sid($this->helper->route('orynider_pafiledb_controller_toplist')),
				'U_PASEARCH' => append_sid($this->helper->route('orynider_pafiledb_controller_search')),
				'U_UPLOAD' => $upload_url,
				'U_VIEW_ALL' => append_sid($this->helper->route('orynider_pafiledb_controller_viewall')),
				'U_PASTATS' => append_sid($this->helper->route('orynider_pafiledb_controller_stats')),
				'U_MCP' => $mcp_url,

				'MX_ROOT_PATH' => $this->root_path,
				'BLOCK_ID' => $mx_block->block_id,

				// Buttons
				'B_SEARCH_IMG' => $this->create_button('icon_pa_search', $this->user->lang['Search'], append_sid($this->helper->route('orynider_pafiledb_controller_search'))),
				'B_STATS_IMG' => $this->create_button('icon_pa_stats', $this->user->lang['Statistics'], append_sid($this->helper->route('orynider_pafiledb_controller_stats'))),
				'B_TOPLIST_IMG' => $this->create_button('icon_pa_toplist', $this->user->lang['Toplist'], append_sid($this->helper->route('orynider_pafiledb_controller_toplist'))),
				'B_UPLOAD_IMG' => $this->create_button('icon_pa_upload', $this->user->lang['User_upload'], $upload_url),
				'B_VIEW_ALL_IMG' => $this->create_button('icon_pa_viewall', $this->user->lang['Viewall'], append_sid($this->helper->route('orynider_pafiledb_controller_viewall'))),
				'B_MCP_LINK' => $this->create_button('icon_pa_moderator', $this->user->lang['MCP_title'], $mcp_url),
			));
	}
	

	/**
	 * Enter description here...
	 *
	 */
	function page_footer()
	{
		
		$module_manager = $this->extension_manager->create_extension_metadata_manager('orynider/pafiledb', $this->template);
		$meta = $module_manager->get_metadata();
		$authors_names = array();
		$authors_homepages = array();

		foreach (array_slice($meta['authors'], 0, 1) as $author)
		{
			$authors_names[] = $author['name'];
			$authors_homepages[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $author['homepage'], $author['name']);
		}		
		
		$cat_id = $this->request->variable('cat_id', 0);
		
		
		if ( !MXBB_MODULE || MXBB_27x )
		{
			$pa_module_version = "pafileDB Download Manager v. 0.9.0";
			
			$pa_extension_title = "Ported as phpBB3 Mod and phpBB Extension by ";
			$pa_extension_author = "FlorinCB aka OryNider";
			$pa_extension_years = "2008-2014, 2018";
			$pa_extension_home = "http://mxpcms.sf.net/";			
			
			$pa_module_title = "Ported as MXP-Addon Module by ";
			$pa_module_author = "Jon Ohlsson";
			$pa_module_years = "2005-2008";
			$pa_module_home = "http://www.samskolan.se/";
			
			$pa_module_dm_title = "Some Ideeas :: Download System by ";			
			$pa_module_dm_author = "dmzx";
			$pa_module_dm_years = "2015-2017";
			$pa_module_dm_home = "http://www.dmzx-web.net";
		
			$pa_module_orig_title = "Ported as phpBB Mod by ";			
			$pa_module_orig_author = "Mohd Basri";
			$pa_module_orig_years = "2002-2005";
			$pa_module_dorig_home = "";			
		}		
		
		$dt = $this->user->create_datetime();
		
		if (function_exists('phpbb_format_timezone_offset'))
		{	
			$timezone_offset = $this->user->lang(array('timezones', 'UTC_OFFSET'), phpbb_format_timezone_offset($dt->getOffset()));
		}
		else
		{	
			$timezone_offset = $this->user->lang(array('timezones', 'UTC_OFFSET')) . ', ' . $this->config['board_timezone'];
		}		
		
		$timezone_name = $this->user->timezone->getName();
		
		if (isset($this->user->lang['timezones'][$timezone_name]))
		{
			$timezone_name = $user->lang['timezones'][$timezone_name];
		}
		elseif (isset($this->user->lang[number_format($this->config['board_timezone'])]))
		{
			$timezone_name = $this->user->lang[number_format($this->config['board_timezone'])];
		}		
		
		$timezone = sprintf(isset($this->user->lang['ALL_TIMES']) ? $this->user->lang['ALL_TIMES'] : $this->user->lang['All_times'], $timezone_offset, $timezone_name);				
				
		$this->template->assign_vars( array(
			'L_QUICK_GO' 						=> $this->user->lang['Quick_go'],
			'L_QUICK_NAV' 						=> $this->user->lang['Quick_nav'],
			'L_QUICK_JUMP' 						=> $this->user->lang['Quick_jump'],
			
			'JUMPMENU' 							=> $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ) ),
			'S_JUMPBOX_ACTION' 					=> append_sid($this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' => $cat_id)) ),

			'S_AUTH_LIST' 						=> $this->auth_can_list,

			'MX_PAGE' 							=> $page_id,
			
			'MODULE_VERSION' 					=> $pa_module_version,			
			'MODULE_TITLE' 						=> $pa_module_title,
			'MODULE_AUTHOR' 					=> $pa_module_author,
			'MODULE_YEARS' 						=> $pa_module_years,
			'MODULE_AUTHOR_HOMEPAGE' 			=> $pa_module_home,
			
			'DISPLAY_NAME'						=> $meta['extra']['display-name'],
			'AUTHORS_NAMES'						=> implode(' &amp; ', $authors_names),
			'AUTHOR_HOMEPAGE'					=> implode(' &amp; ', $authors_homepages),			

			'DOWNLOADSYSTEM_DISPLAY_NAME'		=> $pa_module_dm_title,
			'DOWNLOADSYSTEM_AUTHOR_NAMES'		=> $pa_module_dm_author,
			'DOWNLOADSYSTEM_AUTHOR_YEARS'		=> $pa_module_dm_years,			
			'DOWNLOADSYSTEM_AUTHOR_HOMEPAGES'	=> $pa_module_dm_home,		
			
			'EXTENSION_TITLE' 					=> $pa_extension_title,
			'EXTENSION_AUTHOR' 					=> $pa_extension_author,
			'EXTENSION_YEARS' 					=> $pa_extension_years,		
			'EXTENSION_AUTHOR_HOMEPAGE' 		=> $pa_extension_home,
			
			'MODULE_ORIG_TITLE' 				=> $pa_module_orig_title,
			'MODULE_ORIG_AUTHOR' 				=> $pa_module_orig_author,
			'MODULE_ORIG_YEARS' 				=> $pa_module_orig_years,						
			'MODULE_ORIG_HOMEPAGE' 				=> $pa_module_orig_home,
			
			'S_TIMEZONE' 						=>  $timezone)
		);

		$this->_pafiledb();

		if ( !MXBB_MODULE || MXBB_27x )
		{
			$this->template->assign_block_vars( 'copy_footer', array() );
		}

		if ( !isset( $_GET['explain'] ) )
		{
			//$this->template->pparse( 'body' );
		}

		$this->pafiledb_cache->unload();

		if ( $action != 'download' )
		{
			if ( !$is_block )
			{
				//page_footer();
			}
		}
	}

	/**
	 * Dummy function
	 */
	function message_die($msg_code, $msg_text = '', $msg_title = '', $err_line = '', $err_file = '', $sql = '')
	{		
		//
		// Get SQL error if we are debugging. Do this as soon as possible to prevent
		// subsequent queries from overwriting the status of sql_error()
		//
		if (DEBUG && ($msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR))
		{
				
			if ( isset($sql) )
			{
				//$sql_error = array(@print_r(@$this->db->sql_error($sql)));				
				$sql_error['message'] = $sql_error['message'] ? $sql_error['message'] : '<br /><br />SQL : ' . $sql; 
				$sql_error['code'] = $sql_error['code'] ? $sql_error['code'] : 0;			
			}
			else
			{
				$sql_error = array(@print_r(@$this->db->sql_error_returned));				
				$sql_error['message'] = $sql_error['message'] ? $sql_error['message'] : '<br /><br />SQL : ' . $sql; 
				$sql_error['code'] = $sql_error['code'] ? $sql_error['code'] : 0;					
			}			
			
			$debug_text = '';

			if ( isset($sql_error['message']) )
			{
				$debug_text .= '<br /><br />SQL Error : ' . $sql_error['code'] . ' ' . $sql_error['message'];
			}

			if ( isset($sql_store) )
			{
				$debug_text .= "<br /><br />$sql_store";
			}

			if ( isset($err_line) && isset($err_file) )
			{
				$debug_text .= '</br /><br />Line : ' . $err_line . '<br />File : ' . $err_file;
			}
		}		
		
		switch($msg_code)
		{
			case GENERAL_MESSAGE:
				if ( $msg_title == '' )
				{
					$msg_title = $this->user->lang('Information');
				}
			break;

			case CRITICAL_MESSAGE:
				if ( $msg_title == '' )
				{
					$msg_title = $this->user->lang('Critical_Information');
				}
			break;

			case GENERAL_ERROR:
				if ( $msg_text == '' )
				{
					$msg_text = $this->user->lang('An_error_occured');
				}

				if ( $msg_title == '' )
				{
					$msg_title = $this->user->lang('General_Error');
				}
			break;

			case CRITICAL_ERROR:

				if ($msg_text == '')
				{
					$msg_text = $this->user->lang('A_critical_error');
				}

				if ($msg_title == '')
				{
					$msg_title = 'phpBB : <b>' . $this->user->lang('Critical_Error') . '</b>';
				}
			break;
		}
		
		//
		// Add on DEBUG info if we've enabled debug mode and this is an error. This
		// prevents debug info being output for general messages should DEBUG be
		// set TRUE by accident (preventing confusion for the end user!)
		//
		if ( DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
		{
			if ( $debug_text != '' )
			{
				$msg_text = $msg_text . '<br /><br /><b><u>DEBUG MODE</u></b> ' . $debug_text;
			}
		}		
		
		trigger_error($msg_title . ': ' . $msg_text);
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_posticon
	 * @return unknown
	 */
	function post_icons( $file_posticon = '' )
	{
		$curicons = 1;

		if ( $file_posticon == 'none' || $file_posticon == 'none.gif' or empty( $file_posticon ) )
		{
			$posticons .= '<input type="radio" name="posticon" value="none" checked><a class="gensmall">' . $this->user->lang['None'] . '</a>&nbsp;';
		}
		else
		{
			$posticons .= '<input type="radio" name="posticon" value="none"><a class="gensmall">' . $this->user->lang['None'] . '</a>&nbsp;';
		}

		$handle = @opendir( $this->module_root_path . ICONS_DIR );

		while ( $icon = @readdir( $handle ) )
		{
			if ( $icon !== '.' && $icon !== '..' && $icon !== 'index.htm' )
			{
				if ( $file_posticon == $icon )
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '" checked><img src="' . $this->module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}
				else
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '"><img src="' . $this->module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}

				$curicons++;

				if ( $curicons == 8 )
				{
					$posticons .= '<br>';
					$curicons = 0;
				}
			}
		}
		@closedir( $handle );
		return $posticons;
	}

	/**
	 * Create buttons.
	 *
	 * You can create code for buttons:
	 * 1) Simple textlinks (MX_BUTTON_TEXT)
	 * 2) Standard image links (MX_BUTTON_IMAGE)
	 * 3) Generic buttons, with spanning text on top background image (MX_BUTTON_GENERIC)
	 *
	 * Note: The rollover feature is done using a css shift technique, so you do not need separate images
	 *
	 * @param unknown_type $type
	 * @param unknown_type $label
	 * @param unknown_type $url
	 * @param unknown_type $img
	 */
	function create_button($key, $label, $url)
	{
		$this_buttontype = MX_BUTTON_IMAGE;

		switch($this_buttontype)
		{
			case MX_BUTTON_TEXT:
				return '<a class="textbutton" href="'. $url .'"><span>' . $label . '</span></a>';
			break;

			case MX_BUTTON_IMAGE:
				return '<a class="imagebutton" href="'. $url .'"><img src = "' . $this->templates->img($key, $label, false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
			break;

			case MX_BUTTON_GENERIC:
				return '<a class="genericbutton" href="'. $url .'"><span>' . $label . '</span></a>';
			break;

			default:
				return '<a class="imagebutton" href="'. $url .'"><img src = "' . $this->templates->img($key, $label, false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
			break;
		}
	}

	/**
	 * Create icons.
	 *
	 * You can create code for icons:
	 * 1) Simple textlinks (MX_BUTTON_TEXT)
	 * 2) Standard image links (MX_BUTTON_IMAGE)
	 * 3) Generic buttons, with spanning text on top background image (MX_BUTTON_GENERIC)
	 *
	 * Note: The rollover feature is done using a css shift technique, so you do not need separate images
	 *
	 * @param unknown_type $type
	 * @param unknown_type $label
	 * @param unknown_type $url
	 * @param unknown_type $img
	 */
	function create_icon($key, $label, $url)
	{
		$this_buttontype = MX_BUTTON_IMAGE;

		switch($this_buttontype)
		{
			case MX_BUTTON_TEXT:
				return '<a class="textbutton" href="'. $url .'"><span>' . $label . '</span></a>';
			break;

			case MX_BUTTON_IMAGE:
				return '<a class="imagebutton" href="'. $url .'"><img src = "' . $this->templates->img($key, '', false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
			break;

			case MX_BUTTON_GENERIC:
				return '<a class="genericbutton" href="'. $url .'"><span>' . $label . '</span></a>';
			break;

			default:
				return '<a class="imagebutton" href="'. $url .'"><img src = "' . $this->templates->img($key, '', false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
			break;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $license_id
	 * @return unknown
	 */
	function license_list( $license_id = 0 )
	{
		if ( $license_id == 0 )
		{
			$list .= '<option calue="0" selected>' . $this->user->lang['None'] . '</option>';
		}
		else
		{
			$list .= '<option calue="0">' . $this->user->lang['None'] . '</option>';
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_license_table . '
			ORDER BY license_id';

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
		}

		while ( $license = $this->db->sql_fetchrow( $result ) )
		{
			if ( $license_id == $license['license_id'] )
			{
				$list .= '<option value="' . $license['license_id'] . '" selected>' . $license['license_name'] . '</option>';
			}
			else
			{
				$list .= '<option value="' . $license['license_id'] . '">' . $license['license_name'] . '</option>';
			}
		}
		return $list;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_type
	 * @return unknown
	 */
	function gen_unique_name( $file_type )
	{
		// Read out config values
		$pafiledb_config = $this->config_values();			
		
		srand( ( double )microtime() * 1000000 ); // for older than version 4.2.0 of PHP

		do
		{
			$filename = md5( uniqid( rand() ) ) . $file_type;
		}
		while ( file_exists( $pafiledb_config['upload_dir'] . '/' . $filename ) );

		return $filename;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	 */
	function get_extension( $filename )
	{
		//return strtolower( array_pop( explode( '.', $filename ) ) );
		return strtolower( array_pop( $array = (explode( '.', $filename ))) );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $userfile
	 * @param unknown_type $userfile_name
	 * @param unknown_type $userfile_size
	 * @param unknown_type $upload_dir
	 * @param unknown_type $local
	 * @return unknown
	 */
	function upload_file( $userfile, $userfile_name, $userfile_size, $upload_dir = '', $local = false )
	{
		// Read out config values
		$pafiledb_config = $this->config_values();			
		
		@set_time_limit( 0 );
		$file_info = array();

		$file_info['error'] = false;

		if ( file_exists( $this->module_root_path . $upload_dir . $userfile_name ) )
		{
			$userfile_name = time() . $userfile_name;
		}
		// =======================================================
		// if the file size is more than the allowed size another error message
		// =======================================================
		if ( $userfile_size > $pafiledb_config['max_file_size'] && ( $this->auth_user[$cat_id]['auth_mod'] || $this->user->data['user_level'] != ADMIN ) && $this->user->data['session_logged_in'] )
		{
			$file_info['error'] = true;
			if ( !empty( $file_info['message'] ) )
			{
				$file_info['message'] .= '<br>';
			}
			$file_info['message'] .= $this->user->lang['Filetoobig'];
		}
		// =======================================================
		// Then upload the file, and check the php version
		// =======================================================
		else
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			$upload_mode = ( @$ini_val( 'open_basedir' ) || @$ini_val( 'safe_mode' ) ) ? 'move' : 'copy';
			$upload_mode = ( $local ) ? 'local' : $upload_mode;

			if ( $this->do_upload_file( $upload_mode, $this->userfile, $this->module_root_path . $upload_dir . $this->userfile_name ) )
			{
				$file_info['error'] = true;
				if ( !empty( $file_info['message'] ) )
				{
					$file_info['message'] .= '<br>';
				}
				$file_info['message'] .= 'Couldn\'t Upload the File.';
			}
			$file_info['url'] = $this->get_formated_url() . '/' . $this->module_root_path . $upload_dir . $this->userfile_name;
		}
		return $file_info;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $upload_mode
	 * @param unknown_type $userfile
	 * @param unknown_type $userfile_name
	 * @return unknown
	 */
	function do_upload_file( $upload_mode, $userfile, $userfile_name )
	{
		switch ( $upload_mode )
		{
			case 'copy':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					if ( !@move_uploaded_file( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $this->userfile_name, 0666 );
				break;

			case 'move':
				if ( !@move_uploaded_file( $userfile, $userfile_name ) )
				{
					if ( !@copy( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $userfile_name, 0666 );
				break;

			case 'local':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					return false;
				}
				@chmod( $userfile_name, 0666 );
				@unlink( $userfile );
				break;
		}

		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $file_data
	 * @return unknown
	 */
	function get_file_size( $file_id, $file_data = '' )
	{
		// Read out config values
		$pafiledb_config = $this->config_values();	
		
		$directory = $this->module_root_path . $pafiledb_config['upload_dir'];

		if ( empty( $file_data ) )
		{
			$sql = "SELECT file_dlurl, file_size, unique_name, file_dir
				FROM " . $this->pa_files_table . "
				WHERE file_id = '" . $file_id . "'";

			if ( !( $result = $this->db->sql_query( $sql ) ) )
			{
				$this->message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $this->db->sql_fetchrow( $result );

			$this->db->sql_freeresult( $result );
		}

		$file_url = $file_data['file_dlurl'];
		$file_size = $file_data['file_size'];

		$formated_url = $this->get_formated_url();
		$html_path = $formated_url . '/' . $directory;
		$update_file_size = false;

		if ( ( ( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) || !empty( $file_data['unique_name'] ) ) && empty( $file_size ) )
		{
			$file_url = basename( $file_url ) ;
			$file_name = basename( $file_url );

			if ( ( !empty( $file_data['unique_name'] ) ) && ( !file_exists( $this->module_root_path . $file_data['file_dir'] . $file_data['unique_name'] ) ) )
			{
				return $this->user->lang['Not_available'];
			}

			if ( empty( $file_data['unique_name'] ) )
			{
				$file_size = @file_size( $directory . $file_name );
			}
			else
			{
				$file_size = @file_size( $this->module_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
			}

			$update_file_size = true;
		}
		elseif ( empty( $file_size ) && ( ( !( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) ) || empty( $file_data['unique_name'] ) ) )
		{
			$ourhead = "";
			$url = parse_url( $file_url );
			$host = $url['host'];
			$path = $url['path'];
			$port = ( !empty( $url['port'] ) ) ? $url['port'] : 80;
			$errno = ''; 
			$errstr = '';

			$fp = @fsockopen( $host, $port, $errno, $errstr, 20 );

			if ( !$fp )
			{
				return $this->user->lang['Not_available'];
			}
			else
			{
				fputs( $fp, "HEAD $file_url HTTP/1.1\r\n" );
				fputs( $fp, "HOST: $host\r\n" );
				fputs( $fp, "Connection: close\r\n\r\n" );

				while ( !feof( $fp ) )
				{
					$ourhead = sprintf( '%s%s', $ourhead, fgets ( $fp, 128 ) );
				}
			}
			@fclose ( $fp );

			$split_head = explode( 'Content-Length: ', $ourhead );

			$file_size = round( abs( $split_head[1] ) );
			$update_file_size = true;
		}
		
		if ( !$file_size )
		{
			//Check if file is not hosted on same domain relative to mx_root_path
			if (file_exists(str_replace(PORTAL_URL, "./", $file_url)))
			{
				$file_size = file_size(str_replace(PORTAL_URL, "./", $file_url));
			}
			elseif  (file_exists($mx_root_path . $this->module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url)))
			{			
				$file_size = file_size($mx_root_path . $this->module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url));
			}				
			else
			{
				return $this->user->lang['Not_available'];
			}				
		}		

		if ( $update_file_size )
		{
			$sql = 'UPDATE ' . $this->pa_files_table . "
				SET file_size = '$file_size'
				WHERE file_id = '$file_id'";

			if ( !( $this->db->sql_query( $sql ) ) )
			{
				$this->message_die( GENERAL_ERROR, 'Could not update file_size', '', __LINE__, __FILE__, $sql );
			}
		}

		if ( $file_size < 1024 )
		{
			$file_size_out = intval( $file_size ) . ' ' . $this->user->lang['Bytes'];
		}
		if ( $file_size >= 1025 )
		{
			$file_size_out = round( intval( $file_size ) / 1024 * 100 ) / 100 . ' ' . $this->user->lang['KB'];
		}
		if ( $file_size >= 1048575 )
		{
			$file_size_out = round( intval( $file_size ) / 1048576 * 100 ) / 100 . ' ' . $this->user->lang['MB'];
		}

		return $file_size_out;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	 */
	function pafiledb_unlink( $filename )
	{
		// Read out config values
		$pafiledb_config = $this->config_values();

		$deleted = @unlink( $filename );

		if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
		{
			$filesys = eregi_replace( '/', '\\', $filename );
			$deleted = @system( "del $filesys" );

			if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
			{
				$deleted = @chmod ( $filename, 0775 );
				$deleted = @unlink( $filename );
				$deleted = @system( "del $filesys" );
			}
		}

		return ( $deleted );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function pafiledb_realpath( $path )
	{
		global $phpbb_root_path;

		return ( !@function_exists( 'realpath' ) || !@realpath( $phpbb_root_path . 'includes/functions.' . $this->php_ext ) ) ? $path : @realpath( $path );
	}
	
	/**
	* Create date/time from format and timezone
	* from phpBB2
	 */	
	function create_date($format, $gmepoch, $tz)
	{		
		static $translate;
		static $midnight;
		static $date_cache;
		
		$this->config['user_timezone'] = !empty($this->config['user_timezone']) ? $this->config['user_timezone'] : $this->config['board_timezone'];
		$this->data['user_dst'] = !empty($this->data['user_dst']) ? $this->data['user_dst'] : $this->data['user_timezone'];
		
		$this->date_format = $this->config['default_dateformat'];
		$this->timezone = $this->config['user_timezone'] * 3600;
		$this->dst = $this->data['user_timezone'] * 3600;		

		$format = (!$format) ? $this->user->date_format : $format;
		$now = time();
		$delta = $now - $gmepoch;

		if (!isset($date_cache[$format]))
		{
			// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
			$date_cache[$format] = array(
				'is_short'		=> strpos($format, '|'),
				'format_short'	=> substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
				'format_long'	=> str_replace('|', '', $format),
				// Filter out values that are not strings (e.g. arrays) for strtr().
				'lang'			=> array_filter($this->user->lang['datetime'], 'is_string'),
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				$date_cache[$format]['lang']['May'] = $this->user->lang['datetime']['May_short'];
			}
		}

		// Zone offset
		$zone_offset = $this->timezone + $this->dst;
		
		// Show date <= 1 hour ago as 'xx min ago' but not greater than 60 seconds in the future
		// A small tolerence is given for times in the future but in the same minute are displayed as '< than a minute ago'
		if ($delta <= 3600 && $delta > -60 && ($delta >= -5 || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && $date_cache[$format]['is_short'] !== false && !$forcedate && isset($this->lang['datetime']['AGO']))
		{
			return $this->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
		}

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $zone_offset));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $zone_offset;
		}

		if ($date_cache[$format]['is_short'] !== false && !$forcedate && !($gmepoch < $midnight - 86400 || $gmepoch > $midnight + 172800))
		{
			$day = false;

			if ($gmepoch > $midnight + 86400)
			{
				$day = 'TOMORROW';
			}
			else if ($gmepoch > $midnight)
			{
				$day = 'TODAY';
			}
			else if ($gmepoch > $midnight - 86400)
			{
				$day = 'YESTERDAY';
			}

			if ($day !== false)
			{
				return str_replace('||', $this->user->lang['datetime'][$day], strtr(@gmdate($date_cache[$format]['format_short'], $gmepoch + $zone_offset), $date_cache[$format]['lang']));
			}
		}	
		
		if (empty($translate) && $this->templates->decode_lang($this->config['default_lang']) != 'english')
		{
			@reset($lang['datetime']);
			while (list($match, $replace) = @each($lang['datetime']))
			{
				$translate[$match] = $replace;
			}
		}

		return (is_array($translate)) ? strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $translate) : strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $date_cache[$format]['lang']);
	}	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $args
	 * @param unknown_type $force_standalone_mode
	 * @param unknown_type $non_html_amp
	 * @return unknown
	 */
	function mxurl($args = '', $force_standalone_mode = false, $non_html_amp = false, $is_block = true)
	{
		$pageId = $this->request->variable('page_id', 1);
		$dynamicId = !empty($_GET['dynamic_block']) ? ( $non_html_amp ? '&dynamic_block=' : '&amp;dynamic_block=' ) . $this->request->variable('dynamic_block') : '';
		
		$args = $args ? $args : func_get_args();		
		$args .= ($args == '' ? '' : '&' ) . 'modrewrite=no';
		
		if ( !MXBB_MODULE )
		{
			// Incomplete update?
			if (phpbb_version_compare('3.1', PHPBB_VERSION, '<'))
			{
				$mxurl = $this->module_root_path . 'dload.' . $this->php_ext . ( $args == '' ? '' : '?' . $args );					
			}
			else			
			{
				$mxurl = generate_board_url() . ( $args == '' ? '' : '?' . $args );					
			}			
			return $mxurl;
		}

		if ( $force_standalone_mode || !$is_block )
		{
			$mxurl = $this->module_root_path . 'dload.' . $this->php_ext . ( $args == '' ? '' : '?' . $args );
		}
		else
		{
			$mxurl = $this->mx_root_path . 'index.' . $this->php_ext;
			if ( is_numeric( $pageId ) )
			{
				$mxurl .= '?page=' . $pageId . $dynamicId . ( $args == '' ? '' : ( $non_html_amp ? '&' : '&amp;' ) . $args );
			}
			else
			{
				$mxurl .= ( $args == '' ? '' : '?' . $args );
			}
		}
		return $mxurl;
	}	

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function get_formated_url()
	{
		$server_protocol = ( $this->config['cookie_secure'] ) ? 'https://' : 'http://';
		$server_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $this->config['server_name'] ) );
		$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim( $this->config['server_port'] ) : '';

		$formated_url = $server_protocol . $server_name . $server_port;
		
		$formated_url = function_exists('generate_board_url') ? generate_board_url() . '/' : $formated_url;
		
		return $formated_url;
	}	
	/**
	* Post download announcement
	*/
	public function create_announcement($download_subject, $download_msg, $forum_id)
	{
		// Read out config values
		$pafiledb_config = $this->config_values();

		$lock = $pafiledb_config['comments_lock_enable'];

		$this->message_parser = new \parse_message();

		$subject =	$download_subject;
		$text =	$download_msg;

		// Do not try to post message if subject or text is empty
		if (empty($subject) || empty($text))
		{
			return;
		}

		$this->message_parser->message = $text;

		// Grab md5 'checksum' of new message
		$message_md5 = md5($this->message_parser->message);

		$this->message_parser->parse(true, true, true, true, false, true, true);

		$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		$forum_name = $this->db->sql_fetchfield('forum_name');
		$this->db->sql_freeresult($result);

		$data = array(
			'forum_id'			=> $forum_id,
			'icon_id'			=> false,
			'poster_id' 		=> $this->user->data['user_id'],
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> true,
			'enable_sig'		=> true,
			'message'			=> $this->message_parser->message,
			'message_md5'		=> $message_md5,
			'attachment_data'	=> 0,
			'filename_data'		=> 0,
			'bbcode_bitfield'	=> $this->message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $this->message_parser->bbcode_uid,
			'poster_ip'			=> $this->user->ip,
			'post_edit_locked'	=> 0,
			'topic_title'		=> $subject,
			'topic_status'		=> $lock,
			'notify_set'		=> false,
			'notify'			=> true,
			'post_time' 		=> time(),
			'forum_name'		=> $forum_name,
			'enable_indexing'	=> true,
			'force_approved_state'	=> true,
			'force_visibility' 	=> true,
			'attr_id'			=> 0,
		);
		$poll = array();

		submit_post('post', $subject, '', POST_NORMAL, $poll, $data);
	}

	/**
	* Allowed Extensions
	*/
	public function allowed_extensions()
	{
		// Always use lower and upper case extensions
		$allowed_extensions = array();
		
		// Archive extenstions
		$allowed_extensions[] = 'zip';
		$allowed_extensions[] = 'ZIP';
		$allowed_extensions[] = 'rar';
		$allowed_extensions[] = 'RAR';
		$allowed_extensions[] = '7z';
		$allowed_extensions[] = '7Z';
		$allowed_extensions[] = 'ace';
		$allowed_extensions[] = 'ACE';
		$allowed_extensions[] = 'gtar';
		$allowed_extensions[] = 'GTAR';
		$allowed_extensions[] = 'gz';
		$allowed_extensions[] = 'GZ';
		$allowed_extensions[] = 'tar';
		$allowed_extensions[] = 'TAR';
		
		// Text files
		$allowed_extensions[] = 'txt';
		$allowed_extensions[] = 'TXT';
		
		// Documents
		$allowed_extensions[] = 'doc';
		$allowed_extensions[] = 'DOC';
		$allowed_extensions[] = 'docx';
		$allowed_extensions[] = 'DOCX';
		$allowed_extensions[] = 'xls';
		$allowed_extensions[] = 'XLS';
		$allowed_extensions[] = 'xlsx';
		$allowed_extensions[] = 'XLSX';
		$allowed_extensions[] = 'ppt';
		$allowed_extensions[] = 'PPT';
		$allowed_extensions[] = 'pptx';
		$allowed_extensions[] = 'PPTX';
		$allowed_extensions[] = 'pdf';
		$allowed_extensions[] = 'PDF';
		
		// Real Media files
		$allowed_extensions[] = 'ram';
		$allowed_extensions[] = 'RAM';
		$allowed_extensions[] = 'rm';
		$allowed_extensions[] = 'RM';
		
		// Windows Media files
		$allowed_extensions[] = 'wma';
		$allowed_extensions[] = 'WMA';
		$allowed_extensions[] = 'wmv';
		$allowed_extensions[] = 'WMV';
		
		// Flash files
		$allowed_extensions[] = 'swf';
		$allowed_extensions[] = 'SWF';
		
		// Quick time files
		$allowed_extensions[] = 'mov';
		$allowed_extensions[] = 'MOV';
		$allowed_extensions[] = 'mp4';
		$allowed_extensions[] = 'MP4';
		
		// Different files
		$allowed_extensions[] = 'mp3';
		$allowed_extensions[] = 'MP3';
		$allowed_extensions[] = 'mpeg';
		$allowed_extensions[] = 'MPEG';
		$allowed_extensions[] = 'mpg';
		$allowed_extensions[] = 'MPG';
		
		// GD2 Supported image files
		$allowed_extensions[] = 'gif';
		$allowed_extensions[] = 'GIF';
		
		$allowed_extensions[] = 'jpg';
		$allowed_extensions[] = 'JPG';
		$allowed_extensions[] = 'jpeg';
		$allowed_extensions[] = 'JPEG';
		
		$allowed_extensions[] = 'png';
		$allowed_extensions[] = 'PNG';

		$allowed_extensions[] = 'webp';
		$allowed_extensions[] = 'xbm';
		$allowed_extensions[] = 'xpm';
		$allowed_extensions[] = 'wbmp';
		
		$allowed_extensions[] = 'gd';
		$allowed_extensions[] = 'gd2';
		
		// Scanner image files		
		$allowed_extensions[] = 'tif';
		$allowed_extensions[] = 'TIF';
		$allowed_extensions[] = 'tiff';
		$allowed_extensions[] = 'TIFF';
		
		// Icon image files i.e. for favicon and webdesign
		$allowed_extensions[] = 'ico';
		$allowed_extensions[] = 'ICO';
		
		$allowed_extensions[] = 'psd';
		$allowed_extensions[] = 'PSD';
					
		return $allowed_extensions;
	}

	/**
	*  Check Forbidden Extension
	*/
	public function forbidden_extension($file_realname = '')
	{
		// Read out config values
		$pafiledb_config = $this->config_values();		
		
		// Always use lower and upper case extensions
		$allowed_extensions = $this->allowed_extensions();
		
		$forbidden_extensions = array_map( 'trim', @explode( ',', $pafiledb_config['forbidden_extensions'] ) );

		$file_extension = $this->get_extension( $file_realname );
		
		//'You are not allowed to upload this type of files';
		if ( in_array( $file_extension, $forbidden_extensions ) )
		{
			return true;
		}		
			
		return false;
	}
	
	/**
	 * auth_can.
	 *
	 * @param unknown_type $cat_id
	 */
	function auth_can($cat_id)
	{
		global $lang;
		
		$this->auth_can_list = '<br />' . ( ( $this->auth_user[$cat_id]['auth_upload'] ) ? $lang['PA_Rules_upload_can'] : $lang['PA_Rules_upload_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth_user[$cat_id]['auth_view_file'] ) ? $lang['PA_Rules_view_file_can'] : $lang['PA_Rules_view_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth_user[$cat_id]['auth_edit_file'] ) ? $lang['PA_Rules_edit_file_can'] : $lang['PA_Rules_edit_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth_user[$cat_id]['auth_delete_file'] ) ? $lang['PA_Rules_delete_file_can'] : $lang['PA_Rules_delete_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->comments[$cat_id]['activated'] ? ( ( $this->auth_user[$cat_id]['auth_view_comment'] ? $lang['PA_Rules_view_comment_can'] : $lang['PA_Rules_view_comment_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->comments[$cat_id]['activated'] ? ( ( $this->auth_user[$cat_id]['auth_post_comment'] ? $lang['PA_Rules_post_comment_can'] : $lang['PA_Rules_post_comment_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->ratings[$cat_id]['activated'] ? ( ( $this->auth_user[$cat_id]['auth_rate'] ? $lang['PA_Rules_rate_can'] : $lang['PA_Rules_rate_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->auth_user[$cat_id]['auth_download'] ) ? $lang['PA_Rules_download_can'] : $lang['PA_Rules_download_cannot'] ) . '<br />';

		if ( $this->auth_user[$cat_id]['auth_mod'] )
		{
			$this->auth_can_list .= $lang['PA_Rules_moderate_can'];
		}
	}	
}
