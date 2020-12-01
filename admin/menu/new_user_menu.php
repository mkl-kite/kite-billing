<SCRIPT language="JavaScript">
function AddUserByClaim(a) {
	if(!ldr) ldr = $.loader()
	$.popupForm({
		data:'go=stdform&do=new&tname=users&table=users&id=new&cid='+a,
		onsubmit: function(d){
			if(d.append && d.append[0] && d.append[0][0]){
				uid = d.append[0][0];
				window.open('users.php?uid='+uid,'_self');
			}else{
				window.open($.paramToURL({}),'_self');
			}
		},
		loader: ldr
	})
}
</SCRIPT>
<?php
$newusrmenu = array(
	'Заявление'	=>array('level'=>3,'HREF'=>"docs/claim_contract.pdf","go"=>'claim_contract','target'=>'blank'),
	'Создать'	=>array('level'=>4,'class'=>'linkform','add'=>"go=stdform&do=new&tname=users&table=users&id=new","go"=>'newuser'),
);
echo make_menu($newusrmenu,'newusrmenu'); ?>

<CENTER><BR><BR>
<?php


$new = $q->select("
	SELECT c.unique_id, c.user, r.r_name, c.claimtime, c.fio, c.phone, c.address, c.operator, c.content, wo.prescribe
	FROM `claims` as c, `claimperform` as cp, `workorders` as wo, `rayon` as r
	WHERE c.unique_id=cp.cid and
		cp.woid=wo.woid and
		c.rid=r.rid and
		c.type=1 and
		c.uid=0 and
		cp.status!=2 and
		(c.status=2 or (c.status=4 and wo.status<2))
	ORDER BY wo.prescribe
");

if ($q->rows() == 0) { 
	show_error("В выполняемых нарядах отсутствуют незавершенные задания на установку");
	include("bottom.php");
	exit;
} ?>

<h3>Создать учетную запись на основе заявления:</h3><BR>

<TABLE class="normal" CELLSPACING=2 WIDTH=90%>
<THEAD>
<TR>
<TD>&#8470;</TD>
<TD>Дата</TD>
<TD>Ф.И.О.</TD>
<TD>Район</TD>
<TD>Адрес</TD>
<TD>Телефон</TD>
</THEAD>
<TBODY>
<?php
foreach($new as $k=>$v) { ?>
	<TR class="picked" onclick=AddUserByClaim(<?php echo $v['unique_id']; ?>)>
	<TD ALIGN=center><?php echo $v['unique_id']; ?></TD>
	<TD ALIGN=center nowrap><?php echo $v['prescribe']; ?></TD>
	<TD ALIGN=center><?php echo $v['fio']; ?></TD>
	<TD ALIGN=center><?php echo $v['r_name']; ?></TD>
	<TD ALIGN=center><?php echo $v['address']; ?></TD>
	<TD ALIGN=center><?php echo $v['phone']; ?></TD>
	</TR> <?php
}

?>
</TBODY>
<TFOOT>
<TR><TD colspan="6"></TD></TR>
</TFOOT>
</TABLE>
</CENTER>
