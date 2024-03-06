
$(document).ready(function(){	
	
	$('#add_button').click(function(){
		$('#grouping_form')[0].reset();
		$('.modal-title').text("Create New Grouping");
		$('#action').val("Create");
		$('#operation').val("Add");
	});

	
	var dataTable = $('#grouping_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-all-groupings'
		},
		'columns': [   
			{ data: 'group_name'},
			{ data: 'valuation_method'},
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 3 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[10, 25, 500, -1], [10, 25, 500, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$(document).on('submit', '#grouping_form', function(event){
		event.preventDefault();
		var group_name = $('#group_name').val();	
		var valuation_method = $('#valuation_method').val();	

		if( group_name == '' || valuation_method == '')
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'group_name':  $('#group_name').val(),
				'valuation_method':  $('#valuation_method').val(),
				'id': $('#id2').val()
			}
			
			//Type and URL
			var type_method = "POST";
			if( ($('#operation').val()) ==="Edit" ) {				
				var type_method = "PUT";
			}

			//post with ajax
			$.ajax({
				type: type_method,
				url: "api/create-grouping",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#grouping_form')[0].reset();
						$('#addGroupingModal').modal('hide');

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
			url:"api/get-single-grouping-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#addGroupingModal').modal('show');
					$('#group_name').val(res.data.group_name);
					$('#valuation_method').val(res.data.valuation_method).trigger('change');
					$('.modal-title').text("Edit Grouping");
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
	
	
	$(document).on('click', '.delete', function(){

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to delete this Grouping?',
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
                url: 'api/grouping/action',
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
                beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
				},
                success: function (res) {
					if(res.success) {
                    	Swal.fire('Success.', res.message, 'success')
					} else {						
                    	Swal.fire('Error.', res.message, 'error')
					}					
					dataTable.ajax.reload();
                }
            });


          } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
          }
        });

    });


	
	
});