(function($) {
	$.fn.reditemCropImage = function(options) {
		/**
		 * Calculate top position
		 *
		 * @param   element  target  target to calculate
		 *
		 * @return  int
		 */
		function getAdjustTop(target) {
			return parseInt(target.css('padding-top').replace("px", "")) + parseInt(target.css('margin-top').replace("px", ""));
		}

		/**
		 * Calculate left position
		 *
		 * @param   element  target  target to calculate
		 *
		 * @return  int
		 */
		function getAdjustLeft(target) {
			return parseInt(target.css('padding-left').replace("px", "")) + parseInt(target.css('margin-left').replace("px", ""));
		}

		var target = $(this);
		var obj = getSize(target.attr('src'));
		var cropAreaId = "#" + options.cropAreaId;
		var cropBtnId = "#" + options.cropBtn;
		var cropWidth = options.cropWidth;
		var cropHeight = options.cropHeight;
		var keepRatio = options.keepRatio;
		var adjustTop = getAdjustTop(target);
		var adjustLeft = getAdjustLeft(target);
		var targetRatio = 0.0;

		// Calculate current ratio of image
		var tmpImg = new Image();
		tmpImg.src = target.attr('src');
		targetRatio = tmpImg.width / tmpImg.height;
		tmpImg = null;

		// No crop width & height defined
		if ((cropWidth == "") && (cropHeight == "")) {
			// Set crop area full size of target image.
			cropWidth = target.width();
			cropHeight = target.height();
		}
		// Crop Width has been set
		else if ((cropWidth != "") && (cropHeight == "")) {
			if (cropWidth > target.width())
				cropWidth = target.width();
			cropHeight = cropWidth / targetRatio;
		}
		// Crop Height has been set
		else if ((cropWidth == "") && (cropHeight != "")) {
			if (cropHeight > target.height())
				cropHeight = target.height();
			cropWidth = cropHeight * targetRatio;
		}
		// Crop Width & Height both set
		else {
			var cropRatio = cropWidth / cropHeight;

			// Calculate the crop area with image ratio
			if (cropRatio > targetRatio) {
				cropWidth = target.width();
				cropHeight = cropWidth / cropRatio;
			} else if (cropRatio < targetRatio) {
				cropHeight = target.height();
				cropWidth = cropHeight * cropRatio;
			}
		}

		if (target.length) {
			// Remove current crop area (include overlay)
			$(cropAreaId).parent().remove();

			var overlayTop = adjustTop;
			var overlayLeft = adjustLeft;
			var overlayWidth = target.width();
			var overlayHeight = target.height();

			// Calculate border of target
			var borderHorizontal = ($(target).outerHeight() - $(target).innerHeight()) / 2;
			var borderVertical = ($(target).outerWidth() - $(target).innerWidth()) / 2;

			if (!isNaN(borderHorizontal)) {
				overlayTop += borderHorizontal;
				overlayHeight += borderHorizontal;
			}

			if (!isNaN(borderVertical)) {
				overlayLeft += borderVertical;
				overlayWidth += borderVertical;
			}

			// Add overlay
			var overlay = $('<div>')
				.css({
					position: 'absolute',
					'z-index': 99,
					width: overlayWidth + 'px',
					height: overlayHeight + 'px',
					top: overlayTop + 'px',
					left: overlayLeft + 'px',
					overflow: 'hidden'
				});

			// Add crop area
			$('<div>')
				.attr("id", options.cropAreaId)
				.css({
					position: 'absolute',
					'z-index': 99,
					width: cropWidth + 'px',
					height: cropHeight + 'px',
					top: '0px',
					left: '0px',
					'box-shadow': "0 0 0 1000px rgba(0, 0, 0, 0.65)",
					'box-sizing': "border-box",
					overflow: "hidden"
				})
				.appendTo(overlay);

			// Add to target
			target.parent().append(overlay);

			// if "keepRatio" has enable
			if (keepRatio == true) {
				$(cropAreaId).resizable({
					containment: target,
					handles: 'all',
					aspectRatio: cropWidth / cropHeight
				}).draggable({
					containment: target
				});

				tmpImg = null;
			} else {
				$(cropAreaId).resizable({
					containment: target,
					handles: 'all'
				}).draggable({
					containment: target
				});
			}

			afterUploadedImage();
		}

		$(cropBtnId).click(function(event) {
			event.preventDefault();
			var image_name = target.attr('src').split('/');

			if (image_name.length)
				image_name = image_name[image_name.length - 1];

			image_name = image_name.split('?');

			if (image_name.length)
				image_name = image_name[0];

			$.ajax({
				type: "POST",
				url: "index.php?option=com_reditem&task=item.ajaxCropImage",
				data: {
					image_name: image_name,
					top: $(cropAreaId).position().top,
					left: $(cropAreaId).position().left,
					width: $(cropAreaId).width(),
					height: $(cropAreaId).height(),
					previewWidth: target.width(),
					previewHeight: target.height()
				},
				success: function(e) {
					$(target).addClass('img-cropped');
					if (e == '1')
						target.attr('src', target.attr('src') + '?' + Math.random()).hide().fadeIn('fast');
					else
						alert(Joomla.JText._('COM_REDITEM_FEATURE_CROPIMAGE_FAIL'));
				}
			});

			return false;
		});
	};
}(jQuery));


/**
 * Hook method for fire after upload data
 *
 * @return  void
 */
function afterUploadedImage() {}