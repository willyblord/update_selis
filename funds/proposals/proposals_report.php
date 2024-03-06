<?php
// (A) ACCESS CHECK
require "../../protect.php";
if ($row['can_be_super_user'] != 1 && $row['can_view_cash_reports'] != 1) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Proposal Report | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta content="seris App" name="Smart Applications" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg" />
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
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> Proposal Report
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Proposal</a> ></li>
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
                                                    <label for="country" class="form-label">COUNTRY </label>
                                                    <select class="form-select select2" id="country" name="country">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="department" class="form-label">DEPARTMENT</label>
                                                    <select class="form-select select2" id="department"
                                                        name="department">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="budget_category" class="form-label">PROPOSAL
                                                        BUDGET LINE</label>
                                                    <select class="form-select select2" id="budget_line"
                                                        name="budget_line">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="statusCheck" class="form-label">TYPE </label>
                                                    <select class="form-select select2" id="statusCheck"
                                                        name="statusCheck">
                                                        <option selected>All</option>
                                                        <option value="disbursed">Disbursed Requests</option>
                                                        <option value="inprogress">Inprogress Requests</option>
                                                        <option value="declined">Declined Requests</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">STATUS </label>
                                                    <select class="form-select select2" id="status" name="status">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="created_by" class="form-label">PROPOSED BY </label>
                                                    <select class="form-select select2" id="created_by"
                                                        name="created_by">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
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
                                                    <div class="spinner-border text-primary m-1" role="status"
                                                        id="loader" style="display:none;">
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

                                            <table id="data_table" class="table table-hover dt-responsive w-100"
                                                style="font-size: 0.80em; cursor:pointer">
                                                <thead>
                                                    <tr>
                                                        <th>Ref.Number</th>
                                                        <th>Subject</th>
                                                        <th>Amount</th>
                                                        <th>BudgetLine</th>
                                                        <th>Status</th>
                                                        <th>Created_By</th>
                                                        <th>Onbehalf_of</th>
                                                        <th>Created_At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <center><b>No Data to display</b></center>
                                                        </td>
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

            <div class="modal fade viewModal" id="viewModal" data-bs-backdrop="static" data-bs-keyboard="false"
                tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">
                                Order Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <!-- <div id="single_extract_div"></div> -->

                            <div class="row">
                                <div class="col-sm-12 col-lg-6">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Request Details</h6>
                                        <div class="card-body">

                                            <table class="table table-responsive table-sm table-striped"
                                                style="font-size: 12px;">
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
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Money Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped"
                                                style="font-size: 12px;">
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
                                                        <th style="border-bottom: 2px solid #656565;"><span
                                                                id="view_afterClearance"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <th>TOTAL AMOUNT</th>
                                                        <th><span id="view_totalAmount" class="text-danger"></span></th>
                                                    </tr>
                                                    <tr id="display_disbursed" style="display: none;">
                                                        <th>DISBURSED</th>
                                                        <th style="border-bottom: 2px solid #656565;"><span
                                                                id="view_disbursed" class="text-success"></span></th>
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
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Description Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped"
                                                style="font-size: 12px;">
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
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Approval Details</h6>
                                        <div class="card-body">
                                            <table class="table table-responsive table-sm table-striped"
                                                style="font-size: 12px;">
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
                                                    <td colspan="2" class="bg-info"> <b><span
                                                                id="view_gmdComment"></span></b></td>
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
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Other Details</h6>
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
                                                    <td colspan="2">Date: <b><span id="view_suspendDate"></span></b>
                                                    </td>
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
                                                    <td colspan="3"> Comment: <b><span
                                                                id="view_clearSupervisorComment"></span></b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="display_receipt" style="display: none;">
                                <div class="col-sm-12 col-md-8">
                                    <div class="card shadow-lg">
                                        <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                            Receipt</h6>
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

            <?php include '../../include/footer.php'; ?>
        </div>
        <!-- end main content-->
    </div>
    <?php include '../../include/rightside.php'; ?>
    <div class="rightbar-overlay"></div>

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
    <script src="funds/proposals/js/report.js"></script>
</body>

</html>