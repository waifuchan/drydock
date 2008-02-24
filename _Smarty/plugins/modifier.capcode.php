<?php
// I'm doing it this way to avoid a possible security hole.  Look at the filters_new modifier
// comments for more information.
$capcodes=array();
function smarty_modifier_capcode($trip)
{
	include(THpath.'/cache/capcodes.php');
	return($capcodes[$trip]);
}
?>
