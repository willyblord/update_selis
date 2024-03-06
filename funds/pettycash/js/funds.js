
$(document).ready(function(){

	$('#budget_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#budget_form')[0].reset();
		$('.modal-title').text("Add Budget");

		$('#action').val("Submit");
		$('#operation').val("Add");
		$("#category").val('').trigger('change');
		$("#providers").val('').trigger('change');
		$("#customers").val('').trigger('change');
		$("#otherServer").hide();
		$("#otherServer1").hide();		
		$('#thumbnail_doc').empty();
	});

	//LOAD POPULATE CATEGORIES 
	$.ajax({
		url:"api/list-departments",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#department').append($('<option/>').attr("value", option.id).text(option.department_name));
			});
		}
	})	


	//LOAD POPULATE  
	$.ajax({
		url:"api/list-budget-category",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#budget_category').append($('<option/>').attr("value", option.id).text(option.budget_category));
			});
		}
	})

	
	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-all-budgetlines'
		},
		'columns': [   
			{ data: 'department'},
			{ data: 'budget_category' },
			{ data: 'start_date' },
			{ data: 'end_date' },
			{ data: 'remaining_amount' },
			{ data: 'status' },
			{ data: 'inserted_at' },
			{ data: 'insterted_by' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 7 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	
    $(document).on('submit', '#budget_form', function(event){
		event.preventDefault();
		var department = $('#department').val();
		var budget_category = $('#budget_category').val();
		var start_date = $('#start_date').val();
		var end_date = $('#end_date').val();
		var initial_amount = $('#initial_amount').val();
		var id = $('#id2').val();
		
		if( (department == '') ||  (budget_category == '') || (start_date == '') || (end_date == '') || (initial_amount == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'department': department,
				'budget_category': budget_category,
				'start_date': start_date,
				'end_date': end_date,
				'initial_amount': initial_amount,
				'id': id
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
				url: "api/create-pettycash-budget",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(res){ 

					if(res.success) {
						$('#budget_form')[0].reset();
						$('#addBudgetModal').modal('hide');

						Swal.fire('Success.', res.message, 'success')
					} else {
						Swal.fire('Error.', res.message, 'error')
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
			url:"api/get-single-budgetline-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	console.log(res);	
				if(res.success) {
					$('#addBudgetModal').modal('show');
					$('#department').val(res.data.department_val).trigger('change');
					$('#budget_category').val(res.data.budget_category_val).trigger('change');
					$('#start_date').val(res.data.start_date);
					$('#end_date').val(res.data.end_date);
					$('#initial_amount').val(res.data.initial_amount_Val);

					$('.modal-title').text("Edit Budget");
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
			url: 'api/get-single-budgetline-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');
					
					$('.modal-title').text("View Budget Line");
					$('#view_department').html(res.data.department);
					$('#view_budget_category').html(res.data.budget_category);
					$('#view_start_date').html(res.data.start_date);
					$('#view_end_date').html(res.data.end_date);
					$('#view_initial_amount').html(res.data.initial_amount);
					$('#view_topup_amount').html(res.data.topup_amount);
					$('#view_deducted_amount').html(res.data.deducted_amount);
					$('#view_total_amount').html(res.data.total_amount);
					$('#view_used_amount').html(res.data.used_amount);
					$('#view_remaining_amount').html(res.data.remaining_amount);
					$('#view_status').html(res.data.status);
					$('#view_insterted_by').html(res.data.insterted_by);
					$('#view_inserted_at').html(res.data.inserted_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('#view_updated_at').html(res.data.updated_at);
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});
	
	$(document).on('click', '.activate', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to activate this budget?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'activate_budget',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: "api/pettycash/action",
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
            title: 'Are you sure you want to delete this budget?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'delete_budget',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/pettycash/action',
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