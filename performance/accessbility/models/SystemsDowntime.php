<?php

    class SystemsDowntime {
        //DB Stuff
        private $conn;
        private $table = "systems_downtimes";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $refNo;
        public $country;
        public $country_val;
        public $system;
        public $system_val;
        public $downtime;
        public $time_started;
        public $time_resolved;
        public $tat_in_minutes;
        public $hours_in_minutes;
        public $rca;        
        public $created_by;
        public $created_at;
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
        public function __construct($db) {
            $this->conn = $db;
        }

        //Get Items
        public function read_all_downtimes($country=NULL, $system=NULL, $DateFrom=NULL, $DateTo=NULL){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    a.system_name LIKE "%'.$this->searchValue.'%" OR
                    ctr.country LIKE "%'.$this->searchValue.'%"
                ) ';
            }
            //Order
            $order = 's.id DESC ';
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

            if( $country != NULL ){
                $where_qr .=  ' AND (s.country = :country)';
            }
            if( $system != NULL ){
                $where_qr .=  ' AND (s.`system` = :system)';
            }
            if( $DateFrom != NULL && $DateTo != NULL){
                $where_qr .= ' AND( cast(s.time_started as date) BETWEEN :dateFrom AND :dateTo )';
            }


            //Select Count
            $sql = 'SELECT count(*) 
                FROM 
                    '.$this->table.' s
                LEFT JOIN 
                    users u1 ON s.created_by = u1.userId 
                LEFT JOIN 
                    systems a ON s.system = a.id 
                INNER JOIN 
                    countries ctr ON s.country = ctr.id
                WHERE 1
                    '.$where_qr.'
                    '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            if ($country != NULL) $counted->bindParam(':country',$country);
            if ($system != NULL) $counted->bindParam(':system',$system);
            if ($DateFrom != NULL && $DateTo != NULL) {
                $counted->bindParam(':dateFrom',$DateFrom);
                $counted->bindParam(':dateTo',$DateTo);
            }
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = 'SELECT 
                        s.*, u1.name AS name1, 
                        u1.surname AS surname1,
                        a.system_name,                      
                        ctr.country AS country_name
                    FROM '.$this->table.' s
                    LEFT JOIN 
                        users u1 ON s.created_by = u1.userId 
                    LEFT JOIN 
                        systems a ON s.system = a.id 
                    INNER JOIN 
                        countries ctr ON s.country = ctr.id
                    WHERE 1
                        '.$where_qr.'
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.' 
            '; 

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            if ($country != NULL) $stmt->bindParam(':country',$country);
            if ($system != NULL) $stmt->bindParam(':system',$system);
            if ($DateFrom != NULL && $DateTo != NULL) {
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

                $view = $update = $delete = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                $delete = '<li id="'.$id.'" class="delete"><a href="#" class="dropdown-item"><i class="mdi mdi-cancel font-size-16 text-danger me-1 "></i> Delete</a></li>';
                

                $data[] = array(
                    'id' => $id,
                    'refNo' => $refNo,
                    'system_name' => $system_name,
                    'downtime' => $downtime,
                    'country' => $country_name,
                    'time_started' => date("F j, Y g:i a", strtotime($time_started)),
                    'time_resolved' => date("F j, Y g:i a", strtotime($time_resolved)),
                    'tat_in_minutes' => $tat_in_minutes,
                    'hours_in_minutes' => $hours_in_minutes,
                    'rca' => $rca,
                    'created_by' => $surname1 . ' ' . $name1 ,
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
       

        //Get Single Idea
        public function read_single() {
            //create query
            $query =  'SELECT 
                        s.*, 
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2,
                        a.system_name,                      
                        ctr.id AS country_val,     
                        ctr.country AS country_name
                    FROM ' . $this->table . ' s
                        LEFT JOIN 
                            systems a ON s.system = a.id 
                        INNER JOIN 
                            countries ctr ON s.country = ctr.id
                        LEFT JOIN 
                            users u1 ON s.created_by = u1.userId 
                        LEFT JOIN 
                            users u2 ON s.updated_by = u2.userId
                    WHERE s.id = :id
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
            $this->refNo = $row['refNo'];          
			$this->country = $row["country_name"];
			$this->country_val = $row["country_val"];
            $this->system = $row["system_name"];
            $this->system_val = $row["system"];
            $this->downtime = $row["downtime"];
            $this->time_started = $row["time_started"];
            $this->time_resolved = $row["time_resolved"];
            $this->tat_in_minutes	= $row["tat_in_minutes"];
            $this->hours_in_minutes	= $row["hours_in_minutes"];
            $this->rca	= $row["rca"];
            $this->created_by	= $row["name1"]." ".$row["surname1"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"] ? $row["updated_at"] : "N/A";
            $this->updated_by = $row["updated_at"] ? $row["name2"]." ".$row["surname2"] : "N/A";

            return $stmt;
        }


        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    country = :country, 
                    `system` = :system, 
                    downtime = :downtime, 
                    time_started = :time_started, 
                    time_resolved = :time_resolved, 
                    tat_in_minutes = :tat_in_minutes, 
                    hours_in_minutes = :hours_in_minutes, 
                    rca = :rca, 
                    created_by = :created_by,
                    created_at = Now() 
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':system', $this->system);
            $stmt->bindParam(':downtime', $this->downtime);
            $stmt->bindParam(':time_started', $this->time_started);
            $stmt->bindParam(':time_resolved', $this->time_resolved);
            $stmt->bindParam(':tat_in_minutes', $this->tat_in_minutes);
            $stmt->bindParam(':hours_in_minutes', $this->hours_in_minutes);
            $stmt->bindParam(':rca', $this->rca);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                $last_req_id = $this->conn->lastInsertId();
				$refNo = "DOWN_".$last_req_id;
				$updateRef = $this->conn->prepare('UPDATE '.$this->table.' SET refNo=:refNo WHERE id =:id ');
				$updateRef ->bindParam(':refNo',$refNo);
				$updateRef ->bindParam(':id',$last_req_id);
				
				if($updateRef ->execute())
				{
                    return true;
                }
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
                    `system` = :system, 
                    downtime = :downtime, 
                    time_started = :time_started, 
                    time_resolved = :time_resolved, 
                    tat_in_minutes = :tat_in_minutes, 
                    hours_in_minutes = :hours_in_minutes, 
                    rca = :rca, 
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
            $stmt->bindParam(':system', $this->system);
            $stmt->bindParam(':downtime', $this->downtime);
            $stmt->bindParam(':time_started', $this->time_started);
            $stmt->bindParam(':time_resolved', $this->time_resolved);
            $stmt->bindParam(':tat_in_minutes', $this->tat_in_minutes);
            $stmt->bindParam(':hours_in_minutes', $this->hours_in_minutes);
            $stmt->bindParam(':rca', $this->rca);
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
        
        public function is_downtime_exists() {
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

    }
    