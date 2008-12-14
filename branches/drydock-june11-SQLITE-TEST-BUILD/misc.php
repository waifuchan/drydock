<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			misc.php
		Description:	Perform miscellaneous actions.
		This will be used typically for short AJAX calls or special
		kinds of POST form submission.
		
		Things that take $_GET:
			- nothing yet
								
		Things that take $_POST:
			- delete (delete posts)
				Delete params:
				    - string $_POST['delete'] - just means the delete button was successful
					- string $_POST['password'] - post password
					- bool $POST['chkpost_____'] - post globalid (could be multiple)
			- report (report a post)
				Report params:
					- string $_POST['report'] - just means the report button was successful
					- string $_POST['board'] - board folder
					- bool $POST['chkpost_____'] - post globalid
					
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("config.php");
	require_once("common.php");
	
	if ($_POST['delete'] == "delete" ) // Delete a post/posts
	{
		
	}
	elseif($_POST['report'] == "Report" ) // Report a post
	{

	}

?>