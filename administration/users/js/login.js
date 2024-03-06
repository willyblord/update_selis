
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

	$(document).on('submit', '#login_form', function(event){
		event.preventDefault();
		var country = $('#country').val();
		var username = $('#username').val();
		var password = $('#password').val();
		
		if(  (country == '') || (username == '') ||  (password == ''))
		{
			Swal.fire('Error.', 'Please Enter all Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'country': $('#country').val(),
				'username': $('#username').val(),
				'password': $('#password').val()
			}

			//post with ajax
			$.ajax({
				type: "POST",
				url: "api/login",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){

					if(data.success) {
						$('#login_form')[0].reset();
						location.href = "login-confirmation";
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader").hide();
				},
				error: function (jqXHR, exception) {
					var msg = '';
					if (jqXHR.status === 0) {
						msg = 'Not connect.\n Verify Network.';
					} else if (jqXHR.status == 404) {
						msg = 'Requested page not found. [404]';
					} else if (jqXHR.status == 500) {
						msg = 'Internal Server Error [500].';
					} else if (exception === 'parsererror') {
						msg = 'Requested JSON parse failed.\n' + jqXHR.responseText;
					} else if (exception === 'timeout') {
						msg = 'Time out error.';
					} else if (exception === 'abort') {
						msg = 'Ajax request aborted.';
					} else {
						msg = 'Uncaught Error.\n' + jqXHR.responseText;
					}
					Swal.fire('Error.', msg, 'error')
					$("#loader").hide();
				},

			});


		}
	});
	
});