<?php
/*******************************************************************

  箱庭諸２ for PHP


  $Id: hako-log.php,v 1.1.1.1 2003/02/15 04:15:14 Watson Exp $

*******************************************************************/

class Log extends LogIO {
  function discover($name) {
    global $init;
    $this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>が発見される。");
  }
  function changeName($name1, $name2) {
    global $init;
    $this->history("{$init->tagName_}{$name1}{$init->_tagName}、名称を{$init->tagName_}{$name2}{$init->_tagName}に変更する。");
  }
    // 受賞
  function prize($id, $name, $pName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>が<strong>$pName</strong>を受賞しました。",$id);
    $this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>、<strong>$pName</strong>を受賞");
  }
  // 死滅
  function dead($id, $name) {
    global $init;
    $this->out("{$init->tagName_}${name}{$init->_tagName}から人がいなくなり、<strong>無人国</strong>になりました。", $id);
    $this->history("{$init->tagName_}${name}{$init->_tagName}、人がいなくなり<strong>無人国</strong>となる。");
  }
  // 資金をプレゼント
  function presentMoney($id, $name, $value) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に、資金<strong>{$value}{$init->unitMoney}</strong>をプレゼントしました。", $id);
  }
  // 食料をプレゼント
  function presentFood($id, $name, $value) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に、建材<strong>{$value}{$init->unitMaterial}</strong>をプレゼントしました。", $id);
  }
  function DoNothing2($id, $name, $num1,$num2) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で<strong>{$num1}{$init->unitGoods}</strong>の家具及び<strong>{$num2}{$init->unitMaterial}</strong>の建材が生産されました。",$id);
  }
  // 砲弾製造
  function Make($id, $name, $comName, $value, $kind) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>が<strong>{$value}{$kind}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id);
  }
  // 資源採取
  function Resource($id, $name, $comName, $value, $point, $kind, $kind2) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$value}{$kind2}</strong>の<strong>{$kind}</strong>得ました。",$id);
}
  // 物資足りない
  function NoGoods($id, $name, $comName, $kind) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>備蓄{$kind}不足</strong>のため中止されました。",$id);
  }
  //
  function NoAny($id, $name, $comName, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>{$str}</strong>ため中止されました。",$id);
  }
  // 対象地形の種類による失敗
  function landFail($id, $name, $comName, $kind, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が<strong>{$kind}</strong>だったため中止されました。",$id);
  }
  // 対象地形の条件による失敗
  function JoFail($id, $name, $comName, $kind, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が<strong>条件を満たていない{$kind}</strong>だったため中止されました。",$id);
  }
  // 都市の種類による失敗
  function BokuFail($id, $name, $comName, $kind, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が<strong>条件を満たした都市でなかった</strong>ため中止されました。",$id);
  }
  // 周りに町がなくて失敗
  function NoTownAround($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の<strong>周辺に人口がいなかった</strong>ため中止されました。",$id);
  }
  // 成功
  function landSuc($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 整地系ログまとめ
  function landSucMatome($id, $name, $comName, $point) {
    global $init;
    $this->out("<strong>⇒</strong> {$init->tagName_}{$point}{$init->_tagName}",$id);
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 埋蔵金
  function maizo($id, $name, $comName, $value) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$value}{$init->unitMoney}もの埋蔵金</strong>が発見されました。",$id);
  }
  function noLandAround($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に陸地がなかったため中止されました。",$id);
  }
  // 卵発見
  function EggFound($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>何かの卵</strong>を発見しました。",$id);
  }
  // 卵孵化
  function EggBomb($id, $name, $mName, $point, $lName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の{$lName}から<strong>怪獣{$mName}</strong>が生まれました。",$id);
  }
  // お土産
  function Miyage($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}側のお土産屋さん</strong>から<strong>{$value}{$str}</strong>もの収入がありました。",$id);
  }
  // 収穫
  function Syukaku($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>がもたらした豊作により、さらに<strong>{$str}</strong>もの食料が収穫されました。",$id);
  }
  // 銀行化
  function Bank($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が銀行になりました。",$id);
  }
  // 衛星打ち上げ成功
  function Eiseisuc($id, $name, $kind, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$kind}{$str}{$init->_tagComName}に成功しました。",$id);
  }
  // 衛星撃沈
  function Eiseifail($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが打ち上げは{$init->tagDisaster_}失敗{$init->_tagDisaster}したようです。",$id);
  }
  // 衛星破壊成功
  function EiseiAtts($id, $tId, $name, $tName, $comName, $tEiseiname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$init->_tagName}に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$tEiseiname}</strong>に命中。<strong>$tEiseiname</strong>は跡形もなく消し飛びました。",$id, $tId);
  }
  // 衛星破壊失敗
  function EiseiAttf($id, $tId, $name, $tName, $comName, $tEiseiname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$init->_tagName}の<strong>{$tEiseiname}</strong>に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、何にも命中せず宇宙の彼方へと飛び去ってしまいました。",$id, $tId);
  }
  // 衛星レーザー
  function EiseiLzr($id, $tId, $name, $tName, $comName, $tLname, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$point}{$init->_tagName}に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$tLname}</strong>に命中。一帯が{$str}",$id, $tId);
  }
  // 油田発見
  function oilFound($id, $name, $point, $comName, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われ、<strong>油田が掘り当てられました</strong>。",$id);
  }
  // 炭坑発見
  function Found($id, $name, $point, $comName, $kind) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われ、<strong>{$kind}が発見されました</strong>。",$id);
  }
  // 油田発見ならず
  function oilFail($id, $name, $point, $comName, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが、油田は見つかりませんでした。",$id);
  }
  // 油田発見ならず
  function MineFail($id, $name, $point, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが、何も見つかりませんでした。",$id);
  }
  // 鉱山落盤
  function RoofFall($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}落盤{$init->_tagDisaster}し、山に戻りました。",$id);
  }
  // 鉱山落盤
  function RoofFall2($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}落盤{$init->_tagDisaster}し、鉱山レベルが下がりました。",$id);
  }
  // 防衛施設、自爆セット
  function bombSet($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>の<strong>自爆装置がセット</strong>されました。",$id);
  }
  // 防衛施設、レベルアップ
  function LevelUp($id, $name, $lName, $point) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>が<strong>強化</strong>されました。",$id);
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で防衛施設が<strong>強化</strong>されたたようです。",$id);
  }
  // 防衛施設、自爆作動
  function bombFire($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>、{$init->tagDisaster_}自爆装置作動！！{$init->_tagDisaster}",$id);
  }
  // メルトダウン発生
  function CrushElector($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>で、{$init->tagDisaster_}メルトダウン発生！！{$init->_tagDisaster}一帯が水没しました。",$id);
  }
  // 日照り発生
  function Hideri($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で、{$init->tagDisaster_}日照りが続き{$init->_tagDisaster}、都市部の人口が減少しました。",$id);
  }
  // 植林orミサイル基地
  function PBSuc($id, $name, $comName, $point) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
    $this->out("こころなしか、<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の<strong>森</strong>が増えたようです。",$id);
  }
  // ハリボテ
  function hariSuc($id, $name, $comName, $comName2, $point) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
    $this->landSuc($id, $name, $comName2, $point);
  }
  // 記念碑、発射
  function monFly($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>が<strong>轟音とともに飛び立ちました</strong>。",$id);
  }
  // 実行許可ターン
  function Forbidden($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、実行が許可されませんでした。",$id);
  }
  // ミサイル撃とうとしたが天気が悪い
  function msNoTenki($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、悪天候のため中止されました。",$id);
  }
  // ミサイル撃とうとした(or 怪獣派遣しようとした)がターゲットがいない
  function msNoTarget($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、目標のに人が見当たらないため中止されました。",$id);
  }
  // ステルスミサイルログ
    function mslogS($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
    global $init;
    $missileBE = $missileB + $missileE;
    $missileH = $missiles - $missileA - $missileC - $missileBE;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$missiles}発{$init->_tagComName}の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。(有効{$missileH}発/怪獣命中{$missileD}発/怪獣無効{$missileC}発/防衛{$missileBE}発/無効{$missileA}発)",$id, $tId);
  }
  // その他ミサイルログ
  function mslog($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
    global $init;
    $missileBE = $missileB + $missileE;
    $missileH = $missiles - $missileA - $missileC - $missileBE;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$missiles}発{$init->_tagComName}の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。(有効{$missileH}発/怪獣命中{$missileD}発/怪獣無効{$missileC}発/防衛{$missileBE}発/無効{$missileA}発)",$id, $tId);
  }
  // ステルスミサイル撃ったが怪獣に叩き落とされる
  function msMonsCaughtS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に叩き落とされました。",$id, $tId);
  }
  // 通常ミサイル撃ったが怪獣に叩き落とされる
  function msMonsCaught($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に叩き落とされました。",$id, $tId);
  }
  // ステルスミサイル撃ち防衛施設に命中、レベルダウン
  function MsDamageS($id, $tId, $name, $tName, $comName, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}地点上空にて迎撃され、<strong>防衛力をダウン</strong>させました。",$id, $tId);
  }
  // ステルスミサイル撃ち鉱山に命中、レベルダウン
  function MsRoofFallS2($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、<strong>鉱山レベルをダウン</strong>させました。",$id, $tId);
  }

  // ステルスミサイル撃ち鉱山に命中、鉱山壊滅
  function MsRoofFallS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、<strong>鉱山は壊滅</strong>しました。",$id, $tId);
  }
  // ミサイル撃ち防衛施設に命中、レベルダウン
  function MsDamage($id, $tId, $name, $tName, $comName, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}地点上空にて迎撃され、<strong>防衛力をダウン</strong>させました。",$id, $tId);
  }
  // ミサイル撃ち鉱山に命中、レベルダウン
  function MsRoofFall2($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、<strong>鉱山レベルをダウン</strong>させました。",$id, $tId);
  }
  // ミサイル撃ち鉱山に命中、鉱山壊滅
  function MsRoofFall($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、<strong>鉱山は壊滅</strong>しました。",$id, $tId);
  }
  // 陸地破壊弾、山に命中
  function msLDMountain($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>は消し飛び、荒地と化しました。",$id, $tId);
  }
  // 陸地破壊弾、海底基地に命中
  function msLDSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}に着水後爆発、同地点にあった<strong>{$tLname}</strong>は跡形もなく吹き飛びました。",$id, $tId);
  }
  // 陸地破壊弾、怪獣に命中
  function msLDMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}に着弾し爆発。陸地は<strong>怪獣{$tLname}</strong>もろとも水没しました。",$id, $tId);
  }
  // 陸地破壊弾、浅瀬に命中
  function msLDSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に着弾。海底がえぐられました。",$id, $tId);
  }
  // 陸地破壊弾、その他の地形に命中
  function msLDLand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に着弾。陸地は水没しました。",$id, $tId);
  }
  // バイオミサイル着弾、汚染
  function msPollution($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に着弾。一帯が汚染されました。",$id, $tId);
  }
  // 通常ミサイル、怪獣に命中、硬化中にて無傷
  function msMonNoDamage($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$id, $tId);
  }
  // 通常ミサイル、怪獣に命中、殺傷
  function msMonKill($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>は力尽き、倒れました。",$id, $tId);
  }
  // 戦艦、怪獣に攻撃
  function senkanAttack($id, $name, $lName, $point, $tname, $tPoint) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が多弾頭ミサイルを発射し、<strong>{$tname}</strong>に命中しました。",$id, $tId);
  }
  // 海軍、海賊・揚陸部隊を撃沈
  function marineAttack($id, $name, $lName, $point, $tname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$tname}</strong>が我が国の<strong>{$lName}</strong>によって撃沈されました。",$id, $tId);
  }
  // 衛星消滅？！
  function EiseiEnd($id, $name, $tEiseiname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の<strong>{$tEiseiname}</strong>は{$init->tagDisaster_}崩壊{$init->_tagDisaster}したようです！！",$id);
  }
  // 怪獣あうち
  function BariaAttack($id, $name, $lName, $point, $mName, $tPoint) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$tPoint}{$init->_tagName}の<strong>{$mName}</strong>が強力な力場に押し潰されました。",$id, $tId);
  }
  // 怪獣の死体
  function msMonMoney($tId, $mName, $value) {
    global $init;
    $this->out("<strong>{$mName}</strong>の残骸には、<strong>{$value}{$init->unitFuel}</strong>の燃料がとれました。",$tId);
  }
  // 通常ミサイル、怪獣に命中、ダメージ
  function msMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>は苦しそうに咆哮しました。",$id, $tId);
  }
  // 通常ミサイル通常地形に命中
  function msNormal($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tName}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$id, $tId);
  }
  // バイオミサイル、怪獣に命中、突然変異
  function msMutation($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>に突然変異が生じました。",$id, $tId);
  }
  // 催眠弾が怪獣に命中
  function MsSleeper($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$init->_tagName}の<strong>{$tLname}</strong>は催眠弾によって眠ってしまったようです。",$id, $tId);
  }
  // 睡眠中の怪獣にミサイル命中
  function MsWakeup($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$init->_tagName}で眠っていた<strong>{$tLname}</strong>にミサイルが命中、<strong>{$tLname}</strong>は目を覚ましました。",$id, $tId);
  }
  // 睡眠中の怪獣が目覚める
  function MonsWakeup($id, $name, $lName, $point, $mName) {
    global $init;
      $this->out("{$init->tagName_}{$name}{$init->_tagName}で眠っていた<strong>{$mName}</strong>は目を覚ましました。",$id);
  }
  // ステルスミサイル規模減少
  function msGensyoS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、規模が減少しました。",$id, $tId);
  }
  // 通常ミサイル規模減少
  function msGensyo($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("-{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、規模が減少しました。",$id, $tId);
  }
  // ミサイル撃とうとしたが基地がない
  function msNoBase($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>ミサイル設備を保有していない</strong>ために実行できませんでした。",$id);
  }
  // ミサイル撃とうとしたが最大発射数を超えた
  function msMaxOver($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>最大発射数を超えた</strong>ために実行できませんでした。",$id);
  }
  // 砲弾を作ろうとしたが砲弾工場がない
  function NoFactory($id, $name, $comName, $kind) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>{$kaind}工場を保有していない</strong>ために実行できませんでした。",$id);
  }
  // ミサイル難民到着
  function msBoatPeople($id, $name, $achive) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}にどこからともなく<strong>{$achive}{$init->unitPop}もの難民</strong>が漂着しました。<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}は快く受け入れたようです。",$id);
  }
  // 陸上部隊派遣
  function monsSend($id, $tId, $name, $tName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が<strong>陸上部隊</strong>を編成。<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$init->_tagName}へ送りこみました。",$id, $tId);
  }
  // 軍事鎮圧
  function monsSendme($id,$name,$money){
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で発生した<strong>大規模な暴動</strong>を軍が鎮圧し政府の権威が回復しました。この暴動で<b>{$money}{$init->unitMoney}</b>の経済的損失が発生しました。",$id);
  }
  // 輸出
  function sell($id, $name, $comName, $value) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が<strong>{$value}{$init->unitFood}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id);
  }
  // 援助
  function aid($id, $tId, $name, $tName, $comName, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$init->_tagName}へ<strong>{$str}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id, $tId);
  }
  // 誘致活動
  function propaganda($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 放棄
  function giveup($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}は放棄され、<strong>無人</strong>になりました。",$id);
    $this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}、放棄され<strong>無人</strong>となる。");
  }
  // 油田からの収入
  function oilFuel($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>から、<strong>{$str}</strong>の収益が上がりました。",$id);
  }
  // 油田枯渇
  function oilEnd($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は枯渇したようです。",$id);
  }
  // 油田爆発
  function OilBomb($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点の油田が{$init->tagDisaster_}爆発事故{$init->_tagDisaster}を起こし、一帯が<strong>水没</strong>しました。",$id);
  }
  // 遊園地からの収入
  function ParkMoney($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>から、<B>{$str}</B>の収益が上がりました。",$id);
  }
  // 遊園地のイベント
  function ParkEvent($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>でイベントが開催され、<B>{$str}</B>の食料が消費されました。",$id);
  }
  // 遊園地のイベント増収
  function ParkEventLuck($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>で開催されたイベントが成功して<B>{$str}</B>の収益が上がりました。",$id);
  }
  // 遊園地のイベント減収
  function ParkEventLoss($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>で開催されたイベントが失敗して<B>{$str}</B>の損失がでました。",$id);
  }
  // 遊園地が閉園
  function ParkEnd($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>は施設が老朽化したため閉園となりました。",$id);
  }
  // 怪獣、防衛施設を踏む
  function monsMoveDefence($id, $name, $lName, $point, $mName) {
    global $init;
    $this->out("<strong>{$mName}</strong>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>へ到達、<strong>{$lName}の自爆装置が作動！！</strong>",$id);
  }
  // 怪獣が自爆する
  function MonsExplosion($id, $name, $point, $mName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が<strong>大爆発</strong>を起こしました！",$id);
  }
  // 怪獣分裂
  function monsBunretu($id, $name, $lName, $point, $mName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に<strong>{$mName}</strong>が分裂しました。",$id);
  }
  // 怪獣動く
  function monsMove($id, $name, $lName, $point, $mName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>{$mName}</strong>に踏み荒らされました。",$id);
  }
  // ぞらす動く
  function ZorasuMove($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に<strong>敵軍</strong>が上陸しました。",$id);
  }
  // 火災
  function fire($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}火災{$init->_tagDisaster}により壊滅しました。",$id);
  }
  // 火災未遂
  function firenot($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$point}{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}火災{$init->_tagDisaster}により被害を受けました。",$id);
  }
  // 広域被害、海の建設
  function wideDamageSea2($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は跡形もなくなりました。",$id);
  }
  // 広域被害、怪獣水没
  function wideDamageMonsterSea($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の陸地は<strong>{$lName}</strong>もろとも水没しました。",$id);
  }
  // 広域被害、水没
  function wideDamageSea($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は<strong>水没</strong>しました。",$id);
  }
  // 広域被害、怪獣
  function wideDamageMonster($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は消し飛びました。",$id);
  }
  // 広域被害、荒地
  function wideDamageWaste($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は一瞬にして<strong>荒地</strong>と化しました。",$id);
  }
  // 地震発生
  function earthquake($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で大規模な{$init->tagDisaster_}地震{$init->_tagDisaster}が発生！！",$id);
  }
  // 地震被害
  function eQDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により壊滅しました。",$id);
  }
  // 地震被害
  function EQDown($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により半壊しました。",$id);
  }
  // 地震被害未遂
  function eQDamagenot($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により被害を受けました。",$id);
  }
  // 不況になる
  function Resession($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}が{$init->tagDisaster_}不況{$init->_tagDisaster}に！！",$id);
  }
  // 不況倒産
  function RsDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}不況{$init->_tagDisaster}により倒産しました。",$id);
  }
  // 不況倒産未遂
  function RsDamagenot($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}不況{$init->_tagDisaster}により被害を受けました。",$id);
  }
  // 飢餓
  function Starve($id, $name, $kind) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の{$init->tagDisaster_}{$kind}が不足{$init->_tagDisaster}しています！！",$id);
  }
  // 食料不足被害
  function svDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>に<strong>食料を求めて住民が殺到</strong>。<strong>{$lName}</strong>は壊滅しました。",$id);
  }
  // 人口減少被害
  function popDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>から<strong>住民が逃亡</strong>。<strong>{$lName}</strong>は荒地になりました。",$id);
  }
  // 逃亡発生
  function popDec($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}から住民が逃亡しています！",$id);
  }
  // 津波発生
  function tsunami($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}付近で{$init->tagDisaster_}津波{$init->_tagDisaster}発生！！",$id);
  }
  // 津波被害
  function tsunamiDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}津波{$init->_tagDisaster}により崩壊しました。",$id);
  }
  // 怪獣現る
  function monsCome($id, $name, $mName, $point, $lName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に<strong>{$mName}</strong>出現！！{$init->tagName_}{$point}{$init->_tagName}の<strong>{$lName}</strong>が踏み荒らされました。",$id);
  }
  // ぞらす現る
  function ZorasuCome($id, $name, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}に<strong>揚陸艦</strong>出現！！",$id);
  }
  // 怪獣呼ばれる
  function monsCall($id, $name, $mName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が天に向かって咆哮しました！",$id);
  }
  // 怪獣ワープ
  function monsWarp($id, $tId, $name, $mName, $point, $tName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}</A>{$init->_tagName}にワープしました！",$id, $tId);
  }
  // 怪獣による資金増加
  function MonsMoney($id, $name, $mName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が<strong>{$str}</strong>の金をばら撒きました。",$id);
  }
  // 怪獣による食料増加
  function MonsFood($id, $name, $mName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が撒き散らした栄養たっぷりウンコの影響で、食料が<strong>{$str}</strong>増産されました。",$id);
  }
  // 怪獣による資金減少
  function MonsMoney2($id, $name, $mName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>によって、の資金<strong>{$str}</strong>が強奪されました。",$id);
  }
  // 怪獣による食料減少
  function MonsFood2($id, $name, $mName, $point, $str) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$mName}</strong>が撒き散らした悪臭漂うウンコの影響で、食料が<strong>{$str}</strong>腐敗しました。",$id);
  }
  // 地盤沈下発生
  function falldown($id, $name,$area) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で面積{$area}で{$init->tagDisaster_}地盤沈下{$init->_tagDisaster}が発生しました！！",$id);
  }
  // 地盤沈下被害
  function falldownLand($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は海の中へ沈みました。",$id);
  }
  // 台風発生
  function typhoon($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に{$init->tagDisaster_}台風{$init->_tagDisaster}上陸！！",$id);
  }

  // 台風被害
  function typhoonDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}台風{$init->_tagDisaster}で飛ばされました。",$id);
  }
  // 隕石、その他
  function hugeMeteo($id, $name, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点に{$init->tagDisaster_}巨大隕石{$init->_tagDisaster}が落下！！",$id);
  }
  // 恵みの雨
  function HardRain($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}でほどよく{$init->tagDisaster_}雨{$init->_tagDisaster}が降っているようです♪",$id);
  }
  // 酸性雨
  function HardRain2($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}にて{$init->tagDisaster_}酸性雨{$init->_tagDisaster}が降っているもようです♪",$id);
}

  // 酸性雨（枯れる）
  function NoTree($id, $name, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の森林は{$init->tagDisaster_}酸性雨{$init->_tagDisaster}より<strong>壊滅</strong>しました！",$id);
}

  // 恵みの雨効果
  function IncTree($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に降った{$init->tagDisaster_}雨{$init->_tagDisaster}は森林に発育に貢献したようです♪",$id);
}

  // 酸性雨による被害
  function IncTree2($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に降った{$init->tagDisaster_}酸性雨{$init->_tagDisaster}により森林に被害が出ている模様です！",$id);
}

  // 恵みの雨効果（植林）
  function NewTree($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に降った{$init->tagDisaster_}雨{$init->_tagDisaster}の恩恵により新たに森林地帯が出来た模様です♪",$id);
}
  // 記念碑、落下
  function monDamage($id, $name, $point) {
    global $init;
    $this->out("<strong>何かとてつもないもの</strong>が<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点に落下しました！！",$id);
  }
  // 隕石、海
  function meteoSea($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下しました。",$id);
  }
  // 隕石、山
  function meteoMountain($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は消し飛びました。",$id);
  }
  // 隕石、海底基地
  function meteoSbase($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は崩壊しました。",$id);
  }
  // 隕石、怪獣
  function meteoMonster($id, $name, $lName, $point) {
    global $init;
    $this->out("<strong>{$lName}</strong>がいた<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、陸地は<strong>怪獣{$lName}</strong>もろとも水没しました。",$id);
  }
  // 隕石、浅瀬
  function meteoSea1($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、海底がえぐられました。",$id);
  }
  // 隕石、その他
  function meteoNormal($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、一帯が水没しました。",$id);
  }
  // 噴火
  function eruption($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点で{$init->tagDisaster_}火山が噴火{$init->_tagDisaster}、<strong>山</strong>が出来ました。",$id);
  }
  // 噴火、浅瀬
  function eruptionSea1($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で陸地になりました。",$id);
  }
  // 噴火、海or海基
  function eruptionSea($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で海底が隆起、浅瀬になりました。",$id);
  }
  // 噴火、その他
  function eruptionNormal($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で壊滅しました。",$id);
  }
  // 海底探索の油田
  function tansakuoil($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が油田を発見！",$id);
  }
  // 周りに海がなくて失敗
  function NoSeaAround($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に海がなかったため中止されました。",$id);
  }
  // 周りに浅瀬がなくて失敗
  function NoShoalAround($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に浅瀬がなかったため中止されました。",$id);
  }
  // 海がなくて失敗
  function NoSea($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地が海でなかったため中止されました。",$id);
  }
  // 港がないので、造船失敗
  function NoPort($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<b>港</b>がなかったため中止されました。",$id);
  }
  // 港がないので、造船失敗
  function NoPortT($id, $name, $tid, $tname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた貿易は、<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tid}\">{$init->tagName_}{$tname}</A>{$init->_tagName}に<b>港</b>がなかったため中止されました。",$id);
  }
  // 船破棄
  function ComeBack($id, $name, $comName, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の{$init->tagComName_}{$comName}{$init->_tagComName}が実行され、<strong>{$lName}</strong>は破棄されました。",$id);
  }
  // 船の最大所有数
  function maxShip($id, $name, $comName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>船の最大所有数条約に違反してしまう</strong>ため許可されませんでした。",$id);
  }
  // 港閉鎖
  function ClosedPort($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>は閉鎖したようです。",$id);
  }
  // 海賊船現る
  function VikingCome($id, $name, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}に<B>海賊船</B>出現！！",$id);
  }
  // 海賊船去る
  function VikingAway($id, $name, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}から<B>海賊船</B>がどこかに去っていきました。",$id);
  }
  // 海賊攻撃
  function VikingAttack($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<b>{$lName}</b>は<B>海賊船</B>によって沈没させられました。",$id);
  }
  // 海賊船、強奪
  function RobViking($id, $name, $money, $food) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で<B>海賊</B>が<b>{$money}{$init->unitMoney}</b>の金と<b>{$food}{$init->unitFood}</b>の食料を強奪していきました。",$id);
  }
  // 船座礁
  function RunAground($id, $name, $lName, $point) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>$lName</B>は{$init->tagDisaster_}座礁{$init->_tagDisaster}しました。",$id);
  }
  // 戦艦ステルスミサイル迎撃
  function msInterceptS($id, $tId, $name, $tName, $comName, $point, $missileE) {
    global $init;
    $this->secret("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$id, $tId);
	$this->late("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$tId);
  }
  // 戦艦通常ミサイル迎撃
  function msIntercept($id, $tId, $name, $tName, $comName, $point, $missileE) {
    global $init;
    $this->out("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$id, $tId);
  }
  // すでにある
  function IsFail($id, $name, $comName, $land) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、すでに<strong>{$land}</strong>があるため中止されました。",$id);
  }
  // 警告
  function gvAlert($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の民衆が政権に不満を持ち始めています。",$id);
  }

  // デモ活動
  function gvDemo($id, $name) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で不満を持った民衆によるデモ活動が行われました。",$id);
  }
  // 倉庫襲撃
  function gvRob($id, $name, $money) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}での<B>暴動</B>により<b>{$money}{$init->unitMoney}</b>の経済的損失が発生しました。",$id);
  }
  // 投資成功
  function InvestSuc($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 投資削減
  function InvestDel($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が削減されました。",$id);
  }
  // 投資失敗
  function InvestFail($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は中止されました。",$id);
  }
  // パレード
  function PropaFail($id, $name, $comName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で実行された{$init->tagComName_}{$comName}{$init->_tagComName}は幸福度が低すぎて効果が有りませんでした。",$id);
  }
  // 生産停止
  function ProductStop($id, $name) {
    global $init;
	$this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の工業生産は倉庫が製品で溢れているため停止されました。",$id);
  }
  // 生産量表示
  function ShowProduct($id, $name, $sgoods,$smoney) {
    global $init;
    $this->out("今期の工業生産高:{$sgoods}{$init->unitMoney},今期の商業売上高{$smoney}{$init->unitMoney}",$id);
  }
  // 軍事訓練
  function TrSuc($id, $name, $comName, $point) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で{$init->tagComName_}{$comName}{$init->_tagComName}が行われたようです。",$id);
  }
  // 軍事訓練失敗
  function TrFail($id, $name, $comName, $point) {
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、{$init->tagName_}{$point}{$init->_tagName}が<strong>{$kind}</strong>だったため中止されました。",$id);
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$init->_tagName}</A>で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は中止されたようです。",$id);
  }
  // 秘密警察
  function CutSecpol($id,$name){
    global $init;
    $this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}で予定されていた<strong>反政府策動</strong>は政府機関の活躍により見事阻止されました。",$id);
  }
  // ストライキ
  function Strike($id,$name){
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}の<strong>秘密警察</strong>がストライキに突入しました。",$id);
  }
  //観光都市人口増
  function Booming($id, $name, $lName, $point){
  	global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>が人気を集めています。",$id);
  	  }
  //観光都市人口減
  function Shrinking($id, $name, $lName, $point){
  	global $init;
  	$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$point}{$init->_tagName}の<B>{$lName}</B>の人気が落ちています。",$id);
  }
  // 駐屯
  function FBSuc($id, $name,$point,$tid) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}{$point}</A>{$init->_tagName}に{$init->tagComName_}我が軍の駐屯地{$init->_tagComName}が建設されました。",$tid);
  }
  //駐屯地デバッガー
  function FBDeb($id, $name,$tid,$tname) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$init->_tagName}に<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tid}\">{$init->tagName_}{$tname}</A>{$init->_tagName}が駐屯中。",$tid);
  }
  // デバッガー
  function Debuger($id, $name, $lName, $point ,$opoint,$olName) {
    global $init;
    $this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}</A>{$opoint}→{$point}{$init->_tagName}<strong>{$olName}→{$lName}</strong>",$id);
  }
}

?>
