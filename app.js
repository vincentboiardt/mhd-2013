var App = {
	auth: {},
	bpm: 0,
	init: function() {
		console.log('go');
		$('form').on('submit', App.onSearch);
		$(document).on('click', '.toggle', function(){
			$(this).siblings('.details').slideToggle();
		});

		$('.tracks').droppable({
			hoverClass: 'tracks-hover',
			drop: App.onDrop
		}).sortable({ axis: 'y', stop: App.onSort });

		$('.play').on('click', App.onPlay);

		$(document).on('dblclick', '.main-pattern b', function(){
			$(this).data('player').stop()
			$(this).remove();
		});

		$(document).on('click', '.remove', function(){
			$(this).parent().remove();
		});

		$(document).on('change', 'input[name=filter]', function(){
			$(this).parents('.track').data('player').useFilter( $(this).is(':checked') );
		});

		$(document).on('change', 'input[name=filter_val]', function(){
			//console.log('change', $(this).val() );
			$(this).parents('.track').data('player').setFilter( $(this).val() );
		});

		$(document).on('change', 'input[name=gain]', function(){
			var gainVal = $(this).parents('.track').find('input[name=gain_val]').val();

			$(this).parents('.track').data('player').setGain( $(this).is(':checked') ? gainVal : 1 );
		});

		$(document).on('change', 'input[name=gain_val]', function(){
			//console.log('change', $(this).val() );
			$(this).parents('.track').data('player').setGain( $(this).val() / 100 );
		});
	},
	soundcloudLogin: function() {
		SC.connect(function() {
			SC.record({
				start: function() {
					$('.play').trigger('click');

					window.setTimeout(function() {
						SC.recordStop();
						SC.recordUpload({
							track: { title: 'This is my sound' }
						});
					}, 5000);
				}
			});
		});
	},
	onDrop: function(event, ui) {
		console.log('dropped', event, ui);
		var el = ui.draggable;

		if ( ! el.is('td') )
			return;

		var obj = { id: el.data('id'), analysis: el.data('url'), text: el.text(), bpm: el.data('bpm') };
		//var obj = { id: 'TRJTLOZ12F09775D41', analysis: 'http://echonest-analysis.s3.amazonaws.com/TR/TRJTLOZ12F09775D41/3/full.json?AWSAccessKeyId=AKIAJRDFEY23UEVW42BQ&Expires=1358611902&Signature=ZQONk8sdLZnVrsNVGNYOpN7qq1g%3D', file: 'remixjs/examples/audio/Karl_Blau-Gnos_Levohs.mp3', text: el.text(), bpm: el.data('bpm') };

		el.parents('tr').remove();
		
		$.getJSON( '?access=' + el.data('file-id'), function(response){
			obj.file = response.assets[0].url;
			App.addTrack(obj);
		});
		//App.addTrack(obj);

	},
	onSort: function(event, ui) {
		console.log('sorted', event, ui);

		if ( ui.item.index() == 1 ) {
			App.setBPM(ui.item.data('bpm'));
		}
	},
	onSearch: function(e) {
		e.preventDefault();

		var term = $('#search').val(),
			bpm = $('input[name=bpm]').val();
			//bpmSearch = bpm != '' ? ( '&min_tempo=' + ( bpm - 1 ) + '&max_tempo=' + ( Math.round(bpm) + 1 ) ) : '';

		//$.getJSON( 'http://developer.echonest.com/api/v4/song/search?bucket=id:emi_bluenote&bucket=audio_summary&bucket=tracks&api_key=' + App.key + '&format=jsonp&combined=' + encodeURI(term) + '&callback=?' + bpmSearch, App.onSearchResults );
		$.getJSON('?search=' + term, App.onSearchResults);
	},
	onSearchResults: function(response) {
		console.log('search', response);
		var list = $('#result tbody');

		list.empty();
		//$.each(response.response.songs, function(){
		$.each(response, function(){
			console.log(this);
			list.append('<tr data-spotify="' + this.spotify + '"><td>' + Math.round( this.audio_summary.tempo ) + '</td><td data-url="' + this.audio_summary.analysis_url + '" data-bpm="' + Math.round( this.audio_summary.tempo ) + '" data-file-id="' + this.file_asset_id + '" data-id="' + this.id + '">' + this.artist + ' - ' + this.title + '</td></tr>');
		});
		list.find('td').draggable({ revert : 'invalid' });

		/*$('tr[data-spotify]').popover({
			html: true,
			trigger: 'click',
			title: 'Preview',
			content: function(){
				return '<iframe src="https://embed.spotify.com/?uri=' + $(this).data('spotify') + '" width="250" height="80" frameborder="0" allowtransparency="true"></iframe>';
			}
		});*/
	},
	setBPM: function(bpm) {
		$('#bpm').val(bpm);
		$('input[name=bpm]').val(bpm);
	},
	addTrack: function(obj) {
		var details = $('<div class="details"></div>').append(
			'<div class="pattern all-beats"></div>'
		),
		el = $('<div class="track loading" data-bpm="' + obj.bpm + '"></div>').append(
			'<div class="pattern main-pattern"></div>',
			'<div class="toggle">v</div>',
			'<h2>' + obj.text + '</h2>',
			'<div class="checkbox">Gain: <input type="checkbox" name="gain"> <input type="range" name="gain_val" min="0" max="300" value="0"></div>',
			'<div class="checkbox">Filter: <input type="checkbox" name="filter"> <input type="range" name="filter_val" min="0" max="500" value="440"></div>',
			'<div class="checkbox">Offset: <input type="checkbox" name="offset"> <input type="range" name="offset_val" min="0" max="2000" value="1000"></div>',
			details,
			'<button class="remove btn btn-mini btn-danger">Delete</button>'
		);
		
		el.find('.pattern:not(.all-beats)')
		.sortable({ axis: 'x', cursor: 'move' })
		.droppable({
			drop: App.onBeatDrop,
			accept: 'b',
			scope: 'pattern',
			greedy: true
		});

		el.find('.pattern').disableSelection();

		$('.tracks').append(el);

		if ( el.index() == 1 ) {
			App.setBPM(obj.bpm);
		}
		var context = new webkitAudioContext(),
			remixer = createJRemixer(context, $, App.key),
			player = remixer.getPlayer();
		
		el.player = player;
		el.data('player', player);
		remixer.remixTrackById(obj.id, obj.file, function(t, percent) {
			console.log('percent', percent);

			el.track = t;

			if (el.track.status == 'ok') {
				el.removeClass('loading');
				App.createBeats(el);
			}
		});
	},
	createBeats: function(el) {
		console.log('createBeats');

		var allBeats = el.find('.all-beats');
		
		for (var i = 0; i < el.track.analysis.beats.length; i++) {
			var beat = el.track.analysis.beats[i],
				beatEl = $('<b></b>')
				.data('beat', beat)
				.data('start', beat.start)
				.data('player', el.player)
				//.on('mouseover', App.onBeatMouseOver )
				//.on('mouseout', App.onBeatMouseOut )
				.on('click', App.onBeatMouseOver )
				.draggable({
					cursor: 'move',
					revert: true,
					scope: 'pattern',
					snap: true
				});

			allBeats.append(beatEl);
			
			var h = ( beatEl.index() * 2 ),
				l1 = 10 * ( beatEl.index() % 2 == 0 ? 5 : 3 ),
				l2 = l1 - 10;
			beatEl.css('background', '-webkit-linear-gradient( hsl(' + h + ', 100%, ' + l1 + '%), hsl(' + h + ', 100%, ' + l2 + '%) )');
		}
	},
	onBeatMouseOver: function(e) {
		var beat = $(this).data('beat'),
			track = $(this).parents('.track'),
			offset = track.find('input[name=offset]').is(':checked') ? track.find('input[name=offset_val]').val() - 1000 : 0;

		if ( offset ) {
			beat.start = parseFloat( $(this).data('start') ) + ( offset / 1000 );
		}
			

		$(this).data('player').stop();
		$(this).data('player').play(0, beat);

	},
	onBeatMouseOut: function(e) {
		console.log('out stop!');
		$(this).data('player').stop();
	},
	onBeatDrop: function(e, ui) {
		var el = ui.draggable.clone(true).unbind();

		el.css('position', '').appendTo( $(this) );
	},
	onPlay: function() {
		var masterBeats = false;

		$('.track').each(function(i){
			var track = $(this),
				beats = [],
				beatElements = track.find('.main-pattern b'),
				offset = track.find('input[name=offset]').is(':checked') ? track.find('input[name=offset_val]').val() - 1000 : 0;

			console.log('offset', offset);

			$.each(beatElements, function(j){
				var beat = $(this).data('beat');

				if ( offset ) {
					console.log('offset ja', $(this).data('start'), offset / 1000 );
					beat.start = parseFloat( $(this).data('start') ) + ( offset / 1000 );
				}
				
				if ( typeof masterBeats[j] != 'undefined' && beat.duration > masterBeats[j].duration )
					beat.duration = masterBeats[j].duration;
				
				beats.push(beat);
			});

			if(i === 0) {
				masterBeats = beats;
			}

			track.data('player').play(0, beats);
		});
	}
};
$(document).ready(App.init);