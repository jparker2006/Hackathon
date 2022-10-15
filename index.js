var g_objUserData = {};

onload = () => {
    PrepRTCToBrowser();
    MainFrame();
}

var wsUri = "ws://jakehenryparker.com:58007";
if (window.location.protocol === 'https:') {
    wsUri = "wss://jakehenryparker.com:57007/wss";
}
var wSocket = null;
const initWebSocket = () => {
    try {
        if (typeof MozWebSocket == 'function')
            WebSocket = MozWebSocket;
        if (wSocket && wSocket.readyState == 1) // OPEN
            wSocket.close();
        wSocket = new WebSocket(wsUri);
        wSocket.onopen = (evt) => {
            SendMyID();
            SetGameID(0);
            console.log("Connection established.");
        }
        wSocket.onclose = (evt) => {
            console.log("Connection closed");
        };
        wSocket.onmessage = (evt) => {
            let objData = JSON.parse(evt.data);
            let sType = objData.Type;
            if ("Jake" == sType) {
                if ("Msg2ID" == objData.Message) {
                    if ("TextFrom" == objData.Event) {
                        console.log(objData);
                    }
                    else if ("StartRTC" == objData.Event) {
                        console.log(objData);
                        g_objUserData.nCalling = 1;
                        start(true);
                    }
                    else if ("SDP" == objData.Event) {
                        console.log("SDP recieved");
                        g_objUserData.PC.setRemoteDescription(new RTCSessionDescription(objData.sdp)).then(function() {
                            if (objData.sdp.type == 'offer') {
                                g_objUserData.PC.createAnswer().then(createdDescription).catch(errorHandler);
                            }
                        }).catch(errorHandler);
                    }
                    else if ("Ice" == objData.Event) {
                        console.log("Ice recieved");
                        g_objUserData.PC.addIceCandidate(new RTCIceCandidate(objData.ice)).catch(errorHandler);
                    }
                }
                else if ("WhoAmI" == objData.Message) {
                    console.log("I am: " + objData.ID);
                }
            }
        }
    }
    catch (exception) {
        console.log('ERROR: ' + exception);
    }
}

const gotRemoteStream = (event) => {
    console.log('got remote stream');
    document.getElementById('remote').srcObject = event.streams[0];
}

const SendMyID = () => {
    let objData = {};
    objData.Type = "Jake";
    objData.GameID = 0;
    objData.ID = g_objUserData.id;
    objData.Message = "MyID";
    let jsonData = JSON.stringify(objData);
    sendMessage(jsonData);
}

const SetGameID = (nGameID) => {
    let objData = {};
    objData.Type = "Jake";
    objData.Message = "SetGameID";
    objData.GameID = parseInt(nGameID);
    let jsonData = JSON.stringify(objData);
    sendMessage(jsonData);
    g_objUserData.nGameID = nGameID;
}

const MainFrame = () => {
    let sPage = "";
    sPage += "<input id='idNUmbr' placeholder='id'></input>";
    // sPage += "<input id='chat' placeholder='chat'></input>";
    // sPage += "<button onClick='sendChat()'>Go</button>";
    sPage += "<button onClick='submitID()'>Submit ID</button>";
    sPage += "<button onClick='startRTC()'>Run RTC</button>";
    sPage += "<div>";
    sPage += "<video id='local' style='border: 1px solid; width: 500px; height: 500px;' autoplay muted ></video>";
    sPage += "<video id='remote' style='border: 1px solid; width: 500px; height: 500px;' autoplay muted ></video>";
    sPage += "</div>";
    document.getElementById('Main').innerHTML = sPage;
}

const startRTC = () => {
    let objData = {};
    objData.ToID = parseInt(2);
    objData.Type = "Jake";
    objData.GameID = 0;
    objData.Message = "Msg2ID";
    objData.ID = g_objUserData.id;
    objData.Event = "StartRTC";
    let jsonData = JSON.stringify(objData);
    sendMessage(jsonData);

    g_objUserData.nCalling = 2;

    start(false);
}

const start = (bCaller) => {
    g_objUserData.PC = new RTCPeerConnection({ 'iceServers':
        [ {'urls': 'stun:stun.stunprotocol.org:3478'}, {'urls': 'stun:stun.l.google.com:19302'} ]
    });

    g_objUserData.PC.onicecandidate = gotIceCandidate;
    g_objUserData.PC.ontrack = gotRemoteStream;

    PCStream(bCaller);
}


const PCStream = (bCaller) => {
    setTimeout(function() {
        if (!g_objUserData.LocalStream) {
            PCStream(bCaller);
            console.log("Still trying");
        }
        else {
            g_objUserData.PC.addStream(g_objUserData.LocalStream);
            if (bCaller) {
                console.log("Caller making offer");
                g_objUserData.PC.createOffer().then(createdDescription).catch(errorHandler);
            }
        }
    }, 500);
}

const gotIceCandidate = (event) => {
    if (event.candidate != null) {
        let objData = {};
        objData.ToID = parseInt(g_objUserData.nCalling);
        objData.Type = "Jake";
        objData.GameID = 0;
        objData.ID = g_objUserData.id;
        objData.Event = "Ice";
        objData.Message = "Msg2ID";
        objData.ice = event.candidate;
        let jsonData = JSON.stringify(objData);
        sendMessage(jsonData);
    }
}

const createdDescription = (description) => {
    console.log('got description');

    g_objUserData.PC.setLocalDescription(description).then(function() {

        let objData = {};
        objData.ToID = parseInt(g_objUserData.nCalling);
        objData.Type = "Jake";
        objData.GameID = 0;
        objData.Message = "Msg2ID";
        objData.ID = g_objUserData.id;
        objData.Event = "SDP";
        objData.sdp = g_objUserData.PC.localDescription;
        let jsonData = JSON.stringify(objData);
        sendMessage(jsonData);

    }).catch(errorHandler);
}

const getUserMediaSuccess = (stream) => {
    g_objUserData.LocalStream = stream;
    document.getElementById('local').srcObject = stream;
}

const setRTCConstraints = () => {
    if (navigator.mediaDevices.getUserMedia)
        navigator.mediaDevices.getUserMedia({ video: true, audio: false }).then(getUserMediaSuccess).catch(errorHandler);
    else
        alert('Your browser does not support getUserMedia API');
}

const errorHandler = (error) => {
    console.log(error);
}

const sendChat = () => {
    let objData = {};
    if (1 == g_objUserData.id)
        objData.ToID = parseInt(2);
    else
        objData.ToID = parseInt(1);
    objData.Type = "Jake";
    objData.GameID = 0;
    objData.Message = "Msg2ID";
    objData.ID = g_objUserData.id;
    objData.Event = "TextFrom";
    objData.Chat = document.getElementById('chat').value;
    let jsonData = JSON.stringify(objData);
    sendMessage(jsonData);

    let objChat = {};
    objChat.to = objData.ToID;
    objChat.from = g_objUserData.id;
    objChat.chat = document.getElementById('chat').value;
    storeChat(objChat);
}

const submitID = () => {
    g_objUserData.id = Number(document.getElementById('idNUmbr').value);
    initWebSocket();
    setRTCConstraints();
}

const getCommentChain = () => {
    postFileFromServer("hackathon.php", "getCommentChain=" + encodeURIComponent(1), getCommentChainCallback);
    function getCommentChainCallback(data) {
        alert(data);
    }
}

const search = () => {
    postFileFromServer("hackathon.php", "search=" + encodeURIComponent("is this math system hard"), searchTagCallback);
    function searchTagCallback(data) {
        alert(data);
    }
}

const postComment = () => {
    let objComment = {};
    objComment.uuid = g_objUserData.id;
    objComment.dentry = "what is system print";
    objComment.prev = 0;
    objComment.tags = "Math Science Language CS";
    let jsonComment = JSON.stringify(objComment);
    postFileFromServer("hackathon.php", "postComment=" + encodeURIComponent(jsonComment), postCommentCallback);
    function postCommentCallback(data) {
        alert(data);
    }
}

const searchByTag = () => {
    postFileFromServer("hackathon.php", "searchByTag=" + encodeURIComponent("math"), searchTagCallback);
    function searchTagCallback(data) {
        alert(data);
    }
}

const pullChats = () => {
    let objChat = {};
    objChat.to = 2;
    objChat.from = g_objUserData.id;
    let jsonChat = JSON.stringify(objChat);
    postFileFromServer("hackathon.php", "pullChats=" + encodeURIComponent(jsonChat), pullChatsCallback);
    function pullChatsCallback(data) {
        alert(data);
    }
}

const storeChat = (objChat) => {
    let jsonChat = JSON.stringify(objChat);
    postFileFromServer("hackathon.php", "storeChat=" + encodeURIComponent(jsonChat), storeChatCallback);
    function storeChatCallback(data) {
        console.log(data);
    }
}

const pullBurstComments = () => {
    let nPage = 1;
    postFileFromServer("hackathon.php", "pullBurstComments=" + encodeURIComponent(nPage), pullBurstCommentsCallback);
    function pullBurstCommentsCallback(data) {
        alert(data);
    }
}

const editComment = () => {
    let objComment = {};
    objComment.comment = "this is a new comment";
    objComment.id = 1; // comments id
    let jsonComment = JSON.stringify(objComment);
    postFileFromServer("hackathon.php", "editComment=" + encodeURIComponent(jsonComment), editCommentCallback);
    function editCommentCallback(data) {
        alert(data);
    }
}

const sendFriendRequest = () => {
    let objIDs = {};
    objIDs.to = 2;
    objIDs.from = g_objUserData.id;
    let jsonIDs = JSON.stringify(objIDs);
    postFileFromServer("hackathon.php", "sendFriendRequest=" + encodeURIComponent(jsonIDs), sendFriendRequestCallback);
    function sendFriendRequestCallback(data) {
        alert(data);
    }
}

const acceptFriendRequest = () => {
    let objIDs = {};
    objIDs.to = 2;
    objIDs.from = g_objUserData.id;
    let jsonIDs = JSON.stringify(objIDs);
    postFileFromServer("hackathon.php", "acceptFriendRequest=" + encodeURIComponent(jsonIDs), acceptFriendRequestCallback);
    function acceptFriendRequestCallback(data) {
        alert(data);
    }
}

const denyFriendRequest = () => {
    let objIDs = {};
    objIDs.to = 2;
    objIDs.from = g_objUserData.id;
    let jsonIDs = JSON.stringify(objIDs);
    postFileFromServer("hackathon.php", "denyFriendRequest=" + encodeURIComponent(jsonIDs), denyFriendRequestCallback);
    function denyFriendRequestCallback(data) {
        alert(data);
    }
}

const castVote = () => {
    let objVote = {};
    objVote.nvote = 1;
    objVote.id = 1;
    let jsonVote = JSON.stringify(objVote);
    postFileFromServer("hackathon.php", "castVote=" + encodeURIComponent(jsonVote), castVoteCallback);
    function castVoteCallback(data) {
        alert(data);
    }
}

const pullComments = () => {
    postFileFromServer("hackathon.php", "pullComment=" + encodeURIComponent(1), pullCommentsCallback);
    function pullCommentsCallback(data) {
        alert(data);
    }
}

const uniqueUN = () => {
    let sUsername = document.getElementById('un').value;
    postFileFromServer("hackathon.php", "uniqueUN=" + encodeURIComponent(sUsername), uniqueUNCallback);
    function uniqueUNCallback(data) {
        if (data)
            document.getElementById('feedback').innerHTML = data;
        else
            document.getElementById('feedback').innerHTML = "not unique un";
    }
}

const createAcc = () => {
    let objAccount = {};
    objAccount.username = document.getElementById('un').value;
    objAccount.password = document.getElementById('pw').value;
    let jsonAccount = JSON.stringify(objAccount);
    postFileFromServer("hackathon.php", "createAccount=" + encodeURIComponent(jsonAccount), createAccountCallback);
    function createAccountCallback(data) {
        alert(data);
    }
}

const login = () => {
    let objAccount = {};
    objAccount.username = document.getElementById('un').value;
    objAccount.password = document.getElementById('pw').value;
    let jsonAccount = JSON.stringify(objAccount);
    postFileFromServer("hackathon.php", "login=" + encodeURIComponent(jsonAccount), createAccountCallback);
    function createAccountCallback(data) {
        alert(JSON.stringify(data));
    }
}

const getSnowflake = () => {
    postFileFromServer("hackathon.php", "genSnowflake=" + encodeURIComponent(1), genSnowflakeCallback);
    function genSnowflakeCallback(data) {
        document.getElementById('feedback').innerHTML = data;
    }
}

const postFileFromServer = (url, sData, doneCallback) => {
    var xhr;
    xhr = new XMLHttpRequest();
    xhr.onreadystatechange = handleStateChange;
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(sData);
    function handleStateChange() {
        if (xhr.readyState === 4) {
            doneCallback(xhr.status == 200 ? xhr.responseText : null);
        }
    }
}

const stopWebSocket = () => {
    if (wSocket)
        wSocket.close(1000, "Deliberate disconnection");
}

const close_socket = () => {
    if (wSocket.readyState === WebSocket.OPEN)
        wSocket.close(1000, "Deliberate disconnection");
}

const CheckConnection = () => {
    if (!g_objUserData.sUsername)
        return;
    if (!wSocket)
        initWebSocket();
    else if (wSocket.readyState == 3) { // Closed
        wSocket = null;
        initWebSocket();
    }
}

const sendMessage = (jsonData) => {
    if (wSocket != null && 1 == wSocket.readyState)
        wSocket.send(jsonData);
    else {
        console.log("ws error");
        CheckConnection();
        sendMessage.jsonData = jsonData;
        setTimeout(function(){wSocket.send(sendMessage.jsonData);}, 1500);
    }
}

var hidden, visibilityChange;
const ShowVisibilityChange = () => {
    //var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    if ('visible' === document.visibilityState)
        CheckConnection();
}

const VisiblitySetup = () => {
    if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    }
    else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    }
    else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }
    document.addEventListener(visibilityChange, ShowVisibilityChange, false);
}

VisiblitySetup();

const PrepRTCToBrowser = () => {
    navigator.getUserMedia = navigator.getUserMedia || navigator.mozGetUserMedia || navigator.webkitGetUserMedia;
    window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
    window.RTCIceCandidate = window.RTCIceCandidate || window.mozRTCIceCandidate || window.webkitRTCIceCandidate;
    window.RTCSessionDescription = window.RTCSessionDescription || window.mozRTCSessionDescription || window.webkitRTCSessionDescription;
    window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || window.mozSpeechRecognition || window.msSpeechRecognition || window.oSpeechRecognition;
}
