                                
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Smart Applications International Ltd.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Powered by Smart
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Modal -->
                <div class="modal fade expiresModal" id="expiresModal"  data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                    <div class="modal-dialog modal-dialog" >
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Your Session is About to Expire!</h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p>Your session is about to expire.</p>
                                        <p>Redirecting in <span id="count_sec"></span> seconds.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form method="post" id="logout_form" enctype="multipart/form-data">
                                    <input type="hidden" name="logout" id="logout" value="1">
                                    <input type="submit" name="action" id="action" class="btn btn-secondary" value="Logout" /> 
                                </form>                                    
                                <button type="button" class="btn btn-primary" id="stay_connected" data-bs-dismiss="modal">Stay Connected</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end modal -->

                <!-- <button onclick="$('#cover-spin').show(0)">Save</button> -->
                <div id="cover-spin"></div>
                <style>
                    #cover-spin {
                        position:fixed;
                        width:100%;
                        left:0;right:0;top:0;bottom:0;
                        background-color: rgba(255,255,255,0.7);
                        z-index:9999;
                        display:none;
                    }

                    @-webkit-keyframes spin {
                        from {-webkit-transform:rotate(0deg);}
                        to {-webkit-transform:rotate(360deg);}
                    }

                    @keyframes spin {
                        from {transform:rotate(0deg);}
                        to {transform:rotate(360deg);}
                    }

                    #cover-spin::after {
                        content:'';
                        display:block;
                        position:absolute;
                        left:48%;top:40%;
                        width:40px;height:40px;
                        border-style:solid;
                        border-color:black;
                        border-top-color:transparent;
                        border-width: 4px;
                        border-radius:50%;
                        -webkit-animation: spin .8s linear infinite;
                        animation: spin .8s linear infinite;
                    }

                    .side-menu-list.active {
                        background-color: #b01c2e;
                        border-color: grey; 
                    }
                </style>
                
            </footer>
            
            <script src="assets/libs/jquery/jquery.min.js"></script>  
            <script src="administration/users/js/logout.js"></script>

            <script>
                $('#cover-spin').show()
            </script>
            <script src="administration/users/js/footer.js"></script>
            
            