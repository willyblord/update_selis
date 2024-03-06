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
        

        if( isset($data->operation) && $data->operation == 'delete_approval') {

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

        