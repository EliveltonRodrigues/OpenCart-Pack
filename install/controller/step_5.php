<?php
############################################
# Autor: Valdeir Santana
# Email: valdeirpsr@hotmail.com.br
# Site: http://www.valdeirsantana.com.br
############################################
class ControllerStep5 extends Controller{

	public function index(){
	
		//Deleta os dados da session shipping
		unset($this->session->data['payments']);
		
		$this->data['extensions'] = array();
		
		//Aqruivos n�o exibidos
		$fileNotPermited = array(
			'authorizenet_aim',
			'authorizenet_sim',
			'google_checkout',
			'klarna_account',
			'klarna_invoice',
			'liqpay',
			'nochex',
			'paymate',
			'paypoint',
			'payza',
			'perpetual_payments',
			'pp_express',
			'pp_pro',
			'pp_pro_uk',
			'sagepay',
			'sagepay_direct',
			'sagepay_us',
			'twocheckout',
			'web_payment_software',
			'worldpay',
		);
		
		//Carrega todos os arquivos .php da pasta shipping
		$files = glob(DIR_ROOT . 'admin/controller/payment/*.php');
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				//Instancia o idioma portuguese-br
				$language = new Language('../../admin/language/portuguese-br');
				
				//Carrega a linguagem do m�dulo
				$language->load('payment/' . $extension);
	
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
		$this->data['back'] = $this->url->link('step_4');
		
		$this->template = 'step_5.tpl';
		$this->children = array(
			'header',
			'footer'
		);
		
		$this->response->setOutput($this->render());
	}
	
	//Fun��o respons�vel por instalar o m�dulo e definir as permiss�es de acesso
	public function configure(){
		
		//Verifica se o POST enviado cont�m o campo shipping
		if (isset($this->request->post['payments'])){
			//Salva os dados do campo shipping na session
			$this->session->data = $this->request->post;
		}
		
		//Verifica se existe o m�todo POST e se existe o par�metro NEXT na url
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->get['next'])){
			//Carrega o model setting
			$this->load->model('setting');
			//Verifica se o m�dulo possui arquivo .sql
			if (file_exists(DIR_APPLICATION . 'sql/payment/' . current($this->session->data['payments']) . '.sql'))
				//Executa arquivo .sql do m�dulo caso haja
				$this->model_setting->mysql(DIR_APPLICATION . 'sql/payment/' . current($this->session->data['payments']) . '.sql');
			//Salva as configura��es do m�dulo
			$this->model_setting->editSetting(current($this->session->data['payments']), $this->request->post);
			//Instala o m�dulo
			$this->model_setting->install('payment', current($this->session->data['payments']));
			//Define a permiss�o de acesso
			$this->model_setting->addPermission('access', 'payment/' . current($this->session->data['payments']));
			//Define a permiss�o de modifica��o
			$this->model_setting->addPermission('modify', 'payment/' . current($this->session->data['payments']));
			//Deleta o primeiro �ndice do array
			array_shift($this->session->data['payments']);
		}
		
		//Verifica se a session est� v�zia,
		if (!empty($this->session->data['payments'])){
			//Caso n�o esteja vazia, carrega o layout do m�dulo escolhido
			$this->template = 'payments/' . current($this->session->data['payments']) . '.tpl';
			$this->children = array(
				'header',
				'footer'
			);
		
			$this->response->setOutput($this->render());
		}else{
			//Caso esteja vazio redireciona para a pr�xima etapa
			$this->redirect($this->url->link('step_6'));
		}
		
	}
	
}