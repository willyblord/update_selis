<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';
        include_once '../../models/Module.php';

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
        $module = new Module($db);

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
        $this_user_id = $userDetails['userId'];
        $reply_email_to = $userDetails['email'];
        $reply_name = $userDetails['name'];
        $reply_surname = $userDetails['surname']; 

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
        

        if( isset($data->operation) && $data->operation == 'delete_module') {

            $requiredRoles = ['SUPER_USER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = '';
            
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
                $module->id = clean_data($data->id);
                
                if(!$module->is_module_exists()) {
                    $errorMsg = "Not Found";  
                } 
            } 

            $module->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                $res = $module->delete();
                if($res === true) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Module Deleted"
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

        } elseif( isset($data->operation) && $data->operation == 'save_module_permissions') {

            $requiredRoles = ['SUPER_USER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = '';
            
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
                $module->id = clean_data($data->id);
                
                if(!$module->is_module_exists()) {
                    $errorMsg = "Module Not Found";  
                } 
            } 
            
            $module->permission_id = array_map('clean_data', $data->module_permissions);


            $module->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                $res = $module->save_permissions();
                if($res === true) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Permission Saved"
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

        } else {
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }

        