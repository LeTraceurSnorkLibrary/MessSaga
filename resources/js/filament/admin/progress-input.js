import prettyBytes from 'pretty-bytes';

const initProgressInput = (root) => {
    if (root.dataset.progressInputInitialized === '1') {
        return;
    }

    const rangeInput = root.querySelector('[data-progress-input-range]');
    const numberInput = root.querySelector('[data-progress-input-number]');
    if (!(rangeInput instanceof HTMLInputElement) || !(numberInput instanceof HTMLInputElement)) {
        return;
    }

    const convertedNode = root.querySelector('[data-progress-input-converted]');
    const converter = root.dataset.progressInputConverter ?? '';

    const sync = (source) => {
        const rawValue = Number(source.value);
        const minValue = Number(source.min || numberInput.min || 0);
        const maxValue = Number(source.max || numberInput.max || rawValue);

        let normalizedValue = Number.isNaN(rawValue)
            ? minValue
            : rawValue;
        normalizedValue = Math.max(minValue, Math.min(maxValue, normalizedValue));

        const normalizedString = String(normalizedValue);
        rangeInput.value = normalizedString;
        numberInput.value = normalizedString;

        if (convertedNode instanceof HTMLElement) {
            switch (converter) {
                case 'kbytes':
                    normalizedValue *= 1024;
                    break;

                case 'mbytes':
                    normalizedValue *= 1024 * 1024;
                    break;

                case 'gbytes':
                    normalizedValue *= 1024 * 1024 * 1024;
                    break;
            }
            const converted = prettyBytes(normalizedValue, {
                binary: true,
            });
            convertedNode.textContent = converted ?? '';
        }
    };

    rangeInput.addEventListener('input', () => sync(rangeInput));
    numberInput.addEventListener('input', () => sync(numberInput));
    numberInput.addEventListener('blur', () => sync(numberInput));

    sync(numberInput.value !== '' ? numberInput : rangeInput);
    root.dataset.progressInputInitialized = '1';
};

const initProgressInputs = () => {
    document.querySelectorAll('[data-progress-input]').forEach((node) => {
        if (node instanceof HTMLElement) {
            initProgressInput(node);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProgressInputs);
} else {
    initProgressInputs();
}

document.addEventListener('livewire:navigated', initProgressInputs);
