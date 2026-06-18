import { queuePhoto } from '../photoQueue';
import { logAction } from '../auditLog';
import { saveToDB } from '../db';

export const renderPhotoUpload = (item, container, onCompleteCallback) => {
    container.innerHTML = `
        <div class="mt-3 bg-gray-50 p-3 rounded">
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700">Add Photo</label>
                <input type="file" accept="image/*" multiple class="photo-input mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea class="item-notes mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm" rows="2"></textarea>
            </div>
            <button class="save-item-btn bg-gray-800 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">Save Item</button>
            <div class="preview-area mt-2 flex gap-2 flex-wrap"></div>
        </div>
    `;

    const fileInput = container.querySelector('.photo-input');
    const notesInput = container.querySelector('.item-notes');

    // Safely inject text to prevent XSS
    if (item.notes) {
        notesInput.value = item.notes;
    }

    const saveBtn = container.querySelector('.save-item-btn');
    const previewArea = container.querySelector('.preview-area');

    fileInput.addEventListener('change', async (e) => {
        const files = Array.from(e.target.files);
        for (const file of files) {
            try {
                await queuePhoto(file, item.id);
                await logAction('PHOTO_QUEUED', item.id, `Queued ${file.name}`);

                // Add tiny preview
                const url = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = url;
                img.className = 'h-12 w-12 object-cover rounded shadow';
                previewArea.appendChild(img);
            } catch (err) {
                console.error('Failed to queue photo', err);
            }
        }
    });

    saveBtn.addEventListener('click', async () => {
        item.notes = notesInput.value;
        item.completed = true; // Mark as done for progress calc
        await saveToDB('checklist_items', item);
        await logAction('ITEM_SAVED', item.id, 'Notes updated and marked complete');

        saveBtn.innerText = 'Saved!';
        saveBtn.classList.add('bg-green-600');
        setTimeout(() => {
            saveBtn.innerText = 'Save Item';
            saveBtn.classList.remove('bg-green-600');
        }, 2000);

        if (onCompleteCallback) onCompleteCallback();
    });
};
