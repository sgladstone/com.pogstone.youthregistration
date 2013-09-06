-- +--------------------------------------------------------------------+
--  | EnhancedEventReg version 0.2                                       |
--  +--------------------------------------------------------------------+
--  | Copyright Joseph Murray & Associates Consulting Ltd. (c) 2012      |
--  +--------------------------------------------------------------------+
--  | This file is not a part of CiviCRM, but a separate work that       |
--  | is designed to integrate with CiviCRM's extension framework        |
--  | API.
--  |                                                                    |
--  | EnhancedEventReg is free software; you can copy, modify, and       |
--  | distribute itunder the terms of the GNU Affero General Public      |
--  | License Version 3, 19 November 2007.                               |
--  |                                                                    |
--  | EnhancedEventReg is distributed in the hope that it will be        |
--  | useful, butWITHOUT ANY WARRANTY; without even the implied warranty |
--  | of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            |
--  | See the GNU Affero General Public License for more details.        |
--  |                                                                    |
--  | You should have received a copy of the GNU Affero General Public   |
--  | License with this program; if not, contact JMA Consulting          |
--  | at info[AT]civicrm[DOT]org. If you have questions about the        |
--  | GNU Affero General Public License or the licensing of CiviCRM,     |
--  | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
--  +--------------------------------------------------------------------+
 
DROP TABLE IF EXISTS civicrm_event_enhanced;
DROP TABLE IF EXISTS civicrm_event_enhanced_profile;
DROP TABLE IF EXISTS civicrm_event_enhanced_relationship;

CREATE TABLE civicrm_event_enhanced (
id INT NOT NULL AUTO_INCREMENT,
event_id INT NOT NULL ,
is_enhanced tinyint(4) default 0,
PRIMARY KEY(id)
);

CREATE TABLE civicrm_event_enhanced_profile (
  id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  uf_group_id INT NOT NULL,
  contact_position varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  shares_address tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
);

CREATE TABLE civicrm_event_enhanced_relationship (
id INT NOT NULL AUTO_INCREMENT,
event_enhanced_profile_id_a INT NOT NULL ,
relationship_type_id INT NOT NULL ,
event_enhanced_profile_id_b INT NOT NULL ,
is_optional tinyint(4) default 0 ,
label varchar(255) NULL ,
is_permission_a_b tinyint(4) default 0 ,
is_permission_b_a tinyint(4) default 0,
PRIMARY KEY(id)
);

/* Current_User_Profile */
SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Current_User_Profile';

INSERT IGNORE INTO `civicrm_uf_group` ( `id`, `is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`) VALUES ( @ufGId, 1, 'Individual,Contact', 'Current User Profile', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, 'Current_User_Profile', NULL, NULL, 0);

SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Current_User_Profile';

SET @ufFFNId := '';
SET @ufFLNId := '';
SET @ufFEId := '';
SET @ufFSAId := '';
SET @ufFCId := '';
SET @ufFSPId := '';
SET @ufFPCId := '';

SELECT @ufFFNId := id FROM civicrm_uf_field WHERE field_name = 'first_name' AND uf_group_id = @ufGId;
SELECT @ufFLNId := id FROM civicrm_uf_field WHERE field_name = 'last_name' AND uf_group_id = @ufGId;
SELECT @ufFEId := id FROM civicrm_uf_field WHERE field_name = 'email' AND uf_group_id = @ufGId;
SELECT @ufFSAId := id FROM civicrm_uf_field WHERE field_name = 'street_address' AND uf_group_id = @ufGId;
SELECT @ufFCId := id FROM civicrm_uf_field WHERE field_name = 'city' AND uf_group_id = @ufGId;
SELECT @ufFSPId := id FROM civicrm_uf_field WHERE field_name = 'state_province' AND uf_group_id = @ufGId;
SELECT @ufFPCId := id FROM civicrm_uf_field WHERE field_name = 'postal_code' AND uf_group_id = @ufGId;

INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFFNId, @ufGId, 'first_name', 1, 0, 1, 1, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'First Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFLNId, @ufGId, 'last_name', 1, 0, 1, 2, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Last Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFEId, @ufGId, 'email', 1, 0, 1, 3, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Email (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSAId, @ufGId, 'street_address', 1, 0, 0, 4, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Street Address (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFCId, @ufGId, 'city', 1, 0, 0, 5, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'City (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSPId, @ufGId, 'state_province', 1, 0, 0, 6, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'State (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFPCId, @ufGId, 'postal_code', 1, 0, 0, 7, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Postal Code (Primary)', 'Contact', NULL);

SET @ufJId := '';

SELECT @ufJId := id FROM civicrm_uf_join WHERE module = 'Profile' AND uf_group_id = @ufGId;

INSERT IGNORE INTO `civicrm_uf_join` ( `id`, `is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`) VALUES 
( @ufJId, 1, 'Profile', NULL, NULL, @ufGId , @ufGId );

UPDATE `civicrm_uf_join` SET `weight` = weight+1 ORDER BY id DESC LIMIT 1 ;

/* Other Parent Or Guardian */
SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Other_Parent_Or_Guardian';

INSERT IGNORE INTO `civicrm_uf_group` ( `id`, `is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`) VALUES ( @ufGId, 1, 'Individual,Contact', 'Other Parent Or Guardian', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, 'Other_Parent_Or_Guardian', NULL, NULL, 0);

SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Other_Parent_Or_Guardian';

SET @ufFFNId := '';
SET @ufFLNId := '';
SET @ufFEId := '';
SET @ufFSAId := '';
SET @ufFCId := '';
SET @ufFSPId := '';
SET @ufFPCId := '';

SELECT @ufFFNId := id FROM civicrm_uf_field WHERE field_name = 'first_name' AND uf_group_id = @ufGId;
SELECT @ufFLNId := id FROM civicrm_uf_field WHERE field_name = 'last_name' AND uf_group_id = @ufGId;
SELECT @ufFEId := id FROM civicrm_uf_field WHERE field_name = 'email' AND uf_group_id = @ufGId;
SELECT @ufFSAId := id FROM civicrm_uf_field WHERE field_name = 'street_address' AND uf_group_id = @ufGId;
SELECT @ufFCId := id FROM civicrm_uf_field WHERE field_name = 'city' AND uf_group_id = @ufGId;
SELECT @ufFSPId := id FROM civicrm_uf_field WHERE field_name = 'state_province' AND uf_group_id = @ufGId;
SELECT @ufFPCId := id FROM civicrm_uf_field WHERE field_name = 'postal_code' AND uf_group_id = @ufGId;

INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFFNId, @ufGId, 'first_name', 1, 0, 1, 1, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'First Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFLNId, @ufGId, 'last_name', 1, 0, 1, 2, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Last Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFEId, @ufGId, 'email', 1, 0, 1, 3, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Email', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSAId, @ufGId, 'street_address', 1, 0, 0, 4, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Street Address (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFCId, @ufGId, 'city', 1, 0, 0, 5, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'City (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSPId, @ufGId, 'state_province', 1, 0, 0, 6, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'State (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFPCId, @ufGId, 'postal_code', 1, 0, 0, 7, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Postal Code (Primary)', 'Contact', NULL);

SET @ufJId := '';

SELECT @ufJId := id FROM civicrm_uf_join WHERE module = 'Profile' AND uf_group_id = @ufGId;
  
INSERT IGNORE INTO `civicrm_uf_join` ( `id`, `is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`) VALUES 
( @ufJId, 1, 'Profile', NULL, NULL, @ufGId , @ufGId );

UPDATE `civicrm_uf_join` SET `weight` = weight+1 ORDER BY id DESC LIMIT 1 ;

/* First Emergency Contacts */
SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'First_Emergency_Contacts';

INSERT IGNORE INTO `civicrm_uf_group` ( `id`, `is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`) VALUES ( @ufGId, 1, 'Individual,Contact', 'First Emergency Contacts', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, 'First_Emergency_Contacts', NULL, NULL, 0);

SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'First_Emergency_Contacts';

SET @ufFFNId := '';
SET @ufFLNId := '';
SET @ufFEId := '';
SET @ufFSAId := '';
SET @ufFCId := '';
SET @ufFSPId := '';
SET @ufFPCId := '';

SELECT @ufFFNId := id FROM civicrm_uf_field WHERE field_name = 'first_name' AND uf_group_id = @ufGId;
SELECT @ufFLNId := id FROM civicrm_uf_field WHERE field_name = 'last_name' AND uf_group_id = @ufGId;
SELECT @ufFEId := id FROM civicrm_uf_field WHERE field_name = 'email' AND uf_group_id = @ufGId;
SELECT @ufFSAId := id FROM civicrm_uf_field WHERE field_name = 'street_address' AND uf_group_id = @ufGId;
SELECT @ufFCId := id FROM civicrm_uf_field WHERE field_name = 'city' AND uf_group_id = @ufGId;
SELECT @ufFSPId := id FROM civicrm_uf_field WHERE field_name = 'state_province' AND uf_group_id = @ufGId;
SELECT @ufFPCId := id FROM civicrm_uf_field WHERE field_name = 'postal_code' AND uf_group_id = @ufGId;

INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFFNId, @ufGId, 'first_name', 1, 0, 1, 1, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'First Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFLNId, @ufGId, 'last_name', 1, 0, 1, 2, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Last Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFEId, @ufGId, 'email', 1, 0, 1, 3, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Email', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSAId, @ufGId, 'street_address', 1, 0, 0, 4, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Street Address (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFCId, @ufGId, 'city', 1, 0, 0, 5, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'City (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSPId, @ufGId, 'state_province', 1, 0, 0, 6, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'State (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFPCId, @ufGId, 'postal_code', 1, 0, 0, 7, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Postal Code (Primary)', 'Contact', NULL);

SET @ufJId := '';

SELECT @ufJId := id FROM civicrm_uf_join WHERE module = 'Profile' AND uf_group_id = @ufGId;
  
INSERT IGNORE INTO `civicrm_uf_join` ( `id`, `is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`) VALUES 
( @ufJId, 1, 'Profile', NULL, NULL, @ufGId , @ufGId );

UPDATE `civicrm_uf_join` SET `weight` = weight+1 ORDER BY id DESC LIMIT 1 ;

/* Second Emergency Contacts */
SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Second_Emergency_Contacts';

INSERT IGNORE INTO `civicrm_uf_group` ( `id`, `is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`) VALUES ( @ufGId, 1, 'Individual,Contact', 'Second Emergency Contacts', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, 'Second_Emergency_Contacts', NULL, NULL, 0);

SET @ufGId := '';

SELECT @ufGId := id FROM civicrm_uf_group WHERE name = 'Second_Emergency_Contacts';

SET @ufFFNId := '';
SET @ufFLNId := '';
SET @ufFEId := '';
SET @ufFSAId := '';
SET @ufFCId := '';
SET @ufFSPId := '';
SET @ufFPCId := '';

SELECT @ufFFNId := id FROM civicrm_uf_field WHERE field_name = 'first_name' AND uf_group_id = @ufGId;
SELECT @ufFLNId := id FROM civicrm_uf_field WHERE field_name = 'last_name' AND uf_group_id = @ufGId;
SELECT @ufFEId := id FROM civicrm_uf_field WHERE field_name = 'email' AND uf_group_id = @ufGId;
SELECT @ufFSAId := id FROM civicrm_uf_field WHERE field_name = 'street_address' AND uf_group_id = @ufGId;
SELECT @ufFCId := id FROM civicrm_uf_field WHERE field_name = 'city' AND uf_group_id = @ufGId;
SELECT @ufFSPId := id FROM civicrm_uf_field WHERE field_name = 'state_province' AND uf_group_id = @ufGId;
SELECT @ufFPCId := id FROM civicrm_uf_field WHERE field_name = 'postal_code' AND uf_group_id = @ufGId;

INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFFNId, @ufGId, 'first_name', 1, 0, 1, 1, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'First Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFLNId, @ufGId, 'last_name', 1, 0, 1, 2, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Last Name', 'Individual', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFEId, @ufGId, 'email', 1, 0, 1, 3, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Email', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSAId, @ufGId, 'street_address', 1, 0, 0, 4, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Street Address (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFCId, @ufGId, 'city', 1, 0, 0, 5, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'City (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFSPId, @ufGId, 'state_province', 1, 0, 0, 6, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'State (Primary)', 'Contact', NULL);
INSERT IGNORE INTO `civicrm_uf_field` ( `id`, `uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`) VALUES ( @ufFPCId, @ufGId, 'postal_code', 1, 0, 0, 7, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Postal Code (Primary)', 'Contact', NULL);

SET @ufJId := '';

SELECT @ufJId := id FROM civicrm_uf_join WHERE module = 'Profile' AND uf_group_id = @ufGId;
  
INSERT IGNORE INTO `civicrm_uf_join` ( `id`, `is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`) VALUES 
( @ufJId, 1, 'Profile', NULL, NULL, @ufGId , @ufGId );

UPDATE `civicrm_uf_join` SET `weight` = weight+1 ORDER BY id DESC LIMIT 1 ;