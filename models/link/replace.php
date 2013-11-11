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

class link_replace_class extends AWS_MODEL
{
	var $replace_array;
	
	public function fetch_list($page, $limit)
	{
		return $this->fetch_page('link_replace', null, 'id DESC', $page, $limit);
	}
	
	public function fetch_replace_array()
	{
		if ($list = $this->fetch_all('link_replace'))
		{
			foreach ($list AS $key => $val)
			{
				$data[$val['keyword']] = $val['link'];
			}
		}
		
		return $data;
	}
	
	public function replace_content($text)
	{
		if (!$this->replace_array)
		{
			$this->replace_array = $this->fetch_replace_array();
		}
		
		if ($this->replace_array)
		{
			foreach ($this->replace_array AS $key => $val)
			{
				$text = str_replace($key, '<a href="' . $val . '">' . $key . '</a>', $text);
			}
		}
		
		return $text;
	}
	
	public function add_link($keyword, $link)
	{
		return $this->insert('link_replace', array(
			'keyword' => $keyword,
			'link' => $link
		));
	}
	
	public function remove_link($id)
	{
		return $this->delete('link_replace', 'id = ' . intval($id));
	}
}