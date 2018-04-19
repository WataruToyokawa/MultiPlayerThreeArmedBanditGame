<?php
  require("../ipc_instruction.php");
  session_start();



  // エラーメッセージ
  $errorMessage = "";

  //セッションNO.(新規実験セッションの度に書換える)
  $sessionNo = 8888; //(8888:test, )

  //タスク番号
  $_SESSION['taskVer']= 0;//(GC3 -> 1:gsp, 2:gps, 3:spg, 4:sgp, 5:pgs, 6:psg)

  //１セッションの最大参加人数
  $groupSize = 1500;

  // 画面に表示するため特殊文字をエスケープする
  $viewUserId = 0;
  if(isset($_POST['workerID'])){
    $viewUserId = htmlspecialchars($_POST["workerID"], ENT_QUOTES);
  }

  //IP取得
  $host = getenv('REMOTE_HOST');
  $addr = getenv('REMOTE_ADDR');
    if(!$host or $host == $addr){$host = gethostbyaddr($addr);}
    if(!$addr or $host == $addr){$addr = gethostbyname($host);}
    if(!$host){$host = $addr;}
    if(!$addr){$addr = $host;}

  /*//データベースに接続する(expserv01.let.hokudai.ac.jp上のmySQL)
  mysql_connect('localhost', 'exp', '') or die(mysql_error());
  mysql_select_db('test') or die(mysql_error());
  mysql_query('SET NAMES UTF8');*/


  //航 mac's Localhost上の MAMP でテストの場合はこっち！
  mysql_connect('localhost', 'root', 'root') or die(mysql_error());
  mysql_select_db('workerID') or die(mysql_error());
  mysql_query('SET NAMES UTF8');


  //このセッションに既に何人が参加してるか数える
  $checkSql_num = sprintf('SELECT dateTime FROM workerID WHERE sessionNo=%d',
        mysql_real_escape_string($sessionNo));
  $checkNum = mysql_query($checkSql_num);
  $groupMembers = array();
  while ($row3 = mysql_fetch_array($checkNum, MYSQL_NUM)){
        $k = sprintf('%s', $row3[0]);
        array_push($groupMembers, $k);
  }

    // proceedボタンが押された場合
  if (isset($_POST["proceed"])) {

      // 認証成功
    if($_POST["agreement"] == "disagreed")
    {
      $errorMessage = '<font color="#df4839">'."You have not agreed to participate in this HIT.".'</font>';
    }
    elseif (count($groupMembers) >= $groupSize)
    {
        //人数オーバーだよーん
        //※今回の実験では人数オーバーにはならないよう, groupSize を超デカくする
      $errorMessage = '<font color="#df4839">'."Sorry, this experiment has already gathered sufficient number of participants.<br />Thank you for coming!".'</font>';
    }
    elseif ($_POST["agreement"] == "agreed" && isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] && count($groupMembers) < $groupSize)
    {
        // セッションIDを新規に発行する
      session_regenerate_id(TRUE);
      $_SESSION["USERID"] = $viewUserId;

        //workerIDが既にデータベースに存在しないか調べる
      $checkSql = sprintf('SELECT dateTime FROM workerID WHERE workerID="%s"',
        mysql_real_escape_string($viewUserId));
      $checkID = mysql_query($checkSql);
      $isID = array(); //入力されたworkerIDと同じ文字列が入った行のtimeを格納する配列
      while ($row = mysql_fetch_array($checkID, MYSQL_NUM)){
        $i = sprintf('%s', $row[0]);
        array_push($isID, $i);
      }

        //IPアドレスが既にデータベースに存在しないか調べる
      $checkSql_ip = sprintf('SELECT dateTime FROM workerID WHERE IP_addr="%s"',
        mysql_real_escape_string($addr));
      $checkIP = mysql_query($checkSql_ip);
      $isIP = array();
      while ($row2 = mysql_fetch_array($checkIP, MYSQL_NUM)){
        $j = sprintf('%s', $row2[0]);
        array_push($isIP, $j);
      }

      //if(count($isID) > 0 || count($isIP) > 0){
      if (count($isID) > 0) {

        //もし配列$isIDか$isIPに値が１つでも入ってたら、過去にアクセスがあったと見なし、アク禁
        $errorMessage = '<font color="#df4839">'."Your WorkerID was already used before.".'</font>';

      } else if (count($isIP) > 0) {

        //もし配列$isIPに値が１つでも入ってたら、過去にアクセスがあったと見なし、アク禁
        $errorMessage = '<font color="#df4839">'."Your IP address was already used before.".'</font>';

      } else {

        //もし新しいUSERであれば、workerIDやIP_addr, host名をデータベースへ登録する
        $insertSql = sprintf('INSERT INTO workerID SET sessionNo=%d, dateTime=NOW(), workerID="%s", IP_addr="%s", host="%s"',
          mysql_real_escape_string($sessionNo),
          mysql_real_escape_string($viewUserId),
          mysql_real_escape_string($addr),
          mysql_real_escape_string($host));
        mysql_query($insertSql);
        //インストラクションへ飛ばす（もしPROXY経由のユーザーであれば、インストラクションページ上のphpではじかれる）
        header('Location: ../Instruction.php');
        exit;

      }

    }
    else
    {
      $errorMessage = '<font color="#df4839">'."Wrong".'</font>';
    }
  }

  unset($_SESSION['captcha_keystring']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link href="../import.css" rel="stylesheet" type="text/css" media="all" />
  <link href="../infoSheet.css" rel="stylesheet" type="text/css" media="all" />
  <title>agreement</title>
  <meta name="keywords" content="" />
  <meta name="description" content="" />
</head>

<body>
<div id="wrap">
  <h1>UNIVERSITY OF ST ANDREWS, UK<br />
       Decision Making Study<br />
  </h1>

  <h2>STUDY INFORMATION SHEET</h2>

  <p class="lead">Wataru Toyokawa (or Kevin?) and associates from School of Biology at University of St Andrews, UK are conducting a research study on decision making. This study is designed to improve our understanding of the factors that contribute to economic decision making in an uncertain environment. Your participation in this study is completely voluntary.
  </p>
  <div id="informationSheet">
    <h3>WHAT WILL HAPPEN IF I TAKE PART IN THIS RESEARCH STUDY?</h3>
    <div class="inner">
    <p class="lead">If you volunteer to participate in this study, you will be asked to do the following:
      <ul>
          <li>Task_1: Make several decisions that may bring some monetary payoffs to you.</li>
          <li>Task_2: Complete a brief questionnaires about your personality and background.</li>
      </ul></p>

        <p class="lead">All of these activities will take place online. Your responses will remain completely confidential.</p>
    </div>

    <h3>HOW LONG WILL I BE IN THE RESEARCH STUDY?</h3>
    <div class="inner">
    <p class="lead">On average, participation in the study will take less than 20 minutes. You may wait in a 'waiting page' up to 5 minitues until sufficient number of participants arrives. Please note that you will be paied 12 cents per minutes for any time spent in the waiting page so that your expected earnings from this task would be at least the U.S. federal minimum wage of $7.2 per hour.</p>
    </div>

    <h3>WILL I BE PAID FOR MY PARTICIPATION?</h3>
    <div class="inner">
    <p class="lead">You will receive 25 cents in exchange for your participation and reading instructions. You can also earn some extra money depending on your
          decision performances in the study in addition to your waiting bonus decribed above. Once your responses have been completed, the researchers will be alerted. We will then authorize payment to be transferred to your Mechanical Turk account through Amazon.com.</p>
    </div>

    <h3>WHO CAN I CONTACT IF I HAVE QUESTIONS ABOUT THIS STUDY?</h3>
    <div class="inner">
    <p class="lead">The Research Team:<br />
            You may contact Dr. Wataru Toyokawa at <span>a new e-mail account for this study?</span> with questions or concerns about the research or your participation in this study.</p>
    </div>

  </div>

  <div id="login">
  <form id="loginForm" name="loginForm" action="<?php print($_SERVER['PHP_SELF']) ?>" method="POST">
          <p>
            <label><input type="radio" id="agreement" name="agreement" value="agreed" required>I wish to participate in this study</label>
          </p>
          <br />
          <br />
          <p>Enter Your Worker ID<br />(Copy and paste the ID shown in the HIT page):</p>
          <p><input type="text" id="workerID" name="workerID" value="" required></p>
          <br />
          <br />
          <p>Enter text shown below:</p>
          <p><img src="index.php?<?php echo session_name()?>=<?php echo session_id()?>"></p>
          <p><input type="text" id="keystring" name="keystring" value="" required></p>
          <br />
          <br />
          <!--<input type="hidden" id="postTicket" name="postTicket" value="<?php //echo $ticket ?>"/>-->
          <p><input type="submit" id="proceed" name="proceed" value="Proceed"><div id="errorMessage"><?php echo $errorMessage ?></div></p>
  </form>
  </div>

</div>

<div id="footer">
  <table>
    <tr>
      <td><img src="../img/print-crest.png" class="img" /></td>
    </tr>
  </table>
</div>

</body>
</html>
