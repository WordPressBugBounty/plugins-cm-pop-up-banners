<?php
$campaign_id = isset($_GET['post']) ? $_GET['post'] : 0;
?>
<table style="width:100%;" cellpadding="10">
	<tr>
		<td colspan="2">
			<span class="field-info"><span>(Only in Pro)</span></span>
		</td>
	</tr>
	<tr>
		<td style="text-align:left;color:#999;">Impressions</td>
		<td style="text-align:left;color:#999;">0</td>
	</tr>
	<tr>
		<td style="text-align:left;color:#999;">Clicks</td>
		<td style="text-align:left;color:#999;">0</td>
	</tr>
	<tr>
		<td style="text-align:left;color:#999;">Ratio</td>
		<td style="text-align:left;color:#999;">0%</td>
	</tr>
	<?php if($campaign_id) { ?>
	<tr>
		<td colspan="2" style="text-align:left;">
			<input type="checkbox" name="cmpop_reset_statistics" disabled />
			<span style="color:#999;">Reset</span>
		</td>
	</tr>
	<?php } ?>
</table>