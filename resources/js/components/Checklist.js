import { saveToDB } from '../db';
import { logAction } from '../auditLog';
import { renderWorkflowControls } from './WorkflowControls';
import { renderPhotoUpload } from './PhotoUpload';

export const renderChecklist = async (checklist, container) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'bg-white p-4 rounded-lg shadow border mb-4';

    // The items are passed directly in the nested relation from API
    let items = checklist.items || [];

    // Initial Progress Calculation
    const calculateProgress = () => {
        if (!checklist.hauptschalter) return 0;
        const activeItems = items.filter(i => !i.kriterien_ausgeschaltet);
        if (activeItems.length === 0) return 100;

        // Mocking completion status on item for calculation
        const completedItems = activeItems.filter(i => i.completed);
        return Math.round((completedItems.length / activeItems.length) * 100);
    };

    const updateUI = () => {
        const progress = calculateProgress();
        const progressBar = wrapper.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
            progressBar.innerText = `${progress}%`;
        }

        const itemsContainer = wrapper.querySelector('.items-container');
        if (itemsContainer) {
            itemsContainer.style.display = checklist.hauptschalter ? 'block' : 'none';
        }
    };

    const toggleMainSwitch = async (e) => {
        checklist.hauptschalter = e.target.checked;
        await saveToDB('checklists', checklist);
        await logAction('TOGGLE_MAIN_SWITCH', checklist.id, `Set to ${checklist.hauptschalter}`);
        updateUI();
    };

    const toggleItemSwitch = async (item, checked) => {
        item.kriterien_ausgeschaltet = !checked;
        await saveToDB('checklist_items', item);
        await logAction('TOGGLE_ITEM', item.id, `Set active to ${checked}`);
        updateUI();
    };

    let itemsHtml = items.map(item => `
        <div class="border-t pt-2 mt-2 flex flex-col md:flex-row justify-between items-start md:items-center" id="item-${item.id}">
            <div class="flex-1 pr-4">
                <span class="font-medium text-gray-800">${item.question || `Item ${item.id}`}</span>
                <div class="media-container mt-2"></div>
            </div>
            <div class="mt-2 md:mt-0">
                <label class="inline-flex items-center cursor-pointer">
                  <span class="mr-2 text-sm text-gray-600">Relevanz</span>
                  <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 item-toggle" data-item-id="${item.id}" ${!item.kriterien_ausgeschaltet ? 'checked' : ''}>
                </label>
            </div>
        </div>
    `).join('');

    wrapper.innerHTML = `
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h4 class="font-bold text-lg text-blue-800">${checklist.name || 'Checkliste ' + checklist.id}</h4>
            <div class="flex items-center space-x-4">
                <span class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded font-semibold status-badge">${checklist.status || 'open'}</span>
                <label class="inline-flex items-center cursor-pointer">
                  <span class="mr-2 font-semibold text-gray-700">Aktivieren</span>
                  <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600 main-toggle" ${checklist.hauptschalter ? 'checked' : ''}>
                </label>
            </div>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-4 mb-4 dark:bg-gray-700">
          <div class="bg-blue-600 h-4 rounded-full text-xs font-medium text-blue-100 text-center p-0.5 leading-none progress-bar transition-all duration-500" style="width: ${calculateProgress()}%"> ${calculateProgress()}%</div>
        </div>

        <div class="items-container space-y-4">
            ${itemsHtml.length > 0 ? itemsHtml : '<p class="text-sm text-gray-500">No items available.</p>'}
        </div>

        <div class="workflow-controls mt-6 pt-4 border-t border-gray-200"></div>
    `;

    container.appendChild(wrapper);

    // Event Listeners
    const mainToggle = wrapper.querySelector('.main-toggle');
    if (mainToggle) mainToggle.addEventListener('change', toggleMainSwitch);

    wrapper.querySelectorAll('.item-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            const itemId = parseInt(e.target.getAttribute('data-item-id'));
            const item = items.find(i => i.id === itemId);
            if (item) toggleItemSwitch(item, e.target.checked);
        });
    });

    // Init Photo Uploads
    items.forEach(item => {
        const itemContainer = wrapper.querySelector(`#item-${item.id} .media-container`);
        if (itemContainer) {
            renderPhotoUpload(item, itemContainer, updateUI);
        }
    });

    updateUI();

    // Init Workflow Controls
    const workflowContainer = wrapper.querySelector('.workflow-controls');
    renderWorkflowControls(checklist, workflowContainer, wrapper);
};
