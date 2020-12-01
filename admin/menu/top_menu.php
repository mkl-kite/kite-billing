<?php
include_once("defines.php");
?>
<SCRIPT language="JavaScript">
default_position = "<?php echo $config['map']['default_position']; ?>"
$(document).bind('keydown',function(e){
	if(e.keyCode==118) {
		e.preventDefault()
		$('#searcher').focus()
		return false
	}
});
$(document).ready(function() {
	var 
	loader = $.loader(),
	hidden = false,
	manipulate = function (d){
		if(typeof d !== 'object') return false;
		var mtd = {delete:0,append:0,modify:0}, method, f, i;
		for(method in mtd) {
			f = '_'+method
			if(typeof d.obj === 'object'){
				if(d[method] && $.isFunction(d.obj[f])) for(var i in d[method]) {
					d.obj[f](d[method][i])
				}
			}
		}
	},
	show_searchdata = function(d) {
		if(hidden) { 
			hidden.hide() 
		}else{
			hidden = $('#endmenu').nextAll().hide()
		}
		$('#searchresult').remove()
		var c = $('<div>').attr({id:'searchresult',class:'normal'}).html(d.tab.content)
		$('#endmenu').after(c)
		$('#searchresult').highlight(d.query)
		$('.searchdata').on('click','tr',function(){
			if('objects' in window){
				var
				login = $(this).find('span.login').text(),
				addr = $(this).find('span.address').text(), i, c, o, p;
				addr = $.trim(addr.replace(/\/.*/,'').replace(/^.*(ул|пер|пл|пр|б|п|п|м\-н|р)\./,'$1.'));
				for(i in objects.dbid) {
					p = objects.dbid[i].feature.properties;
					if(p.name == login || p.address == addr){ o = objects.dbid[i]; break; }
				}
				if(!o) for(i in Homes.dbid) {
					if(Homes.dbid[i].feature.properties.address == addr){ o = Homes.dbid[i]; break; }
				}
				$('#searchresult').remove()
				hidden.show();
				$(window).trigger('resize');
				if(!o) $.popupForm({type:'info', data:'Геоданные объекта не найдены!'})
				else{
					c = ('getBounds' in o)? o.getBounds().getCenter() : o.getLatLng();
					map.setView(c,16);
					if(!objects.isSelected(o)) objects.Select(o);
					else objects.deSelect(o).Select(o);
				}
			}else{
				window.open('users.php?go=usrstat&uid='+$(this).attr('id'),'_self');
			}
		})
	}
	$('.linkform').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		if(!ldr) ldr = $.loader();
		var val = $(this).attr('add');
		if(val.match(/\?/))
			window.open(val,'_blank')
		else $.popupForm({
			data: val,
			loader: ldr,
			onsubmit:function(d){
				if(d.tab && d.tab.content){
					show_searchdata(d)
				}else{
					console.log("linkform window reload"+JSON.stringify(d));
					window.open($.paramToURL({}),'_self')
				}
			}
		})
		return false;
	})
	$('.searchdata').on('click','tr',function(){
		var cl = this;
		if(!$('#searchresult')[0]){
			$.popupForm({data:'go=stdform&do=edit&table=users&id='+$(cl).attr('id'),obj:cl,onsubmit:manipulate,loader:loader})
		} else {
			window.open('users.php?go=usrstat&uid='+$(cl).attr('id'),'_self');
		}
	})
	/* добавляем обработку поиска */
	$('#search').bind('click',function(e) {
		e.preventDefault();
		var q = $('#searcher').val()
		loader.get({
			data: "go=search&q="+q,
			onLoaded: show_searchdata,
			onError: function(){},
			showLoading: function(){}
		})
		return false
	})
	$('#searcher').bind('keyup',function(e) {
		if($(this).val()=='') {
			e.preventDefault();
			if(hidden) {
				$('#searchresult').remove()
				hidden.show();
			}
		}
		return false;
	})
	$('#searcher').bind('keydown',function(e) {
		if(e.keyCode==13) { // return
			e.preventDefault();
			$('#search').click();
			return false;
		}
	})
	$('#searcher').focus()

	/* добавляем обработку таблиц */
	$('table[module] tr').css('cursor','pointer');
	$('table[module]').on('click','tr',function(){
		var g = $(this).parents('table').attr('module'),
			id = $(this).attr('id');
		window.open($.paramToURL({'go':g, 'do':'edit', 'id':id}),'_self');
	})
	$('.tablecontainer').prepareTable();

});
</SCRIPT>
<div class="topheader">
<span style="float:left;">
<div class="search-element"><input id="searcher"><div id="search" class="button search"><img src="pic/find.png"></div>
</div>
</span>
<div id="operator"><?php echo shortfio($opdata['fio']); ?></div>
<span><a HREF="index.php" style="text-decoration:none"><H3><?php echo $LOGO; ?></H3></a></span>
</div>
<div style="clear:both;"></div><?php

if(!isset($topmenu)) $topmenu = array(
	'Поиск'			=>array('class'=>'linkform','add'=>"go=stdform&do=edit&table=search"),
	'Новый клиент'	=>array('level'=>3,'HREF'=>"new_user.php"),
	'Наряды'		=>array('level'=>3,'HREF'=>"claims.php"),
	'Статистика'	=>array('level'=>3,'HREF'=>"stat.php"),
	'Касса'			=>array('level'=>3,'HREF'=>"kassa.php"),
	'Журналы'		=>array('level'=>2,'HREF'=>"logs.php"),
	'Карта'			=>array('level'=>2,'HREF'=>"maps.php"),
	'Справочники'	=>array('level'=>2,'HREF'=>"references.php"),
	'Трафик'		=>array('level'=>5,'HREF'=>"traffic.php"),
	'SMS'			=>array('level'=>3,'class'=>'linkform','add'=>"go=stdform&do=new&tname=sms&table=sms&id=new"),
	'Online'		=>array('level'=>3,'HREF'=>"online.php"),
	'Выйти'			=>array('level'=>1,'HREF'=>"index.php?go=logout",'id'=>'logout'),
);
echo make_menu($topmenu);
?>
<div id="endmenu"></div>
