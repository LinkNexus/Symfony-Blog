// Imports

import './styles/main.css';
import FroalaEditor from 'froala-editor';
import 'froala-editor/css/froala_editor.pkgd.min.css';
import 'froala-editor/css/froala_style.min.css';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

// Load your languages
import 'froala-editor/js/languages/en_gb.js';

// Load all plugins, or specific ones
import 'froala-editor/js/plugins.pkgd.min.js';
import 'froala-editor/css/plugins.pkgd.min.css';
import 'froala-editor/css/themes/dark.min.css';

window.FroalaEditor = FroalaEditor;

/**
 * @param editor Editor instance
 * @param error Error object generated by Froala
 * @param response Response object coming from the server
 */
function froalaDisplayError(editor, error, response) {
    alert(`Error ${error.code}: ${error.message}`);
}

window.froalaDisplayError = froalaDisplayError;

dayjs.extend(relativeTime);

async function getCurrentUser() {
    const url = "/current-user";

    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
        }
    })
        .then(response => response.json())
        .then(data => {
            return data;
        })
        .catch((error) => {
            console.error('Error:', error);
            return false;
        })
}
const currentUser = await getCurrentUser();

// Main Header

const fakeSearchBar = document.querySelector('.fake-search-bar'),
    searchForm = document.querySelector('.search-form'),
    searchFormReturnButton = document.querySelector('.return-icon'),
    mainMenuButton = document.querySelector('.menu-action'),
    mainMenu = document.querySelector('.main-menu'),
    mainMenuButtonIcon = document.querySelector('.menu-action svg path'),
    fakeMenuSearchInput = document.querySelector('.fake-search-input'),
    menuSearchInput = document.querySelector('.menu-search-input'),
    profileMenu = document.querySelector('.profile-menu'),
    profileMenuButton = document.querySelector('.header-action.profile-picture');

fakeSearchBar.addEventListener('click', () => {
    fakeSearchBar.style.visibilty = 'hidden';
    searchForm.style.display = 'flex';
})

searchFormReturnButton.addEventListener('click', () => {
    searchForm.style.display = 'none';
    fakeSearchBar.style.visibilty = 'visible';
})

mainMenuButton.addEventListener('click', () => {
    if (getComputedStyle(mainMenu).display === 'none') {
        mainMenu.style.display = 'block';
        mainMenuButtonIcon.style.fill = 'var(--principal-color)';
    } else {
        mainMenu.style.display = 'none';
        mainMenuButtonIcon.style.fill = '#000';
    }

    if (getComputedStyle(profileMenu).display === 'flex') {
        profileMenu.style.display = 'none';
    }
})

profileMenuButton.addEventListener('click', () => {
    if (getComputedStyle(profileMenu).display === 'none') {
        profileMenu.style.display = 'flex';
    } else {
        profileMenu.style.display = 'none';
    }

    if (getComputedStyle(mainMenu).display === 'block') {
        mainMenu.style.display = 'none';
        mainMenuButtonIcon.style.fill = '#000';
    }
})

fakeMenuSearchInput.addEventListener('click', () => {
    menuSearchInput.style.display = 'block';
    menuSearchInput.focus();
    fakeMenuSearchInput.style.display = 'none';
})

window.addEventListener('load', () => {
    menuSearchInput.addEventListener('blur', checkMenuSearchInput);
    checkMenuSearchInput();
})

function checkMenuSearchInput() {
    if (document.activeElement !== menuSearchInput && menuSearchInput.value.trim() === '') {
        menuSearchInput.style.display = 'none';
        fakeMenuSearchInput.style.display = 'flex';
    }
}

// Left Aside Menu of Home Page

const leftAsideMenuElements = document.querySelectorAll('.left-aside-menu-element');

if (Object.keys(leftAsideMenuElements).length !== 0) {

    leftAsideMenuElements[7].addEventListener('click', function (evt) {
        evt.preventDefault();
        this.style.display = 'none';
        for (const leftAsideMenuElement of leftAsideMenuElements) {
            if (leftAsideMenuElement.classList.contains('not-displayed')) {
                leftAsideMenuElement.style.display = 'flex';
            }
        }
    })

    leftAsideMenuElements[13].addEventListener('click', function (evt) {
        evt.preventDefault();
        leftAsideMenuElements[7].style.display = 'flex';
        for (const leftAsideMenuElement of leftAsideMenuElements) {
            if (leftAsideMenuElement.classList.contains('not-displayed')) {
                leftAsideMenuElement.style.display = 'none';
            }
        }
    })
}

// Fake Upload Post Input Redirection

const fakeUploadPostInput = document.querySelector('.fake-upload-post-input');

if (fakeUploadPostInput) {
    fakeUploadPostInput.addEventListener('click', function () {
        window.location.href = '/post/create'
    })
}

// Create Post Editor

const postFormEditor = new FroalaEditor('#post_content', {
    saveURL: '/post/create',
    imageUploadMethod: 'POST',
    imageUploadURL: '/post/upload/images',
    imageAllowedTypes: ['jpeg', 'jpg', 'png'],
    imageMaxSize: 1024 * 1024 * 3,
    videoUploadMethod: 'POST',
    videoUploadURL: '/post/upload/videos',
    videoAllowedTypes: ['mp4', 'gif', 'ogg', 'webm'],
    videoMaxSize: 1024 * 1024 * 10,
    fileUploadMethod: 'POST',
    fileUploadURL: '/post/upload/files',
    fileAllowedTypes: ['application/pdf'],
    fileMaxSize: 1024 * 1024 * 5,
    heightMin: 200,
    events: {
        contentChanged: checkEditorContent,
        "image.removed": function ($img) {
            const url = $img.attr("src");

            fetch("/delete/file", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
                },
                body: JSON.stringify({url: url})
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        }
    },
    "image.replaced": function ($img, response) {
        const url = response.link;

        fetch("/delete/file", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
            },
            body: JSON.stringify({url: url})
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    },
    language: 'en',
    imageManagerDeleteURL: '/post/delete/image',
    imageManagerDeleteMethod: 'POST',
})

// Dark Mode

/* if (localStorage.getItem('darkMode') === 'enabled') {
    postFormEditor.opts.theme = 'dark';

    if (document.querySelector('.fr-element')) {
        document.querySelector('.fr-wrapper').background = '#f0f0f0';
        document.querySelector('.fr-wrapper').color = '#333';
    }
} */

// Verify if the editor has text in it

const submitButton = document.querySelector('.create-post-form>button[type="submit"]');

function checkEditorContent() {
    if (submitButton) {
        if (postFormEditor.html) {
            let editorContent = postFormEditor.html.get(true);
            submitButton.disabled = editorContent.trim().length <= 0;
        } else {
            submitButton.disabled = true;
        }
    }
}

checkEditorContent();

// Post Audience Section

const postFormAudienceSelect = document.querySelector('#post_audienceType'),
    postAudience = document.querySelector('.post-form-audience'),
    postAudienceHeader = document.querySelector('.post-form-audience h2'),
    postFormAudienceUsers = document.querySelector('#post_audience_user'),
    postFormAudienceSave = document.querySelector('.post-form-audience .button');

if (postAudience) {
    postFormAudienceSelect.addEventListener('change', function (event) {
        const selectedOption = event.target.options[event.target.selectedIndex];

        if (selectedOption.innerText.includes('Friends except...') || selectedOption.innerText.includes('Specific friends')) {
            postAudienceHeader.innerText = selectedOption.innerText;
            postAudience.style.display = "block";
            postFormAudienceUsers.selectedIndex = -1;
        } else {
            postAudience.style.display = "none";
            postFormAudienceUsers.selectedIndex = -1;
        }

        postFormAudienceUsers.selectedIndex = -1;
    })

    postFormAudienceSave.addEventListener('click', function (evt) {
        postAudience.style.display = "none";
    })
}

const createPostFormTitle = document.querySelector('.create-post-form-container h2');

if (submitButton && createPostFormTitle && createPostFormTitle.innerText === 'Update Post') {
    submitButton.disabled = false;

    if (postFormAudienceSelect.value.includes('friends_except') || postFormAudienceSelect.value.includes('specific_friends')) {
        postAudience.style.display = "block";
    }
}