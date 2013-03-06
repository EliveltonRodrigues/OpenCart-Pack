<?php
		
	#########################################################
	#
	#
	#    Autor: -=VaLdEiR PsR=-
	#    URL: http://www.razekproducoes.com.br/valdeir/
	#    EMAIL: valdeir_webdesign@hotmail.com
	#    Data: 21/03/2012
	#    Hora: 01:03:13
	#    Fun��o: Adiciona o bot�o Calcula Frete
	#    
	#    
	#########################################################
	
	//Adiciona as v�riaveis do arquivo config
	require_once '../config.php';
	
	//Inicia o startup
	require_once DIR_SYSTEM . 'startup.php';
	
	//Conecta ao banco de dados
	$db = new db(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	
	//Intancia a library Session
	$session = new Session();
	
	//Captura os dados do m�dulo dos Correios
	$config_correios = $db->query('SELECT * FROM ' . DB_PREFIX . 'setting WHERE `group` = "correios" ORDER BY setting_id ASC');
	
	//Salva o CEP para preencher na p�gina do carrinho
	$session->data['shipping_postcode']   = $_POST['cepDestino'];
	
	//Variaveis
	//Seu c�digo administrativo junto � ECT. O c�digo est� dispon�vel no corpo do contrato firmado com os Correios. (opcional)
	$nCdEmpresa = "";
	//Senha para acesso ao servi�o, associada ao seu c�digo administrativo.  (opcional)
	$sDsSenha = "";
	//CEP de Origem  sem h�fen.Exemplo: 05311900 
	$sCepOrigem=$config_correios->rows['0']['value'];
	//CEP de Destino sem h�fen 
	$sCepDestino=$_POST['cepDestino'];
	//Peso da encomenda, incluindo sua embalagem. O peso deve ser informado em quilogramas. Se o formato for Envelope, o valor m�ximo permitido ser� 1 KG
	$nVlPeso=$_POST['pesoProduto'];
	//Formato da encomenda (incluindo embalagem). Valores poss�veis: 1 � Formato caixa/pacote | 2 � Formato rolo/prisma  | 3 - Envelope
	$nCdFormato="1";
	//Comprimento da encomenda (incluindo embalagem), em cent�metros.
	$nVlComprimento=$_POST['comprimentoProduto'];
	//Altura da encomenda (incluindo embalagem), em cent�metros. Se o formato for envelope, informar zero (0). 
	$nVlAltura=$_POST['alturaProduto'];
	//Largura da encomenda (incluindo embalagem), em cent�metros. 
	$nVlLargura=$_POST['larguraProduto'];
	//Indica se a en comenda ser� entregue com o servi�o adicional m�o pr�pria. Valores poss�veis: S = SIM | N = N�O
	$sCdMaoPropria=$config_correios->rows['5']['value'];
	//Indica se a encomenda ser� entregue com o servi�o adicional valor declarado. Neste campo deve ser apresentado o valor declarado desejado, em Reais. 
	$nVlValorDeclarado=$config_correios->rows['14']['value'];
	//Indica se a  encomenda ser� entregue com o servi�o adicional aviso de recebimento. Valores poss�veis: S ou N (S � Sim, N � N�o) 
	$sCdAvisoRecebimento=$config_correios->rows['6']['value'];
	//C�digo do servi�o:
	/********************************************
	'                   TABELA
	'********************************************
	'C�digo   |  Servi�o 
	_______|_______________
	'40010   |   SEDEX sem contrato 
	'40045   |   SEDEX a Cobrar, sem contrato 
	'40126   |   SEDEX a Cobrar, com contrato 
	'40215   |   SEDEX 10, sem contrato 
	'40290   |   SEDEX Hoje, sem contrato 
	'40096   |   SEDEX com contrato 
	'40436   |   SEDEX com contrato 
	'40444   |   SEDEX com contrato 
	'40568   |   SEDEX com contrato 
	'40606   |   SEDEX com contrato 
	'41106   |   PAC sem contrato 
	'41068   |   PAC com contrato 
	'81019   |   e-SEDEX, com contrato 
	'81027   |   e-SEDEX Priorit�rio, com conrato 
	'81035   |   e-SEDEX Express, com contrato 
	'81868   |   (Grupo 1) e-SEDEX, com contrato 
	'81833   |   (Grupo 2) e-SEDEX, com contrato 
	'81850   |   (Grupo 3) e-SEDEX, com contrato  
	
	********************************************/
	$nCdServico = array();
	foreach ($config_correios->rows as $key => $value):
		if ($value['key'] == 'correios_40010')
			$nCdServico[] = '40010';
		
		if ($value['key'] == 'correios_41106')
			$nCdServico[] = '41106';
			
	endforeach;
	
	//Di�metro da encomenda (incluindo embalagem), em cent�metros.
	$nVlDiametro="0";
	//Indica a forma de retorno da consulta. 
	//XML     ->  Resultado em XML 
	//Popup  ->  Resultado em uma janela popup 
	//<URL>  ->  Resultado via post em uma p�gina do requisitante
	$StrRetorno="xml";
	
	
	//Url do correios que calcula o valor do frete
	$url = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa='.$nCdEmpresa.'&sDsSenha='.$sDsSenha.'&sCepOrigem='.$sCepOrigem.'&sCepDestino='.$sCepDestino.'&nVlPeso='.$nVlPeso."&nCdFormato=".$nCdFormato."&nVlComprimento=".$nVlComprimento."&nVlAltura=".$nVlAltura."&nVlLargura=".$nVlLargura."&sCdMaoPropria=".$sCdMaoPropria."&nVlValorDeclarado=".$nVlValorDeclarado."&sCdAvisoRecebimento=".$sCdAvisoRecebimento."&nCdServico=".implode(',',$nCdServico)."&nVlDiametro=".$nVlDiametro."&StrRetorno=".$StrRetorno;
	
	//Pega os dados no formato XML
	$xml = simplexml_load_file($url); 

	//Variaveis Extras
	$sedex_codigo = "";
	//Valor do Frete via Sedex
	$sedex_valor = "";
	//Tempo de Entrega via Sedex
	$sedex_entrega = "";
	$pac_codigo ="";
	//Valor do Frete via PAC
	$pac_valor = "";
	//Tempo de Entrega via PAC
	$pac_entrega = "";
	//Captura Valor do Produto
	$precoProduto = $_POST['precoProduto'];
	//Instancia um novo objeto
	$calcularFrete = new CalculaFrete;
	//Dia Adicional
	$diaUtil = $config_correios->rows['9']['value'];
	//Valor Adicional
	$valorAdicional = $config_correios->rows['8']['value'];
	
	//Repeti��o para capturar os dados
	foreach($xml->cServico as $child){
		//Verifica se o Codigo capturado � do envio via SEDEX se for:
		if ($child->Codigo == "40010") {
			$sedex_codigo = $child->Codigo;
			//Armazena o valor na variavel $sedex_valor
			$sedex_valor = $calcularFrete->formatMoney($calcularFrete->valorSobreFrete($valorAdicional, $child->Valor));
			//Armazena o Prazo da Entrega na variavel $sedex_entrega
			$sedex_entrega = $child->PrazoEntrega + $diaUtil;
			//Verifica se o Codigo capturado � do envio via PAC se for:
		}else if ($child->Codigo == "41106") {
			$pac_codigo = $child->Codigo;
			//Armazena o valor na variavel $pac_valor
			$pac_valor = $calcularFrete->formatMoney($calcularFrete->valorSobreFrete($valorAdicional, $child->Valor));
			//Armazena o Prazo da Entrega na variavel $pac_entrega
			$pac_entrega = $child->PrazoEntrega + $diaUtil;
		}
	}
	
	//Gera JSON
	echo json_encode(array("valor_sedex" => $calcularFrete->formatMoney($sedex_valor),
										"entrega_sedex" => utf8_encode($sedex_entrega),
										"valor_pac" => $calcularFrete->formatMoney($pac_valor),
										"entrega_pac" => utf8_encode($pac_entrega),
										"sedex_codigo" => utf8_encode($sedex_codigo),
										"pac_codigo" => utf8_encode($pac_codigo)));

	
	class CalculaFrete {
	
		public function valorSobreFrete($porcentagem, $valorFrete) {
			$porcentagem = $porcentagem / 100.0;
			$valorFrete = str_replace(',','.',$valorFrete);
			return $valorFrete + ($porcentagem * $valorFrete);
		}
		
		public function formatMoney($number, $fractional=false) { 
			if ($fractional) { 
				return sprintf('%.2f', $number); 
			}else{
				return number_format(str_replace(',', '.', $number), 2, ',', '.');
			}
		}
		
	}
		
?>