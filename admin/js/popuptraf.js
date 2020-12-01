/* скрипт для всплывающего окна */

/* функция подготовки ссылок в таблице подключенных пользователей */
$(document).ready(function() {
	var traf;
	$('body').on('mouseover','.usrview', function() {
		ShowGraf(this);
	})
	$('body').on('mouseout','.usrview', function() {
		traf.fadeOut().remove();
	});

	function ShowGraf(a) {
		if(typeof traf == 'object') traf.remove()
		var u, usr, i, id, cw, l, w, xy, t, tn, r, calcTop, calcLeft;

		if(usr = $(a).attr('user')) u = 'uid='+usr;
		else if(usr = $(a).attr('uid')) u = 'uid='+usr;
		else if(a.nodeName=='A' || a.nodeName=='SPAN') u = 'user='+a.innerHTML;
		else if((id = $(a).parents('tr').attr('id')) && (tn = $(a).parents('table').attr('tname'))) u = 'id='+id+'&table='+tn;

		if(!u) return false;

		traf = $('<div class="Rounded" style="position:absolute;z-index:10;display:none;width:730px;height:220px;font-size:11px;padding:5px;color:#666;background:#ddf;text-align:center;display:none">').appendTo('body');

		xy = $(a).offset();								// берем координаты верхнего левого угла элемента
		t = document.documentElement.scrollTop;			// берем расстояние прорутки
		calcTop = (xy.top - $(traf).height() - 10);		// Вычисляем коорд. верх. лев. угла диагр.
		if(t > calcTop) {								// Если он вылазит за верх. гран. окна - сдвигаем вниз
			calcTop = (xy.top + a.offsetHeight + 5);
		}
		cw = document.body.clientWidth;					// Ширина видимой области
		l = document.documentElement.scrollLeft;		// берем расстояние прорутки
		w = $(traf).width();
		calcLeft = (xy.left);
		if((calcLeft+w)>(l+cw)) {						// Если он вылазит за прав. гран. окна - сдвигаем влево
			calcLeft = (l + cw - w - 15);
		}
		traf.css('top',calcTop + 'px');				// Позиционирование диагр.
		traf.css('left',calcLeft + 'px');

		r = Math.round(Math.random() * 1000000000);
		i = $('<img src="charts/traffic.php?'+u+'&r='+r+'">').attr('id','userchart').css('margin-top','10px').appendTo(traf);
//		console.log("image src: "+$(i).attr('src'));
		$(traf).fadeIn();
	}
});
