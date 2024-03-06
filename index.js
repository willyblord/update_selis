
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
	drawStrategyChart();

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


    // Draw charts for strategy
	 var graph2 = '';
	 function drawStrategyChart() {

        var chart_data = {
        labels:[
                'New Product Line Projects (8)',
                'Strategic Partnerships & Brand (10)',
                'QA & Risk Management (5)',
                'Customer Experienece (6)',
                'Operational Projects (6)',
                'Talent Management (2)',
                'Business Growth & Profitability (7)'
            ],
        datasets:[
                {
                label:'Percentage',
                backgroundColor:'#0073b7',
                color:'#fff',
                data:[84, 98, 99, 94, 95, 100, 100]
                }
            ]
        };
   
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
			   text: 'STRATEGIC PROGRESS ASSESSMENT  - Q1 2023 - 96%'
		   }
		 };
   
		 var group_chart = $('#strategyChart');
		 graph2 = new Chart(group_chart, {
		   type:'horizontalBar',
		   data:chart_data,
		   options:options
		 });
	} 

	//Resetting all chart canvas
	function resetfields() {
		graph.destroy(); 
		graph1.destroy(); 
	}

});