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

class crond_class extends AWS_MODEL
{
	public function start()
	{
		if (!AWS_APP::cache()->get('crond_timer_second'))
		{
			$call_actions[] = 'second';
			
			AWS_APP::cache()->set('crond_timer_second', time(), 1, 'crond');
		}
		
		if (!AWS_APP::cache()->get('crond_timer_half_minute'))
		{
			$call_actions[] = 'half_minute';
			
			AWS_APP::cache()->set('crond_timer_half_minute', time(), 30, 'crond');
		}
		
		if (!AWS_APP::cache()->get('crond_timer_minute'))
		{
			$call_actions[] = 'minute';
			
			AWS_APP::cache()->set('crond_timer_minute', time(), 60, 'crond');
		}
		
		if (date('YW', AWS_APP::cache()->get('crond_timer_week')) != date('YW', time()))
		{
			$call_actions[] = 'week';
			
			AWS_APP::cache()->set('crond_timer_week', time(), 259200, 'crond');
		}
		else if (date('Y-m-d', AWS_APP::cache()->get('crond_timer_day')) != date('Y-m-d', time()))
		{
			$call_actions[] = 'day';
			
			AWS_APP::cache()->set('crond_timer_day', time(), 259200, 'crond');
		}
		else if (!AWS_APP::cache()->get('crond_timer_hour'))
		{
			$call_actions[] = 'hour';
			
			AWS_APP::cache()->set('crond_timer_hour', time(), 3600, 'crond');
		}
		else if (!AWS_APP::cache()->get('crond_timer_half_hour'))
		{
			$call_actions[] = 'half_hour';
			
			AWS_APP::cache()->set('crond_timer_half_hour', time(), 1800, 'crond');
		}
		
		return $call_actions;
	}
	
	// 每秒执行
	public function second($user_id)
	{
		
	}
	
	// 每半分钟执行
	public function half_minute($user_id)
	{
		$this->model('edm')->run_task();
	}
	
	// 每分钟执行
	public function minute($user_id)
	{
		@unlink(TEMP_PATH . 'plugins_table.php');
		@unlink(TEMP_PATH . 'plugins_model.php');
		
		//$this->model('reputation')->calculate_by_uid($user_id);
		
		if ($this->model('reputation')->calculate(AWS_APP::cache()->get('reputation_calculate_start'), 100))
		{
			AWS_APP::cache()->set('reputation_calculate_start', (intval(AWS_APP::cache()->get('reputation_calculate_start')) + 100), 604800);
		}
		else
		{
			AWS_APP::cache()->set('reputation_calculate_start', 0, 604800);
		}
		
		// Modify by anwsion
		$this->model('ios')->process_push(100);	
		
		$this->model('online')->online_active($user_id);
		$this->model('email')->send_mail_queue(120);
	}
	
	// 每半小时执行
	public function half_hour($user_id)
	{
		
	}
	
	// 每小时执行
	public function hour($user_id)
	{
		$this->model('system')->clean_session();
	}
	
	// 每日时执行
	public function day($user_id)
	{
		$this->model('answer')->calc_best_answer();
		$this->model('question')->auto_lock_question();
		$this->model('active')->clean_expire();
	}
	
	// 每周执行
	public function week($user_id)
	{
		$this->model('system')->clean_break_attach();
		$this->model('email')->mail_queue_error_clean();
		
		if (!get_setting('db_engine') OR get_setting('db_engine') == 'MyISAM')
		{
			$this->query('OPTIMIZE TABLE `' . get_table('sessions') . '`');
		}
	}
}