<?php defined( 'ABSPATH' ) || die ?>

<script id="tmpl-rwmb-video-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
	<# if( _.indexOf( i18nRwmbVideo.extensions, data.url.substr( data.url.lastIndexOf('.') + 1 ) ) > -1 ) { #>
		<video controls="controls" class="rwmb-video-element" preload="metadata"
			<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
			<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
			<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
			<source type="{{ data.mime }}" src="{{ data.url }}"/>
		</video>
	<# } else { #>
		<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
			<img src="{{ data.image.src }}" />
		<# } else { #>
			<img src="{{ data.icon }}" />
		<# } #>
	<# } #>
	<div class="rwmb-media-info">
		<a href="{{{ data.url }}}" class="rwmb-file-title" target="_blank">
			<# if( data.title ) { #>
				{{{ data.title }}}
			<# } else { #>
				{{{ i18nRwmbMedia.noTitle }}}
			<# } #>
		</a>
		<div class="rwmb-file-name">{{{ data.filename }}}</div>
		<div class="rwmb-media-actions">
			<a class="rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				{{{ i18nRwmbMedia.edit }}}
			</a>
			<a href="#" class="rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				{{{ i18nRwmbMedia.remove }}}
			</a>
		</div>
	</div>
</script>
