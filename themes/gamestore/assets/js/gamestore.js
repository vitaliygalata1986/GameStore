// Dark Mode Style
let styleMode = localStorage.getItem("styleMode");
const styleToggle = document.querySelector(".header-mode-switcher");

const enableDarkStyle = () => {
    document.body.classList.add('dark-mode-gamestore');
    localStorage.setItem("styleMode", 'dark');
}

const disableDarkStyle = () => {
    document.body.classList.remove('dark-mode-gamestore');
    localStorage.removeItem("styleMode");
}

if (styleToggle) {
    styleToggle.addEventListener('click', ()=>{
        styleMode = localStorage.getItem('styleMode');
        if (styleMode !== 'dark') { // если не темная версия, то включаем темную версию
            enableDarkStyle();
        } else {
            disableDarkStyle()
        }
    });
}

// если юзер зашел на сайт и заранее включил черную версия сайта, то ему нужно это показать
if (localStorage.getItem('styleMode') === 'dark') {
    enableDarkStyle();
}