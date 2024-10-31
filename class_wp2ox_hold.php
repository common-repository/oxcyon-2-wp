<?php
/**
 * Import Functions Page
 *
 * This will hold functions and classes related to the project import.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.1.0
 * @since       2/18/2014
 */

/**
 * SIM Database Transfer Script
 * The process will be: Categories, Tags, Pages, Posts. This data will be used to
 * fill in the different sections of the WordPress default database.
 */

/**
 * Class wp2ox
 */
class wp2ox {

	/**
	 * PDO connection
	 * @param $dbh
	 */
	protected $dbh;

	/** Sets the PDO */
	function __construct($dbh) {
		$this->dbh = $dbh;
	}
	/**
	 * Get Data from database
	 *
	 * @param $sql
	 * @param $value
	 * @return array
	 */
	public function getData( $sql, $value = null ) {
		// create array for search value
		// establish database connection
		$pdo = $this->dbh;
		// prepare database call
		$pdoObject  = $pdo->prepare( $sql );
		// check for errors
		if (!$pdoObject) {
			echo "\nPDO::errorInfo():\n";
			print_r($pdo->errorInfo());
		}
		// execute the database call
		$pdoObject->execute( $value );
		// return row data
		return $pdoObject->fetchAll( PDO::FETCH_ASSOC );
	}
}

/**
 * Class oxc_selectQuery
 *
 * @var PDO $pdo
 * @var $sql = sql select statement
 * @var $searchVal = Value to select from
 *
 * returns array of data
 */
class oxc_selectQuery extends wp2ox {
    private $pdo;
    private $stmt;
    public function __construct( PDO $pdo ) {
        $this->pdo = $pdo;
    }
    public function queryResults( $sql, $searchVal = null ) {
        $stmt = $this->pdo->prepare( $sql );
        if ( $searchVal ) {
            $stmt->bindParam(':value', $searchVal);
        }
        $stmt->execute();
        $this->stmt = $stmt;
        return $stmt->fetchAll( PDO::FETCH_ASSOC );
    }
    public function updated() {
        if ( $this->stmt->rowCount() >= 1) {
            return true;
        } else {
            return false;
        }
    }
}
/**
 * Class oxc_postCategories
 * Returns array of category ID's (INT)
 *
 * @var $data   = string
 * @var $array  = reference table
 *
 * returns array
 */
class oxc_authorCategoryTag {
    // source data for matching
    protected $idArray = Array();
    // old categories
    protected $data = Array();
    // return data
    protected $results;

    // Construct
    public function __construct( $data, $array ) {
        // bust up string into array
        $this->data     = explode( ', ', $data ); // $oxc_row['Taxonomy'];
        // data to compare against
        $this->idArray  = $array;
        return $this->resultTerms();
    }
    // if the tag is match, add to array
    protected function resultTerms( ) {
        // Start the loop
        foreach ( $this->data as $string ) {
            // see if a category matches
            $newTerm = $this->validateData( $string );
            array_push( $newTerm, $this->results );
        }
        return $this->results;
    }
    // checks to see if it's in array
    protected function validateData( $string ) {
        $newId = array_search($string, $this->idArray);
        if ( $newId ) {
            return $newId;
        } else {
            return false;
        }
    }
}
/**
 * Class oxc_postTags
 * @extends oxc_postCategories
 *
 * @var $data
 * String from taxonomy column
 * @var $array
 * Array of Category IDs
 *
 * returns string of tags
 */
class oxc_postTags extends oxc_authorCategoryTag {

	/**
	 * @param $data
	 * @param $array
	 */
	public function __construct( $data, $array ) {
        parent::__construct($data, $array);
    }

    protected function resultTerms( ) {
        // Start the loop
        foreach ( $this->data as $string ) {
            // see if a category matches
            $newTerm = $this->validateData( $string );
            array_push( $newTerm, $this->results );
        }
        $tagResults = explode( ', ', $this->results );
        return $tagResults;
    }

}

function strip_html_tags( $text ) {
	$text = preg_replace(
		[
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<title[^>]*?>.*?</title>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			"/class\s*=\s*'[^\']*[^\']*'/"
		],
		array('', '', '', '', '', '', '', '', '', '', ''),
		$text );

	return $text;
}


function reportText( $stringH, $string ) {
	echo '<' . $stringH . '>' . $string . '</' . $stringH . '>';
}