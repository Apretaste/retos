{foreach item = section from=$goals}
    {if $section['visible']}
        {if $section['complete'] == false}
            <h1>{$section['title']} {number_format($section['complete_count']/$section['total']*100,0)}%</h1>
            <p>Completa los siguientes <b>{$section['total']}</b> retos y gana cr&eacute;dito personal. {if $section['complete_count'] > 0}Ya has completado <b>{$section['complete_count']} {else} No has completado ninguno todav&iacute;a.{/if}</b></p>
            <table width="100%" cellspacing="0">
                {foreach item=goal from=$section['goals']}
                <tr {if $goal@iteration is even} bgcolor="#F2F2F2" {/if}>
                <td valign="top" width="1">{space5}{$goal@iteration})</td>
                <td>
                    {space5}
                    <b>{$goal['caption']}</b>
                    {if $goal['complete']}<font color="green">&#10004;</font>{/if}
                    {space5}
                </td>
                <td>
                    {if !$goal['complete']}
                        {if $goal['link'] != false}
                            {link href="{$goal['link']}" caption="H&aacute;zlo!"}
                        {/if}
                    {/if}
                </td>
                </tr>
                {/foreach}
            </table>
            {space10}
            <p>Al completar estos retos ganar&aacute;s <b>&sect;{$section['prize']|money_format}</b> para tu cr&eacute;dito personal.</p>
        {else}
            {if $section['completion_text'] != false}
                <h2>{$section['title']}</h2>
                <p>{$section['completion_text']}</p>
            {/if}
        {/if}
    {/if}
{/foreach}