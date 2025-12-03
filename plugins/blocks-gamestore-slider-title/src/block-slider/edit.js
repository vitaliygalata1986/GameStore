import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaPlaceholder,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
	RangeControl,
	ToggleControl,
} from '@wordpress/components';
import './editor.scss';

const SlideItem = ({ index, slide, onChange, onRemove }) => {
	const { image = '', caption = '' } = slide || {};

	const handleImageChange = (url) => {
		onChange(
			{
				...slide,
				image: url,
			},
			index
		);
	};

	const handleCaptionChange = (value) => {
		onChange(
			{
				...slide,
				caption: value,
			},
			index
		);
	};

	return (
		<div className="slide-item">
			<div className="slide-item-image">
				<p>Slide Logo</p>

				{image && (
					<div className="image-box">
						<img src={image} alt="Slide image" />
					</div>
				)}

				<MediaPlaceholder
					icon="format-image"
					onSelect={(media) => handleImageChange(media.url)}
					onSelectURL={(url) => handleImageChange(url)}
					labels={{
						title: 'Slide Image',
						instructions: 'Upload an image for the slide.',
					}}
					accept="image/*"
					allowedTypes={['image']}
					multiple={false}
				/>
			</div>

			<TextControl
				label="Caption"
				value={caption}
				onChange={handleCaptionChange}
			/>

			<Button
				className="components-button is-destructive"
				onClick={() => onRemove(index)}
			>
				Remove
			</Button>
		</div>
	);
};

export default function Edit({ attributes, setAttributes }) {
	const { title, slider_count, speed_autoplay = 3, autoplay = true, slides = [] } = attributes;

	const onSlideChange = (updatedSlide, index) => {
		const updatedSlides = [...slides];
		updatedSlides[index] = updatedSlide;
		setAttributes({ slides: updatedSlides });
	};

	const addSlide = () => {
		const updatedSlides = [...slides, { image: '', caption: '' }];
		setAttributes({ slides: updatedSlides });
	};

	const removeSlide = (index) => {
		const updatedSlides = [...slides];
		updatedSlides.splice(index, 1);
		setAttributes({ slides: updatedSlides });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Hero settings">
					<TextControl
						label="Section title"
						value={title}
						onChange={(title) => setAttributes({ title })}
					/>
				</PanelBody>

				<PanelBody title="Slider settings">
					<RangeControl
						label="Slides per view"
						value={slider_count}
						onChange={(value) =>
							setAttributes({ slider_count: value })
						}
						min={1}
						max={10}
					/>

					<RangeControl
						label="Scroll speed"
						value={speed_autoplay}
						onChange={(value) =>
							setAttributes({ speed_autoplay: value })
						}
						min={1}
						max={10}
					/>

					<ToggleControl
						label="Autoplay"
						checked={!!autoplay}
						onChange={(value) =>
							setAttributes({ autoplay: value })
						}
					/>
				</PanelBody>

				<PanelBody title="Hero Slider">
					{slides.map((slide, index) => (
						<SlideItem
							key={index}
							index={index}
							slide={slide}
							onChange={onSlideChange}
							onRemove={removeSlide}
						/>
					))}
					<Button
						className="components-button is-primary"
						onClick={addSlide}
					>
						Add Slide
					</Button>
				</PanelBody>
			</InspectorControls>

			<RichText
				{...useBlockProps()}
				tagName="h1"
				className="hero-title"
				value={title}
				onChange={(title) => setAttributes({ title })}
			/>

			{slides && slides.length > 0 && (
				<div className="hero-slider">
					<div className="slider-container hero-slider__container">
						<div className="swiper-wrapper">
							{slides.map((slide, index) => {
								const { image, caption } = slide || {};
								return (
									<div
										key={index}
										className="swiper-slide slide-item"
									>
										{image && (
											<img
												src={image}
												alt={caption || 'Logo'}
												className="slide-logo"
											/>
										)}
										{caption && (
											<p className="slide-caption">
												{caption}
											</p>
										)}
									</div>
								);
							})}
						</div>
					</div>
				</div>
			)}
		</>
	);
}

