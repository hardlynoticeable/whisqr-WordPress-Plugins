import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.css';

/**
 * @return {WPElement} Element to render.
 */

export default function Edit() {

	if(typeof whisqr_settings.businessdetails.businessname === 'undefined' || whisqr_settings.businessdetails.businessname == '') {
 
		return (

			<div { ...useBlockProps() }>

				<p>Go to your Dashboard and set your keys in the Whisqr&nbsp;Menu</p>

			</div>

		);

	} else {

		return (

			<div { ...useBlockProps() }>


	<h3>{ whisqr_settings.businessdetails.businessname } Customers Get Rewarded</h3>
	<p>Collect Punches and redeem them for the following rewards</p>

	<ul>

		<li><strong>Reward Name</strong> (X punches) - Short but interesting description of this reward.  </li>

		<li><strong>Reward Name</strong> (X punches) - Short but interesting description of this reward.  </li>

		<li><strong>Reward Name</strong> (X punches) - Short but interesting description of this reward.  </li>

		<li><strong>Reward Name</strong> (X punches) - Short but interesting description of this reward.  </li>

		<li><strong>Reward Name</strong> (X punches) - Short but interesting description of this reward.  </li>

	</ul>

</div>


		);

	}

}


