var commentblocks = document.querySelectorAll('.dof_cpassedgradeform .dof_modal_wrapper');

if ( commentblocks ) {
	for (var i = 0; i < commentblocks.length; i++) {
		
		var label = commentblocks[i].querySelector('.grpjournal_comment_modal_label');
		var textareas = commentblocks[i].getElementsByTagName('textarea');
		
		if ( textareas ) {
			for (var t = 0; t < textareas.length; t++) {
				var textarea = textareas[t];
				textareas[t].addEventListener("blur", function( event ) {
					event = event || window.event;
					
					var currenttextareas = event.target.parentNode.getElementsByTagName('textarea');
					var has_comment = false;
					for (var temp = 0; temp < currenttextareas.length; temp++) {
						if ( currenttextareas[temp].value != "" ) {
							has_comment = true;
						}
					}
					
					var clabel = event.target.closest('.dof_modal_wrapper').querySelector('.grpjournal_comment_modal_label');
					if ( has_comment ) {
						clabel.classList.add('grpjournal_has_comments');
					} else {
						clabel.classList.remove('grpjournal_has_comments');
					}
				} );
			}
			
		}
	}
}