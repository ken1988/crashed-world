<?php
/*******************************************************************

  箱庭諸島２ for PHP


  $Id: hako-main.php,v 1.14 2003/10/15 17:52:15 Watson Exp $
  Extracted as hako-util.php by Sanda - 2004/01/22
*******************************************************************/

//--------------------------------------------------------------------
class Util {
  //---------------------------------------------------
  // 資金の表示
  //---------------------------------------------------
  function aboutMoney($m,$t) {
    global $init;
    if($init->moneyMode) {
	$max = $init->allmax[$t];
	$perc = ceil($m/$max*100);
      if($perc < 5) {
        return '<IMG SRC="infoc1.png" align="left" title="皆無" ALT="皆無">';
      } elseif($perc < 20) {
        return '<IMG SRC="infoc2.png" align="left" title="欠乏" ALT="欠乏">';
      } elseif($perc < 50) {
        return '<IMG SRC="infoc3.png" align="left" title="普通" ALT="普通">';
      } elseif($perc < 80) {
        return '<IMG SRC="infoc4.png" align="left" title="潤沢" ALT="潤沢">';
      } else {
        return '<IMG SRC="infoc5.png" align="left" title="底無" ALT="底無">';
      }
    } else {
      return $money . $init->unitMoney;
    }
  }
  //---------------------------------------------------
  // 経験地からミサイル基地レベルを算出
  //---------------------------------------------------
  function expToLevel($kind, $exp) {
    global $init;
    if(($kind == $init->landBase)||
	   ($kind == $init->landHBase)){
      // ミサイル基地
      for($i = $init->maxBaseLevel; $i > 1; $i--) {
        if($exp >= $init->baseLevelUp[$i - 2]) {
          return $i;
        }
      }
      return 1;
    }
  }
  //---------------------------------------------------
  // 怪獣の種類・名前・体力を算出
  //---------------------------------------------------
  function monsterSpec($lv) {
    global $init;
    // 種類
    $kind = (int)($lv / 100);
    // 名前
    $name = $init->monsterName[$kind];
    // 体力
    $hp = $lv - ($kind * 100);
    return array ( 'kind' => $kind, 'name' => $name, 'hp' => $hp );
  }
  //---------------------------------------------------
  // 島の名前から番号を算出
  //---------------------------------------------------
  function  nameToNumber($hako, $name) {
    // 全島から探す
    for($i = 0; $i < $hako->islandNumber; $i++) {
      if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
        return $i;
      }
    }
    // 見つからなかった場合
    return -1;
  }
  //---------------------------------------------------
  // 島名を返す
  //---------------------------------------------------
  function islandName($island, $ally, $idToAllyNumber) {
    $name = '';
    foreach ($island['allyId'] as $id) {
      $i = $idToAllyNumber[$id];
      $mark  = $ally[$i]['mark'];
      $color = $ally[$i]['color'];
      $name .= '<FONT COLOR="' . $color . '"><B>' . $mark . '</B></FONT> ';
    }
    $name .= $island['name'] . "";

    return ($name);
  }
  //---------------------------------------------------
  // 数字を見やすくする
  //---------------------------------------------------
  function Rewriter($amount){
      $amount = strrev($amount);
	  $len = strlen($amount);
	  $lens = floor($len/4);
	  for ($i = 0; $i < $lens; $i++){
	    $addstr = substr($amount,$i*4+1,4);
	  	if(substr($addstr,strlen($addstr)-1,1) == "0"){
		//$addstrの最後に0がある場合取り除く
			$addstr = substr($addstr,0,strlen($addstr)-1);
		}
		//桁区切りを加える
		$addstr.=">".$i."<";
		$amounts.=$addstr;
	  }
	  $last = substr($amount,$lens*4+1,$len-$lens*4);
	  $amounts.= $last;
	  //文字列を逆に戻す
	  $amounts = strrev($amounts);
	  return $amounts;
  }
   //---------------------------------------------------
  // 資源ごとに数字を整える
  //---------------------------------------------------
  function Rewriter2($type,$base){

	//ベース値を整える
		switch($type){
			case 'oil':
				$base = $base*100;
				break;

			case 'money':
				$base = $base*100;
			break;

			case 'silver':
				$base = $base*100;
				break;

			case 'food':
				$base = $base*10;
				break;

			default:
				$base = $base*10000;
				break;
		}
		$amounts = Util::Rewriter($base);
		$amounts = Util::Replacer($type,$amounts);
	  return $amounts;
  }
  //---------------------------------------------------
  // 桁区切り数字を置き換える
  //---------------------------------------------------
  function Replacer($type,$amounts){
  if($type == ""){
  	$amounts = substr($amounts,0,strlen($amounts)-3);
  }else{
  	$amounts = substr($amounts,0,strlen($amounts)-1);
  }
  if(substr($amounts,0,1)=="<"){
  	$amounts = substr($amounts,3,strlen($amounts)-3);
  }
		switch($type){
			case 'oil':
				$amounts = str_replace("<0>","億",$amounts);
				break;

			case 'money':
				$amounts = str_replace("<0>","兆",$amounts);
			break;

			case 'silver':
				$amounts = str_replace("<0>","万",$amounts);
				break;

			case 'food':
				$amounts = str_replace("<0>","億",$amounts);
				break;

			default:
				if(substr($amounts,strlen($amounts)-1,1)!==">"){
					$amounts.= "千";
				}
				$amounts = str_replace("<0>","万",$amounts);
				$amounts = str_replace("<1>","億",$amounts);
				break;
		}
  	  return $amounts;
  }
  //---------------------------------------------------
  // 暦を生成する
  //---------------------------------------------------
  function MKCal($turn,$mode){
	 $year = floor($turn / 36);
	 $month = ceil(($turn % 36) / 3);
	 $termb =($turn % 36) / 3 - floor(($turn % 36) / 3);
	 if($termb == 0){
	 	$term = "下旬";
	 }elseif($termb < 0.34){
	 	$term = "初旬";
	 }elseif($termb <0.67){
	 	$term = "中旬";
	 }
	 if ($month == 0){
	 		$month = 1;
	  }
	  if($mode == 0){
	  //フルセット
	  	$calendars = "墜星暦 ". $turn. "期<br>". $year. "年 ". $month. "月".$term;
	  }elseif($mode == 2){
	  	$calendars = $year. "年 ". $month. "月";
	  }else{
	  //年だけ
	  	$calendars = $year;
	  }
	  return $calendars;

}
  //---------------------------------------------------
  //統計データ出力(総合）
  //---------------------------------------------------
  function Makewstat(){
    global $init;
    $fileName = "{$init->dirName}/statistic.xml";
	$xmldata = simplexml_load_file($fileName);
	$yfstat = Util::Makepstat($xmldata);
	$ysstat = Util::Makemstat($xmldata);
	$ystat = Util::array_merge_x($yfstat,$ysstat);
	return $ystat;
  }
  //-----------------------------------	----------------
  //統計データ出力(人口系）
  //---------------------------------------------------
  function Makepstat($xmldata){

	foreach($xmldata->factdata as  $tstat){
		if($tstat->year != 0){
			$year = (string)$tstat->year;
			$ystat[$year]["pop"]	= (int)$tstat->pop;
			$ystat[$year]["farm"]	= (int)$tstat->farm;
			$ystat[$year]["ind"]	= (int)$tstat->ind;
			$ystat[$year]["market"]	= (int)$tstat->market;
		}
	}
	unset($ystat[$year]);
	$ystat = Util::arrdata($ystat,35);
	return $ystat;

  }
  //---------------------------------------------------
  //統計データ出力(金銭系）
  //---------------------------------------------------
  function Makemstat($xmldata){

	foreach($xmldata->factdata as  $tstat){
		if($tstat->year != 0){
			$year = (string)$tstat->year;
    		$ystat[$year]["pgoods"]	+= (int)$tstat->pgoods;
  			$ystat[$year]["pmoneys"] += (int)$tstat->pmoneys;
    		$ystat[$year]["shell"]	= (int)$tstat->shell;
		}
	}
	unset($ystat[$year]);
	$ystat = Util::arrdata($ystat,35);
	return $ystat;
  }
  //---------------------------------------------------
  //統計データ抽出
  //---------------------------------------------------
    function arrdata($data,$onum){
	$num = count($data);
	$i = 0;
	foreach($data as $year => $ydata){
		if($i < $num - $onum){
			unset($data[$year]);
			$i++;
		}
	}
	return $data;
	}
  //---------------------------------------------------
  // 2次元配列連結
  //---------------------------------------------------
	function array_merge_x($a1, $a2){
		foreach($a2 as $key => $val){
			if(isset($a1[$key]) && is_array($val)){
				$a1[$key] = Util::array_merge_x($a1[$key], $val);
			}else{
				$a1[$key] = $val;
			}
		}
		return($a1);
	}
  //---------------------------------------------------
  // パスワードチェック
  //---------------------------------------------------
  function checkPassword($p1 = "", $p2 = "") {
    global $init;

    // nullチェック
    if(empty($p2))
      return false;

    // マスターパスワードチェック
    if(strcmp($init->masterPassword, $p2) == 0)
      return true;

    if(strcmp($p1, Util::encode($p2)) == 0)
      return true;

    return false;
  }
  //---------------------------------------------------
  // パスワードのエンコード
  //---------------------------------------------------
  function encode($s) {
    global $init;
    if($init->cryptOn) {
      return crypt($s, 'h2');
    } else {
      return $s;
    }
  }
  //---------------------------------------------------
  // 改行コードを LF（\n）に統一
  //---------------------------------------------------
  function conv_LF($str){
  	$str=str_replace("\r\n", "\n", $str);
  	$str=str_replace("\r", "\n", $str);
  	return $str;
  }
  //---------------------------------------------------
  // 0 ～ num -1 の乱数生成
  //---------------------------------------------------
  function random($num = 0) {
    if($num <= 1) return 0;
    return mt_rand(0, $num - 1);
  }
  //---------------------------------------------------
  // ローカル掲示板のメッセージを一つ前にずらす
  //---------------------------------------------------
  function slideBackLbbsMessage(&$lbbs, $num) {
    global $init;
    array_splice($lbbs, $num, 1);
    $lbbs[$init->lbbsMax - 1] = '0>>0>>';
  }
  //---------------------------------------------------
  // ローカル掲示板のメッセージを一つ後ろにずらす
  //---------------------------------------------------
  function slideLbbsMessage(&$lbbs) {
    array_pop($lbbs);
    array_unshift($lbbs, $lbbs[0]);
  }
  //---------------------------------------------------
  // 定期輸送の計画を一つ前にずらす
  //---------------------------------------------------
  function slideregT(&$regT, $num) {
    global $init;
    array_splice($regT, $num, 1);
    $regT[$init->regTMax - 1] = "";
  }
  //---------------------------------------------------
  // 定期輸送の計画を1つ後ろにずらす
  //---------------------------------------------------
  function regTpush(&$regT) {
    array_pop($regT);
    array_unshift($regT, $regT[0]);
  }
  //---------------------------------------------------
  // ランダムな座標を生成
  //---------------------------------------------------
  function makeRandomPointArray() {
    global $init;
    $rx = $ry = array();
    for($i = 0; $i < $init->islandSize; $i++)
      for($j = 0; $j < $init->islandSize; $j++)
        $rx[$i * $init->islandSize + $j] = $j;

    for($i = 0; $i < $init->islandSize; $i++)
      for($j = 0; $j < $init->islandSize; $j++)
        $ry[$j * $init->islandSize + $i] = $j;


    for($i = $init->pointNumber; --$i;) {
      $j = Util::random($i + 1);
      if($i != $j) {
        $tmp = $rx[$i];
        $rx[$i] = $rx[$j];
        $rx[$j] = $tmp;

        $tmp = $ry[$i];
        $ry[$i] = $ry[$j];
        $ry[$j] = $tmp;
      }
    }
    return array($rx, $ry);
  }
  //---------------------------------------------------
  // ランダムな島の順序を生成
  //---------------------------------------------------
  function randomArray($n = 1) {
    // 初期値
    for($i = 0; $i < $n; $i++) {
      $list[$i] = $i;
    }

    // シャッフル
    for($i = 0; $i < $n; $i++) {
      $j = Util::random($n - 1);
      if($i != $j) {
        $tmp = $list[$i];
        $list[$i] = $list[$j];
        $list[$j] = $tmp;
      }
    }
    return $list;
  }
  //---------------------------------------------------
  // コマンドを前にずらす
  //---------------------------------------------------
  function slideFront(&$command, $number = 0) {
    global $init;
    // それぞれずらす
    array_splice($command, $number, 1);

    // 最後に資金繰り
    $command[$init->commandMax - 1] = array (
      'kind'   => $init->comDoNothing,
      'target' => 0,
      'x'      => 0,
      'y'      => 0,
      'arg'    => 0
      );
  }
  //---------------------------------------------------
  // コマンドを後にずらす
  //---------------------------------------------------
  function slideBack(&$command, $number = 0) {
    global $init;
    // それぞれずらす
    if($number == count($command) - 1)
      return;

    for($i = $init->commandMax - 1; $i >= $number; $i--) {
      $command[$i] = $command[$i - 1];
    }
  }

  function euc_convert($arg) {
    // 文字コードをEUC-JPに変換して返す
    // 文字列の文字コードを判別
    $code = i18n_discover_encoding("$arg");
    // 非EUC-JPの場合のみEUC-JPに変換
    if ( $code != "EUC-JP" ) {
      $arg = i18n_convert("$arg","EUC-JP");
    }
    return $arg;
  }
  function utf_convert($arg) {
  	// 文字コードをutf-8に変換して返す
  	// 文字列の文字コードを判別
  	$code = i18n_discover_encoding("$arg");
  	// 非EUC-JPの場合のみEUC-JPに変換
  	if ( $code != "UTF-8" ) {
  		$arg = i18n_convert("$arg","UTF-8");
  	}
  	return $arg;
  }
  function sjis_convert($arg) {
    // 文字コードをSHIFT_JISに変換して返す
    // 文字列の文字コードを判別
    $code = i18n_discover_encoding("$arg");
    // 非SHIFT_JISの場合のみSHIFT_JISに変換
    if ( $code != "SJIS" ) {
      $arg = i18n_convert("$arg","SJIS");
    }
    return $arg;
  }
  //---------------------------------------------------
  // 船なのかのチェック
  //---------------------------------------------------
    function checkShip($kind,$lv) {
      global $init;
      $shiplev = $init->shipKind + 2;
      if(($kind == $init->landSea) && ((($lv > 1) && ($lv < $shiplev)) || ($lv == 255))){
        return true;
      }
      return false; // 船以外
    }
  //---------------------------------------------------
  // ファイルをロックする(書き込み時)
  //---------------------------------------------------
  function lockw($fp) {
    set_file_buffer($fp, 0);
    if(!flock($fp, LOCK_EX)) {
      fclose($fp);
      Error::lockFail();
    }
    rewind($fp);
  }
  //---------------------------------------------------
  // ファイルをロックする(読み込み時)
  //---------------------------------------------------
  function lockr($fp) {
    set_file_buffer($fp, 0);
    if(!flock($fp, LOCK_SH)) {
      fclose($fp);
      Error::lockFail();
    }
    rewind($fp);
  }
  //---------------------------------------------------
  // ファイルをアンロックする
  //---------------------------------------------------
  function unlock($fp) {
    flock($fp, LOCK_UN);
  }
}

?>
