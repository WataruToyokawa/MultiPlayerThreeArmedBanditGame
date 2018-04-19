$(document).ready(function() {
	'use strict';

	var connectBtn = document.getElementById('connectBtn');
	var $script    = $('#script_json');
	var amazonID   = JSON.parse($script.attr('data-amazonID'));
	var	ticket     = JSON.parse($script.attr('data-ticket')),
			save		= JSON.parse($script.attr('data-save'));
	console.log(amazonID);

	var gameServer = 'localhost:8080';
	//var gameServer = 'v157-7-123-45.z1d16.static.cnode.jp:8080';
	//var gameServer = 'expserv01.let.hokudai.ac.jp:8080';
	//var gameServer = 'toyowataexp.webk-vps.net:8080';

	var btn0,
		btn1,
		btn2,
		newDivCol = [],
		socialFreqCanvas;

		//初期値
	var	currentRound = 1,
		earnings = [],
		waitingChoiceTimer;
	var choiceCounter = [0,0,0],
		previousChoice = [0,0,0];
	var round = 1;
	var showingTimer;

		// クライアントの環境判定
	var _ua = (function(u){
	  return {
	    Tablet:(u.indexOf("windows") != -1 && u.indexOf("touch") != -1)
	      || u.indexOf("ipad") != -1
	      || (u.indexOf("android") != -1 && u.indexOf("mobile") == -1)
	      || (u.indexOf("firefox") != -1 && u.indexOf("tablet") != -1)
	      || u.indexOf("kindle") != -1
	      || u.indexOf("silk") != -1
	      || u.indexOf("playbook") != -1,
	    Mobile:(u.indexOf("windows") != -1 && u.indexOf("phone") != -1)
	      || u.indexOf("iphone") != -1
	      || u.indexOf("ipod") != -1
	      || (u.indexOf("android") != -1 && u.indexOf("mobile") != -1)
	      || (u.indexOf("firefox") != -1 && u.indexOf("mobile") != -1)
	      || u.indexOf("blackberry") != -1
	  }
	})(window.navigator.userAgent.toLowerCase());

	createExperimentalElements(round);

	function createExperimentalElements(round){
		if(round>1){
			clearTimeout(showingTimer);
			document.getElementById('guid').innerHTML = 'Round '+round+'!';
		}
		if(round==1){
			document.getElementById('guid').innerHTML = 'Click one of the boxes!';
			document.getElementById('total_earning').innerHTML = 'Total Earnings: 0 cents';
		}else if(round==2){
			document.getElementById('total_earning').innerHTML = 'Total Earnings: ?? cents';
		}else if(round==3){
			document.getElementById('total_earning').innerHTML = 'Total Earnings: ??+△ cents';
		}else if(round==4){
			document.getElementById('total_earning').innerHTML = 'Total Earnings: ??+△+◎ cents';
		}else if(round==5){
			document.getElementById('total_earning').innerHTML = 'Total Earnings: ??+△+◎+☓ cents';
		}

		document.getElementById('current_round').innerHTML = 'Current Round: ' + round + ' / 4';
			// waiting massage の削除
		document.getElementById('box_area').innerHTML = '';
			// 実験用のエレメントを画面に表示
		$('#box_area').attr('class', 'box_exp_mode');
		for (var i = 0; i <= 2; i++) {
			newDivCol[i] = document.createElement("div");
			newDivCol[i].className = 'col';
			newDivCol[i].id = 'col_'+i;
			var j = i+1;
			newDivCol[i].innerHTML = "<div class='btn' id='btn"+i+"'><p class='machineName' id='lot"+i+"'>click to choose</p><img src='./img/machine_normal_"+j+".png' class='machine_img' /></div><br style='line-height:1px;'>"+
									"<div id='socialFreq"+ i +"'>"+ previousChoice[i] +"<br></div><br>";
			document.getElementById('box_area').appendChild(newDivCol[i]);
				// Canvas
			socialFreqCanvas = document.createElement("canvas");
			socialFreqCanvas.className = 'socialInfo';
			socialFreqCanvas.id = 'socialFreq_'+i;
			if(!socialFreqCanvas || !socialFreqCanvas.getContext) return false;
			var ctx = socialFreqCanvas.getContext('2d');
			socialFreqCanvas.width = 100;
			socialFreqCanvas.height = 150;
			//ctx.fillStyle = '#FFFFFF'; // socialInfo's backgroud color
			//ctx.fillRect(0,0,100,150);
			ctx.fillStyle = '#E40741'; // social information's color
			ctx.fillRect(10,0,80, 120*( previousChoice[i] ));
			document.getElementById('socialFreq'+i).appendChild(socialFreqCanvas);
		}
		btn0 = document.getElementById('btn0');
		btn1 = document.getElementById('btn1');
		btn2 = document.getElementById('btn2');
		if(_ua.Mobile || _ua.Tablet){
			btn0.addEventListener('touchend', function() { clickBox(0, round); }	);
			btn1.addEventListener('touchend', function() { clickBox(1, round); }	);
			btn2.addEventListener('touchend', function() { clickBox(2, round); }	);
		} else {
			btn0.addEventListener('click', function() { clickBox(0, round); }	);
			btn1.addEventListener('click', function() { clickBox(1, round); }	);
			btn2.addEventListener('click', function() { clickBox(2, round); }	);
		}
	}

	function clickBox(boxnum, round){
		//console.log(_ua);
		document.getElementById('guid').innerHTML = 'Click to confirm!';
		if(choiceCounter[boxnum]==0){
				//初期化
			choiceCounter = [0,0,0];
			for (var i = 0; i <= 2; i++) {
				var j = i+1;
				if(i == boxnum){
					$('.btn:eq('+i+')').css({'background':'#FFFAD9', 'opacity':'0.9', 'font-size':'100%'});
					document.getElementById('btn' + i).innerHTML = "<p class='machineName' id='lot"+i+"'>click to confirm</p><img src='./img/machine_active_"+j+".png' class='machine_img' />";
				}else{
					$('.btn:eq('+i+')').css({'background':'#E2E4D9', 'opacity':'0.8', 'font-size':'100%'});
					document.getElementById('btn' + i).innerHTML = "<p class='machineName' id='lot"+i+"'>click to choose</p><img src='./img/machine_normal_"+j+".png' class='machine_img' />";
				}
			}

				//カウント
			choiceCounter[boxnum] += 1;
			//$('.btn:eq('+boxnum+')').css({'background':'#FF5E96' #FFFAD9, 'opacity':'1.0', 'font-size':'70%'});
			//document.getElementById('btn' + boxnum).innerHTML = 'click<br />again';
		}else{
				//選択
			makingChoice(boxnum, round);
		}
	}

	function makingChoice(boxnum, round){
		var j = boxnum + 1;
		previousChoice = [0,0,0];
		previousChoice[boxnum] = 1;
		document.getElementById('guid').innerHTML = 'You got some money!';
			// 選択ステージ用の要素を画面から消す
		document.getElementById('box_area').innerHTML = '';
			// result の表示
		if(round==1){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+'??'+' cents</span>.</p>';
		}else if(round==2){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+'△'+' cents</span>.</p>';
		}else if(round==3){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+'◎'+' cents</span>.</p>';
		}else if(round==4){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+'☓'+' cents</span>.</p>';
		}else if(round==5){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+'λ'+' cents</span>.</p>';
		}
		waitingChoiceTimer = setTimeout(function(){ proceeding(j, round) }, 1500);
	}

	function proceeding(lot, round){
			// 選択ステージ用の要素を画面から消す
		clearTimeout(waitingChoiceTimer);
		$('#box_area').attr('class', 'tutorial_finish_mode');
		document.getElementById('total_earning').innerHTML = 'Total Earnings: loading...';
		document.getElementById('current_round').innerHTML = 'Current Round: loading...';
		choiceCounter = [0,0,0];
		if(round==1){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+lot+'</span> and earned <span class="note">'+'??'+' cents</span>.</p>';
		}else if(round==2){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+lot+'</span> and earned <span class="note">'+'△'+' cents</span>.</p>';
		}else if(round==3){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+lot+'</span> and earned <span class="note">'+'◎'+' cents</span>.</p>';
		}else if(round==4){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+lot+'</span> and earned <span class="note">'+'☓'+' cents</span>.</p>';
		}else if(round==5){
			document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+lot+'</span> and earned <span class="note">'+'λ'+' cents</span>.</p>';
		}
		round += 1;
		if(round<5){
			showingTimer = setTimeout(function(){ createExperimentalElements(round) },1500);
		}else{
			showingTimer = setTimeout(function(){ endTutorial() },1500);
		}
	}

	function endTutorial(){
		clearTimeout(showingTimer);
		document.getElementById('guid').innerHTML = 'Well done!';
		document.getElementById('total_earning').innerHTML = '';
		document.getElementById('current_round').innerHTML = '';
		document.getElementById('box_area').innerHTML = '';
		document.getElementById('game_start').innerHTML = "<div class='btn2'><div id='connectBtn'>GAME START</div></div>";
		document.getElementById('tutorial_ending').innerHTML ="<p class='lead'>Tutorial finished!<br><br>Now you can connect to the actual experimental game by cliking the following \'GAME START\' button.</p>";
		connectBtn = document.getElementById('connectBtn');
		connectBtn.addEventListener('click', function(argument) {
			if (ticket === save) {
				window.location.href = "http://"+ gameServer +"/?amazonID=" + amazonID;
			} else {
				window.location.replace("https://www.mturk.com/mturk/welcome");
			}
		});
	}


});
