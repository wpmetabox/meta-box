<script id="tmpl-rwmb-media-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
	<div class="rwmb-file-icon">
		<# if ( 'image' === data.type && data.sizes ) { #>
			<# if ( data.sizes.thumbnail ) { #>
				<img src="{{{ data.sizes.thumbnail.url }}}">
			<# } else { #>
				<img src="{{{ data.sizes.full.url }}}">
			<# } #>
		<# } else { #>
			<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
				<img src="{{ data.image.src }}" />
			<# } else { #>
				<img src="{{ data.icon }}" />
			<# } #>
		<# } #>
	</div>
	<div class="rwmb-file-info">
		<a href="{{{ data.url }}}" class="rwmb-file-title" target="_blank">
			<# if( data.title ) { #>
				{{{ data.title }}}
			<# } else { #>
				{{{ i18nRwmbMedia.noTitle }}}
			<# } #>
		</a>
		<div class="rwmb-file-name">{{{ data.filename }}}</div>
		<div class="rwmb-file-actions">
			<a class="rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				{{{ i18nRwmbMedia.edit }}}
			</a>
			<a href="#" class="rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				{{{ i18nRwmbMedia.remove }}}
			</a>
		</div>
	</div>
</script>

<script id="tmpl-rwmb-media-status" type="text/html">
	<# if ( data.maxFiles > 0 ) { #>
		{{{ data.length }}}/{{{ data.maxFiles }}}
		<# if ( 1 < data.maxFiles ) { #>{{{ i18nRwmbMedia.multiple }}}<# } else {#>{{{ i18nRwmbMedia.single }}}<# } #>
	<# } #>
</script>

<script id="tmpl-rwmb-media-button" type="text/html">
	<a class="button">{{{ data.text }}}</a>
</script>
