<?php
// Developed by Davidi Sheleg, 2020.

class DB{
  protected static $servername="localhost";
  protected static $username="project-db-username";
  protected static $password="project-db-password";
  protected static $dbname="project-db-name";
  protected static $charset="project-charset"; 
  const CHECK_INJECTION=true; // If the var is true,some functions will run the function CheckInjection() for input validity

  //Connect to the database
  public static function connect()
  {
      // Create connection
      $conn = new mysqli(self::$servername, self::$username, self::$password, self::$dbname);
      $conn -> set_charset(self::$charset);
      // Check connection
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
      return $conn; 
  }

  //Check if array is null or empty
  protected static function CheckArray($method,$array)
  {
    if($array==null)
    die($method.': The function need at least one column to execute'); 

    if(is_array($array)!=1)
    die($method.': The function only recive arrays');  
  }

  //Check if parameters contain prohibited words (you can add or remove words as you wish)
  //Return true if the array is ok, and fase if the array contain prohibited words
  protected static function CheckInjection($var_array)
  {
     $prohibited=array("SELECT","INSERT","DELETE","1=1","DROP");
       foreach($prohibited as $word){
        $matches  = preg_grep ('/\b'.$word.'\b/', $var_array);
        if(count($matches)>0){
         return false;
         //HERE YOU CAN ADD SOME CODE TO RECORD THE EVENT
        }
       }
       return true;
  }


  /*Execute SQL query.
  For query without parameters, dont send an array of vars.
  For query with parameters, send an array who contain the content of the (?) vars. 
  Example: DB::QUERY("DELETE FROM Table WHERE name=?",array('Davidi Sheleg'));
  the query return mysqli_stmt Object  */
  public static function QUERY($query,$var_array)
  { 
    self::CheckArray("QUERY",$var_array);
    if(self::CHECK_INJECTION)
    if(!self::CheckInjection($var_array)) die("SQL Injection");

    $conn=self::connect();
    if($var_array==null){
        $result = $conn->query($query);
    }
    else {
      $stmt = $conn->prepare($query);

      if(!$stmt)
      die('prepare() failed: ' . htmlspecialchars($conn->error)); //Handle with errors

      $types = str_repeat('s', count($var_array)); 
      $stmt->bind_param($types,...$var_array); 
      $stmt->execute();
    }

    
    
    if($conn -> error !=null){
      echo $conn -> error;
      $conn->close();  
    }   
    else{
      $conn->close(); 
      return $stmt;
    } 
  }

  /*Execute SELECT query.
  For query without parameters, dont send an array of vars.
  For query with parameters, send an array who contain the content of the (?) vars. 
  Example: DB::SELECT("SELECT name FROM Table WHERE age=? AND city=?",array(23,'NYC'));
  The function return the data if the query found something, otherwise it will return 0 if there is no rows or errors. */
  public static function SELECT($query,$var_array)
  { 
    self::CheckArray("SELECT",$var_array);
    if(self::CHECK_INJECTION)
    if(!self::CheckInjection($var_array)) die("SQL Injection");

    $conn=self::connect();
    if($var_array==null){
        $result = $conn->query($query);
    }
    else {
      $stmt = $conn->prepare($query);

      if(!$stmt)
      die('prepare() failed: ' . htmlspecialchars($conn->error)); //Handle with errors

      $types = str_repeat('s', count($var_array)); 
      $stmt->bind_param($types,...$var_array); 
      $stmt->execute();
      $result = $stmt->get_result(); // get the mysqli result
    }

    
    
    if ($result->num_rows > 0){
         $conn->close(); 
         return $result->fetch_all(MYSQLI_ASSOC); 
        }
    else {   
        if($conn -> error !=null)
        echo $conn -> error;    
        else 
        return 0; // 0 results
    } 
  

    $conn->close();     
  }

  /*INSERT data to the DB, the function recive the table name and array with columns and content.
  The function return the id of the inserted row.
  Example: DB::INSERT("Table",array('name'=>'Davidi Sheleg','phone'=>'123-456-7890')); */
  public static function INSERT($table_name,$content)
  {
      
      self::CheckArray("INSERT",$content);
      if(self::CHECK_INJECTION)
      if(!self::CheckInjection($content)) die("SQL Injection");

      $conn=self::connect();
        //Get the columns string for the SQL query.
        $columns=array_keys($content);
        $columns_arr_len=count($columns);
        for($i=0;$i<$columns_arr_len;$i++){
           if($i==0){
           $table_columns=$columns[$i]; $que_marks="?"; }
           else{
           $table_columns.=",".$columns[$i]; $que_marks.=",?"; } 
        }
        $query="INSERT INTO ".$table_name." (".$table_columns.") VALUES (".$que_marks.");";

        $stmt = $conn->prepare($query);
        if(!$stmt)
        die('prepare() failed: ' . htmlspecialchars($conn->error)); //Handle with errors
  
        $types = str_repeat('s', $columns_arr_len); 
        $stmt->bind_param($types,...array_values($content)); 
        $stmt->execute();    
       $conn->close();
       if($stmt->insert_id!=null)
       return $stmt->insert_id;
       else
       return true;
    }
 

    /*UPDATE data in the DB, the function recive the table name, array with columns and content,
    and if the query have conditions, the function recive the conditions SQL string, and the conditions content.
    The function return the affected rows of the query, and if the function didnt succeed, it will return -1.
    Example with conditions: DB::UPDATE("Table",array('name'=>"Davidi Sheleg"),"user_id=? OR user_id=?",array(1,7));
    Example without conditions (update all the rows): DB::UPDATE("Table",array('name'=>"Davidi Sheleg"));  */
    public static function UPDATE($table_name,$content,$conditions_str,$conditions_content)
    {  
        self::CheckArray("UPDATE",$content);
        if(self::CHECK_INJECTION)
        if(!self::CheckInjection($content)) die("SQL Injection");
  
        $conn=self::connect();
          //Get the columns string for the SQL query.
          $columns=array_keys($content);
          $columns_arr_len=count($columns);
          if($conditions_arr_len!=null)
          $conditions_arr_len=count($conditions_content);

          for($i=0;$i<$columns_arr_len;$i++){
             if($i==0){
             $set_str=$columns[$i]."=?";  
             }
             else{
             $set_str.=",".$columns[$i]."=?";  
             } 
          }
          $query="UPDATE ".$table_name." SET ".$set_str."";
          if($conditions_str!=null)
          $query.=" WHERE ".$conditions_str;

          $query.=";";
  
          $stmt = $conn->prepare($query);
          if(!$stmt)
          die('prepare() failed: ' . htmlspecialchars($conn->error)); //Handle with errors
          if($conditions_arr_len!=null){
            $types = str_repeat('s', ($columns_arr_len+$conditions_arr_len)); 
            $stmt->bind_param($types,...array_merge(array_values($content),array_values($conditions_content))); 
          }
          else{
            $types = str_repeat('s', $columns_arr_len); 
            $stmt->bind_param($types,...array_values($content)); 
          }

          $stmt->execute();    
         $conn->close();


         return $stmt->affected_rows;
      }
   
  

}


?>
