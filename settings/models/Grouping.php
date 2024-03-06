<?php

    class Grouping {
        //DB Stuff
        private $conn;
        private $table = "groupings";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $group_name;
        public $valuation_method;
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

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        //Get Ideas
        public function read_all_groupings(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    g.group_name LIKE "%'.$this->searchValue.'%" OR 
                    g.valuation_method LIKE "%'.$this->searchValue.'%"
                ) ';
            }
            //Order
            $order = 'g.id DESC';
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
                    '.$this->table.' g 
                    LEFT JOIN 
                        users u1 ON g.created_by = u1.id
                    WHERE 1
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        g.*,
                        u1.name AS created_by_name, 
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name, 
                        u2.surname AS updated_by_surname
                    FROM
                        '.$this->table.' g
                    LEFT JOIN 
                        users u1 ON g.created_by = u1.id
                    LEFT JOIN 
                        users u2 ON g.updated_by = u2.id
                    WHERE 1
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
                    "message" => "Groupings Not Found",
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

                   
                $view = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                $delete = '<li id="'.$id.'" class="delete"><a href="#" class="dropdown-item"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Delete</a></li>';
                
                $data[] = array(
                    'id' => $id,
                    'group_name' => $group_name ,
                    'valuation_method' => $valuation_method ,
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'      
                                '.$delete.'
                            </ul>
                        </div>
                   '
                );
            }


            $output = array(
                "success"      => true,
                "message"       => "Groupings Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        public function is_grouping_name_exists($and = ''){
            $query =  'SELECT group_name FROM ' . $this->table . ' WHERE group_name = :group_name '.$and.' ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_name', $this->group_name);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

        public function is_grouping_exists(){
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

        //Get Single Idea
        public function read_single() {
            //create query
            $query =  'SELECT 
                        g.*,
                        u1.name AS created_by_name, 
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name, 
                        u2.surname AS updated_by_surname
                    FROM
                        ' . $this->table . ' g
                    LEFT JOIN 
                        users u1 ON g.created_by = u1.id
                    LEFT JOIN 
                        users u2 ON g.updated_by = u2.id
                    WHERE
                        g.id = :id
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
            $this->group_name = $row['group_name'];
            $this->valuation_method = $row['valuation_method'];
            $this->created_by = $row['created_by_name'] . ' ' . $row['created_by_surname'];
            $this->created_at = date("F j, Y g:i a", strtotime($row['created_at']));
            $this->updated_by = $row['updated_by_name'] ? $row['updated_by_name'] . ' ' . $row['updated_by_surname'] : 'N/A';
            $this->updated_at = $row['updated_at'] ? date("F j, Y g:i a", strtotime($row['updated_at'])) : 'N/A';
            
            return $stmt;
        }


        //Create Idea
        public function create() {
            
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    group_name = :group_name,
                    valuation_method = :valuation_method,
                    created_by = :created_by,
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;
            //bind data
            $stmt->bindParam(':group_name', $this->group_name);
            $stmt->bindParam(':valuation_method', $this->valuation_method);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            return $stmt->error;
        }


        //Update Idea
        public function update() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    group_name = :group_name,
                    valuation_method = :valuation_method,
                    updated_by = :updated_by,
                    updated_at = Now()                    
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;
            //bind data
            $stmt->bindParam(':group_name', $this->group_name);
            $stmt->bindParam(':valuation_method', $this->valuation_method);
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

        //Update Save Idea Changes Status
        public function delete() {

            $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            //Bind Data
            $stmt->bindParam(':id', $this->id);

            //Execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }


        //DROP DOWN LISTS
        public function list_dropdown_groupings(){

            //Select Query
            $query = ' SELECT id, group_name FROM '.$this->table.' ';
            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Execute Query
            $stmt->execute();

            $data = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data[] = array(
                    'id' => $id,
                    'group_name' => $group_name
                );
            }
            return $data;
        }

        public function list_dropdown_groupings_by_method($property_id){

            $method = '';
            $check_qr = ' SELECT pr.valuation_method 
                        FROM projects pr
                        LEFT JOIN properties p ON pr.id = p.project_id
                        WHERE p.id = :property_id 
                    ';
            $check = $this->conn->prepare($check_qr);
            $check->bindParam(':property_id', $property_id);
            $check->execute();
            if($ro = $check->fetch(PDO::FETCH_ASSOC)) {
                extract($ro);
                $method = $valuation_method;
            }

            //Select Query
            $query = ' SELECT id, group_name FROM '.$this->table.' WHERE valuation_method = :valuation_method ';
            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind Data
            $stmt->bindParam(':valuation_method', $method);
            //Execute Query
            $stmt->execute();

            $data = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data[] = array(
                    'id' => $id,
                    'group_name' => $group_name
                );
            }
            return $data;
        }

    }

    ?>