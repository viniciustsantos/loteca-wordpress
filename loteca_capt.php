<?php

include_once 'loteca_geral.php';
include_once 'loteca_db_functions.php';

function limpa_texto($string){
// SOLUÇÃO 3

$result = strtr(
    $string,
    array (
 
      'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
      'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
      'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N',
      'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
      'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ŕ' => 'R',
      'Þ' => 's', 'ß' => 'B', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
      'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
      'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
      'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
      'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y',
      'þ' => 'b', 'ÿ' => 'y', 'ŕ' => 'r'
    ));
/*  SOLUÇÃO 2  
	$string = preg_replace("/[ÁÀÂÃÄáàâãä]/", "a", $string);
    $string = preg_replace("/[ÉÈÊéèê]/", "e", $string);
    $string = preg_replace("/[ÍÌíì]/", "i", $string);
    $string = preg_replace("/[ÓÒÔÕÖóòôõö]/", "o", $string);
    $string = preg_replace("/[ÚÙÜúùü]/", "u", $string);
    $string = preg_replace("/Çç/", "c", $string);
    $string = preg_replace("/[][><}{)(:;,!?*%~^`&#@]/", "", $string);
    $result = preg_replace("/ /", "_", $string);
	*/
//	SOLUÇÃO 1
// $result=preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', $string ) );
    $result = strtoupper($result);
	return $result;
}

function captura_programacao(){
	error_log("Capturando programação. Checando versão do PHP : " . phpversion() );
	$jogos_ok=FALSE;
	$rodada_ok=FALSE;
	$qt_jogos=0;
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
		$estimado="0.0";
		$possiveis_estimados=$resultados->getElementsByTagName('p');
		foreach($possiveis_estimados as $possivel){
			if($possivel->getAttribute('class')=="value"){
				$retirar=array("R$ ",".");
				$estimado=str_replace(',','.',str_replace($retirar,"",$possivel->nodeValue));
			}
		}
		
		$infos=$resultados->getElementsByTagName('small');
		foreach($infos as $info){
			$texto=$info->nodeValue;
			$retirar=array("Concurso ","(",")",",","\r\n","\t","     ","    ","   ","  ");
			$array=explode(" ", trim(str_replace($retirar, " ", $texto)));
			error_log(" Cacpturando programação ... " . print_r($array,true));
			$dia=DateTime::createFromFormat('d/m/Y', $array[1])->getTimestamp();
			$concurso=$array[0];
//			$ajuste=strtotime("-2 days", $dia);
//			$inicio=date("Y-m-d",strtotime("previous monday", $ajuste));
			$fim=date("Y-m-d",strtotime("previous friday", $dia));
			$fim2=strtotime("previous friday", $dia);
			$inicio=date("Y-m-d",strtotime("previous monday", $fim2));
			$fim=date("Y-m-d",strtotime("previous friday", $dia));
//			$dia_sql=DateTime::createFromFormat('Y-m-d', $array[1])->getTimestamp();
			$dia_sql=date("Y-m-d",$dia);
			$sql="
			REPLACE INTO wp_loteca_rodada ( rodada , dt_inicio_palpite , dt_fim_palpite , dt_sorteio , vl_premio_estimado, ts_atualizacao) 
			VALUES ( " . $concurso ." , '" . $inicio . " 00:00:00' , '" . $fim . " 21:00:00' , '" . $dia_sql . "' , " . $estimado . " , NOW() );\n";			
			error_log("INSERINDO NOVA RODADA: " . $sql);
			$querys[]=$sql;
			$sabado=date("Y-m-d",strtotime("previous saturday", $dia));
			$domingo=date("Y-m-d",strtotime("previous sunday", $dia));
			$segunda=date("Y-m-d",strtotime("previous monday", $dia));
			$terca=date("Y-m-d",strtotime("previous tuesday", $dia));
			error_log("SABADO: " . $sabado . " - DOMINGO: " . $domingo . " - SEGUNDA: " . $segunda . " - TERÇA: " . $terca);
			$rodada_ok=TRUE;
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
				$links=$coluna->getElementsByTagName('a');
				$link="";
				foreach($links as $link){
					foreach($link->attributes as $atributo){
						if($atributo->name=='href'){
							$link=$atributo->value;
						}
					}
				}
				if(strlen($texto)<=2){
					if(ord(substr($texto,0,1))>48&&ord(substr($texto,0,1))<58){
						$seq=intval($texto);
						$ok=TRUE;
					}else{
						if($link!=''){
							$ok=TRUE;
						}else{
							$ok=FALSE;
						}
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
							error_log ( $texto . " -> " . limpa_texto($texto));
							switch (limpa_texto($texto)) {
								case "SABADO":
									$jogo['DATA']=$sabado;
									break;
								case "DOMINGO":
									$jogo['DATA']=$domingo;
									break;
								case "SEGUNDA-FEIRA":
								case "SEGUNDA":
									$jogo['DATA']=$segunda;
									break;
								case "TERCA-FEIRA":
								case "TERCA":
									$jogo['DATA']=$terca;
									break;
							}
							break;
						case 5:
							$pos=strpos($link,"http://www.caixa.gov.br/estatisticas-futebol-loterias-caixa/loteca");
							if(substr($link,$pos+66)!=$seq){
								$link=substr($link,0,$pos+66).$seq;
								error_log("link novo:" . $link);
							}
							
								$teste = getWebPage($link, 'trackAllLocations');
								error_log(print_r($teste,true));
							
							$jogo['LINK']=get_redirect_curl($link);
							if(substr($jogo['LINK'],-6,6)=="_error"){
								$jogo['LINK']='';
//							}else{
//								
//								$teste = getWebPage($link, 'trackAllLocations');
//								error_log(print_r($teste,true));
//								
							}
							break;
					}
				}
			}
			if($qt_col==4||$qt_col==5){
/*
				$sql="
				INSERT INTO wp_loteca_jogos ( rodada , seq , time1 , time2 , data , dia , inicio , fim ) 
				VALUES (" . $concurso ." , " . $jogo['SEQ'] . " , '" . $jogo['TIME1'] . "' , '" . $jogo['TIME2'] . "' , '" . $jogo['DATA'] . "' , '" . $jogo['DIA'] . "' , '00:00:00' , '00:00:00' );\n";
*/
				$sql="
				REPLACE INTO wp_loteca_jogos ( rodada , seq , time1 , time2 , data , dia , inicio , fim , link_stat ) 
				VALUES (" . $concurso ." , " . $jogo['SEQ'] . " , '" . $jogo['TIME1'] . "' , '" . $jogo['TIME2'] . "' , '" . $jogo['DATA'] . "' , '" . $jogo['DIA'] . "' , '00:00:00' , '00:00:00' , '" . $jogo['LINK'] . "');\n";
				error_log("INSERINDO NOVO JOGO: " . $sql);
				$querys[]=$sql;
				$qt_jogos++;
			}
			if($qt_jogos==14){
				$jogos_ok=TRUE;
			}
		}
		if($rodada_ok&&$jogos_ok){
			lote_query($querys, 'loteca_capt.php(captura_programacao)');
		}
	}
	if($rodada_ok&&$jogos_ok){
		return TRUE;
	}else{
		return FALSE;
	}
}

function captura_resultado_cef() {
	include_once 'loteca_db_functions.php';
	$pendente=resultado_pendente();
	if($pendente!=0){
		$resultado_cef=le_pagina_resultados();
		if(!$resultado_cef){
//			echo "Erro em le_pagina_resultados\n $resultado_cef";
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
						$querys[]="INSERT INTO `wp_loteca_resultado`  (rodada , seq , time1 , empate , time2 )  VALUES (" . $concurso . " , " .  $chave . " , " . $time1 . " , " . $empate . " , " . $time2 . "); ";
					}
				}
				if(!$erro){
					foreach($querys as $query){
						lote_query($query, 'loteca_capt.php(captura_resultado_cef)');
					}
//					echo 'Incluidas as informações de resultado\n';
					return TRUE;
//					echo "Capturado resultado do concurso " . $concurso . ".\n";
				}else{
//					echo 'Erro na interpretacao dos dados da pagina';
					return FALSE;
//					echo "PROBLEMAS NO TRATAMENTO DAS INFORMAÇÕES RECEBIDAS DA PÁGINA DE RESULTADOS DA CEF;\n";
				}
				break;
			default:
//				echo 'Erro em resultado na pagina diferente do esperado\n';
				return FALSE;
//				echo "Resultado esperado do concurso " . $pendente . " e o disponível na CEF é " . $resultado_cef['CONCURSO'] . "\n"; 
				break;
		}
	}else{
		return true;
//		echo "Nenhum resultado pendente de captura.\n";
	}
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
			} else {
				error_log("Não foi possível capturar os dados da página da CEF (A). HTML: >" . $doc->saveHTML());
				$erro=TRUE;
			}
		}else{
			$resultados=$doc->getElementById('resultados');
			if($resultados != NULL){
				$titulos=$resultados->getElementsByTagName('h2');
			} else {
				error_log("Não foi possível capturar os dados da página da CEF (B). HTML: >" . $doc->saveHTML());
				$erro=TRUE;
			}
		}
	}else{
		echo "Erro em $valor==0\n";
		$erro=TRUE;
	}
// CAPTURA INFORMAÇÃO DO CONCURSO
	if(!$erro){
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
	}
	if($erro){
		$info_resultados=array('CONCURSO' => -1 ,  1 => 0 ,  2 => 0 ,  3 => 0 ,  4 => 0 , 
											 	   5 => 0 ,  6 => 0 ,  7 => 0 ,  8 => 0 , 
										 		   9 => 0 , 10 => 0 , 11 => 0 , 12 => 0 , 
												  13 => 0 , 14 => 0);
	}
	return $info_resultados;
}

function get_redirect_curl($url) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); 
$a = curl_exec($ch); // $a will contain all headers
$url2 = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
$url2 = substr($url2,0,strpos($url2,'/s4/'));
$link=substr($a,strpos($a,'window.location = protocol + host + "')+37);
$link=substr($link,0,strpos($link,'";'));
return $url2 . $link; // Voila	
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

function loteca_captura_cef(){
	// retorno 1 - capturado resultado e programacao
	// retorno 2 - capturado resultado e programacao pendente
	// retorno 3 - capturado resultado e problemas na captura da programacao
	// retorno 5 - problemas na captura do resultado
	if(resultado_pendente()){
		$retorno_captura_resultado=captura_resultado_cef();
		if($retorno_captura_resultado==0){
			return 5;
		}
	}
	$info_ult_rodada=ultima_rodada();
	$rodada=$info_ult_rodada->rodada;
	$rodada++;
	$dt_sorteio=$info_ult_rodada->dt_sorteio;
	$dia=DateTime::createFromFormat('Y-m-d H:i:s', $dt_sorteio . " 11:00:00")->getTimestamp();
	$hoje=time();
//	if ($dia<=$hoje){
	if (programacao_pendente()){
		if(!captura_programacao($rodada)){
			return 3;
		}else{
			return 1;
		}
	}else{
		return 2;
	}

	/* else{
		echo 'Erro em $dia<=$hoje';
		return FALSE;
//		echo "# Aguardando prazo para captura da programação " . $rodada . ". (" . date("Y-m-d H:i:s",$dia) . ">" . date("Y-m-d H:i:s",$hoje) . ") #\n";
	} */
	return FALSE;
}

function loteca_captura(){
	if(captura_cef()){
		return "JOGOS CAPTURADOS";
	}
}

function getWebPage($url, $redirectcallback = null, $count=0){
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // OK
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // OK
    curl_setopt($ch, CURLOPT_HEADER, true); // DIFERENTE
//    curl_setopt($ch, CURLOPT_NOBODY, false); // NAO UTILIZADO
//    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // NAO UTILIZADO
//    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // NAO UTILIZADO
//    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0"); // NAO UTILIZADO

//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header

	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); 
	
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        list($httpheader) = explode("\r\n\r\n", $html, 2);
        $matches = array();
		error_log("httpheader:" . print_r($httpheader,true));
        preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
		error_log("matches:" . print_r($matches,true));
        $nurl = trim(array_pop($matches));
        $url_parsed = parse_url($nurl);
        if (isset($url_parsed)) {
            if($redirectcallback){ // callback
                 $redirectcallback($nurl, $url);
            }
			if(($count<10)&&($nurl!=$url)){
				$html = getWebPage($nurl, $redirectcallback, ++$count);
			}
        }
    }
    return $html;
}

function trackAllLocations($newUrl, $currentUrl){
    error_log("REDIRECT: " . $currentUrl.' ---> '.$newUrl."\r\n");
}

?>