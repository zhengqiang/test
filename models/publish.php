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

class publish_class extends AWS_MODEL
{
	public function approval_publish($id)
	{
		if (!$approval_item = $this->get_approval_item($id))
		{
			return false;
		}
		
		// Modify by wecenter
		$approval_item['data']['add_time'] = time();
		
		switch ($approval_item['type'])
		{			
			case 'question':
				$this->publish_question($approval_item['data']['question_content'], $approval_item['data']['question_detail'], $approval_item['data']['category_id'], $approval_item['uid'], $approval_item['data']['topics'], $approval_item['data']['anonymous'], $approval_item['data']['attach_access_key'], $approval_item['data']['ask_user_id'], $approval_item['data']['permission_create_topic'], $approval_item['data']['buy_link'], $approval_item['data']['add_time'], $approval_item['data']['sub_title']);	// Modify by wecentr
			break;
			
			case 'answer':
				$this->publish_answer($approval_item['data']['question_id'], $approval_item['data']['answer_content'], $approval_item['uid'], $approval_item['data']['anonymous'], $approval_item['data']['attach_access_key'], $approval_item['data']['auto_focus']);
			break;
		}
		
		$this->delete('approval', 'id = ' . intval($id));
		
		return true;
	}
	
	public function decline_publish($id)
	{
		if (!$approval_item = $this->get_approval_item($id))
		{
			return false;
		}
		
		switch ($approval_item['type'])
		{
			case 'question':
			case 'answer':
				$this->delete('approval', 'id = ' . intval($id));
				
				if ($approval_item['data']['attach_access_key'])
				{
					if ($attachs = $this->get_attach_by_access_key($approval_item['type'], $approval_item['data']['attach_access_key']))
					{
						foreach ($attachs AS $key => $val)
						{
							$this->remove_attach($val['id'], $val['access_key']);
						}
					}
				}
			break;
		}
		
		return true;
	}
	
	public function publish_answer($question_id, $answer_content, $uid, $anonymous = null, $attach_access_key = null, $auto_focus = true)
	{
		if (!$question_info = $this->model('question')->get_question_info_by_id($question_id))
		{
			return false;
		}
		
		$answer_id = $this->model('answer')->save_answer($question_id, $answer_content, $uid, $anonymous);
			
		if ($at_users = $this->model('question')->parse_at_user($answer_content, false, true))
		{
			foreach ($at_users as $user_id)
			{
				if ($user_id != $uid)
				{
					$this->model('notify')->send($uid, $user_id, notify_class::TYPE_ANSWER_AT_ME, notify_class::CATEGORY_QUESTION, $question_info['question_id'], array(
						'from_uid' => $uid, 
						'question_id' => $question_info['question_id'], 
						'item_id' => $answer_id,
						'anonymous' => intval($anonymous)
					));
				}
			}
		}
		
		set_human_valid('answer_valid_hour');
		
		if ($auto_focus)
		{
			if (! $this->model('question')->has_focus_question($question_id, $uid))
			{
				$this->model('question')->add_focus_question($question_id, $uid, $anonymous, false);
			}
		}
			
		// 记录日志
		ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ANSWER_QUESTION, htmlspecialchars($answer_content), $question_id);
			
		ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ANSWER_QUESTION, htmlspecialchars($answer_content), $answer_id, 0, intval($anonymous));
			
		// Modify by wecenter
		if ($question_info['published_uid'] != $uid)
		{
			if (!$this->model('integral')->fetch_log($this->user_id, 'ANSWER_QUESTION_' . gmdate('Ymd')))
			{
				$this->model('integral')->process($uid, 'ANSWER_QUESTION_' . gmdate('Ymd'), get_setting('integral_system_config_new_answer'), '回答主题 #' . $question_id, $question_id);
			}
				
			//$this->model('integral')->process($question_info['published_uid'], 'QUESTION_ANSWER', - get_setting('integral_system_config_new_answer'), '主题被回答 #' . $question_id, $question_id);
		}
			
		$this->model('question')->save_last_answer($question_id, $answer_id);
			
		$question_info = $this->model('question')->get_question_info_by_id($question_id);
			
		$notify_uids = array();
			
		if ($focus_uids = $this->model('question')->get_focus_uid_by_question_id($question_id))
		{
			foreach ($focus_uids as $focus_user)
			{
				if ($focus_user['uid'] != $uid)
				{
					$this->model('email')->action_email('NEW_ANSWER', $focus_user['uid'], get_js_url('/question/' . $question_id), array(
						'question_title' => $question_info['question_content']
					));
					
					$notify_uids[] = $focus_user['uid'];
				
					$this->model('notify')->send($uid, $focus_user['uid'], notify_class::TYPE_NEW_ANSWER, notify_class::CATEGORY_QUESTION, $question_id, array(
						'question_id' => $question_id, 
						'from_uid' => $uid, 
						'item_id' => $answer_id, 
						'anonymous' => intval($anonymous)
					));
				}
			}
		}
			
		// 删除回复邀请
		$this->model('question')->answer_question_invite($question_id, $uid);
			
		if ($attach_access_key)
		{
			$this->model('publish')->update_attach('answer', $answer_id, $attach_access_key);
		}
			
		$this->model('question')->delete_question_uninterested($uid, $question_id);
		
		if ($fake_id = $this->model('wecenter')->get_wechat_fake_id('question', $question_id))
		{
			$answer_user = $this->model('account')->get_user_info_by_uid($uid);
			
			$this->model('wecenter')->send_wechat_message($fake_id, "您的问题 [" . $question_info['question_content'] . "] 收到了 " . $answer_user['user_name'] . " 的回答:\n\n" . strip_tags($answer_content) . "\n\n\n<a href=\"" . get_js_url('/question/' . $question_id) . "\">点击查看问题详情</a>");
		}
		
		return $answer_id;
	}
	
	public function publish_approval($type, $data, $uid, $attach_access_key = null)
	{
		if ($attach_access_key)
		{
			$this->shutdown_update('attach', array(
				'wait_approval' => 1
			), "access_key = '" . $this->quote($attach_access_key) . "'");
		}
		
		return $this->insert('approval', array(
			'type' => $type,
			'data' => serialize($data),
			'uid' => intval($uid),
			'time' => time()
		));
	}
	
	public function publish_question($question_content, $question_detail, $category_id, $uid, $topics = null, $anonymous = null, $attach_access_key = null, $ask_user_id = null, $create_topic = true, $buy_link = null, $add_time = null, $sub_title = null)	// Modify by wecentr
	{
		// Modify by wecentr
		if ($question_id = $this->model('question')->save_question($question_content, $question_detail, $uid, $anonymous, null, $buy_link, $add_time, $sub_title))
		{
			set_human_valid('question_valid_hour');
			
			$this->model('question')->update_question_category($question_id, $category_id);
			
			if (is_array($topics))
			{
				foreach ($topics as $key => $topic_title)
				{
					$topic_id = $this->model('topic')->save_topic($question_id, $topic_title, $uid, 0, null, $create_topic);
					
					$this->model('question')->save_link($topic_id, $question_id);
				}
			}
			
			if ($attach_access_key)
			{
				$this->model('publish')->update_attach('question', $question_id, $attach_access_key);
			}
			
			if ($ask_user_id)
			{
				$this->model('question')->add_invite($question_id, $uid, $ask_user_id);
				
				$this->model('notify')->send($uid, $ask_user_id, notify_class::TYPE_INVITE_QUESTION, notify_class::CATEGORY_QUESTION, $question_id, array(
					'from_uid' => $uid,
					'question_id' => $question_id,
				));
				
				$user_info = $this->model('account')->get_user_info_by_uid($uid);
				
				$this->model('email')->action_email('QUESTION_INVITE', $ask_user_id, get_js_url('/question/' . $question_id), array(
					'user_name' => $user_info['user_name'], 
					'question_title' => $question_content
				));
			}
			
			// 自动关注该问题
			$this->model('question')->add_focus_question($question_id, $uid, $anonymous, false);
			
			// 记录日志
			ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_QUESTION, htmlspecialchars($question_content), htmlspecialchars($question_detail), 0, intval($anonymous));
			
			// Modify by wecenter
			switch ($category_id)
			{
				case 1:
					$this->model('integral')->process($uid, 'NEW_QUESTION', 5, '优惠分享 #' . $question_id, $question_id);
				break;
				
				case 2:
					$this->model('integral')->process($uid, 'NEW_QUESTION', 10, '开箱晒单 #' . $question_id, $question_id);
				break;
				
				case 6:
					$this->model('integral')->process($uid, 'NEW_QUESTION', 15, '经验分享 #' . $question_id, $question_id);
				break;
			}
		}
		
		return $question_id;
	}
	
	public function update_attach($item_type, $item_id, $attach_access_key)
	{
		if (! $attach_access_key)
		{
			return false;
		}
		
		if ($this->update('attach', array(
			'item_id' => intval($item_id)
		), "item_type = '" . $this->quote($item_type) . "' AND item_id = 0 AND access_key = '" . $this->quote($attach_access_key) . "'"))
		{
			return $this->shutdown_update($item_type, array('has_attach' => 1), $item_type . '_id = ' . intval($item_id));
		}
	}

	public function add_attach($item_type, $file_name, $attach_access_key, $add_time, $file_location, $is_image = false)
	{
		if ($is_image)
		{
			$is_image = 1;
		}
		
		return $this->insert('attach', array(
			'file_name' => htmlspecialchars($file_name), 
			'access_key' => $attach_access_key, 
			'add_time' => $add_time, 
			'file_location' => htmlspecialchars($file_location), 
			'is_image' => $is_image,
			'item_type' => $item_type
		));
	}

	public function remove_attach($id, $access_key)
	{
		$attach = $this->fetch_row('attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
		
		if (! $attach)
		{
			return false;
		}
		
		$this->delete('attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
		
		if (!$this->fetch_row('attach', 'item_id = ' . $attach['item_id']))
		{
			$this->shutdown_update($attach['item_type'], array(
				'has_attach' => 0
			), $attach['item_type'] . '_id = ' . $attach['item_id']);
		}
		
		if ($attach['item_type'] == 'question')
		{
			$attach['item_type'] = 'questions';
		}
		
		foreach(AWS_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
		{
			@unlink(get_setting('upload_dir').'/' . $attach['item_type'] . '/' . date('Ymd/', $attach['add_time']) . $val['w'] . 'x' . $val['h'] . '_' . $attach['file_location']);
		}
		
		@unlink(get_setting('upload_dir').'/' . $attach['item_type'] . '/' . date('Ymd/', $attach['add_time']) . $attach['file_location']);
		
		return true;
	}
	
	public function get_attach_by_id($id)
	{
		if ($attach = $this->fetch_row('attach', 'id = ' . intval($id)))
		{
			$data = $this->parse_attach_data(array($attach), $attach['item_type'], 'square');
		
			return $data[$id];
		}
		
		return false;
	}
	
	public function parse_attach_data($attach, $item_type, $size)
	{
		if (!$attach OR !$item_type)
		{
			return false;
		}
		
		foreach ($attach as $key => $data)
		{
			if ($item_type == 'question')
			{
				$item_type = 'questions';
			}
			
			// Fix 2.0 attach time zone bug
			$date_dir = gmdate('Ymd', $data['add_time']);
			
			if (! file_exists(get_setting('upload_dir') . '/' . $item_type . '/' . $date_dir . '/' . $data['file_location']))
			{
				$date_dir = gmdate('Ymd', ($data['add_time'] + 86400));
			}
			
			if (! file_exists(get_setting('upload_dir') . '/' . $item_type . '/' . $date_dir . '/' . $data['file_location']))
			{
				$date_dir = gmdate('Ymd', ($data['add_time'] - 86400));
			}
				
			$attach_list[$data['id']] = array(
				'id' => $data['id'], 
				'is_image' => $data['is_image'], 
				'file_name' => $data['file_name'], 
				'access_key' => $data['access_key'], 
				'attachment' => get_setting('upload_url') . '/' . $item_type . '/' . $date_dir . '/' . $data['file_location'],
			);
				
			if ($data['is_image'] == 1)
			{
				$attach_list[$data['id']]['thumb'] = get_setting('upload_url') . '/' . $item_type. '/' . $date_dir . '/' . AWS_APP::config()->get('image')->attachment_thumbnail[$size]['w'] . 'x' . AWS_APP::config()->get('image')->attachment_thumbnail[$size]['h'] . '_' . $data['file_location'];
			}
		}
		
		return $attach_list;
	}
	
	public function get_attach($item_type, $item_id, $size = 'square')
	{
		$attach = $this->fetch_all('attach', "item_type = '" .  $this->quote($item_type). "' AND item_id = " . intval($item_id), "is_image DESC, id ASC");
		
		return $this->parse_attach_data($attach, $item_type, $size);
	}
	
	public function get_attachs($item_type, $item_ids, $size = 'square')
	{
		if (!is_array($item_ids))
		{
			return false;
		}
		
		$attach_list = array();
		
		array_walk_recursive($item_ids, 'intval_string');

		if (!$attachs = $this->fetch_all('attach', "item_type = '" .  $this->quote($item_type). "' AND item_id IN (" . implode(',', $item_ids) . ")", "is_image DESC, id ASC"))
		{
			return false;
		}
		
		foreach ($attachs AS $key => $val)
		{
			$result[$val['item_id']][] = $val;
		}
		
		foreach ($result AS $key => $val)
		{
			$result[$key] = $this->parse_attach_data($val, $item_type, $size);
		}
		
		return $result;
	}
	
	public function get_attach_by_access_key($item_type, $access_key, $size = 'square')
	{
		$attach = $this->fetch_all('attach', "item_type = '" .  $this->quote($item_type). "' AND access_key = '" . $this->quote($access_key) . "'", "is_image DESC, id ASC");
		
		return $this->parse_attach_data($attach, $item_type, $size);
	}

	public function get_file_class($file_name)
	{
		switch (strtolower(H::get_file_ext($file_name)))
		{
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'bmp':
			case 'png':
				return 'image';
				break;
			
			case '3ds' :
				return '3ds';
				break;
			
			case 'ace' :
			case 'zip' :
			case 'rar' :
			case 'gz' :
			case 'tar' :
			case 'cab' :
			case '7z' :
				return 'zip';
				break;
			
			case 'ai' :
			case 'psd' :
			case 'cdr' :
				return 'gif';
				break;
			
			default :
				return 'txt';
				break;
		}
	}
	
	public function get_approval_list($type, $page, $per_page)
	{
		if ($approval_list = $this->fetch_page('approval', "`type` = '" . $this->quote($type) . "'", 'time ASC', $page, $per_page))
		{
			foreach ($approval_list AS $key => $val)
			{
				$approval_list[$key]['data'] = unserialize($val['data']);
			}
		}
		
		return $approval_list;
	}
	
	public function get_approval_item($id)
	{
		if ($approval_item = $this->fetch_row('approval', 'id = ' . intval($id)))
		{
			$approval_item['data'] = unserialize($approval_item['data']);
		}
		
		return $approval_item;
	}
}
