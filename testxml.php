<?
	$datas = array("1"=>3,"2"=>3,"12"=>3,"25"=>4);
	$filename = "fsarmy.xml";
	$xml = simplexml_load_file($filename);
	if($mode == 1){
	//�Z�߂ĕ��z���[�h
	
	}else{
	//�W�v���[�h
	$mil = array();
	foreach ( $xml->army as $army ) {
	$mil[(string)$army->from] = (int)$army->mass;
	}
	print_r($mil);
	print_r($datas);
	$result = $datas+$mil;
	print_r($result);
	}
	
  ?>