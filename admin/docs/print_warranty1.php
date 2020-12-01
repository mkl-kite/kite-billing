<?php
include_once("classes.php");
if(!$doc && $id){
	$q = new sql_query($config['db']);
	$doc = $q->get('users',$id);
}
?>
<style type="text/css">
.warranty {
    font-family: sans-serif; 
    font-size: 10.5pt; 
    text-align: left; 
}
.warranty h2 {
    font-weight: bold; 
    font-size: 12pt; 
    text-align: center; 
}
.warranty h3 {
    font-weight: bold; 
    font-size: 11pt; 
    text-align: center; 
    margin: 1cm 0 0 0;
}
.box {
	margin-left:60%;
}
.box > div.field {
	height:8pt;
    margin-bottom: 10pt;
}
.box .field > div {
	background: #ffffff none repeat scroll 0 0;
	float: left;
	font: 10pt arial;
	font-style : italic;
	height: 14pt;
	padding: 0 5px 0 0;
	margin: 5pt 7mm 0 0;
}

.box .field p {
    border-bottom: 1px solid #000000;
    font-size: 11pt;
}
.box > p span {
    border-bottom: none;
}
.warranty .footer {
	margin: 9mm 0 0 0;
	text-align: center;
}
.warranty .footer div:first-child {
	float: left;
}
.warranty .footer div:last-child {
	float: right;
}
.warranty .footer div {
	display: inline-block;
	text-align: center;
	width: 6cm;
	margin: 0 5mm;
}
.warranty .footer div > p {
	margin-top: 1pt;
    font-size: 7pt; 
	border-top: 1px solid black;
}
.warranty ul {
	list-style-type: decimal;
	list-style-position: inside;
	padding: 0;
}
.warranty li {
	margin-top: 8px;
}
.warranty .note {
	margin-top: 20pt;
    font-size: 8pt; 
}
.warranty .note {
	margin-top: 20pt;
    font-size: 8pt; 
}
.warranty .wcode {
	display: inline-block;
	border-bottom: 1px solid black;
	margin-left:2mm;
	width:2cm;
}
.warranty .term {
	display: inline-block;
	border-bottom: 1px solid black;
	text-align:center;
	min-width:3cm;
}
.separator {
	margin: 20mm 0;
	padding-top: 20mm;
	border-bottom: 1px dashed black;
	clear:both;
}
@media screen {
	.printbtn {
		position: absolute;
		top: 0.5cm;
		left: 0.5cm;
		height: 0.6cm;
		width: 1.6cm;
		color: #ccf;
		text-shadow: #000 0 2px 6px;
		text-decoration: none;
		cursor: pointer;
	}
	.printbtn:hover {
		color: #fff !important;
	}
	HTML  {	
		margin: 0;
		padding: 0;
	}
	div#content {
		position: absolute;
	}
}

@media print {
	BODY  {	background-color: #FFFFFF; }
	.printbtn {
		visibility: hidden;
	}
	div#page {
		height: auto;
		overflow: auto;
	}
}
</style>

<SCRIPT language="JavaScript">
<!--//
$(document).ready(function() {
	var d = $('.printbtn')
	if(d) {
		d.detach()
		d.appendTo('body')
		d.click(function(){
			window.print();
		})
	}
	var w = $('.warranty').html();
	if(w) $('#page').append('<div class="separator">').append('<div class="warranty">'+w+'</div>');
})
//-->
</SCRIPT>

<div class="warranty">
<h2>ГАРАНТИЙНЫЙ ТАЛОН &#8470;<span class="wcode"><?php echo @$doc['id']; ?></span></h2>

<div class="box">
<div class="field"><div>Адрес</div><p class="param"> &nbsp; <span><?php echo @$doc['address']; ?></span></p></div>
<div class="field"><div>Устройство</div><p class="param"> &nbsp; <span><?php echo @$doc['device']; ?></span></p></div>
<div class="field"><div>Модель</div><p class="param"> &nbsp; <span><?php echo @$doc['model']; ?></span></p></div>
<div class="field"><div>s/n или mac</div><p class="param"> &nbsp; <span><?php echo @$doc['code']; ?></span></p></div>
</div>
<div style="clear:both"></div>
<h3>ВНИМАНИЕ!!</h3>
<ul>
<li>Гарантия не распространяется на: на погодные условия (грозу); броски напряжения; попадания влаги; механические повреждения.</li>
<li>В случае неисправности устройства, время тестирования до 3 суток, гарантийный ремонт 1 месяц. На время ремонта и тестирования другое устройство взамен не предоставляется.</li>
<li>Гарантия действительна до <span class="term"><?php echo cyrdate(@$doc['expired']); ?></span></li>
</ul>
<div class="note">
*Гарантия предоставляется при наличии гарантийного талона. Гарантийный талон дает право 
на приобретение нового устройства со скидкой, при условии возврата неисправного оборудования.
</div>
<div class="footer">
<div class="date"><span><?php echo cyrdate(@$doc['created']); ?></span><p>Дата</p></div>
<div class="sine"><br><p>Подпись покупателя</p></div>
<div class="sine"><br><p>Подпись продавца</p></div>
</div>
</div>
<div class="separator"></div>

<div class="warranty">
<h2>ГАРАНТИЙНЫЙ ТАЛОН &#8470;<span class="wcode"><?php echo @$doc['id']; ?></span></h2>

<div class="box">
<div class="field"><div>Адрес</div><p class="param"> &nbsp; <span><?php echo @$doc['address']; ?></span></p></div>
<div class="field"><div>Устройство</div><p class="param"> &nbsp; <span><?php echo @$doc['device']; ?></span></p></div>
<div class="field"><div>Модель</div><p class="param"> &nbsp; <span><?php echo @$doc['model']; ?></span></p></div>
<div class="field"><div>s/n или mac</div><p class="param"> &nbsp; <span><?php echo @$doc['code']; ?></span></p></div>
</div>
<div style="clear:both"></div>
<h3>ВНИМАНИЕ!!</h3>
<ul>
<li>Гарантия не распространяется на: на погодные условия (грозу); броски напряжения; попадания влаги; механические повреждения.</li>
<li>В случае неисправности устройства, время тестирования до 3 суток, гарантийный ремонт 1 месяц. На время ремонта и тестирования другое устройство взамен не предоставляется.</li>
<li>Гарантия действительна до <span class="term"><?php echo cyrdate(@$doc['expired']); ?></span></li>
</ul>
<div class="note">
*Гарантия предоставляется при наличии гарантийного талона. Гарантийный талон дает право 
на приобретение нового устройства со скидкой, при условии возврата неисправного оборудования.
</div>
<div class="footer">
<div class="date"><span><?php echo cyrdate(@$doc['created']); ?></span><p>Дата</p></div>
<div class="sine"><br><p>Подпись покупателя</p></div>
<div class="sine"><br><p>Подпись продавца</p></div>
</div>
</div>

