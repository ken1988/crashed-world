<?php
/*******************************************************************

  箱庭諸島２ for PHP


  $Id: hako-turn.php,v 1.11 2004/03/23 13:00:23 watson Exp $

*******************************************************************/

require 'hako-log.php';


class Make {
  //---------------------------------------------------
  // 島の新規作成モード
  //---------------------------------------------------
  function newIsland($hako, $data) {
    global $init;
    $log = new Log;
    if($hako->islandNumber >= $init->maxIsland) {
      Error::newIslandFull();
      return;
    }

    // 受付ターンかどうか
    if(($hako->islandTurn >= $init->entryTurn) && ($init->entryTurn > 0)) {
        Error::tempNewIslandForbbiden();
        return;
    }

    if(empty($data['ISLANDNAME'])) {
      Error::newIslandNoName();
      return;
    }
    // 名前が正当化チェック
    if(ereg("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
      Error::newIslandBadName();
      return;

    }
    // 名前の重複チェック
    if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
      Error::newIslandAlready();
      return;
    }
    // パスワードの存在判定
    if(empty($data['PASSWORD'])) {
      Error::newIslandNoPassword();
      return;
    }
    if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
      Error::wrongPassword();
      return;
    }
	if($data['agree'] != "agree"){
      Error::newIslandNoAgree();
      return;
	}
    // 新しい島の番号を決める
    $newNumber = $hako->islandNumber;
    $hako->islandNumber++;
    $island = $this->makeNewIsland();

    // 各種の値を設定
    $island['name']  = htmlspecialchars($data['ISLANDNAME']);
    $island['owner'] = htmlspecialchars($data['OWNERNAME']);
    $island['id']    = $hako->islandNextID;
    $hako->islandNextID++;
    $island['starturn'] = $hako->islandTurn;
    $island['absent'] = $init->giveupTurn - 24;
    $island['comment'] = '(未登録)';
    $island['comment_turn'] = $hako->islandTurn;
    $island['password'] = Util::encode($data['PASSWORD']);
    $island['tenki'] = 1;

    Turn::estimate($island);
    $hako->islands[$newNumber] = $island;
    $hako->writeIslandsFile($island['id']);

    $log->discover($island['name']);

    $htmlMap = new HtmlMap;
    $htmlMap->newIslandHead($island['name']);
    $htmlMap->islandInfo($island, $newNumber);
    $htmlMap->islandMap($hako, $island, 1, $data);

  }
  //---------------------------------------------------
  // 新しい島を作成する
  //---------------------------------------------------
  function makeNewIsland() {
    global $init;
    $command = array();
    // 初期コマンド生成
    for($i = 0; $i < $init->commandMax; $i++) {
      $command[$i] = array (
        'kind'   => $init->comDoNothing,
        'target' => 0,
        'x'      => 0,
        'y'      => 0,
        'arg'    => 0,
        );
    }
    $lbbs = "";
    // 初期掲示板生成
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $lbbs[$i] = "0>>0>>";
    }

    $regT = "";
    // 初期定期輸送生成
    for($i = 0; $i < $init->regTMax; $i++) {
      $regT[$i] = "";
    }

    $land = array();
    $landValue = array();
    // 基本形を作成
    for($y = 0; $y < $init->islandSize; $y++) {
      for($x = 0; $x < $init->islandSize; $x++) {
        $land[$x][$y]      = $init->landSea;
        $landValue[$x][$y] = 0;
      }
    }

    // 4*4に荒地を配置
    $center = $init->islandSize / 2 - 1;
    for($y = $center -1; $y < $center + 3; $y++) {
      for($x = $center - 1; $x < $center + 3; $x++) {
        $land[$x][$y] = $init->landWaste;
      }
    }
    // 8*8範囲内に陸地を増殖
    for($i = 0; $i < 120; $i++) {
      $x = Util::random(8) + $center - 3;
      $y = Util::random(8) + $center - 3;
      if(Turn::countAround($land, $x, $y, $init->landSea, 7) != 7) {
        // 周りに陸地がある場合、浅瀬にする
        // 浅瀬は荒地にする
        // 荒地は平地にする
        if($land[$x][$y] == $init->landWaste) {
          $land[$x][$y] = $init->landPlains;
          $landValue[$x][$y] = 0;
        } else {
          if($landValue[$x][$y] == 1) {
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          } else {
            $landValue[$x][$y] = 1;
          }
        }
      }
    }
    // 森を作る
    $count = 0;
    while($count < 4) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこがすでに森でなければ、森を作る
      if($land[$x][$y] != $init->landForest) {
        $land[$x][$y] = $init->landForest;
        $landValue[$x][$y] = 5; // 最初は500本
        $count++;
      }
    }
    $count = 0;
    while($count < 2) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこが森か町でなければ、町を作る
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest)) {
        $land[$x][$y] = $init->landTown;
        $landValue[$x][$y] = 5; // 最初は500人
        $count++;
      }
    }

    // 山を作る
    $count = 0;
    while($count < $init->initMountain) {
      // ランダム座標
      $x = Util::random(6) + $center - 1;
      $y = Util::random(6) + $center - 1;

      // そこが森か町でなければ、町を作る
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest)) {
        $land[$x][$y] = $init->landMountain;
        $landValue[$x][$y] = 0; // 最初は採掘場なし
        $count++;
      }
    }
	//2つ目の山を炭鉱（Lv.1)にする
    $land[$x][$y] = $init->landCoal;

    // 基地を作る
    $count = 0;
    while($count < 1) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこが森か町か山でなければ、基地
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest) &&
         ($land[$x][$y] != $init->landMountain)) {
        $land[$x][$y] = $init->landBase;
        $landValue[$x][$y] = 0;
        $count++;
      }
    }

    return array (
      'money'	  => $init->initialMoney,
      'food'	  => $init->initialFood,
      'goods'	  => $init->initialGoods,
      'alcohol'	  => $init->initialAlcohol,
      'silver'	  => $init->initialSilver,
      'wood'	  => $init->initialWood,
      'stone'	  => $init->initialStone,
      'steel'	  => $init->initialSteel,
      'material'  => $init->initialMaterial,
      'oil'	      => $init->initialOil,
      'fuel'	  => $init->initialFuel,
      'shell'	  => $init->initialShell,
      'land'	  => $land,
      'landValue' => $landValue,
      'command'	  => $command,
      'lbbs'	  => $lbbs,
	  'regT'	  => $regT,
      'prize'	  => '0,0,',
      );
  }
  //---------------------------------------------------
  // コメント更新
  //---------------------------------------------------
  function commentMain($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
    // メッセージを更新
    $island['comment'] = htmlspecialchars($data['MESSAGE']);

	$island['comment'] = Util::conv_LF($island['comment']);
	$island['comment'] = str_replace("\n", "<br />", $island['comment']);
    $island['comment_turn'] = $hako->islandTurn;
    $hako->islands[$num] = $island;

    // データの書き出し
    $hako->writeIslandsFile();

    // コメント更新メッセージ
    HtmlSetted::Comment();

    // owner modeへ
    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    $html->owner($hako, $data);
  }
  //---------------------------------------------------
  // ニュース更新
  //---------------------------------------------------
  function NewsMain($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];
	$dataset = array("nationID"=>'',"category"=>'',"text"=>'',"turn"=>'',"author"=>'');

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
	$wns = new WNSsys();

	$dataset['gamedate'] = Util::MKCal($hako->islandTurn,2);

    // メッセージを更新
	$data['TEXT'] = htmlspecialchars($data['TEXT']);
	$island['news']		 = $data['CATEGORY'].$data['TEXT']."（".$dataset['gamedate']."付）";

	$dataset['nationID'] = $id;
	$dataset['category'] = mb_convert_encoding($data['CATEGORY'],"UTF-8", "UTF-8,SJIS");
	$dataset['text']	 = $data['TEXT'];
	$dataset['turn']	 = $hako->islandTurn;
	$dataset['gamedate'] = mb_convert_encoding($dataset['gamedate'],"UTF-8", "UTF-8,SJIS");
	$dataset['author']	 = mb_convert_encoding($name,"UTF-8",  "UTF-8,SJIS");

    $hako->islands[$num] = $island;

    // データの書き出し
    $wns->Updater($dataset);
    $hako->writeIslandsFile();

    // コメント更新メッセージ
    HtmlSetted::News();

    // owner modeへ
    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    $html->owner($hako, $data);
  }


  //---------------------------------------------------
  // 首都名更新
  //---------------------------------------------------
  function Capitalname($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
    // メッセージを更新
    $island['Cname'] = htmlspecialchars($data['MESSAGE']);
    $hako->islands[$num] = $island;

    // データの書き出し
    $hako->writeIslandsFile();

    // コメント更新メッセージ
    HtmlSetted::Capital();


    // owner modeへ
    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    $html->owner($hako, $data);
	}
  //---------------------------------------------------
  // 国旗番号確定
  //---------------------------------------------------
  function Banner($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
    // メッセージを更新
    $island['banum'] = htmlspecialchars($data['Number']);
    $hako->islands[$num] = $island;

    // データの書き出し
    $hako->writeIslandsFile();

    // コメント更新メッセージ
    HtmlSetted::Capital();


    // owner modeへ
    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    $html->owner($hako, $data);
	}
  //---------------------------------------------------
  // ローカル掲示板モード
  //---------------------------------------------------
  function localBbsMain($hako, $data) {
    global $init;

    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];
    $speaker = "0>";

    // なぜかその島がない場合
    if($num != 0 && empty($num)) {
      Error::problem();
      return;
    }

    // 削除モードじゃなくて名前かメッセージがない場合
    if(empty($data['DEL']) && empty($data['CHK'])) {
      if(empty($data['LBBSNAME']) || (empty($data['LBBSMESSAGE']))) {
        Error::lbbsNoMessage();
        return;
      }
    }

    // 観光者モードじゃない時はパスワードチェック
    if($data['lbbsMode'] == 1) {
      if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
        // password間違い
        Error::wrongPassword();
        return;
      }

      // オーナー名を設定
      $HlbbsName = $island['owner'];
    } else if (empty($data['DEL']) && empty($data['CHK'])) {
      // 観光者モード
      if ($data['LBBSTYPE'] != 'ANON') {
        // 公開と極秘

        // idから島番号を取得
        $sNum = $hako->idToNumber[$data['ISLANDID2']];
        $sIsland = $hako->islands[$sNum];

        // なぜかその島がない場合
        if($sNum != 0 && empty($sNum)) {
          Error::problem();
          return;
        }

        // パスワードチェック
        if(!Util::checkPassword($sIsland['password'], $data['PASSWORD'])) {
          // password間違い
          Error::wrongPassword();
          return;
        }

        // オーナー名を設定
        $HlbbsName = $sIsland['owner'];

        // 通信費用を払う
        if($data['LBBSTYPE'] == 'PUBLIC') {
          $cost = $init->lbbsMoneyPublic;
        } else {
          $cost = $init->lbbsMoneySecret;
        }
        if($sIsland['money'] < $cost) {
          // 費用不足
          Error::lbbsNoMoney();
          return;
        }
        $sIsland['money'] -= $cost;
        $hako->islands[$sNum] = $sIsland;
      }

      // 発言者を記憶する
      if($data['LBBSTYPE'] != 'ANON') {
        // 公開と極秘
        $speaker = $sIsland['name'] . ',' . $data['ISLANDID2'];
      } else {
        // 匿名
        $speaker = getenv('REMOTE_HOST');
        if($speaker == '') {
          $speaker = getenv('REMOTE_ADDR');
        }
      }
      if($data['LBBSTYPE'] != 'SECRET') {
        // 公開と匿名
        $speaker = "0>$speaker";
      } else {
        // 極秘
        $speaker = "1>$speaker";
      }
    } else {
      // 観光者削除モード・極秘確認モード
      // idから島番号を取得
      $sNum = $hako->idToNumber[$data['ISLANDID2']];
      $sIsland = $hako->islands[$sNum];

      // なぜかその島がない場合
      if($sNum != 0 && empty($sNum)) {
        Error::problem();
        return;
      }

      // パスワードチェック
      if(!Util::checkPassword($sIsland['password'], $data['PASSWORD'])) {
        // password間違い
        Error::wrongPassword();
        return;
      }
    }

    $lbbs = $island['lbbs'];

    // モードで分岐
    if(!empty($data['CHK'])) {
      if($data['DEVELOPEMODE'] == "cgi") {
        $html = new HtmlMap;
      } else {
        $html = new HtmlJS;
      }
      $html->visitor($hako, $data, $data['ISLANDID2']);
      return;
    } else if(!empty($data['DEL'])) {
      if($data['lbbsMode'] == 0) {
        list($secret, $sTemp, $mode, $turn, $message) = mb_split(">", $lbbs[$data['NUMBER']]);
        list($sName, $sId) = mb_split(",", $sTemp);
        if($sId != $data['ISLANDID2']) {
          // ID間違い
          Error::wrongID();
          return;
        }
      }
      // 削除モード
      // メッセージを前にずらす
      Util::slideBackLbbsMessage($lbbs, $data['NUMBER']);
      HtmlSetted::lbbsDelete();
    } else {
      // 記帳モード
      Util::slideLbbsMessage($lbbs);

      // メッセージ書き込み
      if($data['lbbsMode'] == 0) {
        $message = '0';
      } else {
        $message = '1';
      }
      $bbs_name = "{$hako->islandTurn}：" . htmlspecialchars($data['LBBSNAME']);
      $bbs_message = htmlspecialchars($data['LBBSMESSAGE']);
      $lbbs[0] = "{$speaker}>{$message}>{$bbs_name}>{$bbs_message}>{$data['LBBSCOLOR']}";

      HtmlSetted::lbbsAdd();
    }
    $island['lbbs'] = $lbbs;
    $hako->islands[$num] = $island;

    // データ書き出し
    $hako->writeIslandsFile($id);

    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    // もとのモードへ
    if($data['lbbsMode'] == 0) {
      $html->visitor($hako, $data);
    } else {
      $html->owner($hako, $data);
    }
  }
  //---------------------------------------------------
  // 定期輸送登録モード
  //---------------------------------------------------
  function regularTradeMain($hako, $data) {
    global $init;

    $id  = $data['ISLANDID'];
	$target = $data['TARGETID'];
	$kind	= $data['command'];
	$value	= $data['num'];
    $num = $hako->idToNumber[$id];
	$tnum = $hako->idToNumber[$target];
    $island = $hako->islands[$num];
	$tisland = $hako->islands[$tnum];

    // なぜかその国がない場合
    if($num != 0 && empty($num)) {
      Error::problem();
      return;
    }
    //パスワードチェック
      if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
        // password間違い
        Error::wrongPassword();
        return;
    }

    $regT = $island['regT'];

    // モードで分岐
	if(!empty($data['DEL'])) {
      // 削除モード
	  list($t,$k,$a,$start) = split(",",$regT[$data['NUMBER']]);
	  if(($hako->islandTurn < $start + $init->regTTerm)&&($hako->islandTurn !== $start)){
	  Error::problem();
	  }else{
      // 輸送予定を前にずらす
      Util::slideregT($regT, $data['NUMBER']);
      HtmlSetted::lbbsDelete();
	  }
    } else {
      // 記帳モード
	  if($id == $target){
	  Error::problem();
		  }else{
		    for($i = 0; $i < $init->regTMax; $i++) {
				if(!empty($regT[$i])){
					$nu++;
				}
			}
		  if(($nu == $init->regTsMax && $island['bport'] == 0)||($nu == $init->regTMax && $island['bport'] == 1)){
			  Error::problem();
		  }else{
			  if($kind == 72){
			  //食糧輸送のみ輸送量を10分の1にする
			  $value = $value / 10;
			  }
		      Util::regTpush($regT);
			  $regT[0] = "{$target},{$kind},{$value},{$hako->islandTurn}";
		      HtmlSetted::lbbsAdd();
		  }
	    }
	}
    $island['regT'] = $regT;
    $hako->islands[$num] = $island;

    // データ書き出し
    $hako->writeIslandsFile($id);

    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    // もとのモードへ
      $html->owner($hako, $data);
  }

  //---------------------------------------------------
  // 情報変更モード
  //---------------------------------------------------
  function changeMain($hako, $data) {
    global $init;
    $log = new Log;

    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワードチェック
    if(strcmp($data['OLDPASS'], $init->specialPassword) == 0) {
      // 特殊パスワード
      //$island['money'] = $init->maxMoney;
      //$island['food']  = $init->maxFood;
    } elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }

    // 確認用パスワード
    if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
      // password間違い
      Error::wrongPassword();
      return;
    }

    if(!empty($data['ISLANDNAME'])) {
      // 名前変更の場合
      // 名前が正当かチェック
      if(ereg("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
        Error::newIslandBadName();
        return;
      }

      // 名前の重複チェック
      if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
        Error::newIslandAlready();
        return;
      }

      if($island['money'] < $init->costChangeName) {
        // 金が足りない
        Error::changeNoMoney();
        return;
      }

      // 代金
      if(strcmp($data['OLDPASS'], $init->specialPassword) != 0) {
        $island['money'] -= $init->costChangeName;
      }

      // 名前を変更
      $log->changeName($island['name'], $data['ISLANDNAME']);
      $island['name'] = htmlspecialchars($data['ISLANDNAME']);
      $flag = 1;
    }
    // password変更の場合
    if(!empty($data['PASSWORD'])) {
      // パスワードを変更
      $island['password'] = Util::encode($data['PASSWORD']);
      $flag = 1;
    }

    if(($flag == 0) && (strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0)) {
      // どちらも変更されていない
      Error::changeNothing();
      return;
    }
    $hako->islands[$num] = $island;
    // データ書き出し
    $hako->writeIslandsFile($id);

    // 変更成功
    HtmlSetted::change();
  }
  //---------------------------------------------------
  // オーナ名変更モード
  //---------------------------------------------------
  function changeOwnerName($hako, $data) {
    global $init;

    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];

    // パスワードチェック
    if(strcmp($data['OLDPASS'], $init->specialPassword) == 0) {
      // 特殊パスワード
    //  $island['money'] = $init->maxMoney;
   //   $island['food']  = $init->maxFood;
    } elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
    $island['owner'] = htmlspecialchars($data['OWNERNAME']);
    $hako->islands[$num] = $island;
    // データ書き出し
    $hako->writeIslandsFile($id);

    // 変更成功
    HtmlSetted::change();
  }
  //---------------------------------------------------
  // 凍結・解除モード
  //---------------------------------------------------
  function freezeNation($hako, $data) {
    global $init;

    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];

    // パスワードチェック
    if(strcmp($data['PASSWORD'], $init->specialPassword) == 0) {
    } elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
	if ($island['freeze'] == 1) {
    $island['freeze'] = 0;
	} else {
	$island['freeze'] = 1;
	}
    $hako->islands[$num] = $island;

    // データ書き出し
    $hako->writeIslandsFile($id);
    // 変更成功
    HtmlSetted::change();
	}
  //---------------------------------------------------
  // コマンドモード
  //---------------------------------------------------
  function commandMain($hako, $data) {
    global $init;
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }

    // モードで分岐
    $command = $island['command'];

    if(strcmp($data['COMMANDMODE'], 'delete') == 0) {
      Util::slideFront($command, $data['NUMBER']);
      HtmlSetted::commandDelete();
    } elseif(($data['COMMAND'] == $init->comAutoPrepare) ||
             ($data['COMMAND'] == $init->comAutoPrepare2)) {
      // フル整地、フル地ならし
      // 座標配列を作る
      $r = Util::makeRandomPointArray();
      $rpx = $r[0];
      $rpy = $r[1];
      $land = $island['land'];
      // コマンドの種類決定
      $kind = $init->comPrepare;
      if($data['COMMAND'] == $init->comAutoPrepare2) {
        $kind = $init->comPrepare2;
      }

      $i = $data['NUMBER'];
      $j = 0;
      while(($j < $init->pointNumber) && ($i < $init->commandMax)) {
        $x = $rpx[$j];
        $y = $rpy[$j];
        if($land[$x][$y] == $init->landWaste) {
          Util::slideBack($command, $data['NUMBER']);
          $command[$data['NUMBER']] = array (
            'kind'	=> $kind,
            'target'	=> 0,
            'x'		=> $x,
            'y'		=> $y,
            'arg'	=> 0,
            );
          $i++;
        }
        $j++;
      }
      HtmlSetted::commandAdd();
    } elseif($data['COMMAND'] == $init->comAutoDelete) {
      // 全消し
      for($i = 0; $i < $init->commandMax; $i++) {
        Util::slideFront($command, 0);
      }
      HtmlSetted::commandDelete();
    } else {
      if(strcmp($data['COMMANDMODE'], 'insert') == 0) {
        Util::slideBack($command, $data['NUMBER']);
      }
      HtmlSetted::commandAdd();
      // コマンドを登録
      $command[$data['NUMBER']] = array (
        'kind'   => $data['COMMAND'],
        'target' => $data['TARGETID'],
        'x'      => $data['POINTX'],
        'y'      => $data['POINTY'],
        'arg'    => $data['AMOUNT'],
        );
    }

    // データの書き出し
    $island['command'] = $command;
    $hako->islands[$num] = $island;
    $hako->writeIslandsFile($island['id']);

    // owner modeへ
    $html = new HtmlMap;
    $html->owner($hako, $data);
  }
}
class MakeJS extends Make {
  //---------------------------------------------------
  // コマンドモード
  //---------------------------------------------------
  function commandMain($hako, $data) {
    global $init;
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      Error::wrongPassword();
      return;
    }
    // モードで分岐
    $command = $island['command'];
    $comary = split(" " , $data['COMARY']);

    for($i = 0; $i < $init->commandMax; $i++) {
      $pos = $i * 5;
      $kind   = $comary[$pos];
      $x      = $comary[$pos + 1];
      $y      = $comary[$pos + 2];
      $arg    = $comary[$pos + 3];
      $target = $comary[$pos + 4];
      // コマンド登録
      if($kind == 0) {
        $kind = $init->comDoNothing;
      }
      $command[$i] = array (
        'kind'   => $kind,
        'x'      => $x,
        'y'      => $y,
        'arg'    => $arg,
        'target' => $target
        );
    }
    HtmlSetted::commandAdd();

    // データの書き出し
    $island['command'] = $command;
    $hako->islands[$num] = $island;
    $hako->writeIslandsFile($island['id']);

    // owner modeへ
    $html = new HtmlJS;
    $html->owner($hako, $data);
  }
}
//--------------------------------------------------------------------
class Turn {
  var $log;
  var $rpx;
  var $rpy;
  //---------------------------------------------------
  // ターン進行モード
  //---------------------------------------------------
  function turnMain(&$hako, $data) {
    global $init;
    $this->log = new Log;

    // 最終更新時間を更新
    $hako->islandLastTime += $init->unitTime;
    // ログファイルを後ろにずらす
    $this->log->slideBackLogFile();

    // ターン番号
    $hako->islandTurn++;
    $GLOBALS['ISLAND_TURN'] = $hako->islandTurn;
    if($hako->islandNumber == 0) {
      // 島がなければターン数を保存して以降の処理は省く
      // ファイルに書き出し
      $hako->writeIslandsFile();
      return;
    }

    // プレゼントファイルを読み込む(終れば消去)
    $hako->readPresentFile(true);
    // 座標配列を作る
    $randomPoint = Util::makeRandomPointArray();
    $this->rpx = $randomPoint[0];
    $this->rpy = $randomPoint[1];
    // 順番決め
    $order = Util::randomArray($hako->islandNumber);

    // 収入・消費
    for($i = 0; $i < $hako->islandNumber; $i++) {

	  $calendars = Util::MKCal($hako->islandTurn,0);
	  $calendars = strip_tags($calendars);
	  //$this->log->turnstart($hako->islands[$order[$i]]['id'],$calendars);

	  if($hako->islands[$order[$i]]['freeze'] == 1) {
	  }else {
	  //集計処理する
      $hako->islands[$order[$i]]['oldMoney'] = $hako->islands[$order[$i]]['money'];
      $this->estimate($hako->islands[$order[$i]],$hako);
      // 食料をメモする
      $hako->islands[$order[$i]]['oldFood'] = $hako->islands[$order[$i]]['food'];
      $this->income($hako->islands[$order[$i]]);
      // 人口、資金、ポイントをメモする
      $hako->islands[$order[$i]]['oldPop'] = $hako->islands[$order[$i]]['pop'];
      $hako->islands[$order[$i]]['oldPoint'] = $hako->islands[$order[$i]]['point'];
	  }
    }
    // コマンド処理
    for($i = 0; $i < $hako->islandNumber; $i++) {
	 if ($hako->islands[$order[$i]]['freeze'] == 1) {
		 if ( $island['present']['item'] == 0 ) {
	     	if ( $island['present']['px'] != 0 ) {
	        	$island['money'] += $island['present']['px'];
	        	$this->log->presentMoney($island['id'], $island['name'], $island['present']['px']);
	      	}
	     if ( $island['present']['py'] != 0 ) {
	        $island['food'] += $island['present']['py'];
	        $this->log->presentFood($island['id'], $island['name'], $island['present']['py']);
	      }
		  }
	  }else {
      // 戻り値1になるまで繰り返し
      while($this->doCommand($hako, $hako->islands[$order[$i]]) == 0);
	  }
    }
    // 成長および単ヘックス災害
    for($i = 0; $i < $hako->islandNumber; $i++) {
	 if ($hako->islands[$order[$i]]['freeze']  == 1) {
	  }else {
      $this->doEachHex($hako, $hako->islands[$order[$i]]);
	  }
    }
    // 島全体処理
    $remainNumber = $hako->islandNumber;
    for($i = 0; $i < $hako->islandNumber; $i++) {

	 if ($hako->islands[$order[$i]]['freeze'] == 1) {
	  // 各種の値を計算
	  $island = $hako->islands[$order[$i]];
      $island['peop'] = 0;
      $island['pots'] = 0;
	  $island['gold'] = 0;
      $island['rice'] = 0;
	  }else {
      $island = $hako->islands[$order[$i]];

      $this->doIslandProcess($hako, $island);

      // 死滅判定
      if($island['dead'] == 1) {
        $island['pop'] = 0;
        $island['point'] = 0;
        $remainNumber--;
      } elseif((($island['pop'] == 0) || ($island['point'] == 0)) && ($island['id'] != 1)) {
        $island['dead'] = 1;
        $remainNumber--;
        // 死滅メッセージ
        $tmpid = $island['id'];
        $this->log->dead($tmpid, $island['name']);
        if(is_file("{$init->dirName}/island.{$tmpid}")) {
          unlink("{$init->dirName}/island.{$tmpid}");
        }
      }
      $hako->islands[$order[$i]] = $island;
	  }
    }
    // 人口順にソート
    $this->islandSort($hako);
    // ターン杯対象ターンだったら、その処理
    if(($hako->islandTurn % $init->turnPrizeUnit) == 0) {
      $island = $hako->islands[0];
      $this->log->prize($island['id'], $island['name'], "{$hako->islandTurn}{$init->prizeName[0]}");
      $hako->islands[0]['prize'] .= "{$hako->islandTurn},";
    }
	//統計前処理
	for($i = 0; $i < $hako->islandNumber; $i++){
	  $stat['pop'] += $hako->islands[$i]['pop'];
	  $stat['farm'] += $hako->islands[$i]['farm'] * 10;
	  $stat['ind'] += $hako->islands[$i]['factory'] * 10;
	  $stat['market'] += $hako->islands[$i]['market'] * 10;
      $stat['shell'] += $hako->islands[$i]['shell'];
	  $stat['pgoods'] += $hako->islands[$i]['p_goods'];
	  $stat['pmoneys'] += $hako->islands[$i]['p_moneys'];
    }

    // 島数カット
    $hako->islandNumber = $remainNumber;

    // バックアップターンであれば、書く前にrename
    if(($hako->islandTurn % $init->backupTurn) == 0) {
      $hako->backUp();
    }
    // ファイルに書き出し
    $hako->writeIslandsFile(-1);

	//統計データ書き出し
	$hako->writeStatistFile($stat);

	// ログ書き出し
    $this->log->flush();

    // 記録ログ調整
    $this->log->historyTrim();

  }
  //---------------------------------------------------
  // ログをまとめる
  //---------------------------------------------------
  function logMatome($island) {
    global $init;

    $sno = $island['seichi'];
    $point = "";
    if($sno > 0) {
      if($init->logOmit == 1) {
        $sArray = $island['seichipnt'];
        for($i = 0; $i < $sno; $i++) {
          $spnt = $sArray[$i];
          if($spnt == "") break;
          $x = $spnt['x'];
          $y = $spnt['y'];
          $point .= "($x, $y) ";
          if(!(($i+1)%20)) { $point .= "<br>　　　"; } // 全角空白３つ
        }
      }
      if($i > 1 || ($init->logOmit != 1)) { $point .= "の<strong>{$sno}ケ所</strong>"; }
    }
    if($point != "") {
      if(($init->logOmit == 1) && ($sno > 1)) {
        $this->log->landSucMatome($island['id'], $island['name'], '整地', $point);
      } else {
        $this->log->landSuc($island['id'], $island['name'], '整地', $point);
      }
    }
  }
  //---------------------------------------------------
  // コマンドフェイズ
  //---------------------------------------------------
  function doCommand(&$hako, &$island) {
    global $init;

    $comArray = &$island['command'];
    $command  = $comArray[0];
    Util::slideFront(&$comArray, 0);
    $island['command'] = $comArray;

    $kind   = $command['kind'];
    $target = $command['target'];
    $x      = $command['x'];
    $y      = $command['y'];
    $arg    = $command['arg'];

    $name = $island['name'];
    $id   = $island['id'];
    $land = $island['land'];
    $landValue = &$island['landValue'];
    $landKind = &$land[$x][$y];
    $lv   = $landValue[$x][$y];
    $cost = $init->comCost[$kind];
    $stonecost = $init->comSCost[$kind];
    $comName = $init->comName[$kind];
    $point = "({$x},{$y})";
    $landName = $this->landName($landKind, $lv);

    $prize = &$island['prize'];

    if($kind == $init->comDoNothing) {
      $island['absent']++;
      // 自動放棄
      if($island['absent'] >= $init->giveupTurn) {
        $comArray[0] = array (
          'kind'   => $init->comGiveup,
          'target' => 0,
          'x'      => 0,
          'y'      => 0,
          'arg'    => 0
          );
        $island['command'] = $comArray;
      }
    }else{
     $island['absent'] = 0;
    }
	if($kind == $init->comPubinvest){
	//公共投資のコストを実際の数字に直す
	if($island['invest'] < 52){
		$cost = (int)(($island['point'] / 30) * ($island['invest']/50));
	}else{
		$cost = ((int)($island['point'] / 30));
	}

	}
	if(($kind == $init->comEduinvest)&&($arg == 0)){
	//教育投資のコストを実際の数字に直す
		$cost = ((int)($island['point'] / 50));
	}
	if ($kind == $init->comCapital){
	//首都建設費を実際の数字に直す
			if ($arg <= $island['capital']){
			//ダウングレードならコスト0
			$cost = 0;
			$stonecost = 0;
			}else{
				//増築分だけ金がかかる
				$cost = $cost * ($arg - $island['capital']);
				$stonecost = $stonecost * ($arg - $island['capital']);
			}
	}
    $island['command'] = $comArray;
    // コストチェック
    if($cost >= 0) {
      // 金の場合
      if($island['money'] < $cost) {
        $this->log->NoGoods($id, $name, $comName, '資金');
        return 0;
      }

      // さらに資材チェック
      if($kind == $init->comMine) {
        // 鉱山整備の場合
        $minelv = ($lv % 10) + 1; // 鉱山レベル読み出し
        if($landKind == $init->landSteel) {
          // 鉄鉱山整備の場合
		  $minmat = 50;
		  $minmon = 500;
        } else if($landKind == $init->landUranium) {
		 // ウラン鉱山整備の場合
		  $minmat = 100;
		  $minmon = 500;

        } else if($landKind == $init->landCoal) {
          // 炭坑整備の場合
		  $minmat = 50;
		  $minmon = 100;
        } else if ($landKind == $init->landStonemine) {
          // 採石場整備の場合
		  $minmat = 100;
		  $minmon = 250;
        } else if ($landKind == $init->landSilver) {
          // 銀鉱整備の場合
		  $minmat = 150;
		  $minmon = 100;
        }
	    if(($island['material'] < ($minmat * $minelv)) ||
        	 ($island['money'] < ($minmon * $minelv))) {
            	$this->log->NoGoods($id, $name, $comName, '物資');
            	return 0;
           }
      } elseif($kind == $init->comBase) {
        if($arg == 2) {
          if(($island['material'] < 10) ||
             ($island['money'] < 10)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else if($arg == 1) {
          if(($island['material'] < 100) ||
             ($island['money'] < 500)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else {
          if(($island['material'] < 100) ||
             ($island['money'] < 300)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        }
      } elseif($kind == $init->comDbase) {
        if($arg == 2){
          if(($island['material'] < 10) ||
            ($island['money'] < 10)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else if ($arg == 1){
          if(($island['material'] < 300) ||
            ($island['money'] < 1500)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else {
          if(($island['material'] < 300) ||
            ($island['money'] < 500)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        }
      } elseif($kind == $init->comSFactory) {
        if($arg == 2){
          // 精製工場
          if(($island['material'] < 150) ||
            ($island['money'] < 150)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else if($arg == 1){
          // 軍事工場
          if(($island['material'] < 10) ||
            ($island['money'] < 50)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        } else {
          // 建材工場
          if(($island['material'] < 50) ||
            ($island['money'] < 150)) {
            $this->log->NoGoods($id, $name, $comName, '物資');
            return 0;
          }
        }
      } elseif(($kind == $init->comProcity)||
	  ($kind == $init->comSdbase)) {
        // 防災都市化の場合
        if($island['steel'] < $stonecost){
          $this->log->NoGoods($id, $name, $comName, '鋼鉄');
          return 0;
        }
      } elseif($kind == $init->comSendMonster) {
        // 怪獣派遣の場合 ($stonecostは燃料、鉄鋼)
        if(($island['fuel'] < -$stonecost) ||
          ($island['steel'] < -$stonecost)) {
          $this->log->NoGoods($id, $name, $comName, '物資');
          return 0;
        }
	  } elseif($kind == $init->comTrain) {
        // 怪獣派遣の場合 ($stonecostは砲弾)
        if($island['shell'] < -$stonecost) {
          $this->log->NoGoods($id, $name, $comName, '物資');
          return 0;
        }
      }elseif($kind == $init->comEisei ||
	  		   $kind == $init->comEiseimente ||
			   $kind == $init->comEiseiAtt ||
			   $kind == $init->comEiseiLzr)  {
        // 衛星関係の場合
        if($island['fuel'] < -$stonecost) {
          $this->log->NoGoods($id, $name, $comName, '燃料');
          return 0;
        }
	  } elseif($kind == $init->comReclaim || $kind == $init->comReclaim2 || $kind == $init->comMonument) {
        // 埋め立て、造成、記念碑の場合
        if($island['stone'] < -$stonecost) {
          // 石材チェック
          $this->log->NoGoods($id, $name, $comName, '石材');
          return 0;
        }
      } else {
        if($island['material'] < $stonecost) {
          // 建材チェック
          $this->log->NoGoods($id, $name, $comName, '建材');
          return 0;
        }
      }
   } elseif($cost < 0) {
     // 金の不要な場合

     if(($kind == $init->comMissileNM) ||
       ($kind == $init->comMissilePP) ||
       ($kind == $init->comMissileSPP) ||
       ($kind == $init->comMissileBT) ||
       ($kind == $init->comMissileSP) ||
       ($kind == $init->comMissileLD)) {
       // ミサイル発射の場合

       if($island['fuel'] < -$cost) {
         $this->log->NoGoods($id, $name, $comName, '燃料');
         return 0;
       }
     }
     if($kind == $init->comMkResource) {
       // 資源採掘の場合

       if($island['fuel'] < (-$cost) ) {
         $this->log->NoGoods($id, $name, $comName, '燃料');
         return 0;
       }
     }
   }

    $returnMode = 1;
    switch($kind) {
    case $init->comPrepare:
    case $init->comPrepare2:
      // 整地、地ならし
      if(($landKind == $init->landSea) ||
         ($landKind == $init->landPoll) ||
         ($landKind == $init->landSdefence) ||
         ($landKind == $init->landSeaCity) ||
         ($landKind == $init->landSfarm) ||
         ($landKind == $init->landNursery) ||
         ($landKind == $init->landOil) ||
         ($landKind == $init->landPort) ||
         ($landKind == $init->landMountain) ||
		 ($landKind == $init->landnMountain) ||
         ($landKind >= 50 && $landkind < 55) ||
         ($landKind == $init->landMonster) ||
         ($landKind == $init->landSleeper) ||
         ($landKind == $init->landZorasu)) {
        // 海、汚染土壌、海底基地、油田、山、山地、鉱山、海底都市、海底農場、養殖場、油田、港、怪獣は整地できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }
      // 石は整地・地ならしで金に、卵は食料になる
      if($landKind == $init->landMonument) {
        if((33 < $lv) && ($lv < 40)) {
          // 金になる
          $island['money'] += 9999;
        } elseif((39 < $lv) && ($lv < 44)) {
          // 食料になる
          $island['food'] += 5000;
        }
      }
      // 目的の場所を平地にする
      $land[$x][$y] = $init->landPlains;
      $landValue[$x][$y] = 0;
      $this->log->landSuc($id, $name, '整地', $point);

      if(Util::random(100) < 3) {
        // 何かの卵発見
        $this->log->EggFound($id, $name, $comName, $point);
        $land[$x][$y] = $init->landMonument;
        $landValue[$x][$y] = 40 + Util::random(3);
      }

      // 金を差し引く
      $island['money'] -= $cost;

      if($kind == $init->comPrepare2) {
        // 地ならし
        $island['prepare2']++;

        // ターン消費せず
        $returnMode = 0;
      } else {
        // 整地なら、埋蔵金の可能性あり
        if(Util::random(1000) < $init->disMaizo) {
          $v = 50 + Util::random(451);
          $island['money'] += $v;
          $this->log->maizo($id, $name, $comName, $v);
        }
        $returnMode = 1;
      }
      break;
    case $init->comReclaim:
    case $init->comReclaim2:
      // 埋め立て
      if(!(($landKind == $init->landSea) && ($lv < 2)) &&
         ($landKind != $init->landPort) &&
         ($landKind != $init->landNursery) &&
         ($landKind != $init->landSfarm) &&
         ($landKind != $init->landSsyoubou) &&
         ($landKind != $init->landSeaCity) &&
         ($landKind != $init->landSdefence) &&
		 ($landKind != $init->landWaste)) {
        // 海、海底基地、港、海底消防署、海底防衛施設、海底都市、海底農場、養殖場、荒地しか埋め立てできない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 周りに陸があるかチェック
      $seaCount =
        Turn::countAround($land, $x, $y, $init->landSea, 7) +
         Turn::countAround($land, $x, $y, $init->landSeaCity, 7) +
          Turn::countAround($land, $x, $y, $init->landOil, 7) +
           Turn::countAround($land, $x, $y, $init->landNursery, 7) +
            Turn::countAround($land, $x, $y, $init->landSfarm, 7) +
             Turn::countAround($land, $x, $y, $init->landPort, 7) +
              Turn::countAround($land, $x, $y, $init->landSdefence, 7);

      if($seaCount == 7) {
        // 全部海だから埋め立て不能
        $this->log->noLandAround($id, $name, $comName, $point);

        $returnMode = 0;
        break;
      }

	switch ($landKind) {
	  case $init->landSea:
	      if($lv == 1) {
	        // 浅瀬の場合
	        // 目的の場所を荒地にする
	        $land[$x][$y] = $init->landWaste;
	        $landValue[$x][$y] = 0;
	        $this->log->landSuc($id, $name, $comName, $point);
	        $island['area']+=0.1;

	        if($seaCount <= 4) {
	          // 周りの海が3ヘックス以内なので、浅瀬にする

	          for($i = 1; $i < 7; $i++) {
	            $sx = $x + $init->ax[$i];
	            $sy = $y + $init->ay[$i];

	            // 行による位置調整
	            if((($sy % 2) == 0) && (($y % 2) == 1)) {
	              $sx--;
	            }

	            if(($sx < 0) || ($sx >= $init->islandSize) ||
	               ($sy < 0) || ($sy >= $init->islandSize)) {
	            } else {
	              // 範囲内の場合
	              if($land[$sx][$sy] == $init->landSea) {
	                $landValue[$sx][$sy] = 1;
	              }
	            }
	          }
	        }
	      } else {
	        // 海なら、目的の場所を浅瀬にする
	        $land[$x][$y] = $init->landSea;
	        $landValue[$x][$y] = 1;
	        $this->log->landSuc($id, $name, $comName, $point);
	      }
	 break;

	case $init->landWaste:
	case $init->landPlains:
	//荒地の場合
	//目的地を山地にする
	$land[$x][$y] = $init->landnMountain;
	$this->log->landSuc($id, $name, $comName, $point);
	break;

	case $init->landPort:
	//港の場合
	//目的地を荒地にする
	$land[$x][$y] = $init->landWaste;
    $landValue[$x][$y] = 0;
    $this->log->landSuc($id, $name, $comName, $point);
    $island['area']+=0.1;
	break;

	default:
	//浅瀬、海、荒地、港じゃない場合
	//目的地を浅瀬にする
    $land[$x][$y] = $init->landSea;
    $landValue[$x][$y] = 1;
    $this->log->landSuc($id, $name, $comName, $point);
	}

      // 金を差し引く
      $island['money'] -= $cost;
      $island['stone'] -= -$stonecost;
      if($kind == $init->comReclaim2) {
        // 造成はターン消費せず
        $island['reclaim2']++;
        $returnMode =  0;
      } else {
         // 埋め立てはターン消費
         $returnMode =  1;
      }
      break;

    case $init->comDestroy:
    case $init->comDestroy2:
      // 掘削
      if((($landKind == $init->landSea) && ($lv > 1)) ||
         ($landKind == $init->landPoll) ||
         ($landKind == $init->landPort) ||
         ($landKind == $init->landSeaCity) ||
         ($landKind == $init->landSfarm) ||
         ($landKind == $init->landSdefence) ||
         ($landKind == $init->landNursery) ||
         ($landKind == $init->landMonster) ||
         ($landKind == $init->landSleeper) ||
         ($landKind == $init->landZorasu)) {
        // 海底基地、汚染土壌、港、海底防衛施設、海底都市、海底農場、養殖場、怪獣は掘削できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 目的の場所を海にする。山なら荒地に。浅瀬なら海に。
      if(($landKind == $init->landMountain) ||
	  	 ($landKind == $init->landnMountain) ||
         ($landKind >= 50 && $landkind < 55)) {
        $land[$x][$y] = $init->landWaste;
        $landValue[$x][$y] = 0;
      } elseif($landKind == $init->landSea) {
        $landValue[$x][$y] = 0;
      } else {
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 1;
        $island['area']-=0.1;
      }
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      if($kind == $init->comDestroy2) {
        // 連続はターン消費せず
        $island['destroy2']++;
        $returnMode =  0;
      } else {
         // 埋め立てはターン消費
         $returnMode =  1;
      }
      break;

    case $init->comSellTree:
      // 伐採
      if($landKind != $init->landForest) {
        // 森以外は伐採できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 目的の場所を平地にする
      $land[$x][$y] = $init->landPlains;
      $landValue[$x][$y] = 0;
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      // 伐採して木材を得る
      $island['wood'] += $lv * 6;

      $returnMode = 0;
      break;

    case $init->comVein:
      // 鉱脈探査
      if($landKind != $init->landMountain) {
        // 山以外は鉱脈探査できない
        $this->log->LandFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }
      $p = Util::random(100); // 乱数
      $k = (int)($arg / 10); // 調査の種類
      $a = $arg % 10; // 調査のレベル
      if($a == 0) {$a = 10;};
         $a = min($a, (int)($island['money'] / 50));

      if(($k == 0) && ($p < ($a * 1))) {
        // ウラン鉱山見つかる
        $this->log->Found($id, $name, $point, $comName, 'ウラン鉱山');
        $land[$x][$y] = $init->landUranium;
        $landValue[$x][$y] = 0;
      } else if(($k == 1) && ($p < ($a * 2))) {
        // 銀鉱山見つかる
        $this->log->Found($id, $name, $point, $comName, '銀鉱山');
        $land[$x][$y] = $init->landSilver;
        $landValue[$x][$y] = 0;
      } else if(($k == 2) && ($p < ($a * 5))) {
        // 鉄鉱山見つかる
        $this->log->Found($id, $name, $point, $comName, '鉄鉱山');
        $land[$x][$y] = $init->landSteel;
        $landValue[$x][$y] = 0;
      } else if(($k == 3) && ($p < ($a * 10))) {
        // 採石場見つかる
        $this->log->Found($id, $name, $point, $comName, '良質の石脈');
        $land[$x][$y] = $init->landStonemine;
        $landValue[$x][$y] = 0;
      } else if(($k == 4) && ($p < ($a * 20))) {
        // 炭坑見つかる
        $this->log->Found($id, $name, $point, $comName, '良質の石炭');
        $land[$x][$y] = $init->landCoal;
        $landValue[$x][$y] = 0;
      } else {
        // はずれ
        $this->log->MineFail($id, $name, $point, $comName);
      }

      // 金を差し引く
      $island['money'] -= $a * $cost;

      $returnMode = 1;
      break;

    case $init->comPort:
      // 港
      if(!
	 (($landKind == $init->landSea && $lv == 1)||
	  ($landKind == $init->landPort && $lv == 0))){
        // 浅瀬・港Lv0以外には建設不可
        $this->log->LandFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }
      $seaCount = Turn::countAround($land, $x, $y, $init->landSea, 7);
      if($seaCount < 1){
        // 周囲に最低1Hexの海も無い場合も建設不可
        $this->log->NoSeaAround($id, $name, $comName, $point);
        $returnMode = 0;
      break;
      }
      if($seaCount == 7){
        // 周りが全部海なので港は建設できない
        $this->log->NoLandAround($id, $name, $comName, $point);
        $returnMode = 0;
        break;
      }
      $land[$x][$y] = $init->landPort;
      $landValue[$x][$y] = 0;
	  if($lv == 0){
  	    $landValue[$x][$y] = 1;
	  }
      $this->log->LandSuc($id, $name, $comName, $point);
      // 金を差し引く
      $island['money'] -= $cost;
      $island['material'] -= $stonecost;
      $returnMode = 1;
      break;

    case $init->comMakeShip:
      // 造船
      if($island['port'] <= 0){
        // 港がないと失敗
        $this->log->NoPort($id, $name, $comName, $point);
        $returnMode = 0;
        break;
      }
      if(!($landKind == $init->landSea &&  $lv <= 1)){
        // 船を設置する場所が海で無い場合は失敗
        $this->log->NoSea($id, $name, $comName, $point);
        $returnMode = 0;
        break;
      }
      if($init->maxShip == ($island['ship']['passenger'] + $island['ship']['fishingboat'] +
        $island['ship']['tansaku'] + $island['ship']['senkan'])){
        // 船が最大所有量を超えていた場合、却下
        $this->log->maxShip($id, $name, $comName, $point);
        $returnMode = 0;
        break;
      }
      $arg += 2;
      if(!Util::checkShip($landKind, $arg)) $arg = 2; // 船で使用できる数値範囲か？
      $land[$x][$y] = $init->landSea;
      $landValue[$x][$y] = $arg;
      $this->log->LandSuc($id, $name, $init->shipName[$arg-2]."の".$comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      $island['steel'] -= $stonecost;
      $returnMode = 1;
      break;

    case $init->comShipBack:
      //船破棄

      //対象が海賊船の場合
      if(!($landKind == $init->landSea && $lv >= 2 && $lv < 255)) {
        $this->log->landFail($id, $name, $comName, $this->landName($landKind, $lv), $point);
        $returnMode = 0;
        break;
      }
      $this->log->ComeBack($id, $name, $comName, $this->landName($landKind, $lv), $point);
      $land[$x][$y] = $init->landSea;
      $landValue[$x][$y] = 0;

      // 金を差し引く
      $island['money'] -= $cost;
      $island['material'] -= $stonecost;
      $returnMode = 1;
      break;

    case $init->comPlant:
    case $init->comFarm:
    case $init->comNursery:
    case $init->comFactory:
    case $init->comHatuden:
    case $init->comMarket:
    case $init->comOild:
    case $init->comSFactory:
    case $init->comBase:
    case $init->comMonument:
    case $init->comNewtown:
    case $init->comPower:
    case $init->comDbase:
    case $init->comPark:
    case $init->comFusya:
	case $init->comMyhome:
	case $init->comNPark:
	case $init->comSecpol:
	case $init->comFBase:
      // 地上建設系
      if(!
         (($landKind == $init->landPlains) ||
          ($landKind == $init->landTown)   ||
          ((($landKind == $init->landFFactory) ||
            ($landKind == $init->landFactory) ||
            ($landKind == $init->landSFactory) ||
			($landKind == $init->landSeeCity) ||
            ($landKind == $init->landMFactory))&& ($kind == $init->comPower)) ||
          (($landKind == $init->landMonument) && ($kind == $init->comMonument)) ||
          (($landKind == $init->landFarm)     && ($kind == $init->comFarm))     ||
          (($landKind == $init->landlandSea) && ($lv == 1) && ($kind == $init->comNursery)) ||
          (($landKind == $init->landNursery) && ($kind == $init->comNursery)) ||
          (($landKind == $init->landFactory) && ($kind == $init->comFactory)) ||
          (($landKind == $init->landSFactory) && ($kind == $init->comSFactory)) ||
          (($landKind == $init->landMFactory) && ($kind == $init->comSFactory)) ||
          (($landKind == $init->landHatuden)  && ($kind == $init->comHatuden))  ||
          (($landKind == $init->landOil) && ($kind == $init->comOild)) ||
          (($landKind == $init->landWaste) && ($kind == $init->comOild)) ||
          (($landKind == $init->landFFactory) && ($kind == $init->comSFactory)) ||
          (($landKind == $init->landMarket) && ($kind == $init->comMarket)) ||
          (($landKind == $init->landPark)  && ($kind == $init->comPark))  ||
          (($landKind == $init->landFusya)  && ($kind == $init->comFusya))  ||
          (($landKind == $init->landHDefence)  && ($kind == $init->comDbase)) ||
		  (($landKind == $init->landCapital)  && ($kind == $init->comCapital)) ||
          (($landKind == $init->landDefence)  && ($kind == $init->comDbase)) ||
		  (($landKind == $init->landForest)  && ($kind == $init->comNPark)))) {
        // 不適当な地形
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      if(($landKind == $init->landPlains) && ($kind == $init->comOild)){
        $this->log->LandFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }
      if((($landKind == $init->landPlains) || ($landKind == $init->landTown)) && ($kind == $init->comPower)){
        $this->log->LandFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

      // 種類で分岐
      switch($kind){

	      case $init->comPlant:
	        // 目的の場所を森にする。
	        $land[$x][$y] = $init->landForest;
	        $landValue[$x][$y] = 1; // 木は最低単位
	        $this->log->PBSuc($id, $name, $comName, $point);
	        break;

	      case $init->comOild:
	        // 目的の場所を油田(候補地)にする。
	        if($landKind != $init->landOil) {
	          $land[$x][$y] = $init->landOil;
	          $landValue[$x][$y] = 1; // 候補地
	        } else {
	          $landValue[$x][$y] ++;
	          if($landValue[$x][$y] > 10){
	            $landValue[$x][$y] = 10;
	          }
	          $this->log->landSuc($id, $name, $comName, $point);
	        }
	        break;

	      case $init->comBase:
	        // 目的の場所をミサイル基地にする。
	        if($arg == 2) {
	          // 目的の場所をハリボテにする
	          $land[$x][$y] = $init->landHaribote;
	          $landValue[$x][$y] = 0;
	          $this->log->landSuc($id, $name, $comName, $point);
	          $island['material'] -= 10;
	          $island['money'] -= 10;
	          $returnMode = 0;
	        } else if($arg == 1) {
	          // 目的の場所を偽装ミサイル基地にする。
	          $land[$x][$y] = $init->landHBase;
	          $landValue[$x][$y] = 0; // 経験値0
	          $this->log->PBSuc($id, $name, $comName, $point);
	          $island['material'] -= 100;
	          $island['money'] -= 500;
	        } else {
	          $land[$x][$y] = $init->landBase;
	          $landValue[$x][$y] = 0; // 経験値0
	          $this->log->landSuc($id, $name, $comName, $point);
	          $island['material'] -= 100;
	          $island['money'] -= 300;
	        }
	        break;

		case $init->comFBase:
				// 目的の場所を他国軍駐屯地にする
		      // ターゲット取得
		      $tn = $hako->idToNumber[$target];
		      $tIsland = $hako->islands[$tn];
		      $tName = $tIsland['name'];
		      if($tn != 0 && empty($tn)) {
		        // ターゲットがすでにない
		        $this->log->msNoTarget($id, $name, $comName);
    		    $island['money'] += $cost;
  			    $island['material'] += $stonecost;
		        break;
		      }
			$tIsland['sfarmy']++;
	        $land[$x][$y] = $init->landFBase;
	        $landValue[$x][$y] = $target;

	        $this->log->landSuc($id, $name, $comName, $point);
			$this->log->FBSuc($id, $name, $point,$target);
	        break;

		case $init->comNPark:
		// 目的の場所を国立公園にする
	        $land[$x][$y] = $init->landNPark;
	        $landValue[$x][$y] = 1;
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comNewtown:
	        // 目的の場所をニュータウンにする
	        $land[$x][$y] = $init->landNewtown;
	        $landValue[$x][$y] = 1;
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comPark:
	        // 目的の場所を遊園地にする
	        $land[$x][$y] = $init->landPark;
	        if($arg > 4) { $arg = 4; }
	        $landValue[$x][$y] = $arg;
	        $island['park']++;
	        $this->log->LandSuc($id, $name, $comName, $point);
	        break;

	      case $init->comFusya:
	        // 目的の場所を風車にする
	        $land[$x][$y] = $init->landFusya;
	        $landValue[$x][$y] = 0;
	        $this->log->LandSuc($id, $name, $comName, $point);
	        break;

	      case $init->comSFactory:
	        if($arg == 2){
			  	if ($landKind == $init->landSactory || $landKind == $init->landMFactory || $landKind == $init->landFFactory) {
					//軍事工場or建材工場or畜産場だったら
					$this->log->landFail($id, $name, $comName, $landName, $point);
					break;
				} else {
		            // 目的の場所を精製工場に
		            $land[$x][$y] = $init->landFFactory;
		            $landValue[$x][$y] = 25; // 規模 = 25(*10)
	            $island['material'] -= 50;
	            $island['money'] -= 150;
	            $this->log->landSuc($id, $name, '畜産場', $point);
				}
	        } elseif($arg == 1){
	          if($landKind == $init->landSFactory) {
	            // すでに軍事工場の場合
	            if($landValue[$x][$y] < 142) {
	              $landValue[$x][$y] += 10;
	            }
	          } else {
			  	if ($landKind == $init->landFFactory || $landKind == $init->landMFactory) {
					//精製工場or建材工場だったら
					$this->log->landFail($id, $name, $comName, $landName, $point);
					break;
				} else {
		            // 目的の場所を砲弾工場に
		            $land[$x][$y] = $init->landSFactory;
		            $landValue[$x][$y] = 10; // 規模 = 10メガトン
					}
	          }
	            $island['material'] -= 10;
	            $island['money'] -= 50;
	            $this->log->landSuc($id, $name, '軍事工場建設', $point);
	        } else {
	          if($landKind == $init->landMFactory) {
	            // すでに建材工場の場合
	            if($landValue[$x][$y] < 172 ) {
	              $landValue[$x][$y] += 30;
	            }
	          } else {
			  	if ($landKind == $init->landFFactory || $landKind == $init->landSactory) {
					//精製工場or軍事工場だったら
					$this->log->landFail($id, $name, $comName, $landName, $point);
					break;
				} else {
		            // 目的の場所を建材工場に
		            $land[$x][$y] = $init->landMFactory;
		            $landValue[$x][$y] = 50; // 規模 = 50万トン
					}
	          }
	            $island['material'] -= 50;
	            $island['money'] -= 150;
	            $this->log->landSuc($id, $name, '建材工場建設', $point);
	        }
	        break;

	      case $init->comFarm:
	        // 農場
	        if($landKind == $init->landFarm) {
	          // すでに農場の場合
	          $landValue[$x][$y] += 5; // 規模 + 5000人
	          if($landValue[$x][$y] > 50) {
	            $landValue[$x][$y] = 50; // 最大 50000人
	          }
	        } else {
	          // 目的の場所を農場に
	          $land[$x][$y] = $init->landFarm;
	          $landValue[$x][$y] = 10; // 規模 = 10000人
	        }
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comNursery:
	        // 養殖場
	        if($landKind == $init->landNursery) {
	          // すでに養殖場の場合
	          $landValue[$x][$y] += 2; // 規模 + 2000人
	          if($landValue[$x][$y] > 30) {
	            $landValue[$x][$y] = 30; // 最大 50000人
	          }
	        } elseif(($landKind == $init->landSea) && ($lv == 1))  {
	          // 目的の場所を養殖場に
	          $land[$x][$y] = $init->landNursery;
	          $landValue[$x][$y] = 4; // 規模 = 10000人
	        } else {
	          // 不適当な地形
	          $this->log->landFail($id, $name, $comName, $landName, $point);
	          return 0;
	        }
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comMarket:
	        // 市場
	        if($landKind == $init->landMarket) {
	          // すでに市場の場合
	          $landValue[$x][$y] += 10; // 規模 + 1000人
	          if($landValue[$x][$y] > 100) {
	            $landValue[$x][$y] = 100; // 最大 10000人
	          }
	        } else {
	          // 目的の場所を市場に
	          $land[$x][$y] = $init->landMarket;
	          $landValue[$x][$y] = 10; // 規模 = 10000人
	        }
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comFactory:
	        // 工場
	        if($landKind == $init->landFactory) {
	          // すでに工場の場合
	          if($landValue[$x][$y] < 112) {
	            $landValue[$x][$y] += 10;
	          }
	        } else {
	          // 目的の場所を工場に
	          $land[$x][$y] = $init->landFactory;
	          $landValue[$x][$y] = 30; // 規模 = 30000人
	        }
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comHatuden:
	        // 発電所
	        if($landKind == $init->landHatuden) {
	          // すでに発電所の場合
	          $landValue[$x][$y] += 40; // 規模 + 40000kw
	          if($landValue[$x][$y] > 400) {
	            $landValue[$x][$y] = 400; // 最大 400000kw
	          }
	        } else {
	          // 目的の場所を発電所に
	          $land[$x][$y] = $init->landHatuden;
	          $landValue[$x][$y] = 40; // 規模 = 40000kw
	        }
	        $this->log->landSuc($id, $name, $comName, $point);
	        break;

	      case $init->comDbase:
	        // 防衛施設
	        if(($landKind == $init->landDefence) && ($arg == 0)) {
	          // すでに防衛施設の場合
	          $landValue[$x][$y] += 1; // 自レベルアップ
	          $this->log->LevelUp($id, $name, $landName, $point);
	          if($landValue[$x][$y] > 3) { // 自爆装置セット
	            $this->log->bombSet($id, $name, $landName, $point);
	          }
	        } else if(($landKind == $init->landHDefence) && ($arg == 1)) {
	          // すでに防衛施設の場合
	          $landValue[$x][$y] += 1; // 自レベルアップ
	          $this->log->LevelUp($id, $name, $landName, $point);
	          if($landValue[$x][$y] > 3) { // 自爆装置セット
	            $this->log->bombSet($id, $name, $landName, $point);
	          }
	        } else {
	          // 目的の場所を防衛施設に
	          if($arg == 2){
	            $land[$x][$y] = $init->landHaribote;
	            $landValue[$x][$y] = 1;
	            $island['material'] -= 10;
	            $island['money'] -= 10;
	            $this->log->landSuc($id, $name, $comName, $point);
	            $returnMode = 0;
	          } else if ($arg == 1){
	            $land[$x][$y] = $init->landHDefence;
	            $landValue[$x][$y] = $init->dBaseInitLv;
	            $this->log->PBSuc($id, $name, $comName, $point);
	            $island['material'] -= 300;
	            $island['money'] -= 1500;
	          } else {
	            $land[$x][$y] = $init->landDefence;
	            $landValue[$x][$y] = $init->dBaseInitLv;
	            $this->log->landSuc($id, $name, $comName, $point);
	            $island['material'] -= 300;
	            $island['money'] -= 50;
	          }
	        }
	        break;

	      case $init->comMonument:
	        // 記念碑
	        if($landKind == $init->landMonument) {
	          // すでに記念碑の場合
	        // 不適当な地形
	        $this->log->landFail($id, $name, $comName, $landName, $point);

	        $returnMode = 0;
	        break;

	        } else {
	          // 目的の場所を記念碑に
	          $land[$x][$y] = $init->landMonument;
	          if($arg >= $init->monumentNumber) {
	            $arg = 0;
	          }
	          $landValue[$x][$y] = $arg;
	          $this->log->landSuc($id, $name, $comName, $point);
	          $island['stone'] -= -$stonecost;
	          $island['material'] += $stonecost;
	        }
	        break;

	     case $init->comMyhome:
			//行政府
	       if($landKind == $init->landMyhome) {
	         // すでに行政府の場合
				$this->log->landFail($id, $name, $comName, $landName, $point);
	           $returnMode = 0;
    		   $island['money'] += $cost;
  			   $island['material'] += $stonecost;
	       } else {
	         // 目的の場所をマイホームに
	         if($island['polit'] != 0) {
	            // すでに議事堂or秘密警察がある
	            $this->log->IsFail($id, $name, $comName, '行政府');
	            $returnMode = 0;
    		    $island['money'] += $cost;
  			    $island['material'] += $stonecost;
		       break;
			 }
	         $land[$x][$y] = $init->landMyhome;
	         $this->log->landSuc($id, $name, $comName, $point);
	       }
	       break;

	     case $init->comSecpol:
			//秘密警察
	       if($landKind == $init->landSecpol) {
	         // すでに秘密警察の場合
				$this->log->landFail($id, $name, $comName, $landName, $point);
	           $returnMode = 0;
    		   $island['money'] += $cost;
  			   $island['material'] += $stonecost;
	       } else {

	         if($island['polit'] != 0) {
	            // すでに議事堂or秘密警察がある
	            $this->log->IsFail($id, $name, $comName, '行政府');
	            $returnMode = 0;
    		    $island['money'] += $cost;
  			    $island['material'] += $stonecost;
		       break;
	         }
	         $land[$x][$y] = $init->landSecpol;
		     $landValue[$x][$y] = 0;
	         $this->log->landSuc($id, $name, $comName, $point);
	       }

	       break;

	  }//switchここまで
      // 金を差し引く
      $island['money'] -= $cost;
      $island['material'] -= $stonecost;

      // 回数付きなら、コマンドを戻す
      if(($kind == $init->comFarm) ||
         ($kind == $init->comSfarm) ||
         ($kind == $init->comNursery) ||
         ($kind == $init->comFactory) ||
         ($kind == $init->comHatuden) ||
         ($kind == $init->comMarket)) {
        if($arg > 1) {
          $arg--;
          Util::slideBack($comArray, 0);
          $comArray[0] = array (
            'kind'   => $kind,
            'target' => $target,
            'x'      => $x,
            'y'      => $y,
            'arg'    => $arg
            );
        }
      }
      $returnMode = 1;
      break;


    case $init->comDisuse:
      // 鉱山廃止
      if($landKind < 25 || $landkind > 30) {
        // 鉱山以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      $land[$x][$y] = $init->landMountain;
      $landValue[$x][$y] = 0;
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;

      $returnMode = 0;
      break;

    case $init->comMine:
      // 鉱山整備
      if($landKind < 25 || $landkind > 30) {
        // 鉱山以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      if($landValue[$x][$y] % 10 != 4) {
        $minelv = ($lv % 10) + 1; // 鉱山レベル読み出し
        if ($landKind == $init->landSteel) {
          // 鉄鉱山整備の場合
          // 金、建材を差し引く
          $island['money'] -= 500 * $minelv;
          $island['material'] -= 50 * $minelv;
        } else if($landKind == $init->landUranium) {
          // ウラン鉱山整備の場合
          // 金、建材を差し引く
          $island['money'] -= 500 * $minelv;
          $island['material'] -= 100 * $minelv;
        } else if($landKind == $init->landCoal) {
          // 炭坑整備の場合
          // 金、建材を差し引く
          $island['money'] -= 100 * $minelv;
          $island['material'] -= 50 * $minelv;
        } else if($landKind == $init->landStonemine) {
          // 採石場整備の場合
          // 金、建材を差し引く
          $island['money'] -= 250 * $minelv;
          $island['material'] -= 100 * $minelv;
        } else if($landKind == $init->landSilver) {
          // 金鉱整備の場合
          // 金、建材を差し引く
          $island['money'] -= 100 * $minelv;
          $island['material'] -= 150 * $minelv;
        }

        $landValue[$x][$y]++;
        $this->log->landSuc($id, $name, $comName, $point);

        // 回数付きなら、コマンドを戻す
        if($arg > 1) {
          $arg--;
          Util::slideBack(&$comArray, 0);
          $comArray[0] = array (
            'kind'   => $kind,
            'target' => $target,
            'x'      => $x,
            'y'      => $y,
            'arg'    => $arg,
            );
        }
        $returnMode = 1;
      }
      break;

    case $init->comSdbase:
      // 海底防衛施設
      if(($landKind != $init->landSea) || ($lv != 0)){
        // 海以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }
      // 目的の場所を防衛艦隊に
      $land[$x][$y] = $init->landSdefence;
      $landValue[$x][$y] = 2;
      $this->log->landSuc($id, $name, $comName, $point);
      // 金を差し引く
      $island['money'] -= $cost;
      $island['steel'] -= $stonecost;
      $returnMode = 1;
      break;

    case $init->comProcity:
      // 防災都市
      if(($landKind != $init->landTown) || ($lv < 150)){
        // 町以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

      $land[$x][$y] = $init->landProcity;
	  $lv = 100;
      $landValue[$x][$y] = 100; // 経験値0
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      $island['steel'] -= $stonecost;
      $returnMode = 1;
      break;

	case $init->comCapital:
        // 目的の場所を首都にする
		//規模の調整
			if ($arg > 5){
				//規模は5まで
				$arg = 5;
			}elseif ($arg == 0){
				$arg = 1;
			}
		if ($island['capital'] > 0){
		//既に首都がある場合
			if(!$landValue[$x][$y] == $init->landCapital){
			//そこの土地が首都ではないとき
	            $this->log->IsFail($id, $name, $comName, '首都');
	            $returnMode = 0;
		        break;
			}
			}else{
				//以下新築首都の場合
		        $land[$x][$y] = $init->landCapital;
		        $landValue[$x][$y] = 10; // 人口
			}
			$island['capital'] = $arg;
	        $this->log->landSuc($id, $name, $comName, $point);
		      $island['money'] -= $cost;
		      $island['material'] -= $stonecost;
		      $returnMode = 1;
	        break;

     case $init->comSeeCity:
	  if($island['capital'] < 2){
			//観光都市は首都（2）が必要
        $this->log->NoAny($id, $name, $comName, "首都レベルが低い");
        $returnMode = 0;
		break;
      }
		//観光都市
	if((Turn::countAround($land, $x, $y, $init->landnMountain, 7) +
        Turn::countAround($land, $x, $y, $init->landForest, 7) +
        Turn::countAround($land, $x, $y, $init->landSeaSide, 7) +
        Turn::countAround($land, $x, $y, $init->landSea, 7) +
        Turn::countAround($land, $x, $y, $init->landNPark, 7)) > 1) {
		//周囲に森、山地、浅瀬がないと作れない
        $land[$x][$y] = $init->landSeeCity;
        $landValue[$x][$y] = 1;
        $this->log->landSuc($id, $name, $comName, $point);
	      $island['money'] -= $cost;
	      $island['material'] -= $stonecost;
	      $returnMode = 1;

		} else{
		$this->log->landFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;

		}
		break ;

	case $init->comIndCity:
      // 工業都市
      if(($landKind != $init->landNewtown) || (($landKind != $init->landNewtown) && ($lv < 149))){
        // ニュータウン以外には作れない
        $this->log->JoFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

	  if(Turn::ChkCapLevel($init->comIndCity,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
        // 目的の場所を工業都市にする
        $land[$x][$y] = $init->landIndCity;
        $landValue[$x][$y] = 1;
        $this->log->landSuc($id, $name, $comName, $point);

        // 金を差し引く
        $island['money'] -= $cost;
        $island['material'] -= $stonecost;
        break;

    case $init->comBigtown:
      // 現代化
      if(($landKind != $init->landNewtown) || (($landKind != $init->landNewtown) && ($lv < 149))){
        // ニュータウン以外には作れない
        $this->log->JoFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

	  if(Turn::ChkCapLevel($init->comBigtown,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      $townCount =
        Turn::countAround($land, $x, $y, $init->landTown, 7) +
          Turn::countAround($land, $x, $y, $init->landNewtown, 7) +
            Turn::countAround($land, $x, $y, $init->landIndCity, 7) +
			  Turn::countAround($land, $x, $y, $init->landSeeCity, 7) +
			  	Turn::countAround($land, $x, $y, $init->landPort, 7) +
			  		Turn::countAround($land, $x, $y, $init->landProcity, 7) ;
	  $checkAround =
            Turn::countAround($land, $x, $y, $init->landBigtown, 19);

      if($townCount < 5) {
        // 都市数が足りないから現代化できない
        $this->log->JoFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }
	  if($checkAround >= 1) {
	  // 2ヘックス以内に現代都市があるから現代化できない
        $this->log->JoFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
	  }

      $land[$x][$y] = $init->landBigtown;
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      $island['material'] -= $stonecost;
      $returnMode = 1;
      break;

	case $init->comIndPlan:
	 if(($arg < 3)&&($arg !== $island['indnum'])&&($island['edinv']/30 >= $arg)){
	 	 $island['indnum'] = $arg;
	 	 $this->log->InvestSuc($id, $name, $comName);
		 // コスト無し
		 $returnMode = 1;
		 }else{
	 	 $this->log->InvestFail($id, $name, $comName);
		 $returnMode = 0;
		 }
		 break;

	case $init->comPubinvest:
	  if(Turn::ChkCapLevel($init->comPubinvest,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
		if ($island['point'] >= $init->BaseHappiDemand[2]){
		//先進国の場合
		$up = 2;
		}else{
		$up = 3;
		}
		 if ($island['invest'] <= (100 - $up)){
			 $island['invest'] += $up;
			 $this->log->InvestSuc($id, $name, $comName);
			 // 金を差し引く
			 $island['money'] -= $cost;
			 $island['material'] -= $stonecost;
			 $returnMode = 1;
			 break;
		 }
	 $this->log->InvestFail($id, $name, $comName);
	 $returnMode = 0;
	 break;

	case $init->comEduinvest:
	  if(Turn::ChkCapLevel($init->comEduinvest,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
	 if($arg == 0){
		 if ($island['edinv'] <= 98){
			 $island['edinv'] += 5;
			 $this->log->InvestSuc($id, $name, $comName);
			 // 金を差し引く
			 $island['money'] -= $cost;
			 $returnMode = 1;
			 break;
		 }
	 }else{
		 if ($island['edinv'] >= 3){
			 $island['edinv'] -= 3;
			 $this->log->InvestDel($id, $name, $comName);
			 $returnMode = 1;
			 break;
		 }
	 }
	 $this->log->InvestFail($id, $name, $comName);
	 $returnMode = 0;
	 break;

	case $init->comSocPlan:
	  if(Turn::ChkCapLevel($init->comSocPlan,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
	 $arg = $arg + 1;
	if(abs($arg-$island['soclv']) > 11){
	 $this->log->InvestFail($id, $name, $comName);
	 $returnMode = 0;
	 break;

	}else{
		$island['soclv'] = $arg;
		$island['money'] -= $cost;
		$this->log->InvestSuc($id, $name, $comName);
		$returnMode = 1;
		 break;
	}

    case $init->comEisei:
      // 人工衛星打ち上げ
	  if(Turn::ChkCapLevel($init->comEisei,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      if($arg > 5) $arg = 0;
      $value = ($arg + 1) * $cost;
      // 気象, 観測, 迎撃, 軍事, 防衛, イレ
      $rocket = array(1, 1, 2, 2, 3, 4);
      $tech   = array(10, 40, 100, 250, 300, 500);
      $failp  = array(700, 500, 600, 400, 200, 3000);
      $failq  = array(100, 100, 10, 10, 10, 1);

      if($island['m23'] < $rocket[$arg]) {
        // ロケットが$rocket以上ないとダメ
        $this->log->NoAny($id, $name, $comName, "ロケットが足りない");
        $returnMode = 0;
        break;
      } elseif($island['rena'] < $tech[$arg]) {
        // 軍事技術Lv$tech以上ないとダメ
        $this->log->NoAny($id, $name, $comName, "軍事技術が足りない");
        $returnMode = 0;
        break;
      } elseif($island['money'] < $value) {
        $this->log->NoAny($id, $name, $comName, "資金不足の");
        $returnMode = 0;
        break;
      } elseif(Util::random(10000) > $failp[$arg] + $failq[$arg] * $island['rena']) {
        $this->log->Eiseifail($id, $name, $comName, $point);
        // 金を差し引く
        $island['money'] -= $value;
        $returnMode = 1;
        break;
      }
      $island['eisei'][$arg] = ($arg == 5) ? 250 : 100;
      $this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "の打ち上げ");
      // 金を差し引く
      $island['money'] -= $value;
      $island['fuel']  -= -$stonecost;
      $returnMode = 1;
      break;

    case $init->comEiseimente:
      // 人工衛星打修復
	  if(Turn::ChkCapLevel($init->comEiseimente,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      if($arg > 5) $arg = 0;
      if($island['eisei'][$arg] > 0) {
        $island['eisei'][$arg] = 150;
        $this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "の修復");
      } else {
        $this->log->NoAny($id, $name, $comName, "指定の人工衛星がない");
        $returnMode = 0;
        break;
      }
      // 金を差し引く
      $island['money'] -= $cost;
      $island['fuel']  -= -$stonecost;
      $returnMode = 1;
      break;

    case $init->comEiseiAtt:
      // 衛星破壊砲
	  if(Turn::ChkCapLevel($init->comEiseiAtt,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      if($arg > 5) $arg = 0;
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      if($tn !== 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }
      // 事前準備
      $tIsland = &$hako->islands[$tn];
      $tName   = &$tIsland['name'];

      if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
        // イレギュラーか軍事衛星がある場合
        $eName = $init->EiseiName[$arg];
        $p = ($island['eisei'][5] >= 1) ? 110 : 70;
        if($tIsland['eisei'][$arg] > 0) {
          if(Util::random(100) < $p - 10 * $arg) {
            $tIsland['eisei'][$arg] = 0;
            $this->log->EiseiAtts($id, $tId, $name, $tName, $comName, $eName);
          } else {
            $this->log->EiseiAttf($id, $tId, $name, $tName, $comName, $eName);
          }
        } else {
          $this->log->NoAny($id, $name, $comName, "指定の人工衛星がない");
          $returnMode = 0;
          break;
        }

        $nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
        $island['eisei'][$nkind] -= 30;
        if($island[$nkind] < 1) {
          $island[$nkind] = 0;
          $this->log->EiseiEnd($id, $name, ($island['eisei'][5] >= 1) ? $init->EiseiName[5] : $init->EiseiName[3]);
        }
      } else {
        // イレギュラーも軍事衛星もない場合
        $this->log->NoAny($id, $name, $comName, "必要な人工衛星がない");
        $returnMode = 0;
        break;
      }
      // 金を差し引く
      $island['money'] -= $cost;
      $island['fuel']  -= -$stonecost;
      $returnMode = 1;
      break;

    case $init->comEiseiLzr:
      // 衛星レーザー
	  if(Turn::ChkCapLevel($init->comEiseiLzr,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      if($tn != 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      // 事前準備
      $tIsland = &$hako->islands[$tn];
      $tName   = &$tIsland['name'];
      $tLand   = &$tIsland['land'];
      $tLandValue = &$tIsland['landValue'];

      if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
        // 実行許可ターンを経過したか？
        $this->log->Forbidden($id, $name, $comName);

        $returnMode = 0;
        break;
      }

	  if (($island['pop'] < $init->limitpop) || ($tIsland['pop'] < $init->limitpop)) {
        // リミッター発動人口を超えているか？
        $this->log->Forbidden($id, $name, $comName);

        $returnMode = 0;
        break;
	  }

      // 着弾点の地形等算出
      $tL  = $tLand[$x][$y];
      $tLv = $tLandValue[$x][$y];
      $tLname = $this->landName($tL, $tLv);
      $tPoint = "({$x}, {$y})";

      if($island['id'] == $tIsland['id']) {
        $tLand[$x][$y] = &$land[$x][$y];
      }

      if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
        // イレギュラーか軍事衛星がある場合
        if((($tL == $init->landSea) && ($tLv < 2)) || ($tL == $init->landSeaCity)) {
          // 効果のない地形
          $this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "暖かくなりました。");
        } elseif((($tL == $init->landSea) && ($tLv >= 2)) || ($tL == $init->landOil)) {
          // 船と油田は海になる
          $this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
          $tLand[$x][$y] = $init->landSea;
          $tLandValue[$x][$y] = 0;
        } elseif(($tL == $init->landNursery) || ($tL == $init->landSeaSide) || ($tL == $init->landPort)) {
          // 養殖場と砂浜と港は浅瀬になる
          $this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
          $tLand[$x][$y] = $init->landSea;
          $tLandValue[$x][$y] = 1;
        } else {
          // その他は荒地に
          $this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
          $tLand[$x][$y] = $init->landWaste;
          $tLandValue[$x][$y] = 1;
        }
        $eName = $init->EiseiName[$arg];
        $p = ($island['eisei'][5] >= 1) ? 110 : 70;
        $nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
        $island['eisei'][$nkind] -= (($island['eisei'][5] >= 1) ? 15 : 30);

      } else {
        // イレギュラーも軍事衛星もない場合
        $this->log->NoAny($id, $name, $comName, "必要な人工衛星がない");
        $returnMode = 0;
        break;
      }

      // 金を差し引く
      $island['money'] -= $cost;
      $island['fuel']  -= -$stonecost;
      $returnMode = 1;
      break;

    case $init->comMissileNM:
    case $init->comMissilePP:
    case $init->comMissileSPP:
    case $init->comMissileBT:
    case $init->comMissileSP:
    case $init->comMissileLD:
      // ミサイル系

      if($island['tenki'] == 4 || $island['tenki'] == 5){
        // 雷・雪の日は打てない
        $this->log->msNoTenki($id, $name, $comName);

        $returnMode = 0;
        break;
      }
	  if(Turn::ChkCapLevel($init->comMissileNM,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }

      $flag = 0;
      do {
      if(($arg == 0) || ($arg > $island['fire'])) {
        // 0の場合は撃てるだけ
        $arg = $island['fire'];
      }
      $comp = $arg;

      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      if($tn != 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      // 事前準備
      $tIsland = &$hako->islands[$tn];
      $tName   = &$tIsland['name'];
      $tLand   = &$tIsland['land'];
      $tLandValue = &$tIsland['landValue'];

      if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
        // 実行許可ターンを経過したか？
        $this->log->Forbidden($id, $name, $comName);

        $returnMode = 0;
        break;
      }

	  if (($island['pop'] < $init->limitpop) || ($tIsland['pop'] < $init->limitpop)) {
        // リミッター発動人口を超えているか？
        $this->log->Forbidden($id, $name, $comName);

        $returnMode = 0;
        break;
	  }

      // 難民の数
      $boat = 0;
      // ミサイルの内訳
      $missiles = 0; // 発射数
      $missileA = 0; // 範囲外、効果なし、荒地
      $missileB = 0; // 空中爆破
      $missileC = 0; // 迎撃
      $missileD = 0; // 怪獣命中
      $missileE = 0; // 戦艦迎撃

      // 誤差
      if(($kind == $init->comMissilePP) || ($kind == $init->comMissileBT) || ($kind == $init->comMissileSP)) {
        $err = 7;
	  } elseif ($kind == $init->comMissileSPP) {
	  	$err = 1;
      } else {
        $err = 19;
      }

      $bx = $by = 0;
      // 燃料が尽きるか砲弾が尽きるか指定数に足りるか基地全部が撃つまでループ
      while(($arg > 0) &&
            ($island['fuel'] >= - $cost) &&
            ($island['shell'] >= -$stonecost)) {
        // 基地を見つけるまでループ
        while($count < $init->pointNumber) {
          $bx = $this->rpx[$count];
          $by = $this->rpy[$count];
          if(($land[$bx][$by] == $init->landBase) ||
             ($land[$bx][$by] == $init->landHBase)) {
            break;
          }
          $count++;
        }
        if($count >= $init->pointNumber) {
          // 見つからなかったらそこまで
          break;
        }
        // 最低一つ基地があったので、flagを立てる
        $flag = 1;
        // 基地のレベルを算出
        $level = Util::expToLevel($land[$bx][$by], $landValue[$bx][$by]);
        // 基地内でループ
        while(($level > 0) &&
              ($arg > 0) &&
              ($island['fuel'] >= - $cost) &&
              ($island['shell'] >= -$stonecost)) {
          // 撃ったのが確定なので、各値を消耗させる
          $level--;
          $arg--;
          $island['fuel'] -= - $cost;
          $island['shell'] -= -$stonecost;
          $missiles++;

          // 着弾点算出
          $r = Util::random($err);
          $tx = $x + $init->ax[$r];
          $ty = $y + $init->ay[$r];
          if((($ty % 2) == 0) && (($y % 2) == 1)) {
            $tx--;
          }

          // 着弾点範囲内外チェック
          if(($tx < 0) || ($tx >= $init->islandSize) ||
             ($ty < 0) || ($ty >= $init->islandSize)) {
            // 範囲外
            $missileA++;
            continue;
          }

          // 着弾点の地形等算出
          $tL  = $tLand[$tx][$ty];
          $tLv = $tLandValue[$tx][$ty];
          $tLname = $this->landName($tL, $tLv);
          $tPoint = "({$tx}, {$ty})";

          // ミサイル迎撃
          if(($tIsland['ship']['senkan'] > 0) && (Util::random(1000) < $init->shipIntercept)) {
            $missileE++;
            continue;
          }

          // 防衛施設判定
          $defence = 0;
          if($defenceHex[$id][$tx][$ty] == 1) {
		  	if($island['id'] != $tIsland['id']){
            $defence = 1;
			}
          } elseif($defenceHex[$id][$tx][$ty] == -1) {
            $defence = 0;
          } else {
            if((((($tL == $init->landDefence) ||
			     ($tL == $init->landHDefence) ||
			 	($tL == $init->landSdefence)) &&
                ($tLv <= 1 || $kind == $init->comMissileLD)) ||
				($tL == $init->landProcity && ($tLv < 160))) &&
				($kind != $init->comMissileSPP)) {
              // 防衛施設に直撃、破壊
              // フラグをクリア
              for($i = 0; $i < 19; $i++) {
                $sx = $tx + $init->ax[$i];
                $sy = $ty + $init->ay[$i];

                // 行による位置調整
                if((($sy % 2) == 0) && (($ty % 2) == 1)) {
                  $sx--;
                }

                if(($sx < 0) || ($sx >= $init->islandSize) ||
                   ($sy < 0) || ($sy >= $init->islandSize)) {
                  // 範囲外の場合何もしない
                } else {
                  // 範囲内の場合
                  $defenceHex[$id][$sx][$sy] = 0;
                }
              }
			  $tLandValue[$tx][$ty] = 0;

            }elseif((($tL == $init->landDefence && ($tLv > 1))  ||
                     ($tL == $init->landHDefence && ($tLv > 1)) ||
                     ($tL == $init->landSdefence && ($tLv > 1)) ||
					 ($tL == $init->landProcity && ($tLv >= 160))) &&
					 ($kind != $init->comMissileSPP)) {
                // ミサイルログ
			if($tL == $init->landProcity){
				$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                $tLandValue[$tx][$ty] -= 30;
			}else{
            	$this->log->MsDamage($id, $target, $name, $tName, $comName, $point, $tPoint);
            	$tLandValue[$tx][$ty]--;
			}
              continue;
            } elseif((Turn::countAround($tLand, $tx, $ty, $init->landDefence, 19))  ||
                     (Turn::countAround($tLand, $tx, $ty, $init->landHDefence, 19)) ||
                     (Turn::countAround($tLand, $tx, $ty, $init->landSdefence, 19)) ||
                     (Turn::countAround($tLand, $tx, $ty, $init->landProcity, 7))) {
			  	if($island['id'] != $tIsland['id']){
	              $defenceHex[$id][$tx][$ty] = 1;
	              $defence = 1;
				  }
            } else {
              $defenceHex[$id][$tx][$ty] = -1;
              $defence = 0;
            }
          }

          if($defence == 1) {
            // 空中爆破
            $missileB++;
            continue;
          }

          if($island['id'] != $tIsland['id']) {
            // 防衛衛星がある場合
            if($tIsland['eisei'][4] && (Util::random(5000) < $tIsland['rena'])) {
              $tIsland['eisei'][4] -= 2;
              if($tIsland['eisei'][4] < 1) {
                $tIsland['eisei'][4] = 0;
                $this->log->EiseiEnd($target, $tName, $init->EiseiName[4]);
              }
            $missileB++;
            continue;
            }
          }

          // 「効果なし」hexを最初に判定
          if((($tL == $init->landSea) && ($tLv == 0))|| // 深い海
             (((($tL == $init->landSea) && ($tLv < 2)) ||    // 海または・・・
               (($tL == $init->landPoll) && ($kind != $init->comMissileBT)) ||  // 汚染土壌または・・・
               ($tL == $init->landWaste)|| //荒れ地または・・・
               ($tL == $init->landMountain) ||// 山または・・・
			   ($tL == $init->landnMountain)) // 山地で・・・
              && ($kind != $init->comMissileLD))) { // 陸破弾以外

            $tLname = $this->landName($tL, $tLv);
            $tLandValue[$tx][$ty] = $tLv;
            $missileA++;
            continue;
          }

          if(($tL >= 50 && $tL < 55)&&($kind != $init->comMissileLD)){
            // 鉱山・・・
            // 鉱山レベル下がる
            $minelv = ($tLv % 10) + 1; // 鉱山レベル読み出し
            if($kind == $init->comMissileSPP) {
              // レベル１の場合
              if($minelv == 1){
                $this->log->MsRoofFall($id, $target, $name, $tName,$comName, $tLname, $point, $tPoint);
                $tLand[$tx][$ty] = $init->landMountain;
                $tLandValue[$tx][$ty] = 0;
              } else {
                $this->log->MsRoofFall2($id, $target, $name, $tName,$comName, $tLname, $point, $tPoint);
                $tLandValue[$tx][$ty] --;
              }
            }
            continue;
          }

          // 弾の種類で分岐
          if($kind == $init->comMissileLD) {
            // 陸地破壊弾
            switch($tL) {
            case $init->landMountain:
              // 山(荒地になる)
              $this->log->msLDMountain($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              // 荒地になる
              $tLand[$tx][$ty] = $init->landWaste;
              $tLandValue[$tx][$ty] = 0;
              continue 2;

            case $init->landSdefence:
            case $init->landSfarm:
              //海底都市、海底防衛施設、海底農場
              $this->log->msLDSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;

            case $init->landMonster:
            case $init->landSleeper:
            case $init->landZorasu:
              // 怪獣
              $this->log->msLDMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;

            case $init->landSea:
              // 浅瀬
              $this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;

            default:
              // その他
              $this->log->msLDLand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
            }

            // 経験値
            if(($tL == $init->landTown) || ($tL == $init->landSeaCity) ||
               ($tL == $init->landNewtown) || ($tL == $init->landBigtown)) {
              if(($land[$bx][$by] == $init->landBase) ||
				 ($land[$bx][$by] == $init->landHBase)) {
                // まだ基地の場合のみ
                $landValue[$bx][$by] += round($tLv / 20);
                if($landValue[$bx][$by] > $init->maxExpPoint) {
                  $landValue[$bx][$by] = $init->maxExpPoint;
                }
              }
            }

            // 浅瀬になる
            $tLand[$tx][$ty] = $init->landSea;
            $tIsland['area']--;
            $tLandValue[$tx][$ty] = 1;

            // でも油田、浅瀬、海底基地、海底防衛施設だったら海
            if(($tL == $init->landOil) ||
               ($tL == $init->landSea) ||
               ($tL == $init->landSdefence) ||
               ($tL == $init->landZorasu)) {
              $tLandValue[$tx][$ty] = 0;
            }
            // でも養殖場だったら浅瀬
            if($tL == $init->landNursery) {
              $tLandValue[$tx][$ty] = 1;
            }
          }elseif($kind != $init->comMissileSP) {
            // その他ミサイル
            if(($tL == $init->landMonster) || ($tL == $init->landSleeper)) {
              // 怪獣
              $monsSpec = Util::monsterSpec($tLv);
              $special = $init->monsterSpecial[$monsSpec['kind']];

                if(($special & 0x100) && (Util::random(100) < 30)) {
                  // ミサイル叩き落とす
                  if($kind == $init->comMissileSPP) {
                    $this->log->msMonsCaughtS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  } else {
                    $this->log->msMonsCaught($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  }
                  $missileC++;
                  continue;
                }
                if($monsSpec['hp'] == 1) {
                  // 怪獣しとめた
                  if(($land[$bx][$by] == $init->landBase) ||
				 	 ($land[$bx][$by] == $init->landHBase)) {
                    // 経験値
                    $landValue[$bx][$by] += $init->monsterExp[$monsSpec['kind']];
                    if($landValue[$bx][$by] > $init->maxExpPoint) {
                      $landValue[$bx][$by] = $init->maxExpPoint;
                    }
                  }
                    // 通常
                    $this->log->msMonKill($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
					$tLand[$tx][$ty] = $init->landWaste;
                  // 収入
                  $value = $init->monsterValue[$monsSpec['kind']];
                  if($value > 0) {
                    $tIsland['money'] += $value;
                    $this->log->msMonMoney($target, $tLname, $value);
                  }

                  // 怪獣退治数
                  $island['taiji']++;

                  // 賞関係
                  list($flags, $monsters, $turns) = split(",", $prize, 3);
                  $v = 1 << $monsSpec['kind'];
                  $monsters |= $v;

                  $prize = "{$flags},{$monsters},{$turns}";
                } else {
                  // 怪獣生きてる
                    // 通常
                    $this->log->msMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  // HPが1減る
                  $tLandValue[$tx][$ty]--;
                  $missileD++;
                  continue;
                }

            } elseif((($tL == $init->landFarm) && ($tLv > 25)) ||
					 (($tL == $init->landFactory) && ($tLv > 100)) ||
                     (($tL == $init->landHatuden) && ($tLv > 500)) ||
					 ($tL == $init->landBigtown)||
					 ($tL == $init->landIndCity)||
					 ($tL == $init->landSeeCity)||
					 ($tL == $init->landCapital)) {
                  // 通常
				  $tLandValue[$tx][$ty] -= 60;

			}elseif(($tL == $init->landSdefence)||
					($tL == $init->landDefence)||
					($tL == $init->landHDefence)){
					$tLandValue[$tx][$ty] -= 1;

			}elseif(($tL == $init->landSecpol)||
					($tL == $init->landMyhome)||
					($tL == $init->landMonument)||
					($tL == $init->landPlains)||
					($tL == $init->landForest)){
					$tLandValue[$tx][$ty] = 0;

			}elseif(($tL != $init->landSea)||
					($tL != $init->landWaste)||
					($tL != $init->landPoll)||
					($tL != $init->landMountain)||
					($tL != $init->landnMountain)){
             	 // 通常地形
                // 通常
				$tLandValue[$tx][$ty] -= 30;
			}

			if($tLandValue[$tx][$ty] <= 0){
				if(($tL == $init->landOil) ||
					($tL == $init->landSdefence) ||
					($tL == $init->landZorasu)){
	         		$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
	                $tLand[$tx][$ty] = $init->landSea;
	                $tLandValue[$tx][$ty] = 0;
				}
				if($tLand[$tx][$ty] != $init->landWaste){
         			$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
					$tLand[$tx][$ty] = $init->landWaste;
	                $tLandValue[$tx][$ty] = 0;
				}
			}else{
				$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
			}

            if(Util::checkShip($tLand[$tx][$ty], $tLv)){
                // 船だったら海になる
                $tLand[$tx][$ty] = $init->landSea;
                $tLandValue[$tx][$ty] = 0;
            } elseif($tL == $init->landNursery) {
              // 浅瀬になる
              $tLand[$tx][$ty] = $init->landSea;
              $tLandValue[$tx][$ty] = 1;
            } elseif($kind == $init->comMissileBT) {
              // バイオミサイルの時は汚染
              if(($tL == $init->landPoll) && ($tLandValue[$tx][$ty] < 10)) {
                $tLandValue[$tx][$ty]++;
              } elseif($tL != $init->landPoll) {
                // 汚染土壌になる
                $tLand[$tx][$ty] = $init->landPoll;
                $tLandValue[$tx][$ty] = Util::random(10) + 1;
              }
            }
            // でも油田、海底防衛施設、海底消防署、海底農場だったら海
            if(($tL == $init->landOil) ||
			   ($tL == $init->landSdefence)) {
              $tLand[$tx][$ty] = $init->landSea;
              $tLandValue[$tx][$ty] = 0;
            }
            // でも耐久力の残っている海底防衛施設なら耐える
            if (($tL == $init->landSdefence) && ($tLv > 1)) {
              $tLand[$tx][$ty] = $init->landSdefence;
              $tLandValue[$tx][$ty] = $tLv;
            }
            // でも耐久力の残っている防衛施設なら耐える
            if (($tL == $init->landDefence) && ($tLv > 1)) {
              $tLand[$tx][$ty] = $init->landDefence;
              $tLandValue[$tx][$ty] = $tLv;
            }
			// でも耐久力の残っている偽装防衛施設なら耐える
            if (($tL == $init->landHDefence) && ($tLv > 1)) {
              $tLand[$tx][$ty] = $init->landHDefence;
              $tLandValue[$tx][$ty] = $tLv;
            }

            // 経験値
            if(($tL == $init->landTown) ||
			   ($tL == $init->landSeaCity) ||
               ($tL == $init->landNewtown) ||
			   ($tL == $init->landBigtown) ||
			   ($tL == $init->landSeeCity) ||
			   ($tL == $init->landProcity)) {
              if(($land[$bx][$by] == $init->landBase) ||
			  	 ($land[$bx][$by] == $init->landHBase)) {
                $landValue[$bx][$by] += round($vdamage / 10);
                $boat += $tLv; // 通常ミサイルなので難民にプラス
                if($landValue[$bx][$by] > $init->maxExpPoint) {
                  $landValue[$bx][$by] = $init->maxExpPoint;
                }
              }
            }


          } else {
            if(($tL == $init->landMonster) && (Util::random(100) < 30)) {
              // 捕獲に成功
              $tLand[$tx][$ty] = $init->landSleeper;
              // 捕獲
              $this->log->MsSleeper($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
            } else {
              $missileA++;
            }
          }
        }

        // カウント増やしとく
        $count++;
      }

      // ミサイルログ
      if($missiles > 0){
          // 通常
          $this->log->mslog($id, $target, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE);
      }

      if($flag == 0) {
        // 基地が一つも無かった場合
        $this->log->msNoBase($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      $tIsland['land'] = $tLand;
      $tIsland['landValue'] = $tLandValue;
      unset($hako->islands[$tn]);
      $hako->islands[$tn] = $tIsland;


      // 難民判定
      $boat = round($boat / 2);
      if(($boat > 0) && ($id != $target)) {
        // 難民漂着
        $achive = 0; // 到達難民
        for($i = 0; ($i < $init->pointNumber && $boat > 0); $i++) {
          $bx = $this->rpx[$i];
          $by = $this->rpy[$i];
          if((($land[$bx][$by] == $init->landTown)&&($landValue[$bx][$by]>190)) ||
		     ($land[$bx][$by] == $init->landBigTown) ||
			 ($land[$bx][$by] == $init->landNewTown) ||
			 ($land[$bx][$by] == $init->landIndCity)) {
            // 町の場合
            $lv = $landValue[$bx][$by];
				if($land[$bx][$by] == $init->landTown){
					$maxlv = 500;
				}elseif($land[$bx][$by] == $init->landBigTown){
					$maxlv = 3000;
				}elseif ($land[$bx][$by] == $init->landNewTown){
					$maxlv = 300;
				}elseif ($land[$bx][$by] == $init->landIndCity){
					$maxlv = 1500;
				}
            if($boat > 50) {
              $lv += 50;
              $boat -= 50;
              $achive += 50;

            } else {
              $lv += $boat;
              $achive += $boat;
              $boat = 0;
            }
            if($lv > $maxlv) {
              $boat += ($lv -  $maxlv);
              $achive -= ($lv -  $maxlv);
              $lv = $maxlv;
            }
            $landValue[$bx][$by] = $lv;
          } elseif($land[$bx][$by] == $init->landPlains) {
            // 平地の場合
            $land[$bx][$by] = $init->landTown;
            if($boat > 10) {
              $landValue[$bx][$by] = 5;
              $boat -= 10;
              $achive += 10;
            } elseif($boat > 5) {
              $landValue[$bx][$by] = $boat - 5;
              $achive += $boat;
              $boat = 0;
            }
          }
          if($boat <= 0) {
            break;
          }
        }

        if($achive > 0) {
          // 少しでも到着した場合、ログを吐く
		  $sachive = Util::Rewriter2("",$achive);
          $this->log->msBoatPeople($id, $name,$sachive);

          // 難民の数が一定数以上なら、平和賞の可能性あり
          if($achive >= 200) {
            $prize = $island['prize'];
            list($flags, $monsters, $turns) = split(",", $prize, 3);

            if((!($flags & 8)) &&  $achive >= 200){
              $flags |= 8;
              $this->log->prize($id, $name, $init->prizeName[4]);
            } elseif((!($flags & 16)) &&  $achive > 500){
              $flags |= 16;
              $this->log->prize($id, $name, $init->prizeName[5]);
            } elseif((!($flags & 32)) &&  $achive > 800){
              $flags |= 32;
              $this->log->prize($id, $name, $init->prizeName[6]);
            }
            $island['prize'] = "{$flags},{$monsters},{$turns}";
          }
        }
      }

        $command  = $comArray[0];
        $kind   = $command['kind'];
        if((($kind == $init->comMissileNM) ||  // 次もミサイル系なら...
            ($kind == $init->comMissilePP) ||
            ($kind == $init->comMissileSPP) ||
            ($kind == $init->comMissileBT) ||
            ($kind == $init->comMissileSP) ||
            ($kind == $init->comMissileLD)) &&
            ($init->multiMissiles)) {

          $island['fire'] -= $comp;
          $cost = $init->comCost[$kind];

          if($island['fire'] < 1) {
            // 最大発射数を超えた場合
            $this->log->msMaxOver($id, $name, $comName);

            $returnMode = 0;
            break;
          }

          if (($island['fire'] > 0) && ($island['money'] >= $cost)) { // 少なくとも1発は撃てる
            Util::slideFront(&$comArray, 0);
            $island['command'] = $comArray;

            $kind   = $command['kind'];
            $target = $command['target'];
            $x      = $command['x'];
            $y      = $command['y'];
            $arg    = $command['arg'];

            $comName = $init->comName[$kind];
            $point = "({$x},{$y})";
            $landName = $this->landName($landKind, $lv);
          } else {
            break;
          }
        } else {
          break;
        }
      } while ($island['fire'] > 0);

      $returnMode = 1;
      break;

    case $init->comSendMonster:
      // 揚陸艦派遣
	  if(Turn::ChkCapLevel($init->comSendMonster,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      $tIsland = $hako->islands[$tn];
      $tName = $tIsland['name'];

      if($tn != 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      if(($hako->islandTurn - $island['starturn']) < $init->noMissile ||
	  ($island['pop'] < $init->limitpop)) {
        // 実行許可ターンを経過したか？
        $this->log->Forbidden($id, $name, $comName);

        $returnMode = 0;
        break;
      }
	  if ($target == $id){
	  //陸上部隊を自国に派遣する場合
  	  if(!($island['money'] < $init->disVikingMinMoney)){
	  	if($island['home'] > 0){
			$vMoney = round(Util::random($island['money'])/2);
		}else{
            $vMoney = round(Util::random($island['money'])/4);
		}
			$vMoney = min($island['money'] - $cost,$vMoney);
            $island['money'] -= $vMoney;
            if($island['money'] < 0) $island['money'] = 0;
      }
	  $this->log->monsSendme($id, $name, $vMoney);
	  $island['siji'] += 30;
	  }else{
      // メッセージ
      $this->log->monsSend($id, $target, $name, $tName);
      $tIsland['monstersend']++;
      $hako->islands[$tn] = $tIsland;
	  }
      $island['money'] -= $cost;
      $island['steel'] += $stonecost;
      $island['fuel'] += $stonecost;
      $returnMode = 1;
      break;

    case $init->comTrain:
      // 軍事訓練
	  if(Turn::ChkCapLevel($init->comTrain,$island['capital'],$id, $name, $comName) == false){
	  	$returnMode = 0;
		break;
	  }
      if(($landKind != $init->landBase) &&
		 ($landKind != $init->landHBase)){
        // 基地以外では実行できない
        $this->log->TrFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }
      $landValue[$x][$y] += 3; // 経験値+3
      $this->log->TrSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      $island['shell'] += $stonecost;
      $returnMode = 1;
      break;

    case $init->comMkShell:
      // 砲弾製造
      $bx = $by = $sumshelllv = $count = 0;
      // 軍事工場を見つけて全部数えるまでループ
      while($count < $init->pointNumber) {
        $bx = $this->rpx[$count];
        $by = $this->rpy[$count];
        if($land[$bx][$by] == $init->landSFactory) {
          $sumshelllv += $landValue[$bx][$by];
        }
        $count++;
      }
      if($sumshelllv == 0) {
        // 軍事工場が一つも無かった場合
        $this->log->NoFactory($id, $name, $comName, '軍事');
        $returnMode = 0;
        break;
      }
      // 最低一つ軍事工場があったので、砲弾製造量決定
      if($arg == 0) { $arg = 1000; }
        // 入力回数と砲弾工場
        $value = min($arg, (int)($sumshelllv  / 10));
        // 燃料
        $value = min($value, (int)($island['fuel'] / 10));
        // 人口
        $value = min($value, (int)($island['pop'] / 100));
        // 石油
        $value = min($value, (int)($island['oil'] / 50));
        // 鉄鋼
        $value = min($value, (int)($island['steel'] / 10));

      // 砲弾製造ログ
      $this->log->Make($id, $name, $comName, $value * 10,  $init->unitShell);
      $island['oil'] -= $value * 50;
      $island['steel'] -= $value * 10;
      $island['shell'] += $value * 10;
      $island['fuel'] += $value * $cost;

      $returnMode = 1;
      break;

    case $init->comMkMaterial:
      // 建材製造
      $bx = $by = $summateriallv = $count = 0;
      // 建材工場を見つけて全部数えるまでループ
      while($count < $init->pointNumber) {
        $bx = $this->rpx[$count];
        $by = $this->rpy[$count];
        if($land[$bx][$by] == $init->landMFactory) {
          $summateriallv += $landValue[$bx][$by];
        }
        $count++;
      }
      if($summateriallv == 0) {
        // 建材工場が一つも無かった場合
        $this->log->NoFactory($id, $name, $comName, '建材');
        $returnMode = 0;
        break;
      }
      // 最低一つ建材工場があったので、建材製造量決定
      if($arg == 0) { $arg = 1000; }

      // 入力回数と建材工場
      $value = min($arg, (int)($summateriallv  / 10));
      // 燃料
      $value = min($value, (int)($island['fuel'] / 10));
      // 人口
      $value = min($value, (int)($island['pop'] / 200));
      // 石材
      $value = min($value, (int)($island['stone'] / 10));

      // 建材製造ログ
      $this->log->Make($id, $name, $comName, ($value * 10), $init->unitMaterial);
      $island['material'] += $value * 10;
      $island['stone'] -= $value * 10;
      $island['fuel'] += $value * $cost;

      $returnMode = 1;
      break;

    case $init->comMkSteel:
      // 建材製造
      $bx = $by = $summateriallv = 0;
      // 建材工場を見つけて全部数えるまでループ
      while($count < $init->pointNumber) {
        $bx = $this->rpx[$count];
        $by = $this->rpy[$count];
        if($land[$bx][$by] == $init->landMFactory) {
          $summateriallv += $landValue[$bx][$by];
        }
        $count++;
      }
      if($summateriallv == 0) {
        // 建材工場が一つも無かった場合
        $this->log->NoFactory($id, $name, $comName, '建材');
        $returnMode = 0;
        break;
      }
      // 最低一つ建材工場があったので、建材強化量決定
      if($arg == 0) { $arg = 1000; }

      // 入力回数と建材工場
      $value = min($arg, (int)($summateriallv  / 10));
      // 燃料
      $value = min($value, (int)($island['fuel'] / 10));
      // 人口
      $value = min($value, (int)($island['pop'] / 100));
      // 建材
      $value = min($value, (int)($island['material'] / 10));
      // 鋼鉄
      $value = min($value, (int)($island['steel'] / 10));

      // 建材製造ログ
      $this->log->Make($id, $name, $comName, $value * 10, $init->unitMaterial);
      $island['material'] += $value * 10;
      $island['steel'] -= $value * 10;
      $island['fuel'] += $value * $cost;

      $returnMode = 1;
      break;
    case $init->comMkResource:
      // 資源採掘
      if($landKind >= 25) {

        $resourcedeposits = (int)($landValue[$x][$y] / 10);
        $minelv = ($landValue[$x][$y] % 10) + 1;
        $landValue[$x][$y] -= $resourcedeposits * 10; // 埋蔵量0に戻す

        if($landKind == $init->landStonemine) {
          $value = $resourcedeposits * 100; // 採石量決定
          $value2 = $resourcedeposits * 40;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['stone'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '石材を',$init->unitStone);

		} else if($landKind == $init->landSteel) {
          $value = $resourcedeposits * 100; // 鉄鋼量決定
          $value2 = $resourcedeposits * 40;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['steel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '鉄鉱を', $init->unitSteel);

	    } else if($landKind == $init->landCoal) {
          $value = $resourcedeposits * 150; // 石炭から燃料量決定
          $value2 = $resourcedeposits * 60;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['fuel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '燃料を石炭から', $init->unitFuel);

	    } else if($landKind == $init->landUranium) {
          $value = $resourcedeposits * 1000; // ウランから燃料量決定
          $value2 = $resourcedeposits * 400;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['fuel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '燃料をウランから', $init->unitFuel);

		} else if($landKind == $init->landSilver) {
          $value = $resourcedeposits * 140; // 銀量決定
          $value2 = $resourcedeposits * 50;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['silver'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '銀を', $init->unitSilver);
        }

        // 燃料を差し引く
        $island['fuel'] -= - $cost;
      } else {
        // 鉱山以外にはできない
        $this->log->LandFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

      $returnMode = 0;
      break;

    case $init->comFood:
    case $init->comMoney:
    case $init->comSilver:
    case $init->comSteel:
    case $init->comMaterial:
    case $init->comStone:
    case $init->comWood:
    case $init->comFuel:
    case $init->comOil:
    case $init->comGoods:
    case $init->comAlcohol:
    case $init->comFood:
    case $init->comShell:
      // 輸送系
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      $tIsland = &$hako->islands[$tn];
      $tName = $tIsland['name'];
	  if(($hako->islandTurn - $island['starturn']) < $init->noAssist){
	  	$str = "建国直後";
	  	$this->log->NoAny($id, $name, $comName, $str);
		$returnMode = 0;
	    break;
	  }
	  $container = "";//参照渡し用
	  $str = "";
	  Turn::comTradesN($kind,$cost,$container,$str);
	  $value = Turn::comTrades($arg,$cost,$island[$container],$tIsland[$container]);
	  $str = "{$value}".$str;
      // 輸出ログ
      $this->log->Aid($id, $target, $name, $tName, $comName, $str);
	  if(($island['port'] + $island['bport']) == 0){
	      $returnMode = 1;
	  }else{
		  $returnMode = 0;
	  }
      break;
  //------------------------------輸送処理ここまで---------------------
    case $init->comPropaganda:
      // 誘致活動

      $this->log->propaganda($id, $name, $comName);
      $island['propaganda'] = 1;
      $island['money'] -= $cost;
	if ($island['siji'] >= 40){
		$this->log->PropaFail($id, $name, $comName);
	}
      $returnMode = 1;
      break;

    case $init->comGiveup:
      // 放棄
      $this->log->giveup($id, $name);
      $island['dead'] = 1;
      unlink("{$init->dirName}/island.{$id}");

      $returnMode = 1;
      break;
    }

    // 変更された可能性のある変数を書き戻す
//    $hako->islands[$hako->idToNumber[$id]] = $island;

    // 事後処理
    unset($island['prize']);
    unset($island['land']);
    unset($island['landValue']);
    unset($island['command']);
    $island['prize'] = $prize;
    $island['land'] = $land;
    $island['landValue'] = $landValue;
    $island['command'] = $comArray;

    return $returnMode;
  }
  //---------------------------------------------------
  // 成長および単ヘックス災害
  //---------------------------------------------------
  function doEachHex(&$hako, &$island) {
    global $init;
    // 導出値
    $name = $island['name'];
    $id = $island['id'];
    $land = $island['land'];
    $landValue = $island['landValue'];

    // 増える人口のタネ値
    $addpop  = 20;  // 村、町
    $addpop2 = 5; // 都市
    $addpop3 = 1; //観光都市
    if($island['food'] < 0) {
      // 食料不足
      $addpop = -10;
	  $addpop3 = -50;
    } elseif($island['ship']['viking'] > 0) {
      // 海賊船が出没中は成長しない
      $addpop = 0;
    } elseif(($island['siji'] + $island['hapiness']) <= 15) {
	  //暴動発生中なら人口減少
	  if(($island['polit'] != 2)||($island['policestat'] == 1)){
	  //警察国家以外or警察スト中
	  if (Util::random(500) < $init->disRobViking){
			if (($hako->islandTurn - $island['starturn']) > $init->noMissile){
				$island['riot'] = 1;
				$addpop = -30;
				$addpop3 = -30;
				$this->log->popDec($id, $name);
			}
		}
		}else{
				$this->log->Cutsecpol($id, $name);
		}
    } elseif($island['propaganda'] == 1) {
      // 誘致活動中
      $addpop = 20;
      $addpop2 = 5;
    } elseif($island['park'] > 0) {
      // 遊園地があると人が集まる
      $addpop  += 5;
      $addpop2 += 2;
      $addpop3 += 3;
	} elseif($island['npark'] > 0) {
	  $addpop3 += 3;
	} elseif($island['m10'] > 0) {
	  $addpop3 += 3;
	}
    $monsterMove = array();
    // ループ
    for($i = 0; $i < $init->pointNumber; $i++) {
      $x = $this->rpx[$i];
      $y = $this->rpy[$i];
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];

      switch($landKind) {
      case $init->landTown:
      case $init->landNewtown:
      case $init->landBigtown:
      case $init->landSeeCity:
      case $init->landIndCity:
      case $init->landCapital:
        // ニュータウン系
		// 工業都市
		// 観光都市
		// 現代都市
		// 首都
        if($addpop < 0) {
          // 不足
          $lv -= Util::random(-$addpop) + 1;
          if($lv <= 0) {
            // 平地に戻す
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
			$this->log->popDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            continue;
          }
		} else {
          // 成長
		  if($landKind == $init->landCapital){
		  //首都用
		  	$nsize = ($island['capital']-1)*2;
		  }else{
		  	$nsize = 0;
		  }
		  if($landKind == $init->landTown){
		  	//大都市成長カウント用
		  	$townCount = Turn::countAround($land, $x, $y, $init->landBigtown, 7);
		  	if($island['capital'] > 2){
		  		$townCounts = Turn::countAround($land, $x, $y, $init->landCapital, 7);
		  		$townCount += $townCounts;
		  	}
		  }
          if($lv < $init->maxLPop[$landKind][$nsize]) {
          	if($landKind == $init->landSeeCity){
          		$lv += Util::random($addpop3) + 1;
          	}
            $lv += Util::random($addpop) + 1;

            if($lv > $init->maxLPop[$landKind][$nsize]) {
              $lv = $init->maxLPop[$landKind][$nsize];
            }
          } else {
            // 都市になると成長遅い
            if($landKind == $init->landTown && $townCount == 0){
            	//農村部は成長しない
            	$lv = $lv;
            }elseif($landKind == $init->landSeeCity){
            	$lName = $this->landName($landKind, $lv);
            	//観光都市はイベント発生時のみ成長する
            	//警察国家ではイベントをキャンセル
            	//遊園地、国立公園、記念碑でイベント確率を決定
            	if((Util::random(100) < ($island['park'] + $island['npark'] + $island['m10']))&&
            		($lv < $init->maxLPop[$landKind][$nsize+1])&&
            	   ($island['polit']) != 2){
            		$this->log->Booming($id, $name, $lName, "($x, $y)");
            		$lv += Util::random($addpop3) + 1;

            	}elseif(Util::random(10000) < 20){
            	//まれに減少するイベントも発生
            		$lv -= Util::random(-$addpop3) + 1;
            		$this->log->Shrinking($id, $name, $lName, "($x, $y)");
            	}
            }else{
            	$lv += Util::random($addpop2) + 1;
            }
          }
        }
        if($lv > $init->maxLPop[$landKind][$nsize+1]) {
          $lv = $init->maxLPop[$landKind][$nsize+1];
        }
        $landValue[$x][$y] = $lv;
        break;

	  case $init->landsea:
	  	//海異常修正用
	  	  if(($lv >= 6)&&($lv != 255)){
		  	$lv = 0;
			$landValue[$x][$y] = $lv;
			}
		  break;
      case $init->landPlains:
        // 平地
        if((Util::random(5) == 0) && ($island['material'] > 9)) {
          // 周りに農場、町があれば、ここも町になる
          if($this->countGrow($land, $landValue, $x, $y)){
            $land[$x][$y] = $init->landTown;
            $landValue[$x][$y] = 1;
            $island['material'] -= 10;
          }
        }
        break;

      case $init->landFFactory:
	  //畜産場自然増
	  if($lv < 125){
	  	$lv++;
	  }
	  $landValue[$x][$y] = $lv;
        break;
      case $init->landPoll:
        // 汚染土壌
        if(Util::random(10) == 0) {
          // 汚染浄化
          $land[$x][$y] = $init->landPoll;
          $landValue[$x][$y]--;
          if(($landKind == $init->landPoll) && ($landValue[$x][$y] == 0)) {
            // 汚染浄化され平地になる
            $land[$x][$y] = $init->landWaste;
          }
        }
        break;

      case $init->landProcity:
        // 防災都市
        if($addpop < 0) {
          // 不足
          $lv -= Util::random(-$addpop) + 1;
          if($lv <= 0) {
            // 平地に戻す
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
			$this->log->popDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            continue;
          }
	    } else {
          // 成長
          if($lv < 100) {
            $lv += Util::random($addpop) + 1;
            if($lv > 100) {
              $lv = 100;
            }
          } else {
            // 都市になると成長遅い
              $lv += Util::random($addpop2) + 1;
          }
        }
        if($lv > 200) {
          $lv = 200;
        }
        $landValue[$x][$y] = $lv;
        break;

      case $init->landForest:
        // 森
        if($lv < 200) {
          // 木を増やす
          $landValue[$x][$y]++;
        }
        break;

      case $init->landMonument:
        // 記念碑
        $lv = $landValue[$x][$y];
        $lName = $this->landName($landKind, $lv);

        if(($lv == 5) || ($lv == 6) || ($lv == 21) || ($lv == 24) || ($lv == 32)) {
          if(util::random(100) < 5) {
            // お土産
            $value = 1+ Util::random(49);
            if ($value > 0) {
              $island['money'] += $value;
              $str = "{$value}{$init->unitMoney}";
              $this->log->Miyage($id, $name, $lName, "($x,$y)", $str);
            break;
            }
          }
        } elseif (($lv == 7) || ($lv == 33)) {
          if(util::random(100) < 5) {
            // 収穫
            $value = round($island['pop'] / 10) * 10 + Util::random(11);
            // 人口１万人ごとに1万トンの収穫
            if ($value > 0) {
              $island['food'] += $value;
              $str = "{$value}{$init->unitFood}";
              $this->log->Syukaku($id, $name, $lName, "($x,$y)", $str);
            break;
            }
          }
		  break;
        } elseif(($lv == 40) || ($lv == 41) || ($lv == 42) || ($lv == 43)) {
          if(util::random(100) < 1) {
            // 卵孵化
            $kind = Util::random($init->monsterLevel1) + 1;
            $lv = $kind * 100
              + $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
            // そのヘックスを怪獣に
            $land[$x][$y] = $init->landMonster;
            $landValue[$x][$y] = $lv;
            // 怪獣情報
            $monsSpec = Util::monsterSpec($lv);
            // メッセージ
            $this->log->EggBomb($id, $name, $mName, "($x,$y)", $lName);
            break;
          }
        }
        break;

      case $init->landDefence:
      case $init->landHDefence:
        if($lv > 3) {
          // 防衛施設自爆
          $lName = $this->landName($landKind, $lv);
          $this->log->bombFire($id, $name, $lName, "($x, $y)");

          // 広域被害ルーチン
          $this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
        }
        break;

      case $init->landHatuden:
        // 発電所
        $lName = $this->landName($landKind, $lv);

        break;

      case $init->landOil:
        // 海底油田
        $lName = $this->landName($landKind, $lv);
        if(Util::random(100) < ($lv * 5)) {
          // 収入
          $value = Util::random(500) + 1;
          $island['oil'] += $value;
          $str = "{$value}{$init->unitOil}";

          // 収入ログ
          $this->log->oilFuel($id, $name, $lName, "($x, $y)", $str);

          // 収入があったときは枯渇判定
          if(Util::random(100) < (12 - $lv)) {
            // 枯渇
            $this->log->oilEnd($id, $name, $lName, "($x, $y)");
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }

        // さらに事故判定
        if(Util::random(10000) < 20){
          $lName = $this->landName($landKind, $lv);
          $this->log->OilBomb($id, $name, $lName, "($x, $y)");

          // 広域被害ルーチン
          $this->oilwideDamage($id, $name, &$land, &$landValue, $x, $y);
        }
        break;

      case $init->landStonemine:
      case $init->landSteel:
      case $init->landCoal:
      case $init->landUranium:
      case $init->landSilver:
        // 鉱山
        $lName = $this->landName($landKind, $lv);
        // 鉱山埋蔵量計算
        $minelv = ($lv % 10) + 1; // 鉱山レベル読み出し
        $minedeposits = floor($lv / 10); // 鉱山埋蔵量読み出し
        // 鉱山落盤

        // 判定
        if(Util::random(1000) < $init->disRoofFall){
          // 落盤
          // レベル１の場合
          if($minelv == 1){
            $this->log->RoofFall($id, $name, $lName, "($x, $y)");
            $land[$x][$y] = $init->landMountain;
            $landValue[$x][$y] = 0;
          } else {
            $this->log->RoofFall2($id, $name, $lName, "($x, $y)");
            $landValue[$x][$y] --;
			$minelv --;
          }
        }

        // 鉱山埋蔵量書き込み
        $landValue[$x][$y] += ($minelv -  floor($minedeposits / $minelv)) * 10;
		$comName = $init->comName[$kind];

      // 資源採掘
	  $done = False;
	  if ($island['fuel'] >= 50){
        if(($hako->islandTurn%5 == 0)&&($landKind == $init->landStonemine)) {
          $value = $minedeposits * 100; // 採石量決定
          $value2 = $minedeposits * 40; //最低量決定
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['stone'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '石材を',$init->unitStone);
		  $done = True;
		} elseif(($hako->islandTurn%5 == 1)&&($landKind == $init->landSteel)) {
          $value = $minedeposits * 100; // 鉄鋼量決定
          $value2 = $minedeposits * 40;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['steel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '鉄鉱を', $init->unitSteel);
		  $done = True;
	    } elseif(($hako->islandTurn%5 == 2)&&($landKind == $init->landCoal)) {
          $value = $minedeposits * 150; // 石炭から燃料量決定
          $value2 = $minedeposits * 60;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['fuel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '燃料を石炭から', $init->unitFuel);
		  $done = True;

	    } elseif(($hako->islandTurn%5 == 3)&&($landKind == $init->landUranium)) {
          $value = $minedeposits * 1000; // ウランから燃料量決定
          $value2 = $minedeposits * 400;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['fuel'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '燃料をウランから', $init->unitFuel);
		  $done = True;

		} elseif(($hako->islandTurn%5 == 4)&&($landKind == $init->landSilver)) {
          $value = $minedeposits * 140; // 銀量決定
          $value2 = $minedeposits * 50;
          $value = Util::random($value);
          if($value < $value2){
            $value = $value2;
          }
          $island['silver'] += $value;
          $this->log->Resource($id, $name, $comName, $value, $point, '銀を', $init->unitSilver);
		  $done = True;

        }
		}
        // 燃料を差し引く
		if ($done == True){
		$landValue[$x][$y] -= $minedeposits * 10; // 埋蔵量0に戻す
        $island['fuel'] -= 50;
		}
        break;

      case $init->landPark:
        // 遊園地
        $lName = $this->landName($landKind, $lv);
        //収益は人口増加とともに横ばい傾向
        //人口の平方根の1～2倍 ex 1万=1～2億Va 100万=10～20億Va
        $value = floor(sqrt($island['pop'])*((Util::random(100)/100)+1)/10);
        $island['money'] += $value;
        $str = "{$value}{$init->unitMoney}";
        //収入ログ
        if ($value > 0)
          $this->log->ParkMoney($id, $name, $lName, "($x,$y)", $str);
        //イベント判定
        if(Util::random(100) < 30) {
          // 毎ターン 30% の確率でイベントが発生する
          //遊園地のイベント
          $value2=$value;
          //食料消費
          $value = floor($island['pop'] * $init->eatenFood / 2); // 規定食料消費の半分消費
          $island['food'] -= $value;
          $str = "{$value}{$init->unitFood}";
          if ($value > 0)
            $this->log->ParkEvent($id, $name, $lName, "($x,$y)", $str);
            //イベントの収支
            $value = floor((Util::random(200) - 100)/100 * $value2);//マイナス100%～プラス100%
            $island['money'] += $value;
            $str = "{$value}{$init->unitMoney}";
            if ($value > 0) $this->log->ParkEventLuck($id, $name, $lName, "($x,$y)", $str);
            if ($value < 0) $this->log->ParkEventLoss($id, $name, $lName, "($x,$y)", $str);
          }
          break;

	  case $init->landFBase:
	  //他国軍基地
		  $tn = $hako->idToNumber[$lv];
		  $tIsland = $hako->islands[$tn];
		  $tname = $tIsland['name'];
		  $this->log->FBDeb($id, $name,$lv,$tname);

		  	if($tn != 0 && empty($tn)) {
			}else{
				$hako->islands[$tn]['sfarmy']++;
			}

                if($land[$sx][$sy] == $init->landMonster) {
                  // 怪獣がいる場合、その怪獣を攻撃する
                  // 対象となる怪獣の各要素取り出し
                  $monsSpec = Util::monsterSpec($landValue[$sx][$sy]);
                  $tLv = $landValue[$sx][$sy];
                  $tspecial  = $init->monsterSpecial[$monsSpec['tkind']];
                  $tname = $monsSpec['name'];

                  $this->log->senkanAttack($id, $name, $this->landName($landKind, $lv), "($x,$y)", $tname, $tPoint);

                  if($monsSpec['hp'] > 1){
                    // 対象の体力を減らす
                    $landValue[$sx][$sy]--;
                  } else {
                    // 対象の怪獣が倒れて荒地になる
                    $land[$sx][$sy] = $init->landWaste;
                    $landValue[$sx][$sy] = 0;
                    // 収入
                    $value = $init->monsterValue[$monsSpec['kind']];
                    if($value > 0) {
                      $island['money'] += $value;
                      $this->log->msMonMoney($target, $mName, $value);
                    }
                  }
                  break;
                }
        break;

      case $init->landPort:
        // 港
        $lName = $this->landName($landKind, $lv);
        $seaCount = Turn::countAround($land, $x, $y, $init->landSea, 7);
        if(!$seaCount){
          // 周囲に最低1Hexの海も無い場合、閉鎖
          $this->log->ClosedPort($id, $name, $lName, "($x,$y)");
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 1;
        }
        if($seaCount == 6){
          // 周囲に最低1Hexの陸地が無い場合、閉鎖
          $this->log->ClosedPort($id, $name, $lName, "($x,$y)");
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 1;
        }
        break;

	  case $init->landMyhome:
	    $siji = Ceil($island['siji'] + $island['hapiness']);
		if($siji > 100){
			$siji = 100;
		}
		$landValue[$x][$y] = $siji;
	  break;

      case $init->landZorasu:
        // 揚陸艦
		$flanding = false;
        if($ZorasuMove[$x][$y] == 1) {
          // すでに動いた後
          break;
        }

        // 動く方向を決定
        for($j = 0; $j < 2; $j++) {
		  $ex = $init->islandSize / 2;
		  $ey = $ex;

		  if($ex>$x){
          	$dx = Util::random(3) + 1;
		  }else{
          	$dx = Util::random(3) + 4;
		  }

		  if($ey>$y){
          	$dy = 1;
		  }elseif($ey == $y){
          	$dy = 0;
		  }else{
		  	$dy = -1;
		  }
		  $sx = $x + $init->ax[$dx];
		  $sy = $y + $dy;

          // 行による位置調整
          if((($sy % 2) == 0) && (($y % 2) == 1)) {
            $sx--;
          }

          // 範囲外判定
          if(($sx < 0) || ($sx >= $init->islandSize) ||
             ($sy < 0) || ($sy >= $init->islandSize)) {
            continue;
			//ここから下をカットしてforへ
          }

		  //上陸判定
		  //移動先のマス＋周囲1マス以内をチェック
			$rlanding = Turn::checkLand($land, $sx, $sy);
          if($rlanding != false) {
		   //陸地に隣接
		   //座標を1つ先へ
			$sx = $rlanding[0];
			$sy = $rlanding[1];
			$flanding = true;
            break;
			//上陸時は揚陸艦として1マス移動＋陸軍として揚陸艦から1マス移動＝計2マス移動
          }else{
		   //1マス以内に上陸可能地点無し
            break;
		  }
        }

		$insShip = Turn::countAround($land, $sx, $sy, $init->landSdefence, 19);
		if($insShip > 0){
			 $landValue[$x][$y] = floor($land[$x][$y]/10)*10+1;
		}else{
			 $landValue[$x][$y] = floor($land[$x][$y]/10)*10;
		}

        if($j == 3) {
          // 動かなかった
          break;
        }

        // 動いた先の地形によりメッセージ
        $l = $land[$sx][$sy];
        $lv = $landValue[$sx][$sy];
        $lName = $this->landName($l, $lv);
        $point = "({$sx}, {$sy})";

	   // 動いた元の地形によりメッセージ
        $ol = $land[$x][$y];
        $olv = $landValue[$x][$y];
        $olName = $this->landName($ol, $olv);
        $opoint = "({$x}, {$y})";

       if($flanding == true) {
	   //上陸メッセージ
	 	    $lv =  $init->monsterBHP[0] + Util::random($init->monsterDHP[0]);
            // そのヘックスを怪獣に
            $land[$sx][$sy] = $init->landMonster;
            $landValue[$sx][$sy] = $lv;
            // 怪獣情報
            $monsSpec = Util::monsterSpec($lv);
            $mName    = $monsSpec['name'];
	        // メッセージ
       	    $this->log->ZorasuMove($id, $name, $lName, $point);
       }else{
        // 移動
 	       $land[$sx][$sy] = $land[$x][$y];
	        $landValue[$sx][$sy] = $landValue[$x][$y];
       	    $this->log->Debuger($id, $name, $lName, $point,$opoint,$olName);
		}
        // もと居た位置を海に
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 0;

        // 移動ずみフラグ、セット
        $ZorasuMove[$sx][$sy] = 1;
        break;

      case $init->landMonster:
        // 怪獣
        if($monsterMove[$x][$y] == 2) {
          // すでに動いた後
          break;
        }

        // 各要素の取り出し
        $monsSpec = Util::monsterSpec($landValue[$x][$y]);
        $special  = $init->monsterSpecial[$monsSpec['kind']];
        $mName    = $monsSpec['name'];

        if((Turn::countAroundValue($island, $x, $y, $init->landProcity, 200, 7)+
		(Turn::countAround($island, $x, $y, $init->landFBase, 7))) && ($monsSpec['kind'] != 0)) {

          $this->log->BariaAttack($id, $name, $lName, "($x,$y)", $mName, $tPoint);
          // 対象の怪獣が倒れて荒地になる
          $land[$x][$y] = $init->landWaste;
          $landValue[$x][$y] = 0;

          // 収入
          $value = $init->monsterValue[$monsSpec['kind']];
          if($value > 0) {
            $island['money'] += $value;
            $this->log->msMonMoney($target, $mName, $value);
          }
          break;
        }

        // 動く方向を決定
        for($j = 0; $j < 3; $j++) {
          $d = Util::random(6) + 1;
          if($special & 0x200){
          // 飛行移動能力
          $d = Util::random(12) + 7;
          }
          $sx = $x + $init->ax[$d];
          $sy = $y + $init->ay[$d];

          // 行による位置調整
          if((($sy % 2) == 0) && (($y % 2) == 1)) {
            $sx--;
          }

          // 範囲外判定
          if(($sx < 0) || ($sx >= $init->islandSize) ||
             ($sy < 0) || ($sy >= $init->islandSize)) {
            continue;
          }
          // 海、海基、海防、海底都市、海底消防署、養殖場、油田、港、怪獣、山、山地、記念碑以外
          if(($land[$sx][$sy] != $init->landSea) &&
             ($land[$sx][$sy] != $init->landSdefence) &&
             ($land[$sx][$sy] != $init->landSeaCity) &&
             ($land[$sx][$sy] != $init->landSsyoubou) &&
             ($land[$sx][$sy] != $init->landSfarm) &&
             ($land[$sx][$sy] != $init->landNursery) &&
             ($land[$sx][$sy] != $init->landOil) &&
             ($land[$sx][$sy] != $init->landPort) &&
             ($land[$sx][$sy] != $init->landMountain) &&
             ($land[$sx][$sy] < 50 || $land[$sx][$sy] > 55) &&
             ($land[$sx][$sy] != $init->landMonument) &&
             ($land[$sx][$sy] != $init->landSleeper) &&
			 ($land[$sx][$sy] != $init->landnMountain) &&
             ($land[$sx][$sy] != $init->landMonster)) {
            break;
          }
        }

        if($j == 3) {
          // 動かなかった
          break;
        }

        // 動いた先の地形によりメッセージ
        $l = $land[$sx][$sy];
        $lv = $landValue[$sx][$sy];
        $lName = $this->landName($l, $lv);
        $point = "({$sx}, {$sy})";

        // 移動
        $land[$sx][$sy] = $land[$x][$y];
        $landValue[$sx][$sy] = $landValue[$x][$y];

        if (($special & 0x20000) && (Util::random(100) < 30)) { // 分裂確率30%
          // 分裂する怪獣
          // もと居た位置を怪獣に
          $land[$bx][$by] = $init->landMonster;
          $landValue[$bx][$by] = $lv;

          // 怪獣情報
          $monsSpec = Util::monsterSpec($lv);

          // メッセージ
          $this->log->monsBunretu($id, $name, $lName, $point, $mName);

          } else {
            // もと居た位置を荒地に
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        // 移動済みフラグ
        if($init->monsterSpecial[$monsSpec['kind']] & 0x2) {
          // 移動済みフラグは立てない
        } elseif($init->monsterSpecial[$monsSpec['kind']] & 0x1) {
          // 速い怪獣
          $monsterMove[$sx][$sy] = $monsterMove[$x][$y] + 1;
        } else {
          // 普通の怪獣
          $monsterMove[$sx][$sy] = 2;
        }
        if(($l == $init->landDefence) && ($init->dBaseAuto == 1)) {
          // 防衛施設を踏んだ
          $this->log->monsMoveDefence($id, $name, $lName, $point, $mName);

          // 広域被害ルーチン
          $this->wideDamage($id, $name, &$land, &$landValue, $sx, $sy);
        } else {
          // 行き先が荒地になる
          if($island['id'] != 1)
          $this->log->monsMove($id, $name, $lName, $point, $mName);
        }
        break;

      case $init->landSleeper:
        // 捕獲怪獣
        // 各要素の取り出し
        $monsSpec = Util::monsterSpec($landValue[$x][$y]);
        $special  = $init->monsterSpecial[$monsSpec['kind']];
        $mName    = $monsSpec['name'];

        if(Util::random(1000) < $monsSpec['hp'] * 10) {
          // (怪獣の体力 * 10)% の確率で捕獲解除
          $point = "({$x}, {$y})";
          $land[$x][$y] = $init->landMonster; // 捕獲解除
          $this->log->MonsWakeup($id, $name, $lName, $point, $mName);
        }
        break;
      }


//----- すでに$init->landTownがcase文で使われているのでswitchを別に用意
      switch($landKind) {
      case $init->landTown:
      case $init->landFactory:
      case $init->landBigtown:
	  case $init->landSeeCity:
	  case $init->landCapital:
        // 火災判定
        if(Util::random(1000) < $init->disFire - (int)($island['eisei'][0] / 20)) {
          // 周囲の森と記念碑を数える
          if((Turn::countAround($land, $x, $y, $init->landForest, 7) +
              Turn::countAround($land, $x, $y, $init->landProcity, 7) +
              Turn::countAround($land, $x, $y, $init->landFusya, 7) +
              Turn::countAround($land, $x, $y, $init->landMonument, 7)) == 0) {
            $l = $land[$x][$y];
            $lv = $landValue[$x][$y];
            $point = "({$x}, {$y})";
            $lName = $this->landName($l, $lv);
				 //無かった場合
              $landValue[$x][$y] -= Util::random(100) + 20;
              $this->log->firenot($id, $name, $lName, $point);
              if($landValue[$x][$y] <= 0) {
                $land[$x][$y] = $init->landWaste;
                $landValue[$x][$y] = 0;
                $this->log->fire($id, $name, $lName, $point);
              }
            }
          }
        break;
      }


//------ 船の移動
      if(Util::checkShip($landKind, $lv)){
        //座標が船の時
        if($shipMove[$x][$y] != 1){
          //船がまだ動いていない時
		if ($island['oil'] >= $init->shipMentenanceOil[$landValue[$x][$y]-2]){
            //石油の残りが消費量を超えているとき
          if($island['ship']['viking'] > 0 && $landValue[$x][$y] != 255){
		  //海賊船がいて対象が海賊船でないとき
            $cntViking = Turn::countAroundValue($island, $x, $y, $init->landSea, 255, 19);
            if($cntViking){
              //周囲2ヘックス以内に海賊船がいる
              if(($cntViking) && (Util::random(1000) < $init->disVikingAttack)){
                // 海賊船に沈没させられる
                $this->log->VikingAttack($id, $name, $this->landName($landKind, $lv), "($x,$y)");
                $land[$x][$y] = $init->landSea;
                $landValue[$x][$y] = 0;
                }
              }
            } elseif($island['ship']['viking'] > 0 && $landValue[$x][$y] == 255){
			//海賊船がいて対象が海賊船のとき
				if(Util::random(1000) < $init->disVikingAway){
	              // 海賊船 去る
	              $this->log->VikingAway($id, $name, "($x,$y)");
	              $island['ship']['viking']--;
	              $landValue[$x][$y] = 0;
				}else{
					$cntWarship =
					Turn::countAroundValue($island, $x, $y, $init->landSea, 5, 38) +
					Turn::countAround($land, $x, $y, $init->landSdefence, 19);
					//周辺に戦艦・防衛艦隊がいるかチェック
					if($cntWarship){
					//周囲2ヘックス以内に戦艦・防衛艦隊がいる
					$this->log->marineAttack($id, $name, "海上部隊", "($x,$y)", "海賊");
    	            $land[$x][$y] = $init->landSea;
	                $landValue[$x][$y] = 0;
					}
				}
            } elseif($landValue[$x][$y] == 5) {
              // 戦艦
              $lName = $this->landName($landKind, $lv);

              for($s = 0; $s < $init->pointNumber; $s++) {
                $sx = $this->rpx[$s];
                $sy = $this->rpy[$s];
                if(($land[$sx][$sy] == $init->landMonster)||
				  (($land[$sx][$sy] == $init->landZorasu)&&($landValue[$sx][$sy] % 10 == 1))) {
                  // 怪獣がいる場合、その怪獣を攻撃する$init->landMonster
                  // 対象となる怪獣の各要素取り出し
				  if($land[$sx][$sy] == $init->landZorasu){

				      $monsSpec['hp'] = floor($landValue[$sx][$sy]/10);
					  $tname = $this->landName($land[$x][$y]);

				  }else{
	                  $monsSpec = Util::monsterSpec($landValue[$sx][$sy]);
	                  $tLv = $landValue[$sx][$sy];
	                  $tspecial  = $init->monsterSpecial[$monsSpec['tkind']];
	                  $tname = $monsSpec['name'];
				  }

                  $this->log->senkanAttack($id, $name, $lName, "($x,$y)", $tname, $tPoint);

                  if($monsSpec['hp'] > 1){
				  	if($land[$sx][$sy] == $init->landZorasu){
						$landValue[$sx][$sy] -= 10;
					}
                    // 対象の体力を減らす
                    $landValue[$sx][$sy]--;
                  } else {
				   if($land[$sx][$sy] == $init->landZorasu){

                    $land[$sx][$sy] = $init->Sea;
					$value = 0;

				   }else{

                    // 対象の怪獣が倒れて荒地になる
                    $land[$sx][$sy] = $init->landWaste;
                    // 収入
                    $value = $init->monsterValue[$monsSpec['kind']];

					}
					$landValue[$sx][$sy] = 0;
                    if($value > 0) {
                      $island['money'] += $value;
                      $this->log->msMonMoney($target, $mName, $value);
                    }
                  }
                  break;
                }
              }
            }

            if ($landValue[$x][$y] != 0){
              //船がまだ存在していたら
              // 動く方向を決定
              for($j = 0; $j < 3; $j++) {
                $d = Util::random(6) + 1;
                $sx = $x + $init->ax[$d];
                $sy = $y + $init->ay[$d];

                // 行による位置調整
                if((($sy % 2) == 0) && (($y % 2) == 1)) {
                  $sx--;
                }

                // 範囲外判定
                if(($sx < 0) || ($sx >= $init->islandSize) ||
                  ($sy < 0) || ($sy >= $init->islandSize)) {
                  continue;
                }

                // 海であれば、動く方向を決定
                if(($land[$sx][$sy] == $init->landSea) && ($landValue[$sx][$sy] <= 1)){
                  break;
                }
              }

              if($j == 3) {
                // 動かなかった
              } else {
                // 移動
                $land[$sx][$sy] = $land[$x][$y];
                $landValue[$sx][$sy] = $landValue[$x][$y];

                if ($landValue[$x][$y] == 4) {
                  // 油田見っけ
                  if (Util::random(100) < 3) {
                    $lName = $this->landName($landKind, $lv);
                    $this->log->tansakuoil($id, $name, $lName, $point);
                    $island['oil']++;
                    $land[$x][$y] = $init->landOil;
                    $landValue[$x][$y] = Util::random(10) + 1;
                  } else {
                    // もと居た位置を海に
                    $land[$x][$y] = $init->landSea;
                    $landValue[$x][$y] = 0;
                  }
                } else {
                  // もと居た位置を海に
                  $land[$x][$y] = $init->landSea;
                  $landValue[$x][$y] = 0;
                }

                // 移動済みフラグ
                if(Util::random(2)){
                  $shipMove[$sx][$sy] = 1;
                }
              }
            }
			}
          }
        }
        //船の移動ここまで
      }
      // 変更された可能性のある変数を書き戻す
      $island['land'] = $land;
      $island['landValue'] = $landValue;
  }

  //---------------------------------------------------
  // 島全体
  //---------------------------------------------------
  function doIslandProcess($hako, &$island) {
    global $init;
    // 導出値
    $name = $island['name'];
    $id = $island['id'];
    $land = $island['land'];
    $landValue = $island['landValue'];
	$hapiness = $island['hapiness'];


	if ($island['invest'] > 0){
		//毎ターン0.1ずつ公共投資が劣化
		 $island['invest'] -= 0.1;
	}
	if ($island['point'] < $init->BaseHappiDemand[0]) {
	//後進国の間は下がらない
	}elseif ($island['home'] == 0){
	$island['siji'] -= 0.5;
	}else{
  	$island['siji'] -= 1;
	}
	if ($island['propaganda'] == 1) {
	      // パレード中
		$island['siji']  += 6;
	}

	if($island['siji'] + $hapiness >= 100){
		$island['siji'] = 100 - $hapiness;
	}elseif($island['siji'] < -$hapiness){
			$island['siji'] = -$hapiness;
	}
	if (($hapiness >= 40) && ($island['siji'] <= -40)){
		$island['siji'] = -40;
		}
	if($island['siji'] >= 40){
		$island['siji'] = 40;
	}




      // 自動輸送系
      // 記録済みベースデータ取得
	$regT = $island['regT'];//輸送データ取得
	if($island['bport'] == 1){
		$max = $init->regTMax;
	}else{
		$max = $init->regTsMax;
	}
    for($i = 0; $i < $max; $i++) {
			if(!empty($regT[$i])){
			$fport = true;
		    list($target,$kind,$arg) = split(",", $regT[$i]);

			$cost = $init->comCost[$kind];//コストを取得
			$tn = $hako->idToNumber[$target];//ターゲットナンバー取得
			$mn = $hako->idToNumber[$id];//自国ナンバー取得
			$tIsland = &$hako->islands[$tn];//ターゲット国変数一括取得
			$tName = $tIsland['name'];//ターゲット名取得
			$tport = $tIsland['port']+$tIsland['bport'];//ターゲット国港数取得
			$comName = $init->comName[$kind];

		  $container = "";//参照渡し用
		  $str = "";
		  if($tName == ""){
		  //国が無い時はログなしでループを飛ばす
		  $fport = false;
		  continue;
		  }
		  Turn::comTradesN($kind,$cost,$container,$str);
		  $ftrade=Turn::comTradeschk($kind,$hako->islandTurn);
		  if($tport == 0){
		  	$fport = false;
			$this->log->NoPortT($id, $name, $target,$tName);
		  	continue;
		  }
		  if($ftrade&&$fport){
		  	//輸送フラグがTRUEなら輸送
			  $value = Turn::comTrades($arg,$cost,$island[$container],$tIsland[$container]);
			  $str = "{$value}".$str;
		      // 輸出ログ
		      $this->log->Aid($id, $target, $name, $tName, $comName, $str);
			  $ftrade = false;//フラグをリセット
		  }
    	}
	}

    // 天気判定
    if($island['tenki'] > 0) {
      if(Util::random(100) < 5) {
        $island['tenki'] = 5;
      } elseif(Util::random(100) < 10) {
        $island['tenki'] = 4;
      } elseif(Util::random(100) < 15) {
        $island['tenki'] = 3;
      } elseif(Util::random(100) < 20) {
        $island['tenki'] = 2;
      } else {
        $island['tenki'] = 1;
      }
    } else {
      $island['tenki'] = 1;
    }

    // 日照り判定
    if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 1)) {
      // 日照り発生
      $this->log->Hideri($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landTown) && ($landValue[$x][$y] > 100)) {
          // 人口が減る
          $people = (Util::random(2) + 1);
          $landValue[$x][$y] -= $people;
        }
      }
    }

    // 地震判定
    if ((Util::random(1000) < (($island['prepare2'] + 1) * $init->disEarthquake) - (int)($island['eisei'][1] / 15))
        || ($island['present']['item'] == 1)) {
      // 地震発生
      $this->log->earthquake($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if((($landKind == $init->landTown) && ($lv >= 100)) ||
           (($landKind == $init->landProcity) && ($lv < 130)) ||
           (($landKind == $init->landSfarm) && ($lv < 20)) ||
           (($landKind == $init->landFactory) && ($lv < 100)) ||
           (($landKind == $init->landHatuden) && ($lv < 100)) ||
           ($landKind == $init->landHaribote) ||
           ($landKind == $init->landSeaCity) ||
		   ($landkind == $init->landSeeCity) ||
           ($landKind == $init->landSFactory) ||
           ($landKind == $init->landMFactory) ||
           ($landKind == $init->landFFactory) ||
		   (($landKind == $init->landBigtown) && ($lv >= 100)) ||
		   (($landkind == $init->landSeeCity) && ($lv >= 100)) ||
           (($landKind == $init->landNewtown) && ($lv >= 100)) ||
           (($landKind == $init->landIndCity) && ($lv >= 100)) ||
		   (($landKind == $init->landCapital) && ($lv >= 1000))) {
          // 1/8で被害
          if(Util::random(8) == 0) {
            $this->log->eQDamagenot($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $landValue[$x][$y] -= Util::random(100) + 20;
          }
          if($landValue[$x][$y] <= 0) {
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
            $this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            continue;
          }
        }
      }
    }

    // 不況判定
    if(Util::random(1000) < $init->disResession) {
      // 不況発生
      $this->log->Resession($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if ((($landKind == $init->landIndCity) && ($lv >= 100)) ||
		   (($landKind == $init->landBigtown) && ($lv >= 100))) {
		          // 1/8で被害
		          if(Util::random(8) == 0) {
		            $this->log->RsDamagenot($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
		            $landValue[$x][$y] -= Util::random(100) + 10;
		          }
		          if($landValue[$x][$y] <= 0) {
		            $land[$x][$y] = $init->landWaste;
		            $landValue[$x][$y] = 0;
		            $this->log->RsDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
		            continue;
				  }
		}
      }
    }

    // 食料不足
    if($island['food'] <= 0) {
      // 不足メッセージ
      $this->log->Starve($id, $name, "食料");
      $island['food'] = 0;

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landFarm) ||
           ($landKind == $init->landSfarm) ||
           ($landKind == $init->landFFactory) ||
           ($landKind == $init->landSFactory) ||
           ($landKind == $init->landMFactory) ||
           ($landKind == $init->landFactory) ||
           ($landKind == $init->landHDefence) ||
           ($landKind == $init->landDefence)) {
          // 1/8で壊滅
          if(Util::random(8) == 0) {
            $this->log->svDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            // でも養殖場なら浅瀬
            if($landKind == $init->landNursery) {
              $land[$x][$y] = $init->landSea;
              $landValue[$x][$y] = 1;
            } elseif($landKind == $init->landSfarm) {
              $land[$x][$y] = $init->landSea;
              $landValue[$x][$y] = 0;
            }
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }
      }
    }

    // 座礁判定
    if(Util::random(1000) < $init->disRunAground1){
      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];
        if((Util::checkShip($landKind, $lv)) && (Util::random(1000) < $init->disRunAground2)){
          $this->log->RunAground($id, $name, $this->landName($landKind, $lv), "($x,$y)");
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 0;
        }
      }
    }

    // 海賊船判定
    if($island['money'] >= 30000) {
      $vik = Util::random(800);
    } else {
      $vik = Util::random(1000);
    }
    if($vik < ($init->disViking * ($island['ship']['passenger'] + $island['ship']['fishingboat'] + $island['ship']['tansaku'] + $island['ship']['senkan']))){
      // どこに現れるか決める
      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];
        if(($landKind == $init->landSea) && ($lv == 0)) {
          // 海賊船登場
          $landValue[$x][$y] = 255; //lv 255 が海賊船
          $this->log->VikingCome($id, $name, "($x,$y)");
        break;
        }
      }
    }

    // 津波判定
    if ((Util::random(1000) < $init->disTsunami - (int)($island['eisei'][1] / 15))
        || ($island['present']['item'] == 2)) {
      // 津波発生
      $this->log->tsunami($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landTown) ||
          (($landKind == $init->landProcity) && ($lv < 110)) ||
           ($landKind == $init->landNewtown) ||
           (($landKind == $init->landBigtown)&& ($lv < 300)) ||
		   (($landKind == $init->landCapital)&& ($lv < 110)) ||
           (($landKind == $init->landFarm) && ($lv < 25)) ||
           ($landKind == $init->landNursery) ||
           ($landKind == $init->landSFactory) ||
           ($landKind == $init->landMFactory) ||
           ($landKind == $init->landFFactory) ||
           ($landKind == $init->landMarket) ||
           ($landKind == $init->landFactory) ||
           ($landKind == $init->landHatuden) ||
           ($landKind == $init->landBase) ||
           ($landKind == $init->landHBase) ||
           ($landKind == $init->landDefence) ||
           ($landKind == $init->landHDefence) ||
           ($landKind == $init->landPort)     ||
		   ($landKind == $init->landSeeCity)  ||
           (Util::checkShip($landKind,$lv))   ||
           ($landKind == $init->landHaribote)) {
          // 1d12 <= (周囲の海 - 1) で崩壊
          if(Util::random(12) <
             (Turn::countAround($land, $x, $y, $init->landOil, 7) +
              Turn::countAround($land, $x, $y, $init->landSea, 7) - 1)) {
            $this->log->tsunamiDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            if (($landKind == $init->landSeaSide)||
                ($landKind == $init->landNursery)||
                ($landKind == $init->landPort)){
               //砂浜か養殖場か港なら浅瀬に
               $land[$x][$y] = $init->landSea;
               $landValue[$x][$y] = 1;
            } elseif(Util::checkShip($landKind,$lv)){
               //船なら水没、海に
               $land[$x][$y] = $init->landSea;
               $landValue[$x][$y] = 0;
            } else {
              $land[$x][$y] = $init->landWaste;
              $landValue[$x][$y] = 0;
            }
          }
        }
      }
    }

    // 怪獣判定
    $r = Util::random(10000);
    $pop = $island['pop'];
    $isMons = (($island['present']['item'] == 3) && ($pop >= $init->disMonsBorder1));
    do{
      if((($r < ($init->disMonster * $island['area'])) &&//出現確率クリア
	      ($pop >= $init->limitpop) && //リミット人口クリア
	  	  (($hako->islandTurn - $island['starturn']) > $init->noMissile)) ||//ターン数ミサイル規制クリア
		  ($isMons) || ($island['monstersend'] > 0))//プレゼントor怪獣派遣or上陸
		  {
        // 怪獣出現
        // 種類を決める
        if($island['monstersend'] > 0) {
          // 揚陸艦
		  $fzorasu = true;
          $island['monstersend']--;
        }elseif($pop >= $init->disMonsBorder3) {
          // level3まで
          $kind = Util::random($init->monsterLevel3) + 1;
        } elseif($pop >= $init->disMonsBorder2) {
          // level2まで
          $kind = Util::random($init->monsterLevel2) + 1;
        } else {
          // level1のみ
          $kind = Util::random($init->monsterLevel1) + 1;
        }

        // lvの値を決める
		if($fzorasu == true){
    		$lv = 20;
		}else{
		    $lv = $kind * 100
	          + $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
		}
        // どこに現れるか決める
		if($fzorasu == true){
			$clcel = array();
			for($i = 0; $i < $init->islandSize; $i++){
				//横方向
				if($land[$i][0]==$init->landSea){
					array_push($clcel,"{$i},0");
				}
				if($land[$i][19]==$init->landSea){
					array_push($clcel,"{$i},19");
				}
				//縦方向
				if($land[0][$i]==$init->landSea){
					array_push($clcel,"0,{$i}");
				}
				if($land[19][$i]==$init->landSea){
					array_push($clcel,"19,{$i}");
				}
			}
			$clcel = array_unique($clcel);
			if(count($clcel)!=0){
				//海がある
				$pnum = Util::random(count($clcel));
				$bx = substr($clcel[$pnum],0,strpos($clcel[$pnum],","));
				$by = strrchr($clcel[$pnum],",");
				$by = substr($by,1);

	            // そのヘックスを揚陸艦に
	            $land[$bx][$by] = $init->landZorasu;
	            $landValue[$bx][$by] = $lv;
	            // メッセージ
	            $this->log->ZorasuCome($id, $name, "({$bx}, {$by})");
				$fzorasu = false;

			}else{
				//1マスも海が無い
				$fz = array(0,Util::random($init->islandSize-4)+2);
				$nz = array(19,Util::random($init->islandSize-4)+2);
				$sd = Util::random(2);
				$ch = Util::random(2);
				if($sd == 0){
					$bx = $fz[$ch];
					$by = $fz[1-$ch];
				}elseif($sd == 1){
					$bx = $nz[$ch];
					$by = $nz[1-$ch];
				}
				// そのヘックスを敵国軍に
		 	    $lv =  $init->monsterBHP[0] + Util::random($init->monsterDHP[0]);
	            $land[$bx][$by] = $init->landMonster;
	            $landValue[$bx][$by] = $lv;
	            // 怪獣情報
	            $monsSpec = Util::monsterSpec($lv);
	            $mName    = $monsSpec['name'];
		        // メッセージ
	            $this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
				$fzorasu = false;
			}

		}else{
	        for($i = 0; $i < $init->pointNumber; $i++) {
	          $bx = $this->rpx[$i];
	          $by = $this->rpy[$i];
	          if(($land[$bx][$by] == $init->landTown) ||
			   ($land[$bx][$by] == $init->landNewtown) ||
			   ($land[$bx][$by] == $init->landSeeCity)){

	            // 地形名
	            $lName = $this->landName($init->landTown, $landValue[$bx][$by]);

	            // そのヘックスを怪獣に
	            $land[$bx][$by] = $init->landMonster;
	            $landValue[$bx][$by] = $lv;

	            // 怪獣情報
	            $monsSpec = Util::monsterSpec($lv);
	            $mName    = $monsSpec['name'];

	            // メッセージ
	            $this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
	            break;
	          }
	        }
		}

      }
    } while($island['monstersend'] > 0);

    // 地盤沈下判定
    if((($island['area'] > $init->disFallBorder) &&
       (Util::random(1000) < $init->disFalldown)) || ($island['present']['item'] == 4)) {
      // 地盤沈下発生
      $this->log->falldown($id, $name,$island['area']);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind != $init->landSea) &&
           ($landKind != $init->landSdefence) &&
           ($landKind != $init->landSfarm) &&
           ($landKind != $init->landOil) &&
           ($landKind != $init->landMountain)) {

          // 周囲に海があれば、値を-1に
          if(Turn::countAround($land, $x, $y, $init->landSea, 7)){
            $this->log->falldownLand($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = -1;
            $landValue[$x][$y] = 0;
          }
        }
      }

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];

        if($landKind == -1) {
          // -1になっている所を浅瀬に
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 1;
        } elseif ($landKind == $init->landSea) {
          // 浅瀬は海に
          $landValue[$x][$y] = 0;
        }

      }
    }

    // 台風判定
    if ((Util::random(1000) < ($init->disTyphoon - (int)($island['eisei'][0] / 10))) && (($island['tenki'] == 2) || ($island['tenki'] == 3))
        || ($island['present']['item'] == 5)) {
      // 台風発生
      $this->log->typhoon($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if((($landKind == $init->landFarm) && ($lv < 25)) ||
           (($landKind == $init->landSfarm) && ($lv < 20)) ||
           ($landKind == $init->landNursery) ||
           ($landKind == $init->landHaribote)) {

          // 1d12 <= (6 - 周囲の森) で崩壊
          if(Util::random(12) <
             (6
              - Turn::countAround($land, $x, $y, $init->landForest, 7)
              - Turn::countAround($land, $x, $y, $init->landFusya, 7)
              - Turn::countAround($land, $x, $y, $init->landMonument, 7))) {
            $this->log->typhoonDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            if ($landKind == $init->landNursery){
               //養殖場ならは浅瀬
               $land[$x][$y] = $init->landSea;
               $landValue[$x][$y] = 1;
            } else {
               //その他は平地に
               $land[$x][$y] = $init->landPlains;
               $landValue[$x][$y] = 0;
            }
          }
        }
      }
    }

    // 巨大隕石判定
    if (((Util::random(1000) < ($init->disHugeMeteo - (int)($island['eisei'][2] / 50))))
        || ($island['present']['item'] == 6)) {

      // 落下
      $x = Util::random($init->islandSize);
      $y = Util::random($init->islandSize);
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];
      $point = "({$x}, {$y})";

      // メッセージ
      $this->log->hugeMeteo($id, $name, $point);

      // 広域被害ルーチン
      $this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
    }

    // 隕石判定
    if ((Util::random(1000) < ($init->disMeteo - (int)($island['eisei'][2] / 40)))
        || ($island['present']['item'] == 7)) {
      $first = 1;
      while((Util::random(2) == 0) || ($first == 1)) {
        $first = 0;

        // 落下
        if (($island['present']['item'] == 7) && ($first == 1)) {
          $x = $island['present']['px'];
          $y = $island['present']['py'];
        } else {
          $x = Util::random($init->islandSize);
          $y = Util::random($init->islandSize);
        }
        $first = 0;
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];
        $point = "({$x}, {$y})";

        if(($landKind == $init->landSea) && ($lv == 0)){
          // 海ポチャ
          $this->log->meteoSea($id, $name, $this->landName($landKind, $lv), $point);
        } elseif(($landKind == $init->landMountain) ||
                ($landKind >= 50 && $landKind < 55)) {
          // 山破壊
          $this->log->meteoMountain($id, $name, $this->landName($landKind, $lv), $point);
          $land[$x][$y] = $init->landWaste;
          $landValue[$x][$y] = 0;
          continue;
        } elseif(($landKind == $init->landSfarm) ||
                 ($landKind == $init->landSeaCity) ||
				 ($landKind == $init->landSdefence)) {
          $this->log->meteoSbase($id, $name, $this->landName($landKind, $lv), $point);
        } elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
          $this->log->meteoMonster($id, $name, $this->landName($landKind, $lv), $point);
        } elseif($landKind == $init->landSea) {
          // 浅瀬
          $this->log->meteoSea1($id, $name, $this->landName($landKind, $lv), $point);
        } else {
          $this->log->meteoNormal($id, $name, $this->landName($landKind, $lv), $point);
        }
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 0;
      }
    }

    // 大雨判定
    if((Util::random(1000) < $init->disHardRain) && ($island['tenki'] == 2)) {
      // 大雨発生
      $raflg = $raflg2 = $raflg3 = 0;
      if(Util::random(1000) < $init->disHardRain2) {
        $this->log->HardRain2($id, $name);
        $raflg = 1;
        if(Util::random(1000) < $init->disHardRain3) {
          $raflg2 = 1;
        }
      }else{
        $this->log->HardRain($id, $name);
        if(Util::random(1000) < $init->disHardRain3) {
          $raflg3 = 1;
        }
      }
      $flag = 0;
      // 森を検索する
      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $point = "({$x}, {$y})";
        if(($landKind == $init->landForest) && ($landValue[$x][$y] < 300)) {
          if($raflg == 1) {
            // 木を減らす
            if($raflg2 == 1) {
              $land[$x][$y] = $init->landWaste;
              $landValue[$x][$y] = 0;
              $this->log->NoTree($id, $name, $point);
              $raflg2 = 0;
            }else{
              $landValue[$x][$y] -= (2 + Util::random(5));
              $flag = 2;
              if($landValue[$x][$y] < 1) {
                // 木の数が０本以下になったら荒地にする
                $land[$x][$y] = $init->landWaste;
                $landValue[$x][$y] = 0;
                // ログ
                $this->log->NoTree($id, $name, $point);
              }
            }
          } else {
            // 木を増やす
            $landValue[$x][$y] += (1 + Util::random(10));
            // 250万本超えていたら切り捨て
            if($landValue[$x][$y] > 250) {
                $landValue[$x][$y] = 250;
            }

            $flag = 1;
          }
        } else if(($landKind == $init->landPlains) && ($raflg3 == 1)) {
          $land[$x][$y] = $init->landForest;
          $landValue[$x][$y] = 3;
          $this->log->NewTree($id, $name);
          $raflg3 = 0;
        }
      }
      if($flag == 1) {
        // 木が増えたらメッセージ
        $this->log->IncTree($id, $name);
      } elseif($flag == 2){
        // 木が減ってもメッセージ
        $this->log->IncTree2($id, $name);
      }
    }

    // 噴火判定
    if (($island['mountain'] < $init->haveMountain) && (Util::random(1000) < ($init->disEruption - (int)($island['eisei'][1] / 40)))
        || ($island['present']['item'] == 8)) {
      if ( $island['present']['item'] == 8 ) {
        $x = $island['present']['px'];
        $y = $island['present']['py'];
      } else {
        $x = Util::random($init->islandSize);
        $y = Util::random($init->islandSize);
      }
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];
      $point = "({$x}, {$y})";
      $this->log->eruption($id, $name, $this->landName($landKind, $lv), $point);
      $land[$x][$y] = $init->landMountain;
      $landValue[$x][$y] = 0;

      for($i = 1; $i < 7; $i++) {
        $sx = $x + $init->ax[$i];
        $sy = $y + $init->ay[$i];

        // 行による位置調整
        if((($sy % 2) == 0) && (($y % 2) == 1)) {
          $sx--;
        }

        $landKind = $land[$sx][$sy];
        $lv = $landValue[$sx][$sy];
        $point = "({$sx}, {$sy})";

        if(($sx < 0) || ($sx >= $init->islandSize) ||
           ($sy < 0) || ($sy >= $init->islandSize)) {
        } else {
          // 範囲内の場合
          $landKind = $land[$sx][$sy];
          $lv = $landValue[$sx][$sy];
          $point = "({$sx}, {$sy})";
          if(($landKind == $init->landSea) ||
             ($landKind == $init->landOil) ||
             ($landKind == $init->landSeaCity) ||
             ($landKind == $init->landSsyoubou) ||
             ($landKind == $init->landSfarm) ||
             ($landKind == $init->landSdefence)) {
            // 海の場合
            if($lv == 1) {
              // 浅瀬
              $this->log->eruptionSea1($id, $name, $this->landName($landKind, $lv), $point);
            } else {
              $this->log->eruptionSea($id, $name, $this->landName($landKind, $lv), $point);
              $land[$sx][$sy] = $init->landSea;
              $landValue[$sx][$sy] = 1;
              continue;
            }
          } elseif(($landKind == $init->landMountain) ||
                  ($landKind >= 50 && $landKind < 55) ||
                  ($landKind == $init->landMonster) ||
                  ($landKind == $init->landSleeper) ||
                  ($landKind == $init->landWaste)) {
            continue;
          } else {
            // それ以外の場合
            $this->log->eruptionNormal($id, $name, $this->landName($landKind, $lv), $point);
          }
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        }
      }
    }
	$rflag = true;
	if(($hako->islandTurn - $island['starturn']) < $init->noMissile){
		//ミサイル発射可能ターンを超えたか？
		$rflag = false;
	}
	// 幸福度デモ&暴動
	if($rflag = true){
	if($island['riot'] == 1) {
				//暴動発生
			        if(!($island['money'] < $init->disVikingMinMoney)){
			            $vMoney = round(Util::random($island['money'])/5);
			            $this->log->gvRob($island['id'], $island['name'], $vMoney);
			            $island['money'] -= $vMoney;
			            if($island['money'] < 0) $island['money'] = 0;
			        	}
		    } elseif (($island['siji'] + $hapiness) <= 25) {
			 // デモメッセージ
			 if(($island['polit'] != 2)||($island['policestat'] == 1)){
		      	$this->log->gvDemo($id, $name);
			  }else{
				$this->log->Cutsecpol($id, $name);
			  }
			} elseif (($island['siji'] + $hapiness) <= 35) {
			// 警告メッセージ
			 if(($island['polit'] != 2)||($island['policestat'] == 1)){
				  $this->log->gvAlert($id, $name);
			  }else{
				$this->log->Cutsecpol($id, $name);
			  }
			}

		}

    // 人工衛星エネルギー減少
    for($i = 0; $i < 6; $i++) {
      if($island['eisei'][$i]) {
        $island['eisei'][$i] -= Util::random(2);
        if($island['eisei'][$i] < 1) {
          $island['eisei'][$i] = 0;
          $this->log->EiseiEnd($id, $name, $init->EiseiName[$i]);
        }
      }
    }

    // 変更された可能性のある変数を書き戻す
    $island['land'] = $land;
    $island['landValue'] = $landValue;

    $island['gold'] = $island['money'] - $island['oldMoney'];
    $island['rice'] = $island['food'] - $island['oldFood'];

    // 食料があふれてたら切り捨て
    if($island['food'] > $init->maxFood) {
      $island['food'] = $init->maxFood;
    }

    // 金があふれてたら切り捨て
    if($island['money'] > $init->maxMoney) {
      $island['money'] = $init->maxMoney;
    }

    // 銀があふれてたら切り捨て
    if($island['silver'] > $init->maxSilver) {
      $island['silver'] = $init->maxSilver;
    }

    // 鉄鋼があふれてたら切り捨て
    if($island['steel'] > $init->maxSteel) {
      $island['steel'] = $init->maxSteel;
    }

    // 資材があふれてたら切り捨て
    if($island['material'] > $init->maxMaterial) {
      $island['material'] = $init->maxMaterial;
    }

    // 砲弾があふれてたら切り捨て
    if($island['shell'] > $init->maxShell) {
      $island['shell'] = $init->maxShell;
    }

    // 燃料があふれてたら切り捨て
    if($island['fuel'] > $init->maxFuel) {
      $island['fuel'] = $init->maxFuel;
    }
    // 木材があふれてたら切り捨て
    if($island['wood'] > $init->maxWood) {
      $island['wood'] = $init->maxWood;
    }

    // 石材があふれてたら切り捨て
    if($island['stone'] > $init->maxStone) {
      $island['stone'] = $init->maxStone;
    }

    // 酒があふれてたら切り捨て
    if($island['alcohol'] > $init->maxAlcohol) {
      $island['alcohol'] = $init->maxAlcohol;
    }

    // 石油があふれてたら切り捨て
    if($island['oil'] > $init->maxOil) {
      $island['oil'] = $init->maxOil;
    }

    // 商品があふれてたら切り捨て
    if($island['goods'] > $init->maxGoods) {
      $island['goods'] = $init->maxGoods;
    }

    // 各種の値を計算
    Turn::estimate($island);

    // 繁栄、災難賞
    $pop = $island['pop'];
    $damage = $island['oldPop'] - $pop;
    $prize = $island['prize'];
    list($flags, $monsters, $turns) = split(",", $prize, 3);

    $island['peop'] = $island['pop'] - $island['oldPop'];
    $island['pots'] = $island['point'] - $island['oldPoint'];

    // 繁栄賞
    if((!($flags & 1)) &&  $pop >= 3000){
      $flags |= 1;
      $this->log->prize($id, $name, $init->prizeName[1]);
    } elseif((!($flags & 2)) &&  $pop >= 5000){
      $flags |= 2;
      $this->log->prize($id, $name, $init->prizeName[2]);
    } elseif((!($flags & 4)) &&  $pop >= 10000){
      $flags |= 4;
      $this->log->prize($id, $name, $init->prizeName[3]);
    }

    // 災難賞
    if((!($flags & 64)) &&  $damage >= 500){
      $flags |= 64;
      $this->log->prize($id, $name, $init->prizeName[7]);
    } elseif((!($flags & 128)) &&  $damage >= 1000){
      $flags |= 128;
      $this->log->prize($id, $name, $init->prizeName[8]);
    } elseif((!($flags & 256)) &&  $damage >= 2000){
      $flags |= 256;
      $this->log->prize($id, $name, $init->prizeName[9]);
    }

    $island['prize'] = "{$flags},{$monsters},{$turns}";

  }

  //---------------------------------------------------
  // 周囲の町、農場があるか判定
  //---------------------------------------------------
  function countGrow($land, $landValue, $x, $y) {
    global $init;

    for($i = 1; $i < 7; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
      } else {
        // 範囲内の場合
        if(($land[$sx][$sy] == $init->landTown) ||
           ($land[$sx][$sy] == $init->landProcity) ||
           ($land[$sx][$sy] == $init->landNewtown) ||
           ($land[$sx][$sy] == $init->landBigtown) ||
		   ($land[$sx][$sy] == $init->landCapital) ||
           ($land[$sx][$sy] == $init->landFarm)) {
          if($landValue[$sx][$sy] != 1) {
            return true;
          }
        }
      }
    }
    return false;
  }
  //---------------------------------------------------
  // 広域被害ルーチン
  //---------------------------------------------------
  function wideDamage($id, $name, $land, $landValue, $x, $y) {
    global $init;

    for($i = 0; $i < 19; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      $landKind = $land[$sx][$sy];
      $lv = $landValue[$sx][$sy];
      $landName = $this->landName($landKind, $lv);
      $point = "({$sx}, {$sy})";

      // 範囲外判定
      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
        continue;
      }

      // 範囲による分岐
      if($i < 7) {
        // 中心、および1ヘックス
        if($landKind == $init->landSea) {
          $landValue[$sx][$sy] = 0;
          continue;
        } elseif(($landKind == $init->landSdefence) ||
                 ($landKind == $init->landSeaCity) ||
                 ($landKind == $init->landSsyoubou) ||
                 ($landKind == $init->landSfarm) ||
                 ($landKind == $init->landZorasu) ||
                 ($landKind == $init->landOil)) {
          $this->log->wideDamageSea2($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landSea;
          $landValue[$sx][$sy] = 0;
        } else {
          if(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
            $this->log->wideDamageMonsterSea($id, $name, $landName, $point);
          } else {
            $this->log->wideDamageSea($id, $name, $landName, $point);
          }
          $land[$sx][$sy] = $init->landSea;
          if($i == 0) {
            // 海
            $landValue[$sx][$sy] = 0;
          } else {
            // 浅瀬
            $landValue[$sx][$sy] = 1;
          }
        }
      } else {
        // 2ヘックス
        if(($landKind == $init->landSea) ||
           ($landKind == $init->landOil) ||
           ($landKind == $init->landWaste) ||
           ($landKind == $init->landMountain) ||
           ($landKind >= 50 && $landKind < 55) ||
           ($landKind == $init->landSeaCity) ||
           ($landKind == $init->landSsyoubou) ||
           ($landKind == $init->landSfarm) ||
           ($landKind == $init->landSdefence)) {
          continue;
        } elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
          $this->log->wideDamageMonster($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        } else {
          $this->log->wideDamageWaste($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        }
      }
    }
  }

  //---------------------------------------------------
  // 広域被害ルーチン2
  //---------------------------------------------------
  function oilwideDamage($id, $name, $land, $landValue, $x, $y) {
    global $init;

    for($i = 0; $i < 7; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      $landKind = $land[$sx][$sy];
      $lv = $landValue[$sx][$sy];
      $landName = $this->landName($landKind, $lv);
      $point = "({$sx}, {$sy})";

      // 範囲外判定
      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
        continue;
      }

      // 範囲による分岐
      if($i < 7) {
        // 中心、および1ヘックス
        if(($landKind == $init->landMonster) ||
           ($landKind == $init->landSea) ||
           ($landKind == $init->landWaste) ||
           ($landKind == $init->landMountain) ||
		   ($landKind == $init->landnMountain) ||
           ($landKind >= 50 && $landKind < 55)) {
          continue;
        } else {
          $this->log->WideDamageWaste($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landWaste;
        }
      } else {
        $land[$sx][$sy] = $init->landSea;
        $landValue[$sx][$sy] = 1;
      }
    }
  }

  //---------------------------------------------------
  // 人口順でソート
  //---------------------------------------------------
  function islandSort(&$hako) {
    global $init;
    usort($hako->islands, 'popComp');
  }
  //---------------------------------------------------
  // 収入、消費フェイズ
  //---------------------------------------------------
  function income(&$island) {
    global $init;

    $pop = $island['pop'];
    $farm = $island['farm'] * 10;
    $pfarm = $island['farm'];
    $factory = $island['factory'];
    $market =$island['market'];
    $hatuden = $island['hatuden'];
	$goods	= $island['goods'];
	$invest	= $island['invest'];
	$edinv	= $island['edinv'];
	$soclv = $island['soclv'];
	$service = $island['service'];
    $name = $island['name']; // ログ出力用
    $id = $island['id']; // ログ出力用
    $pfuel = 0; // ログ出力用

    if(($island['wood'] > 0) && (($island['pop'] / 100) >= 1)){
      $num  = (int)($island['pop'] / 100);
      $num2 = (int)($island['wood'] / 2);
      $num3 = (int)($island['wood'] % 2);
      if($island['wood'] < ($num*2)){
        $island['goods'] += $num2;
        $island['material'] += (($num2)+($num3));
        $island['wood'] = 0;
		$num4 = $num2+$num3;
		$this->log->DoNothing2($id, $name, $num2, $num4);
      }else{
        $island['goods'] += $num;
        $island['material'] += $num;
        $island['wood'] -= ($num*2);
	  	$this->log->DoNothing2($id, $name, $num, $num);
      }
    }

    if($island['fuel'] > 0){
      // 燃料があるときだけ(負荷軽減)
      // 初期条件
      $bfarm = $bfactory = $bmarket = 100;
      $wfarm = $wfactory = $wmarket = $pgoods = 0;

      if ($pfarm + $factory + $market != 0) {
        // 特化判定
      // 工業生産性
		$perind = $factory / ($service + $pfarm + $factory + $market);
		$bfactory =(int)(500 * $perind * sqrt(1.5- $perind * $perind));
		$bfactory =(int)($bfactory * ($invest/100 + $init->BaseIndust[0]/100) * ($edinv/100 + $init->BaseIndust[0]/100));
		//農業生産性

		//0.8^(0.4^y+0.4^x)+0.25
		$perind = $pfarm  / ($service +  $pfarm + $factory + $market);
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
		$perind = $market / ($service + $pfarm + $factory + $market);
		$bmarket = (int)($base * $perind * sqrt(1.5- $perind * $perind));
		$bmarket =(int)($bmarket * ($invest/100 + $init->BaseIndust[2]/100) * ($edinv/100 + $init->BaseIndust[2]/100));
      } // 特化判定閉じ

      // 商業稼動規模決定
      $wmarket = min((int)($pop / 10) - $service, $market);
      // 工場稼動規模決定
	  if($goods == $init->maxGoods){
	  	$this->log->ProductStop($id, $name);
	  }else{
	      if(((int)($pop / 10) -$service - $market) > 0){
		    //商業で労働力が食い尽くされてない場合
		    //wfactory=余剰労働力or最大雇用力
	        $wfactory = min(((int)($pop / 10) - $service - $market), $factory);
	        $pfactory = $wfactory;
			}
	  }

		// 商品製造詳細(工業政策で分岐)
		//燃料消費量計算
		$fcoms = $init->Industfcoms[$island['indnum']];
		if($island['indnum'] == 2){
			$resource = $island['silver']*$fcoms;
		}elseif($island['indnum'] == 1){
			$resource =  $island['steel']*$fcoms;
		}else{
			$fcoms = 1;
			$resource =  $island['food'];
		}
		$pfuel += min($pfactory*$fcoms,$resource);

      // 農業稼動規模決定
      if(((int)($pop / 10) - $service - $market - $factory) > 0){
          $wfarm = min(((int)($pop / 10) - $service- $market - $factory), $pfarm);
      } else {
          $wfarm = 0;
      }
      if($island['fuel'] > (int)($wfarm * 0.4)) {
        // 農場稼動
        $island['fuel'] -= (int)($wfarm * 0.4);
		$pfood = (int)($wfarm * (10 + $bfarm / 10));
        $island['food'] += $pfood;
		//発電所稼動
		if($hatuden >= 800){
		$hatuden = 800;
		}
		if($island['fuel'] > (int)($hatuden * 0.25)) {
		$island['fuel'] -= (int)($hatuden * 0.25); // 燃料徴収

//----------------------------------------------------------------------
        if($island['fuel'] > (int)($pfuel)) {
          // 工場稼動
          if($wfactory > 0){
            $qfuel = (int)($pfuel);
          }
          $island['fuel'] -= (int)($pfuel); // 燃料徴収

          // 商品製造詳細
  //原料消費量計算
  //銀1：商品3.5、鉄1：商品1.5、食料2：商品1
  //労1：商品2.5、労1：商品1.5、労2：商品：1
		   if($island['indnum'] == ""){
		   	$indt = 0;
		   }else{
		   	$indt = $island['indnum'];
		   }
		  	$rescoms = $init->Industcoms[$indt]/10;
			$mancoms = $init->Industmcoms[$indt]/10;
		if($island['indnum'] == 2){
			$resource = $island['silver'];
			$Nameres = 'silver';
		  }elseif($island['indnum'] == 1){
			$resource = $island['steel'];
			$Nameres = 'steel';
		  }else{
			$resource = $island['food'];
			$Nameres = 'food';
		  }
			$prod =  min($pfactory*$mancoms,$resource*$rescoms);
		  	$pgoods += $prod;
			$island[$Nameres] -= round($prod/$rescoms);
			if($island[$Nameres]< 0){
				$island[$Nameres] = 0;
			}

          if($pgoods > 0){
		  	$sgoods = (int)($pgoods * (1 + $bfactory / 100) * (1 + $hatuden / 1000));
            $island['goods'] += $sgoods; // ここで商品増加
          }
          if($island['fuel'] > (int)($wmarket * 0.2)) {
            // 市場稼動
            if($island['goods'] > 0 ){
              // 商品があるときだけ
			  $wmarket = ceil($wmarket *(1 + $bmarket / 100));
              $pmarket = min($island['goods'], $wmarket);
              $island['fuel'] -= (int)($pmarket * 0.2);
			  $smoney = ceil($pmarket);
              $island['money'] += $smoney;
              $island['goods'] -= $pmarket;
            }
          } else {
            // 不足メッセージ
            $this->log->Starve($id, $name, "燃料");
          }
		  $island['p_goods'] = $sgoods;
		  $island['p_moneys'] = $smoney;
		  //$ssgoods = "I/".$pgoods."*".ceil((1 + $bfactory / 100) * (1 + $hatuden / 1000))."O/".$sgoods."R/".$res;
		  $this->log->ShowProduct($id,$name,$sgoods,$smoney);
//----------------------------------------------------------------------
        } else {
          // 不足メッセージ
          $this->log->Starve($id, $name, "燃料");
        }
//----------------------------------------------------------------------
	  } else {
        // 不足メッセージ
        $this->log->Starve($id, $name, "燃料");
		}
      } else {
        // 不足メッセージ
        $this->log->Starve($id, $name, "燃料");
      }
    }//燃料がある場合の処理ここまで

//－－プレゼント系
    if ( $island['present']['item'] == 0 ) {
      if ( $island['present']['px'] != 0 ) {
        $island['money'] += $island['present']['px'];
        $this->log->presentMoney($island['id'], $island['name'], $island['present']['px']);
      }
      if ( $island['present']['py'] != 0 ) {
        $island['material'] += $island['present']['py'];
        $this->log->presentFood($island['id'], $island['name'], $island['present']['py']);
      }
    }
//――食肉生産

if($island['ffactory']> 0){
	$value =  $island['ffactory']*7;//*10000＊0.01/2＝*50（１頭当たり50％が食肉化、単位を万に揃える）
	$island['alcohol'] += $value;
//  	$this->log->Make($id, $name, "食肉生産", $value, $init->unitAlcohol);
}

    // 食料消費
    $island['food'] = round($island['food'] - $pop * $init->eatenFood - $value * 10 * $init->eatenFood);

	//畜産幸福
	if($island['alcohol'] > 0){
		$mneeds = round($pop*2.7/30);
		$mcoms = min($mneeds,$island['alcohol']);
		$percat = round($island['alcohol']/$mneeds*10);
		if($percat > 10){
			$percat = 10;
		}
		$island['percat'] = $percat;
		$island['alcohol'] -= $mcoms;
		if($island['alcohol'] < 0){
			$island['alcohol'] = 0;
		}
//	$this->log->Make($id, $name, "幸福度上昇",$island['percat'], "");
//    $this->log->Make($id, $name, "食肉の消費", $mcoms, $init->unitAlcohol);
	}
	if($pfood * 18 > $island['food']){
		//過去18ターン生産高が備蓄を上回っている場合生産高を0にする
		$pfood = 0;
	}
	//そして飯は腐った
	$island['food'] -= ceil(($island['food']-$pfood*18)*0.005);
	// 観光収入
	$island['money'] += round($island['spop'] * 0.3 *(40 + $island['invest']*2/3) / 100);

	$mentesoc = ceil($island['point']/10*(($island['factory']/2 + $island['market'])/$island['pop'])*$soclv/100);
	$island['fsocst'] = False;

	//公務員維持費
	if ($island['money'] < round($island['cmente'])){

		$this->log->Starve($id, $name, "公務員の給料");
		if($island['polit'] == 2){
			$island['policestat'] = 1;//警察ステータスをスト中に変更
			$this->log->Strike($id, $name);
		    $r = Util::random(10000);
			if($r < ($init->disMonster * 100 * $island['area'])){
				$island['present']['item'] == 3;
				}
			}
	}else{
	$island['money'] -= round($island['cmente']);
	}
	if ($island['money'] < $mentsoc){
		$this->log->Starve($id, $name, "社会保障費");
		$island['fsocst'] = True;
	}else{
	$island['money'] -= $mentesoc;
	}
	//公共投資維持費
	if ($island['money'] < round($island['invest']* 10)){
		//維持費が払えないと減耗する
		$this->log->Starve($id, $name, "インフラ維持費");
		$island['invest'] -= 0.5;
	}else{
	//1単位当り維持費100億Va
	$island['money'] -= round($island['invest'] * 10);
	}
	//教育投資維持費
	if ($island['money'] < round($island['edinv']) * 8){
		//維持費が払えないと減耗する
		$this->log->Starve($id, $name, "教育維持費");
		$island['edinv'] -= 1;
	}else{
	//1単位当り維持費80億Va
	$island['money'] -= round($island['edinv']* 8);
	}
	$island['sfarmy']	= 0;//0にセットしDoEachHexで最新値を入力する

    // 船
    $island['money'] -= $init->shipMentenanceCost[0] * $island['ship']['passenger'] + $init->shipMentenanceCost[1] * $island['ship']['fishingboat'] + $init->shipMentenanceCost[2] * $island['ship']['tansaku'] + $init->shipMentenanceCost[3] * $island['ship']['senkan'];
    $island['oil'] -= $init->shipMentenanceOil[0] * $island['ship']['passenger'] + $init->shipMentenanceOil[1] * $island['ship']['fishingboat'] + $init->shipMentenanceOil[2] * $island['ship']['tansaku'] + $init->shipMentenanceOil[3] * $island['ship']['senkan'];
	if($island['port'] > 0){
        $island['money'] += $init->shipIncom * $island['ship']['passenger'];
        $island['food']  += $init->shipFood  * $island['ship']['fishingboat'];
    }
    if(($island['ship']['viking'] > 0) && (Util::random(1000) < $init->disRobViking)){
        if(!($island['money'] < $init->disVikingMinMoney) || !($island['food'] < $init->disVikingMinFood)){
            $vMoney = round(Util::random($island['money'])/10);
            $vFood  = round(Util::random($island['food'])/10);
            $this->log->RobViking($island['id'], $island['name'], $vMoney, $vFood);
            $island['money'] -= $vMoney;
            $island['food'] -= $vFood;
            if($island['money'] < 0) $island['money'] = 0;
            if($island['food'] < 0) $island['food']  = 0 ;
        }
    }
    if($island['money'] < 0) $island['money'] = 0;
    if($island['food'] < 0) $island['food']  = 0 ;
	if($island['oil'] < 0 ) $island['oil'] = 0 ;
  }
  //---------------------------------------------------
  // 人口その他の値を算出
  //---------------------------------------------------
  function estimate(&$island) {
    // estimate(&$island) のように使用

    global $init;
    $land = $island['land'];
    $landValue = $island['landValue'];

    $area       = 0;
    $pop        = 0;
	$spop		= 0;
    $farm       = 0;
    $factory    = 0;
    $market     = 0;
	$milpop		= 0;
	$navy		= 0;
    $sfactory   = 0;
    $mfactory   = 0;
    $ffactory   = 0;
    $mountain   = 0;
	$nmountain  = 0;
    $mining     = 0;
    $hatuden    = 0;
    $monster    = 0;
    $port       = 0;
    $park       = 0;
  	$m10		= 0;
    $m23        = 0;
	$np			= 0;
	$capital	= 0;
	$capitals   = $island['capital'];
	$Cname		= "";
	$home		= 0;
    $fire = $rena = 0;
	$hapiness	= 0;
	$service = 0;
	$crowed = 0;
	$base   = 0;
	$cmente = 0;
	$polit = 0;
	$bport = 0;
	$fsocst = $island['fsocst'];
    $passenger = $fishingboat = $tansaku = $senkan = $viking = 0;
    // 数える
    for($y = 0; $y < $init->islandSize; $y++) {
      for($x = 0; $x < $init->islandSize; $x++) {
        $kind = $land[$x][$y];
        $value = $landValue[$x][$y];
        $minelv = ($value % 10) + 1;
        if(Util::checkShip($kind, $value)){
            if($value == 2)$passenger++   ;
            if($value == 3)$fishingboat++ ;
            if($value == 4)$tansaku++   ;
            if($value == 5)$senkan++ ;
            if($value == 255)$viking++    ;
        }
        if($kind != $init->landSea){
          if(($kind != $init->landNursery) && ($kind != $init->landSdefence)) $area++;
          switch($kind) {
          case $init->landTown:
		  // 町
            $pop += $value;
			$nwork = (int)($value/12);
			if ($value < 200){
	            if(Turn::countAround($land, $x, $y, $init->landFusya, 19)){
	            // 周囲2へクスに風車があれば２倍の規模に
	              $farm += $nwork * 2;
	            }else{
	              $farm += $nwork;
	            }
				$hapiness += $value * $init->BaseHappiness[0];
			}else{
				$crowed++;
				$hapiness += $value * $init->BaseHappiness[4];
			}
            break;
          case $init->landProcity:
		  // 町
            $pop += $value;
			$hapiness += $value * $init->BaseHappiness[1];
            break;
          case $init->landNewtown:
            // ニュータウン
            $pop += $value;
			$hapiness += $value * $init->BaseHappiness[2];
            $nwork = (int)($value/60);
            $market += $nwork;
		  	$crowed++;
            break;
          case $init->landBigtown:
            // 現代都市
            $pop += $value;
			$hapiness += $value * $init->BaseHappiness[3];
            $mwork = (int)($value/7);
            $lwork = (int)($value/100);
            $factory += $lwork;
            $market += $mwork;
		  	$crowed++;
            break;
		  case $init->landSeeCity:
            // 観光都市
            //$pop += $value;
			//$hapiness += $value * $init->BaseHappiness[1];
			$spop += $value;
		  	$crowed++;
            break;

		  case $init->landIndCity:
            // 工業都市
            $pop += $value;
			$hapiness += $value * $init->BaseHappiness[2];
			$nwork = (int)($value/5);
			$factory += $nwork;
            break;
          case $init->landFarm:
            // 農場
            if(Turn::countAround($land, $x, $y, $init->landFusya, 19)){
            // 周囲2へクスに風車があれば２倍の規模に
              $farm += $value * 2;
            }else{
              $farm += $value;
            }
            break;
          case $init->landNursery:
            // 養殖場
            $farm += $value;
            break;
          case $init->landFactory:
            // 工場
            $factory += $value;
            if(($value % 2) == 1){
              $factory --;
            }
            break;
          case $init->landMarket:
            // 市場
            $market += $value;
			break;
          case $init->landSFactory:
            // 砲弾工場
            $sfactory += $value;
            if(($value % 2) == 1){
              $sfactory --;
            }
            break;
          case $init->landMFactory:
            // 建材工場
            $mfactory += $value;
            if(($value % 2) == 1){
              $mfactory --;
            }
            break;
          case $init->landFFactory:
            // 精製工場
            $ffactory += $value;
            break;
		  case $init->landnMountain:
		  	//山地
			$nmountain++;
			break;
          case $init->landMountain:
            // 山
            $mountain++;
            break;
          case $init->landStonemine:
            // 採石場
            if(($mining % 16) < 15) {
              if((($mining % 16) + $minelv) > 15){
                $mining = ((int)($mining / 16) * 16) + 15;
              } else {
                $mining += $minelv;
              }
            }
            $mountain++;
            break;
          case $init->landCoal:
            // 炭坑
            if(((int)($mining / 16) % 16) < 15) {
              if((((int)($mining / 16) % 16) + $minelv) > 15){
                $omine = $mining % 16;
                $mining = ((((int)($mining / 256) * 16) + 15) * 16) + $omine;
              } else {
                $mining += ($minelv * 16);
              }
            }
            $mountain++;
            break;
          case $init->landSteel:
            // 鉄鉱山
            if(((int)($mining / 256) % 16) < 15) {
              if((((int)($mining / 256) % 16) + $minelv) > 15){
                $omine = $mining % 256;
                $mining = ((((int)($mining / 4096) * 16) + 15) * 256) + $omine;
              } else {
                $mining += ($minelv * 256);
              }
            }
            $mountain++;
            break;
          case $init->landUranium:
            // ウラン鉱山
            if(((int)($mining / 4096) % 16) < 15) {
              if((((int)($mining / 4096) % 16) + $minelv) > 15){
                $omine = $mining % 4096;
                $mining = ((((int)($mining / 65536) * 16) + 15) * 4096) + $omine;
              } else {
                $mining += ($minelv * 4096);
              }
            }
            $mountain++;
            break;
          case $init->landSilver:
            // 金鉱山
            if(((int)($mining / 65536) % 16) < 15) {
              if((((int)($mining / 65536) % 16) + $minelv) > 15){
                $omine = $mining % 65536;
                $mining = (15 * 65536) + $omine;
              } else {
                $mining += ($minelv * 65536);
              }
            }
            $mountain++;
            break;
          case $init->landHatuden:
            // 発電所
            $hatuden += $value;
            break;
          case $init->landBase:
            // ミサイル
            $fire += Util::expToLevel($kind, $value);
			$base++;
            break;
          case $init->landHBase:
            // 偽装ミサイル
            $fire += Util::expToLevel($kind, $value);
			$base++;
            break;
          case $init->landMonster:
          case $init->landSleeper:
            // 怪獣
            $monster++;
            break;
          case $init->landPort:
            // 港
            $port++;
			if($value == 1){
				$bport++;
			}
            break;
          case $init->landPark:
            // 遊園地
            $park++;
            break;
          case $init->landMonument:
            // 記念碑
            if($value == 23) $m23++;
			if(($value == 10) ||
			($value == 32) ||
			($value == 33) ||
			($value == 54)) {
				//カテドラル、神社、神殿、幸福の女神像で宗教幸福度Up
				$m10++;
			}
            break;

		  case $init->landNPark:
		  	//国立公園
			$np++;
			if($np > 5){
			$np = 5;
			}
            break;

		  case $init->landMyhome:
            // 行政府
            $home++;
            break;

		  case $init->landSecpol:
		   //秘密警察
		   $secpol++;
            break;

		  case $init->landSdefence:
		  	$navy++;
			break;

		  case $init->landCapital:
		  	//首都
			$Cname = $island['Cname'];
			$pop += $value;
			$hapiness += $value * $init->BaseHappiness[2];
            $nwork = round($value / 9);
            $market += $nwork;
			$capital = $capitals;
            break;

          }
        }
      }
    }
	//公務員数
	//首都レベル*総人口+軍人
	$milpop = $base * 4;
	$sfarmy = $island['sfarmy'] * 4;
	$mnavy = ($navy+$senkan*3) * 0.3;
	$service =ceil($capital / 800 * $pop + $milpop + $mnavy + $sfarmy);
	if ($island['edinv']>0){
	$service +=ceil($island['edinv'] / 80000 * $pop);
	}
	//基地維持費
	$mbase = $base * 30;

	//首都維持費
	//首都レベル*公務員数*密集地数
	if ($capital == 5){
	//首都レベル5のみ維持費上積み
	$capitals = 7;
	}
	if ($secpol == 1){
	$capitals++;
	}
	$services = $service;//公務員数を維持費用データに代入
	if ($secpol == 1){
	$services -= ceil($milpop+$mnavy+ $sfarmy);
	}
	$hcrowed = $crowed/2;
	$crowed = $crowed + pow(1.11,$hcrowed);//過密地区指数
	$cmente = ceil($capitals * $services * $crowed / 100)+$bport*60+$mbase;//超ド級暫定

	//幸福度関係
	$unemployed = ($pop - ($service + $farm + $factory + $market) * 10 ) / $pop;


	$industry = array("農業"=>$farm,"工業"=>$factory,"商業"=>$market);
	arsort($industry);
	$Tokka = key($industry);

	if ($unemployed > 0) {
  	//失業率がプラスの場合
		if ($Tokka == "農業") {
		//農業国の場合
			$hapiness = $hapiness * (1 - $unemployed * 0.3);//失業者の幸福度2割減

		}elseif($Tokka == "商業") {
		//商業国の場合
			$hapiness = $hapiness * (1 - $unemployed * 0.9);//失業者の幸福度9割減

		}else{
		//工業国の場合
			$hapiness = $hapiness * (1 - $unemployed * 0.5);//失業者の幸福度4割減
		}
	}
	$hapiness = $hapiness * (1 + $farm * 10 / $pop * 0.2); //農民は無条件で幸福度20%Up
	$hapiness = ((int)($hapiness / $pop)); #1人あたり幸福度
	if($fsocst != True){
		$hapiness += ceil($island['soclv']*0.35);
	}
	//減少は0.1ずつなので使うときは小数点切り上げ（ceil）を使う
	//$hapiness += ((int)(ceil($island['invest']) / 3)); #公共投資の3分の1が幸福度に反映
	//$hapiness += ((int)(ceil($island['edinv']) / 10)); #教育投資の10分の1が幸福度に反映
	$hapiness += $island['percat'];//食肉指数を幸福度に加算
	if ($m10 >= 1){
		//宗教施設がある場合幸福度＋8
		$hapiness += 9;
	}
	if ($park >= 1){
		//遊園地がある場合幸福度＋8
		$hapiness += 8;
	}
	if ($hatuden > 0){
		//発電所がある場合幸福度＋8
		$hapiness += 8;
	}
	if ($np > 0){
		//国立公園がある場合1つにつき+1(5個まで）
		$hapiness += (1+$np);
	}
	//ここまで基礎幸福＋ボーナス
	//ここからペナルティ
	//要求幸福度、投資度に満たない場合
	if($island['point'] >= $init->BaseHappiDemand[2]){//先進国の場合-40%
		if (($hapiness <= $init->HappinessDemand[3]) || ($island['invest'] <= $init->InvestDemand[3]) ||  ($island['soclv'] <= $init->InvestDemand[3])){
			$kasan -= $hapiness * 0.4;
		}
	}elseif($island['point'] >= $init->BaseHappiDemand[1]){//中進国の場合-30％
		if (($hapiness <= $init->HappinessDemand[2]) || ($island['invest'] <= $init->InvestDemand[2]) ||  ($island['soclv'] <= $init->InvestDemand[2])){
			$kasan -= $hapiness * 0.3;
		}
	}elseif($island['point'] >= $init->BaseHappiDemand[0]){ //発展途上国の場合-20%
		if (($hapiness <= $init->HappinessDemand[1]) || ($island['invest'] <= $init->InvestDemand[1]) ||  ($island['soclv'] <= $init->InvestDemand[1])){
			$kasan -= $hapiness * 0.2;
		}
	}else{ //後進国の場合
			//後進国ボーナス、投資度要求無効化
	}
	if (($pop > $init->DemoPop) && ($home == 0)) {
		//1000万以上の国家で議事堂が無い場合幸福度－30%
		$kasan -= $hapiness * 0.3;
	}

	if ($island['food'] == 0){
		//飢餓状態の場合幸福度－50%
		$kasan -= $hapiness * 0.5;

	}
	$hapiness = ((int)($hapiness + $kasan));

	//政治体制判別
	if($home == 1){
		$polit = 1;
	}elseif($secpol == 1){
		$polit = 2;
	}
	if($bport > 0){
		$bport = 1;
	}

    // 代入
    $island['pop']      = $pop;
	$island['spop']		= $spop;
    $island['area']     = $area*0.1;
    $island['farm']     = $farm;
    $island['factory']  = $factory;
    $island['market']   = $market;
	$island['milpop']   = $milpop;
	$island['navy']     = $navy;
    $island['sfactory'] = $sfactory;
    $island['mfactory'] = $mfactory;
    $island['ffactory'] = $ffactory;
    $island['mountain'] = $mountain;
	$island['nmountain'] = $nmountain;
    $island['mining']   = $mining;
    $island['hatuden']  = $hatuden;
	$island['home']     = $home;
    $island['monster']  = $monster;
    $island['port']     = $port;
    $island['park']     = $park;
    $island['npark']	= $np;
    $island['m10']		= $m10;
    $island['m23']      = $m23;
    $island['fire']     = $fire;
    $island['rena']     = ceil($fire*1.2) + $island['taiji'];
    $island['ship']['passenger'] = $passenger;
    $island['ship']['fishingboat'] = $fishingboat;
    $island['ship']['tansaku'] = $tansaku;
    $island['ship']['senkan'] = $senkan;
    $island['ship']['viking'] = $viking;
	$island['hapiness'] = $hapiness;
	$island['capital']  = $capital;
	$island['Cname']	= $Cname;
	$island['service']  = $service;
	$island['cmente']   = $cmente;
	$island['polit']	= $polit;
	$island['bport']	= $bport;
	$island['sfarmy']	= $sfarmy;
	if($island['pop'] == 0) {
      $island['point'] = 0;
    } else {
      $island['point'] = round(($island['pop']+$island['spop'])*3 + ($island['money'] + $island['material'] + $island['fuel'] + $island['shell'] + $island['wood'] + $island['stone'] + $island['steel'] + $island['oil'] + $island['explosive'] + $island['alcohol'])*0.01 + ($island['farm'] + $island['factory'] + $island['market'] + $island['hatuden'])*8 + $island['area']*120 + $island['taiji']*40 + $islansd['fire']*60);
    }
    $island['seichi']   = 0;
  }
  //---------------------------------------------------
  // 範囲内の地形を数える
  //---------------------------------------------------
  function countAround($land, $x, $y, $kind, $range) {
    global $init;
    // 範囲内の地形を数える
    $count = 0;
    for($i = 0; $i < $range; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
        // 範囲外の場合
        if($kind == $init->landSea) {
          // 海なら加算
          $count++;
        }
      } else {
        // 範囲内の場合
        if($land[$sx][$sy] == $kind) {
          $count++;
        }
      }
    }
    return $count;
  }
  //---------------------------------------------------
  // 上陸ポイント判定
  //---------------------------------------------------
  function checkLand($land, $x, $y) {
    global $init;

    for($i = 0; $i < 7; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
      } else {
        // 範囲内の場合
        if(($land[$sx][$sy] != $init->landSea) &&
           ($land[$sx][$sy] != $init->landMountain) &&
           ($land[$sx][$sy] != $init->landnMountain) &&
           ($land[$sx][$sy] != $init->landNursery) &&
		   ($land[$sx][$sy] != $init->landOil) &&
           ($land[$sx][$sy] != $init->landSdefence) &&
           ($land[$sx][$sy] != $init->landPort) &&
           ($land[$sx][$sy] != $init->landZorasu)) {
		    $lpoint = array($sx,$sy);
            return $lpoint;
          }
        }
      }
    return false;
  }
  //---------------------------------------------------
  // 範囲内の地形＋値でカウント
  //---------------------------------------------------
  function countAroundValue($island, $x, $y, $kind, $lv, $range) {
    global $init;

    $land = $island['land'];
    $landValue = $island['landValue'];
    $count = 0;

    for($i = 0; $i < $range; $i++) {
        $sx = $x + $init->ax[$i];
        $sy = $y + $init->ay[$i];

        // 行による位置調整
        if((($sy % 2) == 0) && (($y % 2) == 1)) {
            $sx--;
        }

        if(($sx < 0) || ($sx >= $init->islandSize) ||
           ($sy < 0) || ($sy >= $init->islandSize)) {
            // 範囲外の場合
        } else {
            // 範囲内の場合
            if(($land[$sx][$sy] == $init->landProcity) && ($landValue[$sx][$sy] >= $lv)) {
              // 防災都市は以上
               $count++;
            } elseif($land[$sx][$sy] == $kind && $landValue[$sx][$sy] == $lv) {
               $count++;
            }
        }
    }
    return $count;
  }
  //---------------------------------------------------
  // 地形の呼び方
  //---------------------------------------------------
  function landName($land, $lv) {
    global $init;
    switch($land) {
    case $init->landSea:
      if($lv == 1) {
        return '浅瀬';
      } elseif($lv == 2) {
            return $init->shipName[0];
        } elseif($lv == 3) {
            return $init->shipName[1];
        } elseif($lv == 4) {
            return $init->shipName[2];
        } elseif($lv == 5) {
            return $init->shipName[3];
        } elseif($lv == 255) {
            return '海賊船';
      } else {
        return '海';
      }
      break;
    case $init->landPort:
	if($lv == 0){
      return '港';
	}elseif($lv == 1){
      return '大規模港';
	}
    case $init->landWaste:
      return '荒地';
    case $init->landPoll:
      return '汚染土壌';
    case $init->landPlains:
      return '平地';
    case $init->landTown:
      if($lv < 30) {
        return '村';
      } elseif($lv < 100) {
        return '村落';
      } elseif($lv < 200) {
        return '農村';
      } else {
	  	return '近郊住宅地';
	  }
    case $init->landProcity:
      return '防災都市';
    case $init->landNewtown:
      return 'ニュータウン';
    case $init->landBigtown:
      return '現代都市';
    case $init->landForest:
      return '森';
    case $init->landFarm:
      return '農場';
    case $init->landNursery:
      return '養殖場';
    case $init->landFactory:
      if($lv < 100) {
        return '工場';
		} else {
	  	return 'コンビナート';
	  }
    case $init->landMarket:
      return '市場';
    case $init->landStonemine:
      return '採石場';
    case $init->landSFactory:
      return '軍事工場';
    case $init->landMFactory:
      return '建材工場';
    case $init->landFFactory:
      return '畜産場';
    case $init->landHatuden:
      return '発電所';
    case $init->landBase:
      return 'ミサイル基地';
    case $init->landHBase:
      return '偽装ミサイル基地';
    case $init->landDefence:
      return '防衛施設';
    case $init->landHDefence:
      return '偽装防衛施設';
    case $init->landSdefence:
      return '防衛艦隊';
    case $init->landMountain:
      return '山';
    case $init->landnMountain:
      return '山地';
    case $init->landSteel:
      return '鉄鉱山';
    case $init->landCoal:
      return '炭坑';
    case $init->landUranium:
      return 'ウラン鉱山';
    case $init->landSilver:
      return '銀鉱山';
    case $init->landMonster:
      $monsSpec = Util::monsterSpec($lv);
      return $monsSpec['name'];
    case $init->landOil:
      return '海底油田';
    case $init->landHaribote:
      return 'ハリボテ';
    case $init->landMonument:
      return $init->monumentName[$lv];
    case $init->landPark:
      return '遊園地';
    case $init->landFusya:
      return '農業改良センター';
	case $init->landMyhome:
      return '議事堂';
	case $init->landSeeCity:
		return '観光都市';
	case $init->landCapital:
		return '首都';
	case $init->landIndCity:
		return '工業都市';
	case $init->landNPark:
		return '国立公園';
	case $init->landSecpol:
		return '秘密警察';
	case $init->landZorasu:
		return '揚陸艦';
	case $init->landFBase:
		return '他国軍基地';
    }
  }
//-----------------------------------------------
//　建設処理用関数
//-----------------------------------------------
function doComCons($landt,$logtype){

        $land[$x][$y] = $landt;
        $landValue[$x][$y] = 1;
        $this->log->landSuc($id, $name, $comName, $point);
}

function ChkCapLevel($com,$caplv,$id, $name, $comName){
//首都レベルチェック用関数
	global $init;
	if($caplv < $init->comGovernment[$com]){
        $return = false;
        $this->log->NoAny($id, $name, $comName, "首都レベルが低い");
      }else{
	    $return = true;
	  }
	return $return;
}
//-----------------------------------------------
//　輸送系だけ関数を切り離し
//-----------------------------------------------
function comTrades(&$arg,$cost,&$goods,&$tgoods) {
// 輸出量決定
      if($arg == 0) { $arg = 1; }
          $value = min($arg * (-$cost), $goods);
	  $goods -= $value;
      $tgoods += $value;
	  return $value;
	}

//------------------------------------------------
function comTradesN($kind,&$cost,&$container,&$str){
    global $init;
//消費する物資の種類を決定
	switch ($kind){
		case $init->comFood:
					$container = 'food';
					$str = "{$init->unitFood}";
				break;

		case $init->comSilver:
	        		$container = 'silver';
					$str = "{$init->unitSilver}";
				break;

		case $init->comSteel:
	        		$container = 'steel';
					$str = "{$init->unitSteel}";
				break;

		case $init->comMaterial:
	          		$container = 'material';
			 		$str = "{$init->unitMaterial}";
				break;

		case $init->comStone:
	          		$container = 'stone';
			  		$str = "{$init->unitStone}";
		  		break;

		case $init->comShell:
	        		$container = 'shell';
			  		$str = "{$init->unitShell}";
		  		break;

		case $init->comFuel:
	          		$container = 'fuel';
			 		$str = "{$init->unitFuel}";
				break;

		case $init->comOil:
	         		 $container = 'oil';
					 $str = "{$init->unitOil}";
				break;

		case $init->comExplosive:
	          		$container = 'explosive';
			 		$str = "{$init->unitExplosive}";
				break;

		case $init->comAlcohol:
	          		$container = 'alcohol';
			  		$str = "{$init->unitAlcohol}";
				break;

		case $init->comGoods:
	        		$container = 'goods';
			  		$str = "{$init->unitGoods}";
				break;

		case $init->comWood:
	        		$container = 'wood';
			  		$str = "{$init->unitWood}";
				break;

		default:
			      $cost = $cost * (-1);
		          $container = 'money';
				  $str = "{$init->unitMoney}";
			  break;
      }
}
//---------------------------------------------
function comTradeschk($kind,$turn){
//自動輸送時の輸送フラグチェック関数
    global $init;
	$ftrade = false;
	switch ($kind){
		case $init->comFood:
			if($turn % 6 == 0){
					$ftrade = true;
				}
				break;

		case $init->comSilver:
			if($turn % 6 == 0){
					$ftrade = true;
				}
				break;

		case $init->comSteel:
			if($turn % 6 == 1){
					$ftrade = true;
				}
				break;

		case $init->comMaterial:
			if($turn % 6 == 1){
					$ftrade = true;
				}
				break;

		case $init->comStone:
			if($turn % 6 == 2){
					$ftrade = true;
				}
		  		break;

		case $init->comShell:
			if($turn % 6 == 2){
					$ftrade = true;
				}
		  		break;

		case $init->comFuel:
			if($turn % 6 == 3){
					$ftrade = true;
				}
				break;

		case $init->comOil:
			if($turn % 6 == 3){
					$ftrade = true;
				}
				break;

		case $init->comAlcohol:
			if($turn % 6 == 4){
					$ftrade = true;
				}
				break;

		case $init->comGoods:
			if($turn % 6 == 5){
					$ftrade = true;
				}
				break;

		case $init->comWood:
			if($turn % 6 == 5){
					$ftrade = true;
				}
				break;
		default:
			if($turn % 6 == 1){
				  $ftrade = true;
			  }
			  break;
      }
	  return $ftrade;
}
}
//--------------TurnClassここまで---------------
// ポイントを比較
function popComp($x, $y) {
  if($x['point'] == $y['point']) return 0;
  return ($x['point'] > $y['point']) ? -1 : 1;
}


?>