/**
 * Main behavior logic for the Rooms application.
 */


document.addEventListener("DOMContentLoaded", function () {
    // Initial toast notification logic
    const toast = document.getElementById("toast");
    if (toast) {
        toast.style.opacity = "1";
        toast.style.transform = "translateY(0)";

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
        }, 3000);
    }
});
