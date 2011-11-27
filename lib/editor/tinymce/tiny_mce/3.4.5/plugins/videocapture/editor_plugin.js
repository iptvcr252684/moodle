/**
 * @author Kavalerchik Nadav <nadavkav@gmail.com>
 */

(function() {
	var each = tinymce.each;

	tinymce.PluginManager.requireLangPack('videocapture');

	tinymce.create('tinymce.plugins.VideoCapturePlugin', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;
			t.url = url;
            lang = tinyMCE.activeEditor.getParam('language');

			// Register commands
			ed.addCommand('mceVideoCapture', function() {
				ed.windowManager.open({
					file : url + '/videocapture.php?language=' + lang,
					width : 480 + parseInt(ed.getLang('media.delta_width', 0)),
					height : 470 + parseInt(ed.getLang('media.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('videocapture', {
                    title : 'Video Capture',
                    image : url + '/img/icon.png',
                    cmd : 'mceVideoCapture'});

		},

		_parse : function(s) {
			return tinymce.util.JSON.parse('{' + s + '}');
		},

		getInfo : function() {
			return {
				longname : 'Video Capture',
				author : 'Kavalerchik Nadav <nadavkav@gmail.com>',
				version : "1.0"
			};
		}

	});

	// Register plugin
	tinymce.PluginManager.add('videocapture', tinymce.plugins.VideoCapturePlugin);
})();
