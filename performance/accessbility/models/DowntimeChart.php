<?php
   
   class DowntimeChart 
   {
      private $conn;
      private $table = "systems_downtimes";
      public $this_user;

      public $filter_country;
      public $filter_system;
      public $filter_date_from;
      public $filter_date_to;

      //condtructor
      public function __construct($db) {
         $this->conn = $db;
     }

     //Countries Chart
      public function countriesChart(){
         $data = array();
         $where_ctry_dpt = '';
               
         if(!empty($this->filter_country) ){
            $where_ctry_dpt = 'AND country=:country';
         }

         $sql = 'SELECT MONTH(time_started) AS month, COUNT(*) AS total_count, SUM(hours_in_minutes) AS total_hours 
                  FROM '.$this->table.'
                  WHERE 
                     `system`=:system AND (CAST(time_started AS DATE) BETWEEN :dateFrom AND :dateTo) 
                  '.$where_ctry_dpt.' 
                  GROUP BY MONTH(time_started) 
                  ORDER BY time_started ASC';
         $query = $this->conn->prepare($sql);

         $query->bindParam(':system', $this->filter_system);
         $query->bindParam(':dateFrom', $this->filter_date_from);
         $query->bindParam(':dateTo', $this->filter_date_to);
         if(!empty($this->filter_country) ){
            $query->bindParam(':country', $this->filter_country);
         }
         $query->execute();
         $num = $query->rowCount();
         
         if($num > 0) {
            
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
               extract($row);
                              
               $data[] = array(
                  'month' => date('F', mktime(0, 0, 0, $month, 10)),
                  'total_count' => $total_count,
                  'total_hours' => $total_hours
              );

            }
            return $data;

        }
      }  
      

      //Countries Chart
      public function systemsChart(){
         $data = array();
         $where_ctry_dpt = '';
               
         if(!empty($this->filter_country) && !empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $where_ctry_dpt = 'WHERE d.country=:country AND d.`system`=:system AND (CAST(d.time_started AS DATE) BETWEEN :dateFrom AND :dateTo)';
         } 
         elseif(!empty($this->filter_country) && !empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $where_ctry_dpt = 'WHERE d.country=:country AND d.`system`=:system';
         }
         elseif(!empty($this->filter_country) && empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $where_ctry_dpt = 'WHERE d.country=:country AND (CAST(d.time_started AS DATE) BETWEEN :dateFrom AND :dateTo)';
         }
         elseif(empty($this->filter_country) && !empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $where_ctry_dpt = 'WHERE d.`system`=:system AND (CAST(d.time_started AS DATE) BETWEEN :dateFrom AND :dateTo)';
         }
         elseif(empty($this->filter_country) && !empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $where_ctry_dpt = 'WHERE d.`system`=:system';
         }
         elseif(!empty($this->filter_country) && empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $where_ctry_dpt = 'WHERE d.country=:country';
         }
         elseif(empty($this->filter_country) && empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $where_ctry_dpt = 'WHERE (CAST(d.time_started AS DATE) BETWEEN :dateFrom AND :dateTo)';
         }

         $sql = 'SELECT COUNT(*) AS total_count, s.system_name, SUM(d.hours_in_minutes) AS total_hours 
                  FROM '.$this->table.' d
                  LEFT JOIN `systems` s ON d.system = s.id '.$where_ctry_dpt.'
                  GROUP BY d.system';
         $query = $this->conn->prepare($sql);

         if(!empty($this->filter_country) && !empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $query->bindParam(':country', $this->filter_country);
            $query->bindParam(':system', $this->filter_system);
            $query->bindParam(':dateFrom', $this->filter_date_from);
            $query->bindParam(':dateTo', $this->filter_date_to);
         } 
         elseif(!empty($this->filter_country) && !empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $query->bindParam(':country', $this->filter_country);
            $query->bindParam(':system', $this->filter_system);
         }
         elseif(!empty($this->filter_country) && empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $query->bindParam(':country', $this->filter_country);
            $query->bindParam(':dateFrom', $this->filter_date_from);
            $query->bindParam(':dateTo', $this->filter_date_to);
         }
         elseif(empty($this->filter_country) && !empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $query->bindParam(':system', $this->filter_system);
            $query->bindParam(':dateFrom', $this->filter_date_from);
            $query->bindParam(':dateTo', $this->filter_date_to);
         }
         elseif(empty($this->filter_country) && !empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $query->bindParam(':system', $this->filter_system);
         }
         elseif(!empty($this->filter_country) && empty($this->filter_system) && (empty($this->filter_date_from) || empty($this->filter_date_to)) ){
            $query->bindParam(':country', $this->filter_country);
         }
         elseif(empty($this->filter_country) && empty($this->filter_system) && !empty($this->filter_date_from) && !empty($this->filter_date_to) ){
            $query->bindParam(':dateFrom', $this->filter_date_from);
            $query->bindParam(':dateTo', $this->filter_date_to);
         }
         $query->execute();
         $num = $query->rowCount();
         
         if($num > 0) {
            
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
               extract($row);

               $rand = 'rgba('.rand(0,255).', '.rand(0,255).', '.rand(0,255).', 0.73)';
               $rand1 = 'rgba('.rand(0,255).', '.rand(0,255).', '.rand(0,255).', 0.72)';
                              
               $data[] = array(
                  'system_name' => $system_name,
                  'total_count' => $total_count,
                  'total_hours' => $total_hours,
                  'color' => $rand,
                  'color1' => $rand1
              );

            }
            return $data;

        }
      } 

   }

?>