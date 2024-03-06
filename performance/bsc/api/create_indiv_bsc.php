<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/IndividualBsc.php';

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
        $individualBsc = new IndividualBsc($db);

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
        

       $individualBsc->this_user = $userDetails['userId'];
       
       $mycountry = $userDetails['country_id'];
       $my_division_id = $userDetails["division_id"];
       $my_department = $userDetails["department_id"];
       $my_unit = $userDetails["unit_id"];
       $my_section = $userDetails["section_id"];
       $my_division_name = $userDetails["division"];

       $bsc_owner = $userDetails['userId'];
        
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
        

        if( !$individualBsc->get_active_strategy() ) {
            echo json_encode([
                'success' => false,
                'message' => 'There is no active 3 year strategy '
            ]);
            die();
        } else {
            $strategy_id = $individualBsc->get_active_strategy();
            $individualBsc->group_strategy_id = $strategy_id;
        }


        if (empty($data->year) || $data->year == "") {  
            $errorMsg = "Year is required";  
        } else {  
           $individualBsc->year = clean_data($data->year);       
           $year = clean_data($data->year);  

           if( !$individualBsc->check_strategy_year($year) ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The year selected does not fall in the current 3year strategy '
                ]);
                die();
           }
        }


        $individualBsc->country = $mycountry; 
        $individualBsc->division = $my_division_id; 
        $individualBsc->department = $my_department; 
        $individualBsc->bsc_owner = $bsc_owner;

        $this_user = $userDetails['userId'];

        $requiredRoles = ['SUPER_USER_ROLE'];
        $requiredPermissions = [];
        $requiredModules = '';
        
        if ( ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) )
            || ($userDetails['approvalLevel'] == 2 || $userDetails['approvalLevel'] == 3 
            || $userDetails['approvalLevel'] == 4 || $userDetails['approvalLevel'] == 5) )
        {
        

            if(!$individualBsc->check_my_business_plan($strategy_id, $this_user)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You need to add the Business plan first!'
                ]);
                die();
            } 
        } 
        else {

            if($userDetails['approvalLevel'] == 6 ) {
                $and_query = 'AND b.country = '.$mycountry.' AND b.division = '.$my_division_id.' AND a.approval_level IN("2", "3", "4", "5") ';
            }
            elseif($userDetails['approvalLevel'] == 7 ) {
                $and_query = 'AND b.country = '.$mycountry.' AND b.division = '.$my_division_id.' AND b.department = '.$my_department.' 
                AND a.approval_level = 6';
            } 
            elseif($userDetails['approvalLevel'] == 8 ) {
                $and_query = 'AND b.country = '.$mycountry.' AND b.division = '.$my_division_id.' AND b.department = '.$my_department.' 
                AND b.unit = '.$my_unit.' AND a.approval_level = 7';
            }             
            else {
                $and_query = 'AND b.country = '.$mycountry.' AND b.division = '.$my_division_id.' AND b.department = '.$my_department.' 
                AND b.unit = '.$my_unit.' AND b.section = '.$my_section.' AND a.approval_level = 8';
            } 

            if(!$individualBsc->check_supervisor_bsc($strategy_id, $and_query)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'you cannot add your BSC  until your supervisor does'
                ]);
                die();
            } 
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
            
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['add_bsc'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $individualBsc->status = "saved_as_draft";             

            if($errorMsg == '') {
                
                if($individualBsc->check_dupli($mycountry,$year, $bsc_owner)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The BSC owner cannot be added twice in the same year'
                    ]);
                    die();
                } 

                // create project
                $response = $individualBsc->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "BSC is created."
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
            $requiredPermissions = ['edit_bsc'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = ''; 

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
               $individualBsc->id = clean_data($data->id); 
                
                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $individualBsc_id = clean_data($data->id);

                $and = ' AND id <> '.$individualBsc_id.' ';
                if($individualBsc->check_dupli($mycountry, $year, $bsc_owner, $and)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The BSC owner cannot be added twice in the same year'
                    ]);
                    die();
                } 

                $response =$individualBsc->update();
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
        