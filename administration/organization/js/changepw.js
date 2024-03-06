$(document).ready(function(){
    	
	$(document).on('submit', '#recover_form', function(event){
		event.preventDefault();
		var oldpassword = $('#oldpassword').val();
		var password = $('#password').val();
		var rePassword = $('#rePassword').val();
		
		if( (oldpassword == '') || (password == '') || (rePassword == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{
            const submitdata = {
				'oldpassword': oldpassword,
				'password': password,
				'rePassword': rePassword
			}

			//post with ajax
			$.ajax({
				type: "POST",
				url: "api/change-password",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},
				success:function(data){ 

					if(data.success) {
                        Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

                    $('#recover_form')[0].reset();
					$("#loader").hide();
				}
			});
		}
	});

});