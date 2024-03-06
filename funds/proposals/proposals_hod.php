<?php
// (A) ACCESS CHECK
require "../../protect.php";

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE', 'HOD_ROLE'];
$requiredPermissions = ['view_proposal', 'add_proposal', 'approve_proposal'];
$requiredModules = 'Fund';

if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
    header("Location: welcome");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>HOD - Proposals | SERIS</title>
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
                                <h4 class="mb-sm-0 font-size-18"> <i class="fas fa-align-justify"></i> HOD
                                    Proposals </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Proposals </a> ></li>
                                        <li class="breadcrumb-item active">HOD</li>
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
                                    <table id="table_data" class="table table-condensed table-hover dt-responsive w-100" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>Ref.Number</th>
                                                <th>Subject</th>
                                                <th>Amount</th>
                                                <th>BudgetLine</th>
                                                <th>Status</th>
                                                <th>Location</th>
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


            <div class="modal fade budgetTopupModal modals" id="budgetTopupModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="budget_topup_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">
                                    Budget Request
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="budget_to_deduct" class="form-label">Budget To Deduct</label>
                                            <select class="form-select select2" id="budget_to_deduct" name="budget_to_deduct">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="budget_to_increase" class="form-label">Budget To
                                                Increase</label>
                                            <select class="form-select select2" id="budget_to_increase" name="budget_to_increase">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="amount">Amount</label>
                                            <input type="number" class="form-control" name="amount" id="amount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" rows="4" name="description" id="description" required></textarea>
                                        </div>

                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader" style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id2" />
                                <input type="hidden" name="operation" id="operation" />
                                <input type="submit" name="action" id="action" class="btn btn-primary" value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
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
                                    Additional Document
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
                                        <table class="table table-striped table-hover" id="view_proposal_items">
                                            <thead>
                                                <tr>
                                                    <th>Items</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Amount</th>
                                                    <th>Supplier</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="displayc" style="display: none;">
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
            <!-- end -->

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
                                                            <tr style="color:#b01c2e;">
                                                                <th>Ref.Number</th>
                                                                <th>Subject</th>
                                                                <th>Total Amount</th>
                                                                <th>Proposed date</th>
                                                            </tr>
                                                            <tr>
                                                                <td><span id="prop_refNo"></span></td>
                                                                <td><span id="prop_subject"></span></td>
                                                                <td style="color: red;"><span id="prop_totalAmount"></span></td>
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
    <script src="funds/proposals/js/hod.js"></script>

</body>
<style type="text/css">
    .circle {
        width: 50px;
        height: 50px;
        padding: 13px 18px;
        border-radius: 60px;
        font-size: 15px;
        text-align: center;
    }

    .comment-section {
        list-style: none;
        max-width: 100%;
        width: 100%;
        margin: 0px auto;
        padding: 0px;
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