import UserRequests from "./user-requests";
import Utilities from "./utilities";

export default class PostRequests {
    static delete(post, currentUser) {
        if (confirm("Do you really want to delete this post?")) {
            return fetch(`/post/${post}/delete`, {
                method: "POST"
            })
                .then(response => response.json())
                .then(async data => {
                    if (currentUser.roles.includes("ROLE_ADMIN") && data.userId)
                        await UserRequests.warn(data.userId);

                    Utilities.displayMessage(data.message, "success");
                    window.scrollTo(0,0);
                    await Utilities.delay(2000);

                    location.href = "/";
                })
                .catch((error) => {
                    console.error('Error:', error);
                })
        }
    }

    static report(post) {
        if (confirm("Are you sure you want to report this comment?")) {
            return fetch(`/post/${post}/report`, { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    Utilities.displayMessage(data.message, "success");
                    window.scrollTo(0,0);
                })
                .catch(error => {
                    console.log("Error: " + error);
                })
        }
    }

    static hide(post) {
        if (confirm("Do you want to hide this post?")) {
            return fetch(`/post/${post}/hide`, { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    return true
                })
                .catch(error => {
                    console.log("Error: " + error);
                    return false;
                })
        }
    }

    static show(post) {
        return fetch(`/post/${post}/display`, { method: "POST" })
            .then(response => response.json())
            .then(data => {
                return data?.content;
            })
            .catch(error => {
                console.log("Error: " + error);
                return false;
            })
    }
}