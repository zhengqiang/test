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

	function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		
		return $rule_action;
	}

	public function setup()
	{
		header('Content-type: text/xml; charset=UTF-8');
		
		date_default_timezone_set('UTC');
	}

	public function index_action()
	{
		if ($_GET['topic'])
		{
			$list = $this->model('question')->get_question_list_by_topic_ids($_GET['topic'], 1, 20);
		}
		else
		{
			$list = $this->model('question')->get_questions_list(1, 20, 'new', null, $_GET['category']);
		}
		
		// Modify by wecenter
		foreach ($list AS $key => $val)
		{
			if ($val['has_attach'])
			{
				$has_attach_question_ids[] = $val['question_id'];
			}
		}
		
		if ($has_attach_question_ids)
		{
			$question_attachs = $this->model('publish')->get_attachs('question', $has_attach_question_ids, 'min');
		}
		
		foreach ($list AS $key => $val)
		{
			if ($val['has_attach'])
			{
				$list[$key]['attachs'] = $question_attachs[$val['question_id']];
			}
		}
		
		TPL::assign('list', $list);
		
		TPL::output('global/feed');
	}
}