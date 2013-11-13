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

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

define('IN_MOBILE', true);

class baidu extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		HTTP::no_cache_header();
		
		if ($_POST['k'] != '742ae48216250c03d364ccc50a900ee9')
		{
			echo json_encode(array(
				'errorno' => 10004,
				'errormsg' => 'invalid key'
			));
			
			die;
		}
	}
	
	public function index_action()
	{
		$output = array(
			'errorno' => 0,
			'errormsg' => ''
		);
		
		if ($questions_list = $this->model('question')->get_questions_list(1, 40, null, null, 1))
		{
			foreach ($questions_list AS $key => $val)
			{
				$category_id = 11;
				
				/*if ($val['topics'])
				{
					foreach ($val['topics'] AS $topic_key => $topic_val)
					{
						if ($topic_in_feature_ids = $this->model('feature')->get_topic_in_feature_ids($topic_val['topic_id']))
						{
							$category_id = $topic_in_feature_ids[0];
							
							break;
						}
					}
				}*/
				
				$store_name = '极客海淘';
				
				if ($val['buy_link'])
				{
					$buy_link = parse_url($val['buy_link']);
				
					$buy_link_host = explode('.', $buy_link['host']);
					
					krsort($buy_link_host);
					
					foreach ($buy_link_host AS $link_key => $link_val)
					{
						if (strlen($link_val) > 3)
						{
							$store_name = ucfirst($link_val);
							
							break;
						}
					}
				}
				
				$recomm_reason = explode("\n", strip_ubb(strip_tags(str_replace("\r", "\n", trim($val['question_detail'])))));
				
				foreach ($recomm_reason AS $recomm_key => $recomm_val)
				{
					if (strlen($recomm_val) > 10)
					{
						$recomm_reason_text = $recomm_val;
						
						break;
					}
				}
				
				$output['data'][] = array(
					'item_type' => 1,
					'post_title' => $val['question_content'],
					'sub_title' => $val['sub_title'],
					'id' => $val['question_id'],
					'recomm_reason' => $recomm_reason_text,
					'recomm_reason_full' => strip_ubb(strip_tags(FORMAT::parse_markdown($val['question_detail']), '<a>')),
					'store_name' => $store_name,
					'category_id' => $category_id,
					'url' => get_js_url('/t/' . $val['question_id'] . '?r=hao123'),
					'post_status' => 0,
					'image' => array(
						'url' => get_question_image_attach($val['question_id'], $val['question_detail'])
					)
				);
			}
		}
		
		echo json_encode($output);
	}
}