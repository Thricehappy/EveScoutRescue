<?php

// Mark all entry pages with this definition. Includes need check check if this is defined
// and stop processing if called direct for security reasons.
define('ESRC', TRUE);

/**
 * Separate data entry code from data entry page content. 
 * 
 * This file contains all the logic to check data, update database and prepare error logging
 * 
 */

include_once '../class/db.class.php';
include_once '../class/caches.class.php';

 /**
  * Test provided input data to be valid.
 * @param unknown $data data to check
 * @return string processed and cleaned data
 */

function test_input($data) 
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars_decode($data);
	return $data;
}


// check if the request is made by a POST request
if (isset($_POST['sys_adj'])) {
	// yes, process the request
	$db = new Database();

	$activitydate = $pilot = $system = $aidedpilot = $errmsg = $entrytype = $noteDate = '';

	$activitydate = gmdate("Y-m-d H:i:s", strtotime("now"));
	$pilot = test_input($_POST["pilot"]);
	$system = test_input($_POST["sys_adj"]);
	$aidedpilot = test_input($_POST["aidedpilot"]);
	$notes = test_input($_POST["notes"]);

	// check the system
	if (isset($system))
	{
		// make the system uppercase
		$system = strtoupper($system);
	}
	else 
	{
		$errmsg = $errmsg . "No system name is set.";
	}
	
	//FORM VALIDATION
	$entrytype = 'agent';
	if (empty($aidedpilot)) { 
		$errmsg = $errmsg . "You must indicate the name of the capsuleer who required rescue."; 
	}
	//END FORM VALIDATION

	//display error message if there is an error
	if (!empty($errmsg)) {
		$redirectURL = "search.php?sys=". $system ."&errmsg=". urlencode($errmsg);
	} 
	// otherwise, perform DB UPDATES
	else {
		// create a new cache class
		$caches = new Caches($db);
		
		// add a new agent activity
		$caches->addActivity($system, $pilot, $entrytype, $activitydate, $notes, $aidedpilot);

		// add note to cache
		$noteDate = '[' . date("M-d", strtotime("now")) . '] ';
		$agent_note = '<br />' . $noteDate . 'Rescue Agent: '. $pilot . '; Aided: ' . $aidedpilot;
		if (!empty($notes)) { $agent_note = $agent_note. '<br />' . $notes; }
		$caches->addNoteToCache($system, $agent_note);
		
		$redirectURL = "search.php?sys=". $system;
	}
	?>
		<script>
			window.location.replace("<?=$redirectURL?>")
		</script>
	<?php 
	//END DB UPDATES
}

?>