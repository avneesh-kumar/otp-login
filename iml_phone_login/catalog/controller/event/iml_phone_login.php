<?php

namespace Opencart\Catalog\Controller\Extension\Imlphonelogin\Event;

class Imlphonelogin extends \Opencart\System\Engine\Controller{
    public function index(&$route = false, &$data = array(), &$output = array()){
        $template_buffer = $this->getTemplateBuffer($route, $output);
        
        $this->load->language("extension/iml_phone_login/account/iml_phone_login");

        // $link = $this->url->link("extension/iml_phone_login/account/iml_phone_login", 'language=' . $this->config->get('config_language'));

        if($this->config->get('module_iml_phone_login_status')){
            $layout = $this->load->view("extension/iml_phone_login/account/iml_phone_login",[
                'sentOtp' => $this->url->link("extension/iml_phone_login/account/iml_phone_login.sendOtp")
            ]);
            $find = '<button type="submit" class="btn btn-primary">Login</button>';
            $replace = $find . $layout;
            $output = str_replace($find, $replace, $template_buffer);
        }
    }

    protected function getTemplateBuffer($route, $event_template_buffer)
    {
        // if there already is a modified template from view/*/before events use that one
        if ($event_template_buffer) {
            return $event_template_buffer;
        }
    }
}