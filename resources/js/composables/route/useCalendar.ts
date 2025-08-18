import { ref, computed } from 'vue';

export function useCalendar() {
    const currentCalendarDate = ref(new Date());

    // Generate calendar days
    const calendarDays = computed(() => {
        const year = currentCalendarDate.value.getFullYear();
        const month = currentCalendarDate.value.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const days = [];
        // Previous month trailing days
        const prevMonth = new Date(year, month - 1, 0);
        for (let i = firstDayOfWeek - 1; i >= 0; i--) {
            const date = prevMonth.getDate() - i;
            const fullDate = `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            days.push({
                date,
                fullDate,
                isCurrentMonth: false,
                isToday: false,
                isSelected: false,
                key: `prev-${date}`
            });
        }
        // Current month
        for (let date = 1; date <= daysInMonth; date++) {
            const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            days.push({
                date,
                fullDate,
                isCurrentMonth: true,
                isToday: fullDate === todayStr,
                isSelected: false, // This will be set by parent
                key: `current-${date}`
            });
        }
        // Next month leading days
        const remainingDays = 42 - days.length;
        for (let date = 1; date <= remainingDays; date++) {
            const fullDate = `${year}-${String(month + 2).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            days.push({
                date,
                fullDate,
                isCurrentMonth: false,
                isToday: false,
                isSelected: false,
                key: `next-${date}`
            });
        }
        return days;
    });

    // Quick date selection
    const getQuickDate = (type: string) => {
        const today = new Date();
        let targetDate = new Date();
        switch (type) {
            case 'today':
                targetDate = today;
                break;
            case 'yesterday':
                targetDate = new Date(today);
                targetDate.setDate(today.getDate() - 1);
                break;
            case 'week':
                targetDate = new Date(today);
                targetDate.setDate(today.getDate() - 7);
                break;
        }
        const dateStr = `${targetDate.getFullYear()}-${String(targetDate.getMonth() + 1).padStart(2, '0')}-${String(targetDate.getDate()).padStart(2, '0')}`;
        currentCalendarDate.value = targetDate;
        return dateStr;
    };

    return {
        currentCalendarDate,
        calendarDays,
        getQuickDate
    };
}