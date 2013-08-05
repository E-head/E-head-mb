<?php

class sms24x7 {

	protected $email, $password, $curl;
	public $response, $use_session;

	// check and init cURL
	// set some params
	function __construct($email, $password, $api_v = '1.1', $format = 'json'){
		if(!in_array('curl', get_loaded_extensions())){
			throw new Exception('Curl library is not installed.');
		}
		$this->api_v = $api_v;
		$this->email = $email;
		$this->password = $password;
		$this->use_session = 1;
		$this->format = $format;
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->curl, CURLOPT_URL, 'https://api.sms24x7.ru/');
		curl_setopt($this->curl, CURLOPT_POST, True);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, True);
		// save cookies in local file
		//curl_setopt($this->curl, CURLOPT_COOKIEJAR, '.smscookie');
		//curl_setopt($this->curl, CURLOPT_COOKIEFILE, '.smscookie');
	}

	// function to call API method
	function call_method($method, $params = array()){

		if(empty($this->use_session) OR !file_exists('.smscookie') OR !filesize('.smscookie')){ // need to login
			$params['email'] = $this->email;
			$params['password'] = $this->password;
			if(!empty($this->use_session)){ $params['sid'] = NULL; } // inform API to open session, otherwise session will not be started
		}

		$params['format'] = $this->format;
		$params['api_v'] = $this->api_v;
		$params['method'] = $method;
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);

		$data= curl_exec($this->curl);

		// throw errors if they occur
		if($data === false){
			throw new Exception('Curl error: '.curl_error($this->curl).'.');
		}
		if(curl_getinfo($this->curl, CURLINFO_HTTP_CODE) >= 400){
			throw new Exception('API server is not responding.');
		}

		if(!$js = json_decode($data, $assoc = true)){
			throw new Exception('API response is empty or misformed.');
		}

		if(!empty($js['response']['msg']['err_code'])){
			throw new Exception($js['response']['msg']['text']);
		}

		// save API response
		$this->response = $js['response'];
		return $js['response'];
	}

	// close cUrl
	// delete few params
	function __destruct(){
		curl_close($this->curl);
		unset($this->email, $this->password, $this->curl, $this->response, $this->use_session);
		if(file_exists('.smscookie')){ unlink('.smscookie'); }
	}

}
