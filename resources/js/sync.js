import { saveToDB } from './db';
import { processPhotoQueue } from './photoQueue';

const syncJobs = async () => {
    try {
        const response = await fetch('/api/jobs');
        if (!response.ok) throw new Error('Network response was not ok');
        const jobs = await response.json();

        // Assume API returns { data: [...] } or an array directly
        const jobsData = Array.isArray(jobs) ? jobs : (jobs.data || []);

        await saveToDB('jobs', jobsData);
        console.log('Jobs synced to IndexedDB');
    } catch (error) {
        console.error('Failed to sync jobs:', error);
    }
};

const syncChecklists = async () => {
    try {
        const response = await fetch('/api/checklists');
        if (!response.ok) throw new Error('Network response was not ok');
        const checklists = await response.json();

        const checklistsData = Array.isArray(checklists) ? checklists : (checklists.data || []);

        await saveToDB('checklists', checklistsData);
        console.log('Checklists synced to IndexedDB');
    } catch (error) {
        console.error('Failed to sync checklists:', error);
    }
};

const handleOnline = () => {
    console.log('Connection restored. Starting sync...');
    syncJobs();
    syncChecklists();
    processPhotoQueue();
};

const handleOffline = () => {
    console.log('Connection lost. Working offline...');
};

export const initSync = () => {
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    // Initial sync if online
    if (navigator.onLine) {
        syncJobs();
        syncChecklists();
    }
};
