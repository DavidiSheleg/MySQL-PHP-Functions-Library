# MySQL-PHP-Functions-Library
A file who contains several effective PHP functions, that helps you work **more easly and fast** with your database.

## Installation
* Simply add the `db_functions.php` file into your project.
* Set the varibles at the top of the file `$servername,$username,$password,$dbname,$charset`, accroding to your project and database details.
* Include the file `db_functions.php` in the pages that you want to use the MySQL functions: `include_once('db_functions.php');`.

## Code examples
The `db_functions.php` file include the functions:
* `connect()`
* `QUERY($query,$var_array)`
* `SELECT($query,$var_array)`
* `INSERT($table_name,$content)`
* `UPDATE($table_name,$content,$conditions_str,$conditions_content)`

### connect() - Connect to the database
```
$conn=DB::connect(); // Return mysqli object
print_r($conn);
```


### QUERY($query,$var_array) - Execute SQL query.
  For query without parameters, `$var_array` is null.
  For query with parameters, send an array who contain the content of the (?) vars. 
```
$query=DB::QUERY("DELETE FROM Table WHERE name=?",array('Davidi Sheleg')); //Delete all the rows where the 'names' column equal to 'Davidi Sheleg'
print_r($query); // Return mysqli_stmt Object 
```


### SELECT($query,$var_array) - Execute SELECT query.
  For query without parameters, `$var_array` is null.
  For query with parameters, send an array who contain the content of the (?) vars. 
```
$query=DB::SELECT("SELECT name FROM Table WHERE age=? AND city=?",array(23,'NYC'));
//The function return the data if the query found something, otherwise it will return 0 if there is no rows or errors.
if($query!=0){
foreach($query as $person){
echo $person['name']."<br/>";
 }
}
```


### INSERT($table_name,$content) - INSERT data to the DB.
  The function recive the table name and array with columns and content.
  For query with parameters, send an array who contain the content of the (?) vars. 
```
$query=DB::INSERT("Table",array('name'=>'Davidi Sheleg','phone'=>'123-456-7890'));
echo $query; //  The function return the id of the inserted row.
```


### UPDATE($table_name,$content,$conditions_str,$conditions_content) - UPDATE data in the DB.
  the function recive the table name, array with columns and content,
  and if the query have conditions, the function recive the conditions SQL string, and the conditions content.
  For query with parameters, send an array who contain the content of the (?) vars. 
```
$query=DB::UPDATE("Table",array('name'=>"Davidi Sheleg"),"id=? OR age=?",array(1,23)); //Example with conditions
$query=DB::UPDATE("Table",array('name'=>"Davidi Sheleg")); // Example without conditions (update all the rows)
print_r($query); //The function return the affected rows of the query, and if the function didnt succeed, it will return -1.
```
