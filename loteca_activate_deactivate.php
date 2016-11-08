<?php
// loteca_activate_deactivate.php

function loteca_ativar() {
  // Vamos criar um opção para ser guardada na base-de-dados
  // e incluir um valor por defeito.
  update_option( 'loteca_ativa' , '1' );
  update_option( 'loteca_limite_desdobramento' , '50000' );
  update_option( 'loteca_limite_participante' , '50' );
  loteca_cria_pagina_estatistica();
}

function loteca_desativar() {
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

?>