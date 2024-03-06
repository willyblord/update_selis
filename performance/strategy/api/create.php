<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Strategy.php';

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
        $strategy = new Strategy($db);

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
        

        $strategy->this_user = $userDetails['userId'];
        
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

        if (empty($data->strategy_name) || $data->strategy_name == "") {  
            $errorMsg = "Strategy Name is required";  
        } else {  
            $strategy->strategy_name = clean_data($data->strategy_name); 
        } 

        if ( (empty($data->fromYear) || $data->fromYear == "") || ((empty($data->toYear) || $data->toYear == "")) ) {  
            $errorMsg = "Year Range is required";  
        } else {  
            $strategy->year_range = clean_data($data->fromYear).'-'.clean_data($data->toYear); 
        } 
        
        if (empty($data->vision) || $data->vision == "") {  
            $errorMsg = "Vision is required";  
        } else {  
            $strategy->vision = clean_data($data->vision); 
        } 
        
        if (empty($data->mission) || $data->mission == "") {  
            $errorMsg = "Mission is required";  
        } else {  
            $strategy->mission = clean_data($data->mission); 
        }

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

            $strategy->status = "pending";      

            if($errorMsg == '') {

                // create project
                $response = $strategy->create();
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

            $errorMsg = ''; 

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
                $strategy->id = clean_data($data->id); 
                
                if(!($strategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $response = $strategy->update();
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
        