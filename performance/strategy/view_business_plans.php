<?php
// (A) ACCESS CHECK
require "../../protect.php";

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
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
    <title>Annual Business Plan - Strategy | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> VIEW ANNUAL BUSINESS PLAN</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Strategy Performance</a> ></li>
                                        <li class="breadcrumb-item active">Annual Business Plan</li>
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

                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <div class="text-sm">                                                
                                        <a href="annual-business-plans" class="btn btn-secondary waves-effect waves-light mb-2 me-2">
                                            <i class="fas fa-arrow-alt-circle-left"></i> Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-danger text-white" id="returnNoti" style="display:none">
                                <div class="card-body">
                                    <p class="card-text">
                                        <b>Returned By: </b><span id="viewReturnBy">...</span> | 
                                        <b>At: </b><span id="viewReturnAt">...</span> | 
                                        <b>Reason: </b><span id="viewReturnReason">...</span>
                                    </p>
                                </div>
                            </div>

                            <div class="card bg-dark text-white" id="rejectNoti"  style="display:none">
                                <div class="card-body">
                                    <p class="card-text">
                                        <b>Rejectd By: </b><span id="viewRejectBy">...</span> | 
                                        <b>At: </b><span id="viewRejectAt">...</span> | 
                                        <b>Reason: </b><span id="viewRejectReason">...</span>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-lg-12">
                                    <div class="card">
                                        <div class="card-body">

                                            <div class="row">
                                                <style>
                                                    .custom-table {
                                                        width: 50%;
                                                        margin: auto;
                                                        /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
                                                    }
                                                </style>
                                                <table id="" class="table table-borderless custom-table">
                                                    <tr>
                                                        <th><center><i class="dripicons-location"></i>Country</center></th>
                                                        <th><center><i class="dripicons-user-id"></i> Main Function</center></th>
                                                        <th><center><i class="dripicons-calendar"></i> Strategy Year</center></th>
                                                    </tr>
                                                    <tr style="color:#b01c2e; font-weight: bold;">
                                                        <td><center id="viewCountry">Loading...</center></td>
                                                        <td><center id="viewDivision">Loading...</center></td>
                                                        <td><center id="viewYear">Loading...</center></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-12">
                                            <div class="text-sm">

                                                <button type="button" id="add_button"
                                                    class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                    data-bs-toggle="modal" data-bs-target=".addModal">
                                                    <i class="mdi mdi-plus me-1"></i> Add Initiative
                                                </button>

                                                <button type="button" id="mass_upload_button"
                                                    class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                    data-bs-toggle="modal" data-bs-target=".massuploadModal"><i
                                                        class="mdi mdi-plus me-1"></i> Mass Upload
                                                </button>

                                            </div>
                                        </div>
                                        <!-- end col-->
                                    </div>

                                    <style>
                                        .timelineOverdue {
                                            background-color: #ffe8e6 !important;
                                        }
                                        .timelineWarning {
                                            background-color: #feffeb !important;
                                        }
                                        .timelineOkay {
                                            background-color: #ebffee !important;
                                        }
                                    </style>
                                    <table id="table_data" class="table table-condensed dt-responsive w-100" style="font-size: 11px;">
                                        <thead>
                                            <tr>
                                                <th>Pillar</th>
                                                <th width="20%">Initiative</th>
                                                <th>Target</th>
                                                <th>Value Impact</th>
                                                <th>Timeline</th>
                                                <th>Created_at</th>
                                                <th>Created_by</th>
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

            <!-- Transaction Modal -->
            <div class="modal fade addModal modals" id="addModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <form method="post" id="initiative_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">
                                    Modal Title
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pillar_id" class="form-label">3Year Strategy Pillar</label>
                                                <select class="form-control select2" name="pillar_id" id="pillar_id" >
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div><div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="initiative" class="form-label">3Year Strategy Initiative</label>
                                                <select class="form-control select2" name="initiative" id="initiative" >
                                                    <option value="">None</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" id="own_init">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label for="own_initiative" class="form-label">Own Initiative</label>
                                                    <input type="text" class="form-control" id="own_initiative" name="own_initiative">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="business_category" class="form-label">Business Category</label>
                                                    <select class="form-control select2" name="business_category" id="business_category">
                                                        <option value="">...</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="target" class="form-label">Target</label>
                                                <input type="text" class="form-control" id="target" name="target">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="value_impact" class="form-label">Value Impact</label>
                                                <input type="text" class="form-control" id="value_impact" name="value_impact">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="timeline" class="form-label">Timeline</label>
                                                <div class="input-group" id="datepicker1">
                                                    <input type="text" class="form-control" data-date-format="yyyy-mm-dd" data-date-container='#datepicker1' 
                                                    data-provide="datepicker" data-date-autoclose="true" autocomplete="off" name="timeline" id="timeline">

                                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id2" />
                                <input type="hidden" name="operation" id="operation" />
                                <input type="submit" name="action" id="action" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end modal -->

            <!-- Transaction Modal -->
            <div class="modal fade massuploadModal modalss" id="massuploadModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <p><b>Notice:</b> 
                                        <a href="assets/downloads/departmental_initiatives_template.csv"  download="departmental_initiatives_template.csv"  target="_blank"><i class="fa fa-file-text-o" aria-hidden="true"></i>Download and use this Template</a>, <br> 
                                    </p>
                                </div>
                            </div>
                            <form method="post" id="mass_upload_form" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="csv_file" class="form-label">Upload File</label>
                                            <input type="file" class="form-control" name="csv_file"
                                                id="csv_file" multiple accept=".csv">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="alert alert-success " role="alert" id="successAlert"
                                        style="display:none;">
                                        <i class="mdi mdi-check-all me-2"></i>
                                        <strong>Success: </strong> <span id="successMsg"></span>
                                    </div>
                                    <div class="alert alert-danger" role="alert" id="errorAlert" style="display:none;">
                                        <i class="mdi mdi-block-helper me-2"></i>
                                        <strong>Error: </strong> <span id="errorMsg"></span>
                                    </div>
                                </div>

                                <div class="spinner-border text-primary m-1" role="status" id="loader6"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <div class="mt-3">
                                    <input type="hidden" name="id" id="id4" />
                                    <input type="hidden" name="country_strategy_id" id="ctry_strat_id" />
                                    <input type="hidden" name="operation" id="operation4" />
                                    <input type="submit" name="action" id="action4" class="btn btn-primary"
                                        value="Submit" />
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end modal -->


            <div class="modal fade viewModal" id="viewModal" tabindex="-1" role="dialog" aria-labelledby=""
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <style>
                                .banner {
                                    background:#c3c3c3;
                                    color:#323232;
                                    padding: 10px;
                                }
                            </style>
                            <div class="row">
                                <h6 class="banner">Initiative Details</h6>
                                <div class="col-sm-12 col-md-12">
                                    <table class="table table-responsive table-condensed table-striped">
                                        <tbody>
                                            <tr>
                                                <td width="20%">Pillar</td>
                                                <td><b><span id="view_pillar"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Initiative</td>
                                                <td><b><span id="view_initiative"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Target</td>
                                                <td><b><span id="view_target"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Value Impact</td>
                                                <td><b><span id="view_value_impact"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Timeline</td>
                                                <td><b><span id="view_timeline"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Created By</td>
                                                <td><b><span id="view_created_by"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Created At</td>
                                                <td><b><span id="view_created_at"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Updated By</td>
                                                <td><b><span id="view_updated_by"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Updated At</td>
                                                <td><b><span id="view_updated_at"></span></b></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <h6 class="banner">Comments Section</h6>
                                <div class="col-sm-12 col-md-12">
                                    <style>
                                        .commDiv{
                                            background: #f9f9f9;
                                            border-radius: 5px;                                
                                        }
                                        .quotedDiv {
                                            display:block;
                                            background: #e9e9e9;
                                            margin-top: 10px;
                                            margin-left: 10px;
                                            padding: 10px;
                                            border-left: 5px solid #0099c3;
                                            border-radius: 5px;
                                            width:90%;
                                            font-size: 12px;
                                        }
                                        .cmtSection {
                                            max-height: 300px;
                                            overflow: auto;
                                        }
                                    </style>
                                    <div class="d-flex justify-content-center row">
                                        <div class="col-md-12">
                                            <div class="d-flex flex-column comment-section">
                                                <div class="bg-light p-2 mb-3">
                                                    <div class="d-flex flex-row align-items-start">
                                                        <img class="rounded-circle" src="assets/images/users/avatar.png" width="40">
                                                        <textarea class="form-control ms-2 shadow-none textarea" name="comment" id="comment"></textarea>
                                                        <input type="hidden" name="initiative_id" id="initiative_id">
                                                    </div>
                                                    <div class="mt-2 text-right">
                                                        <div class="spinner-border text-primary m-1 float-end" role="status" id="loader4"
                                                            style="display: none">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                        <button class="btn btn-primary btn-sm shadow-none float-end" id="submit_comment" type="button">Post comment</button>
                                                    </div>
                                                </div>
                                                <div class="cmtSection" id="cmtSection">
                                                    <div class="spinner-border text-primary m-1" role="status" id="loader4"
                                                        style="display: none">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade actionModal" id="actionModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog ">
                    <form method="post" id="this_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">
                                    Modal Title
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card shadow-lg">
                                            <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Initiative Details</h6>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-responsive table-hover table-condensed">
                                                        <tbody>
                                                            <tr>
                                                                <td>Initiative</td>
                                                                <td><b><span id="act_initiative"></span></b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Target</td>
                                                                <td><b><span id="act_target"></span></b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Timeline</td>
                                                                <td><b><span id="act_timeline"></span></b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Measure</td>
                                                                <td><b><span id="act_measure"></span></b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Figure</td>
                                                                <td><b><span id="act_figure"></span></b></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card shadow-lg">
                                            <h6 class="card-header bg-secondary text-white border-bottom text-uppercase" id="action_title">Action</h6>
                                            <div class="card-body" id="action_body">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader3"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id3" />
                                <input type="hidden" name="operation" id="operation3" />
                                <input type="submit" name="action" id="action3" class="btn btn-primary"
                                    value="Save Changes" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </form>
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

    <script src="assets/js/app.js"></script>
    <script src="performance/strategy/js/view_business_plan.js"></script>
</body>

</html>