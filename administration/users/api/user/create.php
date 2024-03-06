<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';

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

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['add_user'];
            $requiredModules = 'Administration';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = ''; 

            if (empty($data->name) || $data->name == "") {  
                $errorMsg = "Name is required";  
            } else {  
            $user->name = clean_data($data->name); 
            } 

            if (empty($data->surname) || $data->surname == "" ) {  
                $errorMsg = "Surname is required";  
            } else {  
            $user->surname = clean_data($data->surname); 
            } 

            if(!empty($data->staffNumber) || $data->staffNumber !="") { 
                $user->staffNumber = $data->staffNumber; 
            } else {  
                $user->staffNumber = NULL; 
            } 

            if (empty($data->country) || $data->country == "") {  
                $errorMsg = "Country is required";  
            } else {  
            $user->country_id = clean_data($data->country); 
            } 

            if (empty($data->division) || $data->division == "") {  
                $errorMsg = "Division is required";  
            } else {  
            $user->division_id = clean_data($data->division); 
            } 

            if (empty($data->department) || $data->department == "") {  
                $errorMsg = "Department is required";  
            } else {  
            $user->department_id = clean_data($data->department); 
            } 

            if(!empty($data->unit) || $data->unit !="") { 
                $user->unit_id = $data->unit; 
            } else {  
                $user->unit_id = NULL; 
            } 

            if(!empty($data->section) || $data->section !="") { 
                $user->section_id = $data->section; 
            } else {  
                $user->section_id = NULL; 
            } 
            $user->roles = array_map('clean_data', $data->roles);

            if (empty($data->email) || $data->email == "") {  
                $errorMsg = "Email is required";  
            } else {  

                if(!filter_var($data->email, FILTER_VALIDATE_EMAIL))
                {
                    $errorMsg = "Invalid Email Addresss.";
                }
                elseif (!preg_match('|@smartapplicationsgroup.com$|', $data->email))
                {
                    $errorMsg = "ERROR: Invalid Company Email Address.";
                }

                $user->email = clean_data($data->email); 
                if($user->is_email_exists()) {
                    $errorMsg = "Email used by another user";  
                }

                $parts = explode("@", $data->email);
				$username = $parts[0];
                if($user->is_username_exists()) {
                    $errorMsg = "Username used by another user";  
                } 
                $user->username = clean_data($username); 
            }

           function random_password(){
                $random_characters = 2;
                
                $lower_case = "abcdefghijklmnopqrstuvwxyz";
                $upper_case = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $numbers = "1234567890";
                $symbols = "!@#$%^&*";
                
                $lower_case = str_shuffle($lower_case);
                $upper_case = str_shuffle($upper_case);
                $numbers = str_shuffle($numbers);
                $symbols = str_shuffle($symbols);
                
                $random_password = substr($lower_case, 0, $random_characters);
                $random_password .= substr($upper_case, 0, $random_characters);
                $random_password .= substr($numbers, 0, $random_characters);
                $random_password .= substr($symbols, 0, $random_characters);
                
                return  str_shuffle($random_password);
            }
            $passcode = random_password();
            $password = password_hash($passcode, PASSWORD_DEFAULT);
            $user->password = $password; 

            $user->this_user = $this_user_id;


            $user->status = "active"; 

            if($errorMsg == '') {
                //create user
                $response = $user->create();
                if($response) {

                    $email_to = clean_data($data->email); 
                    $to_name = clean_data($data->name); 
                    $title = 'ACCOUNT CREATED | SERIS';
                    $sender = $this_user_id;

                    $message = 'This is to notify you that your SERIS account has been created by
                                <b>'.$reply_name.' '.$reply_surname.'</b> and your credentials are:
                                <br>
                                Username: <b>'.$username.'</b> <br>
                                Password: <b>'.$passcode.'</b>
                            ';
                    
                    $user->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                    
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Created"
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

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['edit_user'];
            $requiredModules = 'Administration';
            
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
            $user->userId = clean_data($data->id); 
            } 

            if (empty($data->name) || $data->name == "") {  
                $errorMsg = "Name is required";  
            } else {  
            $user->name = clean_data($data->name); 
            } 

            if (empty($data->surname) || $data->surname == "" ) {  
                $errorMsg = "Surname is required";  
            } else {  
            $user->surname = clean_data($data->surname); 
            } 

            if (!empty($data->staffNumber) || $data->staffNumber =="") {  
                $user->staffNumber = $data->staffNumber; 
            } else {  
            $user->staffNumber = NULL; 
            }  

            if (empty($data->country) || $data->country == "") {  
                $errorMsg = "Country is required";  
            } else {  
            $user->country_id = clean_data($data->country); 
            } 

            if (empty($data->division) || $data->division == "") {  
                $errorMsg = "Division is required";  
            } else {  
            $user->division_id = clean_data($data->division); 
            } 

            if (empty($data->department) || $data->department == "") {  
                $errorMsg = "Department is required";  
            } else {  
            $user->department_id = clean_data($data->department); 
            } 

            if(!empty($data->unit) || $data->unit !="") { 
                $user->unit_id = $data->unit; 
            } else {  
                $user->unit_id = NULL; 
            } 

            if(!empty($data->section) || $data->section !="") { 
                $user->section_id = $data->section; 
            } else {  
                $user->section_id = NULL; 
            } 
            $user->roles = array_map('clean_data', $data->roles);

            if (empty($data->email) || $data->email == "") {  
                $errorMsg = "Email is required";  
            } else { 
                
                if(!filter_var($data->email, FILTER_VALIDATE_EMAIL))
                {
                    $errorMsg = "Invalid Email Addresss.";
                }
                elseif (!preg_match('|@smartapplicationsgroup.com$|', $data->email))
                {
                    $errorMsg = "ERROR: Invalid Company Email Address.";
                }

                $user->email = clean_data($data->email); 
                $and = ' AND userId <> "'.$data->id.'" ';
                if($user->is_email_exists($and)) {
                    $errorMsg = "Email used by another user";  
                } 

                $parts = explode("@", $data->email);
				$username = $parts[0];
                $and = ' AND userId <> "'.$data->id.'" ';
                if($user->is_username_exists($and )) {
                    $errorMsg = "Username used by another user";  
                } 
                $user->username = clean_data($username); 
            }
            
            $user->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $user->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Updated"
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
        