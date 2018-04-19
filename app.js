/*===============================================================
// Amazon's MTurk Experiment on 2015 experimental server
// Author: Wataru Toyokawa
// Requirements:
//    * Node.js
//    * node_modules: express, socket.io, fast-csv, php-express
// ============================================================== */

  // 実験条件の設定
  // When you are debugging, you should set the server to localhost
var condition = 1, //1:312, 2:132, 3:321, 4:123, 5:231, 6:213
    expNum = 0,
    maxGroupSize = 3, // setting maxGroupSize > 1, you can run a multi-player game 
    maxGroupSize_sub = 1,
    totalRound = 70,
    portnum = 8080,
    htmlServerName = 'localhost:8888/MultiPlayerThreeArmedBanditGame';
    //htmlServerName = 'toyowataexp.webk-vps.net/MultiPlayerThreeArmedBanditGame';


  // モジュールのインストールと、socket.io & express の決まり文句
var csv = require("fast-csv"),
    fs = require('fs'),
    path = require("path"),
    express = require('express'),
    app = express(),
    server = require('http').Server(app),
    io = require('socket.io')(server);

var oneDay = 86400000,
    thirtyMin = 1800000;

// require php-express and config
var phpExpress = require('php-express')({
      binPath: '/usr/bin/php' // php bin path
    });

app.use(express.static(__dirname + '/public', { maxAge: thirtyMin }));


app.get('/', function(req, res){
    // instruction2 を経由してかつIDが定義されている場合にのみ、タスクを始める
  if (typeof req.query.amazonID !== 'undefined' && req.headers['referer'] === 'http://'+htmlServerName+'/public/tutorial.php'){
    //res.render('/main_task.html');
    res.sendFile(__dirname + '/public/main_task.html');
    AmazonIDs.push(req.query.amazonID);
    //console.log(AmazonIDs);
  } else {
    // 不当なアクセスは、とりあえず同意画面に飛ばす（AMT本家のサイトとかがいいのかも？）
    //console.log(req.headers['referer']);
    res.redirect('http://'+htmlServerName+'/public/doui/ConsentForm.php');
  }
});


    // 実験サーバーの初期化
var changingPoint = 41,//Math.floor(totalRound/2)+3,
    env_1 = [],
    env_2 = [],
    performance_1 = [],
    performance_2 = [],
    AmazonIDs = [];
    sessionNameSpace = {};
var maxWaitingTime = 5*60*1000; // 5min
var countDownWaiting = new Object();
var maxChoiceTime = 11*1000; // 10 sec
var countDownChoice = new Object();
//var hiddenTimer = new Object();
//var hiddenStart_at = new Object();
//var hidden_elapsedTime = new Object();
//var hiddenCounter = new Object();
var subjectCounter = 0; //接続人数
var mainRoomName = 'mainRoom'+condition+'_'+expNum;
var roomList = {};
roomList['finishedRoom'] = {n:0, total_n:0, starting:0, restTime:maxWaitingTime, choiceTime:[], round:1, done:[], socialFreq:[[0, 0, 0]]};
roomList[mainRoomName] = {n:0, total_n:0, starting:0, restTime:maxWaitingTime, choiceTime:[], round:1, done:[], socialFreq:[[0, 0, 0]]};
var choiceCounter = {};
var myD = new Date(),
    myYear = myD.getFullYear(),
    myMonth = myD.getMonth() + 1,
    myDate = myD.getDate(),
    myHour = myD.getUTCHours(),
    myMin = myD.getUTCMinutes();
if(myMonth<10){myMonth = '0'+myMonth;}
if(myDate<10){myDate = '0'+myDate;}
if(myHour<10){myHour = '0'+myHour;}
if(myMin<10){myMin = '0'+myMin;}

var high = 3.1,
    interval = 0.3, //variance = 0.3
    mid = high - interval,
    low = high - 2*interval;

  // condition の設定 (環境1では２種類の選択肢)
switch (condition) {
  case 1:
    env_1 = [low,low,mid];
    env_2 = [high,low,mid];
    performance_1 = [1,1,2];
    performance_2 = [3,1,2];
    break;
  case 2:
    env_1 = [low,low,mid];
    env_2 = [low,high,mid];
    performance_1 = [1,1,2];
    performance_2 = [1,3,2];
    break;
  case 3:
    env_1 = [low,mid,low];
    env_2 = [high,mid,low];
    performance_1 = [1,2,1];
    performance_2 = [3,2,1];
    break;
  case 4:
    env_1 = [low,mid,low];
    env_2 = [low,mid,high];
    performance_1 = [1,2,1];
    performance_2 = [1,2,3];
    break;
  case 5:
    env_1 = [mid,low,low];
    env_2 = [mid,high,low];
    performance_1 = [2,1,1];
    performance_2 = [2,3,1];
    break;
  case 6:
    env_1 = [mid,low,low];
    env_2 = [mid,low,high];
    performance_1 = [2,1,1];
    performance_2 = [2,1,3];
    break;
  default:
}

var //csvStream = csv.format({headers: true, quoteColumns: true}),
    csvStream,
    dataName = "Cond"+condition+'Exp'+expNum+'_'+myYear+myMonth+myDate+myHour+myMin;

  // client が１人接続するたびに、以下の処理が始まる
io.on('connection', function (client) {
      //clientのamazonID
    client.amazonID = client.request._query.amazonID;
    if (typeof client.request._query.sessionName == 'undefined') {
        // client.sessionName: 初回の接続で発行されるIDを永続保持させる
      client.session = client.id;
      client.join(client.session);
      sessionNameSpace[client.session] = 1;
        // client.room: starting サインに従って、入るグループを割り振る
      if (roomList[Object.keys(roomList)[Object.keys(roomList).length-1]]['starting']===0) {
        client.room = Object.keys(roomList)[Object.keys(roomList).length-1];
      } else {
        roomList['subRoom'+condition+'_'+expNum+'_' + Object.keys(roomList).length] = {n:0, total_n:0, starting:0, restTime:maxWaitingTime, choiceTime:[], round:1, done:[], socialFreq:[[0, 0, 0]]};
        client.room = Object.keys(roomList)[Object.keys(roomList).length-1];
      }
      client.join(client.room);
        // increment of total subject number
      subjectCounter++;
      io.to(client.session).emit('S_to_C_clientSessionName', {sessionName: client.session, roomName: client.room});
        // room の人数を更新
      roomList[client.room]['n']++;
      roomList[client.room]['total_n']++;
      choiceCounter[client.session] = {}; // 各ラウンドに１回の選択しかできないように監視するためのもの
    } else if (client.request._query.sessionName == 'already_finished'){
      client.session = client.request._query.sessionName;
      client.room = 'finishedRoom';
      client.join(client.session);
      client.join(client.room);
    } else {
        // client came back!
      client.session = client.request._query.sessionName;
      client.room = client.request._query.roomName;
      client.join(client.session);
      client.join(client.room);
      io.to(client.session).emit('S_to_C_welcomeback', {sessionName: client.session, roomName: client.room});
      subjectCounter++;
      sessionNameSpace[client.session] == 1;
      var now = new Date(),
          logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
          logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
      console.log(logdate+' - '+ client.session +'('+client.amazonID+') in room '+client.room+' reconnected to the server');
      if (subjectCounter == 1) {
        dataName = dataName + '(' + client.id + ')';
      }
        // room の人数を更新
      if(typeof roomList[client.room]['n'] == 'undefined'){
        roomList[client.room] = {n:0, total_n:0, starting:0, restTime:maxWaitingTime, choiceTime:[], round:1, done:[], socialFreq:[[0, 0, 0]]};
        roomList[client.room]['n']++;
        roomList[client.room]['total_n']++;
      } else {
        roomList[client.room]['n']++;
        roomList[client.room]['total_n']++;
      }
    }


      // monitoring client's latency
    client.on('ping', function() {
      io.to(client.session).emit('pong');
    });



    var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
    console.log(logdate+' - '+ client.session +'('+client.amazonID+')'+' joined to '+ client.room +' (n: '+roomList[client.room]['n']+', total N: '+subjectCounter+')');

    if(client.room != 'finishedRoom')
    {
        // csvStream starts
      if (subjectCounter === 1){
        csvStream = csv.format({headers: true, quoteColumns: true});
        csvStream
              .pipe(fs.createWriteStream(path.resolve("./data", dataName+'_'+client.room+'.csv')))
              .on("end", process.exit);
      }
        // maxGroupSizeに到達するまで、待ての信号を送る
      if (roomList[client.room]['n'] < maxGroupSize_sub || (client.room == mainRoomName && roomList[client.room]['n'] < maxGroupSize)) {
          // 1人目の参加者が入った段階で、client.room の waiting stage 時計がスタートする
        if (roomList[client.room]['n']===1) {
          startWaitingStageClock(client.room);
        }
          // room の全員に、「待て」サインを送り直す
        //console.log(roomList[client.room]);
        io.to(client.room).emit('S_to_C_wait_for_starting', {room:roomList[client.room], maxG:maxGroupSize, maxG_sub:maxGroupSize_sub, roomName:client.room});
          // このclientに、残り待ち時間を送る
        io.to(client.session).emit('S_to_C_tellClientId', {id:client.session});
        io.to(client.room).emit('S_to_C_restTime', {restTime:roomList[client.room]['restTime'], max:maxWaitingTime});
      } else if (roomList[client.room]['starting'] == 0) {
        io.to(client.session).emit('S_to_C_tellClientId', {id:client.session});
        startSession(client.room);
      } else {
        // reconnected to the running server
      }
    }

      // 切断したときに送信
    client.on("disconnect", function () {
      client.leave(client.room);
      if (client.room != 'finishedRoom') {
        subjectCounter--;
      }
      sessionNameSpace[client.session] = 0;
      roomList[client.room]['n']--;
      var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
      console.log(logdate+' - client disconnected: '+ client.session+'('+client.amazonID+')'+' (room N: '+roomList[client.room]['n']+', total N: '+subjectCounter+') - handshakeID:'+ client.id);
      if (client.room != 'finishedRoom' && subjectCounter===0) {
        csvStream.end();
      }

    });

      // choiceResult を受け取った時の処理
    client.on('C_to_S_choiceResult', function (data) {

      if (typeof choiceCounter[client.session]['round: '+roomList[client.room]['round']] == 'undefined')
      {
        choiceCounter[client.session]['round: '+roomList[client.room]['round']] = 0;
      }
      //console.log(choiceCounter[client.session]);

      if (choiceCounter[client.session]['round: '+roomList[client.room]['round']] == 0)
      {
        choiceCounter[client.session]['round: '+roomList[client.room]['round']] += 1;
        var now = new Date(),
          logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
          logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
        console.log(logdate+' - Nomal choice: ('+client.room+':'+client.amazonID+') machine: '+ data.machine);

          // サーバー側でデータの保存
        if(roomList[client.room]['round'] < changingPoint) {
          switch (data.machine) {
            case 0:
              data.performance = performance_1[0];
              break;
            case 1:
              data.performance = performance_1[1];
              break;
            case 2:
              data.performance = performance_1[2];
              break;
            default:
              data.performance = 'something wrong';
              break;
          }
        } else {
          switch (data.machine) {
            case 0:
              data.performance = performance_2[0];
              break;
            case 1:
              data.performance = performance_2[1];
              break;
            case 2:
              data.performance = performance_2[2];
              break;
            default:
              data.performance = 'something wrong';
              break;
          }
        }
        data.condition = condition;
        data.expNum = expNum;
        data.amazonID = client.amazonID;
        data.room = client.room;
        data.round = roomList[client.room]['round'];
        data.socialFreq0 = roomList[client.room]['socialFreq'][data.round-1][0];
        data.socialFreq1 = roomList[client.room]['socialFreq'][data.round-1][1];
        data.socialFreq2 = roomList[client.room]['socialFreq'][data.round-1][2];
        if(subjectCounter > 0){
          csvStream.write(data);
        }

        //console.log(data);

          // 次のラウンドで表示する社会情報を更新する
        if (typeof roomList[client.room]['socialFreq'][data.round] !== 'undefined') {
          roomList[client.room]['socialFreq'][data.round][data.machine]++;
        } else {
          roomList[client.room]['socialFreq'][data.round] = [0, 0, 0];
          roomList[client.room]['socialFreq'][data.round][data.machine]++;
        }

          // 同じroomで既に選択済みの人数に応じて、クライアントへ返す反応を変える
        if (typeof roomList[client.room]['done'][roomList[client.room]['round']-1] !== 'undefined') {
              // すでのそのラウンドのdone項目が存在する場合、それに１足し、
              // if: まだ選択を受け付けていない参加者が居るのであれば、このclientには「待て」信号を送る。
              // else: 現在このroomに接続中の参加者全員から選択を受け付けた（i.e., 'done' == 'n'）ら、
              //       'round' を更新して次のラウンドへ進む
            roomList[client.room]['done'][roomList[client.room]['round']-1]++;
            if (roomList[client.room]['done'][roomList[client.room]['round']-1] < roomList[client.room]['n']) {
              io.to(client.session).emit('S_to_C_waitOthers', roomList[client.room] );
            } else {
              proceedRound(client.room);
            }
        } else {
              // まだそのラウンドの done 項目がなければ作る
              // if...else... 処理の内容は上と同じ
            roomList[client.room]['done'][roomList[client.room]['round']-1] = 1;
            if (roomList[client.room]['done'][roomList[client.room]['round']-1] < roomList[client.room]['n']) {
              io.to(client.session).emit('S_to_C_waitOthers', roomList[client.room] );
            } else {
              proceedRound(client.room);
            }
        }
      } else
      {
        choiceCounter[client.session]['round: '+roomList[client.room]['round']] += 1;
        var now = new Date(),
          logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
          logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
        console.log(logdate+' - Multiple Choice! ('+client.room+':'+client.amazonID+') machine: '+ data.machine+' payoff: '+data.result);
        io.to(client.session).emit('S_to_C_multipleChoice', {payoff: data.result});
      }
    });
    // -- C_to_S_choiceResult ここまで

    // choiceResult を受け取った時の処理
  client.on('C_to_S_timeupResult', function (data) {

    if (typeof choiceCounter[client.session]['round: '+roomList[client.room]['round']] == 'undefined')
    {
      choiceCounter[client.session]['round: '+roomList[client.room]['round']] = 0;
    }

    if (choiceCounter[client.session]['round: '+roomList[client.room]['round']] == 0)
    {
      choiceCounter[client.session]['round: '+roomList[client.room]['round']] += 1;
      var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
      console.log(logdate+' - Pink choice: ('+client.room+':'+client.amazonID+') machine: '+ data.machine);

        // サーバー側でデータの保存
      if(roomList[client.room]['round'] < changingPoint) {
        switch (data.machine) {
          case 0:
            data.performance = performance_1[0];
            break;
          case 1:
            data.performance = performance_1[1];
            break;
          case 2:
            data.performance = performance_1[2];
            break;
          default:
            data.performance = 'something wrong';
            break;
        }
      } else {
        switch (data.machine) {
          case 0:
            data.performance = performance_2[0];
            break;
          case 1:
            data.performance = performance_2[1];
            break;
          case 2:
            data.performance = performance_2[2];
            break;
          default:
            data.performance = 'something wrong';
            break;
        }
      }
      data.condition = condition;
      data.expNum = expNum;
      data.amazonID = client.amazonID;
      data.room = client.room;
      data.round = roomList[client.room]['round'];
      data.socialFreq0 = roomList[client.room]['socialFreq'][data.round-1][0];
      data.socialFreq1 = roomList[client.room]['socialFreq'][data.round-1][1];
      data.socialFreq2 = roomList[client.room]['socialFreq'][data.round-1][2];
      if(subjectCounter > 0){
        csvStream.write(data);
      }

      //console.log(data);

        // 次のラウンドで表示する社会情報を更新する
      if (typeof roomList[client.room]['socialFreq'][data.round] !== 'undefined') {
        roomList[client.room]['socialFreq'][data.round][data.machine]++;
      } else {
        roomList[client.room]['socialFreq'][data.round] = [0, 0, 0];
        roomList[client.room]['socialFreq'][data.round][data.machine]++;
      }
    } else
    {
      choiceCounter[client.session]['round: '+roomList[client.room]['round']] += 1;
      var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
      console.log(logdate+' - Multiple Choice! ('+client.room+':'+client.amazonID+') machine: '+ data.machine+' payoff: '+data.result);
      io.to(client.session).emit('S_to_C_multipleChoice', {payoff: data.result});
    }
  });
  // -- C_to_S_timeupResult ここまで


});


var port = process.env.PORT || portnum;
server.listen(port, function(){
  var now = new Date(),
      logtxt = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
      logtxt += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
      logtxt += ' - Multiplayer app listening on port '+ port;
  console.log(logtxt);
});


function startWaitingStageClock (room) {
    var now = new Date(),
        logtxt = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logtxt += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
        logtxt += ' - Waiting room opened at '+ room;
    console.log(logtxt);
    countDown(room);
}

function countDown(room) {
  roomList[room]['restTime'] -= 500;
  //console.log(roomList[room]['restTime']);
  /*console.log(Math.floor(Math.floor(roomList[room]['restTime']/1000)/60) +
                             ' : ' +Math.floor(roomList[room]['restTime']/1000)%60);*/
  if (roomList[room]['restTime'] < 0) {
    startSession(room);
    clearTimeout(countDownWaiting.room);
  } else {
    var room2 = room;
    countDownWaiting.room = setTimeout(function(){ countDown(room2) }, 500);
  }
}

function startSession (room) {
    clearTimeout(countDownWaiting.room);
    roomList[room]['starting'] = 1;
    io.to(room).emit('S_to_C_start_session', {value:roomList[room], choiceTime:maxChoiceTime, env:env_1, totalRound:totalRound});
    var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
    console.log(logdate+' - session started in '+room);
    startChoiceStage(room, 1);
    //setTimeout(function(){ startChoiceStage(room, 1) }, 500);
}

function startChoiceStage (room, round) {
    var now = new Date(),
        logdate = '['+now.getUTCFullYear()+'/'+(now.getUTCMonth()+1)+'/';
        logdate += now.getUTCDate()+'/'+now.getUTCHours()+':'+now.getUTCMinutes()+':'+now.getUTCSeconds()+']';
    console.log(logdate+' - start choice stage at '+round+' in '+room);
    countDownChoiceStage(room, round);
}

function countDownChoiceStage (room, round) {
  if (typeof roomList[room]['choiceTime'][round-1] !== 'undefined') {
      roomList[room]['choiceTime'][round-1] -= 500;
      //console.log('count down: '+room+' '+roomList[room]['choiceTime'][round-1]);
      if (roomList[room]['choiceTime'][round-1] < 0) {
        if (roomList[room]['n']>0){
          proceedRound(room);
        }
      } else {
        var room2 = room;
        var round2 = round;
        var clockName = '_'+room+'_';
        countDownChoice[clockName] = setTimeout(function(){ countDownChoiceStage(room2, round2) }, 500);
      }
  } else {
      if(round == 1){
        roomList[room]['choiceTime'][round-1] = maxChoiceTime+6500; //5,4,3,2,1カウントダウン用に最初だけ余裕がある
      }else{
        roomList[room]['choiceTime'][round-1] = maxChoiceTime;
      }
      var room2 = room;
      var round2 = round;
      var clockName = '_'+room+'_';
      countDownChoice[clockName] = setTimeout(function(){ countDownChoiceStage(room2, round2) }, 500);

  }
}

function proceedRound (room) {

  showResult(room);

  setTimeout(function() {

    //console.log(countDownChoice);
    var clockName = '_'+room+'_';
    clearTimeout(countDownChoice[clockName]);
    //console.log(countDownChoice);
    roomList[room]['round']++;
    //console.log('proceed to ' + roomList[room]['round']);

    if (typeof roomList[room]['socialFreq'][roomList[room]['round']-1] !== 'undefined') {
      if ( roomList[room]['round'] < changingPoint ){
        io.to(room).emit('S_to_C_proceed', {value:roomList[room], choiceTime:maxChoiceTime, env:env_1});
      } else {
        io.to(room).emit('S_to_C_proceed', {value:roomList[room], choiceTime:maxChoiceTime, env:env_2});
      }
    } else {
      roomList[room]['socialFreq'][roomList[room]['round']-1] = [0, 0, 0];
      if ( roomList[room]['round'] < changingPoint ){
        io.to(room).emit('S_to_C_proceed', {value:roomList[room], choiceTime:maxChoiceTime, env:env_1});
      } else {
        io.to(room).emit('S_to_C_proceed', {value:roomList[room], choiceTime:maxChoiceTime, env:env_2});
      }
    }

    if ( roomList[room]['round'] <= totalRound ) {
      startChoiceStage(room, roomList[room]['round']);
    }

  }, 2000);
}

function showResult (room) {
  io.to(room).emit('S_to_C_showResult', roomList[room]);
}
