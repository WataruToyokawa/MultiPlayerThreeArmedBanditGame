<?php
  require("../ipc_instruction.php");
  session_start();

  // エラーメッセージ
  $errorMessage = "";

  //セッションNO.(新規実験セッションの度に書換える)
  $sessionNo = 8888; //(8888:test, )

  // database's and table's name
  $dbName = 'workerid';
  $tableName = 'amazonworkerid';

  //タスク番号
  $_SESSION['taskVer']= 0;//(0: test, | GC3 -> 1:gsp, 2:gps, 3:spg, 4:sgp, 5:pgs, 6:psg)

  //１セッションの最大参加人数
  $groupSize = 1500;

  // 画面に表示するため特殊文字をエスケープする
  $viewUserId = 'null';

  if(isset($_GET['amazonID'])){
    $_SESSION['workerID'] = htmlspecialchars($_GET["amazonID"], ENT_QUOTES);
    $viewUserId = htmlspecialchars($_GET["amazonID"], ENT_QUOTES);
  }else{
    $viewUserId = $_SESSION['workerID'];
  }

  //IP取得
  $host = getenv('REMOTE_HOST');
  $addr = getenv('REMOTE_ADDR');
    if(!$host or $host == $addr){$host = gethostbyaddr($addr);}
    if(!$addr or $host == $addr){$addr = gethostbyname($host);}
    if(!$host){$host = $addr;}
    if(!$addr){$addr = $host;}



  /*//データベースに接続する(expserv01.let.hokudai.ac.jp上のmySQL)
  mysql_connect('localhost', 'pma', '') or die(mysql_error());
  mysql_select_db($dbName) or die(mysql_error());
  mysql_query('SET NAMES UTF8');*/

  // webkeepers (toyowataexp.webk-vps.net)
  mysql_connect('localhost', 'wataru', 'nanao56T') or die(mysql_error());
  mysql_select_db($dbName) or die(mysql_error());
  mysql_query('SET NAMES UTF8');

  /*//航 mac's Localhost上の MAMP でテストの場合はこっち！
  mysql_connect('localhost', 'root', 'root') or die(mysql_error());
  mysql_select_db($dbName) or die(mysql_error());
  mysql_query('SET NAMES UTF8');*/

  //このセッションに既に何人が参加してるか数える
  $checkSql_num = sprintf('SELECT dateTime FROM %s WHERE sessionNo=%d',
        mysql_real_escape_string($tableName),
        mysql_real_escape_string($sessionNo));
  $checkNum = mysql_query($checkSql_num);
  $groupMembers = array();
  while ($row3 = mysql_fetch_array($checkNum, MYSQL_NUM)){
        $k = sprintf('%s', $row3[0]);
        array_push($groupMembers, $k);
  }

    // proceedボタンが押された場合
  if (isset($_POST["proceed"])) {

    if($viewUserId == 'null'){
      header('Location: https://www.mturk.com/mturk/welcome');
      exit;
    }

      // 認証成功
    if($_POST["agreement"] == "disagreed")
    {
      $errorMessage = '<font color="#df4839">'."You have not agreed to participate in this HIT.".'</font>';
      //header('Location: https://www.mturk.com/mturk/welcome');
      //exit;
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
      $checkSql = sprintf('SELECT dateTime FROM %s WHERE workerID="%s"',
        mysql_real_escape_string($tableName),
        mysql_real_escape_string($viewUserId));
      $checkID = mysql_query($checkSql);
      $isID = array(); //入力されたworkerIDと同じ文字列が入った行のtimeを格納する配列
      while ($row = mysql_fetch_array($checkID, MYSQL_NUM)){
        $i = sprintf('%s', $row[0]);
        array_push($isID, $i);
      }

        //IPアドレスが既にデータベースに存在しないか調べる
      $checkSql_ip = sprintf('SELECT dateTime FROM %s WHERE IP_addr="%s"',
        mysql_real_escape_string($tableName),
        mysql_real_escape_string($addr));
      $checkIP = mysql_query($checkSql_ip);
      $isIP = array();
      while ($row2 = mysql_fetch_array($checkIP, MYSQL_NUM)){
        $j = sprintf('%s', $row2[0]);
        array_push($isIP, $j);
      }

      // very rigorous
      /*if (count($isID) > 0) {
        //もし配列$isIDか$isIPに値が１つでも入ってたら、過去にアクセスがあったと見なし、アク禁
        $errorMessage = '<font color="#df4839">'."Your WorkerID was already used before.".'</font>';
      } else if (count($isIP) > 0) {
        //もし配列$isIPに値が１つでも入ってたら、過去にアクセスがあったと見なし、アク禁
        $errorMessage = '<font color="#df4839">'."Your IP address was already used before.".'</font>';
      } else {
        //もし新しいUSERであれば、workerIDやIP_addr, host名をデータベースへ登録する
        $insertSql = sprintf('INSERT INTO %s SET sessionNo=%d, dateTime=NOW(), workerID="%s", IP_addr="%s", host="%s"',
          mysql_real_escape_string($tableName),
          mysql_real_escape_string($sessionNo),
          mysql_real_escape_string($viewUserId),
          mysql_real_escape_string($addr),
          mysql_real_escape_string($host));
        mysql_query($insertSql);
        //インストラクションへ飛ばす（もしPROXY経由のユーザーであれば、インストラクションページ上のphpではじかれる）
        header('Location: ../Instruction.php');
        exit;
      }*/

      // mildly rigorous
      /*if (count($isID) > 0) {
        //もし配列$isIDに値が１つでも入ってたら、過去にアクセスがあったと見なし、アク禁
        $errorMessage = '<font color="#df4839">'."Your WorkerID was already used before.".'</font>';
      } else {
        //もし新しいUSERであれば、workerIDやIP_addr, host名をデータベースへ登録する
        $insertSql = sprintf('INSERT INTO %s SET sessionNo=%d, dateTime=NOW(), workerID="%s", IP_addr="%s", host="%s"',
          mysql_real_escape_string($tableName),
          mysql_real_escape_string($sessionNo),
          mysql_real_escape_string($viewUserId),
          mysql_real_escape_string($addr),
          mysql_real_escape_string($host));
        mysql_query($insertSql);
        //インストラクションへ飛ばす（もしPROXY経由のユーザーであれば、インストラクションページ上のphpではじかれる）
        header('Location: ../Instruction.php');
        exit;
      }*/

      // For debug!
      $insertSql = sprintf('INSERT INTO %s SET sessionNo=%d, dateTime=NOW(), workerID="%s", IP_addr="%s", host="%s"',
        mysql_real_escape_string($tableName),
        mysql_real_escape_string($sessionNo),
        mysql_real_escape_string($viewUserId),
        mysql_real_escape_string($addr),
        mysql_real_escape_string($host));
      mysql_query($insertSql);
      //インストラクションへ飛ばす（もしPROXY経由のユーザーであれば、インストラクションページ上のphpではじかれる）
      header('Location: ../Instruction.php');
      exit;

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
  <script type="text/javascript">
    // ban on back-button
    history.pushState(null, null, null);

    window.addEventListener("popstate", function() {
        history.pushState(null, null, null);
    });
  </script>
</head>

<body>
<div id="wrap">
  <h1>
    Consent Form<br />
  </h1>
  <div id="informationSheet">
    <div class="inner">
      <p id='consentform'>
        This research is being conducted by Wataru Toyokawa, a postdoctoral researcher at the University of St Andrews under the supervision of Prof. Kevin Laland. The Laland lab investigates human and animal decision-making. The study typically takes 15 minutes.
      </p>
      <p>&nbsp;</p>
      <p id='consentform'>
        If you agree to take part in the research, you will particpate in a series of decision making tasks, and then will answer a survey about your opinions and background. After completing the study you will receive a code to enter in the box on our HIT page. You must enter in this code exactly as it is given to you to receive payment. All of the information that we obtain during the research will be kept confidential, and not associated with your name in any way. All information from this experiment will be held for at least 7 years.
      </p>
      <p>&nbsp;</p>
      <p id='consentform'>
        Your participation in this research is voluntary. You are free to refuse to take part, and you may stop taking part at any time. You are free to discontinue participation in this study at any time with no penalty.  If there is any question in the questionnaire that makes you uncomfortable or that you do not want to answer, it is your right to refrain from answering that question.
      </p>
	  <p>&nbsp;</p>
       <p id='consentform'>
        If you agree to take part in the research, please play this experimental task alone without the help of anyone else. In addition, please do not share the details of this task with anyone else. Both of these things are important for insuring the scientific rigor of the study. Thank you for your cooperation.
      </p>
    </div>
  </div>

  <div id="login">
    <form id="loginForm" name="loginForm" action="<?php print($_SERVER['PHP_SELF']) ?>" method="POST">
            <p>
              <label><input type="radio" id="agreement" name="agreement" value="agreed" required> Consent</label>
            </p>
            <br />
            <p>
              <label><input type="radio" id="agreement" name="agreement" value="disagreed" required> Do not consent</label>
            </p>
          <!--
            <br />
            <p>
              Enter Your Worker ID<br />(Copy and paste the ID shown in the HIT page):
            </p>
          -->
          <!--
            <p>
              <input type="hidden" id="workerID" name="workerID" value="<?php
              //if(isset($_GET['amazonID'])){ echo $_GET['amazonID']; }
              ?>">
          -->
            </p>
            <br />
            <br />
            <p>
              Enter text shown below:
            </p>
            <p>
              <img src="index.php?<?php echo session_name()?>=<?php echo session_id()?>">
            </p>
            <p>
              <input type="text" id="keystring" name="keystring" value="" required>
            </p>
            <br />
            <br />
            <!--<input type="hidden" id="postTicket" name="postTicket" value="<?php //echo $ticket ?>"/>-->
            <p><input type="submit" id="proceed" name="proceed" value="Go to instructions"><div id="errorMessage"><?php echo $errorMessage ?></div></p>
    </form>
  </div>

</div>

</body>
</html>
