<?php
/*
Plugin Name: Loteca
Plugin URI: http://vinicius.santos.nom.br/loteca
Description: Admnistração de bolão da loteca
Author: Vinicius Santos
Version: 0.0.1
Author URI: http://vinicius.santos.nom.br
*/

/*
 *      Copyright 2014 Vinicius Santos (email : viniciustsantos@gmail.com)
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

/*

 BACKLOG
 -------
 1. Cadastrar novos grupos / convidar participante
 2. Preparar manual de funcionamento
 3. Melhorar extrato (lançamentos individualizados)
 4. Incluir/alterar manualmente os jogos da semana (somente administrador do site)
 5. Alterar os horários dos jogos (somente administrador do site)
 6. Incluir outras modalidades de loteria (administrador do site/administrador do bolão/participante)
    MEGASENA    6 EM  60 PREMIO 4, 5 6 ACERTOS
	LOTOFACIL  15 EM  25 PREMIO 11, 12, 13, 14 E 15 ACERTOS
	QUINA       5 EM  80 PREMIO 3, 4 E 5 ACERTOS
	LOTOMANIA  50 EM 100 PREMIO 20, 19, 18, 17, 16, 15 E 0 ACERTOS
	TIMEMANIA  
	DUPLA SENA  6 EM  50 PREMIO 6, 5, 4 E 3 ACERTOS (DOIS SORTEIOS)
	TIMEMANIA  10 EM  80 PREMIO 7, 6, 5, 4 E 3 ACERTOS
	FEDERAL
	LOTOGOL    PLACAR DE 5 JOGOS DE FUTEBOL PREMIO ????
 7. Enviar de email de atualizações
 8. Configurar quais emails enviar (administrador do site/administrador do grupo)
 9. Configurar quais emails receber (participante do grupo)
 12. Carregar arquivo/imagem dos bilhetes por volante (administrador do grupo)
 13. Carregar arquivo/imagem do depósito transferência (participante)
 15. Quando estourar o limite de cotas para a modalidade da loteria no volante informar e continuar sem uso de cotas (na impressão dos volantes)
 16. Alterar aposta depois de gerar a aposta automatica
 17. Incluir aposta manualmente
 18. Capturar resultados de outras modalidades de loteria
 19. Capturar resultados e programação via processo batch processar algumas vezes ao dia (só carrega quando um administrador de grupo entre na página)
 20. Permitir ao administrador do site forçar a tentativa de capturar os resultados 
 21. Opção para o administrador do grupo para forçar resultado na aposta ignorando os palpites
 22. Separar arquivo php com funcionalidades de serviço e outro somente com conversão em html
 23. Criar modelo SOA para atender futuro aplicativo mobile 
 24. Compartilhar saldo entre grupos do mesmo administrador de grupo

CONCLUIDO
---------
 10. Retirar o botão TESTE do administrador do grupo
 11. Incluir o botão imprimir aposta (administrador do grupo)
 
EXCLUÍDOS
---------
 14. Quantidade de cotas por modalidade de loteria (Cada grupo só terá uma modalidade)

*/ 
// Carrega pasta do plugin
$loteca_dir = plugin_dir_path( __FILE__ );

// Função utilizada na ativação do plugin
register_activation_hook(__FILE__, 'loteca_ativar_hook' );
 
// Função utilizada na desativação do plugin
register_deactivation_hook(__FILE__, 'loteca_desativar_hook' );

loteca_prepara_plugin();

function loteca_configuration() {
	include_once 'loteca_admin.php';
	loteca_options();
}

function loteca_prepara_plugin(){
// Inclusão de verificação da versão do banco de dados
	add_action( 'plugins_loaded', 'loteca_update_db_check' );
// Inclusão do menu de administração
	if ( is_admin() ){ // admin actions
		add_action( 'admin_menu', 'loteca_menu' );
	}
// inclui scripts
	add_action( 'wp_enqueue_scripts', 'loteca_scripts_styles' );
// Inclui shortcode para loteca
	add_shortcode( 'loteca', 'shortcode_loteca' );
	add_shortcode( 'loteca-estatisticas', 'shortcode_loteca_estatisticas' );
	add_shortcode ('loteca-link-cef', 'shortcode_loteca_link_cef');

// Inclui redirecionamento da página de registro concluído com sucesso
add_filter( 'registration_redirect', 'loteca_registration_redirect' );
}

add_action('template_redirect','redirecionamento_impressao');

function redirecionamento_impressao() {
		
	$uri=end(explode('/',$_SERVER['REQUEST_URI']));
	
	if ($uri=='impressao_volante.png') {
//		error_log('$uri: '.$uri);
//		error_log('$_POST' . print_r($_POST,true));
		include_once 'loteca_volante.php';
		loteca_imprime_volante($_POST['grupo'],$_POST['rodada'],$_POST['jogo']);
		exit();
		wp_die();
	}
	if ($uri=='impressao_volante.pdf') {
//		error_log('$uri: '.$uri);
//		error_log('$_POST' . print_r($_POST,true));
		include_once 'loteca_volante.php';
		loteca_imprime_volante_pdf($_POST['grupo'],$_POST['rodada'],$_POST['jogo'],$_POST['qt_cotas_aposta']);
		exit();
		wp_die();
	}
	if ($uri=='example_001.php') {
//		error_log('$uri: '.$uri);
		include_once 'example_001.php';
		gera_pdf_001($_POST['grupo'],$_POST['rodada'],$_POST['jogo']);
		exit();
		wp_die();
	}
}

function loteca_registration_redirect() {
    return home_url( '/' );
}

function shortcode_loteca ($atts, $content = NULL){
	include_once 'loteca_shortcodes.php';
	return loteca_shortcode($atts, $content = NULL);
}

function shortcode_loteca_estatisticas ($atts, $content = NULL){
	include_once 'loteca_shortcodes.php';
	return loteca_shortcode_estatisticas($atts, $content = NULL);
}

function shortcode_loteca_link_cef ($atts, $content = NULL){
	include_once 'loteca_shortcodes.php';
	return loteca_shortcode_link_cef($atts, $content = NULL);
}

function loteca_menu() {
	if ( is_admin() ){ // admin actions
		add_options_page( 'Opções Gerais da Loteca', 'Loteca', 'manage_options', 'loteca-menu', 'loteca_configuration' );
	}
}
function loteca_update_db_check() {
	include_once 'loteca_db.php';
    global $loteca_db_version;
    if ( get_option( 'loteca_db_version' ) != $loteca_db_version ) {
        loteca_db_install();
    }
}

function loteca_ativar_hook (){
	include_once 'loteca_activate_deactivate.php';
	loteca_ativar();
}

function loteca_desativar_hook (){
	include_once 'loteca_activate_deactivate.php';
	loteca_desativar();
}
function loteca_scripts_styles(){
	wp_register_style('loteca-style', plugin_dir_url(__FILE__)  . 'css/loteca-style.css', array());
	wp_register_style('loteca-jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.min.css', array());
	wp_register_style('loteca-admin-styles', plugin_dir_url(__FILE__)  . 'css/style-admin.css', array('wp-color-picker'));
	wp_register_script('loteca-admin-timepicker-addon-script', plugin_dir_url(__FILE__)  . 'js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-datepicker'),true);
	wp_register_script('loteca-js', plugin_dir_url(__FILE__)  . 'js/loteca_javascript.js', array('jquery', 'jquery-ui-datepicker'), true);
	wp_register_script('loteca-jquery', plugin_dir_url(__FILE__)  . 'js/loteca_jquery.js', array('jquery',), true);
	wp_register_script('loteca-admin-script', plugin_dir_url(__FILE__)  . 'js/scripts-admin.js', array('jquery', 'wp-color-picker', 'loteca-js'),true);
}

?>