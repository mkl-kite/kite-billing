(function($) {

Date.prototype.setUTC = function(o){
	var d = this;
	if(typeof o != 'string') return;
	if(o.match(/[^0-9]/) && !(o = Date.parse(o)/1000)) return;
	d.setTime(o * 1000);
	return d;
}
Date.prototype.getUTC = function(){
	return Math.floor(this.getTime()/1000);
}
Date.prototype.RuDate = function(o){
	var d = this, m=['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря']; 
	function f(v){return v.toString().replace(/^(.)$/,"0$1")};
	return f(d.getDate())+" "+m[d.getMonth()]+" "+d.getFullYear();
}
Date.prototype.RuTime = function(o){
	var d = this;
	function f(v){return v.toString().replace(/^(.)$/,"0$1")};
	return f(d.getHours())+":"+f(d.getMinutes());
}

$.int2ip = function(ip){
	var g = [16777216,65536,256],
		s = function(i){var r = Math.floor(ip/g[i]); ip=ip-r*g[i]; return r;},
		r = s(0)+'.'+s(1)+'.'+s(2)+'.'+ip;
	return r;
}
$.ip2int = function(ip){
	var g = [16777216,65536,256],
		a = ip.split('.'),
		r = a[0]*g[0] + a[1]*g[1] + a[2]*g[2] + a[3]*1;
	return r;
}
$.parseQuery = function(q) {
	if(typeof q != 'string') return {};
	var a = q.replace(/.*\?/,'').split('&'), i, p, r = {};
	for(i in a) {
		p = a[i].split('=');
		if(p.length>1) r[p[0]]=p[1];
	}
	return r;
}
$.mkQuery = function(q){
	if(typeof q !== 'object') return q
	var a = [], n;
	for(n in q) if(typeof(q[n])!='object') a.push(n+"="+encodeURIComponent(q[n]));
	return a.join('&');
}
$.paramFromURL = function(a) {
	var q = $.parseQuery(window.location.href), r = (!a || $.isArray(a))? {} : '';
	if(Object.keys(q).length == 0) return r;
	if(!a) return q;
	if($.isArray(a)){
		for(k in a) if(a[k] in q) r[a[k]] = q[a[k]];
	}else
		if(a in q) r = q[a];
	return r;
}
$.paramToURL = function(a) {
	var k, p='', g = [], get = {}, ref = [],
	url = window.location.href.replace(/\#.*/,"")
	url = url.split('?')
	if(url[1]) g = url[1].split('&')
	g.forEach(function(el,i,a){
		var s = el.split('=');
		if(s.length==2) get[s[0]]=s[1];
	})
	get = $.extend(get,a);
	for(k in get) ref.push(k + '=' + get[k]);
	return url[0] + ((ref.length>0)? '?' + ref.join('&'):'');
}
$.shortFIO = function(s) {
	var f = s.split(/\s+/);
	if(f[1]) f[1] = f[1].charAt(0)+'.';
	if(f[2]) f[2] = f[2].charAt(0)+'.';
	if(f.length>3) f = f.slice(0,3);
	return f.join(' ');
}
$.parseAddress = function(s){
	var m, r={pref:'',ul:'',nd:'',lit:'',kv:'',prim:''};
	if(typeof s != 'string') return r;
	if(m = s.match(/([а-я\-]{1,3})\.(..*)\s+(\d+)([а-я])?\/(\d+)(.*)/i)){
		r.pref = m[1]||''; r.ul = (m[2]||'').replace(/^\s+/,'');
		r.nd = m[3]||''; r.lit = m[4]||''; r.kv = m[5]||''; r.prim = m[6]||'';
	}else if(m = s.match(/([а-я\-]{1,3})\.((\d+)?[^\d]*)\s+(\d+)([а-я])?(.*)/i)){
		r.pref = m[1]||''; r.ul = m[2]||'';
		r.nd = m[4]||''; r.lit = m[5]||''; r.kv = ''; r.prim = m[6]||'';
	}
	return r;
}

$.getTrace = function(e){
	if(!('stack' in e)) return false;
	var o = [];
	e.stack.split(/\n/).forEach(function(s,i){
		o[i] = s.replace(/@.*\/([0-9A-Za-z.]+)\?.*:(\d+:\d+)$/," $1 $2");
	})
	return o;
}

$.fn.highlight = function(pat) {
	function innerHighlight(node, pat) {
		var skip = 0;
		if (node.nodeType == 3) {
			var pos = node.data.toUpperCase().indexOf(pat);
			if (pos >= 0) {
				var spannode = document.createElement('span');
				spannode.className = 'highlight';
				var middlebit = node.splitText(pos);
				var endbit = middlebit.splitText(pat.length);
				var middleclone = middlebit.cloneNode(true);
				spannode.appendChild(middleclone);
				middlebit.parentNode.replaceChild(spannode, middlebit);
				skip = 1;
			}
		}
		else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
			for (var i = 0; i < node.childNodes.length; ++i) {
				i += innerHighlight(node.childNodes[i], pat);
			}
		}
		return skip;
	}
	return this.each(function() {
		innerHighlight(this, pat.toUpperCase());
	});
}

$.fn.removeHighlight = function() {
	return this.find("span.highlight").each(function() {
		this.parentNode.firstChild.nodeName;
		with (this.parentNode) {
			replaceChild(this.firstChild, this);
			normalize();
		}
	}).end();
}

$.fixedMenu = function(m){
	var i, a={}, item, l, img,
	menu = $('<div>').attr({class:'contxMenu',style:'position:fixed;top:30px;right:30px;display:none'}).get(0)
	if(!ldr) ldr = $.loader()
	for(i in m){
		l = (m[i]['label'])? m[i].label : i;
		img = (m[i]['image'])? $('<img src="'+m[i].image+'">') : '';
		id = (typeof(i) === 'string')? i : 'item_'+i
		item = $('<div>').addClass('item').attr({id:id}).append([img,l]).get(0)
		item._itemmenu = m[i];
		$(menu).append(item)
	}
	$('.item',menu).on('click',function(){
		var id = $(this).attr('id').replace(/^item_/,''),
		menuitem = this._itemmenu, q, fn = {delete:0,append:1,modify:2};
		if($.isFunction(menuitem['query'])) { menuitem.query(1); return false; }
		q = $.parseQuery(menuitem['query']);
		q[menuitem['to']] = $(menuitem.obj).attr('selrows')
		ldr.get({
			data:$.mkQuery(q),
			obj: item._itemmenu['obj'],
			onLoaded: function(d){
				var n, i;
				if(d['form'])
					$.popupForm({
						data: {form:d['form']},
						obj: d['obj'],
						oncancel: menuitem.cancel,
						onsubmit: menuitem.submit,
						loader:ldr
					});
				else
					menuitem.submit(d)
			},
			showLoading: true
		})
	})
	return menu;
}

$.contxMenu = function(m,xy,id){
	if(typeof ldr !== 'object') ldr = $.loader();
	var cap = $('<div class="contx">').appendTo('body'),
	menu = $('<div class="contxMenu">').appendTo(cap),
	table, i, s;
	for(i in m){
		if(!(m[i]['to'])) return true;
		var item = $('<div class="item">'+m[i].label+'</div>').appendTo(menu), it=item.get(0);
		it.item_menu = m[i];
		if(!table && m[i].obj) table = m[i].obj;
	}
	menu.on('click','.item',function(){
		var s = this.item_menu, q = (s['query'])? $.parseQuery(s.query):{};
		q['id'] = id;
		if(s.to == 'window') window.open(s.target+'?'+$.mkQuery(q));
		else if(s.to == 'self') window.open(s.target+'?'+$.mkQuery(q),'_self');
		else if(s.to == 'blank') window.open(s.target+'?'+$.mkQuery(q));
		else if(s.to.match(/\.php$/)) window.open(s.to+'?'+$.mkQuery(q));
		else if(s.to == 'select' && s.obj['_select'])
			if(s.obj._select(id))
				$('tr[id='+id+']',s.obj).addClass('selected')
			else
				$('tr[id='+id+']',s.obj).removeClass('selected')
		else if(s.to == 'form') $.popupForm({
			data: $.mkQuery(q),
			obj: s.obj||{},
			onsubmit: s.submit,
			oncancel: s.cancel,
			loader: ldr
		});
		else if(s.to == 'map') ldr.get({
			data: $.mkQuery(q),
			onLoaded: function(d){
				if(d.object) M.storage.set('mapSearch',d.object)
			},
			showLoading: true
		});
		else ldr.get({
			data: $.mkQuery(q),
			obj: s.obj||{},
			onLoaded: s.submit,
			showLoading: true
		})
	})
	if(xy[1] - $(document).scrollTop() + menu.outerHeight() > $(window).height())
		menu.css({left:xy[0],top:xy[1] - $(document).scrollTop() - menu.outerHeight()})
	else
		menu.css({left:xy[0],top:xy[1] - $(document).scrollTop()})
	cap.on('click',function(e){
		cap.hide().remove()
		if(table) $(table).find('.select2del').removeClass('select2del')
	})
}

$.loader = function(options) {
	var Query = [],
	opt = $.extend({
		url: 'ajaxdata.php',
		type: 'POST',
		dataType: 'json'
	},options||{}),
	cap = $('<div class="overlay" style="display:none;">'+
		'<div><div><img src="pic/loading.gif"></div></div></div>'),
	
	_OnLoaded = function(d) {
		if(d.desc) var msg = d.desc 
		else msg = "Данные получены:\n<p>\n\n"+$.toJSON(d)+"</p>"
		$.popupForm({
			type:'info',
			data: msg
		})
	},
	
	_OnError = function(d) {
		$.popupForm({
			type:'error',
			data:"Ошибка:\n<p style=\"text-align:left\">\n\n"+d+"</p>"
		})
	}

	function prepareData(d) {
		var k, v, r=[]
		if(typeof(d)=='object') {
			for(k in d) {
				if(typeof(d[k])!='object') r[r.length]=k+'='+encodeURIComponent(d[k])
			}
			return r.join('&')
		}
		if(typeof(d)=='string') {
			return d
		}
	}

	var send = function(n) {
		var d = Query[n]

		/* Выполняется при запросе и получении данных */
		var Loading = function(sw) {
			if(d.showLoading) {
				if($.isFunction(d.showLoading)) 
					d.showLoading(sw)
				else {
					if(sw) {
						cap.appendTo('body').fadeIn();
					}else{
						if(d.LoadingTimeOut) clearTimeout(d.LoadingTimeOut);
						cap.fadeOut(600,function(){$(this).remove()});
					}
				}
			}
		}
		
		/* Обработка события успешного получения данных */
		var onSuccess = function(data) {
			if(d.LoadingTimeOut) clearTimeout(d.LoadingTimeOut)
			Loading(false)
			data = data || {result:'ERROR',desc:'Сервер не предоставил данных!'}
			if(data.result && data.result!='OK') {
				if(data.result=='close'||data.action=='reload') 
					if(!data['to']) window.open($.paramToURL({go:'logout'}),'_self');
					else window.open(data.to);
				else if(data.result=='WARNING') {
					data.result = 'OK'; data.warning = true;
				}else data.errcode = data.desc
			}
			if (!data.errcode) {
				data.obj = Query[n].obj||{}
				if($.isFunction(d.onLoaded)) d.onLoaded(data)
			}else{
				if($.isFunction(d.onError)) 
					d.onError(data.errcode)
				else
					_OnError(data.errcode)
			}
			Query[n] = false
		}

		/* Обработка ошибки при получении данных */
		var onError = function(xhr, status){
			if(d.LoadingTimeOut) clearTimeout(d.LoadingTimeOut)
			Loading(false)
			var errinfo = { errcode: status }
			if (xhr.status != 200) {
				// может быть статус 200, а ошибка
				// из-за некорректного JSON
				errinfo.message = xhr.statusText
			} else {
				errinfo.message = 'Некорректные данные с сервера<p>'+xhr.responseText+'</p>'
			}
			var msg = "Ошибка "+errinfo.errcode+"<br>"+errinfo.message
			if($.isFunction(d.onError)) 
				d.onError(msg)
			else
				_OnError(errinfo)
			Query[n] = false
		}
		
		d.LoadingTimeOut = setTimeout(function(){
			Loading(true)
		},d.timeout);
		if(typeof(d)=='object') {
			if(d.input && d.input.files){
				var xhr = new XMLHttpRequest, file = $(d.input).val();
				if(typeof d.data != 'object') d.data = $.parseQuery(d.data);
				d.data.qfile = file;
				xhr.open("POST", d.url+'?'+$.mkQuery(d.data), true);
				xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
				xhr.setRequestHeader("X-File-Name", encodeURIComponent(file));
				xhr.setRequestHeader("Content-Type", "application/octet-stream");
				xhr.send(d.input.files[0]);
				xhr.onreadystatechange = function(){            
					if (xhr.readyState == 4){ 
						if(d.dataType == 'json'){
							try{
								response = $.parseJSON(xhr.responseText);
							}catch(e){ response = xhr.responseText; }
						}else{
							response = xhr.responseText;
						}
						onSuccess(response);
					}
				}
			}else{
				var ajax_query = {
					url: d.url,
					type: d.type,
					data: prepareData(d.data),
					dataType: d.dataType,
					success: onSuccess,
					error: onError,
					cache: false
				}
				if(d.headers) ajax_query.headers = d.headers
				$.ajax(ajax_query);
			}
		}else{
			alert("Данный запрос не найден в очереди!")
		}
	}
	
	return {
		get: function(o) {
			var _opt = $.extend({
				name: 'query_'+Query.length,
				data: {},
				timeout: 500,
				onLoaded: _OnLoaded,
				onError: _OnError,
				showLoading: false
			},opt,o||{})
			_opt.number = Query.length;
			Query.push(_opt)
			send(_opt.number)
			return _opt.number
		},
		url: function(s) {
			if(typeof(s)=='undefined') return opt.url
			if(typeof(s)=='string' && s != '') opt.url = s
		}
	}
}

/* пример инициации $('.pager').pager(function(v){set_page(v)})*/
$.fn.pager = function(changer) {
	if(typeof(changer)!=='function') changer=function(v){console.log('set page='+v)};
	this.each(function(){
		function initPager(pager) {
			var all = $(pager).attr('numpages')||0,
				inp = $(pager).find('input').get(0)||{},
				intID, toID,
				v = parseInt($(inp).val())

			pager.updateTable = function() {
				all = $(pager).attr('numpages')||0
				var v = parseInt($(inp).val())
				if(v<1) {$(inp).val(1); v=1}
				if(all>0 && v>all) {$(inp).val(all); v=all}
				changer(v)
			}

			function modifyInput(a) {
				var v = parseInt($(inp).val());
				all = $(pager).attr('numpages')||0
				if(a<0&&v>1&&v+a>=1||a>0&&v<all&&v+a<=all) $(inp).val(v+a)
				if(a<0&&v>1&&v+a<1) $(inp).val(1)
				if(a>0&&v<all&&v+a>all) $(inp).val(all)
				toID = setTimeout(function() {
					intID=setInterval(function() {
						v = parseInt($(inp).val())
						if(a<0&&v>1&&v+a>1||a>0&&v<all&&v+a<all) $(inp).val(v+a)
						if(a<0&&v>1&&v+a<1) $(inp).val(1)
						if(a>0&&v<all&&v+a>all) $(inp).val(all)
					},100)
				},500)
			}

			function stoprepeat() {
				clearTimeout(toID)
				clearInterval(intID)
				pager.updateTable()
			}

			$(pager).find('#begin').click(function() {
				$(inp).val(1)
				pager.updateTable()
			})
			$(pager).find('#last').click(function() {
				all = $(pager).attr('numpages')||0
				$(inp).val(all)
				pager.updateTable()
			})
			$(pager).find('#prev').mousedown(function(){modifyInput(-1)}).mouseup(function(){stoprepeat()})
			$(pager).find('#prev10').mousedown(function(){modifyInput(-10)}).mouseup(function(){stoprepeat()})
			$(pager).find('#next').mousedown(function(){modifyInput(1)}).mouseup(function(){stoprepeat()})
			$(pager).find('#next10').mousedown(function(){modifyInput(10)}).mouseup(function(){stoprepeat()})
			$(inp).bind('keydown',function(e){
				if(e.keyCode==13||!e.keyCode) { // return
					e.preventDefault()
					pager.updateTable()
					return false
				}
			})
		}
		var t,n;
		if(this.tagName=='DIV') {
			$(this).addClass('pager').empty().append(
				'<img id="begin" src="pic/begin.png"> <img id="prev10" src="pic/prev10.png"> <img id="prev" src="pic/prev.png"> '+
				'<input type="text" value="1" size=5 name="page"> <img id="next" src="pic/next.png"> <img id="next10" src="pic/next10.png"> <img id="last" src="pic/last.png">'
			)
			initPager(this);
		}else console.log('object.tagName is not DIV, obj='+this.tagName+'#'+this.id);
	})
	return this;
}

$.Pager = function(option) {
	var opt = $.extend({
		pager: '_pager',
		changer: function(){}
	},option||{});
	$('#'+opt.pager).pager(opt.changer);
	opt.changer(1);
}

$.fn.prepareTable = function() {
	return this.each(function() {
		var
		cr = {
			th:{field:0,class:0,style:0,label:0,title:0},
			td:{id:0,class:0,style:0,colspan:0,rowspan:0},
			table:{id:0,class:0,style:0,target:0,delete:0,module:0,tname:0,limit:0}
		},
		img = '<td class="del"><img class="del-button" src="pic/delete.png"></td>',
		q = $.parseQuery($(this).attr('query')),
		// tname нужно указывать в query что бы считывать сохранённые данные
		tname = (q['tname'])? q.tname : 'default',
		t = $('<table>'), key = 0, trgt, limit, conf = {}, tab = t.get(0);
		tab.container = this;
		if(!q) return false;
		if(q.tname) { // вытаскиваем из coockie сохранённые данные
			if(M && M.conf) 
				conf = M.conf.get(q.tname);
			if(typeof conf === 'undefined') {
				M.conf.save(q.tname,{pagerows:10});
				conf = M.conf.get(q.tname)
			}
		}
		if(conf) for(var i in conf) {
			if(typeof conf[i] === 'object') 
				for(var n in conf[i]) q[n] = conf[i][n]
			else 
				q[i] = conf[i]
		}
		if(typeof ldr !== 'object') ldr = $.loader();
		reloadTable = function(d,t){
			var a, i, container = t.container;
			if(d['table']) {
				$('.pager',container).attr('numpages',d.table.pages||1);
				$('.pager input',container).val(d.table.page||1);
				t.find('tbody').remove();
				if(d.table['tbody']) {
					t.append(buildTBody(d.table.tbody));
				}
				if(d.table['tfoot']) {
					t.find('tfoot').remove();
					t.append(buildTFoot(d.table.tfoot));
				}
				if(a = t.attr('selrows')) {
					a = a.split(',');
					for(i in a) t.find('tr[id='+a[i]+']').addClass('selected');
				}
			}
		}
		tab._reload = function(p){
			var order = tab._sort, ch = false, t = $(this), container = this.container, nokeep={};
			if(order && tab._direction == 1) order = order + ' desc';
			if(!p) p = $('.pager input',container).val();
			if(typeof p !== 'object') p = {page: p||1}
			$('.filter-item [name]',container).each(function(){
				var tmp = [],
				name = $(this).attr('name'), k = $(this).attr('keep'),
				v = $(this).val();
				if(!k || k == 'false') nokeep[name]=1;
				if($(this).hasClass('checklist')){
					$(this).find('input:checked').each(function(){
						tmp.push($(this).attr('value')) 
					})
					if(tmp.length>0) q[name] = tmp.join(','); else delete q[name];
				}else{
					if(v != '' && v != '_') q[name] = v; else delete q[name];
				}
				if(k && q[name] && (!conf[name] || q[name] != conf[name])){
					ch = true; conf[name] = q[name];
				}
			})
			for(n in conf) if(!q[n] || nokeep[n]) { 
				ch = true;
				delete(conf[n]);
			}
			if(ch) 
				M.conf.save(tname,conf)
			q = $.extend(q,p); q.update = 'on';
			if(order) q.sort = order;
			var hstr = $.paramToURL($.extract([q,{go:0,update:0}]));
			if($('.tablecontainer').length == 1) history.replaceState(q,'',location.href);
			ldr.get({
				data: $.mkQuery(q),
				onLoaded: function(d) { reloadTable(d,t) },
				showLoading: true
			})
		}
		var 
		buildTable = function(d){
			try{
				tname = (d.table['tname'])? d.table.tname : 'table';
				var i,
				container = tab.container,
				cfgname = 'pager';
				tab._plus = (d.table['add'])? d.table.add : 'yes';
				if(M && M.conf) 
					conf = M.conf.get(tname);
				if(typeof conf === 'undefined') {
					M.conf.save(tname,{pagerows:10});
					conf = M.conf.get(tname)
				}
				var changes = false;
				t.opt = $.cros([d.table,cr.table])
				if(d.table['table_menu']) tab.table_menu = d.table.table_menu;
				if(d.table['fixed_menu']) tab.fixed_menu = d.table.fixed_menu;
				t.attr(t.opt);
				key = (d.key && typeof d.key != 'number')? d.key : 0;
				trgt = t.attr('target');
				limit = t.attr('limit');
				if(limit == 'yes') {
					var pg = $('<div>');
					if(d.table['pages']) pg.attr({numpages:d.table.pages});
					pg.appendTo(container).pager(function(p){tab._reload(p)});
					$('<div class="rowlimit filter-item"><select id="pagerows" keep="true" name="pagerows">'+
						'<option value="10">10<option value="20">20<option value="50">50'+
						'<option value="100">100<option value="all">все'+
						'</select></div>').appendTo(container);
					$('#pagerows',container).val(d.table.pagerows||conf.pagerows);
				}
				if(t.opt.delete != 'yes' && tab._plus != 'no'){
					var newf = $('<div class="filter-item"><span class="label">Добавить</span><span class="field">'+
						'<img class="add-button" src="pic/add.png" title="Добавить новую запись"/></span></div>');
					$(container).append(newf);
				}
				if(d.table['filters']) for(var i in d.table.filters){
					var el = d.table.filters[i];
					if(conf[i]) el.value = conf[i]
					$(container).append(M.field.make(el));
				} 
/*				if($('#pagerows',container).length == 0) $(container).append($('<br>')); */
				$(container).append('<br style="clear:both">');
				$('.filter-item [name]',container).each(function(){
					var n = $(this).attr('name');
					if($(this).hasClass('dateselect'))
						$(this).datepicker({dateFormat: 'dd-mm-yy'});
					if(conf[n]) $(this).val(conf[n]);
				}).change(function(e){
					var pg = $('.pager',container).find('input')[0],
						mem = ($(this).attr('keep'))? true : false,
						name = $(this).attr('name');
					if(mem){
						conf[name] = $(this).val();
						M.conf.save(tname,conf);
					}else if(conf[name]){
						delete conf[name];
						M.conf.save(tname,conf);
					}
					if(pg) $(pg).val(1).keydown(); else tab._reload(1);
					return false;
				});
				$('.checklist ul',container).mouseleave(function(){
					if(changes){
						changes = false;
						tab._reload(1)
					}
				})
				$('.checklist li input[type=checkbox]',container).click(function(e){
					changes = true;
					e.stopPropagation()
				})
				$('.checklist li',container).click(function(){
					var c = $(this).find('input[type=checkbox]').get(0)
					if(c) c.checked = !c.checked;
					changes=true
				})
				tab._sort = false;
				tab._thead = d.table.thead;
				t._colon = (d.table.thead['length'])? d.table.thead.length : Object.keys(d.table.thead).length;
				t.find('thead').remove();
				t.find('tbody').remove();
				t.append(buildTHead(d.table.thead))
				t.append(buildTBody(d.table.tbody));
				if(d.table['tfoot']) t.append(buildTFoot(d.table.tfoot));
			}catch(e) { console.log('ошибка обработки данных!'); }
			if(typeof trgt == 'string' && trgt!='') t.addClass('editable')
			t.appendTo(container);
			/* переключение сортировки */
			$('thead',t).on('click','td[field]',function(){
				var n, t = $(this).parents('table').get(0),
					pager = $(t.container).find('.pager').get(0),
					c1 = String.fromCharCode(9660), c2 = String.fromCharCode(9650),
					f = $(this).attr('field'), s = $(this).find('.sort');
				if(s.text() == c1) {
					s.text(c2);
					t._direction = 1
				}else if(s.text() == c2){
					s.text(c1)
					t._direction = 0
				}
				if(tab._sort != f) {
					$('thead td .sort',t).empty();
					s.html(c1);
					t._direction = 0
				}
				tab._sort = f;
				if(pager) $('input',pager).keydown(); else tab._reload()
			});
		},
		buildRow = function(d) {
			var tr = $('<tr>'), td, i, prev, n, pn, o=false, arr = '<div class="sort"></div>';
			try {
				prev = false; pn = 0;
				for(n in d) {
					if(o = (typeof(d[n]) === 'object' && d[n]!==null)) {
						$(tr).append($('<td>').attr(d[n]).html('<div>'+d[n].label+'</div>'+arr))
					}else{
						if(n == 0 && d[n]!='footer')
							$(tr).attr({id: ((typeof d[n] === 'string')? d[n] : tname+'_'+d[n])})
						else {
							td = $('<td>').attr($.cros([tab._thead[n],cr.td])).html(d[n]);
							if(pn != n-1){
								if(!prev)
									for(i=1;i<n;i++) tr.append($('<td>'))
								else
									prev.attr('colspan', n - pn);
							}
							$(tr).append(td)
							prev = td;
						}
					}
					pn = n;
				}
				if(pn < t._colon)
					for(i=pn; i<t._colon; i++)
						tr.append($('<td>'))
				if(t.opt['delete'] && t.opt.delete=='yes'){
					if(d[0]) tr.append($(img))
					else if(!o) tr.append($('<td>'))
				}
			}catch(e) { console.log('ошибка добавления записи в таблицу') }
			return tr;
		},
		buildTHead = function(d) {
			var i, th = [];
			for(i in d) th[i] = $.cros([d[i],cr.th]);
			return $('<thead>').append(buildRow(th));
		},
		buildTBody = function(d) {
			var body = $('<tbody>');
			for(var i in d) body.append(buildRow(d[i]));
			return body;
		},
		buildTFoot = function(d) {
			return $('<tfoot>').append(buildRow(d));
		}
		tab._select = function(r) {
			var id = (typeof r == 'object')? r[key] : r, a =[], i, s,
				v = $(this).attr('selrows')
			if(!v){
				$(this).attr('selrows',id)
				if(this['_fixmenu']) $(this._fixmenu).fadeIn()
				s = true;
			}else{
				a = v.split(',')
				if((i = $.inArray(id,a))>=0){
					a.splice(i,1);
					if(a.length == 0){
						$(this).removeAttr('selrows');
						if(this['_fixmenu']) $(this._fixmenu).fadeOut()
					}else
						$(this).attr('selrows',a.join(','));
					s = false;
				}else{
					a.push(id);
					$(this).attr('selrows',a.join(','));
					s = true;
				}
			}
			return s;
		}
		tab._append = function(row) {
			$(this).find('tbody').append(buildRow(row))
		}
		tab._delete = function(row) {
			var id = (typeof row == 'object')? row[key] : row,
				tn = (t.opt['tname'])? t.opt.tname+'_' : 'id_';
			if(typeof id == 'number') id = tn+id;
			else if(typeof id != 'string') id = tn+'unknown';
			try{
				$(this).find('tbody tr[id='+id+']').remove();
			}catch(e){
				tab._reload();
			}
		}
		tab._modify = function(row) {
			var id = (typeof row == 'object')? row[key] : null,
				tr = buildRow(row),
				tn = (t.opt['tname'])? t.opt.tname+'_' : 'id_', o;
			if(typeof id == 'number') id = tn+id;
			else if(typeof id != 'string') id = tn+'unknown';
			o = $(this).find('tbody tr[id='+id+']').get(0);
			if(o) $(o).before(tr).remove()
			else tab._reload(1)
		}
		$(window).on('popstate',function(){
			var s = history.state, n, o={};
			if(s) {
				$('.filter-item [name], .pager [name]',tab.container).each(function(i,e){
					o[$(e).attr('name')] = this;
				})
				for(n in o) if(s[n]) $(o[n]).val(s[n]);
				ldr.get({
					data: $.mkQuery(s),
					onLoaded: function(d) { reloadTable(d,t) },
					showLoading: true
				})
			}
			return false;
		})
		if(history.state) q = $.extract([history.state,{update:0}]);
		ldr.get({
			data: $.mkQuery(q),
			obj: tab,
			onLoaded: function(d) {
				var n, o={}, s;
				buildTable(d)
				$(d.obj).tableInit({loader:ldr});				
				if(s = history.state) {
					$('.filter-item [name], .pager [name]',tab.container).each(function(i,e){
						o[$(e).attr('name')] = this;
					})
					for(n in o) if(s[n]) $(o[n]).val(s[n]);
				}else{
					if($('.tablecontainer').length == 1)
						history.replaceState(q,'',location.href);
				}
			}
		})
	})
}

$.fn.tableInit = function(options) {
	var opt = $.extend({
		tabs: false,
		loader: false
	},options||{})

	return this.each(function() {
		if(this.nodeName=='TABLE') {
			var t = {id:0,target:0,delete:0,module:0,tname:0},
				img = '<img class="add-button" src="pic/add.png">',
				mtd = {delete:0,append:0,modify:0},
				table = this, container = this.container,
				f = $(this).parents('form').get(0),
				obj = container || this,
				tname = $(this).attr('tname'), m, fm;
			for(var i in t) t[i] = $(this).attr(i);

			// Процедура запуска метода объекта (_append,_modify,_delete)
			var proc = function(d) {
				var f, method, p = $('.pager input',container).val();
				for(method in mtd) {
					f = '_'+method
					if(typeof d.obj === 'object'){
						if(d[method] && $.isFunction(d.obj[f])) for(var i in d[method]) {
							d.obj[f](d[method][i])
						}
					}
				}
				if(p && $.isFunction(d.obj['_reload'])){
					d.obj._reload(p);
					$(table).find('.select2del').removeClass('select2del')
				}
				if(d.form) $.popupForm({data: d, loader: ldr});
			}
			if(table['table_menu']){
				m = table.table_menu;
				for(var i in m){
					if(m[i]['to'] && m[i].to == 'form' && !m[i]['query']){
						m[i].query = 'go='+t.module+'&do='+i+'&table='+tname;
						m[i].cancel = function(){$(table).find('.select2del').removeClass('select2del')}
					}
					m[i].obj = table;
					m[i].submit = proc;
				}
			}
			if(table['fixed_menu']){
				fm = table.fixed_menu;
				for(var i in fm){
					if(fm[i]['to'] == 'form' && !fm[i]['query']) { // если to=form и нету query do=название меню
						fm[i].query = 'go='+t.module+'&do='+i+'&table='+tname;
					}
					fm[i].obj = table;
					fm[i].cancel = function(d){
						$(table._fixmenu).fadeOut(function(){
							$(table).removeAttr('selrows').find('tr.selected').removeClass('selected')
							if(table._reload) table._reload()
						})
					}
					fm[i].submit = function(d){
						fm[i].cancel(d);
						table._reload(1);
					}
					if(fm[i]['to'] == 'cancel') fm[i].query = fm[i].cancel;
				}
				table._fixmenu = $.fixedMenu(fm);
				$('body').append(table._fixmenu);
			}
			$(this).on('mouseup','tbody span.linkform',function(e) {
				e.stopPropagation();
				e.preventDefault();
			})
			$(this).on('click','tbody span.linkform',function(e) {
				var tr = $(this).parents('tr'),
				id = tr.attr('id').replace(t.tname+'_',''),
				q = $.parseQuery($(this).attr('add'));
				if(q && q.do!='switch'){
					tr.addClass('select2del')
					$.popupForm({
						data: $.mkQuery(q),
						obj: table,
						onsubmit: proc,
						oncancel: function(){$(table).find('.select2del').removeClass('select2del')},
						loader: opt.loader
					});
				}else opt.loader.get({
					data: $.mkQuery(q),
					onLoaded: function(d){ d.obj = table; proc(d); }
				})
				return false;
			})

			if(t.target!='') {
				if(t.delete=='yes') { // Добавляем кнопку удаления
					$(this).find('thead tr').each(function() {
						var td = (table._plus == 'yes')? '<td>'+img+'</td>' : '<td>';
						$(this).append(td)
					})
					// Добавляем обработку нажатия кнопки удаления
					$(this).on('mouseup','tbody .del-button',function(e) {
						e.stopPropagation(); e.preventDefault();
					})
					$(this).on('click','tbody .del-button',function() {
						var id = $(this).parents('tr').addClass('select2del').attr('id').replace(t.tname+'_','');
						$.popupForm({
							data: 'go='+t.module+'&do=remove&id='+id+'&table='+tname,
							obj: table,
							onsubmit: proc,
							oncancel: function(){$(table).find('.select2del').removeClass('select2del')},
							loader: opt.loader
						});
						return false;
					})
				}
				var btnClick = function(e) { // Добавляем обработку нажатия кнопки создания записи
					var q = $.parseQuery($(this).parents('div.tablecontainer').attr('query')), d;
					if(!q) q = {};
					q.go = t.module; q.do = 'new'; q.table = tname;
					if(f) q.id = $(f).find('[name=id]').val();
					else q.id = 'new';
					$.popupForm({
						data: $.mkQuery(q),
						obj: table,
						onsubmit: proc,
						loader: opt.loader
					});
					return false;
				}
				if(f)
					$($(this).parents('fieldset')[0]).find('.add-button').click(btnClick); // если таблица в субформе
				else
					$('.add-button',obj).on('click',btnClick);
				$(this).on('contextmenu','tbody tr',function(e) {
					e.preventDefault();
					if(m){
						var id = $(this).addClass('select2del').attr('id');
						$.contxMenu(m,[e.pageX,e.pageY],id)
					}
				})
				$(this).on('mouseup','tbody tr',function(e) {
					if(e.which == 2 && t.target=='html') {
						e.stopPropagation();
						var tr = this, id = $(tr).attr('id').replace(t.tname+'_',''), url;
						url = $.paramToURL({go:t.module,'do':'show',id:id,table:tname});
						window.open(url,'_blank');
						return false;
					}
				})
				$(this).on('mousedown','tbody tr',function(e) {
					if(e.button == 0) this.clickOn = true;
				})
				$(this).on('mousemove','tbody tr',function(e) {
					if(this.clickOn) this.clickOn = false;
				})
				$(this).on('mouseup','tbody tr',function(e) {
					if((!this.clickOn && e.button==0) || e.button!=0) return true;
					var tr = this, id = $(tr).attr('id').replace(t.tname+'_',''),
						f = $(this).parents('form');
					if(t.target=='form') { // Вешаем всплывающую форму на щелчек по строке
						$(tr).addClass('select2del')
						$.popupForm({
							name: t.tname+'Form', 
							data: 'go='+t.module+'&do=edit&id='+id+'&table='+tname,
							obj: table,
							onsubmit: proc,
							oncancel: function(){
								$(table).find('.select2del').removeClass('select2del')
								if(table._reload) table._reload()
							},
							loader: opt.loader
						});
						return false;
					}else if(t.target=='doc') {
						window.open('docpdf.php?id='+id+'&table='+tname,'_blank');
						return false;
					}else if(t.target=='html') {
						window.open($.paramToURL({go:t.module,'do':'show',id:id,table:tname}),'_self');
						return false;
					}else if(t.target=='select') {
						if(table['_select']){
							if(table._select(id)) $(tr).addClass('selected');
							else $(tr).removeClass('selected');
						}
						return false;
					}else if(t.target=='map') {
						if(map) {
							if(typeof ldr !== 'object') ldr = $.loader();
							ldr.get({
								data: 'go='+t.module+'&do=position&id='+id+'&table='+tname,
								onLoaded: function(d) {
									f.find('#cancelbutton').click()
									if(d['select']) {
										objects.Select(objects.getObjectByID(d.select))
										if(d.device){
											var el = M.getTreeNodeByType('device',d.device);
											if(el) el.show();
											else onOpenNode = function(){
												onOpenNode = false;
												var el = M.getTreeNodeByType('device',d.device);
												el.show();
											}
												
										}
									}else if(d['position']) {
										map.setView([d.position.lat,d.position.lng],d.position.zoom)
									}
								}
							})
						}
						return false;
					}else if(t.target.match(/\.php$/)){
						window.open(t.target+'?go='+t.module+'&id='+id+'&table='+tname,'_self');
						return false;
					}
				})
			}
		}
	})
}

M = {};
window.M = M;
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
	clone: function(o){
		var n = {}, i;
		for(i in o)
			if(typeof o[i] !== 'function') n[i] = o[i];
		return n;
	},
	compare: function(o,n){
		var cmp={}
		if(typeof(o)=='object' && typeof(n)=='object') {
			for(var i in n) if(i in o && o[i]!=n[i]) cmp[i]=n[i]
		}
		return cmp
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
		var el = $('<'+tagName+'>');
		if (className) el.addClass(className);
		if (container) el.appendTo(container);
		return el.get(0);
	}
}

M.extend = M.Util.extend;
M.setOptions = M.Util.setOptions;
M.stamp = M.Util.stamp;
M.clone = M.Util.clone;
M.compare = M.Util.compare;

M.Class = function () {};
M.Class.extend = function (props) {
	var parent = this;
	var Child = function(){
		if (this.initialize) {
			this.initialize.apply(this, arguments);
		}
	};
	var F = function(){};
	F.prototype = parent.prototype;
	var proto = new F();
	proto.constructor = Child;
	Child.prototype = proto;
	for (var i in parent) {
		if (this.hasOwnProperty(i) && i !== 'prototype') {
			Child[i] = parent[i];
		}
	}
	if (props.statics) {
		M.extend(proto, props.statics);
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
	Child.__super__ = parent.prototype;
	return Child;
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

M.mkGUID = function(){
	var S4 = function () {
		return Math.floor(
			Math.random() * 0x10000 /* 65536 */
		).toString(16);
	};
	return S4() + S4() + "-" + S4() + "-" + S4() +
			"-" + S4() + "-" + S4() + S4() + S4();
};


M.Messendger = M.Class.extend({
	initialize: function(param){
		var to;
		self = this;
		this._list = [];
		this.queue = [];
		this.visible = [];
		this._counter = 0;
		this.contains = 0;
		this.members = {};
		this.options = {
			max:3,
			gap:30,
			position: {right:"30px", bottom:"30px"},
			interval: {show:15000, hide:200, slide:400, pause:1000}
		}
		if(typeof(param)=='object'){
			this.options = M.extend(this.options,param);
		}
		document.addEventListener("DOMContentLoaded", function(e){ 
			self._oplist = $('<div class="oplist" style="display:none">')
			.on('mouseenter',function(e){ clearTimeout(to); })
			.on('mouseleave',function(e){
				to = setTimeout(function(){ self.hideOpList() },300)
			}).appendTo('body').get(0);
			$('.topheader #operator').on('mouseenter',function(e){
				if(to) clearTimeout(to);
				if(!$(self._oplist).is(':visible')) self.showOpList();
			}).on('mouseleave',function(e){
				to = setTimeout(function(){ self.hideOpList() },300)
			}).on('click',function(e){ e.replyAll=1; self.messageForm(e); });
		}, false);
	},
	hideOpList: function(){
		var self = this;
		$(self._oplist).fadeOut(400,function(){$(self._oplist).empty()})
	},
	showOpList: function(){
		var self = this;
		if(!self.fillMembers()) return;
		$(self._oplist).fadeIn(200);
	},
	delivery: function(el){
		if(!el.id || !el.status || el.status > 2) return;
		var m = {wstype:'delivery',id:el.id,result:'OK',window:M.storage._tab};
		if(M.sock) M.sock.send(m);
		else M.storage.set('mkSendMessage',m)
	},
	getMembers: function(field){
		var members = M.storage.get('chatMembers'), m = {}, o, l, k;
		if(typeof members != 'object') return (field)? m : false;
		k = Object.keys(members);
		if(!field) return (k.length==0)? false : members;
		for(l in members){
			o = members[l];
			if(o.login && o[field])
				m[o.login] = o[field];
		}
		return m;
	},
	fillMembers: function(){
		var self = this, o, login, members = this.getMembers();
		if(!members) return false;
		for(login in members){
			o = members[login];
			$('<div class="member" login="'+o.login+'"><img class="photo" src="'+o.photo+'"><div class="fio">'+o.fio+'</div></div>')
			.on('click',function(e){
				e.sendTo = $(e.currentTarget).attr('login');
				self.messageForm(e)
			}).appendTo(self._oplist);
		}
		return true;
	},
	mkHistoryElem: function(v,elem,sender,pos){
		if(typeof v != 'object') return;
		var rd=new Date(), day, t, d, el, utc; rd.setUTC(v.send);
		d = Math.floor(rd.getUTC()/86400)*86400; utc=rd.getUTC();
		day=elem.find('div[utc='+d+']');
		if(!day[0]){
			day=$('<div utc="'+d+'"><div class="date"><em>'+rd.RuDate()+'</em></div></div>');
			if(!pos) elem.prepend(day); else elem.append(day);
		}
		if(typeof v.sender == 'object') t=v.sender.login==sender? 'send':'reciv';
		else t=v.sender==sender? 'send':'reciv';
		el = $('<div utc="'+utc+'"><div class="message" type="'+t+'">'+v.message+'<div class="time">'+rd.RuTime()+'</div></div></div>');
		if(!pos) day.after(el); else elem.append(el);
	},
	messageForm: function(e){
		var self = this, l, mbrs={}, v=e.sendTo, mb = this.getMembers('fio'), f;
		ldr.get({data:'go=operators&do=get',onLoaded: function(d){
			mbrs = d.list; self.sender = d.sender;
			for(l in mb) if(!mbrs[l]) mbrs[l] = mb[l];
			f = $.popupForm({data:{
				form:{name:'messages',focus:'message',force_submit:1,style:'width:450px;padding:12px;border:rgba(0,0,0,0.3) solid 1px;border-radius:12px',fields:{
					login:{label:'получатель',type:'select',list:mbrs,value:v,onchange:1},
					history:{label:'',type:'nofield',style:'width:440px;height:400px;overflow:auto;background-color:#fff;padding:5px',native:false},
					message:{label:'',style:'width:430px;height:80px',type:'textarea',value:""}},
					footer:{cancelbutton:{txt:'Закрыть'},submitbutton:{txt:'Послать'}}
				}},
				submit:function(d){
					var m, q = $.parseQuery(d);
					q.message = decodeURIComponent(q.message);
					if(!q.message) return;
					m = {wstype:'message',to:q.login,message:q.message,guid:M.mkGUID()};
					if(M.sock) M.sock.send(m);
					else M.storage.set('mkSendMessage',m);
					if(e.replyOn) self.hide(e.replyOn);
					$('form[name=messages]').find('[name=message]').val('');
				},
				destroy:false
			});
			f.find('[name=login]').change(function(e){
				var to=$(this).val(), h=f.find('[name=history]'); h.empty(); f.scrl = false;
				if(to=='all' || to=='ALL'){ h.hide(); return; }else h.show();
				ldr.get({
					data:'go=operators&do=history&to='+to,
					onLoaded:function(d){
						for(k in d.list) self.mkHistoryElem(d.list[k],h,d.sender);
						var ah=h[0].scrollHeight;
						h.scrollTop(ah);
						h.unbind('scroll');
						h.on('scroll',function(e){
							if(!f.scrl) return;
							if(h[0].scrollTop == 0){
								f.scrl=false;
								var utc=h.find('div.message').first().parent().attr('utc');
								if(utc) ldr.get({
									data:'go=operators&do=history&to='+to+'&send='+utc,
									onLoaded:function(d){
										for(k in d.list) self.mkHistoryElem(d.list[k],h,d.sender);
										h.scrollTop(h[0].scrollHeight - ah);
										ah=h[0].scrollHeight;
										f.scrl=true;
									}
								})
							}
						});
						f.scrl=true;
					}
				})
			}).change();
		}});
	},
	add: function(m){
		var i, self = this, o = this.options, members = this.getMembers()||{}, el, h, to;
		h = $('form[name=messages]').find('[name=history]');
		to = $('form[name=messages]').find('[name=login]').val();
		if(!m.wstype){
			console.log("Messendger.add: Invalid message ",m);
			return false;
		}
		if(m.wstype == 'delivery'){
			console.log('Messendger.add: recive delivery '+(m.id?'id: '+m.id:""));
			for(i in this._list) {
				el = this._list[i];
				if(el.id && m.id && m.id == el.id){
					el.status = m.status;
				}
			}
			if(this.sender && m.dbrow && to==m.dbrow.to && h.is(':visible')){
				self.mkHistoryElem(m.dbrow,h,this.sender,1);
				h.scrollTop(h[0].scrollHeight);
			}
		}else{
			if(m.wstype=='message' && this.sender && to==m.sender.login && h.is(':visible')){
				if(this.sender != to){
					self.mkHistoryElem(m,h,this.sender,1);
					h.scrollTop(h[0].scrollHeight);
				}
				this.delivery(m);
				M.storage.set('mkReciveMessage',""); // мессенжер в текущем окне принял сообщение
				return true;
			}
			if(document.hidden){
				console.log('Messendger.add: message ('+(m.id?"id: "+m.id:m.message)+') document hidden');
				return false;
			}
			if(this.contains >= o.max){
				console.log('Messendger.add: message ('+(m.id?"id: "+m.id:m.message)+') slot full');
				return false;
			}
			this.contains++;
			el = {id:m.id,num:this._counter++,content:m.message.replace(/\n/g,'<br>'),status:1,msg:m};
			console.log('Messendger.add: input message  N '+(el.num+1)+'  ('+(m.id?"id: "+m.id:m.message)+')');
			if(m.wstype == 'message') el.sender = m.sender;
			if(m.send) el.send = m.send; else el.send = Math.floor(o.interval.show/1000);
			this._list[el.num] = el;
			el.div = this.create(el);
			if(this.visible.length == 0 || this.forward){
				if(this.forward) delete this.forward;
				console.log('Messendger.add: '+m.wstype+' ('+(el.id?"id: "+el.id:"N "+(el.num+1))+') showed');
				this.show(el);
			}else{
				console.log('Messendger.add: message ('+(el.id?"id: "+el.id:"N "+(el.num+1))+') queued');
				this.queue.push(el);
			}
		}
	},
	create: function(el){
		var
		self = this, o = this.options, p = o.position, k = Object.keys(p), s = el.sender, div, tp="";
		if(el.msg.wstype == 'notify') tp=' type="notify"';
		if(el.msg.type) tp = ' type="'+el.msg.type+'" ';
		
		div = $('<div class="message" num="'+el.num+'" id="'+el.id+'"'+tp+'><div class="button" id="close">x</div>'
		+((s)?'<img class="photo" src="'+((s.photo)? s.photo:"pic/unknown.png")+'"><div class="sender" sender="'+s.login+'"><span>'
		+s.fio+'</span></div>':'')+'<div class="content">'+el.content+'</div><div class="send">'+el.send+'</div></div>')
		.css({display:"none"}).css(k[0],p[k[0]]).css(k[1],p[k[1]]).appendTo('body').get(0);

		if(el.msg.wstype == 'message') $(div).on('click',function(e){
			e.sendTo = $(e.currentTarget).find('[sender]').attr('sender');
			e.replyOn = el;
			self.hide(el);
			self.messageForm(e);
		})
		if(el.msg.wstype == 'notify'){
			$(div).on('mouseenter',function(){ self.pause(el); })
			$(div).on('mouseleave',function(){ self.resume(el); })
		}
		$(div).find('#close.button').on('click',function(e){
			e.stopPropagation();
			e.preventDefault();
			self.hide(el);
		})
		console.log('Messendger.create: '+el.msg.wstype+' ('+(el.id?"id: "+el.id:"N "+(el.num+1))+') create DOM element');
		return div;
	},
	move: function(l,delta){
		if(Math.abs(delta)<1 || !l.length) return;
		var self = this, o = this.options, b;
		l.forEach(function(el){
			var p = $(el.div).css('bottom').replace(/[^\d]/g,"")*1;
			b = p + delta;
			$(el.div).animate({bottom:b}, o.interval.slide);
		})
	},
	show: function(el){
		if(!el || typeof el != 'object' || !('num' in el)) return;
		var self = this, o = this.options, i, len = this.visible.length, l = [];
		if(this.removed){ len = this.removed.pos; delete this.removed; }
		for(i=0; i < len; i++) l.push(this._list[this.visible[i]]);
		self.visible.unshift(el.num); // добавляем сообщение в видимые
		this.move(l, $(el.div).outerHeight() + o.gap);
		console.log('Messendger.show: ('+(el.id?"id: "+el.id:"N "+(el.num+1))+') visible '+this.visible.length+' contains '+this.contains);
		console.log('Messendger.show: clear mkReciveMessage ('+(el.id?"id: "+el.id:"N "+(el.num+1))+')');
		M.storage.set('mkReciveMessage',""); // мессенжер в текущем окне принял сообщение
		$(el.div).fadeIn(o.interval.slide,function(){
			var next = self.queue.shift();
			if(next){
				console.log('Messendger.show: next ('+(next.id?"id: "+next.id:"N "+(next.num+1))+') from queue');
				setTimeout(function(){ self.show(next); },o.interval.slide+o.interval.pause);
			}else if(self.contains < o.max){
				self.forward = 1;
			}
			if(el.msg.wstype == 'notify'){
				el.onhide = self.options.interval.show/1000;
				el.tmout = true;
				el.tmint = setInterval(function(){
					if(el.tmout){
						if(el.onhide>=0) $(el.div).find('.send').html(el.onhide);
						else self.hide(el);
						el.onhide--;
					}
				},1000);
			}
		});
	},
	hide: function(el){
		var self = this, o = this.options, p = $(el.div).position(),
		H = $(el.div).outerHeight() + o.gap;
		if(el.tmint){ clearInterval(el.tmint); el.tmint = undefined; }
		$(el.div).fadeOut(o.interval.slide, function(){
			var i, n, l = [], next = M.storage.get('mkReciveMessage');
			if((n = self.visible.indexOf(el.num)) != -1){
				el.pos = n;
				self.visible.splice(n,1);
				console.log('Messendger.hide: ('+(el.id?"id: "+el.id:"N "+(el.num+1))+
					') visible['+el.pos+'] next '+(next?"("+next.message+") ":"empty"));
				self.forward = 1;
				if(!next){
					for(i=n; i<self.visible.length; i++)
						l.push(self._list[self.visible[i]]);
					console.log('Messendger.hide: set forward, visible '+self.visible.length+' contains '+self.contains);
				}else self.removed = el;
				if(l.length>0) self.move(l,(-1)*H);
			}
			self.remove(el);
			if(next) self.add(next);
		});
	},
	pause: function(el){
		el.tmout = false;
	},
	resume: function(el){
		el.tmout = true;
	},
	remove: function(el){
		this.delivery(el);
		$(el.div).remove();
		this.contains--;
		console.log('Messendger.remove: ('+(el.id?"id: "+el.id:"N "+(el.num+1))+') visible '+this.visible.length+' contains '+this.contains);
	}
});

M.msgr = new M.Messendger();

M.WSocket = M.Class.extend({
	initialize: function(param){
		Object.defineProperty(this,"state",{enumerable:true,get:this.State,set:this.State});
		this._state = 0;
		this.in = 0;
		this.out = 0;
		this.attempts = 0;
		this.recvMsg = [];
		this.sendCache = [];
		this.options = {
			url: 'ws://localhost:32180/ws',
			interval: 15000
		}
		if(typeof(param)=='object'){
			this.options = M.extend(this.options,param);
		}else if(typeof(param)=='string'){
			M.extend(this.options,{url:param});
		}
		M.storage.set('wsClientState',0);
		this.open(this.options.url);
	},
	State: function(s){
		if(arguments.length == 0) return this._state;
		if(s != this._state){
			this._state = s;
			var site = M.storage.get('wsClient');
			if(site == window.name) M.storage.set('wsClientState',this._state);
			if(s == 6) this.ws.close();
		}
	},
	open: function(url){ // создаёт WebSocket и обравотку событий
		var sock = this;
		if(this.ws) this.ws.close();
		this.ws = new WebSocket(url);
		this.ws.onopen = function(e){
			sock.onOpen(e);
		}
		this.ws.onmessage = function(incoming,flags){
			var act;
			sock.in++;
			try{
			if((JSON.parse(incoming.data).wstype)=='disconnect'){
				delete M.sock;
				sock.close();
				return;
			}}catch(e){}
			sock.onMessage(incoming.data,flags,sock.in);
		}
		this.ws.onclose = function(e){
			if(sock._state == 6) return true;
			sock.onClose(e);
			sock.reConnect(e);
		}
		this.ws.onerror = function(e){
			switch (this.readyState){
			case this.CLOSED:
				sock.reConnect(e);
				break;
			}
			sock.onError(e);
		}
	},
	close: function(){
		this.State(6);
	},
	send: function(m){ // посылает сообщение серверу если сокет открыт
		var mo;
		if(this.ws && this.ws.readyState == this.ws.OPEN){
			try{
				while(mo = this.sendCache.shift()){
					this.ws.send(JSON.stringify(mo));
					this.counter++;
					console.log("WSClient send cached message N "+(this.out++),m);
				}
				if(m && m!='ping'){
					this.ws.send(JSON.stringify(m));
					this.counter++;
					console.log("WSClient send message N "+(this.out++),m);
				}
				if(M.storage) M.storage.set('mkSendMessage',""); // даём знать что сообщение отправлено
			}catch (e){
				this.ws.emit('WSClient: send error for message N '+(this.out),m,e);
			}
		}else{
			console.log("WSClient cached message ",m);
			this.sendCache.push(m);
			if(M.storage) M.storage.set('mkSendMessage',""); // даём знать что сообщение ушло
		}
	},
	onOpen: function(e){
		var sock = this;
		console.log("WSClient: open "+window.name+" "+this.options.url);
		this.state = 1;
		this.attempts = 0;
		if(this.sendCache.length > 0) this.send();
	},
	onError: function(e){
		this.state = 4;
		console.log("WSClient: error ",e);
	},
	onClose: function(e){
		this.state = 3;
		console.log("WSClient: close ",e);
	},
	onMessage: function(data,flags,number){
		var m, old, audio, audio, self=this, mt = {message:1,notify:1};
		if(!M.storage){console.log("WSClient: ERROR! No Storage"); return false; }
		try { m = JSON.parse(data) } catch(e) { m = {wstype:'error',message:data}; }
		if(!m){ console.log("WSClient: recive message N "+number+" has no data"); return; }

		if(m.wstype == 'members'){
			var members = M.storage.get('chatMembers');
			if(!members) members = {};
			if(m.add) M.extend(members,m.add);
			if(m.del) for(i in m.del) if(members[i]) delete members[i];
			M.storage.set('chatMembers',members);
		}else if(mt[m.wstype]){
			if(old = M.storage.get('mkReciveMessage')){ // предыдущее сообщение не обработано
				console.log('WSClient: Message place in cache ('+this.recvMsg.length+') ',m);
				this.recvMsg.push(m);
			}else{
				console.log('WSClient: Message place in mkReciveMessage',m);
				M.storage.set('mkReciveMessage',m);
			}
			if((m.wstype == 'message' || m.wstype == 'notify') && !this.play){
				this.play = 1;
				audio=new Audio(); audio.src='ding.mp3';
				audio.addEventListener('ended',function(){delete self.play;});
				audio.play();
			}
		}else{
			M.storage.set('mkReciveData',m);
		}
	},
	nextMessage: function(){
		if(old = this.recvMsg.shift()) {
			console.log('WSClient: nextMessage place in mkReciveMessage',old);
			M.storage.set('mkReciveMessage',old);
		}
	},
	ping: function(){
		this.send('ping');
	},
	reopen: function(){
		if(this.state == 6) return false;
		console.log("WSClient: reconnecting "+((this.attempts)? "("+this.attempts+")":""));
		$('#operator').removeClass('error');
		delete this._timeOut;
		this.attempts++;
		this.open(this.options.url);
	},
	reConnect: function(e){ // пересоединяет сокет при разрыве соединения
		if(this.state == 6) return false;
		this.state = 5;
		if(!this.options.interval) return false;
		if(this._timeOut) return false;
		console.log(`WebSocket: retry in ${this.options.interval}ms`,e);
		var sock = this;
		if(this.attempts == 0) this._timeOut = setTimeout(function(){sock.reopen()},3000)
		else this._timeOut = setTimeout(function(){sock.reopen()},this.options.interval);
	}
	
})

M.Cookie = M.Class.extend({
    options:{
		use_timeout: false,
		cookieOpt: { expires: 365, path: '/' },
		timeout: false,
		name: 'config',
		data: false,
		log: false
	},
	initialize: function(param){
		if(typeof(param)=='object') M.extend(this.options,param);
		else if(typeof(param)=='string') this.options.name = param;
		this._load();
	},
	_load: function(){
		var o = this.options, 
			c = '('+$.cookie(o.name)+')';
		this.log('load cookie='+c);
		try { o.data = eval(c) } catch(e) {o.data = false}
		if(typeof o.data !== 'object') {
			this.log('_load: data is empty');
			o.data = {}
			this.saveNow();
		}
	},
	get: function(name){
		var o = this.options;
		if(!o.data) {
			this.log('get: data is empty');
			this._load();
		}
		return o.data[name];
	},
	save: function(name,val){
		var  c = this, o = this.options;
		if(typeof val === 'object' && typeof o.data[name] === 'object') {
			o.data[name] = $.extend(o.data[name],val)
		}else{
			o.data[name] = val;
		}
		if(o.use_timeout) {
			clearTimeout(o.timeout);
			o.timeout = setTimeout(function(){
				c.saveNow();
			}, 20000);
		}else{
			c.saveNow();
		}
	},
	saveNow: function(){
		var o = this.options;
		if(o.use_timeout && o.timeout) clearTimeout(o.timeout);
		$.cookie(o.name, $.toJSON(o.data), o.cookieOpt);
		this.log('save: data is saved');
	},
	del: function(name){
		var  o = this.options;
		if(!(o.data[name])) {
			this.log('del: '+name+' not in data');
			return false;
		}
		delete o.data[name];
		if(o.use_timeout && o.timeout) clearTimeout(o.timeout);
		this.saveNow();
		this.log('del: '+name);
		return true;
	},
	log: function(s) {
		if(typeof this.options.log === 'function') this.options.log(s);
	}
});

M.conf = new M.Cookie('usrconfig');

M.Storage = M.Class.extend({
	initialize: function(param){
		this.state = "created";
		this.watch = {};
		this.initDoc = false;
		this._storage = this.getStorage();
		this._sessid = $.cookie('PHPSESSID'); // запоминает сессию
		this._tab = "GUID-" + M.mkGUID(); // формирует название окна
		window.name = this._tab;
		console.log("window.name = "+window.name);
	},
	getStorage: function(){ // задаёт обработку событий для хранилища
		if(!window['localStorage']) {
			console.log('WARNING localStorage not in window !!!')
			return {};
		}
		var self = this;
		window.addEventListener("storage", function(e){ self.onStorageSaved(e) }, false);
		window.addEventListener("beforeunload", function(e){ self.closeTab(e) }, false);
		document.addEventListener("visibilitychange", function(e){
			var s=document.visibilityState;
			if(s==='visible') self.focusTab(e);
			else if(s==='hidden') self.blurTab(e);
		});
		document.addEventListener("DOMContentLoaded", function(e){ self.initDocument(e) }, false);
		return window.localStorage;
	},
	initDocument: function(e) { // выполняется при загрузке страницы
		this._storage['currentWindow'] = this._tab;
		this.state = "init";
		var name, sid = this.get('session');
		if(sid == '' || (sid != this._sessid && this._sessid != '')) {
			this.set('session', this._sessid);
			for(name in this.watch) this.del(this.watch[name].type)
		}
		for(name in this.watch) this.initWatch(name,this.watch[name]);
		this.initDoc = true;
	},
	blurTab: function(e){
		this.set("currentWindow","");
	},
	closeTab: function(e) { // выполняется при закрытии страницы
		var name, self = this;
		this.state = "beforeunload";
		for(name in this.watch){
			var o = this.watch[name];
			if(this._tab == this.get(o.type)){
				if(o.ismain()){
					if(o.closenotify) o.closenotify();
					this.del(o.type);
				}
			}
		}
	},
	focusTab: function(e){ // выполняется при получении страницей фокуса
		this.state = "focused";
		this.set("currentWindow",this._tab)
		var name;
		for(name in this.watch){
			var o = this.watch[name];
			if(o.focus) o.focus(o.ismain());
		}
	},
	addWatch: function(name,data){ // добавляет остлеживание, должен иметь {type, ismain} может не иметь {openmain, focus, handler, onchange}
		this.watch[name] = data;
		if(this.initDoc) this.initWatch(name,data);
	},
	initWatch: function(name, wo){
		if(wo.type && !this.get(wo.type) && wo.init)
			wo.init(this);
		if(wo.oninit) wo.oninit(this);
		if(wo.ismain())
			this.set(wo.type,this._tab);
	},
	onStorageSaved: function(e){ // выполняется при изменении отслеживаемого параметра
		var nv;
		try{ nv = JSON.parse(e.newValue) }catch(err){ nv = e.newValue; }
		if(e.key && e.key in this.watch){
			if(this.watch[e.key].ismain() && this.watch[e.key].handler){ // выполняется для главного окна
				this.watch[e.key].handler(nv)
				this.del(e.key);
			}
			if(this.watch[e.key].onchange){ // выполняется для подчиненного окна
				this.watch[e.key].onchange(nv)
			}
		}
	},
	get: function(name){ // получает данные из хранилища
		if(!name) return "";
		var data, c = this._storage[name];
		try { data = JSON.parse(c) } catch(e) { data = c; }
		return data;
	},
	set: function(name, val){ // записывает данные в хранилище
		if(this._storage[name] == val) return;
		var o;
		if(typeof val !== 'string') val = JSON.stringify(val);
		this._storage[name] = val;
		if(name in this.watch){
			o = this.watch[name];
			if(!this.get(o.type) && o.openmain)
				o.openmain(val); // выполняется если нет главного окна
			else if(this._storage[o.type] == this._tab && o.onchange)
				this.onStorageSaved({newValue:val,key:name}) // выполняется в главном окне
		}
	},
	del: function(name){ // удаляет параметр из хранилища
		if(!this._storage[name]) return false;
		if(this._storage.removeItem)
			this._storage.removeItem(name);
		else
			delete this._storage[name];
		return true;
	}
});
M.storage = new M.Storage();
M.storage.addWatch('mapSearch',{
	type: 'mapWindow',
	init: function(){
		if($('#map').length>0){
			window._blinkLogo = [$('head link[rel$=icon]').attr('href'),'pic/warn16.png'];
			window._title = document.title;
		}
	},
	ismain: function(){
		return ($('#map').length>0)? true:false;
	},
	openmain: function(val){
		this.mapWindow = window.open('maps.php?select='+val,'_map');
	},
	focus: function(main){ // выполняется для окна при получении им фокуса
		if(!main) return;
		if(window._blink){
			clearInterval(window._blink);
			document.title = window._title;
			delete window._blink;
			$('head link[rel$=icon]').attr({href:window._blinkLogo[0]})
		}
	},
	handler: function(id){
		console.log("handler: 'id' => '"+id+"'");
		var o, c, i = 0, a = window._blinkLogo, audio;
		if(!window.objects) return false;
		if(o = objects.getObjectByID(id)){
			if(!map.hasLayer(o)) map.addLayer(o);
			c = (o.getBounds)? o.getBounds().getCenter() : o.getLatLng();
			map.setView(c,16);
			if(!objects.isSelected(o)) objects.Select(o);
			else objects.deSelect(o).Select(o);
			if(o.feature.properties['address'])
				document.title = o.feature.properties.address + window._title.replace(/.* - /,' - ');
			audio=new Audio(); audio.src='ding.mp3'; audio.play();
			if(a) {
				if(window._blink) clearInterval(window._blink);
				$('head link[rel$=icon]').attr({href:a[1]})
				window._blink = setInterval(function(){
					$('head link[rel$=icon]').attr({href:a[i++ % 2]})
				}, 300);
			}
		}
	}
})
M.openws = function(){
	if(M.sock) return M.sock;
	M.storage.set('wsClient',window.name);
	M.storage.del('mkSendMessage')
	M.storage.del('mkReciveMessage')
	M.storage.set('chatMembers',"");
	var url = window.location.href;
	url = url.replace(/.*\/\/([^\/]*).*/,"$1");
	M.sock = new M.WSocket('wss://'+url+':1380/'+$.cookie('PHPSESSID'));
}
if(SocketEnable) M.storage.addWatch('wsClientState',{
	type: 'wsClient',
	oninit: function(st){
		var wsc = M.storage.get('wsClient'), c=['connect','connected','','','error','connect','offline'];
		if(wsc && window.name != wsc){
			M.storage.set('mkSendMessage',"ping")
			setTimeout(function(){
				if(M.storage.get('mkSendMessage') == 'ping'){
					console.log('wsClientState: no process PING!  WebSocket open')
					M.openws();
				}else $('.topheader #operator').attr('class',c[M.storage.get('wsClientState')]);
			},500);
		}
	},
	ismain: function(){ return false; },
	onchange: function(val){
		if(M.storage._sessid != M.storage.get('session') && M.sock){
			window.open(window.location.href,'_self');
			return;
		}
		console.log('wsClientState change to '+val);
		var o = $('.topheader #operator'), pause=3000;
		if(M.sock && val != 1) M.storage.del('chatMembers')
		switch(val){
		case 0:	o.attr('class','connect');
			break;
		case 1:
			if(M.sock) o.attr('class','online');
			else o.attr('class','connected');
			break;
		case 4:	o.attr('class','error');
			break;
		case 5:	o.attr('class','connect');  // reconnecting
			break;
		case 6:
			o.attr('class',"offline");
			if(M.storage.get('wsClient') == window.name){
				M.storage.set('wsClient',"");
				pause = 8000;
			}
			if(!M.sock) setTimeout(function(){
				if(!M.storage.get('wsClient')) M.openws();
			},Math.floor(Math.random()*5000)+pause);
			break;
		default:
			o.attr('class','default');
		}
	}
})
if(SocketEnable) M.storage.addWatch('mkSendMessage',{
	type: 'wsClient',
	init: function(st){ M.openws(); },
	ismain: function(){
		return M.storage.get('wsClient') == window.name;
	},
	closenotify: function(){
		M.sock.close();
		M.storage.set('wsClient',"");
		M.storage.set('chatMembers',"");
	},
	openmain: function(val){ M.openws(); },
	handler: function(m){
		M.sock.send(m);
	}
})
M.storage.addWatch('mkReciveMessage',{
	type: 'wsClient',
	ismain: function(){
		return M.storage.get('wsClient') == window.name;
	},
	focus: function(){
		var data, m = M.storage.get('mkReciveMessage');
		if(m){
			try { data = JSON.parse(m) } catch(e) { data = m; }
			M.msgr.add(data);
		}
	},
	onchange: function(m){
		var data;
		try { data = JSON.parse(m) } catch(e) { data = m; }
		if(!data && M.storage.get('wsClient') == window.name) M.sock.nextMessage();
		if(!data || typeof data != 'object' || !data.wstype) return false;
		else M.msgr.add(data);
	}
})
M.storage.addWatch('mkReciveData',{
	type: 'wsClient',
	ismain: function(){
		return M.storage.get('wsClient') == window.name;
	},
	onchange: function(m){
		var data;
		try { data = JSON.parse(m) } catch(e) { data = m; }
		if(!data || typeof data != 'object' || !data.wstype) return false;
		if(data.wstype == 'logout') window.open($.paramToURL({go:'logout'}),'_self');
		else if(m.wstype == 'delivery' && M.msgr) M.msgr.add(m);
		else if(M.objectProcess) M.objectProcess(data)
	}
})

// Объект формурующий поле
M.Field = M.Class.extend({
	statics:{
		types: {hidden:'text',password:'text',ac:'autocomplete'}
	},
	options:{},
	initialize: function(param){
		if(typeof(param)=='object')
			M.extend(this.options,param);
		for(var n in this.types)
			this['_'+n] = this['_'+this.types[n]];
	},
	make: function(o){
		var d;
		if(typeof o !== 'object') 
			return $('<span class="field">error</span>');
		var el = $('<div>').addClass('filter-item');
		if(o.native) 
			delete o.native;
		if(o.label){
			el.append($('<span>').addClass('label').html(o.label));
			delete o.label;
		}
		if(o.type && typeof this['_'+o.type] === 'function')
			el.append($('<span>').addClass('field').append(d = this['_'+o.type](o)));
		if(o.type == 'hidden') el.hide();
		if(d && o['style']) d.attr({style:o.style});
		return el;
	},
	_text: function(o){
		return $('<input>').attr(o);
	},
	_checkbox: function(o){
		if(f.value>0) o.checked=true; else o.checked=false;
		delete o.value;
		tmp=$('<input>').attr(o)
	},
	_date: function(o){
		o.type='text';
		return $('<input>').attr(o).addClass('dateselect')
	},
	_nofield: function(o){
		delete o.type;
		o.class='nofield';
		var v = o.value;
		if(v) delete(o.value);
		return $('<div>').attr(o).append(v);
	},
	_textarea: function(o){
		delete o.type;
		var v = o.value;
		if(v) delete(o.value);
		return $('<textarea>').attr(o).text(v);
	},
	_select: function(o){
		delete o.type;
		var k, el=$('<select>');
		if(o.list && typeof o.list === 'object') {
			for(k in o.list) {
				if(k==o.value) 
					el.append($('<option>').attr({value:k,selected:true}).html(o.list[k]));
				else 
					el.append($('<option>').attr({value:k}).html(o.list[k]));
			}
			delete(o.list)
			delete(o.value)
		}
		return el.attr(o);
	},
	_autocomplete: function(o){
		o.type = 'text';
		return $('<input>').attr(o).addClass('ac_field');
	},
	_checklist: function(o){
		o.class = 'checklist';
		var ul = $('<ul>'), v = o.value.split(/,/);
		for(var n in o.list) {
			var 
			li = $('<li>'),
			chbox = $('<input>').attr({type:'checkbox',value:n}),
			s = $('<span>').html(o.list[n]);
			if(v.indexOf(n)!=-1) chbox.attr('checked',true)
			li.append(chbox).append(s);
			ul.append(li);
		}
		if(o.list) delete o.list;
		return $('<div>').append($('<img src="pic/conf.png">')).attr(o).append(ul);
	}
})

M.allNodes = [];

M.TreeNode = M.Class.extend({
	statics: {
		className: 'treeNode',
		listShow: 0,
		btn: '<img class="button" src="pic/tree/conf.png">',
		hud: '<div class="hud">',
		hudTitles: {prn:"Печатать",add:"Добавить устройство",del:"Удалить",cfg:"Изменить",mnt:"Мониторинг", kil:"Сбросить",usr:"Уч.запись",go:"Перейти",conn:"Соединить",opn:"Открыть ВСЁ"}
	},
	includes: M.Mixin.Events,
	initialize: function(param){
		this.options = {
			type: 'default',
			data: {},
			key: '', // параметр в data для идентификации узла
			compare: 1, // задаёт функцию сравнения элементов
			compact: 1,
			hold: 1,
			sort: 1,
			hooks: 0, // рисовать линии дерева [+]
			set: 0,
			parent: false,
			loadname: '' // 1 загружать ветку через ajax
		};
		if(typeof(param)=='object'){
			this.options = M.extend(this.options,param);
			if('listShow' in param){
				this.listShow = param.listShow;
				delete param.listShow;
			}
		}else if(typeof(param)=='string'){
			M.extend(this.options,{data:{name:param},key:param});
			if(this.className=='treeNode') this.listShow = 1;
		}
		this.kind = this.className.toLowerCase();
		if(typeof ldr !== 'object') ldr = $.loader();
		this.state = 0;
		var opt = this.options,
		id = M.stamp(this);
		if(typeof opt.parent === 'object')
			this.parent = opt.parent;
		this._elements = [];
		this._sequence = [];
		M.allNodes[id] = this;
		this.applyData(this.options.data);
		if(!('key' in this)){
			if(!('key' in opt)){
				if('name' in opt.data) this.key = opt.data.name;
				else this.key = id;
			}else
				this.key = (opt.key in opt.data)? opt.data[opt.key] : opt.key;
		}
		this.state=0;
	},
	applyData: function(d){},
	makeTitle: function(d){
		var h = ('name' in d)? d.name : 'element '+M.stamp(this);
		return $('<span class="name">').html(h);
	},
	hudButton: function(t,s){
		var d = {src:"pic/tree/"+t+".png"};
		if(s) d.src = s;
		if(t in this.hudQuery) d.qn = t;
		if(t in this.hudTitles) d.title = this.hudTitles[t];
		return $(this.btn).attr(d).get(0);
	},
	makeHud: function(a){
		var i, r = [];
		for(i in this.hudQuery){
			var b = this.hudButton(i)
			if(a && i in a) 
				$(b).attr('src',a[i]);
			r.push(b)
		}
		return r;
	},
	makeQuery: function(){
		if(this.options.query) return this.options.query;
		else if(this.query) return this.query;
		else return false;
	},
	makeDOMElement: function(){
		var li, el = this,
		li = M.DomUtil.create('li', this.className);
		li.treeNode = this;
		$(li).attr('treeNode',M.stamp(this));
		this._header = M.DomUtil.create('div', 'lihead', li);
		$(this._header).append(this.makeTitle(this.options.data));
		this._list = M.DomUtil.create('ul', '', li);
		this.addHook();
		if(this._svg) $(li).append(this._svg);
		if(!this.listShow)
			$(this._list).hide();
		li.addEventListener('click',function(e){
			e.stopPropagation();
			e.preventDefault();
			return el.onClick(e);
		},false)
		return li;
	},
	onButtonClick: function(e){
		if(!this.hudQuery) return false;
		var el = this, o = e.target,
		q = $.parseQuery(this.hudQuery[$(o).attr('qn')]), n, t, f;
		for(n in q) if(q[n] == '' && n in this.options.data) q[n] = this.options.data[n];
		if(q.func){
			f = q.func; delete q.func; q=$.mkQuery(q);
			if(f in this && typeof this[f] == 'function') this[f](q);
		}else if(q.target){
			t = q.target; delete q.target; q=$.mkQuery(q);
			window.open(t+'?'+q,'_blank');
		}else{
			$.popupForm({
				data: $.mkQuery(q),
				onsubmit:function(d){M.objectProcess(d,el)},
				loader:ldr
			})
		}
		return false;
	},
	onClick: function(e){
		var y;
		if($(e.target).hasClass('button')) return this.onButtonClick(e);
		if(this.state == 3) return false;
		e.treeNode = this;
		if(this.state>1){
			this.hideBranch(e);
		}else{
			if(this.doClick) this.doClick(e);
			this.show(e)
		}
		return false;
	},
	getElem: function (key) {	// найти элемент по ключу
		if(!key) return false;
		var elem = {key:key},
		first = 0, last = this._sequence.length - 1,
		pos = this._position(first, last, elem);
		if(pos === false) return false;
		if(pos in this._sequence){
			var el = this._elements[this._sequence[pos]];
			if(el && this._compare(el.key,key)===0){
				return el;
			}else{
				el = this._elements[this._sequence[pos+1]]
				if(el && this._compare(el.key,key)===0)
					return el;
			}
		}
		return false;
	},
	addElem: function(elem){	// добавить к ветви элемент
		if(!(elem instanceof M.TreeNode)) return elem;
		var id = M.stamp(elem), i, l, pos, root = this;
		while(root.parent) root = root.parent;
		if(!('kinds' in root)) root.kinds = {};
		if(elem.options.data.id){ // добалнение в списки по типу объекта
			if(!root.kinds[elem.kind]) root.kinds[elem.kind] = [];
			if(!root.kinds[elem.kind][elem.options.data.id])
				root.kinds[elem.kind][elem.options.data.id] = id;
		}
		if(this.options.hooks && !this.options.node){
			this.options.node=1;
			this.addHook();
		}
		if(this.hasElem(elem)) return elem;
		if(this.state==2) elem.setState(1); else elem.setState(0);
		this._elements[id] = elem;
		var first = 0, last = this._sequence.length - 1;
		if(this.options.sort){
			pos = this._position(first, last, elem);
			if(pos === false) console.log(this.kind+"["+this.key+"] add mismatch elem "+elem.kind);
			if(this.state==2){
				if(pos in this._sequence)
					$(this._elements[this._sequence[pos]]._elem).after(elem._elem);
				else{
					if(this._sequence.length>0 && pos<0)
						$(this._list).prepend(elem._elem);
					else
						$(this._list).append(elem._elem);
				}
			}
		}else{
			pos = last;
			if(this.state==2) $(this._list).append(elem._elem);
		}
		this._sequence.splice(pos + 1, 0, id);
		elem.parent = this;
		if(elem.onAdd) elem.onAdd();
		return elem;
	},
	update: function(d){ // если передвигать элементы по веткам - потребуется переделка
		var el, t, o, id, te, i,
		e = {'old':M.clone(this.options.data),'new':M.compare(this.options.data,d)};
		if(d && typeof d == 'object'){
			this.options.data = M.extend(this.options.data,d);
			$(this._header).empty().append(this.makeTitle(this.options.data));
		}
		if(this.onUpdate && e.new.length>0) this.onUpdate(e);
		return this;
	},
	empty: function() { // удаляет ветвь
		while(this._sequence.length>0){
			this._elements[this._sequence[0]].remove();
		}
		$(this._list).empty();
		if(this.options.hooks) delete this.options.node;
		return this;
	},
	moveTo: function(elem){ // пересаживает ветвь на др. елемент
		if(!(elem instanceof M.TreeNode)) return this;
		var id = M.stamp(this), i, p = this.parent;
		if(p.opened && p.opened == id) p.opened = 0;
		i = p._sequence.indexOf(id);
		if(i>=0) p._sequence.splice(i,1);
		delete p._elements[id];
		elem.addElem(this)
		if(p._sequence.length==0 && p.onEmpty) p.onEmpty();
		return this;
	},
	remove: function () { // удаляет ветвь и себя 
		var id = M.stamp(this), p, i, root = this;
		if(this._sequence.length>0) this.empty();
		$(this._elem).remove();
		p = this.parent;
		if(p instanceof M.TreeNode){
			if(p.opened && p.opened == id) p.opened = 0;
			i = p._sequence.indexOf(id);
			if(i>=0) p._sequence.splice(i,1);
			delete p._elements[id];
			if(p._sequence.length==0 && p.onEmpty) p.onEmpty();
		}
		while(root.parent) root = root.parent;
		if(this.options.data.id){
			if(root.kinds[this.kind] && root.kinds[this.kind][this.options.data.id])
				delete root.kinds[this.kind][this.options.data.id];
		}
		if(this.onRemove) this.onRemove();
		delete M.allNodes[id];
		return this;
	},
	parents: function(s){
		if(this.kind == s) return this;
		else if(this.parent) return this.parent.parents(s)
		else return false;
	},
	hasElem: function(elem){
		if (!elem) { return false; }
		return (M.stamp(elem) in this._elements);
	},
	_compare: function(k1, k2){ /* сравнивает два элемента результат должен быть >0 <0 или 0 */
		var c = false, i;
		if(typeof k1 == 'object'){
			for(var i in k1){
				if(i in k2) c = this._compare(k1[i], k2[i]);
				if(c) return c;
			}
			if(c !== false) return c;
		}
		if(typeof this.options.compare === 'function') return this.options.compare(k1, k2);
		if(k1 === k2) return 0;
		else if(k1 > k2) return 1;
		else return -1;
	},
	_position: function(first, last, elem){ /* возвращает id для _elements который будет предшествовать вставляемому */
		if (first > last) return last;
		if(typeof(elem.key) != typeof(this._elements[this._sequence[first]].key)) return false;
		var middle = Math.floor(first + (last - first) / 2), cmp;
		if (this._compare(elem.key, this._elements[this._sequence[first]].key) < 0)
			return first - 1;
		else if (this._compare(elem.key, this._elements[this._sequence[last]].key) > 0)
			return last;
		else if (last == first + 1)
			return first;
		else if ((cmp = this._compare(elem.key, this._elements[this._sequence[middle]].key)) > 0)
			return this._position(middle, last, elem);
		else if (cmp < 0)
			return this._position(first, middle, elem);
		else return middle;
	},
	locate: function(){ // выполняет скроллинг к элементу
		var 
		top = $(this._elem).offset().top,
		ptop = $(this.parent._elem).offset().top,
		pheight = $(this.parent._elem).height(), // ???
		winheight = $('#targets').height(),
		offset = (top - ptop < winheight/3)? ptop : top;
		$('#targets').animate({scrollTop: $('#targets').scrollTop() - $('#targets').offset().top + offset - 50},300);
	},
	haveUndeployed: function(){
		var i;
		if(this._elements.length && this.state < 2) return true;
		else for(i in this._elements) if(this._elements[i].haveUndeployed()) return true;
		return false;
	},
	expendAll: function(e){
		var i;
		this.show(e);
		for(i in this._elements) this._elements[i].expendAll(e);
	},
	show: function(e){
		if(this.state>1 && e && typeof e === 'function') e();
		if(this.state>1) return this;
		var query = this.makeQuery(), p = this.parent, el = this;
		if(e && typeof e === 'function') this.onExpend = function(){
			e();
			delete this.onExpend;
		}; 
		if(e === undefined || typeof e === 'function') this.afterShow = function(){
			el.locate();
			delete this.afterShow;
		}
		if(p && p.state < 2) if(p) p.show(false); // ???
		if(query){
			if(this.options.hold && this._sequence.length > 0){
				this.showBranch();
			}else{
				if(this._sequence.length > 0) this.empty();
				this.load(query);
			}
		}else if(this._sequence.length>0)
			this.showBranch()
	},
	unhide: function(){ /* для обхода ветви без изменения в листьях текущего состояния */
		var el = this, i;
		if(!el.options.compact) for(i in el._sequence){
			if(el._elements[el._sequence[i]].state > 0) el._elements[el._sequence[i]].unhide();
		}
		if(el.onShow) el.onShow();
	},
	hide: function(){ /* для обхода ветви без изменения в листьях текущего состояния */
		var el = this, i;
		if(!el.options.compact) for(i in el._sequence){
			if(el._elements[el._sequence[i]].state > 0) el._elements[el._sequence[i]].hide();
		}
		if(el.onHide) el.onHide();
	},
	showBranch: function(e){
		var el = this, p = this.parent, o, i, l;
		if((this.state==1 || this.state==3) && this._sequence.length>0) for(i in this._sequence){ /* наполняем список элементами */
			o = this._elements[this._sequence[i]];
			if(o.state==0) if(o.prevState){ o.setState(o.prevState); delete o.setState }else o.setState(1);
			$(this._list).append(o._elem);
		}
		this.setState(3);
		if(p && p.opened){
			if($(p._elements[p.opened]._elem).offset().top < 0)
				$('#targets').animate({scrollTop: $('#targets').scrollTop() - $(p._elements[p.opened]._elem).height() },300);
			if(p.options.compact) p._elements[p.opened].hideBranch();
		}
		if(window.objects && this.mapObject && (l = objects.getObjectByID(this.mapObject)) && !objects.isSelected(l)) objects.Select(l);
		if(el.onShowBranch) el.onShowBranch();
		$(this._list).slideDown(300,function(){
			el.setState(2)
			$(el._elem).addClass('selected');
			if(el.afterShowBranch) el.afterShowBranch();
		})
		return this;
	},
	hideBranch: function(){
		var el = this, l, i;
		if(window.objects && this.mapObject && (l = objects.getObjectByID(this.mapObject)) && objects.isSelected(l)) objects.deSelect(l);
		if(this.state != 3){
			el.setState(3);
			if(this.opened && this.options.compact) this._elements[this.opened].hideBranch()
			$(this._list).slideUp(300,function(){
				if(!el.options.hold && el._sequence.length > 0) el.empty();
				$(el._elem).removeClass('selected');
				el.setState(1);
				for(i in el._sequence){
					if(!el.options.compact) el._elements[el._sequence[i]].prevState = el._elements[el._sequence[i]].state;
					el._elements[el._sequence[i]].setState(0);
				}
				if(el.onHideBranch) el.onHideBranch();
			});
		}
		return this;
	},
	setOpened: function(elem){
		if(!this.hasElem(elem)) return false
		var p = this.parent, eid = M.stamp(elem), id = M.stamp(this);
		if(elem.state==1){
			if(this.opened==eid) this.opened=0;
			return true;
		}else if(elem.state==2 && this.state==2){
			this.opened = eid;
			if(p) p.setOpened(this);
		}
		
	},
	setState: function(state){
		if(state<0 || state>3){
			console.log("error state="+state);
			state = 0;
		}
		if(state>0 && state<3 && !this._elem){
			this._elem = this.makeDOMElement();
			if(this.onCreateElem) this.onCreateElem(this.options.data);
		}
		if(state>0 && state<3) this.unhide();
		if(state==1 && this._list && this.options.compact) $(this._list).empty();
		if(state==0 && this._elem){
			var a = ['_list','_header','_elem'], n;
			if(this.options.compact) for(n in a){ $(this[a[n]]).remove(); this[a[n]] = false; }
			this.hide();
		}
		if(state==2){
			if(this.onExpend) this.onExpend();
			if(this.afterShow) this.afterShow();
			if(this._svg) $(this._svg).addClass('opened');
		}
		var p = this.parent;
		this.state = state
		if(p) p.setOpened(this);
		return this;
	},
	mkSVG: function(tag, attrs) {
		var el= document.createElementNS('http://www.w3.org/2000/svg', tag);
		for (var k in attrs) el.setAttribute(k, attrs[k]);
		return el;
	},
	addHook: function(){
		if(!this.options.hooks) return this;
		if(this._svg) $(this._svg).remove();
		var
		h = $(this._elem).outerHeight(), w = 21,
		o = $('<svg xmlns="http://www.w3.org/2000/svg">');
		if(!h) h = 23;
		if(this.options.node){
			o.css({cursor:"pointer"});
			o.append(this.mkSVG('polyline',{class:"dot",points:"10,0 10,7"}));
			o.append(this.mkSVG('rect',{x:5,y:6,width:10,height:10}));
			o.append(this.mkSVG('polyline',{id:"x",points:"7,11 14,11"}));
			o.append(this.mkSVG('polyline',{id:"y",points:"10,8 10,14"}));
			o.append(this.mkSVG('polyline',{class:"dot",points:"15,11 26,11"}));
			if(!this.options.last) o.append(this.mkSVG('polyline',{id:"d",class:"dot",points:"10,16 10,"+h}));
		}else{
			o.append(this.mkSVG('polyline',{id:"d",class:"dot",points:"10,0 10,"+(this.options.last?10:h)}));
			o.append(this.mkSVG('polyline',{class:"dot",points:"10,11 26,11"}));
		}
		this._svg = o.get(0);
		if(this.state>0 && this.state<3 && this._elem){
			$(this._elem).append(this._svg)
		}
		return this;
	},
	useLoadedElem: function(d){
		if(typeof d === 'string') return new M.TreeNode({name:d[i]});
		var p = {data:d}, t;
		if(d.type) p.type = d.type;
		t = (d.type)? d.type : this.options.loadname.replace(/s?$/,'');
		t = t.charAt(0).toUpperCase() + t.substr(1);
		return (t in M)? new M[t](p) : new M.TreeNode(p);
	},
	useLoadedData: function(d){
		if (d instanceof Array) for(var i in d){
			this.addElem(this.useLoadedElem(d[i]));
		}
		return this;
	},
	onError: function(d){
		$.popupForm({
			type:'error',
			data:"Ошибка:\n<p style=\"text-align:left\">\n\n"+d+"</p>"
		})
		this.setState(1);
	},
	onload: function (d){
		var n = (this.options.loadname)? this.options.loadname : 'data', el = this;
		if(n in d) this.useLoadedData(d[n])
		else this.useLoadedData(d)

		if('form' in d){
			if(this.state==3) this.setState(1);
			$.popupForm({data:d,onsubmit:function(d){el.onload(d)},loader:ldr})
		}else{
			this.showBranch();
		}
		return this;
	},
	load: function (query) {
		var el = this, q, n, el = this;
		if(query){
			this.setState(3);
			q = $.parseQuery(query);
			for(n in q) if(q[n] == '' && n in this.options.data) q[n] = this.options.data[n];
			ldr.get({
				data:q,
				onLoaded: function(d){ el.onload(d) },
				showLoading: true
			})
		}
		return this
	}
});

M.Home = M.TreeNode.extend({
	statics: {
		className: 'Home',
		query: "go=homes&do=get_users&id=",
		hudQuery:{
			del: "go=homes&do=remove&id=",
			cfg: "go=homes&do=edit&id="
		}
	},
	makeTitle(d){
		var n = (!d.nd)? d.address : d.nd+d.lit;
		return [
			$('<span class="lidata">').html('<b>'+n+'</b>'),
			$('<em>').html((d.prim?d.prim+'&emsp;':'')+d.note.replace(/\n|\r/g,' ')),
			$(this.hud).append(this.makeHud())
		];
	},
	onCreateElem: function(d){
		$(this._elem).attr({home:d.id,title:'Дом: '+d.address}).css({background:'url(pic/tree/home.png) no-repeat 0 5px'});
	},
	applyData: function(d){
		var a;
		if(a = $.parseAddress(d.address)) d = M.extend(d,a);
		var k = (!d.nd)? d.address : [d.nd*1,d.lit];
		this.options = M.extend(this.options,{loadname:'users',hold:0,key:k});
		this.mapObject = d.id;
	},
	onUpdate: function(e){
		var adr, rn;
		if(e.new.address || e.new.rayon){
			if(e.new.address){
				adr = e.new.address
				var a = $.parseAddress(adr);
				M.extend(this.options.data,a);
				this.key = this.options.key = [a.nd*1,a.lit];
			}else adr = this.options.data.address
			if(e.new.rayon){
				rn = e.new.rayon
				M.extend(this.options.data,{rayon:rn});
			}else rn = this.options.data.rayon
			var s = M.getStreetByAddress(adr,rn);
			if(s) this.moveTo(s).show();
		}
	}
});

M.Node = M.TreeNode.extend({
	statics: {
		className: 'Node',
		query: "go=devices&do=get_devices&id=",
		hudQuery:{
			prn: "target=maps.php&go=nodes&do=print&id=&nodeid=",
			del: "go=nodes&do=remove&id=&node=",
			add: "go=devices&do=add&id=&selectednode=",
			cfg: "go=nodes&do=edit&id=&node="
		}
	},
	makeTitle(d){
		var n = (!d.nd)? d.address : d.nd+d.lit;
		return [
			$('<span class="lidata">').html('<b>'+n+'</b>'),
			$('<em>').html((d.prim?d.prim+'&emsp;':'')+d.note.replace(/\n|\r/g,' ')),
			$(this.hud).append(this.makeHud())
		];
	},
	onCreateElem: function(d){
		$(this._elem).attr({node:d.id,title:'Узел: '+d.address}).css({background:'url(pic/tree/node.png) no-repeat 0 5px'});
	},
	applyData: function(d){
		var a;
		if(a = $.parseAddress(d.address)) d = M.extend(d,a);
		var k = (!d.nd)? d.address : [d.nd*1,d.lit];
		this.options = M.extend(this.options,{loadname:'devices',hold:0,key:k});
		this.mapObject = d.id;
		this.options.data.nodeid = d.id;
		this.options.data.selectednode = d.id;
	},
	onUpdate: function(e){
		var adr, rn, s;
		if(e.new.address || e.new.rayon){
			if(e.new.address){
				adr = e.new.address
				var a = $.parseAddress(adr);
				M.extend(this.options.data,a);
				this.key = this.options.key = [a.nd*1,a.lit];
			}else adr = this.options.data.address
			if(e.new.rayon){
				rn = e.new.rayon
				M.extend(this.options.data,{rayon:rn});
			}else rn = this.options.data.rayon
			if(s = M.getStreetByAddress(adr,rn)) this.moveTo(s).show();
		}
	},
	afterShowBranch: function(){
		if(onOpenNode) onOpenNode();
	},
	useLoadedElem: function(p){
		if('macaddress' in p) this.addElem(new M.Device({data:p}));
	}
});

M.Client = M.TreeNode.extend({
	statics: {
		className: 'Client',
		query: "go=devices&do=get_devices&id=",
		hudQuery:{
			mnt: "go=stdform&do=edit&table=monitoring&servicetype=client&id=",
			prn: "go=nodes&do=print&id=&node=",
			go:  "target=users.php&go=usrstat&user=",
			del: "go=clients&do=remove&id=",
			add: "go=devices&do=add&id=&selectednode=",
			usr: "go=stdform&do=edit&user=&table=users",
			cfg: "go=clients&do=edit&id="
		}
	},
	makeTitle(d){
		var n = (!d.nd)? d.address : d.nd+d.lit;
		return [
			$('<span class="lidata">').html('<b>'+n+'</b>'),d.name,
			((d.note||d.prim)?(d.name?'&emsp;':'')+'<em>'+(d.prim?d.prim+'&emsp;':'')+d.note.replace(/\n|\r/g,' ')+'</em>':''),
			$(this.hud).append(this.makeHud())
		];
	},
	onCreateElem: function(d){
		$(this._elem).attr({node:d.id,title:'Клиент: '+d.address}).css({background:'url(pic/tree/user.png) no-repeat 0 5px'});
	},
	onUpdate: function(e){
		var adr = this.options.data.address, rn = this.options.data.rayon;
		if(e.new.address || e.new.rayon){
			if(e.new.address){
				adr = e.new.address
				var a = $.parseAddress(adr);
				M.extend(this.options.data,a);
				this.key = this.options.key = [a.nd*1,a.lit];
			}
			if(e.new.rayon){
				rn = e.new.rayon
				M.extend(this.options.data,{rayon:rn});
			}
			var s = M.getStreetByAddress(adr,rn);
			if(s) this.moveTo(s).show();
		}
		if(this.state==2) this.hideBranch();
	},
	applyData: function(d){
		var a;
		if(a = $.parseAddress(d.address)) d = M.extend(d,a);
		var k = (!d.nd)? d.address : [d.nd*1,d.lit];
		this.options = M.extend(this.options,{loadname:'devices',hold:0,key:k});
		this.options.data.user = d.name;
		this.mapObject = d.id;
		this.options.data.selectednode = d.id;
	},
	useLoadedElem: function(p){
		if('macaddress' in p) this.addElem(new M.Device({data:p}));
	}
});

M.User = M.TreeNode.extend({
	statics: {
		className: 'User',
		query: "go=stdform&do=edit&uid=&table=users",
		hudQuery:{
			go: "target=users.php&go=usrstat&uid=",
			kil: "go=clients&do=userkill&uid=&table=users",
			cfg: "go=stdform&do=edit&id=&table=users"
		},
		ustate:['','online','offline','gone']
	},
	makeTitle(d){
		var p = this.parent;
		return [
			((p.kind == 'home')? $.shortFIO(d['Ф.И.О.']) : d.address)+' ('+d.user+') ',((p.kind == 'home')?$('<span class="lidata">').text(d.kv):''),
			$(this.hud).attr({style:((p.kind == 'home')?'right:20px':'')}).append(this.makeHud())
		];
	},
	applyData: function(d){
		var a;
		if(a = $.parseAddress(d.address)) d = M.extend(d,a);
		this.options.data.kv = a.kv;
		if(d.uid) this.options.data.id = d.uid;
		this.options = M.extend(this.options,{hold:0,key:d.kv*1});
	},
	onCreateElem: function(d){
		var t=[], i;
		for(i in d) if(!i.match(/[a-z]/i)||i=='ip') t.push(i+': '+d[i]);
		this.options = M.extend(this.options,{key:d.kv*1});
		$(this._elem).attr({user:d.user,title:t.join("\r")}).css({background:'url(pic/tree/user.png) no-repeat 0 5px'});
		if(d.state) $(this._header).addClass(this.ustate[d.state])
	},
});

M.Device = M.TreeNode.extend({
	statics: {
		className: 'Device',
		query:"go=ports&do=get&devid=&nodeid=",
		hudQuery:{
			 go: "target=references.php&go=devices&do=show&id=&table=wifi",
			mnt: "go=stdform&do=edit&table=monitoring&servicetype=device&id=",
			prn: "target=maps.php&go=devices&do=print&id=&nodeid=",
			del: "go=devices&do=remove&id=&selectednode=",
			cfg: "go=devices&do=edit&id=&selectednode="
		},
		tit:{
			server:{id:0,ip:0,node1:0,modified:0},
			cable:{id:0,subtype:0,colorscheme:0,node1:0,node2:0,modified:0},
			patchpanel:{id:0,modified:0},
			switch:{id:0,community:0,macaddress:0,modified:0},
			onu:{id:0,macaddress:0,modified:0},
			mconverter:{id:0,modified:0},
			wifi:{id:0,ssid:0,psk:0,modified:0}
		}
	},	
	makeTitle(d){
		var Sw = d.type == 'switch', Cb = d.type == 'cable', Wf = d.type == 'wifi',
		passiv = d.type == 'divisor'||d.type == 'splitter', t,
		wft = {ap:"ap",station:" ",bridge:"мост"},
		np = d.numports+(Cb?'ж':'п'),
		dev = passiv? '' : d.name.replace(/\s(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/,'&emsp;$1'),
		hud = this.makeHud();
		if(!Sw) hud.splice(1,1);
		if(!Wf) hud.splice(0,1);
		t = ((window.devtypes)? devtypes[d.type]:d.type)+' '+((!Cb && d.subtype!='')? wft[d.subtype]||d.subtype : np);
		return [
			$('<span class="devtype">').html(t), dev,
			((d.note)? '&emsp;&nbsp;<em>'+d.note.replace(/\n|\r/g,' ')+'</em>':''),
			$(this.hud).append(hud)
		]
	},
	applyData: function(d){
		this.key = [d.type,d.name];
		if(d.type == 'cable') this.mapObject = d.object;
		this.options.hold = 0;
		this.options.data.devid = d.id;
	},
	onCreateElem: function(d){
		var k, t=[], dev=d.type, h=this.tit;
		for(k in h[d.type]) if(d[k] && d[k]!='' && d[k]!=null) t.push(k+': '+d[k]);
		$(this._elem).attr('device',d.id);
		if(dev) $(this._elem).css({background:'url(pic/tree/'+dev+'.png) no-repeat 0 2px'});
		$(this._header).attr('title',t.join("\r"));
	},
	onAdd: function(){
		var p = this.parent.options.data;
		this.options.data.nodeid = p.id
		this.options.data.selectednode = p.id
	},
	useLoadedElem: function(p){
		var pt = p.porttype, t = this.getElem(pt), el = false;
		if(!t) this.addElem(t = new M.TreeNode(pt)).setState(2);
		p.mydevtype = this.options.data.type;
		el = new M.Port({data:p})
		t.addElem(el);
		return el;
	},
	useLoadedData: function(d) {
		if(typeof d != 'object') return false;
		if(d.ports){
			var el = this, f, t, n, i;
			if(d.ports instanceof Array)
				for(i in d.ports) el.useLoadedElem(d.ports[i]);
			else if(typeof d.ports == 'object') for(t in d.ports){
				f = d.fields;
				if(typeof t == 'string') for(n in d.ports[t]){
					var p={};
					for(i in d.ports[t][n]) p[f[i]] = d.ports[t][n][i];
					el.useLoadedElem(p);
				}
			}
		}
	},
	onUpdate: function(e){
		if(e.new.node1 || e.new.node2){
			var node = this.parents('node'), root = this, n, i, o;
			if(!node) return this;
			n = node.options.data.id;
			if(e.new.node1 && n == e.old.node1) i = 'node1';
			else if(e.new.node2 && n == e.old.node2) i = 'node2';
			if(!i) return this;
			while(root.parent) root = root.parent;
			if(!(o = M.allNodes[root.kinds['node'][e.new[i]]])) return this;
			this.moveTo(o)
		}
	}
});

M.Port = M.TreeNode.extend({
	statics: {
		className: 'Port',
		query: "go=ports&do=getTrace&id=",
		hudQuery:{
			conn: "go=ports&do=connect&id=&selectednode=",
			del: "go=ports&do=disconnect&id=&selectednode=",
			mnt: "go=stdform&do=edit&table=monitoring&servicetype=port&device=&id=",
			cfg: "go=ports&do=edit&id=&selectednode=",
		}
	},
	makeTitle(d){
		var t = [], k, join=d.linkport*1>0,
		hud = this.makeHud();
		if(d.mydevtype != 'switch') hud.splice(2,1);
		if(join) hud.splice(0,1); else hud.splice(1,1);
		return [
		$('<span>').append($('<div>').attr({class:'port',style:'border-style:'+((d.coloropt)?d.coloropt:'inherit')+((d.color)?';background-color:'+d.color:'')}).text(d.number)),
		(join?' &rarr; ':((d.note)?'&emsp;&nbsp;<img src="pic/warn16.png"> '+d.note:'')),
		(join?$('<span>').append($('<div>').attr({class:'port',style:'border-style:'+((d.linkcoloropt)?d.linkcoloropt:'inherit')+((d.linkcolor)? ';background-color:'+d.linkcolor:'')}).text(d.linkport)):''),
		(join?' &nbsp; '+d.devname : ''),
		((join && d.note)?' &nbsp;<img src="pic/warn16.png" title="'+d.note+'">':''),
		$(this.hud).append(hud)
		]
	},
	applyData: function(d){
		this.key = d.number*1;
		this.options = M.extend(this.options,{sort:0,hold:0});
	},
	onCreateElem: function(d){
		var t=[], k, join=d.linkport*1>0;
		for(k in {id:0,device:0,number:0,node:0,porttype:0,note:0}) if(d[k]!='' && d[k]!=null) t.push(k+': '+d[k]);
		$(this._elem).attr({port:d.id});
		$(this._header).attr({title:t.join("\r"),style:((d.bandle)?'background-color:'+d.bandle:'')});
	},
	onAdd: function(){
		var p = this.parent.parent.options.data;
		while(p.kind!='node' && p.parent) p = p.parent
		if(p.selectednode)
			this.options.data.selectednode = p.selectednode
	},
	onUpdate: function(e){
		if(this.state==2) this.hideBranch();
	},
	onHideBranch: function(){
		if(traceline && this.traceLine == traceline._leaflet_id) M.removeTraceLine();
	},
	makeTracePorts: function(d,tp){
		var l = d.length, i, s;
		for(i in d){
			s = false;
			if(s = d[i].sequence) delete d[i].sequence;
			el = new M.TracePort({sort:0,hold:1,hooks:1,node:s?1:0,last:i==l-1,data:d[i]});
			tp.addElem(el);
			if(s) this.makeTracePorts(s,el);
		}
	},
	useLoadedData: function(d){
		var i, s, el;
		if(d.sequence && d.sequence[0]){
			this.makeTracePorts(d.sequence,this);
		}else if(d.users && d.users[0]){
			while(i = d.users.shift()){
				el = new M.User({data:i})
				this.addElem(el);
			}
		}
		if(d.sequence && d.queryport) this.afterShowBranch = function(){
			var el = M.getTreeNodeByType('traceport',d.queryport);
			if(el) el.show();
			delete this.afterShowBranch;
		}
		if(d.geodata && d.geodata.features && d.geodata.features[0] && M.makeTraceLine){
			M.makeTraceLine(d.geodata);
			this.traceLine = traceline._leaflet_id;
		}
		return this;
	}
});

M.TracePort = M.TreeNode.extend({
	statics: {
		className: 'TracePort',
		showFld: {id:'id',fading:'затухание',link:'соединение',node:'узел'}
	},
	makeTitle(d){
		var fade = d.fading? $('<span class="fading">').html((d.fading*1).toFixed(2)+"&ensp;") : "";
		return [
		$('<span>').append($('<div>').attr({class:'port',port:d.type,style:'border-style:'+((d.coloropt)?d.coloropt:'solid')+((d.color)?';background-color:'+d.color:'')}).text(d.number)),((d.device||d.address)?' &larr; ':'&emsp;'),fade,$('<span>').html((d.divide && d.divide<100)? "&nbsp;"+(d.divide*1)+" %":d.device),' &nbsp; ',$('<span>').text(d.address),((d.note)?'<img src="pic/tree/warn16.png" title="'+d.note+'">':''),
		$(this.hud).append(this.makeHud())
		];
	},
	modSVG: function(){
		var l = $('>svg>polyline#d',this._elem), h = $(this._elem).innerHeight();
		if(l) l.attr({points:'10,16 10,'+h});
	},
	afterShowBranch: function(){
		this.modSVG();
		if(this.parent && this.parent.modSVG) this.parent.afterShowBranch();
	},
	onHideBranch: function(){
		this.modSVG();
		if(this.parent && this.parent.modSVG) this.parent.onHideBranch();
	},
	openAll: function(){
		if(this.haveUndeployed()) this.expendAll(false);
	},
	onShow: function(){
		if(l = this.options.layer){
			var p = l.feature.properties;
			if(p.type == 'traceline') l.setStyle({opacity:0.8});
			else if(p.type == 'divicon') map.addLayer(l);
		}
	},
	onHide: function(){
		if(l = this.options.layer){
			var p = l.feature.properties;
			if(p.type == 'traceline') l.setStyle({opacity:0});
			else if(p.type == 'divicon') map.removeLayer(l);
		}
	},
	onCreateElem: function(d){
		var title='', l, k, c, el = this, ft=this.showFld;
		for(k in d) if(ft[k] && d[k]!='') title = title+ft[k]+': '+d[k]+"\r";
		$(this._elem).attr({traceport:d.id,title:title})
		if(d.bandle) c = $.hex2rgb($.colourNameToHex(d.bandle));
		$(this._header).attr({title:title,style:((c)?'background-color: rgba('+c.r+','+c.g+','+c.b+',0.15)':'')});
		$(this._header).on('mouseup',function(e){
			if(e.button == 1){
				e.stopPropagation(); e.preventDefault();
				el.openAll();
				return false;
			}
		});
		$(this._header).on('click',function(e){
			var d = el.options.data, n = d.node, p = d.id, dev = d.dev_id, o, t = d.type;
			if(t == 'switch' && (o = M.allNodes[M.rootNode.kinds.node[n]])){
				o.show(function(e){
					var od;
					delete o.afterShow;
					if(od =  M.allNodes[M.rootNode.kinds.device[dev]]){
						od.show(function(e){
							var op;
							delete od.afterShow;
							if(op =  M.allNodes[M.rootNode.kinds.port[p]])
								op.show();
						});
					}
				})
			}
		})
	},
	applyData: function(d){
		this.options['compact'] = 0;
	},
});

M.Rayon = M.TreeNode.extend({
	statics: { className: 'Rayon' },
	makeTitle: function(d){
		return d.name;
	},
	onCreateElem: function(d){
		$(this._elem).attr({title:'район: '+d.name})
	},
	applyData: function(d){
		this.key = d.name.toUpperCase();
	},
	onEmpty: function(){
		this.remove();
	},
	doClick: function(e){
		var rn = this.options.data;
		if(rayonView && rn.latitude && rn.longitude && rn.zoom)
			rayonView(rn.latitude,rn.longitude,rn.zoom);
	},
});

M.Street = M.TreeNode.extend({
	statics: { className: 'Street' },
	makeTitle: function(d){
		return d.pref+'.'+d.ul;
	},
	onCreateElem: function(d){
		$(this._elem).attr({title:d.pref+'.'+d.ul})
	},
	onEmpty: function(){
		this.remove();
	},
	applyData: function(d){
		this.key = [d.ul.toUpperCase(),d.pref.toUpperCase()];
	}
});

M.getTreeNodeByType = function(t,id){
	if(!M.rootNode || !M.rootNode.kinds) return false;
	var type;
	if(typeof t == 'object' && t.id && t.type){
		type = t.type; id = t.id;
		if(type == 'cable'){
			type = 'device';
			id = t.dev_id;
		}
	}else type = t;
	if(M.rootNode.kinds[type] && M.rootNode.kinds[type][id])
		return M.allNodes[M.rootNode.kinds[type][id]]
	else return false
}

M.getRayon = function(id){
	var rayon, r, rn;
	if(!M.rootNode){
		M.rootNode = new M.TreeNode('begin of tree');
		M.rootNode.setState(2);
	}
	if(!(rn = M.rootNode.getElem('районы')))
		rn = M.rootNode;
	rayon = M.getTreeNodeByType('rayon',id)
	if(!rayon && rayons && (r = rayons.getItemByID(id))){
		rayon = new M.Rayon({data:r});
		rn.addElem(rayon);
	}
	if(!rayon){
		if(!M.noRayon){
			M.noRayon = new M.Rayon({data:{id:0,name:'неизвестный район'}});
			M.rootNode.addElem(M.noRayon);
		}
		rayon = M.noRayon;
	}
	return rayon;
}

M.getStreetByAddress = function(adr,rn){
	var street, a, ul, r;
	if(!M.rootNode){
		M.rootNode = new M.TreeNode('begin of tree');
		M.rootNode.setState(2);
	}
	r = M.getRayon(rn)
	if((a = $.parseAddress(adr)) && a.ul){
		if(!(street = r.getElem([a.ul.toUpperCase(),a.pref.toUpperCase()]))){
			street = new M.Street({data:a});
			r.addElem(street);
		}
	}else{
		if(!M.noStreet){
			M.noStreet = new M.TreeNode('без адреса');
			M.rootNode.addElem(M.noStreet);
		}
		street = M.noStreet;
	}
	return street;
}

M.addTreeNodeByAddress = function(o){
	var t, street, a, n = false;
	t = (o.type)? o.type.charAt(0).toUpperCase() + o.type.substr(1) : 'TreeNode';
	if(!(o.type in {client:0,home:0,node:0})) return n;
	street = M.getStreetByAddress(o.address,o.rayon);
	if(t in M && street){
		n = new M[t]({data:o});
		street.addElem(n);
	}
	return n;
}

M.field = new M.Field();

if($['datepicker']){
	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
		dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
		dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
		weekHeader: 'Нд',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		showButtonPanel: true,
		yearSuffix: ''
	}
	$.datepicker.setDefaults($.datepicker.regional['ru']);
}

window.ldr = $.loader();
})(jQuery)
