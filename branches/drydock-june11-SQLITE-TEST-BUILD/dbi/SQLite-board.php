<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite-board.php
	Description:    Code for the ThornBoardDBI class, based upon the SQLite version of ThornDBI
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornBoardDBI extends ThornDBI
{
	function ThornBoardDBI($bored, $payj, $on = array ())
	{
		$this->ThornDBI();
		$this->page = $payj;
		$this->binfo = $this->myassoc("select * from " . THboards_table . " where id=" . $bored);
		$this->on = $on;
		$this->blotterentries = $this->getblotter($bored);
		//$this->st=$st;
		//$this->et=$et;
		var_dump($this->on);
		//die();
	}

	function getallthreads($p, & $sm)
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
		if (isset ($p['full']) == false)
		{
			$p['full'] = false;
		}
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "bump";
		}
		if (isset ($p['desc']) == false)
		{
			$p['desc'] = true;
		}
		if (isset ($p['date']) == false)
		{
			$p['date'] = false;
		}
		if (isset ($p['start']) == false)
		{
			$p['start'] = false;
		}
		if (isset ($p['end']) == false)
		{
			$p['end'] = false;
		}

		if ($p['full'] == true)
		{
			$q = "select * from " . THthreads_table . " where board=" . $this->binfo['id'];
		}
		else
		{
			$q = "select id, title, name, trip, link, time, pin, lawk, bump, globalid from " . THthreads_table . " where board=" . $this->binfo['id'];
		}

		if ($p['start'] != false)
		{
			$q .= " and time>=" . $p['start'];
		}
		if ($p['end'] != false)
		{
			$q .= " and time<" . $p['end'];
		}

		if (isset ($this->on['year']))
		{
			if (isset ($this->on['month']))
			{
				if (isset ($this->on['day']))
				{
					$q .= " and time>=" . mktime(0, 0, 0, $this->on['month'], $this->on['day'], $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'], $this->on['day'] + 1, $this->on['year']);
				}
				else
				{
					$q .= " and time>=" . mktime(0, 0, 0, $this->on['month'], 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'] + 1, 1, $this->on['year']);
				}
			}
			else
			{
				$q .= " and time>=" . mktime(0, 0, 0, 1, 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, 1, 1, $this->on['year'] + 1);
			}
		}

		if ($p['sortmethod'] == "bump")
		{
			$q .= " order by pin desc, bump";
		}
		elseif ($p['sortmethod'] == "time")
		{
			$q .= " order by pin desc, time";
		}
		elseif ($p['sortmethod'] == "title")
		{
			$q .= " order by pin desc, title";
		}
		elseif ($p['sortmethod'] == "id")
		{
			$q .= " order by pin desc, id";
		}

		if ($p['desc'])
		{
			$q .= " desc";
		}

		$rezs = $this->myquery($q);

		$threads = array ();
		while (@ $th = sqlite_fetch_array($rezs)) //help
		{
			unset ($th['ip']);
			if ($p['full'] == true)
			{
				$th['images'] = $this->getimgs($th['imgidx']);
			}
			if ($p['date'] == true)
			{
				$th['date'] = getdate($th['time']);
			}
			$threads[] = $th;
		}

		return ($threads);
	}

	function getsthreads($p, & $sm)
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
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "bump";
		}
		if (isset ($p['tdesc']) == false)
		{
			$p['tdesc'] = true;
		}
		if (isset ($p['rdesc']) == false)
		{
			$p['rdesc'] = false;
		}

		//What the frick is this crap?
		//var_dump($this->on);
		//var_dump($p);
		//die("here");
		$orderby = "";
		if ($p['sortmethod'] == "bump")
		{
			$orderby .= " order by pin desc, bump";
		}
		elseif ($p['sortmethod'] == "time")
		{
			$orderby .= " order by pin desc, time";
		}
		elseif ($p['sortmethod'] == "title")
		{
			$orderby .= " order by pin desc, title";
		}
		elseif ($p['sortmethod'] == "id")
		{
			$orderby .= " order by pin desc, id";
		}
		if ($p['tdesc'])
		{
			$orderby .= " desc";
		}
		//print_r($this->binfo);
		$orderby .= " limit " . ($this->page * $this->binfo['perpg']) . "," . $this->binfo['perpg'];
		$thatid = $this->binfo['id'];

		$frog = $this->myquery("select * from " . THthreads_table . " where board=" . $thatid . $orderby);
		$sthreads = array ();
		while ($th = sqlite_fetch_array($frog)) //help
		{
			//print_r($th); echo "<br />";
			unset ($th['ip']);
			$th['images'] = $this->getimgs($th['imgidx']);
			$debug = "select count(*) from " . THreplies_table . " where thread=" . $th['id'];
			//echo $debug."<hr>";
			$th['rcount'] = $this->myresult($debug);
			if ($th['rcount'] == 0 || $this->binfo['perth'] == 0)
			{
				$th['reps'] = null;
				$th['scount'] = 0;
			}
			else
			{
				$start = $th['rcount'] - $this->binfo['perth'];
				if ($start < 0)
				{
					$start = 0;
				}

				if ($p['rdesc'])
				{
					$orderby = " order by time desc limit " . $start . "," . $this->binfo['perth'];
				}
				else
				{
					$orderby = " order by time limit " . $start . "," . $this->binfo['perth'];
				}
				$toad = $this->myquery("select * from " . THreplies_table . " where thread=" . $th['id'] . $orderby);
				while ($reply = sqlite_fetch_array($toad)) //help
				{
					unset ($reply['ip']);
					$reply['images'] = $this->getimgs($reply['imgidx']);
					$th['reps'][] = $reply;
				}
				$th['scount'] = count($th['reps']);
			}
			var_dump($th);
			$sthreads[] = $th;
		}
		return ($sthreads);
	} //getsthreads

	function getvisibleboards()
	{
		/*
			Retrieve an array of assoc-arrays for all visible boards
												
			Returns:
				An array of assoc-arrays
		*/
		
		return $this->mymultiarray("SELECT * FROM " . THboards_table . " WHERE hidden != 1 order by folder asc");
	}


} //class ThornBoardDBI
?>