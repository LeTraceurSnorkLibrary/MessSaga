import {computed} from 'vue';

export const useCapitalizeFirstLetter = () => {
    function capitalizeFirstLetter(text: string) {
        return computed(() => {
            if (!text) {
                return '';
            }
            return text.charAt(0).toUpperCase() + text.slice(1);
        });
    }

    return {
        capitalizeFirstLetter,
    };
};
