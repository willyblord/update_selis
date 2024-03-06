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
        //Instantiate user object
        $user = new User($db);
        $approval = new Approval($db);

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

            if (empty($data->approval_name) || $data->approval_name == "") {  
                $errorMsg = "Approval Name is required";  
            } else {  
                $approval->approval_name = clean_data($data->approval_name); 
            } 

            if (empty($data->approval_level) || $data->approval_level == "") {  
                $errorMsg = "Approval Level is required";  
            } else {  
                $approval->approval_level = clean_data($data->approval_level);                 
            } 

            if($data->approval_name != "" && $data->approval_level != "") {
                if($approval->is_same_approval_exists()) {
                    $errorMsg = "The same approval name/level exists";  
                }
            }

            $approval->this_user = $this_user_id;

            if($errorMsg == '') {
                //create user
                $response = $approval->create();
                if($response) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Approval Created"
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
                $approval->id = clean_data($data->id);
                
                $approval_id = clean_data($data->id);
                if(!$approval->is_approval_exists()) {
                    $errorMsg = "Not Found";  
                }                 
            } 

            if (empty($data->approval_name) || $data->approval_name == "") {  
                $errorMsg = "Approval Name is required";  
            } else {  
                $approval->approval_name = clean_data($data->approval_name); 
            } 

            if (empty($data->approval_level) || $data->approval_level == "") {  
                $errorMsg = "Approval Level is required";  
            } else {  
                $approval->approval_level = clean_data($data->approval_level);                 
            } 

            if($data->approval_name != "" && $data->approval_level != "") {
                $and = ' AND id <> '.$approval_id.' ';
                if($approval->is_same_approval_exists($and)) {
                    $errorMsg = "The same approval name/level exists";  
                } 
            }
            
            $approval->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $approval->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Approval Updated"
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
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        