#!/usr/bin/php -q
<?php
// check_snmp_lgpConditionsTable.php
// Written by Tim Pratte <tim.pratte@secglobe.net>
//
// This nagios snmp2_walk scans the conditionally populated  LIEBERT-GP-CONDITIONS-MIB::lgpConditionsTable
// for the output: "OID: LIEBERT-GP-CONDITIONS-MIB::" and prints the lgpConditionsWellKnown alert currently
// populating the lgpConditionsDescr.* table entry.  By default, if the alert is detected but not in our "critical"
// list, go out as a WARNING, otherwise CRITICAL
//
//
// (example:  ./check_snmp_lgpConditionsTable.php 172.25.75.103 public lgpConditionsTable)

if ($argc != 4 ) {
    echo "USAGE:\r\n";
    echo "    $argv[0] <hostname> <community> lgpConditionsTable \r\n";
    exit(2);
}

#$a = snmp2_walk($argv[1], $argv[2], $argv[3]);
$a = array("OID: LIEBERT-GP-CONDITIONS-MIB::lgpConditionHighTemperature",
    "OID: LIEBERT-GP-CONDITIONS-MIB::lgpConditionCompressorHighHeadPressure",
    "OID: LIEBERT-GP-CONDITIONS-MIB::lgpConditionLocalAlarm1",
    "OID: LIEBERT-GP-CONDITIONS-MIB::lgpConditionWhatever");
$alert = " ";
$alertprint = " ";
$alertperf = "";
$exitcode = 0;
$exitprint = "OK:";
$Clist = ""; // Critical list
$Wlist = ""; // Warning list
$Wflag = 0;  // warning flag
$flag = 0;
$conditionspresent = "1.3.6.1.4.1.476.1.42.3.2.2.0"; // lgpConditionsPresent.0 OID
$conditions = 0;
#$conditions = substr((snmpget($argv[1], $argv[2], $conditionspresent)), 9);

foreach ($a as $val) {

    if ($val === FALSE) {
        echo "OK: $argv[3] not triggered\r\n";
        exit(0);
    }  elseif (substr($val, 0, 32) === "OID: LIEBERT-GP-CONDITIONS-MIB::") {
        $alert = substr($val, 32);
#             $alertprint = substr($val, 32).", ".$alertprint;
        $alertperf = substr($val, 32)."=1 ".$alertperf;
        $conditions = $conditions + 1;
        $flag = 0;

        // If we already have a critical alert, don't reset it to warning
        if ($exitcode !== 2) { $exitprint = "WARNING:"; $exitcode = 1; }

        switch ($alert) {
            case (preg_match("/lgpConditionLocalAlarm.*/", $alert) ? true : false ): $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionHighHumidity": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionLowHumidity": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionLossOfAirflow": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionCompressorHighHeadPressure": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionCompressorOverload": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionCompressorShortCycle": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionCompressorLowSuctionPressure": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionMainFanOverLoad": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionWaterUnderFloor": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionHumidifierProblem": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionLowWaterInHumidifier": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionSmokeDetected": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionLowWaterFlow": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpGeneralFault": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionCompressorLowPressure": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionSystemControlBatteryLow": $Clist = $alert." ".$Clist; $flag = 2; break;
            case "lgpConditionEmergencyShutdown": $Clist = $alert." ".$Clist; $flag = 2; break;
#				default:  $exitprint = "WARNING:"; $exitcode=1; break;  // no criticals found--must be warning
        }
        // if We found a critical alert, now set the exitcode and reset the flag
        if ($flag == 2) { $exitcode = 2; $flag = 0; }
        else
        { $Wlist = $alert." ".$Wlist; $Wflag = 1; $flag = 0; }  // else add this alert to the warning list
    }

}

if ($Wflag == 1 && $exitcode == 1) {
    echo "WARNING: $Wlist|lpgConditionsTable_alerts=$conditions $alertperf\r\n"; exit($exitcode);
}

if ($exitcode == 2 && $Wflag == 1) {    // show the critical list first, then the warnings
    echo "CRITICAL: $Clist;  WARNING: $Wlist|lpgConditionsTable_alerts=$conditions $alertperf\r\n"; exit($exitcode);
}

if ($exitcode == 2 && $Wflag == 0) { // else, just show the critical list
    echo "CRITICAL: $Clist |lpgConditionsTable_alerts=$conditions $alertperf\r\n"; exit($exitcode);
}
#  no conditions found so exit cleanly
echo "OK: lgpConditionsTable not triggered |lgpConditionsTable_alerts=$conditions\r\n";
exit(0);

?>
