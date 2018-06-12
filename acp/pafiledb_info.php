<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\acp;

class pafiledb_info
{
	function module()
	{
		return array(
			'filename'		=> 'acp_pa_files',
			'title'			=> 'ACP_PA_FILES',
			'modes'			=> array(
				'config'		=> array('title' => 'ACP_MANAGE_CONFIG', 'auth' => 'ext_orynider/pafiledb && acl_a_pa_files', 'cat' => array('ACP_PA_FILES')),
				'downloads'		=> array('title' => 'ACP_MANAGE_DOWNLOADS', 'auth' => 'ext_orynider/pafiledb && acl_a_pa_files', 'cat' => array('ACP_PA_FILES')),
				'categories'	=> array('title' => 'ACP_MANAGE_CATEGORIES', 'auth' => 'ext_orynider/pafiledb && acl_a_pa_files', 'cat' => array('ACP_PA_FILES')),
			),
		);
	}
}
