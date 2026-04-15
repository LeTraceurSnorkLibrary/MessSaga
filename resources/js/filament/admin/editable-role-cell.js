const registerEditableRoleCell = () => {
    if (!window.Alpine || window.__editableRoleCellRegistered) {
        return;
    }

    window.__editableRoleCellRegistered = true;

    window.Alpine.data('editableRoleCell', ({ userId }) => ({
        tip: null,
        userId,
        init(triggerElement, menuElement) {
            if (!window.tippy || !triggerElement || !menuElement) {
                return;
            }

            if (!menuElement.dataset.roleCellBound) {
                menuElement.addEventListener('click', (event) => {
                    const roleButton = event.target.closest('[data-role]');
                    if (!roleButton) {
                        return;
                    }

                    this.$wire.setEditingUserFieldValue(this.userId, 'role', roleButton.dataset.role);

                    if (this.tip) {
                        this.tip.hide();
                    }
                });

                menuElement.dataset.roleCellBound = 'true';
            }

            if (this.tip) {
                this.tip.destroy();
            }

            this.tip = window.tippy(triggerElement, {
                content: menuElement,
                allowHTML: true,
                interactive: true,
                trigger: 'click',
                placement: 'bottom-start',
                appendTo: () => document.body,
                theme: 'light-border',
                maxWidth: 280,
                onShow: () => {
                    menuElement.style.display = 'flex';
                },
                onHide: () => {
                    menuElement.style.display = 'none';
                },
            });
        },
    }));
};

if (window.Alpine) {
    registerEditableRoleCell();
} else {
    document.addEventListener('alpine:init', registerEditableRoleCell);
}
