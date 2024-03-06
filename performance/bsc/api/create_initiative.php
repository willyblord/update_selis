<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/BSCInitiatives.php';

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
       $bscInitiatives = new BSCInitiatives($db);

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

       $bscInitiatives->this_user = $userDetails['userId'];
       
       $mycountry = $userDetails['country_id'];
       $my_department = $userDetails["department_id"];
        
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

        if (empty($data->individual_bsc_id) || $data->individual_bsc_id == "") {  
            $errorMsg = "Individual BSC is required";  
        } else {  
           $bscInitiatives->individual_bsc_id = clean_data($data->individual_bsc_id); 
           $individual_bsc_id = clean_data($data->individual_bsc_id);
           $group_strategy_id = $bscInitiatives->get_3year_strategy_id($individual_bsc_id);
        }

        if (empty($data->pillar_id) || $data->pillar_id == "") {  
            $errorMsg = "Pillar is required";  
        } else {  
           $bscInitiatives->pillar_id = clean_data($data->pillar_id); 
           $pillar_id = clean_data($data->pillar_id); 
        } 
        
        if (empty($data->bsc_parameter) || $data->bsc_parameter == "") {  
            $errorMsg = "BSC parameter is required";  
        } else {  
           $bscInitiatives->bsc_parameter = clean_data($data->bsc_parameter); 
           $bsc_parameter  = clean_data($data->bsc_parameter); 
        } 

        if (empty($data->initiative_id) || $data->initiative_id == "") {  

            if ( ( empty($data->own_initiative) || $data->own_initiative == "") || ( empty($data->business_category) || $data->business_category == "")) {  
                $errorMsg = "Own Initiative and Business Category required";  
            } else {  
                $group_initiative = clean_data($data->own_initiative); 
                $business_category = clean_data($data->business_category); 

                $init_id = $bscInitiatives->create_own_initiative($business_category, $group_initiative, $pillar_id, 2);
                $bscInitiatives->initiative_id = $init_id;    

                $and_id = '';
                if(isset($data->id) && $data->id != "") {
                    $and_id = ' AND id <> "'.clean_data($data->id).'" ';
                }
                if($bscInitiatives->is_same_initiative_exists($individual_bsc_id, $and_id)) {
                    $errorMsg = "The same Initiative has already been added";  
                } 
            } 

            
        } else {

            $bscInitiatives->initiative_id = clean_data($data->initiative_id);  

            $and_id = '';
            if(isset($data->id) && $data->id != "") {
                $and_id = ' AND id <> "'.clean_data($data->id).'" ';
            }
            if($bscInitiatives->is_same_initiative_exists($individual_bsc_id, $and_id)) {
                $errorMsg = "The same Initiative has already been added";  
            }            
        } 


        if (empty($data->value_impact) || $data->value_impact == "") {  
            $errorMsg = "Value Impact is required";  
        } else {  
           $bscInitiatives->value_impact = clean_data($data->value_impact); 
        }  

        if (empty($data->measure) || $data->measure == "") {  
            $errorMsg = "Measure is required";  
        } else {  
           $bscInitiatives->measure = clean_data($data->measure); 
           $measure = clean_data($data->measure);
        } 

        if ( (empty($data->figure) || $data->figure == "") || ($data->figure === 0) ) {  
            $errorMsg = "Figure is required";  
        } else {  
            $bscInitiatives->figure = ($measure == "Qualitative") ? 100 : clean_data($data->figure); 

            $figure = clean_data($data->figure);
            if( ( $measure == "Quantitative Parcentage" ) && ( $figure > 100 ) ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The Quantitative Parcentage cannot exceed 100%'
                ]);
                die();
            } 
        }

        if ( (empty($data->weight) || $data->weight == "")  || ($data->weight === 0) ) {  
            $errorMsg = "Weight is required";  
        } else {  
            $bscInitiatives->weight = clean_data($data->weight);            
        }         

        if (empty($data->timeline) || $data->timeline == "") {  
            $errorMsg = "Timeline is required";  
        } else {  
           $bscInitiatives->timeline = clean_data($data->timeline); 
           $timeline_date = clean_data($data->timeline); 

            $current_annual = $bscInitiatives->check_year($individual_bsc_id);
            $dated = DateTime::createFromFormat("Y-m-d", $timeline_date);
            $timeline_year = $dated->format("Y");

            if( $timeline_year !== $current_annual ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The Timeline year must be '.$current_annual.''
                ]);
                die();
            } 
        } 

        if (empty($data->target) || $data->target == "") {  
            $errorMsg = "Target is required";  
        } else {  
           $bscInitiatives->target = clean_data($data->target); 
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

            if($errorMsg == '') {

                $total_parameter_weight = $bscInitiatives->check_parameter_weight($individual_bsc_id);
                if( $total_parameter_weight  != 100 ) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'First, the total weight of BSC Parameters must be 100%'
                    ]);
                    die();
                } 

                $total_single_param = $bscInitiatives->check_single_parameter($bsc_parameter);
                $total_init_weight = $bscInitiatives->check_weight($individual_bsc_id, $bsc_parameter);
                
                if( $total_init_weight  + clean_data($data->weight) > $total_single_param) {
                    $remaining = $total_single_param - $total_init_weight ;
                    echo json_encode([
                        'success' => false,
                        'message' => 'The total weight for this BSC Parameter cannot exceed '.$total_single_param.'%, only '.$remaining.'% is remaining'
                    ]);
                    die();
                } 

                // create project
                $response =$bscInitiatives->create();
                if($response) { 
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Value is created."
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

            if (empty($data->id) || $data->id == "") {  
                $errorMsg = "ID is required";  
            } else {  
               $bscInitiatives->id = clean_data($data->id); 
                
                if(!( $bscInitiatives->is_initiative_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }            

            if($errorMsg == '') {

                $bscInitiatives_id = clean_data($data->id);

                $total_parameter_weight = $bscInitiatives->check_parameter_weight($individual_bsc_id);
                if( $total_parameter_weight  != 100 ) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'The total weight of BSC Parameters must be 100%'
                    ]);
                    die();
                } 

                $total_single_param = $bscInitiatives->check_single_parameter($bsc_parameter);
                
                $and = ' AND id <> '.$bscInitiatives_id.' ';
                $total_init_weight = $bscInitiatives->check_weight($individual_bsc_id, $bsc_parameter, $and);
                if( $total_init_weight  + clean_data($data->weight) > $total_single_param) {
                    $remaining = $total_single_param - $total_init_weight ;
                    echo json_encode([
                        'success' => false,
                        'message' => 'The total weight for this BSC Parameter cannot exceed '.$total_single_param.'%, only '.$remaining.'% is remaining'
                    ]);
                    die();
                }

                $response =$bscInitiatives->update();
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
        