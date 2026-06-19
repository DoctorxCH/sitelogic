import { getFromDB } from '../db';
import { renderChecklist } from './Checklist';

export const renderJobDashboard = async (containerId) => {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
        const jobs = await getFromDB('jobs');

        if (jobs.length === 0) {
            container.innerHTML = '<p class="text-gray-500">No jobs available offline.</p>';
            return;
        }

        // For simplicity, render the first available job
        const job = jobs[0];
        // Ensure checklists exist (from API nested relation)
        const jobChecklists = job.checklists || [];

        const escapeHtml = (unsafe) => {
            if (!unsafe) return 'N/A';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        let html = `
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Job Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div><span class="font-semibold">PID:</span> ${escapeHtml(job.pid)}</div>
                    <div><span class="font-semibold">Address:</span> ${escapeHtml(job.adresse)}</div>
                    <div><span class="font-semibold">Project Type:</span> ${escapeHtml(job.projekt_typ)}</div>
                    <div><span class="font-semibold">Bauleiter:</span> ${escapeHtml(job.bauleiter)}</div>
                    <div><span class="font-semibold">Technology:</span> ${escapeHtml(job.technologie)}</div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4">Checklisten</h3>
                <div id="checklists-container" class="space-y-4"></div>
            </div>
        `;

        container.innerHTML = html;

        const checklistsContainer = document.getElementById('checklists-container');
        jobChecklists.forEach(checklist => {
            renderChecklist(checklist, checklistsContainer);
        });

    } catch (error) {
        console.error('Error rendering dashboard:', error);
        container.innerHTML = '<p class="text-red-500">Failed to load dashboard data.</p>';
    }
};
