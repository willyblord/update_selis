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

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $country = $userDetails['country_id'];
    $department = $userDetails["department_id"];


    function budgetline_list($db, $country, $department)
    {

        $query = 'SELECT bc.name AS budget_category, bc.id AS budgetId
                    FROM budget b
                    LEFT JOIN budget_categories bc ON b.budget_category = bc.id
                    WHERE b.country = :country AND b.department = :department AND b.status IN ("active")
                ';

        //Prepare statement
        $stmt = $db->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':department', $department);


        //Execute Query
        $stmt->execute();

        $data = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $data[] = array(
                'id' => $budgetId,
                'budget_category' => $budget_category,
            );
        }

        return $data;
    }

    //query
    $output = budgetline_list($db, $country, $department);

    echo json_encode($output);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}