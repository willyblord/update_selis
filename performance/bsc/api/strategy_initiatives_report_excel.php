<?php  
 
//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../../../administration/users/models/User.php';

//Instantiate SB and Connect
$database = new Database();

if (!isset($_COOKIE["jwt"])) {
    header("Location: login");
    exit();
}


$db = $database->connect(); 
//Instantiate Project object
$user = new User($db);

//Check jwt validation
$userDetails = $user->validate($_COOKIE["jwt"]);
if ($userDetails === false) {
    header("Location: login");
    exit();
}

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'HR_ROLE'];
$requiredPermissions = ['view_report_bsc'];
$requiredModules = 'Performance';

if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

    header("Location: login");
    exit();
} 

function fetch_data($db)  
{ 
    //Instantiate SB and Connect
    $database = new Database();
    $db = $database->connect(); 

    function clean_data($data) {  
        $data = trim($data);  
        $data = strip_tags($data);  
        $data = stripslashes($data);
        $data = htmlspecialchars($data);  
        return $data;  
    }

    $country = clean_data($_POST['country']);
    $department = clean_data($_POST['department']);
    $threeyear_strategy = clean_data($_POST['threeyear_strategy']);
    $annual_year = clean_data($_POST['annual_year']);
    $DateFrom = clean_data($_POST['DateFrom']);
    $DateTo = clean_data($_POST['DateTo']);
    
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

    $query = ' SELECT
                i.*, i.id AS init_id, p.strategy_pillar,               
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                c.status, st.year_range, c.year,
                ctry.country AS countryName,
                dept.category AS depart
            FROM
                strategy_initiatives i
            INNER JOIN 
                users u ON i.created_by = u.userId 
            LEFT JOIN 
                strategy_pillars_group_level p ON i.pillar_id = p.id 
            LEFT JOIN 
                strategy_country_level c ON i.country_strategy_id = c.id                     
            LEFT JOIN
                strategies st ON c.group_strategy_id = st.id
            LEFT JOIN 
                countries ctry ON c.country = ctry.id
            LEFT JOIN 
                department_categories dept ON c.department=dept.id 
            WHERE 1
                '.$where_qr.'
            ORDER BY i.target_score DESC      
    ';

    //Prepare statement
    $stmt = $db->prepare($query);

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
    $num = $stmt->rowCount();		
        
    if( $num > 0 )
    {
        $data_array = array();
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {  
            $data_array[] = $data;
        }   
        $output = '

        <h3 style="text-align:center; text-decoration:underline;">SERIS - STRATEGY INITIATIVES REPORT</h3> <br/><br/>
            
    
            <table border="1" cellspacing="0" cellpadding="5">  
                <tr style="font-weight:bold;">  
                    <th>Strategy</th>
                    <th>Year</th>
                    <th>Initiative</th>
                    <th>Target</th>
                    <th>Measure</th>
                    <th>Figure</th>
                    <th>Weight</th>
                    <th>Raw Score</th>
                    <th>Target Score</th>
                    <th>Achieved Weight</th>
                </tr>

                ';

                foreach ($data_array as $row) {

                    $figure = $row["figure"];
                    $raw_score = $row["raw_score"];
                    if($row["measure"] == "Quantitative Parcentage") {
                        $figure = $row["figure"].'%';
                        $raw_score = $row["raw_score"].'%';
                    }
                    if($row["measure"] == "Quantitative Financial") {
                        $figure = number_format($row["figure"],2);
                        $raw_score = number_format($row["raw_score"],2);
                    }

                    $output .= 
                    '                         
                        <tr nobr="true">  
                            <td>'.$row["year_range"].'</td> 
                            <td>'.$row["year"].'</td> 	
                            <td>'.$row["initiative"].'</td> 
                            <td>'.$row["target"].'</td> 					
                            <td>'.$row["measure"].'</td> 
                            <td>'.$figure.'</td>  
                            <td>'.$row["weight"].'%</td> 
                            <td>'.$raw_score.'</td> 
                            <td>'.$row["target_score"].'%</td> 
                            <td>'.$row["computed_score"].'%</td> 
                        </tr>  
                    ';
                }
                
                $output .= '
            </table>
        ';
        return $output;  
    }
}  
if(isset($_POST["exportExcel"]))  
{
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=SERIS Strategy_initiatives_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $content = fetch_data($db);
    echo $content;
} 
?>