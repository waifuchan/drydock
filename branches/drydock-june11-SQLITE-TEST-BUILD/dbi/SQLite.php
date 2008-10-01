<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:           dbi/SQLite.php
		Description:    Handles interface between database and board functions using a SQLite database
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	define("THdblitefn",sqlite_open(THpath."unlinked/drydock.sqlite", 0666, $sqliteerror));
	require_once("config.php");
	require_once("common.php");
	function escape_string($string) { return(sqlite_escape_string($string)); }

class ThornDBI {
    function ThornDBI()
	{
        if (isset($this->cxn)==false)
		{
            $this->cxn=THdblitefn or THdie($sqliteerror);
        }
    }


	/*  provided by Mell03d0ut from anonib */
    function clean($call)
	{
      $call=htmlspecialchars($call);
      if (get_magic_quotes_gpc()==0) {
        $call=sqlite_escape_string($call);
      }
      $call=trim($call); 
      return($call);
    }    

    function myassoc($call)
	{
        echo("myassoc: ".$call."<br />");
		$pup=sqlite_query(THdblitefn, $call);
        $dog=sqlite_fetch_array($pup, SQLITE_ASSOC);// or return null;
		if ($dog===false)
		{
            return(null);
        }
		return($dog);
    }
	//in mysql this is the same as above but sometimes sqlite craps itself and i don't want to work on it anymore
    function myarray($call)
	{
        echo("myarray: ".$call."<br />");
        $manta=sqlite_fetch_array($call, SQLITE_ASSOC);// or return null;
		if ($manta===false)
		{
            return(null);
        }
		return($manta);
    }

    function myresult($call)
	{	
        echo("myresult: ".$call."<br />");
		$dog = sqlite_query(THdblitefn, $call);
        if ($dog===false || sqlite_num_rows($dog)==0)
		{
            return(null);
        }
        return(sqlite_fetch_single($dog,0));
    }
    
    function myquery($call)
	{
        echo("myquery: ".$call."<br />");
        $dog=sqlite_query(THdblitefn,$call);// or die(mysql_error()."<br />".$call);
        if ($dog===false)
		{
            return(null);
        }
		return($dog);
    }
	
	function mymultiarray($call)
	{	
		/*
		Encapsulate executing a query and iteratively calling myarray on the result.
		
		Parameters:
			string call
		The SQL query to execute
		
		Returns:
			An array of associative arrays (can be size 0)
		*/
		
		$multi=array();
		
		$queryresult = $db->myquery($call);
		if ($queryresult!=null)
		{
			while ($entry=$db->myarray($queryresult))
			{
				$multi[]=$entry;
			}
		}
		return $multi;
	}

	function timecount($start,$end)
	{
		//Returns the number of threads between two specified times.
		if (isset($this->binfo))
		{
			return($this->myresult("select count(*) from ".THthreads_table." where board=".$this->binfo['id']." and time>=".$start." and time<=".$end));
        } else {
			return($this->myresult("select count(*) from ".THthreads_table." where time>=".$start." and time<=".$end));
        }
    }

    function gettimessince($since)
	{
		//Returns the times of all threads since $since.
        if (isset($this->binfo))
		{
            //echo "Binfo";
            //Will there be cases where this will be called without binfo being set?
            if ($since!=null)
			{
                $yay=$this->myquery("select time from ".THthreads_table." where board=".$this->binfo['id']." and time>=".$since);
            } else {
                $yay=$this->myquery("select time from ".THthreads_table." where board=".$this->binfo['id']);
            }
        } else {
            //echo "No binfo";
            if ($since!=null)
			{
                $yay=$this->myresult("select time from ".THthreads_table." where time>=".$since);
            } else {
                $yay=$this->myresult("select time from ".THthreads_table);
            }
        }
        //array($wows);
        $wows=array();
        echo "Row count: ".sqlite_num_rows($yay);
        while ($row=sqlite_current($yay))  //help
		{
            //var_dump($row);
            $wows[]=(int)$row[0];
        }
        return($wows);
    }

    function getimgs($imgidx)
	{
		/*
		Get the images associated with a certain post by its image index.
		Parameters:
			int $imgidx
		The image index to search for.
			Returns: array $images (blank array if none)
		*/
        if ($imgidx==0 || $imgidx==null)
		{
            return(array());
        }
        $imgs=array();
        $turtle=$this->myquery("select * from ".THimages_table." where id=".$this->clean($imgidx));
        while ($img=sqlite_fetch_array($turtle))  //help
		{
            $imgs[]=$img;
        }
        return($imgs);
    }
	
    function getblotter($board)
	{
		/*
		Get the latest blotter entries perhaps associated with a certain board
		Parameters:
			int $board
		The board for which the entries are being retrieved
			Returns: array $entries (blank array if none)
		*/
        $entries=array();
		$count = 0;
        $blotter=$this->myquery("select * from ".THblotter_table." ORDER BY time ASC");
        while ($entry=sqlite_fetch_array($blotter))  //help
		{
			if($entry['board'] == "0" || is_in_csl($board, $entry['board']))
			{
	            $entries[]=$entry;
				$count++;
			}
			
			if($count >= 5)
			{
				break;
			}
        }
        return($entries);
    }

    function getindex($p, &$sm)
	{
		/*
			Returns an index of the boards.
			Parameters:
				bool $p['full']=false
			If true, all board information will be fetched. If false, only the board ID, name and description ('about') are returned.
				string $p['sortmethod']="id"
			If "id", boards are sorted by ID number. If "name", boards are sorted by name. If "last", boards are sorted by last post time.
				bool $p['desc']=false
			If true, the boards are returned in descending order.
				Returns: array $boards (blank if none)
		*/
        if (isset($p['full'])==false)
		{
            $p['full']=false;
        }
        if (isset($p['sortmethod'])==false)
		{
            $p['sortmethod']="id";
        }
        if (isset($p['desc'])==false)
		{
            $p['desc']=false;
        }
        
        if ($p['full'])
		{
            $q="select * from ".THboards_table;
        } else {
            $q="select id, name, about from ".THboards_table;
        }
        
        if ($p['sortmethod']="id")
		{
            $q.=" order by id";
        }
        elseif ($p['sortmethod']="last")
		{
            $q.=" order by lasttime";
        }
        elseif ($p['sortmethod']="name")
		{
            $q.=" order by name";
        }
        
        if ($p['desc'])
		{
            $q.=" desc";
        }
        $iguana=$this->myquery($q);
        $boards=array();
        while ($board=sqlite_fetch_array($iguana))  //help
		{
            $boards[]=$board;
        }
        return($boards);
    }

    function checkban($ip=null)
	{
		/*
			Check to see if an IP is banned. Will check both the actual IP and the IP's subnet.
			Parameters:
				int $ip=ip2long($_SERVER['REMOTE_ADDR']);
			The ip2long'd IP address. If blank, it checks the user's IP address. ("function checkban($ip=ip2long($_SERVER['REMOTE_ADDR']))" makes PHP cwy.)
				Returns: bool $banned
		*/
        if ($ip==null)
		{
            $ip=ip2long($_SERVER['REMOTE_ADDR']);
        }
        //echo();
        $sub=ipsub($ip);
        //Check already banned...
        if ($this->myresult("select count(*) from ".THbans_table." where (ip=".$sub." and subnet=1) or ip=".$ip)>0)
		{
            return(true);
        } else {
            return(false);
        }
    }
	
	function getboard($id=0, $folder="")
	{
		/*
			Get board information, will optionally filter by id and/or folder
			Parameters:
				int id 
			The board ID to optionally filter by
				string folder
			The board filter to optionally filter by
			
			Returns:
				array containing board info
		*/
		
		$querystring = "select * from ".THboards_table." where ";
		$id = intval($id); // Make it explicitly an integer
		
		// No filtering at all
		if( $id == 0 and $folder == "")
		{
			$querystring = $querystring . "1";
		}
		else if( $id != 0 and $folder != "" ) // Filtering by both folder AND ID
		{
			$querystring = $querystring . "id=".$id." AND folder='".$this->clean($folder)."'";
		}
		else if( $id != 0 ) // Filtering by only ID
		{
			$querystring = $querystring . "id=".$id;
		}
		else // Filtering by only folder
		{
			$querystring = $querystring . "folder='".$this->clean($folder)."'";
		}
		
		return $db->myarray($querystring);
	}
	
	function insertBCW($type = -1, $field1="", $field2="", $field3="")
	{
		/*
			Insert either a blotter post, capcode, or wordfilter, based upon the passed type parameter
			Parameters:
				int type
			What to insert.  1 for blotter posts, 2 for capcodes, 3 for wordfilters.
				string field1, field2, field3
			Different fields to insert- the usage of which differs between types.  Look at the comments
			in the switch structure to determine what each parameter should be.
			
			Returns:
				The resulting insertion ID.
		*/
		
		$type = intval($type);
		switch( $type )
		{
			case 1: // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'INSERT INTO '.THblotter_table.' ( entry, board, time ) VALUES ("'.
					$this->clean($field1).'","'.intval($field2).'","'.(THtimeoffset*60) + time().'")';
			break;
			
			case 2: // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO '.THcapcodes_table.' ( capcodefrom, capcodeto, notes ) VALUES ("'.
					$this->clean($field1).'","'.$this->clean($field2).'","'.$this->clean($field3).'");';
			break;
			
			case 3: // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO '.THfilters_table.' ( filterfrom, filterto, notes ) VALUES ("'.
					$this->clean($field1).'","'.$this->clean($field2).'","'.$this->clean($field3).'");';
			break;
			
			default:
				die("BCW error: Invalid type provided!");
			break;
		}
		
		$db->myquery($query);
		return sqlite_last_insert_rowid($this->cxn); // Return the insertion ID.
	}
	
	function updateBCW($type = -1, $id, $field1="", $field2="", $field3="")
	{
		/*
			Update either a blotter post, capcode, or wordfilter, based upon the passed type parameter
			Parameters:
				int type
			What to insert.  1 for blotter posts, 2 for capcodes, 3 for wordfilters.
				int id
			The ID corresponding with the item to update.
				string field1, field2, field3
			Different fields to update- the usage of which differs between types.  Look at the comments
			in the switch structure to determine what each parameter should be.
			
			Returns:
				Nothing
		*/
		$type = intval($type);
		
		// It is assumed that all of these will have some sort of ID by which to identify what to update, therefore it is not listed in the FIELD comments.
		switch( $type )
		{
			case 1: // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'UPDATE '.THblotter_table." SET entry = '".$this->clean($field1)."', board=".intval($field2)." WHERE id=".intval($id);
			break;
			
			case 2: // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE '.THcapcodes_table." SET capcodefrom='".
					$this->clean($field1)."', capcodeto='".$this->clean($field2)."', notes='".$this->clean($field3)."' WHERE id=".intval($id);
			break;
			
			case 3: // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE '.THfilters_table." SET filterfrom='".
					$this->clean($field1)."', filterto='".$this->clean($field2)."', notes='".$this->clean($field3)."' WHERE id=".intval($id);
			break;
			
			default:
				die("BCW error: Invalid type provided!");
			break;
		}
		
		$db->myquery($query);
	}
	
	function deleteBCW($type = -1, $id)
	{
		/*
			Delete either a blotter post, capcode, or wordfilter, based upon the passed type parameter
			Parameters:
				int type
			What type of item to delete.  1 for blotter posts, 2 for capcodes, 3 for wordfilters.
				int id
			The ID corresponding with the item to delete.
			
			Returns:
				Nothing
		*/
		$type = intval($type);
		switch( $type )
		{
			case 1: // Blotter posts
				$query = "DELETE FROM ".THblotter_table." WHERE id=".intval($id);
			break;
			
			case 2: // Capcodes
				$query = "DELETE FROM ".THcapcodes_table." WHERE id=".intval($id);
			break;
			
			case 3: // Wordfilters
				$query = "DELETE FROM ".THfilters_table." WHERE id=".intval($id);
			break;
			
			default:
				die("BCW error: Invalid type provided!");
			break;
		}
		
		$db->myquery($query);	
	}
	
	function fetchBCW($type = -1)
	{
		/*
			Retrieve either all blotter posts, capcodes, or wordfilters, based upon the passed type parameter
			Parameters:
				int type
			What type of items to select.  1 for blotter posts, 2 for capcodes, 3 for wordfilters.
			
			Returns:
				An array of items
		*/
		$type = intval($type);
		switch( $type )
		{
			case 1: // Blotter posts
				$query = "SELECT * FROM ".THblotter_table;
			break;
			
			case 2: // Capcodes
				$query = "SELECT * FROM ".THcapcodes_table;
			break;
			
			case 3: // Wordfilters
				$query = "SELECT * FROM ".THfilters_table;
			break;
			
			default:
				die("BCW error: Invalid type provided!");
			break;
		}	
	
		return $db->mymultiarray($query);
	}
	
}//ThornDBI

//===========================================================================================

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
        return($this->myassoc("select * from ".THthreads_table." where id=".$this->clean($t)));
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
        return($this->myassoc("select * from ".THboards_table." where id=".$b));
        }
    function putthread($name,$tpass,$board,$title,$body,$link,$ip,$mod,$pin,$lock,$permasage,$tyme=false) {
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
        if ($tyme===false)
		{
            $tyme=time()+(THtimeoffset*60);
        }
        $q="INSERT INTO ".THthreads_table." ( board, title, body";
		$v=" VALUES ( ".$board." ,'".$this->clean($title)."','";
		if($board==THnewsboard || $board==THmodboard)  //don't filter the news board nor the mod board
		{	
			$v.=$this->escape_string($body);
		} else {
			$v.=$this->clean($body);
		}
		$q.=", ip, pin, permasage, lawk, time, bump";
		$v.="',".$ip." , ".$pin." , ".$permasage." , ".$lock." , ".$tyme." , ".$tyme;
		$globalid=$this->getglobalid($board);
		$q.=", globalid";
		$v.=",".$globalid;
        if ($name!=null)
		{
            $q.=", name";
			$v.=",'".$this->clean($name)."'";
        }
        if ($tpass!=null)
		{
            $q.=", trip";
			$v.="'".$tpass."'";
            //Not cleaning trip since it should be encoded.
        }
		if ($link!=null)
		{
			if(!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/",$link))
			{
				$link = "mailto:".$link;
			} else {
				$link = $this->clean($link);
			}
            $q.=", link";
			$v.="'".$this->clean($link)."'";
        }
        //echo($q.", time=".$tyme);
		//echo $q;
		$visible=1;
		$q.=",time,visible) ";
		$v.=",".$tyme.",".$visible.")";
		$built = $q.$v;
		$this->myquery($built) or THdie("DBpost");
		if ($board == THnewsboard) { rebuild_rss(); } 
		smclearcache($board, -1, -1); // clear the cache for this board
        $tnum=sqlite_last_insert_rowid(THdblitefn);  //help
        $this->myquery("update ".THboards_table." set lasttime=".$tyme." where id=".$board) or THdie("DBpost");
        return($tnum);
    }
    
    function putpost($name,$tpass,$link,$board,$thread,$body,$ip,$mod,$bump,$tyme=false)
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
		$q="INSERT INTO ".THreplies_table." (thread,board,body";
		$v=" ) VALUES (".$thread.",".$board.",'";
		if($board==THmodboard)  //don't filter the mod board since it should be all locked up anyway
		{
			$v.=escape_string($body);
		} else {
			$v.=$this->clean($body);
		}
		
		//FIX THE REST OF THIS QUERY
		$glob=$this->getglobalid($board);
		$q.=",ip,bump,globalid";
		$v.="',".$ip.",".(int)$bump.",".$glob;
        if ($name!=null)
		{
            $q.=", name";
			$v.=",'".$this->clean($name)."'";
        }
        if ($tpass!=null)
		{
            $q.=", trip";
			$v.=",'".$tpass."'";
        }
		$bump = preg_match("/^(mailto:)?sage$/",$link);
		if ($link!=null)
		{
			if(!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/",$link)){ $link = "mailto:".$link; }
            $q.=", link";
			$v.=",'".$this->clean($link)."'";
        }
        if ($tyme===false)
		{
            $tyme=time()+(THtimeoffset*60);
        }
        //echo($q);
		$visible=1;
		$v.=",".$tyme.",".$visible.");";
		$q.=", time, visible";
		//die($q.$v);
        $this->myquery($q.$v) or THdie("DBpost");
		//if ($board == THnewsboard) { buildnews(); }	
        $pnum=sqlite_last_insert_rowid(THdblitefn);	//help
        if (!$bump)
		{
            $this->myquery("update ".THthreads_table." set bump=".$tyme." where id=".$thread." and permasage = 0");
        }
        $this->myquery("update ".THboards_table." set lasttime=".$tyme." where id=".$board) or THdie("DBpost");
		smclearcache($board, -1, -1); // clear cache for the board
		smclearcache($board, -1, $thread); // and for the thread
        return($pnum);
    }
    
    function putimgs($num,$isthread,$files)
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
		

  
        $id=$this->myresult("select max(id) from ".THimages_table)+1;
        foreach ($files as $file)
		{
            $values[]="(".$id.",'".$file['hash']."','".$this->clean($file['name'])."',".$file['width'].",".$file['height'].",'".$this->clean($file['tname'])."',".$file['twidth'].",".$file['theight'].",".$file['fsize'].",".(int)$file['anim'].",".(int)$file['extra_info'].")";
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
        $this->myquery("insert into ".THimages_table." values ".implode(",",$values));
        if ($isthread)
		{
            $this->myquery("update ".THthreads_table." set imgidx=".$id." where id=".$num);
        } else {
            $this->myquery("update ".THreplies_table." set imgidx=".$id." where id=".$num);
        }
		

        return($id);
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
		$board=$this->getbinfo($boardid);
        if ($this->myresult("select count(*) from ".THthreads_table." where board=".$board['id']." and pin=0")>$board['tmax'])
		{
            $last=$this->myassoc("select bump from ".THthreads_table." where board=".$board['id']." and pin=0 order by bump desc limit ".((int)$board['tmax']-1).",1");//-1 'cuz it's zero-based er' somethin'
            //var_dump($last);
            $dels=$this->myquery("select * from ".THthreads_table." where board=".$board['id']." and bump<".$last['bump']." and pin=0");
            $badimgs=array();
            $badths=array();
            while ($del=sqlite_fetch_array($dels))  //help
			{
                //var_dump($del);
                if ($del['imgidx']!=0)
				{
                    $badimgs[]=$del['imgidx'];
                }
                $badths[]=$del['id'];
				smclearcache($board['id'], -1, $del['id']); // clear the associated cache for this thread
            }
            $this->myquery("delete from ".THthreads_table." where bump<".$last['bump']." and pin=0");
            $badthstr=implode(",",$badths);
            $dels=$this->myquery("select imgidx from ".THreplies_table." where board=".$board['id']." and thread in (".$badthstr.") and imgidx!=0");
            while ($del=mysqlite_current($dels))  //help
			{
                $badimgs[]=$del;
            }
            $this->myquery("delete from ".THreplies_table." where thread in (".$badthstr.")");
            return($badimgs);
        } else {
            return(array());
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
        if (count($hashes)>0)
		{
            return($this->myresult("select count(*) from ".THimages_table." where hash in ('".implode("','",$hashes)."')"));
        } else {
            return(0);
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
		$sql = "select globalid from ".THboards_table." where id=".$board;
		$globalid = $this->myresult($sql);
		$globalid++;
		$newsql = "update ".THboards_table." set globalid=".$globalid." where id=".$board;
		$this->myquery($newsql);
		return($globalid);
		}
    } //ThornPostDBI

//===========================================================================================

class ThornModDBI extends ThornDBI
{
  

	// This class should only be created if the user is a mod or admin.
	function ThornModDBI($server=THdbserver,$user=THdbuser,$pass=THdbpass,$base=THdbbase)
	{
        $this->ThornDBI($server,$user,$pass,$base);
    }
    function banip($ip,$subnet,$privatereason,$publicreason,$adminreason,$postdata,$duration,$bannedby)
	{
		/*
		This adds a banned IP to the database. It checks to see if the IP is already banned first.
		Parameters:
			int $ip
		The ip2long'd IP to ban. Note that if Thorn is banning an IP by subnet, this DBI expects the IP to have its fourth part set to 0 before being ip2long'd. (For example, 123.45.67.89 will become 123.45.67.0.) So, if $subnet, we'll need to do this conversion. Fortunately, there's a function in common.php called ipsub() that does this conversion for us, as I think it's a pretty logical way to check for subnets in a DB and may be used by other DBIs.
			bool $subnet
		Ban the IP's subnet?
			string $reason
		Why is this IP being banned? (Solves the "Why did I ban all these IPs again?" problem with phpBB...)
			Returns: bool $added (will be false if IP was already in the database.)
		*/
        if ($this->checkban($ip))
		{
            return(false);
        }
        if ($subnet)
		{
            $ip=ipsub($ip);
        }
		$when = time()+(THtimeoffset*60);
		
		$banquery="insert into ".THbans_table." ( ip,subnet,privatereason,publicreason,adminreason,postdata,duration,bantime,bannedby ) VALUES (
".$ip.", ".(int)$subnet.",'".$this->clean($privatereason)."', 
'".$this->clean($publicreason)."', '".$this->clean($adminreason)."', 
'".$postdata."', '".(int)$duration."',".$when.", '".$bannedby."');";
        $this->myquery($banquery);
		

        return(true);
    }

function banbody($id,$isthread,$publicbanreason="USER HAS BEEN BANNED FOR THIS POST")
{
  //adds (USER HAS BEEN BANNED FOR THIS POST) in big red text
  
  /*
	Fix from thorn discussion board from anonib guy
	
		On the new page, everythign is exactly the same as the old "mod ban/delete" thing, except for how long someone is banned.
		So just copy the whole mod thing over from reply.php/thread.php

		I then added this into the code: $thehours=(int)(($_POST['bandays'] * 24) + $_POST['banhours']);

		{following code block was posted}

	now, we need to be able to customize our ban reasons.  Andrew I think has the mod window done except for a few things or something
	I'm not sure what the exact problem was.
	
	so we'll use $publicbanreason in the threads, which will follow this format, which basically will just add our crap to it:
	
	$publicbanreason="<br><br><b><font color=red>(".$publicbanreason.")</font></b>";
	we may want to not hard code the text as being red, and leave it up to css to decide what to make it
	
	Now, in the mod window, when you click on the ban reason box, javascript will fill out "USER HAS BEEN BANNED FOR THIS POST"
	as the default, which you can then change to anything
	
	The public ban reason is the only one they will see
	
	Private ban reason will show up on the admin panel and only be viewable by logged in admin
	
	All that the below code does is add the (ban reason).  It doesn't do php banning nor does it actually add to the ban page
	Stuff I think we need to store for bans
		private reason
		public reason
		ip
		time
		post banned for
*/
        if($publicbanreason) {
        $publicbanreason='<br /><br /><b><span class=ban>('.$publicbanreason.')</span></b>';
        } else {
                return;
        }
  if($isthread)
  {
    $thebody=$this->myresult("select body from ".THthreads_table." where id=".$id);
	$thebody.=$publicbanreason;
	$updatequery="update ".THthreads_table." set body='".escape_string(nl2br($thebody))."' where id=".$id;
        $myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
  } else {
    $thebody=$this->myresult("select body from ".THreplies_table." where id=".$id);
	$thebody.=$publicbanreason;
	$updatequery="update ".THreplies_table." set body='".escape_string(nl2br($thebody))."' where id=".$id;
	$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
  }
  return;
}
    function banipfrompost($id,$isthread,$subnet,$privatereason,$publicreason,$adminreason,$duration,$bannedby)
	{
		/*
		Bans an IP or subnet by fetching the naughty IP from a post or thread. Calls banip().
		Parameters:
			int $id
		The ID number of the naughty thread or post.
			bool $isthread
		If true, $id refers to a thread. If false, it refers to a post.
			bool $subnet
		If true, ban the IP's subnet. See banip() for more info on banning subnets.
			string $reason
		Again, see banip().
			Returns: bool $added (will be false if IP was already banned.)
		*/
        if ($isthread)
		{
			$q1="select ip from ".THthreads_table." where id=".$id;
            $ip=$this->myresult($q1);
			$q2 = "select globalid,board,body from ".THthreads_table." where id=".$id;
			$postdata=$this->myassoc($q2);
			$postdata='Post '.$postdata['globalid'].' in /'.getboardname($postdata['board'])
				."/:<br />".nl2br($postdata['body']);
        } else {
            $ip=$this->myresult("select ip from ".THreplies_table." where id=".$id);
			$postdata=$this->myassoc("select globalid,board,body from ".THreplies_table." where id=".$id);
			$postdata='Post '.$postdata['globalid'].' in /'.getboardname($postdata['board'])
				."/:<br />".nl2br($postdata['body']);
        }
		$this->banbody($id,$isthread,$publicreason);
//		echo $result; die();
        return($this->banip($ip,$subnet,$privatereason,$publicreason,$adminreason,$postdata,$duration,$bannedby));
    }

    function delban($ip)
	{
		/*
		Simply deletes a banned IP from the database.
		Parameters:
			int $ip
		The ip2long'd IP to delete.
		*/
		$this->myquery("delete from ".THbans_table." where ip=".$ip);
    }
    
    function getallbans()
	{
		/*
		Simply returns all ban information. Intended for use to render the ban management admin page.
		No parameters.
		*/
        $rows=$this->myquery("select * from ".THbans_table);
        $baddies=array();
        while ($row=sqlite_fetch_array($rows))  //help
		{
			$row['subnet']=(bool)$row['subnet'];
                $baddies[]=$row;
        }
        return($baddies);
    }

    function delpost($id,$isthread)
	{
		/*
		Deletes a post or thread. If $isthread, all posts in the thread will be deleted to. In addition, if the deleted post(s) had images, we need to delete the
		relevant image information from the database.
		Parameters:
			int $id
		The ID number of the offending post.
			bool $isthread
		blah blah yadda yadda
			Returns: array $affected-images, a one-dimensional array of image indexes that need to be deleted. This array is passed to a function in Thorn that deletes images.
			(Note that this is separate from image INFORMATION, which is stored in the database; the actual images are stored in a directory on disk.)
		*/
        if ($isthread)
		{
            $rezs=$this->myquery("select distinct imgidx from ".THreplies_table." where thread=".$id." and imgidx!=0");
            $duck=(int)$this->myresult("select imgidx from ".THthreads_table." where id=".$id);
            //the myresult() in the line above is returning strings for some reason.
            if ($duck!=0)
			{
                $affimg=array($duck);
            } else {
                $affimg=array();
            }
            while ($rez=sqlite_fetch_array($rezs))  //help
			{
                $affimg[]=$rez['imgidx'];
            }
            $this->myquery("delete from ".THreplies_table." where thread=".$id);
            $this->myquery("delete from ".THthreads_table." where id=".$id);
            if (count($affimg)>0)
			{
				$EXIFquery = $this->myquery("select extra_info from ".THimages_table." where id in (".implode(",",$affimg).")"); // Remove extra_info sections
				while ($exif=sqlite_fetch_array($EXIFquery))  //help
				{
					$extra_info_entries[]=$exif['extrainfo'];
				}
				
				if( count($extra_info_entries)>0)
				{
					$this->myquery("delete from ".THextrainfo_table." where id in (".implode(",",$extra_info_entries).")");
				}
				
                $this->myquery("delete from ".THimages_table." where id in (".implode(",",$affimg).")");
            }
			
        } 
		else 
		{
            $duck=$this->myresult("select imgidx from ".THreplies_table." where id=".$id);
            //echo($duck);
            if ($duck!=0)
			{
                $affimg=array($duck);
				
				$extra_info_entries = $this->myquery("what select extra_info from ".THimages_table." where id=".$duck); // remove extra_info sections
				if( count($extra_info_entries)>0)
				{
				$this->myquery("delete from ".THextrainfo_table." where id in (".implode(",",$extra_info_entries).")");
				}
				
                $this->myquery("delete from ".THimages_table." where id=".$duck);
            } 
			else 
			{
                $affimg=array();
            }
			$this->myquery("delete from ".THreplies_table." where id=".$id);
        }
        return($affimg);
    }

    function delip($ip,$delsub=false)
	{
		/*
		Deletes all posts from an IP. If the IP created threads, all posts from those threads are deleted too. If $delsub, all posts from the
		subnet are fragged. If any if the fragged posts contain images, that image info is removed from the database, and the image indexes
		are stored in an array to be passed to Thorn's image deletion function.
		Parameters:
			int $ip
		blah blah
			bool $delsub=false
		yadda yadda
			Returns: array $affected-images
		*/
        if ($delsub)
		{
            $sub=ipsub($ip);
            $submax=$sub+255;
            $q1=$this->myquery("select distinct imgidx from ".THreplies_table." where ip between ".$sub." and ".$submax." and imgidx!=0");
            $q2=$this->myquery("select distinct imgidx from ".THthreads_table." where ip between ".$sub." and ".$submax." and imgidx!=0");
            $q3=$this->myquery("select id, board from ".THthreads_table." where ip between ".$sub." and ".$submax);
            $this->myquery("delete from ".THreplies_table." where ip between ".$sub." and ".$submax);
            $this->myquery("delete from ".THthreads_table." where ip between ".$sub." and ".$submax);
        } 
		else 
		{
            //echo($ip);
            $q1=$this->myquery("select distinct imgidx from ".THreplies_table." where ip=".$ip." and imgidx!=0");
            $q2=$this->myquery("select distinct imgidx from ".THthreads_table." where ip=".$ip." and imgidx!=0");
            $q3=$this->myquery("select id, board from ".THthreads_table." where ip=".$ip);
            $this->myquery("delete from ".THreplies_table." where ip=".$ip);
            $this->myquery("delete from ".THthreads_table." where ip=".$ip);
        }
		
        $affimgs=array();
        while($rez=sqlite_fetch_array($q1))  //help
		{
            $affimgs[]=$rez['imgidx'];
        }
        while($rez=sqlite_fetch_array($q2))  //help
		{
            $affimgs[]=$rez['imgidx'];
        }
        $affthreads=array();
        while($rez=sqlite_fetch_array($q3))  //help
		{
            $affthreads[]=$rez['id'];
			smclearcache($rez['board'], -1, $rez['id']); // clear the associated cache for this thread
			smclearcache($rez['board'], -1, -1); // AND this board
        }
        if (count($affthreads)>0)
		{
            $affstr=implode(",",$affthreads);
            $q4=$this->myquery("select distinct imgidx from ".THreplies_table." where thread in (".$affstr.")");
            $this->myquery("delete from ".THreplies_table." where thread in (".$affstr.")");
        }
        while ($rez=sqlite_fetch_array($q4))  //help
		{
            $affimgs[]=$rez['imgidx'];
        }
        if (count($affimgs)>0)
		{
			$EXIFquery = $this->myquery("select extra_info from ".THimages_table." where id in (".implode(",",$affimgs).")"); // Remove extra_info sections
			while ($exif=sqlite_fetch_array($EXIFquery))  //help
			{
				$extra_info_entries[]=$exif['extrainfo'];
			}
			
			if( count($extra_info_entries)>0)
			{
				$this->myquery("delete from ".THextrainfo_table." where id in (".implode(",",$extra_info_entries).")");
			}
				
            $this->myquery("delete from ".THimages_table." where id in (".implode(",",$affimgs).")");
        }
        return($affimgs);
    }

    function delipfrompost($id,$isthread,$subnet=false)
	{
		/*
		Gets the IP of a post for which we want to delete all posts from that IP. Yeah. Like banipfrompost().
		Parameters:
		(If you've been following along, you can probably guess what all these params do by now.)
			int $id
			bool $isthread
			bool $subnet
		*/
        if ($isthread)
		{
            $ip=$this->myresult("select ip from ".THthreads_table." where id=".$id);
        } else {
            $ip=$this->myresult("select ip from ".THreplies_table." where id=".$id);
        }
        return($this->delip($ip,$subnet));
    }

    function updateboards($boards)
	{
		/*
		You know, I'm getting tired of typing all these things up for every simple little function. If you can't
		figure out by yourself what this does, quit programming. Forever. Now.
		*/
        $max=$this->myresult("select max(id) from ".THboards_table)+1;
        $this->myquery("delete from ".THboards_table);
        $changeroo=array();
        foreach ($boards as $board)
		{
            if ($board['oldid']!=$board['id'])
			{
                //Way to keep posts and threads on their intended board. A bit hackneyed, but should work.
                $changeroo[]=array("now"=>$max,"to"=>$board['id']);
                $this->myquery("update ".THthreads_table." set board=".$max." where board=".$board['oldid']);
                $this->myquery("update ".THreplies_table." set board=".$max." where board=".$board['oldid']);
                $max++;
            }
			$query="insert into ".THboards_table." set id=".$board['id'].", globalid=".$board['globalid'].", name='".$this->clean($board['name'])."', folder='".$this->clean($board['folder'])."', about='".$this->clean($board['about'])."', rules='".$board['rules']."', perpg=".$board['perpg'].", perth=".$board['perth'].", hidden=".$board['hidden'].", forced_anon=".$board['forced_anon'].", tlock=".$board['tlock'].", rlock=".$board['rlock'].", tpix=".$board['tpix'].", rpix=".$board['rpix'].", tmax=".$board['tmax'];
			print_r($query);
            $this->myquery($query);
        }
        foreach ($changeroo as $change)
		{
            $this->myquery("update ".THthreads_table." set board=".$change['to']." where board=".$change['now']);
            $this->myquery("update ".THreplies_table." set board=".$change['to']." where board=".$change['now']);
        }                
    }

    function fragboard($board)
	{
		/* 
		This deletes all the threads, posts and image info from a certain board. This is in case we're deleting the board;
		we wanna frag all the images on the board too. It returns a list of the image indexes so that they can be deleted off of the disk by Thorn.
		Parameters:
			$board
		duh
			Returns: array $imgidxes (one-dimensional)
		*/
        $imgidxes=array();
        $hare=$this->myquery("select distinct imgidx from ".THthreads_table." where board=".$board." and imgidx!=0");
        while ($xyz=sqlite_fetch_array($hare))  //help
		{
            $imgidxes[]=$xyz['imgidx'];
        }
        $hare=$this->myquery("select distinct imgidx from ".THreplies_table." where board=".$board." and imgidx!=0");
        while ($xyz=sqlite_fetch_array($hare))  //help
		{
            $imgidxes[]=$xyz['imgidx'];
        }
        $this->myquery("delete from ".THthreads_table." where board=".$board);
        $this->myquery("delete from ".THreplies_table." where board=".$board);
        if (count($imgidxes)!=0)
		{
            $this->myquery("delete from ".THimages_table." where id in (".implode(",",$imgidxes).")");
        }
		smclearcache($board, -1, -1, true); // clear EVERYTHING in the cache associated with this board
        return($imgidxes);
    }

}//class ThornModDBI

//===========================================================================================

class ThornBoardDBI extends ThornDBI
{
    function ThornBoardDBI($bored,$payj,$on=array())
	{
        $this->ThornDBI();
        $this->page=$payj;
        $this->binfo=$this->myassoc("select * from ".THboards_table." where id=".$bored);
        $this->on=$on;
		$this->blotterentries=$this->getblotter($bored);
        //$this->st=$st;
        //$this->et=$et;
        var_dump($this->binfo);
        //die();
    }
    
    function getallthreads($p, &$sm)
	{
		/*
		Get all threads on this board.
		Parameters:
			bool $p['full']=false
		If false, only basic information about the threads will be returned. If true, all information including body text and images for each thread head will be returned.
			string $p['sortmethod']="bump"
		If "bump", threads will be sorted by bump (most recent reply). If "time", they will be sorted by date. If "title", they will be sorted by title alphabetically. If "id", they will be sorted by ID number.
			bool $p['desc']=true
		If true, threads will be sorted in descending order.
			bool $p['date']=false
		If true, perform a getdate() call on each entry and put result in a 'date' array with each entry.
			Returns: array $threads
		*/
        if (isset($p['full'])==false)
		{
            $p['full']=false;
        }
        if (isset($p['sortmethod'])==false)
		{
            $p['sortmethod']="bump";
        }
        if (isset($p['desc'])==false)
		{
            $p['desc']=true;
        }
        if (isset($p['date'])==false)
		{
            $p['date']=false;
        }
        if (isset($p['start'])==false)
		{
            $p['start']=false;
        }
        if (isset($p['end'])==false)
		{
            $p['end']=false;
        }
        
        if ($p['full']==true)
		{
            $q="select * from ".THthreads_table." where board=".$this->binfo['id'];
        } else {
            $q="select id, title, name, trip, link, time, pin, lawk, bump, globalid from ".THthreads_table." where board=".$this->binfo['id'];
        }

        if ($p['start']!=false)
		{
            $q.=" and time>=".$p['start'];
        }
        if ($p['end']!=false)
		{
            $q.=" and time<".$p['end'];
        }
        
        
        if (isset($this->on['year']))
		{
            if (isset($this->on['month']))
			{
                if (isset($this->on['day']))
				{
                    $q.=" and time>=".mktime(0,0,0,$this->on['month'],$this->on['day'],$this->on['year'])." and time<".mktime(0,0,0,$this->on['month'],$this->on['day']+1,$this->on['year']);
                } else {
                    $q.=" and time>=".mktime(0,0,0,$this->on['month'],1,$this->on['year'])." and time<".mktime(0,0,0,$this->on['month']+1,1,$this->on['year']);
                }
            } else {
                $q.=" and time>=".mktime(0,0,0,1,1,$this->on['year'])." and time<".mktime(0,0,0,1,1,$this->on['year']+1);
            }
        }

        if  ($p['sortmethod']=="bump")
		{
            $q.=" order by pin desc, bump";
        }
        elseif ($p['sortmethod']=="time")
		{
            $q.=" order by pin desc, time";
        }
        elseif ($p['sortmethod']=="title")
		{
            $q.=" order by pin desc, title";
        }
        elseif ($p['sortmethod']=="id")
		{
            $q.=" order by pin desc, id";
        }

        if ($p['desc'])
		{
            $q.=" desc";
        }

        $rezs=$this->myquery($q);
        
        $threads=array();
        while (@$th=sqlite_fetch_array($rezs))  //help
		{
            unset($th['ip']);
            if ($p['full']==true)
			{
                $th['images']=$this->getimgs($th['imgidx']);
            }
            if ($p['date']==true)
			{
                $th['date']=getdate($th['time']);
            }
            $threads[]=$th;
        }
        
        return($threads);
    }
    

    
    function getsthreads($p, &$sm)
	{
		/*
		Get sample threads for this board -- the ones intended to be shown in the main area of the page, with the last $binfo['perth'] replies per thread.
		Parameters:
			string $p['sortmethod']="bump"
		If "bump", threads will be sorted by bump (most recent reply). If "time", they will be sorted by date. If "title", they will be sorted by title alphabetically. If "id", they will be sorted by ID number.
			bool $p['tdesc']=true
		If true, threads will be sorted in descending order.
			bool $p['rdesc']=false
		If true, replies will be sorted in descending order. (Replies are always sorted by time.)
			Returns: array $sthreads
		*/
        if (isset($p['sortmethod'])==false)
		{
            $p['sortmethod']="bump";
        }
        if (isset($p['tdesc'])==false)
		{
            $p['tdesc']=true;
        }
        if (isset($p['rdesc'])==false)
		{
            $p['rdesc']=false;
        }
        
        //What the frick is this crap?
        //var_dump($this->on);
        //var_dump($p);
        //die("here");
        $orderby="";
        if  ($p['sortmethod']=="bump")
		{
            $orderby.=" order by pin desc, bump";
        }
        elseif ($p['sortmethod']=="time")
		{
            $orderby.=" order by pin desc, time";
        }
        elseif ($p['sortmethod']=="title")
		{
            $orderby.=" order by pin desc, title";
        }
        elseif ($p['sortmethod']=="id")
		{
            $orderby.=" order by pin desc, id";
        }
        if ($p['tdesc'])
		{
            $orderby.=" desc";
        }
        //print_r($this->binfo);
        $orderby.=" limit ".($this->page*$this->binfo['perpg']).",".$this->binfo['perpg'];
        $thatid=$this->binfo['id'];

        $frog=$this->myquery("select * from ".THthreads_table." where board=".$thatid.$orderby);
        $sthreads=array();
        while ($th=sqlite_fetch_array($frog))  //help
		{
			//print_r($th); echo "<br />";
            unset($th['ip']);
            $th['images']=$this->getimgs($th['imgidx']);
			$debug="select count(*) from ".THreplies_table." where thread=".$th['id'];
			//echo $debug."<hr>";
            $th['rcount']=$this->myresult($debug);
            if ($th['rcount']==0 || $this->binfo['perth']==0)
			{
                $th['reps']=null;
                $th['scount']=0;
            } else {
                $start=$th['rcount']-$this->binfo['perth'];
                if ($start<0)
				{
                    $start=0;
                }

                if ($p['rdesc'])
				{
                    $orderby=" order by time desc limit ".$start.",".$this->binfo['perth'];
                } else {
                    $orderby=" order by time limit ".$start.",".$this->binfo['perth'];
                }
                $toad=$this->myquery("select * from ".THreplies_table." where thread=".$th['id'].$orderby);
                while ($reply=sqlite_fetch_array($toad))  //help
				{
                    unset($reply['ip']);
                    $reply['images']=$this->getimgs($reply['imgidx']);
                    $th['reps'][]=$reply;
                }
                $th['scount']=count($th['reps']);
            }
            var_dump($th);
            $sthreads[]=$th;
        }
        return($sthreads);
    }//getsthreads
        
}//class ThornBoardDBI

//===========================================================================================

class ThornThreadDBI extends ThornDBI
{
    function ThornThreadDBI($th, $brd)
	{
        $this->ThornDBI();
		//this should fix it!
        $this->head=$this->myassoc("select * from ".THthreads_table." where globalid=".$th." and board=".$brd);
        unset($this->head['ip']);
        $this->head['images']=$this->getimgs($this->head['imgidx']);
        $this->binfo=$this->myassoc("select * from ".THboards_table." where id=".$this->head['board']);
		$this->blotterentries=$this->getblotter($binfo['id']);
    }
    function getreplies($p, &$sm)
	{
		/*
		Returns the replies for this thread.
		Parameters:
			string $p['sortmethod']="time"
		If "time", posts will be sorted by post time. If "id", they will be sorted by ID number. (Theoretically, each of these will yield the same result.)
			bool $p['desc']=false
		If true, posts will be sorted in descending order.
			bool $p['withhead']=false
		If true, the thread head will be included in the results. The thread head will be put at the beginning of the array if $p['desc']==false and at the end if $p['desc']==true.
			bool $p['full']=true
		If true, the full information from each post is retrieved, including images. If false, only the ID, name, trip and time will be returned.
			Returns: array $posts
		*/
        if (isset($p['sortmethod'])==false)
		{
            $p['sortmethod']="time";
        }
        if (isset($p['desc'])==false)
		{
            $p['desc']=false;
        }
        if (isset($p['withhead'])==false)
		{
            $p['withhead']=false;
        }
        if (isset($p['full'])==false)
		{
            $p['full']=true;
        }

        if ($p['full'])
		{
            $q="select * from ".THreplies_table." where thread=".$this->head['id']." order by ";
        } else {
            $q="select id, name, trip, link, time, globalid from ".THreplies_table." where thread=".$this->head['id']." order by ";
        }
        if ($p['sortmethod']=="time")
		{
            $q.="time";
        }
        elseif ($p['sortmethod']="id")
		{
            $q.="id";
        }
        
        if ($p['desc'])
		{
            $q.=" desc";
        }
        $tadpole=$this->myquery($q);
        
        $replies=array();
        if ($p['withhead'] && $p['desc']==false)
		{
            $replies[]=$this->head;
        }
        while ($reply=sqlite_fetch_array($tadpole))  //help
		{
            unset($reply['ip']);
            if ($p['full'])
			{
                $reply['images']=$this->getimgs($reply['imgidx']);
            }
            $replies[]=$reply;
        }
        if ($p['withhead'] && $p['desc'])
		{
            $replies[]=$this->head;
        }

        return($replies);
    }
}//class ThornGlobalThreadDBI

?>
