document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('select-all-companies')
    ?.addEventListener('change', function () {

        document.querySelectorAll('.company-checkbox')
        .forEach(cb => {
            cb.checked = this.checked;
        });

    });

    function bulkAction(action) {

        let ids = [];

        document
        .querySelectorAll('.company-checkbox:checked')
        .forEach(cb => {
            ids.push(cb.value);
        });

        if (ids.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select companies.'
            });

            return;
        }

        fetch("{{ route('companies.bulk-action') }}", {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },

            body: JSON.stringify({
                action: action,
                ids: ids
            })

        })

        .then(async response => {

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message);
            }

            return data;
        })

        .then(data => {

            Swal.fire({
                icon: 'success',
                title: data.message,
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 1500);

        })

        .catch(error => {

            Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: error.message
            });

        });

    }

  document.getElementById('bulk-delete')
?.addEventListener('click', function () {

    let ids = [];
    let totalDepartments = 0;

    document
        .querySelectorAll('.company-checkbox:checked')
        .forEach(cb => {

            ids.push(cb.value);

            totalDepartments += parseInt(
                cb.dataset.departments || 0
            );

        });

    if (ids.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please select companies.'
        });

        return;
    }

    let warningMessage =
        `You are about to delete ${ids.length} selected compan${ids.length > 1 ? 'ies' : 'y'}.`;

    if (totalDepartments > 0) {

        warningMessage +=
            `\n\nThese companies contain ${totalDepartments} department(s).`;

        warningMessage +=
            `\nDeleting the companies will also delete all associated departments.`;
    }

    warningMessage +=
        '\n\nThis action cannot be undone.';

    Swal.fire({

        icon: 'warning',

        title: 'Delete Selected Companies?',

        text: warningMessage,

        showCancelButton: true,

        confirmButtonColor: '#d33',

        confirmButtonText: 'Yes, Delete',

        cancelButtonText: 'Cancel'

    }).then((result) => {

        if (result.isConfirmed) {

            bulkAction('delete');

        }

    });

});

    document.getElementById('bulk-activate')
    ?.addEventListener('click', () => bulkAction('activate'));

    document.getElementById('bulk-deactivate')
    ?.addEventListener('click', () => bulkAction('deactivate'));

});


//company statuswarning
document.querySelectorAll('.company-status-form')
.forEach(form => {

    form.addEventListener('submit', function (e) {

        const currentStatus =
            this.dataset.status;

        const companyName =
            this.dataset.company;

        const activeDepartments =
            parseInt(this.dataset.departments);

        if (currentStatus !== 'active') {
            return;
        }

        e.preventDefault();

        let warningMessage =
            `Company "${companyName}" will be deactivated.`;

        if (activeDepartments > 0) {

            warningMessage +=
                `\n\n${activeDepartments} active department(s) will also be automatically deactivated.`;

        }

        warningMessage +=
            '\n\nDo you want to continue?';

        Swal.fire({

            icon: 'warning',

            title: 'Deactivate Company?',

            text: warningMessage,

            showCancelButton: true,

            confirmButtonText: 'Yes, Deactivate',

            cancelButtonText: 'Cancel'

        }).then((result) => {

            if (result.isConfirmed) {

                form.submit();

            }

        });

    });

});