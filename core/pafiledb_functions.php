<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions.php,v 1.62 2012/01/09 06:58:15 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * pafiledb_functions.
 *
 * This class is used for general pa handling
 *
 * @access public
 * @author Jon Ohlsson
 *
 */
class pafiledb_functions
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

	/** @var \orynider\pafiledb\core\ pafiledb */
	protected $pafiledb;	

	/** @var \orynider\pafiledb\core\pafiledb_cache */
	protected $pafiledb_cache;
	
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
	* @param \orynider\pafiledb\core\pafiledb			$pafiledb		
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
		\orynider\pafiledb\core\pafiledb $pafiledb,		
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
		$this->pafiledb 			= $pafiledb;		
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
		
		$this->ext_name 			= $this->request->variable('ext_name', 'orynider/pafiledb');
		$this->module_root_path		= $this->ext_path = $extension_manager->get_extension_path($this->ext_name, true);	
	}		
	
	/**
	 * This class is used for general pafiledb handling
	 *
	 * @param unknown_type $config_name
	 * @param unknown_type $config_value
	 */
	function set_config($config_name, $config_value)
	{
		global $db, $pafiledb_cache, $pafiledb_config;

		$sql = 'UPDATE ' . PA_CONFIG_TABLE . "
			SET config_value = '" . $db->sql_escape($config_value) . "'
			WHERE config_name = '" . $db->sql_escape($config_name) . "'";

		if (!@$db->sql_query($sql) && !isset($pafiledb_config[$config_name]))
		{
			$sql = 'INSERT INTO ' . PA_CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'config_name'	=> $config_name,
				'config_value'	=> $config_value));
			if (!@$db->sql_query($sql))
			{
				mx_message_die( GENERAL_ERROR, "Failed to update pafiledb configuration for $config_name", "", __LINE__, __FILE__, $sql );
			}
		}

		$pafiledb_config[$config_name] = $config_value;
		$pafiledb_cache->put( 'config', $pafiledb_config );
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function pafiledb_config()
	{
		global $db;

		$sql = "SELECT *
			FROM " . PA_CONFIG_TABLE;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt query pafiledb configuration', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$pafiledb_config[$row['config_name']] = trim( $row['config_value'] );
		}

		$db->sql_freeresult( $result );

		return ( $pafiledb_config );
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function obtain_pafiledb_config($use_cache = true)
	{
		global $db, $pafiledb_cache;
		
		if (($pafiledb_config = $pafiledb_cache->get('config')) && ($use_cache))
		{
			return $config;
		}
		else
		{		
			$sql = "SELECT *
				FROM " . PA_CONFIG_TABLE;

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				if (!function_exists('mx_message_die'))
				{
					die("Couldnt query pafiledb configuration, Allso this hosting or server is using a cache optimizer not compatible with MX-Publisher or just lost connection to database wile query.");
				}
				else
				{
					mx_message_die( GENERAL_ERROR, 'Couldnt query portal configuration', '', __LINE__, __FILE__, $sql );
				}
			}

			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$pafiledb_config[$row['config_name']] = trim( $row['config_value'] );
			}

			$db->sql_freeresult($result);

			$pafiledb_cache->put('config', $pafiledb_config);		

			return($pafiledb_config);
		}			
	}	

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $mode
	 * @param unknown_type $page_id
	 */
	/*
	function generate_smilies( $mode, $page_id )
	{
		global $db, $board_config, $template, $lang, $images, $theme, $phpEx, $phpbb_root_path;
		global $user_ip, $session_length, $starttime;
		global $userdata, $mx_user;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$inline_columns = 4;
		$inline_rows = 5;
		$window_columns = 8;

		if ( $mode == 'window' )
		{
			if ( !MXBB_MODULE )
			{
				$userdata = session_pagestart( $user_ip, $page_id );
				init_userprefs( $userdata );
			}
			else
			{
				$mx_user->init($user_ip, PAGE_INDEX);
			}

			$gen_simple_header = true;

			$page_title = $lang['Review_topic'] . " - $topic_title";

			include( $mx_root_path . 'includes/page_header.' . $phpEx );

			$template->set_filenames( array( 'smiliesbody' => 'posting_smilies.tpl' ) );
		}

		$sql = "SELECT emoticon, code, smile_url
			FROM " . SMILIES_TABLE . "
			ORDER BY smilies_id";
		if ( $result = $db->sql_query( $sql ) )
		{
			$num_smilies = 0;
			$rowset = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				if ( empty( $rowset[$row['smile_url']] ) )
				{
					$rowset[$row['smile_url']]['code'] = str_replace( "'", "\\'", str_replace( '\\', '\\\\', $row['code'] ) );
					$rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
					$num_smilies++;
				}
			}

			if ( $num_smilies )
			{
				$smilies_count = ( $mode == 'inline' ) ? min( 19, $num_smilies ) : $num_smilies;
				$smilies_split_row = ( $mode == 'inline' ) ? $inline_columns - 1 : $window_columns - 1;

				$s_colspan = 0;
				$row = 0;
				$col = 0;

				while ( list( $smile_url, $data ) = @each( $rowset ) )
				{
					if ( !$col )
					{
						$template->assign_block_vars( 'smilies_row', array() );
					}

					$template->assign_block_vars( 'smilies_row.smilies_col', array(
						'SMILEY_CODE' => $data['code'],
						'SMILEY_IMG' => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smile_url,
						'SMILEY_DESC' => $data['emoticon']
					));

					$s_colspan = max( $s_colspan, $col + 1 );

					if ( $col == $smilies_split_row )
					{
						if ( $mode == 'inline' && $row == $inline_rows - 1 )
						{
							break;
						}
						$col = 0;
						$row++;
					}
					else
					{
						$col++;
					}
				}

				if ( $mode == 'inline' && $num_smilies > $inline_rows * $inline_columns )
				{
					$template->assign_block_vars( 'switch_smilies_extra', array() );

					$template->assign_vars( array(
						'L_MORE_SMILIES' => $lang['More_emoticons'],
						'U_MORE_SMILIES' => mx_append_sid( $phpbb_root_path . "posting.$phpEx?mode=smilies" )
					));
				}

				$template->assign_vars( array(
					'L_EMOTICONS' => $lang['Emoticons'],
					'L_CLOSE_WINDOW' => $lang['Close_window'],
					'S_SMILIES_COLSPAN' => $s_colspan
				));
			}
		}

		if ( $mode == 'window' )
		{
			$template->pparse( 'smiliesbody' );
			include( $mx_root_path . 'includes/page_tail.' . $phpEx );
		}
	}
	*/

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
		global $db;

		$query .= ' LIMIT ' . ( ( !empty( $offset ) ) ? $offset . ', ' . $total : $total );
		return $sql_cache ? $db->sql_query( $query, $sql_cache ) : $db->sql_query( $query );
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
		global $db, $lang;

		$sql = "SELECT AVG(rate_point) AS rating
			FROM " . PA_VOTES_TABLE . "
			WHERE votes_file = '" . $file_id . "'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt rating info for the giving file', '', __LINE__, __FILE__, $sql );
		}

		$row = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );
		$file_rating = $row['rating'];

		return ( $file_rating != 0 ) ? round( $file_rating, 2 ) . ' / 10' : $lang['Not_rated'];
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
		
		$cat_id = $this->request->variable('cat_id', 0);		
		
		if ( $action != 'download' )
		{
			//page_header($page_title);
		}
		
		if ( $cat_id )
		{
			$upload_url 	= append_sid($this->helper->route('orynider_pafiledb_controller_user_upload', array('cat_id' => $cat_id)) );
			$mcp_url 		= append_sid($this->helper->route('orynider_pafiledb_controller_mcp', array('cat_id' => $cat_id)) );

			$upload_auth 	= $this->auth_user[$cat_id]['auth_upload'];
			$mcp_auth 		= $this->auth_user[$cat_id]['auth_mod'];
		}
		else
		{
			$upload_url = $this->helper->route('orynider_pafiledb_controller_user_upload  ');
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
				'IS_AUTH_VIEWALL' => ($pafiledb_config['settings_viewall']) ? (($this->auth_global['auth_viewall']) ? true : false) : false,
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

		$pafiledb->modules[$pafiledb->module_name]->_pafiledb();

		if ( !MXBB_MODULE || MXBB_27x )
		{
			$template->assign_block_vars( 'copy_footer', array() );
		}

		if ( !isset( $_GET['explain'] ) )
		{
			$template->pparse( 'body' );
		}

		$pafiledb_cache->unload();

		if ( $action != 'download' )
		{
			if ( !$is_block )
			{
				//include( $mx_root_path . 'includes/page_tail.' . $phpEx );
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_posticon
	 * @return unknown
	 */
	function post_icons( $file_posticon = '' )
	{
		global $lang, $phpbb_root_path;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;
		$curicons = 1;

		if ( $file_posticon == 'none' || $file_posticon == 'none.gif' or empty( $file_posticon ) )
		{
			$posticons .= '<input type="radio" name="posticon" value="none" checked><a class="gensmall">' . $lang['None'] . '</a>&nbsp;';
		}
		else
		{
			$posticons .= '<input type="radio" name="posticon" value="none"><a class="gensmall">' . $lang['None'] . '</a>&nbsp;';
		}

		$handle = @opendir( $module_root_path . ICONS_DIR );

		while ( $icon = @readdir( $handle ) )
		{
			if ( $icon !== '.' && $icon !== '..' && $icon !== 'index.htm' )
			{
				if ( $file_posticon == $icon )
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '" checked><img src="' . PORTAL_URL . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}
				else
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '"><img src="' . PORTAL_URL . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
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
	 * Enter description here...
	 *
	 * @param unknown_type $license_id
	 * @return unknown
	 */
	function license_list( $license_id = 0 )
	{
		global $db, $lang;

		if ( $license_id == 0 )
		{
			$list .= '<option calue="0" selected>' . $lang['None'] . '</option>';
		}
		else
		{
			$list .= '<option calue="0">' . $lang['None'] . '</option>';
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_license_table . '
			ORDER BY license_id';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
		}

		while ( $license = $db->sql_fetchrow( $result ) )
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
		global $pafiledb_config;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

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
		global $phpbb_root_path, $lang, $phpEx, $board_config, $pafiledb_config, $userdata;
		global $pafiledb, $cat_id, $mx_root_path, $module_root_path, $is_block, $phpEx;

		@set_time_limit( 0 );
		$file_info = array();

		$file_info['error'] = false;

		if ( file_exists( $module_root_path . $upload_dir . $userfile_name ) )
		{
			$userfile_name = time() . $userfile_name;
		}
		// =======================================================
		// if the file size is more than the allowed size another error message
		// =======================================================
		if ( $userfile_size > $pafiledb_config['max_file_size'] && ( $pafiledb->modules[$pafiledb->module_name]->auth_user[$cat_id]['auth_mod'] || $userdata['user_level'] != ADMIN ) && $userdata['session_logged_in'] )
		{
			$file_info['error'] = true;
			if ( !empty( $file_info['message'] ) )
			{
				$file_info['message'] .= '<br>';
			}
			$file_info['message'] .= $lang['Filetoobig'];
		}
		// =======================================================
		// Then upload the file, and check the php version
		// =======================================================
		else
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			$upload_mode = ( @$ini_val( 'open_basedir' ) || @$ini_val( 'safe_mode' ) ) ? 'move' : 'copy';
			$upload_mode = ( $local ) ? 'local' : $upload_mode;

			if ( $this->do_upload_file( $upload_mode, $userfile, $module_root_path . $upload_dir . $userfile_name ) )
			{
				$file_info['error'] = true;
				if ( !empty( $file_info['message'] ) )
				{
					$file_info['message'] .= '<br>';
				}
				$file_info['message'] .= 'Couldn\'t Upload the File.';
			}
			$file_info['url'] = get_formated_url() . '/' . $module_root_path . $upload_dir . $userfile_name;
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
				@chmod( $userfile_name, 0666 );
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
		global $db, $lang, $phpbb_root_path, $pafiledb_config;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$directory = $module_root_path . $pafiledb_config['upload_dir'];

		if ( empty( $file_data ) )
		{
			$sql = "SELECT file_dlurl, file_size, unique_name, file_dir
				FROM " . PA_FILES_TABLE . "
				WHERE file_id = '" . $file_id . "'";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );

			$db->sql_freeresult( $result );
		}

		$file_url = $file_data['file_dlurl'];
		$file_size = $file_data['file_size'];

		$formated_url = get_formated_url();
		$html_path = $formated_url . '/' . $directory;
		$update_filesize = false;

		if ( ( ( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) || !empty( $file_data['unique_name'] ) ) && empty( $file_size ) )
		{
			$file_url = basename( $file_url ) ;
			$file_name = basename( $file_url );

			if ( ( !empty( $file_data['unique_name'] ) ) && ( !file_exists( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] ) ) )
			{
				return $lang['Not_available'];
			}

			if ( empty( $file_data['unique_name'] ) )
			{
				$file_size = @filesize( $directory . $file_name );
			}
			else
			{
				$file_size = @filesize( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
			}

			$update_filesize = true;
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
				return $lang['Not_available'];
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
			$update_filesize = true;
		}
		
		if ( !$file_size )
		{
			//Check if file is not hosted on same domain relative to mx_root_path
			if (file_exists(str_replace(PORTAL_URL, "./", $file_url)))
			{
				$file_size = @filesize(str_replace(PORTAL_URL, "./", $file_url));
			}
			elseif  (file_exists($mx_root_path . $module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url)))
			{			
				$file_size = @filesize($mx_root_path . $module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url));
			}				
			else
			{
				return $lang['Not_available'];
			}				
		}		

		if ( $update_filesize )
		{
			$sql = 'UPDATE ' . PA_FILES_TABLE . "
				SET file_size = '$file_size'
				WHERE file_id = '$file_id'";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update filesize', '', __LINE__, __FILE__, $sql );
			}
		}

		if ( $file_size < 1024 )
		{
			$file_size_out = intval( $file_size ) . ' ' . $lang['Bytes'];
		}
		if ( $file_size >= 1025 )
		{
			$file_size_out = round( intval( $file_size ) / 1024 * 100 ) / 100 . ' ' . $lang['KB'];
		}
		if ( $file_size >= 1048575 )
		{
			$file_size_out = round( intval( $file_size ) / 1048576 * 100 ) / 100 . ' ' . $lang['MB'];
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
		global $pafiledb_config, $lang;

		$deleted = @unlink( $filename );

		if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
		{
			$filesys = preg_replace('/', '\\', $filename);
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
		global $phpbb_root_path, $phpEx;

		return ( !@function_exists( 'realpath' ) || !@realpath( $phpbb_root_path . 'includes/functions.' . $phpEx ) ) ? $path : @realpath( $path );
	}
}

?>