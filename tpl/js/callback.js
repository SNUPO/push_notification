function completeAdminInsert(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.reload();
}

function change_status(count){
	var obj_td = xGetElementById("row_"+count);
	obj_td.style.backgroundColor = "#00FF00";
}
