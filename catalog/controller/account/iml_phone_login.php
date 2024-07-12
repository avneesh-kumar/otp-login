<?php
namespace Opencart\Catalog\Controller\Extension\Imlphonelogin\Account;

use Twilio\Rest\Client;

class Imlphonelogin extends \Opencart\System\Engine\Controller {

    private $serviceSid;
    private $accountSid;
    private $authToken;

    public function __construct($registry) {
        parent::__construct($registry);

        $this->serviceSid = $this->config->get('module_iml_phone_login_service_sid');
        $this->accountSid = $this->config->get('module_iml_phone_login_sid');
        $this->authToken = $this->config->get('module_iml_phone_login_auth_token');
    }

    public function index(){
        $this->load->language('account/login');

		$this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('account/login', 'language=' . $this->config->get('config_language'))
		];

        $data['sentOtp'] = $this->url->link("extension/iml_phone_login/account/iml_phone_login.sendOtp");

        $data['language'] = $this->config->get('config_language');
		$data['register'] = $this->url->link('account/register', 'language=' . $this->config->get('config_language'));
		$data['forgotten'] = $this->url->link('account/forgotten', 'language=' . $this->config->get('config_language'));

        $data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('extension/iml_phone_login/account/iml_phone_login', $data));
    }

    public function sendOtp(){

        $this->load->language('account/login');

		$this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('account/login', 'language=' . $this->config->get('config_language'))
		];

        $data['number'] = $number = $this->request->post["phone"];
        $data['email'] = $email = $this->request->post["email"];    

        $this->load->model('extension/iml_phone_login/account/iml_phone_login');
        $result = $this->model_extension_iml_phone_login_account_iml_phone_login->getCustomerByMailAndNumber($number, $email);

        if(empty($result)){
            $data['register'] = $this->url->link("extension/iml_phone_login/account/iml_phone_login.register");

        } else {
            $status = $this->otp($number);
            if($status){
                $data['verification'] = $status;
                $data['verify'] = $this->url->link("extension/iml_phone_login/account/iml_phone_login.verifyOtp");
            }
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/iml_phone_login/account/iml_phone_login_verify',$data));
    }

    public function otp($number){
        $sid    = $this->accountSid;
        $token  = $this->authToken;

        $twilio = new Client($sid, $token);

        $verification = $twilio->verify->v2->services($this->serviceSid)->verifications->create($number, "sms");

        return $verification->status;
    }

    public function verifyOtp(){

        $this->load->language("extension/iml_phone_login/account/iml_phone_login");

        $json = [];

        $number = $this->request->post["telephone"];
        $code = $this->request->post["code"];

        $twilio = new Client($this->accountSid, $this->authToken);

        $verification_check = $twilio->verify->v2->services($this->serviceSid)->verificationChecks->create([
            "to" => $number,
            "code" => $code,
        ]);

        if(!$verification_check->valid){
            $json['error']['warning'] = $this->language->get('error_invalid_otp');
			// $json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
        }

        if($verification_check->valid){

            $this->load->model('account/customer');
            $this->load->model('extension/iml_phone_login/account/iml_phone_login');

			$customer_info = $this->model_extension_iml_phone_login_account_iml_phone_login->getCustomerByMailAndNumber($this->request->post['telephone'], $this->request->post['email']);

            if ($customer_info && !$customer_info['status']) {
				$json['error']['warning'] = $this->language->get('error_approved');
			} elseif (!$this->customer->login($this->request->post['email'], $this->request->post['telephone'], ENT_QUOTES, 'UTF-8')) {
				$json['error']['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['email']);
			}

            if (!$json) {
                // Add customer details into session
                $this->session->data['customer'] = [
                    'customer_id'       => $customer_info['customer_id'],
                    'customer_group_id' => $customer_info['customer_group_id'],
                    'firstname'         => $customer_info['firstname'],
                    'lastname'          => $customer_info['lastname'],
                    'email'             => $customer_info['email'],
                    'telephone'         => $customer_info['telephone'],
                    'custom_field'      => $customer_info['custom_field']
                ];

                unset($this->session->data['order_id']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
    
                // Wishlist
                if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
                    $this->load->model('account/wishlist');
    
                    foreach ($this->session->data['wishlist'] as $key => $product_id) {
                        $this->model_account_wishlist->addWishlist($product_id);
    
                        unset($this->session->data['wishlist'][$key]);
                    }
                }

                // Log the IP info
                $this->model_account_customer->addLogin($this->customer->getId(), $this->request->server['REMOTE_ADDR']);
    
                // Create customer token
                $this->session->data['customer_token'] = oc_token(26);
    
                $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
    
                // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
                if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
                    $json['redirect'] = str_replace('&amp;', '&', $this->request->post['redirect']) . '&customer_token=' . $this->session->data['customer_token'];
                } else {
                    $json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true);
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function register(){

        $this->load->model('extension/iml_phone_login/account/iml_phone_login');

        $data['verification'] = $this->otp($this->request->post['telephone']);

        $this->model_extension_iml_phone_login_account_iml_phone_login->register($this->request->post);

        return $this->load->view('extension/iml_phone_login/account/iml_phone_login_verify',$data);
    }
}