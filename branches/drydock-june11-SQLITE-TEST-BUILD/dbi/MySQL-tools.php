<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-tools.php
	Description:    Code for the ThornToolsDBI class, based upon the MySQL version of ThornDBI.
	ThornToolsDBI is used for things such as recentposts.php and recentpics.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornToolsDBI extends ThornDBI
{

	function ThornToolsDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		$this->ThornDBI($server, $user, $pass, $base);
	}

	function getpicscount()
	{
		
	}
	
	function getpostscount($get_threads, $board, $showhidden)
	{
		/*
			Retrieve a post count based on the specified filtering parameters
			Parameters:
				bool get_threads
			True if threads are being retrieved
				int board
			The ID of the board, -1 to perform no filtering by board
				bool showhidden
			Include posts that have been hidden already
			
			Returns:
				An integer indicating the number of posts which fulfill this criteria
		*/

		
		$boardquery = "";
		
		// Handle board filtering first
		if( $board > -1 )
		{
			$boardquery = "board = ".intval($board);
		}
			
		// Handle hidden filtering
		if($showhidden == false)
		{
			if( $boardquery == "" )
			{
				$boardquery = "hidden = 0";
			}
			else
			{
				$boardquery .= ",hidden = 0";
			}
		}
			
		// No filtering at all, I suppose.
		if($boardquery == "")
		{
			$boardquery = "1";
		}
		
		if($get_threads == false)
		{
			$postquery = "SELECT COUNT(*) FROM ".THreplies_table." WHERE ".$boardquery;
		}
		else
		{
			$postquery = "SELECT COUNT(*) FROM ".THthreads_table." WHERE ".$boardquery;
		}
		
		return $this->myresult($postquery);		
	}
	
	function getpics()
	{
		
	}
	
	function getposts($offset, $get_threads, $board, $showhidden)
	{
		/*
			Retrieve an array of assocs containing post data
			Parameters:
				int offset 
			The offset from which to start (for paging through multiple threads)
				bool get_threads
			True if threads are being retrieved
				int board
			The ID of the board, -1 to perform no filtering by board
				bool showhidden
			Include posts that have been hidden already
			
			Returns:
				array of assoc-arrays containing post data - replies will have an additional "thread_globalid" entry
		*/

		
		$boardquery = "";
		
		// Handle board filtering first
		if( $board > -1 )
		{
			$boardquery = "board = ".intval($board);
		}
			
		// Handle hidden filtering
		if($showhidden == false)
		{
			if( $boardquery == "" )
			{
				$boardquery = "hidden = 0";
			}
			else
			{
				$boardquery .= ",hidden = 0";
			}
		}
			
		// No filtering at all, I suppose.
		if($boardquery == "")
		{
			$boardquery = "1";
		}
		
		if($get_threads == false)
		{
			$postquery = "SELECT ".THreplies_table.".*, ".THthreads_table.".globalid AS thread_globalid FROM ".THreplies_table.
			" LEFT OUTER JOIN ".THthreads_table." ON ".THreplies_table.".thread = ".THthreads_table.".id
			WHERE ".$boardquery." order by id asc LIMIT ".$offset.", 20";
		}
		else
		{
			$postquery = "SELECT * FROM ".THthreads_table." WHERE ".$boardquery." order by id asc LIMIT ".$offset.", 20";
		}
		
		return $this->mymultiarray($postquery);
	}

} //class ThornToolsDBI
?>
