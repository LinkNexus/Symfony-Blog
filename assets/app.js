import './bootstrap.js';
import { Alert } from 'bootstrap';
import dayjs from 'dayjs';
import calendar from 'dayjs/plugin/calendar';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import Darkmode from "darkmode-js/src";

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/external.css';
import './styles/responsive.css';
import './styles/main.css';

const root = document.documentElement;

if (localStorage.getItem('darkMode') === 'enabled') {
    root.style.setProperty('--second-background', '#242526');
    root.style.setProperty('--principal-background', '#18191a');
    root.style.setProperty('--third-background', '#3a3b3c');
    root.style.setProperty('--fourth-background', '#323436');
    root.style.setProperty('--fifth-background', '#303031');
    root.style.setProperty('--secondary-text-color', '#8d8f93');
    document.querySelector('body').style.color = '#fff';
}

dayjs().format();
dayjs.extend(localizedFormat);

document.addEventListener('DOMContentLoaded', function () {

    // Change the welcome text on the login page depending on the screen size
    const loginDescription = document.querySelector('.login-text-container p'),
        initialLoginText = 'Nexus is a technology platform for beginners, researchers, etc.\n' +
            'The goal here is to help each other by sharing our knowledge\n' +
            'on the various technology fields. Nexus is here to bring us closer!',
        newLoginText = 'Connect with other Technology Fans around the World!',
        breakpoint = 925;

    function updateLoginText() {
        if (null !== loginDescription) {
            if (window.innerWidth <= breakpoint) {
                loginDescription.textContent = newLoginText;
            } else {
                loginDescription.textContent = initialLoginText;
            }
        }
    }

    // Initial Check
    updateLoginText();

    // Making sure the login text changes on window resize
    window.addEventListener('resize', updateLoginText);
})

const postModificationDates = document.querySelectorAll('.update-history .modification-date:not(#modification-date)');
postModificationDates.forEach(date => {
    date.textContent = dayjs(date.textContent).format('LLLL');
})

