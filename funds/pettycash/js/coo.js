
$(document).ready(function(){	
	
	var dataTable = $('#request_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-coo-pettycash-requests'
		},
		'columns': [   
			{ data: 'refNo'},
			{ data: 'category' },
			{ data: 'budgetCategory' },
			{ data: 'totalAmount' },
			{ data: 'status' },
			{ data: 'requestDate' },
			{ data: 'requestBy' },
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
	

	var dataTable1 = $('#budgets_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-fin-budgetlines'
		},
		'columns': [   
			{ data: 'department'},
			{ data: 'budget_category' },
			{ data: 'total_amount' },
			{ data: 'used_amount' },
			{ data: 'remaining_amount' },
			{ data: 'start_date' },
			{ data: 'end_date' },
		],
		'columnDefs':[
			{
				"targets": [ 0 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable1.ajax.reload( null, false );
	}, 30000 ); 


	var dataTable3 = $('#budgets_requests_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-dept-budgetrequests'
		},
		'columns': [   
			{ data: 'refNo'},
			{ data: 'amount' },
			{ data: 'budget_from' },
			{ data: 'budget_to' },
			{ data: 'description' },
			{ data: 'status' },
			{ data: 'requested_by' },
			{ data: 'request_date' },
			{ data: 'actions' },
		],
		'columnDefs':[
			{
				"targets": [ 0 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable3.ajax.reload( null, false );
	}, 30000 );
	
	
	$(document).on('click', '.view', function(){
		var id = $(this).attr("id");
		$.ajax({
			url: 'api/get-single-pettycash-request-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');
					
					$('.modal-title').text("View Request");
					$('#view_refNo').html(res.data.refNo);
					$('#view_visitDate').html(res.data.visitDate);
					$('#view_caregory').html(res.data.category);
					$('#view_budget_category').html(res.data.budget_category);

					$('#view_providers').html(res.data.providers);					
					res.data.providers !== null && res.data.providers !== '' ? $('#display_providers').show() : $('#display_providers').hide();
					
					$('#view_customers').html(res.data.customers);									
					res.data.customers !== null && res.data.customers !== '' ? $('#display_customers').show() : $('#display_customers').hide();
										
					$('#view_transport').html(res.data.transport);
					res.data.transport_Val !== null && res.data.transport_Val !== '0' ? $('#display_transport').show() : $('#display_transport').hide();
					
					
					$('#view_accomodation').html(res.data.accomodation);
					res.data.accomodation_Val !== null && res.data.accomodation_Val !== '0' ? $('#display_accomodation').show() : $('#display_accomodation').hide();
					
					$('#view_meals').html(res.data.meals);
					res.data.meals_Val !== null && res.data.meals_Val !== '0' ? $('#display_meals').show() : $('#display_meals').hide();
					
					$('#view_otherExpenses').html(res.data.otherExpenses);
					
					((res.data.transport_Val !== null && res.data.transport_Val !== '0') ||
					(res.data.accomodation_Val !== null && res.data.accomodation_Val !== '0') ||
					(res.data.meals_Val !== null && res.data.meals_Val !== '0')) 
					? $('#other_expenses_to_amount').html('Other Expenses') 
					: $('#other_expenses_to_amount').html('Amount');
					
					$('#view_afterClearance').html(res.data.afterClearance);
					$('#view_requester_charges').html(res.data.requester_charges);
					$('#view_charges').html(res.data.charges);
					$('#view_totalAmount').html(res.data.totalAmount);

					$('#view_disbursed').html(res.data.partiallyDisbursed);
					res.data.partiallyDisbursed_Val !== null && res.data.partiallyDisbursed_Val !== '0' ? $('#display_disbursed').show() : $('#display_disbursed').hide();
					
					$('#view_remaining').html(res.data.partiallyRemaining);
					res.data.partiallyRemaining_Val !== null && res.data.partiallyRemaining_Val !== '0' ? $('#display_remaining').show() : $('#display_remaining').hide();
					

					$('#view_air_departure_date').html(res.data.air_departure_date);
					res.data.air_departure_date !== null && res.data.air_departure_date !== '' ? $('#display_departure').show() : $('#display_departure').hide();
					
					$('#view_air_return_date').html(res.data.air_return_date);
					res.data.air_return_date !== null && res.data.air_return_date !== '' ? $('#display_return').show() : $('#display_return').hide();

					$('#view_checkin').html(res.data.checkin_date);
					res.data.checkin_date !== null && res.data.checkin_date !== '' ? $('#display_checkin').show() : $('#display_checkin').hide();

					$('#view_checkout').html(res.data.checkout_date);
					res.data.checkout_date !== null && res.data.checkout_date !== '' ? $('#display_checkout').show() : $('#display_checkout').hide();

					$('#view_phone').html(res.data.phone);
					res.data.phone !== null && res.data.phone !== '' ? $('#display_phone').show() : $('#display_phone').hide();

					$('#view_cheque').html(res.data.cheque_number);
					res.data.cheque_number !== null && res.data.cheque_number !== '' ? $('#display_cheque').show() : $('#display_cheque').hide();

					$('#view_bank_name').html(res.data.bank_name);
					res.data.bank_name !== null && res.data.bank_name !== '' ? $('#display_bank').show() : $('#display_bank').hide();

					$('#view_status').html(res.data.status);
					$('#view_description').html(res.data.description);
					$('#view_requestBy').html(res.data.requestBy);

					$('#view_requestDate').html(res.data.requestDate);
					$('#view_hodDate').html(res.data.hodDate);
					$('#view_hodApprove').html(res.data.hodApprove);
					$('#view_financeDate').html(res.data.financeDate);
					$('#view_financeApprove').html(res.data.financeApprove);
					$('#view_disburseDate').html(res.data.financeReleaseDate);
					$('#view_disbursedBy').html(res.data.financeRelease);
					$('#view_managerDate').html(res.data.managerDate);
					$('#view_managerApprove').html(res.data.managerApprove);
					$('#view_gmdDate').html(res.data.gmdDate);
					$('#view_gmdApprove').html(res.data.gmdApprove);
					$('#view_gmdComment').html(res.data.gmdComment);
					$('#view_clearanceDate').html(res.data.clearanceDate);
					$('#view_clearedBy').html(res.data.clearedBy);
					$('#view_clearenceReason').html(res.data.clearanceDescription);
					$('#view_clearSupervisorComment').html(res.data.clearSupervisorComment);
					$('#view_suspendDate').html(res.data.suspendDate);
					$('#view_suspendedBy').html(res.data.suspendedBy);
					$('#view_ReturnDate').html(res.data.ReturnDate);
					$('#view_ReturnedBy').html(res.data.ReturnedBy);
					$('#view_returnReason').html(res.data.returnReason);
					$('#view_rejectDate').html(res.data.rejectDate);
					$('#view_rejectedBy').html(res.data.rejectedBy);
					$('#view_rejectReason').html(res.data.rejectReason);

					$('#view_receiptImage').empty();

					var receiptName = res.data.receiptImage;
					if(receiptName != null)
					{
						var ext = receiptName.split('.').pop();

						if(ext === 'pdf' || ext === 'PDF') {
							$('#view_receiptImage').append($('<a href="funds/pettycash/uploads/receipts/'+receiptName+'" target="_blank">Click To View Document</a>'));
						} else {
							$('#view_receiptImage').append($('<img src="funds/pettycash/uploads/receipts/'+receiptName+'"  width="500">'));
						}										
						receiptName !== null && receiptName !== '' ? $('#display_receipt').show() : $('#display_receipt').hide();
					}
					
					$('#view_additional_doc').empty();
					$('#view_additional_doc').append($('<a href="funds/pettycash/uploads/'+res.data.additional_doc+'" target="_blank">Click To View Document</a>'));													
					res.data.additional_doc !== null && res.data.additional_doc !=='' ? $('#display_document').show() : $('#display_document').hide();
					
					$('.modal-title').text("View Request");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

    $(document).on('click', '.approve', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to approve this request?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'coo_approve',
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

	$(document).on('click', '.approve_budget', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to approve this budget request?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'coo_approve_budget',
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
					dataTable3.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


    $(document).on('click', '.unsuspend', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to Unsuspend this request? You are about to approve this request.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'coo_unsuspend',
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

	$(document).on('click', '.suspend', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to Suspend this request?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'suspend',
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
	
    $(document).on('click', '.amend', function() {
		var id = $(this).attr("id");
		$('#this_form').removeClass();
		$.ajax({
			url: 'api/get-single-pettycash-request-' + id,
			method: "GET",
			data: {
				id: id
			},
			dataType: "json",
			success: function(res) {
				if(res.success) {
					$('#actionModal').modal('show');
					$('#budg_refNo').html(res.data.refNo);
					$('#budg_category').html(res.data.category);
					$('#budg_totalAmount').html(res.data.totalAmount);
					$('#budg_requestBy').html(res.data.requestBy);
					$('#budg_requestDate').html(res.data.requestDate);

					$('#action_title').html('Reason');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<textarea class="form-control" rows="4" name="returnReason" id="returnReason" required data-error="Reason is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text("Revert for Amendment");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Revert");
                    $('#operation3').val("coo_amend");
                    $('#this_form').addClass("amend_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.amend_form', function(event) {
		event.preventDefault();

		var returnReason = $('#returnReason').val();

		if (returnReason == '') {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			const submitdata = {
				'operation': 'coo_amend',
				'returnReason': returnReason,
				'id': $('#id3').val()
			}
			$.ajax({
				type: "POST",
				url: "api/pettycash/action",
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
			url: 'api/get-single-pettycash-request-' + id,
			method: "GET",
			data: {
				id: id
			},
			dataType: "json",
			success: function(res) {
				if(res.success) {
					$('#actionModal').modal('show');
					$('#budg_refNo').html(res.data.refNo);
					$('#budg_category').html(res.data.category);
					$('#budg_totalAmount').html(res.data.totalAmount);
					$('#budg_requestBy').html(res.data.requestBy);
					$('#budg_requestDate').html(res.data.requestDate);

					$('#action_title').html('Rejection Reason');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<textarea class="form-control" rows="4" name="rejectReason" id="rejectReason" required data-error="Reason is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text(" Request Rejection");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Reject Request");
                    $('#operation3').val("reject");
                    $('#this_form').addClass("reject_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

    $(document).on('submit', '.reject_form', function(event) {
		event.preventDefault();

		var rejectReason = $('#rejectReason').val();

		if ( rejectReason == '' ) {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			const submitdata = {
				'operation': 'reject',
				'rejectReason': rejectReason,
				'id': $('#id3').val()
			}
			$.ajax({
				type: "POST",
				url: "api/pettycash/action",
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

	$(document).on('click', '.reject_budget', function() {
		var id = $(this).attr("id");
		$('#this_form1').removeClass();

		$('#reasonModal').modal('show');
		$('#action_title1').html('Budget Rejection Reason');
		$("#action_body1").empty();
		var action_body = 
		'<div class="" id="row">'+
			'<div class="col-sm-12 col-md-12">'+
				'<div class="mb-3 mt-2">'+
					'<textarea class="form-control" rows="4" name="approver_comment" id="approver_comment"></textarea>'+
				'</div>'+
			'</div>'+
		'</div>';
		$("#action_body1").append( $(action_body).hide().delay(100).fadeIn(300) );

		$('.title-of-modal').text(" Request Rejection");
		$('#id4').val(id);
		$('#action4').show();
		$('#action4').val("Reject Request");
		$('#operation4').val("reject_budget");
		$('#this_form1').addClass("reject_budget_form");
	});

	$(document).on('submit', '.reject_budget_form', function(event) {
		event.preventDefault();

		var approver_comment = $('#approver_comment').val();
		var id = $('#id4').val();
		
		if ( approver_comment == '' ) {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			const submitdata = {
				'operation': 'reject_budget',
				'approver_comment': approver_comment,
				'id': id
			}
			$.ajax({
				type: "POST",
				url: "api/pettycash/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader4").show();
				},
				success: function(res) {

					if(res.success) {
						$('#this_form1')[0].reset();
						$('#this_form1').removeClass();
						$("#action4").hide();
						$('#reasonModal').modal('hide');
						Swal.fire('Success.', res.message, 'success')
					} else {
						Swal.fire('Error.', res.message, 'error')
					}
						
					$("#loader4").hide();
					dataTable3.ajax.reload();
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
					$("#loader4").hide();
				},
			});
		}
	});

	var cacheData;
	var data = $('#autoFinaBal').html();
	var auto_refresh = setInterval(
	function ()
	{
		$.ajax({
			url: 'api/get-cashbox-balance',
			type: 'GET',
			data: data,
			dataType:"json",
			success: function(data) {
				if (data !== cacheData){
					cacheData = data;
					$('#autoFinaBal').html(data.amount);
				}         
			}
		})
	}, 1000); 

	
});