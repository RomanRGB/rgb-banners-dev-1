jQuery(document).ready(function ($) {
	$('#rgb_banner1 .inside').append('<div style="width:100%;text-align:center;margin:10px 0;"><p><strong>Banner fields guideline:</strong></p><img style="height:auto;max-width:725px;width:100%;" src="/wp-content/plugins/rgb-banners/sample-banner.png" /></div>');
	
	$('.cmb2-id-banner-filter :radio').on('change', function() {
		var new_color = $('.cmb2-id-banner-filter :radio:checked').val();
		var old_color = $('#post_banner_img .content').attr('data-color');
		$('#post_banner_img .content').removeClass('gradient-' + old_color);
		$('#post_banner_img .content').addClass('gradient-' + new_color);
		$('#post_banner_img .content').attr('data-color', new_color);					
	});
	
	$('#banner_link').on('change', function() {
		$('#post_banner_img a').attr('href', $('#banner_link').val() );
	});
	$('#banner_label').on('change', function() {
		if ($('#banner_label').val()!='') {
			$('#post_banner_img .sponsored').text( $('#banner_label').val() );
		} else {
			$('#post_banner_img .sponsored').text( '&nbsp;' );
		}
	});
	$('.cmb2-id-banner-emoji :radio').on('change', function() {
		if ($('.cmb2-id-banner-emoji :radio:checked').val()=='') {
			$('#post_banner_img .icon').html( '&nbsp;' );
		} else {
			$('#post_banner_img .icon').html( '&#x' + $('.cmb2-id-banner-emoji :radio:checked').val() + ';' );
		}				
	});
	$('#banner_title').on('change', function() {
		if ($('#banner_title').val()!='') {
			$('#post_banner_img .headline').text( $('#banner_title').val() );
		} else {
			$('#post_banner_img .headline').text( '&nbsp;' );
		}			
	});
	$('#banner_underline').on('change', function() {
		if ($('#banner_overline').is(':checked')) {
			if ($('#banner_underline').val()!='') {
				$('#post_banner_img .overline').text( $('#banner_underline').val() );
			} else {
				$('#post_banner_img .overline').text( '&nbsp;' );
			}
			$('#post_banner_img .overline').show();
			$('#post_banner_img .underline').hide();
		} else {
			if ($('#banner_underline').val()!='') {
				$('#post_banner_img .underline').text( $('#banner_underline').val() );
			} else {
				$('#post_banner_img .underline').text( '&nbsp;' );
			}
			$('#post_banner_img .underline').show();
			$('#post_banner_img .overline').hide();
		}
	});
	$('#banner_overline').on('change', function() {
		if ($('#banner_overline').is(':checked')) {
			if ($('#banner_underline').val()!='') {
				$('#post_banner_img .overline').text( $('#banner_underline').val() );
			} else {
				$('#post_banner_img .overline').text( '&nbsp;' );
			}
			$('#post_banner_img .overline').show();
			$('#post_banner_img .underline').hide();
		} else {
			if ($('#banner_underline').val()!='') {
				$('#post_banner_img .underline').text( $('#banner_underline').val() );
			} else {
				$('#post_banner_img .underline').text( '&nbsp;' );
			}
			$('#post_banner_img .underline').show();
			$('#post_banner_img .overline').hide();
		}
	});
	$('#banner_label').on('change', function() {
		if ($('#banner_label').val()!='') {
			$('#post_banner_img .sponsored').text( $('#banner_label').val() );
		} else {
			$('#post_banner_img .sponsored').text( '&nbsp;' );
		}
	});
	$('#banner_button').on('change', function() {
		if ($('#banner_button').val()!='') {
			$('#post_banner_img .banner-button').text( $('#banner_button').val() );
			$('#post_banner_img .banner-button').show();
		} else {
			$('#post_banner_img .banner-button').text('');
			$('#post_banner_img .banner-button').hide();
		}
	});
	$('#banner_credit').on('change', function() {
		$('#post_banner_img .bottom').text( $('#banner_credit').val() );
	});
});
