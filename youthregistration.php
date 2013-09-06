<?php

require_once 'youthregistration.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function youthregistration_civicrm_config(&$config) {
  _youthregistration_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function youthregistration_civicrm_xmlMenu(&$files) {
  _youthregistration_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function youthregistration_civicrm_install() {
  $dirRoot =dirname( __FILE__ );
  $dirSQL = $dirRoot . DIRECTORY_SEPARATOR .'sql/EnhancedEventReg.install.sql';
  CRM_Utils_File::sourceSQLFile( CIVICRM_DSN, $dirSQL );
  CRM_Core_Invoke::rebuildMenuAndCaches( );
  return _youthregistration_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function youthregistration_civicrm_uninstall() {
  $dirRoot =dirname( __FILE__ );
  $dirSQL =$dirRoot . DIRECTORY_SEPARATOR .'sql/EnhancedEventReg.uninstall.sql';
  CRM_Utils_File::sourceSQLFile( CIVICRM_DSN,$dirSQL );
  return _youthregistration_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function youthregistration_civicrm_enable() {
  return _youthregistration_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function youthregistration_civicrm_disable() {
  return _youthregistration_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function youthregistration_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _youthregistration_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function youthregistration_civicrm_managed(&$entities) {
  return _youthregistration_civix_civicrm_managed($entities);
}


function youthregistration_civicrm_buildForm( $formName, &$form  )  {
  require_once 'api/api.php';
  $childName  = array( 1 => 'Second', 2 => 'Third', 3 => 'Fourth', 4 => 'Fifth', 5 => 'Sixth', 6 => 'Seventh');
  $paramNames =  array( 'first_name', 'last_name' );
 
  /*if( $formName == 'CRM_Event_Form_Registration_AdditionalParticipant' ) {
    $is_enhanced = CRM_Core_DAO::singleValueQuery( "SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$form->_eventId}" );
    if ( $is_enhanced ) {
	$child = array( 'first_name', 'last_name', 'email-Primary' );
      foreach( $form->_fields as $fieldKey => $fieldValues ) {
	   if (in_array( $fieldKey, $child ) || strstr( $fieldKey , 'custom' )) {
          $partcipantCount = $form->getVar('_name');
          $pCount = explode('_', $partcipantCount);
          $fieldValues['groupTitle'] = $childName[$pCount[1]].' Child';
          $firstChild[$fieldKey] = $fieldValues;
      }
     }       
      $form->assign( 'additionalCustomPre', $firstChild );
    }
  }*/
  
  if ($formName == 'CRM_Event_Form_Registration_Confirm' || $formName == 'CRM_Event_Form_Registration_ThankYou') { 
    $is_enhanced = CRM_Core_DAO::singleValueQuery("SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$form->_eventId}");
    if ($is_enhanced) {
      $profArray = array('Current User Profile' => 1, 'Other Parent Or Guardian' => 2, 'First Emergency Contacts' => 3, 'Second Emergency Contacts' => 4);
      $profiles = CRM_Core_DAO::executeQuery(" SELECT id, title FROM civicrm_uf_group WHERE (title = 'Current User Profile' OR  title = 'Other Parent Or Guardian' OR  title = 'First Emergency Contacts' OR  title = 'Second Emergency Contacts') AND is_active = 1");

      
      $profilesPreExtra = CRM_Core_DAO::executeQuery("SELECT uj.uf_group_id as id, ug.title FROM civicrm_uf_join uj LEFT JOIN civicrm_uf_group ug ON uj.uf_group_id = ug.id WHERE uj.entity_id = {$form->_eventId} AND uj.module ='CiviEvent' AND uj.weight != 1 ");
      
      while ($profilesPreExtra->fetch()) {
        $ids[] = $profilesPreExtra->id;
        $addedProfiles[] = $profilesPreExtra->title;
      }
      
      while($profiles->fetch()) {
        $ids[] = $profiles->id;
        $addedProfiles[] = $profiles->title;
      }
      $form->_fields = array();
      $contacts = $form->getVar('_params');
      foreach ($ids as $profileKey => $profileID) {
        $id[$profileKey] = $profileID;
        $form->buildCustom($id , 'customPost');
        unset($id[$profileKey]);
        $fields = CRM_Core_BAO_UFGroup::getFields($profileID, FALSE, CRM_Core_Action::ADD,
                                                   NULL , NULL, FALSE, NULL,
                                                   FALSE, NULL, CRM_Core_Permission::CREATE,
                                                   'field_name', TRUE);
        foreach ($fields as $key => $value) { 

          if(array_key_exists($value['groupTitle'],$profArray)) {
            $profileKey = $profArray[$value['groupTitle']];
          }
          $newKey = $key;
          if (!empty($profileKey)) {
            if (strstr($key , 'custom')) { 
              $newKey = $key.'#'.$profileKey;
            } else {
              $newKey = $key.$profileKey;
            }
          }
          $postGroupTitle[$profileID]['groupTitle'] = $value['groupTitle'];
          if (array_key_exists($newKey, $contacts[0])) {
            $customFields[$newKey] = $contacts[0][$newKey];
            // FIXME for multi choice custom fields (eg. select, radio, checkbox, multi select)
            if (!empty($form->_elements[$form->_elementIndex[$key]]->_elements)) {
              $multipleData = array();
              if (is_array($contacts[0][$newKey])) {
                foreach ($contacts[0][$newKey] as $multiKey => $multiVal) {
                  if (!empty($multiVal)) {
                    foreach ($form->_elements[$form->_elementIndex[$key]]->_elements as $elementKey => $elementValue) {
                      if ($elementValue->_attributes['id'] == $multiKey) {
                        $multipleData[] = $elementValue->_text;
                      }
                    }
                  }
                }
                $contacts[0][$newKey] = implode(', ', $multipleData);
              } else {
                foreach ($form->_elements[$form->_elementIndex[$key]]->_elements as $elementKey => $elementValue) {
                  if ($elementValue->_attributes['value'] ==$contacts[0][$newKey] ) { 
                    $contacts[0][$newKey] = $elementValue->_text;
                  }
                }
              }
            }
            elseif (!empty($form->_elements[$form->_elementIndex[$key]]->_options)) {
              $multipleData = array();
              if (is_array($contacts[0][$newKey])) {
                foreach ($form->_elements[$form->_elementIndex[$key]]->_options as $elementKey => $elementValue) {
                  foreach ($contacts[0][$newKey] as $multiKey => $multiVal) {
                    if ($elementValue['attr']['value'] == $multiVal) {
                      $multipleData[] = $elementValue['text'];
                    }
                  }
                }
                $contacts[0][$newKey] = implode(', ', $multipleData);
              } else {
                foreach ($form->_elements[$form->_elementIndex[$key]]->_options as $elementKey => $elementValue) {
                  if ($elementValue['attr']['value'] ==$contacts[0][$newKey] ) { 
                    $contacts[0][$newKey] = $elementValue['text'];
                  }
                }
              }
            }
            $customPost[$profileID][$value['title']] = $contacts[0][$newKey];
          }
          unset($form->_rules[$key]);
        }
      }
    
      $custom['CustomPostGroupTitle'] = $postGroupTitle;
      $custom['CustomPost'] = $customPost;

      $profilesPre = CRM_Core_DAO::executeQuery("SELECT uj.uf_group_id as id, ug.title FROM civicrm_uf_join uj LEFT JOIN civicrm_uf_group ug ON uj.uf_group_id = ug.id WHERE uj.entity_id = {$form->_eventId} AND uj.module ='CiviEvent' AND uj.weight = 1 ");
      
      while ($profilesPre->fetch()) {
        $profilePre = $profilesPre->id;
        $addedProfiles[] = $profilesPre->title;
      }
      
      $form->buildCustom($profilePre , 'customPre');
      $fields = CRM_Core_BAO_UFGroup::getFields($profilePre, FALSE, CRM_Core_Action::ADD,
                                                NULL , NULL, FALSE, NULL,
                                                FALSE, NULL, CRM_Core_Permission::CREATE,
                                                'field_name', TRUE);
      foreach ($fields as $fieldKey => $fieldValues) {
        $newKey = $fieldKey;
        if ($fields[$fieldKey]['groupTitle'] == 'New Individual') {
          $fields[$fieldKey]['groupTitle'] = $preGroupTitle = 'First Child';
        } else {
          $preGroupTitle = $fields[$fieldKey]['groupTitle'];
          if( array_key_exists($fieldValues['groupTitle'], $profArray)) {
            if (strstr($fieldKey , 'custom')) { 
              $newKey = $fieldKey.'#'.$profArray[$fieldValues['groupTitle']];
            } else {
              $newKey = $fieldKey.$profArray[$fieldValues['groupTitle']];
            }
          }
        }
        // FIXME for multi choice custom fields
        if (!empty($form->_elements[$form->_elementIndex[$fieldKey]]->_elements)) {
          $multipleData = array();
          if (is_array($contacts[0][$newKey])) {
            foreach ($contacts[0][$newKey] as $multiKey => $multiVal) {
              if (!empty($multiVal)) {
                foreach ($form->_elements[$form->_elementIndex[$fieldKey]]->_elements as $elementKey => $elementValue) {
                  if ($elementValue->_attributes['id'] == $multiKey) {
                    $multipleData[] = $elementValue->_text;
                  }
                }
              }
            }
            $contacts[0][$newKey] = implode(', ', $multipleData);
          } else {
            foreach ($form->_elements[$form->_elementIndex[$fieldKey]]->_elements as $elementKey => $elementValue) {
              if ($elementValue->_attributes['value'] == $contacts[0][$newKey]) {
                $contacts[0][$newKey] = $elementValue->_text;
              }
            }
          }
        }
        elseif (!empty($form->_elements[$form->_elementIndex[$fieldKey]]->_options)) {
          $multipleData = array();
          if (is_array($contacts[0][$newKey])) {
            foreach ($form->_elements[$form->_elementIndex[$fieldKey]]->_options as $elementKey => $elementValue) {
              foreach ($contacts[0][$newKey] as $multiKey => $multiVal) {
                if ($elementValue['attr']['value'] == $multiVal) {
                  $multipleData[] = $elementValue['text'];
                }
              }
            }
            $contacts[0][$newKey] = implode(', ', $multipleData);
          } else {
            foreach ($form->_elements[$form->_elementIndex[$fieldKey]]->_options as $elementKey => $elementValue) {
              if ($elementValue['attr']['value'] == $contacts[0][$newKey]) {
                $contacts[0][$newKey] = $elementValue['text'];
              }
            }
          }
        }

        $customPre[$fieldValues['title']] = $contacts[0][$newKey];
        $customFields[$newKey] = $contacts[0][$newKey];
        $form->_elements[$form->_elementIndex[$fieldKey]]->_name = $newKey;
        $form->_elements[$form->_elementIndex[$fieldKey]]->_label = $fieldValues['title'];
        $fields[$fieldKey]['name'] = $newKey;
      }
      // assign primaryParticipantProfile to display on Confirm and Thank you page
      $custom['CustomPreGroupTitle'] = $preGroupTitle;
      $custom['CustomPre'] = $customPre;
      $form->assign('primaryParticipantProfile', $custom);
      foreach ($contacts as $contactKey => $contactValue) {
        if ($contactKey != 0) {
          $addParticipant = $contactKey+1;
          foreach ($contactValue as $contKey => $contVal) {
            if (array_key_exists($contKey, $form->_elementIndex)) {
              //FIXME for additional profile custom values 
              if (!empty($form->_elements[$form->_elementIndex[$contKey]]->_elements)) {
                $multipleData = array();
                if (is_array($contVal)) {
                  foreach ($contVal as $multiKey => $multiVal) {
                    if (!empty($multiVal)) {
                      foreach ($form->_elements[$form->_elementIndex[$contKey]]->_elements as $elementKey => $elementValue) {
                        if ($elementValue->_attributes['id'] == $multiKey) {
                          $multipleData[] = $elementValue->_text;
                        }
                      }
                    }
                  }
                  $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = implode(', ', $multipleData);
                } else {
                  foreach ($form->_elements[$form->_elementIndex[$contKey]]->_elements as $elementKey => $elementValue) {
                    if ($elementValue->_attributes['value'] == $contVal) {
                      $contacts[0][$newKey] = $elementValue->_text;
                      $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = $elementValue->_text;
                    }
                  }
                }
              }
              elseif (!empty($form->_elements[$form->_elementIndex[$contKey]]->_options)) {
                $multipleData = array();
                if (is_array($contVal)) {
                  foreach ($form->_elements[$form->_elementIndex[$contKey]]->_options as $elementKey => $elementValue) {
                    foreach ($contVal as $multiKey => $multiVal) {
                      if ($elementValue['attr']['value'] == $multiVal) {
                        $multipleData[] = $elementValue['text'];
                        $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = $elementValue['text'];
                      }
                    }
                  }
                  $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = implode(', ', $multipleData);
                } else {
                  foreach ($form->_elements[$form->_elementIndex[$contKey]]->_options as $elementKey => $elementValue) {
                    if ($elementValue['attr']['value'] == $contVal) {
                      $contacts[0][$newKey] = $elementValue['text'];
                      $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = $elementValue['text'];
                    }
                  }
                }
              } else {
                foreach ($form->_elements[$form->_elementIndex[$contKey]] as $addKey  => $addVal) {
                  if ($form->_elements[$form->_elementIndex[$contKey]]->_type != 'hidden') {
                    if (!empty($contVal) && !empty($form->_elements[$form->_elementIndex[$contKey]]->_label)) {
                      $additionalParticipants[$addParticipant]['additionalCustomPre'][$form->_elements[$form->_elementIndex[$contKey]]->_label] = $contVal;
                    }
                  }
                }
              }
            }
            $additionalParticipants[$addParticipant]['additionalCustomPreGroupTitle'] = $childName[$contactKey].' Child';
          }
        }
      }
      if (!empty($additionalParticipants)) {
        $form->assign('addParticipantProfile', $additionalParticipants);
      }
      
      $contacts = $form->getVar('_params');
      foreach ($customFields as $fieldsKey => $fieldsValue) {
        if(array_key_exists($fieldsKey, $contacts[0])) {
          $form->_submitValues[$fieldsKey] = $contacts[0][$fieldsKey];
        }
      }
      $config = CRM_Core_Smarty::singleton();
      $config->_form = $form;
      $config->_addedProfiles = $addedProfiles;
      $config->_is_shareAdd = $contacts[0]['is_shareAdd'];
      $config->_is_spouse   = $contacts[0]['is_spouse'];
    } 
  }

  
  if ($formName == 'CRM_Event_Form_Registration_Register') {
    $is_enhanced = CRM_Core_DAO::singleValueQuery("SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$form->_eventId}");
    
    if ($is_enhanced) {
      $profArray = array('Current User Profile' => 1, 'Other Parent Or Guardian' => 2, 'First Emergency Contacts' => 3, 'Second Emergency Contacts' => 4, 'New Individual' => 6);
      
      $profiles = CRM_Core_DAO::executeQuery(" SELECT id FROM civicrm_uf_group WHERE (title = 'Current User Profile' OR  title = 'Other Parent Or Guardian' OR  title = 'First Emergency Contacts' OR  title = 'Second Emergency Contacts') AND is_active = 1");
      
      while ($profiles->fetch()) {
        $ids[] = $profiles->id;
      }
      $option = array( '1' =>'Yes', '0' => "No" );
      $form->addRadio('is_spouse' , ts( 'Is my Spouse' ),$option, NULL,  NULL, FALSE);
      $form->addRadio('is_shareAdd',ts( 'Shares My Address' ),$option, NULL,  NULL, FALSE);
      $form->assign('addshareNspouse' , $is_enhanced );
      $contacts = $form->_submitValues;
      foreach ($ids as $profileKey => $profileID) {
        $id[$profileKey] = $profileID;
        $form->buildCustom($id , 'customPost');
        unset($id[$profileKey]);
        $fields = CRM_Core_BAO_UFGroup::getFields($profileID, FALSE, CRM_Core_Action::ADD,
                                                  NULL, NULL, FALSE, NULL,
                                                  FALSE, NULL, CRM_Core_Permission::CREATE,
                                                  'field_name', TRUE);
        
        foreach ($fields as $key => $value) {
          $profileKey = $profArray[$value['groupTitle']];
          
          $newKey = $key;
          if (!empty($profileKey)) {
            if (strstr($key , 'custom')){ 
              $newKey = $key.'#'.$profileKey;
            } else {
              $newKey = $key.$profileKey;
            }
          }
          $form->_fields[$newKey] = $form->_fields[$key];
          $form->_elementIndex[$newKey] = $form->_elementIndex[$key];
          $form->_elements[$form->_elementIndex[$key]]->_attributes['name'] = $newKey;
          $form->_elements[$form->_elementIndex[$key]]->_label = $value['title'];
          $form->_elements[$form->_elementIndex[$key]]->_flagFrozen = NULL;
          if ($form->_fields[$key]['is_required'] == 1) {
            $form->_required[] = $newKey;
          }

          if (!empty($form->_submitValues)) {
            if (strstr($newKey, (string)$profArray['New Individual'])) {
              $form->_elements[$form->_elementIndex[$key]]->_attributes['value'] = $form->_submitValues[$key];
            } else {
              $form->_elements[$form->_elementIndex[$key]]->_attributes['value'] = $form->_submitValues[$newKey];
            }
          }
          $form->_elements[$form->_elementIndex[$key]]->_name = $newKey;
          
          $fields[$key]['name'] = $newKey;
          $form->_fields[$newKey] = $fields[$key];
          unset($form->_defaultValues[$key]);
          unset($form->_defaults[$key]);
          unset($form->_elementIndex[$key]);
          unset($form->_fields[$key]);
          unset($form->_rules[$key]);
        }
      }
      $allCount = $individualCount = 0; $head = NULL;
      foreach ($form->_fields as $fieldKey => $fieldValue) {
        $allCount++;
        if ($form->_fields[$fieldKey]['groupTitle'] == 'New Individual') {
          if (!isset($head)) {
            $head = $allCount - 1;
          }
          $individualCount++;
          if (strstr($fieldKey , 'custom')) {
            $newKey = rtrim($fieldKey, $profArray['New Individual']);
            $newKey = rtrim($newKey, '#');
          } else {
            $newKey = rtrim($fieldKey, $profArray['New Individual']);
          }
          $form->_fields[$fieldKey]['groupTitle'] = 'First Child';
          $form->_fields[$fieldKey]['name'] = $newKey;
          $form->_elementIndex[$newKey] = $form->_elementIndex[$fieldKey];
          $individual[$newKey] = $form->_fields[$fieldKey];
          $form->_elements[$form->_elementIndex[$fieldKey]]->_attributes['name'] = $newKey;
          $form->_elements[$form->_elementIndex[$fieldKey]]->_name = $newKey;
          unset($form->_fields[$fieldKey]);
          unset($form->_elementIndex[$fieldKey]);
        } 
      }
      
      if (isset($individual)) {
        $pre = array_slice($form->_fields, 0, $head);
        $tail = array_slice($form->_fields, $head , $allCount - $individualCount);
        $form->_fields = array_merge($pre, $individual, $tail);
      }

      if (!empty($contacts)) {
        foreach ($form->_fields as $field => $value) {
          if (array_key_exists('multiple', (array) $form->_elements[$form->_elementIndex[$field]]->_attributes)) {
            $form->_elements[$form->_elementIndex[$field]]->_values = $contacts[$field];
            $form->_elements[$form->_elementIndex[$field]]->_attributes['value'] = implode( ',', $contacts[$field] );
          } elseif (array_key_exists('_elements', (array) $form->_elements[$form->_elementIndex[$field]])) {
            foreach ($form->_elements[$form->_elementIndex[$field]]->_elements as $readioKey => $radioVal) {
              if ($radioVal->_type == 'radio') {
                if ($contacts[$field] == $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['value']) {
                  $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked'] = 'checked';
                } else {
                  unset($form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked']);
                }
              }
              if ($radioVal->_type == 'checkbox') {
                if ($contacts[$field][$form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['id']]  == 1) {
                  $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked'] = 'checked';
                } else {
                  unset($form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked']);
                }
              }
            }
          } else {
            $form->_elements[$form->_elementIndex[$field]]->_values = array($contacts[$field]);
          }
        }
      }
      
      $profilesPres = CRM_Core_DAO::executeQuery("SELECT uj.uf_group_id, ug.title FROM civicrm_uf_join uj LEFT JOIN civicrm_uf_group ug ON uj.uf_group_id = ug.id WHERE uj.entity_id = {$form->_eventId} AND uj.module ='CiviEvent' AND uj.weight = 1");
      while( $profilesPres->fetch( ) ) {
        $primaryTitle = $profilesPres->title;
        $profilesPre = $profilesPres->uf_group_id;
      }
      $myFields = $form->_fields;
      foreach ($myFields as $myKey => $myVal) {
        if ($profilesPres->title == $myVal['groupTitle']) {
          unset($myFields[$myKey]);
        }
      }
      $form->assign( 'customPost', $myFields );
      //$form->assign('customPost', $form->_fields);
      
      $form->buildCustom($profilesPre , 'customPre');
      $fields = CRM_Core_BAO_UFGroup::getFields($profilesPre, FALSE, CRM_Core_Action::ADD,
                                                NULL , NULL, FALSE, NULL,
                                                FALSE, NULL, CRM_Core_Permission::CREATE,
                                                'field_name', TRUE);

      foreach ($fields as $fieldKey => $fieldValues) {
        $newKey = $fieldKey;
          if( array_key_exists($fieldValues['groupTitle'], $profArray)) {
            if (strstr($fieldKey , 'custom')) { 
              $newKey = $fieldKey.'#'.$profArray[$fieldValues['groupTitle']];
            } else {
              $newKey = $fieldKey.$profArray[$fieldValues['groupTitle']];            }
          }

        $form->_fields[$newKey] = $form->_fields[$fieldKey];
        $form->_elementIndex[$newKey] = $form->_elementIndex[$fieldKey];
        $form->_elements[$form->_elementIndex[$fieldKey]]->_attributes['name'] = $newKey;
        $form->_elements[$form->_elementIndex[$fieldKey]]->_flagFrozen = null;
        if (!empty($form->_submitValues)) {
          $form->_elements[$form->_elementIndex[$fieldKey]]->_attributes['value'] = $form->_submitValues[$newKey];
        }
        $form->_elements[$form->_elementIndex[$fieldKey]]->_name = $newKey;
        $fields[$fieldKey]['name'] = $newKey;
        $firstChild[$newKey] = $fields[$fieldKey];
      }

      if (!empty($contacts)) {
        foreach ($firstChild as $field => $value) { 
          if (array_key_exists('multiple', $form->_elements[$form->_elementIndex[$field]]->_attributes)) {
            $form->_elements[$form->_elementIndex[$field]]->_values = $contacts[$field];
            $form->_elements[$form->_elementIndex[$field]]->_attributes['value'] = implode( ',', $contacts[$field] );
          } elseif (array_key_exists('_elements', (array) $form->_elements[$form->_elementIndex[$field]])) {
            foreach ($form->_elements[$form->_elementIndex[$field]]->_elements as $readioKey => $radioVal) {
              if ($radioVal->_type == 'radio') {
                if ($contacts[$field] == $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['value']) {
                  $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked'] = 'checked';
                } else {
                  unset($form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked']);
                }  
              }
            
              if ($radioVal->_type == 'checkbox') {  
                if ($contacts[$field][$form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['id']]  == 1) {
                  $form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked'] = 'checked';
                } else {
                  unset($form->_elements[$form->_elementIndex[$field]]->_elements[$readioKey]->_attributes['checked']);
                }
              } 
            }
          } else {
            $form->_elements[$form->_elementIndex[$field]]->_values = array($contacts[$field]);
          }
        }
      }
      $form->assign('customPre', $firstChild);
    }
  }

  if( $formName == 'CRM_Event_Form_ManageEvent_Registration' ) {
    $form->addElement( 'checkbox', 'is_enhanced', ts( 'Use Enhanced Registration?' ) );
    $eventID = $form->_id;
    $is_enhanced = null;
    $is_multiple = null;
    $is_enhanced = CRM_Core_DAO::singleValueQuery( "SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = $eventID" );
    $is_multiple = CRM_Core_DAO::singleValueQuery( "SELECT is_multiple_registrations FROM civicrm_event WHERE id = $eventID" );
    $defaults['is_enhanced'] = $is_enhanced;
    $defaults['is_multiple_registrations'] = $is_multiple;
    $form->setDefaults( $defaults );
    //Profile Chooser
    require_once 'CRM/Core/BAO/UFGroup.php';
    require_once 'CRM/Contact/BAO/ContactType.php';
    $types = array_merge(array('Contact', 'Individual', 'Participant'),
                         CRM_Contact_BAO_ContactType::subTypes('Individual')
                         );
    $profiles = CRM_Core_BAO_UFGroup::getProfiles($types);
    //$chimp = array('Chimp1','Chimp2','Chimp3');

    $mainProfiles = array(
                          '' => ts('- select -')) + $profiles;

    //$form->add('select', 'enhanced_profile', ts('Include Profile') . '<br />' . ts('(top of page)'), $chimp);
    /*$form->add('text', 'enhanced_profiles', ts('Include Profile')); */
    /* $form->add('textarea', 'enhanced_profiles', ts('Include Profile'), $attributes['enhanced_profiles']); */

  
    $is_enhanced_profile = 1;
    $defaults['is_enhanced_profile'] = $is_enhanced_profile;
    $form->setDefaults( $defaults );    
  }
}

function youthregistration_civicrm_validate( $formName, &$fields, &$files, &$form )  {

  if( $formName == 'CRM_Event_Form_ManageEvent_Registration' ) {
    $eventId = $form->_id;
    $isenhanced = $form->_submitValues['is_enhanced'];
    if ($isenhanced) {
      //check that the selected profiles have either firstname+lastname or email required
      $profileIds = array(
        CRM_Utils_Array::value('custom_pre_id', $form->_submitValues),
        CRM_Utils_Array::value('custom_post_id', $form->_submitValues),
      );
      $additionalProfileIds = array(
        CRM_Utils_Array::value('additional_custom_pre_id', $form->_submitValues),
        CRM_Utils_Array::value('additional_custom_post_id', $form->_submitValues),
      );

      //additional profile fields default to main if not set
      if (!is_numeric($additionalProfileIds[0])) {
        $additionalProfileIds[0] = $profileIds[0];
      }
      if (!is_numeric($additionalProfileIds[1])) {
        $additionalProfileIds[1] = $profileIds[1];
      }

      //add multiple profiles if set
      CRM_Event_Form_ManageEvent_Registration::addMultipleProfiles($profileIds, $form->_submitValues, 'custom_post_id_multiple');
      CRM_Event_Form_ManageEvent_Registration::addMultipleProfiles($additionalProfileIds, $form->_submitValues, 'additional_custom_post_id_multiple');

      $isProfileComplete = isProfileComplete($profileIds);
      $isAdditionalProfileComplete = isProfileComplete($additionalProfileIds);
      if (!$isProfileComplete) {
        $errors['custom_pre_id'] = ts("Please include a Profile for online registration that contains a First Name + Last Name fields.");
      }
      if (!$isAdditionalProfileComplete) {
        $errors['additional_custom_pre_id'] = ts("Please include a Profile for online registration of additional participants that contains a First Name + Last Name fields.");
      }
    }
  }

  if( $formName == 'CRM_Event_Form_Registration_Register' ) {
    $isenhanced = CRM_Core_DAO::singleValueQuery( "SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$form->_eventId}" );
    if ($isenhanced) {
      foreach ($form->_fields as $name => $fld) {
        if ($fld['is_required'] &&
            CRM_Utils_System::isNull(CRM_Utils_Array::value($name, $fields))
            ) {
          $errors[$name] = ts('%1 is a required field.', array(1 => $fld['title']));
        }
        if (substr($name, 0, 5) == 'email' && CRM_Utils_Array::value($name, $fields)) {
          $valid = filter_var($form->_submitValues[$name], FILTER_VALIDATE_EMAIL);
          if(!$valid) {
            $errors[$name] = ts('Please enter a valid %1.', array(1 => $fld['title']));
          }
        }
      }
    }
  }

//   if( $formName == 'CRM_Event_Form_Registration_Register' ) {
//     $is_enhanced = CRM_Core_DAO::singleValueQuery( "SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$form->_eventId}" );
//     if ( $is_enhanced ) {
//       $getContactsParams0 = array(
//                                   'first_name' => $form->_submitValues['first_name0'] ,
//                                   'last_name' => $form->_submitValues['last_name0'] ,
//                                   'contact_type' => 'Individual',
//                                   'email' => $form->_submitValues['email0'] ,
//                                   'version' => 3
//                                   );
 
//       require_once 'api/api.php';
//       $getContactsResult0 = civicrm_api('contact', 'get', $getContactsParams0 );
//       if( $getContactsResult0['values'] ) {
//         $errors['email0'] = ts( 'Contact is already exists!' );
//       }

//       $getContactsParams1 = array(
//                                   'first_name' => $form->_submitValues['first_name1'] ,
//                                   'last_name' => $form->_submitValues['last_name1'],
//                                   'contact_type' => 'Individual',
//                                   'email' => $form->_submitValues['email1'] ,
//                                   'version' => 3
//                                   );
    
//       $getContactsResult1 = civicrm_api('contact', 'get', $getContactsParams1 );
//       if( $getContactsResult1['values'] ) {
//         $errors['email1'] = ts( 'Contact is already exists!' );
//       }

//       $getContactsParams2 = array(
//                                   'first_name' => $form->_submitValues['first_name2']  ,
//                                   'last_name' => $form->_submitValues['last_name2'],
//                                   'contact_type' => 'Individual',
//                                   'email' => $form->_submitValues['email2'],
//                                   'version' => 3
//                                   );
//       require_once 'api/api.php';
//       $getContactsResult2 = civicrm_api('contact', 'get', $getContactsParams2 );
//       if( $getContactsResult2['values'] ) {
//         $errors['email2'] = ts( 'Contact is already exists!' );
//       }

//       $getContactsParams3 = array(
//                                   'first_name' => $form->_submitValues['first_name3'] ,
//                                   'last_name' => $form->_submitValues['last_name3'],
//                                   'contact_type' => 'Individual',
//                                   'email' => $form->_submitValues['email3'],
//                                   'version' => 3
//                                   );
//       require_once 'api/api.php';
//       $getContactsResult3 = civicrm_api('contact', 'get', $getContactsParams3 );
//       if( $getContactsResult3['values'] ) {
//         $errors['email3'] = ts( 'Contact is already exists!' );
//       }
//     }
//   }
  return empty($errors) ? true : $errors;
}

function youthregistration_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'Participant' && $op == 'create') {
    require_once 'api/api.php';
    $config =& CRM_Core_Smarty::singleton( );
    $form   = $config->_form;
    $addedProfiles = $config->_addedProfiles;
    $parts  = count($form->_part);
    $config->_participants[] = $objectId;
    $participants = $config->_participants;
    $config->_pContactId[$objectId] = $objectRef->contact_id;
    $pContactIds = $config->_pContactId;
    $pCount      = count($participants);
    $is_shareAdd = $config->_is_shareAdd;
    $is_spouse   = $config->_is_spouse;
    if ($parts == $pCount) {
      $is_enhanced = CRM_Core_DAO::singleValueQuery("SELECT is_enhanced FROM civicrm_event_enhanced WHERE event_id = {$objectRef->event_id}");
      if ($is_enhanced) {
        $profArray = array('Current User Profile' => 1, 'Other Parent Or Guardian' => 2, 'First Emergency Contacts' => 3, 'Second Emergency Contacts' => 4);
        // skip contact creation if profile does not present
        foreach ($profArray as $profileKey => $profileValues) {
          if (!in_array( $profileKey, $addedProfiles)) {
            unset($profArray[$profileKey]);
          }
        }
        $createContactsResult2 = $createContactsResult3 = $createContactsResult4 = NULL;
        $relationshipTypeDAO = new CRM_Contact_BAO_RelationshipType();
        $relationshipTypeDAO->find();
        while ($relationshipTypeDAO->fetch()) {
          $relationshipTypes[$relationshipTypeDAO->name_a_b] = $relationshipTypeDAO->id;
        }
        $participantIds = $participants;
        foreach ($participantIds as $partKey => $partID) {
          $contactA = $pContactIds[$partID];
          unset($participantIds[$partKey]);
          foreach ($participantIds as $partKey => $partID) {
            $contactB = $pContactIds[$partID];
            $siblingParams = array( 
                              'contact_id_a' => $contactA,
                              'contact_id_b' => $contactB,
                              'relationship_type_id' => $relationshipTypes['Sibling of'],
                              'is_active' => 1,
                              'version' => 3);
            $siblingResult = civicrm_api('relationship', 'create', $siblingParams);
          }
        }
    
        $participantIds = $participants;
        foreach ($participantIds as $key => $pID) {
          if ($key == 0) {
            $otherParams = array();
            foreach ($profArray as $profKey) {
              $checkDupResult = $addressResult = array();
              if (!empty($form->_submitValues['first_name'.$profKey]) && !empty($form->_submitValues['last_name'.$profKey]) && !empty($form->_submitValues['email-Primary'.$profKey])) {
                $createContactsParams = 'createContactsParams'.$profKey;
                $createContactsResult = 'createContactsResult'.$profKey;
                $contactsCustomParams = $otherParams = array();
                $checkData = array(
                                   'first_name' => $form->_submitValues['first_name'.$profKey] ,
                                   'last_name' => $form->_submitValues['last_name'.$profKey],
                                   'contact_type' => 'Individual',
                                   'email' => $form->_submitValues['email-Primary'.$profKey],
                                   'version' => 3);

                $checkDupResult = civicrm_api('contact', 'get', $checkData);
                if (!empty($checkDupResult['values'])) {
                  $checkData['id'] = CRM_Utils_Array::value('id', $checkDupResult);
                  $addressParams['contact_id'] = CRM_Utils_Array::value('id', $checkDupResult);
                  $addressParams['version']    = 3;
                  $addressParams['location_type_id'] = 1;
                  $addressResult = civicrm_api( 'address', 'get', $addressParams );
                  if (!empty($addressResult['values'])) {
                    $otherParams[$profKey]['api.address.create']['id'] = CRM_Utils_Array::value('id', $addressResult);
                  }
                }
              }
              $$createContactsParams = $checkData;
            
              foreach ($form->_submitValues as $contactsKeys => $contactsValues) {
                $search = '#'.$profKey;
                if (strstr($contactsKeys, $search)) {
                  $keyResult = explode('#', $contactsKeys);
                  $newKey = $keyResult[0];
                  $contactsCustomParams[$newKey] = $contactsValues;
                } else {
                  if (! strstr($contactsKeys, 'custom') && ! strstr($contactsKeys, 'first_name') && ! strstr($contactsKeys, 'last_name') && ! strstr($contactsKeys, 'email')) {
                    $keyResult = explode('-', $contactsKeys);
                    $newKey = $keyResult[0];
                    if (strstr($contactsKeys, (string)$profKey)) {
                      $otherParams[$profKey]['api.address.create'][$newKey] = $contactsValues;
                      if (strstr($contactsKeys, 'state_province')) {
                        $otherParams[$profKey]['api.address.create']['state_province_id'] = $contactsValues;
                      }
                      $otherParams[$profKey]['api.address.create']['location_type_id'] = 1;
                    }
                  }
                }
              }
              if ($profKey == $profArray['Other Parent Or Guardian'] && $is_shareAdd) {
                $addressGet['contact_id'] =  $createContactsResult1['id'];
                $addressGet['version']    =  3;
                $addressGet['location_type_id'] = 1;
                $result = civicrm_api('address','get' , $addressGet);
                $otherParams[$profKey]['api.address.create']['master_id'] = $result['id'];
                //$otherParams[$profKey]['api.address.create']['location_type_id'] = 1;
                $shareOtherParams = $otherParams[$profKey];
                $shareOtherParams['id'] = $pContactIds[$pID];
                $shareOtherParams['version'] = 3;
                $childAddress = civicrm_api('contact', 'create', $shareOtherParams);
              }
              $contactData = $$createContactsParams;
              if (!empty($contactsCustomParams)) {
                $contactData = array_merge($contactData, $contactsCustomParams );
              }
              if (is_array($otherParams) && !empty($otherParams)) {
                $contactData = array_merge($contactData, $otherParams[$profKey]);
              }

              $contactsCustomParams = array();
              $$createContactsResult = civicrm_api('contact', 'create', $contactData);
            }
            $child   = $pContactIds[$pID];
            $current = $createContactsResult1['id'];
            $other = $firstEmergency = $secondEmergency = null;
            if (isset($createContactsResult2)) {
              $other   = $createContactsResult2['id'];
            }
            if ($createContactsResult3) {
              $firstEmergency  = $createContactsResult3['id'];
            }
            if ($createContactsResult4) {
              $secondEmergency = $createContactsResult4['id'];
            }
            if ($is_shareAdd == 1) { 
              if ($form->_submitValues['last_name'.$profArray['Current User Profile']] == $form->_submitValues['last_name2']) {
                $houseHoldName = $form->_submitValues['first_name'.$profArray['Current User Profile']].' & '.$form->_submitValues['first_name'.$profArray['Other Parent Or Guardian']].' '.$form->_submitValues['last_name'.$profArray['Current User Profile']].' Household';
              } else {
                $houseHoldName = $form->_submitValues['first_name'.$profArray['Current User Profile']].' '.$form->_submitValues['last_name'.$profArray['Current User Profile']].', '.$form->_submitValues['first_name'.$profArray['Other Parent Or Guardian']].' '.$form->_submitValues['last_name'.$profArray['Other Parent Or Guardian']].' Household';
              }
              
              $createHouseholdParams = array(
                                         'household_name' => $houseHoldName,
                                         'contact_type' => 'Household',
                                         'email' => $form->_submitValues['email-Primary'.$profArray['Current User Profile']],
                                         'version' => 3);
              $getHouseholdResult = civicrm_api('contact', 'get', $createHouseholdParams);
              if (!empty($getHouseholdResult['values'])) {
                $createHouseholdParams['id'] = $getHouseholdResult['id'];
              }
              if (!empty( $shareOtherParams)) {
                unset($shareOtherParams['id']);
                $createHouseholdParams= array_merge($createHouseholdParams, $shareOtherParams);
              }
              $createHouseholdResult = civicrm_api('contact', 'create', $createHouseholdParams);
        
              $household = $createHouseholdResult['id'];
              $householdHeadRelationshipParams = array( 
                                                   'contact_id_a' => $current,
                                                   'contact_id_b' => $household,
                                                   'relationship_type_id' => $relationshipTypes['Head of Household for'],
                                                   'is_permission_a_b' => 1,
                                                   'is_permission_b_a' => 1,
                                                   'is_active' => 1,
                                                   'version' => 3);
          
              $householdCurrentHeadRelationshipResult = civicrm_api('relationship', 'create', $householdHeadRelationshipParams);
          
              $householdHeadRelationshipParams['relationship_type_id'] = $relationshipTypes['Household Member of'];
              $householdCurrentHeadRelationshipResult = civicrm_api( 'relationship', 'create', $householdHeadRelationshipParams );

              $householdHeadRelationshipParams['contact_id_a'] = $other;
              $householdHeadRelationshipParams['relationship_type_id'] = $relationshipTypes['Household Member of'];
              $householdOtherHeadRelationshipResult = civicrm_api('relationship', 'create', $householdHeadRelationshipParams);
          
              $householdMemberRelationshipParams = array( 
                                                     'contact_id_a' => $child,
                                                     'contact_id_b' => $household,
                                                     'relationship_type_id' => $relationshipTypes['Household Member of'],
                                                     'is_permission_b_a' => 1,
                                                     'is_active' => 1,
                                                     'version' => 3);
        
              $householdMemberRelationshipResult = civicrm_api('relationship', 'create', $householdMemberRelationshipParams);
            }
      
            if ($is_spouse == 1) {
              $spouseRelationshipParams = array( 
                                            'contact_id_a' => $current,
                                            'contact_id_b' => $other,
                                            'relationship_type_id' => $relationshipTypes['Spouse of'],
                                            'is_active' => 1,
                                            'is_permission_a_b' => 1,
                                            'is_permission_b_a' => 1,
                                            'version' => 3);
              $spouseRelationshipResult = civicrm_api('relationship', 'create', $spouseRelationshipParams);
            }
       
            $parentRelationshipParams = array( 
                                          'contact_id_a' => $child,
                                          'contact_id_b' => $current,
                                          'relationship_type_id' => $relationshipTypes['Child of'],
                                          'is_active' => 1,
                                          'is_permission_b_a' => 1,
                                          'version' => 3);
      
            $parentRelationshipResult = civicrm_api('relationship', 'create', $parentRelationshipParams);
      
            // create relationship between child and other parent
            if ($other) {
              $otherParentRelationshipParams = array( 
                                                 'contact_id_a' => $child,
                                                 'contact_id_b' => $other,
                                                 'relationship_type_id' => 1,
                                                 'is_active' => 1,
                                                 'is_permission_b_a' => 1,
                                                 'version' => 3);
              $otherParentRelationshipResult = civicrm_api('relationship', 'create', $otherParentRelationshipParams);
            }
            if ($firstEmergency && $secondEmergency) {
              // create a new relationship type 'is emergency contact of'
              $emergencyTypeParams = array( 
                                       'name_a_b' => 'emergency contact is',
                                       'name_b_a' => 'emergency contact for',
                                       'contact_type_a' => 'Individual',
                                       'contact_type_b' => 'Individual',
                                       'is_reserved' => 0,
                                       'is_active' => 1,
                                       'version' => 3);
      
              $getEmergencyTypeResult = civicrm_api('relationship_type', 'get', $emergencyTypeParams);
              if ($getEmergencyTypeResult['count'] == 0) {
                $createEmergencyTypeResult = civicrm_api('relationship_type', 'create', $emergencyTypeParams);
                $relId = $createEmergencyTypeResult['id'];
              } else {
                $relId = $getEmergencyTypeResult['id'];
              }
            }
            // create relationship of first emergency contact and child
            if ($firstEmergency) {
              $emergency1ChildRelationshipParams = array( 
                                                     'contact_id_a' => $child,
                                                     'contact_id_b' => $firstEmergency,
                                                     'relationship_type_id' => $relId.'_a_b',
                                                     'is_active' => 1,
                                                     'contact_check' => array($firstEmergency => $firstEmergency));
              $ids['contact'] = $child;
              CRM_Contact_BAO_Relationship::create(&$emergency1ChildRelationshipParams, &$ids);
            }
            if ($secondEmergency) {
              // create relationship of second emergency contact and child
              $emergency2ChildRelationshipParams = array( 
                                                     'contact_id_a' => $child,
                                                     'contact_id_b' => $secondEmergency,
                                                     'relationship_type_id' => $relId.'_a_b',
                                                     'is_active' => 1,
                                                     'contact_check' => array($secondEmergency => $secondEmergency));

              CRM_Contact_BAO_Relationship::create(&$emergency2ChildRelationshipParams, &$ids);
            }
          } else {
            $otherChildId = $ids['contact'] = $pContactIds[$pID];
            $parentRelationshipParams['contact_id_a'] = $otherChildId;
            $otherParentRelationshipParams['contact_id_a'] = $otherChildId;
            $emergency1ChildRelationshipParams['contact_id_a'] = $otherChildId;
            $emergency2ChildRelationshipParams['contact_id_a'] = $otherChildId;
            $householdMemberRelationshipParams['contact_id_a'] = $otherChildId;
            $parentRelationshipResult      = civicrm_api('relationship', 'create', $parentRelationshipParams);
            if ($otherParentRelationshipParams) {
              $otherParentRelationshipResult = civicrm_api('relationship', 'create', $otherParentRelationshipParams);
            } 
            if ($emergency1ChildRelationshipParams) {
              CRM_Contact_BAO_Relationship::create(&$emergency1ChildRelationshipParams, &$ids);
            }
            if ($emergency2ChildRelationshipParams) {
              CRM_Contact_BAO_Relationship::create(&$emergency2ChildRelationshipParams, &$ids);
            }
            if ($is_shareAdd == 1) {
              $householdMemberRelationshipResult = civicrm_api('relationship', 'create', $householdMemberRelationshipParams);
              $shareOtherParams['id'] = $otherChildId;
              $childAddress = civicrm_api('contact', 'create', $shareOtherParams);
            }
          }
        }
      }
    }
  }
}

function youthregistration_civicrm_postProcess( $formName, &$form  ) { 
                
  if( $formName == 'CRM_Event_Form_ManageEvent_Registration' ) {                    
    $eventId = $form->_id;
    $isenhanced = $form->_submitValues['is_enhanced'];
    if( !$isenhanced ) { 
      $isenhanced = 0; 
    }
    if( $isenhanced ) {
      $isEnhanced = CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_event_enhanced WHERE event_id = $eventId" );
      if (!empty($isEnhanced) ) {
        
      } else {
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced( id, event_id, is_enhanced ) values( null,'$eventId','$isenhanced' )" );
/*        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile(event_id, uf_group_id, contact_position, shares_address ) values({$eventId}, 4, null,0)" );
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile(event_id, uf_group_id, contact_position, shares_address ) values({$eventId}, 11, null,0)" );
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile(event_id, uf_group_id, contact_position, shares_address ) values({$eventId}, 12, null,0)" );
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile(event_id, uf_group_id, contact_position, shares_address ) values({$eventId}, 13, null,0)" );
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile(event_id, uf_group_id, contact_position, shares_address ) values({$eventId}, 14, null,0)" ); */
      }
	if ($isEnhanced) {
        CRM_Core_DAO::executeQuery( "DELETE FROM civicrm_event_enhanced_profile WHERE event_id = $eventId" );
        CRM_Core_DAO::executeQuery( "INSERT INTO civicrm_event_enhanced_profile (event_id, uf_group_id) select entity_id, uf_group_id FROM civicrm_uf_join WHERE entity_id = $eventId AND entity_table = 'civicrm_event'" );        
   }
    }
  }
}

function isProfileComplete($profileIds) {
  $profileReqFields = array();
  foreach ($profileIds as $profileId) {
    if ($profileId && is_numeric($profileId)) {
      $fields = CRM_Core_BAO_UFGroup::getFields($profileId);
      foreach ($fields as $field) {
        switch (TRUE) {
        case substr_count($field['name'], 'first_name'):
          $profileReqFields[] = 'first_name';
          break;

        case substr_count($field['name'], 'last_name'):
          $profileReqFields[] = 'last_name';
          break;
        }
      }
    }
  }
  $profileComplete = (in_array('first_name', $profileReqFields) && in_array('last_name', $profileReqFields));
  return $profileComplete;
}
