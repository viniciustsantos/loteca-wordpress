<?php
// loteca_shortcodes.php

// Funcionalidade        - admin/user - $_????    - Parametros - Descrição
// quero_participar      -            - $_POST    -
// SOLICITAR             -            - $_POST
// conf_usuario          -            - $_REQUEST - grupo      - Configura usuário
// ranking               -            - $_POST    -              
// conf_previsao         -            - $_REQUEST - grupo      - Configura previsão de deposito
// conf_participacao     -            - $_REQUEST - grupo      - Configura participacao na rodada
// CRIAR                 -            - $_POST
// listarparticipantes   -            - $_POST
// registrarpalpite      -            - $_POST
// extrato               -            - $_POST
// palpitar              -            - $_POST    - grupo      - Incluir palpites
// acessargrupo          -            - $_POST
// admingrupo            -            - $_POST
// adminparticipantes    -            - $_POST
// extratoparticipante   -            - $_POST
// testeemail            -            - $_POST
// enviarsaldos          -            - $_POST
// incluirgasto          -            - $_POST
// incluircredito        -            - $_POST
// confirmarcredito      -            - $_POST
// incluirpremio         -            - $_POST
// resgate               -            - $_POST
// desativarparticipante -            - $_POST
// ativarparticipante    -            - $_POST
// alterarparametros     -            - $_POST
// montarjogos           -            - $_POST
// verrodadas            -            - $_POST
// verpalpites           -            - $_POST
// detalharpalpite       -            - $_POST
// verresultado          -            - $_POST
// novarodada            -            - $_POST
// guardarapostas        -            - $_POST

function loteca_shortcode($atts, $content = NULL){
	include_once 'loteca_db_functions.php';
	include_once 'loteca_functions.php';
	global $loteca_voltar_para, $loteca_pagina_atual;
	wp_enqueue_style('loteca-style');
	wp_enqueue_style('loteca-jquery-ui-css');
	wp_enqueue_style('loteca-admin-styles');
	wp_enqueue_script('loteca-js');
	wp_enqueue_script('loteca-jquery');
	wp_enqueue_script('loteca-admin-timepicker-addon-script');
	$loteca_pagina_atual = '';
	if (isset($_POST['voltarpara'])) {
		$loteca_voltar_para = $_POST['voltarpara'];
	}else{
		$loteca_voltar_para = '';
	}
	$result="";
	if ( !is_user_logged_in() ) {
		$result.="<P>OLÁ, BEM VINDO AO BOLÃO DA LOTECA!</P>";
		$result.="<P>FAÇA SEU <a href='".wp_login_url(site_url(str_replace(array('/loteca/'),array('') , $_SERVER['REQUEST_URI'])))."'>LOGIN</a> E ACESSE OS BOLÕES QUE VOCÊ ESTÁ PARTICIPANDO E/OU ADMINISTRA.</P>";
		$result.="<P>PARA CRIAR UM NOVO BOLÃO TAMBÉM É NECESSÁRIO FAZER O LOGIN ANTES.</P>";
		$result.=loteca_msg_rodape();
		return $result;
	}
	if(isset($_POST['quero_participar'])){
		$id_grupo=$_POST['grupo'];
		$loteca_pagina_atual = 'quero_participar';
		return quero_participar($id_grupo) . loteca_msg_rodape();
	}
	if(isset($_POST['SOLICITAR'])){
		$loteca_pagina_atual = 'SOLICITAR';
		return listargruposabertos() . loteca_msg_rodape();
	}
	if(isset($_POST['conf_usuario'])||isset($_GET['conf_usuario'])){
		$id_grupo=$_REQUEST['grupo'];
		$loteca_pagina_atual = 'CONFIGURAR';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");
		
		$meio=configurarusuario($id_grupo);
		
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['ranking'])){
		$id_grupo=$_POST['grupo'];
		$loteca_pagina_atual = 'RANKING';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		if(isset($_POST['t_ranking'])){
			switch($_POST['t_ranking']){
				case '1':
					$meio=ranking($id_grupo,'1');
					break;
				case '6':
					$meio=ranking($id_grupo,'6');
					break;
				case '12':
					$meio=ranking($id_grupo,'12');
					break;
				case 'A':
					if(isset($_POST['ano'])){
						$meio=ranking($id_grupo,'A',$_POST['ano']);
					}else{
						$meio=ranking($id_grupo,'6');
					}
					break;
				case 'M':
					if(isset($_POST['anomes'])){
						$meio=ranking($id_grupo,'M',substr($_POST['anomes'],0,4),substr($_POST['anomes'],5,2));
					}else{
						$meio=ranking($id_grupo,'M',date('Y'),date('m'));
					}
//					if(isset($_POST['ano'])&&isset($_POST['mes'])){
//						$meio=ranking($id_grupo,'M',$_POST['ano'],$_POST['mes']);
//					}else{
//						$meio=ranking($id_grupo,'6');
//					}
					break;
				case 'R':
					if(isset($_POST['rodada'])){
						$meio=ranking($id_grupo,'R',0,0,$_POST['rodada']);
					}else{
						$meio=ranking($id_grupo,'R');
					}
					break;
				default:
					$meio=ranking($id_grupo,'6');
					break;
			}
			
		}else{
			$meio=ranking($id_grupo,'6');
		}
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if((isset($_POST['conf_participacao']))||(isset($_GET['conf_participacao']))){
		$id_grupo=$_REQUEST['grupo'];
		$loteca_pagina_atual = 'CONFIGURAR';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		$meio=configurarparticipacao($id_grupo);
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['conf_previsao'])||isset($_GET['conf_previsao'])){
		if(isset($_REQUEST['grupo'])){
			$id_grupo=$_REQUEST['grupo'];
		}else{
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'CONFIGURAR';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		$meio=configurarprevisao($id_grupo);
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['outros_grupos'])){
		$loteca_pagina_atual = 'outros_grupos';
		$result.=loteca_outros_grupos();
		return $result;
	}
	if(isset($_POST['CRIAR'])){
		$loteca_pagina_atual = 'CRIAR';
		$result.=loteca_novo_grupo();
		return $result;
	}
	if(isset($_POST['listarparticipantes'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'listarparticipantes';
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		$meio=listarparticipantes($id_grupo);
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
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
		
			error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$_POST['grupo']) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

			$meio=loteca_registrar_palpite($_POST['grupo'],$_POST['rodada'],$_POST['user'],$palpites);
			return acessagrupo($_POST['grupo']) . $meio . loteca_msg_rodape();
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
		$meio=extrato($id_grupo);
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['palpitar'])||isset($_GET['palpitar'])){
		$id_grupo=$_REQUEST['grupo'];
		$loteca_pagina_atual = 'palpitar';
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES. (" . $loteca_pagina_atual .")";
			return $result;
		}
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		$meio=palpitar($id_grupo);
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['acessargrupo'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_acessa_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'acessargrupo';
		
		error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

		$meio=loteca_instrucao_grupo($id_grupo);
		return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
	}

	if(isset($_POST['admingrupo'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'admingrupo';
		return admingrupo($id_grupo) . loteca_msg_rodape();
	}
	if(isset($_POST['adminparticipantes'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'adminparticipantes';
		$meio = adminparticipantes($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['extratoparticipante'])){
		$id_grupo=$_POST['grupo'];
		$id_user=$_POST['id_user'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'listarparticipantes';
		$meio=extrato($id_grupo,$id_user);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['testeemail'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'listarparticipantes';
		$meio=testeemail($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['enviarsaldos'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'listarparticipantes';
		$meio=enviarsaldos($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['incluirgasto'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluirgasto';
		$meio=incluirgasto($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['incluircredito'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluircredito';
		$meio=incluircredito($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['confirmarcredito'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'confirmarcredito';
		$meio=confirmarcredito($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['incluirpremio'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'incluirpremio';
		$meio=incluirpremio($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['resgate'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'resgate';
		$meio=incluirresgate($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['desativarparticipante'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'desativarparticipante';
		$meio=desativarparticipante($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['ativarparticipante'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'ativarparticipante';
		$meio=ativarparticipante($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['alterarparametros'])){
		$id_grupo=$_REQUEST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'alterarparametros';
		$meio=alterarparametros($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['montarjogos'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'montarjogos';
		if(isset($_POST['rodada'])){
			$meio=montarjogos($id_grupo,$_POST['rodada']);
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}else{
			$meio=montarjogos($id_grupo);
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}
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
		
			error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

			$meio=verrodadas($id_grupo,$inicio,$_POST['user']);
			return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
		}else{
			$meio=verrodadas($id_grupo,$inicio,0);
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}
	}
	if(isset($_POST['verpalpites'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'verpalpites';
		$meio=verpalpites($id_grupo,$_POST['rodada']);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
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
			$meio=detalharpalpite($id_grupo,$_POST['rodada'],$_POST['id_user']);
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}else{
		
			error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");		

			$meio=detalharpalpite($id_grupo,$_POST['rodada'],$_POST['id_user']);
			return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
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
		
			error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");

			$meio=verresultado($id_grupo,$_POST['rodada']);
			return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
		}else{
			$meio=verresultado($id_grupo,$_POST['rodada']);
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}
	}
	if(isset($_POST['novarodada'])){
		$id_grupo=$_POST['grupo'];
		if( !loteca_admin_grupo($id_grupo) ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'novarodada';
		$meio=habilitarrodada($id_grupo);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}

	if(isset($_POST['guardarapostas'])){
		$id_grupo=$_POST['grupo'];
		$rodada=$_POST['rodada'];
		if(  (!loteca_admin_grupo($id_grupo)) && (!loteca_acessa_grupo($id_grupo))  ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$loteca_pagina_atual = 'montarjogos';
		$meio=guardarapostas($id_grupo,$rodada,$_POST['jogo'],$_POST['qt_cotas_aposta']);
		return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
	}
	if(isset($_POST['ver_aposta'])){
		$id_grupo=$_POST['grupo'];
		$rodada=$_POST['rodada'];
		$loteca_pagina_atual = 'ver_aposta';
		if(  (!loteca_admin_grupo($id_grupo)) && (!loteca_acessa_grupo($id_grupo))  ){
			$result.="OCORREU UM ERRO. TENTE NOVAMENTE EM ALGUNS INSTANTES.";
			return $result;
		}
		$meio=ver_aposta($id_grupo,$rodada);
		if(isset($_POST['id_user'])){
			error_log("HORA:". current_time('Y-m-d H:i:s') .":USER:". get_current_user_id() .":". tx_user(get_current_user_id(),$id_grupo) ."\nPOST:". print_r($_POST,TRUE) ."\n");
			return acessagrupo($id_grupo) . $meio . loteca_msg_rodape();
		}else{
			return admingrupo($id_grupo) . $meio . loteca_msg_rodape();
		}
		
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
					$result.=acessagrupo($bolao->id_grupo) . loteca_instrucao_grupo($bolao->id_grupo) ;
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
	$result.=loteca_msg_rodape();
	return $result;
}

function loteca_shortcode_estatisticas($atts, $content = NULL){
	$time1=$_REQUEST['time1'];
	$time2=$_REQUEST['time2'];
	$ano=current_time("Y");
	$ano_ant=$ano -1;
	include_once 'loteca_db_functions.php';
	$estatisticas=carrega_estatisticas($time1,$time2);
	$result="";
	$result.="
	<TABLE><TR class='centralizado'><TH>?</TH><TH>" . $ano . "</TH><TH>" . $ano_ant . "</TH><TH>ANTES DE " . $ano_ant . "</TH></TR>
	<TR class='centralizado'>
	<TD>" . $time1 . " X " . $time2 . "</TD>
	<TD>";
	$linha='A';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="
	</TD>
	<TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="
	</TD></TR>
	<TR class='centralizado'><TD>" . $time2 . " X " . $time1 . "</TD>
	<TD>";
	$linha='B';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="
	</TD></TR>
	<TR class='centralizado'><TD>" . $time1 . "</TD>
	<TD>";
	$linha='G';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD></TR>
	<TR class='centralizado'>
	<TD>" . $time2 . "</TD>
	<TD>";
	$linha='H';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="
	</TD></TR>
	<TR class='centralizado'>
	<TD>" . $time1 . " X ?</TD>
	<TD>";
	$linha='C';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD></TR>
	<TR class='centralizado'>
	<TD>? X " . $time1 . "</TD>
	<TD>";
	$linha='D';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="</TD></TR>
	<TR class='centralizado'>
	<TD>" . $time2 . " X ?</TD>
	<TD>";
	$linha='E';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="
	</TD></TR>
	<TR class='centralizado'>
	<TD>? X " . $time2 . "</TD>
	<TD>";
	$linha='F';
	if($estatisticas[$linha][$ano]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][$ano_ant]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][$ano_ant]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][$ano_ant]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][$ano_ant]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][$ano_ant]['VTIME2'];
	}
	$result.="</TD><TD>";
	if($estatisticas[$linha][0]['QT_JOGOS']>0){
		$result.="
		J: " . $estatisticas[$linha][0]['QT_JOGOS'] . " / 
		V: " . $estatisticas[$linha][0]['VTIME1'] . " / 
		E: " . $estatisticas[$linha][0]['EMPATE'] . " / 
		D: " . $estatisticas[$linha][0]['VTIME2'];
	}
	$result.="
	</TD></TR></TABLE>
	TIME 1: " . $_REQUEST['time1'] . " / TIME 2: " . $_REQUEST['time2'];
	$result.=loteca_msg_rodape();
	return $result;
}

function loteca_shortcode_link_cef($atts, $content = NULL) {
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

function loteca_msg_rodape(){
	return "<P><H3>ATENÇÃO: ESTE SITE NÃO EFETUA JOGOS DA LOTECA! <BR>NOSSO OBJETIVO É AJUDAR A ADMINISTRAR GRUPOS DE BOLÃO.</H3></P>";
}

?>