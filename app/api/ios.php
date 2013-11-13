<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Anwsion dev team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

define('IN_MOBILE', true);

class ios extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		HTTP::no_cache_header();
	}
	
	public function device_token_action()
	{	
		if ($_POST['device_token'])
		{
			$device_token = str_replace(array(' ', '<', '>'), '', $_POST['device_token']);
			
			if (!$this->model('system')->fetch_row('ios_device_token', "`device_token` = '" . addslashes($device_token) . "'"))
			{
				$this->model('system')->insert('ios_device_token', array(
					'device_token' => $device_token
				));
			}
			
			echo json_encode(array(
				'result' => 'success'
			));
		}
		else
		{
			echo json_encode(array(
				'result' => 'error'
			));
		}
		
		die;
	}
	
	public function version_action()
	{
		echo json_encode(array(
			'result' => '1.0.0'
		));
	}
}