<?php
	require_once("config.php");
	require_once("common.php");
	checkadmin();
	function dumptable($table)
	{
		/*
			Dear image board internet family,
			This function will provide means for users to back up their databases without relying on phpmyadmin.
			Overall, I think this is a very bad idea and will provide more of a security issue than a useful service,
			but who am i to judge what people want.  I should point out that since I will not be including this
			code in the installation on konamichan, I cannot be held responsible for helping you use this, nor can
			I be blamed if your database is dumped, your passwords changed, and your image board installation deleted.

			This is meant as a service to you, not as code I care about using for my own personal benefit.

							Love,
							Dr. S.W. tyam, III, Esq., the Boss of all Space Ships
							
			what he said
							Sincerely,
							Rev. ordog

		*/

		//Since the dbi we're using doesn't know what to do with these commands, and I see no reason to add them just for this one function, let's just bypass ThornDBI entirely here.
		$link = mysql_connect( THdbserver, THdbuser, THdbpass)  or  die( "Unable  to  connect  to  SQL  server.  >:["); 
		mysql_select_db(THdbbase, $link)  or  die ( "Unable  to  select  database.  >:["); 
		$result  =  mysql_query( "select * from ".$table);
		if(mysql_num_rows($result)==0) { return; }
		echo "<b>INSERT</b> INTO `".$table. "` (";
		$fields = "";  //I'm cheating here because this comma separation is dumb
		$values = "";  //ditto
		while ($field=mysql_fetch_field($result)) 
		{
			$fields .= "`$field->name`, ";
		}
		echo substr($fields, 0, strlen($fields)-2);
		echo ") <b>VALUES</b> ";
		while ($row  =  mysql_fetch_row($result))
		{ 
			$values.= "<br>\n(";
			for  ($i=0;  $i<mysql_num_fields($result);  $i++)
			{ 
				if($i!=mysql_num_fields($result)-1)//is this the last column?
				{
					if(isset($row[$i])) { $values .= "'$row[$i]', "; } else { $values .= "NULL, "; }
				} else {
					if(isset($row[$i])) { $values .= "'$row[$i]'";  } else { $values .= "NULL"; }
				}
			}
			$values.="), ";
		}
		echo substr($values, 0, strlen($values)-2).";";  //what the hell
		echo "<br><br>\n";
		return;
	}//dumptable

	switch ($_GET['table'])
	{
		case "bans":
			dumptable(THbans_table);
			break;
		case "blotter":
			dumptable(THblotter_table);
			break;
		case "boards":
			dumptable(THboards_table);
			break;
		case "capcodes":
			dumptable(THcapcodes_table);
			break;
		case "extra":
			dumptable(THextrainfo_table);
			break;
		case "filters":
			dumptable(THfilters_table);
			break;
		case "images":
			dumptable(THimages_table);
			break;
		case "replies":
			dumptable(THreplies_table);
			break;
		case "threads":
			dumptable(THthreads_table);
			break;
		case "users":
			dumptable(THusers_table);
			break;
		case "all":   //ugh what are they doing
			dumptable(THbans_table);
			dumptable(THblotter_table);
			dumptable(THboards_table);
			dumptable(THcapcodes_table);
			dumptable(THextrainfo_table);
			dumptable(THfilters_table);
			dumptable(THimages_table);
			dumptable(THreplies_table);
			dumptable(THthreads_table);
			dumptable(THusers_table);
			break;
	}
	echo "<a href=".THurl."admin.php?a=hk>Return to housekeeping page</a>";


?>
