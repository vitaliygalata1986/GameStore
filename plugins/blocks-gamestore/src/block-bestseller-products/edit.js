import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls} from '@wordpress/block-editor';
import {PanelBody, TextControl} from '@wordpress/components';
import './editor.scss';
import ServerSideRender from '@wordpress/server-side-render';

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
				<ServerSideRender
					block="blocks-gamestore/bestseller-products"
					attributes={attributes}
				/>
			</div>
		</>
	);
}


/*
 ServerSideRender - указываем 2 атрибута, какой блок мы хотим грузить, и атрибуты
* */
