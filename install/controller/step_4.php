<?php
############################################
# Autor: Valdeir Santana
# Email: valdeirpsr@hotmail.com.br
# Site: http://www.valdeirsantana.com.br
############################################
class ControllerStep4 extends Controller {

	public function index() {
		
		//Deleta os dados da session shipping
		unset($this->session->data['shipping']);
		
		$this->data['extensions'] = array();
		
		//Aqruivos n�o exibidos
		$fileNotPermited = array(
			'auspost',
			'citylink',
			'fedex',
			'parcelforce_48',
			'pickup',
			'royal_mail',
			'ups',
			'usps',
			'weight',
		);
		
		//Carrega todos os arquivos .php da pasta shipping
		$files = glob(DIR_ROOT . 'admin/controller/shipping/*.php');
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				//Instancia o idioma portuguese-br
				$language = new Language('../../admin/language/portuguese-br');
				
				//Carrega a linguagem do m�dulo
				$language->load('shipping/' . $extension);
	
				$action = array();
				
				//Verifica se o arquivo est� permitido para ser exibido
				if (!in_array($extension, $fileNotPermited)){
					//Armazena o t�tulo da extens�o e o nome do arquivo
					$this->data['extensions'][] = array(
						'name'		=> $language->get('heading_title'),
						'extension'	=> $extension
					);
				}
			}
		}
		
		//Link - Voltar a P�gina Anterior
		$this->data['back'] = $this->url->link('step_3');
	
		$this->template = 'step_4.tpl';
		$this->children = array(
			'header',
			'footer'
		);
		
		$this->response->setOutput($this->render());
		
		//Verifica se existe um m�todo POST
		if ($this->request->server['REQUEST_METHOD'] == 'POST'){
			//Carrega a fun��o configure()
			$this->configure();
		}
		
	}
	
	//Fun��o respons�vel por instalar o m�dulo e definir as permiss�es de acesso
	public function configure(){
		
		//Verifica se o POST enviado cont�m o campo shipping
		if (isset($this->request->post['shipping'])){
			//Salva os dados do campo shipping na session
			$this->session->data = $this->request->post;
		}
		
		//Verifica se existe o m�todo POST e se existe o par�metro NEXT na url
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->get['next'])){
			//Carrega o model setting
			$this->load->model('setting');
			//Verifica se o m�dulo possui arquivo .sql
			if (file_exists(DIR_APPLICATION . 'sql/payment/' . current($this->session->data['shipping']) . '.sql'))
				//Executa arquivo .sql do m�dulo caso haja
				$this->model_setting->mysql(DIR_APPLICATION . 'sql/payment/' . current($this->session->data['shipping']) . '.sql');
			//Salva as configura��es do m�dulo
			$this->model_setting->editSetting(current($this->session->data['shipping']), $this->request->post);
			//Instala o m�dulo
			$this->model_setting->install('shipping', current($this->session->data['shipping']));
			//Define a permiss�o de acesso
			$this->model_setting->addPermission('access', 'shipping/' . current($this->session->data['shipping']));
			//Define a permiss�o de modifica��o
			$this->model_setting->addPermission('modify', 'shipping/' . current($this->session->data['shipping']));
			//Deleta o primeiro �ndice do array
			array_shift($this->session->data['shipping']);
		}
		
		//Verifica se a session est� v�zia,
		if (!empty($this->session->data['shipping'])){
			//Caso n�o esteja vazia, carrega o layout do m�dulo escolhido
			$this->template = 'shipping/' . current($this->session->data['shipping']) . '.tpl';
			$this->children = array(
				'header',
				'footer'
			);
			$this->response->setOutput($this->render());
		}else{
			//Caso esteja vazio redireciona para a pr�xima etapa
			$this->redirect($this->url->link('step_5'));
		}
	}
}
?>