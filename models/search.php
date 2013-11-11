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

class search_class extends AWS_MODEL
{
	public function bulid_query($table, $column, $q, $where = null)
	{
		if (is_array($q))
		{
			$q = implode(' ', $q);
		}
		
		if ($analysis_keyword = $this->model('system')->analysis_keyword($q))
		{
			$keyword = implode(' ', $analysis_keyword);
		}
		else
		{
			$keyword = $q;
		}
		
		if ($where)
		{
			$where = ' AND (' . $where . ')';
		}
		
		return "SELECT *, MATCH(" . $column . "_fulltext) AGAINST('" . $this->quote($this->model('search_index')->encode_search_code($keyword)) . "' IN BOOLEAN MODE) AS score FROM " . $this->get_table($table) . " WHERE MATCH(" . $column . "_fulltext) AGAINST('" . $this->quote($this->model('search_index')->encode_search_code($keyword)) . "' IN BOOLEAN MODE) " . $where . " ORDER BY score DESC, add_time DESC";	// Modify by anwsion
	}
	
	public function get_all_result($q, $limit = 20)
	{
		$result = array_merge((array)$this->search_users($q, $limit), (array)$this->search_topics($q, $limit), (array)$this->search_questions($q, null, $limit));
		
		return $result;
	}
	
	public function search_users($q, $limit = 20)
	{
		if (is_array($q) AND sizeof($q) > 1)
		{
			$where[] = "user_name = '" . implode(' ', $q) . "' OR user_name = '" . implode('', $q) . "'";
		}
		else
		{
			if (is_array($q))
			{
				$q = implode('', $q);
			}
			
			$where[] = "user_name LIKE '" . $this->quote($q) . "%'";
		}
		
		return $this->query_all('SELECT uid, last_login FROM ' . get_table('users') . ' WHERE ' . implode(' OR ', $where), $limit);
	}

	public function search_topics($q, $limit = 20)
	{		
		if (is_array($q))
		{
			$q = implode('', $q);
		}
		
		if ($result = $this->fetch_all('topic', "topic_title LIKE '" . $this->quote($q) . "%'", null, $limit))
		{
			foreach ($result AS $key => $val)
			{
				if (!$val['url_token'])
				{
					$result[$key]['url_token'] = urlencode($val['topic_title']);
				}
			}
		}
		
		return $result;
	}
	
	public function search_questions($q, $topic_ids = '', $limit = 20)
	{
		if ($topic_ids)
		{
			$topic_ids = explode(',', $topic_ids);
			
			array_walk_recursive($topic_ids, 'intval_string');
			
			$where = "question_id IN(SELECT question_id FROM " . $this->get_table('topic_question') . " WHERE topic_id IN (" . implode(',', $topic_ids) . "))";
		}
		
		// Modify by anwsion
		if ($where)
		{
			$where .= ' AND add_time <= ' . time();
		}
		else
		{
			$where = 'add_time <= ' . time();
		}
		
		return $this->query_all($this->bulid_query('question', 'question_content', $q, $where), $limit);
	}
	
	public function search($q, $search_type, $limit = 20, $topic_ids = null)
	{		
		if (! in_array($search_type, array(
			'all', 
			'user', 
			'topic', 
			'topic_add', 
			'question'
		)))
		{
			$search_type = 'all';
		}
		
		$q = (array)explode(' ', str_replace('  ', ' ', trim($q)));
		
		foreach ($q AS $key => $val)
		{
			if (cjk_strlen($val) == 1)
			{
				unset($q[$key]);
			}
		}
		
		if (sizeof($q) == 0)
		{
			return array();
		}
		
		$data = array();
		
		switch ($search_type)
		{
			case 'all' :
				$result_list = $this->get_all_result($q, $limit);
				break;
			
			case 'user' :
				$result_list = $this->search_users($q, $limit);
				break;
			
			case 'topic' :
				$result_list = $this->search_topics($q, $limit);
				break;
			
			case 'question' :
				$result_list = $this->search_questions($q, $topic_ids, $limit);
				break;
		}
		
		if ($result_list)
		{
			foreach ($result_list as $result_info)
			{
				$result = $this->prase_result_info($result_info);
			
				if (is_array($result))
				{
					$data[] = $result;
				}
			}
		}

		return $data;
	}

	public function prase_result_info($result_info)
	{
		if (isset($result_info['last_login']))
		{
			$result_type = 3;
			
			$sno = $result_info['uid'];
				
			$user_info = $this->model('account')->get_user_info_by_uid($result_info['uid'], true);
				
			$name = $user_info['user_name'];
			
			$url = get_js_url('/people/' . $user_info['url_token']);
			
			$detail = array(
				'avatar_file' => get_avatar_url($user_info['uid'], 'mid'),	// 头像
				'signature' => $user_info['signature'],	// 签名
				'reputation' =>  $user_info['reputation'],	// 威望
				'agree_count' =>  $user_info['agree_count'],	// 赞同
				'thanks_count' =>  $user_info['thanks_count'],	// 感谢
			);
		}
		else if ($result_info['topic_id'])
		{
			$result_type = 2;
			
			$sno = $result_info['topic_id'];
			
			$url = get_js_url('/topic/' . $result_info['url_token']);
			
			$name = $result_info['topic_title'];
			
			$detail = array(
				'topic_pic'=> get_topic_pic_url('mid', $result_info['topic_pic']),	
				'topic_id' => $result_info['topic_id'],	// 话题 ID
				'focus_count' => $result_info['focus_count'],
				'discuss_count' => $result_info['discuss_count'],	// 讨论数量
				'topic_description' => $result_info['topic_description']
			);
		}
		else if ($result_info['question_id'])
		{
			$result_type = 1;
			
			$sno = $result_info['question_id'];
			
			$url = get_js_url('/question/' . $result_info['question_id']);
			
			$name = $result_info['question_content'];
			
			$detail = array(
				'best_answer' => $result_info['best_answer'],	// 最佳回复 ID
				'answer_count' => $result_info['answer_count'],	// 回复数
				'comment_count' => $result_info['comment_count'],
				'focus_count' => $result_info['focus_count'],
				'agree_count' => $result_info['agree_count']
			);
		}
		
		if ($name)
		{
			return array(
				'uid' => $result_info['uid'], 
				'type' => $result_type, 
				'url' => $url, 
				'sno' => $sno, 
				'name' => $name, 
				'detail' => $detail
			);
		}
	}
	
	/*public function is_hight_similar($input, $string)
	{
		$input_keywords = $this->model('system')->analysis_keyword($input);
		
		$string_keywords = $this->model('system')->analysis_keyword($string);
		
		$similar_count = 0;
		
		foreach ($string_keywords AS $key => $keyword)
		{
			if (in_array($keyword, $input_keywords))
			{
				$similar_count++;
			}
		}
		
		if ($similar_count / sizeof($input_keywords) >= 0.6 AND $similar_count / sizeof($string_keywords) >= 0.6)
		{
			return true;
		}
		
		return false;
	}*/
}