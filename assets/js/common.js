/* ========================================
   SIDEBAR
======================================== */

const sidebar =
document.getElementById('sidebar');

const openSidebar =
document.getElementById('openSidebar');

const closeSidebar =
document.getElementById('closeSidebar');

const collapseSidebar =
document.getElementById('sidebarCollapseToggle');

function applySidebarState(){

const collapsed =
localStorage.getItem('mindmerge-sidebar-collapsed') === 'yes';

if(collapsed){
document.body.classList.add('sidebar-collapsed');
}
else{
document.body.classList.remove('sidebar-collapsed');
}

}

applySidebarState();

/* OPEN SIDEBAR */

if(openSidebar){

openSidebar.addEventListener('click',()=>{

sidebar.classList.add('active');

});

}

/* CLOSE SIDEBAR */

if(closeSidebar){

closeSidebar.addEventListener('click',()=>{

sidebar.classList.remove('active');

});

}

if(collapseSidebar){

collapseSidebar.addEventListener('click',()=>{

const collapsed =
document.body.classList.toggle('sidebar-collapsed');

localStorage.setItem(
'mindmerge-sidebar-collapsed',
collapsed ? 'yes' : 'no'
);

});

}


/* ========================================
   THEME
======================================== */
document.addEventListener("DOMContentLoaded", () => {

const btn =
document.getElementById("themeToggle");

function applyTheme(){

const theme =
localStorage.getItem(
"mindmerge-theme"
) || "light";

if(theme === "dark"){

document.body.classList.add(
"dark-mode"
);

}
else{

document.body.classList.remove(
"dark-mode"
);

}

if(btn){

const icon =
btn.querySelector("i");

if(theme === "dark"){

icon.className =
"fa-solid fa-sun";

}
else{

icon.className =
"fa-solid fa-moon";

}

}

}

applyTheme();

if(btn){

btn.addEventListener("click", () => {

const currentTheme =
localStorage.getItem(
"mindmerge-theme"
) || "light";

const newTheme =
currentTheme === "dark"
? "light"
: "dark";

localStorage.setItem(
"mindmerge-theme",
newTheme
);

applyTheme();

});

}

});

/* =========================================
   TABLE SORTING
========================================= */

document.addEventListener('DOMContentLoaded', () => {

document.querySelectorAll('.custom-table').forEach(table => {

const headers = table.querySelectorAll('th');

headers.forEach((header,index)=>{

if(header.dataset.sort !== 'true'){
return;
}

header.style.cursor = 'pointer';

if(!header.querySelector('i')){

header.innerHTML +=
' <i class="fa-solid fa-sort"></i>';

}

let asc = true;

header.addEventListener('click',()=>{

const rows =
Array.from(
table.querySelectorAll('tbody tr')
);

rows.sort((a,b)=>{

const aCell =
a.children[index];

const bCell =
b.children[index];

const aValue =
aCell.dataset.value ||
aCell.innerText.trim().toLowerCase();

const bValue =
bCell.dataset.value ||
bCell.innerText.trim().toLowerCase();

const aNum = Number(aValue);
const bNum = Number(bValue);

if(
!isNaN(aNum) &&
!isNaN(bNum)
){

return asc
? aNum - bNum
: bNum - aNum;

}

return asc
? aValue.localeCompare(bValue)
: bValue.localeCompare(aValue);

});

asc = !asc;

rows.forEach(row=>{

table
.querySelector('tbody')
.appendChild(row);

});

});

});

});

});
