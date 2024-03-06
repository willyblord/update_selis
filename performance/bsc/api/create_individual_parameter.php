<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/IndividualBsc.php';

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
        $individualBsc = new IndividualBsc($db);

        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if($userDetails===false) {
            setcookie("jwt", null, -1);
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 
        

       $individualBsc->this_user = $userDetails['userId'];
       
       $mycountry = $userDetails['country_id'];
       $my_department = $userDetails["department_id"];
       $my_division_id = $userDetails["division_id"];
       $my_division_name = $userDetails["division"];

       $bsc_owner = $userDetails['userId'];
        
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
        

        if (empty($data->individual_bsc_id) || $data->individual_bsc_id == "") {  
            $errorMsg = "ID is required";  
        } else {  
           $individualBsc->id = clean_data($data->individual_bsc_id); 
           $individual_bsc_id = clean_data($data->individual_bsc_id); 
            
            if(!( $individualBsc->is_bsc_exists() )) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Individual BSC Not Found'
                ]);
                die();
            } 
        } 

        if (empty($data->bsc_parameter_id) || $data->bsc_parameter_id == "") {  
            $errorMsg = "BSC Parameter required";  
        } else {  
           $individualBsc->bsc_parameter_id = clean_data($data->bsc_parameter_id);       
           $bsc_parameter_id = clean_data($data->bsc_parameter_id); 
        }

        if (empty($data->parameter_weight) || $data->parameter_weight == "") {  
            $errorMsg = "Parameter Weight required";  
        } else {  
           $individualBsc->parameter_weight = clean_data($data->parameter_weight);       
           $parameter_weight = clean_data($data->parameter_weight); 
        }

        $this_user = $userDetails['userId'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
            
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['add_bsc'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }          

            if($errorMsg == '') {
               
                if($individualBsc->check_duplicate_parameters($individual_bsc_id, $bsc_parameter_id)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The BSC Parameter cannot be added twice'
                    ]);
                    die();
                } 

                // create project
                $response = $individualBsc->create_parameter();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "BSC Parameter is created."
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

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['edit_bsc'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = ''; 

            if (empty($data->parameter_id) || $data->parameter_id == "") {  
                $errorMsg = "Parameter ID is required";  
            } else {  
               $individualBsc->parameter_id = clean_data($data->parameter_id); 
                
                if(!( $individualBsc->is_bsc_parameter_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $parameter_id = clean_data($data->parameter_id);

                $and = ' AND id <> '.$parameter_id.' ';
                if($individualBsc->check_duplicate_parameters($individual_bsc_id, $bsc_parameter_id, $and)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The BSC Parameter cannot be added twice'
                    ]);
                    die();
                } 

                $response =$individualBsc->update_parameter();
                if($response) {
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
        