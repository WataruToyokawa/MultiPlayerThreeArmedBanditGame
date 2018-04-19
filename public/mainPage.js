//$(document).ready(function() {
$(window).load(function() {
	'use strict';

	var s,
			sessionName,
			roomName,
			connectionCounter = 0;

	var amazonID = location.search;
			amazonID = amazonID.substr( 10 );
			$("#amazonID").val(amazonID);

	//s = io.connect('http://v157-7-123-45.z1d16.static.cnode.jp:8080'); //リモート
	//s = io.connect('http://expserv01.let.hokudai.ac.jp:8080'); //リモート
	//s = io.connect('http://toyowataexp.webk-vps.net:8080', { query: 'amazonID='+amazonID }); //リモート
	s = io.connect('http://localhost:8080', { query: 'amazonID='+amazonID }); //ローカル

		// ２回目以降の connection (reconnection) でも、同じクライアントと認識させるための工夫
		// s.socket のプロパティとしてセッション名を保持させておき、それを認証キーのように使う
	s.on('S_to_C_clientSessionName', function(data) {
			connectionCounter += 1;
			sessionName = data.sessionName;
			roomName = data.roomName;
			s.io.opts.query = 'sessionName='+data.sessionName+'&roomName='+data.roomName+'&amazonID='+amazonID;
			console.log(s.io.opts.query);
	});

		//サーバから受け取るイベント
	s.on("connect", function () {
		console.log('connected');
	});  // 接続時
	s.on("disconnect", function (client) {
		console.log('disconnected');
		setTimeout(function() {
      // 再接続
      s.connect();  // -> connected
    }, 300);
	});  // 切断時
	s.on("reconnect", function() {
		console.log('reconnected');
	});

	// test ように、一回接続をきる関数をつくる======
	function disconnectmachine() {
		console.log('disconnectmachine!!!');
		setTimeout(function() {
      // 接続を切る
      s.disconnect();
    }, 15000);
	}
	//disconnectmachine();
	// =============================================

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

		//======== PING の監視 ======
	startTime = Date.now(); //読み込まれてすぐ
	s.emit('ping'); //読み込まれてすぐ
	setInterval(function() {
	  startTime = Date.now();
	  s.emit('ping');
	}, 500);
		// pong の返信を受けて latency の計算
	s.on('pong', function() {
	  latency.push( Date.now() - startTime );
		latency_10 = latency.slice(-10,latency.length);
		latency_10_total = 0;
		for(var i = 0;i<latency_10.length;i++){
			latency_10_total += latency_10[i];
		}
	  //console.log(latency);
		if(Math.floor(latency_10_total/latency_10.length) > 1500){
				//最近5秒間で平均1.5s以上の遅れが出たらそれ以上進まず、
				//強制的にquestionnaireへ移行させる
			$("#bonus_for_waiting").val(Math.round(waitingBonus*100)/100);
			//$("#totalGamePayoff").val(totalEarning[totalEarning.length-1]);
			$("#totalGamePayoff").val(totalPayoff);
			$("#ping_over").val(1);
				// main_task.php のフォームを介して、実験の変数をPOSTする
			s.io.opts.query = 'sessionName=already_finished';
			s.disconnect();
			setTimeout(function(){
				$("#form").submit();
			}, 100);

		}
	});
		//======== PING の監視 ======

		//======== monitoring Tab activity ==========
	var hiddenTimer,
		hidden_elapsedTime = 0;
		//ページが読み込まれたときの状況を判断
	if(document.hidden){
		hiddenTimer = setInterval(function(){
			hidden_elapsedTime += 500;
		}, 500);
	}
		//状況が変わったら再び判断
	document.addEventListener("visibilitychange",function(e){
		if(document.hidden)
		{
			hidden_elapsedTime += 1;
			hiddenTimer = setInterval(function(){
			hidden_elapsedTime += 500;
			}, 500);
		}
		else
		{
			clearTimeout(hiddenTimer);
			if(hidden_elapsedTime>1000){
				if(waitingStageTimerResetCounter > 0){
					clearTimeout(waitingStageTimer);
				}
				setTimeout(function(){
						//強制的にquestionnaireへ移行させる
					$("#bonus_for_waiting").val(Math.round(waitingBonus*100)/100);
					//$("#totalGamePayoff").val(totalEarning[totalEarning.length-1]);
					$("#totalGamePayoff").val(totalPayoff);
					$("#tab_over").val(1);
						// main_task.php のフォームを介して、実験の変数をPOSTする
					s.io.opts.query = 'sessionName=already_finished';
					s.disconnect();
					setTimeout(function(){
						$("#form").submit();
					}, 100);
				}, 200); //waitingBonus がしっかり計算されるのを待ってから移動させる
			}
			hidden_elapsedTime = 0;
		}
	});
		//======== end: monitoring tab activity =====

	s.on('S_to_C_wait_for_starting', function (data) {
		waiteForStarting(data);
	});

	var waitingStageTimerResetCounter = 0;
	var before;
	s.on('S_to_C_restTime', function (data) {
		maxWaitingTime = data.max;
		if(waitingStageTimerResetCounter > 0){
			clearTimeout(waitingStageTimer);
		}
		waitingStageTimerResetCounter += 1;
		before = new Date();
		countDownWaitingStage(data.restTime);
	});

	s.on('S_to_C_tellClientId', function (data) {
		confirmationID = data.id;
		$("#confirmationID").val(confirmationID);
		//console.log(confirmationID);
	});

	s.on('S_to_C_start_session', function (data) {
		countDownStarting();
		waitingStageTimerResetCounter = 0; // to end waiting stage
		maxChoiceTime = data.choiceTime;
		boxDist = data.env;
		totalRound = data.totalRound;
		setTimeout(function(){
			countDownChoiceStage( data.choiceTime - 3000, data.value.round );
			createExperimentalElements( data.value );
		}, 6000);
		/*setTimeout(function(){
			maxChoiceTime = data.choiceTime;
			boxDist = data.env;
			totalRound = data.totalRound;
			countDownChoiceStage( data.choiceTime - 2000, data.value.round );
			createExperimentalElements( data.value );
		}, 6000);*/
	});

	s.on('S_to_C_waitOthers', function (data) {
		//waiteForStarting(data)
		waitOthers(data);
	});

	s.on('S_to_C_showResult', function (data) {
		if (typeof earnings[data.round-1] !== 'undefined'){
			console.log('your earnings is '+ earnings[data.round-1]+' cents');
		} else {
			earnings[data.round-1] = 0;
			console.log('your earnings is '+ earnings[data.round-1]+' cents');
		}
		if (data.round >= 2) {
			totalEarning[data.round-1] = totalEarning[data.round-2] + earnings[data.round-1];
		} else {
			totalEarning[data.round-1] = earnings[data.round-1];
		}
		if(earnings[data.round-1]){
			totalPayoff += earnings[data.round-1];
		}

			// 選択ステージ用の要素を画面から消す
		clearTimeout(waitingChoiceTimer);
		$('#box_area').attr('class', 'box_result_mode');
		document.getElementById('restTime').innerHTML = '<p>&nbsp;</p>';
		document.getElementById('current_groupsize').innerHTML = '<p>&nbsp;</p>';
		document.getElementById('total_earning').innerHTML = 'Total Earnings: loading...';
		document.getElementById('current_round').innerHTML = 'Current Round: loading...';
		document.getElementById('box_area').innerHTML = '';
			// result の表示
		if(choices[data.round-1] < 0){
			document.getElementById('box_area').innerHTML = '<br><br><p class="payoffMsg">You <span class="note">missed</span> this round and earned  <span class="note">0 cent</span>.</p>';
		} else {
			var j = choices[data.round-1] + 1;
			document.getElementById('box_area').innerHTML = '<br><br><p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+Math.round(earnings[data.round-1]*10000)/100+' cents</span>.</p>';
		}
	});

	s.on('S_to_C_proceed', function (data) {
		if (data.value.round > totalRound) {
			$("#bonus_for_waiting").val(Math.round(waitingBonus*100)/100);
			//$("#totalGamePayoff").val(totalEarning[totalEarning.length-1]);
			$("#totalGamePayoff").val(totalPayoff);
				// main_task.php のフォームを介して、実験の変数をPOSTする
			s.io.opts.query = 'sessionName=already_finished';
			s.disconnect();
			setTimeout(function(){
				$("#form").submit();
			}, 100);
		} else {
			console.log('proceeding to '+data.value.round+' in '+totalRound);
			boxDist = data.env;
			countDownChoiceStage( data.choiceTime-3000, data.value.round );
			createExperimentalElements( data.value );
		}
	});


	s.on('S_to_C_welcomeback', function (data) {
		//console.log('welcome back' + data);
		if (waitingChoiceTimer) {
			clearTimeout(waitingChoiceTimer);
		}
		if (waitingStageTimer) {
			clearTimeout(waitingStageTimer);
		}
		setTimeout(function(){
				//強制的にquestionnaireへ移行させる
			$("#bonus_for_waiting").val(Math.round(waitingBonus*100)/100);
			//$("#totalGamePayoff").val(totalEarning[totalEarning.length-1]);
			$("#totalGamePayoff").val(totalPayoff);
			$("#tab_over").val(2);
				// main_task.php のフォームを介して、実験の変数をPOSTする
			s.io.opts.query = 'sessionName=already_finished';
			s.disconnect();
			setTimeout(function(){
				$("#form").submit();
			}, 100);
		}, 200); //waitingBonus がしっかり計算されるのを待ってから移動させる
	});

	s.on('S_to_C_multipleChoice', function (data) {
			// 二重に送られちゃった分の金額を消去する
		console.log('before reduced: '+totalPayoff);
		totalPayoff -= data.payoff;
		console.log('after reduced: '+totalPayoff);
	});


	/*
	---------slotmachine の挙動 -----------------------------------------------------------
	*/
		//設定値
	var startTime,
		latency = [0,0,0,0,0,0,0,0,0,0],
		latency_10,
		latency_10_total,
		btn0,
		btn1,
		btn2,
		newDivCol = [],
		groupSizeBar,
		restTimeBar,
		waitingBonusBar,
		socialFreqCanvas,
		socialNum,
		maxWaitingTime,
		maxChoiceTime,
		totalRound,
		boxDist;

		//初期値
	var	timers = [],
		nums = [],
		counter = [],
		waitingBonus = 0,
		earning,
		currentRound = 1,
		totalEarning = [0],
		totalPayoff = 0,
		choices = [],
		earnings = [],
		result = [],
		waitingStageTimer,
		waitingChoiceTimer,
		choiceCountDownCounter = [];

	var confirmationID;

	function countDownStarting(){
		document.getElementById('gameLabel').innerHTML = '';
		document.getElementById('restTime').innerHTML = '';
		document.getElementById('current_groupsize').innerHTML = '';
		clearTimeout(waitingStageTimer);
		document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">5</h1>'
		setTimeout(function(){
			document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">4</h1>'
		},1000);
		setTimeout(function(){
			document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">3</h1>'
		},2000);
		setTimeout(function(){
			document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">2</h1>'
		},3000);
		setTimeout(function(){
			document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">1</h1>'
		},4000);
		setTimeout(function(){
			document.getElementById('box_area').innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><h1 id="countDown">GAME STARTS!</h1>'
		},5000);
	}

	function countDownChoiceStage (choiceTime, round) {

		var choiceTime2 = choiceTime - 500;
		if (choiceTime2 < 0) {
				// counter == 1 でピンクになって放置されてる奴があれば、それを止める
			//clearTimeout(timers[counter.indexOf(1)]);
			if(counter.indexOf(1)>=0){
				clearTimeout(timers[counter.indexOf(1)]);
				timeupChoice(counter.indexOf(1), round);
				sendPinkResult(choices[round-1], earnings[round-1]);
			}
			if(typeof earnings[round-1] !== 'undefined'){
				var j = choices[choices.length-1] + 1;
				document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+ Math.round(earnings[round-1]*10000)/100+ ' cents</span>.</p>';
			} else {
				choices[round-1] = -1; //miss
				document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You <span class="note">missed</span> this round and earned  <span class="note">0 cent</span>.</p>';
			}
		}
		restTimeBar = document.createElement("canvas");
		restTimeBar.className = 'bar';
		restTimeBar.id = 'choiceTime';
		if(!restTimeBar || !restTimeBar.getContext) return false;
		var ctx = restTimeBar.getContext('2d');
		restTimeBar.width = 300;
		restTimeBar.height = 10;
		ctx.fillStyle = '#FFFFFF'; // bar's backgroud color
		ctx.fillRect(0,0,300,10);
		ctx.fillStyle = '#19EDF8'; // bar's color
		var barposition = 2+(296*1000/maxChoiceTime)*(maxChoiceTime/1000-Math.floor(choiceTime/1000));
		if (!choiceCountDownCounter[round-1]){
			ctx.fillRect(2,2,2,6);
			choiceCountDownCounter[round-1] = 1;
		}else if(choiceCountDownCounter[round-1] <= 2){
			ctx.fillRect(2,2, barposition*0.5, 6);
			choiceCountDownCounter[round-1] += 1;
		}else if(choiceCountDownCounter[round-1] <= 4){
			ctx.fillRect(2,2, barposition*0.6, 6);
			choiceCountDownCounter[round-1] += 1;
		}else if(choiceCountDownCounter[round-1] <= 6){
			ctx.fillRect(2,2, barposition*0.7, 6);
			choiceCountDownCounter[round-1] += 1;
		}else if(choiceCountDownCounter[round-1] <= 8){
			ctx.fillRect(2,2, barposition*0.8, 6);
			choiceCountDownCounter[round-1] += 1;
		}else if(choiceCountDownCounter[round-1] <= 10){
			ctx.fillRect(2,2, barposition*0.9, 6);
			choiceCountDownCounter[round-1] += 1;
		}else{
			ctx.fillRect(2,2, barposition, 6);
		}
		if (choiceTime >= 0){
			document.getElementById('restTime').innerHTML = '<br><p>Moving to the next round in <span id="time">' +
																Math.floor(choiceTime/1000) + '</span> sec.</p><br>';
			document.getElementById('restTime').appendChild(restTimeBar);
		}
			// 繰返し
		waitingChoiceTimer = setTimeout(function(){ countDownChoiceStage(choiceTime2, round) }, 500);
	}

	function countDownWaitingStage (restTime) {
		//console.log(Math.round(waitingBonus*100)/100);
			//resetting waiting timer
		var now = new Date();
		var elapsedTime = (now.getTime() - before.getTime());
		if(elapsedTime > 500)
		{
				//Recover the motion lost while inactive (in background tabs).
			waitingBonus += 0.5*(elapsedTime/500)*(1/60)*0.12;
			var restTime2 = restTime - 500*(elapsedTime/500);
		}
		else
		{
			waitingBonus += 0.5*(1/60)*0.12; // 12 cents per minutes for eny time spent in waiting room
			var restTime2 = restTime - 500;
		}
		before = now;

			// 残り時間の表示バー
		restTimeBar = document.createElement("canvas");
		restTimeBar.className = 'bar';
		restTimeBar.id = 'waitingTime';
		if(!restTimeBar || !restTimeBar.getContext) return false;
		var ctx = restTimeBar.getContext('2d');
		restTimeBar.width = 300;
		restTimeBar.height = 10;
		ctx.fillStyle = '#FFFFFF'; // bar's backgroud color
		ctx.fillRect(0,0,300,10);
		ctx.fillStyle = '#19EDF8'; // bar's color
		var barposition = 2+(296*1000/maxWaitingTime)*(maxWaitingTime/1000-Math.floor(restTime/1000));
		ctx.fillRect(2,2, barposition, 6);
		document.getElementById('gameLabel').innerHTML = 'Waiting Room';
		if (restTime>0){
			document.getElementById('restTime').innerHTML = 'Game will start in 0' + Math.floor(Math.floor(restTime/1000)/60) +
																 ' : ' +Math.floor(restTime/1000)%60 + '<br>';
		} else {
			document.getElementById('restTime').innerHTML = 'Game will start in 00 : 00<br>';
		}
		document.getElementById('restTime').appendChild(restTimeBar);
			//改行
		var Br=document.createElement("br");
		document.getElementById('restTime').appendChild(Br); //'restTime'の子要素としてBrを追加
		var BonusTxt = document.createElement("div");
		BonusTxt.style.cssText += 'color: #f91970;';
		BonusTxt.innerHTML = 'Your Waiting Bonus is ' + Math.round((Math.round(waitingBonus*100)/100)*100) + ' cents<br>';
		document.getElementById('restTime').appendChild(BonusTxt);
			// waitingBonusBar
		waitingBonusBar = document.createElement("canvas");
		waitingBonusBar.className = 'bar';
		waitingBonusBar.id = 'waitingBonus';
		if(!waitingBonusBar || !waitingBonusBar.getContext) return false;
		var ctx2 = waitingBonusBar.getContext('2d');
		waitingBonusBar.width = 300;
		waitingBonusBar.height = 10;
		ctx2.fillStyle = '#FFFFFF'; // bar's backgroud color
		ctx2.fillRect(0,0,300,10);
		ctx2.fillStyle = '#f91970'; // bar's color
		var bonusPosition = 2+(296/0.6)*waitingBonus;
		ctx2.fillRect(2,2, bonusPosition, 6);
		document.getElementById('restTime').appendChild(waitingBonusBar);
		waitingStageTimer = setTimeout(function(){ countDownWaitingStage(restTime2) }, 500);
	}

	function waiteForStarting(data) {
		if(data.roomName.charAt(0)=='m'){
			document.getElementById('current_groupsize').innerHTML = 'Number of participants: '+data.room.n+' / '+ data.maxG +'<br>';
		} else {
			document.getElementById('current_groupsize').innerHTML = 'Number of participants: '+data.room.n+' / '+ data.maxG_sub +'<br>';
		}
		document.getElementById('box_area').innerHTML = '<h1 id="waitingMsg"><br>Please Wait for Start</h1><p>The task will start when you reach the maximum waiting time or when all participants get ready (whichever earlier).</p><br><p><span>Please do NOT switch to another tab or window. If you do so you would be terminated from this experiment.</span></p>';
			// Canvas
		groupSizeBar = document.createElement("canvas");
		groupSizeBar.className = 'bar';
		groupSizeBar.id = 'waitingOthers';
		if(!groupSizeBar || !groupSizeBar.getContext) return false;
		var ctx = groupSizeBar.getContext('2d');
		groupSizeBar.width = 300;
		groupSizeBar.height = 10;
		ctx.fillStyle = '#FFFFFF'; // bar's backgroud color
		ctx.fillRect(0,0,300,10);
		ctx.fillStyle = '#19EDF8'; // bar's color
		if(data.roomName.charAt(0)=='m'){
			ctx.fillRect(2,2, 2+(296/data.maxG)*data.room.n, 6);
		} else {
			ctx.fillRect(2,2, 2+(296/data.maxG_sub)*data.room.n, 6);
		}
		document.getElementById('current_groupsize').appendChild(groupSizeBar);
	}

	function waitOthers(data) {
		if (typeof earnings[data.round-1] !== 'undefined'){
			console.log('Waiting: your earnings is '+ earnings[data.round-1]);
		} else {
			earnings[data.round-1] = 0;
			console.log('Waiting: your earnings is '+ earnings[data.round-1]);
		}
		document.getElementById('box_area').innerHTML = '';
			// result の表示
		var j = choices[choices.length-1] + 1;
		document.getElementById('box_area').innerHTML = '<p class="payoffMsg">You chose <span class="note">lottery '+j+'</span> and earned <span class="note">'+Math.round(earnings[data.round-1]*10000)/100+' cents</span>.</p>';
	}

	function createExperimentalElements(data){
		console.log(data);
		if(data.round===1){
			document.getElementById('restTime').innerHTML = '';
			//clearTimeout(waitingStageTimer);
		}
		//document.getElementById('current_groupsize').innerHTML = data.n + ' participants exist';
		document.getElementById('gameLabel').innerHTML = 'Lottery Game';
		//document.getElementById('total_earning').innerHTML = 'Total Earnings: ' + Math.round(totalEarning[totalEarning.length-1]*10000)/100 +' cents';
		document.getElementById('total_earning').innerHTML = 'Total Earnings: ' + Math.round(totalPayoff*10000)/100 +' cents';
		document.getElementById('current_round').innerHTML = 'Current Round: ' + data.round + ' / 70';
			// waiting massage の削除
		document.getElementById('box_area').innerHTML = '';
			// 実験用のエレメントを画面に表示
		$('#box_area').attr('class', 'box_exp_mode');
		for (var i = 0; i <= 2; i++) {
			var j = i+1;
			newDivCol[i] = document.createElement("div");
			newDivCol[i].className = 'col';
			newDivCol[i].id = 'col_'+i;
			newDivCol[i].innerHTML = "<div class='btn' id='btn"+i+"'><p class='machineName' id='lot"+i+"'>click to choose</p><img src='./img/machine_normal_"+j+".png' class='machine_img' /></div><br style='line-height:1px;'>"+
									"<div id='socialFreq"+ i +"'>"+ data.socialFreq[data.round-1][i] +"<br></div><br>";
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
			ctx.fillRect(10,0,80, 150*( data.socialFreq[data.round-1][i]/data.n ));
			document.getElementById('socialFreq'+i).appendChild(socialFreqCanvas);
		}
		btn0 = document.getElementById('btn0');
		btn1 = document.getElementById('btn1');
		btn2 = document.getElementById('btn2');
		if(_ua.Mobile || _ua.Tablet){
			btn0.addEventListener('touchend', function() { startStopSlot(0, data.round); }	);
			btn1.addEventListener('touchend', function() { startStopSlot(1, data.round); }	);
			btn2.addEventListener('touchend', function() { startStopSlot(2, data.round); }	);
		} else {
			btn0.addEventListener('click', function() { startStopSlot(0, data.round); }	);
			btn1.addEventListener('click', function() { startStopSlot(1, data.round); }	);
			btn2.addEventListener('click', function() { startStopSlot(2, data.round); }	);
		}
	}

	function sendResult(machineNum, earning) {
		s.emit('C_to_S_choiceResult',
			{ID:sessionName,
			waitingBonus:Math.round(waitingBonus*100)/100,
			machine:machineNum,
			result:earning,
			latency_ms:Math.floor(latency_10_total/latency_10.length)} );
	}

	function sendPinkResult(machineNum, earning) {
		s.emit('C_to_S_timeupResult',
			{ID:sessionName,
			waitingBonus:Math.round(waitingBonus*100)/100,
			machine:machineNum,
			result:earning,
			latency_ms:Math.floor(latency_10_total/latency_10.length)} );
	}

	function showResult(n, round) {
		if(round >= 2){
			totalEarning[round-1] = totalEarning[round-2] + earning;
		} else {
			totalEarning[round-1] = earning;
		}
		choices[round-1] = n;
		earnings[round-1] = earning;
		currentRound ++;

			// 次のラウンドへ向けて初期化
		counter = [];
		nums = [];
		timers = [];

		//document.getElementById('total_earning').innerHTML = 'Total Earnings: ' + Math.round(totalEarning[totalEarning.length-1]*10000)/100+' cents';
		document.getElementById('current_round').innerHTML = 'Current Round: ' + round + ' / 70';
		//document.getElementById('btn' + n).innerHTML = '???'; //ボタンの言葉を戻す
		//$('.btn:eq('+n+')').css('background', '#00aaff'); //ボタンの色を戻す
	}

	function timeupChoice(n, round){
		if(round >=2){
			totalEarning[round-1] = totalEarning[round-2] + earning;
		} else {
			totalEarning[round-1] = earning;
		}
		choices[round-1] = n;
		earnings[round-1] = earning;
			// initialization
		counter = [];
		nums = [];
	}

	function startStopSlot(n, round) {
			//n番目に要素があり、かつ０でない場合: 選択
		if (typeof counter[n] !== 'undefined' && counter[n] !==0) {
			stopSlot(n);
			sendResult(n, nums[n]);
			showResult(n, round);
		} else if (counter[n] ===0) { //n===0ということは、他が今回っている: リセット&スタート
			resetSlot(counter.indexOf(1));
			var j = counter.indexOf(1) + 1;
			$('.btn:eq('+counter.indexOf(1)+')').css({'background':'#E2E4D9', 'opacity':'0.75', 'font-size':'100%'});
			document.getElementById('btn' + counter.indexOf(1)).innerHTML = "<p class='machineName' id='lot"+counter.indexOf(1)+"'>click to choose</p><img src='./img/machine_normal_"+j+".png' class='machine_img' />";
			var k = n + 1;
			$('.btn:eq('+n+')').css({'background':'#FFFAD9', 'opacity':'0.85', 'font-size':'100%'});
			if (typeof document.getElementById('num' + n) !== 'null') {
				document.getElementById('btn' + n).innerHTML = "<p class='machineName' id='lot"+n+"'>click to confirm</p><img src='./img/machine_active_"+k+".png' class='machine_img' />";
			}
			startSlot(n);
			counter = [0,0,0];
			counter[n] = 1;
		} else { //n番目に要素がない場合: 回転スタート
			var k = n + 1;
			$('.btn:eq('+n+')').css({'background':'#FFFAD9', 'opacity':'0.85', 'font-size':'100%'});
			if (typeof document.getElementById('num' + n) !== 'null') {
				document.getElementById('btn' + n).innerHTML = "<p class='machineName' id='lot"+n+"'>click to confirm</p><img src='./img/machine_active_"+k+".png' class='machine_img' />";
			}
			startSlot(n);
			counter = [0, 0, 0];
			counter[n] = 1;
		}
	}

	function resetSlot(n) {
			//すでにn番目の要素に値が入っている場合には return で抜ける
		if (typeof nums[n] !== 'undefined') {
			return;
		}
		var j = n+1;
		clearTimeout(timers[n]);
		//document.getElementById('btn' + n).innerHTML = "<p class='machineName' id='lot"+n+"'>click to choose</p><img src='./img/machine_normal_"+j+".png' class='machine_img' />";
		//$('.btn:eq('+n+')').css({'background':'#E2E4D9', 'opacity':'0.75', 'font-size':'100%'});
	} // old colour: #00aaff

	function startSlot(n) {
			//すでにn番目の要素に値が入っている場合には return で抜ける
		if (typeof nums[n] !== 'undefined') {
			return;
		}
		//var j = n+1;
		//$('.btn:eq('+n+')').css({'background':'#FFFAD9', 'opacity':'0.85', 'font-size':'100%'});
		/*if (typeof document.getElementById('num' + n) !== 'null') {
			document.getElementById('btn' + n).innerHTML = "<p class='machineName' id='lot"+n+"'>click to confirm</p><img src='./img/machine_active_"+j+".png' class='machine_img' />";
		}*/
		runSlot(n);
	} // old colour: #FF5E96

	function stopSlot(n) {
			//すでにn番目の要素に値が入っている場合には return で抜ける
		if (typeof nums[n] !== 'undefined') {
			return;
		}
			//もしまだであれば以下を実行
		clearTimeout(timers[n]);
		//document.getElementById('btn' + n).innerHTML = earning;
		nums[n] = earning;
	}

	function runSlot(n) {
		//var j = n + 1;
		var sigma = 0.55; // sigma^2 = 0.3025
		if (typeof document.getElementById('num' + n) !== 'null') {
			//document.getElementById('btn' + n).innerHTML = slotMarks[Math.floor(Math.random() * slotMarks.length)];
			//document.getElementById('btn' + n).innerHTML = "<p class='machineName' id='lot"+n+"'>click to confirm</p><img src='./img/machine_active_"+j+".png' class='machine_img' />";
		}
		earning = (Math.round(normRand(boxDist[n], sigma)*100)/100)/100;
		if(earning < 0){
			earning = 0;
		}
		//earning = normBinom(boxDist[n]);
		timers[n] = setTimeout(function() {
			runSlot(n);
		}, 50);
	}

		// 確率m であたりが出る関数
	function normBinom (m) {
		var a = Math.random();
		if(a <= m) {
			return 0.2;
		} else {
			return 0.02;
		}
	}

		/**
		 * 正規分布乱数関数 参考:http://d.hatena.ne.jp/iroiro123/20111210/1323515616
		 * @param number m 平均μ
		 * @param number s 分散σ^2 <- σ = s
		 * @return number ランダムに生成された値
		 * ボックス＝ミュラー法
		 */
	function normRand(m, s) {
	    var a = 1 - Math.random();
	    var b = 1 - Math.random();
	    var c = Math.sqrt(-2 * Math.log(a));
	    if(0.5 - Math.random() > 0) {
	        return c * Math.sin(Math.PI * 2 * b) * s + m;
	    }else{
	        return c * Math.cos(Math.PI * 2 * b) * s + m;
	    }
	};

});
