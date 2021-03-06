<?php
define('ESRC', TRUE);

include_once '../class/db.class.php';

//CREATE QUERY TO DB AND PUT RECEIVED DATA INTO ASSOCIATIVE ARRAY

$defaultlimit = 5;

/**
 * query - parameter should contain a part of the WH system name. It's expanded by the code
 * with wildcards.
 * type - supports the values 'cache' (search active caches) and
 *        'system' (search known systems); this may be extended later if necessary
 *        an unknown type make a fall back to all systems
 */

// do not return anything if querystring is not set
if (isset($_REQUEST['query']) && !empty($_REQUEST['query'])) {
	$query = ($_REQUEST['query'] == '%') ? '' : '%' . strtoupper($_REQUEST['query']) . '%';
}
else {
	$query = '%';
}

$type = $_REQUEST['type'] ?? 'system';	// set system as default type if no type is set

switch ($type) {
	case 'cache' :	// search for active caches
		$sql = "SELECT System FROM cache WHERE System LIKE :query AND Status <> 'Expired' ORDER BY System limit :limit";
	break;

	case 'freesystem':	// search for systems without a cache
		$sql = "SELECT System FROM wh_systems WHERE System LIKE :query AND system not in (select system from cache where status <> 'Expired') and (DoNotSowUntil is NULL or DoNotSowUntil is not null and DoNotSowUntil < CURRENT_DATE()) order by system limit :limit";
	break;

	case 'system' :	// search for all wormhole systems
	default :	
		$sql = "SELECT System FROM wh_systems WHERE System LIKE :query ORDER BY System limit :limit";
}

// result data
$result = array();

$db = new Database();
$db->query($sql);
$db->bind(':query', $query);
$db->bind(':limit', $defaultlimit);
$rows = $db->resultset();
$db->closeQuery();

// copy result data to result array
foreach ( $rows as $value ) {
	$result [] = $value ['System'];
}

// RETURN JSON ARRAY
echo json_encode ( $result );
?>
