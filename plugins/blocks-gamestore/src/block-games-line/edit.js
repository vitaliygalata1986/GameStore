import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls} from '@wordpress/block-editor';
import {PanelBody, TextControl} from '@wordpress/components';
import './editor.scss';
import placeholder from './img/default.png';

export default function Edit({attributes, setAttributes}) {
	const {count} = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'blocks-gamestore')}>
					<TextControl
						label={__('Count', 'blocks-gamestore')}
						value={count}
						onChange={(val) => setAttributes({count: parseInt(val, 10) || 0})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<img src={placeholder} alt="Alt"/>
			</div>
		</>
	);
}

/*
*  TextControl сохрнаяет строчные данные, а мы пережаем тип number, поэтому конвертируе в int
*  вот эта 10 это основание системы счисления (radix), то есть: «Парсь эту строку как число в десятичной системе».
*  А || 0 в конце значит: если результат parseInt(val, 10) — NaN (или другое falsy), то взять 0 по умолчанию.
* */
