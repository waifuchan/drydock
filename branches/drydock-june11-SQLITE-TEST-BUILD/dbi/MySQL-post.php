<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-post.php
	Description:    Code for the ThornPostDBI class, based upon the MySQL version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-post.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornPostDBI extends ThornDBI
{
	//This class will not be seen by Smarty, so we can neglect unsetting IPs and such.
	function ThornPostDBI()
	{
		$this->ThornDBI();
	}

	function gettinfo($t)
	{
		return ($this->myassoc("select * from " . THthreads_table . " where id=" . intval($t)));
	}

	function putthread($name, $tpass, $board, $title, $body, $link, $ip, $mod, $pin, $lock, $permasage, $password= "",$tyme = false)
	{
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		$q = "insert into " . THthreads_table . " set board='" . $board . "', title='" . $this->clean($title) . "', body='";
		if ($board == THnewsboard || $board == THmodboard) //don't filter the news board nor the mod board
		{
			$q .= $this->escape_string($body);
		}
		else
		{
			$q .= $this->clean($body);
		}
		$q .= "', ip=" . $ip . ", pin=" . $pin . ", permasage=" . $permasage . ", lawk=" . $lock . ", time=" . $tyme . ", bump=" . $tyme;
		$globalid = $this->getglobalid($board);
		$q .= ", globalid=" . $globalid;
		if ($name != null)
		{
			$q .= ", name='" . $this->clean($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip='" . $tpass . "'";
			//Not cleaning trip since it should be encoded.
		}
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			$q .= ", link='" . $this->clean($link) . "'";
		}
		
		if( $password != "") // password for post deletion
		{
			$q .= ", password='" . $this->escape_string(md5(THsecret_salt.$password)) . "'";
		}
		
		//echo($q.", time=".$tyme);
		//echo $q;
//		if ($link != null)
//		{
//			$q .= ", link='" . $this->clean($link) . "'";
//		}
		$this->myquery($q) or THdie("DBpost");
		if ($board == THnewsboard)
		{
			rebuild_rss();
		}
		smclearcache($board, -1, -1); // clear the cache for this board
		$tnum = mysql_insert_id();
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where id=" . ($board)) or THdie("DBpost");
		return ($tnum);
	}

	function putpost($name, $tpass, $link, $board, $thread, $body, $ip, $mod, $password = "", $tyme = false)
	{
		$q = "insert into " . THreplies_table . " set thread=" . $thread . ", board=" . intval($board) . ", body='";
		if ($board == THmodboard) //don't filter the mod board since it should be all locked up anyway
		{
			$q .= $this->escape_string($body);
		}
		else
		{
			$q .= $this->clean($body);
		}
		
		$bump = preg_match("/^(mailto:)?sage$/", $link); // sage check
		
		$q .= "', ip=" . $ip . ", bump=" . (int) $bump;
		$globalid = $this->getglobalid($board);
		$q .= ", globalid=" . $globalid;
		if ($name != null)
		{
			$q .= ", name='" . $this->clean($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip='" . $tpass . "'";
		}
		$bump = preg_match("/^(mailto:)?sage$/", $link);
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			$q .= ", link='" . $this->clean($link) . "'";
		}
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		
		if( $password != "") // password
		{
			$q .= ", password='" . $this->escape_string(md5(THsecret_salt.$password)) . "'";
		}
		//echo($q);
		$this->myquery($q . ", time=" . $tyme) or THdie("DBpost");
		//if ($board == THnewsboard) { buildnews(); }	
		$pnum = mysql_insert_id();
		if (!$bump)
		{
			$this->myquery("update " . THthreads_table . " set bump=" . $tyme . " where id=" . $thread . " and permasage = 0");
		}
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where id=" . $board) or THdie("DBpost");
		smclearcache($board, -1, -1); // clear cache for the board
		smclearcache($board, -1, $thread); // and for the thread
		return ($pnum);
	}

	function putimgs($num, $isthread, $files)
	{

		$id = $this->myresult("select max(id) from " . THimages_table) + 1;
		foreach ($files as $file)
		{
			$values[] = "(" . $id . ",'" . $file['hash'] . "','" . $this->clean($file['name']) . "'," . $file['width'] . "," . $file['height'] . ",'" . $this->clean($file['tname']) . "'," . $file['twidth'] . "," . $file['theight'] . "," . $file['fsize'] . "," . (int) $file['anim'] . "," . (int) $file['extra_info'] . ")";
			/*
				fputs($fp,"id:\t");
				fputs($fp,$id);
				fputs($fp,"\n"); 
				fputs($fp,"file[hash]:\t");
				fputs($fp,$file['hash']);
				fputs($fp,"\n"); 
				fputs($fp,"file[name]:\t");
				fputs($fp,$file['name']);
				fputs($fp,"\n"); 
				fputs($fp,"file[width]:\t");
				fputs($fp,$file['width']);
				fputs($fp,"\n"); 
				fputs($fp,"file[height]:\t");
				fputs($fp,$file['height']);
				fputs($fp,"\n"); 
				fputs($fp,"file[tname]:\t");
				fputs($fp,$file['tname']);
				fputs($fp,"\n"); 
				fputs($fp,"file[twidth]:\t");
				fputs($fp,$file['twidth']);
				fputs($fp,"\n"); 
				fputs($fp,"file[theight]:\t");
				fputs($fp,$file['theight']);
				fputs($fp,"\n"); 
				fputs($fp,"file[fsize]:\t");
				fputs($fp,$file['fsize']);
				fputs($fp,"\n"); 
				fputs($fp,"file[anim]:\t");
				fputs($fp,$file['anim']);
				fputs($fp,"\n"); 
			*/
		}
		$this->myquery("insert into " . THimages_table . " values " . implode(",", $values));
		if ($isthread)
		{
			$this->myquery("update " . THthreads_table . " set imgidx=" . $id . " where id=" . $num);
		}
		else
		{
			$this->myquery("update " . THreplies_table . " set imgidx=" . $id . " where id=" . $num);
		}

		return ($id);
	}

	function purge($boardid)
	{
		$board = $this->getbinfo($boardid);
		if ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $board['id'] . " && pin=0") > $board['tmax'])
		{
			$last = $this->myassoc("select bump from " . THthreads_table . " where board=" . $board['id'] . " && pin=0 order by bump desc limit " . ((int) $board['tmax'] - 1) . ",1"); //-1 'cuz it's zero-based er' somethin'
			//var_dump($last);
			$dels = $this->myquery("select * from " . THthreads_table . " where board=" . $board['id'] . " && bump<" . $last['bump'] . " && pin=0");
			$badimgs = array ();
			$badths = array ();
			while ($del = mysql_fetch_assoc($dels))
			{
				//var_dump($del);
				if ($del['imgidx'] != 0)
				{
					$badimgs[] = $del['imgidx'];
				}
				$badths[] = $del['id'];
				smclearcache($board['id'], -1, $del['id']); // clear the associated cache for this thread
			}
			$this->myquery("delete from " . THthreads_table . " where bump<" . $last['bump'] . " && pin=0");
			$badthstr = implode(",", $badths);
			$dels = $this->myquery("select imgidx from " . THreplies_table . " where board=" . $board['id'] . " && thread in (" . $badthstr . ") && imgidx!=0");
			while ($del = mysql_fetch_row($dels))
			{
				$badimgs[] = $del;
			}
			$this->myquery("delete from " . THreplies_table . " where thread in (" . $badthstr . ")");
			return ($badimgs);
		}
		else
		{
			return (array ());
		}
	}

	function dupecheck($hashes)
	{
		if (count($hashes) > 0)
		{
			return ($this->myresult("select count(*) from " . THimages_table . " where hash in ('" . implode("','", $hashes) . "')"));
		}
		else
		{
			return (0);
		}
	}
	
	function getglobalid($board)
	{
		$sql = "select globalid from " . THboards_table . " where folder='" . $this->escape_string($board)."'";
		$globalid = $this->myresult($sql);
		
		if( $globalid == null )
		{
			return null;
		}
		
		$globalid++;
		$newsql = "update " . THboards_table . " set globalid=" . $globalid . " where folder='" . $this->escape_string($board) . "'";
		$this->myquery($newsql);
		return ($globalid);
	}
	
	function getpostlocation($threadid, $postid = -1)
	{
		$location = array();
		
		if ( $postid > -1 ) // Retrieving information for a reply
		{
			$location['post_loc'] = $this->myresult("select globalid from ".THreplies_table." where id=".intval($postid));
			$location['thread_loc'] = $this->myresult("select globalid from ".THthreads_table." where id=".intval($threadid));
		}
		else // For a thread
		{
			$location['thread_loc'] = $this->myresult("select globalid from ".THthreads_table." where id=".intval($threadid));
		}
		
		return $location;
	}
	
	function getsinglepost($id, $board)
	{
		$postassoc = array();
		
		// Try replies first
		
		$qstring = "SELECT * FROM " . THreplies_table . " WHERE globalid=" . intval($id) . 
						" AND board=" . intval($board);
		$postassoc = $this->myassoc($qstring);

		if ($postassoc == null)
		{
			$qstring = "SELECT * FROM " . THthreads_table . " WHERE globalid=" . intval($id) . 
						" AND board=" . intval($board);
			$postassoc = $this->myassoc($qstring);
		}
	
		return $postassoc;
	}
	
	function movethread($id, $newboard)
	{
		// Get the new board name
		$newboard = intval($newboard); // Save a bit of time
		
		$destboard_name = $this->getboardname($newboard);
		$newthreadspot = $this->getglobalid($destboard_name);
		
		if( $newthreadspot == null )
			return null;
		
		$this->myquery("update " . THthreads_table . " set globalid=" . $newthreadspot . ", " .
				"board=" . intval($newboard) . " where id=" . intval($id));
	
		// Get an array of reply IDs to move
		$posts = array();
		$posts = $this->myarray("select id from " . THreplies_table . " where thread=" . intval($id) . 
									" order by globalid asc");
	
		foreach( $posts as $reply )
		{
			$db->myquery("update " . THreplies_table . " set globalid=" . $db->getglobalid($newboard) . 
							",board=" . $newboard . " where id=" . intval($reply));
		}
		
		return $newthreadspot;
	}
	
	function updatepost($id, $board, $name, $trip, $link, $subject, $body, $visible, $pin, $lock, $permasage)
	{
		$isreply = $this->myresult("SELECT COUNT(*) FROM " . THreplies_table . 
			" WHERE globalid=".intval($id)." AND board=".intval($board));
			
		if( isreply )
		{
			$querystring = "UPDATE " . THreplies_table . " SET ".
					"name='".$this->escape_string($name)."'," .
					"trip='".$this->escape_string($trip)."'," .
					"title='".$this->escape_string($subject)."'," .
					"link='".$this->escape_string($link)."'," .
					"body='".$this->escape_string($body)."'," .
					"visible=".intval($visible)."," .
					"unvisibletime=".time() + (THtimeoffset * 60).
					" WHERE globalid=".intval($id).
					" AND board=".intval($board);
		}
		else
		{
			$querystring = "UPDATE " . THreplies_table . " SET ".
					"name='".$this->escape_string($name)."'," .
					"trip='".$this->escape_string($trip)."'," .
					"title='".$this->escape_string($subject)."'," .
					"link='".$this->escape_string($link)."'," .
					"body='".$this->escape_string($body)."'," .
					"visible=".intval($visible)."," .
					"pin=".intval($pin)."," .
					"lawk=".intval($lock)."," .
					"permasage=".intval($permasage)."," .
					"unvisibletime=".time() + (THtimeoffset * 60).
					" WHERE globalid=".intval($id).
					" AND board=".intval($board);			
		}
		
		$this->myquery($querystring);
	}
	
	function deleteimage($imgidx, $hash, $extra_info = -1)
	{
		if ($extra_info > 0) // delete any associated extra_info
		{
			$this->myquery("delete from " . THextrainfo_table . " where id=" . intval($extra_info)); 
		}

		$this->myquery("delete from " . THimages_table . " where id=" . intval($imgidx) . 
			" and hash='" . $this->escape_string($hash) . "'");
	}
	
} //ThornPostDBI
?>
