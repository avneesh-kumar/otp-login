<?php

namespace Opencart\Catalog\Controller\Extension\Imlphonelogin\Event;

class Imlphonelogin extends \Opencart\System\Engine\Controller{
    public function index(&$route = false, &$data = array(), &$output = array()){
        $template_buffer = $this->getTemplateBuffer($route, $output);

        $link = $this->url->link("extension/iml_phone_login/account/iml_phone_login", 'language=' . $this->config->get('config_language'));

        if($this->config->get('module_iml_phone_login_status')){

            // $layout = $this->load->view('extension/iml_phone_login/account/iml_phone_login_btn',
            // [
            //     'language' => $this->config->get('config_language'),
            //     'link' => $this->url->link("extension/iml_phone_login/account/iml_phone_login")
            // ]);

            $layout ='<a href="'. $link .'" data-bs-toggle="tooltip" title="Login With Phone Number" id="phone-login" class="btn btn-primary m-1">Phone Login</a>';

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