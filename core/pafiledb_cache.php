<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions_cache.php,v 1.10 2009/10/08 23:23:26 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \phpbb\db\driver\driver_interface;

/**
 * Generic module cache.
 *
 */
class pafiledb_cache extends \phpbb\cache\driver\base
{
	/** @var service */
	protected $cache;	

	/** @var \phpbb\user */
	protected $user;	
	
	/**
	* @param ContainerInterface              $container		 
	 */
	protected $filesystem;
	
	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;	
	
	/** @var string */
	protected $php_ext;		

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string extension root path */
	protected $module_root_path;	
	
	var $modified = false;
	
	/**
	 * @var string
	 */	 
	public $cache_dir;

	private $phpbb_cache;
	private $phpbb_db;
	
	/**
	 * @var array
	 */		
	var $vars = '';
	
	var $vars_ts = array();
	
	var $var_expires = array();
	
	
	/**
	* The database tables
	*
	* @var string
	*/
	protected $pa_files_table;

	protected $pa_cat_table;

	protected $pa_config_table;
	
	protected $pa_votes_table;
	
	protected $pa_comments_table;
	
	protected $pa_license_table;		
	
	/**
	* cache constructor.
	* @param \phpbb\cache\service 								$cache
	
	* @param \phpbb\db\driver\driver_interface 						$db		
	* @param \phpbb\extension\manager							$ext_manager
	* @param \Symfony\Component\DependencyInjection\ContainerInterface 	$phpbb_container DI container
	* @param string $cache_dir Define the path to the cache directory (default: $module_root_path . 'cache/')
	*/
	public function __construct(
								\phpbb\cache\service $cache,
								\phpbb\user $user,								
								\phpbb\db\driver\driver_interface $phpbb_db,
								\phpbb\extension\manager $ext_manager, 
								\Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container,								
								$php_ext, 
								$root_path,		
								$pa_files_table,
								$pa_cat_table,
								$pa_config_table, 
								$pa_votes_table,
								$pa_comments_table,
								$pa_license_table,
								$pa_auth_access_table)
	{
		$this->phpbb_cache 			= $cache;
		$this->user 				= $user;		
		$this->phpbb_db 			= $phpbb_db;
		$this->db 					= $phpbb_db;						
		$this->ext_manager	 		= $ext_manager;
		$this->php_ext 				= $php_ext;

		//global $phpbb_container;		
		$this->container 			= $phpbb_container;
		
		$this->pa_files_table 		= $pa_files_table;
		$this->pa_cat_table 		= $pa_cat_table;
		$this->pa_config_table 		= $pa_config_table;
		$this->pa_votes_table 		= $pa_votes_table;
		$this->pa_comments_table 	= $pa_comments_table;
		$this->pa_license_table 	= $pa_license_table;		
		
		$this->ext_name 			= 'orynider/pafiledb';
		$this->module_root_path		= $this->ext_path = $ext_manager->get_extension_path($this->ext_name, true);
		
		//$this->cache_dir 				= $phpbb_container->getParameter('core.cache_dir'); 
		$this->cache_dir 			= $this->module_root_path . 'cache/';	
		
		$this->filesystem 			= new \phpbb\filesystem\filesystem();		
		
		if (!is_dir($this->cache_dir))
		{
			@mkdir($this->cache_dir, 0777, true);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return pafiledb_cache
	 */
	function pafiledb_cache($cache_dir = false)
	{
		global $phpbb_root_path;
		global $mx_cache, $mx_user, $mx_root_path, $module_root_path, $is_block, $phpEx;
		
		$this->module_root_path = !is_null($module_root_path) ? $module_root_path : $mx_root_path . 'modules/pafiledb/'; 
		$this->cache_dir = !is_null($cache_dir) ? $cache_dir : $module_root_path . 'cache/';			
		$this->container = $mx_cache;
				
		if (!is_dir($this->cache_dir))
		{
			@mkdir($this->cache_dir, 0777, true);
		}		
	}

	/**
	 * Enter description here...
	 *
	 */
	function load()
	{
		global $phpEx;	
		
		$this->cache_dir = !is_null($this->cache_dir) ? $this->cache_dir : $this->module_root_path . 'cache/';			
				
		if (!is_dir($this->cache_dir))
		{
			mkdir($this->cache_dir, 0777, true);
		}
		
		//$this->message_die(GENERAL_ERROR, 'cache file ' . $this->cache_dir . "data_global.$phpEx" . ' couldn\'t be opened.');				 
		if ((@include_once $this->cache_dir . "data_global.$phpEx") === false)
		{			
			$file = '<?php $this->vars=' . $this->format_array($this->vars) . ";\n\$this->vars_ts=" . $this->format_array($this->vars_ts) . ' ?>';
			
			if ($fp = fopen($this->cache_dir . "data_global.$phpEx", 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $file);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
			else			
			{			
				$this->message_die(GENERAL_ERROR, 'cache file ' . $this->cache_dir . "data_global.$phpEx" . ' couldn\'t be opened.');
			}
			
			include_once($this->cache_dir . "data_global.$phpEx");			
		}		
		
		
	}
	
	/**
	* Obtain pafiledb config values
	*/
	public function config_values()
	{		
		if (($this->get('pafiledb_config')) === false)
		{			
			$pafiledb_config = $pafiledb_cached_config = array();

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . $this->pa_config_table;
			$result = $this->phpbb_db->sql_query($sql);

			while ($row = $this->phpbb_db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$pafiledb_cached_config[$row['config_name']] = $row['config_value'];
				}				

				$pafiledb_config[$row['config_name']] = $row['config_value'];
			}
			$this->phpbb_db->sql_freeresult($result);

			$this->put('pafiledb_config', $pafiledb_cached_config);
		}
		else
		{			
			$sql = 'SELECT config_name, config_value
				FROM ' . $this->pa_config_table . '
				WHERE is_dynamic = 1';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$pafiledb_config[$row['config_name']] = $row['config_value'];
			}
			$this->db->sql_freeresult($result);
		}	
		return $pafiledb_config;
	}
	
	/**
	* Set pafiledb config values
	 *
	 * @param unknown_type $config_name
	 * @param unknown_type $config_value
	 */
	function set_config($key, $new_value, $use_cache = false)
	{
		// Read out config values
		$pafiledb_config = $this->config_values();
		$old_value = !isset($pafiledb_config[$key]) ? $pafiledb_config[$key] : false;		
		$use_cache = (($key == 'comments_pagination') || ($key == 'pagination')) ? true : false;
			
		$sql = 'UPDATE ' . $this->pa_config_table . "
			SET config_value = '" . $this->phpbb_db->sql_escape($new_value) . "'
			WHERE config_name = '" . $this->phpbb_db->sql_escape($key) . "'";

		if ($old_value !== false)
		{
			$sql .= " AND config_value = '" . $this->phpbb_db->sql_escape($old_value) . "'";
		}

		$this->phpbb_db->sql_query($sql);

		if (!$this->phpbb_db->sql_affectedrows() && isset($pafiledb_config[$key]))
		{
			return false;
		}

		if (!isset($pafiledb_config[$key]))
		{
			$sql = 'INSERT INTO ' . $this->pa_config_table . ' ' . $this->phpbb_db->sql_build_array('INSERT', array(
				'config_name'	=> $key,
				'config_value'	=> $new_value,
				'is_dynamic'	=> ($use_cache) ? 0 : 1));
			$this->phpbb_db->sql_query($sql);
		}
		
		$pafiledb_config[$key] = $new_value;

		
		if ($use_cache)
		{
			$this->destroy('config');
			$this->put('config', $pafiledb_config);			
		}
		
		return true;		
	}
	
	/**
	 * Enter description here...
	 *
	 */
	function unload()
	{
		$this->save();
		unset( $this->vars );
		unset( $this->vars_ts );
	}

	/**
	 * Enter description here...
	 *
	 */
	function save()
	{
		if ( !$this->modified )
		{
			return;
		}

		global $phpEx;
		$file = '<?php $this->vars=' . $this->format_array( $this->vars ) . ";\n\$this->vars_ts=" . $this->format_array( $this->vars_ts ) . ' ?>';

		if ( $fp = @fopen( $this->cache_dir . 'data_global.' . $phpEx, 'wb' ) )
		{
			@flock( $fp, LOCK_EX );
			fwrite( $fp, $file );
			@flock( $fp, LOCK_UN );
			fclose( $fp );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $expire_time
	 */
	function tidy( $expire_time = 0 )
	{
		global $phpEx;

		$dir = opendir( $this->cache_dir );
		while ( $entry = readdir( $dir ) )
		{
			if ( $entry{0} == '.' || substr( $entry, 0, 4 ) != 'sql_' )
			{
				continue;
			}

			if ( time() - $expire_time >= filemtime( $this->cache_dir . $entry ) )
			{
				unlink( $this->cache_dir . $entry );
			}
		}

		if ( file_exists( $this->cache_dir . 'data_global.' . $phpEx ) )
		{
			foreach ( $this->vars_ts as $varname => $timestamp )
			{
				if ( time() - $expire_time >= $timestamp )
				{
					$this->destroy( $varname );
				}
			}
		}
		else
		{
			$this->vars = $this->vars_ts = array();
			$this->modified = true;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 * @param unknown_type $expire_time
	 * @return unknown
	 */
	function get( $varname, $expire_time = 0 )
	{	
		$varname_exists = $this->exists($varname, $expire_time);
		
		return ($varname_exists) ? $this->vars[$varname] : null;
	}

	/**
	* Put data into cache
	*
	* @param string $var_name 		Cache key
	* @param mixed $var 			Cached data to store
	* @param int $ttl 				Time-to-live of cached data
	* @return null
	*/
	function put($varname, $var, $ttl = 31536000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write('data' . $varname, $var, time() + $ttl);
		}
		else
		{
			$this->vars[$varname] = $var;
			$this->vars_ts[$varname] = time();			
			$this->var_expires[$varname] = time() + $ttl;
			$this->is_modified = $this->modified = true;
		}
	}
	
	/**
	* Destroy cache data
	*
	* @param string $var_name 		Cache key
	* @param string $table 			Table name
	* @return null
	*/
	public function destroy($varname, $table = '')
	{
		global $phpEx;

		if ($varname == 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			$dir = @opendir($this->cache_dir);

			if (!$dir)
			{
				return;
			}

			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}

				if (!($handle = @fopen($this->cache_dir . $entry, 'rb')))
				{
					continue;
				}

				// Skip the PHP header
				fgets($handle);

				// Skip expiration
				fgets($handle);

				// Grab the query, remove the LF
				$query = substr(fgets($handle), 0, -1);

				fclose($handle);

				foreach ($table as $check_table)
				{
					// Better catch partial table names than no table names. ;)
					if (strpos($query, $check_table) !== false)
					{
						$this->remove_file($this->cache_dir . $entry);
						break;
					}
				}
			}
			closedir($dir);

			return;
		}

		if (!$this->_exists($varname))
		{
			return;
		}

		if ($var_name[0] == '_')
		{
			$this->remove_file($this->cache_dir . 'data' . $varname . ".$phpEx", true);
		}
		else if (isset($this->vars[$varname]))
		{
			$this->is_modified = $this->modified = true;
			unset($this->vars[$varname]);
			unset($this->vars_ts[$varname]);			
			unset($this->var_expires[$varname]);

			// We save here to let the following cache hits succeed
			$this->save();
		}		
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 * @param unknown_type $expire_time
	 * @return unknown
	 */
	function exists($varname, $expire_time = 0)
	{
		if ( !is_array( $this->vars ) )
		{
			$this->load();
		}

		if ( $expire_time > 0 && isset( $this->vars_ts[$varname] ) )
		{
			if ( $this->vars_ts[$varname] <= time() - $expire_time )
			{
				$this->destroy( $varname );
				return false;
			}
		}

		return isset( $this->vars[$varname] );
	}
	
	/**
	* Check if a given cache entry exists
	*
	* @param string $var_name 		Cache key
	*
	* @return bool 				True if cache file exists and has not expired.
	*						False otherwise.
	*/	
	function _exists($varname)
	{
		if ($varname[0] == '_')
		{
			global $phpEx;
			
			$var_name = $this->clean_varname($varname);
			
			return file_exists($this->cache_dir . 'data' . $varname . ".$phpEx");
		}
		else
		{
			if (!count($this->vars))
			{
				$this->load();
			}

			if (!isset($this->var_expires[$varname]))
			{
				return false;
			}

			return (time() > $this->var_expires[$varname]) ? false : isset($this->vars[$varname]);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	function format_array( $array )
	{
		$lines = array();
		if ( is_array( $v ) )
		{		
			foreach ( $array as $k => $v )
			{
				if ( is_array( $v ) )
				{
					$lines[] = "'$k'=>" . $this->format_array( $v );
				}elseif ( is_int( $v ) )
				{
					$lines[] = "'$k'=>$v";
				}elseif ( is_bool( $v ) )
				{
					$lines[] = "'$k'=>" . ( ( $v ) ? 'TRUE' : 'FALSE' );
				}
				else
				{
					$lines[] = "'$k'=>'" . str_replace( "'", "\'", str_replace( '\\', '\\\\', $v ) ) . "'";
				}
			}
			return 'array(' . implode( ',', $lines ) . ')';
		}
		else
		{
			return 'array(' . implode( ',', $lines ) . ')';	
		}		
	}

	/**
	* Load result of an SQL query from cache.
	*
	* @param string $query			SQL query
	*
	* @return int|bool				Query ID (integer) if cache contains a rowset
	*						for the specified query.
	*						False otherwise.
	*/
	public function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = md5($query);

		if (($result = $this->_read('sql_' . $query_id)) === false)
		{
			return false;
		}

		$this->sql_rowset[$query_id] = $result;
		$this->sql_row_pointer[$query_id] = 0;

		return $query_id;
	}
	
	/**
	* Save result of an SQL query in cache.
	*
	* In persistent cache stores, this function stores the query
	* result to persistent storage. In other words, there is no need
	* to call save() afterwards.
	*
	* @param \phpbb\db\driver\driver_interface $db	Database connection
	* @param string $query			SQL query, should be used for generating storage key
	* @param mixed $query_result	The result from \dbal::sql_query, to be passed to
	* 								\dbal::sql_fetchrow to get all rows and store them
	* 								in cache.
	* @param int $ttl				Time to live, after this timeout the query should
	*								expire from the cache.
	* @return int|mixed				If storing in cache succeeded, an integer $query_id
	* 								representing the query should be returned. Otherwise
	* 								the original $query_result should be returned.
	*/
	public function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		$query_id = md5($query);
		$this->sql_rowset[$query_id] = array();
		$this->sql_row_pointer[$query_id] = 0;

		while ($row = $db->sql_fetchrow($query_result))
		{
			$this->sql_rowset[$query_id][] = $row;
		}
		$db->sql_freeresult($query_result);

		if ($this->_write('sql_' . $query_id, $this->sql_rowset[$query_id], $ttl + time(), $query))
		{
			return $query_id;
		}

		return $query_result;
	}
	
	/**
	* Check if result for a given SQL query exists in cache.
	*
	* @param int $query_id
	* @return bool
	*/
	public function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}
	
	/**
	* Fetch row from cache (database)
	*
	* @param int $query_id
	* @return array|bool 			The query result if found in the cache, otherwise
	* 						false.
	*/
	public function sql_fetchrow($query_id)
	{
		if ($this->sql_row_pointer[$query_id] < count($this->sql_rowset[$query_id]))
		{
			return $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++];
		}

		return false;
	}
	
	/**
	* Fetch a field from the current row of a cached database result (database)
	*
	* @param int $query_id
	* @param string $field 			The name of the column.
	* @return string|bool 			The field of the query result if found in the cache,
	* 						otherwise false.
	*/
	public function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_row_pointer[$query_id] < count($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++][$field] : false;
		}

		return false;
	}
	
	/**
	* Seek a specific row in an a cached database result (database)
	*
	* @param int $rownum 			Row to seek to.
	* @param int $query_id
	* @return bool
	*/
	public function sql_rowseek($rownum, $query_id)
	{
		if ($rownum >= count($this->sql_rowset[$query_id]))
		{
			return false;
		}

		$this->sql_row_pointer[$query_id] = $rownum;
		return true;
	}
	
	/**
	* Free memory used for a cached database result (database)
	*
	* @param int $query_id
	* @return bool
	*/
	public function sql_freeresult($query_id)
	{
		if (!isset($this->sql_rowset[$query_id]))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);
		unset($this->sql_row_pointer[$query_id]);

		return true;
	}	
	
	/**
	* Read cached data from a specified file
	*
	* @access private
	* @param string $filename Filename to write
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	function _read($filename)
	{
		global $phpEx;

		$filename = $this->clean_varname($filename);
		$file = "{$this->cache_dir}$filename.$phpEx";

		$type = substr($filename, 0, strpos($filename, '_'));

		if (!file_exists($file))
		{
			return false;
		}

		if (!($handle = @fopen($file, 'rb')))
		{
			return false;
		}

		// Skip the PHP header
		fgets($handle);

		if ($filename == 'data_global')
		{
			$this->vars = $this->var_expires = array();

			$time = time();

			while (($expires = (int) fgets($handle)) && !feof($handle))
			{
				// Number of bytes of data
				$bytes = substr(fgets($handle), 0, -1);

				if (!is_numeric($bytes) || ($bytes = (int) $bytes) === 0)
				{
					// We cannot process the file without a valid number of bytes
					// so we discard it
					fclose($handle);

					$this->vars = $this->var_expires = array();
					$this->is_modified = false;

					$this->remove_file($file);

					return false;
				}

				if ($time >= $expires)
				{
					fseek($handle, $bytes, SEEK_CUR);

					continue;
				}

				$var_name = substr(fgets($handle), 0, -1);

				// Read the length of bytes that consists of data.
				$data = fread($handle, $bytes - strlen($var_name));
				$data = @unserialize($data);

				// Don't use the data if it was invalid
				if ($data !== false)
				{
					$this->vars[$var_name] = $data;
					$this->var_expires[$var_name] = $expires;
				}

				// Absorb the LF
				fgets($handle);
			}

			fclose($handle);

			$this->is_modified = false;

			return true;
		}
		else
		{
			$data = false;
			$line = 0;

			while (($buffer = fgets($handle)) && !feof($handle))
			{
				$buffer = substr($buffer, 0, -1); // Remove the LF

				// $buffer is only used to read integers
				// if it is non numeric we have an invalid
				// cache file, which we will now remove.
				if (!is_numeric($buffer))
				{
					break;
				}

				if ($line == 0)
				{
					$expires = (int) $buffer;

					if (time() >= $expires)
					{
						break;
					}

					if ($type == 'sql')
					{
						// Skip the query
						fgets($handle);
					}
				}
				else if ($line == 1)
				{
					$bytes = (int) $buffer;

					// Never should have 0 bytes
					if (!$bytes)
					{
						break;
					}

					// Grab the serialized data
					$data = fread($handle, $bytes);

					// Read 1 byte, to trigger EOF
					fread($handle, 1);

					if (!feof($handle))
					{
						// Somebody tampered with our data
						$data = false;
					}
					break;
				}
				else
				{
					// Something went wrong
					break;
				}
				$line++;
			}
			fclose($handle);

			// unserialize if we got some data
			$data = ($data !== false) ? @unserialize($data) : $data;

			if ($data === false)
			{
				$this->remove_file($file);
				return false;
			}

			return $data;
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
				$sql_error = array(@print_r(@$this->phpbb_db->sql_error_returned));				
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

	/**
	* Write cache data to a specified file
	*
	* 'data_global' is a special case and the generated format is different for this file:
	* <code>
	* <?php exit; ?>
	* (expiration)
	* (length of var and serialised data)
	* (var)
	* (serialised data)
	* ... (repeat)
	* </code>
	*
	* The other files have a similar format:
	* <code>
	* <?php exit; ?>
	* (expiration)
	* (query) [SQL files only]
	* (length of serialised data)
	* (serialised data)
	* </code>
	*
	* @access private
	* @param string $filename Filename to write
	* @param mixed $data Data to store
	* @param int $expires Timestamp when the data expires
	* @param string $query Query when caching SQL queries
	* @return bool True if the file was successfully created, otherwise false
	*/
	function _write($filename, $data = null, $expires = 0, $query = '')
	{
		global $phpEx;

		$filename = $this->clean_varname($filename);
		$file = "{$this->cache_dir}$filename.$phpEx";

		$lock = new \phpbb\lock\flock($file);
		$lock->acquire();

		if ($handle = @fopen($file, 'wb'))
		{
			// File header
			fwrite($handle, '<' . '?php exit; ?' . '>');

			if ($filename == 'data_global')
			{
				// Global data is a different format
				foreach ($this->vars as $var => $data)
				{
					if (strpos($var, "\r") !== false || strpos($var, "\n") !== false)
					{
						// CR/LF would cause fgets() to read the cache file incorrectly
						// do not cache test entries, they probably won't be read back
						// the cache keys should really be alphanumeric with a few symbols.
						continue;
					}
					$data = serialize($data);

					// Write out the expiration time
					fwrite($handle, "\n" . $this->var_expires[$var] . "\n");

					// Length of the remaining data for this var (ignoring two LF's)
					fwrite($handle, strlen($data . $var) . "\n");
					fwrite($handle, $var . "\n");
					fwrite($handle, $data);
				}
			}
			else
			{
				fwrite($handle, "\n" . $expires . "\n");

				if (strpos($filename, 'sql_') === 0)
				{
					fwrite($handle, $query . "\n");
				}
				$data = serialize($data);

				fwrite($handle, strlen($data) . "\n");
				fwrite($handle, $data);
			}

			fclose($handle);

			if (function_exists('opcache_invalidate'))
			{
				@opcache_invalidate($file);
			}

			try
			{
				$this->filesystem->phpbb_chmod($file, CHMOD_READ | CHMOD_WRITE);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}

			$return_value = true;
		}
		else
		{
			$return_value = false;
		}

		$lock->release();

		return $return_value;
	}

	/**
	* Replace slashes in the file name
	*
	* @param string $varname name of a cache variable
	* @return string $varname name that is safe to use as a filename
	*/
	protected function clean_varname($varname)
	{
		return str_replace(array('/', '\\'), '-', $varname);
	}	
	
}
?>