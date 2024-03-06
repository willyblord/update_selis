$(document).ready(function(){

    //LOAD POPULATE CUSTOMERS 
	$.ajax({
		url:"api/list-countries",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#country').append($('<option/>').attr("value", option.id).text(option.country_name));
			});
		}
	})
	
	$(document).on('submit', '#recover_form', function(event){
		event.preventDefault();
		var country = $('#country').val();
		var email = $('#email').val();
		
		if( (country == '') || (email == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{
            const submitdata = {
				'country': $('#country').val(),
				'email':  $('#email').val()
			}
			console.log(submitdata);

			//post with ajax
			$.ajax({
				type: "POST",
				url: "api/recover-password",
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