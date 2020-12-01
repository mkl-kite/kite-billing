<?php
$mac = (isset($_REQUEST['mac']))? preg_replace('/[^0-9A-Fa-f:\- ]/','',$_REQUEST['mac']):'';
$host = (isset($_REQUEST['host']))? preg_replace('/[^0-9A-Za-z\-]/','',$_REQUEST['host']):'';

include_once("utils.php");
?><CENTER><?php
?>
<br>
<p><IMG SRC="<?php echo "{$config['rra_url']}?do=client_graph&host={$host}&mac={$mac}"; ?>" border="0"></p>
<p><IMG SRC="<?php echo "{$config['rra_url']}?do=client_graph&host={$host}&mac={$mac}&period=week"; ?>" border="0"></p>
<p><IMG SRC="<?php echo "{$config['rra_url']}?do=client_graph&host={$host}&mac={$mac}&period=month"; ?>" border="0"></p>
<p><IMG SRC="<?php echo "{$config['rra_url']}?do=client_graph&host={$host}&mac={$mac}&period=year"; ?>" border="0"></p>
</CENTER>
