if(!us) var us={};
if(!us.frayd) us.frayd={};
if(!us.frayd.editnotes) us.frayd.editnotes={};
(function(){
	var pub = us.frayd.editnotes;
	pub.priority = { 0: "low", 1: "med", 2: "high" };

	pub.init = function() {
		// setup drag event
		var $notes = jQuery("#frayd_edit_notes .note");
		pub.setupListeners( $notes );
	}

	pub.newNote = function( evt ) {
		evt.preventDefault();

		var $notes = jQuery("#frayd_edit_notes");

		// create, save note post type (attach to post, of course)
		jQuery.post("/wp-admin/admin.php", { action: "frayd_edit_notes_ajax_new_note", url: $notes.data("page-url") }, function( data ) {
			// place default, blank note on page
			var $note = jQuery( data );
			$notes.append( $note );

			pub.setupListeners( $note );
		});

		// setup note listeners
			// position change
			// color change
			// text change
			// delete
	}

	pub.setupListeners = function( $notes ) {
		if( $notes.length > 0 ) {
			$notes.each(function() {
				var $note = jQuery(this);

				// translate left position on mouseover
				$note.find(".move").on("mousedown", function() {
					var offset = $note.offset();
					$note.css({
						"margin-left": "",
						left: offset.left + "px"
					});
				});
				$note.find(".move").on("mouseup", function() {
					var winWidth = jQuery(window).width();
					var offset = $note.offset();
					var center_offset = offset.left - (winWidth/2);

					$note.css({
						"margin-left": center_offset + "px",
						"left": "50%",
						"top": offset.top + "px"
					});
					$note.attr("data-center-offset", center_offset+115);
					$note.attr("data-top-offset", offset.top);
				});

				// save note
				$note.find(".save").on("click", function() {
					var $save_btn = jQuery(this);
					$save_btn.addClass("saving");

					var note_id = $note.data("note-id");
					var center_offset = $note.data("center-offset");
					var top_offset = $note.data("top-offset");
					var note_text = $note.find(".rte").html();
					var priority = $note.data("priority-id");

					jQuery.post("/wp-admin/admin.php", { action: "frayd_edit_notes_ajax_save_note", note_id: note_id, center_offset: center_offset, top_offset: top_offset, note_text: note_text, priority: priority }, function( data ) {
						console.log(data);
						$save_btn.removeClass("saving");
					});
				});

				// change note priority
				$note.find(".color").on("click", function() {
					var priority = $note.data("priority-id");
					var new_priority = priority < 2 ? priority+1 : 0;

					$note.toggleClass(pub.priority[priority] + " " + pub.priority[new_priority]).data("priority-id", new_priority);
				});

				// drag 'n drop
				$note.draggable({
					handle: ".move",
					start: function() {},
					stop: function() {}
				});
			});
		}
	}
})();


if( window.attachEvent ) { // IE 8
	window.attachEvent("onload", us.frayd.editnotes.init);
} else { // modern browsers
	window.addEventListener("load", us.frayd.editnotes.init, false);
}