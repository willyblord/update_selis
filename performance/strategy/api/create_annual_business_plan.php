<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/CountryStrategy.php';

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
        $countryStrategy = new CountryStrategy($db);

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


       $countryStrategy->this_user = $userDetails['userId'];

       $mycountry = $userDetails['country_id'];
       $my_department = $userDetails["department_id"];
       $my_division_id = $userDetails["division_id"];
       $my_division_name = $userDetails["division"];
        
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

        if (empty($data->group_strategy_id) || $data->group_strategy_id == "") {
            $errorMsg = "Department is required";  
        } else {  
           $countryStrategy->group_strategy_id = clean_data($data->group_strategy_id);      
        } 


        if (empty($data->year) || $data->year == "") {  
            $errorMsg = "Year is required";  
        } else {  
           $countryStrategy->year = clean_data($data->year);       
           $year = clean_data($data->year);  

           if( !$countryStrategy->check_strategy_year($year) ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The year selected does not fall in the current 3year strategy '
                ]);
                die();
           }
        } 
    
        $countryStrategy->division = $my_division_id; 
        $countryStrategy->country = $mycountry; 


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
            
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['add_business_plan_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $countryStrategy->status = "saved_as_draft";             

            if($errorMsg == '') {
                
                if($countryStrategy->check_dupli($mycountry, $my_division_id, $year)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You cannot add the business plan twice in the same year'
                    ]);
                    die();
                } 

                // create project
                $response = $countryStrategy->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy is created."
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

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['edit_business_plan_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = ''; 

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
               $countryStrategy->id = clean_data($data->id); 
                
                if(!( $countryStrategy->is_strategy_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $countryStrategy_id = clean_data($data->id);

                $and = ' AND id <> '.$countryStrategy_id.' ';
                if($countryStrategy->check_dupli($mycountry, $my_division_id, $year, $and)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The country or Main Function cannot be added twice in the same year'
                    ]);
                    die();
                } 

                $response =$countryStrategy->update();
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
        