var
fs = require('fs'),
https = require('https'),
Agent = require('agentkeepalive').HttpsAgent,
WebSocketClient = require('websocket').client,
shell = require('child_process'),
net = require('net'),
ICINGA_AUTH = process.env['ICINGA_AUTH'],
ICINGA_HOST = process.env['ICINGA_HOST'],
WS_URL = process.env['WS_URL'],
SOCKETFILE = '/var/run/icingajs.sock',
SHUTDOWN = false;

var DEBUG = 1;
process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = 0;

if (!Math.round1) Math.round1 = function(value) {
	return Math.round(value*10)/10;
}
Date.prototype.toLog = function(o){
	var d = this;
	function f(v){return v.toString().replace(/^(.)$/,"0$1")};
	return d.getFullYear()+"-"+f(d.getMonth()+1)+"-"+f(d.getDate())+" "+f(d.getHours())+":"+f(d.getMinutes())+":"+f(d.getSeconds());
}

var 
last,
ws, counter = 0,
cache = [],
wscache = [],
table = {},
links = {},
us_conn = {},
members = {},
usServer,
usnum = 0,
updates = 0,
pupdates = 0,

keepaliveAgent = new Agent({
  maxSockets: 100,
  maxFreeSockets: 10,
  freeSocketTimeout: 30000, // free socket keepalive for 30 seconds
}),

opt4long = {
	host: ICINGA_HOST,
	port: 5665,
	auth: ICINGA_AUTH,
	path: '/v1/events?queue=cr&types=CheckResult',
	method: 'POST',
	agent: keepaliveAgent,
	headers: { 'Accept': 'application/json' }
},
opt4post = {
	host: ICINGA_HOST,
	port: 5665,
	auth: ICINGA_AUTH,
	path: '/v1/objects/services?pretty=1',
	method: 'POST',
	headers: {
		'Accept': 'application/json',
		'X-HTTP-Method-Override': 'GET'
	}
}

var parseMessage = function(m){
	var n;
	try{ if(typeof m == 'string') m = JSON.parse(m.replace(/\n/g,'\\n').replace(/\r/g,'\\r').replace(/\t/g,'\\t')) }catch(e){ 
		console.log((new Date()).toLog()+' ошибка JSON: ',e);
		m = {wstype:'notify',sender:{login:'icinga',fio:'monitoring Icinga'},message:m.toString()}
	}
	if(!m.wstype) m.wstype = 'notify';
	if(!m.sender || typeof m.sender != 'object') m.sender = {login:'icinga',fio:'monitoring Icinga'};
	if(m.sender && m.sender.login && m.sender.login=='icinga'){
		m.message = '<img src="pic/icinga-logo.png" style=\"width:40px;height:40px;float:left;margin:3px">'+m.message
	}
	return m;
}

var checkValue = function(val,ex,pd){
	var m = val.match(/^([^ ]+) ?([^ ]+)? \- ([^ ]+).*/);
	if(!m){console.log((new Date()).toLog()+' checkValue: ошибка парсинга val='+val+' ex='+ex); return;}
	if(m[3]) m[3] = m[3].replace(/[^\-0-9.]/g,"") * 1;
	if(m[1]=='SNMP' || m[3] === "") v = ex;
	else if(m[3]<0||m[3]>3) v = Math.round1(m[3]);
	else v = m[3];
	return v;
}

var mkOutData = function(msg){
	var s = "", i, n = msg.length;
	for(i=0;i<4;i++) s += String.fromCharCode(n>>((3-i)*8)&0xFF);
	return s+msg;
}

function createUnixSocketServer(socket){
	if(fs.existsSync(SOCKETFILE)){
		fs.unlink(SOCKETFILE, function(err){
			if(err){ 
				console.error(err);
				process.exit(0);
			}
		});  
	}
    console.log((new Date()).toLog()+' Creating Unix Socket server.');
    var server = net.createServer(function(stream) {
		stream.___client = ++usnum;
		stream.__dataIn = "";

        var self = Date.now();
        us_conn[usnum] = (stream);
        stream.on('end', function() {
            delete us_conn[this.___client];
			m = parseMessage(stream.__dataIn);
            console.log((new Date()).toLog()+' recive '+this.___client+' from Unix Socket: '+stream.__dataIn.replace(/\n/g," "));
			if(stream.__inputlen>0) sendMessage(m);
        });

        stream.on('data', function(msg) {
			var line;
			if(!this.__dataIn){
				this.__datalength = msg[0]<<24|msg[1]<<16|msg[2]<<8|msg[3];
				this.__inputlen = msg.length - 4;
				this.__dataIn = msg.toString().substr(4);
			}else{
				this.__inputlen += msg.length;
				this.__dataIn += msg.toString();
			}
			if(this.__inputlen >= this.__datalength)
				this.write(mkOutData('OK'));
        });
    })
    .listen(socket)
    .on('connection', function(socket){
		this.__dataIn = "";
    })
	.on('listening', function(){
		shell.exec('chown nagios:nagios '+SOCKETFILE+' && chmod g+rw '+SOCKETFILE);
	});
    return server;
}

function cleanup(){
	if(!SHUTDOWN){
		SHUTDOWN = true;
		console.log('\nTerminating\n');
		if(Object.keys(us_conn).length){
			var clients = Object.keys(us_conn);
			while(clients.length){
				var client = clients.pop();
				us_conn[client].write('__disconnect');
				us_conn[client].end(); 
			}
		}
		usServer.close();
		process.exit(0);
	}
}
process.on('SIGINT', cleanup);

function postRequest(callback){
	var post_data = '{"filter":"match(\\"id*\\",service.name) && match(\\"id*\\",host.name)"}';
	var buff = "", len = 0, full = 0;
	opt4post['headers']['Content-Length'] = post_data.length;
	var req = https.request(opt4post, function(res) {
		console.log((new Date()).toLog()+' postRequest STATUS: ' + res.statusCode);
		console.log((new Date()).toLog()+' postRequest HEADERS: ' + JSON.stringify(res.headers));
		if(res.headers['content-length']) full = res.headers['content-length'];
		res.setEncoding('utf8');
		res.on('data', function (chunk) {
			len += chunk.length;
			buff += chunk;
			if(len >= full){
				if(DEBUG>0) console.log((new Date()).toLog()+' postRequest: all data recive '+len+'b');
				if(typeof callback == 'function') callback(buff);
				req.end();
			}
		});
	});
	req.on('error', function(e) {
		console.log('problem with request: ' + e.message);
	});
	req.write(post_data);
}

function refreshData(report){
	postRequest(function(buff){
		var data;
		try{
			data = JSON.parse(buff);
		}catch(e){
			console.log("data json error!");
			data = "";
		}
		if(data && data.results){
			var d = data.results, service, i, len, val, msg, s, r, login, fact=0;
			table = {};
			for(i=0,len=d.length;i<len;i++){
				try{
					service = d[i].attrs.name.replace(/^id/,'');
					r = d[i].attrs.last_check_result;
					val = checkValue(r.output,r.exit_status,r.performance_data);
				}catch(e){
					console.log((new Date()).toLog()+" error ",e.toString());
					val = '';
				}
				if(val !== ''){ table[service] = val; fact++; }
			}
			console.log((new Date()).toLog()+' postRequest: read '+len+' objects from Icinga');
			msg = {wstype:"update",to:'all',state:{}};
			for(s in table){ msg.state[s] = table[s]; }
			sendMessage(msg);
			if(Object.keys(members).length){
				login = Object.keys(members)[0];
				sendMessage({wstype:'getlinks',to:login});
			}
			if(typeof report == 'function') report(len, fact);
		}
	});
}

var client = new WebSocketClient();
client.on('connectFailed', function(error) {
	console.log((new Date()).toLog()+' websocket Connect: ',error);
	setTimeout(function(){ wsConnect(); },1000);
});

client.on('connect', function(conn) {
	console.log((new Date()).toLog()+' websocket Client Connected');
	ws = conn;
	ws.on('error', function(error) {
		console.log((new Date()).toLog()+" websocket Connection Error: " + error.toString());
	});
	ws.on('close', function() {
		ws = undefined;
		console.log((new Date()).toLog()+' websocket echo-protocol Connection Closed');
		setTimeout(function(){ wsConnect(); },1000);
	});
	ws.on('message', function(data) {
		if (data.type === 'utf8') {
			var m, d, s, msg, c, i, st, n, l, msgto, level;
			try{ m = JSON.parse(data.utf8Data); }catch(e){
				console.log((new Date()).toLog()+" websocket Received bad data: '" + data.utf8Data + "'");
				m = "";
			}
			if(m.wstype in {message:1,getstate:1} || DEBUG>1)
				console.log((new Date()).toLog()+" websocket Received: "+data.utf8Data);
			if(typeof m == 'object' && m.wstype){
				if(m.wstype == 'members'){
					if(m.add) Object.keys(m.add).forEach(function(el){members[el]=m.add[el]});
					if(m.del) for(i in m.del) if(members[i]) delete members[i];
				}
				if((m.wstype == 'setlinks' || m.wstype == 'getstate') && m.sender && m.sender.login && m.links){
					l = 0; d = (m.wstype == 'setlinks')? {} : false;
					for(i in m.links){
						s = m.links[i];
						if(links[s] === undefined) links[s] = [];
						if(links[s].indexOf(i) == -1){ links[s].push(i); l++;}
						if(d && s in table) d[i] = table[s];
					}
					if(l) console.log((new Date()).toLog()+' added '+l+' links');
					if(d) sendMessage({wstype:"update",to:'all',state:d});
				}
				if(m.wstype == 'unsetlinks' && m.sender && m.sender.login && m.links){
					l = 0;
					for(i in m.links){
						if(links[m.links[i]] !== undefined && (n = links[m.links[i]].indexOf(i)) != -1){
							links[m.links[i]].splice(n,1); l++;
						}
						if(links[m.links[i]] && links[m.links[i]].length == 0) delete links[m.links[i]];
					}
					if(l) console.log((new Date()).toLog()+' unset '+l+' links');
				}
				if(m.wstype == 'getstate' && m.sender && m.sender.login){
					msg = {wstype:"update",to:m.sender.login,state:{}};
					if(m.objects) for(s in m.objects){
						if((st = table[s]) !== undefined){
							msg.state[s] = st;
							if(links[s] !== undefined) for(i in links[s]) msg.state[links[s][i]] = st;
						}
					}else for(s in table){
						st = table[s]; msg.state[s] = st;
						if(links[s] !== undefined) for(i in links[s]) msg.state[links[s][i]] = st;
					}
					sendMessage(msg);
				}
				try{ level = members[m.sender.login].level; }catch(e){ level = 0; }
				if(m.wstype == 'message' && m.message && m.sender && m.sender.login && level>4){
					msg = {wstype:"message",to:m.sender.login,message:""};
					sendMessage({wstype:'delivery',id:m.id,result:'OK'});
					if(c = m.message.match(/^\s*getvalue\s+([0-9]+)$/)){
						if(table[m[1]] !== '') msg.message = "table["+c[1]+"] = "+table[c[1]];
						else msg.message = "table["+c[1]+"] = not exists";
					}
					if(c = m.message.match(/^\s*setvalue\s+([0-9]+)\s*=\s*([0-9]+)$/)){
						if(table[m[1]] !== undefined){
							table[m[1]] = m[2];
							msg.message = "table["+c[1]+"] = set to "+c[2];
						}else msg.message = "table["+c[1]+"] not exists!";
						
					}
					if(c = m.message.match(/^\s*getlink\s+([0-9]+)$/)){
						if(links[c[1]] !== undefined) msg.message="links["+c[1]+"] = ["+links[c[1]].join(', ')+"]";
						else msg.message = "links["+c[1]+"] = not exists";
					}
					if(c = m.message.match(/^\s*getlinks$/)){
						for(i in links) msg.message+="links["+i+"] = ["+links[i].join(', ')+"]<br>";
					}
					if(c = m.message.match(/^\s*refresh\s*data/)){
						refreshData(function(l,f){
							sendMessage({wstype:"message",to:m.sender.login,message:"length = "+l+"\nfact = "+f});
						});
					}
					if(c = m.message.match(/^\s*getmembers/)){
						Object.keys(members).forEach(function(el){msg.message+=el+"\n"});
					}
					if(c = m.message.match(/^\s*setlink\s+([0-9]+)\s*=\s*([0-9]+)$/)){
						if(links[m[2]] === undefined) links[m[2]] = [m[1]];
						else links[m[2]].push(m[1]);
						msg.message = "links["+c[1]+"] = ["+links[c[1]].join(', ')+"]";
					}
					if(msg.message && !msgto) msgto = setTimeout(function(){
						msgto = undefined;
						sendMessage(msg);
					},1000);
				}
			}
		}
	});
});

function wsConnect() {
	console.log((new Date()).toLog()+' websocket connect to '+WS_URL);
	client.connect(WS_URL, 'echo-protocol');
}

function longRequest(){
	const req = https.request(opt4long, function(res) {
		var buff, counter = 0;
		console.log((new Date()).toLog()+' longRequest STATUS: ' + res.statusCode);
		console.log((new Date()).toLog()+' longRequest HEADERS: ' + JSON.stringify(res.headers));
		res.setEncoding('utf8');
		res.on('data', function (chunk) {
			var s,v, object, out={}, val, chk, r;
			try{
				if(buff) chunk = buff+chunk;
				s = JSON.parse(chunk);
				buff = '';
				counter = 0;
			}catch(e){
				buff = chunk;
				counter++;
			}
			if(!buff && typeof s == 'object'){
				if((m = s.host.match(/^id([0-9]+)\.(.*)/)) && s.service && (ms = s.service.match(/^id([0-9]+)/))){
					object = ms[1], r = s.check_result;
					val = checkValue(r.output,r.exit_status,r.performance_data);
					if(table[object] === undefined) table[object] = "";
					chk = table[object] !== val;
					if(DEBUG>2) console.log((new Date()).toLog()+'  '+s.host+'!'+s.service+'  '+s.check_result.output+' '+(chk?' +++':''));
					if(chk){
						table[object] = val;
						out[object] = val;
						wscache.push(out);
					}
				}
			}
		});
	});
	req.on('error', function(e) {
		console.log((new Date()).toLog()+' longRequest problem: ' + e.message);
		longRequest();
	});
	req.end();
}

function sendMessage(msg) {
	var old, len=0;
	if(msg.state && typeof msg.state == 'object') len = Object.keys(msg.state).length;
	if(msg.wstype == 'update'){
		counter++;
		msg.id = counter;
		msg.timestamp = Date.now();
	}
	m = JSON.stringify(msg);
	if(ws && ws.connected) {
		while(cache.length > 0){
			old=cache.pop();
			ws.sendUTF(old);
			if(DEBUG>0) console.log((new Date()).toLog()+' websocket send cached message: '+((old.length>100)? old.substring(0,100)+" ..." : old));
			else console.log((new Date()).toLog()+'websocket sennd '+cache.length+' cached message');
		}
		ws.sendUTF(m);
		if(DEBUG>1 || len>16) console.log((new Date()).toLog()+' websocket send: '+((len>16)? "to "+msg.to+" "+len+" objects" : m));
		if(DEBUG>0 && msg.wstype=='message') console.log((new Date()).toLog()+' websocket send: '+((len>16)? "to "+msg.to+" "+len+" objects" : m));
	}else{
		if(cache.length < 1000){
			cache.push(m);
			if(DEBUG>0) console.log((new Date()).toLog()+' message cached: '+((len>16)? len+" objects" : m));
		}else
			console.log('websocket cache crowded !!!');
	}
}

refreshData();
usServer = createUnixSocketServer(SOCKETFILE);
wsConnect();
longRequest();

setInterval(function() {
	var c = updates - pupdates;
	console.log((new Date()).toLog()+' sent '+c+' updates in the last minute');
	pupdates = updates;
},60000);

setInterval(function() {
	if (keepaliveAgent.statusChanged) {
		if(keepaliveAgent.getCurrentStatus().createSocketCount > 0){
			console.log((new Date()).toLog()+' keepAlive: Sockets'+
				' create:'+keepaliveAgent.createSocketCount+
				' close:'+keepaliveAgent.closeSocketCount);
			if(keepaliveAgent.createSocketCount == keepaliveAgent.closeSocketCount && !last){
				last = setTimeout(function(){
					last = undefined;
					refreshData();
					longRequest();
				},5000)
			}
		}
	}
	if(wscache.length>0){
		var msg = {wstype:"update",to:'all',state:{}}, obj, k;
		updates += wscache.length;
		while(wscache.length>0){
			obj = wscache.shift();
			for(k in obj) msg.state[k] = obj[k];
		}
		sendMessage(msg);
	}
}, 2000);
