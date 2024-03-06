<?php
// (A) ACCESS CHECK
require "../../protect.php";

$requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
$requiredPermissions = [];
$requiredModules = 'Performance';

if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Strategy Report | SERIS</title>
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

    .modal-body {
        /* min-height: calc(100vh - 210px); */
        overflow-y: auto;
        overflow-x: hidden;
        /* max-height: 100%; */
        /* position: relative; */
    }

    .selisBtn {
        background-color: aliceblue;
        color: black;
        border-width: 0px;
        border-bottom: 3px solid #b01c2e;
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> Strategy Report</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Strategy Performance </a> ></li>
                                        <li class="breadcrumb-item active">Reports</li>
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
                                                    <label for="threeyear_strategy" class="form-label">3 YEAR STRATEGY</label>
                                                    <select class="form-select select2" id="threeyear_strategy" name="threeyear_strategy">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label for="annual_year" class="form-label">ANNUAL </label>
                                                    <select class="form-select select2" id="annual_year" name="annual_year">
                                                        <option value="">ALL</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="country" class="form-label">COUNTRY </label>
                                                    <select class="form-select select2" id="country" name="country">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="department" class="form-label">DEPARTMENT</label>
                                                    <select class="form-select select2" id="department" name="department">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="date_range" class="form-label">TIMELINE</label>
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
                                            <div class="col-md-2">
                                                <div class="mb-3 mt-4">
                                                    <div class="spinner-border text-primary m-1" role="status" id="loader"
                                                        style="display:none;">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>

                                                    <div class="btn-group mb-3 d-flex" role="group">
                                                        <button type="submit" class="btn btn-primary">Filter</button>
                                                        <button type="reset" class="btn btn-danger"
                                                            id="reset">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            
                                            <div id="extract_div"></div>
                                            
                                            <table id="data_table" class="table table-hover dt-responsive w-100" style="font-size: 0.80em; cursor:pointer">
                                                <thead>
                                                    <tr>
                                                        <th>View More</th>
                                                        <th>Strategy</th>
                                                        <th>Year</th>
                                                        <th>Initiative</th>
                                                        <th>Target</th>
                                                        <th>Measure</th>
                                                        <th>Figure</th>
                                                        <th>Weight</th>
                                                        <th>Raw Score</th>
                                                        <th>Target Score</th>
                                                        <th>Achieved Weight</th>
                                                        <th>Created_by</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="13"><center><b>No Data to display</b></center></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> <!-- end col -->
                            </div> <!-- end row -->

                        </div>
                        <!-- end col -->

                    </div>
                    <!-- end row -->
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->


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
    <script src="assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="assets/js/app.js"></script>
    <script src="performance/strategy/js/strategy_report.js"></script>
</body>

</html>