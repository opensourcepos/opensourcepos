/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

(function() {
	Tracy = window.Tracy || {};

	Tracy.Toggle = Tracy.Toggle || {};

	// enables <a class="tracy-toggle" href="#"> or <span data-tracy-ref="#"> toggling
	Tracy.Toggle.init = function() {
		document.body.addEventListener('click', function(e) {
			var el = Tracy.closest(e.target, '.tracy-toggle');
			if (el && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) {
				Tracy.Toggle.toggle(el);
			}
		});
		this.init = function() {};
	};


	// changes element visibility
	Tracy.Toggle.toggle = function(el, show) {
		var collapsed = el.classList.contains('tracy-collapsed'),
			ref = el.getAttribute('data-tracy-ref') || el.getAttribute('href', 2),
			dest = el;

		if (typeof show === 'undefined') {
			show = collapsed;
		} else if (!show === collapsed) {
			return;
		}

		if (!ref || ref === '#') {
			ref = '+';
		} else if (ref.substr(0, 1) === '#') {
			dest = document;
		}
		ref = ref.match(/(\^\s*([^+\s]*)\s*)?(\+\s*(\S*)\s*)?(.*)/);
		dest = ref[1] ? Tracy.closest(dest.parentNode, ref[2]) : dest;
		dest = ref[3] ? Tracy.closest(dest.nextElementSibling, ref[4], 'nextElementSibling') : dest;
		dest = ref[5] ? dest.querySelector(ref[5]) : dest;

		el.classList.toggle('tracy-collapsed', !show);
		dest.classList.toggle('tracy-collapsed', !show);

		if (typeof window.Event === 'function') {
			var toggleEvent = new Event('tracy-toggle', {bubbles: true});
		} else {
			toggleEvent = document.createEvent('Event');
			toggleEvent.initEvent('tracy-toggle', true, false);
		}
		el.dispatchEvent(toggleEvent);
	};


	// save & restore toggles
	Tracy.Toggle.persist = function(baseEl, restore) {
		var saved = [];
		baseEl.addEventListener('tracy-toggle', function(e) {
			if (saved.indexOf(e.target) < 0) {
				saved.push(e.target);
			}
		});

		var toggles = JSON.parse(sessionStorage.getItem('tracy-toggles-' + baseEl.id));
		if (toggles && restore !== false) {
			toggles.forEach(function(item) {
				var el = baseEl;
				for (var i in item.path) {
					if (!(el = el.children[item.path[i]])) {
						return;
					}
				}
				if (el.textContent === item.text) {
					Tracy.Toggle.toggle(el, item.show);
				}
			});
		}

		window.addEventListener('unload', function() {
			toggles = [].map.call(saved, function(el) {
				var item = {path: [], text: el.textContent, show: !el.classList.contains('tracy-collapsed')};
				do {
					item.path.unshift([].indexOf.call(el.parentNode.children, el));
					el = el.parentNode;
				} while (el && el !== baseEl);
				return item;
			});
			sessionStorage.setItem('tracy-toggles-' + baseEl.id, JSON.stringify(toggles));
		});
	};


	// finds closing maching element
	Tracy.closest = function(el, selector, func) {
		var matches = el.matches || el.matchesSelector || el.msMatchesSelector || el.mozMatchesSelector || el.webkitMatchesSelector || el.oMatchesSelector;
		while (el && selector && !(el.nodeType === 1 && matches.call(el, selector))) {
			el = el[func || 'parentNode'];
		}
		return el;
	};

})();
