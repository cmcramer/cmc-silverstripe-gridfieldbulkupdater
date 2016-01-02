<tr class="bulkManagerOptions">
	<% loop $Cols %>
		<th class="main col-$Name">
			<% if $First %>
				<p class="title"><% _t('GRIDFIELD_BULK_UPDATER.COMPONENT_TITLE', 'Update one or more rows. Nothing<br>will be saved until "Publish" clicked.') %></p>
					$Menu
					<a data-url="Up.Button.DataURL" data-config="Up.Button.DataConfig" class="doBulkUpdate ss-ui-button" data-icon="$Up.Button.Icon">
						$Up.Button.Label
					</a>
				
			<% end_if %>
			$UpdateField
			<% if $Last %>
				<input class="no-change-track bulkSelectAll" type="checkbox" title="$Up.Select.Label" name="toggleSelectAll" />
			<% end_if %>
		</th>
	<% end_loop %>
</tr>