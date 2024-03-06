<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';
        include_once '../../models/Permission.php';

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
        $permission = new Permission($db);

        //Check jwt validation
        $user_details = $user->validate($_COOKIE["jwt"]);
        if($user_details===false) {
            setcookie("jwt", null, -1);
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

        if($user_details['can_be_super_user'] != 1 && $user_details['can_add_user'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        

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

            $errorMsg = ''; 

            if (empty($data->permission_name) || $data->permission_name == "") {  
                $errorMsg = "Permission Name is required";  
            } else {  
                $permission->permission_name = clean_data($data->permission_name); 

                if($permission->is_same_permission_exists()) {
                    $errorMsg = "The same permission name exists";  
                }
            } 

            $permission->this_user = $this_user_id;

            if($errorMsg == '') {
                //create user
                $response = $permission->create();
                if($response) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Permission Created"
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

            $errorMsg = ''; 

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                $permission->id = clean_data($data->id);
                
                $permission_id = clean_data($data->id);
                if(!$permission->is_permission_exists()) {
                    $errorMsg = "Not Found";  
                }                 
            } 

            if (empty($data->permission_name) || $data->permission_name == "") {  
                $errorMsg = "Permission Name is required";  
            } else {  
                $permission->permission_name = clean_data($data->permission_name); 
                
                $and = ' AND id <> '.$permission_id.' ';
                if($permission->is_same_permission_exists($and)) {
                    $errorMsg = "The same permission name exists";  
                } 
            } 
            
            $permission->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $permission->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Permission Updated"
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
        