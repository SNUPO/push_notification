<?php
class push_notificationModel extends push_notification {
	function getModule($obj) {
		$result= executeQuery('push_notification.getModules',$obj);
		return $result->data;
	}
	function getMemberSrl($obj) {
		$result=  executeQuery('push_notification.getMemberSrl',$obj);
		return $result->data->member_srl;
	}
	function getDocumentWriter($obj) {
		$args->document_srl = $obj->document_srl;
		$result = executeQuery('push_notification.getDocumentWriter',$args);
		return $result->data->user_id;
	}
	function getParentCommentWriter($obj) {
		$args->comment_srl = $obj->parent_srl;
		$result = executeQuery('push_notification.getCommentWriter',$args);
		return $result->data->user_id;
	}
	function getMid($obj) {
		$result = executeQuery('push_notification.getMid',$obj);
		return $result->data->mid;
	}
	function getGroupSrl($obj) {
		$args->member_srl = $obj->member_srl;
		$result = executeQuery('push_notification.getGroupSrl',$args);
		return $result->data;
	}
	function getAccessibleGroups ($obj) {
		$args->module_srl = $obj->module_srl;
		$args->name ="access";
		$result  = executeQuery('push_notification.getAccessibleGroups',$args); 
		debugPrint($result->data);
		return $result->data;
	}

    function getTitle(&$obj) {
        if ($obj->title)
            return $obj->title;
        $args->document_srl = $obj->document_srl;
        $result = executeQuery('push_notification.getTitle', $args);
        return $result->data->title;
	}



	function sendMessage($obj,$type) {
		$API_ACCESS_KEY="KEY";
		$args->user_id = $obj->user_id;
		$clients = executeQuery('push_notification.getClients',$args); //error : always return boolean value
		
		if(count($clients->data) == 0)	return true; // no clients
		$clients = $clients->data;
		$args->module_srl = $obj->module_srl;
		$mid = $this->getMid($args);
		$acc_groups = $this->getAccessibleGroups($args); //module_srl -> groups_srl

		$oPush_notification = &getController('push_notification');
		$url = 'https://fcm.googleapis.com/fcm/send';
		$headers = array (
			'Authorization:key ='.$API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		$data = array(
			'timestamp' => date('YmdHis'),
			'event_type' => $type,
			'mid' => $mid,
			'document_srl' => $obj->document_srl,
			'title' => $this->getTitle($obj),
			'comment_srl' => any($obj->comment_srl,null),
			'user_name' => $obj->user_name
			);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch, CURL_POST, true);
		curl_setopt($ch, CURLOPT_SSH_VERIFYHOST, 0);
	 	curl_setopt($ch, CURL_SSH_VERIFYPEER,0);//최신이면 할 필요 없다고 함.
		
		if(count($clients) == 1) { // if clients has just 1 element, foreach seperates token, user_id
			$permission = $oPush_notification->checkPermission($acc_groups,$clients->user_id);
			if(!$permission) return true;
			if($event_type=='NC') continue;
			$event_type = $oPush_notification->changeOpt($obj,$clients->user_id,  $type);

			if($event_type=='ND'&& $clients->document_filter != 'all' &&( $clients->document_filter == null || strpos($clients->document_filter,$mid)===false) )return true;

			$data['event_type'] = $event_type;
			$fields = array (
				'to' => $clients->token,
				'data' => $data
			);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($fields));
			$result= curl_exec($ch);
			debugPrint($result);
		}
		
		else {
			foreach ($clients as $cl ) {
				debugPrint($cl);
				$permission = $oPush_notification->checkPermission($acc_groups,$cl->user_id);
				if(!$permission){
					debugPrint("Permission No");
					continue;
				}
				$event_type = $oPush_notification->changeOpt($obj,$cl->user_id,  $type);
				if($event_type=='ND'&& $cl->document_filter != 'all' &&($cl->document_filter == null || strpos($cl->document_filter,$mid)===false) ) {
					debugPrint("FILTER");
					continue;
				}
				if($event_type =='NC') continue;
				$data['event_type'] = $event_type;
				$fields = array (
					'to' => $cl->token,
					'data' => $data
				);
				if($obj->title =='test') continue ;//test용

				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($fields));
				$result = curl_exec($ch);
				debugPrint($result);
			}
		}
		curl_close($ch);
		return true;
	}
}
?>
