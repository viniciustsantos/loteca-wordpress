<?php
// Verifica banco de dados e cria ou atualiza se necessário
global $loteca_db_version;

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

$loteca_db_version = '1.3';

function loteca_db_install(){
	global $wpdb;
	global $loteca_db_version;
	$charset_collate = $wpdb->get_charset_collate();
// ------------------- LOTECA_PARAMETRO
	$table_name = $wpdb->prefix . 'loteca_parametro';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
                data timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp da última atualização',
		limite_proc int(11) NOT NULL COMMENT 'Limite do processamento de desdobramento por volante',
		PRIMARY KEY (data)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros gerais do processamento';";
// ------------------- LOTECA_RODADA
	$table_name = $wpdb->prefix . 'loteca_rodada';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		dt_inicio_palpite datetime NOT NULL COMMENT 'Data e hora em que os palpites podem começar a serem registrados',
		dt_fim_palpite datetime NOT NULL COMMENT 'Data e hora em que terminam os palpites',
		dt_sorteio date NOT NULL COMMENT 'Data em que a Loteria divulga os resultados oficiais',
		PRIMARY KEY (rodada)
	) $charset_collate ENGINE = INNODB COMMENT = 'Informações sobre os concursos da Loteria';";
// ------------------- LOTECA_JOGOS
	$table_name = $wpdb->prefix . 'loteca_jogos';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		time1 varchar(60) NOT NULL COMMENT 'Time mandante',
		time2 varchar(60) NOT NULL COMMENT 'Time desafiante',
		data date NOT NULL COMMENT 'Dia em que acontecerá o jogo',
		dia varchar(16) NOT NULL COMMENT 'Dia da semana SABADO ou DOMINGO',
		inicio time NOT NULL COMMENT 'Horário previsto para início da partida',
		fim time NOT NULL COMMENT 'Horário previsto para término da partida',
		PRIMARY KEY (rodada,seq)
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos jogos de cada rodada da Loteria';";
// ------------------- LOTECA_GRUPO
	$table_name = $wpdb->prefix . 'loteca_grupo';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int(11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int(11) NOT NULL COMMENT 'Número identificador do usuário que administra o grupo',
		nm_grupo varchar(128) NOT NULL COMMENT 'Nome do grupo',
		id_user_subadmin int(11) NOT NULL COMMENT 'Número identificador do usuário autorizado pelo administrador com funções administrativas',
		id_ativo tinyint(1) NOT NULL COMMENT 'Indicador de grupo ativo',
		id_publico tinyint(1) NOT NULL COMMENT 'Se o grupo for publico ele passa a aceitar inscrições de outros usuários cadastrados no site',
		vl_comissao decimal(10,2) NOT NULL COMMENT 'Valor da comissão cobrada pelo administrador de cada participante',
		vl_custo decimal(10,2) NOT NULL COMMENT 'Valor do custo do administrador a ser rateado por igual entre os participantes',
		id_tipo tinyint(1) NOT NULL COMMENT 'Indicador de tipo de grupo 1 - Normal, 2 - Especial, 3 - Premium',
		PRIMARY KEY (id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Detalhes do grupo do bolão';";
// ------------------- LOTECA_PARTICIPANTE
	$table_name = $wpdb->prefix . 'loteca_participante';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário que participa do grupo',
		apelido varchar(128) NOT NULL COMMENT 'Apelido do participante no grupo',
		saldo decimal (10,2) NOT NULL COMMENT 'Saldo atualizado em reais do participante',
		qt_cotas smallint(6) NOT NULL COMMENT 'Quantidade de cotas padrão do participante',
		id_ativo tinyint(1) NOT NULL COMMENT 'Indicador de participante ativo',
		id_admin tinyint(1) NOT NULL COMMENT 'Indicador de participante com status de administrador',
		id_excluido tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indica se o participante foi excluído',
		PRIMARY KEY (id_grupo,id_user),
		UNIQUE KEY `apelido` (id_grupo,apelido),
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos participantes do grupo de bolão';";
// ------------------- LOTECA_PARTICIPANTE_RODADA
	$table_name = $wpdb->prefix . 'loteca_participante_rodada';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário',
		participa tinyint(1) NOT NULL COMMENT 'Indica se está participando ou não do concurso',
		motivo varchar(128) NOT NULL COMMENT 'Descreve o motivo e quem retirou o particante',
		qt_cotas smallint(6) NOT NULL COMMENT 'Quantidade de cotas do participante na rodada',
		vl_saldo_ant decimal (10,2) NOT NULL COMMENT 'Saldo antes de processar a rodada',
		vl_gasto decimal (10,2) NOT NULL COMMENT 'Valor do gasto do participante na rodada',
		vl_credito decimal (10,2) NOT NULL COMMENT 'Valor dos depósitos do participante no periodo',
		vl_premio decimal (10,2) NOT NULL COMMENT 'Valor da premiacao utilizada para credito no saldo do participante',
		vl_resgate decimal (10,2) NOT NULL COMMENT 'Valor de resgate efetuado pelo participante',
		vl_pago_comissao decimal (10,2) NOT NULL COMMENT 'Valor de comissao pago pelo participante ao administrador',
		vl_pago_custo decimal (10,2) NOT NULL COMMENT 'Valor do rateio do custo do bolão',
		vl_saldo decimal (10,2) NOT NULL COMMENT 'Valor do saldo resultante',
		ind_credito_processado tinyint(1) NOT NULL COMMENT 'Indica que o valor do credito já foi verificado e processado',
		PRIMARY KEY (rodada,id_grupo,id_user)
	) $charset_collate ENGINE = INNODB COMMENT = 'Lista dos participantes de cada concurso, pode um participante optar por não participar ou o administrador retirá-lo de um concurso especifico por algum motivo';";
// ------------------- LOTECA_PARAMETRO_RODADA
	$table_name = $wpdb->prefix . 'loteca_parametro_rodada';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria',
		vl_max decimal (10,2) NOT NULL COMMENT 'Valor máximo a ser utilizado de cada participante no concurso',
		vl_min decimal (10,2) NOT NULL COMMENT 'Valor mínimo a ser utilizado de cada participante no concurso',
		tip_rateio smallint(6) NOT NULL COMMENT '1 - mínimo valor acima da média; 2 - máximo valor abaixo da média ; 3 - máximo valor ; 4 - mínimo valor;',
		ind_bolao_volante tinyint (1) NOT NULL COMMENT 'Indica se os volantes terão a marcação de divisão de cotas para bolão',
		vl_lim_rateio decimal (10,2) NOT NULL COMMENT 'Valor mínimo por cota em cada volante',
		qt_max_zebras smallint(6) NOT NULL COMMENT 'Quantidade máxima de zebras por volante',
		qt_min_zebras smallint(6) NOT NULL COMMENT 'Quantidade mínima de zebras por volante',
		amplia_zebra tinyint(1) NOT NULL COMMENT 'Amplia a possibilidade de zebras não selecionadas nos palpites',
		ind_libera_proc_desdobra tinyint(1) NOT NULL COMMENT 'Indica que o administrador do grupo liberou o processamento dos desdobramentos',
		vl_gasto_total decimal (10,2) NOT NULL COMMENT 'Valor total gasto para realização das apostas',
		vl_premio_total decimal (10,2) NOT NULL COMMENT 'Valor total do prêmio recebido na rodada',
		vl_residuo_premio decimal (10,2) NOT NULL COMMENT 'Valor residual do prêmio após distribuição entre os participantes',
		PRIMARY KEY (rodada,id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros do grupo para o concurso';";
// ------------------- LOTECA_PALPITE
	$table_name = $wpdb->prefix . 'loteca_palpite';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		id_user int (11) NOT NULL COMMENT 'Número identificador do usuário',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso',
		PRIMARY KEY (rodada,seq,id_grupo,id_user)
	) $charset_collate ENGINE = INNODB COMMENT = 'Palpite do participante do grupo em cada jogo da rodada';";
// ------------------- LOTECA_FIXO
	$table_name = $wpdb->prefix . 'loteca_palpite_fixo';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso',
		PRIMARY KEY (rodada,seq,id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Palpite fixo do grupo em cada jogo da rodada';";
// ------------------- LOTECA_RESULTADO
	$table_name = $wpdb->prefix . 'loteca_resultado';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		seq smallint(6) NOT NULL COMMENT 'Sequencial do jogo do concurso',
		time1 tinyint(4) NOT NULL COMMENT 'Time mandante vitorioso',
		empate tinyint(4) NOT NULL COMMENT 'Empate',
		time2 tinyint(4) NOT NULL COMMENT 'Time desafiante vitorioso',
		PRIMARY KEY (rodada,seq,id_grupo)
	) $charset_collate ENGINE = INNODB COMMENT = 'Resultado de cada jogo da rodada';";
// ------------------- LOTECA_PROCESSAMENTO
	$table_name = $wpdb->prefix . 'loteca_processamento';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		seq_proc smallint(6) NOT NULL COMMENT 'Sequencial do processamento para o grupo na rodada',
		array_parm longtext NOT NULL COMMENT 'Array com os parametros para processamento',
		array_palpites longtext NOT NULL COMMENT 'Array com os pesos (serialize/unserialize)',
		array_fixos longtext NOT NULL COMMENT 'Array com os palpites (serialize/unserialize)',
		array_pesos longtext NOT NULL COMMENT 'Array com os fixos (serialize/unserialize)',
		opcao_max smallint(6) NOT NULL COMMENT 'Valor maximo de seq_opcao em loteca_processamento_desdobramento',
		ind_perc tinyint(3) NOT NULL COMMENT 'Indica em qual percentual o processamento está',
		ind_processamento tinyint(1) NOT NULL COMMENT 'Indica se o processamento foi concluído',
		nm_arquivo_log longtext NOT NULL COMMENT 'Nome do arquivo de log utilizado no processamento do desdobramento',
		PRIMARY KEY (rodada,id_grupo,seq_proc)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros para processamento do desdobramento de um grupo em uma rodada';";
// ------------------- LOTECA_PROCESSAMENTO_DESDOBRAMENTO
	$table_name = $wpdb->prefix . 'loteca_processamento_desdobramento';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		seq_proc smallint(6) NOT NULL COMMENT 'Sequencial do processamento para o grupo na rodada',
		seq_desdobramento smallint(6) NOT NULL COMMENT 'Sequencial do desdobramento do processamento',
		qt_triplos tinyint(1) NOT NULL COMMENT 'Quantidade de jogos triplos',
		qt_duplos tinyint(1) NOT NULL COMMENT 'Quantidade de jogos duplos',
		seq_opcao smallint(6) NOT NULL COMMENT 'Sequencial da opcao (conjunto de parametros de zebras e fixos)',
		array_volante longtext NOT NULL COMMENT 'Array com os dados do tipo de volante',
		array_opcoes longtext NOT NULL COMMENT 'Opcoes para processamento deste volante',
		ind_perc tinyint(3) NOT NULL COMMENT 'Indica em qual percentual do processo este desdobramento será realizado',
		PRIMARY KEY (rodada,id_grupo,seq_proc,seq_desdobramento)
	) $charset_collate ENGINE = INNODB COMMENT = 'Parametros do desdobramento de um grupo em uma rodada';";
// ------------------- LOTECA_DESDOBRAMENTO
	$table_name = $wpdb->prefix . 'loteca_desdobramento';
	$sql[] = "ALTER TABLE $table_name DROP PRIMARY KEY;";
	$sql[] = "CREATE TABLE $table_name (
		rodada int(11) NOT NULL COMMENT 'Número do concurso da Loteria (loteca_rodada)',
		id_grupo int (11) NOT NULL COMMENT 'Número identificador do grupo',
		seq_proc smallint(6) NOT NULL COMMENT 'Sequencial do processamento para o grupo na rodada',
		seq_desdobramento smallint(6) NOT NULL COMMENT 'Sequencial do desdobramento do processamento',
		min_zebras tinyint(1) NOT NULL COMMENT 'Quantidade menor de zebras',
		max_zebras tinyint(1) NOT NULL COMMENT 'Quantidade maior de zebras',
		peso smallint(6) NOT NULL COMMENT 'Peso dos jogos',
		min_peso smallint(6) NOT NULL COMMENT 'Menor peso dos jogos',
		max_peso smallint(6) NOT NULL COMMENT 'Maior peso dos jogos',
		array_jogos longtext NOT NULL COMMENT 'array com os jogos selecionados como possivel desdobramento',
		ind_escolhido tinyint(1) NOT NULL COMMENT 'indica que o administrador do bolao selecionou o desdobramento',
		PRIMARY KEY (rodada,id_grupo,seq_proc,seq_desdobramento)
	) $charset_collate ENGINE = INNODB COMMENT = 'Desdobramentos gerados no processamento';";
// ------------------
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$sql_ret=dbDelta( $sql );
	update_option( 'erro_criar_tabelas' , serialize($sql_ret));
	update_option( 'loteca_db_version', $loteca_db_version );
}
?>