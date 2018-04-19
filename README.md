# MultiPlayerThreeArmedBanditGame
A browser-based collective decision-making and reinforcement-learning task (i.e. a multi-player 3-armed bandit task)

*Note:* To run the game, you will need to make a directory named node_modules at the same level of README and to put node.js modules, such as express (https://expressjs.com), socket.io (https://socket.io), and fast-csv (https://github.com/C2FO/fast-csv), into the directory. 

## Usage
### Server side
+ The game server program (app.js) is run by node.js + socket.io + express. 
+ You may want to change line:17 so that the game server can find your client-side web server. 
+ By changing value of _condition_ (line:11) you can modify the slot machines' allocation (e.g. when _condition_ = 1, the left, centre and right slot are the poor-excellent, poor and good option, respectively. 

### Client side
+ The behaviorual experiment starts from HIT_Screen.html. This is an advertisement page of our Amazon's Mechanical Turk based task.
+ You have to modify _serverName_ or _gameServer_ in HIT_Screen.html, public/tutorial.js, and public/mainPage.js
+ public/doui/ConsentForm.php connects with mySql database and checks if the client's workerID has already been registered (so as to prevent multiple accesses from the same person). 
