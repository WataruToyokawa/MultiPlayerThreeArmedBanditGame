<?php
  require("../ipc_instruction.php");
  session_start();

  // エラーメッセージ
  $errorMessage = "";

  //セッションNO.(新規実験セッションの度に書換える)
  $sessionNo = 8888; //(8888:test, )

  // database's and table's name
  $dbName = 'workerid';
  //$tableName = 'amazonworkerid_exp'; //amazonworkerid_exp かも
  $tableName = 'amazonworkerid';

  //タスク番号
  $_SESSION['taskVer']= 0;//(0: test, | GC3 -> 1:gsp, 2:gps, 3:spg, 4:sgp, 5:pgs, 6:psg)

  //１セッションの最大参加人数
  $groupSize = 1500;

  // 画面に表示するため特殊文字をエスケープする
  $viewUserId = 'null';

  if(isset($_GET['amazonID'])){
    $_SESSION['workerID'] = htmlspecialchars($_GET["amazonID"], ENT_QUOTES);
    $_SESSION['USERID'] = htmlspecialchars($_GET["amazonID"], ENT_QUOTES);
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
  /*mysql_connect('localhost', 'wataru', 'nanao56T') or die(mysql_error());
  mysql_select_db($dbName) or die(mysql_error());
  mysql_query('SET NAMES UTF8');*/

  //航 mac's Localhost上の MAMP でテストの場合はこっち！
  mysql_connect('localhost', 'root', 'root') or die(mysql_error());
  mysql_select_db($dbName) or die(mysql_error());
  mysql_query('SET NAMES UTF8');

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
    if($_POST["agreement"] == "disagreed" || $_POST["consent1"] == "no" || $_POST["consent2"] == "no" || $_POST["consent3"] == "no" || $_POST["consent4"] == "no" || $_POST["consent5"] == "no")
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
      if (count($isID) > 0) {
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
      }

      // For debug!
      /*$insertSql = sprintf('INSERT INTO %s SET sessionNo=%d, dateTime=NOW(), workerID="%s", IP_addr="%s", host="%s"',
        mysql_real_escape_string($tableName),
        mysql_real_escape_string($sessionNo),
        mysql_real_escape_string($viewUserId),
        mysql_real_escape_string($addr),
        mysql_real_escape_string($host));
      mysql_query($insertSql);
      //インストラクションへ飛ばす（もしPROXY経由のユーザーであれば、インストラクションページ上のphpではじかれる）
      header('Location: ../Instruction.php');
      exit;*/

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
<img class='logo' src="../img/print-crest.png"/>
  <h1>
    Participant Consent Form<br>
    <span><br>Anonymous Data</span>
  </h1>
  <div class='researcherNames'>
    <p>
      <span>Researchers Names:</span><br><br>
      Wataru Toyokawa<br>
      Andrew Whalen<br>
    </p>
    <p>
      <span>Supervisor Name:</span><br><br>
      Kevin Laland<br>(knl1@st-andrews.ac.uk)
    </p>
  </div>
  <div id="informationSheet">
    <div class="inner">
      <p id='consentform'>
        The University of St Andrews attaches high priority to the ethical conduct of research. We therefore ask you to consider the following points before signing this form.
      </p>
      <p>&nbsp;</p>
      <h2>What is Anonymous Data?</h2>
      <p id='consentform'>
        The term ‘Anonymous Data’ refers to data collected by a researcher that has no identifier markers so that even the researcher cannot identify any participant. Consent is still required by the researcher, however no link between the participant’s consent and the data collected can be made.
      </p>
      <p>&nbsp;</p>
      <h2>Consent</h2>
      <p id='consentform'>
        The purpose of this form is to ensure that you are willing to take part in this study and to let you understand what it entails.
      </p>
      <p>&nbsp;</p>
      <p id='consentform'>
        If you agree to take part in the research, please play this experimental task alone without the help of anyone else. In addition, please do not share the details of this task with anyone else. Both of these things are important for insuring the scientific rigor of the study.
      </p>
	    <p>&nbsp;</p>
      <p id='consentform'>
        Material gathered during this research will be anonymous, so it is impossible to trace back to you. It will be securely stored on university servers for up to 3 years. Please answer each statement concerning the collection and use of the research data.
      </p>
    </div>
  </div>


  <div id="login">
    <form id="loginForm" name="loginForm" action="<?php print($_SERVER['PHP_SELF']) ?>" method="POST">
    <p><div id="errorMessage"><?php echo $errorMessage ?></div></p>
    <table class='consentTable'>
      <tr>
        <td>
          <p>
            I have read and understood the information sheet.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="consent1" name="consent1" value="yes" required> YES</label>
            <label><input type="radio" id="consent1" name="consent1" value="no" required>  N O</label>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            I understand that I can withdraw from the study without having to give an explanation.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="consent2" name="consent2" value="yes" required> YES</label>
            <label><input type="radio" id="consent2" name="consent2" value="no" required>  N O</label>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            I understand that my data once processed will be anonymous and that only the researchers and supervisor will have access to the raw data which will be kept confidentially.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="consent3" name="consent3" value="yes" required> YES</label>
            <label><input type="radio" id="consent3" name="consent3" value="no" required>  N O</label>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            I understand that my data will be stored for a period of 3 years before being destroyed.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="consent4" name="consent4" value="yes" required> YES</label>
            <label><input type="radio" id="consent4" name="consent4" value="no" required>  N O</label>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            I have been made fully aware of the potential risks associated with this research and am satisfied with the information provided.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="consent5" name="consent5" value="yes" required> YES</label>
            <label><input type="radio" id="consent5" name="consent5" value="no" required>  N O</label>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            I agree to take part in the study.
          </p>
        </td>
        <td id='answer'>
          <p id='login_answer'>
            <label><input type="radio" id="agreement" name="agreement" value="agreed" required> YES</label>
            <label><input type="radio" id="agreement" name="agreement" value="disagreed" required>  N O</label>
          </p>
        </td>
      </tr>
    </table>


    <p>&nbsp;</p>


            <br />
            <br />
            <p id='capture'>
              Enter text shown below:
            </p>
            <p id='capture'>
              <img src="index.php?<?php echo session_name()?>=<?php echo session_id()?>">
            </p>
            <p id='capture'>
              <input type="text" id="keystring" name="keystring" value="" required>
            </p>
            <br />
            <br />
            <!--<input type="hidden" id="postTicket" name="postTicket" value="<?php //echo $ticket ?>"/>-->
            <p id='capture'><input type="submit" id="proceed" name="proceed" value="Go to instructions"></p>
    </form>
  </div>

</div>

</body>
</html>
