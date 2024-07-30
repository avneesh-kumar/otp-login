<?php

namespace Opencart\Admin\Controller\Extension\Imlphonelogin\Module;

class Imlphonelogin extends \Opencart\System\Engine\Controller{
    public function index(){
        $this->load->language('extension/iml_phone_login/module/iml_phone_login');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashbord', 'user_token=' .$this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' =>$this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' .$this->session->data['user_token'] . '&type=module')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/iml_phone_login/module/iml_phone_login', 'user_token=' .$this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link('extension/iml_phone_login/module/iml_phone_login.save', 'user_token=' . $this->session->data['user_token']);

        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['module_iml_phone_login_status'] = $this->config->get('module_iml_phone_login_status');
        $data['module_iml_phone_login_service_sid'] = $this->config->get('module_iml_phone_login_service_sid');
        $data['module_iml_phone_login_sid'] = $this->config->get('module_iml_phone_login_sid');
        $data['module_iml_phone_login_auth_token'] = $this->config->get('module_iml_phone_login_auth_token');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->view('common/footer');

        $this->response->setOutput($this->load->view('extension/iml_phone_login/module/iml_phone_login', $data));
    }

    public function save(){
        $this->load->language('extension/iml_phone_login/module/iml_phone_login');

        $json = [];

        if(!$this->user->hasPermission('modify','extension/iml_phone_login/module/iml_phone_login')){
            $json['error'] = $this->language->get('error_permission');
        }

        if(!$json){
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_iml_phone_login', $this->request->post);
            $json['success'] = $this->language->get('text_success');
        }
        $this->response->addHEader('content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(){
		$this->__registerEvents();
	}

	protected function __registerEvents(){
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent([
            'code' => 'module_iml_phone_login',
            'description' => 'This is IML Phone Login event',
            'trigger' => 'catalog/view/account/login/after',
            'action' => 'extension/iml_phone_login/event/iml_phone_login',
            'status' => true,
            'sort_order' => 0
		]);

        $this->model_setting_event->addEvent([
            'code' => 'module_iml_phone_login_editNumber',
            'description' => 'This is IML Phone Login event for customer to edit telephone',
            'trigger' => 'catalog/view/account/edit/after',
            'action' => 'extension/iml_phone_login/event/iml_phone_login_editnumber',
            'status' => true,
            'sort_order' => 0
		]);
        
        $this->model_setting_event->addEvent([
            'code' => 'module_iml_phone_login_registerNumber',
            'description' => 'This is IML Phone Login event for customer to register telephone',
            'trigger' => 'catalog/view/account/register/after',
            'action' => 'extension/iml_phone_login/event/iml_phone_login_registernumber',
            'status' => true,
            'sort_order' => 0
		]);
	}

	public function uninstall(){
        $this->__unregisterEvents();
	}

    protected function __unregisterEvents(){
        $this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('module_iml_phone_login');
		$this->model_setting_event->deleteEventByCode('module_iml_phone_login_editNumber');
		$this->model_setting_event->deleteEventByCode('module_iml_phone_login_registerNumber');
	}
}