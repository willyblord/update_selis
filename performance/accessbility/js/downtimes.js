
$(document).ready(function(){

	$('#downtime_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#downtime_form')[0].reset();
		$('.modal-title').text("Add New Downtime");
		$('#action').val("Submit");
		$('#operation').val("Add");
	});


	//LOAD POPULATE COUNTRIES 
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

	//LOAD POPULATE SYSTEMS 
	$.ajax({
		url:"api/list-systems",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#system').append($('<option/>').attr("value", option.id).text(option.system_name));
			});
		}
	})		
		
	var dataTable = $('#downtime_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-all-accessbility-downtimes'
		},
		'columns': [   
			{ data: 'refNo'},
			{ data: 'downtime' },
			{ data: 'system_name' },
			{ data: 'country' },
			{ data: 'time_started' },
			{ data: 'tat_in_minutes' },
			{ data: 'hours_in_minutes' },
			{ data: 'created_at' },
			{ data: 'created_by' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 9 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$(document).on('submit', '#downtime_form', function(event){
		event.preventDefault();
		var country = $('#country').val();
		var system = $('#system').val();
		var downtime = $('#downtime').val();
		var time_started = $('#time_started').val();
		var time_resolved = $('#time_resolved').val();
		var tat_in_minutes = $('#tat_in_minutes').val();
		var hours_in_minutes = $('#hours_in_minutes').val();
		var rca = $('#rca').val();
		
		if( (country == '') ||  (system == '') || (downtime == '') || (tat_in_minutes == '') || (hours_in_minutes == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'country':  country,
				'system': system,
				'downtime': downtime,
				'time_started': time_started,
				'time_resolved': time_resolved,
				'tat_in_minutes': tat_in_minutes,
				'hours_in_minutes': hours_in_minutes,
				'rca': rca,
				'id': $('#id2').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-downtime",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#downtime_form')[0].reset();
						$('#addModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader").hide();
					dataTable.ajax.reload();
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
	


	$(document).on('click', '.update', function(){
		var id = $(this).attr("id");
		console.log(id);	
		$.ajax({
			url:"api/get-single-downtime-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	
				if(res.success) {
					$('#addModal').modal('show');
					$('#country').val(res.data.country_val).trigger('change');
					$('#system').val(res.data.system_val).trigger('change');
					$('#downtime').val(res.data.downtime);
					$('#time_started').val(res.data.time_started);
					$('#time_resolved').val(res.data.time_resolved);
					$('#tat_in_minutes').val(res.data.tat_in_minutes);
					$('#hours_in_minutes').val(res.data.hours_in_minutes);
					$('#rca').val(res.data.rca);

					$('.modal-title').text("Edit Downtime Details");
					$('#id2').val(id);
					$("#action").show();
					$('#action').val("Save Changes");
					$('#operation').val("Edit");

				} else {
					Swal.fire('Error.', res.message, 'error')
				}
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
			},
		})
	});

	
	$(document).on('click', '.view', function(){
		var id = $(this).attr("id");
		$.ajax({
			url: 'api/get-single-downtime-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');
					$('#view_refNo').html(res.data.refNo);
					$('#view_country').html(res.data.country);
					$('#view_system').html(res.data.system);
					$('#view_downtime').html(res.data.downtime);
					$('#view_time_started').html(res.data.time_started);
					$('#view_time_resolved').html(res.data.time_resolved);
					$('#view_tat_in_minutes').html(res.data.tat_in_minutes);
					$('#view_hours_in_minutes').html(res.data.hours_in_minutes);
					$('#view_rca').html(res.data.rca);
					$('#view_created_at').html(res.data.created_at);
					$('#view_created_by').html(res.data.created_by);
					$('#view_updated_at').html(res.data.updated_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('.modal-title').text("View Downtime Details");
					$('#id').html(id);
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

		
	$(document).on('click', '.delete', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'delete_downtime',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/accessbility/action',
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
                beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
					$('#cover-spin').show()
				},
                success: function (res) {
					if(res.success) {
                    	Swal.fire('Success.', res.message, 'success')
					} else {						
                    	Swal.fire('Error.', res.message, 'error')
					}					
					dataTable.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	
});