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

class link_replace extends AWS_ADMIN_CONTROLLER
{
	public function setup()
	{
		TPL::assign('menu_list', $this->model('admin')->fetch_menu_list($this->user_info['group_id'], 308));
	}

	public function index_action()
	{
		$this->crumb('关键词链接', "admin/link_replace/");
		
		TPL::assign('list', $this->model('link_replace')->fetch_list($_GET['page'], 50));
		
		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_setting('base_url') . '/?/admin/link_replace/',
			'total_rows' => $this->model('link_replace')->found_rows(),
			'per_page' => 50
		))->create_links());
		
		TPL::output('admin/link_replace');	
	}
	
	public function add_action()
	{
		if (!$_POST['link'] OR !$_POST['keyword'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '请输入关键词或链接'));
		}
		
		$this->model('link_replace')->add_link($_POST['keyword'], $_POST['link']);
		
		H::ajax_json_output(AWS_APP::RSM(null, '1', null));
	}
	
	public function remove_action()
	{
		$this->model('link_replace')->remove_link($_GET['id']);
		
		H::ajax_json_output(AWS_APP::RSM(null, '1', null));
	}
}