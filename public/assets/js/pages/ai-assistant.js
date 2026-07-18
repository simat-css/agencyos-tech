document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('aiCommandForm');

    if (!form) return;

    const aiRoute = form.dataset.route;

    form.addEventListener('submit', async function (e) {

        e.preventDefault();

        const command = document.getElementById('aiCommand').value;

        if (!command.trim()) return;

        try {

            const response = await fetch(aiRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content
                },
                body: JSON.stringify({
                    command: command
                })
            });

            const data = await response.json();

            const chatMessages =
                document.getElementById('chatMessages');

            chatMessages.innerHTML += `
                <div class="text-end mb-3">
                    <div class="d-inline-block bg-primary text-white p-2 rounded">
                        ${command}
                    </div>
                </div>

                <div class="text-start mb-3">
                    <div class="d-inline-block bg-light border p-2 rounded">
                        ${data.message.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;

            chatMessages.scrollTop =
                chatMessages.scrollHeight;

            document.getElementById('aiCommand').value = '';

        } catch (error) {

            console.error(error);

            alert('Something went wrong.');
        }

    });

});