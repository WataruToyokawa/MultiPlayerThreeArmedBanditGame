<?php
  session_start();

  error_reporting(E_ALL & ~E_NOTICE);//$history3以降がNullだからNoticeが出る。それを表示させない。

  // ログイン状態のチェック
  if (!isset($_POST['amazonID'])) {
    header("Location: ./doui/ConsentForm.php");
    exit;
  }

  //CSVファイル名の設定
  $csvdata = "data.csv";
  //CSVデータの初期化
  $csv = "";

  $amazonID = htmlspecialchars($_POST['amazonID'], ENT_QUOTES);
  $confirmationID = htmlspecialchars($_POST['confirmationID'], ENT_QUOTES);
  $waitingBonus = htmlspecialchars($_POST['waitingBonus'], ENT_QUOTES);
  $totalGamePayoff = round(htmlspecialchars($_POST['totalGamePayoff'], ENT_QUOTES), 2);
  $q1 = htmlspecialchars($_POST['q1'], ENT_QUOTES);
  $q2 = htmlspecialchars($_POST['q2'], ENT_QUOTES);
  $q3 = htmlspecialchars($_POST['q3'], ENT_QUOTES);
  $q4 = htmlspecialchars($_POST['q4'], ENT_QUOTES);
  $q5 = htmlspecialchars($_POST['q5'], ENT_QUOTES);
  $q6 = htmlspecialchars($_POST['q6'], ENT_QUOTES);
  $age = htmlspecialchars($_POST['age'], ENT_QUOTES);
  $sex = htmlspecialchars($_POST['sex'], ENT_QUOTES);
  $country = htmlspecialchars($_POST['country'], ENT_QUOTES);
  $totalPay = round($waitingBonus+ $totalGamePayoff + 0.25, 1);
  $userAgent = $_SERVER["HTTP_USER_AGENT"];


  //CSVデータの作成
  $csv .= $amazonID. "," . $confirmationID.",".$waitingBonus.",".$totalGamePayoff.",".$totalPay.",";
  $csv .= $q1.",".$q2.",".$q3.",".$q4.",".$q5.",".$q6.",".$age.",".$sex.",".$country.",".$userAgent.PHP_EOL;

  //CSVファイルを追記モードで開く
  $fp = fopen($csvdata, 'ab');

  //CSVファイルを排他的ロックする（他の読み込み/書き込みの両方をブロック）
  flock($fp, LOCK_EX);

  //CSVデータ($csv)の内容をファイルに書き込む
  fwrite($fp, $csv);

  //CSVファイルを閉じる
  fclose($fp);


  ////セッションを完全に破棄する
  // セッション変数を全て解除する
  $_SESSION = array();
  // セッションを切断するにはセッションクッキーも削除する。
  // Note: セッション情報だけでなくセッションを破壊する。
  if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time()-42000, '/');
  }
  // 最終的に、セッションを破壊する
  session_destroy();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link href="import.css" rel="stylesheet" type="text/css" media="all" />
  <link href="confirmation.css" rel="stylesheet" type="text/css" media="all" />
  <title>Confirmation Code</title>
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
  <h1>Result & Confirmation</h1>

  <fieldset>
    <legend>Your Total Earnings</legend>
    <div id="earnings">
      <div>
        Base payment: $0.25<br>
        Waiting Bonus: $<?php echo $waitingBonus ?><br>
        Game Reward: $<?php echo $totalGamePayoff ?><br>
      </div>
      <p> Total Earnings: $<?php echo(round($waitingBonus+ $totalGamePayoff + 0.25, 1))?></p>
    </div>
  </fieldset>

  <fieldset>
    <legend>Confirmation Code</legend>
    <div id="confirmation">
      <p><?php echo $confirmationID ?></p>
    </div>
  </fieldset>

  <p class="lead">* <span>Copy and paste this confirmation code</span> into the HIT on Amazon's Mechanical Turk to receive payment. </p>

  <fieldset>
    <legend>
      Participant Debriefing Form
    </legend>
    <div id="debriefing">
      <div class="inner">
      <img class='logo' src="./img/print-crest.png"/>
      <h2>Nature of Project</h2>
        <p class="lead" id='innerText'>
          This postgraduate research project was conducted to investigate how people integrate information from both social cues and individual experiences (such as rewards drawn from the lotteries) in decision making. In particular, this experiment explores how the use of information changes in different group sizes. In other words, how do people in large groups handle the social cues compared with those in a small groups?
        </p>
        <p class="lead" id='innerText'>
          We are also interested in how the behavioural dynamics at the group-level is affected by the use of social cues at the individual-level.
        </p>
        <p class="lead" id='innerText'>
          Thank you for your participation!
        </p>
        <p class="lead" id='innerText'>
          If you are interested in learning more about this topic, below is a list of related scientific papers you might find interesting:
        </p>
        <table class="reference">
          <ul>
            <li>
              Bond, R. (2005). Group size and conformity. <span>Group Processes & Intergroup Relations</span>, 8 (4): 331-354.
            </li>
            <li>
              Kandler, A. & Laland, K. N. (2013). Tradeoffs between the strength of conformity and number of conformists in variable environments. <span>J. Theoretical Biolology</span>, 332: 191-202.
            </li>
            <li>
              Morgan, T. J. H., Rendell, L. E., Ehn, M., Hoppitt, W., & Laland, K. N. (2012). The evolution basis of human social learning. <span>Proc. Roy. Soc. B.</span> 279: 653-662.
            </li>
          </ul>
        </table>
      <h2>Storage of Data</h2>
      <p class='lead' id='innerText'>
        As outlined in the Participant Information Sheet your data will now be retained for a period of 3 years before being destroyed. Your data will remain accessible to only the researchers and supervisors. OR Your data may be used for future scholarly purposes without further contact or permission if you have given permission on the Consent Form.   If you no longer wish for your data to be used in this manner you are free to withdraw your consent by contacting any of the researchers and or Supervisor.
      </p>
      <h2>What should I do if I have concerns about this study?</h2>
      <p class='lead' id='innerText'>
        A full outline of the procedures governed by the University Teaching and Research Ethical Committee are outline on <a target='blank_' href='http://www.st-andrews.ac.uk/utrec/guidelinespolicies/complaints/'>their website</a>
      </p>
      <h2>Contact Details</h2>
      <div class='researcherNames'>
        <p>
          <span>Researchers:</span><br><br>
          Wataru Toyokawa<br>(wt25@st-andrews.ac.uk)<br>
          Andrew Whalen<br>(aczw@st-andrews.ac.uk)<br>
        </p>
        <p>
          <span>Supervisor:</span><br><br>
          Kevin Laland<br>
          Email: knl1@st-andrews.ac.uk<br>
          Phone: +44 01334 463568
        </p>
      </div>
      </div>
    </div>
  </fieldset>

</div>

</body>
</html>
