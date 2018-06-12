<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\files\factory */
	protected $files_factory;

	/**
	* Constructor
	*
	* @param \phpbb\user					$user
	* @param \phpbb\template\template			$template
	* @param \phpbb\controller\helper			$helper
	* @param \phpbb\config\config				$config
	* @param \phpbb\auth\auth				$auth
	* @param string						$php_ext
	* @param \phpbb\files\factory				$files_factory
	*
	*/
	public function __construct(
		\phpbb\user $user,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
		\phpbb\auth\auth $auth,
		$php_ext,
		\phpbb\files\factory $files_factory = null)
	{
		$this->user					= $user;
		$this->template				= $template;
		$this->helper 				= $helper;
		$this->config				= $config;
		$this->auth 				= $auth;
		$this->php_ext				= $php_ext;
		$this->files_factory 		= $files_factory;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'pafiledb_page_header_link',
			'core.viewonline_overwrite_location'	=> 'add_page_viewonline',			
			'core.permissions'						=> 'permissions',
		);
	}


	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'orynider/pafiledb',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function pafiledb_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_PA_FILES'				=> $this->helper->route('orynider_pafiledb_controller'),
			'U_PA_FILES_UPLOAD'			=> $this->helper->route('orynider_pafiledb_controller_upload'),
			'PA_FILES_USE_UPLOAD'		=> $this->auth->acl_get('u_pa_files_upload'),			
			'S_FILES_EXIST'				=> true,
			'PAFILEDB_VERSION'			=> $this->config['pa_module_version'],
			'PHPBB_IS_32'				=> ($this->files_factory !== null) ? true : false,
		));
	}

	public function add_page_viewonline($event)
	{
		if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/pafiledb') === 0 || strrpos($event['row']['session_page'], 'app.' . $this->php_ext . 'category') === 0)
		{
			$event['location'] = $this->user->lang('FILES_DOWNLOADS');
			$event['location_url'] = $this->helper->route('orynider_pafiledb_controller', array('name' => 'index'));
		}

		if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/upload') === 0)
		{
			$event['location'] = $this->user->lang('FILES_UPLOAD_SECTION');
			$event['location_url'] = $this->helper->route('orynider_pafiledb_controller_upload', array('name' => 'index'));
		}
	}
	
	public function permissions($event)
	{
		$event['permissions'] = array_merge($event['permissions'], array(
			'u_pa_files_use'	=> array(
				'lang'		=> 'ACL_U_PA_FILES_USE',
				'cat'		=> 'Download Manager'
			),
			'u_pa_files_download'	=> array(
				'lang'		=> 'ACL_U_PA_FILES_DOWNLOAD',
				'cat'		=> 'Download Manager'
			),
			'u_pa_files_upload'	=> array(
				'lang'		=> 'ACL_U_PA_FILES_UPLOAD',
				'cat'		=> 'Download Manager'
			),
			'a_pa_files'		=> array(
				'lang'		=> 'ACL_U_PA_FILES_USE',
				'cat'		=> 'Download Manager'
			),
		));
		$event['categories'] = array_merge($event['categories'], array(
			'Download Manager'	=> 'ACL_U_PA',
		));
	}
}
