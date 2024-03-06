
$(document).ready(function(){

	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-all-unit-bsc'
		},
		'columns': [   
			{ data: 'bsc_owner' },
			{ data: 'country' },
			{ data: 'department' },
			{ data: 'year' },
			{ data: 'status' },
			{ data: 'location'},
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 6 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	

	$(document).on('click', '.approve_bsc', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to approve this?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'approve_bsc',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/bsc/action',
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
	
	$(document).on('click', '.submit_evaluation', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to submit the Evaluation?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'submit_evaluation',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/bsc/action',
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

	$(document).on('click', '.revert_bsc', function() {
		var id = $(this).attr("id");
		$('#this_form').removeClass();
		$.ajax({
			url:"api/get-single-bsc-" + id,
			method: "GET",
			data: {
				id: id
			},
			dataType: "json",
			success: function(res) {
				if(res.success) {
					$('#actionModal').modal('show');
					$('#act_bsc_owner').html(res.data.bsc_owner_name);
					$('#act_country').html(res.data.country_name);
					$('#act_department').html(res.data.department_name);
					$('#act_bsc_year').html(res.data.year);

					$('#action_title').html('Reason');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<textarea class="form-control" rows="4" name="return_reason" id="return_reason" required data-error="Reason is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text("Revert for Amendment");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Revert");
                    $('#operation3').val("hr_revert_bsc");
                    $('#this_form').addClass("amend_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.amend_form', function(event) {
		event.preventDefault();

		var return_reason = $('#return_reason').val();

		if (return_reason == '') {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			const submitdata = {
				'operation': 'hr_revert_bsc',
				'return_reason': return_reason,
				'id': $('#id3').val()
			}
			$.ajax({
				type: "POST",
				url: "api/bsc/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader3").show();
				},
				success: function(data) { console.log(data)

					if(data.success) {
						$('#this_form')[0].reset();
						$('#this_form').removeClass();
						$("#action3").hide();
						$('#actionModal').modal('hide');
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', res.message, 'error')
					}
						
					$("#loader3").hide();
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


	$(document).on('click', '.reject', function() {
		var id = $(this).attr("id");
		$('#this_form').removeClass();
		$.ajax({
			url:"api/get-single-bsc-" + id,
			method: "GET",
			data: {
				id: id
			},
			dataType: "json",
			success: function(res) {
				if(res.success) {
					$('#actionModal').modal('show');
					$('#act_bsc_owner').html(res.data.bsc_owner_name);
					$('#act_country').html(res.data.country_name);
					$('#act_department').html(res.data.department_name);
					$('#act_bsc_year').html(res.data.year);

					$('#action_title').html('Reason');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<textarea class="form-control" rows="4" name="reject_reason" id="reject_reason" required data-error="Reason is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text("Reject BSC");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Reject");
                    $('#operation3').val("reject_bsc");
                    $('#this_form').addClass("reject_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.reject_form', function(event) {
		event.preventDefault();

		var reject_reason = $('#reject_reason').val();

		if (reject_reason == '') {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			const submitdata = {
				'operation': 'reject_bsc',
				'reject_reason': reject_reason,
				'id': $('#id3').val()
			}
			$.ajax({
				type: "POST",
				url: "api/bsc/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader3").show();
				},
				success: function(data) { console.log(data)

					if(data.success) {
						$('#this_form')[0].reset();
						$('#this_form').removeClass();
						$("#action3").hide();
						$('#actionModal').modal('hide');
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', res.message, 'error')
					}
						
					$("#loader3").hide();
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
	
});