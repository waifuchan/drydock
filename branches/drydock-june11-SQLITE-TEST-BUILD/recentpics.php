<?php

/* 	ThornLight v0.04a 	by Ordog163 <ordog@573chan.org>    
					Alterations by tyam <tyam@meilk.com>  :]
	NO WARRANTY LOL
	
	(02:27:04 AM) wildtyam: LIKE I'VE SAID BEFORE THOSE TWO FILES ARE YOUR BABIES
*/

require_once("config.php");
require_once("common.php");
if(!$_SESSION['admin'] && !$_SESSION['moderator']) { THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in."); } else {
$db=new ThornDBI();
	
// SELECT COUNT(*) FROM 'img'
$count = $db->myresult("SELECT COUNT(*) FROM ".THimages_table);

$offset = 0;

if(isset($_GET['offset'])) 
{
	$offset = intval($_GET['offset']);
	
	if( $offset < 0 ) 
	{
		$offset = 0;
	}
}
	
$beginning = $count - 40 - $offset;

if( $beginning < 0 )
{
	$beginning = 0;
}
//Beginning should never be greater than $count, for the reason that $offset is always >= 0
	
	$imagequery = "SELECT * FROM ".THimages_table." ORDER BY id ASC LIMIT $beginning , 40";
	
	$imgs=array();
	$queryresult=$db->myquery($imagequery);
	if($queryresult!=0)  //Did we return anything at all?
	{
		while ($img=$db->myarray($queryresult))
		{
        	$imgs[]=$img;
		}
		foreach ($imgs as $key => $row)
		{
			$id[$key]  = $row['id'];
		}
		//if our other catch doesn't work (which half the time it doesnt) then let's catch it here.  sloppy :[  ~tyam
		@array_multisort($id, SORT_DESC, $imgs) or $bail=1;  
		$row =0;
		$column =0;
		$nomoreimages = 0;
	} 
	else 
	{
		$bail = 1; //this gives us a "no records found" message in the right place if it catches
	}//if empty return
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<script type="text/javascript" src="<?php echo THurl; ?>js.js"></script>
<title><?php echo THname;?> &#8212; Administration &#8212; Recent Pics</title>
</head>
<body>	
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Recent Pics
        </div>
	<br />
<?php
if($bail==1) 
{ 
	echo "No images on record."; 
} 
else 
{
	// Let's get these cached so we don't have to do a getboardname call for every post.
	$boardquery = "SELECT * FROM ".THboards_table;
	$boards=array();
	$queryresult=$db->myquery($boardquery);
	if($queryresult!=0)  //Did we return anything at all?
	{
		while ($boarditem=$db->myarray($queryresult))
		{
        	$boards[$boarditem['id']]=$boarditem;
		}
	}

	if($count > 40)
	{
			echo '<table width=100%><tr>';
			if($offset > 0)
			{
				$offsetback = $offset - 40;
				if($offsetback < 0)
				{
					$offsetback = 0;
				}
				echo '<td align=left width=50%><a href="recentpics.php?offset='.$offsetback.'">&lt;&lt;</a></td>';
			} 
			else 
			{
				echo '<td align=left width=50%>&lt;&lt;</td>';
			}

			if($beginning > 0)
			{
				$offsetfwd = $offset += 40;
				echo '<td align=right width=50%><a href="recentpics.php?offset='.$offsetfwd.'">&gt;&gt;</a></td>';
			} 
			else 
			{
				echo '<td align=right width=50%>&gt;&gt;</td>';
			}
			echo '</tr></table>';
	}

	echo '<div align="center"><table BORDER="0" CELLPADDING="5" WIDTH=90%>';
	while($nomoreimages == 0 && $row < 8)
	{
		echo '<tr>';
		for($column=0;$column<4;$column++)
		{
			if( $nomoreimages == 1) 
			{
				break;
			}
			
			echo '<td>';
			$thisimage = $imgs[($row*4)+$column];
			
			if( $thisimage == null ) 
			{
				$nomoreimages = 1;
			}
			else 
			{
				$threadquery = $db->myresult("SELECT id FROM ".THthreads_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");
				
				if( $threadquery == NULL )
				{
					$postquery = $db->myresult("SELECT thread FROM ".THreplies_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");

					if( $postquery == NULL )
					{
						$thread=0;
					}
					else
					{
						$thread=$postquery;
					}
				}
				else
				{
				$thread=$threadquery;
				}

				$threadquery = $db->myresult("SELECT board FROM ".THthreads_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");
				if( $threadquery == NULL )
				{
					$postquery = $db->myresult("SELECT board FROM ".THreplies_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");

					if( $postquery == NULL )
					{
						$board=0;
					}
					else
					{
						$board=$postquery;
					}
				}
				else
				{
					$board=$threadquery;
				}
				
				echo '<a class=info href="images/'.$thisimage['id'].'/'.$thisimage['name'].'">';
				if($thisimage['hash'] != "deleted")
				{
					echo '<img src="images/'.$thisimage['id'].'/'.$thisimage['tname'].'" border=0>';
				}
				else
				{
					echo '<img src="'.THurl.'static/file_deleted.png" alt="File Deleted" border=0 />';			
				}

				/*if($thisimage['extra_info'] > 0)
				{
					$extrainfo = $db->myresult("SELECT ".THextrainfo_table." FROM extra_info WHERE id=".$thisimage['extra_info']);
					
					if($extrainfo)
					{
					echo '<span>';
					echo $extrainfo;
					echo '</a></span><BR>';
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
				
				if ($thread != 0)
				{
					if( $board > 0 )
					{
						$boardz = $boards[$board]['folder'];
						
						if($boardz != false)
						{
							echo '/'.$boardz.'/ ';
						}
						
						$globalid = $db->myresult("SELECT globalid FROM ".THthreads_table." WHERE id=".$thread);
						
						if(THuserewrite)
						{
							echo '[<a href="'.THurl.$boardz.'/thread/'.$globalid.'">thread</a>]';
						}
						else
						{
							echo '[<a href="'.THurl.'drydock.php?b='.$boardz.'&i='.$globalid.'">thread</a>]';
						}
					}
					else // welp, board is =<0, something's up
					{
						echo 'boardless?';
					}

				} 
				else 
				{
					echo '[thread]';
				}// thread not 0


				echo '<br />';
				echo "(<i>".$thisimage['fsize']." K, ".$thisimage['width']."x".$thisimage['height']."</i>)";
				if( $thisimage['anim'] > 0 ) { echo " (<i>A</i>)"; } // echo if animated/
				echo "<br />";
			}
			echo '</td>';
		}
		echo '</tr>';
		$row++;
	}
	echo '</table></div>';
	
	$offset = 0;

	if(isset($_GET['offset'])) 
	{
		$offset = intval($_GET['offset']);

		if( $offset < 0 ) 
		{
			$offset = 0;
		}
	}

	$beginning = $count - 40 - $offset;

	if( $beginning < 0 )
	{
		$beginning = 0;
	}
		
	if($count > 40)
	{
		echo '<table width=100%><tr>';
		if($offset > 0)
		{
			if($offsetback < 0)
			{
				$offsetback = 0;
			}
			echo '<td align=left width=50%><a href="recentpics.php?offset='.$offsetback.'">&lt;&lt;</a></td>';
		} 
		else 
		{
			echo '<td align=left width=50%>&lt;&lt;</td>';
		}

		if($beginning > 0)
		{
			echo '<td align=right width=50%><a href="recentpics.php?offset='.$offsetfwd.'">&gt;&gt;</a></td>';
		} 
		else 
		{
			echo '<td align=right width=50%>&gt;&gt;</td>';  
		}
		echo '</tr></table>';
	}
}
?>		
</div>
</div>
<?php include("menu.php"); ?>
</body>
</html>
<?php } ?>
