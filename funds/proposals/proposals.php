<?php
// (A) ACCESS CHECK
require "../../protect.php";

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
$requiredPermissions = ['view_proposal', 'add_proposal'];
$requiredModules = 'Fund';

if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>My Proposal | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta content="seris App" name="Smart Applications" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg" />
    <!-- select2 css -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

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
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18"><i class="fas fa-align-justify"></i> My Proposal</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#"> Proposals</a> ></li>
                                        <li class="breadcrumb-item active"> Proposals</li>
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
                                                <button type="button" id="add_button" class="btn btn-primary waves-effect waves-light mb-2 me-2" data-bs-toggle="modal" data-bs-target=".addModal">
                                                    <i class="mdi mdi-plus me-1"></i> Add Proposal
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end col-->
                                    </div>
                                    <table id="table_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>Ref.Number</th>
                                                <th>Subject</th>
                                                <th>Amount</th>
                                                <th>BudgetLine</th>
                                                <th>Status</th>
                                                <th>location</th>
                                                <th>Created_By</th>
                                                <th>Onbehalf_of</th>
                                                <th>Created_At</th>
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
            <div class="modal fade addModal modals" id="addModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog  modal-xl">
                    <form method="post" id="proposal_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Proposal Templates
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="basicpill-firstname-input">Date
                                                (Required)</label>

                                            <div class="input-group" id="datepicker1">
                                                <input type="text" class="form-control" data-date-format="yyyy-mm-dd" data-date-container='#datepicker1' data-provide="datepicker" data-date-autoclose="true" name="proposal_date" id="proposal_date">

                                                <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="onbehalf_of">On
                                                Behalf of</label>
                                            <select class="form-select select2" id="onbehalf_of" name="onbehalf_of">
                                                <option>...</option>
                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="budget_line" class="form-label">Budget
                                                Line</label>
                                            <select class="form-control select2" id="budget_line_category" name="budget_line">
                                                <option>...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="basicpill-phoneno-input">Subject</label>
                                            <input type="text" name="subject" id="subject" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="basicpill-address-input">Introduction</label>
                                            <textarea class="form-control" rows="4" name="introduction" id="introduction"></textarea>

                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="basicpill-address-input"> Objective
                                                & Business
                                                Case</label>
                                            <textarea class="form-control" rows="4" name="objective" id="objective"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="basicpill-address-input">Insert Supporting Document Link(One
                                                Driver Link)</label>
                                            <input type="url" class="form-control" name="additional_doc" id="additional_doc">
                                        </div>
                                    </div>
                                </div>

                                <div id="dynamicAddRemove">
                                    <div class="row">
                                        <div class="col-sm-2 mb-3 mb-sm-0">
                                            <label for="">Item</label>
                                            <input type="text" placeholder="Enter Item" id="item" name="item[]" class="form-control">
                                        </div>
                                        <div class="col-sm-2 mb-3 mb-sm-0">
                                            <label for="">Quantity</label>
                                            <input type="number" id="quantity" value="0" name="quantity[]" class="form-control quantity">
                                        </div>

                                        <div class="col-sm-2 mb-3 mb-sm-0">
                                            <label for="price">Price</label>
                                            <input type="number" id="price" value="0" name="price[]" class="form-control price">
                                        </div>
                                        <div class="col-sm-2 mb-3 mb-sm-0">
                                            <label for="total">Total</label>
                                            <input type="number" id="total" value="0" name="total[]" class="form-control total" readonly>
                                        </div>
                                        <div class="col-sm-2 mb-3 mb-sm-0">
                                            <label for="Supplier"> Supplier</label>
                                            <input class="form-control" id="supplier" type="text" name="supplier[]">
                                        </div>
                                        <div class="col-sm-2 mb-3" style="margin-bottom: 10px;">
                                            <label for="Supplier"> </label>
                                            <span class="btn btn-primary circle" id="repeatForm"> <i class="fa fa-plus" aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <br>
                                <div class="col-lg-3" style="float: right;">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Total</span>
                                        <input type="number" class="form-control FTotal" name="FTotal" id="FTotal">
                                    </div>
                                </div>

                            </div>
                            </section>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader" style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="text" name="id" id="id2" />
                                <input type="text" name="operation" id="operation" />
                                <input type="submit" name="action" id="action" class="btn btn-primary" value="Register" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end of proposal -->
            <div class="modal fade viewModal" id="viewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <table class="table table-responsive table-condensed table-striped">
                                <tbody>
                                    <tr>
                                        <td width="45%">Subject</td>
                                        <td><b><span id="view_subject"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td width="45%">Amount</td>
                                        <td><b style="color:#b01c2e"><span id="view_FTotal"></span></b></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="card">
                                <div class="card-header">
                                    Objective & Business Case
                                </div>
                                <div class="card-body">
                                    <p class="card-text" id="view_objective"></p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    Proposal introduction
                                </div>
                                <div class="card-body">
                                    <p class="card-text" id="view_introduction"></p>
                                </div>
                            </div>
                            <div class="card" id="displayr" style="display: none;">
                                <div class="card-header">
                                    Revert Reason
                                </div>
                                <div class="card-body">
                                    <p class="card-text" id="view_reasonRevert"></p>
                                </div>
                            </div>
                            <div class="card" id="display_document" style="display: none;">
                                <div class="card-header">
                                    Document
                                </div>
                                <div class="card-body">
                                    <span id="view_additional_doc" target="blank"></span>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    Proposal Assessment
                                </div>
                                <div class="card-body">

                                    <div class="table-responsive">

                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Items</th>
                                                    <th scope="col">Quantity</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">Total</th>
                                                    <th scope="col">Supplier</th>
                                                </tr>
                                            </thead>
                                            <tbody id="view_proposal_items">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    Proposal Comment
                                </div>
                                <div class="col-xl-12">
                                    <div class="card">

                                        <div class="card-body">
                                            <div class="mt-4">
                                                <div data-simplebar style="max-height: 250px;">
                                                    <div class="table-responsive" id="view_commentArray">
                                                        <!-- comment -->
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

            <div class="modal fade actionModal" id="actionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="post" id="this_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">
                                    Modal Title
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card shadow-lg">
                                            <h6 class="card-header bg-secondary text-white border-bottom text-uppercase">
                                                Request Details</h6>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-responsive table-hover table-condensed">
                                                        <tbody>
                                                            <tr>
                                                                <th>Ref.Number</th>
                                                                <th>Subject</th>
                                                                <th>Total Amount</th>
                                                                <th>Proposed date</th>
                                                            </tr>
                                                            <tr>
                                                                <td><span id="prop_refNo"></span></td>
                                                                <td><span id="prop_subject"></span></td>
                                                                <td><span id="prop_totalAmount2"></span></td>
                                                                <td><span id="prop_proposeddate"></span></td>
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
                                <div class="spinner-border text-primary m-1" role="status" id="loader3" style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id3" />
                                <input type="hidden" name="operation" id="operation3" />
                                <input type="submit" name="action" id="action3" class="btn btn-primary" value="Save Changes" />
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
    <?php include '../../include/rightside.php'; ?>
    <div class="rightbar-overlay"></div>

    </script>
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

    <script src="assets/libs/jquery.repeater/jquery.repeater.min.js"></script>
    <script src="assets/js/pages/form-repeater.int.js"></script>

    <script src="assets/js/app.js"></script>
    <script src="funds/proposals/js/proposals.js"></script>
</body>
<script type="text/javascript">

</script>
<style type="text/css">
    .circle {
        width: 28px;
        height: 28px;
        padding: 5px 6px;
        border-radius: 60px;
        font-size: 15px;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .comment-section {
        list-style: none;
        max-width: 100%;
        width: 100%;
        margin: 0px auto;
        padding: 0px;
    }

    .error-message {
        color: red;
    }

    .comment {
        display: flex;
        border-radius: 3px;
        margin-bottom: 45px;
        flex-wrap: wrap;
    }

    .comment.user-comment {
        color: #808080;
    }

    .comment.author-comment {
        color: #60686d;
        justify-content: flex-end;
    }

    /* User and time info */

    .comment .info {
        width: 17%;
    }

    .comment.user-comment .info {
        text-align: right;
    }

    .comment.author-comment .info {
        order: 3;
    }


    .comment .info a {
        /* User name */
        display: block;
        text-decoration: none;
        color: #656c71;
        font-weight: bold;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        padding: 10px 0 3px 0;
    }

    .comment .info span {
        /* Time */
        font-size: 11px;
        color: #9ca7af;
    }


    /* The user avatar */

    .comment .avatar {
        width: 8%;
    }

    .comment.user-comment .avatar {
        padding: 10px 18px 0 3px;
    }

    .comment.author-comment .avatar {
        order: 2;
        padding: 10px 3px 0 18px;
    }

    .comment .avatar img {
        display: block;
        border-radius: 50%;
    }

    .comment.user-comment .avatar img {
        float: right;
    }





    /* The comment text */

    .comment p {
        line-height: 1.5;
        padding: 18px 22px;
        width: 50%;
        position: relative;
        word-wrap: break-word;
    }

    .comment.user-comment p {
        background-color: #f3f3f3;
    }

    .comment.author-comment p {
        background-color: #e2f8ff;
        order: 1;
    }

    .user-comment p:after {
        content: '';
        position: absolute;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 2px solid #f3f3f3;
        left: -8px;
        top: 18px;
    }

    .author-comment p:after {
        content: '';
        position: absolute;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 2px solid #e2f8ff;
        right: -8px;
        top: 18px;
    }

    .write-new {
        margin: 80px auto 0;
        width: 50%;
    }

    .write-new textarea {
        color: #444;
        font: inherit;

        outline: 0;
        border-radius: 3px;
        border: 1px solid #cecece;
        background-color: #fefefe;
        box-shadow: 1px 2px 1px 0 rgba(0, 0, 0, 0.06);
        overflow: auto;

        width: 100%;
        min-height: 80px;
        padding: 15px 20px;
    }

    .write-new img {
        border-radius: 50%;
        margin-top: 15px;
    }

    .write-new button {
        float: right;
        background-color: #87bae1;
        box-shadow: 1px 2px 1px 0 rgba(0, 0, 0, 0.12);
        border-radius: 2px;
        border: 0;
        color: #ffffff;
        font-weight: bold;
        cursor: pointer;

        padding: 10px 25px;
        margin-top: 18px;
    }

    @media (max-width: 800px) {
        .comment p {
            width: 100%;
        }

        .comment.user-comment .info {
            order: 3;
            text-align: left;
        }

        .comment.user-comment .avatar {
            order: 2;
        }

        .comment.user-comment p {
            order: 1;
        }

        .comment.author-comment {
            justify-content: flex-start;
        }


        .comment-section {
            margin-top: 10px;
        }

        .comment .info {
            width: auto;
        }

        .comment .info a {
            padding-top: 15px;
        }

        .comment.user-comment .avatar,
        .comment.author-comment .avatar {
            padding: 15px 10px 0 18px;
            width: auto;
        }

        .comment.user-comment p:after,
        .comment.author-comment p:after {
            width: 12px;
            height: 12px;
            top: initial;
            left: 28px;
            bottom: -6px;
        }

        .write-new {
            width: 100%;
        }
    }
</style>

</html>