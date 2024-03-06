<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Pettycash.php';   

        // Increase PHP execution time
        set_time_limit(0);
        
        require_once("../../../vendor/autoload.php");        
        use ArtisansWeb\Optimizer;

        //Instantiate SB and Connect
        $database = new Database();

        if (!isset($_COOKIE["jwt"])) { 
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }

        $db = $database->connect();
        //Instantiate user object
        $user = new User($db);
        $pettycash= new Pettycash($db);

        //Check jwt validation
        $user_details = $user->validate($_COOKIE["jwt"]);
        if($user_details===false) {
            setcookie("jwt", null, -1);
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 

        if ($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $country_details = $user->get_country($user_details['country']);
        $settings_salis_details= $user->get_settings_salis($user_details['country']);

        $this_user_id = $user_details['userId'];
        $reply_email_to = $user_details['email'];
        $reply_name = $user_details['name'];
        $reply_surname = $user_details['surname']; 

        $mycountry = $user_details['country'];
        $my_department = $user_details["department_val"];

        $is_on_budget = $settings_salis_details['is_on_budget'];
        

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        $errorMsg = ''; 

        $pettycash->country = $mycountry; 
        $pettycash->department = $my_department; 

        if (empty($_POST["visitDate"]) || $_POST["visitDate"] == "") {  
            $errorMsg = "Date is required";  
        } else {  
            $pettycash->visitDate = clean_data($_POST["visitDate"]);                 
        }

        if (empty($_POST["category"]) || $_POST["category"] =='') {  
            $errorMsg = 'Category is required';  
        } else { 
            $pettycash->category = clean_data($_POST["category"]); 

            if($rowC = $pettycash->get_cash_categories($_POST["category"])){
                $category_id = $rowC['id'];

                if($is_on_budget == 1) {
                    if($rowC['budget_category'] != NULL) {
                        
                        $pettycash->budget_category = $rowC['budget_category'];

                        $budget_cat_id = $rowC['budget_category'];
                        $checkTotalAmount = $_POST["amount"];	

                        if(isset($budget_cat_id)){
                            $rowBC = $pettycash->get_depart_cat_budget($mycountry,$my_department,$budget_cat_id);
                            if($rowBC) {
                                $remain = $rowBC['remaining_amount'];
                                if($remain < $checkTotalAmount) {
                                    $errorMsg = 'There is not enough budget based on the category selected.';
                                }
                            } else {
                                $errorMsg = 'There is no budget set based on the category selected.';
                            }
                        } else {
                            $errorMsg = 'The category selected is not mapped to the budget'; 
                        }							

                    } else {
                        $errorMsg = 'The category you selected is not mapped to the budget.';
                    }
                } else {
                    $pettycash->budget_category = NULL;
                }
            }
        }

        $pettycash->providers = NULL;
        $providers = NULL;
        if ( (isset($_POST["category"]) && $_POST["category"] == "Visit to providers") && ( $_POST['providers'] == "")) {  
            $errorMsg = 'Providers are required';  
        } elseif( (isset($_POST["category"]) && $_POST["category"] == "Visit to providers") && (isset($_POST['providers']) && ($_POST['providers'] != ""))) { 
            foreach ($_POST['providers'] as $provider) {
                $providers.= $provider.", ";
            }
            $providers = rtrim($providers,', ');
            $pettycash->providers = clean_data($providers); 
        }

        $pettycash->customers = NULL;
        $customers = NULL;
        if ( (isset($_POST["category"]) && $_POST["category"] == "Customer visit") && ( $_POST['customers'] == "")) {  
            $errorMsg = 'Providers are required';  
        } elseif( (isset($_POST["category"]) && $_POST["category"] == "Customer visit") && (isset($_POST['customers']) && ($_POST['customers'] != ""))) { 
            foreach ($_POST['customers'] as $customer) {
                $customers.= $customer.", ";
            }
            $customers = rtrim($customers,', ');
            $pettycash->customers = clean_data($customers); 
        }

        $pettycash->air_departure_date = $pettycash->air_return_date = NULL;
        if ( (isset($_POST["category"]) && ($_POST["category"] == "Transport")) && ( $_POST['departure_date'] == "" || $_POST['return_date'] == "")) {  
            $errorMsg = 'Departure Date and Return Date are required';  
        } elseif( (isset($_POST["category"]) && ($_POST["category"] == "Transport")) && (isset($_POST['departure_date']) && ($_POST['departure_date'] != "") && isset($_POST['return_date']) && ($_POST['return_date'] != ""))) { 
            $pettycash->air_departure_date = clean_data($_POST["departure_date"]); 
            $pettycash->air_return_date = clean_data($_POST["return_date"]); 
        }

        $pettycash->checkin_date = $pettycash->checkout_date = NULL;
        if ( (isset($_POST["category"]) && ($_POST["category"] == "Accomodation")) && ( $_POST['checkin_date'] == "" || $_POST['checkout_date'] == "")) {  
            $errorMsg = 'Checkin Date and Checkout Date are required';  
        } elseif( (isset($_POST["category"]) && ($_POST["category"] == "Accomodation")) && (isset($_POST['checkin_date']) && ($_POST['checkin_date'] != "") && isset($_POST['checkout_date']) && ($_POST['checkout_date'] != ""))) { 
            $pettycash->checkin_date = clean_data($_POST["checkin_date"]); 
            $pettycash->checkout_date = clean_data($_POST["checkout_date"]); 
        }

        if (empty($_POST["description"]) || $_POST["description"] == "") {  
            $errorMsg = "Description is required";  
        } else {  
            $pettycash->description = clean_data($_POST["description"]);                 
        }

        $pettycash->phone = NULL;
        if (isset($_POST["phone"]) && $_POST["phone"] != "") {  
            $pettycash->phone = clean_data($_POST["phone"]); 
        }

        if(is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
            $filename = $_FILES["additional_doc"]["name"];
            $filetype = $_FILES["additional_doc"]["type"];
            $filesize = $_FILES["additional_doc"]["size"];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            $allowed = array(
                "pdf" => array( "application/pdf" ),
                "doc" => array( "application/msword" ),
                "docx" => array( "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ),
                "xls" => array( "application/vnd.ms-excel" ),
                "xlsx" => array(
                    "application/vnd.ms-excel",
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                )
            );
            if ( !isset( $allowed[$ext] ) || !in_array( $filetype, $allowed[$ext] ) ) {
                $errorMsg = 'Only PDF, Excel and Word documents are allowed';
            }
            elseif($_FILES['additional_doc']['size'] >= 2097152) {
                $errorMsg = 'File too large. File must be less than 2MB';
            }
        }

        $pettycash->transport = $_POST["transport"] !='' ? clean_data($_POST["transport"]) : 0;
        $pettycash->accomodation = $_POST["accomodation"] !='' ? clean_data($_POST["accomodation"]) : 0;
        $pettycash->meals = $_POST["meals"] !='' ? clean_data($_POST["meals"]) : 0;
        $pettycash->otherExpenses = $_POST["other"] !='' ? clean_data($_POST["other"]) : 0;
        $pettycash->requester_charges = $_POST["requester_charges"] !='' ? clean_data($_POST["requester_charges"]) : 0;
        $totalAmount = $_POST["amount"] !='' ? clean_data($_POST["amount"]) : 0;

        $pettycash->totalAmount = $totalAmount;
        if($totalAmount < 1){
            $errorMsg = 'Amount is required'; 
        }

        //AUTO APPROVE CODES
        $cashFinancesLimit = $settings_salis_details['pettycash_finance_limit'];
        $cashCOOLimit = $settings_salis_details['pettycash_coo_limit'];

        $checkTotalAmount = $_POST["amount"];

        $status = "@HOD";
        $msg = " and it needs your approval as a HOD";
        
        if(($user_details['can_be_cash_hod'] == 1) || ($user_details['can_be_cash_coo'] == 1) || ($user_details['can_be_cash_manager'] == 1)) {
            
            if($user_details['can_be_cash_hod'] == 1) {

                $status = "@FinanceFromHOD";
                $msg = " and it is ready for Finance to disburse";
                if( $checkTotalAmount > $cashFinancesLimit){
                    $status = "@COO";
                    $msg = ' and it is sent to the COO/Country Manager for further approval';
                }
                
            }
            if($user_details['can_be_cash_coo'] == 1) {

                $status = "@FinanceFromCOO";
                $msg = " and it is ready for Finance to disburse";
                if( $checkTotalAmount > $cashCOOLimit){ 
                    $status = "@GMDfromCOO";
                    $msg = " and it needs your approval";
                }

            }
            if($user_details['can_be_cash_manager'] == 1) {
                $status = "@FinanceFromGMD";
                $msg = " and it is ready for Finance to disburse";
            }
        }

        
        $pettycash->status = $status;

        $pettycash->this_user = $this_user_id;

        if($_POST["operation"] == "Add"){              

            if($errorMsg == '') {

                $pettycash->additional_doc = NULL;
                if(is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
                    $pettycash->additional_doc = $pettycash->upload_petty_doc();
                }

                // create project
                $response = $pettycash->create();
                if($response[0]) { 
                    
                    $query = "";
                    if($status =="@HOD") {
                        $query = "AND users.department =:dpt AND users.country =:ctr AND privileges.can_be_cash_hod = 1";
                    }
                    if($status =="@FinanceFromHOD" || $status =="@FinanceFromCOO" || $status =="@FinanceFromGMD") {
                        $query = "AND users.country =:ctr AND privileges.can_be_cash_finance = 1";
                    }
                    if($status =="@COO") {
                        $query = "AND users.country =:ctr AND privileges.can_be_cash_coo = 1";
                    }
                    if($status =="@GMDfromCOO") {
                        $query = "AND privileges.can_be_cash_manager = 1";
                    }
                    
                    // Notification
                    $getHOD=$db->prepare("SELECT users.* FROM users 
                                            LEFT JOIN privileges ON users.userId = privileges.userId
                                            WHERE users.status = 'active' ".$query."
                                        ");
                    if($status =="@HOD") {
                        $getHOD->bindParam(':dpt', $my_department);
                        $getHOD->bindParam(':ctr', $mycountry);
                    }
                    if($status =="@FinanceFromHOD" || $status =="@FinanceFromCOO" || $status =="@FinanceFromGMD" || $status =="@COO") {
                        $getHOD->bindParam(':ctr', $mycountry);
                    }

                    $getHOD->execute();
                    $countHOD = $getHOD->rowCount();
                    
                    if($countHOD > 0){
                        while($rowHOD = $getHOD->fetch(PDO::FETCH_ASSOC))
                        {
                            $myHOD = $rowHOD["userId"];
                            $email_to = $rowHOD["email"]; 
                            $to_name = $rowHOD["name"]; 
                            $amount = $_POST["amount"].' '.$country_details['country_currency'];
                            $title = 'PETTY CASH: A new request | SERIS';
                            $ref_No = $response[1];
                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has added a Fund request ('.$ref_No.') of <b>'.$amount.'</b>'.$msg.'. 
                                    ';
                            
                            if($myHOD != ''){
                                $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                            }
                        }
                    }

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Your request is sent."
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $response
                        )
                    );
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }
        }
        elseif($_POST["operation"] == "Edit") {

            $errorMsg = ''; 

            if (empty($_POST["id"])) {  
                $errorMsg = "ID is required";  
            } else { 
                $pettycash->id = clean_data($_POST["id"]);

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            }
            
            $pettycash->this_user = $this_user_id;

            if($errorMsg == '') {

                if(is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
                    $dir = $_POST["id"];
                    $pettycash->unlink_petty_doc($dir);
                    $pettycash->additional_doc = $pettycash->upload_petty_doc();
                }
                else {
                    $pettycash->additional_doc = $_POST["hidden_doc"];
                }

                $response = $pettycash->update();
                if($response) {

                    $query = "";
                    if($status =="@HOD") {
                        $query = "AND users.department =:dpt AND users.country =:ctr AND privileges.can_be_cash_hod = 1";
                    }
                    if($status =="@FinanceFromHOD" || $status =="@FinanceFromCOO" || $status =="@FinanceFromGMD") {
                        $query = "AND users.country =:ctr AND privileges.can_be_cash_finance = 1";
                    }
                    if($status =="@COO") {
                        $query = "AND users.country =:ctr AND privileges.can_be_cash_coo = 1";
                    }
                    if($status =="@GMDfromCOO") {
                        $query = "AND privileges.can_be_cash_manager = 1";
                    }
                        
                    // Notification
                    $getHOD=$db->prepare("SELECT users.* FROM users 
                                            LEFT JOIN privileges ON users.userId = privileges.userId
                                            WHERE users.status = 'active' ".$query."
                                        ");
                    if($status =="@HOD") {
                        $getHOD->bindParam(':dpt', $my_department);
                        $getHOD->bindParam(':ctr', $mycountry);
                    }
                    if($status =="@FinanceFromHOD" || $status =="@FinanceFromCOO" || $status =="@FinanceFromGMD" || $status =="@COO") {
                        $getHOD->bindParam(':ctr', $mycountry);
                    }
                        
                    $getHOD->execute();
                    $countHOD = $getHOD->rowCount();
                    
                    if($countHOD > 0){
                        $ref_No; $amount = 0;
                        $getRefNo = $db->prepare("SELECT refNo,totalAmount FROM cashrequests WHERE id = '".$_POST["id"]."' ");
                        $getRefNo->execute();
                        if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
                            $ref_No = $rowRef['refNo'];
                            $amount = $rowRef['totalAmount'];
                        }

                        while($rowHOD = $getHOD->fetch(PDO::FETCH_ASSOC))
                        {
                            $myHOD = $rowHOD["userId"];
                            $email_to = $rowHOD["email"]; 
                            $to_name = $rowHOD["name"]; 
                            $amount = $amount.' '.$country_details['country_currency'];
                            $title = 'PETTY CASH: An updated request | SERIS';
                            $ref_No = $ref_No;
                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has added a Fund request ('.$ref_No.') of <b>'.$amount.'</b>'.$msg.'. 
                                    ';

                            if($myHOD != ''){
                                $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                            }
                        }
                    }

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Updated"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $response
                        )
                    );
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }
        } 
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        