/* функция подготовки ссылок в таблице */
$(document).ready(function() {
	var blinkInterval = false, port = false;
	$('div.switch').on('click','div[port]',function(e) {
		var pon = $(e.target).hasClass('userport'),
			p = $(e.target).attr('port'), pn = $(e.target).attr('pname'), rg;
			if(pn) rg = new RegExp('\\b'+pn+'\\b');
		if(blinkInterval) {
			clearInterval(blinkInterval);
			$('div[port].userport').removeClass('blink');
		}
		if(port && (p != port || pon))
			$('div.switch div[port='+port+']').removeClass('userport');
		if(!pon)
			$(e.target).addClass('userport');
		$('#table_user_tech_data tbody tr').each(function(i,e){
			var td = $(e).find('td').get(1),
				txt = $(td).text();
			if(((!pn && txt != p) || (pn && !txt.match(rg))) && !pon)
				$(e).hide();
			else
				$(e).show();
		})
//		Включает мигание порта
		if($(e.target).hasClass('userport')) {
			blinkInterval = setInterval(function(){
				$('div[port].userport').toggleClass('blink')
			},250);
		}
		port = p;
	});
	var port = $.paramFromURL('port');
	if(port) $('div[port='+port+']').click()
});
