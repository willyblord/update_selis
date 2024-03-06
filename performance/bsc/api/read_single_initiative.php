<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/BSCInitiatives.php';
    include_once '../../../administration/users/models/User.php';

    //Instantiate SB and Connect
    $database = new Database();

    if ($_SERVER["REQUEST_METHOD"] == 'GET') {

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
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');

            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 

        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
        $requiredPermissions = ['view_bsc'];
        $requiredModules = 'Performance';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        
        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }               

        //Get ID
        $bscInitiatives->id = clean_data($_GET['id']);

        if(!($bscInitiatives->is_initiative_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $bscInitiatives->read_single(); 

        //create array
        $user_arr = array(
            'id' => $bscInitiatives->id,
            'country_strategy_id' => $bscInitiatives->country_strategy_id,
			'pillar_id' => $bscInitiatives->pillar_id,
			'pillar_name' => $bscInitiatives->pillar_name,
			'bsc_parameter_name' => $bscInitiatives->bsc_parameter_name,
			'bsc_parameter_id' => $bscInitiatives->bsc_parameter_id,
            'initiative_id' => $bscInitiatives->initiative_id,
			'group_initiative' => $bscInitiatives->group_initiative,
			'pillar_id' => $bscInitiatives->pillar_id,
			'target' => $bscInitiatives->target,
			'value_impact' => $bscInitiatives->value_impact,
			'timeline' => $bscInitiatives->timeline,
			'measure' => $bscInitiatives->measure,
			'view_figure' => ( $bscInitiatives->measure === "Quantitative Parcentage" || $bscInitiatives->measure === "Qualitative") ? $bscInitiatives->figure . '%' :(( $bscInitiatives->measure === "Quantitative Financial" ) ? number_format($bscInitiatives->figure,2) : $bscInitiatives->figure),        
			'figure' => $bscInitiatives->figure,
			'weight' => $bscInitiatives->weight,
			'raw_score' => ( $bscInitiatives->measure === "Quantitative Parcentage" || $bscInitiatives->measure === "Qualitative") ? $bscInitiatives->raw_score . '%' : (($bscInitiatives->measure === "Quantitative Financial" ) ? number_format($bscInitiatives->raw_score,2) : $bscInitiatives->raw_score), 
			'target_score' => $bscInitiatives->target_score,
			'computed_score' => $bscInitiatives->computed_score,
			'created_by' => $bscInitiatives->created_by,
			'created_at' => $bscInitiatives->created_at,
			'updated_by' => $bscInitiatives->updated_by,
			'updated_at' => $bscInitiatives->updated_at
        );
        
        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $user_arr,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }