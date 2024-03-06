<?php
	session_start();
	
	if(!isset($_SESSION['login_disclaimer_confirmation'])) 
	{
        header("location: login");
        exit(); 
    }
?>
<!doctype html>
<html lang="en">

<head>

        <meta charset="utf-8" />
        <title>Login Confirmation | SERIS</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="SERIS - Smart Application" name="description" />
        <meta content="Smart Applications" name="author" />
        <link rel="shortcut icon" href="assets/images/icon.jpg">
        
        <!-- Sweet Alert-->
        <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    <body>
        
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-danger bg-soft">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-secondary p-4">
                                            <h5 class="text-secondary">Login Disclaimer and</h5>
                                            <p>2 FACTOR AUTHENTICATION</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="assets/images/smartlogo.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0"> 
                                <div>
                                    <a href="#">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="assets/images/icon.jpg" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2">
            
                                        <div class="mb-3" style="text-align: justify; font-size:0.82em;">
                                            <p><b>SMART APPLICATIONS INTERNATIONAL LIMITED</b></p>
                                            <p>
                                                This is a private computer system. Unauthorized access or use is prohibited 
                                                and only authorized users are permitted. Use of this system constitutes 
                                                consent to monitoring at all times and user should have no expectation of 
                                                privacy. Unauthorized access or violations of security regulations is 
                                                unlawful and hence if monitoring reveals either of it, appropriate 
                                                displinary action will be taken against the employees violating security 
                                                regulations or making  unauthorized use of this system.
                                            </p>
                                            <p>
                                                If you agree, fill in the below field with the OTP sent to your registered email address
                                                and click "<span class="text-primary">I accept</span>" to continue the access.
                                                If you click "<span class="text-danger">I decline</span>", you will be logged out.
                                            </p>
                                            
                                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 justify-content-center text-center">
                                                <div class="col">
                                                    <input type="text" class="form-control" id="OTP" placeholder="OTP" maxlength="6">
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="text-center">
                                            <div class="spinner-border text-primary m-1" role="status"
                                                id="loader" style="display:none; ">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <button class="btn btn-danger w-md waves-effect waves-light" id="i_decline">I decline</button>
                                            <button class="btn btn-primary w-md waves-effect waves-light" id="i_accept">I accept</button>
                                        </div>
                                    </form>
                                </div>
            
                            </div>
                        </div>
                        <div class="mt-5 text-center">
                           <p>© <script>document.write(new Date().getFullYear())</script> Smart Applications International Ltd.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>   

        <!-- Sweet Alerts js -->
        <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
        
        <!-- App js -->
        <script src="assets/js/app.js"></script>
        <script src="administration/users/js/login_conf.js"></script>

    </body>
</html>
