<?php
/**
 * Adds functionality to GridFieldExtensionrs->ColumnEditing to allow updating 
 *      fields in multiple rows 
 * 
 * @author cmcramer
 */
 class CmcGridFieldBulkUpdater implements GridField_HTMLProvider, GridField_ColumnProvider {
     
     /**
      * GridFieldBulkUpdater component constructor.
      * 
      * @param bool  $defaultActions Use default actions list. False to start fresh.
      * 
      */
     public function __construct( $defaultActions = true) {
         if ($defaultActions) {
             $this->config['actions'] = array(
                 'bulkUpdate' => array(
                     'label' => _t('CMC_GRIDFIELD_BULK_UPDATER.UPDATE_LABEL', 'Apply'),
                     'handler' => 'CmcGridFieldBulkActionUpdateHandler',
                     'config' => array(
                         'icon' => 'accept',
                     ),
                 ),
             );
         }
     }
     

     /**
      * @param GridField $gridField
      *
      * @return array
      */
     public function getHTMLFragments($gridField)
     {
         Requirements::css(CMC_BULKUPDATER_MODULE_DIR.'/css/CmcGridFieldBulkUpdater.css');
         Requirements::javascript(CMC_BULKUPDATER_MODULE_DIR.'/javascript/CmcGridFieldBulkUpdater.js');
         Requirements::add_i18n_javascript(CMC_BULKUPDATER_MODULE_DIR.'/lang/js');
         
         //initialize column data
         $cols = new ArrayList();
         
         $fields = $gridField->getConfig()->getComponentByType('GridFieldEditableColumns')->getDisplayFields($gridField);
         
         foreach ($gridField->getColumns() as $col) {
             $fieldName = $col;
             $fieldType = '';
             $fieldLabel = '';
             if (isset($fields[$fieldName])) {
                 $fieldData = $fields[$fieldName];
                 if (isset($fieldData['field'])) {
                     $fieldType = $fieldData['field'];
                 }
                 if (isset($fieldData['title'])) {
                     $fieldLabel = $fieldData['title'];
                 }
             }
             //Debug::show($fieldType);
             if (class_exists($fieldType) && $fieldType != 'ReadonlyField') { 
                
                $field = new $fieldType($fieldName, $fieldLabel);
                if ($fieldType == 'DatetimeField' || $fieldType == 'DateField' || $fieldType == 'TimeField') {
                    $field->setValue(date('Y-m-d H:i:s'));
                    $field->setConfig('showcalendar', true);
                    
                }
                      
                 $cols->push(new ArrayData(array(
                     'UpdateField' => $field,
                     'Name'  => $fieldName,
                     'Title' => $fieldLabel,
                 )));
         
             } else {
                 $meta = $gridField->getColumnMetadata($col);
                 $cols->push(new ArrayData(array(
                     'Name'  => $col,
                     'Title' => $meta['title'],
                 )));
             }
         }
         
         
         $templateData = array();
         
         if (!count($this->config['actions'])) {
             user_error('Trying to use GridFieldBulkManager without any bulk action.', E_USER_ERROR);
         }
         
         //set up actions
         $actionsListSource = array();
         $actionsConfig = array();
         
         foreach ($this->config['actions'] as $action => $actionData) {
             $actionsListSource[$action] = $actionData['label'];
             $actionsConfig[$action] = $actionData['config'];
         }
         
         reset($this->config['actions']);
         $firstAction = key($this->config['actions']);
         
         $dropDownActionsList = DropdownField::create('bulkActionName', '')
         ->setSource($actionsListSource)
         ->setAttribute('class', 'bulkActionName no-change-track')
         ->setAttribute('id', '');

         //initialize buttonLabel
         $buttonLabel = _t('CMC_GRIDFIELD_BULK_UPDATER.ACTION1_BTN_LABEL', $this->config['actions'][$firstAction]['label']);
         //add menu if more than one action
         if (count($this->config['actions']) > 1) {
             $templateData = array(
                 'Menu' => $dropDownActionsList->FieldHolder(),
             );
             $buttonLabel = _t('CMC_GRIDFIELD_BULK_UPDATER.ACTION_BTN_LABEL', 'Go');
         }
         //Debug::show($buttonLabel);
         
         $templateData = array_merge($templateData, array ( 
                                 'Button' => array(
                                     'Label' => $buttonLabel,
                                     'Icon' => $this->config['actions'][$firstAction]['config']['icon'],
                                 ),
                                 'Select' => array(
                                     'Label' => _t('CMC_GRIDFIELD_BULK_UPDATER.SELECT_ALL_LABEL', 'Select all'),
                                 ),
                                 'Colspan' => (count($gridField->getColumns()) - 1),
                                 'Cols' => $cols,
                             )
                        );
         
         $templateData = new ArrayData($templateData);
         
         return array(
             'header' => $templateData->renderWith('CmcBulkUpdaterButtons'),
         );
     }
     

     /**
      * Modified from GridFieldBulkEditingTools
      */
     /* **********************************************************************
      * Components settings and custom methodes
      */
     /**
      * Sets the component configuration parameter.
      *
      * @param string $reference
      * @param mixed  $value
      */
     public function setConfig($reference, $value)
     {
         if (!array_key_exists($reference, $this->config)) {
             user_error("Unknown option reference: $reference", E_USER_ERROR);
         }
         if ($reference == 'actions') {
             user_error('Bulk actions must be edited via addBulkAction() and removeBulkAction()', E_USER_ERROR);
         }
         $this->config[$reference] = $value;
         return $this;
     }
     /**
      * Returns one $config parameter of the full $config.
      *
      * @param string $reference $congif parameter to return
      *
      * @return mixed
      */
     public function getConfig($reference = false)
     {
         if ($reference) {
             return $this->config[$reference];
         } else {
             return $this->config;
         }
     }
     
     /* **********************************************************************
     * GridField_ColumnProvider
     * */
    /**
     * Add bulk select column.
     * 
     * @param GridField $gridField Current GridField instance
     * @param array     $columns   Columns list
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('BulkSelect', $columns)) {
            $columns[] = 'BulkSelect';
        }
    }
    /**
     * Which columns are handled by the component.
     * 
     * @param GridField $gridField Current GridField instance
     *
     * @return array List of handled column names
     */
    public function getColumnsHandled($gridField)
    {
        return array('BulkSelect');
    }
    /**
     * Sets the column's content.
     * 
     * @param GridField  $gridField  Current GridField instance
     * @param DataObject $record     Record intance for this row
     * @param string     $columnName Column's name for which we need content
     *
     * @return mixed Column's field content
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $cb = CheckboxField::create('bulkSelect_'.$record->ID)
                ->addExtraClass('bulkSelect no-change-track')
                ->setAttribute('data-record', $record->ID);
        return $cb->Field();
    }
    /**
     * Set the column's HTML attributes.
     * 
     * @param GridField  $gridField  Current GridField instance
     * @param DataObject $record     Record intance for this row
     * @param string     $columnName Column's name for which we need attributes
     *
     * @return array List of HTML attributes
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return array('class' => 'col-bulkSelect');
    }
    /**
     * Set the column's meta data.
     * 
     * @param GridField $gridField  Current GridField instance
     * @param string    $columnName Column's name for which we need meta data
     *
     * @return array List of meta data
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'BulkSelect') {
            return array('title' => 'Select');
        }
    }
     
     
 }