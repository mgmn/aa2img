function aa2imgWhiteBlack(color_char, color_back){
	color_char.value = "#000000";
	color_back.value = "#ffffff";
	$('#color_trans').prop('checked', false);
	aa2imgPickerInit();

	return false;
}
function aa2imgReverse(color_char, color_back){
	var t = color_char.value;
	color_char.value = color_back.value;
	color_back.value = t;
	aa2imgPickerInit();

	return false;
}
function aa2imgPickerHideToggle(){
	$(document).ready(function(){
		$("#pickerHideToggleLink").slideUp(jQueryAnimateSpeed, function(){
			$("#pickerField").slideDown(jQueryAnimateSpeed);
		});
	});
}
function aa2imgPickerInit(){
	$(document).ready(function() {
		var f = $.farbtastic('#picker');
		var p = $('#picker').css('opacity', 0.25);
		var selected;
		$('.colorwell')
			.each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
			.focus(function() {
				if (selected) {
					$(selected).css('opacity', 0.75).removeClass('colorwell-selected');
				}
				f.linkTo(this);
				p.css('opacity', 1);
				$(selected = this).css('opacity', 1).addClass('colorwell-selected');
			});
	});
}
aa2imgPickerInit();

var apiURL = './aa2img.php';
(
	function($){
		'use strict';

		var funcGetParams = function(){
			return {
				str: $('#str').val(),
				font: $('input[name=font]:checked').val(),
				color_char: $('#color_char').val(),
				color_back: $('#color_back').val(),
				color_trans: $('#color_trans').prop('checked')
			};
		}
		var funcAfterPost = function(res){
			$('#out').slideDown(250);
			$('#outA').attr('href', res.body);
			$('#outI').attr('src',  res.body);
		}

		$.fn.extend(
			{
				attachAPIonChange: function(){
					return $(this).change(
						function(){
							$.post(
								apiURL,
								funcGetParams(),
								function(res){ funcAfterPost(res) },
								'json'
							).fail( function(){} );
						}
					);
				},
				attachAPIonKeyUp: function(){
					return $(this).keyup(
						function(){
							$.post(
								apiURL,
								funcGetParams(),
								function(res){ funcAfterPost(res) },
								'json'
							).fail( function(){} );
						}
					);
				},
				attachAPIonClick: function(){
					return $(this).click(
						function(){
							$.post(
								apiURL,
								funcGetParams(),
								function(res){ funcAfterPost(res) },
								'json'
							).fail( function(){} );
						}
					);
				}
			}
		);
	}
)(jQuery);

$('#str').attachAPIonChange();
$('input[name="font"]').attachAPIonChange();
$('#color_char').attachAPIonKeyUp();
$('#color_back').attachAPIonKeyUp();
$('#color_trans').attachAPIonChange();
$('#reverseButton').attachAPIonClick();
$('#restoreWBButton').attachAPIonClick();
$('#picker').attachAPIonClick();
