var ENV = 'LOCAL';
var mraa, groveSensor, soundSensor, lightSensor, tempSensor, userInput;
var sensorInterval, sendInterval;
var envData = [];
var userData = [];
var fs = require('fs');
var api = require('./api.js');
var _SENSOR_INTERVAL, _SYNC_INTERVAL;

var CONFIG;

//TODO: make environment & user name dynamic
//TODO: add LCD
//TODO: check data with recorded data for warning of upcoming migraine, update LCD
//TODO: make data graph pretty


function init() {
	api.loadAPI(setupEnvironment);
}

function setupEnvironment() {
	CONFIG = CONFIG || api.config();

	if (!CONFIG.environment) {
		api.setConfigParam('setEnvironment', {'new':'new', 'name': 'Edison'}, function(envt){
			CONFIG.environment = envt;
			setupEnvironment();
		});
	} 
	else if (!CONFIG.user) {
		api.setConfigParam('setUser', {'new': 'new', 'name': 'User'}, function(user){
			CONFIG.user = user;
			setupEnvironment();
		});
	} else {
		setMachine();
	}	
}

function setMachine() {
	try { 
		mraa = require('mraa'); //require mraa
		groveSensor = require('jsupm_grove');
		ENV = 'EDISON';

	    _SENSOR_INTERVAL = 60 * 1000; //collect data every minute
	    _SYNC_INTERVAL = 60* 60 * 1000; //send results every hour

	    setupPins();
	} catch(e) {
	    console.error("You're not running this on the Edison");
	    CONFIG.ip = 'localhost';

	    _SENSOR_INTERVAL = 1000; //collect data every second
	    _SYNC_INTERVAL = 10 * 1000; //send results every 30sec
        sensorInterval = setInterval(getSensorsValue, _SENSOR_INTERVAL);
    	setTimeout(syncData, _SYNC_INTERVAL);
	}
}

function setupPins() {
    lightSensor = new groveSensor.GroveLight(0);
    soundSensor = new mraa.Aio(1);
    tempSensor = new groveSensor.GroveTemp(2);

    userInput = new groveSensor.GroveRotary(3);

    sensorInterval = setInterval(getSensorsValue, _SENSOR_INTERVAL);
    setTimeout(syncData, _SYNC_INTERVAL);
}

function getSensorsValue() {
    var sensorData = {};
    var inputData = {};

    if(ENV === 'LOCAL') {
        sensorData.sound = Math.round(Math.random()*100);
        sensorData.temp = Math.round(Math.random()*200);
        sensorData.light = Math.round(Math.random()*150);
        inputData.migraine = Math.round(Math.random()*1023);
    } else {
        sensorData.sound = soundSensor.read();
        sensorData.temp = tempSensor.value();
        sensorData.light = lightSensor.value();

        inputData.migraine = getPercentMigraine();
    }

    sensorData.timestamp = Math.round((new Date().getTime())/1000);
    inputData.timestamp = sensorData.timestamp;

    envData.push(sensorData);
    userData.push(inputData);
}

function getPercentMigraine(){
    var sensor_val = userInput.abs_value();

    return Math.round((sensor_val/1023)*100);
}

function syncData() {
	var data = envData;
	var data_user = userData;
	envData = [];
	userData = [];

	var data_sync = {
		"environment": {
			"token": CONFIG.environment,
			"data": data
		},
		"user": {
			"token": CONFIG.user,
			"environment": CONFIG.environment,
			"data": data_user
		}
	};

	api.sendData('sendData', JSON.stringify(data_sync), function(results){
		setTimeout(syncData, _SYNC_INTERVAL);
	});
}

init();