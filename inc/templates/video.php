<script id="tmpl-rwmb-video-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
	<div class="rwmb-media-preview">
		<div class="rwmb-media-content">
			<div class="centered">
				<# if( _.indexOf( i18nRwmbVideo.extensions, data.url.substr( data.url.lastIndexOf('.') + 1 ) ) > -1 ) { #>
				<div class="rwmb-video-wrapper">
					<video controls="controls" class="rwmb-video-element" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</video>
				</div>
				<# } else { #>
					<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
						<img src="{{ data.image.src }}" />
					<# } else { #>
						<img src="{{ data.icon }}" />
					<# } #>
				<# } #>
			</div>
		</div>
	</div>
	<div class="rwmb-media-info">
		<h4>
			<a href="{{{ data.url }}}" target="_blank" title="{{{ i18nRwmbMedia.view }}}">
				<# if( data.title ) { #> {{{ data.title }}}
					<# } else { #> {{{ i18nRwmbMedia.noTitle }}}
				<# } #>
			</a>
		</h4>
		<p>{{{ data.mime }}}</p>
		<p>
			<a class="rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				<span class="dashicons dashicons-edit"></span>{{{ i18nRwmbMedia.edit }}}
			</a>
			<a href="#" class="rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				<span class="dashicons dashicons-no-alt"></span>{{{ i18nRwmbMedia.remove }}}
			</a>
		</p>
	</div>
</script>
