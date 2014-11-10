<?php
session_start();
header('Content-Type: application/json');

// Check the request type 
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH']!="XMLHttpRequest")
	die('0');

// Require an action parameter
if (empty($_REQUEST['action']))
	die('0');

require_once('functions.php');

// GET actions
if (isset($_GET['action'])) {
	switch ($_GET['action'])  {
		case 'get-comments':
			if (isset($_GET['page'])) {
				$_GET['parent'] = 0;
				$_GET['email']  = false;
				$_GET['status'] = 1;
				$_GET['page'] = urldecode($_GET['page']);
				$data = Comments::get_comments($_GET);
				echo json_encode(array('success'=>true, 'data'=> $data));
			}
			else echo 0;
		break;
	}
}

// POST actions
if (isset($_POST['action'])) {
	
	if (Comments::config('logged_only') && !com_is_logged())
		die('0');

	switch ($_POST['action']) {
		case'add-comment':
			$_POST['page'] = urldecode($_POST['page']);
			$data = Comments::add_comment($_POST);
			if (!empty(Comments::errors))
				echo json_encode(array('success' => false, 'data' => Comments::errors));
			else echo json_encode(array('success' => true, 'data' => $data));
		break;

		case 'comment-notification': 
			if ( !empty($_POST['comment_id']) and is_numeric($_POST['comment_id']) and isset($_SESSION['com_email_notif']) and $_SESSION['com_email_notif']+6 > time() ) {
				$rows = 'id,page,author,author_email,author_url,date,comment,user_id,author_ip,status';
				Comments::set_config( array('comment_reply' => false) );
				$comment = Comments::get_comments( array('id' => $_POST['comment_id'], 'rows'=> $rows ));
				$templates = Comments::config('email_templates');
				$notif_email = Comments::config('comment_notification');
				if (!empty($notif_email) and !empty($comment)) {
					if ( $comment['status'] == 1) {
						$subject = sprintf( $templates['new_comment']['subject'] , $_POST['page_title'] );
						$message = sprintf( $templates['new_comment']['header'] , $_POST['page_title']);
						$message .= sprintf( $templates['body'], $comment['author'], $comment['author_ip'], $comment['author_email'], $comment['author_url'], $comment['comment'] );
						$message .= sprintf( $templates['new_comment']['footer'], $_POST['page_url'], $_POST['comment_url'] );
					} else {
						$subject = sprintf( $templates['moderate']['subject'] , $_POST['page_title'] );
						$message = sprintf( $templates['moderate']['header'] , $_POST['page_title'] );
						$message .= sprintf( $templates['body'], $comment['author'], $comment['author_ip'], $comment['author_email'], $comment['author_url'], $comment['comment'] );
						
						$url = Comments::config('base_url').'admin/?page=comments';
						$message .= sprintf( $templates['moderate']['footer'], $url.'&action=approve&comments%5B%5D='.$comment['id'], $url.'&action=spam&comments%5B%5D='.$comment['id'] , $url.($comment['status']==2?'&status=spam':'').'#edit-'.$comment['id'] );
					}
					Comments::send_email( $notif_email , $subject, $message);	
				}
				if (!empty($_POST['reply']) and is_numeric($_POST['reply']) and Comments::config('reply_notification') and !empty($comment) and $comment['status'] == 1) {
					$parent = Comments::get_comments( array('id' => $_POST['reply'], 'rows'=> $rows ));
					if ( $parent ) {
						$subject = sprintf( $templates['reply']['subject'] , $_POST['page_title'] );
						$message = sprintf( $templates['reply']['body'], $comment['author'], $comment['comment'], $_POST['comment_url'] );
						Comments::send_email( $parent['author_email'] , $subject, $message);
					}
				}
			}
			unset($_SESSION['com_email_notif']);
		break;

		case 'refreshcaptcha':
			Comments::captcha();
		break;
	}
}