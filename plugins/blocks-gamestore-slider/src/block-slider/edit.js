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
} from '@wordpress/components';
import './editor.scss';

const SlideItem = ({ index, slide, onImageChange, onRemove }) => {
	return (
		<div className="slide-item">
			<div className="slide-item-image">
				<p>Slide Logo</p>
				{slide && (
					<div className="image-box">
						<img src={slide} alt="Slide image" />
					</div>
				)}
				<MediaPlaceholder
					icon="format-image"
					onSelect={(media) => onImageChange(media.url, index)}
					onSelectURL={(url) => onImageChange(url, index)}
					labels={{
						title: 'Slide Image',
						instructions: 'Upload an image for the slide.',
					}}
					accept="image/*"
					allowedTypes={['image']}
					multiple={false}
				/>
			</div>
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
	const { title, slides = [] } = attributes;

	const onSlideChange = (updatedSlide, index) => {
		const updatedSlides = [...slides];
		updatedSlides[index] = updatedSlide;
		setAttributes({ slides: updatedSlides });
	};

	const addSlide = () => {
		const updatedSlides = [...slides, ''];
		setAttributes({ slides: updatedSlides });
	};

	const removeSlide = (index) => {
		const updatedSlides = [...slides];
		updatedSlides.splice(index, 1);
		setAttributes({ slides: updatedSlides });
	};

	const handleImageChange = (url, index) => {
		onSlideChange(url, index);
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

				<PanelBody title="Hero Slider">
					{slides.map((slide, index) => (
						<SlideItem
							key={index}
							index={index}
							slide={slide}
							onImageChange={handleImageChange}
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
							{slides.map((slide, index) => (
								<div
									key={index}
									className="swiper-slide slide-item"
								>
									{slide && (
										<img
											src={slide}
											alt="Logo"
											className="slide-logo"
										/>
									)}
								</div>
							))}
						</div>
					</div>
				</div>
			)}
		</>
	);
}
