<?php

    class Item {
        //DB Stuff
        private $conn;
        private $table = "grouping_items";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $group_name;
        public $grouping_id;
        public $item_name;
        public $unit;
        public $price_per_unit;
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
        public function read_all_items(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    g.group_name LIKE "%'.$this->searchValue.'%" OR
                    i.item_name LIKE "%'.$this->searchValue.'%"
                ) ';
            }
            //Order
            $order = 'i.id DESC';
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
                    LEFT JOIN 
                        users u1 ON i.created_by = u1.id
                    LEFT JOIN 
                        groupings g ON i.grouping_id = g.id

                    WHERE 1
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*,
                        g.group_name,
                        u1.name AS created_by_name, 
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name, 
                        u2.surname AS updated_by_surname
                    FROM
                        '.$this->table.' i
                    LEFT JOIN 
                        users u1 ON i.created_by = u1.id
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.id
                    LEFT JOIN 
                        groupings g ON i.grouping_id = g.id
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
                    "message" => "Items Not Found",
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
                    'grouping_id' => $grouping_id ,
                    'item_name' => $item_name ,
                    'unit' => $unit ,
                    'price_per_unit' => number_format($price_per_unit,2).' Rwf',
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
                "message"       => "Items Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        public function is_item_name_exists($and = ''){
            $query =  'SELECT item_name FROM ' . $this->table . ' WHERE item_name = :item_name 
                        AND grouping_id = :grouping_id '.$and.' ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':item_name', $this->item_name);
            $stmt->bindParam(':grouping_id', $this->grouping_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

        public function is_item_exists(){
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
                        i.*,
                        g.group_name,
                        u1.name AS created_by_name, 
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name, 
                        u2.surname AS updated_by_surname
                    FROM
                        ' . $this->table . ' i
                    LEFT JOIN 
                        users u1 ON i.created_by = u1.id
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.id
                    LEFT JOIN 
                        groupings g ON i.grouping_id = g.id
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
            $this->group_name = $row['group_name'];
            $this->grouping_id = $row['grouping_id'];
            $this->item_name = $row['item_name'];
            $this->unit = $row['unit'];
            $this->price_per_unit = $row['price_per_unit'];
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
                    grouping_id = :grouping_id,
                    item_name = :item_name,
                    unit = :unit,
                    price_per_unit = :price_per_unit,
                    created_by = :created_by,
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;
            //bind data
            $stmt->bindParam(':grouping_id', $this->grouping_id);
            $stmt->bindParam(':item_name', $this->item_name);
            $stmt->bindParam(':unit', $this->unit);
            $stmt->bindParam(':price_per_unit', $this->price_per_unit);
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
                    grouping_id = :grouping_id,
                    item_name = :item_name,
                    unit = :unit,
                    price_per_unit = :price_per_unit,
                    updated_by = :updated_by,
                    updated_at = Now()                    
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;
            //bind data
            $stmt->bindParam(':grouping_id', $this->grouping_id);
            $stmt->bindParam(':item_name', $this->item_name);
            $stmt->bindParam(':unit', $this->unit);
            $stmt->bindParam(':price_per_unit', $this->price_per_unit);
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
        public function list_dropdown_items(){

            //Select Query
            $query = ' SELECT * FROM '.$this->table.' WHERE grouping_id =:grouping_id ';
            //Prepare statement
            $stmt = $this->conn->prepare($query);
            //Bind Data
            $stmt->bindParam(':grouping_id', $this->grouping_id);
            //Execute Query
            $stmt->execute();

            $data = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data[] = array(
                    'id' => $id,
                    'item_name' => $item_name,
                    'unit' => $unit,
                    'price_per_unit' => $price_per_unit
                );
            }
            return $data;
        }

    }

    ?>