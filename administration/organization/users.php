<?php
// (A) ACCESS CHECK
require "../../protect.php";

if ($row['can_be_super_user'] != 1 && $row['can_view_user'] != 1) {
    header("Location: welcome");
}
?>
<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>User Management | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="smart application" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg">
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
                                <h4 class="mb-sm-0 font-size-18">USERS & ROLES</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Administration</a> ></li>
                                        <li class="breadcrumb-item active">User Management</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="checkout-tabs">
                            <div class="row">
                                <div class="col-xl-2 col-sm-3">
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link active" id="v-pills-users-tab" data-bs-toggle="pill" href="#v-pills-users" role="tab" aria-controls="v-pills-users" aria-selected="true">
                                            <i class= "bx bx-user d-block check-nav-icon mt-1 mb-1"></i>
                                            <p class="fw-bold mb-1">Users</p>
                                        </a>
                                        <a class="nav-link" id="v-pills-roles-tab" data-bs-toggle="pill" href="#v-pills-roles" role="tab" aria-controls="v-pills-roles" aria-selected="false"> 
                                            <i class= "fas fa-users-cog d-block check-nav-icon mt-1 mb-2"></i>
                                            <p class="fw-bold mb-1">Roles & Permissions</p>
                                        </a>
                                        <a class="nav-link" id="v-pills-approvals-tab" data-bs-toggle="pill" href="#v-pills-approvals" role="tab" aria-controls="v-pills-approvals" aria-selected="false">
                                            <i class= "fas fa-users-cog d-block check-nav-icon mt-1 mb-1"></i>
                                            <p class="fw-bold mb-1">Approvals</p>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-xl-10 col-sm-9">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="tab-content" id="v-pills-tabContent">
                                                <div class="tab-pane fade show active" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab">
                                                    <div>
                                                        <!-- Nav tabs -->
                                                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" data-bs-toggle="tab" href="#usersTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                                    <span class="d-none d-sm-block">Users</span> 
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#failedLoginTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                                    <span class="d-none d-sm-block">Failed Logins</span> 
                                                                </a>
                                                            </li>
                                                        </ul>
                        
                                                        <!-- Tab panes -->
                                                        <div class="tab-content p-3 text-muted">
                                                            <div class="tab-pane active" id="usersTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                    <div class="row mb-2">
                                                                        <div class="col-sm-4">
                                                                            <div class="text-sm">
                                                                                <button type="button" id="add_button"
                                                                                    class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                    data-bs-toggle="modal" data-bs-target=".addUserModal"><i
                                                                                        class="mdi mdi-plus me-1"></i> New User</button>
                                                                            </div>
                                                                        </div><!-- end col-->
                                                                    </div>

                                                                        <table id="user_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th width="15%">Names</th>
                                                                                    <th>Country</th>
                                                                                    <th>Main Function</th>
                                                                                    <th>Department</th>
                                                                                    <th>Unit</th>
                                                                                    <th>Section</th>
                                                                                    <th>Status</th>
                                                                                    <th>Registered At</th>
                                                                                    <th>Actions</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="failedLoginTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">

                                                                        <table id="failed_login_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>User</th>
                                                                                    <th>Username</th>
                                                                                    <th>Failed_At</th>
                                                                                    <th>Message</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                    
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="v-pills-roles" role="tabpanel" aria-labelledby="v-pills-roles-tab">
                                                    <div>
                                                        <!-- Nav tabs -->
                                                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" data-bs-toggle="tab" href="#rolesTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                                    <span class="d-none d-sm-block">Roles</span> 
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#permissionsTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                                    <span class="d-none d-sm-block">Permissions</span> 
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#modulesTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                                    <span class="d-none d-sm-block">Modules</span>   
                                                                </a>
                                                            </li>
                                                        </ul>
                        
                                                        <!-- Tab panes -->
                                                        <div class="tab-content p-3 text-muted">
                                                            <div class="tab-pane active" id="rolesTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row mb-2">
                                                                            <div class="col-sm-4">
                                                                                <div class="text-sm">
                                                                                    <button type="button" id="add_role_button"
                                                                                        class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                        data-bs-toggle="modal" data-bs-target=".addRoleModal"><i
                                                                                            class="mdi mdi-plus me-1"></i> New Role</button>
                                                                                </div>
                                                                            </div><!-- end col-->
                                                                        </div>

                                                                        <table id="roles_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Role Name</th>
                                                                                    <th>Description</th>
                                                                                    <th>Status</th>
                                                                                    <th>Permissions</th>
                                                                                    <th>Edit</th>
                                                                                    <th>Delete</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="permissionsTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row mb-2">
                                                                            <div class="col-sm-4">
                                                                                <div class="text-sm">
                                                                                    <button type="button" id="add_permission_button"
                                                                                        class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                        data-bs-toggle="modal" data-bs-target=".addPermissionModal"><i
                                                                                            class="mdi mdi-plus me-1"></i> New Permission</button>
                                                                                </div>
                                                                            </div><!-- end col-->
                                                                        </div>

                                                                        <table id="permissions_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Permission Name</th>
                                                                                    <th>Edit</th>
                                                                                    <th>Delete</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="modulesTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row mb-2">
                                                                            <div class="col-sm-4">
                                                                                <div class="text-sm">
                                                                                    <button type="button" id="add_module_button"
                                                                                        class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                        data-bs-toggle="modal" data-bs-target=".addModuleModal"><i
                                                                                            class="mdi mdi-plus me-1"></i> New Module</button>
                                                                                </div>
                                                                            </div><!-- end col-->
                                                                        </div>

                                                                        <table id="module_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Module Name</th>
                                                                                    <th>Permissions</th>
                                                                                    <th>Edit</th>
                                                                                    <th>Delete</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                    
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="v-pills-approvals" role="tabpanel" aria-labelledby="v-pills-approvals-tab">
                                                    <div>
                                                        <!-- Nav tabs -->
                                                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" data-bs-toggle="tab" href="#approvalsTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                                    <span class="d-none d-sm-block">Approvals</span> 
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#approvalLevelsTab" role="tab">
                                                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                                    <span class="d-none d-sm-block">Approval Hierarchy</span> 
                                                                </a>
                                                            </li>
                                                        </ul>
                        
                                                        <!-- Tab panes -->
                                                        <div class="tab-content p-3 text-muted">
                                                            <div class="tab-pane active" id="approvalsTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row mb-2">
                                                                            <div class="col-sm-4">
                                                                                <div class="text-sm">
                                                                                    <button type="button" id="add_approval_button"
                                                                                        class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                        data-bs-toggle="modal" data-bs-target=".addApprovalModal"><i
                                                                                            class="mdi mdi-plus me-1"></i> New Approval</button>
                                                                                </div>
                                                                            </div><!-- end col-->
                                                                        </div>

                                                                        <table id="approvals_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Approval Name</th>
                                                                                    <th>Approval Level</th>
                                                                                    <th>Edit</th>
                                                                                    <th>Delete</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="approvalLevelsTab" role="tabpanel">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row mb-2">
                                                                            <div class="col-sm-4">
                                                                                <div class="text-sm">
                                                                                    <button type="button" id="add_hierarchy_button"
                                                                                        class="btn btn-primary waves-effect waves-light mb-2 me-2"
                                                                                        data-bs-toggle="modal" data-bs-target=".approvalHierarchyModal"><i
                                                                                            class="mdi mdi-plus me-1"></i> New Hierarchy</button>
                                                                                </div>
                                                                            </div><!-- end col-->
                                                                        </div>

                                                                        <table id="approval_hierarchy_data" class="table table-sm table-hover dt-responsive w-100 ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Staff Name</th>
                                                                                    <th>Staff Country</th>
                                                                                    <th>Approval Name</th>
                                                                                    <th>Supervisor</th>
                                                                                    <th>Edit</th>
                                                                                    <th>Delete</th>
                                                                                </tr>
                                                                            </thead>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
                    </div>

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <!-- User Modal -->
            <div class="modal fade addUserModal modals" id="addUserModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="post" id="user_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name" name="name">
                                                <div class="invalid-feedback">
                                                    Please provide a valid name
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="surname" class="form-label">Surname</label>
                                                <input type="text" class="form-control" id="surname" name="surname">
                                                <div class="invalid-feedback">
                                                    Please provide a valid surname
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="staffNumber" class="form-label">Staff Number</label>
                                                <input type="number" class="form-control" id="staffNumber"
                                                    name="staffNumber">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="country" class="form-label">Country</label>
                                                <select class="form-select select2" id="country" name="country">
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="department" class="form-label">Department</label>
                                                <select class="form-select select2" id="department" name="department">
                                                    <option value="">...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="alert alert-success " role="alert" id="successAlert"
                                            style="display:none;">
                                            <i class="mdi mdi-check-all me-2"></i>
                                            <strong>Success: </strong> <span id="successMsg"></span>
                                        </div>
                                        <div class="alert alert-danger" role="alert" id="errorAlert"
                                            style="display:none;">
                                            <i class="mdi mdi-block-helper me-2"></i>
                                            <strong>Error: </strong> <span id="errorMsg"></span>
                                        </div>
                                    </div>


                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id2" />
                                <input type="hidden" name="operation" id="operation" />
                                <input type="submit" name="action" id="action" class="btn btn-primary"
                                    value="Register" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            <table class="table table-responsive table-condensed table-striped">
                                <tbody>
                                    <tr>
                                        <td>Names</td>
                                        <td><b><span id="view_names"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Staff Number</td>
                                        <td><b><span id="view_staffNumber"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Country</td>
                                        <td><b><span id="view_country"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Department</td>
                                        <td><b><span id="view_department"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td><b><span id="view_email"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Username</td>
                                        <td><b><span id="view_username"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Last Deactivation At</td>
                                        <td><b><span id="view_deactivated_at"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Deactivated By</td>
                                        <td><b><span id="view_deactivated_by"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Last Forgot Password At</td>
                                        <td><b><span id="view_forgot_password"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Registration Date</td>
                                        <td><b><span id="view_register_at"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Registered By</td>
                                        <td><b><span id="view_registered_by"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Last Update</td>
                                        <td><b><span id="view_updated_by"></span></b></td>
                                    </tr>
                                    <tr>
                                        <td>Updated By</td>
                                        <td><b><span id="view_updated_at"></span></b></td>
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

            <!-- Role Modal -->
            <div class="modal fade addRoleModal" id="addRoleModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="role_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="role_name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="role_name" name="role_name" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="role_description" class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" rows="3" name="role_description" id="role_description"></textarea>

                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <div class="form-check form-switch form-switch-lg mb-3" dir="ltr">
                                                <label class="form-check-label" for="role_status">Disabled</label>
                                                <input class="form-check-input" type="checkbox" id="role_status" name="role_status"  value="active">
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader3"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id3" />
                                <input type="hidden" name="operation" id="operation3" />
                                <input type="submit" name="action" id="action3" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end modal -->

            <!-- Role Permissions Modal -->
            <div class="modal fade rolePermissionModal modals1" id="rolePermissionModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <form method="post" id="role_permissions_form"  class="role_permissions_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">
                                    Role Permissions
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="role_name_pemi" class="form-label">Role Name</label>
                                            <input type="text" class="form-control" id="role_name_pemi" name="role_name_pemi"
                                                disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div id="permissions_checkbox_list"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader6"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id6" />
                                <input type="hidden" name="operation" id="operation6" />
                                <input type="submit" name="action" id="action6" class="btn btn-primary" value="Save Changes" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- end Role Permissions Modal-->

            <!-- Permission Modal -->
            <div class="modal fade addPermissionModal" id="addPermissionModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="permission_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="permission_name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="permission_name" name="permission_name" autocomplete="off">
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader4"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id4" />
                                <input type="hidden" name="operation" id="operation4" />
                                <input type="submit" name="action" id="action4" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end modal -->

            <!-- Module Modal -->
            <div class="modal fade addModuleModal" id="addModuleModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="module_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="module_name" class="form-label">Module Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="module_name" name="module_name" autocomplete="off">
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader5"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id5" />
                                <input type="hidden" name="operation" id="operation5" />
                                <input type="submit" name="action" id="action5" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end modal -->

            <!-- Module Permissions Modal -->
            <div class="modal fade modullePermissionModal modals2" id="modullePermissionModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="post" id="module_permissions_form" class="module_permissions_form" enctype="multipart/form-data">
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
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="module_name_pemi" class="form-label">Module Name</label>
                                            <input type="text" class="form-control" id="module_name_pemi" name="module_name_pemi" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="module_permissions" class="form-label">Module Permissions</label>
                                            <select class="form-select select2" multiple id="module_permissions" name="module_permissions[]">
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader7"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id7" />
                                <input type="hidden" name="operation" id="operation7" />
                                <input type="submit" name="action" id="action7" class="btn btn-primary"
                                    value="Save Changes" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end Module Permissions Modal-->

            <!-- Approvals Modal -->
            <div class="modal fade addApprovalModal" id="addApprovalModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="approval_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="approval_name" class="form-label">Approval Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="approval_name" name="approval_name" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="approval_level" class="form-label">Approval Level <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="approval_level" name="approval_level" autocomplete="off">
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader8"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id8" />
                                <input type="hidden" name="operation" id="operation8" />
                                <input type="submit" name="action" id="action8" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end Approvals modal -->

            <!-- Approval Levels Modal -->
            <div class="modal fade approvalHierarchyModal modals4" id="approvalHierarchyModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" id="approval_hierarchy_form" enctype="multipart/form-data">
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
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="h_user_id" class="form-label">Staff Name</label>
                                            <select class="form-select select2" id="h_user_id" name="h_user_id">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="h_manager_id" class="form-label">Supervisor Name</label>
                                            <select class="form-select select2" id="h_manager_id" name="h_manager_id">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="h_approval_id" class="form-label">Approval Name</label>
                                            <select class="form-select select2" id="h_approval_id" name="h_approval_id">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader9"
                                    style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id9" />
                                <input type="hidden" name="operation" id="operation9" />
                                <input type="submit" name="action" id="action9" class="btn btn-primary"
                                    value="Submit" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end Approval Levels Modal-->

            <div class="modal fade privilegeModal" id="privilegeModal" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
                    <form method="post" id="privilege_form" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td width="35%">Names</td>
                                            <td><b><span id="view_privileges_names"></span></b></td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td><b><span id="view_privileges_email"></span></b></td>
                                        </tr>
                                        <tr>
                                            <td>Check / Uncheck All Permissions</td>
                                            <td>
                                                <div class="checkbox">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"><b> Check
                                                                All</b></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                                <div class="row">

                                    <div class="col-sm-12 col-md-3">
                                        <div class="card text-dark bg-light mb-3">
                                            <h5 class="card-header">PettyCash</h5>
                                            <div class="card-body">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_add_cash_requests" name="can_add_cash_requests"
                                                            value="1"> Add Request</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_cash_hod" name="can_be_cash_hod" value="1">
                                                        HOD</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_cash_coo" name="can_be_cash_coo" value="1">
                                                        COO/Country Manager</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_cash_manager" name="can_be_cash_manager"
                                                            value="1"> GMD</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_prosess_flight" name="can_prosess_flight" value="1">
                                                        Flight Processor</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_cash_finance" name="can_be_cash_finance"
                                                            value="1"> Finance</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_view_cash_reports" name="can_view_cash_reports"
                                                            value="1"> PettyCash
                                                        Reports</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12 col-md-3">
                                        <div class="card text-dark bg-light mb-3">
                                            <h5 class="card-header">Users</h5>
                                            <div class="card-body">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_add_user" name="can_add_user" value="1"> Add
                                                        User</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_view_user" name="can_view_user" value="1"> View
                                                        User</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_edit_user" name="can_edit_user" value="1"> Edit
                                                        User</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_deactivate_user" name="can_deactivate_user"
                                                            value="1"> deactivate User</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_delete_user" name="can_delete_user" value="1">
                                                        Delete User</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_give_privileges" name="can_give_privileges"
                                                            value="1"> Give Permissions</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_see_settings" name="can_see_settings" value="1">
                                                        View Settings</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_reset_user_password" name="can_reset_user_password"
                                                            value="1"> Reset Password</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_update_notifications"
                                                            name="can_update_notifications" value="1">
                                                        Notifications</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-3">
                                        <div class="card text-dark bg-light mb-3">
                                            <h5 class="card-header">Leadership</h5>
                                            <div class="card-body">                                                
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_gmd" name="can_be_gmd" value="1"> GMD</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_coo" name="can_be_coo" value="1"> COO</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> CFO</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> EXCO</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> HRD</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_country_manager" name="can_be_country_manager" value="1"> Country Manager</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> HOD</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> Sub-Department Leader</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox" class="form-check-input"
                                                            id="can_be_exco" name="can_be_exco" value="1"> Unit Leader</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="spinner-border text-primary m-1" role="status" id="loader2"
                                    style="display:none;">
                                    <span class="sr-only">Loading...</span>
                                </div>

                                <input type="hidden" name="id" id="id3" />
                                <input type="hidden" name="operation" id="operation3" />
                                <input type="submit" name="action" id="action3" class="btn btn-primary"
                                    value="Save Changes" />
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script src="administration/users/js/users.js"></script>
</body>

</html>