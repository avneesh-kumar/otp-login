<?php

namespace Opencart\Catalog\Controller\Extension\Imlphonelogin\Event;

class ImlphoneloginRegisternumber extends \Opencart\System\Engine\Controller{
    public function index(&$route = false, &$data = array(), &$output = array()){
        $template_buffer = $this->getTemplateBuffer($route, $output);

        if($this->config->get('module_iml_phone_login_status')){
            $layout = '<div class="row mb-3">
                            <label for="input-phone" class="col-sm-2 col-form-label">Telephone</label>
                            <div class="col-sm-10">
                                <input type="tel" name="telephone" value="" placeholder="Telephone" id="input-telephone" class="form-control"/>
                                <div id="error-telephone" class="invalid-feedback"></div>
                                <div class="form-text">Enter telephone with country code <b>(+1, +44).</b></div>
                            </div>
                        </div>';
            $find = '<legend>Your Personal Details</legend>';

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