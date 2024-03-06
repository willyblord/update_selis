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
        //Instantiate user object
        $user = new User($db);
        $role = new Role($db);

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

            if (empty($data->role_name) || $data->role_name == "") {  
                $errorMsg = "Role Name is required";  
            } else {  
                $role->role_name = clean_data($data->role_name); 

                if($role->is_same_role_exists()) {
                    $errorMsg = "The same role name exists";  
                }
            } 

            if (empty($data->role_description) || $data->role_description == "" ) {  
                $errorMsg = "Role Description is required";  
            } else {  
                $role->role_description = clean_data($data->role_description); 
            } 

            if (isset($data->role_status) && $data->role_status == "active" ) { 
                $role->role_status = clean_data($data->role_status); 
            } else {  
                $role->role_status = "inactive"; 
            } 

            $role->this_user = $this_user_id;

            if($errorMsg == '') {
                //create user
                $response = $role->create();
                if($response) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Role Created"
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
                $role->id = clean_data($data->id);
                
                $role_id = clean_data($data->id);
                if(!$role->is_role_exists()) {
                    $errorMsg = "Not Found";  
                }                 
            } 

            if (empty($data->role_name) || $data->role_name == "") {  
                $errorMsg = "Role Name is required";  
            } else {  
                $role->role_name = clean_data($data->role_name); 

                $and = ' AND id <> '.$role_id.' ';
                if($role->is_same_role_exists($and)) {
                    $errorMsg = "The same role name exists";  
                } 
            } 

            if (empty($data->role_description) || $data->role_description == "" ) {  
                $errorMsg = "Role Description is required";  
            } else {  
                $role->role_description = clean_data($data->role_description); 
            }
            
            if (isset($data->role_status) && $data->role_status == "active" ) { 
                $role->role_status = clean_data($data->role_status); 
            } else {  
                $role->role_status = "inactive"; 
            } 
            
            $role->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $role->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Role Updated"
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
        