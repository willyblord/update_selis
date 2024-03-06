<?php
//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../../../administration/users/models/User.php';

if ($_SERVER["REQUEST_METHOD"] == 'GET') {

    if (!isset($_COOKIE["jwt"])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please Login'
        ]);
        die();
    }

    //Instantiate SB and Connect
    $database = new Database();
    $db = $database->connect();

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

    if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {

        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }

    function categories_list($db)
    {

        //Select Query
        $query = ' SELECT id, name AS category, budget_category FROM cashcategories ';

        //Prepare statement
        $stmt = $db->prepare($query);

        //Execute Query
        $stmt->execute();

        $data = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $data[] = array(
                'id' => $id,
                'category' => $category,
                'budget_category' => $budget_category,
            );
        }

        return $data;
    }

    //query
    $output = categories_list($db);

    echo json_encode($output);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}