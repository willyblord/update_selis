<?php
// (A) ACCESS CHECK
require "protect.php";
?>
<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Welcome | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg">
    <!-- select2 css -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

</head>

<body data-topbar="dark" data-layout="horizontal">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ========== Header ========== -->
        <?php include 'include/header.php'; ?>

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
                                <h4 class="mb-sm-0 font-size-18">WELCOME</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Home</a> ></li>
                                        <li class="breadcrumb-item active">Welcome</li>
                                    </ol>
                                </div>                               

                            </div>
                        </div>
                    </div>
                    <!-- end page title --> 

                    <?php
                    if ( $row['department'] == NULL || $row['department'] == "") {
                    ?>
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card bg-danger bg-soft text-danger">
                                    <div class="card-body">
                                        <h5 class="my-2 text-danger">Kindly contact the admin to update your department details!</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'EXCO_ROLE'];
                    $requiredPermissions = [];
                    $requiredModules = 'Performance';
                    
                    } elseif ( ($row['department'] != NULL) && ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) ) {
                        
                    ?>

                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">

                                <h5>STRATEGY PERFORMANCE</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="chart-responsive">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-6">
                                                    <canvas id="strategyChart" style="width:100%;"></canvas>
                                                </div>
                                            </div>                                        
                                        </div>
                                    </div>
                                </div>

                                <h5>SYSTEMS ACCESSBILITY - DOWNTIMES</h5>

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
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>

                    <?php
                    }
                    ?>

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'include/footer.php'; ?>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    <?php include 'include/rightside.php'; ?>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    
    <!-- select 2 plugin -->
    <script src="assets/libs/select2/js/select2.min.js"></script>
    <!-- init js -->
    <script src="assets/js/pages/ecommerce-select2.init.js"></script>
    <!-- bootstrap datepicker -->
    <script src="assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    
    <script src="assets/libs/chartjs/Chart.bundle.min.js"></script>

    <script src="assets/js/app.js"></script>
    <script src="index.js"></script>
</body>

</html>