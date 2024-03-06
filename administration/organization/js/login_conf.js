
$(document).ready(function(){

	$('#i_accept').click(function(){

		var OTP = $('#OTP').val();

		if( (Math.floor(OTP) != OTP && !$.isNumeric(OTP)) || (OTP === '') || ($.trim(OTP).length < 1) )  {

			Swal.fire('Error.', 'The OTP must be a digit', 'error')

		} else {

			const submitdata = {
				'OTP': OTP,
			}
			console.log(submitdata);
			//post with ajax
			$.ajax({
				type: "POST",
				url: "api/login-confirm",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						location.href = "welcome";
					} else {
						if(!data.redirect) {
							Swal.fire('Error.', data.message, 'error');
						} else {
							Swal.fire('Error.', data.message, 'error').then(function(){
								location.href = "login";
							});
						}
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

	$('#i_decline').click(function(){
		//post with ajax
		$.ajax({
			type: "POST",
			url: "api/login-decline",
			ContentType:"application/json",
			beforeSend: function() {
				$("#loader").show();
			},

			success:function(data){ 

				if(data.success) {
					location.href = "login";
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
	});
	
});