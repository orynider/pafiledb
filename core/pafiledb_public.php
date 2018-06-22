<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_public.php,v 1.67 2013/10/03 10:05:44 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

/**
 * Public pafiledb class
 *
 */
class pafiledb_public extends \orynider\pafiledb\core\pafiledb
{
	/** @var \orynider\pafiledb\core\functions */
	protected $functions;

	/** @var \phpbb\template\template */
	protected $template;	
	
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;	
	
	/** @var string */
	protected $php_ext;
	
	
	var $modules = array();
	var $module_name = '';
	/**
	* Constructor
	*
	* @param \orynider\pafiledb\core\functions						$functions
	* @param \phpbb\template\template		 					$template
	* @param \phpbb\extension\manager							$ext_manager	

	*
	*/
	public function __construct(
		\orynider\pafiledb\core\pafiledb_functions $functions,
		\phpbb\template\template $template,
		\phpbb\extension\manager $ext_manager,	
		$php_ext)
	{
		$this->functions 			= $functions;
		$this->template 			= $template;	
		$this->ext_manager	 		= $ext_manager;	
		$this->php_ext 				= $php_ext;
		
		$this->ext_name 			= 'orynider/pafiledb';
		$this->module_root_path		= $this->ext_path = $this->ext_manager->get_extension_path($this->ext_name, true);		
	}
	
	/**
	 * load module.
	 *
	 * @param unknown_type $module_name send module name to load it
	 */
	function module( $addon_name )
	{
		if ( !class_exists( 'pafiledb_' . $addon_name ) )
		{

			$this->addon_name = $addon_name;

			require_once( $this->module_root_path . 'controller/pafiledb_' . $addon_name . '.' . $this->php_ext );
			eval( '$this->modules[' . $addon_name . '] = new pafiledb_' . $addon_name . '();' );

			if ( method_exists( $this->modules[$addon_name], 'init' ) )
			{
				$this->modules[$addon_name]->init();
			}
		}
	}

	/**
	 * this will be replaced by the loaded module
	 *
	 * @param unknown_type $module_id
	 * @return unknown
	 */
	function main($module_id = false)
	{
		return false;
	}

	/**
	 * go ahead and output the page
	 *
	 * @param unknown_type $page_title send page title
	 * @param unknown_type $tpl_name template file name
	 */
	function display( $page_title, $tpl_name )
	{
		$this->functions->page_header( $page_title );
		$this->template->set_filenames( array( 'body' => $tpl_name ) );
		$this->functions->page_footer();
	}
}
?>