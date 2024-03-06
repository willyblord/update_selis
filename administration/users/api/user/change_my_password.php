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

            if (empty($data->password) || $data->password == "") {  
                $errorMsg = "Password is required";  
            } else {   
                $pass = clean_data($data->password);
            } 

            if (empty($data->rePassword) || $data->rePassword == "" ) { 
                $errorMsg = "Password Confirmation is required";  
            }  else {   
                $rePass = clean_data($data->rePassword);
            } 
            
            if ( ($data->password != "" && $data->rePassword != "") && ( $pass !== $rePass ) ) {
                $errorMsg = "Password and Confirmation are not matching";  
            } 

            $uppercase = preg_match('@[A-Z]@', $pass);
            $lowercase = preg_match('@[a-z]@', $pass);
            $number    = preg_match('@[0-9]@', $pass);
            $specialChars = preg_match('@[^\w]@', $pass);

            if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($pass) < 8) {
                $errorMsg = "Password should be at least 8 characters in length and should include at least 
                one upper case letter, one number, and one special character.";  
            }
                        
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $user->password = $password; 

            $user->this_user = $this_user_id;
            $user_id = $this_user_id;


            if($errorMsg == '') {
                //create user
                $response = $user->change_password();
                if($response) {

                    if($user_data = $user->user_auth_details($user_id)){
                        foreach ($user_data as $row) {
                            $to_name = $row['name'];
                            $to_surname = $row['surname'];
                            $email_to =$row['email'];

                            $title = 'PASSWORD CHANGED | SERIS';
                            $sender = $this_user_id;

                            $message = 'You have successfully change your SERIS password.';
                            
                            $user->save_email( $email_to, $to_name, $email_to, $to_name, $title, $message, $sender );
                        }
                    }
                    
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Password Changed"
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
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        