/**
 * @author Dongsheng Cai <dongsheng@moodle.com>
 */
tinyMCEPopup.requireLangPack();

var oldWidth, oldHeight, ed, url;

if (url = tinyMCEPopup.getParam("media_external_list_url")) {
    document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
}

function init() {
    ed = tinyMCEPopup.editor;
    //document.getElementById('filebrowsercontainer').innerHTML = getBrowserHTML('filebrowser','src','media','media');
}

// Create a function that will receive data sent from the server
var ajaxRequest;  // The variable that makes Ajax possible!

try{
	// Opera 8.0+, Firefox, Safari
	ajaxRequest = new XMLHttpRequest();
} catch (e){
	// Internet Explorer Browsers
	try{
		ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try{
			ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e){
			// Something went wrong
			alert("Your browser broke!");
		}
	}
}

ajaxRequest.onreadystatechange = function(){
	if(ajaxRequest.readyState==4 && ajaxRequest.status==200){
        var f = document.forms[0];
        var url = f.FileName.value;
        var linkname = url.substring(url.lastIndexOf('/')+1);
        var h = '<a href="'+ajaxRequest.responseText+'">'+linkname+'</a>';
        ed.execCommand('mceInsertContent', false, h);
        tinyMCEPopup.close();

	}
}

function insertMedia() {
    ajaxRequest.open("GET", "move_tempfile_to_storage.php?audiofile=" + tempaudiofile + "&audiofolder=" + tempaudiofolder, true);
    ajaxRequest.send();

}

tinyMCEPopup.onInit.add(init);
