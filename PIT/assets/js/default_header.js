function setDropdown() {
    if (localStorage.getItem("user") != null) {
        document.getElementById("login_redirect").remove();
        document.getElementById("register_redirect").remove();
    } else {
        document.getElementById("logout_redirect").remove();
        document.getElementById("historico_redirect").remove();
    }
}

function toggleDropdown(dropdownId) {
    var dropdown = document.getElementById(dropdownId);
    dropdown.style.display = (dropdown.style.display === 'flex') ? 'none' : 'flex';
}

function logout() {
    localStorage.removeItem("user");
    window.location.href = "index.html";
}