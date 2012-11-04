<?php
$local = false;  // ローカルでテストするときは、Facebook API を使用しない。
$debug = false;

// Facebook API 使用準備
// Facebook上のアプリ：キャンバスページのURL
require_once 'sdk/facebook.php';
require_once 'parse_signed_request.php';
$APP_ID  = "238489269614070";                   // アプリケーションID
$SECRET  = "6c16b88cde92764ee1dca12a34bc90c3";  // シークレット
$APP_URL = "https://apps.facebook.com/trade-pricing/"; // アプリ実行のURL

// appIDとsecret を渡して php-SDK の使用開始
$facebook = new Facebook(array('appId' => $APP_ID, 'secret' => $SECRET));

// signed_requestはFacebook上でアプリが読みこまれた時に渡されます。
$like_flag=0;
if(isset($_POST['signed_request'])){
  $signed_request=$_POST['signed_request'];
  $data = parse_signed_request($signed_request, $SECRET);
  if ( $debug ) var_dump( $data );
  
  // ユーザーが今見ているfacebookページをlikeしたかどうか。
  // likeしている場合は1が返ります。
  if(isset($data["page"]) && $data["page"]["liked"]){
    $like_flag=1;
  }else{
    $like_flag=0;
  }
}else {
  if ( $local ) {
    $like_flag = 1;
  }else {
    // URL直打ちの時など
    echo "<script type='text/javascript'>top.location.href = '$APP_URL';</script>";
    if ( $debug ) var_dump( $_POST );
    $like_flag=2;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>取引価格検索</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link href="css/blue/style.css" rel="stylesheet">
<link rel="stylesheet" href="css/adjust.css">
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
</head>
<body>
<?php
if ( $debug ) echo ("LIKE FLAG:".$like_flag."<br/>\n");

if ( $like_flag==0 && !$local ){
  // いいねを促す画面を表示
  echo "<img src=\"images/sky-810-4.png\" width=\"810\">\n";
} else {
try {
  if( $local ) {
    require('database-local.php');
  } else {
    require('database.php');
  }
  $pdo = new PDO("mysql:host=$server;dbname=$db", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
  $query = "SELECT * FROM ex_maizuru";
  
  
?>

<img src="images/sky-810-2.png" width="810">

<div class="container">
<h3>検索オプション</h3>
<!-- form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"><fieldset -->
<form action="<?php echo ( ($local?"http://":"https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); ?>" method="post"><fieldset>
<table class="table-striped"><thead></thead>
<tbody>
<?php
  //SELECT areaname,stationname FROM examples.ex_maizuru where (stationname like '東舞鶴') or (stationname like '松尾寺') group by areaname
  $area_east = array("丸山中町","丸山口町","丸山町","丸山西町","亀岩町","京月町","倉梯町","南浜町","字余部上",
  "字北吸","字千歳","字和田","字堂奥","字小倉","字小橋","字市場","字常","字平","字森","字河辺由里","字泉源寺","字浜","字田井",
  "字行永","常新町","愛宕上町","愛宕下町","愛宕中町","愛宕浜町","桃山町","森本町","森町","清美が丘","白浜台","字長浜","字大波下",
  "字溝尻","溝尻町","溝尻中町","矢之助町",
  "行永東町","行永桜通り","八反田北町","八反田南町",
  "字吉野","字朝来中","朝来西町","字松尾","字登尾","字鹿原",
  "字安岡","安岡町","字田中","田中町","字吉坂","田園町","鹿原西町","字木ノ下",
  "字野原","字瀬崎","字多祢寺"
  );
  
  $area_west = array("天台新町","字七日市","字万願寺","字上安","字上安久","字下安久","字上福井","字下福井","字丹波","字京口","字京田","字今田","字伊佐津",
  "字余部上","字余部下","字倉谷","字公文名","字円満寺","字北田辺","字南田辺","字喜多","字境谷","字天台","字女布","字寺内","字平野屋","字引土","字引土新",
  "字朝代","字本","字福来","字竹屋","字紺屋","字西","字西吉原","字野村寺","字青井","字高野由里","字魚屋","昭和台","清美が丘","清道新町","白浜台",
  "福来問屋町",
  "字岡田由里","字東神崎","字西神崎","字中山","字桑飼上","字桑飼下","字丸田","字大川","字久田美","字西方寺","字寺田","字吉田","字水間",
  "字真倉","字志高","字地頭","字別所","字布敷","字八田","字河原"
  );
  
  $wrap=9; 
  echo ("<tr><td colspan='".$wrap."'>東舞鶴</td></tr>\n");
  for($i=0; $i<count($area_east); $i++) {
    if($i%$wrap==0) echo "<tr>";
    $checked="";
    if( isset($_POST["areaname"]) && is_array($_POST["areaname"]) && in_array( $area_east[$i], $_POST["areaname"] ) )  $checked="checked";
    echo ("<td><label class=\"checkbox inline\"><input type=\"checkbox\" name=\"areaname[]\" value=\"".$area_east[$i]."\" ".$checked."/>".$area_east[$i]."</label></td>");
    if($i%$wrap==($wrap-1)) echo "</tr>\n";
  }
  if(count($area_east)%$wrap!=0) echo "</tr>\n" ;
  echo ("<tr><td colspan='".$wrap."'>西舞鶴</td></tr>\n");
  for($i=0; $i<count($area_west); $i++) {
    if($i%$wrap==0) echo "<tr>";
    $checked="";
    if( isset($_POST["areaname"]) && is_array($_POST["areaname"]) && in_array( $area_west[$i], $_POST["areaname"] ) )  $checked="checked";
    echo ("<td><label class=\"checkbox inline\"><input type=\"checkbox\" name=\"areaname[]\" value=\"".$area_west[$i]."\" ".$checked."/>".$area_west[$i]."</label></td>");
    if($i%$wrap==($wrap-1)) echo "</tr>\n";
  }
  if(count($area_west)%$wrap!=0) echo "</tr>\n" ;
?>
</tbody>
</table>
<input type="hidden" name="signed_request" value="<?php echo (isset($_POST['signed_request'])?$_POST['signed_request']:''); ?>">
<button type="submit" class="btn btn-mini">検索</button> <button type="reset" class="btn btn-mini">元に戻す</button>
</fieldset></form>
</div><!-- container -->

<br />

<div class="container">
<h3>凡例</h3>
<p>都市計画は以下の略語を使用しております。</p>
<table class="table-striped"><thead></thead>
<tbody>
<tr><td>1低</td><td>第１種低層住居専用地域</td><td>2低</td><td>第２種低層住居専用地域</td></tr>
<tr><td>1中</td><td>第１種中高層住居専用地域</td><td>2中</td><td>第２種中高層住居専用地域</td></tr>
<tr><td>1住</td><td>第１種住居地域</td><td>2住</td><td>第２種住居地域</td></tr>
<tr><td>準住</td><td>準住居地域</td><td>近商</td><td>近隣商業地域</td></tr>
<tr><td>商業</td><td>商業地域</td><td>準工</td><td>準工業地域</td></tr>
<tr><td>工業</td><td>工業地域</td><td>工専</td><td>工業専用地域</td></tr>
<tr><td>調整</td><td>市街化調整区域</td><td>非線</td><td>市街化区域及び市街化調整区域外の都市計画区域</td></tr>
<tr><td>準都</td><td>準都市計画区域</td><td>区外</td><td>都市計画区域外</td></tr>
</tbody>
</table>
</div>

<div class="container">
<h3>本サービスについて</h3>
<p>本サービスのデータについては、<a href="http://www.land.mlit.go.jp/webland/download.html" target="_brank">国土交通省不動産取引価格情報</a>ダウンロードサービスを使用しております。</p>
</div><!-- container -->

<br />
<br />

<div class="container">
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

</div>
<br>
<div id="fb-root"></div>
<script type="text/javascript">
(function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol + '//connect.facebook.net/ja_JP/all.js';
    document.getElementById('fb-root').appendChild(e);
}());
$(function() {
  $('#trades').tablesorter({ sortList: [[0,0]], widgets: ['zebra'] });
});
window.fbAsyncInit = function() {
  FB.init({appId: '<?php echo $facebook->getAppId(); ?>', status: true, cookie: true, xfbml: true});
  FB.Canvas.setAutoGrow({ width: 810, height: 1280 });
};
</script>
<?php
} // end of else
?>
</body></html>
