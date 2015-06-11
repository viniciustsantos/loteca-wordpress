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
// Carrega pasta do plugin
$loteca_dir = plugin_dir_path( __FILE__ );

// Carrega nome dos arquivos do plugin
$loteca_functions=$loteca_dir.'loteca_functions.php';
$loteca_db=$loteca_dir.'loteca_db.php';

// Carrega processo de atualização do banco de dados
require_once ($loteca_db);

// Carrega functions do plugin
require_once ($loteca_functions);

// Função utilizada na ativação do plugin
register_activation_hook(__FILE__, 'loteca_ativar_hook' );
 
// Função utilizada na desativação do plugin
register_deactivation_hook(__FILE__, 'loteca_desativar_hook' );

// Inclusão do menu de administração
add_action( 'admin_menu', 'loteca_menu' );

// Inclusão de verificação da versão do banco de dados
add_action( 'plugins_loaded', 'loteca_update_db_check' );

// Inclui shortcode para loteca

add_shortcode( 'loteca', 'shortcode_loteca' );

add_shortcode( 'loteca-estatisticas', 'shortcode_loteca_estatisticas' );

function loteca_menu() {
	add_options_page( 'Opções Gerais da Loteca', 'Loteca', 'manage_options', 'loteca-menu', 'loteca_configuration' );
}

function loteca_update_db_check() {
    global $loteca_db_version;
    if ( get_option( 'loteca_db_version' ) != $loteca_db_version ) {
        loteca_db_install();
    }
}

function loteca_configuration() {
	loteca_options();
}

?>