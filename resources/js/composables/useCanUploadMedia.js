import {computed} from 'vue';

/**
 * @param {import('vue').Ref<object|null>|import('vue').ComputedRef<object|null>} quotaRef
 */
export function useCanUploadMedia(quotaRef) {
    return computed(() => {
        const quota = quotaRef.value;
        if (!quota) {
            return true;
        }

        const storageRemaining = Number(quota.storage?.remaining ?? 0);
        const filesRemaining = Number(quota.files?.remaining ?? 0);

        return storageRemaining > 0 && filesRemaining > 0;
    });
}
