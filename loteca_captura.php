<?php

include_once 'loteca_geral.php';
//include_once 'loteca_db_functions.php';

function busca_info_ult_rodada(){
	$sql="SELECT rodada, dt_sorteio FROM `wp_loteca_rodada` where rodada = (select max(rodada) from  `wp_loteca_rodada`)";
	$result=query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		return $row;
	}
}

function captura_programacao(){
	$proxy=false;
// busca pagina HTML da programacao da loteca
	$html = file_get_contents_curl('http://www.loterias.caixa.gov.br/wps/portal/loterias/landing/loteca/programacao/');
	if(!$html){
		echo '<BR>Erro em file_get_contents_curl<BR>';
		return FALSE;
	}
// carrega HTML da programação como DOMDocument para tratamento das informações
	$doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTML($html,LIBXML_NOERROR);
	libxml_use_internal_errors(false);

// VERIFICA SE A PROGRAMAÇÃO DO PROXIMO CONCURSO ESTÁ DISPONÍVEL
	$finder = new DomXPath($doc);
	$classname="content";
	$nodes = $finder->query("//*[@class = '$classname']");
	$indisponivel=FALSE;
	foreach ($nodes as $node) {
		if(strpos($node->textContent,"Não há programação disponível")){$indisponivel=TRUE;}
	}

	if (!$indisponivel){
		$querys=array();
		$resultados=$doc->getElementById('resultados');
// CAPTURA INFORMAÇÃO DO CONCURSO
		$infos=$resultados->getElementsByTagName('small');
		foreach($infos as $info){
			$texto=$info->nodeValue;
			$retirar=array("Concurso ","(",")",",");
			$array=explode(" ", str_replace($retirar, "", $texto));
			$dia=DateTime::createFromFormat('d/m/Y', $array[1])->getTimestamp();
			$concurso=$array[0];
			$inicio=date("Y-m-d",strtotime("previous monday", $dia));
			$fim=date("Y-m-d",strtotime("previous friday", $dia));
//			$dia_sql=DateTime::createFromFormat('Y-m-d', $array[1])->getTimestamp();
			$dia_sql=date("Y-m-d",$dia);
			$querys[]="
			INSERT INTO wp_loteca_rodada ( rodada , dt_inicio_palpite , dt_fim_palpite , dt_sorteio ) 
			VALUES ( " . $concurso ." , '" . $inicio . " 00:00:00' , '" . $fim . " 18:00:00' , '" . $dia_sql . "');\n";			
			
			$sabado=date("Y-m-d",strtotime("previous saturday", $dia));
			$domingo=date("Y-m-d",strtotime("previous sunday", $dia));
		}
// TRATANDO TABELA com os jogos da programação
		$tabelas=$resultados->getElementsByTagName('tbody');
		foreach($tabelas as $tabela){};
		$linhas=$tabela->getElementsByTagName('tr');
		foreach($linhas as $linha){
			$colunas=$linha->getElementsByTagName('td');
			$qt_col=0;
			$seq=0;
			foreach($colunas as $coluna){
//				$texto=utf8_decode(trim($coluna->nodeValue));
				$texto=trim($coluna->nodeValue);
				if(strlen($texto)<=2){
					if(ord(substr($texto,0,1))>48&&ord(substr($texto,0,1))<58){
						$seq=intval($texto);
						$ok=TRUE;
					}else{
						$ok=FALSE;
					}
				}else{
					$ok=TRUE;
				}
				if($seq>0&&$ok){
					$qt_col++;
					switch ($qt_col) {
						case 1:
							$jogo['SEQ']=$texto;
							break;
						case 2:
							$jogo['TIME1']=$texto;
							break;
						case 3:
							$jogo['TIME2']=$texto;
							break;
						case 4:
							$jogo['DIA']=$texto;
							if($texto=="SÁBADO"||$texto=="SABADO"||$texto=="Sábado"||$texto=="Sabado"){
								$jogo['DATA']=$sabado;
							}else{
								$jogo['DATA']=$domingo;
							}
							break;
					}
				}
			}
			if($qt_col==4){
				$querys[]="
				INSERT INTO wp_loteca_jogos ( rodada , seq , time1 , time2 , data , dia , inicio , fim ) 
				VALUES (" . $concurso ." , " . $jogo['SEQ'] . " , '" . $jogo['TIME1'] . "' , '" . $jogo['TIME2'] . "' , '" . $jogo['DATA'] . "' , '" . $jogo['DIA'] . "' , '00:00:00' , '00:00:00' );\n";
			}
		}
		query($querys);
	}
	return TRUE;
}

function captura_resultado_cef() {
	$pendente=busca_resultado_pendente();
	if($pendente!=0){
		$resultado_cef=le_pagina_resultados();
		if(!$resultado_cef){
			echo "Erro em le_pagina_resultados\n $resultado_cef";
			return FALSE;
		}
		switch($resultado_cef['CONCURSO']){
			case -1:
				echo 'Erro em le_pagina_resultados -1';
				return FALSE;
//				echo "ERRO NA CAPTURA DOS DADOS NA PAGINA DE RESULTADOS DA CEF\n"; 
				break;
			case 0:
				echo 'Erro em le_pagina_resultados 0';
				return FALSE;
//				echo "PROBLEMAS NO ACESSO AOS DADOS DA PAGINA DE RESULTADOS DA CEF\n"; 
				break;
			case $pendente:
				$concurso=$resultado_cef['CONCURSO'];
				$query=array();
				$erro=FALSE;
				foreach($resultado_cef as $chave => $valor){
					if ($chave!='CONCURSO'){
						switch($valor){
							case 1:
								$time1=1;$empate=0;$time2=0;
								break;
							case 2:
								$time1=0;$empate=1;$time2=0;
								break;
							case 4:
								$time1=0;$empate=0;$time2=1;
								break;
							default:
								$erro=TRUE;
						}
						$querys[]="INSERT INTO `wp_loteca_resultado` VALUES (" . $concurso . " , " .  $chave . " , " . $time1 . " , " . $empate . " , " . $time2 . "); ";
					}
				}
				if(!$erro){
					foreach($querys as $query){
						query($query);
					}
					echo 'Incluidas as informações de resultado\n';
					return TRUE;
//					echo "Capturado resultado do concurso " . $concurso . ".\n";
				}else{
					echo 'Erro na interpretacao dos dados da pagina';
					return FALSE;
//					echo "PROBLEMAS NO TRATAMENTO DAS INFORMAÇÕES RECEBIDAS DA PÁGINA DE RESULTADOS DA CEF;\n";
				}
				break;
			default:
				echo 'Erro em resultado na pagina diferente do esperado\n';
				return FALSE;
//				echo "Resultado esperado do concurso " . $pendente . " e o disponível na CEF é " . $resultado_cef['CONCURSO'] . "\n"; 
				break;
		}
	}else{
		return TRUE;
//		echo "Nenhum resultado pendente de captura.\n";
	}
}

function busca_resultado_pendente(){
	$sql="SELECT * FROM `wp_loteca_rodada` WHERE rodada not in (SELECT rodada from `wp_loteca_resultado`) and dt_sorteio <= CURRENT_DATE()";
	$result=query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		return intval($row['rodada']);
	}
	return FALSE;
}

function le_pagina_resultados(){
	$erro=FALSE;
	$info_resultados=array('CONCURSO' => 0 ,  1 => 0 ,  2 => 0 ,  3 => 0 , 4 => 0 , 
	                                          5 => 0 ,  6 => 0 ,  7 => 0 , 8 => 0 , 
											  9 => 0 , 10 => 0 , 11 => 0 , 12 => 0 , 
											 13 => 0 , 14 => 0);
// busca pagina HTML da programacao da loteca
	$html = file_get_contents_curl('http://www.loterias.caixa.gov.br/wps/portal/loterias/landing/loteca/');
	if(!$html){
		echo "Erro em file_get_contents_curl\n";
		return FALSE;
	}
// carrega HTML da programação como DOMDocument para tratamento das informações
	$doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTML($html,LIBXML_NOERROR);
	libxml_use_internal_errors(false);
	$titulos=NULL;
	if ($doc != NULL){
		$wp_resultados=$doc->getElementById('wp_resultados');
		if ($wp_resultados != NULL){
			$resultados=$wp_resultados->getElementById('resultados');
			if($resultados != NULL){
				$titulos=$resultados->getElementsByTagName('h2');
			}
		}else{
			$resultados=$doc->getElementById('resultados');
			if($resultados != NULL){
				$titulos=$resultados->getElementsByTagName('h2');
			}
		}
	}
// CAPTURA INFORMAÇÃO DO CONCURSO
	if ($titulos != NULL){
		foreach($titulos as $info){
			$spans=$resultados->getElementsByTagName('span');
			foreach($spans as $span){
				$texto=$span->nodeValue;
				$retirar=array("R$ ", "Concurso ","(",")",",");
				$array=explode(" ", str_replace($retirar, "", $texto));
				if(count($array)==2){
//					echo "<BR>ARRAY ENCONTRADO:<BR>";
//					var_dump($array);
//					echo "<BR>";
					$info_resultados['CONCURSO']=$array[0];
					break 2;
				}
			}
		}
	}
	$tabelas=$resultados->getElementsByTagName('table');
	foreach($tabelas as $table){break;}
	$linhas=$table->getElementsByTagName('tr');
	$qt_linha=0;
	$querys=array();
	foreach($linhas as $linha){
		$colunas=$linha->getElementsByTagName('td');
		if($colunas->length>0){
			$qt_linha++;
			$qt_col=0;
			$qt_sel=0;
			foreach($colunas as $coluna){
				$qt_col++;
				$class=$coluna->getAttribute('class');
				if($class=="selected"){
					switch($qt_col){
						case 2:
							$info_resultados[$qt_linha]=1;
							$qt_sel++;
							break;
						case 4:
							$info_resultados[$qt_linha]=2;
							$qt_sel++;
							break;
						case 6:
							$info_resultados[$qt_linha]=4;
							$qt_sel++;
							break;
					}
				}
			}
			if($qt_sel<>1){
				echo "Erro em $qt_sel<>1\n";
				$erro=TRUE;
			}
		}
	}
	if(!$erro){
		foreach($info_resultados as $valor){
			if($valor==0){
				echo "Erro em $valor==0\n";
//				var_dump($info_resultados);
				$erro=TRUE;
				break;
			}
		}
	}
	if($erro){
		$info_resultados=array('CONCURSO' => -1 ,  1 => 0 ,  2 => 0 ,  3 => 0 ,  4 => 0 , 
											 	   5 => 0 ,  6 => 0 ,  7 => 0 ,  8 => 0 , 
										 		   9 => 0 , 10 => 0 , 11 => 0 , 12 => 0 , 
												  13 => 0 , 14 => 0);
	}
	return $info_resultados;
}

function file_get_contents_curl($url) {
	global $proxy;
    $ch = curl_init();
	if ($proxy) {
		$chave=prompt_silent("User:");
		$senha=prompt_silent();
		curl_setopt($ch, CURLOPT_PROXY, 'cache.bb.com.br');
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $chave.':'.$senha);
		curl_setopt($ch, CURLOPT_PROXYPORT, '80');
	}
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);   
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); 
    $data = curl_exec($ch);
	if(curl_errno($ch)){
		return FALSE;
//		echo 'ERRO: ' . curl_errno($ch) . " -> " . curl_error($ch) ;
	}
    curl_close($ch);
    return $data;
}

function captura_cef(){
	if (!captura_resultado_cef()){
		echo 'Erro em captura_resultado_cef';
		return FALSE;
	};
	$info_ult_rodada=busca_info_ult_rodada();
	$rodada=$info_ult_rodada['rodada'];
	$rodada++;
	$dt_sorteio=$info_ult_rodada['dt_sorteio'];
	$dia=DateTime::createFromFormat('Y-m-d H:i:s', $dt_sorteio . " 18:00:00")->getTimestamp();
	$hoje=time();
	if ($dia<=$hoje){
		if(!captura_programacao($rodada)){
			echo '<BR>Erro em captura_programacao<BR>';
			return FALSE;
		}else{
			return TRUE;
		}
	} /* else{
		echo 'Erro em $dia<=$hoje';
		return FALSE;
//		echo "# Aguardando prazo para captura da programação " . $rodada . ". (" . date("Y-m-d H:i:s",$dia) . ">" . date("Y-m-d H:i:s",$hoje) . ") #\n";
	} */
	return FALSE;
}

function loteca_captura(){
prepara_ambiente();
config_conexao_mysql();
if(captura_cef()){
	return "JOGOS CAPTURADOS";
}
;
	
}
?>