<?php

	$businessdetails = get_option('businessdetails');
	$businessdetails = (gettype($businessdetails) == 'string') ? (json_decode($businessdetails)) : ($businessdetails);

	if(isset($businessdetails->businesscode)) {

?>

<div <?php echo get_block_wrapper_attributes(); ?> onclick="JavaScript: window.location.assign( 'https://qrl.at?<?php echo($businessdetails->businesscode); ?>/signup' );">

	<p>Get your <strong><?php echo($businessdetails->businessname); ?> Punch&nbsp;Card</strong> Here!</p>

	<img class="whisqr-registration-img" src="https://loyalty.whisqr.com/images/register-now.png?embedv1.1" />

	<p><?php echo($businessdetails->businessname); ?> Customers <strong><em>Get&nbsp;Rewarded!</em></strong></p>

</div>

<?php

	}

?>