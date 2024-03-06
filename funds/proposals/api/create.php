<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Proposals.php';
include_once '../../../administration/users/models/User.php';

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
$proposal = new Proposals($db);

//Check jwt validation
$userDetails = $user->validate($_COOKIE["jwt"]);
if ($userDetails === false) {
    setcookie("jwt", null, -1);
    echo json_encode([
        'success' => false,
        'message' => $user->error
    ]);
    die();
}

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
$requiredPermissions = ['add_proposal'];
$requiredModules = 'Funds';

if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {

    echo json_encode([
        'success' => false,
        'message' => "Unauthorized Resource"
    ]);
    die();
}

//
$proposal->this_user = $userDetails['userId'];
$mycountry = $userDetails['country_id'];
$my_department = $userDetails["department_id"];


//get raw posted data
$data = json_decode(urldecode(file_get_contents("php://input")));
function clean_data($data)
{
    $data = trim($data);
    $data = strip_tags($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$errorMsg = '';
if (empty($_POST["subject"]) || $_POST["subject"] == "") {
    $errorMsg = "subject is required";
} else {
    $proposal->subject = clean_data($_POST["subject"]);
}
if (empty($_POST["proposal_date"]) || $_POST["proposal_date"] == "") {
    $errorMsg = "proposal_date is required";
} else {
    $proposal->proposal_date = clean_data($_POST["proposal_date"]);
}
if (empty($_POST["budget_line"]) || $_POST["budget_line"] == "") {
    $errorMsg = "budget_line is required";
} else {
    $proposal->budget_line = clean_data($_POST["budget_line"]);
}
if (empty($_POST["introduction"]) || $_POST["introduction"] == "") {
    $errorMsg = "introduction is required";
} else {
    $proposal->introduction = clean_data($_POST["introduction"]);
}
if (empty($_POST["objective"]) || $_POST["objective"] == "") {
    $errorMsg = "objective is required";
} else {
    $proposal->objective = clean_data($_POST["objective"]);
}
if (empty($_POST["onbehalf_of"]) || $_POST["onbehalf_of"] == "") {
    $errorMsg = "onbehalf_of is required";
} else {
    $proposal->onbehalf_of = clean_data($_POST["onbehalf_of"]);
}
if (empty($_POST["FTotal"]) || $_POST["FTotal"] == "") {
    $errorMsg = "onbehalf_of is required";
} else {
    $proposal->FTotal = clean_data($_POST["FTotal"]);
}
if (empty($_POST["additional_doc"]) || $_POST["additional_doc"] == "") {
    $errorMsg = "inter support doc is required";
} else {
    $proposal->additional_doc = clean_data($_POST["additional_doc"]);
}

if (
    (empty($_POST["item"]) || !array_filter($_POST['item'])) ||
    (empty($_POST["quantity"]) || !array_filter($_POST['quantity'])) ||
    (empty($_POST["price"]) || !array_filter($_POST['price'])) ||
    (empty($_POST["total"]) || !array_filter($_POST['total'])) ||
    (empty($_POST["supplier"]) || !array_filter(($_POST['supplier'])))

) {
    $errorMsg = "Please Inter Required Fields";
} else {
    // $count_role = 0;
    foreach ($_POST["item"] as $index => $item) {
        $item = $item;
        $quantity = $_POST["quantity"][$index];
        $price = $_POST["price"][$index];
        $total = $_POST["total"][$index];
        $supplier = $_POST["supplier"][$index];

        if (empty($item) || $item == "") {
            $errorMsg = 'Proposal Item Name is required';
        }
        if (empty($price) || $price == "") {
            $errorMsg = 'price required';
        }
        if (empty($total) || $total == "") {
            $errorMsg = 'total is required';
        }
        if (empty($supplier) || $supplier == "") {
            $errorMsg = 'supplier is required';
        }
    }
}
$proposal->item = $_POST["item"];
$proposal->quantity = $_POST["quantity"];
$proposal->price = $_POST["price"];
$proposal->total = $_POST["total"];
$proposal->supplier = $_POST["supplier"];
// end
$proposal->country = $mycountry;
$proposal->department = $my_department;
if ($_POST["operation"] == "Add") {
    $proposal->status = "pending";
    $proposal->location = "@ HOD";
    if ($errorMsg == '') {

        // $proposal->additional_doc = NULL;
        // if (is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
        //     $proposal->additional_doc = $proposal->upload_petty_doc();
        // }
        // create project
        $response = $proposal->createp();
        if ($response[0]) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Your Proposal is sent."
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
// put
elseif ($_POST["operation"] == "Edit") {
    $errorMsg = '';
    if (empty($_POST["id"])) {
        $errorMsg = "ID is required";
    } else {
        $proposal->id = clean_data($_POST['id']);
        if (!($proposal->is_proposal_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        }
    }
    if ($errorMsg == '') {
        $response = $proposal->update();
        if ($response) {
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
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}