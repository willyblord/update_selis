<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/SystemsDowntime.php';   

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
        $downtime = new SystemsDowntime($db);

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

        if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_coo'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $downtime->this_user = $user_details['userId'];
        
        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        $errorMsg = ''; 

        if (empty($data->system) || $data->system == "") {  
            $errorMsg = "System Name is required";  
        } else {  
            $downtime->system = clean_data($data->system); 
        } 

        if (empty($data->country) || $data->country == "") {  
            $errorMsg = "Country is required";  
        } else {  
            $downtime->country = clean_data($data->country); 
        } 
        
        if (empty($data->downtime) || $data->downtime == "") {  
            $errorMsg = "Downtime is required";  
        } else {  
            $downtime->downtime = clean_data($data->downtime); 
        } 
        
        if (empty($data->time_started) || $data->time_started == "") {  
            $errorMsg = "Time Started is required";  
        } else {  
            $downtime->time_started = clean_data($data->time_started); 
        } 
        
        $downtime->time_resolved = NULL; 
        if (!empty($data->time_resolved) || $data->time_resolved != "") {  
            $downtime->time_resolved = clean_data($data->time_resolved); 
        }
        
        if (empty($data->tat_in_minutes) || $data->tat_in_minutes == "") {  
            $errorMsg = "TAT is required";  
        } else {  
            $downtime->tat_in_minutes = clean_data($data->tat_in_minutes); 
        } 
        
        if (empty($data->hours_in_minutes) || $data->hours_in_minutes == "") {  
            $errorMsg = "Hours is required";  
        } else {  
            $downtime->hours_in_minutes = clean_data($data->hours_in_minutes); 
        } 
        
        $downtime->rca = NULL; 
        if (!empty($data->rca) || $data->rca != "") {  
            $downtime->rca = clean_data($data->rca); 
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {          

            if($errorMsg == '') {

                // create project
                $response = $downtime->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Your request is sent."
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

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
                $downtime->id = clean_data($data->id); 
                
                if(!($downtime->is_downtime_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $response = $downtime->update();
                if($response) {
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Updated"
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
        