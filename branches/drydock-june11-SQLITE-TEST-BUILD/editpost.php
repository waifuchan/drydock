<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			editpost.php
		Description:	Edit and perform moderation actions upon posts.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

	require_once("config.php");
	require_once("common.php");
	
	/*
		OKAY HERE COMES THE HUGE LIST OF PARAMETERS THAT CAN BE PASSED TO THIS FUNCTION (aka: documentation)
		post - 		The global ID of the post (can't change this, but it's still necessary)
		name - 		The name of the poster
		trip - 			The tripcode of the poster
		link -			Sage goes in the link field :o/saggy goes in da emo
		title - 		The subject (if this post begins a thread)
		body - 		The text of the post
		visible-		Hide/unhide the post	
		pin-			STICKY DIS SHIT
		lock-			LOCK DIS SHIT	
		permasage- 		THE SAGE OF DEATH
		remimage____ - 	Remove the image with the hash matching whatever's in the blank.

		Was it good for you too?
	*/
	
	$db=new ThornPostDBI();
	/*
		What can be done with this is a touchy issue, and thus my idea is the following:
		Unrestricted access to post editing is reserved for admins only.
		Moderators have the ability to sticky, lock, permasage, ban, and hide/unhide posts.  No editing functions are available.
	*/

		if((is_in_csl(intval($_GET['board']), $_SESSION['mod_array'])!=1) && ($_SESSION['admin'] !=1) && ($_SESSION['mod_global'] !=1))
		{
			THdie("You are not permitted to edit posts on this board");
		}
		if ((isset($_SESSION["admin"])) && ($_SESSION["admin"] == 1))
		{ 
			$adminpowers = 1;
		} 
		else 
		{
			$adminpowers = 0;
		}

		if (!isset($_GET['post']) || !isset($_GET['board'])) 
		{
			THdie("No thread and/or board parameter, nothing to do!");
		} 
		else 
		{
			$post = intval($_GET['post']); // SQL injection protection :]
			$board = $db->getboardnumber($_GET['board']);
			$qstring = "SELECT * FROM ".THreplies_table." WHERE globalid=".$post." AND board=".$board;
			//echo $qstring."<br>/n";
			//$postquery = $db->myquery($qstring);
			$posttoedit=$db->myassoc($qstring);

			if( !$posttoedit )
			{
				$qstring = "SELECT * FROM ".THthreads_table." WHERE globalid=".$post." AND board=".$board;
				$posttoedit=$db->myassoc($qstring);
				$threadquery = 1;
				if( !$posttoedit )
				{
					THdie("Post with global ID of ".$_GET['post']." and board ".$_GET['board']." does not exist. :(");
				}
			}
		}
		//print_r($posttoedit);

		$boardname = $db->getboardname($board);
		$isanythingchanged = 0;
		$unvisibletime=0;

		//name
		if (isset($_POST['name']) && $adminpowers > 0 && $posttoedit['name'] != $_POST['name']) //must have admin powers to edit
		{ 
			$name = $_POST['name'];
			$isanythingchanged = 1;
		} 
		else 
		{
			$name = $posttoedit['name'];
		}

		//trip
		if (isset($_POST['trip']) && $adminpowers > 0 && $posttoedit['trip'] != $_POST['trip'])
		{
			$trip = $_POST['trip'];
			$isanythingchanged = 1;
		} 
		else 
		{
			$trip = $posttoedit['trip'];
		}

		//link
		if (isset($_POST['link']) && $adminpowers > 0 && $posttoedit['link'] != $_POST['link'])
		{
			$link = $_POST['link'];
			$isanythingchanged = 1;
		} 
		else 
		{
			$link = $posttoedit['link'];
		}

		//title
		if (isset($_POST['title']) && $adminpowers > 0 && $posttoedit['title'] != $_POST['title']) 
		{
			$title = $_POST['title'];
			$isanythingchanged = 1;
		} 
		else 
		{
			$title = $posttoedit['title'];
		}

		//body
		if (isset($_POST['body']) && $adminpowers > 0 && $posttoedit['body'] != $_POST['body']) 
		{
			$body = $_POST['body'];
			$isanythingchanged = 1;
		} 
		else 
		{
			$body = $posttoedit['body'];
		}

		//visibility
		if (isset($_POST['visible']) && $posttoedit['visible'] != $_POST['visible']) 
		{
			$visible = $_POST['visible'];
			$unvisibletime = time()+(THtimeoffset*60);
			$isanythingchanged = 1;
		} 
		else 
		{
			$visible = $posttoedit['visible'];
			if($posttoedit['unvisibletime'] != NULL) { $unvisibletime = $posttoedit['unvisibletime']; } else { $unvisibletime=0; }
		}

		if($threadquery == NULL) //we are editing a reply
		{
			if(isset($posttoedit['thread'])) { $thread = $posttoedit['thread']; } else { $thread=$posttoedit['id']; }
			if(isset($_POST['editsub'])) 
			{
				$pin = intval($_POST['pin']);
				if($pin != $db->myresult("SELECT pin FROM ".THthreads_table." WHERE id=".$thread))
					$pindelta = 1;
					
				$lock = intval($_POST['lawk']);
				if($lock != $db->myresult("SELECT lawk FROM ".THthreads_table." WHERE id=".$thread))
					$lockdelta = 1;
					
				$psage = intval($_POST['permasage']);
				if($psage != $db->myresult("SELECT permasage FROM ".THthreads_table." WHERE id=".$thread))
					$psdelta = 1;
			} 
			else 
			{
				$pin = $db->myresult("SELECT pin FROM ".THthreads_table." WHERE id=".$thread);
				$pindelta = 0;
				$lock = $db->myresult("SELECT lawk FROM ".THthreads_table." WHERE id=".$thread);
				$lockdelta = 0;
				$psage = $db->myresult("SELECT permasage FROM ".THthreads_table." WHERE id=".$thread);
				$psdelta = 0;
			}
		} 
		else 
		{ // we are editing the OP of a thread
			if (isset($_POST['editsub'])) 
			{
				$pin = intval($_POST['pin']);
				if($posttoedit['pin'] != $pin);
					$pindelta = 1;
				
				$lock = intval($_POST['lawk']);
				// GODDAMN IT WHY DID THORN HAVE TO BE RETARDED AND USE "LAWK"
				if($posttoedit['lawk'] != $lock);
						$lockdelta = 1;
				
				$psage = intval($_POST['permasage']);
				
				if($posttoedit['permasage'] != $psage);
						$psdelta = 1;
			} 
			else 
			{
				$pin = $posttoedit['pin'];
				$pindelta = 0;
				$lock = $posttoedit['lawk'];
				$lockdelta = 0;
				$psage = $posttoedit['permasage'];
				$psdelta = 0;
			}
			$thread = $posttoedit['id'];
		}
		// LOAD STUFF THAT CAN'T BE CHANGED
		$imgidx 	= $posttoedit['imgidx'];
		$ip 		= $posttoedit['ip'];
		$time 		= $posttoedit['time'];
		$id 		= $posttoedit['id'];
		//$bump 		= $posttoedit['bump'];
		$images = $db->getimgs($imgidx);
		foreach($images as $img)
		{
			if($_POST['remimage'.strval($img['hash'])] != 0 && isset($_POST['remimage'.strval($img['hash'])]) )
			{
				
				if($img['extra_info']>0)
				{
					$db->myquery("delete from ".THextrainfo_table." where id=".$img['extra_info']); // delete any associated extra_info
				}
				$path=THpath."images/".$posttoedit['imgidx']."/";
				unlink($path.$img['name']);
				unlink($path.$img['tname']);
				$db->myquery("update ".THimages_table." set hash='deleted' where id=".$posttoedit['imgidx']." and hash='".$db->escape_string($img['hash'])."'"); //"this fixes stupd syntax highlighting in my editor >:[
				$actionstring = "Delete img\timgidx:".$posttoedit['imgidx']."\tn:".$img['name'];
				writelog($actionstring,"moderator");				
			}
		}

		if( $isanythingchanged > 0 )
		{
			if($threadquery == NULL)
			{
				$db->myquery("update ".THreplies_table." set name='".$db->escape_string($name)."', trip='".$db->escape_string($trip)."', title='".$db->escape_string($title)."', body='".$db->escape_string($body)."', visible=".$visible.", unvisibletime=".$unvisibletime.", link='".$db->escape_string($link)."' where globalid=".$post." AND board=".$board);
				$actionstring = "Edit\tpid:".$post."\tb:".$board;
			} 
			else 
			{
				$db->myquery("update ".THthreads_table." set name='".$db->escape_string($name)."', trip='".$db->escape_string($trip)."', title='".$db->escape_string($title)."', body='".$db->escape_string($body)."', visible=".$visible.", unvisibletime=".$unvisibletime.", link='".$db->escape_string($link)."' where globalid=".$post." AND board=".$board);
				$actionstring = "Edit\ttid:".$post."\tb:".$board;	
			}
			smclearcache($board, -1, $thread); // clear the associated cache for this thread
			writelog($actionstring,"moderator");
		}

		if($lockdelta > 0)
		{
			$actionstring = "lock\tt:".$thread."\tb:".$board;
			writelog($actionstring,"moderator");
			$db->myquery("update ".THthreads_table." set lawk=".intval($lock)." WHERE id=".$thread);
		}

		if($pindelta > 0)
		{
			$actionstring = "pin\tt:".$thread."\tb:".$board;
			writelog($actionstring,"moderator");
			$db->myquery("update ".THthreads_table." set pin=".intval($pin)." WHERE id=".$thread);
		}

		if($psdelta > 0)
		{
			$actionstring = "psage\tt:".$thread."\tb:".$board;
			writelog($actionstring,"moderator");
			$db->myquery("update ".THthreads_table." set permasage=".intval($psage)." WHERE id=".$thread);
		}

		$threadid = $db->myresult("SELECT globalid FROM ".THthreads_table." WHERE id=".$thread);

		if(isset($_POST['modban']) || isset($_POST['moddo']))
		{
			if ($_POST['modban']!="nil"||$_POST['moddo']!="nil") 
			{
				$moddb=new ThornModDBI();

				//Get post
				$suckid=$posttoedit['id'];
				if ($posttoedit['thread']) 
				{
					// this is a reply
					$suckisthread=false;
					$shit = "SELECT thread FROM ".THreplies_table." where globalid=".$post." and board=".$board;
					$grabid = mysql_result(mysql_query($shit),0);
					$fuck = "SELECT globalid FROM ".THthreads_table." where id=".$grabid." and board=".$board;
					$threadop = mysql_result(mysql_query($fuck),0);
					$actionstring = "delete:\tp:".$threadop."\tb:".$board."\tp:".$posttoedit['globalid'];
					writelog($actionstring,"moderator");
					
					if(THuserewrite)
					{
					$diereturn='Post(s) deleted.<br><a href="'.THurl.$boardname.'/thread/'.$threadop.'">Return to thread</a>';
					}
					else
					{
					$diereturn='Post(s) deleted.<br><a href="'.THurl.'drydock.php?b='.$boardname.'&i='.$threadop.'">Return to thread</a>';
					}
				} 
				else 
				{
					$suckisthread=true;
					$actionstring = "delete\tt:".$posttoedit['globalid']."\tb:".$board;
					writelog($actionstring,"moderator");
					
					if(THuserewrite)
					{
					$diereturn='Post(s) deleted.<br><a href="'.THurl.$boardname.'">Return to board</a>';
					}
					else
					{
					$diereturn='Post(s) deleted.<br><a href="'.THurl.'drydock.php?b='.$boardname.'">Return to board</a>';
					}
				}

				if ($_POST['modban']=="banip") 
				{
					$moddb->banipfrompost($suckid,$suckisthread,false,$_POST['privatebanreason'],$_POST['publicbanreason'],$_POST['adminbanreason'],$_POST['banduration'], $_SESSION['username']." via mod panel");
				}
				elseif ($_POST['modban']=="bansub") 
				{
					$moddb->banipfrompost($suckid,$suckisthread,true,$_POST['privatebanreason'],$_POST['publicbanreason'],$_POST['adminbanreason'],$_POST['banduration'],$_SESSION['username']." via mod panel");
				}
				elseif ($_POST['modban']=="banthread" && $adminpowers>0) //MOOT MOOT LOL 
				{
					$moddb->banipfrompost($thread,true,false,$_POST['privatebanreason'],$_POST['publicbanreason'],$_POST['adminbanreason'],$_POST['banduration'], $_SESSION['username']." via mod panel (threadban)");
					$posts=$db->myquery("select * from ".THreplies_table." where thread=".$thread);
					while ($reply=mysql_fetch_assoc($posts))
					{
						$suckid=$reply['id'];
						$moddb->banipfrompost($suckid,false,false,$_POST['privatebanreason'],$_POST['publicbanreason'],$_POST['adminbanreason'],$_POST['banduration'],$_SESSION['username']." via mod panel (threadban)");
					}
				}
							
			if($adminpowers > 0)
			{
				if ($_POST['moddo']=="killpost") 
				{
					smclearcache($board, -1, $thread); // clear the associated cache for this thread
					smclearcache($board, -1, -1); // AND the board
					delimgs($moddb->delpost($suckid,$suckisthread));
				}
				elseif ($_POST['moddo']=="killip") 
				{
					delimgs($moddb->delipfrompost($suckid,$suckisthread,false));
				}
				elseif ($_POST['moddo']=="killsub") 
				{
					delimgs($moddb->delipfrompost($suckid,$suckisthread,true));
				}
				if($_POST['moddo']!="nil")
				{
					//Display our link back.
					THdie($diereturn);
				}
			} 
			else 
			{
				die("You lack sufficient ability to delete this post!\n");
			}
		}
	}
	if(isset($_POST['movethread']) && $_POST['movethread']!="nil" && $adminpowers > 0)
	{
		$destboard = intval($_POST['movethread']);
		if($db->myresult("SELECT COUNT(*) FROM ".THboards_table." WHERE id=".$destboard) < 1)
		{
			die("You can't move a thread to a board that doesn't exist!");
		}
	
		// Clear the relevant caches
		smclearcache($board, -1, $thread); // clear the associated cache for this thread
		smclearcache($board, -1, -1); // clear the associated cache for the original board
		smclearcache($destboard, -1, -1); // clear the associated cache for the target board
		
		$newthreadspot = $db->getglobalid($destboard);
		$db->myquery("update ".THthreads_table." set globalid=".$newthreadspot.", board=".$destboard." where id=".$thread);
		
		$actionstring = "Move thread\t(t:".$thread.",ob:".$posttoedit['board'].") => (tid:".$newthreadspot.",b:".$destboard.")";
		writelog($actionstring,"moderator");
	
		$posts=$db->myquery("select * from ".THreplies_table." where thread=".$thread." order by globalid asc");
		while ($reply=mysql_fetch_assoc($posts))
		{
		$db->myquery("update ".THreplies_table." set globalid=".$db->getglobalid($destboard).",board=".$destboard." where id=".$reply['id']);
		}
		
		if(THuserewrite)
		{
			die('Thread moved.<br><a href="'.THurl.$boardname.'/thread/'.$newthreadspot.'">Return to thread</a>');
		}
		else
		{
		   die('Thread moved.<br><a href="'.THurl.'drydock.php?b='.$boardname.'&i='.$newthreadspot.'">Return to thread</a>');
		}
	}

	echo '<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN">';
	echo "<html>\n";
	echo "<head>\n";
	echo " <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">\n";
	echo " <title>".THname." Moderator Window</title>\n";
	echo " <link rel=\"stylesheet\" type=\"text/css\" href=\"".THurl."tpl/".THtplset."/futaba.css\" title=\"Futaba-ish Stylesheet\" />\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<form name=\"postedit\" action=\"".THurl."editpost.php?post=".$post."&board=".$boardname."\" method=\"post\">\n";

	echo '<font size="+1">';
	echo 'Currently editing <a href="'.THurl;
	if(THuserewrite)
	{
		echo $boardname.'/thread/';
	} 
	else 
	{
		echo 'drydock.php?b='.$boardname.'&i=';
	}
	echo $threadid.'#'.$post.'">p.'.$post.'</a> in thread '.$threadid.' in /'.$boardname.'/';
	
	echo "</font><hr width=\"70%\" align=\"left\"/>\n";
	
	echo "<table width=\"80%\"><tbody>\n<tr>";
	echo '<td><b>Public ID:</b> '.$_GET['post'].'</td>';
	echo '<td><b>Poster time:</b> '.strftime(THdatetimestring,$time).'</td>';
	echo "</tr><tr>\n";
	echo '<td><b>Private ID:</b> '.$id.'</td><td><b>Poster IP:</b> '.long2ip($ip)."</td></tr>\n";

	echo '<tr>';
	echo '<td><b>Poster name:</b><br> <input name="name" ';
	if($adminpowers == 0){ echo 'disabled '; }
	echo 'type="text" value="'.$name.'"></td>';
	echo '<td><b>Poster trip:</b><br> <input name="trip" ';
	if($adminpowers == 0){ echo 'disabled '; }
	echo 'type="text" value="'.$trip.'"></td>';
	echo "</tr><tr>\n";
	echo '<td><b>Poster link:</b><br> <input name="link" ';
	if($adminpowers == 0){ echo 'disabled '; }
	echo 'type="text" value="'.$link.'"></td>';
	echo '<td><b>Post subject (if OP):</b><br> <input name="title" ';
	if($adminpowers == 0){ echo 'disabled '; }
	echo 'type="text" value="'.$title.'"></td>';
	echo '</tr></tbody></table>';

	echo "\n";

	echo '<table width="90%"><tbody>';
	echo '<tr>';
	echo '<td><b>Post body:</b></td>';
	echo '<td><textarea ';
	if($adminpowers == 0){ echo 'disabled '; }
	echo 'name="body" cols="48" rows="6" >'.$body.'</textarea></td>';
	echo "</tr></tbody></table>\n";
	echo "<table width=\"90%\"><tbody><tr><td>\n";

	echo "<table><tbody><tr>\n<td><b>Modify thread:</b><br></td>\n<td><b>Visibility:</b><br></td></tr>\n";

	echo '<tr><td>';
	if($lock)
	{
		echo '<input type="checkbox" name="lawk" checked="checked" value="1">Locked';
	} 
	else 
	{
	echo '<input type="checkbox" name="lawk" value="1">Locked';
	}
	echo '</td><td>';

	if( $visible > 0 )
	{
		echo '<input type="radio" name="visible" checked="1" value="1"> Visible';
	} 
	else 
	{
		echo '<input type="radio" name="visible" value="1"> Visible';
	}
	echo "</td></tr>\n<tr><td>";
	
	
	if($pin)
	{
		echo '<input type="checkbox" name="pin" checked="checked" value="1">Stickied';
	} 
	else 
	{
		echo '<input type="checkbox" name="pin" value="1">Stickied';
	}
	echo '</td><td>';
	
	if( $visible > 0 )
	{
		echo '<input type="radio" name="visible" value="0"> Invisible';
	} 
	else 
	{
		echo '<input type="radio" name="visible" checked="1" value="0"> Invisible';
	}
	echo "</td></tr>\n<tr><td>";
	
	
	if($psage)
	{
		echo '<input type="checkbox" name="permasage" checked="checked" value="1">Permasage';
	} 
	else 
	{
		echo '<input type="checkbox" name="permasage" value="1">Permasage';
	}
	echo '</td><td>';
	
	echo '<b>Last changed:</b> ';
	if($unvisibletime != 0)
	{
		echo strftime(THdatetimestring,$unvisibletime).'';
	} 
	else
	{
		echo 'Never.';
	}
	echo '</td></tr></tbody></table>';
	
	echo '</td>';
	
	echo '<td><b>Delete images:</b><br>';
	
	$images = $db->getimgs($imgidx);
	$imgiterator = 0;
	foreach($images as $img)
	{
		$imgiterator++;
		echo '<input type="checkbox" name="remimage'.strval($img['hash']).'" value="1">';
		echo '<a href="'.THurl.'images/'.$imgidx.'/'.$img['name'].'">'.$imgiterator.'</a> ';
		if( $imgiterator%2 == 0)
		{
			echo "<br>\n";
		}
	}
	if($imgiterator == 0)
	{
		echo "No images<br>\n";
	}
	
	echo "</td></tr>\n<tr>";
	
	if($adminpowers > 0)
	{ 
		echo "<td><b>Delete:</b><br>\n";
		echo "<select name=\"moddo\">\n";
		echo "<option value=\"nil\" selected=\"selected\">&#8212;</option>\n";
		echo "<option value=\"killpost\">Delete this post</option>\n";
		echo "<option value=\"killip\">Delete all posts from this poster's IP</option>\n";
		echo "<option value=\"killsub\">Delete all posts from this poster's subnet</option>\n";
		echo "</select><br>\n";
	} 
	else 
	{
		echo "<td>Deletion functions are not available at your access level.<br>Please use the hide functions instead</td>\n";
	}
		
	echo "<b>Ban:</b><br>\n";
	echo "<select name=\"modban\">\n";
	echo "<option value=\"nil\" selected=\"selected\">&#8212;</option>\n";
	echo "<option value=\"banip\">Ban this poster's IP</option>\n";
	echo "<option value=\"bansub\">Ban this poster's subnet</option>\n";
	if($adminpowers > 0 && !$posttoedit['thread']){ echo "<option value=\"banthread\">Ban this thread</option>\n"; }
	
	echo "</select>\n";
	
	if(!$posttoedit['thread'] && $adminpowers > 0)
	{
		echo '<br><b>Move thread:</b><br>';
		echo '<select name="movethread">';
		echo "<option value=\"nil\" selected=\"selected\">&#8212;</option>";
		$boards=$db->myquery("select * from ".THboards_table." order by id asc");
		while ($toboard=mysql_fetch_assoc($boards))
		{
			if($toboard['id']!=$board)
			{
				echo "<option value=\"".$toboard['id']."\">Move to /".$toboard['folder']."/</option>\n";
			}
		}
		echo "</select>\n";
		echo "<br><b>Warning:</b> hiding/deleting this post will hide/delete all replies.";
	}
	
	echo '</td>';
	
	echo '<td><b>Ban options:</b><br>
		<table><tbody><tr>
		<td>Public ban reason:</td>
		<td><input type="text" name="publicbanreason" size="20" /></td></tr>
		<tr><td>Private ban reason:</td>
		<td><input type="text" name="privatebanreason" size="20" /></td></tr>
		<tr><td>Admin ban reason:
		<td><input type="text" name="adminbanreason" size="20" /></td></tr>
		<tr><td>Duration:
		<td><input type="text" name="banduration" size="20" /> hrs</td>
		</tr></tbody></table>
		</td></tr>';
	
	
	echo '</tbody>';
	echo "</table></div>\n";
	echo "<input type=\"hidden\" name=\"editsub\" value=\"1\">\n";
	echo "<INPUT type=\"submit\" value=\"Edit\"> <INPUT type=\"reset\"></form>\n";
	echo "</body></html>\n";
?>
