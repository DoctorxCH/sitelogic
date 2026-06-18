export const openDB = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('SiteLogicDB', 1);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('jobs')) {
                db.createObjectStore('jobs', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('job_assets')) {
                db.createObjectStore('job_assets', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('checklists')) {
                db.createObjectStore('checklists', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('checklist_items')) {
                db.createObjectStore('checklist_items', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('photo_queue')) {
                db.createObjectStore('photo_queue', { keyPath: 'id', autoIncrement: true });
            }
        };

        request.onsuccess = (event) => {
            resolve(event.target.result);
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
};

export const saveToDB = async (storeName, data) => {
    const db = await openDB();
    const tx = db.transaction(storeName, 'readwrite');
    const store = tx.objectStore(storeName);

    if (Array.isArray(data)) {
        data.forEach(item => store.put(item));
    } else {
        store.put(data);
    }

    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
};

export const getFromDB = async (storeName) => {
    const db = await openDB();
    const tx = db.transaction(storeName, 'readonly');
    const store = tx.objectStore(storeName);
    const request = store.getAll();

    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
};

export const deleteFromDB = async (storeName, id) => {
    const db = await openDB();
    const tx = db.transaction(storeName, 'readwrite');
    const store = tx.objectStore(storeName);
    const request = store.delete(id);

    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
};
