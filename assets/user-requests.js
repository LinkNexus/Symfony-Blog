import Utilities from "./utilities";

export default class UserRequests {
    static warn(user) {
        if (confirm("Do you want to give a warning to this user?")) {
            return fetch(`/user/${user}/warn`, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
                },
                body: JSON.stringify({reason: "The comment violated the Community Rules"})
            })
                .then(response => response.json())
                .then(data => {
                    Utilities.displayMessage(data.message)
                    window.scrollTo(0,0);
                })
                .catch(error => console.log("Error: " + error));
        }
    }

    static block(user) {
        if (confirm("Do you want to block this user?")) {
            return fetch(`/user/${user}/block`, { method: "POST" })
                .then(response => response.json())
                .then(async data => {
                    Utilities.displayMessage(data.message);
                    window.scrollTo(0,0);
                    await Utilities.delay(2000);
                    location.reload();
                })
                .catch(error => console.log("Error: " + error));
        }
    }

    static unblock(user) {
        if (confirm("Do you want to unblock this user?")) {
            return fetch(`/user/${user}/unblock`, { method: "POST" })
                .then(response => response.json())
                .then(async data => {
                    Utilities.displayMessage(data.message);
                    window.scrollTo(0,0);
                    await Utilities.delay(2000);
                    location.reload();
                })
                .catch(error => console.log("Error: " + error));
        }
    }

    static snooze(user) {
        if (confirm("Do you want to snooze this user?")) {
            return fetch(`/user/${user}/snooze`, { method: "POST" })
                .then(response => response.json())
                .then(async data => {
                    Utilities.displayMessage(data.message);
                    window.scrollTo(0,0);
                    await Utilities.delay(2000);
                    location.reload();
                })
                .catch(error => console.log("Error: " + error));
        }
    }
}