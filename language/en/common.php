<?php
/**
*
* @package phpBB Extension - Download Manager
* @copyright (c) 2016 orynider - http://mxpcms.sourceforge.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	//
	// General
	//
	'Category'							=>	'Category',
	'Error_no_download'					=>	'The selected File does not exist anymore',
	'Options'							=>	'Options',
	'Click_return'						=>	'Click %sHere%s to return to the previous page',
	'Click_here'						=>	'Click Here',
	'never'								=>	'None',
	'pafiledb_disable'					=>	'Download Database is disabled',
	'jump'								=>	'Select a category',
	'viewall_disabled'					=>	'This feature is disabled by the admin.',
	'New_file'							=>	'New file',
	'No_new_file'						=>	'No new file',
	'None'								=>	'None',
	'No_file'							=>	'No Files',
	'View_latest_file'					=>	'View Latest File',

	//
	// Return
	//
	'Click_return'						=>	'Click %sHere%s to return to the previous page',

	//
	// Main
	//
	'Files'									=>	'Files',
	'Viewall'								=>	'View All Files',
	'Vainfo'								=>	'View all of the files in the database',
	'Quick_nav'								=>	'Quick Navigation',
	'Quick_jump'							=>	'Select Category',
	'Quick_go'								=>	'Go',
	'Sub_category'							=>	'Sub Category',
	'Last_file'								=>	'Last File',

	//
	// Sort
	//
	'Sort'								=>	'Sort',
	'Name'								=>	'Name',
	'Update_time'						=>	'Last Updated',

	//
	// Category
	//
	'No_files'							=>	'No files found',
	'No_files_cat'						=>	'There are no files in this category.',
	'Cat_not_exist'						=>	'The category you selected does not exist.',
	'File_not_exist'					=>	'The file you selected does not exist.',
	'License_not_exist'					=>	'The license you selected does not exist.',

	//
	// File
	//
	'FILE_TITLE'							=> 'Filename',
	'FILE_DESC'								=> 'Description',
	'FILE_CREATOR' 							=> 'Creator',
	'FILE_SUBMITER' 						=> 'Submited by',
	'FILE_VERSION'							=> 'Version',
	'FILE_SCRSHT' 							=> 'Screenshot',
	'DOCS' 									=> 'Documentation/Manual',
	'FILE_LASTDL' 							=> 'Last Download',
	'FILE_LASTDL_NEVER' 					=> 'Never',
	'FILE_VOTES' 							=> ' Votes',
	'FILE_DATE' 							=> 'Date',
	'FILE_UPDATE_TIME' 						=> 'Last Updated',
	'FILE_RATING' 							=> 'Rating',
	'FILE_CLICKS'							=> 'Total Downloads',	
	'FILE_DLS' 								=> 'Unique Downloads',
	'FILE_DOWNLOAD' 						=> 'Download File',
	'FILE_SIZE' 							=> 'File Size',
	'FILE_NOT_AVAILABLE' 					=> 'Not Available!',
	'FILE_SIZE_BYTES' 						=> 'Bytes',
	'FILE_SIZE_KB' 							=> 'Kilo Byte',
	'FILE_SIZE_MB' 							=> 'Mega Byte',
	'FILE_MIRRORS' 							=> 'Mirrors',
	'FILE_MIRRORS_EXPLAIN' 					=> 'Here you can add or edit mirrors for this file, make sure to verify all the information because the file will be submitted to the database',
	'FILE_CLICK_HERE_MIRRORS' 				=> 'Click Here to Add mirrors',
	'FILE_MIRROR_LOCATION' 					=> 'Mirror Location',
	'FILE_ADD_NEW_MIRROR' 					=> 'Add new mirror',
	'FILE_SAVE_AS' 							=> 'Save As...',	
	'FILES_BACK_INDEX'						=> 'Back to index',
	'FILES_BACK_LINK'						=> 'Click %shere%s to return to the download index',
	'FILES_CATS_NAME'						=> 'Categories',
	'FILES_CAT_DESC'						=> 'Description',
	'FILES_CAT_NAME'						=> 'Category',
	'FILES_COST'							=> 'Costs',
	'FILES_COST_ERROR'						=> 'You need more %1$s in order to download this file',
	'FILES_COST_FREE'						=> 'This download is for free',
	'FILES_COST_OK'							=> 'You have enough %1$s to download this file',
	'FILES_DISABLED'						=> 'The Download Manager is currently deactivated. Please try again later.<br />If you later on still encounter the same, please inform the admin.',
	'FILES_DL_NOEXISTS'						=> 'This download does not exist',
	'FILES_DOWNLOAD'						=> 'Download',
	'FILES_DOWNLOADS'						=> 'Downloads',
	'FILES_DOWNLOAD_EXPLAIN'				=> 'Click the icon on the right to download the desired file.',
	'FILES_FREE'							=> 'Free',
	'FILES_INDEX'							=> 'Index',
	'FILES_LAST_CHANGED_ON'					=> 'Last changed on',
	'FILES_LAST_DOWNLOAD'					=> '&nbsp;<strong>%1$s</strong><br /><br />&nbsp,Downloaded: %2$s<br />&nbsp,Last changed on: %3$s',
	'FILES_LAST_FILE'						=> 'Newest file',
	'FILES_LEGEND'							=> 'Legend',
	'FILES_LEGEND_ERROR'					=> 'You need more %1$s',
	'FILES_LEGEND_FREE'						=> 'Download is free',
	'FILES_LEGEND_NO_DL'					=> 'You are not allowed to download files',
	'FILES_LEGEND_OK'						=> 'You have enough %1$s',
	'FILES_MULTI'							=> '%1$s Downloads',
	'FILES_NO_CAT'							=> '<strong>Sorry! There are currently no categories available.</strong><br /><br />',
	'FILES_NO_CAT_IN_UPLOAD'				=> 'Sorry! There are currently no categories available.',
	'FILES_NO_DOWNLOADS'					=> '<strong>Sorry! There are currently no downloads available.</strong><br /><br />',
	'FILES_NO_FILES'						=> 'There are no downloads',
	'FILES_NO_ID'							=> 'No id given',
	'FILES_NUMBER_DOWNLOADS'				=> 'Files',
	'FILES_REGULAR_DOWNLOAD'				=> 'Click here to download the selected file',
	'FILES_REQUIRES_POINTS'					=> '<strong>As we have the Ultimate Points Mod installed and you require points for this download,<br />you need to be logged in, before you can download this file!</strong>',
	'FILES_SINGLE'							=> '1 Download',
	'FILES_SUB_CAT'							=> 'Sub category',
	'FILES_SUB_CATS'						=> 'Sub categories',
	'FILES_TITLE'							=> 'Downloads',
	'FILES_TITLE_EXPLAIN'					=> 'Select a category below',
	'FILES_UPLOADED_ON'						=> 'Uploaded on',
	'FILES_UPLOAD'							=> 'Upload',
	'FILES_UPLOADS'							=> 'Download system upload section',
	'FILES_UPLOAD_SECTION'					=> 'Upload section',
	'FILES_UPLOAD_MESSAGE'					=> 'Upload here your file in correct category.',
	'FILES_FILESIZE'						=> 'Filesize',
	'FILES_CAT_NOT_EXIST'					=> 'The selected category does not exist!',
	'FILES_BACK_DOWNLOADS'					=> 'Back to download overview',
	'FILES_NO_PERMISSION'					=> 'You don\'t have the permission to use the Download Manager',
	'FILES_NO_DOWNLOAD'						=> 'You don\'t have the permission to download files from the Download Manager',
	'FILES_NO_UPLOAD'						=> 'You don\'t have the permission to use the upload section',
	'FILES_NO_DIRECT_DL'					=> 'You are not allowed to download files',
	'FILES_CAT'								=> '%d category',
	'FILES_CATS'							=> '%d categories',
	'FILES_SUB_CATEGORY'					=> 'and %d subcategory',
	'FILES_SUB_CATEGORIES'					=> 'and %d subcategories',
	'FILES_CURRENT_VERSION'					=> 'Current Version',
	'FILES_NEW_TITLE'						=> 'Title',
	'FILES_NEW_TITLE_EXPLAIN'				=> 'Title for your new download.',
	'FILES_NEW_VERSION'						=> 'Version',
	'FILES_NEW_VERSION_EXPLAIN'				=> 'Version of your download.',
	'FILES_NEW_DESC'						=> 'Description',
	'FILES_NEW_DESC_EXPLAIN'				=> 'BBcode & Smilies allowed.',
	'FILES_NEW_DL_CAT'						=> 'Category',
	'FILES_NEW_DL_CAT_EXPLAIN'				=> 'Select here the category.',
	'FILES_NEW_DOWNLOAD'					=> 'New download',
	'FILES_NEW_FILENAME'					=> 'File name',
	'FILES_NEW_FILENAME_EXPLAIN'			=> 'Select the file to upload.',
	'FILES_NEW_DOWNLOAD_SIZE'				=> 'The maximum size of the file is <strong>%1$s %2$s</strong>! Due to the upload time you might need, this value can be lower!',
	'FILES_SUBCAT_FILE'						=> '1 file',
	'FILES_SUBCAT_FILES'					=> '%1$d files',
	
	//
	// Admin Panels - File
	//
	'File_manage_title'			=> 'File Management',

	'Afile'						=> 'File: Add',
	'Efile'						=> 'File: Edit',
	'Dfile'						=> 'File: Delete',
	'Afiletitle'				=> 'Add File',
	'Efiletitle'				=> 'Edit File',
	'Dfiletitle'				=> 'Delete File',
	'Fileexplain'				=> 'You can use the file management section to add, edit, and delete files.',
	'Upload'					=> 'Upload File',
	'Uploadinfo'				=> 'Upload this file',
	'Uploaderror'				=> 'This file already exists. Please rename the file and try again.',
	'Uploaddone'				=> 'This file has been successfully uploaded. The URL to the file is',
	'Uploaddone2'				=> 'Click Here to place this URL in the Download URL field.',
	'Upload_do_done'			=> 'Uploaded Sucessfully',
	'Upload_do_not'				=> 'Not Uploaded',
	'Upload_do_exist'			=> 'File Exist',
	'Filename'					=> 'File Name',
	'Filenameinfo'				=> 'This is the name of the file you are adding, such as \'My Picture.\'',
	'Filesd'					=> 'Short Description',
	'Filesdinfo'				=> 'This is a short description of the file. This will go on the page that lists all the files in a category, so this description should be short',
	'Fileld'					=> 'Long Description',
	'Fileldinfo'				=> 'This is a longer description of the file. This will go on the file\'s information page so this description can be longer',
	'Filecreator'				=> 'Creator/Author',
	'Filecreatorinfo'			=> 'This is the name of whoever created the file.',
	'Fileversion'				=> 'File Version',
	'Fileversioninfo'			=> 'This is the version of the file, such as 3.0 or 1.3 Beta',
	'Filess'					=> 'Screenshot URL',
	'Filessinfo'				=> 'This is a URL to a screenshot of the file. For example, if you are adding a Winamp skin, this would be a URL to a screenshot of Winamp with this skin. You can manually enter a URL or you can leave it blank and upload a screen shot using "Browse" above.',
	'Filess_upload'				=> 'Upload Screenshot',
	'Filessinfo_upload'			=> 'You can upload a screenshot by clicking on "Browse"',
	'Filess_link'				=> 'Screenshot as a Link',
	'Filess_link_info'			=> 'If you want to show the screenshot as a link, choose "yes".',
	'Filedocs'					=> 'Documentation/Manual URL',
	'Filedocsinfo'				=> 'This is a URL to the documentation or a manual for the file',
	'Fileurl'					=> 'File URL',
	'Fileurlinfo'				=> 'This is a URL to the file that will be downloaded. You can type it in manually or you can click on "Browse" above and upload a file.',
	'File_upload'				=> 'File Upload',
	'Fileinfo_upload'			=> 'You can upload a file by clicking on "Browse"',
	'Uploaded_file'				=> 'Uploaded file',
	'Filepi'					=> 'Post Icon',
	'Filepiinfo'				=> 'You can choose a post icon for the file. The post icon will be shown next to the file in the list of files.',
	'Filecat'					=> 'Category',
	'Filecatinfo'				=> 'This is the category the file belongs in.',
	'Filelicense'				=> 'License',
	'Filelicenseinfo'			=> 'This is the license agreement the user must agree to before downloading the file.',
	'Filepin'					=> 'Pin File',
	'Filepininfo'				=> 'Choose if you want the file pinned or not. Pinned files will always be shown at the top of the file list.',
	'Filedisable'				=> 'Disable file download',
	'Filedisableinfo'			=> 'This setting makes the file disabled, but still visible. A pop-up message informs the user this file is not available at the moment.',
	'Filedisablemsg'			=> 'Disable message',
	'Filedisablemsginfo'		=> 'The pop-up message...',
	'Fileadded'					=> 'The new file has been successfully added',
	'Filedeleted'				=> 'The file has been successfully deleted',
	'Fileedited'				=> 'The file you selected has been successfully edited',
	'Fderror'					=> 'You didn\'t select any files to delete',
	'Filesdeleted'				=> 'The files you selected have been successfully deleted',
	'Filetoobig'				=> 'That file is too big!',
	'Approved'					=> 'Approved',
	'Not_approved'				=> '(Not Approved)',
	'Approved_info'				=> 'Use this option to make the file available for users, and also to approve a file that has been uploaded by the users.',

	'Filedls'					=> 'Download Total',
	'Addtional_field'			=> 'Additional Field',
	'File_not_found'			=> 'The file you specified cannot be found',
	'SS_not_found'				=> 'The screenshot you specified cannot be found',

	//
	// MCP
	//
	'MCP_title'					=> 'Moderator Control Panel',
	'MCP_title_explain'			=> 'Here moderators can approve and manage files',

	'View'						=> 'View',

	'Approve_selected'			=> 'Approve Selected',
	'Unapprove_selected'		=> 'Unapprove Selected',
	'Delete_selected'			=> 'Delete Selected',
	'No_item'					=> 'There is no files',

	'All_items'					=> 'All Files',
	'Approved_items'			=> 'Approved Files',
	'Unapproved_items'			=> 'Unapproved Files',
	'Broken_items'				=> 'Broken Files',
	'Item_cat'					=> 'File in Category',
	'Approve'					=> 'Approve',
	'Unapprove'					=> 'Unapprove',

	'Sorry_auth_delete'			=> 'Sorry, but you cannot delete files in this category.',
	'Sorry_auth_mcp'			=> 'Sorry, but you cannot moderate this category.',
	'Sorry_auth_approve'		=> 'Sorry, but you cannot approve files in this category.',	
	
	//
	// User Upload
	//
	'User_upload'					=> 'User Upload',

	//
	// License
	//
	'License'						=> 'License Agreement',
	'Licensewarn'					=> 'You must agree to this license agreement to download',
	'Iagree'						=> 'I Agree',
	'Dontagree'						=> 'I Dont Agree',

	//
	// Search
	//
	'Search'						=> 'Search',
	'Search_for'					=> 'Search for',
	'Results'						=> 'Results for',
	'No_matches'					=> 'Sorry, no matches were found for',
	'Matches'						=> 'matches were found for',
	'All'							=> 'All Categories',
	'Choose_cat'					=> 'Choose Category:',
	'Include_comments'				=> 'Include Comments',
	'Submiter'						=> 'Submited by',	
	
	//
	// ACP
	//	
	'ACP_ADD'							=> 'Add',
	'ACP_ALL_DOWNLOADS'					=> 'All downloads',
	'ACP_ANNOUNCE_ENABLE'				=> 'Announce new downloads',
	'ACP_ANNOUNCE_ENABLE_EXPLAIN'		=> 'Set to Yes, if you like to announce new Downloads in a certain forum.',
	'ACP_ANNOUNCE_LOCK'					=> 'Lock announcement',
	'ACP_ANNOUNCE_LOCK_EXPLAIN'			=> 'Set to Yes, the topic will be locked.',
	'ACP_ANNOUNCE_ID'					=> 'Announcement forum',
	'ACP_ANNOUNCE_ID_EXPLAIN'			=> 'Enter here the ID of the forum, where you like to announce new downloads.',
	'ACP_ANNOUNCE_MSG'					=> 'Hello,

we have a new download!

[b]Title:[/b] %1$s
[b]Description:[/b] %2$s
[b]Category:[/b] %3$s
[b]Click %4$s to go to the download page![/b]

Have fun!',
	'ACP_ANNOUNCE_SETTINGS'				=> 'Announcement settings',
	'ACP_ANNOUNCE_TITLE'				=> '%1$s',
	'ACP_CAT_NAME_SHOW_YES'				=> 'yes',
	'ACP_CAT_NAME_SHOW_NO'				=> 'no',
	'ACP_NEW_CAT_NAME_SHOW'				=> 'Show on index upload',
	'ACP_NEW_CAT_NAME_SHOW_EXPLAIN'		=> 'Show category on upload section for groups that allowed to upload.<br /><strong>Note:</strong> Admins can always see all categories in upload section.',
	'ACP_ANNOUNCE_UP'					=> 'Announce download again',
	'ACP_ANNOUNCE_UP_EXPLAIN'			=> 'Activate, if you like to re-announce the download. The message will be sent as an update information',
	'ACP_ANNOUNCE_UP_MSG'				=> 'Hello,

we have an updated download!

[b]Title:[/b] %1$

[b]Description:[/b] %2$s

[b]Category:[/b] %3$s

[b]Click %4$s to go to the category![/b]

Have fun!',
	'ACP_ANNOUNCE_UP_TITLE'				=> '[UPD] %1$s',
	'ACP_BASIC'							=> 'Basic settings',
	'ACP_CAT'							=> 'Category',
	'ACP_CATEGORIES'					=> 'Categories',
	'ACP_CAT_DELETE'					=> 'Delete category',
	'ACP_CAT_DELETE_DONE'				=> 'Your category was successfully deleted',
	'ACP_CAT_DELETE_EXPLAIN'			=> 'Here you can delete a category.',
	'ACP_CAT_EDIT_DONE'					=> 'Your category was successfully updated',
	'ACP_CAT_EXIST'						=> 'The folder name already exists on your webspace!',
	'ACP_CAT_EXPLAIN'					=> 'Enter here the category, where your download should be listed in',
	'ACP_CAT_INDEX'						=> 'Categories Index',
	'ACP_CAT_NAME_ERROR'				=> 'You need to enter a folder name for your category!',
	'ACP_CAT_NEW'						=> 'Add a new category',
	'ACP_CAT_NEW_DONE'					=> 'Your new category was added and the folder created successfully on your webspace!',
	'ACP_CAT_NEW_EXPLAIN'				=> 'Here you can add a new category.',
	'ACP_CAT_NOT_EXIST'					=> 'The requested category does not exist!',
	'ACP_CAT_SELECT'					=> 'Here you can add, edit or delete categories.',
	'ACP_CLICK'							=> 'here',
	'ACP_CONFIG_SUCCESS'				=> 'The configuration was successfully updated',
	'ACP_COPY_NEW'						=> 'Copy as draft',
	'ACP_COST_ERROR'					=> 'You can\'t set negative costs for a download!<br />Enter 0 to make it free or any positive value.',
	'ACP_COST_EXPLAIN'					=> 'Here you can set, how much %1$s the users have to pay for this download. Set 0, to leave the download for free.',
	'ACP_COST_FREE'						=> 'Free',
	'ACP_COST_SHORT'					=> 'Costs',
	'ACP_DELETE_HAS_FILES'				=> 'There are still files in the category!<br />Please delete them or move them to another category first!',
	'ACP_DELETE_SUB_CATS'				=> 'Please delete first your sub categories!',
	'ACP_DEL_CAT'						=> 'Are you sure, you want to delete the category <strong>%1$s</strong>?<br />The physical folder on your web server - if there are no more downloads inside - will be removed too!',
	'ACP_DEL_CAT_EXPLAIN'				=> 'Here you can delete an existing category.',
	'ACP_DEL_DOWNLOAD'					=> 'Delete a download',
	'ACP_DEL_DOWNLOADS_TO'				=> 'Move downloads to',
	'ACP_DEL_DOWNLOAD_YES'				=> 'Delete category including the downloads?',
	'ACP_DEL_SUBS'						=> 'Delete sub-categories',
	'ACP_DEL_SUBS_TO'					=> 'Move sub-categories to',
	'ACP_DEL_SUBS_YES'					=> 'Delete category including the sub-categories?',
	'ACP_DOWNLOADS'						=> 'Downloads',
	'ACP_DOWNLOAD_DELETED'				=> 'Your download was successfully deleted.',
	'ACP_DOWNLOAD_UPDATED'				=> 'Your download was successfully updated',
	'ACP_DOWNLOAD_SYSTEM'				=> 'Download system',
	'ACP_EDIT_CAT'						=> 'Edit category',
	'ACP_EDIT_CAT_EXPLAIN'				=> 'Here you can edit an existing category.',
	'ACP_EDIT_DOWNLOADS'				=> 'Edit downloads',
	'ACP_EDIT_DOWNLOADS_EXPLAIN'		=> 'Here you can edit the selected download.',
	'ACP_EDIT_FILENAME'					=> 'Saved File',
	'ACP_EDIT_FILENAME_EXPLAIN'			=> '<strong>IMPORTANT:</strong> If you change the file name over here, there will be no further check, if the file really exists on your webspace. <strong>You need to upload the new file	via FTP and manually delete the old one!</strong>',
	'ACP_EDIT_SUB_CAT_EXPLAIN'			=> 'The already created subdirectory can\'t be edited. So if you like to have a different subdirectory, you need to delete the current category and create a new one!',
	'ACP_FILE_TOO_BIG'					=> 'The file is bigger, than your host allows!',
	'ACP_FORUM_ID_ERROR'				=> 'The entered forum ID does not exist!',
	'ACP_FILES_INDEX'					=> 'Download Manager',
	'ACP_MANAGE_DOWNLOADS_EXPLAIN'		=> 'Here you can add, edit or delete your downloads.',
	'ACP_MULTI_DOWNLOAD'				=> '%d downloads',
	'ACP_NEED_DATA'						=> 'You need to fill all fields!',
	'ACP_NEW_ADDED'						=> 'Your entry was successfully added to the database',
	'ACP_NEW_CAT'						=> 'New category',
	'ACP_NEW_CAT_DESC'					=> 'Description of the category',
	'ACP_NEW_CAT_DESC_EXPLAIN'			=> 'Enter a useful description of your new category.<br />BB-Codes, smiles and links will be recognised automatically.',
	'ACP_NEW_CAT_NAME'					=> 'Category name',
	'ACP_NEW_CAT_PARENT'				=> 'Parent category',
	'ACP_NEW_COPY_DOWNLOAD'				=> 'New download with copy',
	'ACP_NEW_COPY_DOWNLOAD_EXPLAIN'		=> 'You selected to copy an already existing download for your new download. This will save a little time, especially if you like to upload ie a new version',
	'ACP_NEW_DESC'						=> 'Description',
	'ACP_NEW_DESC_EXPLAIN'				=> 'Enter here a description for your download.',
	'ACP_NEW_DL_CAT'					=> 'Category',
	'ACP_NEW_DL_CAT_EXPLAIN'			=> 'Select here the category, where your download should stay in.',
	'ACP_NEW_DOWNLOAD'					=> 'New download',
	'ACP_NEW_DOWNLOAD_EXPLAIN'			=> 'Here you can add new downloads.',
	'ACP_NEW_DOWNLOAD_SIZE'				=> 'The maximum size of the file, which is allowed by your host, is <strong>%1$s %2$s</strong>! Due to the upload time you might need, this value can be lower!',
	'ACP_NEW_FILENAME'					=> 'File name',
	'ACP_NEW_FILENAME_EXPLAIN'			=> 'Select the file to upload.',
	'ACP_NEW_SUB_CAT_EXPLAIN'			=> 'Enter here the folder name, which you like to use on your webspace for this category (without slashes!).<br />This folder will then be created automatically under your root/ext/orynider/pafiledb/uploads/ folder.<br />Allowed characters are a-z, A-Z, 0-9, the hyphen ( - ) and the underscore ( _ ) signs.',
	'ACP_NEW_SUB_CAT_NAME'				=> 'Path name for the category',
	'ACP_NEW_TITLE'						=> 'Title',
	'ACP_NEW_TITLE_EXPLAIN'				=> 'Enter here the title for your new download.',
	'ACP_NEW_VERSION'					=> 'Version',
	'ACP_NEW_VERSION_EXPLAIN'			=> 'Enter here the version of your download.',
	'ACP_NO_CAT'						=> 'There are no categories available!<br />You first need to create at least one category, before you can start to add downloads!',
	'ACP_NO_CAT_ID'						=> 'No Cat ID',
	'ACP_NO_CAT_PARENT'					=> 'no parent category',
	'ACP_NO_CAT_UPLOAD'					=> 'There are no categories available!<br />You first need to create at least one category, before you can start adding files!',
	'ACP_NO_DOWNLOADS'					=> 'No Downloads',
	'ACP_NO_FILENAME'					=> 'You have to enter a file, which belongs to your upload!',
	'ACP_PAGINATION_ACP'				=> 'Set number of pagination on Manage Downloads page in ACP',
	'ACP_PAGINATION_ACP_EXPLAIN'		=> 'Set here, how much entries you want to see on the Manage Downloads page in ACP. <em>Default is 5.</em>',
	'ACP_PAGINATION_DOWNLOADS'			=> 'Set number of pagination on category page',
	'ACP_PAGINATION_DOWNLOADS_EXPLAIN'	=> 'Set here, how much entries you want to see on the category page. <em>Default is 25.</em>',
	'ACP_PAGINATION_ERROR_ACP'			=> 'You cannot set a value smaller than 5!',
	'ACP_PAGINATION_ERROR_USER'			=> 'You cannot set a value smaller than 3!',
	'ACP_PAGINATION_ERROR_DOWNLOADS'	=> 'You cannot set a value smaller than 10!',
	'ACP_PAGINATION_USER'				=> 'Set number of pagination on downloads page',
	'ACP_PAGINATION_USER_EXPLAIN'		=> 'Set here, how much entries you want to see on the downloads page. <em>Default is 3.</em>',
	'ACP_PARENT_OPTION_NAME'			=> 'Select a category',
	'ACP_REALLY_DELETE'					=> 'Are you sure, you want to delete your download?<br />The physical file on your web server will be deleted too!',
	'ACP_SINGLE_DOWNLOAD'				=> '1 download',
	'ACP_SORT_ASC'						=> 'Ascending',
	'ACP_SORT_CAT'						=> 'Category',
	'ACP_SORT_DESC'						=> 'Descending',
	'ACP_SORT_DIRECTION'				=> 'sort direction',
	'ACP_SORT_KEYS'						=> 'sort by ',
	'ACP_SORT_TITLE'					=> 'Title',
	'ACP_SUB_DL_CAT'					=> 'Subcategory',
	'ACP_SUB_NO_CAT'					=> '-----------',
	'ACP_SUB_DL_CAT_EXPLAIN'			=> 'Select here the subcategory.',
	'ACP_SUB_HAS_CAT_EXPLAIN'			=> 'This category has subcategories so cannot be linked to other category.',
	'ACP_UPLOAD_FILE_EXISTS'			=> 'The file you like to upload, does already exist in this category!',
	'ACP_WRONG_CHAR'					=> 'You entered a wrong character in the path name for the category!<br />Following characters are allowed: a-z, A-Z, 0-9, as well the hyphen ( - ) and the underscore ( _ )!',
	'ACP_MANAGE_CONFIG_EXPLAIN'			=> 'Here you can set a few basic values.',
	'ACP_SET_USERNAME'					=> 'Username for a transfer',
	'ACP_SET_USERNAME_EXPLAIN'			=> 'Here you can set a username, to which the download costs should be transferred to. Leave empty, if none should receive the above named costs.',
	'ACP_FTP_OR_UPLOAD'					=> 'You can do only a FTP upload <strong>OR</strong> normal upload!',
	'ACP_NEW_FTP_FILENAME_EXPLAIN'		=> 'Enter here the file name (ie. sample.zip), if you like to use the FTP upload method.',
	'ACP_NEW_FTP_FILENAME'				=> 'FTP file name',
	'ACP_UPLOAD_METHOD'					=> 'Upload Method',
	'ACP_UPLOAD_METHOD_EXPLAIN'			=> 'You can add a new upload via FTP or directly. If you are going to use the FTP upload method, the file needs to be uploaded to the correct category <strong>before</strong> you enter it here! You only can use on or the other method at a time!',
	'ACP_UPLOAD_FILE_NOT_EXISTS'		=> 'The file does not exists in the named category. Since you selected the FTP upload method, this file needs to be uploaded via FTP in the correct directory <strong>BEFORE</strong> you can add it!',
));
