import UserRequests from "./user-requests";

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
                    alert(data.message);
                    location.href = "/";
                })
                .catch((error) => {
                    console.error('Error:', error);
                })
        }
    }
}