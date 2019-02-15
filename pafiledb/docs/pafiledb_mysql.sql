# phpMyAdmin MySQL-Dump
# version 2.3.0-rc2
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Jul 05, 2003 at 08:14 PM
# Server version: 4.00.01
# PHP Version: 4.2.2
# Database : `main`
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_cat`
#

CREATE TABLE phpbb_pa_cat (
  cat_id int(10) NOT NULL auto_increment,
  cat_name text,
  cat_desc text,
  cat_parent int(50) default NULL,
  parents_data text NOT NULL,
  cat_order int(50) default NULL,
  cat_allow_file tinyint(2) NOT NULL default '0',
  cat_allow_ratings tinyint(2) NOT NULL default '1',
  cat_allow_comments tinyint(2) NOT NULL default '1',
  cat_files mediumint(8) NOT NULL default '-1',
  cat_last_file_id mediumint(8) unsigned NOT NULL default '0',
  cat_last_file_name varchar(255) NOT NULL default '',
  cat_last_file_time INT(50) UNSIGNED DEFAULT '0' NOT NULL,
  auth_view tinyint(2) NOT NULL default '0',
  auth_read tinyint(2) NOT NULL default '0',
  auth_view_file tinyint(2) NOT NULL default '0',
  auth_edit_file tinyint(1) DEFAULT '0' NOT NULL,
  auth_delete_file tinyint(1) DEFAULT '0' NOT NULL,
  auth_upload tinyint(2) NOT NULL default '0',
  auth_download tinyint(2) NOT NULL default '0',
  auth_rate tinyint(2) NOT NULL default '0',
  auth_email tinyint(2) NOT NULL default '0',
  auth_view_comment tinyint(2) NOT NULL default '0',
  auth_post_comment tinyint(2) NOT NULL default '0',
  auth_edit_comment tinyint(2) NOT NULL default '0',
  auth_delete_comment tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (cat_id)
) TYPE=MyISAM;

# --------------------------------------------------------

INSERT INTO phpbb_pa_cat VALUES (1, 'My Category', '', 0, '', 1, 0, 1, 1, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO phpbb_pa_cat VALUES (2, 'Test Cagegory', 'Just a test category', 1, '', 2, 1, 1, 1, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);


CREATE TABLE phpbb_pa_auth (
   group_id mediumint(8) DEFAULT '0' NOT NULL,
   cat_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   auth_view tinyint(1) DEFAULT '0' NOT NULL,
   auth_read tinyint(1) DEFAULT '0' NOT NULL,
   auth_view_file tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit_file tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete_file tinyint(1) DEFAULT '0' NOT NULL,
   auth_upload tinyint(1) DEFAULT '0' NOT NULL,
   auth_download tinyint(1) DEFAULT '0' NOT NULL,
   auth_rate tinyint(1) DEFAULT '0' NOT NULL,
   auth_email tinyint(1) DEFAULT '0' NOT NULL,
   auth_view_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_post_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_mod tinyint(1) DEFAULT '0' NOT NULL,
   auth_search tinyint(1) DEFAULT '1' NOT NULL,
   auth_stats tinyint(1) DEFAULT '1' NOT NULL,
   auth_toplist tinyint(1) DEFAULT '1' NOT NULL,
   auth_viewall tinyint(1) DEFAULT '1' NOT NULL,
   KEY group_id (group_id),
   KEY cat_id (cat_id)
);

#
# Table structure for table `phpbb_pa_comments`
#

CREATE TABLE phpbb_pa_comments (
  comments_id int(10) NOT NULL auto_increment,
  file_id int(10) NOT NULL default '0',
  comments_text text NOT NULL,
  comments_title text NOT NULL,
  comments_time int(50) NOT NULL default '0',
  comment_bbcode_uid varchar(10) default NULL,
  poster_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (comments_id),
  KEY comments_id (comments_id),
  FULLTEXT KEY comment_bbcode_uid (comment_bbcode_uid)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_config`
#

CREATE TABLE phpbb_pa_config (
  config_name varchar(255) NOT NULL default '',
  config_value varchar(255) NOT NULL default '',
  PRIMARY KEY  (config_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_custom`
#

CREATE TABLE phpbb_pa_custom (
  custom_id int(50) NOT NULL auto_increment,
  custom_name text NOT NULL,
  custom_description text NOT NULL,
  data text NOT NULL,
  field_order int(20) NOT NULL default '0',
  field_type tinyint(2) NOT NULL default '0',
  regex varchar(255) NOT NULL default '',
  PRIMARY KEY  (custom_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_customdata`
#

CREATE TABLE phpbb_pa_customdata (
  customdata_file int(50) NOT NULL default '0',
  customdata_custom int(50) NOT NULL default '0',
  data text NOT NULL
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_download_info`
#

CREATE TABLE phpbb_pa_download_info (
  file_id mediumint(8) NOT NULL default '0',
  user_id mediumint(8) NOT NULL default '0',
  downloader_ip varchar(8) NOT NULL default '',
  downloader_os varchar(255) NOT NULL default '',
  downloader_browser varchar(255) NOT NULL default '',
  browser_version varchar(255) NOT NULL default ''
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_files`
#

CREATE TABLE phpbb_pa_files (
  file_id int(10) NOT NULL auto_increment,
  user_id mediumint(8) NOT NULL default '0',
  poster_ip varchar(8) NOT NULL default '',
  file_name text,
  file_size int(20) NOT NULL default '0',
  unique_name varchar(255) NOT NULL default '',
  real_name VARCHAR(255) NOT NULL,
  file_dir VARCHAR(255) NOT NULL,
  file_desc text,
  file_creator text,
  file_version text,
  file_longdesc text,
  file_ssurl text,
  file_sshot_link tinyint(2) NOT NULL default '0',
  file_dlurl text,
  file_time int(50) default NULL,
  file_update_time int(50) NOT NULL default '0',
  file_catid int(10) default NULL,
  file_posticon text,
  file_license int(10) default NULL,
  file_dls int(10) default NULL,
  file_last int(50) default NULL,
  file_pin int(2) default NULL,
  file_docsurl text,
  file_approved TINYINT(1) DEFAULT '1' NOT NULL,
  file_broken TINYINT(1) DEFAULT '0' NOT NULL,
  PRIMARY KEY  (file_id)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `phpbb_pa_license`
#

CREATE TABLE phpbb_pa_license (
  license_id int(10) NOT NULL auto_increment,
  license_name text,
  license_text text,
  PRIMARY KEY  (license_id)
) TYPE=MyISAM;
# --------------------------------------------------------


CREATE TABLE phpbb_pa_mirrors (
  mirror_id mediumint(8) NOT NULL auto_increment, 
  file_id int(10) NOT NULL,
  unique_name varchar(255) NOT NULL default '',
  file_dir VARCHAR(255) NOT NULL, 
  file_dlurl varchar(255) NOT NULL default '',
  mirror_location VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  (mirror_id),
  KEY file_id (file_id)
) TYPE=MyISAM;

#
# Table structure for table `phpbb_pa_votes`
#

CREATE TABLE phpbb_pa_votes (
  user_id mediumint(8) NOT NULL default '0',
  votes_ip varchar(50) NOT NULL default '0',
  votes_file int(50) NOT NULL default '0',
  rate_point tinyint(3) unsigned NOT NULL default '0',
  voter_os varchar(255) NOT NULL default '',
  voter_browser varchar(255) NOT NULL default '',
  browser_version varchar(8) NOT NULL default '',
  KEY user_id (user_id)
) TYPE=MyISAM;


INSERT INTO phpbb_pa_config VALUES ('allow_comment_images', '0');
INSERT INTO phpbb_pa_config VALUES ('no_comment_image_message', '[No image please]');
INSERT INTO phpbb_pa_config VALUES ('allow_smilies', '1');
INSERT INTO phpbb_pa_config VALUES ('allow_comment_links', '1');
INSERT INTO phpbb_pa_config VALUES ('no_comment_link_message', '[No links please]');
INSERT INTO phpbb_pa_config VALUES ('settings_disable', '0');
INSERT INTO phpbb_pa_config VALUES ('allow_html', '1');
INSERT INTO phpbb_pa_config VALUES ('allow_bbcode', '1');
INSERT INTO phpbb_pa_config VALUES ('settings_topnumber', '10');
INSERT INTO phpbb_pa_config VALUES ('settings_newdays', '1');
INSERT INTO phpbb_pa_config VALUES ('settings_stats', '');
INSERT INTO phpbb_pa_config VALUES ('settings_viewall', '1');
INSERT INTO phpbb_pa_config VALUES ('settings_dbname', 'Download Database');
INSERT INTO phpbb_pa_config VALUES ('settings_dbdescription', '');
INSERT INTO phpbb_pa_config VALUES ('max_comment_chars', '5000');
INSERT INTO phpbb_pa_config VALUES ('tpl_php', '0');
INSERT INTO phpbb_pa_config VALUES ('settings_file_page', '20');
INSERT INTO phpbb_pa_config VALUES ('hotlink_prevent', '1');
INSERT INTO phpbb_pa_config VALUES ('hotlink_allowed', '');
INSERT INTO phpbb_pa_config VALUES ('sort_method', 'file_time');
INSERT INTO phpbb_pa_config VALUES ('sort_order', 'DESC');
INSERT INTO phpbb_pa_config VALUES ('need_validation', '0');
INSERT INTO phpbb_pa_config VALUES ('validator', 'validator_admin');
INSERT INTO phpbb_pa_config VALUES ('pm_notify', '0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_search','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_stats','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_toplist','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_viewall','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('max_file_size','262144');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('upload_dir','pafiledb/uploads/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('screenshots_dir','pafiledb/images/screenshots/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('forbidden_extensions','php, php3, php4, phtml, pl, asp, aspx, cgi');

