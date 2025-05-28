<table>
    <thead>
    <tr>
		<?php foreach ( $columns as $column ) { ?>
            <th>
				<?php echo $column; ?>
            </th>
		<?php } ?>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ( $rows as $row_table ) {
		?>
        <tr>
			<?php foreach ( $row_table as $cell ) { ?>
                <td>
                    <?php echo $cell ;?>
                </td>
			<?php } ?>
        </tr>
		<?php
	}
	?>
    </tbody>
</table>