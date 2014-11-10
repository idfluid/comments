<?php 

namespace Idfluid\Comments;

if (!defined('BASEPATH'))
	define('BASEPATH', TRUE);

//Include database class
require_once('Database.php');

class Comments {

    public $errors  = array();
	public static $config  = array(
		'jquery'    => true,
		'bootstrap' => true,
	);
	public $pattern = array(
			'email'    => '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i',
			'url'      => '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i'
		);
	public $view;


	public function __construct(array $config = array())
    {
        $this->configure($config);
    }

    /**
     * Overrides configuration settings
     *
     * @param array $config
     */
    public function configure(array $config = array())
    {
        self::$config = array_replace(self::$config, $config);

        return $this;
    }

	/**
	 * Process the comment, format date, set author, avatar
	 *
	 * @access  private
	 * @param   array
	 * @return  array
	 */
	private function process_comment($comment) {

		//Get date formats from the config
		$full_date_format = isset(self::$config['full_date_format']) ?  self::$config['full_date_format']  : 'M d, Y  h:i:s A';
		$short_date_format = isset(self::$config['short_date_format']) ? self::$config['short_date_format'] : FALSE ;
		
		//Convert string date to timestamp
		$timestamp = strtotime($comment['date']);
		
		//Set the full date and the short date using the date formats
		$comment['date'] = date($full_date_format, $timestamp);
		$comment['short_date'] = $short_date_format ? date($short_date_format, $timestamp) : $this->datef($timestamp);

		//If the user_id exists get the user details from database
		if (!empty($comment['user_id'])) {

			/*$u = self::$config['db_users'];
			$rows = $u['id'].','.$u['first_name'].','.$u['last_name'].','.$u['email'];

			$db = new Database();
			if ($db->select($u['table'], $rows, $u['id'].'="'.$comment['user_id'].'"', null, 1)) {
				$user = $db->getResult(); $user = $user[1];
				$comment['author'] = $user[$u['first_name']].' '.$user[$u['last_name']];

				if ( !empty($u['avatar']) )
					$comment['avatar'] = $user['avatar'];
				else if ( function_exists('com_get_user_avatar') )
					$comment['avatar'] = com_get_user_avatar( $user[$u['id']] );
				else $comment['avatar'] = $this->gravatar( $user['email'] );
			}*/

			$user = \Sentry::findUserById($comment['user_id']);
			$comment['author'] = $user->first_name.' '.$user->last_name;
			if ($user->provider == 'facebook') {
				$comment['avatar'] = 'http://graph.facebook.com/'.$user->username.'/picture?height=48&type=normal&width=48';
			}
			else {
				$comment['avatar'] =  \URL::to('public/packages/idfluid/comments/images/noavatar.png');
			}
		}
		else $comment['avatar'] = \URL::to('public/packages/idfluid/comments/images/noavatar.png');

		$comment['reply'] = $this->config('comment_reply') ? 1 : 0;

		return $comment;
	}

	/**
	 * Get comments from database
	 *
	 * @access  public
	 * @param   array
	 * @return  array
	 */
	public function get_comments($data = array())
	{
		$order = self::$config['comments_order'];
		$per_page = self::$config['comments_per_page'];
		$rows = 'id,page,author,author_email,author_url,date,comment,user_id';
		$where = '';
		extract($data);
		
		if(empty($paged) or !is_numeric($paged) or $paged<1)
			$paged = 1;

		$start = ( $paged - 1 ) * $per_page;

		if (isset($page)) {
			$page = $this->xss($page);
			$where = "page = '$page'";
		}	
		if (isset($status))
			$where .=  (empty($where)?'':' and ') . "status = $status";
		
		if (isset($parent)) 
			$where .= (empty($where)?'':' and ') . " parent = $parent";

		if (isset($_where)) 
			$where .= (empty($where)?'':' and ') . $_where;

		if (isset($id))
			$where = "id = $id";	

		$db = new Database();
		if ($db->select('comments', $rows, $where, 'date ' . $order, $start.','.$per_page)) {
			$result['comments'] = $db->getResult();
			
			$result['total'] = $result['count'] = 0;
			$where = '';
			if ( isset($page) )
				$where = "page = '$page'";
			if ( isset($status) )
				$where .= (empty($where)?'':' and ') . "status = $status";
			if ($db->select('comments', 'parent', $where ) and !isset($id)) {
				$res = $db->getResult();
				foreach ($res as $v)
					if (!$v['parent'])
						$result['count']++;
				$result['total'] = count($res);
			}
			if ( isset($page) )
				$data = array('per_page'=>999999, 'start'=>0, 'page'=>$page, 'rows'=>$rows);
			if ( isset($status) )
				$data['status'] = $status;
			foreach ($result['comments'] as $key => $comment) {
				if (isset($email))
					$comment['_email'] = $email;
				if (!isset($process_comment) or $process_comment)
					$comment = $this->process_comment($comment);
				//Check for comment replies
				$data['parent'] = $comment['id'];
				if ( $this->config('comment_reply') )
					$comment['replies'] = $this->get_comments( $data );
				//Add the commnet array to the result array
				$result['comments'][$key] =  $comment;
			}
			if (isset($id))
				return $result['comments'][1];
			
			return $result;
		} else if ($start > 0) {
			$data['paged'] = 1;
			return $this->get_comments($data);
		}

		return array();
		/*$db = new Database();
		if ($db->select('comments')) {
			$x =  $db->select('comments');
			return $x;
		}*/

		
	}

	/**
	 * Include comments template with the html
	 *
	 * @access  public
	 */
	public function display_comments()
	{
		if ( $this->config('comments_template') )
			$this->view = \View::make('comments::'.$this->config('comments_template'));
		else $this->view = \View::make('comments::template');

		return $this->view;

	}

	/**
	 * Add new comment to database
	 *
	 * @access  public
	 * @param   array
	 * @return  array or false
	 */
	public function add_comment($data)
	{
		extract($data);
		
		//get the page
		$_data['page'] = $this->xss((empty($page)) ? $this->currentURL() : $page);

		//get the ip
		$_data['author_ip'] = \Request::server('REMOTE_ADDR');

		//get user agent
		$_data['agent'] = \Request::server('HTTP_USER_AGENT');

		//check for comment parent
		$_data['parent'] = (empty($parent)) ? 0 : $parent;

		$_data['date'] = date("Y-m-d H:i:s", time());

		if (!empty($reply) and is_numeric($reply))
			$_data['parent'] = $data['reply'];

		//comment content
		if (empty($comment))
			$this->set_error('empty_com');
		else $_data['comment'] = $this->xss($comment);

		//Comment status
		$_data['status'] = self::$config['comment_status'];
		$_data['user_id'] = $user_id;

		if (empty($this->errors)) {
			
			$data = array($_data['comment'], $_data['author_ip']);
			if (isset($_data['author']))
				$data[] = $_data['author'];

			$db = new Database();
			if($db->insert('comments', $_data)) {
				$data = array(
					'id'           => $db->get_id(),
					'user_id'      => $user_id,
					'author'       => isset($_data['author']) ? $_data['author'] : '',
					'date'         => $_data['date'],
					'comment'      => $_data['comment'],
					'status'	   => $_data['status']
 				);
				$data = $this->process_comment($data);

				return $data;
			}
			else $this->set_error('error');
		}
		return FALSE;
	}

	public function update_comment($data, $where = null, $limit = null)
	{
		if ( !$where and !empty($data['id']) ) {
			$where = 'id = '.$data['id'];
			$limit = 1;
			unset($data['id']);
		}
		if (!empty($data['author_email']) and !preg_match($this->pattern['email'], $data['author_email']))
			unset($data['author_email']);

		if (isset($data['author_url']) and !preg_match($this->pattern['url'], $data['author_url']))
			unset($data['author_url']);

		if (isset($data['status']) and !in_array($data['status'], array(0,1,2)))
			unset($data['status']);
		
		$db = new Database();
		return $db->update('comments', $data, $where, $limit);
	}

	public function delete_comment($id)
	{
		$db =new Database();
		if (is_numeric($id) and $id>0)
			return $db->delete('comments', "id = $id OR parent = $id");
		return false;
	}

	/**
	 * Get user gravatar from database or custom avatar or gravatar
	 *
	 * @access  public
	 * @return  string
	 */
	public function user_avatar()
	{
		if ( com_is_logged() ) {
			$u = self::$config['db_users'];
			$rows = $u['id'].','.$u['name'].','.$u['email'] . ( !empty($u['avatar']) ? ','.$u['avatar'] : '' );
			$user_id = com_get_user_id();
			$db = new Database();
			if ($db->select($u['table'], $rows, $u['id']." = $user_id ", null, 1)) {
				$user = $db->getResult(); $user = $user[1];
				if ( !empty($u['avatar']) )
					return $user['avatar'];
				else if ( function_exists('com_get_user_avatar') )
					return com_get_user_avatar( $user_id );
				else return $this->gravatar( $user['email'] );
			}
		}
	}

	public function get_paged($page, $comment_id)
	{
		$paged = 1;
		$per_page = $this->config('comments_per_page');
		$data = array(
			'page'   => $page,
			'parent' => 0,
			'status' => 1,
			'per_page' => 99999999,
			'process_comment' => false
		);
		$this->set_config(array('comment_reply'=>false));
		$data = $this->get_comments($data);
		if ($data && !empty($data['total'])) {
			foreach ($data['comments'] as $comment) {
				if ($comment['id'] == $comment_id)
					if ($paged > $per_page)
					 	return ceil($paged/$per_page);
					else return 1;
				$paged++;
			}
		}
	}

	/**
	 * Get gravatar image by email
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public function gravatar($email)
	{
		return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode(self::$config['default_avatar']) . "&s=100";
	}

	private function comment_filter($subject, $filter) 
	{
		$keys = $this->config( $filter );
		if ( !empty($keys) ) {
			$keys = explode(',', $keys);
			foreach ($keys as $key)
				if (is_array($subject)) {
					foreach ($subject as $subj) 
						if (preg_match('/\b'.$key.'\b/', $subj))
							return TRUE;
				}
				else if (preg_match('/\b'.$key.'\b/', $subject))
					return TRUE;
		}
		return FALSE;
	}

	/**
	 * Set config options
	 *
	 * @access  public
	 * @param   array
	 */
	public function set_config($config)
	{
		if (is_array($config))
			foreach ($config as $key => $value)
				self::$config[$key] = $value;
	}

	/**
	 * Get a config option
	 *
	 * @access  public
	 * @param   string
	 * @return  string/array/boolean
	 */
	public function config($option)
	{
		if (isset(self::$config[$option]))
			return self::$config[$option];
		else return FALSE;
	}

	/**
	 * Set error
	 *
	 * @access  public
	 * @param   string
	 */
	public function set_error($error)
	{
		$this->errors[$error] = 1;
	}

	/**
	 * Convert timestamp to a date format with difference
	 *
	 * @access  public
	 * @param   integer or string
	 * @return  string
	 */
	public function datef($timestamp)
	{

		if (!is_numeric($timestamp))
			$timestamp = strtotime($timestamp);

		$difference = time() - $timestamp + 1; //add one second so won't be 0;
		$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");

		if ($difference > 0) { // this was in the past time
			$ending = "ago";
		} else { // this was in the future time
			$difference = -$difference;
			$ending = "to go";
		}
		for($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];
		$difference = round($difference);
		if($difference != 1) $periods[$j].= "s";
			$text = "$difference $periods[$j] $ending";
		return $text;
	}

	/**
	 * Initialize captcha session
	 *
	 * @access  public
	 */
	public function captcha()
	{	
		//Create 2 captcha sessions, one that is used by the captcha.php and one for checking 
		$_SESSION['com_captcha'] = substr(md5(time()), 0, 4);
		$_SESSION['com_captcha_init'] = $_SESSION['com_captcha'];
	}

	/**
	 * Convert special characters to HTML entities
	 *
	 * @access  public
	 * @param   string or array 
	 * @return  string or array
	 */
	public function xss($val)
	{
		if(is_array($val)) {
			foreach ($val as $key=>$value)
				$val[$key] = $this->xss($value);
			return $val;
		}
		else return htmlspecialchars(trim($val), ENT_QUOTES);
	}

	/**
	 * Return the current url
	 *
	 * @access  public
	 * @return  string
	 */
	public function currentURL()
	{	
		$base = \URL::to('/');
		$current = \Request::url();
		$output = str_replace($base,"",$current);
		return $current;
	}

	/**
	 * Send email with Gmail or server email
	 *
	 * @access  public
	 * @param   string or array
	 * @param   string
	 * @param   string
	 * @return  true or false
	 */
	public function send_email($to, $subject, $message) {
		
		if (!isset($this->config['PHPMailer']) or $this->config['PHPMailer'] != FALSE) {

			require_once(dirname(__FILE__) . '/PHPMailer/phpmailer.php');
			$mail = new PHPMailer();

			//If the gmail api is set then use gmail
			if (!empty($this->config['gmail_username']) and !empty($this->config['gmail_password'])) {
				$mail->IsSMTP();
				$mail->SMTPAuth   = true;
				$mail->Host       = 'ssl://smtp.gmail.com';
				$mail->Port       = '465';
				$mail->Username   = $this->config['gmail_username'];
				$mail->Password   = $this->config['gmail_password'];
				//$mail->SMTPDebug  = 1;
			}
			//Else use the server email
			else {
				$mail->IsSendmail();
			}
			$mail->From     = $this->config['from_email'];
			$mail->FromName = $this->config['from_name'];

			if (is_array($to)) {
				foreach ($to as $sendTo) {
					$mail->AddAddress($sendTo);
				}
			} else {
				$mail->AddAddress($to);
			}
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			if($mail->Send()) {
				return TRUE;
				//echo "Message Sent";
			} else {
				return FALSE;
				 //echo "Mailer Error: " . $mail->ErrorInfo;
			}
		} else {
			//Simple email sender without the PHPMailer library
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.$this->config['from_name'].' <'.$this->config['from_email'].'>';
			
			if (is_array($to)) {
				foreach ($to as $sendTo) {
					@mail($to, $subject, $message, $headers);
				}
			} else {
				if ( @mail($to, $subject, $message, $headers) )
					return TRUE;
				else return FALSE;
			}
		}
	}

	public function greeting() {
		$db = new Database;
		return $this->display_comments();
	}

}