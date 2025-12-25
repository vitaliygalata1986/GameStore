import { useBlockProps, RichText, InspectorControls, MediaPlaceholder } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from "@wordpress/components";
import './editor.scss';

const LinkRepeater = ({ links, setLinks }) => {
	const addLink = () => {
		setLinks([...links, { url: "", anchor: "" }]);
	};

	const removeLink = (index) => {
		const updatedLinks = links.filter((_, i) => i !== index);
		setLinks(updatedLinks);
	};

	const updateLink = (index, key, value) => {
		const updatedLinks = [...links];
		updatedLinks[index][key] = value;
		setLinks(updatedLinks);
	};

	return (
		<div className="link-repeater">
			<h4>Manage Links</h4>
			{links.map((link, index) => (
				<div key={index} className="link-repeater-item">
					<TextControl
						label="URL"
						value={link.url}
						onChange={(value) => updateLink(index, "url", value)}
						placeholder="https://example.com"
					/>
					<TextControl
						label="Anchor Text"
						value={link.anchor}
						onChange={(value) => updateLink(index, "anchor", value)}
						placeholder="Link text"
					/>
					<Button
						variant="secondary"
						onClick={() => removeLink(index)}
						className="remove-link-button"
					>
						Remove Link
					</Button>
				</div>
			))}
			<br/>
			<Button variant="primary" onClick={addLink} className="add-link-button">
				Add Link
			</Button>
		</div>
	);
};

export default function Edit({attributes, setAttributes}) {
	const { title, description, links = [], imageBg, image } = attributes;
	const setLinks = (newLinks) => setAttributes({ links: newLinks });

	return (
		<>
			<InspectorControls>
				<PanelBody title="CTA Setting">

					<TextControl label="Title" value={title} onChange={(title) => setAttributes({title})}/>

					<TextareaControl label="Description" value={description}
									 onChange={(description) => setAttributes({description})}/>

					{imageBg && <img src={imageBg} alt="Background" />}

					<MediaPlaceholder
						icon="format-image"
						labels={{ title: "Background Image" }}
						onSelect={(media) => setAttributes({ imageBg: media.url })}
						accept="image/*"
						allowedTypes={["image"]}
					/>

					<br />
					<br />

					{image && <img src={image} alt="CTA" />}
					<MediaPlaceholder
						icon="format-image"
						labels={{ title: "CTA Image" }}
						onSelect={(media) => setAttributes({ image: media.url })}
						accept="image/*"
						allowedTypes={["image"]}
					/>
				</PanelBody>

				<PanelBody title="Manage Links">
					<LinkRepeater links={links} setLinks={setLinks} />
				</PanelBody>

			</InspectorControls>
			<div
				{...useBlockProps({
					className: "alignfull",
					style: {
						background: imageBg ? `url(${imageBg})` : undefined,
					},
				})}
			>
				<div className="wrapper cta-inner">
					<div className="left-part">
						<RichText
							tagName="h2"
							className="cta-title"
							value={title}
							onChange={(title) => setAttributes({ title })}
						/>
						<RichText
							tagName="p"
							className="cta-description"
							value={description}
							onChange={(description) => setAttributes({ description })}
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
		</>
	)
}


/*
	Смысл ровно в том, что при нажатии “Add Link” ты добавляешь новую строку формы (новый объект ссылки) без данных,
	чтобы пользователь потом ввел URL и Anchor в TextControl.

	1) links — это массив объектов
		Например сначала:
		links = []
	2) Нажали “Add Link”
	setLinks([...links, { url: "", anchor: "" }]);
		...links — копирует все существующие ссылки (важно: нельзя мутировать старый массив напрямую)
		{ url: "", anchor: "" } — добавляет новый объект в конец массива
	После первого добавления будет:
		links = [
  			{ url: "", anchor: "" }
		]
	После второго:
		links = [
	  		{ url: "", anchor: "" },
	  		{ url: "", anchor: "" }
		]
	3) Почему “пустые” строки норм?
	Потому что эти пустые значения — это начальные значения полей формы.
	TextControl ожидает строку:
	<TextControl value={link.url} ... />
	Если бы ты не задал "", а оставил undefined, будут приколы:
		* ворнинги “controlled/uncontrolled”
		* в некоторых случаях value станет undefined и инпут будет вести себя странно
	Поэтому лучше всегда начинать с пустых строк.

	filter оставляет элементы, для которых условие true.
	Тут условие: i !== index
	в updatedLinks попадут ВСЕ элементы, кроме того, у которого индекс равен index

	links = [A, B, C, D]
	removeLink(1)
	Индексы: 0 1 2 3
	Фильтр оставит: i !== 1 → оставляем 0,2,3
	Результат: [A, C, D] (B удалили)


	updateLink — как работает обновление конкретного поля

	const updateLink = (index, key, value) => {
  		const updatedLinks = [...links];
  		updatedLinks[index][key] = value;
  		setLinks(updatedLinks);
	};

	Шаг 1: const updatedLinks = [...links];
		Это делает поверхностную копию массива.

	Шаг 2: updatedLinks[index][key] = value;
	Берём объект на позиции index и меняем одно поле:
		key = "url" или "anchor"
		value = то, что ввёл пользователь
	Пример:
	links = [
  		{ url: "", anchor: "" }
	]
	updateLink(0, "url", "https://site.com")

	updatedLinks станет:
		[
		  { url: "https://site.com", anchor: "" }
		]

	Шаг 3: setLinks(updatedLinks);
	Передаём новый массив в атрибуты → Gutenberg сохраняет links в block attributes → компонент перерисуется.
* */
