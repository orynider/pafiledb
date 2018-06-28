<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_viewall.php,v 1.23 2008/09/21 14:25:40 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\controller;

/**
 * Enter description here...
 *
 */
class pafiledb_viewall extends \orynider\pafiledb\core\pafiledb_public
{ 	
	/** @var \orynider\pafiledb\core\pafiledb */
	protected $functions;	
	
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\cache */
	protected $cache;	

	/** @var \orynider\pafiledb\core\functions_cache */
	protected $pafiledb_cache;	
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\request\request */
	protected $request;	
	
	/** @var \phpbb\controller\helper */
	protected $helper;	

	/** @var \phpbb\pagination */
	protected $pagination;	
	
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;	
	
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

	protected $pa_auth_access_table;	
	
	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions						$functions
	* @param \phpbb\template\template		 						$template
	* @param \phpbb\user										$user
	* @param \phpbb\auth\auth									$auth
	* @param \phpbb\cache\service								$cache
	* @param \orynider\pafiledb\core\functions_cache					$functions_cache		
	* @param \phpbb\db\driver\driver_interface						$db
	* @param \phpbb\request\request		 						$request
	* @param \phpbb\controller\helper		 						$helper	
	* @param \phpbb\pagination									$pagination
	* @param \phpbb\extension\manager							$ext_manager
	* @param \phpbb\path_helper									$path_helper
	* @param string 											$php_ext
	* @param string 											$root_path
	* @param string 											$pa_files_table
	* @param string 											$pa_cat_table
	* @param string 											$pa_config_table
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,		
		\phpbb\cache\service $cache,
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,			
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper,		
		\phpbb\pagination $pagination,
		\phpbb\extension\manager $ext_manager,
		\phpbb\path_helper $path_helper,
		$php_ext, 
		$root_path,
		
		$pa_files_table,
		$pa_cat_table,
		$pa_auth_access_table,		
		$pa_config_table)
	{
		$this->functions 			= $functions;
		$this->template 			= $template;
		$this->user 				= $user;
		$this->auth 				= $auth;		
		$this->cache 				= $cache;
		$this->pafiledb_cache 		= $pafiledb_cache;		
		$this->db 					= $db;
		$this->request 				= $request;
		$this->helper 				= $helper;		
		$this->pagination 			= $pagination;
		$this->ext_manager	 		= $ext_manager;
		$this->path_helper	 		= $path_helper;
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_auth_access_table = $pa_auth_access_table;		
		$this->pa_config_table 		= $pa_config_table;
		
		$this->ext_name 			= $this->functions->ext_name;
		$this->module_root_path		= $this->functions->module_root_path;
		
		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			ORDER BY cat_order ASC';

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->message_die(GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql);
		}
		
		$cat_rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
		
		$this->auth($cat_rowset, '');

		for( $i = 0; $i < $cats = count($cat_rowset); $i++ )
		{
			if ( $auth->acl_get('u_pa_files_download') || $this->auth_user[$cat_rowset[$i]['cat_id']]['auth_view'] )
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
	}	
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function handle_viewall($action  = false)
	{
		// Read out config values
		$pafiledb_config = $this->pafiledb_cache->config_values();
			
		$this->backend = $this->functions->confirm_backend();		
	
		$start = $this->request->variable('start', 0);		

		if ($this->request->is_set('sort_method'))
		{
			switch ($this->request->variable('sort_method', ''))
			{
				case 'file_name':
					$sort_method = 'file_name';
				break;
				case 'file_time':
					$sort_method = 'file_time';
				break;
				case 'file_dls':
					$sort_method = 'file_dls';
				break;
				case 'file_rating':
					$sort_method = 'rating';
				break;
				case 'file_update_time':
					$sort_method = 'file_update_time';
				break;
				default:
					$sort_method = $pafiledb_config['sort_method'];
			}
		}
		else
		{
			$sort_method = $pafiledb_config['sort_method'];
		}

		if ($this->request->is_set('sort_order'))
		{
			switch ($this->request->variable('sort_order', ''))
			{
				case 'ASC':
					$sort_order = 'ASC';
				break;
				case 'DESC':
					$sort_order = 'DESC';
				break;
				default:
					$sort_order = $pafiledb_config['sort_order'];
			}
		}
		else
		{
			$sort_order = $pafiledb_config['sort_order'];
		}
			
		if ($pafiledb_config['settings_viewall'] != 1)
		{
			$this->functions->message_die( GENERAL_MESSAGE, $this->user->lang['viewall_disabled'] . ' Settings viewall: ' . $pafiledb_config['settings_viewall'] );
		}
		elseif ( !$this->auth_global['auth_viewall'] )
		{
			if ($this->user->data['user_id'] != 1)
			{
				//redirect( append_sid( "login.$this->php_ext?redirect=dload.$this->php_ext?action=viewall", true ) );
			}

			$message = sprintf( $this->user->lang['Sorry_auth_viewall'], $this->auth_global['auth_viewall_type']) . ' Auth viewall: ' . $this->auth_global['auth_viewall'];
			$this->functions->message_die(GENERAL_MESSAGE, $message);
		}

		$this->template->assign_vars( array(
			'L_VIEWALL' => $this->user->lang['Viewall'],
			'L_INDEX' => "<<",

			'U_INDEX' => append_sid( $this->get_formated_url(true) . 'index.' . $phpEx ),
			'U_DOWNLOAD' => append_sid( $this->functions->mxurl() ),

			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		$this->functions->display_items( $sort_method, $sort_order, $start, false,  true );

		// ===================================================
		// assign var for navigation
		// ===================================================
		//
		// Output all
		//
		$tpl_name = 'pa_viewall_body.html';
		$page_title = $this->user->lang('FILES_TITLE');
		//$this->display( $this->user->lang['Download'], 'pa_viewall_body.html' );
		$this->functions->page_header( $page_title );
		$this->template->set_filenames( array( 'body' => $tpl_name ) );
		$this->functions->page_footer();		
		return $this->helper->render($tpl_name, $page_title . ' &bull; ' . $file_data['file_name']);		
	}
}
?>