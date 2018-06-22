<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_templates.php,v 1.11 2008/06/03 20:17:06 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, FlorinCB] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

if(defined('IN_PHPBB') && !defined('PHPBB_URL'))
{
	@define('PHPBB_URL', generate_board_url() . '/');			
}

@define('MX_BUTTON_IMAGE'	, 10);
@define('MX_BUTTON_TEXT'		, 20);
@define('MX_BUTTON_GENERIC'	, 30);
/**#@-*/

/**
 * Class: pafiledb_templates.
 *
 * @package Style
 * @author FlorinCB
 * @access public
 */
class pafiledb_templates
{
	/** @var \orynider\pafiledb\core\functions */
	//protected $functions;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\cache\cache */
	protected $cache;
	/** @var \orynider\pafiledb\core\functions_cache */
	protected $functions_cache;
	/** @var \phpbb\config\config */
	protected $config;	
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;	
	/** @var string */
	protected $php_ext;
	/** @var string phpBB root path */
	protected $root_path;

	var $images;	

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions						$functions
	* @param \phpbb\template\template		 					$template
	* @param \phpbb\user									$user		
	* @param \phpbb\db\driver\driver_interface					$db
	* @param \phpbb\request\request		 					$request
	* @param \phpbb\extension\manager							$ext_manager	
	* @param string 										$custom_table
	* @param string 										$custom_data_table
	*
	*/
	public function __construct(
		//\orynider\pafiledb\core\pafiledb_functions $functions,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\cache\driver\driver_interface $cache,
		\orynider\pafiledb\core\pafiledb_cache $pafiledb_cache,		
		\phpbb\config\config $config,		
		\phpbb\request\request $request,
		\phpbb\extension\manager $ext_manager,	
		$php_ext, 
		$root_path)
	{
		//$this->functions 			= $functions;
		$this->template 			= $template;	
		/** Info: user setup sandbox for phpBB Proteus
		$this->user->data = $user_data;
		$this->user->lang_name = $user_lang_name;
		$this->user->date_format = $user_date_format;
		$this->user->style = $db->sql_fetchrow($result);	
		$this->user->style[$key] = htmlspecialchars($this->user->style[$key]);
		$this->user->img_lang = $this->user->lang_name;
		*/		
		$this->user 				= $user;
		$this->db 					= $db;
		$this->pafiledb_cache 		= $pafiledb_cache;
		$this->cache 				= $cache;		
		/** Info: config setup sandbox for phpBB Proteus		
		'user_lang'			=> (string) $config['default_lang'],
		'user_style'		=> (int) $config['default_style'],
		*/			
		$this->config 				= $config;		
		$this->request 				= $request;
		$this->ext_manager	 		= $ext_manager;	
		$this->php_ext 				= $php_ext;		
		$this->phpbb_root_path 		= $root_path;
		$this->module_root_path 	= $this->ext_path = $this->ext_manager->get_extension_path('orynider/pafiledb', true);
		$this->backend 				= $this->confirm_backend(true);		
			
		
		//
		// We setup common user language variables
		//		
		$this->user_lang = !empty($user->lang['USER_LANG']) ? $user->lang['USER_LANG'] : $this->encode_lang($user->lang_name);
		$user_lang = $user->user_lang;
		
		$this->user_language		= $this->encode_lang($user->lang_name);
		$this->default_language		= $this->encode_lang($config['default_lang']);
		
		$this->user_language_name		= $this->decode_lang($user->lang_name);
		$this->default_language_name	= $this->decode_lang($config['default_lang']);
		
		$counter = 0; //First language pack lang_id		
		$user->lang_ids = array();
		$user->lang_list = $this->get_lang_list();
		
		if (is_array($user->lang_list))
		{		
			foreach ($user->lang_list as $user->lang_english_name => $user->lang_local_name)
			{
				$user->lang_ids[$user->lang_english_name] = $counter;
				$counter++;	
			}	
		}	
		
		$user->lang_entries = array(
			'lang_id' => !empty($user->lang_ids['lang_' . $this->user_language_name]) ? $user->lang_ids['lang_' . $this->user_language_name] : $counter,
			'lang_iso' => !empty($user->lang['USER_LANG']) ? $user->lang['USER_LANG'] : $this->encode_lang($this->lang_name),
			'lang_dir' => 'lang_' . $this->lang_name,
			'lang_english_name' => $this->user_language_name,
			'lang_local_name' => $this->ucstrreplace('lang_', '', $this->lang_name),
			'lang_author' => !empty($user->lang['TRANSLATION_INFO']) ? $user->lang['TRANSLATION_INFO'] : 'Language pack author not set in ACP.'
		);
		
		//
		// Finishing setting language variables to ouput
		//
		$this->lang_iso = $user->lang_iso = $user->lang_entries['lang_iso'];		
		$this->lang_dir = $user->lang_dir = $user->lang_entries['lang_dir'];
		$this->lang_english_name = $user->lang_english_name = $user->lang_entries['lang_english_name'];		
		$this->lang_local_name = $user->lang_local_name = $user->lang_entries['lang_local_name'];
		
		//
		// We setup common template variables
		//				
		$this->style = $this->theme = $user->style;
		$this->style['body_background'] = $this->style['body_background'] ? $this->style['body_background'] : 'ffffff';
		$this->template_name = isset($user->style['style_path']) ? $user->style['style_path'] : $this->theme['template_name'];		
		
		$this->template_path = ($this->backend !== 'phpbb2') ? 'styles/' : 'templates/';

		//Setup cloned template	as prosilver based for phpBB3 styles		
		if( @file_exists(@phpbb_realpath($this->phpbb_root_path . $this->template_path . $this->template_name . '/style.cfg')) )
		{
			$cfg = parse_cfg_file($this->phpbb_root_path . $this->template_path . $this->template_name . '/style.cfg');
			$this->cloned_template_name = !empty($cfg['parent']) ? $cfg['parent'] : 'prosilver';
			$this->cloned_template_path = $this->template_path . $this->cloned_template_name;			
			$this->default_template_name = !empty($cfg['parent']) ? $cfg['parent'] : 'prosilver';		
		}
		
		//Setup current_template_path	
		$this->default_current_template_path = $this->template_path . $this->default_current_template_name;
		$this->current_template_path = $this->template_path . $this->template_name;
		$this->theme['theme_path'] = $this->template_name;			
	
		$parsed_array = $this->cache->get('_cfg_' . $this->template_path);

		if ($parsed_array === false)
		{
			$parsed_array = array();
		}	
		
		if( @file_exists(@phpbb_realpath($this->phpbb_root_path . $this->current_template_path . '/style.cfg')) )
		{
			//parse phpBB3 style cfg file
			$cfg_file_name = 'style.cfg';			
			$cfg_file = $this->phpbb_root_path . $this->current_template_path . '/style.cfg';
					
			if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
			{
				// Re-parse cfg file
				$parsed_array = parse_cfg_file($cfg_file);		
				$parsed_array['filetime'] = @filemtime($cfg_file);				
				$this->cache->put('_cfg_' . $this->template_path, $parsed_array);
			}							
		}
		else
		{	
			//parse phpBB2 style cfg file	
			$cfg_file_name = $this->template_name . '.cfg';
			$cfg_file = $this->phpbb_root_path . $this->current_template_path . '/' . $cfg_file_name;
			
			if (file_exists($this->phpbb_root_path .  $this->current_template_path . '/' . $cfg_file_name))
			{
				if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
				{				
					$parsed_array = parse_cfg_file($cfg_file);		
					$parsed_array['filetime'] = @filemtime($cfg_file);
					$this->cache->put('_cfg_' . $this->template_path, $parsed_array);				
				}
			}		
		}
		
		$check_for = array(
			'pagination_sep'    => (string) ', '
		);

		foreach ($check_for as $key => $default_value)
		{
			$this->style[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			$this->theme[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			settype($this->style[$key], gettype($default_value));
			settype($this->theme[$key], gettype($default_value));
			if (is_string($default_value))
			{
				$this->style[$key] = htmlspecialchars($this->style[$key]);
				$this->theme[$key] = htmlspecialchars($this->theme[$key]);
			}
		}
		
 		// If the style author specified the theme needs to be cached
		// (because of the used paths and variables) than make sure it is the case.
		// For example, if the theme uses language-specific images it needs to be stored in db.
		if (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/stylesheet.css'))
		{
			//phpBB3 Style Sheet
			$theme_file = 'stylesheet.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/theme/';
			$stylesheet = file_get_contents("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/stylesheet.css");
		}
		else
		{	
			//phpBB2 Style Sheet	
			$theme_file = !empty($this->theme['head_stylesheet']) ?  $this->theme['head_stylesheet'] : $this->template_name . '.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/';
			if (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/' . $theme_file))
			{
				$stylesheet = file_get_contents("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/{$theme_file}");
			}		
		}		
		
		if (!empty($stylesheet))
		{			
			// Match CSS imports
			$matches = array();
			preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);
			
			if (sizeof($matches))
			{
				$content = '';
				foreach ($matches[0] as $idx => $match)
				{
					if ($content = @file_get_contents("{$this->phpbb_root_path}{$css_file_path}" . $matches[1][$idx]))
					{
						$content = trim($content);
					}
					else
					{
						$content = '';
					}
					$stylesheet = str_replace($match, $content, $stylesheet);
				}
				unset($content);
			}

			$stylesheet = str_replace('./', $css_file_path, $stylesheet);

			$theme_info = array(
				'theme_data'	=> $stylesheet,
				'theme_mtime'	=> time(),
				'theme_storedb'	=> 0
			);
			$theme_data = &$theme_info['theme_data'];
		}			
		
		//		
		// - First try old Olympus image sets then phpBB2  and phpBB3 Proteus template lang images 	
		//		
		if (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/imageset/"))
		{
			$this->imageset_path = '/imageset/'; //Olympus ImageSet
			$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . $this->lang_iso)) ? $this->lang_iso : $this->default_language;
			$this->img_lang_dir = $this->img_lang;
			$this->imageset_backend = 'olympus';
		}
		elseif (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/images/"))
		{			
			$this->imageset_path = '/theme/images/';  //phpBB3 Images
			if ((@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->user_language_name}")) || (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->default_language_name}")))
			{
				$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . 'lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
				$this->img_lang_dir = 'lang_' . $this->img_lang;
				$this->imageset_backend = 'phpbb2';	
			}
			if ((@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->user_language}")) || (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->default_language}")))
			{
				$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . $this->user_language_name)) ? $this->user_language : $this->default_language;
				$this->img_lang_dir = $this->img_lang;
				$this->imageset_backend = 'phpbb3';	
			}			
		}		
		elseif (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/images/"))
		{
			$this->imageset_path = '/images/';  //phpBB2 Images
			$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . '/images/lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
			$this->img_lang_dir = 'lang_' . $this->img_lang;
			$this->imageset_backend = 'phpbb2';	
		}
				
		//		
		// Olympus image sets main images
		//		
		if (@file_exists("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg"))
		{		
			$cfg_data_imageset = parse_cfg_file("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg");
			
			foreach ($cfg_data_imageset as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}
				
				if (strpos($image_name, '') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);				
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> (int) $style_id,
						'image_lang'		=> '',
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}		
		}
		
		//		
		// - Olympus image sets lolalised images	
		//		
		if (@file_exists("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg"))
		{
			$cfg_data_imageset_data = parse_cfg_file("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg");
			foreach ($cfg_data_imageset_data as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}

				if (strpos($image_name, '') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> !empty($this->theme['imageset_id']) ? (int) $this->theme['imageset_id'] : 0,
						'image_lang'		=> (string) $this->img_lang,
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}
		}
		/**
		 * Define backend specific style defs
		 */		
		$this->setup_style();
		
		/**
		 * Define module or extension specific style defs
		 */
		$this->_load_images($this->module_root_path);
		
	}
	
	/**
	 * Setup style
	 *
	 * Define backend specific style defs
	 *
	 */
	function setup_style()
	{		
		//$template = $this->template;		 
		$this->module_root_path = $this->ext_path = $this->ext_manager->get_extension_path('orynider/pafiledb', true);		
		@define('IP_ROOT_PATH', $this->phpbb_root_path); //for ICY-PHOENIX Styles
		
		if(is_dir($this->phpbb_root_path . $this->current_template_path . '/theme/images/'))
		{
			$current_template_images = $this->current_template_images = $this->current_template_path . "/theme/images";						
		}
		elseif(is_dir($this->phpbb_root_path . $this->current_template_path . '/images/'))
		{
			$current_template_images = $this->current_template_images = $this->current_template_path . "/images";					
		}			
		
		$phpbb_root_path = $this->phpbb_root_path;			
		$current_template_path = $this->template_path . $this->template_name;
			
		$row = array();			
		
		$row = $this->style;
		
		if(@file_exists(@phpbb_realpath($this->phpbb_root_path . $this->template_path . $this->template_name . '/' . $this->template_name . '.cfg')) )
		{
			//include($this->phpbb_root_path . $this->template_path . $this->template_name . '/' . $this->template_name . '.cfg');
				
			if (!defined('TEMPLATE_CONFIG'))
			{
				//
				// Do not alter this line!
				//
				@define(TEMPLATE_CONFIG, TRUE);					
			}				
		}	
		elseif( @file_exists(@phpbb_realpath($this->phpbb_root_path . $this->template_path . $this->template_name . "/style.cfg")) )
		{
			//
			// Do not alter this line!
			//
			@define(TEMPLATE_CONFIG, TRUE);

			//		
			// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
			//		
			if ( is_dir($this->phpbb_root_path . $this->current_template_path . '/theme/images/') )
			{
				$this->current_template_images = $this->current_template_path . '/theme/images';
			}		
			else if ( is_dir($this->phpbb_root_path . $this->current_template_path  . '/images/') )
			{		
				$this->current_template_images = $this->current_template_path  . '/images';
			}		
			if ( is_dir($this->phpbb_root_path . $this->current_template_path  . '/imageset/') )
			{		
				$this->current_template_images = $this->current_template_path  . '/imageset';
			}
				
			$current_template_images = $this->current_template_images;
			//die($this->imageset_path);
			//die($current_template_images);			
			$images['icon_quote'] = "$current_template_images/{LANG}/" . $this->img('icon_post_quote.gif', '', '', '', 'filename');
			$images['icon_edit'] = "$current_template_images/{LANG}/" . $this->img('icon_post_edit.gif', '', '', '', 'filename');			
			$images['icon_search'] = "$current_template_images/{LANG}/" . $this->img('icon_user_search.gif', '', '', '', 'filename');
			$images['icon_profile'] = "$current_template_images/{LANG}/" . $this->img('icon_user_profile.gif', '', '', '', 'filename');
			$images['icon_pm'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_pm.gif', '', '', '', 'filename');
			$images['icon_email'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_email.gif', '', '', '', 'filename');
			$images['icon_delpost'] = "$current_template_images/{LANG}/" . $this->img('icon_post_delete.gif', '', '', '', 'filename');
			$images['icon_ip'] = "$current_template_images/{LANG}/" . $this->img('icon_user_ip.gif', '', '', '', 'filename');
			$images['icon_www'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_www.gif', '', '', '', 'filename');
			$images['icon_icq'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_icq_add.gif', '', '', '', 'filename');
			$images['icon_aim'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_aim.gif', '', '', '', 'filename');
			$images['icon_yim'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_yim.gif', '', '', '', 'filename');
			$images['icon_msnm'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_msnm.gif', '', '', '', 'filename');
			$images['icon_minipost'] = "$current_template_images/" . $this->img('icon_post_target.gif', '', '', '', 'filename');
			$images['icon_gotopost'] = "$current_template_images/" . $this->img('icon_gotopost.gif', '', '', '', 'filename');
			$images['icon_minipost_new'] = "$current_template_images/" . $this->img('icon_post_target_unread.gif', '', '', '', 'filename');
			$images['icon_latest_reply'] = "$current_template_images/" . $this->img('icon_latest_reply.gif', '', '', '', 'filename');
			$images['icon_newest_reply'] = "$current_template_images/" . $this->img('icon_newest_reply.gif', '', '', '', 'filename');

			$images['forum'] = "$current_template_images/" . $this->img('forum_read.gif', '', '27', '', 'filename');
			$images['forum_new'] = "$current_template_images/" . $this->img('forum_unread.gif', '', '', '', 'filename');
			$images['forum_locked'] = "$current_template_images/" . $this->img('forum_read_locked.gif', '', '', '', 'filename');
			
			// Begin Simple Subforums MOD
			$images['forums'] = "$current_template_images/" . $this->img('forum_read_subforum.gif', '', '', '', 'filename');
			$images['forums_new'] = "$current_template_images/" . $this->img('forum_unread_subforum.gif', '', '', '', 'filename');
			// End Simple Subforums MOD

			$images['folder'] = "$current_template_images/" . $this->img('topic_read.gif', '', '', '', 'filename');
			$images['folder_new'] = "$current_template_images/" . $this->img('topic_unread.gif', '', '', '', 'filename');
			$images['folder_hot'] = "$current_template_images/" . $this->img('topic_read_hot.gif', '', '', '', 'filename');
			$images['folder_hot_new'] = "$current_template_images/" . $this->img('topic_unread_hot.gif', '', '', '', 'filename');
			$images['folder_locked'] = "$current_template_images/" . $this->img('topic_read_locked.gif', '', '', '', 'filename');
			$images['folder_locked_new'] = "$current_template_images/" . $this->img('topic_unread_locked.gif', '', '', '', 'filename');
			$images['folder_sticky'] = "$current_template_images/" . $this->img('sticky_read_mine.gif', '', '', '', 'filename');
			$images['folder_sticky_new'] = "$current_template_images/" . $this->img('sticky_unread_mine.gif', '', '', '', 'filename');
			$images['folder_announce'] = "$current_template_images/" . $this->img('announce_read.gif', '', '', '', 'filename');
			$images['folder_announce_new'] = "$current_template_images/" . $this->img('announce_unread.gif', '', '', '', 'filename');
			
			$images['post_new'] = "$current_template_images/{LANG}/" . $this->img('button_topic_new.gif', '', '', '', 'filename');
			$images['post_locked'] = "$current_template_images/{LANG}/" . $this->img('button_topic_locked.gif', '', '', '', 'filename');
			$images['reply_new'] = "$current_template_images/{LANG}/" . $this->img('button_topic_reply.gif', '', '', '', 'filename');
			$images['reply_locked'] = "$current_template_images/{LANG}/" . $this->img('icon_post_target_unread.gif', '', '', '', 'filename');

			$images['pm_inbox'] = "$current_template_images/" . $this->img('msg_inbox.gif', '', '', '', 'filename');
			$images['pm_outbox'] = "$current_template_images/" . $this->img('msg_outbox.gif', '', '', '', 'filename');
			$images['pm_savebox'] = "$current_template_images/" . $this->img('msg_savebox.gif', '', '', '', 'filename');
			$images['pm_sentbox'] = "$current_template_images/" . $this->img('msg_sentbox.gif', '', '', '', 'filename');
			$images['pm_readmsg'] = "$current_template_images/" . $this->img('topic_read.gif', '', '', '', 'filename');
			$images['pm_unreadmsg'] = "$current_template_images/" . $this->img('topic_unread.gif', '', '', '', 'filename');
			$images['pm_replymsg'] = "$current_template_images/{LANG}/" . $this->img('reply.gif', '', '', '', 'filename');
			$images['pm_postmsg'] = "$current_template_images/{LANG}/" . $this->img('msg_newpost.gif', '', '', '', 'filename');
			$images['pm_quotemsg'] = "$current_template_images/{LANG}/" . $this->img('icon_quote.gif', '', '', '', 'filename');
			$images['pm_editmsg'] = "$current_template_images/{LANG}/" . $this->img('icon_edit.gif', '', '', '', 'filename');
			$images['pm_new_msg'] = "";
			$images['pm_no_new_msg'] = "";

			$images['Topic_watch'] = "";
			$images['topic_un_watch'] = "";
			$images['topic_mod_lock'] = "$current_template_images/" . $this->img('topic_lock.gif', '', '', '', 'filename');
			$images['topic_mod_unlock'] = "$current_template_images/" . $this->img('topic_unlock.gif', '', '', '', 'filename');
			$images['topic_mod_split'] = "$current_template_images/" . $this->img('topic_split.gif', '', '', '', 'filename');
			$images['topic_mod_move'] = "$current_template_images/" . $this->img('topic_move.gif', '', '', '', 'filename');
			$images['topic_mod_delete'] = "$current_template_images/" . $this->img('topic_delete.gif', '', '', '', 'filename');

			$images['voting_graphic'][0] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][1] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][2] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][3] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][4] = "$current_template_images/voting_bar.gif";
			
			//
			// Import phpBB Graphics, prefix with PHPBB_URL, and apply LANG info
			//
			while( list($key, $value) = @each($images) )
			{
				if (is_array($value))
				{
					foreach( $value as $key2 => $val2 )
					{
						$this->images[$key][$key2] = $images[$key][$key2] = PHPBB_URL . $val2;
					}
				}
				else
				{
					$this->images[$key] = $images[$key] = str_replace('{LANG}', $img_dir, $value);
					$this->images[$key] = $images[$key] = PHPBB_URL . $images[$key];
				}
				
				if(empty($images['forum']))
				{
					//print_r('Your style configuration file has a typo! ');
					//print_r($images);
					$images['forum'] = 'folder.gif';
				}						
				
				/* Here we overwrite phpBB images from the template db or configuration file  */		
				$rows = $this->image_rows($images);		
				
				foreach ($rows as $row)
				{
					$row['image_filename'] = rawurlencode($row['image_filename']);
					
					if(empty($row['image_name']))
					{
						//print_r('Your style configuration file has a typo! ');
						//print_r($row);
						$row['image_name'] = 'spacer.gif';
					}
								
					$this->img_array[$row['image_name']] = $row;				
				}			
			}			
			
			//include($this->phpbb_root_path . $this->cloned_current_template_path . '/' . $this->cloned_template_name . '.cfg');
				
			//
			// Vote graphic length defines the maximum length of a vote result
			// graphic, ie. 100% = this length
			//
			$config['vote_graphic_length'] = 205;
			$config['privmsg_graphic_length'] = 175;			
		}
		else		
		{		
			if ((@include $this->phpbb_root_path . $this->template_path . "prosilver2/prosilver2.cfg") === false)
			{
				$this->message_die(CRITICAL_ERROR, "Could not open phpBB $this->template_name template config file", '', __LINE__, __FILE__);
			}
			else
			{
				print_r("Could not open phpBB $this->template_name template config file");
			}			
		}

		//
		// We have no template to use - die
		//
		if ( !defined('TEMPLATE_CONFIG') )
		{
			//
			// Load phpBB Template configuration data
			// - Last try current template
			//		
			if ((@include $this->phpbb_root_path . $this->template_path . $this->template_name . '/' . $this->template_name . '.cfg') === false)
			{
				$this->message_die(CRITICAL_ERROR, "Could not open phpBB $this->template_name template config file", '', __LINE__, __FILE__);
			}			
		}

		$parsed_array = $this->cache->get('_cfg_' . $this->template_path);

		if ($parsed_array === false)
		{
			$parsed_array = array();
		}	
		
		//
		// Try phpBB2 then phpBB3 style configuration file
		//		
		if(@file_exists(@phpbb_realpath($this->phpbb_root_path . $current_template_path . '/' . $template_name . '.cfg')) )
		{		
			//parse phpBB2 style cfg file	
			$cfg_file_name = $this->template_name . '.cfg';
			$cfg_file = $this->phpbb_root_path . $this->current_template_path . '/' . $cfg_file_name;
			
			if (file_exists($this->phpbb_root_path .  $this->current_template_path . '/' . $cfg_file_name))
			{
				if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
				{				
					$parsed_array = parse_cfg_file($cfg_file);		
					$parsed_array['filetime'] = @filemtime($cfg_file);
					$this->cache->put('_cfg_' . $this->template_path, $parsed_array);				
				}
			}		
		}
		elseif( @file_exists(@phpbb_realpath($this->phpbb_root_path . $this->current_template_path . '/style.cfg')) )
		{
			//parse phpBB3 style cfg file
			$cfg_file_name = 'style.cfg';			
			$cfg_file = $this->phpbb_root_path . $this->current_template_path . '/style.cfg';
					
			if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
			{
				// Re-parse cfg file
				$parsed_array = parse_cfg_file($cfg_file);		
				$parsed_array['filetime'] = @filemtime($cfg_file);				
				$this->cache->put('_cfg_' . $this->template_path, $parsed_array);
			}							
		}		
		
		$check_for = array(
			'pagination_sep'    => (string) ', '
		);

		foreach ($check_for as $key => $default_value)
		{
			$this->style[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			$this->theme[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			settype($this->style[$key], gettype($default_value));
			settype($this->theme[$key], gettype($default_value));
			
			if (is_string($default_value))
			{
				$this->style[$key] = htmlspecialchars($this->style[$key]);
				$this->theme[$key] = htmlspecialchars($this->theme[$key]);
			}
		}
		
 		// If the style author specified the theme needs to be cached
		// (because of the used paths and variables) than make sure it is the case.
		// For example, if the theme uses language-specific images it needs to be stored in db.
		if (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/stylesheet.css'))
		{
			//phpBB3 Style Sheet
			$theme_file = 'stylesheet.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/theme/';
			$stylesheet = file_get_contents("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/stylesheet.css");
		}
		else
		{	
			//phpBB2 Style Sheet	
			$theme_file = !empty($this->theme['head_stylesheet']) ?  $this->theme['head_stylesheet'] : $this->template_name . '.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/';
			if (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/' . $theme_file))
			{
				$stylesheet = file_get_contents("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/{$theme_file}");
			}		
		}		
		
		if (!empty($stylesheet))
		{			
			// Match CSS imports
			$matches = array();
			preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);
			
			if (sizeof($matches))
			{
				$content = '';
				foreach ($matches[0] as $idx => $match)
				{
					if ($content = @file_get_contents("{$this->phpbb_root_path}{$css_file_path}" . $matches[1][$idx]))
					{
						$content = trim($content);
					}
					else
					{
						$content = '';
					}
					$stylesheet = str_replace($match, $content, $stylesheet);
				}
				unset($content);
			}

			$stylesheet = str_replace('./', $css_file_path, $stylesheet);

			$theme_info = array(
				'theme_data'	=> $stylesheet,
				'theme_mtime'	=> time(),
				'theme_storedb'	=> 0
			);
			$theme_data = &$theme_info['theme_data'];
		}			
		
		//		
		// - First try old Olympus image sets then phpBB2  and phpBB3 Proteus template lang images 	
		//		
		if (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/imageset/"))
		{
			$this->imageset_path = '/imageset/'; //Olympus ImageSet
			$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . $this->lang_iso)) ? $this->lang_iso : $this->default_language;
			$this->img_lang_dir = $this->img_lang;
			$this->imageset_backend = 'olympus';		
		}
		elseif (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/images/"))
		{
			if ((@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->user_language_name}")) || (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->default_language_name}")))
			{
				$this->imageset_path = '/theme/images/';  //phpBB3 Images				
				$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . 'lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
				$this->img_lang_dir = 'lang_' . $this->img_lang;
				$this->imageset_backend = 'phpbb2';	
			}
			if ((@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->user_language}")) || (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->default_language}")))
			{
				$this->imageset_path = '/theme/images/';  //phpBB3 Images				
				$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . $this->user_language_name)) ? $this->user_language : $this->default_language;
				$this->img_lang_dir = $this->img_lang;
				$this->imageset_backend = 'phpbb3';	
			}			
		}		
		elseif (@is_dir("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}/images/"))
		{
			$this->imageset_path = '/images/';  //phpBB2 Images
			$this->img_lang = (file_exists($this->phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . '/images/lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
			$this->img_lang_dir = 'lang_' . $this->img_lang;
			$this->imageset_backend = 'phpbb2';	
		}
			
		//		
		// phpBB2 image sets main images
		//				
		$img_dir = isset($this->img_lang_dir) ? $this->img_lang_dir : 'lang_' . $this->default_language_name;
		
		//
		// Import phpBB Graphics, prefix with PHPBB_URL, and apply LANG info
		//
		while( list($key, $value) = @each($images) )
		{
			if (is_array($value))
			{
				foreach( $value as $key2 => $val2 )
				{
					$this->images[$key][$key2] = $images[$key][$key2] = PHPBB_URL . $val2;
				}
			}
			else
			{
				$this->images[$key] = $images[$key] = str_replace('{LANG}', $img_dir, $value);
				$this->images[$key] = $images[$key] = PHPBB_URL . $images[$key];
			}
			
			if(empty($images['forum']))
			{
				//print_r('Your style configuration file has a typo! ');
				//print_r($images);
				$images['forum'] = 'folder.gif';
			}						
			
			/* Here we overwrite phpBB images from the template db or configuration file  */		
			$rows = $this->image_rows($images);		
			
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					//print_r('Your style configuration file has a typo! ');
					//print_r($row);
					$row['image_name'] = 'spacer.gif';
				}
							
				$this->img_array[$row['image_name']] = $row;				
			}			
		}
				
		// Import phpBB Olympus image sets main images
		//		
		if (@file_exists("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg"))
		{		
			$cfg_data_imageset = parse_cfg_file("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg");
			
			foreach ($cfg_data_imageset as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}
				
				if (strpos($image_name, '') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);				
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> (int) $style_id,
						'image_lang'		=> '',
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}		
		}
		
		//		
		// - Olympus image sets lolalised images	
		//		
		if (@file_exists("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg"))
		{
			$cfg_data_imageset_data = parse_cfg_file("{$this->phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg");
			foreach ($cfg_data_imageset_data as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}

				if (strpos($image_name, '') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> !empty($this->theme['imageset_id']) ? (int) $this->theme['imageset_id'] : 0,
						'image_lang'		=> (string) $this->img_lang,
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}
		}
		
		//		
		// - Import phpBB phpBB3 Rhea and Proteus images 	
		//		
		if (empty($this->img_array))
		{
			/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 			
			* /
			
			/* Here we overwrite phpBB images from the template db or configuration file  */		
			$rows = $this->image_rows($this->images);		
			
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					//print_r('Your style configuration file has a typo! ');
					$row['image_name'] = 'spacer.gif';
				}
							
				$this->img_array[$row['image_name']] = $row;				
			}	
		}			
	}

	/**
	 * Enter _load_images
	 * Define module or extension specific style defs
	 *
	 * @access private
	 * @param core_type $module_root_path
	 */
	function _load_images($module_root_path = '')
	{		
		if(defined('IN_PHPBB') && !defined('PORTAL_URL'))
		{
			@define('PORTAL_URL', generate_board_url() . '/');			
		}		
		/**
		*
		*/		
		$this->ext_path = $this->ext_manager->get_extension_path('orynider/pafiledb', true);		
		$this->module_root_path = !empty($module_root_path) ? $module_root_path : $this->ext_path;
		
		//		
		if (empty($this->images))
		{
			$this->setup_style();
		}		
		
		//This will  keep loaded images
		$core_images = $this->images;
			
		//unset($GLOBALS['MX_TEMPLATE_CONFIG']);
		$mx_template_config = false;
		
		/*
		* Load module cfg
		*/
		$moduleCfgFile = str_replace('/', '', str_replace(array('modules/', 'ext/'), '', $this->module_root_path));
		
		switch (PORTAL_BACKEND)
		{
			case 'internal':
			case 'smf2':
			case 'mybb':
				$this->template_name2 = 'mxSilver';
				@define('TEMPLATE_CONFIG', TRUE);
			break;
			
			case 'phpbb2':
				$this->template_name2 = 'subSilver';			
			break;
			
			case 'phpbb3':
			case 'olympus':
			case 'ascraeus':		
			case 'rhea':
				$this->template_name2 = 'subsilver2';			
				/** /
				// Here we overwrite phpBB images from the template configuration file with images from database
				$images['icon_quote'] =  $this->images('icon_quote');
				$images['icon_edit'] = $this->images('icon_edit');
				$images['icon_search'] = $this->images('icon_search');
				$images['icon_profile'] = $this->images('icon_profile');
				$images['icon_pm'] = $this->images('icon_pm');
				$images['icon_email'] = $this->images('icon_email');
				$images['icon_delpost'] = $this->images('icon_delpost');
				$images['icon_ip'] = $this->images('icon_ip');
				$images['icon_www'] = $this->images('icon_www');
				$images['icon_icq'] = $this->images('icon_icq');
				$images['icon_aim'] = $this->images('icon_aim');
				$images['icon_yim'] = $this->images('icon_yim');
				$images['icon_msnm'] = $this->images('icon_msnm');
				$images['icon_minipost'] = $this->images('icon_minipost');
				$images['icon_gotopost'] = $this->images('icon_gotopost');
				$images['icon_minipost_new'] = $this->images('icon_minipost_new');
				$images['icon_latest_reply'] = $images['icon_topic_latest'] = $this->images('icon_topic_latest');
				$images['icon_newest_reply'] = $this->images('icon_newest_reply');
				
				$images['forum'] = $this->images('forum');
				$images['forums'] = $this->images('forums');
				$images['forum_new'] = $this->images('forum_new');
				$images['forum_locked'] = $this->images('forum_locked');
				
				$images['folder'] = $images['topic_read'] = $this->images('topic_read');
				$images['folder_new'] = $images['topic_unread'] = $this->images('topic_unread');
				$images['folder_hot'] = $images['topic_hot'] = $this->images('topic_hot');
				$images['folder_hot_new'] = $images['topic_hot_unread'] = $this->images('topic_hot_unread');
				$images['folder_locked'] = $images['topic_locked'] = $this->images('topic_locked');
				$images['folder_locked_new'] = $images['topic_locked_unread'] = $this->images('topic_locked_unread');
				$images['folder_sticky'] = $images['topic_sticky'] = $this->images('topic_sticky');
				$images['folder_sticky_new'] = $images['topic_sticky_unread'] = $this->images('topic_sticky_unread');
				$images['folder_announce'] = $images['topic_announce'] = $this->images('topic_announce');
				$images['folder_announce_new'] = $images['topic_announce_unread'] = $this->images('topic_announce_unread');
				
				$images['post_new'] = $this->images('post_new');
				$images['post_locked'] = $this->images('post_locked');
				$images['reply_new'] = $this->images('reply_new');
				$images['reply_locked'] = $this->images('reply_locked');
				
				$images['pm_inbox'] = $this->images('pm_inbox');
				$images['pm_outbox'] = $this->images('pm_outbox');
				$images['pm_savebox'] = $this->images('pm_savebox');
				$images['pm_sentbox'] = $this->images('pm_sentbox');
				$images['pm_readmsg'] = $this->images('pm_readmsg');
				$images['pm_unreadmsg'] = $this->images('pm_unreadmsg');
				$images['pm_replymsg'] = $this->images('pm_replymsg');
				$images['pm_postmsg'] = $this->images('pm_postmsg');
				$images['pm_quotemsg'] = $this->images('pm_quotemsg');
				$images['pm_editmsg'] = $this->images('pm_editmsg');
				$images['pm_new_msg'] = $this->images('pm_new_msg');
				$images['pm_no_new_msg'] = $this->images('pm_no_new_msg');
				
				$images['Topic_watch'] = $this->images('Topic_watch');
				$images['topic_un_watch'] = $this->images('topic_un_watch');
				$images['topic_mod_lock'] = $this->images('topic_mod_lock');
				$images['topic_mod_unlock'] = $this->images('topic_mod_unlock');
				$images['topic_mod_split'] = $this->images('topic_mod_split');
				$images['topic_mod_move'] = $this->images('topic_mod_move');
				$images['topic_mod_delete'] = $this->images('topic_mod_delete');
				
				$images['voting_graphic'] = $this->images('voting_graphic');
				/**/
			break;				
		}
		
		/*
		* Load MX-Publisher Template configuration data
		* - First try current template
		*/
		$current_template_path = $current_template_path_d = $this->module_root_path . $this->current_template_path;
		$cloned_template_path = $cloned_template_path_d = $this->module_root_path . $this->cloned_current_template_path;
		$default_template_path = $default_template_path_d = $this->module_root_path . $this->default_current_template_path;
		$template_name = $template_name_d = $this->template_name;
		$template_config_d = TEMPLATE_CONFIG;
				
		/**
		/* Try phpBB2 then phpBB3 style 
		/* mx_user->_load_mxbb_images( )
		/* Icludes here MXP styles configuration file
		/* include( 'www\templates\prosilver2\prosilver2.cfg' )
		**/
	
		unset($GLOBALS['MX_TEMPLATE_CONFIG']);		
		$mx_template_config = false;
		$module_root_path = $this->module_root_path;		
		$current_template_path = $this->current_template_path;
		$template_name = $this->template_name;
		
		if (@file_exists($this->mx_root_path . $this->module_root_path . $this->current_template_path . '/' . $template_name . '.cfg'))
		{
			$current_module_images = $this->mx_root_path . $this->module_root_path . $this->current_template_path . '/';
			@include($this->mx_root_path . $this->module_root_path . $this->current_template_path . '/' . $template_name . '.cfg');
		}		
		
		if (!$mx_template_config)
		{		
			if (@file_exists($this->mx_root_path . $this->module_root_path . $this->current_template_path . '/' . $moduleCfgFile . '.cfg'))
			{
				$current_module_images = $this->mx_root_path . $this->module_root_path . $this->current_template_path . '/';
				@include($this->mx_root_path . $this->module_root_path . $this->current_template_path . '/' . $moduleCfgFile . '.cfg');
			}		
		}
				
		/*
		* Since we have no current Template Config file, try the cloned template instead
		*/
		if (!$mx_template_config)
		{
			$current_template_path = $this->cloned_current_template_path;
			$template_name = $this->cloned_template_name;
			
			@include($this->mx_root_path . $this->module_root_path . $this->cloned_current_template_path . '/' . $template_name . '.cfg');
			if (!$mx_template_config)
			{
				@include($this->mx_root_path . $this->module_root_path . $this->cloned_current_template_path . '/' . $moduleCfgFile . '.cfg');
			}
		}
		
		/*
		* If use default template intead
		*/
		if (!$mx_template_config)
		{
			$current_template_path = $this->default_current_template_path;
			$template_name = $this->default_template_name;
			
			@include($this->mx_root_path . $this->module_root_path . $this->default_current_template_path . '/' . $template_name . '.cfg');
			if (!$mx_template_config)
			{
				@include($this->mx_root_path . $this->module_root_path . $this->default_current_template_path . '/' . $moduleCfgFile . '.cfg');
			}
		}
		
		/*
		* If old version 2 module search for  subSilver template intead
		*/
		if (!$mx_template_config)
		{
			$current_template_path = $this->default_current_template_path;
			$template_name = $this->default_template_name;
			
			@include($this->mx_root_path . $this->module_root_path . $this->default_current_template_path . '/' . $template_name2 . '.cfg');
			if (!$mx_template_config)
			{
				@include($this->mx_root_path . $this->module_root_path . $this->default_current_template_path . '/' . $moduleCfgFile . '.cfg');
			}
		}
		
		/*
		* We have no template to use - die
		*/
		if (!$mx_template_config)
		{
			$this->message_die(CRITICAL_ERROR, "Could not open " 
			. $this->mx_root_path . $this->module_root_path . $this->default_current_template_path .  '/' . $this->template_name . '.cfg' . " style config file " 
			. "<br /> current_template_path: " . $this->mx_root_path . $this->module_root_path . $current_template_path_d  . '/' . $template_name_d . '.cfg' 
			. "<br /> cloned_template_path: " . $this->mx_root_path . $this->module_root_path . $cloned_template_path_d 
			. "<br /> default_template_path: " . $this->mx_root_path . $this->module_root_path . $default_template_path_d 
			. "<br /> template_name: " . $template_name_d 
			. "<br /> template_config: "  . $template_config_d . "", '', __LINE__, __FILE__);
		}
		
		/**
		*
		*/		
		$img_lang = ( file_exists($this->mx_root_path . $current_template_path . '/images/lang_' . $this->decode_lang($this->config['default_lang'])) ) ? $this->decode_lang($this->config['default_lang']) : 'english';
		$img_dir = isset($this->img_lang_dir) ? $this->img_lang_dir : 'lang_' . $img_lang;
		
		while(list($key, $value) = @each($mx_images))
		{
			if (is_array($value))
			{
				foreach( $value as $key2 => $val2 )
				{
					$images[$key][$key2] = $val2;
				}
			}
			else
			{
				$images[$key] = str_replace('{LANG}', $img_dir, $value);
			}
		}
		
		//
		// What template is the module using?
		//
		$this->module_key = $module_key = !empty($this->module_root_path) ? $this->module_root_path : '_core';
		$this->current_module_template_name = isset($template_name) ? $template_name : $this->template_name;
		$this->current_module_images = isset($current_module_images) ? $current_module_images : $current_template_path;		
		$this->template_names[$module_key] = $this->template_name;
		
		//This will  keep loaded images
		$this->images = is_array($images) ? array_merge($core_images, $images) : $core_images;
		
		// We include common temlate config file here to not load it every time a module template config file is included
		//$this->theme = is_array($this->theme) ? array_merge($this->theme, $theme) : $theme;		
		$this->theme = &$this->theme;
		unset($core_images);
	}

	/**
	* Read style configuration file
	*
	* @param string $dir style directory
	* @return array|bool Style data, false on error
	*/
	protected function read_style_cfg($dir)
	{
		static $required = array('name', 'phpbb_version', 'copyright');
		$cfg = parse_cfg_file($this->styles_path . $dir . '/style.cfg');

		// Check if it is a valid file
		foreach ($required as $key)
		{
			if (!isset($cfg[$key]))
			{
				return false;
			}
		}

		// Check data
		if (!isset($cfg['parent']) || !is_string($cfg['parent']) || $cfg['parent'] == $cfg['name'])
		{
			$cfg['parent'] = '';
		}
		if (!isset($cfg['template_bitfield']))
		{
			$cfg['template_bitfield'] = $this->default_bitfield();
		}

		return $cfg;
	}
	
	/**
	* Specify/Get phpBB3 images array from phpBB2 images  variable
	*/
	function image_rows($images)
	{	
			/* Here we overwrite phpBB images from the template db or configuration file  */		
			$rows = array( 
			array(	'image_id' => 1, 
					'image_name' => $this->img_name_ext('site_logo.gif', false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext('site_logo.gif', false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 52, 
					'image_width' => 139, 
					'imageset_id' => 1 
				), 
			array(	'image_id' => 2, 
					'image_name' => 'forum_link', 
					'image_filename' => 'forum_link.gif', 
					'image_lang' => '', 
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
				), 
			array( 'image_id' => 3, 
					'image_name' => $this->img_name_ext($images['forum'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum'], false, false, $type = 'filename'), 
					'image_lang' => '', 
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 4, 
					'image_name' => $this->img_name_ext($images['forum_locked'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum_locked'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					),
			array( 'image_id' => 5, 
					'image_name' => $this->img_name_ext($images['forums'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forums'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 
					'image_id' => 6, 
					'image_name' => $this->img_name_ext($images['forum_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum_new'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 7, 
					'image_name' => 'forum_unread_locked', 
					'image_filename' => 'forum_unread_locked.gif', 
					'image_lang' => '', 'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 8, 
					'image_name' => $this->img_name_ext($images['forums_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forums_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 9, 
					'image_name' => 'topic_moved', 
					'image_filename' => 'topic_moved.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 10, 
					'image_name' => $this->img_name_ext($images['folder'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 11, 
					'image_name' => $this->img_name_ext($images['folder_sticky'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_sticky'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 12, 
					'image_name' => $this->img_name_ext($images['folder_hot'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_hot'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 13, 
					'image_name' => 'topic_read_hot_mine', 
					'image_filename' => 'topic_read_hot_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 14, 
					'image_name' => $this->img_name_ext($images['folder_locked'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_locked'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 15, 
					'image_name' => 'topic_read_locked_mine', 
					'image_filename' => 'topic_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 16, 
					'image_name' => $this->img_name_ext($images['folder_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 17, 
					'image_name' => $this->img_name_ext($images['folder_sticky_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_sticky_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 18, 
					'image_name' => $this->img_name_ext($images['folder_hot_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_hot_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 19, 
					'image_name' => 'topic_unread_hot_mine', 
					'image_filename' => 'topic_unread_hot_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					),
			array( 'image_id' => 20, 
					'image_name' => $this->img_name_ext($images['folder_locked_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_locked_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 21, 
					'image_name' => 'topic_unread_locked_mine', 
					'image_filename' => 'topic_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 22, 
					'image_name' => 'sticky_read', 
					'image_filename' => 'sticky_read.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 23, 
					'image_name' => 'sticky_read_mine', 
					'image_filename' => 'sticky_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 24, 
					'image_name' => 'sticky_read_locked', 
					'image_filename' => 'sticky_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 25, 
					'image_name' => 'sticky_read_locked_mine', 
					'image_filename' => 'sticky_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 26, 
					'image_name' => 'sticky_unread', 
					'image_filename' => 'sticky_unread.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 27, 
					'image_name' => 'sticky_unread_mine', 
					'image_filename' => 'sticky_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 28, 
					'image_name' => 'sticky_unread_locked', 
					'image_filename' => 'sticky_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 29, 
					'image_name' => 'sticky_unread_locked_mine', 
					'image_filename' => 'sticky_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 30, 
					'image_name' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 31, 
					'image_name' => 'announce_read_mine', 
					'image_filename' => 'announce_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 32, 
					'image_name' => 'announce_read_locked', 
					'image_filename' => 'announce_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 33, 
					'image_name' => 'announce_read_locked_mine', 
					'image_filename' => 'announce_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 34, 
					'image_name' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 35, 
					'image_name' => 'announce_unread_mine', 
					'image_filename' => 'announce_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 36, 
					'image_name' => 'announce_unread_locked', 
					'image_filename' => 'announce_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 37, 
					'image_name' => 'announce_unread_locked_mine', 
					'image_filename' => 'announce_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 38, 
					'image_name' => 'global_read', 
					'image_filename' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 39, 
					'image_name' => 'global_read_mine', 
					'image_filename' => 'announce_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 40, 
					'image_name' => 'global_read_locked', 
					'image_filename' => 'announce_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 41, 
					'image_name' => 'global_read_locked_mine', 
					'image_filename' => 'announce_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1
					), 
			array( 'image_id' => 42, 
					'image_name' => 'global_unread', 
					'image_filename' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 43, 
					'image_name' => 'global_unread_mine', 
					'image_filename' => 'announce_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 44, 
					'image_name' => 'global_unread_locked', 
					'image_filename' => 'announce_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 45, 
					'image_name' => 'global_unread_locked_mine', 
					'image_filename' => 'announce_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 46, 
					'image_name' => 'pm_read', 
					'image_filename' => $this->img_name_ext($images['folder'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 47, 
					'image_name' => 'pm_unread', 
					'image_filename' => $this->img_name_ext($images['folder_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 48, 
					'image_name' => 'icon_back_top', 
					'image_filename' => 'icon_back_top.gif', 
					'image_lang' => '',  
					'image_height' => 11, 
					'image_width' => 11 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 49, 
					'image_name' => $this->img_name_ext($images['icon_aim'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_aim'], false, false, $type = 'filename'), 
					'image_lang' => '{LANG}',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 50, 
					'image_name' => $this->img_name_ext($images['icon_email'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_email'], false, false, $type = 'filename'), 
					'image_lang' => '{LANG}',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 51, 
					'image_name' => $this->img_name_ext($images['icon_icq'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_icq'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 52, 
					'image_name' => 'icon_contact_jabber', 
					'image_filename' => 'icon_contact_jabber.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 53, 
					'image_name' => $this->img_name_ext($images['icon_msnm'], false, false, $type = 'name'),  
					'image_filename' => $this->img_name_ext($images['icon_msnm'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 54, 
					'image_name' => $this->img_name_ext($images['icon_www'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_www'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 55, 
					'image_name' => $this->img_name_ext($images['icon_yim'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_yim'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 56, 
					'image_name' => $this->img_name_ext($images['icon_delpost'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_delpost'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 57, 
					'image_name' => 'icon_post_info', 
					'image_filename' => 'icon_post_info.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 58, 
					'image_name' => 'icon_post_report', 
					'image_filename' => 'icon_post_report.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 59, 
					'image_name' => $this->img_name_ext($images['icon_minipost'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_minipost'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 9, 
					'image_width' => 11 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 60, 
					 'image_name' => $this->img_name_ext($images['icon_minipost_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_minipost_new'], false, false, $type = 'filename'), 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 61, 
					 'image_name' => 'icon_topic_attach', 
					 'image_filename' => 'icon_topic_attach.gif', 
					 'image_lang' => '',  
					 'image_height' => 10, 
					 'image_width' => 7 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 62, 
					 'image_name' => 'icon_topic_latest', 
					 'image_filename' => 'icon_topic_latest.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 63, 
					 'image_name' => 'icon_topic_newest', 
					 'image_filename' => 'icon_topic_newest.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 64, 
					 'image_name' => 'icon_topic_reported', 
					 'image_filename' => 'icon_topic_reported.gif', 
					 'image_lang' => '',  
					 'image_height' => 14, 
					 'image_width' => 16 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 65, 
					 'image_name' => 'icon_topic_unapproved', 
					 'image_filename' => 'icon_topic_unapproved.gif', 
					 'image_lang' => '',  
					 'image_height' => 14, 
					 'image_width' => 16 , 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 66, 
					 'image_name' => 'icon_user_warn', 
					 'image_filename' => 'icon_user_warn.gif', 
					 'image_lang' => '',  
					 'image_height' => 20, 
					 'image_width' => 20, 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 67, 
					 'image_name' => 'subforum_read', 
					 'image_filename' => 'subforum_read.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 68, 
					 'image_name' => 'subforum_unread', 
					 'image_filename' => 'subforum_unread.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 69, 
					 'image_name' => $this->img_name_ext($images['icon_pm'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_pm'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}',   
					 'image_height' => 20, 
					 'image_width' => 28 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 70, 
					 'image_name' => $this->img_name_ext($images['icon_edit'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_edit'], false, false, $type = 'filename'),  
					 'image_lang' => '{LANG}', 
					 'image_height' => 20, 
					 'image_width' => 42 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 71, 
					 'image_name' => $this->img_name_ext($images['icon_quote'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_quote'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 20, 
					 'image_width' => 54 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 72, 
					 'image_name' => 'icon_user_online', 
					 'image_filename' => 'icon_user_online.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 58, 
					 'image_width' => 58 , 
					 'imageset_id' => 1 
					 ),
			array( 'image_id' => 73, 
					 'image_name' => 'button_pm_forward', 
					 'image_filename' => 'button_pm_forward.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 74, 
					 'image_name' => 'button_pm_new', 
					 'image_filename' => 'button_pm_new.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 84 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 75, 
					 'image_name' => 'button_pm_reply', 
					 'image_filename' => 'button_pm_reply.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 76, 
					 'image_name' => $this->img_name_ext($images['post_locked'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['post_locked'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 88 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 77, 
					 'image_name' => $this->img_name_ext($images['post_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['post_new'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 78, 
					 'image_name' => $this->img_name_ext($images['reply_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['reply_new'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1
				)	
			);
		return $rows;
	}	
	
	/**
	* Specify/Get image name , extension
	*/
	function img_name_ext($img, $prefix = '', $new_prefix = '', $type = 'filename')
	{	
		if (strpos($img, '.') !== false)
		{
			// Nested img
			$image_filename = $img;
			$img_ext = substr(strrchr($image_filename, '.'), 1);
			$img = basename($image_filename, '.' . $img_ext);			
			
			unset($img_name, $image_filename);
		}
		else
		{
			$img_ext = 'gif';			
		}		
		
		switch ($type)
		{						
			case 'filename':
				return $img . '.' . $img_ext;
			break;
			
			case 'class':
				return $prefix . '_' . $img;
			break;
			
			case 'name':		
				return $img;
			break;
			
			case 'ext':
				return $img_ext;
			break;
		}		
	}	
	
	/**
	* Specify/Get image
	//
	// phpBB2 Graphics - redefined for mxBB
	// - Uncomment and redefine phpBB graphics
	//
	// If you need to redefine some phpBB graphics, look within the phpBB/templates folder for the template_name.cfg file and
	// redefine those $image['xxx'] you want. Note: Many phpBB images are reused all over mxBB (eg see below), thus if you redefine
	// common phpBB images, this will have immedaite effect for all mxBB pages.
	//
	*/
	function img($img, $alt = '', $width = false, $suffix = '', $type = '')
	{
		static $imgs; //$this->module_root_path; //$this->root_path;
				
		$this->module_root_path = $this->ext_path = $this->ext_manager->get_extension_path('orynider/pafiledb', true);
				
		$title = '';
		
		if ($alt)
		{
			$alt = $this->user->lang($alt);
			$title = ' title="' . $alt . '"';
		}
		
		if (strpos($img, '.') !== false)
		{
			// Nested img
			$image_filename = $img;
			$img_ext = substr(strrchr($image_filename, '.'), 1);
			$img = basename($image_filename, '.' . $img_ext);
			$this->img_array[$img]['image_filename'] = array(
				''.$img => $img . '.' . $img_ext,
			);			
			unset($img_name, $image_filename);
		}
		
		if ($width !== false)
		{
			$this->img_array['image_width'] = array(
				''.$img => $width,
			);	
		}		
				
		// print_r($this->img_array['image_filename']);
		// array ( [img_forum_read] => forum_read.gif )
		// Load phpBB Template configuration data
		$current_template_path = $this->current_template_path;
		$template_name = $this->template_name;
		
		//		
		// - First try phpBB2 then phpBB3 template
		//		
		if ( file_exists($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg') )
		{
			@include($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg'); 
			@define('TEMPLATE_CONFIG', true);
			
			//$img_keys = array_keys($images);
			//$img_values = array_values($images);
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					//print_r('Your style configuration file has a typo! ');
					//print_r($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					//print_r($row);
					$row['image_name'] = 'spacer.gif';
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}	
		}		
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/stylesheet.css') )
		{		
			@define('TEMPLATE_CONFIG', true);
			$current_template_images = $current_template_path . "/theme/images";
		}
		
		//
		// Since we have no current Template Config file, try the cloned template instead
		//
		if ( file_exists($this->phpbb_root_path . $this->cloned_current_template_path . '/' . $this->cloned_template_name . '.cfg') && !defined('TEMPLATE_CONFIG') )
		{
			$current_template_path = $this->cloned_current_template_path;
			$template_name = $this->cloned_template_name;

			@include($this->phpbb_root_path . $this->cloned_current_template_path . '/' . $this->cloned_template_name . '.cfg');
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					print_r('Your style configuration file has a typo! ');
					print_r($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					print_r($row);
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}	
		}
		
		//
		// Last attempt, use default template intead
		//
		if ( file_exists($this->phpbb_root_path . $this->default_current_template_path . '/' . $this->default_template_name . '.cfg') && !defined('TEMPLATE_CONFIG') )
		{
			$current_template_path = $this->default_current_template_path;
			$template_name = $this->default_template_name;

			@include($this->phpbb_root_path . $this->default_current_template_path . '/' . $this->default_template_name . '.cfg');
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					print_r('Your style configuration file has a typo! ');
					print_r($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					print_r($row);
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}			
		}		
		
		//		
		// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
		// default language		
		if ( file_exists($this->phpbb_root_path . $current_template_path . '/images/lang_' . $this->default_language_name . '/') )
		{
			$this->img_lang = $this->default_language_name;
		}		
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/images/lang_' . $this->default_language_name . '/') )
		{		
			$this->img_lang = $this->default_language_name;
		}		
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/' . $this->default_language . '/') )
		{		
			$this->img_lang = $this->default_language;
		}
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/imageset/' . $this->default_language . '/') )
		{		
			$this->img_lang = $this->default_language;
		}		
		
		//		
		// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
		// user language		
		if ( file_exists($this->phpbb_root_path . $current_template_path . '/images/lang_' . $this->user_language_name . '/') )
		{
			$this->img_lang = $this->user_language_name;
		}		
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/images/lang_' . $this->user_language_name . '/') )
		{		
			$this->img_lang = $this->user_language_name;
		}		
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/' . $this->user_language . '/') )
		{		
			$this->img_lang = $this->user_language;
		}
		else if ( file_exists($this->phpbb_root_path . $current_template_path  . '/theme/imageset/' . $this->user_language . '/') )
		{		
			$this->img_lang = $this->user_language;
		}
		
		/**
		* group everything by the Core images IDs
		*/
		$img_rows = $this->image_rows($this->images);		
		
		/** /
		$count = count($img_rows);	
		for ($i = 0; $i < $count; ++$i)
		{
			if($img_row[$i]['image_name'] = $img)
			{			
				$this->img_data[$img]['image_id'] 		= $img_rows[$i]['image_id'];
				$this->img_data[$img]['image_name']		= $img_rows[$i]['image_name'];
				$this->img_data[$img]['image_filename']	= $img_rows[$i]['image_filename'];
				$this->img_data[$img]['image_lang'] 		= $img_rows[$i]['image_lang']; 
				$this->img_data[$img]['image_height'] 	= $img_rows[$i]['image_height']; 
				$this->img_data[$img]['image_width'] 	= $img_rows[$i]['image_width']; 
				$this->img_data[$img]['imageset_id'] 	= $img_rows[$i]['imageset_id']; 				
			}
			
		}			
		/**/
		
		foreach ($img_rows as $row)
		{
			$row['image_filename'] = rawurlencode($row['image_filename']);
				
			if(empty($row['image_name']))
			{
				//print_r('Your style configuration file has a typo! ');
				//print_r($this->phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
				$row['image_name'] = 'spacer.gif';
			}
			/** 
			* Now check for the correct existance of all of the images into
			* each image of a prosilver based style. 
			*/
			$this->img_data[$row['image_name']] = $row;			
		}		
			
		$current_template_name = !isset($this->current_module_template_name) ? $this->template_name : $this->current_module_template_name;				
		$current_template_path = !isset($this->current_module_images) ? $current_template_path : $this->module_root_path . $this->current_module_images;				
			
		if (isset($this->img_data[$img]['image_lang']) && !isset($this->current_module_images))
		{
			//		
			// - First try phpBB2 then phpBB3 template lang images
			//					
			if ( file_exists($this->phpbb_root_path . $current_template_path . $current_template_name . '/images/' . $this->decode_lang($this->img_data[$img]['image_lang']) . '/') )
			{
				$current_template_images = $current_template_path . $current_template_name . '/images/' . $this->decode_lang($this->img_data[$img]['image_lang']);
			}		
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name. '/theme/images/' . $this->img_data[$img]['image_lang'] . '/') )
			{		
				$current_template_images = $current_template_path . $current_template_name . '/theme/images/' . $this->img_data[$img]['image_lang'];
			}
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/images/' . $this->encode_lang($this->lang_name) . '/') )
			{		
				$current_template_images = $current_template_path  . $current_template_name . '/theme/images/' . $this->encode_lang($this->lang_name);
			}
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/imageset/' . $this->encode_lang($this->lang_name) . '/') )
			{		
				$current_template_images = $current_template_path  . $current_template_name . '/theme/imageset/' . $this->encode_lang($this->lang_name);
			}				
		}		
		elseif (isset($this->img_data[$img]['image_lang']) && isset($this->current_module_images))
		{			
			//		
			// - First try phpBB2 then phpBB3 template lang images
			//		
			if ( file_exists($this->phpbb_root_path . $this->module_root_path . $current_template_path . $current_template_name . '/images/' . $this->decode_lang($this->img_data[$img]['image_lang']) . '/') )
			{
				$current_template_images = $current_template_path . $current_template_name . '/images/' . $this->decode_lang($this->img_data[$img]['image_lang']);
			}		
			else if ( file_exists($this->phpbb_root_path . $this->module_root_path . $current_template_path  . $current_template_name . '/theme/images/' . $this->img_data[$img]['image_lang'] . '/') )
			{		
				$current_template_images = $current_template_path . $current_template_name . '/theme/images/' . $this->img_data[$img]['image_lang'];
			}
			else if ( file_exists($this->phpbb_root_path . $this->module_root_path . $current_template_path  . $current_template_name . '/theme/images/' . $this->encode_lang($this->lang_name) . '/') )
			{		
				$current_template_images = $current_template_path  . $current_template_name . '/theme/images/' . $this->encode_lang($this->lang_name);
			}
			else if ( file_exists($this->phpbb_root_path . $this->module_root_path . $current_template_path  . '/theme/imageset/' . $this->encode_lang($this->lang_name) . '/') )
			{		
				$current_template_images = $current_template_path  . $current_template_name . '/theme/imageset/' . $this->encode_lang($this->lang_name);
			}				
		}			
		else
		{					
			//		
			// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
			// default language		
			if ( file_exists($this->phpbb_root_path . $current_template_path . $current_template_name . '/images/lang_' . $this->default_language_name . '/') )
			{
				$this->img_lang = $this->default_language_name;
			}		
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/images/lang_' . $this->default_language_name . '/') )
			{		
				$this->img_lang = $this->default_language_name;
			}		
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/images/' . $this->default_language . '/') )
			{		
				$this->img_lang = $this->default_language;
			}
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/imageset/' . $this->default_language . '/') )
			{		
				$this->img_lang = $this->default_language;
			}		
			
			//		
			// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
			// user language		
			if ( file_exists($this->phpbb_root_path . $current_template_path . $current_template_name . '/images/lang_' . $this->user_language_name . '/') )
			{
				$this->img_lang = $this->user_language_name;
			}		
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/images/lang_' . $this->user_language_name . '/') )
			{		
				$this->img_lang = $this->user_language_name;
			}		
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/images/' . $this->user_language . '/') )
			{		
				$this->img_lang = $this->user_language;
			}
			else if ( file_exists($this->phpbb_root_path . $current_template_path  . $current_template_name . '/theme/imageset/' . $this->user_language . '/') )
			{		
				$this->img_lang = $this->user_language;
			}			
		}		
		
		$board_url = generate_board_url() . '/';
		
		if (isset($this->images[$img]))
		{
			$this->img_data[$img]['src'] = $this->images[$img];
		}		
		elseif (isset($this->img_data[$img]['image_filename']))
		{
			$this->img_data[$img]['src'] = PHPBB_URL . $current_template_images . '/' . $this->img_data[$img]['image_filename'];	
		}
		else
		{
			$lastrow = count($img_rows);
			$this->img_data[$img]['image_id'] 		= $lastrow + 1;
			$this->img_data[$img]['image_name']		= $img;
			$this->img_data[$img]['image_filename']	= $img . '.' . (isset($img_ext) ? $img_ext : 'gif');
			$this->img_data[$img]['image_lang'] 	= $this->img_lang; 
			$this->img_data[$img]['image_height'] 	= ($width === false) ? '' : $width; 
			$this->img_data[$img]['image_width'] 	= ($width === false) ? '' : $width; 
			$this->img_data[$img]['imageset_id'] 	= $img_rows[$lastrow]['imageset_id']; 		
			
			$this->img_data[$img]['src'] = PHPBB_URL . $current_template_images . '/' . $this->img_data[$img]['image_filename'];								
		}		
		
		$this->img_data[$img]['width'] = !empty($height) ? $height : (!empty($this->img_array[$img]['image_width']) ? (!empty($this->img_array[$img]['image_width']) ? $this->img_array[$img]['image_width'] : (!empty($this->img_array[$img]['image_width']) ? $this->img_array[$img]['image_width'] : 47)) : 47);
		$this->img_data[$img]['height'] = !empty($height) ? $height : (!empty($this->img_array[$img]['image_height']) ? (!empty($this->img_array[$img]['image_width']) ? $this->img_array[$img]['image_height'] : (!empty($this->img_array[$img]['image_height']) ? $this->img_array[$img]['image_height'] : 47)) : 47);
			
		$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : $alt;
		
		$use_width = ($width === false) ? $img_data[$img]['width'] : $width;
		$use_height = ($width === false) ? $img_data[$img]['height'] : $width;
		
		$full_tag = '<img src="' . $this->img_data[$img]['src'] . '"' . (($use_width) ? ' width="' . $use_width . '"' : '') . (($use_height) ? ' height="' . $use_height . '"' : '') . ' alt="' . $alt . '" title="' . $alt . '" />';
		
		switch ($type)
		{
			case 'src':
				return $this->img_data[$img]['src'];
			break;

			case 'width':
				return $use_width;
			break;

			case 'height':
				return $this->img_data[$img]['height'];
			break;
							
			case 'filename':
				return $img . '.' . $img_ext;
			break;
			
			case 'class':			
			case 'name':		
				return $img;
			break;
			
			case 'alt':
				return $alt;
			break;
			
			case 'ext':
				return $img_ext;
			break;
			
			case 'full_tag':
				
				return $full_tag;
			break;
			
			case 'html':			
			default:		
				return '<span class="imageset ' . $img . '"' . $title . '>' . $alt . '</span>';						
			break;
		}
	}
		
	/**
	 * Load available languages list
	 * author: Jan Kalah aka culprit_cz
	 * @return array available languages list: KEY = folder name
	 */
	function get_lang_list($ext_root_path = '')
	{
		if (count($this->language_list))
		{
			return $this->language_list;
		}
		/* c:\Wamp\www\Rhea\language\ */
		$dir = opendir($this->phpbb_root_path . 'language/');			
		while($f = readdir($dir))
		{
			if (($f == '.' || $f == '..') || !is_dir($this->phpbb_root_path . 'language/' . $f))
			{
				continue;
			}
			$this->language_list[$f] =  $this->ucstrreplace('lang_', '', $f);	
		}
		closedir($dir);
		if (!empty($ext_root_path))
		{	
			$dir = opendir($this->phpbb_root_path . 'ext/' . $ext_root_path . '/language/');			
			while($f = readdir($dir))
			{
				if (($f == '.' || $f == '..') || !is_dir($this->phpbb_root_path . 'ext/' . $ext_root_path . '/language/' . $f))
				{
					continue;
				}
				$this->ext_language_list[$f] =  $this->ucstrreplace('lang_', '', $f);	
			}
			closedir($dir);
			return $this->language_list = array_merge($this->ext_language_list, $this->language_list);
		}			
		return $this->language_list;
	}
	
	/**
	 * encode_lang
	 *
	 * This function is used with phpBB2 backend to specify xml:lang  in overall headers (only two chars are allowed)
	 * Do not change!
	 *
	 * $default_lang = $user->encode_lang($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function encode_lang($lang)
	{
			switch($lang)
			{
				case 'afar':
					$lang_name = 'aa';
				break;
				case 'abkhazian':
					$lang_name = 'ab';
				break;
				case 'avestan':
					$lang_name = 'ae';
				break;
				case 'afrikaans':
					$lang_name = 'af';
				break;
				case 'akan':
					$lang_name = 'ak';
				break;
				case 'amharic':
					$lang_name = 'am';
				break;
				case 'aragonese':
					$lang_name = 'an';
				break;
				case 'arabic':
					$lang_name = 'ar';
				break;
				case 'assamese':
					$lang_name = 'as';
				break;
				case 'avaric':
					$lang_name = 'av';
				break;
				case 'aymara':
					$lang_name = 'ay';
				break;
				case 'azerbaijani':
					$lang_name = 'az';
				break;
				case 'bashkir':
					$lang_name = 'ba';
				break;
				case 'belarusian':
					$lang_name = 'be';
				break;
				case 'bulgarian':
					$lang_name = 'bg';
				break;
				case 'bihari':
					$lang_name = 'bh';
				break;
				case 'bislama':
					$lang_name = 'bi';
				break;
				case 'bambara':
					$lang_name = 'bm';
				break;
				case 'bengali':
					$lang_name = 'bn';
				break;
				case 'tibetan':
					$lang_name = 'bo';
				break;
				case 'breton':
					$lang_name = 'br';
				break;
				case 'bosnian':
					$lang_name = 'bs';
				break;
				case 'catalan':
					$lang_name = 'ca';
				break;
				case 'chechen':
					$lang_name = 'ce';
				break;
				case 'chamorro':
					$lang_name = 'ch';
				break;
				case 'corsican':
					$lang_name = 'co';
				break;
				case 'cree':
					$lang_name = 'cr';
				break;
				case 'czech':
					$lang_name = 'cs';
				break;
				case 'slavonic':
					$lang_name = 'cu';
				break;
				case 'chuvash':
					$lang_name = 'cv';
				break;
				case 'welsh_cymraeg':
					$lang_name = 'cy';
				break;
				case 'danish':
					$lang_name = 'da';
				break;
				case 'german':
					$lang_name = 'de';
				break;
				case 'divehi':
					$lang_name = 'dv';
				break;
				case 'dzongkha':
					$lang_name = 'dz';
				break;
				case 'ewe':
					$lang_name = 'ee';
				break;
				case 'greek':
					$lang_name = 'el';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'english':
					$lang_name = '{LANG}';
				break;
				case 'english_us':
					$lang_name = 'en_us';
				break;
				case 'esperanto':
					$lang_name = 'eo';
				break;
				case 'spanish':
					$lang_name = 'es';
				break;
				case 'estonian':
					$lang_name = 'et';
				break;
				case 'basque':
					$lang_name = 'eu';
				break;
				case 'persian':
					$lang_name = 'fa';
				break;
				case 'fulah':
					$lang_name = 'ff';
				break;
				case 'finnish':
					$lang_name = 'fi';
				break;
				case 'fijian':
					$lang_name = 'fj';
				break;
				case 'faroese':
					$lang_name = 'fo';
				break;
				case 'french':
					$lang_name = 'fr';
				break;
				case 'frisian':
					$lang_name = 'fy';
				break;
				case 'irish':
					$lang_name = 'ga';
				break;
				case 'scottish':
					$lang_name = 'gd';
				break;
				case 'galician':
					$lang_name = 'gl';
				break;
				case 'guaraní':
					$lang_name = 'gn';
				break;
				case 'gujarati':
					$lang_name = 'gu';
				break;
				case 'manx':
					$lang_name = 'gv';
				break;
				case 'hausa':
					$lang_name = 'ha';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'hindi':
					$lang_name = 'hi';
				break;
				case 'hiri_motu':
					$lang_name = 'ho';
				break;
				case 'croatian':
					$lang_name = 'hr';
				break;
				case 'haitian':
					$lang_name = 'ht';
				break;
				case 'hungarian':
					$lang_name = 'hu';
				break;
				case 'armenian':
					$lang_name = 'hy';
				break;
				case 'herero':
					$lang_name = 'hz';
				break;
				case 'interlingua':
					$lang_name = 'ia';
				break;
				case 'indonesian':
					$lang_name = 'id';
				break;
				case 'interlingue':
					$lang_name = 'ie';
				break;
				case 'igbo':
					$lang_name = 'ig';
				break;
				case 'sichuan_yi':
					$lang_name = 'ii';
				break;
				case 'inupiaq':
					$lang_name = 'ik';
				break;
				case 'ido':
					$lang_name = 'io';
				break;
				case 'icelandic':
					$lang_name = 'is';
				break;
				case 'italian':
					$lang_name = 'it';
				break;
				case 'inuktitut':
					$lang_name = 'iu';
				break;
				case 'japanese':
					$lang_name = 'ja';
				break;
				case 'javanese':
					$lang_name = 'jv';
				break;
				case 'georgian':
					$lang_name = 'ka';
				break;
				case 'kongo':
					$lang_name = 'kg';
				break;
				case 'kikuyu':
					$lang_name = 'ki';
				break;
				case 'kwanyama':
					$lang_name = 'kj';
				break;
				case 'kazakh':
					$lang_name = 'kk';
				break;
				case 'kalaallisut':
					$lang_name = 'kl';
				break;
				case 'khmer':
					$lang_name = 'km';
				break;
				case 'kannada':
					$lang_name = 'kn';
				break;
				case 'korean':
					$lang_name = 'ko';
				break;
				case 'kanuri':
					$lang_name = 'kr';
				break;
				case 'kashmiri':
					$lang_name = 'ks';
				break;
				case 'kurdish':
					$lang_name = 'ku';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'cornish_kernewek':
					$lang_name = 'kw';
				break;
				case 'kirghiz':
					$lang_name = 'ky';
				break;
				case 'latin':
					$lang_name = 'la';
				break;
				case 'luxembourgish':
					$lang_name = 'lb';
				break;
				case 'ganda':
					$lang_name = 'lg';
				break;
				case 'limburgish':
					$lang_name = 'li';
				break;
				case 'lingala':
					$lang_name = 'ln';
				break;
				case 'lao':
					$lang_name = 'lo';
				break;
				case 'lithuanian':
					$lang_name = 'lt';
				break;
				case 'luba-katanga':
					$lang_name = 'lu';
				break;
				case 'latvian':
					$lang_name = 'lv';
				break;
				case 'malagasy':
					$lang_name = 'mg';
				break;
				case 'marshallese':
					$lang_name = 'mh';
				break;
				case 'maori':
					$lang_name = 'mi';
				break;
				case 'macedonian':
					$lang_name = 'mk';
				break;
				case 'malayalam':
					$lang_name = 'ml';
				break;
				case 'mongolian':
					$lang_name = 'mn';
				break;
				case 'moldavian':
					$lang_name = 'mo';
				break;
				case 'marathi':
					$lang_name = 'mr';
				break;
				case 'malay':
					$lang_name = 'ms';
				break;
				case 'maltese':
					$lang_name = 'mt';
				break;
				case 'burmese':
					$lang_name = 'my';
				break;
				case 'nauruan':
					$lang_name = 'na';
				break;
				case 'norwegian':
					$lang_name = 'nb';
				break;
				case 'ndebele':
					$lang_name = 'nd';
				break;
				case 'nepali':
					$lang_name = 'ne';
				break;
				case 'ndonga':
					$lang_name = 'ng';
				break;
				case 'dutch':
					$lang_name = 'nl';
				break;
				case 'norwegian_nynorsk':
					$lang_name = 'nn';
				break;
				case 'norwegian':
					$lang_name = 'no';
				break;
				case 'southern_ndebele':
					$lang_name = 'nr';
				break;
				case 'navajo':
					$lang_name = 'nv';
				break;
				case 'chichewa':
					$lang_name = 'ny';
				break;
				case 'occitan':
					$lang_name = 'oc';
				break;
				case 'ojibwa':
					$lang_name = 'oj';
				break;
				case 'oromo':
					$lang_name = 'om';
				break;
				case 'oriya':
					$lang_name = 'or';
				break;
				case 'ossetian':
					$lang_name = 'os';
				break;
				case 'panjabi':
					$lang_name = 'pa';
				break;
				case 'pali':
					$lang_name = 'pi';
				break;
				case 'polish':
					$lang_name = 'pl';
				break;
				case 'pashto':
					$lang_name = 'ps';
				break;
				case 'portuguese':
					$lang_name = 'pt';
				break;
				case 'portuguese_brasil':
					$lang_name = 'pt_br';
				break;
				case 'quechua':
					$lang_name = 'qu';
				break;
				case 'romansh':
					$lang_name = 'rm';
				break;
				case 'kirundi':
					$lang_name = 'rn';
				break;
				case 'romanian':
					$lang_name = 'ro';
				break;
				case 'russian':
					$lang_name = 'ru';
				break;
				case 'kinyarwanda':
					$lang_name = 'rw';
				break;
				case 'sanskrit':
					$lang_name = 'sa';
				break;
				case 'sardinian':
					$lang_name = 'sc';
				break;
				case 'sindhi':
					$lang_name = 'sd';
				break;
				case 'northern_sami':
					$lang_name = 'se';
				break;
				case 'sango':
					$lang_name = 'sg';
				break;
				case 'serbo-croatian':
					$lang_name = 'sh';
				break;
				case 'sinhala':
					$lang_name = 'si';
				break;
				case 'slovak':
					$lang_name = 'sk';
				break;
				case 'slovenian':
					$lang_name = 'sl';
				break;
				case 'samoan':
					$lang_name = 'sm';
				break;
				case 'shona':
					$lang_name = 'sn';
				break;
				case 'somali':
					$lang_name = 'so';
				break;
				case 'albanian':
					$lang_name = 'sq';
				break;
				case 'serbian':
					$lang_name = 'sr';
				break;
				case 'swati':
					$lang_name = 'ss';
				break;
				case 'sotho':
					$lang_name = 'st';
				break;
				case 'sundanese':
					$lang_name = 'su';
				break;
				case 'swedish':
					$lang_name = 'sv';
				break;
				case 'swahili':
					$lang_name = 'sw';
				break;
				case 'tamil':
					$lang_name = 'ta';
				break;
				case 'telugu':
					$lang_name = 'te';
				break;
				case 'tajik':
					$lang_name = 'tg';
				break;
				case 'thai':
					$lang_name = 'th';
				break;
				case 'tigrinya':
					$lang_name = 'ti';
				break;
				case 'turkmen':
					$lang_name = 'tk';
				break;
				case 'tagalog':
					$lang_name = 'tl';
				break;
				case 'tswana':
					$lang_name = 'tn';
				break;
				case 'tonga':
					$lang_name = 'to';
				break;
				case 'turkish':
					$lang_name = 'tr';
				break;
				case 'tsonga':
					$lang_name = 'ts';
				break;
				case 'tatar':
					$lang_name = 'tt';
				break;
				case 'twi':
					$lang_name = 'tw';
				break;
				case 'tahitian':
					$lang_name = 'ty';
				break;
				case 'uighur':
					$lang_name = 'ug';
				break;
				case 'ukrainian':
					$lang_name = 'uk';
				break;
				case 'urdu':
					$lang_name = 'ur';
				break;
				case 'uzbek':
					$lang_name = 'uz';
				break;
				case 'venda':
					$lang_name = 've';
				break;
				case 'vietnamese':
					$lang_name = 'vi';
				break;
				case 'volapuk':
					$lang_name = 'vo';
				break;
				case 'walloon':
					$lang_name = 'wa';
				break;
				case 'wolof':
					$lang_name = 'wo';
				break;
				case 'xhosa':
					$lang_name = 'xh';
				break;
				case 'yiddish':
					$lang_name = 'yi';
				break;
				case 'yoruba':
					$lang_name = 'yo';
				break;
				case 'zhuang':
					$lang_name = 'za';
				break;
				case 'chinese':
					$lang_name = 'zh';
				break;
				case 'chinese_simplified':
					$lang_name = 'zh_cmn_hans';
				break;
				case 'chinese_traditional':
					$lang_name = 'zh_cmn_hant';
				break;
				case 'zulu':
					$lang_name = 'zu';
				break;
				default:
					$lang_name = (strlen($lang) > 2) ? substr($lang, 0, 2) : $lang;
					break;
			}
		return $lang_name;
	}
 
	/**
	 * decode_lang
	 *
	 * $default_lang = $user->decode_lang($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function decode_lang($lang)
	{
			switch($lang)
			{
				case 'aa':
					$lang_name = 'afar';
				break;
				case 'ab':
					$lang_name = 'abkhazian';
				break;
				case 'ae':
					$lang_name = 'avestan';
				break;
				case 'af':
					$lang_name = 'afrikaans';
				break;
				case 'ak':
					$lang_name = 'akan';
				break;
				case 'am':
					$lang_name = 'amharic';
				break;
				case 'an':
					$lang_name = 'aragonese';
				break;
				case 'ar':
					$lang_name = 'arabic';
				break;
				case 'as':
					$lang_name = 'assamese';
				break;
				case 'av':
					$lang_name = 'avaric';
				break;
				case 'ay':
					$lang_name = 'aymara';
				break;
				case 'az':
					$lang_name = 'azerbaijani';
				break;
				case 'ba':
					$lang_name = 'bashkir';
				break;
				case 'be':
					$lang_name = 'belarusian';
				break;
				case 'bg':
					$lang_name = 'bulgarian';
				break;
				case 'bh':
					$lang_name = 'bihari';
				break;
				case 'bi':
					$lang_name = 'bislama';
				break;
				case 'bm':
					$lang_name = 'bambara';
				break;
				case 'bn':
					$lang_name = 'bengali';
				break;
				case 'bo':
					$lang_name = 'tibetan';
				break;
				case 'br':
					$lang_name = 'breton';
				break;
				case 'bs':
					$lang_name = 'bosnian';
				break;
				case 'ca':
					$lang_name = 'catalan';
				break;
				case 'ce':
					$lang_name = 'chechen';
				break;
				case 'ch':
					$lang_name = 'chamorro';
				break;
				case 'co':
					$lang_name = 'corsican';
				break;
				case 'cr':
					$lang_name = 'cree';
				break;
				case 'cs':
					$lang_name = 'czech';
				break;
				case 'cu':
					$lang_name = 'slavonic';
				break;
				case 'cv':
					$lang_name = 'chuvash';
				break;
				case 'cy':
					$lang_name = 'welsh_cymraeg';
				break;
				case 'da':
					$lang_name = 'danish';
				break;
				case 'de':
					$lang_name = 'german';
				break;
				case 'dv':
					$lang_name = 'divehi';
				break;
				case 'dz':
					$lang_name = 'dzongkha';
				break;
				case 'ee':
					$lang_name = 'ewe';
				break;
				case 'el':
					$lang_name = 'greek';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case 'en':
					$lang_name = 'english';
				break;
				case 'en_us':
					$lang_name = 'english';
				break;
				case 'eo':
					$lang_name = 'esperanto';
				break;
				case 'es':
					$lang_name = 'spanish';
				break;
				case 'et':
					$lang_name = 'estonian';
				break;
				case 'eu':
					$lang_name = 'basque';
				break;
				case 'fa':
					$lang_name = 'persian';
				break;
				case 'ff':
					$lang_name = 'fulah';
				break;
				case 'fi':
					$lang_name = 'finnish';
				break;
				case 'fj':
					$lang_name = 'fijian';
				break;
				case 'fo':
					$lang_name = 'faroese';
				break;
				case 'fr':
					$lang_name = 'french';
				break;
				case 'fy':
					$lang_name = 'frisian';
				break;
				case 'ga':
					$lang_name = 'irish';
				break;
				case 'gd':
					$lang_name = 'scottish';
				break;
				case 'gl':
					$lang_name = 'galician';
				break;
				case 'gn':
					$lang_name = 'guaraní';
				break;
				case 'gu':
					$lang_name = 'gujarati';
				break;
				case 'gv':
					$lang_name = 'manx';
				break;
				case 'ha':
					$lang_name = 'hausa';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case 'hi':
					$lang_name = 'hindi';
				break;
				case 'ho':
					$lang_name = 'hiri_motu';
				break;
				case 'hr':
					$lang_name = 'croatian';
				break;
				case 'ht':
					$lang_name = 'haitian';
				break;
				case 'hu':
					$lang_name = 'hungarian';
				break;
				case 'hy':
					$lang_name = 'armenian';
				break;
				case 'hz':
					$lang_name = 'herero';
				break;
				case 'ia':
					$lang_name = 'interlingua';
				break;
				case 'id':
					$lang_name = 'indonesian';
				break;
				case 'ie':
					$lang_name = 'interlingue';
				break;
				case 'ig':
					$lang_name = 'igbo';
				break;
				case 'ii':
					$lang_name = 'sichuan_yi';
				break;
				case 'ik':
					$lang_name = 'inupiaq';
				break;
				case 'io':
					$lang_name = 'ido';
				break;
				case 'is':
					$lang_name = 'icelandic';
				break;
				case 'it':
					$lang_name = 'italian';
				break;
				case 'iu':
					$lang_name = 'inuktitut';
				break;
				case 'ja':
					$lang_name = 'japanese';
				break;
				case 'jv':
					$lang_name = 'javanese';
				break;
				case 'ka':
					$lang_name = 'georgian';
				break;
				case 'kg':
					$lang_name = 'kongo';
				break;
				case 'ki':
					$lang_name = 'kikuyu';
				break;
				case 'kj':
					$lang_name = 'kwanyama';
				break;
				case 'kk':
					$lang_name = 'kazakh';
				break;
				case 'kl':
					$lang_name = 'kalaallisut';
				break;
				case 'km':
					$lang_name = 'khmer';
				break;
				case 'kn':
					$lang_name = 'kannada';
				break;
				case 'ko':
					$lang_name = 'korean';
				break;
				case 'kr':
					$lang_name = 'kanuri';
				break;
				case 'ks':
					$lang_name = 'kashmiri';
				break;
				case 'ku':
					$lang_name = 'kurdish';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'kw':
					$lang_name = 'cornish_kernewek';
				break;
				case 'ky':
					$lang_name = 'kirghiz';
				break;
				case 'la':
					$lang_name = 'latin';
				break;
				case 'lb':
					$lang_name = 'luxembourgish';
				break;
				case 'lg':
					$lang_name = 'ganda';
				break;
				case 'li':
					$lang_name = 'limburgish';
				break;
				case 'ln':
					$lang_name = 'lingala';
				break;
				case 'lo':
					$lang_name = 'lao';
				break;
				case 'lt':
					$lang_name = 'lithuanian';
				break;
				case 'lu':
					$lang_name = 'luba-katanga';
				break;
				case 'lv':
					$lang_name = 'latvian';
				break;
				case 'mg':
					$lang_name = 'malagasy';
				break;
				case 'mh':
					$lang_name = 'marshallese';
				break;
				case 'mi':
					$lang_name = 'maori';
				break;
				case 'mk':
					$lang_name = 'macedonian';
				break;
				case 'ml':
					$lang_name = 'malayalam';
				break;
				case 'mn':
					$lang_name = 'mongolian';
				break;
				case 'mo':
					$lang_name = 'moldavian';
				break;
				case 'mr':
					$lang_name = 'marathi';
				break;
				case 'ms':
					$lang_name = 'malay';
				break;
				case 'mt':
					$lang_name = 'maltese';
				break;
				case 'my':
					$lang_name = 'burmese';
				break;
				case 'na':
					$lang_name = 'nauruan';
				break;
				case 'nb':
					$lang_name = 'norwegian';
				break;
				case 'nd':
					$lang_name = 'ndebele';
				break;
				case 'ne':
					$lang_name = 'nepali';
				break;
				case 'ng':
					$lang_name = 'ndonga';
				break;
				case 'nl':
					$lang_name = 'dutch';
				break;
				case 'nn':
					$lang_name = 'norwegian_nynorsk';
				break;
				case 'no':
					$lang_name = 'norwegian';
				break;
				case 'nr':
					$lang_name = 'southern_ndebele';
				break;
				case 'nv':
					$lang_name = 'navajo';
				break;
				case 'ny':
					$lang_name = 'chichewa';
				break;
				case 'oc':
					$lang_name = 'occitan';
				break;
				case 'oj':
					$lang_name = 'ojibwa';
				break;
				case 'om':
					$lang_name = 'oromo';
				break;
				case 'or':
					$lang_name = 'oriya';
				break;
				case 'os':
					$lang_name = 'ossetian';
				break;
				case 'pa':
					$lang_name = 'panjabi';
				break;
				case 'pi':
					$lang_name = 'pali';
				break;
				case 'pl':
					$lang_name = 'polish';
				break;
				case 'ps':
					$lang_name = 'pashto';
				break;
				case 'pt':
					$lang_name = 'portuguese';
				break;
				case 'pt_br':
					$lang_name = 'portuguese_brasil';
				break;
				case 'qu':
					$lang_name = 'quechua';
				break;
				case 'rm':
					$lang_name = 'romansh';
				break;
				case 'rn':
					$lang_name = 'kirundi';
				break;
				case 'ro':
					$lang_name = 'romanian';
				break;
				case 'ru':
					$lang_name = 'russian';
				break;
				case 'rw':
					$lang_name = 'kinyarwanda';
				break;
				case 'sa':
					$lang_name = 'sanskrit';
				break;
				case 'sc':
					$lang_name = 'sardinian';
				break;
				case 'sd':
					$lang_name = 'sindhi';
				break;
				case 'se':
					$lang_name = 'northern_sami';
				break;
				case 'sg':
					$lang_name = 'sango';
				break;
				case 'sh':
					$lang_name = 'serbo-croatian';
				break;
				case 'si':
					$lang_name = 'sinhala';
				break;
				case 'sk':
					$lang_name = 'slovak';
				break;
				case 'sl':
					$lang_name = 'slovenian';
				break;
				case 'sm':
					$lang_name = 'samoan';
				break;
				case 'sn':
					$lang_name = 'shona';
				break;
				case 'so':
					$lang_name = 'somali';
				break;
				case 'sq':
					$lang_name = 'albanian';
				break;
				case 'sr':
					$lang_name = 'serbian';
				break;
				case 'ss':
					$lang_name = 'swati';
				break;
				case 'st':
					$lang_name = 'sotho';
				break;
				case 'su':
					$lang_name = 'sundanese';
				break;
				case 'sv':
					$lang_name = 'swedish';
				break;
				case 'sw':
					$lang_name = 'swahili';
				break;
				case 'ta':
					$lang_name = 'tamil';
				break;
				case 'te':
					$lang_name = 'telugu';
				break;
				case 'tg':
					$lang_name = 'tajik';
				break;
				case 'th':
					$lang_name = 'thai';
				break;
				case 'ti':
					$lang_name = 'tigrinya';
				break;
				case 'tk':
					$lang_name = 'turkmen';
				break;
				case 'tl':
					$lang_name = 'tagalog';
				break;
				case 'tn':
					$lang_name = 'tswana';
				break;
				case 'to':
					$lang_name = 'tonga';
				break;
				case 'tr':
					$lang_name = 'turkish';
				break;
				case 'ts':
					$lang_name = 'tsonga';
				break;
				case 'tt':
					$lang_name = 'tatar';
				break;
				case 'tw':
					$lang_name = 'twi';
				break;
				case 'ty':
					$lang_name = 'tahitian';
				break;
				case 'ug':
					$lang_name = 'uighur';
				break;
				case 'uk':
					$lang_name = 'ukrainian';
				break;
				case 'ur':
					$lang_name = 'urdu';
				break;
				case 'uz':
					$lang_name = 'uzbek';
				break;
				case 've':
					$lang_name = 'venda';
				break;
				case 'vi':
					$lang_name = 'vietnamese';
				break;
				case 'vo':
					$lang_name = 'volapuk';
				break;
				case 'wa':
					$lang_name = 'walloon';
				break;
				case 'wo':
					$lang_name = 'wolof';
				break;
				case 'xh':
					$lang_name = 'xhosa';
				break;
				case 'yi':
					$lang_name = 'yiddish';
				break;
				case 'yo':
					$lang_name = 'yoruba';
				break;
				case 'za':
					$lang_name = 'zhuang';
				break;
				case 'zh':
					$lang_name = 'chinese';
				break;
				case 'zh_cmn_hans':
					$lang_name = 'chinese_simplified';
				break;
				case 'zh_cmn_hant':
					$lang_name = 'chinese_traditional';
				break;
				case 'zu':
					$lang_name = 'zulu';
				break;
				default:
					$lang_name = $lang;
				break;
			}
		return $lang_name;
	}
	
	/**
	 * ucstrreplace
	 *
	 * $lang_local_name = $user->ucstrreplace($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function ucstrreplace($pattern = '%{$regex}%i', $matches = '', $string) 
	{
		/* return with no uppercase if patern not in string */
		if (strpos($string, $pattern) === false)
		{
			/* known languages */
			switch($string)
			{
				case 'aa':
					$lang_name = 'afar';
				break;
				case 'ab':
					$lang_name = 'abkhazian';
				break;
				case 'ae':
					$lang_name = 'avestan';
				break;
				case 'af':
					$lang_name = 'afrikaans';
				break;
				case 'ak':
					$lang_name = 'akan';
				break;
				case 'am':
					$lang_name = 'amharic';
				break;
				case 'an':
					$lang_name = 'aragonese';
				break;
				case 'ar':
					$lang_name = 'arabic';
				break;
				case 'as':
					$lang_name = 'assamese';
				break;
				case 'av':
					$lang_name = 'avaric';
				break;
				case 'ay':
					$lang_name = 'aymara';
				break;
				case 'az':
					$lang_name = 'azerbaijani';
				break;
				case 'ba':
					$lang_name = 'bashkir';
				break;
				case 'be':
					$lang_name = 'belarusian';
				break;
				case 'bg':
					$lang_name = 'bulgarian';
				break;
				case 'bh':
					$lang_name = 'bihari';
				break;
				case 'bi':
					$lang_name = 'bislama';
				break;
				case 'bm':
					$lang_name = 'bambara';
				break;
				case 'bn':
					$lang_name = 'bengali';
				break;
				case 'bo':
					$lang_name = 'tibetan';
				break;
				case 'br':
					$lang_name = 'breton';
				break;
				case 'bs':
					$lang_name = 'bosnian';
				break;
				case 'ca':
					$lang_name = 'catalan';
				break;
				case 'ce':
					$lang_name = 'chechen';
				break;
				case 'ch':
					$lang_name = 'chamorro';
				break;
				case 'co':
					$lang_name = 'corsican';
				break;
				case 'cr':
					$lang_name = 'cree';
				break;
				case 'cs':
					$lang_name = 'czech';
				break;
				case 'cu':
					$lang_name = 'slavonic';
				break;
				case 'cv':
					$lang_name = 'chuvash';
				break;
				case 'cy':
					$lang_name = 'welsh_cymraeg';
				break;
				case 'da':
					$lang_name = 'danish';
				break;
				case 'de':
					$lang_name = 'german';
				break;
				case 'dv':
					$lang_name = 'divehi';
				break;
				case 'dz':
					$lang_name = 'dzongkha';
				break;
				case 'ee':
					$lang_name = 'ewe';
				break;
				case 'el':
					$lang_name = 'greek';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case '{LANG}':
					$lang_name = 'english';
				break;
				case 'en_us':
					$lang_name = 'english';
				break;
				case 'eo':
					$lang_name = 'esperanto';
				break;
				case 'es':
					$lang_name = 'spanish';
				break;
				case 'et':
					$lang_name = 'estonian';
				break;
				case 'eu':
					$lang_name = 'basque';
				break;
				case 'fa':
					$lang_name = 'persian';
				break;
				case 'ff':
					$lang_name = 'fulah';
				break;
				case 'fi':
					$lang_name = 'finnish';
				break;
				case 'fj':
					$lang_name = 'fijian';
				break;
				case 'fo':
					$lang_name = 'faroese';
				break;
				case 'fr':
					$lang_name = 'french';
				break;
				case 'fy':
					$lang_name = 'frisian';
				break;
				case 'ga':
					$lang_name = 'irish';
				break;
				case 'gd':
					$lang_name = 'scottish';
				break;
				case 'gl':
					$lang_name = 'galician';
				break;
				case 'gn':
					$lang_name = 'guaraní';
				break;
				case 'gu':
					$lang_name = 'gujarati';
				break;
				case 'gv':
					$lang_name = 'manx';
				break;
				case 'ha':
					$lang_name = 'hausa';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case 'hi':
					$lang_name = 'hindi';
				break;
				case 'ho':
					$lang_name = 'hiri_motu';
				break;
				case 'hr':
					$lang_name = 'croatian';
				break;
				case 'ht':
					$lang_name = 'haitian';
				break;
				case 'hu':
					$lang_name = 'hungarian';
				break;
				case 'hy':
					$lang_name = 'armenian';
				break;
				case 'hz':
					$lang_name = 'herero';
				break;
				case 'ia':
					$lang_name = 'interlingua';
				break;
				case 'id':
					$lang_name = 'indonesian';
				break;
				case 'ie':
					$lang_name = 'interlingue';
				break;
				case 'ig':
					$lang_name = 'igbo';
				break;
				case 'ii':
					$lang_name = 'sichuan_yi';
				break;
				case 'ik':
					$lang_name = 'inupiaq';
				break;
				case 'io':
					$lang_name = 'ido';
				break;
				case 'is':
					$lang_name = 'icelandic';
				break;
				case 'it':
					$lang_name = 'italian';
				break;
				case 'iu':
					$lang_name = 'inuktitut';
				break;
				case 'ja':
					$lang_name = 'japanese';
				break;
				case 'jv':
					$lang_name = 'javanese';
				break;
				case 'ka':
					$lang_name = 'georgian';
				break;
				case 'kg':
					$lang_name = 'kongo';
				break;
				case 'ki':
					$lang_name = 'kikuyu';
				break;
				case 'kj':
					$lang_name = 'kwanyama';
				break;
				case 'kk':
					$lang_name = 'kazakh';
				break;
				case 'kl':
					$lang_name = 'kalaallisut';
				break;
				case 'km':
					$lang_name = 'khmer';
				break;
				case 'kn':
					$lang_name = 'kannada';
				break;
				case 'ko':
					$lang_name = 'korean';
				break;
				case 'kr':
					$lang_name = 'kanuri';
				break;
				case 'ks':
					$lang_name = 'kashmiri';
				break;
				case 'ku':
					$lang_name = 'kurdish';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'kw':
					$lang_name = 'cornish_kernewek';
				break;
				case 'ky':
					$lang_name = 'kirghiz';
				break;
				case 'la':
					$lang_name = 'latin';
				break;
				case 'lb':
					$lang_name = 'luxembourgish';
				break;
				case 'lg':
					$lang_name = 'ganda';
				break;
				case 'li':
					$lang_name = 'limburgish';
				break;
				case 'ln':
					$lang_name = 'lingala';
				break;
				case 'lo':
					$lang_name = 'lao';
				break;
				case 'lt':
					$lang_name = 'lithuanian';
				break;
				case 'lu':
					$lang_name = 'luba-katanga';
				break;
				case 'lv':
					$lang_name = 'latvian';
				break;
				case 'mg':
					$lang_name = 'malagasy';
				break;
				case 'mh':
					$lang_name = 'marshallese';
				break;
				case 'mi':
					$lang_name = 'maori';
				break;
				case 'mk':
					$lang_name = 'macedonian';
				break;
				case 'ml':
					$lang_name = 'malayalam';
				break;
				case 'mn':
					$lang_name = 'mongolian';
				break;
				case 'mo':
					$lang_name = 'moldavian';
				break;
				case 'mr':
					$lang_name = 'marathi';
				break;
				case 'ms':
					$lang_name = 'malay';
				break;
				case 'mt':
					$lang_name = 'maltese';
				break;
				case 'my':
					$lang_name = 'burmese';
				break;
				case 'na':
					$lang_name = 'nauruan';
				break;
				case 'nb':
					$lang_name = 'norwegian';
				break;
				case 'nd':
					$lang_name = 'ndebele';
				break;
				case 'ne':
					$lang_name = 'nepali';
				break;
				case 'ng':
					$lang_name = 'ndonga';
				break;
				case 'nl':
					$lang_name = 'dutch';
				break;
				case 'nn':
					$lang_name = 'norwegian_nynorsk';
				break;
				case 'no':
					$lang_name = 'norwegian';
				break;
				case 'nr':
					$lang_name = 'southern_ndebele';
				break;
				case 'nv':
					$lang_name = 'navajo';
				break;
				case 'ny':
					$lang_name = 'chichewa';
				break;
				case 'oc':
					$lang_name = 'occitan';
				break;
				case 'oj':
					$lang_name = 'ojibwa';
				break;
				case 'om':
					$lang_name = 'oromo';
				break;
				case 'or':
					$lang_name = 'oriya';
				break;
				case 'os':
					$lang_name = 'ossetian';
				break;
				case 'pa':
					$lang_name = 'panjabi';
				break;
				case 'pi':
					$lang_name = 'pali';
				break;
				case 'pl':
					$lang_name = 'polish';
				break;
				case 'ps':
					$lang_name = 'pashto';
				break;
				case 'pt':
					$lang_name = 'portuguese';
				break;
				case 'pt_br':
					$lang_name = 'portuguese_brasil';
				break;
				case 'qu':
					$lang_name = 'quechua';
				break;
				case 'rm':
					$lang_name = 'romansh';
				break;
				case 'rn':
					$lang_name = 'kirundi';
				break;
				case 'ro':
					$lang_name = 'romanian';
				break;
				case 'ru':
					$lang_name = 'russian';
				break;
				case 'rw':
					$lang_name = 'kinyarwanda';
				break;
				case 'sa':
					$lang_name = 'sanskrit';
				break;
				case 'sc':
					$lang_name = 'sardinian';
				break;
				case 'sd':
					$lang_name = 'sindhi';
				break;
				case 'se':
					$lang_name = 'northern_sami';
				break;
				case 'sg':
					$lang_name = 'sango';
				break;
				case 'sh':
					$lang_name = 'serbo-croatian';
				break;
				case 'si':
					$lang_name = 'sinhala';
				break;
				case 'sk':
					$lang_name = 'slovak';
				break;
				case 'sl':
					$lang_name = 'slovenian';
				break;
				case 'sm':
					$lang_name = 'samoan';
				break;
				case 'sn':
					$lang_name = 'shona';
				break;
				case 'so':
					$lang_name = 'somali';
				break;
				case 'sq':
					$lang_name = 'albanian';
				break;
				case 'sr':
					$lang_name = 'serbian';
				break;
				case 'ss':
					$lang_name = 'swati';
				break;
				case 'st':
					$lang_name = 'sotho';
				break;
				case 'su':
					$lang_name = 'sundanese';
				break;
				case 'sv':
					$lang_name = 'swedish';
				break;
				case 'sw':
					$lang_name = 'swahili';
				break;
				case 'ta':
					$lang_name = 'tamil';
				break;
				case 'te':
					$lang_name = 'telugu';
				break;
				case 'tg':
					$lang_name = 'tajik';
				break;
				case 'th':
					$lang_name = 'thai';
				break;
				case 'ti':
					$lang_name = 'tigrinya';
				break;
				case 'tk':
					$lang_name = 'turkmen';
				break;
				case 'tl':
					$lang_name = 'tagalog';
				break;
				case 'tn':
					$lang_name = 'tswana';
				break;
				case 'to':
					$lang_name = 'tonga';
				break;
				case 'tr':
					$lang_name = 'turkish';
				break;
				case 'ts':
					$lang_name = 'tsonga';
				break;
				case 'tt':
					$lang_name = 'tatar';
				break;
				case 'tw':
					$lang_name = 'twi';
				break;
				case 'ty':
					$lang_name = 'tahitian';
				break;
				case 'ug':
					$lang_name = 'uighur';
				break;
				case 'uk':
					$lang_name = 'ukrainian';
				break;
				case 'ur':
					$lang_name = 'urdu';
				break;
				case 'uz':
					$lang_name = 'uzbek';
				break;
				case 've':
					$lang_name = 'venda';
				break;
				case 'vi':
					$lang_name = 'vietnamese';
				break;
				case 'vo':
					$lang_name = 'volapuk';
				break;
				case 'wa':
					$lang_name = 'walloon';
				break;
				case 'wo':
					$lang_name = 'wolof';
				break;
				case 'xh':
					$lang_name = 'xhosa';
				break;
				case 'yi':
					$lang_name = 'yiddish';
				break;
				case 'yo':
					$lang_name = 'yoruba';
				break;
				case 'za':
					$lang_name = 'zhuang';
				break;
				case 'zh':
					$lang_name = 'chinese';
				break;
				case 'zh_cmn_hans':
					$lang_name = 'chinese_simplified';
				break;
				case 'zh_cmn_hant':
					$lang_name = 'chinese_traditional';
				break;
				case 'zu':
					$lang_name = 'zulu';
				break;
				default:
					$lang_name = (strlen($string) > 2) ? ucfirst(str_replace($pattern, '', $string)) : $string;
				break;
			}		
			return ucwords(str_replace(array(" ","-","_"), ' ', $lang_name));	
		}
		return ucwords(str_replace(array(" ","-","_"), ' ', str_replace($pattern, '', $string)));
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
}
?>