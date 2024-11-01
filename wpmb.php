<?php
/*
Plugin Name: WPMB
Plugin URI: http://www.bargolf.net
Description: A plugin to show recent MotionBased activities in the side bar
Version: 1.2
Author: Matt Underwood
Author URI: http://www.bargolf.net
*/

/*  Copyright 2007  Matt Underwood  (email : matt@bargolf.net)

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

function widget_wpmb_init() {

    // Check to see required Widget API functions are defined...
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
        return; // ...and if not, exit gracefully from the script.

	function widget_wpmb($args) {
		extract($args);
	?>
		    <?php echo $before_widget; ?>
		        <?php echo $before_title
		            . 'MotionBased'
		            . $after_title; ?>
			<?php

			$options = get_option('widget_wpmb');

			$szMBUser = empty($options['mbuser']) ? "" : $options['mbuser'];
			$iMaxActivities = empty($options['profshownum']) ? 5 : $options['profshownum'];

			if($szMBUser != "")
				$activities = wpmb_getactivities($szMBUser, $iMaxActivities);
			else
				$activities = false;

			echo wpmb_getactivityhtml($activities);
			?>
		    <?php echo $after_widget; ?>
	<?php
	}

    function widget_wpmb_control() {

        $options = get_option('widget_wpmb');

        $newoptions = Array();

        // This is for handing the control form submission.
        if ( $_POST['wpmb-submit'] ) {
            // Clean up control form submission options
            $newoptions['mbuser'] = strip_tags(stripslashes($_POST['wpmb-mbuser']));
            $newoptions['profshownum'] = strip_tags(stripslashes($_POST['wpmb-profshownum']));
            $newoptions['profshowloc'] = strip_tags(stripslashes($_POST['wpmb-profshowloc']));
            $newoptions['profshowdist'] = strip_tags(stripslashes($_POST['wpmb-profshowdist']));
            $newoptions['profshowdur'] = strip_tags(stripslashes($_POST['wpmb-profshowdur']));
            $newoptions['profshowevtype'] = strip_tags(stripslashes($_POST['wpmb-profshowevtype']));

			// If original widget options do not match control form
			// submission options, update them.
			if ( $options != $newoptions ) {
				$options = $newoptions;
				update_option('widget_wpmb', $options);
			}
        }

        $szMBUser = htmlspecialchars($options['mbuser'], ENT_QUOTES);

        echo "<div>";
        echo "Specify the MotionBased.com account to show activities for:<br>";
        echo "<label for='wpmb-mbuser' style='line-height:35px;display:block;'>MotionBased account name: "
        	."<input type='text' id='wpmb-mbuser' name='wpmb-mbuser' value='".$szMBUser."' /></label>";

        echo "Select how many activities should be shown:<br>";
        $arrNums = Array();
        for($i = 1; $i <= 20; $i++)
        	$arrNums[$i] = $i;
        $arrNums[-1] = "All of them";
		wpmb_show_select("Number of activities",$options,"profshownum",5,$arrNums);

        echo "Select which activity details will be shown:<br>";
		wpmb_show_option("Location",$options,"profshowloc",1,Array(0 => "Don't show", 1=> "Short location", 2=>"Full location"));
		wpmb_show_option("Distance",$options,"profshowdist",1,Array(0 => "Don't show", 1=> "Show in miles", 2=>"Show in kilometres", 3=>"Show in Miles and Kilometres"));
		wpmb_show_option("Duration",$options,"profshowdur",1,Array(0 => "Don't show", 1=> "Show activity duration"));
		wpmb_show_option("Event Type",$options,"profshowevtype",1,Array(0 => "Don't show", 1=> "Show event (e.g. running)", 2=>"Show event type (e.g. recreation)", 3=>"Show event and event type"));

        echo "<input type='hidden' name='wpmb-submit' id='wpmb-submit' value='1' />";
        echo "</div>";
    }

    function wpmb_show_select($szTitle, $options, $szOption, $iDefault, $arrChoices)
    {
    	if(!isset($arrChoices[$options[$szOption]]))
    		$options[$szOption] = $iDefault;

    	echo "<table width=100% style='padding-bottom:10px;'>";
		echo "<tr>";
		echo "<td width=40% valign=top rowspan=".count($arrChoices).">".$szTitle."</td>";
		echo "<td>";
		echo "<select id='wpmb-".$szOption."' name='wpmb-".$szOption."'>";
    	foreach($arrChoices as $iValue => $szChoice)
    	{
    		echo "<option ";
			if($options[$szOption] == $iValue)
				echo "selected ";
	    	echo "value=".$iValue.">".$szChoice;
	    }
		echo "</select>";
		echo "</td>";
		echo "</tr>";
    	echo "</table>";
    }

    function wpmb_show_option($szTitle, $options, $szOption, $iDefault, $arrChoices)
    {
    	$bFirstRow = true;

    	if(!isset($arrChoices[$options[$szOption]]))
    		$options[$szOption] = $iDefault;

    	echo "<table width=100% style='padding-bottom:10px;'>";
    	foreach($arrChoices as $iValue => $szChoice)
    	{
    		echo "<tr>";
    		if($bFirstRow)
		    	echo "<td width=40% valign=top rowspan=".count($arrChoices).">".$szTitle."</td>";
    		echo "<td>";
	    	echo "<input type='radio' id='wpmb-".$szOption."' name='wpmb-".$szOption."' ";
			if($options[$szOption] == $iValue)
				echo "checked ";
	    	echo "value=".$iValue.">".$szChoice."<br>";
	    	echo "</td>";
	    	echo "</tr>";
	    	$bFirstRow = false;
	    }
    	echo "</table>";

    }

	function wpmb_getactivities($szMBUser, $iMaxActivities)
	{
		$activities = false;

		$bForce = true;
		$szMBRss = wpmb_GetRSSFile($szMBUser, $iMaxActivities, $bForce);

		$xml = simplexml_load_file($szMBRss);

		if($xml !== false)
		{
			$activities = Array();
			if(isset($xml->channel))
			{
				$xmlChannel = $xml->channel;
				foreach($xmlChannel->item as $xmlItem)
				{
					if(isset($xmlItem->pubDate))
					{
						// the pubDate string has the TZ information on the end. But we want to store the localtime
						// so trim this off before converting
						$szTS = $xmlItem->pubDate;
						$szTZ = "0000";
						if((strlen($szTS) > 5) && (substr($szTS,-5,1) == "+") || (substr($szTS,-5,1) == "-"))
						{
							$szTZ = substr($szTS,-4);
							$szTS = substr($szTS,0,-5);
						}

						$tPubDate = strtotime($szTS);

						// This is a workaround for the MB bug that gets pubdates for PM timestamps wrong by 12 hours
						if(strstr($xmlItem->description," PM near ") !== false)
						{
							// If the description contains PM near then this should be a PM timestamp!
							$arrDate = getdate($tPubDate);
							if($arrDate["hours"] < 12)
								$tPubDate += 12 * 3600;
						}

						$activities[$tPubDate] = Array();
						$activities[$tPubDate]["title"] = $xmlItem->title;
						$activities[$tPubDate]["pubdate"] = $tPubDate;
						$activities[$tPubDate]["pubdatetz"] = $szTZ;
						$activities[$tPubDate]["description"] = $xmlItem->description;
						$activities[$tPubDate]["distance"] = wpmb_GetDistanceMetres($xmlItem->description);
						$activities[$tPubDate]["duration"] = wpmb_GetDurationSecs($xmlItem->description);

						$xpath = $xmlItem->xpath("geo:lat");
						$activities[$tPubDate]["lat"] = $xpath[0];
						$xpath = $xmlItem->xpath("geo:long");
						$activities[$tPubDate]["long"] = $xpath[0];
						$xpath = $xmlItem->xpath("georss:box");
						$activities[$tPubDate]["box"] = $xpath[0];

						for($iCategory = 0; $iCategory < count($xmlItem->category); $iCategory++)
						{
							$attrs =  $xmlItem->category[$iCategory]->attributes();
							if(isset($attrs["domain"]))
							{
								if($attrs["domain"] == "http://www.motionbased.net/activity")
									$activities[$tPubDate]["activity"] = $xmlItem->category[$iCategory];
								else if($attrs["domain"] == "http://www.motionbased.net/activity/type")
									$activities[$tPubDate]["type"] = $xmlItem->category[$iCategory];
								else if($attrs["domain"] == "http://www.motionbased.net/event/type")
									$activities[$tPubDate]["eventtype"] = $xmlItem->category[$iCategory];
								else if($attrs["domain"] == "http://www.motionbased.net/location/absolute")
									$activities[$tPubDate]["location"] = $xmlItem->category[$iCategory];
							}
						}
					}
				}
			}
		}

		return $activities;
	}

	function wpmb_getactivityhtml($activities)
	{
		$szHTML = "";

		if($activities == false)
		{
				$szHTML = "<h3>No MotionBased account</h3><ul><li>No MotionBased account configured yet</ul>";
		}
		else
		{
			if(count($activities) == 0)
			{
				$szHTML = "<h3>No MotionBased activites available</h3>";
			}
			else
			{
				krsort($activities);

				foreach($activities as $actkey => $activity)
				{
					$options = get_option('widget_wpmb');

					$iShowLoc = empty($options['profshowloc']) ? 1 : $options['profshowloc'];
					$iShowDist = empty($options['profshowdist']) ? 1 : $options['profshowdist'];
					$iShowDur = empty($options['profshowdur']) ? 1 : $options['profshowdur'];
					$iShowType = empty($options['profshowevtype']) ? 1 : $options['profshowevtype'];

					$szHTML .= "<ul>";
					$szHTML .= '<li>';
					$szHTML .=  "<a title='View at MotionBased.com' class='outlink' href='http://trail.motionbased.com/trail/episode/view.mb?episodePk.pkValue=".$activity["activity"]."'>";
					$szHTML .= strftime("%d %b", $activity['pubdate']);
					$szHTML .= ' - '.$activity['title'];
					$szHTML .= "</a>";

					if($iShowLoc > 0)
					{
						$szLoc = $activity['location'];
						if($iShowLoc == 2)
						{
							$iCommaPos = strpos($szLoc,',');
							if($iCommaPos !== false)
							{
								$szLoc = substr($szLoc,0,$iCommaPos);
							}
						}
						$szHTML .= '<br>'.$szLoc;
					}

					if(($iShowDist + $iShowDur + $iShowType) > 0)
					{
						$szHTML .= '<br>';
						if(($iShowDist > 0) && ($activity["distance"] !== false))
						{
							$szHTML .= wpmb_FormatDistanceString($activity["distance"],$iShowDist);
						}

						if(($iShowDur > 0) && ($activity["duration"] !== false))
						{
							if($iShowDist > 0)
								$szHTML .= " - ";

							$iTotalSecs = $activity["duration"];
							$szDurationWide = wpmb_FormatDuration($iTotalSecs, true);
							$szDurationNarrow = wpmb_FormatDuration($iTotalSecs, false);

							$szHTML .= $szDurationNarrow;
						}

						if($iShowType > 0)
						{
							if(($iShowDist + $iShowDur) > 0)
								$szHTML .= " - ";

							if($iShowType == 3)
								$szHTML .= "(";

							if(($iShowType == 1) || ($iShowType == 3))
								$szHTML .= $activity['type'];

							if($iShowType == 3)
								$szHTML .= " - ";

							if(($iShowType == 2) || ($iShowType == 3))
								$szHTML .= $activity['eventtype'];

							if($iShowType == 3)
								$szHTML .= ")";
						}
					}
					$szHTML .= "</ul>";
				}
			}
		}

		return $szHTML;
	}

	function wpmb_FormatDuration($iTotalSecs, $bWide)
	{
		$szDuration = "";
		if($iTotalSecs >= 3600)
		{
			if($bWide)
				$szDuration .= floor($iTotalSecs / 3600)."h ";
			else
				$szDuration .= floor($iTotalSecs / 3600)."h";
		}

		if($iTotalSecs >= 60)
		{
			if($bWide)
				$szDuration .= (floor($iTotalSecs / 60) % 60)."m ";
			else
				$szDuration .= (floor($iTotalSecs / 60) % 60)."m";
		}

		if($bWide)
			$szDuration .= ($iTotalSecs % 60)."s";
		else if($iTotalSecs < 3600)
			$szDuration .= ($iTotalSecs % 60)."s";

		return $szDuration;
	}

	function wpmb_FormatDistanceString($iMeters, $iFormat)
	{
		// 1, Miles, 2, Km, 3 Km/Mi
		$szDistance = "";
		if(($iFormat == 2) || ($iFormat == 3))
			$szDistance .= wpmb_FormatDistance($iMeters, true);

		if($iFormat == 3)
			$szDistance .= "/";

		if(($iFormat == 1) || ($iFormat == 3))
			$szDistance .= wpmb_FormatDistance($iMeters, false);

		return $szDistance;
	}

	function wpmb_FormatDistance($iMetres, $bMetric)
	{
		$szDistance = "";

		if($bMetric)
		{
			if($iMetres < 1000)
				$szDistance = $iMetres."m";
			else
				$szDistance = round($iMetres / 1000.0,2)."Km";
		}
		else
		{
			$szDistance = round($iMetres / 1609.344,2)."mi";
		}

		return $szDistance;
	}

	function wpmb_GetDistanceMetres($szDescr)
	{
		$fDistance = false;

		$iStartPos = strpos($szDescr,"distance of ");
		if($iStartPos !== false)
		{
			$iStartPos += strlen("distance of ");

			$iEndPos = strpos($szDescr, " mile", $iStartPos);
			if($iEndPos !== false)
			{
				$fDistanceMiles = substr($szDescr, $iStartPos, $iEndPos - $iStartPos);
				$fDistance = $fDistanceMiles * 1609.344;
			}
		}

		return $fDistance;
	}

	function wpmb_GetDurationSecs($szDescr)
	{
		$iSecs = 0;

		if(preg_match('/([0-9]+) hour/',$szDescr, $arrMatches) == 1)
			$iSecs += $arrMatches[1] * 3600;

		if(preg_match('/([0-9]+) minute/',$szDescr, $arrMatches) == 1)
			$iSecs += $arrMatches[1] * 60;

		if(preg_match('/([0-9]+) second/',$szDescr, $arrMatches) == 1)
			$iSecs += $arrMatches[1];

		if($iSecs == 0)
			$iSecs = false;

		return $iSecs;
	}

	function wpmb_GetCachedRSSFileName($szMBUser, $iMaxActivities)
	{
		$path = get_settings('upload_path');
		$szFile = $path."/".sha1($szMBUser.":".$iMaxActivities).".xml";

		return $szFile;
	}

	function wpmb_GetRSSFile($szMBUser, $iMaxActivities, $bForce = false)
	{
		$szFile = wpmb_GetCachedRSSFileName($szMBUser, $iMaxActivities);

		if(($bForce) || (!file_exists($szFile)))
		{
			// not currently present. Try to collect
			$szMBRss = "http://".$szMBUser.".motionbased.com/rss/?resultsLimit=".$iMaxActivities;
			
			if(ini_get("allow_url_fopen"))
			{
				if(!copy($szMBRss, $szFile))
				{
					$szFile = false;
				}
			}
			else
			{
				// try to use Curl lib instead
				
				$ch = curl_init($szMBRss);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				if(curl_exec($ch) === false)
				{
					$szFile = false;
				}
				else
				{
					$szReturn =  curl_multi_getcontent  ( $ch  );
					curl_close($ch);
				
					$fCopy = fopen($szFile, "w");
					if($fCopy === false)
					{
						$szFile = false;
					}
					else
					{
						fputs($fCopy, $szReturn);
						fclose($fCopy);
					}
				}
			}
		}

		return $szFile;
	}

	// This registers the widget
	register_sidebar_widget('MotionBased', 'widget_wpmb');

	// This registers the widget control form
	register_widget_control('MotionBased', 'widget_wpmb_control',600,500);

}

// Delays plugin execution until Dynamic Sidebar has loaded first.
add_action('plugins_loaded', 'widget_wpmb_init');

?>
