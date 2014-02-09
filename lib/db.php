<?php
/**
 * Class for safe database operations
 *
 * This class use newest php database library PDO, recomended for use for database operations.
 * using pdo is not so easy as classic mysql_* functions, but provide safety and easy to use
 * result. (array)
 *
 * most important methode in this class is paramQuery(). instructions and example of use included.
 *
 * @author Andrzej Łza Woźniak
 *
 */

class dataBase
{
    /**
     * set this value to true to print all variables to screen.
     * set to false to hide (switch off) all printed debug.
     *
     * JG 2013-10-20
     */
    private $debug;

    private $persistent_connection = false;
    private $dbh = null;

    private $was_persistent_connection = false;
    private $rollback_transaction = false;
    private $in_transaction_count = 0;
    
    /**
     * database link setup
     * @var string
     */
    private $server = null;
    private $name = null;
    private $username = null;
    private $password = null;

    /**
     * data obtained from database
     * @var object
     */
    private $dbData = null;
    private $lastInsertId;
    private $errorEmail;
    private $replyToEmail;

    const dbQuote = '`';
    const bindChar = ':';

    function __construct($debug = false) {
        include __DIR__.'/settings.inc.php';
        $this->server   = $opt['db']['server'];
        $this->name     = $opt['db']['name'];
        $this->username = $opt['db']['username'];
        $this->password = $opt['db']['password'];

        $this->debug = $debug_page;
        $this->errorEmail[] = $mail_rt;
        $this->replyToEmail = $mail_rt;

        // turn on debug to screen
        if ($debug === true) {
            $this->debug = true;
        }
    }

    function __destruct() {
        // free up the memory
        $debug = null;
        $server = null;
        $name = null;
        $username = null;
        $password = null;
        $dbData = null;
        $dbNumRows = null;
        if ($this->dbh != null && $this->in_transaction_count > 0){
            if ($this->debug) {
                self::debugOut('implicitly rolling back a transaction<br>');
            }
            try{
                $this->dbh->rollBack();
            }catch(Exception $e){
                // ignore
            }
        }
        $this->dbh = null;
        if ($this->debug){
            self::debugOut('destructing object dataBase class <br ><br >');
        }
    }

    //JG 2013-12-14
    public function switchDebug( $debug ) {
        $this->debug = $debug;
    }

    /**
     * Enables or disables persistent database connections.
     * In persistent mode, each query issued against this dataBase object
     * uses the same database connection. To release the connection,
     * simply drop the object, or set persistentConnection to false.
     *
     * In non persistent mode, each query establishes new, fresh connection
     * to the database backend.
     *
     * In persistentConnection mode, remember to closeCursor() when
     * it's no longer needed.
     *
     * WARNING!
     * This is still experimental functionality. It may not work as expected,
     * especially in complicated query scenarios!
     */
    public function switchPersistentConnection($persistent_connection) {
        if ($in_transaction_count > 0){
            throw new Exception('Can not change PersistentConnection flag while in transaction');
        }
        $this->persistent_connection = $persistent_connection;
        if (!$persistent_connection){
            $this->dbh = null;
        }
    }

    /**
     * @return one row from result, or FALSE if there are no more rows available
     * The data is returned as an array indexed by column name, as returned in your
     * SQL SELECT
     */
    public function dbResultFetch() {
        return $this->dbData->fetch();
    }

    /**
     * @return number of row in results (i.e. number of rows returned by SQL SELECT)
     * or the number of rows affected by the last DELETE, INSERT, or UPDATE statement
     */
    public function rowCount() {
        return $this->dbData->rowCount();
    }

    /**
     * @return all rows from result as complex array.
     * The returned array contains all of the remaining rows (if you have previously called
     * dbResultFetch(), or all returned rows if not) in the result set. The array represents
     * each row as an array indexed by column name, as returned in your SQL SELECT.
     * An empty array is returned if there are zero results to fetch, or FALSE on failure.
     */
    public function dbResultFetchAll() {
        $result = $this->dbData->fetchAll();
        $this->closeCursor();
        return $result;
    }

    /**
     * @return id of last inserted row
     */
    public function lastInsertId() {
        // return $this->dbData->lastInsertId();
        return $this->lastInsertId;
    }

    private function setupPDO()
    {
        if ($this->persistent_connection && $this->dbh != null){
            return $this->dbh;
        }

        // We are instantinating new PDO object, and new connection for every query.
        // This is inefficient (compared to old sql() function, and especially on my Windows box)
        // Moreover, this could prevent TEMPORARY TABLES and transactions from working -> find out!

        $params = array();
        if ( $this->debug )
            $params[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION; //JG 2013-10-19
        $dbh = new PDO("mysql:host=".$this->server.";dbname=".$this->name,
            $this->username,$this->password,$params);

        // JG 2013-10-20
        $dbh -> query ("SET NAMES utf8");
        $dbh -> query ("SET CHARACTER SET utf8");
        $dbh -> query ("SET collation_connection = utf8_unicode_ci" );

        if ($this->persistent_connection){
            $this->dbh = $dbh;
        }
        return $dbh;
    }

    /**
     * simple querry
     * Use only with static queries, Queries should contain no variables.
     * For queries with variables use paramQery method
     *
     * @param string $query
     * @return true, if the query succeeded; false, if there was SQL error
     */
    public function simpleQuery($query) {
        try {
            $dbh = $this->setupPDO();

            $this->dbData  = $dbh -> prepare($query);
            $this->dbData  -> setFetchMode(PDO::FETCH_ASSOC);
            $this->dbData  -> execute();
            $this->lastInsertId = $dbh->lastInsertId();
        } catch (PDOException $e) {
            $message = $this->errorMessage( __line__, $e, $query, array());
            if ($this->debug) {
                self::debugOut($message);
            } else {
                self::errorMail($message);
            }
            return false;
        }

        if ($this->debug) {
            self::debugOut('db.php, # ' . __line__ .', mysql query on input: ' . $query .'<br />');
        }

        return true;
    }

    /**
     * @param $query - string, with params representation instead variables.
     * @param $params - array with variables.
     *
     * [keyname][value]
     * [keyname][data_type]
     *
     * example:
     * ----------------------------------------------------------------------------------
     * $query: 'SELECT * FROM tabele WHERE field1 = :variable1 AND field2 = :variable2'
     * $params['variable1']['value'] = 1;
     * $params['variable1']['data_type'] = 'integer';
     * $params['variable2']['value'] = 'cat is very lovelly animal';
     * $params['variable2']['data_type'] = 'string';
     * ----------------------------------------------------------------------------------
     * data type can be:
     *
     * - 'boolean'                  Represents a boolean data type.
     * - 'null'                     Represents the SQL NULL data type.
     * - 'integer' or 'int' or 'i'  Represents the SQL INTEGER data type.
     * - 'string' or 'str' or 's'   Represents the SQL CHAR, VARCHAR, or other string data type.
     * - 'large'                    Represents the SQL large object data type.
     * - 'recordset'                Represents a recordset type. Not currently supported by any drivers.
     *
     * @return true, if the query succeeded; false, if there was SQL error
     */
    public function paramQuery($query, $params) {
        if (!is_array($params)) return false;

        try {
            $dbh = $this->setupPDO();

            $this->dbData = $dbh->prepare($query);

            foreach ($params as $key => $val) {
                switch ($val['data_type']) {
                    case 'integer':
                    case 'int':
                    case 'i':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_INT);
                        break;
                    case 'boolean':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_BOOL);
                        break;
                    case 'string':
                    case 'str':
                    case 's':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_STR);
                        break;
                    case 'null':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_NULL);
                        break;
                    case 'large':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_LOB);
                        break;
                    case 'recordset':
                        $this->dbData->bindParam($key, $val['value'], PDO::PARAM_STMT);
                        break;
                    default:
                        return false;
                }
            }

            $this->dbData->setFetchMode(PDO::FETCH_ASSOC);
            $this->dbData->execute();
            $this->lastInsertId = $dbh->lastInsertId();
        } catch (PDOException $e) {
            $message = $this->errorMessage( __line__, $e, $query, $params );
            if ($this->debug) {
                self::debugOut($message);
            } else {
                self::errorMail($message);
            }

            return false;
        }
        if ($this->debug) {
            self::debugOut('db.php, # ' . __line__ .', Query on input: ' . $query .'<br />');
            self::debugOC('db.php, # ' . __line__ .', input parametres for query', $params );
            // self::debugOC('db.php, # ' . __line__ .', database output', $result );
        }

        return true;
    }

    /**
     * @param $query - string, with params representation instead variables.
     * @param $param1, param2 .. paramN - variables.
     *
     *
     * example:
     * ----------------------------------------------------------------------------------
     * $param1 = 1;
     * $param2 = 'cat is very lovelly animal';
     * // note that variable in query MUST be in format :1, :2, :3 (and so on).
     * $query = 'SELECT something FROM tabele WHERE field1=:1 AND field2=:2';
     *
     * multiVariableQuery($query, $param1, $param2 )
     * ----------------------------------------------------------------------------------
     *
     * @return true, if the query succeeded; false, if there was SQL error
     */
    public function multiVariableQuery($query) {
        $numargs = func_num_args();
        $arg_list = func_get_args();

        if($numargs === 2 && is_array($arg_list[1]) ){ // params were passed in array
            $arg_list = $arg_list[1];
            $numargs = count($arg_list)+1;
        }

        try {
            $dbh = $this->setupPDO();

            $this->dbData  = $dbh->prepare($query);

            for ($i = 1; $i < $numargs; $i++) {
                // if ($this->debug) echo 'db.php, # ' . __line__ .". Argument $i is: " . $arg_list[$i] . "<br />\n";
                $this->dbData->bindParam(self::bindChar.$i,$arg_list[$i]);
            }

            $this->dbData ->setFetchMode(PDO::FETCH_ASSOC);
            $this->dbData ->execute();
            $this->lastInsertId = $dbh->lastInsertId();
        } catch (PDOException $e) {
            $message = $this->errorMessage( __line__, $e, $query, $arg_list);

            if ($this->debug) {
                self::debugOut($message);
            } else {
                self::errorMail($message);
            }

            return false;
        }
        if ($this->debug) {
            self::debugOut('db.php, # ' . __line__ .', Query on input: ' . $query .'<br />');
            for ($i = 1; $i < $numargs; $i++)
                self::debugOut("Param :" . $i. " = " . $arg_list[$i]. "<br>");
        }
        return true;
    }

    /**
     * Executes given query. If the query return no rows, or null value, default value is returned.
     * Otherwise, value of first column in a first row is returned.
     *
     * @param $query Query to be executed
     * @param $default Default value
     *
     * @return
     */
    public function simpleQueryValue($query, $default) {
        $this->simpleQuery($query);
        $r = $this->dbResultFetch();
        $this->closeCursor();
        if ($r){
            $value = reset($r);
            if ($value == null)
                return $default;
            else
                return $value;
        } else {
            return $default;
        }
    }

    /**
     * Executes given query, as described in method multiVariableQuery().
     * If the query return no rows, or null value, default value is returned.
     * Otherwise, value of first column in a first row is returned.
     *
     * @param $query Query to be executed, default value, query params
     *
     * @return
     */
    public function multiVariableQueryValue($query) {
        $arg_list = func_get_args();
        $default = null;
        if (count($arg_list)>=2){
            $default = $arg_list[1];
            unset($arg_list[1]);
        }
        // could be this done better?
        call_user_func_array(array($this, 'multiVariableQuery'), $arg_list);
        $r = $this->dbResultFetch();
        $this->closeCursor();
        if ($r){
            $value = reset($r);
            if ($value == null)
                return $default;
            else
                return $value;
        } else {
            return $default;
        }
    }

    /**
     * Executes given query, as described in method paramQuery().
     * If the query return no rows, or null value, default value is returned.
     * Otherwise, value of first column in a first row is returned.
     *
     * @param $query Query to be executed
     * @param $default Default value
     * @param $params Query params
     *
     * @return
     */
    public function paramQueryValue($query, $default, $params) {
        if (!is_array($params)) return false;
        $this->paramQuery($query, $params);
        $r = $this->dbResultFetch();
        $this->closeCursor();
        if ($r){
            $value = reset($r);
            if ($value == null)
                return $default;
            else
                return $value;
        } else {
            return $default;
        }
    }

    /**
     * Starts a transaction. 
     * 
     * The transaction is a separate flow of database statements issued against current 
     * dataBase object, which will be applied (commited) by the database in a single step, 
     * or reverted (rolled back) as if they never happen. Statements executed within 
     * a transaction are not visible by other transactions (i.e. statements executed by 
     * different dataBase object instance).
     *
     * Transactions can nest. This mean, that you can call beginTransaction() many times
     * against one dataBase object. The transaction will commit if any only if each call
     * to beginTransaction() is paired with a call to commit(). A call to
     * rollback(), at any nesting level, will cause the whole transaction to
     * rollback. The transaction is commited when a commit() call balances
     * with beginTransaction() calls.
     *
     * Terminating script without commiting a transaction, or in any unusual way, will
     * implicitly rollback the transaction.
     *
     * After the transaction has finished (either with a commit, or rollback), there is not
     * implicit transaction in progress. To perform other transactional changes, setup
     * a new transaction, calling beginTransaction(). Normally, you should not need to use
     * this pattern.
     *
     * The transactions are supported only for tables with InnoDB storage engine. They don't
     * work for MyISAM tables;
     *
     * The preffered usage patter follows:
     *
     * $db = new dataBase();
     * 
     * $db->beginTransaction();
     * $db->paramQuery(...);
     * $db->multiVariableQuery(...);
     * some_function($db);
     * $db->simpleQuery(...);
     * some_function_with_separate_transaction();
     * $db->multiVariableQuery(...);
     * $db->commit();
     *
     *
     * function some_function($db) 
     * {
     *     // executed within scope of calling transaction
     *     $db->beginTransaction();
     *     $db->paramQuery(...);
     *     if (...){
     *         // since we are nested transaction, this will not commit immediately
     *         // the transaction may not commit at all, if other function calls rollback()
     *         $db->commit(); 
     *     } else {
     *         // mark the transaction for rollback, now it can not commit never ever
     *         $db->rollback();
     *     }
     * }
     *
     * function some_function_with_separate_transaction() 
     * {
     *     $db = new dataBase();
     *     $db->beginTransaction();
     *     // remember, we can not see changes from other transactions here!
     *     $db->paramQuery(...);
     *     if (...){
     *         // since we are separated transaction, this will commit immediately
     *         $db->commit(); 
     *     } else {
     *         // rollback the transaction now
     *         $db->rollback();
     *     }
     * }
     *
     
     */
    public function beginTransaction()
    {
        if ($this->in_transaction_count == 0){
            // start a transaction
            if ($this->debug) {
                self::debugOut('Starting new transaction<br>');
            }
            $this->was_persistent_connection = persistent_connection;
            $this->switchPersistentConnection(true);

            $this->in_transaction_count = 1;
            $this->transaction_success_count = 0;
            $this->rollback_transaction = false;

            $dbh = $this->setupPDO();
            //$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
            $dbh->beginTransaction();
            
        } else {
            $this->in_transaction_count++;
            if ($this->debug) {
                self::debugOut('incrementing transaction nesting to: ' . $this->in_transaction_count . '<br>');
            }
        }
    }
    
    /**
     * Marks a transaction for rollback. Once called, the transaction can not commit.
     * If this call balances with first beginTransaction(), the transaction is rolled
     * back immediatelly, and it ends (which means that other database statemens are
     * implicitly commited as they are executed).
     */
    public function rollback()
    {
        if ($this->debug) {
            self::debugOut('rollback, transaction nesting: ' . $this->in_transaction_count . '<br>');
        }
        if ($this->in_transaction_count <= 0){
            throw new Exception('Not in a transaction');
        }
        $this->rollback_transaction = true;
        $this->in_transaction_count--;
        $this->endTransaction();
    }

    /**
     * Closes current transaction block with success. If this call balances with first 
     * beginTransaction(), and rollback() has never been called, the transaction 
     * is commited immediatelly, and it ends (which means that other database statemens are
     * implicitly commited as they are executed).
     */
    public function commit()
    {
        if ($this->debug) {
            self::debugOut('commit, transaction nesting: ' . $this->in_transaction_count . '<br>');
        }
        if ($this->in_transaction_count <= 0){
            throw new Exception('Not in a transaction');
        }
        $this->in_transaction_count--;
        $this->endTransaction();
    }
    
    private function endTransaction()
    {
        if ($this->in_transaction_count == 0){
            // closing last transaction block, perform commit or rollback
            if ($this->debug) {
                if ($this->rollback_transaction){
                    self::debugOut('transaction rollback<br>');
                } else {
                    self::debugOut('transaction commit<br>');
                }
            }
            if ($this->rollback_transaction){
                $this->pdo->rollBack();
            } else {
                $this->pdo->commit();
            }
            //$this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            $this->switchPersistentConnection($this->was_persistent_connection);
        }
    }
    
    /**
     * Closes current cursor. Some methods, which drain cursor (like dbResultFetchAll())
     * or expect only one row (like *QueryValue()) close cursor implicitly.
     *
     * Remember to close cursor if you are using this class in persistentConnection mode.
     */
    public function closeCursor()
    {
        try{
            $this->dbData->closeCursor();
        }catch(Exception $e){
            // ignore
        }
        $this->dbData = null;
    }

    private function errorMail($message, $topic=null) {
        if(self::wasEmailSentLast60Sec()) return;
        $message = 'NOTE: dataBase Class send ONLY 1 message per minute to avoid mass email.'."\r\n \r\n".$message;
        $headers =  'From: dataBase class' . "\r\n" .
                    'Reply-To: '.$this->replyToEmail . "\r\n" .
                    'X-Mailer: PHP/' . phpversion().
                    'MIME-Version: 1.0' . "\r\n" .
                    'Content-type: text/html; charset=utf-8' . "\r\n";

        if(!isset($topic)) $topic = 'Database error caught in db.php';
        foreach ($this->errorEmail as $email) {
            mail($email, $topic, $message, $headers);
        }
    }

    private static function wasEmailSentLast60Sec(){
        $lockFile = __DIR__."/../tmp/dataBaseClassEmailLock.txt";
        $lastEmail = false;
        if (file_exists($lockFile)) {
            $lastEmail = filemtime($lockFile);
        }
        if ($lastEmail !== false && (time()-$lastEmail) < 60) {
            return true;
        }
        @touch($lockFile);
        return false;
    }

    private function errorMessage( $line, $e, $query, $params ) {
        $message = 'db.php, line: ' . $line .', <p class="errormsg"> PDO error: ' . $e .'</p><br />
                    Database Query: '.$query.'<br>
                    Parametres array: <pre>'.
                    print_r($params, true).
                    '</pre><br><br>';

        return $message;
    }

    /**
     * static experimental methode
     *
     * build select query automaticly, on argument basic.
     */
    public static function select($columnNameArray, $tableName, $whereArray=array(array('fieldName'=>'1','operator'=>'','fieldValue'=>''))){
        $query = 'SELECT ';
        foreach ($columnNameArray as $column) {
            $query .= self::dbQuote.$column.self::dbQuote.',';
        }
        $query = rtrim($query, ",");
        $query .= ' FROM '.$tableName.' WHERE ';
        $i = 1;
        foreach ($whereArray as $field) {
           $query .= self::dbQuote.$field['fieldName'].self::dbQuote.$field['operator'].self::bindChar.$i.' AND ';
           $variableArray[$i] = $field['fieldValue'];
           $i++;
        }
        $query = rtrim($query, ' AND ');
        $db = new dataBase;
        $db->multiVariableQuery($query, $variableArray);

        return $db->dbResultFetchAll();
    }

    /**
     * this methode can be used for display any array from anywhere
     *
     * @param string $position - put here what you want. just title(name) of array
     * @param array $array - array to display
     *
     * @example dataBase::debugOC('some.php, # ' . __line__ .', my variable', $array_variable );
     */
    public static function debugOC($position, $array) {
        dataBase::debugOut("<pre> --- $position --<br>");
        dataBase::debugOut(print_r ($array, true));
        dataBase::debugOut('----------------------<br /><br /></pre>', true);
    }

    private static function debugOut($text, $onlyHtmlString = false) {
        // TODO: make it configurable
        // useful when debugging stripts generating content other than HTML

        //if ($onlyHtmlString !== true){
        //  error_log($text);
        //}
        print $text;
    }
}
