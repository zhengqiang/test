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

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "black"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		
		return $rule_action;
	}
	
	public function index_action()
	{
		$url = str_replace('&amp;', '&', base64_decode($_GET['id']));
		
		$query_string_tag = '?';
		
		if (strstr($url, '?'))
		{
			$query_string_tag = '&';
		}
		
		if (strstr(strtolower($url), 'amazon.com/'))
		{
			if (substr($url, 0, -1) != '/')
			{
				$url .= '/';
			}
			
			$ptype = 'prodid';
			
			if (strstr($url, '/gp/'))
			{
				preg_match_all('#/gp/product/(.*)/#siU', $url, $matchs);
				
				$prodid = $matchs[1][0];
				
				$ptype = 'asin';
			}
			else if (strstr($url, '/dp/'))
			{
				preg_match_all('#/dp/(.*)/#siU', $url, $matchs);
				
				$prodid = $matchs[1][0];
			}
			else if (strstr($url, 'asin=')) 
			{
				$url_param = explode('asin=', $url);
				
				$url_param = explode('&', $url_param[1]);
				
				$prodid = $url_param[0];
				
				$ptype = 'asin';
			}
			else
			{
				$url_param = explode('prodid=', $url);
				
				$url_param = explode('&', $url_param[1]);
				
				$prodid = $url_param[0];
			}
			
			$amazon_tag = 'nptd-20';
			
			if ($_GET['uid'])
			{
				if ($user_info = $this->model('account')->get_user_info_by_uid($_GET['uid']))
				{
					$user_group = $this->model('account')->get_user_group($user_info['group_id'], $user_info['reputation_group']);
					
					if ($user_group['permission']['custom_amazon_tag'] AND $user_info['amazon_tag'])
					{
						$amazon_tag = $user_info['amazon_tag'];
					}
				}
			}
			
			header('HTTP/1.1 301 Moved Permanently');
			
			if (!$prodid)
			{
				if (strstr($url, '?'))
				{
					header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . '&tag=' . $amazon_tag);
				}
				else
				{
					header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . '?tag=' . $amazon_tag);
				}
				die;
			}
			
			if ($ptype == 'asin')
			{
				header('Location: ' . 'http://www.amazon.com/gp/product/' . $prodid . '/?tag=' . $amazon_tag);
			}
			else
			{
				header('Location: ' . 'http://www.amazon.com/dp/' . $prodid . '/?tag=' . $amazon_tag);
			}
		}
		else if (strstr(strtolower($url), 'yihaodian.com/') OR strstr(strtolower($url), 'yhd.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'tracker_u=104632634');
		}
		else if (strstr(strtolower($url), 'ebags.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'sourceID=COMJFEED&PID=6416433&CA_6C15C=120011660000321624');
		}
		else if (strstr(strtolower($url), 'newegg.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'cm_mmc=CPS-_-naplesblue-_-naplesblue-_-eventcode');
		}
		else if (strstr(strtolower($url), 'backcountry.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'AID=306722&PID=6416433');
		}
		else if (strstr(strtolower($url), 'myhabit.com/'))
		{
			$amazon_tag = 'nptd-20';
			
			if ($_GET['uid'])
			{
				if ($user_info = $this->model('account')->get_user_info_by_uid($_GET['uid']))
				{
					$user_group = $this->model('account')->get_user_group($user_info['group_id'], $user_info['reputation_group']);
					
					if ($user_group['permission']['custom_amazon_tag'] AND $user_info['amazon_tag'])
					{
						$amazon_tag = $user_info['amazon_tag'];
					}
				}
			}
			
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . '&tag=' . $amazon_tag);
		}
		else if (strstr(strtolower($url), 'woot.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'utm_campaign=Commission+Junction+-+10860750&utm_source=Commission+Junction+Publisher+-+6416433&utm_medium=affiliate+-+Product+Catalog');
		}
		else if (strstr(strtolower($url), 'moosejaw.com/'))
		{
			header('Location: ' . str_replace('&amp;', '&', base64_decode($_GET['id'])) . $query_string_tag . 'ad_id=CJ&cm_mmc=Affiliate-_-CJ-_-na-_-6416433');
		}
		else
		{
			header('Location: http://redirect.viglink.com/?key=dd6a20bcaca9710fd1a3dff8ef71de07&u=' . str_replace('&amp;', '&', base64_decode($_GET['id'])));
		}
	}
}