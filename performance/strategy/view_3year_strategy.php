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
    <title>3 Year - Strategy Performance | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> VIEW 3 YEAR STRATEGY</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Strategy Performance</a> ></li>
                                        <li class="breadcrumb-item"><a href="#">3 Year Strategy</a> ></li>
                                        <li class="breadcrumb-item active">View</li>
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
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <div class="text-sm">
                                                <a href="strategy-3-year" class="btn btn-secondary waves-effect waves-light mb-2 me-2">
                                                <i class="fas fa-arrow-alt-circle-left"></i> Back to List
                                                </a>
                                            </div>
                                        </div>
                                        <!-- end col-->
                                    </div>

                                    <div>
                                        <center><h4>SMART STRATEGY: <span style="color:#b01c2e;" id="view_year_range">Loading...</span></h4></center> <br>
                                        <center><h4>Vision</h4></center>
                                        <center><p id="view_vision">Loading...</p></center>
                                        <center><h4>Mission</h4></center>
                                        <center><p id="view_mission">Loading...</p></center>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
    
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#valuesTab" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                <span class="d-none d-sm-block">VALUES</span> 
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#pillarsTab" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                <span class="d-none d-sm-block">PILLARS</span> 
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#initiativesTab" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                <span class="d-none d-sm-block">INITIATIVES</span>   
                                            </a>
                                        </li>
                                    </ul>
    
                                    <!-- Tab panes -->
                                    <div class="tab-content p-3 text-muted">
                                        <div class="tab-pane active" id="valuesTab" role="tabpanel">
                                            <div class="row mb-2 mt-3">
                                                <div class="col-sm-4">
                                                    <div class="text-sm">
                                                        <button type="button" id="add_value_button"
                                                            class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                            data-bs-toggle="modal" data-bs-target=".addModal">
                                                            <i class="mdi mdi-plus me-1"></i> Add Values
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- end col-->
                                            </div>

                                            <table id="value_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                                <thead>
                                                    <tr>
                                                        <th>Value Title</th>
                                                        <th>Description</th>
                                                        <th>Created_At</th>
                                                        <th>Created_By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="pillarsTab" role="tabpanel">
                                            <div class="row mb-2 mt-3">
                                                <div class="col-sm-4">
                                                    <div class="text-sm">
                                                        <button type="button" id="add_pillar_button"
                                                            class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                            data-bs-toggle="modal" data-bs-target=".addPillarModal">
                                                            <i class="mdi mdi-plus me-1"></i> Add Pillar
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
                                            
                                            <div class="table-responsive">
                                                <table id="pillar_data" class="table table-condensed table-hover w-100" style="font-size: 12px;">
                                                    <thead>
                                                        <tr style="text-align: justify;">
                                                            <th width="15%">Strategy_Pillar</th>
                                                            <th width="">Strategic_Objective</th>
                                                            <th width="">Picture_of_Success</th>
                                                            <th width="10%">Created_By</th>
                                                            <th>Created_At</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="initiativesTab" role="tabpanel">
                                            <div class="row mb-2 mt-3">
                                                <div class="col-sm-4">
                                                    <div class="text-sm">
                                                        <button type="button" id="add_initiative_button"
                                                            class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                            data-bs-toggle="modal" data-bs-target=".addInitiativeModal">
                                                            <i class="mdi mdi-plus me-1"></i> Add Initiatives
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- end col-->
                                            </div>

                                            <table id="initiative_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Initiative</th>
                                                        <th>Pillar</th>
                                                        <th>Business Category</th>
                                                        <th width="15%">Target</th>
                                                        <th width="15%">Timeline</th>
                                                        <th>Created_At</th>
                                                        <th>Created_By</th>
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
            <div class="modal fade addModal" id="addModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog ">
                    <form method="post" id="strategy_value_form" enctype="multipart/form-data">
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
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="value_title" class="form-label">Value Title</label>
                                                <input type="text" class="form-control" id="value_title" name="value_title">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="value_description" class="form-label">Description</label>
                                                <textarea class="form-control" name="value_description" id="value_description" rows="4"></textarea>
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
            <div class="modal fade addInitiativeModal modalsss" id="addInitiativeModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
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
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="group_initiative" class="form-label">Initiative</label>
                                                <input type="text" class="form-control" id="group_initiative" name="group_initiative">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="business_category" class="form-label">Business Category</label>
                                                <select class="form-control select2" name="business_category" id="business_category" >
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="pillar_id" class="form-label">Strategy Pillar</label>
                                                <select class="form-control select2" name="pillar_id" id="pillar_id" >
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="timeline" class="form-label">Timeline</label>
                                                <input type="text" class="form-control" id="timeline" name="timeline">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="target" class="form-label">Target</label>
                                                <textarea class="form-control" name="target" id="target" rows="4"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="measure" class="form-label">Measure</label>
                                                <textarea class="form-control" name="measure" id="measure" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader3"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id5" />
                                <input type="hidden" name="operation" id="operation3" />
                                <input type="submit" name="action" id="action3" class="btn btn-primary"
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
            <div class="modal fade addPillarModal modals" id="addPillarModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog ">
                    <form method="post" id="strategy_pillar_form" enctype="multipart/form-data">
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
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="strategy_pillar" class="form-label">Pillar Name</label>
                                                <select class="form-control select2" name="strategy_pillar" id="strategy_pillar" >
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="strategic_objective" class="form-label">Strategic Objective</label>
                                                <textarea class="form-control" name="strategic_objective" id="strategic_objective" rows="4"></textarea>
                                            </div>
                                        </div>                                        
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="picture_of_success" class="form-label">Picture of Success</label>
                                                <textarea class="form-control" name="picture_of_success" id="picture_of_success" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader1"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id3" />
                                <input type="hidden" name="operation" id="operation1" />
                                <input type="submit" name="action" id="action1" class="btn btn-primary"
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
                    <div class="modal-dialog  modal-dialog-centered modal-fullscreen-lg-down">
                        <!-- <form method="post" id="property_form" autocomplete="off" enctype="multipart/form-data"> -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p><b>Notice:</b> 
                                            <a href="assets/downloads/group_pillars_template.csv" download="group_pillars_template.csv" target="_blank"><i class="fa fa-file-text-o" aria-hidden="true"></i>Download and use this Template</a>, <br> 
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
                                        <input type="hidden" class="form-control" name="stratId" id="stratId">
                                    </div>

                                    <div class="spinner-border text-primary m-1" role="status" id="loader2"
                                        style="display:none;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="mt-3">
                                        <input type="hidden" name="id" id="id4" />
                                        <input type="hidden" name="operation" id="operation2" />
                                        <input type="submit" name="action" id="action2" class="btn btn-primary"
                                            value="Submit" />
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                        <!-- </form> -->
                    </div>
                </div>
                <!-- end modal -->

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
    <script src="performance/strategy/js/view3year.js"></script>
</body>

</html>