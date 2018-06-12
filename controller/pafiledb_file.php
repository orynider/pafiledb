<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_file.php,v 1.29 2009/12/02 03:49:01 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/



/**
 * Enter description here...
 *
 */
class pafiledb_file extends pafiledb_public
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var ContainerInterface */
	protected $container;	
	
	/** @var \phpbb\cache\cache */
	protected $cache;	

	/** @var \orynider\pafiledb\core\functions_cache */
	protected $functions_cache;
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $root_path;

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
	* @param \orynider\pafiledb\core\functions			$functions
	
	
	* @param \phpbb\template\template		 		$this->template
	* @param \phpbb\user						$user
	* @param \phpbb\auth\auth					$auth
	* @param \phpbb\db\driver\driver_interface		$this->db
	* @param \phpbb\request\request		 		$request
	* @param \phpbb\controller\helper		 		$helper
	* @param \phpbb\cache\service					$cache
	* @param \orynider\pafiledb\core\functions_cache		$functions_cache
	* @param \phpbb\config\config					$config
	* @param ContainerInterface                    			$container
	* @param \phpbb\pagination					$pagination
	* @param \phpbb\extension\manager 				$extension_manager
	* @param string							$php_ext
	* @param string							$root_path
	* @param								$pa_files_table
	* @param								$pa_cat_table
	* @param								$pa_config_table
	* @param								$pa_votes_table
	* @param								$pa_comments_table
	* @param								$pa_license_table	
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb_functions $functions,
		\orynider\pafiledb\core\pafiledb_templates $pafiledb_templates,
		\phpbb\cache\driver\driver_interface $cache,
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,
		\phpbb\template\template $this->template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $this->db,
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper,
		\phpbb\pagination $pagination,
		\phpbb\config\config $config,
		\phpbb\extension\manager $ext_manager, 
		ContainerInterface $container,
		$php_ext,
		$root_path,
		$pa_files_table,
		$pa_cat_table,
		$pa_config_table, 
		$pa_votes_table,
		$pa_comments_table,
		$pa_license_table)
	{
		$this->functions 			= $functions;
		$this->templates 			= $pafiledb_templates;
		$this->pafiledb 			= $pafiledb_cache;
		$this->template 			= $template;
		$this->user 				= $user;
		$this->auth 				= $auth;
		$this->db 					= $this->db;
		$this->request 				= $request;
		$this->helper 				= $helper;
		$this->pagination 			= $pagination;
		$this->config 				= $config;
		$this->ext_manager	 		= $ext_manager;		
		$this->container 			= $container;		
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_config_table 		= $pa_config_table;
		$this->pa_votes_table 		= $pa_votes_table;
		$this->pa_comments_table 	= $pa_comments_table;
		$this->pa_license_table 	= $pa_license_table;

		$this->ext_name 			= $this->request->variable('ext_name', 'orynider/pafiledb');
		$this->module_root_path		= $this->ext_path = $this->ext_manager->get_extension_path($this->ext_name, true);
	
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
	}	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		// Read out config values
		$pafiledb_config = $this->functions->config_values();
		
		$images = $this->templates->images;
		$is_block = false;

		// =======================================================
		// Request vars
		// =======================================================
		$start = $this->request->get('start', MX_TYPE_INT, 0);
		$file_id = $this->request->request('file_id', MX_TYPE_INT, '');
		$page_num = $this->request->request('page_num', MX_TYPE_INT, 1) - 1;

		if ( empty( $file_id ) )
		{
			$this->functions->message_die( GENERAL_MESSAGE, $this->user->lang['File_not_exist'] );
		}

		// =======================================================
		// =======================================================
		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f.file_id = r.votes_file(+)
					AND f.user_id = u.user_id(+)
					AND f.file_id = c.file_id(+)
					AND f.file_id = $file_id
					AND f.file_approved = 1
					AND f.file_catid = cat.cat_id
					GROUP BY f.file_id ";
				break;

			default:
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file
						LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f.file_id = c.file_id
						LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f.file_catid = cat.cat_id
					WHERE f.file_id = $file_id
					AND f.file_approved = 1
					GROUP BY f.file_id ";
				break;
		}

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->functions->message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		}

		// ===================================================
		// file doesn't exist'
		// ===================================================
		if ( !$file_data = $this->db->sql_fetchrow( $result ) )
		{
			$this->functions->message_die( GENERAL_MESSAGE, $this->user->lang['File_not_exist'] );
		}
		$this->db->sql_freeresult( $result );

		// ===================================================
		// Pafiledb auth for viewing file
		// ===================================================
		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_view_file'] ) )
		{
			/*
			if ( !$this->user->data['session_logged_in'] )
			{
				mx_redirect(mx_append_sid($this->root_path . "login.$this->php_ext?redirect=".$this->this_mxurl("action=file&file_id=" . $file_id), true));
			}
			*/
			$message = sprintf( $this->user->lang['Sorry_auth_view'], $this->auth_user[$file_data['file_catid']]['auth_view_file_type'] );
			$this->functions->message_die( GENERAL_MESSAGE, $message );
		}

		$this->template->assign_vars( array(
			'L_INDEX' => "<<",

			'U_INDEX' => append_sid( $this->root_path . 'index.' . $this->php_ext ),
			'U_DOWNLOAD_HOME' => append_sid( $this->this_mxurl() ),

			'FILE_NAME' => $file_data['file_name'],
			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		// ===================================================
		// Prepare file info to display them
		// ===================================================
		$file_time = phpBB2::create_date( $this->config['default_dateformat'], $file_data['file_time'], $this->config['board_timezone'] );
		$file_last_download = ( $file_data['file_last'] ) ? phpBB2::create_date( $this->config['default_dateformat'], $file_data['file_last'], $this->config['board_timezone'] ) : $this->user->lang['never'];
		$file_update_time = ( $file_data['file_update_time'] ) ? phpBB2::create_date( $this->config['default_dateformat'], $file_data['file_update_time'], $this->config['board_timezone'] ) : $this->user->lang['never'];
		$file_author = trim( $file_data['file_creator'] );
		$file_version = trim( $file_data['file_version'] );
		$file_screenshot_url = trim( $file_data['file_ssurl'] );
		$file_website_url = trim( $file_data['file_docsurl'] );
		$file_download_link = ( $file_data['file_license'] > 0 ) ? mx_append_sid( $this->this_mxurl( 'action=license&license_id=' . $file_data['file_license'] . '&file_id=' . $file_id ) ) : mx_append_sid( $this->this_mxurl( 'action=download&file_id=' . $file_id, 1 ) );
		$file_size = $this->functions->get_file_size( $file_id, $file_data );

		$file_poster = ( $file_data['user_id'] != ANONYMOUS ) ? '<a href="' . mx_append_sid( $this->root_path . 'profile.' . $this->php_ext . '?mode=viewprofile&amp;' . POST_USERS_URL . '=' . $file_data['user_id'] ) . '">' : '';
		$file_poster .= ( $file_data['user_id'] != ANONYMOUS ) ? $file_data['username'] : $this->user->lang['Guest'];
		$file_poster .= ( $file_data['user_id'] != ANONYMOUS ) ? '</a>' : '';

		if ( !MXBB_MODULE )
		{
			$server_protocol = ($this->config['cookie_secure']) ? 'https://' : 'http://';
			$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($this->config['server_name']));
			$server_port = ($this->config['server_port'] <> 80) ? ':' . trim($this->config['server_port']) : '';
			$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($this->config['script_path']));
			$false_phpbb_url = $server_protocol . $server_name . $server_port . '/';
			$false_phpbb_path = './';
			$file_screenshot_url = str_replace($false_phpbb_url . $false_phpbb_path, PORTAL_URL, $file_screenshot_url);
		}

		//
		// Disabled file
		//
		if ($file_data['file_disable'])
		{
			$file_download_link = 'javascript:disable_popup()';
		}
		
		//overwrite some phpBB3 vars
		$images['pa_icon_delpost'] = $this->user->img('icon_post_delete', 'DELETE_POST', false, '', 'src');
		$images['pa_icon_edit'] = $this->user->img('icon_post_edit', 'EDIT_POST', false, '', 'src');

		$this->template->assign_vars( array(
			'L_CLICK_HERE' => $this->user->lang['Click_here'],
			'L_AUTHOR' => $this->user->lang['Creator'],
			'L_VERSION' => $this->user->lang['Version'],
			'L_SCREENSHOT' => $this->user->lang['Scrsht'],
			'L_WEBSITE' => $this->user->lang['Docs'],
			'L_FILE' => $this->user->lang['File'],
			'L_DESC' => $this->user->lang['Desc'],
			'L_DATE' => $this->user->lang['Date'],
			'L_UPDATE_TIME' => $this->user->lang['Update_time'],
			'L_LASTTDL' => $this->user->lang['Lastdl'],
			'L_DLS' => $this->user->lang['Dls'],
			'L_SIZE' => $this->user->lang['File_size'],
			'L_EDIT' => $this->user->lang['Editfile'],
			'L_DELETE' => $this->user->lang['Deletefile'],
			'L_DOWNLOAD' => $this->user->lang['Downloadfile'],
			'L_EMAIL' => $this->user->lang['Emailfile'],
			'L_SUBMITED_BY' => $this->user->lang['Submiter'],

			'SHOW_AUTHOR' => ( !empty( $file_author ) ) ? true : false,
			'SHOW_VERSION' => ( !empty( $file_version ) ) ? true : false,
			'SHOW_SCREENSHOT' => ( !empty( $file_screenshot_url ) ) ? true : false,
			'SHOW_WEBSITE' => ( !empty( $file_website_url ) ) ? true : false,
			'SS_AS_LINK' => ( $file_data['file_sshot_link'] ) ? true : false,
			'FILE_NAME' => $file_data['file_name'],
			'FILE_LONGDESC' => nl2br( $file_data['file_longdesc'] ),
			'FILE_SUBMITED_BY' => $file_poster,
			'FILE_AUTHOR' => $file_author,
			'FILE_VERSION' => $file_version,
			'FILE_SCREENSHOT' => $file_screenshot_url,
			'FILE_WEBSITE' => $file_website_url,
			'FILE_DISABLE_MSG' => nl2br( $file_data['disable_msg'] ),

			'AUTH_EDIT' => ( ( $this->auth_user[$file_data['file_catid']]['auth_edit_file'] && $file_data['user_id'] == $this->user->data['user_id'] ) || $this->auth_user[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DELETE' => ( ( $this->auth_user[$file_data['file_catid']]['auth_delete_file'] && $file_data['user_id'] == $this->user->data['user_id'] ) || $this->auth_user[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DOWNLOAD' => ( $this->auth_user[$file_data['file_catid']]['auth_download'] ) ? true : false,
			'AUTH_EMAIL' => ( $this->auth_user[$file_data['file_catid']]['auth_email'] ) ? true : false,

			'DELETE_IMG' => $images['pa_icon_delpost'],
			'EDIT_IMG' => $images['pa_icon_edit'],
			'DOWNLOAD_IMG' => $images['pa_download'],
			'EMAIL_IMG' => $images['pa_email'],

			'TIME' => $file_time,
			'UPDATE_TIME' => ( $file_data['file_update_time'] != $file_data['file_time'] ) ? $file_update_time : $this->user->lang['never'],
			'FILE_DLS' => intval( $file_data['file_dls'] ),
			'FILE_SIZE' => $file_size,
			'LAST' => $file_last_download,

			'U_DOWNLOAD' => $file_download_link,
			'U_DELETE' => append_sid( $this->this_mxurl( 'action=user_upload&do=delete&file_id=' . $file_id ) ),
			'U_EDIT' => append_sid( $this->this_mxurl( 'action=user_upload&file_id=' . $file_id ) ),
			'U_EMAIL' => append_sid( $this->this_mxurl( 'action=email&file_id=' . $file_id ) ),

			// Buttons
			'B_DOWNLOAD_IMG' => $this->user->create_button('pa_download', $this->user->lang['Downloadfile'], $file_download_link),
			'B_DELETE_IMG' => $this->user->create_button('pa_icon_delpost', $this->user->lang['Deletefile'], "javascript:delete_item('". mx_append_sid( $this->this_mxurl( 'action=user_upload&do=delete&file_id=' . $file_id )) . "')"),
			'B_EDIT_IMG' => $this->user->create_button('pa_icon_edit', $this->user->lang['Editfile'], mx_append_sid( $this->this_mxurl( 'action=user_upload&file_id=' . $file_id ) )),
			'B_EMAIL_IMG' => $this->user->create_button('pa_email', $this->user->lang['Emailfile'], mx_append_sid( $this->this_mxurl( 'action=email&file_id=' . $file_id ))),
		));

		$custom_field = new custom_field();
		$custom_field->init();
		$custom_field->display_data( $file_id );

		//
		// Ratings
		//
		if ( $this->ratings[$file_data['file_catid']]['activated'] )
		{
			$file_rating = ( $file_data['rating'] != 0 ) ? round( $file_data['rating'], 2 ) . '/10' : $this->user->lang['Not_rated'];

			if ( $this->auth_user[$file_data['file_catid']]['auth_rate'] )
			{
				$rate_img = $images['pa_rate'];
			}

			$this->template->assign_block_vars( 'use_ratings', array(
				'L_RATING' => $this->user->lang['DlRating'],
				'L_RATE' => $this->user->lang['Rate'],
				'L_VOTES' => $this->user->lang['Votes'],
				'FILE_VOTES' => $file_data['total_votes'],
				'RATING' => $file_rating,

				//
				// Allowed to rate
				//
				'RATE_IMG' => $rate_img,
				'U_RATE' => append_sid( $this->this_mxurl( 'action=rate&file_id=' . $file_id ) ),

				// Buttons
				'B_RATE_IMG' => $this->user->create_button('pa_rate', $this->user->lang['Rate'], mx_append_sid( $this->this_mxurl( 'action=rate&file_id=' . $file_id ) )),

			));
		}

		//
		// Comments
		//
		if ( $this->comments[$file_data['file_catid']]['activated'] && $this->auth_user[$file_data['file_catid']]['auth_view_comment'])
		{
			$comments_type = $this->comments[$file_data['file_catid']]['internal_comments'] ? 'internal' : 'phpbb';

			//
			// Instatiate comments
			//
			include_once( $this->module_root_path . 'pafiledb/includes/functions_comment.' . $this->php_ext );
			$pafiledb_comments = new pafiledb_comments();
			$pafiledb_comments->init( $file_data, $comments_type );
			$pafiledb_comments->display_comments();
		}

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		//
		// User authorisation levels output
		//
		$this->auth_can($file_data['file_catid']);

		//
		// Output all
		//
		$this->display( $this->user->lang['Download'], 'pa_file_body.tpl' );
	}
}
?>