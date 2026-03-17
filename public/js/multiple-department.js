document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".department-scroll");
    const left = document.querySelector(".left-arrow");
    const right = document.querySelector(".right-arrow");

    left.addEventListener("click", () => {
        container.scrollBy({
            left: -200,
            behavior: "smooth",
        });
    });

    right.addEventListener("click", () => {
        container.scrollBy({
            left: 200,
            behavior: "smooth",
        });
    });
});


document.querySelectorAll(".department-list li").forEach((item) => {
    item.addEventListener("click", () => {
        document
            .querySelector(".department-list .active")
            ?.classList.remove("active");
        item.classList.add("active");
    });
});
