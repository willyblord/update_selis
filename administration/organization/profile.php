<?php
    // (A) ACCESS CHECK
    require "../../protect.php";
    
    if( $row['can_be_super_user'] != 1) {
        header("Location: welcome");
    }
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Change Password | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg">
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

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
                                    <h4 class="mb-sm-0 font-size-18">PASSWORD SETTINGS</h4>

                                    

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    <form method="post" id="changForm" autocomplete="off" enctype="multipart/form-data">
                                    <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="oldpassword" class="form-label">Old Password</label>
                                                    <input type="password" class="form-control" id="oldpassword" name="oldpassword">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">New Password</label>
                                                    <input type="password" class="form-control" id="password" name="password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="rePassword" class="form-label">New Password Confirmation</label>
                                                    <input type="password" class="form-control" id="rePassword" name="rePassword">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="spinner-border text-primary m-1" role="status" id="loader"
                                            style="display:none;">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="mt-3">
                                            <input type="hidden" name="id" id="id2" />
                                            <input type="hidden" name="operation" id="operation" />
                                            <input type="submit" name="action" id="action" class="btn btn-primary"
                                                value="Change Password" />
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

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

    <script src="assets/js/app.js"></script>
    <script src="administration/users/js/changepw.js"></script>
</body>

</html>