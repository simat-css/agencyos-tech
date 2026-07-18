<!-- AI Floating Button -->
<button
    class="btn btn-primary rounded-circle shadow-lg position-fixed"
    style="bottom:25px; right:25px; width:60px; height:60px; z-index:1055;"
    data-bs-toggle="offcanvas"
    data-bs-target="#aiAssistant">

    <i class="fas fa-robot fs-4"></i>
</button>

<!-- AI Assistant Panel -->
<div class="offcanvas offcanvas-end"
     tabindex="-1"
     id="aiAssistant"
     style="width:420px;">

    <div class="offcanvas-header border-bottom">

        <div>
            <h5 class="mb-0">
                <i class="fas fa-robot text-primary me-2"></i>
                AgencyOS AI Assistant
            </h5>

            <small class="text-muted">
                Company Management MVP
            </small>
        </div>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"></button>

    </div>

    <div class="offcanvas-body d-flex flex-column p-0">

        <!-- Chat Area -->
        <div id="chatMessages"
             class="flex-grow-1 p-3"
             style="overflow-y:auto; min-height:500px;">

            <div class="card border-0 bg-light mb-3">
                <div class="card-body">

                    <strong>👋 Welcome to AgencyOS AI</strong>

                    <hr>

                    <p class="mb-1">Try commands:</p>

                    <ul class="small mb-0">
                        <li>Create company ABC Technologies</li>
                        <li>Show active companies</li>
                        <li>Deactivate ABC Technologies</li>
                    </ul>

                </div>
            </div>

        </div>

        <!-- Input -->
        <div class="border-top p-3 bg-white">

            <form id="aiCommandForm" data-route="{{ route('ai.execute') }}">

                @csrf

                <div class="input-group">

                    <input
                        type="text"
                        id="aiCommand"
                        class="form-control"
                        placeholder="Type your command...">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-paper-plane"></i>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@push('scripts')
<script src="{{ asset('assets/js/pages/ai-assistant.js') }}"></script>
@endpush