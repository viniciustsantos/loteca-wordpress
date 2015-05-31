<?php

function captura_survey($rodada){
    global $mysql_link;
	$sid='';
	$qid='';
	$gid='';
	$sql="SELECT DISTINCT C.sid sid, C.parent_qid qid, C.gid gid
            FROM `ls_surveys` A,
                 `ls_surveys_languagesettings` B,
                 `ls_questions` C
           where C.relevance is NULL
             and C.scale_id = 0
             and C.parent_qid <> 0 
             and B.surveyls_title like '%loteca%$rodada%' 
             and B.surveyls_language = 'pt-BR'
             and A.sid = B.surveyls_survey_id
             and A.sid = C.sid";
	$result=query_survey($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$sid=$row['sid'];
		$qid=$row['qid'];
		$gid=$row['gid'];
	}
	$insert="SELECT rodada, 
			          id_grupo, 
					  participante , 
					  seq, 
					  CAST(time1 AS UNSIGNED) AS time1, 
					  CAST(empate AS UNSIGNED) AS empate, 
					  CAST(time2 AS UNSIGNED) AS time2, 
					  certeza 
                 from (";
	for ($x=1;$x<=14;$x++){
		if($x==1){
			$insert=$insert
			.
			"SELECT '$rodada' as 'rodada' , '1' as 'id_grupo' , email 'participante' , " . $x . " 'seq', " . $sid . "X" . $gid . "X" . $qid . $x . "_1 AS 'time1', " . $sid . "X" . $gid . "X" . $qid . $x . "_2 as 'empate', " . $sid . "X" . $gid . "X" . $qid . $x . "_3 as 'time2' , 0 'certeza'
               FROM `ls_survey_" . $sid . "`, `ls_tokens_" . $sid . "`
              WHERE `ls_survey_" . $sid . "`.token = `ls_tokens_" . $sid . "`.token AND `ls_survey_" . $sid . "`.submitdate IS NOT NULL
             UNION
			 ";
		}else{
			if($x==14){
			$insert=$insert
			.
			"SELECT '$rodada' , '1' , email , " . $x . " , " . $sid . "X" . $gid . "X" . $qid . $x . "_1 , " . $sid . "X" . $gid . "X" . $qid . $x . "_2 , " . $sid . "X" . $gid . "X" . $qid . $x . "_3 , 0
               FROM `ls_survey_" . $sid . "`, `ls_tokens_" . $sid . "`
              WHERE `ls_survey_" . $sid . "`.token = `ls_tokens_" . $sid . "`.token AND `ls_survey_" . $sid . "`.submitdate IS NOT NULL
              ) x";
			}else{
			$insert=$insert
			.
			"SELECT '$rodada' , '1' , email , " . $x . " , " . $sid . "X" . $gid . "X" . $qid . $x . "_1 , " . $sid . "X" . $gid . "X" . $qid . $x . "_2 , " . $sid . "X" . $gid . "X" . $qid . $x . "_3 , 0
               FROM `ls_survey_" . $sid . "`, `ls_tokens_" . $sid . "`
              WHERE `ls_survey_" . $sid . "`.token = `ls_tokens_" . $sid . "`.token AND `ls_survey_" . $sid . "`.submitdate IS NOT NULL
             UNION
			 ";
			}
		}
	}
	$insert.= " ORDER BY rodada, id_grupo, participante";
	$result=query_survey($insert);
	$delete="DELETE FROM wp_loteca_palpite WHERE id_grupo=1 AND rodada = $rodada;";
	query($delete);
	$part_ant='';
	while ($row = mysqli_fetch_assoc($result)) {
		$id_user=busca_user($row['participante'],$row['id_grupo'] );
		if($row['participante']!=$part_ant){
			$part_ant=$row['participante'];
			echo "# PALPITE DE " . $part_ant . "\n";
		}
		$insere="INSERT INTO wp_loteca_palpite ( rodada, id_grupo, id_user, seq, time1, empate, time2 ) VALUES (" .
			$row['rodada'] . " , " . $row['id_grupo'] . " , '". $id_user . "' , ". $row['seq'] . " , " . $row['time1'] . " , " . $row['empate'] . " , " . $row['time2'] . " )";
		query($insere);
	}
}

function busca_user($email,$id_grupo){
	$sql="SELECT id_user FROM wp_users a, wp_loteca_participante b WHERE a.ID=b.id_user AND b.id_grupo = " . $id_grupo . " AND a.user_email = '". $email . "' ;";
	$result=query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		return $row['id_user'];
	}
}

function query_survey($sql){
	global $mysql_link_survey;
	conecta_survey();
	$result = $mysql_link_survey->query($sql);
	if (!$result) {
		echo "Erro do banco de dados, não foi possível consultar o banco de dados\n";
		echo 'Erro MySQL: ' . mysqli_error($mysql_link_survey);
		exit;
	}
	return $result;
}

function conecta_survey(){
	global $mysql_host, $mysql_user, $mysql_password, $mysql_link_survey;
    $mysql_dbname='hg7ne213_lime258';
    $mysql_host='vinicius.santos.nom.br';
	$mysql_user='hg7ne213_loteca';
	$mysql_password='senhaloteca123';
	if (!isset($mysql_link_survey)){
		if (!$mysql_link_survey = mysqli_connect($mysql_host, $mysql_user, $mysql_password , $mysql_dbname)) {
			echo "Não foi possível conectar ao mysql! $mysql_host $mysql_user $mysql_password $mysql_dbname";
			exit;
		}
	}
}

include_once 'loteca_geral.php';
prepara_ambiente();
$hora = date("Y-m-d H:i:s");
echo "##################################### " . $hora . " ############################ INICIO #\n";
config_conexao_mysql();

$rodada=captura_proxima_rodada(); // ok
if($rodada==NULL){exit("Problemas na captura do código da rodada atual");}
captura_survey($rodada);
$hora = date("Y-m-d H:i:s");
echo "##################################### " . $hora . " ############################ FIM ####\n";

?>