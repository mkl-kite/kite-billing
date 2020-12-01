
M = {};
window.M = M;
M.loader = $.loader();
M.Util = {
	extend: function (dest) {
		var sources = Array.prototype.slice.call(arguments, 1),
		i, j, len, src;
		for (j = 0, len = sources.length; j < len; j++) {
			src = sources[j] || {};
			for (i in src) {
				if (src.hasOwnProperty(i)) {
					dest[i] = src[i];
				}
			}
		}
		return dest;
	},
	trim: function (str) {
		return str.trim ? str.trim() : str.replace(/^\s+|\s+$/g, '');
	},
	splitWords: function (str) {
		return M.Util.trim(str).split(/\s+/);
	},
	setOptions: function (obj, options) {
		obj.options = M.extend({}, obj.options, options);
		return obj.options;
	},
	invokeEach: function (obj, method, context) {
		var i, args;
		if (typeof obj === 'object') {
			args = Array.prototype.slice.call(arguments, 3);
			for (i in obj) {
				method.apply(context, [i, obj[i]].concat(args));
			}
			return true;
		}
		return false;
	},
	stamp: (function () {
		var lastId = 0,
		 key = '_elem_id';
		 return function (obj) {
			 obj[key] = obj[key] || ++lastId;
			 return obj[key];
		 };
	}())
};

M.DomUtil = {
	create: function (tagName, className, container) {
		var el = $(tagName);
		if (className) el.addClass(className);
		if (container) el.appendTo(container);
		return el;
	},
}

M.extend = M.Util.extend;
M.setOptions = M.Util.setOptions;
M.stamp = M.Util.stamp;

M.Class = function () {};
M.Class.extend = function (props) {
	var NewClass = function(){
		if (this.initialize) {
			this.initialize.apply(this, arguments);
		}
	};
	var F = function(){};
	F.prototype = this.prototype;
	var proto = new F();
	proto.constructor = NewClass;
	NewClass.prototype = proto;
	for (var i in this) {
		if (this.hasOwnProperty(i) && i !== 'prototype') {
			NewClass[i] = this[i];
		}
	}
	if (props.statics) {
		M.extend(NewClass, props.statics);
		delete props.statics;
	}
	if (props.includes) {
		M.Util.extend.apply(null, [proto].concat(props.includes));
		delete props.includes;
	}
	if (props.options && proto.options) {
		props.options = M.extend({}, proto.options, props.options);
	}
	M.extend(proto, props);
	var parent = this;
	NewClass.__super__ = parent.prototype;
	return NewClass;
};
M.Class.include = function (props) {
	M.extend(this.prototype, props);
};
M.Class.mergeOptions = function (options) {
	M.extend(this.prototype.options, options);
};

M.Mixin = {};

M.Mixin.Events = {
	
	addEventListener: function (types, fn, context) { // (String, Function[, Object]) or (Object[, Object])
		if (M.Util.invokeEach(types, this.addEventListener, this, fn, context)) { return this; }
		
		var events = this[eventsKey] = this[eventsKey] || {},
		contextId = context && context !== this && M.stamp(context),
		i, len, event, type, indexKey, indexLenKey, typeIndex;
		
		types = M.Util.splitWords(types);
		
		for (i = 0, len = types.length; i < len; i++) {
			event = {
				action: fn,
				context: context || this
			};
			type = types[i];
			
			if (contextId) {
				
				indexKey = type + '_idx';
				indexLenKey = indexKey + '_len';
				
				typeIndex = events[indexKey] = events[indexKey] || {};
				
				if (!typeIndex[contextId]) {
					typeIndex[contextId] = [];
					
					events[indexLenKey] = (events[indexLenKey] || 0) + 1;
				}
				
				typeIndex[contextId].push(event);
				
				
			} else {
				events[type] = events[type] || [];
				events[type].push(event);
			}
		}
		
		return this;
	},
	
	hasEventListeners: function (type) { // (String) -> Boolean
		var events = this[eventsKey];
		return !!events && ((type in events && events[type].length > 0) ||
		(type + '_idx' in events && events[type + '_idx_len'] > 0));
	},
	
	removeEventListener: function (types, fn, context) { // ([String, Function, Object]) or (Object[, Object])
		
		if (!this[eventsKey]) {
			return this;
		}
		
		if (!types) {
			return this.clearAllEventListeners();
		}
		
		if (M.Util.invokeEach(types, this.removeEventListener, this, fn, context)) { return this; }
		
		var events = this[eventsKey],
		contextId = context && context !== this && M.stamp(context),
		i, len, type, listeners, j, indexKey, indexLenKey, typeIndex, removed;
		
		types = M.Util.splitWords(types);
		
		for (i = 0, len = types.length; i < len; i++) {
			type = types[i];
			indexKey = type + '_idx';
			indexLenKey = indexKey + '_len';
			
			typeIndex = events[indexKey];
			
			if (!fn) {
				delete events[type];
				delete events[indexKey];
				delete events[indexLenKey];
			} else {
				listeners = contextId && typeIndex ? typeIndex[contextId] : events[type];
				
				if (listeners) {
					for (j = listeners.length - 1; j >= 0; j--) {
						if ((listeners[j].action === fn) && (!context || (listeners[j].context === context))) {
							removed = listeners.splice(j, 1);
							// set the old action to a no-op, because it is possible
							// that the listener is being iterated over as part of a dispatch
							removed[0].action = M.Util.falseFn;
						}
					}
					
					if (context && typeIndex && (listeners.length === 0)) {
						delete typeIndex[contextId];
						events[indexLenKey]--;
					}
				}
			}
		}
		
		return this;
	},
	
	clearAllEventListeners: function () {
		delete this[eventsKey];
		return this;
	},
	
	fireEvent: function (type, data) { // (String[, Object])
		if (!this.hasEventListeners(type)) {
			return this;
		}
		
		var event = M.Util.extend({}, data, { type: type, target: this });
		
		var events = this[eventsKey],
		listeners, i, len, typeIndex, contextId;
		
		if (events[type]) {
			listeners = events[type].slice();
			
			for (i = 0, len = listeners.length; i < len; i++) {
				listeners[i].action.call(listeners[i].context, event);
			}
		}
		
		typeIndex = events[type + '_idx'];
		
		for (contextId in typeIndex) {
			listeners = typeIndex[contextId].slice();
			
			if (listeners) {
				for (i = 0, len = listeners.length; i < len; i++) {
					listeners[i].action.call(listeners[i].context, event);
				}
			}
		}
		
		return this;
	},
	
	addOneTimeEventListener: function (types, fn, context) {
		
		if (M.Util.invokeEach(types, this.addOneTimeEventListener, this, fn, context)) { return this; }
		
		var handler = M.bind(function () {
			this
			.removeEventListener(types, fn, context)
			.removeEventListener(types, handler, context);
		}, this);
		
		return this
		.addEventListener(types, fn, context)
		.addEventListener(types, handler, context);
	}
};

M.Mixin.Events.on = M.Mixin.Events.addEventListener;
M.Mixin.Events.off = M.Mixin.Events.removeEventListener;
M.Mixin.Events.once = M.Mixin.Events.addOneTimeEventListener;
M.Mixin.Events.fire = M.Mixin.Events.fireEvent;

M.Tree = M.Class.extend({
	includes: M.Mixin.Events,
	elements: [],
	options:{
		class: '',
		sort: true,
		wrap: true
	},
	initialize: function(param){
		if(typeof(param)=='object') M.extend(this.options,param);
		var opt = this.options;
		this.root = new M.TreeNode({});
	},
	add: function(param){
		var 
		el = new M.TreeNode({});
		this.elements.push(el);
		return id;
	}
})

M.allNodes = [];

M.treeTypes = {
	traceport: function(p){
		var title='',k;
		for(k in p) if(p[k]!='') title=title+k+': '+p[k]+"\r";
		return {
		object: $('<li>').attr({traceport:p.id,title:title}).append($('<div>').attr({class:"lihead"}).append([
		$('<span>').append($('<div>').attr({class:'port',port:p.type,style:'background-color:'+((p.color)?p.color:'inherit')}).text(p.number)), '&larr; ', $('<span>').text(p.device), $('<span>').text(p.address)
		])),
		key: false,
		name: p.device + ' ' + p.address
		}
	},
	port: function(p){
		var t = [], k;
		for(k in {id:0,device:0,number:0,node:0,porttype:0,note:0}) if(p[k]!='' && p[k]!=null) t.push(k+': '+p[k]);
		return {
		object: $('<li>').attr({port:p.id,devid:p.device,nodeid:p.node,class:((p.linkport)?'connected':'')}).append(
		$('<div>').attr({class:"lihead",title:t.join("\r"),style:'background-color:'+((p.bandle)?p.bandle:'inherit')+';background-opacity:0.3'}).append([
		$('<span>').append($('<div>').attr({class:'port',style:'background-color:'+((p.color)?p.color:'inherit')+';border-style:'+((p.coloropt)?p.coloropt:'inherit')+''}).text(p.number)),
		((p.linkport)?' &rarr; ':''),
		((p.linkport)?$('<span>').append($('<div>').attr({class:'port',style:'background-color:'+((p.linkcolor)?p.linkcolor:'inherit')+';border-style:'+((p.linkcoloropt)?p.linkcoloropt:'inherit')}).text(p.linkport)):''),
		((p.linkport)?' '+p.devtype+'('+((p.ip!='')?p.ip:p.numports)+')'+'&nbsp;'+p.devname:''),
		$('<div>').attr({class:'hud'}).append([
		((p.linkport)?$('<img>').attr({go:"ports",do:"disconnect",class:"button",src:"pic/del.png",key:"port",title:"разорвать"}):
		$('<img>').attr({go:"ports",do:"connect",class:"button",src:"pic/conn.png",key:"port",title:"соединить"})),
		$('<img>').attr({go:"ports",do:"edit",class:"button",src:"pic/conf.png",key:"port",title:"изменить"})
		])
		])),
		key: p.number,
		name: p.device + ' ' + p.number + ' порт'
		}
	},
	device: function(p){
		var t = [], k, n,
		btn = '<img go="devices" do="print" class="button" src="pic/prn.png" key="device" title="печать">'+
		'<img go="devices" do="remove" class="button" src="pic/del.png" key="device" title="удалить">'+
		'<img go="devices" do="edit" class="button" src="pic/conf.png" key="device" title="изменить">';
		if(p.type == 'switch') btn = '<img go="devices" do="nagios_switch" class="button" src="pic/ng.png" key="device" title="добавить в Nagios">' + btn;
		for(k in p) if(p[k]!='' && p[k]!=null) t.push(k+': '+p[k]);
		n = devtypes[p.type]+' '+((p.type!='cable' && p.subtype!='')? p.subtype:p.numports+((p.type=='cable')?'ж':'п'))+((p.type=='switch')?' '+p.ip:'');
		return {
		object: $('<li>').attr({device:p.id,object:p.object}).append([
		$('<div>').attr({class:"lihead",title:t.join("\r")}).append([
		$('<span>').attr({class:"devtype"}).html(n),p.name,
		$('<div>').attr({class:'hud'}).append(btn)
		])]),
		key: n,
		name: n
		}
	},
	node: function(d){
		return {
			object: $('<li>').attr({node:p.id}).append(
			$('<div>').attr({class:'lihead'}).text(p.address).append([
			$('<div>').attr({class:'hud'}).append([
			$('<img>').attr({go:"nodes",do:"print",class:"button",src:"pic/prn.png",key:"node",title:"печать"}),
			$('<img>').attr({go:"nodes",do:"remove",class:"button",src:"pic/remove.png",key:"node",title:"удалить"}),
			$('<img>').attr({go:"devices",do:"add",class:"button",src:"pic/add.png",key:"node",title:"добавить устройство"}),
			$('<img>').attr({go:"nodes",do:"edit",class:"button",src:"pic/conf.png",key:"node",title:"изменить"})
			])
			])),
			key: p.address,
			name: p.address
		}
	},
	user: function(p){
		return {
			object: $('<li>').attr({uid:p.uid,user:p.user}).append(
			$('<div>').attr({class:'lihead '+user_state[p.state]}).append([
			p.user,
			$('<span>').attr({class:"lidata"}).text(p.kv),
			$('<div>').attr({class:'hud',style:'right:20px'}).append([
			$('<img>').attr({src:"pic/delred.png",class:"button",go:"userkill",do:"",uid:p.uid,key:"uid"})])
			])).append(MakeLiUsrInfo(p)),
			key: p.kv,
			name: p.user
		}
	},
	usrinfo: function(p){
		var info = $('<ul>').attr({class:"user branch"})
		for(var v in p)
			if(v!='user'&&v!='uid'&&v!='kv'&&v!='state')
				info.append($('<li>')
				.append($('<div>').attr({class:"lihead"}).text(v)
				.append($('<span>').attr({class:"lidata"}).text(p[v]))))
		return { object: info, key: v, name: p[v] };
	},
	home: function(p){
		return p.id;
	},
	street: function(p){
	},
	default: function(p){
		var n, l = [], t = typeof(p);
		if(t === 'string') return $('<span>').html(p);
		if(t === 'object' && 'name' in p) return $('<span>').html(p.name);
		if(t === 'object'){
			for(var n in p) l.push('<span>'+p[n]+'</span>');
			return $(l.join(' '));
		}
		console.log('unsupported treeNode type: '+t);
	}
}

M.TreeNode = M.Class.extend({
	statics: {
		className: 'treeNode',
		state: 0 // 0 - невидим, 1 видим и ветвь закрыта, 2 видим и ветвь открыта
	},
	includes: M.Mixin.Events,
	options:{
		type: 'default',
		data: {id:0},
		key: 'id', // параметр в data для идентификации узла
		class: 'leaf',
		compare: 1, // задаёт функцию сравнения элементов
		query: false, // для формирования запроса ajax для подгрузки данных
		hold: 1,
		sort: 1,
		parent: false,
		seqname: 'sequence'
	},
	initialize: function(param){
		if(typeof(param)=='object') M.extend(this.options,param);
		var opt = this.options,
		id = M.stamp(this);
		if(!opt.key) this.key = id;
		else this.key = (opt.data[opt.key])? opt.data[opt.key] : opt.key;
		if(typeof opt.parent === 'object')
			this.parent = opt.parent;
		this._elements = [];
		this._sequence = [];
		this._elem = this._makeDOMElement();
		M.allNodes[id] = this;
	},
	_makeDOMElement: function(){
		var li, el;
		li = M.DomUtil.create('li', this.className);
		$(li).attr('treeNode',M.stamp(this));
		if(this.options.class) li.addClass(this.options.class)
		this._header = M.DomUtil.create('div', 'lihead', li);
		if(typeof M.treeTypes[this.options.type] === 'function'){
			this._header.append(M.treeTypes[this.options.type](this.options.data)));
		}else{
			this._header.append(M.treeTypes.default(this.options.data));
		}
		this._list = M.DomUtil.create('ul', '', li);
		return li;
	},
	_compare: function(elem1, elem2){ /* сравнивает два элемента результат должен быть >0 <0 или 0 */
		if(typeof this.options.compare === 'function') return this.options.compare(elem1.key, elem2.key);
		if(elem1.key === elem2.key) return 0;
		else if(elem1.key > elem2.key) return 1;
		else return -1;
	},
	expend: function(){	// наполняет ветвь объектами
		if(this.options.hold && this.elements.length > 0){
			this.fire('update',{ data:this.options.data })
			this._list.showBranch()
		}
		this.fire('expend',{ head:this._header, list:this._list, data:this.options.data })
		for(var i = 0, l = this._sequence.length; i < l; i++){
			this._list.append(this._elements[this._sequence[i]]._elem)
		}
	},
	addElem: function (elem) {	// добавить к ветви элемент
		if(!(elem instanceof M.TreeNode)) return false;
		var id = M.stamp(elem), i, l;
		if (this.hasElem(elem)) { return true; }
		this._elements[id] = elem;
		var first = 0, last = this._sequence.length - 1,
		pos = this._position(first, last, elem);
		this._sequence.splice(pos + 1, 0, id);
		if(pos in this._sequence) $(this._elements[this._sequence[pos]]._elem).after(elem._elem);
		else $(this._list).append(elem._elem);
		elem.parent = this;
		return this;
	},
	remove: function () { // удаляет ветвь и себя 
		var id = M.stamp(this), pos = -1, i, len = this._sequence.length, p;
		for(i = 0; i < len; i++){
			this._elements[this._sequence[i]].remove();
		}
		p == this.parent
		if(p instanceof M.TreeNode){
			i = p._sequence.indexOf(id);
			p._sequence.splice(i,1);
			delete p._elements[id];
		}
		$(this._elem).remove();
		delete M.allNodes[id]; // ??? возможна колизия
		return this;
	},
	hasElem: function(elem){
		if (!elem) { return false; }
		return (M.stamp(elem) in this._elements);
	},
	_position: function(first, last, elem){ /* возвращает id для _elements который будет предшествовать вставляемому */
		if (first > last) return last;
		var middle = Math.floor(first + (last - first) / 2), cmp;
		if (this._compare(elem, this._sequence[first]) < 0) return first - 1;
		else if (this._compare(elem, this._elements[this._sequence[last]]) > 0) return last;
		else if (last == first + 1) return first;
		else if ((cmp = this._compare(elem, this._elements[this._sequence[middle]])) > 0) return this._position(middle, last, elem);
		else if (cmp < 0) return this._position(first, middle, elem);
		else return middle;
	},
	showBranch: function(method){
		var f = (typeof method === 'function')? method : this.setState;
		if(this.state == 0) $(this._list).slideDown(300,f(2))
		else f();
	},
	hideBranch: function(method){
		var f = (typeof method === 'function')? method : this.setState;
		if(this.state > 0) $(this._list).slideUp(300,f(1));
	},
	setState: function(state){
		this.state = state;
	}
	list: function(){
		var l = [], i;
		for(i in this._sequence) l.push(this._elements[this._sequence[i]].getName());
		return l;
	},
	getName: function(){
		return this.options.name;
	},
	onload: function (d){
		var n = this.options.seqname, o;
		if (n in d){
			for(var i in d[n]){
				if(typeof d[n][i] !== 'object') continue;
				d[n][i].type = n;
				o = new M.TreeNode(d[n][i]);
				this.addElem(o);
			}
		}
	},
	load: function (ldr) {
		if(typeof ldr.get !== 'function') ldr = $.loader();
		if(!this.options.query) return false;
		var q = $.parseQuery(this.options.query);
		for(var n in q) if(q[n] == '' && n in this.options.data) q[n] = this.options.data[n];
		ldr.get({ data:q, onLoaded: this.onload })
	}
});
