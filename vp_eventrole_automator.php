<?php

require_once 'vp_eventrole_automator.civix.php';
require_once 'CRM/VpEventroleAutomator/Utils.php';

use CRM_VpEventroleAutomator_ExtensionUtil as E;
use CRM\VpEventroleAutomator\Utils as U;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function vp_eventrole_automator_civicrm_config(&$config): void
{
  _vp_eventrole_automator_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function vp_eventrole_automator_civicrm_install(): void
{
  _vp_eventrole_automator_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function vp_eventrole_automator_civicrm_enable(): void
{
  _vp_eventrole_automator_civix_civicrm_enable();
}

function vp_eventrole_automator_civicrm_postCommit($op, $objectName, $objectId, &$objectRef)
{
  Civi::log()->debug("Post Commit: op = {$op}, objectName = {$objectName}, objectId = {$objectId}");

  if ($objectName === "Activity") {
    $activityType = U::getActivityType($objectRef->activity_type_id);
    Civi::log()->debug("Type: {$activityType}");
    if ($activityType === "Volunteer Event Role") {
      if ($op === "create") {
        Civi::log()->debug("Creating Volunteer Event Role");
        // Fetch the event role details
        $details = U::getEventRoleDetails($objectId);
        Civi::log()->debug("Event Role Details: ", $details);

        if ($details) {
          // Calculate the registration dates
          $registrationDates = U::calculateRegistrationDates(
            $details['activity_date_time'],
            $details[U::CUSTOMFIELDGROUP.'.Registration_Start_Days_Before'],
            $details[U::CUSTOMFIELDGROUP.'.Registration_End_Days_Before']
          );
          Civi::log()->debug("Registration Dates: ", $registrationDates);

          // Populate the registration dates
          U::populateRegistrationDates($objectId, $registrationDates['Registration_Start'], $registrationDates['Registration_End']);
        }
      }

      if ($op === "edit") {
        Civi::log()->debug("Editing Volunteer Event Role");
        // Fetch the event role details
        $details = U::getEventRoleDetails($objectId);
        Civi::log()->debug("Event Role Details: ", $details);

        if ($details) {
          // Fetch the original activity details
          $original = U::getOriginalActivity($objectId);
          Civi::log()->debug("Original Activity Details: ", $original);

          if ($original) {
            $originalStart = $original[U::CUSTOMFIELDGROUP.'.Registration_Start'];
            $originalEnd = $original[U::CUSTOMFIELDGROUP.'.Registration_End'];

            // Calculate the registration dates
            $registrationDates = U::calculateRegistrationDates(
              $details['activity_date_time'],
              $details[U::CUSTOMFIELDGROUP.'.Registration_Start_Days_Before'],
              $details[U::CUSTOMFIELDGROUP.'.Registration_End_Days_Before']
            );
          }
          Civi::log()->debug("Registration Dates: ", $registrationDates);

          $calculatedStart = $registrationDates['Registration_Start'];
          $calculatedEnd = $registrationDates['Registration_End'];

          if ($originalStart != $calculatedStart || $originalEnd != $calculatedEnd) {
            // Populate the new registration dates
            U::populateRegistrationDates($objectId, $calculatedStart, $calculatedEnd);
          } else {
            Civi::log()->debug("No changes in registration dates");
          }
        }
      }
    }
  }
}