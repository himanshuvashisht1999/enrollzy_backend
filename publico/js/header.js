/* ============================================
   DESKTOP MEGA MENU — Smooth Hover Logic
============================================ */
const menuItem = document.querySelector(".nav-item");
const megaMenu = document.querySelector(".mega-menu");

let closeTimer;

if (menuItem && megaMenu) {
    // SHOW MENU INSTANTLY
    menuItem.addEventListener("mouseenter", () => {
        clearTimeout(closeTimer);
        megaMenu.style.display = "block";

        setTimeout(() => {
            megaMenu.style.opacity = "1";
            megaMenu.style.transform = "translateY(0px)";
        }, 10);
    });

    // HIDE MENU — DELAYED (for smooth UX)
    menuItem.addEventListener("mouseleave", () => {
        closeTimer = setTimeout(() => {
            megaMenu.style.opacity = "0";
            megaMenu.style.transform = "translateY(10px)";
            setTimeout(() => (megaMenu.style.display = "none"), 200);
        }, 250);
    });

    // Prevent accidental close when moving slowly inside menu
    megaMenu.addEventListener("mouseenter", () => {
        clearTimeout(closeTimer);
    });

    megaMenu.addEventListener("mouseleave", () => {
        closeTimer = setTimeout(() => {
            megaMenu.style.opacity = "0";
            megaMenu.style.transform = "translateY(10px)";
            setTimeout(() => (megaMenu.style.display = "none"), 200);
        }, 200);
    });
}

/* ============================================
   MOBILE DRAWER — Toggle System (No Functions)
============================================ */
const mobileMenu = document.getElementById("mobileMenu");
const mobileOverlay = document.getElementById("mobileMenuOverlay");
const mobileToggleBtn = document.getElementById("mobileToggleBtn");

// CLICK to toggle drawer
if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("open");
        mobileOverlay.classList.toggle("show");
    });
}

// CLICK overlay to close
if (mobileOverlay) {
    mobileOverlay.addEventListener("click", () => {
        mobileMenu.classList.remove("open");
        mobileOverlay.classList.remove("show");
    });
}
