import {RichText} from '@wordpress/block-editor';
import { useBlockProps } from '@wordpress/block-editor';
export default function save({attributes}) {
	const { title, slides = [], speed_autoplay = 3, 	autoplay = true, slider_count = 4 } = attributes;

	return (
		<>
		<div {...useBlockProps.save({
			'data-slides-per-view': slider_count || 1,
			'data-autoplay': autoplay ? 'true' : 'false',
			'data-speed-autoplay': speed_autoplay || 3,
		})}>
			{'Blocks Gamestore – hello from the saved content!'}
			<div>
				<RichText.Content
					tagName="h1"
					className="hero-title"
					value={title}
				/>
			</div>
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
		</div>
		</>
	);
}
