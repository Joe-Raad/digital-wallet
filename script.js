
function logout() {
    window.location.href = 'logout.php';
}


document.addEventListener("DOMContentLoaded", () => {
    // Toggle dropdown sections
    document.querySelectorAll(".toggle-title").forEach(title => {
        title.addEventListener("click", () => {
            let content = title.nextElementSibling;
            content.style.display = content.style.display === "block" ? "none" : "block";
        });
    });

    // Chat window
    document.querySelector(".chat-btn").addEventListener("click", () => {
        document.querySelector(".chat-window").style.display = "block";
    });

    document.querySelector(".close-btn").addEventListener("click", () => {
        document.querySelector(".chat-window").style.display = "none";
    });
});