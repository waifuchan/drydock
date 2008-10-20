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

function escape_string($string)
{
	return (sqlite_escape_string($string));
}


class ThornDBI
{
	function ThornDBI()
	{
		if (isset ($this->cxn) == false)
		{
			$this->cxn = THdblitefn or THdie($sqliteerror);
		}
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

	/*  provided by Mell03d0ut from anonib */
	function clean($call)
	{
		$call = htmlspecialchars($call);
		if (get_magic_quotes_gpc() == 0)
		{
			$call = sqlite_escape_string($call);
		}
		$call = trim($call);
		return ($call);
	}

	function myassoc($call)
	{
		echo ("myassoc: " . $call . "<br />");
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
		echo ("myarray: " . $call . "<br />");
		$manta = sqlite_fetch_array($call, SQLITE_ASSOC); // or return null;
		if ($manta === false)
		{
			return (null);
		}
		return ($manta);
	}

	function myresult($call)
	{
		echo ("myresult: " . $call . "<br />");
		$dog = sqlite_query(THdblitefn, $call);
		if ($dog === false || sqlite_num_rows($dog) == 0)
		{
			return (null);
		}
		return (sqlite_fetch_single($dog, 0));
	}

	function myquery($call)
	{
		echo ("myquery: " . $call . "<br />");
		$dog = sqlite_query(THdblitefn, $call); // or die(mysql_error()."<br />".$call);
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
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
		$turtle = $this->myquery("select * from " . THimages_table . " where id=" . $this->clean($imgidx));
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
			Check to see if an IP is banned. Will check both the actual IP and the IP's subnet.
			Parameters:
				int $ip=ip2long($_SERVER['REMOTE_ADDR']);
			The ip2long'd IP address. If blank, it checks the user's IP address. ("function checkban($ip=ip2long($_SERVER['REMOTE_ADDR']))" makes PHP cwy.)
				Returns: bool $banned
		*/
		if ($ip == null)
		{
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		//echo();
		$sub = ipsub($ip);
		//Check already banned...
		if ($this->myresult("select count(*) from " . THbans_table . " where (ip=" . $sub . " and subnet=1) or ip=" . $ip) > 0)
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

} //ThornDBI

//===========================================================================================

// This concludes the main body of ThornDBI- the following includes contain derived classes 
// which encapsulate the other (more specialized) functions required by various tasks

require_once ("SQLite-board.php"); //ThornBoardDBI
require_once ("SQLite-mod.php"); //ThornModDBI
require_once ("SQLite-post.php"); //ThornPostDBI
require_once ("SQLite-thread.php"); //ThornThreadDBI
require_once ("SQLite-profile.php"); // ThornProfileDBI
?>
