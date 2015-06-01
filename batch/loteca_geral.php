<?php

/*

PENDENCIAS
==========
## 01 - GRAVAR ARQUIVOS DE LOG DO PROCESSAMENTO, UM PARA CADA GRUPO, PARA QUE POSSAM SER CONSULTADOS PELOS ADMINISTRADORES
##      O NOME DO ARQUIVO GRAVADO FICA ARMAZENADO NO BANCO DE DADOS DO PROCESSAMENTO
## 02 - INCLUIR CAMPO PARA LIBERAR O INICIO DO PROCESSAMENTO
03 - TELAS DE ADMINISTRACAO DO SITE
## - Alteração do parametro de limite de processamento
## - Liberar novo grupo (ativar)
04 - TELAS DE ADMINISTRACAO DO BOLAO
05 - TELAS DE CONSULTA PELO USUARIO DO BOLAO E ESCOLHA DOS PALPITES DA RODADA
06 - TELAS DE CADASTRAMENTO DE NOVO BOLAO
07 - TELAS DE INSCRIÇÃO EM BOLÃO
08 - CALCULAR A GARANTIA 
09 - INCLUIR VERIFICAÇÃO DE RESULTADOS IDENTICOS PARA DIFERENTES DESDOBRAMENTOS CALCULADOS:
 - MANTER O DE MENOR QUANTIDADE DE VOLANTES;
 - SE A QUANTIDADE DE VOLANTES FOR A MESMA, MANTER O QUE TEM A MELHOR GARANTIA DIANTE DOS PESOS ESCOLHIDOS
 - SE A GARANTIA FOR A MESMA, MANTER O QUE FOI CALCULADO PRIMEIRO
10 - MELHORAR AS INFORMAÇÕES GERADAS NO LOG DE FORMA QUE O ADMINISTRADOR POSSA ENTENDER O PASSO A PASSO DE CADA PROCESSAMENTO
11 - O ALGORITMO DO loteca_processa.php AINDA ESTÁ BASTANTE COMPLEXO, ANALISAR PARA TENTAR DEIXÁ-LO MAIS SIMPLES E MAIS RÁPIDO
12 - REALIZAR TESTE DE STRESS DO ALGORITMO 
 - UTILIZANDO UM MODELO DE GRUPO COM 50 PARTICIPANTES E COM TODOS OS PESOS POSSÍVEIS NOS JOGOS
13 - MELHORAR/INCLUIR TRATAMENTO DOS ERROS NOS SCRIPTS (enviar mensagem quando o processamento falhar)
14 - ENVIAR EMAILS AOS PARTICIPANTES NOS EVENTOS REGISTRADOS NO SISTEMA 
   - Registro de crédito/depósito
   - Registro de débito
   - Disponibilidade para palpites
   - Escolha dos cartões
15 - OPÇÃO PARA IMPRESSAO DOS VOLANTES
16 - Automatizar os calculos e atualizações na base de dados para a nova rodada capturada da CEF

8. Calcular o indice de garantia:
8.1. a partir do jogo matriz desdobrar cada resultado possível da matriz
8.2. a partir de cada jogo selecionado desdobrar cada resultado
8.3. de cada desdobramento da matriz confrontar com os desdobramentos dos jogos selecionados
8.4. o menor indice de acerto será a garantia
8.5. calcular o percentual de chances de acerto de 14 e 13 pontos

** MELHORAR A ESTRUTURA DO ALGORITMO
** PREPARAR PLUGIN PARA WORDPRESS PARA:
- REGISTRO DE PARAMETROS
- CADASTRO DE PARTICIPANTES
- REGISTRO DE SALDO
- CONFIRMAÇÃO DE PARTICIPAÇÃO
- REGISTRO DE PALPITES
- LISTAGEM E CONSULTA DOS PALPITES
- CADASTRO DE JOGO
- CADASTRO DE RESULTADO
- REGISTRO DO CONJUNTO DE JOGOS ESCOLHIDO PARA SER APOSTADO
- LISTAGEM COM HISTORICO DO ACERTO INDIVIDUAL DE CADA PARTICIPANTE (RANKING)
- CONFRONTO DA CONJUNTO DE JOGOS ESCOLHIDO COM O RESULTADO

*/

// FUNCOES -----------------------------------------------------

function captura_proxima_rodada(){ // OK
	$sql="SELECT MAX(rodada) AS rodada FROM wp_loteca_rodada WHERE dt_inicio_palpite <= NOW( )";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			return $row['rodada'];
		}
	}
}

function captura_jogos_rodada($rodada){ // ok 
	$jogos=array();
	$sql="SELECT seq, time1, time2, data, dia FROM wp_loteca_jogos WHERE rodada = $rodada ORDER BY seq";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			$jogos[$row['seq']] = array ( 1 => utf8_decode($row['time1']) , 2 => utf8_decode($row['time2']) , "DATA" => $row['data'] , "DIA" => utf8_decode($row['dia']) );	
		}
		return $jogos;
	}
}

function prepara_ambiente(){ // OK
	date_default_timezone_set("America/Sao_Paulo");
	set_time_limit(20000);
}

function cria_globals(){ // OK
	global $numero_maximo_resultados, $desenho, $desenho2, $desenho3, $contem, $valores;
	global $validos, $validos_contem, $validos_cont_d_t, $qt_jogos_volante, $contem_base;
	global $max_array, $mid_array, $fator_pular;
	global $h_log, $h_grupo;
	
	$max_array=7500;
	$mid_array=5000;
	$fator_pular=2;
	$h_log=NULL;
	$h_grupo=array();

	$numero_maximo_resultados = 4782970;
	$desenho =
		   array ( 0 => "0 0 0" , 
                   1 => "X 0 0" ,
                   2 => "0 X 0" , 
                   3 => "X X 0" , 
                   4 => "0 0 X" , 
                   5 => "X 0 X" , 
                   6 => "0 X X" , 
                   7 => "X X X" );

	$desenho2 =
			array ( 0 => "000" , 
                    1 => "X00" ,
                    2 => "0X0" , 
                    3 => "XX0" , 
                    4 => "00X" , 
                    5 => "X0X" , 
                    6 => "0XX" , 
                    7 => "XXX" );

	$desenho3 =
			array ( 0 => array("1"=>"0","E"=>"0","2"=>"0") , 
                    1 => array("1"=>"X","E"=>"0","2"=>"0") , 
                    2 => array("1"=>"0","E"=>"X","2"=>"0") , 
                    3 => array("1"=>"X","E"=>"X","2"=>"0") , 
                    4 => array("1"=>"0","E"=>"0","2"=>"X") , 
                    5 => array("1"=>"X","E"=>"0","2"=>"X") , 
                    6 => array("1"=>"0","E"=>"X","2"=>"X") , 
                    7 => array("1"=>"X","E"=>"X","2"=>"X") );

	$contem = // quais tipos contem cada tipo (devido às marcações serem registradas como somatórios binários)
		array ( 0 => array ( ), 
				1 => array ( 1 => 1 ), 
                2 => array ( 1 => 2 ),
                3 => array ( 1 => 1 , 2 => 2 , 3 => 3 ),
                4 => array ( 1 => 4 ),
                5 => array ( 1 => 1 , 2 => 4 , 3 => 5 ),
                6 => array ( 1 => 2 , 2 => 4 , 3 => 6 ),
                7 => array ( 1 => 1 , 2 => 2 , 3 => 3 , 4 => 4 , 5 => 5 , 6 => 6 , 7 => 7 ));

//	$simples = array ( 1 , 2 , 4 ); // estes são os tipos de marcação de jogos simples
//	$duplo = array( 3 , 5 , 6 ); // estes são os tipos de marcação de jogos duplos
//	$triplo = array ( 7 );  // este é o tipo de marcação de jogo triplo

	$valores = // custo de cada tipo de combinação
		array (  1 =>    2.00 ,
                 2 =>    4.00 ,
                 3 =>    8.00 ,
                 4 =>   16.00 ,
                 5 =>   32.00 ,
                 6 =>   64.00 ,
                 7 =>  128.00 ,
                 8 =>  256.00 ,
                 9 =>  512.00 ,
                10 =>    2.00 ,
                11 =>    6.00 ,
                12 =>   12.00 ,
                13 =>   24.00 ,
                14 =>   48.00 ,
                15 =>   96.00 ,
                16 =>  192.00 ,
                17 =>  384.00 ,
                18 =>  768.00 ,
                19 =>    8.00 ,
                20 =>   18.00 ,
                21 =>   36.00 ,
                22 =>   72.00 ,
                23 =>  144.00 ,
                24 =>  288.00 ,
                25 =>  576.00 ,
                26 =>   26.00 ,
                27 =>   54.00 ,
                28 =>  108.00 ,
                29 =>  216.00 ,
                30 =>  432.00 ,
                31 =>  864.00 ,
                32 =>   80.00 ,
                33 =>  162.00 ,
                34 =>  324.00 ,
                35 =>  648.00 ,
                36 =>  242.00 ,
                37 =>  486.00 ,
                38 =>  728.00 ) ;

/*		array (  1 =>   1.00 , // até o sorteio 653 em maio de 2015
                 2 =>   2.00 , 
                 3 =>   4.00 , 
                 4 =>   8.00 , 
                 5 =>  16.00 , 
                 6 =>  32.00 , 
                 7 =>  64.00 , 
                 8 => 128.00 ,  
                 9 => 256.00 ,  
                10 =>   1.50 ,  
                11 =>   3.00 ,  
                12 =>   6.00 ,  
                13 =>  12.00 ,  
                14 =>  24.00 ,  
                15 =>  48.00 ,  
                16 =>  96.00 ,  
                17 => 192.00 ,  
                18 => 384.00 ,  
                19 =>   4.50 ,  
                20 =>   9.00 ,  
                21 =>  18.00 ,  
                22 =>  36.00 ,  
                23 =>  72.00 ,  
                24 => 144.00 ,  
                25 => 288.00 ,  
                26 =>  13.50 ,  
                27 =>  27.00 ,  
                28 =>  54.00 ,  
                29 => 108.00 ,  
                30 => 216.00 ,  
                31 => 432.00 ,  
                32 =>  40.50 ,  
                33 =>  81.00 ,  
                34 => 162.00 ,  
                35 => 324.00 ,  
                36 => 121.50 ,  
                37 => 243.00 ,  
                38 => 364.50 ) ;
*/
	$validos = // tipos de jogos válidos para os volantes da loteca
		array ( 1 => array ( "S" => 13 , "D" => 1 , "T" => 0 ) , 
                2 => array ( "S" => 12 , "D" => 2 , "T" => 0 ) ,
                3 => array ( "S" => 11 , "D" => 3 , "T" => 0 ) , 
                4 => array ( "S" => 10 , "D" => 4 , "T" => 0 ) , 
                5 => array ( "S" =>  9 , "D" => 5 , "T" => 0 ) , 
                6 => array ( "S" =>  8 , "D" => 6 , "T" => 0 ) , 
                7 => array ( "S" =>  7 , "D" => 7 , "T" => 0 ) , 
                8 => array ( "S" =>  6 , "D" => 8 , "T" => 0 ) , 
                9 => array ( "S" =>  5 , "D" => 9 , "T" => 0 ) , 
               10 => array ( "S" => 13 , "D" => 0 , "T" => 1 ) , 
               11 => array ( "S" => 12 , "D" => 1 , "T" => 1 ) , 
               12 => array ( "S" => 11 , "D" => 2 , "T" => 1 ) , 
               13 => array ( "S" => 10 , "D" => 3 , "T" => 1 ) , 
               14 => array ( "S" =>  9 , "D" => 4 , "T" => 1 ) , 
               15 => array ( "S" =>  8 , "D" => 5 , "T" => 1 ) , 
               16 => array ( "S" =>  7 , "D" => 6 , "T" => 1 ) , 
               17 => array ( "S" =>  6 , "D" => 7 , "T" => 1 ) , 
               18 => array ( "S" =>  5 , "D" => 8 , "T" => 1 ) , 
               19 => array ( "S" => 12 , "D" => 0 , "T" => 2 ) , 
               20 => array ( "S" => 11 , "D" => 1 , "T" => 2 ) , 
               21 => array ( "S" => 10 , "D" => 2 , "T" => 2 ) , 
               22 => array ( "S" =>  9 , "D" => 3 , "T" => 2 ) , 
               23 => array ( "S" =>  8 , "D" => 4 , "T" => 2 ) , 
               24 => array ( "S" =>  7 , "D" => 5 , "T" => 2 ) , 
               25 => array ( "S" =>  6 , "D" => 6 , "T" => 2 ) , 
               26 => array ( "S" => 11 , "D" => 0 , "T" => 3 ) , 
               27 => array ( "S" => 10 , "D" => 1 , "T" => 3 ) , 
               28 => array ( "S" =>  9 , "D" => 2 , "T" => 3 ) , 
               29 => array ( "S" =>  8 , "D" => 3 , "T" => 3 ) , 
               30 => array ( "S" =>  7 , "D" => 4 , "T" => 3 ) , 
               31 => array ( "S" =>  6 , "D" => 5 , "T" => 3 ) , 
               32 => array ( "S" => 10 , "D" => 0 , "T" => 4 ) , 
               33 => array ( "S" =>  9 , "D" => 1 , "T" => 4 ) , 
               34 => array ( "S" =>  8 , "D" => 2 , "T" => 4 ) , 
               35 => array ( "S" =>  7 , "D" => 3 , "T" => 4 ) , 
               36 => array ( "S" =>  9 , "D" => 0 , "T" => 5 ) , 
               37 => array ( "S" =>  8 , "D" => 1 , "T" => 5 ) , 
               38 => array ( "S" =>  8 , "D" => 0 , "T" => 6 ) );

	$validos_contem = 
		array (  1 => array ( 1 ), // ( "S" => 13, "D" => 1, "T" => 0, "X" => 0 ),   
                 2 => array ( 1, 2 ), //  ( "S" => 12, "D" => 2, "T" => 0, "X" => 0 ) ,
                 3 => array ( 1, 2, 3 ), //  ( "S" => 11, "D" => 3, "T" => 0, "X" => 0 ), 
                 4 => array ( 1, 2, 3, 4 ), //  ( "S" => 10, "D" => 4, "T" => 0, "X" => 0 ), 
                 5 => array ( 1, 2, 3, 4, 5 ), //  ( "S" =>  9, "D" => 5, "T" => 0, "X" => 0 ), 
                 6 => array ( 1, 2, 3, 4, 5, 6 ), //  ( "S" =>  8, "D" => 6, "T" => 0, "X" => 0 ), 
                 7 => array ( 1, 2, 3, 4, 5, 6, 7 ), //  ( "S" =>  7, "D" => 7, "T" => 0, "X" => 0 ), 
                 8 => array ( 1, 2, 3, 4, 5, 6, 7, 8 ), //  ( "S" =>  6, "D" => 8, "T" => 0, "X" => 0 ), 
                 9 => array ( 1, 2, 3, 4, 5, 6, 7, 8, 9 ), //  ( "S" =>  5, "D" => 9, "T" => 0, "X" => 0 ), 
                10 => array ( 1, 10 ), //  ( "S" => 13, "D" => 0, "T" => 1, "X" => 0 ), 
                11 => array ( 1, 2, 10, 11 ), //  ( "S" => 12, "D" => 1, "T" => 1, "X" => 0 ), 
                12 => array ( 1, 2, 3, 10, 11, 12 ), //  ( "S" => 11, "D" => 2, "T" => 1, "X" => 0 ), 
                13 => array ( 1, 2, 3, 4, 10, 11, 12, 13 ), //  ( "S" => 10, "D" => 3, "T" => 1, "X" => 0 ), 
                14 => array ( 1, 2, 3, 4, 5, 10, 11, 12, 13, 14 ), //  ( "S" =>  9, "D" => 4, "T" => 1, "X" => 0 ), 
                15 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15 ), //  ( "S" =>  8, "D" => 5, "T" => 1, "X" => 0 ), 
                16 => array ( 1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16 ), //  ( "S" =>  7, "D" => 6, "T" => 1, "X" => 0 ) , 
                17 => array ( 1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17 ), 
				              //  ( "S" =>  6, "D" => 7, "T" => 1, "X" => 0 ), 
                18 => array ( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18 ), 
				              //  ( "S" =>  5, "D" => 8, "T" => 1, "X" => 0 ), 
                19 => array ( 1, 2, 10, 11, 19 ), //  ( "S" => 12, "D" => 0, "T" => 2, "X" => 0 ), 
                20 => array ( 1, 2, 3, 10, 11, 12, 19, 20 ), //  ( "S" => 11, "D" => 1, "T" => 2, "X" => 0 ), 
                21 => array ( 1, 2, 3, 4, 10, 11, 12, 13, 19, 20, 21 ), //  ( "S" => 10, "D" => 2, "T" => 2, "X" => 0 ), 
                22 => array ( 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 19, 20, 21, 22 ), //  ( "S" =>  9, "D" => 3, "T" => 2, "X" => 0 ), 
                23 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 19, 20, 21, 22, 23 ), 
				              //  ( "S" =>  8, "D" => 4, "T" => 2, "X" => 0 ), 
                24 => array ( 1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24 ), 
                              //  ( "S" =>  7, "D" => 5, "T" => 2, "X" => 0 ), 
                25 => array ( 1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 19, 20, 21, 22, 23, 24, 25 ), 
                              //  ( "S" =>  6, "D" => 6, "T" => 2, "X" => 0 ), <<<<
                26 => array ( 1, 2, 3, 10, 11, 12, 19, 20, 26 ), //  ( "S" => 11, "D" => 0, "T" => 3, "X" => 0 ), 
                27 => array ( 1, 2, 3, 4, 10, 11, 12, 13, 19, 20, 21, 26, 27 ), //  ( "S" => 10, "D" => 1, "T" => 3, "X" => 0 ), 
                28 => array ( 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 19, 20, 21, 22, 26, 27, 28 ), 
				              //  ( "S" =>  9, "D" => 2, "T" => 3, "X" => 0 ), 
                29 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 19, 20, 21, 22, 23, 26, 27, 28, 29 ), 
                              //  ( "S" =>  8, "D" => 3, "T" => 3, "X" => 0 ), 
                30 => array ( 1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 26, 27, 28, 29, 30 ), 
                              //  ( "S" =>  7, "D" => 4, "T" => 3, "X" => 0 ), 
                31=>array (1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30), 
                              //  ( "S" =>  6, "D" => 5, "T" => 3, "X" => 0 ),
                32 => array ( 1, 2, 3, 4, 10, 11, 12, 13, 19, 20, 21, 26, 27, 32 ), 
				              //  ( "S" => 10, "D" => 0, "T" => 4, "X" => 0 ), 
                33 => array ( 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 19, 20, 21, 22, 26, 27, 28, 32, 33 ), 
                              //  ( "S" =>  9, "D" => 1, "T" => 4, "X" => 0 ), 
                34 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 19, 20, 21, 22, 23, 26, 27, 28, 29, 32, 33, 34 ), 
                              //  ( "S" =>  8, "D" => 2, "T" => 4, "X" => 0 ), 
                35=>array(1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 26, 27, 28, 29, 32, 33, 34, 35), 
                              //  ( "S" =>  7, "D" => 3, "T" => 4, "X" => 0 ), 
                36 => array ( 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 19, 20, 21, 22, 26, 27, 28, 32, 33, 36 ), 
                               //  ( "S" =>  9, "D" => 0, "T" => 5, "X" => 0 ), 
                37 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 19, 20, 21, 22, 23, 26, 27, 28, 29, 32, 33, 36, 37 ), 
                              //  ( "S" =>  8, "D" => 1, "T" => 5, "X" => 0 ), 
                38 => array ( 1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 19, 20, 21, 22, 23, 26, 27, 28, 29, 32, 33, 36, 37, 38 ) );
                              //  ( "S" =>  8, "D" => 0, "T" => 6, "X" => 0 ) );

	$validos_cont_d_t = // $validos_cont_d_t -> soma da contagem de duplos e triplos, para facilitar a reducao
		array (  1 => 1 , // array ( "S" => 13 , "D" => 1 , "T" => 0 , "X" => 0 ) , 
                 2 => 2 , // array ( "S" => 12 , "D" => 2 , "T" => 0 , "X" => 0 ) ,
                 3 => 3 , // array ( "S" => 11 , "D" => 3 , "T" => 0 , "X" => 0 ) , 
                 4 => 4 , // array ( "S" => 10 , "D" => 4 , "T" => 0 , "X" => 0 ) , 
                 5 => 5 , // array ( "S" =>  9 , "D" => 5 , "T" => 0 , "X" => 0 ) , 
                 6 => 6 , // array ( "S" =>  8 , "D" => 6 , "T" => 0 , "X" => 0 ) , 
                 7 => 7 , // array ( "S" =>  7 , "D" => 7 , "T" => 0 , "X" => 0 ) , 
                 8 => 8 , // array ( "S" =>  6 , "D" => 8 , "T" => 0 , "X" => 0 ) , 
                 9 => 9 , // array ( "S" =>  5 , "D" => 9 , "T" => 0 , "X" => 0 ) , 
                10 => 1 , // array ( "S" => 13 , "D" => 0 , "T" => 1 , "X" => 0 ) , 
                11 => 2 , // array ( "S" => 12 , "D" => 1 , "T" => 1 , "X" => 0 ) , 
                12 => 3 , // array ( "S" => 11 , "D" => 2 , "T" => 1 , "X" => 0 ) , 
                13 => 4 , // array ( "S" => 10 , "D" => 3 , "T" => 1 , "X" => 0 ) , 
                14 => 5 , // array ( "S" =>  9 , "D" => 4 , "T" => 1 , "X" => 0 ) , 
                15 => 6 , // array ( "S" =>  8 , "D" => 5 , "T" => 1 , "X" => 0 ) , 
                16 => 7 , // array ( "S" =>  7 , "D" => 6 , "T" => 1 , "X" => 0 ) , 
                17 => 8 , // array ( "S" =>  6 , "D" => 7 , "T" => 1 , "X" => 0 ) , 
                18 => 9 , // array ( "S" =>  5 , "D" => 8 , "T" => 1 , "X" => 0 ) , 
                19 => 2 , // array ( "S" => 12 , "D" => 0 , "T" => 2 , "X" => 0 ) , 
                20 => 3 , // array ( "S" => 11 , "D" => 1 , "T" => 2 , "X" => 0 ) , 
                21 => 4 , // array ( "S" => 10 , "D" => 2 , "T" => 2 , "X" => 0 ) , 
                22 => 5 , // array ( "S" =>  9 , "D" => 3 , "T" => 2 , "X" => 0 ) , 
                23 => 6 , // array ( "S" =>  8 , "D" => 4 , "T" => 2 , "X" => 0 ) , 
                24 => 7 , // array ( "S" =>  7 , "D" => 5 , "T" => 2 , "X" => 0 ) , 
                25 => 8 , // array ( "S" =>  6 , "D" => 6 , "T" => 2 , "X" => 0 ) , 
                26 => 3 , // array ( "S" => 11 , "D" => 0 , "T" => 3 , "X" => 0 ) , 
                27 => 4 , // array ( "S" => 10 , "D" => 1 , "T" => 3 , "X" => 0 ) , 
                28 => 5 , // array ( "S" =>  9 , "D" => 2 , "T" => 3 , "X" => 0 ) , 
                29 => 6 , // array ( "S" =>  8 , "D" => 3 , "T" => 3 , "X" => 0 ) , 
                30 => 7 , // array ( "S" =>  7 , "D" => 4 , "T" => 3 , "X" => 0 ) , 
                31 => 8 , // array ( "S" =>  6 , "D" => 5 , "T" => 3 , "X" => 0 ) , 
                32 => 4 , // array ( "S" => 10 , "D" => 0 , "T" => 4 , "X" => 0 ) , 
                33 => 5 , // array ( "S" =>  9 , "D" => 1 , "T" => 4 , "X" => 0 ) , 
                34 => 6 , // array ( "S" =>  8 , "D" => 2 , "T" => 4 , "X" => 0 ) , 
                35 => 7 , // array ( "S" =>  7 , "D" => 3 , "T" => 4 , "X" => 0 ) , 
                36 => 5 , // array ( "S" =>  9 , "D" => 0 , "T" => 5 , "X" => 0 ) , 
                37 => 6 , // array ( "S" =>  8 , "D" => 1 , "T" => 5 , "X" => 0 ) , 
                38 => 6 ); // array ( "S" =>  8 , "D" => 0 , "T" => 6 , "X" => 0 ) );

	$qt_jogos_volante = // $qt_jogos_volante -> quantidade de jogos pelo tipo de volante
		array (  1 =>   2 , // array ( "S" => 13 , "D" => 1 , "T" => 0 , "X" => 0 ) ,   1.00
                 2 =>   4 , // array ( "S" => 12 , "D" => 2 , "T" => 0 , "X" => 0 ) ,   2.00
                 3 =>   8 , // array ( "S" => 11 , "D" => 3 , "T" => 0 , "X" => 0 ) ,   4.00
                 4 =>  16 , // array ( "S" => 10 , "D" => 4 , "T" => 0 , "X" => 0 ) ,   8.00
                 5 =>  32 , // array ( "S" =>  9 , "D" => 5 , "T" => 0 , "X" => 0 ) ,  16.00
                 6 =>  64 , // array ( "S" =>  8 , "D" => 6 , "T" => 0 , "X" => 0 ) ,  32.00
                 7 => 128 , // array ( "S" =>  7 , "D" => 7 , "T" => 0 , "X" => 0 ) ,  64.00
                 8 => 256 , // array ( "S" =>  6 , "D" => 8 , "T" => 0 , "X" => 0 ) , 128.00
                 9 => 512 , // array ( "S" =>  5 , "D" => 9 , "T" => 0 , "X" => 0 ) , 256.00
                10 =>   3 , // array ( "S" => 13 , "D" => 0 , "T" => 1 , "X" => 0 ) ,   1.50
                11 =>   6 , // array ( "S" => 12 , "D" => 1 , "T" => 1 , "X" => 0 ) ,   3.00
                12 =>  12 , // array ( "S" => 11 , "D" => 2 , "T" => 1 , "X" => 0 ) ,   6.00
                13 =>  24 , // array ( "S" => 10 , "D" => 3 , "T" => 1 , "X" => 0 ) ,  12.00
                14 =>  48 , // array ( "S" =>  9 , "D" => 4 , "T" => 1 , "X" => 0 ) ,  24.00
                15 =>  96 , // array ( "S" =>  8 , "D" => 5 , "T" => 1 , "X" => 0 ) ,  48.00
                16 => 192 , // array ( "S" =>  7 , "D" => 6 , "T" => 1 , "X" => 0 ) ,  96.00
                17 => 384 , // array ( "S" =>  6 , "D" => 7 , "T" => 1 , "X" => 0 ) , 192.00
                18 => 768 , // array ( "S" =>  5 , "D" => 8 , "T" => 1 , "X" => 0 ) , 384.00
                19 =>   9 , // array ( "S" => 12 , "D" => 0 , "T" => 2 , "X" => 0 ) ,   4.50
                20 =>  18 , // array ( "S" => 11 , "D" => 1 , "T" => 2 , "X" => 0 ) ,   9.00
                21 =>  36 , // array ( "S" => 10 , "D" => 2 , "T" => 2 , "X" => 0 ) ,  18.00
                22 =>  72 , // array ( "S" =>  9 , "D" => 3 , "T" => 2 , "X" => 0 ) ,  36.00
                23 => 144 , // array ( "S" =>  8 , "D" => 4 , "T" => 2 , "X" => 0 ) ,  72.00
                24 => 288 , // array ( "S" =>  7 , "D" => 5 , "T" => 2 , "X" => 0 ) , 144.00
                25 => 576 , // array ( "S" =>  6 , "D" => 6 , "T" => 2 , "X" => 0 ) , 288.00
                26 =>  27 , // array ( "S" => 11 , "D" => 0 , "T" => 3 , "X" => 0 ) ,  13.50
                27 =>  54 , // array ( "S" => 10 , "D" => 1 , "T" => 3 , "X" => 0 ) ,  27.00
                28 => 108 , // array ( "S" =>  9 , "D" => 2 , "T" => 3 , "X" => 0 ) ,  54.00
                29 => 216 , // array ( "S" =>  8 , "D" => 3 , "T" => 3 , "X" => 0 ) , 108.00
                30 => 432 , // array ( "S" =>  7 , "D" => 4 , "T" => 3 , "X" => 0 ) , 216.00
                31 => 864 , // array ( "S" =>  6 , "D" => 5 , "T" => 3 , "X" => 0 ) , 432.00
                32 =>  81 , // array ( "S" => 10 , "D" => 0 , "T" => 4 , "X" => 0 ) ,  40.50
                33 => 162 , // array ( "S" =>  9 , "D" => 1 , "T" => 4 , "X" => 0 ) ,  81.00
                34 => 324 , // array ( "S" =>  8 , "D" => 2 , "T" => 4 , "X" => 0 ) , 162.00
                35 => 648 , // array ( "S" =>  7 , "D" => 3 , "T" => 4 , "X" => 0 ) , 324.00
                36 => 243 , // array ( "S" =>  9 , "D" => 0 , "T" => 5 , "X" => 0 ) , 121.50
                37 => 486 , // array ( "S" =>  8 , "D" => 1 , "T" => 5 , "X" => 0 ) , 243.00
                38 => 729 ); // array ( "S" =>  8 , "D" => 0 , "T" => 6 , "X" => 0 ) )364.50;

	$contem_base = // quais tipos contem cada tipo (devido às marcações serem registradas como somatórios binários)
		array ( 1 => array ( 1 => 1 ), 
                2 => array ( 1 => 2 ),
                3 => array ( 1 => 1 , 2 => 2 ),
                4 => array ( 1 => 4 ),
                5 => array ( 1 => 1 , 2 => 4 , ),
                6 => array ( 1 => 2 , 2 => 4 ),
                7 => array ( 1 => 1 , 2 => 2 , 4 => 4 ));
}

function config_conexao_mysql(){ // OK
 global $mysql_host, $mysql_user, $mysql_password, $mysql_dbname, $link;
 if(file_exists('conf/loteca.conf.php')){
  include('conf/loteca.conf.php');
 }else{
/*
informe os dados do servidor, user, password e dbname
*/
  $mysql_host='localhost';
  $mysql_user='';
  $mysql_password='';
  $mysql_dbname='';
 }
}

function grava_err_sql($sql){
	global $mysql_link;
	if(is_object($mysql_link)){
		gr_l("Erro do banco de dados, não foi possível processar a query no banco de dados\n");
		gr_l('Erro MySQL: ' . mysqli_error($mysql_link) . "\n");
	}else{
		gr_l("Erro do banco de dados, não foi possível conectar ao banco de dados\n");
	}
	if(!is_array($sql)){
		gr_l(" SQL = >>'" . $sql . "'<<\n");
	}else{
		gr_l(" SQL = >>'\n");
		gr_l(implode("\n",$sql));
		gr_l("'<<\n");
	}

}

function grava_err($msg){
	global $h_grupo,$h_log;
	if(is_resource($h_log)){
		gr_l($msg);
	}else{
		echo $msg . ' | NÃO FOI POSSÍVEL GRAVAR NO ARQUIVO DE LOG';
	}
	foreach($h_grupo as $handle){
		if(is_resource($handle)){
			gr_g($msg);
		}
	}
}


function query($sql){
	global $mysql_link;
	if(!conecta()){
		$result=FALSE;
		grava_err_sql($sql);
	}else{
		$mysql_link->begin_transaction();
		if(!is_array($sql)){
			$result = $mysql_link->query($sql);
		}else{
			$result = $mysql_link->multi_query(implode('',$sql));
			while(($mysql_link->more_results()==TRUE)&&($result==TRUE)){
				$result=$mysql_link->next_result();
			}
		}
		if (!$result) {
			grava_err_sql($sql);
			$mysql_link->rollback();
		}else{
			$mysql_link->commit();
		}
	}
	return $result;
}

function conecta(){
	global $mysql_host, $mysql_user, $mysql_password , $mysql_dbname, $mysql_link;
	if (!is_object($mysql_link)){
		if (!$mysql_link = mysqli_connect($mysql_host, $mysql_user, $mysql_password , $mysql_dbname)) {
			gr_l("Não foi possível conectar ao mysql! $mysql_host $mysql_user $mysql_password $mysql_dbname");
			return FALSE;
		}
	}
	return TRUE;
};

function gr_l($linhas){
	global $arquivo_log,$h_log;
	if(!is_resource($h_log)){
		$h_log=fopen($arquivo_log,'a');
	}
	if(is_array($linhas)){
		foreach($linhas as $linha){
			fwrite($h_log,$linha);
		}
	}else{
		fwrite($h_log,$linhas);
	}
}

function gr_g($linhas){
	global $arquivo_grupo,$h_grupo;
	if((!isset($h_grupo[$arquivo_grupo]))||(!is_resource($h_grupo[$arquivo_grupo]))){
		$h_grupo[$arquivo_grupo]=fopen($arquivo_grupo,'a');
	}
	if(is_array($linhas)){
		foreach($linhas as $linha){
			fwrite($h_grupo[$arquivo_grupo],$linha);
		}
	}else{
		fwrite($h_grupo[$arquivo_grupo],$linhas);
	}
}

function totais($opcao_x){
	$tot_simples=0;
	$tot_duplo=0;
	$tot_triplo=0;
	foreach($opcao_x as $j){
		$tipo=1; // 1 - simples ; 2 - duplo ; 3 - triplo
		foreach($j as $val){
			if(($val==3)||($val==5)||($val==6)){
				if($tipo!=3){
					$tipo=2;
				}
			}
			if($val==7){
				$tipo=3;
			}
		}
		switch ($tipo) {
			case 1:
				$tot_simples++;break;
			case 2:
				$tot_duplo++;break;
			case 3:
				$tot_triplo++;break;
		}
	}
	return array('S' => $tot_simples , 'D' => $tot_duplo, 'T' => $tot_triplo);
}

function finaliza(){
	global $h_log, $h_grupo;
	if(is_resource($h_log)){
		fclose($h_log);
	}
	foreach($h_grupo as $handle){
		if(is_resource($handle)){
			fclose($handle);
		}
	}
}

?>