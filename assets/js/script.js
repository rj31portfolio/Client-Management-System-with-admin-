 
// Auto-scroll chat to bottom
function scrollChat() {
    let chatBox = document.getElementById('chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
}

// Call on page load
document.addEventListener('DOMContentLoaded', scrollChat);