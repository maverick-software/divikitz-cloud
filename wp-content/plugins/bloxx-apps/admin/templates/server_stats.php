<?php

$server_id = $_GET['server_id'];
$cloudways = new Cloudways();

$token = $cloudways->getToken();
$operation = json_decode($cloudways->getRequest('/server/'.$server_id.'/diskUsage?access_token='.$token));

if(isset($operation->status) && $operation->status === true){
	sleep(10);
	$output = json_decode($cloudways->getRequest('/operation/'.$operation->operation_id.'?access_token='.$token));echo "<pre>";print_r($output);
	if(isset($output->operation) && $output->operation->is_completed == '1'){
		echo 'Remainig Disk Space: '.formatBytes($output->operation->parameters->free_disk->remaining);
	}
}

function formatBytes($bytes, $precision = 2) { 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $total  = round($bytes, $precision);
   	if($total == 1024){
   		$unit = 'MB';
   	}elseif($total >= 1024){
   		$unit = 'GB';
   	}elseif($total < 1024){
   		$unit = 'KB';
   	}

   	return $total .' '.$unit;
} 





//$output = json_decode($cloudways->getRequest('/operation/'.$op_id.'?access_token='.$cloudways->getToken()));