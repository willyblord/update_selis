
$(document).ready(function(){

	$('#initiative_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#initiative_form')[0].reset();
		$('.modal-title').text("Add Initiative");
		// $("#depart_initiative_id").val('').trigger('change');	
		$('#bsc_parameter').val('').trigger('change');
		$('#pillar_id').val('').trigger('change');
		$('#initiative_id').val('').trigger('change');

		$('#action').val("Save");
		$('#operation').val("Add");
	});

	var url = window.location.pathname;
	var extracted = url.substring(url.lastIndexOf('-') + 1);
	let isnum = /^\d+$/.test(extracted);	

	if(isnum) {
		var param_id = extracted;
	}
	else {
		location.href = "annual-individual-bsc";
	}

	var ctry_id = null;
	var depart_id = null;
	var strat_year = null;
	var bscOwner = null;
	var group_strategy_id = null;
	$.ajax({
		url: 'api/get-single-bsc-' + param_id,
		method:"GET",
		dataType:"json",
		async : false, 
		success:function(res) {				
			if(res.success) {
				ctry_id = res.data.country;
				depart_id = res.data.department;
				strat_year = res.data.year;
				bscOwner = res.data.bsc_owner;
				group_strategy_id = res.data.group_strategy_id;

				$('#viewName').html(res.data.bsc_owner_name);
				$('#viewCountry').html(res.data.country_name);
				$('#viewDepartment').html(res.data.department_name);
				$('#viewYear').html(res.data.year);

				if(res.data.status === "returnedFromHOD" || res.data.status === "returnedFromCountry" || res.data.status === "returnedFromHR") {
					$('#returnNoti').show();

					$('#viewReturnBy').html(res.data.returned_by);
					$('#viewReturnAt').html(res.data.returned_at);
					$('#viewReturnReason').html(res.data.return_reason);
				}

				if(res.data.status === "rejected") {
					$('#rejectNoti').show();

					$('#viewRejectBy').html(res.data.rejected_by);
					$('#viewRejectAt').html(res.data.rejected_at);
					$('#viewRejectReason').html(res.data.reject_reason);
				}

			} else {
				location.href = "annual-individual-bsc";
			}
		}
	});

	// Load scores
	$.ajax({
		url: 'api/get-individual-bsc-scores-' + param_id,
		method:"GET",
		dataType:"json",
		async : false, 
		success:function(res) {				
			if(res.success) {

				var t_target_scrore = res.data.target_score !== null ? res.data.target_score : 0
				var t_weight = res.data.achieved_weight !== null ? res.data.achieved_weight : 0
				var a_target_scrore = res.data.hr_target_score !== null ? res.data.hr_target_score : 0
				var a_weight = res.data.hr_computed_score !== null ? res.data.hr_computed_score : 0

				$('#t_target_scrore').html(t_target_scrore + '%');
				$('#t_weight').html(t_weight + '%');
				$('#a_target_scrore').html(a_target_scrore + '%');
				$('#a_weight').html(a_weight + '%');
			} else {
				location.href = "annual-individual-bsc";
			}
		}
	});


	// Function to populate dropdown based on selected ID and URL
	function populateDropdown(targetId, url, nameKey, selectedId) {
		// Append a version or timestamp to the URL to create a unique cache key
		var cacheKey = url + '_v1'; // Example: Append a version number
	
		// Fetch dropdown data via AJAX
		$.ajax({
			url: url,
			method: 'GET',
			dataType: 'json',
			success: function(res) {
				
				// Remove deleted records from the cached list
				var cachedData = sessionStorage.getItem(cacheKey);
				if (cachedData) {
					var cachedList = JSON.parse(cachedData);
					var updatedList = cachedList.filter(function(item) {
						// Check if the item exists in the fetched data
						return res.some(function(newItem) {
							return newItem.id === item.id;
						});
					});
					// Cache the updated dropdown data
					sessionStorage.setItem(cacheKey, JSON.stringify(updatedList));
				}
				// Populate the dropdown with the fetched data
				populateDropdownWithOptions(targetId, res, nameKey, selectedId);
			},
			error: function(xhr, status, error) {
				console.error('Error fetching data:', error);
			}
		});
	}
	
	// Function to populate dropdown with options
	function populateDropdownWithOptions(targetId, data, nameKey, selectedId) {
		var dropdown = $('#' + targetId);
		dropdown.empty().append($('<option>').attr('value', '').text('...'));
		$.each(data, function(i, option) {
			var optionElement = $('<option>').attr('value', option.id).text(option[nameKey]);
			if (option.id == selectedId) {
				optionElement.attr('selected', 'selected');
			}
			dropdown.append(optionElement);
		});
	}
	

	//LOAD POPULATE PILLARS 
	populateDropdown('bsc_parameter', 'api/list-parameters-per-bsc-' + param_id, 'bsc_parameter_name');

	// Event listeners for dropdown changes
	$('#bsc_parameter').change(function() {
		var bsc_parameter_val = $(this).val();
		if (bsc_parameter_val !== '') {
			populateDropdown('pillar_id', 'api/list-pillars-per-parameter-' + bsc_parameter_val, 'pillar_name');
		} else {
			$('#pillar_id').empty().append($('<option>').attr('value', '').text('...'));
		}
	});

	$('#pillar_id').change(function() {
		var pillar_id_val = $(this).val();
		if (pillar_id_val !== '') {
			populateDropdown('initiative_id', 'api/list-by-level-threeyear-initiatives-' + pillar_id_val, 'group_initiative');
		} else {
			$('#initiative_id').empty().append($('<option>').attr('value', '').text('...'));
		}
	});


	//LOAD POPULATE 
	populateDropdown('bsc_parameter_id', 'api/list-bsc-parameters', 'bsc_parameter_name');
	populateDropdown('business_category', 'api/list-business-categories', 'business_category');
	
	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-bsc-individual-initiatives',
			'data': {
				bscId: param_id,
			}
		},
		'columns': [   
			{ data: 'bsc_parameter_name' },
			{ data: 'initiative' },
			{ data: 'target' },
			{ data: 'timeline' },
			{ data: 'measure' },
			{ data: 'figure' },
			{ data: 'weight' },
			{ data: 'raw_score' },
			{ data: 'target_score' },
			{ data: 'computed_score' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 10 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.figure ) { $(row).find('td:eq(5)').css({ "font-weight": "bold", "color": "#390505" }); }
			if ( data.weight ) { $(row).find('td:eq(6)').css({ "font-weight": "bold", "color": "#b01c2e" }); }
			if ( data.raw_score ) { $(row).find('td:eq(7)').css({ "font-weight": "bold", "color": "#005ca4" }); }
			if ( data.target_score ) { $(row).find('td:eq(8)').css({ "font-weight": "bold", "color": "#009114" }); }
			if ( data.computed_score ) { $(row).find('td:eq(9)').css({ "font-weight": "bold", "color": "#8b0063" }); }
			if ( data.timeline_update && (data.timeline < data.timeline_update) ) { $(row).addClass('timelineOverdue');}
			if ( data.timeline_update && (data.timeline == data.timeline_update) ) { $(row).addClass('timelineWarning');}
			if ( data.timeline_update && (data.timeline > data.timeline_update) ) { $(row).addClass('timelineOkay');}
			
		}
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 

	var dataTable1 = $('#parameters_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-individual-bsc-parameters',
			'data': {
				bscId: param_id,
			}
		},
		'columns': [   
			{ data: 'bsc_parameter_name' },
			{ data: 'parameter_weight' },
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_parameter"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},       
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_parameter"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
					}
					return data;
				}
			},
		],
		'columnDefs':[
			{
				"targets": [ 2, 3 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.bsc_parameter_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#b01c2e" }); }
			if ( data.parameter_weight ) { $(row).find('td:eq(1)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
		}
	});
	setInterval( function () {
		dataTable1.ajax.reload( null, false );
	}, 30000 ); 	
	
	

	$("#measure").on("change",function(){

		$measure_value = $(this).val();
	
		if($measure_value === 'Qualitative') {
			$('#figure').val(100);
			$('#figure').attr('disabled', true);
		}
		else {
			$('#figure').val('');
			$('#figure').attr('disabled', false);
		}
	
	})

	$("#initiative_id").on("change",function(){

		var initiative_value = $(this).val();	
		if(initiative_value === '') {
			$('#own_initiative').attr('disabled', false);
			$('#business_category').attr('disabled', false);
			$('#own_initiative').val('').trigger('change');
			$('#business_category').val('').trigger('change');
			$('#own_init').show();
		}
		else {
			$('#own_initiative').attr('disabled', true);
			$('#business_category').attr('disabled', true);
			$('#own_init').hide();
		}
	})

	$(document).on('submit', '#initiative_form', function(event){
		event.preventDefault();
		var pillar_id = $('#pillar_id').val();
		var bsc_parameter = $('#bsc_parameter').val();
		var initiative_id = $('#initiative_id').val();
		var business_category = $('#business_category').val();
		var own_initiative = $('#own_initiative').val();
		var target = $('#target').val();
		var value_impact = $('#value_impact').val();
		var timeline = $('#timeline').val();
		var measure = $('#measure').val();
		var figure = $('#figure').val();
		var weight = $('#weight').val();

		if( (bsc_parameter == '') || (pillar_id == '') || (initiative_id == '' && own_initiative == '') || (target == '') || (value_impact == '') 
            || (timeline == '')  || (measure == '')  || (figure == '')  || (weight == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'pillar_id': pillar_id,
				'bsc_parameter': bsc_parameter,
				'initiative_id': initiative_id,
				'business_category': business_category,
				'own_initiative': own_initiative,
				'target': target,
				'value_impact': value_impact,
				'timeline': timeline,
				'measure': measure,
				'figure': figure,
				'weight': weight,
				'individual_bsc_id':param_id,
				'assigned_to':bscOwner,
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
				url: "api/create-bsc-initiative",
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

	$(document).on('submit', '#parameters_form', function(event){
		event.preventDefault();
		var bsc_parameter_id = $('#bsc_parameter_id').val();
		var parameter_weight = $('#parameter_weight').val();

		if( (bsc_parameter_id == '') || (parameter_weight == '')  || (param_id == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'bsc_parameter_id': bsc_parameter_id,
				'parameter_weight': parameter_weight,
				'individual_bsc_id':param_id,
				'parameter_id': $('#id5').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation5').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-individual-bsc-parameter",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader5").show();
				},

				success:function(data){ 

					if(data.success) {						
						$('#bsc_parameter_id').val('').trigger('change');
						$('#parameters_form')[0].reset();

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader5").hide();
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

					$("#loader5").hide();
				},

			});


		}
	});


	$(document).on('click', '.update', function(){
		var id = $(this).attr("id");

		$('#cover-spin').show();

		$.ajax({
			url:"api/get-single-bsc-initiative-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) { 
				if(res.success) { 
					$('#addModal').modal('show');
					$('#bsc_parameter').val(res.data.bsc_parameter_id).trigger('change');
					$('#pillar_id').val(res.data.pillar_id).trigger('change');
					$('#initiative_id').val(res.data.initiative_id).trigger('change');
					$('#target').val(res.data.target);
					$('#value_impact').val(res.data.value_impact);
					$('#timeline').val(res.data.timeline);
					$('#measure').val(res.data.measure).trigger('change');
					$('#figure').val(res.data.figure);
					$('#weight').val(res.data.weight);
					$('#value_impact').val(res.data.value_impact);

					$('.modal-title').text("Edit Initiative");
					$('#id2').val(id);
					$("#action").show();
					$('#action').val("Save Changes");
					$('#operation').val("Edit");

					// Populate dropdowns with selected IDs
					populateDropdown('pillar_id', 'api/list-pillars-per-parameter-' + res.data.bsc_parameter_id, 'pillar_name', res.data.pillar_id);
					populateDropdown('initiative_id', 'api/list-by-level-threeyear-initiatives-' + res.data.pillar_id, 'group_initiative', res.data.initiative_id);

				} else {
					Swal.fire('Error.', res.message, 'error')
				}

				$('#cover-spin').hide();
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

	$(document).on('click', '.update_parameter', function(){
		var id = $(this).attr("id");
			
		$.ajax({
			url:"api/get-single-individual-bsc-parameter-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) { 
				if(res.success) { 

					$('#bsc_parameter_id').val(res.data.bsc_parameter_id).trigger('change');
					$('#parameter_weight').val(res.data.parameter_weight);
					$('#id5').val(id);
					$('#action5').val("Save Changes");
					$('#operation5').val("Edit");

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

    var autoRefreshInterval;
    $(document).on('click', '.view', function(){
		var id = $(this).attr("id");
		$.ajax({
			url: 'api/get-single-bsc-initiative-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');

					$('.modal-title').text("View Initiatives");
					$('#view_country').html(res.data.country);
					$('#view_department').html(res.data.department);
					$('#view_pillar').html(res.data.strategy_pillar);
					$('#view_pillar').html(res.data.pillar_name);
					$('#view_pillar').html(res.data.bsc_parameter_name);
					$('#view_initiative').html(res.data.group_initiative);
					$('#view_target').html(res.data.target);
					$('#view_value_impact').html(res.data.value_impact);
					$('#view_timeline').html(res.data.timeline);
					$('#view_measure').html(res.data.measure);
					$('#view_figure').html(res.data.view_figure);
					$('#view_weight').html(res.data.weight + '%');
					$('#view_raw_score').html(res.data.raw_score);
					$('#view_target_score').html(res.data.target_score + '%');
					$('#view_computed_score').html(res.data.computed_score + '%');
					$('#view_created_by').html(res.data.created_by);
					$('#view_created_at').html(res.data.created_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('#view_updated_at').html(res.data.updated_at);

					// Comments Section
					$("#comment").val('').trigger('change');	
					$('#initiativeId').val(id);
					$("#cmtSection").empty();

					startAutoRefresh(id);


				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})

		$("#loader4").show();
	});

	
	function startAutoRefresh(id) {
		var cacheData = null;
		autoRefreshInterval = setInterval(function () {
			var init_id = id;
			console.log(init_id)
					
			$.ajax({
				url: 'api/get-init-bsc-comments-' + init_id,
				method:"GET",
				dataType:"json",
				beforeSend: function() {
					// $("#loader5").show();
				},
				success:function(res) {
					$("#loader4").hide();			
					if(res.success) {
						if (res.data !== cacheData) { 
							cacheData = res.data;

							$("#cmtSection").empty();
							if(res.data.length > 0) {
								$.each(res.data , function(index, item) { 
									var comments_returned = 
										'<div class="p-2 commDiv">'+
											'<div class="d-flex flex-row user-info"><img class="rounded-circle" src="assets/images/users/avatar.png" width="40">'+
												'<div class="d-flex flex-column justify-content-start  ms-2">'+
													'<span class="d-block text-primary">' + item.comment_by + '</span>'+
													'<span class="date text-black-50">' + item.comment_date + '</span>'+
												'</div>'+
											'</div>'+
											'<div class="mt-1">'+
												'<p class="comment-text">' + item.comment + '</p>'+
											'</div>'+
										'</div>'+
										'<hr class="bg-muted border-2 border-top border-muted" />';

										$("#cmtSection").append( comments_returned );
								});
							} else {
								$("#cmtSection").append( '<p class="comment-text">No Comments Added Yet</p>' );
							}
						}

					} else {
						Swal.fire('Error.', res.message, 'error')
					}
				}
			})
		}, 3000);
	}

	$('#viewModal').on('hide.bs.modal', function () {
		if (autoRefreshInterval) {
			clearInterval(autoRefreshInterval);
		}
	});

	$('#submit_comment').click(function(){

		var csid = param_id;
		var comment = $('#comment').val();
		var initiative_id = $('#initiativeId').val();

		if ( (csid !== null || csid !== '') &&(comment !== null || comment !== '')  &&(initiative_id !== null || initiative_id !== '')) {

			const submitdata = {
				'operation': 'add_initiative_comment',
				'country_strategy_id': csid,
				'comment': comment,
				'initiative_id': initiative_id
			}
			$.ajax({
				type: "POST",
				url: "api/bsc/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader4").show();
				},
				success: function(data) {
					if(data.success) {					
						$("#comment").val('').trigger('change');	
						$("#loader4").hide();
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}
						
					$("#loader4").hide();
				}
			});
		} else {
			Swal.fire('Error.', 'Please Input the Figure', 'error')
		}
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
				'operation': 'delete_initiative_dp',
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


	$(document).on('click', '.delete_parameter', function(){

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
				'operation': 'delete_parameter',
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
					dataTable1.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	$(document).on('click', '.update_progress', function() {
		var id = $(this).attr("id");
		$('#this_form').removeClass();
		$.ajax({
			url: 'api/get-single-bsc-initiative-' + id,
			method: "GET",
			data: {
				id: id
			},
			dataType: "json",
			success: function(res) {
				if(res.success) {
					$('#actionModal').modal('show');
					$('#act_initiative').html(res.data.initiative);
					$('#act_target').html(res.data.target);
					$('#act_timeline').html(res.data.timeline);
					$('#act_measure').html(res.data.measure);
					$('#act_figure').html(res.data.view_figure);

					$('#action_title').html('Enter Updated Figure');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<input type="number" min="0" step=".01" class="form-control" id="raw_score" name="raw_score">'+
							'</div>'+
						'</div>'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<input type="text" class="form-control" id="" name="" placeholder="URL">'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text("Progress");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Save Changes");
					$('#operation3').val("update_initiative_progress");
					$('#this_form').addClass("progress_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.progress_form', function(event) {
		event.preventDefault();
		var raw_score = $('#raw_score').val();
		if (raw_score !== null && raw_score.trim() !== '') {
			const submitdata = {
				'operation': 'update_initiative_progress',
				'raw_score': raw_score,
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
			Swal.fire('Error.', 'Please Input the Figure', 'error')
		}
	});

// ============= UPLOAD INITIATIVES ==============

	$('#mass_upload_button').click(function(){
		$('#mass_upload_form')[0].reset();
		$('.modal-title').text("Upload Targets");
		$('#preview_uploaded_data').empty();
		$('#add-row').hide();		
		$('#action').val("Save");
		$('#operation').val("Add");
	});

    // Predefined options for the dropdown select
	var predefinedOptions = ["Quantitative Financial", "Quantitative Count", "Quantitative Parcentage", "Qualitative"];

	// Function to generate options for the dropdown select
	function generateOptions(selectedValue) {
		var options = '<option value="">...</option>'; // Default empty option
		predefinedOptions.forEach(function(option) {
			if (option === selectedValue) {
				options += '<option value="' + option + '" selected>' + option + '</option>';
			} else {
				options += '<option value="' + option + '">' + option + '</option>';
			}
		});
		return options;
	}

	$('#csv_file').change(function(event) {
		var file = event.target.files[0];
		if (file) {
			$('#add-row').show();    
			var reader = new FileReader();
			reader.onload = function(e) {
				var csv = e.target.result;
				var rows = csv.split("\n");
				var headerRow = rows[0].trim(); // Get the first row (header row) of the CSV
				
				// Define expected column names
				var expectedColumnNames = ["TARGET", "OBJECTIVE", "TIMELINE", "MEASURE", "FIGURE_COUNT_PERCENTAGE", "WEIGHT"];
				
				// Extract column names from header row
				var columnNames = headerRow.split(",");
				
				// Check if column names match expected names
				var isValid = true;
				if (columnNames.length !== expectedColumnNames.length) {
					isValid = false;
				} else {
					for (var i = 0; i < columnNames.length; i++) {
						if (columnNames[i].trim() !== expectedColumnNames[i]) {
							isValid = false;
							break;
						}
					}
				}
				
				// Display appropriate message based on validation result
				if (isValid) {
					// Proceed with further processing
					// console.log("Column names are valid.");
					
					// Continue with your existing code to populate the preview table or perform other actions
					var table = '<table id="table_preview_csv" class="table table-sm w-100">';
					for (var i = 0; i < rows.length; i++) {
						var cells = rows[i].split(",");
						if (i === 0) { // Check if it's the first row (headers)
							table += '<tr>';
							for (var j = 0; j < cells.length; j++) {
								table += '<th>' + cells[j] + '</th>';
							}
							table += '</tr>';
						} else if (cells.length > 1 || cells[0].trim() !== "") { // Exclude the last empty row
							table += '<tr>';
							for (var j = 0; j < cells.length; j++) {
								var cellValue = cells[j].trim(); // Trim cell value
								if (j === 2) { // Datepicker in the third column
									table += '<td><div class="input-group"><input type="text" class="form-control" value="' + cellValue + '"></div></td>';
								} else if (j === 3) { // Dropdown select in the fourth column
									table += '<td><select class="form-control">' + generateOptions(cellValue) + '</select></td>';
								} else if (j === 5) { // Text field in the sixth column
									table += '<td><input type="text" class="form-control" value="' + cellValue + '"></td>';
								} else if (j !== cells.length - 1) { // Skip the last cell which is auto-added
									table += '<td><input type="text" class="form-control" value="' + cellValue + '"></td>';
								}
							}
							table += '<td><button class="btn btn-danger btn-sm remove-row">Remove</button></td>';
							table += '</tr>';
						}
					}
					table += '</table>';
					$('#preview_uploaded_data').html(table);
					
					// Initialize datepicker
					$('.datepicker').datepicker({
						format: 'yyyy-mm-dd',
						autoclose: true,
						todayHighlight: true
					});
				} else {
					// Prevent submission and inform the user
					Swal.fire('Error.', 'Column names do not match expected format. Please download the template and use it instead.', 'error')
				}
			};
			reader.readAsText(file);
		}
	});
	
	
	
	

	// Remove additional remove button below the table
	$('#remove-below').remove();

	// Add Row button click event handler
	$('#add-row').click(function() {
		var newRow = '<tr>';
		newRow += '<td><input type="text" class="form-control" value=""></td>';
		newRow += '<td><input type="text" class="form-control" value=""></td>';
		newRow += '<td><div class="input-group "><input type="text" class="form-control"></div></td>';
		newRow += '<td><select class="form-control">' + generateOptions('') + '</select></td>';
		newRow += '<td><input type="text" class="form-control" value=""></td>';
		newRow += '<td><input type="text" class="form-control" value=""></td>';
		newRow += '<td><button class="btn btn-danger btn-sm remove-row">Remove</button></td>';
		newRow += '</tr>';
		$('#table_preview_csv').append(newRow);
		
		// Initialize datepicker for the new row
		$('.datepicker').datepicker({
			format: 'yyyy-mm-dd',
			autoclose: true,
			todayHighlight: true
		});
	});

	// Remove Row button click event handler
	$(document).on('click', '.remove-row', function() {
		$(this).closest('tr').remove();
	});


	$(document).on('submit', '#mass_upload_form', function(event){
		event.preventDefault();
		// Create an array to store the table data
		var tableData = [];
		// Iterate over each row in the table
		$('#preview_uploaded_data table tbody tr:not(:first)').each(function() {
			var rowData = {};
			var isEmptyRow = true; // Assume the row is empty initially
		
			// Iterate over each cell in the row
			$(this).find('td').each(function(index) {
				// Skip the last cell (remove button)
				if (index < $(this).closest('tr').find('td').length - 1) {
					var cellValue = $(this).find('input, select').val().trim(); // Trim whitespace characters
					var cellName = $(this).closest('table').find('th').eq(index).text().trim(); // Trim the key
					rowData[cellName] = cellValue;
		
					// Check if the cell is not empty
					if (cellValue !== '') {
						isEmptyRow = false; // Set the flag to false if any cell is not empty
					}
				}
			});
		
			// Add the rowData object to the tableData array if the row is not empty
			if (!isEmptyRow) {
				tableData.push(rowData);
			}
		});
		
		// console.log('Table Data:', tableData); // Log tableData array before sending via AJAX
	
		// Send the tableData array to the server using AJAX
		$.ajax({
			type: "POST",
			url: "api/upload-bsc-initiatives",
			data: { tableData: JSON.stringify(tableData) },	
			success:function(data){ 

				if(data.success) {
					$('#mass_upload_form')[0].reset();
					$('#massuploadModal').modal('hide');
					$('#preview_uploaded_data').empty();

					Swal.fire('Success.', data.message, 'success')
				} else {
					Swal.fire('Error.', data.message, 'error')
				}

				$("#loader6").hide();
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

				$("#loader6").hide();
			},
		});
	});
	
	
	
	
});