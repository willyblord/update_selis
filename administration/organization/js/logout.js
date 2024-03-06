
$(document).ready(function(){	

	$(document).on('submit', '#logout_form', function(event){
		event.preventDefault();	
		const submitdata = {
			'logout': $('#logout').val()
		}

		//post with ajax
		$.ajax({
			type: "POST",
			url: "api/logout",
			data: JSON.stringify(submitdata),
			ContentType:"application/json",

			success:function(data){

				if(data.success) {
                    location.reload()
				} else {
					alert(data.message);
				}
			}
		});
	});	
});