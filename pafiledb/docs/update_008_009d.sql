CREATE TABLE phpbb_pa_download_info ( 
    file_id MEDIUMINT(8) DEFAULT '0' NOT NULL, 
    user_id MEDIUMINT(8) DEFAULT '0' NOT NULL, 
    downloader_ip VARCHAR(8) NOT NULL, 
    downloader_os VARCHAR(8) NOT NULL, 
    downloader_browser VARCHAR(8) NOT NULL, 
    browser_version  VARCHAR(8) NOT NULL 
);

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
  real_name VARCHAR(255) NOT NULL default '',
  file_dir VARCHAR(255) NOT NULL, 
  file_dlurl varchar(255) NOT NULL default '',
  mirror_location VARCHAR(255) NOT NULL default '',
  PRIMARY KEY  (mirror_id),
  KEY file_id (file_id)
) TYPE=MyISAM;


CREATE TABLE phpbb_pa_config (
    config_name varchar(255) NOT NULL,
    config_value varchar(255) NOT NULL,
    PRIMARY KEY (config_name)
);


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

INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_search','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_stats','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_toplist','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('auth_viewall','0');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('max_file_size','262144');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('upload_dir','pafiledb/uploads/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('screenshots_dir','pafiledb/images/screenshots/');
INSERT INTO phpbb_pa_config (config_name, config_value) VALUES ('forbidden_extensions','php, php3, php4, phtml, pl, asp, aspx, cgi');


ALTER TABLE phpbb_pa_cat 
DROP cat_files.
ADD parents_data TEXT NOT NULL AFTER cat_parent,
ADD cat_allow_file TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_view TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_read TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_view_file TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_upload TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_download TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_rate TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_email TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_view_comment TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_post_comment TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_edit_comment TINYINT(2) DEFAULT '0' NOT NULL, 
ADD auth_delete_comment TINYINT(2) DEFAULT '0' NOT NULL,
ADD cat_files MEDIUMINT(8) NOT NULL default '-1' AFTER cat_allow_file,
ADD cat_last_file_id MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL AFTER cat_files, 
ADD cat_last_file_name VARCHAR(255) NOT NULL AFTER cat_last_file_id,
ADD cat_last_file_time INT(50) UNSIGNED DEFAULT '0' NOT NULL AFTER cat_last_file_name, 
DROP cat_1xid;

ALTER TABLE phpbb_pa_files 
ADD user_id MEDIUMINT(8) DEFAULT '0' NOT NULL AFTER file_id,
ADD poster_ip VARCHAR(8) NOT NULL AFTER user_id,
ADD file_sshot_link TINYINT(2) DEFAULT '0' NOT NULL AFTER file_ssurl, 
ADD file_update_time INT(50) NOT NULL AFTER file_time,
ADD file_approved TINYINT(2) DEFAULT '0' NOT NULL,
ADD file_broken TINYINT(1) DEFAULT '0' NOT NULL,
ADD file_size INT(20) NOT NULL AFTER file_name,
ADD unique_name VARCHAR(255) NOT NULL AFTER file_size,
ADD real_name VARCHAR(255) NOT NULL AFTER unique_name
ADD file_dir VARCHAR(255) NOT NULL AFTER real_name,
DROP file_rating,
DROP file_totalvotes;

ALTER TABLE phpbb_pa_votes 
ADD user_id MEDIUMINT(8) DEFAULT '0' NOT NULL FIRST,
ADD rate_point tinyint(3) UNSIGNED NOT NULL AFTER votes_file,
ADD voter_os VARCHAR(255) NOT NULL, 
ADD voter_browser VARCHAR(255) NOT NULL,
ADD browser_version VARCHAR(8) NOT NULL;


ALTER TABLE phpbb_pa_custom 
ADD data text NOT NULL,
ADD regex VARCHAR(255) NOT NULL,
ADD field_order INT(20) NOT NULL, 
ADD field_type TINYINT(2) NOT NULL; 


DROP TABLE phpbb_pa_settings;

UPDATE phpbb_pa_files SET file_approved = 1, user_id = 2;
UPDATE phpbb_pa_cat SET cat_allow_file = 1;
DELETE FROM phpbb_pa_votes WHERE rate_point = '' OR rate_point = 0;

