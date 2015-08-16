(function($) {
	$.fn.dragndrop = function(options) {
		var rowCount = 0;
		var mainObject = $(this);
		var singleStatusObject = null;
		var galleryImagesIndex = 0;

		var isEventSupported = (function() {
			var TAGNAMES = {
				'select': 'input',
				'change': 'input',
				'submit': 'form',
				'reset': 'form',
				'error': 'img',
				'load': 'img',
				'abort': 'img'
			};

			function isEventSupported(eventName, element) {
				element = element || document.createElement(TAGNAMES[eventName] || 'div');
				eventName = 'on' + eventName;

				// When using `setAttribute`, IE skips "unload", WebKit skips "unload" and "resize", whereas `in` "catches" those
				var isSupported = eventName in element;

				if (!isSupported) {
					// If it has no `setAttribute` (i.e. doesn't implement Node interface), try generic element
					if (!element.setAttribute) {
						element = document.createElement('div');
					}

					if (element.setAttribute && element.removeAttribute) {
						element.setAttribute(eventName, '');
						isSupported = typeof element[eventName] == 'function';

						// If property was created, "remove it" (by setting value to `undefined`)
						if (typeof element[eventName] != 'undefined') {
							element[eventName] = undefined;
						}

						element.removeAttribute(eventName);
					}
				}

				element = null;
				return isSupported;
			}
			return isEventSupported;
		})();

		var isFileAPIEnabled = function() {
			return !!window.FileReader;
		};

		function preUploadDefensive() {
			if ($.inArray(uploadType, ["file", "image", "gallery"]) < 0) {
				return false;
			}

			if (!uploadTarget) {
				return false;
			}
		}

		// Handle file upload
		function handleFileUpload(files, obj) {
			for (var i = 0; i < files.length; i++) {
				if (uploadValidation(files[i])) {
					var fd = new FormData();
					fd.append('dragFile', files[i]);

					// Using this we can set progress.
					var uniqueId = Date.now();
					var singleElement = $('<div>').attr('id', 'div_media_single_' + uniqueId).addClass('reditemDragnDrop-single-element');
					$('#statusBar_' + uploadTarget).after(singleElement);
					var status = new createStatusbar($(singleElement));
					status.setFileNameSize(files[i].name, files[i].size);

					sendFileToServer(fd, status, singleElement);
				}
			}
		}

		function createStatusbar(obj) {
			rowCount++;
			var row = "odd";
			if (rowCount % 2 == 0) row = "even";
			this.statusbar = $("<div class='statusbar " + row + " status_" + rowCount + "'></div>");
			this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
			this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
			this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
			this.abort = $("<div class='abort'>" + Joomla.JText._("COM_REDEVENT_UPLOAD_ABORT") + "</div>").appendTo(this.statusbar);
			obj.append(this.statusbar);

			this.setFileNameSize = function(name, size) {
				var sizeStr = "";
				var sizeKB = size / 1024;
				if (parseInt(sizeKB) > 1024) {
					var sizeMB = sizeKB / 1024;
					sizeStr = sizeMB.toFixed(2) + " MB";
				} else {
					sizeStr = sizeKB.toFixed(2) + " KB";
				}

				this.filename.html(name);
				this.size.html(sizeStr);
			}

			this.setProgress = function(progress) {
				var progressBarWidth = progress * this.progressBar.width() / 100;
				this.progressBar.find('div').animate({
					width: progressBarWidth
				}, 10).html(progress + "% ");

				if (parseInt(progress) >= 100) {
					this.abort.hide();
				}
			}

			this.setAbort = function(jqxhr) {
				var sb = this.statusbar;

				this.abort.click(function() {
					jqxhr.abort();
					sb.hide();
				});
			}
		}

		function sendFileToServer(formData, status, parentDiv) {
			var uploadURL = options.url; //Upload URL
			var extraData = {}; //Extra Data.
			var jqXHR = jQuery.ajax({
				xhr: function() {
					var xhrobj = jQuery.ajaxSettings.xhr();
					if (xhrobj.upload) {
						xhrobj.upload.addEventListener('progress', function(event) {
							var percent = 0;
							var position = event.loaded || event.position;
							var total = event.total;

							if (event.lengthComputable) {
								percent = Math.ceil(position / total * 100);
							}

							//Set progress
							status.setProgress(percent);
						}, false);
					}

					return xhrobj;
				},
				url: uploadURL + '&uploadType=' + uploadType + '&uploadTarget=' + uploadTarget,
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				data: formData,
				success: function(data) {
					responseHandlerProcess(data, status, parentDiv);
				}
			});

			status.setAbort(jqXHR);
		}

		/**
		 * Method fprocess on handle reponse from upload image service
		 *
		 * @param   string  data       Data from service
		 * @param   object  status     Status object
		 * @param   object  parentDiv  Parent object
		 *
		 * @return  void
		 */
		function responseHandlerProcess(data, status, parentDiv) {
			status.setProgress(100);

			if (uploadType == 'file') {
				$('#cform_dragndrop_upload' + uid).val(data.trim());
			}

			if (uploadType == 'gallery') {
				$('#cform_dragndrop_upload' + uid).val($('#cform_dragndrop_upload' + uid).val() + ',' + data.trim());
				status.deleteFile = $("<div class='deleteFile'><a class='dragndrop-delete-file'><img src='' alt='" + Joomla.JText._("COM_REDEVENT_UPLOAD_DELETE_FILE") + "'/></a></div>").appendTo(status.statusbar);

				if (!Date.now) {
					Date.now = function() {
						return new Date().getTime();
					};
				}

				status.preview = $('#gallery_preview_' + uindex);

				var uindex = Date.now();
				var imgPreview = $(parentDiv);

				galleryImagesIndex = $('input[name="jform[customfield_gallery_default][' + uploadTarget + ']"]').length;

				imgPreview.append('<div id="div_media_' + uindex + '" class="media"><div class="img-preview-container" style="position:relative;"><img id="gallery_preview_' + uindex + '" src="" class="img-polaroid" style="max-width: 100%; max-height: 100%;" /><label class="radio" for="reditemGallery-DefaultImage-Index-' + uindex + '"><input id="reditemGallery-DefaultImage-Index-' + uindex + '" class="reditem_gallery_default_image_index" type="radio" name="jform[customfield_gallery_default][' + uploadTarget + ']" value="' + galleryImagesIndex + '" /> ' + Joomla.JText._("COM_REDEVENT_CUSTOMFIELD_GALLERY_SET_DEFAULT") + '</label></div></div>');

				var preview = $('#gallery_preview_' + uindex);

				preview.attr('src', img_preview_path + data.trim()).load(function() {
					if (isCropEnable) {
						var divMedia = $('#div_media_' + uindex);

						// Clear old crop button
						if ($('#div_media_' + uindex + ' > .div-cropping-outer').length) {
							$('#div_media_' + uindex + ' > .div-cropping-outer').remove();
						}

						// Add new crop area
						divMedia.append('<div class="div-cropping-outer"><a id="btnCrop_gallery_' + uindex + '" class="btn btnCrop">' + Joomla.JText._("COM_REDEVENT_FEATURE_CROP_BTN_LBL") + '</a></div>');

						preview.reditemCropImage({
							cropAreaId: "crop_area_" + uindex,
							cropWidth: cropWidth,
							cropHeight: cropHeight,
							cropBtn: 'btnCrop_gallery_' + uindex,
							keepRatio: keepRatio
						});
					}
				});

				// Call function for update index of image
				updateImageDefaultIndex();

				status.deleteFile.click(function() {
					$('#cform_dragndrop_upload' + uid).val($('#cform_dragndrop_upload' + uid).val().replace(',' + data.trim(), ''));
					status.statusbar.remove();
					$('#div_media_' + uindex).parent().remove();

					// Call function for update index of image
					updateImageDefaultIndex();
				});
			}

			if (uploadType == 'image') {
				// Delete old one if exist already
				if (singleStatusObject != null) {
					$('#' + singleStatusObject).find('.deleteFile').click();
					$('#' + singleStatusObject).remove();
					singleStatusObject = $(parentDiv).attr('id');
				} else {
					singleStatusObject = $(parentDiv).attr('id');
				}

				$('#cform_dragndrop_upload' + uid).val(data.trim());

				if (options.hasOwnProperty('img_hidden_value')) {
					var img_hidden = $("#" + options.img_hidden_value);
					img_hidden.val(data.trim());
				}

				status.deleteFile = $("<div class='deleteFile'><a class='dragndrop-delete-file'><img src='' alt='" + Joomla.JText._("COM_REDEVENT_UPLOAD_DELETE_FILE") + "'/></a></div>").appendTo(status.statusbar);

				// Add preview image content
				var imgPreview = $("#" + img_preview);

				if (!$('#img_preview_' + uploadTarget).length) {
					imgPreview.prepend('<div class="pull-left"><div class="img-preview-container" style="position:relative;"><img id="img_preview_' + uploadTarget + '" src="" class="img-polaroid" style="max-width: 100%; max-height: 100%;" /></div></div>');
				} else {
					$('#img_preview_' + uploadTarget).css({
						'max-height': '100%',
						'max-width': '100%'
					});
				}

				$('#img_preview_' + uploadTarget).attr('src', img_preview_path + data.trim());
				$('#img_preview_' + uploadTarget).load(function() {
					if (isCropEnable) {
						// Clear old crop button
						if ($('#' + imgPreview.attr('id') + ' > .div-cropping-outer').length) {
							$('#' + imgPreview.attr('id') + ' > .div-cropping-outer').remove();
						}

						// Add new crop area
						imgPreview.append('<div class="div-cropping-outer"><a id="btnCrop_' + uploadTarget + '" class="btn btnCrop">' + Joomla.JText._("COM_REDEVENT_FEATURE_CROP_BTN_LBL") + '</a></div>');

						$(this).reditemCropImage({
							cropAreaId: "crop_area_" + uploadTarget,
							cropWidth: cropWidth,
							cropHeight: cropHeight,
							cropBtn: 'btnCrop_' + uploadTarget,
							keepRatio: keepRatio
						});
					}
				});

				status.deleteFile.click(function() {
					$('#cform_dragndrop_upload' + uid).val('');
					$(imgPreview).find('.div-cropping-outer').remove();
					$(imgPreview).find('.img-preview-container').remove();
					$('#img_preview_' + uploadTarget).remove();
					status.statusbar.remove();

					// Re-add default image if available
					if (defaultImgPath != '') {
						var imgPreviewContainer = $('<div>');
						$(imgPreviewContainer).addClass('img-preview-container')
							.css('position', 'relative');
						$('<img>').attr('id', 'img_preview_' + uploadTarget)
							.attr('src', defaultImgPath)
							.addClass('img-polaroid')
							.css('max-width', previewWidth + 'px')
							.css('max-height', previewHeight + 'px')
							.css('margin-right', '20px')
							.appendTo($(imgPreviewContainer));
						$(imgPreviewContainer).appendTo($(imgPreview));
					}
				});
			}
		}

		function generateUid(separator) {
			var delim = separator || "-";

			function S4() {
				return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
			}

			return (S4() + S4() + delim + S4() + delim + S4() + delim + S4() + delim + S4() + S4() + S4());
		};

		function uploadValidation(file) {
			if (file.type == "") {
				alert(Joomla.JText._("COM_REDEVENT_UPLOAD_FILE_INVALID"));
				return false;
			} else {
				var extTemp = file.type.split('/');

				if ($.inArray(extTemp[1], allowExt) < 0) {
					alert(Joomla.JText._("COM_REDEVENT_UPLOAD_FILE_INVALID"));
					return false;
				}

				if ($.inArray(file.type, allowMime) < 0) {
					alert(Joomla.JText._("COM_REDEVENT_UPLOAD_FILE_INVALID"));
					return false;
				}
			}

			if (file.size == 0) {
				alert(Joomla.JText._("COM_REDEVENT_UPLOAD_FILE_INVALID"));
				return false;
			}

			if (allowSize > 0) {
				if (file.size > allowSize) {
					alert(Joomla.JText._("COM_REDEVENT_UPLOAD_FILE_TOO_BIG"));
					return false;
				}
			}

			return true;
		}

		/**
		 * Method for update the index of uploaded images. Only for Gallery upload type
		 *
		 * @return  void
		 */
		function updateImageDefaultIndex()
		{
			if ($(mainObject).parent().find('.reditemDragnDrop-single-element').length > 0) {
				var count = $(mainObject).parent().find('.reditemDragnDrop-single-element').length;

				$(mainObject).parent().find('.reditemDragnDrop-single-element').each(function(index){
					var value = count - index;
					var optionObj = $(this).find('.reditem_gallery_default_image_index');
					$(optionObj).val(value);
				});

				if ($(mainObject).parent().find('.reditemDragnDrop-single-element input.reditem_gallery_default_image_index[type="radio"]:checked').length <= 0) {
					$(mainObject).parent().find('.reditemDragnDrop-single-element input.reditem_gallery_default_image_index[type="radio"]').first().prop('checked', true);
				}
			}
		}

		if (isEventSupported('dragenter') && isEventSupported('dragover') && isEventSupported('drop')) {
			var uploadType = mainObject.attr('upload-type');
			var uploadTarget = mainObject.attr('target');
			var inputTarget = mainObject.attr('input-target');

			// Check if file input has required.
			if ($('#' + inputTarget).prop('required')) {
				// Remove required attribute on file input
				$('#' + inputTarget).prop('required', false);

				// Add required attribute on target input
				$('#' + uploadTarget).prop('required', true);
			}

			preUploadDefensive();

			var uid = generateUid('_');
			var dragText = options['text'];
			var keepOldProcessBar = 0;

			if (uploadType == 'image') {
				dragText = Joomla.JText._("COM_REDEVENT_DRAG_AN_IMAGE");
			}

			if (uploadType == 'gallery') {
				dragText = Joomla.JText._("COM_REDEVENT_DRAG_IMAGES");
			}

			if (!isFileAPIEnabled()) {
				dragText = Joomla.JText._("COM_REDEVENT_DRAG_FEATURE_NOT_SUPPORT");
			}

			// Include browse link
			var includeBrowse = 0;

			if (options.config.hasOwnProperty('includeBrowse')) {
				includeBrowse = parseInt(options.config.includeBrowse);
				if (isNaN(includeBrowse)) includeBrowse = 0;
			}

			// Add elements for dragndrop process
			mainObject.after('<div id="statusBar_' + uploadTarget + '"></div>');
			mainObject.after('<div id="reditem_dragselect_' + uid + '" class="dragselect">' + dragText + '</div>');
			mainObject.after('<input type="hidden" id="cform_dragndrop_upload' + uid + '" name="jform[custom' + uploadTarget + ']" value="" />');

			// Include Browse option enabled
			if ((includeBrowse == 1) && isFileAPIEnabled()) {
				$('<span>').attr('href', 'javascript:void(0)')
					.html(Joomla.JText._("COM_REDEVENT_DRAG_AND_DROP_BROWSE"))
					.appendTo($('#reditem_dragselect_' + uid))
					.click(function(event) {
						event.preventDefault();
						$('#' + inputTarget).click();
					});
			}

			// If browser doesn't support upload file AJAX
			if (!isFileAPIEnabled()) {
				$('<input>').attr('type', 'file')
					.attr('name', 'dragFile')
					.appendTo($('#reditem_dragselect_' + uid))
					.ajaxfileupload({
						action: options.url + '&uploadType=' + uploadType + '&uploadTarget=' + uploadTarget,
						params: {
							extra: 'info'
						},
						onComplete: function(response) {
							if ($('#' + inputTarget).attr('old-name'))
								$('#' + inputTarget).attr('name', $('#' + inputTarget).attr('old-name')).removeAttr('old-name');

							// Using this we can set progress.
							var uniqueId = Date.now();
							var singleElement = $('<div>').attr('id', 'div_media_single_' + uniqueId).addClass('reditemDragnDrop-single-element');
							$('#statusBar_' + uploadTarget).after(singleElement);
							var status = new createStatusbar($(singleElement));

							responseHandlerProcess(response, status, singleElement);
						},
						onStart: function() {
							$('#' + inputTarget).attr('old-name', $('#' + inputTarget).attr('name')).attr('name', 'dragFile');
						},
						onCancel: function() {
							if ($('#' + inputTarget).attr('old-name'))
								$('#' + inputTarget).attr('name', $('#' + inputTarget).attr('old-name')).removeAttr('old-name');
						}
					});
			}

			var obj = $('#reditem_dragselect_' + uid);
			var files = null;
			var allowExt = options.config.ext.split(',');
			var allowMime = options.config.mime.split(',');
			var allowSize = -1;

			if (options.config.hasOwnProperty('size')) {
				try {
					allowSize = parseInt(options.config.size);
				} catch (err) {
					allowSize = -1;
				}
			}

			if (options.config.hasOwnProperty('keepOldProcessBar')) {
				try {
					keepOldProcessBar = parseInt(options.config.keepOldProcessBar);
				} catch (err) {
					keepOldProcessBar = 0;
				}
			}

			// Preview image div ID
			var img_preview = "";

			if (options.hasOwnProperty('img_preview')) {
				try {
					img_preview = options.img_preview;
				} catch (err) {
					img_preview = "";
				}
			}

			// Review path param
			var img_preview_path = ""

			if (options.hasOwnProperty('img_preview_path')) {
				try {
					img_preview_path = options.img_preview_path;
				} catch (err) {
					img_preview_path = "";
				}
			}

			// Default image path
			var defaultImgPath = '';

			if (options.hasOwnProperty('default_img')) {
				try {
					defaultImgPath = options.default_img;
				} catch (error) {
					defaultImgPath = '';
				}
			}

			var isCropEnable = false;
			var previewWidth = "300";
			var previewHeight = "300";
			var cropWidth = "";
			var cropHeight = "";
			var keepRatio = false;

			if (options.hasOwnProperty('cropConfig')) {
				if (options.cropConfig.hasOwnProperty('isEnable')) {
					if (options.cropConfig.isEnable == "1") {
						isCropEnable = true;
					}

					if (options.cropConfig.hasOwnProperty('previewWidth')) {
						previewWidth = options.cropConfig.previewWidth;
					}

					if (options.cropConfig.hasOwnProperty('previewHeight')) {
						previewHeight = options.cropConfig.previewHeight;
					}

					if (options.cropConfig.hasOwnProperty('cropWidth')) {
						cropWidth = options.cropConfig.cropWidth;
					}

					if (options.cropConfig.hasOwnProperty('cropHeight')) {
						cropHeight = options.cropConfig.cropHeight;
					}

					if (options.cropConfig.hasOwnProperty('keepRatio')) {
						keepRatio = options.cropConfig.keepRatio;
					}
				}
			}

			obj.on('dragenter', function(e) {
				e.stopPropagation();
				e.preventDefault();
			});

			obj.on('dragover', function(e) {
				e.stopPropagation();
				e.preventDefault();
			});

			obj.on('drop', function(e) {
				e.preventDefault();
				if ((e.type === "drop") && isFileAPIEnabled()) {
					preUploadDefensive();
					files = (!e.dataTransfer) ? e.originalEvent.dataTransfer.files : e.dataTransfer.files;
					// If do not keep old process then clean them
					if ((!keepOldProcessBar) && (uploadType != "gallery")) {
						$("#statusBar_" + uploadTarget).empty();
					}

					// Check if uploadType is file or image then allow upload a file per time
					if (((uploadType == 'file') || (uploadType == 'image')) && (files.length > 1)) {
						alert(Joomla.JText._("COM_REDEVENT_UPLOAD_1_FILE_ONLY"));
						return;
					}

					handleFileUpload(files, obj);
				}
			});

			// Prevent outside the box
			$(document).on('dragenter', function(e) {
				e.stopPropagation();
				e.preventDefault();
			});

			$(document).on('dragover', function(e) {
				e.stopPropagation();
				e.preventDefault();
				obj.css('border', '2px dotted #0B85A1');
			});

			$(document).on('drop', function(e) {
				e.stopPropagation();
				e.preventDefault();
			});

			this.addInputTarget = function(inputTarget) {
				if (isFileAPIEnabled()) {
					$('#' + inputTarget).on('change', function(e) {
						e.preventDefault();
						preUploadDefensive();
						files = e.originalEvent.target.files;

						// If do not keep old process then clean them
						if ((!keepOldProcessBar) && (uploadType != "gallery")) {
							$("#statusBar_" + uploadTarget).empty();
						}

						// Check if uploadType is file or image then allow upload a file per time
						if (((uploadType == 'file') || (uploadType == 'image')) && (files.length > 1)) {
							alert(Joomla.JText._("COM_REDEVENT_UPLOAD_1_FILE_ONLY"));
							return;
						}

						handleFileUpload(files, obj);
					});
				}
			};

			// Add on select file of target
			if ((inputTarget != null) && isFileAPIEnabled()) {
				this.addInputTarget(inputTarget);
			}

			return this;
		}
	};
}(jQuery));