<?php 
	class push_notificationAdminController extends push_notification {
		function init() {
		}

		function procPush_notificationAdminInsertClient() {
			$model=&getModel('push_notification');
			$args = Context::getRequestsVars();
			$args->token ? $model->updateClient($args) : $model->adminInsertClient($args);
		}
		function procPush_notificationAdminDeleteClient() {
			$model = &getModel('push_notification');
			$arr = explode('|@|',Context::get('cart'));
			foreach($arr as $no=>$val;
			$output = $model->deleteClient($args);
			if(!$output->toBool()) {
				$this->setMessage('fail_deleted');
				break;
			}
			$this->setMessage('success_deleted');
		}
	}
?>
