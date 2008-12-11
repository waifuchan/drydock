<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite-profile.php
	Description:    Code for the ThornProfileDBI class, based upon the SQLite version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-profile.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornProfileDBI extends ThornDBI
{

	function ThornProfileDBI()
	{
		$this->ThornDBI();
	}

	function getuserdata_login($username, $password)
	{
		$query = "SELECT * FROM " . THusers_table . " WHERE username='" . $this->escape_string($username) .
		"' AND password='" . $this->escape_string(md5(THsecret_salt . $password)) . "' AND approved=1";

		return $this->myassoc($query);
	}
	
	function getuserdata_cookielogin($username, $id)
	{
		return $this->myassoc("SELECT * FROM ".THusers_table." WHERE username='".$this->escape_string($username).
				"' AND userid='".$this->escape_string($id)."' AND approved = 1");
	}

	function getuserdata($username)
	{
		return $this->myassoc("SELECT * FROM " . THusers_table . " WHERE username='" . $this->escape_string($username) . "'");
	}

	function updateuser($username, $id)
	{
		$this->myquery("UPDATE " . THusers_table . " SET userid='" . $this->escape_string($id) . "', timestamp=" . time() .
		"WHERE username='" . $this->escape_string($username) . "'");
	}

	function getuserlist()
	{
		return $this->mymultiarray("SELECT * FROM " . THusers_table);
	}

	function getusercapcode($capcode)
	{
		return $this->myresult("SELECT capcodeto FROM " . THcapcodes_table . " WHERE capcodefrom='" . $this->escape_string($capcode) . "'");
	}
	
	function getuserimage($username)
	{
		return $this->myresult("SELECT has_picture FROM " .	THusers_table . " WHERE username='" . $this->escape_string($username) . "'");
	}

	function registeruser($username, $password, $userlevel, $email, $approved)
	{
		return $this->myquery(
				"INSERT INTO " . THusers_table . 
				"(username, password, userlevel, email, approved) VALUES ('" .
				$this->escape_string($username) . "','" . 
				$this->escape_string( md5( THsecret_salt . $password) ). "'," . 
				intval($userlevel) . ",'" . 
				$this->escape_string($email) . "',".
				intval($approved).")"
			);
	}

	function updateuserinfo($username, $age, $gender, $location, $contact, $description, $picture_ext, $picture_pending)
	{
		$this->myquery(
			"UPDATE " . THusers_table . " SET ".
			"age = '".$this->escape_string($age)."',".
			"gender = '".$this->escape_string($gender)."',".
			"location = '".$this->escape_string($location)."',".
			"contact = '".$this->escape_string($contact)."',".
			"description = '".$this->escape_string($description)."',".
			"has_picture = '".$this->escape_string($picture_ext)."',".
			"pic_pending = '".$this->escape_string($picture_pending)."' ".
			"WHERE username='".$this->escape_string($username)."'"		
		);
	}

	function updateuserpermissions($username, $admin, $moderator, $userlevel, $boards, $capcode)
	{
		$admin = intval($admin); // make it explicit
		$moderator = intval($moderator);
		$userlevel = intval($userlevel);
		
		// If they have no capcode, strip out any capcode proposals
		if($capcode == "")
		{
			$this->myquery("UPDATE ".THusers_table." SET proposed_capcode='' WHERE username='".$this->escape_string($username) . "'");
		}
		
		$this->myquery(
			"UPDATE " . THusers_table . " SET ".
			"mod_admin = ".$admin.",".
			"mod_global = ".$moderator.",".
			"userlevel = ".$userlevel.",".
			"mod_array = '".$this->escape_string($boards)."',".
			"capcode = '".$this->escape_string($capcode)."' ".
			"WHERE username='".$this->escape_string($username)."'"
		);
	}

	function proposeusercapcode($username, $capcode)
	{
		// First 128 characters, if they want more they'll have to use the admin panel :]
		if (strlen($capcode) > 128)
			$capcode = substr($capcode, 0, 128);

		// This is here to prevent the remote possibility of someone proposing a capcode, and then in between the time the admin views the proposed capcodes page
		// and clicks the "Approve" link, someone changes it to something malicious.
		if (!$this->myresult("SELECT proposed_capcode FROM " . THusers_table .
			" WHERE username='" . $this->escape_string($username) . "'"))
		{
			$this->myquery("UPDATE " . THusers_table . " SET proposed_capcode='" . $this->escape_string($capcode) . "' WHERE username='" . $this->escape_string($username) . "'");
		}

	}

	function setuserpass($username, $password)
	{
		return $this->myquery("UPDATE " . THusers_table . " SET password='" . $this->escape_string(md5(THsecret_salt.$password)) .
		"' WHERE username='" . $this->escape_string($username) . "' AND mod_admin = 0");
	}

	function suspenduser($username)
	{
		$this->myquery("UPDATE " . THusers_table . " SET approved = '-2' WHERE username='" . $this->escape_string($username) . "'");
	}

	function userexists($username)
	{
		$count = $this->myresult("SELECT COUNT(*) FROM " . THusers_table . " WHERE username='" . $this->escape_string($username) . "'");

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
		$count = $this->myresult("SELECT COUNT(*) FROM " . THusers_table . " WHERE email='" . $this->escape_string(email) . "'");

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
		$userlevel = $this->myresult("SELECT userlevel FROM " . THusers_table . " WHERE username='" . $this->escape_string($username) . "'");
		if ($userlevel >= $_SESSION['userlevel'] || $userlevel == null)
		{
			return false;
		}
		return true;
	}

} //class ThornProfileDBI
?>
