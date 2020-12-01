(function () {
	'use strict';

	var document = typeof window !== 'undefined' && typeof window.document !== 'undefined' ? window.document : {};
	var isCommonjs = typeof module !== 'undefined' && module.exports;
	var keyboardAllowed = typeof Element !== 'undefined' && 'ALLOW_KEYBOARD_INPUT' in Element;

	var fn = (function () {
		var val;

		var fnMap = [
			[
				'requestFullscreen',
				'exitFullscreen',
				'fullscreenElement',
				'fullscreenEnabled',
				'fullscreenchange',
				'fullscreenerror'
			],
			// New WebKit
			[
				'webkitRequestFullscreen',
				'webkitExitFullscreen',
				'webkitFullscreenElement',
				'webkitFullscreenEnabled',
				'webkitfullscreenchange',
				'webkitfullscreenerror'

			],
			// Old WebKit (Safari 5.1)
			[
				'webkitRequestFullScreen',
				'webkitCancelFullScreen',
				'webkitCurrentFullScreenElement',
				'webkitCancelFullScreen',
				'webkitfullscreenchange',
				'webkitfullscreenerror'

			],
			[
				'mozRequestFullScreen',
				'mozCancelFullScreen',
				'mozFullScreenElement',
				'mozFullScreenEnabled',
				'mozfullscreenchange',
				'mozfullscreenerror'
			],
			[
				'msRequestFullscreen',
				'msExitFullscreen',
				'msFullscreenElement',
				'msFullscreenEnabled',
				'MSFullscreenChange',
				'MSFullscreenError'
			]
		];

		var i = 0;
		var l = fnMap.length;
		var ret = {};

		for (; i < l; i++) {
			val = fnMap[i];
			if (val && val[1] in document) {
				for (i = 0; i < val.length; i++) {
					ret[fnMap[0][i]] = val[i];
				}
				return ret;
			}
		}

		return false;
	})();

	var eventNameMap = {
		change: fn.fullscreenchange,
		error: fn.fullscreenerror
	};

	var screenfull = {
		request: function (elem) {
			var request = fn.requestFullscreen;

			elem = elem || document.documentElement;

			// Work around Safari 5.1 bug: reports support for
			// keyboard in fullscreen even though it doesn't.
			// Browser sniffing, since the alternative with
			// setTimeout is even worse.
			if (/ Version\/5\.1(?:\.\d+)? Safari\//.test(navigator.userAgent)) {
				elem[request]();
			} else {
				elem[request](keyboardAllowed && Element.ALLOW_KEYBOARD_INPUT);
			}
		},
		exit: function () {
			document[fn.exitFullscreen]();
		},
		toggle: function (elem) {
			if (this.isFullscreen) {
				this.exit();
			} else {
				this.request(elem);
			}
		},
		onchange: function (callback) {
			this.on('change', callback);
		},
		onerror: function (callback) {
			this.on('error', callback);
		},
		on: function (event, callback) {
			var eventName = eventNameMap[event];
			if (eventName) {
				document.addEventListener(eventName, callback, false);
			}
		},
		off: function (event, callback) {
			var eventName = eventNameMap[event];
			if (eventName) {
				document.removeEventListener(eventName, callback, false);
			}
		},
		raw: fn
	};

	if (!fn) {
		if (isCommonjs) {
			module.exports = false;
		} else {
			window.screenfull = false;
		}

		return;
	}

	Object.defineProperties(screenfull, {
		isFullscreen: {
			get: function () {
				return Boolean(document[fn.fullscreenElement]);
			}
		},
		element: {
			enumerable: true,
			get: function () {
				return document[fn.fullscreenElement];
			}
		},
		enabled: {
			enumerable: true,
			get: function () {
				// Coerce to boolean in case of old WebKit
				return Boolean(document[fn.fullscreenEnabled]);
			}
		}
	});

	if (isCommonjs) {
		module.exports = screenfull;
	} else {
		window.screenfull = screenfull;
	}
})();

(function(window, document, undefined) {

L.Control.Button = L.Control.extend({
	options: {
		position: 'bottomright'
	},
	initialize: function (options) {
		var opt = {fscreen:0,extinit:1}, n;
		this._button = {};
		this.setButton(options);
		for(n in opt) if(n in options){
			this.options[n] = options[n];
			delete options[n];
		}
		if('extinit' in this.options && typeof this.options.extinit === 'function')
			this.options.extinit(this)
	},

	onAdd: function (map) {
		this._map = map;
		var container = L.DomUtil.create('div', 'leaflet-control-button');
	
		this._container = container;
		
		this._update();
		return this._container;
	},

	onRemove: function (map) {
	},

	setButton: function (options) {
		var button = {
			'text': options.text,				//string
			'iconUrl': options.iconUrl,			//string
			'onClick': options.onClick,			//callback function
			'hideText': !!options.hideText,		//forced bool
			'maxWidth': options.maxWidth || 70,	//number
			'doToggle': options.toggle,			//bool
			'toggleStatus': false				//bool
		};

		this._button = button;
		this._update();
	},
	
	getText: function () {
		return this._button.text;
	},
	
	getIconUrl: function () {
		return this._button.iconUrl;
	},
	
	destroy: function () {
		this._button = {};
		this._update();
	},
	
	toggle: function (e) {
		if(typeof e === 'boolean'){
			this._button.toggleStatus = e;
		}
		else{
			this._button.toggleStatus = !this._button.toggleStatus;
		}
		this._update();
	},
	
	_update: function () {
		if (!this._map) {
			return;
		}

		this._container.innerHTML = '';
		this._makeButton(this._button);
	},

	_makeButton: function (button) {
		var newButton = L.DomUtil.create('div', 'leaflet-buttons-control-button', this._container);
		if(button.toggleStatus)
			L.DomUtil.addClass(newButton,'leaflet-buttons-control-toggleon');
				
		var image = L.DomUtil.create('img', 'leaflet-buttons-control-img', newButton);
		image.setAttribute('src',button.iconUrl);
		
		if(button.text !== ''){

			L.DomUtil.create('br','',newButton);	//there must be a better way

			var span = L.DomUtil.create('span', 'leaflet-buttons-control-text', newButton);
			var text = document.createTextNode(button.text);	//is there an L.DomUtil for this?
			span.appendChild(text);
			if(button.hideText)
				L.DomUtil.addClass(span,'leaflet-buttons-control-text-hide');
		}

		L.DomEvent
			.addListener(newButton, 'click', L.DomEvent.stop)
			.addListener(newButton, 'click', button.onClick,this)
			.addListener(newButton, 'click', this._clicked,this);
		L.DomEvent.disableClickPropagation(newButton);
		return newButton;
	},
	
	_clicked: function () {	//'this' refers to button
		if(this._button.doToggle){
			if(this._button.toggleStatus) {	//currently true, remove class
				L.DomUtil.removeClass(this._container.childNodes[0],'leaflet-buttons-control-toggleon');
			}
			else{
				L.DomUtil.addClass(this._container.childNodes[0],'leaflet-buttons-control-toggleon');
			}
			this.toggle();
		}
		return;
	}

})

L.fullScreenBtn = new L.Control.Button({
	text: '',
	iconUrl: 'pic/fsOn.png',
	doToggle: true,
	fscreen: screenfull,
	onClick: function(){
		var container;
		if(!this._map) return;
		if (!this.options.fscreen) return;
		if (!this.options.fscreen.enabled) return;
		this.options.fscreen.toggle(this._map._container);
		this._map.invalidateSize();
	},
	extinit: function(elem){
		elem.options.fscreen.on('change',function(){
			if(elem.options.fscreen.isFullscreen){
				console.log('fullscreen on')
			} else {
				console.log('fullscreen off')
			}
		})
	}
})
})(window, document)
