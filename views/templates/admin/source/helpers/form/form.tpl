{extends file="helpers/form/form.tpl"}
{block name="other_input"}
{if isset($id_controlling_source) }
<div class="source-form">
        <div id='sourceinfo'>
            
    		<h2>{l s='Source Name: ' } <strong> {$source_name} </strong><input type="text" size="5" id="source_name" name="source_name" value="{$source_name}"/></h2>
    		<p>{l s='Added on: '} {$date_add}</p>
    		<p><label for="position">{l s='Position: ' }</label><input type="text" size="5" id="position" name="position" value="{$position}"/>
    		<p><label for="collection_rank">{l s='Collection order: ' }</label><input type="text" size="5" id="collection_rank" name="collection_rank" value="{$collection_rank}"/>
    		<input type="hidden"  id="id_controlling_source" name="id_controlling_source" value="{$id_controlling_source}"/>
        </div>
        
        
		<div class='sourcestep'>
            <legend>
    			<img alt="Ad" src="../img/admin/edit.gif">
    			{l s='Ad definition' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 1 is the defintion of the ad API (FB or Adwords)' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="step1_id_source" name="step1_id_source" value="{$step1data->id}"/>

    		<label class="required" for="step1_source_type">{l s='Source type:' }</label>
    		<select name="step1_source_type" id="step1_source_type">
    		    {foreach $source_types as $entry_type}
    		        {if $step1data->id_entry_type==$entry_type.id_entry_type}
    		            <option value="{$entry_type.id_entry_type}" selected>{$entry_type.type}</option>
    		        {else}
    		            <option value="{$entry_type.id_entry_type}">{$entry_type.type}</option>
    		        {/if}
    		    {/foreach}
            </select>
    		<label class="required" for="step1_source">{l s='Source:' }</label>
   		    <input type="text" size="50" id="step1_source" name="step1_source" value="{$step1data->entry_name}"/>	
    	</div>
        
        <div class='sourcestep'>
            <legend>
    			<img alt="Reg" src="../img/admin/edit.gif">
    			{l s='Reg definition' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 1 is the defintion of registration' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="step2_id_source" name="step2_id_source" value="{$step2data->id}"/>

    		<label class="required" for="step2_source_type">{l s='Source type:' }</label>
    		<select name="step2_source_type" id="step2source_type">
    		    {foreach $source_types as $entry_type}
    		        {if $step2data->id_entry_type==$entry_type.id_entry_type}
    		            <option value="{$entry_type.id_entry_type}" selected>{$entry_type.type}</option>
    		        {else}
    		            <option value="{$entry_type.id_entry_type}">{$entry_type.type}</option>
    		        {/if}
    		    {/foreach}
            </select>
    		<label class="required" id="label_step2_source" for="step2_source">{l s='Source:' }</label>
   		    <input type="text" size="50" id="step2_source" name="step2_source" value="{$step2data->entry_name}"/>	
   		    <div class='utm2'>
    		    <label id="label_step2_dimensions" class="required" for="label_step2_dimensions">{l s='UTM dimension:' }</label>
    		    <input type="text" id="step2_dimensions" name="step2_dimensions" value="{$step2data->dimensions}"/>
    		    <label id="label_step2_filter" class="required" for="label_step2_filter">{l s='UTM filter:' }</label>
    		    <input type="text" id="step2_filter" name="step2_filter" value="{$step2data->filter}"/>
    		</div>
    	</div>   
    	
        <div class='sourcestep'>
            <legend>
    			<img alt="oto" src="../img/admin/edit.gif">
    			{l s='OTO definition' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 3 is the definition of OTO data' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="step3_id_source" name="step3_id_source" value="{$step3data->id}"/>

    		<label class="required" for="step3_source_type">{l s='Source type:' }</label>
    		<select name="step3_source_type" id="step3_source_type">
    		    {foreach $source_types as $entry_type}
    		        {if $step3data->id_entry_type==$entry_type.id_entry_type}
    		            <option value="{$entry_type.id_entry_type}" selected>{$entry_type.type}</option>
    		        {else}
    		            <option value="{$entry_type.id_entry_type}">{$entry_type.type}</option>
    		        {/if}
    		    {/foreach}
            </select>
    		<label class="required" for="step3_source">{l s='Source:' }</label>
   		    <input type="text" size="50" id="step3_source" name="step3_source" value="{$step3data->entry_name}"/>	
   		    <div class='utm3'>
    		    <label id="label_step3_referer" class="required" for="label_step3_referer">{l s='URL Referer:' }</label>
    		    <input type="text" id="step3_referer" name="step3_referer" value="{$step3data->referer}"/>
    		    <label id="label_step3_uri" class="required" for="label_step3_uri">{l s='URI:' }</label>
    		    <input type="text" id="step3_uri" name="step3_uri" value="{$step3data->uri}"/>
    		    <label id="label_step3_products" class="required" for="label_step3_products">{l s='Products:' }</label>
    		    <input type="text" id="step3_products" name="step3_products" value="{$step3data->products}"/>
    		</div>
    	</div>    	 	
    	
        <div class='sourcestep'>
            <legend>
    			<img alt="order" src="../img/admin/edit.gif">
    			{l s='Order definition' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 4 is the definition order data)' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="step4_id_source" name="step4_id_source" value="{$step4data->id}"/>

    		<label class="required" for="step4_source_type">{l s='Source type:' }</label>
    		<select name="step4_source_type" id="step4_source_type">
    		    {foreach $source_types as $entry_type}
    		        {if $step4data->id_entry_type==$entry_type.id_entry_type}
    		            <option value="{$entry_type.id_entry_type}" selected>{$entry_type.type}</option>
    		        {else}
    		            <option value="{$entry_type.id_entry_type}">{$entry_type.type}</option>
    		        {/if}
    		    {/foreach}
            </select>
    		<label class="required" id="label_step4_source" for="step4_source">{l s='Source:' }</label>
   		    <input type="text" size="50" id="step4_source" name="step4_source" value="{$step4data->entry_name}"/>	
   		    <div class='utm4'>
    		    <label id="label_step4_referer" class="required" for="label_step4_referer">{l s='URL Referer:' }</label>
    		    <input type="text" id="step4_referer" name="step4_referer" value="{$step4data->referer}"/>
    		    <label id="label_step4_uri" class="required" for="label_step4_uri">{l s='URI:' }</label>
    		    <input type="text" id="step4_uri" name="step4_uri" value="{$step4data->uri}"/>
    		    <label id="label_step4_products" class="required" for="label_step4_products">{l s='Products:' }</label>
    		    <input type="text" id="step4_products" name="step4_products" value="{$step4data->products}"/>
    		</div>
    	</div>    	
    	
    		
		
		<p>&nbsp;</p>

		<br style='clear: both'; />
		
		<div class="panel-footer">
	        <a href="{$link->getAdminLink('AdminSource', true)|escape:'html':'UTF-8'}" class="btn btn-default">
							<i class="process-icon-cancel"></i> {l s='Cancel'}
			</a>
		    <button type='submit' value="1" class='btn btn-default pull-right' name="submitupdatecontrolling_source" >
		        <i class="process-icon-save"></i>{l s='Save'  mod='andiocontrolling'}
		    </button>    
		</div>
    <br style='clear: both'; />
</div>


{/if}
{/block}