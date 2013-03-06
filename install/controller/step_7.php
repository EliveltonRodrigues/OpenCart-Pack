<?php
############################################
# Autor: Valdeir Santana
# Email: valdeirpsr@hotmail.com.br
# Site: http://www.valdeirsantana.com.br
############################################
class ControllerStep7 extends Controller{
	
	public function index(){
		
		//Deleta os dados da session vqmod
		unset($this->session->data['vqmod']);
		
		$this->data['extensions'] = array();
		
		//Arquivos n�o permitidos
		$fileNotPermited = array(
			'vqmod_opencart'
		);
		
		//Carrega todos os arquivos .xml da pasta vqmod/xml
		$files = glob(DIR_ROOT . 'vqmod/xml/*');
		
		if ($files){
			
			//Organiza na ordem Crescente
			array_multisort($files, SORT_ASC);
			
			foreach ($files as $file){
				
				//Captura informa��es do arquivo
				$extensions = pathinfo($file);
				
				//Captura o conte�do do arquivo
				$handle = file_get_contents($file);
				
				//Transforma o conte�do do arquivo em XML
				$xml = new SimpleXMLElement($handle);
				
				//Verifica se o arquivo est� permitido para ser exibido
				if (!in_array($extensions, $fileNotPermited)){
					//Armazena as informa��es do VQMod
					$this->data['extensions'][] = array(
						'name' 		=> $xml->id,
						'version' 	=> $xml->version,
						'author'	=> $xml->author,
						'basename'	=> $extensions['filename'],
						'actived'	=> ($extensions['extension'] == 'xml') ? 1 : 0
					);
				}
			}
		}
		
		//Link - Voltar a P�gina Anterior
		$this->data['back'] = $this->url->link('step_6');
		
		$this->template = 'step_7.tpl';
		$this->children = array(
			'header',
			'footer'
		);
		
		$this->response->setOutput($this->render());
		
	}
	
	public function removeVqmod(){
		
		$file = $this->request->get['vqmod'];
		
		if (file_exists(DIR_ROOT . 'vqmod/xml/' . $file . '.xml')){
			rename(DIR_ROOT . 'vqmod/xml/' . $file . '.xml', DIR_ROOT . 'vqmod/xml/' . $file . '.disabled');
		}
		
	}
	
	public function addVqmod(){
		
		$file = $this->request->get['vqmod'];
		
		echo DIR_ROOT . 'vqmod/xml/' . $file . '.disabled';
		if (file_exists(DIR_ROOT . 'vqmod/xml/' . $file . '.disabled')){
			rename(DIR_ROOT . 'vqmod/xml/' . $file . '.disabled', DIR_ROOT . 'vqmod/xml/' . $file . '.xml');
		}
		
	}
	
}








