<?php
/////////////////////////////////////////////
//  PHP���FIP-Checker    Ver�F2.9          //
//  ����ҁF�V��                           //
//  ���[���Fsofts@tenskystar.net           //
//  �Ĕz�z�F������(���K�v)               //
//  �쐬��:07/02/18                        //
//  URL:http://www.tenskystar.net/         //
// �A������PHP��2�s�ڂ�require("ipc.php"); //
// ��ǉ����ė��p���ĉ������B              //
/////////////////////////////////////////////

//�^�C�g��
$title="Security Gate";

//�p�X�u���b�N 0=OFF 1=���ON 2=�K���Ώێ҂̂�ON
$ps_block = 0;

//�F�؃p�X�@�����ݒ�\
$bps=array("1234","5678",);

//�A�N�Z�X�֎~���� (ON=1,OFF=0)
$noacsts = 1;

//�A�N�Z�X�֎~����
// "���t �� �j��/[�A�N�Z�X�֎~�J�n����]-[�A�N�Z�X�֎~�I������]";�̂悤�ɐݒ�B
//���t���j���̕�����All�ɂ���Ɩ����̐ݒ�ɂȂ�܂��B�ݒ��:"All/15-16";
//�܂��A""����,�ŋ�؂鎖�ɂ�蕡���ݒ�ł��܂��B�ݒ��:"��/15-16,��/16-17,��/17-18";
// �f�t�H���g�͌��j����15:00����16:00
$noacs = "All/1-24";

//���t�@�����������Ė����ꍇ�ɃA�N�Z�X���K������(ON=1,OFF=0)
$ref = 0;

//�ȉ���URL�ȊO����̃A�N�Z�X������
// �ݒ肵�Ȃ��ꍇ�͋󗓁B�����ݒ��
//http://�s�v�B���K�\���Ŏw��B ''�ň͂݁A,�ŋ�؂��ĉ������B
$noacsr = array('',);

//----- IP �A�N�Z�X�K�� -----//

//IP �A�N�Z�X�K������ 0=�w�肵��IP����̃A�N�Z�X�݂̂����� 1=�w�肵��IP����̃A�N�Z�X������
$xipcf = 1;

// �A�N�Z�X�K��IP�ݒ� IP�A�N�Z�X�K���ŋ֎~or������IP,HOST�𐳋K�\���Ŏw�肵�Ă��������B
//  �����ł��ǉ��Bhost���ꕔ�ł��\�B''�ň͂�ł��������B
//����؂��Ă���������؂�ꍇ��,�ŋ�؂��Ă��������B
$deny = array(
'cache*.*.interlog.com',
'anonymizer',
);

//---- PROXY �K�� ----//

//PROXY�K��������1=ON 0=OFF
$refuseproxy = 1;

//PROXY�����g�p����PROXY���g�p���Ă��Ȃ��̂ɂ������Ă��܂����ꍇ��
//�ʉ߂��\�ɂ���IP��ݒ�B''�ň͂�,�Ő�΋�؂��Ă��������B�@���K�\���Ŏw�肵�Ă��������B
$kips=array('',);

//---- Tor �K�� ----//

//Tor���`�F�b�N����(0=OFF , 1=ON);
$tor_sw = 0;

//Tor IP List
$tor_ip_list[0] = "http://torstatus.blutmagie.de/ip_list_all.php/Tor_ip_list_ALL.csv";//�擾��
$tor_save_name[0] = "./tor_data.dat";//�ۑ��ꏊ

$up_time_set = 60;//Tor IP List  �Ď擾-���� (��) 60 => 1���Ԗ�


//---- �N�G���`�F�b�N ----//

$quck = 0;//�N�G�����̕������`�F�b�N����(1=ON 0=OFF)

//�N�G�����̋֎~�����B''�ň͂݁A,�ŋ�؂��Ă��������B 
//�N�G���S�̂��`�F�b�N����̂ŁA�����p�̕����܂ł��ݒ肵�Ȃ��悤�ɂ��ĉ������B
$word = array('�n��','�o�J','baka','�΂�');


//���e �K���p
//�w�� �N�G�����M �K��
$ck_reg = 2;//0=OFF , 1=�w�莞�Ԍo�߂��Ȃ��� , 2=���ԂƉ�
$ck_tlim = 2;//���e�Ԋu(��)
$ck_cnt = 20; //�ő��(1��)

//���e���[�h ���ʗp
$ck_qname = "mode";//�`�F�b�N����N�G����name�l
$ck_qval = "writing";//�`�F�b�N����N�G����valu�l

//- �ȉ� PHP�m��҈ȊO�͐G��ׂ��炸 -//

//Tor Save File Exists Check
if($tor_sw){
	foreach ($tor_save_name as $tor_save_file){
		if(!file_exists($tor_save_file)){errorpage("�ݒ�G���[","�f�[�^�t�@�C��������܂���B","Setting");}
	}
}

 if($_GET){$ipc_queries=$_GET;}else{$ipc_queries=$_POST;}
 foreach($ipc_queries as $key => $val){if($key != "limsw"){$ipc_que = $ipc_que . $key ."=" .urldecode($val)."&"; } if(preg_match("/^(lgps|ipcmode)$/",$key)){$ipc_useq .= $key . "=" . urldecode($val)."&";}}

//mb_convert_encoding($ipc_que,"SJIS");

 if($quck){
 	foreach($word as $check){
		if(mb_ereg($check,$ipc_que)){errorpage('�N�G���G���[',"�N�G���ɋ֎~�������܂܂�Ă��܂��B","NoWord");}
 	}
 }

 mb_parse_str($ipc_useq);


//IP�擾
	$host = getenv('REMOTE_HOST');
	$addr = getenv('REMOTE_ADDR');
		if(!$host or $host == $addr){$host = gethostbyaddr($addr);}
		if(!$addr or $host == $addr){$addr = gethostbyname($host);}
		if(!$host){$host = $addr;}
		if(!$addr){$addr = $host;}


//���Ԏ擾   # ���{����
   $_ENV{'TZ'} = "JST-9";
   $times = time();
    list($sec,$min,$hour,$mday,$mon,$year,$wday)=localtime($times);
   $week = array('��','��','��','��','��','��','�y');

   // �����̃t�H�[�}�b�g
   $date = sprintf("%04d/%02d/%02d(%s) %02d:%02d",
       $year+1900,$mon+1,$mday,$week[$wday],$hour,$min);
	   
//���t�@���擾
   $referer = getenv('HTTP_REFERER');
//����PHP��
   $php=basename(__FILE__);


//�N�G���񐔃`�F�b�N
	if($ck_reg){
		$qckc_t = $times + 24 * 60 * 60;
			if(!$_COOKIE[ckreg]){setcookie("ckreg",sha1($host),$qckc_t);setcookie("ckcnt",0,$qckc_t);}
			elseif($_COOKIE[ckreg] and $ipc_queries{$ck_qname} == $ck_qval){
				$ck_tflag = $times - $_COOKIE[cktime];
					if($ck_tflag < ($ck_tlim * 60)){errorpage('�N�G���G���[',"���Ԃ��󂯂Ă��瑗�M���Ă��������B","NoReqTime");}
					if($ck_reg == 2){
						if($_COOKIE[ckcnt] <= $ck_cnt){$_COOKIE[ckcnt]++; setcookie("ckcnt",$_COOKIE[ckcnt],$qckc_t); setcookie("cktime",$times,$qckc_t);}
						else{errorpage('�N�G���G���[',"����ȏ�ɏ������󂯕t�����܂���B","NoQuery");}
					}
			}
 
	}

//�p�X�u���b�N
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
		if(!$host){errorpage('�擾�G���[','IP���擾�o���܂���ł����B',"NoIP");}
		if($ref and !$referer and $ipcmode != "ok"){errorpage('�擾�G���[','���t�@���[���擾�o���܂���ł����B',"NoReferer");}

//���ԋK��
		if($noacsts){
			$times = time;
			$noacset = explode(",",$noacs);//��������
			
			foreach ($noacset as $_){
				list($noday,$noacstime) = explode("/",$_);//�j��/���ԕ���
					if(!$noday or !$noacstime){errorpage('�ݒ�G���[',"�A�N�Z�X�֎~�����̐ݒ肪�ُ�ł��B","NoSet");}
					
				list($start,$end) = explode("-",$noacstime);//���ԕ���
					if(!$start or !$end){errorpage('�ݒ�G���[',"�A�N�Z�X�֎~�����̐ݒ肪�ُ�ł�","NoSet");}
	
				if($noday == $mday or $noday == $week[$wday] or $noday == "All"){//���E�j��
					if ( $start < $end){if ($hour >= $start && $hour < $end){errorpage('�A�N�Z�X�֎~',	"�{��" . $start . ":00����" . $end . ":00�܂ł�<BR>�A�N�Z�X�֎~�ƂȂ��Ă���܂��B\n","Time");}}
					elseif ($start > $end){if ($hour >= $start || $hour < $end){errorpage('�A�N�Z�X�֎~',"�{��" . $start . ":00���痂��" . $end . ":00�܂ł�<BR>�A�N�Z�X�֎~�ɂȂ��Ă���܂��B\n","Time");}}
				}
			}
		}

//���t�@���K��

	foreach ($noacsr as $one){
		if($one){
//�p�^�[���}�b�`�����i���K�\��)
			$cnt = preg_match("/$one/",$referer);
			if(1 > $cnt && $referer != $noacsr && $referer != $script && !$eflag){$noarf=1;}
			elseif(1 <= $cnt){$noarf=0;$eflag=1;}
		}
	}
	if($noarf){errorpage('�A�N�Z�X�֎~',"�Ǘ��҂��w�肵���A�h���X�ȊO����̃A�N�Z�X���֎~���Ă��܂��B\n","Referer");}

//Tor - PROXY����
	if($tor_sw){
		//���݂̎���(Sec)
		$now_time = intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s"));
		//�����̓��t(timestamp)
		$to_date = time();
		//�ŏI�X�V��(timestamp)
		$last_up_date = filemtime($tor_save_name[0]);
		if(filesize($tor_save_name[0]) == 0){$last_up_date = 0;}//if DataFile Size = 0
		
		//�X�V�\�� ����(sec)
		$up_time = $up_time_set * 60;
		//�X�V�\���(timestamp
		$next_up_date = $last_up_date + $up_time;
		
		if($next_up_date and $next_up_date <= $to_date and $last_up_date <= $next_up_date){
		//�X�V���Ԃ𒴂��Ă���
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
	
		if($tor_flag){errorpage("�A�N�Z�X�֎~",'Tor-PROXY �o�R�ł̃A�N�Z�X�͋֎~����Ă��܂��B',"Tor");}
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

//��������
		foreach ($kips as $_) {
//�p�^�[���}�b�`�����i���K�\��)
			$hcnt=preg_match("/$_/",$host);
			$acnt=preg_match("/$_/",$addr);
			
			if($_){if ($host == $_  or $addr == $_ or $hcnt > 0 or $acnt > 0){$proxyflag = 0;}}
		}
	
		if ($proxyflag){errorpage("�A�N�Z�X�֎~",'PROXY�o�R�ł̃A�N�Z�X�͋֎~����Ă��܂��B',"Proxy");}
	}

	if($ps_block == 2 and !$_COOKIE[ipchost]){
		if(!preg_match("/$_COOKIE[ipchost]/",$host)){errorpage("�A�N�Z�X�֎~","�s���ȃA�N�Z�X�ł��B","NoMatch");}
	}

//��������
	$cflag=1;
	foreach ($deny as $_) {
//�p�^�[���}�b�`�����i���K�\��)
		$hcnt = preg_match("/$_/",$host);
		$acnt = preg_match("/$_/",$addr);
		
		if($xipcf){if($_){if($host == $_ or $addr == $_ or $acnt > 0 or $hcnt > 0){errorpage('�A�N�Z�X�֎~',"�z�X�g�͊Ǘ��҂ɂ���ăA�N�Z�X���֎~���Ă���܂��B","Host");}}}
		elseif(!$xipcf){if($_){if($host == $_ or $addr == $_ or $acnt > 0 or $hcnt > 0){$cflag=0;}}}
		else{errorpage("�ݒ�G���[","�A�N�Z�X�֎~�����̐ݒ�l���ُ�ł��B","Set");}	
	}
 	if($cflag and !$xipcf){errorpage("�A�N�Z�X�֎~","�z�X�g�͊Ǘ��҂ɂ���ăA�N�Z�X�������Ă���܂���B","Host");}


}

	function login($hsw){
 		if($hsw){
  			global $title;
			print "<html><head><title>$title</title></head>\n";
 		}
	
		print "<form method=\"post\" action=\"?\">\n";
		print "<center>\n";
		print "<p style=\"font-weight:bold;\">$title</p>\n";
		print "<div>�F�؂��Ă��������B</div>\n";
		print "<div><input type=\"password\" name=\"lgps\" style=\"width:80px;\"></div>\n";
		print "<div><input type=\"submit\" value=\"�F��\">\n";
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
		print "IP�F$host ( $addr ) ... ";if($epc != "NoIP"){print "OK";}else{print "OUT";}print "<BR>";
		print "�e���� ... ";if($epc != "NoReferer"){print "OK";}else{print "OUT";}print "<BR>";
		print "�e��K�� ... ";if($epc !="NoIP" or $epc !="NoReferer"){print "OUT";}else{print "OK";}print "<BR>";
		print "���ʁF<font color=\"red\"><B>$ername</B></font><BR>";
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

//���L������ɍ폜���Ă͂Ȃ�܂���B����Ȃ�폜�҂́A�L���ł̎g�p�Ƃ����đՂ��܂��B
	function ipcfoot($n){
		global $limsw,$_COOKIE,$lgps,$host,$ipchost;

		if($n){
			print "<p align=\"center\">IP-Checker for PHP Ver2.9 - <B><a href=\"http://www.tenskystar.net\">�V��̔ޕ�</a></B></p>";
		}
	}
//Ver.2.9 12/12/20 Tor-Check��ǉ��B
//Ver.2.8 12/05/19 �Z�L�����e�B��̒���
//Ver:2.7 12/03/23 �w��N�G�����`�F�b�N���ē��e-��,�Ԋu�̋K�����s����悤�ɁB
//Ver:2.6 11/06/30 �K���Ώێ҂݂̂̃p�X���[�h�F�؂ɂ��ʉ߂��o����悤�ɁB
//Ver:2.5 11/01/11 �p�X�u���b�N���s����悤�ɁB�iIPC�ƘA�������邾���ŁA�p�X���[�h������������B)
//Ver:2.4 10/11/01 �A�N�Z�X����URL�𕡐��ݒ�\��
//Ver:2.3 10/10/17 �w��IP�A�N�Z�X�֎~���w��IP�̂݃A�N�Z�X�������ɂ��ł���l�ɁB
//Ver:2.2 10/04/05 �A�g����Cookie���p����ꍇ�ɃG���[����������̂��C���A�N�G�����Ɋ܂܂�镶���`�F�b�N��ǉ��B
//Ver:2.1 09/10/09 �������A����������IP,HOST�ǂ���ł��w��\�ɏC���B
//Ver:2.0 09/03/05 ����PHP�ƘA�����ċK�����s�������ɕύX�B
//Ver:1.5 08/09/16 �`�F�b�N�ʉߌ�̃y�[�W�\����ύX?
//Ver:1.4 08/03/31 �����ɂ��A�N�Z�X�֎~�̋K�����@��ύX�B�e���E�j���̎��Ԃ�ݒ肵�K�����s���l�ɁB
//Ver:1.3 08/03/30 ��Ăɂ��A�N�Z�X�֎~������E�j���̐ݒ��ǉ��A���t�@���[��񂪖����ꍇ�ɋK���B
//Ver:1.2 08/02/03 ��������('',)�ƂȂ��Ă���ƑS��������Ă��܂��̂��C���B
//Ver:1.1 07/02/18  �p�^�[���}�b�`���O���������ׁ̈AIPC-CGI����{�Ƃ��Ď��ԁA�w��URL�ȊO����̃A�N�Z�X�Ȃǂ̋K���A�ʉ߉\���Ȃǂ�ǉ��B
//Ver:1.0 07/02/18? IPC-CGI����{�Ƃ��Đ���B���PROXY�K�������݈̂ꉞ�����B

?>
