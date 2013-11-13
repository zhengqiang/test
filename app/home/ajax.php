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

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER
{
	public $per_page;

	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		
		// Modify by wecenter
		$rule_action['actions'] = array(
			'index'
		);
				
		return $rule_action;
	}

	public function setup()
	{
		if (get_setting('index_per_page'))
		{
			$this->per_page = get_setting('index_per_page');
		}
		
		HTTP::no_cache_header();
	}

	public function notifications_action()
	{
		H::ajax_json_output(AWS_APP::RSM(array(
			'inbox_num' => $this->user_info['notice_unread'], 
			'notifications_num' => $this->user_info['notification_unread']
		), '1', null));
	}

	public function index_actions_action()
	{
		//if ($_GET['filter'] == 'publish')
		if ($_GET['filter'] == 'focus')
		{			
			if ($data = $this->model('question')->get_user_focus($this->user_id, (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}"))
			{				
				foreach ($data as $key => $val)
				{
					$question_ids[] = $val['question_id'];
				}
				
				/*if ($this->user_id)
				{
					$has_focus_questions = $this->model('question')->has_focus_questions($question_ids, $this->user_id);
				}*/
				
				$topics_questions = $this->model('question')->get_question_topic_by_question_ids($question_ids);
				
				foreach ($data as $key => $val)
				{
					$data[$key]['add_time'] = $val['add_time'];
					$data[$key]['update_time'] = $val['update_time'];
					
					if (! $user_info_list[$val['published_uid']])
					{
						$user_info_list[$val['published_uid']] = $this->model('account')->get_user_info_by_uid($val['published_uid'], true);
					}
					
					$data[$key]['user_info'] = $user_info_list[$val['published_uid']];
					
					$data[$key]['associate_type'] = 1;	
										
					$data[$key]['topics'] = $topics_questions[$val['question_id']];
					$data[$key]['anonymous'] = $val['anonymous'];
				}
			}
		}
		else if ($_GET['filter'] == 'public')
		{
			$data = $this->model('account')->get_user_actions(null, (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}", null, $this->user_id);
		}
		else
		{
 			$data = $this->model('index')->get_index_focus($this->user_id, (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}");
		}

		if (!is_array($data))
		{
			$data = array();
		}
		
		TPL::assign('list', $data);
		
		if ($_GET['template'] == 'm')
		{
			TPL::output('m/ajax/index_actions');
		}
		else
		{
			TPL::output('home/ajax/index_actions');
		}
	}

	public function check_actions_new_action()
	{	
		$new_count = 0;
		
		if ($data = $this->model('index')->get_index_focus($this->user_id, $this->per_page))
		{
			foreach ($data as $key => $val)
			{
				if ($val['add_time'] > intval($_GET['time']))
				{
					$new_count++;
				}
			}
		}
		H::ajax_json_output(AWS_APP::RSM(array(
			'new_count' => $new_count
		), 1, null));
	}
	
	public function draft_action()
	{
		if ($drafts = $this->model('draft')->get_all('answer', $this->user_id, intval($_GET['page']) * $this->per_page .', '. $this->per_page))
		{
			foreach ($drafts AS $key => $val)
			{
				$drafts[$key]['question_info'] = $this->model("question")->get_question_info_by_id($val['item_id']);
			}
		}
		
		TPL::assign('drafts', $drafts);
		
		if ($_GET['template'] == 'm')
		{
			TPL::output('m/ajax/draft');
		}
		else
		{
			TPL::output('home/ajax/draft');
		}
	}
	
	public function invite_action()
	{
		if ($list = $this->model('question')->get_invite_question_list($this->user_id, intval($_GET['page']) * $this->per_page .', '. $this->per_page))
		{
			$uids = array();
				
			foreach($list as $key => $val)
			{
				$uids[] = $val['sender_uid'];
			}
				
			$user_info = $this->model('account')->get_user_info_by_uids($uids);
			
			foreach($list as $key => $val)
			{
				$list[$key]['user'] = array(
					'user_name' => $user_info[$val['sender_uid']]['user_name'],
				);
			}
		}
		
		if($this->user_info['invite_count'] != count($list))
		{
			$this->model('account')->update_question_invite_count($this->user_id);
		}
		
		TPL::assign('list', $list);
		
		if ($_GET['template'] == 'm')
		{
			TPL::output('m/ajax/invite');
		}
		else
		{
			TPL::output('home/ajax/invite');
		}
	}
	
	// Modify by wecenter
	public function index_action()
	{
		if ($_GET['id'])
		{
			if ($feature_info = $this->model('feature')->get_feature_by_url_token($_GET['id']))
			{
				$this->crumb($feature_info['title'], '/home/' . $feature_info['url_token']);
				
				TPL::assign('feature_info', $feature_info);
				
				$topic_ids = $this->model('feature')->get_topics_by_feature_id($feature_info['id']);
			
				TPL::assign('hot_topics', $this->model('topic')->get_topics_by_ids($this->model('feature')->get_topics_by_feature_id($feature_info['id'])));
			}
		}
		
		// Modify by wecenter
		if (!$topic_ids AND $this->user_id)
		{
			$topic_ids = $this->model('topic')->get_focus_topic_ids_by_uid($this->user_id);
		}
		
		if ($this->user_id)
		{
			$pull_index_uids = $this->model('follow')->get_user_friends_ids($this->user_id);
		}
		
		if (!$pull_index_uids AND get_setting('pull_index_uids'))
		{
			$pull_index_uids = explode(',', get_setting('pull_index_uids'));
		}
		
		if (!@in_array($this->user_id, $pull_index_uids))
		{
			$pull_index_uids[] = $this->user_id;
		}
		
		$trade_category_ids = explode(',', TRADE_CATEGORY_ID);
		
		$question_list = $this->model('question')->get_questions_list($_GET['page'], get_setting('contents_per_page'), null, $topic_ids, null, null, null, false, $pull_index_uids, true, $_GET['id'], $trade_category_ids);
		
		// Modify by wecenter
		foreach ($question_list AS $key => $val)
		{
			$question_ids[] = $val['question_id'];
		}
		
		if ($question_ids)
		{
			$questions_thanks = $this->model('question')->get_questions_thanks($question_ids, $this->user_id);
		}
		
		foreach ($question_list AS $key => $val)
		{
			if ($questions_thanks[$val['question_id']])
			{
				$question_list[$key]['question_thanks'] = $questions_thanks[$val['question_id']];
			}
			else
			{
				$question_list[$key]['question_thanks'] = false;
			}
			
			if (!strstr($val['question_detail'], '<p>'))
			{
				$question_list[$key]['question_detail'] = FORMAT::parse_attachs(nl2br(FORMAT::parse_markdown($val['question_detail'])));
			}
		}
		
		TPL::assign('question_list', $question_list);
		
		if ($_GET['template'] == 'm')
		{
			TPL::output('m/ajax/index');
		}
		else
		{
			TPL::output('home/ajax/index');
		}
	}
}