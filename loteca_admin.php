<?php
// loteca_admin.php
function loteca_options() {
	include_once 'loteca_functions.php';
	datetimepicker();
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$result='';
	$result.='<div class="wrap">';
	$result.='Loteca ativa : ';
	$result.=(get_option('loteca_ativa'))?'Sim':'Não';
	$result.='<br>';
	$result.='Versão do Banco de dados : ' . get_option('loteca_db_version') . '<br>';
//	$result.='<p>Limite de desdobramento para processamento : ' . get_option('loteca_limite_desdobramento') . '</p>';
	$result.='Limite de participantes por bolão : ' . get_option('loteca_limite_participante') . '<br>';
	$result.='Formulário: ';
	$form=FALSE;
//	if(isset($_POST['novarodada'])){$result.='Cadastrar nova rodada';$form=TRUE;}
//	if(isset($_POST['novogrupo'])){$result.='Cadastrar novo grupo';$form=TRUE;}
	if(isset($_POST['alterarparametro'])){$result.='Alterar parametros gerais';$form=TRUE;}
	if(isset($_POST['submeternovosparametros'])){$result.='Alterar parametros gerais (confirmação)';$form=TRUE;}
//	if(isset($_POST['cadastrarodada'])){$result.='Cadastrar rodada';$form=TRUE;}
//	if(isset($_POST['cadastragrupo'])){$result.='Cadastrar grupo';$form=TRUE;}
	if(isset($_POST['ativargrupo'])){$result.='Ativar grupo';$form=TRUE;}
	if(isset($_POST['desativargrupo'])){$result.='Desativar grupo';$form=TRUE;}
	if(!$form){$result.='Início';}
	$result.='<br>';
	$result.='</div>';
	$result.='<div class="wrap">';
	$result.="<div class='submit'><form method='POST'>";
//	$result.="&nbsp;<input name='novarodada' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Cadastrar nova rodada' />";
//	$result.="&nbsp;<input name='novogrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Cadastrar novo grupo' />";
	$result.="&nbsp;<input name='alterarparametro' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Alterar parametros gerais' />";
	$result.="&nbsp;<input name='capturacef' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Capturar dados da CEF' />";
	$result.="</form></div>";
	$result.='</div>';
	$listar=TRUE;
	if(isset($_POST['alterarparametro'])){
		$result.=alterar_parametros();
		$listar=FALSE;
	}
	if(isset($_POST['submeternovosparametros'])){
		$result.=submeter_parametros();
		$listar=FALSE;
	}
	if(isset($_POST['ativargrupo'])&&isset($_POST['grupo'])){
		include_once 'loteca_db_admin.php';
		$result.=loteca_ativar_grupo($_POST['grupo']);
		$listar=TRUE;
	}
	if(isset($_POST['desativargrupo'])&&isset($_POST['grupo'])){
		include_once 'loteca_db_admin.php';
		$result.=loteca_desativar_grupo($_POST['grupo']);
		$listar=TRUE;
	}
	if(isset($_POST['capturacef'])){
		include_once 'loteca_captura.php';
		$result.=loteca_captura();
		$listar=TRUE;
	}
	if($listar==TRUE){
		include_once 'loteca_functions.php';
		$result.='<div class="wrap">';
		$result.="<table><tr><td>";
		$result.=tab_rodadas();
		$result.="</td><td>";
		$result.=tab_grupos();
		$result.="</td></tr></table>";
		$result.='</div>';
	}
	echo $result;
}

function alterar_parametros(){
	include_once 'loteca_db_admin.php';
	$result='';
	$result.='<div stile="background-color:#00CED1" class="wrap">';
	$result.="<form method='POST'>";
	$result.='<table><tr><td valign="top">';
	$result.="</tr><tr><td>";
	
	$result.="<table>";
	$result.="<tr><td>Limite de processamento:&nbsp;</td><td><input name='limiteproc' type='text' maxlength=6 min=0 max=999999 VALUE='" . captura_parametros()->limite_proc . "' required /></td></tr>";
	$result.="</table>";

	$result.="</tr><tr><td>";
	
	$result.='<div stile="background-color:#00CED1" class="submit">';
	$result.="&nbsp;<input name='submeternovosparametros' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Alterar paramêtros' />";
	$result.="&nbsp;<a href=''><input name='cancela' class='loteca button-primary' type='button' " . SUBMITDISABLED . " value='Cancelar' /></a>";
	$result.="</div>";
	$result.='</td></tr></table>';
	$result.='</form>';
	$result.='</div>';
	return $result;
}

function submeter_parametros(){
	include_once 'loteca_db_admin.php';
	$result='';
	$limite = $_POST['limiteproc'];
	altera_parametros($limite);
	$result.="<div stile='background-color:#00CED1' class='wrap'>";
	$result.="<form method='POST'>";
	$result.='<table><tr><td valign="top">';
	$result.="Paramêtros alterados com sucesso.";
	$result.="</tr><tr><td>";
	
	$result.="<table>";
	$result.="<tr><td>Limite de processamento:&nbsp;</td><td><input name='limiteproc' type='text' maxlength=6 min=0 max=999999 VALUE='" . captura_parametros()->limite_proc . "' required /></td></tr>";
	$result.="</table>";

	$result.="</tr><tr><td>";
	
	$result.="<div class='submit'>";
	$result.="&nbsp;<input name='submeternovosparametros' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Alterar paramêtros' />";
	$result.="&nbsp;<a href=''><input name='cancela' class='loteca button-primary' type='button' " . SUBMITDISABLED . " value='Cancelar' /></a>";
	$result.="</div>";
	$result.='</td></tr></table>';
	$result.='</form>';
	$result.='</div>';
	return $result;
}

?>