<?php
/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-tools.php
	Description:    Abstract interface for a ThornToolsDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * ThornToolsDBI is used for specific pages like recentposts.php
 * and recentpics.php.  If a particular tool needs a specialized kind
 * of database query, this might be an appropriate place to have
 * the function encapsulating that query.
 */
interface absThornToolsDBI
{
	/**
	 * TODO
	 */
	function getpicscount();

	/**
	 * Retrieve a post count based on the specified filtering parameters.
	 * 
	 * @param bool $get_threads True if threads are being retrieved (false is for replies)
	 * @param int $board The ID of the board, -1 to not filter by board
	 * @param bool $showhidden Include posts that have been hidden already
	 * 
	 * @return int The number of posts which fulfill this criteria
	 */
	function getpostscount($get_threads, $board, $showhidden);
	
	/**
	 * TODO
	 */
	function getpics($offset);
	
	/**
	 * Retrieve an array of assocs containing post data
	 * 
	 * @param int $offset The offset from which to start (for going through "pages")
	 * @param bool $get_threads True if threads are being retrieved (false is for replies)
	 * @param int $board The ID of the board, -1 to not filter by board
	 * @param bool $showhidden Include posts that have been hidden already
	 * 
	 * @return array An array of assoc-arrays containing post data - replies will 
	 * have an additional "thread_globalid" entry in each assoc
	 */
	function getposts($offset, $get_threads, $board, $showhidden);
	
	/**
	 * Retrieve the top 15 threads from the news board.
	 * 
	 * @return array An array of assoc-arrays containing thread data
	 */
	function getnewsthreads();
	
}
?>
