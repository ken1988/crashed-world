<?php
/*******************************************************************

  箱庭諸島２ for PHP


  hako-entrance.php,v 1.0

*******************************************************************/

require 'config.php';
require 'hako-html.php';
define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-main.php";

class HtmlEntrance extends HTML {
  function enter($urllist, $menulist) {
    global $init;
    print <<<END
<script type="text/javascript" language="JavaScript">
<!-- 
function go(obj) {
  if(obj.menulist.value) {
    obj.action = obj.menulist.value;
  }
}
-->
</script>

<h1>箱島２ 管理室入り口</h1>
<form method="post" onSubmit="go(this)">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<select name="menulist">
<option selected="selected">管理メニュー</option>

END;
    for ( $i = 0; $i < count($urllist); $i++ ) {
      print "<option value=\"{$init->baseDir}{$urllist[$i]}\">{$menulist[$i]}</option>\n";
    }
    print <<<END
</select>
<input type="submit" value="管理室へ">
</form>

END;
  }
}

class Main {
  var $urllist = array();
  var $menulist = array();

  function Main() {
    $this->urllist = array( ini_get('safe_mode') ? '/hako-mente-safemode.php' : '/hako-mente.php', '/hako-axes.php', '/hako-present.php', '/hako-edit.php');
    $this->menulist = array('データ管理','アクセスログ閲覧','プレゼント','マップエディタ');
  }

  function execute() {
    $html = new HtmlEntrance;

    $html->header();
    $html->enter($this->urllist, $this->menulist);
    $html->footer();
  }
}

$start = new Main();
$start->execute();

?>
