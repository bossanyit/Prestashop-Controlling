
<div id="statsContainer" class="col-md-9">
		<div class="panel">
		    
			{if $module_name}
				<h3 class="space">{l s='Data displaying for '}{$module_name}</h3>
				{$hook}
				<div id="data">
				    
				</div>
			{else}
				<h2 class="space">{l s='Class for visualization does not exists: '}{$module_name}</h2>
			{/if}
			<h2 class="space">{$error}</h2>
		</div>
	</div>
</div>