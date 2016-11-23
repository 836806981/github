function showMenu(li) {
	var subMenu = li.getElementsByTagName("ul")[0];
	subMenu.style.display = "block";
}

function hideMenu(li) {
	var subMenu = li.getElementsByTagName("ul")[0];
	subMenu.style.display = "none";
}
