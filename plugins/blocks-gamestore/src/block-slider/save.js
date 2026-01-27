import {useBlockProps} from '@wordpress/block-editor';
import {RichText} from '@wordpress/block-editor';

export default function save({attributes}) {
	const {title, description, slides} = attributes;
	return (
		<div {...useBlockProps.save({className: "alignfull"})}>
			<div className="slider-inner-content">
				<RichText.Content
					tagName="h2"
					className="slider-title"
					value={title}
				/>
				<RichText.Content
					tagName="p"
					className="slider-description"
					value={description}
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
	);
}
