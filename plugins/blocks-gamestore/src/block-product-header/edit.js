import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls, MediaPlaceholder, RichText} from '@wordpress/block-editor';
import {PanelBody, TextControl} from '@wordpress/components';
import './editor.scss';

export default function Edit({attributes, setAttributes}) {
	const {title, image} = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'blocks-gamestore')}>

					<TextControl
						label={__('Title', 'blocks-gamestore')}
						value={title}
						onChange={(title) => setAttributes({title})}
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
						onSelect={(media) => setAttributes({image: media.url})}
						onSelectURL={(url) => setAttributes({image: url})}
						accept='image/*'
						allowedTypes={['image']}
						multiple={false}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps({
				className: 'alignfull',
				style: {
					background: image ? `url(${image})` : undefined
				}
			})}>
				<div className='wrapper'>
					<RichText
						tagName="h1"
						className="show-header-title"
						value={title}
						onChange={(title) => setAttributes({title})}
					/>
				</div>
			</div>
		</>
	);
}
