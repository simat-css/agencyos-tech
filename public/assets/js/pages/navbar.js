//search 
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("globalSearch");
    const resultBox = document.getElementById("searchResult");

    if (!searchInput || !resultBox) {
        return;
    }

    searchInput.addEventListener("keyup", function () {

        let keyword = this.value.trim();

        if (keyword.length < 2) {

            resultBox.innerHTML = "";
            resultBox.style.display = "none";

            return;
        }

        fetch(`${window.routes.globalSearch}?q=${encodeURIComponent(keyword)}`)

            .then(response => response.json())

            .then(data => {

                let html = '';

                if (data.length > 0) {

                    data.forEach(item => {

                        html += `
                            <div class="result-item"
                                 data-url="${item.url}">

                                <div class="d-flex align-items-start">

                                    <i class="${item.icon} me-2 mt-1"></i>

                                    <div>

                                        <div>
                                            ${item.name}
                                        </div>

                                        <small class="text-muted">
                                            ${item.subtitle}
                                        </small>

                                    </div>

                                </div>

                            </div>
                        `;
                    });

                } else {

                    html = `
                        <div class="result-item text-muted">
                            No results found
                        </div>
                    `;
                }

                resultBox.innerHTML = html;
                resultBox.style.display = 'block';

            })

            .catch(error => {

                console.error(error);

            });

    });

    // Click suggestion

    resultBox.addEventListener('click', function (e) {

        let item = e.target.closest('.result-item');

        if (!item) {
            return;
        }

        let url = item.dataset.url;

        if (url) {
            window.location.href = url;
        }

    });

    // Hide dropdown

    document.addEventListener('click', function (e) {

        if (!e.target.closest('.agency-search')) {

            resultBox.style.display = 'none';

        }

    });

});


//responsive sidebar 
document.addEventListener('DOMContentLoaded', function () {

    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.querySelector('.app-sidebar');

    if (!sidebar) return;

    if (toggleBtn) {

        toggleBtn.addEventListener('click', function (e) {

    e.preventDefault();

    if(window.innerWidth <= 768){

        sidebar.classList.toggle('show');

    }

});

    }

    if (closeBtn) {

        closeBtn.addEventListener('click', function () {

            sidebar.classList.remove('show');

        });

    }

});