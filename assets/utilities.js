export default class Utilities {
    static getCurrentUser() {
        const url = "/current-user";

        return fetch(url)
            .then(response => response.json())
            .then(data => data)
            .catch((error) => {
                console.error('Error:', error);
                return false;
            });
    }
    /* static confirmAction(message, action) {
        if (confirm(message)) {
            return fetch(action, {method: "POST"})
                .then(response => response.json())
                .then(async data => {
                    this.displayMessage(data.message);
                    await this.delay(2000);

                    location.reload();
                })
                .catch(error => console.log("Error: " + error));
        }
    } */

    static displayMessage(message, type = "success") {
        const alertBlock = document.createElement("div"),
            alertContainer = document.querySelector(".alerts");

        alertBlock.classList.add("alert", `alert-${type}`);
        alertBlock.setAttribute("role", "alert");
        alertBlock.innerHTML = `<p>${message}</p>`;
        alertContainer.appendChild(alertBlock);

        window.scrollTo(0,0);
    }

    static delay = time => new Promise(res => setTimeout(res, time));
}