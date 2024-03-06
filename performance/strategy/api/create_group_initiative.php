<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Initiative.php';

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
        $initiative = new Initiative($db);

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

       $initiative->this_user = $userDetails['userId'];
       
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
        

        if (empty($data->group_initiative) || $data->group_initiative == "") {  
            $errorMsg = "Group Initiative is required";  
        } else {  
           $initiative->group_initiative = clean_data($data->group_initiative); 
        }

        if (empty($data->business_category) || $data->business_category == "") {  
            $errorMsg = "Business Category is required";  
        } else {  
           $initiative->business_category = clean_data($data->business_category); 
        } 

        if (empty($data->group_initiative) || $data->group_initiative == "") {  
            $errorMsg = "Group Initiative is required";  
        } else {  
           $initiative->group_initiative = clean_data($data->group_initiative); 
        }
        
        if (empty($data->pillar_id) || $data->pillar_id == "") {  
            $errorMsg = "Pillar is required";  
        } else {  
            $initiative->pillar_id = clean_data($data->pillar_id); 
        }
        
        if (empty($data->target) || $data->target == "") {  
            $errorMsg = "Target is required";  
        } else {  
            $initiative->target = clean_data($data->target); 
        }
        
        if (empty($data->measure) || $data->measure == "") {  
            $errorMsg = "Measure is required";  
        } else {  
            $initiative->measure = clean_data($data->measure); 
        }
        
        if (empty($data->timeline) || $data->timeline == "") {  
            $errorMsg = "Timeline is required";  
        } else {  
            $initiative->timeline = clean_data($data->timeline); 
        }
        
        $initiative->type = 1; 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['add_group_strategy'];
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
                $response =$initiative->create();
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

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['edit_group_strategy'];
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
               $initiative->id = clean_data($data->id); 
                
                if(!( $initiative->is_initiative_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $response =$initiative->update();
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
        