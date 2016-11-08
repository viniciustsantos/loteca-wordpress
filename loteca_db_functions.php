<?php
// loteca_db_functions.php

// TABELA loteca_rodada
function ultima_rodada(){
	global $wpdb;
	$ultima=$wpdb->get_row("SELECT rodada, dt_sorteio FROM " .  $wpdb->prefix . "loteca_rodada WHERE rodada = (SELECT MAX(rodada) rodada FROM " .  $wpdb->prefix . "loteca_rodada);");
	return $ultima;
}

function novo_grupo ($id_user, $nm_grupo, $publico, $msg_email, $tx_instrucao){
	global $wpdb;
	$id_grupo=$wpdb->get_var("SELECT (IFNULL(MAX(id_grupo),0) + 1) AS id_grupo FROM " . $wpdb->prefix . "loteca_grupo");
	$sql=$wpdb->prepare(
	"INSERT INTO " . $wpdb->prefix . "loteca_grupo 
	 (id_grupo, id_user, nm_grupo, id_ativo, id_tipo, id_publico, tx_msg_email_rodape, tx_instrucao)
	 VALUES
	 (  %s 
	  , %s
	  , %s
	  , 0
	  , 1
	  , %s
	  , %s
	  , %s )
	;" , $id_grupo , $id_user , $nm_grupo , $publico , $msg_email , $tx_instrucao
	);
	$wpdb->query($sql);
	if ($wpdb->last_error!='') {
		return FALSE;
	} else {
		return $id_grupo;
	}	
}

function dados_participante_rodada($id_user,$id_grupo,$rodada){
	global $wpdb;
	$dados_participante=$wpdb->get_row("SELECT * FROM " .
      	 $wpdb->prefix . "loteca_participante_rodada" . 
		" WHERE id_user = " . $id_user . " AND id_grupo = " . $id_grupo . " AND rodada = " . $rodada .
		";" , OBJECT , 0);
	if($wpdb->last_error!=''){
		return 'Não foi possível recuperar os dados do usuário na rodada informada';
	}else{
		return $dados_participante;
	}
}

function atualiza_previsao_participante($id_user,$id_grupo,$rodada, $valor){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET vl_credito = %s, ind_credito_processado = 0
		WHERE id_grupo = %s
	      AND id_user = %s
		  AND rodada = %s
	", 
    $valor,
	$id_grupo,
	$id_user,
	$rodada	) );
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function atualiza_participante_rodada($id_user,$id_grupo,$rodada, $participa, $cotas, $federal){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET participa = %s , qt_cotas =  %s , id_federal = %s
		WHERE id_grupo = %s
	      AND id_user = %s
		  AND rodada = %s
	", 
    $participa, $cotas, $federal,
	$id_grupo,
	$id_user,
	$rodada	) );
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function dados_participante($id_user,$id_grupo){
	global $wpdb;
	$dados_participante=$wpdb->get_row("SELECT * FROM " .
      	 $wpdb->prefix . "loteca_participante" . 
		" WHERE id_user = " . $id_user . " AND id_grupo = " . $id_grupo .
		";" , OBJECT , 0);
	if($wpdb->last_error!=''){
		return 'Não foi possível recuperar os dados do usuário';
	}else{
		return $dados_participante;
	}
}

function atualiza_participante($id_user,$id_grupo,$apelido, $participa_sem_saldo, $padrao_cotas, $federal){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante
		SET apelido = %s, id_aposta_sem_saldo = %s , qt_cotas =  %s, id_federal = %s
		WHERE id_grupo = %s
	      AND id_user = %s
	", 
    $apelido, $participa_sem_saldo, $padrao_cotas, $federal,
	$id_grupo,
	$id_user ) );
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function tx_user($id_user,$id_grupo){
	global $wpdb;
	$tx_user=$wpdb->get_var("SELECT CONCAT(id_user, ' - ', apelido) FROM " .
      	 $wpdb->prefix . "loteca_participante" . 
		" WHERE id_user = " . $id_user . " AND id_grupo = " . $id_grupo .
		";");
	if($wpdb->last_error!=''){
		return 'Não foi possível recuperar os dados do usuário';
	}else{
		return $tx_user;
	}
}

function captura_grupos(){
	global $wpdb;
	$grupos=$wpdb->get_results("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido FROM " .
      	$wpdb->prefix . "loteca_grupo A, " . $wpdb->prefix . "loteca_participante B " . 
		" WHERE A.id_user = B.id_user " .
		" AND A.id_grupo = B.id_grupo " .
		" ORDER BY A.id_ativo ASC, B.id_grupo DESC;" , OBJECT, 0);
	return $grupos;
}

function captura_rodadas($limit,$inicio,$id_grupo=0,$usuario=0){
	global $wpdb;
		
	if($id_grupo == 0){
		$sql=$wpdb->prepare("
		SELECT B.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio, A.vl_premio_estimado 
		FROM " . $wpdb->prefix . "loteca_rodada A 
		ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT %d , %d ;" , $inicio , $limit );
	}
		
	if($id_grupo <> 0){
		if($usuario <> 0){
			$sql=$wpdb->prepare("
			SELECT A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio, COALESCE( COUNT(B.rodada) , 0 ) qt_palpites , A.vl_premio_estimado , COALESCE( MIN(C.seq_aposta) , 0 ) tem_aposta
			FROM " . $wpdb->prefix . "loteca_rodada A 
			LEFT JOIN " . $wpdb->prefix . "loteca_palpite B
			ON A.rodada = B.rodada AND B.id_grupo = %s AND B.id_user = %s
			LEFT JOIN " . $wpdb->prefix . "loteca_aposta C
			ON A.rodada = C.rodada AND C.id_grupo = %s
			GROUP BY A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio 
			ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT %d , %d ;" , $id_grupo, $usuario, $id_grupo, $inicio , $limit);
		}else{
			$sql=$wpdb->prepare("
			SELECT A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio, COALESCE( COUNT(B.rodada) , 0 ) qt_palpites , A.vl_premio_estimado , COALESCE( MIN(C.seq_aposta) , 0 ) tem_aposta
			FROM " . $wpdb->prefix . "loteca_rodada A 
			LEFT JOIN " . $wpdb->prefix . "loteca_palpite B 
			ON A.rodada = B.rodada AND B.id_grupo = %s 
			LEFT JOIN " . $wpdb->prefix . "loteca_aposta C
			ON B.rodada = C.rodada AND B.id_grupo = C.id_grupo
			GROUP BY A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio 
			ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT %d , %d ;" , $id_grupo, $inicio , $limit);
		}
	}
	$rodadas=$wpdb->get_results($sql, OBJECT, 0);
	return $rodadas;
}

function captura_boloes($admin){
// Se $admin = 1 captura boloes que o usuário administra, se $admin = 0 captura boloes que o usuário é participante
	global $wpdb;
	if ($admin==0) {
		$grupos=$wpdb->get_results("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido, B.saldo, SUM(C.saldo) saldo_grupo FROM " .
			$wpdb->prefix . "loteca_grupo A, " . $wpdb->prefix . "loteca_participante B, " . $wpdb->prefix . "loteca_participante C " . 
			" WHERE A.id_grupo = B.id_grupo " .
			" AND A.id_grupo = C.id_grupo " .
			" AND B.id_user = " . get_current_user_id() . 
			" AND A.id_ativo = 1 " . 
			" GROUP BY A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido, B.saldo" . 
			" ORDER BY B.id_grupo ASC;" , OBJECT, 0);
	}
	if ($admin==1) {
		$grupos=$wpdb->get_results("
			SELECT A.id_grupo, A.nm_grupo, A.id_ativo, " .
			"      IFNULL(B.apelido, '" . wp_get_current_user()->display_name . "'), " .
			"	   IFNULL(SUM(C.saldo) , 0 ) saldo 
			FROM " .
			$wpdb->prefix . "loteca_grupo A LEFT JOIN " . 
			$wpdb->prefix . "loteca_participante B ON " . 
			" A.id_user = B.id_user AND" . 
			" A.id_grupo = B.id_grupo LEFT JOIN " . 
			$wpdb->prefix . "loteca_participante C ON " . 
			" A.id_grupo = C.id_grupo" . 
			" WHERE (A.id_grupo IN (SELECT id_grupo
   			                         FROM " . $wpdb->prefix . "loteca_grupo 
									 WHERE id_user = " . get_current_user_id() . ")) " .
			"    OR (A.id_grupo IN (SELECT id_grupo 
									 FROM " . $wpdb->prefix . "loteca_participante 
									 WHERE id_user = " . get_current_user_id() . " AND id_admin = 1))" .
			" GROUP BY A.id_grupo, A.nm_grupo, A.id_ativo " . 
			" ORDER BY A.id_grupo ASC;" , OBJECT, 0);
	}
	return $grupos;
}

function carrega_estatisticas($time1,$time2){
	global $wpdb;
	$ano=current_time("Y");
	$ano_ant=$ano -1;
	$estatisticas=array();
//   CONFRONTOS DIRETOS 
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1      = '%s'
	      AND A.time2      = '%s'
		  AND YEAR(A.data) = '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2, $ano);
	$estatisticas['A'][$ano]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
//	echo $query;
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1      = '%s'
	      AND A.time2      = '%s'
		  AND YEAR(A.data) = '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2, $ano_ant);
	$estatisticas['A'][$ano_ant]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1      = '%s'
	      AND A.time2      = '%s'
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2, $ano_ant);
	$estatisticas['A'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//	echo $query;
//   CONFRONTOS DIRETOS (MANDO INVERTIDO)
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
	      AND A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2);
	$estatisticas['B'][$ano]=$wpdb->get_row( $query, ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
	$estatisticas['B'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
	      AND A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['B'][0]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
	      AND A.time1 = '%s'
		  AND YEAR(data) < YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 1 MANDANTE
	$estatisticas['C'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['C'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $ano_ant);
	$estatisticas['C'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 1 VISITANTE
	$estatisticas['D'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['D'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1, $ano_ant);
	$estatisticas['D'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 2 MANDANTE
	$estatisticas['E'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['E'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time1 = '%s'
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2, $ano_ant);
	$estatisticas['E'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 2 VISITANTE
	$estatisticas['F'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['F'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE A.time2 = '%s'
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2, $ano_ant);
	$estatisticas['F'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 1
	$estatisticas['G'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1,$time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['G'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1,$time1) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time1,$time1, $ano_ant);
	$estatisticas['G'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
//   JOGOS DO TIME 2
	$estatisticas['H'][$ano]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(data) = YEAR(CURRENT_DATE)
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2,$time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$estatisticas['H'][$ano_ant]=$wpdb->get_row( $wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(data) = YEAR(CURRENT_DATE) - 1
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2,$time2) , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		return $estatisticas;
	}
	$query=$wpdb->prepare( 
	"
		SELECT COUNT(*) QT_JOGOS, SUM(B.time1) VTIME1, SUM(B.empate) EMPATE, SUM(B.time2) VTIME2
		FROM " . $wpdb->prefix . "loteca_jogos A, " . $wpdb->prefix . "loteca_resultado B
		WHERE (A.time1 = '%s' OR A.time2 = '%s')
		  AND YEAR(A.data) < '%s'
		  AND A.rodada     = B.rodada
		  AND A.seq        = B.seq
	", 
    $time2,$time2, $ano_ant);
	$estatisticas['H'][0]=$wpdb->get_row( $query , ARRAY_A, 0 );
	if($wpdb->last_error!=""){
		echo $wpdb->last_query;
		return $estatisticas;
	}
	return $estatisticas;
}

function lote_query($sqls,$origem=''){
	global $wpdb;
	@mysql_query("BEGIN", $wpdb->dbh);
	if(!is_array($sqls)){
		$sql=$sqls;
		$wpdb->query($sql);
	}else{
		foreach($sqls as $sql){
			$wpdb->query($sql);
			if($wpdb->last_error!=''){break;}
		}
	}
	if ($wpdb->last_error!='') {
    // Error occured, don't save any changes
		@mysql_query("ROLLBACK", $wpdb->dbh);
//		error_log($origem . ' : ' . $sql);
		return FALSE;
	} else {
   // All ok, save the changes
		@mysql_query("COMMIT", $wpdb->dbh);
		return TRUE;
	}
}

function db_habilitarrodada($id_grupo,$rodada){
	global $wpdb;
	$rodada_atual=dadosrodada(0 , 1);
	@mysql_query("BEGIN", $wpdb->dbh);
	$sql=$wpdb->prepare(
	"
		INSERT INTO " . $wpdb->prefix . "loteca_parametro_rodada
		( id_grupo, rodada, vl_max, vl_min, tip_rateio, 
          ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra,
		  ind_libera_proc_desdobra, vl_premio_total, vl_residuo_premio , dt_inicio_palpite, dt_fim_palpite, vl_custo_total, vl_comissao,
		  vl_ajuste_topo_volante , vl_ajuste_esqu_volante )
		( SELECT %s as id_grupo, %s as rodada, vl_max, vl_min, tip_rateio, 
               ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra,
			   0 as ind_libera_proc_desdobra, 0 as vl_premio_total, 0 as vl_residuo_premio, %s as dt_inicio_palpite, %s dt_fim_palpite, vl_custo_total, vl_comissao,
			   vl_ajuste_topo_volante , vl_ajuste_esqu_volante
          FROM " . $wpdb->prefix . "loteca_parametro_rodada WHERE rodada = (SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_parametro_rodada) );
	" , $id_grupo, $rodada, $rodada_atual->dt_inicio_palpite, $rodada_atual->dt_fim_palpite);
	$wpdb->query($sql);
	$wpdb->query($wpdb->prepare(
	"
		INSERT INTO " . $wpdb->prefix ."loteca_participante_rodada 
			(rodada, id_grupo, id_user, participa, motivo, qt_cotas, vl_saldo_ant, vl_gasto, vl_credito, vl_premio, vl_saldo,
			 ind_credito_processado, vl_resgate, vl_pago_comissao, vl_pago_custo, id_federal)
			(SELECT %s as rodada, 
					%s as id_grupo, 
					id_user, 
					id_ativo as participa, 
					'' as motivo, 
					qt_cotas, 
					saldo as vl_saldo_ant, 
					0 as vl_gasto, 
					0 as vl_credito, 
					0 as vl_premio,
					saldo as vl_saldo,
					0 as ind_credito_processado,
					0 as vl_resgate,
					0 as vl_pago_comissao,
					0 as vl_pago_custo, 
					id_federal as id_federal
			FROM " . $wpdb->prefix . "loteca_participante A
			WHERE id_grupo = %s
			  and id_ativo = 1
			)
	", $rodada, $id_grupo, $id_grupo) );
	if ($wpdb->last_error!='') {
    // Error occured, don't save any changes
		@mysql_query("ROLLBACK", $wpdb->dbh);
		return FALSE;
	} else {
   // All ok, save the changes
		@mysql_query("COMMIT", $wpdb->dbh);
		return TRUE;
	}
}

function db_inclui_premio($id_grupo,$rodada,$valor){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET vl_premio = %s , vl_saldo =  vl_saldo_ant + vl_credito + %s - vl_gasto - vl_resgate - vl_pago_comissao - vl_pago_custo
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND participa = 1
	", 
    $valor, $valor,
	$id_grupo,
	$rodada ) );
	if($wpdb->last_error==""){
		return db_atualiza_saldo_participantes($id_grupo);
	}else{
		return FALSE;
	}
}

function rodada_atual_grupo($id_grupo){
	global $wpdb;
	$rodada=$wpdb->get_var("" .
	"SELECT MAX(A.rodada) rodada " .
	"FROM " . $wpdb->prefix . "loteca_parametro_rodada A," . 
	$wpdb->prefix . "loteca_participante_rodada B" .
	" WHERE A.rodada = B.rodada AND A.id_grupo = B.id_grupo AND A.id_grupo = " . $id_grupo . 
	";");
	return $rodada;
	
}

function db_inclui_gasto($id_grupo,$rodada,$valor,$tipo_jogo=0,$texto=''){
	// tipo_jogo => 0 - LOTECA , 1 - FEDERAL
	global $wpdb;
	$sql=$wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada 
		SET vl_gasto = %s * qt_cotas, vl_saldo =  vl_saldo_ant + vl_credito + vl_premio - %s * qt_cotas - vl_resgate - vl_pago_comissao - vl_pago_custo
		  , tx_gasto = %s
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND ( ( participa = 1 AND %s = 0 ) OR ( id_federal = 1 AND %s = 1 ))
	", 
    $valor, $valor, $texto,
	$id_grupo,
	$rodada, $tipo_jogo , $tipo_jogo);
	$wpdb->query( $sql  );
	if($wpdb->last_error==""){
		return db_atualiza_saldo_participantes($id_grupo);
	}else{
//		error_log(print_r($sql, true));
		return FALSE;
	}
}

function db_atualiza_saldo_participantes($id_grupo,$id_user = 0){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
		"UPDATE " . $wpdb->prefix . "loteca_participante A" .
		" SET saldo = " .
		" ( SELECT COALESCE(MAX(vl_saldo), 0 ) vl_saldo " .
		"     FROM " . $wpdb->prefix . "loteca_participante_rodada B " .
		"    WHERE A.id_user = B.id_user AND A.id_grupo = B.id_grupo" .
		"      AND B.rodada = ( SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_participante_rodada C WHERE C.id_user = B.id_user AND C.id_grupo = B.id_grupo) )" . 
		" WHERE id_grupo = %s AND %s IN (id_user , 0);"
		, $id_grupo , $id_user ) );
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function db_confirma_credito($id_grupo,$id_user,$rodada){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET ind_credito_processado = TRUE
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND id_user = %s
	", 
	$id_grupo,
	$rodada,
	$id_user ) );
	if($wpdb->last_error==""){
		return db_atualiza_saldo_participantes($id_grupo,$id_user);
	}else{
		return FALSE;
	}
}

function novarodada($id_grupo){
	global $wpdb;
	$valor=$wpdb->get_var("" .
	"SELECT rodada " .
	"FROM " . $wpdb->prefix . "loteca_rodada A" . 
	" WHERE A.rodada >  ( " . 
	"  SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . 
	"  ) ;");
	if($wpdb->last_error==""){
		return $valor;
	}else{
		return FALSE;
	}
}

function valorresgate($id_grupo,$rodada,$id_user){
	global $wpdb;
	$valor=$wpdb->get_var("" .
	"SELECT vl_resgate " .
	"FROM " . $wpdb->prefix . "loteca_participante_rodada A" . 
	" WHERE A.rodada = " . $rodada . " AND A.id_grupo = " . $id_grupo . " AND A.id_user = " . $id_user . 
	";");
	return $valor;
}

function db_inclui_resgate($id_grupo,$id_user,$rodada,$valor,$texto){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET vl_resgate = %s , vl_saldo =  vl_saldo_ant + vl_credito + vl_premio - vl_gasto - %s - vl_pago_comissao - vl_pago_custo
		  , tx_resgate = %s
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND id_user = %s
	", 
    $valor, $valor, $texto,
	$id_grupo,
	$rodada,
	$id_user ) );
	if($wpdb->last_error==""){
		return db_atualiza_saldo_participantes($id_grupo,$id_user);
	}else{
		return FALSE;
	}

}

function db_desativar_participante($id_grupo,$rodada,$id_user){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET participa = FALSE
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND id_user = %s
	", 
	$id_grupo,
	$rodada,
	$id_user ) );
	if($wpdb->last_error==""){
		$wpdb->query( $wpdb->prepare( 
		"
			UPDATE " . $wpdb->prefix . "loteca_participante
			SET id_ativo = FALSE
			WHERE id_grupo = %s
			AND id_user = %s
		", 
		$id_grupo,
		$id_user ) );
		if($wpdb->last_error==""){
			return TRUE;
		}else{
			return FALSE;
		}
	}else{
		return FALSE;
	}
}

function db_ativar_participante($id_grupo,$rodada,$id_user){
	global $wpdb;
	$sql = $wpdb->prepare( 
	" SELECT COUNT(*) FROM " . $wpdb->prefix . "loteca_participante_rodada
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND id_user = %s
	", 
	$id_grupo,
	$rodada,
	$id_user );
//	error_log($sql);
	$count=$wpdb->get_var( $sql	);
	if($count!=0){
		$sql = $wpdb->prepare( 
		"
			UPDATE " . $wpdb->prefix . "loteca_participante_rodada
			SET participa = TRUE
			WHERE id_grupo = %s
			AND rodada = %s
			AND id_user = %s
		", 
		$id_grupo,
		$rodada,
		$id_user );
		$wpdb->query( $sql );
	}else{
		$sql = $wpdb->prepare(
		"
			INSERT INTO " . $wpdb->prefix . "loteca_participante_rodada 
				(rodada, id_grupo, id_user, participa, motivo, qt_cotas, vl_saldo_ant, vl_gasto, vl_credito, vl_premio, vl_saldo,
			     ind_credito_processado, vl_resgate, vl_pago_comissao, vl_pago_custo, id_federal, tx_resgate, tx_gasto)
			(SELECT %s as rodada, 
					id_grupo, 
					id_user, 
					1 as participa, 
					'' as motivo, 
					qt_cotas, 
					saldo as vl_saldo_ant, 
					0 as vl_gasto, 
					0 as vl_credito, 
					0 as vl_premio,
					saldo as vl_saldo,
					0 as ind_credito_processado,
					0 as vl_resgate,
					0 as vl_pago_comissao,
					0 as vl_pago_custo,
					id_federal as id_federal,
					'' as tx_resgate,
					'' as tx_gasto
			FROM " . $wpdb->prefix . "loteca_participante A
			WHERE id_grupo = %s
			  and id_user = %s
			)
		", $rodada, $id_grupo, $id_user);
		$wpdb->query( $sql );
	}
	if($wpdb->last_error==""){
		$sql=$wpdb->prepare( 
		"
			UPDATE " . $wpdb->prefix . "loteca_participante
			SET id_ativo = TRUE
			WHERE id_grupo = %s
			AND id_user = %s
		", 
		$id_grupo,
		$id_user );
//		error_log($sql);
		$wpdb->query( $sql );
		if($wpdb->last_error!=""){
			return FALSE;
		}else{
			return TRUE;
		}
	}else{
		return FALSE;
	}
}

function valorcredito($id_grupo,$rodada,$id_user){
	global $wpdb;
	$valor=$wpdb->get_var("" .
	"SELECT vl_credito " .
	"FROM " . $wpdb->prefix . "loteca_participante_rodada A" . 
	" WHERE A.rodada = " . $rodada . " AND A.id_grupo = " . $id_grupo . " AND A.id_user = " . $id_user . 
	";");
	return $valor;
}

function valorgasto($id_grupo,$rodada){
	global $wpdb;
	$valor=$wpdb->get_var("" .
	"SELECT MAX(vl_gasto) " .
	"FROM " . $wpdb->prefix . "loteca_participante_rodada A" . 
	" WHERE A.rodada = " . $rodada . " AND A.id_grupo = " . $id_grupo . 
	";");
	return $valor;
}

function valorpremio($id_grupo,$rodada){
	global $wpdb;
	$valor=$wpdb->get_var("" .
	"SELECT MAX(vl_premio) " .
	"FROM " . $wpdb->prefix . "loteca_participante_rodada A" . 
	" WHERE A.rodada = " . $rodada . " AND A.id_grupo = " . $id_grupo . 
	";");
	return $valor;
}

function db_inclui_credito($id_grupo,$id_user,$rodada,$valor){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET vl_credito = %s , vl_saldo =  vl_saldo_ant + %s + vl_premio - vl_gasto - vl_resgate - vl_pago_comissao - vl_pago_custo
		WHERE id_grupo = %s
	      AND rodada = %s
		  AND id_user = %s
	", 
    $valor, $valor,
	$id_grupo,
	$rodada,
	$id_user ) );
	if($wpdb->last_error==""){
		return db_atualiza_saldo_participantes($id_grupo,$id_user);
	}else{
		return FALSE;
	}

}

function captura_resultado($rodada,$id_grupo){
	global $wpdb;
	$palpite=$wpdb->get_results("SELECT C.seq, C.time1, C.time2, C.data, C.dia, " . 
		" COALESCE(B.time1,0) vtime1, COALESCE(B.empate,0) empate, COALESCE(B.time2,0) vtime2, " .
	    "SUM(A.time1) qttime1, SUM(A.empate) qtempate, SUM(A.time2) qttime2, " .
		"SUM(IF(A.time1=1,IF(A.empate=1,IF(A.time2=1,2,3),IF(A.time2=1,3,6)),0)) peso1, " .
		"SUM(IF(A.empate=1,IF(A.time1=1,IF(A.time2=1,2,3),IF(A.time2=1,3,6)),0)) pesoe, " . 
		"SUM(IF(A.time2=1,IF(A.empate=1,IF(A.time1=1,2,3),IF(A.time1=1,3,6)),0)) peso2 " .
	    "FROM " .
		$wpdb->prefix . "loteca_jogos C LEFT JOIN " . $wpdb->prefix . "loteca_resultado B " .
		" ON B.rodada = C.rodada " . 
		" AND B.seq = C.seq " . 
		"LEFT JOIN " . $wpdb->prefix . "loteca_palpite A " .
		" ON C.rodada = A.rodada " . 
		" AND C.seq = A.seq " . 
		" AND A.id_grupo = " . $id_grupo .
		" WHERE C.rodada = " . $rodada . 
		" GROUP BY C.seq, C.time1, C.time2, C.data, B.time1, B.empate, B.time2 " .
		" ORDER BY C.seq ASC;" , OBJECT, 0);
	if($wpdb->last_error!=''){
		return FALSE;
	}else{
		return $palpite;
	}
}

function captura_palpites_rodada($id_grupo,$rodada){
	global $wpdb;
	$palpites=$wpdb->get_results("SELECT DISTINCT A.id_user, A.apelido " .
	    "FROM " .
		$wpdb->prefix . "loteca_participante A, " . $wpdb->prefix . "loteca_palpite B " . 
		" WHERE A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" AND A.id_grupo = " . $id_grupo . 
		" AND B.rodada = " . $rodada . 
		" ORDER BY A.apelido ASC;" , OBJECT, 0);
	if($wpdb->last_error!=''){
		return FALSE;
	}else{
		return $palpites;
	}
}

function captura_palpite($id_grupo,$rodada,$id_user){
	global $wpdb;
	$palpite=$wpdb->get_results("SELECT A.id_user, A.apelido , C.seq, C.time1, C.time2, C.data, B.time1 vtime1, B.empate, B.time2 vtime2 " .
		" , D.time1 rtime1, D.empate rempate, D.time2 rtime2 " .
	    "FROM " .
		$wpdb->prefix . "loteca_participante A, " . $wpdb->prefix . "loteca_palpite B, " . $wpdb->prefix . "loteca_jogos C " .
		" LEFT JOIN " . $wpdb->prefix . "loteca_resultado D ON C.seq = D.seq AND C.rodada = D.rodada " .
		" WHERE A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" AND A.id_user = " . $id_user . 
		" AND A.id_grupo = " . $id_grupo . 
		" AND B.rodada = " . $rodada . 
		" AND B.rodada = C.rodada " . 
		" AND B.seq = C.seq " . 
		" ORDER BY C.seq ASC;" , OBJECT, 0);
	if($wpdb->last_error!=''){
		return FALSE;
	}else{
		return $palpite;
	}
}

function captura_participantes($id_grupo){
	global $wpdb;
	$sql="SELECT A.id_grupo, A.id_user, A.saldo, A.apelido, A.id_ativo " .
		", F.rodada, B.participa, B.vl_saldo_ant, B.vl_gasto, B.vl_credito, B.vl_premio, B.vl_resgate, B.vl_saldo, B.ind_credito_processado " . 
		", C.user_email email , D.nm_grupo, E.user_email email_grupo, F.vl_max, F.vl_min, F.tip_rateio, F.dt_fim_palpite, SUM(G.saldo) saldo_grupo" .
		", A.id_aposta_sem_saldo, B.participa, B.id_federal, B.qt_cotas qt_cotas_rodada, A.qt_cotas" .
	    " FROM " .
		$wpdb->prefix . "loteca_participante A LEFT JOIN " . $wpdb->prefix . "loteca_participante_rodada B " .
		" ON A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" LEFT JOIN " . $wpdb->prefix . "users C " . 
		" ON A.id_user = C.ID " . 
		" LEFT JOIN " . $wpdb->prefix . "loteca_grupo D " . 
		" ON A.id_grupo = D.id_grupo " . 
		" LEFT JOIN " . $wpdb->prefix . "users E " . 
		" ON D.id_user = E.ID " . 
		" LEFT JOIN " . $wpdb->prefix . "loteca_parametro_rodada F " . 
		" ON A.id_grupo = F.id_grupo AND F.rodada = " . 
		" (SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . " )" .
		" LEFT JOIN " . $wpdb->prefix . "loteca_participante G " . 
		" ON A.id_grupo = G.id_grupo " . 
		" WHERE A.id_grupo = " . $id_grupo . 
		" AND ( B.rodada IS NULL OR B.rodada = " . 
		"  (SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_participante_rodada WHERE id_grupo = " . $id_grupo . " ) )" .
		" GROUP BY A.id_grupo, A.id_user, A.saldo, A.apelido, A.id_ativo " .
		", B.rodada, B.participa, B.vl_saldo_ant, B.vl_gasto, B.vl_credito, B.vl_premio, B.vl_resgate, B.vl_saldo, B.ind_credito_processado " . 
		", C.user_email, D.nm_grupo, E.user_email, F.vl_max, F.vl_min, F.tip_rateio, F.dt_fim_palpite" . 
		" ORDER BY A.id_ativo DESC, A.apelido ASC;";
//	echo $sql;
//	error_log($sql);
	$participantes=$wpdb->get_results( $sql, OBJECT, 0);
		return $participantes;
}

function carrega_extrato($id_grupo,$id_user=null){
	global $wpdb;
	if ($id_user==NULL){$id_user=get_current_user_id();}
	$extrato=$wpdb->get_results("SELECT A.id_grupo, A.id_user, A.saldo, A.apelido, A.id_ativo " .
		", B.rodada, B.participa, B.vl_saldo_ant, B.vl_gasto, B.vl_credito, B.vl_premio, B.vl_resgate, B.vl_saldo, B.ind_credito_processado " . 
		", C.dt_inicio_palpite, C.dt_fim_palpite " . 
	    " FROM " .
		$wpdb->prefix . "loteca_participante A LEFT JOIN " . $wpdb->prefix . "loteca_participante_rodada B " . 
		" ON A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" LEFT JOIN " . $wpdb->prefix . "loteca_rodada C " .
		" ON B.rodada = C.rodada " . 
		" WHERE A.id_grupo = " . $id_grupo . 
		" AND B.id_user = " . $id_user . 
		" ORDER BY B.rodada DESC;" , OBJECT, 0);
		return $extrato;
}

function carrega_participantes($id_grupo){
	global $wpdb;
	$participantes=$wpdb->get_results("SELECT A.id_user, A.apelido, B.user_email email, A.id_ativo, A.id_federal " .
	    "FROM " .
		$wpdb->prefix . "loteca_participante A LEFT JOIN " . $wpdb->prefix . "users B " . 
		" ON A.id_user = B.ID " . 
		" WHERE A.id_grupo = " . $id_grupo . 
		" ORDER BY A.id_ativo DESC, A.apelido ASC;" , OBJECT, 0);
		return $participantes;
}

function le_palpites($id_grupo,$rodada,$user){
	global $wpdb;
	$result=$wpdb->get_results("
	SELECT * FROM " . $wpdb->prefix . "loteca_palpite 
	WHERE id_grupo = " . $id_grupo . " AND id_user = " . $user . " AND rodada = " . $rodada . " ORDER BY seq;", ARRAY_A );
	return $result;
}

function loteca_registrar_palpite($id_grupo,$rodada,$user,$palpites){
	global $wpdb;
	$result='';
	$querys=array();
	for($seq=1;$seq<=14;$seq++){
		if(($palpites[$seq . '-1'])||($palpites[$seq . '-X'])||($palpites[$seq . '-2'])){
			$querys[]=$wpdb->prepare("
			REPLACE INTO " . $wpdb->prefix . "loteca_palpite ( rodada , seq , id_grupo , id_user , time1 , empate , time2 ) 
			VALUES ( %s , %s , %s , %s , %s , %s , %s )",
			 $rodada , $seq  , $id_grupo , $user , $palpites[$seq . '-1']?'1':'0' , $palpites[$seq . '-X']?'1':'0' , $palpites[$seq . '-2']?'1':'0' );
		}
	}
	if(count($querys)==14){
		@mysql_query("BEGIN", $wpdb->dbh);
		foreach($querys as $query){
			$wpdb->query($query);
		}
		if ($wpdb->last_error!='') {
    // Error occured, don't save any changes
			@mysql_query("ROLLBACK", $wpdb->dbh);
			$result.="PROBLEMAS AO TENTAR REGISTRAR OS PALPITES, TENTE MAIS TARDE (ERRO BANCO DE DADOS).";
		} else {
   // All ok, save the changes
			@mysql_query("COMMIT", $wpdb->dbh);
			$result.="PALPITES REGISTRADOS COM SUCESSO.";
		}
	}else{
		$result.="PROBLEMAS AO TENTAR REGISTRAR OS PALPITES, TENTE MAIS TARDE (PARAMETROS INVÁLIDOS).";
	}
	return $result;
}

function loteca_apostas($id_grupo,$rodada){
	global $wpdb;
	$query=$wpdb->prepare("
		SELECT seq_aposta, seq, time1, empate, time2
		  FROM " . $wpdb->prefix . "loteca_aposta
		 WHERE id_grupo = %s
		   AND rodada = %s
		 ORDER BY seq_aposta, seq
	",$id_grupo, $rodada);
	$result=$wpdb->get_results($query, ARRAY_A, 0);
	return $result;
}

function loteca_guardar_aposta($id_grupo,$rodada,$apostas){
	global $wpdb;
	$result='';
	$querys=array();
	$querys[]=$wpdb->prepare("
	DELETE FROM " . $wpdb->prefix . "loteca_aposta 
	WHERE rodada = %s AND id_grupo = %s",
	 $rodada , $id_grupo );
	foreach($apostas as $key => $jogo){
		foreach($jogo as $seq => $valor){
			$time1=0;
			$empate=0;
			$time2=0;
			if($valor==1||$valor==3||$valor==5||$valor==7){
				$time1=1;
			}
			if($valor==2||$valor==3||$valor==6||$valor==7){
				$empate=1;
			}
			if($valor==4||$valor==5||$valor==6||$valor==7){
				$time2=1;
			}
			$querys[]=$wpdb->prepare("
			REPLACE INTO " . $wpdb->prefix . "loteca_aposta ( rodada , id_grupo , seq_aposta, seq , time1 , empate , time2 ) 
			VALUES ( %s , %s , %s , %s , %s , %s , %s )",
			 $rodada , $id_grupo , $key , $seq , $time1 , $empate , $time2 );
		}
	}
	
	$dadosgruporodada=dadosgruporodada($id_grupo, 1, $rodada);
	
	$cotas=$dadosgruporodada->qt_cotas;
	
	$querys[]=$wpdb->prepare("
	UPDATE " . $wpdb->prefix . "loteca_parametro_rodada 
	   SET qt_cotas_aposta = %s 
	 WHERE rodada = %s
	   AND id_grupo = %s "
	, $cotas , $rodada, $id_grupo);
		@mysql_query("BEGIN", $wpdb->dbh);
		foreach($querys as $query){
			$wpdb->query($query);
		}
		if ($wpdb->last_error!='') {
    // Error occured, don't save any changes
			@mysql_query("ROLLBACK", $wpdb->dbh);
			$result.="PROBLEMAS AO TENTAR REGISTRAR A APOSTA, TENTE MAIS TARDE (ERRO BANCO DE DADOS).";
		} else {
   // All ok, save the changes
			@mysql_query("COMMIT", $wpdb->dbh);
			$result.="APOSTA ARMAZENADA COM SUCESSO.";
		}
	return $result;
}

function carrega_jogos_palpitar(){
	global $wpdb;
//	error_log ("SOLICITANDO REGISTRAR PALPITE - HORARIO ATUAL DO SISTEMA " . current_time('Y-m-d H:i:s') );
	$sql="
		SELECT * FROM " . $wpdb->prefix . "loteca_jogos WHERE rodada = (select max(rodada) from " . $wpdb->prefix . "loteca_rodada where '" . current_time('Y-m-d H:i:s') ."' between dt_inicio_palpite AND dt_fim_palpite) ORDER BY seq;
	";
	$jogos=$wpdb->get_results($sql , OBJECT, 0);
//	echo $sql;
	return $jogos;
}

function dadosgrupo($id_grupo,$admin = 0){
	global $wpdb;
	if ($admin==0) {
		$grupo=$wpdb->get_row("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, A.tx_instrucao, B.apelido, SUM(C.saldo) saldo_grupo , D.saldo saldo_participante FROM " .
			$wpdb->prefix . "loteca_grupo A, " . $wpdb->prefix . "loteca_participante B, " . $wpdb->prefix . "loteca_participante C, " . $wpdb->prefix . "loteca_participante D " . 
			" WHERE A.id_grupo = B.id_grupo " .
			" AND A.id_grupo = C.id_grupo " .
			" AND A.id_grupo = D.id_grupo " .
			" AND A.id_grupo = " . $id_grupo . 
			" AND A.id_user = B.id_user " . 
			" AND A.id_ativo = 1 " . 
			" AND D.id_user = " . get_current_user_id() . " " .
			" GROUP BY A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido, D.saldo" . 
			" ORDER BY B.id_grupo ASC;" , OBJECT, 0);
	}
	if ($admin==1) {
		$grupo=$wpdb->get_row("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, A.tx_instrucao, B.apelido, SUM(C.saldo) saldo_grupo FROM " .
			$wpdb->prefix . "loteca_grupo A, " . $wpdb->prefix . "loteca_participante B, " . $wpdb->prefix . "loteca_participante C " . 
			" WHERE A.id_grupo = B.id_grupo " .
			" AND A.id_grupo = C.id_grupo " .
			" AND A.id_user = B.id_user " . 
			" AND A.id_grupo = " . $id_grupo . 
			" GROUP BY A.id_grupo, A.nm_grupo, A.id_ativo, B.apelido " . 
			" ORDER BY B.id_grupo ASC;" , OBJECT, 0);
	}

	return $grupo;
}

function dadosrodada($rodada = 0,$admin = 0){
	global $wpdb;
//	if ($admin==1) {
		$rodada=$wpdb->get_row("SELECT " .
			"rodada, dt_inicio_palpite, dt_fim_palpite, dt_sorteio " .
			" FROM " .
			$wpdb->prefix . "loteca_rodada " . 
			" WHERE " . $rodada . " IN ( 0 , rodada ) ORDER BY rodada DESC LIMIT 1" . 
			";" , OBJECT, 0);
//	}
	return $rodada;
}

function cotas_por_faixa($id_grupo,$rodada=0){
	global $wpdb;
	$query="SELECT LIMITES.valor, SUM(X.qt_cotas) cotas ".
			"FROM " . $wpdb->prefix . "loteca_participante_rodada X,".
			"	" . $wpdb->prefix . "loteca_participante Y, ".
			"(".
			"SELECT DISTINCT CASE TRUE WHEN C.id_aposta_sem_saldo = 1 OR B.vl_saldo + B.vl_gasto > A.vl_max THEN A.vl_max ELSE B.vl_saldo + B.vl_gasto END valor ".
			"FROM " . $wpdb->prefix . "loteca_parametro_rodada A, ".
			"	" . $wpdb->prefix . "loteca_participante_rodada B, ".
			"	" . $wpdb->prefix . "loteca_participante C ".
			"WHERE A.rodada = B.rodada AND A.id_grupo = A.id_grupo ".
			"AND A.id_grupo = C.id_grupo AND B.id_user = C.id_user AND C.id_ativo = 1 ".
			"AND (A.vl_min <= B.vl_saldo + B.vl_gasto OR C.id_aposta_sem_saldo = 1 ) ".
			"AND A.rodada = ".
			"(SELECT MAX(rodada) FROM ". $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . 
			"   AND " . $rodada . " IN ( 0 , rodada ) )" . 
			"	) LIMITES ".
			"where (X.vl_saldo + X.vl_gasto >= LIMITES.valor OR Y.id_aposta_sem_saldo = 1) ".
			"AND X.id_grupo = Y.id_grupo AND X.id_user = Y.id_user ".
			"AND X.rodada = ".
			"(SELECT MAX(rodada) FROM ". $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . 
			"   AND " . $rodada . " IN ( 0 , rodada ) ) " . 
			"GROUP BY LIMITES.valor ORDER BY LIMITES.valor DESC ;";
	$faixas=$wpdb->get_results( $query , OBJECT, 0);
//	error_log($query);
	return $faixas;
}


function dadosgruporodada($id_grupo,$admin = 0, $rodada = 0){
	global $wpdb;
	$query="SELECT " .
			"A.rodada, vl_max, vl_min, tip_rateio, ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra, ind_libera_proc_desdobra , A.dt_inicio_palpite, A.dt_fim_palpite, qt_participantes_ativos, qt_participantes, D.vl_premio_estimado, A.vl_comissao, A.vl_custo_total, D.dt_sorteio, F.qt_cotas, F.qt_ok ".
			", A.vl_ajuste_topo_volante, A.vl_ajuste_esqu_volante, A.qt_cotas_aposta " .
			" FROM " .
			$wpdb->prefix . "loteca_grupo E, " . 
			$wpdb->prefix . "loteca_parametro_rodada A " . 
			" LEFT JOIN " . 
			" ( SELECT id_grupo, rodada, COUNT(id_user) qt_participantes 
			      FROM " . $wpdb->prefix . "loteca_participante_rodada 
			     GROUP BY id_grupo, rodada ) B " . 
			" ON A.id_grupo = B.id_grupo AND A.rodada = B.rodada " . 
			" LEFT JOIN " .
			"( SELECT id_grupo, rodada, COUNT(id_user) qt_participantes_ativos FROM " . $wpdb->prefix . "loteca_participante_rodada WHERE participa = 1 GROUP BY id_grupo, rodada ) C " . 
			" ON A.id_grupo = C.id_grupo AND A.rodada = C.rodada " . 
			" LEFT JOIN " . $wpdb->prefix . "loteca_rodada D " . 
			" ON A.rodada = D.rodada " . 
			" LEFT JOIN " . 
			" ( SELECT X.id_grupo, X.rodada, SUM(X.qt_cotas) qt_cotas, COUNT(X.id_user) qt_ok
			      FROM " . $wpdb->prefix . "loteca_participante_rodada X, " . $wpdb->prefix . "loteca_participante Y, " . $wpdb->prefix . "loteca_parametro_rodada Z
                 WHERE X.id_user = Y.id_user AND X.id_grupo = Y.id_grupo AND X.id_grupo = Z.id_grupo AND X.rodada = Z.rodada
				   AND X.participa = 1 AND Y.id_ativo = 1
				   AND ( ( Y.id_aposta_sem_saldo AND (X.vl_saldo + X.vl_gasto ) < Z.vl_max ) OR ( (X.vl_saldo + X.vl_gasto) >= Z.vl_max ) )
			     GROUP BY X.id_grupo, X.rodada ) F " . 
			" ON A.id_grupo = F.id_grupo AND A.rodada = F.rodada " . 
			" WHERE A.id_grupo = " . $id_grupo . 
			"   AND E.id_grupo = " . $id_grupo . 
			"   AND A.rodada = " .
			"(SELECT MAX(rodada) FROM ". $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . 
			"   AND " . $rodada . " IN ( 0 , rodada ) )" . 
//			" GROUP BY A.rodada, vl_max, vl_min, tip_rateio, ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra, ind_libera_proc_desdobra , " .
//			" A.dt_inicio_palpite, A.dt_fim_palpite, vl_premio_estimado " . 
			";";
//	if ($admin==1) {
		$grupo=$wpdb->get_row( $query , OBJECT, 0);
//	} // grupo
//	error_log($query);
	return $grupo;
}

function loteca_tipos_volante ($aposta_min, $aposta_max, $asc_desc, $opcao = 1){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	if (($asc_desc!=' ASC ') && ($asc_desc!=' DESC ')){
		return FALSE;
	}
	global $wpdb;
	$sql="";
	switch ($opcao){
		case 1:
			$sql=$wpdb->prepare("
				SELECT * FROM " . $wpdb->prefix . "loteca_valores_apostas A 
				WHERE vl_aposta BETWEEN %s AND %s 
				  AND dt_inicio = (SELECT MAX(dt_inicio) 
				                   FROM " . $wpdb->prefix . "loteca_valores_apostas B
								   WHERE A.duplos = B.duplos AND A.triplos = B.triplos)
				ORDER BY vl_aposta " . $asc_desc . ";
			", $aposta_min, $aposta_max);
			break;
		case 2:
			$sql=$wpdb->prepare("
				SELECT * FROM " . $wpdb->prefix . "loteca_valores_apostas A 
				WHERE vl_aposta BETWEEN %s AND %s 
				  AND dt_inicio = (SELECT MAX(dt_inicio) 
				                   FROM " . $wpdb->prefix . "loteca_valores_apostas B
								   WHERE A.duplos = B.duplos AND A.triplos = B.triplos)
				ORDER BY duplos + triplos DESC, vl_aposta " . $asc_desc . ";
			", $aposta_min, $aposta_max);
			break;
		case 3:
			$sql=$wpdb->prepare("
				SELECT * FROM " . $wpdb->prefix . "loteca_valores_apostas A 
				WHERE vl_aposta BETWEEN %s AND %s 
				  AND dt_inicio = (SELECT MAX(dt_inicio) 
				                   FROM " . $wpdb->prefix . "loteca_valores_apostas B
								   WHERE A.duplos = B.duplos AND A.triplos = B.triplos)
				ORDER BY triplos ASC, duplos ASC, vl_aposta " . $asc_desc . ";
			", $aposta_min, $aposta_max);
			break;
	}
//	error_log ($sql);
	$volantes=$wpdb->get_results($sql , OBJECT, 0);
	return $volantes;
}

function loteca_valor_volante ($duplos,$triplos){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$sql=$wpdb->prepare("
				SELECT vl_aposta FROM " . $wpdb->prefix . "loteca_valores_apostas A
				WHERE duplos= %s AND triplos = %s 
				  AND dt_inicio = (SELECT MAX(dt_inicio) 
				                   FROM " . $wpdb->prefix . "loteca_valores_apostas B
								   WHERE A.duplos = B.duplos AND A.triplos = B.triplos)
				;
			", $duplos, $triplos);
	$valor=$wpdb->get_var($sql);
	return $valor;
}

function loteca_acessa_grupo($id_grupo){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$ok=$wpdb->get_var("SELECT " .
		" COUNT(*) ok" .
		" FROM " .
		$wpdb->prefix . "loteca_participante " . 
		" WHERE id_grupo = " . $id_grupo . 
		"   AND id_user = " . get_current_user_id() . " " . 
		"   AND id_ativo = TRUE " . 
		";");
	return $ok;
}

function resultado_pendente(){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$pendente=$wpdb->get_var("SELECT rodada FROM " . $wpdb->prefix . "loteca_rodada WHERE rodada > 0 AND rodada not in (SELECT rodada from " . $wpdb->prefix . "loteca_resultado) and dt_sorteio <= CURRENT_DATE();");
	return $pendente;
}

function programacao_pendente(){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$pendente=$wpdb->get_var("
			SELECT MAX(A.rodada) + 1 AS rodada_pendente
			FROM " . $wpdb->prefix . "loteca_rodada A
				LEFT JOIN " . $wpdb->prefix . "loteca_rodada B
				ON B.dt_sorteio > CURRENT_DATE
			WHERE A.dt_sorteio <= CURRENT_DATE
			HAVING MIN(B.rodada) IS NULL
			UNION
			SELECT DISTINCT A.rodada AS rodada_pendente
			FROM " . $wpdb->prefix . "loteca_rodada A, " . $wpdb->prefix . "loteca_jogos B
			WHERE A.rodada = B.rodada
			AND B.link_stat = ''
			AND A.rodada = (SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_rodada)
			AND A.ts_atualizacao < SUBTIME(CURRENT_TIMESTAMP , '00:15:00.000000');"
		);
	return $pendente;
}

function loteca_admin_grupo($id_grupo){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$ok=$wpdb->get_var("SELECT " .
		" COUNT(*) ok" .
		" FROM " .
		$wpdb->prefix . "loteca_grupo " . 
		" WHERE id_grupo = " . $id_grupo . 
		"   AND id_user = " . get_current_user_id() . " " . 
		"   AND id_ativo = TRUE " . 
		";");
	if ( $ok ) {
		return $ok;
	}
	$ok=$wpdb->get_var("SELECT " .
		" COUNT(*) ok" .
		" FROM " .
		$wpdb->prefix . "loteca_participante " . 
		" WHERE id_grupo = " . $id_grupo . 
		"   AND id_user = " . get_current_user_id() . " " . 
		"   AND id_ativo = TRUE " . 
		"   AND id_admin = TRUE " . 
		";");
	return $ok;
}

function captura_grupos_abertos(){
	global $wpdb;
	return $wpdb->get_results( 
	//$wpdb->prepare( 
	"
		SELECT  A.id_grupo id_grupo, A.nm_grupo nm_grupo, A.id_user id_user, D.apelido apelido, B.user_email email, B.display_name nome, COUNT(*) qt_participante
		 FROM " . $wpdb->prefix . "loteca_grupo A
		  LEFT JOIN " . $wpdb->prefix . "loteca_participante D
		    ON A.id_user = D.id_user AND A.id_grupo = D.id_grupo
			, " . $wpdb->prefix . "users B, " . $wpdb->prefix . "loteca_participante C
		WHERE A.id_publico = 1 AND A.id_ativo = 1
		  AND A.id_user = B.ID
		  AND A.id_grupo = C.id_grupo
		  AND C.id_ativo = 1
		  AND A.id_grupo NOT IN ( SELECT id_grupo FROM " . $wpdb->prefix . "loteca_participante WHERE id_user = " . get_current_user_id() . ")
		  GROUP BY A.id_grupo, A.nm_grupo, A.id_user, D.apelido, B.user_email, B.display_name
		; 
	"
//	, $var )
	 , OBJECT, 0);
	
}

function verifica_grupo_aberto($id_grupo){
	global $wpdb;
	$count_grupo = $wpdb->get_var( 
		$wpdb->prepare( 
	"
		SELECT   COUNT(*)
		 FROM " . $wpdb->prefix . "loteca_grupo A
		WHERE A.id_publico = 1 AND A.id_ativo = 1
		  AND A.id_grupo = %s
		; 
	"
		, $id_grupo )
	 );
	 $count_participante = $wpdb->get_var( 
		$wpdb->prepare( 
	"
		SELECT   COUNT(*)
		 FROM " . $wpdb->prefix . "loteca_participante A
		WHERE A.id_grupo = %s
		  AND A.id_user = %s
		; 
	"
		, $id_grupo , get_current_user_id())
	 );
	if(($count_participante==0)&&($count_grupo==1)){
		return TRUE;
	}else{
		return FALSE;
	}
}

function inclui_solicitacao($id_grupo){
	global $wpdb;
	$query = $wpdb->prepare( 
	"
		INSERT INTO " . $wpdb->prefix . "loteca_participante (id_grupo, id_user, saldo, apelido, qt_cotas, id_ativo, id_admin)
		VALUES ( '%s' , '%s' , 0 , '%s' , 1 , 0 , 0 )
		;
	", 
	$id_grupo, get_current_user_id(), wp_get_current_user()->display_name );
	$wpdb->query( $query );
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function atualiza_parametro_rodada($id_grupo, $rodada, $vl_max, $vl_min, $tip_rateio, $ind_bolao_volante, $cota, $qt_max_zebras, $qt_min_zebras, $amplia_zebra, $vl_custo,  $vl_comissao, $topo, $esquerda){
	global $wpdb;
	if($ind_bolao_volante==''){$ind_bolao_volante=0;}
	if($amplia_zebra==''){$amplia_zebra=0;}
	$query = $wpdb->prepare(
	"
		UPDATE " . $wpdb->prefix . "loteca_parametro_rodada
		SET vl_max = %s ,
		    vl_min = %s , 
			tip_rateio = %s , 
			ind_bolao_volante = %s ,
			vl_lim_rateio = %s ,
			qt_max_zebras = %s ,
			qt_min_zebras = %s ,
			amplia_zebra = %s ,
			vl_custo_total = %s , 
			vl_comissao = %s ,
			vl_ajuste_topo_volante = %s ,
			vl_ajuste_esqu_volante = %s
		WHERE id_grupo = %s AND rodada = %s
	", $vl_max, $vl_min, $tip_rateio, $ind_bolao_volante, $cota , $qt_max_zebras, $qt_min_zebras, $amplia_zebra, $vl_custo, $vl_comissao, $topo, $esquerda, $id_grupo, $rodada );
	$wpdb->query ($query);
//	echo "QUERY PARA ATUALIZAR OS PARAMETROS: " . $query;
//	error_log( "QUERY PARA ATUALIZAR OS PARAMETROS: " . $query);
	if($wpdb->last_error==""){
		return TRUE;
	}else{
		return FALSE;
	}
}

function loteca_jogos($rodada){
	global $wpdb;
	$query=$wpdb->prepare("SELECT seq, time1, time2, data, dia, inicio, fim 
	FROM " . $wpdb->prefix ."loteca_jogos A
	WHERE rodada = %s
	ORDER BY seq
	",$rodada);
 return $wpdb->get_results( $query , ARRAY_A, 0);
 }

function ranking_rodada ($id_grupo,$rodada){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media,
 SUM(triplos) as triplos, SUM(duplos) as duplos, SUM(simples) as simples
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA, 
               SUM(triplo) as triplos, SUM(duplo) as duplos, SUM(simples) as simples FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo,
				 (CASE WHEN B.time1 + B.empate + B.time2 = 3 THEN 1 ELSE 0 END) AS triplo,
				 (CASE WHEN B.time1 + B.empate + B.time2 = 2 THEN 1 ELSE 0 END) AS duplo,
				 (CASE WHEN B.time1 + B.empate + B.time2 = 1 THEN 1 ELSE 0 END) AS simples
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                      ON C.seq = D.seq AND C.rodada = D.rodada 
         WHERE A.id_grupo = B.id_grupo 
           AND B.rodada = E.rodada
		   AND B.rodada = %s
           AND A.id_user = B.id_user 
           AND A.id_grupo = %s
           AND B.rodada = C.rodada 
           AND B.seq = C.seq 
           AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $rodada, $id_grupo);
 return $wpdb->get_results( $query , OBJECT, 0);
}

function ranking_1 ($id_grupo){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                      ON C.seq = D.seq AND C.rodada = D.rodada 
         WHERE A.id_grupo = B.id_grupo 
           AND B.rodada = E.rodada
           AND MONTH(E.dt_sorteio) = MONTH(DATE_SUB(curdate(), INTERVAL 1 MONTH))
           AND YEAR(E.dt_sorteio) = YEAR(DATE_SUB(curdate(), INTERVAL 1 MONTH))
           AND A.id_user = B.id_user 
           AND A.id_grupo = %s
           AND B.rodada = C.rodada 
           AND B.seq = C.seq 
           AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $id_grupo);
 return $wpdb->get_results( $query , OBJECT, 0);
}

function ranking_6 ($id_grupo){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                      ON C.seq = D.seq AND C.rodada = D.rodada 
         WHERE A.id_grupo = B.id_grupo 
           AND B.rodada = E.rodada
           AND E.dt_sorteio >= (DATE_SUB(curdate(), INTERVAL 6 MONTH))
           AND A.id_user = B.id_user 
           AND A.id_grupo = %s
           AND B.rodada = C.rodada 
           AND B.seq = C.seq 
           AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $id_grupo);
 return $wpdb->get_results( $query , OBJECT, 0);
}

function ranking_12 ($id_grupo){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                      ON C.seq = D.seq AND C.rodada = D.rodada 
         WHERE A.id_grupo = B.id_grupo 
           AND B.rodada = E.rodada
           AND E.dt_sorteio >= (DATE_SUB(curdate(), INTERVAL 12 MONTH))
           AND A.id_user = B.id_user 
           AND A.id_grupo = %s
           AND B.rodada = C.rodada 
           AND B.seq = C.seq 
           AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $id_grupo);
 return $wpdb->get_results( $query , OBJECT, 0);
}

function ranking_ano ($id_grupo, $ano){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                      ON C.seq = D.seq AND C.rodada = D.rodada 
         WHERE A.id_grupo = B.id_grupo 
           AND B.rodada = E.rodada
           AND YEAR(E.dt_sorteio) = %s
           AND A.id_user = B.id_user 
           AND A.id_grupo = %s
           AND B.rodada = C.rodada 
           AND B.seq = C.seq 
           AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $ano, $id_grupo);
 return $wpdb->get_results( $query , OBJECT, 0);
}


function ranking_mes ($id_grupo, $ano, $mes){
	global $wpdb;
	$query=$wpdb->prepare("
SELECT
 id_user, apelido, SUM(ACERTOS) pontos, COUNT(*) rodadas, TRUNCATE(AVG(ACERTOS),2) media,
 MAX(ACERTOS) maximo, MIN(ACERTOS) minimo, 
 TRUNCATE(SUM(MIRA),2) mira , TRUNCATE(AVG(MIRA),2) p_media
 FROM ( SELECT rodada, id_user, apelido, COUNT(*) ACERTOS, SUM(acertou/tipo) * 6 MIRA FROM
        ( SELECT B.rodada, A.id_user, A.apelido, E.dt_sorteio, 
		         ( ( B.time1  = D.time1  AND D.time1  = 1 ) OR
                   ( B.empate = D.empate AND D.empate = 1 ) OR 
                   ( B.time2  = D.time2  AND D.time2  = 1 ) ) acertou,
				 ( B.time1 + B.empate + B.time2 ) tipo
          FROM " . $wpdb->prefix . "loteca_participante A,
               " . $wpdb->prefix . "loteca_palpite B,
               " . $wpdb->prefix . "loteca_rodada E,
               " . $wpdb->prefix . "loteca_jogos C
               LEFT JOIN " . $wpdb->prefix . "loteca_resultado D 
                        ON C.seq = D.seq AND C.rodada = D.rodada 
           WHERE A.id_grupo = B.id_grupo 
             AND B.rodada = E.rodada
             AND YEAR(E.dt_sorteio) = %s
             AND MONTH(E.dt_sorteio) = %s
             AND A.id_user = B.id_user 
             AND A.id_grupo = %s
             AND B.rodada = C.rodada 
             AND B.seq = C.seq 
             AND A.id_ativo = 1
       ) X WHERE acertou = TRUE GROUP BY rodada, dt_sorteio, id_user, apelido ) Y
 GROUP BY id_user, apelido
 ORDER BY mira DESC, media DESC, RODADAS DESC, PONTOS DESC ;
                              " , $ano, $mes, $id_grupo);
// error_log($query);
 return $wpdb->get_results( $query , OBJECT, 0);
}
?>