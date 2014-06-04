<?
header('Content-Type: image/png');
$img = Graphic_Out();
ImagePNG($img);

Class Init {
  // ���̑傫��
  var $islandSize	= 20;
}
function HTML_Out(){

}
function Data_load(){



}
function Graphic_land(){


}
function Graphic_Out(){
	$img = ImageCreate(2800,1600);

	$black =ImageColorAllocate($img, 0x00, 0x00, 0x00);
	$blue = ImageColorAllocate($img, 0x00, 0x00, 0x80);
	$gray = ImageColorAllocate($img, 0x70, 0x80, 0x90);
	$green =ImageColorAllocate($img, 0x22, 0x8B, 0x22);
	$brown =ImageColorAllocate($img, 0xA0, 0x52, 0x2D);
	$collors = Array();
	$collors = array($black,$blue,$gray,$green,$brown);

  for($ny = 0; $ny < 4; $ny++) {
  	for($nx = 0; $nx < 7; $nx++) {
      for($y = 0; $y < 20; $y++) {
        for($x = 0; $x < 20; $x++) {
	  	  	$num = rand(0,4);
			ImageFilledRectangle($img,$nx*400+$x*20,$ny*400+$y*20, $nx*400+$x*20+20,$ny*400+$y*20+20, $collors[$num]);
        }
      }
	  }
	}

	return $img;
}
?>