<?php
define( 'IN_PHPBB', true );
define( 'IN_DOWNLOAD', true );
$phpbb_root_path = './../../';
include( $phpbb_root_path . 'extension.inc' );
include( $phpbb_root_path . 'common.' . $phpEx );

// Start session management

$userdata = session_pagestart( $user_ip, PAGE_DOWNLOAD );
init_userprefs( $userdata );

// End session management

/*if(!$db->sql_query(""))
{
$error = $db->sql_error();
echo " -> <b>FAILED</b> ---> <u>" . $error['message'] . "</u><br /><br />\n\n";
exit();
}
else
die("done");
exit();
* */
if ( $userdata['user_level'] != ADMIN )
{
	message_die( GENERAL_MESSAGE, $lang['Not_admin'] );
}
// include common files
// include($phpbb_root_path . 'pafiledb/pafiledb_common.'.$phpEx);

// Lets build a page ...


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;">
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--

font,th,td,p,body { font-family: "Courier New", courier; font-size: 11pt }

a:link,a:active,a:visited { color : #006699; }
a:hover		{ text-decoration: underline; color : #DD6900;}

hr	{ height: 0px; border: solid #D1D7DC 0px; border-top-width: 1px;}

.maintitle,h1,h2	{font-weight: bold; font-size: 22px; font-family: "Trebuchet MS",Verdana, Arial, Helvetica, sans-serif; text-decoration: none; line-height : 120%; color : #000000;}

.ok {color:green}

-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#006699" vlink="#5584AA">

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center">
	<tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="center" width="100%" valign="middle"><span class="maintitle">Installing PaFileDB for phpBB2</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<?php 

// Here we go

include( $phpbb_root_path . 'includes/sql_parse.' . $phpEx );

$available_dbms = array( "mysql" => array( "SCHEMA" => "pafiledb_mysql",
		"UPDATE008" => "update_008_009d",
		"UPDATE009A" => "update_009a_009d",
		"UPDATE009B" => "update_009b_009d",
		"UPDATE009DMX10" => "update_009d_mx10",
		"DELIM" => ";",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_remarks" 
		),
	"mysql4" => array( "SCHEMA" => "pafiledb_mysql",
		"UPDATE008" => "update_008_009d",
		"UPDATE009A" => "update_009a_009d",
		"UPDATE009B" => "update_009b_009d",
		"UPDATE009DMX10" => "update_009d_mx10",
		"DELIM" => ";",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_remarks" 
		),
	"mssql" => array( "SCHEMA" => "pafiledb_mssql",
		"UPDATE008" => "update_008_009c",
		"UPDATE009A" => "update_009a_009c",
		"UPDATE009B" => "update_009b_009c",
		"UPDATE009DMX10" => "update_009d_mx10",
		"DELIM" => "GO",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments" 
		),
	"mssql-odbc" => array( "SCHEMA" => "pafiledb_mssql",
		"UPDATE008" => "update_008_009c",
		"UPDATE009A" => "update_009a_009c",
		"UPDATE009B" => "update_009b_009c",
		"UPDATE009DMX10" => "update_009d_mx10",
		"DELIM" => "GO",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments" 
		),
	"postgres" => array( "LABEL" => "PostgreSQL 7.x",
		"SCHEMA" => "pafiledb_postgres",
		"UPDATE008" => "update_008_009c",
		"UPDATE009A" => "update_009a_009c",
		"UPDATE009B" => "update_009b_009c",
		"UPDATE009DMX10" => "update_009d_mx10",
		"DELIM" => ";",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments" 
		) 
	);

if ( isset( $_REQUEST['action'] ) )
{
	if ( $_REQUEST['action'] == 'install' )
	{
		$dbms_file = $available_dbms[$dbms]['SCHEMA'] . '.sql';
	}elseif ( $_REQUEST['action'] == 'update008' )
	{
		$dbms_file = $available_dbms[$dbms]['UPDATE0008'] . '.sql';
	}elseif ( $_REQUEST['action'] == 'update009a' )
	{
		$dbms_file = $available_dbms[$dbms]['UPDATE009A'] . '.sql';
	}elseif ( $_REQUEST['action'] == 'update009b' )
	{
		$dbms_file = $available_dbms[$dbms]['UPDATE009B'] . '.sql';
	}elseif ( $_REQUEST['action'] == 'update009d_mx10' )
	{
		$dbms_file = $available_dbms[$dbms]['UPDATE009DMX10'] . '.sql';
	}
	else
	{
		die( 'INVALID ACTION' );
	}

	$remove_remarks = $available_dbms[$dbms]['COMMENTS'];;
	$delimiter = $available_dbms[$dbms]['DELIM'];
	$delimiter_basic = $available_dbms[$dbms]['DELIM_BASIC'];

	if ( !( $fp = @fopen( $dbms_file, 'r' ) ) )
	{
		message_die( GENERAL_MESSAGE, "Can't open " . $dbms_file );
	}

	fclose( $fp ); 
	
	// process db schema & basic
	
	$sql_query = @fread( @fopen( $dbms_file, 'r' ), @filesize( $dbms_file ) );
	$sql_query = preg_replace( '/phpbb_/', $table_prefix, $sql_query );

	$sql_query = $remove_remarks( $sql_query );
	$sql_query = split_sql_file( $sql_query, $delimiter );

	$sql_count = count( $sql_query );

	for( $i = 0; $i < $sql_count; $i++ )
	{
		echo "Running :: " . $sql_query[$i];
		@flush();

		if ( !( $result = $db->sql_query( $sql_query[$i] ) ) )
		{
			$errored = true;
			$error = $db->sql_error();
			echo " -> <b>FAILED</b> ---> <u>" . $error['message'] . "</u><br /><br />\n\n";
		}
		else
		{
			echo " -> <b><span class=\"ok\">COMPLETED</span></b><br /><br />\n\n";
		}
	}

	$message = '';

	if ( $errored )
	{
		$message .= '<br />Some queries failed. Please contact me at <a href="http://mohd.vraag-en-antwoord.nl/main/">http://mohd.vraag-en-antwoord.nl/main/</a> we may solve your problems...';
	}
	else
	{
		$message .= '<br />Pafiledb Tables generated successfully.';
	}

	echo "\n<br />\n<b>COMPLETE!</b><br />\n";
	echo $message . "<br />";
	echo "<br /><b>NOW DELETE THIS FILE</b><br />\n";
}
else
{
	echo '<center>
	<b><a href="pafiledb_installer.php?action=install">New Installation</a></b><br />
	<b><a href="pafiledb_installer.php?action=update008">Update From pafiledb 0.0.8</a></b><br />
	<b><a href="pafiledb_installer.php?action=update009a">Update From pafiledb 0.0.9a</a></b><br />
	<b><a href="pafiledb_installer.php?action=update009b">Update From pafiledb 0.0.9b</a></b><br />
	<b><a href="pafiledb_installer.php?action=update009d_mx10">Update From pafiledb 0.0.9d</a></b>
	</center>';
}

echo "</body>";
echo "</html>";

/*$temp_config = pafiledb_config();

foreach($temp_config as $field_name => $field_value)
{
	$value = trim(str_replace("\'", "''", $field_value));
	if($field_name == 'settings_id' || $field_name == 'settings_showss' || $field_name == 'settings_sitename' || $field_name == 'settings_dburl' || $field_name == 'settings_homeurl')
	{
		continue;
	}
	$sql = "INSERT INTO phpbb_pa_config VALUES ('$field_name', '$value')";
	if( !$db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Failed to update Pafiledb configuration for $field_name", "", __LINE__, __FILE__, $sql);
	}
	$sql_out .= $sql . "<b>\n";
}

message_die(GENERAL_ERROR, "DONE<br>\n" . $sql_out);
*/

?>
