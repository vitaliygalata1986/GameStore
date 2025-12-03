<?php
// This file is generated. Do not modify it manually.
return array(
	'block-slider' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/blocks-slider',
		'version' => '0.1.0',
		'title' => 'Blocks Slider',
		'category' => 'gamestore',
		'icon' => 'smiley',
		'description' => 'Slider hero',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'blocks-slider',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'source' => 'html',
				'selector' => '.hero-title'
			),
			'slides' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		)
	)
);
