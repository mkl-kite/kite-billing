$(document).ready(function() {
	var ldr = $.loader();
	$(window).resize(function() {
		var td = $('#content').parents('td')[0];
		$('#content').innerHeight(0);
		$('#content').outerHeight($(td).height());
	})
	$(window).trigger('resize');
	
	$('.inner').on('click','p.menu',function(e){
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
