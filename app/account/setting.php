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

class setting extends AWS_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array();
		
		return $rule_action;
	}

	function setup()
	{
		$this->crumb(AWS_APP::lang()->_t('设置'), '/account/setting/');
		
		TPL::import_css('css/user-setting.css');
	}

	function index_action()
	{
		HTTP::redirect('/account/setting/profile/');
	}

	function profile_action()
	{		
		if ($this->user_info['birthday'] != 0)
		{
			TPL::assign('birthday_y_s', date('Y', $this->user_info['birthday']));
			TPL::assign('birthday_m_s', date('m', $this->user_info['birthday']));
			TPL::assign('birthday_d_s', date('d', $this->user_info['birthday']));
		}
				
		for ($i = date('Y'); $i > 1900; $i --)
		{
			$years[$i] = $i;
		}
		
		TPL::assign('birthday_y', $years);
		
		// 月符值
		TPL::assign('birthday_m', array(
			0 => '', 
			1 => 1, 
			2 => 2, 
			3 => 3, 
			4 => 4, 
			5 => 5, 
			6 => 6, 
			7 => 7, 
			8 => 8, 
			9 => 9, 
			10 => 10, 
			11 => 11, 
			12 => 12
		));
		
		for ($tmp_i = 1; $tmp_i <= 31; $tmp_i ++)
		{
			$day_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('birthday_d', $day_array);
		
		TPL::assign('job_list', $this->model('work')->get_jobs_list());
		
		TPL::assign('education_experience_list', $this->model('education')->get_education_experience_list($this->user_id));
		
		$jobs_list = $this->model('work')->get_jobs_list();
		
		if ($work_experience_list = $this->model('work')->get_work_experience_list($this->user_id))
		{
			foreach ($work_experience_list as $key => $val)
			{
				$work_experience_list[$key]['job_name'] = $jobs_list[$val['job_id']];
			}
		}
		
		TPL::assign('work_experience_list', $work_experience_list);
		
		$this->crumb(AWS_APP::lang()->_t('基本资料'), '/account/setting/profile/');
		
		TPL::import_js('js/ajaxupload.js');
		
		TPL::output('account/setting/profile');
	}
	
	function privacy_action()
	{
		TPL::assign('notification_settings', $this->model('account')->get_notification_setting_by_uid($this->user_id));
		TPL::assign('notify_actions', $this->model('notify')->notify_action_details);
		
		$this->crumb(AWS_APP::lang()->_t('隐私/提醒'), '/account/setting/privacy');
		
		TPL::output('account/setting/privacy');
	}
		
	function openid_action()
	{
		$sina_weibo = $this->model('openid_weibo')->get_users_sina_by_uid($this->user_id);
		$qq_weibo = $this->model('openid_qq_weibo')->get_users_qq_by_uid($this->user_id);
		$qq = $this->model('openid_qq')->get_user_info_by_uid($this->user_id);
		
		TPL::assign('sina_weibo', $sina_weibo);
		TPL::assign('qq_weibo', $qq_weibo);
		TPL::assign('qq', $qq);
		
		$this->crumb(AWS_APP::lang()->_t('账号绑定'), '/account/setting/openid/');
		
		TPL::output('account/setting/openid');
	}
	
	function integral_action()
	{
		$this->crumb(AWS_APP::lang()->_t('我的积分'), '/account/setting/integral/');
		
		TPL::output('account/setting/integral');
	}
	
	function security_action()
	{
		$this->crumb(AWS_APP::lang()->_t('安全设置'), '/account/setting/security/');
		
		TPL::output('account/setting/security');
	}
	
	function verify_action()
	{		
		TPL::assign('verify_apply', $this->model('verify')->fetch_apply($this->user_id));
		
		$this->crumb(AWS_APP::lang()->_t('申请认证'), '/account/setting/verify/');
		
		TPL::output('account/setting/verify');
	}
	
	// Modify by anwsion
	function coin_action()
	{
		$this->crumb(AWS_APP::lang()->_t('我的金币'), '/account/setting/coin/');
		
		TPL::output('account/setting/coin');
	}
}
