
$(document).ready(function(){

	$('#strategy_pillar_form .select2').select2({
        dropdownParent: $('.modals')
    });
	$('#initiative_form .select2').select2({
        dropdownParent: $('.modalsss')
    });
	
	
	$('#add_value_button').click(function(){
		$('#strategy_value_form')[0].reset();
		$('.modal-title').text("Add Value");

		$('#action').val("Submit");
		$('#operation').val("Add");
	});
	
	$('#add_initiative_button').click(function(){
		$('#initiative_form')[0].reset();
		$('.modal-title').text("Add Initiative");
		$('#pillar_id').val('').trigger('change');
		$('#business_category').val('').trigger('change');

		$('#action3').val("Submit");
		$('#operation3').val("Add");
	});

    $('#add_pillar_button').click(function(){
		$('#strategy_pillar_form')[0].reset();
		$('.modal-title').text("Add Pillar");
		$('#strategy_pillar').val('').trigger('change');
		$('#action1').val("Submit");
		$('#operation1').val("Add");
	});

	var url = window.location.pathname;
	var extracted = url.substring(url.lastIndexOf('-') + 1);
	let isnum = /^\d+$/.test(extracted);	

	if(isnum) {
		var param_id = extracted;
	}
	else {
		location.href = "strategy-3-year";
	}
	
	$.ajax({
		url: 'api/get-single-strategy-' + param_id,
		method:"GET",
		dataType:"json",
		success:function(res) {				
			if(res.success) {
				$('#view_year_range').html(res.data.year_range);
				$('#view_vision').html(res.data.vision);
				$('#view_mission').html(res.data.mission);
			} else {
				location.href = "strategy-3-year";
			}
		}
	});
	
	//LOAD POPULATE BUSINESS CATEGORY 
	$.ajax({
		url:"api/list-business-categories",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#business_category').append($('<option/>').attr("value", option.id).text(option.business_category));
			});
		}
	})

	//LOAD POPULATE PILLARS 
	$.ajax({
		url:"api/list-pillars",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#strategy_pillar').append($('<option/>').attr("value", option.id).text(option.pillar_name));
			});
		}
	})

	//LOAD POPULATE STRATEGY PILLARS 
	$.ajax({
		url:"api/list-strategy-pillars-" + param_id,
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#pillar_id').append($('<option/>').attr("value", option.id).text(option.pillar_name));
			});
		}
	})
	
	var dataTable = $('#value_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/strategy/get-all-values',
			'data': {
				stratId: param_id,
			}
		},
		'columns': [   
			{ data: 'value_title' },
			{ data: 'value_description' },
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 4 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[6, 10, 25, 500, -1], [6, 10, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.value_title ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#b01c2e" }); }
		}
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 

	
	var dataTable2 = $('#initiative_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/strategy/get-all-group-initiatives',
			'data': {
				stratId: param_id,
			}
		},
		'columns': [   
			{ data: 'group_initiative' },
			{ data: 'strategy_pillar' },
			{ data: 'business_category' },
			{ data: 'target' },
			{ data: 'timeline' },
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 7 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.group_initiative ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#b01c2e" }); }
		}
	});
	setInterval( function () {
		dataTable2.ajax.reload( null, false );
	}, 30000 ); 
	
	var dataTable1 = $('#pillar_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/strategy/get-all-group-pillars',
			'data': {
				stratId: param_id,
			}
		},
		'columns': [   
			{ data: 'strategy_pillar' },
			{ data: 'strategic_objective' },
			{ data: 'picture_of_success' },
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 5 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.strategy_pillar ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#b01c2e" }); }
		}
	});
	setInterval( function () {
		dataTable1.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$(document).on('submit', '#strategy_value_form', function(event){
		event.preventDefault();
		var value_title = $('#value_title').val();
		var value_description = $('#value_description').val();

		if( (value_title == '') ||  (value_description == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'value_title':  value_title,
				'value_description': value_description,
				'stratId': param_id,
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
				url: "api/create-strategy-value",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#strategy_value_form')[0].reset();
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

	$(document).on('click', '.update_value', function(){
		var id = $(this).attr("id");	
		$.ajax({
			url:"api/get-single-strategyvalue-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addModal').modal('show');
					$('#value_title').val(res.data.value_title);
					$('#value_description').val(res.data.value_description);
					$('.modal-title').text("Edit Value");
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

	

	$(document).on('submit', '#initiative_form', function(event){
		event.preventDefault();
		var group_initiative = $('#group_initiative').val();
		var business_category = $('#business_category').val();
		var pillar_id = $('#pillar_id').val();
		var target = $('#target').val();
		var measure = $('#measure').val();
		var timeline = $('#timeline').val();

		if( (group_initiative == '') ||  (business_category == '') ||  (pillar_id == '')  
			||  (target == '')||  (measure == '') ||  (timeline == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'group_initiative':  group_initiative,
				'business_category': business_category,
				'pillar_id': pillar_id,
				'target': target,
				'measure': measure,
				'timeline': timeline,
				'id': $('#id5').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation3').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-group-initiative",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader3").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#initiative_form')[0].reset();
						$('#addInitiativeModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader3").hide();
					dataTable2.ajax.reload();
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

					$("#loader3").hide();
				},

			});


		}
	});
	
	$(document).on('click', '.update_initiative', function(){
		var id = $(this).attr("id");	
		$.ajax({
			url:"api/get-single-group-initiative-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addInitiativeModal').modal('show');
					$('#group_initiative').val(res.data.group_initiative);
					$('#business_category').val(res.data.business_category).trigger('change');
					$('#pillar_id').val(res.data.pillar_id).trigger('change');
					$('#target').val(res.data.target);
					$('#measure').val(res.data.measure);
					$('#timeline').val(res.data.timeline);
					$('.modal-title').text("Edit Initiative");
					$('#id5').val(id);
					$("#action").show();
					$('#action3').val("Save Changes");
					$('#operation3').val("Edit");

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

	$(document).on('click', '.delete_initiative', function(){

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
				'operation': 'delete_group_initiative',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/strategy/action',
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
					dataTable2.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	$(document).on('submit', '#strategy_pillar_form', function(event){
		event.preventDefault();
		var strategy_pillar = $('#strategy_pillar').val();
		var strategic_objective = $('#strategic_objective').val();
		var picture_of_success = $('#picture_of_success').val();

		if( (strategy_pillar == '') ||  (strategic_objective == '')  ||  (picture_of_success == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'strategy_pillar':  strategy_pillar,
				'strategic_objective': strategic_objective,
				'picture_of_success': picture_of_success,
				'stratId': param_id,
				'id': $('#id3').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation1').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-strategy-grouppillar",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader1").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#strategy_pillar_form')[0].reset();
						$('#addPillarModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader1").hide();
					dataTable1.ajax.reload();
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

					$("#loader1").hide();
				},

			});


		}
	});

	$(document).on('click', '.update_pillar', function(){
		var idd = $(this).attr("id");	
		$.ajax({
			url:"api/get-single-strategygrouppillar-" + idd,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addPillarModal').modal('show');
					$('#strategy_pillar').val(res.data.strategy_pillar).trigger('change');
					$('#picture_of_success').val(res.data.picture_of_success);
					$('#strategic_objective').val(res.data.strategic_objective);
					$('.modal-title').text("Edit Pillar");
					$('#id3').val(idd);
					$("#action1").show();
					$('#action1').val("Save Changes");
					$('#operation1').val("Edit");

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

	$(document).on('click', '.delete_value', function(){

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
				'operation': 'delete_value',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/strategy/action',
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

	$(document).on('click', '.delete_pillar', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var idd = $(this).attr("id");
            const submitdata = {
				'operation': 'delete_pillar',
				'id': idd
			}

            $.ajax({
                type: "POST",
                url: 'api/strategy/action',
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
					dataTable1.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	// =====UPLOADS=========

	$('#mass_upload_button').click(function(){
		$('#mass_upload_form')[0].reset();
		$('.modal-title').text("Pillars Mass Upload");
		$('#action2').val("Upload");
		$('#operation2').val("Upload");
		$('#stratId').val(param_id);

	});

	$(document).on('submit', '#mass_upload_form', function(event){
		event.preventDefault();
		var extension = $('#csv_file').val().split('.').pop().toLowerCase();
		if(extension != '')
		{
			if(jQuery.inArray(extension, ['csv']) == -1)
			{
				Swal.fire('Error.', 'Invalid File, Only .csv file are allowed.', 'error')
				$('#csv_file').val('');
				return false;
			}
		}	
		$.ajax({
            url:"api/upload_pillars",
            method:'POST',
			data:new FormData(this),
			contentType:false,
			processData:false,
			beforeSend: function(){
				$("#loader2").show();
			},
            success:function(data)
            {
				if(data.success) {
					$('#mass_upload_form')[0].reset();
					$('#massuploadModal').modal('hide');

					Swal.fire('Success.', data.message, 'success')
				} else {
					Swal.fire('Error.', data.message, 'error')
				}

				$("#loader2").hide();
				dataTable1.ajax.reload();				
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
				$("#loader2").hide();
			},
        });
	});
	
});