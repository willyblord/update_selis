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

        function permissions_list($db){

            //Instantiate SB and Connect
            $database = new Database();
            $db = $database->connect();

            // Fetch permissions grouped by modules from the database
            $stmt = $db->prepare("
                        SELECT m.module_name, p.id AS permission_id, p.permission_name
                        FROM modules m
                        INNER JOIN module_permissions mp ON m.id = mp.module_id
                        INNER JOIN permissions p ON mp.permission_id = p.id
                        ORDER BY m.module_name, p.permission_name
                    ");
            $stmt->execute();
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group permissions by module
            $groupedPermissions = [];
            foreach ($permissions as $permission) {
                $moduleName = $permission['module_name'];
                unset($permission['module_name']); // Remove module_name from permission array

                // Check if the module exists in the groupedPermissions array
                if (!isset($groupedPermissions[$moduleName])) {
                    // If not, create a new array for the module
                    $groupedPermissions[$moduleName] = [];
                }
                // Add the permission to the module array
                $groupedPermissions[$moduleName][] = $permission;
            }

            return $groupedPermissions;
        }

        //query
        $output = permissions_list($db);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>