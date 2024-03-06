<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Pettycash.php';  
        include_once '../models/Budget.php';  

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
        //Instantiate idea object
        $user = new User($db);
        $pettycash= new Pettycash($db);
        $budget = new Budget($db);

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
        $this_user_id = $user_details['userId'];

        $country_details = $user->get_country($user_details['country']);
        $settings_salis_details= $user->get_settings_salis($user_details['country']);

        $this_user_id = $user_details['userId'];
        $sender = $this_user_id;
        $reply_email_to = $user_details['email'];
        $reply_name = $user_details['name'];
        $reply_surname = $user_details['surname']; 

        $mycountry = $user_details['country'];
        $my_department = $user_details["department_val"];

        $is_on_budget = $settings_salis_details['is_on_budget'];
        $cashFinancesLimit = $settings_salis_details['pettycash_finance_limit'];
        $cashCOOLimit = $settings_salis_details['pettycash_coo_limit'];

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
        
        // $user->this_user = $_SESSION['userIdentification'];

        if( isset($data->operation) && $data->operation == 'cancel_req') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
                $pettycash->id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->rejectReason = clean_data($data->cancelReason); 
            } 

            $pettycash->status = "cancelled";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->cancel_request()) {

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
        elseif( isset($data->operation) && $data->operation == 'complete_req') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
                $pettycash->id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            $pettycash->status = "completed";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->complete_request()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Completed"
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

        } elseif( isset($_POST["operation"]) && $_POST["operation"] == 'clear_req') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($_POST["id"])) {
				$errorMsg = 'Request ID is required';  
			} else {  
				$pettycash->id = clean_data($_POST["id"]); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
			} 

			if (empty($_POST["amountUsed"])) {  
                $errorMsg = 'Amount is missing';  
            } else {  
                $pettycash->totalUsed = clean_data($_POST["amountUsed"]); 
            }
			
			if (empty($_POST["reconciliation_comment"])) {  
                $errorMsg = 'Comment is missing';  
            } else {  
                $pettycash->clearanceDescription = clean_data($_POST["reconciliation_comment"]); 
            }

            if(is_uploaded_file($_FILES['receipt']['tmp_name'])) {
                $supported_files = array('application/pdf', 'image/jpeg', 'image/jpg', 'image/png');  
                if (!in_array($_FILES['receipt']['type'], $supported_files)) {
                    $errorMsg = 'Only PDF, image files are allowed';
                }
                elseif($_FILES['receipt']['size'] >= 2097152) {
                    $errorMsg = 'File too large. File must be less than 2MB';
                }
            }

            
            $pettycash->status = "clearing";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {

                $pettycash->receiptImage = NULL;
                if(is_uploaded_file($_FILES['receipt']['tmp_name'])) {
                    $pettycash->receiptImage = $pettycash->upload_petty_receipt();
                }

                //update idea
                if($pettycash->reconciliation_request()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Reconciliation submitted"
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
        elseif( isset($_POST["operation"]) && $_POST["operation"] == 'fina_clearing') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($_POST["id"])) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $pettycash->id = clean_data($_POST["id"]); 
                $request_id = clean_data($_POST["id"]); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            if (empty($_POST["reconciliation_comment"])) {  
                $errorMsg = 'Comment is missing';  
            } else {  
                $pettycash->clearSupervisorComment = clean_data($_POST["reconciliation_comment"]); 
            }            

            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}

            if($is_on_budget == 1) {
				if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) { 
					$errorMsg = 'The category selected is not mapped to the budget';  
				} else {  
					$budget_cat_id = $rowReq['budget_category'];
				} 
			}

			$req_department = $rowReq["department"];					
			$totusd = $rowReq["totalUsed"];
			$totgiv = $rowReq['totalAmount'];
			$diffClr = $totusd - $totgiv;

            $previousCharges = 0; $charges = 0; $requesterCharges = 0;
            if(isset($rowReq["charges"]) && $rowReq["charges"] != ""){
				$previousCharges = $rowReq["charges"];
			}
			if(isset($_POST["charges"]) && $_POST["charges"] != ""){
				$charges = $_POST["charges"];
			}
			if(isset($rowReq["requester_charges"]) && $rowReq["requester_charges"] != ""){
				$requesterCharges = $rowReq["requester_charges"];
			}
            $newCharges = $previousCharges + $charges;			
			$totalTotCleared = $totgiv + $diffClr + $requesterCharges + $previousCharges + $charges;

			$diffAndCharges = $diffClr + $charges;

            $pettycash->totalAmount = $totalTotCleared;
            $pettycash->charges = $newCharges;
            $pettycash->afterClearance = $diffClr;
            $pettycash->country = $mycountry;

            if( ($is_on_budget == 1) && ($diffAndCharges > 0)) {	
				if(isset($budget_cat_id)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
					$remain = $rowBC['remaining_amount'];
					if($remain < $diffAndCharges) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'The category selected is not mapped to the budget'; 
				}
			}

            $balanceToSubtract = 0;
            $rowBal = $pettycash->get_finance_balance($mycountry); 
            if($rowBal)	{
                $balanceToSubtract = $rowBal['amount'];				
                if( $rowBal['amount'] < $diffAndCharges ) {
                    $errorMsg = 'You do not have enough funds to disburse the extra spent money, Please recharge your account first.'; 					
                }
            }

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //SUBTRACT MONEY
                $remainingBalance = $balanceToSubtract - $diffAndCharges;
                $pettycash->finaRemainingBalance = $remainingBalance;
                $pettycash->status = "cleared";

                if($pettycash->approve_reconciliation()) {

                    if($is_on_budget == 1) {								
                        //Get Budget Details
                        $rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
                        $used_budg = $rowBC['used_amount'];
                        $avail_budg = $rowBC['remaining_amount'];
                        $budget_id = $rowBC['id'];

                        $budg_to_remain = $avail_budg - $diffAndCharges;
                        $budg_used_increased = $used_budg + $diffAndCharges;
                        $pettycash->update_budget($budg_used_increased,$budg_to_remain,$budget_id);
                    }

                    // Email Notification Logic
                    $ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
                    $getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
                                                LEFT JOIN users u ON ca.requestBy = u.userId
                                                WHERE ca.id = '.$request_id.' 
                                            ');
                    $getRefNo->execute();
                    if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
                        $ref_No = $rowRef['refNo'];
                        $amount = $rowRef['totalAmount'];
                        $to_name = $rowRef['name'];
                        $to_surname = $rowRef['surname'];
                        $email_to =$rowRef['email'];
                        $requestBy =$rowRef['requestBy'];
                    }
                    $amount = $amount.' '.$country_details['country_currency'];

                    if($requestBy != '')
                    {
                        $title = 'PETTY CASH: Your Reconciliation Request is approved | SERIS';

                        $sender = $this_user_id;
                        $cmt = $_POST["reconciliation_comment"];

                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                has been cleared by <b>'.$reply_name.' '.$reply_surname.'</b>.<br/>
                                
                                <b>Comment:</b> '.$cmt.' <br/>';


                        $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                        
                    }

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
        elseif( isset($_POST["operation"]) && $_POST["operation"] == 'fina_cancel_clearing') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($_POST["id"])) {
				$errorMsg = 'Request ID is required';  
			} else {  
				$pettycash->id = clean_data($_POST["id"]); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
			} 
			
			if (empty($_POST["reconciliation_comment"])) {  
                $errorMsg = 'Comment is missing';  
            } else {  
                $pettycash->clearSupervisorComment = clean_data($_POST["reconciliation_comment"]); 
            }
            
            $pettycash->status = "clearDenied";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {

                //update idea
                if($pettycash->reconciliation_deny()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Reconciliation Denied"
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
        elseif( isset($data->operation) && ( $data->operation == 'hod_approve' || $data->operation == 'hod_unsuspend') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_hod'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}

            if($is_on_budget == 1) {
				if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) { 
					$errorMsg = 'The category selected is not mapped to the budget';  
				} else {  
					$budget_cat_id = $rowReq['budget_category'];
				} 
			}

            $checkTotalAmount = $rowReq["totalAmount"];

            if($is_on_budget == 1) {	
				if(isset($budget_cat_id)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$my_department,$budget_cat_id);
					$remain = $rowBC['remaining_amount'];
					if($remain < $checkTotalAmount) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'The category selected is not mapped to the budget'; 
				}
			}

            if( $checkTotalAmount > $cashFinancesLimit){
                $pettycash->status = "@COO";
            }else {
                $pettycash->status = "@FinanceFromHOD";
            }

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->hod_approve_request()) {

                    // Email Notification Logic
					if( $checkTotalAmount > $cashFinancesLimit){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_coo = 1 AND users.status = 'active'
										");
					}else {
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_finance = 1 AND users.status = 'active'
										");
					}
					$getFina->bindParam(':ctr', $mycountry);
					$getFina->execute();
					$countFina = $getFina->rowCount();
					
					if($countFina > 0){
						$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
						$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
													LEFT JOIN users u ON ca.requestBy = u.userId
													WHERE ca.id = '.$request_id.' 
												');
						$getRefNo->execute();
						if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
							$ref_No = $rowRef['refNo'];
							$amount = $rowRef['totalAmount'];
							$to_name = $rowRef['name'];
							$to_surname = $rowRef['surname'];
							$email_to =$rowRef['email'];
							$requestBy =$rowRef['requestBy'];
						}
						$amount = $amount.' '.$country_details['country_currency'];

						if($requestBy != '')
						{
							if( $checkTotalAmount > $cashFinancesLimit){
								$title = 'PETTY CASH: Your Request is Updated | SERIS';
								$msg = 'it is sent to the COO/Country Manager for further approve';
							}else {
								$title = 'PETTY CASH: Your Request is Ready For Disbursement | SERIS';
								$msg = 'it is ready for Finance to disburse';
							}

                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

                            $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
							
						}

						while($rowFina = $getFina->fetch(PDO::FETCH_ASSOC))
						{
							$myFina = $rowFina["userId"];
							$email_to1 = $rowFina["email"]; 
							$to_name1 = $rowFina["name"]; 

							if( $checkTotalAmount > $cashFinancesLimit){
								$title = 'PETTY CASH: A Request To Approve | SERIS';
								$msg = 'it needs your approval';
							}else {
								$title = 'PETTY CASH: A Request Ready To Disburse | SERIS';
								$msg = 'it is ready for Finance to disburse';
							}

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

							if($myFina != ''){
                                $pettycash->save_email( $email_to1, $to_name1, $reply_email_to, $reply_name, $title, $message, $sender );
							}
						}
					}

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
        elseif( isset($data->operation) && ( $data->operation == 'fina_disburse' || $data->operation == 'finance_unsuspend') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 
            
            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}

            if($is_on_budget == 1) {
				if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) { 
					$errorMsg = 'The category selected is not mapped to the budget';  
				} else {  
					$budget_cat_id = $rowReq['budget_category'];
				} 
			}

            $checkTotalAmount = $rowReq["totalAmount"];
			$checkReqStatus = $rowReq["status"];	
			$checkPreviousCharges = $rowReq["charges"];
			$partiallyRemains = $rowReq["partiallyRemaining"];	
			$req_department = $rowReq["department"];	

            $charges = 0;	 
			if(isset($data->charges) && $data->charges != ""){
				$charges = clean_data($data->charges);
			}			
			$totAmountUpdated = $checkTotalAmount + $charges;
			$chargesUpdated = $checkPreviousCharges + $charges;

            $pettycash->totalAmount = $totAmountUpdated;
            $pettycash->charges = $chargesUpdated;
            $pettycash->country = $mycountry;

            if($is_on_budget == 1) {	
				if(isset($budget_cat_id)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
					$remain = $rowBC['remaining_amount'];
					if($remain < $totAmountUpdated) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'The category selected is not mapped to the budget'; 
				}
			}

            $totalCalculatedNeeded = $checkTotalAmount + $charges;
            if($checkReqStatus == "partiallyDisbursed") {
                $partiallyRemains = $partiallyRemains;
                $totalCalculatedNeeded = $partiallyRemains + $charges;
            }

            $rowBalance = $pettycash->get_finance_balance($mycountry);
            if($rowBalance)	{
                $balanceToSubtract = $rowBalance['amount'];				
                if( $rowBalance['amount'] <= 0 ) {
                    $errorMsg = 'You do not have funds to disburse, Please recharge your account first.'; 					
                }
            }

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //SUBTRACT MONEY
                $remainingBalance = $balanceToSubtract - $totalCalculatedNeeded;
                $pettycash->finaRemainingBalance = $remainingBalance;
                $pettycash->status= "approved";
                $msg = 'approved and disbursed';
                $pettycash->partiallyDisbursed = NULL;
                $pettycash->partiallyRemaining = NULL;

                //IF AMOUNT IS LESS THAN FINANCE BAL, USE AVAILABLE BAL
                if( $rowBalance['amount'] < $totalCalculatedNeeded )
                {
                    $remainingBalance = $balanceToSubtract - $balanceToSubtract;
                    $pettycash->finaRemainingBalance = $remainingBalance;
                    $pettycash->status= "partiallyDisbursed";
                    $msg = 'approved and partially disbursed';
                    $pettycash->partiallyDisbursed = $balanceToSubtract;
                    $partiallyDisbursed = $balanceToSubtract;
                    $pettycash->partiallyRemaining = $totalCalculatedNeeded - $partiallyDisbursed;
                }

                if($pettycash->finance_disburse_request()) {

                    if($is_on_budget == 1) {								
                        //Get Budget Details
                        $rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
                        $used_budg = $rowBC['used_amount'];
                        $avail_budg = $rowBC['remaining_amount'];
                        $budget_id = $rowBC['id'];

                        $budg_to_remain = $avail_budg - $totalCalculatedNeeded;
                        $budg_used_increased = $used_budg + $totalCalculatedNeeded;
                        $pettycash->update_budget($budg_used_increased,$budg_to_remain,$budget_id);
                    }

                    // Email Notification Logic
                    $ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
                    $getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
                                                LEFT JOIN users u ON ca.requestBy = u.userId
                                                WHERE ca.id = '.$request_id.' 
                                            ');
                    $getRefNo->execute();
                    if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
                        $ref_No = $rowRef['refNo'];
                        $amount = $rowRef['totalAmount'];
                        $to_name = $rowRef['name'];
                        $to_surname = $rowRef['surname'];
                        $email_to =$rowRef['email'];
                        $requestBy =$rowRef['requestBy'];
                    }
                    $amount = $amount.' '.$country_details['country_currency'];

                    if($requestBy != '')
                    {
                        $title = 'PETTY CASH: Your Request is approved | SERIS';

                        $sender = $this_user_id;

                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                has been '.$msg.' by <b>'.$reply_name.' '.$reply_surname.'</b>. </p><br/>';


                        $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                        
                    }

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
        elseif( isset($data->operation) && ($data->operation == 'fina_disburse_cheque') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
				$pettycash->bank_name = clean_data($data->bank_name); 
			} 

			if (empty($data->cheque_number)) { 
				$errorMsg = 'Cheque Number is required';  
			} else {  
				$pettycash->cheque_number = clean_data($data->cheque_number); 
			}

            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}

            if($is_on_budget == 1) {
				if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) { 
					$errorMSG = 'The category selected is not mapped to the budget';  
				} else {  
					$budget_cat_id = $rowReq['budget_category'];
				} 
			}

            $checkTotalAmount = $rowReq["totalAmount"];	
			$checkReqStatus = $rowReq["status"];	
			$checkPreviousCharges = $rowReq["charges"];
			$partiallyRemains = $rowReq["partiallyRemaining"];	
			$req_department = $rowReq["department"];	

            if($is_on_budget == 1) {
				if(isset($budget_cat_id)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
					$remain = $rowBC['remaining_amount'];
					if($remain < $checkTotalAmount) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'The category selected is not mapped to the budget'; 
				}
			}
            
            $pettycash->status = "approved";

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->finance_disburse_cheque_request()) {

                    if($is_on_budget == 1) {								
                        //Get Budget Details
                        $rowBC = $pettycash->get_depart_cat_budget($mycountry,$req_department,$budget_cat_id);
                        $used_budg = $rowBC['used_amount'];
                        $avail_budg = $rowBC['remaining_amount'];
                        $budget_id = $rowBC['id'];

                        $budg_to_remain = $avail_budg - $checkTotalAmount;
                        $budg_used_increased = $used_budg + $checkTotalAmount;
                        $pettycash->update_budget($budg_used_increased,$budg_to_remain,$budget_id);
                    }

                    // Email Notification Logic
                    $ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
                    $getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
                                                LEFT JOIN users u ON ca.requestBy = u.userId
                                                WHERE ca.id = '.$request_id.' 
                                            ');
                    $getRefNo->execute();
                    if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
                        $ref_No = $rowRef['refNo'];
                        $amount = $rowRef['totalAmount'];
                        $to_name = $rowRef['name'];
                        $to_surname = $rowRef['surname'];
                        $email_to =$rowRef['email'];
                        $requestBy =$rowRef['requestBy'];
                    }
                    $amount = $amount.' '.$country_details['country_currency'];

                    if($requestBy != '')
                    {
                        $title = 'PETTY CASH: Your Request is approved | SERIS';

                        $sender = $this_user_id;

                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                has been approved and disbursed by <b>'.$reply_name.' '.$reply_surname.'</b>. </p><br/>';


                        $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                        
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

        } 
        elseif( isset($data->operation) && ( $data->operation == 'coo_approve' || $data->operation == 'coo_unsuspend') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_coo'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}

            if($is_on_budget == 1) {
				if (!isset($rowReq['budget_category']) && $rowReq['budget_category'] == NULL) { 
					$errorMsg = 'The category selected is not mapped to the budget';  
				} else {  
					$budget_cat_id = $rowReq['budget_category'];
				} 
			}

            $checkTotalAmount = $rowReq["totalAmount"];

            if($is_on_budget == 1) {	
				if(isset($budget_cat_id)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$my_department,$budget_cat_id);
					$remain = $rowBC['remaining_amount'];
					if($remain < $checkTotalAmount) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'The category selected is not mapped to the budget'; 
				}
			}

            if( $checkTotalAmount > $cashCOOLimit){
                $pettycash->status = "@GMDfromCOO";
            }else {
                $pettycash->status = "@FinanceFromCOO";
            }

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->coo_approve_request()) {

                    // Email Notification Logic
					if( $checkTotalAmount > $cashCOOLimit){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE privileges.can_be_cash_manager = 1 AND users.status = 'active'
										");
					}else {
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_finance = 1 AND users.status = 'active'
										");
					    $getFina->bindParam(':ctr', $mycountry);
					}
					$getFina->execute();
					$countFina = $getFina->rowCount();
					
					if($countFina > 0){
						$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
						$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
													LEFT JOIN users u ON ca.requestBy = u.userId
													WHERE ca.id = '.$request_id.' 
												');
						$getRefNo->execute();
						if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
							$ref_No = $rowRef['refNo'];
							$amount = $rowRef['totalAmount'];
							$to_name = $rowRef['name'];
							$to_surname = $rowRef['surname'];
							$email_to =$rowRef['email'];
							$requestBy =$rowRef['requestBy'];
						}
						$amount = $amount.' '.$country_details['country_currency'];

						if($requestBy != '')
						{
							if( $checkTotalAmount > $cashCOOLimit){
								$title = 'PETTY CASH: Your Request is Updated | SERIS';
								$msg = 'it is sent to the GMD for further approval';
							}else {
								$title = 'PETTY CASH: Your Request is Ready For Disbursement | SERIS';
								$msg = 'it is ready for Finance for disbursement';
							}

                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

                            $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
							
						}

						while($rowFina = $getFina->fetch(PDO::FETCH_ASSOC))
						{
							$myFina = $rowFina["userId"];
							$email_to1 = $rowFina["email"]; 
							$to_name1 = $rowFina["name"]; 

							if( $checkTotalAmount > $cashCOOLimit){
								$title = 'PETTY CASH: A Request To Approve | SERIS';
								$msg = 'it needs your approval';
							}else {
								$title = 'PETTY CASH: A Request Ready To Disburse | SERIS';
								$msg = 'it is ready for Finance to disburse';
							}

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

							if($myFina != ''){
                                $pettycash->save_email( $email_to1, $to_name1, $reply_email_to, $reply_name, $title, $message, $sender );
							}
						}
					}

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
        elseif( isset($data->operation) && ( $data->operation == 'gmd_approve' || $data->operation == 'gmd_unsuspend') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_manager'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            $pettycash->status = "@FinanceFromGMD";

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->gmd_approve_request()) {

                    // Email Notification Logic
					$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_finance = 1 AND users.status = 'active'
										");
					$getFina->bindParam(':ctr', $mycountry);
					$getFina->execute();
					$countFina = $getFina->rowCount();
					
					if($countFina > 0){
						$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
						$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
													LEFT JOIN users u ON ca.requestBy = u.userId
													WHERE ca.id = '.$request_id.' 
												');
						$getRefNo->execute();
						if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
							$ref_No = $rowRef['refNo'];
							$amount = $rowRef['totalAmount'];
							$to_name = $rowRef['name'];
							$to_surname = $rowRef['surname'];
							$email_to =$rowRef['email'];
							$requestBy =$rowRef['requestBy'];
						}
						$amount = $amount.' '.$country_details['country_currency'];

						if($requestBy != '')
						{
							$title = 'PETTY CASH: Your Request is Ready For Disbursement | SERIS';
                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and it is ready at Finance for disbursement.
                                    ';

                            $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
							
						}

						while($rowFina = $getFina->fetch(PDO::FETCH_ASSOC))
						{
							$myFina = $rowFina["userId"];
							$email_to1 = $rowFina["email"]; 
							$to_name1 = $rowFina["name"]; 

							$title = 'PETTY CASH: A Request Ready To Disburse | SERIS';

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and it is ready at Finance to disburse.
                                    ';

							if($myFina != ''){
                                $pettycash->save_email( $email_to1, $to_name1, $reply_email_to, $reply_name, $title, $message, $sender );
							}
						}
					}

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
        elseif( isset($data->operation) && ($data->operation == 'resend_req') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_cash_requests'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            if($pettycash->get_single_request_details($request_id)){
				$rowReq = $pettycash->get_single_request_details($request_id);
			} else {
				$errorMsg = 'The request ID is unkown';
			}
            
            if($rowReq['status'] == 'returnedFromHOD'){
                $pettycash->status = "@HOD";
            } elseif($rowReq['status'] == 'returnedFromFinance'){
                $pettycash->status = "@FinanceFromHOD";
            } elseif($rowReq['status'] == 'returnedFromCOO'){
                $pettycash->status = "@COO";
            } elseif($rowReq['status'] == 'returnedFromGMD'){
                $pettycash->status = "@GMDfromCOO";
            }

            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->hod_approve_request()) {

                    // Email Notification Logic
					if($rowReq['status'] == 'returnedFromHOD'){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND users.department =:department AND privileges.can_be_cash_hod = 1 AND users.status = 'active'
										");
					    $getFina->bindParam(':ctr', $mycountry);
                        $getFina->bindParam(':department', $my_department);

					} elseif($rowReq['status'] == 'returnedFromFinance'){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_finance = 1 AND users.status = 'active'
										");
					    $getFina->bindParam(':ctr', $mycountry);

					} elseif($rowReq['status'] == 'returnedFromCOO'){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_coo = 1 AND users.status = 'active'
										");
					    $getFina->bindParam(':ctr', $mycountry);

					} elseif($rowReq['status'] == 'returnedFromGMD'){
						$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE privileges.can_be_cash_manager = 1 AND users.status = 'active'
										");
					}
					$getFina->execute();
					$countFina = $getFina->rowCount();
					
					if($countFina > 0){
						$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
						$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
													LEFT JOIN users u ON ca.requestBy = u.userId
													WHERE ca.id = '.$request_id.' 
												');
						$getRefNo->execute();
						if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
							$ref_No = $rowRef['refNo'];
							$amount = $rowRef['totalAmount'];
							$to_name = $rowRef['name'];
							$to_surname = $rowRef['surname'];
							$email_to =$rowRef['email'];
							$requestBy =$rowRef['requestBy'];
						}
						$amount = $amount.' '.$country_details['country_currency'];

						while($rowFina = $getFina->fetch(PDO::FETCH_ASSOC))
						{
							$myFina = $rowFina["userId"];
							$email_to1 = $rowFina["email"]; 
							$to_name1 = $rowFina["name"]; 

							$title = 'PETTY CASH: Amended Request | SERIS';

                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has resent the amended request ('.$ref_No.') of <b>'.$amount.'</b> 
                                            and it needs your approval.
                                    ';

							if($myFina != ''){
                                $pettycash->save_email( $email_to1, $to_name1, $reply_email_to, $reply_name, $title, $message, $sender );
							}
						}
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

        } 
        elseif( isset($data->operation) && $data->operation == 'reject') {

            if($user_details['can_be_super_user'] != 1 && 
                $user_details['can_be_cash_hod'] != 1 &&
                $user_details['can_be_cash_coo'] != 1 &&
                $user_details['can_be_cash_finance'] != 1 &&
                $user_details['can_be_cash_manager'] != 1
            )
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->rejectReason = clean_data($data->rejectReason); 
            } 

            $pettycash->status = "rejected";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->reject_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
                        $rejectReason = $data->rejectReason;
						$title = 'PETTY CASH: Your Request is rejected | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been rejected by <b>'.$reply_name.' '.$reply_surname.'</b>.
                                    <br><br><u>The reason:<u>
                                    <br><b>'.$rejectReason.'</b>
                                ';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

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
        elseif( isset($data->operation) && $data->operation == 'hod_amend') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_hod'] != 1 )
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->returnReason = clean_data($data->returnReason); 
            } 

            $pettycash->status = "returnedFromHOD";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->amend_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
                        $returnReason = $data->returnReason;
						$title = 'PETTY CASH: Your Request is returned | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been returned by <b>'.$reply_name.' '.$reply_surname.' for amendment</b>.
                                    <br><br><u>The reason:<u>
                                    <br><b>'.$returnReason.'</b>
                                ';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Returned"
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
        elseif( isset($data->operation) && $data->operation == 'coo_amend') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_coo'] != 1 )
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->returnReason = clean_data($data->returnReason); 
            } 

            $pettycash->status = "returnedFromCOO";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->amend_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
                        $returnReason = $data->returnReason;
						$title = 'PETTY CASH: Your Request is returned | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been returned by <b>'.$reply_name.' '.$reply_surname.' for amendment</b>.
                                    <br><br><u>The reason:<u>
                                    <br><b>'.$returnReason.'</b>
                                ';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Returned"
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
        elseif( isset($data->operation) && $data->operation == 'finance_amend') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 )
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->returnReason = clean_data($data->returnReason); 
            } 

            $pettycash->status = "returnedFromFinance";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->amend_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
                        $returnReason = $data->returnReason;
						$title = 'PETTY CASH: Your Request is returned | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been returned by <b>'.$reply_name.' '.$reply_surname.' for amendment</b>.
                                    <br><br><u>The reason:<u>
                                    <br><b>'.$returnReason.'</b>
                                ';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Returned"
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
        elseif( isset($data->operation) && $data->operation == 'gmd_amend') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_manager'] != 1 )
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
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
                $pettycash->returnReason = clean_data($data->returnReason); 
            } 

            $pettycash->status = "returnedFromGMD";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->amend_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
                        $returnReason = $data->returnReason;
						$title = 'PETTY CASH: Your Request is returned | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been returned by <b>'.$reply_name.' '.$reply_surname.' for amendment</b>.
                                    <br><br><u>The reason:<u>
                                    <br><b>'.$returnReason.'</b>
                                ';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Returned"
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
        elseif( isset($data->operation) && ( $data->operation == 'fina_higher_level') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 
            
            $pettycash->status = "@COO";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->sendToCOO()) {

                    // Email Notification Logic
					$getFina=$db->prepare("SELECT users.* FROM users 
											LEFT JOIN privileges ON users.userId = privileges.userId
											WHERE users.country =:ctr AND privileges.can_be_cash_coo = 1 AND users.status = 'active'
										");
					$getFina->bindParam(':ctr', $mycountry);
					$getFina->execute();
					$countFina = $getFina->rowCount();
					
					if($countFina > 0){
						$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
						$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
													LEFT JOIN users u ON ca.requestBy = u.userId
													WHERE ca.id = '.$request_id.' 
												');
						$getRefNo->execute();
						if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
							$ref_No = $rowRef['refNo'];
							$amount = $rowRef['totalAmount'];
							$to_name = $rowRef['name'];
							$to_surname = $rowRef['surname'];
							$email_to =$rowRef['email'];
							$requestBy =$rowRef['requestBy'];
						}
						$amount = $amount.' '.$country_details['country_currency'];

						if($requestBy != '')
						{
							$title = 'PETTY CASH: Your Request is Updated | SERIS';
							$msg = 'it is sent to the COO/Country Manager for further approval';

                            $sender = $this_user_id;

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

                            $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
							
						}

						while($rowFina = $getFina->fetch(PDO::FETCH_ASSOC))
						{
							$myFina = $rowFina["userId"];
							$email_to1 = $rowFina["email"]; 
							$to_name1 = $rowFina["name"]; 

							$title = 'PETTY CASH: A Request To Approve | SERIS';
							$msg = 'it needs your approval';

                            $message = 'This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> 
                                            has approved the request ('.$ref_No.') of <b>'.$amount.'</b> and '.$msg.'.
                                    ';

							if($myFina != ''){
                                $pettycash->save_email( $email_to1, $to_name1, $reply_email_to, $reply_name, $title, $message, $sender );
							}
						}
					}

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
        elseif( isset($data->operation) && $data->operation == 'suspend') {

            if($user_details['can_be_super_user'] != 1 && 
                $user_details['can_be_cash_hod'] != 1 &&
                $user_details['can_be_cash_coo'] != 1 &&
                $user_details['can_be_cash_finance'] != 1 &&
                $user_details['can_be_cash_manager'] != 1
            ) 
            {
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
                $pettycash->id = clean_data($data->id); 
                $request_id = clean_data($data->id); 

                if(!($pettycash->is_request_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Request Not Found'
                    ]);
                    die();
                } 
            } 

            $pettycash->status = "suspended";
            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($pettycash->suspend_request()) {

                    // Email notification
					$ref_No = $to_name = $to_surname = $email_to = $requestBy = ''; $amount = 0;
					$getRefNo = $db->prepare('SELECT ca.refNo,ca.totalAmount, ca.requestBy, u.name, u.surname, u.email FROM cashrequests ca 
												LEFT JOIN users u ON ca.requestBy = u.userId
												WHERE ca.id = '.$request_id.' 
											');
					$getRefNo->execute();
					if($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)){
						$ref_No = $rowRef['refNo'];
						$amount = $rowRef['totalAmount'];
						$to_name = $rowRef['name'];
						$to_surname = $rowRef['surname'];
						$email_to =$rowRef['email'];
						$requestBy =$rowRef['requestBy'];
					}
					$amount = $amount.' '.$country_details['country_currency'];

					if($requestBy != '')
					{
						$title = 'PETTY CASH: Your Request is suspended | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> has been suspended by <b>'.$reply_name.' '.$reply_surname.'</b>.';

						$pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
					}

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Suspended"
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
        elseif( isset($data->operation) && $data->operation == 'activate_budget') {

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

                if(!($budget->is_budget_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Budget Not Found'
                    ]);
                    die();
                }
                
                if(($budget->is_budget_active())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Active Budget Cannot be Re-ativated'
                    ]);
                    die();
                }
            } 

            $budget->status = "active";
            $budget->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($budget->activate_budget()) {

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

        }
        elseif( isset($data->operation) && $data->operation == 'delete_budget') {

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

                if(!($budget->is_budget_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Budget Not Found'
                    ]);
                    die();
                } 

                if(($budget->is_budget_active())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Active Budget Cannot be Deleted'
                    ]);
                    die();
                } 
            } 

            if($errorMsg =='') {
                //update idea
                if($budget->delete_budget()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Budget Deleted"
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
        elseif( isset($data->operation) && $data->operation == 'cashbox_topup') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 )
            {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->category)) {  
                $errorMsg = "Category is required";  
            } else { 
                $recharge_category = clean_data($data->category); 
            } 

            if (empty($data->amount)) {  
                $errorMsg = "Amount is required";  
            } else { 
                $amaountRecharge = clean_data($data->amount); 
                $amaountWithdraw = clean_data($data->amount);
            } 

            if (empty($data->comment)) {  
                $errorMsg = "Comment is required";  
            } else { 
                $recharge_comment = clean_data($data->comment); 
            } 


            $pettycash->this_user = $this_user_id;

            if($errorMsg =='') {                

                if($recharge_category == "withdraw")
				{
                    $rowFinaAcc = $pettycash->get_finance_balance($mycountry);
                    if (!$rowFinaAcc) {  
                        echo json_encode(
                            array(
                                "success" => false,
                                "message" => "Please recharge your account first."
                            )
                        );
                    }

                    $thisFinaBalance = $rowFinaAcc['amount'];

                    if( $amaountWithdraw > $thisFinaBalance){

                        echo json_encode(
                            array(
                                "success" => false,
                                "message" => "You are withdrawing more than available!"
                            )
                        );

                    } else {
                        $amountUpdated = $thisFinaBalance - $amaountWithdraw;

                        if($pettycash->update_finance_balance($mycountry,$amountUpdated)) { 

                            $user_id = $this_user_id; 
                            $country = $mycountry;
                            $previous_amount = $thisFinaBalance; 
                            $new_recharge = 0; 
                            $new_withdraw = $amaountWithdraw; 
                            $total_amount = $amountUpdated; 
                            $comment = $recharge_comment;
                            
                            if($pettycash->finance_recharge_logs($user_id, $country, $previous_amount, $new_recharge, $new_withdraw, $total_amount, $comment) ) {

                                echo json_encode(
                                    array(
                                        "success" => true,
                                        "message" => "Money Withdrawn"
                                    )
                                );
                            }
                        }
                    }
                } 
                else
                {
                    if ($rowFinaAcc = $pettycash->get_finance_balance($mycountry)) {  
                        
                        $thisFinaBalance = $rowFinaAcc['amount'];
                        $amountUpdated = $amaountRecharge + $thisFinaBalance;

                        if($pettycash->update_finance_balance($mycountry,$amountUpdated)) { 

                            $user_id = $this_user_id; 
                            $country = $mycountry;
                            $previous_amount = $thisFinaBalance; 
                            $new_recharge = $amaountRecharge; 
                            $new_withdraw = 0; 
                            $total_amount = $amountUpdated; 
                            $comment = $recharge_comment;
                            
                            if($pettycash->finance_recharge_logs($user_id, $country, $previous_amount, $new_recharge, $new_withdraw, $total_amount, $comment) ) {

                                echo json_encode(
                                    array(
                                        "success" => true,
                                        "message" => "Account Recharged"
                                    )
                                );
                            }
                        }
                    }
                    else 
                    {
                        if($pettycash->insert_finance_balance($mycountry,$amaountRecharge)) { 

                            $user_id = $this_user_id; 
                            $country = $mycountry;
                            $previous_amount = $thisFinaBalance; 
                            $new_recharge = $amaountRecharge; 
                            $new_withdraw = 0; 
                            $total_amount = $amaountRecharge; 
                            $comment = $recharge_comment;
                            
                            if($pettycash->finance_recharge_logs($user_id, $country, $previous_amount, $new_recharge, $new_withdraw, $total_amount, $comment) ) {

                                echo json_encode(
                                    array(
                                        "success" => true,
                                        "message" => "Account Recharged"
                                    )
                                );
                            }
                        }
                    }

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
        elseif( isset($data->operation) && ($data->operation == 'hod_request_budget') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_hod'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->budget_to_increase)) {
				$errorMsg = 'Select Budget To Increase';  
			} else {  
				$budget_to_increase = clean_data($data->budget_to_increase); 
			} 

            if (empty($data->budget_to_deduct)) {  
                $errorMsg = 'Select Budget To Deduct';  
            } else {  
                $budget_to_deduct = clean_data($data->budget_to_deduct); 
            }

            if (empty($data->amount)) {  
                $errorMsg = 'Amount is Required';  
            } else {  
                $amount = clean_data($data->amount); 
            }
			
			if (empty($data->description)) {  
                $errorMsg = 'Description is Required';  
            } else {  
                $description = clean_data($data->description); 
            }			
			

            if($is_on_budget == 1) {

				if(isset($budget_to_deduct) && isset($budget_to_increase)){
					$rowBC = $pettycash->get_depart_cat_budget($mycountry,$my_department,$budget_to_deduct);
					$remain = $rowBC['remaining_amount'];
					if($remain < $amount) {
						$errorMsg = 'There is not enough budget based on the category selected.';
					}
				} else {
					$errorMsg = 'Please Select Budgets First'; 
				}

			} else {
				$errorMsg = 'The system is not set to Budget Model'; 
			}

            if ( (isset($budget_to_deduct) && isset($budget_to_increase)) 
				&& ($budget_to_deduct == $budget_to_increase)
			) {  
                $errorMsg = 'You have selected the same budgets';  
            }
            
            $status = "pending";

            $this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                $response = $budget->hod_requests_budget($mycountry, $my_department, $budget_to_deduct, $budget_to_increase, $amount, $description, $status, $this_user);
                if($response[0]) { 

                    // Email Notification Logic
                    $getCOO=$db->prepare("SELECT u.* FROM users u
											LEFT JOIN privileges p ON u.userId = p.userId
											WHERE u.country =:ctr AND p.can_be_cash_coo = 1
										");
                    $getCOO->bindParam(':ctr', $mycountry);
                    $getCOO->execute();
                    $countCOO = $getCOO->rowCount();

                    if($countCOO > 0){

                        
                        $amount = $amount.' '.$country_details['country_currency'];
                        $title = 'PETTY CASH: A new budget request Added | SERIS';
                        $sender = $this_user_id;
                        $ref_No = $response[1];

                        while($rowCOO = $getCOO->fetch(PDO::FETCH_ASSOC))
                        {
                            $myCOO = $rowCOO["userId"];
                            $email_to = $rowCOO["email"]; 
                            $to_name = $rowCOO["name"]; 

                            $message = 'This is to notify you that <b>' . $reply_name . ' ' . $reply_surname . '</b> 
                                    has added a Budget TopUp Request (' . $ref_No . ') of <b>' . $amount . '</b> and 
                                    it needs your approval. </p><br/>';
                            
                            if($myCOO != ''){
                                $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                            }
                        }
                    }

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Request Submitted"
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
        elseif( isset($data->operation) && ($data->operation == 'reject_budget') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_coo'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {
				$errorMsg = 'ID is Required';  
			} else {  
				$request_id = clean_data($data->id); 

                if($row_req = $budget->is_budget_request_exists($request_id)) {
                    $ref_No = $row_req['refNo'];
                } else {
                    $errorMsg = 'The request ID is unkown';
                }
			} 

            if (empty($data->approver_comment)) {  
                $errorMsg = 'Comment is Required';  
            } else {  
                $approver_comment = clean_data($data->approver_comment); 
            }			
            
            $status = "rejected";

            $this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($budget->reject_budget_request($status,$approver_comment,$request_id)) { 

                    // Email Notification Logic
                    

                    $sender = $this_user_id;
                    $ref_No = $to_name = $to_surname = $email_to = $requestBy = '';
                    $amount = 0;
                    $getRefNo = $db->prepare('SELECT hb.refNo,hb.amount, hb.requested_by, u.name, u.surname, u.email FROM hod_budget_requests hb 
                                    LEFT JOIN users u ON hb.requested_by = u.userId
                                    WHERE hb.id = ' . $request_id . ' 
                                ');
                    $getRefNo->execute();
                    if ($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)) {
                        $ref_No = $rowRef['refNo'];
                        $amount = $rowRef['amount'];
                        $to_name = $rowRef['name'];
                        $to_surname = $rowRef['surname'];
                        $email_to = $rowRef['email'];
                        $requestBy = $rowRef['requested_by'];
                    }
                    $amount = $amount . ' ' . $country_details['country_currency'];

                    if ($requestBy != '') {

                        $title = 'PETTY CASH: Your Budget Request is rejected | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                has been rejected by <b>'.$reply_name.' '.$reply_surname.'</b>. 
                                <br><br><u>The reason:<u>
                                <br><b>'.$approver_comment.'</b>';                               

                        $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                    }


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
        elseif( isset($data->operation) && ($data->operation == 'coo_approve_budget') ) {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_coo'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {
				$errorMsg = 'ID is Required';  
			} else {  
				$bg_request_id = clean_data($data->id); 

                if($rowReq = $budget->is_budget_request_exists($bg_request_id)) {
                    
                    $ref_No = $rowReq['refNo'];
                    $bg_country = $rowReq['country'];
					$bg_department = $rowReq['department'];
					$bg_to_deduct = $rowReq['budget_to_deduct'];
					$bg_to_increase = $rowReq['budget_to_increase'];
					$bg_amount = $rowReq['amount'];
                } else {
                    $errorMsg = 'The request ID is unkown';
                }
			} 

            if($is_on_budget == 1) {
                if(isset($rowReq)){
                    //TO DEDUCT
                    $rowBC = $pettycash->get_depart_cat_budget($bg_country,$bg_department,$bg_to_deduct);                
                    $bdg_id = $rowBC['id'];
                    $remain = $rowBC['remaining_amount'];
                    $initial_amount = $rowBC['initial_amount'];
                    $previous_total_amount = $rowBC['total_amount'];
                    $previous_deducted_amount = $rowBC['deducted_amount'];

                    if($remain < $bg_amount) {
                        $errorMSG = 'There is not enough budget based on the category selected.';
                    }

                    //TO INCREASE  
                    $rowBC_incr = $pettycash->get_depart_cat_budget($bg_country,$bg_department,$bg_to_increase);
                    $bdg_id_incr = $rowBC_incr['id'];
                    $remain_incr = $rowBC_incr['remaining_amount'];
                    $initial_amount_incr = $rowBC_incr['initial_amount'];
                    $previous_total_amount_icr = $rowBC_incr['total_amount'];
                    $previous_topup_amount = $rowBC_incr['topup_amount'];
                }
            } else {
                $errorMsg = 'Budget Mode is not enabled.'; 
            }

            $status = "approved";

            $this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($budget->approve_budget_request($status,$bg_request_id)) {

                    // Update Deducted Account
					$bdg_id = $bdg_id ;
					$deducted_amount = $previous_deducted_amount + $bg_amount;
					$ded_total_amount = $previous_total_amount - $bg_amount;
					$ded_new_remain = $remain - $bg_amount;
                    $topup_amount = 0;

                    $budget->update_deduct_budget_on_approve($deducted_amount,$ded_total_amount,$ded_new_remain,$bdg_id);

                    // Update Increase Account
					$bdg_id_incr = $bdg_id_incr;
					$topup_amount = $previous_topup_amount + $bg_amount;
					$incr_total_amount = $previous_total_amount_icr + $bg_amount;
					$incr_new_remain = $remain_incr + $bg_amount;

                    $budget->update_topup_budget_on_approve($topup_amount,$incr_total_amount,$incr_new_remain,$bdg_id_incr);

                    // Email Notification Logic   
                    $sender = $this_user_id;
                    $ref_No = $to_name = $to_surname = $email_to = $requestBy = '';
                    $amount = 0;
                    $getRefNo = $db->prepare('SELECT hb.refNo,hb.amount, hb.requested_by, u.name, u.surname, u.email FROM hod_budget_requests hb 
                                    LEFT JOIN users u ON hb.requested_by = u.userId
                                    WHERE hb.id = ' . $bg_request_id . ' 
                                ');
                    $getRefNo->execute();
                    if ($rowRef = $getRefNo->fetch(PDO::FETCH_ASSOC)) {
                        $ref_No = $rowRef['refNo'];
                        $amount = $rowRef['amount'];
                        $to_name = $rowRef['name'];
                        $to_surname = $rowRef['surname'];
                        $email_to = $rowRef['email'];
                        $requestBy = $rowRef['requested_by'];
                    }
                    $amount = $amount . ' ' . $country_details['country_currency'];

                    if ($requestBy != '') {

                        $title = 'PETTY CASH: Your Budget Request is approved | SERIS';
                        $message = 'This is to notify you that your request ('.$ref_No.') of <b>'.$amount.'</b> 
                                    has been approved and the budget has been successfully amended by <b>'.$reply_name.' '.$reply_surname.'</b>. 
                                ';                               

                        $pettycash->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                    }


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
        else {
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }