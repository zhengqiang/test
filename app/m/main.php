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

define('IN_MOBILE', true);

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		if ($_GET['ignore_ua_check'] == 'FALSE')
		{
			HTTP::set_cookie('_ignore_ua_check', 'FALSE');
		}
	
		//HTTP::redirect('/m/login/url-' . base64_encode(get_js_url($_SERVER['QUERY_STRING'])));
		
		TPL::import_clean();
		
		TPL::import_css(array(
			'js/mobile/mobile.css',
		));
		
		TPL::import_js(array(
			'js/jquery.js',
			'js/jquery.form.js',
			'js/mobile/mobile.js',
			'js/mobile/bootstrap.js',
		));
	}
	
	public function index_action()
	{
		if (!is_mobile())
		{
			HTTP::redirect('/');
		}
		
		$this->crumb(AWS_APP::lang()->_t('首页'), '/m/');
		
		TPL::output('m/index');
	}
	
	public function question_action()
	{
		if (!is_mobile())
		{
			HTTP::redirect('/question/' . $_GET['id']);
		}
		
		if (! isset($_GET['id']))
		{
			HTTP::redirect('/m/explore/');
		}
		
		if (! $question_id = intval($_GET['id']))
		{
			H::redirect_msg(AWS_APP::lang()->_t('主题不存在或已被删除'), '/m/explore/');
		}
		
		if ($_GET['notification_id'])
		{
			$this->model('notify')->read_notification($_GET['notification_id'], $this->user_id, $_GET['ori']);
		}
		
		if (! $question_info = $this->model("question")->get_question_info_by_id($question_id))
		{
			H::redirect_msg(AWS_APP::lang()->_t('主题不存在或已被删除'), '/m/explore/');
		}
		
		$question_info['redirect'] = $this->model("question")->get_redirect($question_info['question_id']);
		
		if ($question_info['redirect']['target_id'])
		{
			$target_question = $this->model("question")->get_question_info_by_id($question_info['redirect']['target_id']);
		}
		
		if (is_numeric($_GET['rf']) and $_GET['rf'])
		{
			if ($from_question = $this->model("question")->get_question_info_by_id($_GET['rf']))
			{
				$redirect_message[] = AWS_APP::lang()->_t('从主题') . ' <a href="' . get_js_url('/m/question/' . $_GET['rf'] . '?rf=false') . '">' . $from_question['question_content'] . '</a> ' . AWS_APP::lang()->_t('跳转而来');
			}
		}
		
		if ($question_info['redirect'] and ! $_GET['rf'])
		{
			if ($target_question)
			{
				HTTP::redirect('/m/question/' . $question_info['redirect']['target_id'] . '?rf=' . $question_info['question_id']);
			}
			else
			{
				$redirect_message[] = AWS_APP::lang()->_t('重定向目标主题已被删除, 将不再重定向主题');
			}
		}
		else if ($question_info['redirect'])
		{
			if ($target_question)
			{
				$message = AWS_APP::lang()->_t('此主题将跳转至') . ' <a href="' . get_js_url('/m/question/' . $question_info['redirect']['target_id'] . '?rf=' . $question_info['question_id']) . '">' . $target_question['question_content'] . '</a>';
				
				if ($this->user_id && ($this->user_info['permission']['is_administortar'] OR $this->user_info['permission']['is_moderator'] OR (!$this->question_info['lock'] AND $this->user_info['permission']['redirect_question'])))
				{
					$message .= '&nbsp; (<a href="javascript:;" onclick="ajax_request(G_BASE_URL + \'/question/ajax/redirect/\', \'item_id=' . $question_id . '\');">' . AWS_APP::lang()->_t('撤消重定向') . '</a>)';
				}
				
				$redirect_message[] = $message;
			}
			else
			{
				$redirect_message[] = AWS_APP::lang()->_t('重定向目标主题已被删除, 将不再重定向主题');
			}
		}
		
		if ($question_info['has_attach'])
		{
			$question_info['attachs'] = $this->model('publish')->get_attach('question', $question_info['question_id'], 'min');
			$question_info['attachs_ids'] = FORMAT::parse_attachs($question_info['question_detail'], true);
		}
		
		$this->model('question')->update_views($question_id);
		
		if (get_setting('answer_unique') == 'Y')
		{
			if ($this->model('answer')->has_answer_by_uid($question_id, $this->user_id))
			{
				TPL::assign('user_answered', TRUE);
			}
		}
		
		$question_info['question_detail'] = FORMAT::parse_attachs(nl2br(FORMAT::parse_markdown($question_info['question_detail'])));
		
		TPL::assign('question_id', $question_id);
		TPL::assign('question_info', $question_info);
		TPL::assign('question_focus', $this->model("question")->has_focus_question($question_id, $this->user_id));
		TPL::assign('question_topics', $this->model('question')->get_question_topic_by_question_id($question_id));
		
		$this->crumb($question_info['question_content'], '/m/question/' . $question_id);
		
		TPL::assign('redirect_message', $redirect_message);
		
		$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_info['question_id'], calc_page_limit($_GET['page'], 20), null, 'agree_count DESC, against_count ASC, add_time ASC');
		
		TPL::assign('answers_list', $answer_list);
		
		$total_page = $question_info['answer_count'] / 20;
		
		if ($total_page > intval($total_page))
		{
			$total_page = intval($total_page) + 1;
		}
		
		if (!$_GET['page'])
		{
			$_GET['page'] = 1;
		}
		
		if ($_GET['page'] < $total_page)
		{
			$_GET['page'] = $_GET['page'] + 1;
			
			TPL::assign('next_page', $_GET['page']);
		}
		
		TPL::output('m/question');
	}
	
	public function login_action()
	{
		$url = base64_decode($_GET['url']);
		
		if (($this->user_id AND !$_GET['weixin_id']) OR $this->user_info['weixin_id'])
		{
			if ($url)
			{
				header('Location: ' . $url); 
			}
			else
			{
				HTTP::redirect('/m/');
			}
		}
		
		if ($url)
		{
			$return_url = $url;
		}
		else if (strstr($_SERVER['HTTP_REFERER'], '/m/'))
		{
			$return_url = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$return_url = get_js_url('/m/');
		}
		
		TPL::assign('r_uname', HTTP::get_cookie('r_uname'));
		TPL::assign('return_url', strip_tags($return_url));
		
		$this->crumb(AWS_APP::lang()->_t('登录'), '/m/login/');
		
		TPL::output('m/login');
	}
	
	public function register_action()
	{
		if (!is_mobile())
		{
			HTTP::redirect('/account/register/');
		}
		
		if ($this->user_id AND $_GET['invite_question_id'])
		{
			if ($invite_question_id = intval($_GET['invite_question_id']))
			{
				HTTP::redirect('/question/' . $invite_question_id);
			}
		}
		
		if (get_setting('invite_reg_only') == 'Y' AND !$_GET['icode'])
		{
			H::redirect_msg(AWS_APP::lang()->_t('本站只能通过邀请注册'), '/');
		}
		
		if ($_GET['icode'])
		{
			if ($this->model('invitation')->check_code_available($_GET['icode']))
			{
				TPL::assign('icode', $_GET['icode']);
			}
			else
			{
				H::redirect_msg(AWS_APP::lang()->_t('邀请码无效或已经使用，请使用新的邀请码'), '/');
			}
		}
		
		$this->crumb(AWS_APP::lang()->_t('注册'), '/m/register/');
		
		TPL::output('m/register');
	}
	
	public function explore_action()
	{
		if (!is_mobile())
		{
			HTTP::redirect('/home/explore/');
		}
		
		$this->crumb(AWS_APP::lang()->_t('发现'), '/m/explore/');
			
		TPL::output('m/explore');
	}
}
