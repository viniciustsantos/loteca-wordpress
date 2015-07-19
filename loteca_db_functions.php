<?php
// loteca_db_functions.php

// TABELA loteca_rodada
function ultima_rodada(){
	global $wpdb;
	$ultima=$wpdb->get_row("SELECT MAX(rodada) rodada FROM " .  $wpdb->prefix . "loteca_rodada;");
	return $ultima;
}

function captura_parametros(){
	global $wpdb;
	$parametros=$wpdb->get_row("SELECT limite_proc FROM " .  $wpdb->prefix . "loteca_parametro ORDER BY `data` DESC ;" , OBJECT, 0);
	return $parametros;
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
	$rodadas=$wpdb->get_results("SELECT B.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio FROM " .
      	 $wpdb->prefix . "loteca_rodada A " .
		" ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT " . $inicio . " , " . $limit ." ;" , OBJECT, 0);
	}
		
	if($id_grupo <> 0){
		if($usuario <> 0){
			$rodadas=$wpdb->get_results("SELECT A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio, COALESCE( COUNT(B.rodada) , 0 ) qt_palpites FROM " .
			$wpdb->prefix . "loteca_rodada A LEFT JOIN " . $wpdb->prefix . "loteca_palpite B" .
			" ON A.rodada = B.rodada AND B.id_grupo = " . $id_grupo . " AND B.id_user = " . $usuario . 
			" GROUP BY A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio " .
			" ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT " . $inicio . " , " . $limit ." ;" , OBJECT, 0);
		}else{
			$rodadas=$wpdb->get_results("SELECT A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio, COALESCE( COUNT(B.rodada) , 0 ) qt_palpites FROM " .
			$wpdb->prefix . "loteca_rodada A " . 
			" LEFT JOIN " . $wpdb->prefix . "loteca_palpite B " .
			" ON A.rodada = B.rodada AND B.id_grupo = " . $id_grupo . 
			" GROUP BY A.rodada, A.dt_inicio_palpite, A.dt_fim_palpite, A.dt_sorteio " .
			" ORDER BY A.rodada DESC, A.dt_sorteio DESC LIMIT " . $inicio . " , " . $limit ." ;" , OBJECT, 0);
		}
	}
	return $rodadas;
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

function altera_parametros($limite_proc){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		REPLACE INTO " . $wpdb->prefix . "loteca_parametro
		( data, limite_proc )
		VALUES ( %s, %d )
	", 
    date("Y-m-d"), 
	$limite_proc
) );
//	if($wpdb->last_error)
	return TRUE;
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
		$grupos=$wpdb->get_results("SELECT A.id_grupo, A.nm_grupo, A.id_ativo, B.apelido, SUM(C.saldo) saldo FROM " .
			$wpdb->prefix . "loteca_grupo A, " . $wpdb->prefix . "loteca_participante B, " . $wpdb->prefix . "loteca_participante C " . 
			" WHERE A.id_grupo = B.id_grupo " .
			" AND A.id_grupo = C.id_grupo " .
			" AND A.id_user = " . get_current_user_id() . 
			" AND A.id_user = B.id_user " . 
			" GROUP BY A.id_grupo, A.nm_grupo, A.id_ativo, B.apelido " . 
			" ORDER BY B.id_grupo ASC;" , OBJECT, 0);
	}
	return $grupos;
}

function carrega_estatisticas($time1,$time2){
	global $wpdb;
	$ano=date("Y");
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

function db_habilitarrodada($id_grupo,$rodada){
	global $wpdb;
	@mysql_query("BEGIN", $wpdb->dbh);
	$wpdb->query($wpdb->prepare(
	"
		INSERT INTO " . $wpdb->prefix . "loteca_parametro_rodada
		( id_grupo, rodada, vl_max, vl_min, tip_rateio, 
          ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra,
		  ind_libera_proc_desdobra, vl_premio_total, vl_residuo_premio )
		( SELECT %s as id_grupo, %s as rodada, vl_max, vl_min, tip_rateio, 
               ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra,
			   0 as ind_libera_proc_desdobra, 0 as vl_premio_total, 0 as vl_residuo_premio
          FROM wp_loteca_parametro_rodada WHERE rodada = (SELECT MAX(rodada) FROM wp_loteca_parametro_rodada) );
	" , $id_grupo, $rodada));
	$wpdb->query($wpdb->prepare(
	"
		INSERT INTO " . $wpdb->prefix ."loteca_participante_rodada 
			(rodada, id_grupo, id_user, participa, motivo, vl_saldo_ant, vl_gasto, vl_credito, vl_premio, vl_saldo,
			 ind_credito_processado, vl_resgate, vl_pago_comissao, vl_pago_custo)
			(SELECT %s as rodada, 
					%s as id_grupo, 
					id_user, 
					id_ativo as participa, 
					'' as motivo, 
					saldo as vl_saldo_ant, 
					0 as vl_gasto, 
					0 as vl_credito, 
					0 as vl_premio,
					saldo as vl_saldo,
					0 as ind_credito_processado,
					0 as vl_resgate,
					0 as vl_pago_comissao,
					0 as vl_pago_custo
			FROM wp_loteca_participante A
			WHERE id_grupo = %s
			)
	", $rodada, $id_grupo, $id_grupo) );
	if ($error) {
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

function db_inclui_gasto($id_grupo,$rodada,$valor){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada 
		SET vl_gasto = %s , vl_saldo =  vl_saldo_ant + vl_credito + vl_premio - %s - vl_resgate - vl_pago_comissao - vl_pago_custo
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

function db_atualiza_saldo_participantes($id_grupo,$id_user = 0){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
		"UPDATE " . $wpdb->prefix . "loteca_participante A" .
		" SET saldo = " .
		" ( SELECT COALESCE(MAX(vl_saldo), 0 ) vl_saldo " .
		"     FROM wp_loteca_participante_rodada B " .
		"    WHERE A.id_user = B.id_user AND A.id_grupo = B.id_grupo" .
		"      AND B.rodada = ( SELECT MAX(rodada) FROM wp_loteca_participante_rodada C WHERE C.id_user = B.id_user AND C.id_grupo = B.id_grupo) )" . 
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

function db_inclui_resgate($id_grupo,$id_user,$rodada,$valor){
	global $wpdb;
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET vl_resgate = %s , vl_saldo =  vl_saldo_ant + vl_credito + vl_premio - vl_gasto - %s - vl_pago_comissao - vl_pago_custo
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
	$wpdb->query( $wpdb->prepare( 
	"
		UPDATE " . $wpdb->prefix . "loteca_participante_rodada
		SET participa = TRUE
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
			SET id_ativo = TRUE
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
	$palpite=$wpdb->get_results("SELECT C.seq, C.time1, C.time2, C.data, " . 
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
	$participantes=$wpdb->get_results("SELECT A.id_grupo, A.id_user, A.saldo, A.apelido, A.id_ativo " .
		", B.rodada, B.participa, B.vl_saldo_ant, B.vl_gasto, B.vl_credito, B.vl_premio, B.vl_resgate, B.vl_saldo, B.ind_credito_processado " . 
	    "FROM " .
		$wpdb->prefix . "loteca_participante A LEFT JOIN " . $wpdb->prefix . "loteca_participante_rodada B " . 
		" ON A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" WHERE A.id_grupo = " . $id_grupo . 
		" AND ( B.rodada IS NULL OR B.rodada = " . 
		"  (SELECT MAX(rodada) FROM " . $wpdb->prefix . "loteca_participante_rodada WHERE id_grupo = " . $id_grupo . " ) )" .
		" ORDER BY A.id_ativo DESC, A.apelido ASC;" , OBJECT, 0);
		return $participantes;
}

function carrega_extrato($id_grupo){
	global $wpdb;
	$extrato=$wpdb->get_results("SELECT A.id_grupo, A.id_user, A.saldo, A.apelido, A.id_ativo " .
		", B.rodada, B.participa, B.vl_saldo_ant, B.vl_gasto, B.vl_credito, B.vl_premio, B.vl_resgate, B.vl_saldo, B.ind_credito_processado " . 
	    "FROM " .
		$wpdb->prefix . "loteca_participante A LEFT JOIN " . $wpdb->prefix . "loteca_participante_rodada B " . 
		" ON A.id_grupo = B.id_grupo " .
		" AND A.id_user = B.id_user " . 
		" WHERE A.id_grupo = " . $id_grupo . 
		" AND B.id_user = " . get_current_user_id() . 
		" ORDER BY B.rodada DESC;" , OBJECT, 0);
		return $extrato;
}

function carrega_participantes($id_grupo){
	global $wpdb;
	$participantes=$wpdb->get_results("SELECT A.id_user, A.apelido, B.user_email email, A.id_ativo " .
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
		if ($error) {
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

function carrega_jogos_palpitar(){
	global $wpdb;
	$jogos=$wpdb->get_results("
		SELECT * FROM " . $wpdb->prefix . "loteca_jogos WHERE rodada = (select max(rodada) from " . $wpdb->prefix . "loteca_rodada where CURRENT_TIMESTAMP between dt_inicio_palpite AND dt_fim_palpite) ORDER BY seq;
	" , OBJECT, 0);
	return $jogos;
}

function dadosgrupo($id_grupo,$admin = 0){
	global $wpdb;
	if ($admin==0) {
		$grupo=$wpdb->get_row("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido, SUM(C.saldo) saldo_grupo , D.saldo saldo_participante FROM " .
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
		$grupo=$wpdb->get_row("SELECT A.id_grupo, A.id_user, A.nm_grupo, A.id_ativo, B.apelido, SUM(C.saldo) saldo_grupo FROM " .
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
	if ($admin==1) {
		$rodada=$wpdb->get_row("SELECT " .
			"rodada, dt_inicio_palpite, dt_fim_palpite, dt_sorteio " .
			" FROM " .
			$wpdb->prefix . "loteca_rodada " . 
			" WHERE " . $rodada . " IN ( 0 , rodada ) ORDER BY rodada DESC LIMIT 1" . 
			";" , OBJECT, 0);
	}
	return $rodada;
}

function dadosgruporodada($id_grupo,$admin = 0, $rodada = 0){
	global $wpdb;
	if ($admin==1) {
		$grupo=$wpdb->get_row("SELECT " .
			"rodada, vl_max, vl_min, tip_rateio, ind_bolao_volante, vl_lim_rateio, qt_max_zebras, qt_min_zebras, amplia_zebra, ind_libera_proc_desdobra " .
			" FROM " .
			$wpdb->prefix . "loteca_parametro_rodada " . 
			" WHERE id_grupo = " . $id_grupo . 
			"   AND rodada = " .
			"(SELECT MAX(rodada) FROM ". $wpdb->prefix . "loteca_parametro_rodada WHERE id_grupo = " . $id_grupo . 
			"   AND " . $rodada . " IN ( 0 , rodada ) )" . 
			";" , OBJECT, 0);
	}
	return $grupo;
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
	$pendente=$wpdb->get_var("SELECT rodada FROM " . $wpdb->prefix . "loteca_rodada WHERE rodada not in (SELECT rodada from " . $wpdb->prefix . "loteca_resultado) and dt_sorteio <= CURRENT_DATE();");
	return $pendente;
}

function programacao_pendente(){
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	global $wpdb;
	$pendente=$wpdb->get_var("
			SELECT MAX(A.rodada) + 1 AS rodada_anterior, MIN(B.rodada) AS nova_rodada
			FROM " . $wpdb->prefix . "loteca_rodada A
				LEFT JOIN " . $wpdb->prefix . "loteca_rodada B
				ON B.dt_sorteio >= CURRENT_DATE
			WHERE A.dt_sorteio <= CURRENT_DATE
			HAVING nova_rodada IS NULL;"
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
		VALUES ( '%s' , '%s' , 0 , '%s' , 0 , 0 , 0 )
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

?>