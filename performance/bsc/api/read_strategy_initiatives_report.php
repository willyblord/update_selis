<?php
    
    //Headers
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/BSCInitiatives.php';
    include_once '../../../administration/users/models/User.php';


    //Instantiate SB and Connect
    $database = new Database();

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {

        if (!isset($_COOKIE["jwt"])) { 
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }    


        $db = $database->connect();
        //Instantiate object
        $user = new User($db);
        $bscInitiatives= new BSCInitiatives($db);

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

        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'HR_ROLE'];
        $requiredPermissions = ['view_report_bsc'];
        $requiredModules = 'Performance';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        
        // Get raw posted data
        // $data = json_decode(file_get_contents("php://input"));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        $country = clean_data($_POST['country']);
        $department = clean_data($_POST['department']);
        $threeyear_strategy = clean_data($_POST['threeyear_strategy']);
        $annual_year = clean_data($_POST['annual_year']);
        $DateFrom = clean_data($_POST['DateFrom']);
        $DateTo = clean_data($_POST['DateTo']);

        $bscInitiatives->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
        $bscInitiatives->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
        $bscInitiatives->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
        $bscInitiatives->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
        $bscInitiatives->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$bscInitiatives->columnIndex]['data']) : "";
        $bscInitiatives->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir'])  : "";
        $bscInitiatives->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';


        //pettycash query
        $output = $bscInitiatives->strategy_initiatives_report($country, $department, $threeyear_strategy, $annual_year, $DateFrom, $DateTo);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }