	jQuery(document).ready(function($){
	$("#wks_insert_button").on("click",function(){
		var wks_id=$("#wks-select").val();
		send_to_editor('[wks id='+wks_id+']');
		});
		
	$(".sl-delete").on("click",function(){
		var cm=confirm('A you sure to delete ?');
		return cm;
		});
	
	});