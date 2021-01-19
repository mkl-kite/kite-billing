$(document).ready(function() {
	var ldr = $.loader(), prevH=5000, prevW=5000;
	var clickMenu = function(e){
		var $do = $(this).attr('do'),
		$go = $(this).attr('go');
		if(!$do || !$go) return;
		ldr.get({
			data: 'go='+$go+'&do='+$do,
			onLoaded: function(d) {
				if(d.result=='OK') {
					$('#content').fadeOut(300,function(){
						$('#content').empty().append(d.content).fadeIn(600)
					})
				}
			}
		})
		$('.container > #menu').hide('slide',{direction:'left'},400);
	}
	var swipeMenu = function(e){
		var el = $('.container > #menu').get(0);
		if(e.detail.dir == 'left'){
			$('.container > #menu').hide('slide',{direction:'left'},400);
		}
// 		if(e.detail.dir == 'right'){
// 			$('.container > #menu').show('slide',{direction:'left'},400);
// 		}
	}
	$(window).resize(function(e){
		var
		h = $(window).height(), w = $(window).width(), wm = 320, maxw = 940, maxh = 615,
		fio = $('#fio').outerWidth(), addr = $('#address').outerWidth(), wf = w-50-fio-addr,
		fs = $('.topinfo').css('font-size').replace(/[^0-9.]/g,''), newfs;
		$('.box1').outerHeight(h); $('.box1').outerWidth(w);
		var upfs = function(s){if(s < 17.6) return s+2}
		var downfs = function(s){if(s > 13.2) return s-2}
		
		if(h < 670){
			$('.outer').outerHeight(h - 55 - 20);
			$('div.inner').outerHeight(h - 55 - 45*2 - 30);
			$('#menu,div.data').outerHeight(h - 55 - 45*2 - 30);
		}else{
			$('.outer').outerHeight(maxh);
			$('div.inner').outerHeight(maxh - 50*2);
			$('#menu,div.data').outerHeight(maxh - 50*2);
		}
		
		if(w > maxw+20){
			$('div.outer').outerWidth(maxw);
			if(fs < 17.6) newfs = 17.6;
			if(prevW < 700){
				$('div#menu').hide().remove().prependTo('.inner').css({left:'15px'}).outerHeight($('.inner').outerHeight()).show();
				$('#menu').on('click','p.menu',clickMenu);
			}
			$('#menu').outerWidth(wm);
			$('div.data').outerWidth(maxw-wm-45).css({left:wm+30});
			$('#togglemenu').hide();
		}else if(w < maxw+20 && w >= 700){
			$('div.outer').outerWidth(w - 20);
			if( wf <= -30 ) newfs = 13.2;
			else if( -30 < wf && wf < 15 ) newfs = downfs(fs);
			else if( 15 <= wf && wf < 45) newfs = upfs(fs);
			else if( wf >= 45 && fs < 17 ) newfs = 17.6;
			if(prevW < 700){
				$('#menu').hide().remove().prependTo('.inner').css({left:'15px'}).outerHeight($('.inner').outerHeight()).show();
				$('#menu').on('click','p.menu',clickMenu);
			}
			$('#menu').outerWidth(wm);
			$('div.data').outerWidth(w-wm-70).css({left:wm+30});
			$('#togglemenu').hide();
		}else{
			if( wf <= -30 ) newfs = 13.2;
			else if( -30 < wf && wf < 15 ) newfs = downfs(fs);
			else if( 15 <= wf && wf < 45) newfs = upfs(fs);
			else if( wf >= 45 && fs < 17 ) newfs = 17.6;
			$('div.outer').outerWidth(w - 20);
			if(prevW >= 700){
				$('#menu').hide().remove().appendTo('.container');
				var el = $('#menu').on('click','p.menu',clickMenu).get(0);
				swipe(el);
				el.addEventListener('swipe',swipeMenu);
			}
			$('div.data').outerWidth(w-50).css('left','15px');
			$('#togglemenu').show();
		}
//		console.log("  wf: "+wf+"  fs: "+fs+"  newfs: "+(newfs?newfs:0));
		if(newfs) $('.topinfo').css('font-size',newfs+'px')
		prevH = h; prevW = w;
	})
	$(window).trigger('resize');
	$('#menu').on('click','p.menu',clickMenu);
	$('#togglemenu').on('click',function(e){
		var m = $('.container > #menu');
		if(!m.length) return false;
		e.preventDefault(); e.stopPropagation();
		if(m.is(':visible')){
			m.hide('slide',{direction:'left'},400);
		}else{
			m.outerHeight($('.container').outerHeight()).css({top:0,left:0,position:'absolute'}).show('slide',{direction:'left'},400);
		}
	})
		
	$('#content').on('click','input.button',function(e){
		var $do = $(this).attr('do'),
			go = $(this).attr('go'),
			vars = {}, arg = '';
		$('#content [name]').each(function(i,o){
			var n = $(this).attr('name'), 
				v = $(this).val();
			vars[n] = v;
		})
		for(var n in vars) arg = arg + '&'+n+'='+encodeURIComponent(vars[n]);
		ldr.get({
			data: 'go='+go+'&do='+$do+arg,
			onLoaded: function(d) {
				var ids = ['phone','deposit','fio','address'];
				if(d.result=='OK') {
					$('#content').fadeOut(300,function(){
						$('#content').empty().append(d.content).fadeIn(600)
					})
					for(var i in ids) {
						if(ids[i] in d) 
							$('#'+ids[i]).empty().html(d[ids[i]]);
					}
				}
			}
		})
	})
	$('#content').hide();
	var flash = 2000, flash1 = 300, glow1, glow2, flashInt1, flashInt2;
	glow1 = function(){
		var l = 50 + Math.round(Math.random() * flash1);
		flashInt1 = setTimeout(function(){
			$('.logo').toggleClass('flash');
			if(flashInt2) clearInterval(flashInt2)
			/* console.log('flashInt1 = '+l) */
			glow2()
		},l)
	}
	glow2 = function(){
		var l = 50 + Math.round(Math.random() * flash);
		flashInt2 = setTimeout(function(){
			$('.logo').toggleClass('flash');
			if(flashInt1) clearInterval(flashInt1)
			/* console.log('flashInt2 = '+l) */
			glow1()
		},l)
	}
	ldr.get({
		data: 'go=menu&do=news',
		onLoaded: function(d) {
			if(d.result=='OK') {
				$('#content').empty().append(d.content).fadeIn(800)
				glow1()
			}
		}
	})
})
