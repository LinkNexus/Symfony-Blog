import UserRequests from "./user-requests";

export default class CommentRequests {
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
            alert("You cannot submit an empty content!");
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
                    }
                } else {
                    alert(data.message);
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
                    alert(data.message);
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
                    alert(data.message);
                })
                .catch(error => {
                    console.log("Error: " + error);
                })
        }
    }
}