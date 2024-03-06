
$(document).ready(function(){

	$('#cover-spin').hide();
    
	$(document).on('click', '#stay_connected', function(){
		$.ajax({
			url: 'api/refresh-token',
			method:"GET",
			success: function(data) { 
				if (!data.success) {
					location.reload()
				}
			}
		})
	});
	
	let dupli = 0;	
	var auto_refresh = setInterval(
	function ()
	{		
		$.ajax({
			url: 'api/about-expiration',
			method:"GET",
			success: function(data) { 
				if (data.success && data.reload) {
					location.reload();
				} else if (data.success && !data.reload) {
					
					$('#expiresModal').modal('show');
					$('#count_sec').html(data.remaining);
				} else {
					location.reload();
				}
			}
		})
	}, 10000);


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


	$(".side-menu-list").on("click",function(){
		$(".side-menu-list.active").removeClass('active');
		$(this).addClass('active');
	});
		
});