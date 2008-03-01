<?php

/* 	ThornQuasiLight v0.05a 	by Ordog163 <ordog@573chan.org>
						Alterations by tyam <tyam@meilk.com>  :]
	NO WARRANTY LOL
	
	(02:27:04 AM) wildtyam: LIKE I'VE SAID BEFORE THOSE TWO FILES ARE YOUR BABIES
*/

	require_once("config.php");
	require_once("common.php");

	if(!$_SESSION['admin'] && !$_SESSION['moderator']) 
	{ 
	THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in."); 
	} 
	else 
	{
	$db=new ThornDBI();	
	function comp_post_ids($a, $b)
	{
		$first=$a['id'];
		$second=$b['id'];
		if ($first == $second) 
		{ //This should never happen, but whatever.
			return 0;
		}

		return ($first < $second) ? 1 : -1;
	}

	// SELECT COUNT(*) FROM 'img'
	
	$board = mysql_real_escape_string($_GET['board']); //clean the board name from get
	
	// We just append this to the end of all the SQL queries/links.  Makes things simpler because we only have to do it once.
	if(isset($_GET['board']) && getboardnumber($_GET['board']) )
	{
		$boardquery = " WHERE board=".getboardnumber($_GET['board']);
		$boardlink = "&board=".$board;
	}
	else 
	{
		$boardquery = "";
		$boardlink = "";
	}
	
	if(isset($_GET['showhidden']) && $_GET['showhidden'] == true)
	{
		if($boardquery != "")
			$boardquery .= " and visible=0";
		else
			$boardquery = " WHERE visible=0";
		if($_GET['type'] == "posts")
		{
			$boardlink .= "&showhidden=1"; // this means we'll append show hidden on the end of offset links
		} else {
			$boardlink .= "?showhidden=1";
		}
	}
/*
	else //it's 4am and i have a wedding to go to and a midterm, i shouldn't be doing else blocks right now :[
	{
		if($boardquery != "")
			$boardquery .= " and visible=1";
		else
			$boardquery = " WHERE visible=1";
	}
*/

	//print "Posts Count:".$count;
	if($_GET['type'] == "posts")
	{
		$count = $db->myresult("SELECT COUNT(*) FROM ".THreplies_table.$boardquery);
	}
	else
	{
		$count = $db->myresult("SELECT COUNT(*) FROM ".THthreads_table.$boardquery);
	}
	//print "Total Count:".$count;
	$offset = 0;
	
	if(isset($_GET['offset']))
	{
		$offset = intval($_GET['offset']);
		if( $offset < 0 )
		{
			$offset = 0;
		}//if offset <0
	}//if offset

	$beginning = $count - 19 - $offset;

	if( $beginning < 0 )
	{
		$beginning = 0;
	}//if beg < 0
	//Beginning should never be greater than $count, for the reason that $offset is always >= 0

	if($_GET['type'] == "posts")
	{
		$postquery = "SELECT * FROM ".THreplies_table.$boardquery." order by id asc LIMIT ".$beginning.", 20";
	}
	else
	{
		$postquery = "SELECT * FROM ".THthreads_table.$boardquery." order by id asc LIMIT ".$beginning.", 20";
	}
	
	//echo $postquery;
	//$postquery = "(SELECT * FROM posts ORDER BY id DESC) UNION ALL (SELECT * FROM threads ORDER BY id DESC) LIMIT ".$beginning.", 20";

	$posts=array();
	$queryresult=$db->myquery($postquery);
	
	if( $queryresult==false ) 
	{
	echo "<font size=3 color=\"#FF0000\"><b>Error ".mysql_errno().": ".mysql_error()."</b><br />$postquery</font><br>\n";
	}

	while ($post=mysql_fetch_assoc($queryresult))
	{
		$posts[]=$post;
	}//while posts

/*	foreach ($posts as $key => $row)
	{
		$id[$key]  = $row['id'];
	}//foreach posts
	array_multisort($id, SORT_DESC, $posts); */

	usort($posts, 'comp_post_ids'); // THIS SHOULD WORK?

	$row =0;
	$column =0;
	$nomoreposts = 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<script type="text/javascript" src="<?php echo THurl; ?>js.js"></script>
<title><?php echo THname;?> &#8212; Administration &#8212; Recent Posts</title>
</head>
<body>	
<div id="main">
    <div class="box">
		<div class="pgtitle">
			Recent Posts
<?php
	if($board != "")
	{
		echo "&#8212; showing only posts in /".$board."/\n";
	}
	echo "</div>\n<br />\n";
	
	// Let's get these cached so we don't have to do a getboardname call for every post.
	$boardquery = "SELECT * FROM ".THboards_table;
	$boards=array();
	$queryresult=$db->myquery($boardquery);
	if($queryresult!=0)  //Did we return anything at all?
	{
		while ($boarditem=mysql_fetch_assoc($queryresult))
		{
        	$boards[$boarditem['id']]=$boarditem;
		}
	}
	
	if($count > 20)
	{
		echo '<table width=100%><tr>';
		if($offset > 0)
		{
			$offsetback = $offset - 20;
			if($offsetback < 0)
			{
				$offsetback = 0;
			}
			
			if($_GET['type'] == "posts")
			{
			echo '<td align=left width=50%><a href="recentposts.php?type=posts&offset='.$offsetback.$boardlink.'">&lt;&lt;</a></td>';
			}
			else
			{
			echo '<td align=left width=50%><a href="recentposts.php?offset='.$offsetback.$boardlink.'">&lt;&lt;</a></td>';
			}
		} 
		else 
		{
			echo '<td align=left width=50%>&lt;&lt;</td>';
		}//if offset >0
	
		if($beginning > 0)
		{
			$offsetfwd = $offset += 20;
			if($_GET['type'] == "posts")
			{
			echo '<td align=right width=50%><a href="recentposts.php?type=posts&offset='.$offsetfwd.$boardlink.'">&gt;&gt;</a></td>';
			}
			else
			{
			echo '<td align=right width=50%><a href="recentposts.php?offset='.$offsetfwd.$boardlink.'">&gt;&gt;</a></td>';
			}
		} 
		else 
		{
			echo '<td align=right width=50%>&gt;&gt;</td>';
		}//if beginning > 0
		echo '</tr></table><hr>';
	}//if count > 20
	echo '<div align="center">';
	
	$boardsquery = "SELECT id,folder FROM ".THboards_table." order by id desc";	
	$bqueryresult=$db->myquery($boardsquery);
	
	if(isset($_GET['showhidden']) && $_GET['showhidden'] == true)
	{
		if($_GET['type'] == "posts")
		{
			echo '- Pull posts + <a href="recentposts.php?showhidden=1">Pull threads</a> + <a href="recentposts.php?type=posts">Show all posts</a> -';
			echo ' Filter by board: ';
			echo "<select name=\"board\">\n";
			echo "<option value=\"0\" onclick=\"window.location='recentposts.php?type=posts&showhidden=1'\">All boards</option>";
			while ($board=mysql_fetch_assoc($bqueryresult))
			{
				echo "<option value=\"".$board[folder].
				"\"  onclick=\"window.location='recentposts.php?type=posts&showhidden=1&board=".$board[folder]."'\">/".
				$board['folder']."/</option>\n";
			}	
		}
		else
		{
			echo '- <a href="recentposts.php?type=posts&showhidden=1">Pull posts</a> + Pull threads + <a href="recentposts.php">Show all posts</a> -';
			echo ' Filter by board: ';
			echo "<select name=\"board\">\n";
			echo "<option value=\"0\" onclick=\"window.location='recentposts.php?showhidden=1'\">All boards</option>";
			while ($board=mysql_fetch_assoc($bqueryresult))
			{
				echo "<option value=\"".$board[folder].
				"\"  onclick=\"window.location='recentposts.php?showhidden=1&board=".$board[folder]."'\">/".
				$board['folder']."/</option>\n";
			}	
		}

	}
	else
	{
		if($_GET['type'] == "posts")
		{
			echo '- Pull posts + <a href="recentposts.php">Pull threads</a> + <a href="recentposts.php?type=posts&showhidden=1">Show only hidden</a> -';
			echo ' Filter by board: ';
			echo "<select name=\"board\">\n";
			echo "<option value=\"0\" onclick=\"window.location='recentposts.php?type=posts'\">All boards</option>";
			while ($board=mysql_fetch_assoc($bqueryresult))
			{
				echo "<option value=\"".$board[folder].
				"\"  onclick=\"window.location='recentposts.php?type=posts&board=".$board[folder]."'\">/".
				$board['folder']."/</option>\n";
			}	
		}
		else
		{
			echo '- <a href="recentposts.php?type=posts">Pull posts</a> + Pull threads + <a href="recentposts.php?showhidden=1">Show only hidden</a> -';
			echo ' Filter by board: ';
			echo "<select name=\"board\">\n";
			echo "<option value=\"0\" onclick=\"window.location='recentposts.php'\">All boards</option>";
			while ($board=mysql_fetch_assoc($bqueryresult))
			{
				echo "<option value=\"".$board[folder].
				"\"  onclick=\"window.location='recentposts.php?board=".$board[folder]."'\">/".
				$board['folder']."/</option>\n";
			}	
		}
	}
	echo "</select>\n";
	
	echo '<hr>';
	
	while($nomoreposts == 0)
	{
		echo '<table BORDER="0" CELLPADDING="5" WIDTH=90%><tr><td>';
		$thispost = $posts[$row];
		if( $thispost == null )
		{
			$nomoreposts = 1;
		} 
		else 
		{
			//echo "ID: ".$thispost['id']."<br>";
			//fix thread=0 bug - tyam
			if ($thispost['thread'] != 0) 
			{
				//$boardz = getboardname($thispost['board']);
				$boardz = $boards[$thispost['board']]['folder'];

				if( $boardz != false ) 
				{					
					$thread = $db->myresult("SELECT globalid FROM ".THthreads_table." WHERE id=".$thispost['thread']." and board=".
											$thispost['board']);
					//echo '[<a href='.THurl.$boardz.'/thread/'.$globalid.'>thread</a>]';
					
					echo 'Post '.$thispost['globalid'].' in thread '.$thread.' on /'.$boardz.'/';
					if(THuserewrite)
					{
						echo '[<a href="'.THurl.$boardz.'/thread/'.$thread.'#'.$thispost['globalid'].'">thread</a>]';
					}
					else
					{
						echo '[<a href="'.THurl.'drydock.php?b='.$boardz.'&i='.$thread.'#'.$thispost['globalid'].'">thread</a>]';
					}
				} 
				else 
				{
					echo 'Post '.$thispost['globalid'].' in [<a href='.THurl.'drydock.php?t='.$thispost['thread'].'>thread</a>]
					[<a href='.THurl.'editpost.php?post='.$thispost['globalid'].'&board='.$boardz.'>edit</a>]';
				}//board not 0
			} 
			else 
			{
				//$boardz = getboardname($thispost['board']);
				$boardz = $boards[$thispost['board']]['folder'];
				// hopefully this will fix the problem with new threads not being displayed correctly
				if( $thispost['id'] != 0 ) 
				{
					echo 'Post '.$thispost['globalid'].' in /'.$boardz.'/';
					if(THuserewrite)
					{
						echo '[<a href='.THurl.$boardz.'/thread/'.$thispost['globalid'].'>thread</a>]';
					}
					else
					{
						echo '[<a href="'.THurl.'drydock.php?b='.$boardz.'&i='.$thispost['globalid'].'">thread</a>]';
					}
					
					//[<a href='.THurl.'editpost.php?post='.$thispost['globalid'].'&board='.$thispost['board'].'>edit</a>]';
					echo '[<a href='.THurl;
					if(THuserewrite)
					{
						echo $boardz.'/edit/';
					} 
					else 
					{
						echo 'editpost.php?board='.$boardz.'&post=';
					}
					echo $thispost['globalid'].'>edit</a>]';
				}
				else
				{
				echo '[thread]';
				}
			}// thread not 0

			if( $thispost['link'] != '' )
			{
				echo '<a href="'.$thispost['link'].'">';
			}
			
			echo '<br><label><span class="postername">';
			if( $thispost['name'] != '' || $thispost['trip'] != '')
			{
				echo $thispost['name'];
			} 
			else 
			{
				echo 'Anonymous';
			}//if name and trip
			echo '</span>';
			
			if( $thispost['trip'] != '')
			{
				echo '<span class="postertrip">!';
				echo $thispost['trip'];
				echo '</span>     ';
			}//trip
			
			if( $thispost['link'] != '' )
			{
			echo '</a>';
			}
			
			echo strftime("     ".THdatetimestring, $thispost['time']);
			if( $thispost['visible'] == 0)
			{
			echo '<img src="'.THurl.'static/invisible.png" alt="INVISIBLE" border="0">';
			}
			echo '</label>';
						
			if( $thispost['title'] != '')
			{
				echo '<br><span class="filetitle">';
				echo $thispost['title'];
				echo '</span>';
			}
			
			$images=array();
			$query= "select * from ".THimages_table." where id=".$thispost['imgidx'];
			$imagesquery = $db->myquery($query);
			while ($singleimage=mysql_fetch_assoc($imagesquery))
			{
				$images[]=$singleimage;
			}//while images
			
			if( $images[0] != null )
			{
				echo '<table><tbody>';
				foreach($images as $postimage)
				{
					echo '<tr><td><div class="filesize">';
					echo 'File: <a href="images/'.$thispost['imgidx'].'/'.$postimage['name'].'" target="_blank">'.$postimage['name'].'</a><br />';
					echo '(<em>'.$postimage['fsize'].' K, '.$postimage['width'].'x'.$postimage['height'];
					if( $postimage['anim'] )
					{
						echo 'a';
					}//if anim
					echo '</em>)</div>';
					echo 'File: <a class="info" href="images/'.$thispost['imgidx'].'/'.$postimage['name'].'" target="_blank">';
					
					if($postimage['hash'] != "deleted")
					{
					echo '<img src="images/'.$thispost['imgidx'].'/'.$postimage['tname'].
					'" width="'.$postimage['twidth'].'" height="'.$postimage['theight'].'" alt="'.$postimage['name'].'" class="thumb" />';
					}
					else
					{
					echo '<img src="'.THurl.'static/file_deleted.png" alt="File deleted" width="100" height="16" class="thumb" />';
					}
					
					/* if($postimage['extra_info'] > 0)
					{
						$extrainfo = $db->myresult("SELECT ".THextrainfo_table." FROM extra_info WHERE id=".$postimage['extra_info']);
						
						if($extrainfo)
						{
						echo '<span>';
						echo $extrainfo;
						echo '</a></span>';
						}
						else
						{
						echo '</a><BR>';
						}
					}
					else
					{
					echo '</a><BR>';
					} */
					echo '</a><br />';
					
					echo '</td></tr>';
				}//for images
				echo '</tbody></table>';
			}//if img[0]
			echo '</td></tr><tr><td>';  //split images from body
			echo '<blockquote>'.nl2br($thispost['body']).'</blockquote>';
			echo '</td></tr></table><hr>';
		}//end post and i think fix our bug? ~tyam
		
		echo "\n";
		$row++;
	}//while posts
	$offset = 0;
	echo '</div>';
	if(isset($_GET['offset']))
	{
		$offset = intval($_GET['offset']);
		if( $offset < 0 )
		{
			$offset = 0;
		}//offset < 0
	}//is offset set?

	$beginning = $count - 20 - $offset;

	if( $beginning < 0 ){
		$beginning = 0;
	}//if beg < 0
	
	if($count > 20)
	{
		echo '<table width=100%><tr>';
		if($offset > 0)
		{
			$offsetback = $offset - 20;
			if($offsetback < 0)
			{
				$offsetback = 0;
			}
			
			if($_GET['type'] == "posts")
			{
			echo '<td align=left width=50%><a href="recentposts.php?type=posts&offset='.$offsetback.$boardlink.'">&lt;&lt;</a></td>';
			}
			else
			{
			echo '<td align=left width=50%><a href="recentposts.php?offset='.$offsetback.$boardlink.'">&lt;&lt;</a></td>';
			}
		} 
		else 
		{
			echo '<td align=left width=50%>&lt;&lt;</td>';
		}//if offset >0
	
		if($beginning > 0)
		{
			$offsetfwd = $offset += 20;
			if($_GET['type'] == "posts")
			{
			echo '<td align=right width=50%><a href="recentposts.php?type=posts&offset='.$offsetfwd.$boardlink.'">&gt;&gt;</a></td>';
			}
			else
			{
			echo '<td align=right width=50%><a href="recentposts.php?offset='.$offsetfwd.$boardlink.'">&gt;&gt;</a></td>';
			}
		} 
		else 
		{
			echo '<td align=right width=50%>&gt;&gt;</td>';
		}//if beginning > 0
		echo '</tr></table>';
	}//if count > 20
	
?>	
</div>
</div>
<?php include("menu.php"); ?>	
</body>
</html>
<?php } ?>
