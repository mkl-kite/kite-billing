<?php
# файл формы выбора периода просмотра и оператора
# Список операторов для выбора
$period_fields = array(
	'call_to'=>array(
		'type'=>'select',
		'label'=>'оператор',
		'title'=>'оператор',
		'style'=>'width:90px',
		'list'=>all2array(list_operators()),
		'value'=>''
	),
	'employer'=>array(
		'type'=>'select',
		'label'=>'служащий',
		'title'=>'оператор',
		'style'=>'width:90px',
		'list'=>all2array(list_of_employers()),
		'value'=>'_'
	),
	'owner'=>array(
		'type'=>'select',
		'label'=>'принадлежность',
		'title'=>'принадлежность',
		'style'=>'width:90px',
		'list'=>all2array($config['owns']),
		'value'=>''
	),
	'date_begin'=>array(
		'type'=>'date',
		'label'=>'начало',
		'class'=>'date',
		'style'=>'width:80px',
		'title'=>'дата начала',
		'value'=>cyrdate(date('Y-m-d'))
	),
	'date_end'=>array(
		'type'=>'date',
		'label'=>'конец',
		'class'=>'date',
		'style'=>'width:80px',
		'title'=>'дата конца',
		'value'=>cyrdate(date('Y-m-d'))
	),
);
if(!isset($config['owns'])) unset($period_fields['owner']);

function period_menu($fields){
	global $config, $q, $opdata, $period;
	$html = "";
	$in = array('/[^0-9,]/','/,,*/'); $out = array('',',');
	$html .= "
	<SCRIPT language=\"javascript\">
	$(document).ready(function() {
		var
		period_submit = function(){
			var url = window.location.href.split('?'), q=$.parseQuery(url[1]);
			$('.period [name]').each(function(i,e){
				var n=$(this).attr('name'), v=$(this).val();
				if(!v || v=='_' && q[n]) delete q[n]
				if(v!='' && v!='_') q[n]=v;
			});
			window.open(url[0]+'?'+$.mkQuery(q),'_self');
			return false;
		}
		$('.period input.date').datepicker({dateFormat: 'dd-mm-yy'})
		$('.period [name]').change(period_submit);
	})
	</SCRIPT>
	";
	$html .= "<div class=\"period\" style=\"margin:8px 0;\">";
	$he = new HtmlElement();
	foreach($fields as $n=>$f){
		if(!isset($f['name'])) $f['name'] = $n;
		if($f['type']=='checklist') {
			if(isset($_REQUEST[$n]) && ($val = preg_replace($in,$out,$_REQUEST[$n]))!='') $f['value'] = $val;
		}elseif($f['type']=='text'){
			if(isset($_REQUEST[$n]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$n])) !='') $f['value'] = $val;
		}elseif($f['type']=='select'){
			if(isset($_REQUEST[$n]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$n]))!='') $f['value'] .= $val;
		}else{
			if(isset($_REQUEST[$n]) && ($val = str($_REQUEST[$n]))!='') $f['value'] = $val;
		}
		if($v = $he->create($f)) $html .= $v;
	}
	$html .= "</div>";
	return $html;
}
?>
