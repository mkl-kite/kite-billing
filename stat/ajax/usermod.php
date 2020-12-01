<?php
include_once("classes.php");
include_once("users.cfg.php");

if(!isset($valute)) $valute = $q->select("SELECT * FROM currency WHERE rate=1.0",1);
if(!$valute) $valute=array('id'=>1,'name'=>'рубль','rate'=>1.00,'short'=>'руб');

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['newphone'] = (isset($_REQUEST['newphone']))? preg_replace('/[^0-9\-+]/','',$_REQUEST['newphone']) : '';
$in['addphone'] = (isset($_REQUEST['addphone']))? preg_replace('/[^0-9\-+]/','',$_REQUEST['addphone']) : '';
$in['newmail'] = (isset($_REQUEST['newmail']))? preg_replace('/[^0-9A-Za-z\-_@\.]/','',$_REQUEST['newmail']) : '';
$in['pw'] = (isset($_REQUEST['pw']))? strong($_REQUEST['pw']) : '';
$in['pw1'] = (isset($_REQUEST['pw1']))? strong($_REQUEST['pw1']) : '';
$in['pw2'] = (isset($_REQUEST['pw2']))? strong($_REQUEST['pw2']) : '';
$in['friend'] = (isset($_REQUEST['friend']))? numeric($_REQUEST['friend']) : 0;
$in['present'] = (isset($_REQUEST['present']))? numeric($_REQUEST['present']) : '';
$in['card'] = (isset($_REQUEST['card']))? preg_replace('/[^0-9\-]/','',$_REQUEST['card']) : '';

if(!isset($client['uid'])) log_txt("usermod: WARNING client not have UID ".arrstr($client));

switch($in['do']){

	case 'newphone':
		$usr = new user($client['uid']);
		if(!$usr->change(array('phone'=>$in['newphone']))) {
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$usr->errors)));
		}
		$out = "Ваш новый номер телефона:<h2>{$usr->data['phone']}</h2>";
		$_SESSION['sess_user']['phone'] = $client['phone'] = $usr->data['phone'];
		stop(array('result'=>'OK','content'=>$out,'phone'=>$usr->data['phone']));
		break;

	case 'addphone':
		$usr = new user($client['uid']);
		$ph = preg_split('/\s+/',preg_replace('/[^0-9\0- ]/','',$in['newphone']));
		if(!$usr->change(array('phone'=>$usr->data['phone']." ".$in['newphone']))) {
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$usr->errors)));
		}
		$out = "Ваши номера телефонов:<h2>{$usr->data['phone']}</h2>";
		$_SESSION['sess_user']['phone'] = $client['phone'] = $usr->data['phone'];
		stop(array('result'=>'OK','content'=>$out,'phone'=>$usr->data['phone']));
		break;

	case 'newmail':
		$usr = new user($client['uid']);
		if(!$usr->change(array('email'=>$in['newmail']))) {
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$usr->errors)));
		}
		$out = "Ваш новый почтовый адрес:<h2>{$usr->data['email']}</h2>";
		$_SESSION['sess_user']['email'] = $client['email'] = $usr->data['email'];
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'changepassword':
		$usr = new user($client['uid']);
		if($in['pw'] != $client['password']) { stop(array('result'=>'ERROR','desc'=>"Неверный старый пароль!")); }
		if($in['pw1'] == '') { stop(array('result'=>'ERROR','desc'=>"Новый пароль пустой!")); }
		if($in['pw1'] != $_REQUEST['pw1']) { stop(array('result'=>'ERROR','desc'=>"Использованы недопустимые символы!")); }
		if($in['pw1'] != $in['pw2']) { stop(array('result'=>'ERROR','desc'=>"Пароли не совпадают!")); }
		if(!$usr->change(array('password'=>$in['pw1']))){
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$usr->errors)));
		}
		$out = "Ваш новый пароль:<h2>{$usr->data['password']}</h2>";
		$_SESSION['sess_user']['password'] = $client['password'] = $usr->data['password'];
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'present':
		$p = new payment($config);
		if($client['contract'] == $in['friend']) stop(array('result'=>'ERROR','desc'=>"Дарить самому себе?<br>Миленько..."));
		if($in['friend']==0) stop(array('result'=>'ERROR','desc'=>"Не указан лицевой счет (кому)!"));
		if(!($fpay = $p->pay(array('contract'=>$client['contract'],'money'=>-1*$in['present'],'povod_id'=>17,'note'=>"для ".$in['friend'])))) {
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
		}
		if(!($cpay = $p->pay(array('contract'=>$in['friend'],'money'=>$in['present'],'povod_id'=>17,'note'=>"от ".$client['contract'])))) {
			$p->remove_pay($fpay);
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
		}
		$out = "<h2>Сумма {$in['present']} {$p->valute['short']}. переведена на :</h2><h2>".shortfio($p->user->data['fio'])."</h2>";
		log_txt("[{$client['user']}] deposit: ".$q->get('users',$client['uid'],'','deposit'));
		$_SESSION['sess_user']['deposit'] = $client['deposit'] = $newdeposit = $q->get('users',$client['uid'],'','deposit');
		stop(array('result'=>'OK','content'=>$out,'deposit'=>sprintf("%.2f {$valute['short']}.",$newdeposit)));
		break;

	case 'pay':
		if($in['card'] == '') stop(array('result'=>'ERROR','desc'=>"Не указан номер карты!"));
		$p = new payment($config);
		if(!($fpay = $p->pay(array('contract'=>$client['contract'],'card'=>$in['card'])))) {
			stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
		}
		$out = "От Вас получен платёж:<BR><BR><H2>{$in['card']}</H2><H2>на сумму: <span class=\"sum\">{$p->payment['summ']}</span> {$p->valute['short']}</H2>";
		if($p->user->active) $out .= "<br><br><p class=\"warning\">В данный момент у Вас ограниченное соединение,".
			"<br>система автоматически прервёт его в течение 2-3 мин.</p><p class=\"msg\">После чего будет возможно установить обычное соединение.</p>";
		$_SESSION['sess_user']['deposit'] = $client['deposit'] = $newdeposit = $q->get('users',$client['uid'],'','deposit');
		stop(array('result'=>'OK','content'=>$out,'deposit'=>sprintf("%.2f {$valute['short']}.",$newdeposit)));
		break;

	case 'newclaim':
		$in['cltype'] = (isset($_REQUEST['cltype']))? numeric($_REQUEST['cltype']) : 0;
		$in['unique_id'] = (isset($_REQUEST['unique_id']))? numeric($_REQUEST['unique_id']) : 0;
		$in['content'] = (isset($_REQUEST['content']))? substr($_REQUEST['content'],0,1000) : '';
		if($in['cltype'] == 0) stop(array('result'=>'ERROR','desc'=>"Не указан тип заявления!"));
		if(mb_strlen($in['content'])<10) stop(array('result'=>'ERROR','desc'=>"Опишите проблему подробнее!"));
		if(CITYCODE == 56 && ($phone = normalize_phone($client['phone'])) && !preg_match('/^(094|071|072)/',$phone)) stop(array(
			'result'=>'ERROR', 'desc'=>"Наш оператор не сможет связаться по Вашему телефону ($phone)! Пожалуйста укажите другой телефон (Феникс или Лугаком)"
		));
		if($in['unique_id'] == 0) {
			$req = array_intersect_key($client,array('uid'=>0,'rid'=>0,'user'=>0,'fio'=>0,'phone'=>0,'address'=>0));
			$req = array_merge($req,array('type'=>$in['cltype'],'content'=>$in['content'],'operator'=>'CLIENT'));
			if(!($id = $q->insert('claims',$req))) {
				stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
			}
		}else{
			$req = array('unique_id'=>$in['unique_id'],'type'=>$in['cltype'],'content'=>$in['content'],'operator'=>'CLIENT');
			if(!($id = $q->update_record('claims',$req))) {
				stop(array('result'=>'ERROR','desc'=>implode('<br>',$q->errors)));
			}
			$id = $in['unique_id'];
		}
		$out = "От Вас получено заявление:<BR><H2>{$id}</H2>";
		if($client['email'] != '') $out .= "<br>результаты рассмотрения будут направлены Вам на адрес: ".$client['email'];
		else $out .= "<br><p class=\"warning\">Мы не сможем предоставить Вам результаты работы по вашему заявлению так как Вы не указали свой адрес электронной почты</p>";
		$_SESSION['sess_user']['deposit'] = $client['deposit'] = $newdeposit = $q->get('users',$client['uid'],'','deposit');
		stop(array('result'=>'OK','content'=>$out,'deposit'=>sprintf("%.2f {$valute['short']}.",$newdeposit)));
		break;

	case 'macaddress':
		stop(array('result'=>'OK','content'=>"пока не реализовано"));
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre>
		<center>Эта функция пока не реализована</center><pre>"
		));
}
?>
