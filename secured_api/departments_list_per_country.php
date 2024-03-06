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

        $my_country = $userDetails['country_id'];

        $query = 'SELECT DISTINCT d.category AS dptName, d.id AS deptId
                    FROM users u
                    LEFT JOIN department_categories d ON u.department = d.id
                    WHERE u.country = '.$my_country.'
                ';

        function departments_list($db, $query){

            //Prepare statement
            $stmt = $db->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $deptId,
                    'department_name' => $dptName,
                );
            }

            return $data;
        }

        //query
        $output = departments_list($db, $query);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>