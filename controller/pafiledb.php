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

class pafiledb
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions		$functions
	* @param \phpbb\template\template		 			$template
	* @param \phpbb\user								$user
	* @param \phpbb\auth\auth							$auth
	* @param \phpbb\request\request		 				$request
	* @param \phpbb\controller\helper					$helper
	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb_functions $functions,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper)
	{
		$this->functions 		= $functions;
		$this->template 		= $template;
		$this->user 			= $user;
		$this->auth 			= $auth;
		$this->request 			= $request;
		$this->helper 			= $helper;
	}

	public function handle_pafiledb()
	{
		if (!$this->auth->acl_get('u_pa_files_use'))
		{
			throw new http_exception(401, 'FILES_NO_PERMISSION');
		}

		$cat_id = $this->request->variable('cat_id', 0);
				
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
		
		// Generate the sub categories list
		$this->functions->generate_cat_list($cat_id);

		// Build navigation link
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang('FILES_DOWNLOADS'),
			'U_VIEW_FORUM'	=> $this->helper->route('orynider_pafiledb_controller'),
		));

		$this->functions->assign_authors();
		$this->template->assign_var('PAFILEDB_FOOTER_VIEW', true);

		// Send all data to the template file
		return $this->helper->render('index_body.html', $this->user->lang('FILES_TITLE') . ' &bull; ' . $this->user->lang('FILES_INDEX'));
	}
}
