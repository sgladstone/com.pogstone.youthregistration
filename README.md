CiviCRM extension to allow a parent to register their children for an event, while collecting typical information about each parent and each emergency contact.

Version of CiviCRM tested: 4.3.5

Directions:

1) Download this to your "extensions" directory, then install this extension (Details at: http://wiki.civicrm.org/confluence/display/CRMDOC40/Extensions+Admin)

2) Create a new paid event (or edit an existing paid event).

3) Go to the event configuration tab "Online Registration" and choose to "allow online registration" and "Use Enhanced Registration?"

4) (Optional) Check the box to allow "Register multiple participants?" (This will allow a parent to register multiple children as part of one registration)

5) In the section for selecting "Include Profile" be sure to select an appropriate profile to collect data for each child. ( eg such as a profile named "Student Info" ) All data from the selected profile will be recorded on the child's contact/participant record. Do NOT ask for any data about the parent or emergency contacts in this profile.

6) Test out your event! You should see the profile(s) that you selected on step 5, along with profiles for "Current User" (ie the primary parent/parent 1), "Other Parent Or Guardian", and 2 emergency contacts.

After someone registers their child, you should see 6 contact records in the back office: One household contact, contact records for each parent, contact records for each emergency contact, and a contact and partcipant record for each child. The primary parent and the children will ALWAYS be given the relationship "household member of" connecting them to the household. The children will ALWAYS get the relationship of "Child of" connecting them to both parents. The children will ALWAYS get the relationship "emergency contact is" to the 2 emergency contacts.

Depending on the choices the primary parent makes when filling out the event registration form ( ie "Is my Spouse" and "Shares my Address" questions), then the 2nd parent may get the relationships "spouse of" and "household member of" added.

Current features:

- You can choose any profile or profiles for each child being registered. (Works the expected way when using the CiviEvent "online registration" tab)
- You can select different child profiles for each event.
- You can add or remove fields to the profiles for each parent and emergency contacts.
- The primary parent is not required to fill in any information about the 2nd parent. (Such as a single parent who does not wish to share information about the other parent)

Current limitations:

- Only works for paid events (of course event fee can be 0.00)
- Physical address info (street address, city, etc) must be collected for the primary parent, or disaster follows.
- The profiles for both parents and both emergency contacts are the same for ALL events using this extension.
- Assumes a common family situation: A parent is registering their own child for an event.
- Financial data ends up on the contact record of the first child being registered, not the primary parent.
- If a field such as "nickname" is used for parent 1, then it will not show for the 2nd parent profile or emergency contact profiles.


Enhancement ideas:

- Allow for unpaid events
- Record financial data on the contact record of the primary parent
- Create "sibling of" relationships between children registered together; or this could be done in batch after the fact for any contacts with the same parent(s)
- Allow other kinds of adults to register a child for an event. (For example: allow for a step-parent to register their step-child for an event, or allow a grandparent to register their grandchild for an event)
