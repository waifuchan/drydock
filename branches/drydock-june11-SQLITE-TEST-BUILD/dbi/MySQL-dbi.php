<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-dbi.php
	Description:    Handles interface between database and board functions using a MySQL database.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

require_once ("config.php");
require_once ("common.php");



class ThornDBI
{
	function ThornDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		if (isset ($this->cxn) == false)
		{
			$this->cxn = mysql_connect($server, $user, $pass) or THdie("DBcxn");
			mysql_select_db($base, $this->cxn) or THdie("DBsel");
		}
	}
	function escape_string($string)
	{
		return (mysql_real_escape_string($string));
	}
	function lastid()
	{
		mysql_insert_id();
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
	/*  provided by Mell03d0ut from anonib */
	function clean($call)
	{
		$call = htmlspecialchars($call);
		if (get_magic_quotes_gpc() == 0)
		{
			$call = mysql_real_escape_string($call);
		}
		$call = trim($call);
		return ($call);
	}

	function myassoc($call)
	{
		echo("myassoc: $call<br />");
		$dog = @ mysql_fetch_assoc(mysql_query($call)); // or return null;
		if ($dog === false)
		{
			return (null);
		}
		var_dump($dog);echo"<br>";
		return ($dog);
	}

	function myarray($call)
	{
		echo("myarray: $call<br />");
		$manta = @ mysql_fetch_array(mysql_query($call)); // or return null;
		if ($manta === false)
		{
			return (null);
		}
		var_dump($manta);echo"<br>";
		return ($manta);
	}

	function myresult($call)
	{
		echo("myresult: $call<br />");
		$dog = mysql_query($call); // or die(mysql_error()."<br />".$call);
		if ($dog === false || mysql_num_rows($dog) == 0)
		{
			return (null);
		}
		var_dump(mysql_result($dog, 0));echo"<br>";
		return (mysql_result($dog, 0));
	}

	function myquery($call)
	{
		echo("myquery: $call<br />");
		$dog = mysql_query($call); // or die(mysql_error()."<br />".$call);
		if ($dog === false)
		{
			return (null);
		}
		var_dump($dog);echo"<br>";
		return ($dog);
	}

	function mymultiarray($call)
	{
		echo("mymultiarray: $call<br />");
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
			while ($entry = mysql_fetch_array($queryresult))
			{
				$multi[] = $entry;
			}
		}
		var_dump($multi);echo"<br>";
		return $multi;
	}

	function timecount($start, $end)
	{
		//Returns the number of threads between two specified times.
		if (isset ($this->binfo))
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $this->binfo['id'] . " && time>=" . $start . " && time<=" . $end));
		}
		else
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where time>=" . $start . " && time<=" . $end));
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
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id'] . " && time>=" . $since);
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
		echo "Row count: " . mysql_num_rows($yay);
		while ($row = mysql_fetch_row($yay))
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
		$turtle = "select * from " . THimages_table . " where id=" . $this->clean($imgidx);
/*
		while ($img = $this->myassoc($turtle))
		{
			$imgs[] = $img;
		}
*/

		$imgs[] = $this->myassoc($turtle);
		echo"<b>";var_dump($imgs);echo"</b>";
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
		while ($entry = mysql_fetch_assoc($blotter))
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
		while ($board = mysql_fetch_assoc($iguana))
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
		if ($this->myresult("select count(*) from `" . THbans_table . "` where 
			`ip_octet1`=" . intval($octets[0]) . " 
			&& `ip_octet2`=" . intval($octets[1]) . " 
			&& (`ip_octet3`=" . intval($octets[2]) . " || `ip_octet3` = -1 )
			&& (`ip_octet4`=" . intval($octets[3]) . " || `ip_octet4` = -1 )
			") > 0)
		{
			return (true);
		}
		else
		{
			return (false);
		}
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
			$querystring = $querystring . "id=" . $id . " AND folder='" . $this->clean($folder) . "'";
		}
		elseif ($id != 0) // Filtering by only ID
		{
			$querystring = $querystring . "id=" . $id;
		}
		else // Filtering by only folder
		{
			$querystring = $querystring . "folder='" . $this->clean($folder) . "'";
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

} //ThornDBI

//===========================================================================================

// This concludes the main body of ThornDBI- the following includes contain derived classes 
// which encapsulate the other (more specialized) functions required by various tasks

require_once ("MySQL-board.php"); //ThornBoardDBI
require_once ("MySQL-mod.php"); //ThornModDBI
require_once ("MySQL-post.php"); //ThornPostDBI
require_once ("MySQL-thread.php"); //ThornThreadDBI
require_once ("MySQL-profile.php"); // ThornProfileDBI
?>
