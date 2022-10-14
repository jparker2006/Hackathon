onload = () => {
    MainFrame();
}

const MainFrame = () => {
    let sPage = "";
    sPage += "<input id='un'></input>";
    sPage += "<input id='pw'></input>";
    sPage += "<button onClick='login()'>Go</button>";
    sPage += "<div id='feedback'></div>";
    document.getElementById('Main').innerHTML = sPage;

    getSnowflake();
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
