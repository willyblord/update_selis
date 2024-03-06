$(document).ready(function(){
	
	$(document).on('submit', '#changForm', function(event){
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
				'oldpassword':  $('#oldpassword').val(),
				'password': $('#password').val(),
				'rePassword': $('#rePassword').val()
			}
			console.log(submitdata);

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
						$('#changForm')[0].reset();
                        Swal.fire('Success.', data.message, 'success');

						setTimeout(function() { location.reload(); }, 3000);
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader").hide();
				}
			});
		}
	});

});