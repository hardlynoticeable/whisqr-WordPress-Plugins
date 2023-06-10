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

				<p>Get your <strong>{ whisqr_settings.businessdetails.businessname } Punch&nbsp;Card</strong> Here!</p>

				<img id="whisqr-img" src="https://loyalty.whisqr.com/images/register-now.png?embedv1.0" />
				
				<p>{ whisqr_settings.businessdetails.businessname } Customers <strong><em>Get&nbsp;Rewarded!</em></strong></p>

			</div>

		);

	}

}


