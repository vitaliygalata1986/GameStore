import {__} from '@wordpress/i18n';
import {useBlockProps, RichText, InspectorControls, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl} from '@wordpress/components';
import './editor.scss';

export default function Edit({attributes, setAttributes}) {
	const {title, description, image, shortcode} = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'blocks-gamestore')}>
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
					<br/>
					<TextControl
						label={__('Shortcode', 'blocks-gamestore')}
						value={shortcode}
						onChange={(val) => setAttributes({shortcode:val})}
					/>

				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps({
				className: 'alignfull',
				style:{
					background: image ? `url(${image})` : undefined
				}
			})}>
				<div className="subscribe-inner wrapper">
					<RichText
						tagName="h2"
						className="subscribe-title"
						value={title}
						onChange={(title) => setAttributes({title})}
					/>
					<RichText
						tagName="p"
						className="subscribe-description"
						value={description}
						onChange={(description) => setAttributes({description})}
					/>
				</div>
			</div>
		</>
	);
}


/*
 ServerSideRender - указываем 2 атрибута, какой блок мы хотим грузить, и атрибуты
* */

/*
 useBlockProps - возвращает не только классы, а все атрибуты для главного родительского контейнера.
 Можно и класы свои передавать.
* */
