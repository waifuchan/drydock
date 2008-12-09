<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite-mod.php
	Description:    Code for the ThornModDBI class, based upon the SQLite version of ThornDBI
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornModDBI extends ThornDBI
{

	// This class should only be created if the user is a mod or admin.
	function ThornModDBI()
	{
		$this->ThornDBI();
	}
	
	function banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby)
	{
		/*
		This adds a banned IP to the database. It checks to see if the IP is already banned first.
		
		Parameters:
			int $ip
		The ip2long'd IP to ban.   Additionally, a string is acceptable.  If is_int($ip) is true it will be converted
		back into a string.
			int $subnet
		0 to ban no subnet, 1 to ban the subnet, 2 to ban the class C subnet (be careful with this)
			string $privatereason
		What reason will the user see for being banned?
			string $publicreason
		What reason will be publically shown?
			string $publicreason
		What reason will be the admins see for being banned?
			string $postdata
		What post was the user banned for?
			int $duration
		How long will the ban be? (-1 = perma, 0 = warning, everything else is in hours)
			string $bannedby
		Who is responsible for the banning?
		
			Returns: bool $added (will be false if IP was already in the database.)
		*/
		
		if ($this->checkban($ip) && $subnet == 0) // Check if it's a ban for a specific IP that's already covered by a subnet (the reverse is OK)
		{
			return (false);
		}

		if ( is_int($ip) ) // If it's an int, change it back over to the other format
		{
			$ip = long2ip($ip);
		}
		
		// Start messing around with the octets
		$octets = explode(".", long2ip($ip), 4);
		
		$octets[0] = intval($octets[0]);
		$octets[1] = intval($octets[1]);
		
		if( $subnet > 0 ) // Ban a subnet
		{
			$octets[3] = -1;
		}
		
		if( $subnet > 1) // Ban a class C subnet
		{
			$octets[2] = -1;
		}
		
		// Calculate when the ban was made
		$when = time() + (THtimeoffset * 60);
		
		$banquery = "insert into ".THbans_table." 
		( ip_octet1,ip_octet2,ip_octet3,ip_octet4,privatereason,publicreason,adminreason,postdata,duration,bantime,bannedby )
		VALUES (" . $octets[0] . "," . $octets[1] . "," . $octets[2] . "," . $octets[3] . ",
		'" . $this->clean($privatereason) . "','" . $this->clean($publicreason) . "','" . $this->clean($adminreason) . "', 
		'" . $this->clean($postdata) . "','" . intval($duration) . "'," . $when . ",'" . $this->clean($bannedby) . "')";
		
		$this->myquery($banquery);
		
		return (true);
	}

	function banbody($id, $isthread, $publicbanreason = "USER HAS BEEN BANNED FOR THIS POST")
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
		if ($publicbanreason)
		{
			$publicbanreason = '<br /><br /><b><span class=ban>(' . $publicbanreason . ')</span></b>';
		}
		else
		{
			return;
		}
		if ($isthread)
		{
			$thebody = $this->myresult("select body from " . THthreads_table . " where id=" . $id);
			$thebody .= $publicbanreason;
			$updatequery = "update " . THthreads_table . " set body='" . $this->escape_string(nl2br($thebody)) . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		else
		{
			$thebody = $this->myresult("select body from " . THreplies_table . " where id=" . $id);
			$thebody .= $publicbanreason;
			$updatequery = "update " . THreplies_table . " set body='" . $this->escape_string(nl2br($thebody)) . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		return;
	}
	function banipfrompost($id, $isthread, $subnet, $privatereason, $publicreason, $adminreason, $duration, $bannedby)
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
			$q1 = "select ip from " . THthreads_table . " where id=" . $id;
			$ip = $this->myresult($q1);
			$q2 = "select globalid,board,body from " . THthreads_table . " where id=" . $id;
			$postdata = $this->myassoc($q2);
			$postdata = 'Post ' . $postdata['globalid'] . ' in /' . $this->getboardname($postdata['board']) .
			"/:<br />" . nl2br($postdata['body']);
		}
		else
		{
			$ip = $this->myresult("select ip from " . THreplies_table . " where id=" . $id);
			$postdata = $this->myassoc("select globalid,board,body from " . THreplies_table . " where id=" . $id);
			$postdata = 'Post ' . $postdata['globalid'] . ' in /' . $this->getboardname($postdata['board']) .
			"/:<br />" . nl2br($postdata['body']);
		}
		$this->banbody($id, $isthread, $publicreason);
		//		echo $result; die();
		return ($this->banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby));
	}
	
	function delban($id, $reason="None provided")
	{
		/*
		Simply deletes a ban from the database.  It will be moved to the ban history table.
		
		Parameters:
			int $id
		The ID of the ban to delete.
			string $reason
		Why the ban is getting lifted
		*/
		
		$singleban = $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
		
		if( $singleban )
		{
			$history = "insert into ".THbanhistory_table." 
			( ip_octet1,ip_octet2,ip_octet3,ip_octet4,privatereason,publicreason,adminreason,postdata,duration,bantime,bannedby,unbaninfo )
			VALUES (" . $singleban['ip_octet1'] . "," . $singleban['ip_octet2'] . "," . $singleban['ip_octet3'] . "," . 
			$singleban['ip_octet4'] . ",'" . $this->clean($singleban['privatereason']) . "','" . $this->clean($singleban['publicreason']) . 
			"','" . $this->clean($singleban['adminreason']) . "','" . $this->clean($singleban['postdata']) . "','" . 
			intval($singleban['duration']) . "'," . $singleban['bantime'] . ",'" . $this->$singleban['bannedby'] . "','" 
			.$this->clean($reason)."')";
				
			$this->myquery($history);
		
			$this->myquery("delete from " . THbans_table . " where id=" . intval($id));
		}
	}

	function getbanfromid($id)
	{
		/*
		Retrieve a ban from the database based on ID
		Parameters:
			int $id
		The ID of the ban
		
			Returns:
		An array with the ban data
		*/
		
		return $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
	}
	
	function getiphistory($ip)
	{
		/*
			Get ban history information for a particular IP.  Note that this does not include active bans.
			
			Parameters:
				int $ip
			The IP address.  long2ip will be used on it.
			
			Returns:
				An array of assoc-arrays
		*/
			
		// Break up into octets
		$octets = explode(".", long2ip($ip), 4);

		//Retrieve the bans
		$bans = $this->mymultiarray("select * from " . THbanhistory_table . " where 
			ip_octet1=" . intval($octets[0]) . " 
			&& ip_octet2=" . intval($octets[1]) . " 
			&& (ip_octet3=" . intval($octets[2]) . " || ip_octet3 = -1 )
			&& (ip_octet4=" . intval($octets[3]) . " || ip_octet4 = -1 )");
		
		return $bans;
	}

	function getallbans()
	{
		/*
		Simply returns all ban information. Intended for use to render the ban management admin page.
		No parameters.
		*/
		$baddies = array ();
		$baddies = $this->mymultiarray("select * from " . THbans_table);

		// New ban table makes this unnecessary!
		//foreach ($baddies as $row)
		//{
		//	$row['subnet'] = (bool) $row['subnet'];
		//}
		return ($baddies);
	}

	function delpost($id, $isthread)
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
			$rezs = $this->myquery("select distinct imgidx from " . THreplies_table . " where thread=" . $id . " and imgidx!=0");
			$duck = (int) $this->myresult("select imgidx from " . THthreads_table . " where id=" . $id);
			//the myresult() in the line above is returning strings for some reason.
			if ($duck != 0)
			{
				$affimg = array (
					$duck
				);
			}
			else
			{
				$affimg = array ();
			}
			while ($rez = sqlite_fetch_array($rezs)) //help
			{
				$affimg[] = $rez['imgidx'];
			}
			$this->myquery("delete from " . THreplies_table . " where thread=" . $id);
			$this->myquery("delete from " . THthreads_table . " where id=" . $id);
			if (count($affimg) > 0)
			{
				$EXIFquery = $this->myquery("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimg) . ")"); // Remove extra_info sections
				while ($exif = sqlite_fetch_array($EXIFquery)) //help
				{
					$extra_info_entries[] = $exif['extrainfo'];
				}

				if (count($extra_info_entries) > 0)
				{
					$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
				}

				$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $affimg) . ")");
			}

		}
		else
		{
			$duck = $this->myresult("select imgidx from " . THreplies_table . " where id=" . $id);
			//echo($duck);
			if ($duck != 0)
			{
				$affimg = array (
					$duck
				);

				$extra_info_entries = $this->myquery("what select extra_info from " . THimages_table . " where id=" . $duck); // remove extra_info sections
				if (count($extra_info_entries) > 0)
				{
					$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
				}

				$this->myquery("delete from " . THimages_table . " where id=" . $duck);
			}
			else
			{
				$affimg = array ();
			}
			$this->myquery("delete from " . THreplies_table . " where id=" . $id);
		}
		return ($affimg);
	}

	function delip($ip, $delsub = false)
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
			$sub = ipsub($ip);
			$submax = $sub +255;
			$q1 = $this->myquery("select distinct imgidx from " . THreplies_table . " where ip between " . $sub . " and " . $submax . " and imgidx!=0");
			$q2 = $this->myquery("select distinct imgidx from " . THthreads_table . " where ip between " . $sub . " and " . $submax . " and imgidx!=0");
			$q3 = $this->myquery("select id, board from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
			$this->myquery("delete from " . THreplies_table . " where ip between " . $sub . " and " . $submax);
			$this->myquery("delete from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
		}
		else
		{
			//echo($ip);
			$q1 = $this->myquery("select distinct imgidx from " . THreplies_table . " where ip=" . $ip . " and imgidx!=0");
			$q2 = $this->myquery("select distinct imgidx from " . THthreads_table . " where ip=" . $ip . " and imgidx!=0");
			$q3 = $this->myquery("select id, board from " . THthreads_table . " where ip=" . $ip);
			$this->myquery("delete from " . THreplies_table . " where ip=" . $ip);
			$this->myquery("delete from " . THthreads_table . " where ip=" . $ip);
		}

		$affimgs = array ();
		while ($rez = sqlite_fetch_array($q1)) //help
		{
			$affimgs[] = $rez['imgidx'];
		}
		while ($rez = sqlite_fetch_array($q2)) //help
		{
			$affimgs[] = $rez['imgidx'];
		}
		$affthreads = array ();
		while ($rez = sqlite_fetch_array($q3)) //help
		{
			$affthreads[] = $rez['id'];
			smclearcache($rez['board'], -1, $rez['id']); // clear the associated cache for this thread
			smclearcache($rez['board'], -1, -1); // AND this board
		}
		if (count($affthreads) > 0)
		{
			$affstr = implode(",", $affthreads);
			$q4 = $this->myquery("select distinct imgidx from " . THreplies_table . " where thread in (" . $affstr . ")");
			$this->myquery("delete from " . THreplies_table . " where thread in (" . $affstr . ")");
		}
		while ($rez = sqlite_fetch_array($q4)) //help
		{
			$affimgs[] = $rez['imgidx'];
		}
		if (count($affimgs) > 0)
		{
			$EXIFquery = $this->myquery("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimgs) . ")"); // Remove extra_info sections
			while ($exif = sqlite_fetch_array($EXIFquery)) //help
			{
				$extra_info_entries[] = $exif['extrainfo'];
			}

			if (count($extra_info_entries) > 0)
			{
				$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
			}

			$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $affimgs) . ")");
		}
		return ($affimgs);
	}

	function delipfrompost($id, $isthread, $subnet = false)
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
			$ip = $this->myresult("select ip from " . THthreads_table . " where id=" . $id);
		}
		else
		{
			$ip = $this->myresult("select ip from " . THreplies_table . " where id=" . $id);
		}
		return ($this->delip($ip, $subnet));
	}

	function updateboards($boards)
	{
		/*
		You know, I'm getting tired of typing all these things up for every simple little function. If you can't
		figure out by yourself what this does, quit programming. Forever. Now.
		*/
		$max = $this->myresult("select max(id) from " . THboards_table) + 1;
		$this->myquery("delete from " . THboards_table);
		$changeroo = array ();
		foreach ($boards as $board)
		{
			if ($board['oldid'] != $board['id'])
			{
				//Way to keep posts and threads on their intended board. A bit hackneyed, but should work.
				$changeroo[] = array (
					"now" => $max,
					"to" => $board['id']
				);
				$this->myquery("update " . THthreads_table . " set board=" . $max . " where board=" . $board['oldid']);
				$this->myquery("update " . THreplies_table . " set board=" . $max . " where board=" . $board['oldid']);
				$max++;
			}
			$query = "insert into " . THboards_table . " set id=" . $board['id'] . ", globalid=" . $board['globalid'] . ", name='" . $this->escape_string($board['name']) . "', folder='" . $this->escape_string($board['folder']) . "', about='" . $this->escape_string($board['about']) . "', rules='" . $board['rules'] . "', perpg=" . $board['perpg'] . ", perth=" . $board['perth'] . ", hidden=" . $board['hidden'] . ", forced_anon=" . $board['forced_anon'] . ", tlock=" . $board['tlock'] . ", rlock=" . $board['rlock'] . ", tpix=" . $board['tpix'] . ", rpix=" . $board['rpix'] . ", tmax=" . $board['tmax'];
			print_r($query);
			$this->myquery($query);
		}
		foreach ($changeroo as $change)
		{
			$this->myquery("update " . THthreads_table . " set board=" . $change['to'] . " where board=" . $change['now']);
			$this->myquery("update " . THreplies_table . " set board=" . $change['to'] . " where board=" . $change['now']);
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
		$imgidxes = array ();
		$hare = $this->myquery("select distinct imgidx from " . THthreads_table . " where board='" . $board . "' and imgidx!=0");
		while ($xyz = sqlite_fetch_array($hare)) //help
		{
			$imgidxes[] = $xyz['imgidx'];
		}
		$hare = $this->myquery("select distinct imgidx from " . THreplies_table . " where board='" . $board . "' and imgidx!=0");
		while ($xyz = sqlite_fetch_array($hare)) //help
		{
			$imgidxes[] = $xyz['imgidx'];
		}
		$this->myquery("delete from " . THthreads_table . " where board='" . $board. "'");
		$this->myquery("delete from " . THreplies_table . " where board='" . $board. "'");
		if (count($imgidxes) != 0)
		{
			$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $imgidxes) . ")");
		}
		smclearcache($board, -1, -1, true); // clear EVERYTHING in the cache associated with this board
		return ($imgidxes);
	}

	function insertBCW($type = -1, $field1 = "", $field2 = "", $field3 = "")
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
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$time = (THtimeoffset * 60) + time();
				$query = "INSERT INTO " . THblotter_table . " ( entry, board, time ) VALUES ('" .
				$this->escape_string($field1) . "','" . intval($field2) . "','" . $time . "')";
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THcapcodes_table . ' ( capcodefrom, capcodeto, notes ) VALUES ("' .
				$this->escape_string($field1) . '","' . $this->escape_string($field2) . '","' . $this->escape_string($field3) . '");';
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THfilters_table . ' ( filterfrom, filterto, notes ) VALUES ("' .
				$this->escape_string($field1) . '","' . $this->escape_string($field2) . '","' . $this->escape_string($field3) . '");';
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
		return sqlite_last_insert_rowid($this->cxn); // Return the insertion ID.
	}

	function updateBCW($type = -1, $id, $field1 = "", $field2 = "", $field3 = "")
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
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'UPDATE ' . THblotter_table . " SET entry = '" . $this->escape_string($field1) . "', board=" . intval($field2) . " WHERE id=" . intval($id);
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THcapcodes_table . " SET capcodefrom='" .
				$this->escape_string($field1) . "', capcodeto='" . $this->escape_string($field2) . "', notes='" . $this->escape_string($field3) . "' WHERE id=" . intval($id);
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THfilters_table . " SET filterfrom='" .
				$this->escape_string($field1) . "', filterto='" . $this->escape_string($field2) . "', notes='" . $this->escape_string($field3) . "' WHERE id=" . intval($id);
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
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
		switch ($type)
		{
			case 1 : // Blotter posts
				$query = "DELETE FROM " . THblotter_table . " WHERE id=" . intval($id);
				break;

			case 2 : // Capcodes
				$query = "DELETE FROM " . THcapcodes_table . " WHERE id=" . intval($id);
				break;

			case 3 : // Wordfilters
				$query = "DELETE FROM " . THfilters_table . " WHERE id=" . intval($id);
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
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
		switch ($type)
		{
			case 1 : // Blotter posts
				$query = "SELECT * FROM " . THblotter_table;
				break;

			case 2 : // Capcodes
				$query = "SELECT * FROM " . THcapcodes_table;
				break;

			case 3 : // Wordfilters
				$query = "SELECT * FROM " . THfilters_table;
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		return $this->mymultiarray($query);
	}

} //class ThornModDBI
?>