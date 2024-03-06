<?php
// (A) ACCESS CHECK
require "../../protect.php";

if ($row['can_be_super_user'] != 1 && $row['can_be_cash_coo'] != 1) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>COO - PettyCash | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i>
                                    COO Operations </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">PettyCash </a> ></li>
                                        <li class="breadcrumb-item active">COO</li>
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
                                    <button type="button" class="btn btn-primary">FINANCE CASHBOX:: BALANCE = <span id="autoFinaBal"></span> </button>
                                    <?php if ($is_on_budget) { ?>
                                    <button type="button" id="add_button"
                                        class="btn btn-primary waves-effect waves-light "
                                        data-bs-toggle="modal" data-bs-target=".budgetLinesModal">
                                        BUDGET LINES
                                    </button>
                                    <button type="button" id="hod_req_button" class="btn btn-primary waves-effect waves-light "
                                        data-bs-toggle="modal" data-bs-target=".budgetRequestsModal">BUDGET LINE REQUESTS LOGS
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <table id="request_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>Ref.No</th>
                                                <th>Category</th>
                                                <th>Budget_Line</th>
                                                <th>Total_Amount</th>
                                                <th>Status</th>
                                                <th>Requested_At</th>
                                                <th>Requested_By</th>
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

            <div class="modal fade budgetLinesModal modals" id="budgetLinesModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">
                                Departmental Budgets
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive"> 
                                <table id="budgets_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Category</th>
                                            <th>Total Amount</th>
                                            <th>Used Amount</th>
                                            <th>Remaining Amount</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                        </tr>
                                    </thead>
                                </table>                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade budgetRequestsModal" id="budgetRequestsModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">
                                Budget Requests
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive"> 
                                <table id="budgets_requests_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 10px;">
                                    <thead>
                                        <tr>
                                            <th>Ref.Number</th>
                                            <th>Amount</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>RequestDate</th>
                                            <th>RequestedBy</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade viewModal" id="viewModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby=""
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">
                                Order Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12 col-lg-6">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Request Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped" style="font-size: 12px;">
                                                <tbody>
                                                    <tr>
                                                        <td width="50%">Ref. Number</td>
                                                        <th><span id="view_refNo"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <th><span id="view_visitDate"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Category</td>
                                                        <th><span id="view_caregory"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Affected Budget</td>
                                                        <th><span id="view_budget_category"></span></th>
                                                    </tr>
                                                    
                                                    <tr id="display_departure" style="display: none;">
                                                        <td>Departure Date</td>
                                                        <th><span id="view_air_departure_date"></span></th>
                                                    </tr>
                                                    <tr id="display_return" style="display: none;">
                                                        <td>Departure Date</td>
                                                        <th><span id="view_air_return_date"></span></th>
                                                    </tr>
                                                    <tr id="display_checkin" style="display: none;">
                                                        <td>Checkin Date</td>
                                                        <th><span id="view_checkin"></span></th>
                                                    </tr>
                                                    <tr id="display_checkout" style="display: none;">
                                                        <td>Checkout Date</td>
                                                        <th><span id="view_checkout"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Requested By</td>
                                                        <th><span id="view_requestBy"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Request Date</td>
                                                        <th><span id="view_requestDate"></span></th>
                                                    </tr>
                                                    <tr id="display_phone" style="display: none;">
                                                        <td>Mobile Money Number</td>
                                                        <th><span id="view_phone"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td>
                                                        <th><span id="view_status"></span></th>
                                                    </tr>
                                                    <tr id="display_cheque" style="display: none;">
                                                        <td>Cheque Number</td>
                                                        <th><span id="view_cheque"></span></th>
                                                    </tr>
                                                    <tr id="display_bank" style="display: none;">
                                                        <td>Bank Name</td>
                                                        <th><span id="view_bank_name"></span></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-lg-6">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Money Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped" style="font-size: 12px;">
                                                <tbody>
                                                    <tr id="display_transport" style="display: none;">
                                                        <td width="50%">Transport</td>
                                                        <th><span id="view_transport"></span></th>
                                                    </tr>
                                                    <tr id="display_accomodation" style="display: none;">
                                                        <td>Accomodation</td>
                                                        <th><span id="view_accomodation"></span></th>
                                                    </tr>
                                                    <tr id="display_meals" style="display: none;">
                                                        <td>Meals</td>
                                                        <th><span id="view_meals"></span></th>
                                                    </tr> 
                                                    <tr>
                                                        <td width="50%"><span id="other_expenses_to_amount"></span></td>
                                                        <th><span id="view_otherExpenses"></span></th>
                                                    </tr> 
                                                    <tr>
                                                        <td>Requester Charges</td>
                                                        <th><span id="view_requester_charges"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Disburser Charges</td>
                                                        <th><span id="view_charges"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Extra After Clearance</td>
                                                        <th style="border-bottom: 2px solid #656565;"><span id="view_afterClearance"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <th>TOTAL AMOUNT</th>
                                                        <th><span id="view_totalAmount" class="text-danger"></span></th>
                                                    </tr>
                                                    <tr id="display_disbursed" style="display: none;">
                                                        <th>DISBURSED</th>
                                                        <th style="border-bottom: 2px solid #656565;"><span id="view_disbursed" class="text-success"></span></th>
                                                    </tr>
                                                    <tr id="display_remaining" style="display: none;">
                                                        <th>REMAINING</th>
                                                        <th><span id="view_remaining" class="text-warning"></span></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">					
                                <div class="col-sm-12">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Description Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped" style="font-size: 12px;">
                                                <tr>
                                                    <td width="30%">Description :</td>
                                                    <th><span id="view_description"></span></th>
                                                </tr>
                                                <tr id="display_providers" style="display: none;">
                                                    <td>Providers To Visit :</td>
                                                    <th><span id="view_providers"></span></th>
                                                </tr>
                                                <tr id="display_customers" style="display: none;">
                                                    <td>Customers To Visit :</td>
                                                    <th><span id="view_customers"></span></th>
                                                </tr>
                                                <tr id="display_document" style="display: none;">
                                                    <td>Additional Document :</td>
                                                    <th><span id="view_additional_doc"></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">					
                                <div class="col-sm-12">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Approval Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped" style="font-size: 12px;">
                                                <tr>
                                                    <th width="30%">HOD Level:</th>
                                                    <td> Name: <b><span id="view_hodApprove"></span></b></td>
                                                    <td>Date: <b><span id="view_hodDate"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Finance Level:</th>
                                                    <td> Name: <b><span id="view_financeApprove"></span></b></td>
                                                    <td>Date: <b><span id="view_financeDate"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>COO/Country Manager Level:</th>
                                                    <td> Name: <b><span id="view_managerApprove"></span></b></td>
                                                    <td>Date: <b><span id="view_managerDate"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>GMD Level:</th>
                                                    <td> Name: <b><span id="view_gmdApprove"></span></b></td>
                                                    <td>Date: <b><span id="view_gmdDate"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>GMD Comment:</th>
                                                    <td colspan="2" class="bg-info"> <b><span id="view_gmdComment"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Disbursement Level:</th>
                                                    <td> Name: <b><span id="view_disbursedBy"></span></b></td>
                                                    <td>Date: <b><span id="view_disburseDate"></span></b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">					
                                <div class="col-sm-12">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Other Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped">
                                                <tr>
                                                    <th width="30%">Returned:</th>
                                                    <td> Name: <b><span id="view_ReturnedBy"></span></b></td>
                                                    <td>Date: <b><span id="view_ReturnDate"></span></b></td>
                                                    <td>Reason: <b><span id="view_returnReason"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Suspension:</th>
                                                    <td> Name: <b><span id="view_suspendedBy"></span></b></td>
                                                    <td colspan="2">Date: <b><span id="view_suspendDate"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Rejection:</th>
                                                    <td> Name: <b><span id="view_rejectedBy"></span></b></td>
                                                    <td>Date: <b><span id="view_rejectDate"></span></b></td>
                                                    <td>Reason: <b><span id="view_rejectReason"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Requester Reconciliation:</th>
                                                    <td> Name: <b><span id="view_clearedBy"></span></b></td>
                                                    <td>Date: <b><span id="view_clearanceDate"></span></b></td>
                                                    <td>Comment: <b><span id="view_clearenceReason"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <th>Disburser Reconciliation:</th>
                                                    <td colspan="3"> Comment: <b><span id="view_clearSupervisorComment"></span></b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="display_receipt" style="display: none;">					
                                <div class="col-sm-12 col-md-8">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">Receipt</h6>
                                        <div class="card-body">
                                            <div id="view_receiptImage"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
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

            <div class="modal fade reasonModal" id="reasonModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog ">
                    <form method="post" id="this_form1" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title title-of-modal" id="transaction-detailModalLabel">
                                    Modal Title
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card shadow-lg">
                                            <h6 class="card-header bg-secondary text-white border-bottom text-uppercase" id="action_title1">Action</h6>
                                            <div class="card-body" id="action_body1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader4"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id4" />
                                <input type="hidden" name="operation" id="operation4" />
                                <input type="submit" name="action" id="action4" class="btn btn-primary"
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

    <script src="assets/js/app.js"></script>
    <script src="funds/pettycash/js/coo.js"></script>
</body>

</html>