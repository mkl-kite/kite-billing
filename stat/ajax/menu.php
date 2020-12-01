<?php
include_once("classes.php");

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;

switch($in['do']){

	case 'news':
		$out = "<p>Приветствуем Вас на нашем сервере.<br><br>";
		if($news = $q->select("SELECT n.*, o.status, o.unique_id FROM news n LEFT OUTER JOIN operators o ON n.operator=o.unique_id WHERE (uid=0 OR uid='{$client['uid']}') AND  expired>now() ORDER BY n.created DESC")) {
			$out .= "<p>Наши объявления:</p>";
			foreach($news as $k=>$v) {
				$out .= "<div class=\"news\"><p>".cyrdate($v['created'])." {$v['name']}</p>{$v['content']}<p>{$config['op_status'][$v['status']]} ".($v['unique_id']? "({$v['unique_id']})" : COMPANY_NAME)."</p></div>";
			}
		}
		$out .= "</p>";
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'tarif':
		if(!($tarif = $q->get('packets',$client['pid']))) {
			stop(array('result'=>'ERROR','desc'=>"Не удалось получить информацию!"));
		}
		$out = "<div class=\"tarif\"><p><span class=\"label\">название</span><span class=\"data\">".$tarif['name']."</span></p>";
		if($tarif['tos']==0 && $tarif['fixed']==10) {
			$out .= "<p><span class=\"label\">дата начала</span><span class=\"data\">".cyrdate($client['late_payment'],'%d %B %Y')."</span></p>";
			$out .= "<p><span class=\"label\">дата окончания</span><span class=\"data\">".cyrdate($client['expired'],'%d %B %Y')."</span></p>";
		}
		$out .= "<br><p><span class=\"label\">тип тарификации</span><span class=\"data\">".$config['tos'][$tarif['tos']]."</span></p>";
		if($tarif['tos']!=0 && $tarif['direction']>0) 
			$out .= "<p><span class=\"label\">учитывается трафик</span><span class=\"data\">".$config['direction'][$tarif['direction']]."</span></p>";
		if($tarif['tos']==0 && $tarif['fixed']>0) {
			$out .= "<p><span class=\"label\">Фикс. сумма снимается</span><span class=\"data\">".$config['fixed'][$tarif['fixed']]['title']."</span></p>";
			$out .= "<p><span class=\"label\">Фиксированная сумма</span><span class=\"data\">".sprintf("%.2f",$tarif['fixed_cost'])."</span></p>";
			if($tarif['fixed']==10)
				$out .= "<p><span class=\"label\">тарифный период</span><span class=\"data\">".$tarif['period']." мес.</span></p>";
		}
		$out .= "</div>";
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'claim':
		$content = ''; $cltype=''; $claim['unique_id'] = 0; $out = ''; $list = '';
		if($client['email'] == ""){
			$out = "<p>Наша компания сможет уведомить Вас о результатах работы по вашему заявлению если вы предоставите свой <span class=\"warning\">адрес электронной почты.</span> (слева в меню)</p>";
		}
		if($claim = $q->select("SELECT * FROM claims WHERE user='{$client['user']}' AND status=0 AND operator='CLIENT' ORDER BY claimtime DESC",1)) {
			$content=$claim['content'];
			$cltype=$claim['type'];
		}
		foreach($config['claim_types'] as $k=>$v) $list .= sprintf("<option value=\"%s\" %s>%s</option>",$k,(($cltype==$k)?'selected':''),$v);
		$out .= '
		<form>'.
		(($claim['unique_id']>0)?'<input type="hidden" name="unique_id" value="'.$claim['unique_id'].'">':'').'
		<p><span class="label">Тип заявления</span>
		<select type=text name=cltype size=1>'.$list.'</select></p>
		<p><span class="label">Укажите Вашу проблему</span>
		<textarea type=text name=content style="width:300px;height:200px">'.$content.'</textarea></p>
		<p><input class="button" type="button" go="usermod" do="newclaim" value="Послать"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'money':
		$out = '
		<form>
		<p><span class="label">Код (указанный в карточке)</span>
		<input type=text name=card style="width:300px" value=""></p>
		<p><input class="button" type="button" go="usermod" do="pay" value="Оплатить"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'password':
		$out = '
		<form><br>Важно: Символы / \\ " \' ! @ # % ^ & * ( ) { } ; | < > не разрешены для использования в строке пароля и автоматически удаляются!<br><br>
		<input type="hidden" name="cryptmethod" value=1>
		<p><span class="label">Старый Пароль:</span>
		<input type="password" name="pw" style="width:150px">
		</p><br><br>
		<p><span class="label">Новый Пароль:</span>
		<input type="password" name="pw1" style="width:150px"><br></p>
		<p><span class="label">Ещё раз Новый Пароль:</span>
		<input type="password" name="pw2" style="width:150px"></p>

		<p><input class="button" type="button" go="usermod" do="changepassword" value="Изменить"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'stat':
		$in['begin'] = (array_key_exists('begin',$_REQUEST))? str($_REQUEST['begin']) : date("Y-m-d",strtotime("-1 month"));
		$in['end'] = (array_key_exists('end',$_REQUEST))? str($_REQUEST['end']) : date('Y-m-d G:i:s');
		$fld = array('starttime','acctsessiontime','tout','tin','before_billing','billing_minus');
		$fld = array_flip($fld);
		$out='';
		$tab = $q->select("
			SELECT 
				radacctid,
				UNIX_TIMESTAMP(acctstarttime) as starttime,
				acctsessiontime,
				callingstationid,
				framedipaddress,
				outputgigawords << 32 | acctoutputoctets as tin,
				inputgigawords << 32 | acctinputoctets as tout, 
				before_billing,
				billing_minus
			FROM radacct
			WHERE 
				acctstarttime > '{$in['begin']}' AND
				acctstarttime < '{$in['end']}' AND
				username = '{$client['user']}'
			ORDER BY acctstarttime
		");
		$dprev = date('Y-m-d');
		$out = "<table class=\"stat\">";
		$out .= "<thead><tr><td>Начало</td><td>Длительноcть</td><td>in</td><td>out</td><td>Было</td><td>Снято</td></tr></thead>";
		if($tab) foreach($tab as $k=>$v) {
			$title = array(); foreach(array_diff_key($v,$fld) as $n=>$f) $title[] = "$n: $f";
			if($dprev != ($now = strftime('%d %B %Y',$v['starttime']))) {
				$out .= "<tr><td colspan=\"".count($fld)."\">".$now."</td></tr>";
				$dprev = $now;
			}
			$out.="<tr id=\"{$v['radacctid']}\" title=\"".implode("\r",$title)."\">";
			foreach($v as $i=>$cell) {
				if(key_exists($i,$fld)) {
					if(function_exists($sub = 'sub_'.$i)) $cell = $sub($cell);
					$out.="<td>$cell</td>";
				}
			}
			$out.="</tr>";
		}
		$out.="</table>";
		
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'paystat':
		$in['begin'] = (array_key_exists('begin',$_REQUEST))? str($_REQUEST['begin']) : date("Y-m-d",strtotime("-1 year"));
		$in['end'] = (array_key_exists('end',$_REQUEST))? str($_REQUEST['end']) : date('Y-m-d G:i:s');
		$fld = array('starttime','acctsessiontime','before_billing','billing_minus');
		$fld = array_flip($fld);
		$out='';
		$tab = $q->select("
			SELECT 
				p.unique_id,
				UNIX_TIMESTAMP(p.acttime) as acttime,
				pk.name,
				p.card,
				p.povod_id,
				pv.povod,
				p.from,
				o.unique_id as op,
				o.status as status,
				p.note,
				p.money,
				c.short
			FROM pay as p 
				LEFT OUTER JOIN packets as pk ON p.pid=pk.pid
				LEFT OUTER JOIN povod as pv ON p.povod_id=pv.povod_id
				LEFT OUTER JOIN currency as c ON p.currency=c.id
				LEFT OUTER JOIN operators as o ON p.from=o.login
			WHERE 
				p.acttime > '{$in['begin']}' AND
				p.acttime < '{$in['end']}' AND
				p.uid = '{$client['uid']}'
			ORDER BY acttime
		");
		$out = "<table class=\"stat\">";
		$out .= "<thead><tr><td>Дата</td><td>Пакет</td><td>Тип платежа</td><td>источник</td><td>Сумма</td></tr></thead>";
		setlocale(LC_TIME,"ru_RU.UTF-8");
		if($tab) foreach($tab as $k=>$v) {
			if(!is_null($v['op'])) $v['from'] = $config['op_status'][$v['status']]."(".$v['op'].")";
			if($v['povod_id'] == 1) $v['povod'] = $v['povod']." ".$v['card'];
			if($v['povod_id'] == 17) $v['povod'] = $v['povod']." ".$v['note'];
			$out.="<tr id=\"{$v['unique_id']}\">";
			$out.="<td>".strftime('%d %B %Y',$v['acttime'])."</td>";
			$out.="<td>{$v['name']}</td>";
			$out.="<td>".$v['povod']."</td>";
			$out.="<td>".$v['from']."</td>";
			$out.="<td>".sprintf("%.2f",$v['money'])." {$v['short']}</td>";
			$out.="</tr>";
		}
		$out.="</table>";
		setlocale(LC_TIME,"C");
		
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'smsstat':
		$in['begin'] = (array_key_exists('begin',$_REQUEST))? str($_REQUEST['begin']) : date("Y-m-d",strtotime("-1 year"));
		$in['end'] = (array_key_exists('end',$_REQUEST))? str($_REQUEST['end']) : date('Y-m-d G:i:s');
		$out = "<p>Список посланных Вам SMS:<br>";
		if($news = $q->select("SELECT * FROM sms WHERE created>'{$in['begin']}' AND created<='{$in['end']}' AND uid='{$client['uid']}' ORDER BY created DESC")) {
			foreach($news as $k=>$v) {
				$out .= "<div class=\"sms\"><p>".cyrdate($v['created'])." <span>{$v['phone']}</span></p>{$v['message']}</div>";
			}
		}
		$out .= "</p>";
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'documents':
		$out = "<p>Список выданных Вам документов:<br>";
		if($docs = $q->select("SELECT * FROM documents WHERE uid='{$client['uid']}' ORDER BY created")) {
			foreach($docs as $k=>$v) {
				$button="<A href=\"docpdf.php?id={$v['id']}\" class=\"button\" target=\"blank\">Открыть</A>";
				$out .= "<div class=\"doc\"><p>".cyrdate($v['created'])." <span>{$doctypes[$v['type']]['label']}</span>{$button}</p></div>";
			}
		}
		$out .= "</p>";
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'changepacket':
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'present':
		$list = '';
		foreach($config['menu']['present']['summ'] as $k=>$v) $list .= sprintf("<option>%.0f</option>",$v);
		$out = '
		<form><br>Сумма, которую вы выберете будет снята с вашего счета и перечислена на счет указанный в поле "кому".<br><br>
		<p><span class="label">Сумма (для передачи)</span>
		<select type=text name=present size=1>'.$list.'</select>
		<p><span class="label">Лицевой счет (кому):</span> 
		<input type=text name=friend size="20" value=""></p><br><br>
		<p><input class="button" type="button" go="usermod" do="present" value="Передать"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'newuser':
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'newphone':
		$out = '
		<form> <BR>На этот номер нашей компанией высылаются уведомления о приближении даты окончания пакета, поступивших платежах, и подтверждения принятых заявок.<br><br>
		<p><span class="label">Номер телефона</span>
		<input type=text name=newphone size="20" value="'.$client['phone'].'"></p><br><br>
		<p><input class="button" type="button" go="usermod" do="newphone" value="Заменить"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	case 'newmail':
		$out = '
		<form><BR>Этот адрес наша фирма использует для официальных уведомлений о различных событиях.<br><br>
		<p><span class="label">Почтовый адрес</span>
		<input type=text name=newmail size="20" value="'.$client['email'].'"></p><br><br>
		<p><input class="button" type="button" go="usermod" do="newmail" value="Сохранить"></p>
		</form>
		';
		stop(array('result'=>'OK','content'=>$out));
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre><center>неверные данные<br>
			go={$in['go']}<br>
			do={$in['do']}<br>
			id={$in['id']}<br>
			</center><pre>"
		));
}

function year($t) { return date('Y',$t); }
function day($t) { return date('d',$t); }
function month($t) { return date('m',$t); }
function sub_starttime($t) { return date('H:i',$t); }
function sub_acctsessiontime($t) { return uptime('d h:m:s',$t); }
function sub_before_billing($d) { return sprintf("%.2f",$d); }
function sub_billing_minus($d) { return sprintf("%.2f",$d); }
function sub_tin($f){ return sprintf("%.1f",$f/MBYTE); };
function sub_tout($f){ return sprintf("%.1f",$f/MBYTE); };

?>
