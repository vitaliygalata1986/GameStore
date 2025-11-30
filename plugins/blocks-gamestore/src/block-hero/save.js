import { useBlockProps } from '@wordpress/block-editor';
import {RichText} from '@wordpress/block-editor';
export default function save({ attributes }) {
	const { title, description, link, linkAnchor, video, image, slides } = attributes
	// console.log('slides',slides);
	// console.log('title',title);
	// console.log('attributes',attributes);
	return (
		<div {...useBlockProps.save()}>
			{video && (
				<video className='video-bg' loop="loop" autoPlay="" muted playsInline width="100%" height="100%">
					<source className='source-element' src={video} type="video/mp4"/>
				</video>
			)}
			{image && <img className='image-bg' src={image} alt="Background"/>}
			<div className="hero-mask"></div>
			<div className="hero-content">
				<RichText.Content
					tagName="h1"
					className="hero-title"
					value={title}
				/>
				<RichText.Content
					tagName="p"
					className="hero-description"
					value={description}
				/>
				<a href={link} className="hero-button shadow">{linkAnchor}</a>
			</div>
			{slides &&
				<div className='hero-slider'>
					<div className='slider-container'>
						<div className='swiper-wrapper'>
							{slides.map((slide, index) => (
								<div key={index} className='swiper-slide slide-item'>
									<img src={slide.lightImage} alt="Logo" className='light-logo' />
									<img src={slide.darkImage} alt="Logo" className='dark-logo'/>
								</div>
							))}
						</div>
					</div>
				</div>
			}
		</div>
	);
}


// https://www.youtube.com/watch?v=c8nob2rQ9To&t=36638s
// 6-17
//
