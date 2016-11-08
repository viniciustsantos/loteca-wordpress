<?php
// loteca_db_admin.php

function captura_parametros(){
	global $wpdb;
	$parametros=$wpdb->get_row("SELECT limite_proc FROM " .  $wpdb->prefix . "loteca_parametro ORDER BY `data` DESC ;" , OBJECT, 0);
	return $parametros;
}

function altera_parametros($limite_proc){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		REPLACE INTO " . $wpdb->prefix . "loteca_parametro
		( data, limite_proc )
		VALUES ( %s, %d )
	", 
    current_time("Y-m-d"), 
	$limite_proc
) );
//	if($wpdb->last_error)
	return TRUE;
}

function loteca_ativar_grupo($grupo){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_grupo
		SET id_ativo = 1 
		WHERE id_grupo = %s
	", 
    $grupo ) );
	return TRUE;
}

function loteca_desativar_grupo($grupo){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_grupo
		SET id_ativo = 0
		WHERE id_grupo = %s
	", 
    $grupo ) );
	return TRUE;
}


?>