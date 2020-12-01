$(document).ready(function(){
	$('#checkall').change(function () {
		if($(this).is(':checked')) {
			$('input.checkitem').prop('checked',true);
		} else {
			$('input.checkitem').prop('checked',false);
		}
	});

	$('#btn_delete-sidebar').click(function () {
		if(isSelected() == true ) {
			$('#submit_type').val('DELETE');
			$('#related_links_form' ).submit();
		} else {
			alert( 'Select items..' );
		}
	});

	$("#related_links_insert_form").submit(function(){
		if($.trim($('#related_links_insert_form input[type="text"]').val())=='') {
			alert('insert subject');
			$('#related_links_insert_form input[type="text"]').focus();
			return false;
		}

		if($.trim($('#related_links_insert_form input[type="url"]').val())=='') {
			alert('insert url');
			$('#related_links_insert_form input[type="url"]').focus();
			return false;
		}
	});
});

function isSelected () {
	var is_checked = false;

	$('input.checkitem').each(function() {
		if($(this).is(':checked'))
			is_checked=true;
	});
	return is_checked;
}
