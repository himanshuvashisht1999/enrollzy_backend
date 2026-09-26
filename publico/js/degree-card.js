
document.querySelector('.apply-filter').addEventListener('click', () => {
    const checked = [...document.querySelectorAll('.filter-menu input:checked')]
                    .map(i => i.value);

    document.querySelectorAll('.degree-card-wrapper').forEach(card => {
        if (checked.length === 0 || checked.includes(card.dataset.level)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

document.querySelector('.clear-filter').addEventListener('click', () => {
    document.querySelectorAll('.filter-menu input').forEach(i => i.checked = false);
    document.querySelectorAll('.degree-card-wrapper').forEach(c => c.style.display = 'block');
});
