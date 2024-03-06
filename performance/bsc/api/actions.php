<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/BSCInitiatives.php';
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
        //Instantiate idea object
        $user = new User($db);
        $bscInitiatives = new BSCInitiatives($db);
        $individualBsc = new IndividualBsc($db);


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
        
        $this_user = $userDetails['userId'];
        $mycountry = $userDetails['country_id'];
        $my_division_id = $userDetails["division_id"];
        $my_department = $userDetails["department_id"];
        $my_unit = $userDetails["unit_id"];
        $my_section = $userDetails["section_id"];
        $managerId = $userDetails['managerId'];
        
        $individualBsc->this_user = $userDetails['userId'];
        $bscInitiatives->this_user = $userDetails['userId'];

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
    
        if( isset($data->operation) && $data->operation == 'delete_bsc') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['delete_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!($individualBsc->is_bsc_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                $res = $individualBsc->delete();
                if($res === true) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $res
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
        elseif( isset($data->operation) && $data->operation == 'delete_initiative_dp') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['delete_bsc'];
            $requiredModules = 'Performance';
            
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
                $bscInitiatives->id = clean_data($data->id); 

                if(!($bscInitiatives->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($bscInitiatives->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
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

        } 
        elseif( isset($data->operation) && $data->operation == 'delete_parameter') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['delete_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->parameter_id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_parameter_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($individualBsc->delete_parameter()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Item Deleted"
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

        } 
        elseif( isset($data->operation) && $data->operation == 'update_initiative_progress') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['update_progress_bsc'];
            $requiredModules = 'Performance';
            
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
                $bscInitiatives->id = clean_data($data->id); 

                if(!($bscInitiatives->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 

                if($bscInitiatives->read_single()) {
                    $set_figure = $bscInitiatives->figure;
                    $set_weight = $bscInitiatives->weight;    
                }
            } 

            if (empty($data->raw_score)) {  
                $errorMsg = "Figure is required";  
            } else { 
                // Set ID to update 
                $bscInitiatives->raw_score = clean_data($data->raw_score);
                $raw_score = clean_data($data->raw_score);
            } 

            if($errorMsg =='') {

                $bscInitiatives->target_score = ( $raw_score / $set_figure ) * 100;
                $bscInitiatives->computed_score = ( $raw_score / $set_figure ) * $set_weight;
                
                //update idea
                if($bscInitiatives->update_progress()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Changes Saved"
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

        }        
        elseif( isset($data->operation) && $data->operation == 'bsc_evaluate') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'UNIT_LEADER_ROLE', 'HOD_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['evaluate_bsc'];
            $requiredModules = 'Performance';
            
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
                $bscInitiatives->id = clean_data($data->id); 

                if(!($bscInitiatives->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 

                if($bscInitiatives->read_single()) {
                    $set_figure = $bscInitiatives->figure;
                    $set_weight = $bscInitiatives->weight; 
                    $individual_bsc_id = $bscInitiatives->individual_bsc_id;   
                }
            }

            $bsc_status = $individualBsc->check_bsc_status($individual_bsc_id);
            
            if($bsc_status !== "Q1_evaluation_at_hod" && $bsc_status !== "Q1_evaluation_at_country" 
                    && $bsc_status !== "Q1_evaluation_at_hr" && $bsc_status !== "Q1_evaluation_at_gmd"
                    && $bsc_status !== "Q2_evaluation_at_hod" && $bsc_status !== "Q2_evaluation_at_country"
                    && $bsc_status !== "Q2_evaluation_at_hr" && $bsc_status !== "Q2_evaluation_at_gmd"
                    && $bsc_status !== "Q3_evaluation_at_hod" && $bsc_status !== "Q3_evaluation_at_country"
                    && $bsc_status !== "Q3_evaluation_at_hr" && $bsc_status !== "Q3_evaluation_at_gmd"
                    && $bsc_status !== "Q4_evaluation_at_hod" && $bsc_status !== "Q4_evaluation_at_country"
                    && $bsc_status !== "Q4_evaluation_at_hr" && $bsc_status !== "Q4_evaluation_at_gmd"
            ){
                echo json_encode([
                    'success' => false,
                    'message' => 'The initiative is not in the evaluation phase.'
                ]);
                die();
            }

            if (empty($data->raw_score)) {  
                $errorMsg = "Figure is required";  
            } else { 
                // Set ID to update                
                $raw_score = clean_data($data->raw_score);
            } 

            if($errorMsg =='') {

                $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HOD_ROLE'];
                $requiredPermissions = ['evaluate_bsc'];
                $requiredModules = 'Performance';
                
                if(($bsc_status == "Q1_evaluation_at_hod" || $bsc_status == "Q2_evaluation_at_hod" || $bsc_status == "Q3_evaluation_at_hod" || $bsc_status == "Q4_evaluation_at_hod") 
                    &&  ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) ) {
                    
                    $bscInitiatives->line_raw_score = $raw_score;
                    $bscInitiatives->line_target_score = ( $raw_score / $set_figure ) * 100;
                    $bscInitiatives->line_computed_score = ( $raw_score / $set_figure ) * $set_weight;

                    if($bscInitiatives->hod_evaluate()) {

                        echo json_encode(
                            array(
                                "success" => true,
                                "message" => "Changes Saved"
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
                }

                $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
                $requiredPermissions = ['evaluate_bsc'];
                $requiredModules = 'Performance';

                if(($bsc_status == "Q1_evaluation_at_country" || $bsc_status == "Q2_evaluation_at_country" || $bsc_status == "Q3_evaluation_at_country" || $bsc_status == "Q4_evaluation_at_country") 
                    &&  ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules))  ) {

                    $bscInitiatives->division_raw_score = $raw_score;
                    $bscInitiatives->division_target_score = ( $raw_score / $set_figure ) * 100;
                    $bscInitiatives->division_computed_score = ( $raw_score / $set_figure ) * $set_weight;

                    if($bscInitiatives->division_evaluate()) {

                        echo json_encode(
                            array(
                                "success" => true,
                                "message" => "Changes Saved"
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
                }

                $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE'];
                $requiredPermissions = ['evaluate_bsc'];
                $requiredModules = 'Performance';

                if(($bsc_status == "Q1_evaluation_at_hr" || $bsc_status == "Q2_evaluation_at_hr" || $bsc_status == "Q3_evaluation_at_hr" || $bsc_status == "Q4_evaluation_at_hr") 
                    &&  ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules))  ) {

                    $bscInitiatives->hr_raw_score = $raw_score;
                    $bscInitiatives->hr_target_score = ( $raw_score / $set_figure ) * 100;
                    $bscInitiatives->hr_computed_score = ( $raw_score / $set_figure ) * $set_weight;

                    if($bscInitiatives->hr_evaluate()) {

                        echo json_encode(
                            array(
                                "success" => true,
                                "message" => "Changes Saved"
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
        elseif( isset($data->operation) && $data->operation == 'submit_bsc_for_approval') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['submit_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $individual_bsc_id = clean_data($data->id);
            $total_weight = $bscInitiatives->check_wait($individual_bsc_id);
            
            if($total_weight != 100) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The total inititatives weight must be 100%'
                ]);
                die();
            }
            if($bscInitiatives->is_missing_some_data($individual_bsc_id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Some of your targets details are not complete'
                ]);
                die();
            }
            

            $requiredRoles = ['SUPER_USER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = '';
            if ( $user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {
                $individualBsc->location = "@GMD";
            }
            elseif($userDetails['approvalLevel'] == 2 || $userDetails['approvalLevel'] == 3 || $userDetails['approvalLevel'] == 4 || $userDetails['approvalLevel'] == 5){
                $individualBsc->location = "@GMD";
            } 
            elseif( ($userDetails['approvalLevel'] == 6 ) && ($userDetails['managerId'] != '')){
        
                $individualBsc->location = "@MainFunction";
            } 
            elseif( ($userDetails['approvalLevel'] == 7) && ($userDetails['managerId'] != '')){
        
                $individualBsc->location = "@Department";
            } 
            elseif( ($userDetails['approvalLevel'] == 8) && ($userDetails['managerId'] != '')){
        
                $individualBsc->location = "@Unit";
            } 
            elseif($userDetails['approvalLevel'] == "") {
    
                if($my_section != "") {
                    $individualBsc->location = "@Section";
                }
                elseif($my_section = "" && $my_unit != "") {
                    $individualBsc->location = "@Unit";
                }
                elseif($my_section = "" && $my_unit == "" && $my_department != "") {
                    $individualBsc->location = "@Department";
                }
            }

            
            $individualBsc->status = "pending";
            

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_initiative()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Initiative Submited"
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
          
        } 
        elseif( isset($data->operation) && $data->operation == 'submit_bsc_for_evaluation') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['submit_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $individual_bsc_id = clean_data($data->id);
            $bsc_status = $individualBsc->check_bsc_status($individual_bsc_id);
            
            if($bsc_status != "approved" && $bsc_status != "Q1_evaluated" && $bsc_status != "Q2_evaluated" && $bsc_status != "Q3_evaluated") {
                echo json_encode([
                    'success' => false,
                    'message' => 'The BSC must be approved or evaluated'
                ]);
                die();
            }
            
            if($bsc_status == "approved"){
                $individualBsc->status = "Q1_evaluation_at_hod";
            } elseif($bsc_status == "Q1_evaluated"){
                $individualBsc->status = "Q2_evaluation_at_hod";
            } elseif($bsc_status == "Q2_evaluated"){
                $individualBsc->status = "Q3_evaluation_at_hod";
            } elseif($bsc_status == "Q3_evaluated"){
                $individualBsc->status = "Q4_evaluation_at_hod";
            }

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_initiative()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Initiative Submited"
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
              
        } 
        elseif( isset($data->operation) && $data->operation == 'submit_evaluation') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'MAIN_FUNCTION_LEADER_ROLE', 'HOD_ROLE', 'UNIT_LEADER_ROLE', 'SECTION_LEADER_ROLE'];
            $requiredPermissions = ['evaluate_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $individual_bsc_id = clean_data($data->id);
            $bsc_status = $individualBsc->check_bsc_status($individual_bsc_id);
            
            if($bsc_status != "Q1_evaluation_at_hod" && $bsc_status != "Q2_evaluation_at_hod" && $bsc_status != "Q3_evaluation_at_hod" && $bsc_status != "Q4_evaluation_at_hod") {
                echo json_encode([
                    'success' => false,
                    'message' => 'The BSC must be in evaluation phase'
                ]);
                die();
            }
            
            if($bsc_status == "Q1_evaluation_at_hod"){
                $individualBsc->status = "Q1_evaluation_at_country";
            } elseif($bsc_status == "Q2_evaluation_at_hod"){
                $individualBsc->status = "Q2_evaluation_at_country";
            } elseif($bsc_status == "Q3_evaluation_at_hod"){
                $individualBsc->status = "Q3_evaluation_at_country";
            } elseif($bsc_status == "Q4_evaluation_at_hod"){
                $individualBsc->status = "Q4_evaluation_at_country";
            }

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_evaluation()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Evaluation Submited"
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
              
        }
        elseif( isset($data->operation) && $data->operation == 'division_submit_evaluation') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['evaluate_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

           $individual_bsc_id = clean_data($data->id);
            $bsc_status = $individualBsc->check_bsc_status($individual_bsc_id);
            
            if($bsc_status != "Q1_evaluation_at_country" && $bsc_status != "Q2_evaluation_at_country" && $bsc_status != "Q3_evaluation_at_country" && $bsc_status != "Q4_evaluation_at_country") {
                echo json_encode([
                    'success' => false,
                    'message' => 'The BSC must be in evaluation phase'
                ]);
                die();
            }
            
            if($bsc_status == "Q1_evaluation_at_country"){
                $individualBsc->status = "Q1_evaluation_at_hr";
            } elseif($bsc_status == "Q2_evaluation_at_country"){
                $individualBsc->status = "Q2_evaluation_at_hr";
            } elseif($bsc_status == "Q3_evaluation_at_country"){
                $individualBsc->status = "Q3_evaluation_at_hr";
            }elseif($bsc_status == "Q4_evaluation_at_country"){
                $individualBsc->status = "Q4_evaluation_at_hr";
            }

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_evaluation()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Evaluation Submited"
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
              
        }
        elseif( isset($data->operation) && $data->operation == 'hr_submit_evaluation') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['evaluate_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $individual_bsc_id = clean_data($data->id);
            $bsc_status = $individualBsc->check_bsc_status($individual_bsc_id);
            
            if($bsc_status != "Q1_evaluation_at_hr" && $bsc_status != "Q2_evaluation_at_hr" && $bsc_status != "Q3_evaluation_at_hr" && $bsc_status != "Q4_evaluation_at_hr") {
                echo json_encode([
                    'success' => false,
                    'message' => 'The BSC must be in evaluation phase'
                ]);
                die();
            }
            
            if($bsc_status == "Q1_evaluation_at_hr"){
                $individualBsc->status = "Q1_evaluated";
            } elseif($bsc_status == "Q2_evaluation_at_hr"){
                $individualBsc->status = "Q2_evaluated";
            } elseif($bsc_status == "Q3_evaluation_at_hr"){
                $individualBsc->status = "Q3_evaluated";
            } elseif($bsc_status == "Q4_evaluation_at_hr"){
                $individualBsc->status = "Q4_evaluated";
            }
            

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_evaluation()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Evaluation Submited"
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
              
        }
        elseif( isset($data->operation) && $data->operation == 'hod_submit_bsc_for_approval') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HOD_ROLE'];
            $requiredPermissions = ['add_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $individual_bsc_id = clean_data($data->id);
            $total_weight = $bscInitiatives->check_wait($individual_bsc_id);
            
            if($total_weight != 100) {
                echo json_encode([
                    'success' => false,
                    'message' => 'The total inititatives weight must be 100%'
                ]);
                die();
            }
            
            $individualBsc->status = "@MainFunction";

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->submit_initiative()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Initiative Submited"
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
            
        } elseif( isset($data->operation) && $data->operation == 'approve_bsc') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'MAIN_FUNCTION_LEADER_ROLE', 'HOD_ROLE', 'UNIT_LEADER_ROLE', 'SECTION_LEADER_ROLE'];
            $requiredPermissions = ['approve_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            $country_strategy_id = clean_data($data->id);
            
            if( $userDetails['approvalLevel'] = 1 ){

                $individualBsc->status = "approved";
                $individualBsc->location = "@GMD";

            } elseif($userDetails['approvalLevel'] = 2 || $userDetails['approvalLevel'] = 3 || $userDetails['approvalLevel'] = 4 || $userDetails['approvalLevel'] = 5) {

                $individualBsc->status = "approved";
                $individualBsc->location = "@MainFunction";

            } elseif($userDetails['approvalLevel'] = 6) {

                $individualBsc->status = "pending";
                $individualBsc->location = "@Department";

            } elseif($userDetails['approvalLevel'] = 7) {

                $individualBsc->status = "pending";
                $individualBsc->location = "@Unit";
                
            } elseif($userDetails['approvalLevel'] = 8) {

                $individualBsc->status = "pending";
                $individualBsc->location = "@Section";
                
            }

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->approve_bsc()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "BSC Approved"
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
            
        } elseif( isset($data->operation) && ($data->operation == 'hod_revert_bsc' || $data->operation == 'country_revert_bsc' || $data->operation == 'hr_revert_bsc') ) {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HR_ROLE', 'HOD_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['approve_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            }             

            if (empty($data->return_reason) || $data->return_reason == "") {  
                $errorMsg = "Reason is required";  
            } else {  
               $individualBsc->return_reason = clean_data($data->return_reason);      
            } 

            $country_strategy_id = clean_data($data->id);
            
            if($data->operation == 'hod_revert_bsc') {
                $individualBsc->status = "returnedFromHOD";
            } elseif($data->operation == 'country_revert_bsc') {
                $individualBsc->status = "returnedFromCountry";
            } elseif($data->operation == 'hr_revert_bsc') {
                $individualBsc->status = "returnedFromHR";
            }

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->return_dep_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "BSC Returned"
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
            
        } elseif( isset($data->operation) && ($data->operation == 'reject_bsc') ) {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HR_ROLE', 'HOD_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = ['approve_bsc'];
            $requiredModules = 'Performance';
            
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
                $individualBsc->id = clean_data($data->id); 

                if(!( $individualBsc->is_bsc_exists() )) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if (empty($data->reject_reason) || $data->reject_reason == "") {  
                $errorMsg = "Reason is required";  
            } else {  
               $individualBsc->reject_reason = clean_data($data->reject_reason);      
            } 

            $country_strategy_id = clean_data($data->id);
            
            $individualBsc->status = "rejected";

            if($errorMsg =='') {
                
                //update idea
                if($individualBsc->reject_strategy()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "BSC Rejected"
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
            
        } elseif( isset($data->operation) && $data->operation == 'add_initiative_comment') {

            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = ['add_comment_bsc'];
            $requiredModules = 'Performance';
            
            if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->initiative_id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $bscInitiatives->id = clean_data($data->initiative_id); 

                if(!($bscInitiatives->is_initiative_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Item Not Found'
                    ]);
                    die();
                } 
            } 

            if (empty($data->comment)) {  
                $errorMsg = "Comment is required";  
            } else { 
                // Set ID to update 
                $bscInitiatives->comment = clean_data($data->comment);
            }

            if($errorMsg =='') {

                //update idea
                if($bscInitiatives->add_initiative_comment()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Comment Submitted"
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
        }
        else {
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }
        