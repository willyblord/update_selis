<?php 
    //Headers
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json'); 

    include_once '../include/Database.php';
    include_once '../administration/users/models/User.php'; 

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
        //Instantiate Project object
        $user = new User($db);
    
        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if ($userDetails === false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        }
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
        $requiredPermissions = [];
        $requiredModules = '';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $my_country = $userDetails['country'];        

        function status_list($db, $my_country){

            $query = 'SELECT DISTINCT status FROM cashrequests WHERE country = '.$my_country.' ';

            //Prepare statement
            $stmt = $db->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'status' => $status,
                );
            }

            return $data;
        }

        //query
        $output = status_list($db, $my_country);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>