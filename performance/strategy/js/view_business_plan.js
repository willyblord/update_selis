
$(document).ready(function(){

	$('#initiative_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#initiative_form')[0].reset();
		$('.modal-title').text("Add Initiative");
		$("#department").val('').trigger('change');	
		$("#pillar_id").val('').trigger('change');	
		$("#initiative").val('').trigger('change');	

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
		location.href = "annual-business-plans";
	}

	var strat_id = null;
	$.ajax({
		url: 'api/get-single-division-strategy-' + param_id,
		method:"GET",
		dataType:"json",
		async : false, 
		success:function(res) {				
			if(res.success) {
				strat_id = res.data.group_strategy_id;
				
				$('#viewCountry').html(res.data.country_name);
				$('#viewDivision').html(res.data.division_name);
				$('#viewYear').html(res.data.year);
				
				if(res.data.status === "returnedFromCOO") {
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
				location.href = "annual-business-plans";
			}
		}
	});


	// Function to populate dropdown based on selected ID and URL
	function populateDropdown(targetId, url, nameKey, selectedId) {
		// Check if dropdown data is already cached
		var cachedData = sessionStorage.getItem(url);
		if (cachedData) {
			// If cached data exists, populate dropdown immediately
			populateDropdownWithOptions(targetId, JSON.parse(cachedData), nameKey, selectedId);
		} else {
			// Fetch dropdown data via AJAX
			$.ajax({
				url: url,
				method: 'GET',
				dataType: 'json',
				success: function(res) {
					// Cache the fetched dropdown data
					sessionStorage.setItem(url, JSON.stringify(res));
					// Populate the dropdown with the fetched data
					populateDropdownWithOptions(targetId, res, nameKey, selectedId);
				},
				error: function(xhr, status, error) {
					console.error('Error fetching data:', error);
				}
			});
		}
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
	populateDropdown('pillar_id', 'api/list-strategy-pillars-' + strat_id, 'pillar_name');

	// Event listeners for dropdown changes
	$('#pillar_id').change(function() {
		var pillar_id = $(this).val();
		if (pillar_id !== '') {
			populateDropdown('initiative', 'api/list-pillar-initiatives-' + pillar_id, 'group_initiative');
		} else {
			$('#initiative').empty().append($('<option>').attr('value', '').text('...'));
		}
	});

	//LOAD POPULATE BUSINESS CATEGORY 
	populateDropdown('business_category', 'api/list-business-categories', 'business_category');

	
	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-main-function-initiatives',
			'data': {
				stratId: param_id,
			}
		},
		'columns': [   
			{ data: 'pillar' },
			{ data: 'initiative' },
			{ data: 'target' },
			{ data: 'value_impact' },
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
		'lengthMenu': [[10, 25, 500, -1], [10, 25, 50, "All"]],
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$("#initiative").on("change",function(){

		var initiative_value = $(this).val();	
		if(initiative_value === '') {
			$('#own_initiative').attr('disabled', false);
			$('#business_category').attr('disabled', false);
			$('#own_initiative').val('').trigger('change');
			$('#business_category').val('').trigger('change');
			$('#own_init').show();
		}
		else if(initiative_value !== '') {
			$('#own_initiative').attr('disabled', true);
			$('#business_category').attr('disabled', true);
			$('#own_init').hide();
		}
	})


	$(document).on('submit', '#initiative_form', function(event){
		event.preventDefault();
		var pillar_id = $('#pillar_id').val();
		var initiative = $('#initiative').val();
		var business_category = $('#business_category').val();
		var own_initiative = $('#own_initiative').val();
		var target = $('#target').val();
		var value_impact = $('#value_impact').val();
		var timeline = $('#timeline').val();

		if( (pillar_id == '') || (initiative == ''&& own_initiative == '') || (target == '') || (value_impact == '') || (timeline == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'pillar_id': pillar_id,
				'initiative': initiative,
				'business_category': business_category,
				'own_initiative': own_initiative,
				'target': target,
				'value_impact': value_impact,
				'timeline': timeline,
				'country_strategy_id':param_id,
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
				url: "api/create-initiative-country",
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
			
		$.ajax({
			url:"api/get-single-initiative-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addModal').modal('show');
					$('#pillar_id').val(res.data.pillar_id).trigger('change');
					$('#initiative').val(res.data.initiative);
					$('#target').val(res.data.target);
					$('#value_impact').val(res.data.value_impact);
					$('#timeline').val(res.data.timeline);
					$('#value_impact').val(res.data.value_impact);

					$('.modal-title').text("Edit Initiative");
					$('#id2').val(id);
					$("#action").show();
					$('#action').val("Save Changes");
					$('#operation').val("Edit");

					// Populate dropdowns with selected IDs
					populateDropdown('initiative', 'api/list-pillar-initiatives-' + res.data.pillar_id, 'group_initiative', res.data.initiative_id);
					

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
			url: 'api/get-single-initiative-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');

					$('.modal-title').text("View Initiatives");
					$('#view_country').html(res.data.country);
					$('#view_department').html(res.data.department);
					$('#view_pillar').html(res.data.strategy_pillar);
					$('#view_initiative').html(res.data.initiative);
					$('#view_target').html(res.data.target);
					$('#view_value_impact').html(res.data.value_impact);
					$('#view_timeline').html(res.data.timeline);
					$('#view_created_by').html(res.data.created_by);
					$('#view_created_at').html(res.data.created_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('#view_updated_at').html(res.data.updated_at);

					// Comments Section
					$("#comment").val('').trigger('change');	
					$('#initiative_id').val(id);
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
				url: 'api/get-departmental-strategy-comments-' + init_id,
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

		var comment = $('#comment').val();
		var initiative_id = $('#initiative_id').val();

		if ( (initiative_id !== null || initiative_id !== '') &&(comment !== null || comment !== '') ) {

			const submitdata = {
				'operation': 'add_initiative_comment',
				'comment': comment,
				'initiative_id': initiative_id
			}
			$.ajax({
				type: "POST",
				url: "api/strategy/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader4").show();
				},
				success: function(data) { 
					if(data.success) {					
						$("#comment").val('').trigger('change');	
						$("#action4").hide();
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



	// =====UPLOADS=========

	$('#mass_upload_button').click(function(){
		$('#mass_upload_form')[0].reset();
		$('.modal-title').text("Initiatives Mass Upload");
		$('#action4').val("Upload");
		$('#operation4').val("Upload");
		$('#ctry_strat_id').val(param_id);

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
            url:"api/upload-departmental-initiatives",
            method:'POST',
			data:new FormData(this),
			contentType:false,
			processData:false,
			beforeSend: function(){
				$("#loader6").show();
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