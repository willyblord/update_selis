<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';
        include_once '../../models/ApprovalHierarchy.php';

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
        $approvalHierarchy = new ApprovalHierarchy($db);

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

            if (empty($data->user_id) || $data->user_id == "") {  
                $errorMsg = "Staff Name is required";  
            } else {  
                $approvalHierarchy->user_id = clean_data($data->user_id); 
            } 

            if (empty($data->approval_id) || $data->approval_id == "") {  
                $errorMsg = "Approval Name is required";  
            } else {  
                $approvalHierarchy->approval_id = clean_data($data->approval_id);    
                $approval_id = clean_data($data->approval_id);               
            } 

            $approvalHierarchy->manager_id = NULL; 
            if ( isset($approval_id) && $approval_id != 1 && $data->manager_id == "") {  
                $errorMsg = "Supervisor Name is required";  
            } elseif ( isset($approval_id) && $approval_id != 1 && $data->manager_id != "") {
                $approvalHierarchy->manager_id = clean_data($data->manager_id);                 
            } 

            $approvalHierarchy->this_user = $this_user_id;

            if($errorMsg == '') {
                if( $approvalHierarchy->is_same_hierarchy_exists() ) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The same staff/supervisor/approval exists'  
                    ]);
                    die();
                }

                //create user
                $response = $approvalHierarchy->create();
                if($response) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Hierarchy Created"
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
                $approvalHierarchy->id = clean_data($data->id);
                
                $approvalHierarchy_id = clean_data($data->id);
                if(!$approvalHierarchy->is_hierarchy_exists()) {
                    $errorMsg = "Not Found";  
                }                 
            } 

            if (empty($data->user_id) || $data->user_id == "") {  
                $errorMsg = "Staff Name is required";  
            } else {  
                $approvalHierarchy->user_id = clean_data($data->user_id); 
            } 

            if (empty($data->approval_id) || $data->approval_id == "") {  
                $errorMsg = "Approval Name is required";  
            } else {  
                $approvalHierarchy->approval_id = clean_data($data->approval_id);    
                $approval_id = clean_data($data->approval_id);               
            } 

            $approvalHierarchy->manager_id = NULL; 
            if ( isset($approval_id) && $approval_id != 1 && $data->manager_id == "") {  
                $errorMsg = "Supervisor Name is required";  
            } elseif ( isset($approval_id) && $approval_id != 1 && $data->manager_id != "") {
                $approvalHierarchy->manager_id = clean_data($data->manager_id);                 
            } 
            
            $approvalHierarchy->this_user = $this_user_id;

            if($errorMsg == '') {

                $and = ' AND id <> '.$approvalHierarchy_id.' ';
                if( $approvalHierarchy->is_same_hierarchy_exists($and ) ) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The same staff/supervisor/approval exists'  
                    ]);
                    die();
                }

                $response = $approvalHierarchy->update();
                if($response ) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Hierarchy Updated"
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
        