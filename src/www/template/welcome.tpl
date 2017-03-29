<form method="get" action="index.php" />
<input type="hidden" name="page" value="view" />
PXD:
<input type="text" name="pxd" />
<input type="submit" />
</form>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        {foreach $currentSync as $pxd}
        <tr>
            <td>{$pxd}</td>
            <td><a href="?page=view&amp;pxd={$pxd}">View</a></td>
        </tr>
        {/foreach}
    </tbody>
</table>