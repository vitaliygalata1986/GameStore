import {useBlockProps, RichText, InspectorControls, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl, TextareaControl, Button} from '@wordpress/components';
import {useState} from '@wordpress/element';
import './editor.scss';

const SlideItem = ({index, slide, onImageChange, onRemove}) => {
	return (
		<div className='slide-item'>
			<div className='slide-item-image'>
				<MediaPlaceholder
					mediaPreview={slide.image ? <img src={slide.image} alt="Image"/> : null}
					icon="format-image"
					onSelect={(media) => onImageChange(media.url, index, "image")}
					onSelectURL={(url) => onImageChange(url, index, "image")}
					labels={{
						title: 'Slide Image',
						instructions: 'Upload an image for the slide.'
					}}
					accept='image/*'
					allowedTypes={['image']}
					multiple={false}
				/>
			</div>
			<Button className='components-button is-destructive' onClick={() => onRemove(index)}>Remove</Button>
		</div>
	)
}

export default function Edit({attributes, setAttributes}) {
	const {title, description, slides: initialSlides} = attributes;
	const [slides, setSlides] = useState(initialSlides || []);
	const onSlideChange = (updatedSlide, index) => {
		const updatedSlides = [...slides];
		updatedSlides[index] = updatedSlide;
		setSlides(updatedSlides);
		setAttributes({slides: updatedSlides});
	}
	const addSlide = () => {
		const newSlide = {image: ''};
		const updateSlides = [...slides, newSlide];
		setSlides(updateSlides);
		setAttributes({slides: updateSlides});
	}
	const removeSlide = (index) => {
		const updatedSlides = [...slides];
		updatedSlides.splice(index, 1);
		setSlides(updatedSlides);
		setAttributes({slides: updatedSlides});
	}
	const handleImageChange = (url, index, imageType) => {
		const updatedSlide = {...slides[index], [imageType]: url};
		onSlideChange(updatedSlide, index);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title=" Setting">
					<TextControl label="Title" value={title} onChange={(title) => setAttributes({title})}/>
					<TextareaControl label="Description" value={description}
									 onChange={(description) => setAttributes({description})}/>
				</PanelBody>
				<PanelBody title="Slider Images">
					{slides.map((slide, index) => (
						<SlideItem
							key={index}
							index={index}
							slide={slide}
							onImageChange={handleImageChange}
							onRemove={removeSlide}
						/>
					))}
					<Button className='components-button is-primary' onClick={addSlide}>Add Slide</Button>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps({className: "alignfull"})}>

				<div className="slider-inner-content">
					<RichText
						tagName="h2"
						className="slider-title"
						value={title}
						onChange={(title) => setAttributes({title})}
					/>
					<RichText
						tagName="p"
						className="slider-description"
						value={description}
						onChange={(description) => setAttributes({description})}
					/>
					<div className="slider-media">
						<div className="swiper-wrapper">
							{slides.map((slide, index) => (
								<div key={index} className='swiper-slide slide-item'>
									<img src={slide.image} alt="Logo" className='blur-image'/>
									<img src={slide.image} alt="Logo" className='original-image'/>
								</div>
							))}
						</div>
					</div>
				</div>
			</div>
		</>
	)
}

/*
	<div {...useBlockProps({className: "alignfull"})}>
	alignfull - чтобы слайдер был на всю ширину
* */



