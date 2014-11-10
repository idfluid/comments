<?php namespace Idfluid\Comments\Controllers;
use \Illuminate\Routing\Controllers\Controller;
class AjaxController extends \BaseController {

	public function ajax()
	{

		//require_once('functions.php');

		// GET actions
		if (\Input::get('action')) {
			switch (\Input::get('action'))  {
				case 'get-comments':
					if (\Input::get('page')) {
						$def = array('parent'=> 0, 'email'=>false, 'status'=>1, 'page'=> urldecode(\Input::get('page')));
						$input = array_merge($def, \Input::get());
						$data = \Comments::get_comments($input);
						return \Response::json(array('success'=>true, 'data'=> $data));
					}
					else return 0;
				break;
			}
		}

		// POST actions
		if (\Input::get('action')) {
			
			//if (Comments::config('logged_only') && !com_is_logged())
				//die('0');

			switch (\Input::get('action')) {
				case'add-comment':
					$def = array('page'=> urldecode(\Input::get('page')));
					$input = array_merge($def, \Input::get());

					$data = \Comments::add_comment($input);
					if (!empty(\Comments::$errors))
						return \Response::json(array('success'=>false, 'data'=> \Comments::$errors));
					else return \Response::json(array('success'=>true, 'data'=> $data));
				break;
			}
		}
	}

}
