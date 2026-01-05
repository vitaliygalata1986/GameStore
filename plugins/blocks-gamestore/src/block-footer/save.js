import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
export default function save({ attributes }) {
	const {copyrights, logos, links} = attributes;
	return (
		<div { ...useBlockProps.save() }>
			<div className='wrapper inner-footer'>
				<InnerBlocks.Content />
				<div className='footer-line'></div>
				<div className="footer-bottom">
					<div className='left-part'>
						{copyrights && (<p>{copyrights}</p>)}
						{logos && (
							<div className="footer-logos">
								{logos.map((logo, index) => (
									<a key={index} href={logo.url} target="_blank" rel="nofollow noreferrer">
										<img src={logo.image} className="light-logo" alt="Logo"/>
										<img src={logo.imageDark} className="dark-logo" alt="Logo"/>
									</a>
								))}
							</div>
						)}
					</div>
					<div className='right-part'>
						{links && (
							links.map((link, index) => (
								<a key={index} href={link.url} > {link.anchor}</a>
							))
						)}
					</div>
				</div>
			</div>
		</div>
	);
}

/*
	<InnerBlocks.Content /> нужен только в save() для одного: вывести на фронтенде (и сохранить в HTML поста) то,
	что пользователь добавил внутрь блока через <InnerBlocks /> в edit().
	Что именно он делает
		В edit() у тебя обычно стоит <InnerBlocks /> — чтобы редактор позволял “вкладывать” другие блоки внутрь твоего блока (например: колонки, меню, текст, кнопки и т.д.).
		Когда пост сохраняется, WordPress должен записать вложенные блоки в контент (в post_content) как HTML-комментарии блоков.
		В save() <InnerBlocks.Content /> вставляет этот сохранённый контент (вложенные блоки) в итоговую разметку.

	Если его убрать — что будет?
		В редакторе вложенные блоки могут отображаться (в edit()), но:
		На фронтенде (и в сохранённой разметке) они не появятся, потому что ты их не “вывел” в save().
		Результат: пользователь добавил что-то внутрь блока, а на сайте это пропало.


* */
