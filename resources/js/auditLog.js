import { saveToDB, getFromDB, deleteFromDB } from './db';

export const logAction = async (action, entityId, details) => {
    const logEntry = {
        action: action,
        entity_id: entityId,
        details: details,
        timestamp: new Date().toISOString()
    };

    try {
        await saveToDB('audit_logs', logEntry);
    } catch (e) {
        console.error("Failed to save audit log locally", e);
    }
};

export const processAuditQueue = async () => {
    if (!navigator.onLine) return;

    // In a real app, you would post these to an API endpoint
    try {
        const logs = await getFromDB('audit_logs');
        if (!logs || logs.length === 0) return;

        // This is a stub for the actual API call
        // const response = await fetch('/api/audit-logs', { ... });

        // For now just clear them to simulate success
        for (const log of logs) {
            await deleteFromDB('audit_logs', log.id);
        }
    } catch (error) {
        console.error('Error processing audit queue:', error);
    }
};
