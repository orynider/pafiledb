<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_user_info,v 1.62 2012/01/09 06:58:15 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;


/**
 * pafiledb_user_info
 *
 * This class is used to determin Browser and operating system info of the user
 *
 * @access public
 * @author http://www.chipchapin.com
 * @copyright (c) 2002 Chip Chapin <cchapin@chipchapin.com>
 */
class pafiledb_user_info
{
	
	/**#@+
	* Constant identifying the super global with the same name.
	*/
	const POST = 0;
	const GET = 1;
	const REQUEST = 2;
	const COOKIE = 3;
	const SERVER = 4;
	const FILES = 5;
	/**#@-*/
	
	/** @var \orynider\pafiledb\core\ pafiledb */
	protected $functions;	
	/** @var \orynider\pafiledb\core\ pafiledb_functions */
	protected $pafiledb_functions;
	/** @var \orynider\pafiledb\core\pafiledb_cache */
	protected $pafiledb_cache;		
	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;	
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\request\request */
	protected $request;
	
	var $agent = 'Unknown';
	var $user_agent = 'Unknown';
	var $ver = 0;
	var $majorver = 0;
	var $minorver = 0;
	var $platform = 'Unknown';	
		
	/**
	 * Constructor.
	 *
	 * Determine client browser type, version and platform using heuristic examination of user agent string.
	 *
	 * @param unknown_type $user_agent allows override of user agent string for testing.
	 */
	public function __construct(
		\orynider\pafiledb\core\pafiledb $functions,
		\orynider\pafiledb\core\pafiledb_functions $pafiledb_functions,		
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,		
		\phpbb\request\request $request,
		\phpbb\auth\auth $auth,		
		$pa_download_info_table)
	{
		$this->functions 				= $functions;
		$this->pafiledb_functions 		= $pafiledb_functions;		
		$this->template 				= $template;
		$this->user 					= $user;
		$this->db 						= $db;
		$this->config 					= $config;			
		$this->request 					= $request;
		$this->auth 					= $auth;		
		$this->pa_download_info_table	= $pa_download_info_table;
		// Read out config values
		$this->pafiledb_config 			= $functions->config_values();
		$this->is_admin 				= $auth->acl_get('a_') ? true : 0;		
		
		global $HTTP_USER_AGENT, $HTTP_SERVER_VARS;
		
		if ($this->request->is_set('HTTP_USER_AGENT', \phpbb\request\request_interface::SERVER))
		{
			$HTTP_USER_AGENT = $this->request->variable('HTTP_USER_AGENT', '', true, \phpbb\request\request_interface::SERVER);
		}
		else if ( !isset( $HTTP_USER_AGENT ) )
		{
			$HTTP_USER_AGENT = $this->request->server('HTTP_USER_AGENT', '');
		}

		if (empty($this->user_agent))
		{
			$this->user_agent = $HTTP_USER_AGENT;
		}

		$user_agent = strtolower($this->user_agent);
		
		// Determine browser and version
		// The order in which we test the agents patterns is important
		// Intentionally ignore Konquerer.  It should show up as Mozilla.
		// post-Netscape Mozilla versions using Gecko show up as Mozilla 5.0
		// known browsers, list will be updated routinely, check back now and then
		if ( preg_match( '/(android\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(iphone\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ipod\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(phoenix\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(firebird\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;	
		elseif ( preg_match( '/(konqueror |konq\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(netscape\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(opera |opera\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(msie )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;			
		elseif ( preg_match( '/(theworld )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(chrome\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(safari |saf\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(applewebkit )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ); 
		elseif ( preg_match( '/(mozilla\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) );
		elseif ( preg_match( '/(firefox\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(maxthon )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		// covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
		elseif ( preg_match( '/(gecko |moz\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(netpositive |netp\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(lynx |lynx\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(elinks |elinks\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(links |links\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(w3m |w3m\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(webtv |webtv\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(amaya |amaya\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(dillo |dillo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ibrowsevibrowse |ibrowsevibrowse\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(icab |icab\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(crazy browser |ie\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(sonyericssonp800 |sonyericssonp800\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(aol )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(camino )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// search engine spider bots:
		elseif ( preg_match( '/(googlebot |google\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(mediapartners-google |adsense\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo-verticalcrawler |yahoo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo! slurp |yahoo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo-mm |yahoomm\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(inktomi |inktomi\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(slurp |inktomi\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(fast-webcrawler |fast\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(msnbot |msn\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ask jeeves |ask\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(teoma |ask\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(scooter |scooter\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(openbot |openbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ia_archiver |ia_archiver\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(zyborg |looksmart\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(almaden |ibm\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(baiduspider |baidu\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(psbot |psbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(gigabot |gigabot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(naverbot |naverbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(surveybot |surveybot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(boitho.com-dc |boitho\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(objectssearch |objectsearch\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(answerbus |answerbus\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(sohu-search |sohu\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(iltrovatore-setaccio |il-set\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// various http utility libaries
		elseif ( preg_match( '/(w3c_validator |w3c\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(wdg_validator |wdg\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(libwww-perl |libwww-perl\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(jakarta commons-httpclient |jakarta\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(python-urllib |python-urllib\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// download apps
		elseif ( preg_match( '/(getright |getright\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(wget |wget\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		else
		{
			$matches[1] = 'unknown';
			$matches[2] = 0;
			$matches[3] = 0;
		}

		$this->majorver = $matches[2];
		$this->minorver = $matches[3];
		$this->ver = $matches[2] . '.' . $matches[3];
		switch ( $matches[1] )
		{
			case 'Android/':
			case 'android ':
				$this->agent = 'ANDROID';
			break;
			case 'iPhone/':
			case 'iphone ':
				$this->agent = 'IPHONE';
			break;
			case 'iPod/':
			case 'ipod ':
				$this->agent = 'IPOD';
			break;
			case 'Chrome/':
			case 'chrome ':
				$this->agent = 'GOOGLE_CHROME';
			break;
			case 'opera/':
			case 'opera ':
				$this->agent = 'OPERA';
			break;				
			case 'opera/':
			case 'opera ':
				$this->agent = 'OPERA';
			break;
			case 'msie ':
				$this->agent = 'IE';
			break;
			case 'TheWorld/':
			case 'theworld ':			
				$this->agent = 'THEWORLD';
			break;			
			case 'mozilla/':
				$this->agent = 'NETSCAPE';
				if ( $this->majorver >= 5 )
				{
					$this->agent = 'MOZILLA';
				}
			break;
			case 'firefox/':
			case 'firefox ':			
				$this->agent = 'MOZILLA';
			break;			
 			case 'phoenix ':
 			case 'firebird ':
				$this->agent = 'MOZILLA';
			break;
			case 'konqueror ':
			case 'konq ':
				$this->agent = 'KONQUEROR';
			break;
			case 'lynx/':
			case 'lynx ':
				$this->agent = 'LYNX';
			break;
			case 'safari ':
			case 'saf ':
				$this->agent = 'SAFARI';
			break;
			case 'Maxthon/':
			case 'maxthon ':			
				$this->agent = 'MAXTHON';
			break;			
			case 'aol/':
			case 'aol ':
				$this->agent = 'AOL';
			break;
			case 'omniweb':
			case 'omni ':
				$this->agent = 'OTHER';
			break;
			case 'gecko ':
 			case 'moz ':
				$this->agent = 'OTHER';
			break;
			case 'netpositive ':
			case 'netp ':
				$this->agent = 'OTHER';
			break;

			case 'elinks/':
			case 'elinks ':
				$this->agent = 'OTHER';
			break;
			case 'links/':
			case 'links ':
				$this->agent = 'OTHER';
			break;
			case 'w3m/':
			case 'w3m ':
				$this->agent = 'OTHER';
			break;
			case 'webtv/':
			case 'webtv ':
				$this->agent = 'OTHER';
			break;
			case 'amaya/':
			case 'amaya ':
				$this->agent = 'OTHER';
			break;
			case 'dillo/':
			case 'dillo ':
				$this->agent = 'OTHER';
			break;
			case 'ibrowsevibrowse/':
			case 'ibrowsevibrowse ':
				$this->agent = 'OTHER';
			break;
			case 'icab/':
			case 'icab ':
				$this->agent = 'OTHER';
			break;
			case 'crazy browser ':
			case 'ie ':
				$this->agent = 'OTHER';
			break;
			case 'camino/ ':
			case 'camino ':
				$this->agent = 'OTHER';
			break;
			case 'sonyericssonp800/':
			case 'sonyericssonp800 ':
				$this->agent = 'OTHER';
			break;
			
			case 'googlebot ':
			case 'google ':
			case 'mediapartners-google ':
			case 'adsense ':
			case 'yahoo-verticalcrawler ':
			case 'yahoo ':
			case 'yahoo! slurp ':
			case 'yahoo-mm ':
			case 'yahoomm ':
			case 'inktomi ':
			case 'slurp ':
			case 'fast-webcrawler ':
			case 'msnbot ':
			case 'msn ':
			case 'ask jeeves ':
			case 'ask ':
			case 'teoma ':
			case 'scooter ':
			case 'openbot ':
			case 'ia_archiver ':
			case 'zyborg ':
			case 'looksmart ':
			case 'almaden ':
			case 'baiduspider ':
			case 'baidu ':
			case 'psbot ':
			case 'gigabot ':
			case 'naverbot ':
			case 'surveybot ':
			case 'boitho.com-dc ':
			case 'boitho ':
			case 'objectssearch ':
			case 'answerbus ':
			case 'sohu-search ':
			case 'sohu ':
			case 'iltrovatore-setaccio ':
			case 'il-set ':
				$this->agent = 'BOT';
			break;
			case 'unknown':
				$this->agent = 'UNKNOWN';
			break;
			default:
				$this->agent = 'Oops!';
		}	
		// Determine platform
		// This is very incomplete for platforms other than Win/Mac
		if ( preg_match( '/(android|iphone|ipod|win|mac|linux|unix|x11|freebsd|beos|ubuntu|fedora|os2|irix|sunos|aix)/', $user_agent, $matches ) );
		else $matches[1] = 'unknown';
		
		switch ( $matches[1] )
		{
			// Mobiles		
			case 'android':
				$this->platform = 'Android';
			break;		
			case 'iphone':
				$this->platform = 'IOS';
			break;	
			case 'ipod':
				$this->platform = 'IOS';
			break;
			// Windows			
			case 'win':
				$this->platform = 'Win';
			break;		
			// Mac			
			case 'mac':
				$this->platform = 'Mac';
			break;
			case 'os2':
				$this->platform = 'OS2';
				break;			
			// Linux		
			case 'linux':
				$this->platform = 'Linux';
			break;
			case 'unix':
			case 'x11':
				$this->platform = 'Unix';
			break;
			case 'freebsd':
				$this->platform = 'FreeBSD';
			break;
			case 'beos':
				$this->platform = 'BeOS';
			break;
			case 'ubuntu':
				$this->platform = 'Ubuntu';
			break;
			case 'fedora':
				$this->platform = 'Fedora';
			break;			
			
            case 'irix':
				$this->platform = 'IRIX';
			break;
            case 'sunos':
				$this->platform = 'SunOS';
			break;
            case 'aix':
				$this->platform = 'Aix';
			break;
            case 'palm':
				$this->platform = 'PalmOS';
			break;
			case 'unknown':
				$this->platform = 'Other';
			break;
			default:
				$this->platform = 'Oops!';
		}
	}

	/**
	 * update_info.
	 *
	 * @param unknown_type $id
	 */
	function update_info( $id )
	{		
		$user_id = empty($this->user->data) ? ANONYMOUS : $this->user->data['user_id'];
		$user_ip = empty($this->user->ip) ? '' : $this->user->ip;		

		$where_sql = ( $this->user->data['user_id'] != ANONYMOUS ) ? "user_id = '" . $this->user->data['user_id'] . "'" : "downloader_ip = '" . $user_ip . "'";

		$sql = "SELECT user_id, downloader_ip
			FROM " . $this->pa_download_info_table . "
			WHERE $where_sql";

		if ( !( $result = $this->db->sql_query( $sql ) ) )
		{
			$this->functions->message_die( GENERAL_ERROR, 'Couldnt Query User id', '', __LINE__, __FILE__, $sql );
		}

		if ( !$this->db->sql_affectedrows( $result ) )
		{
			$sql = "INSERT INTO " . $this->pa_download_info_table . " (file_id, user_id, downloader_ip, downloader_os, downloader_browser, browser_version)
						VALUES('" . $id . "', '" . $this->user->data['user_id'] . "', '" . $user_ip . "', '" . $this->platform . "', '" . $this->agent . "', '" . $this->ver . "')";
			if ( !( $this->db->sql_query( $sql ) ) )
			{
				$this->functions->message_die( GENERAL_ERROR, 'Couldnt Update Downloader Table Info', '', __LINE__, __FILE__, $sql );
			}
		}

		$this->db->sql_freeresult( $result );
	}

	// ------------------------------------
	// Functions
	// ------------------------------------

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function get_formated_url()
	{
		$server_protocol = ( $this->config['cookie_secure'] ) ? 'https://' : 'http://';
		$server_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $this->config['server_name'] ) );
		$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim( $this->config['server_port'] ) : '';

		$formated_url = $server_protocol . $server_name . $server_port;
		
		$formated_url = function_exists('generate_board_url') ? generate_board_url() . '/' : $formated_url;
		
		return $formated_url;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $rating
	 * @return unknown
	 */
	function paImageRating( $rating )
	{

		if ( !$rating )
			return( "<i>Not Rated</i>" );
		else
			return ( round( $rating, 2 ) );
	}

	// =========================================================================
	// this function Borrowed from Acyd Burn attachment mod, (thanks Acyd for this great mod)
	// =========================================================================
	function send_file_to_browser($real_filename, $physical_filename, $upload_dir)
	{
		global $HTTP_USER_AGENT, $HTTP_SERVER_VARS;

		if ( $upload_dir == '' )
		{
			$filename = $physical_filename;
		}
		else
		{
			$filename = $upload_dir . $physical_filename;
		}

		$gotit = false;
		if ( @!file_exists( @$this->pafiledb_functions->pafiledb_realpath( $filename ) ) )
		{
			$this->functions->message_die( GENERAL_ERROR, $lang['Error_no_download'] . '<br /><br /><b>404 File Not Found:</b> The File <i>' . $filename . '</i> does not exist.' );
		}
		else
		{
			$gotit = true;
			$size = @filesize( $filename );
			if ( $size > ( 1048575 * 6 ) )
			{
				return false;
			}
		}

		// Determine the Browser the User is using, because of some nasty incompatibilities.
		// borrowed from phpMyAdmin. :)
		$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $this->user_agent;
		$log_version = array();
		//return preg_match(‘|#'.$pattern.'#', $string, $array);  
		if (preg_match('/Opera ([0-9].[0-9]{1,2})/', $user_agent, $log_version))
		{
			$browser_version = $log_version[2];
			$browser_agent = 'opera';
		}
		else if (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $user_agent, $log_version))
		{
			$browser_version = $log_version[1];
			$browser_agent = 'ie';
		}
		else if (preg_match( '/(mozilla\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))
		{
			$browser_version = $log_version[1];
			$browser_agent = 'mozilla';
		}
		else if (preg_match( '/(Safari\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))		
		{
			$browser_version = $log_version[1] . '.' . $log_version[1];
			$browser_agent = 'safari';
		}
		else if (preg_match('/BROWSER_CHROME ([0-9].[0-9]{1,2})/', strtoupper($user_agent), $log_version))		
		{
			$browser_version = $log_version[1] . '.' . $log_version[1];
			$browser_agent = 'CHROME';
		}	
		else if (preg_match( '/(theworld\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))
		{
			$browser_version = $log_version[1];
			$browser_agent = 'theworld';
		}		
		else if (preg_match( '/(maxthon\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))
		{
			$browser_version = $log_version[1];
			$browser_agent = 'maxthon';
		}	
		else if (preg_match( '/(OmniWeb\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))	
		{
			$browser_version = $log_version[1];
			$browser_agent = 'omniweb';
		}
		else if (preg_match( '/(Konqueror\/)([0-9]*).([0-9]{1,2})/', $user_agent, $log_version))		
		{
			$browser_version = $log_version[2];
			$browser_agent = 'konqueror';
		}
		else if (preg_match('/BROWSER_IPHONE ([0-9].[0-9]{1,2})/', strtoupper($user_agent), $log_version))		
		{
			$browser_version = $log_version[2];
			$browser_agent = 'IPHONE';	        
		}
		else if (preg_match('/BROWSER_IPOD ([0-9].[0-9]{1,2})/', strtoupper($user_agent), $log_version))		
		{	
			$browser_version = $log_version[2];
			$browser_agent = 'IPOD';	        
		} 
		else if (preg_match('/BROWSER_ANDROID ([0-9].[0-9]{1,2})/', strtoupper($user_agent), $log_version))		
		{		
			$browser_version = $log_version[2];
			$browser_agent = 'ANDROID';	        
		}        
		else
		{
			$browser_version = 0;
			$browser_agent = 'other';
		}

		//
		// Get mimetype
		//
		switch ($this->pafiledb_functions->get_extension($physical_filename))
		{
			case 'pdf':
				$mimetype = 'application/pdf';
			break;

			case 'zip':
				$mimetype = 'application/zip';
			break;

			case 'gzip':
				$mimetype = 'application/x-gzip';
			break;

			case 'tar':
				$mimetype = 'application/x-tar';
			break;

			case 'tar.gz':
				$mimetype = 'application/x-gzip';
			break;

			case 'tar.bz2':
				$mimetype = 'application/x-bzip2';
			break;

			case 'doc':
				$mimetype = 'application/msword';
			break;

			// Windows Media Player
			case 'mpg':
				$mimetype = 'application/x-mplayer2';
			break;

			case 'mp3':
				$mimetype = 'audio/mp3';
			break;

			/*
			case 'asx':
				$mimetype = 'video/x-ms-asf';
			break;

			case 'wma':
				$mimetype = 'audio/x-ms-wma';
			break;

			case 'wax':
				$mimetype = 'audio/x-ms-wax';
			break;

			case 'wmv':
				$mimetype = 'video/x-ms-wmv';
			break;

			case 'wvx':
				$mimetype = 'video/x-ms-wvx';
			break;

			case 'wm':
				$mimetype = 'video/x-ms-wm';
			break;

			case 'wmx':
				$mimetype = 'video/x-ms-wmx';
			break;

			case 'wmz':
				$mimetype = 'application/x-ms-wmz';
			break;

			case 'wmd':
				$mimetype = 'application/x-ms-wmd';
			break;
			*/

			// Real Player
			case 'rpm':
				$mimetype = 'audio/x-pn-realaudio-plugin';
			break;

			default:
				$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
			break;
		}

		//
		// Correct the Mime Type, if it's an octetstream
		//
		/*
		if ( ( $mimetype == 'application/octet-stream' ) || ( $mimetype == 'application/octetstream' ) )
		{
			$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
		}
		*/

		// Correct the mime type - we force application/octetstream for all files, except images
		// Please do not change this, it is a security precaution
		//$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';

		if (@ob_get_length())
		{
			@ob_end_clean();
		}
		@ini_set( 'zlib.output_compression', 'Off' );

		header('Pragma: public');
		header('Cache-control: private, must-revalidate');

		// Send out the Headers
		if ($this->request->is_set('save_as', \phpbb\request\request_interface::GET) || true)
		{
			//
			// Force the "save file as" dialog
			//
			$mimetype = 'application/x-download'; // Fix for avoiding browser doing an 'inline' for known mimetype anyway
			header('Content-Type: ' . $mimetype . '; name="' . $real_filename . '"');
			header('Content-Disposition: attachment; filename="' . $real_filename . '"');
		}
		else
		{
			header('Content-Type: ' . $mimetype . '; name="' . $real_filename . '"');
			header('Content-Disposition: inline; filename="' . $real_filename . '"');
		}

		// Now send the File Contents to the Browser
		$size = @filesize($filename);
		if ($size)
		{
			header("Content-length: $size");
		}
		$result = @readfile($filename);

		if (!$result)
		{
			// PHP track_errors setting On?
			if (!empty($php_errormsg))
			{
				$this->functions->message_die( GENERAL_ERROR, 'Unable to deliver file.<br />Error was: ' . $php_errormsg, E_USER_WARNING);
			}

			$this->functions->message_die( GENERAL_ERROR, 'Unable to deliver file.');
		}

		flush();
		exit;
	}

	function pa_redirect( $file_url )
	{
		if ( isset( $this->db ) )
		{
			$this->$db->sql_close();
		}

		if ( isset( $this->pafiledb_cache ) )
		{
			$this->pafiledb_cache->unload();
		}
		// Redirect via an HTML form for PITA webservers
		if ( @preg_match( '/Microsoft|WebSTAR|Xitami/', getenv( 'SERVER_SOFTWARE' ) ) )
		{
			header( 'Refresh: 0; URL=' . $file_url );
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta http-equiv="refresh" content="0; url=' . $file_url . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . $file_url . '">HERE</a> to be redirected</div></body></html>';
			exit;
		}
		// Behave as per HTTP/1.1 spec for others
		header( "Location: $file_url" );
		exit();
	}
}
?>