<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';
        include_once '../../models/Role.php';

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
        $role = new Role($db);

        //Check jwt validation
        $user_details = $user->validate($_COOKIE["jwt"]);
        if($user_details===false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        }
        $this_user_id = $user_details['userId'];
        $reply_email_to = $user_details['email'];
        $reply_name = $user_details['name'];
        $reply_surname = $user_details['surname']; 

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
        

        if( isset($data->operation) && $data->operation == 'delete_role') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_delete_user'] != 1) {
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
                $role->id = clean_data($data->id); 

                $role_id = clean_data($data->id);
                if(!$role->is_role_exists()) {
                    $errorMsg = "Not Found";  
                } 
                
            } 

            $role->role_status = "deleted";
            $role->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($role->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Role Deleted"
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

        }  elseif( isset($data->operation) && $data->operation == 'save_role_permissions') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_delete_user'] != 1) {
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
                $role->id = clean_data($data->id);
                
                if(!$role->is_role_exists()) {
                    $errorMsg = "Role Not Found";  
                } 
            } 
            
            $role->permission_id = array_map('clean_data', $data->role_permissions);


            $role->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                $res = $role->save_permissions();
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