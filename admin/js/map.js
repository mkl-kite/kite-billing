var 
	Gtypes = {},
	rayonView = function(){},
	rayons = false,
	tfade = [],
	devtypes = {},
	map,osm,yndx,
	objects, popup = L.popup(), control_layers,
	nagiosURL = '',
	onuSignalURL = '',
	onOpenNode = false,
	onOpenDevice = false

function Compare(_old,_new) {
	var cmp={}
	if(typeof(_old)=='object' && typeof(_new)=='object') {
		for(var i in _new) if(i in _old && _old[i]!=_new[i]) cmp[i]=_new[i]
	}
	return cmp
}

function getFadeStyle(value){
	if(!tfade) return 0;
	var n;
	for(n=0; n < tfade.length; n++) {
		if(value > tfade[n]) break;
	}
	return n;
}

(function ($) {
$.each(['show', 'hide'], function (i, ev) {
	var el = $.fn[ev];
	$.fn[ev] = function () {
		this.trigger(ev);
		return el.apply(this, arguments);
	};
});
})(jQuery);

$(document).ready(function() {

	function clone(o) {
		var n = {}
		for(var i in o) n[i] = o[i]
		return n
	}
	L.Circle.include({
		toGeoJSON: function() {
			var circleProperties = function(circle) {
					return {
						point_type: 'circle',
						radius: circle.getRadius()
					};
				}
			if(this.feature && this.feature.properties) {
				return {
					type: 'Feature',
					properties: this.feature.properties,
					geometry: {
						type: 'Point',
						coordinates: L.GeoJSON.latLngToCoords(this.getLatLng()),
						properties: circleProperties(this)
					}
				}
			}else{
				return {
					type: 'Point',
					coordinates: L.GeoJSON.latLngToCoords(this.getLatLng()),
					properties: circleProperties(this)
				}
			}
		}
	})
	L.GeoObjects = L.FeatureGroup.extend({
		initialize: function (layers) {
			this.selectId = false;
			this.dbid = [];
			L.FeatureGroup.prototype.initialize.call(this, layers);
		},
		getObjectByID: function(id) {
			if(typeof(this.dbid[id]) == 'object') return this.dbid[id]
			return false;
		},
		isSelected: function(layer){
			if(this.hasLayer(layer)){
				if(this.selectId == this.getLayerId(layer)) return true
			}
			return false
		},
		deSelect: function(layer){
			if(this.hasLayer(layer)){
				this.selectId = false
				this.fire('deselect', {layer: layer});
			}
			return this
		},
		Select: function(layer){
			if(this.hasLayer(layer)){
				if(this.isSelected(layer)){
					this.deSelect(layer)
				}else{
					if(this.selectId) this.deSelect(this.getLayer(this.selectId))
					this.selectId = this.getLayerId(layer)
					this.fire('select', {layer: layer});
				}
			}
			return this
		},
		setProperties: function(layer,props) {
			try{
				var id = props.id,
					old = clone(layer.feature.properties),
					cmp = Compare(old,props)
				if(this.hasLayer(layer) && Object.keys(cmp).length>0) {
					for(var k in cmp) {
						if(k=='id') {
							if(!this.dbid[cmp.id]) {
								delete this.dbid[old.id]
								this.dbid[cmp.id]=layer
							}else{
								console.log("error change feature id, new_id="+id+"; object exist!")
							}
						}
						if(k=='type') console.log("error change feature "+k+" id="+id)
						else layer.feature.properties[k] = cmp[k];
					}
					this.fire('onchange', {layer: layer, old: old, new: cmp});
					delete old;
				}
			}catch(e){
//				console.log("error change feature "+e.name+" "+e.message);
			}
			return this;
		},
		addLayer: function(layer) {
			L.FeatureGroup.prototype.addLayer.call(this, layer);
			if('id' in layer.feature.properties){
				this.dbid[layer.feature.properties.id] = layer
			}
		},
		removeLayer: function(layer) {
			if(layer.feature && 'id' in layer.feature.properties) {
				delete this.dbid[layer.feature.properties.id]
			}
			L.FeatureGroup.prototype.removeLayer.call(this, layer);
		}
	})
	L.Control.List = L.Control.extend({
		options: {
			collapsed: true,
			position: 'topright',
			autoZIndex: true,
			listName: 'menu'
		},

		initialize: function (items, fn, options) {
			L.setOptions(this, options);

			this._listitems = {};
			this._handlingClick = false;
			this._clickItem = (typeof(fn)=='function') ? fn : function(){};

			for (var i in items) {
				this._addMyItem(items[i], i);
			}
		},

		onAdd: function (map) {
			this._initLayout();
			this.update();
			return this._container;
		},

		reload: function(items) {
			if(typeof(items) !== 'undefined') {
				this._listitems = {};
				for (var i in items) {
					this._addMyItem(items[i], i);
				}
			}
			this.update();
		},
		
		getItemByID: function (id) {
			for(var i in this._listitems){
				if(this._listitems[i].id == id)
					return this._listitems[i];
			}
			return false;
		},
		
		addItem: function (item, name) {
			this._addMyItem(item, name);
			this.update();
			return this;
		},

		removeItem: function (item) {
			var id = item.id;
			delete this._listitems[id];
			this.update();
			return this;
		},

		_addMyItem: function (item, name) {
			var id = name,
			obj = this._listitems[id] = item;
			obj.listId = id;
		},

		_initLayout: function () {
			var className = 'leaflet-control-layers',
				container = this._container = L.DomUtil.create('div', className);

			//Makes this work on IE10 Touch devices by stopping it from firing a mouseout event when the touch is released
			container.setAttribute('aria-haspopup', true);

			if (!L.Browser.touch) {
				L.DomEvent
					.disableClickPropagation(container)
					.disableScrollPropagation(container);
			} else {
				L.DomEvent.on(container, 'click', L.DomEvent.stopPropagation);
			}

			var form = this._form = L.DomUtil.create('form', className + '-list');

			if (this.options.collapsed) {
				if (!L.Browser.android) {
					L.DomEvent
						.on(container, 'mouseover', this._expand, this)
						.on(container, 'mouseout', this._collapse, this);
				}
				var link = this._listitemsLink = L.DomUtil.create('a', className + '-' + this.options.listName, container);
				link.href = '#';
				link.title = 'List';

				if (L.Browser.touch) {
					L.DomEvent
						.on(link, 'click', L.DomEvent.stop)
						.on(link, 'click', this._expand, this);
				}
				else {
					L.DomEvent.on(link, 'focus', this._expand, this);
				}
				this._map.on('click', this._collapse, this);
			} else {
				this._expand();
			}

			this._itemsList = L.DomUtil.create('div', className + '-item', form);

			container.appendChild(form);
		},

		update: function (d) {
			if (!this._container) {
				return;
			}
			this._itemsList.innerHTML = '';
			if (typeof d !== 'undefined') {
				this._itemsList.innerHTML = d;
				this._expand()
			} else {
				if (L.DomUtil.hasClass(this._container, 'leaflet-control-layers-expanded'))
					this._container.className = this._container.className.replace(' leaflet-control-layers-expanded', '');
				var i, obj;
				for (i in this._listitems) {
					obj = this._listitems[i];
					this._addItem(obj);
				}
			}
		},

		_addItem: function (item) {
			var label = document.createElement('label');
			label.className = 'list-item';

			label.item = item;
			label.list = this;
			L.DomEvent.on(label, 'click', this._onLabelClick, label);

			var name = document.createElement('span');
			name.innerHTML = ' ' + item.name;

			label.appendChild(name);

			var container = this._itemsList;
			container.appendChild(label);

			return label;
		},

		_onLabelClick: function () {
			this._handlingClick = true;
			var obj = this.item;
			this.list._clickItem(obj)
			this._handlingClick = false;
		},

		_expand: function () {
			L.DomUtil.addClass(this._container, 'leaflet-control-layers-expanded');
		},

		_collapse: function () {
			this._container.className = this._container.className.replace(' leaflet-control-layers-expanded', '');
		}
	});

	ltype = function(l){ // определяет в какой из слоёв пихнуть клиента
		var
		types = {pon:0,ftth:1,wifi:2}, p;
		if(typeof l !== 'object') return false;
		if(l.feature) p = l.feature.properties;
		else if(l.properties) p = l.properties;
		else p = l;
		if(p.type == 'client' && p.subtype && p.subtype in types) return 'cl_'+p.subtype;
		else return p.type;
	}
	arrstr = function(a){ // для вывода содержимого массивов в console.log
		if(typeof a !== 'object' || !a) return '';
		var keys = Object.keys(a), i, k, n=[], s='{';
		for(i in keys){
			k = keys[i];
			if(typeof a[k] === 'object' && a[k])
				n.push(k+':'+arrstr(a[k]));
			else if(typeof a[k] === 'string')
				n.push(k+':"'+a[k]+'"');
			else
				n.push(k+':'+a[k]);
		}
		s = s + n.join(', ') + '}';
		return s;
	}
	// Стили для разных объектов
	var
	selectedstyle = {color:'#f0f',opacity:'0.9',weight:4},
	nodestyle 		= {color:'#000',opacity:0.8,fillColor:'#808',fillOpacity:0.5,weight:1},
	nodeSelectStyle = {color:'#f0f',opacity:0.8,fillColor:'#dd0',fillOpacity:0.8,weight:4},
	defaultLinkStyle= {color:'#669',opacity:0.8,fillColor:'#000',fillOpacity:0.0,weight:4,dashArray:0,dashOffset:0},
	linkstyle		= {},
	linkSelectStyle = {color:'#b0b',opacity:0.8,fillColor:'#000',fillOpacity:0.0,weight:5,dashArray:0,dashOffset:0},
	linkHoverStyle	= {color:'#449',opacity:0.8,fillColor:'#000',fillOpacity:0.0,weight:4,dashArray:'5,10',dashOffset:0},
	linkWifiStyle   = {color:'#84f',weight:4,opacity:0.4,fill:false,clickable:false,dashArray:'5,10'},
	user_state = ['unknown','online','offline','gone'],
	claimstyle = function(n){
		var t = claimIcons, ic = 'pic/marker-', sh = 'js/images/marker-shadow.png';
		if(!n) n=0; if(n<0||n>t.length-1) n = t.length;
		return {iconUrl:ic+t[n]+'.png',shadowUrl:sh,iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]}
	},
	clientstyle = function(n){
		if(!n) n=7;
		return {iconUrl:'pic/client'+n+'.png',shadowUrl:'pic/client-shadow.png',iconSize:[15,30],shadowSize:[27,15],iconAnchor:[8,29],shadowAnchor:[9,14],popupAnchor:[-2,-28]}
	},
	reservstyle = function(n){
		return {iconUrl:'pic/store.png',shadowUrl:'pic/store-shadow.png',iconSize:[20,30],shadowSize:[25,10],iconAnchor:[11,27],shadowAnchor:[11,7],popupAnchor:[-2,-28]}
	},
	fadingColor = ['#888','#f38','#e83370','#e36','#d83355','#d34','#c33','#b32'];
	diviconstyle = function(txt,id){
		return {icon:L.divIcon({iconSize: new L.Point(30, 18), html:txt,className: 'div-icon fade-div-icon'+id})}
	},
	homeStyle = function(state){
		var s = {fillColor:'#444',fillOpacity:'0.7',color:'#444',opacity:'0.8',weight:1},
		colors = ['#0f0','#b6ff6d','#f7ff6d','#ffe66d','#fea953','#ff3939'], c, n;
		if(state==0) { s.fillColor = '#4f4'; s.color = '#282' }
		if(state==1) { s.fillColor = '#f88'; s.color = '#822' }
		if(state==2) { s.fillColor = '#b33'; s.color = '#862' }
		if(state>3) {
			c = state * (-1);
			n = getFadeStyle(c);
			if(fadingColor[n+1]) { s.fillColor = colors[n]; s.color = '#282' }
		}
		return s
	},
	claimTypes = [],
	claimIcons = [],
	traceline = false,
	tracewifi = false,
	linkHoverInerval = false,

	divicons	= new L.FeatureGroup(),
	drawnItems	= new L.FeatureGroup(),
	Homes		= new L.GeoObjects(),
	Nodes		= new L.GeoObjects(),
	Links		= new L.GeoObjects(),
	WiFiLinks	= new L.FeatureGroup(),
	cl_pon		= new L.GeoObjects(),
	cl_ftth		= new L.GeoObjects(),
	cl_wifi		= new L.GeoObjects(),
	Reserves	= new L.GeoObjects(),
	Claims		= new L.GeoObjects();
	Gtypes = {
		home:Homes,
		node:Nodes,
		cable:Links,
		wifilink:WiFiLinks,
		cl_pon:cl_pon,
		cl_ftth:cl_ftth,
		cl_wifi:cl_wifi,
		reserv:Reserves,
		claim:Claims
	};
	var clstyle = function(e){ // определяет стиль для клиента по состоянию
		if(typeof e.layer !== 'object') return false;
		var p = e.layer.feature.properties, c, s, to, t = ltype(p);
		if(p.type != 'client') return false;
		if(typeof e.new !== 'object') e.new = {};
		if(typeof e.old !== 'object') e.old = {};
		if('subtype' in e.new){
			to = ltype(e.old)
			Gtypes[to].removeLayer(e.layer);
			Gtypes[t].addLayer(e.layer);
		}
		if(p.subtype != 'wifi'){
			if('state' in e.new) c = e.new.state;
			else if(p.state && !('subtype' in e.new)) c = p.state;
			else c=0;
			if(c>3) c = c * (-1);
			if(c == 0) s=1
			else if(c == 1) s=5
			else if(c == 2 || c == 3) s=6
			else {
				for(s=0; s < tfade.length; s++) {
					if(c > tfade[s]) break;
				}
			}
		}else{ if(p.connect) s=7; else s=8; }
		var icon = new L.Icon(clientstyle(s+1));
		e.layer.setIcon(icon);
		return true;
	}

	drawnItems._drawenabled = false;

	L.Draw.Reserv = L.Draw.Marker.extend({
		statics: {
			TYPE: 'reserv'
		},
		initialize: function (map, options) {
			this.type = L.Draw.Reserv.TYPE;
			L.Draw.Feature.prototype.initialize.call(this, map, options);
		},
		options: {icon: new L.Icon(reservstyle())}
	});

	L.DrawToolbar.include({
		options: {
			polyline: {},
			polygon: {},
			reserv: {},
			circle: {},
			marker: {}
		},
		addToolbar: function (map) {
			var container = L.DomUtil.create('div', 'leaflet-draw-section'),
				buttonIndex = 0, name,
				buttonClassPrefix = 'leaflet-draw-draw';

			this._toolbarContainer = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar');

			for (var i in this.options){
				name = i.charAt(0).toUpperCase() + i.substr(1);
				if (this.options[i]) {
					this._initModeHandler(
						new L.Draw[name](map, this.options[i]),
						this._toolbarContainer,
						buttonIndex++,
						buttonClassPrefix,
						L.drawLocal.draw.toolbar.buttons[i]
					);
				}
			}

			this._lastButtonIndex = --buttonIndex;

			this._actionsContainer = this._createActions([
				{
					title: L.drawLocal.draw.toolbar.actions.title,
					text: L.drawLocal.draw.toolbar.actions.text,
					callback: this.disable,
					context: this
				}
			]);

			container.appendChild(this._toolbarContainer);
			container.appendChild(this._actionsContainer);

			return container;
		}
	});

	L.Draw.Marker.include({options:{icon: new L.Icon(clientstyle(7))}})

	objects = new L.GeoObjects();
	
	function loadClaims(cl){
		ldr.get({
			data:"go=claims&do=get4map"+((cl)?'&claims='+cl:''),
			onLoaded:function(d){
				var n, l, c;
				if(!cl && 'Заявки' in overlays) overlays['Заявки'].eachLayer(function(l){
					overlays['Заявки'].removeLayer(l);
					l = false;
				})
				if('claimtypes' in d) claimTypes = d.claimtypes;
				if('claimicons' in d) claimIcons = d.claimicons;
				if('claims' in d && d.claims) {
					if(cl){
						c = cl.split(/,/);
						for(n in c){
							l = objects.getObjectByID(c[n]);
							if(l) overlays['Заявки'].removeLayer(l)
							l = false;
						}
					}
					for(n in d.claims){
						c = d.claims[n].location.split(/,/);
//						l = new L.Marker([c[0],c[1]]);
						l = L.marker([c[0],c[1]], {icon: new L.Icon(claimstyle(d.claims[n].ctype))})
						l.feature = {properties: d.claims[n]};
						l.feature.properties.type = 'claim';
						l.addTo(overlays['Заявки']);
					}
				}
			}
		})
	}

	drawnItems.on('layerremove',function(e){
		if(drawnItems._drawenabled) return true;
		if(!map.hasLayer(e.layer) && objects.hasLayer(e.layer)){
			var p = e.layer.feature.properties, l;
			map.addLayer(e.layer);
			if(p.type == 'home')
				e.layer.bringToBack();
			if(p.type == 'cable'){
				if((l = objects.getObjectByID(p.dev_node1)) && l.feature.properties.type=='node') l.bringToFront();
				if((l = objects.getObjectByID(p.dev_node2)) && l.feature.properties.type=='node') l.bringToFront();
			}
		}
	})

	objects.on('layeradd',function(e){
		var p = e.layer.feature.properties, el;
		if(p.type in {client:0,home:0,node:0}){
			if(!M.rootNode){
				M.rootNode = new M.TreeNode('дерево объектов');
				M.rootNode.setState(2);
				$('#objects').append(M.rootNode._elem);
				$('.lihead',M.rootNode._elem).remove();
				$(M.rootNode._header).empty();
				el = M.rootNode.addElem(new M.TreeNode('районы')).setState(2);
			}
			e.layer.treeNode = M.addTreeNodeByAddress(p);
		}
		var type = ltype(e.layer)
		if(type in Gtypes){
			Gtypes[type].addLayer(e.layer)
		}
	})

	objects.on('layerremove',function(e){
		var type = ltype(e.layer), n;
		if(type in Gtypes)
			Gtypes[type].removeLayer(e.layer)
		e.layer.treeNode = false
		if(n = M.getTreeNodeByType(e.layer.feature.properties))
			n.remove();
	})

	objects.on('select',function(e){
		if(drawnItems._drawenabled) return true;
		var type = ltype(e.layer), p = e.layer.feature.properties, n;
		if(type in Gtypes) Gtypes[type].Select(e.layer)
		if(n = M.getTreeNodeByType(p)) n.show()
		if(type != 'claim' && map.hasLayer(e.layer))
			drawnItems.addLayer(e.layer);
	})

	objects.on('deselect',function(e){
		var type = ltype(e.layer)
		if(type in Gtypes) Gtypes[type].deSelect(e.layer)
		if(e.layer) drawnItems.removeLayer(e.layer);
	})

	objects.on('onchange',function(e){
		var type = ltype(e.layer);
		if(e.layer.treeNode && e.layer.treeNode.onUpdate)
			e.layer.treeNode.onUpdate(e);
		if(type in Gtypes){
			Gtypes[type].fire('onchange',e)
		}
	})

	Homes.on('select',function(e){
		var l = e.layer, c, p = l.feature.properties,
			b = l.getBounds(),
			lat = b.getNorthWest().lat, lng = b.getCenter().lng,
			point = new L.LatLng(lat,lng),
			el = M.getTreeNodeByType(p);
		if(el) el.show();
		var style = $.extend(homeStyle(p.state),selectedstyle)
		l.setStyle(style)
		delete style
		if(!map.getBounds().contains(b))
			if(c = e.layer.getBounds().getCenter()) rayonView(c.lat,c.lng)
		popup.setLatLng(point).setContent(popup_description(e.layer)).openOn(map);
	})

	Homes.on('deselect',function(e){
		var p = e.layer.feature.properties, style = homeStyle(p.state)
		e.layer.setStyle(style)
		map.closePopup(popup)
	})

	Homes.on('onchange',function(e){
		var p = e.layer.feature.properties, c = false, d={}, m;
		if('state' in e.new){
			var style = {}, c = false
			if(p.id==this.selected) {
				style = homeStyle(e.new.state)
				c = true
				style = $.extend(style,selectedstyle)
			}else{
				style = homeStyle(e.new.state)
			}
			e.layer.setStyle(style)
			if(c) delete style
		}
		if('service' in e.new){
			if((m = e.new.service.match(/^id([0-9]+)$/))){
				d[p.id] = m[1];
				talkIcinga(((p.id == m[1])?'getstate':'setlinks'),d);
			}else if(!e.new.service && e.old.service){
				d[p.id] = e.old.service.replace(/[^0-9]/g,'');
				talkIcinga('unsetlinks',d);
			}
		}
	})

	Nodes.on('select',function(e){ // Раскрывает/Закрывает список устройств
		var l = e.layer, c, p = l.feature.properties, el = M.getTreeNodeByType(p);
		if(el) el.show()
		l.setStyle(nodeSelectStyle).setRadius(13)
		if(!map.getBounds().contains(l.getBounds()))
			if(c = l.getLatLng()) rayonView(c.lat,c.lng)
	})

	Nodes.on('deselect',function(e){
		e.layer.setStyle(nodestyle).setRadius(10)
	})

	Links.on('select',function(e){
		var b = e.layer.getBounds(), c, el,
			 line = e.layer.getLatLngs(), begin = line[0], end = line[line.length-1];
		if(linkHoverInerval) clearInterval(linkHoverInerval);
		e.layer.setStyle(linkSelectStyle)
		if(!map.getBounds().contains(begin) && !map.getBounds().contains(end))
			if(c = e.layer.getBounds().getCenter()) rayonView(c.lat,c.lng)
		if(el = M.getTreeNodeByType(e.layer.feature.properties)) el.show();
	})

	Links.on('deselect',function(e){
		var st = e.layer.feature.properties.dev_subtype, ls = (linkstyle[st])? linkstyle[st]:defaultLinkStyle;
		e.layer.setStyle(ls)
	})

	cl_ftth.on('select',function(e){
		if(!map.getBounds().contains(e.layer.getLatLng()))
			if(c = e.layer.getLatLng()) rayonView(c.lat,c.lng)
		e.layer.bindPopup(popup_description(e.layer)).openPopup();
	})

	cl_ftth.on('deselect',function(e){
		e.layer.closePopup()
		e.layer.unbindPopup()
	})

	cl_pon.on('select',function(e){
		if(!map.getBounds().contains(e.layer.getLatLng()))
			if(c = e.layer.getLatLng()) rayonView(c.lat,c.lng)
		e.layer.bindPopup(popup_description(e.layer)).openPopup();
	})

	cl_pon.on('deselect',function(e){
		e.layer.closePopup()
		e.layer.unbindPopup()
	})

	cl_wifi.on('select',function(e){
		if(!map.getBounds().contains(e.layer.getLatLng()))
			if(c = e.layer.getLatLng()) rayonView(c.lat,c.lng)
		e.layer.bindPopup(popup_description(e.layer)).openPopup();
	})

	cl_wifi.on('deselect',function(e){
		e.layer.closePopup()
		e.layer.unbindPopup()
	})

	Reserves.on('select',function(e){
		if(!map.getBounds().contains(e.layer.getLatLng()))
			if(c = e.layer.getLatLng()) rayonView(c.lat,c.lng)
		e.layer.bindPopup(popup_description(e.layer)).openPopup();
	})

	Reserves.on('deselect',function(e){
		e.layer.closePopup()
		e.layer.unbindPopup()
	})

	
	if(M && M.conf) mconf = M.conf.get('map');
	if(typeof mconf === 'undefined') { M.conf.save('map',{}); mconf = M.conf.get('map') }

	var wmap = Math.floor($('#all').width() * 0.7 - 2);
	if(typeof mconf.trg === 'undefined') {
		mconf.trg = {width: $('#all').width() - wmap}
		M.conf.save('map',mconf)
	}else{
		if(mconf.trg.width <= 700) wmap = $('#all').width() - mconf.trg.width -2
	}
	var deltaW = Math.floor($(window).width() - $('#all').width()), wwStart = false;
	$('#map').outerWidth(wmap);
	$('#targets').outerWidth(Math.floor($('#all').width() - $('#map').outerWidth()));
	$('#map').resizable({
		handles: 'e',
		minWidth: '600',
		maxWidth: Math.floor(($(window).width() - deltaW) * 0.8),
		helper: 'ui-resizable-helper',
		start: function(e,ui){
			wwStart = Math.floor($(window).width());
			var ww = wwStart, mw = ui.size.width;
			console.log("start:   win: "+ww+"   map: "+mw);
		},
		stop: function(e,ui){
			var ww = (wwStart)? wwStart : $(window).width(), mw = ui.size.width;
			console.log("stop:   win: "+ww+"   map: "+mw);
			mconf.trg.width = Math.floor(ww - deltaW - mw - 2);
			M.conf.save('map',mconf);
			$('#targets').outerWidth(mconf.trg.width);
			map.invalidateSize();
			wwStart = false;
		}
	})
	$(window).resize(function(e) {
		if(wwStart) return this;
		var head = $('#map').offset()['top'], top = $(window).height() - head - 10,
			ww = $(window).width(), mw = $('#map').outerWidth(), tw = $('#targets').outerWidth();
		console.log("resize:   win: "+ww+"   map: "+mw+"  tw: "+tw);
		$('#map').outerHeight(top);
		$('#targets').outerHeight(top);
		$('#map').resizable('option',{ maxWidth: Math.floor(ww * 0.8) });
		$('#map').outerWidth(Math.floor(ww - deltaW - tw - 2));
	})
	$(window).trigger('resize');

	var ldr = $.loader(), target, mconf;
	if('place' in mconf)
		target = new L.LatLng(mconf.place.lat,mconf.place.lng)
	else
		target = new L.LatLng(48.0178386687,38.7565791607)
/*
	var toggleCRS = function(e){
		var xy = map.getCenter(), z = map.getZoom();
		if(e){
			map._oldCRS = map.options.crs;
			map.options.crs = L.CRS.EPSG3395;
		}else{
			map.options.crs = map._oldCRS;
			delete(map._oldCRS);
		}
		map.setView(xy,z);
	}
*/
	osm = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
	google = new L.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}',{
		attribution: 'google'
	});
	if(autonom_name && autonom_url) terra = new L.TileLayer(autonom_url,{
		crs: 'EPSG3395',
		reuseTiles: true,
		updateWhenIdle: false,
		attribution: '<a href="'+(company_url||'#menu')+'" target="_blank">'+autonom_name+'</a>'
	});
//	terra.on('add',function(){ toggleCRS(true); })
//	terra.on('remove',function(){ toggleCRS(false); })
	yndx = new L.TileLayer('https://vec{s}.maps.yandex.net/tiles?l=map&v=18.03.01-4&z={z}&x={x}&y={y}&scale=1&lang=ru_RU',{
		crs: 'EPSG3395',
		subdomains: ['01', '02', '03', '04'],
		attribution: '<a http="yandex.ru" target="_blank">Яндекс</a>',
		reuseTiles: true,
		updateWhenIdle: false
	});
//	yndx.on('add',function(){ toggleCRS(true); })
//	yndx.on('remove',function(){ toggleCRS(false); })
	// googleLayer = new L.Google('ROADMAP')
	map = new L.Map('map', {center: target, zoom: 16, zoomAnimation: false })
	var baseLayers = {'Yandex':yndx, 'Google':google, 'OSM':osm},
	overlays = {
		"Объекты":Homes,
		"Узлы":Nodes,
		"Коммуникации":Links,
		"Wi-Fi линки":WiFiLinks,
		"Клиенты FTTH":cl_ftth,
		"Клиенты PON":cl_pon,
		"Клиенты Wi-Fi":cl_wifi,
		"Запасы":Reserves,
		"Заявки":Claims
	}
	if(autonom_name && autonom_url) baseLayers[autonom_name] = terra;

	// Показываем на карте диаграммы по домам
	function popup_description(l) {
		var p = l.feature.properties, _go = 'homes', img = '', a = '',
			o = '', note = p.note,
			name = (p.address)? p.address : p.name;
		if(p.type == 'claim') {
			_go='claims';
			if(!p.woid) a = '<img class="button" title="Создать новый наряд" src="pic/add.png" query="go='+
				'stdform&do=new&table=workorders&claims='+p.id+'&object=none" />'+
				'<img class="button" title="Добавить в наряд" src="pic/go.png" query="go=worders&do='+
				'list&table=workorders&claims='+p.id+'&object=none" />';
			note = ((p.woid)?'<span class="linkform" add="go=stdform&do=edit&table=workorders&id='+p.woid+'">'+p.woid+' </span>':'')+
			claimTypes[p.ctype]+((p.prescribe)? ' на <b>'+p.prescribe+'</b>':'')+((p.begintime)? ' <b>'+p.begintime+'</b>' : '')+'<br>'+p.content;
		}
		if(p.type == 'client') {
			_go='clients'; a = '<a href="users.php?user='+p.name+'" target="blank"><img class="button" src="pic/go.png"/></a>';
			if(p.subtype=='pon' && p.state>3) note='<p style="position:relative">Уровень сигнала: &emsp; <b>'+p.state*(-1)+'</b></p>';
		}
		if(p.type == 'reserv') _go='reserv';
		return '<div><h4 style="white-space:nowrap">'+name+'&nbsp&nbsp'+
			'<img class="button" src="pic/conf.png" query="go='+_go+'&do=edit&id='+p.id+'" />'+a+
			'<br><div class="online"></div></h4>'+
			((note)?'<p>'+note+'</p>':'')
	}

	var addFeature = function(feature, layer) {
		var p = feature.properties;
		if(feature.properties.id) {
			switch(p.type) {
				case 'home':
					layer.feature.properties.center = layer.getBounds().getCenter();
					layer.feature.properties.state = 3;
					break
				case 'client':
					var t = ltype(feature);
					if(t == 'cl_wifi') layer.feature.properties.state = 0;
					else layer.feature.properties.state = 3;
					break;
			}
			if(p.type == 'claim') layer.addTo(overlays['Заявки'])
			else if(p.type == 'wifilink') layer.addTo(overlays['Wi-Fi линки'])
			else layer.addTo(objects)
		}
	},
	point2layer = function (feature, latlng) {
		var type,radius,p;
		try{
			p = feature.properties
			type = ltype(feature)
			radius = feature.geometry.properties.radius
		}catch(e){
			console.log("point2layer ERROR "+e.name+" "+e.message);
		}
		if(type=='node')
			return L.circle(latlng, radius, nodestyle);
		else if(type=='cl_ftth' || type=='cl_pon') 
			return L.marker(latlng, {icon: new L.Icon(clientstyle(7))});
		else if(type=='cl_wifi') 
			return L.marker(latlng, {icon: new L.Icon(clientstyle(8))});
		else if(type=='reserv') 
			return L.marker(latlng, {icon: new L.Icon(reservstyle())});
		else if(type=='divicon') 
			return L.marker(latlng, diviconstyle(p.fade,p.style));
		else
			return L.circleMarker(latlng,{radius: 10,fillColor: "#ff7800",color: "#000",weight: 1,opacity: 1,fillOpacity: 0.8})
	}

	var geoJson2map = function(d,eachFeature){
		if(typeof(eachFeature)!='function') 
			eachFeature=addFeature;
		return L.geoJson(d, {
			onEachFeature: eachFeature,
			pointToLayer: point2layer
		})
	}

	var arrangeLayers = function () {
		if(map.hasLayer(overlays["Узлы"])) 
			overlays["Узлы"].bringToBack()
		if(map.hasLayer(overlays["Коммуникации"])) 
			overlays["Коммуникации"].bringToBack()
		if(map.hasLayer(overlays["Объекты"])) 
			overlays["Объекты"].bringToBack()
		if(traceline && map.hasLayer(traceline)) 
			traceline.bringToFront()
	}

	// Добавление дома
	Homes.on('layeradd', function(e){
		e.layer.feature.properties.center = e.layer.getBounds().getCenter();
		e.layer.feature.properties.state = 3;
		e.layer.setStyle(homeStyle(3))
		e.layer.on('mouseover', function(e){
			info.set(this.feature.properties)
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(this);
		})
	})
	// Удаление дома
	Homes.on('layerremove', function(e){
	})
	// Добавление узла
	Nodes.on('layeradd', function(e){
		e.layer.setStyle(nodestyle)
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(this);
		})
		e.layer.on('mouseover', function(e){
			info.set(this.feature.properties)
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
	})
	// Удаление узла
	Nodes.on('layerremove', function(e){
	})
	Nodes.on('onchange', function(e){
	})
	// Добавление WiFi линка
	WiFiLinks.on('layeradd', function(e){
		e.layer.setStyle(linkWifiStyle);
		var p = e.layer.feature.properties,
		line = e.layer.getLatLngs(), begin = line[0], end = line[line.length-1];
		p.length = Math.round(begin.distanceTo(end) / 100)/10;
		e.layer.on('mouseover', function(e){
			info.set(p)
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
	})
	// Удаление WiFi линка
	WiFiLinks.on('layerremove', function(e){
	})
	// Добавление кабеля
	Links.on('layeradd', function(e){
		var st = e.layer.feature.properties.dev_subtype, ls = (linkstyle[st])? linkstyle[st]:defaultLinkStyle;
		e.layer.setStyle(ls)
		var p = e.layer.feature.properties, d = {}, m, li, n, n1, n2;
		if(M.rootNode && M.rootNode.kinds && M.rootNode.kinds.node){
			for(var n in p) if(m = n.match(/^dev_(.*)/)) d[m[1]] = p[n];
			n = M.allNodes[M.rootNode.kinds.node[d.node1]];
			if(!n || n.state != 2) n = M.allNodes[M.rootNode.kinds.node[d.node2]];
			if(!n || n.state != 2) n = false;
			if(n){
				n1 = objects.getObjectByID((n.options.data.id==d.node1)?d.node2:d.node1);
				d.name = "на "+((n1)? n1.feature.properties.address:'пустой конец');
				n.addElem(new M.Device({data:d}));
			}
		}
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			var str, p = this.feature.properties, _go='devices', el,
			n1 = objects.getObjectByID(p.dev_node1),
			n2 = objects.getObjectByID(p.dev_node2),
			a1 = (n1)? n1.feature.properties.address:'',
			a2 = (n2)? n2.feature.properties.address:'';
			if(el = M.getTreeNodeByType(d)){
				el.show()
			}else if(objects.isSelected(this)){
				str = '<div><h4>'+'кабель('+p.dev_numports+'ж) '+p.length+'м <span style="float:right">'+
				'<img class="button" src="pic/conf.png" query="go=devices&do=edit&id='+p.dev_id+'" /> '+
				'<img class="button" src="pic/cut.png" query="go=devices&do=cutform'+
				'&lat='+e.latlng.lat+'&lng='+e.latlng.lng+'&id='+p.dev_id+'" />'+
				'</span><br>'+a1+' - '+a2+'</h4></div>'+((p.note)?'<p>'+p.note+'</p>':'')
				popup.setLatLng(e.latlng).setContent(str).openOn(map);
			}else{
				objects.Select(this);
			}
			return false
		})
		e.layer.on('mouseover', function(e){
			var p = p = this.feature.properties,
			n1 = objects.getObjectByID(p.dev_node1), n2 = objects.getObjectByID(p.dev_node2),
			a1 = (n1)? n1.feature.properties.address:"???", a2 = (n2)? n2.feature.properties.address:"???";
			info.set({type:'traceline', ports:p.dev_numports, length:p.length, begin:a1, end:a2})
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
	})
	// Удаление кабеля
	Links.on('layerremove', function(e){
		var el;
		if(el = M.getTreeNodeByType(e.layer.feature.properties)) el.remove();
	})
	Links.on('onchange', function(e){
		if('dev_subtype' in e.new) {
			var ls = (linkstyle[e.new.dev_subtype])? linkstyle[e.new.dev_subtype] : defaultLinkStyle;
			e.layer.setStyle(ls)
		}
	})

	// Добавление клиента PON
	cl_pon.on('layeradd', function(e){
		var l = e.layer, p = l.feature.properties;
		clstyle(e);
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(l)
			return false
		})
		e.layer.on('mouseover', function(e){
			var state = '<span style="float:right">PON'+((p.state>3)? ': Уровень сигнала: -'+p.state+'Дб':'')+'</span>';
			info.set({usrchart: 'charts/traffic.php?user='+p.name, user: p.address+state})
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
	})
	// Удаление клиента PON
	cl_pon.on('layerremove', function(e){
	})

	// Изменение клиента PON
	cl_pon.on('onchange',function(e){
		var p = e.layer.feature.properties;
		if('hostname' in e.new || 'service' in e.new) UpdateState(p.id)
		if('state' in e.new || 'subtype' in e.new) clstyle(e)
	})

	// Добавление клиента FTTH
	cl_ftth.on('layeradd', function(e){
		var l = e.layer, p = l.feature.properties;
		clstyle(e);
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(l)
			return false
		})
		e.layer.on('mouseover', function(e){
			var state = '<span style="float:right">FTTH</span>';
			info.set({usrchart: 'charts/traffic.php?user='+p.name, user: p.address+state})
		})
		e.layer.on('mouseout', function(e){
			info.set()
		})
	})
	// Удаление клиента FTTH
	cl_ftth.on('layerremove', function(e){
	})

	// Изменение клиента FTTH
	cl_ftth.on('onchange',function(e){
		if('hostname' in e.new || 'service' in e.new) UpdateState(p.id)
		if('state' in e.new || 'subtype' in e.new) clstyle(e)
	})

	// Добавление клиента Wi-Fi
	cl_wifi.on('layeradd', function(e){
		var l = e.layer, p = l.feature.properties;
		clstyle(e);
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(l)
			return false
		})
		e.layer.on('mouseover', function(e){
			var node = Nodes.getObjectByID(p.connect), a='';
			if(node){
				a = node.feature.properties.address+' &emsp;';
				line = L.polyline([l.getLatLng(),node.getLatLng()],linkWifiStyle);
				if(tracewifi) map.removeLayer(tracewifi)
				tracewifi = L.layerGroup([line]).addTo(map);
			}
			var state = '<span style="float:right">'+a+'Wi-Fi</span>';
			info.set({usrchart: 'charts/traffic.php?user='+p.name, user: p.address+state})
			return false
		})
		e.layer.on('mouseout', function(e){
			info.set()
			if(tracewifi){
				map.removeLayer(tracewifi);
				tracewifi = false;
			}
		})
	})
	// Удаление клиента Wi-Fi
	cl_wifi.on('layerremove', function(e){
	})

	// Изменение клиента Wi-Fi
	cl_wifi.on('onchange',function(e){
		if('hostname' in e.new || 'service' in e.new) UpdateState(p.id)
		if('state' in e.new || 'subtype' in e.new || 'connect' in e.new) clstyle(e)
	})

	// Добавление запаса
	Reserves.on('layeradd', function(e){
		var l = e.layer, p = l.feature.properties;
		e.layer.on('click', function(e){
			if(drawnItems._drawenabled) return true;
			objects.Select(l)
			return false
		})
	})

	// Добавление заявки
	Claims.on('layeradd', function(e){
		var l = e.layer, p = l.feature.properties;
		l.bindPopup(popup_description(l))
	})

	// подключение тайлового слоя и оверлеев
	if(M && typeof M.conf === 'object') {
		if(typeof mconf !== 'object') mconf = {};
		if(!(mconf.baseLayer && baseLayers[mconf.baseLayer])){
			if(autonom_name) mconf.baseLayer = autonom_name;
			else mconf.baseLayer = 'Yandex';
		}
		map.addLayer(baseLayers[mconf.baseLayer]);
		if(!('overlays' in mconf) || typeof mconf.overlays !== 'object'){
			mconf.overlays = {};
			for(var n in overlays) if(n!='Заявки') mconf.overlays[n] = 1;
			M.conf.save('map',mconf);
		}
		for(var i in mconf.overlays)
			if(mconf.overlays[i] == 1){
				if(i in overlays)
					map.addLayer(overlays[i]);
				else
					delete(mconf.overlays[i])
			}
		M.conf.save('map',mconf);
	}

	var info = new L.Control.List(
		[
			{name:'местоположение района',link:'go=rayons&do=setview'},
			{name:'список серверов',link:'go=devices&do=listservers'},
			{name:'список свичей',link:'go=devices&do=listswitches'},
			{name:'список базовых станций Wi-Fi',link:'go=devices&do=listwifi'},
			{name:'список шаблонов',link:'go=devices&do=getcolorscheme'}
		], 
		function(obj){
			$.popupForm({
				data: obj.link + '&latitude=' + map.getCenter().lat + '&longitude=' + map.getCenter().lng + '&zoom=' + map.getZoom(),
				onsubmit: function(d) {
					if('rayons' in d) {
						var i, el;
						rayons.reload(d.rayons)
						for(i in d.rayons){
							if(el = M.getTreeNodeByType('rayon',d.rayons[i].id)){
								el.options.data = d.rayons[i];
							}
						}
					}
				},
				loader:ldr
			})
		},
		{position: 'bottomleft', listName: 'menu'}
	)

	info.set = function (d) {
		if(d) {
			if(d.percent) this.update('<b>ЗАГРУЗКА '+d.percent+' %</b>');
			if(d.mrtg&&d.mrtg!='') {
				var iphost = d.mrtg.replace(/^.*\//,'').replace(/-day.*/,'')
				this.update('<p class="hinfo">Траффик по '+d.address+
				'<span style="float:right">'+iphost+'</span></p><img src="'+d.mrtg+'">')
			}
			if(d.usrchart) {
				this.update('<p class="hinfo">'+((d.type)?'Сигнал '+d.type+' ':'Траффик по ')+((d.user)?d.user:'')+
				'</p><img src="'+d.usrchart+'">')
			}
			if(d.type=='node') {
				this.update('<p class="hinfo">Узел: '+d.address+'</p>')
			}
			if(d.type=='traceline') {
				var l = '';
				if(d.ports) l = l + 'Кабель '+d.ports+'ж<br>';
				if(d.begin) l = l + 'Начало: '+d.begin+'<br>';
				if(d.end) l = l + 'Конец: '+d.end+'<br>';
				this.update('<p class="hinfo">'+l+'Длина: '+d.length+'м</p>')
			}
			if(d.type=='wifilink') {
				this.update('<p class="hinfo">'+d.address+'<br>Расстояние: '+d.length+' км</p>')
			}
		}else{
			this.update()
		}
	};

	info.addTo(map);


	$('#objects').on('mouseover','li[user]',function(e) { //	Показывает диаграмму по пользователю
		info.set({usrchart: 'charts/traffic.php?user='+$(this).attr('user'), user: $(this).attr('user')})
		return false
	})

	$('#objects').on('mouseout','li[user]',function(e) { //	Скрывает диаграмму по пользователю
		info.set()
		return false
	})

	$('#map').on('click','.linkform',function(e) { //	Выполняет действие для ссылки на форму
		var val = $(this).attr('add');
		if(val) $.popupForm({data: val, loader: ldr, onsubmit:function(d){}})
		return false;
	})

	$('#map').on('click','.button',function(e) { //	Выполняет действие для нажатой кнопки
		var q = $(this).attr('query');
		if(q){
			e.stopPropagation();
			e.preventDefault();
			$.popupForm({
				data: q,
				onsubmit: M.objectProcess,
				loader:ldr
			})
		}
	})

	$('#objects').on('mouseover','li.Device > .lihead',function(e) {
		var tn = $(this).parent().attr('treenode'), el = M.allNodes[tn], d, layer;
		if(el){
			d = el.options.data;
			if(d.type == 'wifi'){
				var lines=[];
				if(layer = Nodes.getObjectByID(d.node1)){
					var c = layer.getLatLng(), xy=[];
					cl_wifi.eachLayer(function(l){
						var p = l.feature.properties;
						if(p.ap == d.id) xy.push(l.getLatLng());
					})
					if(xy.length>0) for(var i in xy){
						lines.push(L.polyline([c,xy[i]],linkWifiStyle));
					}
					if(lines.length>0){
						if(tracewifi) map.removeLayer(tracewifi)
						tracewifi = L.layerGroup(lines).addTo(map);
					}
				}
			}else if(d.type == 'cable'){
				layer = objects.getObjectByID(d.object);
				if(layer && !objects.isSelected(layer)){
					var s = linkHoverStyle;
					s.dashArray = "5,8"; s.color = layer.options.color;
					if(linkHoverInerval) clearInterval(linkHoverInerval);
					linkHoverInerval = setInterval(function(){
						if(s.dashOffset>0) s.dashOffset--; else s.dashOffset = 12;
						layer.setStyle(s);
					},25);
				}
			}else if(d.type == 'onu'){
				var l = objects.getObjectByID(d.node1);
				info.set({type:d.type,user:l.feature.properties.address, usrchart:onuSignalURL+d.macaddress})
			}
		}
		return false
	})

	$('#objects').on('mouseout','li.Device > .lihead',function(e) { //	Выключает показ Wi-Fi
		var tn = $(this).parent().attr('treenode'), el = M.allNodes[tn], d;
		if(el) {
			d = el.options.data;
			if(d.type == 'wifi'){
				if(tracewifi){
					map.removeLayer(tracewifi);
					tracewifi = false;
				}
			}else if(d.type == 'cable') {
				if(linkHoverInerval) {
					clearInterval(linkHoverInerval);
					linkHoverInerval = false;
				}
				layer = objects.getObjectByID(d.object);
				if(layer && !objects.isSelected(layer)){
					var st = layer.feature.properties.dev_subtype,
					ls = (linkstyle[st])? linkstyle[st] : defaultLinkStyle;
					layer.setStyle(ls)
				}
			}else if(d.type == 'onu'){
				info.set();
			}
		}
		return false
	})

	$('#objects').on('mouseenter','li.TracePort > .lihead',function(e) { //	Показывает соединения Wi-Fi
		var tn = $(this).parent().attr('treenode'), el = M.allNodes[tn], cable, layer, port, c;
		if(el){
			if(el.options.layer && el.options.layer.feature.properties.cable){
				layer = el.options.layer;
				cable = layer.feature.properties.cable;
				port = layer.feature.properties.port;
				traceline.eachLayer(function(l){
					var p = l.feature.properties;
					if(cable == p.cable && port != p.port && l.options.opacity>0){
						l._treeOpacity = l.options.opacity; l.setStyle({opacity:0});
					}
				})
				if(c = objects.getObjectByID(cable)) c.setStyle({opacity:0})
				var s = linkHoverStyle;
				s.dashArray = "5,8"; s.color = fadingColor[layer.feature.properties.style];
				if(linkHoverInerval) clearInterval(linkHoverInerval);
				linkHoverInerval = setInterval(function(){
					if(s.dashOffset>0) s.dashOffset--; else s.dashOffset = 12;
					layer.setStyle(s);
				},25);
			}
		}
		return false
	})

	$('#objects').on('mouseleave','li.TracePort > .lihead',function(e) { //	Выключает показ Wi-Fi
		var tn = $(this).parent().attr('treenode'), el = M.allNodes[tn], cable, layer, port, c;
		if(el) {
			if(el.options.layer && el.options.layer.feature.properties.cable){
				layer = el.options.layer;
				cable = layer.feature.properties.cable;
				port = layer.feature.properties.port;
				traceline.eachLayer(function(l){
					var p = l.feature.properties;
					if(cable == p.cable && port != p.port && l._treeOpacity){
						l.setStyle({opacity:l._treeOpacity}); delete l._treeOpacity;
					}
				})
				if(c = objects.getObjectByID(cable)) c.setStyle({opacity:0.8})
				if(linkHoverInerval) {
					clearInterval(linkHoverInerval);
					linkHoverInerval = false;
				}
				var p = layer.feature.properties;
				layer.setStyle({color:fadingColor[p.style],opacity:0.8,fillColor:'#000',fillOpacity:0.0,weight:5,dashArray:'0',dashOffset:0})
			}
		}
		return false
	})

	function getObjects(id) {	// Функуия возвращает список объектов для проверки состояния
		var r = {},
		muster = function(l){
			var p = l.feature.properties
			if(p.hostname!='' && p.service!=''){
				if(!r[p.hostname]) r[p.hostname] = {}
				if(!r[p.hostname][p.service]) r[p.hostname][p.service] = []
				if(r[p.hostname][p.service][0]) r[p.hostname][p.service].push(p.id);
				else r[p.hostname][p.service] = [p.id];
			}
		}
		if(id && id>0) {
			muster(objects.getObjectByID(id))
		}else{
			Homes.eachLayer(muster)
			cl_ftth.eachLayer(muster)
			cl_pon.eachLayer(muster)
		}
		if(Object.keys(r).length>0) return r
		return false
	}

	var MapUpdateInterval = null;
	var UpdateState = function(id) { // Функуия обновления состояния по интервалу времени 
		if(nagiosURL=='') return false;
		var out = getObjects(id)
		if (out) {
			ldr.get({
				url: nagiosURL,
				headers: {'X-Requested-With':'XMLHttpRequest'},
				data: 'do=get_state&jsonData=' + JSON.stringify(out),
				onLoaded: function(d) {
					for(var k in d){
						objects.setProperties(objects.getObjectByID(k),{state: d[k]})
					}
				},
				onError: function(d) {
					clearInterval(MapUpdateInterval)
				}
			})
		}
	}
	function UpdateHomeState(){
		if(nagiosURL) MapUpdateInterval = setInterval(UpdateState,20000);
	}

//	плавно добавляет на карту объекты при загрузке
	var chank=100, totalData=0;
	var initChank = function(d){
		var ch = {features:[], type:'FeatureCollection'}, to, sel, o, c, m;
		for(var i=0; i<chank; i++) {
			if(d.features.length == 0) break;
			ch.features.push(d.features.shift())
		}
		geoJson2map(ch);
		if(d.features.length>0){
			to = setTimeout(function(){
				info.set({percent:Math.round((1-d.features.length/totalData)*100)})
				initChank(d)
			},40);
		}else{
			UpdateState();
			talkIcinga('getstate');
			arrangeLayers();
			info.set();
			if(sel = $.paramFromURL('select')){
				o = objects.getObjectByID(sel);
				if(!map.hasLayer(o)) map.addLayer(o);
				c = ('getBounds' in o)? o.getBounds().getCenter() : o.getLatLng();
				objects.Select(o);
				map.setView(c,16);
				M.storage.del('mapSearch')
			}
		}
	}

	ldr.get({ //	Получает данные по гео объектам и отображает их на карте
		data: "go=homes&do=get",
		onLoaded:function(d) {
			var mconf, i;
			if(d.nagiosURL) nagiosURL = d.nagiosURL;
			if(d.onuSignalURL) onuSignalURL = d.onuSignalURL;
			if(d.devtypes) devtypes = d.devtypes;
			if(d.tfade) tfade = d.tfade;
			if(d.rayons) {
				if(rayons) rayons = false;
				rayons = new L.Control.List(d.rayons, function(obj){
					if(obj.latitude && obj.longitude &&obj.zoom)
						rayonView(obj.latitude,obj.longitude,obj.zoom)
				},{listName:'rayon'})
				map.addControl(rayons);
				if(M && typeof M.conf === 'object') mconf = M.conf.get('map'); else mconf={}
				if(typeof mconf.place === 'object') {
					rayonView(mconf.place.lat,mconf.place.lng,mconf.place.zoom)
				}else if(default_position){
					var pos = default_position.split(/,/);
					rayonView(pos[0],pos[1],pos[2]);
				}else{
					if(d.rayons[0] && typeof d.rayons[0] == 'object')
						rayonView(d.rayons[0].latitude,d.rayons[0].longitude,d.rayons[0].zoom)
				}
			}
			if(d.cablecolors){
				for(var n in d.cablecolors){
					var ls = clone(defaultLinkStyle);
					ls.color = d.cablecolors[n];
					linkstyle[n] = ls;
				}
			}
			if(d.GeoJSON && d.GeoJSON.features){
				totalData = d.GeoJSON.features.length;
				initChank(d.GeoJSON);
			}
			UpdateHomeState();
		}
	})

	function makeConnectNodes(layer){ // определяет начальный и конечный узлы для кабеля
		if(layer.feature.properties.type == 'cable') {
			var line = layer.getLatLngs(),
				begin = line[0], 
				end = line[line.length-1],
				min_begin = {node:null,s:50000000},
				min_end = {node:null,s:50000000},
				node1=null, node2=null;
			function minmax(l){
				var distance1 = begin.distanceTo(l.getLatLng()),
					distance2 = end.distanceTo(l.getLatLng())
				if(distance1<min_begin.s){
					min_begin.node = l.feature.properties.id
					min_begin.s = distance1
				}
				if(distance2<min_end.s){
					min_end.node = l.feature.properties.id
					min_end.s = distance2
				}
			}
			Nodes.eachLayer(function(l){
				minmax(l)
			})
			cl_ftth.eachLayer(function(l){
				minmax(l)
			})
			cl_pon.eachLayer(function(l){
				minmax(l)
			})
			if(min_begin.s<15) layer.feature.properties.dev_node1 = node1 = min_begin.node;
			else layer.feature.properties.dev_node1 = null;
			if(min_end.s<15) layer.feature.properties.dev_node2 = node2 = min_end.node
			else layer.feature.properties.dev_node2 = null;
			if(node1>0) line[0] = M.clone(objects.getObjectByID(node1).getLatLng())
			if(node2>0) line[line.length-1] = M.clone(objects.getObjectByID(node2).getLatLng())
			layer.setLatLngs(line)
		}
	}

	M.makeTraceLine = function(geodata){
		if(traceline) map.removeLayer(traceline)
		traceline = geoJson2map(geodata,function(f,l){
			var p = f.properties, tp;
			if(p.type == 'traceline'){
				l.setStyle({color:fadingColor[p.style],opacity:0,fillColor:'#000',fillOpacity:0.0,weight:5})
				tp = M.getTreeNodeByType('traceport',p.port);
				if(tp) tp.options.layer = l;
			}
			if(p.type == 'divicon'){
				tp = M.getTreeNodeByType('traceport',p.port);
				if(tp) tp.options.layer = l;
			}
		})
		traceline.eachLayer(function(l){
			if(l.feature.properties.type == 'divicon' ){
				traceline.removeLayer(l);
				divicons.addLayer(l);
			}
		})
		traceline.addTo(map)
		arrangeLayers()
		/* if(!map.getBounds().contains(traceline.getBounds()))
				map.fitBounds(traceline.getBounds()) */
		traceline.on('mouseover', function (e) {
			info.set(e.layer.feature.properties)
		})
		traceline.on('mouseout', function (e) {
			info.set()
		})
	}

	M.removeTraceLine = function(geodata){
		if(traceline){
			traceline.eachLayer(function(l){
				var p = l.feature.properties, tp = M.getTreeNodeByType('traceport',p.port);
				if(tp) delete tp.options.layer;
			})
			map.removeLayer(traceline)
			traceline = false;
		}
		if(divicons){
			divicons.eachLayer(function(l){
				var p = l.feature.properties, tp = M.getTreeNodeByType('traceport',p.port);
				if(tp) delete tp.options.layer;
				map.removeLayer(l);
				divicons.removeLayer(l);
			})
		}
	}

	M.objectProcess = function(d,elem){
		var id, i, k, type, el;
		if(d.wstype && d.wstype == 'getlinks') talkIcinga('setlinks');
		if(d.state) for(k in d.state){
			objects.setProperties(objects.getObjectByID(k),{state: d.state[k]})
		}
		if(d.modify && 'GeoJSON' in d.modify) for(i in d.modify.GeoJSON){
			geoJson2map(d.modify.GeoJSON[i],function(f,l){
				var layer = objects.getObjectByID(f.properties.id)
				if(layer) objects.removeLayer(layer)
				addFeature(f,l)
				arrangeLayers()
			})
		}
		if(d.feature && (id = d.feature.properties.id)) {
			if(window.objects){
				if(!objects.getObjectByID(id)){ 
					if(!d.append) d.append = {};
					if(!d.append.GeoJSON) d.append.GeoJSON = [d.feature];
					else d.append.GeoJSON.push(d.feature);
				}else
					objects.setProperties(objects.getObjectByID(id),d.feature.properties)
			}
		}
		if(d.delete && d.delete.objects) for(i in d.delete.objects){
			if(window.objects){
				var id = d.delete.objects[i],
				l = objects.getObjectByID(id),
				p = (l)? l.feature.properties : {},
				el = M.getTreeNodeByType(p);
				if(el) el.remove();
				if(l) objects.removeLayer(l);
			}
		}
		if(d.delete && d.delete.wifilinks) for(i in d.delete.wifilinks){
			Gtypes.wifilink.eachLayer(function(l){
				if(d.delete.wifilinks[i] == l.feature.properties.id){
					Gtypes.wifilink.removeLayer(l);
				}
			})
		}
		if(d.remove) for(k in d.remove) {
			if(typeof k == 'string') type = k.replace(/s\s*$/,''); else type = false;
			for(i in d.remove[k]){
				id = (typeof d.remove[k][i] == 'object')? d.remove[k][i].id : d.remove[k][i];
				if(el = M.getTreeNodeByType(type,id)) el.remove();
			}
		}
		if(d.modify) for(k in d.modify) {
			if(typeof k == 'string') type = k.replace(/s\s*$/,''); else type = false;
			for(i in d.modify[k]){
				el = M.getTreeNodeByType(type,d.modify[k][i].id);
				if(el) el.update(d.modify[k][i]);
			}
		}
		if(d.append) for(var k in d.append) {
			if(k == 'GeoJSON') for(var i in d.append.GeoJSON){
				if(window.objects){
					geoJson2map(d.append.GeoJSON[i],function(f,l){ addFeature(f,l) })
					arrangeLayers()
				}
			}else if(elem instanceof M.TreeNode) for(var i in d.append[k]){
				elem.useLoadedElem(d.append[k][i]);
			}
		}
	}

	L.drawLocal.draw.toolbar={
		actions: {
			title: 'Отменить нарисованное',
			text: 'Отмена'
		},
		buttons: {
			polyline: 'Проложить кабель',
			polygon: 'Очертить дом',
			reserv: 'Оставить запас',
			circle: 'Создать узел',
			marker: 'Разместить клиента',
		}
	}
	L.drawLocal.edit.toolbar={
			actions: {
				save: {
					title: 'Сохранить изменения.',
					text: 'Сохранить'
				},
				cancel: {
					title: 'Отменить редактирование, забыть все изменения.',
					text: 'Отмена'
				}
			},
			buttons: {
				edit: 'Изменить объекты',
				editDisabled: 'Нет объектов для редактирования.',
				remove: 'Удалить объекты.',
				removeDisabled: 'Нет объектов для удаления.'
			}
		}

	var drawControl = new L.Control.Draw({
		draw: {
			polygon: {
				shapeOptions: {
					color: '#666666'
				}
			}
		},
		edit: {
			featureGroup: drawnItems
		}
	});
	map.addControl(drawControl);

	map.on('draw:editstart', function(e){
		drawnItems._drawenabled = true
	})

	map.on('draw:editstop', function(e){
		drawnItems._drawenabled = false
	})

	map.on('draw:created', function(e){
		var type = e.layerType,
			layer = e.layer,
			go;

		if (type === 'polygon') {
			layer.options = $.extend(layer.options,homeStyle(3))
			layer.feature={
				type:"Feature",
				properties:{
					type:'home',
					name:'object_'+objects.dbid.length,
					center:layer.getBounds().getCenter()
				}
			}
			go = 'homes'
		}

		if (type === 'polyline') {
			layer.feature={
				type:"Feature",
				properties:{
					type:'cable',
					name:'cable_'+objects.dbid.length,
					center:layer.getBounds().getCenter()
				}
			}
			makeConnectNodes(layer)
			go = 'devices'
		}

		if (type === 'circle') {
			layer.setRadius(10)
			layer.feature={
				type:"Feature",
				properties:{
					type:'node',
					name:'node_'+objects.dbid.length,
					center:layer.getBounds().getCenter()
				}
			}
			go = 'nodes'
		}

		if (type === 'marker') {
			layer.feature={
				type:"Feature",
				properties:{
					type:'client',
					name:'client_'+objects.dbid.length
				}
			}
			go = 'clients'
		}

		if (type === 'reserv') {
			layer.feature={
				type:"Feature",
				properties:{
					type:'reserv',
					name:'reserv_'+objects.dbid.length
				}
			}
			go = 'reserv'
		}

		$.popupForm({
			data: "go="+go+"&do=new&home="+objects.dbid.length+
				"&GeoJSON="+JSON.stringify(layer.toGeoJSON()),
			onerror: function(d){
				var p = layer.feature.properties;
				if(layer) drawnItems.removeLayer(layer);
				console.log('layer '+p.id+' '+p.type+((p.subtype)?' ('+p.subtype+')':'')+' removed by error');
			},
			ongetform: function(d) {	
				try {
					if('object' in d.form.fields) layer.feature.properties.id = d.form.fields.object.value;
					else layer.feature.properties.id = d.form.fields.id.value;
					var p = layer.feature.properties;
					console.log('get form id='+p.id)
				}catch(e) {
					$.popupForm({type:'error',data:'Сервер не вернул id!'})
				}
			},
			oncancel: function(d) {
				if(layer && layer.feature.properties.id)
				ldr.get({
					data:"go="+go+"&do=delete&ids="+layer.feature.properties.id,
					onLoaded:function(d) { 
						drawnItems.removeLayer(layer) 
					}
				})
			},
			onsubmit: function(d) {
				var k, i, ul, obj = {GeoJSON:geoJson2map}
				if(d.feature) {
					layer.feature = d.feature
					addFeature(layer.feature,layer)
				}
				if(d.append) {
					for(var k in d.append) {
						for(var i in d.append[k]){
							if($.isFunction(obj[k])) {
								obj[k](d.append[k][i],function(f,l){
									var p = f.properties;
									addFeature(f,l)
									if(p.type == 'home' || p.type == 'client') UpdateState(p.id)
								})
							}
						}
					}
				}
				arrangeLayers();
			},
			loader: ldr
		})

		// Do whatever else you need to. (save to db, add to map etc)
	});

	map.on('draw:edited', function(e) {
		if(e.layers) {
			e.layers.eachLayer(function(layer){
				var pn = layer.feature.properties
				// 	при перемещении узла перемещаем конечные или начальные точки кабелей на этом узле
				if(pn && (pn.type=='node' || pn.type=='client')) {
					Links.eachLayer(function(l){
						var p = l.feature.properties, line;
						if(p && p.type=='cable'){
							if(p.dev_node1==pn.id){
								line = l.getLatLngs()
								line[0] = layer.getLatLng()
								l.setLatLngs(line)
								e.layers.addLayer(l)
							}
							if(p.dev_node2==pn.id){
								line = l.getLatLngs()
								line[line.length-1] = layer.getLatLng()
								l.setLatLngs(line)
								e.layers.addLayer(l)
							}
						}
					})
				}
				if(pn && pn.type=='cable') {
					makeConnectNodes(layer)
				}
			})
 			var tmp = e.layers.toGeoJSON()
			ldr.get({
				data: "go=homes&do=modify&GeoJSON="+JSON.stringify(tmp),
				onLoaded: M.objectProcess
			})
		}
	});

	map.on('draw:deleted', function(e) {
		var ids = []
		if(e.layers) {
			e.layers.eachLayer(function(l) {
				ids.push(l.feature.properties.id)
			})
		}
		if(ids.length>0)
		ldr.get({
			data:"go=homes&do=delete&ids="+ids.join(","),
			onLoaded:function(d) {
				if(d.modify && 'GeoJSON' in d.modify) for(i in d.modify.GeoJSON){
					geoJson2map(d.modify.GeoJSON[i],function(f,l){
						var layer = objects.getObjectByID(f.properties.id)
						if(layer) objects.removeLayer(layer)
						addFeature(f,l)
						arrangeLayers()
					})
				}
				if(d.delete) {
					for(var k in d.delete) {
						for(var i in d.delete[k])
							if(k=='objects') {
								var l = objects.getObjectByID(d.delete[k][i]);
								objects.removeLayer(l)
							}
					}
				}
			},
			onError:function(d) {
				$.popupForm({
					type:'error',
					data:"Ошибка:\n<p style=\"text-align:left\">\n\n"+d+"</p>",
					oncancel:function() {e.layers.eachLayer(function(l) {objects.addLayer(l)})}
				})
			}
		})
	});

	map.on('moveend', function(e){
		if(M && typeof M.conf === 'object') {
			M.conf.save('map',{place:{
				lat: map.getCenter().lat,
				lng: map.getCenter().lng,
				zoom: map.getZoom()
			}})
		}
	})

	map.on('baselayerchange', function(e){
		if(e.name in baseLayers) {
			mconf = M.conf.get('map');
			if(typeof mconf !== 'object') mconf = {};
			mconf.baseLayer = e.name;
			M.conf.save('map',mconf)
		}
	})

	map.on('overlayadd', function(e){
		if(e.name in overlays) {
			if(e.name == 'Заявки') {
				loadClaims();
			}else{
				arrangeLayers()
				mconf = M.conf.get('map');
				if(typeof mconf !== 'object') mconf = {};
				if(typeof mconf.overlays !== 'object') mconf.overlays = {};
				mconf.overlays[e.name] = 1;
				M.conf.save('map',mconf)
			}
		}
	})

	map.on('overlayremove', function(e){
		var mconf;
		if(e.name in overlays) {
			arrangeLayers()
			mconf = M.conf.get('map');
			if(typeof mconf !== 'object') mconf = {};
			if(typeof mconf.overlays !== 'object') mconf.overlays = {};
			mconf.overlays[e.name] = 0;
			M.conf.save('map',mconf)
		}
	})

	map.addControl(control_layers = new L.Control.Layers(baseLayers,overlays));
	drawnItems.addTo(map);

	rayonView = function(lat,lng,zoom) {
		if(map) {
			map.setView([lat,lng],zoom)
		}
	}
	
	talkIcinga = function(cmd,data){
		var m = {wstype:cmd,to:"icinga"}, mb;
		try{ mb = M.msgr.getMembers('fio'); }catch(e){ mb={} }
		if(!mb.icinga) return false;
		function setLinks(){ Homes.eachLayer(function(l){
			var id, p = l.feature.properties, f;
			if(p.service && (f = p.service.match(/^id([0-9]+)$/))) id = f[1];
			if(id && id != p.id){ if(m.links === undefined) m.links = {}; m.links[p.id] = id; }
		})}
		if(cmd=='getstate' && data) m.objects = data;
		if(cmd=='getstate' || cmd=='setlinks' && !data) setLinks();
		if((cmd=='setlinks' || cmd=='unsetlinks') && data) m.links = data;
		if(M.sock) M.sock.send(m);
		else M.storage.set('mkSendMessage',m);
	}
})
