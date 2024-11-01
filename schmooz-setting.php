<?php
if($_POST)
{
	$mesage = "";
	if(trim($_POST['site_shortname'])!="")
	{
		update_option('schmooz_short_name', $_POST['site_shortname']);
		$mesage = "Shortname saved sucessfully";
	}
	else
	{
		$mesage = "Shortname is required";
	}
}
?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Schmooz Setting</h2>
	<form method="post">
		<input name="action" value="update" type="hidden" />
		<table class="form-table">
			<tbody>
				<?php if($mesage != "") { ?>
				<tr>
					<td><b><?php echo $mesage; ?></b></td>
				</tr>
				<?php } ?>
				<tr>
					<td>Shortname</td>
					<td><input type="text" value="<?php echo get_option( "schmooz_short_name", ""); ?>" name="site_shortname" id="site_shortname" /><br />Don't have shortname ? Dony't worry just <a  target="_blank" href="http://www.youschmooz.com/register">Click Here</a></td>
				</tr>
				<tr>
					<td></td>
					<td><p class="submit"><input name="submit" id="submit"  value="Update" type="submit" /></p></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div class="clear"></div>