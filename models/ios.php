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

class ios_class extends AWS_MODEL
{
	public function process_push($limit = 100)
	{
		if ($push_queue = $this->fetch_all('ios_push', 'result IS NULL', 'id ASC', $limit))
		{
			foreach ($push_queue AS $key => $val)
			{
				if ($val['result'])
				{
					continue;
				}
				
				if ($this->push($val['device_token'], $val['message'], $val['item_id']))
				{
					$this->update('ios_push', array(
						'result' => 1,
						'push_time' => time()
					), 'id = ' . intval($val['id']));
				}
				else
				{
					$this->update('ios_push', array(
						'result' => -1
					), 'id = ' . intval($val['id']));
				}
			}
		}
	}
	
	public function push($device_token, $message, $item_id = null)
	{
		// Put your private key's passphrase here:
		$passphrase = '123haitao';
		
		////////////////////////////////////////////////////////////////////////////////
		
		$stream = stream_context_create();
		
		stream_context_set_option($stream, 'ssl', 'local_cert', ROOT_PATH . 'ios_cert.pem');
		stream_context_set_option($stream, 'ssl', 'passphrase', $passphrase);
		
		// Open a connection to the APNS server
		if (!$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $stream))
		{
			//exit("Failed to connect: $err $errstr" . PHP_EOL);
			return false;
		}
		
		// Create the payload body
		$body['aps'] = array(
			'alert' => $message,
			//'badge' => 1,
			'sound' => 'default'
		);
		
		if ($item_id)
		{
			$body['url'] = '/m/question/' . $item_id;
		}
		else
		{
			$body['url'] = '/m/';
		}
		
		// Encode the payload as JSON
		$payload = json_encode($body);
		
		// Build the binary notification
		$notification = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', $device_token)) . pack('n', strlen($payload)) . $payload;
		
		// Send it to the server
		$result = fwrite($fp, $notification, strlen($notification));
		
		fclose($fp);
		
		if ($result)
		{
			return true;
		}
		
		return false;
	}
}