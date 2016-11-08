<?php
// Verifica banco de dados e cria ou atualiza se necessário
global $loteca_db_version;

// Version 1.23
// - Incluido campo quantidade de acertadores e valor do premio
// - Incluidas tabelas para histórico completo dos lançamentos
// Version 1.22
// - Incluida chave unica para nm_grupo
// Version 1.21
// - Incluido quantidade de cotas da aposta
// Version 1.20
// - Incluido timestamp de atualizacao (ts_alt) em diversas tabelas
// - Incluido inicio da tabela de valores das apostas

$loteca_db_version = '1.23';

function loteca_db_install(){
	global $wpdb;
	global $loteca_db_version;
	$charset_collate = $wpdb->get_charset_collate();
// ------------------- LOTECA_VALORES_APOSTAS
	$table_name = $wpdb->prefix . 'loteca_valores_apostas';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
				duplos tinyint(1) NOT NULL COMMENT 'Quantidade de duplos',
				triplos tinyint(1) NOT NULL COMMENT 'Quantidade de triplos',
				dt_inicio date NOT NULL COMMENT 'data de inicio dos valores das apostas',
				ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
				vl_aposta decimal(10,2) NOT NULL COMMENT 'Valor da aposta', PRIMARY KEY(duplos,triplos,dt_inicio)
	) $charset_collate ENGINE = INNODB COMMENT = 'Valores vigentes das apostas';";
	$sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 0 , '2016-01-01' , 2 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 2 , 0 , '2016-01-01' , 4 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 3 , 0 , '2016-01-01' , 8 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 4 , 0 , '2016-01-01' , 16 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 5 , 0 , '2016-01-01' , 32 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 6 , 0 , '2016-01-01' , 64 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 7 , 0 , '2016-01-01' , 128 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 8 , 0 , '2016-01-01' , 256 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 9 , 0 , '2016-01-01' , 512 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 1 , '2016-01-01' , 3 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 1 , '2016-01-01' , 6 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 2 , 1 , '2016-01-01' , 12 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 3 , 1 , '2016-01-01' , 24 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 4 , 1 , '2016-01-01' , 48 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 5 , 1 , '2016-01-01' , 96 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 6 , 1 , '2016-01-01' , 192 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 7 , 1 , '2016-01-01' , 384 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 8 , 1 , '2016-01-01' , 768 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 2 , '2016-01-01' , 9 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 2 , '2016-01-01' , 18 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 2 , 2 , '2016-01-01' , 36 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 3 , 2 , '2016-01-01' , 72 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 4 , 2 , '2016-01-01' , 144 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 5 , 2 , '2016-01-01' , 288 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 6 , 2 , '2016-01-01' , 576 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 3 , '2016-01-01' , 27 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 3 , '2016-01-01' , 54 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 2 , 3 , '2016-01-01' , 108 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 3 , 3 , '2016-01-01' , 216 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 4 , 3 , '2016-01-01' , 432 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 5 , 3 , '2016-01-01' , 864 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 4 , '2016-01-01' , 81 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 4 , '2016-01-01' , 162 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 2 , 4 , '2016-01-01' , 324 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 3 , 4 , '2016-01-01' , 648 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 5 , '2016-01-01' , 243 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 1 , 5 , '2016-01-01' , 486 );";
    $sql[] = "REPLACE INTO $table_name (duplos, triplos, dt_inicio, vl_aposta) VALUES ( 0 , 6 , '2016-01-01' , 729 );";
// ------------------- LOTECA_PARAMETRO
	$table_name = $wpdb->prefix . 'loteca_parametro';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
                data timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp da última atualização',
		limite_proc int(11) NOT NULL COMMENT 'Limite do processamento de desdobramento por volante', PRIMARY KEY(data)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros gerais do processamento';";
// ------------------- LOTECA_RODADA
	$table_name = $wpdb->prefix . 'loteca_rodada';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		dt_inicio_palpite datetime NOT NULL COMMENT 'Data e hora em que os palpites podem começar a serem registrados',
		dt_fim_palpite datetime NOT NULL COMMENT 'Data e hora em que terminam os palpites',
		dt_sorteio date NOT NULL COMMENT 'Data em que a Loteria divulga os resultados oficiais',
		vl_premio_estimado decimal(10,2) NOT NULL COMMENT 'Valor estimado do prêmio',
		ts_atualizacao datetime NOT NULL COMMENT 'Timestamp de atualizaçao de informações da rodada', PRIMARY KEY(rodada)
	) $charset_collate ENGINE = INNODB COMMENT = 'Informações sobre os concursos da Loteria';";
// ------------------- LOTECA_JOGOS
	$table_name = $wpdb->prefix . 'loteca_jogos';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		time1 varchar(60) NOT NULL COMMENT 'Time mandante',
		time2 varchar(60) NOT NULL COMMENT 'Time desafiante',
		link_stat varchar(1024) NOT NULL COMMENT 'Link para estatisticas da CEF',
		data date NOT NULL COMMENT 'Dia em que acontecerá o jogo',
		dia varchar(16) NOT NULL COMMENT 'Dia da semana SABADO ou DOMINGO',
		qt_acertadores_14 int(11) NOT NULL DEFAULT 0 COMMENT 'Quantidade de acertadores de 14 jogos',
		qt_acertadores_13 int(11) NOT NULL DEFAULT 0 COMMENT 'Quantidade de acertadores de 13 jogos',
		vl_premio_14 decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Valor do prêmio para 14 acertos',
		vl_premio_13 decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Valor do prêmio para 13 acertos',
		inicio time NOT NULL COMMENT 'Horário previsto para início da partida',
		fim time NOT NULL COMMENT 'Horário previsto para término da partida', PRIMARY KEY(rodada,seq)
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos jogos de cada rodada da Loteria';";
// ------------------- LOTECA_GRUPO
	$table_name = $wpdb->prefix . 'loteca_grupo';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int(11) NOT NULL COMMENT 'Número identificador do grupo',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		id_user int(11) NOT NULL COMMENT 'Número identificador do usuário que administra o grupo',
		nm_grupo varchar(128) NOT NULL COMMENT 'Nome do grupo',
		id_user_subadmin int(11) NOT NULL COMMENT 'Número identificador do usuário autorizado pelo administrador com funções administrativas',
		id_ativo tinyint(1) NOT NULL COMMENT 'Indicador de grupo ativo',
		id_publico tinyint(1) NOT NULL COMMENT 'Se o grupo for publico ele passa a aceitar inscrições de outros usuários cadastrados no site',
		vl_comissao decimal(10,2) NOT NULL COMMENT 'Valor da comissão cobrada pelo administrador de cada participante',
		vl_custo decimal(10,2) NOT NULL COMMENT 'Valor do custo do administrador a ser rateado por igual entre os participantes',
		tx_email varchar(128) NOT NULL COMMENT 'Email utilizado para receber informações dos participantes',
		id_email_ssl tinyint(1) NOT NULL COMMENT 'Indicador de Servidor seguro do email utilizado para receber informações dos participantes',
		tx_email_serv varchar(128) NOT NULL COMMENT 'Servidor do email utilizado para receber informações dos participantes',
		tx_email_tip_serv varchar(128) NOT NULL COMMENT 'Tipo do servidor [IMAP/POP3] do email utilizado para receber informações dos participantes',
		tx_email_porta varchar(128) NOT NULL COMMENT 'Parta do servidor do email utilizado para receber informações dos participantes',
		id_tipo tinyint(1) NOT NULL COMMENT 'Indicador de tipo de grupo 1 - Normal, 2 - Especial, 3 - Premium',
		tx_msg_email_rodape varchar(2048) NOT NULL DEFAULT '' COMMENT 'Mensagem a ser utilizada nos rodapés de todos os emails enviados aos integrantes do grupo', 
		tx_instrucao varchar(2048) NOT NULL DEFAULT '' COMMENT 'Mensagem com as instruções para participar do bolão', PRIMARY KEY(id_grupo), UNIQUE KEY `nm_grupo` (nm_grupo),
	) $charset_collate ENGINE = INNODB COMMENT = 'Detalhes do grupo do bolão';";
// ------------------- LOTECA_PARTICIPANTE
	$table_name = $wpdb->prefix . 'loteca_participante';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário que participa do grupo',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		apelido varchar(128) NOT NULL COMMENT 'Apelido do participante no grupo',
		saldo decimal (10,2) NOT NULL COMMENT 'Saldo atualizado em reais do participante',
		qt_cotas smallint(6) NOT NULL COMMENT 'Quantidade de cotas padrão do participante',
		id_aposta_sem_saldo tinyint(1) NOT NULL COMMENT 'Indicador de participante mesmo sem saldo',
		id_federal tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indicador de participante do bolao da federal',
		id_ativo tinyint(1) NOT NULL COMMENT 'Indicador de participante ativo',
		id_admin tinyint(1) NOT NULL COMMENT 'Indicador de participante com status de administrador',
		id_excluido tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indica se o participante foi excluído', PRIMARY KEY(id_grupo,id_user), UNIQUE KEY `apelido` (id_grupo,apelido),
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos participantes do grupo de bolão';";
// ------------------- LOTECA_PARTICIPANTE_RODADA
	$table_name = $wpdb->prefix . 'loteca_participante_rodada';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		participa tinyint(1) NOT NULL COMMENT 'Indica se está participando ou não do concurso',
		id_federal tinyint(1) NOT NULL COMMENT 'Indica se está participando ou não do bolao da federal nesta rodada',
		motivo varchar(128) NOT NULL COMMENT 'Descreve o motivo e quem retirou o particante',
		qt_cotas smallint(6) NOT NULL COMMENT 'Quantidade de cotas do participante na rodada',
		vl_saldo_ant decimal (10,2) NOT NULL COMMENT 'Saldo antes de processar a rodada',
		vl_gasto decimal (10,2) NOT NULL COMMENT 'Valor do gasto do participante na rodada',
		vl_credito decimal (10,2) NOT NULL COMMENT 'Valor dos depósitos do participante no periodo',
		vl_premio decimal (10,2) NOT NULL COMMENT 'Valor da premiacao utilizada para credito no saldo do participante',
		vl_resgate decimal (10,2) NOT NULL COMMENT 'Valor de resgate efetuado pelo participante',
		vl_pago_comissao decimal (10,2) NOT NULL COMMENT 'Valor de comissao pago pelo participante ao administrador',
		vl_pago_custo decimal (10,2) NOT NULL COMMENT 'Valor do rateio do custo do bolão',
		tx_gasto varchar(128) NOT NULL COMMENT 'Descreve o motivo do gasto',
		tx_resgate varchar(128) NOT NULL COMMENT 'Descreve o motivo do resgate',
		vl_saldo decimal (10,2) NOT NULL COMMENT 'Valor do saldo resultante',
		ind_credito_processado tinyint(1) NOT NULL COMMENT 'Indica que o valor do credito já foi verificado e processado', PRIMARY KEY(rodada,id_grupo,id_user)
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos participantes de cada concurso, pode um participante optar por não participar ou o administrador retirá-lo de um concurso especifico por algum motivo';";
// ------------------- LOTECA_LANCAMENTOS
	$table_name = $wpdb->prefix . 'loteca_lancamentos';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		tip_lancamento smallint(6) NOT NULL COMMENT 'Codigo do Tipo do lançamento',
		tx_lancamento varchar(128) NOT NULL COMMENT 'Descrição do Tipo do lançamento', PRIMARY KEY(tip_lancamento)
	) $charset_collate ENGINE = INNODB COMMENT = 'Tipos de Lançamento';";
// ------------------- LOTECA_LANCAMENTOS_PARTICIPANTE
	$table_name = $wpdb->prefix . 'loteca_lancamentos_participante';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário',
		seq_lancamento int(11) NOT NULL DEFAULT COMMENT 'Sequencial do lançamento',
		ts_inc timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP COMMENT 'TimeStamp da inclusao',
		ts_confirma timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP COMMENT 'TimeStamp da confirmacao',
		tip_lancamento smallint(6) NOT NULL COMMENT 'Tipo do lançamento',
		ind_confirmado tinyint (1) NOT NULL COMMENT 'Lançamento confirmado pelo sistema ou pelo administrador',
		usu_inc int (11) NOT NULL COMMENT 'Usuário de inclusao - 0 = Sistema',
		usu_confirma int (11) NOT NULL COMMENT 'Usuário de confirmação - 0 = Sistema',
		vl_ant decimal (10,2) NOT NULL COMMENT 'Saldo anterior ao lançamento',
		vl_lancamento decimal (10,2) NOT NULL COMMENT 'Valor do lançamento',
		vl_final decimal (10,2) NOT NULL COMMENT 'Saldo após o lançamento', PRIMARY KEY(id_grupo,id_user,seq_lancamento)
	) $charset_collate ENGINE = INNODB COMMENT = 'Lançamentos de um participante em um grupo';";
// ------------------- LOTECA_PARAMETRO_RODADA
	$table_name = $wpdb->prefix . 'loteca_parametro_rodada';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		vl_max decimal (10,2) NOT NULL COMMENT 'Valor máximo a ser utilizado de cada participante no concurso',
		vl_min decimal (10,2) NOT NULL COMMENT 'Valor mínimo a ser utilizado de cada participante no concurso',
		tip_rateio smallint(6) NOT NULL COMMENT '1 - mínimo valor acima da média; 2 - máximo valor abaixo da média ; 3 - máximo valor ; 4 - mínimo valor;',
		ind_bolao_volante tinyint (1) NOT NULL COMMENT 'Indica se os volantes terão a marcação de divisão de cotas para bolão',
		vl_lim_rateio decimal (10,2) NOT NULL COMMENT 'Valor mínimo por cota em cada volante',
		qt_max_zebras smallint(6) NOT NULL COMMENT 'Quantidade máxima de zebras por volante',
		qt_min_zebras smallint(6) NOT NULL COMMENT 'Quantidade mínima de zebras por volante',
		amplia_zebra tinyint(1) NOT NULL COMMENT 'Amplia a possibilidade de zebras não selecionadas nos palpites',
		ind_libera_proc_desdobra tinyint(1) NOT NULL COMMENT 'Indica que o administrador do grupo liberou o processamento dos desdobramentos',
		vl_custo_total decimal (10,2) NOT NULL COMMENT 'Valor total do custo para processamento do jogo',
		vl_comissao decimal (10,2) NOT NULL COMMENT 'Valor da comissão cobrada pelo administrador de cada participante',
		vl_gasto_total decimal (10,2) NOT NULL COMMENT 'Valor total gasto nas apostas',
		vl_premio_total decimal (10,2) NOT NULL COMMENT 'Valor total do prêmio recebido na rodada',
		vl_residuo_premio decimal (10,2) NOT NULL COMMENT 'Valor residual do prêmio após distribuição entre os participantes',
		dt_inicio_palpite datetime NOT NULL COMMENT 'Data e hora em que os palpites podem começar a serem registrados para o grupo',
		vl_ajuste_topo_volante decimal (4,2) NOT NULL COMMENT 'Valor de ajuste no topo da impressao do volante',
		vl_ajuste_esqu_volante decimal (4,2) NOT NULL COMMENT 'Valor de ajuste na esquerda da impressao do volante',
		qt_cotas_aposta smallint (6) NOT NULL COMMENT 'Quantidade de cotas participantes da aposta',
		dt_fim_palpite datetime NOT NULL COMMENT 'Data e hora em que terminam os palpites para o grupo', PRIMARY KEY(rodada,id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros do grupo para o concurso';";
// ------------------- LOTECA_PALPITE
	$table_name = $wpdb->prefix . 'loteca_palpite';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso', PRIMARY KEY(rodada,seq,id_grupo,id_user)
	) $charset_collate ENGINE = INNODB COMMENT = 'Palpite do participante do grupo em cada jogo da rodada';";
// ------------------- LOTECA_FIXO
	$table_name = $wpdb->prefix . 'loteca_palpite_fixo';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso', PRIMARY KEY(rodada,seq,id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Palpite fixo do grupo em cada jogo da rodada';";
// ------------------- LOTECA_RESULTADO
	$table_name = $wpdb->prefix . 'loteca_resultado';
//	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso', PRIMARY KEY(rodada,seq)
	) $charset_collate ENGINE = INNODB COMMENT = 'Resultado de cada jogo da rodada';";
// ------------------- LOTECA_APOSTA
	$table_name = $wpdb->prefix . 'loteca_aposta';
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		seq_aposta smallint (6) NOT NULL COMMENT 'Sequencial da aposta',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		ts_alt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'TimeStamp da ultima alteracao',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso', PRIMARY KEY(rodada,id_grupo,seq_aposta,seq)
	) $charset_collate ENGINE = INNODB COMMENT = 'Apostas do grupo';";
// ------------------
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$sql_ret=dbDelta( $sql );
	update_option( 'erro_criar_tabelas' , serialize($sql_ret));
	update_option( 'loteca_db_version', $loteca_db_version );
}

// Version 1.19
// - Incluido ajustes para impressao do volante
// Version 1.18
// - Incluido texto para resgate e para gasto
// Version 1.17
// - Incluido indicador de participante do bolao da federal
// Version 1.16
// - Incluido campo para data e hora da atualização da programação dos jogos
// Version 1.15
// - Incluido campo para link das estatisticas da caixa
// Version 1.14
// - Retiradas tabelas de processamento dos desdobramento não mais utilizadas
// - Incluida tabela das apostas do grupo
// Version 1.13
// - Retirado DROP PRIMARY KEY
// Version 1.12
// - PRIMARY KEYS e INDEX colocados ultima linha da definição dos campos para tentar resolver um bug do dbDelta
// Version 1.11
// - Inclui campos para "email para receber comandos dos participantes do bolao"
// Version 1.10
// - Inclui campo para "apostar sem saldo"
// Version 1.9
// - Inclui campo para descrever o funcionamento do bolão
// Version 1.8
// - Inclui campos de comissao e custo por rodada
// Version 1.7
// - Inclui tabela com valores das apostas por tipo de jogo
// Version 1.6
// - Inclui texto da mensagem do rodape do email aos integrantes do grupo
// Version 1.5
// - Inclui valor estimado de premio para rodada
// Version 1.4
// - Inclui inicio e fim dos palpites por grupo
// Version 1.3
// - Inclui indicador de participante excluído
// ============================================================================
// Version 1.2
// - Inclui subadministrador
// ============================================================================
// Version 1.1 
// - Inclui parametro do grupo para ser visível para novos usuários
// - Inclui parametro de custo por usuário por rodada (Comissao)
// - Inclui gasto fixo por rodada a ser rateado por cada usuário (Custo)
// ============================================================================


?>