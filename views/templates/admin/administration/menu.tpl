<div id="container" class="row">
	<div class="sidebar navigation col-md-3">
		<nav class="list-group categorieList">
		{if count($modules)}
			{foreach $modules as $module}				
				<a class="list-group-item{if ($current_module_name && $current_module_name == $module.name)} active{/if}" href="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}&amp;module={$module.name}">{$module.displayName}</a>
			{/foreach}
		{else}
			{l s='No module has been installed.'}
		{/if}
		</nav>
	</div>
    