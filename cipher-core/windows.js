function openApp(app){

    let win = document.createElement("div");

    win.className = "window";

    win.innerHTML = `
        <div class="window-header">
            ${app}

            <button onclick="this.parentElement.parentElement.remove()">X</button>
        </div>

        <iframe src="${app}/index.php"></iframe>
    `;

    document.body.appendChild(win);
}
