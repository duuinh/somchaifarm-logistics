import { ref } from 'vue';

interface CacheEntry {
    data: any;
    timestamp: number;
    deviceId: number;
    date: string;
}

class IndexedDBCache {
    private dbName = 'RouteHistoryCache';
    private dbVersion = 1;
    private storeName = 'routes';
    private db: IDBDatabase | null = null;

    async init(): Promise<void> {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve();
            };

            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;
                
                // Create object store with compound key
                const store = db.createObjectStore(this.storeName, { 
                    keyPath: ['deviceId', 'date'] 
                });
                
                // Create indexes for efficient querying
                store.createIndex('deviceId', 'deviceId', { unique: false });
                store.createIndex('date', 'date', { unique: false });
                store.createIndex('timestamp', 'timestamp', { unique: false });
            };
        });
    }

    async set(deviceId: number, date: string, data: any): Promise<void> {
        if (!this.db) throw new Error('Database not initialized');

        const cacheEntry: CacheEntry = {
            data,
            timestamp: Date.now(),
            deviceId,
            date
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.put(cacheEntry);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async get(deviceId: number, date: string): Promise<any> {
        if (!this.db) throw new Error('Database not initialized');

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const request = store.get([deviceId, date]);

            request.onsuccess = () => {
                const result = request.result as CacheEntry | undefined;
                
                if (result) {
                    const age = Date.now() - result.timestamp;
                    
                    // Check if this is today's date
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    
                    // Different expiration for today vs older dates
                    const maxAge = date === todayStr 
                        ? 24 * 60 * 60 * 1000  // 24 hours for today's data
                        : 30 * 24 * 60 * 60 * 1000;  // 30 days for older data
                    
                    if (age < maxAge) {
                        resolve(result.data);
                    } else {
                        // Entry is expired, remove it
                        this.delete(deviceId, date);
                        resolve(null);
                    }
                } else {
                    resolve(null);
                }
            };
            request.onerror = () => reject(request.error);
        });
    }

    async delete(deviceId: number, date: string): Promise<void> {
        if (!this.db) throw new Error('Database not initialized');

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.delete([deviceId, date]);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async clear(): Promise<void> {
        if (!this.db) throw new Error('Database not initialized');

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async getStats(): Promise<{
        count: number;
        estimatedSize: number;
        oldestEntry: Date | null;
        newestEntry: Date | null;
    }> {
        if (!this.db) throw new Error('Database not initialized');

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();

            request.onsuccess = () => {
                const entries = request.result as CacheEntry[];
                
                let estimatedSize = 0;
                let oldestTimestamp = Infinity;
                let newestTimestamp = 0;

                entries.forEach(entry => {
                    estimatedSize += JSON.stringify(entry).length;
                    oldestTimestamp = Math.min(oldestTimestamp, entry.timestamp);
                    newestTimestamp = Math.max(newestTimestamp, entry.timestamp);
                });

                resolve({
                    count: entries.length,
                    estimatedSize: Math.round(estimatedSize / 1024), // KB
                    oldestEntry: oldestTimestamp === Infinity ? null : new Date(oldestTimestamp),
                    newestEntry: newestTimestamp === 0 ? null : new Date(newestTimestamp)
                });
            };
            request.onerror = () => reject(request.error);
        });
    }

    async clearExpired(): Promise<number> {
        if (!this.db) throw new Error('Database not initialized');

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();
            
            request.onsuccess = async () => {
                const entries = request.result as CacheEntry[];
                let deletedCount = 0;
                
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                
                for (const entry of entries) {
                    const age = Date.now() - entry.timestamp;
                    
                    // Different expiration for today vs older dates
                    const maxAge = entry.date === todayStr 
                        ? 24 * 60 * 60 * 1000  // 24 hours for today's data
                        : 30 * 24 * 60 * 60 * 1000;  // 30 days for older data
                    
                    if (age > maxAge) {
                        await this.delete(entry.deviceId, entry.date);
                        deletedCount++;
                    }
                }
                
                resolve(deletedCount);
            };
            request.onerror = () => reject(request.error);
        });
    }
}

export const useIndexedDBCache = () => {
    const cache = new IndexedDBCache();
    const isInitialized = ref(false);
    const error = ref<string | null>(null);

    const init = async () => {
        try {
            await cache.init();
            isInitialized.value = true;
            error.value = null;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to initialize cache';
            console.error('IndexedDB cache initialization failed:', err);
        }
    };

    const getCachedData = async (deviceId: number, date: string) => {
        if (!isInitialized.value) return null;
        try {
            return await cache.get(deviceId, date);
        } catch (err) {
            console.error('Error getting cached data:', err);
            return null;
        }
    };

    const setCachedData = async (deviceId: number, date: string, data: any) => {
        if (!isInitialized.value) return;
        try {
            await cache.set(deviceId, date, data);
        } catch (err) {
            console.error('Error setting cached data:', err);
        }
    };

    const clearAllCache = async () => {
        if (!isInitialized.value) return;
        try {
            await cache.clear();
        } catch (err) {
            console.error('Error clearing cache:', err);
        }
    };

    const getCacheStats = async () => {
        if (!isInitialized.value) return { count: 0, estimatedSize: 0, oldestEntry: null, newestEntry: null };
        try {
            return await cache.getStats();
        } catch (err) {
            console.error('Error getting cache stats:', err);
            return { count: 0, estimatedSize: 0, oldestEntry: null, newestEntry: null };
        }
    };

    const clearExpiredCache = async () => {
        if (!isInitialized.value) return 0;
        try {
            return await cache.clearExpired();
        } catch (err) {
            console.error('Error clearing expired cache:', err);
            return 0;
        }
    };

    return {
        init,
        isInitialized,
        error,
        getCachedData,
        setCachedData,
        clearAllCache,
        getCacheStats,
        clearExpiredCache
    };
};