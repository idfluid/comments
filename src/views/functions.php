<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

// Check if user is logged
function com_is_logged()
{
	//Here you need to check if the user is logged into your website and then return TRUE or FALSE
	if (isset($_SESSION['user']))
		return true;
	else return false;
}

// Return the id of the user if is logged
function com_get_user_id()
{
	//Here you need to set the user_id from your session
	
	if (com_is_logged()) {
		$user_id = $_SESSION['user']['id'];
		return $user_id;
	}
	else return false;
}

// Use these functions to return custom urls and avatars based on the logged user_id
// function com_get_user_url( $user_id )
// {
// }

// function com_get_user_avatar( $user_id )
// {
// }


/**
 * Callback function before the comment is added
 *
 * @access  public
 * @param 	array
 * @return  array
 */
function add_comment_cb_before($data)
{
	//Must return $data back !
	return $data;
}

/**
 * Callback function after the comment is added
 *
 * @access  public
 * @param 	array
 * @return  array
 */
function add_comment_cb_after($data)
{	
	//Must return $data back !
	return $data;
}
?> 