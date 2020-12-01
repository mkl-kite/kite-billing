(function($){

$.fn.getmax = function(n) {
	var m=old=false, r={};
	this.each(function(){
		var v=$(this).attr(n)
		if(m) { m = Math.max(m,v); if(old!=m) r=this }
		else { m = v; r=this }
		old=m
	})
	return r
}

$.colourNameToHex = function (colour) {
    var colours = {"aliceblue":"#f0f8ff","antiquewhite":"#faebd7","aqua":"#00ffff","aquamarine":"#7fffd4","azure":"#f0ffff",
    "beige":"#f5f5dc","bisque":"#ffe4c4","black":"#000000","blanchedalmond":"#ffebcd","blue":"#0000ff","blueviolet":"#8a2be2",
	"brown":"#a52a2a","burlywood":"#deb887","cadetblue":"#5f9ea0","chartreuse":"#7fff00","chocolate":"#d2691e","coral":"#ff7f50",
	"cornflowerblue":"#6495ed","cornsilk":"#fff8dc","crimson":"#dc143c","cyan":"#00ffff","darkblue":"#00008b","darkcyan":"#008b8b",
	"darkgoldenrod":"#b8860b","darkgray":"#a9a9a9","darkgreen":"#006400","darkkhaki":"#bdb76b","darkmagenta":"#8b008b",
	"darkolivegreen":"#556b2f","darkorange":"#ff8c00","darkorchid":"#9932cc","darkred":"#8b0000","darksalmon":"#e9967a",
	"darkseagreen":"#8fbc8f","darkslateblue":"#483d8b","darkslategray":"#2f4f4f","darkturquoise":"#00ced1","darkviolet":"#9400d3",
	"deeppink":"#ff1493","deepskyblue":"#00bfff","dimgray":"#696969","dodgerblue":"#1e90ff","firebrick":"#b22222","floralwhite":"#fffaf0",
	"forestgreen":"#228b22","fuchsia":"#ff00ff","gainsboro":"#dcdcdc","ghostwhite":"#f8f8ff","gold":"#ffd700","goldenrod":"#daa520",
	"gray":"#808080","green":"#008000","greenyellow":"#adff2f","honeydew":"#f0fff0","hotpink":"#ff69b4","indianred ":"#cd5c5c",
	"indigo":"#4b0082","ivory":"#fffff0","khaki":"#f0e68c","lavender":"#e6e6fa","lavenderblush":"#fff0f5","lawngreen":"#7cfc00",
	"lemonchiffon":"#fffacd","lightblue":"#add8e6","lightcoral":"#f08080","lightcyan":"#e0ffff","lightgoldenrodyellow":"#fafad2",
	"lightgrey":"#d3d3d3","lightgreen":"#90ee90","lightpink":"#ffb6c1","lightsalmon":"#ffa07a","lightseagreen":"#20b2aa",
	"lightskyblue":"#87cefa","lightslategray":"#778899","lightsteelblue":"#b0c4de","lightyellow":"#ffffe0","lime":"#00ff00",
	"limegreen":"#32cd32","linen":"#faf0e6","magenta":"#ff00ff","maroon":"#800000","mediumaquamarine":"#66cdaa","mediumblue":"#0000cd",
	"mediumorchid":"#ba55d3","mediumpurple":"#9370d8","mediumseagreen":"#3cb371","mediumslateblue":"#7b68ee","mediumspringgreen":"#00fa9a",
	"mediumturquoise":"#48d1cc","mediumvioletred":"#c71585","midnightblue":"#191970","mintcream":"#f5fffa","mistyrose":"#ffe4e1",
	"moccasin":"#ffe4b5","navajowhite":"#ffdead","navy":"#000080","oldlace":"#fdf5e6","olive":"#808000","olivedrab":"#6b8e23",
	"orange":"#ffa500","orangered":"#ff4500","orchid":"#da70d6","palegoldenrod":"#eee8aa","palegreen":"#98fb98","paleturquoise":"#afeeee",
	"palevioletred":"#d87093","papayawhip":"#ffefd5","peachpuff":"#ffdab9","peru":"#cd853f","pink":"#ffc0cb","plum":"#dda0dd",
	"powderblue":"#b0e0e6","purple":"#800080","red":"#ff0000","rosybrown":"#bc8f8f","royalblue":"#4169e1","saddlebrown":"#8b4513",
	"salmon":"#fa8072","sandybrown":"#f4a460","seagreen":"#2e8b57","seashell":"#fff5ee","sienna":"#a0522d","silver":"#c0c0c0",
	"skyblue":"#87ceeb","slateblue":"#6a5acd","slategray":"#708090","snow":"#fffafa","springgreen":"#00ff7f","steelblue":"#4682b4",
    "tan":"#d2b48c","teal":"#008080","thistle":"#d8bfd8","tomato":"#ff6347","turquoise":"#40e0d0","violet":"#ee82ee","wheat":"#f5deb3",
	"white":"#ffffff","whitesmoke":"#f5f5f5","yellow":"#ffff00","yellowgreen":"#9acd32"};
    if (typeof(colour)=='string' && typeof(colours[colour.toLowerCase()])!='undefined')
        return colours[colour.toLowerCase()];
    return false;
}

$.hex2rgb = function(hex){
	if(!(typeof hex == 'string') || !hex) return false
    hex = hex.replace(/^\s*#|\s*$/g, '');
    if(hex.length == 3)
        hex = hex.replace(/(.)/g, '$1$1');
    var r = parseInt(hex.substr(0, 2), 16),
        g = parseInt(hex.substr(2, 2), 16),
        b = parseInt(hex.substr(4, 2), 16);
	return {r:r,g:g,b:b}
}

$.rgb2hex = function(rgb){
	var h = rgb.replace(/[^\d,]/g, '').split(',');
    return '#' + ((h[2] | h[1] << 8 | h[0] << 16) | 1 << 24).toString(16).slice(1);
}

$.bright = function(hex, ratio){
	var h = $.hex2rgb(hex)
    return '#' +
       ((0|(1<<8) + h.r + (256 - h.r) * ratio).toString(16)).substr(1) +
       ((0|(1<<8) + h.g + (256 - h.g) * ratio).toString(16)).substr(1) +
       ((0|(1<<8) + h.b + (256 - h.b) * ratio).toString(16)).substr(1);
}

$.hex2rgba = function(hex,opacity) {
    var h = $.hex2rgb(hex),
		result = 'rgba('+h.r+','+h.g+','+h.b+','+opacity+')';
    return result;
}

$.cros = function(o) {
	var $res={},i=0,e,k,l,k1;
	if($.isArray(o) && o.length>1) {
		l=o[i];
		for(k in l) {
			e=true;
			for(i=1;i<o.length;i++) {
				if(!(k in o[i])) { e=false; break; }
			}
			if(e) $res[k]=l[k];
		}
	}
	return $res
}

$.extract = function(o) {
	var res={},i=0,e,k,k1;
	if($.isArray(o) && o.length>1) {
		l=o[i];
		for(k in l) {
			var e=true;
			for(i=1;i<o.length;i++) {
				if(k in o[i]) { e=false; break; }
			}
			if(e) res[k]=l[k]
		}
	}
	return res
}

$.buildFormHeader = function(o) {
	if(o) var r=$('<div>').attr({class:'header'}).html(o)
	return r;
}

$.buildFormFooter = function(o) {
	var k, b, txt,
	r=$('<div>').attr({class:'submit-container'});
	if(typeof(o)=='object') {
		for(k in o) {
			b=$.extend({id:k,class:'submit-button',txt:'Закрыть'},o[k]||{});
			txt=b['txt']||'Закрыть'; delete b['txt'];
			r.append($('<a>').attr(b).text(txt))
		}
	return r;
	}
}
	
$.errorForm = function(m) {
	return $('<form>').attr({id:'err',class:'error',name:'errorform',style:'display:none;'}).append([
		$('<span>').attr({class:'form-title'}).html(m),$.buildFormFooter({cancelbutton:{txt:'Ok'}})]);
}

$.infoForm = function(m) {
	return $('<form>').attr({id:'info',class:'normal info',name:'infoform',style:'display:none;'}).append([
		$('<span>').attr({class:'form-title'}).html(m),$.buildFormFooter({cancelbutton:{txt:'Ok'}})]);
}

$.confirmForm = function(m) {
	return $('<form>').attr({id:'confirm',class:'normal info',name:'confirmform',style:'display:none;',force_submit:1}).append([
		$('<div>').attr({class:'form-title'}),
		$('<input>').attr({type:"hidden",name:"do",value:"yes"}), m,
		$.buildFormFooter({cancelbutton:{txt:'Нет'},submitbutton:{txt:'Да'}})
	]);
}

$.setEventsTable = function(t) {
	var cr = {td:{id:0,class:0,style:0},table:{id:0,class:0,style:0,target:0,delete:0,module:0,tname:0}}
	if(t.nodeName=='TABLE') {
		if(!t._key) t._key=0;
		if(typeof(t._head)=='undefined') {
			t._head = [];
			var i = 0;
			$('thead tr:last-child td',t).each(function(){
				var f = $(this).attr('field'),
					n = $(this).attr('num');
				if(!n) n = i++;
				t._head[n] = {name:f,type:'text',label:$(this).text()}
			})
		}
		t._append = function(row) {
			if(!$.isArray(row)) return false;
			var id = row[this._key],
				tr = $('<tr>').attr({id: id}),
				trgt = $(this).attr('target'),
				tbody = $(this).find('tbody');
			for (var cell in this._thead) {
				tr.append($('<td>').attr($.cros([this._thead[cell],cr.td])).append(row[cell]))
			}
			if(trgt && trgt!='custom') {
				tr.append($(img))
			}
			tbody.append(tr)
			return tr;
		}
		t._delete = function(row) {
			var id = ($.isArray(row) && this._key in row)? row[key] : row;
			var tr = $(this).find('tbody tr[id='+id+']').remove();
			if(tr.length == 0) return false;
			return tr;
		}
		t._modify = function(row) {
			if(!$.isArray(row)) return false;
			var id = row[this._key],
				trgt = $(this).attr('target'),
				tr = $(this).find('tbody tr[id='+id+']').empty();
			for (var cell in this._thead) {
				tr.append($('<td>').attr($.cros([this._thead[cell],cr.td])).append(row[cell]))
			}
			if(trgt && trgt!='custom') {
				tr.append($(img))
			}
			return tr;
		}
	}
}

// Функция формурующая таблицу как субформу
$.buildTable = function(o) {
	var cr = {td:{id:0,class:0,style:0},table:{id:0,class:0,style:0,target:0,delete:0,module:0,tname:0}},
		img = '<td class="del"><img class="del-button" src="pic/delete.png"></td>',
		key = ('key' in o)? o.key : 0;
	try {
		var t=$('<table>').attr($.cros([o.table,cr.table])).get(0)
		t._key = key;
		t._thead = o.table.thead;
		t._tbody = o.table.tbody;
		$(t).append($('<tbody>'));
		setEventsTable(t);
		for(r in t._tbody) t._append(t._tbody[r]);
		return $('<div>').attr($.cros([o,cr.td])).append(t)
	}catch (e) {
		return $('<div>').text('формирование таблицы завершилось ошибкой');
	}
}

$.buildForm = function(options) {
	var opt = $.extend({
		id: 'jQueryForm',
		class: 'normal',
		breakLine: '',
		header: false,
		footer: {
			cancelbutton:{txt:'Отменить'},
			submitbutton:{txt:'Сохранить'}
		},
		fields: {},
		layout: {},
		SFdata: function(){}
	},options||{});

	function GetF(o) {
		var d = $('<div>').attr({id:o.id,class:o.class});
		if(o.label) d.append($('<span>').attr({class:'label'}).append(o.label));
		if(o.field.attr('type')=='password') o.field = [o.field,$('<img src="pic/eog.png">')]
		d.append(opt.breakLine).append($('<span>').attr({class:'field'}).append(o.field));
		return d;
	}

	// Функция формурующая поле SELECT
	function GetFselect(o) {
		var k, r=$('<select>');
		for(k in o.list) {
			if(k==o.value) 
				r.append($('<option>').attr({value:k,selected:true}).html(o.list[k]));
			else 
				r.append($('<option>').attr({value:k}).html(o.list[k]));
		}
		if(o.list) delete(o.list)
		return r.attr(o);
	}

	function GetPlist(o){
		var l = $('<div>').addClass('photolist'), k, s, a={}, h, w,
			d = {id:1,label:2,list:3,tabindex:4,size:5}, obj = l[0],
			size = o.size;
		obj._add = function(item){
			if($(obj).find('[item='+item.id+']')[0]) return false;
			var s, h = $(obj).height(), w, val = $(obj).attr('value'),
			a = (val)? val.split(',') : [],
			ratio = $(obj).attr('ratio');
			if(ratio && h){
				s = ratio.split(/[^0-9]/); w = h*s[0]/s[1];
				s = {width:w+'px',height:h+'px'}
			}else if(size){
				s = size.split('x');
				s = {width:s[0]+'px',height:s[1]+'px'}
			}else s = {}
			var pi = $('<div>').addClass('photoitem').attr({item:item.id}).css(s)[0]
			pi.item = item;
			$(obj).append($(pi).append([
				$('<img class="photo" src="'+item.photo+'">'),
				$('<div class="chk"><img src="pic/ok.png"></div>'),
				$('<div>').addClass('title').html(item.fio)
			]));
			if((k = a.indexOf(item.id)) == -1) a.push(item.id);
			$(obj).attr('value',a.join(','))
		}
		obj._del = function(item){
			var  val = $(obj).attr('value'), a = (val == '')? [] : val.split(',');
			if(typeof item !== 'object') item = {id:item}
			$(obj).find('[item='+item.id+']').remove();
			if((k = a.indexOf(item.id)) != -1) a.splice(k,1);
			$(obj).attr('value',a.join(','))
		}
		obj._modify = function(item){
			this._del.call(this,item);
			this._add.call(this,item);
		}
		a = $.extract([o,d]);
		l.attr(a);
		for(k in o.list) obj._add(o.list[k])
		return l;
	}

	// Функция формурующая Объект photoselect
	function GetPselect(o) {
		var k, item, r=$('<div>'), l = GetPlist({size:o.size,list:o.list}),
			d = ['id','label','list','tabindex','size'];
		r.append([l,
			$('<div>').addClass('list-scroll-left').append($('<img src="pic/arr-left.png">')),
			$('<div>').addClass('list-scroll-right').append($('<img src="pic/arr-right.png">'))
		])
		for(k in d) if(o[d[k]]) delete(o[d[k]]);
		return r.attr(o);
	}

	function validateDate(date) {
		var dt=date.split(' ')
		var md=dt[0].split('-')
		if(md[0].length>3) { 
			return md[2]+'-'+md[1]+'-'+md[0]
		}else{
			return dt[0];
		}
	}
	// Функция формурующая список для выбора кликом
	function GetArtSelect(o) {
		try {
			var k, s = $('<ul>');
			for(k in o.list) s.append($('<li>').attr({id:k,class:'item'}).html(o.list[k]));
			return s;
		}catch (e) { return $('<H3>ERROR gen ArtSelect<H3><BR>'); }
	}
	// Функция формурующая список c chrckbox-ами
	function GetCheckSet(o) {
		try {
			var k, n,
			name = ('name' in o)? o.name : 'set',
			set = $('<div>');
			function ChkBox(id,o) {
				return $('<div>').attr({id:name+'_'+id,class:'checkitem'}).append([
					$('<span>').attr({class:'label'}).html(o.label),
					$('<span>').attr({class:'field'}).append(
						$('<input>').attr({
							id:name+'_'+id,
							type:"checkbox",
							checked:(o.value!=0)
						})
					)
				])
			}
			for(k in o.set) set.append(ChkBox(('id' in o.set[k])? o.set[k].id:k,o.set[k]));
			return set;
		}catch (e) {
			return $('<H3>ERROR gen CheckSet<H3><BR>'); 
		}
	}
	// Функция формурующая таблицу как субформу
	var buildSubForm = function(o) {
		var cr = {td:{id:0,class:0,style:0},table:{id:0,class:0,style:0,target:0,delete:0,module:0,tname:0}},
			img = '<td class="del"><img class="del-button" src="pic/delete.png"></td>',
			key = ('key' in o)? o.key : 0;
		try {
			var t=$('<table>').attr($.cros([o.table,cr.table])).get(0), tbody=$('<tbody>');
			t._thead = o.table.thead;
			t._tbody = o.table.tbody;
			if(o.table['table_menu']) t.table_menu = o.table.table_menu;
			if(o.table['fixed_menu']) t.fixed_menu = o.table.fixed_menu;
			t._append = function(row) {
				var id = row[key],
					tr = $('<tr>').attr({id: id}),
					trgt = $(this).attr('target'),
					tbody = $(this).find('tbody');
				for (var cell in this._thead) {
					tr.append($('<td>').attr($.cros([this._thead[cell],cr.td])).append(row[cell]))
				}
				if(trgt && trgt!='custom') {
					tr.append($(img))
				}
				tbody.append(tr)
			}
			t._delete = function(row) {
				var id = ($.isArray(row) && key in row)? row[key] : row;
				$(this).find('tbody tr[id='+id+']').remove();
			}
			t._modify = function(row) {
				var id = (key in row)? row[key] : row,
					trgt = $(this).attr('target'),
					tr = $(this).find('tbody tr[id='+id+']').empty();
				for (var cell in this._thead) {
					tr.append($('<td>').attr($.cros([this._thead[cell],cr.td])).append(row[cell]))
				}
				if(trgt && trgt!='custom') {
					tr.append($(img))
				}
			}
			$(t).append(tbody);
			for(r in t._tbody) t._append(t._tbody[r]);
			return $('<div>').attr($.cros([o,cr.td])).append(t)
		}catch (e) {
			return $('<div>').text('формирование субформы завершилось ошибкой');
		}
	}

	// Функция формурующая fieldset
	function buildLayout(layout,fields) {
		var fieldname, done={}, atr, k, v, i
		var cr={id:0,class:0,style:0}
		var fc=$('<div>').attr({class:'field-container'})
		var l = $.extend({},layout||{});
		for(k in l) {
			v=l[k]
			if(v.type=='fieldset') {
				l[k].id=k
				tmp=$('<fieldset>').attr($.cros([v,cr]))
				tmp.append($('<legend>').html(v.legend))
				if(v.fields) for(i=0;i<v.fields.length;i++) {
					fieldname=v.fields[i]
					tmp.append(fields[fieldname])
					done[fieldname]=1
				}
				if(v.target) {
				}
				fc.append(tmp)
			}
			if(v.type=='field') {
				fc.append(fields[k])
			}
		}
		for(k in fields) if(!done[k]) fc.append(fields[k])
		return fc
	}

	// Функция формурующая поле
	function buildFields(o) {
		var res, v, k, k1, tmp, f, img;
		res={};
		for(k in o) {
			v=o[k]; tmp='';
			for(k1 in v) {
				if(v[k1]==null) delete v[k1];
				if(typeof(v[k1])=='string' && v[k1]=='') delete v[k1];
			}
			f=$.extend({type:'',id:k,class:'form-field',name:k,value:''},v||{});
			if(!('value' in f)) f.value='';
			if(f.type=='date') f.value=validateDate(f.value)
			var atr = $.extract([f,{label:0,native:0}])
			try {
				switch(f.type) {
					case 'hidden':
					case 'text':
					case 'password':
						tmp=$('<input>').attr(atr)
						break;
					case 'checkbox':
						if(f.value>0) atr.checked=true; else atr.checked=false
						tmp=$('<input>').attr(atr)
						break;
					case 'date':
						atr.type='text'
						tmp=$('<input>').attr(atr).addClass('dateselect');
						break;
					case 'nofield':
						atr.class='nofield'
						delete(atr.value)
						tmp=$('<div>').attr(atr).append(f.value)
						tmp.get(0).value = f.value
						break;
					case 'textarea':
						delete(atr.value)
						tmp=$('<textarea>').attr(atr).text(f.value)
						break;
					case 'select':
						tmp=GetFselect(f);
						break;
					case 'photoselect':
						tmp=GetPselect(f);
						break;
					case 'photolist':
						f.label = '';
						tmp=GetPlist(f);
						break;
					case 'autocomplete':
						atr.type='text'
						atr.class=atr.class+' ac_field'
						tmp=$('<input>').attr(atr)
						break;
					case 'photo':
						var img = {src: atr.value}
						if(img.src=='') img.src='pic/unknown.png'
						tmp=$('<div>').attr(atr).attr({class:'photobox'}).append($('<img>').attr(img))
						break;
					case 'map':
						tmp=$('<div id="map">').attr(atr)
						break;
					case 'checkset':
						tmp=GetCheckSet(v['list'])
						tmp.attr(atr)
						break;
					case 'artselect':
						tmp=GetArtSelect(f)
						tmp.attr(atr)
						break;
					case 'subform':
						tmp=buildSubForm(v['sub'])
						atr.subform=atr.name
						tmp.after($('<input>').attr($.extend(atr,{type:'hidden'})))
						break;
				}

				if(f.type!='') {
					if($.inArray(f.type,['subform'])<0) tmp.get(0).oldvalue = f.value
					if($.inArray(f.type,['hidden','checkset','subform','map'])<0) { 
						res[k]=GetF({id:'field-'+k,class:'form-item',label:f.label,field:tmp});
					}else{
						res[k]=tmp;
					}
				}
			}catch(e){
				 res[k]=$('<p>').text('ERROR '+k);
			}
		}
		return res;
	}

	// Функция расставляет tabindex
	function SetTabIndex(o) {
		var k,v,k1,v1,ti;
		var noF = ['hidden','date','subform','fieldset','nofield','checkset','photo','photolist','photoselect','artselect'];
		if(typeof(o)=='object') {
			ti=1;
			for(k in o) {
				v=o[k];
				if($.inArray(v.type,noF)<0) {
					o[k].tabindex=ti;
					ti++;
				}else{
					if(v.type=='fieldset' && v.set.fields) {
						for(k1 in v.set.fields) {
							v1=v.set.fields[k1];
							if($.inArray(v1.type,noF)<0) {
								o[k].set.fields[k1].tabindex=ti;
								ti++;
							}
						}
					}
				}
			}
		}
		return o;
	}

	var form_pattern = {id:0,class:0,name:0,style:0,force_submit:0},
		atr = $.extend({style:'display:none'},$.cros([opt,form_pattern])),
		bf = buildFields(SetTabIndex(opt.fields));
	return $('<form>').attr(atr).append($.buildFormHeader(opt.header))
		.append(buildLayout(opt.layout,bf)).append(
		$.buildFormFooter(opt.footer))
}

$.popupForm = function(options) {
	var  f,
	opt = $.extend({
		type: 'normal',
		data: '',
		name: 'tmpFormName',
		obj: {},
		onerror: function(){},
		oncancel: false,
		onsubmit: false,
		ongetform: function(){},
		onchange: function(){},
		cancel: false,
		submit: false,
		destroy: true,
		loader: {get:function(d){if(d.onLoaded) d.onLoaded(d.data)}}
	},options||{}),
	FormDestroy = function(){},
	_shift = false,
	baseLayer = false,
	cap = $('<div class="overlay"><div><div class="formbox"></div></div></div>').appendTo('body'),
	ldcap = $('<div class="overlay"><div><div><img src="pic/loading.gif"></div></div></div>');

	function onSelectInit(f){ // Функция включающая обновление селектов
		f.on('change','select[onselect]',function() {
			var select = $(this), och,
			n = select.attr('name'),
			s = select.attr('onselect'),
			t = f.attr('name'),
			id = f.find('input[name=id]').val(),
			go = f.find('input[name=go]').val()
			opt.loader.get({ 
				data: 'go='+go+'&do='+s+'&fname='+n+'&id='+id+'&table='+t+'&value='+select.val(),
				onLoaded: function(d){
					if(d.select) {
						var t = f.find('[name='+d.target+']').get(0)
						t.options.length = 0
						for(var k in d.select.list) {
							var op = {value:k};
							if(t.oldvalue==k) op.selected = true;
							t.options[t.options.length] = $('<option>').attr(op).html(d.select.list[k]).get(0)
						}
						if($(t).attr('onchange') != ''){
							$(t).change()
						}
					}
					if(d.modify){
						for(var fld in d.modify){
							var el = f.find('[name='+fld+']');
							if(el.hasClass('nofield')) el.html(d.modify[fld]); else el.val(d.modify[fld]);
						}
					}
				}, 
				onError: function(){},
				showLoading: function(){}
			})
		})
	}

	function setNewVal(o) { // Функция нужна при наличии subform, переводит форму из new в обычную
		if(typeof o === 'object'){
			var nd = opt.obj['nodeName'], noforce = false;
			if(nd != 'FORM') var f = $(opt.obj).parents('form').get(0);
			if(!f) return false
			for(var fn in o){
				if(typeof fn === 'string'){
					if(fn=='id') noforce = true;
					$(f).find('[name='+fn+']').each(function(i,s){
						if($(s).attr('type')=='hidden') $(s).val(o[fn]);
						if('oldvalue' in s) s.oldvalue = o[fn];
					})
				}
			}
			if(noforce) $(f).removeAttr('force_submit');
		}
	}
	
	function AutoCompleteInit(f){ // Функция включающая автодополнение
		f.find('.ac_field').each(function() {
			var ac = this
			$(this).autocomplete({
				minLength: 2,
				source: function(req,add){
					var m = $(ac).attr('module'),
						_go = (m)? m : f.find('[name=go]').val(),
						_do = 'auto_'+$(ac).attr('name'),
						 tn = f.attr('name')
					opt.loader.get({
						data: "go="+_go+"&do="+_do+"&req="+encodeURIComponent(req.term)+'&table='+tn,
						onLoaded: function(d) {
							var s = [];
							if(d.complete) $.each(d.complete, function(i, val){
								s.push(val);
							})
							add(s);
						},
						onError: function(){},
						showLoading: function(){}
					})
				},
				select: function(e,ui){
					var el, n;
					if(Object.keys(ui.item).length==2 && ui.item.label && ui.item.value) {
						$(ac).val(ui.item.value)
					}else{
						for(n in ui.item) {
							if( n != 'label' && n != 'value'){
								el = f.find('[name='+n+']');
								if(el.attr('type') == 'nofield') el.text(ui.item[n])
								else el.val(ui.item[n])
							}
						}
					}
				}
			})
		})
	}

	var getInputData = function(f) {
		var m = [], i = 0, n, el = {photo:0,photoselect:1,map:2,photolist:3,checkset:4,artselect:5},
			force = f.attr('force_submit') // при отсутствии изменений данные не отсылаются, нужно иногда отослать...
		f.find('[subform]').each(function(){
			if(subFormData) {
				var v = $.toJSON(subFormData)
				$(this).val(v)
			}
		})
		f.find('[name]').each(function(){
			if($(this).hasClass('nofield')) return true;
			var n = $(this).attr('name'),
				t = $(this).attr('type'),
				v = $(this).val(),
				ov = this.oldvalue
			if(t=='checkbox') 
				v = (this.checked)?"1":"0";
			if(t=='select'){
				v = v.replace(/^_/,'');
				ov = ov.replace(/^_/,'');
			}
			if(t in el)
				v = $(this).attr('value');
			if(n && typeof(v)!='undefined' && typeof(ov)!='undefined' && (ov!=v||force==1)) {
				m[m.length] = 'old_'+n+'='+encodeURIComponent(ov);
				m[m.length] = n+'='+encodeURIComponent(v);
				i++
			}else{
				if(t=='hidden') m[m.length] = n+'='+encodeURIComponent(v);
			}
		})
		return (i>0)? m.join('&') : '';
	},

	initPassword = function(f){
		var inp = f.find('[type=password]')
		f.find('[type=password]+img').click(function(){
			var t = inp.attr('type');
			if(t == 'text')
				inp.attr('type','password')
			else
				inp.attr('type','text')
		})
	},

	initPhotoSelect = function(f){
		var ps = f.find('[type=photoselect]')[0], l = $(ps).find('.photolist')[0],
			v = $(ps).attr('value'), a = (v)? v.split(','):[], k;
		for(k in a) $(l).find('[item='+a[k]+']').addClass('selected');
		$(ps).find('.photolist').bind('mousewheel',function(e){
			if (e.preventDefault) e.preventDefault();
			if(e.deltaY > 0) {
				$(l).scrollLeft($(l).scrollLeft() - 126)
			}else{
				$(l).scrollLeft($(l).scrollLeft() + 126)
			}
			return false;
		})
		$(ps).on('click','.list-scroll-left',function(e){
			$(l).animate({scrollLeft: $(l).scrollLeft() - $(ps).width()},400)
		})
		$(ps).on('click','.list-scroll-right',function(e){
			$(l).animate({scrollLeft: $(l).scrollLeft() + $(ps).width()},400)
		})
		$(l).on('click','.photoitem',function(e){
			var val = $(ps).attr('value'), k,
				a = (val == '')? [] : val.split(','), i = $(this).attr('item');
			$(this).toggleClass('selected');
			if($(this).hasClass('selected')) a.push(i)
			else if((k = a.indexOf(i)) != -1) a.splice(k,1);
			$(ps).attr('value',a.join(','))
		})
	},

	initPhotoList = function(f){
		f.find('[type=photolist]').each(function(){
			if($(this).parents('photoselect')[0]) return false;
			var l = this, k, q = $.parseQuery($(l).attr('query'));
			$(this).on('click',function(e){
				q['id'] = f.find('[name=id]').val();
				q.pers = $(l).attr('value');
				$.popupForm({
					data: $.mkQuery(q),
					onsubmit: function(d){
						var k, f;
						if(d.obj) for(f in d){
							if(typeof d[f] === 'object' && d.obj[f] && typeof d.obj[f] === 'function'){
								for(k in d[f]) 
									d.obj[f](d[f][k]);
							}
						}
					},
					obj:l,
					loader: opt.loader
				})
			})
		})
	},

	initExtButton = function(f){
		f.find('.linkform').click(function(){
			var l = $(this).attr('add').split('?'),
			q = $.parseQuery(l[1]);
			q['id'] = f.find('[name=id]').val()
			window.open(l[0]+'?'+$.mkQuery(q))
		})
		f.find('#ext').click(function(){
			var fs = f.find('fieldset.closable'), el = f.find('[type=map]').get(0),
			map, target, yndx, terra, osm, xy = [], oid = $(el).attr('id'), z, mark, client = false;
			if(fs.is(':visible')){
				$(this).css('background','url(pic/next.png) no-repeat center');
				fs.hide();
			}else{
				$(this).css('background','url(pic/prev.png) no-repeat center');
				fs.show();
				if(el && !('_map' in el)){
					xy = $(el).attr('value').split(/,/);
					if(xy.length >= 2){
						target = {lat: xy[0],lng:xy[1]};
						if(xy.length == 3) z = xy[2];
						client = true;
					}else{
						if('default_position' in window){
							xy = default_position.split(/,/)
							target = {lat: xy[0],lng:xy[1]}
							z = xy[2];
						}else{
							target = {lat:48,lng:33};
							z = 11;
						}
					}
					osm = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
					google = new L.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}',{
						attribution: 'google'
					});
					if(autonom_name && autonom_url) terra = new L.TileLayer(autonom_url,{
						crs: 'EPSG3395',
						reuseTiles: true,
						updateWhenIdle: false
					});
					yndx = new L.TileLayer('http://vec{s}.maps.yandex.net/tiles?l=map&v=18.03.01-4&z={z}&x={x}&y={y}&scale=1&lang=ru_RU',{
						crs: 'EPSG3395',
						subdomains: ['01', '02', '03', '04'],
						attribution: '<a http="yandex.ru" target="_blank">Яндекс</a>',
						reuseTiles: true,
						updateWhenIdle: false
					});
					baseLayer = terra||yndx||osm;
					baseLayers = {'Yandex':yndx, 'Google':google, 'OSM':osm};
					if(autonom_name && autonom_url) baseLayers[autonom_name] = terra;
					map = new L.Map(oid,{center: target, zoom: z, zoomAnimation: false });
					el._map = map;
					if(client) mark = new L.Marker(target).addTo(map)
					map.on('click',function(e){
						var p = e.latlng;
						if(!mark) mark = new L.Marker(p).addTo(map)
						else mark.setLatLng(p);
						$(el).attr('value',p.lat+','+p.lng+','+14)
					})
					if(baseLayer) el._map.addLayer(baseLayer);
					map.addControl(control_layers = new L.Control.Layers(baseLayers,{}));
					if(L.fullScreenBtn) map.addControl(L.fullScreenBtn)
				}
			}
			return false;
		})
	},
 
	initCheckSet = function(f){
		f.find('[type=checkset]').each(function(){
			var set = this,
			ch = function(o,val){
				var id = $(o).attr('id').replace(/[^0-9]/g,''),
					el = $(o).find('input[type=checkbox]').get(0),
					v = $(set).attr('value'), a = (v)? v.split(','):[];
				if(!('indexOf' in a)) alert('Ваш браузер не полностью поддерживаеи javaScript!')
				el.checked = val;
				if(val) a.push(id); else a.splice(a.indexOf(id),1);
				$(set).attr('value',a.join(','))
			}
			$(this).find('input[type=checkbox]').click(function(e){
				e.stopPropagation()
			}).change(function(e) {
				var el = $(this).parents('.checkitem').get(0);
				$(el).toggleClass('select')
				ch(el,$(el).hasClass('select'))
				return false;
			});
			$(this).on('click','.checkitem',function(e){
				$(this).toggleClass('select')
				ch(this,$(this).hasClass('select'))
				return false;
			})
		})
	},
 
	initArtSelect = function(f){
		f.find('[type=artselect]').on('click','li',function(){
			var ul = $(this).parents('ul').get(0);
			$(ul).attr('value',$(this).attr('id'))
			formSubmit();
			return false;
		})
	},

	formSubmit = function(){ // on press button submit
		if($.isFunction(opt.submit)) {
			opt.submit(getInputData(f))
			if(opt.destroy) FormDestroy()
		} else {
			var d = getInputData(f)
			if (!d || d=='') {
				if($.isFunction(opt.oncancel))
					opt.oncancel(f)
				FormDestroy()
			}else{
				opt.loader.get({ 
					data: d,
					onLoaded: onFormSave, 
					onError: showError,
					showLoading: formLoading
				})
			}
		}
	},

	showForm = function(txt) {
		f = $(txt).appendTo(cap.find('.formbox').get(0));
		f.find('table.normal').tableInit({
			loader: opt.loader
		})
		f.fadeIn()
		FormDestroy = function() {
			if($.isFunction(opt.ondestroy)) opt.ondestroy(f)
			$('.ac_results[fname='+opt.name+']').remove()
			$('body').unbind('keydown.'+f.attr('name'))
			cap.fadeOut(500,function(){$(this).remove()})
		}
		var
		current_ti = $('[tabindex=1]').hasClass('dateselect')? 2 : 1,
		myFocus = function(el) {
			if(typeof(el)!=='object') el = f.find('[tabindex='+current_ti+']')
			if(typeof(el)!=='object') return false;
			el.focus();
			if($(el).attr('tabindex')!=f.find(':focus').attr('tabindex')) el.focus();
			if(el.nodeName=='INPUT')
				if(el.setSelectionRange){
					var len = $(el).val().length * 2;
					if(!$(el).hasClass('summ')) el.setSelectionRange(len, len);
					else el.setSelectionRange(0, $(el).val().length);
				}else{
					if(!$(el).hasClass('summ')) $(el).val($(el).val());
				}
		},
		next = function(to) {
			var ti = f.find(':focus').attr('tabindex')
			if(!ti) ti = current_ti; else ti = Number(ti)
			var	add = ti,
				next = false,
				el = f.find('[tabindex='+Number(ti)+']').get(0)
			while(el){
				add = add + to
				if(next = f.find('[tabindex='+Number(add)+']').get(0)){
					el = next
				}else{
					if(to>0){
						add = 1;
					}else{
						add = $(f.find('[tabindex]').getmax('tabindex')).attr('tabindex')
					}
					el = f.find('[tabindex='+Number(add)+']').get(0);
				}
				if($(el).is(':visible') && !$(el).is(':disabled')) break
				if(add==ti) break
			}
			current_ti = $(el).attr('tabindex')
			return el
		}
		f.find('[tabindex]').on('focus',function(){
			current_ti = Number($(this).attr('tabindex'))
//			console.log('current_ti='+current_ti);
		})
		f.bind('keydown',function(e){
// 			console.log('keydown keyCode='+e.keyCode)
			if(e.keyCode==27) { // esc
				e.preventDefault()
				f.find('#cancelbutton').click()
				return false
			}
			if(e.keyCode==9) { // tab
				e.preventDefault()
				var to = (_shift)? -1:1;
				myFocus(next(to));
				return false
			}
			if(e.keyCode==16) { // shift
				e.preventDefault()
				_shift=true;
				return false
			}
			if(e.keyCode==13) { // return
				e.preventDefault()
				if($(e.target).hasClass('ac_field')) {
					var to = (_shift)? -1:1;
					myFocus(next(to));
				}else{
					f.find('#submitbutton').click()
				}
				return false
			}
		})
		f.bind('keyup',function(e){
			if(e.keyCode==16) { // shift
				e.preventDefault()
				_shift=false; 
				return false
			}
		})
		f.find('[tabindex]').bind('blur',function(e){
			var ti = $(this).attr('tabindex');
			if(ti>0) {
				ti++;
				var o = f.find('[tabindex='+ti+']')[0];
				if(!o && !$(e.relatedTarget).hasClass('dateselect')) 
					f.find('[tabindex=1]').get(0).focus();
			}
		})
		AutoCompleteInit(f)
		f.find('.dateselect').datepicker({dateFormat: 'dd-mm-yy'})
		onSelectInit(f)
		initPassword(f)
		initPhotoSelect(f)
		initPhotoList(f)
		initExtButton(f)
		initCheckSet(f)
		initArtSelect(f)
		f.on('mousewheel',function(e){
			if(!_shift) return;
			if(e.preventDefault) e.preventDefault();
			if(e.deltaY > 0) {
				f.css('opacity',+f.css('opacity')+0.1)
			}else{
				f.css('opacity',+f.css('opacity')-0.1)
			}
			return false;
		})
		f.find('#cancelbutton').bind('click',function(){
			var photo = f.find('[type=photo]')[0];
			if(photo){
				var v = $(photo).attr('value');
				if(photo.oldvalue != v){
					opt.loader.get({
						data:{go:'photo','do':'realremove',file:v},
						onLoaded:function(){}
					})
				}
			}
			if($.isFunction(opt.oncancel)) opt.oncancel(f)
			if($.isFunction(opt.cancel)) opt.cancel(f); else FormDestroy();
		});
		f.find('[onchange]').each(function(i,s){
			$(s).change()
		})
		f.find('#submitbutton').bind('click',formSubmit)
		f.find('.photobox').on('click',function(e){
			var img = $('img',this)[0],
				box = this,
				val = $(box).attr('value')
			$('<input type="file">').on('change',function(e){
				var fileinput = this;
				opt.loader.get({
					data: {go:'photo','do':'save'},
					input: fileinput,
					onLoaded: function(d){
						val = d.file;
						$(box).attr('value',val);
						$(img).attr('src',val);
						$(fileinput).remove()
					}
				})
			}).click()
		})
		if(opt.focus) myFocus(f.find('[name='+opt.focus+']').get(0));
		else myFocus(f.find('[tabindex='+current_ti+']').get(0));
		if('setNewVal' in opt) setNewVal(opt.setNewVal);
	},

	showError = function(data) {
		var f = cap.find('form').get(0)
		if(!f) {
			showForm($.errorForm(data));
		}else{
			$(f).hide()
			var txt = $.errorForm(data),
				ef = $(txt).appendTo(cap.find('.formbox'))
			ef.fadeIn()
			ef.find('#cancelbutton').bind('click',function(){
				ef.fadeOut(500,function(){
					$(this).remove()
					$(f).fadeIn()
				})
			})
		}
		opt.onerror(data);
	},

	/* Блокирование элемента при запросе и получении данных */
	formLoading = function(sw) {
		if(sw) {
			ldcap.appendTo('body');
			$(ldcap).fadeIn();
		}else{
			$(ldcap).fadeOut(200,function(){
				ldcap.remove()
			});
		}
	},

	onLoaded = function(data) {
		opt.ongetform(data)
		if(typeof(data)=='object') {
			if(data['form']) {
				opt.name = data.form.name
				if('setNewVal' in data.form) opt.setNewVal = data.form.setNewVal
				if('focus' in data.form) opt.focus = data.form.focus
				data.form.SFdata = function(d) {
					if($.isArray(d)&&d.length==0) 
						subFormData = new Object() 
					else subFormData = d
				}
				var txt = $.buildForm(data.form)
				showForm(txt)
			}else{ 
				showError("Сервер не вернул данные для формы")
			}
		}
		if(typeof(data)=='string'){
			showError(data)
		}
	},

	/* Обработка события успешного сохранения данных */
	onFormSave = function(data) {
		var d, o;
		data.obj = opt.obj
		if($.isFunction(opt.onsubmit))
			if(!data['form'])
				d = opt.onsubmit(data)
		FormDestroy();
		if(typeof data == 'object' && data['form']) $.popupForm({ 
			data:data,
			obj:opt.obj,
			onsubmit:opt.onsubmit,
			oncancel:opt.oncancel,
			loader:opt.loader
		})
	}

	cap.fadeIn()
	switch(opt.type) {
		case 'info':
			showForm($.infoForm(opt.data))
			break;
		case 'error':
			showForm($.errorForm(opt.data))
			break;
		case 'confirm':
			showForm($.confirmForm(opt.data))
			break;
		default:
			if(typeof opt.data == 'string'){
				opt.loader.get({
					data: opt.data,
					onLoaded: onLoaded,
					onError: showError,
					showLoading: formLoading
				})
			}else if(typeof opt.data == 'object' && opt.data.form){
				onLoaded(opt.data)
			}
	}
	return f;
}
}) (jQuery);
