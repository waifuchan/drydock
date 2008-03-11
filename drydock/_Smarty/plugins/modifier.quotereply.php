<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			modifier.quotereply.php
		Description:	Provides >>1 quote reply features


		step 1:	parse the incoming text (expected format /&gt;&gt;\040?([0-9]{1,6})/)
				split the >> from the [numbers] (this is done with the subexpression in the regexp)
		step 2:	look up that number in posts&board.
				if it exists, look up it's thread
					form link
				if it doesnt exist, look it up in thread table
					if that exists, link it
				if neither of those are true, it's a nonexistant >>quotereply
		step 3:	link formation:
					if op drydock/board/thread/[numbers]
					if reply drydock/board/thread/op#[numbers]
	
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

function lookup_qr_link($bid, $bfold, $threadid, $threadglob, $pulledvalue)
{

	$string ="SELECT COUNT(*) FROM ".THreplies_table." where thread=".$threadid." and globalid=".$pulledvalue." and board=".$bid;
	$query = mysql_query($string);
	$count = mysql_result($query,0);	

	if ($count != 0)
	{
		//this is a reply, let's link to the reply		
		if(THuserewrite)
		{
			$link = "<a href=\"".THurl.$bfold."/thread/".$threadglob."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
		}
		else
		{
			$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&i='.$threadglob."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
		}
	}
	else if ($pulledvalue == $threadglob)
	{
		//assume we found the OP and don't link a post.  this also provides if somehow something glitches we still link to the thread OP	
		if(THuserewrite)
		{	
			$link = "<a href=\"".THurl.$bfold."/thread/".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
		}
		else
		{
			$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&i='.$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
		}
	}
	else
	{
		//now we check other threads
		//mysql_result("SELECT thread FROM posts where thread=".$grabid." and board=".$bid,0);
		$newstring ="SELECT COUNT(*) FROM ".THthreads_table." where globalid=".$pulledvalue." and board=".$bid;
		$newquery = mysql_query($newstring);
		$newcount = mysql_result($newquery,0);
		if ($newcount != 0)
		{
			//grab an OP that isn't us
			$shit = "SELECT id FROM ".THthreads_table." where globalid=".$pulledvalue." and board=".$bid;
			$grabid = mysql_result(mysql_query($shit),0);
			$fuck = "SELECT globalid FROM ".THthreads_table." where id=".$grabid." and board=".$bid;
			$threadop = mysql_result(mysql_query($fuck),0);
			
			if(THuserewrite)
			{
				$link = "<a href=\"".THurl.$bfold."/thread/".$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
			}
			else
			{
				$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&i='.$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
			}
		}
		else
		{
			//it's a reply in an op that isnt us maybe?
			$newerstring ="SELECT COUNT(*) FROM ".THreplies_table." where globalid=".$pulledvalue." and board=".$bid;
			$newerquery = mysql_query($newerstring);
			$newercount = mysql_result($newerquery,0);
			if ($newercount != 0)
			{
				$shit = "SELECT thread FROM ".THreplies_table." where globalid=".$pulledvalue." and board=".$bid;
				$grabid = mysql_result(mysql_query($shit),0);
				$fuck = "SELECT globalid FROM ".THthreads_table." where id=".$grabid." and board=".$bid;
				$threadop = mysql_result(mysql_query($fuck),0);				
				
				if(THuserewrite)
				{
					$link = "<a href=\"".THurl.$bfold."/thread/".$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
				}
				else
				{
					$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&i='.$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
				}
			}
			else
			{	
				//we failed to find any match  :[
				$link = "&gt;&gt;".$pulledvalue;
			}
		}
	}
	
	return $link;
}

function smarty_modifier_quotereply($text, $binfo, $post, $thread)
{
	$search = '/&gt;&gt;\040?([0-9]{1,6})/e';
	return preg_replace($search, "lookup_qr_link( ".$binfo['id'].",".$binfo['folder']." ,".$thread['id'].",".$thread['globalid'].",'\\1')", $text); // FUCK YOU PHP
	
	//ONE MORE TIME FOR THE LADIES: FUCK PHP
}

?>

