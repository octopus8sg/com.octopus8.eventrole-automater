# Volunteer Portal Event Role Automator (vp_eventrole_automator)

This extension improves the experience when creating/updating Volunteer Event Role activities by automatically calculating and creating registration start and end date custom fields.

## Getting Started

1. Create a new activity of Volunteer Event Role activity type

2. Fill in the activity date time field, Registration Start Days Before, and Registration End Days Before custom fields.
   **Note:** Based on the activity date, count how many days before that date you would want the registration to start and end.

3. This extension uses a hook to find the activity created. It then retrieves the activity date time, Registration Start Days Before, and Registration End Days Before fields.

4. Next, it performs a calculation for the registration start & end dates:

- **Registration Start Date** = activity date time - Registration Start Days Before
- **Registration End Date** = activity date time - Registration End Days Before

5. After calculation, the extension will make an update activity API4 request for the newly created activity to populate the registration start & end date custom fields.

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [AGPL-3.0](LICENSE.txt).