<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/Pettycash.php';
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
        $pettycash = new Pettycash($db);

        //Check jwt validation
        $user_details = $user->validate($_COOKIE["jwt"]);
        if($user_details===false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');

            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 
        if($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
        $pettycash->id = clean_data($_GET['id']);

        if(!($pettycash->is_request_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $pettycash->read_single();        

        //create array
        $user_arr = array(
            'id' => $pettycash->id,
            'refNo' => $pettycash->refNo,
			'visitDate' => $pettycash->visitDate,
			'category' => $pettycash->category,
			'budget_category' => $pettycash->budget_category,
			'budget_category_val' => $pettycash->budget_category_val,
			'providers' => $pettycash->providers,
			'providers_Val' => $pettycash->providers_Val,
			'customers' => $pettycash->customers,
			'customers_Val' => $pettycash->customers_Val,
			'transport' => $pettycash->transport,
			'transport_Val' => $pettycash->transport_Val,
			'accomodation' => $pettycash->accomodation,
			'accomodation_Val' => $pettycash->accomodation_Val,
			'meals' => $pettycash->meals,
			'meals_Val' => $pettycash->meals_Val,
			'otherExpenses' => $pettycash->otherExpenses,
			'otherExpenses_Val' => $pettycash->otherExpenses_Val,
			'afterClearance' => $pettycash->afterClearance,
			'afterClearance_Val' => $pettycash->afterClearance_Val,
			'charges' => $pettycash->charges,
			'charges_Val' => $pettycash->charges_Val,
			'requester_charges' => $pettycash->requester_charges,
			'requester_charges_Val' => $pettycash->requester_charges_Val,
			'partiallyDisbursed' => $pettycash->partiallyDisbursed,
			'partiallyDisbursed_Val' => $pettycash->partiallyDisbursed_Val,
			'partiallyRemaining' => $pettycash->partiallyRemaining,
			'partiallyRemaining_Val' => $pettycash->partiallyRemaining_Val,
			'totalAmount' => $pettycash->totalAmount,
			'totalAmount_Val' => $pettycash->totalAmount_Val,
			'totalUsed' => $pettycash->totalUsed,
			'totalUsed_Val' => $pettycash->totalUsed_Val,
			'air_departure_date' => $pettycash->air_departure_date,
			'air_return_date' => $pettycash->air_return_date,
			'checkin_date' => $pettycash->checkin_date,
			'checkout_date' => $pettycash->checkout_date,
			'phone' => $pettycash->phone,
			'bank_name' => $pettycash->bank_name,
			'cheque_number' => $pettycash->cheque_number,
			'description' => $pettycash->description,
			'requestBy' => $pettycash->requestBy,
			'requestDate' => $pettycash->requestDate,
			'receiptImage' => $pettycash->receiptImage,
			'additional_doc' => $pettycash->additional_doc,
			'status' => $pettycash->status,
			'hodDate' => $pettycash->hodDate,
			'hodApprove' => $pettycash->hodApprove,
			'financeDate' => $pettycash->financeDate,
			'financeApprove' => $pettycash->financeApprove,
			'financeReleaseDate' => $pettycash->financeReleaseDate,
			'financeRelease' => $pettycash->financeRelease,
			'managerDate' => $pettycash->managerDate,
			'managerApprove' => $pettycash->managerApprove,
			'gmdDate' => $pettycash->gmdDate,
			'gmdApprove' => $pettycash->gmdApprove,
			'gmdComment' => $pettycash->gmdComment,
			'clearanceDate' => $pettycash->clearanceDate,
			'clearedBy' => $pettycash->clearedBy,
			'clearanceDescription' => $pettycash->clearanceDescription,
			'clearSupervisorComment' => $pettycash->clearSupervisorComment,
			'suspendDate' => $pettycash->suspendDate,
			'suspendedBy' => $pettycash->suspendedBy,
			'ReturnDate' => $pettycash->ReturnDate,
			'ReturnedBy' => $pettycash->ReturnedBy,
			'returnReason' => $pettycash->returnReason,
			'rejectDate' => $pettycash->rejectDate,
			'rejectReason' => $pettycash->rejectReason,
			'amountGiven' => $pettycash->amountGiven,
			'diff' => $pettycash->diff,
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