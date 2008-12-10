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

define("THdblitefn", sqlite_open(THpath . "unlinked/drydock.sqlite", 0666, $sqliteerror));
require_once ("config.php");
require_once ("common.php");
require_once ("ABSTRACT-dbi.php"); // abstract interface
define("DDDEBUG",1);


class ThornDBI implements absThornDBI
{
	function ThornDBI()
	{
		if (isset ($this->cxn) == false)
		{
			$this->cxn = THdblitefn or THdie($sqliteerror);
		}
	}
	
	/*  suggested by Mell03d0ut from anonib - edited by us to add new ideas */
	function escape_string($call)
	{
		if(DDDEBUG==1) { echo "0: $call<br>"; }
		$call = htmlspecialchars($call);
		if(DDDEBUG==1) { echo "1: $call<br>"; }
		if (get_magic_quotes_gpc() == 0)
		{
			$call = sqlite_escape_string($call);
			if(DDDEBUG==1) { echo "2: $call<br>"; }
		}
		$call = trim($call);
		if(DDDEBUG==1) { echo "3: $call<br>"; }
		$call = str_replace("\'", "&#039;", $call);
		if(DDDEBUG==1) { echo "4: $call<br>"; }
		$call = str_replace("\&quot;", "&#034;", $call);
		if(DDDEBUG==1) { echo "5: $call<br>"; }
		return ($call);
	}
	
	/*  provided by Mell03d0ut from anonib */
	function clean($call)
	{
		$call = escape_string($call);
		$call = trim($call);
		return ($call);
	}	
	
	function lastid()
	{
		sqlite_last_insert_rowid( THdblitefn );
	}
	
	function getvisibleboards()
	{
		/*
			Retrieve an array of assoc-arrays for all visible boards
												
			Returns:
				An array of assoc-arrays
		*/
		
		return $this->mymultiarray("SELECT * FROM " . THboards_table . " WHERE hidden != 1 order by folder asc");
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
		return ($this->myassoc("select * from " . THboards_table . " where folder='" . intval($b) ."'"));
	}
	
	function myassoc($call)
	{
		if(DDDEBUG==1) { echo ("myassoc: " . $call . "<br />"); } 
		$pup = sqlite_query(THdblitefn, $call);
		$dog = sqlite_fetch_array($pup, SQLITE_ASSOC); // or return null;
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}
	//in mysql this is the same as above but sometimes sqlite craps itself and i don't want to work on it anymore
	function myarray($call)
	{
		if(DDDEBUG==1) { echo ("myarray: " . $call . "<br />"); }
		$manta = sqlite_fetch_array($call, SQLITE_ASSOC); // or return null;
		if ($manta === false)
		{
			return (null);
		}
		return ($manta);
	}

	function myresult($call)
	{
		if(DDDEBUG==1) { echo ("myresult: " . $call . "<br />"); }
		$dog = sqlite_query(THdblitefn, $call);
		if ($dog === false || sqlite_num_rows($dog) == 0)
		{
			return (null);
		}
		return (sqlite_fetch_single($dog, 0));
	}

	function myquery($call)
	{
		if(DDDEBUG==1) { echo ("myquery: " . $call . "<br />"); }
		$dog = sqlite_query(THdblitefn, $call); // or die(mysql_error()."<br />".$call);
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}

	function mymultiarray($call)
	{
		if(DDDEBUG==1) { echo ("mymultiarray: " . $call . "<br />"); } 
		/*
		Encapsulate executing a query and iteratively calling myarray on the result.
		
		Parameters:
			string call
		The SQL query to execute
		
		Returns:
			An array of associative arrays (can be size 0)
		*/

		$multi = array ();

		$queryresult = $this->myquery($call);
		if ($queryresult != null)
		{
			while ($entry = sqlite_fetch_array($queryresult))
			{
				$multi[] = $entry;
			}
		}
		return $multi;
	}

	function timecount($start, $end)
	{
		//Returns the number of threads between two specified times.
		if (isset ($this->binfo))
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $this->binfo['id'] . " and time>=" . $start . " and time<=" . $end));
		}
		else
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where time>=" . $start . " and time<=" . $end));
		}
	}

	function gettimessince($since)
	{
		//Returns the times of all threads since $since.
		if (isset ($this->binfo))
		{
			//echo "Binfo";
			//Will there be cases where this will be called without binfo being set?
			if ($since != null)
			{
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id'] . " and time>=" . $since);
			}
			else
			{
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id']);
			}
		}
		else
		{
			//echo "No binfo";
			if ($since != null)
			{
				$yay = $this->myresult("select time from " . THthreads_table . " where time>=" . $since);
			}
			else
			{
				$yay = $this->myresult("select time from " . THthreads_table);
			}
		}
		//array($wows);
		$wows = array ();
		echo "Row count: " . sqlite_num_rows($yay);
		while ($row = sqlite_current($yay)) //help
		{
			//var_dump($row);
			$wows[] = (int) $row[0];
		}
		return ($wows);
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
		if ($imgidx == 0 || $imgidx == null)
		{
			return (array ());
		}
		$imgs = array ();
		$turtle = $this->myquery("select * from " . THimages_table . " where id=" . $this->escape_string($imgidx));
		while ($img = sqlite_fetch_array($turtle)) //help
		{
			$imgs[] = $img;
		}
		return ($imgs);
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
		$entries = array ();
		$count = 0;
		$blotter = $this->myquery("select * from " . THblotter_table . " ORDER BY time ASC");
		while ($entry = sqlite_fetch_array($blotter)) //help
		{
			if ($entry['board'] == "0" || is_in_csl($board, $entry['board']))
			{
				$entries[] = $entry;
				$count++;
			}

			if ($count >= 5)
			{
				break;
			}
		}
		return ($entries);
	}

	function getindex($p, & $sm)
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
		if (isset ($p['full']) == false)
		{
			$p['full'] = false;
		}
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "id";
		}
		if (isset ($p['desc']) == false)
		{
			$p['desc'] = false;
		}

		if ($p['full'])
		{
			$q = "select * from " . THboards_table;
		}
		else
		{
			$q = "select id, name, about from " . THboards_table;
		}

		if ($p['sortmethod'] = "id")
		{
			$q .= " order by id";
		}
		elseif ($p['sortmethod'] = "last")
		{
			$q .= " order by lasttime";
		}
		elseif ($p['sortmethod'] = "name")
		{
			$q .= " order by name";
		}

		if ($p['desc'])
		{
			$q .= " desc";
		}
		$iguana = $this->myquery($q);
		$boards = array ();
		while ($board = sqlite_fetch_array($iguana)) //help
		{
			$boards[] = $board;
		}
		return ($boards);
	}

	function checkban($ip = null)
	{
		
		/*
			Check to see if an IP is banned. Will check both the actual IP and the IP's last
			two subnets.
			
			Parameters:
				int $ip
			The IP address.  If it comes in as an int, long2ip will be used.  If it comes in as a string,
			nothing will be done to it.  If it comes in as null, $_SERVER['REMOTE_ADDR'] will
			be used by default.
			
			Returns:
				bool $banned
		*/
		
		// If it's null
		if ($ip == null)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else if ( is_int($ip) ) // If it's an int
		{
			$ip = long2ip($ip);
		}
		
		// Break up into octets
		$octets = explode(".", $ip, 4);

		//Check already banned...
		if ($this->myresult("select count(*) from " . THbans_table . " where 
			ip_octet1=" . intval($octets[0]) . " 
			&& ip_octet2=" . intval($octets[1]) . " 
			&& (ip_octet3=" . intval($octets[2]) . " || ip_octet3 = -1 )
			&& (ip_octet4=" . intval($octets[3]) . " || ip_octet4 = -1 )
		") > 0)
		{
			return (true);
		}
		else
		{
			return (false);
		}
		
	}

	function getban($ip = null)
	{
		/*
			Get ban information for a particular IP.  If the ban is a warning, or if
			the ban has expired, the ban will additionally be moved out of the active bans table
			and into the ban history table.
			
			Parameters:
				int $ip
			The IP address.  If it comes in as an int, long2ip will be used.  If it comes in as a string,
			nothing will be done to it.  If it comes in as null, $_SERVER['REMOTE_ADDR'] will
			be used by default.
			
			Returns:
				An assoc-array
		*/
		
		// If it's null
		if ($ip == null)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else if ( is_int($ip) ) // If it's an int
		{
			$ip = long2ip($ip);
		}
		
		// Break up into octets
		$octets = explode(".", $ip, 4);

		//Retrieve the bans
		$bans = $this->mymultiarray("select * from `" . THbans_table . "` where 
			`ip_octet1`=" . intval($octets[0]) . " 
			&& `ip_octet2`=" . intval($octets[1]) . " 
			&& (`ip_octet3`=" . intval($octets[2]) . " || `ip_octet3` = -1 )
			&& (`ip_octet4`=" . intval($octets[3]) . " || `ip_octet4` = -1 )");

		// Move old bans to the ban history table
		foreach( $bans as $singleban )
		{
			if( $singleban['duration'] == 0 ) // Warning
			{
				// Move to ban history table
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
				unbaninfo='viewed'";
			
				$this->myquery($history);
				
				// Delete this ban from the active bans table
				$this->myquery("delete from ".THbans_table." where id=".intval($singleban['id']));
			}
			else if( $singleban['duration'] != -1 ) // May have expired, so we'll have to check
			{
				//we'll need to know the difference between the ban time and the duration for actually expiring the bans
				$offset = THtimeoffset*60;
				$now = time()+$offset;
				$banoffset = $singleban['duration']*3600; // convert to hours
				$expiremath = $banoffset+$singleban['bantime'];
			
				if($now>$expiremath) // It expired.
				{
					// Move to ban history table
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
					unbaninfo='expired'";
				
					$this->myquery($history);
					
					// Delete from active bans table
					$this->myquery("delete from ".THbans_table." where id=".intval($singleban['id']));
				} 
			}
			
		}
		
		return $bans;
	}

	
	function getboard($id = 0, $folder = "")
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

		$querystring = "select * from " . THboards_table . " where ";
		$id = intval($id); // Make it explicitly an integer

		if ($id == 0 and $folder == "") // No filtering at all
		{
			$querystring = $querystring . "1";
		}
		elseif ($id != 0 and $folder != "") // Filtering by both folder AND ID
		{
			$querystring = $querystring . "id=" . $id . " AND folder='" . $this->escape_string($folder) . "'";
		}
		elseif ($id != 0) // Filtering by only ID
		{
			$querystring = $querystring . "id=" . $id;
		}
		else // Filtering by only folder
		{
			$querystring = $querystring . "folder='" . $this->escape_string($folder) . "'";
		}
		
		return $this->mymultiarray($querystring);
	}
	
	function getboardname($number)
	{
		/*
			Get the folder name of a board from an ID
			Parameters:
				int id 
			The board ID
			
			Returns:
				The board folder, or null if it does not exist
		*/
		
		$boardquery = "SELECT folder FROM ".THboards_table." WHERE id =".intval($number);
		$name = $this->myresult($boardquery);
		if($name != null)
		{ 
			return $name;
		} 
		else 
		{ 
			return false;
		}
	}

	function getboardnumber($folder)
	{
		/*
			Get the ID of a board from an folder
			Parameters:
				string folder
			The board folder
			
			Returns:
				The board ID, or null if it does not exist
		*/
		
		$boardquery = "SELECT id FROM ".THboards_table." WHERE folder ='".$this->escape_string($folder)."'";
		$number = $this->myresult($boardquery);
		if($number != null)
		{ 
			return $number;
		} 
		else 
		{ 
			return false;
		}
	}
	
	function addexifdata($exif)
	{
		/*
			Insert metadata info into the extra info table, and return the ID
			
			Parameters:
				string exif
			The metadata to store
			
			Returns:
				The insert id, or 0 if it failed
		*/	
		
		$ex_inf_result = 
			$this->myquery("INSERT INTO ".THextrainfo_table." ( id, extra_info ) VALUES (NULL, '".$this->clean($exif)."')");
		
		if($ex_inf_result)
		{
			return $this->lastid();
		}
		else
		{
			return 0;
		}
	}

} //ThornDBI

//===========================================================================================

// This concludes the main body of ThornDBI- the following includes contain derived classes 
// which encapsulate the other (more specialized) functions required by various tasks

require_once ("SQLite-board.php"); //ThornBoardDBI
require_once ("SQLite-mod.php"); //ThornModDBI
require_once ("SQLite-post.php"); //ThornPostDBI
require_once ("SQLite-thread.php"); //ThornThreadDBI
require_once ("SQLite-profile.php"); // ThornProfileDBI
require_once ("SQLite-tools.php"); // ThornToolsDBI
?>
