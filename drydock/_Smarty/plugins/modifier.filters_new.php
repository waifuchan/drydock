<?php
// Here's something interesting.  If we declare these here, we make sure
// that $from and $to will always be defined as SOMETHING (avoiding possible
// issues if someone does something stupid like define register_globals, and has
// the include statement fail).  Since the include statement is inside of the function,
// the $from and $to in the cached file are the values that will be used because
// of variable scoping.
$from=array(); 
$to=array();
srand(time());
function checkspoilers($text)
{
	/*
	(11:19:34 PM) Ordog163: you need to make your quantifiers nongreedy
	(11:19:53 PM) Ordog163: because if you do
	(11:20:21 PM) Ordog163: [spoiler]blashaskdjhsdfkjsdf[/spoiler]
	this text shouldn't be under spoiler tags
	[spoiler]as.djhsdlksgafd[/spoiler]
	(11:20:26 PM) Ordog163: all of it will show up as spoiler text
	*/
	$search= '/\[spoiler](.*)\[\/spoiler]/iU';
	$replace='<span class="spoiler" onmouseover="this.style.color=\'#FFF\';" onmouseout="this.style.color=this.style.backgroundColor=\'#000\'">';
	return(preg_replace($search, $replace, $text));
}
function smarty_modifier_filters_new($filt)
{
	
	$filt=checkspoilers($filt);
/*
		WOOHOO WE'RE CACHED
	$db = new ThornDBI();
	$query = "SELECT * FROM ".THfilters_table;
	
	$to=array();
	$from=array();
	
    $queryresult=$db->myquery($query);
     while ($row_item=mysql_fetch_assoc($queryresult)) {
        $to[]=$row_item['filterto'];
		$from[]=$row_item['filterfrom'];
    }
*/
	include(THpath.'cache/filters.php');
	return nl2br(preg_replace($from, $to, $filt));

}
?>
