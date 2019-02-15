ALTER TABLE phpbb_pa_cat 
ADD auth_edit_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_view_file,
ADD auth_delete_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_edit_file,
ADD cat_allow_ratings tinyint(2) NOT NULL default '1' AFTER cat_allow_file,
ADD cat_allow_comments tinyint(2) NOT NULL default '1' AFTER cat_allow_ratings;

ALTER TABLE phpbb_pa_auth 
ADD auth_edit_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_view_file,
ADD auth_delete_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_edit_file;

INSERT INTO phpbb_pa_config VALUES ('need_validation', '0');
INSERT INTO phpbb_pa_config VALUES ('validator', 'validator_admin');
INSERT INTO phpbb_pa_config VALUES ('pm_notify', '0');