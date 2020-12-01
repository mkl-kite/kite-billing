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
	font: bold 13pt/1.2 serif;
    text-align: center; 
	margin-top: 10pt;
}
p {
	font: normal 13pt serif;
	text-align:justify;
	margin: 6pt 0;
}
.header {
	font: bold 12pt serif;
    text-align: center; 
	padding-bottom:3pt;
	margin:2cm 0 5mm;
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
    text-align: center;
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
    font: italic bold 13pt tahoma; 
    text-decoration:none;
	margin:0;
	padding:0;
	height:18pt;
}
i {
	display:inline-block;
	position:relative;
	font: italic 7pt tahoma;
	top:-4pt;
}
ul {
    text-align: left; 
	font: normal 11pt serif;
}
ul.numeric{
	list-style:none;
	padding:0;
	counter-reset: li;
}
ul.numeric ul.numeric {
	list-style:none;
	padding:0;
	margin:2mm 0;
	counter-reset: li;
}
ul.numeric > li{
    text-align: center;
	font: normal bold 10pt sans-serif;
}
ul.numeric > li p {
	margin: 1mm 0;
}
ul.numeric > li li {
    text-align: justify; 
	font: normal 11pt/1.08 serif;
}
ul.numeric > li li .value {
    text-align: left;
	background-color:#fff;
	min-width: 10cm;
	float:right;
	display:inline-block
}
ul.numeric > li:before {
	counter-increment: li;
	content: counters(li,".") ". ";
}
li { padding: 3mm 0 0 0; }
#content { text-align:center; }
#content p { text-indent:1cm; }
#content p:first-child{ text-indent:0;}
u {
	font: italic bold 13pt tahoma;
	padding:0 2mm;
	text-decoration:none;
	border-bottom:1pt solid black;
}
.banner p { text-align:center; }
div.talon {
	text-align:center;
	border-top:1px dashed black;
	margin: 15mm 0 15mm 0;
}
div.talon > p {
	text-align:left;
}
img.logo {
	display:inline-block;
}
h4 { margin-bottom:10mm; }
p#sine { text-align:left; }
p#sine em { font-weight:bold; }
.footer u { display: inline-block; }
</style>

<div class="header">
<h3>УВЕДОМЛЕНИЕ от <SPAN>"<u> <?php echo cyrdate($doc['created'],'%d');?> </u> " <u> <?php echo cyrdate($doc['created'],'%B');?> </u> 20 <u> <?php echo cyrdate($doc['created'],'%y');?> </u>г.</SPAN> о наличии/ отсутствии<br>
технической возможности подключения к сети Интернет</h3>
<img class="logo" src="<?php echo COMPANY_LOGO;?>">
</div>

<div id="content">
<h4>в соответствии с условиями Договора на оказание услуг по передачи данных / Договора-оферты на оказание услуг доступа к сети Интернет (далее – Уведомление)<br><i>(подчеркнуть вид договора)</i></h4>

<div class="banner">
	<p><?php echo $doc['fio'];?></p><i>(ФИО зявителя)</i>
	<p><?php echo $doc['address'];?></p><i>(адрес подключения)</i>
	<h3>По указанному адресу <u><?php echo $doc['reply'];?></u> техническая возможность подключения к сети Интернет</h3>
	<i>(результат рассмотрения заявления  о заключении договора о предоставлении услуг по передаче данных, кроме услуг по передачи голосовой информации)</i>
</div>

<p>В случае положительного ответа о технической возможности подключения, для заключения договора о передаче данных, необходим следующий пакет документов (п. 13, 15, 17, 19, 22 Правил предоставления и получения телекоммуникационных услуг по передаче данных на территории Донецкой Народной Республики, утвержденных Постановлением Совета Министров ДНР от 10.08.2018 г., вступивших в силу 06.09.2018 г., ) :</p>
<ul>
<li>заверенная надлежащим образом копия паспорта или иного документа, его заменяющего, с указанием места регистрации.</li>
<li>заверенная надлежащим образом копия документа, удостоверяющего регистрацию физического лица в Республиканском реестре физических лиц –налогоплательщиков (карточки налогоплательщика) или справку о наличии права осуществлять любые платежи по серии и номеру паспорта для лиц, которые из-за своих религиозных убеждений отказываются от регистрационного номера учетной карточки налогоплательщика и сообщили об этом в соответствующий орган доходов и сборов Донецкой Народной Республики.</li>
<li>согласие на обработку персональных данных, подписанное будущим Абонентом.</li>
</ul>
<div class="banner" style="width:10cm"><p id="sine"><em><?php echo FIRMNAME;?></em></div>
<div class="talon">
<img class="logo" src="<?php echo COMPANY_LOGO;?>">
<p>С результатами рассмотрения заявления (уведомления) б/н от <SPAN>"<u> <?php echo cyrdate($doc['created'],'%d');?> </u> " <u> <?php echo cyrdate($doc['created'],'%B');?> </u> 20 <u> <?php echo cyrdate($doc['created'],'%y');?> </u>г.</SPAN> о заключении договора на предоставлении услуг по передаче данных ОЗНАКОМЛЕН:<br><br></p>
</div>

<div class="footer">
	<SPAN>"<u style="width:6mm"></u> " <u style="width:2.5cm"></u> 20 <u style="width:6mm"></u> г.</SPAN><SPAN class="sign" style="width:8cm">Расшифровка подписи</SPAN><SPAN class="sign" style="width:4cm">подпись</SPAN>
</div>
</div>
