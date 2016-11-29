var fs = require('fs');
var http = require('http');
var os = require('os');
var getmac = require('getmac');
var md5 = require('md5-node');

var conf;

var _API;

function loadAPI(callback) {
	fs.readFile('endpoints.json', 'utf8',  function(err, res){
		if(err) throw err
		else {
			_API = JSON.parse(res);
			loadConfig(callback)
		}
	});
}

function loadConfig(callback) {
	fs.readFile('config.json', 'utf8',  function(err, res){
		if(err && err.errno === -2) {
			setConfig(callback);
		}

		else {
			conf = JSON.parse(res);
			callback();
		}
	});
}

function setConfig(callback) {
	var ip = getDeviceIP(); 
	var options = {};
	options.ip = ip;

	var token = generateToken(function(token){
		options.token = token;

		var secret = callEndpoint(_API.endpoints.getSecret, {"token":token}, function(secret){

			options.secret = secret;


			var apiToken = callEndpoint(_API.endpoints.getApiToken, {"token": token, "secret": secret}, function(apitoken){	
				options.apiToken = apitoken;

				console.log(options);
				
				fs.writeFile('config.json', JSON.stringify(options), function(err){
					if(err) throw err;

					loadConfig(callback);
				});
			});
		});	
	});
}

function setConfigParam(param, params, callback) {
	callEndpoint(_API.endpoints[param], params, function(result){
		switch(param) {
			case 'setEnvironment':
				conf.envName = params.name;
				conf.environment = result;
			break;

			case 'setUser':
				conf.userName = param.name;
				conf.user = result;
			break;
		}

		fs.unlink('config.json', function(){
			fs.writeFile('config.json', JSON.stringify(conf), function(err){
				if(err) throw err;
				
				callback(result);
			});
		})
	});
}

function sendData(param, params, callback) {
	callEndpoint(_API.endpoints[param], params, callback);
}

function callEndpoint(endpoint, params, callback) {
	var path = endpoint.path;
	var callParams = [];
	var headers = {'Content-Type': 'application/json'};

	if(endpoint.token) {
		callParams.push("token=" + params.token);
	}

	if(endpoint.secret) {
		callParams.push("secret="+params.secret);
	}

	if(endpoint.apiToken) {
		headers['Auth'] = 'Bearer '+ conf.apiToken;

		if(endpoint.method === 'GET') {
			for (var param in params) {
		        if(!params.hasOwnProperty(param)) continue;
		        
		        callParams.push(param +'='+params[param]);
		    }
		} else {
			//bit hacky, but required for post
			callParams.push('new=new');
		}
	}

	if(callParams.length > 0) {
		path += '?' + callParams.join('&');
	}

	var options = {
		host: _API.serverIP,
		path: path,
		method: endpoint.method,
		headers: headers
	};
	var req = http.request(options, function(response) {
        // console.log('STATUS CODE: ' + response.statusCode);
        // console.log('HEADERS: ' + JSON.stringify(response.headers));
        var body = '';
        var result = null;

        response.on('data', function(d) {
            body += d;
        });

        response.on('end', function() {
        	if(endpoint.resultParam === null) {
        		result = result = JSON.parse(body).description;
        	} else {
        		result = JSON.parse(body).result[endpoint.resultParam];
        	}
        	
        	callback(result);
        });
    });

	if(endpoint.method === 'POST') {
		req.write(params);
	}

    req.end();
}

function generateToken(callback) {
	var ts = new Date().getTime();
	var mac = getmac.getMac(function(err, macAddress){
		if(err) throw err;
		
		callback(md5(macAddress.toString + ts));
	});
}

function getDeviceIP() {
	var net = os.networkInterfaces();
	var addresses = [];

	Object.keys(net).forEach(function (ifname) {
	  var alias = 0;

	  net[ifname].forEach(function (iface) {
	    if ('IPv4' !== iface.family || iface.internal !== false) {
	      // skip over internal (i.e. 127.0.0.1) and non-ipv4 addresses
	      return;
	    }

	    if (alias >= 1) {
	      // this single interface has multiple ipv4 addresses
	      addresses[alias] = iface.address
	    } else {
	      addresses[0] = iface.address;
	    }
	    ++alias;
	  });
	});

	return addresses[0];
}

function getConfig() {
	return conf;
}


module.exports = {
	loadAPI: loadAPI,
	config: getConfig,
	setConfigParam: setConfigParam,
	sendData: sendData
}