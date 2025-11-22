import {useBlockProps, RichText, InspectorControls, MediaUpload, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl, TextareaControl, ToggleControl} from '@wordpress/components';
import {Button} from '@wordpress/components';
import {useState} from '@wordpress/element';
import './editor.scss';

const SlideItem = ({index, slide, onImageChange, onRemove}) => {
	return (
		<div className='slide-item'>
			<div className='slide-item-image'>
				<p>Light Version Logo</p>
				{slide.lightImage && <div className='image-box'><img src={slide.lightImage} alt="Slide image"/></div>}
				<MediaPlaceholder
					icon="format-image"
					onSelect={(media) => onImageChange(media.url, index, "lightImage")}
					onSelectURL={(url) => onImageChange(url, index, "lightImage")}
					labels={{
						title: 'Slide Light Image',
						instructions: 'Upload an image for the slide.'
					}}
					accept='image/*'
					allowedTypes={['image']}
					multiple={false}
				/>
			</div>
			<div className='slide-item-image'>
				<p>Dark Version Logo</p>
				{slide.darkImage && <div className='image-box'><img src={slide.darkImage} alt="Slide image"/></div>}
				<MediaPlaceholder
					icon="format-image"
					onSelect={(media) => onImageChange(media.url, index, "darkImage")}
					onSelectURL={(url) => onImageChange(url, index, "darkImage")}
					labels={{
						title: 'Slide Dark Image',
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
	const {title, description, link, video, linkAnchor, image, isVideo, slides: initialSlides} = attributes;
	const [isVideoUpload, setIsVideoUpload] = useState(isVideo);
	const [slides, setSlides] = useState(initialSlides || []);
	// console.log(slides);

	/*
		[
			{lightImage: 'http://localhost:8200/wp-content/uploads/2025/11/Снимок-экрана-от-2025-10-18-10-15-03.png', darkImage: 'http://localhost:8200/wp-content/uploads/2025/11/Снимок-экрана-от-2025-11-04-19-32-16.png'}
			{lightImage: 'http://localhost:8200/wp-content/uploads/2025/11/Снимок-экрана-от-2025-10-14-15-26-53.png', darkImage: 'http://localhost:8200/wp-content/uploads/2025/11/Снимок-экрана-от-2025-10-24-15-26-06.png'}
		]
	*/

	const onSlideChange = (updatedSlide, index) => {
		const updatedSlides = [...slides];
		updatedSlides[index] = updatedSlide;
		setSlides(updatedSlides);
		setAttributes({slides: updatedSlides});
	}
	const addSlide = () => {
		const newSlide = {lightImage: '', darkImage: ''};
		const updateSlides = [...slides, newSlide];
		setSlides(updateSlides);
		setAttributes({slides: updateSlides});
	}
	const removeSlide = (index) => {
		const updatedSlides = [...slides];
		updatedSlides.splice(index, 1); // С позиции index удаляем ровно 1 элемент (тот самый слайд) Массив updatedSlides изменяется на месте — его длина уменьшается на 1
		setSlides(updatedSlides);
		setAttributes({slides: updatedSlides});
	}
	const handleImageChange = (url, index, imageType) => {
		const updatedSlide = {...slides[index], [imageType]: url};
		onSlideChange(updatedSlide, index);
	}

	// console.log('isVideoUpload',isVideoUpload)

	return (
		<>
			<InspectorControls>

				<PanelBody title="Hero Setting">
					<TextControl label="Title" value={title} onChange={(title) => setAttributes({title})}/>
					<TextareaControl label="Description" value={description}
									 onChange={(description) => setAttributes({description})}/>
					<TextControl label="Button URL" value={link} onChange={(link) => setAttributes({link})}/>
					<TextControl label="Button Value" value={linkAnchor}
								 onChange={(linkAnchor) => setAttributes({linkAnchor})}/>

					<ToggleControl
						label="Upload Video"
						checked={isVideoUpload}
						onChange={(value) => {
							// console.log('value', value); // если video -> true
							setIsVideoUpload(value)
							setAttributes({isVideo: value, video: '', image: ''});
						}}
					/>
					{isVideoUpload ? (
						video && (
							<video controls muted>
								<source src={video} type="video/mp4"/>
							</video>
						)
					) : (
						image && <img src={image} alt="Uploaded"/>
					)}
					<MediaUpload
						onSelect={(media) => {
							console.log(media) // Object
							if (isVideoUpload) {
								setAttributes({video: media.url});
							} else {
								setAttributes({image: media.url});
							}
						}}
						type={isVideoUpload ? ['video'] : ['image']}
						render={({open}) => (
							<button className='components-button is-secondary media-upload' onClick={open}>
								{isVideoUpload ? 'Upload Video' : 'Upload Image'}
							</button>
						)}
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
					<Button className='components-button is-primary' onClick={addSlide}>Add Slide</Button>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{video && (
					<video className='video-bg' loop="loop" autoplay="" muted playsinline width="100%" height="100%">
						<source className='source-element' src={video} type="video/mp4"/>
					</video>
				)}
				{image && <img className='image-bg' src={image} alt="Background"/>}
				<div className="hero-mask"></div>
				<div className="hero-content">
					<RichText
						tagName="h1"
						className="hero-title"
						value={title}
						onChange={(title) => setAttributes({title})}
					/>
					<RichText
						tagName="p"
						className="hero-description"
						value={description}
						onChange={(description) => setAttributes({description})}
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
		</>
	)
}

// компонент InspectorControls - для создания сайдбара
// PanelBody - буду сами поля
/*
	playsInline — это атрибут для <video>, который говорит мобильным браузерам (особенно iOS/Safari):
	«Играй видео прямо внутри страницы, а не разворачивай его на весь экран и не открывай системный видеоплеер».
	То есть:
	На iPhone без playsInline видео обычно автоматически открывается в полноэкранном плеере.
	С playsInline оно остаётся внутри блока (div {...useBlockProps()}), как фон/элемент интерфейса.
	В связке с muted и autoPlay это позволяет: автовоспроизведение на мобильных, без перехода в полноэкранный режим.
	В твоём случае это важно, потому что видео — фон (video-bg), и тебе нужно, чтобы оно играло тихо и на заднем плане, а не перехватывало экран пользователя.
*/

/*
className="hero-title" именно такой указываем, так как в block.json указывали: "selector": ".hero-title", иначе gutenberg не будет знать - где брать значение
* */


/*
	title, description, link, linkAnchor имеют source и selector
	👉 значит, при загрузке поста редактор берёт их значения из HTML-разметки, которую вернул save().

	video не имеет source
	👉 это «простое» атрибутное значение, оно хранится в JSON-комментарии блока и не зависит от HTML.
* */

// MediaPlaceholder - компонент для загрузки картинок


/*
	isVideo: value в setAttributes нужно не для самой работы тоггла в момент клика, а чтобы:
	1. Сохранить выбор в атрибутах блока (в БД/JSON)
		setIsVideoUpload(value) меняет только локальный React-state — он живёт, пока открыт редактор.
		setAttributes({ isVideo: value, ... }) записывает этот флаг в атрибуты Gutenberg-блока, которые сохраняются в посте.

    2. Восстанавливать состояние при повторном открытии блока
       В начале компонента ты делаешь:
        const { ..., isVideo, slides: initialSlides } = attributes;
		const [isVideoUpload, setIsVideoUpload] = useState(isVideo);
	То есть при открытии редактора значение isVideoUpload берётся из attributes.isVideo.
	Если бы ты не писал isVideo: value в атрибуты, после перезагрузки редактора/страницы блок “забыл бы”, что там был выбран видео-режим.

	3. Использовать флаг на фронтенде (в save)
		Обычно в save() можно по attributes.isVideo решать, что выводить: <video> или <img>.
		Сейчас у тебя логика показа в Edit завязана на video/image, но флаг isVideo всё равно полезен как явный признак режима.
	4. Сбросить несоответствующие данные
		В той же строке ты обнуляешь:
		setAttributes({ isVideo: value, video: '', image: '' }); чтобы при переключении режима не осталось “старой” картинки/видео.
* */

/*
	Если коротко:
		setIsVideoUpload(value) — для текущей сессии редактора.
		isVideo: value в setAttributes — чтобы этот выбор жил в атрибутах блока, переживал сохранение поста и мог использоваться в save() и при повторном редактировании.
* */


