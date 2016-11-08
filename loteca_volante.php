<?php
function loteca_imprime_volante_pdf($grupo,$rodada,$jogo,$qt_cotas_aposta){
	if(!isset($grupo)){exit();}
	include_once 'loteca_functions.php';
	include_once 'loteca_db_functions.php';
	$dadosgruporodada=dadosgruporodada($grupo, 1, $rodada);
	setlocale(LC_CTYPE, 'pt_BR');
	header("Content-type:application/pdf",true,200);
	header('Cache-Control: public');
	// ponto de referencia inicial
	$esquerda=13;
	$topo=25.1;
	// ajustes
	$ajuste_topo=$dadosgruporodada->vl_ajuste_topo_volante;
	$ajuste_esquerda=$dadosgruporodada->vl_ajuste_esqu_volante;
//	error_log('$dadosgruporodada->vl_ajuste_topo_volante' . $dadosgruporodada->vl_ajuste_topo_volante . ' / $dadosgruporodada->vl_ajuste_esqu_volante: ' . $dadosgruporodada->vl_ajuste_esqu_volante);
	$esquerda+=$ajuste_esquerda;
	$topo+=$ajuste_topo;
	// tamanho da imagem
	$altura=540;
	$largura=320;
	// linhas e colunas
	$cols =array(  0   , 25   , 50.2 , 56.8 , 63   );
	$lins =array(  4.5 ,  9.3 , 14.3 , 19.1   , 23.8   , 
	              28.6   , 33.4   , 38.1   , 43   , 47.6 , 
				  52.3 , 57.1   , 61.6   , 65.7   );
	foreach($cols  as $key => $col){
		$cols[$key] =$col+$esquerda;
	}
	foreach($lins  as $key => $lin){
		$lins[$key] =$lin+$topo;
	}
	
	$cols2=array(   0 ,  6 ,  12.3 ,  18.5 ,   24.7 , 30.8 , 37 , 44.1 , 50.5 , 56.6 );
	$lins2=array( 75.7 , 79.4 );
	foreach($cols2 as $key => $col){
		$cols2[$key]=$col+$esquerda;
	}
	foreach($lins2 as $key => $lin){
		$lins2[$key]=$lin+$topo;
	}
	// altura e largura dos retangulos
	$h_ret=1.2;
	$w_ret=2;
	

//header("Content-type:application/pdf");
	require_once('tcpdf_min/tcpdf.php');
	                                           
// create new PDF document
 $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(138 , 82), true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Bolão Loteca - Avançado');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(0 , 0 , 0 , 0);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
$pdf->SetFont('helvetica', '', 5, '', true);

// set text shadow effect
// $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

	$pdf->setImageScale(200);

	$dados_grupo=dadosgrupo($grupo);
	$jogos=loteca_jogos($rodada);
//	$cotas=$dadosgruporodada->qt_cotas;
	$cotas=$qt_cotas_aposta;
	$rateio=$dadosgruporodada->ind_bolao_volante;
	$qt_jogos=count($jogo);
	foreach($jogo as $seq => $volante){
		$pdf->AddPage();
		$tipos=array_count_values($volante);
		$duplos=$tipos[3]+$tipos[5]+$tipos[6];
		$triplos=$tipos[7];
		$valor=loteca_valor_volante($duplos,$triplos);
		$pdf->SetFont('courier', '', 11, '', true);
		$pdf->Text($esquerda , $topo - 21, '.'.substr($dados_grupo->nm_grupo,0,30).'.');
		$pdf->Text($esquerda+0.2 , $topo - 21, '.'.substr($dados_grupo->nm_grupo,0,30).'.');
		$pdf->SetFont('courier', '', 10, '', true);
		if($rateio){
			$pdf->Text($esquerda , $topo - 17, sprintf('      CONCURSO %4d - COTAS %2d',$rodada,$cotas));
			$pdf->Text($esquerda+0.2 , $topo - 17, sprintf('      CONCURSO %4d - COTAS %2d',$rodada,$cotas));
		}else{
			$pdf->Text($esquerda , $topo - 17, sprintf('                 CONCURSO %4d',$rodada));
			$pdf->Text($esquerda+0.2 , $topo - 17, sprintf('                 CONCURSO %4d',$rodada));
		}
		$pdf->Text($esquerda , $topo - 14, sprintf(' VOLANTE %02d DE %02d - R$ %\' 7.2f' , $seq , $qt_jogos , $valor));
		$pdf->Text($esquerda+0.2 , $topo - 14, sprintf(' VOLANTE %02d DE %02d - R$ %\' 7.2f' , $seq , $qt_jogos , $valor));
		$pdf->SetFont('courier', '', 5.5, '', true);
		// escolha dos resultados
		for($col=0;$col<5;$col++){
			for($lin=0;$lin<14;$lin++){
				if(($col==0&&($volante[$lin+1]==1||$volante[$lin+1]==3||$volante[$lin+1]==5||$volante[$lin+1]==7))||
				   ($col==1&&($volante[$lin+1]==2||$volante[$lin+1]==3||$volante[$lin+1]==6||$volante[$lin+1]==7))||
				   ($col==2&&($volante[$lin+1]==4||$volante[$lin+1]==5||$volante[$lin+1]==6||$volante[$lin+1]==7))||
				   ($col==3&&($volante[$lin+1]==3||$volante[$lin+1]==5||$volante[$lin+1]==6))||
				   ($col==4&&($volante[$lin+1]==7))
				   ){
					$pdf->SetDrawColor( 0 , 0 , 0);
					$pdf->Ellipse($cols[$col] + ($w_ret / 2) , $lins[$lin] + ($h_ret / 2) , $w_ret , $h_ret, 0 , 0 , 360 , 'DF' , array() , array( 0 , 0 , 0 ));
				}
				switch ($col){
					case 0:
						$time=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['time1']);
						$time=$jogos[$lin]['time1'];
						if(strlen($time)>17){
							$time=substr($time, 0 , 11) . substr($time,-3,3);
						}
						$info=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['data']);
						$pdf->Text($cols[$col] + $w_ret + 1.5 , $lins[$lin] - 1.3, $time);
						$pdf->Text($cols[$col] + $w_ret + 1.5 , $lins[$lin] + 0.5, $info);
						break;
					case 1:
						$time=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['time2']);
						$time=$jogos[$lin]['time2'];
						if(strlen($time)>17){
							$time=substr($time, 0 , 8) . substr($time,-3,3);
						}
						$info=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['dia']);
						$pdf->Text($cols[$col] + $w_ret + 1.6 , $lins[$lin] - 1.3, $time);
						$pdf->Text($cols[$col] + $w_ret + 1.6 , $lins[$lin] + 0.5, $info);
						break;
				}
			}
		}
		
		//escolha do rateio no bolao
		if(($rateio)&&($cotas>0)){
			for($col=0;$col<10;$col++){
				for($lin=0;$lin<2;$lin++){
					if(($lin==1)||($col<5)){
						$unidade=$cotas % 10;
						$dezena=($cotas - $unidade)/10;
						if(($lin==0&&($col==$dezena-1))||
						($lin==1&&($col==$unidade))
						){
							$pdf->SetDrawColor( 0 , 0 , 0);
							$pdf->Ellipse($cols2[$col] + ($w_ret / 2) , $lins2[$lin] + ($h_ret / 2) , $w_ret , $h_ret , 0 , 0 , 360 , 'DF' , array() , array( 0 , 0 , 0 ));
						}
					}
				}
			}
		}
	}

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.

//$pdf->Output('impressao_volante.pdf', 'I');
$pdf->Output(iconv('UTF-8','ASCII//TRANSLIT',$dados_grupo->nm_grupo) . '_RODADA_' . $rodada .'.pdf', 'I');
}

function loteca_imprime_volante($grupo,$rodada,$jogo){
	setlocale(LC_CTYPE, 'pt_BR');
	// ponto de referencia inicial
	$esquerda=45;
	$topo=94;
	// ajustes
	$ajuste_topo=-3;
	$ajuste_esquerda=0;
	// tamanho da imagem
	$altura=540;
	$largura=320;
	// linhas e colunas
	$cols =array(   0 ,  96 , 193 , 217 , 240 );
	$lins =array(  18 ,  36 ,  54 ,  73 ,  91 , 
	              109 , 127 , 145 , 163 , 181 , 
				  199 , 217 , 235 , 251 );
	foreach($cols  as $key => $col){
		$cols[$key] =$col+$esquerda;
	}
	foreach($lins  as $key => $lin){
		$lins[$key] =$lin+$topo;
	}
	
	$cols2=array(   0 ,  26 ,  50 ,  74 ,   98 , 122 , 145 , 170 , 194 , 217 );
	$lins2=array( 287 , 301 );
	foreach($cols2 as $key => $col){
		$cols2[$key]=$col+$esquerda;
	}
	foreach($lins2 as $key => $lin){
		$lins2[$key]=$lin+$topo;
	}
	// altura e largura dos retangulos
	$h_ret=7;
	$w_ret=12;
	
	header("Content-Type: image/png");
	// ajustando
	$topo=$topo+$ajuste_topo;
	$esquerda=$esquerda+$ajuste_esquerda;
	// criando figura
	$im = @imagecreate($largura, $altura) // 82mm x 138mm / 0,225
		or die("Cannot Initialize new GD image stream");
	$white = imagecolorallocate($im, 255, 255, 255);
	$black=imagecolorallocate($im, 0, 0, 0);
	$blue=imagecolorallocate($im, 100, 100, 255);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	imagerectangle($im, 1 , 1 , $largura - 1 , $altura - 1 , $black);
	// cabeçalho
	include_once 'loteca_functions.php';
	include_once 'loteca_db_functions.php';
	$dados_grupo=dadosgrupo($grupo);
	$jogos=loteca_jogos($rodada);
	$dadosgruporodada=dadosgruporodada($grupo, 1, $rodada);
	$cotas=$dadosgruporodada->qt_cotas;
	foreach($jogo as $seq => $volante){
		$tipos=array_count_values($volante);
		$duplos=$tipos[3]+$tipos[5]+$tipos[6];
		$triplos=$tipos[7];
		$valor=loteca_valor_volante($duplos,$triplos);
		imagestring($im, 3, $esquerda, $topo - 75 , substr(iconv('UTF-8','ASCII//TRANSLIT',$dados_grupo->nm_grupo),0,30), $black);
		imagestring($im, 3, $esquerda, $topo - 60 , sprintf('                 CONCURSO %4d',$rodada) , $black);
		imagestring($im, 3, $esquerda, $topo - 45 , sprintf('        VOLANTE %02d - R$ %3.2f' , $seq , $valor), $black);
		
		// escolha dos resultados
		for($col=0;$col<5;$col++){
			for($lin=0;$lin<14;$lin++){
				if(($col==0&&($volante[$lin+1]==1||$volante[$lin+1]==3||$volante[$lin+1]==5||$volante[$lin+1]==7))||
				   ($col==1&&($volante[$lin+1]==2||$volante[$lin+1]==3||$volante[$lin+1]==6||$volante[$lin+1]==7))||
				   ($col==2&&($volante[$lin+1]==4||$volante[$lin+1]==5||$volante[$lin+1]==6||$volante[$lin+1]==7))||
				   ($col==3&&($volante[$lin+1]==3||$volante[$lin+1]==5||$volante[$lin+1]==6))||
				   ($col==4&&($volante[$lin+1]==7))
				   ){
//					imagerectangle($im, $cols[$col] , $lins[$lin] , $cols[$col] + $w_ret , $lins[$lin] + $h_ret , $black);
//					imagefill($im, $cols[$col] + 1, $lins[$lin] + 1, $black);
					imageellipse($im, $cols[$col] + ($w_ret / 2) , $lins[$lin] + ($h_ret / 2) , $w_ret , $h_ret , $black);
					imagefill($im, $cols[$col] + ($w_ret / 2), $lins[$lin] + ($h_ret / 2), $black);
				}
				switch ($col){
					case 0:
						$time=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['time1']);
						if(strlen($time)>14){
							$time=substr($time, 0 , 11) . substr($time,-3,3);
						}
						$info=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['data']);
						imagestring($im, 1, $cols[$col] + $w_ret + 8 , $lins[$lin] - 3 , $time , $text_color);
						imagestring($im, 1, $cols[$col] + $w_ret + 8 , $lins[$lin] + 4 , $info , $blue);
						break;
					case 1:
						$time=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['time2']);
						if(strlen($time)>14){
							$time=substr($time, 0 , 8) . substr($time,-3,3);
						}
						$info=iconv('UTF-8','ASCII//TRANSLIT',$jogos[$lin]['dia']);
						imagestring($im, 1, $cols[$col] + $w_ret + 8 , $lins[$lin] - 3 , $time , $text_color);
						imagestring($im, 1, $cols[$col] + $w_ret + 8 , $lins[$lin] + 4 , $info , $blue);
						break;
				}
			}
		}
		
		//escolha do rateio no bolao
		for($col=0;$col<10;$col++){
			for($lin=0;$lin<2;$lin++){
				if(($lin==1)||($col<5)){
					$unidade=$cotas % 10;
					$dezena=($cotas - $unidade)/10;
					if(($lin==0&&($col==$dezena-1))||
					   ($lin==1&&($col==$unidade))
					   ){
						imageellipse($im, $cols2[$col] + ($w_ret / 2) , $lins2[$lin] + ($h_ret / 2) , $w_ret , $h_ret , $black);
						imagefill($im, $cols2[$col] + ($w_ret / 2), $lins2[$lin] + ($h_ret / 2), $black);
					}
				}
			}
		}
		
		imagepng($im);
		//imagejpeg($im, null, 100);
		imagedestroy($im);
		break;
	}
}
?>