<?php

include_once 'loteca_db_functions.php';

function loteca_ativar_hook() {
  // Vamos criar um opção para ser guardada na base-de-dados
  // e incluir um valor por defeito.
  update_option( 'loteca_ativa' , '1' );
  update_option( 'loteca_limite_desdobramento' , '50000' );
  update_option( 'loteca_limite_participante' , '50' );
  loteca_cria_pagina_estatistica();
}

function loteca_desativar_hook() {
  // Vamos criar um opção para ser guardada na base-de-dados
  // e incluir um valor por defeito.
  update_option( 'loteca_ativa' , '0' );
  loteca_remove_pagina_estatistica();
}

function loteca_cria_pagina_estatistica(){
$estatisticas_id = 'estatisticas-loteca-id';
$estatisticas_permalink = 'estatisticas-loteca';
$estatisticas_title = 'Loteca - Estatísticas';
	
    // the menu entry...
    delete_option($estatisticas_title);
    add_option($estatisticas_title, $estatisticas_title, '', 'yes');
    // the slug...
    delete_option($estatisticas_permalink);
    add_option($estatisticas_permalink, $estatisticas_permalink, '', 'yes');
    // the id...
    delete_option($estatisticas_id);
    add_option($estatisticas_id, '0', '', 'yes');
	
	$the_page = get_page_by_title( $estatisticas_title );
	
if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $estatisticas_title;
        $_p['post_name'] = $estatisticas_permalink;
        $_p['post_content'] = "[loteca-estatisticas]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); 

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );
    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
		$the_page->post_name = $estatisticas_permalink;
        $the_page_id = wp_update_post( $the_page );
    }
	
    delete_option($estatisticas_id);
    add_option($estatisticas_id, $the_page_id );
}

function loteca_remove_pagina_estatistica(){
$estatisticas_id = 'estatisticas-loteca-id';
$estatisticas_permalink = 'estatisticas-loteca';
$estatisticas_title = 'Loteca - Estatísticas';

   $the_page_id = get_option($estatisticas_id);
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option($estatisticas_title);
    delete_option($estatisticas_permalink);
    delete_option($estatisticas_id);
}

function loteca_options() {
	carrega_js();
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
/*	if(isset($_POST['novarodada'])){
		$result.=cadastrar_rodada();
		$listar=FALSE;
	}
	if(isset($_POST['novogrupo'])){
		$result.='<div class="wrap">';
		$result.="<form method='POST'>";
		$result.="<p>Sequencial do Grupo:&nbsp;<input name='seqgrupo' type='text' maxlength=3 min=0 max=999 required /></p>";
		$result.="<p>Nome do Grupo:&nbsp;<input name='nomegrupo' type='text' required /></p>";
		$result.="<p>Administrador do Grupo:&nbsp;<input name='admingrupo' type='text' required /></p>";
		$result.="<p>Grupo Ativo:&nbsp;<input name='grupoativo' type='checkbox' /></p>";
		$result.="<div class='submit'>";
		$result.="&nbsp;<input name='cadastragrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Cadastrar grupo' />";
		$result.="&nbsp;<a href=''><input name='cancela' class='loteca button-primary' type='button' " . SUBMITDISABLED . " value='Cancelar' /></a>";
		$result.="</div>";
		$result.='</form>';
		$result.='</div>';
		$listar=FALSE;
	}
*/
	if(isset($_POST['alterarparametro'])){
		$result.=alterar_parametros();
		$listar=FALSE;
	}
	if(isset($_POST['capturacef'])){
		include_once 'loteca_captura.php';
		$result.=loteca_captura();
		$listar=TRUE;
	}
	if(isset($_POST['submeternovosparametros'])){
		$result.=submeter_parametros();
		$listar=FALSE;
	}
	if(isset($_POST['ativargrupo'])&&isset($_POST['grupo'])){
		$result.=loteca_ativar_grupo($_POST['grupo']);
		$listar=TRUE;
	}
	if(isset($_POST['desativargrupo'])&&isset($_POST['grupo'])){
		$result.=loteca_desativar_grupo($_POST['grupo']);
		$listar=TRUE;
	}
	if($listar==TRUE){
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

function cadastrar_rodada(){
	$result="";
	$result.='<div class="wrap">';
	$result.="<form method='POST'>";
	$result.='<table><tr><td valign="top">';
	$prox_rodada=ultima_rodada()->rodada;
	$prox_rodada++;
	$inicio_palpite_ts=current_time('timestamp');
	$fim_palpite_ts=strtotime('next friday');
	$data_sorteio_ts=strtotime('next monday');
	$inicio_palpite=date('d-m-Y H:i:s', $inicio_palpite_ts);
	$fim_palpite=date('d-m-Y', $fim_palpite_ts);
	$data_sorteio=date('d-m-Y', $data_sorteio_ts);
	$result.="<table>";
	$result.="<tr><td>Sequencial da Rodada:&nbsp;</td><td><input name='seqrodada' type='text' maxlength=4 min=" . $prox_rodada . " max=9999 VALUE='" . $prox_rodada . "' required /></td></tr>";
	$result.="<tr><td>Início dos palpites:&nbsp;</td><td><input class='lotecadatahora' name='iniciopalpites' type='text' VALUE='" . $inicio_palpite . "' required /></td></tr>";
	$result.="<tr><td>Fim dos palpites:&nbsp;</td><td><input class='lotecadatahora' name='fimpalpites' VALUE='" . $fim_palpite . " 23:59:59' type='text' required /></td></tr>";
	$result.="<tr><td>Fechamento CEF:&nbsp;</td><td><input class='lotecadata' name='sorteiocef' type='text' VALUE='" . $data_sorteio . "' required /></td></tr>";
	$result.="</table>";
	$result.='</td><td>';
	$result.="<table>";
	$result.="<tr><td>JOGOS</td><td>CASA</td><td>VISITANTE</td><td>SAB</td><td>DOM</td><td>INÍCIO PREVISTO</td></tr>";
	for($x=1;$x<=14;$x++){
		$result.="<tr><td>JOGO " . $x . "</td>";
		$result.="<td><input name='casa" . $x . "' type='text' required/></td>";
		$result.="<td><input name='visitante" . $x . "' type='text' required/></td>";
		$result.="<td><input name='dia" . $x . "' type='radio' value='S' required/></td>";
		$result.="<td><input name='dia" . $x . "' type='radio' value='D'/></td>";
		$result.="<td><input class='lotecahora' name='inicio' type='text' VALUE='18:00' required /></td>";
		$result.="</tr>";
	}
	$result.="</table>";
	$result.="</tr><tr><td>";
	$result.="<div class='submit'>";
	$result.="&nbsp;<input name='cadastrarodada' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='Cadastrar rodada' />";
	$result.="&nbsp;<a href=''><input name='cancela' class='loteca button-primary' type='button' " . SUBMITDISABLED . " value='Cancelar' /></a>";
	$result.="</div>";
	$result.='</td></tr></table>';
	$result.='</form>';
	$result.='</div>';
	return $result;
}

function tab_grupos(){
	$result="";
	$grupos=captura_grupos();
	if($grupos){
		$result.="<TABLE>";
		$result.="<TR><TH COLSPAN=4>GRUPOS</TR><TR><TD COLSPAN=4></TR>";
		$result.="<TR>";
		$result.="<TH>";
		$result.="ID";
		$result.="</TH>";
		$result.="<TH>";
		$result.="Nome do grupo";
		$result.="</TH>";
		$result.="<TH>";
		$result.="Administrador";
		$result.="</TH>";
		$result.="<TH>";
		$result.="ATIVAR";
		$result.="</TH>";
		$result.="<TR><TD COLSPAN=4></TR></TR>";
		foreach ($grupos as $grupo){
			$result.="<TR>";
			$result.="<TD>&nbsp;";
			$result.=$grupo->id_grupo;
			$result.="</TD>";
			$result.="<TD>&nbsp;";
			$result.=$grupo->nm_grupo;
			$result.="</TD>";
			$result.="<TD>&nbsp;";
			$result.=$grupo->apelido;
			$result.="</TD>";
			$result.="<TD class='centralizado'>";
			if($grupo->id_ativo){
				$result.="<form method='POST'>";
				$result.="<input name=grupo type=hidden value=" . $grupo->id_grupo .">";
				$result.="&nbsp;<input name='desativargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DESATIVAR' />";
				$result.="</form>";
			}else{
				$result.="<form method='POST'>";
				$result.="<input name=grupo type=hidden value=" . $grupo->id_grupo .">";
				$result.="&nbsp;<input name='ativargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ATIVAR' />";
				$result.="</form>";
			}
			$result.="</TD>";
			$result.="</TR>";
		}
		$result.="</TABLE>";
	}
	return $result;
}

function tab_rodadas($limite=10, $inicio=0, $id_grupo=0, $usuario=0){
	$result="";
	$rodadas=captura_rodadas($limite , $inicio, $id_grupo , $usuario);
	if($rodadas){
		$result.="<TABLE>";
		$result.="<TR><TH COLSPAN=";
		if($id_grupo!=0){
			$novarodada=novarodada($id_grupo);
			if(($novarodada)&&($usuario==0)){
				$result.="3 >RODADAS</TH>";
				$result.="<TH class='semborda' COLSPAN=2>";
				$result.="<form method='POST'>";
				$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
				$result.="<input name=rodada type=hidden value=" . $novarodada .">";
				$result.="&nbsp;<input name='novarodada' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='HABILITAR PRÓXIMA RODADA(" . $novarodada . ")' />";
				$result.="</form>";
				$result.="</TH>";
			}else{
				$result.="5 >RODADAS</TH>";
			}
		}else{
			$novarodada=0;
			$result.="3 >RODADAS</TH>";
		}
		$result.="<TD class='centralizado'>";
		if($inicio!=0){
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value='" . $id_grupo ."'>";
			$posicao=$inicio - $limite;
			$result.="<input name=inicio type=hidden value='" . $posicao ."'>";
			if($usuario!=0){
				$result.="<input name=user type=hidden value=" . $usuario .">";
			}
			$result.="&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PÁGINA ANTERIOR' />";
			$result.="</form>";
		}
		$result.="</TD>";
		$result.="<TD class='centralizado'>";
		if(count($rodadas)>=$limite){
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value='" . $id_grupo ."'>";
			$posicao=$inicio + $limite;
			$result.="<input name=inicio type=hidden value='" . $posicao ."'>";
			if($usuario!=0){
				$result.="<input name=user type=hidden value=" . $usuario .">";
			}
			$result.="&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PRÓXIMA PÁGINA' />";
			$result.="</form>";
		}
		$result.="</TD>";

		$result.="</TR>";
		$result.="<TR>";
		$result.="<TH>";
		$result.="Rodada";
		$result.="</TH>";
		$result.="<TH>";
		$result.="Início dos palpites";
		$result.="</TH>";
		$result.="<TH>";
		$result.="Fim dos palpites";
		$result.="</TH>";
		$result.="<TH>";
		$result.="Data do sorteio";
		$result.="</TH>";
		$result.="<TH";
		if($id_grupo!=0){
			$result.=" COLSPAN=3";
		}
		$result.=">";
		$result.="Opções";
		$result.="</TH>";
		foreach ($rodadas as $rodada){
			$result.="<TR>";
			$result.="<TD>&nbsp;";
			$result.=$rodada->rodada;
			$result.="</TD>";
			$result.="<TD>&nbsp;";
			$result.=$rodada->dt_inicio_palpite;
			$result.="</TD>";
			$result.="<TD>&nbsp;";
			$result.=$rodada->dt_fim_palpite;
			$result.="</TD>";
			$result.="<TD>&nbsp;";
			$result.=$rodada->dt_sorteio;
			$result.="</TD>";
			if($id_grupo!=0){
				$result.="<TD>";
				if($rodada->qt_palpites >0){
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="<input name=rodada type=hidden value=" . $rodada->rodada .">";
					if($usuario==0){
						$result.="&nbsp;<input name='verpalpites' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />";
					}else{
						$result.="<input name=id_user type=hidden value=" . $usuario .">";
						$result.="<input name=admin type=hidden value=0>";
						$result.="&nbsp;<input name='detalharpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />";
					}
					$result.="</form>";
				}
				$result.="</TD>";
				$result.="<TD>";
				if($usuario==0){
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="<input name=rodada type=hidden value=" . $rodada->rodada .">";
					$result.="&nbsp;<input name='verdesdobramentos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DESDOBRAMENTOS' />";
					$result.="</form>";
				}
				$result.="</TD>";
			}
			$result.="<TD>";
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			if($usuario!=0){
				$result.="<input name=id_user type=hidden value=" . $usuario .">";
			}
			$result.="<input name=rodada type=hidden value=" . $rodada->rodada .">";
			$result.="&nbsp;<input name='verresultado' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RESULTADO' />";
			$result.="</form>";
			$result.="</TD>";


			$result.="</TR>";
		}
		$result.="</TABLE>";
	}
	return $result;
}

function alterar_parametros(){
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

function shortcode_loteca_link_cef() {
	$url='http://loterias.caixa.gov.br/wps/portal/loterias/landing/loteca/';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); 
	
	$a = curl_exec($ch); // $a will contain all headers
	
	$url = rtrim(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), '/'); // This is what you need, it will return you the last effective URL
	
	// Uncomment to see all headers
	/*
	echo "<pre>";
	print_r($a);echo"<br>";
	echo "</pre>";
	*/
	
	return $url; // Voila
}

function get_redirect_url($url){
    $redirect_url = null; 

    $url_parts = @parse_url($url);
    if (!$url_parts) return false;
    if (!isset($url_parts['host'])) return false; //can't process relative URLs
    if (!isset($url_parts['path'])) $url_parts['path'] = '/';

    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if (!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
    $request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
    $request .= "Connection: Close\r\n\r\n"; 
    fwrite($sock, $request);
    $response = '';
    while(!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);

    if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
        if ( substr($matches[1], 0, 1) == "/" )
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        else
            return trim($matches[1]);

    } else {
        return false;
    }

}

function shortcode_loteca($atts, $content = NULL){
	carrega_css();
	carrega_js();
	global $loteca_voltar_para, $loteca_pagina_atual;
	$loteca_pagina_atual = '';
	if (isset($_POST['voltarpara'])){
		$loteca_voltar_para = $_POST['voltarpara'];
	}else{
		$loteca_voltar_para = '';
	}
	$result="";
	if ( !is_user_logged_in() ) {
		$result.="<P>OLÁ, BEM VINDO AO BOLÃO DA LOTECA!</P>";
		$result.="<P>FAÇA SEU <a href='".wp_login_url()."'>LOGIN</a> E ACESSE OS BOLÕES QUE VOCÊ ESTÁ PARTICIPANDO E/OU ADMINISTRA.</P>";
		$result.="<P>PARA CRIAR UM NOVO BOLÃO TAMBÉM É NECESSÁRIO FAZER O LOGIN ANTES.</P>";
		$result.=msg_rodape();
		return $result;
	}
	if(isset($_POST['quero_participar'])){
		$id_grupo=$_POST['grupo'];
		$loteca_pagina_atual = 'quero_participar';
		return quero_participar($id_grupo) . msg_rodape();
	}
	if(isset($_POST['SOLICITAR'])){
		$loteca_pagina_atual = 'SOLICITAR';
		return listargruposabertos() . msg_rodape();
	}
	if(isset($_POST['CRIAR'])){
		$loteca_pagina_atual = 'CRIAR';
		$result.='<P>CLICOU EM CRIAR</P>';
	}
	if(isset($_POST['listarparticipantes'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'listarparticipantes';
		return acessagrupo($id_grupo) . listarparticipantes($id_grupo) . msg_rodape();
	}
	if(isset($_POST['registrarpalpite'])){
		if(isset($_POST['grupo'])&&isset($_POST['rodada'])&&isset($_POST['user'])&&
		   loteca_acessa_grupo($_POST['grupo'])&&
		   (isset($_POST['1-1']) ||isset($_POST['1-X']) ||isset($_POST['1-2']) )&&
		   (isset($_POST['2-1']) ||isset($_POST['2-X']) ||isset($_POST['2-2']) )&&
		   (isset($_POST['3-1']) ||isset($_POST['3-X']) ||isset($_POST['3-2']) )&&
		   (isset($_POST['4-1']) ||isset($_POST['4-X']) ||isset($_POST['4-2']) )&&
		   (isset($_POST['5-1']) ||isset($_POST['5-X']) ||isset($_POST['5-2']) )&&
		   (isset($_POST['6-1']) ||isset($_POST['6-X']) ||isset($_POST['6-2']) )&&
		   (isset($_POST['7-1']) ||isset($_POST['7-X']) ||isset($_POST['7-2']) )&&
		   (isset($_POST['8-1']) ||isset($_POST['8-X']) ||isset($_POST['8-2']) )&&
		   (isset($_POST['9-1']) ||isset($_POST['9-X']) ||isset($_POST['9-2']) )&&
		   (isset($_POST['10-1'])||isset($_POST['10-X'])||isset($_POST['10-2']))&&
		   (isset($_POST['11-1'])||isset($_POST['11-X'])||isset($_POST['11-2']))&&
		   (isset($_POST['12-1'])||isset($_POST['12-X'])||isset($_POST['12-2']))&&
		   (isset($_POST['13-1'])||isset($_POST['13-X'])||isset($_POST['13-2']))&&
		   (isset($_POST['14-1'])||isset($_POST['14-X'])||isset($_POST['14-2']))){
			$loteca_pagina_atual = 'registrarpalpite';
			$palpites=array();
			for($seq=1;$seq<=14;$seq++){
				if(isset($_POST[$seq.'-1'])){$palpites[$seq.'-1'] = $_POST[$seq.'-1'];}
				if(isset($_POST[$seq.'-X'])){$palpites[$seq.'-X'] = $_POST[$seq.'-X'];}
				if(isset($_POST[$seq.'-2'])){$palpites[$seq.'-2'] = $_POST[$seq.'-2'];}
			}
			return acessagrupo($_POST['grupo']) . loteca_registrar_palpite($_POST['grupo'],$_POST['rodada'],$_POST['user'],$palpites) . msg_rodape();
		}else{
			return "OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES. (" . $loteca_pagina_atual . ")";
		}
	}
	if(isset($_POST['extrato'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'extrato';
		return acessagrupo($id_grupo) . extrato($id_grupo) . msg_rodape();
	}
	if(isset($_POST['palpitar'])){
		$id_grupo=$_POST['grupo'];
		$loteca_pagina_atual = 'palpitar';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES. (" . $loteca_pagina_atual .")";
			return $result;
		}
		return acessagrupo($id_grupo) . palpitar($id_grupo) . msg_rodape();
	}
	if(isset($_POST['acessargrupo'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'acessargrupo';
//		return acessagrupo($id_grupo) .  acessagrupo_2($id_grupo) . msg_rodape();
		return acessagrupo($id_grupo) . msg_rodape();
	}

	if(isset($_POST['admingrupo'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'admingrupo';
		return admingrupo($id_grupo) . msg_rodape();
	}
	if(isset($_POST['adminparticipantes'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'adminparticipantes';
		return admingrupo($id_grupo) . adminparticipantes($id_grupo) . msg_rodape();
	}
	if(isset($_POST['incluirgasto'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluirgasto';
		return admingrupo($id_grupo) . incluirgasto($id_grupo) . msg_rodape();
	}
	if(isset($_POST['incluircredito'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluircredito';
		return admingrupo($id_grupo) . incluircredito($id_grupo) . msg_rodape();
	}
	if(isset($_POST['confirmarcredito'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'confirmarcredito';
		return admingrupo($id_grupo) . confirmarcredito($id_grupo) . msg_rodape();
	}
	if(isset($_POST['incluirpremio'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluirpremio';
		return admingrupo($id_grupo) . incluirpremio($id_grupo) . msg_rodape();
	}
	if(isset($_POST['resgate'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'resgate';
		return admingrupo($id_grupo) . incluirresgate($id_grupo) . msg_rodape();
	}
	if(isset($_POST['desativarparticipante'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'desativarparticipante';
		return admingrupo($id_grupo) . desativarparticipante($id_grupo) . msg_rodape();
	}
	if(isset($_POST['ativarparticipante'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'ativarparticipante';
		return admingrupo($id_grupo) . ativarparticipante($id_grupo) . msg_rodape();
	}
	if(isset($_POST['alterarparametros'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'alterarparametros';
		return admingrupo($id_grupo) . alterarparametros($id_grupo) . msg_rodape();
	}
	if(isset($_POST['verrodadas'])){
		$id_grupo=$_POST['grupo'];
		if( (!loteca_admin_grupo($id_grupo)) && (!loteca_acessa_grupo($id_grupo)) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		if(isset($_POST['inicio'])){
			$inicio=$_POST['inicio'];
		}else{
			$inicio=0;
		}
		$loteca_pagina_atual = 'verrodadas';
		if(isset($_POST['user'])){
			return acessagrupo($id_grupo) . verrodadas($id_grupo,$inicio,$_POST['user']) . msg_rodape();
		}else{
			return admingrupo($id_grupo) . verrodadas($id_grupo,$inicio,0) . msg_rodape();
		}
	}
	if(isset($_POST['verpalpites'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'verpalpites';
		return admingrupo($id_grupo) . verpalpites($id_grupo,$_POST['rodada']) . msg_rodape();
	}
	if(isset($_POST['detalharpalpite'])){
		$id_grupo=$_POST['grupo'];
		if(isset($_POST['admin'])){
			$admin=$_POST['admin'];
		}else{
			$admin=1;
		}
		$loteca_pagina_atual = 'detalharpalpite';
		if($admin==1){
			if( !loteca_admin_grupo($id_grupo) ){
				$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
				return $result;
			}
			return admingrupo($id_grupo) . detalharpalpite($id_grupo,$_POST['rodada'],$_POST['id_user']) . msg_rodape();
		}else{
			return acessagrupo($id_grupo) . detalharpalpite($id_grupo,$_POST['rodada'],$_POST['id_user']) . msg_rodape();
		}
	}
	if(isset($_POST['verresultado'])){
		$id_grupo=$_POST['grupo'];
		if(  (!loteca_admin_grupo($id_grupo)) && (!loteca_acessa_grupo($id_grupo))  ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'verresultado';
		if(isset($_POST['id_user'])){
			return acessagrupo($id_grupo) . verresultado($id_grupo,$_POST['rodada']) . msg_rodape();
		}else{
			return admingrupo($id_grupo) . verresultado($id_grupo,$_POST['rodada']) . msg_rodape();
		}
	}
	if(isset($_POST['novarodada'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'novarodada';
		return admingrupo($id_grupo) . habilitarrodada($id_grupo) . msg_rodape();
	}

	$boloes_admin=captura_boloes(1);
	$boloes_usu=captura_boloes(0);
	if(count($boloes_admin)||count($boloes_usu)){
		if(count($boloes_admin)){
			if((count($boloes_usu)==0)&&(count($boloes_admin)==1)){
				foreach ($boloes_admin as $bolao){
					$result.=admingrupo($bolao->id_grupo);
				}
			}else{
				$result.=tab_grupos_admin($boloes_admin);
			}
		}
		if(count($boloes_usu)){
			if((count($boloes_admin)==0)&&(count($boloes_usu)==1)){
				foreach ($boloes_usu as $bolao){
//					$result.=acessagrupo($bolao->id_grupo) . acessagrupo_2($bolao->id_grupo);
					$result.=acessagrupo($bolao->id_grupo);
				}
			}else{
				$result.=tab_grupos_usu($boloes_usu);
			}
		}
	}else{
		$result.="<P>VOCÊ AINDA NÃO PARTICIPA DE NENHUM BOLÃO!</P>";
		$result.="<P>SOLICITE UM CONVITE DE UM DOS ADMNISTRADORES DOS BOLÕES OU CRIE O SEU PRÓPRIO BOLÃO</P>";
		$result.="<form method='post' style='display:inline'><INPUT TYPE=submit NAME='SOLICITAR' VALUE='SOLICITAR' /></form> ";
		$result.="<form method='post' style='display:inline'><INPUT TYPE=submit NAME='CRIAR' VALUE='CRIAR' /></form>";
	}
	$result.=msg_rodape();
	return $result;
}

function shortcode_loteca_estatisticas($atts, $content = NULL){
	carrega_css();
	carrega_js();
	$time1=$_REQUEST['time1'];
	$time2=$_REQUEST['time2'];
	$ano=date("Y");
	$ano_ant=$ano -1;
	$estatisticas=carrega_estatisticas($time1,$time2);
	$result="";
	$result.="<TABLE><TR class='centralizado'><TH>?</TH><TH>" . $ano . "</TH><TH>" . $ano_ant . "</TH><TH>ANTES DE " . $ano_ant . "</TH></TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time1 . " X " . $time2;
	$result.="</TD>";
	$linha='A';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time2 . " X " . $time1;
	$result.="</TD>";
	$linha='B';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time1;
	$result.="</TD>";
	$linha='G';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time2;
	$result.="</TD>";
	$linha='H';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time1 . " X ?";
	$result.="</TD>";
	$linha='C';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.="? X " . $time1;
	$result.="</TD>";
	$linha='D';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.=$time2 . " X ?";
	$result.="</TD>";
	$linha='E';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR class='centralizado'>";
	$result.="<TD>";
	$result.="? X " . $time2;
	$result.="</TD>";
	$linha='F';
	$result.="<TD>";
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD>";
	$result.="<TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / ";
		$result.="V: " . $estatisticas[$linha][0]['VTIME1'] . " / ";
		$result.="E: " . $estatisticas[$linha][0]['EMPATE'] . " / ";
		$result.="D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	$result.="TIME 1: " . $_REQUEST['time1'];
	$result.=" / TIME 2: " . $_REQUEST['time2'];
	$result.=msg_rodape();
	return $result;
}

function msg_rodape(){
	return "<P><H3>ATENÇÃO: ESTE SITE NÃO EFETUA JOGOS DA LOTECA! <BR>NOSSO OBJETIVO É AJUDAR A ADMINISTRAR GRUPOS DE BOLÃO.</H3></P>";
}

function verrodadas($id_grupo,$inicio,$usuario){
	return tab_rodadas(10,$inicio,$id_grupo,$usuario);
}

function admingrupo($id_grupo){
	$result="";
	
	$resultado_pendente=resultado_pendente();
	$programacao_pendente=programacao_pendente();
	if( $resultado_pendente || $programacao_pendente ){
		$result.='
<script type="text/javascript">
(function($){ jQuery(document).ready(function($){ 
$("#loteca-msg").click(function(event) {
	var acao = $(this).attr("value");
	$("#loteca-msg").load("' . plugin_dir_url( __FILE__ ).  'loteca_processa_captura.php",{acc:acao},ready());
});
 }); })(jQuery);
function ready(){
//	alert("Ajax terminou com sucesso.");
}
</script>
	';
	$result.="<DIV id='loteca-msg'>";
	if($resultado_pendente) {
		$result.=" | RESULTADO PENDENTE: " . $resultado_pendente;
	}
	if($programacao_pendente) {
		$result.=" | PROGRAMACAO PENDENTE: " . $programacao_pendente;
	}
	$result.="</DIV>";
	}
	
	$novarodada=novarodada($id_grupo);
	$result.="<TABLE>";
	$result.=tab_dadosgrupo($id_grupo,1,FALSE);
	$result.=tab_dadosrodada(0,1,FALSE);
	$result.=tab_dadosgruporodada($id_grupo,1,FALSE);
	if($novarodada){
		$result.="<TR><TD class='vermelho' COLSPAN=3>ESTÁ DISPONÍVEL A PRÓXIMA RODADA - CLIQUE EM RODADAS E EM HABILITAR PRÓXIMA RODADA.</TD></TR>";
	}
	$result.="</TABLE>";
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TD>";
	$result.="GERAL";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPANTES' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='alterarparametros' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIGURAR' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RODADAS' />";
	$result.="</form>";
	$result.="</TD>";
	$boloes_admin=captura_boloes(1);
	$boloes_usu=captura_boloes(0);

	if((count($boloes_admin))+(count($boloes_usu))>1){
		$result.="<TD>";
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name='INICIO' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INICIO' />";
		$result.="</form>";
		$result.="</TD>";
	}
	$result.="</TR>";
	$result.="</TABLE>";
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TD>";
	$result.="RODADA ATUAL";
	$result.="</TD>";
	
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='verpalpites' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='verresultado' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RESULTADO' />";
	$result.="</form>";
	$result.="</TD>";
	
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='incluirgasto' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INCLUIR GASTO' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	global $loteca_pagina_atual;
	$result.="<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />";
	error_log('botao criado com loteca_pagina_atual = "' . $loteca_pagina_atual . '"');
	$result.="&nbsp;<input name='incluirpremio' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INCLUIR PRÊMIO' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	return $result;
}

function habilitarrodada($id_grupo){
	$result="";
	if(isset($_POST['rodada'])){
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		if(db_habilitarrodada($id_grupo,$_POST['rodada'])){
			$result.="<H3>RODADA " . $_POST['rodada'] . " HABILITADA PARA O SEU GRUPO.</H3>";
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE(2).</H3>";
		}
		$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}else{
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE(1).</H3>";
	}
	return $result;
}

function alterarparametros($id_grupo){
	$result="";
	if(isset($_POST['confirma'])){
		$result.=confirma_alterarparametros();
	}else{
		$result.=recebe_alterarparametos();
	}
	return $result;
}

function confirma_alterarparametros(){
	$result="";
	return $result;
}

function recebe_alterarparametos(){
	$result="";
	$result.="VALOR MÁXIMO:";
	$result.="<BR>";
	$result.="VALOR MÍNIMO:";
	$result.="<BR>";
	$result.="QUANTO GASTAR:";
	$result.="<BR>";
	$result.="ZEBRAS POR VOLANTE(MÍN):";
	$result.="<BR>";
	$result.="ZEBRAS POR VOLANTE(MÁX):";
	$result.="<BR>";
	$result.="AMPLIA ZEBRAS:";
	$result.="<BR>";
	$result.="VOLANTE COM COTA:";
	$result.="<BR>";
	$result.="VALOR MÍNIMO POR COTA (CAIXA):";
	$result.="<BR>";
	return $result;
}

function incluirpremio($id_grupo){
	$result="";
	if(!isset($_POST['valorpremio'])){
		$result.="<form method='POST'>";
		$rodada=rodada_atual_grupo($id_grupo);
		$valor=valorpremio($id_grupo,$rodada);
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=rodada type=hidden value=" . $rodada .">";
		$result.="RODADA ATUAL: " . $rodada;
		$result.="&nbsp;<input name=valorpremio type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor .">";
		$result.="&nbsp;<input name='incluirpremio' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR PRÊMIO' />";
		global $loteca_voltar_para;
		$result.="&nbsp;<input name='" . $loteca_voltar_para . "' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		error_log('botao criado com loteca_voltar_para = "' . $loteca_voltar_para . '"');
		$result.="</form>";
		$result.="<BR>Se desejar alterar os premios de rodadas anteriores selecione o botão 'RODADAS'";
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		if(isset($_POST['rodada'])){
			if(db_inclui_premio($id_grupo,$_POST['rodada'],$_POST['valorpremio'])){
				$result.="<H3>PRÊMIO INCLUÍDO COM SUCESSO</H3>";
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function incluirgasto($id_grupo){
	$result="";
	if(!isset($_POST['valorgasto'])){
		$result.="<form method='POST'>";
		$rodada=rodada_atual_grupo($id_grupo);
		$valor=valorgasto($id_grupo,$rodada);
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=rodada type=hidden value=" . $rodada .">";
		$result.="RODADA ATUAL: " . $rodada;
		$result.="&nbsp;<input name=valorgasto type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor .">";
		$result.="&nbsp;<input name='incluirgasto' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR GASTO' />";
		$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
		$result.="<BR>Se desejar alterar os gastos de rodadas anteriores selecione o botão 'RODADAS'";
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		if(isset($_POST['rodada'])){
			if(db_inclui_gasto($id_grupo,$_POST['rodada'],$_POST['valorgasto'])){
				$result.="<H3>GASTO INCLUÍDO COM SUCESSO</H3>";
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function confirmarcredito($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		if(db_confirma_credito($id_grupo,$id_user,$rodada)){
			$result.="<H3>CREDITO CONFIRMADO COM SUCESSO</H3>";
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function incluirresgate($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="<p>" . tx_user($id_user,$id_grupo);
		if(!isset($_POST['valorresgate'])){
			$valor=valorresgate($id_grupo,$rodada,$id_user);
			$result.="<script type='text/javascript'>";
			$result.=" function tx_loteca_valorresgate_mudou(){";
			$result.="  if(" . $valor . "!=document.getElementById('tx_loteca_valor_resgate').value) {";
			$result.="    document.getElementById('btn_loteca_registra_resgate').disabled = false;";
			$result.="  }else{";
			$result.="    document.getElementById('btn_loteca_registra_resgate').disabled = true;";
			$result.="  }";
			$result.="}";
			$result.="</script>";
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$result.="<input name=id_user type=hidden value=" . $id_user .">";
			$result.="<input name=rodada type=hidden value=" . $rodada .">";
			$result.="<input id='tx_loteca_valor_resgate' name=valorresgate type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor ." onchange='tx_loteca_valorresgate_mudou()'>";
			$result.="&nbsp;<input id='btn_loteca_registra_resgate' name='resgate' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='GRAVAR RESGATE' disabled/>";
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
		}else{
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$valor=valorresgate($id_grupo,$rodada,$id_user);
			if(db_inclui_resgate($id_grupo,$id_user,$rodada,$_POST['valorresgate'])){
				if($valor!=0){
					$result.="<H3>RESGATE ALTERADO COM SUCESSO</H3>";
				}else{
					$result.="<H3>RESGATE INCLUÍDO COM SUCESSO</H3>";
				}
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
		}
		$result.="</p>";
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function desativarparticipante($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		if(!isset($_POST['confirmadesativarparticipante'])){
			$result.="<p>" . tx_user($id_user,$id_grupo) . "</p>";
			$result.="<H3>AO CONFIRMAR A DESATIVAÇÃO DO PARTICIPANTE, ELE NÃO FARÁ PARTE DO PROCESSAMENTO DESTA RODADA E NÃO SERÁ INCLUÍDO NAS PRÓXIMAS ATÉ QUE SEJA ATIVADO NOVAMENTE</H3>";
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$result.="<input name=id_user type=hidden value=" . $id_user .">";
			$result.="<input name=rodada type=hidden value=" . $rodada .">";
			$result.="<input name=confirmadesativarparticipante type=hidden value=TRUE>";
			$result.="&nbsp;<input name='desativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA DESATIVAÇÃO'/>";
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
		}else{
			if($_POST['confirmadesativarparticipante']==TRUE){
				if(db_desativar_participante($id_grupo,$rodada,$id_user)){
					$result.="<p>" . tx_user($id_user,$id_grupo) . "</p>";
					$result.="<H3>PARTICIPANTE DESATIVADO COM SUCESSO.</H3>";
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
					$result.="</form>";
				}else{
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
					$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
					$result.="</form>";
				}
			}else{
				$result.="<form method='POST'>";
				$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
				$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
				$result.="</form>";
			}
		}
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function ativarparticipante($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		if(!isset($_POST['confirmaativarparticipante'])){
			$result.="<p>" . tx_user($id_user,$id_grupo) . "</p>";
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$result.="<input name=id_user type=hidden value=" . $id_user .">";
			$result.="<input name=rodada type=hidden value=" . $rodada .">";
			$result.="<input name=confirmaativarparticipante type=hidden value=TRUE>";
			$result.="&nbsp;<input name='ativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA ATIVAÇÃO'/>";
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
		}else{
			if($_POST['confirmaativarparticipante']==TRUE){
				if(db_ativar_participante($id_grupo,$rodada,$id_user)){
					$result.="<p>" . tx_user($id_user,$id_grupo) . "</p>";
					$result.="<H3>PARTICIPANTE ATIVADO COM SUCESSO.</H3>";
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
					$result.="</form>";
				}else{
					$result.="<form method='POST'>";
					$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
					$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
					$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
					$result.="</form>";
				}
			}else{
				$result.="<form method='POST'>";
				$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
				$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
				$result.="</form>";
			}
		}
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		$result.="<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function incluircredito($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="<p>" . tx_user($id_user,$id_grupo);
		if(!isset($_POST['valorcredito'])){
			$valor=valorcredito($id_grupo,$rodada,$id_user);
			$result.="<script type='text/javascript'>";
			$result.=" function tx_loteca_valorcredito_mudou(){";
			$result.="  if(" . $valor . "!=document.getElementById('tx_loteca_valor_credito').value) {";
			if($valor!=0){
				$result.="    document.getElementById('btn_loteca_confirma_credito').disabled = true;";
			}
			$result.="    document.getElementById('btn_loteca_registra_credito').disabled = false;";
			$result.="  }else{";
			if($valor!=0){
				$result.="    document.getElementById('btn_loteca_confirma_credito').disabled = false;";
			}
			$result.="    document.getElementById('btn_loteca_registra_credito').disabled = true;";
			$result.="  }";
			$result.="}";
			$result.="</script>";
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$result.="<input name=id_user type=hidden value=" . $id_user .">";
			$result.="<input name=rodada type=hidden value=" . $rodada .">";
			$result.="<input id='tx_loteca_valor_credito' name=valorcredito type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor ." onchange='tx_loteca_valorcredito_mudou()'>";
			if($valor!=0){
				$result.="&nbsp;<input id='btn_loteca_registra_credito' name='incluircredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ALTERAR CREDITO' disabled/>";
				$result.="&nbsp;<input id='btn_loteca_confirma_credito' name='confirmarcredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMAR CREDITO' />";
			}else{
				$result.="&nbsp;<input id='btn_loteca_registra_credito' name='incluircredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INCLUIR CREDITO' disabled/>";
			}
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
			
		}else{
			$result.="<form method='POST'>";
			$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
			$valor=valorcredito($id_grupo,$rodada,$id_user);
			if(db_inclui_credito($id_grupo,$id_user,$rodada,$_POST['valorcredito'])){
				if($valor!=0){
					$result.="<H3>CREDITO ALTERADO COM SUCESSO</H3>";
				}else{
					$result.="<H3>CREDITO INCLUÍDO COM SUCESSO</H3>";
				}
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
			$result.="&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			$result.="</form>";
		}
		$result.="</p>";
	}else{
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
		$result.="</form>";
	}
	return $result;
}

function verresultado($id_grupo,$rodada = 0){
	$result="";
	if(!$rodada){
		$rodada=rodada_atual_grupo($id_grupo);
	}
	$result.=tab_admin_resultado($id_grupo,$rodada);
	return $result;
}

function tab_admin_resultado($id_grupo,$rodada){
	$result.="<TABLE class='minimo'>";
	$palpite=captura_resultado($rodada,$id_grupo);
	$result.="<TR>";
	$result.="<TH class='centralizado' COLSPAN=6>";
	$result.="RODADA : " . $rodada;
	$result.="</TH>";
	$result.="</TR>";
	$result.="<TR>";
	$result.="<TH class='direita'>";
	$result.="</TH>";
	$result.="<TH class='direita'>";
	$result.="TIME DA CASA";
	$result.="</TH>";
	$result.="<TH>";
	$result.="1";
	$result.="</TH>";
	$result.="<TH>";
	$result.="X";
	$result.="</TH>";
	$result.="<TH>";
	$result.="2";
	$result.="</TH>";
	$result.="<TH class='esquerda'>";
	$result.="VISITANTE";
	$result.="</TH>";
	$result.="</TR>";
	foreach($palpite as $jogada){
		$result.="<TR>";
		$result.="<TD class='direita'>";
		$result.=$jogada->seq;
		$result.="</TD>";
		$result.="<TD class='direita'>";
		$result.=$jogada->time1;
		$result.="</TD>";
		$result.="<TD class='centralizado";
		$result.="'><DIV class='";
		if($jogada->vtime1){
			$result.=" fundopreto";
		}
		$result.="'>";
		if($jogada->qttime1){
			$result.=$jogada->qttime1;
			$result.="&nbsp;/&nbsp;" . $jogada->peso1;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="</DIV>";
		$result.="</TD>";

		$result.="<TD class='centralizado";
		$result.="'><DIV class='";
		if($jogada->empate){
			$result.=" fundopreto";
		}
		$result.="'>";
		if($jogada->qtempate){
			$result.=$jogada->qtempate;
			$result.="&nbsp;/&nbsp;";
			$result.=$jogada->pesoe;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="</DIV>";
		$result.="</TD>";
		$result.="<TD class='centralizado";
		$result.="'><DIV class='";
		if($jogada->vtime2){
			$result.=" fundopreto";
		}
		$result.="'>";
		if($jogada->qttime2){
			$result.=$jogada->qttime2;
			$result.="&nbsp;/&nbsp;" .$jogada->peso2;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="</DIV>";
		$result.="</TD>";
		$result.="<TD class='esquerda'>";
		$result.=$jogada->time2;
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="</TABLE>";
	return $result;
}

function verpalpites($id_grupo,$rodada = 0){
	$result="";
	if(!$rodada){
		$rodada=rodada_atual_grupo($id_grupo);
	}
	$result.=tab_admin_palpites($id_grupo,$rodada);
	return $result;
}

function tab_admin_palpites($id_grupo,$rodada){
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="ID";
	$result.="</TH>";
	$result.="<TH>";
	$result.="APELIDO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="";
	$result.="</TH>";
	$result.="</TR>";
	$palpites=captura_palpites_rodada($id_grupo,$rodada);
	foreach($palpites as $palpite){
		$result.="<TR>";
		$result.="<TD>";
		$result.=$palpite->id_user;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$palpite->apelido;
		$result.="</TD>";
		$result.="<TD>";
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=id_user type=hidden value=" . $palpite->id_user .">";
		$result.="<input name=rodada type=hidden value=" . $rodada .">";
		$result.="<input name=admin type=hidden value=1>";
		$result.="&nbsp;<input name='detalharpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DETALHAR' />";
		$result.="</form>";
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="</TABLE>";
	return $result;
}

function detalharpalpite($id_grupo,$rodada,$id_user=0){
	if($id_user==0){
		$id_user=get_current_user_id();
	}
	$result="";
	$result.=tab_detalhepalpite($id_grupo,$rodada,$id_user);
	return $result;
}

function tab_detalhepalpite($id_grupo,$rodada,$id_user){
	$result.="<TABLE class='minimo'>";
	$palpite=captura_palpite($id_grupo,$rodada,$id_user);
	$result.="<TR>";
	$result.="<TH COLSPAN=6>";
	$result.="RODADA : " . $rodada;
	if(get_current_user_id()!=$id_user){
		$result.=" - APELIDO : " . $palpite[0]->apelido;
	}
	$result.="</TH>";
	$result.="</TR>";
	$result.="<TR>";
	$result.="<TH class='direita'>";
	$result.="</TH>";
	$result.="<TH class='direita'>";
	$result.="TIME DA CASA";
	$result.="</TH>";
	$result.="<TH>";
	$result.="1";
	$result.="</TH>";
	$result.="<TH>";
	$result.="X";
	$result.="</TH>";
	$result.="<TH>";
	$result.="2";
	$result.="</TH>";
	$result.="<TH class='esquerda'>";
	$result.="VISITANTE";
	$result.="</TH>";
	$result.="</TR>";
	$qt_acertos=0;
	foreach($palpite as $jogada){
		if((($jogada->rtime1)&&($jogada->vtime1==$jogada->rtime1))||(($jogada->rtime2)&&($jogada->vtime2==$jogada->rtime2))||(($jogada->rempate)&&($jogada->empate==$jogada->rempate))){
			$qt_acertos++;
		}
		$result.="<TR>";
		$result.="<TD class='direita'>";
		$result.=$jogada->seq;
		$result.="</TD>";
		$result.="<TD class='direita'>";
		$result.=$jogada->time1;
		$result.="</TD>";
		$result.="<TD class='centralizado";
		if($jogada->rtime1){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->vtime1){
			$result.="1";
		}
		$result.="</TD>";
		$result.="<TD class='centralizado";
		if($jogada->rempate){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->empate){
			$result.="X";
		}
		$result.="</TD>";
		$result.="<TD class='centralizado";
		if($jogada->rtime2){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->vtime2){
			$result.="2";
		}
		$result.="</TD>";
		$result.="<TD class='esquerda'>";
		$result.=$jogada->time2;
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="<TR>";
	$result.="<TH COLSPAN=6>";
	$result.="ACERTOS : " . $qt_acertos;
	$result.="</TH>";
	$result.="</TR>";
	$result.="</TABLE>";
	return $result;
}

function adminparticipantes($id_grupo){
	$result="";
	$result.=tab_admin_participantes($id_grupo);
	return $result;
}

function tab_admin_participantes($id_grupo){
	$participantes=captura_participantes($id_grupo);
	$situacao=99;
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="ID";
	$result.="</TH>";
	$result.="<TH>";
	$result.="APELIDO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="SALDO ANT";
	$result.="</TH>";
	$result.="<TH>";
	$result.="GASTO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="CRÉDITO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="PRÊMIO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="RESGATE";
	$result.="</TH>";
	$result.="<TH>";
	$result.="SALDO ***";
	$result.="</TH>";
	$result.="<TH COLSPAN=3>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='incluirparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='NOVO PARTICIPANTE' />";
	$result.="</form>";
	$result.="</TH>";
	$result.="</TR>";
	foreach($participantes as $participante){
		if($situacao!=$participante->id_ativo){
			$situacao=$participante->id_ativo;
			$result.="<TR><TH COLSPAN=8>";
			if($situacao==1){
				$result.="ATIVOS";
			}else{
				$result.="INATIVOS";
			}
			$result.="</TH>";
			$result.="<TH COLSPAN=3>OPÇÕES</TH></TR>";
		}
		$result.="<TR>";
		$result.="<TD>";
		$result.=$participante->id_user;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$participante->apelido;
		$result.=($participante->participa==1)?"":"**";
		$result.="</TD>";
		$result.="<TD";
		if($participante->vl_saldo_ant<0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$participante->vl_saldo_ant;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$participante->vl_gasto;
		$result.="</TD>";
		$result.="<TD";
		if($participante->vl_credito>0){
			$result.=" class='verde'";
		}
		$result.=">";
		$result.=$participante->vl_credito;
		$result.="</TD>";
		$result.="<TD";
		if($participante->vl_premio>0){
			$result.=" class='verde'";
		}
		$result.=">";
		$result.=$participante->vl_premio;
		$result.="</TD>";
		$result.="<TD";
		if($participante->vl_resgate>0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$participante->vl_resgate;
		$result.="</TD>";
		$result.="<TD";
		if($participante->vl_saldo<0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$participante->vl_saldo . "/" . $participante->saldo;
		$result.="</TD>";
		$result.="<TD>";
		
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=id_user type=hidden value=" . $participante->id_user .">";
		$result.="<input name=rodada type=hidden value=" . $participante->rodada .">";
		if($participante->ind_credito_processado==0){
			$result.="&nbsp;<input name='incluircredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CREDITO' />";
		}else{
			$result.="CONFIRMADO";
		}
		$result.="</form>";

		$result.="</TD>";
		$result.="<TD>";
		
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=id_user type=hidden value=" . $participante->id_user .">";
		$result.="<input name=rodada type=hidden value=" . $participante->rodada .">";
			if($participante->vl_saldo>0){
				$result.="&nbsp;<input name='resgate' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RESGATE' />";
			}else{
				if($participante->vl_saldo==0){
					$result.="ZERADO";
				}else{
					$result.="NEGATIVO";
				}
			}
		$result.="</form>";

		$result.="</TD>";
		$result.="<TD>";
		
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="<input name=id_user type=hidden value=" . $participante->id_user .">";
		$result.="<input name=rodada type=hidden value=" . $participante->rodada .">";
		if($participante->id_ativo==0){
			$result.="&nbsp;<input name='ativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ATIVAR' />";
		}else{
			$result.="&nbsp;<input name='desativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DESATIVAR' />";
		}
		$result.="</form>";

		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="<TR>";
	$result.="<TD COLSPAN=11>";
//	$result.="SALDO *: SALDO PARA CONCILIAÇÃO<BR>";
	$result.="**: INFORMA SE ESTAVA PARTICIPANDO DA ÚLTIMA RODADA<BR>";
	$result.="SALDO ***: CALCULO CONSIDERANDO A MOVIMENTAÇÃO DE VALORES DA ÚLTIMA RODADA/SALDO PARA CONCILIAÇÃO<BR>";
	$result.="INC CRED: INCLUIR CREDITO RECEBIDO PARA PARTICIPAÇÃO NO BOLÃO<BR>";
	$result.="CONF CRED: REGISTRA QUE O CRÉDITO FOI CONFIRMADO<BR>";
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	return $result;
}

function acessagrupo($id_grupo){
//	carrega_js();
	$result="";
	$result.=tab_dadosgrupo($id_grupo,0,TRUE);
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='listarparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPANTES' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='palpitar' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITAR' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="&nbsp;<input name='extrato' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='EXTRATO' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="<input name=user type=hidden value=" . get_current_user_id() .">";
	$result.="&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RODADAS' />";
	$result.="</form>";
	$result.="</TD>";

	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name='SOLICITAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPAR DE OUTROS GRUPOS' />";
	$result.="</form>";
	$result.="</TD>";

	$boloes_admin=captura_boloes(1);
	$boloes_usu=captura_boloes(0);

	if((count($boloes_admin))+(count($boloes_usu))>1){
		$result.="<TD>";
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		$result.="&nbsp;<input name='INICIO' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INICIO' />";
		$result.="</form>";
		$result.="</TD>";
	}

	$result.="</TR>";
	$result.="</TABLE>";
	return $result;
}

function acessagrupo_2($id_grupo){
	$result="";
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name='SOLICITAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPAR DE OUTROS GRUPOS' />";
	$result.="</form>";
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	return $result;
}

function extrato($id_grupo){
 $result='';
 $extrato=carrega_extrato($id_grupo);
 if($extrato){
	$situacao=99;
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="RODADA";
	$result.="</TH>";
	$result.="<TH>";
	$result.="SALDO ANT";
	$result.="</TH>";
	$result.="<TH>";
	$result.="GASTO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="CRÉDITO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="PRÊMIO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="RESGATE";
	$result.="</TH>";
	$result.="<TH>";
	$result.="SALDO ***";
	$result.="</TH>";
	$result.="</TR>";
	foreach($extrato as $linha){
		$result.="<TR>";
		$result.="<TD>";
		$result.=$linha->rodada;
		$result.="</TD>";
		$result.="<TD";
		if($linha->vl_saldo_ant<0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$linha->vl_saldo_ant;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$linha->vl_gasto;
		$result.="</TD>";
		$result.="<TD";
		if($linha->vl_credito>0){
			$result.=" class='verde'";
		}
		$result.=">";
		$result.=$linha->vl_credito;
		$result.="</TD>";
		$result.="<TD";
		if($linha->vl_premio>0){
			$result.=" class='verde'";
		}
		$result.=">";
		$result.=$linha->vl_premio;
		$result.="</TD>";
		$result.="<TD";
		if($linha->vl_resgate>0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$linha->vl_resgate;
		$result.="</TD>";
		$result.="<TD";
		if($linha->vl_saldo<0){
			$result.=" class='vermelho'";
		}
		$result.=">";
		$result.=$linha->vl_saldo;
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="</TABLE>";
 }else{
	$result.="<H3>PROBLEMAS NA CAPTURA DAS INFORMAÇÕES DO EXTRATO! TENTE NOVAMENTE MAIS TARDE!</H3>";
 }
 
 return $result;

}

function listarparticipantes($id_grupo){
 $result='';
 $participantes=carrega_participantes($id_grupo);
 if($participantes){
	$result.="<TABLE>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="ID";
	$result.="</TH>";
	$result.="<TH>";
	$result.="";
	$result.="</TH>";
	$result.="<TH>";
	$result.="APELIDO";
	$result.="</TH>";
	$result.="<TH>";
	$result.="EMAIL";
	$result.="</TH>";
	$result.="<TH>";
	$result.="SITUAÇÃO";
	$result.="</TH>";
	$result.="</TR>";
	foreach($participantes as $linha){
		$result.="<TR>";
		$result.="<TD>";
		$result.=$linha->id_user;
		$result.="</TD>";
		$result.="<TD>";
		$result.=get_avatar($linha->id_user, 24);
		$result.="</TD>";
		$result.="<TD>";
		$result.=$linha->apelido;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$linha->email;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$linha->id_ativo==1?'OK':'INATIVO';
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="</TABLE>";
 }else{
	$result.="<H3>PROBLEMAS NA CAPTURA DAS INFORMAÇÕES DOS PARTICIPANTES! TENTE NOVAMENTE MAIS TARDE!</H3>";
 }
 
 return $result;

}


function palpitar($id_grupo){
 $result='';
 $jogos=carrega_jogos_palpitar();
 if($jogos){
	foreach($jogos as $jogo){
		$rodada=$jogo->rodada;
		break;
	}
	$user=get_current_user_id();
	$palpites_temp=le_palpites($id_grupo,$rodada,$user);
	$result.="\n<script type='text/javascript'>";
	$result.="\n function atualiza_jogo(){";
	$result.="\n  lista = [ 'XX', 'S:13 D:1 T:0' ,
'S:12 D:2 T:0' ,
'S:11 D:3 T:0' ,
'S:10 D:4 T:0' ,
'S:9 D:5 T:0'  ,
'S:8 D:6 T:0'  ,
'S:7 D:7 T:0'  ,
'S:6 D:8 T:0'  ,
'S:5 D:9 T:0'  ,
'S:13 D:0 T:1' ,
'S:12 D:1 T:1' ,
'S:11 D:2 T:1' ,
'S:10 D:3 T:1' ,
'S:9 D:4 T:1'  ,
'S:8 D:5 T:1'  ,
'S:7 D:6 T:1'  ,
'S:6 D:7 T:1'  ,
'S:5 D:8 T:1'  ,
'S:12 D:0 T:2' ,
'S:11 D:1 T:2' ,
'S:10 D:2 T:2' ,
'S:9 D:3 T:2'  ,
'S:8 D:4 T:2'  ,
'S:7 D:5 T:2'  ,
'S:6 D:6 T:2'  ,
'S:11 D:0 T:3' ,
'S:10 D:1 T:3' ,
'S:9 D:2 T:3'  ,
'S:8 D:3 T:3'  ,
'S:7 D:4 T:3'  ,
'S:6 D:5 T:3'  ,
'S:10 D:0 T:4' ,
'S:9 D:1 T:4'  ,
'S:8 D:2 T:4'  ,
'S:7 D:3 T:4'  ,
'S:9 D:0 T:5'  ,
'S:8 D:1 T:5'  ,
'S:8 D:0 T:6' ];";
	$result.="\n  triplo=0;";
	$result.="\n  duplo=0;";
	$result.="\n  simples=0;";
	$result.="\n  for (i=1;i<15;i++){";
	$result.="\n   if(document.getElementById(i + '-1').checked) {";
	$result.="\n    if(document.getElementById(i + '-X').checked) {";
	$result.="\n     if(document.getElementById(i + '-2').checked) {";
	$result.="\n      triplo++;";
	$result.="\n     } else {";
	$result.="\n      duplo++;";
	$result.="\n     }";
	$result.="\n    } else {";
	$result.="\n     if(document.getElementById(i + '-2').checked) {";
	$result.="\n      duplo++;";
	$result.="\n     } else {";
	$result.="\n      simples++;";
	$result.="\n     }";
	$result.="\n    }";
	$result.="\n   } else {";
	$result.="\n 	  if(document.getElementById(i + '-X').checked) {";
	$result.="\n     if(document.getElementById(i + '-2').checked) {";
	$result.="\n      duplo++;";
	$result.="\n     } else {";
	$result.="\n      simples++;";
	$result.="\n     }";
	$result.="\n    } else {";
	$result.="\n     if(document.getElementById(i + '-2').checked) {";
	$result.="\n      simples++;";
	$result.="\n     }";
	$result.="\n    }";
	$result.="\n   }";
	$result.="\n  }";
	$result.="\n  jogos=simples+duplo+triplo;";
	$result.="\n  texto='S:';";
	$result.="\n  texto=texto.concat(simples);";
	$result.="\n  texto=texto.concat(' D:');";
	$result.="\n  texto=texto.concat(duplo);";
	$result.="\n  texto=texto.concat(' T:');";
	$result.="\n  texto=texto.concat(triplo);";
	$result.="\n  ok=lista.indexOf(texto);";
	$result.="\n  texto=texto.concat(' J:');";
	$result.="\n  texto=texto.concat(jogos);";
	$result.="\n  if(ok!=-1){";
	$result.="\n   texto=texto.concat(' OK');";
	$result.="\n  } else {";
	$result.="\n   texto=texto.concat(' INVÁLIDO');";
	$result.="\n  }";
	$result.="\n  document.getElementById('combinacao').innerHTML = texto;";
	$result.="\n  for(i=1;i<39;i++){";
	$result.="\n  	document.getElementById('tipojogo' + i).className =";
   	$result.="\n  		document.getElementById('tipojogo' + i).className.replace";
    $result.="\n  			( /(?:^|\s)vermelho(?!\S)/g , '' )	";
	$result.="\n  }";
	$result.="\n  if((jogos!=14)||(ok==-1)){";
	$result.="\n   document.getElementById('registrarpalpite').disabled = true;";
	$result.="\n  } else {";
	$result.="\n   document.getElementById('registrarpalpite').disabled = false;";
	$result.="\n   document.getElementById('tipojogo' + ok).className += ' vermelho';";
	$result.="\n  }";
	$result.="\n}";
	$result.="\n</script>";
	$result.="<div class='centralizado'>";
	$result.="<TABLE class='semborda'>";
	$result.="<TR>";
	$result.="<TD>";
	$result.="<form method='POST'>";
	$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
	$result.="<input name=rodada type=hidden value=" . $rodada .">";
	$result.="<input name=user type=hidden value=" . $user .">";
	$result.="<input name=palpites type=hidden value=PALPITES>";
	$result.="<TABLE class='minimo'>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="#";
	$result.="</TH>";
	$result.="<TH>";
	$result.="TIME DA CASA";
	$result.="</TH>";
	$result.="<TH>";
	$result.="1";
	$result.="</TH>";
	$result.="<TH>";
	$result.="X";
	$result.="</TH>";
	$result.="<TH>";
	$result.="2";
	$result.="</TH>";
	$result.="<TH>";
	$result.="VISITANTE";
	$result.="</TH>";
	$result.="<TH>";
	$result.="DIA";
	$result.="</TH>";
	$result.="<TH>";
	$result.="#";
	$result.="</TH>";
	$result.="</TR>";
	foreach($palpites_temp as $palpite){
		$palpites[$palpite['seq']]['1']=$palpite['time1'];
		$palpites[$palpite['seq']]['X']=$palpite['empate'];
		$palpites[$palpite['seq']]['2']=$palpite['time2'];
	}
	foreach($jogos as $jogo){
		$result.="<TR><TD>";
		$result.=$jogo->seq;
		$result.="</TD>";
		$result.="<TD class='direita'>";
		$result.=$jogo->time1;
		$result.="</TD>";
		$result.="<TD>";
		$result.="<input id='" . $jogo->seq . "-1' name='" . $jogo->seq . "-1' type=checkbox autofocus onchange='atualiza_jogo()'";
		if($palpites[$jogo->seq]['1']){
			$result.=" checked ";
		}
		$result.=">";
		$result.="</TD>";
		$result.="<TD>";
		$result.="<input id='" . $jogo->seq . "-X' name='" . $jogo->seq . "-X' type=checkbox onchange='atualiza_jogo()'";
		if($palpites[$jogo->seq]['X']){
			$result.=" checked ";
		}
		$result.=">";
		$result.="</TD>";
		$result.="<TD>";
		$result.="<input id='" . $jogo->seq . "-2' name='" . $jogo->seq . "-2' type=checkbox onchange='atualiza_jogo()'";
		if($palpites[$jogo->seq]['2']){
			$result.=" checked ";
		}
		$result.=">";
		$result.="</TD>";
		$result.="<TD>";
		$result.=$jogo->time2;
		$result.="</TD>";
		$result.="<TD>";
		$result.=$jogo->dia;
		$result.="</TD>";
		$result.="<TD>";
		$result.="<INPUT TYPE=BUTTON VALUE='#' class='loteca button-primary' onclick=";
		$result.='"window.open(';
		$result.="'estatisticas-loteca?time1=";
		$result.=$jogo->time1;
		$result.="&time2=";
		$result.=$jogo->time2;
		$result.="'";
		$result.=',' . "'loteca-estatisticas'" . ')">';
		$result.="</TD>";
		$result.="</TR>";
	}
	$result.="<TR>";
	$result.="<TD id='combinacao' COLSPAN=8 class='centralizado'>";
	$result.="S:0 D:0 T:0 J:0 INVÁLIDO";
	$result.="</TD>";
	$result.="</TR>";
	$result.="<TR>";
	$result.="<TD COLSPAN=8 class='centralizado'>";
	$result.="&nbsp;<input id='registrarpalpite' name='registrarpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR PALPITE' DISABLED/>";
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	$result.="</form>";
	$result.="</TD>";
	$result.="<TD>";
	$result.="<TABLE class='centralizado'>";
	$result.="<TR>";
	$result.="<TH COLSPAN=4>";
	$result.="TIPOS DE JOGOS VÁLIDOS";
	$result.="</TH>";
	$result.="</TR>";
	$result.="<TR><TD id=tipojogo1 >S:13 D:1 T:0</TD>";
	$result.="<TD id=tipojogo2 >S:12 D:2 T:0</TD>";
	$result.="<TD id=tipojogo3 >S:11 D:3 T:0</TD>";
	$result.="<TD id=tipojogo4 >S:10 D:4 T:0</TD></TR>";
	$result.="<TR><TD id=tipojogo5 >S:9 D:5 T:0</TD>";
	$result.="<TD id=tipojogo6 >S:8 D:6 T:0</TD>";
	$result.="<TD id=tipojogo7 >S:7 D:7 T:0</TD>";
	$result.="<TD id=tipojogo8 >S:6 D:8 T:0</TD></TR>";
	$result.="<TR><TD id=tipojogo9 >S:5 D:9 T:0</TD>";
	$result.="<TD id=tipojogo10>S:13 D:0 T:1</TD>";
	$result.="<TD id=tipojogo11>S:12 D:1 T:1</TD>";
	$result.="<TD id=tipojogo12>S:11 D:2 T:1</TD></TR>";
	$result.="<TR><TD id=tipojogo13>S:10 D:3 T:1</TD>";
	$result.="<TD id=tipojogo14>S:9 D:4 T:1</TD>";
	$result.="<TD id=tipojogo15>S:8 D:5 T:1</TD>";
	$result.="<TD id=tipojogo16>S:7 D:6 T:1</TD></TR>";
	$result.="<TR><TD id=tipojogo17>S:6 D:7 T:1</TD>";
	$result.="<TD id=tipojogo18>S:5 D:8 T:1</TD>";
	$result.="<TD id=tipojogo19>S:12 D:0 T:2</TD>";
	$result.="<TD id=tipojogo20>S:11 D:1 T:2</TD></TR>";
	$result.="<TR><TD id=tipojogo21>S:10 D:2 T:2</TD>";
	$result.="<TD id=tipojogo22>S:9 D:3 T:2</TD>";
	$result.="<TD id=tipojogo23>S:8 D:4 T:2</TD>";
	$result.="<TD id=tipojogo24>S:7 D:5 T:2</TD></TR>";
	$result.="<TR><TD id=tipojogo25>S:6 D:6 T:2</TD>";
	$result.="<TD id=tipojogo26>S:11 D:0 T:3</TD>";
	$result.="<TD id=tipojogo27>S:10 D:1 T:3</TD>";
	$result.="<TD id=tipojogo28>S:9 D:2 T:3</TD></TR>";
	$result.="<TR><TD id=tipojogo29>S:8 D:3 T:3</TD>";
	$result.="<TD id=tipojogo30>S:7 D:4 T:3</TD>";
	$result.="<TD id=tipojogo31>S:6 D:5 T:3</TD>";
	$result.="<TD id=tipojogo32>S:10 D:0 T:4</TD></TR>";
	$result.="<TR><TD id=tipojogo33>S:9 D:1 T:4</TD>";
	$result.="<TD id=tipojogo34>S:8 D:2 T:4</TD>";
	$result.="<TD id=tipojogo35>S:7 D:3 T:4</TD>";
	$result.="<TD id=tipojogo36>S:9 D:0 T:5</TD></TR>";
	$result.="<TR><TD id=tipojogo37>S:8 D:1 T:5</TD>";
	$result.="<TD id=tipojogo38>S:8 D:0 T:6</TD><TD></TD><TD></TD></TR>";
	$result.="</TABLE>";
	$result.="</TD>";
	$result.="</TR>";
	$result.="</TABLE>";
	$result.="</div>";
	$result.="<script type='text/javascript'>\natualiza_jogo();\n</script>";
 }else{
	$result.="<H3>NÃO HÁ JOGOS PARA FAZER PALPITES!</H3>";
 }
 
 return $result;

}

function tab_dadosgrupo($id_grupo,$admin = 0,$table = TRUE){
	$dadosgrupo=dadosgrupo($id_grupo,$admin);
	$result="";
	if($table){
		$result.="<TABLE>";
	}
	$result.="<TR>";
	$result.="<TD>";
	$result.="Grupo: " . $dadosgrupo->id_grupo . " / " . $dadosgrupo->nm_grupo;
	$result.="</TD>";
	$result.="<TD>";
	$result.="Administrador: " . $dadosgrupo->id_user . " / " . $dadosgrupo->apelido;
	$result.="</TD>";
	$result.="<TD>";
	$result.="Saldo do grupo: " . $dadosgrupo->saldo_grupo;
	$result.="</TD>";
	$result.="</TR>";
	if($admin==0){
		$result.="<TR>";
		$result.="<TD>";
		$result.="Seu saldo: " . $dadosgrupo->saldo_participante;
		$result.="</TD>";
		$result.="<TD>";
		$result.="...";
		$result.="</TD>";
		$result.="<TD>";
		$result.="...";
		$result.="</TD>";
		$result.="</TR>";
	}
	if($table){
		$result.="</TABLE>";
	}		
	return $result;
}

function tab_dadosrodada($rodada = 0,$admin = 0,$table = TRUE){
	$dadosrodada=dadosrodada($rodada,$admin);
	$result="";
	if($table){
		$result.="<TABLE>";
	}

	$result.="<TR>";
	$result.="<TD>";
	$result.="Início dos palpites: " . $dadosrodada->dt_inicio_palpite;
	$result.="</TD>";
	$result.="<TD>";
	$result.="Término dos palpites: " . $dadosrodada->dt_fim_palpite;
	$result.="</TD>";
	$result.="<TD>";
	$result.="Data da apuração: " . $dadosrodada->dt_sorteio;
	$result.="</TD>";
	$result.="</TR>";
	
	if($table){
		$result.="</TABLE>";
	}
	return $result;
}

function tab_dadosgruporodada($id_grupo,$admin = 0,$table = TRUE){
	$dadosgrupo=dadosgruporodada($id_grupo,$admin);
	$result="";
	if($table){
		$result.="<TABLE>";
	}
	$result.="<TR>";
	$result.="<TD>";
	$result.="Rodada atual: " . $dadosgrupo->rodada;
	$result.="<BR>";
	$result.="Valor máximo: " . $dadosgrupo->vl_max;
	$result.="<BR>";
	$result.="Valor mínimo: " . $dadosgrupo->vl_min;
	$result.="<BR>";
	$result.="Quanto gastar: ";
	switch ($dadosgrupo->tip_rateio){
		case 1:
			$result.="Mínimo acima da média";
			break;
		case 2:
			$result.="Máximo abaixo da média";
			break;
		case 3:
			$result.="Máximo";
			break;
		case 4:
			$result.="Mínimo";
			break;
	}
	$result.="</TD>";
	$result.="<TD>";
	$result.="Gera volante com cota: ";
	$result.=($dadosgrupo->ind_bolao_volante==1)?"Sim":"Não";
	$result.="<BR>";
	$result.="Valor mínimo da cota: " . $dadosgrupo->vl_lim_rateio;
	$result.="<BR>";
	$result.="Máximo de zebras por volante: " . $dadosgrupo->qt_max_zebras;
	$result.="</TD>";
	$result.="<TD>";
	$result.="Mínimo de zebras por volante: " . $dadosgrupo->qt_min_zebras;
	$result.="<BR>";
	$result.="Amplia ZEBRA: ";
	$result.=$dadosgrupo->amplia_zebra==1?"Sim":"Não";
	$result.="<BR>";
	$result.="Desdobramento liberado: ";
	$result.=$dadosgrupo->ind_libera_proc_desdobra==1?"Sim":"Não";
	$result.="</TD>";
	$result.="</TR>";
	if($table){
		$result.="</TABLE>";
	}
		
	return $result;
}

function tab_grupos_admin($boloes_admin){
	$result="";
	$result.="<TABLE>";
	$result.="<TR><TH COLSPAN=6>BOLÕES QUE ADMINISTRO</TR>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="ID";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Grupo";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Situação";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Apelido";
	$result.="</TH>";
	$result.="<TH>";
	$result.="R$ Grupo";
	$result.="</TH>";
	$result.="<TH>";
	$result.="";
	$result.="</TH>";
	$result.="</TR>";
	foreach ($boloes_admin as $bolao){
		$result.="<TR>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->id_grupo;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->nm_grupo;
		$result.="</TD>";
		$result.="<TD class='centralizado'>";
		if($bolao->id_ativo){
			$result.="ATIVO";
		}else{
			$result.="INATIVO";
			}
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->apelido;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->saldo;
		$result.="</TD>";
		$result.="<TD class='centralizado'>";
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $bolao->id_grupo .">";
		$result.="&nbsp;<input name='admingrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OPCOES' />";
		$result.="</form>";
		$result.="</TD>";
		$result.="</TR>";
		}
	$result.="</TABLE>";
	return $result;
}

function tab_grupos_usu($boloes_usu){
	$result="";
	$result.="<TABLE>";
	$result.="<TR><TH COLSPAN=6>BOLÕES QUE PARTICIPO</TR>";
	$result.="<TR>";
	$result.="<TH>";
	$result.="ID";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Grupo";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Apelido";
	$result.="</TH>";
	$result.="<TH>";
	$result.="Seu Saldo";
	$result.="</TH>";
	$result.="<TH>";
	$result.="R$ Grupo";
	$result.="</TH>";
	$result.="<TH>";
	$result.="";
	$result.="</TH>";
	$result.="</TR>";
	foreach ($boloes_usu as $bolao){
		$result.="<TR>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->id_grupo;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->nm_grupo;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->apelido;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->saldo;
		$result.="</TD>";
		$result.="<TD>&nbsp;";
		$result.=$bolao->saldo_grupo;
		$result.="</TD>";
		$result.="<TD class='centralizado'>";
		$result.="<form method='POST'>";
		$result.="<input name=grupo type=hidden value=" . $bolao->id_grupo .">";
		$result.="&nbsp;<input name='acessargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OPCOES' />";
		$result.="</form>";
		$result.="</TD>";
		$result.="</TR>";
		}
	$result.="</TABLE>";
	return $result;
}

function carrega_css(){
	wp_enqueue_style('loteca-style', plugin_dir_url(__FILE__)  . 'css/loteca-style.css', array());
}

function carrega_js(){
	wp_enqueue_style('loteca-jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.min.css', array());
	wp_enqueue_style('loteca-admin-styles', plugin_dir_url(__FILE__)  . 'css/style-admin.css', array('wp-color-picker'));
	wp_enqueue_script('loteca-admin-timepicker-addon-script', plugin_dir_url(__FILE__)  . 'js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-datepicker'));
	wp_enqueue_script('loteca-js', plugin_dir_url(__FILE__)  . 'js/loteca_javascript.js', array('jquery', 'jquery-min', 'jquery-ui', 'wp-color-picker'));
}

function listargruposabertos(){
	$gruposabertos=captura_grupos_abertos();
	$result="";
	if($gruposabertos){
		$result.="<table>";
		$result.="<tr>";
		$result.="<th>";
		$result.="ID";
		$result.="</th>";
		$result.="<th>";
		$result.="Grupo";
		$result.="</th>";
		$result.="<th>";
		$result.="Administrador";
		$result.="</th>";
		$result.="<th>";
		$result.="Nome";
		$result.="</th>";
		$result.="<th>";
		$result.="Email";
		$result.="</th>";
		$result.="<th>";
		$result.="Participantes";
		$result.="</th>";
		$result.="<th>";
		$result.="QUERO";
		$result.="</th>";
		$result.="</tr>";
		
		foreach($gruposabertos as $linha){
			$result.="<tr class='centralizado'>";
			$result.="<td>";
			$result.=$linha->id_grupo ;
			$result.="</td>";
			$result.="<td>";
			$result.=$linha->nm_grupo ;
			$result.="</td>";
			$result.="<td>";
			$result.=$linha->id_user ;
			$result.=" - ";
			$result.=$linha->apelido ;
			$result.="</td>";
			$result.="<td>";
			$result.=$linha->nome ;
			$result.="</td>";
			$result.="<td>";
			$result.=$linha->email ;
			$result.="</td>";
			$result.="<td>";
			$result.=$linha->qt_participante ;
			$result.="</td>";
			$result.="<td>";
			$result.="<form method='post' style='display:inline'><input type=submit name='quero_participar' value='SOLICITAR'><input type=hidden name=grupo value='". $linha->id_grupo. "'></form>";
			$result.="</td>";
			$result.="</tr>";
		}
		$result.="</table>";
	}else{
		$result.="NENHUM GRUPO DISPONÍVEL PARA SOLICITAR PARTICIPAÇÃO.";
		$result.="<form method='post' style='display:inline'><input type=submit name='VOLTAR' value='VOLTAR' /></form>";
	}
	return $result;
}

function quero_participar ($id_grupo){
	if(verifica_grupo_aberto($id_grupo)){
		if(inclui_solicitacao($id_grupo)){
			return "Solicitação efetuada, o administrador do bolão receberá um email informando.";
		}else{
			return "Não foi possível incluir sua solicitação. Entre em contato com o administrador do bolão.";
		}
	}else{
		return "Não foi possível incluir sua solicitação. Entre em contato com o administrador do bolão.";
	}
}

function datetimepicker(){
	wp_enqueue_script('loteca-admin-script', plugin_dir_url(__FILE__)  . 'js/scripts-admin.js', array('jquery', 'wp-color-picker', 'loteca-js'));
}

?>