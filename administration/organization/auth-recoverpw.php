<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Recover Password | SERIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="SERIS - Smart Application" name="description" />
    <meta content="Smart Applications" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/icon.jpg">

    <!-- owl.carousel css -->
    <link rel="stylesheet" href="assets/libs/owl.carousel/assets/owl.carousel.min.css">

    <link rel="stylesheet" href="assets/libs/owl.carousel/assets/owl.theme.default.min.css">

    <!-- Sweet Alert-->
    <link href="assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

</head>

<body class="auth-body-bg">

    <div>
        <div class="container-fluid p-0">
            <div class="row g-0">

                <div class="col-xl-9">
                    <div class="auth-full-bg pt-lg-5 p-4">
                        <div class="w-100">
                            <div class="bg-overlay"></div>
                            <div class="d-flex h-100 flex-column">

                                <div class="p-4 mt-auto">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-7">
                                            <div class="text-center">

                                                <h4 class="mb-3"><i class="bx bxs-quote-alt-left text-primary h1 align-middle me-3"></i>
                                                        <span class="text-primary">Who We Are</span></h4>

                                                <div dir="ltr">
                                                    <div class="owl-carousel owl-theme auth-review-carousel"
                                                        id="auth-review-carousel">
                                                        <div class="item">
                                                            <div class="py-3">
                                                                <p class="font-size-16 mb-4">                                                                    
                                                                    Smart Applications International is a leading ICT solutions provider delivering a wide range of world class technological solutions.
                                                                </p>
                                                                <p class="font-size-16 mb-4">  
                                                                    We offer solutions that are secure, relevant and convenient.
                                                                </p>

                                                                <div>
                                                                    <h4 class="font-size-16 text-primary">Smart Applications</h4>
                                                                    <!-- <p class="font-size-14 mb-0">- Skote User</p> -->
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="item">
                                                            <div class="py-3">
                                                                <p class="font-size-16 mb-4">
                                                                    Our vision:  Create a Smarter Society Through 
                                                                    World Class Innovative Technology Solutions.
                                                                </p>
                                                                <p class="font-size-16 mb-4">
                                                                    Our mission: Inspiring a world of convenience.
                                                                </p>

                                                                <div>
                                                                    <h4 class="font-size-16 text-primary">Smart Applications</h4>
                                                                    <!-- <p class="font-size-14 mb-0">- Skote User</p> -->
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
                </div>
                <!-- end col -->

                <div class="col-xl-3">
                    <div class="auth-full-page-content p-md-5 p-4">
                        <div class="w-100">

                            <div class="d-flex flex-column h-100">
                                <div class="mb-4 mb-md-5">
                                    <a href="login" class="d-block auth-logo">
                                        <img src="assets/images/smart_logo.jpg" alt="" height="30"
                                            class="auth-logo-dark">
                                        <img src="assets/images/smart_logo.jpg" alt="" height="30"
                                            class="auth-logo-light">
                                    </a>
                                </div>
                                <div class="my-auto">

                                    <div>
                                        <h5 class="text-primary">Reset Password</h5>
                                        <p class="text-muted">Reset Password with SERIS.</p>
                                    </div>

                                    <div class="mt-4">
                                        <div class="alert alert-success text-center mb-4" role="alert">
                                            Enter your Email and instructions will be sent to you!
                                        </div>
                                        <form id="recover_form" enctype="multipart/form-data">
            
                                            <div class="mb-3">
                                                <label for="country" class="form-label">Country</label>
                                                <select class="form-select" id="country" name="country">
                                                    <option value="">Select Country</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="useremail" class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
                                            </div>
                        
                                            <div class="text-end">

                                                <div class="spinner-border text-primary m-1" role="status" id="loader" style="display:none; ">
                                                    <span class="sr-only">Loading...</span>
                                                </div>

                                                <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Reset</button>
                                            </div>
        
                                        </form>
                                        <div class="mt-5 text-center">
                                            <p>Remember It ? <a href="login" class="fw-medium text-primary"> Sign In here</a> </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0">©
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script> Smart Applications
                                    </p>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container-fluid -->
    </div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>

    <!-- owl.carousel js -->
    <script src="assets/libs/owl.carousel/owl.carousel.min.js"></script>

    <!-- auth-2-carousel init -->
    <script src="assets/js/pages/auth-2-carousel.init.js"></script>

    <!-- Sweet Alerts js -->
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <script src="administration/users/js/recoverpw.js"></script>

</body>

</html>