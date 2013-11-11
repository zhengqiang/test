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


if (!defined('IN_ANWSION'))
{
	die;
}

class coin_class extends AWS_MODEL
{
	public function process($uid, $action, $coin, $note = '', $item_id = null)
	{		
		if ($coin == 0)
		{
			return false;
		}
		
		$log_id = $this->log($uid, $action, $coin, $note, $item_id);
		
		$this->sum_integral($uid, $coin);
		
		return $log_id;
	}
	
	public function fetch_log($uid, $action)
	{
		return $this->fetch_row('integral_log', 'uid = ' . intval($uid) . ' AND action = \'' . $this->quote($action) . '\'');
	}
	
	public function log($uid, $action, $coin, $note = '', $item_id = null)
	{		
		if ($user_info = $this->model('account')->get_user_info_by_uid($uid))
		{
			return $this->insert('coin_log', array(
				'uid' => intval($uid),
				'action' => $action,
				'coin' => (int)$coin,
				'balance' => ((int)$user_info['integral'] + (int)$coin),
				'note' => $note,
				'item_id' => (int)$item_id,
				'time' => time()
			));
		}
	}
	
	// 根据日志计算积分
	public function sum_integral($uid, $coin)
	{
		return $this->query("UPDATE " . get_table('users') . " SET coin = coin + " . intval($coin) . " WHERE uid = " . intval($uid));
	}
	
	public function parse_log_item($parse_items)
	{
		if (!is_array($parse_items))
		{
			return false;
		}
		
		foreach ($parse_items AS $log_id => $item)
		{
			if (strstr($item['action'], 'ANSWER_FOLD_'))
			{
				$item['action'] = 'ANSWER_FOLD';
			}
			
			// Modify by wecenter
			if (strstr($item['action'], 'PULL_INDEX_'))
			{
				$item['action'] = 'PULL_INDEX';
			}
			
			if (strstr($item['action'], 'QUESTION_THANKS_'))
			{
				$item['action'] = 'QUESTION_THANKS';
			}
			
			if (strstr($item['action'], 'ANSWER_QUESTION_'))
			{
				$item['action'] = 'ANSWER_QUESTION';
			}
			
			switch ($item['action'])
			{
				case 'NEWS_QUESTION':
				case 'ANSWER_QUESTION':
				case 'QUESTION_ANSWER':
				case 'INVITE_ANSWER':
				case 'ANSWER_INVITE':
				case 'THANKS_QUESTION':
				case 'QUESTION_THANKS':
				case 'PULL_INDEX': // Modify by wecenter
				case 'AWARD': // Modify by wecenter
					if ($item['item_id'])
					{
						$question_ids[] = $item['item_id'];
					}
				break;
				
				case 'ANSWER_THANKS':
				case 'THANKS_ANSWER':
				case 'ANSWER_FOLD':
				case 'BEST_ANSWER':
					$answer_ids[] = $item['item_id'];
				break;
				
				case 'INVITE':
					$user_ids[] = $item['item_id'];
				break;
			}
		}
		
		if ($question_ids)
		{
			$questions_info = $this->model('question')->get_question_info_by_ids($question_ids);
		}
		
		if ($answer_ids)
		{
			$answers_info = $this->model('answer')->get_answers_by_ids($answer_ids);
		}
		
		if ($user_ids)
		{
			$users_info = $this->model('account')->get_user_info_by_uids($user_ids);
		}
		
		foreach ($parse_items AS $log_id => $item)
		{
			if (!$item['item_id'])
			{
				continue;
			}
			
			if (strstr($item['action'], 'ANSWER_FOLD_'))
			{
				$item['action'] = 'ANSWER_FOLD';
			}
			
			switch ($item['action'])
			{
				case 'NEWS_QUESTION':
				case 'ANSWER_INVITE':
				case 'ANSWER_QUESTION':
				case 'QUESTION_ANSWER':
				case 'INVITE_ANSWER':
				case 'THANKS_QUESTION':
				case 'QUESTION_THANKS':
				case 'PULL_INDEX': // Modify by wecenter
				case 'AWARD': // Modify by wecenter
					if ($questions_info[$item['item_id']])
					{
						$result[$log_id] = array(
							'title' => '主题: ' . $questions_info[$item['item_id']]['question_content'],
							'url' => get_js_url('/question/' . $item['item_id'])
						);
					}
					
				break;
				
				case 'ANSWER_THANKS':
				case 'THANKS_ANSWER':
				case 'ANSWER_FOLD':
				case 'BEST_ANSWER':
					if ($answers_info[$item['item_id']])
					{
						$result[$log_id] = array(
							'title' => '答案: ' . cjk_substr($answers_info[$item['item_id']]['answer_content'], 0, 24, 'UTF-8', '...'),
							'url' => get_js_url('/question/id-' . $answers_info[$item['item_id']]['question_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
						);
					}
				break;
				
				case 'INVITE':
					if ($users_info[$item['item_id']])
					{
						$result[$log_id] = array(
							'title' => '会员: ' . $users_info[$item['item_id']]['user_name'],
							'url' => get_js_url('/people/' . $users_info[$item['item_id']]['uid'])
						);
					}
				break;
			}
		}
		
		return $result;
	}
}