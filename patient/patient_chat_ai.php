<?php
require "../auth/patient_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Health Chat | MediTrack</title>

<link rel="stylesheet" href="../assets/css/panel.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>

/* PAGE */
.chat-page {
    padding: 20px;
}

/* CHAT CONTAINER */
.chat-container {
    width: 90%;
    height: 80vh;
    margin: auto;
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

/* HEADER */
.chat-header {
    padding: 16px 20px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    font-size: 22px;
    font-weight: bold;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

/* CHAT BODY */
.chat-body {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

/* BUBBLES */
.chat-bubble {
    max-width: 70%;
    padding: 12px 15px;
    border-radius: 12px;
    margin-bottom: 12px;
    line-height: 1.5;
    animation: fadeIn .3s ease-in;
}
.chat-bubble.user { background: #1abc9c55; margin-left: auto; text-align: right; }
.chat-bubble.ai { background: #3498db55; margin-right: auto; }

/* INPUT AREA */
.chat-input-area {
    display: flex;
    padding: 14px;
    background: rgba(255,255,255,0.15);
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}
.chat-input-area input {
    flex: 1;
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
}
.send-btn {
    margin-left: 10px;
    padding: 12px 18px;
    background: #00bcd4;
    color: white;
    font-weight: bold;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}

/* THINKING DOTS */
#aiThinking { display: none; }
.loading-dots {
    font-size: 22px;
    animation: blink 1.2s infinite steps(1);
}
@keyframes blink {
    0% { opacity: 0.2; }
    50% { opacity: 1; }
    100% { opacity: 0.2; }
}

/* AI FORMATTING */
.chat-heading { font-size: 18px; font-weight: bold; margin-top: 12px; }
.chat-warning { color: #ff7675; font-weight: bold; }
.chat-list { margin-left: 20px; }

/* FADE ANIMATION */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>

</head>

<body>

<div class="patient-panel">
<?php include "sidebar.php"; ?>

<div class="patient-content">
<div class="chat-page">

    <h1>🤖 AI Health Chat</h1>

    <div class="chat-container">

        <!-- HEADER -->
        <div class="chat-header">Talk with AI Health Assistant</div>

        <!-- CHAT BODY -->
        <div class="chat-body" id="chatMessages"></div>

        <!-- THINKING ANIMATION -->
        <div id="aiThinking">
            <div class="chat-bubble ai" style="display:flex; align-items:center;">
                <span>AI is thinking</span>
                <span class="loading-dots">●●●</span>
            </div>
        </div>

        <!-- INPUT -->
        <div class="chat-input-area">
            <input 
                type="text" 
                id="chatInput" 
                placeholder="Ask anything about health, diet, stress, fitness, sleep…" 
                onkeydown="if(event.key==='Enter') sendMessage();"
            >
            <button class="send-btn" onclick="sendMessage()">Send</button>
        </div>

    </div>

</div>
</div>
</div>


<script>

let chatHistory = [];

/* GREETING */
chatHistory.push({
    sender: "ai",
    text: "Hello! I'm your AI health companion. Ask me anything about fitness, diet, stress, sleep, motivation or general wellness!"
});
updateChat();

/* UPDATE CHAT UI */
function updateChat(){
    let box = document.getElementById("chatMessages");
    box.innerHTML = "";

    chatHistory.forEach(msg => {
        if(msg.html)
            box.innerHTML += `<div class="chat-bubble ai">${msg.html}</div>`;
        else
            box.innerHTML += `<div class="chat-bubble ${msg.sender}">${msg.text}</div>`;
    });

    document.getElementById("aiThinking").style.display = "none";
    box.scrollTop = box.scrollHeight;
}

/* SEND MESSAGE */
function sendMessage(){
    let input = document.getElementById("chatInput");
    let message = input.value.trim();
    if(!message) return;

    chatHistory.push({ sender:"user", text:message });
    updateChat();
    input.value = "";

    // show dots
    document.getElementById("aiThinking").style.display = "block";

    fetch("ai_chat_api.php", {
        method: "POST",
        body: JSON.stringify({
            question: message,
            mode: "health_general" // for safety on backend if needed
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("aiThinking").style.display = "none";

        let formatted = formatAI(data.analysis);
        chatHistory.push({ sender:"ai", html: formatted });
        updateChat();
    });
}

/* FORMAT AI TEXT */
function formatAI(text){
    let lines = text.split("\n");
    let html = "";
    let list = false;

    for(let l of lines){
        l = l.trim();
        if(!l) continue;

        if(/^\d+\./.test(l)){
            if(list){ html+="</ul>"; list=false; }
            html += `<h3 class="chat-heading">${l}</h3>`;
        }
        else if(l.startsWith("-") || l.startsWith("•")){
            if(!list){ html+="<ul class='chat-list'>"; list=true; }
            html += `<li>${l.substring(1).trim()}</li>`;
        }
        else if(/warning|caution|danger/i.test(l)){
            if(list){ html+="</ul>"; list=false; }
            html += `<p class="chat-warning">${l}</p>`;
        }
        else{
            if(list){ html+="</ul>"; list=false; }
            html += `<p>${l}</p>`;
        }
    }

    if(list) html+="</ul>";
    return html;
}

</script>

</body>
</html>
