var g_objUserData = {};

onload = () => {
    MainFrame();
}

const MainFrame = () => {
    g_objUserData.id = 1;

    let sPage = "";
    sPage += "<input id='un'></input>";
    sPage += "<input id='pw'></input>"
    sPage += "<button onClick='storeChat()'>Go</button>";
    sPage += "<div id='feedback'></div>";
    document.getElementById('Main').innerHTML = sPage;

    // postComment();
    getCommentChain();
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

const storeChat = () => {
    let objChat = {};
    objChat.to = g_objUserData.id;
    objChat.from = 2;
    objChat.chat = document.getElementById('un').value;
    let jsonChat = JSON.stringify(objChat);
    postFileFromServer("hackathon.php", "storeChat=" + encodeURIComponent(jsonChat), storeChatCallback);
    function storeChatCallback(data) {
        alert(data);
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
