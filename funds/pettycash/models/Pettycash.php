<?php

class Pettycash
{
    //DB Stuff
    private $conn;
    private $table = "cashrequests";
    public $user_details;

    public $this_user;
    public $error;

    //User properties
    public $id;
    public $refNo;
    public $country;
    public $department;
    public $visitDate;
    public $category;
    public $budget_category;
    public $budget_category_val;
    public $providers;
    public $providers_Val;
    public $customers;
    public $customers_Val;
    public $transport;
    public $transport_Val;
    public $accomodation;
    public $accomodation_Val;
    public $meals;
    public $meals_Val;
    public $otherExpenses;
    public $otherExpenses_Val;
    public $requester_charges;
    public $requester_charges_Val;
    public $charges;
    public $charges_Val;
    public $partiallyDisbursed;
    public $partiallyDisbursed_Val;
    public $partiallyRemaining;
    public $partiallyRemaining_Val;
    public $finaRemainingBalance;
    public $totalAmount;
    public $totalAmount_Val;
    public $totalUsed;
    public $totalUsed_Val;
    public $afterClearance;
    public $afterClearance_Val;
    public $clearanceDescription;
    public $clearSupervisorComment;
    public $receiptImage;
    public $additional_doc;
    public $description;
    public $phone;
    public $bank_name;
    public $cheque_number;
    public $air_departure_date;
    public $air_return_date;
    public $checkin_date;
    public $checkout_date;
    public $status;
    public $hodApprove;
    public $hodDate;
    public $financeApprove;
    public $financeDate;
    public $financeRelease;
    public $financeReleaseDate;
    public $clearanceDate;
    public $clearedBy;
    public $managerApprove;
    public $managerDate;
    public $gmdApprove;
    public $gmdDate;
    public $gmdComment;
    public $suspendDate;
    public $suspendedBy;
    public $returnReason;
    public $ReturnDate;
    public $ReturnedBy;
    public $rejectReason;
    public $rejectDate;
    public $rejectedBy;
    public $requestBy;
    public $requestDate;
    public $amountGiven;
    public $diff;


    //Pagination properties
    public $draw;
    public $start;
    public $rowperpage; // Rows display per page
    public $columnIndex; // Column index
    public $columnName; // Column name
    public $columnSortOrder; // asc or desc
    public $searchValue; // Search value


    //condtructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    //Get Items
    public function read_my_requests()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = 'FIELD(c.status, "@HOD", "returnedFromGMD", "returnedFromCOO", "returnedFromFinance", "returnedFromHOD", "approved", "clearDenied", "completed", "partiallyDisbursed", "@FinanceFromCOO", "@FinanceFromGMD", "@GMDfromCOO", "@FinanceFromHOD", "@COO", "suspended", "clearing"), c.id ASC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                INNER JOIN 
                    users u ON c.requestBy = u.userId 
                LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                WHERE c.status NOT IN ("cleared","rejected","cancelled") 
                    AND c.requestBy = :requestBy AND c.country = :country
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':requestBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*, u.name AS request_by_name, 
                        u.surname AS request_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    INNER JOIN 
                        users u ON c.requestBy = u.userId 
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE c.status NOT IN ("cleared","rejected","cancelled") 
                        AND c.requestBy = :requestBy AND c.country = :country
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':requestBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $view = $update = $cancel = $resend = $complete  = $clear = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if (($status === "@HOD") || ($status === "returnedFromHOD" || $status === "returnedFromFinance" || $status === "returnedFromCOO" || $status === "returnedFromGMD")) {
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
            }
            if (($status === "@HOD") || ($status === "returnedFromHOD" || $status === "returnedFromFinance" || $status === "returnedFromCOO" || $status === "returnedFromGMD")) {
                $cancel = '<li id="' . $id . '" class="cancel"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Cancel Request</a></li>';
            }
            if ($status === "returnedFromHOD" || $status === "returnedFromFinance" || $status === "returnedFromCOO" || $status === "returnedFromGMD") {
                $resend = '<li id="' . $id . '" class="resend"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i> Resend</a></li>';
            }
            if ($status == "approved") {
                $complete = '<li id="' . $id . '" class="complete"><a href="#" class="dropdown-item"><i class="fas fa-check font-size-16 text-info me-1 "></i> Complete</a></li>';
            }
            if ($status === "completed" || $status === "clearDenied") {
                $clear = '<li id="' . $id . '" class="clear"><a href="#" class="dropdown-item"><i class="fas fa-receipt font-size-16 text-info me-1 "></i> Reconciliation</a></li>';
            }


            if ($status == "@HOD") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif ($status == "returnedFromHOD" || $status == "returnedFromFinance" || $status == "returnedFromCOO" || $status == "returnedFromGMD") {
                $status = '<span class="badge" style="background:#ff0303; color:#ffebf7; ">' . $status . '</span>';
            } elseif ($status == "@FinanceFromHOD") {
                $status = '<span class="badge" style="background:#d66700; color:#fffbeb;">' . $status . '</span>';
            } elseif ($status == "@COO") {
                $status = '<span class="badge" style="background:#ffd935; color:#4c4c4c;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromCOO") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromGMD") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@GMDfromCOO") {
                $status = '<span class="badge" style="background:#bd0074; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "approved" || $status == "partiallyDisbursed") {
                $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "clearing") {
                $status = '<span class="badge" style="background:#510c78; color:#f0e2fc;">' . $status . '</span>';
            } elseif ($status == "completed") {
                $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">' . $status . '</span>';
            } elseif ($status == "clearDenied") {
                $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">' . $status . '</span>';
            } elseif ($status == "rejected") {
                $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">' . $status . '</span>';
            } elseif ($status == "cleared") {
                $status = '<span class="badge" style="background:#d6d6d6; color:#585858;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "cancelled") {
                $status = '<span class="badge" style="background:#4f4f4f; color:#e6e6e6;">' . $status . '</span>';
            }



            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2),
                'status' => $status,
                'requestBy' => $request_by_surname . ' ' . $request_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $update . '                             
                                ' . $cancel . '                       
                                ' . $resend . '
                                ' . $complete . '
                                ' . $clear . '
                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }

    public function department_requests()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' c.id ASC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                INNER JOIN 
                    users u ON c.requestBy = u.userId 
                LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                LEFT JOIN 
                    user_new_depart nd ON c.department = nd.department
                WHERE ((c.status IN ("@HOD")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))		
                    AND ( (c.department = :department AND c.country = :country)  OR ( nd.country = c.country AND nd.userId = :userId))
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':department', $this->department);
        $counted->bindParam(':country', $this->country);
        $counted->bindParam(':userId', $this->this_user);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*, u.name AS request_by_name, 
                        u.surname AS request_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    INNER JOIN 
                        users u ON c.requestBy = u.userId 
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    LEFT JOIN 
                        user_new_depart nd ON c.department = nd.department
                    WHERE ((c.status IN ("@HOD")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))		
                        AND ( (c.department = :department AND c.country = :country)  OR ( nd.country = c.country AND nd.userId = :userId))
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':userId', $this->this_user);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $view = $approve = $suspend = $unsuspend = $amend  = $reject = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if ($status === "@HOD") {
                $approve = '<li id="' . $id . '" class="approve"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if (($status === "@HOD") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if (($status === "@HOD") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }


            if ($status == "@HOD") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }


            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2),
                'status' => $status,
                'requestBy' => $request_by_surname . ' ' . $request_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                                ' . $amend . '
                                ' . $reject . '
                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }


    public function country_requests()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' c.id ASC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                INNER JOIN 
                    users u ON c.requestBy = u.userId 
                LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                WHERE ((c.status IN ("@COO")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))		
                    AND ( c.country = :country)
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*, u.name AS request_by_name, 
                        u.surname AS request_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    INNER JOIN 
                        users u ON c.requestBy = u.userId 
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE ((c.status IN ("@COO")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))		
                        AND ( c.country = :country)
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $view = $approve = $suspend = $unsuspend = $amend  = $reject = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if ($status === "@COO") {
                $approve = '<li id="' . $id . '" class="approve"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if (($status === "@COO") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if (($status === "@COO") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }


            if ($status == "@COO") {
                $status = '<span class="badge" style="background:#ffd935; color:#4c4c4c;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }


            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2),
                'status' => $status,
                'requestBy' => $request_by_surname . ' ' . $request_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                                ' . $amend . '
                                ' . $reject . '
                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }


    public function group_requests()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' c.id ASC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                INNER JOIN 
                    users u ON c.requestBy = u.userId 
                LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                WHERE ((c.status IN ("@GMDfromCOO")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*, u.name AS request_by_name, 
                        u.surname AS request_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    INNER JOIN 
                        users u ON c.requestBy = u.userId 
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE ((c.status IN ("@GMDfromCOO")) OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy))		
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':suspendedBy', $this->this_user);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $view = $approve = $suspend = $unsuspend = $amend  = $reject = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if ($status === "@GMDfromCOO") {
                $approve = '<li id="' . $id . '" class="approve"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if (($status === "@GMDfromCOO") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if (($status === "@GMDfromCOO") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }


            if ($status == "@GMDfromCOO") {
                $status = '<span class="badge" style="background:#bd0074; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }


            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2),
                'status' => $status,
                'requestBy' => $request_by_surname . ' ' . $request_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                                ' . $amend . '
                                ' . $reject . '
                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }


    public function finance_requests()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' c.id ASC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                INNER JOIN 
                    users u ON c.requestBy = u.userId 
                LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                WHERE ((c.status IN ("@FinanceFromHOD","@FinanceFromCOO","@COO","@FinanceFromGMD","partiallyDisbursed")) 
                        OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy)
                        OR (c.status IN ("clearing") AND c.financeRelease = :financeRelease )
                    )		
                    AND ( c.country = :country)
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':financeRelease', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*, u.name AS request_by_name, 
                        u.surname AS request_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    INNER JOIN 
                        users u ON c.requestBy = u.userId 
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE ((c.status IN ("@FinanceFromHOD","@FinanceFromCOO","@COO","@FinanceFromGMD","partiallyDisbursed")) 
                        OR (c.status IN ("suspended") AND c.suspendedBy = :suspendedBy)
                        OR (c.status IN ("clearing") AND c.financeRelease = :financeRelease )
                    )		
                    AND ( c.country = :country)
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':financeRelease', $this->this_user);
        $stmt->bindParam(':country', $this->country);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $view = $disburse = $clear = $cancelclear = $suspend  = $unsuspend = $amend = $reject = $higher_level = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if (($status === "@FinanceFromHOD") || ($status === "@FinanceFromCOO") || ($status === "@FinanceFromGMD") || ($status === "partiallyDisbursed")) {
                $disburse = '<li id="' . $id . '" class="fina_disburse"><a href="#" class="dropdown-item"><i class="far fa-money-bill-alt font-size-16 text-info me-1 "></i> Disburse</a></li>';
            }
            if (($status === "clearing") && ($financeRelease == $this->this_user)) {
                $clear = '<li id="' . $id . '" class="clearing"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve Reconciliation</a></li>';
                $cancelclear = '<li id="' . $id . '" class="cancelclear"><a href="#" class="dropdown-item"><i class="fas fa-times font-size-16 text-danger me-1 "></i> Deny Reconciliation</a></li>';
            }
            if ($status === "@FinanceFromHOD") {
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if (($status === "@FinanceFromHOD") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if (($status === "@FinanceFromHOD") || (($status === "suspended") && ($suspendedBy == $this->this_user))) {
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }
            if (($status === "@FinanceFromHOD")) {
                $higher_level = '<li id="' . $id . '" class="higher_level"><a href="#" class="dropdown-item"><i class="fas fa-arrow-up font-size-16 text-info me-1 "></i> Send to COO</a></li>';
            }


            if ($status == "@FinanceFromHOD") {
                $status = '<span class="badge" style="background:#d66700; color:#fffbeb;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromCOO") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromGMD") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "partiallyDisbursed") {
                $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@COO") {
                $status = '<span class="badge" style="background:#ffd935; color:#4c4c4c;">' . $status . '</span>';
            } elseif ($status == "clearing") {
                $status = '<span class="badge" style="background:#510c78; color:#f0e2fc;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }


            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2),
                'status' => $status,
                'requestBy' => $request_by_surname . ' ' . $request_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $disburse . '
                                ' . $higher_level . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                                ' . $clear . '
                                ' . $cancelclear . '
                                ' . $amend . '
                                ' . $reject . '
                            </ul>
                        </div>
                    '
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }

    public function read_finance_account_logs()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    f.recharge_date LIKE "%' . $this->searchValue . '%" OR
                    u1.name LIKE "%' . $this->searchValue . '%" OR
                    u1.surname LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'f.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    finance_recharge_logs f
                LEFT JOIN 
                    users u1 ON f.user_id = u1.userId 
                LEFT JOIN 
                    countries ctr ON f.country = ctr.id
                WHERE f.country = :country
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT 
                        f.*,u1.name AS name1, u1.surname AS surname1, ctr.currency
                    FROM 
                        finance_recharge_logs f
                    LEFT JOIN 
                        users u1 ON f.user_id = u1.userId 
                    LEFT JOIN 
                        countries ctr ON f.country = ctr.id
                    WHERE f.country = :country
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':country', $this->country);

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $new_recharge = '<span style="color:#00a401;">' . number_format($new_recharge, 2) . ' ' . $currency . '</span>';
            $new_withdraw = '<span style="color:#d60000;">' . number_format($new_withdraw, 2) . ' ' . $currency . '</span>';
            $total_amount = '<span style="color:#0087a4;">' . number_format($total_amount, 2) . ' ' . $currency . '</span>';

            $data[] = array(
                'id' => $id,
                'previous_amount' => number_format($previous_amount, 2) . ' ' . $currency,
                'new_recharge' => $new_recharge,
                'new_withdraw' => $new_withdraw,
                'total_amount' => $total_amount,
                'comment' => $comment,
                'recharge_date' => $recharge_date,
                'user' => $name1 . ' ' . $surname1
            );
        }

        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }


    //Get Single Idea
    public function read_single()
    {
        //create query
        $query =  'SELECT 
                        c.*, 
                        ctr.currency,
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2,
                        u3.name AS name3,
                        u3.surname AS surname3,
                        u4.name AS name4,
                        u4.surname AS surname4,
                        u5.name AS name5,
                        u5.surname AS surname5,
                        u6.name AS name6,
                        u6.surname AS surname6,
                        u7.name AS name7,
                        u7.surname AS surname7,
                        u8.name AS name8,
                        u8.surname AS surname8,
                        u9.name AS name9,
                        u9.surname AS surname9,
                        u10.name AS name10,
                        u10.surname AS surname10,
                        bc.name AS budgetCategory
                    FROM 
                        ' . $this->table . ' c
                    INNER JOIN 
                        countries ctr ON c.country = ctr.id
                    INNER JOIN 
                        users u1 ON c.requestBy = u1.userId
                    LEFT JOIN 
                        users u2 ON c.hodApprove = u2.userId
                    LEFT JOIN 
                        users u3 ON c.financeRelease = u3.userId
                    LEFT JOIN 
                        users u4 ON c.managerApprove = u4.userId
                    LEFT JOIN 
                        users u5 ON c.financeApprove = u5.userId
                    LEFT JOIN 
                        users u6 ON c.clearedBy = u6.userId
                    LEFT JOIN 
                        users u7 ON c.rejectedBy = u7.userId
                    LEFT JOIN 
                        users u8 ON c.suspendedBy = u8.userId
                    LEFT JOIN 
                        users u9 ON c.ReturnedBy = u9.userId
                    LEFT JOIN 
                        users u10 ON c.gmdApprove = u10.userId
                    LEFT JOIN 
                    budget_categories bc ON c.budget_category = bc.id
                    WHERE
                        c.id = :id
                    LIMIT 0,1
                ';

        //Prepare Statement
        $stmt = $this->conn->prepare($query);

        //Bind ID
        $stmt->bindParam(':id', $this->id);

        //Execute query
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //Set Properties
        $this->id = $row['id'];
        $this->refNo = $row["refNo"];
        $this->visitDate = $row["visitDate"];
        $this->category    = $row["category"];
        $this->budget_category = $row["budgetCategory"] ? $row["budgetCategory"] : 'N/A';
        $this->budget_category_val = $row["budget_category"];
        $this->providers = $row["providers"] ? str_replace(',', ',<br />', $row["providers"]) : "";
        $this->providers_Val = explode(', ', $row["providers"]);
        $this->customers = $row["customers"] ? str_replace(',', ',<br />', $row["customers"]) : "";
        $this->customers_Val = explode(', ', $row["customers"]);
        $this->transport = number_format($row["transport"], 2) . ' ' . $row['currency'];
        $this->transport_Val = $row["transport"];
        $this->accomodation    = number_format($row["accomodation"], 2) . ' ' . $row['currency'];
        $this->accomodation_Val     = $row["accomodation"];
        $this->meals                 = number_format($row["meals"], 2) . ' ' . $row['currency'];
        $this->meals_Val             = $row["meals"];
        $this->otherExpenses         = number_format($row["otherExpenses"], 2) . ' ' . $row['currency'];
        $this->otherExpenses_Val     = $row["otherExpenses"];
        $this->afterClearance         = number_format($row["afterClearance"], 2) . ' ' . $row['currency'];
        $this->afterClearance_Val     = $row["afterClearance"];
        $this->charges                 = number_format($row["charges"], 2) . ' ' . $row['currency'];
        $this->charges_Val             = $row["charges"];
        $this->requester_charges     = number_format($row["requester_charges"], 2) . ' ' . $row['currency'];
        $this->requester_charges_Val = $row["requester_charges"];
        $this->partiallyDisbursed     = number_format($row["partiallyDisbursed"], 2) . ' ' . $row['currency'];
        $this->partiallyDisbursed_Val = $row["partiallyDisbursed"];
        $this->partiallyRemaining     = number_format($row["partiallyRemaining"], 2) . ' ' . $row['currency'];
        $this->partiallyRemaining_Val = $row["partiallyRemaining"];
        $this->totalAmount             = number_format($row["totalAmount"], 2) . ' ' . $row['currency'];
        $this->totalAmount_Val         = $row["totalAmount"];
        $this->totalUsed             = number_format($row["totalUsed"], 2) . ' ' . $row['currency'];
        $this->totalUsed_Val         = $row["totalUsed"];
        $this->air_departure_date     = $row["air_departure_date"];
        $this->air_return_date         = $row["air_return_date"];
        $this->checkin_date         = $row["checkin_date"];
        $this->checkout_date         = $row["checkout_date"];
        $this->phone                = $row["phone"];
        $this->bank_name            = $row["bank_name"];
        $this->cheque_number         = $row["cheque_number"];
        $this->description             = $row["description"];
        $this->requestBy            = $row["name1"] . " " . $row["surname1"];
        $this->requestDate             = $row["requestDate"];
        $this->status                = $row["status"];
        $this->receiptImage            = $row["receiptImage"];
        $this->additional_doc        = $row["additional_doc"];
        $this->hodDate                 = $row["hodDate"] ? $row["hodDate"] : "N/A";
        $this->hodApprove             = $row["hodDate"] ? $row["name2"] . " " . $row["surname2"] : "N/A";
        $this->financeDate            = $row["financeDate"] ? $row["financeDate"] : "N/A";
        $this->financeApprove         = $row["financeDate"] ? $row["name5"] . " " . $row["surname5"] : "N/A";
        $this->financeReleaseDate     = $row["financeReleaseDate"] ? $row["financeReleaseDate"] : "N/A";
        $this->financeRelease        = $row["financeReleaseDate"] ? $row["name3"] . " " . $row["surname3"] : "N/A";
        $this->managerDate             = $row["managerDate"] ? $row["managerDate"] : "N/A";
        $this->managerApprove         = $row["managerDate"] ? $row["name4"] . " " . $row["surname4"] : "N/A";
        $this->gmdDate                 = $row["gmdDate"] ? $row["gmdDate"] : "N/A";
        $this->gmdApprove             = $row["gmdDate"] ? $row["name10"] . " " . $row["surname10"] : "N/A";
        $this->gmdComment             = $row["gmdComment"] ? $row["gmdComment"] : "N/A";
        $this->clearanceDate        = $row["clearanceDate"] ? $row["clearanceDate"] : "N/A";
        $this->clearedBy             = $row["clearanceDate"] ? $row["name6"] . " " . $row["surname6"] : "N/A";
        $this->clearanceDescription = $row["clearanceDescription"] ? $row["clearanceDescription"] : "N/A";
        $this->clearSupervisorComment = $row["clearSupervisorComment"] ? $row["clearSupervisorComment"] : "N/A";
        $this->suspendDate             = $row["suspendDate"] ? $row["suspendDate"] : "N/A";
        $this->suspendedBy             = $row["suspendDate"] ? $row["name8"] . " " . $row["surname8"] : "N/A";
        $this->ReturnDate             = $row["ReturnDate"] ? $row["ReturnDate"] : "N/A";
        $this->ReturnedBy             = $row["ReturnDate"] ? $row["name9"] . " " . $row["surname9"] : "N/A";
        $this->returnReason         = $row["returnReason"] ? $row["returnReason"] : "N/A";
        $this->rejectDate            = $row["rejectDate"] ? $row["rejectDate"] : "N/A";
        $this->rejectedBy             = $row["rejectDate"] ? $row["name7"] . " " . $row["surname7"] : "N/A";
        $this->rejectReason         = $row["rejectReason"] ? $row["rejectReason"] : "N/A";

        $amountGiven = $row["transport"] + $row["accomodation"] + $row["meals"] + $row["otherExpenses"];
        $this->amountGiven = number_format($amountGiven, 2) . ' ' . $row['currency'];
        $diff = $amountGiven - $row["totalUsed"];
        $this->diff = number_format($diff, 2) . ' ' . $row['currency'];

        return $stmt;
    }


    //Create Idea
    public function create()
    {
        //query
        $query = ' INSERT INTO ' . $this->table . '
                SET
                    country = :country,
                    department = :department,
                    visitDate = :visitDate,
                    category = :category,
                    budget_category = :budget_category,
                    providers = :providers,
                    customers = :customers,
                    transport = :transport,
                    accomodation = :accomodation,
                    meals = :meals,
                    otherExpenses = :otherExpenses,
                    requester_charges = :requester_charges,
                    totalAmount = :totalAmount,
                    air_departure_date = :air_departure_date,
                    air_return_date = :air_return_date,
                    checkin_date = :checkin_date,
                    checkout_date = :checkout_date,
                    additional_doc = :additional_doc,
                    description = :description,
                    phone = :phone,
                    status = :status,
                    requestBy = :requestBy,
                    requestDate = Now()
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        $this->requestBy = $this->this_user;

        //bind data
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':visitDate', $this->visitDate);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':budget_category', $this->budget_category);
        $stmt->bindParam(':providers', $this->providers);
        $stmt->bindParam(':customers', $this->customers);
        $stmt->bindParam(':transport', $this->transport);
        $stmt->bindParam(':accomodation', $this->accomodation);
        $stmt->bindParam(':meals', $this->meals);
        $stmt->bindParam(':otherExpenses', $this->otherExpenses);
        $stmt->bindParam(':requester_charges', $this->requester_charges);
        $stmt->bindParam(':totalAmount', $this->totalAmount);
        $stmt->bindParam(':air_departure_date', $this->air_departure_date);
        $stmt->bindParam(':air_return_date', $this->air_return_date);
        $stmt->bindParam(':checkin_date', $this->checkin_date);
        $stmt->bindParam(':checkout_date', $this->checkout_date);
        $stmt->bindParam(':additional_doc', $this->additional_doc);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':requestBy', $this->requestBy);

        //execute query
        if ($stmt->execute()) {
            $last_req_id = $this->conn->lastInsertId();
            $refNo = "PTY_" . $last_req_id;
            $updateRef = $this->conn->prepare('UPDATE ' . $this->table . ' SET refNo=:refNo WHERE id =:id ');
            $updateRef->bindParam(':refNo', $refNo);
            $updateRef->bindParam(':id', $last_req_id);

            if ($updateRef->execute()) {
                // return true;
                return array(true, $refNo);
            }
        }
        //print if something goes wrong
        // printf("Error: %s.\n", $stmt->error);

        return $stmt->error;
    }


    //  //Update Idea
    public function update()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    visitDate = :visitDate,
                    category = :category,
                    budget_category = :budget_category,
                    providers = :providers,
                    customers = :customers,
                    transport = :transport,
                    accomodation = :accomodation,
                    meals = :meals,
                    otherExpenses = :otherExpenses,
                    requester_charges = :requester_charges,
                    totalAmount = :totalAmount,
                    air_departure_date = :air_departure_date,
                    air_return_date = :air_return_date,
                    checkin_date = :checkin_date,
                    checkout_date = :checkout_date,
                    additional_doc = :additional_doc,
                    description = :description,
                    phone = :phone
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':visitDate', $this->visitDate);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':budget_category', $this->budget_category);
        $stmt->bindParam(':providers', $this->providers);
        $stmt->bindParam(':customers', $this->customers);
        $stmt->bindParam(':transport', $this->transport);
        $stmt->bindParam(':accomodation', $this->accomodation);
        $stmt->bindParam(':meals', $this->meals);
        $stmt->bindParam(':otherExpenses', $this->otherExpenses);
        $stmt->bindParam(':requester_charges', $this->requester_charges);
        $stmt->bindParam(':totalAmount', $this->totalAmount);
        $stmt->bindParam(':air_departure_date', $this->air_departure_date);
        $stmt->bindParam(':air_return_date', $this->air_return_date);
        $stmt->bindParam(':checkin_date', $this->checkin_date);
        $stmt->bindParam(':checkout_date', $this->checkout_date);
        $stmt->bindParam(':additional_doc', $this->additional_doc);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }


    public function read_pettycash_report($country, $department, $budget_category, $cashCategory, $statusCheck, $status, $cashReqBy, $cashDisbBy, $DateFrom, $DateTo)
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    c.refNo LIKE "%' . $this->searchValue . '%" OR 
                    c.category LIKE "%' . $this->searchValue . '%" OR 
                    c.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = 'c.id DESC';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)) {
            $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }
        //Filters Checking
        $where_qr = '';

        if ($country != "") {
            $where_qr .=  ' AND (c.country = :country)';
        }
        if ($department != "") {
            $where_qr .=  ' AND (c.department = :department)';
        }
        if ($budget_category != "") {
            $where_qr .=  ' AND (c.budget_category = :budget_category)';
        }
        if ($cashCategory != "") {
            $where_qr .=  ' AND (c.category = :category)';
        }
        if ($statusCheck != "") {
            if ($statusCheck == "disbursed") {
                $where_qr .=  ' AND ( c.status IN ("completed", "cleared", "clearing", "clearDenied") ) ';
            } elseif ($statusCheck == "inprogress") {
                $where_qr .=  ' AND ( c.status IN ("approved", "@FinanceFromHOD", "@HOD", "suspended", "returnedFromHOD", "returnedFromFinance", "returnedFromCOO", "returnedFromGMD", "@COO", "@FinanceFromCOO", "@FinanceFromGMD", "@GMDfromCOO", "@GMDfromCOO") )';
            } elseif ($statusCheck == "declined") {
                $where_qr .=  ' AND ( c.status IN ("rejected", "cancelled") )';
            }
        }
        if ($status != "") {
            $where_qr .=  ' AND (c.status = :status)';
        }
        if ($cashReqBy != "") {
            $where_qr .=  ' AND (c.requestBy = :requestBy)';
        }
        if ($cashDisbBy != "") {
            $where_qr .=  ' AND (c.financeRelease = :financeRelease)';
        }
        if ($DateFrom != "" && $DateTo != "") {
            if (($statusCheck != "") && ($statusCheck == "disbursed")) {
                $where_qr .= ' AND( cast(c.financeReleaseDate as date) BETWEEN :dateFrom AND :dateTo )';
            } elseif (($statusCheck != "") || ($statusCheck == "inprogress")) {
                $where_qr .= ' AND( cast(c.requestDate as date) BETWEEN :dateFrom AND :dateTo )';
            } elseif (($statusCheck != "") || ($statusCheck == "declined")) {
                $where_qr .= ' AND( cast(c.requestDate as date) BETWEEN :dateFrom AND :dateTo )';
            }
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' c 
                    LEFT JOIN 
                        users u ON c.requestBy = u.userId
                    LEFT JOIN 
                        users u1 ON c.financeRelease = u1.userId
                    LEFT JOIN 
                        countries ctry ON c.country = ctry.id
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE 1
                        ' . $where_qr . '
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        if ($country != "") $counted->bindParam(':country', $country);
        if ($department != "") $counted->bindParam(':department', $department);
        if ($budget_category != "") $counted->bindParam(':budget_category', $budget_category);
        if ($cashCategory != "") $counted->bindParam(':category', $cashCategory);
        if ($status != "") $counted->bindParam(':status', $status);
        if ($cashReqBy != "") $counted->bindParam(':requestBy', $cashReqBy);
        if ($cashDisbBy != "") $counted->bindParam(':financeRelease', $cashDisbBy);
        if ($DateFrom != "" && $DateTo != "") {
            $counted->bindParam(':dateFrom', $DateFrom);
            $counted->bindParam(':dateTo', $DateTo);
        }
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = ' SELECT
                        c.*,
                        c.id AS pty_id,
                        ctry.currency ,
                        u.name AS req_by_name,
                        u.surname AS req_by_surname,
                        u1.name AS disb_by_name,
                        u1.surname AS disb_by_surname,
                        bc.name AS budgetCategory
                    FROM
                        ' . $this->table . ' c
                    LEFT JOIN 
                        users u ON c.requestBy = u.userId
                    LEFT JOIN 
                        users u1 ON c.financeRelease = u1.userId
                    LEFT JOIN 
                        countries ctry ON c.country = ctry.id
                    LEFT JOIN 
                        budget_categories bc ON c.budget_category = bc.id
                    WHERE 1
                        ' . $where_qr . '
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '
            
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        if ($country != "") $stmt->bindParam(':country', $country);
        if ($department != "") $stmt->bindParam(':department', $department);
        if ($budget_category != "") $stmt->bindParam(':budget_category', $budget_category);
        if ($cashCategory != "") $stmt->bindParam(':category', $cashCategory);
        if ($status != "") $stmt->bindParam(':status', $status);
        if ($cashReqBy != "") $stmt->bindParam(':requestBy', $cashReqBy);
        if ($cashDisbBy != "") $stmt->bindParam(':financeRelease', $cashDisbBy);
        if ($DateFrom != "" && $DateTo != "") {
            $stmt->bindParam(':dateFrom', $DateFrom);
            $stmt->bindParam(':dateTo', $DateTo);
        }

        //Execute Query
        $stmt->execute();


        //RowCount
        $num = $stmt->rowCount();

        $output = array();
        $data = array();

        //Check if any Idea
        if ($num < 1) {

            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw"                =>    intval($this->draw),
                "recordsTotal"        =>     $num,
                "recordsFiltered"    =>    $number_of_rows,
                "data"                =>    []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            if ($status == "@HOD") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif ($status == "returnedFromHOD" || $status == "returnedFromFinance" || $status == "returnedFromCOO" || $status == "returnedFromGMD") {
                $status = '<span class="badge" style="background:#ff0303; color:#ffebf7; ">' . $status . '</span>';
            } elseif ($status == "@FinanceFromHOD") {
                $status = '<span class="badge" style="background:#d66700; color:#fffbeb;">' . $status . '</span>';
            } elseif ($status == "@COO") {
                $status = '<span class="badge" style="background:#ffd935; color:#4c4c4c;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromCOO") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@FinanceFromGMD") {
                $status = '<span class="badge" style="background:#006c8b; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@GMDfromCOO") {
                $status = '<span class="badge" style="background:#bd0074; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "approved" || $status == "partiallyDisbursed") {
                $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">' . $status . '</span>';
            } elseif ($status == "clearing") {
                $status = '<span class="badge" style="background:#510c78; color:#f0e2fc;">' . $status . '</span>';
            } elseif ($status == "completed") {
                $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">' . $status . '</span>';
            } elseif ($status == "clearDenied") {
                $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">' . $status . '</span>';
            } elseif ($status == "rejected") {
                $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">' . $status . '</span>';
            } elseif ($status == "cleared") {
                $status = '<span class="badge" style="background:#d6d6d6; color:#585858;">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "cancelled") {
                $status = '<span class="badge" style="background:#4f4f4f; color:#e6e6e6;">' . $status . '</span>';
            }

            $data[] = array(
                'id' => $id,
                'refNo' => $refNo ? $refNo : 'N/A',
                'category' => $category,
                'budgetCategory' => $budgetCategory ? $budgetCategory : 'N/A',
                'totalAmount' => number_format($totalAmount, 2) . ' ' . $currency,
                'status' => $status,
                'requestBy' => $req_by_surname . ' ' . $req_by_name,
                'requestDate' => date("F j, Y g:i a", strtotime($requestDate)),
                'disbursedBy' => $financeReleaseDate ? $disb_by_surname . ' ' . $disb_by_name : 'N/A',
                'disbursedDate' => $financeReleaseDate ? date("F j, Y g:i a", strtotime($financeReleaseDate)) : 'N/A'
            );
        }


        $output = array(
            "success"      => true,
            "message"       => "Data Found",
            "draw"            =>    intval($this->draw),
            "recordsTotal"    =>     $num,
            "recordsFiltered" =>    $number_of_rows,
            "data"            =>    $data
        );

        return $output;
    }


    //Update Save Idea Changes priority
    public function cancel_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    rejectReason = :rejectReason,
                    rejectedBy = :rejectedBy,
                    rejectDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':rejectReason', $this->rejectReason);
        $stmt->bindParam(':rejectedBy', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function reject_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    rejectReason = :rejectReason,
                    rejectedBy = :rejectedBy,
                    rejectDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':rejectReason', $this->rejectReason);
        $stmt->bindParam(':rejectedBy', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function amend_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    returnReason = :returnReason,
                    ReturnedBy = :ReturnedBy,
                    ReturnDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':returnReason', $this->returnReason);
        $stmt->bindParam(':ReturnedBy', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function reconciliation_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    totalUsed = :totalUsed,
                    receiptImage = :receiptImage,
                    clearanceDescription = :clearanceDescription
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':totalUsed', $this->totalUsed);
        $stmt->bindParam(':receiptImage', $this->receiptImage);
        $stmt->bindParam(':clearanceDescription', $this->clearanceDescription);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function approve_reconciliation()
    {
        //query
        $query_ = ' UPDATE finacebalance SET amount = :amount WHERE country = :country ';
        $stmt_ = $this->conn->prepare($query_);
        $stmt_->bindParam(':amount', $this->finaRemainingBalance);
        $stmt_->bindParam(':country', $this->country);

        //execute query
        if ($stmt_->execute()) {
            $query = ' UPDATE ' . $this->table . '
                    SET
                        status = :status,
                        clearSupervisorComment = :clearSupervisorComment,
                        afterClearance = :afterClearance,
                        charges = :charges,
                        totalAmount = :totalAmount,
                        clearedBy = :clearedBy,
                        clearanceDate = Now()
                    WHERE 
                        id = :id
                ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':clearSupervisorComment', $this->clearSupervisorComment);
            $stmt->bindParam(':afterClearance', $this->afterClearance);
            $stmt->bindParam(':charges', $this->charges);
            $stmt->bindParam(':totalAmount', $this->totalAmount);
            $stmt->bindParam(':clearedBy', $this->this_user);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if ($stmt->execute()) {
                return true;
            }
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function reconciliation_deny()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    clearSupervisorComment = :clearSupervisorComment
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':clearSupervisorComment', $this->clearSupervisorComment);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    //Update Save Idea Changes priority
    public function complete_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    //Update Save Idea Changes Status
    public function hod_approve_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    hodApprove = :hodApprove,
                    hodDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':hodApprove', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function finance_disburse_request()
    {
        //query
        $query_ = ' UPDATE finacebalance SET amount = :amount WHERE country = :country ';
        $stmt_ = $this->conn->prepare($query_);
        $stmt_->bindParam(':amount', $this->finaRemainingBalance);
        $stmt_->bindParam(':country', $this->country);

        //execute query
        if ($stmt_->execute()) {
            $query = ' UPDATE ' . $this->table . '
                    SET
                        status = :status,
                        partiallyDisbursed = :partiallyDisbursed,
                        partiallyRemaining = :partiallyRemaining,
                        charges = :charges,
                        totalAmount = :totalAmount,
                        financeRelease = :financeRelease,
                        financeReleaseDate = Now()
                    WHERE 
                        id = :id
                ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':partiallyDisbursed', $this->partiallyDisbursed);
            $stmt->bindParam(':partiallyRemaining', $this->partiallyRemaining);
            $stmt->bindParam(':charges', $this->charges);
            $stmt->bindParam(':totalAmount', $this->totalAmount);
            $stmt->bindParam(':financeRelease', $this->this_user);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if ($stmt->execute()) {
                return true;
            }
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function finance_disburse_cheque_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    bank_name = :bank_name,
                    cheque_number = :cheque_number,
                    financeRelease = :financeRelease,
                    financeReleaseDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':bank_name', $this->bank_name);
        $stmt->bindParam(':cheque_number', $this->cheque_number);
        $stmt->bindParam(':financeRelease', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function coo_approve_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    managerApprove = :managerApprove,
                    managerDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':managerApprove', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function sendToCOO()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    financeApprove = :financeApprove,
                    financeDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':financeApprove', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function gmd_approve_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    gmdApprove = :gmdApprove,
                    gmdDate = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':gmdApprove', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function suspend_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    suspendedBy = :suspendedBy,
                    suspendDate = Now()
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    // SOME FUNCTIONS     

    public function is_request_exists()
    {
        $query =  'SELECT id FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $num = $stmt->rowCount();
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_finance_balance($country)
    {
        $statement = $this->conn->prepare('SELECT * FROM finacebalance WHERE country =:country');
        $statement->bindParam(':country', $country);
        $statement->execute();
        $num = $statement->rowCount();
        if ($num > 0) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return $row;
        } else {
            return false;
        }
    }

    public function hod_budget_spent_percentage($country, $department)
    {
        $statement = $this->conn->prepare('SELECT concat(ROUND(((SUM(used_amount)/SUM(total_amount)) * 100 ),1),"%") AS percentage
                                                FROM budget 
                                                WHERE country = :country AND department = :department AND status = "active" ');
        $statement->bindParam(':country', $country);
        $statement->bindParam(':department', $department);
        $statement->execute();
        $num = $statement->rowCount();
        if ($num > 0) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return $row;
        } else {
            return false;
        }
    }

    public function insert_finance_balance($country, $amount)
    {
        $query = 'INSERT INTO finacebalance
                SET
                    country = :country,
                    amount = :amount
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':amount', $amount);
        //execute query
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update_finance_balance($country, $amount)
    {
        $query = 'UPDATE finacebalance
                SET amount = :amount
                WHERE country = :country
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':country', $country);
        //execute query
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function finance_recharge_logs($user_id, $country, $previous_amount, $new_recharge, $new_withdraw, $total_amount, $comment)
    {
        $query = 'INSERT INTO finance_recharge_logs
                SET
                    user_id = :user_id,
                    country = :country,
                    previous_amount = :previous_amount,
                    new_recharge = :new_recharge,
                    new_withdraw = :new_withdraw,
                    total_amount = :total_amount,
                    comment = :comment,
                    recharge_date = Now()
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':previous_amount', $previous_amount);
        $stmt->bindParam(':new_recharge', $new_recharge);
        $stmt->bindParam(':new_withdraw', $new_withdraw);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':comment', $comment);
        //execute query
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function get_single_request_details($request_id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM ' . $this->table . ' WHERE id =:id');
        $stmt->bindParam(':id', $request_id);
        $stmt->execute();
        $num = $stmt->rowCount();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($num > 0) {
            return $row;
        } else {
            return false;
        }
    }

    public function get_cash_categories($category_name)
    {
        $statement = $this->conn->prepare('SELECT * FROM cashcategories WHERE name=:cname');
        $statement->bindParam(':cname', $category_name);
        $statement->execute();
        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return false;
        }
    }

    public function get_depart_cat_budget($country, $department, $budget_category)
    {
        $getBudget = $this->conn->prepare('SELECT * FROM budget WHERE status IN("active") AND country =:country AND 
                                        department=:department AND budget_category=:budget_category');
        $getBudget->bindParam(':country', $country);
        $getBudget->bindParam(':department', $department);
        $getBudget->bindParam(':budget_category', $budget_category);
        $getBudget->execute();
        if ($row = $getBudget->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return false;
        }
    }

    public function update_budget($used_amount, $remaining_amount, $budget_id)
    {
        $updateBudget = $this->conn->prepare('UPDATE budget SET used_amount=:used_amount,remaining_amount=:remaining_amount WHERE id=:id ');
        $updateBudget->bindParam(':used_amount', $used_amount);
        $updateBudget->bindParam(':remaining_amount', $remaining_amount);
        $updateBudget->bindParam(':id', $budget_id);

        if ($updateBudget->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function upload_petty_doc()
    {
        if (is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777);
            }

            $extension = explode('.', $_FILES['additional_doc']['name']);
            $new_name = rand() . '_' . date("Y-m-d") . '.' . $extension[1];
            $destination = '../uploads/' . $new_name;
            move_uploaded_file($_FILES['additional_doc']['tmp_name'], $destination);
            return $new_name;
        }
    }

    public function unlink_petty_doc($request_id)
    {
        $statement = $this->conn->prepare('SELECT additional_doc FROM ' . $this->table . ' WHERE id = :id');
        $statement->bindParam(':id', $request_id);
        $statement->execute();
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            $dir = '../uploads/' . $row["additional_doc"];
            if ($row["additional_doc"] != '') {
                unlink($dir);
            }
        }
    }

    public function upload_petty_receipt()
    {
        if (is_uploaded_file($_FILES['receipt']['tmp_name'])) {
            if (!file_exists('../uploads/receipts')) {
                mkdir('../uploads/receipts', 0777);
            }

            $extension = explode('.', $_FILES['receipt']['name']);
            $new_name = rand() . '_' . date("Y-m-d") . '.' . $extension[1];
            $destination = '../uploads/receipts/' . $new_name;
            move_uploaded_file($_FILES['receipt']['tmp_name'], $destination);
            return $new_name;
        }
    }

    public function unlink_petty_receipt($request_id)
    {
        $statement = $this->conn->prepare('SELECT receiptImage FROM ' . $this->table . ' WHERE id = :id');
        $statement->bindParam(':id', $request_id);
        $statement->execute();
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            $dir = '../uploads/' . $row["receipt"];
            if ($row["receipt"] != '') {
                unlink($dir);
            }
        }
    }

    public function save_email($email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender)
    {

        $body = '<html><head>';
        $body .=    '<title>' . $title . '</title>';
        $body .=    '</head>';
        $body .=    ' <body style="font-size:14px;">';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear ' . $to_name . ',</p><br/>';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">' . $message . ' </p><br/>';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/seris/login" target="_blank">Login</a> to SERIS to view it.</p><br/>';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
        $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
        $body .= "</body></html>";

        $status_unsent = "unsent";
        $statement = $this->conn->prepare("INSERT INTO emails_to_send 
                                                (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                        VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
        $statement->bindParam(':email_to', $email_to);
        $statement->bindParam(':to_name', $to_name);
        $statement->bindParam(':reply_email', $reply_email_to);
        $statement->bindParam(':reply_name', $reply_name);
        $statement->bindParam(':email_subject', $title);
        $statement->bindParam(':body', $body);
        $statement->bindParam(':status', $status_unsent);
        $statement->bindParam(':user', $sender);

        if ($statement->execute()) {
            return true;
        }
    }
}