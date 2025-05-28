<?php


Class API_call_writesonic_business{
	
	Public $api_key_writesonic;
	Public $api_url_writesonic = 'https://api.writesonic.com/v1/business/content';
	Public $q = '';
	Public $p = '1';

	public function __construct(){
		$this->api_key_writesonic = get_option( 'api_key_writesonic_bussiness' );

	}
	
	public function call($data,$type,$lang,$engine){

		$final_url = $this->api_url_writesonic."/".$type."?end_user_id=".$data['end_user_id']."&engine=".$engine."&language=".$lang;

		$post_data = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$final_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  		curl_setopt($ch, CURLOPT_POST, true);
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		      'Content-Type: application/json',
		      'accept: application/json',
		      'X-API-KEY: ' . $this->api_key_writesonic)
		  );


		$server_output = curl_exec ($ch);

		curl_close ($ch);

		


		return $server_output;
		return json_decode($server_output);
	} 
}


?>