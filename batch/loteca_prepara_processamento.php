<?php

// FUNCOES -----------------------------------------------------

function captura_participantes_rodada($rodada,$grupo){ // ok
	$participantes=array();
	$sql="SELECT c.user_email email, a.apelido nm , a.saldo saldo
	      FROM wp_loteca_participante a, wp_loteca_participante_rodada b, wp_users c
		  WHERE rodada = $rodada 
		    AND a.id_grupo = $grupo 
			AND a.id_grupo = b.id_grupo 
			AND a.id_user = b.id_user
			AND a.id_user = c.ID
			AND b.participa = 1
			ORDER BY c.user_email
	";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			$participantes[] = array ( 'EMAIL' => $row['email'] , 'NOME' => utf8_decode($row['nm']), 'SALDO' => $row['saldo']);	
		}
		return $participantes;
	}
}

function captura_grupos_a_processar($rodada){ // ok
	$grupos=array();
	$sql="
	SELECT `id_grupo`,`nm_grupo` FROM `wp_loteca_grupo`
	WHERE `id_ativo` = TRUE 
	  AND id_grupo NOT IN (SELECT distinct id_grupo FROM wp_loteca_processamento WHERE rodada = " . $rodada . " AND ind_processamento = FALSE AND ind_perc <> -1)
	ORDER BY `id_grupo`";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			$grupos[]=array( 'ID' => $row['id_grupo'] , 'NOME' => $row['nm_grupo'] );
		}
		return $grupos;
	}
}

function captura_parametros_grupo($rodada,$grupo){ // ok
	$sql="SELECT vl_max, vl_min, vl_lim_rateio, tip_rateio, ind_bolao_volante, qt_max_zebras, qt_min_zebras, amplia_zebra
          FROM wp_loteca_parametro_rodada
		  WHERE id_grupo = $grupo AND rodada = $rodada";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			return array(
				'MAX' => $row['vl_max'] , 
				'MIN' => $row['vl_min'] ,
				'MED' => (ceil((($row['vl_max']+$row['vl_min'])/2)*100)/100), 
				'MIN_RAT' => $row['vl_lim_rateio'] , 
				'TIPO' => $row['tip_rateio'] , 
				'BOLAO' => $row['ind_bolao_volante'] , 
				'MAX_ZEBRAS' => $row['qt_max_zebras'] , 
				'MIN_ZEBRAS' => $row['qt_min_zebras'] ,
				'amplia_zebra' => $row['amplia_zebra']);
		}
	}
}

function captura_palpites($rodada,$grupo){ // ok
	$palpites=array();
	$sql="SELECT rodada, b.user_email participante, seq, time1, empate, time2
	      FROM wp_loteca_palpite a, wp_users b
		  WHERE rodada = $rodada AND id_grupo = $grupo
		  AND a.id_user = b.ID
		  ORDER BY participante, seq";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		$participante_anterior='';
		while ($row = mysqli_fetch_assoc($result)) {
			if($row['participante']!=$participante_anterior){
				$participante_anterior=$row['participante'];
				$seq_jogo=0;
			}
			if (++$seq_jogo!=$row['seq']){
				gr_g("Problemas na ordem dos palpites!!! " . $seq_jogo . " <> " . $row['seq']);
				return NULL;
			}
			$palpites[]=$row;
		}
		return $palpites;
	}
}

function captura_fixos($rodada,$grupo){ // ok
	$palpites_fixos=array();
	$sql="SELECT rodada, seq, time1, empate, time2
	      FROM wp_loteca_palpite_fixo
		  WHERE rodada = $rodada AND id_grupo = $grupo
		  ORDER BY seq";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			$palpites_fixos[]=$row;
		}
		return $palpites_fixos;
	}
}

function calcula_pesos($palpites,$fixos,$qt_participantes,$parametros_grupo){ // OK
/* array ('MENOR' , 'MAIOR' , 'MENOR_MAIORES', 'MAIOR_MENORES', 'L' => array , 'QT_MAIORES' ,
          'P' => array ( sequencial x resultado) )
*/
	$pesos = array ();
	$pesos['P'] = array ();
	$amplia = array ();
	for($x=1;$x<=14;$x++){
		$pesos['P'][$x] = array( 1 => 0, 2 => 0, 4 => 0);
		$amplia[$x] = array( 1 => 0, 2 => 0, 4 => 0);
	}

	foreach ($palpites as $palpite) {
		if(($palpite['time1']==1)&&($palpite['time2']==0)&&($palpite['empate']==0)){
			$pesos['P'][$palpite['seq']][1] += 6;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][2] += 1;
			}
		}
		if(($palpite['time1']==0)&&($palpite['time2']==1)&&($palpite['empate']==0)){
			$pesos['P'][$palpite['seq']][4] += 6;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][2] += 1;
			}
		}
		if(($palpite['time1']==0)&&($palpite['time2']==0)&&($palpite['empate']==1)){
			$pesos['P'][$palpite['seq']][2] += 6;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][1] += 1;
				$amplia[$palpite['seq']][4] += 1;
			}
		}
		if(($palpite['time1']==1)&&($palpite['time2']==1)&&($palpite['empate']==0)){
			$pesos['P'][$palpite['seq']][1] += 3;
			$pesos['P'][$palpite['seq']][4] += 3;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][2] += 1;
			}
		}
		if(($palpite['time1']==1)&&($palpite['time2']==0)&&($palpite['empate']==1)){
			$pesos['P'][$palpite['seq']][1] += 3;
			$pesos['P'][$palpite['seq']][2] += 3;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][4] += 1;
			}
		}
		if(($palpite['time1']==0)&&($palpite['time2']==1)&&($palpite['empate']==1)){
			$pesos['P'][$palpite['seq']][2] += 3;
			$pesos['P'][$palpite['seq']][4] += 3;
			if($parametros_grupo['amplia_zebra']){
				$amplia[$palpite['seq']][1] += 1;
			}
		}
		if(($palpite['time1']==1)&&($palpite['time2']==1)&&($palpite['empate']==1)){
			$pesos['P'][$palpite['seq']][1] += 2;
			$pesos['P'][$palpite['seq']][2] += 2;
			$pesos['P'][$palpite['seq']][4] += 2;
		}
	}
	foreach($amplia as $key => $amplia_zebras){
		foreach($amplia_zebras as $jogo => $peso_zebra){
			if ($pesos['P'][$key][$jogo]!=0){
				$pesos['P'][$key][$jogo] += $peso_zebra;
			}
		}
	}
	$pesos['MENOR_MAIORES'] = $qt_participantes*8+1; // Menor ENTRE OS Maiores PESOS
	$pesos['MAIOR_MENORES'] = 0; // Maior ENTRE OS Menores PESOS
	$pesos['MENOR'] = $qt_participantes*8+1; // Menor ENTRE OS Maiores PESOS
	$pesos['MAIOR'] = 0; // Maior ENTRE OS Menores PESOS
	$pesos['L'] = array (0 => -1);

	$x=0;
	foreach($pesos['P'] as $key => $peso){
		foreach($peso as $peso_vl){
			if (($peso_vl > 0)&&(array_search($peso_vl , $pesos['L'] ) == NULL)){
				$pesos['L'][++$x]=$peso_vl;
			}
		}
		if(($peso[1]>=$peso[2])&&($peso[1]>=$peso[4])){
			if($peso[1]<$pesos['MENOR_MAIORES']){
				$pesos['MENOR_MAIORES']=$peso[1];
			}
			if($peso[1]>$pesos['MAIOR']){
				$pesos['MAIOR']=$peso[1];
			}
		}
		if(($peso[2]>=$peso[1])&&($peso[2]>=$peso[4])){
			if($peso[2]<$pesos['MENOR_MAIORES']){
				$pesos['MENOR_MAIORES']=$peso[2];
			}
			if($peso[2]>$pesos['MAIOR']){
				$pesos['MAIOR']=$peso[2];
			}
		}
		if(($peso[4]>=$peso[1])&&($peso[4]>=$peso[2])){
			if($peso[4]<$pesos['MENOR_MAIORES']){
				$pesos['MENOR_MAIORES']=$peso[4];
			}
			if($peso[4]>$pesos['MAIOR']){
				$pesos['MAIOR']=$peso[4];
			}
		}
		if(($peso[1]<=$peso[2])&&($peso[1]<=$peso[4])){
			if($peso[1]>$pesos['MAIOR_MENORES']){
				$pesos['MAIOR_MENORES']=$peso[1];
			}
			if(($peso[1]<$pesos['MENOR'])&&($peso[1]>0)){
				$pesos['MENOR']=$peso[1];
			}
		}
		if(($peso[2]<=$peso[1])&&($peso[2]<=$peso[4])){
			if($peso[2]>$pesos['MAIOR_MENORES']){
				$pesos['MAIOR_MENORES']=$peso[2];
			}
			if(($peso[2]<$pesos['MENOR'])&&($peso[2]>0)){
				$pesos['MENOR']=$peso[2];
			}
		}
		if(($peso[4]<=$peso[1])&&($peso[4]<=$peso[2])){
			if($peso[4]>$pesos['MAIOR_MENORES']){
				$pesos['MAIOR_MENORES']=$peso[4];
			}
			if(($peso[4]<$pesos['MENOR'])&&($peso[4]>0)){
				$pesos['MENOR']=$peso[4];
			}
		}
	}
// sort utilizado para gerar a lista de pesos em ordem crescente de valor
	sort($pesos['L']);
    unset($pesos['L'][0]);

	foreach ($fixos as $fixo) {
		if($fixo['time1']==1){
			$pesos['P'][$fixo['seq']][1]=$pesos['MAIOR'];
		}
		if($fixo['empate']==1){
			$pesos['P'][$fixo['seq']][2]=$pesos['MAIOR'];
		}
		if($fixo['time2']==1){
			$pesos['P'][$fixo['seq']][4]=$pesos['MAIOR'];
		}
	}
	$pesos['QT_MAIORES']=0;
	foreach ($pesos['L'] as $key => $vl_peso){
		if ($vl_peso == $pesos['MENOR_MAIORES']){
			$pesos['CH_MENOR_MAIORES'] = $key;
		}
		if ($vl_peso == $pesos['MAIOR_MENORES']){
			$pesos['CH_MAIOR_MENORES'] = $key;
		}
		if ($vl_peso == $pesos['MAIOR']){
			$pesos['CH_MAIOR'] = $key;
		}
		if ($vl_peso == $pesos['MENOR']){
			$pesos['CH_MENOR'] = $key;
		}
		if ($vl_peso >= $pesos['MENOR_MAIORES']){
			$pesos['QT_MAIORES']++;
		}
	}
// AJUSTANDO MENOR_MAIORES
	if($pesos['CH_MENOR_MAIORES']==$pesos['CH_MAIOR_MENORES']){
		$pesos['CH_MENOR_MAIORES']++;
		$pesos['MENOR_MAIORES']=$pesos['L'][$pesos['CH_MENOR_MAIORES']];
	}

	return $pesos;
}

function proc_tentativas($pesos,$palpites,$parametros_grupo,$palpites_fixos){
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
						$eh_fixo=FALSE;
						foreach($palpites_fixos as $fixo){
							if($fixo['seq']==$key){
								$eh_fixo=TRUE;
							}
						}
						if(!$eh_fixo){
							$invalido=TRUE;
						}
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

function calcula_volantes($participantes,$parametros_grupo){
	global $valores;
	$volantes=array();
	$tipo_rateio=$parametros_grupo['TIPO'];
	
	$menor_rateio=999.9;
	$maior_rateio=0.1;
	$menor_rateio2=999.9;
	$maior_rateio2=0.1;
	$qt_menor_rateio=0;
	$qt_maior_rateio=0;
	$qt_participantes=count($participantes);
	for ($x = 1; $x <= 38 ; $x++) {
		if( ( ( ( $valores[$x] / $qt_participantes) >= $parametros_grupo['MIN_RAT']) || ( $parametros_grupo['BOLAO'] == FALSE ) ) &&  // valor do bilhete por participante deve ser maior que o limite do rateio
			( $valores[$x] <= $parametros_grupo['MAX'] * $qt_participantes ) // &&     // o valor do bilhete deve ser menor que o valor total se ser jogado na rodada
			) {
			$max_vol = intval(( $qt_participantes * $parametros_grupo['MAX'] ) / $valores[$x]);
			$min_vol = ceil(( $qt_participantes * $parametros_grupo['MIN'] ) / $valores[$x]);
			if ($tipo_rateio==1){
				$ideal = ceil ( ( $qt_participantes * $parametros_grupo['MED'] ) / $valores[$x] );
				if($ideal>$max_vol){
					$ideal=$max_vol;
				};
			}
			if ($tipo_rateio==2){
				$ideal = floor ( ( $qt_participantes * $parametros_grupo['MED'] ) / $valores[$x] );
				if($ideal<$min_vol){
					$ideal=$min_vol;
				};
			}
			if ($tipo_rateio==3){
				$ideal = $max_vol;
			}
			if ($tipo_rateio==4){
				$ideal = $min_vol;
			}
			if($ideal>$max_vol){
				$ideal=$max_vol;
			}
			if($ideal<$min_vol){
				$ideal=$min_vol;
			}
			$rateio = (ceil( $valores[$x] * $ideal / $qt_participantes * 100 ))/100;
			if ((($tipo_rateio==1)&&($rateio >= $parametros_grupo['MED'])&&($rateio <= $parametros_grupo['MAX']))||
				(($tipo_rateio==2)&&($rateio <= $parametros_grupo['MED'])&&($rateio >= $parametros_grupo['MIN']))||
				(($tipo_rateio==3)&&($rateio >= $parametros_grupo['MED'])&&($rateio <= $parametros_grupo['MAX']))||
				(($tipo_rateio==4)&&($rateio <= $parametros_grupo['MED'])&&($rateio >= $parametros_grupo['MIN'])))
			{
				$rateio_temp=0;
				if($rateio<$menor_rateio){
					$rateio_temp=$menor_rateio;
					$menor_rateio=$rateio;
					$qt_menor_rateio=1;
				}else{
					if($rateio==$menor_rateio){
						$qt_menor_rateio++;
					}else{
						if($rateio<$menor_rateio2){
							$rateio_temp=$rateio;
						}
					}
				}

					if($menor_rateio<$menor_rateio2&&$rateio_temp<>0){
						$menor_rateio2=$rateio_temp;
					}

				$rateio_temp=0;
				if($rateio>$maior_rateio){
					$rateio_temp=$maior_rateio;
					$maior_rateio=$rateio;
					$qt_maior_rateio=1;
				}else{
					if($rateio==$maior_rateio){
						$qt_maior_rateio++;
					}else{
						if($rateio>$maior_rateio2){
							$rateio_temp=$rateio;
						}
					}
				}
				
					if($maior_rateio>$maior_rateio2&&$rateio_temp<>0){
						$maior_rateio2=$rateio_temp;
					}
				if(($tipo_rateio==1)||($tipo_rateio==4)){
					$ordem_volante=$ideal;
				}else{
					$ordem_volante=999-$ideal;
				}
				$index=sprintf('%d%03d%02d',intval($rateio * 100),$ordem_volante,$x);
				$volantes[$index]=array('I' => $x , 'MIN' => $min_vol, 'MAX' => $max_vol, 'IDEAL' => $ideal, 'RATEIO' => $rateio, 'MELHOR' => ' ');
			}
		}
	}
	if(($tipo_rateio==1)||($tipo_rateio==4)){
		ksort($volantes);
	}else{
		krsort($volantes);
	}
	foreach(array_keys($volantes) as $index){
		if(($tipo_rateio==1)||($tipo_rateio==4)){
			if($volantes[$index]['RATEIO']<=$menor_rateio2){
				$volantes[$index]['MELHOR']="*";
			}
		}else{
			if($volantes[$index]['RATEIO']>=$maior_rateio2){
				$volantes[$index]['MELHOR']="*";
			}
		}
	}
	foreach(array_keys($volantes) as $key){
		$volantes[$key]['MAIOR_PESO']=0;
	}
	return $volantes;
}

function display_inicial($rodada,$jogos,$grupo,$parametros_grupo,$participantes,$palpites,$pesos,$seq_proc){ // OK
	gr_g("|---------------------------------------------------------------------------------------|\n");
	gr_g("| RODADA: " . sprintf("%04d",$rodada) . " | GRUPO: " . sprintf("%05d",$grupo['ID']) . " |                                                         |\n");
	gr_g("| PARTICIPANTES: " . sprintf("%02d",count($participantes)) . "      |");
	gr_g(" LIMITE RATEIO: " . sprintf("%7s",number_format($parametros_grupo['MIN_RAT'], 2, ",",".") ). " | ");
	gr_g("OPCAO: ");
	switch($parametros_grupo['TIPO']){
		case 1: // 1 - mínimo valor acima da média; 2 - máximo valor abaixo da média ; 3 - máximo valor ; 4 - mínimo valor;
			gr_g("MENOR ACIMA DA MEDIA        ");
			break;
		case 2:
			gr_g("MAIOR ABAIXO DA MEDIA       ");
			break;
		case 3:
			gr_g("MAXIMO                      ");
			break;
		case 4:
			gr_g("MINIMO                      ");
			break;
	}
	gr_g(" |\n");
	gr_g("| VALOR MAXIMO : " . sprintf("%7s",number_format($parametros_grupo['MAX'], 2, ",",".") ). " | ");
	gr_g("VALOR MINIMO : " . sprintf("%7s",number_format($parametros_grupo['MIN'], 2, ",",".") ). " | ");
	gr_g("VALOR IDEAL  : " . sprintf("%7s",number_format($parametros_grupo['MED'], 2, ",",".") ). "              |\n");
	gr_g(sprintf("| ZEBRAS: MAXIMO - %02d | MINIMO - %02d | AMPLIA: %-8s", $parametros_grupo['MAX_ZEBRAS'], $parametros_grupo['MIN_ZEBRAS'], $parametros_grupo['amplia_zebra']==0?'NAO':'SIM'));
	gr_g("                                  |\n");
	// CAPTURA DOS PALPITES NO BANCO DE DADOS E CARGA DOS PESOS - FIM
	gr_g("|---------------------------------------------------------------------------------------|\n");
	gr_g("| JOGOS CAPTURADOS NO BANCO DE DADOS                      PESOS ----> |  1  |  X  |  2  |\n");
	gr_g("|---------------------------------------------------------------------------------------|\n");
	$texto ="| %02d | %20.20s | %-20.20s | %10s | %3.3s | %03d | %03d | %03d |\n";
	foreach($jogos as $key => $valor){
		gr_g(sprintf($texto , $key , $valor[1] , $valor[2] , $valor['DATA'] , $valor['DIA'] , $pesos['P'][$key][1] , $pesos['P'][$key][2] ,$pesos['P'][$key][4] ));
	}
	gr_g("|---------------------------------------------------------------------------------------|\n");
	
	$lista_chaves_1="";
	$lista_chaves_2="";
	$qt_chave_1=0;
	$qt_chave_2=0;
	$nova_linha_1=0;
	$nova_linha_2=0;
	foreach ($pesos['L'] as $key => $valor){
		if ($pesos['L'][$key] <= $pesos['MAIOR_MENORES']){
			if ($nova_linha_1 == 1){
				$lista_chaves_1=$lista_chaves_1 . "\n|               - ";
				$nova_linha_1=0;
				$qt_chave_1=0;
			}
			$lista_chaves_1=$lista_chaves_1 . sprintf ("%02d > %04d | " ,$key ,$valor);
			if(($qt_chave_1++==5)&&($pesos['L'][$key] < $pesos['MAIOR_MENORES'])){
				$nova_linha_1=1;
			} 
		}
		if ($pesos['L'][$key] >= $pesos['MENOR_MAIORES']){
			if ($nova_linha_2 == 1){
				$lista_chaves_2=$lista_chaves_2 . "\n|               - ";
				$nova_linha_2=0;
				$qt_chave_2=0;
			}
			$lista_chaves_2=$lista_chaves_2 . sprintf ("%02d > %04d | " ,$key ,$valor);
			if(($qt_chave_2++==5)&&($pesos['L'][$key] > $pesos['MENOR_MAIORES'])){
				$nova_linha_2=1;
			} 
		}
	}
	for($qt_chave_1;$qt_chave_1<=5;$qt_chave_1++){
		$lista_chaves_1=$lista_chaves_1 . "          | ";
	}
	for($qt_chave_2;$qt_chave_2<=5;$qt_chave_2++){
		$lista_chaves_2=$lista_chaves_2 . "          | ";
	}
	gr_g("|  PESOS  SEL   - " . $lista_chaves_1 . "\n");
	gr_g("|  PESOS  FIXOS - " . $lista_chaves_2 . "\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	gr_g("| PARTICIPANTE                    | SALDO      | PARTICIPANTE                    | SALDO      |\n");
	gr_g("|----------------------------------------------|----------------------------------------------|\n");
	$texto=" %-31.31s | %10s |";
	$seq_jogo=0;
	$participante_ant='';
	$saldo_geral=0;
	$col=1;
	foreach($participantes as $participante){
		if($col!=2){
			gr_g("|");
			$col=2;
		}else{
			$col=1;
		}
		gr_g(sprintf($texto, $participante['NOME'], "R$ " . number_format($participante['SALDO'], 2, ",",".")));
		if($col==1){
			gr_g("\n");
		}
		$saldo_geral += $participante['SALDO'];
	}
	if($col!=1){
		gr_g("                                              |\n");
	}else{
		gr_g("|---------------------------------------------------------------------------------------------|\n");
	}
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	gr_g("| TOTAL                           | " . sprintf("%10s", "R$ " . number_format($saldo_geral , 2 , "," , ".")) . " |");
	gr_g("                                              |\n");
	gr_g("|---------------------------------------------------------------------------------------------|\n");
	
	gr_g("|---------------------------------------------------------|\n");
	gr_g("| LISTAGEM DOS PALPITES CAPTURADOS                        |\n");
	$texto="| %20.20s | %-20.20s | %1s | %1s | %1s |\n";
	$seq_jogo=0;
	$participante_ant='';
	foreach($palpites as $palpite){
		if($participante_ant!=$palpite['participante']){
			$participante_ant=$palpite['participante'];
			$seq_jogo=0;
			gr_g("|---------------------------------------------|---|---|---|\n");
			gr_g(sprintf("| %-43.43s | 1 | X | 2 |\n",$palpite['participante']));
			gr_g("|---------------------------------------------|---|---|---|\n");
		}
		gr_g(sprintf($texto, $jogos[$palpite['seq']][1], $jogos[$palpite['seq']][2], $palpite['time1']?"X":" ", $palpite['empate']?"X":" ", $palpite['time2']?"X":" "));
	}
	
	gr_g("|---------------------------------------------------------|\n");
	
}

function display_volantes($volantes){
	global $valores, $validos;
	gr_g("#########################################################################################\n");
	gr_g("|---------------------------------------------------------------------------------|\n");
	gr_g("|  TIPO DE VOLANTE   | QUANTIDADE |       GASTO        |           IDEAL          |\n");
	gr_g("|--------------------|------------|--------------------|--------------------------|\n");
	gr_g("| S   D   T   R$     | MAX   MIN  |   MAX       MIN    | QTD     GASTO     RATEIO |\n"); 
	gr_g("| --  --  --  ------ | ----  ---- | --------  -------- | ----  ----------- ------ |\n");
	$texto = "| %02d  %02d  %02d  %6s | %04d  %04d | %8s  %8s | %04d  %10s  %6s%1s|\n";
	foreach($volantes as $volante){
		gr_g(sprintf($texto , $validos[$volante['I']]["S"] , $validos[$volante['I']]["D"] , $validos[$volante['I']]["T"] , 
						number_format ( $valores[$volante['I']] , 2 , "," , "." ) , $volante['MAX'] , $volante['MIN'] ,
						number_format ( $valores[$volante['I']] * $volante['MAX'] , 2 , "," , "." ) , 
						number_format ( $valores[$volante['I']] * $volante['MIN'] , 2 , "," , "." ) ,
						$volante['IDEAL'] , number_format ( $valores[$volante['I']] * $volante['IDEAL'] , 2 , "," , "." ) ,
						number_format ( $volante['RATEIO'], 2 , "," , "." ) , $volante['MELHOR'] ));
	}
	gr_g("|---------------------------------------------------------------------------------|\n");
}

function prx_seq_proc($rodada,$id_grupo){
	$sql="SELECT COALESCE(MAX(seq_proc),0) + 1 AS seq_proc FROM wp_loteca_processamento WHERE rodada = '" . $rodada . "'AND id_grupo = '" . $id_grupo ."';" ;
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			return $row['seq_proc'];
		}
	}
}

function captura_ultimo_processamento($rodada,$id_grupo){
	$sql="
	SELECT array_parm, array_palpites, array_fixos, array_pesos FROM (
	SELECT max(seq_proc) seq_proc, array_parm, array_palpites, array_fixos, array_pesos 
	  FROM wp_loteca_processamento 
	 WHERE rodada = '" . $rodada . "' 
	   AND id_grupo = '" . $id_grupo ."' 
	   GROUP BY array_parm, array_palpites, array_fixos, array_pesos
	   ORDER BY seq_proc DESC
	   LIMIT 1 ) A;";
	$result=query($sql);
	if($result==FALSE){
		return NULL;
	}else{
		while ($row = mysqli_fetch_assoc($result)) {
			return $row;
		}
	}

}

function processa_grupo($grupo,$rodada,$seq_proc,$jogos){
	global $validos;
	global $mysql_link;
	$hora = date("Y-m-d H:i:s");
	gr_l("## LENDO BANCO DE DADOS  " . sprintf("%04d",$grupo['ID']) . " ####### " . $hora . " #####################################\n");

// captura participantes a serem utilizados no processamento
	$participantes=captura_participantes_rodada($rodada, $grupo['ID']); // ok
	if($participantes==NULL){
		gr_l("Problemas na captura dos participantes do grupo na rodada atual");
		gr_g("Problemas na captura dos participantes do grupo na rodada atual");
		return FALSE;
	}

// captura parametros de processamento especificos do grupo
	$parametros_grupo=captura_parametros_grupo($rodada, $grupo['ID']); // ok
	if($parametros_grupo==NULL){
		gr_l("Problemas na captura dos parametros do grupo na rodada atual");
		gr_g("Problemas na captura dos parametros do grupo na rodada atual");
		return FALSE;
	}

// captura os palpites realizados pelos participantes do grupo
	$palpites=captura_palpites($rodada,$grupo['ID']);
	if($palpites==NULL){
		gr_l("Problemas na captura dos palpites do grupo na rodada atual. Rodada " . $rodada . "\n");
		gr_g("Problemas na captura dos palpites do grupo na rodada atual. Rodada " . $rodada . "\n");
		return FALSE;
	}

// captura os resultados que ficam fixos nos volantes por determinação do administrador do grupo
	$palpites_fixos=captura_fixos($rodada,$grupo['ID']);
	if(!is_array($palpites_fixos)){
		gr_l("Problemas na captura dos palpites fixos do grupo na rodada atual. Rodada " . $rodada . "\n");
		gr_g("Problemas na captura dos palpites fixos do grupo na rodada atual. Rodada " . $rodada . "\n");
		return FALSE;
	}

// calcula os pesos a partir dos parametros, dos palpites e dos fixos
	$pesos=calcula_pesos($palpites,$palpites_fixos,count($participantes),$parametros_grupo);
	if($pesos==NULL){
		gr_l("Problemas no calculo dos pesos dos jogos do grupo na rodada atual. Rodada " . $rodada . "\n");
		gr_g("Problemas no calculo dos pesos dos jogos do grupo na rodada atual. Rodada " . $rodada . "\n");
		return FALSE;
	}
	
	$proc_ant=captura_ultimo_processamento($rodada,$grupo['ID']);
	
	if(($proc_ant['array_parm']==serialize($parametros_grupo))&&
	   ($proc_ant['array_palpites']==serialize($palpites))&&
	   ($proc_ant['array_fixos']==serialize($palpites_fixos))&&
	   ($proc_ant['array_pesos']==serialize($pesos))){
       $hora = date("Y-m-d H:i:s");
	   gr_l("## NÃO PRECISA REPROCESSAR  " . sprintf("%04d",$grupo['ID']) . " #### " . $hora . " #####################################\n");
//	   return TRUE;
	}
	
	$query_proc=array();
	$query_proc[]="INSERT INTO wp_loteca_processamento " .
	" (rodada, id_grupo, seq_proc, array_parm, array_palpites, array_fixos, array_pesos, ind_processamento , opcao_max , ind_perc , nm_arquivo_log ) " .
	" VALUES ( '" . 
	$rodada . "' , '" .
	$grupo['ID'] . "' , '" . 
	$seq_proc . "' , \"" . 
	$mysql_link->real_escape_string(serialize($parametros_grupo)) . "\" , \"" . 
	$mysql_link->real_escape_string(serialize($palpites)) . "\" , \"" . 
	$mysql_link->real_escape_string(serialize($palpites_fixos)) . "\" , \"" . 
	$mysql_link->real_escape_string(serialize($pesos)) . "\" , " . 
	"FALSE , 0 , -1 , '' );";

	display_inicial($rodada,$jogos,$grupo,$parametros_grupo,$participantes,$palpites,$pesos,$seq_proc);
	
// calcula as possibilidades de volantes
	$volantes=calcula_volantes($participantes,$parametros_grupo);
	if($volantes==NULL){
		gr_l("Problemas no calculo dos volantes do grupo na rodada atual. Rodada " . $rodada . "\n");
		gr_g("Problemas no calculo dos volantes do grupo na rodada atual. Rodada " . $rodada . "\n");
		return FALSE;
	}

	display_volantes($volantes);
	
// calcula as opções a partir dos palpites (variacoes de zebras, fixos e opcionais)
	$hora = date("Y-m-d H:i:s");
	gr_l("## CALCULANDO OPCOES     " . sprintf("%04d",$grupo['ID']) . " ####### " . $hora . " #####################################\n");
	gr_g("## CALCULANDO OPCOES     " . sprintf("%04d",$grupo['ID']) . " ####### " . $hora . " #####################################\n");

	$opcoes=proc_tentativas($pesos,$palpites,$parametros_grupo,$palpites_fixos);
	
	if($opcoes==NULL){
		gr_l("Problemas no calculo das opções para os jogos do grupo na rodada atual. Rodada " . $rodada . "\n");
		gr_g("Problemas no calculo das opções para os jogos do grupo na rodada atual. Rodada " . $rodada . "\n");
		return FALSE;
	}

	$cnt_opcoes=count($opcoes);
	$seq_opcao=0;
	$seq_desdobramento=0;
	foreach($opcoes as $opcao){
		$array_count_zebras=array_count_values($opcao['Z']);
		if(!isset($array_count_zebras[0])){$array_count_zebras[0]=0;}
		$qt_zebras=14-$array_count_zebras[0];
		$seq_processada=FALSE;
		if($qt_zebras>=$parametros_grupo['MIN_ZEBRAS']){
			foreach($volantes as $key => $volante){
				$seq_volante=$key;
//				if ($volante['MELHOR']=='*'){
					$totais=totais($opcao['X']);
					$tot_simples=$totais['S'];
					$tot_duplo=$totais['D'];
					$tot_triplo=$totais['T'];
					if (($validos[$volante['I']]['T']<=$tot_triplo)&&(($validos[$volante['I']]['D']<=$tot_duplo+($tot_triplo-$validos[$volante['I']]['T'])))){
						if(!$seq_processada){
							$seq_opcao++;
							$fase=floor($seq_opcao/$cnt_opcoes*100);
							$seq_processada=TRUE;
						}
						$seq_desdobramento++;
						$query_proc[]="INSERT INTO `wp_loteca_processamento_desdobramento` " .
						"(rodada, id_grupo, seq_proc, seq_desdobramento, seq_volante, seq_opcao, array_volante, array_opcoes, ind_perc ) " .
						" VALUES ( '" . 
						$rodada . "' , '" .
						$grupo['ID'] . "' , '" . 
						$seq_proc . "' , '" . 
						$seq_desdobramento . "' , '" . 
						$seq_volante . "' , '" .
						$seq_opcao . "' , \"" . 
						$mysql_link->real_escape_string(serialize($volante)) . "\" , \"" . 
						$mysql_link->real_escape_string(serialize($opcao)) . "\" , '" . 
						$fase . "' );";
					}
//				}
			}
		}
	}
	$query_proc[]=
		"UPDATE `wp_loteca_processamento` " .
		"SET opcao_max = '$seq_opcao' " .
		"WHERE rodada='$rodada' " .
		"AND id_grupo='" . $grupo['ID'] ."' " . 
		"AND seq_proc='$seq_proc';"
		;
	$hora = date("Y-m-d H:i:s");
	gr_l("## INICIANDO ATUALIZACAO " . sprintf("%04d",$grupo['ID']) . " ####### " . $hora . " #####################################\n");
	$atualizacao=query($query_proc);
	if($atualizacao==FALSE){
		gr_g("FALHA NA ATUALIZACAO DO BANCO DE DADOS, ENTRE EM CONTATO COM O ADMINISTRADOR\n");
		gr_l("FALHA NA ATUALIZACAO DO BANCO DE DADOS, VEJA MENSAGENS DE ERRO\n");
		return FALSE;
	}
	$hora = date("Y-m-d H:i:s");
	gr_l("## ATUALIZACAO CONCLUIDA " . sprintf("%04d",$grupo['ID']) . " ####### " . $hora . " #####################################\n");
	return TRUE;
}

function processa($data){
	global $arquivo_grupo;
// prepara e conecta banco de dados
	$erro=FALSE;
	if(!conecta()){
		grava_err("Problemas com a conexao ao banco de dados");
		return FALSE;
	};
	
// capturando rodada a ser processada
	$rodada=captura_proxima_rodada(); // ok
	if($rodada==NULL){
		grava_err("Problemas na captura do código da rodada atual");
		return FALSE;
	}
	
// captura jogos a serem processados
	$jogos=captura_jogos_rodada($rodada); // ok 
	if($jogos==NULL){
		grava_err("Problemas na captura dos jogos da rodada atual");
		return FALSE;
	}

// captura grupos que não registraram inicio do processamento
	$grupos=captura_grupos_a_processar($rodada);
	if($grupos==NULL){
		grava_err("Não foi possível identificar grupos para serem processados.\n");
		return FALSE;
	}
	foreach($grupos as $grupo){
		$seq_proc=prx_seq_proc($rodada,$grupo['ID']);
		if($seq_proc==NULL){
			grava_err("ERRO na captura do proximo sequencial de processamento do grupo " . $grupo['ID']);
			$erro=TRUE;
			continue;
		}
		$arquivo_grupo='G' . sprintf('%05d',$grupo['ID']) . '_S' . sprintf('%05d',$seq_proc) . '_D' . $data . '.log';
		if(!processa_grupo($grupo,$rodada,$seq_proc,$jogos)){
			$erro=TRUE;
			gr_l('FALHA NO PROCESSAMENTO DO GRUPO ' . $grupo['ID']. "\n");
		}
	}
	return !$erro;
}

// COMEÇA AQUI -------------------------------------------------

global $arquivo_log;
include_once 'loteca_geral.php';

prepara_ambiente();
cria_globals();

// captura dia do processamento

$data=date('Y-m-d');
$arquivo_log='loteca_D' . $data . '.log';

$hora_inicio = date("Y-m-d H:i:s");
gr_l("## INICIO - PREPARANDO PROCESSAMENTO ## " . $hora_inicio . " ###################################\n");
config_conexao_mysql();
if(!processa($data)){
	gr_l("Ocorreram problemas no processamento verifique o log " . $arquivo_log . "\n");
}
$hora_atual = date("Y-m-d H:i:s");
gr_l("## FIM    - PROCESSAMENTO PREPARADO ### " . $hora_atual .  " ###################################\n");

finaliza();
// TERMINA AQUI -------------------------------------------------

?>