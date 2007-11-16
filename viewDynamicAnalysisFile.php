<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $RCSfile: common.php,v $
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include("config.php");
include("common.php");

@$id = $_GET["id"];
@$date = $_GET["date"];

include("config.php");
$db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
mysql_select_db("$CDASH_DB_NAME",$db);

$dyn_array = mysql_fetch_array(mysql_query("SELECT * FROM dynamicanalysis WHERE id='$id'"));
$buildid = $dyn_array["buildid"];

$build_array = mysql_fetch_array(mysql_query("SELECT starttime,projectid FROM build WHERE id='$buildid'"));		
$projectid = $build_array["projectid"];
				
$project = mysql_query("SELECT * FROM project WHERE id='$projectid'");
if(mysql_num_rows($project)>0)
		{
		$project_array = mysql_fetch_array($project);
		$svnurl = $project_array["cvsurl"];
		$homeurl = $project_array["homeurl"];
		$bugurl = $project_array["bugtrackerurl"];			
		$projectname	= $project_array["name"];		
		}

list ($previousdate, $currenttime, $nextdate) = get_dates($date,$project_array["nightlytime"]);
$logoid = getLogoID($projectid);

$xml = '<?xml version="1.0"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .="<dashboard>
  <datetime>".date("D, d M Y H:i:s",strtotime($build_array["starttime"]))."</datetime>
  <date>".$date."</date>
  <svn>".$svnurl."</svn>
  <bugtracker>".$bugurl."</bugtracker>	
  <home>".$homeurl."</home>
  <projectid>".$projectid."</projectid>	
  <logoid>".$logoid."</logoid>	
  <projectname>".$projectname."</projectname>	
  <previousdate>".$previousdate."</previousdate>	
  <nextdate>".$nextdate."</nextdate>	
  </dashboard>
  ";
		
		// Build
		$xml .= "<build>";
		$build = mysql_query("SELECT * FROM build WHERE id='$buildid'");
		$build_array = mysql_fetch_array($build); 
		$siteid = $build_array["siteid"];
		$site_array = mysql_fetch_array(mysql_query("SELECT name FROM site WHERE id='$siteid'"));
		$xml .= add_XML_value("site",$site_array["name"]);
		$xml .= add_XML_value("buildname",$build_array["name"]);
		$xml .= add_XML_value("buildid",$build_array["id"]);
		$xml .= add_XML_value("buildtime",$build_array["starttime"]);		
			
  $xml .= "</build>";
		
		// dynamic analysis
		$xml .= "<dynamicanalysis>";
		$xml .= add_XML_value("status",ucfirst($dyn_array["status"]));
		$xml .= add_XML_value("filename",$dyn_array["name"]);
		$xml .= add_XML_value("log",$dyn_array["log"]);
		$href = "testSummary.php?project=".$projectid."&#38;name=".$dyn_array["name"];
		if($date)
		  {
				$href .= "&#38;date=".$date;
		  }
		else
		  {
				$href .= "&#38;date=".date("Ymd");
		  }
		$xml .= add_XML_value("href",$href);
		$xml .= "</dynamicanalysis>";
				
  $xml .= "</cdash>";

// Now doing the xslt transition
generate_XSLT($xml,"viewDynamicAnalysisFile");
?>
