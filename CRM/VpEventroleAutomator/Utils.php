<?php
namespace CRM\VpEventroleAutomator;

use DateTime;
use Exception;
use Civi;

class Utils
{
    public const CUSTOMFIELDGROUP = "Volunteer_Event_Role_Details";

    public static function getActivityType($activityTypeId)
    {
        Civi::log()->debug("TypeId: {$activityTypeId}");
        $activityType = civicrm_api4('OptionValue', 'get', [
            'select' => [
                'label',
            ],
            'where' => [
                ['value', '=', $activityTypeId],
            ],
            'checkPermissions' => FALSE,
        ]);

        return $activityType[0]['label'];
    }

    public static function getEventRoleDetails($activityId)
    {
        $details = civicrm_api4('Activity', 'get', [
            'select' => [
                'activity_date_time',
                self::CUSTOMFIELDGROUP . '.Registration_Start_Days_Before',
                self::CUSTOMFIELDGROUP . '.Registration_End_Days_Before',
            ],
            'where' => [
                ['id', '=', $activityId],
            ],
            'checkPermissions' => FALSE,
        ]);

        return $details[0];
    }

    public static function getOriginalActivity($activityId)
    {
        try {
            $result = civicrm_api4('Activity', 'get', [
                'select' => [
                    self::CUSTOMFIELDGROUP . '.Registration_Start',
                    self::CUSTOMFIELDGROUP . '.Registration_End',
                ],
                'where' => [
                    ['id', '=', $activityId]
                ],
                'checkPermissions' => FALSE,
            ]);

            if (count($result[0]) != 0) {
                return $result[0];
            }
        } catch (Exception $e) {
            Civi::log()->debug("Error fetching original activity details for activity ID {$activityId}: " . $e->getMessage());
        }
    }

    public static function calculateRegistrationDates($activityDateTime, $startDaysBefore, $endDaysBefore)
    {
        $eventDateTime = new DateTime($activityDateTime);

        $registration_start = clone $eventDateTime;
        $registration_start->modify("-$startDaysBefore days");
        $registration_start->setTime(0, 0, 0);

        $registration_end = clone $eventDateTime;
        $registration_end->modify("-$endDaysBefore days");
        $registration_end->setTime(23, 59, 0);

        return [
            'Registration_Start' => $registration_start->format('Y-m-d H:i:s'),
            'Registration_End' => $registration_end->format('Y-m-d H:i:s')
        ];
    }

    public static function populateRegistrationDates($activityId, $start, $end)
    {
        Civi::log()->debug($start);
        Civi::log()->debug($end);
        Civi::log()->debug($activityId);

        try {
            $formattedStart = (new DateTime($start))->format('Y-m-d H:i:s');
            $formattedEnd = (new DateTime($end))->format('Y-m-d H:i:s');

            Civi::log()->debug("API Call Parameters: " . json_encode([
                'values' => [
                    self::CUSTOMFIELDGROUP . '.Registration_Start_Date' => $formattedStart, // Registration_Start
                    self::CUSTOMFIELDGROUP . '.Registration_End_Date' => $formattedEnd,   // Registration_End
                ],
                'where' => [
                    ['id', '=', $activityId],
                ],
                'checkPermissions' => FALSE,
            ], JSON_PRETTY_PRINT));

            $result = civicrm_api4('Activity', 'update', [
                'values' => [
                    self::CUSTOMFIELDGROUP . '.Registration_Start_Date' => $formattedStart, // accessing Date has issue, might be because of data incompatibility
                    self::CUSTOMFIELDGROUP . '.Registration_End_Date' => $formattedEnd,
                ],
                'where' => [
                    ['id', '=', $activityId],
                ],
                'checkPermissions' => FALSE,
            ]);

            Civi::log()->debug(json_encode($result, JSON_PRETTY_PRINT));

            Civi::log()->debug("Registration dates populated successfully for activity ID {$activityId}");
        } catch (Exception $e) {
            Civi::log()->debug("Error populating registration dates for activity ID {$activityId}: " . $e->getMessage());
        }
    }
}
?>