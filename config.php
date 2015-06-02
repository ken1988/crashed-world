<?php
/*******************************************************************

  箱庭諸島２ for PHP

  - 初期設定用ファイル -

  $Id: config.php,v 1.7 2003/09/29 11:54:19 Watson Exp $

*******************************************************************/
define("GZIP", false);	// true: GZIP 圧縮転送を使用  false: 使用しない
define("DEBUG", false);	// true: デバッグ false: 通常

class Init {
  //----------------------------------------
  // 各種設定値
  //----------------------------------------
  // プログラムを置くディレクトリ
  var $baseDir		= "http://crashed.crap.jp/";

  // 画像を置くディレクトリ
  var $imgDir		= "http://crashed.crap.jp/img";

  // ローカル設定用画像
  var $imgPack          = "http://crashed.crap.jp/img.lzh";
  // ローカル設定説明ページ
  var $imgExp           = "http://crashed.crap.jp/local.html";

  // CSSファイルを置くディレクトリ
  var $cssDir		= "http://crashed.crap.jp/css";

  // CSSリスト
  var $cssList		= array('Autumn.css');

  //パスワードの暗号化 true: 暗号化、false: 暗号化しない
  var $cryptOn		= true;
  // マスターパスワード
  var $masterPassword	= "63810";
  var $specialPassword	= "63810";

  // データディレクトリの名前
  var $dirName		= "data";

  // ゲームタイトル
  var $title		= "箱庭墜落世界";

  var $adminName	= "TRT";
  var $adminEmail	= "novilen.gov@gmail.com";
  var $urlBbs		= "http://crashed.crap.jp/moin/";
  var $urlTopPage	= "http://crashed.crap.jp/";
  var $urlManual	= "http://crashed.crap.jp/moin/users_manual";
  var $urlHowTo		= "http://crashed.crap.jp/moin/tips";

  // ディレクトリ作成時のパーミション
  var $dirMode		= 0777;

  // 新規登録受付ターン
  var $entryTurn    = 0;  // 設定ターンまで受付、0:使用しない
  // 終了ターン
  var $endTurn      = 0; // 設定ターンで終了、0:使用しない

  // 新規島の登録モード (0:通常、1:管理人)
  var $registMode = 0;
  // 管理人モード
  var $adminMode;

  // 1ターンが何秒か
  var $unitTime		= 14400; // 4時間

  // 島の最大数
  var $maxIsland	= 40;

  // TOPページに一度に表示する島の数(0なら全島表示)
    var $islandListRange = 20;
  // 資金表示モード
  var $moneyMode	= true; // true: 100の位で四捨五入, false: そのまま
  // トップページに表示するログのターン数
  var $logTopTurn	= 5;
  // ログファイル保持ターン数
  var $logMax		= 42;

  // バックアップを何ターンおきに取るか
  var $backupTurn	= 4;
  // バックアップを何回分残すか
  var $backupTimes	= 10;

  // 発見ログ保持行数
  var $historyMax	= 15;

  // 放棄コマンド自動入力ターン数
  var $giveupTurn	= 360;

  // コマンド入力限界数
  var $commandMax	= 40;

  // ローカル掲示板行数を使用するかどうか(false:使用しない、true:使用する)
  var $useBbs		= true;
  // 文字色選択
  var $lbbsColor = array('#EE82EE','#DB7093','#A020F0','#FF69B4','#FF1493','#CD5C5C','#FF0000','#800000','#FFA500','#D2691E','#7CFC00','#32CD32','#008B8B','#00FFFF','87CEEB','#00BFFF','0000FF','#808080','#000000');
  // ローカル掲示板行数
  var $lbbsMax		= 15;
  // ローカル掲示板への匿名発言を許可するか(false:禁止、true:許可)
  var $lbbsAnon         = false;
  // ローカル掲示板の発言に発言者の島名を表示するか(false:表示しない、true:表示する)
  var $lbbsSpeaker      = true;
  // 他島のローカル掲示板に発言するための費用(0:無料)
  var $lbbsMoneyPublic =   0; // 公開
  var $lbbsMoneySecret =  100; // 極秘

  //定期輸送の最大本数
  var $regTMax	= 5;
  //定期輸送の1次最大本数
  var $regTsMax = 2;
  //定期輸送の最低期間
  var $regTTerm	= 18;
  // 島の大きさ
  var $islandSize	= 20;

  // 負荷計測するか？(0:しない、1:する)
  var $performance = 1;
  var $CPU_start;

  // アクセスログファイルの名前
  var $logname	= "ip.log";

  // アクセスログ最大記録レコード数
  var $axesmax	= 1000;

  // ========== 同盟関連の設定はここから、がんばれ！！ ==========

  // 同盟作成を許可するか？(0:しない、1:する、2:管理者のみ)
  var $allyUse      = 1;

  // ひとつの同盟にしか加盟できないようにするか？(0:しない、1:する)
  var $allyJoinOne  = 0;

  // 同盟データの管理ファイル
  var $allyData     = 'ally.xml';

  // 同盟のマーク
  var $allyMark = array(
      'Б','Г','Д','Ж','Й',
      'Ф','Ц','Ш','Э','Ю',
      'Я','б','Θ','Σ','Ψ',
      'Ω','ゑ','ゐ','¶','‡',
      '†','♪','♭','♯','‰',
      'Å','∽','∇','∂','∀',
      '⇔','∨','〒','￡','￠',
      '＠','★','♂','♀','＄',
      '￥','℃','仝','〆',
    );

  // 入力文字数の制限 (全角文字数で指定) 実際は、<input> 内の MAXLENGTH を直に修正してください。 (;^_^A
  var $lengthAllyName    = 15;   // 同盟の名前
  var $lengthAllyComment = 40;   // 「各同盟の状況」欄に表示される盟主のコメント
  var $lengthAllyTitle   = 30;   // 「同盟の情報」欄の上に表示される盟主メッセージのタイトル
  var $lengthAllyMessage = 1500; // 「同盟の情報」欄の上に表示される盟主メッセージ

  // スタイルシートを改変していないので、ここに記述
  var $bgMarkCell = 'style="background-color:#ffffff;" align="center"';
  var $tagMoney_  = '<span style="color:#999933; font-weight:bold;">';
  var $_tagMoney  = '</span>';

  // コメントの自動リンク (0:しない 1:する)
  var $autoLink   = 1;


  // 以下は、表示関連で使用しているだけで、実際の機能を有していません、さらなる改造で実現可能です。

  // 加盟・脱退をコマンドで行うようにする？(0:しない、1:する)
  var $allyJoinComUse = 0;

  // 同盟に加盟することで通常災害発生確率が減少？(0:しない)
  // 対象となる災害：地震、津波、台風、隕石、巨大隕石、噴火
  var $allyDisDown  = 0; // 設定する場合、通常時に対する倍率を設定。(例)0.5なら半減。2なら倍増(^^;;;

  var $costMakeAlly =  10; // 同盟の結成・変更にかかる費用
  var $costKeepAlly =  500; // 同盟の維持費(加盟している島で均等に負担)

  // ========== 同盟関連の設定はここまで。お疲れでした。<(_~_)> ==========
  //----------------------------------------
  // 資金、食料などの設定値と単位
  //----------------------------------------

  // 初期資金
  var $initialMoney	     = 1500;
  // お金の単位
  var $unitMoney	     = "0億Va";

  // 初期食料
  var $initialFood	     = 1500;
  // 食料の単位
  var $unitFood		     = "万トン";

  // 初期商品
  var $initialGoods	     = 0;
  // 商品の単位
  var $unitGoods		 = "0億Va相当";

  // 初期銀
  var $initialSilver     = 0;
  var $unitSilver		 = "0トン";

  // 初期肉
  var $initialAlcohol    = 0;
  var $unitAlcohol		 = "0トン";

  // 初期木材
  var $initialWood       = 500;
  var $unitWood		     = "0万トン";

  // 初期石材
  var $initialStone	     = 500;
  var $unitStone		 = "0万トン";

  // 初期鉄鋼
  var $initialSteel	     = 500;
  var $unitSteel		 = "0万トン";

  // 初期建材
  var $initialMaterial   = 500;
  var $unitMaterial		 = "0万トン";

  // 初期石油
  var $initialOil        = 1000;
  var $unitOil		     = "0万バレル";

  // 初期燃料
  var $initialFuel       = 1000;
  var $unitFuel		     = "0万ガロン";

  // 初期砲弾
  var $initialShell      = 0;
  var $unitShell		 = "0メガトン";

  // 初期弾薬
  var $initialExplosive	 = 0;
  var $unitExplosive     = "0万トン";

  var $unitCoal		= "0万トン";
  var $unitUranium	= "0トン";
  var $unitCow		= "万頭";

  //各単位を配列にまとめておく
  var $allunit = Array(
"0億Va", #資金
"万トン", #食糧
"0億Va相当", #商品
"0ガロン", #酒
"0万トン", #木材
"0万トン", #石材
"0万トン", #鉄鉱
"0万トン", #建材
"0トン", #銀
"0万バレル", #石油
"0万ガロン", #燃料
"0メガトン", #砲弾
"0万トン"); #弾薬

  // 資金最大値
  var $maxMoney		= 300000;
  // 食料最大値
  var $maxFood		= 10000000;
  // 商品最大値
  var $maxGoods		= 300000;
    // 銀最大値
  var $maxSilver	= 20000;
    // 鉄鋼最大値
  var $maxSteel		= 20000;
    // 建材最大値
  var $maxMaterial	= 50000;
    // 砲弾最大値
  var $maxShell		= 99999;
    // 燃料最大値
  var $maxFuel		= 100000;
    // 木材最大値
  var $maxWood		= 50000;
    // 石材最大値
  var $maxStone		= 10000;
    // 酒最大値
  var $maxAlcohol	= 100000;
    // 石油最大値
  var $maxOil		= 100000;

  var $allmax;

  // 人口の単位
  var $unitPop		= "人";
  // 広さの単位
  var $unitArea		= "万sq.Km";
  // 木の数の単位
  var $unitTree		= "万本";

  // 木の単位当たりの木材換算
  var $treeValue	= 8;

  // 名前変更のコスト
  var $costChangeName	= 100;

  // 人口1単位あたりの食料消費料
  var $eatenFood	= 0.4;

  // 油田の収入
  var $oilFuel		= 90;
  // 油田の枯渇確率
  var $oilRatio		= 90;

  // 船に関する設定
  var $shipMentenanceCost = array(40, 10, 20, 150); # 維持費
  var $shipMentenanceOil  = array(3, 4, 5, 10); # 維持石油
  var $shipIncom          = 100; # 収入
  var $shipFood           = 1000; # 食料
  var $shipKind           =   4; # 船の種類
  var $shipName           = array("遊覧船","漁船", "海底探索船", "戦艦");
  var $shipIntercept      = 600; # 戦艦がミサイルを迎撃する確率
  var $disRunAground1     =  50; # 座礁確率  座礁処理に入るための確率
  var $disRunAground2     =  10; # 座礁確率  船 個別の判定
  var $disZorasu          =  0; # ぞらす 出現確率
  var $disViking          =  8; # 海賊船 出現確率 船１つあたり（たくさん船を持てばその分確率UP）
  var $disVikingAway      = 300; # 海賊船 去る確率
  var $disRobViking       = 500; # 海賊船強奪
  var $disVikingMinMoney  = 2500;# 強奪するときの最低金額（これ以下は強奪しない)
  var $disVikingMinFood   = 2500;# 強奪するときの最低食料（これ以下は強奪しない)
  var $disVikingAttack    = 200; # 海賊の攻撃で沈没する確率
  var $maxShip            =   15; # 船の最大所有数

  //幸福度に関する設定
  var $BaseHappiness	  =array(38,30,40,25,20);#基礎幸福度（町、防災＆観光＆海底&工業、ニュータウン、現代都市、大都市）
  var $BaseHappiDemand	  =array(30000,70000,170000);#要求幸福度基準ポイント（後進国、発展途上国、中進国、先進国）
  var $HappinessDemand	  =array(30,40,55,70);#要求幸福度（後進国、発展途上国、中進国、先進国）
  var $InvestDemand	  =array(10,15,50,65);#要求投資率（後進国、発展途上国、中進国、先進国）
  var $DemoPop			  = 10000;#議事堂要求人口

  //産業定数
  var $BaseIndust	=array(30,80,15);#工業、農業、商業（利用時は100分の1）
  //原料消費量計算
  //銀1：商品3.5、鉄1：商品1.5、食料2：商品1
  //労1：商品2.5、労1：商品1.5、労2：商品：1
  //工業消費定数
  var $Industcoms	=array(5,15,35);#原料1単位商品生産性　軽工業、重工業、先端工業（利用時は10分の1）
  var $Industmcoms	=array(5,15,25);#労働者1単位商品生産性　軽工業、重工業、先端工業（利用時は10分の1）
  var $Industfcoms  =array(1,4,2);#燃料　軽工業、重工業、先端工業
  //----------------------------------------
  // 基地の経験値
  //----------------------------------------
  // ミサイル発射禁止ターン
  var $noMissile    = 50; // これより前には実行が許可されない
  // 援助禁止ターン
  var $noAssist     = 20; // これより前には実行が許可されない
  // リミッター発動人口
  var $limitpop     = 1000; // これより人口が少ないと許可されない

  // 複数地点へのミサイル発射を可能にするか？1:Yes 0:No
  var $multiMissiles = 0;

  // ミサイル基地
  // 経験値の最大値
  var $maxExpPoint	= 255; // ただし、最大でも255まで

  // レベルの最大値
  var $maxBaseLevel	= 30; // ミサイル基地
  var $maxSBaseLevel	= 5; // 海底基地

  // 経験値がいくつでレベルアップか
  var $baseLevelUp	= array(5,8,11,15,19,23,28,33,38,43,48,53,58,63,68,74,80,86,92,100,110,120,130,145,160,175,190,210,225,255); // ミサイル基地

  // 目標の島 所有の島が選択された状態でリストを生成 1、順位がTOPの島なら0
  // ミサイルの誤射が多い場合などに使用すると良いかもしれない
  var $targetIsland = 1;
  //----------------------------------------
  // 初期の山の配置数
  //----------------------------------------
  // 初期の山の配置数、配置しないなら0
  var $initMountain = 2;
  // 山の最大配置数、配置しないなら0
  var $haveMountain = 2;

  //----------------------------------------
  // 防衛施設の自爆・偽装・レベル
  //----------------------------------------
  // 怪獣に踏まれた時自爆するなら1、しないなら0
  var $dBaseAuto = 0;

  // 防衛施設を森に偽装するなら1、しないなら0
  var $dBaseHide = 0;

  // 防衛施設の初期レベル
  var $dBaseInitLv = 1;

  // 防衛施設の最大レベル
  var $dBaseMaxLv = 3;

  //----------------------------------------
  // 災害
  //----------------------------------------
  // 通常災害発生率(確率は0.1%単位)
  var $disEarthquake = 15; // 地震
  var $disTsunami    = 2; // 津波
  var $disTyphoon    = 20; // 台風
  var $disMeteo      = 5; // 隕石
  var $disHugeMeteo  = 3;  // 巨大隕石
  var $disEruption   = 15;  // 噴火
  var $disFire       = 40; // 火災
  var $disMaizo      = 1; // 埋蔵金
  var $disHardRain   = 20; // 雨
  var $disHardRain2  = 20; // 酸性雨変化率
  var $disHardRain3  = 30; // 森増殖（深刻な被害）
  var $disResession  = 50; // 不況
  var $disRoofFall   = 1;  // 落盤

  // 地盤沈下
  var $disFallBorder = 30; // 安全限界の広さ(Hex数)
  var $disFalldown   = 100; // その広さを超えた場合の確率

  // 怪獣
  var $disMonsBorder1 = 2000;  // 人口基準1(怪獣レベル1)
  var $disMonsBorder2 = 4000;  // 人口基準2(怪獣レベル2)
  var $disMonsBorder3 = 6000;  // 人口基準3(怪獣レベル3)
  var $disMonster     = 0.5;    // 単位面積あたりの出現率(0.01%単位)

  var $monsterLevel1  = 2; // サンジラまで
  var $monsterLevel2  = 5; // いのらゴーストまで
  var $monsterLevel3  = 6; // かおくと（♀）まで


  var $monsterNumber	= 8; // 怪獣の種類
  // 怪獣の名前
  var $monsterName	= array (
     '敵国軍',         # 0
     '反乱軍',       # 1
     'いのら',       # 2
     'サンジラ',     # 3
     'クジラ',       # 4
     'キングいのら', # 5
     '爆撃機',         # 6
    );
  // 最低体力、体力の幅、特殊能力、経験値、死体の値段
  var $monsterBHP	= array(3, 3, 4, 5, 6, 8, 8);
  var $monsterDHP	= array(0, 5, 1, 2, 2, 2, 2);
  var $monsterSpecial	= array(0x0, 0x0, 0x100, 0x400, 0x20000, 0x2, 0x200);
  var $monsterExp	= array(8, 6, 5, 7, 10, 10, 9);
  var $monsterValue	= array(1000, 300, 200, 400, 600, 500, 900);
  // 特殊能力の内容は、(各能力は 1bit に割り当てる)
  // 0x0 特になし
  // 0x1 足が速い(最大2歩あるく)
  // 0x2 足がとても速い(最大何歩あるくか不明)
  // 0x100 ミサイル叩き落とす
  // 0x200 飛行移動能力

  // ターン杯を何ターン毎に出すか
  var $turnPrizeUnit	= 100;

  // 賞の名前
  var $prizeName	= array (
    'ターン杯',
    '繁栄賞',
    '超繁栄賞',
    '究極繁栄賞',
    '平和賞',
    '超平和賞',
    '究極平和賞',
    '災難賞',
    '超災難賞',
    '究極災難賞',
    );

  // 記念碑
  var $monumentNumber	= 54;
  var $monumentName	= array (
    '建国記念碑', '国立墓地', '解放記念碑', '成長記念碑', '平和祈念塔', 'キャッスル城', 'モノリス', '聖樹', '戦いの碑', 'ラスカル', 'カテドラル', '宮殿', '刑務所', '白鳥城', '官邸', '自由の女神', 'モアイ', '地球儀', 'バッグ', '南夏電波塔', 'ダークいのら像', 'テトラ像', 'はねはむ像', 'ロケット', 'ピラミッド', 'アサガオ', 'チューリップ', 'チューリップ', '水仙', 'サボテン', '仙人掌', '魔方陣', '神殿', '神社', '闇石', '地石', '氷石', '風石', '炎石', '光石', '卵', '卵', '卵', '卵', '古代遺跡', '銀杏', '壊れた侵略者', '憩いの公園', '桜', '向日葵', 'ポートタワー', '灯台', '総督府', '幸福の女神像'
    );

  // 人工衛星
  // 何種類あるか
  var $EiseiNumber = 6;

  // 名前
  var $EiseiName = array (
    '気象衛星', '観測衛星', '迎撃衛星', '軍事衛星', '防衛衛星', 'イレギュラー'
    );

  var $Captext = array('なし','原始的','未成熟','発展途上','地方分権/先進的','中央集権/先進的');

  /********************
      外見関係
   ********************/
  // 大きい文字
  var $tagBig_ = '<span class="big">';
  var $_tagBig = '</span>';
  // 島の名前など
  var $tagName_ = '<span class="islName">';
  var $_tagName = '</span>';
  // 薄くなった島の名前
  var $tagName2_ = '<span class="islName2">';
  var $_tagName2 = '</span>';
    //首都の名前など
  var $tagCapName_ = '<span class="capName">';
  var $_tagCapName = '</span>';
  // 順位の番号など
  var $tagNumber_ = '<span class="number">';
  var $_tagNumber = '</span>';
  // 順位表における見だし
  var $tagTH_ = '<span class="head">';
  var $_tagTH = '</span>';
  // 開発計画の名前
  var $tagComName_ = '<span class="command">';
  var $_tagComName = '</span>';
  // 災害
  var $tagDisaster_ = '<span class="disaster">';
  var $_tagDisaster = '</span>';
  // ローカル掲示板、観光者の書いた文字
  var $tagLbbsSS_ = '<span class="lbbsSS">';
  var $_tagLbbsSS = '</span>';
  // ローカル掲示板、島主の書いた文字
  var $tagLbbsOW_ = '<span class="lbbsOW">';
  var $_tagLbbsOW = '</span>';
  // 順位表、セルの属性
  var $bgTitleCell   = 'class="TitleCell"';   // 順位表見出し
  var $bgNumberCella  = 'class="NumberCella"';  // 順位表順位
  var $bgNumberCellb  = 'class="NumberCellb"';  // 順位表順位
  var $bgNumberCellc  = 'class="NumberCellc"';  // 順位表順位
  var $bgNumberCelld  = 'class="NumberCelld"';  // 順位表順位
  var $bgNameCell    = 'class="NameCell"';    // 順位表島の名前
  var $bgInfoCell    = 'class="InfoCell"';    // 順位表島の情報
  var $bgCommentCell = 'class="CommentCell"'; // 順位表コメント欄
  var $bgInputCell   = 'class="InputCell"';   // 開発計画フォーム
  var $bgMapCell     = 'class="MapCell"';     // 開発計画地図
  var $bgCommandCell = 'class="CommandCell"'; // 開発計画入力済み計画

  var $spanend = '</span>';
  var $tplcss;
  /********************
      地形番号
   ********************/

  var $landSea		=  0; // 海
  var $landWaste	=  1; // 荒地
  var $landPlains	=  2; // 平地
  var $landTown		=  3; // 町系
  var $landForest	=  4; // 森
  var $landMountain =  5; // 山
  var $landPoll     =  6; // 汚染土壌
  var $landOil		=  7; // 海底油田
  var $landFarm		=  8; // 農場
  var $landNursery  =  9; // 養殖場
  var $landMarket	= 10; // 市場
  var $landFactory	= 11; // 工場
  var $landFFactory	= 12; // 養豚場
  var $landHatuden  = 13; // 発電所
  var $landSecpol   = 14; // 秘密警察
  var $landFBase    = 15; // 他国軍基地
  var $landBank     = 16; // 銀行
  var $landBase		= 17; // ミサイル基地
  var $landHDefence	= 26; // 防衛施設(偽装)
  var $landSdefence = 27; // 海底防衛施設
  var $landNewtown	= 28; // ニュータウン
  var $landBigtown	= 29; // 現代都市
  var $landProcity  = 30; // 防災都市
  var $landPark     = 32; // 遊園地
  var $landPort     = 33; // 港
  var $landMonument	= 34; // 記念碑
  var $landZorasu   = 35; // 揚陸艦
  var $landMonster  = 36; // 怪獣
  var $landMyhome   = 38; // 行政府
  var $landnMountain = 39; //鉱山が作れない山
  var $landCapital	 = 41; //首都
  var $landIndCity = 42; //工業都市


  // 地形番号50以上は鉱山系
  var $landStonemine = 50; // 採石場
  var $landSteel     = 51; // 鉄鉱山
  var $landCoal      = 52; // 炭坑
  /********************
       コマンド
   ********************/
  // コマンド分割
  // このコマンド分割だけは、自動入力系のコマンドは設定しないで下さい。
  var $commandDivido =
	array(
	'開発,0,10',  // 計画番号00～10
	'建設,11,40', // 計画番号11～40
	'製造,41,50', // 計画番号41～50
	'攻撃,51,70', // 計画番号51～70
	'貿易,71,82', // 計画番号71～83
	'運営,83,90'  // 計画番号84～90
	);
  // 注意：スペースは入れないように
  // ○→	'開発,0,10',  # 計画番号00～10
  // ×→	'開発, 0  ,10  ',  # 計画番号00～10

  var $commandTotal	= 72; // コマンドの種類
  // 順序
  var $comList;
  // 整地系
  var $comPrepare	= 1; // 整地
  var $comPrepare2	= 2; // 地ならし
  var $comReclaim	= 3; // 埋め立て
  var $comReclaim2	= 4; // 造成（急速埋め立て）
  var $comDestroy	= 5; // 掘削
  var $comDestroy2	= 6; // 連続掘削
  var $comSellTree	= 7; // 伐採
  var $comVein      = 8; // 鉱脈探査
  var $comMine      = 9; // 鉱山整備
  var $comMkResource = 10; // 資源採掘

  // 作る系
  var $comPlant		= 11; // 植林
  var $comFarm		= 12; // 農場整備
  var $comNursery   = 13; // 養殖場設置
  var $comMarket	= 14; // 市場整備
  var $comFactory	= 15; // 工場建設
  var $comSFactory	= 16; // 専門工場建設
  var $comHatuden   = 17; // 発電所
  var $comOild	    = 18; // 油田整備
  var $comFusya     = 19; // 農業改良センター設置
  var $comBase		= 20; // ミサイル基地建設
  var $comDbase		= 21; // 防衛施設建設
  var $comSdbase    = 22; // 海底防衛施設
  var $comMonument	= 23; // 記念碑建造
  var $comNewtown	= 24; // ニュータウン建設
  var $comBigtown	= 25; // 現代都市建設
  var $comProcity   = 26; // 防災都市
  var $comNPark  	= 27; // 国立公園建設
  var $comPark      = 28; // 遊園地建設
  var $comPort      = 29; // 港建設
  var $comMakeShip  = 30; // 造船
  var $comShipBack  = 31; // 船破棄
  var $comMyhome    = 32; // 行政府建設
  var $comSeeCity   = 33; // 観光都市建設
  var $comCapital	 = 34; //首都建設
  var $comIndCity	 = 35; //工業都市建設
  var $comSecpol	 = 36; //秘密警察建設
  var $comFBase 	 = 37; //他国軍基地建設

  // 製造系(島全体)
  var $comMkShell     = 41; // 砲弾製造
  var $comMkMaterial  = 42; // 建材製造
  var $comMkSteel     = 43; // 建材強化

  // 発射系
  var $comMissileNM	 = 51; // ミサイル発射
  var $comMissilePP	 = 52; // PPミサイル発射
  var $comMissileSPP  = 53; // SPPミサイル発射
  var $comMissileBT	 = 54; // BTミサイル発射
  var $comMissileSP	 = 55; // 催眠弾発射→廃止予定
  var $comMissileLD	 = 56; // 陸地破壊弾発射
  var $comEisei      = 57; // 人工衛星発射
  var $comEiseimente    = 58; // 人工衛星発修復
  var $comEiseiAtt      = 59; // 人工衛星破壊
  var $comEiseiLzr      = 60; // 衛星レーザー
  var $comSendMonster	= 61; // 陸軍派遣
  var $comTrain			= 62; // 軍事訓練

  // 貿易系
  var $comMoney     = 71; // 送金
  var $comFood      = 72; // 食料輸送
  var $comGoods     = 73; // 商品輸送
  var $comAlcohol   = 74; // 酒輸送
  var $comWood      = 75; // 木材輸送
  var $comStone     = 76; // 石材輸送
  var $comSteel     = 77; // 鉄鋼輸送
  var $comMaterial  = 78; // 建材輸送
  var $comSilver    = 79; // 銀輸送
  var $comOil       = 80; // 石油輸送
  var $comFuel      = 81; // 燃料輸送
  var $comShell     = 82; // 砲弾輸送

  // 運営系
  var $comPubinvest  = 83; // 公共投資
  var $comEduinvest  = 84; // 教育投資
  var $comSocPlan  = 85; //社会保障政策
  var $comPropaganda = 86; // パレード
  var $comIndPlan	 = 87; //工業政策
  var $comDoNothing	 = 88; // 放置
  var $comDisuse     = 89; // 鉱山廃止
  var $comGiveup	 = 90; // 島の放棄

  // 自動入力系
  var $comAutoPrepare	= 91; // フル整地
  var $comAutoPrepare2	= 92; // フル地ならし
  var $comAutoDelete	= 93; // 全コマンド消去

  var $comName;
  var $comCost;
  var $comSCost;

  // 島の座標数
  var $pointNumber;


  //首都レベル依存リスト
  var $comGovernment;

  //最大人口値
  var $maxLPop;
  // 周囲2ヘックスの座標
  var $ax = array(0, 1, 1, 1, 0,-1, 0, 1, 2, 2, 2, 1, 0,-1,-1,-2,-1,-1, 0);//(原点,X+1が3つ、X-1が3つ、X+2、X-2)
  var $ay = array(0,-1, 0, 1, 1, 0,-1,-2,-1, 0, 1, 2, 2, 2, 1, 0,-1,-2,-2);

  // コメントなどに、予\定のように\が勝手に追加される
  var $stripslashes;

  function setVariable() {
    $this->pointNumber = $this->islandSize * $this->islandSize;

    $this->comList	= array(
      $this->comPrepare,
      $this->comPrepare2,
      $this->comReclaim,
      $this->comReclaim2,
      $this->comDestroy,
	  $this->comDestroy2,
      $this->comSellTree,
      $this->comVein,
      $this->comMine,
      $this->comMkResource,
      $this->comPlant,
      $this->comFarm,
      $this->comNursery,
      $this->comFactory,
      $this->comMarket,
      $this->comSFactory,
      $this->comHatuden,
      $this->comOild,
      $this->comFusya,
      $this->comBase,
      $this->comDbase,
      $this->comSdbase,
      $this->comMonument,
      $this->comNewtown,
      $this->comBigtown,
      $this->comProcity,
	  $this->comNPark,
      $this->comPark,
      $this->comPort,
      $this->comMakeShip,
      $this->comShipBack,
	  $this->comMyhome,
	  $this->comSeeCity,
	  $this->comCapital,
	  $this->comIndCity,
	  $this->comSecpol,
	  $this->comFBase,
      $this->comMkShell,
      $this->comMkMaterial,
      $this->comMkSteel,
      $this->comMissileNM,
      $this->comMissilePP,
      $this->comMissileSPP,
      $this->comMissileBT,
      $this->comMissileSP,
      $this->comMissileLD,
      $this->comEisei,
      $this->comEiseimente,
      $this->comEiseiAtt,
      $this->comEiseiLzr,
      $this->comSendMonster,
	  $this->comTrain,
      $this->comMoney,
      $this->comFood,
      $this->comGoods,
      $this->comAlcohol,
      $this->comWood,
      $this->comStone,
      $this->comSteel,
      $this->comMaterial,
      $this->comSilver,
      $this->comOil,
      $this->comFuel,
      $this->comShell,
	  $this->comPubinvest,
	  $this->comEduinvest,
	  $this->comSocPlan,
      $this->comPropaganda,
	  $this->comIndPlan,
      $this->comDoNothing,
      $this->comDisuse,
      $this->comGiveup,
      $this->comAutoPrepare,
      $this->comAutoPrepare2,
      $this->comAutoDelete,
      );
    // 計画の名前と値段
    $this->comName[$this->comPrepare]      = '整地';
    $this->comCost[$this->comPrepare]      = 5;
    $this->comSCost[$this->comPrepare]     = 0;
    $this->comName[$this->comPrepare2]     = '地ならし';
    $this->comCost[$this->comPrepare2]     = 20;
    $this->comSCost[$this->comPrepare2]    = 0;
    $this->comName[$this->comReclaim]      = '埋め立て';
    $this->comCost[$this->comReclaim]      = 150;
    $this->comSCost[$this->comReclaim]     = -50;
    $this->comName[$this->comReclaim2]     = '造成';
    $this->comCost[$this->comReclaim2]     = 200;
    $this->comSCost[$this->comReclaim2]    = -50;
    $this->comName[$this->comDestroy]      = '掘削';
    $this->comCost[$this->comDestroy]      = 150;
    $this->comSCost[$this->comDestroy]     = 0;
    $this->comName[$this->comDestroy2]      = '連続掘削';
    $this->comCost[$this->comDestroy2]      = 200;
    $this->comSCost[$this->comDestroy]     = 0;
    $this->comName[$this->comSellTree]     = '伐採';
    $this->comCost[$this->comSellTree]     = 50;
    $this->comSCost[$this->comSellTree]    = 0;
    $this->comName[$this->comVein]         = '鉱脈探査';
    $this->comCost[$this->comVein]         = 50;
    $this->comSCost[$this->comVein]        = 0;
    $this->comName[$this->comMine]         = '鉱山整備';
    $this->comCost[$this->comMine]         = 0;
    $this->comSCost[$this->comMine]        = 0;
    $this->comName[$this->comMkResource]   = '資源採掘';
    $this->comCost[$this->comMkResource]   = -50;
    $this->comSCost[$this->comMkResource]  = 0;
    $this->comName[$this->comPlant]        = '植林';
    $this->comCost[$this->comPlant]        = 50;
    $this->comSCost[$this->comPlant]       = 0;
    $this->comName[$this->comFarm]         = '共同農場整備';
    $this->comCost[$this->comFarm]         = 20;
    $this->comSCost[$this->comFarm]        = 10;
    $this->comName[$this->comNursery]      = '養殖場設置';
    $this->comCost[$this->comNursery]      = 40;
    $this->comSCost[$this->comNursery]     = 10;
    $this->comName[$this->comMarket]       = '国営市場整備';
    $this->comCost[$this->comMarket]       = 150;
    $this->comSCost[$this->comMarket]      = 10;
    $this->comName[$this->comFactory]      = '国営工場建設';
    $this->comCost[$this->comFactory]      = 200;
    $this->comSCost[$this->comFactory]     = 50;
    $this->comName[$this->comSFactory]     = '各種専門工場建設';
    $this->comCost[$this->comSFactory]     = 0;
    $this->comSCost[$this->comSFactory]    = 0;
    $this->comName[$this->comHatuden]      = '発電所建設';
    $this->comCost[$this->comHatuden]      = 800;
    $this->comSCost[$this->comHatuden]     = 100;
    $this->comName[$this->comOild]         = '油田整備';
    $this->comCost[$this->comOild]         = 1000;
    $this->comSCost[$this->comOild]        = 50;
    $this->comName[$this->comFusya]        = '農業改良センター建設';
    $this->comCost[$this->comFusya]        = 1500;
    $this->comSCost[$this->comFusya]       = 1000;
    $this->comName[$this->comBase]         = 'ミサイル基地建設';
    $this->comCost[$this->comBase]         = 0;
    $this->comSCost[$this->comBase]        = 0;
    $this->comName[$this->comDbase]        = '防衛施設建設';
    $this->comCost[$this->comDbase]        = 0;
    $this->comSCost[$this->comDbase]       = 0;
    $this->comName[$this->comSdbase]       = '防衛艦隊配備';
    $this->comCost[$this->comSdbase]       = 2000;
    $this->comSCost[$this->comSdbase]      = 300;
    $this->comName[$this->comMonument]     = '記念碑建造';
    $this->comCost[$this->comMonument]     = 20000;
    $this->comSCost[$this->comMonument]    = -500;
    $this->comName[$this->comNewtown]      = 'ニュータウン建設';
    $this->comCost[$this->comNewtown]      = 2000;
    $this->comSCost[$this->comNewtown]     = 500;
    $this->comName[$this->comBigtown]      = '現代都市建設';
    $this->comCost[$this->comBigtown]      = 20000;
    $this->comSCost[$this->comBigtown]     = 8000;
    $this->comName[$this->comProcity]      = '防災都市化';
    $this->comCost[$this->comProcity]      = 8000;
    $this->comSCost[$this->comProcity]     = 800;
	$this->comName[$this->comNPark]    = '国立公園建設';
    $this->comCost[$this->comNPark]    = 3000;
    $this->comSCost[$this->comNPark]   = 500;
    $this->comName[$this->comPark]         = '遊園地建設';
    $this->comCost[$this->comPark]         = 2000;
    $this->comSCost[$this->comPark]        = 700;
    $this->comName[$this->comPort]         = '港建設';
    $this->comCost[$this->comPort]         = 1500;
    $this->comSCost[$this->comPort]        = 800;
	$this->comName[$this->comSeeCity]     = '観光都市建設';
    $this->comCost[$this->comSeeCity]    = 5000;
    $this->comSCost[$this->comSeeCity]   = 1500;
    $this->comName[$this->comMyhome]     = '議事堂建設';
    $this->comCost[$this->comMyhome]     = 3000;
    $this->comSCost[$this->comMyhome]    = 1600;
	$this->comName[$this->comCapital]    = '首都建設';
    $this->comCost[$this->comCapital]    = 5000;
    $this->comSCost[$this->comCapital]   = 1000;
	$this->comName[$this->comIndCity]    = '工業都市建設';
    $this->comCost[$this->comIndCity]    = 6000;
    $this->comSCost[$this->comIndCity]   = 2000;
	$this->comName[$this->comSecpol]    = '秘密警察本部設置';
    $this->comCost[$this->comSecpol]    = 2000;
    $this->comSCost[$this->comSecpol]   = 500;
	$this->comName[$this->comFBase]    = '他国軍駐屯地建設';
    $this->comCost[$this->comFBase]    = 5000;
    $this->comSCost[$this->comFBase]   = 1000;
    $this->comName[$this->comMakeShip]     = '造船';
    $this->comCost[$this->comMakeShip]     = 1000;
    $this->comSCost[$this->comMakeShip]    = 20;
    $this->comName[$this->comShipBack]     = '船破棄';
    $this->comCost[$this->comShipBack]     = 50;
    $this->comSCost[$this->comShipBack]    = 0;
    $this->comName[$this->comMkShell]      = '砲弾製造';
    $this->comCost[$this->comMkShell]      = -10;
    $this->comSCost[$this->comMkShell]     = 0;
    $this->comName[$this->comMkMaterial]   = '建材製造';
    $this->comCost[$this->comMkMaterial]   = -5;
    $this->comSCost[$this->comMkMaterial]  = 0;
    $this->comName[$this->comMkSteel]      = '建材強化';
    $this->comCost[$this->comMkSteel]      = -10;
    $this->comSCost[$this->comMkSteel]     = 0;
    $this->comName[$this->comMissileNM]    = 'ミサイル発射';
    $this->comCost[$this->comMissileNM]    = -10;
    $this->comSCost[$this->comMissileNM]   = -10;
    $this->comName[$this->comMissilePP]    = 'PPミサイル発射';
    $this->comCost[$this->comMissilePP]    = -10;
    $this->comSCost[$this->comMissilePP]   = -20;
	$this->comName[$this->comMissileSPP]    = 'SPPミサイル発射';
    $this->comCost[$this->comMissileSPP]    = -20;
	$this->comSCost[$this->comMissileSPP]   = -40;
    $this->comName[$this->comMissileBT]    = 'BTミサイル発射';
    $this->comCost[$this->comMissileBT]    = -30;
    $this->comSCost[$this->comMissileBT]   = -30;
    $this->comName[$this->comMissileSP]    = '催眠弾発射';
    $this->comCost[$this->comMissileSP]    = -20;
    $this->comSCost[$this->comMissileSP]   = -20;
    $this->comName[$this->comMissileLD]    = '陸地破壊弾発射';
    $this->comCost[$this->comMissileLD]    = -50;
    $this->comSCost[$this->comMissileLD]   = -50;
    $this->comName[$this->comEisei]        = '人工衛星打ち上げ';
    $this->comCost[$this->comEisei]        = 9999;
    $this->comSCost[$this->comEisei]       = -9999;
    $this->comName[$this->comEiseimente]   = '人工衛星修復';
    $this->comCost[$this->comEiseimente]   = 6000;
    $this->comSCost[$this->comEiseimente]  = -500;
    $this->comName[$this->comEiseiAtt]     = '衛星破壊砲発射';
    $this->comCost[$this->comEiseiAtt]     = 30000;
    $this->comSCost[$this->comEiseiAtt]    = -300;
    $this->comName[$this->comEiseiLzr]     = '衛星レーザー発射';
    $this->comCost[$this->comEiseiLzr]     = 25000;
    $this->comSCost[$this->comEiseiLzr]    = -200;
    $this->comName[$this->comSendMonster]  = '陸上部隊派遣';
    $this->comCost[$this->comSendMonster]  = 500;
    $this->comSCost[$this->comSendMonster] = -500;
    $this->comName[$this->comTrain]  = '軍事訓練';
    $this->comCost[$this->comTrain]  = 300;
    $this->comSCost[$this->comTrain] = -100;
    $this->comName[$this->comMoney]        = '送金';
    $this->comCost[$this->comMoney]        = 50;
    $this->comSCost[$this->comMoney]       = 0;
    $this->comName[$this->comFood]         = '食料輸送';
    $this->comCost[$this->comFood]         = -10000;
    $this->comSCost[$this->comFood]        = 0;
    $this->comName[$this->comGoods]        = '商品輸送';
    $this->comCost[$this->comGoods]        = -50;
    $this->comSCost[$this->comGoods]       = 0;
    $this->comName[$this->comAlcohol]      = '食肉輸送';
    $this->comCost[$this->comAlcohol]      = -50;
    $this->comSCost[$this->comAlcohol]     = 0;
    $this->comName[$this->comWood]         = '木材輸送';
    $this->comCost[$this->comWood]         = -50;
    $this->comSCost[$this->comWood]        = 0;
    $this->comName[$this->comStone]        = '石材輸送';
    $this->comCost[$this->comStone]        = -50;
    $this->comSCost[$this->comStone]       = 0;
    $this->comName[$this->comSteel]        = '鉄鋼輸送';
    $this->comCost[$this->comSteel]        = -50;
    $this->comSCost[$this->comSteel]       = 0;
    $this->comName[$this->comMaterial]     = '建材輸送';
    $this->comCost[$this->comMaterial]     = -50;
    $this->comSCost[$this->comMaterial]    = 0;
    $this->comName[$this->comSilver]       = '銀輸送';
    $this->comCost[$this->comSilver]       = -50;
    $this->comSCost[$this->comSilver]      = 0;
    $this->comName[$this->comOil]          = '石油輸送';
    $this->comCost[$this->comOil]          = -50;
    $this->comSCost[$this->comOil]         = 0;
    $this->comName[$this->comFuel]         = '燃料輸送';
    $this->comCost[$this->comFuel]         = -50;
    $this->comSCost[$this->comFuel]        = 0;
    $this->comName[$this->comShell]        = '砲弾輸送';
    $this->comCost[$this->comShell]        = -50;
    $this->comSCost[$this->comShell]       = 0;
	$this->comName[$this->comPubinvest]    = '公共投資';
    $this->comCost[$this->comPubinvest]    = 100;
    $this->comSCost[$this->comPubinvest]   = 200;
	$this->comName[$this->comEduinvest]    = '教育投資';
    $this->comCost[$this->comEduinvest]    = 100;
    $this->comSCost[$this->comEduinvest]   = 0;
	$this->comName[$this->comSocPlan]    = '社会保障政策';
    $this->comCost[$this->comSocPlan]    = 100;
    $this->comSCost[$this->comSocPlan]   = 0;
    $this->comName[$this->comPropaganda]   = 'パレード';
    $this->comCost[$this->comPropaganda]   = 1000;
    $this->comSCost[$this->comPropaganda]  = 0;
	$this->comName[$this->comIndPlan]    = '工業政策';
    $this->comCost[$this->comIndPlan]      = 0;
    $this->comSCost[$this->comIndPlan]     = 0;
    $this->comName[$this->comDoNothing]    = '放置';
    $this->comCost[$this->comDoNothing]    = 0;
    $this->comSCost[$this->comDoNothing]   = 0;
    $this->comName[$this->comDisuse]       = '鉱山廃止';
    $this->comCost[$this->comDisuse]       = 100;
    $this->comSCost[$this->comDisuse]      = 0;
    $this->comName[$this->comGiveup]       = '島の放棄';
    $this->comCost[$this->comGiveup]       = 0;
    $this->comSCost[$this->comGiveup]      = 0;
    $this->comName[$this->comAutoPrepare]  = '整地自動入力';
    $this->comCost[$this->comAutoPrepare]  = 0;
    $this->comSCost[$this->comAutoPrepare] = 0;
    $this->comName[$this->comAutoPrepare2] = '地ならし自動入力';
    $this->comCost[$this->comAutoPrepare2] = 0;
    $this->comSCost[$this->comAutoPrepare2]= 0;
    $this->comName[$this->comAutoDelete]   = '全計画を白紙撤回';
    $this->comCost[$this->comAutoDelete]   = 0;
    $this->comSCost[$this->comAutoDelete]  = 0;


	 //ゲージ表示のために最大保有数を連想配列化
	 $this->allmax = Array('money'=>$this->maxMoney,
	  'food'=>$this->maxFood,
	  'goods'=>$this->maxGoods,
	  'material'=>$this->maxMaterial,
	  'fuel'=>$this->maxFuel,
	  'shell'=>$this->maxShell,
	  'wood'=>$this->maxWood,
	  'stone'=>$this->maxStone,
	  'silver'=>$this->maxSilver,
	  'steel'=>$this->maxSteel,
	  'oil'=>$this->maxOil);

	 $this->comGovernment = array(
	  $init->comTrain => '1',
      $this->comMissileNM=>'1',
	  $this->comPubinvest=>'2',
	  $this->comEduinvest=>'2',
	  $this->comSocPlan=>'3',
      $this->comBigtown=>'3',
	  $this->comIndCity=>'3',
      $this->comEisei=>'4',
      $this->comEiseimente=>'4',
      $this->comEiseiAtt=>'4',
      $this->comEiseiLzr=>'4');

	 $this->maxLPop = array(
	$this->landTown    => array(190,500),
	$this->landNewtown => array(350,350),
	$this->landBigtown => array(1000,3000),
	$this->landProcity => array(100,200),
	$this->landSeeCity => array(150,250),
	$this->landCapital => array(50,100,150,200,300,500,1000,1500,2500,4090),
	$this->landIndCity => array(1000,1200)
	 );

	$this->tplcss = array(
		'tagBig'=>$this->tagBig_,
		'tagName'=>$this->tagName_,
		'tagName2'=>$this->tagName2_,
		'tagCapName'=>$this->tagCapName_,
		'tagNumber'=>$this->tagNumber_,
		'tagTH'=>$this->tagTH_,
		'tagComName'=>$this->tagComName_,
		'tagDisaster'=>$this->tagDisaster_,
		'tagLbbsSS'=>$this->tagLbbsSS_,
		'tagLbbsOW'=>$this->tagLbbsOW_,
		'bgTitleCell'=>$this->bgTitleCell,
		'spanend'=>$this->spanend,
		'bgNumberCella'=>$this->bgNumberCella,
		'bgNumberCellb'=>$this->bgNumberCellb,
		'bgNumberCellc'=>$this->bgNumberCellc,
		'bgNumberCelld'=>$this->bgNumberCelld,
		'bgNameCell'=>$this->bgNameCell,
		'bgInfoCell'=>$this->bgInfoCell,
		'bgCommentCell'=>$this->bgCommentCell,
		'bgInputCell'=>$this->bgInputCell,
		'bgMapCell'=>$this->bgMapCell,
		'bgCommandCell'=>$this->bgCommandCell
	);

  }

  function Init() {
    $this->CPU_start = microtime();
    $this->setVariable();
    mt_srand(time());
    // 日本時間にあわせる
    // 海外のサーバに設置する場合は次の行にある//をはずす。
    // putenv("TZ=JST-9");

    // 予\定のように\が勝手に追加される
    $this->stripslashes	= get_magic_quotes_gpc();
  }
}
?>
