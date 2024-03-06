<?php

class Budget
{
    //DB Stuff
    private $conn;
    private $table = "budget";
    public $user_details;

    public $this_user;
    public $error;
    //User properties
    public $id;
    public $country;
    public $department;
    public $department_val;
    public $budget_category;
    public $budget_category_val;
    public $start_date;
    public $end_date;
    public $initial_amount;
    public $initial_amount_Val;
    public $topup_amount;
    public $topup_amount_Val;
    public $deducted_amount;
    public $deducted_amount_Val;
    public $total_amount;
    public $total_amount_Val;
    public $used_amount;
    public $used_amount_Val;
    public $remaining_amount;
    public $remaining_amount_Val;
    public $status;
    public $has_notified;
    public $insterted_by;
    public $inserted_at;
    public $updated_by;
    public $updated_at;

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
    public function read_all_budgets()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    d.category LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
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
                    ' . $this->table . ' b 
                LEFT JOIN 
                    users u1 ON b.insterted_by = u1.userId 
                LEFT JOIN 
                    department_categories d ON b.department = d.id 
                INNER JOIN 
                    countries ctr ON b.country = ctr.id
                LEFT JOIN 
                    budget_categories bc ON b.budget_category = bc.id 
                WHERE ( (b.status NOT IN ("expired") ) AND ( b.country = :country ) ) 
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT 
                        b.*, u1.name AS name1, 
                        u1.surname AS surname1, 
                        d.category AS budgetDepartment, 
                        bc.name AS budgetCategory,                        
                        ctr.currency
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        users u1 ON b.insterted_by = u1.userId 
                    LEFT JOIN 
                        department_categories d ON b.department = d.id 
                    INNER JOIN 
                        countries ctr ON b.country = ctr.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_category = bc.id 
                    WHERE ( (b.status NOT IN ("expired") ) AND ( b.country = :country ) ) 
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

            $view = $update = $activate = $delete = '';

            $view = '<li id="' . $id . '" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';

            if ($status === "pending") {
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                $activate = '<li id="' . $id . '" class="activate"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Activate</a></li>';
                $delete = '<li id="' . $id . '" class="delete"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Delete</a></li>';
            }


            if ($status == "pending") {
                $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">' . $status . '</span>';
            } elseif ($status == "active") {
                $status = '<span class="badge" style="background:#00a422; color:#e6ffdf; ">' . $status . '</span>';
            } elseif ($status == "expired") {
                $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">' . $status . '</span>';
            }



            $data[] = array(
                'id' => $id,
                'department' => $budgetDepartment,
                'budget_category' => $budgetCategory,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'remaining_amount' => number_format($remaining_amount, 2) . ' ' . $row['currency'],
                'status' => $status,
                'insterted_by' => $surname1 . ' ' . $name1,
                'inserted_at' => date("F j, Y g:i a", strtotime($inserted_at)),
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $view . '                                
                                ' . $update . '                             
                                ' . $activate . '                       
                                ' . $delete . '
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




    public function read_all_budgets_at_finance()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    d.category LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
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
                    ' . $this->table . ' b 
                LEFT JOIN 
                    department_categories d ON b.department = d.id 
                LEFT JOIN 
                    countries ctr ON b.country = ctr.id
                LEFT JOIN 
                    budget_categories bc ON b.budget_category = bc.id 
                WHERE ( (b.status IN ("active") ) AND ( b.country = :country ) ) 
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT 
                        b.*,
                        d.category AS budgetDepartment, 
                        bc.name AS budgetCategory,                        
                        ctr.currency
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        department_categories d ON b.department = d.id 
                    LEFT JOIN 
                        countries ctr ON b.country = ctr.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_category = bc.id 
                    WHERE ( (b.status IN ("active") ) AND ( b.country = :country ) ) 
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

            $total_amount = '<span style="color:#00a401;">' . number_format($total_amount, 2) . ' ' . $currency . '</span>';
            $used_amount = '<span style="color:#d60000;">' . number_format($used_amount, 2) . ' ' . $currency . '</span>';
            $remaining_amount = '<span style="color:#0087a4;">' . number_format($remaining_amount, 2) . ' ' . $currency . '</span>';

            $data[] = array(
                'id' => $id,
                'department' => $budgetDepartment,
                'budget_category' => $budgetCategory,
                'total_amount' => $total_amount,
                'used_amount' => $used_amount,
                'remaining_amount' => $remaining_amount,
                'start_date' => $start_date,
                'end_date' => $end_date
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


    public function read_all_budgets_at_hod()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    d.category LIKE "%' . $this->searchValue . '%"
                ) ';
        }
        //Order
        $order = 'b.id DESC ';
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
                    ' . $this->table . ' b 
                LEFT JOIN 
                    department_categories d ON b.department = d.id 
                LEFT JOIN 
                    countries ctr ON b.country = ctr.id
                LEFT JOIN 
                    budget_categories bc ON b.budget_category = bc.id 
                WHERE ( (b.status IN ("active") ) AND ( b.country = :country ) AND ( b.department = :department) ) 
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':country', $this->country);
        $counted->bindParam(':department', $this->department);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT 
                        b.*,
                        d.category AS budgetDepartment, 
                        bc.name AS budgetCategory,                        
                        ctr.currency
                    FROM ' . $this->table . ' b
                    LEFT JOIN 
                        department_categories d ON b.department = d.id 
                    LEFT JOIN 
                        countries ctr ON b.country = ctr.id
                    LEFT JOIN 
                        budget_categories bc ON b.budget_category = bc.id 
                    WHERE ( (b.status IN ("active") ) AND ( b.country = :country ) AND ( b.department = :department) ) 
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . ' 
            ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':department', $this->department);

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

            $total_amount = '<span style="color:#00a401;">' . number_format($total_amount, 2) . ' ' . $currency . '</span>';
            $used_amount = '<span style="color:#d60000;">' . number_format($used_amount, 2) . ' ' . $currency . '</span>';
            $remaining_amount = '<span style="color:#0087a4;">' . number_format($remaining_amount, 2) . ' ' . $currency . '</span>';

            $data[] = array(
                'id' => $id,
                'department' => $budgetDepartment,
                'budget_category' => $budgetCategory,
                'total_amount' => $total_amount,
                'used_amount' => $used_amount,
                'remaining_amount' => $remaining_amount,
                'start_date' => $start_date,
                'end_date' => $end_date
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
                        b.*, 
                        ctr.currency,
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2,
                        d.category AS budgetDepartment, 
                        bc.name AS budgetCategory
                    FROM ' . $this->table . ' b
                        LEFT JOIN 
                            countries ctr ON b.country = ctr.id
                        LEFT JOIN 
                            users u1 ON b.insterted_by = u1.userId
                        LEFT JOIN 
                            users u2 ON b.updated_by = u2.userId
                        LEFT JOIN 
                            department_categories d ON b.department = d.id 
                        LEFT JOIN 
                            budget_categories bc ON b.budget_category = bc.id 
                    WHERE b.id = :id
                    LIMIT 1
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
        $this->department = $row["budgetDepartment"];
        $this->department_val = $row["department"];
        $this->budget_category = $row["budgetCategory"];
        $this->budget_category_val = $row["budget_category"];
        $this->start_date = $row["start_date"];
        $this->end_date    = $row["end_date"];
        $this->initial_amount = number_format($row["initial_amount"], 2) . ' ' . $row['currency'];
        $this->initial_amount_Val = $row["initial_amount"];
        $this->topup_amount    = number_format($row["topup_amount"], 2) . ' ' . $row['currency'];
        $this->topup_amount_Val = $row["topup_amount"];
        $this->deducted_amount = number_format($row["deducted_amount"], 2) . ' ' . $row['currency'];
        $this->deducted_amount_Val = $row["deducted_amount"];
        $this->total_amount = number_format($row["total_amount"], 2) . ' ' . $row['currency'];
        $this->total_amount_Val    = $row["total_amount"];
        $this->used_amount = number_format($row["used_amount"], 2) . ' ' . $row['currency'];
        $this->used_amount_Val = $row["used_amount"];
        $this->remaining_amount    = number_format($row["remaining_amount"], 2) . ' ' . $row['currency'];
        $this->remaining_amount_Val = $row["remaining_amount"];
        $this->status = $row["status"];
        $this->insterted_by    = $row["name1"] . " " . $row["surname1"];
        $this->inserted_at = $row["inserted_at"];
        $this->updated_at = $row["updated_at"] ? $row["updated_at"] : "N/A";
        $this->updated_by = $row["updated_at"] ? $row["name2"] . " " . $row["surname2"] : "N/A";

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
                    budget_category = :budget_category, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    initial_amount = :initial_amount, 
                    total_amount = :total_amount, 
                    remaining_amount = :remaining_amount, 
                    status = :status, 
                    insterted_by = :insterted_by,
                    inserted_at = Now() 
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        $this->insterted_by = $this->this_user;

        //bind data
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':budget_category', $this->budget_category);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':initial_amount', $this->initial_amount);
        $stmt->bindParam(':total_amount', $this->initial_amount);
        $stmt->bindParam(':remaining_amount', $this->initial_amount);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':insterted_by', $this->insterted_by);

        //execute query
        if ($stmt->execute()) {
            // return true;
            return true;
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
                    department = :department, 
                    budget_category = :budget_category, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    initial_amount = :initial_amount, 
                    total_amount = :total_amount, 
                    remaining_amount = :remaining_amount, 
                    updated_by = :updated_by, 
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        $this->updated_by = $this->this_user;

        //bind data
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':budget_category', $this->budget_category);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':initial_amount', $this->initial_amount);
        $stmt->bindParam(':total_amount', $this->initial_amount);
        $stmt->bindParam(':remaining_amount', $this->initial_amount);
        $stmt->bindParam(':updated_by', $this->updated_by);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }


    //Update 
    public function activate_budget()
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    status = :status,
                    updated_by = :updated_by,
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':updated_by', $this->this_user);
        $stmt->bindParam(':id', $this->id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function delete_budget()
    {
        //query
        $query = ' DELETE FROM ' . $this->table . ' WHERE id = :id ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        //bind data
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

    public function is_budget_exists()
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

    public function is_budget_active()
    {
        $query =  'SELECT status FROM ' . $this->table . ' WHERE id = :id ';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['status'] == "active") {
            return true;
        } else {
            return false;
        }
    }

    public function is_budget_request_exists($id)
    {
        $query =  'SELECT * FROM hod_budget_requests WHERE id = :id ';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $num = $stmt->rowCount();
        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        } else {
            return false;
        }
    }

    public function is_same_budget_exists($country, $department, $budget_category, $start_date, $end_date, $and = '')
    {
        $query = ' SELECT * FROM budget 
                    WHERE 
                        budget_category = :budget_category
                        AND department = :department
                        AND country = :country
						AND ( :dateStart BETWEEN start_date AND end_date
								OR :dateEnd BETWEEN start_date AND end_date
								OR start_date BETWEEN :dateStart AND :dateEnd )		
						AND (status NOT IN ("expired"))	' . $and . '
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':budget_category', $budget_category);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':dateStart', $start_date);
        $stmt->bindParam(':dateEnd', $end_date);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return false;
        }
    }


    public function hod_requests_budget($country, $department, $budget_to_deduct, $budget_to_increase, $amount, $description, $status, $requested_by)
    {
        $query = 'INSERT INTO hod_budget_requests
                SET
                    country=:country, 
                    department=:department, 
                    budget_to_deduct=:budget_to_deduct, 
                    budget_to_increase=:budget_to_increase, 
                    amount=:amount, 
                    description=:description, 
                    status=:status, 
                    requested_by=:requested_by,
                    request_date=Now()
            ';
        //prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':budget_to_deduct', $budget_to_deduct);
        $stmt->bindParam(':budget_to_increase', $budget_to_increase);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':requested_by', $requested_by);

        //execute query
        if ($stmt->execute()) {
            $last_req_id = $this->conn->lastInsertId();
            $refNo = "BDG_" . $last_req_id;
            $updateRef = $this->conn->prepare('UPDATE hod_budget_requests SET refNo=:refNo WHERE id =:id ');
            $updateRef->bindParam(':refNo', $refNo);
            $updateRef->bindParam(':id', $last_req_id);

            if ($updateRef->execute()) {
                // return true;
                return array(true, $refNo);
            }
        } else {
            return false;
        }
    }


    public function hod_budget_requests_logs()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    hb.refNo LIKE "%' . $this->searchValue . '%" OR 
                    hb.status LIKE "%' . $this->searchValue . '%" OR 
                    bc1.name LIKE "%' . $this->searchValue . '%" OR 
                    u2.name LIKE "%' . $this->searchValue . '%" OR 
                    u2.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' hb.id ASC ';
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
                    hod_budget_requests hb 
                LEFT JOIN 
                    users u1 ON hb.requested_by = u1.userId 
                LEFT JOIN 
                    users u2 ON hb.approver = u2.userId 
                LEFT JOIN 
                    budget_categories bc ON hb.budget_to_deduct = bc.id 
                LEFT JOIN 
                    budget_categories bc1 ON hb.budget_to_increase = bc1.id 
                WHERE hb.requested_by = :requested_by AND hb.status IN ("pending")
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':requested_by', $this->this_user);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT hb.*, bc.name AS budget_from, bc1.name AS budget_to,
						u1.name AS name1, u1.surname AS surname1, 
						u2.name AS name2, u2.surname AS surname2
                    FROM 
                        hod_budget_requests hb 
                    LEFT JOIN 
                        users u1 ON hb.requested_by = u1.userId 
                    LEFT JOIN 
                        users u2 ON hb.approver = u2.userId 
                    LEFT JOIN 
                        budget_categories bc ON hb.budget_to_deduct = bc.id 
                    LEFT JOIN 
                        budget_categories bc1 ON hb.budget_to_increase = bc1.id 
                    WHERE hb.requested_by = :requested_by AND hb.status IN ("pending")
                        ' . $searchQuery . '
                    ORDER BY
                        ' . $order . '
                    ' . $limit . '  
			    ';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind
        $stmt->bindParam(':requested_by', $this->this_user);

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

            $update = $cancel = '';

            if ($status === "pending") {
                $update = '<li id="' . $id . '" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                $cancel = '<li id="' . $id . '" class="cancel"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Cancel</a></li>';
            }


            if ($status == "pending") {
                $status = '<span class="badge" style="background:#e30000; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "approved") {
                $status = '<span class="badge" style="background:#307e00; color:#ffffff;">' . $status . '</span>';
            } elseif ($status == "rejected") {
                $status = '<span class="badge" style="background:#0d0d0d; color:#ffffff;">' . $status . '</span>';
            }


            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'amount' => number_format($amount, 2),
                'budget_from' => $budget_from,
                'budget_to' => $budget_to,
                'description' => $description,
                'status' => $status,
                'requested_by' => $surname1 . ' ' . $name1,
                'request_date' => date("F j, Y g:i a", strtotime($request_date)),
                'approver' => $approver_date ? $surname2 . ' ' . $name2 : 'N/A',
                'approver_date' => $approver_date ? date("F j, Y g:i a", strtotime($approver_date)) : 'N/A',
                'approver_comment' => $approver_comment ? $approver_comment : 'N/A',
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $update . '                                
                                ' . $cancel . '
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


    public function hod_to_coo_budget_requests_logs()
    {
        // Search 
        $searchQuery = '';
        if (isset($this->searchValue)) {
            $searchQuery = ' AND (
                    hb.refNo LIKE "%' . $this->searchValue . '%" OR 
                    hb.status LIKE "%' . $this->searchValue . '%" OR 
                    bc1.name LIKE "%' . $this->searchValue . '%" OR 
                    u2.name LIKE "%' . $this->searchValue . '%" OR 
                    u2.surname LIKE "%' . $this->searchValue . '%" 
                ) ';
        }
        //Order
        $order = ' hb.id ASC ';
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
                    hod_budget_requests hb 
                LEFT JOIN 
                    users u1 ON hb.requested_by = u1.userId 
                LEFT JOIN 
                    users u2 ON hb.approver = u2.userId 
                LEFT JOIN 
                    budget_categories bc ON hb.budget_to_deduct = bc.id 
                LEFT JOIN 
                    budget_categories bc1 ON hb.budget_to_increase = bc1.id 
                WHERE hb.country = :country AND hb.status IN ("pending")
                    ' . $searchQuery . '
                ';
        $counted = $this->conn->prepare($sql);
        $counted->bindParam(':country', $this->country);
        $counted->execute();
        $number_of_rows = $counted->fetchColumn();

        //Create Query
        $query = 'SELECT hb.*, bc.name AS budget_from, bc1.name AS budget_to,
						u1.name AS name1, u1.surname AS surname1, 
						u2.name AS name2, u2.surname AS surname2
                    FROM 
                        hod_budget_requests hb 
                    LEFT JOIN 
                        users u1 ON hb.requested_by = u1.userId 
                    LEFT JOIN 
                        users u2 ON hb.approver = u2.userId 
                    LEFT JOIN 
                        budget_categories bc ON hb.budget_to_deduct = bc.id 
                    LEFT JOIN 
                        budget_categories bc1 ON hb.budget_to_increase = bc1.id 
                    WHERE hb.country = :country AND hb.status IN ("pending")
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

            $approve = $reject = '';

            if ($status === "pending") {
                $approve = '<li id="' . $id . '" class="approve_budget"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $reject = '<li id="' . $id . '" class="reject_budget"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
            }


            if ($status == "pending") {
                $status = '<span class="badge" style="background:#e30000; color:#ffffff;">' . $status . '</span>';
            }

            $data[] = array(
                'id' => $id,
                'refNo' => $refNo,
                'amount' => number_format($amount, 2),
                'budget_from' => $budget_from,
                'budget_to' => $budget_to,
                'description' => $description,
                'status' => $status,
                'requested_by' => $surname1 . ' ' . $name1,
                'request_date' => date("F j, Y g:i a", strtotime($request_date)),
                'approver' => $approver_date ? $surname2 . ' ' . $name2 : 'N/A',
                'approver_date' => $approver_date ? date("F j, Y g:i a", strtotime($approver_date)) : 'N/A',
                'approver_comment' => $approver_comment ? $approver_comment : 'N/A',
                'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ' . $approve . '                                
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

    public function reject_budget_request($status, $approver_comment, $id)
    {
        //query
        $query = ' UPDATE hod_budget_requests
                SET
                    status = :status,
                    approver_comment = :approver_comment,
                    approver = :approver,
                    approver_date = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':approver_comment', $approver_comment);
        $stmt->bindParam(':approver', $this->this_user);
        $stmt->bindParam(':id', $id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function approve_budget_request($status, $id)
    {
        //query
        $query = ' UPDATE hod_budget_requests
                SET
                    status = :status,
                    approver = :approver,
                    approver_date = Now()
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':approver', $this->this_user);
        $stmt->bindParam(':id', $id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function update_deduct_budget_on_approve($deducted_amount, $total_amount, $remaining_amount, $id)
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    deducted_amount = :deducted_amount,
                    total_amount = :total_amount,
                    remaining_amount = :remaining_amount
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':deducted_amount', $deducted_amount);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':remaining_amount', $remaining_amount);
        $stmt->bindParam(':id', $id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    public function update_topup_budget_on_approve($topup_amount, $total_amount, $remaining_amount, $id)
    {
        //query
        $query = ' UPDATE ' . $this->table . '
                SET
                    topup_amount = :topup_amount,
                    total_amount = :total_amount,
                    remaining_amount = :remaining_amount
                WHERE 
                    id = :id
            ';

        //prepare statement
        $stmt = $this->conn->prepare($query);

        //bind data
        $stmt->bindParam(':topup_amount', $topup_amount);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':remaining_amount', $remaining_amount);
        $stmt->bindParam(':id', $id);

        //execute query
        if ($stmt->execute()) {
            return true;
        }

        //print if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
}