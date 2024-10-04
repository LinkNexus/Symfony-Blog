import UserRequests from "./user-requests";
import Utilities from "./utilities";

export default class CommentRequests {

    static react(comment, reaction = "like") {
        const url = `/comment/${comment}/react`;
        const data = {
            reaction: reaction
        };

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                return true;
            })
            .catch((error) => {
                console.error('Error:', error);
                return false;
            });
    }

    static submit(editor, comment, isEdited = false, post = null) {
        let url = "";
        let data = {
            content: editor.html.get(true),
        }

        // Check if a new comment is supposed to be created or
        // an existing comment must be edited
        if (!isEdited) {
            url = "/comment/create";
            data["commentReplied"] = comment;
        } else {
            url = `/comment/${comment}/edit`;
        }

        if (!comment) {
            data["post_id"] = post;
        }

        if (!editor.html || editor.html.get(true).trim().length <= 0 ) {
            Utilities.displayMessage("You cannot submit an empty content!", "danger");
            return;
        }

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Important for Symfony to detect AJAX request
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(async data => {
                if (data.id) {
                    if (data.respondedComment)
                        location.href = `/comment/${data.respondedComment}/replies#comment-${data.id}`;
                    else {
                        location.href = `/post/${data.postId}/#comment-${data.id}`; //location.href.split("#")[0] + `#comment-${data.id}`
                        location.reload();
                    }
                } else {
                    Utilities.displayMessage(data.message, "danger");
                    await Utilities.delay(2000);
                    location.reload();
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    static delete(comment, currentUser, post = null) {
        if (confirm("Do you really want to delete this comment?")) {
            return fetch(`/comment/${comment}/delete`, {
                method: "POST"
            })
                .then(response => response.json())
                .then(async data => {
                    if (currentUser.roles.includes("ROLE_ADMIN") && data.userId)
                        await UserRequests.warn(data.userId);

                    Utilities.displayMessage(data.message, "success");
                    await Utilities.delay(2000);
                    // Check if the deleted comment is the principal one and if so,
                    // redirect the user to the initial post upon deleting
                    if (post)
                        location.href = `/post/${post}`;
                    else
                        location.href = location.origin + location.pathname;
                })
                .catch((error) => {
                    console.error('Error:', error);
                })
        }
    }

    static hide(comment) {
        return fetch(`/comment/${comment}/hide`, { method: "POST" })
            .then(response => response.json())
            .then(data => {
                return true;
            })
            .catch(error => {
                console.log("Error: " + error);
                return false;
            })
    }

    static show(comment) {
        return fetch(`/comment/${comment}/display`, { method: "POST" })
            .then(response => response.json())
            .then(data => {
                return data?.content;
            })
            .catch(error => {
                console.log("Error: " + error);
                return false;
            })
    }

    static reduceDateFormat(date) {
        const words = date.split(" ");
        const secondWord = words[1];

        return words[0] + secondWord.charAt(0);
    }

    static report(comment) {
        if (confirm("Are you sure you want to report this comment?")) {
            return fetch(`/comment/${comment}/report`, { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    Utilities.displayMessage(data.message, "success");
                })
                .catch(error => {
                    console.log("Error: " + error);
                })
        }
    }
}