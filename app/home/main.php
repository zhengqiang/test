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

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array(
			'browser_not_support'
		);
		
		if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
			$rule_action['actions'][] = 'explore';
		}
		
		return $rule_action;
	}

	public function setup()
	{
		if (is_mobile() AND HTTP::get_cookie('_ignore_ua_check') != 'TRUE' AND !$_GET['ignore_ua_check'])
		{
			switch ($_GET['app'])
			{
				default:
					HTTP::redirect('/m/');
				break;
			}
		}
		
		if ($_GET['ignore_ua_check'] == 'TRUE')
		{
			HTTP::set_cookie('_ignore_ua_check', 'TRUE', (time() + 3600 * 24 * 7));
		}
	}

	// Modify by wecenter
	public function index_action()
	{
		TPL::assign('feature_list', $this->model('feature')->get_feature_list());
		
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
		else	// Modify by anwsion
		{
			$hot_question = $this->model('question')->get_hot_question(null, null, null, 1, 10);
			
			TPL::assign('hot_question', $hot_question);
		}
		
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
					
		/*TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/home/' . $feature_info['url_token']), 
			'total_rows' => $this->model('question')->get_questions_list_total(),
			'per_page' => get_setting('contents_per_page')
		))->create_links());*/
		
		if ($_GET['page'] > 1)
		{
			TPL::assign('ajax_start_page', (intval($_GET['page']) + 1));
		}
		else
		{
			TPL::assign('ajax_start_page', 2);
		}
		
		foreach ($question_list AS $key => $val)
		{
			$question_ids[] = $val['question_id'];
		}
		
		if ($question_ids)
		{
			$questions_thanks = $this->model('question')->get_questions_thanks($question_ids, $this->user_id);
			
			$questions_attachs = $this->model('publish')->get_attachs('question', $question_ids, 'min');
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
			
			if ($val['has_attach'])
			{
				$question_list[$key]['attachs'] = $questions_attachs[$val['question_id']];
				$question_list[$key]['attachs_ids'] = FORMAT::parse_attachs($val['question_detail'], true);
			}
		}
		
		TPL::assign('question_list', $question_list);
		
		//TPL::assign('slide_feature', $this->model('question')->get_questions_list(1, 100, 'new', '1966'));	// 首页专题		
		TPL::assign('slide_feature', $this->model('question')->get_questions_list(1, 100, 'new', $this->model('feature')->get_topics_by_feature_id(13)));	// 首页专题
		
		TPL::output('home/index');
	}

	public function explore_action()
	{		
		if ($this->user_id)
		{
			$this->crumb(AWS_APP::lang()->_t('发现'), '/home/explore/');
		}
		
		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'home/explore'))
		{
			$nav_menu = $this->model('menu')->get_nav_menu_list(null, true);
			
			TPL::assign('feature_ids', $nav_menu['feature_ids']);
			
			unset($nav_menu['feature_ids']);
			
			TPL::assign('content_nav_menu', $nav_menu);
		}
		
		//边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'home/explore'))
		{
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);
			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}
		
		//边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'home/explore'))
		{
			$sidebar_hot_users = $this->model('module')->sidebar_hot_users($this->user_id, 5);
			
			TPL::assign('sidebar_hot_users', $sidebar_hot_users);
		}
		
		//边栏热门话题
		// Modify by wecenter
		//if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'home/explore'))
		{
			$sidebar_hot_topics = $this->model('module')->sidebar_hot_topics($_GET['category']);
			
			TPL::assign('sidebar_hot_topics', $sidebar_hot_topics);
		}
		
		//边栏专题
		if (TPL::is_output('block/sidebar_feature.tpl.htm', 'home/explore'))
		{
			$feature_list = $this->model('module')->feature_list();
			
			TPL::assign('feature_list', $feature_list);
		}
		
		if ($_GET['category'])
		{
			if (is_numeric($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}
		
		if ($category_info)
		{
			TPL::assign('category_info', $category_info);
			
			$this->crumb($category_info['title'], '/explore/category-' . $category_info['id']);
			
			$meta_description = $category_info['title'];
			
			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}
			
			TPL::set_meta('description', $meta_description);
		}
		
		// 问题
		// Modify by wecenter
		//if (TPL::is_output('block/content_question.tpl.htm', 'home/explore'))
		{	
			/*if (! $_GET['sort_type'])
			{
				$_GET['sort_type'] = 'new';
			}*/
			
			if ($_GET['sort_type'] == 'unresponsive')
			{
				$_GET['answer_count'] = '0';
			}
			
			if ($_GET['sort_type'] == 'hot')
			{
				$question_list = $this->model('question')->get_hot_question($category_info['id'], $_GET['topic_id'], $_GET['day'], $_GET['page'], get_setting('contents_per_page'));
			}
			else
			{
				$question_list = $this->model('question')->get_questions_list($_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], $_GET['topic_id'], $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
			}
				
			if ($question_list)
			{
				foreach ($question_list AS $key => $val)
				{
					if ($val['answer_count'])
					{
						$question_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
					}
				}
			}
			
			if ($category_info AND !$_GET['sort_type'] AND intval($_GET['page']) < 2)
			{
				if (HTTP::get_cookie('channelsread_status'))
				{
					$channelsread_status = unserialize(HTTP::get_cookie('channelsread_status'));
				}
				
				$channelsread_status[$category_info['id']]['question_id'] = $question_list[0]['question_id'];
				
				HTTP::set_cookie('channelsread_status', serialize($channelsread_status), time() + 60 * 60 * 24 * 30);
			}
			
			if ($_GET['page'] > 1)
			{
				TPL::assign('ajax_start_page', (intval($_GET['page']) + 1));
			}
			else
			{
				TPL::assign('ajax_start_page', 2);
			}
			
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
			}
			
			TPL::assign('question_list', $question_list);
			TPL::assign('question_list_bit', TPL::output('question/ajax/list', false));
		}
		
		// Modify by anwsion
		$article_category_ids = explode(',', ARTICLE_CATEGORY_ID);
		$trade_category_ids = explode(',', TRADE_CATEGORY_ID);
		
		if (in_array($category_info['id'], $article_category_ids))
		{
			TPL::output('home/explore_article');
		}
		else if (in_array($category_info['id'], $trade_category_ids))
		{
			TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
				//'base_url' => get_js_url('/home/explore/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . $_GET['is_recommend']), 
				'base_url' => get_js_url('/home/explore/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id']), 	// Modify by wecenter
				'total_rows' => $this->model('question')->get_questions_list_total(),
				'per_page' => get_setting('contents_per_page')
			))->create_links());
			
			TPL::output('home/explore_trade');
		}
		else
		{
			TPL::output('home/explore');
		}
	}
	
	public function browser_not_support_action()
	{
		if (!HTTP::is_browser('ie', 7) AND !HTTP::is_browser('ie', 6))
		{
			HTTP::redirect('/');
		}
		
		TPL::output('global/browser_not_support');
	}
}