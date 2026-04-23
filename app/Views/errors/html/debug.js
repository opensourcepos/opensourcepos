const tabLinks    = new Array();
const contentDivs = new Array();

function init()
{
    // Grab the tab links and content divs from the page
    const tabListItems = document.getElementById('tabs').childNodes;
    console.log(tabListItems);
    for (let i = 0; i < tabListItems.length; i ++)
    {
        if (tabListItems[i].nodeName == "LI")
        {
            const tabLink     = getFirstChildWithTagName(tabListItems[i], 'A');
            const id          = getHash(tabLink.getAttribute('href'));
            tabLinks[id]    = tabLink;
            contentDivs[id] = document.getElementById(id);
        }
    }

    // Assign onclick events to the tab links, and
    // highlight the first tab
    let i = 0;

    for (const id in tabLinks)
    {
        tabLinks[id].onclick = showTab;
        tabLinks[id].onfocus = function () {
            this.blur()
        };
        if (i == 0)
        {
            tabLinks[id].className = 'active';
        }
        i ++;
    }

    // Hide all content divs except the first
    let j = 0;

    for (const id in contentDivs)
    {
        if (j != 0)
        {
            console.log(contentDivs[id]);
            contentDivs[id].className = 'content hide';
        }
        j ++;
    }
}

function showTab()
{
    const selectedId = getHash(this.getAttribute('href'));

    // Highlight the selected tab, and dim all others.
    // Also show the selected content div, and hide all others.
    for (const id in contentDivs)
    {
        if (id == selectedId)
        {
            tabLinks[id].className    = 'active';
            contentDivs[id].className = 'content';
        }
        else
        {
            tabLinks[id].className    = '';
            contentDivs[id].className = 'content hide';
        }
    }

    // Stop the browser following the link
    return false;
}

function getFirstChildWithTagName(element, tagName)
{
    for (let i = 0; i < element.childNodes.length; i ++)
    {
        if (element.childNodes[i].nodeName == tagName)
        {
            return element.childNodes[i];
        }
    }
}

function getHash(url)
{
    const hashPos = url.lastIndexOf('#');
    return url.substring(hashPos + 1);
}

function toggle(elem)
{
    elem = document.getElementById(elem);
    let disp;

    if (elem.style && elem.style['display'])
    {
        // Only works with the "style" attr
        disp = elem.style['display'];
    }
    else if (elem.currentStyle)
    {
        // For MSIE, naturally
        disp = elem.currentStyle['display'];
    }
    else if (window.getComputedStyle)
    {
        // For most other browsers
        disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
    }

    // Toggle the state of the "display" style
    elem.style.display = disp == 'block' ? 'none' : 'block';

    return false;
}
