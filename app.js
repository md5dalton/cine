
let app = {};

function handleNodeError (err) {
	
	console.log(`Node fetch Error occured: ${err}`)
	
}
async function node (query, data = {}) {

	try {

		data.query = query;
		
		let response = await fetch(
			'./node.php',
			{
				method: 'post',
				body: JSON.stringify(data)
			}
		)
			
		return await response.json();
		
	} catch (error) {
		
		handleNodeError (error)

	}
	
}
function copyToClipboard (text) {

	let TA = textarea({style: 'top:0;left:0;position:fixed'});

	document.body.append(TA);

	TA.focus();

	TA.value = text;

	TA.select();

	try {
		
		let copy = document.execCommand('copy');

		let msg = copy ? 'Successful' : 'Failed';

		alert(msg);

	} catch (error) {
		
		alert(error);

	}

	TA.remove();

}

function gConfig () {
	
	return {
		
		language: localStorage.getItem('lang') || 'en'

	}


}

function Config () {

	return {
		
		toggle: (key) => {
			
			localStorage.getItem(key)
		
		},
		set: (key) => localStorage.getItem(key),
		set: (key, value) => localStorage.setItem(key, value)

	}

}

window.addEventListener("load", () => {

	app = new App;
	
	//listener('.ripple-effect', 'click', createRipple);
	
	listen('.tab-link', 'click', t => {

		let tabs;
		
		if (tabs = climbTree(t, '.tabs')) {

			let tabindex = t.attributes.tabindex;
			let name = t.attributes.name;

			if (name) {
					
				for (const i of tabs.querySelectorAll(`.tab-link.active[name=${name.value}]`)) i.classList.remove('active');
				for (const i of tabs.querySelectorAll(`.tab-content.active[name=${name.value}]`)) i.classList.remove('active');

			} else {
				
				console.log('tab-link has no name');
				console.log(t);

			}

			addClass(t, 'active');
			
			if (tabindex) addClass(tabs.querySelector(`.tab-content[name=${name.value}][tabindex=${tabindex.value}]`), 'active');

		}


	});

}, false);

class App
{
	constructor () {

		this.ui = document.body;

		this.config = {};

		this.head = new Header;
		this.menu = new Menu;

		this.suggestions = new Suggestions;

		this.ui.prepend(this.head.ui, this.menu.ui, this.suggestions.ui);

		this.head.menu.onclick = t => this.menu.toggle();
		this.head.search.onclick = t => this.suggestions.toggle();

		console.log(document.cookie);

	}
}

class List
{
	//version 5
	constructor () {

		this._items = [];
		this.pointer = 0;
		this.counter = 0;
		this.pause = 21;

		this.fetchLoading = false;

	}
	set items (value) {
		this._items = value;
		this.pointer = 0;
		if (this._items.length) this.append();
	}
	
	get items () {
		return this._items;
	}


	li (e)  {}

	fetch (callback = null)  {}

	append () {

		if (this.pointer >= this.items.length) {
			
			this.fetch();
		
			return;

		}

		this.insertPosition.insertAdjacentElement('beforebegin', this.li(this.items[this.pointer]));

		this.pointer++;
		this.counter++;

		if (this.counter < this.pause && this.pointer < this.items.length) this.append();
		if (this.counter == this.pause) this.counter = 0;

	}

}
class GUIList extends List
{

	constructor () {super();
		
		this.loadmore = div({class: 'load-more'});

		this.insertPosition = this.loadmore;
		
		this.ui = div({class: 'list'}, this.loadmore);

		if (!this.items.length) this.empty();

		//this.observe();

	}

	observe () {
		
		this.observing = true;

		if ('IntersectionObserver' in window) {
			
			// THIS IS NOT SUPPORTED ON IOS < 12.2

			this.obs = new IntersectionObserver((entries) => {

				if (entries[0].isIntersecting === true) {
					this.append();
				}

			}, {threshold: [1]});

			this.obs.observe(this.loadmore);
			
		} else alert('your browser is too old');
	}

	empty ()  {}

}
class UXList extends GUIList
{
	constructor (items = []) {super();

		if (items.length) this.items = items;

	}
}



class ItemsList
{
	//version 2021.07.16
	constructor () {

		this.pause = 21;
		this.reset();

	}

	set items (value) {
		this._items = value;
		this.pointer = 0;
		if (this._items.length) this.append();
	}
	
	get items () {
		return this._items;
	}

	reset () {

		this._items = [];
		this.pointer = 0;
		this.counter = 0;

	}


	li (e)  {}

	append () {

		let i = this.items[this.pointer];

		if (!i) return;

		this.ui.append(this.li(i));

		this.pointer++;
		this.counter++;

		if (this.counter < this.pause && this.pointer < this.items.length) this.append();
		if (this.counter == this.pause) this.counter = 0;

	}


}
class uiList extends ItemsList
{

	constructor (items = [], loader = true, emptyMessage = '') {super();
		
		this.spinner = spinner();

		this.ui = div({class: 'list'}, loader ? this.spinner : '');

		this.emptyMessage = emptyMessage;

		if (items.length) this.sItems(items);

	}

	addSpinner () {

		this.ui.append(this.spinner);

	}

	removeSpinner () {

		return this.spinner.remove();

	}

	
	clear () {

		this.ui.innerHTML = '';

	}

	error (e) {

		this.ui.append(div({class: 'error'}, e));

	}
	
	sItems (arr) {

		this.items = arr;

		if (!this.items.length && this.emptyMessage) this.ui.append(div({class: 'empty'}, this.emptyMessage));

	}
}
class uiStaticList extends uiList
{
	constructor (emptyMessage = '') {super([], false, emptyMessage);}
}
class uiInfiniteList extends uiList
{
	constructor (emptyMessage = '', items = []) {super(items, false, emptyMessage);}

	sItems (arr) {
		
		if (arr) {
			
			for (var i of arr) this.items.push(i);

			this.append();

			if (!this.items.length && this.emptyMessage) this.ui.append(div({class: 'empty'}, this.emptyMessage));

		}


	}

}

class uiDynamicList extends uiList
{
	constructor (emptyMessage = '') {super([], false, emptyMessage);}


}

class Language
{
	constructor (fullnames = false) {
		
		this.values = {en: 'English', es: 'Espanol'}

		this.value = (`; ${document.cookie}`).split('; language=').pop().split(';').shift()
		
		this.value = this.value ? this.value : 'en'

		this.checkbox = checkbox({label: fullnames ? this.values[this.value] : this.value})

		this.input = this.checkbox.gInput()
		this.label = this.checkbox.gLabel()

		this.ui = div({class: 'language'}, this.checkbox)

		this.input.onchange = () => {

			for (const key in this.values) if (key != this.value) {

				this.value = key

				break

			} 

			this.label.innerHTML = fullnames ? this.values[this.value] : this.value

			const param = new URLSearchParams(window.location.search)
				
			param.set('language', this.value)

			const location = window.location

			const url = `${location.protocol}//${location.host}${location.pathname}?${param.toString()}`

			window.location = url

		}


	}


}
class Header
{
	constructor () {
		
		//this.home = span('MedCine');
		this.home = img({src: 'bg/medcine-b.png'});
		this.menu = div({class: 'icon menu-handle linearicons-menu'});
		this.search = div({class: 'icon search-handle linearicons-magnifier'});
		this.logo = div({class: 'logo flex'}, this.home);
		
		this.tv = div({class: 'link'}, 'Series');
		this.cine = div({class: 'link'}, 'Movies');
		
		this.tv.onclick = t => window.location = './?q=search&c=tv';
		this.cine.onclick = t => window.location = './?q=search&c=movie';

		this.home.onclick = t  => window.location = './';

		this.lang = new Language

		this.ui = div({class: 'top-bar flex'}, 
			this.menu,
			div({class: 'inner flex'}, 
				this.logo,
				div({class: 'links'}, this.tv, this.cine)
			),
			this.lang.ui,
			this.search
		);


		/*
		let prev = 0;

		window.addEventListener('scroll', t => {
			
			const scrollingEl = t.target.scrollingElement

			const current = scrollingEl.scrollTop;

			//if (this.ui.offsetTop) if (prev > current) removeClass(this.ui, 'no-showing'); else addClass(this.ui, 'no-showing');

			prev = current;

			this.search.innerHTML = this.ui.offsetTop

		})
		*/

	}
}

class Footer
{
	constructor () {

		this.home = div(
			div({class: 'icon linearicons-home2'}),
			div({class: 'text'}, 'Home')
		);

		this.groups = div(
			div({class: 'icon linearicons-users2'}),
			div({class: 'text'}, 'Groups')
		);
		
		this.user = div(
			div({class: 'icon linearicons-user'}),
			div({class: 'text'}, 'Me')
		);

		this.media = div(
			div({class: 'icon linearicons-play-circle'}),
			div({class: 'text'}, 'Playing')
		);

		this.search = div(
			div({class: 'icon linearicons-magnifier'}),
			div({class: 'text'}, 'Search')
		);

		this.media.onclick = t  => app.switchMedia();

		this.home.onclick = t  => app.switchHome();
		
		this.user.onclick = t  => app.openUser();

		this.search.onclick = t  => app.openSearch();
		
		this.ui = 
			div({class: 'bottom-bar'},
				this.home,
				this.search,
				this.media
			);
	}
}

class Menu
{
	constructor () {

		this.tv = div({class: 'link'}, 'Series');
		this.cine = div({class: 'link'}, 'Movies');
		
		this.tv.onclick = t => window.location = './?q=search&c=tv';
		this.cine.onclick = t => window.location = './?q=search&c=movie';

		this.lang = new Language(true)

		this.ui =
		div({class: 'menu', tabindex: 1},
			div({class: 'links'},
				div({class: 'group media'},
					this.tv,
					this.cine
				),
				div({class: 'group account'},
					div({class: 'link'}, 'My account'),
					//div({class: 'link'}, 'Login'),
					//div({class: 'link'}, 'Sign up')
				),
				this.lang.ui,
			)
		);

		this.ui.onblur = t => this.close();

	}

	close () {

		removeClass(this.ui, 'open');

		setTimeout(() => {

			this.isOpen = false;
			
		}, 20);

	}

	open () {

		addClass(this.ui, 'open');

		this.isOpen = true;

	}

	toggle () {

		if (this.isOpen) this.close();  else {

			this.open();
		
			this.ui.focus();
			
		}

	}
	
}



class VideoplayerUI
{
	constructor (player) {

		this.ui = div({class: 'video-player'}, player);

		this.ui.append(this.gOverlay());

		
	}

	load (e, config = {}) {

		this.config = config;

		this.timecode.total.innerText = e.duration;

		this.repeatInput.checked = config.repeat;

	}

	gOverlay () {

		this.repeatInput = input({type: 'checkbox'});
		
		this.controls = {
			play: div({class: 'play linearicons-play-circle'}),
			input: input({type: 'range', value: 0}),
			current: div({class: 'background current'}),
			buffered: div({class: 'background buffered'}),
			repeat: div({class: 'repeat'},
				this.repeatInput,
				label({class: 'linearicons-repeat-one2'}),
			)
		}

		this.timecode = {
			current: span({class: 'current'}, '0:00'),
			total: span({class: 'total'}, '0:00')
		}

		this.skip = {
			backward: div({class: 'skip back linearicons-backward-circle'}),
			forward: div({class: 'skip forward linearicons-forward-circle'})
		}
		
		this.controls.repeat.onclick = t => {

			if (this.player.loop) {
				
				this.player.loop = false;
				this.config.repeat = false;
			
			} else {
				
				this.player.loop = true;

				this.config.repeat = true;

			}
		}

		this.fullscreen = div({class: 'fullscreen linearicons-frame-expand'});
		this.fullscreen.onclick = t => toggleFullscreen(this.ui);

		this.ui.addEventListener('fullscreenchange', t => {
			
			if (document.fullscreenElement) replaceClass(this.fullscreen, 'linearicons-frame-expand', 'linearicons-frame-contract'); else replaceClass(this.fullscreen, 'linearicons-frame-contract', 'linearicons-frame-expand');

		}, false);


		this.overlay = 
		div({class: 'overlay'}, 
			div({class: 'options'}, 
				this.controls.repeat,
				div({class: 'settings linearicons-cog'})
			),
			div({class: 'controls'}, 
				this.skip.backward,
				this.controls.play,
				this.skip.forward
			),
			div({class: 'progress'},
				div({class: 'time'},
					this.timecode.current,
					span('/'),
					this.timecode.total
				), 
				div({class: 'bar'},
					div({class: 'background total'}),
					this.controls.current,
					this.controls.buffered,
					this.controls.input
				),
				this.fullscreen
			), 
		);

		var timeout;

		this.overlay.onmousemove = t => {

			clearTimeout(timeout);
			
			addClass(this.overlay, 'opaque');

			timeout = setTimeout(() => {

				removeClass(this.overlay, 'opaque');
				
			}, 5000);

		}


		return this.overlay;

	}
}
class Videoplayer
{
	constructor () {

		this.playerUi = new VideoplayerUI(video({autoplay: true, playsinline: true}));

		this.ui = this.playerUi.ui;

	}

	load (e, config = {}) {

		this.playerUi.load(e, config);

	}
}
class Media
{

	constructor (e) {

		this.ui = document.querySelector('.card.media');

		this.videoplayer = new Videoplayer;

		this.ui.append(
			div({class: 'inner'},
				div({class: 'player'},
					this.videoplayer.ui,
					div({class: 'info'},
						div({class: 'name'}, e.name),
						div({class: 'sub-details'},
							//div(e.info)
							div('Published May 27, 2020')
						),
						//div({class: 'overview'}, e.overview),
						div({class: 'buttons'},  
							div({class: 'inner'},  
								div({class: 'button'}, 
									div({class: 'icon linearicons-heart'}), 
									div({class: 'text'}, 'Like') 
								), 
								div({class: 'button'}, 
									div({class: 'icon linearicons-download2'}), 
									div({class: 'text'}, 'Download') 
								),
								div({class: 'button'}, 
									div({class: 'icon linearicons-share'}), 
									div({class: 'text'}, 'Share') 
								)
							)
						),
						div({class: 'overview'}, e.overview)
					)
				),
				div({class: 'queue'}, 
					div({class: 'title'}, 
						div({class: 'main'}, e.channel.name), 
						div({class: 'sub'}, e.playlist?.name) 
					),
					div({class: 'media-list'}) 
				) 
			)
		);
	
	}

	open (e) {

		let previousMedia = this.id;
		let progress = this.gProgress();

		this.id = e.id;
		
		this.metadata.clear();
		this.queue.clear();

		addClass(this.video.loading, 'opaque');

		this.destruct();

		f('media', {id: e.id, playlist: e.playlist, previous: previousMedia, progress: progress}, r => {

			if (r.media) this.setUp(r.media); else alert(e.errormsg);//this.error(r.errormsg);

		});

	}

	error (err) {
		
		return div({class: 'start-error'}, err);

	}

	gProgress () {

		return this.uiPlayer ? this.uiPlayer.progress(this.player.currentTime) : 0;

	}
	setUp (e) {

		this.video.sDuration(e.duration);
		this.metadata.sData(e);
		this.queue.sList(e.queue);

		this.player = this.video.player;

		this.player.src = e.url;

		this.uiPlayer = new UiVideoplayer(this.player);


		this.controls = this.video.controls;
		this.timecode = this.video.timecode;

		this.skip = this.video.skip;
		
		this.owner = this.metadata.owner;

		this.playnext = this.queue.playnext;


		this.uiPlayer.controls = this.controls;
		this.uiPlayer.timecode = this.timecode;

		this.player.onpause = () => this.controls.play.classList.replace('linearicons-pause-circle', 'linearicons-play-circle');
		this.player.onplay = () => this.controls.play.classList.replace('linearicons-play-circle', 'linearicons-pause-circle');
		//this.player.onstalled = t => console.log('stalled');
		
			//this.player.onwaiting = t => addClass(this.video.loading, 'opaque');
		
		//this.player.onsuspend = t => console.log('suspend');
			//this.player.oncanplay = t => removeClass(this.video.loading, 'opaque');
		//this.player.oncanplaythrough = t => console.log('canplaythru');
		this.player.onended = t => {
			
			if (this.playnext.checked && e.queue.length) app.openMedia(e.queue[0], this.ui.matches('.showing'));

		}

		this.skip.backward.ondblclick = t => this.player.currentTime -= 10;
		this.skip.forward.ondblclick = t => this.player.currentTime += 10;

		this.owner.onclick = t => app.openChannel(e.owner);
		this.playnext.onclick = t => {
			
			app.config.playnext = this.playnext.checked;
		
		}

		//this.download.onclick = t => a({href: `/common/media/?f=${e.id}`, target: '_'}).click();
			
	}

	destruct () {

		if (this.player) {
				
			this.player.pause();

			//this.player.src = './vid.mp4';

			//this.player.load();

			//this.player.remove();

		}

		//this.ui.remove();

	 }

}

class SearchResults extends uiList
{

	constructor (items) {

		super([], false, 'No media found')

		this.sItems(items)

	}

	li (e) {

		let i =
		div({class: 'item result'},
			div({class: 'thumb ripple-effect', style: bgImg(e.poster)},
				div({class: 'context-menu-handle linearicons-ellipsis'})
			),
			div({class: 'details'},
				div({class: 'name'}, e.name),
				div({class: 'sub-details'},
					div(e.date)
				)
			)
		);
		if (e.tv) i.onclick = t => window.location = `./?q=channel&c=tv&id=${e.id}`;
		if (e.movie) i.onclick = t => window.location = `./?q=channel&c=movie&id=${e.id}`;

		//i.addEventListener('click', createRipple);
		return i;

	}
}

function range ($start, $end) {

	let arr = []

	for (let i = $start; i <= $end; i++) arr.push(i)

	return arr

}

class Pagination
{
	constructor (e) {

		this.total = parseInt(e.pages)
		this.current = parseInt(e.current)

		this.pages = []

		this.differenceLeft = this.current - 1
		
		if (this.differenceLeft > 1) {

			/*
			this.pages.push({
				page: 1, 
				ui: div({class: 'icon next linearicons-chevron-left'})
			})*/

			this.pages.push({
				page: 1, 
				ui: div({class: 'page jump back'}, 1)
			})
			
		}
		if (this.differenceLeft > 0) {

			this.pages.push({
				page: this.current - 1, 
				ui: div({class: 'page'}, this.current - 1)
			})

		}
		
		this.pages.push({
			page: this.current, 
			ui: div({class: 'page current'}, this.current)
		})

		this.difference = this.total - this.current

		
		if (this.difference > 0) {

			this.pages.push({
				page: this.current + 1, 
				ui: div({class: 'page'}, this.current + 1)
			})

		}

		
		if (this.difference > 1) {
			
			this.pages.push({
				page: this.total, 
				ui: div({class: 'page jump forward'}, this.total)
			})

			/*
			this.pages.push({
				page: this.current + 1, 
				ui: div({class: 'icon next linearicons-chevron-right'})
			})*/
			
		}

		this.pagesEl = div({class: 'pages'})

		for (const p of this.pages) {

			this.pagesEl.append(p.ui)

			p.ui.onclick = () => {
			
				const param = new URLSearchParams(window.location.search)
				
				param.set('pg', p.page)

				const location = window.location

				const url = `${location.protocol}//${location.host}${location.pathname}?${param.toString()}`

				//console.log(url, location)
				window.location = url

			}
		}


		this.ui = div({class: 'pagination'}, this.pagesEl)
		
		/*
		this.ui = 
		div({class: 'pagination'},
			div({class: 'pages'},
				div({class: 'icon prev linearicons-chevron-left'}),
				div({class: 'page'}, 1),
				div({class: 'sep'}, '...'),
				div({class: 'page'}, 8),
				div({class: 'page current'}, 9),
				div({class: 'page'}, 10),
				div({class: 'sep'}, '...'),
				div({class: 'page'}, 69),
				div({class: 'icon next linearicons-chevron-right'})
			)
		)*/

	}

	goTo (page) {

		this.current = page


		//this.sPages()

		return this

	}

	sPages () {

		let pages_to_remove = this.current - this.limit

		if (pages_to_remove > 0) {

			this._pages.splice(1, pages_to_remove)

		}

		let pages = this._pages.slice(0, this.limit - 1)


	}

}

class DualSlider
{
	constructor (e) {

		this.min = e.default[0]
		this.max = e.default[1]

		this.inputs = {
			min: input({type: 'number', min: this.min, max: this.max, placeholder: this.min}),
			max: input({type: 'number', min: this.min, max: this.max, placeholder: this.max})
		}

		if (e.values) {
			if (e.values[0]) this.inputs.min.value = e.values[0]
			if (e.values[1]) this.inputs.max.value = e.values[1]

		}

		this.ui = 
		div({class: 'dual-slider'},
			div({class: 'values'},
				div('Min'),
				div('Max')
			),
			div({class: 'range-sliders'},
				this.inputs.min,
				this.inputs.max
			)
		)

	}
	
	gSelected () {

		if (this.inputs.min.value || this.inputs.max.value) {

			let min = this.inputs.min.value || this.min
			let max = this.inputs.max.value || this.max

			return `${min},${max}`

		}

	}
}

class Options extends uiList
{
	constructor (query_index, type, items) {
		
		super([], false)

		this.query = query_index

		this.type = type

		this.name = rand_str();

		this.selected = []

		this.sItems(items)

	}
	
	li (e) {

		//if (e.checked) this.selected.push(e.id)

		let options = {label: e.name, name: this.name, checked: e.checked}

		let el = (this.type == 'rad') ? radio(options) : checkbox(options)
		
		el.addEventListener('click', () => {
			
			const param = new URLSearchParams(window.location.search)
				
			param.delete('pg')
			param.set(this.query, e.id)

			const location = window.location

			const url = `${location.protocol}//${location.host}${location.pathname}?${param.toString()}`

			//console.log(url)
			window.location = url

		})

		/*
		let i = el.i();

		i.onchange = () => {
			
			if (this.type == 'rad') {

				if (i.checked) {

					this.selected = [e.id]

				}

			} else {

				const index = this.selected.indexOf(e.id)

				if (index >= 0) this.selected.splice(index, 1)

				if (i.checked) this.selected.push(e.id)


			}

		
		}*/
		
		return el;

	}

	gSelected () {

		return this.selected.join(',')

	}

}
class FilterGroups extends uiList
{
	li (e) {

		let list

		switch (e.type) {
			case 'range':
				list = new DualSlider(e.values)
				break;
		
			default:
				list = new Options(e.q_id, e.type, e.values)
				break;
		}

		let i =
		div({class: 'group'},
			div({class: 'title'},
				div({class: 'text'}, e.title)
			),
			div({class: 'options'}, list.ui)
		);

		e.list = list


		return i;

	}

	gFilters () {
		
		let filters = []

		for (const g of this.items) {

			if (g.list) {
				
				let selected = g.list.gSelected()

				if (selected) filters.push(`${g.q_id}=${selected}`)
			
			}
		
		}

		return filters.join('&')

	}
}

class Filtering
{
	constructor (a) {

		this.groups = new FilterGroups(a, false)

		this.title =
		div({class: 'title'},
			div({class: 'text'}, 'Filters'),
			div({class: 'chevron linearicons-chevron-right'})
		)

		this.title.addEventListener('click', () => toggleClass(this.ui, 'open'))

		this.ui =
		div({class: 'filtering'},
			this.title,
			div({class: 'groups'}, this.groups.ui)
		)

	}

	gFilters () {

		return this.groups.gFilters()

	}
}

class Sorting
{
	constructor (a) {
		
		this.options = new Options('s', 'rad', a.options)
		this.order = new Options('o', 'rad', a.order)

		this.title =
		div({class: 'title'},
			div({class: 'text'}, 'Sorting'),
			div({class: 'chevron linearicons-chevron-right'})
		)

		this.title.addEventListener('click', () => toggleClass(this.ui, 'open'))

		this.ui =
		div({class: 'sorting'},
			this.title,
			div({class: 'groups'},
				div({class: 'item group'},
					div({class: 'title'}, 
						div({class: 'text'}, 'Sort'), 
						div({class: 'order'}, this.order.ui)
					),
					div({class: 'options'}, this.options.ui)
				)
			)
		)

	}

	gSort () {

		let selected = this.options.gSelected();

		if (selected) return `s=${selected}`

	}

	gOrder () {

		let selected = this.order.gSelected();

		if (selected) return `o=${selected}`

	}
}

class Search
{

	constructor (e) {

		this.ui = document.querySelector('.card.search')
		
		this.e = e

		this.setup();
		console.log(e)

	}

	setup () {

		this.wrapper = div({class: 'inner'})
		
		this.ui.append(
			div({class: 'title'}, this.e.title),
			this.wrapper
		)

		this.sControls()
		this.sResults()

	}

	sControls () {

		this.controls = div({class: 'controls'})

		this.sSorting()
		this.sFiltering()
		//this.sApply()

		this.wrapper.append(this.controls)

	}

	sSorting () {

		this.sorting = new Sorting(this.e.sort)

		this.controls.append(this.sorting.ui)

	}

	sFiltering () {

		this.filtering = new Filtering(this.e.filters)

		this.controls.append(this.filtering.ui)

	}

	sApply () {

		let button = div({class: 'search-btn ripple-effect apply-filters'}, 'Apply');

		button.addEventListener('click', () => {
			
			let location = [
				'./?q=search',
				this.sorting.gOrder(),
				this.sorting.gSort(),
				this.filtering.gFilters()
			]

			location = location.filter(i=>i)

			console.log(location.join('&'))
			window.location = location.join('&');
			
		})

		this.controls.append(button)

	}

	sResults () {

		this.results = new SearchResults(this.e.results)
		this.pagination = new Pagination(this.e.pagination)

		this.wrapper.append(
			div({class: 'media-list'}, 
				div({class: 'results'}, this.results.ui),
				this.pagination.ui
			)
		)

	}





	gControls () {
		
		this.controls.addSpinner();

		f('search', {q: 'controls'}, r => {

			this.controls.removeSpinner();

			if (r.errormsg) this.controls.error(r.errormsg); else if (r.search) {

				if (r.search.controls) this.controls.sItems(r.search.controls); else this.controls.error('Response not complete');

			} else this.controls.error('Invalid Response');

		})
		.finally(z => this.gResults());

	}

	gResults (page = 1) {

		if (!this.loading) {
			
			this.loading = true;

			this.results.clear();
			this.pages.clear();

			this.results.addSpinner();

			f('search', {q: 'results', sort: this.gSortOptions(), page: page, content: this.gContentFilters()}, r => {

				this.results.removeSpinner();

				if (r.errormsg) this.results.error(r.errormsg); else if (r.search) {

					if (r.search.results) {
						
						this.results.sItems(r.search.results); 
						this.pages.sItems(r.search.pages); 
					
					} else this.results.error('Response not complete');


				} else this.results.error('Invalid Response');

			}).finally(q => this.loading = false);

		}

	}

	gSortOptions () {
		
		let options = this.controls.items[0].groups[0].options;
		
		if (options) for (const i of options) if (i.checked) return i.value;

	}
	gContentFilters () {
		
		let options = this.controls.items[1].groups[0].options;
		
		if (options) for (const i of options) if (i.checked) return i.value;

	}


}


class RegisterBenefits extends uiStaticList
{
	li (e) {

		let i =
		div({class: 'item'},
			div({class: 'dot'}),
			div({class: 'text'}, e)
		);

		return i;
	}
}
class HomeList extends uiStaticList
{

	li (e) {
		
		let i =
		div({class: 'item'},
			div({class: 'thumb ripple-effect'},
				img({src: e.poster}),
				div({class: 'context-menu-handle linearicons-ellipsis'}),
				div({class: 'info flex'},
					div({class: 'inner flex'},
						div({class: 'play linearicons-play-circle'}),
						div({class: 'duration'}, e.duration)
					)
				)
			),
			div({class: 'details'},
				div({class: 'name'}, e.name),
				div({class: 'sub-details'},
					div(e.year)
				)
			)
		);

		//if (e.trailer) i.onclick = t => window.location = `./?q=media&id=${e.id}`;
		if (e.tv) i.onclick = t => window.location = `./?q=channel&c=tv&id=${e.id}`;
		if (e.movie) i.onclick = t => window.location = `./?q=channel&c=movie&id=${e.id}`;

		return i;

	}

}

class Sections extends uiInfiniteList
{

	li (e) {

		let i;

		switch (e.type) {
			case 'welcome':
				i = this.gWelcome(e);
				break;
		
				case 'register':
					i = this.gRegister(e);
					break;
				
				default:
					i = this.gSection(e);
					break;
		}

		return i;

	}

	gWelcome (e) {
		
		let search =
		div({class: 'search-dummy flex'}, 
			div({class: 'placeholder'},
				span('Search for a movie or tv show...')
			), 
			div({class: 'button'}, 'Search') 
		);

		search.onclick = () => app.suggestions.toggle()

		let i =
		div({class: 'welcome'},
			div({class: 'bg'},
				img({src: e.banner}),
				div({class: 'translucent'})
			),
			div({class: 'content'},
				div({class: 'title'}, e.title),
				div({class: 'message'}, e.message),
				search
			)
		);

		return i;
		
	}

	gRegister (e) {
		
		let list = new RegisterBenefits;

		list.sItems(e.media);

		let i =
		div({class: 'register'},
			div({class: 'bg'},
				img({src: e.banner}),
				div({class: 'translucent'})
			),
			div({class: 'content'},
				div({class: 'title'}, e.title),
				div({class: 'columns'}, 
					div({class: 'column message'}, 
						span('Get access to  maintain your own'),
						span({class: 'link'}, 'custom personal lists'),
						span(', track what'),
						span({class: 'link'}, 'you have seen'),
						span('and search and filter for'),
						span({class: 'link'}, 'what to watch next'),
						div({class: 'button'}, 'Join'),
					),
					div({class: 'column benefits'}, list.ui)  
				)
			)
		);

		return i;

	}

	gSection (e) {
		
		let list = new HomeList('No media was found');
		
		list.sItems(e.media);

		let bg = '', has_bg = '';

		if (e.banner) {

			bg = 
			div({class: 'bg', style: bgImg(e.banner)},
				div({class: 'translucent'})
			);

			has_bg = ' has-bg';

		}

		let i =
		div({class: `section ${e.type}${has_bg}`}, 
			bg,	
			div({class: 'title'}, e.title),
			div({class: 'media-list'}, list.ui)
		);

		return i;

	}

}

class HomeFeed
{

	constructor (e) {

		this.ui = document.querySelector('.card.home')

		this.sections = new Sections('No Media was found', e.s)

		this.ui.append(
			div({class: 'sections'}, this.sections.ui)
		)

		this.gSections()

	}
	
	gSections () {
		
		this.sections.addSpinner()
		
		f('home', {q: 'sections'}, r => {

			this.sections.removeSpinner();

			if (r.errormsg) this.sections.error(r.errormsg); else if (r.home) {

				if (r.home.sections) this.sections.sItems(r.home.sections); else this.sections.error('Response incomplete');

			} else this.sections.error('Invalid response');

		})


	}
}


class ChannelPlaylists extends uiStaticList
{
	li (e) {

		let i =
		div({class: 'item'},
			//div({class: 'thumb', style: bgImg(e.poster)}),
			div({class: 'thumb', style: bgImg('p/?f=w')}),
			div({class: 'details'},
				div({class: 'name'}, e.name),
				div({class: 'sub-details'},
					//div(e.info)
					div('2017, 10 Episodes')
				),
				//div({class: 'overview'}, e.overview),
				//div({class: 'overview'}, 'This is a long text representing an overview of a playlist if available.'),
				div({class: 'buttons'},  
					div({class: 'inner'},  
						div({class: 'button'}, 
							div({class: 'icon linearicons-play-circle'}), 
							div({class: 'text'}, 'Play') 
						), 
						div({class: 'button'}, 
							div({class: 'icon linearicons-download2'}), 
							div({class: 'text'}, 'Download') 
						),
						div({class: 'button'}, 
							div({class: 'icon linearicons-share'}), 
							div({class: 'text'}, 'Share') 
						)
					)
				)
			)
		);

		return i;

	}

	downloadTXT (e) {

		let text = this.gText(e.media);

		a({href: 'data:text/plain;chaset=utf-8,' + encodeURIComponent(text), download: e.name}).click();

	}
	
	copyToClipboard (media) {

		let text = this.gText(media);

		let TA = textarea({style: 'top:0;left:0;position:fixed'});

		this.ui.append(TA);

		TA.focus();

		TA.value = text;

		TA.select();

		try {
			
			let copy = document.execCommand('copy');

			let msg = copy ? 'Successful' : 'Failed';

			console.log(msg);

		} catch (error) {
			
			alert(error);

		}

		TA.remove();

	}

	gText (media) {

		let t = "";

		for (const i of media) {
			
			//t = t + "http://cdn.medcine.net/?f=" + i.id + "\n";
			t = t + "http://192.168.0.104/dev/cine/cdn/?f=" + i.id + "\n";

		}

		console.log(t);

		return t;

	}

}

class ChannelSummary extends uiStaticList
{
	li (e) {

		let i = div(e);

		return i;

	}

}
function divs (arr) {
	
	let el = div()

	if (arr) for (const i of arr) el.append(div(i))

	return el

}

function circleProgress (to) {
	
	let cnv = canvas({width: 500, height: 500, style: 'background: #f7f7f7'})

	let 
		context = cnv.getContext('2d'),
		x = cnv.width / 2,
		y = cnv.height / 2

		rad = Math.PI * 2 / 100

		speed = 0.1

		accent_circle = n => {

			context.save()
			context.beginPath()
			context.strokeStyle ='#49f' 
			context.lineWidth = 12
			context.arc(x, y, 100, -Math.PI / 2, Math.PI / 2 + n * rad, false)
			context.stroke()
			context.restore()

		}

		bg_circle = () => {

			context.save()
			context.beginPath()
			context.strokeStyle ='#a5def1'
			context.lineWidth = 12
			context.arc(x, y, 100, 0, Math.PI * 2, false)
			context.stroke()
			context.closePath()
			context.restore()

		}

		text = n => {

			context.save()
			context.fillStyle = '#f47c7c'
			context.font ='40px Arial'
			context.textAlign ='center'
			context.textBaseline ='middle'
			context.fillText(n.toFixed(0) + '%', x, y)
			context.restore()

		}

		(drawFrame = () => {

			//window.requestAnimationFrame(drawFrame, cnv)
			context.clearRect(0,0,cnv.width,cnv.height)

			bg_circle()
			text(speed)
			accent_circle(speed)

			if (speed > 100) speed = 0

			speed+=0.1

		})()

		drawFrame()

		//window.requestAnimationFrame(drawFrame, cnv)

	return cnv;

}

class ChannelInfo
{
	constructor (e) {

		this.e = e

		this.sColors()
		
		this.ui = div({class: 'info-wrapper'})

		this.sBackground()
		this.sInfo()


	}

	sColors () {
		
		this.color = {}
		
		let r, g, b

		const theme = this.e.theme

		r=theme.r
		g=theme.g
		b=theme.b

		//const contrast_rgb = `${255 - r} ${255 - g} ${255 - b}`
		//const contrast = `rgba(${contrast_rgb} / 1)`
		
		this.rgb = `${r} ${g} ${b}`

		const brightness = Math.round((parseInt(r * 299) + parseInt(g * 587) + parseInt(b * 114)) / 1000)

		this.color.text = brightness > 125 ? 'rgba(0 0 0 / .6)' : 'white'
		this.color.primary = `rgba(${this.rgb} / 1)`


	}

	sBackground () {

		this.ui.append(
			div({class: 'background'},
				div({class: 'main', style: `background-color: ${this.color.primary}`}),
				div({class: 'banner'}, img({src: this.e.banner})),
				div({class: 'cloudy', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 0%, rgba(0 0 0 / .6) 100%)`}),
				//div({class: 'cloudy', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 200px, rgba(${this.rgb} / .6) 100%)`}),
				div({class: 'clear', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 60px, rgba(${this.rgb} / 0) 100%)`})
			)
		)

	}

	sButtons () {
		
		let share = div({class: 'icon adjust-line linearicons-upload2'})

		share.onclick = async () => {

			let res = await node('channel', {q: 'info', id: this.e.id})

			let r = res.result

			if (r.errormsg) return alert(r.errormsg)

			if (!r.info) return alert('no info')

			copyToClipboard(r.info?.text)

			a({download: `${this.e.name}.jpg`, href: this.e.poster}).click()

		}

		this.info.append(
			div({class: 'buttons'},
				div({class: 'user-score'},
					div({class: 'inner'},
						div({class: 'icon linearicons-star'}),
						div({class: 'text'}, this.e.score)
					),
					div({class: 'label'}, 'Vote Avg')
				),
				div({class: 'icon-buttons'},
					div({class: 'icon linearicons-heart'}),
					div({class: 'icon linearicons-bookmark2'}),
					div({class: 'icon adjust-line linearicons-download2'}),
					share
				),
				div({class: 'text-buttons'},
					div({class: 'button'},
						div({class: 'icon linearicons-play-circle'}),
						div({class: 'text'}, 'Watch Trailer')
					)
				)
			)
		)
	}

	sSummary () {
		
		this.info.append(
			div({class: 'summary'},
				div({class: 'inner'},
					div({class: 'runtime'}, `${this.e.runtime} minutes`)
				),
				div({class: 'genre'}, divs(this.e.genres))
			)
		)
	}

	sTagline () {
		
		if (this.e.tagline) {
		
			this.info.append(
				div({class: 'tagline'}, this.e.tagline)
			)
		
		}
	}
	sOverview () {

		this.info.append(
			div({class: 'overview tabs'}, 
				div({class: 'title'},  
					div({class: 'text'}, 'Overview')
				),
				div({class: 'content'}, this.e.overview || 'Not Available')
			)
		)
	}

	sInfo () {

		this.info = 
		div({class: 'info'},
			div({class: 'name'},
				span({class: 'text'}, this.e.name),
				span({class: 'year'}, `(${this.e.year})`)
			),
		)

		this.sButtons()
		this.sSummary()
		this.sTagline()
		this.sOverview()

		this.ui.append(
			//div({class: 'info-outer', style: `color: ${this.color.text}`},
			div({class: 'info-outer'},
				div({class: 'poster'}, img({src: this.e.poster})),
				this.info
			)
		)				
	
	}
}
class Channel
{

	constructor (e) {

		console.log(e)
		this.e = e;

		this.ui = document.querySelector('.card.channel');

		this.sInfo();
	//	this.sContent();


	}

	sInfo () {

		this.info = new ChannelInfo(this.e)

		this.ui.append(this.info.ui)

	}
	sContent () {}

	
	copyToClipboard (text) {

		let TA = textarea({style: 'top:0;left:0;position:fixed'});

		this.ui.append(TA);

		TA.focus();

		TA.value = text;

		TA.select();

		try {
			
			let copy = document.execCommand('copy');

			let msg = copy ? 'Successful' : 'Failed';

			alert(msg);

		} catch (error) {
			
			alert(error);

		}

		TA.remove();

	}

	gText (e) {

		let type = e.tv ? 'series' : 'movie'

		let languages = {
			es: 'Español',
			en: 'inglés'
		}

		let language = languages[e.original_language]

		let t = `\t#${type}\n\n`
			
		t += `*${e.name} (${e.year})*\n\n`

		t += `*Género:* ${e.genres.join(', ')}\n`
		t += `*Idioma:* ${language}\n\n`
		
		t += `*Sinopsis:*\n`
		t += `${e.overviews[0].content}\n\n`

		t += `${e.overviews[1].content}`

		
		console.log(t);
		return t;

	}

}

class Movie extends Channel
{
}
class Series extends Channel
{
	sContent () {

		this.list = new ChannelPlaylists('No playlists were found');

		this.playlistsButton = div({class: 'playlist-button'}, 'View All Seasons');

		this.ui.append(
			div({class: 'playlists'},
				this.playlistsButton,
				div({class: 'playlists-list'}, this.list.ui)
			)
		);

		this.playlistsButton.onclick = t => this.gPlaylists();

	}

	gPlaylists () {

		if (!this.loading) {
				
			this.loading = true;

			this.list.addSpinner();

			f('channel', {q: 'playlists', id: this.e.id}, r => {
				
				this.list.removeSpinner();

				if (r.errormsg) this.list.error(r.errormsg); else if (r.channel) {
					
					if (r.channel.playlists) this.list.sItems(r.channel.playlists); else this.list.error('Incomplete response');

				} else this.list.error('Invalid response');
				
			}).finally(z => {
				
				this.loading = false;

			});

		}
		
	}

}

class SuggestionsList extends uiStaticList
{

	li (e) {

		let licon = 'magnifier';

		if (e.movie) licon = 'film';
		if (e.tv) licon = 'screen';

		let f = 
		div({class: 'item'}, 
			div({class: `item-icon linearicons-${licon}`}),
			div({class: 'name'}, e.name)
		);

		
		if (e.movie) f.onclick = t => window.location = `./?q=channel&c=movie&id=${e.id}`;
		if (e.tv) f.onclick = t => window.location = `./?q=channel&c=tv&id=${e.id}`;

		return f;

	}

}
class Suggestions
{

	constructor () {

		this.term = ''

		this.list = new SuggestionsList('No suggestions were found related to your search');
		
		this.input = input({type: 'text', placeholder: 'Search for a movie or tv show'});

		this.closeButton = div({class: 'item-icon linearicons-cross'});

		this.currentSearchTitle = div({class: 'current'});
		
		this.defaultResultsTitle = 
		div({class: 'default'}, 
			div({class: 'item'}, 
				div({class: 'item-icon linearicons-magnifier'}),
				div({class: 'name'}, 'Random')
			) 
		);


		this.ui = 
		div({class: 'search-suggestions', tabindex: 1},  
			div({class: 'inner'},
				div({class: 'search-bar'}, 
					div({class: 'item'}, 
						div({class: 'item-icon linearicons-magnifier'}),
						div({class: 'name'}, this.input),
						this.closeButton
					)
				),	
				this.currentSearchTitle, 
				div({class: 'suggestions-list'}, this.list.ui)
			)
		);

		this.sListeners();
		
	}

	gTerm () {

		this.term = this.input.value;

		return this.term;

	}

	run () {

		if (!this.loading) {

			this.loading = true;
				
			clearTimeout(this.timeout);

			if (this.gTerm()) {
					
				this.timeout = setTimeout(to => {
					
					this.gSuggestions();

				}, 500);

			} else this.gSuggestions();
		}
	}

	gSuggestions () {

		this.list.clear();
		this.list.addSpinner();

		if (!this.term) this.currentSearchTitle.append(this.defaultResultsTitle); else this.defaultResultsTitle.remove();

		f('suggestions', {q: 'results', t: this.term.slice(0, 50)}, r => {

			this.list.removeSpinner();
			this.list.reset();

			if (r.errormsg) this.list.error(r.errormsg); else if (r.suggestions) {

				if (r.suggestions.results) this.list.sItems(r.suggestions.results); else this.list.error('Response incomplete');

			} else this.list.error('Invalid response');

		}).finally(z => this.loading = false);

	}

	
	close () {

		removeClass(this.ui, 'open');

		setTimeout(() => {

			this.isOpen = false;
			
		}, 0);

	}

	open () {

		addClass(this.ui, 'open');

		this.isOpen = true;

		this.input.focus()

		this.gSuggestions()

	}

	toggle () {

		if (this.isOpen) this.close();  else {
			
			this.ui.focus();
			
			this.open();

		}

	}
	

	sListeners () {
		
		this.closeButton.onclick = t => this.close();

		this.input.oninput = t => this.run();

		this.input.onblur = t => {
			
			if (!t.relatedTarget) this.close();

		}
	}

}


