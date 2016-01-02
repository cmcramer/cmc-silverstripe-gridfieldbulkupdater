Allows changes to fields of multiple rows in a GridField that has GridFieldEditableColumns enabled

Nothing is saved. Items are updated in CMS view-only and not saved until 'Save & Publish' button clicked.

Requires silverstripe-gridfieldextensions
https://github.com/silverstripe-australia/silverstripe-gridfieldextensions/blob/master/code/GridFieldTitleHeader.php

*Sample GridField config*

//Bulk updater has to come after EditableColumns or Select added in first column
$groupGridField = new GridField(
                        'TrailGroupID'.$trailGroup->ID,
                        $trailGroup->getTitle(),
                        $trailGroup->Trails(),
                        GridFieldConfig::create()
                            ->addComponent(new GridFieldToolbarHeader())
                            ->addComponent(new GridFieldEditableColumns())
                            ->addComponent(new GridFieldTitleHeader())
                            ->addComponent(new CmcGridFieldBulkUpdater())


*lang and select all code pulled from GridFieldBulkEditingTools


