import "./styles/profile.css";

const profileNavBarActions = document.querySelectorAll('.profile-nav-bar-actions>.profile-nav-bar-action'),
    profileNavBarDropdownTrigger = document.querySelector(".profile-nav-bar-dropdown-trigger"),
    profileNavBarDropdown = document.querySelector(".profile-nav-bar-dropdown-menu"),
    profileNavBarDropdownChevronUp = document.querySelector(".profile-nav-bar-dropdown-trigger .chevron-up"),
    profileNavBarDropdownChevronDown = document.querySelector(".profile-nav-bar-dropdown-trigger .chevron-down"),
    profileOptionsBlock = document.querySelector('.profile-options-container-block'),
    profileOptionsBlockTrigger = document.querySelector('.supplementary-actions-button'),
    profileOptionsBlockClose = document.querySelector('.profile-options-container-close'),
    profileImageContainer = document.querySelector(".profile-image-block .image-container"),
    profileImageBlockButton = document.querySelector(".profile-image-block-button"),
    uploadAvatarFormContainer = document.querySelector("#profile-picture-form.upload-avatar-form-container"),
    uploadAvatarFormClose = document.querySelector("#profile-picture-form .upload-avatar-container-close"),
    uploadCoverFormContainer = document.querySelector("#cover-photo-form.upload-avatar-form-container"),
    uploadCoverFormClose = document.querySelector("#cover-photo-form .upload-avatar-container-close"),
    coverPhotoButton = document.querySelector(".cover-photo-button"),
    coverPhoto = document.querySelector(".cover-photo");

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

const defaultProfileImages = [
    "male_icon.jpg",
    "female_icon.jpg",
    "other_icon.jpg"
];

profileImageBlockButton?.addEventListener("click", function () {
    uploadAvatarFormContainer.style.display = "flex";
});

coverPhotoButton?.addEventListener("click", function () {
    uploadCoverFormContainer.style.display = "flex";
})

uploadAvatarFormClose?.addEventListener("click", function () {
    uploadAvatarFormContainer.style.display = "none";
})

uploadCoverFormClose?.addEventListener("click", function () {
    uploadCoverFormContainer.style.display = "none";
})

profileImageContainer.addEventListener("click", function (evt) {
    // evt.stopPropagation();
    if (
        defaultProfileImages.includes(this.children[0].getAttribute("src").replace("/images/", "")) &&
        this.dataset.authenticatedUser === this.dataset.user
    ) {
        uploadAvatarFormContainer.style.display = "flex";
    } else {
        location.href = this.children[0].getAttribute("src");
    }
})

coverPhoto.addEventListener("click", function (evt) {
    evt.stopPropagation();

    if (getComputedStyle(this).backgroundColor === "rgb(240, 242, 245)" && this.dataset.authenticatedUser === this.dataset.user) {
            uploadCoverFormContainer.style.display = "flex";
    } else {
        const backgroundImage = getComputedStyle(this).backgroundImage;
        const urlMatch = backgroundImage.match(/url\(["']?([^"']*)["']?\)/);
        if (urlMatch) {
            location.href = urlMatch[1];
        }
    }
})

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