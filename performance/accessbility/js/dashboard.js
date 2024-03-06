
$(document).ready(function(){

	//LOAD POPULATE COUNTRIES 
	$.ajax({
		url:"api/list-countries",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;		
			$('#country').append($('<option/>').attr("value", "").text("ALL"));			
			$.each(data, function(i, option) {
				$('#country').append($('<option/>').attr("value", option.id).text(option.country_name));
			});
		}
	});

	//LOAD POPULATE SYSTEMS 
	$.ajax({
		url:"api/list-systems",
		method:"GET",
		dataType:"json",
		success:function(res) {
			var data = res;		
			$('#system').append($('<option/>').attr("value", "").text("ALL"));			
			$.each(data, function(i, option) {
				$('#system').append($('<option/>').attr("value", option.id).text(option.system_name));
			});
		}
	});

	var today = new Date();

	var dateFrom = today.getFullYear()+'-01-01';
	$('#DateFrom').val(dateFrom).trigger('change');

	var dateTo = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
	$('#DateTo').val(dateTo).trigger('change');

	var DateFrom = $("#DateFrom").val();
	var DateTo = $("#DateTo").val();

	getCountriesChart('', '', DateFrom, DateTo);
	getSystemsChart('', '', DateFrom, DateTo);
	getDataTable('','',DateFrom,DateTo);

	$("#filter").on("click", function (e) {
		e.preventDefault();
		var country = $("#country").val();
		var system = $("#system").val();
		var DateFrom = $("#DateFrom").val();
		var DateTo = $("#DateTo").val();

		if( (DateFrom == '') || (DateTo == '') )
		{
			Swal.fire('Error.', 'Please Select Dates', 'error')
		}
		else
		{
			resetfields();
			getCountriesChart(country,system,DateFrom,DateTo);
			getSystemsChart(country,system,DateFrom,DateTo);
			getDataTable(country,system,DateFrom,DateTo);
		}

	});

	$("#reset").on("click", function (e) {
		e.preventDefault();
		
		$("#country").val('').trigger('change');
		$("#system").val('').trigger('change');
		$("#DateFrom").val(dateFrom).trigger('change');
		$("#DateTo").val(dateTo).trigger('change');

		
		var DateFrom = $("#DateFrom").val();
		var DateTo = $("#DateTo").val();

		resetfields();
		getCountriesChart('', '', dateFrom, dateTo);
		getSystemsChart('', '', dateFrom, dateTo);
		getDataTable('','',DateFrom,DateTo);
	});

	function getCountriesChart(country,system,DateFrom,DateTo) {

		const submitdata = {
			'country':  country,
			'system': system,
			'DateFrom': DateFrom,
			'DateTo': DateTo
		}

		$.ajax({
			url: "api/accessbility/dashboard",
			method: "POST",
			data: JSON.stringify(submitdata),
			ContentType:"application/json",
			success: function(data) {
			  
				let country = [];
				let marks = [];
				let marks1 = [];
		
				if(data !== null) {
					for(let i = 0; i < data.length; i++) {
						country.push(data[i].month);
						marks.push(data[i].total_count);
						marks1.push(data[i].total_hours);
					}
				}
				drawSystemsChart(country, marks, marks1);           
			}
		});
	}

	function getSystemsChart(country,system,DateFrom,DateTo) {

		const submitdata = {
			'country':  country,
			'system': system,
			'DateFrom': DateFrom,
			'DateTo': DateTo
		}

		$.ajax({
			url: "api/accessbility/dashboardsystem",
			method: "POST",
			data: JSON.stringify(submitdata),
			ContentType:"application/json",
			success: function(data) {
			  
				let system_name = [];
				let marks = [];
				let marks1 = [];
			
				if(data !== null) {
					for(let i = 0; i < data.length; i++) {
						system_name.push(data[i].system_name);
						marks.push(data[i].total_count);
						marks1.push(data[i].total_hours);
					}
				}
			  	drawIdeasPerDepartmentChart(system_name, marks, marks1);            
			}
		});
	}

	 // Draw charts for countries
	var graph = '';
	function drawSystemsChart(label_arr, data_arr, data_arr1) {

		if(data_arr.length > 0 && data_arr1.length > 0 ) {

			var chart_data = {
			labels:label_arr,
			datasets:[
				{
					label:'Count',
					backgroundColor:'#0073b7',
					color:'#fff',
					data:data_arr
				},			
				{
					label:'Hours',
					backgroundColor:'#ff851b', 
					color:'#fff',
					data:data_arr1
				}
			]
			};
		} else {
			var chart_data = {
				labels:['No Data'],
				datasets:[
					{
						label:'No Data',
						backgroundColor:'#bdbdbd',
						color:'#fff',
						data:[0]
					}
				]
			};
		}

		var options = {
			responsive:true,
			scales:{
				yAxes:[{
				ticks:{
					min:0
				}
				}]
			},
			title: {
				display: true,
				text: 'MONTHLY DOWNTIME - EACH SYSTEM VS COUNT & HOURS VS DATE SELECTED'
			}
		};

		var group_chart = $('#countriesChart');
			graph = new Chart(group_chart, {
			type:'bar',
			data:chart_data,
			options:options
		}); 
	} 

	 // Draw charts for departments
	 var graph1 = '';
	 function drawIdeasPerDepartmentChart(label_arr, data_arr, data_arr1) {

		if(data_arr.length > 0 && data_arr1.length > 0 ) {
			var chart_data = {
			labels:label_arr,
			datasets:[
					{
					label:'Count',
					backgroundColor:'#0073b7',
					color:'#fff',
					data:data_arr
					},			 
					{
						label:'Hours',
						backgroundColor:'#00a65a',
						color:'#fff',
						data:data_arr1
					}
				]
			};
		} else {
			var chart_data = {
				labels:['No Data'],
				datasets:[
						{
						label:'No Data',
						backgroundColor:'#bdbdbd',
						color:'#fff',
						data:[0]
						}
					]
				};
		}
   
		 var options = {
		   responsive:true,
		   scales:{
				xAxes:[{
				ticks:{
					min:0
				}
				}]
			},
		   title: {
			   display: true,
			   text: 'DOWNTIME - ALL SYSTEMS VS COUNT & HOURS VS DATE SELECTED'
		   }
		 };
   
		 var group_chart = $('#systemChart');
		 graph1 = new Chart(group_chart, {
		   type:'horizontalBar',
		   data:chart_data,
		   options:options
		 });
	} 


	//Resetting all chart canvas
	function resetfields() {
		graph.destroy(); 
		graph1.destroy(); 

		var table = $('#downtime_data').DataTable();
		table.clear().destroy();
	}



	// #################################################

	function getDataTable(country,system,DateFrom,DateTo) {
		var dataTable = $('#downtime_data').DataTable({
			'processing': true,
			'serverSide': true,
			'stateSave': true,
			'serverMethod': 'post',				
			'destroy': true,
			'ajax': {
				'url':'api/get-all-accessbility-downtimes',
				'data': {
					country: country,
					system: system,
					DateFrom: DateFrom,
					DateTo: DateTo
				}
			},
			'columns': [   
				{ data: 'refNo'},
				{ data: 'downtime' },
				{ data: 'system_name' },
				{ data: 'country' },
				{ data: 'time_started' },
				{ data: 'tat_in_minutes' },
				{ data: 'hours_in_minutes' },
				{ data: 'id',				
						"render": function ( data, type, full, meta ) {
							return '<button type="button" id="'+data+'" class="btn btn-primary btn-sm view"><i class="fa fa-eye"></i> View</button>';
						}
					},
			],
			'columnDefs':[
				{
					"targets": [ 7 ],
					"orderable": false
				},
			],
			'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
		});
	} 

	$(document).on('click', '.view', function(){
		var id = $(this).attr("id");
		$.ajax({
			url: 'api/get-single-downtime-' + id,
			method:"GET",
			dataType:"json",
			success:function(res) {				
				if(res.success) {
					$('#viewModal').modal('show');
					$('#view_refNo').html(res.data.refNo);
					$('#view_country').html(res.data.country);
					$('#view_system').html(res.data.system);
					$('#view_downtime').html(res.data.downtime);
					$('#view_time_started').html(res.data.time_started);
					$('#view_time_resolved').html(res.data.time_resolved);
					$('#view_tat_in_minutes').html(res.data.tat_in_minutes);
					$('#view_hours_in_minutes').html(res.data.hours_in_minutes);
					$('#view_rca').html(res.data.rca);
					$('#view_created_at').html(res.data.created_at);
					$('#view_created_by').html(res.data.created_by);
					$('#view_updated_at').html(res.data.updated_at);
					$('#view_updated_by').html(res.data.updated_by);
					$('.modal-title').text("View Downtime Details");
					$('#id').html(id);
				} else {
					Swal.fire('Error.', res.message, 'error')
				}
			}
		})
	});

});