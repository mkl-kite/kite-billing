<?php
$CSSfile="base-admin.css";
if (@$go=="") if($opdata['status']>2){
	$go="claimplane";
	include("claims.php");
}else{
	$go="rayon";
	include("references.php");
}
?>
