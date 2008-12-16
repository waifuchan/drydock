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
	
	if ($_POST['delete'] == "Delete" ) // Delete a post/posts
	{
		$message = "what";
		
		if( !isset($_POST['password']) || $_POST['password'] == ""
		||  !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No password provided!";
		}
		else
		{
			$db = new ThornModDBI();
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{
				// This will be an array of global IDs
				$posts_to_delete = array();
				
				foreach(array_keys($_POST) as $entry)
				{
					if(preg_match('/^chkpost\d+$/', $entry ) && $_POST['entry'] != 0 )
					{
						// strip out the leading "chkpost" and add the id to the posts_to_delete array
						$posts_to_delete[] = intval(preg_replace('/^chkpost/', '', $entry ));
					}
				}
				
				if( count($posts_to_delete) <= 0 )
				{
					$message = "No valid posts to delete!";
				}
				else
				{
					$delcount = $db->userdelpost($posts_to_delete, $board, $_POST['password']);
					$message = $delcount." posts deleted.";
				}
			}
		}
		
		$sm=sminit("popup");
		$sm->assign("text",$message);
		$sm->assign("timeout", "5000"); // 5000 ms
		$sm->assign("title", "Post deletion form");
		$sm->display("popup");
		die();
	}
	elseif($_POST['report'] == "Report" ) // Report a post
	{

	}

?>