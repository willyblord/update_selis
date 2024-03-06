<?php
// (A) ACCESS CHECK
require "../../protect.php";

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'UNIT_LADER_ROLE'];
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
    <title>Annual Unit Initiatives - Strategy | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> VIEW GROUP BSC INITIATIVES</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Strategy Performance</a> ></li>
                                        <li class="breadcrumb-item active">3 Year Strategy</li>
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
                                        <a href="annual-unit-bsc" class="btn btn-secondary waves-effect waves-light mb-2 me-2">
                                            <i class="fas fa-arrow-alt-circle-left"></i> Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card">
                                        <div class="card-body">
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#ownerTab" role="tab">
                                                        <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                        <span class="d-none d-sm-block">BSC Owner & Scores</span> 
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#initiativesTab" role="tab">
                                                        <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                        <span class="d-none d-sm-block">BSC Initiatives & Targets</span>   
                                                    </a>
                                                </li>
                                            </ul>
            
                                            <!-- Tab panes -->
                                            <div class="tab-content p-3 text-muted">
                                                <div class="tab-pane active" id="ownerTab" role="tabpanel">
                                                    <div class="row mt-3">
                                                        <h5>BSC Owner Details</h5>
                                                        <div class="col-sm-12 col-lg-12">
                                                            <div class="card shadow-lg">
                                                                <div class="card-body">

                                                                    <div class="row">
                                                                        <table id="" class="table table-borderless custom-table">
                                                                            <tr>
                                                                                <th><center><i class="fas fa-user"></i> BSC Owner</center></th>
                                                                                <th><center><i class="dripicons-location"></i>Country</center></th>
                                                                                <th><center><i class="dripicons-user-id"></i> Department</center></th>
                                                                                <th><center><i class="dripicons-calendar"></i> BSC Year</center></th>
                                                                            </tr>
                                                                            <tr style="color:#b01c2e; font-weight: bold;">
                                                                                <td><center id="viewName">Loading...</center></td>
                                                                                <td><center id="viewCountry">Loading...</center></td>
                                                                                <td><center id="viewDepartment">Loading...</center></td>
                                                                                <td><center id="viewYear">Loading...</center></td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-12">                                                         
                                                            <h5>Scores Summary</h5>
                                                            <div class="card shadow-lg">
                                                                <div class="card-body" style="padding-bottom: 0px;">   
                                                                    <div class="row">
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#605ca8; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Total Target Score</p>
                                                                                            <h5 class="mb-0"><span id="t_target_scrore">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#e97900; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Achieved Weight</p>
                                                                                            <h5 class="mb-0"><span id="t_weight">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#46bd00; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Exceeded Score</p>
                                                                                            <h5 class="mb-0"><span id="t_weight">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#0069b0; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Appraised Target Score</p>
                                                                                            <h5 class="mb-0"><span id="a_target_scrore">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#009d58; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Appraised Weight</p>
                                                                                            <h5 class="mb-0"><span id="a_weight">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                        <div class="col-sm-6 col-lg-2">
                                                                            <div class="card mini-stats-wid text-white" style="background:#dc0000; text-align:center;">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex">
                                                                                        <div class="flex-grow-1">
                                                                                            <p class="fw-medium" style="font-size: 12px;">Appraised Exceeded</p>
                                                                                            <h5 class="mb-0"><span id="a_weight">0%</span></h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div><!--end col-->
                                                                    </div><!--end row-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="initiativesTab" role="tabpanel">
                                                    <div class="row mb-2 mt-5">
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
                                                                <th>Parameter</th>
                                                                <th>Initiative</th>
                                                                <th>Target</th>
                                                                <th>Timeline</th>
                                                                <th>Measure</th>
                                                                <th>Figure</th>
                                                                <th>Weight</th>
                                                                <th>Raw Score</th>
                                                                <th>Target Score</th>
                                                                <th>Achieved Weight</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
            
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
                <div class="modal-dialog modal-lg modal-dialog-centered">
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
                                                <label for="pillar_id" class="form-label">Strategy Pillar</label>
                                                <select class="form-control select2" name="pillar_id" id="pillar_id" required>
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="initiative" class="form-label">Initiative</label>
                                                <input type="text" class="form-control" id="initiative" name="initiative">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="target" class="form-label">Target</label>
                                                <input type="text" class="form-control" id="target" name="target">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="value_impact" class="form-label">Value Impact</label>
                                                <input type="text" class="form-control" id="value_impact" name="value_impact">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="timeline" class="form-label">Timeline</label>
                                                <div class="input-group" id="datepicker1">
                                                    <input type="text" class="form-control" data-date-format="yyyy-mm-dd" data-date-container='#datepicker1' 
                                                    data-provide="datepicker" data-date-autoclose="true" autocomplete="off" name="timeline" id="timeline">

                                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="measure" class="form-label">Target Measure</label>
                                                <select class="form-control" name="measure" id="measure" required>
                                                    <option value="">...</option>
                                                    <option value="Quantitative Financial">Quantitative Financial</option>
                                                    <option value="Quantitative Count">Quantitative Count</option>
                                                    <option value="Quantitative Parcentage">Quantitative Parcentage</option>
                                                    <option value="Qualitative">Qualitative</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="figure" class="form-label">Target Figure</label>
                                                <input type="number" min="0" step=".01" class="form-control" id="figure" name="figure">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="number" min="0" step=".01" class="form-control" id="weight" name="weight">
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
                                <div class="col-sm-12 col-md-7">
                                    <table class="table table-responsive table-condensed table-striped">
                                        <tbody>
                                            <tr>
                                                <td>Pillar</td>
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
                                <div class="col-sm-12 col-md-5">
                                    <table class="table table-responsive table-condensed table-striped">
                                        <tbody>
                                            <tr>
                                                <td>Timeline</td>
                                                <td><b><span id="view_timeline"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Measure</td>
                                                <td><b><span id="view_measure"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Figure</td>
                                                <td><b><span style="color:#390505;" id="view_figure"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Weight</td>
                                                <td><b><span style="color:#b01c2e;" id="view_weight"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Raw Score</td>
                                                <td><b><span style="color:#005ca4;" id="view_raw_score"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Target Score</td>
                                                <td><b><span style="color:#009114;" id="view_target_score"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Computed Score</td>
                                                <td><b><span style="color:#8b0063;" id="view_computed_score"></span></b></td>
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
    <script src="performance/bsc/js/view_unit_initiatives.js"></script>
</body>

</html>