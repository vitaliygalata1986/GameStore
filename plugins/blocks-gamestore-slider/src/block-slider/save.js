import {RichText} from '@wordpress/block-editor';
import { useBlockProps } from '@wordpress/block-editor';
export default function save({attributes}) {
	const { title, description, link, linkAnchor, video, image, slides } = attributes
	return (
		<>
		<div {...useBlockProps.save()}>
			{'Blocks Gamestore – hello from the saved content!'}
			<div>
				<RichText.Content
					tagName="h1"
					className="hero-title"
					value={title}
				/>
			</div>
			{slides &&
				<div className='hero-slider'>
					<div className='slider-container hero-slider__container'>
						<div className='swiper-wrapper'>
							{slides.map((slide, index) => (
								<div key={index} className='swiper-slide slide-item'>
									<img src={slide} alt="Logo" className='light-logo' />
								</div>
							))}
						</div>
					</div>
				</div>
			}
		</div>
		</>
	);
}
