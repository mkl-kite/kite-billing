<?php
include_once("news.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'news';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;

$q = new sql_query($config['db']);
$t = $tables['news'];
$t['name']='news';

switch($in['do']){

	case 'get':
		if($d=get_news()){
			stop(array('result'=>'OK','news'=>$d));
		}else{
			stop(array('result'=>'ERROR','desc'=>"Районы не найдены"));
		}
		break;

	case 'get_table':
		$t['header'] = 'Новости';
		$t['fields']['operator']['type'] = 'text';
		foreach($t['fields'] as $k=>$v){ if(isset($t['fields'][$k]['style'])) unset($t['fields'][$k]['style']); }
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'add':
	case 'new':
		$t['header'] = 'Новость от<br>'.cyrdate();
		$t['fields']['created']['type'] = 'hidden';
		$t['defaults']['expired'] = strftime('%Y-%m-%d',strtotime('14 day'));
		unset($t['fields']['created']);
		$form = new form($config);
		$out = $form->getnew($t);
		stop($out);
		break;

	case 'edit':
		$form = new form($config);
		$t['name']='news';
		$t['fields']['created']['type'] = 'hidden';
		stop($form->get($t));
		break;

	case 'save':
		$t = $tables['news'];
		$t['name']='news';
		$t['form_onsave']='news_onsave';
		$form = new form($config);
		stop($form->save($t));
		break;

	case 'delete':
	case 'remove':
		if($news=$q->get('news',$in['id'])){
			$form = new form($config);
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить новость:<br>'{$news['name']}' ?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанная новость отсутствует в базе!'));
		}
		break;

	case 'realremove':
		$form = new form($config);
		$t['id']=$in['id'];
		stop($form->delete($t));
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
?>
