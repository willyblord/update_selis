<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';
        include_once '../../models/Approval.php';

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
        $approval = new Approval($db);

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
        

        if( isset($data->operation) && $data->operation == 'delete_approval') {

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
                $approval->id = clean_data($data->id);
                
                if(!$approval->is_approval_exists()) {
                    $errorMsg = "Not Found";  
                } 
            } 

            $approval->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                $res = $approval->delete();
                if($res === true) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Approval Deleted"
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

        