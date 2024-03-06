<?php
    // (A) ACCESS CHECK
    require "../protect.php";
    
    if( $row['can_be_super_user'] != 1 && $row['can_be_admin'] != 1 ) {
        header("Location: welcome");
    }
?>

<!doctype html>
<html lang="en">
<head>
        
        <meta charset="utf-8" />
        <title>Groupings | UPCE Valuation System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="UPCE valuation system" name="description" />
        <meta content="Jean Luc Niyigena" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/icon.png">
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
				width:100%!important;
			}
			.select2-container .select2-selection--single{
				height:34px !important;
			}
			.select2-container--default .select2-selection--single{
				border: 1px solid #ccc !important; 
				border-radius: 0px !important; 
			}
		</style>
    </head>

    <body data-sidebar="dark" data-layout-mode="light">

    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">
        
            <!-- ========== Header ========== -->
            <?php include '../include/header.php'; ?>

            <!-- ========== Left Sidebar Start ========== -->
            <?php include '../include/sidebar.php'; ?>
            <!-- Left Sidebar End -->

            

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
                                    <h4 class="mb-sm-0 font-size-18">Groupings</h4>

                                    

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
        
                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <div class="text-sm">
                                                    <button type="button" id="add_button" class="btn btn-primary waves-effect waves-light mb-2 me-2" data-bs-toggle="modal" data-bs-target=".addGroupingModal"><i class="mdi mdi-plus me-1"></i> New Grouping</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                                        
                                        <table id="grouping_data" class="table table-hover dt-responsive w-100 ">
                                            <thead>
                                                <tr>
                                                    <th>Grouping Name</th>
                                                    <th>Valuation Method</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                        </table>
        
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <!-- Transaction Modal -->
                <div class="modal fade addGroupingModal modals" id="addGroupingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="" aria-hidden="true">
                    <div class="modal-dialog" >
                        <form method="post" id="grouping_form" autocomplete="off" enctype="multipart/form-data">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="transaction-detailModalLabel">Modal Title</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="needs-validation" novalidate>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="group_name" class="form-label">Grouping Name</label>
                                                    <input type="text" class="form-control" id="group_name" name="group_name"  >
                                                    <div class="invalid-feedback">
                                                        Please provide a valid name
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3 mt-2">
                                                    <label for="valuation_method" class="form-label">Valuation Method</label>
                                                    <select class="form-select" id="valuation_method" name="valuation_method" >
                                                        <option value="">...</option>
                                                        <option value="Detailed">Detailed (Détaillé)</option>
                                                        <option value="Areal">Areal (Surfacique)</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select a valid method
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="spinner-border text-primary m-1" role="status" id="loader" style="display:none;">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="mt-3">                                            
                                            <input type="hidden" name="id" id="id2" />
                                            <input type="hidden" name="operation" id="operation" />
                                            <input type="submit" name="action" id="action" class="btn btn-primary" value="Register" />                                            
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- end modal -->

                <div class="modal fade viewModal" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                    <div class="modal-dialog modal-lg" >
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-7">
                                            <h5 class="text-primary">PROJECT DETAILS</h5>
                                            <table class="table table-responsive table-sm table-condensed table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="40%">Project Name</td>
                                                        <th><span id="view_project_name"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Project Manager</td>
                                                        <th><span id="view_project_manager"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Start Date</td>
                                                        <th><span id="view_start_date"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>End Date</td>
                                                        <th><span id="view_end_date"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Estimated Number of Properties</td>
                                                        <th><span id="view_estimated_number"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Valuation Method</td>
                                                        <th><span id="view_valuation_method"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Description</td>
                                                        <th><span id="view_description"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td>
                                                        <th><span id="view_status"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Created By</td>
                                                        <th><span id="view_registered_by"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Created At</td>
                                                        <th><span id="view_register_at"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Updated At</td>
                                                        <th><span id="view_updated_at"></span></th>
                                                    </tr>
                                                    <tr>
                                                        <td>Updated By</td>
                                                        <th><span id="view_updated_by"></span></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-12 col-md-5">
                                            <h5 class="text-primary">TEAM DETAILS</h5>
                                            <table class="table table-responsive table-sm table-condensed ">
                                                <tbody>
                                                    <tr class="bg-primary text-white">
                                                        <th>Team Leader</th>
                                                    </tr>
                                                    <tr>
                                                        <td><span id="view_team_leader"></span></td>
                                                    </tr>
                                                    <tr class="bg-primary text-white">
                                                        <th>Team Members</th>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <ol style="padding-left: 10px;" id="view_team_members">
                                                            </ol>
                                                            
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="privilegeModal" class="modal fade">
                        <div class="modal-dialog modal-dialog" >
                            <form method="post" id="privilege_form" enctype="multipart/form-data">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                    
                                    <div class="container-fluid">		
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td width="35%">Names</td>
                                                    <td><span id="view_privileges_names"></span></td>
                                                </tr>
                                                <tr>
                                                    <td>Email</td>
                                                    <td><span id="view_privileges_email"></span></td>
                                                </tr>
                                                <tr>
                                                    <td>Check / Uncheck All Permissions</td>
                                                    <td>
                                                        <div class="checkbox">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                                                <label class="form-check-label" for="checkAll"> Check All</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            
                                            </tbody>
                                        </table>
                                    
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6">

                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_be_admin" name="can_be_admin" value="1"> Admin</label>
                                                </div>
                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_add_user" name="can_add_user" value="1"> Add User</label>
                                                </div>
                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_view_user" name="can_view_user" value="1"> View User</label>
                                                </div>
                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_delete_user" name="can_delete_user" value="1" > Delete User</label>
                                                </div>
                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_give_privileges" name="can_give_privileges" value="1" > Give Permissions</label>
                                                </div>
                                                <div class="checkbox">
                                                <label><input type="checkbox" id="can_reset_user_password" name="can_reset_user_password" value="1" > Reset Password</label>
                                                </div>
                                            </div>                                        
                                            <div class="col-sm-12 col-md-6">
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-12">
                                                        <div class="checkbox">
                                                        <label><input type="checkbox" id="can_see_settings" name="can_see_settings" value="1" > System Settings</label>
                                                        </div>
                                                        <div class="checkbox">
                                                        <label><input type="checkbox" id="can_be_project_manager" name="can_be_project_manager" value="1" > Project Manager</label>
                                                        </div>
                                                        <div class="checkbox">
                                                        <label><input type="checkbox" id="can_activate_project" name="can_activate_project" value="1" > Activate Project</label>
                                                        </div>
                                                        <div class="checkbox">
                                                        <label><input type="checkbox" id="can_view_reports" name="can_view_reports" value="1" > Access To Reports</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="spinner-border text-primary m-1" role="status" id="loader2" style="display:none;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="mt-3">                                            
                                        <input type="hidden" name="id" id="id3" />
                                        <input type="hidden" name="operation" id="operation3" />
                                        <input type="submit" name="action" id="action3" class="btn btn-primary" value="Save Changes" />                                            
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
               

			


                <?php include '../include/footer.php'; ?>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <!-- Right Sidebar -->
        <?php include '../include/rightside.php'; ?>

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
        <script>
            $('.select2').select2({
                dropdownParent: $('.modals')
                
            });
        </script>
        
        <!-- bootstrap datepicker -->
        <script src="assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

        <script src="assets/js/app.js"></script>
        <script src="settings/js/groupings.js"></script>

        


    </body>


</html>