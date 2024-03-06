
$(document).ready(function(){	
	
	$('#add_button').click(function(){
		$('#item_form')[0].reset();
		$('.modal-title').text("Create New Item");
		$('#action').val("Create");
		$('#operation').val("Add");
		$("#grouping_id").val('').trigger('change');
	});

	//LOAD POPULATE GROUPING
	$.ajax({
		url:"api/get-groupingslist",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;				
			$.each(data, function(i, option) {
				$('#grouping_id').append($('<option/>').attr("value", option.id).text(option.group_name));
			});
		}
	})
	
	var dataTable = $('#item_data').DataTable({
		'processing': true,
		'serverSide': true,
		'stateSave': true,
		'serverMethod': 'post',
		'ajax': {
			'url':'api/get-all-items'
		},
		'columns': [   
			{ data: 'item_name'},
			{ data: 'group_name' },
			{ data: 'unit' },
			{ data: 'price_per_unit' },
			{ data: 'created_by' },
			{ data: 'created_at' },
			{ data: 'actions'},
		],
		'columnDefs':[
			{
				"targets": [ 6 ],
      			"orderable": false
			},
		],
		'lengthMenu': [[10, 25, 500, -1], [10, 25, 500, "All"]]
	});
	setInterval( function () {
		dataTable.ajax.reload( null, false );
	}, 30000 ); 
	
	

	$(document).on('submit', '#item_form', function(event){
		event.preventDefault();
		var grouping_id = $('#grouping_id').val();
		var item_name = $('#item_name').val();
		var unit = $('#unit').val();
		var price_per_unit = $('#price_per_unit').val();
		
		if( (grouping_id == '') ||  (item_name == '') || (unit == '') || (price_per_unit == '') )
		{
			Swal.fire('Error.', 'Please Enter Required Fields', 'error')
		}
		else
		{			
			
			const submitdata = {
				'grouping_id':  $('#grouping_id').val(),
				'item_name': $('#item_name').val(),
				'unit': $('#unit').val(),
				'price_per_unit': $('#price_per_unit').val(),
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
				url: "api/create-item",
				data: JSON.stringify(submitdata),
				ContentType:"application/json",
				beforeSend: function() {
					$("#loader").show();
				},

				success:function(data){ 

					if(data.success) {
						$('#item_form')[0].reset();
						$('#addItemModal').modal('hide');

						Swal.fire('Success.', data.message, 'success')
					} else {
						$('#errorMsg').html(data.message);
						$("#errorAlert").fadeToggle(500).show();
						function timedMsg(){
							var t=setTimeout("document.getElementById('errorAlert').style.display='none';",3000);
						}
						timedMsg();
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
					// $('#post').html(msg);
					$('#errorMsg').html(msg);
					$("#errorAlert").fadeToggle(500).show();
					function timedMsg(){
						var t=setTimeout("document.getElementById('errorAlert').style.display='none';",10000);
					}
					timedMsg();
					$("#loader").hide();
				},

			});


		}
	});
	

	$(document).on('click', '.update', function(){
		var id = $(this).attr("id");
		$.ajax({
			url:"api/get-single-item-" + id,
			method:"GET",
			// data:{id:id},
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#addItemModal').modal('show');
					$('#grouping_id').val(res.data.grouping_id).trigger('change');	
					$('#item_name').val(res.data.item_name);
					$('#unit').val(res.data.unit);
					$('#price_per_unit').val(res.data.price_per_unit);
					$('.modal-title').text("Edit Item");
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
            title: 'Are you sure you want to delete this Item?',
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
                url: 'api/item/action',
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