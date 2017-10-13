{foreach item = section from=$goals}
    {if $section['visible']}
        {if $section['complete'] == false}
            <h1>{$section['title']} {number_format($section['complete_count']/$section['total']*100,0)}%</h1>
            <p>Completa los siguientes <b>{$section['total']}</b> retos y gana <b>&sect;{$section['prize']|money_format}</b>  de cr&eacute;dito personal.</b></p>
            <table width="100%" cellspacing="0">
                {foreach item=goal from=$section['goals']}
                <tr {if $goal@iteration is even} bgcolor="#F2F2F2" {/if}>
                <td valign="top" width="1">{space5}{$goal@iteration})</td>
                <td>
                    {space5}
                    {if $goal['complete']}
                        <s>{$goal['caption']}</s>
                    {else}
                        <b>{$goal['caption']}</b>
                    {/if}
                    {space5}
                </td>
                <td>
                    {if $goal['complete']}
                        <font color="green">&#10004;</font>
                    {else}
                        {if $goal['link'] != false}
                            {link href="{$goal['link']}" caption="H&aacute;zlo!"}
                        {/if}
                    {/if}
                </td>
                </tr>
                {/foreach}
            </table>
            {space10}
        {else}
            {if $section['completion_text'] != false}
                <h2>{$section['title']}</h2>
                <p>{$section['completion_text']}</p>
            {/if}
        {/if}
    {/if}
{/foreach}