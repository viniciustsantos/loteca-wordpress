function generateXMLHttp() {
	if (typeof XMLHttpRequest != "undefined"){
		return new XMLHttpRequest();
	}
	else{	
	 	if (window.ActiveXObject){
			var versions = ["MSXML2.XMLHttp.5.0", 
		                 "MSXML2.XMLHttp.4.0", 
                                 "MSXML2.XMLHttp.3.0",
		                 "MSXML2.XMLHttp", 
		                 "Microsoft.XMLHttp"
		               		];
		}
	}
	for (var i=0; i < versions.length; i++){
		try{
			return new ActiveXObject(versions[i]);
		}catch(e){}
	}
	alert('Seu navegador não pode trabalhar com Ajax!');
}

/* XMLHttp.onreadystatechange - funcao para monitorar a propriedade readyState */
/* XMLHttp.readyState: 
	0 (não iniciado): o objeto já foi criado mas o método open() não foi invocado até o momento;
	1 (carregando): o método open() já foi invocado mas a requisição ainda não foi enviada;
	2 (carregado): a requisição foi enviada;
	3 (incompleto): apenas uma parte da resposta do servidor foi recebida;
	4 (completo): todas as informações foram recebidas e a conexão foi encerrada com sucesso.

   XMLHttp.status:
	200 (OK): arquivo encontrado com sucesso;
	304 (NOT MODIFIED): o arquivo não foi modificado desde o ultimo request;
	401 (UNAUTHORIZED): cliente não tem autorização para acessar o arquivo;
	403 (FORBIDDEN): cliente falhou na autorização;
	404 (NOT FOUND): quando o arquivo não existe na localização informada.

   XMLHttp.responseText: É esse retorno que deve ser manipulado para exibir o resultado desejado.
   XMLHttp.statusText: Esse é o texto de retorno do status, normalmente utilizado para saber qual foi o erro.
   XMLHttp.send: Envia a requisição

	*/