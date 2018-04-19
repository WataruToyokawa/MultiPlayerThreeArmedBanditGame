<?php
require("./ipc_instruction.php");
session_start();

  // ログイン状態のチェック
if (!isset($_SESSION["USERID"])) {
  header("Location: ./doui/ConsentForm.php");
  exit;
}

  //  ワンタイムチケットを生成する。
$ticket = md5(uniqid(rand(), true));
  //  生成したチケットをセッション変数へ保存する。
$_SESSION['ticket'] = $ticket;

$amazonID = htmlspecialchars($_SESSION["USERID"], ENT_QUOTES);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<!-- ビューポートの設定 -->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link href="import.css" rel="stylesheet" type="text/css" media="all" />
	<link rel="prefetch" href="img/choice1.png">
	<link rel="prefetch" href="img/confirmation.png">
	<link rel="prefetch" href="img/result.png">
	<link rel="prefetch" href="img/socialInformation.png">
	<!--<link href="Instruction.css" rel="stylesheet" type="text/css" media="all" />-->
	<title>Instruction 1</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
</head>

<body>
<div id="wrap">
	<h1>
    Instructions
  </h1>

	<div id="overview">
	<p class="lead">
    Thank you for agreeing to participate in this study!<br><br>
	 This study is split into two parts.
  </p>
	 <ol>
	 <li>
     In the first part, you will participate with other participants in an interactive economic decision-making game. Because we would like for many participants to take part at the same time, you may have to wait for up to 5 minutes to begin the task. You will be paid for the amount of time you wait.
   </li>
	 <li>
     In the second part, you will fill out a short survey. This survey is optional, but it provides useful information about who participates in this experiment. All results are anonymous, and confidential.
   </li>
	</ol><br />
	 <p class="lead">
     At the end of the experiment, you will be paid 25 cents for your participation plus any extra bonus you earn both in the waiting room and the experimental decision task.
   </p>
	</div>

	<h2>
    Important Notes
  </h2>
	<div id="importantNote">
		<ol>
		<li>
      You can participate in this study <span class="note">only once</span>.
    </li>
		<li>
      Please <span class="note">do not restart your browser</span> once this study has started. If you do so, your participation will be terminated automatically without any payment and you will not be able to participate in this study again.
    </li>
    <li>
      Please <span class="note">do not open another tab</span> nor another browser during the experimental task. If your browser will be active in another tab, you will be automatically removed and not be able to complete the task.
    </li>
		<li>
      If you lose the Internet access in the middle of participating in this task, you will not be able to complete the task. <span class="note">Please make sure that your Internet connection is stable</span>.
    </li>
		</ol>
	</div>

	<br /><br /><br />
	<!--<p class="nextButton"><a class="nextButton" href="./instruction2.php" target="_top">Next Page</a></p>-->
	<form style="display: hidden" action="Instruction2.php" method="POST" id="form">
	    <!--<input type="hidden" id="postAmazonID" name="postAmazonID" value="<?php //echo $getAmazonId ?>"/>-->
      <input type="hidden" id="ticket" name="ticket" value="<?php echo $ticket ?>"/>
	    <p id='submit'><input type="submit" id="proceed" name="login" value="Next Page"></p>
	</form>

</div>

</body>
</html>
