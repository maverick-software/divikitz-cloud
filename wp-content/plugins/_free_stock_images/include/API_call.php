<?php


Class API_call{
	
	Public $api_key_pixabay;
	Public $api_url_pixabay = 'https://pixabay.com/api/';
	Public $q = '';
	Public $p = '1';

	public function __construct(){
		$this->api_key_pixabay = get_option( 'api_key_pixabay' );

	}
	public function set_query($q){
		$this->q = $q;
	}
	public function set_page($p){
		$this->p = $p;
	}
	public function call(){

		$final_url = $this->api_url_pixabay."?key=".$this->api_key_pixabay;

		if(!empty($this->q)){
			$final_url.='&q='.urldecode($this->q);
		}
		$final_url.='&page='.urldecode($this->p);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$final_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


		$server_output = curl_exec ($ch);

		curl_close ($ch);

		


		//return $server_output;
		return json_decode($server_output);
	} 
}


?>