<?php

    class IndividualBsc {
        //DB Stuff
        private $conn;
        private $table = "strategy_individual_bsc";
        public $user_details;

        public $this_user;
        public $error;

        //Table properties
        public $id;
        public $country;
        public $country_name;
        public $department;
        public $department_name;
        public $division;
        public $division_name;
        public $group_strategy_id;
        public $bsc_owner;
        public $bsc_owner_name;
        public $strategy_name;
        public $year;
        public $status;
        public $location;
        public $parameter_id;
        public $bsc_parameter_id;
        public $parameter_weight;
        public $line_approved_at;
        public $line_approved_by;
        public $division_approved_at;
        public $division_approved_by;
        public $hr_approved_at;
        public $hr_approved_by;
        public $returned_at;
        public $returned_by;
        public $return_reason;
        public $rejected_at;
        public $rejected_by;
        public $reject_reason;
        public $created_at;
        public $created_by;
        public $submitted_at;
        public $submitted_by;
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

        public function read_all_individual_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended")AND bsc.bsc_owner = :bsc_owner
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':bsc_owner', $this->bsc_owner);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        bsc.*, c.country AS country_name, d.department_name,                
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS bsc_owner_name, 
                        u1.surname AS bsc_owner_surname
                    FROM
                        '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND bsc.bsc_owner = :bsc_owner
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind ID
            $stmt->bindParam(':bsc_owner', $this->bsc_owner);

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

                $view = $update = $delete = $submit = $review = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-bsc-individual-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft" || $status === "returned") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $submit = '<li id="'.$id.'" class="submit_for_approval"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i> Submit for Approval</a></li>';
                    $delete = '<li id="'.$id.'" class="delete"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }

                if( ($status === "approved" || $status === "Q1_evaluated" || $status === "Q2_evaluated" || $status === "Q3_evaluated") ){
                    $review = '<li id="'.$id.'" class="submit_for_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i> Submit for Evaluation</a></li>';
                }


                if($status == "pending") {
                    $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                } elseif($status == "returned"){
                    $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                    $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                } elseif($status == "saved_as_draft") {
                    $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                } elseif($status == "rejected") {
                    $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                    $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                }
                

                $data[] = array(
                    'id' => $id,
                    'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                    'country' => $country_name,
                    'department' => $department_name,
                    'year' => $year,
                    'status' => $status,
                    'location' => $location,
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
                                '.$review.' 
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

        public function read_bsc_approval_logs(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    al.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'al.id DESC ';
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
                        strategy_bsc_approval_logs al
                    INNER JOIN 
                        users u ON al.approved_by = u.userId 
                    LEFT JOIN
                        strategy_individual_bsc bsc ON al.individual_bsc_id = bsc.id
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND bsc.bsc_owner = :bsc_owner
                    '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':bsc_owner', $this->bsc_owner);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        al.*, u.name AS approved_by_name, u.surname AS approved_by_surname
                    FROM
                        strategy_bsc_approval_logs al
                    INNER JOIN 
                        users u ON al.approved_by = u.userId 
                    LEFT JOIN
                        strategy_individual_bsc bsc ON al.individual_bsc_id = bsc.id
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") AND bsc.bsc_owner = :bsc_owner
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind ID
            $stmt->bindParam(':bsc_owner', $this->bsc_owner);

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

                $data[] = array(
                    'id' => $id,
                    'approved_by' => $approved_by_surname . ' ' . $approved_by_name ,
                    'location' => $location,
                    'status' => $status,
                    'supervisor_comments' => $supervisor_comments,
                    'approved_at' => date("F j, Y g:i a", strtotime($approved_at))
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

        public function read_bsc_approval_logs_by_id(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    al.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'al.id DESC ';
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
                        strategy_bsc_approval_logs al
                    INNER JOIN 
                        users u ON al.approved_by = u.userId 
                    WHERE al.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':individual_bsc_id', $this->id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        al.*, u.name AS approved_by_name, u.surname AS approved_by_surname
                    FROM
                        strategy_bsc_approval_logs al
                    INNER JOIN 
                        users u ON al.approved_by = u.userId 
                    WHERE al.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind ID
            $stmt->bindParam(':individual_bsc_id', $this->id);

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

                $data[] = array(
                    'id' => $id,
                    'approved_by' => $approved_by_surname . ' ' . $approved_by_name ,
                    'location' => $location,
                    'status' => $status,
                    'supervisor_comments' => $supervisor_comments,
                    'approved_at' => date("F j, Y g:i a", strtotime($approved_at))
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

        public function read_individual_bsc_parameters(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%"
                ) ';
            }
            //Order
            $order = 'ip.id DESC ';
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
                    strategy_individual_bsc_param ip
                LEFT JOIN
                    strategy_bsc_parameters bp ON ip.bsc_parameter_id = bp.id 
                WHERE ip.individual_bsc_id = :individual_bsc_id
                    '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':individual_bsc_id', $this->id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        ip.*, bp.bsc_parameter_name
                    FROM
                        strategy_individual_bsc_param ip
                    LEFT JOIN
                        strategy_bsc_parameters bp ON ip.bsc_parameter_id = bp.id 
                    WHERE ip.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind ID
            $stmt->bindParam(':individual_bsc_id', $this->id);

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

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name ,
                    'parameter_weight' => $parameter_weight
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

        public function read_all_section_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                    AND bsc.location IN("@Section") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                    AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department AND  bsc.unit = :unit AND bsc.section = :section
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->bindParam(':department', $this->department);
            $counted->bindParam(':unit', $this->unit);
            $counted->bindParam(':section', $this->section);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
            bsc.*, c.country AS country_name, d.department_name,                
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                u1.name AS bsc_owner_name, 
                u1.surname AS bsc_owner_surname
            FROM
                '.$this->table.' bsc
            INNER JOIN 
                users u ON bsc.created_by = u.userId 
            LEFT JOIN
                countries c ON bsc.country = c.id 
            LEFT JOIN
                departments d ON bsc.department = d.id
            LEFT JOIN
                users u1 ON bsc.bsc_owner = u1.userId
            LEFT JOIN
                strategies s ON bsc.group_strategy_id = s.id
            WHERE s.status NOT IN ("ended") 
                AND bsc.location IN("@Section") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department AND  bsc.unit = :unit AND bsc.section = :section
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
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':unit', $this->unit);
            $stmt->bindParam(':section', $this->section);

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

                $view = $update = $submit = $delete = $amend = $reject = $approve = $review = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-bsc-section-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                    
                    if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                        $review = '<li id="'.$id.'" class="submit_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i>Submit Evaluation</a></li>';
                    }
                    else{
                        $approve = '<li id="'.$id.'" class="approve_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                        $amend = '<li id="'.$id.'" class="revert_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                        $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                    } 
                    
                    //Customize status color for HR                
                    if($status == "pending") {
                        $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                    } elseif($status == "returned"){
                        $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                    } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                        $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                    } elseif($status == "saved_as_draft") {
                        $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                    } elseif($status == "rejected") {
                        $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                    } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                        $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                    }
                    
    
                    $data[] = array(
                        'id' => $id,
                        'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                        'country' => $country_name,
                        'department' => $department_name,
                        'year' => $year,
                        'status' => $status,
                        'location' => $location,
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
                                    '.$review.'
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

        public function read_all_unit_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                    AND bsc.location IN("@Unit") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                    AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department AND  bsc.unit = :unit
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->bindParam(':department', $this->department);
            $counted->bindParam(':unit', $this->unit);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
            bsc.*, c.country AS country_name, d.department_name,                
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                u1.name AS bsc_owner_name, 
                u1.surname AS bsc_owner_surname
            FROM
                '.$this->table.' bsc
            INNER JOIN 
                users u ON bsc.created_by = u.userId 
            LEFT JOIN
                countries c ON bsc.country = c.id 
            LEFT JOIN
                departments d ON bsc.department = d.id
            LEFT JOIN
                users u1 ON bsc.bsc_owner = u1.userId
            LEFT JOIN
                strategies s ON bsc.group_strategy_id = s.id
            WHERE s.status NOT IN ("ended") 
                AND bsc.location IN("@Unit") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department AND  bsc.unit = :unit
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
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':unit', $this->unit);

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

                $view = $update = $submit = $delete = $amend = $reject = $approve = $review = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-bsc-unit-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                    
                    if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                        $review = '<li id="'.$id.'" class="submit_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i>Submit Evaluation</a></li>';
                    }
                    else{
                        $approve = '<li id="'.$id.'" class="approve_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                        $amend = '<li id="'.$id.'" class="revert_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                        $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                    } 
                    
                    //Customize status color for HR                
                    if($status == "pending") {
                        $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                    } elseif($status == "returned"){
                        $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                    } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                        $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                    } elseif($status == "saved_as_draft") {
                        $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                    } elseif($status == "rejected") {
                        $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                    } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                        $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                    }
                    
    
                    $data[] = array(
                        'id' => $id,
                        'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                        'country' => $country_name,
                        'department' => $department_name,
                        'year' => $year,
                        'status' => $status,
                        'location' => $location,
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
                                    '.$review.'
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

        public function read_all_department_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                    AND bsc.location IN("@HOD") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                            AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->bindParam(':department', $this->department);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
            bsc.*, c.country AS country_name, d.department_name,                
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                u1.name AS bsc_owner_name, 
                u1.surname AS bsc_owner_surname
            FROM
                '.$this->table.' bsc
            INNER JOIN 
                users u ON bsc.created_by = u.userId 
            LEFT JOIN
                countries c ON bsc.country = c.id 
            LEFT JOIN
                departments d ON bsc.department = d.id
            LEFT JOIN
                users u1 ON bsc.bsc_owner = u1.userId
            LEFT JOIN
                strategies s ON bsc.group_strategy_id = s.id
            WHERE s.status NOT IN ("ended") 
                AND bsc.location IN("@HOD") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND bsc.country = :country AND bsc.division = :division AND  bsc.department = :department
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
            $stmt->bindParam(':department', $this->department);

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

                $view = $update = $submit = $delete = $amend = $reject = $approve = $review = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-bsc-department-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                    
                    if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                        $review = '<li id="'.$id.'" class="submit_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i>Submit Evaluation</a></li>';
                    }
                    else{
                        $approve = '<li id="'.$id.'" class="approve_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                        $amend = '<li id="'.$id.'" class="revert_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                        $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                    } 
                    
                    //Customize status color for HR                
                    if($status == "pending") {
                        $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                    } elseif($status == "returned"){
                        $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                    } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                        $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                    } elseif($status == "saved_as_draft") {
                        $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                    } elseif($status == "rejected") {
                        $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                    } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                        $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                    }
                    
    
                    $data[] = array(
                        'id' => $id,
                        'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                        'country' => $country_name,
                        'department' => $department_name,
                        'year' => $year,
                        'status' => $status,
                        'location' => $location,
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
                                    '.$review.'
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

        public function read_all_division_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                    AND bsc.location IN("@MainFunction") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                    AND bsc.country = :country AND bsc.division = :division
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                         bsc.*, c.country AS country_name, d.department_name,                
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS bsc_owner_name, 
                        u1.surname AS bsc_owner_surname
                    FROM
                        '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                        AND bsc.location IN("@MainFunction") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND bsc.country = :country AND bsc.division = :division
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
    
                    $view = $amend = $reject = $approve = $review = ''; 
    
                    $view = '<li id="'.$id.'" class="view"><a href="view-bsc-function-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                    
                    if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                        $review = '<li id="'.$id.'" class="submit_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i>Submit Evaluation</a></li>';
                    }
                    else{
                        $approve = '<li id="'.$id.'" class="approve_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                        $amend = '<li id="'.$id.'" class="revert_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                        $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                    } 
                    
                    //Customize status color for HR                
                    if($status == "pending") {
                        $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                    } elseif($status == "returned"){
                        $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                    } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                        $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                    } elseif($status == "saved_as_draft") {
                        $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                    } elseif($status == "rejected") {
                        $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                    } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                        $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                    }
                    
    
                    $data[] = array(
                        'id' => $id,
                        'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                        'country' => $country_name,
                        'department' => $department_name,
                        'year' => $year,
                        'status' => $status,
                        'location' => $location,
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
                                    '.$review.'
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

        public function read_all_gmd_bsc(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u1.name LIKE "%'.$this->searchValue.'%" OR 
                    u1.surname LIKE "%'.$this->searchValue.'%" OR 
                    bsc.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'bsc.id DESC ';
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
                    '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                    AND bsc.location IN("@GMD") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        bsc.*, c.country AS country_name, d.department_name,                
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        u1.name AS bsc_owner_name, 
                        u1.surname AS bsc_owner_surname
                    FROM
                        '.$this->table.' bsc
                    INNER JOIN 
                        users u ON bsc.created_by = u.userId 
                    LEFT JOIN
                        countries c ON bsc.country = c.id 
                    LEFT JOIN
                        departments d ON bsc.department = d.id
                    LEFT JOIN
                        users u1 ON bsc.bsc_owner = u1.userId
                    LEFT JOIN
                        strategies s ON bsc.group_strategy_id = s.id
                    WHERE s.status NOT IN ("ended") 
                        AND bsc.location IN("@GMD") AND bsc.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
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

                $view = $amend = $reject = $approve = $review = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-bsc-gmd-initiative-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                    $review = '<li id="'.$id.'" class="submit_evaluation"><a href="#" class="dropdown-item"><i class="dripicons-forward font-size-16 text-info me-1 "></i>Submit Evaluation</a></li>';
                }
                else{
                    $approve = '<li id="'.$id.'" class="approve_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-check font-size-16 text-info me-1 "></i> Approve</a></li>';
                    $amend = '<li id="'.$id.'" class="revert_bsc"><a href="#" class="dropdown-item"><i class="fas fas fa-reply font-size-16 text-info me-1 "></i> Revert for Amending</a></li>';
                    $reject = '<li id="'.$id.'" class="reject"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Reject</a></li>';
                } 
                
                //Customize status color for HR                
                if($status == "pending") {
                    $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                } elseif($status == "returned"){
                    $status = '<span class="badge" style="background:#840000; color:#ffebf7; ">'.$status.'</span>';
                } elseif($status == "approved" || $status == "Q1_evaluated" || $status == "Q2_evaluated" || $status == "Q3_evaluated") {
                    $status = '<span class="badge" style="background:#00a422; color:#e6ffdf;">'.$status.'</span>';
                } elseif($status == "saved_as_draft") {
                    $status = '<span class="badge" style="background:#949494; color:#f2f2f2;">'.$status.'</span>';
                } elseif($status == "rejected") {
                    $status = '<span class="badge" style="background:#101010; color:#e6e6e6;">'.$status.'</span>';
                } elseif($status == "Q1_evaluating" || $status == "Q2_evaluating" || $status == "Q3_evaluating" || $status == "Q4_evaluating") {
                    $status = '<span class="badge" style="background:#7e0061; color:#ffb3f8;">'.$status.'</span>';
                }
                

                $data[] = array(
                    'id' => $id,
                    'bsc_owner' => $bsc_owner_surname . ' ' . $bsc_owner_name ,
                    'country' => $country_name,
                    'department' => $department_name,
                    'year' => $year,
                    'status' => $status,
                    'location' => $location,
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
                                '.$review.'
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
                        d.department_name, 
                        c.country AS country_name, 
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2,
                        u3.name AS name3,
                        u3.surname AS surname3
                    FROM 
                        ' . $this->table . ' i
                    INNER JOIN 
                        users u1 ON i.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.userId
                    LEFT JOIN
                        departments d ON i.department = d.id
                    LEFT JOIN
                        countries c ON i.country = c.id
                    LEFT JOIN
                        users u3 ON i.bsc_owner = u3.userId
                    WHERE
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
            $this->bsc_owner = $row["bsc_owner"];
            $this->bsc_owner_name = $row["name3"]." ".$row["surname3"];
            $this->country = $row["country"];
            $this->country_name = $row["country_name"];
			$this->department_name = $row["department_name"];
			$this->department = $row["department"];
			$this->year = $row["year"];
			$this->status 	= $row["status"];
			$this->location 	= $row["location"];
			$this->created_by	= $row["name1"]." ".$row["surname1"];
			$this->created_at	= $row["created_at"];
			$this->updated_by	= $row["name2"]." ".$row["surname2"];
			$this->updated_at 	= $row["updated_at"];
			$this->bsc_owner	= $row["name3"]." ".$row["surname3"];

            return $stmt;
        }

        public function read_single_parameter() {
            //create query
            $query =  'SELECT
                        *
                    FROM 
                        strategy_individual_bsc_param
                    WHERE
                        id = :id
                    LIMIT 0,1
                ';

            //Prepare Statement
            $stmt = $this->conn->prepare($query);

            //Bind ID
            $stmt->bindParam(':id', $this->parameter_id);

            //Execute query
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //Set Properties
            $this->parameter_id = $row['id'];
            $this->bsc_parameter_id = $row["bsc_parameter_id"];
            $this->parameter_weight = $row["parameter_weight"];

            return $stmt;
        }

        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    country = :country,
                    division = :division,
                    department = :department,
                    group_strategy_id = :group_strategy_id,
                    bsc_owner = :bsc_owner,
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
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':group_strategy_id', $this->group_strategy_id);
            $stmt->bindParam(':bsc_owner', $this->bsc_owner);
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
                    bsc_owner = :bsc_owner,
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
            $stmt->bindParam(':bsc_owner', $this->bsc_owner);
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

        //Create Idea
        public function create_parameter() {
            //query
            $query = ' INSERT INTO strategy_individual_bsc_param
                SET
                    individual_bsc_id = :individual_bsc_id,
                    bsc_parameter_id = :bsc_parameter_id,
                    parameter_weight = :parameter_weight
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':individual_bsc_id', $this->id);
            $stmt->bindParam(':bsc_parameter_id', $this->bsc_parameter_id);
            $stmt->bindParam(':parameter_weight', $this->parameter_weight);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            return $stmt->error;
        }

        public function update_parameter() {
            //query
            $query = ' UPDATE strategy_individual_bsc_param
                SET
                    individual_bsc_id = :individual_bsc_id,
                    bsc_parameter_id = :bsc_parameter_id,
                    parameter_weight = :parameter_weight
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':individual_bsc_id', $this->id);
            $stmt->bindParam(':bsc_parameter_id', $this->bsc_parameter_id);
            $stmt->bindParam(':parameter_weight', $this->parameter_weight);
            $stmt->bindParam(':id', $this->parameter_id);

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
                    submitted_by = :submitted_by,                   
                    submitted_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->submitted_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':submitted_by', $this->submitted_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function submit_evaluation() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->submitted_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            // $stmt->bindParam(':submitted_by', $this->submitted_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function approve_bsc() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    location = :location
                WHERE 
                    id = :id
            ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();

            // Insert into another table query
            $insertQuery = 'INSERT INTO strategy_bsc_approval_logs (individual_bsc_id, status, location, approved_at, approved_by)
            VALUES (:individual_bsc_id, :status, :location, Now(), :approved_by)';

            // Prepare and execute insert statement
            $stmtInsert = $this->conn->prepare($insertQuery);
            // Bind values for insertion
            $value1 = $this->this_user;

            $stmtInsert->bindParam(':individual_bsc_id', $this->id);
            $stmtInsert->bindParam(':status', $this->status);
            $stmtInsert->bindParam(':location', $this->location);
            $stmtInsert->bindParam(':approved_by', $value1);
            $stmtInsert->execute();

            // Check if both update and insert were successful
            if($stmt->rowCount() > 0 && $stmtInsert->rowCount() > 0) {
                return true; // Success
            } else {
                return false; // Error
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            // return false;
        }

        public function return_dep_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    return_reason = :return_reason,
                    returned_by = :returned_by,                   
                    returned_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->returned_by = $this->this_user;

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':return_reason', $this->return_reason);
            $stmt->bindParam(':returned_by', $this->returned_by);
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

        public function delete_parameter() {

            $query = ' DELETE FROM strategy_individual_bsc_param WHERE id = :id ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':id', $this->parameter_id);            

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
        
        public function is_bsc_exists() {
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

        public function is_bsc_parameter_exists() {
            $query =  'SELECT id FROM strategy_individual_bsc_param WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->parameter_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function check_bsc_status($individual_bsc_id) {
            $query =  'SELECT status FROM ' . $this->table . ' WHERE id = :id ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $individual_bsc_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['status'];
            } else {
                return false;
            }
        }
        
        public function check_dupli($mycountry, $year, $bsc_owner, $and ='') {
            $query =  'SELECT * FROM ' . $this->table . ' WHERE country = :country AND bsc_owner = :bsc_owner AND year = :year '.$and.'';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':country', $mycountry);
            $stmt->bindParam(':bsc_owner', $bsc_owner);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function check_duplicate_parameters($individual_bsc_id, $bsc_parameter_id, $and ='') {
            $query =  'SELECT * FROM strategy_individual_bsc_param 
                        WHERE individual_bsc_id = :individual_bsc_id AND bsc_parameter_id = :bsc_parameter_id '.$and.'';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->bindParam(':bsc_parameter_id', $bsc_parameter_id);
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

        public function get_active_strategy() {
            $query =  'SELECT id FROM strategies WHERE status IN ("active")';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();            
            $num = $stmt->rowCount();

            if($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['id'];
            } else {
                return false;
            }
        }

        public function check_my_business_plan($group_strategy_id, $this_user) {
        
            $query = 'SELECT * FROM strategy_country_level
                        WHERE group_strategy_id = :group_strategy_id 
                        AND created_by = :created_by AND status IN ("approved")
                    ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_strategy_id', $group_strategy_id);
            $stmt->bindParam(':created_by', $this_user);
            $stmt->execute();
            
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }
        public function check_supervisor_bsc($group_strategy_id, $and) {
            
            $query = 'SELECT b.* FROM strategy_individual_bsc b
                        LEFT JOIN approval_hierarchy ah ON b.bsc_owner = ah.user_id
                        LEFT JOIN approvals a ON ah.approval_id = a.id
                        WHERE b.group_strategy_id = :group_strategy_id 
                        '.$and.'
                        AND status IN ("approved")
                ';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_strategy_id', $group_strategy_id);
            $stmt->execute();
            
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
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
    