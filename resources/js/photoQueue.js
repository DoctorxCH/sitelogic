import { saveToDB, getFromDB, deleteFromDB } from './db';

export const queuePhoto = async (photoFile, checklistItemId) => {
    const reader = new FileReader();

    return new Promise((resolve, reject) => {
        reader.onloadend = async () => {
            const base64Data = reader.result;
            const photoRecord = {
                checklist_item_id: checklistItemId,
                file_data: base64Data,
                file_name: photoFile.name,
                mime_type: photoFile.type,
                timestamp: new Date().toISOString()
            };

            try {
                await saveToDB('photo_queue', photoRecord);
                resolve();
            } catch (error) {
                reject(error);
            }
        };
        reader.onerror = reject;
        reader.readAsDataURL(photoFile);
    });
};

export const processPhotoQueue = async () => {
    if (!navigator.onLine) return;

    try {
        const queuedPhotos = await getFromDB('photo_queue');
        if (!queuedPhotos || queuedPhotos.length === 0) return;

        for (const photo of queuedPhotos) {
            // Upload to backend API
            const response = await fetch('/api/photos/upload', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Assuming CSRF or Bearer token is handled elsewhere
                },
                body: JSON.stringify(photo)
            });

            if (response.ok) {
                // Remove from local queue if successful
                await deleteFromDB('photo_queue', photo.id);
            } else {
                console.error(`Failed to upload photo ID: ${photo.id}`);
            }
        }
    } catch (error) {
        console.error('Error processing photo queue:', error);
    }
};
