<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Pillar.php';
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
        $pillar = new Pillar($db);

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


        $pillar->this_user = $userDetails['userId'];
        
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

        if (empty($data->stratId) || $data->stratId == "") {  
            $errorMsg = "Strategy is required";  
        } else {  
            $strategy->id = clean_data($data->stratId);
            
            if(!($strategy->is_strategy_exists())) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Strategy Not Found'
                ]);
                die();
            } 

            $pillar->strategy_id = clean_data($data->stratId);
            $strategy_id = clean_data($data->stratId);
        } 

        if (empty($data->strategy_pillar) || $data->strategy_pillar == "") {
            $errorMsg = "Pillar is required";  
        } else { 
            $pillar->strategy_pillar = clean_data($data->strategy_pillar); 

            // $and_id = '';
            // if(isset($data->id) && $data->id != "") {
            //     $and_id = ' AND id <> "'.clean_data($data->id).'" ';
            // }
            // if($pillar->is_same_pillar_exists($strategy_id, $and_id)) {
            //     $errorMsg = "The same pillar already exists!";  
            // } 
        } 
        
        
        if (empty($data->strategic_objective) || $data->strategic_objective == "") {  
            $errorMsg = "Strategic Objective is required";  
        } else {  
            $pillar->strategic_objective = clean_data($data->strategic_objective); 
        } 
        
        
        if (empty($data->picture_of_success) || $data->picture_of_success == "") {  
            $errorMsg = "picture of success is required";  
        } else {  
            $pillar->picture_of_success = clean_data($data->picture_of_success); 
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

            if($errorMsg == '') {

                // create project
                $response = $pillar->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Pillar is created."
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
                $pillar->id = clean_data($data->id); 
                
                if(!($pillar->is_pillar_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $response = $pillar->update();
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
        