<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2013 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|   
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class account_class extends AWS_MODEL
{
	/**
	 * 检查用户名是否已经存在
	 * @param $user_name
	 * @return boolean
	 */
	function check_username($user_name)
	{
		return $this->fetch_one('users', 'uid', "user_name = '" . $this->quote(trim($user_name)) . "' OR url_token = '" . $this->quote(trim($user_name)) . "'");
	}
	
	/**
	 * 检查用户名中是否包含敏感词或用户信息保留字
	 * @param string $user_name
	 * @return boolean
	 */
	function check_username_sensitive_words($user_name)
	{
		if (H::sensitive_word_exists($user_name, '', true))
		{
			return true;
		}
		
		if (!get_setting('censoruser'))
		{
			return false;
		}
		
		if ($censorusers = explode("\n", get_setting('censoruser')))
		{
			foreach ($censorusers as $name)
			{
				$name = trim($name);
				
				if (!$name)
				{
					continue;
				}
				
				if (preg_match('/(' . $name . ')/is', $user_name))
				{
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * 检查用户名是否已经存在
	 * @param $user_name
	 * @return rows 
	 */
	
	function check_uid($uid)
	{		
		return $this->fetch_one('users', 'uid', 'uid = ' . intval($uid));
	}

	/**
	 * 检查电子邮件地址是否已经存在
	 * @param $email
	 * @return int
	 */
	function check_email($email)
	{
		if (! H::valid_email($email))
		{
			return TRUE;
		}
		
		return $this->fetch_one('users', 'uid', "email = '" . $this->quote($email) . "'");
	}

	/**
	 * 正式表用户登录检查,错误返回FALSE,正确返回用户数据
	 * @param $user_name
	 * @param $password
	 * @return
	 */
	function check_login($user_name, $password)
	{		
		if (!$user_name OR !$password)
		{
			return false;
		}
		
		if (H::valid_email($user_name))
		{
			$user_info = $this->get_user_info_by_email($user_name);
		}
		
		if (! $user_info)
		{
			if (! $user_info = $this->get_user_info_by_username($user_name))
			{
				return false;
			}
		}
		
		if (! $this->check_password($password, $user_info['password'], $user_info['salt']))
		{
			return false;
		}
		else
		{
			return $user_info;
		}
	
	}
	
	function check_hash_login($user_name, $password_md5)
	{
		if (!$user_name OR !$password_md5)
		{
			return false;
		}
		
		if (H::valid_email($user_name))
		{
			$user_info = $this->get_user_info_by_email($user_name);
		}
		
		if (! $user_info)
		{
			if (! $user_info = $this->get_user_info_by_username($user_name))
			{
				return false;
			}
		}
		
		if ( $password_md5 != $user_info['password'])
		{
			return false;
		}
		else
		{
			return $user_info;
		}
	
	}

	/**
	 * 检验密码是否和数据库里面的密码相同
	 *
	 * @param string $password		新密码
	 * @param string $db_password   数据库密码
	 * @param string $salt			混淆码
	 * @return bool
	 */
	function check_password($password, $db_password, $salt)
	{
		$password = compile_password($password, $salt);
		
		if ($password == $db_password)
		{
			return true;
		}
		
		return false;
	
	}

	/**
	 * 通过用户名获取用户信息
	 * @param $user_name		用户名或邮箱地址
	 * @return
	 */
	function get_user_info_by_username($user_name, $attrb = false)
	{
		if ($uid = $this->fetch_one('users', 'uid', "user_name = '" . $this->quote($user_name) . "'"))
		{
			return $this->get_user_info_by_uid($uid, $attrb);
		}
	}

	/**
	 * 通过用户邮箱获取用户信息
	 * @param $email 用邮箱地址
	 * @return row
	 */
	function get_user_info_by_email($email)
	{
		if ($uid = $this->fetch_one('users', 'uid', "email = '" . $this->quote($email) . "'"))
		{
			return $this->get_user_info_by_uid($uid, $attrb);
		}
	}
	
	
	function get_user_info_by_url_token($url_token, $attrb = false)
	{
		if ($uid = $this->fetch_one('users', 'uid', "url_token = '" . $this->quote($url_token) . "'"))
		{
			return $this->get_user_info_by_uid($uid, $attrb);
		}
	}
	
	function get_user_info_by_weixin_id($weixin_id, $attrb = false)
	{
		if ($uid = $this->fetch_one('users', 'uid', "`weixin_id` = '" . $this->quote($weixin_id) . "'"))
		{
			return $this->get_user_info_by_uid($uid, $attrb);
		}
	}

	/**
	 * 通过用户 uid 获取用户信息
	 * @param $user_name
	 * @return
	 */
	function get_user_info_by_uid($uid, $attrib = false, $var_cache = true)
	{
		if (! $uid)
		{
			return false;
		}
		
		if ($var_cache)
		{
			static $users_info;
			
			if ($users_info[$uid . '_attrib'])
			{
				return $users_info[$uid . '_attrib'];
			}
			else if ($users_info[$uid])
			{
				return $users_info[$uid];
			}
		}
		
		if ($attrib)
		{
			$sql = "SELECT MEM.*, MEB.* FROM " . $this->get_table('users') . " AS MEM LEFT JOIN " . $this->get_table('users_attrib') . " AS MEB ON MEM.uid = MEB.uid WHERE MEM.uid = " . intval($uid);
		}
		else
		{
			$sql = "SELECT * FROM " . $this->get_table('users') . " WHERE uid = " . intval($uid);
		}
		
		if (! $user_info = $this->query_row($sql))
		{
			return false;
		}
		
		if (!$user_info['url_token'] AND $user_info['user_name'])
		{
			$user_info['url_token'] = urlencode($user_info['user_name']);
		}
		
		if ($user_info['email_settings'])
		{
			$user_info['email_settings'] = unserialize($user_info['email_settings']);
		}
		else
		{
			$user_info['email_settings'] = array();
		}
		
		$users_info[$uid] = $user_info;
		
		if ($attrib)
		{
			unset($users_info[$uid]);
			
			$users_info[$uid . '_attrib'] = $user_info;
		}
		
		return $user_info;
	}

	/**
	 * 通过指量用户 uid 返回指量用户数据
	 * 
	 * @param arrary $uids 用户 IDS
	 * @param bool	 $attrib   是否返回附加表数据
	 */
	function get_user_info_by_uids($uids, $attrib = false)
	{
		if (! is_array($uids) OR sizeof($uids) == 0)
		{
			return false;
		}
		
		array_walk_recursive($uids, 'intval_string');
		
		$uids = array_unique($uids);
		
		if (sizeof($uids) == 1)
		{
			if ($one_user_info = $this->get_user_info_by_uid(end($uids), $attrib))
			{
				return array(
					end($uids) => $one_user_info
				);
			}
			
		}
		
		static $users_info;
		
		if ($users_info[implode('_', $uids) . '_attrib'])
		{
			return $users_info[implode('_', $uids) . '_attrib'];
		}
		else if ($users_info[implode('_', $uids)])
		{
			return $users_info[implode('_', $uids)];
		}
		
		if ($user_info = $this->fetch_all('users', "uid IN(" . implode(',', $uids) . ")"))
		{
			foreach ($user_info as $key => $val)
			{
				if (!$val['url_token'])
				{
					$val['url_token'] = urlencode($val['user_name']);
				}
				
				if ($val['email_settings'])
				{
					$val['email_settings'] = unserialize($val['email_settings']);
				}
				
				$data[$val['uid']] = $val;
				
				$query_uids[] = $val['uid'];
			}
			
			$users_info[implode('_', $uids)] = $data;
		}
		
		if ($attrib AND $query_uids)
		{
			if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $query_uids) . ')'))
			{
				foreach ($users_attrib AS $key => $val)
				{
					unset($val['id']);
					
					foreach ($val AS $attrib_key => $attrib_val)
					{
						$data[$val['uid']][$attrib_key] = $attrib_val;
					}
				}
			}
			
			unset($users_info[implode('_', $uids)]);
			
			$users_info[implode('_', $uids) . '_attrib'] = $data;
		}
		
		return $data;
	}

	/**
	 * 根据用户ID获取用户通知设置
	 * @param $uid
	 */
	function get_notification_setting_by_uid($uid)
	{
		$setting = $this->fetch_row('users_notification_setting', 'uid = ' . intval($uid));
		
		if (empty($setting))
		{
			return array('data' => array());
		}
		
		$setting['data'] = unserialize($setting['data']);
		
		if (empty($setting['data']))
		{
			$setting['data'] = array();
		}
		
		return $setting;
	}

	/**
	 * 更新邀请名额
	 * 
	 * @param int $uid
	 */
	function consume_invitation_available($uid)
	{
		return $this->query("UPDATE " . $this->get_table('users') . " SET invitation_available = invitation_available - 1 WHERE uid = " . intval($uid));
	}
	
	function insert_user($user_name, $password, $email, $sex = 0, $mobile = null)
	{
		if (!$user_name OR !$password OR !$email)
		{
			return false;
		}
		
		if ($user_info = $this->get_user_info_by_username($user_name))
		{
			return $user_info['uid'];
		}
		
		if ($user_info = $this->get_user_info_by_email($user_name))
		{
			return $user_info['uid'];
		}
		
		$salt = fetch_salt(4);
		
		if ($uid = $this->insert('users', array(
			'user_name' => htmlspecialchars($user_name),
			'password' => compile_password($password, $salt),
			'salt' => $salt,
			'email' => htmlspecialchars($email),
			'sex' => intval($sex),
			'mobile' => htmlspecialchars($mobile),
			'reg_time' => time(),
			'reg_ip' => ip2long(fetch_ip()),
			'email_settings' => serialize(get_setting('new_user_email_setting'))
		)))
		{
			$this->insert('users_attrib', array(
				'uid' => $uid
			));
			
			$this->update_notification_setting_fields(get_setting('new_user_notification_setting'), $uid);
			
			//$this->model('search_index')->push_index('user', $user_name, $uid);
		}
		
		return $uid;
	}
	
	function user_register($user_name, $password, $email, $email_valid = false)
	{
		if ($uid = $this->insert_user($user_name, $password, $email))
		{
			if ($def_focus_uids_str = get_setting('def_focus_uids'))
			{
				$def_focus_uids = explode(',', $def_focus_uids_str);
				
				foreach ($def_focus_uids as $key => $val)
				{
					$this->model('follow')->user_follow_add($uid, $val);
				}
			}
			
			$group_id = (get_setting('register_email_reqire') == 'N' || $email_valid) ? 4 : 3;
			
			$this->update_users_fields(array(
				'valid_email' => intval($email_valid),
				'group_id' => $group_id,
				'reputation_group' => 5,
				'invitation_available' => get_setting('newer_invitation_num'),
				'is_first_login' => 1
			), $uid);
			
			$this->model('integral')->process($uid, 'REGISTER', get_setting('integral_system_config_register'), '初始资本');
			
			if ($email_valid)
			{
				$this->welcome_message($uid, $user_name, $email);
			}
		}
		
		return $uid;
	}
	
	function welcome_message($uid, $user_name, $email)
	{
		if (get_setting('welcome_message_email'))
		{
			AWS_APP::mail()->send($email, '欢迎来到 ' . get_setting('site_name'), str_replace(array('{username}', '{time}', '{sitename}'), array($user_name, date('Y-m-d H:i:s', time()), get_setting('site_name')), nl2br(get_setting('welcome_message_email'))), get_setting('site_name'), $user_name);
		}
			
		if (get_setting('welcome_message_pm'))
		{
			$this->model('message')->send_message($uid, $uid, null, str_replace(array('{username}', '{time}', '{sitename}'), array($user_name, date('Y-m-d H:i:s', time()), get_setting('site_name')), get_setting('welcome_message_pm')), 0, 0);
		}
	}
	
	/**
	 * 更新用户状态或字段
	 * @param $update_data 字段
	 * @param $userid 用户id
	 * @return  
	 */
	function update_users_fields($update_data, $uid)
	{
		return $this->update('users', $update_data, 'uid = ' . intval($uid));
	}
	
	function update_user_name($user_name, $uid)
	{
		$this->update('users', array(
			'user_name' => htmlspecialchars($user_name),
		), 'uid = ' . intval($uid));
		
		//return $this->model('search_index')->push_index('user', $user_name, $uid);
		
		return true;
	}

	/**
	 * 更新用户附加表状态或字段
	 * @param $update_data 字段
	 * @param $userid	用户id
	 * @return 
	 */
	function update_users_attrib_fields($update_data, $uid)
	{
		return $this->update('users_attrib', $update_data, 'uid = ' . intval($uid));
	}

	/**
	 * 更改用户密码
	 *
	 * @param  $oldpassword 旧密码
	 * @param  $password 新密码
	 * @param  $userid 用户id
	 * @param  $salt 混淆码
	 */
	function update_user_password($oldpassword, $password, $uid, $salt)
	{
		if (!$salt OR !$uid)
		{
			return false;
		}
		
		$oldpassword = compile_password($oldpassword, $salt);
		
		if ($this->count('users', "uid = " . intval($uid) . " AND password = '" . $this->quote($oldpassword) . "'") != 1)
		{
			return false;
		}
		
		return $this->update_user_password_ingore_oldpassword($password, $uid, $salt);
	
	}

	/**
	 * 更改用户不用旧密码密码
	 *
	 * @param $password
	 * @param $userid
	 */
	function update_user_password_ingore_oldpassword($password, $uid, $salt)
	{
		if (!$salt OR !$password OR !$uid)
		{
			return false;
		}
		
		$this->update('users', array(
			'password' => compile_password($password, $salt),
			'salt' => $salt
		), 'uid = ' . intval($uid));
		
		return true;
	}

	function clean_first_login($uid)
	{
		if (! $this->shutdown_update('users', array(
			'is_first_login' => 0
		), 'uid = ' . intval($uid)))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	function update_user_last_login($uid)
	{
		if (! $uid)
		{
			return false;
		}
		
		return $this->shutdown_update('users', array(
			'last_login' => time(),
			'last_ip' => ip2long(fetch_ip())
		), 'uid = ' . intval($uid));
	}

	/**
	 * 更新用户通知设置
	 * 
	 * @param  $data	更新数组
	 * @param  $uid	UID
	 * 
	 * @return bool
	 */
	public function update_notification_setting_fields($data, $uid)
	{
		if (!$this->count('users_notification_setting', 'uid = ' . intval($uid)))
		{			
			$this->insert('users_notification_setting', array(
				'data' => serialize($data),
				'uid' => intval($uid)
			));
		}
		else
		{
			$this->update('users_notification_setting', array(
				'data' => serialize($data)
			), 'uid = ' . intval($uid));
		}
		
		return true;
	}
	
	public function update_notification_unread($uid)
	{
		return $this->shutdown_update('users', array(
			'notification_unread' => $this->count('notification', 'read_flag = 0 AND recipient_uid = ' . intval($uid))
		), 'uid = ' . intval($uid));
	}
	
	public function update_question_invite_count($uid)
	{
		return $this->update('users', array(
			'invite_count' => $this->count('question_invite', 'recipients_uid = ' . intval($uid))
		), 'uid = ' . intval($uid));
	}
	
	public function update_notic_unread($uid)
	{
		return $this->shutdown_update('users', array(
			'notice_unread' => $this->count('notice_recipient', "recipient_uid = " . intval($uid) . " AND recipient_time = 0 AND (recipient_del IS NULL OR recipient_del = 0)")
		), 'uid = ' . intval($uid));
	}
	
	
	function setcookie_login($uid, $user_name, $password, $salt, $expire = null, $hash_password = true)
	{
		if (! $uid)
		{
			return false;
		}
		
		if (! $expire)
		{
			HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid, $hash_password));
		}
		else
		{			
			HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid, $hash_password), (time() + $expire));
		}
		
		return true;
	}
	
	public function setcookie_logout()
	{
		HTTP::set_cookie('_user_login', '', time() - 3600);
	}
	
	public function logout()
	{
		$this->setcookie_logout();
		$this->setsession_logout();
	}

	function setsession_logout()
	{
		if (isset(AWS_APP::session()->client_info))
		{
			unset(AWS_APP::session()->client_info);
		}
		
		if (isset(AWS_APP::session()->permission))
		{
			unset(AWS_APP::session()->permission);
		}
	}
	
	public function check_username_char($user_name)
	{
		$length = strlen(convert_encoding($user_name, 'UTF-8', 'GB2312'));
		
		$length_min = intval(get_setting('username_length_min'));
		
		$length_max = intval(get_setting('username_length_max'));
		
		if ($length < $length_min || $length > $length_max)
		{
			$flag = true;
		}
		
		switch(get_setting('username_rule'))
		{
			default:
			
			break;
			
			case 1:
				if (!preg_match('/^[\x{4e00}-\x{9fa5}_a-zA-Z0-9]+$/u', $user_name) OR $flag)
				{
					return AWS_APP::lang()->_t('请输入大于 %s 字节的用户名, 允许汉字、字母与数字', ($length_min . ' - ' . $length_max));
				}
			break;
				
			case 2:
				if (!preg_match("/^[a-zA-Z0-9_]+$/i", $user_name) OR $flag)
				{
					return AWS_APP::lang()->_t('请输入 %s 个字母、数字或下划线', ($length_min . ' - ' . $length_max));
				}
			break;
				
			case 3:
				if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $user_name) OR $flag)
				{
					return AWS_APP::lang()->_t('请输入 %s 个汉字', (ceil($length_min / 2) . ' - ' . floor($length_max / 2)));
				}
			break;
		}
		
		return false;
	}
	
	public function get_users_list($where, $limit = 10, $attrib = false, $exclude_self = true, $orderby = 'uid DESC')
	{
		if ($where)
		{
			$where = '(' . $where . ') AND forbidden = 0 AND group_id <> 3';
		}
		else
		{
			$where = 'forbidden = 0 AND group_id <> 3';
		}
		
		if ($exclude_self)
		{
			if ($where)
			{
				$where = '(' . $where . ') AND uid <> ' . USER::get_client_uid();
			}
			else
			{
				$where = 'uid <> ' . USER::get_client_uid();
			}
		}
			
		$result = $this->fetch_all('users', $where, $orderby, $limit);
		
		if ($result)
		{
			foreach ($result AS $key => $val)
			{
				$data[$val['uid']] = $val;
				
				if (!$val['url_token'] AND $val['user_name'])
				{
					$data[$val['uid']]['url_token'] = urlencode($val['user_name']);
				}
				
				if ($val['email_settings'])
				{
					$data[$val['uid']]['email_settings'] = unserialize($val['email_settings']);
				}
				
				$uids[] = $val['uid'];
			}
			
			if ($attrib AND $uids)
			{
				if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $uids) . ')'))
				{
					foreach ($users_attrib AS $key => $val)
					{
						unset($val['id']);
						
						foreach ($val AS $attrib_key => $attrib_val)
						{
							$data[$val['uid']][$attrib_key] = $attrib_val;
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * 批量获取多个话题关注的用户列表
	 * @param  $topics_array
	 */
	public function get_users_list_by_topic_focus($topic_ids)
	{	
		if ( !is_array($topic_ids) OR sizeof($topic_ids))
		{
			return false;
		}
		
		array_walk_recursive($topic_ids, 'intval_string');
		
		return $this->query_all("SELECT DISTINCT uid, topic_id FROM " . $this->get_table('topic_focus') . " WHERE topic_id IN(" . implode(',', $topic_ids) . ")");
	}

	/**
	 * 
	 * 根据where条件获取用户数量
	 * @param string $where
	 * @param int    $limit
	 * 
	 * @return array
	 */
	public function get_user_count($where = null)
	{
		return $this->count('users', $where);
	}

	/**
	 * 获取个人动态
	 */
	function get_user_actions($uid, $limit = 10, $actions = false, $this_uid = 0)
	{
		$cache_key = 'user_actions_' . md5($uid . $limit . $actions . $this_uid);
		
		if ($user_actions = AWS_APP::cache()->get($cache_key))
		{
			return $user_actions;
		}
		
		$action_question = ACTION_LOG::ADD_QUESTION;
		
		if (strstr($actions, ','))
		{
			$action_question = explode(',', $actions);
			
			array_walk_recursive($action_question, 'intval_string');
			
			$action_question = implode(',', $action_question);
		}
		else if ($actions)
		{
			$action_question = intval($actions);
		}
			
		if (!$uid)
		{
			$where[] = "(associate_type = " . ACTION_LOG::CATEGORY_QUESTION . " AND associate_action IN(" . $this->quote($action_question) . "))";
		}
		else
		{
			$where[] = "(associate_type = " . ACTION_LOG::CATEGORY_QUESTION . " AND uid = " . intval($uid) . " AND associate_action IN(" . $this->quote($action_question) . "))";
		}
		
		if ($this_uid == $uid)
		{
			$show_anonymous = true;
		}
	
		$action_list = ACTION_LOG::get_action_by_where(implode($where, ' OR '), $limit, $show_anonymous);
		
		// 重组信息
		foreach ($action_list as $key => $val)
		{
			$users_ids[] = $val['uid'];
						
			switch ($val['associate_type'])
			{
				case ACTION_LOG::CATEGORY_QUESTION:
					$question_ids[] = $val['associate_id'];
					
					if (in_array($val['associate_action'], array(
						ACTION_LOG::ADD_TOPIC,
						ACTION_LOG::MOD_TOPIC,
						ACTION_LOG::MOD_TOPIC_DESCRI,
						ACTION_LOG::MOD_TOPIC_PIC,
						ACTION_LOG::DELETE_TOPIC,
						ACTION_LOG::ADD_TOPIC_FOCUS,
						ACTION_LOG::DELETE_TOPIC_FOCUS,
						ACTION_LOG::ADD_TOPIC_PARENT,
						ACTION_LOG::DELETE_TOPIC_PARENT
					)) AND $val['associate_attached'])
					{
						$associate_topic_ids[] = $val['associate_attached'];
					}
					
					/*if (in_array($val['associate_action'], array(
						ACTION_LOG::ADD_QUESTION
					)) AND $question_info['has_attach'])
					{
						$has_attach_question_ids[] = $question_info['question_id'];
					}*/
				break;	
			}
		}
		
		if ($users_ids)
		{
			$action_list_users = $this->get_user_info_by_uids($users_ids, true);
		}
		
		if ($question_ids)
		{
			$action_questions_info = $this->model('question')->get_question_info_by_ids($question_ids);
			
			/*if ($this_uid)
			{
				$action_questions_focus = $this->model('question')->has_focus_questions($question_ids, $this_uid);
			}
			else if ($uid)
			{
				$action_questions_focus = $this->model('question')->has_focus_questions($question_ids, $uid);
			}*/
		}
		
		if ($associate_topic_ids)
		{
			$associate_topics = $this->model('topic')->get_topics_by_ids($associate_topic_ids);
		}
		
		/*if ($has_attach_question_ids)
		{
			$question_attachs = $this->model('publish')->get_attachs('question', $has_attach_question_ids, 'min');
		}*/
		
		foreach ($action_list as $key => $val)
		{
			$action_list[$key]['user_info'] = $action_list_users[$val['uid']];
			
			switch ($val['associate_type'])
			{
				case ACTION_LOG::CATEGORY_QUESTION :
					$question_info = $action_questions_info[$val['associate_id']];
					
					if (in_array($val['associate_action'], array(
						ACTION_LOG::ADD_TOPIC,
						ACTION_LOG::MOD_TOPIC,
						ACTION_LOG::MOD_TOPIC_DESCRI,
						ACTION_LOG::MOD_TOPIC_PIC,
						ACTION_LOG::DELETE_TOPIC,
						ACTION_LOG::ADD_TOPIC_FOCUS,
						ACTION_LOG::DELETE_TOPIC_FOCUS,
						ACTION_LOG::ADD_TOPIC_PARENT,
						ACTION_LOG::DELETE_TOPIC_PARENT
					)) AND $val['associate_attached'])
					{
						$topic_info = $associate_topics[$val['associate_attached']];
					}
					else
					{
						unset($topic_info);
					}
					
					if (in_array($val['associate_action'], array(
						ACTION_LOG::ADD_QUESTION
					)) AND $question_info['has_attach'])
					{
						$question_info['attachs'] = $question_attachs[$question_info['question_id']];
					}
					
					if ($val['uid'])
					{			
						$question_info['last_action_str'] = ACTION_LOG::format_action_str($val['associate_action'], $val['uid'], $action_list_users[$val['uid']]['user_name'], $question_info, $topic_info);
					}
					
					if (in_array($val['associate_action'], array(
						ACTION_LOG::ANSWER_QUESTION
					)) AND $question_info['answer_count'] > 0)
					{
						$answer_list = $this->model('answer')->get_answer_by_id($val['associate_attached']);
					}
					else
					{
						$answer_list = null;
					}
					
					if (! empty($answer_list))
					{
						$user_info = $this->get_user_info_by_uid($answer_list['uid'], true);
						
						$answer_list['user_name'] = $user_info['user_name'];
						$answer_list['url_token'] = $user_info['url_token'];
						$answer_list['signature'] = $user_info['signature'];
						$answer_list['answer_content'] = strip_ubb($answer_list['answer_content']);
						$question_info['answer_info'] = $answer_list;
						
						/*if ($answer_list['has_attach'])
						{
							$answer_list['attachs'] = $this->model('publish')->get_attach('answer', $val['associate_attached'], 'min');
						}*/
					}
					
					//$action_list[$key]['has_focus'] = $action_questions_focus[$question_info['question_id']];
					
					// 还原到单个数组ROW里面
					if ($question_info)
					{
						foreach ($question_info as $qkey => $qval)
						{
							if ($qkey == 'add_time')
							{
								continue;
							}
							
							$action_list[$key][$qkey] = $qval;
						}
					}
					
					//$action_list[$key]['topics'] = $action_questions_topics[$question_info['question_id']];
					break;
			}
		}
		
		AWS_APP::cache()->set($cache_key, $action_list, get_setting('cache_level_normal'));
		
		return $action_list;
	}

	public function get_user_recommend_v2($uid, $limit = 10)
	{
		if ($users_list = AWS_APP::cache()->get('user_recommend_' . $uid))
		{
			return $users_list;
		}
		
		if (!$friends = $this->model('follow')->get_user_friends($uid, 100))
		{
			return $this->get_users_list(null, $limit, true);
		}

		foreach ($friends as $key => $val)
		{
			$follow_uids[] = $val['uid'];
			$follow_users_info[$val['uid']] = $val;
		}
		
		if ($users_focus = $this->query_all("SELECT DISTINCT friend_uid, fans_uid FROM " . $this->get_table('user_follow') . " WHERE fans_uid IN (" . implode(',', $follow_uids) . ") ORDER BY follow_id DESC", $limit))
		{
			foreach ($users_focus as $key => $val)
			{
				$friend_uids[$val['friend_uid']] = $val['friend_uid'];
				
				$users_ids_recommend[$val['friend_uid']] = array(
					'type' => 'friend', 
					'fans_uid' => $val['fans_uid']
				);
			}
		}
		
		// 取我关注的话题
		if ($my_focus_topics = $this->model('topic')->get_focus_topic_list($uid, null))
		{
			foreach ($my_focus_topics as $key => $val)
			{
				$my_focus_topics_ids[] = $val['topic_id'];
				$my_focus_topics_info[$val['topic_id']] = $val;
			}
		}
		
		if ($topic_focus_uids = $this->get_users_list_by_topic_focus($my_focus_topics_ids))
		{
			foreach ($topic_focus_uids as $key => $val)
			{
				if ($friend_uids[$val['uid']])
				{
					continue;
				}
				
				$friend_uids[$val['uid']] = $val['uid'];
				
				$users_ids_recommend[$val['uid']] = array(
					'type' => 'topic', 
					'topic_id' => $val['topic_id']
				);
			}
		}
		
		if (! $friend_uids)
		{
			return $this->get_users_list('uid NOT IN (' . implode($follow_uids, ',') . ')', $limit, true);
		}
		
		if ($users_list = $this->get_users_list('uid IN(' . implode($friend_uids, ',') . ') AND uid NOT IN (' . implode($follow_uids, ',') . ')', $limit, true, true))
		{
			foreach ($users_list as $key => $val)
			{
				$users_list[$key]['type'] = $users_ids_recommend[$val['uid']]['type'];
				
				if ($users_ids_recommend[$val['uid']]['type'] == 'friend')
				{
					$users_list[$key]['friend_users'] = $follow_users_info[$users_ids_recommend[$val['uid']]['fans_uid']];
				}
				else if ($users_ids_recommend[$val['uid']]['type'] == 'topic')
				{
					$users_list[$key]['topic_info'] = $my_focus_topics_info[$users_ids_recommend[$val['uid']]['topic_id']];
				}
			}
			
			AWS_APP::cache()->set('user_recommend_' . $uid, $users_list, get_setting('cache_level_normal'));
		}
		
		return $users_list;
	}
	
	/**
	 * 根据职位ID获取职位信息
	 */
	public function get_jobs_by_id($id)
	{
		if (!$id)
		{
			return false;
		}
		
		static $jobs_info;
		
		if (!$jobs_info[$id])
		{
			$jobs_info[$id] = $this->fetch_row('jobs', 'id = ' . intval($id));
		}
		
		return $jobs_info[$id];
	}

	/**
	 * 获取头像目录文件地址
	 * @param  $uid
	 * @param  $size
	 * @param  $return_type 0=返回全部 1=返回目录(a/b/c/) 2=返回文件名
	 * @return string
	 */
	function get_avatar($uid, $size = 'min', $return_type = 0)
	{
		$size = in_array($size, array(
			'max', 
			'mid', 
			'min'
		)) ? $size : 'real';
		
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		
		if ($return_type == 1)
		{
			return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
		}
		
		if ($return_type == 2)
		{
			return substr($uid, - 2) . '_avatar_' . $size . '.jpg';
		}
		
		return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . '_avatar_' . $size . '.jpg';
	}
	
	/**
	 * 删除用户头像
	 * @param unknown_type $uid
	 * @return boolean
	 */
	function delete_avatar($uid)
	{
		if (!$uid)
		{
			return false;
		}
			
		foreach(AWS_APP::config()->get('image')->avatar_thumbnail as $key => $val)
		{
			@unlink(get_setting('upload_dir') . '/avatar/' . $this->get_avatar($uid, $key, 1) . $this->get_avatar($uid, $key, 2));
		}
		
		@unlink(get_setting('upload_dir') . '/avatar/' . $this->get_avatar($uid, '', 1) . $this->get_avatar($uid, $key, 2));
		
		return $this->update_users_fields(array('avatar_file' => ''), $uid);
	}
	
	function update_thanks_count($uid)
	{
		$answer_counter = $this->sum('answer', 'thanks_count', 'uid = ' . intval($uid));
		$question_counter = $this->sum('question', 'thanks_count', 'published_uid = ' . intval($uid));
		
		return $this->update('users', array(
			'thanks_count' => ($answer_counter + $question_counter)
		), "uid = " . intval($uid));
	}
	
	// 获取活跃用户 (非垃圾用户)
	function get_activity_random_users($limit = 10)
	{
		// 好友 & 粉丝 > 5, 回复 > 5, 根据登陆时间, 倒序		
		return $this->fetch_all('users', "fans_count > 5 AND friend_count > 5 AND answer_count > 1", 'last_login DESC', $limit);
	}
	
	function add_user_group($group_name, $type, $reputation_lower = 0, $reputation_higer = 0, $reputation_factor = 0)
	{
		return $this->insert('users_group', array(
			'type' => intval($type),
			'custom' => 1,
			'group_name' => $group_name,
			'reputation_lower' => $reputation_lower,
			'reputation_higer' => $reputation_higer,
			'reputation_factor' => $reputation_factor,
		));
	}
	
	function delete_user_group_by_id($group_id)
	{
		$this->update('users', array(
			'group_id' => 4,
		), 'group_id = ' . intval($group_id));
		
		return $this->delete('users_group', 'group_id = ' . intval($group_id));
	}
	
	function update_user_group_data($group_id, $data)
	{
		return $this->update('users_group', $data, 'group_id = ' . intval($group_id));
	}
	
	function get_user_group_by_id($group_id, $field = null)
	{
		if (!$group_id)
		{
			return false;
		}
		
		static $user_groups;
		
		if (isset($user_groups[$group_id]))
		{
			if ($field)
			{
				return $user_groups[$group_id][$field];
			}
			else
			{
				return $user_groups[$group_id];
			}
		}
		
		if (!$user_group = AWS_APP::cache()->get('user_group_' . intval($group_id)))
		{
			$user_group = $this->fetch_row('users_group', 'group_id = ' . intval($group_id));
		
			if ($user_group['permission'])
			{
				$user_group['permission'] = unserialize($user_group['permission']);
			}
			
			AWS_APP::cache()->set('user_group_' . intval($group_id), $user_group, get_setting('cache_level_normal'), 'users_group');
		}

		$user_groups[$group_id] = $user_group;
		
		if ($field)
		{
			return $user_group[$field];
		}
		else
		{
			return $user_group;
		}
	}
	
	function get_user_group_list($type = 0, $custom = null)
	{
		$where[] = 'type = ' . intval($type);
		
		if ($custom !== null)
		{
			$where[] = 'custom = ' . intval($custom);
		}
		
		if ($users_groups = $this->fetch_all('users_group', implode(' AND ', $where)))
		{
			foreach ($users_groups as $key => $val)
			{
				$group[$val['group_id']] = $val;
			}
		}
		
		return $group;
	}
	
	function get_user_group_by_reputation($reputation, $field = null)
	{
		if ($mem_groups = $this->get_user_group_list(1))
		{
			foreach ($mem_groups as $key => $val)
			{
				if ((intval($reputation) >= intval($val['reputation_lower'])) && (intval($reputation) < intval($val['reputation_higer'])))
				{
					$group = $val;
					break;
				}
			}		
		}
		else	// 若会员组为空，则返回为普通会员组
		{
			$system_groups = $this->get_user_group_list(0);
			
			$group = $system_groups[4];
		}
		
		if ($field)
		{
			return $group[$field];
		}
		
		return $group;
	}
	
	function update_user_reputation_group($uid)
	{	
		$user_info = $this->get_user_info_by_uid($uid);
	
		$user_group = $this->get_user_group($user_info['group_id']);
	
		if ($user_group['custom'] == 1)
		{
			if ($user_info['reputation_group'])
			{
				$this->update_users_fields(array(
					'reputation_group' => 0
				), $uid);
			}
		
			return false;
		}
	
		$reputation_group = $this->get_user_group_by_reputation($user_info['reputation'], 'group_id');
	
		if ($reputation_group != $user_info['reputation_group'])
		{
			return $this->update_users_fields(array(
				'reputation_group' => $reputation_group
			), $uid);
		}
	
		return false;
	}
	
	function get_user_group($group_id, $reputation_group = 0)
	{
		if ($group_id == 4 AND $reputation_group)
		{
			if ($user_group = $this->model('account')->get_user_group_by_id($reputation_group))
			{
				return $user_group;
			}
		}
		
		return $this->model('account')->get_user_group_by_id($group_id);
	}
	
	function check_url_token($url_token, $uid)
	{
		return $this->count('users', "(url_token = '" . $this->quote($url_token) . "' OR user_name = '" . $this->quote($url_token) . "') AND uid != " . intval($uid));
	}
	
	function update_url_token($url_token, $uid)
	{
		return $this->update('users', array(
			'url_token' => $url_token,
			'url_token_update' => time()
		), 'uid = ' . intval($uid));
	}
	
	function forbidden_user($uid, $status, $admin_uid)
	{
		if (!$uid)
		{
			return false;
		}
		
		$this->model('account')->update_users_fields(array(
			'forbidden' => intval($status)
		), intval($uid));
		
		return $this->insert('users_forbidden', array(
			'uid' => intval($uid),
			'status' => intval($status), 
			'admin_uid' => $admin_uid,
			'add_time' => time(),
		));
	}
	
	function get_forbidden_user_list($count = false, $order = 'uid DESC', $limit = 10)
	{
		if ($count)
		{
			return $this->count('users', 'forbidden = 1');
		}
		
		if ($user_list = $this->fetch_all('users', 'forbidden = 1', $order, $limit))
		{
			$uids = fetch_array_value($user_list, 'uid');
			
			$users_forbidden = $this->fetch_all('users_forbidden', 'uid IN (' . implode(',', $uids) . ')', 'id DESC');
			
			$admin_uids = fetch_array_value($users_forbidden, 'admin_uid');
			
			$admin_user = $this->get_user_info_by_uids($admin_uids);
			
			$forbidden_log = array();
			
			foreach($users_forbidden as $key => $log)
			{
				if (!isset($forbidden_log[$log['uid']]))
				{
					$log['admin_info'] = $admin_user[$log['admin_uid']];
					
					$forbidden_log[$log['uid']] = $log;
				} 
			}
			
			foreach ($user_list as $key => $user)
			{
				$user_list[$key]['forbidden_log'] = $forbidden_log[$user['uid']];
			}
			
			return $user_list;
		}
		else
		{
			return array();
		}
	}
	
	public function set_default_timezone($time_zone, $uid)
	{
		return $this->update('users', array(
			'default_timezone' => htmlspecialchars($time_zone)
		), 'uid = ' . intval($uid));
	}
	
	public function send_delete_message($published_uid, $question_content, $question_detail)
	{
		$message = AWS_APP::lang()->_t('你发表的问题 %s 已被管理员删除', $question_content);
		$meesage .= "\r\n----- " . AWS_APP::lang()->_t('问题内容') . " -----\r\n" . $question_detail;
		$meesage .= "\r\n-----------------------------\r\n";
		$meesage .= AWS_APP::lang()->_t('如有疑问, 请联系管理员');

		$this->model('message')->send_message($this->user_id, $question_info['published_uid'], null, $message, 0, 0);
				
		$this->model('email')->action_email('QUESTION_DEL', $published_uid, get_js_url('/inbox/'), array(
			'question_title' => $question_content,
			'question_detail' => $question_detail,
		));
		
		return true;
	}
	
	public function save_recent_topics($uid, $topic_title)
	{
		if (!$user_info = $this->get_user_info_by_uid($uid))
		{
			return false;
		}
		
		if ($user_info['recent_topics'])
		{
			$recent_topics = unserialize($user_info['recent_topics']);
		}
		
		if (!$recent_topics)
		{
			$recent_topics = array();
		}
		
		foreach ($recent_topics AS $key => $val)
		{
			if ($val == $topic_title)
			{
				return false;
			}
		}
		
		$recent_topics[] = $topic_title;
		
		rsort($recent_topics);
		
		if (count($recent_topics) > 10)
		{
			$recent_topics = array_slice($recent_topics, 0, 10);
		}
		
		return $this->update('users', array(
			'recent_topics' => serialize($recent_topics)
		), 'uid = ' . intval($uid));
	}
}
