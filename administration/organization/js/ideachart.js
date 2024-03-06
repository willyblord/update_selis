$(document).ready(function () {
  
  getCountriesChart();
  getDepartmentsChart();
  getStatusChart();
  infoBoxChart();

  $(".filter").on("click", function (e) {
    e.preventDefault();
    var cnty = $("#select_cnty").val();
    var dpt = $("#select_dpt").val();
    var user = $("#select_user").val();

      resetfields();
      getCountriesChart(cnty,dpt,user);
      getDepartmentsChart(cnty,dpt,user);
      getStatusChart(cnty,dpt,user);
      infoBoxChart(cnty,dpt,user);

  });

  $(".reset").on("click", function (e) {
    e.preventDefault();
    
    $("#select_cnty").val('').trigger('change');
    $("#select_dpt").val('').trigger('change');
    $("#select_user").val('').trigger('change');
    resetfields();
    getCountriesChart();
    getDepartmentsChart();
    getStatusChart();
    infoBoxChart();
  });
  

  function infoBoxChart(cnty,dpt,user) {
    var allIdeas = 0;
    var newIdeas = 0;
    var unmetIdeas = 0;
    var improIdeas = 0;

    $.ajax({
        url: "api/charts/ideasCount.php",
        method: "POST",
        data: {
          cnty: cnty,
          dpt: dpt,
          user: user
        },
        dataType: "JSON",
        success: function(data) { 
          allIdeas = data.all;
          newIdeas = data.new;
          unmetIdeas = data.unmet;
          improIdeas = data.improve;

          $("#allIdeas").html(allIdeas);
          $("#newIdeas").html(newIdeas);
          $("#unmetIdeas").html(unmetIdeas);
          $("#improIdeas").html(improIdeas);   

          $('.number').each(function () {
            var $this = $(this);
            jQuery({ Counter: 0 }).animate({ Counter: $this.text() }, {
              duration: 900,
              easing: 'swing',
              step: function () {
                $this.text(Math.ceil(this.Counter));
              }
            });
          });

        }
    });
    
  }

  function getCountriesChart(cnty,dpt,user) {
    $.ajax({
        url: "api/charts/ideaPerCountry.php",
        method: "POST",
        data: {
          cnty: cnty,
          dpt: dpt,
          user: user
        },
        dataType: "JSON",
        success: function(data) {
          
          let country = [];
          let marks = [];
          let color = [];

          for(let i = 0; i < data.length; i++) {
            country.push(data[i].country_name);
            marks.push(data[i].idea_number);
            color.push(data[i].color);
          }
          drawIdeasPerCountryChart(country, marks, color);           
        }
    });
}

  function getDepartmentsChart(cnty,dpt,user) {
      $.ajax({
          url: "api/charts/ideaPerDepartment.php",
          method: "POST",
          data: {
            cnty: cnty,
            dpt: dpt,
            user: user
          },
          dataType: "JSON",
          success: function(data) {
            
            let department = [];
            let marks = [];
            let color = [];

            for(let i = 0; i < data.length; i++) {
              department.push(data[i].department_name);
              marks.push(data[i].idea_number);
              color.push(data[i].color);
            }
            drawIdeasPerDepartmentChart(department, marks, color);            
          }
      });
  }

  function getStatusChart(cnty,dpt,user) {
    $.ajax({
        url: "api/charts/ideaPerStatus.php",
        method: "POST",
        data: {
          cnty: cnty,
          dpt: dpt,
          user: user
        },
        dataType: "JSON",
        success: function(data) {
          
          let status = [];
          let marks = [];
          let color = [];

          for(let i = 0; i < data.length; i++) {
            status.push(data[i].status);
            marks.push(data[i].idea_number);
            color.push(data[i].color);
          }
          drawIdeasPerStatusChart(status, marks, color);            
        }
    });
}

  // Draw charts for countries
  var graph = '';
  function drawIdeasPerCountryChart(label_arr, data_arr, bgcolor_arr) {
      var chart_data = {
        labels:label_arr,
        datasets:[
          {
            label:'Ideas',
            backgroundColor:bgcolor_arr,
            color:'#fff',
            data:data_arr
          }
        ]
      };

      var options = {
        responsive:true,
        scales:{
          yAxes:[{
            ticks:{
              min:0
            }
          }]
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
  function drawIdeasPerDepartmentChart(label_arr, data_arr, bgcolor_arr) {
      var chart_data = {
        labels:label_arr,
        datasets:[
          {
            label:'Ideas',
            backgroundColor:bgcolor_arr,
            color:'#fff',
            data:data_arr
          }
        ]
      };

      var options = {
        responsive:true,
        scales:{
        }
      };

      var group_chart = $('#departChart');
      graph1 = new Chart(group_chart, {
        type:'doughnut',
        data:chart_data,
        options:options
      });
  } 


  // Draw charts for departments
  var graph2 = '';
  function drawIdeasPerStatusChart(label_arr, data_arr, bgcolor_arr) {
      var chart_data = {
        labels:label_arr,
        datasets:[
          {
            label:'Ideas',
            backgroundColor:bgcolor_arr,
            color:'#fff',
            data:data_arr
          }
        ]
      };

      var options = {
        responsive:true,
        scales:{
          yAxes:[{
            ticks:{
              min:0
            }
          }]
        }
      };

      var group_chart = $('#statusChart');
      graph2 = new Chart(group_chart, {
        type:'line',
        data:chart_data,
        options:options
      });
  }

  //Resetting all chart canvas
  function resetfields() {
    graph.destroy(); 
    graph1.destroy(); 
    graph2.destroy(); 
  }


});
