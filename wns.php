<?
//------------
//WorldNewsFeeder V0.11
//------------
include("./feedcreator/include/feedcreator.class.php");
class WNSsys{
var $rsspath = "./feedcreator/news/feed.xml";
var $xmlpath = "./news/news.xml";
var $items = array("newsID"=>'',"nationID"=>'',"category"=>'',"text"=>'',"date"=>'',"turn"=>'',"gamedate"=>'',"author"=>'');

function Updater($datas){
	$this->items = array_merge($this->items,$datas);
	$this->items['date'] = date("Y-m-d\TH:i:sO");
	$newID = WNSsys::WriteXML($this->items);
	WNSsys::UpdateRSS($newID);
}
function WriteXML($newdata){
	$xml = new SimpleXMLElement($this->xmlpath,NULL,TRUE);
	$fp = fopen($this->xmlpath, 'a');
	Util::lockw($fp);//追記処理中はロック
//追記処理
	$newdata['newsID'] = WNSsys::XMLcounter($xml);
	$addnews = $xml->addChild('item');
	foreach($newdata as $key => $val){
		$addnews->addChild($key,$val);
	}
	$xml->asXML($this->xmlpath);
	Util::unlock($fp);//追記処理完了でロック解除
	return $newdata['newsID'];
	}
	
function UpdateRSS($newID){
	//ニュースをRSSファイルに書き出す
	$rss = new UniversalFeedCreator();
	$rss->useCached();
	$rss->title = "フリューゲル共同通信";
	$rss->description = "貿易版箱庭諸国の最新ニュースをお届けします";
	$rss->link = "http://tanstafl.sakura.ne.jp/";
	$rss->syndicationURL = "http://tanstafl.sakura.ne.jp/".$PHP_SELF;
	
	$newall = WNSsys::LatestRSS();
	
	foreach($newall as $eachdats){
			$item = new FeedItem();
			$item->title = $eachdats['category'].$eachdats['text'];
		    $item->link = "";
		    $item->description = $eachdats['text']."（".$eachdats['gamedate']."付 ".$eachdats['author']."電）";
		    $item->date = $eachdats['date'];
		    $item->source = "http://www.dailyphp.net";
		    $item->author = $eachdats['author'];
		    $rss->addItem($item);
	}
	$rss->saveFeed("ATOM1.0", $this->rsspath);
	}
	
function LatestRSS(){
	$alldats = simplexml_load_file($this->xmlpath);
	$newID = WNSsys::XMLcounter($alldats);
	$count = 1;
	foreach($alldats as $key => $eachdats){
		if($newID < $count+16){
			$newall[] = (array)$eachdats;
		}
	$count++;
	}
	$newall = array_reverse($newall);
	return $newall;
	}

function XMLcounter($xml){
	$count = 1;
	foreach($xml as $item){
		$count++;
	}
	return $count;
}

function MakeHTML($request,$basepath){
//閲覧用にリスト形式のニュースを出力
		$newall = WNSsys::LatestRSS();
		$count = 0;
		$html = "<ul id=\"latestnews\">\n";
			$path = $basepath.substr($this->rsspath,1);
		foreach($newall as $eachdats){
			if($count < $request){
				$html .= '<li> '.$eachdats['category']."<a href=\"{$path}\">".$eachdats['text']."</a>（".$eachdats['gamedate']."付 ".$eachdats['author']."電）</li>\n";
				$count++;
			}else{
				Break;
			}
		}
		$html .= "</ul>\n";
		$html = mb_convert_encoding($html,"UTF-8","UTF-8");
	return $html;
	//読み込んだデータを整形
	
	}
}
?>