<?php

/*
Plugin Name: FS-Pax Pirep
Plugin URI: http://www.federalproductions.com/studio/game-add-ons/fs-pax/
Description: Adds scripted PIREP display as a plugin to WordPress
Version: 2.0
Author: Ted Thompson
Author URI: http://www.federalproductions.com
*/


/*  Copyright 2008  Ted Thompson  (email : info@federalproductions.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function fedprod_addScript() {

$file = dirname(__FILE__) . '/fs-pax-pirep.php';
$file_url = plugin_dir_url($file);

?>
<!-- Start FS-PAX added code -->
	<script type="text/javascript" src="<?php echo $file_url ?>js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#pireplist').dataTable( {
			"bSort": false,
			"sPaginationType": "full_numbers"
			} );
		} );
	</script>
<!-- End FS-PAX added code -->
<?php
}

function fedprod_showfspflights($content)
	{
		$tag = '[fp-pirep-report]';
		$tagcheck = strpos($content, $tag);

// Check to see if the tag is present before we bother building a report...

		if ($tagcheck)
		{

		add_action('wp_footer','fedprod_addScript');

		$vascriptpath = get_option( 'FSPassengers VA Script Path' );
		//NEEDED STUFF USUALLY YOU WILL NOT NEED TO CHANGE THIS///////////////////////////////////
		define("FSP", 1);
		# setting.php contain MySQL database setting and other setting it contain also the FSP UNIT SETTING
		require($vascriptpath."/FsPadmin/setting.php");			
		# common.php do the connexion to MySQL the value $databaseconnexion is set to true if the connexion is okay
		require($vascriptpath."/FsPadmin/common.php");			
		// the value "$databaseconnexion" is set to true in common.php if the connexion is ok
		if($databaseconnexion==FALSE){echo "Error - unable to connect to mySQL database;";return;}
		///////////////////////////////////////////////////////////////////////////////////////////
		// THIS IS THE LIST TABLE OUTPUT //////////////////////////////////////////////////////////
		
		return str_replace($tag, build_flight_list($listhtml), $content);
		}
	return $content;
	}

/*******************************************************************************************************************/
//                                                                                                                   
// Those below are fonction to help you to deal with the value returned by FsP, as they contain already the unit     
// (ie: 1400 ft instead of 1400) you might not be able to do mathematical operation with them so those function      
// below will help you.                                                                                              
/*******************************************************************************************************************/

// this add two hours of FsP and return them in hour format also
// ( 12:30:45+02:05:06=14:35:51 for example)
function AddTime($Time1,$Time2)
{
	$timea=explode(":",$Time1);
	$timeb=explode(":",$Time2);
	$secondes=($timea[0]+$timeb[0])*3600;
	$secondes+=($timea[1]+$timeb[1])*60;
	$secondes+=$timea[2]+$timeb[2];
	$hours = floor($secondes / 3600);
	$minute = floor(($secondes - ($hours * 3600)) / 60);
	$secconde = $secondes - ($hours * 3600) - ($minute * 60);
	return sprintf("%02d:%02d:%02d", $hours, $minute, $secconde);
}

function build_flight_list($reporthtml)
{

	// EDIT THE TABLE DESIGN HERE 
	$ListStart	='<table id="pireplist" class="pireptable">';
	$TDTitStyle 	='<th class="pireptitle">';
	$TDListOdd	='<td class="pirepcell pirepodd"><div class="pireptext">';
	$TDListEven	='<td class="pirepcell pirepeven"><div class="pireptext">';
	$TRList		="<tr>";
	$ListStop	="</tbody>\n</table>\n";
	$ListSummary	='<div class="pirepsum">';
	//

	$linkattribs = stripslashes( get_option( 'FP_pirep_linkattribs' ) );
	$CompanyFlightTime="00:00:00";
	$TotalPassengers=0;
	$TotalCargo=0;
	$vascriptpath = get_bloginfo('url')."/".get_option( 'FSPassengers VA Script Path' );

	$TableTitle 	="<thead><tr>";
	if (get_option('FP_pirep_showcol1')) $TableTitle .="$TDTitStyle Id</th>";
	if (get_option('FP_pirep_showcol2')) $TableTitle .="$TDTitStyle Date</th>";
	if (get_option('FP_pirep_showcol3')) $TableTitle .="$TDTitStyle Airline</th>";
	if (get_option('FP_pirep_showcol4')) $TableTitle .="$TDTitStyle PIC</th>";
	if (get_option('FP_pirep_showcol5')) $TableTitle .="$TDTitStyle Dep</th>";
	if (get_option('FP_pirep_showcol6')) $TableTitle .="$TDTitStyle Arr</th>";
	if (get_option('FP_pirep_showcol7')) $TableTitle .="$TDTitStyle Pax</th>";
	if (get_option('FP_pirep_showcol8')) $TableTitle .="$TDTitStyle A/C</th>";
	if (get_option('FP_pirep_showcol9')) $TableTitle .="$TDTitStyle Block Time</th>";
	if (get_option('FP_pirep_showcol10')) $TableTitle .="$TDTitStyle Result</th>";
	$TableTitle .="</tr></thead>\n<tbody>\n";	

	$reporthtml = $ListStart.$TableTitle;

	if($query = "SELECT * FROM flights WHERE 1 ORDER BY id desc")

	$result=@mysql_query($query);if(!$result){echo "SQL Error - ".mysql_error();return;}
	$NrfFlights=mysql_num_rows($result);
	
	if($NrfFlights==0){echo "No flights to display";return;}
	
	$Line=0;
	$boardlength=0;

	//Build table rows - to omit columns, comment out with double slash

	while ($row = mysql_fetch_assoc($result)) //(($row = mysql_fetch_assoc($result)) && ($boardlength != 10)) 
	{
		$reporthtml .= $TRList;
		if (get_option('FP_pirep_showcol1'))
			{
			if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
			$reporthtml .= '<a href="'.$vascriptpath.'/FsPlistflight.php?action=va&amp;listflight='.$row[id].'" '.$linkattribs.'>'.$row["FlightId"].'</a>'."</div></td>";
			}
		if (get_option('FP_pirep_showcol2'))
			{
			if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
			$reporthtml .= $row["FlightDate"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol3'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["CompanyName"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol4'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["PilotName"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol5'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= substr($row["DepartureIcaoName"],0,4)."</div></td>";
			}
		if (get_option('FP_pirep_showcol6'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= substr($row["ArrivalIcaoName"],0,4)."</div></td>";
			}
		if (get_option('FP_pirep_showcol7'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["NbrPassengers"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol8'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["AircraftType"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol9'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["TotalBlockTime"]."</div></td>";
			}
		if (get_option('FP_pirep_showcol10'))
			{
		if($Line==0)$reporthtml .= $TDListOdd;else $reporthtml .= $TDListEven;
		$reporthtml .= $row["FlightResult"]."</div></td>";
			}

		$reporthtml .= "</tr>\n";
	
		$Line=!$Line;
		$boardlength+=1;

		settype($row["CargoWeight"],"integer");
		$TotalCargo+=$row["CargoWeight"];
		$TotalPassengers+=$row["NbrPassengers"];
		$CompanyFlightTime=AddTime($row["TotalBlockTime"],$CompanyFlightTime);

	}
$reporthtml .= $ListStop;

$reporthtml .= "<br />".$ListSummary."Total Flight made: $NrfFlights<br />total flight time: $CompanyFlightTime<br />Total passengers carried: $TotalPassengers<br />Total Cargo: $TotalCargo kg</div>";

return $reporthtml;
}

// Plugin Options Page

function fp_pirep_admin_options() {
    // variables for the field and option names 
	$opt_name 	= 'FSPassengers VA Script Path';
	$opt_name1 	= 'FP_pirep_showcol1';
	$opt_name2 	= 'FP_pirep_showcol2';
	$opt_name3 	= 'FP_pirep_showcol3';
	$opt_name4 	= 'FP_pirep_showcol4';
	$opt_name5 	= 'FP_pirep_showcol5';
	$opt_name6 	= 'FP_pirep_showcol6';
	$opt_name7 	= 'FP_pirep_showcol7';
	$opt_name8 	= 'FP_pirep_showcol8';
	$opt_name9 	= 'FP_pirep_showcol9';
	$opt_name10 	= 'FP_pirep_showcol10';
	$opt_name11	= 'FP_pirep_linkattribs';
	
	$hidden_field_name = 'mt_submit_hidden';
	$data_field_name  = 'va_script_path';
	$data_field_name1 = 'ShowCol1';
	$data_field_name2 = 'ShowCol2';
	$data_field_name3 = 'ShowCol3';
	$data_field_name4 = 'ShowCol4';
	$data_field_name5 = 'ShowCol5';
	$data_field_name6 = 'ShowCol6';
	$data_field_name7 = 'ShowCol7';
	$data_field_name8 = 'ShowCol8';
	$data_field_name9 = 'ShowCol9';
	$data_field_name10 = 'ShowCol10';
	$data_field_name11 = 'linkattribs';

    // Read in existing option value from database
	$opt_val   = get_option( $opt_name );
	$opt_val1  = get_option( $opt_name1 );
	$opt_val2  = get_option( $opt_name2 );
	$opt_val3  = get_option( $opt_name3 );
	$opt_val4  = get_option( $opt_name4 );
	$opt_val5  = get_option( $opt_name5 );
	$opt_val6  = get_option( $opt_name6 );
	$opt_val7  = get_option( $opt_name7 );
	$opt_val8  = get_option( $opt_name8 );
	$opt_val9  = get_option( $opt_name9 );
	$opt_val10 = get_option( $opt_name10 );
	$opt_val11 = get_option( $opt_name11 );
	$fsp_admin_link = get_bloginfo('url')."/".$opt_val."/FsPadmin";

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt_val1 = $_POST[ $data_field_name1 ];
        $opt_val2 = $_POST[ $data_field_name2 ];
        $opt_val3 = $_POST[ $data_field_name3 ];
        $opt_val4 = $_POST[ $data_field_name4 ];
        $opt_val5 = $_POST[ $data_field_name5 ];
        $opt_val6 = $_POST[ $data_field_name6 ];
        $opt_val7 = $_POST[ $data_field_name7 ];
        $opt_val8 = $_POST[ $data_field_name8 ];
        $opt_val9 = $_POST[ $data_field_name9 ];
        $opt_val10 = $_POST[ $data_field_name10 ];
	$opt_val11 = $_POST[ $data_field_name11 ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt_name1, $opt_val1 );
        update_option( $opt_name2, $opt_val2 );
        update_option( $opt_name3, $opt_val3 );
        update_option( $opt_name4, $opt_val4 );
        update_option( $opt_name5, $opt_val5 );
        update_option( $opt_name6, $opt_val6 );
        update_option( $opt_name7, $opt_val7 );
        update_option( $opt_name8, $opt_val8 );
        update_option( $opt_name9, $opt_val9 );
        update_option( $opt_name10, $opt_val10 );
	update_option( $opt_name11, $opt_val11 );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
<?php

    }

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'FP FsP Pirep Plugin Options', 'mt_trans_domain' ) . "</h2>";

    // options form
    
    ?>
<h3>USEAGE</h3><p>To make your VA report appear on a page or post, place '[fp-pirep-report]' (without the quotes, but with the brackets) in the content of the page where you want the report table to appear.</p>
<p>Table cells are class 'pirepcell' and can be styled using CSS with that name</p>
<hr /><h3>Paths</h3>
<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("VA Script Path->  ".get_bloginfo('url')."/" , 'mt_trans_domain' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20"> (NOTE: NO PRECEEDING OR TRAILING SLASH!)
</p><hr />
<p><?php _e("Extra Link attributes (target, class, etc.) " , 'mt_trans_domain' ); ?> 
<input type="text" name="<?php echo $data_field_name11; ?>" value="<?php echo str_replace('\"', '&quot;', $opt_val11 ); ?>" size="20"> (NOTE: ENTER AS target="_blank" ETC.)
</p><hr />
<h3>Select Columns to Display</h3>
<table style="width: 90%; margin: 0 auto;">
	<tr>
		<td style="width: 50%">
			<input name="<?php echo $data_field_name1; ?>" type="checkbox" value="1" <?php checked('1', $opt_val1); ?>"  /> 
			<label for="<?php echo $data_field_name1; ?>">Show Flight Number / Report link</label>
		</td><td>
			<input name="<?php echo $data_field_name2; ?>" type="checkbox" value="1" <?php checked('1', $opt_val2); ?>"  /> 
			<label for="<?php echo $data_field_name2; ?>">Show Flight Date</label>
	</tr><tr>
		<td>
			<input name="<?php echo $data_field_name3; ?>" type="checkbox" value="1" <?php checked('1', $opt_val3); ?>"  /> 
			<label for="<?php echo $data_field_name3; ?>">Show Airline Name</label>
		</td><td>
			<input name="<?php echo $data_field_name4; ?>" type="checkbox" value="1" <?php checked('1', $opt_val4); ?>"  /> 
			<label for="<?php echo $data_field_name4; ?>">Show Pilot Name</label>
	</tr><tr>
		<td>
			<input name="<?php echo $data_field_name5; ?>" type="checkbox" value="1" <?php checked('1', $opt_val5); ?>"  /> 
			<label for="<?php echo $data_field_name5; ?>">Show Departure</label>
		</td><td>
			<input name="<?php echo $data_field_name6; ?>" type="checkbox" value="1" <?php checked('1', $opt_val6); ?>"  /> 
			<label for="<?php echo $data_field_name6; ?>">Show Arrival</label>
	</tr><tr>
		<td>
			<input name="<?php echo $data_field_name7; ?>" type="checkbox" value="1" <?php checked('1', $opt_val7); ?>"  /> 
			<label for="<?php echo $data_field_name7; ?>">Show Number of Pax</label>
		</td><td>
			<input name="<?php echo $data_field_name8; ?>" type="checkbox" value="1" <?php checked('1', $opt_val8); ?>"  /> 
			<label for="<?php echo $data_field_name8; ?>">Show A/C Flown</label>
	</tr><tr>
		<td>
			<input name="<?php echo $data_field_name9; ?>" type="checkbox" value="1" <?php checked('1', $opt_val9); ?>"  /> 
			<label for="<?php echo $data_field_name9; ?>">Show Block Time</label>
		</td><td>
			<input name="<?php echo $data_field_name10; ?>" type="checkbox" value="1" <?php checked('1', $opt_val10); ?>"  /> 
			<label for="<?php echo $data_field_name10; ?>">Show Results</label>
	</tr>
</table>
		
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p>

</form>
<hr />
<h3>Links</h3>
For your convenience...
<ul>
	<li>
		<a href="<?php echo $fsp_admin_link; ?>" target="_blank">FsP ADMIN</a> - Link assumes you did not change the default admin folder name (default name was 'FsPadmin')
	</li>
	<li>
		<a href="http://www.fspassengers.com" target="_blank">FSpassengers.com</a>
	</li>
	<li>
		<a href="http://www.federalproductions.com" target="_blank">Federal Productions</a>
	</li>
</ul>
</div>
<?php
 
}

// Add Settings link
function FP_FSPax_add_settings_link( $links, $file ) 
	{
	if ( $file == plugin_basename( dirname(__FILE__).'/fs-pax-pirep.php' ) ) 
		{
		$links[] = '<a href="'.get_admin_url().'/options-general.php?page=fs-pax-pirep.php">'.__('Settings').'</a>';
		}
	return $links;
	}

add_filter( 'plugin_action_links', 'FP_FSPax_add_settings_link', 10 , 2 );

// Add Options menu
add_action('admin_menu', 'mt_add_pages');

// action function for above hook
function mt_add_pages() 
	{
	add_options_page('FP FSpax Pirep', 'FP FSpax Pirep', 8, basename(__FILE__), 'fp_pirep_admin_options');
	}

// Add hook to WP

add_filter('the_content','fedprod_showfspflights');

function fedprod_addToHead() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		$file = dirname(__FILE__) . '/fs-pax-pirep.php';
		$file_url = plugin_dir_url($file);
		$style_sheet = $file_url . "css/fs-pax.css";
		WP_Enqueue_Style('fs-pax-pirep', $style_sheet, false, $plugin_version);
	}
}
add_action('init', 'fedprod_addToHead');