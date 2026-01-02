import {useBlockProps, RichText, InspectorControls} from '@wordpress/block-editor';
import {PanelBody, TextControl, TextareaControl, Button, ToggleControl} from "@wordpress/components";
import {useState} from "@wordpress/element";
import './editor.scss';

const FAQItem = ({index, faq, onTitleChange, onDescriptionChange, onRemove}) => {
	return (
		<>
			<div className="gutenberg-faq-item">
				<TextControl
					label="Qusestion"
					value={faq.title}
					onChange={(title) => onTitleChange(title, index)}
				/>
				<TextareaControl
					label="Answer"
					value={faq.description}
					onChange={(description) => onDescriptionChange(description, index)}
				/>
				<Button
					className="components-button is-secondary"
					isDestructive
					onClick={() => onRemove(index)}
				>Remove Item</Button>
			</div>
			<br/>
		</>
	);
}
export default function Edit({attributes, setAttributes}) {
	const {title, margin} = attributes;
	const [faqs, setFaqs] = useState(attributes.faqs || []);
	const addFAQ = () => {
		const updated = [...faqs, {title: '', description: ''}];
		setFaqs(updated);
		setAttributes({faqs: updated}); // нужно, чтобы новый список FAQ сохранился как данные блока, а не только временно отобразился в редакторе.
	};

	// меняется и вид в редакторе, и данные, которые Gutenberg сохранит.
	const onFAQChange = (updatedFAQ, index) => {
		const updatedFaqs = [...faqs];       // копия массива
		updatedFaqs[index] = updatedFAQ;     // заменяем элемент по индексу
		setFaqs(updatedFaqs);                // обновили UI
		setAttributes({faqs: updatedFaqs}); // сохранили в блок
	};
	const handleTitleChange = (newTitle, index) => {
		const updatedFAQ = {...faqs[index], title: newTitle};
		onFAQChange(updatedFAQ, index);
	};
	const handleDescriptionChange = (newDescription, index) => {
		const updatedFAQ = {...faqs[index], description: newDescription};
		onFAQChange(updatedFAQ, index);
	};
	const removeFAQ = (index) => {
		/*
			const updatedFaqs = [...faqs];
			updatedFaqs.splice(index, 1); // мутируешь уже копию, а не faqs
			setFaqs(updatedFaqs);
			setAttributes({faqs: updatedFaqs});
		*/

		const updatedFaqs = faqs.filter((_, i) => i !== index);
		setFaqs(updatedFaqs);
		setAttributes({ faqs: updatedFaqs });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="FAQs Setting">
					<TextControl label="Title" value={title} onChange={(title) => setAttributes({title})}/>
					<ToggleControl
						label="Margins Zero"
						checked={margin}
						onChange={(margin) => setAttributes({margin})}
					/>
					{faqs.map((faq, index) => (
						<FAQItem
							key={index}
							index={index}
							faq={faq}
							onTitleChange={handleTitleChange}
							onDescriptionChange={handleDescriptionChange}
							onRemove={removeFAQ}
						/>
					))}
					<Button className="components-button is-primary" onClick={addFAQ}>Add FAQ</Button>
				</PanelBody>
			</InspectorControls>

			<div
				{...useBlockProps({className: `${margin ? 'no-margin' : ''}`})}
			>
				<div className="wrapper faq-inner">
					<RichText
						tagName="h2"
						className="faq-title"
						value={title}
						onChange={(title) => setAttributes({title})}
					/>

					{faqs.map((faq, index) => (
						<div key={index} className="faq-item">
							<RichText
								tagName="div"
								className="faq-item-title"
								value={faq.title}
								onChange={(newTitle) => handleTitleChange(newTitle, index)}
							/>
							<RichText
								tagName="div"
								className="faq-item-description"
								value={faq.description}
								onChange={(newDescription) => handleDescriptionChange(newDescription, index)}
							/>
						</div>
					))}

				</div>
			</div>
		</>
	)
}

/*
	У тебя есть 2 “хранилища” списка FAQ:
	1. Локальное состояние React
	const [faqs, setFaqs] = useState(attributes.faqs || []);
    Это нужно, чтобы интерфейс в редакторе мгновенно обновлялся.

    2. Атрибуты Gutenberg блока (то, что сохраняется в пост)
    setAttributes({ faqs: updatedFaqs });
    Идея: любое изменение списка → обновляем и faqs, и attributes.faqs.

	Если сделать только:
	setFaqs(updated)
		то ты увидишь изменения в редакторе, но это будет просто React-стейт компонента Edit — временный.
		Перезагрузишь страницу / закроешь редактор / переключишься — и изменения могут пропасть, потому что в блоке они не записались.
	А когда ты делаешь:
	setAttributes({ faqs: updated })
		ты говоришь Gutenberg:
		“вот новое значение атрибутов блока, сохрани его как данные блока”.
	И тогда эти данные попадут в attributes и будут доступны в save.js.

	2) Тогда зачем save.js?

		Потому что Gutenberg работает так:
		Edit (edit.js) — как блок выглядит в редакторе + как пользователь меняет данные.
		Save (save.js) — как блок превращается в финальную разметку, которая сохранится в контент записи (в post_content) и будет показана на сайте.
		То есть:
			В edit.js ты нажал “Add FAQ” → обновил attributes.faqs через setAttributes.
			Gutenberg запоминает эти атрибуты и при сохранении записи вызывает save.js.
			save.js берет attributes и рендерит HTML, который сохранится в базе.

	3) При сохранении записи:
		Gutenberg вызывает save.js, и он делает:
		faqs.map(...)
	и генерирует HTML, например:
	<div class="faq-item">
	  <div class="faq-item-title">Q1</div>
	  <div class="faq-item-description">A1</div>
	</div>

	<div class="faq-item">
	  <div class="faq-item-title">Q2</div>
	  <div class="faq-item-description">A2</div>
	</div>

	И вот этот HTML реально сохранится в контент записи и будет на фронте.

	4) Самая короткая формула
		* setAttributes(...) = сохранить данные блока
		* save.js = превратить сохранённые данные в HTML на сайте

	Без setAttributes → save.js просто не получит новые данные.
	Без save.js → данные будут сохранены, но не будет разметки, что вывести на фронте (для обычных блоков).

    5) Удаление FAQ (removeFAQ)

    const removeFAQ = (index) => {
	  const updatedFaqs = [...faqs];
	  updatedFaqs.splice(index, 1);
	  setFaqs(updatedFaqs);
	  setAttributes({ faqs: updatedFaqs });
	};

	Допустим:

	faqs = [
	  { title: "Q1", description: "A1" },
	  { title: "Q2", description: "A2" },
	  { title: "Q3", description: "A3" }
	]

	Нажал Remove на index = 1 (второй элемент “Q2”):
	splice(1, 1) удаляет 1 элемент начиная с позиции 1
	Получаем:
	updatedFaqs = [
	  { title: "Q1", description: "A1" },
	  { title: "Q3", description: "A3" }
	]
	Затем:
		setFaqs(updatedFaqs) → UI обновился
		setAttributes({faqs: updatedFaqs}) → сохранение обновилось
	“Q2” исчез и больше не сохранится.

	splice() мутирует, но у тебя он мутирует копию, так что это ок.
	filter() применять можно и часто лучше, потому что он сразу иммутабельный.

* */
