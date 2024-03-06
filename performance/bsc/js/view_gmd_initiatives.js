
$(document).ready(function(){

	$('#initiative_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#initiative_form')[0].reset();
		$('.modal-title').text("Add Initiative");
		$("#department").val('').trigger('change');	
		$("#pillar_id").val('').trigger('change');	

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
		location.href = "annual-division-bsc";
	}

	var strat_id = null;
	$.ajax({
		url: 'api/get-single-bsc-' + param_id,
		method:"GET",
		dataType:"json",
		async : false, 
		success:function(res) {				
			if(res.success) {
				strat_id = res.data.group_strategy_id;

				$('#viewName').html(res.data.bsc_owner_name);
				$('#viewCountry').html(res.data.country_name);
				$('#viewDepartment').html(res.data.department_name);
				$('#viewYear').html(res.data.year);
			} else {
				location.href = "annual-division-bsc";
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
				location.href = "annual-division-bsc";
			}
		}
	});
	
	var dataTable = $('#table_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-bsc-gmd-initiatives',
			'data': {
				stratId: param_id,
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

	$(document).on('submit', '#initiative_form', function(event){
		event.preventDefault();
		var pillar_id = $('#pillar_id').val();
		var initiative = $('#initiative').val();
		var target = $('#target').val();
		var value_impact = $('#value_impact').val();
		var timeline = $('#timeline').val();
		var measure = $('#measure').val();
		var figure = $('#figure').val();
		var weight = $('#weight').val();

		if( (pillar_id == '') || (initiative == '') || (target == '') || (value_impact == '') 
            || (timeline == '')  || (measure == '')  || (figure == '')  || (weight == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'pillar_id': pillar_id,
				'initiative': initiative,
				'target': target,
				'value_impact': value_impact,
				'timeline': timeline,
				'measure': measure,
				'figure': figure,
				'weight': weight,
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
		console.log(id);	
		$.ajax({
			url:"api/get-single-bsc-initiative-" + id,
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
					$('#measure').val(res.data.measure).trigger('change');
					$('#figure').val(res.data.figure);
					$('#weight').val(res.data.weight);
					$('#value_impact').val(res.data.value_impact);

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

	$('#comment_button').click(function(){

		$('.modal-title').text("Comments");
		$("#overall_comment").val('').trigger('change');	

		var ctry_stra_id = param_id;

		repeatAjax();

		function repeatAjax(){
			$.ajax({
				url: 'api/get-departmental-strategy-comments-' + ctry_stra_id,
				method:"GET",
				dataType:"json",
				beforeSend: function() {
					$("#loader5").show();
				},
				success:function(res) {	
					$("#loader5").hide();			
					if(res.success) {

						$("#cmtSection").empty();
						if(res.data.length > 0) {
							$.each(res.data , function(index, item) { 
								var init = item.initiative !== null ? '<div class="quotedDiv"><i><b>Initiative:</b> ' + item.initiative + '</i></div>' : '';						
								var comments_returned = 
									'<div class="p-2 commDiv">'+
										'<div class="d-flex flex-row user-info"><img class="rounded-circle" src="assets/images/users/avatar.png" width="40">'+
											'<div class="d-flex flex-column justify-content-start  ms-2">'+
												'<span class="d-block text-primary">' + item.comment_by + '</span>'+
												'<span class="date text-black-50">' + item.comment_date + '</span>'+
											'</div>'+
										'</div>'+
										init
										+'<div class="mt-1">'+
											'<p class="comment-text">' + item.comment + '</p>'+
										'</div>'+
									'</div>'+
									'<hr class="bg-muted border-2 border-top border-muted" />';

									$("#cmtSection").append( comments_returned );
							});
						} else {
							$("#cmtSection").append( '<p class="comment-text">No Comments Added Yet</p>' );
						}

					} else {
						Swal.fire('Error.', res.message, 'error')
					}
				},
				complete: function() {
					setTimeout(repeatAjax,5000);
				}
			})
		}

		
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
					$('#view_initiative').html(res.data.initiative);
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
		var initiative_id = $('#initiative_id').val();

		if ( (csid !== null || csid !== '') &&(comment !== null || comment !== '') ) {

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


	$(document).on('click', '.evaluate', function() {
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

					$('#action_title').html('Enter Evaluated Figure');
					$("#action_body").empty();
					var action_body = 
					'<div class="row dynamic_row" id="row">'+
						'<div class="col-sm-12 col-md-12">'+
							'<div class="mb-3 mt-2">'+
								'<input type="number" min="0" step=".01" class="form-control" id="raw_score" name="raw_score">'+
							'</div>'+
						'</div>'+
					'</div>';
					$("#action_body").append( $(action_body).hide().delay(100).fadeIn(300) );

					$('.modal-title').text("BSC Evaluation");
					$('#id3').val(id);
					$('#action3').show();
					$('#action3').val("Save Changes");
					$('#operation3').val("bsc_evaluate");
					$('#this_form').addClass("evaluation_form");
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('submit', '.evaluation_form', function(event) {
		event.preventDefault();
		var raw_score = $('#raw_score').val();
		if (raw_score !== null && raw_score.trim() !== '') {
			const submitdata = {
				'operation': 'bsc_evaluate',
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
	
});