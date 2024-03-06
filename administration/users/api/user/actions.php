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
        //Instantiate idea object
        $user = new User($db);

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
        
        // $user->this_user = $_SESSION['userIdentification'];

        if( isset($data->operation) && $data->operation == 'deactivate') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['activate_deactivate_user'];
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
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "inactive";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->deactivate()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Deactivated"
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

        } elseif( isset($data->operation) && $data->operation == 'activate') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['activate_deactivate_user'];
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
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "active";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->activate()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Activated"
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

        } elseif( isset($data->operation) && $data->operation == 'reset_password') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['reset_user_password'];
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
                // Set ID to update 
                $user->userId = clean_data($data->id); 
                $user_id = clean_data($data->id);
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

            if($errorMsg =='') {
                //update idea
                if($user->reset_password()) {

                    // Email Notification Logic
                    $to_name = $to_surname = $email_to = '';

                    if($user_data = $user->user_auth_details($user_id)){
                        foreach ($user_data as $row) {
                            $to_name = $row['name'];
                            $to_surname = $row['surname'];
                            $email_to =$row['email'];

                            $title = 'PASSWORD RESET | SERIS';
                            $sender = $this_user_id;

                            $message = 'This is to notify you that your SERIS password has been reset by
                                        <b>'.$reply_name.' '.$reply_surname.'</b> and your password is:
                                        <br>
                                        Password: <b>'.$passcode.'</b>
                                    ';
                            
                            $user->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                        }
                    }

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Password Reset"
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

        } elseif( isset($data->operation) && $data->operation == 'delete') {

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
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "deleted";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Deleted"
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

        } else {
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }