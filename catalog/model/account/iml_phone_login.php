<?php

namespace Opencart\Catalog\Model\Extension\Imlphonelogin\Account;

class Imlphonelogin extends \Opencart\System\Engine\Model {

    public function getCustomerByNumber($number){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `telephone` = '".$number."'")->row;
        return $query;
    }

    public function register(array $data){

        if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = (int)$data['customer_group_id'];
		} else {
			$customer_group_id = (int)$this->config->get('config_customer_group_id');
		}

		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$customer_group_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `language_id` = '" . (int)$this->config->get('config_language_id') . "', `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `telephone` = '" . $this->db->escape((string)$data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `newsletter` = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', `status` = '" . (int)!$customer_group_info['approval'] . "', `date_added` = NOW()");

		$customer_id = $this->db->getLastId();

		if ($customer_group_info['approval']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_approval` SET `customer_id` = '" . (int)$customer_id . "', `type` = 'customer', `date_added` = NOW()");
		}
        
		return $customer_id;
    }
}