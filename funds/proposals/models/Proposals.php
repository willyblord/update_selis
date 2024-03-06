<?php

class Proposals
{
    //DB Stuff
    private $conn;
    private $table = "proposal";
    public $user_details;
    public $this_user;
    public $error;
    //User properties
    public $id;
    public $refNo;
    public $country;
    public $subject;
    public $department;
    public $proposal_date;
    public $budget_line;
    public $introduction;
    public $objective;
    public $additional_doc;
    public $status;
    public $location;
    public $FTotal;
    public $charges;
    public $bank_name;
    public $cheque_number;
    public $financeApprove;
    public $financeDate;
    public $financeRelease;
    public $financeReleaseDate;
    public $partiallyDisbursed;
    public $partiallyDisbursed_Val;
    public $partiallyRemaining;
    public $partiallyRemaining_Val;
    public $finaRemainingBalance;
    public $totalAmount;
    public $totalAmount_Val;
    public $totalUsed;
    public $totalUsed_Val;
    public $budget;
    public $remaining_amount;

    // proposal Assessmetn
    public $proposal_id;
    public $item;
    public $quantity;
    public $price;
    public $total;
    public $supplier;
    public $proposalItem_array;
    // comment
    public $comment;
    public $commentBy;
    public $commentdate;
    public $date;
    public $commentArray;
    // end
    public $created_by;
    public $created_at;
    public $onbehalf_of;
    public $updated_by;
    public $updated_at;
    public $upproved_by;
    public $approved_at;
    public $cooApprove;
    public $coo_at;
    public $gmdApprove;
    public $gmd_at;
    public $rejectReason;
    public $rejectDate;
    public $rejectedBy;
    public $returnReason;
    public $ReturnDate;
    public $ReturnedBy;
    public $gmdrejectReason;
    public $gmdrejectDate;
    public $gmdrejectedBy;
    public $hodApprove;
    public $hodDate;
    public $clearanceDate;
    public $clearedBy;
    public $managerApprove;
    public $managerDate;
    public $gmdDate;
    public $gmdComment;
    public $suspendDate;
    public $suspendedBy;
    public $requestBy;
    public $requestDate;
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


    public function read_all_proposals()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("suspended","approved","@HOD","@COO","@GMD", "@FINANCE","@returnedFromCM","@returnedFromCOO","@returnedFromHOD","@returnedFromCOF","@returnedFromGMD","@CFO","@CM","pending","approve","complete", "active", "reject")))
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        cashcategories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("suspended","approved","@HOD","@COO","@GMD", "@FINANCE","@returnedFromCM","@returnedFromCOO","@returnedFromHOD","@returnedFromCOF","@returnedFromGMD","@CFO","@CM","pending","approve", "active","completed", "reject")))
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        //Bind
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }
        // changes


        // end of changes

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view  = $update  = $cancel = $complete = $resend = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item" data-bs-target="#staticBackdrop"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if ($status === "pending") {
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
                $cancel = '<li id="' . $id . '" class="cancel"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Cancel Request</a></li>';
            }
            if ($status === "approved") {
                $status = '<span class="badge" style="background: #00a422;color: #e6ffdf;" >' . $status . '</span>';
                $complete = '<li id="' . $id . '" class="complete"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Complete</a></li>';
            }

            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;" >' . $status . '</span>';
            }
            if ($status == "@HOD") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;" >' . $status . '</span>';
            }
            if ($status == "@COO") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            }
            if ($status == "@GMD") {
                $status = '<span class="badge" style="background:#b70000; color:#e6ffdf; ">' . $status . '</span>';
            }
            if ($status == "@CM") {
                $status = '<span class="badge" style="background:#b70000; color:#e6ffdf; ">' . $status . '</span>';
            }
            if ($status == "@CFO") {
                $status = '<span class="badge" style="background: #b70000;color: #e6ffdf;">' . $status . '</span>';
            }
            if ($status == "completed") {
                $status = '<span class="badge" style="background: #949494;color: #f2f2f2;">' . $status . '</span>';
            }

            if ($status === "@returnedFromCOO") {
                $status = '<span class="badge" style="background: #b70000;color: #ffdfdf;">' . $status . '</span>';
                $resend = '<li id="' . $id . '" class="resend" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
            }

            if ($status === "@returnedFromHOD") {
                $status = '<span class="badge" style="background: #b70000;color: #ffdfdf;">' . $status . '</span>';
                $resend = '<li id="' . $id . '" class="resend" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
            }

            if ($status === "@returnedFromCOF") {
                $status = '<span class="badge" style="background: #b70000;color: #ffdfdf;">' . $status . '</span>';
                $resend = '<li id="' . $id . '" class="resend" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
            }
            if ($status === "@returnedFromCM") {
                $status = '<span class="badge" style="background: #b70000;color: #ffdfdf;">' . $status . '</span>';
                $resend = '<li id="' . $id . '" class="resend" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
            }

            if ($status === "@returnedFromGMD") {
                $status = '<span class="badge" style="background: #b70000;color: #ffdfdf;">' . $status . '</span>';
                $resend = '<li id="' . $id . '" class="resend" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Update</a></li>';
            }
            if ($status == "@FINANCE") {
                $status = '<span class="badge" style="background: #b70000;color: #e6ffdf; ">' . $status . '</span>';
            } elseif ($status == "active") {
                $status = '<span class="badge" style="background:#00a422; color:#e6ffdf; ">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }
            $data[] = array(
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => is_numeric($FTotal) ? number_format($FTotal, 2) : 'N/A',
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'location' => $location,
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

                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }

    // hod
    public function read_all_proposals_hod()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("suspended","approved","@HOD","@COO","@GMD", "@FINANCE","@returnedFromCM","@returnedFromCOO","@returnedFromHOD","@returnedFromCOF","@returnedFromGMD","@CFO","@CM","pending","approve","complete", "active", "reject")))
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        cashcategories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("suspended","approved","@HOD","@COO","@GMD", "@FINANCE","@returnedFromCM","@returnedFromCOO","@returnedFromHOD","@returnedFromCOF","@returnedFromGMD","@CFO","@CM","pending","approve", "active","completed", "reject")))
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        //Bind
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }
        // changes


        // end of changes

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $approve = $resend = $reject = $suspend  = $unsuspend = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
            if ($status === "@HOD") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }
            if ($status === "pending") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }
            if ($status === "pending") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }

            if (
                $status == "@HOD"
            ) {
                $status = '<span class="badge" style="background:#8f4b20; color:#e6ffdf; ">' . $status . '</span>';
            }
            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif (
                $status == "suspended"
            ) {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }
            $data[] = array(
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'location' => $location,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $reject . '
                                ' . $resend . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                            </ul>
                        </div>
                   '
            );
        }


        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }
    // 
    public function read_all_proposals_finance()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@FINANCE")))
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@FINANCE")))
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        //Bind
        $stmt->execute();
        //RowCount
        $num = $stmt->rowCount();
        $output = array();
        $data = array();
        if ($num < 1) {
            $output = array(
                "success" => false,
                "message" => "Data Not Found",
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $disburse = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if (($status === "@FINANCE") || ($status === "@FINANCE") || ($status === "@FINANCE") || ($status === "partiallyDisbursed")) {
                $disburse = '<li id="' . $id . '" class="fina_disburse"><a href="#" class="dropdown-item"><i class="far fa-money-bill-alt font-size-16 text-info me-1 "></i> Disburse</a></li>';
            }

            if ($status == "@FINANCE") {
                $status = '<span class="badge" style="background: #006c8b;color: #e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@FINANCE") {
                $status = '<span class="badge" style="background: #006c8b;color: #e6ffdf;">' . $status . '</span>';
            } elseif ($status == "@FINANCE") {
                $status = '<span class="badge" style="background: #006c8b;color: #e6ffdf;">' . $status . '</span>';
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
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                               ' . $view . '
                                ' . $disburse . '
                            </ul>
                        </div>
                   '
            );
        }
        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );
        return $output;
    }

    public function read_all_proposals_gmd()
    {
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }
        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@GMD")) OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();
        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@GMD"))OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $approve = $reject =  $amend = $suspend = $unsuspend = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
            if ($status === "@GMD") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if ($status === "pending") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }

            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            }
            if ($status == "@COO") {
                $status = '<span class="badge" style="background:#8f4b20; color:#e6ffdf; ">' . $status . '</span>';
            }
            if ($status == "@GMD") {
                $status = '<span class="badge" style="background:#849c7e; color:#e6ffdf; ">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }
            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $reject . '
                                ' . $amend . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                            </ul>
                        </div>
                   '
            );
        }

        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }



    public function read_all_proposals_coo()
    {
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }
        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@COO")) OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();
        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@COO"))OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $approve = $amend = $reject = $suspend = $unsuspend = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
            if ($status === "@COO") {
                $approve = '<li id="' . $id . '" class="activateCOO" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }

            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            }
            if ($status == "@returnedFromCOF") {
                $approve = '<li id="' . $id . '" class="activate" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Resend</a></li>';

                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if ($status == "@returnedFromCOF") {
                $status = '<span class="badge" style="background:#1e91aa; color:#e6ffdf; ">' . $status . '</span>';
            }
            if ($status === "@COO") {
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $status = '<span class="badge" style="background:#6b2300; color:#ffdfdf;">' . $status . '</span>';
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if ($status == "@COO") {
                $status = '<span class="badge" style="background:#8f4b20; color:#e6ffdf; ">' . $status . '</span>';
            }
            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $reject . '
                                ' . $suspend . '
                                ' . $amend . '
                                ' . $unsuspend . '
                            </ul>
                        </div>
                   '
            );
        }
        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }


    // hod



    // HOD APPROVE
    public function activate()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    upproved_by= :upproved_by,
                    approved_at = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);
        //clean data

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':upproved_by', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }
        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
    // HOD APPROVE
    public function activateCoo()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    cooApprove= :cooApprove,
                    coo_at = Now()
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        //clean data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':cooApprove', $this->this_user);
        $stmt->bindParam(':id', $this->id);
        //execute query
        if ($stmt->execute()) {
            return true;
        }
        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function activategmd()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    gmdApprove= :gmdApprove,
                    gmd_at = Now()
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        //clean data
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

    // create Proposal
    //Create Idea

    public function createp()
    {
        $query = ' INSERT INTO ' . $this->table . '
                   SET
                    country = :country,
                    subject = :subject, 
                    department = :department, 
                    proposal_date = :proposal_date,
                    budget_line = :budget_line,
                    introduction = :introduction, 
                    objective =:objective,
                    status = :status,
                    location =:location,
                    FTotal =:FTotal,
                    additional_doc = :additional_doc,
                    created_by = :created_by,         
                    onbehalf_of = :onbehalf_of,
                    created_at = Now()          
                ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $this->created_by = $this->this_user;
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':subject', $this->subject);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':proposal_date', $this->proposal_date);
        $stmt->bindParam(':budget_line', $this->budget_line);
        $stmt->bindParam(':introduction', $this->introduction);
        $stmt->bindParam(':objective', $this->objective);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':location', $this->location);

        $stmt->bindParam(':FTotal', $this->FTotal);
        // $stmt->bindParam(':quantity', $this->quantity);
        // $stmt->bindParam(':price', $this->price);
        // $stmt->bindParam(':total', $this->total);
        // $stmt->bindParam(':supplier', $this->supplier);


        $stmt->bindParam(':additional_doc', $this->additional_doc);
        $stmt->bindParam(':created_by', $this->created_by);
        $stmt->bindParam(':onbehalf_of', $this->onbehalf_of);
        //execute query
        if ($stmt->execute()) {
            $last_prop_id = $this->conn->lastInsertId();
            $refNo = "PROP_" . $last_prop_id;
            $updateRef = $this->conn->prepare('UPDATE ' . $this->table . ' SET refNo=:refNo WHERE id =:id ');
            $updateRef->bindParam(':refNo', $refNo);
            $updateRef->bindParam(':id', $last_prop_id);

            if ($updateRef->execute()) {
                // return true;
                foreach ($this->item as $key => $value) {
                    $item = clean_data($value);
                    $quantity = clean_data($this->quantity[$key]);
                    $price = clean_data($this->price[$key]);
                    $total = clean_data($this->total[$key]);
                    $supplier = clean_data($this->supplier[$key]);
                    $query = ' INSERT INTO proposal_items
                                    SET
                                    proposal_id = :prop_id,
                                    item = :item,
                                    quantity = :quantity,
                                    price = :price,
                                    total = :total,
                                    supplier = :supplier,
                                    created_by = :created_by,
                                    created_at = Now()
                                ';
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':prop_id', $last_prop_id);
                    $stmt->bindParam(':item', $item);
                    $stmt->bindParam(':quantity', $quantity);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':total', $total);
                    $stmt->bindParam(':supplier', $supplier);
                    $stmt->bindParam(':created_by', $this->created_by);
                    $stmt->execute();
                }
                return array(true, $refNo);
            }
        }
        return $stmt->error;
    }



    // public function upload_petty_doc()
    // {
    //     if (is_uploaded_file($_FILES['additional_doc']['tmp_name'])) {
    //         if (!file_exists('../uploads')) {
    //             mkdir('../uploads', 0777);
    //         }
    //         $extension = explode('.', $_FILES['additional_doc']['name']);
    //         $new_name = rand() . '_' . date("Y-m-d") . '.' . $extension[1];
    //         $destination = '../uploads/' . $new_name;
    //         move_uploaded_file($_FILES['additional_doc']['tmp_name'], $destination);
    //         return $new_name;
    //     }
    // }

    // public function unlink_petty_doc($request_id)
    // {
    //     $statement = $this->conn->prepare('SELECT additional_doc FROM ' . $this->table . ' WHERE id = :id');
    //     $statement->bindParam(':id', $request_id);
    //     $statement->execute();
    //     $result = $statement->fetchAll();
    //     foreach ($result as $row) {
    //         $dir = '../uploads/' . $row["additional_doc"];
    //         if ($row["additional_doc"] != '') {
    //             unlink($dir);
    //         }
    //     }
    // }

    //Get Single Idea
    public function read_single()
    {
        $query = 'SELECT b.*,
                ctr.currency,
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                u1.name AS onbehalf_of_name, 
                u1.surname AS onbehalf_of_surname,
                bc.name AS budget_line,
                dp.category AS department,
                bg.remaining_amount AS remaining_amount 
            FROM ' . $this->table . ' b
            INNER JOIN 
                countries ctr ON b.country = ctr.id
            LEFT JOIN 
                users u ON b.created_by = u.userId 
            LEFT JOIN 
                users u1 ON b.onbehalf_of = u1.userId
            LEFT JOIN 
                budget_categories bc ON b.budget_line = bc.id 
            LEFT JOIN
                department_categories dp ON b.department = dp.id 
            LEFT JOIN  
                budget bg ON b.remaining_amount = bg.id   
            WHERE b.id = :id
            LIMIT 1';
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        // Set Properties
        $this->id = $row['id'];
        $this->refNo = $row['refNo'];
        $this->subject = $row["subject"];
        $this->department = $row["department"];
        $this->introduction = $row["introduction"];
        $this->budget_line = $row["budget_line"];
        $this->remaining_amount = number_format($row["remaining_amount"], 2) . ' ' . $row['currency'];
        $this->objective = $row["objective"];
        $this->onbehalf_of = $row["onbehalf_of"];
        $this->additional_doc = $row["additional_doc"];
        $this->proposal_date = $row["proposal_date"];
        $this->returnReason = $row["returnReason"];
        $this->FTotal = number_format($row["FTotal"], 2) . ' ' . $row['currency'];
        $query2 = 'SELECT * FROM proposal_items WHERE proposal_id = :proposal_id';
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(':proposal_id', $this->id);
        $stmt2->execute();

        $this->comment = array();
        $this->proposalItem_array = array();
        // Fetch all proposal items
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $this->proposalItem_array[] = $row2;
        }
        $query3 = 'SELECT users.name AS commentBy, proposal_comment.comment AS comment,proposal_comment.date AS date
           FROM proposal_comment
           LEFT JOIN users ON proposal_comment.commentBy = users.userId
           WHERE proposal_comment.proposal_id = :proposal_id';
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->bindParam(':proposal_id', $this->id);
        $stmt3->execute();
        $this->comment = array();
        $this->commentArray = array();
        // Fetch all proposal items
        while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $this->commentArray[] = $row3;
        }
        return true; // Indicate success
    }

    //Update Idea
    public function update()
    {
        // Update main proposal information
        $query = 'UPDATE ' . $this->table . '
        SET
            subject = :subject, 
            department = :department, 
            proposal_date = :proposal_date,
            budget_line = :budget_line,
            introduction = :introduction, 
            objective = :objective,
            onbehalf_of = :onbehalf_of,
            FTotal = :FTotal,
            updated_by = :updated_by, 
            updated_at = NOW()
        WHERE 
            id = :id';

        $stmt = $this->conn->prepare($query);
        $this->updated_by = $this->this_user;
        $stmt->bindParam(':subject', $this->subject);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':proposal_date', $this->proposal_date);
        $stmt->bindParam(':budget_line', $this->budget_line);
        $stmt->bindParam(':introduction', $this->introduction);
        $stmt->bindParam(':objective', $this->objective);
        $stmt->bindParam(':FTotal', $this->FTotal);
        $stmt->bindParam(':onbehalf_of', $this->onbehalf_of);
        $stmt->bindParam(':updated_by', $this->updated_by);
        $stmt->bindParam(':id', $this->id);

        if (!$stmt->execute()) {
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
        $del = $this->conn->prepare('DELETE FROM proposal_items WHERE proposal_id = :id');
        $del->bindParam(':id', $this->id);

        if (!$del->execute()) {
            return false;
        }
        $stmt2 = $this->conn->prepare('INSERT INTO proposal_items
        SET
            proposal_id = :prop_id,
            item = :item,
            quantity = :quantity,
            price = :price,
            total = :total,
            supplier = :supplier,
            created_by = :created_by,
            created_at = NOW()');

        foreach ($this->item as $key => $value) {
            $item = clean_data($value);
            $quantity = clean_data($this->quantity[$key]);
            $price = clean_data($this->price[$key]);
            $total = clean_data($this->total[$key]);
            $supplier = clean_data($this->supplier[$key]);

            $stmt2->bindParam(':prop_id', $this->id);
            $stmt2->bindParam(':item', $item);
            $stmt2->bindParam(':quantity', $quantity);
            $stmt2->bindParam(':price', $price);
            $stmt2->bindParam(':total', $total);
            $stmt2->bindParam(':supplier', $supplier);
            $stmt2->bindParam(':created_by', $this->this_user);

            $stmt2->execute();
        }
        return true;
    }


    public function delete()
    {
        $query = ' DELETE FROM ' . $this->table . ' WHERE id = :id ';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
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

    // SOME FUNCTIONS     

    public function is_proposal_exists()
    {
        $query = 'SELECT id FROM ' . $this->table . ' WHERE id = :id';
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
    public function approve_comment()
    {
        // First query for UPDATE
        $query = 'UPDATE ' . $this->table . '
            SET
                status = :status,
                upproved_by = :upproved_by,
                approved_at = NOW()
            WHERE 
                id = :id
        ';

        // Second query for INSERT
        $query2 = 'INSERT INTO proposal_comment
                SET
                proposal_id = :proposal_id,
                comment = :comment,
                commentBy = :commentBy,
                date = NOW()
            ';
        // Prepare the second statement (INSERT)
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(':proposal_id', $this->id);
        // $stmt2->bindParam(':comment', $this->comment);
        $stmt2->bindParam(':comment', $this->comment, PDO::PARAM_NULL | PDO::PARAM_STR);
        $stmt2->bindParam(':commentBy', $this->this_user);

        // Prepare the first statement (UPDATE)
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':upproved_by', $this->this_user);
        $stmt->bindParam(':id', $this->id);
        $this->conn->beginTransaction();
        try {
            $stmt->execute();
            $stmt2->execute();
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());

            return false;
        }
    }


    public function returned_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    returnReason = :returnReason,
                    returnedBy = :returnedBy,
                    returnedDate = Now()
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);

        //clean data
        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':returnReason', $this->returnReason);
        $stmt->bindParam(':returnedBy', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }



    // COM (COUNTRY MANAGER )
    public function read_all_proposals_com()
    {
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }
        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@CM")) OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();
        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@CM"))OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $approve = $amend = $reject = $resend = $suspend  = $unsuspend = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
            if ($status === "@CM") {
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
                $approve = '<li id="' . $id . '" class="activateCOO" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if ($status == "pending") {
                $status = '<span class="badge" style="background:#6b2300; color:#ff0303">' . $status . '</span>';
            }
            if ($status == "@CM") {
                $status = '<span class="badge" style="background:#185936; color:#fff; ">' . $status . '</span>';
            } elseif ($status == "suspended") {
                $status = '<span class="badge" style="background:#6b2300; color:#ffffff;">' . $status . '</span>';
            }
            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $reject . '
                                ' . $amend . '
                                ' . $resend . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                            </ul>
                        </div>
                   '
            );
        }
        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }
    //read  cof 
    public function read_all_proposals_cof()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.subject LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
        if (isset($this->columnIndex) && !empty($this->columnIndex)) {
            $order = ' ' . $this->columnIndex . ' ' . $this->columnSortOrder . ' ';
        }

        //limit
        $limit = '';
        if (isset($this->rowperpage) && $this->rowperpage != -1 && !empty($this->rowperpage)) {
            $limit = 'LIMIT ' . $this->start . ', ' . $this->rowperpage;
        }
        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget b1 ON b.budget_line = b1.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@CFO")) OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':suspendedBy', $this->this_user);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();
        //Create Query
        $query = 'SELECT b.*, 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS onbehalf_of_name, 
                        u1.surname AS onbehalf_of_surname,
                        bc.name AS budgetCategory,
                        b.price 
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId 
                    LEFT JOIN 
                        users u1 ON b.onbehalf_of = u1.userId 
                    LEFT JOIN 
                        budget_categories bc ON b.budget_line = bc.id 
                    WHERE ((b.status IN ("@CFO"))OR (b.status IN ("suspended") AND b.suspendedBy = :suspendedBy))
                    AND ( b.country = :country)
                            ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';
        //Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':suspendedBy', $this->this_user);
        $stmt->bindParam(':country', $this->country);
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
                "draw" => intval($this->draw),
                "recordsTotal" => $num,
                "recordsFiltered" => $number_of_rows,
                "data" => []
            );
            return $output;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $view = $approve = $amend = $reject = $suspend = $unsuspend = $update = '';
            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
            if ($status === "@CFO") {
                $approve = '<li id="' . $id . '" class="activateCOF" ><a href="#" class="dropdown-item" ><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                $amend = '<li id="' . $id . '" class="amend"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
            }
            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ff0303;">' . $status . '</span>';
            }
            if (($status === "suspended") && ($suspendedBy == $this->this_user)) {
                $status = '<span class="badge" style="background:#6b2300; color:#ffdfdf;">' . $status . '</span>';
                $unsuspend = '<li id="' . $id . '" class="unsuspend"><a href="#" class="dropdown-item"><i class="fas fas fa-play font-size-16 text-danger me-1 "></i> Unsuspend</a></li>';
            }
            if ($status === "@CFO") {
                $suspend = '<li id="' . $id . '" class="suspend"><a href="#" class="dropdown-item"><i class="fas fas fa-pause font-size-16 text-info me-1 "></i> Suspend</a></li>';
            }
            if ($status == "@CFO") {
                $status = '<span class="badge" style="background: #185936;color: #e6ffdf;">' . $status . '</span>';
            }
            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2),
                'onbehalf_of' => $onbehalf_of_surname . ' ' . $onbehalf_of_name,
                'created_by' => $created_by_surname . ' ' . $created_by_name,
                'created_at' => $created_at,
                'proposal_date' => $proposal_date,
                'budget_line' => $budgetCategory,
                'objective' => $objective,
                'status' => $status,
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $approve . '
                                ' . $update . '
                                ' . $suspend . '
                                ' . $unsuspend . '
                                ' . $reject . '
                                ' . $amend . '
                                
                            </ul>
                        </div>
                   '
            );
        }
        $output = array(
            "success" => true,
            "message" => "Data Found",
            "draw" => intval($this->draw),
            "recordsTotal" => $num,
            "recordsFiltered" => $number_of_rows,
            "data" => $data
        );

        return $output;
    }
    // get single
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
    public function amend_request()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    returnReason = :returnReason,
                    returnedBy = :returnedBy,
                    returnDate = Now()
                WHERE 
                    id = :id
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':returnReason', $this->returnReason);
        $stmt->bindParam(':returnedBy', $this->this_user);
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
                        charges = :charges,
                        financeRelease = :financeRelease,
                        financeReleaseDate = Now()
                    WHERE 
                        id = :id
                ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':charges', $this->charges);
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

    public function finance_disburse_cheque_request()
    {
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    bank_name = :bank_name,
                    charges = :charges,
                    cheque_number = :cheque_number,
                    financeRelease = :financeRelease,
                    financeReleaseDate = Now()
                WHERE 
                    id = :id
            ';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':bank_name', $this->bank_name);
        $stmt->bindParam(':charges', $this->charges);
        $stmt->bindParam(':cheque_number', $this->cheque_number);
        $stmt->bindParam(':financeRelease', $this->this_user);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);

        return false;
    }


    public function get_finance_balance($country)
    {
        $statement = $this->conn->prepare('SELECT * FROM finacebalance WHERE country =:country');
        $statement->bindParam(':country', $country);
        $statement->execute();
        $num = $statement->rowCount();
        if (
            $num > 0
        ) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return $row;
        } else {
            return false;
        }
    }

    // get budget per proposal
    public function get_depart_cat_budget($country, $department, $budget_category, $remaining_amount)
    {
        $getBudget = $this->conn->prepare('SELECT * FROM budget WHERE status IN("active") AND country =:country AND 
                                        department=:department AND budget_category=:budget_category AND remaining_amount =: remaining_amount');
        $getBudget->bindParam(':country', $country);
        $getBudget->bindParam(':department', $department);
        $getBudget->bindParam(':budget_category', $budget_category);
        $getBudget->bindParam(':remaining_amount', $remaining_amount);
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


    public function read_proposal_report($country, $department, $budget_line, $statusCheck, $status, $created_by, $DateFrom, $DateTo)
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    b.refNo LIKE "%' . $this->searchValue . '%" OR 
                    b.status LIKE "%' . $this->searchValue . '%" OR 
                    u.name LIKE "%' . $this->searchValue . '%" OR 
                    u.surname LIKE "%' . $this->searchValue . '%" 
                    
                ) ';
        }
        //Order
        $order = 'b.id DESC';
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
        if (
            $country != ""
        ) {
            $where_qr .=  ' AND (b.country = :country)';
        }
        if ($department != "") {
            $where_qr .=  ' AND (b.department = :department)';
        }
        if ($budget_line != "") {
            $where_qr .=  ' AND (b.budget_line = :budget_line)';
        }
        if (
            $status != ""
        ) {
            $where_qr .=  ' AND (b.status = :status)';
        }
        if (
            $created_by != ""
        ) {
            $where_qr .=  ' AND (b.created_by = :created_by)';
        }
        if ($statusCheck != "") {
            if ($statusCheck == "disbursed") {
                $where_qr .=  ' AND ( b.status IN ("completed", "cleared", "clearing", "clearDenied") ) ';
            } elseif ($statusCheck == "inprogress") {
                $where_qr .=  ' AND ( b.status IN ("approved","pending","@GMD","@HOD","@COO","@CFO","@FinanceFromHOD","@FINANCE", "suspended", "@returnedFromHOD", "returnedFromFinance", "returnedFromCOO", "returnedFromGMD", "@FinanceFromCOO", "@FinanceFromGMD", "@GMDfromCOO", "@GMDfromCOO") )';
            } elseif ($statusCheck == "declined") {
                $where_qr .=  ' AND ( b.status IN ("rejected", "cancelled") )';
            }
        }

        if (
            $DateFrom != "" && $DateTo != ""
        ) {
            if (($statusCheck != "") && ($statusCheck == "disbursed")) {
                $where_qr .= ' AND( cast(b.financeReleaseDate as date) BETWEEN :dateFrom AND :dateTo )';
            } elseif (($statusCheck != "") || ($statusCheck == "inprogress")) {
                $where_qr .= ' AND( cast(b.created_at as date) BETWEEN :dateFrom AND :dateTo )';
            } elseif (($statusCheck != "") || ($statusCheck == "declined")) {
                $where_qr .= ' AND( cast(b.created_at as date) BETWEEN :dateFrom AND :dateTo )';
            }
        }

        //Select Count
        $sql = 'SELECT count(*) 
                FROM 
                    ' . $this->table . ' b 
                    LEFT JOIN 
                        users u ON b.created_by = u.userId
                    LEFT JOIN 
                        users u1 ON b.financeRelease = u1.userId
                    LEFT JOIN 
                        countries ctry ON b.country = ctry.id
                    LEFT JOIN
                    budget_categories bc ON b.budget_line = bc.id 

                    WHERE 1
                        ' . $where_qr . '
                        ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        if ($country != "") $counted->bindParam(':country', $country);
        if ($department != "") $counted->bindParam(':department', $department);
        if ($budget_line != "") $counted->bindParam(':budget_line', $budget_line);
        if ($status != "") $counted->bindParam(':status', $status);
        if ($created_by != "") $counted->bindParam(':created_by', $created_by);
        if (
            $DateFrom != "" && $DateTo != ""
        ) {
            $counted->bindParam(':dateFrom', $DateFrom);
            $counted->bindParam(':dateTo', $DateTo);
        }
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();
        //Create Query
        $query = ' SELECT
                        b.*,
                        b.id AS pty_id,
                        ctry.currency ,
                        u.name AS req_by_name,
                        u.name AS onbehalf_of,
                        u.surname AS req_by_surname,
                        u1.name AS disb_by_name,
                        u1.surname AS disb_by_surname,
                        bc.name AS budget_line
                        
                    FROM
                        ' . $this->table . ' b
                    LEFT JOIN 
                        users u ON b.created_by = u.userId
                    LEFT JOIN 
                        users u1 ON b.financeRelease = u1.userId
                    LEFT JOIN 
                        countries ctry ON b.country = ctry.id
                    LEFT JOIN 
                    budget_categories bc ON b.budget_line = bc.id 
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
        if ($budget_line != "") $stmt->bindParam(':budget_line', $budget_line);
        if ($status != "") $stmt->bindParam(':status', $status);
        if ($created_by != "") $stmt->bindParam(':created_by', $created_by);
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

            if (
                $status == "@HOD" ||
                $status == "@FINANCE" ||
                $status == "@returnedFromCM" ||
                $status == "pending" ||
                $status == "@CM" ||
                $status == "@GMD"
            ) {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif (
                $status == "@returnedFromCOO" ||
                $status == "@CFO" ||
                $status == "returnedFromFinance" ||
                $status == "returnedFromCOO" ||
                $status == "returnedFromGMD"
            ) {
                $status = '<span class="badge" style="background:#b70000; color:#ffebf7; ">' . $status . '</span>';
            } elseif ($status == "@FinanceFromHOD") {
                $status = '<span class="badge" style="background:#d66700; color:#fffbeb;">' . $status . '</span>';
            } elseif ($status == "@COO") {
                $status = '<span class="badge" style="background:#b70000; color:#4c4c4c;">' . $status . '</span>';
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
                'subject' => $subject,
                'FTotal' => number_format($FTotal, 2) . ' ' . $currency,
                'budget_line' => $budget_line ? $budget_line : 'N/A',
                'status' => $status,
                'created_by' => $req_by_surname . ' ' . $req_by_name,
                'onbehalf_of' => $onbehalf_of ? $onbehalf_of : 'N/A',
                'created_at' => date("F j, Y g:i a", strtotime($created_at))
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
    // email  configuration
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
