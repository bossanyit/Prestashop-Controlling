{extends file="helpers/form/form.tpl"}
{block name="other_input"}
{if isset($id_controlling_funnel) }
<div class="funnel-form">
        <div id='funnelinfo'>
            
    		<h2>{l s='Funnel Name: ' } <strong> {$funnel_name} </strong><input type="text" size="5" id="funnel_name" name="funnel_name" value="{$funnel_name}"/></h2>
    		<p>{l s='Added on: '} {$date_add}</p>
    		<p><label for="position">{l s='Position: ' }</label><input type="text" size="5" id="position" name="position" value="{$position}"/>
    		<input type="hidden"  id="id_controlling_funnel" name="id_controlling_funnel" value="{$id_controlling_funnel}"/>
        </div>
        
        
		<div class='funnelstep'>
            <legend>
    			<img alt="Step1" src="../img/admin/edit.gif">
    			{l s='Step 1' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 1 can be an ad (FB or Adwords) or a sales email' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="id_step1_source" name="id_step1_source" value="{$step1data->id}"/>
    		<label class="required" for="step1_name">{l s='Step name:' }</label>
   		    <input type="text" id="step1_name" name="step1_name" value="{$step1data->step_name}"/>	

    		<label class="required" for="step1_source_type">{l s='Source type:' }</label>
    		<select name="step1_source_type" id="step1_source_type">
    		    {foreach $source_types as $source_key=>$source_item}
    		        {if $step1data->funnel_source_type==$source_key}
    		            <option value="{$source_key}" selected>{$source_item}</option>
    		        {else}
    		            <option value="{$source_key}">{$source_item}</option>
    		        {/if}
    		    {/foreach}
            </select>

    		<label class="required" for="step1_funnel_source">{l s='Funnel source:' }</label>
   		    <input type="text" size="50" id="step1_funnel_source" name="step1_funnel_source" value="{$step1data->funnel_source}"/>	
    	</div>
    	
    	<div class='funnelstep'>
            <legend>
    			<img alt="Step1" src="../img/admin/edit.gif">
    			{l s='Step 2' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 2 is optional, can be a registration landing page' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="id_step2_source" name="id_step2_source" value="{$step2data->id}"/>
    		<label class="required" for="step2_name">{l s='Step name:' }</label>
    		<input type="text" size="50" id="step2_name" name="step2_name" value="{$step2data->step_name}"/>	
    		<label class="required" for="step2_source_type">{l s='Source type:' }</label>
    		<select name="step2_source_type" id="step2_source_type">
    		    {foreach $source_types as $source_key=>$source_item}
    		        {if $step2data->funnel_source_type == $source_key}
    		            <option value="{$source_key}" selected>{$source_item}</option>
    		        {else}
    		            <option value="{$source_key}">{$source_item}</option>
    		        {/if}
    		    {/foreach}
            </select>
            <label class="required" for="step2_funnel_source">{l s='Funnel source:' }</label>
    		<input type="text" size="50" id="step2_funnel_source" name="step2_funnel_source" value="{$step2data->funnel_source}"/>	
    	</div>
    	
    	<div class='funnelstep'>
            <legend>
    			<img alt="Step3" src="../img/admin/edit.gif">
    			{l s='Step 3' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 3 could be an OTO or sales page (could be optional)' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="id_step3_source" name="id_step3_source" value="{$step3data->id}"/>
    		<label class="required" for="step3_name">{l s='Step name:' }</label>
    		<input type="text" size="50" id="step3_name" name="step3_name" value="{$step3data->step_name}"/>	
    		<label class="required" for="step3_source_type">{l s='Source type:' }</label>
    		<select name="step3_source_type" id="step3_source_type">
    		    {foreach $source_types as $source_key=>$source_item}
    		        {if $step3data->funnel_source_type eq $source_key}
    		            <option value="{$source_key}" selected>{$source_item}</option>
    		        {else}
    		            <option value="{$source_key}">{$source_item}</option>
    		        {/if}
    		    {/foreach}
            </select>
            <label class="required" for="step3_funnel_source">{l s='Funnel source:' }</label>
    		<input type="text" size="50" id="step3_funnel_source" name="step3_funnel_source" value="{$step3data->funnel_source}"/>	
    	</div>
    	
    	<div class='funnelstep'>
            <legend>
    			<img alt="Step4" src="../img/admin/edit.gif">
    			{l s='Step 4' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 4 is the UTM parameter for putting a product into the cart' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="id_step4_source" name="id_step4_source" value="{$step4data->id}"/>
    		<label class="required" for="step4_name">{l s='Step name:' }</label>
    		<input type="text" size="50" id="step4_name" name="step4_name" value="{$step4data->step_name}"/>
    		<label class="required" for="step4_source_type">{l s='Source type:' }</label>	
    		<select name="step4_source_type" id="step4_source_type">
    		    {foreach $source_types as $source_key=>$source_item}
    		        {if $step4data->funnel_source_type==$source_key}
    		            <option value="{$source_key}" selected>{$source_item}</option>
    		        {else}
    		            <option value="{$source_key}">{$source_item}</option>
    		        {/if}
    		    {/foreach}
            </select>
            <label id="label_step4_funnel_source" class="required" for="step4_funnel_source">{l s='Funnel source:' }</label>
    		<input type="text" size="50" id="step4_funnel_source" name="step4_funnel_source" value="{$step4data->funnel_source}"/>	
    		<div class='utm4'>
    		    <label id="label_step4_dimension" class="required" for="label_step4_dimension">{l s='UTM dimension:' }</label>
    		    <input type="text" id="step4_dimension" name="step4_dimension" value="{$step4data->dimensions}"/>
    		    <label id="label_step4_filter" class="required" for="label_step4_filter">{l s='UTM filter:' }</label>
    		    <input type="text" id="step4_filter" name="step4_filter" value="{$step4data->filter}"/>
    		</div>
    	</div>
    	
    	<div class='funnelstep'>
            <legend>
    			<img alt="Step4" src="../img/admin/edit.gif">
    			{l s='Step 5' mod='andiocontrolling' }
    		</legend>
    		<div id='step_description'>
    		    <p>{l s='The step 5 is the defintion of coupons which are used for the campaign' mod='andiocontrolling'  }</p>
    		</div>
    		<input type="hidden"  id="id_step5_source" name="id_step5_source" value="{$step5data->id}"/>
    		<label class="required" for="step5_name">{l s='Step name:' }</label>
    		<input type="text" size="50" id="step5_name" name="step5_name" value="{$step5data->step_name}"/>	
    		<label class="required" for="step5_source_type">{l s='Source type:' }</label>
    		<select name="step5_source_type" id="step5_source_type">
    		    {foreach $source_types as $source_key=>$source_item}
    		       {if $step5data->funnel_source_type==$source_key}
    		            <option value="{$source_key}" selected>{$source_item}</option>
    		        {else}
    		            <option value="{$source_key}">{$source_item}</option>
    		        {/if}
    		    {/foreach}
            </select>            
            <label id="label_step5_funnel_source" class="required" for="step5_funnel_source">{l s='Funnel source:' }</label>
    		<input type="text" size="50" id="step5_funnel_source" name="step5_funnel_source" value="{$step5data->funnel_source}"/>	
    		<div class='utm5'>
    		    <label id="label_step5_dimension" class="required" for="label_step5_dimension">{l s='UTM dimension:' }</label>
    		    <input type="text" id="step5_dimension" name="step5_dimension" value="{$step5data->dimensions}"/>
    		    <label id="label_step5_filter" class="required" for="label_step5_filter">{l s='UTM filter:' }</label>
    		    <input type="text" id="step5_filter" name="step5_filter" value="{$step5data->filter}"/>
    		</div>
    	</div>    	    	
		
		<p>&nbsp;</p>

		<br style='clear: both'; />
		
		<div class="panel-footer">
	        <a href="{$link->getAdminLink('AdminFunnel', true)|escape:'html':'UTF-8'}" class="btn btn-default">
							<i class="process-icon-cancel"></i> {l s='Cancel'}
			</a>
		    <button type='submit' value="1" class='btn btn-default pull-right' name="submitupdatecontrolling_funnel" >
		        <i class="process-icon-save"></i>{l s='Save'  mod='andiocontrolling'}
		    </button>    
		</div>
    <br style='clear: both'; />
</div>


{/if}
{/block}