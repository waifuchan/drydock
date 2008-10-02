<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-post.php
	Description:    Code for the ThornPostDBI class, based upon the MySQL version of ThornDBI
	
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
		/*
			Basically, just gets the thread head. Getting images are not necessary.
			Parameters:
				int $t
			The thread to fetch.
				Returns: array $thread
		*/
		return ($this->myassoc("select * from " . THthreads_table . " where id=" . $this->clean($t)));
	}

	function getbinfo($b)
	{
		/*
			Another simple function. This just gets information about a board.
			Parameters:
				int $b
			The board to fetch.
				returns: array $board
		*/
		return ($this->myassoc("select * from " . THboards_table . " where id=" . $b));
	}

	function putthread($name, $tpass, $board, $title, $body, $link, $ip, $mod, $pin, $lock, $permasage, $tyme = false)
	{
		/*
			Posts a new thread, and updates the respective board's last post time. Note that the storing of image information is done in a separate function, putimgs().
			Parameters:
				string $name
			The poster's name.
				string $tpass
			The poster's encoded tripcode.
				int $board
			The board this thread will go into.
				string $title
			The title of this new thread.
				string $body
			The body text of this new thread.
				string $link
			The link field of this new thread
				int $ip
			The ip2long()'d IP of the poster.
				bool $mod
			Is the poster a mod or admin? (For future feature; currently ignored by this DBI as well as the included templates.)
				bool $pin
			Should the thread be pinned? (Since MySQL doesn't support booleans, this is stored as a 0 or 1 in an integer column.)
				bool $lock
			Should the thread be locked? (Ditto)
				int $tyme
			Time of post (now if set to false)
				Returns: int $thread-id
		*/
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		$q = "insert into " . THthreads_table . " set board=" . $board . ", title='" . $this->clean($title) . "', body='";
		if ($board == THnewsboard || $board == THmodboard) //don't filter the news board nor the mod board
		{
			$q .= escape_string($body);
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
		//echo($q.", time=".$tyme);
		//echo $q;
		if ($link != null)
		{
			$q .= ", link='" . $this->clean($link) . "'";
		}
		$this->myquery($q) or THdie("DBpost");
		if ($board == THnewsboard)
		{
			rebuild_rss();
		}
		smclearcache($board, -1, -1); // clear the cache for this board
		$tnum = mysql_insert_id();
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where id=" . $board) or THdie("DBpost");
		return ($tnum);
	}

	function putpost($name, $tpass, $link, $board, $thread, $body, $ip, $mod, $bump, $tyme = false)
	{
		/*
			Posts a reply to a thread, updates the "bump" column of the relevant thread, and updates the last post time of the relevant board. Note that, as with putthread, images are stored using the separate putimgs() function.
			Parameters:
				string $name
			The poster's name.
				string $tpass
			The poster's encoded tripcode.
				string $link
			The poster's link (could be mailto, could be something similar, who knows!)
				int $board
			The board to which this post's thread belongs.
				int $thread
			The thread for which this post is a reply.
				string $body
			This post's body text.
				int $ip
			The ip2long()'d IP address of the poster.
				bool $mod
			Is the poster a mod or admin? (For future feature; currently ignored by this DBI as well as the included templates.)
				bool $bump
			Should we bump the thread?
				Returns: int $post-id
		*/
		$q = "insert into " . THreplies_table . " set thread=" . $thread . ", board=" . $board . ", body='";
		if ($board == THmodboard) //don't filter the mod board since it should be all locked up anyway
		{
			$q .= escape_string($body);
		}
		else
		{
			$q .= $this->clean($body);
		}
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
		/*
		Puts image information into the database, then updates the relevant thread or post with the image data's image index.
		Parameters:
			int $num
		The ID number of the post or thread to which we are putting images.
			bool $isthread
		Is $num referring to a post or a thread?
			array $files
		An array containing information about the images we're uploading. The parameters are:
				string $file['hash']
			The sha1 hash of the image file.
				string $file['name']
			The image's filename.
				int $file['width']
			The width in pixels of the image.
				int $file['height']
			Take a wild guess...
				string $file['tname']
			The name of the image's thumbnail.
				int $file['twidth']
			The thumbnail's width in pixels.
				int $file['theight']
			Whatever this is, it is absolutely NOT the height of the thumbnail in pixels. That's just what they WANT you to think...
				int $file['fsize']
			The image's filesize in K, rounded up.
				bool $file['anim']
			Is the image animated?
			Returns: int $image-index
		*/

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
		/*
		Purges a board after a new thread is posted. It would be nice if we could include this with the putthread() function, 
		but both these functions need to return very important and very separate things...
		Parameters:
			int $boardid
		The ID of the board we're purging.
			Returns: array $images-from-deleted-threads (to be deleted from the disk by Thorn)
		*/
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
		/*
		A simple function to check to see if any of the sha1 hashes in $hashes are already present. 
		Parameters:
			array $hashes
		A one-dimensional string array of hashes.
			Returns: int $num-found-hashes (For other DBIs, returning just true or false should suffice -- the count really isn't important.)
		*/
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
		/*
			This gets the global post id for passing to other functions
			Pulls overall global.
			
			Gives us individual board numbering and overall numbering instead of counts for both
			threads and replies.
		*/
		$sql = "select globalid from " . THboards_table . " where id=" . $board;
		$this->myresult($sql, 0, "globalid");
		$globalid++;
		$newsql = "update " . THboards_table . " set globalid=" . $globalid . " where id=" . $board;
		$this->myquery($newsql);
		return ($globalid);
	}
} //ThornPostDBI
?>
