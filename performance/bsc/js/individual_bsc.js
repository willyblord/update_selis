
$(document).ready(function(){

	$('#initiative_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#initiative_form')[0].reset();
		$('.modal-title').text("Add Individual BSC");
		$("#group_strategy_id").val('').trigger('change');	
		$("#department").val('').trigger('change');	
		$("#year").val('').trigger('change');	

		$('#action').val("Save");
		$('#operation').val("Add");
	});

	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url': 'api/get-all-individual-bsc',
		},
		'columns': [   
			{ data: 'bsc_owner' },
			{ data: 'country' },
			{ data: 'department' },
			{ data: 'year' },
			{ data: 'status' },
			{ data: 'location' },
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions' },
		],
		'columnDefs':[
			{
				"targets": [ 8 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 

	
	var dataTable1 = $('#approval_logs_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url': 'api/get-bsc-approval-logs',
		},
		'columns': [   
			{ data: 'approved_by' },
			{ data: 'location' },
			{ data: 'status' },
			{ data: 'supervisor_comments' },
			{ data: 'approved_at' },
		],
		'columnDefs':[
			{
				"targets": [ 3 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable1.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#initiative_form', function(event){
		event.preventDefault();
		var year = $('#year').val();

		if( year == '' )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'year': year,
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
				url: "api/create-ind-bsc",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#initiative_form')[0].reset();
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
			url:"api/get-single-bsc-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addModal').modal('show');
					$('#department').val(res.data.department).trigger('change');
					$('#group_strategy_id').val(res.data.group_strategy_id).trigger('change');
					$('#year').val(res.data.year).trigger('change');

					$('.modal-title').text("Edit Initiative");
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

	$(document).on('click', '.submit_for_approval', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to submit this for Approval?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'submit_bsc_for_approval',
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

	
	$(document).on('click', '.submit_for_evaluation', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to submit this for Evaluation?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'submit_bsc_for_evaluation',
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
				'operation': 'delete_bsc',
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

	
});