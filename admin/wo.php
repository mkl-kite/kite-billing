<?php
$TOP = "Наряды";
$showmenu="no";
$CSSfile = array('js/jquery-ui/jquery-ui.css','js/jquery-ui/jquery-ui.theme.css');
include("top.php");
?>
<link rel="stylesheet" type="text/css" href="js/forms.css" />
<style type="text/css">
html, body {
	border: 0;
	margin: 0;
	height:100%
}
div#all {
	height:100%;
	box-sizing:border-box;
}
.button {
	cursor: pointer;
}
.worder:first-child {
	margin-top: 0;
}
.worder {
	width: 100%;
	margin-top: 2px;
	height: 50px;
	overflow: hidden;
	background-color: rgba(255,255,255,0.2);
	border: 1px solid rgba(0,0,0,0);
}
.employers {
	width: 250px;
	float: left;
	height: 100%;
}
.employer {
	border-bottom: 1px solid #ccc;
	border-right: 1px solid #ccc;
	border-top: 1px solid #e0e0e0;
	border-left: 1px solid #e0e0e0;
	margin: 0 5px 5px 0;
	font: normal 7pt sans-serif;
	line-height:1.4;
	float: left;
	padding: 2px;
	position: relative;
	border-radius: 10px;
	background: -moz-linear-gradient(top , #eeefff, #e8e8f0);
	cursor: default;
	height:13px;
	min-width:13pt;
	white-space:nowrap;
}
.employer .button {
	position: absolute;
	background: rgba(255,255,255,0.5);
	background: -moz-radial-gradient(center, ellipse cover, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.1) 100%);
	border-radius: 3px;
	top: 1px;
	right: 1px;
}
.employer:hover .button {
	visibility: visible;
}
.employer .add-button {
	right: 2px;
}
.employer .del-button {
	visibility: hidden;
}
.employer .icon {
	position: absolute;
	top: 1px;
	left: 1px;
}
.employer select {
	font: normal 8pt sans-serif;
	line-height:0.8;
	height:13px;
	border: none;
	background: rgba(200,200,200,0.2);
}
.jobs {
	width: 80%;
	float: left;
	height: 100%
	overflow: hide;
}
.jobs {
	width: 2000px;
	margin-top: 5px;
	height: 33px;
	padding: 0;
}
.worder .pane {
	width: 4000px;
	height: 100%;
	padding: 0;
}
.job {
	position: relative;
	font: normal 9pt sans-serif;
	color: #686;
	float: left;
	border: 1px solid rgba(0,0,0,0.1);
	overflow: hidden;
	margin-top: 5px;
	margin-right: 5px;
	height: 25px;
	padding: 3px 5px;
	border-radius: 3px;
	cursor: move;
	background: -moz-linear-gradient(top , #efefef, #b0dcb0);
	white-space: nowrap;
	text-overflow: ellipsis;
	text-shadow: 1px 1px 0 rgba(255, 255, 255, 0.5);
}
.job .label {
	position: absolute;
	right:0;
	bottom:0;
	background: -moz-radial-gradient(center, ellipse cover, rgba(255,255,255,1) 0%, rgba(248,248,248,0.05) 100%);
	padding:2px;
}
.custom {
	color: #669;
	background: -moz-linear-gradient(top , #f7f7ff, #cce);
}
.custom:hover {
	color: #555;
}
.tuning {
	color: #886;
	background: -moz-linear-gradient(top , #e7e7c0, #b3b252);
}
.tuning:hover {
	color: #886;
}
.repair {
	color: #966;
	background: -moz-linear-gradient(top , #f8f0f0, #e2c8c8);
}
.repair:hover {
	color: #966;
}
.mount {
	color: #686;
	background: -moz-linear-gradient(top , #f8f8ef, #b0dcb0);
}
.mount:hover {
	color: #686;
}
.missing {
	color: #666;
	background: -moz-linear-gradient(top , #ddd, #e8e8e8);
	text-shadow: none;
}
.missing:hover {
	color: #555;
	text-shadow: none;
}
.done {
	color: #777;
	background: -moz-linear-gradient(top , #fafafa, #eee);
	text-shadow: none;
}
.done:hover {
	color: #555;
	text-shadow: none;
}

.job .home, .job .street, .job .content {
	float: left;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
}
.job:hover {
}
.wo_day {
/*	display: table; */
	width: 100%;
	overflow: hidden;
}
.wo_day h3 {
	font: normal 12pt sans-serif;
	text-align: center;
	font-weight: bold;
	color: #fff;
	height: 25pt;
	background: -moz-linear-gradient(top , #dde, #fff);
	text-shadow: 1px 2px 2px rgba(0, 0, 0, 0.7);
}
.wo_window {
	position: relative;
	height: 100%;
}
.wo_main {
	position: relative;
	height: 100%;
	top:0;
	overflow: hidden;
}
.wo_orders {
	position: absolute;
	width:100%;
	top: 0px;
	height: 60%;
	overflow: hidden;
}
.wo_orders:hover {
	overflow-y: scroll;
}
.wo_claims{
	position: absolute;
	width:100%;
	bottom: 0;
	height: 40%;
}
.ui-resizable-e {
	right:0;
	width:10px;
}
.ui-state-default {
	background: rgba(0,0,0,0);
	border: none;
}
.ui-state-hover {
	background: rgba(255,255,128,0.2);
	border: none;
}
#hud {
	position:absolute;
	width:60px;
	top:5px;
	right:5px;
	border: 1px solid #444;
	background-color: #f8f8f8;
	padding: 2px 8px;
	font: normal 9pt sans-serif;
}
#hud em {
	font: normal 9pt sans-serif;
}
</style>

<SCRIPT language="JavaScript">
$(document).ready(function() {
	var wtypes = ['unknown','mount','repair','tuning','custom'],
		wstate = ['plane','perform','missing','done'],
		hud = $('<div id="hud">X: <em id="x"></em><br>Y: <em id="y"></em></div>').appendTo('body'),
		drag = false, cent = [], jobs = false;

	$('body').on('mousemove',function(e) {
		$('#x').empty().text(e.clientX);
		$('#y').empty().text(e.clientY);
	})

	if(typeof(ldr) !== 'object') ldr = $.loader();
	function woWidth(begin,end){
		return 80;
	}
	function leftPosition(begin){
		return 0;
	}
	var resizeJob = function(){
		var w = $(this).width(),
			street = $(this).find('.street'),
			sow = street.outerWidth(),
			sw = street.width(),
			home = $(this).find('.home'),
			how = home.outerWidth() + 1,
			remainingSpace = w - how,
			newWidth = remainingSpace - ( sow - sw ),
			bw = street.attr('beginwidth');
			if(newWidth > bw) newWidth = bw;
		street.width(newWidth);
	}
	function setLayout(s){
// 		if(s>0) separator=s;
// 		$('.wo_orders .jobs').css('width', function(i, v){
// 			var w=$(this).parents('.worder').width();
// 			return (w-separator)+'px'
// 		});
// 		$('.wo_orders .employers').css({width:separator+'px'});
		$('.wo_orders .job').resizable({
			handles: 'e',
			minWidrh: '50',
			maxWidth: '800',
			resize: resizeJob
		});
		$('.wo_orders .employers').resizable({
			handles: 'e',
			minWidrh: '100',
			maxWidth: '400',
			resize: function(){
				var all = $('.wo_orders .employers').not(this).css('width',$(this).width()),
					remainingSpace = $(this).parent().width()-$(this).outerWidth(),
					divTwo = $(this).next(),
					divTwoWidth = remainingSpace - ( divTwo.outerWidth() - divTwo.width() );
				$('.wo_orders .jobs').css('width', divTwoWidth + 'px');
			}
		})

		$(".pane").sortable({
			appendTo: "body",
			helper: 'clone',
			connectWith: ".pane",
			items: "div.job"
		});

		$('.job').disableSelection();
//		действия при вставке задания в наряд

//		удаление из наряда служащего
		$('.wo_orders').on('click','.employer .del-button',function() {
			em = $(this).parents('.employer').animate({width:0},400,function(){$(this).remove()});
		})
//		добавление в наряд служащего
		$('.wo_orders').on('click','.employer .add-button',function() {
			var woid = $(this).parents('.worder').attr('id').replace(/[^0-9]/,''),
				em = $(this).parents('.employer').get(0);
			ldr.get({
				data: 'go=worders&do=get_employers&woid='+woid,
				onLoaded: function(d){
					if('employers' in d) {
						var s = $('<select>').css('width','80px');
						for(var e in d.employers) s.append($('<option>').attr({value:e}).html(d.employers[e]))
						$(em).animate({width:'86px'},400,function(){
							$(em).find('img').remove()
							$(em).append(s);
							s.on('change',function(){
								var v = s.val(),
									txt = d.employers[v];
								s.remove();
								$(em).css({width:'auto'}).html(txt)
									.append('<img class="button del-button" src="pic/remove.png">')
									.before('<span class="employer"><img src="pic/add.png" class="button add-button"></span>')
							})
						})
					}
				}
			})
		})
	}

	var orders={},
	separator=300;
	module='worders',
	makeEmployer = function(o){
		var e = $('<span>').addClass('employer');
		if(o) {
			e.attr(o).text(o.fio)
			e.append($('<img>').attr({src:'pic/remove.png'}).addClass('button del-button'));
		}else{
			e.append($('<img>').attr({src:'pic/add.png'}).addClass('button add-button'));
		}
		return e;
	},
	makeEmployers = function(o){
		var tmp=$('<div>').addClass('employers');
		if(o){
			tmp.append(makeEmployer());
			for(i in o){
				tmp.append(makeEmployer(o[i]));
			}
		}
		return tmp;
	},
	makeJob = function(o){
		var street = o.address.replace(/ [^ ]*$/,''),
			home = o.address.replace(street,'').replace(/^\s+|\s+$/,''),
			l = leftPosition(o.begintime),
			w = woWidth(o.begintime,o.endtime),
			jclass = (o.status<2)? wtypes[o.type] : wstate[o.status],
			j = $('<div>').attr(o).addClass('job').addClass(jclass)
			.append($('<div>').addClass('street').text(street))
			.append($('<div>').addClass('home').append('&nbsp;'+home)).append('<br>')
			.append($('<div>').addClass('content').text(o.content))
			.css({/*left:l,*/width:w})
			if(o.status == 3) j.append('<img class="label" src=pic/bookmark.png>')
		return j
	},
	makeTaskList = function(o){
		var tmp=[], w, l;
		for(i in o) tmp.push(makeJob(o[i]));
		return $('<div>').addClass('pane').append(tmp);
	},
	makeOrder = function(o){
		var w=$('<div>').addClass('worder');
		if(o&&o.woid>0){
			w.attr({
				id:o.woid,
				wo_date:o.date
			})
			.append(makeEmployers(o.employers))
			.append(makeTaskList(o.cids));
		}
		return w
	},
	makeWorkDay = function(o) {
		var day=$('<div>').addClass('wo_day').append($('<h3>').html(i))
		$.each(val, function(j, w){
			day.append(makeOrder(w))
		})
	},
	woLoad = function() {
		var i, req = ['go=worders','do=get'], dat = $.paramFromURL(['begin','end']);
		for(i in dat) req.push(i + '=' + dat[i]);
		req = req.join('&');
		ldr.get({
			data: req, 
			onLoaded: function(data) {
				orders=$('.wo_orders').empty();
				var day;
				if(data&&data.orders){
					$.each(data.orders, function(i, val){
						var day=$('<div>').addClass('wo_day').append($('<h3>').html(i))
						$.each(val, function(j, w){
							day.append(makeOrder(w))
						})
						orders.append(day);
					});
					setLayout();
					$('.job').find('.street').attr('beginwidth',function(){return $(this).width()}).end().each(resizeJob);
				}
			}
		});
	},
	woSave = function(d) {
	}

	woLoad();

})
</SCRIPT>

<DIV class="wo_window">
	<DIV class="wo_main">
		<DIV class="wo_orders"></DIV>
		<DIV class="wo_claims pane"></DIV>
	</DIV>
</DIV>
<?php
include("bottom.php");
?>
