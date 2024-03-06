<?php
// (A) ACCESS CHECK
require "../../protect.php";

if ($row['can_be_super_user'] != 1 && $row['can_add_cash_requests'] != 1) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dashboard - Accessibility | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta content="seris App" name="Smart Applications" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg" />
    <!-- select2 css -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />

    <!-- Sweet Alert-->
    <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

    <link href="assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <!-- datetimepicker -->
    <!-- Tempus Dominus Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.11/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <style>
    .select2 {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 34px !important;
    }

    .select2-container--default .select2-selection--single {
        /* border: 1px solid #ccc !important; */
        border-radius: 0px !important;
    }

    .select2-container--default .select2-results>.select2-results__options {
		background-color: #ffffff;
		color: #3c3c3c;
	}

	/* Clear "X" */
	.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
		color: #ffffff;
	}

	/* Clear "X" Hover */
	.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
		color: #e99b85;
	}

	/* Each Result */
	.select2-container--default .select2-selection--multiple .select2-selection__choice {
		background-color: #b01c2e;
        color: #ffffff;
        border-radius: 4px;
	}
    </style>
</head>

<body data-topbar="dark" data-layout="horizontal">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- ========== Header ========== -->
        <?php include '../../include/header.php'; ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> Dashboard</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Performane</a> ></li>
                                        <li class="breadcrumb-item"><a href="#">Business Performane</a> ></li>
                                        <li class="breadcrumb-item active">Systems Accessbility</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-sm-12 col-lg-2">  
                            <?php include 'include/side_menu.php'; ?>
                        </div>
                        <div class="col-sm-12 col-lg-10">
                            <div class="card">
                                <div class="card-body">
                                <form method="post" id="filter_form" autocomplete="off"
                                        enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="country" class="form-label">COUNTRY </label>
                                                    <select class="form-select select2" id="country" name="country">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="system" class="form-label">SYSTEM</label>
                                                    <select class="form-select select2" id="system" name="system">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="date_range" class="form-label">Created Date
                                                        Range</label>
                                                    <div class="input-daterange input-group"
                                                        id="project-date-inputgroup" data-provide="datepicker"
                                                        data-date-format="yyyy-mm-dd"
                                                        data-date-container='#project-date-inputgroup'
                                                        data-date-autoclose="true">
                                                        <input type="text" class="form-control" placeholder="From Date"
                                                            name="DateFrom" id="DateFrom" />
                                                        <input type="text" class="form-control" placeholder="To Date"
                                                            name="DateTo" id="DateTo" />
                                                        <div class="invalid-feedback">
                                                            Please provide a valid date
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3 mt-4">
                                                    <div class="spinner-border text-primary m-1" role="status" id="loader"
                                                        style="display:none;">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>

                                                    <div class="btn-group mb-3 d-flex" role="group">
                                                        <button type="button" class="btn btn-primary" id="filter" >Filter</button>
                                                        <button type="button" class="btn btn-danger" id="reset">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="chart-responsive">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6">
                                                <canvas id="systemChart" style="width:100%;"></canvas>
                                            </div>
                                            <div class="col-sm-12 col-md-6">
                                                <canvas id="countriesChart" style="width:100%;"></canvas>
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>

                            <style>
                                .tableStyle {
                                    font-size: 12px;
                                }
                            </style>
                            <div class="card">
                                <div class="card-body">
                                    <table id="downtime_data" class="table table-condensed table-hover dt-responsive w-100 tableStyle">
                                        <thead>
                                            <tr>
                                                <th>Ref.No</th>
                                                <th>Downtime</th>
                                                <th>System</th>
                                                <th>Country</th>
                                                <th>Time_Started</th>
                                                <th>TAT</th>
                                                <th>Hours</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <div class="modal fade viewModal" id="viewModal" tabindex="-1" role="dialog" aria-labelledby=""
                aria-hidden="true">
                <div class="modal-dialog modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-responsive table-condensed table-striped">
                                <tbody>
                                    <tr>
                                        <td width="35%">Reference Number</td>
                                        <td><b><span id="view_refNo"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Downtime</td>
                                        <td><b><span id="view_downtime"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Country</td>
                                        <td><b><span id="view_country"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>System Name</td>
                                        <td><b><span id="view_system"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Time Started</td>
                                        <td><b><span id="view_time_started"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Time Resolved</td>
                                        <td><b><span id="view_time_resolved"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>TAT</td>
                                        <td><b><span id="view_tat_in_minutes"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Hours</td>
                                        <td><b><span id="view_hours_in_minutes"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>RCA & PREVENTION STRATEGIES</td>
                                        <td><b><span id="view_rca"></span></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../../include/footer.php'; ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    <?php include '../../include/rightside.php'; ?>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>

    <!-- Required datatable js -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="assets/libs/jszip/jszip.min.js"></script>
    <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <!-- Responsive examples -->
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="assets/js/pages/datatables.init.js"></script>

    <script src="assets/libs/parsleyjs/parsley.min.js"></script>

    <script src="assets/js/pages/form-validation.init.js"></script>

    <!-- toastr plugin -->
    <script src="assets/libs/toastr/build/toastr.min.js"></script>

    <!-- Sweet Alerts js -->
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- select 2 plugin -->
    <script src="assets/libs/select2/js/select2.min.js"></script>
    <!-- init js -->
    <script src="assets/js/pages/ecommerce-select2.init.js"></script>
    <!-- bootstrap datepicker -->
    <script src="assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    
    <script src="assets/libs/chartjs/Chart.bundle.min.js"></script>
    
    <script src="assets/js/app.js"></script>
    <script src="performance/accessbility/js/dashboard.js"></script>
   
</body>

</html>