import {useBlockProps} from '@wordpress/block-editor';
import './editor.scss';
export default function Edit() {
	return (
		<>
			<div {...useBlockProps()}>
				New Post Template
			</div>
		</>
	);
}
