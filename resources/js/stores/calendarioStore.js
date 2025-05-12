import { defineStore } from 'pinia';

export const useCalendarioStore = defineStore('calendario', {
    state: () => ({
        festivos: [],
    }),
    actions: {
        setFestivos(festivos) {
            this.festivos = festivos;
        },
        calcularHoras(initDate, endDate, schedule, currently, overtime = 0) {
            if (!initDate) {
                return 0;
            }

            const start = new Date(initDate);
            const end = currently === 'yes' ? new Date() : endDate ? new Date(endDate) : null;

            if (!end || end < start) {
                return 0;
            }

            let currentDate = new Date(start);
            let workingDays = 0;

            while (currentDate <= end) {
                const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                const isFestivo = this.festivos.includes(
                    currentDate.toISOString().split('T')[0]
                );
                if (!isWeekend && !isFestivo) {
                    workingDays++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            const hoursPerDay = Number(schedule) + Number(overtime);
            const totalHours = workingDays * hoursPerDay;

            console.debug('Calculando horas:', {
                initDate,
                endDate: end ? end.toISOString().split('T')[0] : null,
                schedule,
                overtime,
                currently,
                workingDays,
                hoursPerDay,
                totalHours,
            });

            return totalHours;
        },
    },
});