<?php

	$businessdetails = get_option('businessdetails');
	$businessdetails = (gettype($businessdetails) == 'string') ? (json_decode($businessdetails)) : ($businessdetails);

	if(isset($businessdetails->businesscode)) {

		$rewardslist = json_decode(get_option( 'whisqr_rewardsdata' ));

?>

<div <?php echo get_block_wrapper_attributes(); ?>>

	<h3><?php echo($businessdetails->businessname); ?> Customers Get Rewarded</h3>
	<p>Collect Punches and&nbsp;redeem&nbsp;them for the following&nbsp;rewards</p>

	<ul>
<?php

		foreach($rewardslist as $reward) {

			if($reward->active == 'true') {

?>
		<li><strong><?php echo $reward->fields->rewardname; ?></strong> (<?php echo $reward->fields->punchcost; ?> punches) - <?php echo $reward->fields->description; ?></li>
<?php

			}

		}

?>
	</ul>

</div>

<?php

	}

?>