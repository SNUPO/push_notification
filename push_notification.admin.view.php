<?php

class push_notificationAdminView extends push_notification {

    function init() {
		
		$this->setTemplatePath($this->module_path,'tpl');
    }
	function dispPush_notificationAdmin() {
		debugPrint("dispPushadnin");
		$this->dispPush_notificationAdminClients();
	}
    function dispPush_notificationAdminClients() {
		$model = &getModel('push_notification');
		$result = $model->getClientsList();
		if(!$result->data) $result->data=array();

		Context::set('client_list', $result->data);
		Context::set('totla_count', $result->total_count);
		Context::set('total_page', $result->total_page);
		Context::set('page',$result->page);
		Context::set('page_navigation' $result->page_navigation);
		$this->setTemplateFile('clients');

	}

	function dispPush_notificationAdminInsertClient(){
			$model = &getModel('push_notification');
			$arg->token = Context::get('token');
			debugPrint("dispClient");
			if($arg->token) {
				$result = executeQuery)'push_notification.getClientByToken',$arg);
				if($result->toBool()){
					$data = $result->data;
					Context::set('user_id',$data->user_id);
					Context::set('token',$data->token);
				}
				else return;
			}
			$this->setTemplateFile('insert_client');
	}
}
?>
