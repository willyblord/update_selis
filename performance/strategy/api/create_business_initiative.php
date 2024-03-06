<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/BusinessInitiative.php';

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
       $businessInitiative = new BusinessInitiative($db);

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

       $businessInitiative->this_user = $userDetails['userId'];
       
       $mycountry = $userDetails['country_id'];
       $my_department = $userDetails["department_id"];
        
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

        if (empty($data->country_strategy_id) || $data->country_strategy_id == "") {  
            $errorMsg = "Strategy is required";  
        } else {  
           $businessInitiative->country_strategy_id = clean_data($data->country_strategy_id); 
           $country_strategy_id = clean_data($data->country_strategy_id);
           $group_strategy_id = $businessInitiative->get_3year_strategy_id($country_strategy_id);
        } 

        if (empty($data->pillar_id) || $data->pillar_id == "") {  
            $errorMsg = "Pillar is required";  
        } else {  
           $businessInitiative->pillar_id = clean_data($data->pillar_id); 
           $pillar_id = clean_data($data->pillar_id); 
        } 

        
        if (empty($data->initiative) || $data->initiative == "") {  
            
            $businessInitiative->initiative_id = NULL;

            if ( ( empty($data->own_initiative) || $data->own_initiative == "") || ( empty($data->business_category) || $data->business_category == "")) {  
                $errorMsg = "Own Initiative and Business Category required";  
            } else {  
               $group_initiative = clean_data($data->own_initiative); 
               $business_category = clean_data($data->business_category); 

               $init_id = $businessInitiative->create_own_initiative($business_category, $group_initiative, $pillar_id, 2);
               $businessInitiative->initiative_id = $init_id;    
            } 

            
        } else {

            $businessInitiative->initiative_id = clean_data($data->initiative);             
        }        
        

        if (empty($data->target) || $data->target == "") {  
            $errorMsg = "Target is required";  
        } else {  
           $businessInitiative->target = clean_data($data->target); 
        } 

        if (empty($data->value_impact) || $data->value_impact == "") {  
            $errorMsg = "Value Impact is required";  
        } else {  
           $businessInitiative->value_impact = clean_data($data->value_impact); 
        } 

        if (empty($data->timeline) || $data->timeline == "") {  
            $errorMsg = "Timeline is required";  
        } else {  
           $businessInitiative->timeline = clean_data($data->timeline); 
           $timeline_date = clean_data($data->timeline); 

            $current_annual = $businessInitiative->check_year($country_strategy_id);
            $dated = DateTime::createFromFormat("Y-m-d", $timeline_date);
            $timeline_year = $dated->format("Y");

            if( $timeline_year !== $current_annual ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The Timeline year must be '.$current_annual.''
                ]);
                die();
            } 
        } 

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

            if($errorMsg == '') {

                // create project
                $response =$businessInitiative->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Initiative is created."
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

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
               $businessInitiative->id = clean_data($data->id); 
                
                if(!( $businessInitiative->is_initiative_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $response =$businessInitiative->update();
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
        