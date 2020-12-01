<style>
BODY {
	background-color: #FFFFFF;
}
H1, H2, H3, H4, H5 { margin: 0; }
H1 {
	font: bold 20pt serif;
    text-align: center; 
	padding: 0 20 0 0;
	margin-top: 10pt;
	z-index:10;
}
H2 {
	font: normal 16pt/1.3 serif;
    text-align: center; 
	margin-top: 10pt;
	line-height:1.6;
}
H3 {
	font: bold 13pt serif;
    text-align: center; 
	margin-top: 10pt;
}
p {
	font: normal 13pt serif;
	text-align:justify;
	margin: 16pt 0;
	line-height:1.6;
}
.header {
	font: bold 12pt serif;
    text-align: center; 
	padding-bottom:3pt;
	margin:2cm 0 15mm;
}

.sign {
	border-top: solid 1px black;
	font: normal 8pt serif;
	width:100px;
	margin-top:20px;
	float:right;
	text-align:center;
	margin-right:20px;
}
.banner {
    text-align: left; 
	background: #fff;
}
.banner em {
	display:inline-block;
	position:relative;
	font: normal 13pt serif;
	padding:0 3mm 0 0;
	top:2mm;
	background:#fff;
}
.banner p {
	border-bottom:1px solid #000;
    font: italic bold 12pt arial; 
    text-decoration:none;
	margin:0;
	padding:0;
	height:18pt;
}
.banner i {
	display:inline-block;
	position:relative;
	font: italic 7pt tahoma;
	top:-4pt;
}
#content p {
	text-indent:1cm;
}
#content p:first-child{ text-indent:0;}
span > u {
	font: italic bold 13pt tahoma;
	padding:0 2mm;
	text-decoration:none;
	border-bottom:1pt solid black;
}
</style>
<?php
$now=date("Y-m-d H:m:s",time());
$my_date=date("Y-m-d",time());

# Получение данных документа
if(!isset($doc)) $doc = get_docdata($id);
?>
<div class="header">
	<H2>СОГЛАСИЕ<br>НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ</H2>
</div>

<div class="banner"><p><em>Я, </em>&emsp; <?php echo $doc['fio']; ?> &emsp;</p><i style="left:10cm">(ФИО)</i></div>
<div class="banner" style="float:left"><p><em>паспорт </em>&emsp; <?php echo $doc['psp']; ?> &emsp;</p><i style="left:3cm">(Серия, номер)</i></div>
<div class="banner" style="float:right;width:17cm"><p><em>выдан </em>&emsp; <?php echo $doc['pspissue']; ?> &emsp;</p><i style="left:6.5cm">(когда и кем выдан)</i></div>
<div style="clear:both"></div>
<div class="banner"><p><em>адрес регистрации: </em>&emsp; <?php echo $doc['address']; ?> &emsp;</p></div>
<div style="clear:both"></div>

<div id="content">
<p>даю свое согласие на обработку в офисе по обслуживанию абонентов <b><?php echo FIRMNAME; ?></b> моих персональных данных, относящихся исключительно к перечисленным ниже категориям персональных данных: фамилия, имя, отчество; пол; дата рождения; тип документа, удостоверяющего личность; данные документа, удостоверяющего личность; гражданство.</p>
<p>Я даю согласие на использование персональных данных исключительно в целях предоставление телекоммуникационных услуг, а также на хранение данных об этих результатах на электронных носителях.</p>
<p>Настоящее согласие предоставляется мной на осуществление действий в отношении моих персональных данных, которые необходимы для достижения указанных выше целей, включая (без ограничения) сбор, систематизацию, накопление, хранение, уточнение (обновление, изменение), использование, передачу третьим лицам для осуществления действий по обмену информацией, обезличивание, блокирование персональных данных, а также осуществление любых иных действий, предусмотренных действующим законодательством Российской Федерации.</p>
<p>Я проинформирован, что <b><?php echo FIRMNAME; ?></b> гарантирует обработку моих персональных данных в соответствии с действующим законодательством Донецкой Народной Республики как неавтоматизированным, так и автоматизированным способами.</p>
<p>Данное согласие действует до достижения целей обработки персональных данных или в течение срока хранения информации.</p>
<p>Данное согласие может быть отозвано в любой момент по моему  письменному заявлению.</p>
<p>Я подтверждаю, что, давая такое согласие, я действую по собственной воле и в своих интересах.</p>
</div>
<div style="height:3cm"></div>
<SPAN>"<u> <?php echo cyrdate($doc['created'],'%d');?> </u> " <u> <?php echo cyrdate($doc['created'],'%B');?> </u> 20 <u> <?php echo cyrdate($doc['created'],'%y');?> </u>г.</SPAN>
<SPAN class="sign" style="width:8cm">Расшифровка подписи</SPAN><SPAN class="sign" style="width:4cm">подпись</SPAN>
