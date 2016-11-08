<?php

/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */


class  Table{


	public static $_db=null;	//1.数据库对象 数据类型对象
	public $aColumns = array();   	//2.字段
	public $sTable = null;   	//2.表
	public $primaryKey = null;   	//2.表

	//初始化参数
	public function __construct($sTable,$aColumns,$primaryKey)
	{

		if (null === self::$_db) {
			$sql_details = array(
				'user' => 'root',
				'pass' => '111111',
				'db'   => 'yfcmf',
				'host' => '192.168.80.128'
			);
			self::$_db = self::db($sql_details);
		}
		$this -> sTable = $sTable;
		$this -> aColumns = $aColumns;
		$this -> primaryKey = $primaryKey;

	}


	/**
	 * Database connection
	 *
	 * Obtain an PHP PDO connection from a connection details array
	 *
	 *  @param  array $conn SQL connection details. The array should have
	 *    the following properties
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 *  @return resource PDO connection
	 */
	static function db ( $conn )
	{
		if ( is_array( $conn ) ) {
			return self::sql_connect( $conn );
		}

		return $conn;
	}

	/**
	 * Connect to the database
	 *
	 * @param  array $sql_details SQL server connection details array, with the
	 *   properties:
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 * @return resource Database connection handle
	 */
	static function sql_connect ( $sql_details )
	{
		try {
			$db = @new PDO(
				"mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
				$sql_details['user'],
				$sql_details['pass'],
				array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
			);
		}
		catch (PDOException $e) {
			self::fatal(
				"An error occurred while connecting to the database. ".
				"The error reported by the server was: ".$e->getMessage()
			);
		}

		return $db;
	}



	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 */
	static function limit()
	{
		$sLimit = "";
		if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".mysql_real_escape_string( $_POST['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_POST['iDisplayLength'] );
		}

		return $sLimit;
	}

	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order($aColumns)
	{
		$sOrder = "";
		if ( isset( $_POST['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ )
			{
				if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".
						mysql_real_escape_string( $_POST['sSortDir_'.$i] ) .", ";
				}
			}

			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}
		}

		return $sOrder;

	}



	/*
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 * @param  array $columns Column information array
	 * @return string SQL where clause
	 */
	static function filter ($aColumns)
	{
		$sWhere = "";
		if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string( $_POST['sSearch'] )."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}

		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string($_POST['sSearch_'.$i])."%' ";
			}
		}


		return $sWhere;
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal ( $msg )
	{
		echo json_encode( array(
			"error" => $msg
		) );

		exit(0);
	}

	/**
	 * Execute an SQL query on the database
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from bind() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */

	static function sql_exec ($db,$bindings, $sql=null )
	{
		// Argument shifting
		if ( $sql === null ) {
			$sql = $bindings;
		}
		$stmt = $db->prepare( $sql );
		//echo $sql;
		// Bind parameters
		if ( is_array( $bindings ) ) {
			for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
				$binding = $bindings[$i];
				$stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
			}
		}

		// Execute
		try {
			$stmt->execute();
		}
		catch (PDOException $e) {
			self::fatal( "An SQL error occurred: ".$e->getMessage() );
		}

		// Return all
		//return $stmt->fetchAll( PDO::FETCH_BOTH );
		return $stmt->fetchAll( PDO::FETCH_ASSOC );
	}



	/**
	 * Return a string from an array or a string
	 *
	 * @param  array|string $a Array to join
	 * @param  string $join Glue for the concatenation
	 * @return string Joined string
	 */
	static function _flatten ( $a, $join = ' AND ' )
	{
		if ( ! $a ) {
			return '';
		}
		else if ( $a && is_array($a) ) {
			return implode( $join, $a );
		}
		return $a;
	}



	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array|PDO $conn PDO connection resource or connection parameters array
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @param  string $whereResult WHERE condition to apply to all queries
	 *  @return array          Server-side processing response array
	 */
	public function complex ($whereResult=null)
	{
		$bindings = array();
		$db = self::$_db;
		// Build the SQL query string from the request
		$columns = $this->aColumns;
		$sTable = $this->sTable;
		$primaryKey = $this->primaryKey;
		$sLimit = self::limit($columns );
		$sOrder = self::order($columns );
		$sWhere = self::filter($columns );

		$whereResult = self::_flatten( $whereResult );
		if ( $whereResult ) {
			$sWhere = $sWhere ?
				$sWhere .' AND '.$whereResult :
				'WHERE '.$whereResult;
		}

		// Main query to actually get the data

		$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $columns))."`
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
		";
		$rResult = self::sql_exec( $db, $bindings,$sQuery);

		// Data set length after filtering
		$resFilterLength = self::sql_exec( $db, "SELECT FOUND_ROWS() AS F");
		//var_dump($resFilterLength);
		$recordsFiltered = $resFilterLength[0]['F'];

		// Total data set length
		$resTotalLength = self::sql_exec( $db, $bindings,
			"SELECT COUNT(`{$primaryKey}`) AS T
			 FROM   `$sTable` "
		);

		//var_dump($resTotalLength);
		$recordsTotal = $resTotalLength[0]['T'];

		/*
		 * Output
		 */
		$output = array(
			"sEcho"            => isset ( $_POST['sEcho'] ) ? intval( $_POST['sEcho'] ) : 0,//当前是第几页
			"iTotalRecords"    => intval( $recordsTotal ),//数据表中总的记录条数【没有搜索条件时的总记录条数】
			"iTotalDisplayRecords" => intval( $recordsFiltered ),//按照搜索条件（去掉limit限制），获取的总的记录条数【有搜索条件时的总记录条数】
			"aaData"            => array(), //定义一个临时的数组，存放获取的数据信息
			"result"		=>$rResult
		);
		return $output;
	}







}

