<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../../models/Grouping.php';

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
        $grouping= new Grouping($db);

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

        if($user_details['can_be_super_user'] != 1 && $user_details['can_be_admin'] != 1 && $user_details['can_see_settings'] != 1) {
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

            if (empty($data->group_name) || $data->group_name == "") {  
                $errorMsg = "Grouping Name is required";  
            } else {  

                $grouping->group_name = clean_data($data->group_name); 
                if($grouping->is_grouping_name_exists()) {
                    $errorMsg = "The same Grouping Name already exists";  
                } 
            }
            
            if (empty($data->valuation_method) || $data->valuation_method == "") {  
                $errorMsg = "Valuation Method is required";  
            } else { 
                $grouping->valuation_method = clean_data($data->valuation_method); 
            }

            $grouping->this_user = $this_user_id;

            if($errorMsg == '') {
                //create grouping
                $response = $grouping->create();
                if($response) {
                    
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Grouping Created"
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
            $grouping->id = clean_data($data->id); 
            }

            if (empty($data->group_name) || $data->group_name == "") {  
                $errorMsg = "Grouping Name is required";  
            } else {  

                $grouping->group_name = clean_data($data->group_name); 
                $and = ' AND id <> "'.$data->id.'" ';
                if($grouping->is_grouping_name_exists($and)) {
                    $errorMsg = "The same Grouping Name already exists";  
                } 
            }

            if (empty($data->valuation_method) || $data->valuation_method == "") {  
                $errorMsg = "Valuation Method is required";  
            } else { 
                $grouping->valuation_method = clean_data($data->valuation_method); 
            }
            
            $grouping->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $grouping->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Grouping Updated"
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
        