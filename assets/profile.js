import "./styles/profile.css";

const profileNavBarActions = document.querySelectorAll('.profile-nav-bar-actions>.profile-nav-bar-action'),
    profileNavBarDropdownTrigger = document.querySelector(".profile-nav-bar-dropdown-trigger"),
    profileNavBarDropdown = document.querySelector(".profile-nav-bar-dropdown-menu"),
    profileNavBarDropdownChevronUp = document.querySelector(".profile-nav-bar-dropdown-trigger .chevron-up"),
    profileNavBarDropdownChevronDown = document.querySelector(".profile-nav-bar-dropdown-trigger .chevron-down"),
    profileOptionsBlock = document.querySelector('.profile-options-container-block'),
    profileOptionsBlockTrigger = document.querySelector('.supplementary-actions-button'),
    profileOptionsBlockClose = document.querySelector('.profile-options-container-close');

profileNavBarDropdownTrigger.addEventListener("click", function () {
    if (getComputedStyle(profileNavBarDropdown).display === "none") {
        profileNavBarDropdown.style.display = "block";
        profileNavBarDropdownChevronUp.style.display = "block";
        profileNavBarDropdownChevronDown.style.display = "none";
    } else {
        profileNavBarDropdown.style.display = "none";
        profileNavBarDropdownChevronUp.style.display = "none";
        profileNavBarDropdownChevronDown.style.display = "block";
    }
})

profileOptionsBlockTrigger.addEventListener('click', function () {
    profileOptionsBlock.style.display = 'flex';
})

profileOptionsBlockClose.addEventListener('click', function () {
    profileOptionsBlock.style.display = 'none';
})

const url = new URL(window.location.href);
const urlParts = (url.host + url.pathname).split("/");
if (urlParts[2] !== "") {
    const profileAction = document.querySelector(`.profile-nav-bar-action.${urlParts[2]}`);
    if (profileAction) {
        console.log(profileAction);
        await onVisible(profileAction, () => {
            profileNavBarDropdownTrigger.classList.remove('active');
            profileAction.classList.add('active')
        });
        await notVisible(profileAction, () => {
            profileAction.classList.remove('active');
            profileNavBarDropdownTrigger.classList.add('active');
        });
    }
} else
    profileNavBarActions[0].classList.add('active');

function onVisible(element, callback) {
    new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.intersectionRatio > 0) {
                callback(element);
                // observer.disconnect();
            }
        });
    }).observe(element);
    if(!callback) return new Promise(r => callback=r);
}

function notVisible(element, callback) {
    new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.intersectionRatio <= 0) {
                callback(element);
                // observer.disconnect();
            }
        });
    }).observe(element);
    if(!callback) return new Promise(r => callback=r);
}