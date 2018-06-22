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

// Switches
@define('PAFILEDB_DEBUG', 1); // Pafiledb Mod Debugging on
@define('PAFILEDB_QUERY_DEBUG', 1);
@define('PA_ROOT_CAT', 0);
@define('PA_CAT_ALLOW_FILE', 1);
@define('FILE_PINNED', 1);

class pafiledb_category extends \orynider\pafiledb\core\pafiledb_public
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
	
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;
	
	/** @var \phpbb\pagination */
	protected $pagination;
	
	/** @var ContainerInterface */
	//protected $container;
	
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

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions			$functions
	
	
	* @param \phpbb\template\pafiledb		 		$template
	* @param \phpbb\user						$user
	* @param \phpbb\auth\auth					$auth
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\request\request		 		$request
	* @param \phpbb\controller\helper		 		$helper
	* @param \phpbb\pagination					$pagination
	* @param string							$php_ext
	* @param string							$root_path
	* @param string							$pa_files_table
	* @param string							$pa_cat_table
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,	
		\orynider\pafiledb\core\pafiledb_templates $pafiledb_templates,
		\phpbb\cache\driver\driver_interface $cache,		
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,		
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper,
		\phpbb\pagination $pagination,
		\phpbb\extension\manager $ext_manager, 	
		$php_ext,
		$root_path,
		$pa_files_table,
		$pa_cat_table)
	{
		$this->functions 			= $functions;
		$this->templates 			= $pafiledb_templates;
		$this->pafiledb 			= $pafiledb_cache;		
		$this->template 			= $template;		
		$this->user 				= $user;
		$this->auth 				= $auth;
		$this->db 					= $db;
		$this->request 				= $request;
		$this->helper 				= $helper;
		$this->pagination 			= $pagination;
		$this->ext_manager	 		= $ext_manager;
		//$this->container	 		= $container;		
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		
		$this->ext_name 			= $this->request->variable('ext_name', 'orynider/pafiledb');
		$this->module_root_path		= $this->ext_path = $this->ext_manager->get_extension_path($this->ext_name, true);

		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
	}

	public function handle_category()
	{		
		$board_url = generate_board_url() . '/';
		define('ICONS_DIR', 'styles/all/images/icons/');		
		
		$images = $this->templates->images;
		
		//		
		// Read out config values
		//		
		$pafiledb_config = $this->functions->config_values();
		
		// =======================================================
		// Retrieve cat id
		// =======================================================
		$start	= $this->request->variable('start', 0);
		$cat_id = $this->request->variable('cat_id', '');

		$number	= $pafiledb_config['pagination_user'];
		
		//
		// Setup message parser
		//		
		$this->message_parser = new \parse_message();
		
		//
		// Sorting of items
		//
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
			switch ($this->request->is_set('sort_order'))
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
		
		// =======================================================
		// If user not allowed to view file listing (read) and there is no sub Category
		// or the user is not allowed to view these category we gave him a nice message.
		// =======================================================
		$show_category = false;
		if ( isset( $this->functions->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->functions->subcat_rowset[$cat_id] as $sub_cat_id => $sub_cat_row )
			{
				if ( $this->functions->auth_user[$sub_cat_id]['auth_view'] )
				{
					$show_category = true;
					break;
				}
			}
		}

		if ( !isset( $this->functions->cat_rowset[$cat_id] ) )
		{
			//print_r($this->functions->cat_rowset);
			//$this->functions->message_die(GENERAL_MESSAGE, $this->user->lang('Cat_not_exist'));
		}		
		
		//
		// User authorisation levels output
		$this->functions->auth_can($cat_id);		
		
		/**
		* We need some information about the cat, we are in
		*/
		// Select cat name
		$sql = 'SELECT cat_name
			FROM ' . $this->pa_cat_table . '
			WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			throw new http_exception(401, 'FILES_CAT_NOT_EXIST');
		}
		else
		{
			$cat_data = $this->functions->get_cat_info($cat_id);
		}

		/**
		* Generate the navigation
		*/
		$this->functions->generate_cat_nav($cat_data);

		// Total number of downloads
		$sql = 'SELECT COUNT(file_id) AS total_downloads
			FROM ' . $this->pa_files_table . '
			WHERE file_catid = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_downloads = $row['total_downloads'];
		$this->db->sql_freeresult($result);

		// Select cat name
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
		if ( !($result = $this->db->sql_query_limit($sql, $pafiledb_config['pagination_downloads'], $start, 60)) )
		{
			$this->functions->message_die(GENERAL_ERROR, 'Could not query category information', '', __LINE__, __FILE__, $sql);
		}	
		
		$new_topic_data = array();
		while( $topic_data = $this->db->sql_fetchrow($result) )
		{
			$cat_name = $topic_data['cat_name'];
			$new_topic_data[$topic_data['cat_id']][$topic_data['file_id']] = $topic_data['file_update_time'];
		}
		$this->db->sql_freeresult($result);
		
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
	
		$unread_topics = false;
		if ($this->user->data['user_id'] != 1)
		{
			if ( !empty($new_topic_data[$cat_id]) )
			{
				$forum_last_post_time = 0;
					
				while( list($check_topic_id, $check_post_time) = @each($new_topic_data[$cat_id]) )
				{
					if ( empty($tracking_topics[$check_topic_id]) )
					{
						$unread_topics = true;
						$forum_last_post_time = max($check_post_time, $forum_last_post_time);
					}
					else
					{
						if ( $tracking_topics[$check_topic_id] < $check_post_time )
						{
							$unread_topics = true;
							$forum_last_post_time = max($check_post_time, $forum_last_post_time);
						}
					}
				}
					
				if ( !empty($tracking_forums[$cat_id]) )
				{
					if ( $tracking_forums[$cat_id] > $forum_last_post_time )
					{
						$unread_topics = false;
					}
				}
			}
		}
		
		$pa_folder_image = (($topic_data['left_id'] + 1) != $topic_data['right_id']) ? 'pa_icon_subfolder' : 'pa_icon_folder';
		$pa_folder_title = (($topic_data['left_id'] + 1) != $topic_data['right_id']) ? $this->user->lang['SUBFORUM'] : $this->user->lang['FOLDER'];
		
		$folder_image = isset($unread_topics) ? $images['forum_new'] : $images['forum']; 
		$folder_title = isset($unread_topics) ? $lang['New_posts'] : $lang['No_new_posts'];		
		
		// Check if there are downloads
		if ($total_downloads == 0)
		{
			$this->template->assign_vars(array(
				'CAT_NAME'		=> $cat_name,
				'S_NO_FILES'	=> true,
				'MAIN_LINK'		=> $this->helper->route('orynider_pafiledb_controller'),
				'U_BACK'		=> append_sid("{$this->root_path}index.{$this->php_ext}"),
			));
		}
		else
		{			
			//
			// Main query
			//
			switch ( SQL_LAYER )
			{
				case 'oracle':
					$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, u.user_colour
						FROM " . $this->pa_files_table . " AS f1, " . $this->pa_votes_table . " AS r, " . USERS_TABLE . " AS u, " . $this->pa_cat_table . " AS cat
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
							LEFT JOIN " . $this->pa_category_table . " AS cat ON f1.file_catid = cat.cat_id
						WHERE f1.file_pin <> " . FILE_PINNED . "
						AND f1.file_approved = 1
						$cat_where
						$sql_xtra
						GROUP BY f1.file_id
						ORDER BY $sort_method $sort_order";
					break;
			}				
			
			$sql = 'SELECT f1.*, cat.*
				FROM ' . $this->pa_files_table . ' f1
				LEFT JOIN ' . $this->pa_cat_table. ' cat
					ON f1.file_catid = cat.cat_id
				WHERE cat.cat_id = ' . (int) $cat_id . '
				ORDER BY LOWER(f1.file_version) DESC';
			$result = $this->db->sql_query_limit($sql, $number, $start);			
			
			
			while ($row = $this->db->sql_fetchrow($result))
			{
				$file_id			= $row['file_id'];
				$file_name			= $row['file_name'];
				$file_version		= $row['file_version'];
				$file_clicks		= $row['file_count'];
				$cat_name 			= $row['cat_name'];
				$file_time 			= $row['file_time'];
				$file_update_time 	= $row['file_update_time'];
				$file_size			= $row['file_size'] > 1048576 ? round($row['file_size'] / 1048576, 2) . ' MB' : round($row['file_size'] / 1024) . ' kB';
							
				// ===================================================
				// Get the post icon fot this file
				// ===================================================
				if ( $row['file_pin'] != FILE_PINNED )
				{
					if ( $row['file_posticon'] == 'none' || $row['file_posticon'] == 'pa_no_posticon' || $row['file_posticon'] == 'none.gif' )
					{
						$posticon 			= $this->templates->img('pa_no_posticon', $this->user->lang['FILES_NO_POSTICON'], false, '', 'src');
						$posticon_full_tag	= $this->templates->img('pa_no_posticon', $this->user->lang['FILES_NO_POSTICON'], false, '', 'full_tag');					
					}
					else
					{
						$posticon 			= empty($row['file_posticon']) ? $this->templates->img($pa_folder_image, $this->user->lang['FILES_POSTICON'], false, '', 'src') : $this->module_root_path . ICONS_DIR . $row['file_posticon'];
						$posticon_full_tag 	= empty($row['file_posticon']) ? $this->templates->img($pa_folder_image, $this->user->lang['FILES_POSTICON'], false, '', 'full_tag') :  '<img src="'.$posticon.'" alt="'.$this->user->lang['TOPIC_ICON'].'" title="'.$this->user->lang['TOPIC_ICON'].'" border="0" />';						
					}
				}
				else
				{
					//sticky_read or folder_sticky
					$posticon 				= $this->templates->img('folder_sticky', $this->user->lang('POST_STICKY'), false, '', 'src');
					$posticon_full_tag		= $this->templates->img('folder_sticky', $this->user->lang('POST_STICKY'), false, '23', 'full_tag');
				}								
				
				//
				// Define the little post icon
				//
				if ( $this->user->data['user_id'] == ANONYMOUS && $row['post_time'] > $this->user->data['user_lastvisit'] && $row['post_time'] > $topic_last_read )
				{
					$mini_post_img = $this->templates->img('icon_pa_new', '', false, '', 'src');
					$mini_post_alt = $lang['New_post'];
					$post_unread = true;
				}
				else
				{
					$mini_post_img = $this->templates->img('pa_icon_mini_dl', '', false, '', 'src');
					$mini_post_alt = $lang['Post'];
					$post_unread = false;		
				}
								
				$download_url = $this->helper->route('orynider_pafiledb_controller_download', array('file_id' =>	$file_id));								
				$file_url = $this->helper->route('orynider_pafiledb_controller_file', array('file_id' =>	$file_id));				
				
				if ($this->auth->acl_get('u_pa_files_download'))
				{
					$download_tag = '<a href="' . $download_url . '">' . $this->templates->img('pa_icon_mini_dl', $this->user->lang['FILES_REGULAR_DOWNLOAD'], '13', '', 'full_tag') . '</a>';					
				}
				else
				{
					$download_tag = $this->templates->img('pa_files_no_download', $this->user->lang['FILES_NO_PERMISSION'], false, '', 'full_tag');
				}								
 				
				$dl_title_url = '<a href="' . $board_url . $download_url . '">' . $dl_title . '</a>';; 
				
				$this->message_parser->message = $row['file_desc'];
				$this->message_parser->bbcode_bitfield = $row['bbcode_bitfield'];
				$this->message_parser->bbcode_uid = $row['bbcode_uid'];
				$allow_bbcode = $allow_magic_url = $allow_smilies = true;
				$this->message_parser->format_display($allow_bbcode, $allow_magic_url, $allow_smilies);

				$this->template->assign_block_vars('files_row', array(				
					'U_FILE'			=> print_r($file_url, true),					
					'PIN_IMAGE' 		=> $posticon,
					'PIN_IMAGE_TAG' 	=> $posticon_full_tag,					
					'FILE_TITLE'		=> $file_name,
					'FILE_VERSION'		=> $file_version,
					'FILE_CLICKS'		=> $file_clicks,
					'FILE_DESC'			=> $this->message_parser->message,
					'FILE_UPLOAD_TIME' 	=> $this->user->format_date($file_time),
					'FILE_LAST_CHANGED' => $this->user->format_date($file_update_time),
					'FILESIZE'			=> $file_size,
					'U_DOWNLOAD'		=> $download_tag,
					'BLOCK_ID' 			=> $cat_id,					
				));
			}

			$this->db->sql_freeresult($result);

			$pagination_url = $this->helper->route('orynider_pafiledb_controller_cat', array('cat_id' =>	$cat_id));
			//Start pagination
			$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_downloads, $number, $start);

			$this->functions->assign_authors();

			$this->template->assign_vars(array(
				'CAT_NAME' 						=> $cat_name,
				'MAIN_LINK'						=> $this->helper->route('orynider_pafiledb_controller'),
				'PAFILEDB_FOOTER_VIEW'			=> true,
				'TOTAL_DOWNLOADS'				=> ($total_downloads == 1) ? $this->user->lang['FILES_SINGLE'] : sprintf($this->user->lang['FILES_MULTI'], $total_downloads),
				'L_MAIN_LINK'					=> sprintf($this->user->lang['FILES_BACK_LINK'], '<a href= "' . $this->helper->route('orynider_pafiledb_controller') . '">', '</a>'),
			));
		}

		// Send all data to the template file
		return $this->helper->render('showcat_body.html', $this->user->lang('FILES_TITLE') . ' &bull; ' . $cat_name);
	}
}
