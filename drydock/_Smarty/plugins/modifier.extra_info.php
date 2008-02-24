<?php
function smarty_modifier_extra_info($id)
{
	$db = new ThornDBI();
	
	$extrainfo = $db->myresult("SELECT extra_info FROM extra_info WHERE id=".$id);
	
	if($extrainfo)
	{
	return $extrainfo;
	}

	return "";
}

?>
