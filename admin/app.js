
window.addEventListener("load", () => {

	app = new App;

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


class Header
{
	constructor () {
		
		//this.home = span('MedCine');
		this.home = img({src: '../bg/medcine-b.png'});
		this.menu = div({class: 'icon menu-handle linearicons-menu'});
		this.search = div({class: 'icon search-handle linearicons-magnifier'});
		this.logo = div({class: 'logo flex'}, this.home);
		
		this.tv = div({class: 'link'}, 'Series');
		this.cine = div({class: 'link'}, 'Movies');
		
		this.tv.onclick = t => window.location = './?q=search&c=tv';
		this.cine.onclick = t => window.location = './?q=search&c=movie';

		this.home.onclick = t  => window.location = './';

		this.ui = div({class: 'top-bar flex'}, 
			this.menu,
			div({class: 'inner flex'}, 
				this.logo,
				div({class: 'links'}, this.tv, this.cine)
			),
			div({class: 'language'}, checkbox({label: gConfig().language})),
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
				div({class: 'language'}, checkbox({label: gConfig().language})),
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



class SearchResults extends uiList
{

	constructor (items) {

		super([], false)

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

class ChannelInfo
{
	constructor (e) {

		this.e = e

		//this.sColors()
		
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
				//div({class: 'main', style: `background-color: ${this.color.primary}`}),
				div({class: 'banner'}, img({src: this.e.banner || ''})),
				//div({class: 'cloudy', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 0%, rgba(0 0 0 / .6) 100%)`}),
				//div({class: 'cloudy', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 200px, rgba(${this.rgb} / .6) 100%)`}),
				//div({class: 'clear', style: `background-image: linear-gradient(to right, rgba(${this.rgb} / 1) 60px, rgba(${this.rgb} / 0) 100%)`})
			)
		)

	}

	sButtons () {
		
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
					div({class: 'icon adjust-line linearicons-upload2'})
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
		
		let content = 'Not Available'

		let lang = gConfig().language

		let overviews = this.e.overviews

		if (overviews) for (const o of overviews) if (o.language == lang) content = o.content

		this.info.append(
			div({class: 'overview tabs'}, 
				div({class: 'title'},  
					div({class: 'text'}, 'Overview')
				),
				div({class: 'content'}, content)
			)
		)
	}

	sInfo () {

		this.info = 
		div({class: 'info'},
			div({class: 'name'},
				span({class: 'text'}, this.e.name),
				div(this.e.has_details ? 'has details' : 'no details')
				//span({class: 'year'}, `(${this.e.year})`)
			),
		)

		//this.sButtons()
		//this.sSummary()
		//this.sTagline()
		//this.sOverview()

		this.ui.append(
			//div({class: 'info-outer', style: `color: ${this.color.text}`},
			div({class: 'info-outer'},
				div({class: 'poster'}, img({src: this.e.poster || ''})),
				this.info
			)
		)				
	
	}
}
class ChannelSearchResults extends uiStaticList
{

	constructor (message_if_empty) {

		super(message_if_empty)

		this.api = new TMDB

	}

	gItem (year, name, buttons) {

		let buttonsEL = div({class: 'buttons'})

		for (let b of Object.values(buttons)) buttonsEL.append(b)

		return div({class: 'item'}, 
				div({class: 'flex'}, 
					div({class: 'year'}, year),
					div({class: 'name'}, name),
					buttonsEL
				)
			);

	}


	gButton (text, icon, tasks = []) {

		let progressEl = div({class: 'current'})

		let b =
		div({class: 'button'},
			div({class: 'button-wrapper'},
				div({class: `icon linearicons-${icon}`}),
				div({class: 'text'}, text)
			),
			div({class: 'progress'}, progressEl)
		)

		if (tasks.length) b.onclick = () => {

			this.startTasks(tasks, progressEl)

		}

		return b

	}

	async startTasks (tasks, progressEl) {

		const total = tasks.length

		let counter = 1

		for (let t of tasks) {

			let result = await t()

			if (result === true) {
				
				progressEl.style.width = `${counter * 100 / total}%`

				counter++
			
			}
		}

	}

	async gImage (path, destination, size) {

		let blob = await this.api.gImage(size, path)
		
		if (!blob) return console.log(`${size} image get failed`)

		let reader = new FileReader
					
		reader.onload = () => destination[size] = {size: size, path: path, blob: reader.result}

		reader.readAsDataURL(blob)

		return true
		
	}

	async uploadImage (e) {
	
		let res = await node('channel-search', {q: 'image-upload', d: e})

		let r = res.result

		if (r.errormsg) return console.log(r.errormsg)
		
		if (!r.upload) return console.log('image upload FAILED')
		
		return true

	}
}
class ChannelSearchResultsLocal extends ChannelSearchResults
{

	constructor (channel, msg) {

		super(msg)

		this.channel = channel

	}

	li (e) {

		let images = {}

		let buttons = {
			confirm: this.gButton('Set as default', 'thumbs-up', [
				() => {return this.sDetail(e)},
			]),
			images: this.gButton('Get Images', 'download2', [
				() => {return this.gImage(e.poster_path, images, 'w500')},//details.en.posterpath
				() => {return this.gImage(e.backdrop_path, images, 'w780')}//details.en.backdrop_path
			]),
			upload: this.gButton('Upload data', 'upload2', [
				() => {return this.uploadImage(images.w500)},
				() => {return this.uploadImage(images.w780)},
			])
		}

		return this.gItem(e.release_date || e.last_air_date, e.name, buttons)

	}
	
	async sDetail (e) {
		
		let res = await node('channel-search', {q: 'set-default', path: e.path, channel: this.channel.id})
		
		let r = res.result

		if (r.errormsg) return console.log(r.errormsg)
		
		if (!r.default) return console.log('setting default FAILED')
		
		return true


	}
}

class ChannelSearchResultsRemote extends ChannelSearchResults
{

	constructor (contentType) {

		super('No results found')

		this.contentType = contentType

	}
	li (e) {

		let details = {}
		let images = {}

		let buttons = {
			details: this.gButton('Get Details', 'enter-down2', [
				() => {return this.gDetail(e, details, 'en')},
				() => {return this.gDetail(e, details, 'es')}
			]),
			images: this.gButton('Get Images', 'download2', [
				() => {return this.gImage(e.poster_path, images, 'w500')},//details.en.posterpath
				() => {return this.gImage(e.backdrop_path, images, 'w780')}//details.en.backdrop_path
			]),
			upload: this.gButton('Upload data', 'upload2', [
				() => {return this.uploadDetail(details.en, 'en', this.contentType)},
				() => {return this.uploadDetail(details.es, 'es', this.contentType)},
				() => {return this.uploadImage(images.w500)},
				() => {return this.uploadImage(images.w780)},
			])
		}
		
		return this.gItem(e.release_date || e.last_air_date, e.title || e.name, buttons)

	}

	
	async gDetail (e, destination, language) {
			
		let json = await this.api.gDetails(this.contentType, e.id, language);

		if (!json) return console.log(`${e.name || e.title}: returned no data`)

		destination[language] = json

		console.log(destination)

		return true

	}

	async uploadDetail (e, language, contentType) {
	
		let res = await node('channel-search', {q: 'submitdata', d: e, l: language, c: contentType})
		
		let r = res.result

		if (r.errormsg) return console.log(r.errormsg)
		
		if (!r.submitdata) return console.log('data submit FAILED')
		
		return true

	}

}

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
class ChannelSearch
{
	constructor (e) {

		this.buttons = {
			search: div({class: 'icon linearicons-arrow-right'}),
			close: div({class: 'icon linearicons-cross'})
		}

		this.input = input({type: 'text', placeholder: e})

		this.ui = div({class: 'results', tabindex: 1})

		//this.ui.onblur = t => this.close();
		this.input.onfocus = () => this.open()

		this.buttons.close.onclick = () => this.close()


	}

	sList () {

		this.ui.append(
			div({class: 'inner'}, this.list.ui)
		)

	}
	
	sTerm () {

		this.term = this.input.value;

		return this.term;

	}

	run () {

		if (!this.loading) {

			this.loading = true;
				
			clearTimeout(this.timeout);

			if (this.sTerm()) {
					
				this.timeout = setTimeout(to => {
					
					this.gResults();

				}, 500);

			} else this.gResults();
		}
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

	}

	toggle () {

		if (this.isOpen) this.close();  else {

			this.open();
		
			this.ui.focus();
			
		}

	}
	
}

class ChannelSearchLocal extends ChannelSearch
{
	constructor (e) {

		super('Search for json file')
		
		this.buttons.query = div({class: 'button'}, 'Query from API')

		this.buttons.query.onclick = () => {

			this.remote.sQuery()
			this.close()

		}
		
		this.buttons.search.onclick = () => {

			this.remote.sQuery()
			this.close()

		}


		this.list = new ChannelSearchResultsLocal(e,
			div({class: 'no-details'},
				div({class: 'no-details'}, 'No details found'),
				this.buttons.query	
			)
		)

		this.sList()

		this.input.oninput = () => this.run()

	}
	
	async gResults () {

		this.list.clear();
		this.list.addSpinner();

		let json = await node('channel-search', {q: 'json-local-details', t: this.term.slice(0, 50)})

		let r = json.result

		this.list.removeSpinner()
		this.list.reset()
		
		if (r.errormsg) return this.list.error(r.errormsg)
		
		if (!r.details) return this.list.error('Response incomplete')
			
		this.list.sItems(r.details)

		this.loading = false

	}

}	

class TMDB
{

	constructor () {
	
		this.url = 'https://api.themoviedb.org/3/'
		this.key = 'e9e7b217e0bdbe5e6c15e6212c413636'

		this.config = {
			images: {
				base_url: 'http://image.tmdb.org/t/p/'
			}
		}

		this.sizes = {
			poster: 'w500',
			banner: 'w780'
		}
	}

	async fetch (url) {
		
		try {
			
			let response = await fetch(url)
			
			let json = await response.json()
			
			return json;

		} catch (err) {
			
			this.handlerror(err)
		
		}
	}

	gQuery (e) {

		return `${this.url}search/${e.type}?api_key=${this.key}&query=${e.name}`

	}

	async query (query) {
	
		return await this.fetch(query)

	}

	async gDetails (type, id, language) {

		let url = `${this.url}${type}/${id}?api_key=${this.key}&`

		url += language == 'en' ? 'append_to_response=release_dates,content_ratings,credits' : `language=${language}`

		return await this.fetch(url)
			
	}

	async gImage (size, path) {

		try {
			
			const url = `${this.config.images.base_url}${size}${path}`

			let response = await fetch(url)
			
			return await response.blob()

		} catch (err) {
			
			this.handlerror(err)
		
		}
		
	}

	gPoster (path) {

		let blob = this.gImage(this.poster_size, path);
		
		return {
			blob: blob,
			size: this.poster_size,
			path: path
		}
		
	}

	gBackdrop (path) {

		return this.gImage(this.backdrop_size, path);

	}


	handlerror (err) {
		
		console.log(`Error occured: ${err}`)

	}

}
class ChannelSearchRemote extends ChannelSearch
{
	constructor (e) {

		super('Query for results from API')
	
		this.list = new ChannelSearchResultsRemote(e.type)

		this.list.app = this

		this.sList()

		this.e = e

		this.api = new TMDB

		this.buttons.search.onclick = () => {

			this.sTerm()
			this.gResults()

		}

		this.details = []
		this.images = []

	}

	sQuery () {

		this.input.value = this.api.gQuery(this.e)

		this.input.focus()

	}

	async gResults () {

		this.list.clear();
		this.list.addSpinner();

		let json = await this.api.query(this.term)

		this.list.removeSpinner()
		this.list.reset()

		if (!json) return this.list.error('No results were returned')

		this.results = json.results

		console.log(this.results)
		this.list.sItems(this.results)

		this.loading = false

	}
	
	async gDetail (result, language = '') {
			
		let json = await this.api.gDetails(this.e.type, result.id, language);

		if (!json) return console.log(`${result.name || result.title}: returned no data`)

		this.details.push(json)

		return true

	}

	async gDetails (e) {
		
		let en = await this.gDetail(e)
		let es = await this.gDetail(e, 'es')

		console.log(this.details)

		if (en && es) return true

	}

	async gImage (size, path) {

		let blob = await this.api.gImage(size, path)
		
		if (!blob) return console.log(`${size} image get failed`)

		let reader = new FileReader
					
		reader.onload = () => this.images.push({size: size, path: path, blob: reader.result})

		reader.readAsDataURL(blob)

		return true
		
	}
 	
	async gImages (e) {
		
		let poster = await this.gImage('w500', e.poster_path)
		let banner = await this.gImage('w780', e.backdrop_path)

		console.log(this.images)

		if (poster && banner) return true

	}

	async upload () {
	
		let details = await this.uploadDetails('w500', e.poster_path)
		let images = await this.gImage('w780', e.backdrop_path)

		console.log(this.images)

		if (poster && banner) return true
		


	}



}	
class Channel
{

	constructor (e) {
		
		this.e = e;

		this.ui = document.querySelector('.card.channel');

		this.sInfo();
		this.sContent();


	}

	sInfo () {

		this.info = new ChannelInfo(this.e)

		this.ui.append(this.info.ui)

	}
	sContent () {

		let search = {
			local: new ChannelSearchLocal(this.e),
			remote: new ChannelSearchRemote(this.e)
		}

		search.local.remote = search.remote

		let searchesEl = div({class: 'searches'})

		for (const s of Object.values(search)) {

			searchesEl.append(
				div({class: 'channel-search'},
					div({class: 'input'},
						s.input,
						s.buttons.search,
						s.buttons.close,
					),
					s.ui
				)
			)
		}

		this.ui.append(
			div({class: 'channel-content'},
				searchesEl,
				/*div({class: 'buttons'},
					div({class: 'button'}, 'Get Images'),
					div({class: 'button'}, 'Add details to database')
				)*/
			)
		)

	}

	
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




class Widget
{
	constructor (e) {

		this.content = div({class: 'content'});

		this.ui =
		div({class: `widget ${e.class}`},
			div({class: 'title'}, e.title),
			this.content
		);

		this.sContent();
		
	}

	sContent () {}

}

class ScannerUx
{
	constructor (e) {

		this.ui = e.ui;

		this.id = e.id;

	}

	report (msg) {

		//this.ui.text.innerHTML = msg;
		this.ui.text.append(div(msg));

	}

	log (id, d) {

		switch (id) {
			case 'USER-DENIED':
				this.report('Access Denied');
				break;
			case 'PROGRESS':
				this.ui.percent.innerHTML = e.percent;
				break;
		
			default:
				console.log(id, d);
				break;
		}

		
	}

	close () {

		if (this.es) return this.es.close;

	}

	start () {

		if (this.es) this.es.close();

		this.es = new EventSource(`scan.php?q=${this.id}`);
	
		this.es.onopen = t => this.report('Scanning...');

		this.es.onmessage = e => {
			
			let data = JSON.parse(e.data);

			if(e.lastEventId == 'CLOSE') {
				
				this.report('Completed');

				this.es.close();
			
			}
			this.log(e.lastEventId, data);

		}
		
		this.es.onerror = ev => {
		
			this.report(`Error: Scan canceled`);

			this.es.close();
		
		}

		return this.es;

	}
}
class Scan
{
	constructor (e) {

		this.buttons = {
			close: div({class: 'button'}, 'close'),
			stop: div({class: 'button'}, 'stop')
		}

		this.progress = {
			percent: div({class: 'percent'}, '0%'),
			text: div({class: 'text'}, 'Waiting')
		}

		this.ui = 
		div({class: 'editor scan'}, 
			div({class: 'status'}, 
				div({class: 'loading-spinner'}),
				this.progress.percent,
				this.progress.text
			), 
			div({class: 'buttons'}, 
				this.buttons.close,
				this.buttons.stop
			)
		);

		this.scanner = new ScannerUx({
			id: e.id,
			ui: this.progress
		});

		this.buttons.close.onclick = t => this.close();
		this.buttons.stop.onclick = t => this.scanner.close();

	}

	close () {

		this.active = false;

		this.ui.remove();

	}

	start () {

		this.scanner.start();

		console.log('scan started');

	}
}
class NewItemEditor
{
	constructor (e) {

		this.inputs = {
			path: input({type: 'text', placeholder: e.input_placeholders.path}),
			level: input({type: 'number', placeholder: e.input_placeholders.level})
		};

		if (e.input_placeholders.id) this.inputs.id = input({type: 'text', placeholder: e.input_placeholders.id});

		this.buttons = {
			close: div({class: 'button'}, 'close'),
			confirm: div({class: 'button'}, 'confirm')
		}

		this.ui = 
		div({class: 'editor new-item'}, 
			div({class: 'inputs'}, 
				this.inputs.level, 
				this.inputs?.id, 
				this.inputs.path
			), 
			div({class: 'buttons'}, 
				this.buttons.close,
				this.buttons.confirm
			)
		);

		this.buttons.close.onclick = t => this.close();
		this.buttons.confirm.onclick = t => e.addNewCallback(this.inputs);

	}

	close () {

		this.active = false;

		this.ui.remove();

	}
}

class DirectoryList extends uiInfiniteList
{
	li (e) {

		let i =
		div({class: 'item directory'},
			div({class: 'name'}, e.path)	
		);

		return i;

	}

}
class ContentTypesList extends uiInfiniteList
{
	li (e) {

		let list = new DirectoryList('No directories');

		let editors = new Editors({
			newItem: {
				input_placeholders: {
					path: 'Directory path',
					level: 'Channel Level'
				},
				addNewCallback: inputs => this.addNewContent({list: list, e: e, inputs: inputs})
			},
			scan: {
				id: e.id
			}
		});

		let scan_button = div({class: 'button'}, 'scan');
		let new_button = div({class: 'button'}, 'new');
		let del_button = div({class: 'button'}, 'delete');


		let i =
		div({class: 'row'},
			div({class: 'info'},
				div({class: 'name'}, e.name),
				div({class: 'buttons'},
					div({class: 'button'}, 'restrict'),
					scan_button,
					new_button,
					del_button
				)
			),
			editors.ui,
			div({class: 'directory-list'}, list.ui)
		);

		scan_button.onclick = t => editors.toggleScan();
		new_button.onclick = t => editors.toggleNew();
		list.sItems(e.directories);

		return i;

	}
		
	addNewContent (e) {

		//add overlay spinner 
		//this.media.addSpinner();

		if (!this.loading) {

			let list = e.list;

			if (e.inputs.path.value) {
					
				this.loading = true

				f('ContentTypeManager', {q: 'new-directory', path: e.inputs.path.value, cl: e.inputs.level.value, type: e.e.id}, r => {

					if (r.errormsg) alert(r.errormsg); else if (r.content) {

						if (r.content.new_directory) {

							if (!list.items.length) list.clear();
							
							if (r.content.new_directory.media) {
								
								list.sItems(r.content.new_directory.media);
								
							} else list.error('Response not complete');
							
						} else alert('Response not complete');

					} else list.error('Invalid Response');

				}).finally(z => {
				
					this.loading = false;

					//remove overlay spinner
				
				});

			} else e.inputs.path.focus();
		}
	}

}

class Editors
{
	constructor (e) {

		this.ui = div({class: 'editors'});

		this.newItem = new NewItemEditor(e.newItem);
		this.scan = new Scan(e.scan);

	}

	clear () {

		this.ui.innerHTML = '';

	}

	add (e) {

		this.clear();

		e.active = true;

		this.ui.append(e.ui);

		return this.active;

	}

	remove (e) {

		e.active = false;

		e.ui.remove();

		return this.active;
		//this.clear();

	}

	toggle (e) {

		return e.active ? this.remove(e) : this.add(e);

	}

	toggleNew () {

		this.toggle(this.newItem);

	}
	toggleScan () {

		this.toggle(this.scan);
		
		if (this.scan.active) this.scan.start();

	}
}
class ContentTypeManager extends Widget
{
	constructor () {

		super({
			title: 'Content Type Manager', class: 'content-type-manager'
		});

	}

	sContent () {
		
		this.media = new ContentTypesList('No Content');

		this.editors = new Editors({
			newItem: {
				input_placeholders: {
					id: 'Content id',
					path: 'Content name',
					level: 'Media Level'
				},
				addNewCallback: inputs => this.addNewContent({inputs: inputs})
			},
			scan: {
				id: 'all'
			}
		})

		this.new_button = div({class: 'button'}, 'new');
		this.scan_button = div({class: 'button'}, 'scan');

		this.new_button.onclick = t => this.editors.toggleNew();
		this.scan_button.onclick = t => this.editors.toggleScan();

		this.content.append(
			div({class: 'content-list'},
				div({class: 'row head'},
					div({class: 'info'},
						div({class: 'name'}, 'Content Types'),
						div({class: 'buttons'},
							div({class: 'button'}, 'restrict'),
							this.scan_button,
							this.new_button
						)
					),
					this.editors.ui
				),
				div({class: 'media-list-wrapper'}, this.media.ui)
			)
		);


		this.gMedia();

	}
	
	addNewContent (e) {

		//add overlay spinner 
		//this.media.addSpinner();

		if (!this.loading) {

			this.val = e.inputs.path.value;

			if (this.val) {
					
				this.loading = true

				f('ContentTypeManager', {q: 'new-content', name: this.val, ml: e.inputs.level.value, id: e.inputs.id.value}, r => {

					if (r.errormsg) alert(r.errormsg); else if (r.content) {

						if (r.content.new_content) {

							if (!this.media.items.length) this.media.clear();
							
							if (r.content.new_content.media) {
								
								this.media.sItems(r.content.new_content.media);
								
							} else this.media.error('Response not complete');
							
						} else alert('Response not complete');

					} else this.media.error('Invalid Response');

				}).finally(z => {
				
					this.loading = false;

					//remove overlay spinner
				
				});

			} else e.inputs.path.focus();
		}
	}
	gMedia () {

		this.media.addSpinner();

		f('ContentTypeManager', {q: 'media'}, r => {

			this.media.removeSpinner();

			if (r.errormsg) this.media.error(r.errormsg); else if (r.content) {

				if (r.content.media) this.media.sItems(r.content.media); else this.media.error('Response not complete');

			} else this.media.error('Invalid Response');

		});

	}

}

class ReportSummary 
{
	constructor (channels, images, localDetails) {
		
		this.channels = channels
		this._images = images
		this._local = localDetails
		
		this.local = {}
		this.details = {}
		this.images = []

		this.api = new TMDB

		this.rows = ['channels', 'images']
		this.columns = ['complete', 'failed']


		this.status = {}

		for (const column of this.columns) {
			
			this.status[column] = {}
			
			for (const row of this.rows) {

				this.status[column][row] = {
					ui: div({class: `column status num ${column}`}, '0'),
					counter: 0		
				} 
			
			}

		}

		this.console = div({class: 'console'})

		this.ui = 
		div({class: 'report-summary'},
			div({class: 'row main'},
				div({class: 'column name'}, 'Content'),
				div({class: 'column status total'}, 'Total'),
				div({class: 'column status complete'}, 'Complete'), 
				div({class: 'column status failed'}, 'Failed') 
			),
			div({class: 'row'},
				div({class: 'column name'}, 'Channels'),
				div({class: 'column status num total'}, String(this.channels.length)),
				this.status.complete.channels.ui,
				this.status.failed.channels.ui
			),
			div({class: 'row'},
				div({class: 'column name'}, 'Images'),
				div({class: 'column status num total'}, String(this._images.length)),
				this.status.complete.images.ui,
				this.status.failed.images.ui 
			),
			this.console
		)

	}

	fail (row) {

		let o = this.status.failed[row]

		o.counter++

		o.ui.innerHTML = o.counter

	}
	success (type) {

		let o = this.status.complete[type]

		o.counter++

		o.ui.innerHTML = o.counter

	}
	reset (row) {
		
		for (const column of this.columns) this.status[column][row].counter = 0;		

	}

	msg (msg) {

		this.console.append(div(msg))

	}

	async gResultDetails (channel, result) {

		let en = await this.api.gDetails(channel.type, result.id, 'en')
		
		if (!en) return this.fail('channels')

		let es = await this.api.gDetails(channel.type, result.id, 'es')

		this.details[en.id] = {
			channels: [channel.id],
			content_type: channel.type,
			details: {
				en: en,
				es: es
			}
		}
		
		this.success('channels')

	}
	async gResult (c) {

		let query = this.api.gQuery(c)

		let res = await this.api.query(query)

		if (!res) return this.fail('channels')

		let results = res.results

		if (!results.length) return this.fail('channels')

		let result = results[0]

		if (this._local[result.id]) {

			if (!this.local[result.id]) this.local[result.id] = []

			this.local[result.id].push(c.id)

			return this.success('channels')

		}

		if (this.details[result.id]) {

			this.details[result.id].channels.push(c.id)

			return this.success('channels')

		}

		this.gResultDetails(c, result)

	}
	async gDetails () {

		this.reset('channels')

		for (const c of this.channels) await this.gResult(c)

	}

	async gImage (i) {

		let blob = await this.api.gImage(i.size, i.path)

		if (!blob) return this.fail('images')
		
		let reader = new FileReader
				
		reader.onload = () => {
		
			this.images.push({
				blob: reader.result,
				size: i.size,
				path: i.path
			})
				
		}
	
		reader.readAsDataURL(blob)

		this.success('images')

	}
	async gImages () {

		this.reset('images')

		for (const i of this._images) await this.gImage(i)

	}

	async uploadLocal () {

		let res = await node('home', {q: 'submitlocaldata', data: this.local})

		this.handlResult('local details upload', res)

	}

	async uploadDetails () {

		let res = await node('home', {q: 'submitdata', data: this.details})

		this.handlResult('details upload', res)

	}
	async uploadImages () {

		let res = await node('home', {q: 'image-upload', images: this.images})

		this.handlResult('images upload', res)

	}
	
	async upload () {

		this.msg('upload started')
		
		console.log(this.images, this.details, this.local)
		
		await this.uploadLocal()
		await this.uploadDetails()
		await this.uploadImages()

		this.msg('upload end')
		
	}

	handlResult (task, response) {
		
		if (!response) return this.msg(`${task}: no response`)
		if (!response.result) return this.msg(`${task}: no result`)

		let r = response.result

		if (r.errormsg) return this.msg(`${task}: ${r.errormsg}`)

		this.msg(`${task}: ${r.status}`)


	}

}
class ChannelBatchSearch extends Widget
{

	constructor (e) {
		
		super({
			title: 'Browser', class: 'batch-search'
		});

		this.summary = new ReportSummary(e.channels, e.images, e.local_details);

		this.buttons = {
			details: div({class: 'button'}, 'Get details'),
			images: div({class: 'button'}, 'Get images'),
			upload: div({class: 'button'}, 'Upload')
		}
		
		this.content.append(
			div({class: 'summary-table'}, 
				this.summary.ui
			),
			div({class: 'buttons'},
				this.buttons.details,
				this.buttons.images,
				this.buttons.upload
			)
		);

		this.buttons.details.onclick = () => this.summary.gDetails()
		this.buttons.images.onclick = () => this.summary.gImages()
		this.buttons.upload.onclick = () => this.summary.upload()


	}

	gButton (name) {

		return div({class: 'button'}, name)

	}

	sContent () {}

	handlerror (error) {

		console.log(`Error occured: ${error}`);

	}

	gConfig () {

		const url = `${this.api.url}configuration?api_key=${this.api.key}`
		
		fetch(url)
			.then(response => response.json())
			.then(json => this.config = json)
			.catch(error => this.handlerror(error))

	}
	
}



class HomeFeed
{

	constructor (e) {
		
		this.ui = document.querySelector('.card.home')
		
		this.uiArea = div({class: 'inner'})
		this.iconsbar = div({class: 'icons-bar'})

		this.ui.append(
			div({class: 'widgets'}, this.uiArea, this.iconsbar)
		)

		this.add({widget: new ContentTypeManager, icon: 'presentation'});
		this.add({widget: new ChannelBatchSearch(e.batch), icon: 'glasses'});

	}
	
	add (e) {

		let icon = div({class: `icon linearicons-${e.icon}`})

		icon.onclick = () => toggleClass(e.widget.ui, 'open')

		this.uiArea.append(e.widget.ui)
		this.iconsbar.append(icon)

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