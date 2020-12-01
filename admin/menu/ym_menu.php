<?php
     $yearf=(array_key_exists('yearf',$_POST))? intval($_POST['yearf']):0;
     $monthf=(array_key_exists('monthf',$_POST))? intval($_POST['monthf']):0;
     $yeart=(array_key_exists('yeart',$_POST))? intval($_POST['yeart']):0;
     $montht=(array_key_exists('montht',$_POST))? intval($_POST['montht']):0;
     $is_period=(array_key_exists('is_period',$_POST))? intval($_POST['is_period']) : "";

if (($yearf == 0) OR ($yearf > date("Y"))) {
    $ny = date("Y");
} else {
    $ny = $yearf;
}
if (($monthf == 0) OR ($monthf > 12)) {
    $nm = date("m");
} else {
    $nm = $monthf;
}

 if (($yeart == 0) OR ($yeart > date("Y"))) {
     $nyt = date("Y");
 } else {
     $nyt = $yeart;
 }

 if (($montht == 0) OR ($montht > 12)) {
     $nmt = date("m");
 } else {
     $nmt = $montht;
 }

 if (!$is_period) {
     $nyt=$ny;
     $nmt=$nm;
  }
    $_SESSION['yearf']=$yearf;
     $_SESSION['monthf']=$monthf;
     $_SESSION['yeart']=$yeart;
     $_SESSION['montht']=$montht;
     $_SESSION['is_period']=$is_period;  
?>
<?php

$res = $q->select("SELECT YEAR(MIN(acctstarttime)) AS start_year, YEAR(MAX(acctstarttime)) AS stop_year FROM radacct",1);

?>

<FORM ACTION="<?php echo @$faction;?>" METHOD=post>
<?php if($packet) { ?>
<INPUT NAME=pid TYPE=hidden VALUE="<?php echo @$pid;?>">
<?php } ?>
<TABLE CELLSPACING = 0 CELLPADDING=6>
<TR>
 <TD><select name=is_period onChange="form.submit();">
  <option value=0<?php echo (!$is_period)? " selected":"";?>>Дата
  <option value=1<?php echo ($is_period)? " selected":"";?>>Период
  </select></TD>
  <?php
      if (!$is_period) {
  ?>
  <td><a href="#" onClick="prev();">&lt;&lt;</a></td>
  <?php
      }
      if ($is_period) {
  ?>
  <TD>С </TD>
  <?php
      }
  ?>
  <TD>Год: </TD>
  <TD><SELECT NAME=yearf onchange="this.form.submit()">
  <?php
  for($y = $res['start_year'];$y <= $res['stop_year'];$y++) {
      echo "<OPTION value=$y".(($y==$ny)?" SELECTED":"").">".$y;
  }
  ?>
  </SELECT></TD>
  <TD>Месяц: </TD>
  <TD><SELECT NAME=monthf onchange="this.form.submit()">
  <?php
  for ($m=1;$m<=12;$m++) {
      echo "<OPTION VALUE=$m".(($m==$nm)?" SELECTED":"").">$mon[$m]\n";
  }
  ?>
 </SELECT></TD>
 <?php
     if ($is_period) {
 ?>
 <TD>По: </TD>
<TD>Год: </TD>
 <TD><SELECT NAME=yeart onchange="this.form.submit()">
 <?php
    for($y = $res['start_year'];$y <= $res['stop_year'];$y++) {
     echo "<OPTION value=$y".(($y==$nyt)?" SELECTED":"").">".$y;
  }
?>
  </SELECT></TD>
    <TD>Месяц: </TD>
 <TD><SELECT NAME=montht onchange="this.form.submit()">
<?php
    for ($m=1;$m<=12;$m++) {
     echo "<OPTION VALUE=$m".(($m==$nmt)?" SELECTED":"").">$mon[$m]\n";
  }
?>
  </SELECT></TD>
 <?php
     }
     if (!$is_period) {
 ?>
 <td><a href="#" onClick="next();">&gt;&gt;</a></td>
 <?php
     }
 ?>
			  
<?php if(@$fstat) { ?>
<TD>Логин: </TD>
<TD><INPUT NAME=user TYPE=text SIZE=20 MAXLENGTH=16 VALUE="<?php echo $user;?>"></TD>
<?php } ?>
<NOSCRIPT><TD><INPUT TYPE=submit VALUE=Показать></TD></NOSCRIPT>
</TR>
</TABLE>
</FORM>
<BR>
 <script>
 function prev()
 {
     var m=--document.forms[0].monthf.value;
     if (m==0) {
         document.forms[0].monthf.value=12;
         document.forms[0].yearf.value--;
     }
     document.forms[0].submit();
 }
 function next()
 {
     var m=++document.forms[0].monthf.value;
     if (m==13) {
         document.forms[0].monthf.value=1;
         document.forms[0].yearf.value++;
     }
     document.forms[0].submit();
 }
 </script>

