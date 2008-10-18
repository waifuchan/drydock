<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-profile.php
	Description:    Code for the ThornProfileDBI class, based upon the MySQL version of ThornDBI
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornProfileDBI extends ThornDBI
{

	function ThornProfileDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		$this->ThornDBI($server, $user, $pass, $base);
	}

	function getuserdata_login($username, $password)
	{
		/*
			Retrieve an assoc containing the userdata for a given user/pass combination (used for logins)
			Parameters:
				string username 
			The username of the (approved) profile to retrieve
				string password
			The corresponding password (not salted yet)
			
			Returns:
				array containing user info (if name and password are valid)
		*/

		$query = "SELECT * FROM " . THusers_table . " WHERE username='" . $this->clean($username) .
		"' AND password='" . escape_string(md5(THsecret_salt . $password)) . "' AND approved=1";

		return $this->myassoc($query);
	}
	
	function getuserdata_cookielogin($username, $id)
	{
		/*
			Retrieve an assoc containing the userdata for a given user/id combination (used for persistent logins)
			Parameters:
				string username 
			The username of the (approved) profile to retrieve
				string id
			The corresponding user ID
			
			Returns:
				array containing user info (if name and id are valid)
		*/		
		
		return $this->myassoc("SELECT * FROM ".THusers_table." WHERE username='".escape_string($username).
				"' AND userid='".escape_string($id)."' AND approved = 1");
	}

	function getuserdata($username)
	{
		/*
			Retrieve an assoc containing the userdata for a given user
			Parameters:
				string username 
			The username of the (approved) profile to retrieve
			
			Returns:
				array containing user info (if name is valid)
		*/

		return $this>myarray("SELECT * FROM " . THusers_table . " WHERE username='" . escape_string($username) . "'");
	}

	function updateuser($username, $id)
	{
		/*
			Update a profile to have a new ID and timestamp
			Parameters:
				string username 
			The username of the profile to update
				string id
			The new ID value
			
			Returns:
				Nothing
		*/
		$this->myquery("UPDATE " . THusers_table . " SET userid=''" . $this->clean($id) . "\", timestamp=" . time() .
		"WHERE username=''" . $this->clean($username) . "''");
	}

	function getuserlist()
	{
		/*
			Retrieve a list of all the users
						
			Returns:
				An array of assoc-arrays 
		*/

		return $this->mymultiarray("SELECT * FROM " . THusers_table);
	}

	function getusercapcode($capcode)
	{
		/*
			Retrieve a capcodeto based on an incoming capcodefrom
						
			Parameters:
				string capcode
			The capcodefrom associated with the capcodeto
						
			Returns:
				A string
		*/

		return $this->myresult("SELECT capcodeto FROM " . THcapcodes_table . " WHERE capcodefrom='" . escape_string($capcode) . "'");
	}
	
	function getuserimage($username)
	{
		/*
			Retrieves the extension associated with a user's profile picture, if any
						
			Parameters:
				string username
			The username of the target profile
						
			Returns:
				A string
		*/
		
		return $this->myresult("SELECT has_picture FROM " .	THusers_table . " WHERE username='" . escape_string($username) . "'");
	}

	function registeruser($username, $password, $userlevel, $email, $approved)
	{
		/*
			Register a new user into the system
						
			Parameters:
				string username
			The username of the target profile
				string password
			The (unsalted) password for the new user
				int userlevel
			The access level for the new user
				string email
			The email address of the new user
				int approved
			The initial approval status of the new user
						
			Returns:
				The result of the myquery call
		*/
		
		return $this->myquery(
				"INSERT INTO " . THusers_table . 
				"(username, password, userlevel, email, approved) VALUES ('" .
				escape_string($username) . "','" . 
				escape_string(THsecret_salt.$password) . "'," . 
				intval($userlevel) . ",'" . 
				escape_string($email) . "',".
				intval($approved).")"
			);
	}

	function updateuserinfo($username, $age, $gender, $location, $contact, $description, $picture_ext, $picture_pending)
	{
		/*
			Updates a given user's information
						
			Parameters:
				string username
			The username of the target profile
				string age
			The new age
				string gender
			The new gender
				string location
			The new location
				string contact
			The new contact information
				string description
			The new description
				string picture_ext
			The extension of the user's profile picture
				string picture_pending
			The extension of the currently proposed (but not approved) profile picture
						
			Returns:
				Nothing
		*/
		
		$this->myquery(
			"UPDATE " . THusers_table . " SET ".
			"age = '".escape_string($age)."',".
			"gender = '".escape_string($gender)."',".
			"location = '".escape_string($location)."',".
			"contact = '".escape_string($contact)."',".
			"description = '".escape_string($description)."',".
			"has_picture = '".escape_string($picture_ext)."',".
			"pic_pending = '".escape_string($picture_pending)."' ".
			"WHERE username='".escape_string($username)."'"		
		);
	}

	function updateuserpermissions($username, $admin, $moderator, $userlevel, $boards, $capcode)
	{
		/*
			Updates a given user's permissions
						
			Parameters:
				string username
			The username of the target profile
				int admin
			The user's administrator status
				int moderator
			The user's global moderator status
				int userlevel
			The user's access level
				string boards
			The boards a (per-board) moderator has access to
				string capcode
			The capcode hash associated with this user

						
			Returns:
				Nothing
		*/
		
		$admin = intval($admin); // make it explicit
		$moderator = intval($moderator);
		$userlevel = intval($userlevel);
		
		// If they have no capcode, strip out any capcode proposals
		if($capcode == "")
		{
			$this->myquery("UPDATE ".THusers_table." SET proposed_capcode='' WHERE username='".escape_string($username) . "'");
		}
		
		$this->myquery(
			"UPDATE " . THusers_table . " SET ".
			"mod_admin = ".$admin.",".
			"mod_global = ".$moderator.",".
			"userlevel = ".$userlevel.",".
			"mod_array = '".escape_string($boards)."',".
			"capcode = '".escape_string($capcode)."' ".
			"WHERE username='".escape_string($username)."'"
		);
	}

	function proposeusercapcode($username, $capcode)
	{
		/*
			Propose a new capcodeto for a given user
						
			Parameters:
				string username
			The username of the profile requesting a new capcode
				string capcode
			The new proposed capcodeto
						
			Returns:
				Nothing
		*/

		// First 128 characters, if they want more they'll have to use the admin panel :]
		if (strlen($capcode) > 128)
			$capcode = substr($capcode, 0, 128);

		// This is here to prevent the remote possibility of someone proposing a capcode, and then in between the time the admin views the proposed capcodes page
		// and clicks the "Approve" link, someone changes it to something malicious.
		if (!$this->myresult("SELECT proposed_capcode FROM " . THusers_table .
			" WHERE username='" . escape_string($username) . "'"))
		{
			$this->myquery("UPDATE " . THusers_table . " SET proposed_capcode='" . escape_string($capcode) . "' WHERE username='" . escape_string($username) . "'");
		}

	}

	function setuserpass($username, $password)
	{
		/*
			Reset a (non-admin) user's password
						
			Parameters:
				string username
			The username whose password will be reset
				string password
			The (unsalted) password
						
			Returns:
				Nothing
		*/
		
		return $this->myquery("UPDATE " . THusers_table . " SET password='" . escape_string(md5(THsecret_salt.$password)) .
		"' WHERE username='" . escape_string($username) . "' AND mod_admin = 0");
	}

	function suspenduser($username)
	{
		/*
			Suspend a user's account.
						
			Parameters:
				string username
			The username to suspend
						
			Returns:
				Nothing
		*/

		$this>myquery("UPDATE " . THusers_table . " SET approved = '-2' WHERE username='" . escape_string($username) . "'");
	}

	function userexists($username)
	{
		/*
			Check whether a user exists or not.
						
			Parameters:
				string username
			The username to check
						
			Returns:
				A boolean
		*/

		$count = $this->myresult("SELECT COUNT(*) FROM " . THusers_table . " WHERE username='" . escape_string($username) . "'");

		if ($count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function emailexists($email)
	{
		/*
			Check whether a particular email has already been associated with an account
						
			Parameters:
				string email
			The address to check
						
			Returns:
				A boolean
		*/

		$count = $this->myresult("SELECT COUNT(*) FROM " . THusers_table . " WHERE email='" . escape_string(email) . "'");

		if ($count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function caneditprofile($username)
	{
		/*
			Check whether the currently logged in user can edit a given user's profile
						
			Parameters:
				string username
			The username associated with the profile to check
						
			Returns:
				A boolean
		*/

		if (!isset ($_SESSION['username'])) // Not logged in?  That means no.
		{
			return false;
		}
		if ($_SESSION['username'] == $username) // Trying to edit your own profile? OK.
		{
			return true;
		}
		if (!$_SESSION['admin']) // You can't get past here if you're not an admin.
		{
			return false;
		}

		// We assume $user is a valid username, so any functions should make that check beforehand
		$userlevel = $this->myresult("SELECT userlevel FROM " . THusers_table . " WHERE username='" . escape_string($username) . "'");
		if ($userlevel >= $_SESSION['userlevel'] || $userlevel == null)
		{
			return false;
		}
		return true;
	}

} //class ThornProfileDBI
?>
