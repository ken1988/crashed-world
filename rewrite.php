<?
for($i = 0; $i < 42; $i++){
$fileName ='./data/hakojima.log'.$i;
$data = @file_get_contents($fileName);
$data = mb_convert_encoding($data, "UTF-8","UTF-8,SJIS");
$fp = fopen($fileName, "w");
@fwrite( $fp,$data, strlen($data)); // ファイルへの書き込み
fclose($fp);
echo $fileName;
}
?>