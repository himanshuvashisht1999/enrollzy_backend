        const observer = new IntersectionObserver((entries) => {

            entries.forEach((entry) => {

                if (entry.isIntersecting) {

                    entry.target.classList.add("show");

                }

            })

        }, {
            threshold: .2
        })

        document
            .querySelectorAll(".animate")
            .forEach(el => observer.observe(el))

        window.addEventListener("scroll", () => {

            const timeline =
                document.querySelector(".timeline");

            const progress =
                document.querySelector(".timeline-progress");

            if (!timeline || !progress) return;

            const rect =
                timeline.getBoundingClientRect();

            const windowHeight =
                window.innerHeight;

            const totalHeight =
                timeline.offsetHeight;

            const visible =
                windowHeight - rect.top;

            let percentage =
                (visible / totalHeight) * 100;

            percentage =
                Math.max(
                    0,
                    Math.min(100, percentage)
                );

            progress.style.height =
                percentage + "%";

        });

        new Swiper(".testimonialSwiper", {

            slidesPerView: 1,
            spaceBetween: 25,

            pagination: {
                el: ".swiper-pagination",
                clickable: true
            },

            breakpoints: {

                576: {
                    slidesPerView: 2
                },

                768: {
                    slidesPerView: 3
                },

                1200: {
                    slidesPerView: 4
                }

            }

        })

        new Swiper(".testimonialSlider", {

            spaceBetween: 25,

            pagination: {
                el: ".swiper-pagination",
                clickable: true
            },

            breakpoints: {

                0: {
                    slidesPerView: 1
                },

                576: {
                    slidesPerView: 2
                },

                992: {
                    slidesPerView: 3
                },

                1200: {
                    slidesPerView: 4
                }

            }

        })

        document.addEventListener('DOMContentLoaded', function() {
            const orgData = window.enrollzyOrgData || {};

            const orgSelectors = document.querySelectorAll('.org-selector');
            const courseSelectors = document.querySelectorAll('.course-selector');
            const resultsDiv = document.getElementById('comparisonResults');
            const emptyMessage = document.getElementById('emptyMessage');
            const paramTabs = document.getElementById('paramTabs');
            const matrixHead = document.getElementById('matrixHead');
            const matrixBody = document.getElementById('matrixBody');
            const resetBtn = document.getElementById('resetComparison');

            const params = [{
                    label: 'Mode of Study',
                    key: 'mode',
                    icon: 'fas fa-laptop-house'
                },
                {
                    label: 'Total Fees',
                    key: 'fee',
                    icon: 'fas fa-money-bill-wave'
                },
                {
                    label: 'Duration',
                    key: 'duration',
                    icon: 'fas fa-clock'
                },
                {
                    label: 'Rating',
                    key: 'rating',
                    isRating: true,
                    icon: 'fas fa-star'
                },
                {
                    label: 'Eligibility',
                    key: 'eligibility',
                    icon: 'fas fa-user-check'
                },
                {
                    label: 'Admission Process',
                    key: 'admission',
                    icon: 'fas fa-file-signature'
                },
                {
                    label: 'Placement',
                    key: 'placement',
                    icon: 'fas fa-briefcase'
                },
                {
                    label: 'ROI',
                    key: 'roi',
                    icon: 'fas fa-chart-line'
                },
                {
                    label: 'Ind. Collaboration',
                    key: 'industrial',
                    icon: 'fas fa-handshake'
                },
                {
                    label: 'Internship Rank',
                    key: 'internship',
                    icon: 'fas fa-medal'
                }
            ];

            let selections = {
                1: null,
                2: null,
                3: null
            };

            orgSelectors.forEach(select => {
                select.addEventListener('change', function() {
                    const slot = this.getAttribute('data-slot');
                    const orgId = this.value;
                    const courseSelect = document.querySelector(
                        `.course-selector[data-slot="${slot}"]`);

                    courseSelect.innerHTML = '<option value="">Select Course</option>';
                    selections[slot] = null;

                    if (orgId && orgData[orgId]) {
                        courseSelect.disabled = false;
                        const compareCard = this.closest(".compare-card");
                        if (compareCard) compareCard.classList.add('active-slot');

                        orgData[orgId].courses.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            courseSelect.appendChild(option);
                        });
                    } else {
                        courseSelect.disabled = true;
                        const compareCard = this.closest(".compare-card");
                        if (compareCard) compareCard.classList.remove('active-slot');
                    }

                    updateComparison();
                });
            });

            courseSelectors.forEach(select => {
                select.addEventListener('change', function() {
                    const slot = this.getAttribute('data-slot');
                    const courseId = this.value;
                    const orgId = document.querySelector(`.org-selector[data-slot="${slot}"]`)
                        .value;

                    if (courseId && orgId) {
                        const courseData = orgData[orgId].courses.find(c => c.id == courseId);
                        selections[slot] = {
                            orgName: orgData[orgId].name,
                            ...courseData
                        };
                    } else {
                        selections[slot] = null;
                    }

                    updateComparison();
                });
            });

            function updateComparison() {
                const activeSelections = Object.values(selections).filter(s => s !== null);

                if (activeSelections.length > 0) {
                    emptyMessage.classList.add('d-none');
                    resultsDiv.classList.remove('d-none');
                    paramTabs.classList.remove('d-none');
                    renderTabs();
                    renderMatrix(activeSelections);
                } else {
                    emptyMessage.classList.remove('d-none');
                    resultsDiv.classList.add('d-none');
                    paramTabs.classList.add('d-none');
                }
            }

            function renderTabs() {
                const scrollContainer = paramTabs.querySelector('.param-tabs-scroll');
                scrollContainer.innerHTML = '';
                params.forEach(p => {
                    const btn = document.createElement('div');
                    btn.className = 'param-tab-btn';
                    btn.innerHTML = `<i class="${p.icon} me-1 small"></i> ${p.label}`;
                    btn.onclick = () => {
                        const target = document.getElementById('row-' + p.key);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            target.style.backgroundColor = 'rgba(128, 92, 216, 0.05)';
                            setTimeout(() => target.style.backgroundColor = '', 2000);
                        }
                    };
                    scrollContainer.appendChild(btn);
                });
            }

            function renderMatrix(data) {
                let headHtml = `<th class="params-column py-4 ps-4">
                                    <div class="fs-5 fw-bold text-dark">Comparison</div>
                                    <div class="small text-muted fw-normal">Key Performance Indicators</div>
                                </th>`;
                data.forEach(item => {
                    headHtml += `
                        <th class="matrix-org-header">
                            <div class="matrix-org-badge text-truncate px-2" style="font-size:1.1rem;font-weight:700;color:#fff;">${item.orgName}</div>
                            <div class="matrix-course-badge text-truncate px-2" style="font-size:0.8rem;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:4px;color:#fff;">${item.name}</div>
                        </th>`;
                });
                matrixHead.innerHTML = headHtml;

                let bodyHtml = '';
                params.forEach(p => {
                    bodyHtml += `<tr id="row-${p.key}">
                        <td class="params-column ps-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-3 bg-light text-primary d-none d-lg-flex" style="width:30px;height:30px;border-radius:50%;align-items:center;justify-content:center;font-size:0.8rem;">
                                    <i class="${p.icon}"></i>
                                </div>
                                <div class="matrix-label" style="font-weight:700;font-size:0.8rem;text-transform:uppercase;">${p.label}</div>
                            </div>
                        </td>`;
                    data.forEach(item => {
                        let val = item[p.key] || 'N/A';
                        if (p.isRating) {
                            const starCount = Math.round(val);
                            let stars = '';
                            for (let i = 1; i <= 5; i++) {
                                stars +=
                                    `<i class="fa${i <= starCount ? 's' : 'r'} fa-star text-warning"></i>`;
                            }
                            val =
                                `<div class="rating-box">${stars} <span class="ms-1 text-dark fw-bold">${val}</span></div>`;
                        }
                        bodyHtml += `<td>
                            <div class="matrix-value-card">
                                <div class="matrix-value" style="color:#475569;">${val}</div>
                            </div>
                        </td>`;
                    });
                    bodyHtml += '</tr>';
                });
                matrixBody.innerHTML = bodyHtml;
            }

            resetBtn.addEventListener('click', function() {
                orgSelectors.forEach(s => s.value = '');
                courseSelectors.forEach(s => {
                    s.innerHTML = '<option value="">Select Course</option>';
                    s.disabled = true;
                });
                document.querySelectorAll('.compare-card').forEach(c => c.classList.remove('active-slot'));
                selections = {
                    1: null,
                    2: null,
                    3: null
                };
                updateComparison();
            });
        });
