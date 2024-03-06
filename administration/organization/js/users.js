
$(document).ready(function(){

	$('#user_form .select2').select2({
        dropdownParent: $('.modals')
    });
	
	$('#add_button').click(function(){
		$('#user_form')[0].reset();
		$('.modal-title').text("Add User");
		$('#action').val("Register");
		$('#operation').val("Add");
		$("#country").val('').trigger('change');
		$("#department").val('').trigger('change');
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

	//LOAD POPULATE DEPARTMENTS 
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
	
	var dataTable = $('#user_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-users'
		},
		'columns': [   
			{ data: 'names'},
			{ data: 'country' },
			{ data: 'dept_name' },
			{ data: 'dept_name' },
			{ data: 'dept_name' },
			{ data: 'dept_name' },
			{ data: 'status' },
			{ data: 'registerDate' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 7 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$(document).on('submit', '#user_form', function(event){
		event.preventDefault();
		var name = $('#name').val();
		var surname = $('#surname').val();
		var gender = $('#gender').val();
		var email = $('#email').val();
		var phone = $('#phone').val();
		
		if( (name == '') ||  (surname == '') || (gender == '') || (email == '') || (phone == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'name':  $('#name').val(),
				'surname': $('#surname').val(),
				'staffNumber': $('#staffNumber').val(),
				'country': $('#country').val(),
				'department': $('#department').val(),
				'email': $('#email').val(),
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
				url: "api/create-user",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#user_form')[0].reset();
						$('#addUserModal').modal('hide');

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
			url:"api/get-single-user-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#addUserModal').modal('show');
					$('#name').val(res.data.name);
					$('#surname').val(res.data.surname);
					$('#staffNumber').val(res.data.staffNumber);
					$('#country').val(res.data.country_val).trigger('change');
					$('#department').val(res.data.department_val).trigger('change');
					$('#email').val(res.data.email);
					$('.modal-title').text("Edit User");
					$('#id2').val(id);
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
			url: 'api/get-single-user-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');
					$('#view_names').html(res.data.names);
					$('#view_staffNumber').html(res.data.staffNumber);
					$('#view_email').html(res.data.email);
					$('#view_department').html(res.data.department);
					$('#view_country').html(res.data.country);
					$('#view_deactivated_by').html(res.data.deactivated_by);
					$('#view_deactivated_at').html(res.data.deactivated_at);
					$('#view_forgot_password').html(res.data.forgot_password_date);
					$('#view_username').html(res.data.username);
					$('#view_status').html(res.data.status);
					$('#view_register_at').html(res.data.created_at);
					$('#view_registered_by').html(res.data.created_by);
					$('#view_updated_at').html(res.data.updated_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('.modal-title').text("View User");
					$('#id').html(id);
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

	$(document).on('click', '.reset_password', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to reset password for this user?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'reset_password',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/action',
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

	
	$(document).on('click', '.activate', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to activate this user?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'activate',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/action',
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

	$(document).on('click', '.deactivate', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to deactivate this user?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

			const submitdata = {
				'operation': 'deactivate',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/action',
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
            title: 'Are you sure you want to delete this user?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");
            const submitdata = {
				'operation': 'delete',
				'id': id
			}

            $.ajax({
                type: "POST",
                url: 'api/action',
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


	$(document).on('click', '.rights', function(){

		$("#checkAll").click(function () {
			$('input:checkbox').not(this).prop('checked', this.checked);
		});

		var id = $(this).attr("id"); 
		
		$.ajax({
			url:"api/get-privileges-" + id,
			method:"GET",
			dataType:"json",
			success:function(res) 
			{

				$('#privilegeModal').modal('show');
				$('#view_privileges_names').html(res.data.names);
				$('#view_privileges_email').html(res.data.email);
				
				res.data.can_add_user == 1 ? $('#can_add_user').prop('checked', true) : $('#can_add_user').prop('checked', false);
				res.data.can_view_user == 1 ? $('#can_view_user').prop('checked', true) : $('#can_view_user').prop('checked', false);
				res.data.can_edit_user == 1 ? $('#can_edit_user').prop('checked', true) : $('#can_edit_user').prop('checked', false);
				res.data.can_deactivate_user == 1 ? $('#can_deactivate_user').prop('checked', true) : $('#can_deactivate_user').prop('checked', false);
				res.data.can_reset_user_password == 1 ? $('#can_reset_user_password').prop('checked', true) : $('#can_reset_user_password').prop('checked', false);
				res.data.can_delete_user == 1 ? $('#can_delete_user').prop('checked', true) : $('#can_delete_user').prop('checked', false);
				res.data.can_give_privileges == 1 ? $('#can_give_privileges').prop('checked', true) : $('#can_give_privileges').prop('checked', false);
				res.data.can_see_settings == 1 ? $('#can_see_settings').prop('checked', true) : $('#can_see_settings').prop('checked', false);
				res.data.can_update_notifications == 1 ? $('#can_update_notifications').prop('checked', true) : $('#can_update_notifications').prop('checked', false);
				
				res.data.can_be_country_manager == 1 ? $('#can_be_country_manager').prop('checked', true) : $('#can_be_country_manager').prop('checked', false);
				res.data.can_be_exco == 1 ? $('#can_be_exco').prop('checked', true) : $('#can_be_exco').prop('checked', false);
				res.data.can_be_gmd == 1 ? $('#can_be_gmd').prop('checked', true) : $('#can_be_gmd').prop('checked', false);
				res.data.can_be_coo == 1 ? $('#can_be_coo').prop('checked', true) : $('#can_be_coo').prop('checked', false);
				
				res.data.can_add_activity == 1 ? $('#can_add_activity').prop('checked', true) : $('#can_add_activity').prop('checked', false);
				res.data.can_be_activity_hod == 1 ? $('#can_be_activity_hod').prop('checked', true) : $('#can_be_activity_hod').prop('checked', false);
				res.data.can_be_activity_coo == 1 ? $('#can_be_activity_coo').prop('checked', true) : $('#can_be_activity_coo').prop('checked', false);
				res.data.can_be_activity_country_manager == 1 ? $('#can_be_activity_country_manager').prop('checked', true) : $('#can_be_activity_country_manager').prop('checked', false);			
				res.data.can_view_activities_reports == 1 ? $('#can_view_activities_reports').prop('checked', true) : $('#can_view_activities_reports').prop('checked', false);
				res.data.can_be_activity_md == 1 ? $('#can_be_activity_md').prop('checked', true) : $('#can_be_activity_md').prop('checked', false);
				
				res.data.can_be_incident_pro_manager == 1 ? $('#can_be_incident_pro_manager').prop('checked', true) : $('#can_be_incident_pro_manager').prop('checked', false);	
				res.data.can_view_incident_reports == 1 ? $('#can_view_incident_reports').prop('checked', true) : $('#can_view_incident_reports').prop('checked', false);	
				
				res.data.can_add_ideas == 1 ? $('#can_add_ideas').prop('checked', true) : $('#can_add_ideas').prop('checked', false);	
				res.data.can_do_ideas_funneling == 1 ? $('#can_do_ideas_funneling').prop('checked', true) : $('#can_do_ideas_funneling').prop('checked', false);	
				res.data.can_do_ideas_sharktank == 1 ? $('#can_do_ideas_sharktank').prop('checked', true) : $('#can_do_ideas_sharktank').prop('checked', false);	
				res.data.can_view_ideas_reports == 1 ? $('#can_view_ideas_reports').prop('checked', true) : $('#can_view_ideas_reports').prop('checked', false);	
				
				res.data.can_add_booking == 1 ? $('#can_add_booking').prop('checked', true) : $('#can_add_booking').prop('checked', false);
				res.data.can_be_approver == 1 ? $('#can_be_approver').prop('checked', true) : $('#can_be_approver').prop('checked', false);				
				res.data.can_view_book_reports == 1 ? $('#can_view_book_reports').prop('checked', true) : $('#can_view_book_reports').prop('checked', false);
				
				res.data.can_add_cash_requests == 1 ? $('#can_add_cash_requests').prop('checked', true) : $('#can_add_cash_requests').prop('checked', false);
				res.data.can_be_cash_hod == 1 ? $('#can_be_cash_hod').prop('checked', true) : $('#can_be_cash_hod').prop('checked', false);
				res.data.can_be_cash_coo == 1 ? $('#can_be_cash_coo').prop('checked', true) : $('#can_be_cash_coo').prop('checked', false);
				res.data.can_be_cash_manager == 1 ? $('#can_be_cash_manager').prop('checked', true) : $('#can_be_cash_manager').prop('checked', false);
				res.data.can_prosess_flight == 1 ? $('#can_prosess_flight').prop('checked', true) : $('#can_prosess_flight').prop('checked', false);
				res.data.can_be_cash_finance == 1 ? $('#can_be_cash_finance').prop('checked', true) : $('#can_be_cash_finance').prop('checked', false);				
				res.data.can_view_cash_reports == 1 ? $('#can_view_cash_reports').prop('checked', true) : $('#can_view_cash_reports').prop('checked', false);	
									
				res.data.can_add_equip_requests == 1 ? $('#can_add_equip_requests').prop('checked', true) : $('#can_add_equip_requests').prop('checked', false);	
				res.data.can_be_equip_hod == 1 ? $('#can_be_equip_hod').prop('checked', true) : $('#can_be_equip_hod').prop('checked', false);		
				res.data.can_be_equip_inn == 1 ? $('#can_be_equip_inn').prop('checked', true) : $('#can_be_equip_inn').prop('checked', false);		
				res.data.can_be_equip_country_manager == 1 ? $('#can_be_equip_country_manager').prop('checked', true) : $('#can_be_equip_country_manager').prop('checked', false);		
				res.data.can_be_equip_coo == 1 ? $('#can_be_equip_coo').prop('checked', true) : $('#can_be_equip_coo').prop('checked', false);		
				res.data.can_be_equip_operations == 1 ? $('#can_be_equip_operations').prop('checked', true) : $('#can_be_equip_operations').prop('checked', false);		
				res.data.can_be_equip_gmd == 1 ? $('#can_be_equip_gmd').prop('checked', true) : $('#can_be_equip_gmd').prop('checked', false);		
				res.data.can_view_equip_reports == 1 ? $('#can_view_equip_reports').prop('checked', true) : $('#can_view_equip_reports').prop('checked', false);		

				res.data.can_view_monitoring_tasks == 1 ? $('#can_view_monitoring_tasks').prop('checked', true) : $('#can_view_monitoring_tasks').prop('checked', false);		
				res.data.can_be_monitoring_pro_manager == 1 ? $('#can_be_monitoring_pro_manager').prop('checked', true) : $('#can_be_monitoring_pro_manager').prop('checked', false);		
				res.data.can_view_monitoring_reports == 1 ? $('#can_view_monitoring_reports').prop('checked', true) : $('#can_view_monitoring_reports').prop('checked', false);
				res.data.can_view_my_minutes == 1 ? $('#can_view_my_minutes').prop('checked', true) : $('#can_view_my_minutes').prop('checked', false);
				res.data.can_add_all_minutes == 1 ? $('#can_add_all_minutes').prop('checked', true) : $('#can_add_all_minutes').prop('checked', false);
				res.data.can_view_minute_reports == 1 ? $('#can_view_minute_reports').prop('checked', true) : $('#can_view_minute_reports').prop('checked', false);
				res.data.can_view_copay == 1 ? $('#can_view_copay').prop('checked', true) : $('#can_view_copay').prop('checked', false);
				res.data.can_add_copay == 1 ? $('#can_add_copay').prop('checked', true) : $('#can_add_copay').prop('checked', false);
				res.data.can_activate_copay == 1 ? $('#can_activate_copay').prop('checked', true) : $('#can_activate_copay').prop('checked', false);
				res.data.can_view_copay_reports == 1 ? $('#can_view_copay_reports').prop('checked', true) : $('#can_view_copay_reports').prop('checked', false);
				
				res.data.can_add_strategy_bsc == 1 ? $('#can_add_strategy_bsc').prop('checked', true) : $('#can_add_strategy_bsc').prop('checked', false);
				res.data.can_be_strategy_hod == 1 ? $('#can_be_strategy_hod').prop('checked', true) : $('#can_be_strategy_hod').prop('checked', false);
				res.data.can_be_strategy_division == 1 ? $('#can_be_strategy_division').prop('checked', true) : $('#can_be_strategy_division').prop('checked', false);	
				res.data.can_be_strategy_hr == 1 ? $('#can_be_strategy_hr').prop('checked', true) : $('#can_be_strategy_hr').prop('checked', false);			
			
				$('.modal-title').text("User Permissions");
				$('#id3').val(id);
				$('#action3').val("Save Changes");
				$('#operation3').val("permissions");
			}
		})
	});
	
	
	$(document).on('submit', '#privilege_form', function(event){
		event.preventDefault();
		
		const submitdata = {
			'can_add_user': $('#can_add_user:checked').val(),
			'can_view_user': $('#can_view_user:checked').val(),
			'can_edit_user': $('#can_edit_user:checked').val(),
			'can_deactivate_user': $('#can_deactivate_user:checked').val(),
			'can_reset_user_password': $('#can_reset_user_password:checked').val(),
			'can_delete_user': $('#can_delete_user:checked').val(),
			'can_give_privileges': $('#can_give_privileges:checked').val(),
			'can_see_settings': $('#can_see_settings:checked').val(),
			'can_update_notifications': $('#can_update_notifications:checked').val(),
			
			'can_be_country_manager': $('#can_be_country_manager:checked').val(),
			'can_be_exco': $('#can_be_exco:checked').val(),
			'can_be_gmd': $('#can_be_gmd:checked').val(),
			'can_be_coo': $('#can_be_coo:checked').val(),
			
			'can_add_activity': $('#can_add_activity:checked').val(),
			'can_be_activity_hod': $('#can_be_activity_hod:checked').val(),
			'can_be_activity_coo': $('#can_be_activity_coo:checked').val(),
			'can_be_activity_country_manager': $('#can_be_activity_country_manager:checked').val(),
			'can_view_activities_reports': $('#can_view_activities_reports:checked').val(),
			'can_be_activity_md': $('#can_be_activity_md:checked').val(),
			
			'can_be_incident_pro_manager': $('#can_be_incident_pro_manager:checked').val(),
			'can_view_incident_reports': $('#can_view_incident_reports:checked').val(),
			
			'can_add_ideas': $('#can_add_ideas:checked').val(),
			'can_do_ideas_funneling': $('#can_do_ideas_funneling:checked').val(),
			'can_do_ideas_sharktank': $('#can_do_ideas_sharktank:checked').val(),
			'can_view_ideas_reports': $('#can_view_ideas_reports:checked').val(),
			
			'can_add_booking': $('#can_add_booking:checked').val(),
			'can_be_approver': $('#can_be_approver:checked').val(),
			'can_view_book_reports': $('#can_view_book_reports:checked').val(),
			
			'can_add_cash_requests': $('#can_add_cash_requests:checked').val(),
			'can_be_cash_hod': $('#can_be_cash_hod:checked').val(),
			'can_be_cash_coo': $('#can_be_cash_coo:checked').val(),
			'can_be_cash_manager': $('#can_be_cash_manager:checked').val(),
			'can_prosess_flight': $('#can_prosess_flight:checked').val(),
			'can_be_cash_finance': $('#can_be_cash_finance:checked').val(),
			'can_view_cash_reports': $('#can_view_cash_reports:checked').val(),
			
			'can_add_equip_requests': $('#can_add_equip_requests:checked').val(),
			'can_be_equip_hod': $('#can_be_equip_hod:checked').val(),
			'can_be_equip_inn': $('#can_be_equip_inn:checked').val(),
			'can_be_equip_country_manager': $('#can_be_equip_country_manager:checked').val(),
			'can_be_equip_coo': $('#can_be_equip_coo:checked').val(),
			'can_be_equip_operations': $('#can_be_equip_operations:checked').val(),
			'can_be_equip_gmd': $('#can_be_equip_gmd:checked').val(),
			'can_view_equip_reports': $('#can_view_equip_reports:checked').val(),
			
			'can_view_monitoring_tasks': $('#can_view_monitoring_tasks:checked').val(),
			'can_be_monitoring_pro_manager': $('#can_be_monitoring_pro_manager:checked').val(),
			'can_view_monitoring_reports': $('#can_view_monitoring_reports:checked').val(),
			'can_view_my_minutes': $('#can_view_my_minutes:checked').val(),
			'can_add_all_minutes': $('#can_add_all_minutes:checked').val(),
			'can_view_minute_reports': $('#can_view_minute_reports:checked').val(),
			'can_view_copay': $('#can_view_copay:checked').val(),
			'can_add_copay': $('#can_add_copay:checked').val(),
			'can_activate_copay': $('#can_activate_copay:checked').val(),
			'can_view_copay_reports': $('#can_view_copay_reports:checked').val(),
			
			'can_add_strategy_bsc': $('#can_add_strategy_bsc:checked').val(),
			'can_be_strategy_hod': $('#can_be_strategy_hod:checked').val(),
			'can_be_strategy_division': $('#can_be_strategy_division:checked').val(),
			'can_be_strategy_hr': $('#can_be_strategy_hr:checked').val(),

			'operation': $('#operation3').val(),
			'id': $('#id3').val()
		}
		
		$.ajax({
			type: "POST",
            url: 'api/action',
			data: JSON.stringify(submitdata),
			ContentType:"application/json",
			beforeSend: function() {
				$("#loader2").show();
			},
			success:function(res)
			{
				if(res.success) {					
					$('#privilege_form')[0].reset();
					$('#privilegeModal').modal('hide');
					dataTable.ajax.reload();

					Swal.fire('Success.', res.message, 'success')
				} else {						
					Swal.fire('Error.', res.message, 'error')
				}					
				
				$("#loader2").hide();
			}
		});
	});
	
	// #################### ROLES ################################

	$('#role_permissions_form .select2').select2({
        dropdownParent: $('.modals1')
    });

	$('#add_role_button').click(function(){
		$('#role_form')[0].reset();
		$('.modal-title').text("Add Role");
		$('#action3').val("Save");
		$('#operation3').val("Add");
	});

	var dataTable1 = $('#roles_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-roles'
		},
		'columns': [   
			{ data: 'role_name'},
			{ data: 'role_description' },
			{ data: 'role_status' },
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-primary btn-sm view_role_permissions"><i class="fas fa-eye" aria-hidden="true"></i> Permissions</button>';
					}
					return data;
				}
			}, 
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_role"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},       
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_role"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
					}
					return data;
				}
			},
		],
		'columnDefs':[
			{
				"targets": [ 3, 4, 5 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.role_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
		}
	});
	setInterval( function () {
		dataTable1.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#role_form', function(event){
		event.preventDefault();
		var role_name = $('#role_name').val();
		var role_description = $('#role_description').val();
		var role_status = $('#role_status').val();
		
		if( (role_name == '') ||  (role_description == '') || (role_status == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'role_name':  $('#role_name').val(),
				'role_description': $('#role_description').val(),
				'role_status': $('#role_status:checked').val(),
				'id': $('#id3').val()
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
				url: "api/create-role",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader3").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#role_form')[0].reset();
						$('#addRoleModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader3").hide();					
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

					$("#loader3").hide();
				},

			});


		}
	});	

	$(document).on('click', '.update_role', function(){
		var id = $(this).attr("id");
		// console.log(id);	
		$.ajax({
			url:"api/get-single-role-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	
				if(res.success) {
					$('#addRoleModal').modal('show');
					$('#role_name').val(res.data.role_name);
					$('#role_description').val(res.data.role_description);
					res.data.role_status == "active" ? $('#role_status').prop('checked', true) : $('#role_status').prop('checked', false);
					$('.modal-title').text("Edit Role");
					$('#id3').val(id);
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

    // Function to generate and append cards
    function generateCards(permissionsData, permissionsContainer) {
        // Clear previous cards
        permissionsContainer.empty();

        // Generate cards for each module
        $.each(permissionsData, function(moduleName, permissions) {
            var card = $("<div>").addClass("card text-dark bg-light mb-3");
            var cardHeader = $("<div>").addClass("card-header");
            var headerText = $("<h5>").addClass("card-title").text(moduleName);
            cardHeader.append(headerText); // Add h5 to card header
            card.append(cardHeader);

            var cardBody = $("<div>").addClass("card-body");

            $.each(permissions, function(index, permission) {
                var checkbox = $("<input>")
                    .addClass("form-check-input permission_checkbox") // Add form-check-input class
                    .attr("type", "checkbox")
                    .attr("name", "role_permissions[]")
                    .val(permission.permission_id);
                var label = $("<label>")
                    .addClass("form-check-label")
                    .text(permission.permission_name)
                    .prepend(checkbox);
                var formCheck = $("<div>").addClass("form-check").append(label);
                cardBody.append(formCheck);
            });

            card.append(cardBody);
            permissionsContainer.append(card);
        });

        // Wrap each card in a column with appropriate grid classes
        var cards = permissionsContainer.find(".card");
        var row = $("<div>").addClass("row");
        $.each(cards, function(index, card) {
            var col = $("<div>").addClass("col-lg-4");
            col.append(card);
            row.append(col);
            if ((index + 1) % 3 === 0 || index === cards.length - 1) {
                permissionsContainer.append(row);
                row = $("<div>").addClass("row");
            }
        });
    }


	$(document).on('click', '.view_role_permissions', function(){
		var roleId = $(this).attr("id"); // Get the role ID
	
		// jQuery AJAX to fetch all system permissions
		$.ajax({
			url: 'api/list-permissions-per-module',
			type: 'GET',
			dataType: 'json',
			success: function(allPermissions) {
				// jQuery AJAX to fetch permissions linked to the role ID
				$.ajax({
					url: 'api/get-role-permissions-' + roleId, // Endpoint to fetch permissions for a role
					type: 'GET',
					dataType: 'json',
					success: function(res) {						
						
						// Reference the container element where checkboxes will be added
						var permissionsContainer = $("#permissions_checkbox_list");
	
						// Generate and append checkboxes for the fetched permissions
						generateCards(allPermissions, permissionsContainer);
	
						if(res.success) {
							// Extract role name from the response data
							var roleName = res.data[0].role_name;
							$('#role_name_pemi').val(roleName);

							// Check checkboxes for permissions linked to the role
							$.each(res.data, function(index, rolePermission) {
								permissionsContainer.find('input[value="' + rolePermission.permission_id + '"]').prop('checked', true);
							});
						}						
	
						// Show the modal
						$('#rolePermissionModal').modal('show');

						$('.modal-title').text("Assign Permissions");
						$('#id6').val(roleId);
						$('#action6').val("Save Changes");
						$('#operation6').val("save_role_permissions");
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error('AJAX Error: ' + textStatus, errorThrown);
					}
				});
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error('AJAX Error: ' + textStatus, errorThrown);
			}
		});
	});

	$(document).on('submit', '.role_permissions_form', function(event) {
		event.preventDefault();
		
		// Define an empty array to store the selected permissions
		var role_permissions = [];
		// Iterate over each checkbox with the class 'permission_checkbox'
		$('.permission_checkbox').each(function() {
			// If the checkbox is checked, add its value (permission ID) to the role_permissions array
			if ($(this).prop('checked')) {
				role_permissions.push($(this).val());
			}
		});
		var role_id = $('#id6').val();
		if ( (role_id !== null && role_id.trim() !== '') ) {

			const submitdata = {
				'operation': 'save_role_permissions',
				'role_permissions': role_permissions,
				'id': role_id
			}
			$.ajax({
				type: "POST",
				url: "api/roles/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader6").show();
				},
				success: function(data) {
					if(data.success) {
						$('#role_permissions_form')[0].reset();
						$('#rolePermissionModal').modal('hide');
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}
						
					$("#loader6").hide();
					// dataTable.ajax.reload();
				}
			});
		} else {
			Swal.fire('Error.', 'Please Input Required data', 'error')
		}
	});
	
	

	$(document).on('click', '.delete_role', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this role?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'delete_role',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/roles/action/',
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


// #################### PERMISSIONS ################################

	$('#add_permission_button').click(function(){
		$('#permission_form')[0].reset();
		$('.modal-title').text("Add Permission");
		$('#action4').val("Save");
		$('#operation4').val("Add");
	});

	var dataTable2 = $('#permissions_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-permissions'
		},
		'columns': [   
			{ data: 'permission_name'},
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_permission"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},       
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_permission"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
					}
					return data;
				}
			},
		],
		'columnDefs':[
			{
				"targets": [ 1 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.permission_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
		}
	});
	setInterval( function () {
		dataTable2.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#permission_form', function(event){
		event.preventDefault();
		var permission_name = $('#permission_name').val();
		
		if(permission_name == '')
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'permission_name':  $('#permission_name').val(),
				'id': $('#id4').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation4').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-permission",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader4").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#permission_form')[0].reset();
						$('#addPermissionModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader4").hide();					
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

					$("#loader4").hide();
				},

			});


		}
	});	

	$(document).on('click', '.update_permission', function(){
		var id = $(this).attr("id");
		// console.log(id);	
		$.ajax({
			url:"api/get-single-permission-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	
				if(res.success) {
					$('#addPermissionModal').modal('show');
					$('#permission_name').val(res.data.permission_name);
					$('.modal-title').text("Edit Permission");
					$('#id4').val(id);
					$('#action4').val("Save Changes");
					$('#operation4').val("Edit");
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

	$(document).on('click', '.delete_permission', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this permission?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'delete_permission',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/permissions/action/',
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


	// #################### MODULES ################################

	$('#module_permissions_form .select2').select2({
        dropdownParent: $('.modals2')
    });

	$('#add_module_button').click(function(){
		$('#module_form')[0].reset();
		$('.modal-title').text("Add Module");
		$('#action5').val("Save");
		$('#operation5').val("Add");
	});

	var dataTable3 = $('#module_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-modules'
		},
		'columns': [   
			{ data: 'module_name'},  
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-primary btn-sm view_module_permissions"><i class="fas fa-eye" aria-hidden="true"></i> Permissions</button>';
					}
					return data;
				}
			}, 
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_module"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},        
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_module"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
					}
					return data;
				}
			},
		],
		'columnDefs':[
			{
				"targets": [ 1, 2, 3 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.module_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
		}
	});
	setInterval( function () {
		dataTable3.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#module_form', function(event){
		event.preventDefault();
		var module_name = $('#module_name').val();
		
		if(module_name == '')
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'module_name':  $('#module_name').val(),
				'id': $('#id5').val()
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
				url: "api/create-module",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader5").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#module_form')[0].reset();
						$('#addModuleModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader5").hide();					
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

	$(document).on('click', '.update_module', function(){
		var id = $(this).attr("id");
		// console.log(id);	
		$.ajax({
			url:"api/get-single-module-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	
				if(res.success) {
					$('#addModuleModal').modal('show');
					$('#module_name').val(res.data.module_name);
					$('.modal-title').text("Edit Module");
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

	$(document).on('click', '.view_module_permissions', function(){
		$('#module_permissions_form')[0].reset();
		$("#module_name_pemi").val('').trigger('change');
		
		// Empty the module_permissions select element to avoid duplication
		$('#module_permissions').empty();
	
		var id = $(this).attr("id");
		// Call load_permissions() and execute AJAX request inside its success callback
		load_permissions(function() {
			$.ajax({
				url: "api/get-module-permissions-" + id,
				method: "GET",
				dataType: "json",
				success: function(res) {
					if (res.success) {
						$('#modullePermissionModal').modal('show');
						$('#module_name_pemi').val(res.data[0].module_name);
	
						// Array to store permission IDs
						var selectedPermissions = [];
	
						// Iterate over each permission and add its ID to the array
						$.each(res.data, function(index, item) {
							selectedPermissions.push(item.permission_id);
						});	
						// Set the value of the select element to the array of permission IDs
						$('#module_permissions').val(selectedPermissions).trigger('change');

						$('.modal-title').text("Assign Permissions");
						$('#id7').val(id);
						$('#action7').val("Save Changes");
						$('#operation7').val("save_module_permissions");
					} else {
						Swal.fire('Error.', res.message, 'error')
					}
				},
				error: function(jqXHR, exception) {
					// Handle AJAX errors
					handleAjaxError(jqXHR, exception);
				}
			});
		});
	});

	$(document).on('submit', '.module_permissions_form', function(event) {
		event.preventDefault();
		var module_permissions = $('#module_permissions').val();
		var module_id = $('#id7').val();
		if ( (module_id !== null && module_id.trim() !== '') ) {

			const submitdata = {
				'operation': 'save_module_permissions',
				'module_permissions': module_permissions,
				'id': module_id
			}
			$.ajax({
				type: "POST",
				url: "api/modules/action",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader7").show();
				},
				success: function(data) {
					if(data.success) {
						$('#module_permissions_form')[0].reset();
						$('#modullePermissionModal').modal('hide');
						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}
						
					$("#loader7").hide();
					// dataTable.ajax.reload();
				}
			});
		} else {
			Swal.fire('Error.', 'Please Input Required data', 'error')
		}
	});
	
	// Function to load permissions
	function load_permissions(callback) {
		$.ajax({
			url: "api/list-permissions",
			method: "GET",
			dataType: "json",
			success: function(res) {
				var data = res;
				$.each(data, function(i, option) {
					$('#module_permissions').append($('<option/>').attr("value", option.id).text(option.permission_name));
				});
				// Call the callback function after permissions are loaded
				if (typeof callback === 'function') {
					callback();
				}
			},
			error: function(jqXHR, exception) {
				// Handle AJAX errors
				handleAjaxError(jqXHR, exception);
			}
		});
	}
	

	$(document).on('click', '.delete_module', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this module?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'delete_module',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/modules/action/',
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


	// #################### APPROVALS ################################

	$('#approval_form .select2').select2({
        dropdownParent: $('.modals3')
    });

	$('#add_approval_button').click(function(){
		$('#module_form')[0].reset();
		$('.modal-title').text("Add Approval");
		$('#action8').val("Save");
		$('#operation8').val("Add");
	});

	var dataTable4 = $('#approvals_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-approvals'
		},
		'columns': [   
			{ data: 'approval_name'},  
			{ data: 'approval_level'},   
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_approval"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},        
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_approval"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
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
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.approval_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
			if ( data.approval_level ) { $(row).find('td:eq(1)').css({ "font-weight": "bold", "color": "#c94e00" }); }
		}
	});
	setInterval( function () {
		dataTable4.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#approval_form', function(event){
		event.preventDefault();
		var approval_name = $('#approval_name').val();
		var approval_level = $('#approval_level').val();
		
		if(approval_name == '' || approval_level == '')
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'approval_name': approval_name,
				'approval_level': approval_level,
				'id': $('#id8').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation8').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-approval",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader8").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#approval_form')[0].reset();
						$('#addApprovalModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader8").hide();					
					dataTable4.ajax.reload();
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

					$("#loader8").hide();
				},

			});


		}
	});	

	$(document).on('click', '.update_approval', function(){
		var id = $(this).attr("id");
		// console.log(id);	
		$.ajax({
			url:"api/get-single-approval-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {	
				if(res.success) {
					$('#addApprovalModal').modal('show');
					$('#approval_name').val(res.data.approval_name);
					$('#approval_level').val(res.data.approval_level);
					$('.modal-title').text("Edit Approval");
					$('#id8').val(id);
					$('#action8').val("Save Changes");
					$('#operation8').val("Edit");
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
	

	$(document).on('click', '.delete_approval', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete for this approval?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'delete_approval',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/approvals/action/',
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
					dataTable4.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	// #################### APPROVAL HIERARCHY ################################

	$('#approval_hierarchy_form .select2').select2({
        dropdownParent: $('.modals4')
    });

	function load_active_users() {
		//LOAD POPULATE COUNTRIES 
		$.ajax({
			url:"api/list-active-users",
			method:"GET",
			dataType:"json",
			success:function(res) {
				var data = res;				
				$.each(data, function(i, option) {
					$('#h_user_id').append($('<option/>').attr("value", option.id).text(option.name));
					$('#h_manager_id').append($('<option/>').attr("value", option.id).text(option.name));
				});
			}
		})
	}

	function load_approvals() {
		//LOAD POPULATE COUNTRIES 
		$.ajax({
			url:"api/list-approvals",
			method:"GET",
			dataType:"json",
			success:function(res) {
				var data = res;				
				$.each(data, function(i, option) {
					$('#h_approval_id').append($('<option/>').attr("value", option.id).text(option.approval_name));
				});
			}
		})
	}

	load_active_users();
	load_approvals();

	$('#add_hierarchy_button').click(function(){

		$('#module_form')[0].reset();
		$('.modal-title').text("Add Hierarchy");
		$("#h_user_id").val('').trigger('change');
		$("#h_manager_id").val('').trigger('change');
		$("#h_approval_id").val('').trigger('change');
		$('#action9').val("Save");
		$('#operation9').val("Add");
	});

	var dataTable5 = $('#approval_hierarchy_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-approval-hierarchy'
		},
		'columns': [   
			{ data: 'staff_name'},  
			{ data: 'staff_country'},   
			{ data: 'approval_name'},  
			{ data: 'supervisor_name'}, 
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
				  return '<button type="button" id="'+data+'" class="btn btn-success btn-sm update_hierarchy"><i class="fas fa-edit" aria-hidden="true"></i></button>';
				}
			},        
			{ data: 'id', 
				
				"render": function ( data, type, full, meta ) {
					if (type === 'display') {
						return '<button type="button" id="'+data+'" class="btn btn-danger btn-sm delete_hierarchy"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
					}
					return data;
				}
			},
		],
		'columnDefs':[
			{
				"targets": [ 4, 5 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[7, 25, 500, -1], [7, 25, 50, "All"]],
		'createdRow': function( row, data, dataIndex ) {
			if ( data.staff_name ) { $(row).find('td:eq(0)').css({ "font-weight": "bold", "color": "#00a6bd" }); }
			if ( data.approval_name ) { $(row).find('td:eq(2)').css({ "font-weight": "bold", "color": "#c94e00" }); }
			if ( data.supervisor_name ) { $(row).find('td:eq(3)').css({ "font-weight": "bold", "color": "#006527" }); }
		}
	});
	setInterval( function () {
		dataTable5.ajax.reload( null, false );
	}, 30000 ); 


	$(document).on('submit', '#approval_hierarchy_form', function(event){
		event.preventDefault();
		var h_user_id = $('#h_user_id').val();
		var h_manager_id = $('#h_manager_id').val();
		var h_approval_id = $('#h_approval_id').val();
		
		if(approval_name == '' || approval_level == '')
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'user_id': h_user_id,
				'manager_id': h_manager_id,
				'approval_id': h_approval_id,
				'id': $('#id9').val()
			}
			// console.log(submitdata);
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation9').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-approval-hierarchy",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader9").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#approval_hierarchy_form')[0].reset();
						$('#approvalHierarchyModal').modal('hide');

						$("#h_user_id").val('').trigger('change');
						$("#h_manager_id").val('').trigger('change');
						$("#h_approval_id").val('').trigger('change');

						Swal.fire('Success.', data.message, 'success')
					} else {
						Swal.fire('Error.', data.message, 'error')
					}

					$("#loader9").hide();					
					dataTable5.ajax.reload();
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

					$("#loader9").hide();
				},

			});


		}
	});	

	$(document).on('click', '.update_hierarchy', function(){
		var id = $(this).attr("id");
		// console.log(id);			

		$.ajax({
			url:"api/get-single-hierarchy-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {
				if(res.success) {
					$('#approvalHierarchyModal').modal('show');

					$("#h_user_id").val(res.data.user_id).trigger('change');
					$("#h_manager_id").val(res.data.manager_id).trigger('change');
					$("#h_approval_id").val(res.data.approval_id).trigger('change');
					
					$('.modal-title').text("Edit Approval Hierarchy");
					$('#id9').val(id);
					$('#action9').val("Save Changes");
					$('#operation9').val("Edit");
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
	

	$(document).on('click', '.delete_hierarchy', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete for this hierarchy?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {

            var id = $(this).attr("id");

            const submitdata = {
				'operation': 'delete_hierarchy',
				'id': id
			}
            $.ajax({
                type: "POST",
                url: 'api/approval_hierarchy/action/',
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
					dataTable5.ajax.reload();
					$('#cover-spin').hide()
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });
	
});