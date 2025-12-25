import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { title, description, links, imageBg, image } = attributes;
	return (
		<div { ...useBlockProps.save({
			className: "alignfull",
			style: {
				background: imageBg ? `url(${imageBg})` : undefined,
			},
		}) }>
			<div className="wrapper cta-inner">
				<div className="left-part">
					<RichText.Content
						tagName="h2"
						className="cta-title"
						value={title}
					/>
					<RichText.Content
						tagName="p"
						className="cta-description"
						value={description}
					/>
					<div className="links-list">
						{links.map((link, index) => (
							<p key={index}>
								<a href={link.url} target="_blank" rel="noopener noreferrer">
									{link.anchor || "Untitled Link"}
								</a>
							</p>
						))}
					</div>
				</div>
				<div className="right-part">
					{image && <img className="image-cta" src={image} alt="CTA" />}
				</div>
			</div>
		</div>
	);
}


/*
	useBlockProps — это хук из @wordpress/block-editor, который возвращает набор атрибутов для корневого HTML-элемента вашего блока.
	Вы «размазываете» ({...}) эти пропсы на корень в edit и в save, чтобы Гутенберг автоматически добавил нужные классы/атрибуты и повёл себя правильно в редакторе и на фронтенде.
	Что именно делает:
		Добавляет служебный класс вида wp-block-namespace-blockname и объединяет его с вашим className.
		Применяет классы и стили из Block Supports (например, выравнивание, типографика, фон), если вы их включили в block.json.
		В редакторе добавляет нужные ARIA/data-* атрибуты, обработчики, ref — чтобы работали выделение, dnd, контур блока и т.д.
		В save вариант (useBlockProps.save()) формирует тот же набор классов/атрибутов для корректного HTML на сайте.

		<InnerBlocks.Content/> - чтобы грузить именно фронт-часть
* */

/*
	<RichText.Content
		tagName="h2"
		className="cta-title"
		value={title}
	onChange={(title) => setAttributes({ title })}
	/>
	onChange={(title) => setAttributes({ title })} 0 здесь не нужен, так как на фронт-части он не меняется
* */

