<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../models/Budget.php';
        include_once '../../../administration/users/models/User.php';        

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
        $budget = new Budget($db);

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
        $this_user_id = $user_details['userId'];

        if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 && $user_details['can_be_cash_coo'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $mycountry = $user_details['country'];
        $my_department = $user_details["department_val"];

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $errorMsg = ''; 

            if (empty($data->department) || $data->department == "") {  
                $errorMsg = "Department is required";  
            } else {  
                $budget->department = clean_data($data->department); 
            } 

            if (empty($data->budget_category) || $data->budget_category == "" ) {  
                $errorMsg = "Budget Line is required";  
            } else {  
                $budget->budget_category = clean_data($data->budget_category); 

                $budget_category = $data->budget_category;
                $start_date = $data->start_date;
                $end_date = $data->end_date;

                if($budget->is_same_budget_exists($mycountry,$my_department,$budget_category,$start_date,$end_date)) {
                    $errorMsg = "This same category has been added in this date range selected.";  
                }
            }

            if (empty($data->start_date) || $data->start_date =="") {  
                $errorMsg = "Start Date is required";  
            } else {  
                $budget->start_date = $data->start_date; 
            } 

            if (empty($data->end_date) || $data->end_date == "") {  
                $errorMsg = "End Date is required";  
            } else {  
                $budget->end_date = clean_data($data->end_date); 
            } 

            if ( ($data->start_date > $data->end_date) || ($data->start_date == $data->end_date)) {  
                $errorMsg = 'End Date must be greater than Start Date';  
            }

            if (empty($data->initial_amount) || $data->initial_amount == ""  || $data->initial_amount < 1) {  
                $errorMsg = "Initial Amount is required";  
            } else {  
                $budget->initial_amount = clean_data($data->initial_amount); 
            } 

            $budget->this_user = $this_user_id;
            $budget->country = $mycountry;

            $budget->status = "pending"; 

            if($errorMsg == '') {
                //create user
                $response = $budget->create();
                if($response) {
                    
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Budget Added"
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
        elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {

            $errorMsg = ''; 

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                $budget->id = clean_data($data->id);

                if(($budget->is_budget_active())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Active Budget Cannot be Updated'
                    ]);
                    die();
                }  
            } 

            if (empty($data->department) || $data->department == "") {  
                $errorMsg = "Department is required";  
            } else {  
                $budget->department = clean_data($data->department); 
            } 

            if (empty($data->budget_category) || $data->budget_category == "" ) {  
                $errorMsg = "Budget Line is required";  
            } else {  
                $budget->budget_category = clean_data($data->budget_category); 

                $budget_category = $data->budget_category;
                $start_date = $data->start_date;
                $end_date = $data->end_date;
                $and = ' AND id <> "'.$data->id.'" ';

                if($budget->is_same_budget_exists($mycountry,$my_department,$budget_category,$start_date,$end_date,$and)) {
                    $errorMsg = "This same category has been added in this date range selected.";  
                }
            }

            if (empty($data->start_date) || $data->start_date =="") {  
                $errorMsg = "Start Date is required";  
            } else {  
                $budget->start_date = $data->start_date; 
            } 

            if (empty($data->end_date) || $data->end_date == "") {  
                $errorMsg = "End Date is required";  
            } else {  
                $budget->end_date = clean_data($data->end_date); 
            } 

            if ( ($data->start_date > $data->end_date) || ($data->start_date == $data->end_date)) {  
                $errorMsg = 'End Date must be greater than Start Date';  
            }

            if (empty($data->initial_amount) || $data->initial_amount == ""  || $data->initial_amount < 1) {  
                $errorMsg = "Initial Amount is required";  
            } else {  
                $budget->initial_amount = clean_data($data->initial_amount); 
            } 
            
            $budget->this_user = $this_user_id;
            $budget->country = $mycountry;

            if($errorMsg == '') {

                $response = $budget->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Budget Updated"
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
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        