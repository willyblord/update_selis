<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../../models/Item.php';

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
        $item= new Item($db);

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

            if (empty($data->grouping_id) || $data->grouping_id == "") {  
                $errorMsg = "Grouping Name is required";  
            } else {  
                $item->grouping_id = clean_data($data->grouping_id); 
            }

            if (empty($data->item_name) || $data->item_name == "") {  
                $errorMsg = "Item Name is required";  
            } else {  

                $item->item_name = clean_data($data->item_name); 
                if($item->is_item_name_exists()) {
                    $errorMsg = "The same Grouping Name already exists";  
                } 
            }

            if (empty($data->unit) || $data->unit == "") {  
                $errorMsg = "Unit is required";  
            } else {  
                $item->unit = clean_data($data->unit); 
            }

            if (empty($data->price_per_unit) || $data->price_per_unit == "") {  
                $errorMsg = "Price is required";  
            } else {  
                $item->price_per_unit = clean_data($data->price_per_unit); 
            }

            $item->this_user = $this_user_id;

            if($errorMsg == '') {
                //create grouping
                $response = $item->create();
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
            $item->id = clean_data($data->id); 
            }

            if (empty($data->grouping_id) || $data->grouping_id == "") {  
                $errorMsg = "Grouping Name is required";  
            } else {  
                $item->grouping_id = clean_data($data->grouping_id); 
            }

            if (empty($data->item_name) || $data->item_name == "") {  
                $errorMsg = "Item Name is required";  
            } else {  

                $item->item_name = clean_data($data->item_name); 
                $and = ' AND id <> "'.$data->id.'" ';
                if($item->is_item_name_exists($and)) {
                    $errorMsg = "The same Grouping Name already exists";  
                } 
            }

            if (empty($data->unit) || $data->unit == "") {  
                $errorMsg = "Unit is required";  
            } else {  
                $item->unit = clean_data($data->unit); 
            }

            if (empty($data->price_per_unit) || $data->price_per_unit == "") {  
                $errorMsg = "Price is required";  
            } else {  
                $item->price_per_unit = clean_data($data->price_per_unit); 
            }
            
            $item->this_user = $this_user_id;

            if($errorMsg == '') {

                $response = $item->update();
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
        