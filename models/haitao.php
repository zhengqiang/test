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

class haitao_class extends AWS_MODEL
{
	public function get_question_image_attach($question_id, $question_detail = false, $size = 'stream')
	{
		if (!$question_info = $this->model('question')->get_question_info_by_id($question_id))
		{
			return false;
		}
		
		if ($question_info['cover_attach'])
		{
			$attach = $this->fetch_row('attach', "item_type = 'question' AND id = " . intval($question_info['cover_attach']));
		}
		else
		{
			$attach = $this->fetch_row('attach', "item_type = 'question' AND item_id = " . intval($question_id), "is_image DESC, id ASC");
		}
		
		if ($attach AND $size == 'stream')
		{
			$data = $this->model('publish')->parse_attach_data(array($attach), 'question', $size);

			return $data[$attach['id']]['thumb'];
		}
		else if ($attach AND $size == 'stream_sw')
		{
			$data = $this->model('publish')->parse_attach_data(array($attach), 'question', $size);
			
			$thumb_file = get_setting('upload_dir') . '/stream_thumb/' . $question_id . '.jpg';
			
			if (file_exists($thumb_file))
			{
				return get_setting('upload_url') . '/stream_thumb/' . $question_id . '.jpg';
			}
			
			if ( ! is_dir(dirname($thumb_file)))
			{
				if (! make_dir(dirname($thumb_file)))
				{
					return FALSE;
				}
			}
			
			$source_image = str_replace(get_setting('upload_url'), get_setting('upload_dir'), $data[$attach['id']]['attachment']);
			
			if (!file_exists($source_image))
			{
				return FALSE;
			}
			
			AWS_APP::image()->initialize(array(
				'quality' => 90,
				'source_image' => $source_image,
				'new_image' => $thumb_file,
				'width' => AWS_APP::config()->get('image')->attachment_thumbnail['stream']['w'],
				'clipping' => IMAGE_CORE_CM_DEFAULT,
				'scale' => IMAGE_CORE_SC_BEST_RESIZE_WIDTH
			))->resize();
			
			return get_setting('upload_url') . '/stream_thumb/' . $question_id . '.jpg';
			
		}
		else if ($question_detail)
		{
			$thumb_file = get_setting('upload_dir') . '/wp_thumb/' . $question_id . '.jpg';
			
			if (file_exists($thumb_file))
			{
				return get_setting('upload_url') . '/wp_thumb/' . $question_id . '.jpg';
			}
			
			$simple_html = simple_html_parse($question_detail);
			
			foreach($simple_html->find('img') as $element)
			{
				if ($element->src)
				{
					$image_url = $element->src;
				
					break;
				}
			}
			
			if ( ! is_dir(dirname($thumb_file)))
			{
				if (! make_dir(dirname($thumb_file)))
				{
					return FALSE;
				}
			}
			
			if (!$image_url)
			{
				return FALSE;
			}
			
			if (!$stream = @fopen($image_url, 'r'))
			{
				return '403';
			}
			
			$tmp_name = TEMP_PATH . 'xhr_' . md5($field . microtime(TRUE) . rand(1, 999)) . '.jpg';
			
			if (!$file_size = stream_copy_to_stream($stream, fopen($tmp_name, 'w')))
			{
				return '-1';
			}
			
			fclose($stream);
			
			if (!file_exists($tmp_name))
			{
				return '-1';
			}
			
			AWS_APP::image()->initialize(array(
				'quality' => 90,
				'source_image' => $tmp_name,
				'new_image' => $thumb_file,
				'width' => AWS_APP::config()->get('image')->attachment_thumbnail['stream']['w'],
				'height' => AWS_APP::config()->get('image')->attachment_thumbnail['stream']['h']
			))->resize();
			
			unlink($tmp_name);
			
			return get_setting('upload_url') . '/wp_thumb/' . $question_id . '.jpg';
		}
		
		return '';
	}
}