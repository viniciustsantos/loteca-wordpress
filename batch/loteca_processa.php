<?php

// FUNCOES -----------------------------------------------------

function prepara_jogos($jogos_escolhidos,$quantidade_desejada){
	global $contem_base, $proc_ze, $proc_grupo;
	global $arquivo_grupo;
	if (count($jogos_escolhidos)>0){
		$zebra_pendente=$proc_ze;
	    $zebra_repetida=array();
		foreach(array_keys($zebra_pendente) as $key){
			if($zebra_pendente[$key]==0){
				unset($zebra_pendente[$key]);
			}else{
				foreach($contem_base[$zebra_pendente[$key]] as $val){
					$zebra_repetida[$key][$val]=0;
				}
			}
		}
		$zebra_pendente_original=$zebra_pendente;
//		var_dump ($zebra_pendente);exit;
		for($x=1;$x<=14;$x++){
			$ja_proc[$x]=array( 1 => FALSE , 2 => FALSE , 4 => FALSE );
		}
		$qtd_jogos=0;
		$qtd_volantes=0;
		$jogos_processados=array();
		$jogos_selecionados=array();
		$proc=array();
		$zebra_ant=array();
		foreach($jogos_escolhidos as $jogos_por_peso){
			// este shuffle é para deixar aleatório somente os volantes que apresentam o mesmo peso para a selecao entre pesos iguais se tornar aleatória
			if (!shuffle($jogos_por_peso)){
				gr_l("Problema na randomização do array de jogos escolhidos : " . $arquivo_grupo);
			};
			
			foreach($jogos_por_peso as $jogo){
				
				if(count($jogos_selecionados)!=0){
					$qt_zebras=floor(($proc_grupo['MIN_ZEBRAS']+$proc_grupo['MIN_ZEBRAS'])/2);
					$zebra_ok=zebra_ok($jogo['ZEBRAS'],$qt_zebras,$zebra_repetida,$zebra_pendente,$zebra_ant);
				}
				
				if((count($jogos_selecionados)==0)||($zebra_ok)){
					for($x=1;$x<=14;$x++){
//                      BASTA QUE UM RESULTADO SEJA DIFERENTE PARA QUE O JOGO SEJA VÁLIDO PARA FORMAR O RESULTADO
						$achou=FALSE;
						foreach($contem_base[$jogo[$x]] as $proc){
							if((($proc==1)&&($ja_proc[$x][1]))||
							   (($proc==2)&&($ja_proc[$x][2]))||
							   (($proc==4)&&($ja_proc[$x][4]))){
								$achou=TRUE;
								break;
							}
						}
						if($achou==FALSE){
							$qtd_volantes++;
							$jogos_selecionados[]=$jogo;
							if($qtd_volantes==$quantidade_desejada){
								return $jogos_selecionados;
							}
							$zebra_ant=$jogo['ZEBRAS'];
							foreach($zebra_pendente as $key => $seq){
								if(isset($jogo['ZEBRAS'][$key][$seq])&&($jogo['ZEBRAS'][$key][$seq]==1)){
									unset($zebra_pendente[$key]);
								}
							}
							if(count($zebra_pendente)==0){
								$zebra_pendente=$zebra_pendente_original;
							}
							foreach($zebra_repetida as $key => $zebra){
								foreach(array_keys($zebra) as $seq){
									if((isset($jogo['ZEBRAS'][$key][$seq]))&&($jogo['ZEBRAS'][$key][$seq]==1)){
										$zebra_repetida[$key][$seq]++;
									}else{
										$zebra_repetida[$key][$seq]=0;
									}
								}
							}

							for($y=1;$y<=14;$y++){
								foreach($contem_base[$jogo[$y]] as $proc){
									$ja_proc[$y][$proc]=TRUE;
								}
							}
							break;
						}
					}
				}
			}
		}
		return $jogos_selecionados;
	}
}

function zebra_ok($zebras,$qt_zebras,$zebra_repetida,$zebra_pendente,&$zebra_ant){
	$erro_zebra=FALSE;

//  TESTE ZEBRA 001 - INICIO
//   - Se um zebra repetir em dois volantes anteriores não será utilizada na próxima 
	$repetiu_zebra=FALSE;
	foreach($zebra_repetida as $key => $zebra){
		foreach(array_keys($zebra) as $seq){
			if((isset($zebras[$key][$seq]))&&($zebras[$key][$seq]==1)){
				if($zebra_repetida[$key][$seq]>=2){
					$repetiu_zebra=TRUE;
					$erro_zebra=TRUE;
				}
			}
		}
	}
//  TESTE ZEBRA 001 - FIM
				
	if(!$erro_zebra){
//  TESTE ZEBRA 002 - INICIO
//   - Se houver zebra ainda não escolhida, o sistema calcula o mínimo de zebras a serem processadas para o volante
		$zebra_nova=0;
		if(count($zebra_pendente)>0){
			foreach($zebra_pendente as $key => $seq){
				if((isset($zebras[$key][$seq]))&&($zebras[$key][$seq]==1)){
					$zebra_nova++;
//					break;
				}
			}
			$qt_min_zebra_nova=floor((count($zebra_pendente)/3)*2);
			if($qt_min_zebra_nova<=0){
				$qt_min_zebra_nova=1;
			}
			if($qt_min_zebra_nova>$qt_zebras){
				if($qt_zebras>2){
					$qt_min_zebra_nova=$qt_zebras-1;
				}else{
					$qt_min_zebra_nova=1;
				}
			}
//		echo "\n 1. NOVAS : " . $zebra_nova  . " MINIMO : " . $qt_min_zebra_nova . "\n";
			if($zebra_nova>=$qt_min_zebra_nova){
//				echo "\n 2. NOVAS : " . $zebra_nova  . " MINIMO : " . $qt_min_zebra_nova . "\n";
				ksort($zebras);
				foreach(array_keys($zebras) as $idx_zebra){
					asort($zebras[$idx_zebra]);
				}
				if($zebra_ant==$zebras){
					$erro_zebra=TRUE;
				}
			}else{
				$erro_zebra=TRUE;
			}
		}
//  TESTE ZEBRA 002 - FIM
	}
	return !$erro_zebra;
}

function captura_parametros(){ // ok
	$parametros=array();
	$sql='SELECT limite_proc FROM wp_loteca_parametro ORDER BY data DESC LIMIT 1;';
	$result=query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$parametros=array( 'LIMITE' => $row['limite_proc'] );
		return $parametros;
	}
}

function trata_jogo($jogo){

// Esta função verifica o jogo informado, se enquadrando nos parametros de jogos a serem selecionados 
// o jogo será armazenado em uma variavel global
// deve conter a quantidade correta de zebras
// deve ter o peso minimo, que será recalculado sempre que houverem mais de $max_array jogos selecionados
// 
	global $proc_selecionados, $proc_peso_min, $proc_cnt_ok;
	global $max_array, $mid_array;
	if (($jogo['PESO']>$proc_peso_min)&&($proc_cnt_ok>$max_array)){
// krsort utilizado para ordem reversa dos pesos, assim serão eliminados os pesos menores até que a quantidade total de selecionados seja inferior a $max_array.					
		$chaves=array_keys($proc_selecionados);
		rsort($chaves);
		$qtd_proc_temp=0;
		foreach($chaves as $idx){
			$qtd_proc_temp+=count($proc_selecionados[$idx]);
			if($qtd_proc_temp>$mid_array){
				$peso=intval(substr($idx,0,5));
				if ($proc_peso_min<$peso){
					$proc_peso_min=$peso;
				}
				$proc_cnt_ok=$proc_cnt_ok-count($proc_selecionados[$idx]);
				unset($proc_selecionados[$idx]);
			}
		}
	}
	if ($jogo['PESO']>$proc_peso_min){
// como os jogos são processados fora da ordem da tabela da CEF precisam ser reordenados pela chave ao serem liberados
		ksort($jogo);
		$idx=sprintf("%05d%02d",$jogo['PESO'],$jogo['QT_ZEBRAS']);
		$proc_selecionados[$idx][]=$jogo;
		$proc_cnt_ok++;
	}
}

function sem_zebra($zebras,$jogo){
	$sem_zebra=FALSE;
	switch($zebras){
		case 0:
			$sem_zebra=TRUE;
		case 1:
			if(($jogo==2)||($jogo==4)||($jogo==6)){
				$sem_zebra=TRUE;
			}
			break;
		case 2:
			if(($jogo==1)||($jogo==4)||($jogo==5)){
				$sem_zebra=TRUE;
			}
			break;
		case 3:
			if($jogo==4){
				$sem_zebra=TRUE;
			}
			break;
		case 4:
			if(($jogo==1)||($jogo==2)||($jogo==3)){
				$sem_zebra=TRUE;
			}
			break;
		case 5:
			if($jogo==2){
				$sem_zebra=TRUE;
			}
			break;
		case 6:
			if($jogo==1){
				$sem_zebra=TRUE;
			}
			break;
	}
	return $sem_zebra;
}

function tem_zebra($zebras,$jogo){
	$tem_zebra=FALSE;
	switch($zebras){
		case 0:
			break;
		case 1:
			if(($jogo==3)||($jogo==5)||($jogo==7)){
				$tem_zebra=1;
			}
			break;
		case 2:
			if(($jogo==3)||($jogo==6)||($jogo==7)){
				$tem_zebra=2;
			}
			break;
		case 3:
			if(($jogo==5)||($jogo==6)){
				$tem_zebra=$jogo-4;
			}
			break;
		case 4:
			if(($jogo==5)||($jogo==6)||($jogo==7)){
				$tem_zebra=4;
			}
			break;
		case 5:
			if(($jogo==3)||($jogo==6)){
				$tem_zebra=$jogo-2;
			}
			break;
		case 6:
			if(($jogo==3)||($jogo==5)){
				$tem_zebra=$jogo-1;
			}
			break;
	}
	return $tem_zebra;
}

function proc_cartao($jogo,$pos){
	global $contem_base;
	global $proc_cnt, $proc_parm, $seq_proc;
	global $proc_op, $proc_pesos, $proc_ze, $proc_grupo,  $proc_pulado, $proc_peso_min;
	$falta=14-$pos;
	foreach($proc_op[$seq_proc[$pos]] as $jogo[$seq_proc[$pos]]) {
		$tem_zebra=tem_zebra($proc_ze[$seq_proc[$pos]],$jogo[$seq_proc[$pos]]);
		if($tem_zebra){
			if($jogo['QT_ZEBRAS']>=$proc_grupo['MAX_ZEBRAS']){
				$proc_pulado++;
				continue;
			}else{
				$jogo['ZEBRAS'][$seq_proc[$pos]][$tem_zebra]=1;
				$jogo['QT_ZEBRAS']++;
			}
		}else{
			if($proc_grupo['MIN_ZEBRAS']>$jogo['QT_ZEBRAS']+$falta){
				$proc_pulado++;
				continue;
			}
		}
		$peso_atu=0;
		foreach($contem_base[$jogo[$seq_proc[$pos]]] as $y){
			if(isset($proc_pesos['P'][$seq_proc[$pos]][$y])){
				$peso_atu+=$proc_pesos['P'][$seq_proc[$pos]][$y];
			}
		}
		$jogo['PESO']+=$peso_atu;

		$proc_op['PESO_RESTA']-=$proc_op['PESO_MAX'][$seq_proc[$pos]];
		
		if($jogo['PESO']+$proc_op['PESO_RESTA']>=$proc_peso_min){
			if($pos<14){
				$pos2=$pos+1;
				proc_cartao($jogo,$pos2);
			}else{
				$proc_cnt++;
				trata_jogo($jogo);			
			}
		}else{
			$proc_pulado++;
		}
		
		if($tem_zebra){
			$jogo['QT_ZEBRAS']--;
			unset($jogo['ZEBRAS'][$seq_proc[$pos]][$tem_zebra]);
			if(count($jogo['ZEBRAS'][$seq_proc[$pos]])==0){
				unset($jogo['ZEBRAS'][$seq_proc[$pos]]);
			}
		};
		$jogo['PESO']-=$peso_atu;
		$proc_op['PESO_RESTA']+=$proc_op['PESO_MAX'][$seq_proc[$pos]];
	}

}

function ordem_jogos($opcao,$pesos,&$op){
// 2. cria array $seq_proc() que será utilizada para gerar as variações de jogos mais indefinidos primeiro;
//    os jogos serão processados fora da ordem informada pela CEF;
//    Após o krsort é necessário refazer os indices do array pois estamos trabalhando com o sequencial
//    do jogo como indice;
// 3. cria array $peso()
//    Este array recebe o peso de cada resultado escolhido e é ordenado pelo conteúdo 
//    mantendo a relação com o indexador e o array $op() é carregado com os jogos na ordem de 
//    maior para menor peso;
//    Neste caso estamos deixando para desdobrar os jogos de maior peso primeiro que é o que buscamos
//    interfere um pouco na aleatoriedade mas é preciso reduzir a quantidade de desdobramentos
//    processados para não gastar muito tempo com resultados muito distantes dos palpites dos 
//    participantes;
// 4. cria array $op()
// 5. Lembrando $opcao['X'] são todas as possibilidades de jogos
	$seq_proc=array();
	for($x=1;$x<=14;$x++){
// chave_1,_2 e _3 são os pesos de cada "X" ordenados, para que a seleção dos itens seja feita com o peso maior primeiro
// neste caso o array seq_proc recebe como chave a junção destas chaves mais o proprio sequencial para evitar sobreposição
// e o valor atribuido é o sequencial
		$chave_1=0;
		$chave_2=0;
		$chave_3=0;
		$grupo=0;
		$qt_x=3;
		$pesos_p=array();
		if(($opcao['S'][$x]==1)||($opcao['S'][$x]==3)||($opcao['S'][$x]==5)||($opcao['S'][$x]==7)){$pesos_p[1]=$pesos['P'][$x][1];}else{$pesos_p[1]=0;}
		if(($opcao['S'][$x]==2)||($opcao['S'][$x]==3)||($opcao['S'][$x]==6)||($opcao['S'][$x]==7)){$pesos_p[2]=$pesos['P'][$x][2];}else{$pesos_p[2]=0;}
		if(($opcao['S'][$x]==4)||($opcao['S'][$x]==5)||($opcao['S'][$x]==6)||($opcao['S'][$x]==7)){$pesos_p[4]=$pesos['P'][$x][4];}else{$pesos_p[4]=0;}
		if($pesos_p[1]>$pesos_p[2]){
			if($pesos_p[1]>$pesos_p[4]){
				$chave_1=$pesos_p[1];
				if($pesos_p[2]>$pesos_p[4]){
					$chave_2=$pesos_p[2];
					$chave_3=$pesos_p[4];
				}else{
					$chave_2=$pesos_p[4];
					$chave_3=$pesos_p[2];
				}
			}else{
				$chave_1=$pesos_p[4];
				$chave_2=$pesos_p[1];
				$chave_3=$pesos_p[2];
			}
		}else{
			if($pesos_p[2]>$pesos_p[4]){
				$chave_1=$pesos_p[2];
				$chave_2=$pesos_p[4];
				$chave_3=$pesos_p[1];
			}else{
				$chave_1=$pesos_p[4];
				$chave_2=$pesos_p[2];
				$chave_3=$pesos_p[1];
			}
		}
		if($chave_1>0){$qt_x--;};
		if($chave_2>0){$qt_x--;};
		if($chave_3>0){$qt_x--;};
		if(isset($opcao['Z'][$x])){
			if($opcao['Z'][$x]==0){
				$tem_zebra=0;
			}else{
				$tem_zebra=1;
			}
		}else{
			$tem_zebra=0;
		}
		if($opcao['S'][$x]==$opcao['F'][$x]){
			// somente fixos
				$grupo=9;
		}else{
			if($tem_zebra==0){
				if($opcao['F'][$x]<>0){
					// fixos e opcionais, sem zebras
					$grupo=8;
				}else{
					// somente opcionais
					$grupo=7;
				}
			}else{
				if($opcao['F'][$x]<>0){
				// fixos, opcionais e zebras
					$grupo=6;
				}else{
					// somente opcionais e zebras
					$grupo=5;
				}
			}
		}
		$chave_peso=sprintf("%01d%01d%04d%04d%04d%02d",$grupo,$qt_x,$chave_1,$chave_2,$chave_3,$x);
		$seq_proc[$chave_peso]=$x;
		$peso=array();
		foreach($opcao['X'][$x] as $k => $y){
			$peso[$k]=0;
			if($y==1||$y==3||$y==5||$y==7){
				if($peso[$k]==0){
					if($pesos['P'][$x][1]!=0){
						$peso[$k]=$pesos['P'][$x][1];
					}
				}else{
					if($pesos['P'][$x][1]!=0){
						$peso[$k]=intval(round(($peso[$k]+$pesos['P'][$x][1])/2));
					}
				}
			}
			if($y==2||$y==3||$y==6||$y==7){
				if($peso[$k]==0){
					if($pesos['P'][$x][2]!=0){
						$peso[$k]=$pesos['P'][$x][2];
					}
				}else{
					if($pesos['P'][$x][2]!=0){
						$peso[$k]=intval(round(($peso[$k]+$pesos['P'][$x][2])/2));
					}
				}
			}
			if($y==4||$y==5||$y==6||$y==7){
				if($peso[$k]==0){
					if($pesos['P'][$x][4]!=0){
						$peso[$k]=$pesos['P'][$x][4];
					}
				}else{
					if($pesos['P'][$x][4]!=0){
						$peso[$k]=intval(round(($peso[$k]+$pesos['P'][$x][4])/2));
					}
				}
			}
		}
		arsort($peso);
		$y=0;
		foreach(array_keys($peso) as $k){
			$y++;
			$op[$x][$y]=$opcao['X'][$x][$k];
		}
		$jogo[$x]=0;
	}
	if(count($seq_proc)!=14){
		gr_l("Contagem de sequencias de jogos diferente que 14.\n");
		return NULL;
	}
	if(!ksort($seq_proc)){
		gr_l("Ordenação do sequencial de processamento pelo peso falhou.\n");
		return NULL;
	};
	$y=0;
	foreach($seq_proc as $x){
		$seq_tmp[++$y]=$x;
	}
	$seq_proc=$seq_tmp;
	return $seq_proc;
}

function gera($tamanho,$qtd_1,$tipo,$chaves,$pos,&$saida,&$control){
	$pos++;
	foreach( array( ' ' , $tipo ) as $val){
		if(isset($saida[$control][$chaves[$pos]])){
			if($val==' '){
				unset($saida[$control][$chaves[$pos]]);
			}
		}else{
			if($val==$tipo){
				$saida[$control][$chaves[$pos]]=$val;
			}
		}
		if($pos < ($tamanho -1)){
			gera($tamanho, $qtd_1, $tipo, $chaves, $pos, $saida,$control);
		}else{
			if(isset($saida[$control])){
				$conta=array_count_values($saida[$control]);
				if((isset($conta[$tipo]))&&($conta[$tipo]==$qtd_1)){
					$atual=$control;
					$control++;
					$saida[$control]=$saida[$atual];
				}
			}
		}
	}
}

function gera_01($tamanho,$qtd_1,$tipo,$chaves){
	$saida=array();
	$control=1;
	gera($tamanho,$qtd_1,$tipo, $chaves, -1 , $saida,$control);
	if(array_count_values($saida[$control])[$tipo]!=$qtd_1){
		unset($saida[$control]);
	}
	return $saida;
}

function gera_multi_opcoes($opcao,$volante,&$qt_multi){
	global $validos;
	global $proc_ze, $proc_grupo;
	$triplos = $validos[$volante['I']]['T'];
	$duplos = $validos[$volante['I']]['D'];
	$simples = $validos[$volante['I']]['S'];
	$opcoes=array();
	$tipos=array('S' => array() ,'D' => array() ,'T' => array() );
	foreach($opcao as $k => $escolhas){
		foreach($escolhas as $sel){
			switch ($sel){
				case 1:
				case 2:
				case 4:
					$tipos['S'][$k][]=$sel;
					break;
				case 3:
				case 5:
				case 6:
					$tipos['D'][$k][]=$sel;
					break;
				case 7:
					$tipos['T'][$k][]=$sel;
					break;
			}
		}
	}
	$qtd_opcao=0;
	$total=0;
	if($triplos>0){
		$combinacoes_t=gera_01(count($tipos['T']),$triplos,'T',array_keys($tipos['T']));
		foreach($combinacoes_t as $combinacao_t){
			$tipo_d=$tipos['D'];
			foreach(array_keys($combinacao_t) as $key_t){
				unset($tipo_d[$key_t]);
			}
			$combinacoes_d=gera_01(count($tipo_d),$duplos,'D',array_keys($tipo_d));
			foreach($combinacoes_d as $combinacao_d){
				$qt_temp=1;
				$qtd_opcao++;
				foreach(array_keys($combinacao_d) as $key_d){
					$opcoes[$qtd_opcao][$key_d]=$tipos['D'][$key_d];
					$qt_temp*=count($tipos['D'][$key_d]);
				}
				foreach(array_keys($combinacao_t) as $key_t){
					$opcoes[$qtd_opcao][$key_t]=$tipos['T'][$key_t];
					$qt_temp*=count($tipos['T'][$key_t]);
				}
				$erro=FALSE;
				for($x=1;$x<=14;$x++){
					if(!isset($opcoes[$qtd_opcao][$x])){
						if(isset($tipos['S'][$x])){
							$opcoes[$qtd_opcao][$x]=$tipos['S'][$x];
							$qt_temp*=count($tipos['S'][$x]);
						}else{
							$erro=TRUE;
						}
					}
				}
				if($erro){
					unset($opcoes[$qtd_opcao]);
					$qtd_opcao--;
				}else{
					$total+=$qt_temp;
					$opcoes[$qtd_opcao]['TOTAL']=$qt_temp;
				}
			}
		}
	}else{
		$combinacoes_d=gera_01(count($tipos['D']),$duplos,'D',array_keys($tipos['D']));
		foreach($combinacoes_d as $combinacao_d){
			$qt_temp=1;
			$qtd_opcao++;
			foreach(array_keys($combinacao_d) as $key_d){
				$opcoes[$qtd_opcao][$key_d]=$tipos['D'][$key_d];
				$qt_temp*=count($tipos['D'][$key_d]);
			}
			$erro=FALSE;
			for($x=1;$x<=14;$x++){
				if(!isset($opcoes[$qtd_opcao][$x])){
					if(isset($tipos['S'][$x])){
						$opcoes[$qtd_opcao][$x]=$tipos['S'][$x];
						$qt_temp*=count($tipos['S'][$x]);
					}else{
						$erro=TRUE;
					}
				}
			}
			if($erro){
				unset($opcoes[$qtd_opcao]);
				$qtd_opcao--;
			}else{
				$total+=$qt_temp;
				$opcoes[$qtd_opcao]['TOTAL']=$qt_temp;
			}
		}
	}

	foreach($opcoes as $key => $opcao){
		$qt_zebra=0;
		$qt_sem_zebra=0;
		$qt_temp=1;
		foreach($opcao as $seq => $jogo){
			if($seq!='TOTAL'){
				$tem_zebra=FALSE;
				$sem_zebra=FALSE;
				foreach($jogo as $op){
					if(tem_zebra($proc_ze[$seq],$op)){
						$tem_zebra=TRUE;
					}
					if(sem_zebra($proc_ze[$seq],$op)){
						$sem_zebra=TRUE;
					}
				}
				if($tem_zebra){
					$qt_zebra++;
				}
				if($sem_zebra){
					$qt_sem_zebra++;
				}
			}
		}
		if(($qt_zebra<$proc_grupo['MIN_ZEBRAS'])||(14-$qt_sem_zebra>$proc_grupo['MAX_ZEBRAS'])){
			$total-=$opcao['TOTAL'];
			unset($opcoes[$key]);
		}
	}
	$qt_multi=$total;
	if($total==0){
		return NULL;
	}
	return $opcoes;
}

function proc_desdobra($opcao,$pesos,$volante,$parametros,$parametros_grupo,$chave){
	global $contem_base,$validos,$desenho2, $desenho3, $maior_peso_real, $volantes;
	global $proc_cnt, $proc_parm, $seq_proc;
	global $proc_op, $proc_pesos, $proc_selecionados, $proc_peso_min, $proc_ze, $proc_cnt_ok, $proc_grupo, $proc_pulado;
	global $melhor_ok;
//	global $fator_pular, $proc_sair;
// -------------------------------------------------------------------------------------------------
// 1. cria array $jogo() ---- PARECE QUE NÃO ESTÁ MAIS SENDO UTILIZADA AQUI...
// 2. cria array $seq_proc() que será utilizada para gerar as variações de jogos mais indefinidos primeiro;
//    os jogos serão processados fora da ordem informada pela CEF;
//    Após o krsort é necessário refazer os indices do array pois estamos trabalhando com o sequencial
//    do jogo como indice;
// 3. cria array $peso()
//    Este array recebe o peso de cada resultado escolhido e é ordenado pelo conteúdo 
//    mantendo a relação com o indexador e o array $op() é carregado com os jogos na ordem de 
//    maior para menor peso;
//    Neste caso estamos deixando para desdobrar os jogos de maior peso primeiro que é o que buscamos
//    interfere um pouco na aleatoriedade mas é preciso reduzir a quantidade de desdobramentos
//    processados para não gastar muito tempo com resultados muito distantes dos palpites dos 
//    participantes;
// 4. cria array $op()
// 5. Lembrando $opcao['X'] são todas as possibilidades de jogos
	$op=array();
	$datetime_inicio=new DateTime();
	$seq_proc=ordem_jogos($opcao,$pesos,$op);
	if($seq_proc==NULL){
		gr_l("Não foi possível calcular a sequencia dos jogos para o desdobramento, veja o log.\n");
		gr_g("PROBLEMAS NO DESDOBRAMENTO, ENTRE EM CONTATO COM O ADMINISTRADOR.\n");
		return FALSE;
	}
	$datetime_fim=new DateTime();
	$minutos_s=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%i")));
	$segundos_s=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%s")));

// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//	CRIA AS VARIAVEIS GLOBAIS PARA SEREM UTILIZADAS EM proc_cartao()
	$proc_cnt=0;
	$proc_cnt_ok=0;
	$proc_pulado=0;
	$proc_parm=$parametros;
	$proc_grupo=$parametros_grupo;
	$proc_ze = $opcao['Z'];
	$proc_pesos = $pesos;
	$jogo=array();
	$jogo['QT_ZEBRAS']=0;
	$jogo['PESO']=0;
	$proc_selecionados=array();
	$proc_peso_min=0;
	$datetime_inicio=new DateTime();
	$multi_op=gera_multi_opcoes($op,$volante,$qt_multi);
	
	gr_g(sprintf("PROC: %10d ",$qt_multi));
	
	$datetime_fim=new DateTime();
	$minutos_m=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%i")));
	$segundos_m=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%s")));
	$datetime_inicio=new DateTime();
	if(($qt_multi>0)&&($qt_multi<=$proc_parm['LIMITE'])){
		foreach($multi_op as $proc_op){
// PESO_RESTA E PESO_MAX SERVEM PARA VERIFICAR O PESO DISPONIVEL PARA SER UTILIZADO NO JOGO
// CASO O PESO QUE AINDA RESTA SER ADICIONADO NÃO SEJA MAIOR OU IGUAL AO NECESSÁRIO PARA ATINGIR O PESO MINIMO
// O DESDOBRAMENTO NÃO CONTINUA NA function proc_cartao
			$proc_op['PESO_RESTA']=0;
			for($x=1;$x<=14;$x++){
				$proc_op['PESO_MAX'][$x]=0;
				foreach($proc_op[$x] as $y){
					$peso_atu=0;
					foreach($contem_base[$y] as $z){
						$peso_atu+=$pesos['P'][$x][$z];
					}
					if($peso_atu>$proc_op['PESO_MAX'][$x]){
						$proc_op['PESO_MAX'][$x]=$peso_atu;
					}
				}
				$proc_op['PESO_RESTA']+=$proc_op['PESO_MAX'][$x];
			}
		
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//  processa desdobramentos possíveis para este tipo de volante proc_cartao é uma função recursiva e 
//  utilizamos as variáveis globais para controle do processamento e a variável $jogo para a recursão
			proc_cartao($jogo,1);
		}
	}
	$datetime_fim=new DateTime();
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
//	ELIMINA AS VARIÁVEIS QUE FORAM UTILIZADAS EM proc_cartao() e não serão mais utilizadas

// -------------------------------------------------------------------------------------------------
//  Mostra quantos jogos puderam ser desdobrados
	$minutos=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%i")));
	$segundos=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%s")));
//	if(($proc_cnt<$proc_parm['LIMITE'])&&($proc_pulado<$proc_parm['LIMITE']*$fator_pular)){
		gr_g(" " . sprintf("%06d/%09d/%09d",$proc_cnt_ok,$proc_cnt,$proc_pulado) . ' VOLs   M ' . $minutos_m . ":" . $segundos_m . ' D ' . $minutos . ":" . $segundos . ' |' ."\n");
//	}else{
//		gr_g(" " . sprintf("%06d/%09d/%09d",$proc_cnt_ok,$proc_cnt,$proc_pulado) . ' VOLs * M ' . $minutos_m . ":" . $segundos_m . ' D ' . $minutos . ":" . $segundos . ' |' ."\n");
//	}
// -------------------------------------------------------------------------------------------------
	unset($proc_cnt);
	unset($proc_cnt_ok);
	unset($proc_parm);
	unset($proc_op);
	unset($proc_pesos);
	unset($jogo);
	unset($proc_peso_min);
// -------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------
// 1. Caso não tenham sido gerados jogos suficientes devido as restrições do processo e seus
//    parametros é dada a mensagem de que nenhum jogo pode ser escolhido;
// 2. Caso tenham sido gerados jogos suficientes então os cartões tem seus jogos resultados 
//    embaralhados para que seja incluída aleatoriedade na seleção dos resultados para os volantes
// 3. o array $jogos_escolhidos_limpos recebe a lista de jogos selecionados
// 4. Caso o número de jogos seja menor do que o necessário para atingir o custo desejado é apresentada
//    a mensagem de quantidade de jogos abaixo do mínimo
// 5. Caso o número de jogos seja encontrado então são apresentados os resultados selecionados
	if (!isset($proc_selecionados)||count($proc_selecionados)<1){
		$proc_selecionados=array();
		unset($proc_selecionados);
		gr_g("| >>>>>>>>>>>>>>>>>>> NENHUM JOGO PODE SER ESCOLHIDO <<<<<<<<<<<<<<<<<<< M " . sprintf("%06d",count($multi_op)) . "             |\n");
		$jogos_escolhidos_limpos=array();
	}else{
// -------------------------------------------------------------------------------------------------
// 1. os jogos processados tem na sua chave o peso total do volante, por isso o array $proc_selecionados
//    é reordenado pela chave da maior para a menor, dessa forma iniciaremos a seleção dos volantes 
//    pelos jogos mais relevantes em relação aos palpites dos participantes
		krsort($proc_selecionados);
// -------------------------------------------------------------------------------------------------
		$datetime_inicio=new DateTime();
		$jogos_escolhidos_limpos=prepara_jogos($proc_selecionados,$volante['IDEAL']);
		$datetime_fim=new DateTime();
		$proc_selecionados=array();
		unset($proc_selecionados);
		$minutos=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%i")));
		$segundos=sprintf("%02d",intval($datetime_inicio->diff($datetime_fim)->format("%s")));
		if(count($jogos_escolhidos_limpos)<$volante['IDEAL']){
			gr_g("| >>>>>>>>>>>>>>>>>>> QUANTIDADE DE JOGOS ABAIXO DO MINIMO <<<<<<<<<<<<< M " . sprintf("%06d",count($multi_op)) . "    " . " E " . $minutos . ":" . $segundos . " |\n");
		}else{
			$melhor_ok=TRUE;
			inclui_desdobramento($jogos_escolhidos_limpos,$chave);
			gr_g("| S:" . sprintf("%02d",$validos[$volante['I']]['S']) . " D:" . sprintf("%02d",$validos[$volante['I']]['D']) . " T:" . sprintf("%02d",$validos[$volante['I']]['T']) . " QTD: " . sprintf("%03d",$volante['IDEAL']) . "  ");
			gr_g(">>>>>>>>> JOGOS ESCOLHIDOS - INICIO <<<<<<<<< M " . sprintf("%06d",count($multi_op)) . "    " . " E " . $minutos . ":" . $segundos . " |\n");
			gr_g('| \1/ \2/ \3/ \4/ \5/ \6/ \7/ \8/ \9/ \A/ \B/ \C/ \D/ \E/' . "                                     |\n");
			$peso_atual=0;
			$peso_total=0;
			foreach(array(1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14) as $x){
				foreach(array(1 , 2 , 4) as $k){
					$peso_usado[$x][$k]=0;
				}
			}
			foreach($jogos_escolhidos_limpos as $jogo){
				foreach($jogo as $x => $resultado){
					if(($x!='PESO')&&($x!='QT_ZEBRAS')&&($x!='ZEBRAS')){
						foreach($contem_base[$jogo[$x]] as $y){
							if(isset($pesos['P'][$x][$y])&&($peso_usado[$x][$y]==0)){
								$peso_usado[$x][$y]=1;
								$peso_total=$peso_total+$pesos['P'][$x][$y];
							}
						}
					}
				}
				gr_g("|");
				$qt_jogos=1;
				foreach($jogo as $key => $resultado){
					if (($key!="PESO")&&($key!="QT_ZEBRAS")&&($key!="ZEBRAS")){
						if(($resultado==3)||($resultado==5)||($resultado==6)){
							$qt_jogos*=2;
						}else{
							if($resultado==7){
								$qt_jogos*=3;
							}
						}
						gr_g(" ");
						foreach($desenho3[$resultado] as $chave_des => $info_des){
							if(($desenho3[$opcao['F'][$key]][$chave_des]==$info_des)&&($info_des=="X")){
								gr_g("F");
							}else{
								if(($desenho3[$opcao['Z'][$key]][$chave_des]==$info_des)&&($info_des=="X")){
									gr_g("Z");
								}else{
									gr_g($info_des);
								}
							}
						}
					}
				}
				gr_g(" PESO: " . $jogo["PESO"] . " ZEBRAS: " . sprintf("%02d  J:%07d",$jogo["QT_ZEBRAS"],$qt_jogos) . "     |\n");
				$peso_atual=$peso_atual+$jogo["PESO"];
			}
			if ($peso_total > $maior_peso_real){
				$maior_peso_real = $peso_total;
			}
			gr_g("| /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ /|\ --> " . sprintf("%05d",$peso_atual) . " / " . sprintf("%03d",$volante['IDEAL']) . " = " . sprintf("%05d",floor($peso_atual / $volante['IDEAL'])) . "             |\n");
			gr_g("| PESO REAL = " . sprintf("%05d",$peso_total) . " / " . sprintf("%05d",$maior_peso_real));
			gr_g(" " . sprintf("%02d",$validos[$volante['I']]["S"]) . " " . sprintf("%02d",$validos[$volante['I']]["D"]) . " " . sprintf("%02d",$validos[$volante['I']]["T"]) . " ");
			if($volante['MAIOR_PESO']>$peso_total){
				gr_g(sprintf("%05d",$volante['MAIOR_PESO']) . "                                                    |\n");
			}else{
				gr_g(sprintf("%05d",$peso_total) . "                                                    |\n");
			}
			gr_g("|                          >>>>>>>>> JOGOS ESCOLHIDOS - FIM    <<<<<<<<<                      |\n");
// -------------------------------------------------------------------------------------------------
		}
		
   }
	unset($proc_ze);
	unset($proc_grupo);
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	return TRUE;
}

function inclui_desdobramento($desdobramento,$chave){
	global $mysql_link,$proc_query;
	/*
	
		min_zebras tinyint(1) NOT NULL COMMENT 'Quantidade menor de zebras',
		max_zebras tinyint(1) NOT NULL COMMENT 'Quantidade maior de zebras',
		peso smallint(6) NOT NULL COMMENT 'Peso dos jogos',
		min_peso smallint(6) NOT NULL COMMENT 'Menor peso dos jogos',
		max_peso smallint(6) NOT NULL COMMENT 'Maior peso dos jogos',

	*/
	
	$proc_query[]="INSERT INTO wp_loteca_desdobramento " .
	" (rodada, id_grupo, seq_proc, seq_desdobramento, array_jogos, ind_escolhido ) " .
	" VALUES ( '" . 
	$chave['rodada'] . "' , '" .
	$chave['id_grupo'] . "' , '" . 
	$chave['seq_proc'] . "' , '" . 
	$chave['seq_desdobramento'] . "' , \"" . 
	$mysql_link->real_escape_string(serialize($desdobramento)) . "\" , " . 
	"FALSE );";
}

function proc_tentativas($pesos,$palpites,$parametros,$parametros_grupo){
	global $validos, $contem;
	$opcoes=array();
	$s_opcao=0;
	for($ch_f=$pesos['CH_MAIOR'];$ch_f>=$pesos['CH_MENOR_MAIORES'];$ch_f--){
		for($ch_s=$pesos['CH_MENOR'];$ch_s<=$pesos['CH_MAIOR_MENORES'];$ch_s++){
			for($ch_z=$pesos['CH_MENOR'];$ch_z<$ch_s;$ch_z++){
				$opcao_tmp['CHAVES']=array('F' => $ch_f, 'S' => $ch_s, 'Z' => $ch_z);
				$opcao_tmp['S']=array();
				$opcao_tmp['F']=array();
				$opcao_tmp['Z']=array();
				$opcao_tmp['X']=array();
				$invalido=FALSE;
				$qt_zebra=0;
				foreach(array_keys($pesos['P']) as $key){
					$t1=0;
					$e=0;
					$t2=0;
	
//          	    INCLUINDO AS ZEBRAS NA SELECAO - INICIO
					if(($pesos['P'][$key][1]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][1]<=$pesos['L'][$ch_s])){$t1=1;};
					if(($pesos['P'][$key][2]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][2]<=$pesos['L'][$ch_s])){$e=1;};
					if(($pesos['P'][$key][4]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][4]<=$pesos['L'][$ch_s])){$t2=1;};
//          	    INCLUINDO AS ZEBRAS NA SELECAO - FIM
	
					if($pesos['P'][$key][1]>=$pesos['L'][$ch_s]){$t1=1;};
					if($pesos['P'][$key][2]>=$pesos['L'][$ch_s]){$e=1;};
					if($pesos['P'][$key][4]>=$pesos['L'][$ch_s]){$t2=1;};
					$opcao_tmp['S'][$key]=(($t1*1)+($e*2)+($t2*4));
					$t1=0;
					$e=0;
					$t2=0;
					if(($pesos['P'][$key][1]>=$pesos['L'][$ch_f])||($opcao_tmp['S'][$key]==1)){$t1=1;};
					if(($pesos['P'][$key][2]>=$pesos['L'][$ch_f])||($opcao_tmp['S'][$key]==2)){$e=1;};
					if(($pesos['P'][$key][4]>=$pesos['L'][$ch_f])||($opcao_tmp['S'][$key]==4)){$t2=1;};
					$opcao_tmp['F'][$key]=(($t1*1)+($e*2)+($t2*4));
					$t1=0;
					$e=0;
					$t2=0;
					if(($pesos['P'][$key][1]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][1]<=$pesos['L'][$ch_s])){$t1=1;};
					if(($pesos['P'][$key][2]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][2]<=$pesos['L'][$ch_s])){$e=1;};
					if(($pesos['P'][$key][4]>=$pesos['L'][$ch_z])&&($pesos['P'][$key][4]<=$pesos['L'][$ch_s])){$t2=1;};
					$sel=(($t1*1)+($e*2)+($t2*4));
					if(array_search($sel,$contem[$opcao_tmp['S'][$key]])){
						$opcao_tmp['Z'][$key]=$sel;
						$qt_zebra++;
					}else{
						$opcao_tmp['Z'][$key]=0;
					}
					if(($opcao_tmp['Z'][$key]==7)||
					   array_search($opcao_tmp['F'][$key],array(3 , 5 , 6 , 7))){
					   $invalido=TRUE;
					}
				}
				if($qt_zebra<=$parametros_grupo['MIN_ZEBRAS']){
					$invalido=TRUE;
				}
				if(!$invalido){
					$opcoes[++$s_opcao]=$opcao_tmp;
	
					// CALCULAR OS TIPOS DE COMBINAÇÃO VÁLIDOS PARA A RODADA
		
					$opcoes[$s_opcao]['X']=array();
					foreach(array_keys($pesos['P']) as $x){
						$peso_1=sprintf("%04d",$pesos['P'][$x][1]);
						$peso_2=sprintf("%04d",$pesos['P'][$x][2]);
						$peso_3=sprintf("%04d",intval(($pesos['P'][$x][1]+$pesos['P'][$x][2])/2));
//						$peso_3=sprintf("%04d",$pesos['P'][$x][1]+$pesos['P'][$x][2]);
						$peso_4=sprintf("%04d",$pesos['P'][$x][4]);
						$peso_5=sprintf("%04d",intval(($pesos['P'][$x][1]+$pesos['P'][$x][4])/2));
//						$peso_5=sprintf("%04d",$pesos['P'][$x][1]+$pesos['P'][$x][4]);
						$peso_6=sprintf("%04d",intval(($pesos['P'][$x][2]+$pesos['P'][$x][4])/2));
//						$peso_6=sprintf("%04d",$pesos['P'][$x][2]+$pesos['P'][$x][4]);
						$peso_7=sprintf("%04d",intval(($pesos['P'][$x][1]+$pesos['P'][$x][2]+$pesos['P'][$x][4])/3));
//						$peso_7=sprintf("%04d",$pesos['P'][$x][1]+$pesos['P'][$x][2]+$pesos['P'][$x][4]);
						$contem_temp=$contem[$opcoes[$s_opcao]['S'][$x]];
// o shuffle a s	eguir serve somente para deixar aleatorio os casos em que os pesos forem os mesmos, pois cada opção do case recebe um sequencial para não sobrepor os resultados
						shuffle($contem_temp);
						$y=0;
						$opcoes[$s_opcao]['X'][$x]=array();
						foreach($contem_temp as $val){
							$y++;
							switch ($val){
								case 1:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=1)&&($opcoes[$s_opcao]['Z'][$x]!=3)&&($opcoes[$s_opcao]['Z'][$x]!=5)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==1)){
											$peso_1=$peso_1."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_1]=$val;
										}
									}
									break;
								case 2:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=2)&&($opcoes[$s_opcao]['Z'][$x]!=3)&&($opcoes[$s_opcao]['Z'][$x]!=6)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==2)){
											$peso_2=$peso_2."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_2]=$val;
										}
									}
									break;
								case 3:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=3)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==1)||($opcoes[$s_opcao]['F'][$x]==2)||($opcoes[$s_opcao]['F'][$x]==3)){
											$peso_3=$peso_3."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_3]=$val; 
										}
									}
									break;
								case 4:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=4)&&($opcoes[$s_opcao]['Z'][$x]!=5)&&($opcoes[$s_opcao]['Z'][$x]!=6)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==4)){
											$peso_4=$peso_4."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_4]=$val;
										}
									}
									break;
								case 5:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=5)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==1)||($opcoes[$s_opcao]['F'][$x]==4)||($opcoes[$s_opcao]['F'][$x]==5)){
											$peso_5=$peso_5."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_5]=$val;
										}
									}
									break;
								case 6:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=6)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										if(($opcoes[$s_opcao]['F'][$x]==0)||($opcoes[$s_opcao]['F'][$x]==2)||($opcoes[$s_opcao]['F'][$x]==4)||($opcoes[$s_opcao]['F'][$x]==6)){
											$peso_6=$peso_6."$y";
											$opcoes[$s_opcao]['X'][$x][$peso_6]=$val;
										}
									}
									break;
								case 7:
									if(($opcoes[$s_opcao]['Z'][$x]==0)||(($opcoes[$s_opcao]['Z'][$x]!=3)&&($opcoes[$s_opcao]['Z'][$x]!=5)&&($opcoes[$s_opcao]['Z'][$x]!=6)&&($opcoes[$s_opcao]['Z'][$x]!=7))){
										$peso_7=$peso_7."$y";
										$opcoes[$s_opcao]['X'][$x][$peso_7]=$val;
									}
									break;
							}
						}
						krsort($opcoes[$s_opcao]['X'][$x]);
						$y=0;
						$jogo_temp=array();
						foreach($opcoes[$s_opcao]['X'][$x] as $val){
							$y++;
							$jogo_temp[$y]=$val;
						}
						$opcoes[$s_opcao]['X'][$x]=$jogo_temp;
					}
				
					$opcoes[$s_opcao]['TOTAL'] = 1;
					for ($x=1;$x<=14;$x++){ $opcoes[$s_opcao]['TOTAL'] = $opcoes[$s_opcao]['TOTAL'] * count($opcoes[$s_opcao]['X'][$x]); };
				}
			}
		}
	}
	return $opcoes;
}

function display_opcao($opcao,$jogos,$pesos,$parametros,$seq_opcao,$cnt_opcoes){
	global $desenho, $desenho3,$desenho2;
	$contador = array_count_values($opcao['S']);
	for ($x=1;$x<=7;$x++){if(!isset($contador[$x])){$contador[$x]=0;}}
	$tot_simples = $contador[1]+$contador[2]+$contador[4]+0;
	$tot_duplo = $contador[3]+$contador[5]+$contador[6]+0;
	$tot_triplo = $contador[7]+0;
	gr_g("###############################################################################################\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	gr_g("| OPCAO: " . sprintf("%04d",$seq_opcao) . "/" . sprintf("%04d",$cnt_opcoes) . "  JOGO COMPLETO - S: " . sprintf("%02d",$tot_simples) . " D: " . sprintf("%02d",$tot_duplo) . " T: " . sprintf("%02d",$tot_triplo) . "                                         |\n");
	gr_g("| TOTAL DE JOGOS A SEREM DESDOBRADOS: " . sprintf("%11u", $opcao['TOTAL']));
	if($opcao['TOTAL'] > $parametros['LIMITE']){
		gr_g(" # ULTRAPASSOU O LIMITE (" . sprintf("%09d",$parametros['LIMITE']) . ") #       ");
	}else{
		gr_g(" ## ESTE SERA PROCESSADO           ##       ");
	}
	gr_g(" |\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	gr_g("| JOGOS SELECIONADOS, PESOS: DESDOBRAR >= " . sprintf("%03d",$pesos['L'][$opcao['CHAVES']['Z']]). "/" . sprintf("%03d",$pesos['L'][$opcao['CHAVES']['S']]) .", FIXO >= " . sprintf("%03d",$pesos['L'][$opcao['CHAVES']['F']]) . "| SEL | FIX | ZEB |    PESOS    |\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");

	$texto ="| %02d |%20.20s|%-20.20s|%10s|%3.3s| %3s | %3s | %3s | %03d %03d %03d |\n";
	foreach($jogos as $key => $valor){
		gr_g(sprintf($texto , $key , $valor[1] , $valor[2] , $valor['DATA'] , $valor['DIA'] , $desenho2[$opcao['S'][$key]] , $desenho2[$opcao['F'][$key]] , $desenho2[$opcao['Z'][$key]], $pesos['P'][$key][1] , $pesos['P'][$key][2] ,$pesos['P'][$key][4] )); 
	}
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	$totais=totais($opcao['X']);
	gr_g("|         ".'\1/ \2/ \3/ \4/ \5/ \6/ \7/ \8/ \9/ \A/ \B/ \C/ \D/ \E/'."             " .
	     sprintf(" S:%02d D:%02d T:%02d |\n",$totais['S'],$totais['D'],$totais['T']));
	gr_g("| MATRIZ: ");
	$qt_jogos=1;
	$qt_volantes=1;
	foreach($jogos as $key => $valor){
		$qt_volantes*=count($opcao['X'][$key]);
		if(($opcao['S'][$key]==3)||($opcao['S'][$key]==5)||($opcao['S'][$key]==6)){
			$qt_jogos*=2;
		}else{
			if($opcao['S'][$key]==7){
				$qt_jogos*=3;
			}
		}
		foreach($desenho3[$opcao['S'][$key]] as $chave_des => $info_des){
			if(($desenho3[$opcao['F'][$key]][$chave_des]==$info_des)&&($info_des=="X")){
				gr_g("F");
			}else{
				if(($desenho3[$opcao['Z'][$key]][$chave_des]==$info_des)&&($info_des=="X")){
					gr_g("Z");
				}else{
					gr_g($info_des);
				}
			}
		}
		gr_g(" ");
	}
	gr_g(sprintf(" V:%012d  J:%07d",$qt_volantes,$qt_jogos) . "  |\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");

}

function le_desdobramentos($perc,$rodada){
	$desdobramentos=array();
	$sql="
	SELECT 
		A.rodada, A.id_grupo, A.seq_proc, A.array_parm, A.array_palpites, A.array_fixos, A.array_pesos,
		B.seq_desdobramento, B.array_volante, B.seq_opcao, B.array_opcoes, A.opcao_max
	FROM wp_loteca_processamento A, wp_loteca_processamento_desdobramento B
	WHERE
		A.ind_processamento = 0
		AND
		A.rodada = '$rodada'
		AND
		B.ind_perc = '$perc'
		AND
		A.rodada = B.rodada
		AND
		A.id_grupo = B.id_grupo
		AND
		A.seq_proc = B.seq_proc
	ORDER BY A.rodada, B.seq_desdobramento, A.id_grupo, A.seq_proc, B.seq_opcao";
	$result=query($sql);
	if($result==NULL){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			$desdobramentos[]=$row;
		}
		return $desdobramentos;
	}
}

function captura_percentuais_rodada($rodada){
	$desdobramentos=array();
	$sql="
	SELECT DISTINCT
		B.ind_perc
	FROM wp_loteca_processamento A, wp_loteca_processamento_desdobramento B
	WHERE
		A.ind_processamento = 0
		AND
		A.rodada = '$rodada'
		AND
		A.rodada = B.rodada
		AND
		A.id_grupo = B.id_grupo
		AND
		A.seq_proc = B.seq_proc
		AND B.ind_perc > A.ind_perc
	ORDER BY B.ind_perc";
	$result=query($sql);
	if($result==NULL){
		return NULL;
	}else{
		$percs=array();
		while ($row = mysqli_fetch_assoc($result)) {
			$percs[]=$row['ind_perc'];
		}
		return $percs;
	}
}

function proc_desdobramento($desdobramento,$parametros,$display,$jogos){
	global $validos, $melhor_ok;
	$volante=unserialize($desdobramento['array_volante']);
	if(($volante['MELHOR']=='*')||($melhor_ok==false)){
		$opcao=unserialize($desdobramento['array_opcoes']);
		$pesos=unserialize($desdobramento['array_pesos']);
		$parametros_grupo=unserialize($desdobramento['array_parm']);
		$max_opcao=$desdobramento['opcao_max'];
		if($display){
			$seq_opcao=$desdobramento['seq_opcao'];
			display_opcao($opcao,$jogos,$pesos,$parametros,$seq_opcao,$max_opcao);
		}
		gr_g("| S:" . sprintf("%02d",$validos[$volante['I']]['S']) . " D:" . sprintf("%02d",$validos[$volante['I']]['D']) . " T:" . sprintf("%02d",$validos[$volante['I']]['T']) . " QTD: " . sprintf("%03d",$volante['IDEAL']) . " ");
		$chave=array();
		$chave['rodada']=$desdobramento['rodada'];
		$chave['id_grupo']=$desdobramento['id_grupo'];
		$chave['seq_proc']=$desdobramento['seq_proc'];
		$chave['seq_desdobramento']=$desdobramento['seq_desdobramento'];
		if(!proc_desdobra($opcao,$pesos,$volante,$parametros,$parametros_grupo,$chave)){
			gr_l('Não foi possível realizar um desdobramento, verifique o arquivo de log.\n');
			gr_g('Não foi possível realizar um desdobramento, entre em contato com o administrador.\n');
			return FALSE;
		}
	}
	return TRUE;
}

function processa_desdobramentos($perc,$parametros,$rodada,$jogos){
	global $proc_query,$arquivo_grupo, $melhor_ok;
	$proc_query=array();
	$desdobramentos=le_desdobramentos($perc,$rodada);
	if($desdobramentos==NULL){
		grava_err("Não foi possivel recuperar os desdobramentos com percentual " . $perc . " e rodada " . $rodada . "\n");
		return FALSE;
	}
	
	$grupo_ant=0;
	$seq_ant=0;
	$seq_proc=0;
	$grupos=array();
	$seq_proc_ant=0;
	$melhor_ok=FALSE;
	foreach($desdobramentos as $desdobramento){
		if(($grupo_ant!=$desdobramento['id_grupo'])||($seq_ant!=$desdobramento['seq_opcao'])||($seq_proc!=$desdobramento['seq_proc'])){
			$display=TRUE;
			$melhor_ok=FALSE;
		}else{
			$display=FALSE;
		}
		$arquivo_grupo='G' . sprintf('%05d',$desdobramento['id_grupo']) . '_S' . sprintf('%05d',$desdobramento['seq_proc']) . '_D' . $parametros['DATA'] . '.log';
		if(!proc_desdobramento($desdobramento,$parametros,$display,$jogos)){
			gr_l('Não foi possível concluir o processamento dos desdobramentos, verifique o arquivo de log.\n');
			gr_g('Não foi possível concluir o processamento dos desdobramentos, entre em contato com o administrador.\n');
			return FALSE;
		}
		$rodada_ant=$desdobramento['rodada'];
		$grupo_ant=$desdobramento['id_grupo'];
		$seq_ant=$desdobramento['seq_opcao'];
		$seq_proc=$desdobramento['seq_proc'];
		if($desdobramento['seq_proc']!=$seq_proc_ant){
			$grupos[$desdobramento['id_grupo']][]=$desdobramento['seq_proc'];
			$seq_proc_ant=$desdobramento['seq_proc'];
		}
	}
	foreach($grupos as $id_grupo => $seq_procs){
		foreach($seq_procs as $seq_proc){
			if($perc==100){
				$proc_query[]="UPDATE wp_loteca_processamento SET ind_processamento = 1, nm_arquivo_log = '" . $arquivo_grupo . "' WHERE rodada = '$rodada' AND id_grupo = '$id_grupo' AND seq_proc = '$seq_proc';";
			}
			$proc_query[]="UPDATE wp_loteca_processamento SET ind_perc = '$perc' WHERE rodada = '$rodada' AND id_grupo = '$id_grupo' AND seq_proc = '$seq_proc';";
		}
	}
	$result=query($proc_query);
	if(!$result){
		grava_err('Problemas na atualização do processamento no banco de dados' . $perc . " e rodada " . $rodada . "\n");
		return FALSE;
	}
	return TRUE;
}

function processa($data){
	$parametros=captura_parametros();
	if($parametros==NULL){
		gr_l("Problemas na captura dos parametros gerais do sistema\n");
		return FALSE;
	}
	$parametros['DATA']=$data;
	$rodada=captura_proxima_rodada();
	if($rodada==NULL){
		gr_l("Problemas na captura do código da rodada atual\n");
		return FALSE;
	}
	
	$jogos=captura_jogos_rodada($rodada);
	if($jogos==NULL){
		gr_l("Problemas na captura dos jogos da rodada atual\n");
		return FALSE;
	}
	$percs=captura_percentuais_rodada($rodada);
	if($percs==NULL){
		gr_l("Problemas na captura dos percentuais da rodada atual\n");
		return FALSE;
	}
	foreach($percs as $perc){
		$hora = date("Y-m-d H:i:s");
		gr_l("## PROCESSANDO PERCENTUAL " . sprintf('%03d',$perc) . " #### " . $hora . " ########################\n");
		if(!processa_desdobramentos($perc,$parametros,$rodada,$jogos)){
			gr_l('FALHA NO PROCESSAMENTO DO DESDOBRAMENTO COM PERCENTUAL ' . $perc . "\n");
			return FALSE;
		}
	}
	return TRUE;
}

// COMEÇA AQUI -------------------------------------------------
global $arquivo_log;

include_once 'loteca_geral.php';
prepara_ambiente();

// captura dia do processamento

$data=date('Y-m-d');
$arquivo_log='loteca_D' . $data . '.log';

$hora = date("Y-m-d H:i:s");
gr_l("##################################### " . $hora . " #####################################\n");
cria_globals();
config_conexao_mysql();
if(!processa($data)){
	grava_err("Ocorreram problemas no processamento verifique o log " . $arquivo_log . "\n");
}
$hora = date("Y-m-d H:i:s");
gr_l("##################################### " . $hora .  " #####################################\n");

finaliza();
// TERMINA AQUI -------------------------------------------------

?>