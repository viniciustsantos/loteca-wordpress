<?php
include_once '../../../wp-config.php';
include_once 'loteca_captura.php';
if(loteca_captura()){
	$result="JOGOS DA LOTECA CAPTURADOS NO SITE DA CEF - ATUALIZE A PÁGINA - ";
	$result.=date('d-m-Y H:i:s');
	$result.="<BR>";
}
echo $result;
?>