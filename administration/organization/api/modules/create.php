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
        //Instantiate user object
        $user = new User($db);
        $module = new Module($db);

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

            if (empty($data->module_name) || $data->module_name == "") {  
                $errorMsg = "module Name is required";  
            } else {  
                $module->module_name = clean_data($data->module_name); 

                if($module->is_same_module_exists()) {
                    $errorMsg = "The same module name exists";  
                }
            } 

            $module->this_user = $this_user_id;

            if($errorMsg == '') {
                //create user
                $response = $module->create();
                if($response) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Module Created"
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
                $module->id = clean_data($data->id);
                
                $module_id = clean_data($data->id);
                if(!$module->is_module_exists()) {
                    $errorMsg = "Not Found";  
                }                 
            } 

            if (empty($data->module_name) || $data->module_name == "") {  
                $errorMsg = "Module Name is required";  
            } else {  
                $module->module_name = clean_data($data->module_name); 
                
                $and = ' AND id <> '.$module_id.' ';
                if($module->is_same_module_exists($and)) {
                    $errorMsg = "The same module name exists";  
                } 
            } 
            
            $module->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $module->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Module Updated"
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
        