import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import './editor.scss';
import {__} from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
	const { memberLink, cartLink } = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title="Header Links">
					<TextControl
						label="Member Link"
						value={memberLink}
						onChange={(value) => setAttributes({memberLink: value})}
					/>
					<TextControl
						label={"Cart Link"}
						value={cartLink}
						onChange={(value) => setAttributes({cartLink: value})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div className='inner-header'>
					<InnerBlocks/>
					<div className='right-section'>
						<div className='header-search'>
							<svg width="36" height="36" viewBox="0 0 36 36" fill="none"
								 xmlns="http://www.w3.org/2000/svg">
								<path
									d="M28.29 27.66L23.61 22.95C26.97 19.2 26.7 13.44 22.95 10.05C19.2 6.66002 13.44 6.96002 10.05 10.71C6.66002 14.46 6.96002 20.22 10.71 23.61C14.19 26.76 19.5 26.76 22.98 23.61L27.69 28.32L28.29 27.66ZM16.83 25.05C12.3 25.05 8.61002 21.36 8.61002 16.83C8.61002 12.27 12.3 8.61002 16.83 8.61002C21.36 8.61002 25.05 12.3 25.05 16.83C25.05 21.36 21.36 25.05 16.83 25.05Z"
									fill="var(--action-main-svg, rgb(255, 255, 255))" fill-opacity="0.64"/>
								<path
									d="M16.8301 9.83984V10.7398C20.1901 10.7398 22.9201 13.4698 22.9201 16.8298H23.8201C23.8201 12.9598 20.7001 9.83984 16.8301 9.83984Z"
									fill="var(--action-main-svg, rgb(255, 255, 255))" fill-opacity="0.64"/>
							</svg>
						</div>
						<div className='header-mode-switcher'>
							<svg width="36" height="36" viewBox="0 0 36 36" fill="none"
								 xmlns="http://www.w3.org/2000/svg">
								<path d="M21 24V12" stroke="var(--action-main-svg, rgb(255, 255, 255))"
									  stroke-opacity="0.64" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M9 18H12" stroke="var(--action-main-svg, rgb(255, 255, 255))"
									  stroke-opacity="0.64" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M12.5098 9.51025L14.6398 11.6403"
									  stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
									  stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M21 6V9" stroke="var(--action-main-svg, rgb(255, 255, 255))"
									  stroke-opacity="0.64" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M21 30V27" stroke="var(--action-main-svg, rgb(255, 255, 255))"
									  stroke-opacity="0.64" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M12.5098 26.4899L14.6398 24.3599"
									  stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
									  stroke-linecap="round" stroke-linejoin="round"/>
								<path
									d="M21 24C24.3137 24 27 21.3137 27 18C27 14.6863 24.3137 12 21 12C17.6863 12 15 14.6863 15 18C15 21.3137 17.6863 24 21 24Z"
									stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
									stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						{cartLink &&
							(<div className='header-cart-link'>
								<a href={cartLink}>
									<svg width="36" height="36" viewBox="0 0 36 36" fill="none"
										 xmlns="http://www.w3.org/2000/svg">
										<path d="M7.71436 14.5718L9.42864 26.5718H26.5715L28.2858 14.5718"
											  stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
											  stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M12.8569 16.2859L14.5712 9.42871"
											  stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
											  stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M23.143 16.2859L21.4287 9.42871"
											  stroke="var(--action-main-svg, rgb(255, 255, 255))" stroke-opacity="0.64"
											  stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M6 14.5718H30" stroke="var(--action-main-svg, rgb(255, 255, 255))"
											  stroke-opacity="0.64" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</a>
							</div>)
						}
						{memberLink &&
							(<div className='header-member-link'>
								<a href={memberLink}>
									{__(
										'Member Area',
										'blocks-gamestore'
									)}</a>
							</div>)
						}
					</div>
				</div>
			</div>
		</>
	);
}

// InnerBlocks - ддя вставки
/*
	InnerBlocks — это «контейнер» для вложенных блоков в Гутенберге. С его помощью вы делаете свой блок-обёртку, внутри которой пользователь может вставлять другие блоки (параграфы, колонки, кнопки и т.п.), а вы можете ограничить, какие именно блоки разрешены, задать стартовый шаблон и режим блокировки.
* */

/*
InspectorControls — это панель настроек блока в правом сайдбаре редактора. В неё вы выносите поля для изменения атрибутов блока (input’ы, переключатели, селекты и т.п.).
* */
