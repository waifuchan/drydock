<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			modifier.extra_info.php
		Description:	Smarty modifier for retrieving HTML-formatted
					file metadata (like EXIF tags) and returning it
					as a string.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
function smarty_modifier_extra_info($id)
{
	$db = new ThornDBI();
	
	$extrainfo = $db->myresult("SELECT extra_info FROM ".THextrainfo_table." WHERE id=".$id);
	
	if($extrainfo)
	{
		return $extrainfo;
	}

	return "";
}

?>
