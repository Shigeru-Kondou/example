<?php
$debug = false;

// Facebook API 使用準備
// Facebook上のアプリ：キャンバスページのURL
require('sdk/facebook.php');
$APP_URL = "https://tradeprice.phpfogapp.com/"; // アプリ実行のURL

// appIDとsecret を渡して php-SDK の使用開始
$facebook = new Facebook(array('appId' => '334485126649907', 'secret' => '1d45ecc6c4e82b20a0cf391279640602'));

// ■Facebookとアプリ間の情報を取得
$signe = $facebook->getSignedRequest();
// ■もしFacebook外で呼ばれていたらFacebookのURLへ移動
//if (!$signe["oauth_token"]) {
//  echo "<script type='text/javascript'>top.location.href = '$APP_URL';</script>";
//  exit;
//}

try {

  require('database.php');
  
  $pdo = new PDO("mysql:host=$server;dbname=$db", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
  $query = "SELECT * FROM ex_maizuru";
  $areaname_query = "select areaname from ex_maizuru group by areaname";
  
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>取引価格検索</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/blue/style.css" rel="stylesheet">
<link href="css/adjust.css"     rel="stylesheet">
<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>

</head>
<body>
<img src="sky-810.jpg" width="701">

<div class="container">
<h3>検索オプション</h3>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"><fieldset>
<table class="table-striped">
<?php
  if($debug && isset($_POST["areaname"])) {
    var_dump( $_POST );
  }
  $area_stmt = $pdo->prepare($areaname_query);
  $area_stmt->execute();
  $count=0;
  $wrap=9; 
  while($row = $area_stmt->fetchObject()) {
    if($count%$wrap==0) echo "<tr>";
    $checked="";
    if( isset($_POST["areaname"]) && is_array($_POST["areaname"]) && in_array( $row->areaname, $_POST["areaname"] ) )  $checked="checked";
    echo ("<td><label class=\"checkbox inline\"><input type=\"checkbox\" name=\"areaname[]\" value=\"".$row->areaname."\" ".$checked."/>".$row->areaname."</label></td>");
    if($count%$wrap==($wrap-1)) echo "</tr>\n";
    $count++;
  }
  if($count%$wrap!=0) echo "</tr>\n" ;
?>
</table>
<button type="submit" class="btn btn-mini">検索</button> <button type="reset" class="btn btn-mini">元に戻す</button>
</fieldset></form>
</div><!-- container -->

<br />

<div class="container">
<h3>凡例</h3>
<p>都市計画は以下の略語を使用しております。</p>
<table>
<tr><td>1低</td><td>第１種低層住居専用地域</td><td>2低</td><td>第２種低層住居専用地域</td></tr>
<tr><td>1中</td><td>第１種中高層住居専用地域</td><td>2中</td><td>第２種中高層住居専用地域</td></tr>
<tr><td>1住</td><td>第１種住居地域</td><td>2住</td><td>第２種住居地域</td></tr>
<tr><td>準住</td><td>準住居地域</td><td>近商</td><td>近隣商業地域</td></tr>
<tr><td>商業</td><td>商業地域</td><td>準工</td><td>準工業地域</td></tr>
<tr><td>工業</td><td>工業地域</td><td>工専</td><td>工業専用地域</td></tr>
<tr><td>調整</td><td>市街化調整区域</td><td>非線</td><td>市街化区域及び市街化調整区域外の都市計画区域</td></tr>
<tr><td>準都</td><td>準都市計画区域</td><td>区外</td><td>都市計画区域外</td><td></td><td></td></tr>
</table>
<h3>本サービスについて</h3>
<p>本サービスのデータについては、<a href="http://www.land.mlit.go.jp/webland/download.html" target="_brank">国土交通省不動産取引価格情報</a>ダウンロードサービスを使用しております。</p>
</div><!-- container -->

<br />



<br />
<table id="trades" class="tablesorter">
<?php
  // POST データがあればクエリに where 句を追加する。
  if( isset($_POST["areaname"]) && is_array($_POST["areaname"]) ) {
    $query .= " where ";
    for ( $i=0; $i<count($_POST["areaname"]); $i++ ) {
      $query .= "areaname like '".$_POST["areaname"][$i]."'";
      if ( $i!=count($_POST["areaname"])-1 ) $query .= " or ";
    }
  }
  if( $debug ) echo ( $query . "<br/>\n" );
  $stmt= $pdo->prepare($query);
  $stmt->execute();
  
  $cityplan_mapper = array( "第１種低層住居専用地域"=>"1低","第２種低層住居専用地域"=>"2低","第１種中高層住居専用地域"=>"1中","第２種中高層住居専用地域"=>"2中",
  "第１種住居地域"=>"1住","第２種住居地域"=>"2住","準住居地域"=>"準住","近隣商業地域"=>"近商","商業地域"=>"商業","準工業地域"=>"準工","工業地域"=>"工業","工業専用地域"=>"工専",
  "市街化調整区域"=>"調整","市街化区域及び市街化調整区域外の都市計画区域"=>"非線","準都市計画区域"=>"準都","都市計画区域外"=>"区外");
  echo ("<thead><tr><th>地区名</th><th>種類</th><th>最寄駅-距離(分)</th><th>取引価格</th><th>坪単価</th>" );
  echo ("<th>面積(m2)</th><th>m2単価</th><th>土地の形状</th><th>間口</th>" );
  echo ("<th>前面道路:方位</th><th>：種類</th><th>：幅員(m)</th><th>都市計画</th><th>取引時点</th><th>備考</th></tr></thead>\n" );
  echo ("<tbody>\n" );
  while($row = $stmt->fetchObject()) {
    $tradedate = mb_convert_kana($row->tradedate,"n","utf-8");
    $pattern = '/.*(\d\d).*(\d).*/';
    $tradedate=preg_replace( $pattern, "H".'\\1'.'.'.'\\2'."Q" , $tradedate);
    $cityplanning = strtr( $row->cityplanning, $cityplan_mapper );
    echo ("<tr><td>".$row->areaname."</td><td>".$row->kind."</td><td>".$row->stationname.((strlen($row->stationname)>0)?"-":"").$row->stationdistance."</td><td align=\"right\">".$row->tradeprice."</td><td align=\"right\">".$row->unitpricet."</td>\n" );
    echo ("<td align=\"right\">".$row->square."</td><td align=\"right\">".$row->unitpircem."</td><td>".$row->landshape."</td><td align=\"right\">".$row->width."</td><td>".$row->frontalroadd."</td>\n" );
    echo ("<td>".$row->frontalroadk."</td><td align=\"right\">".$row->frontalroadwidth."</td><td>".$cityplanning."</td><td>".$tradedate."</td><td>".$row->note."</td></tr>\n" );
  }
  echo ("</tbody>\n" );

}catch (PDOException $e){
  var_dump( $e->getMessage() );
}
$pdo = null;
?>
</table>

<br>

<script>
$(function() {
  $("#trades").tablesorter({ sortList: [[1,0]], widgets: ['zebra'] });
});
</script>
</body>
</html>
