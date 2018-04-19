<?php
session_start();

  // ログイン状態のチェック
if (!isset($_SESSION["USERID"])) {
  header("Location: ./doui/ConsentForm.php");
  exit;
}

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
	header("Location: ./doui/ConsentForm.php");
  //header("Location: https://www.mturk.com/mturk/welcome")
  exit;
}

  //ブラウザの戻るボタン対策
//unset($_SESSION['ticket']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<!-- ビューポートの設定 -->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link href="import.css" rel="stylesheet" type="text/css" media="all" />
	<!-- importing heavy staffs in advance -->
	<link rel="prefetch" href="img/machine_normal_1.png">
	<link rel="prefetch" href="img/machine_normal_2.png">
	<link rel="prefetch" href="img/machine_normal_3.png">
	<link rel="prefetch" href="img/machine_active_1.png">
	<link rel="prefetch" href="img/machine_active_2.png">
	<link rel="prefetch" href="img/machine_active_3.png">
	<!--<link href="Instruction.css" rel="stylesheet" type="text/css" media="all" />-->
	<title>Instruction 2</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
</head>

<body>
<div id="wrap">
	<h1>Instruction</h1>
	<p class="lead">
		Please read the following instructions carefully. After reading the instructions you will play a short "tutorial" version of the game, after which you will be placed in a waiting room for up to 5 minutes. You will be paid 12 cents per minutes ($7.20 per hour) for time spent in the waiting room. When all of the participants arrive or after five minutes, the task will start.
	</p>

	<h2>Economic decision-making experiment</h2>
	<div id="task1">
		<p class="lead">
		    In the task there are three slot machines. Each round you will have the option to pick one of the three slot machines to get a reward. Each slot machines differs in the average payout it provides. One of the machines may have a higher payoff than the other two machines. Although the payoffs of all the slot machines are random, you can guess which machine has a higher payoff by selecting different machines over different trials.
		</p>

 	<p class='lead'>
	    <tr>
		  <td><img src="./img/choice1.png" class="img" /></td>
	      <!--<td><img src="./img/choice2.png" class="img" /></td>-->
	      <td>
	        <p class="lead">
	          During each round you will have the option to choose one of the slot machines, by clicking on it. Please note that you will only have 7 seconds to make your choice. If you fail to make a decision before the time is up you will not receive a reward from that round.
	        </p>
	      </td>
	      <td>
	        <p class="lead">
	          After making a choice, you will earn some money. The monetary payoff for each machine is random, but some of the machines may generate a higher payoff (on average) than others. The average payoff of the machine is generally the same from round to round, although it may secretly change during the task!
	        </p>
	      </td>
	      <td><img src="./img/result.png" class="img" /></td>
	      <td>
	        <p class='lead'>
	          After seeing your reward, you will proceed to the next round and the same slot machines will appear again. You will play for 70 rounds in total and your total payout will be based on the sum of all your earnings.
	        </p><br>
	      </td>
		</tr>
 	</p>

 	<h2>You can see other participants' choices</h2>
		<p class='lead'>
			You will participate in this experiment at the same time as other participants. <span>The red number and bar below each slot machine shows how many participants chose each machine in the previous round</span>.
		</p>
		<p class="lead">
			Please note that <span>other participants are playing with the same slot machines as you</span>, so although the exact amount of money they earn may be different from round to round, the average payoff of each machine will be the same.
		</p>
		 <p class='lead'><img src="./img/socialInformation.png" class="img" /></p>

		 <p class="lead">
		 	After completing the 70 rounds, you will be asked to complete a questionnaire and shown your reward as picture below.
		 </p>
		 <p class='lead'><img src="./img/confirmation.png" class="img" /></p>
	 </div>

	<h2>Go to a tutorial session!</h2>
	<div id="importantNote">
		<p class="lead">
			In the next page, you will play a 4-round tutorial session to let you make sure how the task goes. While it mimics the actual experimental task, please note that in this tutorial:
		</p>
		<ol>
			<li><span>You will not earn any monetary reward.</span></li>
			<li><span>Red bars only indicate your own previous choice. You will not be able to see the other participants' choices.</span></li>
		</ol>
		<p class="lead">
			<br><br>Click the button below to go to tutorial!
		</p>
		<!--<ol>
		<li>You can participate in this study <span class="note">only once</span>.</li>
		<li>Please <span class="note">do not restart your browser</span> once this study has started. If you do so, your participation will be terminated automatically without any payment and you will not be able to participate in this study again.</li>
		<li>If you lose the Internet access in the middle of participating in this task, you will not be able to complete the task. <span class="note">Please make sure that your Internet connection is stable</span>. </li>
		</ol>-->
	</div>

	<br /><br /><br />
	<!--<p class="nextButton"><a class="nextButton" href="./instruction2.php" target="_top">Next Page</a></p>-->
	<form style="display: hidden" action="tutorial.php" method="POST" id="form">
	    <!--<input type="hidden" id="postAmazonID" name="postAmazonID" value="<?php //echo $getAmazonId ?>"/>-->
      <input type="hidden" id="ticket" name="ticket" value="<?php echo $ticket ?>"/>
	    <p id='submit'><input type="submit" id="proceed" name="login" value="PLAY TUTORIAL"></p>
	</form>

</div>


</body>
</html> 
