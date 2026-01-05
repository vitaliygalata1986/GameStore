import {useBlockProps, InnerBlocks, InspectorControls, MediaPlaceholder} from '@wordpress/block-editor';
import {PanelBody, TextControl, Button, __experimentalDivider as Divider} from '@wordpress/components';
import './editor.scss';
import {__} from '@wordpress/i18n';

const LinkRepeater = ({links, setLinks}) => {
	const addLink = () => {
		setLinks([...links, {url: "", anchor: ""}]);
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
const LogosRepeater = ({logos, setLogos}) => {
	const addLogo = () => {
		setLogos([...logos, {url: "", image: "", imageDark: ""}]);
	};
	const removeLogo = (index) => {
		const updateLogo = logos.filter((_, i) => i !== index);
		setLogos(updateLogo);
	};

	const updateLogo = (index, key, value) => {
		const updateLogo = [...logos];
		updateLogo[index][key] = value;
		setLogos(updateLogo);
	};

	return (
		<div className="logo-repeater">
			<h4>Manage Logos</h4>
			{logos.map((logo, index) => (
				<div key={index} className="logo-repeater-item">
					<TextControl
						label="URL"
						value={logo.url}
						onChange={(value) => updateLogo(index, "url", value)}
						placeholder="https://example.com"
					/>
					{logo.images && <img src={logo.images} alt="Background"/>}
					<br/>
					<MediaPlaceholder
						icon="format-image"
						labels={{title: 'Logo'}}
						onSelect={(media) => updateLogo(index, 'image', media.url)}
						accept="image/"
						allowedTypes={["image"]}
					/>
					<br/>
					{logo.imageDark && <img src={logo.imageDark} alt="Background"/>}
					<br/>
					<MediaPlaceholder
						icon="format-image"
						labels={{title: 'Dark Variant Logo'}}
						onSelect={(media) => updateLogo(index, 'imageDark', media.url)}
						accept="image/"
						allowedTypes={["image"]}
					/>
					<br/>
					<Button
						variant="secondary"
						onClick={() => removeLogo(index)}
						className="remove-logo-button"
					>
						Remove Logo
					</Button>
				</div>
			))}
			<br/>
			<Button variant="primary" onClick={addLogo} className="add-logo-button">
				Add Link
			</Button>
		</div>
	);
};

export default function Edit({attributes, setAttributes}) {
	const {copyrights, logos = [], links = []} = attributes;
	const setLinks = (newLinks) => setAttributes({links: newLinks});
	const setLogos = (newLogo) => setAttributes({logos: newLogo});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Footer Settings">
					<TextControl
						label="Copyrights"
						value={copyrights}
						onChange={(copyrights) => setAttributes({copyrights})}
					/>
					<Divider margin={8}/>
					<LinkRepeater links={links} setLinks={setLinks}/>
					<Divider margin={8}/>
					<LogosRepeater logos={logos} setLogos={setLogos}/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div className='inner-footer'>
					<InnerBlocks/>
					<div className="footer-line"></div>
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
		</>
	);
}

// Divider - компонент разделитель
// Если ты пишешь просто: <InnerBlocks /> - то Gutenberg не ограничивает список блоков,
// и пользователь может вставлять любые доступные в редакторе блоки (плюс то, что разрешено текущим контекстом/темой/плагинами).
// InnerBlocks - чтобы вставить внутрь нашего плагина блоки gutenberg

