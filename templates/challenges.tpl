<h1>Retos de la app</h1>
<p>Completa los siguientes retos y gana <b>&sect;{$credit|money_format}</b> de cr&eacute;dito</b></p>

<table id="retos" width="100%" cellspacing="0">
	{foreach item=goal from=$goals}
    <tr {if $goal@iteration is even} bgcolor="#F2F2F2" {/if}>
		<td width="1">
			{$goal@iteration})&nbsp;
		</td>
		<td>
			{if $goal['completed']}<s>{$goal['caption']}</s>
			{else}<b>{$goal['caption']}</b>{/if}
		</td>
		<td width="1" align="center">
			{if $goal['completed']}
				<font color="green">&#10004;&nbsp;&nbsp;</font>
			{elseif $goal['link']}
				{button href="{$goal['link']}" caption="&rArr;" size="small" color="grey"}
			{/if}
		</td>
    </tr>
    {/foreach}
</table>

<style>
	#retos td {
		padding: 10px 0px;
	}
</style>