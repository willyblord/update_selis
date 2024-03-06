
$(document).ready(function(){

	$('#my_request_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#my_request_form')[0].reset();
		$('.modal-title').text("Add Fund Request");

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
		url:"api/list-cash-categories",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#category').append($('<option/>').attr("value", option.category).text(option.category));
			});
		}
	})	


	// ADD NEW FIELDS BASED ON CATEGORY
	$('#category').change(function() {
		if ($("#category").val() === "Visit to providers") {
			$("#otherServer").show();
			$("#transAcc").show();
			$("#providers").prop('required', true);
			$("#otherServer1").hide();
			$("#otherAmountInput").html("Others");

		} else if ($("#category").val() === "Customer visit") {

			$("#otherServer1").show();
			$("#transAcc").show();
			$("#customers").prop('required', true);
			$("#otherServer").hide();
			$("#otherAmountInput").html("Others");

		} else {
			$("#otherServer").hide();
			$("#transAcc").hide();
			$("#otherServer1").hide();
			$("#otherAmountInput").html("Amount");
		}

		if ($("#category").val() === "Transport") {
			$("#checkinCheckout").show();
			$("#departure_date").prop('required', true);
			$("#return_date").prop('required', true);
		} else {
			$("#checkinCheckout").hide();
		}
	});

	// AUTO CALCULATE TOTAL AMOUNT REQUESTED
	var $total = $('#amount'), $value = $('.inputT');
	$value.on('input', function(e) {
		var total = 0;
		$value.each(function(index, elem) {
			if (!Number.isNaN(parseInt(this.value, 10)))
				total = total + parseInt(this.value, 10);
		});
		$total.val(total);
	});


	//LOAD POPULATE PROVIDERS 
	$.ajax({
		url:"api/list-providers",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#providers').append($('<option/>').attr("value", option.providerName).text(option.providerName));
			});
		}
	})

	//LOAD POPULATE CUSTOMERS 
	$.ajax({
		url:"api/list-customers",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#customers').append($('<option/>').attr("value", option.customerName).text(option.customerName));
			});
		}
	})
	
	
	
	var dataTable = $('#request_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-my-pettycash-requests'
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
	
	

	$(document).on('submit', '#my_request_form', function(event){
		event.preventDefault();			
			
		//post with ajax
		$.ajax({
			url: "api/create-pettycash-request",
			method: 'POST',
			data: new FormData(this),
			contentType: false,
			processData: false,
			beforeSend: function() {
				$("#loader").show();
			},
			success:function(data){ 

				if(data.success) {
					$('#my_request_form')[0].reset();
					$('#addRequestModal').modal('hide');

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
	});
	


	$(document).on('click', '.update', function(){
		var id = $(this).attr("id");
		console.log(id);	
		$.ajax({
			url:"api/get-single-pettycash-request-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	console.log(res);	
				if(res.success) {
					$('#addRequestModal').modal('show');
					$('#visitDate').val(res.data.visitDate);
					$('#category').val(res.data.category);
					$("#category").trigger('change');

					var arr_prov = res.data.providers_Val;
					$("#providers").val(arr_prov).trigger('change');

					var arr_cust = res.data.customers_Val;
					$("#customers").val(arr_cust).trigger('change');

					$('#transport').val(res.data.transport_Val);
					$('#accomodation').val(res.data.accomodation_Val);
					$('#meals').val(res.data.meals_Val);
					$('#other').val(res.data.otherExpenses_Val);
					$('#requester_charges').val(res.data.requester_charges_Val);
					$('#amount').val(res.data.totalAmount_Val);
					$('#air_departure_date').val(res.data.air_departure_date);
					$('#air_return_date').val(res.data.air_return_date);
					$('#checkin_date').val(res.data.checkin_date);
					$('#checkout_date').val(res.data.checkout_date);
					$('#description').val(res.data.description);
					$('#phone').val(res.data.phone);

					$('#thumbnail_doc').empty();
					$('#thumbnail_doc').append(
						$('<input/>').attr('type', 'hidden').attr('name', 'hidden_doc').attr('value', res.data.additional_doc)
					);

					$('.modal-title').text("Edit My Request");
					$('#id2').val(id);
					$("#action").show();
					$('#action').val("Save Changes");
					$('#operation').val("Edit");
					$("#otherServer1").show();

					$("#otherServer").hide();
					if (res.data.category == 'Visit to providers') {
						$("#otherServer").show();
						$("#providers").prop('required', true);
					}

					$("#otherServer1").hide();
					if (res.data.category == 'Customer visit') {
						$("#otherServer1").show();
						$("#customers").prop('required', true);
					}

					$("#airTravel").hide();
					if (res.data.category == 'Air Travel' || res.data.category == 'Transport') {
						$("#airTravel").show();
						$("#air_departure_date").prop('required', true);
						$("#air_return_date").prop('required', true);
					}

					

					$("#checkinCheckout").hide();
					if (res.data.category == 'Accomodation') {
						$("#checkinCheckout").show();
						$("#checkin_date").prop('required', true);
						$("#checkout_date").prop('required', true);
					}

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

	$(document).on('click', '.cancel', function() {
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

					$('#action_title').html('Cancellation Reason');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<textarea class="form-control" rows="4" name="cancelReason" id="cancelReason" required data-error="Reason is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text(" Request Cancellation");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Cancel Request");
					$('#operation3').val("cancel_req");
					$('#this_form').addClass("cancel_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.cancel_form', function(event) {
		event.preventDefault();
		var cancelReason = $('#cancelReason').val();
		if (cancelReason !== null && cancelReason.trim() !== '') {
			const submitdata = {
				'operation': 'cancel_req',
				'cancelReason': cancelReason,
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
				success: function(data) {
					if(data.success) {
						$('#this_form')[0].reset();
						$('#this_form').removeClass();
						$("#action3").hide();
						$('#actionModal').modal('hide');
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}
						
					$("#loader3").hide();
					dataTable.ajax.reload();
				}
			});
		} else {
			Swal.fire('Error.', 'Please Input Reason', 'error')
		}
	});

	
	$(document).on('click', '.complete', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to complete this request?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'complete_req',
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

	$(document).on('click', '.clear', function() {
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

					$('#action_title').html('Reconciliation Details');
					$("#action_body").empty();
					var action_body = 
					'<div class="row" id="row">'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="amountGiven" class="form-label">Amount given</label>'+
								'<input type="number" class="form-control" name="amountGiven" id="amountGiven" value="0" disabled>'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="requesterCharges">Requester Transfer Charges</label>'+
								'<input type="number" class="form-control" name="requesterCharges" id="requesterCharges" value="0" disabled>'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="disburserCharges">Disburser Transfer Charges</label>'+
								'<input type="number" class="form-control" name="disburserCharges" id="disburserCharges" value="0" disabled>'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="amountUsed">Amount used</label>'+
								'<input type="number" min="0" class="form-control" name="amountUsed" id="amountUsed" data-error="Amount is required" required>'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="balance">Balance</label>'+
								'<input type="number" class="form-control" name="balance" id="balance" value="0" disabled>'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-4">'+
							'<div class="mb-3 mt-2">'+
								'<label for="amount">Receipt (Only images & PDF files)</label>'+
								'<input type="file" class="form-control" name="receipt" id="receipt" accept=".png,.jpg,.jpeg,.pdf" />'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<label for="reconciliation_comment">Comment</label>'+
								'<textarea class="form-control" rows="4" name="reconciliation_comment" id="reconciliation_comment" required data-error="Comment is required"></textarea>'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					var amountGiven = Number(res.data.transport_Val) + Number(res.data.accomodation_Val) + Number(res.data.meals_Val) + Number(res.data.otherExpenses_Val);
					$('#amountGiven').val(amountGiven);
					$('#requesterCharges').val(res.data.requester_charges_Val);
					$('#disburserCharges').val(res.data.charges_Val);

					function sub() {
						var num1 = document.getElementById('amountUsed').value;
						var num2 = document.getElementById('amountGiven').value;

						var result = parseInt(num2) - parseInt(num1);
						if (!isNaN(result)) {
							document.getElementById('balance').value = result;
						}
					}
					sub();
					$("#amountGiven, #amountUsed").on("keydown keyup", function() {
						sub();
					});

					$('.modal-title').text(" Reconciliation Request");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Submit");
					$('#operation3').val("clear_req");
					$('#this_form').addClass("clear_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.clear_form', function(event) {
		event.preventDefault();

		var amountUsed = $('#amountUsed').val();
		var reconciliation_comment = $('#reconciliation_comment').val();

		if ((amountUsed == '') || (reconciliation_comment == '')) {
			Swal.fire('Error.', 'Please Input Required Fields', 'error')
		} else {
			$.ajax({
				url: "api/pettycash/action",
				method: 'POST',
				data: new FormData(this),
				contentType: false,
				processData: false,
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
	
		
	$(document).on('click', '.resend', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to resend this request?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'resend_req',
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