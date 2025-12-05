import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl, TextareaControl} from '@wordpress/components';
import './editor.scss';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({attributes, setAttributes}) {
	const {count, title, description, image} = attributes;
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

					<TextControl
						label={__('Description', 'blocks-gamestore')}
						value={description}
						onChange={(description) => setAttributes({description})}
					/>
					{image && (
						<img
							src={image}
							alt={title || ''}
						/>
					)}
					<MediaPlaceholder
						icon="format-image"
						labels={{
							title: 'Image',
						}}
						onSelect={(media) => setAttributes({ image: media.url })}
						onSelectURL={(url) => setAttributes({ image: url })}
						accept='image/*'
						allowedTypes={['image']}
						multiple={false}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender
					block="blocks-gamestore/recent-news"
					attributes={attributes}
				/>
			</div>
		</>
	);
}


/*
 ServerSideRender - указываем 2 атрибута, какой блок мы хотим грузить, и атрибуты
* */
