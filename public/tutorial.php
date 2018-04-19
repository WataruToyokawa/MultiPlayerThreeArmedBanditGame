<?php
session_start();

	// ログイン状態のチェック
if (!isset($_SESSION["USERID"])) {
  header("Location: ./doui/ConsentForm.php");
  exit;
}

	// PHP の値をJavaScriptに渡す処理
function json_safe_encode($data){
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

$amazonID = htmlspecialchars($_SESSION['USERID'], ENT_QUOTES);

  //  ポストされたワンタイムチケットを取得する。
$ticket = 'hoge';
if(isset($_POST['ticket'])) {
  $ticket = $_POST['ticket'];
}
  //  SESSION 変数に入っているワンタイムチケットを取得する。
$save = 'foo';
if(isset($_SESSION['ticket'])) {
$save = $_SESSION['ticket'];
}

	//  ポストされたワンタイムチケットの中身が空だった、または、ポス
	//  トすらされてこなかった場合、不正なアクセスとみなしてdoui へ
	//  る。
if ($ticket === '') {
	//echo 'no id';
	header("Location: ./doui/ConsentForm.php");
  exit;
}
if ($ticket !== $save) {
	//echo 'ticket !== save';
	//header("Location: ./doui/ConsentForm.php");
  echo $save;
  //header("Location: https://www.mturk.com/mturk/welcome")
  exit;
}

  //ブラウザの戻るボタン対策
unset($_SESSION['ticket']);
?>

<!DOCTYPE html>
<html>
<head>
	<!-- ビューポートの設定 -->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="import.css" rel="stylesheet" type="text/css" media="all" />
  <link href="mainPage.css" media="all" rel="stylesheet" type="text/css">
  <!-- importing heavy staffs in advance -->
	<link rel="prefetch" href="img/machine_normal_1.png">
	<link rel="prefetch" href="img/machine_normal_2.png">
	<link rel="prefetch" href="img/machine_normal_3.png">
	<link rel="prefetch" href="img/machine_active_1.png">
	<link rel="prefetch" href="img/machine_active_2.png">
	<link rel="prefetch" href="img/machine_active_3.png">
	<title>Tutorial</title>
	<!--<link href="firstPage.css" media="screen" rel="stylesheet" type="text/css">-->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
	<script id='script_json' src='./tutorial.js'
		data-amazonID         ='<?php echo json_safe_encode($amazonID); ?>'
    data-ticket           ='<?php echo json_safe_encode($ticket); ?>'
    data-save             ='<?php echo json_safe_encode($save)    ; ?>'
	></script>
</head>
<body>
	<div id='wrap'>
		<h1>Tutorial Game</h1>
    <h2 id='guid'></h2>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <div id='current_round'></div>
    <div id='total_earning'></div>
    <div id='box_area'></div>

		<div>
        <div id='tutorial_ending'></div>
        <div id='game_start'></div>
		</div>
	</div>
	<div id="footer">
    <table>
        <tr>
          <td><img src="./img/machine_normal_1.png" class="img_hidden" /></td>
          <td><img src="./img/machine_normal_2.png" class="img_hidden" /></td>
          <td><img src="./img/machine_normal_3.png" class="img_hidden" /></td>
          <td><img src="./img/machine_active_1.png" class="img_hidden" /></td>
          <td><img src="./img/machine_active_2.png" class="img_hidden" /></td>
          <td><img src="./img/machine_active_3.png" class="img_hidden" /></td>
        </tr>
      </table>
	</div>
</body>
</html>
