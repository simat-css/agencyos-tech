document.addEventListener('DOMContentLoaded', function () {

    // View Log Modal
    document.querySelectorAll('.view-log-btn').forEach(button => {

        button.addEventListener('click', function () {

            document.getElementById('logUser').textContent = this.dataset.user;
            document.getElementById('logCompanyId').textContent = this.dataset.companyId;
            document.getElementById('logModule').textContent = this.dataset.module;
            document.getElementById('logAction').textContent = this.dataset.action;
            document.getElementById('logDescription').textContent = this.dataset.description;
            document.getElementById('logIp').textContent = this.dataset.ip;
            document.getElementById('logBrowser').textContent = this.dataset.browser;
            document.getElementById('logDate').textContent = this.dataset.date;

            let oldData = {};
            let newData = {};

            try {
                oldData = JSON.parse(this.dataset.old || '{}');
            } catch (e) {}

            try {
                newData = JSON.parse(this.dataset.new || '{}');
            } catch (e) {}

            const tbody = document.getElementById('changesTableBody');
            const section = document.getElementById('changesSection');

            tbody.innerHTML = '';

            if (
                Object.keys(oldData).length > 0 ||
                Object.keys(newData).length > 0
            ) {

                section.style.display = 'block';

                const fields = new Set([
                    ...Object.keys(oldData),
                    ...Object.keys(newData)
                ]);

                fields.forEach(field => {

                    tbody.innerHTML += `
                        <tr>
                            <td>${field}</td>
                            <td>${oldData[field] ?? '-'}</td>
                            <td>${newData[field] ?? '-'}</td>
                        </tr>
                    `;
                });

            } else {

                section.style.display = 'none';
            }

        });

    });

    // Archive Activity Log
    document.querySelectorAll('.archive-log-form').forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({
                title: 'Archive Activity Log?',
                text: 'This log will be removed from the activity logs list.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Archive',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        });

    });

});