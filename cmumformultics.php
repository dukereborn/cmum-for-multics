<?php
// ** CONFIG SECTION START ** //

// mysql settings for cmum3 database, fill after "," and inside "".
define("DBHOST",""); //must be an ip or domain like: 212.221.121.111 or myserver.dyndns.org or mydomain.com
define("DBUSER",""); //must be a valid username that have access to the database you host the CSP configuration
define("DBPASS",""); //here goes the password for the user specified above
define("DBNAME","");

// local settings
define("CHARSET","utf-8"); //leave it as it is, recommended
define("TIMEZONE","Europe/London"); //change it according to timezone of your country

// multics settings
define("CCCAMFILE",""); //here goes the multics cccam file location wich can be: /var/etc/multics/cccam.list
define("MGCAMDFILE",""); //same as above but for mgcamd
define("NEWCAMDFILE",""); //same as above but for newcamd

// misc settings
define("PROFILEFIELD","cspvalue"); //need to ask the developer for more info

// ** CONFIG SECTION END ** //

// ** SCRIPT, DO NOT EDIT ANYTING BELOW ** //

define("VERSION","0.2.0");
date_default_timezone_set(TIMEZONE);
mb_internal_encoding(CHARSET);

function consolewrite($input) {
	print("[".date("Y-m-d H:i:s")."] ".$input."\n");
}

function checkconfig() {
	if(empty(DBHOST), || empty(DBUSER), || empty(DBPASS), || empty(DBNAME), || empty(CHARSET), || empty(TIMEZONE), || empty(PROFILEFIELD)) {
		consolewrite("configuration incomplete, aborting");
		exit;
	}
}

function checkfile($type,$file) {
	clearstatcache();
	if(file_exists($file)) {
		if(!is_writable($file)) {
			consolewrite($type." is not writable, aborting");
			exit;
		} else {
			consolewrite("truncating ".$type." file");
			$exfile=fopen($file,"w");
			ftruncate($exfile,0);
			fclose($exfile);
		}
	} else {
		consolewrite($type." file may not exists, trying to create one!");
		try {
			$newfile=fopen($file,"w");
			fclose($newfile);
		} catch (ErrorException $e) {
			if(isset($e) && !empty($e)){
				consolewrite($e);
				exit;
			}	
		}
	}
}

function getprofiles() {
	$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
		$psql=$mysqli->query("SELECT id,".PROFILEFIELD." FROM profiles");
		$profiles=array();
			while($pdata=$psql->fetch_array()) {
				$profiles[$pdata["id"]]=$pdata[PROFILEFIELD];
			}
	mysqli_close($mysqli);
return($profiles);
}

function gencccamusers($file) {
	consolewrite("generating cccam users");
		$cccamusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='cccam'");
				while($usrdata=$users->fetch_array()) {
					$profres="";
					$profvalues="";
					if($usrdata["profiles"]=="") {
						$profres="";
					} else {
						$dbprof=unserialize($usrdata["profiles"]);
						$cmumprof=getprofiles();
							if($dbprof<>"" && $dbprof<>"N;") {
								foreach($dbprof as $useprof) {
									$profvalues.=$cmumprof[$useprof].", ";
								}
								$profres=trim($profvalues);
								$profres=substr($profres,0,-1);
								$profdata="";
								$profvalues="";
							} else {
								$profres="";
							}
					}
					if($usrdata["ipmask"]<>"") {
	 					$usripmask="host=".$usrdata["ipmask"]."; ";
 					} else {
	 					$usripmask="";
 					}
 					if($usrdata["displayname"]<>"") {
	 					$usrdisplayname="name=".$usrdata["displayname"]."; ";
 					} else {
	 					$usrdisplayname="";
 					}
					$cccamusers.='F: {$usrdata["user"]} {$usrdata["password"]} { {$profres}; {$usripmask.$usrdisplayname} }\n';	
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$cccamusers);
		fclose($usrfile);
}

function genmgcamdusers($file) {
	consolewrite("generating mgcamd users");
		$mgcamdusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='mgcamd'");
				while($usrdata=$users->fetch_array()) {
					$profres="";
					$profvalues="";
					if($usrdata["profiles"]=="") {
						$profres="";
					} else {
						$dbprof=unserialize($usrdata["profiles"]);
						$cmumprof=getprofiles();
							if($dbprof<>"" && $dbprof<>"N;") {
								foreach($dbprof as $useprof) {
									$profvalues.=$cmumprof[$useprof].", ";
								}
								$profres=trim($profvalues);
								$profres=substr($profres,0,-1);
								$profdata="";
								$profvalues="";
							} else {
								$profres="";
							}
					}
					if($usrdata["ipmask"]<>"") {
	 					$usripmask="host=".$usrdata["ipmask"]."; ";
 					} else {
	 					$usripmask="";
 					}
 					if($usrdata["displayname"]<>"") {
	 					$usrdisplayname="name=".$usrdata["displayname"]."; ";
 					} else {
	 					$usrdisplayname="";
 					}
					$mgcamdusers.="MG: ".$usrdata["user"]." ".$usrdata["password"]." { ".$profres."; ".$usripmask.$usrdisplayname."}\n";	
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$mgcamdusers);
		fclose($usrfile);
}

function gennewcamdusers($file) {
	consolewrite("generating newcamd users");
		$newcamdusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			$users=$mysqli->query("SELECT user,password,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='newcamd'");
				while($usrdata=$users->fetch_array()) {
					$profres="";
					$profvalues="";
					if($usrdata["profiles"]=="") {
						$profres="";
					} else {
						$dbprof=unserialize($usrdata["profiles"]);
						$cmumprof=getprofiles();
							if($dbprof<>"" && $dbprof<>"N;") {
								foreach($dbprof as $useprof) {
									$profvalues.=$cmumprof[$useprof].", ";
								}
								$profres=trim($profvalues);
								$profres=substr($profres,0,-1);
								$profdata="";
								$profvalues="";
							} else {
								$profres="";
							}
					}
					$newcamdusers.="USER: ".$usrdata["user"]." ".$usrdata["password"]." { ".$profres." }\n";	
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$newcamdusers);
		fclose($usrfile);
}

consolewrite("cmum-for-multics v".VERSION." by dukereborn");
consolewrite("checking configuration");
	checkconfig();
consolewrite("checking loop");
	if(isset($argv[1]) && $argv[1]=="-l") {
		$loop="1";
			if(isset($argv[2]) && $argv[2]<>"") {
				$looptime=$argv[2];
			} else {
				$looptime="300";
			}
	} else {
		$loop="0";
	}
startpoint:
	consolewrite("checking userfiles");
		if(CCCAMFILE<>"") {
			checkfile("cccam",CCCAMFILE);
			gencccamusers(CCCAMFILE);
		} else {
			consolewrite("no cccam file given, skipping cccam users");
		}
		if(MGCAMDFILE<>"") {
			checkfile("mgcamd",MGCAMDFILE);
			genmgcamdusers(MGCAMDFILE);
		} else {
			consolewrite("no mgcamd file given, skipping mgcamd users");
		}
		if(NEWCAMDFILE<>"") {
			checkfile("newcamd",NEWCAMDFILE);
			gennewcamdusers(NEWCAMDFILE);
		} else {
			consolewrite("no newcamd file given, skipping newcamd users");
		}
	if($loop=="1") {
		sleep($looptime);
		goto startpoint;
	}
?>
