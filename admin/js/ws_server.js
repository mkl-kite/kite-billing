/* исполняемый файл для nodejs реализует WebSocket чат и передачу сообщений через unix socket */
var
https = require('https'),
WebSocketServer = require('ws'),
fs = require('fs'),
PHPUnserialize = require('php-unserialize'),
shell = require('child_process');
net = require('net'),
mysql = require('mysql');

var
SHUTDOWN = false,
SOCKETFILE = '/var/run/ws_server.sock',
SQLADMPASS = process.env['SQLADMPASS'],
DBKEY = {connectionLimit:5,host:'localhost',user:'sqladm',password:SQLADMPASS,database:'radius'},
SSL_KEY = process.env['SSL_KEY'],
SSL_CERT = process.env['SSL_CERT'],
PHP_SESSIONS_DIR = process.env['PHP_SESSIONS_DIR'];

var
port = 1380, ip, nolog = {}, delay_m = {},
us_conn = {},
connections = {},
members = {},
db = {one:function(){},get:function(){},set:function(){},ins:function(){},upd:function(){}},
directConnect = JSON.parse(process.env['WS_DIRECT']),
server = null, usServer = null, wsServer = null,
usnum = 0, num=0, mstatus=['cached','received','delivered','confirmed'];

Date.prototype.toLog = function(o){
	var d = this;
	function f(v){return v.toString().replace(/^(.)$/,"0$1")};
	return d.getFullYear()+"-"+f(d.getMonth()+1)+"-"+f(d.getDate())+" "+f(d.getHours())+":"+f(d.getMinutes())+":"+f(d.getSeconds());
}

var shortFIO = function(s) {
	var f = s.split(/\s+/);
	if(f[1]) f[1] = f[1].charAt(0)+'.';
	if(f[2]) f[1] += f[2].charAt(0)+'.';
	if(f.length>2) f = f.slice(0,2);
	return f.join(' ');
}

var wslog = function(msg){ console.log((new Date()).toLog()+"  "+msg); }

var allowConnect = function(url){
	if(!url || typeof url != 'string') return false;
	var s = url.split(/\?/);
	if(s.length < 2) return false;
	var aaa = s[1].split(/&/), l, i, d, r={};
	for(i=0,l=aaa.length; i<l; i++) { d = aaa[i].split(/=/); r[d[0]] = d[1]; }
	if(!r.login || !r.password) return false;
	if(!directConnect[r.login] || directConnect[r.login] != r.password) return false;
	return r.login;
}

var wsend = function(conn,m){
	if(!conn || !m) return false;
	var data, sended;
	data = JSON.stringify(m);
	conn.forEach(function(sess,i){
		try{
			sess.send(data);
			sended = true;
			wsServer.__counter++;
		}catch(e){
			if(sended === undefined) sended = false;
		}
	})
	if(!sended){
		console.log((new Date()).toLog()+'  send ERROR: \n',m,e);
		if(m.id) db.set(m.id,2);
		return false;
	}
	if(conn[0] && conn[0].__user){
		var s = t = id = res = msg = "";
		if(typeof m == 'object'){
			sid = (conn[0]._id)? " SocketID:"+conn[0]._id : "";
			s = (typeof m.sender == 'object' && m.sender.login)? " from '"+m.sender.login+"'":((m.sender)?" from '"+m.sender+"'":"");
			lm = ('add' in m)? " add ("+Object.keys(m.add).join(",")+")" : (('del' in m)? " del ("+Object.keys(m.del).join(",")+")" : "");
			id = ('id' in m)? " (id:"+m.id+")" : "";
			res = ('result' in m)? " result: "+m.result : "";
			msg = (m.message)? " '"+m.message+"'" : "";
		}
		wslog("send "+m.wstype+lm+sid+id+s+" to '"+conn[0].__user.login+"' ("+data.length+"b)"+res+msg);
	}
	m.status = 1;
	return true;
}

var	send_all = function(m,usr,onSend){
	var login;
	if(!m || typeof m != 'object') return false;
	wsServer.__counter++;
	for(login in connections){
		if(usr && usr == login) continue
		var msg = JSON.parse(JSON.stringify(m));
		msg.to = login;
		if(m.wstype == 'message') db.ins(msg,function(r){ wsend(connections[r.to],r); })
		else wsend(connections[login],msg);
	}
	if(onSend && typeof onSend == 'function') onSend(m)
}

var parseMessage = function(m, sender){
	var n;
	try{ if(typeof m == 'string') m = JSON.parse(m) }catch(e){ 
		m = {sender:(sender?sender:""),message:m.toString()}
	}
	if(!m.wstype) m.wstype = 'message';
	if(!m.sender && sender) m.sender = sender;
	if(m.sender && typeof m.sender == 'string'){
		if(connections[m.sender][0]) m.sender = connections[m.sender][0].__user;
		else if(members[m.sender]) m.sender = members[m.sender];
	}
	return m;
}

var getMembers = function(login){
	var lst = {};
	Object.keys(connections).forEach(function(el){
		if(!login || el != login) lst[el] = connections[el][0].__user;
	});
	return lst;
}

var delaySendMembers = function(login){
	if(!login || delay_m[login]) return;
	delay_m[login] = setTimeout(function(){
		delete delay_m[login];
		var m = {wstype:"members",del:{}};
		m.del[login] = members[login];
		if(Object.keys(m.del).length>0) send_all(m,login);
	},10000)
}

var mkOutData = function(msg){
	var s = "", i, n = msg.length;
	for(i=0;i<4;i++) s += String.fromCharCode(n>>((3-i)*8)&0xFF);
	return s+msg;
}

function checkMembers(){
	var login, a=[];
	for(login in connections){
		connections[login].forEach(function(s,i){
			if(s.__session != 'direct' && !fs.existsSync(PHP_SESSIONS_DIR+'/sess_'+s.__session)){
				a.push(s);
			}
		})
	}
	if(a.length>0){
		wsend(a,{wstype:"logout"});
		setTimeout(function(e){a.forEach(function(s,i){s.close()})},1000);
	}
	wslog('members checked');
}

function updateMembers(pool){
	var q = pool.query('SELECT unique_id as id, login, fio, status as level, photo FROM operators WHERE blocked=0 AND status>2');
	q.on('result',function(row){
		if('fio' in row) row.fio = shortFIO(row.fio);
		if('photo' in row && row.photo) row.photo = "photo.php?id="+row.photo;
		members[row.login] = row;
	});
	wslog('members updated');
}

function createDBConnection(key){
	var myPool = mysql.createPool(key);
	myPool.on('end',function(){wslog("db connection is close")});
	updateMembers(myPool);
	return {
		one: function(id, handler){
			if(!id) return;
			var q = myPool.query("SELECT * FROM messages WHERE id=? LIMIT 1",id);
			q.on('result',function(row){
				if(handler && typeof handler == 'function') handler(row);
			})
			q.on('error',function(err){ console.log(err); });
		},
		get: function(login, handler){
			if(!login) return;
			var q = myPool.query('SELECT * FROM messages WHERE status<3 AND `to`=? ORDER BY send',login);
			q.on('result',function(row){
				row.sender = (members[row.sender])? members[row.sender] : {login:row.sender};
				if(row.send) row.send = row.send.toLog();
				if(handler && typeof handler == 'function') handler(row);
			})
			q.on('error',function(err){ console.log(err); });
		},
		set: function(id,status,handler){
			if(!id) return false;
			var s = (status)? status : 3,
			q = myPool.query("UPDATE messages SET `status`='"+status+"' WHERE `id`='"+id+"'",function(err,res,fld){
				if(err) throw err;
				if(handler && typeof handler == 'function') handler(res.changedRows);
			})
		},
		ins: function(q, handler){
			var r = false, cl = {}, bk, f=["id","status","send","recive","sender","to","message","guid"];
			if(!q.wstype || q.wstype != 'message' || !q.to || q.to == 'all') return false;
			q.send = (new Date()).toLog();
			bk = JSON.stringify(q);
			for(n in q){
				if(f.indexOf(n) == -1) continue;
				if(typeof q[n] != 'object') cl[n] = q[n];
				else if(n == 'sender' && 'login' in q[n]) cl[n] = q[n].login;
			}
			for(n in {sender:1,to:1,message:1}) if(cl[n] === undefined){
				wslog("db.ins: mrecord["+n+"] is undefined");
				return false;
			}
			try{
				myPool.query("INSERT INTO messages SET ?", cl, function(err,result,fields){
					if(err) throw err;
					wslog("message '"+cl.message+"' id: "+result.insertId+" from "+cl.sender+" to "+cl.to+ " saved in DB");
					bk = JSON.parse(bk);
					bk.id = result.insertId;
					if(handler && typeof handler == 'function') handler(bk);
				})
			}catch(e){
				console.log(e);
				return false;
			}
			return true;
		},
		upd: function(){updateMembers(myPool)},
		pool: myPool
	}
}
db = createDBConnection(DBKEY);

function createUnixSocketServer(socket){
    wslog('Creating Unix Socket server.');
    var server = net.createServer(function(stream) {
		stream.___client = ++usnum;
		stream.__dataIn = "";

        var self = Date.now();
        us_conn[usnum] = (stream);
        stream.on('end', function() {
            delete us_conn[this.___client];
			m = parseMessage(stream.__dataIn);
            wslog('recive '+this.___client+' from Unix Socket: '+JSON.stringify(m));
			if(stream.__inputlen>0){
				var u = (m.sender && m.sender.login)? m.sender.login : "";
				if(m.to){
					if(m.wstype && m.wstype=='message') db.ins(m,function(r){
						if(connections[r.to]) wsend(connections[r.to],r);
					}); else if(connections[m.to]) wsend(connections[m.to],m);
				}else send_all(m,u);
			}
			wsServer.__counter++;
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
		shell.exec('chown root:www-data '+SOCKETFILE+' && chmod g+rw '+SOCKETFILE);
	});
    return server;
}

function cleanup(){
	if(!SHUTDOWN){
		SHUTDOWN = true;
		wslog('\nTerminating\n');
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

if(fs.existsSync(SOCKETFILE)){
	fs.unlink(SOCKETFILE, function(err){
		if(err){
			console.error(err);
			process.exit(0);
		}
	});  
}
usServer = createUnixSocketServer(SOCKETFILE);

var processRequest = function( req, res ) {
	wslog('HTTP server. URL'+req.url+" requested.\n"+JSON.stringify(req.headers));
	res.writeHead(200, { 'Content-Type': 'application/json' });
	res.end("All glory to WebSockets!\n");
};

server = https.createServer({
	key: fs.readFileSync(SSL_KEY, 'utf8'),
	cert: fs.readFileSync(SSL_CERT, 'utf8'),
	rejectUnauthorized: false,
}, processRequest ).listen(port);

server.on('connection', function(sock) {
	if(!sock.remoteAddress){
		wslog("https server socket ERROR no remoteAddress");
		return;
	}
	sock.sess_ip = sock.remoteAddress.replace(/.*:/,'');
});

var wsServer = new WebSocketServer.Server( { server: server, rejectUnauthorized: false } );
wsServer.__counter = 0;

wsServer.on('connection', function(ws, req) {
	ws._id = num++;
	var ip = req.connection.remoteAddress.replace(/.*:/,''), d=new Date(), s,
	cookie = (req.headers.cookie)? req.headers.cookie : '',
	phpid = cookie.match(/PHPSESSID=([0-9a-z]*)/), session = (phpid)? phpid[1]:"none",
	sessfile = PHP_SESSIONS_DIR+"/sess_" + session;

	for(s in nolog) if(d - nolog[s] > 120000) delete nolog[session];
	if(!fs.existsSync(sessfile)){
		var directUser = allowConnect(req.url);
		if(!directUser){
			if(!nolog[session]) wslog('no session file! ip: '+ip+' ('+sessfile.replace(/.*\//,"")+')  Send logout.');
			nolog[session]=d;
			wsend([ws],{wstype:"logout"});
			ws.close();
			return false;
		}
		userdata = {sess_ip:ip,sess_opdata:{unique_id:0,login:directUser,fio:directUser,level:1}};
		session = 'direct';
	}else{
		userdata = PHPUnserialize.unserializeSession(fs.readFileSync(sessfile).toString());
	}

	if(!userdata){
		if(!nolog[session]) wslog('no userdata! ip: '+ip+' in ('+sessfile.replace(/.*\//,"")+') Send logout.');
		nolog[session]=d;
		wsend([ws],{wstype:"logout"});
		ws.close();
		return false;
	}
	if(userdata.sess_ip != ip){
		if(!nolog[session]) wslog('user_ip ('+ip+') != session_ip ('+userdata.sess_ip+')  Send logout.');
		nolog[session]=d;
		wsend([ws],{wstype:"logout"});
		ws.close();
		return false;
	}
	if(!userdata.sess_opdata){
		if(!nolog[session]) wslog('user_ip ('+ip+') no operators data. Connection close.');
		nolog[session]=d;
		wsend([ws],{wstype:'logout'});
		ws.close();
		return false;
	}

	try{
		ws.__user = {
			id:userdata.sess_opdata.unique_id,
			photo:((userdata.sess_opdata.photo)?'photo.php?id='+userdata.sess_opdata.photo:"pic/unknown.png"),
			login:userdata.sess_opdata.login,
			fio:shortFIO(userdata.sess_opdata.fio),
			level:userdata.sess_opdata.level
		};
		ws.__session = session;
		ws.__ip = ip;
	}catch(e){
		console.log("ERROR userdata: ",userdata);
		ws.close();
		return false;
	}

	if(nolog[session]) delete nolog[session];
	if(connections[ws.__user.login] === undefined) connections[ws.__user.login] = [];
	connections[ws.__user.login].push(ws);
	if(!members[ws.__user.login]) members[ws.__user.login] = ws.__user;
	wslog('Connect: '+ws.__user.login+' SocketID:'+ws._id+' ('+ip+') session: '+session);

	ws.on('message', function (msg) {
		var m = parseMessage(msg, this.__user), self = this, r, qr;
		wslog('recive '+m.wstype+((m.id)?' (id:'+m.id+")":"")+' from '+this.__user.login+((m.result)?' '+m.result:"")+(((m.to)?' to '+m.to:""))+((m.message)?': '+m.message:""));
		if(!m.wstype){
			console.log(m);
			return;
		}
		if(m.wstype == 'delivery' && m.id){ // пришло подтверждение получателя о получении сообщения
			db.set(m.id,3, function(c){
				db.one(m.id, function(r){
					if(connections[r.sender])
						wsend(connections[r.sender],{wstype:"delivery",id:r.id,result:mstatus[3]}); // уведомление отправителя об успешной доставке
				});
			});
		}else if(m.wstype == 'members' && 'get' in m){
			wsend([this],{wstype:"members",add:getMembers(this.__user.login)});
		}else if(m.to && m.to == 'all'){ // рассылка всем
			m.status = 2;
			send_all(m,this.__user.login,function(r){
				wsend([self], {wstype:"delivery",id:(r.id)?r.id:0,result:mstatus[2]}); // уведомление отправителя о получении сообщения
			});
		}else if(m.to && m.to == 'ALL' && m.message){ // рассылка всем
			var login; m.status = 2;
			for(login in members){
				if(login == this.__user.login) continue;
				m.to = login;
				db.ins(m,function(r){
					if(r.to in connections) wsend(connections[r.to],m);
				})
			}
			wsend([this], {wstype:"delivery",id:(m.id)?m.id:0,result:mstatus[2]}); // уведомление отправителя о получении сообщения
		}else if(m.to && m.message && !(m.to in connections)){ // пришло сообщение но адресат не в сети
			wslog("WARNING! "+m.to+" not online "+Object.keys(connections).join(", "));
			m.status = 1;
			db.ins(m,function(r){
				wsend([self], {wstype:"delivery",id:r.id,result:mstatus[0],dbrow:r}); // уведомление отправителя о получении сообщения
			});
		}else if(m.to && m.message && m.to in connections){ // пришло сообщение и адресат в сети
			m.status = 2;
			qr = db.ins(m,function(r){
				wsend([self], {wstype:"delivery",id:r.id,result:mstatus[2],dbrow:r}); // уведомление отправителя о получении сообщения
				wsend(connections[r.to], r); // отсылка сообщения адресату
			});
			if(!qr){
				wsend([self], {wstype:"delivery",id:m.id,result:mstatus[2]}); // уведомление отправителя о получении сообщения
				wsend(connections[m.to], m); // отсылка сообщения адресату
			}
		}else if(m.to && !m.message && m.to in connections){ // пришло сообщение и адресат в сети
			m.status = 2;
			wsend([self], {wstype:"delivery",id:m.id,result:mstatus[1]}); // уведомление отправителя о получении сообщения
			wsend(connections[m.to], m); // отсылка сообщения адресату
		}
	})
	ws.on('close', function (code,d) {
		var self = this, elem;
		if(connections[this.__user.login]){
			connections[this.__user.login].forEach(function(s,i){ if(s._id == self._id) elem = i; })
			if(elem !== undefined){
				connections[this.__user.login].splice(elem,1);
				if(connections[this.__user.login].length == 0){
					delete connections[this.__user.login];
					delaySendMembers(this.__user.login);
				}
			}
		}
		wslog('Disconnect: '+this.__user.login+' SocketID:'+this._id+' code:'+code+' connections left '+Object.keys(connections).length);
	})

	var lm = getMembers(ws.__user.login);
	if(Object.keys(lm).length>0) wsend([ws],{wstype:"members",add:lm}); // отправляем подключившемуся список контактов

	if(delay_m[ws.__user.login]){ // если рассылка оповещения об отключении ещё не произведена - отменяем
		clearTimeout(delay_m[ws.__user.login]);
		delete delay_m[ws.__user.login];
	}else{ // оповещаем всех о новом подключившемся
		var usr = {};
		usr[ws.__user.login] = ws.__user;
		send_all({wstype:"members",add:usr},ws.__user.login);
	}

	// посылаем неотправленные сообщения
	db.get(ws.__user.login, function(r){
		r.wstype='message';
		wsend([ws],r);
	});
})

setInterval(function(){updateMembers(db.pool)},3600000);
setInterval(function(){checkMembers()},300000);
