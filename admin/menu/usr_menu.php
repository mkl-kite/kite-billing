<SCRIPT language="JavaScript">
var userkill;

$(document).ready(function() {
	$('a.killusr').on('click', function(e) {
		if(!ldr) ldr = $.loader();
		var a = $(this), u = $(this).attr('uid');
		ldr.get({
			data: 'go=clients&do=userkill&uid='+u,
			onLoaded: function(d) {
				try {
					if(d.result=='OK' && d.delete && d.delete.length>0) {
						$.popupForm({type:'info',data:"пользователь <b>"+u+"</b><br>был сброшен с линии"}); 
					}
					if(d.result=='INFO') {
						$.popupForm({type:'info',data:d.desc}); 
					}
					if(d.result=='ERROR') {
						$.popupForm({type:'error',data:d.desc}); 
					}
				}catch (e) { 
					$.popupForm({type:'error',data:"Не удалось обработать полученные данные!"}); 
				}
			},
			showLoading: function(sw) {
				if(sw) {
					$(a).switchClass('killusr','killing')
				}else{
					$(a).switchClass('killing','killusr')
				}
			}
		})
	})
	$('a.get').on('click', function(u) {
		var o = $(this).attr('add');
		if(o) ldr.get({
			data: o,
			onLoaded: function(d){
				if(d.object) M.storage.set('mapSearch',d.object)
			},
			showLoading: true
		});
	})

	var blinkInterval = false;
//	Включает мигание 
	blinkInterval = setInterval(function(){
		$('div[port].userport').toggleClass('blink')
	},250);
})
</SCRIPT>
<?php

$usrmenu = array(
	'Клиент'	=>array('HREF'=>"users.php?go=usrcard&uid={$client['uid']}",'go'=>'usrcard'),
	'Платеж'	=>array('class'=>'linkform','add'=>"go=stdform&do=new&table=pay&uid={$client['uid']}"),
	'Заявка'	=>array('class'=>'linkform','add'=>"go=claims&do=new&cltype=2&uid={$client['uid']}"),
	'Тех.данные'=>array('HREF'=>"users.php?go=switches&do=userport&uid={$client['uid']}",'go'=>'switches'),
	'Статистика'=>array('HREF'=>"users.php?go=usrstat&uid={$client['uid']}",'go'=>'usrstat'),
	'Платежи'	=>array('level'=>3,'HREF'=>"users.php?go=usrlogpay&uid={$client['uid']}",'go'=>'usrlogpay'),
	'Журнал'	=>array('HREF'=>"users.php?go=usrlog&uid={$client['uid']}",'go'=>'usrlog'),
	'Лог'		=>array('HREF'=>"users.php?go=usrradius&uid={$client['uid']}",'go'=>'usrradius'),
	'Документы'	=>array('HREF'=>"users.php?go=documents&uid={$client['uid']}",'go'=>'documents'),
	'Показать на карте'=>array('class'=>'get','HREF'=>"#",'add'=>"go=clients&do=clientobject&uid={$client['uid']}"),
	'Сбросить'	=>array('class'=>'killusr','HREF'=>"#",'uid'=>$client['uid'],'title'=>"Сбросить пользователя с линии"),
);
echo make_menu($usrmenu,'usrmenu');
if(isset($error)) stop(array('result'=>'ERROR','desc'=>$error));
else echo make_client($client);
?>
