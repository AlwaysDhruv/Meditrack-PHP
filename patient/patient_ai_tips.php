<?php
require "../auth/patient_guard.php";
require "../config/db.php";

// Fetch patient records
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS doctor_name
    FROM records r
    JOIN users u ON r.doctor_id = u.id
    WHERE r.patient_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$patient['id']]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Health Assistant | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">

<style>

/* RECORD CARD */
.record-card {
    padding: 20px;
    border-radius: 12px;
    background: rgba(255,255,255,0.12);
    margin-bottom: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
}

/* BUTTONS */
.ask-ai-btn, .chat-btn {
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-right: 10px;
    border: none;
}
.ask-ai-btn { background: rgba(0,200,255,0.35); }
.ask-ai-btn:hover { background: rgba(0,200,255,0.55); }

.chat-btn { background: rgba(46,204,113,0.35); }
.chat-btn:hover { background: rgba(46,204,113,0.55); }

/* MODAL OVERLAY */
.modal-overlay {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

/* ASK AI MODAL */
.modal-box {
    background:#ffffff15;
    backdrop-filter:blur(12px);
    padding:20px;
    width:650px;
    border-radius:12px;
    color:white;
}

/* CHAT MODAL */
.chat-modal {
    width:750px;
    height:520px;
    background:#ffffff15;
    border-radius:12px;
    display:flex;
    flex-direction:column;
    box-shadow:0 10px 35px rgba(0,0,0,0.4);
}

/* CHAT HEADER */
.chat-header {
    padding:18px 22px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:rgba(255,255,255,0.13);
    border-top-left-radius:12px;
    border-top-right-radius:12px;
}
.close-btn {
    background:transparent;
    border:none;
    font-size:22px;
    color:white;
    cursor:pointer;
}

/* CHAT BODY */
.chat-body {
    flex:1;
    padding:18px;
    overflow-y:auto;
}
.chat-bubble {
    max-width:70%;
    padding:12px 15px;
    margin-bottom:10px;
    border-radius:10px;
    line-height:1.4;
    animation:fadeIn .2s ease-in;
}
.chat-bubble.user { background:#1abc9c55; margin-left:auto; text-align:right; }
.chat-bubble.ai { background:#3498db55; margin-right:auto; }

/* CHAT INPUT */
.chat-input-area {
    padding:14px;
    display:flex;
    background:rgba(255,255,255,0.10);
    border-bottom-left-radius:12px;
    border-bottom-right-radius:12px;
}
.chat-input-area input {
    flex:1;
    padding:12px;
    border-radius:8px;
    background:rgba(255,255,255,0.15);
    border:none;
    color:white;
    font-size:15px;
}
.send-btn {
    margin-left:10px;
    padding:12px 20px;
    border-radius:8px;
    background:#00bcd4;
    cursor:pointer;
    border:none;
    font-weight:bold;
}

/* THINKING DOTS */
#aiThinking {
    padding:10px;
    display:none;
}
.loading-dots {
    font-size:22px;
    animation: blink 1.2s infinite steps(1);
}
@keyframes blink {
    0% { opacity:0.2; }
    50% { opacity:1; }
    100% { opacity:0.2; }
}

/* AI OUTPUT */
.ai-output {
    background:rgba(255,255,255,0.10);
    padding:15px;
    border-radius:8px;
    max-height:300px;
    overflow-y:auto;
}

/* FORMAT */
.chat-heading { font-size:18px; font-weight:bold; margin-top:12px; }
.chat-warning { color:#ff7675; font-weight:bold; }
.chat-list { margin-left:20px; }

/* ANIMATION */
@keyframes fadeIn {
    from {opacity:0; transform:translateY(6px);}
    to {opacity:1; transform:translateY(0);}
}

</style>

<script>
let chatHistory = [];
let activeRecord = null;

/* =====================================================
   OPEN CHAT
===================================================== */
function openChat(recordData){
    activeRecord = recordData;
    chatHistory = [
        { sender:"ai", text:"Ask me anything about this record!" }
    ];

    updateChat();
    openModal("chatModal");
}

/* UPDATE UI */
function updateChat(){
    let box = document.getElementById("chatMessages");
    box.innerHTML = "";

    chatHistory.forEach(msg => {
        if (msg.html)
            box.innerHTML += `<div class="chat-bubble ai">${msg.html}</div>`;
        else
            box.innerHTML += `<div class="chat-bubble ${msg.sender}">${msg.text}</div>`;
    });

    document.getElementById("aiThinking").style.display = "none";
    box.scrollTop = box.scrollHeight;
}

/* SEND MESSAGE */
function sendChatMessage(){
    let input = document.getElementById("chatInput");
    let question = input.value.trim();
    if (!question) return;

    chatHistory.push({ sender:"user", text:question });
    updateChat();
    input.value = "";

    document.getElementById("aiThinking").style.display="block";

    fetch("ai_api.php", {
        method:"POST",
        body:JSON.stringify({ ...activeRecord, question })
    })
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("aiThinking").style.display="none";
        let formatted = formatAIText(d.analysis);
        chatHistory.push({ sender:"ai", html:formatted });
        updateChat();
    });
}

/* ASK AI (ANALYSIS) */
function askAI(recordData){
    openModal("aiModal");
    document.getElementById("aiResponse").innerHTML = "Analyzing... ●●●";

    fetch("ai_api.php", {
        method:"POST",
        body:JSON.stringify(recordData)
    })
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("aiResponse").innerHTML = formatAIText(d.analysis);
    });
}

/* FORMAT AI MESSAGE */
function formatAIText(text){
    let lines = text.split("\n");
    let html = "";
    let list = false;

    for(let l of lines){
        l=l.trim(); if(!l) continue;

        if(/^\d+\./.test(l)){
            if(list){ html+="</ul>"; list=false; }
            html+=`<h3 class="chat-heading">${l}</h3>`;
        }
        else if(l.startsWith("-")||l.startsWith("•")){
            if(!list){ html+="<ul class='chat-list'>"; list=true; }
            html+=`<li>${l.substring(1).trim()}</li>`;
        }
        else if(/warning|caution|danger/i.test(l)){
            if(list){ html+="</ul>"; list=false; }
            html+=`<p class="chat-warning">${l}</p>`;
        }
        else {
            if(list){ html+="</ul>"; list=false; }
            html+=`<p>${l}</p>`;
        }
    }
    if(list) html+="</ul>";
    return html;
}

function openModal(id){ document.getElementById(id).style.display="flex"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

</script>

</head>
<body>

<div class="patient-panel">
<?php include "sidebar.php"; ?>

<div class="patient-content">
<h1>🤖 AI Health Assistant</h1>

<?php foreach($records as $r): ?>

<div class="record-card">

    <h2><?php echo htmlspecialchars($r["title"]); ?></h2>
    <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($r["doctor_name"]); ?></p>
    <p><strong>Description:</strong><br><?php echo nl2br($r["description"]); ?></p>

    <p><strong>Vitals:</strong></p>
    <?php if($r['bp']) echo "<p>BP: {$r['bp']}</p>"; ?>
    <?php if($r['pulse']) echo "<p>Pulse: {$r['pulse']}</p>"; ?>
    <?php if($r['temperature']) echo "<p>Temperature: {$r['temperature']}</p>"; ?>

    <br>

    <!-- ASK AI BUTTON -->
    <button class="ask-ai-btn"
        onclick="askAI({
            title:'<?php echo addslashes($r['title']); ?>',
            description:'<?php echo addslashes($r['description']); ?>',
            bp:'<?php echo $r['bp']; ?>',
            pulse:'<?php echo $r['pulse']; ?>',
            temperature:'<?php echo $r['temperature']; ?>'
        })">
        Ask AI
    </button>

    <!-- CHAT BUTTON -->
    <button class="chat-btn"
        onclick="openChat({
            title:'<?php echo addslashes($r['title']); ?>',
            description:'<?php echo addslashes($r['description']); ?>',
            bp:'<?php echo $r['bp']; ?>',
            pulse:'<?php echo $r['pulse']; ?>',
            temperature:'<?php echo $r['temperature']; ?>'
        })">
        Chat
    </button>

</div>

<?php endforeach; ?>

</div>
</div>

<!-- ASK AI MODAL -->
<div id="aiModal" class="modal-overlay">
    <div class="modal-box">
        <h2>AI Analysis</h2>
        <div class="ai-output" id="aiResponse"></div>
        <br>
        <button class="close-btn" onclick="closeModal('aiModal')">Close</button>
    </div>
</div>

<!-- CHAT MODAL -->
<div id="chatModal" class="modal-overlay">
    <div class="chat-modal">

        <div class="chat-header">
            <h2>Chat About This Record</h2>
            <button class="close-btn" onclick="closeModal('chatModal')">✖</button>
        </div>

        <div class="chat-body" id="chatMessages"></div>

        <div id="aiThinking">
            <div class="chat-bubble ai" style="display:flex; align-items:center;">
                <span>AI is thinking</span>
                <span class="loading-dots">●●●</span>
            </div>
        </div>

        <div class="chat-input-area">
            <input type="text" id="chatInput" placeholder="Ask something about this record...">
            <button class="send-btn" onclick="sendChatMessage()">Send</button>
        </div>

    </div>
</div>

</body>
</html>
