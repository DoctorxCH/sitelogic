import { saveToDB } from '../db';
import { logAction } from '../auditLog';

export const renderWorkflowControls = (checklist, container, wrapper) => {
    // In a real app, user role would come from auth context. Hardcoding for demo.
    const isBauleiter = window.location.search.includes('role=bauleiter');
    const isMonteur = !isBauleiter;

    const updateStatus = async (newStatus, comment = null) => {
        checklist.status = newStatus;
        if (comment) checklist.reject_comment = comment;

        await saveToDB('checklists', checklist);
        await logAction('STATUS_CHANGE', checklist.id, `Changed to ${newStatus}`);

        // Force refresh UI (simplified reload)
        window.location.reload();
    };

    let html = '';

    if (isMonteur) {
        if (checklist.status !== 'completed' && checklist.status !== 'approved') {
            html += `
                <div class="flex gap-2">
                    <button class="btn-save bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600">Zwischenspeichern (in work)</button>
                    <button class="btn-submit bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Abschließen (completed)</button>
                </div>
            `;
        } else {
            html += `<p class="text-green-600 font-semibold">Checkliste ist abgeschlossen und gesperrt.</p>`;
            // Lock UI
            const inputs = wrapper.querySelectorAll('input, textarea, button:not(.btn-save):not(.btn-submit)');
            inputs.forEach(el => el.disabled = true);
        }
    }

    if (isBauleiter) {
        if (checklist.status === 'completed') {
            html += `
                <div class="flex flex-col gap-2 mt-4 p-4 bg-yellow-50 rounded">
                    <h4 class="font-bold">Bauleiter Freigabe</h4>
                    <textarea class="reject-comment w-full border rounded p-2" placeholder="Kommentar (Pflicht bei Ablehnung)"></textarea>
                    <div class="flex gap-2 mt-2">
                        <button class="btn-approve bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Freigeben (approved)</button>
                        <button class="btn-reject bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700">Ablehnen (rejected)</button>
                    </div>
                </div>
            `;
        } else {
            html += `<p class="text-sm text-gray-500">Warten auf Abschluss durch Monteur...</p>`;
        }
    }

    container.innerHTML = html;

    // Attach listeners
    const btnSave = container.querySelector('.btn-save');
    if (btnSave) btnSave.addEventListener('click', () => updateStatus('in work'));

    const btnSubmit = container.querySelector('.btn-submit');
    if (btnSubmit) btnSubmit.addEventListener('click', () => updateStatus('completed'));

    const btnApprove = container.querySelector('.btn-approve');
    if (btnApprove) btnApprove.addEventListener('click', () => updateStatus('approved'));

    const btnReject = container.querySelector('.btn-reject');
    if (btnReject) {
        btnReject.addEventListener('click', () => {
            const comment = container.querySelector('.reject-comment').value.trim();
            if (!comment) {
                alert('Ein Kommentar ist bei Ablehnung zwingend erforderlich!');
                return;
            }
            // Flip back to 'in work' to restore editing rights for Monteur
            updateStatus('in work', comment);
        });
    }
};
