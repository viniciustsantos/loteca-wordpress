//(function ($) {
jQuery(document).ready(function($){
	// http://www.stats.betradar.com/s4/?clientid=1351#2_1,3_13,22_3,5_12825,9_headtohead,7_1976,178_1977
	// http://www.caixa.gov.br/estatisticas-futebol-loterias-caixa/loteca1
	// jQuery methods go here...
//	tem=$(document).hasClass('stats-loteca');
//	if(tem){
	if($(".stats-loteca").length){
		texto=jQuery.get('http://loterias.caixa.gov.br/wps/portal/loterias/landing/loteca/programacao',function(){},'jsonp');
		texto=jQuery.get('http://www.caixa.gov.br/estatisticas-futebol-loterias-caixa/loteca1',function(){},'jsonp');
	}
});
