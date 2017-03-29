<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Downloaded</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
        {foreach $fileList as $file}
        <tr>
            <td>{$file['name']}</td>
            <td>{$file['isDownloaded']}</td> {if isset($file['error'])}
            <td>{$file['error']}</td> {else}
            <td>&nbsp;</td> {/if}
        </tr>
        {/foreach}
    </tbody>
</table>