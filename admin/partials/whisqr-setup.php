<style>

/* Extra small devices (phones, 600px and down) */
@media only screen and (max-width: 767px) {
	
	video {
		width: 100%;
	}

	
}

/* Medium devices (landscape tablets, 768px and up) */
@media only screen and (min-width: 768px) {
	
	video {
		min-width: 600px;
		width: 50%;
	}

}

</style>

<div class="wrap whisqr_welcome">

	<p>The following video shows you where to find your business's Short Code on your Whisqr Administration page.</p>

	<video controls>
		<source src="https://loyalty.whisqr.com/videos/get_shortcode.webm" type="video/webm" />
	</video>

<?php

echo '	<h2>' . __( 'You haven\'t yet signed up for a Whisqr account?' ) . '</h2>';

echo '	<p style="margin-bottom: 30px;"><a href="https://loyalty.whisqr.com/register" class="button-primary" target="_blank">' . __( 'Get started for free' ) . '</a></p>';

?>

</div>