<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Strategy.php';
        include_once '../models/Value.php';
        include_once '../models/Pillar.php';
        include_once '../models/BusinessInitiative.php';
        include_once '../models/Initiative.php';
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
        //Instantiate idea object
        $user = new User($db);
        $strategy = new Strategy($db);
        $value = new Value($db);
        $pillar = new Pillar($db);
        $businessInitiative = new BusinessInitiative($db);
        $initiative = new Initiative($db);
        $countryStrategy = new CountryStrategy($db);


        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if($userDetails===false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        }
        
        $strategy->this_user = $userDetails['userId'];
        $businessInitiative->this_user = $userDetails['userId'];
        $countryStrategy->this_user = $userDetails['userId'];

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
    
        if( isset($data->operation) && $data->operation == 'delete_strategy') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['delete_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $strategy->id = clean_data($data->id); 

                if(!($strategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($strategy->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'activate_strategy') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['activate_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $strategy->id = clean_data($data->id); 

                if(!($strategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 

                if(($strategy->is_there_an_active_strategy())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'There is another active Strategy. You need first to End the active one.'
                    ]);
                    die();
                }
                
            } 

            if($errorMsg =='') {
                $strategy->status = "active"; 
                //update idea
                if($strategy->activate_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Activated"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'end_strategy') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['end_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $strategy->id = clean_data($data->id); 

                if(!($strategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                $strategy->status = "ended"; 
                //update idea
                if($strategy->end_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Ended"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'delete_value') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['delete_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $value->id = clean_data($data->id); 

                if(!($value->is_value_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($value->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'delete_group_initiative') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['delete_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $initiative->id = clean_data($data->id); 

                if(!($initiative->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($initiative->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'delete_pillar') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['delete_group_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $pillar->id = clean_data($data->id); 

                if(!($pillar->is_pillar_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($pillar->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'delete_country_strategy') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['delete_business_plan_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $countryStrategy->id = clean_data($data->id); 

                if(!($countryStrategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 

                if( $countryStrategy->check_strategy_status() != "saved_as_draft") {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Only draft strategies can be deleted'
                    ]);
                    die();
                }
            } 

            if($errorMsg =='') {
                //update idea
                $res = $countryStrategy->delete();
                if($res === true) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $res
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
        elseif( isset($data->operation) && $data->operation == 'delete_initiative_dp') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['delete_business_plan_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $businessInitiative->id = clean_data($data->id); 

                if(!($businessInitiative->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($businessInitiative->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
        elseif( isset($data->operation) && $data->operation == 'submit_strategy_for_approval') {

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

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $countryStrategy->id = clean_data($data->id); 

                if(!( $countryStrategy->is_strategy_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 
            
            $countryStrategy->status = "@COO";

            if($errorMsg =='') {
                
                //update idea
                if($countryStrategy->submit_initiative()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Submited"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
            
        } elseif( isset($data->operation) && $data->operation == 'approve_division_strategy') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['approve_business_plan_strategy'];
            $requiredModules = 'Performance';

            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $countryStrategy->id = clean_data($data->id); 

                if(!( $countryStrategy->is_strategy_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $country_strategy_id = clean_data($data->id);
            
            $countryStrategy->status = "approved";

            if($errorMsg =='') {
                
                //update idea
                if($countryStrategy->approve_division_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Approved"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
            
        } elseif( isset($data->operation) && ($data->operation == 'coo_revert_strategy') ) {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['approve_business_plan_strategy'];
            $requiredModules = 'Performance';

            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $countryStrategy->id = clean_data($data->id); 

                if(!( $countryStrategy->is_strategy_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }             

            if (empty($data->return_reason) || $data->return_reason == "") {  
                $errorMsg = "Reason is required";  
            } else {  
               $countryStrategy->return_reason = clean_data($data->return_reason);      
            } 

            $country_strategy_id = clean_data($data->id);
            
            if($data->operation == 'division_revert_strategy') {
                $countryStrategy->status = "returnedFromDivision";
            } elseif($data->operation == 'coo_revert_strategy') {
                $countryStrategy->status = "returnedFromCOO";
            } 

            if($errorMsg =='') {
                
                //update idea
                if($countryStrategy->return_dep_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Returned"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
            
        } elseif( isset($data->operation) && ($data->operation == 'reject_strategy') ) {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['approve_business_plan_strategy'];
            $requiredModules = 'Performance';

            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $countryStrategy->id = clean_data($data->id); 

                if(!( $countryStrategy->is_strategy_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if (empty($data->reject_reason) || $data->reject_reason == "") {  
                $errorMsg = "Reason is required";  
            } else {  
               $countryStrategy->reject_reason = clean_data($data->reject_reason);      
            } 

            $country_strategy_id = clean_data($data->id);
            
            $countryStrategy->status = "rejected";

            if($errorMsg =='') {
                
                //update idea
                if($countryStrategy->reject_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Strategy Rejected"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
            
        }  elseif( isset($data->operation) && $data->operation == 'add_initiative_comment') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['add_comment_strategy'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->initiative_id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $businessInitiative->id = clean_data($data->initiative_id); 

                if(!($businessInitiative->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if (empty($data->comment)) {  
                $errorMsg = "Comment is required";  
            } else { 
                // Set ID to update 
                $businessInitiative->comment = clean_data($data->comment);
                $comment = clean_data($data->comment);
            }

            if($errorMsg =='') {

                //update idea
                if($businessInitiative->add_initiative_comment()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Comment Submitted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
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
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }
        