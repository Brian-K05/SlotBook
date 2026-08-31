document.addEventListener('alpine:init', () => {
    Alpine.data('slotbook', (payload) => ({
        days: payload.days,
        selectedDate: payload.selectedDate,
        selectedSlotId: payload.selectedSlotId,

        get selectedDay() {
            return this.days.find((day) => day.date === this.selectedDate) || null;
        },

        get selectedSlot() {
            if (!this.selectedDay || !this.selectedSlotId) {
                return null;
            }

            return this.selectedDay.slots.find((slot) => slot.id === this.selectedSlotId) || null;
        },

        selectDayByDate(date) {
            const day = this.days.find((item) => item.date === date);

            if (!day || !day.slotCount) {
                return;
            }

            this.selectedDate = day.date;
            this.selectedSlotId = null;
            this.revealLedger();
        },

        selectSlot(slot) {
            if (!slot.open) {
                return;
            }

            this.selectedSlotId = slot.id;
            this.$nextTick(() => this.$refs.nameField?.focus());
        },

        closeDay() {
            this.selectedDate = null;
            this.selectedSlotId = null;
        },

        revealLedger() {
            this.$nextTick(() => {
                const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                this.$refs.ledger?.scrollIntoView({
                    behavior: reduce ? 'auto' : 'smooth',
                    block: 'nearest',
                });
            });
        },

        formatDate(iso) {
            const date = new Date(iso + 'T00:00:00');

            return date.toLocaleDateString('en-PH', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
            });
        },
    }));
});
