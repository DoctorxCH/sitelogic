import { saveToDB } from './db';

import { getFromDB, deleteFromDB } from './db';

export const logAction = async (actionType, entityId, details) => {
    // Map to the backend schema fields: wer, wann, punkt_deaktiviert, status_geaendert
    const logEntry = {
        wer: 'current-user-or-monteur',
        wann: new Date().toISOString(),
        punkt_deaktiviert: actionType === 'TOGGLE_ITEM' || actionType === 'TOGGLE_MAIN_SWITCH' ? String(details) : null,
        status_geaendert: actionType === 'STATUS_CHANGE' ? String(details) : null,
        action_type_local: actionType,
        details: typeof details === 'string' ? details : JSON.stringify(details),
        entity_id_local: entityId
    };

    try {
        await saveToDB('audit_logs', logEntry);
        console.log(`Audit logged: ${actionType}`);
    } catch (e) {
        console.error('Failed to write audit log', e);
    }
};

export const processAuditQueue = async () => {
    if (!navigator.onLine) return;

    try {
        const queuedLogs = await getFromDB('audit_logs');
        if (!queuedLogs || queuedLogs.length === 0) return;

        for (const log of queuedLogs) {
            const response = await fetch('/api/audit-logs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(log)
            });

            if (response.ok) {
                await deleteFromDB('audit_logs', log.id);
            } else {
                console.error(`Failed to upload audit log ID: ${log.id}`);
            }
        }
    } catch (error) {
        console.error('Error processing audit queue:', error);
    }
};
