<?php


class push_notification extends ModuleObject {

	function moduleInstall() {
		$this->insertFlag();
		return new Object();
	}

	function checkUpdate() {
	}
	function moduleUpdate() {
		return new Object(0,'success_updated');

	}	
	function recomplieCache() {
	}

	function insertFlag() {
		$moduleController= &getController('module');
		$moduleController->insertTrigger('document.insertDocument','push_notification','controller','triggerSendDocument','after');
		$moduleController->insertTrigger('comment.insertComment','push_notification','controller','triggerSendComment','after');
	}

}
