SNMPlgpConditionsTable
======================

A nagios SNMP service check to check the current alerts from a Liebert HVAC.

 check_snmp_lgpConditionsTable.php
 Written by Tim Pratte 

 This nagios snmp2_walk scans the conditionally populated  LIEBERT-GP-CONDITIONS-MIB::lgpConditionsTable
 for the output: "OID: LIEBERT-GP-CONDITIONS-MIB::" and prints the lgpConditionsWellKnown alert currently
 populating the lgpConditionsDescr.* table entry.  By default, if the alert is detected but not in our "critical"
 list, go out as a WARNING, otherwise CRITICAL


 (example:  ./check_snmp_lgpConditionsTable.php 172.25.1.1 public lgpConditionsTable)
