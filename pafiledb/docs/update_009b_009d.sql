CREATE TABLE phpbb_pa_auth (
   group_id mediumint(8) DEFAULT '0' NOT NULL,
   cat_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   auth_view tinyint(1) DEFAULT '0' NOT NULL,
   auth_read tinyint(1) DEFAULT '0' NOT NULL,
   auth_view_file tinyint(1) DEFAULT '0' NOT NULL,
   auth_upload tinyint(1) DEFAULT '0' NOT NULL,
   auth_download tinyint(1) DEFAULT '0' NOT NULL,
   auth_rate tinyint(1) DEFAULT '0' NOT NULL,
   auth_email tinyint(1) DEFAULT '0' NOT NULL,
   auth_view_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_post_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete_comment tinyint(1) DEFAULT '0' NOT NULL,
   auth_mod tinyint(1) DEFAULT '1' NOT NULL,
   auth_search tinyint(1) DEFAULT '1' NOT NULL,
   auth_stats tinyint(1) DEFAULT '1' NOT NULL,
   auth_toplist tinyint(1) DEFAULT '1' NOT NULL,
   auth_viewall tinyint(1) DEFAULT '1' NOT NULL,
   KEY group_id (group_id),
   KEY cat_id (cat_id)
);


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

ALTER TABLE phpbb_pa_cat 
ADD cat_files MEDIUMINT(8) DEFAULT '-1' NOT NULL AFTER cat_allow_file,
ADD cat_last_file_id MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL AFTER cat_files, 
ADD cat_last_file_name VARCHAR(255) NOT NULL AFTER cat_last_file_id,
ADD cat_last_file_time INT(50) UNSIGNED DEFAULT '0' NOT NULL AFTER cat_last_file_name; 

ALTER TABLE phpbb_pa_files 
ADD file_size INT(20) NOT NULL AFTER file_name,
ADD unique_name VARCHAR(255) NOT NULL AFTER file_size,
ADD real_name VARCHAR(255) NOT NULL AFTER unique_name,
ADD file_dir VARCHAR(255) NOT NULL AFTER real_name,
ADD file_broken TINYINT(1) DEFAULT '0' NOT NULL; 

INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_search','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_stats','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_toplist','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_viewall','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('max_file_size','262144');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('upload_dir','pafiledb/uploads/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('screenshots_dir','pafiledb/images/screenshots/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('forbidden_extensions','php, php3, php4, phtml, pl, asp, aspx, cgi');