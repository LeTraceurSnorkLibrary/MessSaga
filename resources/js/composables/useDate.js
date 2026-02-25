export function useDate() {
    const formatDate = (dateString) => {
        const date = new Date(dateString);

        return new Intl.DateTimeFormat('ru-RU', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).format(date);
    };

    const formatDateShort = (dateString) => {
        const date = new Date(dateString);

        return new Intl.DateTimeFormat('ru-RU', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    };

    const formatTime = (dateString) => {
        const date = new Date(dateString);

        return new Intl.DateTimeFormat('ru-RU', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).format(date);
    };

    return {
        formatDate,
        formatDateShort,
        formatTime
    };
}
