<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\acp;

class ug_auth_manage_info
{
	function module()
	{
		return array(
			'filename'			=> 'orynider\pafiledb\acp\ug_auth_manage_module',
			'title'				=> 'ACP_PA_FILES',
			'modes'	=> array(
				'catauth_manage'	=> array(
					'title' => 'ACP_CAT_PERMISSIONS',
					'auth' => 'ext_orynider/pafiledb && acl_a_pa_files',
					'cat' => array('ACP_PA_FILES')
				),					
				'user'	=> array(
					'title' => 'ACP_USER_PERMISSIONS',
					'auth' => 'ext_orynider/pafiledb && acl_a_pa_files',
					'cat' => array('ACP_PA_FILES')
				),
				'group'			=> array(
					'title' => 'ACP_GROUP_PERMISSIONS',
					'auth' => 'ext_orynider/pafiledb && acl_a_pa_files',
					'cat' => array('ACP_PA_FILES')
				),
				'global_user'	=> array(
					'title' => 'ACP_USER_GLOBAL_PERMISSIONS',
					'auth' => 'ext_orynider/pafiledb && acl_a_pa_files',
					'cat' => array('ACP_PA_FILES')
				),
				'global_group'	=> array(
					'title' => 'ACP_GROUP_GLOBAL_PERMISSIONS',
					'auth' => 'ext_orynider/pafiledb && acl_a_pa_files',
					'cat' => array('ACP_PA_FILES')
				),				
			),			
		);		
	}
}
