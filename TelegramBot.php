<?php

class TelegramBot {
	private $api_url;
	private $token;
	
	public function __construct($inputToken) {
		$this->token = $inputToken;
		$this->api_url = 'https://api.telegram.org';
	}
	
	private function call($method, $options = array(), $custom_headers = array()) {
		$curl = curl_init();
		$url = $this->api_url.'/bot'.$this->token.'/'.$method;

		$headers = array(
			'Content-Type' => 'application/json'
		);
		foreach ($custom_headers as $key => $value) {
			$headers[$key] = $value;
		}

		$out_headers = [];
		foreach ($headers as $key => $value) {
			$out_headers[] = $key.': '.$value;
		}

		if ($headers['Content-Type'] == 'application/json') {
			$options = json_encode($options);
			// var_dump($options);
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $out_headers);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $options);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
		// curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
		$out = curl_exec($curl);
		// var_dump($out);
		$res = json_decode($out, true);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// $header = substr($res, 0, $header_size);
		// $body = substr($res, $header_size);
		// $result = json_decode($body, true);

		$http_code = (int) $http_code;

		$error = curl_error($curl);
		// $str = $http_code.' + '.$url.' + '.json_encode($options).' = '.json_encode($res).', '.curl_error($curl);
		curl_close($curl);
		if ($http_code >= 300 or $http_code < 200) {
			throw new Exception(json_encode($res).' ('.$url.') ('.$error.', '.json_encode($options).')', $http_code);
		} else {
			return $res;
		}
	}


	
	public function sendMessage($chat_id, $message, $add_options = array()) {
		$options = array(
			'chat_id' => $chat_id, 
			'text' => $message
		);
		// $add_options['parse_mode'] = 'Markdown';

		foreach ($add_options as $key => $value) {
			$options[$key] = $value;
		}
		
		return $this->call('sendMessage', $options);
	}

	public function sendLocation($chat_id, $lat, $lng, $add_options = array()) {
		$options = array(
			'chat_id' => $chat_id, 
			'latitude' => $lat, 
			'longitude' => $lng
		);

		foreach ($add_options as $key => $value) {
			$options[$key] = $value;
		}
		
		return $this->call('sendLocation', $options);
	}
	public function sendPhoto($chat_id, $image_path, $add_options = array()) {
		$cfile = new CURLFile($image_path, 'image/jpeg', 'image.jpg');

		$options = array(
			'chat_id' => $chat_id, 
			'photo' => $cfile 
		);

		foreach ($add_options as $key => $value) {
			$options[$key] = $value;
		}

		return $this->call('sendPhoto', $options, ['Content-Type' => 'multipart/form-data']);
	}

	public function sendAudio($chat_id, $audio_path, $add_options = array()) {
		$cfile = new CURLFile($audio_path, 'audio/mpeg', 'audio.mp3');

		$options = array(
			'chat_id' => $chat_id, 
			'audio' => $cfile
		);

		foreach ($add_options as $key => $value) {
			$options[$key] = $value;
		}

		return $this->call('sendAudio', $options, ['Content-Type' => 'multipart/form-data']);
	}

	public function sendDocument($chat_id, $doc_path, $filename, $mimetype, $add_options = array()) {
		$cfile = new CURLFile($doc_path, $mimetype, $filename);

		$options = array(
			'chat_id' => $chat_id, 
			'document' => $cfile
		);

		foreach ($add_options as $key => $value) {
			$options[$key] = $value;
		}

		return $this->call('sendDocument', $options, ['Content-Type' => 'multipart/form-data']);
	}
	
	public function sendChatAction($chat_id, $action) {
		$allowed = array('typing', 'upload_photo', 'record_video', 'upload_video', 'record_audio', 'upload_audio', 'upload_document', 'find_location');
		if (!(in_array($action, $allowed) === false)) {
			$options = array('chat_id' => $chat_id, 'action' => $action);
		} else {
			$options = array('chat_id' => $chat_id, 'action' => 'typing');
		}
		return $this->call('sendChatAction', $options);
	}
}
?>