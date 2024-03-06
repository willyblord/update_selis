<?php

    class CountryStrategy {
        //DB Stuff
        private $conn;
        private $table = "strategy_country_level";
        public $user_details;

        public $this_user;
        public $error;

        //Table properties
        public $id;
        public $country;
        public $division;
        public $country_name;
        public $department;
        public $department_name;
        public $group_strategy_id;
        public $strategy_name;
        public $year;
        public $status;
        public $division_approved_at;
        public $division_approved_by;
        public $coo_approved_at;
        public $coo_approved_by;
        public $returned_at;
        public $returned_by;
        public $return_reason;
        public $rejected_at;
        public $rejected_by;
        public $reject_reason;
        public $created_at;
        public $created_by;
        public $updated_at;
        public $updated_by;


        //Pagination properties
        public $draw;
        public $start;
        public $rowperpage; // Rows display per page
        public $columnIndex; // Column index
        public $columnName; // Column name
        public $columnSortOrder; // asc or desc
        public $searchValue; // Search value


        //condtructor
        public function __construct($db) {
            $this->conn = $db;
        }

        public function read_main_function_strategies(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    d.division_name LIKE "%'.$this->searchValue.'%" OR 
                    i.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'i.id DESC ';
            if(isset($this->columnIndex) && !empty($this->columnIndex)){
                $order = ' '.$this->columnIndex.' '.$this->columnSortOrder.' ';
            }

            //limit
            $limit = '';
            if(isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)){
                $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
            }

            //Select Count
            $sql = 'SELECT count(*) 
                FROM 
                    '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        countries c ON i.country = c.id 
                    LEFT JOIN
                        divisions d ON i.division = d.id
                    LEFT JOIN
                        strategies s ON i.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND i.country = :country AND i.division = :division
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*, c.country AS country_name, d.division_name, s.strategy_name,                   
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        countries c ON i.country = c.id 
                    LEFT JOIN
                        divisions d ON i.division = d.id
                    LEFT JOIN
                        strategies s ON i.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND i.country = :country AND i.division = :division
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind ID
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':division', $this->division);

            //Execute Query
            $stmt->execute();


            //RowCount
            $num = $stmt->rowCount();

            $output = array();
            $data = array();

            //Check if any Idea
            if($num < 1) {

                $output = array(
                    "success" => false,
                    "message" => "Data Not Found",
                    "draw"				=>	intval($this->draw),
                    "recordsTotal"		=> 	$num,
                    "recordsFiltered"	=>	$number_of_rows,
                    "data"			    =>	[]
                );
                return $output;

            }

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $view = $update = $delete = $submit = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-business-plan-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                               
                if( ($status === "saved_as_draft" || $status === "returnedFromDivision" || $status === "returnedFromCOO" ) ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $submit = '<li id="'.$id.'" class="submit_for_approval"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i> Submit for Approval</a></li>';
                    $delete = '<li id="'.$id.'" class="delete"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }


                if($status == "@COO") {
                    $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                } elseif($status == "returnedFromCOO"){
                    $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                } elseif($status == "approved") {
                    $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                } elseif($status == "saved_as_draft") {
                    $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                } elseif($status == "rejected") {
                    $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                }
                

                $data[] = array(
                    'id' => $id,
                    'strategy_name' => $strategy_name,
                    'country' => $country_name,
                    'division_name' => $division_name,
                    'year' => $year,
                    'status' => $status,
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'                                
                                '.$update.'                               
                                '.$submit.'                           
                                '.$delete.'  
                            </ul>
                        </div>
                   '
                );
            }


            $output = array(
                "success"      => true,
                "message"       => "Data Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        public function read_all_group_strategies(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    d.division_name LIKE "%'.$this->searchValue.'%" OR 
                    i.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'i.id DESC ';
            if(isset($this->columnIndex) && !empty($this->columnIndex)){
                $order = ' '.$this->columnIndex.' '.$this->columnSortOrder.' ';
            }

            //limit
            $limit = '';
            if(isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)){
                $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
            }

            //Select Count
            $sql = 'SELECT count(*) 
                FROM 
                    '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        countries c ON i.country = c.id 
                    LEFT JOIN
                        divisions d ON i.division = d.id
                    LEFT JOIN
                        strategies s ON i.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND i.status IN ("@COO")
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*, c.country AS country_name, d.division_name, s.strategy_name,                   
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        countries c ON i.country = c.id 
                    LEFT JOIN
                        divisions d ON i.division = d.id
                    LEFT JOIN
                        strategies s ON i.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND i.status IN ("@COO")
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            //Execute Query
            $stmt->execute();


            //RowCount
            $num = $stmt->rowCount();

            $output = array();
            $data = array();

            //Check if any Idea
            if($num < 1) {

                $output = array(
                    "success" => false,
                    "message" => "Data Not Found",
                    "draw"				=>	intval($this->draw),
                    "recordsTotal"		=> 	$num,
                    "recordsFiltered"	=>	$number_of_rows,
                    "data"			    =>	[]
                );
                return $output;

            }

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $view = $amend = $reject = $approve = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-strategy-group-annual-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                $approve = '<li id="'.$id.'" class="coo_approve_strategy"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                $amend = '<li id="'.$id.'" class="coo_revert_strategy"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                

                if($status == "@COO") {
                    $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                } elseif($status == "returnedFromCOO"){
                    $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                } elseif($status == "approved") {
                    $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                } elseif($status == "saved_as_draft") {
                    $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                } elseif($status == "rejected") {
                    $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                }
                

                $data[] = array(
                    'id' => $id,
                    'strategy_name' => $strategy_name,
                    'country' => $country_name,
                    'division_name' => $division_name,
                    'year' => $year,
                    'status' => $status,
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'                                
                                '.$approve.'                            
                                '.$amend.'                                 
                                '.$reject.'   
                            </ul>
                        </div>
                   '
                );
            }


            $output = array(
                "success"      => true,
                "message"       => "Data Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        //Get Single Idea
        public function read_single() {
            //create query
            $query =  'SELECT
                        i.*, 
                        d.division_name,
                        c.country AS country_name, 
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2,
                        s.strategy_name,
                        u3.name AS name3,
                        u3.surname AS surname3,
                        u4.name AS name4,
                        u4.surname AS surname4,
                        u6.name AS name6,
                        u6.surname AS surname6
                    FROM 
                        ' . $this->table . ' i
                    INNER JOIN 
                        users u1 ON i.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.userId
                    LEFT JOIN
                        divisions d ON i.division = d.id
                    LEFT JOIN
                        countries c ON i.country = c.id
                    LEFT JOIN
                        strategies s ON i.group_strategy_id = s.id
                    LEFT JOIN
                        users u3 ON i.returned_by = u3.userId
                    LEFT JOIN
                        users u4 ON i.rejected_by = u4.userId
                    LEFT JOIN
                        users u6 ON i.coo_approved_by = u6.userId
                    WHERE
                        s.status NOT IN ("ended") AND 
                        i.id = :id
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
            $this->group_strategy_id = $row["group_strategy_id"];
            $this->strategy_name = $row["strategy_name"];
            $this->country = $row["country"];
            $this->country_name = $row["country_name"];
			$this->division_name = $row["division_name"];
			$this->division = $row["division"];
			$this->year = $row["year"];
			$this->status 	= $row["status"];
			$this->created_by	= $row["name1"]." ".$row["surname1"];
			$this->created_at	= $row["created_at"];
			$this->updated_by	= $row["name2"]." ".$row["surname2"];
			$this->updated_at 	= $row["updated_at"];
			$this->returned_by	= $row["name3"]." ".$row["surname3"];
			$this->returned_at 	= $row["returned_at"];
			$this->return_reason= $row["return_reason"];
			$this->rejected_by	= $row["name4"]." ".$row["surname4"];
			$this->rejected_at 	= $row["rejected_at"];
			$this->reject_reason= $row["reject_reason"];
			$this->coo_approved_by	= $row["name6"]." ".$row["surname6"];
			$this->coo_approved_at = $row["coo_approved_at"];

            return $stmt;
        }

        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    country = :country,
                    division = :division,
                    group_strategy_id = :group_strategy_id,
                    year = :year,
                    status = :status,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':division', $this->division);
            $stmt->bindParam(':group_strategy_id', $this->group_strategy_id);
            $stmt->bindParam(':year', $this->year);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }


        //  //Update Idea
         public function update() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    country = :country,
                    division = :division,
                    group_strategy_id = :group_strategy_id,
                    year = :year,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':division', $this->division);
            $stmt->bindParam(':group_strategy_id', $this->group_strategy_id);
            $stmt->bindParam(':year', $this->year);
            $stmt->bindParam(':updated_by', $this->updated_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function submit_initiative() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':updated_by', $this->updated_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function approve_dep_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    division_approved_by = :division_approved_by,                   
                    division_approved_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->division_approved_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':division_approved_by', $this->division_approved_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function approve_division_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    coo_approved_by = :coo_approved_by,                   
                    coo_approved_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->coo_approved_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':coo_approved_by', $this->coo_approved_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function return_dep_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    returned_by = :returned_by, 
                    return_reason = :return_reason,                  
                    returned_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->returned_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':returned_by', $this->returned_by);
            $stmt->bindParam(':return_reason', $this->return_reason);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function reject_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    reject_reason = :reject_reason,
                    rejected_by = :rejected_by,                   
                    rejected_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->rejected_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':reject_reason', $this->reject_reason);
            $stmt->bindParam(':rejected_by', $this->rejected_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function delete() {

            $query = ' DELETE FROM '.$this->table.' WHERE id = :id ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':id', $this->id);            

            try {
                $stmt->execute();
                return true;
            } catch (PDOException $e) {
                if($e->getCode() == 23000){ // Cannot delete or update a parent row
                    return "You need to first delete initiatives attached to this!";
                } else {
                    return $e->getMessage();
                }
            }
        }

        // SOME FUNCTIONS     
        
        public function is_strategy_exists() {
            $query =  'SELECT id FROM ' . $this->table . ' WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        
        public function check_dupli($mycountry, $my_division_id, $year, $and ='') {
            $query =  'SELECT * FROM ' . $this->table . ' WHERE country = :country AND division = :division AND year = :year '.$and.'';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':country', $mycountry);
            $stmt->bindParam(':division', $my_division_id);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function check_strategy_year($year) {
            $query =  'SELECT year_range FROM strategies WHERE status IN ("active")';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();            
            $num = $stmt->rowCount();

            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $extr = explode("-", $row['year_range'], 2);
                $first = $extr[0];
                $second = $extr[1];

                if( ($first <= $year) && ($year <= $second) ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        public function check_strategy_status() {
            $query =  'SELECT `status` FROM ' . $this->table . ' WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['status'];
            } else {
                return false;
            }
        }


        public function save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender )
        {

            $body = '<html><head>';
            $body .=	'<title>'.$title.'</title>';
            $body .=	'</head>';
            $body .=	' <body style="font-size:14px;">';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">'.$message.' </p><br/>';
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

            if ($statement->execute()) 
            {
                return true;
            }
        }

    }
    