<?php
$result='';
//foreach($_REQUEST as $nome => $valor){
//	if(isset($nome)&&isset($valor)){
//		$result.=$nome . " : " . $valor . "<BR>";
//	}
//}
$result.=date('d-m-Y H:i:s');
$result.="<BR>";
echo $result;
?>