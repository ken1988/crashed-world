<?php
/*******************************************************************

  箱庭諸島２ for PHP


  $Id: hako-main.php,v 1.16 2004/02/14 15:51:45 watson Exp $

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-file.php';
require 'hako-html.php';
require 'hako-turn.php';
require 'hako-util.php';
require 'hako-ally.php';
require 'wns.php';
$init = new Init;

define("READ_LINE", 1024);
$THIS_FILE =  $init->baseDir . "/hako-main.php";
$BACK_TO_TOP = "<A HREF=\"{$THIS_FILE}?\">{$init->tagBig_}トップへ戻る{$init->_tagBig}</A>";
$_TURN; // ターン数
$PRODUCT_VERSION = '0.85';

//--------------------------------------------------------------------
class Hako extends HakoIO {
  var $islandList;	// 島リスト
  var $targetList;	// ターゲットの島リスト
  var $defaultTarget;	// 目標補足用ターゲット

  function readIslands(&$cgi) {
    global $init;

    $m = $this->readIslandsFile($cgi);
    $this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);
    if($init->targetIsland == 1) {
      // 目標の島 所有の島が選択されたリスト
      $this->targetList = $this->islandList;
    } else {
      // 順位がTOPの島が選択された状態のリスト
      $this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
    }
    return $m;
  }
  //---------------------------------------------------
  // 島リスト生成
  //---------------------------------------------------
  function getIslandList($select = 0) {
    global $init;

    $list = "";
    for($i = 0; $i < $this->islandNumber; $i++) {
      if($init->allyUse) {
        $name = Util::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // 同盟マークを追加
      } else {
        $name = $this->islands[$i]['name'];
      }
      $id   = $this->islands[$i]['id'];

      // 攻撃目標をあらかじめ自分の島にする
      if(empty($this->defaultTarget)) {$this->defaultTarget = $id;}

      if($id == $select) {
        $s = "selected";
      } else {
        $s = "";
      }
      if($init->allyUse) {
        $list .= "<option value=\"$id\" $s>{$name}</option>\n"; // 同盟マークを追加
      } else {
        $list .= "<option value=\"$id\" $s>{$name}</option>\n";
      }
    }
    return $list;
  }
  //---------------------------------------------------
  // 賞に関するリストを生成
  //---------------------------------------------------
  function getPrizeList($prize) {
    global $init;
    list($flags, $monsters, $turns) = split(",", $prize, 3);

    $turns = split(",", $turns);
    $prizeList = "";
    // ターン杯
    $max = -1;
    $nameList = "";
    if($turns[0] != "") {
      for($k = 0; $k < count($turns) - 1; $k++) {
        $nameList .= "[{$turns[$k]}] ";
        $max = $k;
      }
    }
    if($max != -1) {
      $prizeList .= "<img src=\"prize0.gif\" alt=\"$nameList\" width=\"16\" height=\"16\"> ";
    }
    // 賞
    $f = 1;
    for($k = 1; $k < count($init->prizeName); $k++) {
      if($flags & $f) {
        $prizeList .= "<img src=\"prize{$k}.gif\" alt=\"{$init->prizeName[$k]}\" width=\"16\" height=\"16\"> ";
      }
      $f = $f << 1;
    }
    return $prizeList;
  }
  //------------------------------------------------------------------

  //---------------------------------------------------
  // 地形に関するデータ生成
  //---------------------------------------------------
  function landString($l, $lv, $x, $y, $mode, $comStr, $invest, $Cname,$ctype,$target) {
    global $init;
    $point = "({$x},{$y})";
    $naviExp = "''";

    if($x < $init->islandSize / 2)
      $naviPos = 0;
    else
      $naviPos = 1;

    switch($l) {
    case $init->landSea:
      switch($lv) {
      case 1:
        // 浅瀬
        $image = 'land14.gif';
        $naviTitle = '浅瀬';
        break;
      case 2:
        // 工作船
        $image = 'ship.gif';
        $naviTitle = $init->shipName[0];
        break;
      case 3:
        // 漁船
        $image = 'fishingboat.gif';
        $naviTitle = $init->shipName[1];
        break;
      case 4:
        // 海底探索船
        $image = 'ship2.gif';
        $naviTitle = $init->shipName[2];
        break;
      case 5:
        // 戦艦
        $image = 'senkan.gif';
        $naviTitle = $init->shipName[3];
        break;
      case 255:
        // 海賊船
        $image = 'viking.gif';
        $naviTitle = '海賊船';
        break;
      default:
        // 海
        $image = 'land0.gif';
        $naviTitle = '海';
		//$naviText = "{$lv}";
      }
      break;
    case $init->landSeaCity:
      // 海底都市
        $image = 'SeaCity.gif';
        $naviTitle = '海底都市';
		$lv = Util::Rewriter2("",$lv);
        $naviText = "{$lv}{$init->unitPop}";
      break;
    case $init->landPort:
        // 港
        $image = 'port.gif';
		if($lv == 0){
	        $naviTitle = '港';
		}else{
	        $naviTitle = '大規模港';
        $naviText = "維持費600億Va";
		}
        break;
    case $init->landSeaSide:
        // 海岸
        $image = 'sunahama.gif';
        $naviTitle = '砂浜';
        break;
    case $init->landPark:
        // 遊園地
        $image = "park{$lv}.gif";
        $naviTitle = '遊園地';
        break;
    case $init->landFusya:
        // 風車
        $image = 'fusya.gif';
        $naviTitle = '農業改良センター';
        break;
    case $init->landNPark:
        // 消防署
        $image = 'nationalpark.gif';
        $naviTitle = '国立公園';
        break;
    case $init->landNursery:
        // 養殖場
        $image = 'Nursery.gif';
        $naviTitle = '養殖場';
	    $lv = Util::Rewriter2("",$lv*10);
        $naviText = "{$lv}{$init->unitPop}規模";
        break;
    case $init->landWaste:
      // 荒地
      if($lv == 1) {
        $image = 'land13.gif'; // 着弾点
      } else {
        $image = 'land1.gif';
      }
      $naviTitle = '荒地';
      break;
    case $init->landPlains:
      // 平地
      $image = 'land2.gif';
      $naviTitle = '平地';
      break;
    case $init->landPoll:
      // 汚染土壌
      $image = 'poll.gif';
      $naviTitle = '汚染土壌';
      $naviText = "汚染レベル{$lv}";
      break;
    case $init->landForest:
      // 森
      if($mode == 1) {
        $image = 'land6.gif';
        $naviText= "${lv}{$init->unitTree}";
      } else {
        // 観光者の場合は木の本数隠す
        $image = 'land6.gif';
      }
      $naviTitle = '森';
      break;
    case $init->landTown:
      // 町
      $p; $n;
	  $nwork = (int)($lv/12);
      if($lv < 30) {
        $p = 3;
        $naviTitle = '村';
      } else if($lv < 100) {
        $p = 4;
        $naviTitle = '村落';
      } else if($lv < 200) {
        $p = 5;
        $naviTitle = '農村';
      } else {
        $p = 52;
        $naviTitle = '近郊住宅地';
		$nwork = 0;
      }
      $image = "land{$p}.gif";
	  if ($nwork == 0){
	  	$naviTexts ="";
	  }else{
	    $nwork = Util::Rewriter2("",$nwork * 10);
      	$naviTexts = "/農業{$nwork}{$init->unitPop}";
	  }
	  $lv = Util::Rewriter2("",$lv);
	  $naviText = "{$lv}{$init->unitPop}".$naviTexts ;
      break;
    case $init->landProcity:
      // 防災都市
      if($lv < 110) {
        $naviTitle = '防災都市ランクＥ';
      } else if($lv < 130) {
        $naviTitle = '防災都市ランクＤ';
      } else if($lv < 160) {
        $naviTitle = '防災都市ランクＣ';
      } else if($lv < 200) {
        $naviTitle = '防災都市ランクＢ';
      } else {
        $naviTitle = '防災都市ランクＡ';
      }
      $image = "bousai.gif";
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "{$lv}{$init->unitPop}";
      break;
    case $init->landNewtown:
      // ニュータウン
      $nwork =  (int)($lv/60);
      $image = 'new.gif';
      $naviTitle = 'ニュータウン';
	  if ($nwork == 0){
	  	$naviTexts ="";
	  }else{
	    $nwork = Util::Rewriter2("",$nwork * 10);
		$naviTexts = "/商業{$nwork}{$init->unitPop}";
	  }
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "{$lv}{$init->unitPop}".$naviTexts ;
      break;
    case $init->landBigtown:
      // 現代都市
      $mwork =  (int)($lv/7);
      $lwork =  (int)($lv/100);
      $image = 'big.gif';
      $naviTitle = '現代都市';
	  $mwork = Util::Rewriter2("",$mwork * 10);
	  $lwork = Util::Rewriter2("",$lwork * 10);
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "{$lv}{$init->unitPop}/商業{$mwork}{$init->unitPop}/工業{$lwork}{$init->unitPop}";
      break;

   	 case $init->landIndCity:
      // 工業都市
      $nwork =  (int)($lv/5);
	  $image = 'indust0.gif';
      $naviTitle = '工業都市';
	  $nwork = Util::Rewriter2("",$nwork * 10);
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "{$lv}{$init->unitPop}/工業{$nwork}{$init->unitPop}";
      break;

	case $init->landCapital:
	  //首都
      $nwork =  (int)($lv/9);
      $image = 'capital'.$ctype.'.gif';
      $naviTitle = '首都'.$Cname;
	  $nwork = Util::Rewriter2("",$nwork * 10);
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "{$lv}{$init->unitPop}/商業{$nwork}{$init->unitPop}";
      break;

	case $init->landSecpol:
	 //秘密警察本部
      $image = "secpol.gif";
      $naviTitle = '秘密警察';
      $naviText = "支持率 100％";
      break;

    case $init->landFarm:
      // 農場
      $image = 'land7.gif';
      $naviTitle = '共同農場';
      if($lv > 25) {
      // ドーム型農場
      $image = 'land71.gif';
      $naviTitle = 'ドーム型共同農場';
      }
	  $lv = Util::Rewriter2("",$lv * 10);
      $naviText = "{$lv}{$init->unitPop}規模";
      break;
    case $init->landFactory:
      // 工場
      $image = 'land8.gif';
      $naviTitle = '国営工場';
      if($lv > 100) {
      // 大工場
      $image = 'land82.gif';
      $naviTitle = '国営コンビナート';
      }
	  $lv = Util::Rewriter2("",$lv * 10);
      $naviText = "{$lv}{$init->unitPop}規模";
      break;
    case $init->landMarket:
      // 市場
      $image = 'land22.gif';
      $naviTitle = '国営市場';
	  $lv = Util::Rewriter2("",$lv * 10);
      $naviText = "{$lv}{$init->unitPop}規模";
      break;
    case $init->landHatuden:
      // 発電所
      $image = 'hatuden.gif';
      $naviTitle = '発電所';
      $naviText = "{$lv}000kw";
      if($lv > 100) {
      // 大型発電所
      $image = 'hatuden2.gif';
      $naviTitle = '大型発電所';
      }
      break;
    case $init->landSFactory:
      // 軍事工場
      $naviTitle = '軍事工場';
      $image = 'land17.gif';
      $naviText = "{$lv}{$init->unitShell}規模";
      break;
    case $init->landMFactory:
      // 建材工場
      $naviTitle = '建材工場';
      $image = 'land25.gif';
      $naviText = "{$lv}{$init->unitMaterial}規模";
      break;
    case $init->landFFactory:
      // 元精製工場
      $naviTitle = '畜産場';
      $image = 'land33.gif';
      $naviText = "{$lv}万頭";
      break;
    case $init->landHBase:
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は森のふり
        $image = 'land6.gif';
        $naviTitle = '森';
      } else {
        // ミサイル基地
        $level = Util::expToLevel($l, $lv);
        $image = 'land9.gif';
        $naviTitle = '偽装ミサイル基地';
        $naviText = "レベル {$level} / 経験値 {$lv}";
      }
      break;
    case $init->landBase:
        // ミサイル基地
        $level = Util::expToLevel($l, $lv);
        $image = 'land9.gif';
        $naviTitle = 'ミサイル基地';
        $naviText = "レベル {$level} / 経験値 {$lv}";
      break;
	case $init->landFBase:
        // 他国軍駐屯地
		$image = 'land73.gif';
        $naviTitle = "他国軍駐屯地";
        $naviText = "{$target}軍";
      break;

    case $init->landDefence:
      // 防衛施設
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は防衛施設のレベル隠蔽
      } else {
        $naviText = "耐久力 {$lv}";
      }
      $image = 'land10.gif';
      $naviTitle = '防衛施設';
      break;
    case $init->landHDefence:
      // 偽装防衛施設
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は防衛施設のレベル隠蔽
        $image = 'land6.gif';
        $naviTitle = '森';
      } else {
        $image = 'land10.gif';
        $naviTitle = '偽装防衛施設';
        $naviText = "耐久力 {$lv}";
      }
      break;
    case $init->landSdefence:
      // 防衛艦隊
      if($mode == 0 || $mode == 2) {
        $image = 'land102.gif';
        $naviTitle = '防衛艦隊';
      } else {
        $image = 'land102.gif';
        $naviTitle = '防衛艦隊';
        $naviText = "耐久力 {$lv}";
      }
      break;
	case $init->landMyhome:
      // 議事堂
      $image = "government.gif";
      $naviTitle = '議事堂';
      $naviText = "支持率 {$lv}％";
	  break;
	case $init->landSeeCity:
      // 観光都市
      $image = "land92.gif";
      $naviTitle = '観光都市';
	  $income = round($lv * 0.3 * (40 + $invest*2/3) /100);
	  $lv = Util::Rewriter2("",$lv);
      $naviText = "滞在人口 {$lv}{$init->unitPop}/収入 {$income}{$init->unitMoney}";
	  break;
    case $init->landHaribote:
      // ハリボテ
      if($lv == 0){
        $image = 'land9.gif';
        if($mode == 0 || $mode == 2) {
          // 観光者の場合はミサイル基地のふり
          $naviTitle = 'ミサイル基地';
        } else {
          $naviTitle = 'ハリボテ';
        }
      } else {
        $image = 'land10.gif';
        if($mode == 0 || $mode == 2) {
          // 観光者の場合は防衛施設のふり
          $naviTitle = '防衛施設';
        } else {
          $naviTitle = 'ハリボテ';
        }
      }
      break;
    case $init->landOil:
      // 海底油田
      $image = 'land16.gif';
      $naviTitle = '海底油田';
      $naviText = "Lv {$lv}";
      break;
    case $init->landMountain:
      // 山
      $image = 'land11.gif';
      $naviTitle = '山';
      break;
	case $init->landnMountain:
      // 山地
      $image = 'land26.gif';
      $naviTitle = '山地';
      break;
    case $init->landStonemine:
      // 採石場
      $level = ($lv % 10) + 1;
      $maizo = (int)($lv / 10) * 50;
      $image = 'land15.gif';
      $naviTitle = '採石場';
      $naviText = "Lv{$level} 埋蔵量{$maizo}{$init->unitSulfur}";
      break;
    case $init->landCoal:
      // 炭坑
      $level = ($lv % 10) + 1;
      $maizo = (int)($lv / 10) * 100;
      $image = 'land15.gif';
      $naviTitle = '炭坑';
      $naviText = "Lv{$level} 埋蔵量{$maizo}{$init->unitCoal}";
      break;
    case $init->landSteel:
      // 鉄鉱
      $level = ($lv % 10) + 1;
      $maizo = (int)($lv / 10) * 50;
      $image = 'land15.gif';
      $naviTitle = '鉄鉱';
      $naviText = "Lv{$level} 埋蔵量{$maizo}{$init->unitSteel}";
      break;
    case $init->landUranium:
      // ウラン鉱山
      $level = ($lv % 10) + 1;
      $maizo = (int)($lv / 10) * 1;
      $image = 'land15.gif';
      $naviTitle = 'ウラン鉱山';
      $naviText = "Lv{$level} 埋蔵量{$maizo}{$init->unitUranium}";
      break;
    case $init->landSilver:
      // 銀鉱山
      $level = ($lv % 10) + 1;
      $maizo = (int)($lv / 10) * 50;
      $image = 'land15.gif';
      $naviTitle = '銀鉱';
      $naviText = "Lv{$level} 埋蔵量{$maizo}{$init->unitSilver}";
      break;
    case $init->landMonument:
      // 記念碑
      $image = "monument{$lv}.gif";
      $naviTitle = '記念碑';
      $naviText = $init->monumentName[$lv];
      break;
	case $init->landZorasu:
	 //揚陸艦
	 if(($lv % 10) == 0){
        $image = 'land0.gif';
        $naviTitle = '海';
	 }else{
      $image = "transport.gif";
      $naviTitle = '揚陸艦';
	  $lv = floor($lv / 10);
      $naviText = "体力{$lv}";
	 }
      break;
    case $init->landMonster:
    case $init->landSleeper:
      // 怪獣
      $monsSpec = Util::monsterSpec($lv);
      $spec = $monsSpec['kind'];
      $special = $init->monsterSpecial[$spec];
      $image = "monster{$spec}.gif";
      if($l == $init->landSleeper) {
        $naviTitle = "{$monsSpec['name']}（睡眠中）";
      } else {
        $naviTitle = "{$monsSpec['name']}";
      }

      $naviText = "体力{$monsSpec['hp']}";
    }

    if($mode == 1 || $mode == 2) {
      print "<a href=\"javascript: void(0);\" onclick=\"ps($x,$y)\">";
      $naviText = "{$comStr}\\n{$naviText}";
    }
    print "<img class=\"mapchip\" src=\"{$image}\"width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle} {$comStr}\" onMouseOver=\"Navi({$naviPos},'{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\">";

    // 座標設定閉じ
    if($mode == 1 || $mode == 2)
      print "</a>";
  }
}
//--------------------------------------------------------------------
class LogIO {
  var $logPool = array();
  var $secretLogPool = array();
  var $lateLogPool = array();

  //---------------------------------------------------
  // ログファイルを後ろにずらす
  //---------------------------------------------------
  function slideBackLogFile() {
    global $init;
    for($i = $init->logMax - 1; $i >= 0; $i--) {
      $j = $i + 1;
      $s = "{$init->dirName}/hakojima.log{$i}";
      $d = "{$init->dirName}/hakojima.log{$j}";
      if(is_file($s)) {
        if(is_file($d))
           unlink($d);
        rename($s, $d);
      }
    }
  }
  //---------------------------------------------------
  // 最近の出来事を出力
  //---------------------------------------------------
  function logFilePrint($num = 0, $id = 0, $mode = 0) {
    global $init;
    $fileName = $init->dirName . "/hakojima.log" . $num;
    if(!is_file($fileName)) {
      return;
    }
    $fp = fopen($fileName, "r");
    Util::lockr($fp);
    while($line = chop(fgets($fp, READ_LINE))) {
      list($m, $turn, $id1, $id2, $message) = split(",", $line, 5);
      if($m == 1) {
        if(($mode == 0) || ($id1 != $id)) {
          continue;
        }
        $m = "<strong>(機密)</strong>";
      } else {
        $m = "";
      }
      if($id != 0) {
        if(($id != $id1) && ($id != $id2)) {
          continue;
        }
      }
      print "{$init->tagNumber_}ターン{$turn}{$m}{$init->_tagNumber}：{$message}<br>\n";
    }
	$calendars = Util::MKCal($turn,0);
	$calendars = strip_tags($calendars);
	print "＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝{$init->tagName_}［↑{$calendars}↑］{$init->_tagName}＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝<br>\n";
    Util::unlock($fp);
    fclose($fp);
  }
  //---------------------------------------------------
  // 発見の記録を出力
  //---------------------------------------------------
  function historyPrint() {
    global $init;
    $fileName = $init->dirName . "/hakojima.his";
    if(!is_file($fileName)) {
      return;
    }
    $fp = fopen($fileName, "r");
    Util::lockr($fp);

    $history = array();
    $k = 0;
    while($line = chop(fgets($fp, READ_LINE))) {
      array_push($history, $line);
      $k++;
    }
    for($i = 0; $i < $k; $i++) {
      list($turn, $his) = split(",", array_pop($history), 2);
      print "{$init->tagNumber_}ターン{$turn}{$init->_tagNumber}：$his<br>\n";
    }
  }
  //---------------------------------------------------
  // 発見の記録を保存
  //---------------------------------------------------
  function history($str) {
    global $init;
    $fileName = "{$init->dirName}/hakojima.his";

    if(!is_file($fileName))
      touch($fileName);

    $fp = fopen($fileName, "a");
    Util::lockw($fp);
    fputs($fp, "{$GLOBALS['ISLAND_TURN']},{$str}\n");
    Util::unlock($fp);
    fclose($fp);
//    chmod($fileName, 0666);

  }
  //---------------------------------------------------
  // 発見の記録ログ調整
  //---------------------------------------------------
  function historyTrim() {
    global $init;
    $fileName = "{$init->dirName}/hakojima.his";
    if(is_file($fileName)) {
      $fp = fopen($fileName, "r");
      Util::lockr($fp);

      $line = array();
      while($l = chop(fgets($fp, READ_LINE))) {
        array_push($line, $l);
        $count++;
      }
      Util::unlock($fp);
      fclose($fp);
      if($count > $init->historyMax) {

        if(!is_file($fileName))
          touch($fileName);

        $fp = fopen($fileName, "w");
        Util::lockw($fp);
        for($i = ($count - $init->historyMax); $i < $count; $i++) {
          fputs($fp, "{$line[$i]}\n");
        }
        Util::unlock($fp);
        fclose($fp);
//        chmod($fileName, 0666);
      }
    }
  }
  //---------------------------------------------------
  // ログ
  //---------------------------------------------------
  function out($str, $id = "", $tid = "") {
    array_push($this->logPool, "0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // 機密ログ
  //---------------------------------------------------
  function secret($str, $id = "", $tid = "") {
    array_push($this->secretLogPool,"1,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // 遅延ログ
  //---------------------------------------------------
  function late($str, $id = "", $tid = "") {
    array_push($this->lateLogPool,"0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // ログ書き出し
  //---------------------------------------------------
  function flush() {
    global $init;
    $fileName = "{$init->dirName}/hakojima.log0";

    if(!is_file($fileName))
      touch($fileName);

    $fp = fopen($fileName, "w");
    Util::lockw($fp);

    // 全部逆順にして書き出す
    if(!empty($this->secretLogPool)) {
      for($i = count($this->secretLogPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->secretLogPool[$i]}\n");
      }
    }
    if(!empty($this->lateLogPool)) {
      for($i = count($this->lateLogPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->lateLogPool[$i]}\n");
      }
    }
    if(!empty($this->logPool)) {
      for($i = count($this->logPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->logPool[$i]}\n");
      }
    }
    Util::unlock($fp);
    fclose($fp);
//    chmod($fileName, 0666);
  }
  //---------------------------------------------------
  // お知らせを出力
  //---------------------------------------------------
  function infoPrint() {
    global $init;

    if($init->infoFile == "") return;

    $fileName = "{$init->infoFile}";
    if(!is_file($fileName)) return;

    $fp = fopen($fileName, "r");
    while($line = fgets($fp, READ_LINE)) {
      $line = chop($line);
      print "{$line}<br>\n";
    }
    fclose($fp);
  }
}
class Cgi {
  var $mode = "";
  var $dataSet = array();
  //---------------------------------------------------
  // COOKIEを取得
  //---------------------------------------------------
	function getCookies() {
    if(!empty($_COOKIE)) {
      while(list($name, $value) = each($_COOKIE)) {
        switch($name) {
        case "OWNISLANDID":
          $this->dataSet['defaultID'] = $value;
          break;
        case "OWNISLANDPASSWORD":
          $this->dataSet['defaultPassword'] = $value;
          break;
        case "TARGETISLANDID":
          $this->dataSet['defaultTarget'] = $value;
          break;
        case "LBBSNAME":
          $this->dataSet['defaultName'] = $value;
          break;
        case "LBBSCOLOR":
          $this->dataSet['defaultColor'] = $value;
          break;
        case "POINTX":
          $this->dataSet['defaultX'] = $value;
          break;
        case "POINTY":
          $this->dataSet['defaultY'] = $value;
          break;
        case "COMMAND":
          $this->dataSet['defaultKind'] = $value;
          break;
        case "DEVELOPEMODE":
          $this->dataSet['defaultDevelopeMode'] = $value;
          break;
        case "SKIN":
          $this->dataSet['defaultSkin'] = $value;
          break;
        case "IMG":
          $this->dataSet['defaultImg'] = $value;
          break;
        }
      }
    }
  }
  //---------------------------------------------------
  // COOKIEを生成
  //---------------------------------------------------
  function setCookies() {
    $time = time() + 30 * 86400; // 現在 + 30日有効

    // Cookieの設定 & POSTで入力されたデータで、Cookieから取得したデータを更新
    if($this->dataSet['ISLANDID'] && $this->mode == "owner") {
      setcookie("OWNISLANDID",$this->dataSet['ISLANDID'], $time);
      $this->dataSet['defaultID'] = $this->dataSet['ISLANDID'];
    }
    if($this->dataSet['PASSWORD']) {
      setcookie("OWNISLANDPASSWORD",$this->dataSet['PASSWORD'], $time);
      $this->dataSet['defaultPassword'] = $this->dataSet['PASSWORD'];
    }
    if($this->dataSet['TARGETID']) {
      setcookie("TARGETISLANDID",$this->dataSet['TARGETID'], $time);
      $this->dataSet['defaultTarget'] = $this->dataSet['TARGETID'];
    }
    if($this->dataSet['LBBSNAME']) {
      setcookie("LBBSNAME",$this->dataSet['LBBSNAME'], $time);
      $this->dataSet['defaultName'] = $this->dataSet['LBBSNAME'];
    }
    if($this->dataSet['LBBSCOLOR']) {
      setcookie("LBBSCOLOR",$this->dataSet['LBBSCOLOR'], $time);
      $this->dataSet['defaultColor'] = $this->dataSet['LBBSCOLOR'];
    }
    if($this->dataSet['POINTX']) {
      setcookie("POINTX",$this->dataSet['POINTX'], $time);
      $this->dataSet['defaultX'] = $this->dataSet['POINTX'];
    }
    if($this->dataSet['POINTY']) {
      setcookie("POINTY",$this->dataSet['POINTY'], $time);
      $this->dataSet['defaultY'] = $this->dataSet['POINTY'];
    }
    if($this->dataSet['COMMAND']) {
      setcookie("COMMAND",$this->dataSet['COMMAND'], $time);
      $this->dataSet['defaultKind'] = $this->dataSet['COMMAND'];
    }
    if($this->dataSet['DEVELOPEMODE']) {
      setcookie("DEVELOPEMODE",$this->dataSet['DEVELOPEMODE'], $time);
      $this->dataSet['defaultDevelopeMode'] = $this->dataSet['DEVELOPEMODE'];
    }
    if($this->dataSet['SKIN']) {
      setcookie("SKIN",$this->dataSet['SKIN'], $time);
      $this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
    }
    if($this->dataSet['IMG']) {
      setcookie("IMG",$this->dataSet['IMG'], $time);
      $this->dataSet['defaultImg'] = $this->dataSet['IMG'];
    }
  }
  //---------------------------------------------------
  // POST、GETのデータを取得
  //---------------------------------------------------
  function parseInputData() {
    global $init;

    $this->mode = $_POST['mode'];
    if(!empty($_POST)) {
      while(list($name, $value) = each($_POST)) {
        $value = str_replace(",", "", $value);
        $value = JcodeConvert($value, 0, 4);
        //$value = HANtoZEN($value,4);
        if($init->stripslashes == true) {
          $this->dataSet["{$name}"] = stripslashes($value);
        } else {
          $this->dataSet["{$name}"] = $value;
        }
      }
    }
    if(!empty($this->dataSet['IMGLINE'])) {
      $neo = $this->dataSet['IMGLINE'];
      if(strcmp($neo, 'delete') == 0) {
        $neo = $init->imgDir;
      } else {
        $neo = str_replace("\\", "/", $neo);
        $neo = preg_replace("/\/[\w\.]+\.gif/", "", $neo);
        $neo = 'file:///' . $neo;
      }
      $this->dataSet['IMG'] = $neo;
    }
    if(!empty($_GET['Sight'])) {
      $this->mode = "print";
      $this->dataSet['ISLANDID'] = $_GET['Sight'];
    }
    if(!empty($_GET['target'])) {
      $this->mode = "targetView";
      $this->dataSet['ISLANDID'] = $_GET['target'];
    }
	$this->dataSet['mobile'] = false;
	$is_iphone = strpos( $_SERVER['HTTP_USER_AGENT'],'iPhone');
	$is_android = strpos($_SERVER['HTTP_USER_AGENT'],'Android');
	if(($is_iphone || $is_android) == true){
		$this->dataSet['mobile'] = true;
	}

    if($_GET['mode'] == "conf") {
      $this->mode = "conf";
    }
    if($_GET['mode'] == "New") {
      $this->mode = "New";
    }
    if($_GET['mode'] == "log") {
      $this->mode = "log";
    }
	if($_GET['mode'] == "wstat"){
      $this->mode = "wstat";
	}
	if($_GET['mode'] == "ally"){
      $this->mode = "ally";
	}

    $init->adminMode = 0;
    if(empty($_GET['AdminButton'])) {
      if(Util::checkPassword("", $this->dataSet['PASSWORD'])) { $init->adminMode = 1; }
    }
    if($this->mode == "turn") {
      // この段階で mode に turn がセットされるのは不正アクセスがある場合のみなのでクリアする
      $this->mode = '';
    }
    if(!empty($_GET['islandListStart'])) {
      $this->dataSet['islandListStart'] = $_GET['islandListStart'];
    } else {
      $this->dataSet['islandListStart'] = 1;
    }
    $this->dataSet["ISLANDNAME"] = mb_substr($this->dataSet["ISLANDNAME"], 0, 16,"UTF-8");
    $this->dataSet["MESSAGE"] = mb_substr($this->dataSet["MESSAGE"], 0, 100,"UTF-8");
    $this->dataSet["LBBSMESSAGE"] = mb_substr($this->dataSet["LBBSMESSAGE"], 0, 60,"UTF-8");
  }
//更新時刻を取得
  function lastModified() {
    global $init;
    $fileName = "{$init->dirName}/hakojima.dat";
    $time_stamp = filemtime($fileName);
    $time = gmdate("D, d M Y G:i:s", $time_stamp);
    $this->modifiedSinces($time_stamp);
  }
  function modifiedSinces($time) {
    $modsince = $_SERVER{'HTTP_IF_MODIFIED_SINCE'};

    $ms = gmdate("D, d M Y G:i:s", $time) . " GMT";
    if($modsince == $ms)
      // RFC 822
      header ("HTTP/1.1 304 Not Modified\n");

    $ms = gmdate("l, d-M-y G:i:s", $time) . " GMT";
    if($modsince == $ms)
      // RFC 850
      header ("HTTP/1.1 304 Not Modified\n");

    $ms = gmdate("D M j G:i:s Y", $time);
    if($modsince == $ms)
      // ANSI C's asctime() format
      header ("HTTP/1.1 304 Not Modified\n");
  }
}
//--------------------------------------------------------------------
class Main {

  function execute() {
    $hako = new Hako;
    $cgi = new Cgi;

    $cgi->parseInputData();
    $cgi->getCookies();
    if(!$hako->readIslands($cgi)) {
      HTML::header($cgi->dataSet);
      Error::noDataFile();
      HTML::footer();
      exit();
    }
    $cgi->setCookies();
    $cgi->lastModified();

    if($cgi->dataSet['DEVELOPEMODE'] == "java") {
      $html = new HtmlJS;
      $com = new MakeJS;

    } elseif($cgi->dataSet['DEVELOPEMODE'] == "adv"){
      $html = new HtmlAdv;
      $com = new MakeJS;
	} else {
      $html = new HtmlMap;
      $com = new Make;
    }
    switch($cgi->mode) {
    case "turn":
      $turn = new Turn;
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $turn->turnMain($hako, $cgi->dataSet);
      $html->main($hako, $cgi->dataSet); // ターン処理後、TOPページopen
      $html->footer();
      break;
    case "owner":
      $html->header($cgi->dataSet);
      $html->owner($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "command":
      $html->header($cgi->dataSet);
      $com->commandMain($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "new":
      $html->header($cgi->dataSet);
      $com->newIsland($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "comment":
      $html->header($cgi->dataSet);
      $com->commentMain($hako, $cgi->dataSet);
      $html->footer();
      break;
	case "postnews":
      $html->header($cgi->dataSet);
      $com->NewsMain($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "Cname":
      $html->header($cgi->dataSet);
      $com->Capitalname($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "Banner":
      $html->header($cgi->dataSet);
      $com->Banner($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "print":
      $html->header($cgi->dataSet);
      $html->visitor($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "targetView":
      $html->header($cgi->dataSet);
      $html->printTarget($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "change":
      $html->header($cgi->dataSet);
      $com->changeMain($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "ChangeOwnerName":
      $html->header($cgi->dataSet);
      $com->changeOwnerName($hako, $cgi->dataSet);
      $html->footer();
      break;
	case "freezeNation":
      $html->header($cgi->dataSet);
      $com->freezeNation($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "regT":
      $html->header($cgi->dataSet);
      $com->regularTradeMain($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "lbbs":
      $lbbs = new Make;
      $html->header($cgi->dataSet);
      $lbbs->localBbsMain($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "skin":
      $html = new HtmlSetted;
      $html->header($cgi->dataSet);
      $html->setSkin();
      $html->footer();
      break;

    case "imgset":
      $html = new HtmlSetted;
      $html->header($cgi->dataSet);
      $html->setImg();
      $html->footer();
      break;

    case "conf":
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->regist($hako, $cgi->dataSet);
      $html->footer();
      break;

	case "New":
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->newDiscovery($hako, $cgi->dataSet);
      $html->footer();
      break;

    case "log":
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->log();
      $html->footer();
      break;

	case "wstat":
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->World_Stat();
      $html->footer();
      break;

	case "ally":
      $html = new HtmlAlly;
      $html->header($cgi->dataSet);
	  $html->allyTop($hako);
      $html->footer();
      break;

    default:
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->main($hako, $cgi->dataSet);
      $html->footer();
    }
    exit();
  }
}
$start = new Main;
$start->execute();
?>
