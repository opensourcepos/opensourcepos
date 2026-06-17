function modalSwitchProfile() {
    document.getElementById("modal-profile").classList.remove("d-none");
    document.getElementById("modal-password").classList.add("d-none");
    document.getElementById("modal-button-profile").classList.add("active");
    document.getElementById("modal-button-password").classList.remove("active");
    document.getElementById("modal-button-save").classList.add("d-none");
    document.getElementById("modal-button-logout").classList.remove("d-none");
}

function modalSwitchPassword() {
    document.getElementById("modal-password").classList.remove("d-none");
    document.getElementById("modal-profile").classList.add("d-none");
    document.getElementById("modal-button-password").classList.add("active");
    document.getElementById("modal-button-profile").classList.remove("active");
    document.getElementById("modal-button-logout").classList.add("d-none");
    document.getElementById("modal-button-save").classList.remove("d-none");
}
