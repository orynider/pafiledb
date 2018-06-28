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

class pafiledb_user_upload extends \orynider\pafiledb\core\pafiledb_public
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;
	
	/** @var \orynider\pafiledb\core\custom_field */	
	protected $custom_field;	

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string */
	protected $php_ext;

	/** @var string phpBB root path */
	protected $root_path;

	/**
	* The database tables
	*
	* @var string
	*/
	protected $pa_files_table;

	protected $pa_cat_table;
	
	protected $custom_table;
	
	protected $custom_data_table;		

	/** @var \phpbb\files\factory */
	protected $files_factory;
	

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions				$functions
	* @param \orynider\pafiledb\core\custom_field				$custom_field	
	* @param \phpbb\template\pafiledb		 				$template
	* @param \phpbb\user								$user
	* @param \phpbb\auth\auth							$auth
	* @param \phpbb\log								$log
	* @param \phpbb\db\driver\driver_interface				$db
	* @param \phpbb\controller\helper		 				$helper
	* @param \phpbb\request\request		 				$request
	* @param \phpbb\extension\manager					$ext_manager
	* @param \phpbb\path_helper							$path_helper
	* @param string 									$php_ext
	* @param string 									$root_path
	* @param string 									$pa_files_table
	* @param string 									$pa_cat_table
	* @param string 									$custom_table
	* @param string 									$custom_data_table	
	* @param \phpbb\files\factory						$files_factory
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,
		\orynider\pafiledb\core\custom_field $custom_field,			
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\log\log $log,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\extension\manager $ext_manager,
		\phpbb\path_helper $path_helper,
		$php_ext, 
		$root_path,
		$pa_files_table,
		$pa_cat_table,
		$pa_auth_access_table,
		$custom_table,
		$custom_data_table,		
		\phpbb\files\factory $files_factory = null)
	{
		$this->functions 			= $functions;
		$this->custom_field 		= $custom_field;
		$this->template 			= $template;
		$this->user 				= $user;
		$this->auth 				= $auth;
		$this->log 					= $log;
		$this->db 					= $db;
		$this->helper 				= $helper;
		$this->request 				= $request;
		$this->ext_manager	 		= $ext_manager;
		$this->path_helper	 		= $path_helper;
		$this->php_ext 				= $php_ext;
		$this->root_path 			= $root_path;
		
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_auth_access_table = $pa_auth_access_table;		
		$this->custom_table 		= $custom_table;
		$this->custom_data_table 	= $custom_data_table;		
		
		$this->files_factory 		= $files_factory;
		
		$this->ext_path 			= $this->ext_manager->get_extension_path('orynider/pafiledb', true);
		$this->ext_path_web 		= $this->path_helper->update_web_root_path($this->ext_path);

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!defined('PHPBB_USE_BOARD_URL_PATH'))
		{
			define('PHPBB_USE_BOARD_URL_PATH', true);
		}
		
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

	public function handle_upload()
	{
		if (!$this->auth->acl_get('u_pa_files_upload'))
		{
			throw new http_exception(401, 'FILES_NO_UPLOAD');
		}
		
		if(!is_object($mx_block))
		{
			global $phpbb_container;
			/* @var $phpbb_content_visibility \phpbb\content_visibility */
			$mx_block = $phpbb_container->get('content.visibility');
		}		
		
		//
		// Go full page
		//
		$mx_block->full_page = true;		
		
		// Read out config values
		$pafiledb_config = $this->functions->config_values();
		
		// =======================================================
		// Request vars
		// =======================================================
		$cat_id 		= $this->request->variable('cat_id', 0);
		$file_id		= $this->request->variable('file_id', 0);
		$title			= $this->request->variable('title', '', true);
		$cat_name_show	= $this->request->variable('cat_name_show', 1);
		$filename		= $this->request->variable('filename', '', true);
		$desc			= $this->request->variable('desc', '', true);
		$file_version	= $this->request->variable('file_version', '', true);
		$costs_dl		= $this->request->variable('cost_per_dl', 0.00, true);
		$cat_option 	= $this->request->variable('parent', '', true);
		$ftp_upload		= $this->request->variable('ftp_upload', '', true);

		$do = ($this->request->is_set('do')) ? intval($this->request->variable('do', '', true)) : '';
		$mirrors = ($this->request->is_set_post('mirrors')) ? true : 0;			
		
		$uid = $bitfield = $options = '';
		$allow_bbcode = $allow_urls = $allow_smilies = true;

		$max_file_size = @ini_get('upload_max_filesize');
		$unit = 'MB';

		if (!empty($max_file_size))
		{
			$unit = strtolower(substr($max_file_size, -1, 1));
			$max_file_size = (int) $max_file_size;
			$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
		}

		add_form_key('add_upload');

		$this->user->add_lang('posting');
		
		//
		// Main Auth
		//
		if ( !empty( $cat_id ) )
		{
			if ( !$this->auth_user[$cat_id]['auth_upload'] )
			{
				$message = sprintf( $lang['Sorry_auth_upload'], $this->auth_user[$cat_id]['auth_upload_type'] );
			}
		}
		else
		{
			$dropmenu = ( !$cat_id ) ? $this->generate_jumpbox( 0, 0, '', true, true, 'auth_upload' ) : $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true, true, 'auth_upload' );

			if ( empty( $dropmenu ) )
			{
				$message = sprintf( $lang['Sorry_auth_upload'], $this->auth_user[$cat_id]['auth_upload_type'] );
			}
		}		
		
		//
		// Not authorized? Output nice message and die.
		//
		if (!empty($message))
		{
			$this->functions->message_die( GENERAL_MESSAGE, $message );
		}
		
		//
		// Load file info...if file_id is set
		//
		if ( $file_id )
		{
			$sql = 'SELECT *
				FROM ' . $this->pa_files_table . "
				WHERE file_id = '" . $file_id . "'";

			if ( !( $result = $this->db->sql_query( $sql ) ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Couldnt query File data', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $this->db->sql_fetchrow( $result );
			$cat_id = $file_data['file_catid'];

			$this->db->sql_freeresult( $result );
		}

		//
		// Further security.
		// Reset vars if no related data exist.
		//
		if ( $file_id && !$cat_id )
		{
			$file_id = 0;
		}

		if ( $cat_id && !$this->cat_rowset[$cat_id]['cat_id'] )
		{
			$cat_id = 0;
		}
				
		//
		// Load custom fields
		//
		$custom_field = new \orynider\pafiledb\core\custom_field($this->functions,
		$this->template,
		$this->user,
		$this->db,
		$this->request,
		$this->custom_table,
		$this->custom_data_table);
		$custom_field->init();
		
		// =======================================================
		// Delete
		// =======================================================
		if ( $do == 'delete' && $file_id )
		{
			if ( ( $this->auth_user[$cat_id]['auth_edit_file'] && $file_data['user_id'] == $this->user->data['user_id'] ) || $this->auth_user[$cat_id]['auth_mod'] )
			{
				//
				// Notification
				//
				$this->functions->update_add_item_notify($file_id, 'delete');

				//
				// Comments
				//
				if ($this->comments[$cat_id]['activated'] && $pafiledb_config['del_topic'])
				{
					if ( $this->comments[$cat_id]['internal_comments'] )
					{
						$sql = 'DELETE FROM ' . $this->pa_comments_table . "
						WHERE file_id = '" . $file_id . "'";

						if ( !( $this->db->sql_query( $sql ) ) )
						{
							$this->functions->message_die( GENERAL_ERROR, 'Couldnt delete comments', '', __LINE__, __FILE__, $sql );
						}
					}
					else
					{
						if ( $file_data['topic_id'] )
						{
							include( $this->module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
							$mx_pa_comments = new pafiledb_comments();
							$mx_pa_comments->init( $file_data, 'phpbb');
							$mx_pa_comments->post('delete_all', $file_data['topic_id']);
						}
					}
				}

				$this->delete_items( $file_id );
				$this->_pafiledb();
				$message = $this->user->lang['Filedeleted'] . '<br /><br />' . sprintf( $this->user->lang['Click_return'], '<a href="' . $this->functions->append_sid( $this->this_mxurl( "action=category&cat_id=" . $cat_id ) ) . '">', '</a>' );
				$this->functions->message_die( GENERAL_MESSAGE, $message );
			}
			else
			{
				$message = sprintf( $this->user->lang['Sorry_auth_delete'], $this->auth_user[$cat_id]['auth_delete_type'] );
				$this->functions->message_die( GENERAL_MESSAGE, $message );
			}
		}		
		
		if ($this->request->is_set_post('submit'))
		{
			$filecheck = $multiplier = '';

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

			$sql = 'SELECT cat_sub_dir
				FROM ' . $this->pa_cat_table . '
				WHERE cat_id = ' . (int) $target_folder;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$target = $row['cat_sub_dir'];
			$this->db->sql_freeresult($result);

			$upload_dir = $this->ext_path . 'pafiledb/uploads/' . $target;

			if (!$ftp_upload)
			{
				$upload_file = (isset($this->files_factory)) ? $fileupload->handle_upload('files.types.form', 'filename') : $fileupload->form_upload('filename');

				if (!$upload_file->get('uploadname'))
				{
					meta_refresh(3, $this->helper->route('orynider_pafiledb_controller_user_upload'));
					throw new http_exception(400, 'ACP_NO_FILENAME');
				}

				if (file_exists($this->root_path . $upload_dir . '/' . $upload_file->get('uploadname')))
				{
					meta_refresh(3, $this->helper->route('orynider_pafiledb_controller_user_upload'));
					throw new http_exception(400, 'ACP_UPLOAD_FILE_EXISTS');
				}

				$upload_file->move_file($upload_dir, false, false, false);
				@chmod($this->ext_path_web . 'pafiledb/uploads/' . $upload_file->get('uploadname'), 0644);

				if (sizeof($upload_file->error) && $upload_file->get('uploadname'))
				{
					$upload_file->remove();
					meta_refresh(3, $this->helper->route('orynider_pafiledb_controller_user_upload'));

					trigger_error(implode('<br />', $upload_file->error));
				}

				// End the upload
				$file_size = @filesize($this->root_path . $upload_dir . '/' . $upload_file->get('uploadname'));
				$sql_ary = array(
					'file_name'			=> $title,
					'file_desc'	 		=> $desc,
					'real_name'			=> $upload_file->get('uploadname'),
					'file_version'		=> $file_version,
					'file_catid'		=> $cat_option,
					'file_time'			=> time(),
					'cost_per_dl'		=> $costs_dl,
					'file_update_time'	=> time(),
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

				if ($file_size > ($max_file_size * $multiplier))
				{
					@unlink($this->root_path . $upload_dir . '/' . $upload_file->get('uploadname'));
					throw new http_exception(400, 'ACP_FILE_TOO_BIG');
				}
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
					$file_name = $title;
				}
				else
				{
					$file_name = $title . ' v' . $file_version;
				}
				
				$download_link = '[url=' . generate_board_url() . '/category?cat_id=' . $cat_option . ']' . $this->user->lang['ACP_CLICK'] . '[/url]';
				$download_subject = sprintf($this->user->lang['ACP_ANNOUNCE_TITLE'], $file_title);

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

		if ($this->auth->acl_get('a_'))
		{
			$sql_show_cat =	'';
		}
		else
		{
			$sql_show_cat = ' WHERE cat_name_show = ' . (int) $cat_name_show . '';
		}

		// Check if categories exists
		$sql = 'SELECT COUNT(cat_id) AS total_cats
			FROM ' . $this->pa_cat_table . '
			' . $sql_show_cat;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total_cats = $row['total_cats'];
		$this->db->sql_freeresult($result);

		if ($total_cats <= 0)
		{
			throw new http_exception(400, 'FILES_NO_CAT_IN_UPLOAD');
		}

		$sql = 'SELECT *
			FROM ' . $this->pa_cat_table . '
			' . $sql_show_cat . '
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

		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off') ? '' : ' enctype="multipart/form-data"';

		$this->template->assign_vars(array(
			'ID'				=> $file_id,
			'TITLE'				=> $title,
			'DESC'				=> $desc,
			'FILENAME'			=> $filename,
			'DL_VERSION'		=> $file_version,
			'PARENT_OPTIONS'	=> $cat_options,
			'ALLOWED_SIZE'		=> sprintf($this->user->lang['FILES_NEW_DOWNLOAD_SIZE'], $max_file_size, $unit),
			'S_FORM_ENCTYPE'	=> $form_enctype,
		));

		// Build navigation link
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang('FILES_UPLOAD_SECTION'),
			'U_VIEW_FORUM'	=> $this->helper->route('orynider_pafiledb_controller_user_upload'),
		));

		/* assign template lang keys if they not assigned yet start * /
		//reset($this->user->lang);
		foreach($this->user->lang as $key => $value)
		{		
			// Check compat
			$this->template->assign_var('L_' . strtoupper($key), $value);
		}
		/* assign template lang keys if they not assigned yet ends */		
		
		$this->functions->assign_authors();
		$this->template->assign_var('PAFILEDB_FOOTER_VIEW', true);

		// Send all data to the template file
		return $this->helper->render('upload_body.html', $this->user->lang('FILES_TITLE') . ' &bull; ' . $this->user->lang('FILES_UPLOAD_SECTION'));
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

		meta_refresh(3, $this->helper->route('orynider_pafiledb_controller_user_upload'));

		trigger_error($this->user->lang[$user_message]);
	}
}
