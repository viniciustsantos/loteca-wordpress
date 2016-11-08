<?php 

include_once 'loteca_db_functions.php';

function create_form($method='POST', $style='', $uri='', $target='_self'){
	if($style==NULL||$style==''){
		$style='';
	}else{
		$style="style='" . $style . "'";
	}
	if((!isset($method))||($method==NULL)||(($method!='POST')&&($method!='GET'))){
		$method=='POST';
	}
	return "<form action='" . action_form() . $uri ."' method='".$method."' target='" . $target."'". $style .">";
}

function action_form(){
	return str_replace(array($_SERVER[HTTP_ORIGIN]),array(''),site_url()) .'/';
}

function tab_grupos(){
	$result="";
	$grupos=captura_grupos();
	if($grupos){
		$result.="
		<TABLE>
		<TR><TH COLSPAN=4>GRUPOS</TR><TR><TD COLSPAN=4></TR>
		<TR><TH>ID</TH>
			<TH>Nome do grupo</TH>
			<TH>Administrador</TH>
			<TH>ATIVAR</TH>
		<TR><TD COLSPAN=4></TR></TR>";
		foreach ($grupos as $grupo){
			$result.="
			<TR>
			<TD>&nbsp;" . $grupo->id_grupo .
			"
			</TD>
			<TD>&nbsp;" . $grupo->nm_grupo .
			"
			</TD>
			<TD>&nbsp;" . $grupo->apelido . 
			"
			</TD>
			<TD class='centralizado'>
				" . create_form();
			if($grupo->id_ativo){
				$result.="
				<input name=grupo type=hidden value=" . $grupo->id_grupo . ">
				&nbsp;<input name='desativargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DESATIVAR' />";
			}else{
				$result.="
				<input name=grupo type=hidden value=" . $grupo->id_grupo .">
				&nbsp;<input name='ativargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ATIVAR' />";
			}
			$result.="
				</form>
			</TD>
			</TR>";
		}
		$result.="</TABLE>";
	}
	return $result;
}

function tab_rodadas($limite=10, $inicio=0, $id_grupo=0, $usuario=0){
	$result="";
	$rodadas=captura_rodadas($limite , $inicio, $id_grupo , $usuario);
	if($rodadas){
		$result.="
		<TABLE>
		<TR><TH COLSPAN=";
		if($id_grupo!=0){
			$novarodada=novarodada($id_grupo);
			if(($novarodada)&&($usuario==0)){
				$result.="3 >RODADAS</TH>
				<TH class='semborda' COLSPAN=2>
				" . create_form() . "
				<input name=grupo type=hidden value=" . $id_grupo .">
				<input name=rodada type=hidden value=" . $novarodada .">
				&nbsp;<input name='novarodada' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='HABILITAR PRÓXIMA RODADA(" . $novarodada . ")' />
				</form>
				</TH>";
			}else{
				$result.="5 >RODADAS</TH>";
			}
		}else{
			$novarodada=0;
			$result.="3 >RODADAS</TH>";
		}
		$result.="<TD class='centralizado'>";
		if($inicio!=0){
			$posicao=$inicio - $limite;
			$result.="
			" . create_form() . "
			<input name=grupo type=hidden value='" . $id_grupo ."'>
			<input name=inicio type=hidden value='" . $posicao ."'>";
			if($usuario!=0){
				$result.="<input name=user type=hidden value=" . $usuario .">";
			}
			$result.="
			&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PÁGINA ANTERIOR' />
			</form>";
		}
		$result.="
		</TD>
		<TD class='centralizado'>";
		if(count($rodadas)>=$limite){
			$posicao=$inicio + $limite;
			$result.="
			" . create_form() . "
			<input name=grupo type=hidden value='" . $id_grupo ."'>
			<input name=inicio type=hidden value='" . $posicao ."'>";
			if($usuario!=0){
				$result.="<input name=user type=hidden value=" . $usuario .">";
			}
			$result.="
			&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PRÓXIMA PÁGINA' />
			</form>";
		}
		$result.="
		</TD>
		</TR>
		<TR><TH>Rodada</TH>
			<TH>Início dos palpites</TH>
			<TH>Fim dos palpites</TH>
			<TH>Data do sorteio</TH>
		<TH";
		if($id_grupo!=0){
			$result.=" COLSPAN=4";
		}
		$result.="
		>
		Opções
		</TH>";
		foreach ($rodadas as $rodada){
			$result.="
			<TR>
			<TD>&nbsp;" . $rodada->rodada . "</TD>
			<TD>&nbsp;" . $rodada->dt_inicio_palpite . "</TD>
			<TD>&nbsp;" . $rodada->dt_fim_palpite . "</TD>
			<TD>&nbsp;" . $rodada->dt_sorteio . "</TD>";
			if($id_grupo!=0){
				$result.="<TD>";
				if($rodada->qt_palpites >0){
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<input name=rodada type=hidden value=" . $rodada->rodada .">";
					if($usuario==0){
						$result.="&nbsp;<input name='verpalpites' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />";
					}else{
						$result.="
						<input name=id_user type=hidden value=" . $usuario .">
						<input name=admin type=hidden value=0>
						&nbsp;<input name='detalharpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />";
					}
					$result.="</form>";
				}
				$result.="
				</TD>
				<TD>";
/*
				if($usuario==0){
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<input name=rodada type=hidden value=" . $rodada->rodada .">
					&nbsp;<input name='verdesdobramentos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DESDOBRAMENTOS' />
					</form>";
				}
*/
				$result.="</TD>";
/*
				$result.="<TD>";
				if(($usuario==0)&&($rodada->qt_palpites >0)){
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<input name=rodada type=hidden value=" . $rodada->rodada .">
					&nbsp;<input name='montarjogos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='APOSTAR !' />
					</form>";
				}
				$result.="</TD>";
*/
				$result.="<TD>";
				if($rodada->tem_aposta != 0){
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<input name=rodada type=hidden value=" . $rodada->rodada .">";
					if($usuario!=0){
						$result.="<input name=id_user type=hidden value=" . $usuario .">";
					}
					$result.="
					&nbsp;<input name='ver_aposta' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VER APOSTA' />
					</form>";
				}
				$result.="</TD>";
			}
			$result.="
			<TD>
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">";
			if($usuario!=0){
				$result.="<input name=id_user type=hidden value=" . $usuario .">";
			}
			$result.="
			<input name=rodada type=hidden value=" . $rodada->rodada .">
			&nbsp;<input name='verresultado' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RESULTADO' />
			</form>
			</TD>
			</TR>";
		}
		$result.="</TABLE>";
	}
	return $result;
}

function get_redirect_url($url){
    $redirect_url = null; 
    $url_parts = @parse_url($url);
    if (!$url_parts) return false;
    if (!isset($url_parts['host'])) return false; //can't process relative URLs
    if (!isset($url_parts['path'])) $url_parts['path'] = '/';

    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if (!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
    $request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
    $request .= "Connection: Close\r\n\r\n"; 
    fwrite($sock, $request);
    $response = '';
    while(!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);

    if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
        if ( substr($matches[1], 0, 1) == "/" )
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        else
            return trim($matches[1]);

    } else {
        return false;
    }
}

function verrodadas($id_grupo,$inicio,$usuario){
	return tab_rodadas(10,$inicio,$id_grupo,$usuario);
}

function admingrupo($id_grupo){
	$result="";
//	$resultado_pendente=resultado_pendente();
//	$programacao_pendente=programacao_pendente();
//	if( $resultado_pendente || $programacao_pendente ){
	if( resultado_pendente() || programacao_pendente()){
		include_once 'loteca_capt.php';
		$resultado_captura=loteca_captura_cef();
		$result.="<DIV id='loteca-msg'>";
		switch($resultado_captura){
			case 1:
			{	$result.='Resultados e programação capturados com sucesso. ';
				break; }
			case 2:
			{	$result.='Resultados capturados com sucesso e programação pendente. ';
				break; }
			case 3:
			{	$result.='Resultados capturados com sucesso, falha ao capturar a programação do próximo jogo. ';
				break; }
			case 4:
			{	$result.='Captura de resultados pendentes. ';
				break; }
			case 5:
			{	$result.='Falha ao capturar o resultado do ultimo jogo. ';
				break; }
			default:
			{	$result.='Problemas no tratamento do retorno da captura de resultados e programação na CEF. ';
				break; }
		}
		$result.=' &nbsp; ' . current_time('d-m-Y H:i:s');
		$result.="</DIV>";
	}
	$novarodada=novarodada($id_grupo);
	$result.="<TABLE>";
	$result.=tab_dadosgrupo($id_grupo,1,FALSE);
//	$result.=tab_dadosrodada(0,1,FALSE);
	$result.=tab_dadosgruporodada($id_grupo,1,FALSE);
	if($novarodada){
		$result.="<TR><TD class='vermelho' COLSPAN=3>ESTÁ DISPONÍVEL A PRÓXIMA RODADA - CLIQUE EM RODADAS E EM HABILITAR PRÓXIMA RODADA.</TD></TR>";
	}
	$result.="
	</TABLE>";
	$result.="
	<TABLE>
	<TR><TD>GERAL</TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPANTES' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RODADAS' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='enviarsaldos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='@ SALDO' TITLE='Enviar saldos por email'/>
			</form></TD>";
	$boloes_admin=captura_boloes(1);
	$boloes_usu=captura_boloes(0);
	if((count($boloes_admin))+(count($boloes_usu))>1){
		$result.="
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			<input name='INICIO' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INICIO' />
			</form></TD>";
	}
	global $loteca_pagina_atual;
//	error_log('botao criado com loteca_pagina_atual = "' . $loteca_pagina_atual . '"');
	$result.="
	</TR>
	</TABLE>
	<TABLE>
	<TR class='centralizado'><TD ROWSPAN=2>RODADA ATUAL</TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />
			&nbsp;<input name='alterarparametros' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIGURAR' />
			</form></TD>
	    <TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='incluirgasto' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INCLUIR GASTO' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />
			&nbsp;<input name='incluirpremio' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INCLUIR PRÊMIO' />
			</form></TD>
		<TD></TD>
		<TD>" . create_form() . "
			<input name='outros_grupos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OUTROS GRUPOS' />
			</form></TD>
	</TR>
	<TR class='centralizado'>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='verpalpites' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />
			&nbsp;<input name='montarjogos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='APOSTAR !' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />
			&nbsp;<input name='ver_aposta' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VER APOSTA' />
			</form></TD>
		<TD>" . create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='verresultado' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RESULTADO' />
			</form></TD>
		<TD></TD>
	</TR>
	</TABLE>";
	return $result;
}

function habilitarrodada($id_grupo){
	$result="";
	if(isset($_POST['rodada'])){
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">";
		if(db_habilitarrodada($id_grupo,$_POST['rodada'])){
			$result.="<H3>RODADA " . $_POST['rodada'] . " HABILITADA PARA O SEU GRUPO.</H3>";
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE(2).</H3>";
		}
		$result.="
		<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}else{
		$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE(1).</H3>";
	}
	return $result;
}

function alterarparametros($id_grupo){
	$result="";
	$result.="ID GRUPO RECEBIDO : " . $id_grupo . "<BR>";
	if(isset($_POST['confirma'])){
		$result.=confirma_alterarparametros($id_grupo);
	}else{
		$result.=recebe_alterarparametos($id_grupo);
	}
	return $result;
}

function confirma_alterarparametros($id_grupo){
	$result="";
	if (!atualiza_parametro_rodada($_POST['grupo'],$_POST['rodada'],$_POST['valormaximo'],$_POST['valorminimo'],$_POST['quantogastar'],$_POST['volantecota'],$_POST['valorcota'],$_POST['maxzebras'],$_POST['minzebras'],$_POST['ampliazebra'],$_POST['valorcusto'],$_POST['valorcomissao'],$_POST['topo'],$_POST['esquerda'])){
		$result="NÃO FOI POSSÍVEL ATUALIZAR OS PARAMETROS DA RODADA PARA O SEU GRUPO.";
		return $result;
	}
	$gruporodada=dadosgruporodada($id_grupo,1);
	$result.="
	<TABLE>
	<TR><TD COLSPAN=2>RODADA ATUAL: " . $gruporodada->rodada . "</TD></TR>
	<TR><TD COLSPAN=2>VALOR MÁXIMO: R$ " . $gruporodada->vl_max . "</TD></TR>
	<TR><TD COLSPAN=2>VALOR MÍNIMO: R$" . $gruporodada->vl_min . "</TD></TR>
	<TR><TD COLSPAN=2>QUANTO GASTAR: ";
	switch($gruporodada->tip_rateio){
	case 0:
	{	$result.='Não teremos bolão nessa rodada';
		break; }
	case 1:
	{	$result.='Menor valor acima de média';
		break; }
	case 2:
	{	$result.='Maior valor abaixo da média';
		break; }
	case 3:
	{	$result.='Valor máximo';
		break; }
	case 4:
	{	$result.='Valor mínimo';
		break; }
	}
	$result.="
	</TD></TR>
	<TR><TD COLSPAN=2>VOLANTE COM COTA: ";
	if($gruporodada->ind_bolao_volante){$result.='SIM';}else{$result.='NÃO';}
	$result.="
	</TD></TR>
	<TR><TD COLSPAN=2>VALOR MÍNIMO POR COTA (CEF): R$ " . $gruporodada->vl_lim_rateio . "</TD></TR>
	<TR><TD COLSPAN=2>MÁXIMO DE ZEBRAS POR VOLTANTE: " . $gruporodada->qt_max_zebras . "</TD></TR>
	<TR><TD COLSPAN=2>MÍNIMO DE ZEBRAS POR VOLTANTE: " . $gruporodada->qt_min_zebras . "</TD></TR>
	<TR><TD COLSPAN=2>AJUSTE NO TOPO DA IMPRESSAO VOLANTE: " . $gruporodada->vl_ajuste_topo_volante . "</TD></TR>
	<TR><TD COLSPAN=2>AJUSTE NA ESQUERDA DA IMPRESSAO VOLANTE: " . $gruporodada->vl_ajuste_esqu_volante . "</TD></TR>
	<TR><TD COLSPAN=2>AMPLIA ZEBRAS (VERSÕES FUTURAS): ";
	if($gruporodada->amplia_zebra){$result.='SIM';}else{$result.='NÃO';}
	global $loteca_voltar_para;
	$result.="
	</TD></TR>
	<TR><TD COLSPAN=2>&nbsp;<input name='" . $loteca_voltar_para . "' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' /></TD></TR>
	</TABLE>
	</form>";
	return $result;
}

function recebe_alterarparametos($id_grupo){
	datetimepicker();
	$gruporodada=dadosgruporodada($id_grupo,1);
	$result="";
	$result.="
	" . create_form() . "
	<TABLE>
	<TR><TD COLSPAN=2>RODADA ATUAL: " . $gruporodada->rodada . "</TD></TR>
	<TR><TD COLSPAN=2><input name=confirma type=hidden value=1 />
		<input name=grupo type=hidden value=" . $id_grupo . " />
		<input name=rodada type=hidden value=" . $gruporodada->rodada . " />
		VALOR MÁXIMO:&nbsp;<input name=valormaximo type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $gruporodada->vl_max ."></TD></TR>
	<TR><TD COLSPAN=2>VALOR MÍNIMO:&nbsp;<input name=valorminimo type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $gruporodada->vl_min ."></TD></TR>
	<TR><TD COLSPAN=2>VALOR CUSTO:&nbsp;<input name=valorcusto type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $gruporodada->vl_custo_total ."></TD></TR>
	<TR><TD COLSPAN=2>VALOR COMISSÃO:&nbsp;<input name=valorcomissao type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $gruporodada->vl_comissao ."></TD></TR>
	<TR><TD COLSPAN=2>QUANTO GASTAR:</TD></TR>
	<TR><TD></TD><TD><input name=quantogastar type=radio value=0 ";
	if($gruporodada->tip_rateio==0){$result.='checked ';}
	$result.="
	> Não teremos bolão nessa rodada </input>	</TD></TR>
	<TR><TD></TD><TD><input name=quantogastar type=radio value=1 " ;
	if($gruporodada->tip_rateio==1){$result.='checked ';}
	$result.="
	> Menor valor acima de média </input></TD></TR>
	<TR><TD></TD><TD><input name=quantogastar type=radio value=2 " ;
	if($gruporodada->tip_rateio==2){$result.='checked ';}
	$result.="
	> Maior valor abaixo da média </input></TD></TR>
	<TR><TD></TD><TD><input name=quantogastar type=radio value=3 " ;
	if($gruporodada->tip_rateio==3){$result.='checked ';}
	$result.="
	> Valor máximo </input></TD></TR>
	<TR><TD></TD><TD><input name=quantogastar type=radio value=4 " ;
	if($gruporodada->tip_rateio==4){$result.='checked ';}
	$result.="
	> Valor mínimo </input></TD></TR>
	<TR><TD COLSPAN=2>VOLANTE COM COTA:<input name=volantecota type=checkbox value=1 ";
	if($gruporodada->ind_bolao_volante){$result.='checked ';}
	$result.="
	/></TD></TR>
	<TR><TD COLSPAN=2>
		VALOR MÍNIMO POR COTA (CEF):<input name=valorcota type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $gruporodada->vl_lim_rateio .">
	</TD></TR>
	<TR><TD COLSPAN=2>MÁXIMO DE ZEBRAS POR VOLTANTE:<input name=maxzebras type=number step='1' min=1  value=" . $gruporodada->qt_max_zebras ."></TD></TR>
	<TR><TD COLSPAN=2>MÍNIMO DE ZEBRAS POR VOLTANTE:<input name=minzebras type=number step='1' min=1  value=" . $gruporodada->qt_min_zebras ."></TD></TR>
	<TR><TD COLSPAN=2>AJUSTE NO TOPO DA IMPRESSAO VOLANTE:<input name=topo type=number step='0.01' min=-9.99 max=9.99 value=" . $gruporodada->vl_ajuste_topo_volante ."></TD></TR>
	<TR><TD COLSPAN=2>AJUSTE NA ESQUERDA DA IMPRESSAO DO VOLANTE:<input name=esquerda type=number step='0.01' min=-9.99 max=9.99 value=" . $gruporodada->vl_ajuste_esqu_volante ."></TD></TR>
	<TR><TD COLSPAN=2>AMPLIA ZEBRAS (VERSÕES FUTURAS):<input name=ampliazebra type=checkbox value=1 ";
	if($gruporodada->amplia_zebra){$result.='checked ';}
	global $loteca_pagina_atual;
	global $loteca_voltar_para;
//	error_log('botao criado com loteca_pagina_atual = "' . $loteca_pagina_atual . '"');
	$result.="
	/></TD></TR>
	<TR><TD COLSPAN=2><input name='alterarparametros' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA' />
	<input name='voltarpara' value=" . $loteca_pagina_atual ." type=hidden />
	<input name='" . $loteca_voltar_para . "' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
	</TD></TR></TABLE>
	</form>";
	return $result;
}

function montarjogos($id_grupo,$rodada = 0){
	
	return processa_jogos($id_grupo,$rodada);
	
}
function processa_jogos($id_grupo,$rodada = 0){
	
	$dadosgruporodada=dadosgruporodada($id_grupo, 1, $rodada);
	$cotas_por_faixa=cotas_por_faixa($id_grupo, $rodada);
//	error_log('COTAS POR FAIXA ' . print_r($cotas_por_faixa,true));
//	$jogos_montados=array();
	foreach($cotas_por_faixa as $key=>$cota_faixa){
		if($dadosgruporodada->vl_max>$cota_faixa->valor){
			$jogos_montados=montarjogos_2($id_grupo,$rodada, $limite_ant , $cota_faixa->valor + 0.01, $cota_ant);
		}
		if(isset($jogos_montados['apostas'][1])){
			break;
		}
		$limite_ant=$cota_faixa->valor;
		$cota_ant=$cota_faixa->cotas;
	}
	if(!isset($jogos_montados['apostas'][1])){
		if(count($cotas_por_faixa)!=0){
			$jogos_montados=montarjogos_2($id_grupo,$rodada, $limite_ant,$dadosgruporodada->vl_min, $cota_ant);
		}
	}
//	error_log('JOGOS MONTADOS ' .print_r($jogos_montados,true));
	$result.=$jogos_montados['HTML'];
	if(isset($jogos_montados['apostas'])){
// recupera dados sobre os palpites dos participantes
		$palpite=captura_resultado($dadosgruporodada->rodada,$id_grupo);
		$result.=mostrar_sugestao($palpite, $jogos_montados['apostas'],$dadosgruporodada->rodada);
//		error_log('2dadosgruporodada.' . print_r($dadosgruporodada,true));
		$result.=mostrar_guardar($id_grupo,$dadosgruporodada->rodada, $jogos_montados['apostas'],$dadosgruporodada->qt_cotas_aposta);
	}

	return $result;

}

function montarjogos_2($id_grupo,$rodada = 0, $vl_max,$vl_min, $cotas){
	
	$dadosgruporodada=dadosgruporodada($id_grupo, 1, $rodada);
// calculos dos valores
	$media=floor(($vl_max + $vl_min) / 2 * 100) / 100; // valor medio a ser gasto por participante
	$media_f=number_format($media, 2  , ',' , '.');                                        // valor medio formatado
	$max=$vl_max;                                                        // valor maximo a ser gasto por participante 
	$max_f=number_format($max, 2  , ',' , '.');                                            // valor maximo formatado
	$min=$vl_min;                                                        // valor minimo a ser gasto por participante
	$min_f=number_format($min, 2  , ',' , '.');                                            // valor minimo formatado
	$custo=$dadosgruporodada->vl_custo_total;                                              // valor do custo para realizar as apostas no total
	$custo_f=number_format($custo, 2  , ',' , '.');                                        // valor do custo formatado
	$custoj=ceil($dadosgruporodada->vl_custo_total / $cotas * 100)/100;                     // valor do custo rateado arredondado para cima
	$custoj_f=number_format($custoj, 2  , ',' , '.');                                      // valor do custo rateado formatado
	$custonovo=$custoj * $cotas;                                                            // valor pago pelos participantes para cobrir o custo total
	$custonovo_f=number_format($custonovo, 2  , ',' , '.');                                // valor pago para cobrir o custo formatado
	$comissao=$dadosgruporodada->vl_comissao;                                              // percentual da comissao do administrador do bolão
	$comissao_f=number_format($comissao, 2  , ',' , '.');                                  // percentual da comissao formatada
	$comissao_max = floor ( ( floor( ( ( $max * $cotas ) - $custonovo ) * $comissao ) / 100 ) / $cotas * 100 ) * $cotas / 100 ;   // valor da comissão pelo gasto máximo
	$comissao_med = floor ( ( floor( ( ( $media * $cotas ) - $custonovo ) * $comissao ) / 100 ) / $cotas * 100 ) * $cotas / 100 ; // valor da comissão pelo gasto médio
	$comissao_min = floor ( ( floor( ( ( $min * $cotas ) - $custonovo ) * $comissao ) / 100 ) / $cotas * 100 ) * $cotas / 100 ;   // valor da comissão pelo gasto mínimo
	$comissao_max_j=number_format($comissao_max, 2  , ',' , '.');                          // valor da comissão máxima formatado
	$comissao_med_j=number_format($comissao_med, 2  , ',' , '.');                          // valor da comissão média formatado
	$comissao_min_j=number_format($comissao_min, 2  , ',' , '.');                          // valor da comissão mínima formatado
	$max_aposta = ( ( $max * $cotas) - $custonovo ) - $comissao_max;                        // valor máximo das apostas deduzindo o custo e a comissão
	$med_aposta = ( ( $media * $cotas ) - $custonovo ) - $comissao_med;                     // valor máximo das apostas deduzindo o custo e a comissão
	$min_aposta = ( ( $min * $cotas ) - $custonovo ) - $comissao_min;                       // valor máximo das apostas deduzindo o custo e a comissão
	switch($dadosgruporodada->tip_rateio){
	case 0:                                // SEM APOSTAS
	{	$aposta_min=0;
		$aposta_max=0;
		$asc_desc=' ASC ';
		break; }
	case 1:                                // APOSTA ACIMA E MAIS PRÓXIMO DO VALOR MÍNIMO
	{	$aposta_min=$min_aposta;
		$aposta_max=$med_aposta;
		$asc_desc=' ASC ';
		break; }
	case 2:                                // APOSTA ABAIXO E MAIS PRÓXIMO DO VALOR MÉDIO
	{	$aposta_min=$min_aposta;
		$aposta_max=$med_aposta;
		$asc_desc=' DESC ';
		break; }
	case 3:                                // APOSTA MAIS PRÓXIMO DO VALOR MÁXIMO
	{	$aposta_min=$med_aposta;
		$aposta_max=$max_aposta;
		$asc_desc=' DESC ';
		break; }
	case 4:                                // APOSTA ABAIXO E MAIS PRÓXIMO DO VALOR DO MÍNIMO
	{	$aposta_min=0;
		$aposta_max=$min_aposta;
		$asc_desc=' ASC ';
		break; }
	}
	// tabela html - INFORMAÇÕES CADASTRAIS
	$result="";
	$result.=
	"<TABLE><TR><TH COLSPAN=2>INFORMAÇÕES INICIAIS - RODADA " . $dadosgruporodada->rodada . 
	" - MÍNIMO ". $vl_min . " / MÁXIMO " . $vl_max .
	" </TH></TR>";
	$result.="<TR><TD COLSPAN=2>";
	$result.="VALOR DO CUSTO: R$ " . $custo_f . " - Por jogador: R$ " . $custoj_f . " x " . $cotas . " = Total: R$ " . $custonovo_f . " -  (valor gasto pelo administrador para realizar a jogada)";
	$result.="</TD></TR><TR><TD>";
	$result.="COMISSÃO: " . $comissao_f . " % - (percentual da comissão do administrador)";
	$result.="</TD><TD>";
	$result.="VALOR ESCOLHIDO: ";
	switch($dadosgruporodada->tip_rateio){
	case 0:
	{	$result.='R$ 0,00 (Não haverá aposta)';
		break; }
	case 1:
	{	$result.='acima de R$ ' . $media_f . ' (Menor valor acima de média)';
		break; }
	case 2:
	{	$result.='abaixo de R$ ' . $media_f . ' (Maior valor abaixo da média)';
		break; }
	case 3:
	{	$result.='até R$ ' . $max_f . ' (Valor máximo)';
		break; }
	case 4:
	{	$result.='até R$ ' . $min_f . ' (Valor mínimo)';
		break; }
	}
	$result.="</TD></TR><TR><TD>";
	$result.="VALOR MÉDIO POR JODADOR: R$ " . $media_f;
	$result.="</TD><TD>";
	$result.="VALOR MÉDIO DAS APOSTAS: R$ " . number_format($med_aposta  , 2 , ',' , '.') . " (descontando de R$ " . number_format($media * $cotas , 2 , ',' , '.') . ", o custo de R$ " . $custonovo_f . " e a comissão de R$ " . $comissao_med_j .")";
	$result.="</TD></TR><TR><TD>";
	$result.="VALOR MÁXIMO POR JODADOR: R$ " . $max_f;
	$result.="</TD><TD>";
	$result.="VALOR MÁXIMO DAS APOSTAS: R$ " . number_format($max_aposta  , 2 , ',' , '.') . " (descontando de R$ " . number_format($max * $cotas , 2 , ',' , '.') . ", o custo de R$ " . $custonovo_f . " e a comissão de R$ " . $comissao_max_j . ")";
	$result.="</TD></TR><TR><TD>";
	$result.="VALOR MÍNIMO POR JODADOR: R$ " . $min_f;
	$result.="</TD><TD>";
	$result.="VALOR MÍNIMO DAS APOSTAS: R$ " . number_format($min_aposta  , 2 , ',' , '.') . " (descontando de R$ " . number_format($min * $cotas , 2 , ',' , '.') . ", o custo de R$ " . $custonovo_f . " e a comissão de R$ " . $comissao_min_j .")";
	$result.="</TD></TR><TR><TD>";
	$result.="VOLANTE COM COTAS : ";
	if($dadosgruporodada->ind_bolao_volante){
		$result.="SIM - R$ " . number_format($dadosgruporodada->vl_lim_rateio , 2 , ',' , '.');
		$result.="</TD><TD>";
		$result.="APOSTA MÍNIMA PARA COTAS : " . number_format($dadosgruporodada->vl_lim_rateio * $dadosgruporodada->qt_participantes_ativos , 2 , ',' , '.');
	}else{
		$result.="NÃO";
		$result.="</TD><TD>";
	}
	$result.="</TD></TR></TABLE>";

// Se o tipo de rateio = 0 (Não fazer apostas), para aqui
	if($dadosgruporodada->tip_rateio==0){ return $result ; }

// recupera dados sobre os palpites dos participantes
	$palpite=captura_resultado($dadosgruporodada->rodada,$id_grupo);

// IDENTIFICAR JOGO COM 1 VOLANTE 
// ------------------------------
// 1. ORDENAR OS PALPITES POR (PESO-DESC/QUANTIDADE DE PALPITES-DESC/DIA DO JOGO-ASC)
//    - ASSIM SERÃO LIDOS PRIMEIRO OS MAIORES PESOS, O PRIMEIRO CRITÉRIO DE DESEMPATE É O NÚMERO DE PALPITES E O SEGUNDO É O DIA DO JOGO
// 2. ELIMINAR OS TIPOS DE JOGOS COM VALOR MAIOR QUE O MÁXIMO A SER APOSTADO
// 3. ELIMINAR OS TIPOS DE JOGOS COM VALOR MENOR QUE O MÍNIMO A SER APOSTADO
// 4. SE ESTIVER MARCADO JOGO POR BOLÃO, ELIMINAR OS TIPOS DE JOGOS INCOMPATÍVEIS COM O NÚMERO DE PARTICIPANTES
// 5. ORDENAR OS TIPOS DE JOGOS PELO VALOR TOTAL DA APOSTA (ASCENDENTE PARA TIPO DE RATEIO 1 E 4 E DESCENDENTE PARA TIPO DE RATEIO 2 E 3)
// 6. CALCULAR OS LIMITES DE PALPITES NÃO ZEBRA (A PARTIR DO MÁXIMO "N_ZEB_MAX" E DO MÍNIMO DE ZEBRAS "N_ZEB_MIN")
// 7. AGRUPAR OS PALPITES POR PESO
// 8. SELECIONAR PALPITE POR PALPITE A PARTIR DA LISTA ORDENADA EM 1.:
//    - ATÉ ATINGIR O TIPO DE JOGO ORDENADO EM 5.;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MENOR OU IGUAL QUE "N_ZEB_MIN", SELECIONAR O GRUPO TODO; (NENHUMA ZEBRA)
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MIN" E MENOR QUE "N_ZEB_MAX", SELECIONAR O GRUPO TODO E COMEÇAR A SELECIONAR AS ZEBRAS; (ALGUMAS ZEBRAS)
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR OU IGUAL QUE "N_ZEB_MAX", SELECIONAR ATÉ O LIMITE DE N_ZEB_MAX; (MINIMO DE ZEBRAS)
//    - DEPOIS DE SELECIONAR ATÉ O LIMITE NÃO ZEBRA, DEVE SER PULADO O GRUPO DE PESO ATUAL E O PRÓXIMO (PULAR UM GRUPO DE PESOS) E ESCOLHIDAS AS ZEBRAS ATÉ O LIMITE DO TIPO DE JOGO
// 9. SE NÃO FOR POSSÍVEL MONTAR O TIPO DE JOGO ORDENADO EM 5, SELECIONAR O PRÓXIMO TIPO DE JOGO E REINICIAR O PASSO 8.
//
// IDENTIFICAR JOGO COM 2 VOLANTES
// -------------------------------
//  1. ORDENAR OS PALPITES POR (PESO-DESC/QUANTIDADE DE PALPITES-DESC/DIA DO JOGO-ASC)
//     - ASSIM SERÃO LIDOS PRIMEIRO OS MAIORES PESOS, O PRIMEIRO CRITÉRIO DE DESEMPATE É O NÚMERO DE PALPITES E O SEGUNDO É O DIA DO JOGO
//  2. LISTAR OS TIPOS DE JOGOS
//  3. SE ESTIVER MARCADO JOGO POR BOLÃO, ELIMINAR OS TIPOS DE JOGOS INCOMPATÍVEIS COM O NÚMERO DE PARTICIPANTES
//  4. COMBINAR OS TIPOS DE JOGOS 2 A 2
//  5. ELIMINAR AS COMBINAÇÕES DE TIPOS COM VALOR MAIOR QUE O MÁXIMO A SER APOSTADO
//  6. ELIMINAR AS COMBINAÇÕES DE TIPOS COM VALOR MENOR QUE O MÍNIMO A SER APOSTADO
//  7. ORDENAR AS COBINAÇÕES DE TIPOS PELO VALOR TOTAL DA APOSTA (ASCENDENTE PARA TIPO DE RATEIO 1 E 4 E DESCENDENTE PARA TIPO DE RATEIO 2 E 3)
//  8. CALCULAR OS LIMITES DE PALPITES NÃO ZEBRA (A PARTIR DO MÁXIMO "N_ZEB_MAX" E DO MÍNIMO DE ZEBRAS "N_ZEB_MIN")
//  9. AGRUPAR OS PALPITES POR PESO
// *** JOGO 1 ***
// 10. SELECIONAR PALPITE POR PALPITE A PARTIR DA LISTA ORDENADA EM 1. PARA MONTAR O PRIMEIRO JOGO:
//    - ATÉ ATINGIR O PRIMEIRO TIPO DE JOGO DAS COMBINAÇÕES ORDENADAS EM 7.;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MENOR OU IGUAL QUE "N_ZEB_MIN", SELECIONAR O GRUPO TODO; (NENHUMA ZEBRA)
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MIN" E MENOR QUE "N_ZEB_MAX", SELECIONAR O GRUPO TODO E PULAR O PRÓXIMO PASSO E COMEÇAR A SELECIONAR AS ZEBRAS;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MAX", SELECIONAR ATÉ O LIMITE DE (N_ZEB_MAX + N_ZEB_MAX - N_ZEB_MIN); (ABAIXO DO MÍNIMO)
//    - DEPOIS DE SELECIONAR ATÉ O LIMITE NÃO ZEBRA, DEVE SER PULADO O GRUPO DE PESO ATUAL E O PRÓXIMO (PULAR UM GRUPO DE PESOS) E ESCOLHIDAS AS ZEBRAS ATÉ O LIMITE DO TIPO DE JOGO;
// 11. SE NÃO FOR POSSÍVEL MONTAR O TIPO DE JOGO NO PASSO 10, SELECIONAR A PRÓXIMA COMBINAÇÃO E REINICIAR O PASSO 10.
// 12. SELECIONAR PALPITE POR PALPITE A PARTIR DA LISTA ORDENADA EM 1. PARA MONTAR O SEGUNDO JOGO:
//    - ATÉ ATINGIR O SEGUNDO TIPO DE JOGO DAS COMBINAÇÕES ORDENADAS EM 7.;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MENOR OU IGUAL QUE "N_ZEB_MIN", SELECIONAR O GRUPO TODO; (NENHUMA ZEBRA)
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MIN" E MENOR QUE "N_ZEB_MAX", SELECIONAR O GRUPO TODO E PULAR O PRÓXIMO PASSO E COMEÇAR A SELECIONAR AS ZEBRAS;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MAX", SELECIONAR ATÉ O LIMITE DE (N_ZEB_MAX + N_ZEB_MAX - N_ZEB_MIN); (ABAIXO DO MÍNIMO)
//    - DEPOIS DE SELECIONAR ATÉ O LIMITE NÃO ZEBRA, DEVE SER PULADO O GRUPO DE PESO ATUAL E O PRÓXIMO (PULAR UM GRUPO DE PESOS) E ESCOLHIDAS AS ZEBRAS ATÉ O LIMITE DO TIPO DE JOGO;
// 13. SE NÃO FOR POSSÍVEL MONTAR O TIPO DE JOGO NO PASSO 12, SELECIONAR A PRÓXIMA COMBINAÇÃO E REINICIAR O PASSO 10.
	
// IDENTIFICAR JOGO COM 2 VOLANTES
// -------------------------------
//  1. ORDENAR OS PALPITES POR (PESO-DESC/QUANTIDADE DE PALPITES-DESC/DIA DO JOGO-ASC)
//     - ASSIM SERÃO LIDOS PRIMEIRO OS MAIORES PESOS, O PRIMEIRO CRITÉRIO DE DESEMPATE É O NÚMERO DE PALPITES E O SEGUNDO É O DIA DO JOGO
// ------------- inicio
	$array_palpite=array();
	foreach($palpite as $jogo){
		$data=substr($jogo->data,0,4).substr($jogo->data,5,2).substr($jogo->data,8,2);
		$data_reversa=99999999-$data;
		$chave=sprintf('%03d%03d%010d%03d%02d',$jogo->peso1,$jogo->qttime1,$data_reversa,$jogo->seq,8);
		$array_palpite[$chave] = array( "seq" => $jogo->seq , "coluna" => "1" , "somar" => 1, "peso" => $jogo->peso1 , "qtd" => $jogo->qttime1 , "dt" => $data , "key" => $chave );
		$chave=sprintf('%03d%03d%010d%03d%02d',$jogo->pesoe,$jogo->qtempate,$data_reversa,$jogo->seq,7);
		$array_palpite[$chave] = array( "seq" => $jogo->seq , "coluna" => "2" , "somar" => 2, "peso" => $jogo->pesoe , "qtd" => $jogo->qtempate , "dt" => $data , "key" => $chave );
		$chave=sprintf('%03d%03d%010d%03d%02d',$jogo->peso2,$jogo->qttime2,$data_reversa,$jogo->seq,6);
		$array_palpite[$chave] = array( "seq" => $jogo->seq , "coluna" => "3" , "somar" => 4, "peso" => $jogo->peso2 , "qtd" => $jogo->qttime2 , "dt" => $data , "key" => $chave );
	}
	krsort($array_palpite);
//	error_log("MONTANDO JOGO 003 \$array_palpite ... \n" . print_r($array_palpite, TRUE));
//  ------------ fim 	
//  2. LISTAR OS TIPOS DE JOGOS
//  3. SE ESTIVER MARCADO JOGO POR BOLÃO, ELIMINAR OS TIPOS DE JOGOS INCOMPATÍVEIS COM O NÚMERO DE PARTICIPANTES
// ------------- inicio
	if($dadosgruporodada->ind_bolao_volante){
		$valor_min_volante = ($dadosgruporodada->vl_lim_rateio * $dadosgruporodada->qt_participantes_ativos);
	}else{
		$valor_min_volante = 0;
	}
//	$volantes=loteca_tipos_volante( $valor_min_volante , $max_aposta , $asc_desc , 2 );
	$volantes=loteca_tipos_volante( $valor_min_volante , $max_aposta , $asc_desc , 1 );
//  ------------ fim 	
//  4. COMBINAR OS TIPOS DE JOGOS 1 a 1, 2 A 2 e 3 A 3
//  5. ELIMINAR AS COMBINAÇÕES DE TIPOS COM VALOR MAIOR QUE O MÁXIMO A SER APOSTADO
//  6. ELIMINAR AS COMBINAÇÕES DE TIPOS COM VALOR MENOR QUE O MÍNIMO A SER APOSTADO
//  8. CALCULAR OS LIMITES DE PALPITES NÃO ZEBRA (A PARTIR DO MÁXIMO "N_ZEB_MAX" E DO MÍNIMO DE ZEBRAS "N_ZEB_MIN")
// ------------- inicio
	$volantes_combinados=array();
	foreach($volantes as $key1=>$volante1){
		$vl_comb=$volante1->vl_aposta;
		if(($vl_comb>=$aposta_min)&&($vl_comb<=$aposta_max)){
			$vl_comb_f=sprintf("%05d%02d%02d%02d" , $vl_comb , $volante1->duplos+$volante1->triplos  , 99 , 99);
			$n_zeb_max_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_min_zebras;
			$n_zeb_min_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_max_zebras;
			if($n_zeb_min_1<14){$n_zeb_min_1=14;};
			if($n_zeb_max_1<14){$n_zeb_max_1=14;};
			$volante1_x = (object) array_merge( (array)$volante1, array( 'N_ZEB_MAX' => $n_zeb_max_1 ,'N_ZEB_MIN' =>$n_zeb_min_1 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
			$volantes_combinados[$vl_comb_f]=array( 1 => $volante1_x);
		}
	}
	foreach($volantes as $key1=>$volante1){
		foreach($volantes as $key2=>$volante2){
			if($key1 <= $key2){
				$vl_comb=($volante1->vl_aposta + $volante2->vl_aposta);
				if(($vl_comb>=$aposta_min)&&($vl_comb<=$aposta_max)){
					$vl_comb_f=sprintf("%05d%02d%02d%02d",$vl_comb, $volante1->duplos+$volante1->triplos , $volante2->duplos+$volante2->triplos , 99);
					$n_zeb_max_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_min_zebras;
					$n_zeb_max_2=14 + $volante2->duplos + 2 * $volante2->triplos - $dadosgruporodada->qt_min_zebras;
					$n_zeb_min_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_max_zebras;
					$n_zeb_min_2=14 + $volante2->duplos + 2 * $volante2->triplos - $dadosgruporodada->qt_max_zebras;
					if($n_zeb_min_1<14){$n_zeb_min_1=14;};
					if($n_zeb_max_1<14){$n_zeb_max_1=14;};
					if($n_zeb_min_2<14){$n_zeb_min_2=14;};
					if($n_zeb_max_2<14){$n_zeb_max_2=14;};
					$volante1_x = (object) array_merge( (array)$volante1, array( 'N_ZEB_MAX' => $n_zeb_max_1 ,'N_ZEB_MIN' =>$n_zeb_min_1 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
					$volante2_x = (object) array_merge( (array)$volante2, array( 'N_ZEB_MAX' => $n_zeb_max_2 ,'N_ZEB_MIN' =>$n_zeb_min_2 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
					$volantes_combinados[$vl_comb_f]=array( 1 => $volante1_x , 2 => $volante2_x);
				}
			}
		}
	}
	foreach($volantes as $key1=>$volante1){
		foreach($volantes as $key2=>$volante2){
			foreach($volantes as $key3=>$volante3){
				if(($key1 <= $key2)&&($key2 <= $key3)){
					$vl_comb=($volante1->vl_aposta + $volante2->vl_aposta + $volante3->vl_aposta);
					if(($vl_comb>=$aposta_min)&&($vl_comb<=$aposta_max)){
						$vl_comb_f=sprintf("%05d%02d%02d%02d",$vl_comb, $volante1->duplos+$volante1->triplos , $volante2->duplos+$volante2->triplos , $volante3->duplos+$volante3->triplos);
						$n_zeb_max_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_min_zebras;
						$n_zeb_max_2=14 + $volante2->duplos + 2 * $volante2->triplos - $dadosgruporodada->qt_min_zebras;
						$n_zeb_max_3=14 + $volante3->duplos + 2 * $volante3->triplos - $dadosgruporodada->qt_min_zebras;
						$n_zeb_min_1=14 + $volante1->duplos + 2 * $volante1->triplos - $dadosgruporodada->qt_max_zebras;
						$n_zeb_min_2=14 + $volante2->duplos + 2 * $volante2->triplos - $dadosgruporodada->qt_max_zebras;
						$n_zeb_min_3=14 + $volante3->duplos + 2 * $volante3->triplos - $dadosgruporodada->qt_max_zebras;
						if($n_zeb_min_1<14){$n_zeb_min_1=14;};
						if($n_zeb_max_1<14){$n_zeb_max_1=14;};
						if($n_zeb_min_2<14){$n_zeb_min_2=14;};
						if($n_zeb_max_2<14){$n_zeb_max_2=14;};
						if($n_zeb_min_3<14){$n_zeb_min_3=14;};
						if($n_zeb_max_3<14){$n_zeb_max_3=14;};
						$volante1_x = (object) array_merge( (array)$volante1, array( 'N_ZEB_MAX' => $n_zeb_max_1 ,'N_ZEB_MIN' =>$n_zeb_min_1 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
						$volante2_x = (object) array_merge( (array)$volante2, array( 'N_ZEB_MAX' => $n_zeb_max_2 ,'N_ZEB_MIN' =>$n_zeb_min_2 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
						$volante3_x = (object) array_merge( (array)$volante3, array( 'N_ZEB_MAX' => $n_zeb_max_3 ,'N_ZEB_MIN' =>$n_zeb_min_3 , 'MAX_ZEB' => $dadosgruporodada->qt_max_zebras , 'MIN_ZEB' => $dadosgruporodada->qt_min_zebras) );
						$volantes_combinados[$vl_comb_f]=array( 1 => $volante1_x , 2 => $volante2_x , 3 => $volante3_x);
					}
				}
		}
		}
	}
//  ------------ fim 	
//  7. ORDENAR AS COBINAÇÕES DE TIPOS PELO VALOR TOTAL DA APOSTA (ASCENDENTE PARA TIPO DE RATEIO 1 E 4 E DESCENDENTE PARA TIPO DE RATEIO 2 E 3)
// ------------- inicio
	if($asc_desc == " DESC "){
		krsort($volantes_combinados);
	}else{
		ksort($volantes_combinados);
	}
//	error_log(print_r($volantes_combinados,true));
//	error_log ("volantes : " . print_r($volantes_combinados,true));
//  ------------ fim 	
//  9. AGRUPAR OS PALPITES POR PESO
// ------------- inicio
	$palpite_grupo_peso=array();
	foreach($array_palpite as $key =>$p) {
		if($p['peso']>0){
			$palpite_grupo_peso[$p['peso']][$key]=$p;
		}
	}
//	error_log("MONTANDO JOGO 003 \$palpite_grupo_peso ... \n" . print_r($palpite_grupo_peso, TRUE));
//  ------------ fim 	
// 10. SELECIONAR PALPITE POR PALPITE A PARTIR DA LISTA ORDENADA EM 1. PARA MONTAR O PRIMEIRO JOGO:
//    - ATÉ ATINGIR O PRIMEIRO TIPO DE JOGO DAS COMBINAÇÕES ORDENADAS EM 7.;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MENOR OU IGUAL QUE "N_ZEB_MIN", SELECIONAR O GRUPO TODO; (NENHUMA ZEBRA)
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MIN" E MENOR QUE "N_ZEB_MAX", SELECIONAR O GRUPO TODO E PULAR O PRÓXIMO PASSO E COMEÇAR A SELECIONAR AS ZEBRAS;
//    - SE A QUANTIDADE DO PRÓXIMO GRUPO DE PESO FOR MAIOR QUE "N_ZEB_MAX", SELECIONAR ATÉ O LIMITE DE (N_ZEB_MAX + N_ZEB_MAX - N_ZEB_MIN); (ABAIXO DO MÍNIMO)
//    - DEPOIS DE SELECIONAR ATÉ O LIMITE NÃO ZEBRA, DEVE SER PULADO O GRUPO DE PESO ATUAL E O PRÓXIMO (PULAR UM GRUPO DE PESOS) E ESCOLHIDAS AS ZEBRAS ATÉ O LIMITE DO TIPO DE JOGO;
// 11. SE NÃO FOR POSSÍVEL MONTAR O TIPO DE JOGO NO PASSO 10, SELECIONAR A PRÓXIMA COMBINAÇÃO E REINICIAR O PASSO 10.
// ------------- inicio

	$apostas=monta_apostas_novo($volantes_combinados,$palpite_grupo_peso);
	
//	error_log('combinacoes originais...' . print_r($volantes_combinados, true));
//	error_log('volante gerado...' . print_r($apostas, true));

//  ------------ fim 	

	$valor_apostado=0;
	foreach($apostas as $key => $jogo){
		if($key='TIPOS'){
			if(is_array($jogo)){
				foreach ($jogo as $tipo){
					$valor_apostado+=$tipo->vl_aposta;
				}
			}
		}
	}
	
	$result.="VALOR TOTAL: R$ " . number_format($valor_apostado, 2 , ',' , '.') . "<BR>";
	$result.="VALOR TOTAL + CUSTO: " . number_format($valor_apostado + $custo, 2 , ',' , '.') . "<BR>";
	$result.="VALOR POR JOGADOR: " . number_format(ceil(($valor_apostado + $custo)/$cotas*100)/100, 2 , ',' , '.') . "<BR>";
	$result.="VALOR POR JOGADOR + COMISSÃO: " . number_format(floor(($comissao/100)*(ceil($valor_apostado/$cotas*100)/100)*100)/100+ceil(($valor_apostado + $custo)/$cotas*100)/100, 2 , ',' , '.') . "<BR>";
	$result.="VALOR TOTAL ARRECADADO: " . number_format((floor(($comissao/100)*(ceil($valor_apostado/$cotas*100)/100)*100)/100+ceil(($valor_apostado + $custo)/$cotas*100)/100)*$cotas, 2 , ',' , '.') . "<BR>";

	$resultado['HTML']=$result;
	$resultado['apostas']=$apostas;

	return $resultado;
	
}

function mostrar_guardar($id_grupo,$rodada,$apostas,$qt_cotas_aposta){
	$result='';
	$palpite=captura_resultado($rodada,$id_grupo);
//	error_log('montar guardar.' . print_r($apostas,true));
	$result.=create_form();
	$result.='<input type="hidden" name=grupo value="' . $id_grupo . '">';
	$result.='<input type="hidden" name=rodada value="' . $rodada . '">';
	$result.='<input type="hidden" name=qt_cotas_aposta value="' . $qt_cotas_aposta . '">';
	foreach($apostas as $key => $jogo){
		if(($key!='TIPOS')&&($key!='FALHA')){
			if(isset($jogo['JOGO'])&&is_array($jogo['JOGO'])){
				foreach($jogo['JOGO'] as $seq =>$val){
					$result.='<input type="hidden" name="jogo['.$key.']['.$seq.']" value="'.$val.'" >';
				}
			}
		}
	}
	$result.="<input name='guardarapostas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='GUARDAR APOSTAS' />";
	$result.='</FORM>';
	return $result;
}

function guardarapostas($id_grupo,$rodada,$apostas,$qt_cotas_aposta){
	$result='';
//	error_log('guardar apostas.' . print_r($apostas,true));
	$result.=loteca_guardar_aposta($id_grupo,$rodada,$apostas);
	$result.=create_form( 'POST' , '' , 'impressao_volante.pdf','_blank');
	$result.='<input type="hidden" name=grupo value="' . $id_grupo . '">';
	$result.='<input type="hidden" name=rodada value="' . $rodada . '">';
	$result.='<input type="hidden" name=qt_cotas_aposta value="' . $qt_cotas_aposta . '">';
	foreach($apostas as $key => $seq){
		foreach($seq as $linha => $jogo){
			$result.='<input type="hidden" name=jogo['.$key.']['.$linha.'] value="' . $jogo . '">';
		}
	}
	$result.="<input name='pdf' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='IMPRIMIR VOLANTES(PDF)' />";
	$result.='</FORM>';
	return $result;
}

function mostrar_sugestao($palpite,$apostas,$rodada){
	$result='';
	$cnt_vol=0;
	$cabec=array();
	foreach(array_keys($apostas) as $idx){
		if(($idx!='TIPOS')&&($idx!='FALHA')){
			$cnt_vol++;
			$cabec[$idx]['ini']="VOL " . $idx;
		}else{
			if(($idx=='TIPOS')){
				foreach($apostas[$idx] as $key => $tipo){
					$cabec[$key]['fim']=" " . $tipo->duplos . "D-" . $tipo->triplos . "T R$ " . $tipo->vl_aposta ;
				}
			}
		}
	}
	$result.="<TABLE><TR><TH COLSPAN=7>RODADA: " . $rodada . " </TH>";
	foreach($cabec as $tit){
		$result.="<TH COLSPAN=5>" . $tit['ini'] . $tit['fim'] . "</TH>";
	}
	$result.="<TR><TH>#</TH><TH>TIME 1</TH><TH>P1</TH><TH>PE</TH><TH>P2</TH><TH>TIME 2</TH><TH>HORÁRIO</TH>";
	foreach($cabec as $tit){
		$result.="<TH>#</TH><TH>1</TH><TH>X</TH><TH>2</TH><TH>TIPO</TH>";
	}

	$tot_jogo=array(  0 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  1 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  2 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  3 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  4 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  5 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  6 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  7 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  8 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               ,  9 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               , 10 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               , 11 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               , 12 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               , 13 => array( 1 => 0 , 2 => 0, 4 => 0 )
	               );
	$idx_tot=0;
	foreach(array_keys($apostas) as $idx){
		if(($idx!='TIPOS')&&($idx!='FALHA')){
			$idx_tot++;
			$tot_parcial[$idx_tot]=$tot_jogo;
		}
	}
	
	foreach($palpite as $key => $jogo){
		$qt_apostas=count($apostas);
		$result.="<TR>";
		$result.="<TD>" . $jogo->seq . "</TD>";
		$result.="<TD>" . $jogo->time1 . "</DIV></TD>";
		$result.="<TD class='centralizado'><DIV class='";
		if($jogo->vtime1){
			$result.="fundopreto";
		}
		$result.="'>";
		$result.=$jogo->qttime1 . "/" . $jogo->peso1 . "</DIV></TD>";
		$result.="<TD class='centralizado'><DIV class='";
		if($jogo->empate){
			$result.="fundopreto";
		}
		$result.="'>";
		$result.=$jogo->qtempate . "/" . $jogo->pesoe . "</DIV></TD>";
		$result.="<TD class='centralizado'><DIV class='";
		if($jogo->vtime2){
			$result.="fundopreto";
		}
		$result.="'>";
		$result.=$jogo->qttime2 . "/" . $jogo->peso2 . "</DIV></TD>";
		$result.="<TD>" . $jogo->time2 . "</TD>";
//		$result.="<TD>" . $jogo->data . "/" . $jogo->dia . "</TD>";
		$result.="<TD>" . $jogo->data . "/" . mb_substr(mb_strtoupper($jogo->dia,'UTF-8'),0,3,'UTF-8') . "</TD>";
		$idx_tot=0;
		foreach(array_keys($apostas) as $idx){
			if(($idx!='TIPOS')&&($idx!='FALHA')){
				$result.="<TD>" . $jogo->seq . "</TD>";
				$linha=array(1 => '', 2 => '', 3 => '');
				$idx_tot++;
				if(isset($apostas[$idx]['JOGO'][$jogo->seq])){
					switch($apostas[$idx]['JOGO'][$jogo->seq]){
						case 1:
							$linha[1]='X';
							$linha[2]='&nbsp;';
							$linha[4]='&nbsp;';
//							$linha_tipo="SIMPLES";
							$linha_tipo="&nbsp;";
							$tot_parcial[$idx_tot][$jogo->seq][1]=1;
							$tot_jogo[$jogo->seq][1]=1;
							break;
						case 2:
							$linha[1]='&nbsp;';
							$linha[4]='&nbsp;';
							$linha[2]='X';
//							$linha_tipo="SIMPLES";
							$linha_tipo="&nbsp;";
							$tot_parcial[$idx_tot][$jogo->seq][2]=1;
							$tot_jogo[$jogo->seq][2]=1;
							break;
						case 3:
							$linha[1]='X';
							$linha[2]='X';
							$linha[4]='&nbsp;';
//							$linha_tipo="DUPLO";
							$linha_tipo="DUP";
							$tot_parcial[$idx_tot][$jogo->seq][1]=1;
							$tot_parcial[$idx_tot][$jogo->seq][2]=1;
							$tot_jogo[$jogo->seq][1]=1;
							$tot_jogo[$jogo->seq][2]=1;
							break;
						case 4:
							$linha[1]='&nbsp;';
							$linha[2]='&nbsp;';
							$linha[4]='X';
//							$linha_tipo="SIMPLES";
							$linha_tipo="&nbsp;";
							$tot_parcial[$idx_tot][$jogo->seq][4]=1;
							$tot_jogo[$jogo->seq][4]=1;
							break;
						case 5:
							$linha[1]='X';
							$linha[2]='&nbsp;';
							$linha[4]='X';
//							$linha_tipo="DUPLO";
							$linha_tipo="DUP";
							$tot_parcial[$idx_tot][$jogo->seq][1]=1;
							$tot_parcial[$idx_tot][$jogo->seq][4]=1;
							$tot_jogo[$jogo->seq][1]=1;
							$tot_jogo[$jogo->seq][4]=1;
							break;
						case 6:
							$linha[1]='&nbsp;';
							$linha[2]='X';
							$linha[4]='X';
//							$linha_tipo="DUPLO";
							$linha_tipo="DUP";
							$tot_parcial[$idx_tot][$jogo->seq][2]=1;
							$tot_parcial[$idx_tot][$jogo->seq][4]=1;
							$tot_jogo[$jogo->seq][2]=1;
							$tot_jogo[$jogo->seq][4]=1;
							break;
						case 7:
							$linha[1]='X';
							$linha[2]='X';
							$linha[4]='X';
//							$linha_tipo="TRIPLO";
							$linha_tipo="TRI";
							$tot_parcial[$idx_tot][$jogo->seq][1]=1;
							$tot_parcial[$idx_tot][$jogo->seq][2]=1;
							$tot_parcial[$idx_tot][$jogo->seq][4]=1;
							$tot_jogo[$jogo->seq][1]=1;
							$tot_jogo[$jogo->seq][2]=1;
							$tot_jogo[$jogo->seq][4]=1;
							break;
					}
					switch($apostas[$idx]['ZEBRA'][$jogo->seq]){
						case 1:
							$linha[1]='Z';
							break;
						case 2:
							$linha[2]='Z';
							break;
						case 3:
							$linha[1]='Z';
							$linha[2]='Z';
							break;
						case 4:
							$linha[4]='Z';
							break;
						case 5:
							$linha[1]='Z';
							$linha[4]='Z';
							break;
						case 6:
							$linha[2]='Z';
							$linha[4]='Z';
							break;
						case 7:
							$linha[1]='Z';
							$linha[2]='Z';
							$linha[4]='Z';
							break;
					}
					foreach(array( 1 , 2 , 4) as $key=>$x){
						$result.="<TD class='centralizado'><DIV class='";
						if((($jogo->vtime1)&&($x==1))||(($jogo->empate)&&($x==2))||(($jogo->vtime2)&&($x==4))){
							$result.="fundopreto";
						}
						$result.="'>".$linha[$x] . "</DIV></TD>";
					}
					$result.="<TD>" . $linha_tipo . "</TD>";
				}
			}
		}
		$result.="<TR>";
	}
	$result.="</TABLE>";
	$tot_y=0;
	foreach($tot_parcial as $idx => $tot){
		$tot_x=1;
		foreach($tot as $key => $jogo){
			if(($jogo[1]==1)&&($jogo[2]==1)&&($jogo[4]==1)){
				$tot_x=$tot_x*3;
			}
			if(($jogo[1]==1)&&($jogo[2]==1)&&($jogo[4]==0)){
				$tot_x=$tot_x*2;
			}
			if(($jogo[1]==1)&&($jogo[2]==0)&&($jogo[4]==1)){
				$tot_x=$tot_x*2;
			}
			if(($jogo[1]==0)&&($jogo[2]==1)&&($jogo[4]==1)){
				$tot_x=$tot_x*2;
			}
		}
		$tot_y=$tot_y+$tot_x;
	}
	$result.="TOTAL DE POSSIBILIDADES DE RESULTADO JOGADAS: " . $tot_y . " de ";
	$tot_x=1;
	foreach($tot_jogo as $key => $jogo){
		if(($jogo[1]==1)&&($jogo[2]==1)&&($jogo[4]==1)){
			$tot_x=$tot_x*3;
		}
		if(($jogo[1]==1)&&($jogo[2]==1)&&($jogo[4]==0)){
			$tot_x=$tot_x*2;
		}
		if(($jogo[1]==1)&&($jogo[2]==0)&&($jogo[4]==1)){
			$tot_x=$tot_x*2;
		}
		if(($jogo[1]==0)&&($jogo[2]==1)&&($jogo[4]==1)){
			$tot_x=$tot_x*2;
		}
	}
	$result.=$tot_x . "<BR>\n";
	if(isset($apostas['FALHA'])){
		$result.="TENTAMOS OS SEGUINTES VOLANTES<BR>";
		foreach($apostas['FALHA'] as $tentou){
			$cnt=0;
			$total=0;
			foreach($tentou as $tipo_volante){
				$result.=($cnt++>=1?" ++ ":"") . "(" . $cnt . ") DUPLOS : " . $tipo_volante->duplos . " | TRIPLOS : " . $tipo_volante->triplos . " | R$ : " . $tipo_volante->vl_aposta;
				$total+=$tipo_volante->vl_aposta;
			}
			$result.=" | TOTAL : R$ " . $total . "<BR>";
		}
	}
	return $result;
}

function incluirpremio($id_grupo){
	$result="";
	if(!isset($_POST['valorpremio'])){
		$rodada=rodada_atual_grupo($id_grupo);
		$valor=valorpremio($id_grupo,$rodada);
		global $loteca_voltar_para;
//		error_log('botao criado com loteca_voltar_para = "' . $loteca_voltar_para . '"');
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=rodada type=hidden value=" . $rodada .">
		RODADA ATUAL: " . $rodada . "&nbsp;<input name=valorpremio type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor .">
		&nbsp;<input name='incluirpremio' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR PRÊMIO' />
		&nbsp;<input name='" . $loteca_voltar_para . "' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>
		<BR>Se desejar alterar os premios de rodadas anteriores selecione o botão 'RODADAS'";
	}else{
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo ." />";
		if(isset($_POST['rodada'])){
			if(db_inclui_premio($id_grupo,$_POST['rodada'],$_POST['valorpremio'])){
				$result.="<H3>PRÊMIO INCLUÍDO COM SUCESSO</H3>";
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="
		&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function incluirgasto($id_grupo){
	$result="";
	if(!isset($_POST['valorgasto'])){
		$rodada=rodada_atual_grupo($id_grupo);
		$valor=valorgasto($id_grupo,$rodada);
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=rodada type=hidden value=" . $rodada .">TIPO DE JOGO: ";
		$result.="<input name=tipo_jogo type=radio value=0 CHECKED> LOTECA </INPUT>";
		$result.="<input name=tipo_jogo type=radio value=1> FEDERAL </INPUT>";
		$result.="&nbsp; RODADA ATUAL: 
		&nbsp;<input name=valorgasto type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $valor .">
		&nbsp;<input name='incluirgasto' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR GASTO' />
		&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>
		<BR>Se desejar alterar os gastos de rodadas anteriores selecione o botão 'RODADAS'";
	}else{
		$result.=create_form();
		$result.="<input name=grupo type=hidden value=" . $id_grupo .">";
		if(isset($_POST['rodada'])){
			switch($_POST['tipo_jogo']){
				case 0:
					$texto='LOTECA';
					break;
				case 1:
					$texto='FEDERAL';
					break;
				default:
					$texto='LOTECA(default)';
					break;
			}
			if(db_inclui_gasto($id_grupo,$_POST['rodada'],$_POST['valorgasto'],$_POST['tipo_jogo'],$texto)){
				$result.="<H3>GASTO INCLUÍDO COM SUCESSO</H3>";
				informar_inclusao_gasto();
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="
		&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function informar_inclusao_gasto(){
	
}

function confirmarcredito($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">";
		if(db_confirma_credito($id_grupo,$id_user,$rodada)){
			$result.="<H3>CREDITO CONFIRMADO COM SUCESSO</H3>";
		}else{
			$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
		}
		$result.="
		<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}else{
		$result.=create_form() . "<input name=grupo type=hidden value=" . $id_grupo .">
		<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function incluirresgate($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="<p>" . tx_user($id_user,$id_grupo);
		if(!isset($_POST['valorresgate'])){
			$valor=valorresgate($id_grupo,$rodada,$id_user);
			$result.="
			<script type='text/javascript'>
				function tx_loteca_valorresgate_mudou(){
					if(" . $valor . "!=document.getElementById('tx_loteca_valor_resgate').value) {
						document.getElementById('btn_loteca_registra_resgate').disabled = false;
					}else{
						document.getElementById('btn_loteca_registra_resgate').disabled = true;
					}
				}
			</script>
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=id_user type=hidden value=" . $id_user .">
			<input name=rodada type=hidden value=" . $rodada .">
			<input id='tx_loteca_valor_resgate' name=valorresgate type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' 
				value=" . $valor ." onchange='tx_loteca_valorresgate_mudou()'>
			&nbsp;<input id='btn_loteca_registra_resgate' name='resgate' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='GRAVAR RESGATE' disabled/>
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
		}else{
			$result.="
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">";
			$valor=valorresgate($id_grupo,$rodada,$id_user);
			if(db_inclui_resgate($id_grupo,$id_user,$rodada,$_POST['valorresgate'],'RESGATE')){
				if($valor!=0){
					$result.="<H3>RESGATE ALTERADO COM SUCESSO</H3>";
				}else{
					$result.="<H3>RESGATE INCLUÍDO COM SUCESSO</H3>";
				}
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
			$result.="
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
		}
		$result.="</p>";
	}else{
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function desativarparticipante($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		if(!isset($_POST['confirmadesativarparticipante'])){
			$result.="
			<p>" . tx_user($id_user,$id_grupo) . "</p>
			<H3>AO CONFIRMAR A DESATIVAÇÃO DO PARTICIPANTE, ELE NÃO FARÁ PARTE DO PROCESSAMENTO DESTA RODADA E NÃO SERÁ INCLUÍDO NAS PRÓXIMAS ATÉ QUE SEJA ATIVADO NOVAMENTE</H3>
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=id_user type=hidden value=" . $id_user .">
			<input name=rodada type=hidden value=" . $rodada .">
			<input name=confirmadesativarparticipante type=hidden value=TRUE>
			&nbsp;<input name='desativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA DESATIVAÇÃO'/>
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
		}else{
			if($_POST['confirmadesativarparticipante']==TRUE){
				if(db_desativar_participante($id_grupo,$rodada,$id_user)){
					$result.="
					<p>" . tx_user($id_user,$id_grupo) . "</p>
					<H3>PARTICIPANTE DESATIVADO COM SUCESSO.</H3>
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
					</form>";
				}else{
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
					<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
					</form>";
				}
			}else{
				$result.="
				" . create_form() . "
				<input name=grupo type=hidden value=" . $id_grupo .">
				<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
				<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
				</form>";
			}
		}
	}else{
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
		<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function ativarparticipante($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&isset($_POST['rodada'])){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		if(!isset($_POST['confirmaativarparticipante'])){
			$result.="
			<p>" . tx_user($id_user,$id_grupo) . "</p>
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=id_user type=hidden value=" . $id_user .">
			<input name=rodada type=hidden value=" . $rodada .">
			<input name=confirmaativarparticipante type=hidden value=TRUE>
			&nbsp;<input name='ativarparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA ATIVAÇÃO'/>
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
		}else{
			if($_POST['confirmaativarparticipante']==TRUE){
				if(db_ativar_participante($id_grupo,$rodada,$id_user)){
					$result.="
					<p>" . tx_user($id_user,$id_grupo) . "</p>
					<H3>PARTICIPANTE ATIVADO COM SUCESSO.</H3>
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
					</form>";
				}else{
					$result.="
					" . create_form() . "
					<input name=grupo type=hidden value=" . $id_grupo .">
					<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
					<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
					</form>";
				}
			}else{
				$result.="
				" . create_form() . "
				<input name=grupo type=hidden value=" . $id_grupo .">
				<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
				<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
				</form>";
			}
		}
	}else{
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>
		<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function incluircredito($id_grupo){
	$result="";
	if(isset($_POST['id_user'])&&($_POST['id_user']!='')&&isset($_POST['rodada'])&&($_POST['rodada']!='')){
		$id_user=$_POST['id_user'];
		$rodada=$_POST['rodada'];
		$result.="<p>" . tx_user($id_user,$id_grupo);
		if(!isset($_POST['valorcredito'])){
			$valor=valorcredito($id_grupo,$rodada,$id_user);
			$result.="
			<script type='text/javascript'>
				function tx_loteca_valorcredito_mudou(){
					if(" . $valor . "!=document.getElementById('tx_loteca_valor_credito').value) {";
			if($valor!=0){
				$result.="    document.getElementById('btn_loteca_confirma_credito').disabled = true;";
			}
			$result.="
						document.getElementById('btn_loteca_registra_credito').disabled = false;
					}else{";
			if($valor!=0){
				$result.="    document.getElementById('btn_loteca_confirma_credito').disabled = false;";
			}
			$result.="
						document.getElementById('btn_loteca_registra_credito').disabled = true;
					}
				}
			</script>
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=id_user type=hidden value=" . $id_user .">
			<input name=rodada type=hidden value=" . $rodada .">
			<input id='tx_loteca_valor_credito' name=valorcredito type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' 
			value=" . $valor ." onchange='tx_loteca_valorcredito_mudou()'>";
			if($valor!=0){
				$result.="
				&nbsp;<input id='btn_loteca_registra_credito' name='incluircredito' class='loteca button-primary' type='submit' " . 
				SUBMITDISABLED . " value='ALTERAR CREDITO' disabled/>
				&nbsp;<input id='btn_loteca_confirma_credito' name='confirmarcredito' class='loteca button-primary' type='submit' " . 
				SUBMITDISABLED . " value='CONFIRMAR CREDITO' />";
			}else{
				$result.="&nbsp;<input id='btn_loteca_registra_credito' name='incluircredito' class='loteca button-primary' type='submit' " . 
				SUBMITDISABLED . " value='INCLUIR CREDITO' disabled/>";
			}
			$result.="
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
			
		}else{
			$result.="
			" . create_form() . "
			<input name=grupo type=hidden value=" . $id_grupo .">";
			$valor=valorcredito($id_grupo,$rodada,$id_user);
			if(db_inclui_credito($id_grupo,$id_user,$rodada,$_POST['valorcredito'])){
				if($valor!=0){
					$result.="<H3>CREDITO ALTERADO COM SUCESSO</H3>";
				}else{
					$result.="<H3>CREDITO INCLUÍDO COM SUCESSO</H3>";
				}
			}else{
				$result.="<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3>";
			}
			$result.="
			&nbsp;<input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
			</form>";
		}
		$result.="</p>";
	}else{
		$result.="
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<H3>OCORREU UM ERRO, TENTE NOVAMENTE.</H3><input name='adminparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />
		</form>";
	}
	return $result;
}

function ver_aposta($id_grupo,$rodada = 0){
	$result="";
	if(!$rodada){
		$rodada=rodada_atual_grupo($id_grupo);
	}
	$result.=tab_ver_aposta($id_grupo,$rodada);
	return $result;
}

function tab_ver_aposta($id_grupo,$rodada){
	include_once 'loteca_db_functions.php';
	$apostas=loteca_apostas($id_grupo,$rodada);
	$palpite=captura_resultado($rodada,$id_grupo);
	$dadosgruporodada=dadosgruporodada($id_grupo , 1 , $rodada);
	
	$seq_aposta_ant=0;
	$apostas_2=array();
	$apostas_2['TIPOS']=array();
	foreach($apostas as $aposta){
		if($seq_aposta_ant!=$aposta['seq_aposta']){
			$seq_aposta_atu=$aposta['seq_aposta'];
			$duplos=0;
			$triplos=0;
			$apostas_2[$seq_aposta_atu]=array();
			$apostas_2[$seq_aposta_atu]['JOGO']=array();
			$seq_aposta_ant=$aposta['seq_aposta'];
		}
		$total=$aposta['time1']+$aposta['empate']*2+$aposta['time2']*4;
		$apostas_2[$seq_aposta_atu]['JOGO'][$aposta['seq']]=$total;
		if(($total==3)||($total==5)||($total==6)){
			$duplos++;
		}else{
			if($total==7){
				$triplos++;
			}
		}
		if($aposta['seq']==14){
			$array['duplos']=$duplos;
			$array['triplos']=$triplos;
			$valor=loteca_valor_volante($duplos,$triplos);
			$array['vl_aposta']=$valor;
			$apostas_2['TIPOS'][$seq_aposta_atu] = (object) $array;
		}
	}
//	error_log('$apostas'. print_r($apostas,true));
//	error_log('$apostas_2'. print_r($apostas_2,true));
	$result.=mostrar_sugestao($palpite, $apostas_2, $rodada);

	$result.=create_form( 'POST' , '' , 'impressao_volante.pdf','_blank');
	$result.='<input type="hidden" name=grupo value="' . $id_grupo . '">';
	$result.='<input type="hidden" name=rodada value="' . $rodada . '">';
	$result.='<input type="hidden" name=qt_cotas_aposta value="' . $dadosgruporodada->qt_cotas_aposta . '">';
	foreach($apostas_2 as $key => $jogo){
		if(($key!='TIPOS')&&($key!='FALHA')){
			foreach($jogo['JOGO'] as $seq =>$val){
				$result.='<input type="hidden" name="jogo['.$key.']['.$seq.']" value="'.$val.'" >' . "\r\n";
			}
		}
	}
	$result.="<input name='pdf' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='IMPRIMIR VOLANTES(PDF)' />";
	$result.='</FORM>';
	return $result;
}

function verresultado($id_grupo,$rodada = 0){
	$result="";
	if(!$rodada){
		$rodada=rodada_atual_grupo($id_grupo);
	}
	$result.=tab_admin_resultado($id_grupo,$rodada);
	return $result;
}

function tab_admin_resultado($id_grupo,$rodada){
	$palpite=captura_resultado($rodada,$id_grupo);
	$result.="
	<TABLE class='minimo'>
	<TR><TH class='centralizado' COLSPAN=8>RODADA : " . $rodada . "</TH></TR>
	<TR><TH class='direita'></TH>
	<TH class='direita'>TIME DA CASA</TH>
	<TH>1</TH>
	<TH>X</TH>
	<TH>2</TH>
	<TH class='esquerda'>VISITANTE</TH>
	<TH class='esquerda' colspan=2>DATA</TH>
	</TR>";
	foreach($palpite as $jogada){
		$result.="
		<TR><TD class='direita'>" . $jogada->seq . "</TD>
		<TD class='direita'>" . $jogada->time1 . "</TD>
		<TD class='centralizado'><DIV class='";
		if($jogada->vtime1){
			$result.=" fundopreto";
		}
		$result.="'>";
		if($jogada->qttime1){
			$result.=$jogada->qttime1 . "&nbsp;/&nbsp;" . $jogada->peso1;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="
		</DIV></TD>
		<TD class='centralizado'><DIV class='";
		if($jogada->empate){
			$result.="fundopreto";
		}
		$result.="'>";
		if($jogada->qtempate){
			$result.=$jogada->qtempate . "&nbsp;/&nbsp;" . $jogada->pesoe;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="
		</DIV></TD>
		<TD class='centralizado'><DIV class='";
		if($jogada->vtime2){
			$result.="fundopreto";
		}
		$result.="'>";
		if($jogada->qttime2){
			$result.=$jogada->qttime2 . "&nbsp;/&nbsp;" .$jogada->peso2;
		}else
		{
			$result.="&nbsp;";
		}
		$result.="
		</DIV></TD>
		<TD class='esquerda'>" . $jogada->time2 . "</TD>
		<TD class='direita'>" . $jogada->dia . "</TD>
		<TD class='direita'>" . $jogada->data . "</TD>
		</TR>";
	}
	$result.="</TABLE>";
	return $result;
}

function verpalpites($id_grupo,$rodada = 0){
	$result="";
	if(!$rodada){
		$rodada=rodada_atual_grupo($id_grupo);
	}
	$result.=tab_admin_palpites($id_grupo,$rodada);
	return $result;
}

function tab_admin_palpites($id_grupo,$rodada){
	$result.="
	<TABLE>
	<TR>
	<TH>ID</TH>
	<TH>APELIDO</TH>
	<TH></TH>
	<TH>ACERTOS</TH>
	</TR>";
	$palpites=captura_palpites_rodada($id_grupo,$rodada);
	foreach($palpites as $palpite){
		$qt_acertos=0;
		$palpite2=captura_palpite($id_grupo,$rodada,$palpite->id_user);
		foreach($palpite2 as $jogada){
			if((($jogada->rtime1)&&($jogada->vtime1==$jogada->rtime1))||(($jogada->rtime2)&&($jogada->vtime2==$jogada->rtime2))||(($jogada->rempate)&&($jogada->empate==$jogada->rempate))){
				$qt_acertos++;
			}
		}
		$result.="
		<TR>
		<TD>" . $palpite->id_user . "</TD>
		<TD>" . $palpite->apelido . "</TD>
		<TD>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=id_user type=hidden value=" . $palpite->id_user .">
		<input name=rodada type=hidden value=" . $rodada .">
		<input name=admin type=hidden value=1>
		&nbsp;<input name='detalharpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DETALHAR' />
		</form>
		</TD>
		<TD>" . $qt_acertos ."</TD>
		</TR>";
	}
	$result.="</TABLE>";
	return $result;
}

function detalharpalpite($id_grupo,$rodada,$id_user=0){
	if($id_user==0){
		$id_user=get_current_user_id();
	}
	return tab_detalhepalpite($id_grupo,$rodada,$id_user);;
}

function tab_detalhepalpite($id_grupo,$rodada,$id_user){
	$palpite=captura_palpite($id_grupo,$rodada,$id_user);
	$result.="
	<TABLE class='minimo'>
	<TR>
	<TH COLSPAN=6>RODADA : " . $rodada;
	if(get_current_user_id()!=$id_user){
		$result.=" - APELIDO : " . $palpite[0]->apelido;
	}
	$result.="
	</TH>
	</TR>
	<TR>
	<TH class='direita'></TH>
	<TH class='direita'>TIME DA CASA</TH>
	<TH>1</TH>
	<TH>X</TH>
	<TH>2</TH>
	<TH class='esquerda'>VISITANTE</TH>
	</TR>";
	$qt_acertos=0;
	foreach($palpite as $jogada){
		if((($jogada->rtime1)&&($jogada->vtime1==$jogada->rtime1))||(($jogada->rtime2)&&($jogada->vtime2==$jogada->rtime2))||(($jogada->rempate)&&($jogada->empate==$jogada->rempate))){
			$qt_acertos++;
		}
		$result.="
		<TR>
		<TD class='direita'>" . $jogada->seq . "</TD>
		<TD class='direita'>" . $jogada->time1 . "</TD>
		<TD class='centralizado";
		if($jogada->rtime1){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->vtime1){
			$result.="1";
		}
		$result.="
		</TD>
		<TD class='centralizado";
		if($jogada->rempate){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->empate){
			$result.="X";
		}
		$result.="
		</TD>
		<TD class='centralizado";
		if($jogada->rtime2){
			$result.=" fundovermelho";
		}
		$result.="'>";
		if($jogada->vtime2){
			$result.="2";
		}
		$result.="
		</TD>
		<TD class='esquerda'>" . $jogada->time2 . "</TD>
		</TR>";
	}
	$result.="
	<TR>
	<TH COLSPAN=6>ACERTOS : " . $qt_acertos . "</TH>
	</TR>
	</TABLE>";
	return $result;
}

function testeemail($id_grupo){
	$user="loteca@vinicius.santos.nom.br";
	$senha="v77T05s22";
/*
	// acessando imap sem ssl
	try {
		$mbox = imap_open("{mail.vinicius.santos.nom.br:143}INBOX", $user, $senha);
	}
	catch(Exception $e){
		$mbox=false;
	}
	if($mbox){
		$num=1;
//		error_log("imap " . $num . " . " . print_r($mbox,true));
		$headers = imap_headers($mbox);
//		error_log("imap " . $num . ".a . " . print_r($headers,true));
//		foreach(array_keys($headers) as $key){
//			$header=imap_headerinfo($mbox,$key+1);
//			error_log("imap " . $num . ".b . " . print_r($header,true));
//			$body=imap_body($mbox,$key+1);
//			error_log("imap " . $num . ".c . " . print_r($body,true));
//		}
	
		$mensagens=imap_search($mbox, "UNSEEN");
		if(is_array($mensagens)){
//			error_log("imap " . $num . ".d . " . print_r($mensagens,true));
			foreach($mensagens as $key){
				$header=imap_headerinfo($mbox,$key);
//				error_log("imap " . $num . ".e . " . print_r($header,true));
				$body=imap_body($mbox,$key);
//				error_log("imap " . $num . ".f . " . print_r($body,true));
			}
		}
	}
	// acessando imap com ssl
	try {
		$mbox = imap_open("{br374.hostgator.com.br:993/imap/ssl}INBOX", $user, $senha);
	}
	catch(Exception $e){
		$mbox=false;
	}
	if($mbox){
		$num=2;
//		error_log("imap " . $num . " . " . print_r($mbox,true));
		$headers = imap_headers($mbox);
//		error_log("imap " . $num . ".a . " . print_r($headers,true));
//		foreach(array_keys($headers) as $key){
//			$header=imap_headerinfo($mbox,$key+1);
//			error_log("imap " . $num . ".b . " . print_r($header,true));
//			$body=imap_body($mbox,$key+1);
//			error_log("imap " . $num . ".c . " . print_r($body,true));
//		}

		$mensagens=imap_search($mbox, "UNSEEN");
		if(is_array($mensagens)){
//			error_log("imap " . $num . ".d . " . print_r($mensagens,true));
			foreach($mensagens as $key){
				$header=imap_headerinfo($mbox,$key);
//				error_log("imap " . $num . ".e . " . print_r($header,true));
				$body=imap_body($mbox,$key);
//				error_log("imap " . $num . ".f . " . print_r($body,true));
			}
		}
	}
	// acessando pop3 sem ssl
	try {
		$mbox = imap_open("{mail.vinicius.santos.nom.br:110/pop3}INBOX", $user, $senha);
	}
	catch(Exception $e){
		$mbox=false;
	}
	if($mbox){
		$num=3;
//		error_log("imap " . $num . " . " . print_r($mbox,true));
		$headers = imap_headers($mbox);
//		error_log("imap " . $num . ".a . " . print_r($headers,true));
//		foreach(array_keys($headers) as $key){
//			$header=imap_headerinfo($mbox,$key+1);
//			error_log("imap " . $num . ".b . " . print_r($header,true));
//			$body=imap_body($mbox,$key+1);
//			error_log("imap " . $num . ".c . " . print_r($body,true));
//		}

		$mensagens=imap_search($mbox, "UNSEEN");
		if(is_array($mensagens)){
//			error_log("imap " . $num . ".d . " . print_r($mensagens,true));
			foreach($mensagens as $key){
				$header=imap_headerinfo($mbox,$key);
//				error_log("imap " . $num . ".e . " . print_r($header,true));
				$body=imap_body($mbox,$key);
//				error_log("imap " . $num . ".f . " . print_r($body,true));
			}
		}
	}
*/
	// acessando pop3 com ssl
	$erro=array();
	try {
//		$mbox = imap_open("{br374.hostgator.com.br:995/pop3/ssl}INBOX", $user, $senha);
		$mbox = imap_open("{br374.hostgator.com.br:995/pop3/ssl}", $user, $senha);
		$erro[] = imap_last_error();
	}
	catch(Exception $e){
		$mbox=false;
	}
	if($mbox){
		$num=4;
//		error_log("imap " . $num . " . " . print_r($mbox,true));
		$list = imap_getmailboxes($mbox, "{br374.hostgator.com.br}", "*");
//		error_log("imap " . $num . ".x . " . print_r($list,true));
		$headers = imap_headers($mbox);
		$erro[] = imap_last_error();
//		error_log("imap " . $num . ".a . " . print_r($headers,true));
//		foreach(array_keys($headers) as $key){
//			$header=imap_headerinfo($mbox,$key+1);
//			$erro[] = imap_last_error();
//			error_log("imap " . $num . ".b . " . print_r($header,true));
//			$body=imap_body($mbox,$key+1);
//			$erro[] = imap_last_error();
//			error_log("imap " . $num . ".c . " . print_r($body,true));
//		}

		$mensagens=imap_search($mbox, "UNSEEN");
		$erro[] = imap_last_error();
		if(is_array($mensagens)){
//			error_log("imap " . $num . ".d . " . print_r($mensagens,true));
			foreach($mensagens as $key){
				$header=imap_headerinfo($mbox,$key);
				$erro[] = imap_last_error();
//				error_log("imap " . $num . ".e . " . print_r($header,true));
				$body=imap_body($mbox,$key);
				$erro[] = imap_last_error();
//				error_log("imap " . $num . ".f . " . print_r($body,true));
//				imap_delete ( $mbox , $key );
				imap_mail_move($mbox, $key, 'Trash');
				$erro[] = imap_last_error();
				imap_expunge ( $mbox );
				$erro[] = imap_last_error();
			}
		}
	}
//	foreach ($erro as $err){error_log("--> " . $err);}
	imap_close($mbox);
}

function enviarsaldos ($id_grupo){
	$result='';
	$participantes=captura_participantes($id_grupo);
	$headers[] = 'From: ' . $participantes[0]->nm_grupo . ' <' . $participantes[0]->email_grupo . '>';
	add_filter( 'wp_mail_content_type', create_function('', 'return "text/html"; ') );
	foreach($participantes as $participante){
		if($participante->id_ativo==1){
			$result.="Enviando email para " . $participante->apelido . " " . $participante->email . " " . $participante->vl_saldo ;
			$to=$participante->email;
			$subject=$participante->nm_grupo . " - Info";
			switch ($participante->tip_rateio){
				case 0: { $valor=0; break; }
				case 1: { $valor=($participante->vl_min + $participante->vl_max)/2; break; }
				case 2: { $valor=($participante->vl_min + $participante->vl_max)/2; break; }
				case 3: { $valor=$participante->vl_max ; break; }
				case 4: { $valor=$participante->vl_min; break; }
			}
			if($participante->vl_saldo<0){
				$subject.=" - SALDO NEGATIVO";
			}else{
				if($participante->vl_saldo<$valor){
					$subject.=" - TALVEZ PRECISE DEPOSITAR";
				}
			}
			if(($participante->tip_rateio)!=0){
				$msg_palpite="\nO registro de palpites estará disponível até " . $participante->dt_fim_palpite . "\n";
			}else{
				$msg_palpite="";
			};
			switch ($participante->tip_rateio){
				case 0:
				{
					$vl_medio=($vl_min + $vl_max)/2;
					$msg_tipo="<H2>Até o momento não vamos apostar nesta rodada.</H2>";
					break;
				}
				case 1:
				{
					$vl_medio=($vl_min + $vl_max)/2;
					$msg_tipo="A programação é de utilizarmos no mínimo <H2>R$ " . $participante->vl_medio . "</H2> por participante.";
					break;
				}
				case 2:
				{
					$vl_medio=($vl_min + $vl_max)/2;
					$msg_tipo="A programação é de utilizarmos no máximo <H2>R$ " . $participante->vl_medio . "</H2> por participante.";
					break;
				}
				case 3:
				{
					$msg_tipo="A programação é de utilizarmos até <H2>R$ " . $participante->vl_max . "</H2> por participante.";
					break;
				}
				case 4:
				{
					$msg_tipo="A programação é de utilizarmos pouco mais de <H2>R$ " . $participante->vl_min . "</H2> por participante.";
					break;
				}
			}
			$message="
<HTML>
<HEAD> <style type='text/css'>h2 { display: inline; } h1 { display: inline; }</style></HEAD>
<BODY>
Olá,<BR>\n
<BR>\n
Segue a informação do saldo de sua participação no bolão <H1>" . $participante->nm_grupo . "</H1>:<BR>\n
<BR>\n
Seu saldo atual: <H2>R$ " . $participante->vl_saldo . "</H2><BR>\n
<BR>\n
Próxima rodada: " . $participante->rodada .".<BR>\n
<BR>\n" . $msg_tipo . "
<BR>\n" . $msg_palpite . "<BR>\n
O saldo total do grupo é </H2>R$ " . $participante->saldo_grupo . "</H2>.<BR>\n
Em caso de dúvidas acesso o link <A HREF='" . home_url() . "'>" . home_url() ."</A> e confira o extrato ou entre em contato com o administrador do grupo.<BR>\n
Para participar do bolão mantenha o saldo compatível com o gasto previsto para as rodadas!<BR>\n
Se necessário, efetue depósito/transferência para conta no Banco do Brasil (001), agência 1263-7, C/C 9540738-3. (Para DOC entre em contato por telefone para passar o número do CPF). Envie o comprovante por email ou pelo whatsapp.<BR>\n
\n
</BODY></HTML>";
			if(wp_mail( $to, $subject, $message , $headers )){
				$result.="  Email enviado com sucesso.";
			}else{
				$result.="  Falha no envio do email.";
			};
			$result.="<BR>";
		}
	}
	remove_filter( 'wp_mail_content_type', create_function('', 'return "text/html"; '));
	return $result;
}

function wpdocs_set_html_mail_content_type() {
    return 'text/html';
}

function adminparticipantes($id_grupo){
	return tab_admin_participantes($id_grupo);
}

function tab_admin_participantes($id_grupo){
	$participantes=captura_participantes($id_grupo);
	$situacao=99;
	$result.="
	<TABLE>
	<TR>
	<TH>ID</TH>
	<TH>APELIDO (COTAS)</TH>
	<TH>SALDO ANT</TH>
	<TH>GASTO</TH>
	<TH>CRÉDITO</TH>
	<TH>PRÊMIO</TH>
	<TH>RESGATE</TH>
	<TH>SALDO</TH>
	<TH>FEDERAL</TH>
	<TH COLSPAN=3>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='incluirparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='NOVO PARTICIPANTE' />
	</form>
	</TH>
	</TR>";
	foreach($participantes as $participante){
		if($situacao!=$participante->id_ativo){
			$situacao=$participante->id_ativo;
			$result.="<TR><TH COLSPAN=9>";
			if($situacao==1){
				$result.="ATIVOS";
			}else{
				$result.="INATIVOS";
			}
			$result.="
			</TH>
			<TH COLSPAN=4>OPÇÕES</TH></TR>";
		}
		$result.="
		<TR>
		<TD>" . $participante->id_user . "</TD>
		<TD>" . $participante->apelido . " (".$participante->qt_cotas ."/".$participante->qt_cotas_rodada .")";
		$result.=($participante->participa==1)?"":"**";
		$result.="
		</TD>
		<TD";
		$result.=($participante->vl_saldo_ant<0)?" class='vermelho'":"";
		$result.=">" . $participante->vl_saldo_ant . "</TD>
		<TD>" . $participante->vl_gasto . "</TD>
		<TD";
		$result.=($participante->vl_credito>0)?" class='verde'":"";
		$result.=">" . $participante->vl_credito . "</TD>
		<TD";
		$result.=($participante->vl_premio>0)?" class='verde'":"";
		$result.=">" . $participante->vl_premio . "</TD>
		<TD";
		$result.=($participante->vl_resgate>0)?" class='vermelho'":"";
		$result.=">" . $participante->vl_resgate . "</TD>
		<TD";
		$result.=($participante->vl_saldo<0)?" class='vermelho'":"";
		$result.=">" . $participante->vl_saldo;
		if($participante->vl_saldo!=$participante->saldo){
			$result.="/" . $participante->saldo;
		}
		if($participante->id_aposta_sem_saldo==0){
			$result.=" *";
		}
		$result.="</TD>
		<TD>";
		$result.=($participante->id_federal==1)?'SIM':'NÃO';
		$result.="</TD>
		<TD>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=id_user type=hidden value=" . $participante->id_user .">
		<input name=rodada type=hidden value=" . $participante->rodada .">";
		if($participante->vl_credito>0){
			if($participante->ind_credito_processado==0){
				$result.="&nbsp;<input name='incluircredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CNF' TITLE='Informar crédito'/>";
			}else{
				$result.="CONFIRMADO";
			}
		}else{
			$result.="&nbsp;<input name='incluircredito' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CRD' TITLE='Informar crédito'/>";
		}
		$result.="
		</form>
		</TD>
		<TD>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=id_user type=hidden value=" . $participante->id_user .">
		<input name=rodada type=hidden value=" . $participante->rodada .">";
		if($participante->vl_saldo>0){
			$result.="&nbsp;<input name='resgate' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DEB' TITLE='Informar resgate' />";
		}else{
			if($participante->vl_saldo==0){
				$result.="ZERO";
			}else{
				$result.="* - *";
			}
		}
		$result.="
		</form>
		</TD>
		<TD>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=id_user type=hidden value=" . $participante->id_user .">
		<input name=rodada type=hidden value=" . $participante->rodada .">
		&nbsp;<input name='";
		$result.=($participante->id_ativo==0)?"ativarparticipante":"desativarparticipante";
		$result.="' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='";
		$result.=($participante->id_ativo==0)?"ATIV":"DES";
		$result.="' TITLE='";
		$result.=($participante->id_ativo==0)?"Ativar":"Desativar";
		$result.=" participante'/>
		</form>
		</TD>
		<TD>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $id_grupo .">
		<input name=id_user type=hidden value=" . $participante->id_user .">
		&nbsp;<input name='extratoparticipante' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='EXTR' TITLE='Extrato do participante'/>
		</form>
		</TD>
		</TR>";
	}
	$result.="
	<TR>
	<TD COLSPAN=11>
	**: INFORMA SE ESTAVA PARTICIPANDO DA ÚLTIMA RODADA<BR>
	CRD: INCLUIR CREDITO RECEBIDO PARA PARTICIPAÇÃO NO BOLÃO<BR>
	CNF: REGISTRA QUE O CRÉDITO FOI CONFIRMADO<BR>
	</TD>
	</TR>
	</TABLE>";
	return $result;
}

function loteca_instrucao_grupo ($id_grupo){
	$result="INSTRUÇÕES DO GRUPO... <BR>";
	$dadosgrupo=dadosgrupo($id_grupo , 0 );
	$result.=$dadosgrupo->tx_instrucao;
	return $result;
}

function loteca_novo_grupo (){
	if(isset($_POST['CONFIRMA'])){
		$id_user=get_current_user_id();
		$nm_grupo=$_POST['nm_grupo'];
		$publico=$_POST['publico'];
		$msg_email=htmlentities($_POST['msg_email']);
		$tx_instrucao=htmlentities($_POST['tx_instrucao']);
		$id_grupo=novo_grupo($id_user, $nm_grupo, $publico, $msg_email, $tx_instrucao);
		if($id_grupo){
			$publico_tx=$publico==1?'SIM':'NÃO';
			$result="
			<TABLE>
			
  <colgroup>
    <col width=150px>
    <col>
  </colgroup>
			
			<TR><TD COLSPAN=2 style='font-weight: bold;'>GRUPO INCLUÍDO COM SUCESSO. EM BREVE O ADMINISTRADOR DO SITE HABILITARÁ O GRUPO.
			</TD></TR>
			<TR><TD class=direita style='font-weight: bold;'>ID DO GRUPO
			</TD>
			<TD class=esquerda style='font-style: oblique;'>
			" . $id_grupo . "
			</TD></TR>
			<TR><TD class=direita style='font-weight: bold;'>NOME DO GRUPO
			</TD>
			<TD class=esquerda style='font-style: oblique;'>
			" . $nm_grupo . "
			</TD></TR>
			<TR><TD class=direita style='font-weight: bold;'>GRUPO ABERTO
			</TD>
			<TD class=esquerda style='font-style: oblique;'>
			" . $publico_tx . "
			</TD></TR>
			<TR><TD class=direita style='font-weight: bold;'>RODAPÉ DOS EMAILS
			</TD>
			<TD class=esquerda style='font-style: oblique;'>
			" . nl2br($msg_email) . "
			</TD></TR>
			<TR><TD class=direita style='font-weight: bold;'>RODAPÉ DAS PÁGINAS
			</TD>
			<TD class=esquerda style='font-style: oblique;'>
			" . nl2br($tx_instrucao) . "
			</TD></TR>
			</TABLE>
			";
		}else{
			$result="<TABLE><TR><TD>OCORREU UM PROBLEMA NA INCLUSÃO DO NOVO GRUPO, TENTE NOVAMENTE.</TD></TR></TABLE>";
		}
	}else{
		$result="
		" . create_form() . "
		<TABLE>
		<TR>
		<TD class=direita>NOME DO GRUPO
		</TD>
		<TD class=esquerda>
		<input name=nm_grupo type=text width=100% size=128 /><BR>(incluir validaçao online para disponibilidade do nome do grupo)
		</TD>
		</TR>
		<TR>
		<TD class=direita>GRUPO ABERTO PARA INSCRIÇÕES
		</TD>
		<TD class=esquerda>
		<input name=publico type=checkbox value=1 />
		</TD>
		</TR>
		<TR>
		<TD class=direita>MENSAGEM PARA RODAPÉ DOS EMAILS
		</TD>
		<TD class=esquerda>
		<textarea name=msg_email cols=128 maxlength=2000></textarea>
		</TD>
		</TR>
		<TR>
		<TD class=direita>MENSAGEM PARA RODAPÉ DAS PÁGINAS
		</TD>
		<TD class=esquerda>
		<textarea name=tx_instrucao cols=128 maxlength=2000></textarea>
		</TD>
		</TR>
		<TR>
		<TD COLSPAN=2>
		<input name='CONFIRMA' type='hidden' />
		<input name='CRIAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='NOVO GRUPO' />
		</TD>
		</TR>
		</TABLE>
		</FORM>";
	}
	return $result;
}

function loteca_outros_grupos (){
	$result="<TABLE>
	<TR>
	<TD>" . create_form() . "
	<input name='CRIAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='NOVO GRUPO' />
	</form>
	</TD>
	<TD>" . create_form() . "
	<input name='SOLICITAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ESCOLHER GRUPO PARA PARTICIPAR' />
	</form>
	</TD>
	</TABLE>";
	return $result;
}

function acessagrupo($id_grupo){
	$result="";
//	$result.=tab_dadosgrupo($id_grupo,0,TRUE);
// estou mexendo aqui
	$result.="<TABLE>";
	$result.=tab_dadosgrupo($id_grupo,0,FALSE);
//	$result.=tab_dadosrodada(0,0,FALSE);
	$result.=tab_dadosgruporodada($id_grupo,0,FALSE);
	$result.="</TABLE>";


	$result.="
	<TABLE>
	<TR>
	<TD>
	RODADA ATUAL
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='palpitar' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITAR' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='conf_participacao' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='MINHA PARTICIPAÇÃO/COTAS' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='conf_previsao' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOU DEPOSITAR' />
	</form>
	</TD>
	</TR>
	</TABLE>
	<TABLE>
	<TR>
	<TD>
	GERAL
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='listarparticipantes' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PARTICIPANTES' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RANKING' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	&nbsp;<input name='extrato' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='EXTRATO' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=user type=hidden value=" . get_current_user_id() .">
	&nbsp;<input name='verrodadas' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='RODADAS' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=user type=hidden value=" . get_current_user_id() .">
	<input name='conf_usuario' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIGURAR' />
	</form>
	</TD>
	<TD>
	" . create_form() . "
	<input name='outros_grupos' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OUTROS GRUPOS' />
	</form>
	</TD>
	";
	$boloes_admin=captura_boloes(1);
	$boloes_usu=captura_boloes(0);
	if((count($boloes_admin))+(count($boloes_usu))>1){
		$result.="
		<TD>" . create_form() ."
		<input name=grupo type=hidden value=" . $id_grupo .">
		&nbsp;<input name='INICIO' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='INICIO' />
		</form>
		</TD>";
	}
	$result.="
	</TR>
	</TABLE>";
	
	return $result;
}

function acessagrupo_2($id_grupo){
	$result="";
	$result.="
	<TABLE><TR><TD>
	" . create_form() . "
	<input name='SOLICITAR' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='ENTRAR EM OUTROS GRUPOS' />
	</form>
	</TD></TR></TABLE>";
	return $result;
}

function extrato($id_grupo,$id_user=NULL){
	$result='';
	$extrato=carrega_extrato($id_grupo,$id_user);
	if($extrato){
		$situacao=99;
		$result.="<TABLE>";
		if($id_user!=NULL){
			$result.="
			<TR>
			<TH COLSPAN=7>APELIDO : " . $extrato[0]->apelido . "</TH>
			</TR>";
		}
		$result.="
		<TR>
		<TH>RODADA</TH>
		<TH>INICIO</TH>
		<TH>FIM</TH>
		<TH>SALDO ANT</TH>
		<TH>GASTO</TH>
		<TH>CRÉDITO</TH>
		<TH>PRÊMIO</TH>
		<TH>RESGATE</TH>
		<TH>SALDO ***</TH>
		</TR>";
		foreach($extrato as $linha){
			$result.="
			<TR>
			<TD class='centralizado'>" . $linha->rodada . "</TD>
			<TD class='centralizado'>" . date('d/m/Y',strtotime($linha->dt_inicio_palpite)) . "</TD>
			<TD class='centralizado'>" . date('d/m/Y',strtotime($linha->dt_fim_palpite)) . "</TD>
			<TD class='esquerda";
			$result.=($linha->vl_saldo_ant<0)?" vermelho":"";
			$result.="'>" . $linha->vl_saldo_ant . "</TD>
			<TD class='esquerda'>" . $linha->vl_gasto . "</TD>
			<TD class='esquerda";
			$result.=($linha->vl_credito>0)?" verde":"";
			$result.="'>" . $linha->vl_credito . "</TD>
			<TD class='esquerda";
			$result.=($linha->vl_premio>0)?" verde":"";
			$result.="'>" . $linha->vl_premio . "</TD>
			<TD class='esquerda";
			$result.=($linha->vl_resgate>0)?" vermelho":"";
			$result.="'>" . $linha->vl_resgate . "</TD>
			<TD class='esquerda";
			$result.=($linha->vl_saldo<0)?" vermelho":"";
			$result.="'>" . $linha->vl_saldo . "</TD>
			</TR>";
		}
		$result.="</TABLE>";
	}else{
		$result.="<H3>PROBLEMAS NA CAPTURA DAS INFORMAÇÕES DO EXTRATO! TENTE NOVAMENTE MAIS TARDE!</H3>";
	}
	return $result;
}

function listarparticipantes($id_grupo){
	$result='';
	$participantes=carrega_participantes($id_grupo);
	if($participantes){
		$result.="
		<TABLE>
		<TR>
		<TH>ID</TH>
		<TH></TH>
		<TH>APELIDO</TH>
		<TH>EMAIL</TH>
		<TH>FEDERAL</TH>
		<TH>SITUAÇÃO</TH>
		</TR>";
		foreach($participantes as $linha){
			$result.="
			<TR>
			<TD>" . $linha->id_user . "</TD>
			<TD>" . get_avatar($linha->id_user, 24) . "</TD>
			<TD>" . $linha->apelido . "</TD>
			<TD>" . $linha->email . "</TD>
			<TD>";
			$result.=$linha->id_federal==1?'SIM':'NÃO';
			$result.="</TD>
			<TD>";
			$result.=$linha->id_ativo==1?'OK':'INATIVO';
			$result.="
			</TD>
			</TR>";
		}
		$result.="</TABLE>";
	}else{
		$result.="<H3>PROBLEMAS NA CAPTURA DAS INFORMAÇÕES DOS PARTICIPANTES! TENTE NOVAMENTE MAIS TARDE!</H3>";
	}
	return $result;
}

function palpitar($id_grupo){
 $result='';
 $jogos=carrega_jogos_palpitar();
 if($jogos){
	foreach($jogos as $jogo){
		$rodada=$jogo->rodada;
		break;
	}
	$user=get_current_user_id();
	$palpites_temp=le_palpites($id_grupo,$rodada,$user);
	$volantes=loteca_tipos_volante( 0 , 9999 , ' ASC ' , 3);
	$lista="\n lista = [ 'XX' ";
	$valores="\n valores = [ 0 ";
	foreach($volantes as $key=>$volante){
		$simples = 14 - ( $volante->duplos + $volante->triplos );
		$lista.=", 'S:" . $simples . " D:" . $volante->duplos . " T:" . $volante->triplos . "' ";
		$valores.=", '" . number_format($volante->vl_aposta , 2 , "," , "") . "' ";
	}
	$lista.=" ];";
	$valores.=" ];";
	$result.="
\n<script type='text/javascript'>
\n function atualiza_jogo(){" . $lista . $valores . "
\n  triplo=0;
\n  duplo=0;
\n  simples=0;
\n  for (i=1;i<15;i++){
\n   if(document.getElementById(i + '-1').checked) {
\n    if(document.getElementById(i + '-X').checked) {
\n     if(document.getElementById(i + '-2').checked) {
\n      triplo++;
\n     } else {
\n      duplo++;
\n     }
\n    } else {
\n     if(document.getElementById(i + '-2').checked) {
\n      duplo++;
\n     } else {
\n      simples++;
\n     }
\n    }
\n   } else {
\n 	  if(document.getElementById(i + '-X').checked) {
\n     if(document.getElementById(i + '-2').checked) {
\n      duplo++;
\n     } else {
\n      simples++;
\n     }
\n    } else {
\n     if(document.getElementById(i + '-2').checked) {
\n      simples++;
\n     }
\n    }
\n   }
\n  }
\n  jogos=simples+duplo+triplo;
\n  texto='S:';
\n  texto=texto.concat(simples);
\n  texto=texto.concat(' D:');
\n  texto=texto.concat(duplo);
\n  texto=texto.concat(' T:');
\n  texto=texto.concat(triplo);
\n  ok=lista.indexOf(texto);
\n  texto=texto.concat(' J:');
\n  texto=texto.concat(jogos);
\n  if(ok!=-1){
\n   texto=texto.concat(' OK * ');
\n   texto=texto.concat(valores[ok]);
\n   texto=texto.concat(' * Se você fosse apostar estes palpites!');
\n  } else {
\n   texto=texto.concat(' INVÁLIDO');
\n  }
\n  document.getElementById('combinacao').innerHTML = texto;
\n  for(i=1;i<39;i++){
\n  	document.getElementById('tipojogo' + i).className =
\n  		document.getElementById('tipojogo' + i).className.replace
\n  			( /(?:^|\s)vermelho(?!\S)/g , '' )	
\n  }
\n  if((jogos!=14)||(ok==-1)){
\n   document.getElementById('registrarpalpite').disabled = true;
\n  } else {
\n   document.getElementById('registrarpalpite').disabled = false;
\n   document.getElementById('tipojogo' + ok).className += ' vermelho';
\n  }
\n}
\n</script>
	<div class='centralizado'>
	<TABLE class='semborda'>
	<TR>
	<TD>
	" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=rodada type=hidden value=" . $rodada .">
	<input name=user type=hidden value=" . $user .">
	<input name=palpites type=hidden value=PALPITES>
	<TABLE class='minimo'>
	<TR>
	<TH>#</TH>
	<TH>TIME DA CASA</TH>
	<TH>1</TH>
	<TH>X</TH>
	<TH>2</TH>
	<TH>VISITANTE</TH>
	<TH>DIA</TH>
	<TH>#</TH>
	<TH>STATS</TH>
	</TR>";
	foreach($palpites_temp as $palpite){
		$palpites[$palpite['seq']]['1']=$palpite['time1'];
		$palpites[$palpite['seq']]['X']=$palpite['empate'];
		$palpites[$palpite['seq']]['2']=$palpite['time2'];
	}
	foreach($jogos as $jogo){
		$result.="
		<TR>
		<TD>" . $jogo->seq . "</TD>
		<TD class='direita'>" . $jogo->time1 . "</TD>
		<TD>
		<input id='" . $jogo->seq . "-1' name='" . $jogo->seq . "-1' type=checkbox autofocus onchange='atualiza_jogo()'";
		$result.=($palpites[$jogo->seq]['1'])?" checked ":"";
		$result.="></TD>
		<TD><input id='" . $jogo->seq . "-X' name='" . $jogo->seq . "-X' type=checkbox onchange='atualiza_jogo()'";
		$result.=($palpites[$jogo->seq]['X'])?" checked ":"";
		$result.="></TD>
		<TD><input id='" . $jogo->seq . "-2' name='" . $jogo->seq . "-2' type=checkbox onchange='atualiza_jogo()'";
		$result.=($palpites[$jogo->seq]['2'])?" checked ":"";
		$result.="></TD>
		<TD>" . $jogo->time2 . "</TD>
		<TD>" . $jogo->dia . "</TD>
		<TD><INPUT TYPE=BUTTON VALUE='#' class='loteca button-primary' 
		onclick=" . '"window.open(' . "'estatisticas-loteca?time1=" . $jogo->time1 . "&time2=" . $jogo->time2 . "','loteca-estatisticas')" . '"' . ">
		</TD>
		<TD><a class='stats-loteca' href='" . $jogo->link_stat . "' target='_blank'>STATS</a></TD>
		</TR>";
	}
	$result.="
	<TR>
	<TD id='combinacao' COLSPAN=8 class='centralizado'>
	S:0 D:0 T:0 J:0 INVÁLIDO
	</TD>
	</TR>
	<TR>
	<TD COLSPAN=8 class='centralizado'>
	&nbsp;<input id='registrarpalpite' name='registrarpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='REGISTRAR PALPITE' DISABLED/>
	</TD>
	</TR>
	</TABLE>
	</form>
	</TD>
	<TD> " . tab_jogos_validos() . "
	</TD>
	</TR>
	</TABLE>
	</div>
	<script type='text/javascript'>\natualiza_jogo();\n</script>";
 }else{
	$result.="<H3>NÃO HÁ JOGOS PARA FAZER PALPITES!</H3>";
 }
 
 return $result;

}

function tab_jogos_validos(){
	$volantes=loteca_tipos_volante( 0 , 9999 , ' ASC ' , 3);
	$col=1;
	$lin=1;
	$result="
	<TABLE class='centralizado'>
	<TR><TH COLSPAN=4>TIPOS DE JOGOS VÁLIDOS</TH></TR>";
	foreach($volantes as $key=>$volante){
		$simples = 14 - ( $volante->duplos + $volante->triplos );
		if($col==1){
			$result.="<TR>";
		}
		$result.="<TD id=tipojogo" . $lin . " > S:" . $simples . " D:" . $volante->duplos . " T:" . $volante->triplos . "</TD>";
		$col++;
		$lin++;
		if($col>4){
			$result.="</TR>";
			$col=1;
		}
	}
	while($col<4){
		$col++;
		$result.="<TD></TD>";
		if($col==4){
			$result.="</TR>";
		}
	}
	$result.="</TABLE>";
	return $result;
}

function tab_dadosgrupo($id_grupo,$admin = 0,$table = TRUE){
	$dadosgrupo=dadosgrupo($id_grupo,$admin);
	$result="";
	if($table){
		$result.="<TABLE>";
	}
	$result.="
	<TR>
	<TD>Grupo: " . $dadosgrupo->id_grupo . " / " . $dadosgrupo->nm_grupo . "</TD>
	<TD>Administrador: " . $dadosgrupo->id_user . " / " . $dadosgrupo->apelido . "</TD>
	<TD>Saldo do grupo: " . $dadosgrupo->saldo_grupo . "</TD>
	</TR>";
	if($admin==0){
		$result.="
		<TR><TD>Seu saldo: " . $dadosgrupo->saldo_participante . "</TD>
		<TD>...</TD>
		<TD>...</TD></TR>";
	}
	if($table){
		$result.="</TABLE>";
	}		
	return $result;
}
/*
function tab_dadosrodada($rodada = 0,$admin = 0,$table = TRUE){
	$dadosrodada=dadosgruporodada($rodada,$admin);
	$result="";
	if($table){
		$result.="<TABLE>";
	}
	$result.="
	<TR><TD>Início dos palpites: " . $dadosrodada->dt_inicio_palpite . "</TD>
	<TD>Término dos palpites: " . $dadosrodada->dt_fim_palpite . "</TD>
	<TD>Data da apuração: " . $dadosrodada->dt_sorteio . "</TD></TR>";
	if($table){
		$result.="</TABLE>";
	}
	return $result;
}
*/
function tab_dadosgruporodada($id_grupo,$admin = 0,$table = TRUE){
	$dadosgrupo=dadosgruporodada($id_grupo,$admin);
	$result="";

	if($table){
		$result.="<TABLE>";
	}
	$result.="
	<TR><TD>Início dos palpites: " . $dadosgrupo->dt_inicio_palpite . "</TD>
	<TD>Término dos palpites: " . $dadosgrupo->dt_fim_palpite . "</TD>
	<TD>Data da apuração: " . $dadosgrupo->dt_sorteio . "</TD></TR>";
	if($table){
		$result.="</TABLE>";
	}

	if($table){
		$result.="<TABLE>";
	}
	$result.="
	<TR><TD>Rodada atual: " . $dadosgrupo->rodada . "<BR>
	Valor máximo: " . $dadosgrupo->vl_max . "<BR>
	Valor mínimo: " . $dadosgrupo->vl_min . "<BR>
	Quanto gastar: ";
	switch ($dadosgrupo->tip_rateio){
		case 0:
			$result.='Não teremos bolão nessa rodada';
			break;
		case 1:
			$result.="Mínimo acima da média";
			break;
		case 2:
			$result.="Máximo abaixo da média";
			break;
		case 3:
			$result.="Máximo";
			break;
		case 4:
			$result.="Mínimo";
			break;
	}
//	$result.="<BR>Participantes: " . $dadosgrupo->qt_participantes_ativos . " / " . $dadosgrupo->qt_participantes;
	$result.="</TD>";
	$result.="
	<TD>Gera volante com cota: ";
	$result.=($dadosgrupo->ind_bolao_volante==1)?"Sim":"Não";
	$result.="
	<BR>
	Valor mínimo da cota: " . $dadosgrupo->vl_lim_rateio . "<BR>
	Máximo de zebras por volante: " . $dadosgrupo->qt_max_zebras . "<BR> " .
//	Início dos palpites: " . $dadosgrupo->dt_inicio_palpite . "<BR>
    "
	Prêmio estimado: R$ " . number_format ( $dadosgrupo->vl_premio_estimado  , 2  , ',' , '.') . "</TD>
	<TD>Mínimo de zebras por volante: " . $dadosgrupo->qt_min_zebras . "<BR>
	Amplia ZEBRA: ";
	$result.=$dadosgrupo->amplia_zebra==1?"Sim":"Não";
	$result.="
	<BR>
	Desdobramento liberado: ";
	$result.=$dadosgrupo->ind_libera_proc_desdobra==1?"Sim":"Não";
//	$result.="<BR>Término dos palpites: " . $dadosgrupo->dt_fim_palpite;
	$result.="<BR>Participantes: " . $dadosgrupo->qt_ok . "(OK) / " . $dadosgrupo->qt_participantes_ativos . "(ATIVOS) / " . $dadosgrupo->qt_participantes . "(TOTAL) / Cotas: " . $dadosgrupo->qt_cotas;
	$result.="</TD></TR>";
	if($table){
		$result.="</TABLE>";
	}
	return $result;
}

function tab_grupos_admin($boloes_admin){
	$result="";
	$result.="
	<TABLE>
	<TR><TH COLSPAN=6>BOLÕES QUE ADMINISTRO</TR>
	<TR><TH>ID</TH>
	<TH>Grupo</TH>
	<TH>Situação</TH>
	<TH>Apelido</TH>
	<TH>R$ Grupo</TH>
	<TH></TH>
	</TR>";
	foreach ($boloes_admin as $bolao){
		$result.="
		<TR>
		<TD>&nbsp;" . $bolao->id_grupo . "</TD>
		<TD>&nbsp;" . $bolao->nm_grupo . "</TD>
		<TD class='centralizado'>";
		$result.=($bolao->id_ativo)?"ATIVO":"INATIVO";
		$result.="
		</TD>
		<TD>&nbsp;" . $bolao->apelido . "</TD>
		<TD>&nbsp;" . $bolao->saldo . "</TD>
		<TD class='centralizado'>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $bolao->id_grupo .">
		&nbsp;<input name='admingrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OPCOES' />
		</form>
		</TD>
		</TR>";
		}
	$result.="</TABLE>";
	return $result;
}

function tab_grupos_usu($boloes_usu){
	$result="";
	$result.="
	<TABLE>
	<TR><TH COLSPAN=6>BOLÕES QUE PARTICIPO</TR>
	<TR><TH>ID</TH>
	<TH>Grupo</TH>
	<TH>Apelido</TH>
	<TH>Seu Saldo</TH>
	<TH>R$ Grupo</TH>
	<TH></TH>
	</TR>";
	foreach ($boloes_usu as $bolao){
		$result.="
		<TR>
		<TD>&nbsp;" . $bolao->id_grupo . "</TD>
		<TD>&nbsp;" . $bolao->nm_grupo . "</TD>
		<TD>&nbsp;" . $bolao->apelido . "</TD>
		<TD>&nbsp;" . $bolao->saldo . "</TD>
		<TD>&nbsp;" . $bolao->saldo_grupo . "</TD>
		<TD class='centralizado'>
		" . create_form() . "
		<input name=grupo type=hidden value=" . $bolao->id_grupo .">
		&nbsp;<input name='acessargrupo' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='OPCOES' />
		</form>
		</TD>
		</TR>";
		}
	$result.="</TABLE>";
	return $result;
}

function configurarusuario($id_grupo,$id_user=0){
	$result="";
	if($id_user==0){
		$id_user=get_current_user_id();
	}
	if(isset($_POST['participa_sem_saldo'])){
		$participa_sem_saldo=$_POST['participa_sem_saldo'];
		if(!in_array($participa_sem_saldo,array(0 , 1))){
			$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(001).</H1>';
			return $result;
		}
	}else{
		$participa_sem_saldo=0;
	}
	if(isset($_POST['id_federal'])){
		$id_federal=$_POST['id_federal'];
		if(!in_array($id_federal,array(0 , 1))){
			$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(003).</H1>';
			return $result;
		}
	}else{
		$id_federal=0;
	}
	if(isset($_POST['padrao_cotas'])){
		$padrao_cotas=$_POST['padrao_cotas'];
	}
	if(isset($_POST['apelido'])){
		$apelido=$_POST['apelido'];
	}
	$dados_participante=dados_participante($id_user,$id_grupo);
	if(isset($_REQUEST['conf_usuario'])){
		if(($_POST['conf_usuario']!="CONFIRMA")){
			$result.="<TABLE>
			" . create_form() . "
			<TR><TD>APELIDO: <input name=apelido type=text value='" . $dados_participante->apelido . "'>";
			$result.="
			</TD></TR>
			<TR><TD>PARTICIPAR SEM SALDO: <input name=participa_sem_saldo type=checkbox value=1 ";
			if($dados_participante->id_aposta_sem_saldo){$result.='checked ';}
			$result.="
			> Você se compromete a realizar os depósitos, o administrador vai fazer as apostas contando com a sua participação, mesmo sem saldo suficiente.</TD></TR>";
			$result.="
			<TR><TD>PARTICIPA DO BOLÃO DA FEDERAL: <input name=id_federal type=checkbox value=1 ";
			if($dados_participante->id_federal){$result.='checked ';}
			$result.="
			> (Quando houver!!!) </TD></TR>
			<TR><TD>QUANTIDADE PADRÃO DE COTAS: <input name=padrao_cotas type=number step='1' min=1 value=" . $dados_participante->qt_cotas . " >";
			$result.="
			</TD></TR>
			<TR><TD>
			<input name=grupo type=hidden value=" . $id_grupo .">
			&nbsp;<input name='conf_usuario' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA' />
			</TD></TR>
			</form>
			</TABLE>
			";
		}else{
			if(!(isset($padrao_cotas)&&isset($participa_sem_saldo)&&isset($apelido))){
				$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(002).</H1>';
				return $result;
			}
			if(atualiza_participante($id_user,$id_grupo,$apelido, $participa_sem_saldo, $padrao_cotas, $id_federal)){
				$result.='<H1>DADOS ATUALIZADOS.</H1>';
			}else{
				$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(003).</H1>';
				return $result;
			}
		}	
	}else{
		$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(004).</H1>';
		return $result;
	}
	return $result;
}

function configurarprevisao($id_grupo,$id_user = 0){
	$result="";
	if($id_user==0){
		$id_user=get_current_user_id();
	}
	if(isset($_POST['previsao'])){
		$previsao=$_POST['previsao'];
	}else{
		$previsao=0;
	}
	if(isset($_POST['rodada'])){
		$rodada=$_POST['rodada'];
	}else{
		$gruporodada=dadosgruporodada($id_grupo,0);
		$rodada=$gruporodada->rodada;
	}
	$dados_participante=dados_participante_rodada($id_user,$id_grupo,$rodada);
	if(isset($_POST['conf_previsao'])){
		$conf_previsao=$_POST['conf_previsao'];
	}else{
		if(isset($_GET['conf_previsao'])){
			$conf_previsao=$_GET['conf_previsao'];
		}else{
			unset($conf_previsao);
		}
	}
	if(isset($conf_previsao)){
		if(($_POST['conf_previsao']!="CONFIRMA")){
			$result.="<TABLE>
			" . create_form() . "
			<TR><TD>";
			if($dados_participante->ind_credito_processado){
				$result.="VALOR QUE VOU DEPOSITAR: R$ " . $dados_participante->vl_credito . " (Confirmado pelo administrador)";
			}else{
				$result.="VALOR QUE VOU DEPOSITAR: <input name=previsao type=number step='0.01' min=0 pattern='^\d+(\.|\,)\d{2}$' value=" . $dados_participante->vl_credito ." >";
			}
			$result.="</TD></TR>
			<TR><TD>
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=rodada type=hidden value=" . $rodada .">";
			if($dados_participante->ind_credito_processado){
				$result.="&nbsp;<input name='voltar' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='VOLTAR' />";
			}else{
				$result.="&nbsp;<input name='conf_previsao' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA' />";
			}
			$result.="</TD></TR>
			</form>
			</TABLE>
			";
		}else{
//			if(atualiza_previsao_participante($id_user,$id_grupo,$rodada, $previsao)){
			if(db_inclui_credito($id_grupo,$id_user,$rodada,$previsao)){
				$result.='<H1>DADOS ATUALIZADOS.</H1>';
			}else{
				$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(3).</H1>';
				return $result;
			}
		}	
	}else{
		$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(4).</H1>';
		return $result;
	}
	return $result;
}

function configurarparticipacao($id_grupo,$id_user = 0){
	$result="";
	if($id_user==0){
		$id_user=get_current_user_id();
	}
	if(isset($_POST['participa'])){
		$participa=$_POST['participa'];
		if(!in_array($participa,array(0 , 1))){
			$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(1).</H1>';
			return $result;
		}
	}else{
		$participa=0;
	}
	if(isset($_POST['id_federal'])){
		$id_federal=$_POST['id_federal'];
		if(!in_array($id_federal,array(0 , 1))){
			$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(3).</H1>';
			return $result;
		}
	}else{
		$id_federal=0;
	}
	if(isset($_POST['cotas'])){
		$cotas=$_POST['cotas'];
	}
	if(isset($_POST['rodada'])){
		$rodada=$_POST['rodada'];
	}else{
		$gruporodada=dadosgruporodada($id_grupo,0);
		$rodada=$gruporodada->rodada;
	}
	$dados_participante=dados_participante_rodada($id_user,$id_grupo,$rodada);
	if(isset($_REQUEST['conf_participacao'])){
		if(($_REQUEST['conf_participacao']!="CONFIRMA")){
			$result.="<TABLE>
			" . create_form() . "
			<TR><TD>PARTICIPAR DESTA RODADA: <input name=participa type=checkbox value=1 ";
			if($dados_participante->participa){$result.='checked ';}
			$result.="
			></TD></TR><TR><TD>PARTICIPAR DA FEDERAL NESTA RODADA: <input name=id_federal type=checkbox value=1 ";
			if($dados_participante->id_federal){$result.='checked ';}
			$result.="
			> (Se houver!!!) </TD></TR>
			<TR><TD>COTAS: <input name=cotas type=number step='1' min=1 value=" . $dados_participante->qt_cotas . " >";
			$result.="
			</TD></TR>
			<TR><TD>
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=rodada type=hidden value=" . $rodada .">
			&nbsp;<input name='conf_participacao' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='CONFIRMA' />
			</TD></TR>
			</form>
			</TABLE>
			";
		}else{
			if(!(isset($cotas)&&isset($participa))){
				$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(2).</H1>';
				return $result;
			}
			if(atualiza_participante_rodada($id_user,$id_grupo,$rodada, $participa, $cotas, $id_federal)){
				$result.='<H1>DADOS ATUALIZADOS.</H1>';
			}else{
				$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(3).</H1>';
				return $result;
			}
		}	
	}else{
		$result.='<H1>PROBLEMAS NA ATUALIZAÇÃO DO PARTICIPANTE(4).</H1>';
		return $result;
	}
	return $result;
}

function listargruposabertos(){
	$gruposabertos=captura_grupos_abertos();
	$result="";
	if($gruposabertos){
		$result.="
		<table>
		<tr><th>ID</th>
		<th>Grupo</th>
		<th>Administrador</th>
		<th>Nome</th>
		<th>Email</th>
		<th>Participantes</th>
		<th>QUERO</th>
		</tr>";		
		foreach($gruposabertos as $linha){
			$result.="
			<tr class='centralizado'>
			<td>" . $linha->id_grupo  . "</td>
			<td>" . $linha->nm_grupo  . "</td>
			<td>" . $linha->id_user  . " - " . $linha->apelido . "</td>
			<td>" . $linha->nome . "</td>
			<td>" . $linha->email . "</td>
			<td>" . $linha->qt_participante . "</td>
			<td>" . create_form('POST','display:inline') . "<input type=submit name='quero_participar' value='SOLICITAR'><input type=hidden name=grupo 
			value='". $linha->id_grupo. "'></form>
			</td>
			</tr>";
		}
		$result.="</table>";
	}else{
		$result.="
		NENHUM GRUPO DISPONÍVEL PARA SOLICITAR PARTICIPAÇÃO.
		" . create_form('POST','display:inline') . "<input type=submit name='VOLTAR' value='VOLTAR' /></form>";
	}
	return $result;
}

function quero_participar ($id_grupo){
	if(verifica_grupo_aberto($id_grupo)){
		if(inclui_solicitacao($id_grupo)){
			return "Solicitação efetuada, o administrador do bolão receberá um email informando.";
		}else{
			return "Não foi possível incluir sua solicitação. Entre em contato com o administrador do bolão.";
		}
	}else{
		return "Não foi possível incluir sua solicitação. Entre em contato com o administrador do bolão.";
	}
}

function datetimepicker(){
	wp_enqueue_script('loteca-admin-script', plugin_dir_url(__FILE__)  . 'js/scripts-admin.js', array('jquery', 'wp-color-picker', 'loteca-js'));
}

function table_ranking($ranking,$id_grupo=0,$rodada=0){
	$result="<TABLE><TH>#</TH><TH>ID</TH><TH>PALPITEIRO</TH><TH>ACERTOS</TH>";
	if($rodada){
		$result.="<TH>TIPO DE PALPITE</TH>";
	}else{
		$result.="<TH>RODADAS</TH><TH>MEDIA</TH><TH>MÁXIMO</TH><TH>MÍNIMO</TH>";
	}
	$result.="<TH>PONTUAÇÃO</TH>";
	$seq=0;
	foreach($ranking as $dados){
		$result.="<TR><TD>". ++$seq ."</TD><TD>". $dados->id_user ."</TD><TD>". $dados->apelido ."</TD><TD class='centralizado'>". $dados->pontos ."</TD>";
		if($rodada){
			$result.="<TD class='centralizado'>". $dados->duplos . "D-" .$dados->triplos . "T";
			
			$result.=create_form("POST","display:inline;") . "
			<input name=grupo type=hidden value=" . $id_grupo .">
			<input name=id_user type=hidden value=" . $dados->id_user .">
			<input name=rodada type=hidden value=" . $rodada .">
			<input name=admin type=hidden value=0>
			&nbsp;<input name='detalharpalpite' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='PALPITES' />
			</form>";
			
			$result.="</TD>";
		}else{
			$result.="<TD class='centralizado'>". $dados->rodadas ."</TD><TD class='centralizado'>". $dados->media ."</TD><TD class='centralizado'>". $dados->maximo ."</TD><TD class='centralizado'>". $dados->minimo ."</TD>";
		}
		$result.="<TD class='centralizado'>". $dados->mira;
		if(!$rodada){
			$result.="(MEDIA:" . $dados->p_media . ")";
		}
		$result.="</TD></TR>";
	}
	$result.="</TABLE>";
	return $result;
}
function ranking($id_grupo,$tipo,$ano=0,$mes=0,$rodada=0){
	$info_ult_rodada=ultima_rodada();
	$max_rodada=$info_ult_rodada->rodada;
	$max_rodada--;
	if($rodada==0){
		$rodada=$max_rodada;
	}
	if($ano==0){
		$ano=date('Y');
	}
	if($mes==0){
		$mes=date('m');
	}
	$result="";
	$result.=
	"<TABLE>
	<TR>
	<TD>
	RANKING
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='6'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='6 MESES' />
	</form>
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='12'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='12 MESES' />
	</form>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='1'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='MES ANTERIOR' />
	</form>
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='M'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DO MES:' />" .
//   "&nbsp;<input name='mes' class='loteca button-primary' type=number step='1' min=1 max=12 " . SUBMITDISABLED . " value='" . $mes . "' />
//	 "&nbsp;<input name='ano' class='loteca button-primary' type=number step='1' min=2015 max=2100 " . SUBMITDISABLED . " value='" . $ano . "' />
	"&nbsp;<input name='anomes' class='loteca button-primary' type=month min='2014-01' max='" . date('Y') . "-" . date('m') . "'" . SUBMITDISABLED . " value='" . $ano . "-" . $mes . "' />
	</form>
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='A'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DO ANO:' />
	&nbsp;<input name='ano' class='loteca button-primary' type=number step='1' min=2015 max=2100 " . SUBMITDISABLED . " value='" . $ano . "' />
	</form>
	</TD>
	<TD>" . create_form() . "
	<input name=grupo type=hidden value=" . $id_grupo .">
	<input name=t_ranking type=hidden value='R'>
	&nbsp;<input name='ranking' class='loteca button-primary' type='submit' " . SUBMITDISABLED . " value='DA RODADA:' />
	&nbsp;<input name='rodada' class='loteca button-primary' type=number step='1' min=1 max=" . $max_rodada . " " . SUBMITDISABLED . " value='" . $rodada . "' />
	</form>
	</TD>
	</TABLE>";
	switch($tipo){
		case '6':
			$result.="<TABLE><TR><TH class='maior'>ÚLTIMOS 6 MESES</TH></TR></TABLE>";
			$ranking=ranking_6($id_grupo);
			$result.=table_ranking($ranking);
			break;
		case '12':
			$result.="<TABLE><TR><TH class='maior'>ÚLTIMOS 12 MESES</TH></TR></TABLE>";
			$ranking=ranking_12($id_grupo);
			$result.=table_ranking($ranking);
			break;
		case '1':
			$result.="<TABLE><TR><TH class='maior'>MÊS ANTERIOR</TH></TR></TABLE>";
			$ranking=ranking_1($id_grupo);
			$result.=table_ranking($ranking);
			break;
		case 'M':
			$result.="<TABLE><TR><TH class='maior'>MÊS " . $mes . "/" . $ano ."</TH></TR></TABLE>";
			$ranking=ranking_mes($id_grupo, $ano, $mes);
			$result.=table_ranking($ranking);
			break;
		case 'A':
			$result.="<TABLE><TR><TH class='maior'>ANO " . $ano ."</TH></TR></TABLE>";
			$ranking=ranking_ano($id_grupo, $ano);
			$result.=table_ranking($ranking);
			break;
		case 'R':
			$result.="<TABLE><TR><TH class='maior'>RODADA " . $rodada ."</TH></TR></TABLE>";
			$ranking=ranking_rodada($id_grupo, $rodada);
			$result.=table_ranking($ranking,$id_grupo,$rodada);
			break;
		default:
			$result.=" ERRO INESPERADO!!!";
	}
	$result.="<H2>PONTUAÇÃO: ACERTO COM ESCOLHA SIMPLES 6 PONTOS/ACERTO COM ESCOLHA DUPLA 3 PONTOS/ACERTO COM ESCOLHA TRIPLA 2 PONTOS</H2>";
	return $result;
}

function monta_apostas_novo($volantes_combinados,$palpite_grupo_peso) {
	
//	error_log('IDENTIFICANDO ESTRUTURA ' .print_r($palpite_grupo_peso,true) );

// identificar jogos com somente um resultado possível
	$somente_um=array();
	foreach($palpite_grupo_peso as $peso => $controle){
		foreach($controle as $dado){
			if(isset($somente_um[$dado['seq']])){
				$somente_um[$dado['seq']]=9;
			}else{
				$somente_um[$dado['seq']]=$dado['somar'];
			}
		}
	}
	foreach($somente_um as $seq => $linha){
		if($linha==9){
			unset($somente_um[$seq]);
		}
	}
//	error_log('SOMENTE UM ' .print_r($somente_um,true) );
	
	$max_loop=count($palpite_grupo_peso)+1;
//	$palpite_grupo_tmp=$palpite_grupo_peso;
//  calcula o maximo de cada linha para permitir a eliminação dos jogos anteriores;
	$linha=array();
	foreach($palpite_grupo_peso as $peso=> $grupo ){
		foreach($grupo as $key => $palpite_1){
			if($palpite_1['peso']!=0){
				if(isset($linha[$palpite_1['seq']])){
					$linha[$palpite_1['seq']]+=$palpite_1['somar'];
				}else{
					$linha[$palpite_1['seq']]=$palpite_1['somar'];
				}
			}
		}
	}
	$linha=array_reverse($linha,true);
	
	$retirar=array();
	$tentou=array();
	$volantes=array();
	foreach($volantes_combinados as $combinacao){
		$tentou[]=$combinacao;
		$volantes=monta_volante($combinacao,$palpite_grupo_peso,$somente_um);
		if($volantes['TIPO']!='FALHA'){
			break;
		}
	}
//	error_log('SAINDO... $cnt : ' . $cnt . ' | $max_loop : ' . $max_loop . ' | $cnt2 : ' . $cnt2 );
	foreach($tentou as $combinacao_2){
		if(isset($volantes['TIPOS'])){
			if($combinacao_2!=$volantes['TIPOS']){
				$volantes['FALHA'][]=$combinacao_2;
			}
		}else{
			$volantes['FALHA']=$tentou;
		}
	}
//	error_log('volantes ... ' . print_r($volantes,true));
	return $volantes;
//  ------------ fim 	
};

function monta_volante($combinacao,$palpite_grupo_peso,$somente_um){
	$palpite_grupo=$palpite_grupo_peso;
	$volantes_1=array();
	$volantes=array();
	$cnt_comb=0;
	$elimina=true;
	foreach($combinacao as $keycomb => $tipo_volante){
		$cnt_comb++;
		if($cnt_comb==count($combinacao)){
			$elimina=false;
		}
		$volantes_1[$keycomb]=monta_jogo_volante($tipo_volante,$palpite_grupo,$somente_um,$elimina);
	}
	$ok=true;
//	error_log('volantes kkk ' . print_r($volantes_1,true));
	foreach($volantes_1 as $key => $volante){
		if(($volante['DUP']!=$volante['MDUP'])||($volante['TRI']!=$volante['MTRI'])||($volante['LIN']!=14)){
			$OK=false;
		}else{
			foreach($volante as $lin => $jogo){
				switch($lin){
					case 1     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 2     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 3     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 4     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 5     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 6     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 7     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 8     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 9     : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 10    : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 11    : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 12    : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 13    : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 14    : $volantes[$key]['JOGO'] [$lin] = $jogo; break;
					case 'Z1'  : $volantes[$key]['ZEBRA'][1]    = $jogo; break;
					case 'Z2'  : $volantes[$key]['ZEBRA'][2]    = $jogo; break;
					case 'Z3'  : $volantes[$key]['ZEBRA'][3]    = $jogo; break;
					case 'Z4'  : $volantes[$key]['ZEBRA'][4]    = $jogo; break;
					case 'Z5'  : $volantes[$key]['ZEBRA'][5]    = $jogo; break;
					case 'Z6'  : $volantes[$key]['ZEBRA'][6]    = $jogo; break;
					case 'Z7'  : $volantes[$key]['ZEBRA'][7]    = $jogo; break;
					case 'Z8'  : $volantes[$key]['ZEBRA'][8]    = $jogo; break;
					case 'Z9'  : $volantes[$key]['ZEBRA'][9]    = $jogo; break;
					case 'Z10' : $volantes[$key]['ZEBRA'][10]   = $jogo; break;
					case 'Z11' : $volantes[$key]['ZEBRA'][11]   = $jogo; break;
					case 'Z12' : $volantes[$key]['ZEBRA'][12]   = $jogo; break;
					case 'Z13' : $volantes[$key]['ZEBRA'][13]   = $jogo; break;
					case 'Z14' : $volantes[$key]['ZEBRA'][14]   = $jogo; break;			
				}
			}
		}
	}
	if ( ( count($volantes) == count($combinacao) ) && ($ok) ){
		$volantes['TIPOS']=$combinacao;
	}else{
		$volantes['TIPOS']=array();
		$volantes['TIPO']='FALHA';
	}
	return $volantes;
}

function adiciona_palpite(&$qt_aux , &$ok, &$dup, &$tri, &$jogo, &$palpite, &$jogo_det){
	$dup=$jogo['DUP'];
	$tri=$jogo['TRI'];
	$max_dup=$jogo['MDUP'];
	$max_tri=$jogo['MTRI'];
	$qt_lin=$jogo['LIN'];
	switch($palpite['somar']){
	case 1:
		$qt_j=1;
		break;
	case 2:
		$qt_j=1;
		break;
	case 3:
		$qt_j=2;
		break;
	case 4:
		$qt_j=1;
		break;
	case 5:
		$qt_j=2;
		break;
	case 6:
		$qt_j=2;
		break;
	case 7:
		$qt_j=3;
		break;
	}
//	error_log("palpite['seq'] " . $palpite['seq']);
	if(!in_array($palpite['somar'] , $jogo_det[$palpite['seq']])){
		if($jogo[$palpite['seq']]==0){
			if($qt_j==1){
				$qt_lin++;
				$ok=true;
				$qt_aux++;
			}else{
				if(($qt_j==2)&&($dup<$max_dup)){
					$qt_lin++;
					$ok=true;
					$dup++;
					$qt_aux++;
					$qt_aux++;
				}else{
					if(($qt_j==3)&&($tri<$max_tri)){
						$qt_lin++;
						$ok=true;
						$tri++;
						$qt_aux++;
						$qt_aux++;
						$qt_aux++;
					}
				}
			}
		}else{
			if(in_array($jogo[$palpite['seq']],array(1,2,4))){
				if(($qt_j==1)&&($dup<$max_dup)){
					$ok=true;
					$dup++;
					$qt_aux++;
				}else{
					if(($qt_j==2)&&($tri<$max_tri)){
						$ok=true;
						$tri++;
						$qt_aux++;
						$qt_aux++;
					}
				}
			}else{
				if(in_array($jogo[$palpite['seq']],array(3,5,6))){
					if(($qt_j==1)&&($tri<$max_tri)){
						$ok=true;
//						error_log('testes xxx ' . $jogo[$palpite['seq']] . 'tri : ' . $tri . ' - max_tri :' . $max_tri );
						$tri++;
						$dup--;
						$qt_aux++;
					}
				}
			}
		}
	}
	$jogo['DUP']=$dup;
	$jogo['TRI']=$tri;	
	$jogo['LIN']=$qt_lin;
}

function completa_zebras(&$palpite_grupo_zebra,&$palpite_grupo_tmp,$max,$jogo_max){
	$qt_zebra=count($palpite_grupo_zebra);
//	error_log('completando zebras . ' . print_r($palpite_grupo_zebra,true));
	$salto=0;
	$saltar=rand(0 , 2);
	foreach(array_reverse($palpite_grupo_tmp) as $grupo){
		if($salto>=$saltar){
			foreach($grupo as $palpite){
				if(($qt_zebra < $max)&&($jogo_max[$palpite['seq']]['MAX']!=$palpite['somar'])){
					$palpite_grupo_zebra[]=$palpite;
					unset($palpite_grupo_tmp[$palpite['peso']][$palpite['key']]);
					if(count($palpite_grupo_tmp[$palpite['peso']])==0){
						unset($palpite_grupo_tmp[$palpite['peso']]);
					}
					$qt_zebra++;
				}
				if($qt_zebra >=$max){
					break;
				}
			}
			if($qt_zebra >=$max){
				break;
			}
		}
		$salto++;
	}
}

function monta_jogo_volante($tipo_volante,&$palpite_grupo,$somente_um,$elimina){
	$palpite_grupo_tmp=$palpite_grupo;
	$qt_aux=0; // quantidade de marcações no volante
	$jogo_det=array( );
	$preparada_dif=false;
	$jogo=array( 1 => 0 , 2 => 0 , 3 => 0 , 4 => 0 , 5 => 0 , 6 => 0 , 7 => 0 , 8 => 0 , 9 => 0 , 10 => 0 , 11 => 0 , 12 => 0 , 13 => 0 , 14 => 0 ,
	    'Z1' => 0 , 'Z2' => 0 ,  'Z3' => 0 ,  'Z4' => 0 ,  'Z5' => 0 ,  'Z6' => 0 ,  'Z7' => 0 , 
		'Z8' => 0 , 'Z9' => 0 , 'Z10' => 0 , 'Z11' => 0 , 'Z12' => 0 , 'Z13' => 0 , 'Z14' => 0 ,
	    'DUP' => 0 , 'TRI' => 0, 'MDUP' => $tipo_volante->duplos , 'MTRI' => $tipo_volante->triplos , 'LIN' => 0);
	foreach(array_keys($jogo) as $seq ){
		if(in_array($seq, array( 1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 ))){
			$jogo_det[$seq]=array();
		}
	}
	$max_aux=$tipo_volante->duplos+($tipo_volante->triplos*2)+14;
	foreach($somente_um as $linha => $somar){
		$jogo[$linha]=$somar;
		$jogo_det[$linha][]=$somar;
		$jogo['LIN']++;
	}
	$tri=0;
	$dup=0;
	$jogo_max=array( );
	foreach($palpite_grupo_tmp as $peso => $grupo){
		foreach($grupo as $key => $palpite){
			$jogo_max[$palpite['seq']][]=$palpite['somar'];
			$jogo_max[$palpite['seq']]['MAX']+=$palpite['somar'];
		}
	}
	$palpite_grupo_zebra=array();
	completa_zebras($palpite_grupo_zebra,$palpite_grupo_tmp,$tipo_volante->MAX_ZEB,$jogo_max);
	$zebrou=false;
	$qt_zebra=0;
	$array_tmp=array_keys($palpite_grupo_tmp);
	$jogo_max=array( );
	foreach($palpite_grupo_tmp as $peso => $grupo){
		foreach($grupo as $key => $palpite){
			$jogo_max[$palpite['seq']][]=$palpite['somar'];
			$jogo_max[$palpite['seq']]['MAX']+=$palpite['somar'];
		}
	}
	foreach($palpite_grupo_tmp as $peso => $palpites){
		foreach($palpites as $key => $palpite){
			if(isset($palpite['EXTRA'])){
				unset($palpite_grupo_tmp[$peso][$key]);
				if(count($palpite_grupo_tmp[$peso])==0){
					unset($palpite_grupo_tmp[$peso]);
				}
				
			}
		}
	}
	$quando_eliminar=rand(7 , 14);
	$quando_zebrar=rand(0 , 14);
//	error_log('$palpite_grupo_zebra: ' . print_r($palpite_grupo_zebra,true));
//	error_log('$jogo_max: ' . print_r($jogo_max,true));
	foreach($array_tmp as $peso){
		$grupo=$palpite_grupo_tmp[$peso];
//		error_log('palpite_grupo ' . print_r($grupo,true));
		foreach($grupo as $key => $palpite_1){
			$ok=false;
			adiciona_palpite( $qt_aux , $ok , $dup , $tri , $jogo , $palpite_1 , $jogo_det );
//			error_log('$jogo[LIN]' . $jogo['LIN'] . ' ' . $jogo['DUP'] . ' ' . $jogo['TRI'] . ' ' . $ok);
			if($ok==true){
				$jogo[$palpite_1['seq']]+=$palpite_1['somar'];
// ------------------------------------------------------------------------------------------------------------------------------
//	RETIRAR O JOGO PARA O PROXIMO PALPITE - INICIO
				if($elimina==true){
					if($preparada_dif){
						if($eliminado_na_linha==$palpite_1['seq']){
							unset($palpite_grupo[$peso][$key]);
							if(count($palpite_grupo[$peso])==0){
								unset($palpite_grupo[$peso]);
							}
						}
					}else{
						if(($jogo[$palpite_1['seq']]!=$jogo_max[$palpite_1['seq']]['MAX'])&&($jogo['LIN']>$quando_eliminar)){
							foreach($palpite_grupo_tmp as $x => $y){
								foreach($y as $k => $z){
									if(($peso>$x)&&($peso<=$x+15)&&($key>$k)&&($palpite_1['somar']!=$z['somar'])&&($palpite_1['seq']==$z['seq'])){
//										error_log("eliminando para o proximo ... \n " . '$palpite_1: ' . print_r($palpite_1,true) . 
//										          "\n " . '$palpite_grupo[' . $peso . '][' . $key . ']:' . print_r($palpite_grupo[$peso][$key],true) .
//										          "\n " .'$z:' . print_r($z,true));
										unset($palpite_grupo[$peso][$key]);
										if(count($palpite_grupo[$peso])==0){
											unset($palpite_grupo[$peso]);
										}
										$preparada_dif=true;
										$eliminado_na_linha=$palpite_1['seq'];
										break;
									}
								}
								if($preparada_dif){break;}
							}
						}
					}
				}
//	RETIRAR O JOGO PARA O PROXIMO PALPITE - FIM
// ------------------------------------------------------------------------------------------------------------------------------
				$jogo_det[$palpite_1['seq']][]=$palpite_1['somar'];
// ------------------------------------------------------------------------------------------------------------------------------
// TENTA INCLUIR UMA ZEBRA - INICIO
				if(((!$zebrou)&&($jogo['LIN']>=$quando_zebrar))||
				   ((count($palpite_grupo_tmp)==0)&&($qt_zebra<$tipo_volante->MAX_ZEB))){
					for($zeb=0;(($zeb<$tipo_volante->MIN_ZEB)&&(count($palpite_grupo_zebra)>0));$zeb++){
						$zebra=rand(0,count($palpite_grupo_zebra)-1);
						$ok=false;
						adiciona_palpite($qt_aux , $ok, $dup, $tri, $jogo, $palpite_grupo_zebra[$zebra], $jogo_det);
						if($ok==true){
							$jogo[$palpite_grupo_zebra[$zebra]['seq']]+=$palpite_grupo_zebra[$zebra]['somar'];
							$jogo['Z'.$palpite_grupo_zebra[$zebra]['seq']]+=$palpite_grupo_zebra[$zebra]['somar'];
							$jogo_det[$palpite_grupo_zebra[$zebra]['seq']][]=$palpite_grupo_zebra[$zebra]['somar'];
							unset($palpite_grupo[$palpite_grupo_zebra[$zebra]['peso']][$palpite_grupo_zebra[$zebra]['key']]);
							if(count($palpite_grupo[$palpite_grupo_zebra[$zebra]['peso']])==0){
								unset($palpite_grupo[$palpite_grupo_zebra[$zebra]['peso']]);
							}
							unset($palpite_grupo_zebra[$zebra]);
							if(count($palpite_grupo_zebra[$zebra])==0){
								unset($palpite_grupo_zebra[$zebra]);
							}
							$palpite_grupo_zebra=array_values($palpite_grupo_zebra);
							$qt_zebra++;
							if($qt_zebra>=$tipo_volante->MIN_ZEB){
								$zebrou=true;
							}
						}
					}
				}
// TENTA INCLUIR UMA ZEBRA - FIM
// ------------------------------------------------------------------------------------------------------------------------------
			}else{
// ------------------------------------------------------------------------------------------------------------------------------
// COLOCA O PALPITE NÃO UTILIZADO PARA OS PRÓXIMOS GRUPOS DE PESO/PALPITE - INICIO
				$ok=false;
				for($peso_1=$peso;($peso_1==0||$ok);$peso_1--){
					if(isset($array_tmp[$peso_1])){
						$ok=true;
						$peso_a=$peso_1;
					}
				}
				$palpite_grupo_tmp[$peso_a][$key]=$palpite_grupo_tmp[$peso][$key];
				$palpite_grupo_tmp[$peso_a][$key]['EXTRA']=1;
				foreach($palpite_grupo_tmp as $peso_a => $palpites_a){
					foreach($palpites_a as $key_a => $palpite_a){
						if(($palpite_a['seq']==$palpite_1['seq'])&&($palpite_a['key']!=$palpite_1['key'])&&(!isset($palpite_a['EXTRA']))){
							$palpite_1['somar']+=$palpite_a['somar'];
							$palpite_1['EXTRA']=1;
							$palpite_grupo_tmp[$peso_a][$palpite_a['key'].$palpite_1['key']]=$palpite_1;
							unset($palpite_grupo_tmp[$peso][$key]);
							if(count($palpite_grupo_tmp[$peso])==0){
								unset($palpite_grupo_tmp[$peso]);
							}
							
						}
					}
				}
// COLOCA O PALPITE NÃO UTILIZADO PARA OS PRÓXIMOS GRUPOS DE PESO/PALPITE - FIM
// ------------------------------------------------------------------------------------------------------------------------------
			}
		}
// ------------------------------------------------------------------------------------------------------------------------------
// SAI DO LOOP QUANDO O VOLANTE ESTÁ PRONTO - INICIO
		if(($jogo['LIN']==14)&&($jogo['DUP']==$jogo['MDUP'])&&($jogo['TRI']==$jogo['MTRI'])){
//			error_log('JOGO XXX ' . print_r($jogo,true));
			break;
		}
// SAI DO LOOP QUANDO O VOLANTE ESTÁ PRONTO - FIM
// ------------------------------------------------------------------------------------------------------------------------------
	}
	return $jogo;
}
?>