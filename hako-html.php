<?php
/*******************************************************************

  箱庭諸国２ for PHP


  $Id: hako-html.php,v 1.10 2004/02/14 15:51:35 watson Exp $

*******************************************************************/

if(GZIP == true) {
  // gzip圧縮転送用
  require_once "HTTP/Compress.php";
  $http = new HTTP_Compress;
}

//--------------------------------------------------------------------
class HTML {



  //---------------------------------------------------
  // HTML ヘッダ出力
  //---------------------------------------------------


  function header($data = "") {
    global $init;
    global $PRODUCT_VERSION;

    // 圧縮転送
    if(GZIP == true) {
      global $http;
      $http->start();
    }
	header("X-Product-Version: {$PRODUCT_VERSION}");

	$css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
	$bimg =  (empty($data['defaultImg'])) ? $init->imgDir : $data['defaultImg'];
	$param['bimg'] = $bimg;
	$param['cssDir'] = $init->cssDir;
	$param['css'] = $css;
	$param['title'] =$init->title;
	$param['baseDir'] = $init->baseDir;

	if($data['mobile'] == true){
		print HTML::tplengine('./templates/mobile/head.html',$param);
	}else{
		$param['urlTopPage'] = $init->urlTopPage;
		$param['t_title'] = $GLOBALS['THIS_FILE'];
		$param['urlManual'] = $init->urlManual;
		$param['urlHowTo'] = $init->urlHowTo;
		$param['urlBbs'] = $init->urlBbs;
		print HTML::tplengine('./templates/head.html',$param);
	}
  }
  //---------------------------------------------------
  // HTML フッタ出力
  //---------------------------------------------------
  function footer() {
    global $init;

	if(GZIP == true) {
      global $http;
      $http->output();
    }

	$param['adminName'] = $init->adminName;
	$param['adminEmail'] = $init->adminEmail;
	$param['urlBbs'] = $init->urlBbs;
	$param['urlTopPage'] = $init->urlTopPage;

	if($init->performance) {
    	list($tmp1, $tmp2) = split(" ", $init->CPU_start);
        list($tmp3, $tmp4) = split(" ", microtime());
        $tex = sprintf("　<SMALL>(CPU : %.6f秒)</SMALL>", $tmp4-$tmp2+$tmp3-$tmp1);
    }

	$param['perform'] = $tex;
	if($data['mobile'] == true){
		print HTML::tplengine('./templates/mobile/footer.html',$param);
	}else{
		print HTML::tplengine('./templates/footer.html',$param);
	}
  }
  //---------------------------------------------------
  // 最終更新時刻 ＋ 次ターン更新時刻出力
  //---------------------------------------------------
  function lastModified($hako) {
    global $init;
    $timeString = date("Y/m/d H時", $hako->islandLastTime);
    print <<<END
<h2 class="lastModified">最終更新時間 : $timeString
END;

    if(($init->endTurn > 0) && ($hako->islandTurn >= $init->endTurn)) {
      print "<span class='attention'>(ゲーム終了)</span>\n";
    } else {
      print <<<END

<script type="text/javascript">
<!--
 var nextTime = $hako->islandLastTime + $init->unitTime;
 function watch() {
 now = new Date();
 remain = nextTime - Math.floor(now / 1000);
 if(remain < 0) {
   hour = "00";
   min  = "00";
   sec  = "00";
 } else {
   hour = Math.floor(remain / 3600);
   min  = Math.floor(remain % 3600 / 60);
   sec  = Math.floor(remain % 3600 % 60);
   if(min < 10) { min = "0" + min;}
   if(sec < 10) { sec = "0" + sec;}
 }
 document.form.watch.value ='次のターンまで' + hour + '時間 ' + min + '分' + sec + '秒';
 setTimeout("watch()", 999); // 1000msec = 1sec
}
document.write('<FORM name=form><INPUT style="background-color: orange;" name=watch size=33></FORM>');
watch();

//-->
</script>


END;

//


}

    print <<<END
</h2>

END;
   }
  //---------------------------------------------------
  // テンプレートエンジン
  //---------------------------------------------------
	Function tplengine($tpl,$param){
		global $init;
		$param = array_merge($param,$init->tplcss);
		//テンプレートエンジン部分
	    $html = file_get_contents($tpl);
	    $html = preg_replace('/{(.+?)}/e', '$param[$1]', $html);
	    return $html;
	}
}
//--------------------------------------------------------------------
class HtmlTop extends HTML {
  //---------------------------------------------------
  // ＴＯＰページ
  //---------------------------------------------------
  function main($hako, $data) {
    global $init;
	$wns = new WNSsys();

    // 最終更新時刻 ＋ 次ターン更新時刻出力
    $this->lastModified($hako);
    if(empty($data['defaultDevelopeMode']) || $data['defaultDevelopeMode'] == "cgi") {
      $radio = "checked"; $radio2 = "";
    } else {
      $radio = ""; $radio2 = "checked";
    }
    if(DEBUG == true) {
      print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="mode" value="debugTurn">
<input type="submit" value="ターンを進める">
</form>

END;
  }
    if (!$init->endTurn) {
 	$calendars = Util::MKCal($hako->islandTurn,0);
      print "<h2 class='Turn'>$calendars</h2>\n";
    } else {
      print "<h2 class='Turn'>フリューゲル暦 $hako->islandTurn / $init->endTurn 期</h2>\n";
    }

    $fileName = "{$init->dirName}/statistic.xml";
	$xmldata = simplexml_load_file($fileName);

	$num = count($xmldata);
	$sallpop = Util::Rewriter2('',$xmldata->factdata[$num-1]->pop);
	$sallfarm =Util::Rewriter2('',$xmldata->factdata[$num-1]->farm);
	$sallind = Util::Rewriter2('',$xmldata->factdata[$num-1]->ind);
	$sallmarket = Util::Rewriter2('',$xmldata->factdata[$num-1]->market);
	$spgoods = Util::Rewriter2('money',$xmldata->factdata[$num-1]->pgoods);
	$smoneys =  Util::Rewriter2('money',$xmldata->factdata[$num-1]->pmoneys);
	$allshell = $xmldata->factdata[$num-1]->shell;
    print <<<END

<div ID="Loginbox">
	<div ID="login">
		<form action="{$GLOBALS['THIS_FILE']}" method="post">
		国名<br>
		<select name="ISLANDID">$hako->islandList</select><br>
		パスワード<br><input type="password" name="PASSWORD" value="{$data['defaultPassword']}" size="32" maxlength="32"><br>
		<input type="hidden" name="mode" value="owner">
		<input type="radio" name="DEVELOPEMODE" value="cgi" id="cgi" $radio><label for="cgi">通常版</label>
		<input type="radio" name="DEVELOPEMODE" value="java" id="java" $radio2><label for="java">JS版</label>
		<input type="radio" name="DEVELOPEMODE" value="adv" id="adv" $radio3><label for="adv">（開発中）</label><br>
		<input type="submit" id="login_button" value="ログイン">
		</form>
	</div>
	<div ID="Register">
		新規登録
		<a href="{$GLOBALS['THIS_FILE']}?mode=New"><img src="regist.png"></a>
	</div>
</div>

<div ID="IslandView">
<h2>諸国の状況</h2>
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}総人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総農業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総工業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総商業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総工業生産高{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総商業売上高{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総砲弾{$init->_tagTH}</th>
</tr>
<tr>
<td {$init->bgInfoCell}>$sallpop{$init->unitPop}</td>
<td {$init->bgInfoCell}>$sallfarm{$init->unitPop}</td>
<td {$init->bgInfoCell}>$sallind{$init->unitPop}</td>
<td {$init->bgInfoCell}>$sallmarket{$init->unitPop}</td>
<td {$init->bgInfoCell}>$spgoods{$init->unitMoney}</td>
<td {$init->bgInfoCell}>$smoneys{$init->unitMoney}</td>
<td {$init->bgInfoCell}>$allshell{$init->unitShell}</td>

</tr>
</table><a href="{$GLOBALS['THIS_FILE']}?mode=wstat">[世界統計]</a>
END;

if ($hako->islandNumber != 0) {
  $islandListStart = $data['islandListStart'];
  if ($init->islandListRange == 0) {
    $islandListSentinel = $hako->islandNumber;
  } else {
    $islandListSentinel = $islandListStart + $init->islandListRange - 1;
    if ($islandListSentinel > $hako->islandNumber) {
      $islandListSentinel = $hako->islandNumber;
    }
  }
  print " [" . $islandListStart . " - " . $islandListSentinel . "位 ]";
}
if($data['mobile'] != true){
echo $wns->MakeHTML(10,$init->baseDir);


    print <<<END

<p>
国の名前をクリックすると、<strong>観光</strong>することができます。
</p>
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell} colspan="14">{$init->tagTH_}国情報{$init->_tagTH}</th>
</tr>
END;

}else{
	echo $wns->MakeHTML(5,$init->baseDir);
}
if (($islandListStart != 1) || ($islandListSentinel != $hako->islandNumber)) {
  for ($i = 1; $i <= $hako->islandNumber ; $i += $init->islandListRange) {
    $j = $i + $init->islandListRange - 1;
    if ($j > $hako->islandNumber) {
      $j = $hako->islandNumber;
    }
      print " ";
      if ( $i != $islandListStart ) {
        print "<a href=\"" . $GLOBALS['THIS_FILE'] . "?islandListStart=" . $i ."\">";
      }
        print " [ ". $i . " - " . $j . " ]";
        if ($i != $islandListStart) {
          print "</a>";
        }
      }
    }
    $islandListStart--;
    for($i = $islandListStart; $i < $islandListSentinel ; $i++) {
      $island = $hako->islands[$i];
      $j = $i + 1;
      $id    = $island['id'];
      $pop   = Util::Rewriter2('',$island['pop']) . $init->unitPop;
	  $spop  = ($island['spop'] <= 10) ? "1万人未満" : $spop  = Util::Rewriter2('',$island['spop']) . $init->unitPop;
      $area  = $island['area'] . $init->unitArea;
      $point = $island['point'];
      $eisei = $island['eisei'];
	  $senkan = $island['ship']['senkan'];
      $unemployed = ($island['pop'] - ($island['service'] + $island['farm'] + $island['factory'] + $island['market']) * 10 ) / $island['pop'] * 100;
      $unemployed = '<font color="' . ($unemployed < 0 ? 'black' : 'red') . '">' . sprintf("%.2f%%", $unemployed) . '</font>';
      $peop  = ($island['peop'] <= 0) ? "増加なし" : "+".Util::Rewriter2('',$island['peop']).$init->unitPop;
      $okane  = ($island['gold'] < 0) ? "{$island['gold']}{$init->unitMoney}" : "+{$island['gold']}{$init->unitMoney}";
      $gohan  = ($island['rice'] < 0) ? "{$island['rice']}{$init->unitFood}" : "+{$island['rice']}{$init->unitFood}";
      $poin  = ($island['pots'] < 0) ? "{$island['pots']}pts" : "+{$island['pots']}pts";
      $farm  = ($island['farm'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['farm'] * 10) . $init->unitPop;
      $factory  = ($island['factory'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['factory'] * 10) . $init->unitPop;
      $market  = ($island['market'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['market'] * 10) . $init->unitPop;
	  $service =  ($island['service'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['service'] * 10) . $init->unitPop;
	  $armypop =  ($island['milpop']<= 0) ? "保有せず" : Util::Rewriter2('',($island['milpop'] * 10)) . $init->unitPop;
	  $sfarmypop = ($island['sfarmy']<= 0) ? "保有せず" : Util::Rewriter2('',($island['sfarmy']) * 10) . $init->unitPop;
	  $navypop =  ($island['navy']+$senkan<= 0) ? "保有せず" : Util::Rewriter2('',($island['navy']+$senkan*3)*3) . $init->unitPop;
      $mfactory  = ($island['mfactory'] <= 0) ? "保有せず" : $island['mfactory'] . $init->unitMaterial;
      $sfactory  = ($island['sfactory'] <= 0) ? "保有せず" : $island['sfactory'] . $init->unitShell;
      $ffactory  = ($island['ffactory'] <= 0) ? "保有せず" : $island['ffactory'] . $init->unitOil;
      $hatuden  = ($island['hatuden'] <= 0) ? "0kw" : $island['hatuden'] * 1000 . kw;
      $mining = ($island['mining'] <= 0) ? "保有せず" : $island['mining'] * 10 . $init->unitPop;
      $orgmining  = $island['mining'];
      $comment  = $island['comment'];
      $comment_turn = $island['comment_turn'];
	  $news 	    = $island['news'];
      $monster = '';
      $taiji    = ($island['taiji'] <= 0) ? "0匹" : $island['taiji'] * 1 . $init->unitMonster;
      $tenki    = $island['tenki'];
	  $hapiness = $island['hapiness'];
	  $siji		= $island['siji'];
	  $invest	= $island['invest'];
	  $freeze   = $island['freeze'];
	  $capital  = $island['capital'];
	  $Cname    = $island['Cname'];
	  $edinv	= $island['edinv'];
	  $banum	= $island['banum'];
	  $indnum	= $island['indnum'];
	  $polit	= $island['polit'];
	  $soclv	=$island['soclv'];
	  if ($freeze == 1) {
	  $freeze = '<font color="red">' . '凍結中' . '</font>';
	  }else {
	  $freeze = "";
	  }
      if($mining == 0) {
        $mining = "保有せず";
      } else {
        $minelv; // 総鉱山レベル(各種)
        $mining = "";
        if($orgmining % 16 > 0) {
          // 採石場
          $minelv = $orgmining % 16;
          if($minelv == 15){
            $minelv="M";
          }
          $mining .= "石$minelv";
        }
        if(((int)($orgmining / 16) % 16) > 0) {
          // 炭坑
          $minelv = (int)($orgmining / 16) % 16;
          if($minelv == 15){
            $minelv = "M";
          }
          $mining .= "炭$minelv";
        }
        if(((int)($orgmining / 256) % 16) > 0) {
          // 鉄鉱山
          $minelv = (int)($orgmining / 256) % 16;
          if($minelv==15){
            $minelv="M";
          }
          $mining .= "鉄$minelv";
        }
        if(((int)($orgmining / 4096) % 16) > 0) {
          // ウラン鉱山
          $minelv = (int)($orgmining / 4096) % 16;
          if($minelv == 15){
            $minelv = "M";
          }
          $mining .= "ウ$minelv";
        }
        if(((int)($orgmining / 65536) % 16) > 0) {
          // 銀鉱山
          $minelv = (int)($orgmining / 65536) % 16;
          if($minelv == 15){
            $minelv = "M";
          }
          $mining .= "銀$minelv";
        }
      }
	  //主産業表示
	  $industry = array("農業国"=>$island['farm'],"工業国"=>$island['factory'],"商業国"=>$island['market']);
	  arsort($industry);
	  $Tokka = key($industry);

	  $industpic = array("農業国"=>"farm.png","工業国"=>"factory.png","商業国"=>"business.png");

      if($island['monster'] > 0) {
        $monster = "<strong class=\"monster\">[怪獣{$island['monster']}体]</strong>";
      }

      $name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
	  $pname = $name;
      if($island['absent']  == 0) {
        $name = "{$init->tagName_}{$name}{$init->spanend}";
      } else {
        $name = "{$init->tagName2_}{$name}({$island['absent']}){$init->spanend2}";
      }
      if(!empty($island['owner'])) {
        $owner = $island['owner'];
      } else {
        $owner = "コメント";
      }

      $prize = $island['prize'];
      $prize = $hako->getPrizeList($prize);

      $sora = "";
      if($tenki == 1) {
        $sora .= "<IMG SRC=\"tenki1.gif\" ALT=\"晴れ\">";
      } elseif($tenki == 2) {
        $sora .= "<IMG SRC=\"tenki2.gif\" ALT=\"曇り\">";
      } elseif($tenki == 3) {
        $sora .= "<IMG SRC=\"tenki3.gif\" ALT=\"雨\">";
      } elseif($tenki == 4) {
        $sora .= "<IMG SRC=\"tenki4.gif\" ALT=\"雷\">";
      } else {
        $sora .= "<IMG SRC=\"tenki5.gif\" ALT=\"雪\">";
      }

      $eiseis = "";
      for($e = 0; $e < $init->EiseiNumber; $e++) {
        if($eisei[$e] > 0) {
          $eiseis .= "<img src=\"eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eisei[$e]}%\"> ";
        } else {
          $eiseis .= "";
        }
      }

      $viking = "";
      if($island['ship']['viking'] > 0) {
        $viking .= "<IMG SRC=\"viking.gif\" width=\"16\" height=\"16\" ALT=\"海賊船出現中\">";
      } else {
        $viking .= "　";
      }

      $start = "";
      if(($hako->islandTurn - $island['starturn']) < $init->noMissile) {
        $start .= "<IMG SRC=\"start.gif\" width=\"16\" height=\"16\" ALT=\"初心者マーク\">";
      } else {
        $start .= "　";
      }
	  $indpic = $industpic[$Tokka];
	  $indad1 = "<IMG SRC=\"{$indpic}\" width=\"25\" height=\"25\" title=\"{$Tokka}\" ALT=\"{$Tokka}\">";
	  $happiad = "";
	  if($hapiness > 81) {
        $happiad .= "<IMG SRC=\"happy1.png\"";
      } elseif($hapiness > 61) {
        $happiad .= "<IMG SRC=\"happy2.png\"";
      } elseif($hapiness > 41) {
        $happiad .= "<IMG SRC=\"happy3.png\"";
      } elseif($hapiness > 21) {
        $happiad .= "<IMG SRC=\"happy4.png\"";
      } else {
        $happiad .= "<IMG SRC=\"happy5.png\"";
      }
       $happiad .= "width=\"23\" height=\"23\" title=\"幸福度： {$hapiness}\" ALT=\"幸福度： {$hapiness}\"><br/> {$hapiness}";
	  $numrow = 5;
	  if ($point < $init->BaseHappiDemand[0]){
	  $numcell = "<th {$init->bgNumberCella} rowspan=\"$numrow\">{$init->tagNumber_}$j{$init->_tagNumber}";
	  $nprop ="[Least Developed]";
	  }elseif ($point < $init->BaseHappiDemand[1]){
	  $numcell = "<th {$init->bgNumberCellb} rowspan=\"$numrow\">{$init->tagNumber_}$j{$init->_tagNumber}";
	  $nprop ="[Developing]";
	  }elseif ($point < $init->BaseHappiDemand[2]){
	  $numcell = "<th {$init->bgNumberCellc} rowspan=\"$numrow\">{$init->tagNumber_}$j{$init->_tagNumber}";
	  $nprop ="[Newly Industrializing]";
	  }else {
	  $numcell = "<th {$init->bgNumberCelld} rowspan=\"$numrow\">{$init->tagNumber_}$j{$init->_tagNumber}";
	  $nprop ="[Developed]";
	  }

		$invest = ceil($invest);
		$investad = "";
		if ($invest > 0){
			$investad .="<IMG SRC=\"public.png\" width=\"23\" height=\"23\" title=\"インフラ指数： {$invest}\" ALT=\"インフラ指数： {$invest}\"><br/> {$invest}";
	    }
		$edinv = ceil($edinv);
		$edcad = "";
		if ($edinv > 0){
			$edcad .="<IMG SRC=\"edc.png\" width=\"23\" height=\"23\" title=\"教育指数：{$edinv}\" ALT=\"教育指数：{$edinv}\"><br/> {$edinv}";
		}

		$socad = "";
		$socad .="<IMG SRC=\"socsec.png\" width=\"23\" height=\"23\" title=\"社会保障指数：{$soclv}\" ALT=\"社会保障指数：{$soclv}\"><br/> {$soclv}";

		if ((!$Cname == "")&&(!$capital == "")){
			$Cname = "<br>{$init->tagCapName_}首都：{$Cname}{$init->spanend}";
		}else{
			$Cname = "";
		}
		$owner =  "<br>{$init->tagTH_}元首：{$owner}{$init->_tagTH}";
		$bannerad = "img/up/log";
		if (file_exists($bannerad.'/'.$banum.'.png')){
			$bannerad = 'up/log/'.$banum.'.png';
			$bannerad = "<IMG SRC=\"{$bannerad}\" width=\"45\" height=\"30\"align=\"left\" title=\"{$pname}旗\" ALT=\"{$pname}旗\">";
		}else{
			$bannerad = "";
		}
		$indad = "";
		if ($indnum == ""){
			$indnum = 0;
		}
		$indad = 'ind'.$indnum.'.png';
		$indad = "<IMG SRC=\"{$indad}\">";


			if ($polit == 0){
				$poltype = "権威主義";
			}elseif ($polit == 1){
				$poltype = "民主主義";
			}elseif ($polit == 2){
				$poltype = "警察国家";
			}

			$hmilpop ="<IMG SRC=\"milpop.png\" width=\"25\" height=\"25\" title=\"軍事力\" ALT=\"軍事力\">";
			if($polit != 2){
				$mildat = "陸空軍：{$armypop}　海外部隊：{$sfarmypop}  海軍：{$navypop}（戦艦{$senkan}艦隊）";
			}else{
				$mildat = "非公開";
			}
			$politad ="<IMG SRC=\"poli{$polit}.png\" width=\"25\" height=\"25\" title=\"{$poltype}\" ALT=\"{$poltype}\">";

		$httool = "<div class=\"tooltip\" id=\"t{$id}\"><p id=\"comm\">{$comment}</p></div>";

		$scapital = $init->Captext[$capital];
	  	if($data['mobile'] == true){
			$param['banner'] = $bannerad;
			$param['name'] = $name;
			$param['id'] = $id;
			print HTML::tplengine('./templates/mobile/nation.html',$param);
		  }else{
      if($init->moneyMode) {
        $mTmp1 = Util::aboutMoney($island['food'],'food');
        $mTmp2 = Util::aboutMoney($island['goods'],'goods');
        $mTmp3 = Util::aboutMoney($island['money'],'money');
        $mTmp4 = Util::aboutMoney($island['material'],'material');
        $mTmp5 = Util::aboutMoney($island['fuel'],'fuel');
        $mTmp6 = Util::aboutMoney($island['shell'],'shell');
        $mTmp7 = Util::aboutMoney($island['wood'],'wood');
        $mTmp8 = Util::aboutMoney($island['stone'],'stone');
        $mTmp9 = Util::aboutMoney($island['steel'],'steel');
        $mTmp10 = Util::aboutMoney($island['oil'],'oil');
        $mTmp11 = Util::aboutMoney($island['silver'],'silver');
        $mStr1 = "<td {$init->bgInfoCell}>$mTmp1</td>\n";
        $mStr2 = "<td {$init->bgInfoCell}>$mTmp2</td>\n";
        $mStr3 = "<td {$init->bgInfoCell}>$mTmp3</td>\n";
        $mStr4 = "<td {$init->bgInfoCell}>$mTmp4</td>\n";
        $mStr5 = "<td {$init->bgInfoCell}>$mTmp5</td>\n";
        $mStr6 = "<td {$init->bgInfoCell}>$mTmp6</td>\n";
        $mStr7 = "<td {$init->bgInfoCell}>$mTmp7</td>\n";
      } else {
        $mStr1 = "{$island['food']}{$init->unitFood}";
        $mStr2 = "<td {$init->bgInfoCell}>{$island['goods']}{$init->unitGoods}</td>\n";
        $mStr3 = "<td {$init->bgInfoCell}>{$island['money']}{$init->unitMoney}</td>\n";
        $mStr4 = "<td {$init->bgInfoCell}>{$island['material']}{$init->unitMaterial}</td>\n";
        $mStr5 = "<td {$init->bgInfoCell}>{$island['fuel']}{$init->unitFuel}</td>\n";
        $mStr6 = "<td {$init->bgInfoCell}>{$island['shell']}{$init->unitShell}</td>\n";
      }


      print "<tr>\n";
      print "{$numcell}<br>{$bannerad}</th>\n";
      print "<td {$init->bgNameCell} rowspan=\"3\"><p>
	  <a href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$name}</a>
	  {$sora}{$Cname}{$owner}{$start} {$monster} {$viking}<br><font size=\"small\">{$nprop}
	  <br>得点:{$point} <b> {$freeze}</b><br>({$peop} {$okane} {$gohan} {$poin})</font></td>\n";
	  print "<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>\n";
	  print "<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>\n";
	  print "<th {$init->bgTitleCell}>{$init->tagTH_}滞在者{$init->_tagTH}</th>\n";
	  print "<th {$init->bgTitleCell}>{$init->tagTH_}人工衛星{$init->_tagTH}</th>\n";
	  print "<th {$init->bgTitleCell} colspan=\"1\">{$init->tagTH_}鉱業{$init->_tagTH}</th>\n";
	  print "</tr>\n";

      print "<tr>\n";
      print "<td {$init->bgInfoCell}>$area</td>\n";
      print "<td {$init->bgInfoCell}>$pop</td>\n";
      print "<td {$init->bgInfoCell}>$spop</td>\n";
	  print "<td {$init->bgInfoCell}>{$eiseis}</td>\n";
      print "<td {$init->bgInfoCell}>$mining</td>\n";
      print "</tr>\n";

      print "<tr>\n";
	  print "<td class=\"prevnat\" colspan=\"10\">{$init->tagTH_}<ul>
	  		<li class=\"IndD\"><a href=\"t{$id}\">連絡<div class=\"databox\">
			<p id=\"comm\">{$comment}</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">産業<br />{$indad1}<div class=\"databox\">
			<p>{$Tokka}  失業率:{$unemployed}<br/>公務員:{$service} 農業:{$farm} 工業:{$factory} 商業:{$market} {$indad}</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">資源<div  class=\"databox\">
			<table><tr><td>食料{$mTmp1}</td><td>木材{$mTmp7}</td><td>石材{$mTmp8}</td><td>銀{$mTmp11}</td>
	  		<td>鋼鉄{$mTmp9}</td><td>石油{$mTmp10}</td><td>燃料{$mTmp5}</td>
			<td>資金{$mTmp3}</td><td>商品{$mTmp2}</td><td>建材{$mTmp4}</td><td>砲弾{$mTmp6}</td></tr></table></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">政体<br/>{$politad}<div  class=\"databox\">
			<p>政体：{$poltype}<br/>行政：{$scapital}（Lv{$capital}）</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">幸福<br/>{$happiad}<div  class=\"databox\">
			<p>幸福度</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">福祉<br/>{$socad}<div  class=\"databox\">
			<p>社会保障</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">国土<br/>{$investad}<div  class=\"databox\">
			<p>インフラ</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">教育<br/>{$edcad}<div  class=\"databox\">
			<p>教育</p></div></a></li>
			<li class=\"IndD\"><a href=\"t{$id}\">軍隊<br/>{$hmilpop}<div  class=\"databox\">
			<p>{$mildat}</p></div></a></li>
	  </ul>{$init->_tagTH}</td>\n";
      print "</tr>\n";
	  print "<tr>\n";
	  print "<td></td>\n";
      print "<td class=\"datacel\" colspan=\"10\">{$httool}</td>\n";
	  print "</tr>\n";

      print "<tr>\n";
	  print "<td {$init->bgInfoCell}>{$prize}</td>\n";
      print "<td {$init->bgCommentCell} colspan=\"10\"><IMG SRC=\"radio.png\" width=\"20\" height=\"20\" title=\"報道\" ALT=\"報道\">{$news}</td>\n";
	  print "</tr>\n";
	  }
    }
    print "</table>\n</div>\n";
    print "<hr>\n";
    $this->historyPrint();

      print <<<END
<FORM action="{$GLOBALS['THIS_FILE']}?mode=conf" method="POST">
<DIV align="right">
<INPUT TYPE="password" NAME="PASSWORD" SIZE=8 MAXLENGTH=32>
<INPUT TYPE="submit" VALUE="管理用" NAME="AdminButton">
</DIV>
</FORM>
END;
  }
  //---------------------------------------------------
  // 国の登録と設定
  //---------------------------------------------------
  function regist(&$hako, $data = "") {
    global $init;
    $this->changeIslandInfo($hako->islandList);
    $this->changeOwnerName($hako->islandList);
	$this->freezeNation($hako->islandList);
    $this->setStyleSheet();
    $this->setLocalImage($data);
  }
  //---------------------------------------------------
  // 新しい国を探す
  //---------------------------------------------------
  function newDiscovery($number,$islandTurn) {
	global $init;

    print "<div id=\"NewIsland\">\n";
    print "<h2>新しい国を探す</h2>\n";
    if(($number < $init->maxIsland) &&
       (($init->entryTurn == 0) || ($islandTurn < $init->entryTurn))) {
      if($init->registMode == 1 && $init->adminMode == 0) {
        print "当箱庭では不適当な国名などの事前チェックを行っています。<BR>\n";
        print "参加希望の方は、管理人まで「国名」と「パスワード」をメールしてください。<BR>\n";
      } else {
      print <<<END

<p><IMG src="Warning.png"><strong>ユーザー登録</strong>はお済ですか？</a>まだの方は<a href="http://tanstafl.sakura.ne.jp/register.php">こちら</a>からまずユーザー登録を行ってください。<br>ユーザー登録を行うことで掲示板やプライベートメッセージが利用可能になります。</p>
<form action="{$GLOBALS['THIS_FILE']}" method="post" accept-charset=”UTF-8″>
<table>
<tr><th>国名</th><td><input type="text" name="ISLANDNAME" size="32" maxlength="32"><br>
国名についての<a href="http://tanstafl.sakura.ne.jp/rule.html#name">注意</a>は参照しましたか？</td></tr>
<tr><th>国主名</th><td><input type="text" name="OWNERNAME" size="32" maxlength="32"></td></tr>
<tr><th>パスワード</th><td><input type="password" name="PASSWORD" size="32" maxlength="32"></td></tr>
<tr><th>パスワードを再度入力してください</th><td><input type="password" name="PASSWORD2" size="32" maxlength="32"></td></tr>
</table>
<label><input type="checkbox" name="agree" value="agree">私は<strong><a href="http://tanstafl.sakura.ne.jp/rule.html" target="_blank">ゲームルール</a></strong>を熟読し内容や制限項目を理解した上で同意しゲームに参加します。</label>
<br>
<input type="hidden" name="mode" value="new">
<input type="submit" value="新しい国を作る">
</form>
END;
      }
    } else {
      if (!$init->endTurn) {
        print "国の数が最大数です・・・現在登録できません。\n";
      } else {
        print "国の数が最大数か、その他の理由により、現在登録できません。\n";
      }
    }
    print "</div>\n";
    print "<hr>\n";
  }
  //---------------------------------------------------
  // 国の名前とパスワードの変更
  //---------------------------------------------------
  function changeIslandInfo($islandList = "") {
    global $init;
    print <<<END
<div id="ChangeInfo">
<h2>国の名前とパスワードの変更</h2>
<p>
(注意)名前の変更には500億Vaかかります。
</p>
<form action="{$GLOBALS['THIS_FILE']}" method="post" accept-charset=”UTF-8″>
どの国ですか？<br>
<select NAME="ISLANDID">
$islandList
</select>
<br>
どんな名前に変えますか？(変更する場合のみ)<br>
<input type="text" name="ISLANDNAME" size="32" maxlength="32"><br>
パスワードは？(必須)<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
新しいパスワードは？(変更する時のみ)<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
念のためパスワードをもう一回(変更する時のみ)<br>
<input type="password" name="PASSWORD2" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="change">
<input type="submit" value="変更する">
</form>
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // オーナー名の変更
  //---------------------------------------------------
  function changeOwnerName($islandList = "") {
    global $init;
    print <<<END
<div id="ChangeOwnerName">
<h2>オーナー名の変更</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post" accept-charset=”UTF-8″>
どの国ですか？<br>
<select name="ISLANDID">
{$islandList}
</select>
<br>
新しいオーナー名は？<br>
<input type="text" name="OWNERNAME" size="32" maxlength="32"><br>
パスワードは？<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="ChangeOwnerName">
<input type="submit" value="変更する">
</form>
</div>
END;
  }
  //---------------------------------------------------
  // 国の凍結（超テスト仕様）
  //---------------------------------------------------
  function freezeNation($islandList = ""){
      global $init;
      if($init->adminMode == 0) {
    	print "<div id=\"freezeNation\">\n";
	    print "<h2>国を凍結する</h2>\n";
        print "当箱庭では管理人のみ国の凍結/凍結解除を行えます。<BR>\n";
        print "凍結/解除をご希望の方は掲示板の該当スレッドにて申請を行ってください。<BR>\n";
      } else {
    print <<<END
<div id="freezeNation">
<h2>国の凍結/凍結解除</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どの国ですか？<br>
<select name="ISLANDID">
{$islandList}
</select>
<br>
パスワードは？<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="freezeNation">
<input type="submit" value="凍結/凍結解除">
</form>
</div>

END;
  }
  }
  //---------------------------------------------------
  // スタイルシートの設定
  //---------------------------------------------------
  function setStyleSheet() {
    global $init;
    $styleSheet;
    for($i = 0; $i < count($init->cssList); $i++) {
      $styleSheet .= "<option value=\"{$init->cssList[$i]}\">{$init->cssList[$i]}</option>\n";
    }
    print <<<END
<div id="HakoSkin">
<h2>スタイルシートの設定</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<select name="SKIN">
$styleSheet
</select>
<input type="hidden" name="mode" value="skin">
<input type="submit" value="設定">
</form>
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // 画像のローカル設定
  //---------------------------------------------------
  function setLocalImage($data = "") {
    global $init;
    $Himgflag;
    if(empty($data['defaultImg']) || (strcmp($data['defaultImg'], $init->imgDir) == 0)){
      $Himgflag = '<span class=attention>未設定</span>';
    } else {
      $Himgflag = $data['defaultImg'];
    }
    print <<<END
<div id="localImage">
<h2>画像のローカル設定</h2>
<table border width=50%><tr><td class='N'>
　画像転送によるサーバーへの負荷を軽減するだけでなく、あなたのパソコンにある画像を呼び出すので、表示スピードが飛躍的にアップします。<br>
　画像は<B><a href="{$init->imgPack}">ここ</a></B>からダウンロードして、１つのフォルダに解凍し、下の設定で「land0.gif」を指定して下さい。<br>
　詳しくは<B><a href="{$init->imgExp}">説明のページ</a></B>をご覧下さい。
</td></tr></table>
<table border=0 width=50%><tr><td class="M">
現在の設定<B>[</B> ${Himgflag} <B>]</B>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type=file name="IMGLINE">
<input type="hidden" name="mode" value="imgset">
<input type="submit" value="設定">
</form>

<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type=hidden name="IMGLINE" value="delete">
<input type="hidden" name="mode" value="imgset">
<input type="submit" value="設定を解除する">
</form>
</td></tr></table>
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // 最近の出来事
  //---------------------------------------------------
  function log() {
    global $init;
    print "<CENTER>{$GLOBALS['BACK_TO_TOP']}</CENTER>";
    print "<div id=\"RecentlyLog\">\n";
    print "<h2>最近の出来事</h2>\n";
    for($i = 0; $i < $init->logTopTurn; $i++) {
      LogIO::logFilePrint($i, 0, 0);
    }
    print "</div>\n";
  }
  //---------------------------------------------------
  // 統計データ
  //---------------------------------------------------
  function World_Stat() {
	$statdata = Util::Makewstat();
	echo "<table id=\"wstattable\">\n<caption>世界統計</caption>";
	$theads = "<thead><tr>
	<td></td><th>人口</th><th>農業人口</th><th>工業人口</th><th>商業人口</th><th>工業生産</th><th>商業生産</th><th>砲弾</th></tr>
	</thead>";
	foreach($statdata as $fkey => $value){
		$tbodys .= "<tr><th>".$fkey."年</th>";
		foreach($value as $key => $secval){
			$tbodys .= "<td>".$secval."</td>";
		}
  		$tbodys .= "</tr>\n";
	}
	echo $theads."<tbody>\n".$tbodys."</tbody></table>";
  }

  //---------------------------------------------------
  // 発見の記録
  //---------------------------------------------------
  function historyPrint() {
    print "<div id=\"HistoryLog\">\n";
    print "<h2>発見の記録</h2>";
    LogIO::historyPrint();
    print "</div>\n";
  }
}
//------------------------------------------------------------------
class HtmlMap extends HTML {
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function owner($hako, $data) {
    global $init;
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    $island = $hako->islands[$number];

    // パスワードチェック
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])){
      Error::wrongPassword();
      return;
    }
    $this->tempOwer($hako, $data, $number);

    //ＩＰ情報取得
    $logfile = $init->logname;
    $ax = $init->axesmax - 1;
    $log = file($logfile);
    $fp = fopen($logfile,"w");
    $timedata =date ("Y年m月d日(D) H時i分s秒");
    $islandID = "<center>{$data['ISLANDID']}</center>";
    $name = "{$island['name']}国";
    $ip = getenv("REMOTE_ADDR");
	$proxy = "false";
	if(getenv("HTTP_X_FORWARDED_FOR") == true){
		$proxy = "true";
	}
    $host = gethostbyaddr(getenv("REMOTE_ADDR"));
    fputs($fp,$timedata.",".$islandID.",".$name.",".$ip.",".$host.",".$proxy."\n");
    for($i=0; $i<$ax; $i++) fputs($fp,$log[$i]);
    fclose($fp);

      print "<div id=\"TradeBox\" style=\"float:left; width:50%\">\n";
	  $this->regTHead($island);
      $this->regTInputOW($hako,$island, $data);
      $this->regTContents($hako, $island);
      print "</div>\n</div>\n";

    if($init->useBbs) {
      print "<hr style=\"clear:both;\">\n<div id=\"localBBS\">\n";
      $this->lbbsHead($island);
      $this->lbbsInputOW($island, $data);
      $this->lbbsContents($hako, $island, 1);
      print "</div>\n";
    }
    $this->islandRecent($island, 1);
  }

  //---------------------------------------------------
  // 観光画面
  //---------------------------------------------------
  function visitor($hako, $data, $speakerID = 0) {
    global $init;
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    $island = $hako->islands[$number];
    $name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

    print <<<END
<div align="center">
{$init->tagBig_}{$init->tagName_}「{$name}」{$init->spanend}へようこそ！！{$init->spanend}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>

END;

    $this->islandInfo($island, $number, 0);
    $this->islandMap($hako, $island, 0);
    // 他の国へ
    print <<<END
<div align="center"><form action="{$GLOBALS['THIS_FILE']}" method="get">
<select name="Sight">$hako->islandList</select><input type="submit" value="観光">
</form></div>
END;

    if($init->useBbs) {
      print "<div id=\"localBBS\">\n";
      $this->lbbsHead($island);
      $this->lbbsInput($hako, $island, $data);
      $this->lbbsContents($hako, $island, 0, $speakerID);
      print "</div>\n";
    }
    $this->islandRecent($island, 0);
  }
  //---------------------------------------------------
  // 国の情報
  //---------------------------------------------------
  function islandInfo($island, $number = 0, $mode = 0) {
    global $init;
	$wns = new WNSsys();
    $rank = $number + 1;
	$point = $island['point'];
    $pop   = Util::Rewriter2('',$island['pop']) . $init->unitPop;
	$spop  = Util::Rewriter2('',$island['spop']) . $init->unitPop;
    $area  = $island['area'] . $init->unitArea;
    $eisei = $island['eisei'];
	$senkan = $island['ship']['senkan'];
    $farm  = ($island['farm'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['farm'] * 10) . $init->unitPop;
    $market  = ($island['market'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['market'] * 10) . $init->unitPop;
	$service =  ($island['service'] <= 0) ? "保有せず" : Util::Rewriter2('',$island['service'] * 10) . $init->unitPop;
	$armypop =  ($island['milpop'] +$island['sfarmy'])* 10;
	$navypop =  ($island['navy']+$senkan*3)*3;
    $hatuden  = ($island['hatuden'] <= 0) ? "0kw" : $island['hatuden'] * 1000 . kw;
    $factory  = ($island['factory'] <= 0) ? "保有せず" :Util::Rewriter2('',$island['factory'] * 10) . $init->unitPop;
    $mfactory  = ($island['mfactory'] <= 0) ? "保有せず" : $island['mfactory'] . $init->unitMaterial;
    $sfactory  = ($island['sfactory'] <= 0) ? "保有せず" : $island['sfactory'] . $init->unitShell;
    $ffactory  = ($island['ffactory'] <= 0) ? "保有せず" : $island['ffactory'] . $init->unitCow;
    $mining = ($island['mining'] <= 0) ? "保有せず" : $island['mining'] * 10 . $init->unitPop;
    $orgmining  = $island['mining'];
    $taiji  = ($island['taiji'] <= 0) ? "0匹" : $island['taiji'] * 1 . $init->unitMonster;
    $tenki    = $island['tenki'];
	$freeze   = $island['freeze'];
    $comment  = $island['comment'];
	$Cname    = $island['Cname'];
	$invest	= $island['invest'];
	$edinv	= $island['edinv'];
	$hapiness = $island['hapiness'];
	$cmente	  = $island['cmente'];
	$banum	= $island['banum'];
	$indnum	= $island['indnum'];
	$soclv = $island['soclv'];

    if($mining == 0) {
        $mining = "保有せず";
    } else {
      $minelv; // 総鉱山レベル(各種)
      $mining = "";
      if($orgmining % 16 > 0) {
        // 採石場
        $minelv = $orgmining % 16;
        if($minelv == 15){
          $minelv="M";
        }
        $mining .= "石$minelv";
      }
      if(((int)($orgmining / 16) % 16) > 0) {
        // 炭坑
        $minelv = (int)($orgmining / 16) % 16;
        if($minelv == 15){
          $minelv = "M";
        }
        $mining .= "炭$minelv";
      }
      if(((int)($orgmining / 256) % 16) > 0) {
        // 鉄鉱山
        $minelv = (int)($orgmining / 256) % 16;
        if($minelv==15){
          $minelv="M";
        }
        $mining .= "鉄$minelv";
      }
      if(((int)($orgmining / 4096) % 16) > 0) {
        // ウラン鉱山
        $minelv = (int)($orgmining / 4096) % 16;
        if($minelv == 15){
          $minelv = "M";
        }
        $mining .= "ウ$minelv";
      }
      if(((int)($orgmining / 65536) % 16) > 0) {
        // 銀鉱山
        $minelv = (int)($orgmining / 65536) % 16;
        if($minelv == 15){
          $minelv = "M";
        }
        $mining .= "銀$minelv";
      }
    }

    $sora = "";
    if($tenki == 1) {
      $sora .= "<IMG SRC=\"tenki1.gif\" ALT=\"晴れ\">";
    } elseif($tenki == 2) {
      $sora .= "<IMG SRC=\"tenki2.gif\" ALT=\"曇り\">";
    } elseif($tenki == 3) {
      $sora .= "<IMG SRC=\"tenki3.gif\" ALT=\"雨\">";
    } elseif($tenki == 4) {
      $sora .= "<IMG SRC=\"tenki4.gif\" ALT=\"雷\">";
    } else {
      $sora .= "<IMG SRC=\"tenki5.gif\" ALT=\"雪\">";
    }

    $eiseis = "";
    for($e = 0; $e < $init->EiseiNumber; $e++) {
      $eiseip = "";
      if($eisei[$e] > 0) {
        $eiseip .= $eisei[$e];
        $eiseis .= "<img src=\"eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eiseip}%\"> ({$eiseip}%)";
      } else {
        $eiseis .= "";
      }
    }

    $Tokka = "　";
    if ($island['farm'] +  $island['factory'] + $island['market'] != 0) {
      // 特化判定
      // 工業国
		$perind = $island['factory'] / ($island['service'] + $island['farm'] + $island['factory'] + $island['market']);
		$bfactory =(int)(500 * $perind * sqrt(1.5- $perind * $perind));
		$bfactory =(int)($bfactory * ($invest/100 + $init->BaseIndust[0]/100) * ($edinv/100 + $init->BaseIndust[0]/100));

		//農業生産性
		$perind = $island['farm']  / ($island['service'] + $island['farm'] + $island['factory'] + $island['market']);
		$bfarm = (int)(700 * $perind * sqrt(1.5- $perind * $perind));
		$bfarm =(int)($bfarm * ($invest/100 + $init->BaseIndust[1]/100) * ($edinv/100 + $init->BaseIndust[1]/100));

		if($island['polit'] == 0){
			$base = 200;
		}elseif($island['polit'] == 1){
			$base = 300;
		}elseif($island['polit'] == 2){
			$base = 0;
		}
		//商業生産性
		$perind = $island['market'] / ($island['service'] + $island['farm'] + $island['factory'] + $island['market']);
		$bmarket = (int)($base * $perind * sqrt(1.5- $perind * $perind));
		$bmarket =(int)($bmarket * ($invest/100 +$init->BaseIndust[2]/100) * ($edinv/100 + $init->BaseIndust[2]/100));
        if($mode == 1){
          $Tokka = "農{$bfarm}% 工{$bfactory}% 商{$bmarket}% UP";
        } else {
          $Tokka = "";
        }
    }
	  if($hapiness > 81) {
        $happiad .= "<IMG SRC=\"happy1.png\"";
      } elseif($hapiness > 61) {
        $happiad .= "<IMG SRC=\"happy2.png\"";
      } elseif($hapiness > 41) {
        $happiad .= "<IMG SRC=\"happy3.png\"";
      } elseif($hapiness > 21) {
        $happiad .= "<IMG SRC=\"happy4.png\"";
      } else {
        $happiad .= "<IMG SRC=\"happy5.png\"";
      }
       $happiad .= "width=\"20\" height=\"20\" title=\"幸福度： {$hapiness}\" ALT=\"幸福度： {$hapiness}\"> {$hapiness}";

	$invest = ceil($invest);
	$investad = "";
	if ($invest > 0){
		$investad .="<IMG SRC=\"public.png\" width=\"20\" height=\"20\" title=\"インフラ指数： {$invest}\" ALT=\"インフラ指数： {$invest}\"> {$invest}";
    }
	$edinv = ceil($edinv);
	$edcad = "";
	if ($edinv > 0){
		$edcad .="<IMG SRC=\"edc.png\" width=\"20\" height=\"20\" title=\"教育指数：{$edinv}\" ALT=\"教育指数：{$edinv}\"> {$edinv}";
	}
	$socad = "";
	$socad .="<IMG SRC=\"socsec.png\" width=\"20\" height=\"20\" title=\"社会保障指数：{$soclv}\" ALT=\"社会保障指数：{$soclv}\"> {$soclv}";

	$bannerad = "img/up/log";
	if (file_exists($bannerad.'/'.$banum.'.png')){
		$bannerad = 'up/log/'.$banum.'.png';
		$bannerad = "<IMG align = \"center\" SRC=\"{$bannerad}\" width=\"45\" height=\"30\"align=\"left\" title=\"{$pname}旗\" ALT=\"{$pname}旗\">";
	}else{
		$bannerad = "";
	}

    if($mode == 1) {
      $arm = "Lv.{$island['rena']}";
	  $milpop = $armypop+$navypop;
	  $milpop =(($milpop <= 0) ? "保有せず" : Util::Rewriter2('',$milpop)) . $init->unitPop;
    } else {
      $arm = "機密";
	  $milpop = "機密";
    }
	//維持費
	$mentinv = $invest * 10;
	$mentedc  = $edinv * 8;
	$mentsoc = ceil($point/10*(($island['factory']/2 + $island['market'])/$island['pop'])*$soclv/100);
	$mente = $mentinv + $mentedc + $cmente + $mentsoc;
	$mentad ="<IMG SRC=\"coin.gif\" width=\"20\" height=\"20\" title=\"インフラ：{$mentinv}{$init->unitMoney} 　教育：{$mentedc}{$init->unitMoney} 　公務：{$cmente}{$init->unitMoney}　　社会保障：{$mentsoc}{$init->unitMoney}\" ALT=\"インフラ：{$mentinv}{$init->unitMoney}\"> {$mente}{$init->unitMoney}";
	//工業レベル
	$indad = "";
	if ($indnum == ""){
		$indnum = 0;
	}
	$indad = 'ind'.$indnum.'.png';
	$indad = "<IMG SRC=\"{$indad}\" align=\"left\">";
	$latestnews = $wns->MakeHTML(10,$init->baseDir);

      if(!($init->moneyMode) || ($mode == 1)) {
        $mTmp1 = Util::Rewriter2('money',$island['money'])."{$init->unitMoney}</td>";
        $mTmp2 = Util::Rewriter2('food',$island['food'])."{$init->unitFood}</td>";
        $mTmp3 = Util::Rewriter2('money',$island['goods'])."{$init->unitGoods}</td>";
        $mTmp4 = Util::Rewriter2('oil',$island['material'])."{$init->unitMaterial}</td>";
        $mTmp5 = Util::Rewriter2('oil',$island['fuel'])."{$init->unitFuel}</td>";
        $mTmp6 = $island['shell']."{$init->unitShell}</td>";
        $mTmp7 = Util::Rewriter2('oil',$island['wood'])."{$init->unitWood}</td>";
        $mTmp8 = Util::Rewriter2('oil',$island['stone'])."{$init->unitStone}</td>";
        $mTmp9 = Util::Rewriter2('oil',$island['steel'])."{$init->unitSteel}</td>";
        $mTmp10 = Util::Rewriter2('oil',$island['oil'])."{$init->unitOil}</td>";
        $mTmp11 = Util::Rewriter2('silver',$island['alcohol'])."{$init->unitAlcohol}</td>";
        $mTmp12 = Util::Rewriter2('silver',$island['silver'])."{$init->unitSilver}</td>";
      } elseif($init->moneyMode) {
        $mTmp1 = Util::aboutMoney($island['money'],'money');
        $mTmp2 = Util::aboutMoney($island['food'],'food');
        $mTmp3 = Util::aboutMoney($island['goods'],'goods');
        $mTmp4 = Util::aboutMoney($island['material'],'material');
        $mTmp5 = Util::aboutMoney($island['fuel'],'fuel');
        $mTmp6 = Util::aboutMoney($island['shell'],'shell');
        $mTmp7 = Util::Rewriter2('oil',$island['wood'])."{$init->unitWood}</td>";
        $mTmp8 = Util::Rewriter2('oil',$island['stone'])."{$init->unitStone}</td>";
        $mTmp9 = Util::Rewriter2('oil',$island['steel'])."{$init->unitSteel}</td>";
        $mTmp10 = Util::Rewriter2('oil',$island['oil'])."{$init->unitOil}</td>";
        $mTmp11 = Util::Rewriter2('silver',$island['alcohol'])."{$init->unitAlcohol}</td>";
        $mTmp12 = Util::Rewriter2('silver',$island['silver'])."{$init->unitSilver}</td>";
      }
        $mStr1 = "<td {$init->bgInfoCell}>$mTmp1</td>";
        $mStr2 = "<td {$init->bgInfoCell}>$mTmp2</td>";
        $mStr3 = "<td {$init->bgInfoCell}>$mTmp3</td>";
        $mStr4 = "<td {$init->bgInfoCell}>$mTmp4</td>";
        $mStr5 = "<td {$init->bgInfoCell}>$mTmp5</td>";
        $mStr6 = "<td {$init->bgInfoCell}>$mTmp6</td>";
        $mStr7 = "<td {$init->bgInfoCell}>$mTmp7</td>";
        $mStr8 = "<td {$init->bgInfoCell}>$mTmp8</td>";
        $mStr9 = "<td {$init->bgInfoCell}>$mTmp9</td>";
        $mStr10 = "<td {$init->bgInfoCell}>$mTmp10</td>";
        $mStr11 = "<td {$init->bgInfoCell}>$mTmp11</td>";
        $mStr12 = "<td {$init->bgInfoCell}>$mTmp12</td>";

    print <<<END
<div id="islandInfo">
{$latestnews}
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}天気{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}公務員{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}軍人{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}農業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}商業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}工業人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}発電所{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}建材工場{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}畜産場{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}軍事工場{$init->_tagTH}</th>

</tr>
<tr>
<th {$init->bgNumberCelld} rowspan="4">{$init->tagNumber_}$rank{$init->_tagNumber}<br>$bannerad</th>
<td class="TenkiCell">$sora</td>
<td {$init->bgInfoCell}>$pop</td>
<td {$init->bgInfoCell}>$area</td>
<td {$init->bgInfoCell}>$service</td>
<td {$init->bgInfoCell}>$milpop</td>
<td {$init->bgInfoCell}>$farm</td>
<td {$init->bgInfoCell}>$market</td>
<td {$init->bgInfoCell}>$factory</td>
<td {$init->bgInfoCell}>$hatuden</td>
<td {$init->bgInfoCell}>$mfactory</td>
<td {$init->bgInfoCell}>$ffactory</td>
<td {$init->bgInfoCell}>$sfactory</td>

</tr>
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}資金{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}商品{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}建材{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}燃料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}砲弾{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}木材{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}石材{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}鋼鉄{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}石油{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}銀{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食肉{$init->_tagTH}</th>
</tr>
<tr>
$mStr1
$mStr2
$mStr3
$mStr4
$mStr5
$mStr6
$mStr7
$mStr8
$mStr9
$mStr10
$mStr12
$mStr11
</tr>
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}怪獣退治数{$init->_tagTH}</th>
<td {$init->bgInfoCell}>$taiji</td>

<th {$init->bgTitleCell}>{$init->tagTH_}ステータス{$init->_tagTH}</th>
<td class="ItemCell" colspan="3"> $indad $investad $edcad $socad $happiad</td>
<th {$init->bgTitleCell}>{$init->tagTH_}生産性{$init->_tagTH}</th>
<td {$init->bgInfoCell} colspan="3">$Tokka</td>
<th {$init->bgTitleCell}>{$init->tagTH_}維持費{$init->_tagTH}</th>
<td {$init->bgInfoCell}>$mentad</td>

</tr>
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}人工衛星{$init->_tagTH}</th>
<td class="ItemCell" colspan="6">　$eiseis</td>
<th {$init->bgTitleCell}>{$init->tagTH_}軍事技術{$init->_tagTH}</th>
<td {$init->bgInfoCell} colspan="3">{$arm}</td>
<th {$init->bgTitleCell}>{$init->tagTH_}鉱業{$init->_tagTH}</th>
<td {$init->bgInfoCell}>$mining</td>
</tr>
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}コメント{$init->_tagTH}</th>
<td colspan="12" {$init->bgCommentCell}>$comment</td>
</tr>
</table>
</div>






END;
  }
  //---------------------------------------------------
  // 地形出力
  // $mode = 1 -- ミサイル基地なども表示
  //---------------------------------------------------
  function islandMap($hako, $island, $mode = 0) {
    global $init;
    $land      = $island['land'];
    $landValue = $island['landValue'];
    $command   = $island['command'];
	$invest    = $island['invest'];
	$Cname     = $island['Cname'];
	$ctype	   = $island['capital'];
    if($mode == 1) {
      for($i = 0; $i < $init->commandMax; $i++) {
        $j = $i + 1;
        $com = $command[$i];
        if($com['kind'] < 40) {
          $comStr[$com['x']][$com['y']] .=
            "[{$j}]{$init->comName[$com['kind']]} ";
        }
      }
    }

    print "<div id=\"islandMap\" align=\"center\"><table border=\"1\"><tr><td>\n";
    for($y = 0; $y < $init->islandSize; $y++) {
      if($y % 2 == 0) { print "<img class=\"subchip\" src=\"land00.gif\" width=\"16\" height=\"32\" alt=\"{$y}\">"; }

      for($x = 0; $x < $init->islandSize; $x++) {
	  	if($land[$x][$y] == $init->landFBase){
			$target = $hako->idToName[$landValue[$x][$y]];
		}
        $hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr[$x][$y], $invest, $Cname,$ctype,$target);
      }

      if($y % 2 == 1) { print "<img name=\" class=\"subchip\" src=\"land00.gif\" width=\"16\" height=\"32\" alt=\"{$y}\">"; }

      print "<br>";
    }
    print "<div id=\"NaviView\"><div id=\"NaviTitle\"></div><img class=\"NaviImg\" src=\"img\"></div><div class=\"NaviText\"></div>";
    print "</td></tr></table></div>\n";
  }
  //---------------------------------------------------
  // 定期輸送
  //---------------------------------------------------
    function regTHead($island) {
    global $init;
    print <<<END
<h2>{$island['name']}定期輸送入力欄</h2>

END;
  }
  //---------------------------------------------------
  //定期輸送入力部分
  //---------------------------------------------------
   function regTInputOW($hako,$island, $data) {
    global $init;
	for($i = 0; $i < 12; $i++){
		$j = $i +71;
		$kinds .='<option value="'.$j.'">'.$init->comName[$j].'</option>\n';
		}
	$num .="<option value=\"10\">10</option>\n";
	for($i = 20;$i <201; $i +=20){
		$num .="<option value=\"{$i}\">{$i}</option>\n";
	}
	for($i = 300;$i <401; $i +=100){
		$num .="<option value=\"{$i}\">{$i}</option>\n";
	}

    print <<<END
<table id="regtin" border="1">
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<tr>
<th>目標の国</th>
<th>種類</th>
<th>数量</th>
<th>総計</th>
<th>動作</th>
</tr>
<tr>
<td>
<select name="TARGETID">
$hako->targetList
</select>
</TD>
<td>
<select id="sel_kind" name="command">
$kinds
</select>
</td>
<td>
<select id="sel_val" name="num">
$num
</select>
</td>
<td id="gross">
</td>
<td>
<input type="hidden" name="mode" value="regT">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記録">
</td>
</tr>
END;
  }
  //---------------------------------------------------
  // 定期輸送 書き込んた内容を出力
  //---------------------------------------------------
  function regTContents($hako, $island) {
    global $init;
    $regT = $island['regT'];
	if($island['bport'] == 0){
		$max = $init->regTsMax;
	}elseif($island['bport'] == 1){
		$max = $init->regTMax;
	}
    for($i = 0; $i < $init->regTMax; $i++) {
		if(!empty($regT[$i])){
			$nu++;
		}
	}
	print "輸送本数：{$nu}/{$max}本";
    print <<<END
<table border="1">
<tr>
<th style="width:3em;">番号</th>
<th>取引内容</th>
<th>開始ターン</th>
<th>中止</th>
</tr>

END;
    for($i = 0; $i < $init->regTMax; $i++) {
		if(!empty($regT[$i])){
			list($target,$kind,$value,$turn) = split(",", $regT[$i]);
			$j = $i + 1;
			$tnum = $hako->idToNumber[$target];
			$tisland = $hako->islands[$tnum];
		  $nacanl = "";
		  $value = $value * $init->comCost[$kind];
		  $value = abs($value);
		  $unit  = $init->allunit[$kind - 71];
		  $kind = $init->comName[$kind];
		  $target = $tisland['name'];
		  if($hako->islandTurn - $turn < $init->regTTerm){
		  	$nacanl = "※不可";
		  }
	      print "<tr><th>{$init->tagNumber_}{$j}{$init->_tagNumber}</th>";
		  print "<td>{$target}へ{$value}{$unit}を{$kind}する</td><td>{$turn}</td><td><input type=\"radio\" name=\"NUMBER\" value=\"{$i}\">{$nacanl}</td></tr>\n";
	    }
	}
    print "<td></td><td></td><td></td><td><input type=\"submit\" name=\"DEL\" value=\"削除\"></td></form></table>\n";
  }
  //---------------------------------------------------
  // 観光者通信
  //---------------------------------------------------
  function lbbsHead($island) {
    global $init;
    print <<<END
<h2>{$island['name']}観光者通信</h2>

END;
  }
  //---------------------------------------------------
  // 観光者通信 入力部分
  //---------------------------------------------------
  function lbbsInput($hako, $island, $data) {
    global $init;

    $lbbsAtention = '';
    if($init->lbbsMoneyPublic + $init->lbbsMoneySecret > 0) {
      // 発言は有料
      $lbbsAtention .= "<CENTER><B>※</B>";
      if($init->lbbsMoneyPublic > 0){
        $lbbsAtention .= "公開通信は<B>{$init->lbbsMoneyPublic}{$init->unitMoney}</B>です。";
      }
      if($init->lbbsMoneySecret > 0){
        $lbbsAtention .= "極秘通信は<B>{$init->lbbsMoneySecret}{$init->unitMoney}</B>です。";
      }
      $lbbsAtention .= "</CENTER>";
    }
    $lbbsAnonny = '';
    if ($init->lbbsAnon){
      $lbbsAnonny .= "<input type=\"radio\" name=\"LBBSTYPE\" value=\"ANON\">観光客";
    } else {
      $col = " colspan=2";
    }

    print <<<END
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" accept-charset="UTF-8">
{$lbbsAtention}<B>※</B>国を持っている方は国名がコメントのあとにつきます。
<table border="1">
<TR>
<th>名前</th>
<th{$col}>内容</th>
<th>通信方法</th>
</tr>
<tr>
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" value="{$data['defaultName']}"></td>
<td{$col}><input type="text" size="80" name="LBBSMESSAGE"></td>
<td>
<input type="radio" name="LBBSTYPE" value="PUBLIC" checked>公開
<input type="radio" name="LBBSTYPE" value="SECRET"><font color="red">極秘</font>
</td>
</tr>
<tr>
<td colspan="4">
END;

    for ($i=0; $i < sizeof($init->lbbsColor); $i++) {
      if($init->lbbsColor[$i] == $data['defaultColor']) {
        print "<input type=radio name=\"LBBSCOLOR\" value=\"{$init->lbbsColor[$i]}\" checked>";
        print "<font color=\"{$init->lbbsColor[$i]}\">■</font>\n";
      } else {
        print "<input type=radio name=\"LBBSCOLOR\" value=\"{$init->lbbsColor[$i]}\">";
        print "<font color=\"{$init->lbbsColor[$i]}\">■</font>\n";
      }
    }

    print <<<END

</td>
</tr>
<tr>
<th>パスワード</th>
<th>国名</th>
<th{$col}>動作</th>
</tr>
<tr>
<td><input type=password size="16" maxlength="16" name=PASSWORD value="{$data['PASSWORD']}"></td>
<td>
<select name="ISLANDID2">{$hako->islandList}</select>
{$lbbsAnonny}
</td>
<td>
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="0">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する">
<input type="submit" name="CHK" value="極秘確認"></TD>
END;
    if ($init->lbbsAnon == 0){
      print <<<END
<td align="right">
番号
<select name="NUMBER">
END;
      // 発言番号
      for($i = 0; $i < $init->lbbsMax; $i++) {
        $j = $i + 1;
        print "<option value=\"{$i}\">{$j}</option>\n";
      }
      print <<<END
</select>
<input type="submit" name="DEL" value="削除する">
</td>
END;
    }
    print <<<END
</tr>
</table>
</form>
</div>

END;
  }
  //---------------------------------------------------
  // 観光者通信 入力部分 オーナ用
  //---------------------------------------------------
  function lbbsInputOW($island, $data) {
    global $init;
    print <<<END
<div align="center">
<table border="1">
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<tr>
<th>名前</th>
<th colspan="2">内容</th>
</tr>
<tr>
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" VALUE="{$data['defaultName']}"></TD>
<td colspan="2"><input type="text" size="80" name="LBBSMESSAGE"></td>
</tr>
<tr>
<td colspan="2">
END;

    for ($i=0; $i < sizeof($init->lbbsColor); $i++) {
      if($init->lbbsColor[$i] == $data['defaultColor']) {
        print "<input type=radio name=\"LBBSCOLOR\" value=\"{$init->lbbsColor[$i]}\" checked>";
        print "<font color=\"{$init->lbbsColor[$i]}\">■</font>\n";
      } else {
        print "<input type=radio name=\"LBBSCOLOR\" value=\"{$init->lbbsColor[$i]}\">";
        print "<font color=\"{$init->lbbsColor[$i]}\">■</font>\n";
      }
    }

    print <<<END

</td>
</tr>
<tr>
<th colspan="2">動作</th>
</tr>
<tr>
<td align="right">
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="1">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する">
</td>
<td align="right">
番号
<select name="NUMBER">

END;

    // 発言番号
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $j = $i + 1;
      print "<option value=\"{$i}\">{$j}</option>\n";
    }
    print <<<END
</select>
<input type="submit" name="DEL" value="削除する">
</form>
</td>
</tr>
</table>
</div>

END;
  }
  //---------------------------------------------------
  // 観光者通信 書き込まれた内容を出力
  //---------------------------------------------------
  function lbbsContents($hako, $island, $owner = 0, $speakerID = 0) {
    global $init;
    $lbbs = $island['lbbs'];
    print <<<END
<div align="center">
<table border="1">
<tr>
<th style="width:3em;">番号</th>
<th>記帳内容</th>
</tr>

END;
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $j = $i + 1;
      $line = $lbbs[$i];
      list($secret, $sTemp, $mode, $turn, $message, $color) = explode(">", $line);
      list($sName, $sId) = explode(",", $sTemp);
      $sNo = $hako->idToNumber[$sId];
      print "<tr><th>{$init->tagNumber_}{$j}{$init->_tagNumber}</th>";
      $speaker = '';
      if($init->lbbsSpeaker && ($sName != '')) {
        if($sNo == '0' || !empty($sNo)) {
          $speaker = " <font color=gray><b><small>(<a style=\"text-decoration:none\" href=\"{$GLOBALS['THIS_FILE']}?Sight={$sId}\">{$sName}</a>)</small></b></font>";
        } else {
          $speaker = " <font color=gray><b><small>({$sName})</small></b></font>";
        }
      }
      if($mode == 0) {
        // 観光者
        if($secret == 0) {
          // 公開
          print "<td><font color=\"$color\">{$init->tagLbbsSS_}{$turn} &gt; {$message}{$init->_tagLbbsSS} {$speaker}</font></td></tr>\n";
        } else {
          // 極秘
          if($owner == 0) {
            // 観光客
            print "<td><center><font color=gray>- 極秘 -</font></center></td></tr>\n";
          } else {
            // オーナー
            print "<td><font color=\"$color\">{$init->tagLbbsSS_}{$turn} &gt;(秘) {$message}{$init->_tagLbbsSS} {$speaker}</font></td></tr>\n";
          }
        }
      } else {
        // 国主
        print "<td><font color=\"$color\">{$init->tagLbbsOW_}{$turn} &gt; {$message}{$init->_tagLbbsOW}</font></td></tr>\n";
      }

    }
    print "</table></div>\n";
  }
  //---------------------------------------------------
  // 国の近況
  //---------------------------------------------------
  function islandRecent($island, $mode = 0) {
    global $init;
    print "<hr>\n";
    print "<div id=\"RecentlyLog\">\n";
    print "<h2>{$island['name']}{$init->spanend}の近況</h2>\n";
    for($i = 0; $i < $init->logMax; $i++) {
      LogIO::logFilePrint($i, $island['id'], $mode);
    }
    print "</div>\n";
  }
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function tempOwer($hako, $data, $number = 0) {
    global $init;
    $island = $hako->islands[$number];
    $name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

    $width  = $init->islandSize * 32 + 50;
    $height = $init->islandSize * 32 + 100;
    $defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
    print <<<END
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;
function ps(x, y) {
  document.InputPlan.POINTX.options[x].selected = true;
  document.InputPlan.POINTY.options[y].selected = true;
  return true;
}

function ns(x) {
  document.InputPlan.NUMBER.options[x].selected = true;
  return true;
}

function settarget(part){
  p = part.options[part.selectedIndex].value;
}
function targetopen() {
  w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}


//-->
</script>
<div align="center">
{$init->tagBig_}{$init->tagName_}{$name}{$init->spanend}開発計画{$init->spanend}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>

END;
    $this->islandInfo($island, $number, 1);
    print <<<END
<div align="center">
<table border="1">
<tr>
<td {$init->bgInputCell}>
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="submit" value="計画送信">
<hr>
<strong>計画番号</strong>
<select name="NUMBER">

END;
    // 計画番号
    for($i = 0; $i < $init->commandMax; $i++) {
      $j = $i + 1;
      print "<option value=\"{$i}\">{$j}</option>";
    }
    print <<<END
</select><br>
<hr>
<strong>開発計画</strong><br>
<select name="COMMAND">

END;
    // コマンド
    for($i = 0; $i < $init->commandTotal; $i++) {
      $kind = $init->comList[$i];
      $cost = $init->comCost[$kind];
      if($cost == 0) {
        $cost = '無料';
      } elseif($cost < 0) {

        if($kind == $init->comFood) {
        // 食料輸送
            $cost = - $cost;
            $cost .= $init->unitFood;
        }
        if($kind == $init->comShell) {
        // 砲弾輸送
            $cost = - $cost;
            $cost .= $init->unitShell;
        }
        if($kind == $init->comSteel) {
        // 鉄鋼輸送
            $cost = - $cost;
            $cost .= $init->unitSteel;
        }
        if($kind == $init->comMaterial) {
        // 建材輸送
            $cost = - $cost;
            $cost .= $init->unitMaterial;
        }
        if($kind == $init->comSilver) {
        // 銀輸送
            $cost = - $cost;
            $cost .= $init->unitSilver;
        }
        if($kind == $init->comWood) {
        // 木材輸送
            $cost = - $cost;
            $cost .= $init->unitWood;
        }
        if($kind == $init->comStone) {
        // 石材輸送
            $cost = - $cost;
            $cost .= $init->unitStone;
        }
        if($kind == $init->comAlcohol) {
        // 食肉輸送
            $cost = - $cost;
            $cost .= $init->unitAlcohol;
        }
        if($kind == $init->comOil) {
        // 石油輸送
            $cost = - $cost;
            $cost .= $init->unitOil;
        }
        if($kind == $init->comGoods) {
        // 商品輸送
            $cost = - $cost;
            $cost .= $init->unitGoods;
        }
        if(($kind == $init->comMissileNM)  ||
           ($kind == $init->comMissilePP)  ||
           ($kind == $init->comMissileSPP)  ||
           ($kind == $init->comMissileBT)  ||
           ($kind == $init->comMissileSP)  ||
           ($kind == $init->comMissileLD)  ||
           ($kind == $init->comMkResource) ||
           ($kind == $init->comMkMaterial) ||
           ($kind == $init->comMkSteel)    ||
           ($kind == $init->comControl)    ||
           ($kind == $init->comMkShell)    ||
           ($kind == $init->comFuel)) {
        // ミサイル発射、資源採掘、砲弾製造、燃料輸送
            $cost = - $cost;
            $cost .= $init->unitFuel;
        }
        if (($kind == $init->comMissileNM) ||
           ($kind == $init->comMissilePP)  ||
           ($kind == $init->comMissileSPP)  ||
           ($kind == $init->comMissileBT)  ||
           ($kind == $init->comMissileSP)  ||
           ($kind == $init->comMissileLD)  ||
		   ($kind == $init->comTrain)) {
        // ミサイル発射関係
            $cost .= "+砲弾";
            $cost .= -$init->comSCost[$kind];
            $cost .= $init->unitShell;
        }
		}elseif (($kind == $init->comPubinvest)||
				 ($kind == $init->comEduinvest)){
			$cost = "ポイントによる";
      	} else {
        $cost .= $init->unitMoney;
      }

      if(($kind == $init->comFarm) ||
         ($kind == $init->comNursery) ||
         ($kind == $init->comFactory) ||
         ($kind == $init->comMarket) ||
         ($kind == $init->comHatuden) ||
         ($kind == $init->comOild) ||
         ($kind == $init->comPower) ||
         ($kind == $init->comFusya) ||
         ($kind == $init->comNewtown) ||
         ($kind == $init->comBigtown) ||
         ($kind == $init->comSeaCity) ||
         ($kind == $init->comPark) ||
         ($kind == $init->comPort) ||
		 ($kind == $init->comSeeCity) ||
		 ($kind == $init->comMyhome) ||
		 ($kind == $init->comPubinvest)){
        // 建材表示
        $cost .= "+建材";
        $cost .= $init->comSCost[$kind];
        $cost .= $init->unitMaterial;
      }
      if(($kind == $init->comReclaim) ||
         ($kind == $init->comReclaim2) ||
         ($kind == $init->comMonument)){
        // 石材表示
        $cost .= "+石材";
        $cost .= -$init->comSCost[$kind];
        $cost .= $init->unitStone;
      }
      if(($kind == $init->comProcity) ||
         ($kind == $init->comSdbase) ||
	     ($kind == $init->comMakeShip)){
        // 防災都市化
        $cost .= "+鋼鉄";
        $cost .= $init->comSCost[$kind];
        $cost .= $init->unitSteel;
      }
      if(($kind == $init->comEisei)      ||
         ($kind == $init->comEiseimente) ||
         ($kind == $init->comEiseiAtt)   ||
         ($kind == $init->comEiseiLzr)) {
        // 人口衛星
        $cost .= "+燃料";
        $cost .= -$init->comSCost[$kind];
        $cost .= $init->unitFuel;
      }
      if($kind == $init->comSendMonster) {
        // 怪獣派遣
        $cost = "+資金・鋼鉄・燃料：";
        $cost .= $init->comCost[$kind];
        $cost .= "0ずつ";
      }
      if($kind == $init->comSendSleeper) {
        // 誘致活動
        $cost .= "+人口に比例";
      }

      if(($kind == $init->comMine) ||
         ($kind == $init->comSFactory) ||
         ($kind == $init->comBase) ||
         ($kind == $init->comDbase)){
        // 鉱山整備など
        $cost = '種別による';
      }

      if($kind == $data['defaultKind']) {
        $s = 'selected';
      } else {
        $s = '';
      }
      print "<option value=\"{$kind}\" {$s}>{$init->comName[$kind]}({$cost})</option>\n";
    }
    print <<<END
</select>
<hr>
<strong>座標(</strong>
<select name="POINTX">

END;
    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultX']) {
        print "<option value=\"{$i}\" selected>{$i}</option>\n";
      } else {
        print "<option value=\"{$i}\">{$i}</option>\n";
      }
    }
    print "</select>, <select name=\"POINTY\">";
    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultY']) {
        print "<option value=\"{$i}\" selected>{$i}</option>\n";
      } else {
        print "<option value=\"{$i}\">{$i}</option>\n";
      }
    }
    print <<<END
</select><strong>)</strong>
<hr>
<strong>数量</strong>
<select name="AMOUNT">

END;
     for($i = 0; $i < 100; $i++)
       print "<option value=\"{$i}\">{$i}</option>\n";

     print <<<END
</select>
<hr>
<strong>目標の国</strong><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>
<strong>動作</strong><br>
<input type="radio" name="COMMANDMODE" id="insert" value="insert" checked><label for="insert">挿入</label>
<input type="radio" name="COMMANDMODE" id="write" value="write"><label for="write">上書き</label><BR>
<input type="radio" name="COMMANDMODE" id="delete" value="delete"><label for="delete">削除</label>
<hr>
<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" value="計画送信">
</form>
<center>ミサイル発射上限数[<b> {$island['fire']} </b>]発</center>
</div>
</td>
<td {$init->bgMapCell}>

END;
    $this->islandMap($hako, $island, 1);    // 国の地図、所有者モード
    print <<<END
</td>
<td {$init->bgCommandCell}>
END;
    $command = $island['command'];
    for($i = 0; $i < $init->commandMax; $i++) {
      $this->tempCommand($i, $command[$i], $hako);
    }
    print <<<END
</td>
</tr>
</table>
</div>
<hr>
END;
$param['GLTH'] = $GLOBALS['THIS_FILE'];
$param['comment'] =str_replace( "<br />","\n",$island['comment']);
$param['PAS'] =   $data['PASSWORD'];
$param['id']  = $island['id'];
$param['CNAM'] =  $island['Cname'];
$param['num'] =  $island['banum'];
$param['mode'] = "cgi";

print HTML::tplengine('./templates/banners.html',$param);
  }
  //---------------------------------------------------
  // 入力済みコマンド表示
  //---------------------------------------------------
  function tempCommand($number, $command, $hako) {
    global $init;

    $kind   = $command['kind'];
    $target = $command['target'];
    $x      = $command['x'];
    $y      = $command['y'];
    $arg    = $command['arg'];

    $comName = "{$init->tagComName_}{$init->comName[$kind]}{$init->_tagComName}";
    $point   = "{$init->tagName_}({$x},{$y}){$init->spanend}";
    $target  = $hako->idToName[$target];
    if(empty($target)) {
      $target = "無人";
    }
    $target = "{$init->tagName_}{$target}{$init->spanend}";
    $value = $arg * $init->comCost[$kind];
    if($value == 0) {
      $value = $init->comCost[$kind];
    }
    if($value < 0) {

        if($kind == $init->comFood) {
        // 食料輸送
            $value = -$value;
            $value = "{$value}{$init->unitFood}";
        }
        if($kind == $init->comShell) {
        // 砲弾輸送
            $value = -$value;
            $value = "{$value}{$init->unitShell}";
        }
        if($kind == $init->comSteel) {
        // 鉄鋼輸送
            $value = -$value;
            $value = "{$value}{$init->unitSteel}";
        }
        if($kind == $init->comMaterial) {
        // 鉄鋼輸送
            $value = -$value;
            $value = "{$value}{$init->unitMaterial}";
        }
        if($kind == $init->comOil) {
        // 石油輸送
            $value = -$value;
            $value = "{$value}{$init->unitOil}";
        }
        if($kind == $init->comSilver) {
        // 銀輸送
            $value = -$value;
            $value = "{$value}{$init->unitSilver}";
        }
        if($kind == $init->comWood) {
        // 木材輸送
            $value = -$value;
            $value = "{$value}{$init->unitWood}";
        }
        if($kind == $init->comAlcohol) {
        // 食肉輸送
            $value = -$value;
            $value = "{$value}{$init->unitAlcohol}";
        }

        if($kind == $init->comGoods) {
        // 商品輸送
            $value = -$value;
            $value = "{$value}{$init->unitGoods}";
        }
        if($kind == $init->comStone) {
        // 石材輸送
            $value = -$value;
            $value = "{$value}{$init->unitStone}";
        }
        if(($kind == $init->comFuel) ||
           ($kind == $init->comMkMaterial) ||
           ($kind == $init->comMkSteel) ||
           ($kind == $init->comMkShell)) {
        // 燃料輸送、砲弾製造など
            $value = -$value;
            $value = "$value{$init->unitFuel}";
        }

    } else {
      $value = "{$value}{$init->unitMoney}";
    }
    $value = "{$init->tagName_}{$value}{$init->spanend}";

    $j = sprintf("%02d：", $number + 1);

    print "<a href=\"javascript:void(0);\" onclick=\"ns({$number})\">{$init->tagNumber_}{$j}{$init->_tagNumber}";

    switch($kind) {
    case $init->comDoNothing:
    case $init->comGiveup:
    case $init->comPropaganda:
	case $init->comPubinvest:
	case $init->comEduinvest:
      $str = "{$comName}";
      break;
    case $init->comMissileNM:
    case $init->comMissilePP:
    case $init->comMissileSPP:
    case $init->comMissileBT:
    case $init->comMissileSP:
    case $init->comMissileLD:
      // ミサイル系
      $n = ($arg == 0) ? '無制限' : "{$arg}発";
      $str = "{$target}{$point}へ{$comName}({$init->tagName_}{$n}{$init->spanend})";
      break;
    case $init->comEisei:
      // 人工衛星発射
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str = "{$init->tagComName_}{$init->EiseiName[$arg]}打ち上げ{$init->_tagComName}";
      break;
    case $init->comEiseimente:
      // 人工衛星修復
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str = "{$init->tagComName_}{$init->EiseiName[$arg]}修復{$init->_tagComName}";
      break;
    case $init->comEiseiAtt:
      // 人工衛星破壊砲
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str =  "{$target}へ{$init->tagComName_}{$init->EiseiName[$arg]}破壊砲発射{$init->_tagComName}";
      break;
    case $init->comEiseiLzr:
      // 衛星レーザー
      $str = "{$target}{$point}へ{$comName}";
      break;
    case $init->comSendMonster:
      // 怪獣派遣
      $str = "{$target}へ{$comName}";
      break;
    case $init->comMkShell:
    case $init->comMkMaterial:
    case $init->comMkSteel:
      $n = ($arg == 0 ? "{$init->tagName_}フル稼働{$init->spanend}" : $value);
      $str ="{$comName}($n)";
      break;
    case $init->comMoney:
    case $init->comSilver:
    case $init->comSteel:
    case $init->comMaterial:
    case $init->comStone:
    case $init->comShell:
    case $init->comOil:
    case $init->comWood:
    case $init->comExplosive:
    case $init->comAlcohol:
    case $init->comFuel:
    case $init->comGoods:
    case $init->comFood:
      // 輸送系
      $str = "{$target}へ{$comName}{$value}";
      break;
    case $init->comDestroy:
    case $init->comDestroy2:
      // 掘削
      $str = "{$point}で{$comName}";
      break;
    case $init->comBase:
      if($arg == 2) {
        $name = "ハリボテ(ミサイル基地)建設";
      } else if($arg == 1) {
        $name = "偽装ミサイル基地建設";
      } else {
        $name = "ミサイル基地建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comDbase:
      if($arg == 2) {
        $name = "ハリボテ(防衛施設)建設";
      } else if($arg == 1) {
        $name = "偽装防衛施設建設";
      } else {
        $name = "防衛施設建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comSFactory:
      if($arg == 2) {
        $name = "畜産場建設";
      } else if($arg == 1) {
        $name = "軍事工場建設";
      } else {
        $name = "建材工場建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comSdbase:
      // 海底防衛施設
        $str = "{$point}で{$comName}(耐久力5)";
      break;
    case $init->comFarm:
    case $init->comNursery:
    case $init->comMine:
    case $init->comMarket:
    case $init->comFactory:
    case $init->comHatuden:
    case $init->comBoku:
      // 回数付き
      if($arg == 0) {
        $str = "{$point}で{$comName}";
      } else {
        $str = "{$point}で{$comName}({$arg}回)";
      }
      break;
	case $init->comCapital:
	case $init->comIndPlan:
      // 回数付き
      if($arg == 0) {
        $str = "{$point}で{$comName}";
      } else {
        $str = "{$point}で{$comName}(レベル：{$arg})";
      }
      break;
    case $init->comVein:
      $success;
      if($arg > 49) {
        $name = "調査不可";
        $success = 0;
      } else if($arg > 39) {
        $success = min(100, (($arg - 40) * 20));
        if($arg == 40){$success = 100;}
        $name = "炭坑調査";
      } else if($arg > 29) {
        $success = min(100, (($arg - 30) * 10));
        if($arg == 30){$success = 100;}
        $name = "採石場調査";
      } else if($arg > 19) {
        $success = min(100, (($arg - 20) * 5));
        if($arg == 20){$success = 50;}
        $name = "鉄鉱山調査";
      } else if($arg > 9) {
        $success = min(100, (($arg - 10) * 2));
        if($arg == 10){$success = 20;}
        $name = "銀山調査";
      } else {
        $success = min(100, $arg);
        if($arg == 0){$success = 10;}
        $name = "ウラン鉱調査";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}({$init->tagName_}成功率{$success}％{$init->spanend})";
      break;
    default:
      // 座標付き
      $str = "{$point}で{$comName}";
    }

    print "{$str}</a><br>";
  }
  //---------------------------------------------------
  // 新しく発見した国
  //---------------------------------------------------
  function newIslandHead($name) {
    global $init;
    print <<<END
<div align="center">
{$init->tagBig_}国を発見しました！！{$initspanend}<br>
{$init->tagBig_}{$init->tagName_}「{$name}国」{$init->spanend}と命名します。{$init->spanend}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>
END;
  }
  //---------------------------------------------------
  // 目標捕捉モード
  //---------------------------------------------------
  function printTarget($hako, $data) {
    global $init;
    // idから国番号を取得
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    // なぜかその国がない場合
    if($number < 0 || $number > $hako->islandNumber) {
      Error::problem();
      return;
    }
    $island = $hako->islands[$number];

print <<<END
<script type="text/javascript">
<!--
function ps(x, y) {
  window.opener.document.InputPlan.POINTX.options[x].selected = true;
  window.opener.document.InputPlan.POINTY.options[y].selected = true;
  return true;
}
//-->
</script>

<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}国{$init->spanend}{$init->spanend}<br>
</div>

END;

    //国の地図
    $this->islandMap($hako, $island, 2);

  }
}
//------------------------------------------------------------------
class HtmlJS extends HtmlMap {
  function header($data = "") {
    global $init;
    global $PRODUCT_VERSION;

    // 圧縮転送
    if(GZIP == true) {
      global $http;
      $http->start();
    }
    header("X-Product-Version: {$PRODUCT_VERSION}");
    $css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
    $bimg = (empty($data['defaultImg'])) ? $init->imgDir : $data['defaultImg'];

	$param['bimg'] = $bimg;
	$param['css'] = $css;
	$param['title'] =$init->title;
	$param['cssDir'] = $init->cssDir;
	$param['baseDir'] = $init->baseDir;
	$param['urlTopPage'] = $init->urlTopPage;
	$param['t_title'] = $GLOBALS['THIS_FILE'];
	$param['urlManual'] = $init->urlManual;
	$param['urlHowTo'] = $init->urlHowTo;
	$param['urlBbs'] = $init->urlBbs;

	print HTML::tplengine('./templates/head.html',$param);
  }
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function tempOwer($hako, $data, $number = 0) {
    global $init;
    $island = $hako->islands[$number];
    $name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

    $width  = $init->islandSize * 32 + 50;
    $height = $init->islandSize * 32 + 100;

    // コマンドセット
    $set_com = "";
    $com_max = "";
    for($i = 0; $i < $init->commandMax; $i++) {
      // 各要素の取り出し
      $command  = $island['command'][$i];
      $s_kind   = $command['kind'];
      $s_target = $command['target'];
      $s_x      = $command['x'];
      $s_y      = $command['y'];
      $s_arg    = $command['arg'];

      // コマンド登録
      if($i == $init->commandMax - 1){
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target]\n";
        $com_max .= "0";
      } else {
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target],\n";
        $com_max .= "0,";
      }
    }

    //コマンドリストセット
    $l_kind;
    $set_listcom = "";
    $click_com = "";
    $click_com2 = "";
    $All_listCom = 0;
    $com_count = count($init->commandDivido);
    for($m = 0; $m < $com_count; $m++) {
      list($aa,$dd,$ff) = split(",", $init->commandDivido[$m]);
      $set_listcom .= "[ ";
      for($i = 0; $i < $init->commandTotal; $i++) {
        $l_kind = $init->comList[$i];
        $l_cost = $init->comCost[$l_kind];
        $S_cost = -$init->comSCost[$l_kind];
        if($l_cost < 0) {
          $l_cost = - $l_cost;
        }
          if($l_kind >= 87) {
            $l_cost = "無料";
          }elseif($l_kind >= 41 && $l_kind <= 43) { $l_cost .= $init->unitFuel;
          }elseif($l_kind >= 75 && $l_kind <= 78 || $l_kind == 82) { $l_cost .= $init->unitSteel;
          }elseif($l_kind == 9 || $l_kind == 16 || $l_kind == 20 || $l_kind == 21) { $l_cost = "種別による"; // 16⇒専門工場、20⇒ミサイル基地、21⇒防衛施設
		  }elseif($l_kind == 83 || $l_kind == 84) { $l_cost = "ポイントによる"; //　83⇒公共投資,84⇒教育投資
          }elseif($l_kind == 10 || $l_kind == 81) { $l_cost .= $init->unitFuel;
          }elseif($l_kind == 80) { $l_cost .= $init->unitOil;
          }elseif($l_kind == 72) { $l_cost .= $init->unitFood;
          }elseif($l_kind == 73) { $l_cost .= $init->unitGoods;
          }elseif($l_kind == 74) { $l_cost .= $init->unitAlcohol;
          }elseif($l_kind == 79) { $l_cost .= $init->unitSilver;
          }elseif($l_kind == 82) { $l_cost .= $init->unitShell;
          }elseif($l_kind >= 51 && $l_kind <= 56) { $l_cost .= $init->unitFuel;
          }elseif($l_kind == 61){
            $l_cost = '資金:鋼鉄:燃料:';
            $l_cost .= $init->comCost[$l_kind];
            $l_cost .= "0ずつ";
          }else {
            $l_cost .= $init->unitMoney;
          }
          if($S_cost != 0) {
            if($l_kind == 3 || $l_kind == 4 || $l_kind == 23){
			 // 23⇒記念碑
              $l_cost .= '+石材';
              $l_cost .= $S_cost;
              $l_cost .= $init->unitStone;
            }elseif($l_kind == 30 || $l_kind == 22){
			  // 30⇒造船
              $l_cost .= '+鋼鉄';
              $l_cost .= -$S_cost;
              $l_cost .= $init->unitSteel;
            }elseif($l_kind >= 12 && $l_kind <= 21 || $l_kind >= 23 && $l_kind <= 29 || $l_kind >= 32 && $l_kind <= 37||$l_kind == 83){
              $l_cost .= '+建材';
              $l_cost .= -$S_cost;
              $l_cost .= $init->unitStone;
            }elseif($l_kind >= 51 && $l_kind <= 56 || $l_kind == 62) {
              $l_cost .= "+砲弾";
              $l_cost .= $S_cost;
              $l_cost .= $init->unitShell;
            }elseif($l_kind >= 57 && $l_kind <= 60) {
              $l_cost .= "+";
              $l_cost .= $S_cost;
              $l_cost .= $init->unitFuel;
            }elseif($l_kind != 61){
              $l_cost .= '+建材';
              $l_cost .= $S_cost;
              $l_cost .= $init->unitMaterial;
            }
          }
        if($l_kind > $dd-1 && $l_kind < $ff+1) {
          $set_listcom .= "[$l_kind, '{$init->comName[$l_kind]}', '{$l_cost}'],\n";
          if($m == 0){
            $click_com .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})</a><br>\n";
          } elseif($m == 1) {
            $click_com2 .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})</a><br>\n";
          }
          $All_listCom++;
        }
        if($l_kind < $ff+1) { next; }
      }
      $bai = strlen($set_listcom);
      $set_listcom = substr($set_listcom, 0, $bai - 2);
      $set_listcom .= " ],\n";
    }
    $bai = strlen($set_listcom);
    $set_listcom = substr($set_listcom, 0, $bai - 2);
    if(empty($data['defaultKind'])) {
      $default_Kind = 1;
    } else {
      $default_Kind = 1;
    }

    // 船リストセット
    //$set_ships = implode("," , $init->shipName);
    for($i = 0; $i < count($init->shipName); $i++) {
        $set_ships .= "'".$init->shipName[$i]."',";
    }

    // 衛星リストセット
    //$set_eisei = implode("," , $init->EiseiName);
    for($i = 0; $i < count($init->EiseiName); $i++) {
        $set_eisei .= "'".$init->EiseiName[$i]."',";
    }

    // 国リストセット
    $set_island = "";
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $l_name = $hako->islands[$i]['name'];
      $l_name = preg_replace("/'/", "\'", $l_name);
      $l_id = $hako->islands[$i]['id'];
      if($i == $hako->islandNumber - 1){
        $set_island .= "[$l_id, '$l_name']\n";
      }else{
        $set_island .= "[$l_id, '$l_name'],\n";
      }
    }
    $defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;

    print <<<END
<center>
{$init->tagBig_}{$init->tagName_}{$name}{$init->spanend}開発計画{$init->spanend}<BR>
{$GLOBALS['BACK_TO_TOP']}<br>
</center>
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;

// ＪＡＶＡスクリプト開発画面配布元
// あっぽー庵箱庭諸国（ http://appoh.execweb.cx/hakoniwa/ ）
// Programmed by Jynichi Sakai(あっぽー)
// ↑ 削除しないで下さい。
var str;
g = [$com_max];
k1 = [$com_max];
k2 = [$com_max];
tmpcom1 = [ [0,0,0,0,0] ];
tmpcom2 = [ [0,0,0,0,0] ];
command = [
$set_com];

comlist = [
$set_listcom];

islname = [
$set_island];

shiplist = [$set_ships];
eiseilist = [$set_eisei];

function init() {
  for(i = 0; i < command.length ;i++) {
    for(s = 0; s < $com_count ;s++) {
      var comlist2 = comlist[s];
      for(j = 0; j < comlist2.length ; j++) {
        if(command[i][0] == comlist2[j][0]) {
          g[i] = comlist2[j][1];
        }
      }
    }
  }
  SelectList('');
  outp();
  str = plchg();
  str = '<font color="blue">■ 送信済み ■<\\/font><br>' + str;
  disp(str, "");
	document.onmousemove = Mmove;

  if(document.layers) {
    document.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
  }
  document.onmouseup   = Mup;
  document.onmousemove = Mmove;
  document.onkeydown = Kdown;
  document.ch_numForm.AMOUNT.options.length = 100;
  for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
    document.ch_numForm.AMOUNT.options[i].value = i;
    document.ch_numForm.AMOUNT.options[i].text  = i;
  }
  document.InputPlan.SENDPROJECT.disabled = true;
  ns(0);
}

function cominput(theForm, x, k, z) {
  a = theForm.NUMBER.options[theForm.NUMBER.selectedIndex].value;
  b = theForm.COMMAND.options[theForm.COMMAND.selectedIndex].value;
  c = theForm.POINTX.options[theForm.POINTX.selectedIndex].value;
  d = theForm.POINTY.options[theForm.POINTY.selectedIndex].value;
  e = theForm.AMOUNT.options[theForm.AMOUNT.selectedIndex].value;
  f = theForm.TARGETID.options[theForm.TARGETID.selectedIndex].value;
  if(x == 6){ b = k; menuclose(); }
  var newNs = a;
  if (x == 1 || x == 2 || x == 6){
    if(x == 6) b = k;
    if(x != 2) {
      for(i = $init->commandMax - 1; i > a; i--) {
        command[i] = command[i-1];
        g[i] = g[i-1];
      }
    }

    for(s = 0; s < $com_count ;s++) {
      var comlist2 = comlist[s];
      for(i = 0; i < comlist2.length; i++){
        if(comlist2[i][0] == b){
          g[a] = comlist2[i][1];
          break;
        }
      }
    }
    command[a] = [b,c,d,e,f];
    newNs++;
//    menuclose();
  } else if(x == 3) {
    var num = (k) ? k-1 : a;
    for(i = Math.floor(num); i < ($init->commandMax - 1); i++) {
      command[i] = command[i + 1];
      g[i] = g[i+1];
    }
    command[$init->commandMax - 1] = [88, 0, 0, 0, 0];
    g[$init->commandMax - 1] = '放置';
  } else if(x == 4) {
    i = Math.floor(a);
    if (i == 0){ return true; }
    i = Math.floor(a);
    tmpcom1[i] = command[i];tmpcom2[i] = command[i - 1];
    command[i] = tmpcom2[i];command[i-1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i - 1];
    g[i] = k2[i];g[i-1] = k1[i];
    ns(--i);
    str = plchg();
    str = '<font color="red"><strong>■ 未送信 ■<\\/strong><\\/font><br>' + str;
    disp(str,"white");
    outp();
    newNs = i-1;
  } else if(x == 5) {
    i = Math.floor(a);
    if (i == $init->commandMax - 1){ return true; }
    tmpcom1[i] = command[i];tmpcom2[i] = command[i + 1];
    command[i] = tmpcom2[i];command[i + 1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i + 1];
    g[i] = k2[i];g[i + 1] = k1[i];
    newNs = i+1;
  }else if(x == 7){
    // 移動
    var ctmp = command[k];
    var gtmp = g[k];
    if(z > k) {
      // 上から下へ
      for(i = k; i < z-1; i++) {
        command[i] = command[i+1];
        g[i] = g[i+1];
      }
    } else {
      // 下から上へ
      for(i = k; i > z; i--) {
        command[i] = command[i-1];
        g[i] = g[i-1];
      }
    }
    command[i] = ctmp;
    g[i] = gtmp;
  }else if(x == 8){
    command[a][3] = k;
  }

  str = plchg();
  str = '<font color="red"><b>■ 未送信 ■<\\/b><\\/font><br>' + str;
  disp(str, "white");
  outp();
  theForm.SENDPROJECT.disabled = false;
  ns(newNs);
  return true;
}
function plchg() {
  strn1 = "";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];

    kind = '{$init->tagComName_}' + g[i] + '{$init->_tagComName}';
    x = c[1];
    y = c[2];
    tgt = c[4];
    point = '{$init->tagName_}' + "(" + x + "," + y + ")" + '{$init->spanend}';
    for(j = 0; j < islname.length ; j++) {
      if(tgt == islname[j][0]){
        tgt = '{$init->tagName_}' + islname[j][1] + '{$init->spanend}';
      }
    }
    if(c[0] == $init->comDoNothing || c[0] == $init->comGiveup){ // 放置、国の放棄
      strn2 = kind;
    }else if(c[0] == $init->comMissileNM || // ミサイル関連
             c[0] == $init->comMissilePP ||
             c[0] == $init->comMissileSPP ||
             c[0] == $init->comMissileBT ||
             c[0] == $init->comMissileSP ||
             c[0] == $init->comMissileLD){
      if(c[3] == 0) {
        arg = "（無制限）";
      } else {
        arg = "（" + c[3] + "発）";
      }
      strn2 = tgt + point + "へ" + kind + arg;
    } else if(c[0] == $init->comSendMonster) { // 怪獣派遣
      strn2 = tgt + "へ" + kind;
    } else if(c[0] == $init->comMkShell || // 製造関連
              c[0] == $init->comMkMaterial ||
              c[0] == $init->comMkSteel){
      if(c[3] == 0){
        arg = "（フル稼動）";
      } else {
        arg = "（" + c[3] + "0万ガロン）";
      }
      strn2 = kind + arg;
    } else if(c[0] == $init->comPropaganda) { // 誘致活動
      strn2 = kind;
    } else if(c[0] == $init->comMoney) { // 送金
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * {$init->comCost[$init->comMoney]};
      arg = "（" + arg + "{$init->unitMoney}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comFood) { // 食料輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 10000;
      arg = "（" + arg + "{$init->unitFood}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comSilver) { // 銀輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitSilver}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comGoods) { // 商品輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitGoods}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comAlcohol) { // 酒輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitAlcohol}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comOil) { // 石油輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitOil}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comFuel) { // 燃料輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitFuel}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comWood) { // 木材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitWood}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comStone) { // 石材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitStone}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comSteel) { // 鉄鋼輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitSteel}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comMaterial) { // 建材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitMaterial}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comShell) { // 砲弾輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitShell}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comDestroy||
	c[0] == $init->comDestroy2) { // 掘削
      strn2 = point + "で" + kind;
    }else if(c[0] == $init->comVein){ // 鉱脈調査
      if(c[3] > 49){
        strn2 = point + "で" + kind +"（調査不可）";
      } else{
        if(c[3] > 39){
          if(100 > ((c[3]- 40) * 20)){
            arg = ((c[3]- 40) * 20);
          } else { arg = 100;}
          if(c[3] == 40){arg = 100;}
            kind = '{$init->tagName_}' + "炭坑調査" + '{$init->_tagComName}';
          } else if(c[3] > 29){
            if(100 > ((c[3]- 30) * 10)){
              arg = ((c[3]- 30) * 10);
            } else { arg = 100;}
            if(c[3] == 30){arg = 100;}
            kind = '{$init->tagName_}' + "採石所調査" + '{$init->_tagComName}';
          } else if(c[3] > 19){
            if(100 > ((c[3]- 20) * 5)){
              arg = ((c[3]- 20) * 5);
            } else { arg = 100;}
            if(c[3] == 20){arg = 50;}
            kind = '{$init->tagName_}' + "鉄鋼山の調査" + '{$init->_tagComName}';
          } else if(c[3] > 9){
            if(100 > ((c[3]- 10) * 2)){
              arg = ((c[3]- 10) * 2);
            } else { arg = 100;}
            if(c[3] == 10){arg = 20;}
            kind = '{$init->tagName_}' + "銀山調査" + '{$init->_tagComName}';
          } else {
            if(100 > (c[3] * 1)){
              arg = (c[3] * 1);
            } else { arg = 100;}
            if(c[3] == 0){arg = 10;}
            kind = '{$init->tagName_}' + "ウラン鉱調査" + '{$init->_tagComName}';
          }
          arg = "（成功率" + arg + "％{$init->unitVein}）";
          strn2 = point + "で" + kind + arg;
        }
    }else if(c[0] == $init->comBase){ // ミサイル基地
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "ハリボテ(ミサイル基地)建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "偽装ミサイル基地建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "ミサイル基地建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
	}else if(c[0] == $init->comPubinvest ||
			 c[0] == $init->comEduinvest){　//公共投資,教育投資
		 kind = '{$init->tagName_}' + kind + '{$init->_tagComName}';
		 strn2 = kind;
    }else if(c[0] == $init->comIndPlan ||
			 c[0] == $init->comSocPlan){　//工業政策,社会保障
        arg = "（レベル：" + c[3]+"）";
        strn2 =  kind + arg;
    }else if(c[0] == $init->comFBase){
        kind = '{$init->tagName_}' + tgt + "軍駐屯地建設" + '{$init->_tagComName}';
        strn2 = point + "で" + kind;
	}else if(c[0] == $init->comDbase){ // 防衛施設
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "ハリボテ(防衛施設)建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "偽装防衛施設建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "防衛施設建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
    }else if(c[0] == $init->comSFactory){ // 専門工場
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "畜産場建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "軍事工場建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "建材工場建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
    } else if(c[0] == $init->comSdbase) { // 海底防衛施設
        arg = "(耐久力" + 5 + "）";
        strn2 = point + "で" + kind + arg;
    } else if(c[0] == $init->comFarm || // 農場、養殖場、工場、市場、発電所、僕の引越し
              c[0] == $init->comNursery ||
              c[0] == $init->comFactory ||
              c[0] == $init->comMarket ||
              c[0] == $init->comHatuden) {
      if(c[3] != 0){
        arg = "（" + c[3] + "回）";
        strn2 = point + "で" + kind + arg;
      }else{
        strn2 = point + "で" + kind;
      }
	} else if(c[0] == $init->comCapital) {
      if(c[3] == 0){
	  	lv = 1;
		}else{
		lv = c[3];
		}
        arg = "（レベル：" + lv+"）";
        strn2 = point + "で" + kind + arg;
      }else if(c[0] == $init->comMakeShip){ // 造船
        if(c[3] >= $init->shipKind) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  point + "で" + kind + " (" + shiplist[arg] + ")";
    } else if(c[0] == $init->comEisei){ // 人工衛星打ち上げ
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  '{$init->tagComName_}' + eiseilist[arg] + "打ち上げ" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseimente){ // 人工衛星修復
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  '{$init->tagComName_}' + eiseilist[arg] + "修復" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseiAtt){ // 人工衛星破壊
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  tgt + "へ" + '{$init->tagComName_}' + eiseilist[arg] + "破壊砲発射" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseiLzr) { // 衛星レーザー
      strn2 = tgt + point + "へ" + kind;
    }else{
      strn2 = point + "で" + kind;
    }
    tmpnum = '';
    if(i < 9){ tmpnum = '0'; }
    strn1 +=
      '<div id="com_'+i+'" '+
        'onmouseover="mc_over('+i+');return false;" '+
          '><a style="text-decoration:none;color:000000" HREF="javascript:void(0);" onclick="ns('+i+')" onkeypress="ns('+i+')" '+
            'onmousedown="return comListMove('+i+');" '+'ondblclick="chNum('+c[3]+');return false;" '+
              '><nobr>'+
                tmpnum+(i+1)+':'+
                  strn2+'<\\/nobr><\\/a><\\/div>\\n';
  }
  return strn1;
}

function disp(str,bgclr) {
  if(str==null)  str = "";

  if(document.getElementById){
    document.getElementById("LINKMSG1").innerHTML = str;
    if(bgclr != "")
      document.getElementById("plan").bgColor = bgclr;
  } else if(document.all){
    el = document.all("LINKMSG1");
    el.innerHTML = str;
    if(bgclr != "")
      document.all.plan.bgColor = bgclr;
  } else if(document.layers) {
    lay = document.layers["PARENT_LINKMSG"].document.layers["LINKMSG1"];
    lay.document.open();
    lay.document.write("<font style='font-size:11pt'>"+str+"<\\/font>");
    lay.document.close();
    if(bgclr != "")
      document.layers["PARENT_LINKMSG"].bgColor = bgclr;
  }
}

function outp() {
  comary = "";

  for(k = 0; k < command.length; k++){
    comary = comary + command[k][0]
      + " " + command[k][1]
        + " " + command[k][2]
          + " " + command[k][3]
            + " " + command[k][4]
              + " " ;
  }
  document.InputPlan.COMARY.value = comary;
}

function ps(x, y) {
  document.InputPlan.POINTX.options[x].selected = true;
  document.InputPlan.POINTY.options[y].selected = true;
  if(!(document.InputPlan.MENUOPEN.checked))
    moveLAYER("menu",mx+10,my-10);
}


function ns(x) {
  if (x == $init->commandMax){ return true; }
  document.InputPlan.NUMBER.options[x].selected = true;
  return true;
}

function set_com(x, y, land) {
  com_str = land + " ";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];
    x2 = c[1];
    y2 = c[2];
    if(x == x2 && y == y2 && c[0] < 27){
      com_str += "[" + (i + 1) +"]" ;
      kind = g[i];
      if(c[0] == $init->comFarm ||
         c[0] == $init->comFactory ||
         c[0] == $init->comMarket ||
         c[0] == $init->comHatuden) {
        if(c[3] != 0){
          arg = "（" + c[3] + "回）";
          com_str += kind + arg;
        } else {
          com_str += kind;
        }
      } else {
        com_str += kind;
      }
      com_str += " ";
    }
  }
  document.InputPlan.COMSTATUS.value= com_str;
}

function SelectList(theForm) {
  var u, selected_ok;
  if(!theForm) { s = '' }
  else { s = theForm.menu.options[theForm.menu.selectedIndex].value; }
  if(s == ''){
    u = 0; selected_ok = 0;
    document.InputPlan.COMMAND.options.length = $All_listCom;
    for (i=0; i<comlist.length; i++) {
      var command = comlist[i];
      for (a=0; a<command.length; a++) {
        comName = command[a][1] + "(" + command[a][2] + ")";
        document.InputPlan.COMMAND.options[u].value = command[a][0];
        document.InputPlan.COMMAND.options[u].text = comName;
        if(command[a][0] == $default_Kind){
          document.InputPlan.COMMAND.options[u].selected = true;
          selected_ok = 1;
        }
        u++;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  } else {
    var command = comlist[s];
    document.InputPlan.COMMAND.options.length = command.length;
    for (i=0; i<command.length; i++) {
      comName = command[i][1] + "(" + command[i][2] + ")";
      document.InputPlan.COMMAND.options[i].value = command[i][0];
      document.InputPlan.COMMAND.options[i].text = comName;
      if(command[i][0] == $default_Kind){
        document.InputPlan.COMMAND.options[i].selected = true;
        selected_ok = 1;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  }
}

function moveLAYER(layName,x,y){
  if(document.getElementById){            //NN6,IE5
    el = document.getElementById(layName);
    el.style.left = x;
    el.style.top  = y;
  } else if(document.layers){                             //NN4
    msgLay = document.layers[layName];
    msgLay.moveTo(x,y);
  } else if(document.all){                                //IE4
    msgLay = document.all(layName).style;
    msgLay.pixelLeft = x;
    msgLay.pixelTop = y;
  }
}

function menuclose() {
  moveLAYER("menu",-500,-500);
}

function Mmove(e){
  if(document.all){
    mx = event.x + document.body.scrollLeft;
    my = event.y + document.body.scrollTop;
  }else if(document.layers){
    mx = e.pageX;
    my = e.pageY;
  }else if(document.getElementById){
    mx = e.pageX;
    my = e.pageY;
  }

  return moveLay.move();
}

function LayWrite(layName, str) {
   if(document.getElementById){
      document.getElementById(layName).innerHTML = str;
   } else if(document.all){
      document.all(layName).innerHTML = str;
   } else if(document.layers){
      lay = document.layers[layName];
      lay.document.open();
      lay.document.write(str);
      lay.document.close();
   }
}

function SetBG(layName, bgclr) {
   if(document.getElementById) document.getElementById(layName).style.backgroundColor = bgclr;
   else if(document.all)       document.all.layName.bgColor = bgclr;
   //else if(document.layers)    document.layers[layName].bgColor = bgclr;
}

var oldNum=0;
function selCommand(num) {
  document.getElementById('com_'+oldNum).style.backgroundColor = '';
  document.getElementById('com_'+num).style.backgroundColor = '#FFFFAA';
  oldNum = num;
}

/* コマンド ドラッグ＆ドロップ用追加スクリプト */
var moveLay = new MoveFalse();

var newLnum = -2;
var Mcommand = false;

function Mup() {
   moveLay.up();
   moveLay = new MoveFalse();
}

function setBorder(num, color) {
   if(document.getElementById) {
      if(color.length == 4) document.getElementById('com_'+num).style.borderTop = ' 1px solid '+color;
      else document.getElementById('com_'+num).style.border = '0px';
   }
}

function mc_out() {
   if(Mcommand && newLnum >= 0) {
      setBorder(newLnum, '');
      newLnum = -1;
   }
}

function mc_over(num) {
   if(Mcommand) {
      if(newLnum >= 0) setBorder(newLnum, '');
      newLnum = num;
      setBorder(newLnum, '#116');    // blue
   }
}

function comListMove(num) { moveLay = new MoveComList(num); return (document.layers) ? true : false; }

function MoveFalse() {
   this.move = function() { }
   this.up   = function() { }
}

function MoveComList(num) {
   var setLnum  = num;
   Mcommand = true;

   LayWrite('mc_div', '<NOBR><strong>'+(num+1)+': '+g[num]+'</strong></NOBR>');

   this.move = function() {
      moveLAYER('mc_div',mx+10,my-30);
      return false;
   }

   this.up   = function() {
      if(newLnum >= 0) {
         var com = command[setLnum];
         cominput(document.InputPlan,7,setLnum,newLnum);
      }
      else if(newLnum == -1) cominput(document.InputPlan,3,setLnum+1);

      mc_out();
      newLnum = -2;

      Mcommand = false;
      moveLAYER("mc_div",-50,-50);
   }
}

function showElement(layName) {
        var element = document.getElementById(layName).style;
        element.display = "block";
        element.visibility ='visible';
}

function hideElement(layName) {
        var element = document.getElementById(layName).style;
        element.display = "none";
}

function chNum(num) {
        document.ch_numForm.AMOUNT.options.length = 100;
        for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
                if(document.ch_numForm.AMOUNT.options[i].value == num){
                        document.ch_numForm.AMOUNT.selectedIndex = i;
                        document.ch_numForm.AMOUNT.options[i].selected = true;
                        moveLAYER('ch_num', mx-100, my-600);
                        showElement('ch_num');
                        break;
                }
        }
}

function chNumDo() {
        var num = document.ch_numForm.AMOUNT.options[document.ch_numForm.AMOUNT.selectedIndex].value;
        cominput(document.InputPlan,8,num);
        hideElement('ch_num');
}

function settarget(part){
  p = part.options[part.selectedIndex].value;
}
function targetopen() {
  w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}

    //-->
</script>
END;

    $this->islandInfo($island, $number, 1);

    print <<<END
<div id="menu">
<table border=0 bgcolor=#e0ffff>
<tr><td nowrap>
$click_com<hr>
$click_com2<hr>
<a href="Javascript:void(0);" onClick="menuclose()" style="text-decoration:none">メニューを閉じる</a>
</td></tr>
</table>
</div>

<div ID="mc_div" style="background-color:white;position:absolute;top:-50;left:-50;height:22px;">&nbsp;</div>
<div ID="ch_num" style="position:absolute;visibility:hidden;display:none">
<form name="ch_numForm">
<table border=1 bgcolor="#e0ffff" cellspacing=1>
<tr><td valign=top nowrap>
<a href="JavaScript:void(0)" onClick="hideElement('ch_num');" style="text-decoration:none"><B>×</B></a><br>
<select name="AMOUNT" size=13 onchange="chNumDo()">
</select>
</TD>
</TR>
</TABLE>
</form>
</div>
<div align="center">
<table border="1">
<tr valign="top">
<td $init->bgInputCell>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="COMARY" value="comary">
<input type="hidden" name="DEVELOPEMODE" value="java">
<center>
<br>
<b>コマンド入力</b><br>
<b>
<a href="javascript:void(0);" onclick="cominput(InputPlan,1)">挿入</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,2)">上書き</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,3)">削除</a>
</b>
<hr>
<b>計画番号</b>
<select name="NUMBER">
END;

    // 計画番号
    for($i = 0; $i < $init->commandMax; $i++) {
      $j = $i + 1;
      print "<option value=\"$i\">$j</option>\n";
    }

    if ($data['MENUOPEN'] == 'on') {
      $open = "CHECKED";
    }else{
      $open = "";
    }

    print <<<END
</select>
<hr>
<b>開発計画</b>
<input type="checkbox" name="NAVIOFF" $open>NaviOff
<input type="checkbox" name="MENUOPEN" $open>PopupOff<br>
<br>
<select name="menu" onchange="SelectList(InputPlan)">
<option value="">全種類</option>

END;

    for($i = 0; $i < $com_count; $i++) {
      list($aa, $tmp) = split(",", $init->commandDivido[$i], 2);
      print "<option value=\"$i\">{$aa}</option>\n";
    }
    print <<<END
</select><br>
<select name="COMMAND">
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
</select>
<hr>
<b>座標(</b>
<select name="POINTX">

END;

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultX']) {
        print "<option value=\"$i\" selected>$i</option>\n";
      } else {
        print "<option value=\"$i\">$i</option>\n";
      }
    }

    print "</select>, <select name=\"POINTY\">\n";

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultY']) {
        print "<option value=\"$i\" selected>$i</option>\n";
      } else {
        print "<option value=\"$i\">$i</option>\n";
      }
    }

    print <<<END
</select><b> )</b>
<hr>
<b>数量</b><select name="AMOUNT">

END;

    // 数量
    for($i = 0; $i < 100; $i++) {
      print "<option value=\"$i\">$i</option>\n";
    }

    print <<<END
</select>
<hr>
<b>目標の国</b><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList<br>
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>
<b>コマンド移動</b>：
<a href="javascript:void(0);" onclick="cominput(InputPlan,4)" style="text-decoration:none"> ▲ </a>・・
<a href="javascript:void(0);" onclick="cominput(InputPlan,5)" style="text-decoration:none"> ▼ </a>
<hr>
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="submit" value="計画送信" name="SENDPROJECT"	>
<br>最後に<font color="red">計画送信ボタン</font>を<br>押すのを忘れないように。</font>
</center>
</form>
<center>ミサイル発射上限数[<b> {$island['fire']} </b>]発</center>
</td>
<td $init->bgMapCell><center>
</center>
</div>
END;

    $this->islandMap($hako, $island, 1);    // 国の地図、所有者モード

    $comment = $hako->islands[$number]['comment'];
	$Cname = $hako->islands[$number]['Cname'];
	$banum = $hako->islands[$number]['banum'];
    print <<<END

</td>
<td $init->bgCommandCell id="plan">
<ilayer name="PARENT_LINKMSG" width="100%" height="100%">
<layer name="LINKMSG1" width="200"></layer>
<span id="LINKMSG1"></span>
</ilayer>
<br>
</td>
</tr>
</table>
</center>
<hr>
</div>
<div>
END;
$param['GLTH'] = $GLOBALS['THIS_FILE'];
$param['comment'] =str_replace( "<br />","\n",$island['comment']);
$param['PAS'] =   $data['PASSWORD'];
$param['id']  = $island['id'];
$param['CNAM'] =  $island['Cname'];
$param['num'] =  $island['banum'];
$param['mode'] = "java";

print HTML::tplengine('./templates/banners.html',$param);

  }
}

Class HtmlAdv extends HtmlJS{
  function header($data = "") {
    global $init;
    global $PRODUCT_VERSION;

    // 圧縮転送
    if(GZIP == true) {
      global $http;
      $http->start();
    }
    header("X-Product-Version: {$PRODUCT_VERSION}");
    $css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
    $bimg = (empty($data['defaultImg'])) ? $init->imgDir : $data['defaultImg'];
    print <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<base href="{$bimg}/">
<meta http-equiv="Content-type" content="text/html; charset=Shift_JIS">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/li-scroller.css">
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<title>{$init->title}</title>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('jquery', '1');
google.load('jqueryui', '1');
</script>
<script type="text/javascript" src="{$init->baseDir}/jquery.li-scroller.1.0.js"></script>
<script type="text/javascript" src="{$init->baseDir}/hako.js"></script>
</head>
<body onload="init()">
END;
HTML::header_Link();
  }

  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function tempOwer($hako, $data, $number = 0) {
    global $init;
    $island = $hako->islands[$number];
    $name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

    $width  = $init->islandSize * 32 + 50;
    $height = $init->islandSize * 32 + 100;

    // コマンドセット
    $set_com = "";
    $com_max = "";
    for($i = 0; $i < $init->commandMax; $i++) {
      // 各要素の取り出し
      $command  = $island['command'][$i];
      $s_kind   = $command['kind'];
      $s_target = $command['target'];
      $s_x      = $command['x'];
      $s_y      = $command['y'];
      $s_arg    = $command['arg'];

      // コマンド登録
      if($i == $init->commandMax - 1){
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target]\n";
        $com_max .= "0";
      } else {
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target],\n";
        $com_max .= "0,";
      }
    }

    //コマンドリストセット
    $l_kind;
    $set_listcom = "";
    $click_com = "";
    $click_com2 = "";
    $All_listCom = 0;
    $com_count = count($init->commandDivido);
    for($m = 0; $m < $com_count; $m++) {
      list($aa,$dd,$ff) = split(",", $init->commandDivido[$m]);
      $set_listcom .= "[ ";
      for($i = 0; $i < $init->commandTotal; $i++) {
        $l_kind = $init->comList[$i];
        $l_cost = $init->comCost[$l_kind];
        $S_cost = -$init->comSCost[$l_kind];
        if($l_cost < 0) {
          $l_cost = - $l_cost;
        }
          if($l_kind >= 87) {
            $l_cost = "無料";
          }elseif($l_kind >= 41 && $l_kind <= 43) { $l_cost .= $init->unitFuel;
          }elseif($l_kind >= 75 && $l_kind <= 78 || $l_kind == 82) { $l_cost .= $init->unitSteel;
          }elseif($l_kind == 9 || $l_kind == 16 || $l_kind == 20 || $l_kind == 21) { $l_cost = "種別による"; // 16⇒専門工場、20⇒ミサイル基地、21⇒防衛施設
		  }elseif($l_kind == 83 || $l_kind == 84) { $l_cost = "ポイントによる"; //　83⇒公共投資,84⇒教育投資
          }elseif($l_kind == 10 || $l_kind == 81) { $l_cost .= $init->unitFuel;
          }elseif($l_kind == 80) { $l_cost .= $init->unitOil;
          }elseif($l_kind == 72) { $l_cost .= $init->unitFood;
          }elseif($l_kind == 73) { $l_cost .= $init->unitGoods;
          }elseif($l_kind == 74) { $l_cost .= $init->unitAlcohol;
          }elseif($l_kind == 79) { $l_cost .= $init->unitSilver;
          }elseif($l_kind == 82) { $l_cost .= $init->unitShell;
          }elseif($l_kind >= 51 && $l_kind <= 56) { $l_cost .= $init->unitFuel;
          }elseif($l_kind == 61){
            $l_cost = '資金:鋼鉄:燃料:';
            $l_cost .= $init->comCost[$l_kind];
            $l_cost .= "0ずつ";
          }else {
            $l_cost .= $init->unitMoney;
          }
          if($S_cost != 0) {
            if($l_kind == 3 || $l_kind == 4 || $l_kind == 23){
			 // 23⇒記念碑
              $l_cost .= '+石材';
              $l_cost .= $S_cost;
              $l_cost .= $init->unitStone;
            }elseif($l_kind == 30 || $l_kind == 22){
			  // 30⇒造船
              $l_cost .= '+鋼鉄';
              $l_cost .= -$S_cost;
              $l_cost .= $init->unitSteel;
            }elseif($l_kind >= 12 && $l_kind <= 21 || $l_kind >= 23 && $l_kind <= 29 || $l_kind >= 32 && $l_kind <= 37||$l_kind == 83){
              $l_cost .= '+建材';
              $l_cost .= -$S_cost;
              $l_cost .= $init->unitStone;
            }elseif($l_kind >= 51 && $l_kind <= 56 || $l_kind == 62) {
              $l_cost .= "+砲弾";
              $l_cost .= $S_cost;
              $l_cost .= $init->unitShell;
            }elseif($l_kind >= 57 && $l_kind <= 60) {
              $l_cost .= "+";
              $l_cost .= $S_cost;
              $l_cost .= $init->unitFuel;
            }elseif($l_kind != 61){
              $l_cost .= '+建材';
              $l_cost .= $S_cost;
              $l_cost .= $init->unitMaterial;
            }
          }
        if($l_kind > $dd-1 && $l_kind < $ff+1) {
          $set_listcom .= "[$l_kind, '{$init->comName[$l_kind]}', '{$l_cost}'],\n";
          if($m == 0){
            $click_com .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})</a><br>\n";
          } elseif($m == 1) {
            $click_com2 .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})</a><br>\n";
          }
          $All_listCom++;
        }
        if($l_kind < $ff+1) { next; }
      }
      $bai = strlen($set_listcom);
      $set_listcom = substr($set_listcom, 0, $bai - 2);
      $set_listcom .= " ],\n";
    }
    $bai = strlen($set_listcom);
    $set_listcom = substr($set_listcom, 0, $bai - 2);
    if(empty($data['defaultKind'])) {
      $default_Kind = 1;
    } else {
      $default_Kind = 1;
    }

    // 船リストセット
    //$set_ships = implode("," , $init->shipName);
    for($i = 0; $i < count($init->shipName); $i++) {
        $set_ships .= "'".$init->shipName[$i]."',";
    }

    // 衛星リストセット
    //$set_eisei = implode("," , $init->EiseiName);
    for($i = 0; $i < count($init->EiseiName); $i++) {
        $set_eisei .= "'".$init->EiseiName[$i]."',";
    }

    // 国リストセット
    $set_island = "";
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $l_name = $hako->islands[$i]['name'];
      $l_name = preg_replace("/'/", "\'", $l_name);
      $l_id = $hako->islands[$i]['id'];
      if($i == $hako->islandNumber - 1){
        $set_island .= "[$l_id, '$l_name']\n";
      }else{
        $set_island .= "[$l_id, '$l_name'],\n";
      }
    }
    $defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;

    print <<<END
<center>
{$init->tagBig_}{$init->tagName_}{$name}{$init->spanend}開発計画{$init->spanend}<BR>
{$GLOBALS['BACK_TO_TOP']}<br>
</center>
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;

// ＪＡＶＡスクリプト開発画面配布元
// あっぽー庵箱庭諸国（ http://appoh.execweb.cx/hakoniwa/ ）
// Programmed by Jynichi Sakai(あっぽー)
// ↑ 削除しないで下さい。
var str;
g = [$com_max];
k1 = [$com_max];
k2 = [$com_max];
tmpcom1 = [ [0,0,0,0,0] ];
tmpcom2 = [ [0,0,0,0,0] ];
command = [
$set_com];

comlist = [
$set_listcom];

islname = [
$set_island];

shiplist = [$set_ships];
eiseilist = [$set_eisei];

function init() {
  for(i = 0; i < command.length ;i++) {
    for(s = 0; s < $com_count ;s++) {
      var comlist2 = comlist[s];
      for(j = 0; j < comlist2.length ; j++) {
        if(command[i][0] == comlist2[j][0]) {
          g[i] = comlist2[j][1];
        }
      }
    }
  }
  SelectList('');
  outp();
  str = plchg();
  str = '<font color="blue">■ 送信済み ■<\\/font><br>' + str;
  disp(str, "");
	document.onmousemove = Mmove;

  if(document.layers) {
    document.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
  }
  document.onmouseup   = Mup;
  document.onmousemove = Mmove;
  document.onkeydown = Kdown;
  document.ch_numForm.AMOUNT.options.length = 100;
  for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
    document.ch_numForm.AMOUNT.options[i].value = i;
    document.ch_numForm.AMOUNT.options[i].text  = i;
  }
  document.InputPlan.SENDPROJECT.disabled = true;
  ns(0);
}

function cominput(theForm, x, k, z) {
  a = theForm.NUMBER.options[theForm.NUMBER.selectedIndex].value;
  b = theForm.COMMAND.options[theForm.COMMAND.selectedIndex].value;
  c = theForm.POINTX.options[theForm.POINTX.selectedIndex].value;
  d = theForm.POINTY.options[theForm.POINTY.selectedIndex].value;
  e = theForm.AMOUNT.options[theForm.AMOUNT.selectedIndex].value;
  f = theForm.TARGETID.options[theForm.TARGETID.selectedIndex].value;
  if(x == 6){ b = k; menuclose(); }
  var newNs = a;
  if (x == 1 || x == 2 || x == 6){
    if(x == 6) b = k;
    if(x != 2) {
      for(i = $init->commandMax - 1; i > a; i--) {
        command[i] = command[i-1];
        g[i] = g[i-1];
      }
    }

    for(s = 0; s < $com_count ;s++) {
      var comlist2 = comlist[s];
      for(i = 0; i < comlist2.length; i++){
        if(comlist2[i][0] == b){
          g[a] = comlist2[i][1];
          break;
        }
      }
    }
    command[a] = [b,c,d,e,f];
    newNs++;
//    menuclose();
  } else if(x == 3) {
    var num = (k) ? k-1 : a;
    for(i = Math.floor(num); i < ($init->commandMax - 1); i++) {
      command[i] = command[i + 1];
      g[i] = g[i+1];
    }
    command[$init->commandMax - 1] = [88, 0, 0, 0, 0];
    g[$init->commandMax - 1] = '放置';
  } else if(x == 4) {
    i = Math.floor(a);
    if (i == 0){ return true; }
    i = Math.floor(a);
    tmpcom1[i] = command[i];tmpcom2[i] = command[i - 1];
    command[i] = tmpcom2[i];command[i-1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i - 1];
    g[i] = k2[i];g[i-1] = k1[i];
    ns(--i);
    str = plchg();
    str = '<font color="red"><strong>■ 未送信 ■<\\/strong><\\/font><br>' + str;
    disp(str,"white");
    outp();
    newNs = i-1;
  } else if(x == 5) {
    i = Math.floor(a);
    if (i == $init->commandMax - 1){ return true; }
    tmpcom1[i] = command[i];tmpcom2[i] = command[i + 1];
    command[i] = tmpcom2[i];command[i + 1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i + 1];
    g[i] = k2[i];g[i + 1] = k1[i];
    newNs = i+1;
  }else if(x == 7){
    // 移動
    var ctmp = command[k];
    var gtmp = g[k];
    if(z > k) {
      // 上から下へ
      for(i = k; i < z-1; i++) {
        command[i] = command[i+1];
        g[i] = g[i+1];
      }
    } else {
      // 下から上へ
      for(i = k; i > z; i--) {
        command[i] = command[i-1];
        g[i] = g[i-1];
      }
    }
    command[i] = ctmp;
    g[i] = gtmp;
  }else if(x == 8){
    command[a][3] = k;
  }

  str = plchg();
  str = '<font color="red"><b>■ 未送信 ■<\\/b><\\/font><br>' + str;
  disp(str, "white");
  outp();
  theForm.SENDPROJECT.disabled = false;
  ns(newNs);
  return true;
}
function plchg() {
  strn1 = "";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];

    kind = '{$init->tagComName_}' + g[i] + '{$init->_tagComName}';
    x = c[1];
    y = c[2];
    tgt = c[4];
    point = '{$init->tagName_}' + "(" + x + "," + y + ")" + '{$init->spanend}';
    for(j = 0; j < islname.length ; j++) {
      if(tgt == islname[j][0]){
        tgt = '{$init->tagName_}' + islname[j][1] + '{$init->spanend}';
      }
    }
    if(c[0] == $init->comDoNothing || c[0] == $init->comGiveup){ // 放置、国の放棄
      strn2 = kind;
    }else if(c[0] == $init->comMissileNM || // ミサイル関連
             c[0] == $init->comMissilePP ||
             c[0] == $init->comMissileSPP ||
             c[0] == $init->comMissileBT ||
             c[0] == $init->comMissileSP ||
             c[0] == $init->comMissileLD){
      if(c[3] == 0) {
        arg = "（無制限）";
      } else {
        arg = "（" + c[3] + "発）";
      }
      strn2 = tgt + point + "へ" + kind + arg;
    } else if(c[0] == $init->comSendMonster) { // 怪獣派遣
      strn2 = tgt + "へ" + kind;
    } else if(c[0] == $init->comMkShell || // 製造関連
              c[0] == $init->comMkMaterial ||
              c[0] == $init->comMkSteel){
      if(c[3] == 0){
        arg = "（フル稼動）";
      } else {
        arg = "（" + c[3] + "0万ガロン）";
      }
      strn2 = kind + arg;
    } else if(c[0] == $init->comPropaganda) { // 誘致活動
      strn2 = kind;
    } else if(c[0] == $init->comMoney) { // 送金
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * {$init->comCost[$init->comMoney]};
      arg = "（" + arg + "{$init->unitMoney}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comFood) { // 食料輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 10000;
      arg = "（" + arg + "{$init->unitFood}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comSilver) { // 銀輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitSilver}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comGoods) { // 商品輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitGoods}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comAlcohol) { // 酒輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitAlcohol}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comOil) { // 石油輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitOil}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comFuel) { // 燃料輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitFuel}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comWood) { // 木材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitWood}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comStone) { // 石材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitStone}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comSteel) { // 鉄鋼輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitSteel}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comMaterial) { // 建材輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitMaterial}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comShell) { // 砲弾輸送
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 50;
      arg = "（" + arg + "{$init->unitShell}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comDestroy||
	c[0] == $init->comDestroy2) { // 掘削
      strn2 = point + "で" + kind;
    }else if(c[0] == $init->comVein){ // 鉱脈調査
      if(c[3] > 49){
        strn2 = point + "で" + kind +"（調査不可）";
      } else{
        if(c[3] > 39){
          if(100 > ((c[3]- 40) * 20)){
            arg = ((c[3]- 40) * 20);
          } else { arg = 100;}
          if(c[3] == 40){arg = 100;}
            kind = '{$init->tagName_}' + "炭坑調査" + '{$init->_tagComName}';
          } else if(c[3] > 29){
            if(100 > ((c[3]- 30) * 10)){
              arg = ((c[3]- 30) * 10);
            } else { arg = 100;}
            if(c[3] == 30){arg = 100;}
            kind = '{$init->tagName_}' + "採石所調査" + '{$init->_tagComName}';
          } else if(c[3] > 19){
            if(100 > ((c[3]- 20) * 5)){
              arg = ((c[3]- 20) * 5);
            } else { arg = 100;}
            if(c[3] == 20){arg = 50;}
            kind = '{$init->tagName_}' + "鉄鋼山の調査" + '{$init->_tagComName}';
          } else if(c[3] > 9){
            if(100 > ((c[3]- 10) * 2)){
              arg = ((c[3]- 10) * 2);
            } else { arg = 100;}
            if(c[3] == 10){arg = 20;}
            kind = '{$init->tagName_}' + "銀山調査" + '{$init->_tagComName}';
          } else {
            if(100 > (c[3] * 1)){
              arg = (c[3] * 1);
            } else { arg = 100;}
            if(c[3] == 0){arg = 10;}
            kind = '{$init->tagName_}' + "ウラン鉱調査" + '{$init->_tagComName}';
          }
          arg = "（成功率" + arg + "％{$init->unitVein}）";
          strn2 = point + "で" + kind + arg;
        }
    }else if(c[0] == $init->comBase){ // ミサイル基地
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "ハリボテ(ミサイル基地)建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "偽装ミサイル基地建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "ミサイル基地建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
	}else if(c[0] == $init->comPubinvest ||
			 c[0] == $init->comEduinvest){　//公共投資,教育投資
		 kind = '{$init->tagName_}' + kind + '{$init->_tagComName}';
		 strn2 = kind;
    }else if(c[0] == $init->comIndPlan ||
			 c[0] == $init->comSocPlan){　//工業政策,社会保障
        arg = "（レベル：" + c[3]+"）";
        strn2 =  kind + arg;
    }else if(c[0] == $init->comFBase){
        kind = '{$init->tagName_}' + tgt + "軍駐屯地建設" + '{$init->_tagComName}';
        strn2 = point + "で" + kind;
	}else if(c[0] == $init->comDbase){ // 防衛施設
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "ハリボテ(防衛施設)建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "偽装防衛施設建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "防衛施設建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
    }else if(c[0] == $init->comSFactory){ // 専門工場
      if(c[3] == 2){
        kind = '{$init->tagName_}' + "畜産場建設" + '{$init->_tagComName}';
      } else if(c[3] == 1){
        kind = '{$init->tagName_}' + "軍事工場建設" + '{$init->_tagComName}';
      } else {
        kind = '{$init->tagName_}' + "建材工場建設" + '{$init->_tagComName}';
      }
      strn2 = point + "で" + kind;
    } else if(c[0] == $init->comSdbase) { // 海底防衛施設
        arg = "(耐久力" + 5 + "）";
        strn2 = point + "で" + kind + arg;
    } else if(c[0] == $init->comFarm || // 農場、養殖場、工場、市場、発電所、僕の引越し
              c[0] == $init->comNursery ||
              c[0] == $init->comFactory ||
              c[0] == $init->comMarket ||
              c[0] == $init->comHatuden) {
      if(c[3] != 0){
        arg = "（" + c[3] + "回）";
        strn2 = point + "で" + kind + arg;
      }else{
        strn2 = point + "で" + kind;
      }
	} else if(c[0] == $init->comCapital) {
      if(c[3] == 0){
	  	lv = 1;
		}else{
		lv = c[3];
		}
        arg = "（レベル：" + lv+"）";
        strn2 = point + "で" + kind + arg;
      }else if(c[0] == $init->comMakeShip){ // 造船
        if(c[3] >= $init->shipKind) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  point + "で" + kind + " (" + shiplist[arg] + ")";
    } else if(c[0] == $init->comEisei){ // 人工衛星打ち上げ
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  '{$init->tagComName_}' + eiseilist[arg] + "打ち上げ" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseimente){ // 人工衛星修復
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  '{$init->tagComName_}' + eiseilist[arg] + "修復" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseiAtt){ // 人工衛星破壊
        if(c[3] >= $init->EiseiNumber) {
          c[3] = 0;
        }
        arg = c[3];
        strn2 =  tgt + "へ" + '{$init->tagComName_}' + eiseilist[arg] + "破壊砲発射" + '{$init->_tagComName}';
    } else if(c[0] == $init->comEiseiLzr) { // 衛星レーザー
      strn2 = tgt + point + "へ" + kind;
    }else{
      strn2 = point + "で" + kind;
    }
    tmpnum = '';
    if(i < 9){ tmpnum = '0'; }
    strn1 +=
      '<div id="com_'+i+'" '+
        'onmouseover="mc_over('+i+');return false;" '+
          '><a style="text-decoration:none;color:000000" HREF="javascript:void(0);" onclick="ns('+i+')" onkeypress="ns('+i+')" '+
            'onmousedown="return comListMove('+i+');" '+'ondblclick="chNum('+c[3]+');return false;" '+
              '><nobr>'+
                tmpnum+(i+1)+':'+
                  strn2+'<\\/nobr><\\/a><\\/div>\\n';
  }
  return strn1;
}

function disp(str,bgclr) {
  if(str==null)  str = "";

  if(document.getElementById){
    document.getElementById("LINKMSG1").innerHTML = str;
    if(bgclr != "")
      document.getElementById("plan").bgColor = bgclr;
  } else if(document.all){
    el = document.all("LINKMSG1");
    el.innerHTML = str;
    if(bgclr != "")
      document.all.plan.bgColor = bgclr;
  } else if(document.layers) {
    lay = document.layers["PARENT_LINKMSG"].document.layers["LINKMSG1"];
    lay.document.open();
    lay.document.write("<font style='font-size:11pt'>"+str+"<\\/font>");
    lay.document.close();
    if(bgclr != "")
      document.layers["PARENT_LINKMSG"].bgColor = bgclr;
  }
}

function outp() {
  comary = "";

  for(k = 0; k < command.length; k++){
    comary = comary + command[k][0]
      + " " + command[k][1]
        + " " + command[k][2]
          + " " + command[k][3]
            + " " + command[k][4]
              + " " ;
  }
  document.InputPlan.COMARY.value = comary;
}

function ps(x, y) {
  document.InputPlan.POINTX.options[x].selected = true;
  document.InputPlan.POINTY.options[y].selected = true;
  if(!(document.InputPlan.MENUOPEN.checked))
    moveLAYER("menu",mx+10,my-10);
}


function ns(x) {
  if (x == $init->commandMax){ return true; }
  document.InputPlan.NUMBER.options[x].selected = true;
  return true;
}

function set_com(x, y, land) {
  com_str = land + " ";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];
    x2 = c[1];
    y2 = c[2];
    if(x == x2 && y == y2 && c[0] < 27){
      com_str += "[" + (i + 1) +"]" ;
      kind = g[i];
      if(c[0] == $init->comFarm ||
         c[0] == $init->comFactory ||
         c[0] == $init->comMarket ||
         c[0] == $init->comHatuden) {
        if(c[3] != 0){
          arg = "（" + c[3] + "回）";
          com_str += kind + arg;
        } else {
          com_str += kind;
        }
      } else {
        com_str += kind;
      }
      com_str += " ";
    }
  }
  document.InputPlan.COMSTATUS.value= com_str;
}

function SelectList(theForm) {
  var u, selected_ok;
  if(!theForm) { s = '' }
  else { s = theForm.menu.options[theForm.menu.selectedIndex].value; }
  if(s == ''){
    u = 0; selected_ok = 0;
    document.InputPlan.COMMAND.options.length = $All_listCom;
    for (i=0; i<comlist.length; i++) {
      var command = comlist[i];
      for (a=0; a<command.length; a++) {
        comName = command[a][1] + "(" + command[a][2] + ")";
        document.InputPlan.COMMAND.options[u].value = command[a][0];
        document.InputPlan.COMMAND.options[u].text = comName;
        if(command[a][0] == $default_Kind){
          document.InputPlan.COMMAND.options[u].selected = true;
          selected_ok = 1;
        }
        u++;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  } else {
    var command = comlist[s];
    document.InputPlan.COMMAND.options.length = command.length;
    for (i=0; i<command.length; i++) {
      comName = command[i][1] + "(" + command[i][2] + ")";
      document.InputPlan.COMMAND.options[i].value = command[i][0];
      document.InputPlan.COMMAND.options[i].text = comName;
      if(command[i][0] == $default_Kind){
        document.InputPlan.COMMAND.options[i].selected = true;
        selected_ok = 1;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  }
}

function moveLAYER(layName,x,y){
  if(document.getElementById){            //NN6,IE5
    el = document.getElementById(layName);
    el.style.left = x;
    el.style.top  = y;
  } else if(document.layers){                             //NN4
    msgLay = document.layers[layName];
    msgLay.moveTo(x,y);
  } else if(document.all){                                //IE4
    msgLay = document.all(layName).style;
    msgLay.pixelLeft = x;
    msgLay.pixelTop = y;
  }
}

function menuclose() {
  moveLAYER("menu",-500,-500);
}

function Mmove(e){
  if(document.all){
    mx = event.x + document.body.scrollLeft;
    my = event.y + document.body.scrollTop;
  }else if(document.layers){
    mx = e.pageX;
    my = e.pageY;
  }else if(document.getElementById){
    mx = e.pageX;
    my = e.pageY;
  }

  return moveLay.move();
}

function LayWrite(layName, str) {
   if(document.getElementById){
      document.getElementById(layName).innerHTML = str;
   } else if(document.all){
      document.all(layName).innerHTML = str;
   } else if(document.layers){
      lay = document.layers[layName];
      lay.document.open();
      lay.document.write(str);
      lay.document.close();
   }
}

function SetBG(layName, bgclr) {
   if(document.getElementById) document.getElementById(layName).style.backgroundColor = bgclr;
   else if(document.all)       document.all.layName.bgColor = bgclr;
   //else if(document.layers)    document.layers[layName].bgColor = bgclr;
}

var oldNum=0;
function selCommand(num) {
  document.getElementById('com_'+oldNum).style.backgroundColor = '';
  document.getElementById('com_'+num).style.backgroundColor = '#FFFFAA';
  oldNum = num;
}

/* コマンド ドラッグ＆ドロップ用追加スクリプト */


function showElement(layName) {
        var element = document.getElementById(layName).style;
        element.display = "block";
        element.visibility ='visible';
}

function hideElement(layName) {
        var element = document.getElementById(layName).style;
        element.display = "none";
}

function chNum(num) {
        document.ch_numForm.AMOUNT.options.length = 100;
        for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
                if(document.ch_numForm.AMOUNT.options[i].value == num){
                        document.ch_numForm.AMOUNT.selectedIndex = i;
                        document.ch_numForm.AMOUNT.options[i].selected = true;
                        moveLAYER('ch_num', mx-100, my-600);
                        showElement('ch_num');
                        break;
                }
        }
}

function chNumDo() {
        var num = document.ch_numForm.AMOUNT.options[document.ch_numForm.AMOUNT.selectedIndex].value;
        cominput(document.InputPlan,8,num);
        hideElement('ch_num');
}

function settarget(part){
  p = part.options[part.selectedIndex].value;
}
function targetopen() {
  w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}

    //-->
</script>
END;

    $this->islandInfo($island, $number, 1);

    print <<<END
<div id="menu">
<table border=0 bgcolor=#e0ffff>
<tr><td nowrap>
$click_com<hr>
$click_com2<hr>
<a href="Javascript:void(0);" onClick="menuclose()" style="text-decoration:none">メニューを閉じる</a>
</td></tr>
</table>
</div>

<div ID="mc_div" style="background-color:white;position:absolute;top:-50;left:-50;height:22px;">&nbsp;</div>
<div ID="ch_num" style="position:absolute;visibility:hidden;display:none">
<form name="ch_numForm">
<table border=1 bgcolor="#e0ffff" cellspacing=1>
<tr><td valign=top nowrap>
<a href="JavaScript:void(0)" onClick="hideElement('ch_num');" style="text-decoration:none"><B>×</B></a><br>
<select name="AMOUNT" size=13 onchange="chNumDo()">
</select>
</TD>
</TR>
</TABLE>
</form>
</div>
<div align="center">
<table border="1">
<tr valign="top">
<td $init->bgInputCell>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="COMARY" value="comary">
<input type="hidden" name="DEVELOPEMODE" value="java">
<center>
<br>
<b>コマンド入力</b><br>
<b>
<a href="javascript:void(0);" onclick="cominput(InputPlan,1)">挿入</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,2)">上書き</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,3)">削除</a>
</b>
<hr>
<b>計画番号</b>
<select name="NUMBER">
END;

    // 計画番号
    for($i = 0; $i < $init->commandMax; $i++) {
      $j = $i + 1;
      print "<option value=\"$i\">$j</option>\n";
    }

    if ($data['MENUOPEN'] == 'on') {
      $open = "CHECKED";
    }else{
      $open = "";
    }

    print <<<END
</select>
<hr>
<b>開発計画</b>
<input type="checkbox" name="NAVIOFF" $open>NaviOff
<input type="checkbox" name="MENUOPEN" $open>PopupOff<br>
<br>
<select name="menu" onchange="SelectList(InputPlan)">
<option value="">全種類</option>

END;


    for($i = 0; $i < $com_count; $i++) {
      list($aa, $tmp) = split(",", $init->commandDivido[$i], 2);
      print "<option value=\"$i\">{$aa}</option>\n";
    }
    print <<<END
</select><br>
<select name="COMMAND">
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
</select>
<hr>
<b>座標(</b>
<select name="POINTX">

END;

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultX']) {
        print "<option value=\"$i\" selected>$i</option>\n";
      } else {
        print "<option value=\"$i\">$i</option>\n";
      }
    }

    print "</select>, <select name=\"POINTY\">\n";

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultY']) {
        print "<option value=\"$i\" selected>$i</option>\n";
      } else {
        print "<option value=\"$i\">$i</option>\n";
      }
    }

    print <<<END
</select><b> )</b>
<hr>
<b>数量</b><select name="AMOUNT">

END;

    // 数量
    for($i = 0; $i < 100; $i++) {
      print "<option value=\"$i\">$i</option>\n";
    }

    print <<<END
</select>
<hr>
<b>目標の国</b><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList<br>
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>
<b>コマンド移動</b>：
<a href="javascript:void(0);" onclick="cominput(InputPlan,4)" style="text-decoration:none"> ▲ </a>・・
<a href="javascript:void(0);" onclick="cominput(InputPlan,5)" style="text-decoration:none"> ▼ </a>
<hr>
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="submit" value="計画送信" name="SENDPROJECT"	>
<br>最後に<font color="red">計画送信ボタン</font>を<br>押すのを忘れないように。</font>
</center>
</form>
<center>ミサイル発射上限数[<b> {$island['fire']} </b>]発</center>
</td>
<td $init->bgMapCell><center>
</center>
END;

    $this->islandMap($hako, $island, 1);    // 国の地図、所有者モード

    $comment = $hako->islands[$number]['comment'];
	$Cname = $hako->islands[$number]['Cname'];
	$banum = $hako->islands[$number]['banum'];
    print <<<END

</td>
<td $init->bgCommandCell id="plan">
<ul id="sortable">
END;
    $command = $island['command'];
    for($i = 0; $i < $init->commandMax; $i++) {
      $this->tempCommand2($i, $command[$i], $hako);
    }
    print <<<END
</ul>
</td>
</tr>
</table>
</center>
<hr>
<div id="accordion">
    <h3><a href="#">ニュース更新</a></h3>
		<div id='NewsBox'>
		<form action="{$GLOBALS['THIS_FILE']}" method="post">
		<select name="CATEGORY" size="1" tabindex="0">
		<option value="【政治】">【政治】</option>
		<option value="【経済】">【経済】</option>
		<option value="【貿易】">【貿易】</option>
		<option value="【国際】">【国際】</option>
		</select>
		コメント<input type="text" name="TEXT" size="80" maxlength="40" value="{$island['comment']}"><br>
		<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
		<input type="hidden" name="mode" value="postnews">
		<input type="hidden" name="DEVELOPEMODE" value="java">
		<input type="hidden" name="ISLANDID" value="{$island['id']}">
		<input type="submit" value="ニュース更新">
		</FORM>
		</DIV>
    <h3><a href="#">ニュース更新</a></h3>
		<div id='CommentBox'>
		<form action="{$GLOBALS['THIS_FILE']}" method="post">
		コメント<input type="text" name="MESSAGE" size="80" maxlength="40" value="{$island['comment']}"><br>
		<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
		<input type="hidden" name="mode" value="comment">
		<input type="hidden" name="DEVELOPEMODE" value="java">
		<input type="hidden" name="ISLANDID" value="{$island['id']}">
		<input type="submit" value="コメント更新">
		</FORM>
		</DIV>
    <h3><a href="#">首都名入力</a></h3>
		<div id='CapitalName'>
		<form action="{$GLOBALS['THIS_FILE']}" method="post">
		首都名<input type="text" name="MESSAGE" size="30" maxlength="10" value="{$island['Cname']}"><br>
		<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
		<input type="hidden" name="mode" value="Cname">
		<input type="hidden" name="DEVELOPEMODE" value="java">
		<input type="hidden" name="ISLANDID" value="{$island['id']}">
		<input type="submit" value="首都名変更">
		</form>
		</div>
    <h3><a href="#">国旗番号入力</a></h3>
		<div id='Banner'>
		<form action="{$GLOBALS['THIS_FILE']}" method="post">
		国旗番号<input type="text" name="Number" size="5" maxlength="3" value="{$island['banum']}"><br>
		<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
		<input type="hidden" name="mode" value="Banner">
		<input type="hidden" name="DEVELOPEMODE" value="java">
		<input type="hidden" name="ISLANDID" value="{$island['id']}">
		<input type="submit" value="国旗番号確定">
		</form>
		<a href="/trade/img/up/">国旗ファイルはここからアップロード</a>
		<p>サイズは任意。ただし表示サイズは45×30ピクセル固定<br>
		ファイルサイズは4KB以下<br>
		ファイル形式はPNGのみ<br>
		手順：アップロード⇒ファイル番号をこのページで入力<br>
		<b>ファイル番号は半角英数字</b></p>
		</div>
</div>




</DIV>
END;

  }
  //---------------------------------------------------
  // 入力済みコマンド表示2
  //---------------------------------------------------
	function tempCommand2($number, $command, $hako) {
    global $init;

    $kind   = $command['kind'];
    $target = $command['target'];
    $x      = $command['x'];
    $y      = $command['y'];
    $arg    = $command['arg'];

    $comName = "{$init->tagComName_}{$init->comName[$kind]}{$init->_tagComName}";
    $point   = "{$init->tagName_}({$x},{$y}){$init->spanend}";
    $target  = $hako->idToName[$target];
    if(empty($target)) {
      $target = "無人";
    }
    $target = "{$init->tagName_}{$target}{$init->spanend}";
    $value = $arg * $init->comCost[$kind];
    if($value == 0) {
      $value = $init->comCost[$kind];
    }
    if($value < 0) {

        if($kind == $init->comFood) {
        // 食料輸送
            $value = -$value;
            $value = "{$value}{$init->unitFood}";
        }
        if($kind == $init->comShell) {
        // 砲弾輸送
            $value = -$value;
            $value = "{$value}{$init->unitShell}";
        }
        if($kind == $init->comSteel) {
        // 鉄鋼輸送
            $value = -$value;
            $value = "{$value}{$init->unitSteel}";
        }
        if($kind == $init->comMaterial) {
        // 鉄鋼輸送
            $value = -$value;
            $value = "{$value}{$init->unitMaterial}";
        }
        if($kind == $init->comOil) {
        // 石油輸送
            $value = -$value;
            $value = "{$value}{$init->unitOil}";
        }
        if($kind == $init->comSilver) {
        // 銀輸送
            $value = -$value;
            $value = "{$value}{$init->unitSilver}";
        }
        if($kind == $init->comWood) {
        // 木材輸送
            $value = -$value;
            $value = "{$value}{$init->unitWood}";
        }
        if($kind == $init->comAlcohol) {
        // 食肉輸送
            $value = -$value;
            $value = "{$value}{$init->unitAlcohol}";
        }

        if($kind == $init->comGoods) {
        // 商品輸送
            $value = -$value;
            $value = "{$value}{$init->unitGoods}";
        }
        if($kind == $init->comStone) {
        // 石材輸送
            $value = -$value;
            $value = "{$value}{$init->unitStone}";
        }
        if(($kind == $init->comFuel) ||
           ($kind == $init->comMkMaterial) ||
           ($kind == $init->comMkSteel) ||
           ($kind == $init->comMkShell)) {
        // 燃料輸送、砲弾製造など
            $value = -$value;
            $value = "$value{$init->unitFuel}";
        }

    } else {
      $value = "{$value}{$init->unitMoney}";
    }
    $value = "{$init->tagName_}{$value}{$init->spanend}";

    $j = sprintf("%02d：", $number + 1);
    print "<li class=\"ui-state-default\"><a href=\"javascript:void(0);\" onclick=\"ns({$number})\">{$init->tagNumber_}{$j}{$init->_tagNumber}";

    switch($kind) {
    case $init->comDoNothing:
    case $init->comGiveup:
    case $init->comPropaganda:
	case $init->comPubinvest:
	case $init->comEduinvest:
      $str = "{$comName}";
      break;
    case $init->comMissileNM:
    case $init->comMissilePP:
    case $init->comMissileSPP:
    case $init->comMissileBT:
    case $init->comMissileSP:
    case $init->comMissileLD:
      // ミサイル系
      $n = ($arg == 0) ? '無制限' : "{$arg}発";
      $str = "{$target}{$point}へ{$comName}({$init->tagName_}{$n}{$init->spanend})";
      break;
    case $init->comEisei:
      // 人工衛星発射
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str = "{$init->tagComName_}{$init->EiseiName[$arg]}打ち上げ{$init->_tagComName}";
      break;
    case $init->comEiseimente:
      // 人工衛星修復
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str = "{$init->tagComName_}{$init->EiseiName[$arg]}修復{$init->_tagComName}";
      break;
    case $init->comEiseiAtt:
      // 人工衛星破壊砲
      if($arg >= $init->EiseiNumber) {
        $arg = 0;
      }
      $str =  "{$target}へ{$init->tagComName_}{$init->EiseiName[$arg]}破壊砲発射{$init->_tagComName}";
      break;
    case $init->comEiseiLzr:
      // 衛星レーザー
      $str = "{$target}{$point}へ{$comName}";
      break;
    case $init->comSendMonster:
      // 怪獣派遣
      $str = "{$target}へ{$comName}";
      break;
    case $init->comMkShell:
    case $init->comMkMaterial:
    case $init->comMkSteel:
      $n = ($arg == 0 ? "{$init->tagName_}フル稼働{$init->spanend}" : $value);
      $str ="{$comName}($n)";
      break;
    case $init->comMoney:
    case $init->comSilver:
    case $init->comSteel:
    case $init->comMaterial:
    case $init->comStone:
    case $init->comShell:
    case $init->comOil:
    case $init->comWood:
    case $init->comExplosive:
    case $init->comAlcohol:
    case $init->comFuel:
    case $init->comGoods:
    case $init->comFood:
      // 輸送系
      $str = "{$target}へ{$comName}{$value}";
      break;
    case $init->comDestroy:
    case $init->comDestroy2:
      // 掘削
      $str = "{$point}で{$comName}";
      break;
    case $init->comBase:
      if($arg == 2) {
        $name = "ハリボテ(ミサイル基地)建設";
      } else if($arg == 1) {
        $name = "偽装ミサイル基地建設";
      } else {
        $name = "ミサイル基地建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comDbase:
      if($arg == 2) {
        $name = "ハリボテ(防衛施設)建設";
      } else if($arg == 1) {
        $name = "偽装防衛施設建設";
      } else {
        $name = "防衛施設建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comSFactory:
      if($arg == 2) {
        $name = "畜産場建設";
      } else if($arg == 1) {
        $name = "軍事工場建設";
      } else {
        $name = "建材工場建設";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}";
      break;
    case $init->comSdbase:
      // 海底防衛施設
        $str = "{$point}で{$comName}(耐久力5)";
      break;
    case $init->comFarm:
    case $init->comNursery:
    case $init->comMine:
    case $init->comMarket:
    case $init->comFactory:
    case $init->comHatuden:
    case $init->comBoku:
      // 回数付き
      if($arg == 0) {
        $str = "{$point}で{$comName}";
      } else {
        $str = "{$point}で{$comName}({$arg}回)";
      }
      break;
	case $init->comCapital:
	case $init->comIndPlan:
      // 回数付き
      if($arg == 0) {
        $str = "{$point}で{$comName}";
      } else {
        $str = "{$point}で{$comName}(レベル：{$arg})";
      }
      break;
    case $init->comVein:
      $success;
      if($arg > 49) {
        $name = "調査不可";
        $success = 0;
      } else if($arg > 39) {
        $success = min(100, (($arg - 40) * 20));
        if($arg == 40){$success = 100;}
        $name = "炭坑調査";
      } else if($arg > 29) {
        $success = min(100, (($arg - 30) * 10));
        if($arg == 30){$success = 100;}
        $name = "採石場調査";
      } else if($arg > 19) {
        $success = min(100, (($arg - 20) * 5));
        if($arg == 20){$success = 50;}
        $name = "鉄鉱山調査";
      } else if($arg > 9) {
        $success = min(100, (($arg - 10) * 2));
        if($arg == 10){$success = 20;}
        $name = "銀山調査";
      } else {
        $success = min(100, $arg);
        if($arg == 0){$success = 10;}
        $name = "ウラン鉱調査";
      }
      $str = "{$point}で{$init->tagName_}{$name}{$init->spanend}({$init->tagName_}成功率{$success}％{$init->spanend})";
      break;
    default:
      // 座標付き
      $str = "{$point}で{$comName}";
    }

    print "{$str}</li>";
	}
}
//---------------------------------------------------------------------------------------------------------------------------------
class HtmlSetted extends HTML {
  function setSkin() {
    global $init;
    print "{$init->tagBig_}スタイルシートを設定しました。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
  }
  function setImg() {
    global $init;
    print "{$init->tagBig_}画像のローカル設定をしました。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
  }
  function comment() {
    global $init;
    print "{$init->tagBig_}コメントを更新しました{$init->spanend}<hr>";
  }
  function News() {
    global $init;
    print "{$init->tagBig_}ニュースを投稿しました{$init->spanend}<hr>";
  }
  function Capital() {
    global $init;
    print "{$init->tagBig_}首都名を更新しました{$init->spanend}<hr>";
  }
  function change() {
    global $init;
    print "{$init->tagBig_}変更完了しました{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
  }
  function lbbsDelete() {
    global $init;
    print "{$init->tagBig_}記帳内容を削除しました{$init->spanend}<hr>";
  }
  function lbbsAdd() {
    global $init;
    print "{$init->tagBig_}記帳を行いました{$init->spanend}<hr>";
  }
  // コマンド削除
  function commandDelete() {
    global $init;
    print "{$init->tagBig_}コマンドを削除しました{$init->spanend}<hr>\n";
  }

  // コマンド登録
  function commandAdd() {
    global $init;
    print "{$init->tagBig_}コマンドを登録しました{$init->spanend}<hr>\n";
  }
}
class Error {
  function wrongPassword() {
    global $init;
    print "{$init->tagBig_}パスワードが違います。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function wrongID() {
    global $init;
    print "{$init->tagBig_}IDが違います。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  // hakojima.datがない
  function noDataFile() {
    global $init;
    print "{$init->tagBig_}データファイルが開けません。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandFull() {
    global $init;
    print "{$init->tagBig_}申し訳ありません、国が一杯で登録できません！！{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  // 受付中かどうか
  function tempNewIslandForbbiden() {
    global $init;
    print "{$init->tagBig_}申し訳ありません、受付を中止しています。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandNoName() {
    global $init;
    print "{$init->tagBig_}国につける名前が必要です。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandBadName() {
    global $init;
    print "{$init->tagBig_},?()<>\$とか入ってたり、「無人国」とかいった変な名前はやめましょうよ～。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandAlready() {
    global $init;
    print "{$init->tagBig_}その国ならすでに発見されています。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandNoPassword() {
    global $init;
    print "{$init->tagBig_}パスワードが必要です。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function newIslandNoAgree() {
    global $init;
    print "{$init->tagBig_}規約に同意してください。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function changeNoMoney() {
    global $init;
    print "{$init->tagBig_}資金不足のため変更できません{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function changeNothing() {
    global $init;
    print "{$init->tagBig_}名前、パスワードともに空欄です{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function problem() {
    global $init;
    print "{$init->tagBig_}問題発生、とりあえず戻ってください。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function lbbsNoMessage() {
    global $init;
    print "{$init->tagBig_}名前または内容の欄が空欄です。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function lockFail() {
    global $init;
    print "{$init->tagBig_}同時アクセスエラーです。<BR>ブラウザの「戻る」ボタンを押し、<BR>しばらく待ってから再度お試し下さい。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
  function lbbsNoMoney() {
    global $init;
    print "{$init->tagBig_}資金不足のため記帳できません。{$init->spanend}{$GLOBALS['BACK_TO_TOP']}\n";
    HTML::footer();
    exit;
  }
}
?>
