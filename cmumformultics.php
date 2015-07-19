<?php
// ** CONFIG SECTION START ** //

// mysql settings for cmum3 database
define("DBHOST","");
define("DBUSER","");
define("DBPASS","");
define("DBNAME","");

// local settings
define("CHARSET","utf-8");
define("TIMEZONE","Europe/London");

// multics settings
define("CCCAMFILE","");
define("MGCAMDFILE","");
define("NEWCAMDFILE","");

// misc settings
define("PROFILEFIELD","cspvalue");
define("STARTEXPIREDATE","0");

// ** CONFIG SECTION END ** //

// ** SCRIPT, DO NOT EDIT ANYTING BELOW ** //
define("VERSION","0.4.0");
error_reporting(0);
date_default_timezone_set(TIMEZONE);
mb_internal_encoding(CHARSET);
set_error_handler("internalerror");

function consolewrite($input) {
	print("[".date("Y-m-d H:i:s")."] ".$input."\n");
}

function internalerror($errno,$errstr) {
	consolewrite("Error: [".$errno."] ".$errstr);
}

function checkconfig() {
	if(empty(DBHOST) || empty(DBUSER) || empty(DBPASS) || empty(DBNAME) || empty(CHARSET) || empty(TIMEZONE) || empty(PROFILEFIELD)) {
		consolewrite("configuration incomplete, aborting");
		exit;
	}
}

function checkdatabase() {
	$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
		if(mysqli_connect_errno()) {
			consolewrite(strtolower(mysqli_connect_error()));
			consolewrite("cannot connect to mysql server, aborting");
			mysqli_close($mysqli);
			exit;
		} else {
			$sql=$mysqli->query("SELECT cmumversion FROM settings WHERE id='1'");
			$data=$sql->fetch_array();
			mysqli_close($mysqli);
				if($data["cmumversion"]<"3.0.0") {
					consolewrite("unsupported cmum version, aborting");
					exit;
				}
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
		consolewrite($type." file do not exists, creating file");
		$newfile=fopen($file,"w");
		fclose($newfile);
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

function checkuserdate($start,$expire,$enabled) {
	if($start<>"0000-00-00" && $expire<>"0000-00-00") {
		if(time()>=strtotime($start) && time()<=strtotime($expire) && $enabled<>"0") {
			$status="1";
		} else  {
			$status="0";
		}
	} elseif($start<>"0000-00-00" && $expire=="0000-00-00") {
		if(time()>=strtotime($start) && $enabled<>"0") {
			$status="1";
		} else {
			$status="0";
		}
	} elseif($start=="0000-00-00" && $expire<>"0000-00-00") {
		if(time()<=strtotime($expire) && $enabled<>"0") {
			$status="1";
		} else {
			$status="0";
		}
	} elseif($start=="0000-00-00" && $expire=="0000-00-00") {
		if($enabled=="1" || $enabled=="") {
			$status="1";
		} else {
			$status="0";
		}
	}
return($status);
}

function gencccamusers($file,$expire) {
	consolewrite("generating cccam users");
		$cccamusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			if($expire=="1") {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles,enabled,startdate,expiredate FROM users WHERE boxtype='cccam'");
			} else {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='cccam'");	
			}
				while($usrdata=$users->fetch_array()) {
					if($expire=="1" && checkuserdate($usrdata["startdate"],$usrdata["expiredate"],$usrdata["enabled"])=="1" || $expire=="0") {
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
						$cccamusers.="F: ".$usrdata["user"]." ".$usrdata["password"]." { ".$profres."; ".$usripmask.$usrdisplayname."}\n";
					}
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$cccamusers);
		fclose($usrfile);
}

function genmgcamdusers($file,$expire) {
	consolewrite("generating mgcamd users");
		$mgcamdusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			if($expire=="1") {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles,enabled,startdate,expiredate FROM users WHERE boxtype='mgcamd'");
			} else {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='mgcamd'");	
			}
				while($usrdata=$users->fetch_array()) {
					if($expire=="1" && checkuserdate($usrdata["startdate"],$usrdata["expiredate"],$usrdata["enabled"])=="1" || $expire=="0") {
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
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$mgcamdusers);
		fclose($usrfile);
}

function gennewcamdusers($file,$expire) {
	consolewrite("generating newcamd users");
		$newcamdusers="";
		$mysqli=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
			if($expire=="1") {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles,enabled,startdate,expiredate FROM users WHERE boxtype='newcamd'");
			} else {
				$users=$mysqli->query("SELECT user,password,displayname,ipmask,profiles FROM users WHERE (enabled='1' OR enabled='') AND boxtype='newcamd'");	
			}
				while($usrdata=$users->fetch_array()) {
					if($expire=="1" && checkuserdate($usrdata["startdate"],$usrdata["expiredate"],$usrdata["enabled"])=="1" || $expire=="0") {
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
				}
		mysqli_close($mysqli);
		$usrfile=fopen($file,"w");
		fwrite($usrfile,$newcamdusers);
		fclose($usrfile);
}

consolewrite("cmum-for-multics v".VERSION." by dukereborn");
consolewrite("checking configuration");
	checkconfig();
consolewrite("checking database");
	checkdatabase();
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
			gencccamusers(CCCAMFILE,STARTEXPIREDATE);
		} else {
			consolewrite("no cccam file given, skipping cccam users");
		}
		if(MGCAMDFILE<>"") {
			checkfile("mgcamd",MGCAMDFILE);
			genmgcamdusers(MGCAMDFILE,STARTEXPIREDATE);
		} else {
			consolewrite("no mgcamd file given, skipping mgcamd users");
		}
		if(NEWCAMDFILE<>"") {
			checkfile("newcamd",NEWCAMDFILE);
			gennewcamdusers(NEWCAMDFILE,STARTEXPIREDATE);
		} else {
			consolewrite("no newcamd file given, skipping newcamd users");
		}
	if($loop=="1") {
		sleep($looptime);
		goto startpoint;
	}
?>