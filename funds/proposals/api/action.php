<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');


include_once '../../../include/Database.php';
include_once '../../../administration/users/models/User.php';
include_once '../models/Proposals.php';
include_once '../../pettycash/models/Budget.php';


//Instantiate SB and Connect
$database = new Database();
$db = $database->connect();
$proposal = new Proposals($db);
$budget = new Budget($db);
$user = new User($db);

//Check jwt validation
$user_details = $user->validate($_COOKIE["jwt"]);
if ($user_details === false) {
    setcookie("jwt", null, -1, '/');
    setcookie("jwt_r", null, -1, '/');
    echo json_encode([
        'success' => false,
        'message' => $user->error
    ]);
    die();
}

$this_user_id = $user_details['userId'];
$country_details = $user->get_country($user_details['country']);
$settings_salis_details = $user->get_settings_salis($user_details['country']);

$this_user_id = $user_details['userId'];

$mycountry = $user_details['country'];
$my_department = $user_details["department_val"];

$is_on_budget = $settings_salis_details['is_on_budget'];
$cashFinancesLimit = $settings_salis_details['pettycash_finance_limit'];
$cashCOOLimit = $settings_salis_details['pettycash_coo_limit'];

$db = $database->connect();
//Instantiate idea object
$user = new User($db);
$proposal = new Proposals($db);

//Check jwt validation
$user_details = $user->validate($_COOKIE["jwt"]);
if ($user_details === false) {
    setcookie("jwt", null, -1, '/');
    setcookie("jwt_r", null, -1, '/');
    echo json_encode([
        'success' => false,
        'message' => $user->error
    ]);
    die();
}

$proposal->this_user = $user_details['userId'];
$this_user_id = $user_details['userId'];

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


if (isset($data->operation) && $data->operation == 'cancel_req') {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
        $proposal->id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if (empty($data->cancelReason)) {
        $errorMsg = "ID is required";
    } else {
        // Set ID to update 
        $proposal->rejectReason = clean_data($data->cancelReason);
    }

    $proposal->status = "cancelled";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->cancel_request()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request cancelled"
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
// complete proposal
elseif (isset($data->operation) && $data->operation == 'complete_req') {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
        $proposal->id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "completed";
    $proposal->this_user = $this_user_id;
    if (
        $errorMsg == ''
    ) {
        //update idea
        if ($proposal->complete_request()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Completed"
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
} elseif (isset($data->operation) && $data->operation == 'delete') {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_coo'] != 1) {
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
        $proposal->id = clean_data($data->id);


        if (!($proposal->is_proposal_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Item Not Found'
            ]);
            die();
        }
    }

    if ($errorMsg == '') {
        //update idea
        if ($proposal->delete()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Deleted"
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
} elseif (isset($data->operation) && $data->operation == 'approve') {
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_hod'] != 1) {
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
        $proposal->id = clean_data($data->id);
    }
    $proposal->status = "@CM";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->activate()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Activated"
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
} elseif (isset($data->operation) && $data->operation == 'approvecof' || $data->operation == 'cfo_unsuspend') {
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_cof'] != 1) {
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
        $proposal->id = clean_data($data->id);
    }
    $proposal->status = "@GMD";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->activate()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Activated"
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
} elseif (isset($data->operation) && $data->operation == 'approvegmd' || $data->operation == 'gmd_unsuspend') {
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_cof'] != 1) {
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
        $proposal->id = clean_data($data->id);
    }
    $proposal->status = "@FINANCE";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->activate()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Activated"
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
} elseif (isset($data->operation) && $data->operation == 'reject') {

    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1 &&
        $user_details['can_be_cash_cm'] != 1 &&
        $user_details['can_be_cash_coo'] != 1 &&
        $user_details['can_be_cash_cof'] != 1 &&
        $user_details['can_be_cash_finance'] != 1 &&
        $user_details['can_be_cash_manager'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if (empty($data->rejectReason)) {
        $errorMsg = "Reason is required";
    } else {
        // Set ID to update 
        $proposal->rejectReason = clean_data($data->rejectReason);
    }

    $proposal->status = "rejected";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request Rejected"
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


// comment section hod
elseif (isset($data->operation) && $data->operation == 'comment') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    if (empty($data->comment)) {
        $errorMsg = "Comment is required";
    } else {
        // Set ID to update 
        $proposal->comment = clean_data($data->comment);
    }
    $proposal->status = "@CM";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->approve_comment()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Aproved"
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
// country Manager
elseif (isset($data->operation) && $data->operation == 'approvecm' || $data->operation == 'cm_unsuspend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_cm'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    if (isset($data->comment)) {
        $proposal->comment = clean_data($data->comment);
    } else {
        $proposal->comment = clean_data('');
    }
    $proposal->status = "@COO";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->approve_comment()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Aproved"
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
// end
// COO APPROVE Manager
elseif (isset($data->operation) && $data->operation == 'approveCOO' || $data->operation == 'coo_unsuspend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_coo'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    if (isset($data->comment)) {
        $proposal->comment = clean_data($data->comment);
    } else {
        $proposal->comment = clean_data('');
    }
    $proposal->status = "@CFO";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->approve_comment()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Aproved"
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
// COF APPROVE Manager
elseif (isset($data->operation) && $data->operation == 'approveCOF') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_coo'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    if (empty($data->comment)) {
        $proposal->comment = null;
    } else {
        // Set ID to update 
        $proposal->comment = clean_data($data->comment);
    }
    $proposal->status = "@GMD";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->approve_comment()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Aproved"
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
// end
elseif (isset($data->operation) && $data->operation == 'suspend') {

    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1 &&
        $user_details['can_be_cash_coo'] != 1 &&
        $user_details['can_be_cash_finance'] != 1 &&
        $user_details['can_be_cash_manager'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $request_id = clean_data($data->id);
        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "suspended";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->suspend_request()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Suspended"
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

// reverting 
elseif (isset($data->operation) && $data->operation == 'reject') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1 &&
        $user_details['can_be_cash_cm'] != 1 &&
        $user_details['can_be_cash_coo'] != 1 &&
        $user_details['can_be_cash_cof'] != 1 &&
        $user_details['can_be_cash_finance'] != 1 &&
        $user_details['can_be_cash_manager'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        $id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if (empty($data->rejectReason)) {
        $errorMsg = "Reason is required";
    } else {
        // Set ID to update 
        $proposal->rejectReason = clean_data($data->rejectReason);
    }

    $proposal->status = "rejected";
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request Rejected"
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
} elseif (isset($data->operation) && $data->operation == 'coo_amend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        // $id = clean_data($data->id); 

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "@returnedFromCOO";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Suspended"
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
} elseif (isset($data->operation) && $data->operation == 'finance_amend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_hod'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        // $id = clean_data($data->id); 

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "@returnedFromFINANCE";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Suspended"
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
} elseif (isset($data->operation) && $data->operation == 'cm_amend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_cm'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        // $id = clean_data($data->id); 

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    if (empty($data->returnReason)) {
        $errorMsg = "Reason is required";
    } else {
        // Set ID to update 
        $proposal->returnReason = clean_data($data->returnReason);
    }
    $proposal->status = "@returnedFromCM";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->returned_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Returned"
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
} elseif (isset($data->operation) && $data->operation == 'cof_amend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_cof'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        // $id = clean_data($data->id); 

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "@returnedFromCOF";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Reverted"
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
} elseif (isset($data->operation) && $data->operation == 'gmd_amend') {
    if (
        $user_details['can_be_super_user'] != 1 &&
        $user_details['can_be_cash_cof'] != 1
    ) {
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
        $proposal->id = clean_data($data->id);
        // $id = clean_data($data->id); 

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }
    $proposal->status = "@returnedFromGMD";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->reject_request()) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Proposal Returned"
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
} elseif (isset($data->operation) && $data->operation == 'activate_budget') {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 && $user_details['can_be_cash_coo'] != 1) {
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
        $budget->id = clean_data($data->id);

        if (!($budget->is_budget_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Budget Not Found'
            ]);
            die();
        }

        if (($budget->is_budget_active())) {
            echo json_encode([
                'success' => false,
                'message' => 'Active Budget Cannot be Re-ativated'
            ]);
            die();
        }
    }

    $budget->status = "active";
    $budget->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($budget->activate_budget()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Budget Activated"
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
} elseif (isset($data->operation) && ($data->operation == 'myproposalResend')) {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
        $proposal->id = clean_data($data->id);
        $request_id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if ($proposal->get_single_request_details($request_id)) {
        $rowReq = $proposal->get_single_request_details($request_id);
    } else {
        $errorMsg = 'The proposal ID is unkown';
    }
    if ($rowReq['status'] == '@returnedFromHOD') {
        $proposal->status = "@CM";
    } elseif ($rowReq['status'] == '@returnedFromCOO') {
        $proposal->status = "@COO";
    } elseif ($rowReq['status'] == '@returnedFromGMD') {
        $proposal->status = "@GMD";
    } elseif ($rowReq['status'] == '@returnedFromCOF') {
        $proposal->status = "@CFO";
    } elseif ($rowReq['status'] == '@returnedFromFINANCE') {
        $proposal->status = "@FINANCE";
    } elseif ($rowReq['status'] == '@returnedFromCM') {
        $proposal->status = "@CM";
    }
    $proposal->this_user = $this_user_id;

    if ($errorMsg == '') {
        //update idea
        if ($proposal->activate()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request Re-submitted"
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
} elseif (isset($data->operation) && ($data->operation == 'fina_disburse_cheque')) {
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
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
        $proposal->id = clean_data($data->id);
        $request_id = clean_data($data->id);
        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if (empty($data->bank_name)) {
        $errorMsg = 'Bank Name is required';
    } else {
        $proposal->bank_name = clean_data($data->bank_name);
    }
    if (empty($data->cheque_number)) {
        $errorMsg = 'Cheque Number is required';
    } else {
        $proposal->cheque_number = clean_data($data->cheque_number);
    }

    if ($proposal->get_single_request_details($request_id)) {
        $rowReq = $proposal->get_single_request_details($request_id);
    } else {
        $errorMsg = 'The request ID is unkown';
    }

    if ($is_on_budget == 1) {
        if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) {
            $errorMSG = 'The category selected is not mapped to the budget';
        } else {
            $budget_cat_id = $rowReq['budget_category'];
        }
    }
    if ($is_on_budget == 1) {
        if (isset($budget_cat_id)) {
            $rowBC = $proposal->get_depart_cat_budget($mycountry, $req_department, $budget_cat_id, $remaining_amount);
            $remain = $rowBC['remaining_amount'];
            if ($remain < $checkTotalAmount) {
                $errorMsg = 'There is not enough budget based on the category selected.';
            }
        } else {
            $errorMsg = 'The category selected is not mapped to the budget';
        }
    }
    $proposal->status = "approved";
    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //update idea
        if ($proposal->finance_disburse_cheque_request()) {

            if ($is_on_budget == 1) {
                //Get Budget Details
                $rowBC = $proposal->get_depart_cat_budget($mycountry, $req_department, $budget_cat_id, $remaining_amount);
                $used_budg = $rowBC['used_amount'];
                $avail_budg = $rowBC['remaining_amount'];
                $budget_id = $rowBC['id'];
                $budg_to_remain = $avail_budg - $checkTotalAmount;
                $budg_used_increased = $used_budg + $checkTotalAmount;
                $proposal->update_budget($budg_used_increased, $budg_to_remain, $budget_id);
            }
            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request Re-submitted"
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
} elseif (isset($data->operation) && ($data->operation == 'fina_disburse' || $data->operation == 'finance_unsuspend')) {

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
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
        $proposal->id = clean_data($data->id);
        $request_id = clean_data($data->id);

        if (!($proposal->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Request Not Found'
            ]);
            die();
        }
    }

    if ($proposal->get_single_request_details($request_id)) {
        $rowReq = $proposal->get_single_request_details($request_id);
    } else {
        $errorMsg = 'The request ID is unkown';
    }


    $checkReqStatus = $rowReq["status"];
    $checkPreviousCharges = $rowReq["charges"];

    $charges = 0;
    if (isset($data->charges) && $data->charges != "") {
        $charges = clean_data($data->charges);
    }



    // $totalCalculatedNeeded = $checkTotalAmount + $charges;
    // if ($checkReqStatus == "partiallyDisbursed") {
    //     $partiallyRemains = $partiallyRemains;
    //     $totalCalculatedNeeded = $partiallyRemains + $charges;
    // }

    // $rowBalance = $proposal->get_finance_balance($mycountry);
    // if ($rowBalance) {
    //     $balanceToSubtract = $rowBalance['amount'];
    //     if ($rowBalance['amount'] <= 0) {
    //         $errorMsg = 'You do not have funds to disburse, Please recharge your account first.';
    //     }
    // }

    $proposal->this_user = $this_user_id;
    if ($errorMsg == '') {
        //SUBTRACT MONEY
        $proposal->status = "approved";
        $msg = 'approved and disbursed';
        if ($proposal->finance_disburse_request()) {

            echo json_encode(
                array(
                    "success" => true,
                    "message" => "Request Approved"
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
