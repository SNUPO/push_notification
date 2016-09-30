<?php

class push_notificationController extends push_notification{ 

    function init() {
    }
	
	function triggerSendDocument ($obj) {
		$oPush_notification = &getModel('push_notification');
		return $oPush_notification->sendMessage($obj,'D');
	}
	function triggerSendComment($obj) {
		$oPush_notification = &getModel('push_notification');
		$oPush_notification->sendMessage($obj,'C');
	}

	function insertClient() {
		$user_id = $_GET["user_id"];
		$token= $_GET["token"];
		$document_filter = $_GET["document_filter"];
		debugPrint($document_filter);
		if(!$token) return new Object();
		$temp->user_id = $user_id;
		$valid = executeQuery('push_notification.getMemberSrl',$temp);//id validation check
		if(count($valid->data) > 0) $args->user_id = $user_id;
		$args->token = $token;
		$args->document_filter= $document_filter;
		debugPrint($args->document_filter);
		$is_exist = executeQuery('push_notification.getClient',$args);
		if(count($is_exist->data) > 0)  return $this->updateClient($args);
		else {
			executeQuery('push_notification.insertClient',$args);
			return new Object() ;
		}
	}

	function updateClient($obj) {
		$args->token = $obj->token;
		$args->user_id = $obj->user_id;
		$args->document_filter = $obj->document_filter;
		executeQuery('push_notification.updateClient',$args);
		return new Object();
	}


	function adminInsertClient($args) {
	}

	function deleteClient($obj){
		$args->token = $obj->token;
		executeQuery('push_notification.deleteClient',$args);
		return new Object();
	}
	
/*
	function triggerModuleHandlerInit() {
		if(Context::get('obid')){
			$_SESSION["obid"] = Context::get('obid');
		}
	}

	function triggerAfterLogin($$obj) {
		$member_srl = $obj->member_srl;
		if(!$member_srl) return new Object();

		if(!$_SESSION["obid"]) return new object();

		$query = "update xe_member set 'obid' = '".$_SESSION["obid"]."'where 'member_srl' = ".$member_srl;
		$sql = mysql_query($query);

		if($sql) {
			unset($_SESSION["obid"]);
			return new Object();
		}
		else return new Object(-1,"단말기 정보를 가져오는데 실패하였습니다.");
	}
*/
	function changeOpt($obj, $user_id,$opt) {
		$oPush_notification = &getModel('push_notification');
		if($user_id == null) return 'N'.$opt;
		else {
			$document_writer = $oPush_notification->getDocumentWriter($obj);
			if($document_writer == $user_id) return 'MDC';
			$parent_comment_writer = $oPush_notification->getParentCommentWriter($obj);
			if($parent_comment_writer == $user_id) return 'MCC';
			$args->user_id = $user_id;
			if(strpos( $obj->content, '@') !==false  ) { //Tag exist.
				$result = executeQuery('push_notification.getUserName',$args);
				if(strpos( $obj->content, '@'.$result->data->user_name)!== false) return 'T'.$opt; 
			}
		}
		return 'N'.$opt;
	}

	function checkPermission($acc_groups,$user_id) {
		$oPush_notification = &getModel('push_notification');
		$args->user_id = $user_id;
		$is_guest = false;
		if($user_id != null) {
			$args->member_srl  = $oPush_notification->getMemberSrl($args); // user_id -> member_srl from xe_members table
			$temp =$oPush_notification->getGroupSrl($args); // member_srl -> group_srl from xe_member_group_member table
		}
		else $is_guest = true; //guest
		
		debugPrint($acc_groups);
		debugPrint($temp);
		if( count($temp) > 1) { //ex : 정회원, 객원
			foreach ($temp as $key => $value) {
				if($this->innerCheckPermission($acc_groups, $value->group_srl)) return true;
			}
		}
		else {
			if($is_guest) $temp->group_srl = 0;
			return $this->innerCheckPermission($acc_groups, $temp->group_srl);
		}
		return false;
	}	
	function innerCheckPermission($obj,$group_srl) {
		if($group_srl == 1) return true; //admin user
		if(count($obj) ==1) return $obj == $group_srl;
		foreach ($obj as $key => $value) {
			if($value->group_srl == 0 || $value->group_srl == $group_srl) return true;
		}
		return false;
	}

}
?>
