// Deep linking - load tab on refresh
let url = location.href.replace(/\/$/, "");
if (location.hash) {
    const hash = url.split("#");
    const currentTab = document.querySelector('#configs-list-tab button[href="#' + hash[1] + '"]' || '#configs-dropdown a[href="#' + hash[1] + '"]');
    const curTab = new bootstrap.Tab(currentTab);
    curTab.show();
    url = location.href.replace(/\/#/, "#");
    history.replaceState(null, null, url);
}

// Change url based on selected tab
const selectableTabList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]' || 'a[data-bs-toggle="tab"]'));
selectableTabList.forEach((selectableTab) => {
    const selTab = new bootstrap.Tab(selectableTab);
    selectableTab.addEventListener("click", function () {
        var newUrl;
        const hash = selectableTab.getAttribute("href");
        newUrl = url.split("#")[0] + hash;
        history.replaceState(null, null, newUrl);
    });
});
