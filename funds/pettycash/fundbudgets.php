<?php
// (A) ACCESS CHECK
require "../../protect.php";

if ((($row['can_be_super_user'] != 1) && ($row['can_be_cash_finance'] != 1) && ($row['can_be_cash_coo'] != 1)) ) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Budget Lines | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> Departmental Budget Lines</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">PettyCash</a> ></li>
                                        <li class="breadcrumb-item active">Budget Lines</li>
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
                                                <button type="button" id="add_button"
                                                    class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                    data-bs-toggle="modal" data-bs-target=".addBudgetModal">
                                                    <i class="mdi mdi-plus me-1"></i> Add Budget
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end col-->
                                    </div>

                                    <table id="table_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th width="15%">Budget Category</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Remaining Amount</th>
                                                <th>Status</th>
                                                <th>Insert Date</th>
                                                <th>Inserted By</th>
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
            <div class="modal fade addBudgetModal modals" id="addBudgetModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="budget_form" enctype="multipart/form-data">
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
                                                <label for="department" class="form-label">Department</label>
                                                <select class="form-control select2" name="department" id="department" required>
                                                    <option>...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="budget_category" class="form-label">Budget Category</label>
                                                <select class="form-control select2" name="budget_category" id="budget_category" required>
                                                    <option>...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12">
                                            <div class="mb-3">
                                                <label class="col-form-label">Start and End Date</label>
                                                <div class="input-daterange input-group" id="project-date-inputgroup" data-provide="datepicker" data-date-format="yyyy-mm-dd"  data-date-container='#project-date-inputgroup' data-date-autoclose="true">
                                                    <input type="text" class="form-control" placeholder="Start Date" name="start_date" id="start_date" autocomplete="off" />
                                                    <input type="text" class="form-control" placeholder="End Date" name="end_date" id="end_date" autocomplete="off" />
                                                </div>
                                            </div>
                                        </div>                                        
                                        <div class="col-sm-12 col-md-12">
                                            <div class="mb-3">
                                                <label class="col-form-label">Amount</label>
                                                <input type="number" min="0" class="form-control" name="initial_amount" id="initial_amount" required>
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

            <div class="modal fade viewModal" id="viewModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
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
                                        <td width="40%">Department</td>
                                        <td><b><span id="view_department"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Budget Category</td>
                                        <td><b><span id="view_budget_category"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Dates</td>
                                        <td>From <b><span id="view_start_date"></span></b> To <b><span id="view_end_date"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Initial Amount</td>
                                        <td><b><span id="view_initial_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>TopUp Amount</td>
                                        <td><b><span id="view_topup_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                    <tr>
                                        <td>Reduced Amount</td>
                                        <td><b><span id="view_deducted_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Total Amount</td>
                                        <td><b><span id="view_total_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Used Amount</td>
                                        <td><b><span id="view_used_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Remaining Amount</td>
                                        <td><b><span id="view_remaining_amount"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td><b><span id="view_status"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Insert Date</td>
                                        <td><b><span id="view_inserted_at"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Inserted By</td>
                                        <td><b><span id="view_insterted_by"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Last Update</td>
                                        <td><b><span id="view_updated_at"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Updated By</td>
                                        <td><b><span id="view_updated_by"></span></b></td>
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

            <div class="modal fade actionModal" id="actionModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
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
                                            <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Request Details</h6>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-responsive table-hover table-condensed">
                                                        <tbody>
                                                            <tr>
                                                                <th>Ref.Number</th>
                                                                <th>Category</th>
                                                                <th>Total_Amount</th>
                                                                <th>Requested_By</th>
                                                                <th>Requested_At</th>
                                                            </tr>
                                                            <tr>
                                                                <td><span id="budg_refNo"></span></td>
                                                                <td><span id="budg_category"></span></td>
                                                                <td><span id="budg_totalAmount"></span></td>
                                                                <td><span id="budg_requestBy"></span></td>
                                                                <td><span id="budg_requestDate"></span></td>
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
    <script src="funds/pettycash/js/funds.js"></script>
</body>

</html>