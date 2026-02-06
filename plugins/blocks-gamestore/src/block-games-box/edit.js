import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl, TextareaControl} from '@wordpress/components';
import './editor.scss';
import placeholder from './img/default.png';

export default function Edit({attributes, setAttributes}) {
	const {count, title} = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'blocks-gamestore')}>
					<TextControl
						label={__('Count', 'blocks-gamestore')}
						value={count}
						onChange={(val) => setAttributes({count: parseInt(val, 10) || 0})}
					/>
					<TextControl
						label={__('Title', 'blocks-gamestore')}
						value={title}
						onChange={(title) => setAttributes({title})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<img src={placeholder} alt="Alt"/>
			</div>
		</>
	);
}


/*
 ServerSideRender - указываем 2 атрибута, какой блок мы хотим грузить, и атрибуты
* */
