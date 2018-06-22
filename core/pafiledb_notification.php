<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_notification.php,v 1.67 2013/10/03 10:05:44 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

namespace orynider\pafiledb\core;

/**
 * mx_pa_notification.
 *
 * This class extends general mx_notification class
 *
 * // MODE: MX_PM_MODE/MX_MAIL_MODE, $id: get all file/article data for this id
 * $mx_notification->init($mode, $id); // MODE: MX_PM_MODE/MX_MAIL_MODE
 *
 * // MODE: MX_PM_MODE/MX_MAIL_MODE, ACTION: MX_NEW_NOTIFICATION/MX_EDITED_NOTIFICATION/MX_APPROVED_NOTIFICATION/MX_UNAPPROVED_NOTIFICATION
 * $mx_notification->notify( $mode = MX_PM_MODE, $action = MX_NEW_NOTIFICATION, $to_id, $from_id, $subject, $message, $html_on, $bbcode_on, $smilies_on )
 *
 * @access public
 * @author Jon Ohlsson
 */
class pafiledb_notification extends \phpbb\notification\type\base
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $item_id
	 */
	function init($item_id = 0, $allow_comment_wysiwyg = 0)
	{
		global $db, $lang, $module_root_path, $phpbb_root_path, $mx_root_path, $phpEx, $userdata, $pafiledb;

			// =======================================================
			// item id is not set, give him/her a nice error message
			// =======================================================
			if (empty($item_id))
			{
				mx_message_die(GENERAL_ERROR, 'Bad Init pars');
			}

			unset($this->langs);

			//
			// Build up generic lang keys
			//
			$this->langs['item_not_exist'] = $lang['File_not_exist'];
			$this->langs['module_title'] = $lang['PA_prefix'];

			$this->langs['notify_subject_new'] = $lang['PA_notify_subject_new'];
			$this->langs['notify_subject_edited'] = $lang['PA_notify_subject_edited'];
			$this->langs['notify_subject_approved'] = $lang['PA_notify_subject_approved'];
			$this->langs['notify_subject_unapproved'] = $lang['PA_notify_subject_unapproved'];
			$this->langs['notify_subject_deleted'] = $lang['PA_notify_subject_deleted'];

			$this->langs['notify_new_body'] = $lang['PA_notify_new_body'];
			$this->langs['notify_edited_body'] = $lang['PA_notify_edited_body'];
			$this->langs['notify_approved_body'] = $lang['PA_notify_approved_body'];
			$this->langs['notify_unapproved_body'] = $lang['PA_notify_unapproved_body'];
			$this->langs['notify_deleted_body'] = $lang['PA_notify_deleted_body'];

			$this->langs['item_title'] = $lang['File'];
			$this->langs['author'] = $lang['Submited'] ? $lang['Submited'] : $lang['Creator'];
			$this->langs['item_description'] = $lang['Desc'];
			$this->langs['item_type'] = '';
			$this->langs['category'] = $lang['Category'];
			$this->langs['read_full_item'] = $lang['PA_goto'];
			$this->langs['edited_item_info'] = $lang['Edited_Article_info'];

			switch ( SQL_LAYER )
			{
				case 'oracle':
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username
						FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS c
						WHERE f.file_id = r.votes_file(+)
						AND f.user_id = u.user_id(+)
						AND c.cat_id = a.file_catid
						AND f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;

				default:
            		$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username
                  		FROM " . PA_FILES_TABLE . " AS f
                     		LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f.file_catid = cat.cat_id
                     		LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file
                     		LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
                  		WHERE f.file_id = '" . $item_id . "'
                  		GROUP BY f.file_id ";
					break;
			}

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
			}

			// ===================================================
			// file doesn't exist'
			// ===================================================
			if ( !$item_data = $db->sql_fetchrow( $result ) )
			{
				mx_message_die( GENERAL_MESSAGE, $this->langs['Item_not_exist'] );
			}

			$db->sql_freeresult( $result );

			unset($this->data);

			//
			// File data
			//
			$this->data['item_id'] = $item_id;
			$this->data['item_title'] = $item_data['file_name'];
			$this->data['item_desc'] = $item_data['file_desc'];


			//
			// Category data
			//
			$this->data['item_category_id'] = $item_data['cat_id'];
			$this->data['item_category_name'] = $item_data['cat_name'];

			//
			// File author
			//
			$this->data['item_author_id'] = $item_data['user_id'];
			$this->data['item_author'] = ( $item_data['user_id'] != ANONYMOUS ) ? $item_data['username'] : $lang['Guest'];

			//
			// File editor
			//
			$this->data['item_editor_id'] = $userdata['user_id'];
			$this->data['item_editor'] = ( $userdata['user_id'] != '-1' ) ? $userdata['username'] : $lang['Guest'];

			$mx_root_path_tmp = $mx_root_path; // Stupid workaround, since phpbb posts need full paths.
			$mx_root_path = '';
			$this->temp_url = PORTAL_URL . $pafiledb->this_mxurl("action=" . "file&file_id=" . $this->data['item_id'], false, true);
			$mx_root_path = $mx_root_path_tmp;

			//
			// Toggles
			//
			$this->allow_comment_wysiwyg = $allow_comment_wysiwyg;
	}		
}
?>