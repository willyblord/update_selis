<?php

    class BusinessInitiative {
        //DB Stuff
        private $conn;
        private $table = "strategy_initiatives";
        public $user_details;

        public $this_user;
        public $error;

        //Table properties
        public $id;
        public $country;
        public $department;
        public $division;
        public $country_strategy_id;
        public $pillar_id;
        public $initiative_id;
        public $strategy_pillar;
        public $initiative;
        public $strategic_initiative;
        public $own_initiative;
        public $business_category;
        public $value_impact;
        public $target;
        public $timeline;
        public $approved_by;
        public $approved_at;
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

        
        public function read_all_department_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    sp.pillar_name LIKE "%'.$this->searchValue.'%" OR 
                    si.group_initiative LIKE "%'.$this->searchValue.'%" OR 
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
                        strategy_group_initiatives si ON i.initiative_id = si.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON si.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id 
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND i.country_strategy_id = :country_strategy_id  AND c.country = :country AND c.division = :division
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country_strategy_id', $this->country_strategy_id);
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*, p.strategy_pillar, si.group_initiative, sp.pillar_name,                  
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        c.status
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN 
                        strategy_group_initiatives si ON i.initiative_id = si.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON si.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id 
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND i.country_strategy_id = :country_strategy_id AND c.country = :country AND c.division = :division
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':country_strategy_id', $this->country_strategy_id);
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

                $view = $update = $delete = '';

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft" || $status === "returnedFromCOO") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }

                $data[] = array(
                    'id' => $id,
                    'pillar' => htmlspecialchars_decode($pillar_name),
                    'initiative' => htmlspecialchars_decode($group_initiative),
                    'value_impact' => htmlspecialchars_decode($value_impact),
                    'target' => htmlspecialchars_decode($target),
                    'timeline' => $timeline ? $timeline : '-',
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

        public function read_all_group_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    sp.pillar_name LIKE "%'.$this->searchValue.'%" OR 
                    si.group_initiative LIKE "%'.$this->searchValue.'%" OR 
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
                        strategy_group_initiatives si ON i.initiative_id = si.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON si.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id 
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND i.country_strategy_id = :country_strategy_id AND c.status IN ("@COO")
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':country_strategy_id', $this->country_strategy_id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*, p.strategy_pillar, si.group_initiative, sp.pillar_name,                      
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        c.status
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN 
                        strategy_group_initiatives si ON i.initiative_id = si.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON si.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id 
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND i.country_strategy_id = :country_strategy_id AND c.status IN ("@COO")
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':country_strategy_id', $this->country_strategy_id);

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

                $view = $update = $delete = $progress = '';

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                $data[] = array(
                    'id' => $id,
                    'pillar' => htmlspecialchars_decode($pillar_name),
                    'initiative' => htmlspecialchars_decode($group_initiative),
                    'value_impact' => htmlspecialchars_decode($value_impact),
                    'target' => htmlspecialchars_decode($target),
                    'timeline' => $timeline ? $timeline : '-',
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'          
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
                        p.strategy_pillar, p.id AS pillar_id, si.group_initiative, sp.pillar_name,
                        u1.name AS created_by_name,
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name,
                        u2.surname AS updated_by_surname,
                        d.division_name,
                        c.country AS country_name
                    FROM 
                        ' . $this->table . ' i
                    INNER JOIN 
                        users u1 ON i.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.userId
                    LEFT JOIN 
                        strategy_group_initiatives si ON i.initiative_id = si.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON si.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_country_level cs ON i.country_strategy_id = cs.id 
                    LEFT JOIN
                        divisions d ON cs.division = d.id
                    LEFT JOIN
                        countries c ON cs.country = c.id                        
                    LEFT JOIN
                        strategies st ON cs.group_strategy_id = st.id
                    WHERE
                        st.status NOT IN ("ended") AND 
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
            $this->country_strategy_id = $row["country_strategy_id"];
            $this->country = $row["country_name"];
            $this->division = $row["division_name"];
			$this->pillar_id = $row["pillar_id"];
			$this->initiative_id = $row["initiative_id"];
			$this->strategic_initiative = htmlspecialchars_decode($row["group_initiative"]);
			$this->strategy_pillar = htmlspecialchars_decode($row["pillar_name"]);
			$this->target  = htmlspecialchars_decode($row["target"]);
			$this->value_impact  = htmlspecialchars_decode($row["value_impact"]);
			$this->timeline  = $row["timeline"];
			$this->created_by	= $row["created_by_name"]." ".$row["created_by_surname"];
			$this->created_at	= date("F j, Y g:i a", strtotime($row['created_at']));
			$this->updated_by = $row['updated_by_name'] ? $row['updated_by_name'] . ' ' . $row['updated_by_surname'] : 'N/A';
            $this->updated_at = $row['updated_at'] ? date("F j, Y g:i a", strtotime($row['updated_at'])) : 'N/A';


            return $stmt;
        }

        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    country_strategy_id = :country_strategy_id,
                    initiative_id = :initiative_id,
                    target = :target,
                    value_impact = :value_impact,
                    timeline = :timeline,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':country_strategy_id', $this->country_strategy_id);
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
            $stmt->bindParam(':timeline', $this->timeline);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }

        public function upload() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    country_strategy_id = :country_strategy_id,
                    initiative = :initiative,
                    target = :target,
                    value_impact = :value_impact,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':country_strategy_id', $this->country_strategy_id);
            $stmt->bindParam(':initiative', $this->own_initiative);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
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
                    initiative_id = :initiative_id,
                    target = :target,
                    value_impact = :value_impact,
                    timeline = :timeline,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
            $stmt->bindParam(':timeline', $this->timeline);
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


        public function delete() {

            $queryCmt = ' DELETE FROM strategy_initiative_comments WHERE initiative_id = :id ';
            $stmtCmt = $this->conn->prepare($queryCmt);
            $stmtCmt->bindParam(':id', $this->id);
            $stmtCmt->execute();

            //query
            $query = ' DELETE FROM '.$this->table.' WHERE id = :id ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        // SOME FUNCTIONS  
        
        public function create_own_initiative($business_category, $group_initiative, $pillar_id, $type) {
            //query
            $query = ' INSERT INTO strategy_group_initiatives
                SET
                    pillar_id = :pillar_id,
                    business_category = :business_category,
                    group_initiative = :group_initiative,
                    type = :type,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':pillar_id', $pillar_id);
            $stmt->bindParam(':business_category', $business_category);
            $stmt->bindParam(':group_initiative', $group_initiative);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                $last_init_id = $this->conn->lastInsertId();
                return $last_init_id;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }
        
        public function is_initiative_exists() {
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

        public function get_3year_strategy_id($country_strategy_id) {
            $query =  'SELECT group_strategy_id FROM strategy_country_level WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $country_strategy_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['group_strategy_id'];
            } else {
                return false;
            }
        }

        public function is_same_pillar_exists($country_strategy_id, $and = ''){
            $query =  'SELECT pillar_id FROM ' . $this->table . ' 
                        WHERE pillar_id=:pillar_id AND country_strategy_id = :country_strategy_id '.$and.' 
                    ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pillar_id', $this->pillar_id);
            $stmt->bindParam(':country_strategy_id', $country_strategy_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

        public function is_same_initiative_exists($country_strategy_id, $and = ''){
            $query =  'SELECT own_initiative FROM ' . $this->table . ' 
                        WHERE own_initiative=:own_initiative AND country_strategy_id = :country_strategy_id '.$and.' 
                    ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':own_initiative', $this->own_initiative);
            $stmt->bindParam(':country_strategy_id', $country_strategy_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

        public function check_year($country_strategy_id) {
            $query =  'SELECT year FROM strategy_country_level WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $country_strategy_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['year'];
            } else {
                return false;
            }
        }

        public function get_department_comments($initiative_id) {
            $query =  'SELECT s.*, u.name, u.surname FROM strategy_initiative_comments s
                        LEFT JOIN users u ON s.userId = u.userId
                        WHERE s.initiative_id = :initiative_id  ORDER BY s.id DESC';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':initiative_id', $initiative_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            
            $data = array();
            if($num > 0) {

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
    
                    $data[] = array(
                        'id' => $id,
                        'comment' => $comment,
                        'comment_date' => date("M j, Y g:i a", strtotime($comment_date)),
                        'comment_by' => $name . ' ' . $surname ,
                    );
                }

                return $data;
            } else {
                return false;
            }
        }

        public function add_initiative_comment() {
            //query
            $query = ' INSERT INTO strategy_initiative_comments
                SET
                    userId = :userId,
                    initiative_id = :initiative_id,
                    comment = :comment,                
                    comment_date = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':userId', $this->this_user);
            $stmt->bindParam(':initiative_id', $this->id);
            $stmt->bindParam(':comment', $this->comment);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }


        public function strategy_initiatives_report($country, $department, $threeyear_strategy, $annual_year, $DateFrom, $DateTo)
        {
            // Search 
            $searchQuery = '';
            // if(isset($this->searchValue)){
            //     $searchQuery = ' AND (
            //         c.refNo LIKE "%'.$this->searchValue.'%" OR 
            //         c.category LIKE "%'.$this->searchValue.'%" OR 
            //         c.status LIKE "%'.$this->searchValue.'%" OR 
            //         u.name LIKE "%'.$this->searchValue.'%" OR 
            //         u.surname LIKE "%'.$this->searchValue.'%" 
            //     ) ';
            // }
            //Order
            $order = 'i.target_score DESC';
            if(isset($this->columnIndex) && !empty($this->columnIndex)){
                $order = ' '.$this->columnIndex.' '.$this->columnSortOrder.' ';
            }

            //limit
            $limit = '';
            if(isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)){
                $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
            }
            //Filters Checking
            $where_qr = ''; 

            if( $country !="" ){
                $where_qr .=  ' AND (c.country = :country)';
            }
            if( $department !="" ){
                $where_qr .=  ' AND (c.department = :department)';
            }
            if( $threeyear_strategy !="" ){
                $where_qr .=  ' AND (st.id = :year_range)';
            }
            if( $annual_year !="" ){
                $where_qr .=  ' AND (c.year = :year_annual)';
            }
            if( $DateFrom !="" && $DateTo !=""){
                $where_qr .= ' AND( cast(i.timeline as date) BETWEEN :dateFrom AND :dateTo )';
            }

            //Select Count
            $sql = 'SELECT count(*) 
                FROM 
                    '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON i.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id                     
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE 1
                        '.$where_qr.'
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            if ($country !="") $counted->bindParam(':country',$country);
            if ($department !="") $counted->bindParam(':department',$department);
            if ($threeyear_strategy !="") $counted->bindParam(':year_range',$threeyear_strategy);
            if ($annual_year !="") $counted->bindParam(':year_annual',$annual_year);
            if ($DateFrom !="" && $DateTo !="") {
                $counted->bindParam(':dateFrom',$DateFrom);
                $counted->bindParam(':dateTo',$DateTo);
            }
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query

            $query = ' SELECT
                        i.*, i.id AS init_id, p.strategy_pillar,                 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        c.status, st.year_range, c.year
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON i.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id                     
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE 1
                        '.$where_qr.'
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            if ($country !="") $stmt->bindParam(':country',$country);
            if ($department !="") $stmt->bindParam(':department',$department);
            if ($threeyear_strategy !="") $stmt->bindParam(':year_range',$threeyear_strategy);
            if ($annual_year !="") $stmt->bindParam(':year_annual',$annual_year);
            if ($DateFrom !="" && $DateTo !="") {
                $stmt->bindParam(':dateFrom',$DateFrom);
                $stmt->bindParam(':dateTo',$DateTo);
            } 

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
                    'id' => $init_id,
                    'year_range' => $year_range,
                    'year' => $year,
                    'initiative' => $initiative,
                    'target' => $target,
                    'measure' => $measure,
                    'figure' => $figure,
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
                    'created_by' => $created_by_surname . ' ' . $created_by_name
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
    