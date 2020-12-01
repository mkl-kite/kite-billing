/* скрипт для таблицы выходов */

/* функция подготовки ссылок в таблице */
$(document).ready(function() {
	var dayUpdate = function(d) {
		var o = $('#'+d.eid+'d'+d.day);
		o.removeClass('planned worked vacation sickleave');
		if(d.work == 1) o.addClass('planned');
		if(d.work == 2) {
			o.empty()
			if(d.worktime == 8) o.addClass('planned worked');
			else o.addClass('planned').html(d.worktime);
			o.attr('title','рабочее время: '+d.worktime+'ч.\rсверхурочные: '+d.overtime+'ч');
		}
		if(d.work == 3) {
			o.empty().addClass('vacation');
		}
		if(d.work == 4) {
			o.empty().addClass('sickleave');
		}
		if(d.overtime == 0) o.find('.overtime').remove();
		if(d.overtime > 0 && o.find('.overtime').length == 0) o.append($('<div>').addClass("overtime"))
		if(d.note == '') o.find('.daynote').remove();
		if(d.note != '' && o.find('.daynote').length == 0)
			o.append($('<div>').addClass("daynote").attr('title',d.note));
		if(d.wd) $('tr#'+'e'+d.eid).find('.wd').empty().html(d.wd);
		if(d.fd) $('tr#'+'e'+d.eid).find('.fd').empty().html(d.fd);
		if(d.ot) $('tr#'+'e'+d.eid).find('.ot').empty().html(d.ot);
		if(d.vd) $('tr#'+'e'+d.eid).find('.vd').empty().html(d.vd);
	}
	if(typeof ldr !== 'function') ldr = $.loader();
	$('table.workdays').on('contextmenu','td.wday', function(e) {
		e.preventDefault();
	});
	$('table.workdays').on('mouseup','td.wday',function(e) {
		if(e.button == 2){
			e.stopPropagation();
			var id = $(this).attr('id'),
				year = $.paramFromURL('year'),
				month = $.paramFromURL('month');
			$.popupForm({
				data: 'go=workdays&do=edit&id='+id+'&year='+year+'&month='+month,
				obj: this,
				onsubmit: function(d){
					if(d.modify) {
						for(i in d.modify) dayUpdate(d.modify[i])
					}
				},
				loader: ldr
			});
		}
	});
	$('td.wday').bind('click',function(e) {
		e.preventDefault();
		var id = $(this).attr('id');
		if(id) {
			var year = $.paramFromURL('year'),
				month = $.paramFromURL('month');
			ldr.get({
				data: 'go=workdays&do=click&id='+id+'&year='+year+'&month='+month,
				onLoaded: function(d) {
					if(d.modify) {
						for(i in d.modify) dayUpdate(d.modify[i])
					}
				}
			})
		}
		return false;
	});
});
