<?php
/////////////////////////////////////////////
//  PHP名：IP-Checker    Ver：2.9          //
//  製作者：天星                           //
//  メール：softs@tenskystar.net           //
//  再配布：無許可(許可必要)               //
//  作成日:07/02/18                        //
//  URL:http://www.tenskystar.net/         //
// 連動するPHPの2行目にrequire("ipc.php"); //
// を追加して利用して下さい。              //
/////////////////////////////////////////////

//タイトル
$title="Security Gate";

//パスブロック 0=OFF 1=常にON 2=規制対象者のみON
$ps_block = 0;

//認証パス　複数設定可能
$bps=array("1234","5678",);

//アクセス禁止日時 (ON=1,OFF=0)
$noacsts = 1;

//アクセス禁止日時
// "日付 か 曜日/[アクセス禁止開始時間]-[アクセス禁止終了時間]";のように設定。
//日付か曜日の部分をAllにすると毎日の設定になります。設定例:"All/15-16";
//また、""内で,で区切る事により複数設定できます。設定例:"日/15-16,月/16-17,水/17-18";
// デフォルトは月曜日の15:00から16:00
$noacs = "All/1-24";

//リファラが代入されて無い場合にアクセスを規制する(ON=1,OFF=0)
$ref = 0;

//以下のURL以外からのアクセスを拒否
// 設定しない場合は空欄。複数設定可
//http://不要。正規表現で指定。 ''で囲み、,で区切って下さい。
$noacsr = array('',);

//----- IP アクセス規制 -----//

//IP アクセス規制方式 0=指定したIPからのアクセスのみを許可 1=指定したIPからのアクセスを拒否
$xipcf = 1;

// アクセス規制IP設定 IPアクセス規制で禁止or許可するIP,HOSTを正規表現で指定してください。
//  いくつでも追加可。host名一部でも可能。''で囲んでください。
//一つ一つ区切ってください区切る場合は,で区切ってください。
$deny = array(
'cache*.*.interlog.com',
'anonymizer',
);

//---- PROXY 規制 ----//

//PROXY規制をする1=ON 0=OFF
$refuseproxy = 1;

//PROXY制限使用時にPROXYを使用していないのにかかってしまった場合に
//通過を可能にするIPを設定。''で囲み,で絶対区切ってください。　正規表現で指定してください。
$kips=array('',);

//---- Tor 規制 ----//

//Torをチェックする(0=OFF , 1=ON);
$tor_sw = 0;

//Tor IP List
$tor_ip_list[0] = "http://torstatus.blutmagie.de/ip_list_all.php/Tor_ip_list_ALL.csv";//取得先
$tor_save_name[0] = "./tor_data.dat";//保存場所

$up_time_set = 60;//Tor IP List  再取得-時間 (分) 60 => 1時間毎


//---- クエリチェック ----//

$quck = 0;//クエリ内の文字をチェックする(1=ON 0=OFF)

//クエリ内の禁止文字。''で囲み、,で区切ってください。 
//クエリ全体をチェックするので、処理用の文字までも設定しないようにして下さい。
$word = array('馬鹿','バカ','baka','ばか');


//投稿 規制用
//指定 クエリ送信 規制
$ck_reg = 2;//0=OFF , 1=指定時間経過しないと , 2=時間と回数
$ck_tlim = 2;//投稿間隔(分)
$ck_cnt = 20; //最大回数(1日)

//投稿モード 判別用
$ck_qname = "mode";//チェックするクエリのname値
$ck_qval = "writing";//チェックするクエリのvalu値

//- 以下 PHP知る者以外は触るべからず -//

//Tor Save File Exists Check
if($tor_sw){
	foreach ($tor_save_name as $tor_save_file){
		if(!file_exists($tor_save_file)){errorpage("設定エラー","データファイルがありません。","Setting");}
	}
}

 if($_GET){$ipc_queries=$_GET;}else{$ipc_queries=$_POST;}
 foreach($ipc_queries as $key => $val){if($key != "limsw"){$ipc_que = $ipc_que . $key ."=" .urldecode($val)."&"; } if(preg_match("/^(lgps|ipcmode)$/",$key)){$ipc_useq .= $key . "=" . urldecode($val)."&";}}

//mb_convert_encoding($ipc_que,"SJIS");

 if($quck){
 	foreach($word as $check){
		if(mb_ereg($check,$ipc_que)){errorpage('クエリエラー',"クエリに禁止文字が含まれています。","NoWord");}
 	}
 }

 mb_parse_str($ipc_useq);


//IP取得
	$host = getenv('REMOTE_HOST');
	$addr = getenv('REMOTE_ADDR');
		if(!$host or $host == $addr){$host = gethostbyaddr($addr);}
		if(!$addr or $host == $addr){$addr = gethostbyname($host);}
		if(!$host){$host = $addr;}
		if(!$addr){$addr = $host;}


//時間取得   # 日本時間
   $_ENV{'TZ'} = "JST-9";
   $times = time();
    list($sec,$min,$hour,$mday,$mon,$year,$wday)=localtime($times);
   $week = array('日','月','火','水','木','金','土');

   // 日時のフォーマット
   $date = sprintf("%04d/%02d/%02d(%s) %02d:%02d",
       $year+1900,$mon+1,$mday,$week[$wday],$hour,$min);
	   
//リファラ取得
   $referer = getenv('HTTP_REFERER');
//このPHP名
   $php=basename(__FILE__);


//クエリ回数チェック
	if($ck_reg){
		$qckc_t = $times + 24 * 60 * 60;
			if(!$_COOKIE[ckreg]){setcookie("ckreg",sha1($host),$qckc_t);setcookie("ckcnt",0,$qckc_t);}
			elseif($_COOKIE[ckreg] and $ipc_queries{$ck_qname} == $ck_qval){
				$ck_tflag = $times - $_COOKIE[cktime];
					if($ck_tflag < ($ck_tlim * 60)){errorpage('クエリエラー',"時間を空けてから送信してください。","NoReqTime");}
					if($ck_reg == 2){
						if($_COOKIE[ckcnt] <= $ck_cnt){$_COOKIE[ckcnt]++; setcookie("ckcnt",$_COOKIE[ckcnt],$qckc_t); setcookie("cktime",$times,$qckc_t);}
						else{errorpage('クエリエラー',"これ以上に処理を受け付けられません。","NoQuery");}
					}
			}
 
	}

//パスブロック
	if($ps_block){
		foreach($bps as $logps){
			if($logps and $_COOKIE[ipcps] == $logps and preg_match("/$_COOKIE[ipchost]/",$host)){$limsw=$_COOKIE[ipclim];}
		}
	}

	if($ps_block){
		$hspl = explode("\.",$host);
			for($a=1;$a<count($hspl);$a++){$ipchost=$ipchost."\.".$hspl[$a];}
			if(!$ipchost){$ipchost=$host;}

				if($ipcmode == "login"){
					foreach($bps as $logps){
						if($logps and $logps == $lgps){
							if(!$_COOKIE[ipchost] or preg_match("/$_COOKIE[ipchost]/",$host)){setcookie("ipcps",$lgps);setcookie("ipchost",$ipchost); setcookie("ipclim","1"); $limsw=1;}
						}
					}

				}
			if(!$limsw and $ps_block == 1){login(1);}
	}
  
	if(!$limsw or $ps_block != 2){
		if(!$script){$script = "http://" . getenv('SERVER_NAME') . getenv('SCRIPT_NAME');}
		if(!$host){errorpage('取得エラー','IPが取得出来ませんでした。',"NoIP");}
		if($ref and !$referer and $ipcmode != "ok"){errorpage('取得エラー','リファラーが取得出来ませんでした。',"NoReferer");}

//時間規制
		if($noacsts){
			$times = time;
			$noacset = explode(",",$noacs);//複数分解
			
			foreach ($noacset as $_){
				list($noday,$noacstime) = explode("/",$_);//曜日/時間分解
					if(!$noday or !$noacstime){errorpage('設定エラー',"アクセス禁止日時の設定が異常です。","NoSet");}
					
				list($start,$end) = explode("-",$noacstime);//時間分解
					if(!$start or !$end){errorpage('設定エラー',"アクセス禁止時刻の設定が異常です","NoSet");}
	
				if($noday == $mday or $noday == $week[$wday] or $noday == "All"){//日・曜日
					if ( $start < $end){if ($hour >= $start && $hour < $end){errorpage('アクセス禁止',	"本日" . $start . ":00から" . $end . ":00までは<BR>アクセス禁止となっております。\n","Time");}}
					elseif ($start > $end){if ($hour >= $start || $hour < $end){errorpage('アクセス禁止',"本日" . $start . ":00から翌日" . $end . ":00までは<BR>アクセス禁止になっております。\n","Time");}}
				}
			}
		}

//リファラ規制

	foreach ($noacsr as $one){
		if($one){
//パターンマッチ検索（正規表現)
			$cnt = preg_match("/$one/",$referer);
			if(1 > $cnt && $referer != $noacsr && $referer != $script && !$eflag){$noarf=1;}
			elseif(1 <= $cnt){$noarf=0;$eflag=1;}
		}
	}
	if($noarf){errorpage('アクセス禁止',"管理者が指定したアドレス以外からのアクセスを禁止しています。\n","Referer");}

//Tor - PROXY制限
	if($tor_sw){
		//現在の時刻(Sec)
		$now_time = intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s"));
		//今日の日付(timestamp)
		$to_date = time();
		//最終更新日(timestamp)
		$last_up_date = filemtime($tor_save_name[0]);
		if(filesize($tor_save_name[0]) == 0){$last_up_date = 0;}//if DataFile Size = 0
		
		//更新予定 時刻(sec)
		$up_time = $up_time_set * 60;
		//更新予定日(timestamp
		$next_up_date = $last_up_date + $up_time;
		
		if($next_up_date and $next_up_date <= $to_date and $last_up_date <= $next_up_date){
		//更新時間を超えている
			$fno=0;
			foreach ($tor_ip_list as $tor_get_file){
				$file_sorce = file_get_contents($tor_get_file);
				$match_cnt = preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\n/",$file_sorce,$tor_match);
				if($match_cnt > 0){
					$fs = fopen($tor_save_name[$fno],"w+");
					$wflag = fwrite($fs,$file_sorce);//Write
					fclose($fs);
				}
				
				$fno++;
			}
		}
		
		//Check
		$tor_flag = 0;
		foreach ($tor_save_name as $tor_file){
			if(!$tor_flag){
				$tor_data = file_get_contents($tor_file);
				
				if($tor_data){
					$addr_pt = preg_replace("/\./","\.",$addr);
					preg_match_all("/(".$addr_pt.")\n/",$tor_data,$tor_match);
					$tor_flag = count($tor_match[1]);
					if($tor_flag > 0){$tor_flag = 1;}	
					else{$tor_flag = 0;}
				}
			}else{continue;}
		}
	
		if($tor_flag){errorpage("アクセス禁止",'Tor-PROXY 経由でのアクセスは禁止されています。',"Tor");}
	}

// Proxy
	if ($refuseproxy){
		if (getenv('HTTP_X_FORWARDED_FOR')){$proxyflag = 1;}
		if (getenv('HTTP_CLIENT_IP')){$proxyflag = 1;}
		if (getenv('HTTP_SP_HOST')){$proxyflag = 1;}
		if (getenv('HTTP_VIA')){$proxyflag = 1;}
		if (getenv('HTTP_CACHE_INFO')){$proxyflag = 1;}
		if (preg_match("/(prox|squid|cache|gate|firewall|webwarper|torproject|tor-exit|anonymizer)/",$host)){$proxyflag = 1;}
		if (preg_match("/(via|proxy|gate)/",getenv('HTTP_USER_AGENT'))){$proxyflag = 1;}
		if (preg_match("/no-cache/",getenv('HTTP_PRAGMA'))){$proxyflag = 1;}
		if(!getenv('HTTP_ACCEPT_ENCODING')){$proxyflag = 1;}
		if (preg_match("/close/",getenv('HTTP_CONNECTION'))){$proxyflag = 1;}

//強制許可
		foreach ($kips as $_) {
//パターンマッチ検索（正規表現)
			$hcnt=preg_match("/$_/",$host);
			$acnt=preg_match("/$_/",$addr);
			
			if($_){if ($host == $_  or $addr == $_ or $hcnt > 0 or $acnt > 0){$proxyflag = 0;}}
		}
	
		if ($proxyflag){errorpage("アクセス禁止",'PROXY経由でのアクセスは禁止されています。',"Proxy");}
	}

	if($ps_block == 2 and !$_COOKIE[ipchost]){
		if(!preg_match("/$_COOKIE[ipchost]/",$host)){errorpage("アクセス禁止","不正なアクセスです。","NoMatch");}
	}

//強制制限
	$cflag=1;
	foreach ($deny as $_) {
//パターンマッチ検索（正規表現)
		$hcnt = preg_match("/$_/",$host);
		$acnt = preg_match("/$_/",$addr);
		
		if($xipcf){if($_){if($host == $_ or $addr == $_ or $acnt > 0 or $hcnt > 0){errorpage('アクセス禁止',"ホストは管理者によってアクセスを禁止しております。","Host");}}}
		elseif(!$xipcf){if($_){if($host == $_ or $addr == $_ or $acnt > 0 or $hcnt > 0){$cflag=0;}}}
		else{errorpage("設定エラー","アクセス禁止方式の設定値が異常です。","Set");}	
	}
 	if($cflag and !$xipcf){errorpage("アクセス禁止","ホストは管理者によってアクセスを許可しておりません。","Host");}


}

	function login($hsw){
 		if($hsw){
  			global $title;
			print "<html><head><title>$title</title></head>\n";
 		}
	
		print "<form method=\"post\" action=\"?\">\n";
		print "<center>\n";
		print "<p style=\"font-weight:bold;\">$title</p>\n";
		print "<div>認証してください。</div>\n";
		print "<div><input type=\"password\" name=\"lgps\" style=\"width:80px;\"></div>\n";
		print "<div><input type=\"submit\" value=\"認証\">\n";
		print "<input type=\"hidden\" name=\"ipcmode\" value=\"login\">\n";
//  if(!$hsw){print "<input type=\"hidden\" name=\"limsw\" value=\"1\">\n";}

		if($hsw){
			ipcfoot(1);
			print "</center></form></html>\n";
		exit;
		}
	}

//ERROR-Page
	function errorpage($ername,$ermes,$epc){
		global $host,$addr,$title,$ps_block;
			setcookie("ipclim","0");
		print "<html><head><title>$title</title></head>";
		print "<center><B>$title</B><BR><HR width=\"50%\"><BR>\n";
		print "<table border=\"0\">\n";
		print "<TR><TD align=\"center\"> - - Your Infomation Check - - </TD></TR>\n";
		print "<TR><TD>Start ...<BR>";
		print "IP：$host ( $addr ) ... ";if($epc != "NoIP"){print "OK";}else{print "OUT";}print "<BR>";
		print "各種情報 ... ";if($epc != "NoReferer"){print "OK";}else{print "OUT";}print "<BR>";
		print "各種規制 ... ";if($epc !="NoIP" or $epc !="NoReferer"){print "OUT";}else{print "OK";}print "<BR>";
		print "結果：<font color=\"red\"><B>$ername</B></font><BR>";
		print "End ...</TD></TR>";
		print "<TR><TD align=\"center\"> - - - - - - - - - - - - - - - - </TD></TR>\n";
		print "</table><BR>\n";
		print "<font color=\"red\"><B>$ermes</B></font>";
		print "<BR><BR><HR width=\"50%\">";
		
		if($ps_block == 2){login(0);}
			ipcfoot(1);
		print "</center></html>";
	exit;
	}

ipcfoot(0);

//下記を勝手に削除してはなりません。勝手なる削除者は、有料での使用とさせて戴きます。
	function ipcfoot($n){
		global $limsw,$_COOKIE,$lgps,$host,$ipchost;

		if($n){
			print "<p align=\"center\">IP-Checker for PHP Ver2.9 - <B><a href=\"http://www.tenskystar.net\">天空の彼方</a></B></p>";
		}
	}
//Ver.2.9 12/12/20 Tor-Checkを追加。
//Ver.2.8 12/05/19 セキュリティ上の調整
//Ver:2.7 12/03/23 指定クエリをチェックして投稿-回数,間隔の規制を行えるように。
//Ver:2.6 11/06/30 規制対象者のみのパスワード認証による通過も出来るように。
//Ver:2.5 11/01/11 パスブロックを行えるように。（IPCと連動させるだけで、パスワード制限をつけられる。)
//Ver:2.4 10/11/01 アクセス拒否URLを複数設定可能に
//Ver:2.3 10/10/17 指定IPアクセス禁止を指定IPのみアクセス許可方式にもできる様に。
//Ver:2.2 10/04/05 連携物がCookie利用する場合にエラーが発生するのを修正、クエリ内に含まれる文字チェックを追加。
//Ver:2.1 09/10/09 強制許可、強制制限をIP,HOSTどちらでも指定可能に修正。
//Ver:2.0 09/03/05 他のPHPと連動して規制を行う方式に変更。
//Ver:1.5 08/09/16 チェック通過後のページ表示を変更?
//Ver:1.4 08/03/31 日時によるアクセス禁止の規制方法を変更。各日・曜日の時間を設定し規制を行う様に。
//Ver:1.3 08/03/30 提案によりアクセス禁止する日・曜日の設定を追加、リファラー情報が無い場合に規制。
//Ver:1.2 08/02/03 強制許可が('',)となっていると全員許可されてしまうのを修正。
//Ver:1.1 07/02/18  パターンマッチング方式判明の為、IPC-CGIを基本として時間、指定URL以外からのアクセスなどの規制、通過可能化などを追加。
//Ver:1.0 07/02/18? IPC-CGIを基本として製作。主にPROXY規制部分のみ一応完成。

?>
