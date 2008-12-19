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
					- bool $_POST['chkpost_____'] - post globalid (could be multiple)
			- report (report a post)
				Report params:
					- string $_POST['report'] - "report" just means the report button was successful
					- string $_POST['board'] - board folder
					- bool $_POST['chkpost_____'] - post globalid
			- report2 (report a post, with category)
				Report params:
					- string $_POST['report'] - "report2" signifies second stage
					- string $_POST['board'] - board folder
					- int $_POST['post'] - post globalid
					- int $_POST['category'] - classification					
		
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
			$message = "No password or board provided!";
		}
		else
		{
			$db = new ThornModDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
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
		$message = "what";
		$timeout = 5000; // we'll change it to something else if the report is ok so far
		
		if( !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No board provided!";
		}
		else
		{
			$db = new ThornToolsDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{			
				$found_post = -1;
				
				foreach(array_keys($_POST) as $entry)
				{
					if(preg_match('/^chkpost\d+$/', $entry ) && $_POST['entry'] != 0 )
					{
						// strip out the leading "chkpost" and set the integer value to $found_post
						$found_post = intval(preg_replace('/^chkpost/', '', $entry ));
						break;
					}
				}
				
				if( $found_post <= 0 )
				{
					$message = "No valid post to report!";
				}
				else
				{	
					$status = $db->checkreportpost( $found_post, $board);
					
					switch( $status )
					{
						case 0: // valid report attempt
							$message = 'Reporting post #'.$found_post.' in /'.$_POST['board'].'/.<br>
										<form action="misc.php" method="post">Report category:<br>
										<input type="radio" name="category" value="1"> Illegal content<br>
										<input type="radio" name="category" value="2"> Rule violation<br>
										<input type="radio" name="category" value="3"> Terrible posting<br>
										<input type="hidden" name="report" value="report2">
										<input type="hidden" name="post" value="'.$found_post.'">
										<input type="hidden" name="board" value="'.$_POST['board'].'">
										<input type="submit"></form>';
							$timeout = -1; // disable auto-closing of the popup
							
							break;
						case 1:
							$message = "You must wait before reporting another post.";
							break;
						case 2:
							$message = "Someone with your IP has already reported this post.";
							break;
						case 3:
							$message = "This post does not exist!";
							break;
						case 4:
							$message = "This post has already been reviewed.";
							break;
						default:
							$message = "Unknown error!";
							break;
					}
					
				}
			}
		}
		
		$sm=sminit("popup");
		$sm->assign("text",$message);
		$sm->assign("timeout", $timeout);
		$sm->assign("title", "Post reporting form");
		$sm->display("popup");
		die();
	}
	elseif( $_POST['report'] == "report2") // Second stage, with category submission
	{
		$message = "what";
		
		if( !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No board provided!";
		}
		else
		{
			$db = new ThornToolsDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{			
				if( $_POST['post'] <= 0 || isset($_POST['post']) == false )
				{
					$message = "No valid post to report!";
				}
				else
				{	
					if( isset($_POST['category']))
					{
						$category = intval($_POST['category']);
						
						if( $category > 0 && $category < 4)
						{
							if( $db->checkreportpost( $_POST['post'], $board) == 0)
							{
								$db->reportpost($_POST['post'], $board, $category);
							}
							
							// We'll just tell them they got this far even if
							// it was invalid for whatever reason.
							$message = "Your report has been submitted.";
						}
						else
						{
							$message = "Invalid category provided!";
						}
					}
					else
					{
						$message = "No category provided!";
					}
				}
			}
		}
		
		$sm=sminit("popup");
		$sm->assign("text",$message);
		$sm->assign("timeout", 5000);
		$sm->assign("title", "Post reporting form");
		$sm->display("popup");
		die();	
	}

?>