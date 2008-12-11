<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-mod.php
	Description:    Code for the ThornModDBI class, based upon the MySQL version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-mod.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornModDBI extends ThornDBI
{

	// This class should only be created if the user is a mod or admin.
	function ThornModDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		$this->ThornDBI($server, $user, $pass, $base);
	}

	function banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby)
	{
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
		
		$banquery = "insert into `".THbans_table."` 
		set ip_octet1=" . $octets[0] . ",
		ip_octet2=" . $octets[1] . ",
		ip_octet3=" . $octets[2] . ",
		ip_octet4=" . $octets[3] . ",
		privatereason='" . $this->clean($privatereason) . "', 
		publicreason='" . $this->clean($publicreason) . "', 
		adminreason='" . $this->clean($adminreason) . "', 
		postdata='" . $this->clean($postdata) . "', 
		duration='" . intval($duration) . "', 
		bantime=" . $when . ", 
		bannedby='" . $this->clean($bannedby) . "'";
		
		$this->myquery($banquery);
		
		return (true);
	}

	function banbody($id, $isthread, $publicbanreason = "USER HAS BEEN BANNED FOR THIS POST")
	{
		if ($publicbanreason)
		{
			$publicbanreason = '<br /><br /><b><span class=ban>(' . $publicbanreason . ')</span></b>';
		}
		else // return if we don't have one
		{
			return;
		}

		if ($isthread)
		{
			$thebody = $this->myresult("select body from " . THthreads_table . " where id=" . $id);
			//$thebody = escape_string($thebody);
			//$thebody.=' (USER HAS BEEN BANNED FOR THIS POST)';
			$thebody .= $publicbanreason;
			$updatequery = "update " . THthreads_table . " set body='" . $this->escape_string(nl2br($thebody)) . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		else
		{
			$thebody = $this->myresult("select body from " . THreplies_table . " where id=" . $id);
			//$thebody = escape_string($thebody);
			//$thebody.=' (USER HAS BEEN BANNED FOR THIS POST)';
			$thebody .= $publicbanreason;
			$updatequery = "update " . THreplies_table . " set body='" . escape_string(nl2br($thebody)) . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		return;
	}
	
	function banipfrompost($id, $isthread, $subnet, $privatereason, $publicreason, $adminreason, $duration, $bannedby)
	{
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
		$singleban = $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
		
		if( $singleban )
		{
			// Move to the ban history table
			$history = "insert into `".THbanhistory_table."` 
			set ip_octet1=" . $singleban['ip_octet1'] . ",
			ip_octet2=" . $singleban['ip_octet2'] . ",
			ip_octet3=" . $singleban['ip_octet3'] . ",
			ip_octet4=" . $singleban['ip_octet4'] . ",
			privatereason='" . $this->clean($singleban['privatereason']) . "', 
			publicreason='" . $this->clean($singleban['publicreason']) . "', 
			adminreason='" . $this->clean($singleban['adminreason']) . "', 
			postdata='" . $this->clean($singleban['postdata']) . "', 
			duration='" . $singleban['duration'] . "', 
			bantime=" . $singleban['bantime'] . ", 
			bannedby='" . $singleban['bannedby'] . "',
			unbaninfo='" . $this->clean($reason) . "'";
		
			$this->myquery($history);
		
			$this->myquery("delete from " . THbans_table . " where id=" . intval($id));
		}
	}
	
	function getbanfromid($id)
	{		
		return $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
	}
	
	function getiphistory($ip)
	{	
		// Break up into octets
		$octets = explode(".", long2ip($ip), 4);

		//Retrieve the bans
		$bans = $this->mymultiarray("select * from `" . THbanhistory_table . "` where 
			`ip_octet1`=" . intval($octets[0]) . " 
			&& `ip_octet2`=" . intval($octets[1]) . " 
			&& (`ip_octet3`=" . intval($octets[2]) . " || `ip_octet3` = -1 )
			&& (`ip_octet4`=" . intval($octets[3]) . " || `ip_octet4` = -1 )");
		
		return $bans;
	}

	function getallbans()
	{
		$baddies = array ();
		$baddies = $this->mymultiarray("select * from " . THbans_table);

		// Rendered unnecessary with our new ban format!
		// foreach ($baddies as $row)
		// {
			// $row['subnet'] = (bool) $row['subnet'];
		// }
		return ($baddies);
	}

	function delpost($id, $isthread)
	{
		if ($isthread)
		{
			$rezs = $this->myquery("select distinct imgidx from " . THreplies_table . " where thread=" . $id . " && imgidx!=0");
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
			while ($rez = mysql_fetch_assoc($rezs))
			{
				$affimg[] = $rez['imgidx'];
			}
			$this->myquery("delete from " . THreplies_table . " where thread=" . $id);
			$this->myquery("delete from " . THthreads_table . " where id=" . $id);
			if (count($affimg) > 0)
			{
				$EXIFquery = $this->myquery("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimg) . ")"); // Remove extra_info sections
				while ($exif = mysql_fetch_assoc($EXIFquery))
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

				$extra_info_entries = $this->myquery("select extra_info from " . THimages_table . " where id=" . $duck); // remove extra_info sections
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
		if ($delsub)
		{
			$sub = ipsub($ip);
			$submax = $sub +255;
			$q1 = $this->myquery("select distinct imgidx from " . THreplies_table . " where ip between " . $sub . " and " . $submax . " && imgidx!=0");
			$q2 = $this->myquery("select distinct imgidx from " . THthreads_table . " where ip between " . $sub . " and " . $submax . " && imgidx!=0");
			$q3 = $this->myquery("select id, board from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
			$this->myquery("delete from " . THreplies_table . " where ip between " . $sub . " and " . $submax);
			$this->myquery("delete from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
		}
		else
		{
			//echo($ip);
			$q1 = $this->myquery("select distinct imgidx from " . THreplies_table . " where ip=" . $ip . " && imgidx!=0");
			$q2 = $this->myquery("select distinct imgidx from " . THthreads_table . " where ip=" . $ip . " && imgidx!=0");
			$q3 = $this->myquery("select id, board from " . THthreads_table . " where ip=" . $ip);
			$this->myquery("delete from " . THreplies_table . " where ip=" . $ip);
			$this->myquery("delete from " . THthreads_table . " where ip=" . $ip);
		}

		$affimgs = array ();
		while ($rez = mysql_fetch_assoc($q1))
		{
			$affimgs[] = $rez['imgidx'];
		}
		while ($rez = mysql_fetch_assoc($q2))
		{
			$affimgs[] = $rez['imgidx'];
		}
		$affthreads = array ();
		while ($rez = mysql_fetch_assoc($q3))
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
		while ($rez = mysql_fetch_assoc($q4))
		{
			$affimgs[] = $rez['imgidx'];
		}
		if (count($affimgs) > 0)
		{
			$EXIFquery = $this->myquery("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimgs) . ")"); // Remove extra_info sections
			while ($exif = mysql_fetch_assoc($EXIFquery))
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
			$query = "insert into " . THboards_table . " set id=" . $board['id'] . ", globalid=" . $board['globalid'] . ", name='" . $this->clean($board['name']) . "', folder='" . $this->clean($board['folder']) . "', about='" . $this->clean($board['about']) . "', rules='" . $board['rules'] . "', perpg=" . $board['perpg'] . ", perth=" . $board['perth'] . ", hidden=" . $board['hidden'] . ", forced_anon=" . $board['forced_anon'] . ", tlock=" . $board['tlock'] . ", rlock=" . $board['rlock'] . ", tpix=" . $board['tpix'] . ", rpix=" . $board['rpix'] . ", tmax=" . $board['tmax'];
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
		$imgidxes = array ();
		$hare = $this->myquery("select distinct imgidx from " . THthreads_table . " where board=" . $board . " && imgidx!=0");
		while ($xyz = mysql_fetch_assoc($hare))
		{
			$imgidxes[] = $xyz['imgidx'];
		}
		$hare = $this->myquery("select distinct imgidx from " . THreplies_table . " where board=" . $board . " && imgidx!=0");
		while ($xyz = mysql_fetch_assoc($hare))
		{
			$imgidxes[] = $xyz['imgidx'];
		}
		$this->myquery("delete from " . THthreads_table . " where board=" . $board);
		$this->myquery("delete from " . THreplies_table . " where board=" . $board);
		if (count($imgidxes) != 0)
		{
			$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $imgidxes) . ")");
		}
		smclearcache($board, -1, -1, true); // clear EVERYTHING in the cache associated with this board
		return ($imgidxes);
	}

	function insertBCW($type = -1, $field1 = "", $field2 = "", $field3 = "")
	{
		$type = intval($type);
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'INSERT INTO ' . THblotter_table . ' ( entry, board, time ) VALUES ("' .
				$this->clean($field1) . '","' . intval($field2) . '","' . (THtimeoffset * 60) + time() . '")';
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THcapcodes_table . ' ( capcodefrom, capcodeto, notes ) VALUES ("' .
				$this->clean($field1) . '","' . $this->clean($field2) . '","' . $this->clean($field3) . '");';
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THfilters_table . ' ( filterfrom, filterto, notes ) VALUES ("' .
				$this->clean($field1) . '","' . $this->clean($field2) . '","' . $this->clean($field3) . '");';
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
		return mysql_insert_id($this->cxn); // Return the insertion ID.
	}

	function updateBCW($type = -1, $id, $field1 = "", $field2 = "", $field3 = "")
	{
		$type = intval($type);

		// It is assumed that all of these will have some sort of ID by which to identify what to update, therefore it is not listed in the FIELD comments.
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'UPDATE ' . THblotter_table . " SET entry = '" . $this->clean($field1) . "', board=" . intval($field2) . " WHERE id=" . intval($id);
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THcapcodes_table . " SET capcodefrom='" .
				$this->clean($field1) . "', capcodeto='" . $this->clean($field2) . "', notes='" . $this->clean($field3) . "' WHERE id=" . intval($id);
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THfilters_table . " SET filterfrom='" .
				$this->clean($field1) . "', filterto='" . $this->clean($field2) . "', notes='" . $this->clean($field3) . "' WHERE id=" . intval($id);
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
	}

	function deleteBCW($type = -1, $id)
	{
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
