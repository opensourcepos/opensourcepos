/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

(function(){
	Tracy = window.Tracy || {};

	var BlueScreen = Tracy.BlueScreen = {},
		inited;

	BlueScreen.init = function(ajax) {
		var blueScreen = document.getElementById('tracy-bs');

		for (var i = 0, styles = []; i < document.styleSheets.length; i++) {
			var style = document.styleSheets[i];
			if (!style.ownerNode.classList.contains('tracy-debug')) {
				style.oldDisabled = style.disabled;
				style.disabled = true;
				styles.push(style);
			}
		}

		document.getElementById('tracy-bs-toggle').addEventListener('tracy-toggle', function() {
			var collapsed = this.classList.contains('tracy-collapsed');
			for (i = 0; i < styles.length; i++) {
				styles[i].disabled = collapsed ? styles[i].oldDisabled : true;
			}
		});

		if (!ajax) {
			document.body.appendChild(blueScreen);
			var id = location.href + document.getElementById('tracy-bs-error').textContent;
			Tracy.Toggle.persist(blueScreen, sessionStorage.getItem('tracy-toggles-bskey') === id);
			sessionStorage.setItem('tracy-toggles-bskey', id);
		}

		if (inited) {
			return;
		}
		inited = true;

		// enables toggling via ESC
		document.addEventListener('keyup', function(e) {
			if (e.keyCode === 27 && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) { // ESC
				Tracy.Toggle.toggle(document.getElementById('tracy-bs-toggle'));
			}
		});
	};

	BlueScreen.loadAjax = function(content, dumps) {
		var ajaxBs = document.getElementById('tracy-bs');
		if (ajaxBs) {
			ajaxBs.parentNode.removeChild(ajaxBs);
		}
		document.body.insertAdjacentHTML('beforeend', content);
		ajaxBs = document.getElementById('tracy-bs');
		Tracy.Dumper.init(dumps, ajaxBs);
		BlueScreen.init(true);
		window.scrollTo(0, 0);
	};

})();
