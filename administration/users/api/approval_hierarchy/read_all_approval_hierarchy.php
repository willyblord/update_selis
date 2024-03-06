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

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {

        if (!isset($_COOKIE["jwt"])) { 
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }      


        $db = $database->connect();
        //Instantiate Project object
        $user = new User($db);
        $approvalHierarchy = new ApprovalHierarchy($db);

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
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = ['manage_hierarchy'];
        $requiredModules = 'Administration';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }        

        
        //Get raw posted data
        //$data = json_decode(file_get_contents("php://input"));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
        
        $approvalHierarchy->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
        $approvalHierarchy->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
        $approvalHierarchy->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
        $approvalHierarchy->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
        $approvalHierarchy->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$approvalHierarchy->columnIndex]['data']) : "";
        $approvalHierarchy->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir'])  : "";
        $approvalHierarchy->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

        //Projects query
        $output = $approvalHierarchy->read_approval_hierarchy();

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }