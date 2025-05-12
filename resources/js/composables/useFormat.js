import { getCurrentInstance } from 'vue';

export function useFormat() {
  const instance = getCurrentInstance();
  const $t = instance?.proxy?.$t ?? ((key) => key);

  const formatNumber = (value) => {
    const numericValue = Number(value);
    if (isNaN(numericValue)) {
      console.warn('Valor no numérico:', value);
      return $t('na');
    }
    return numericValue.toLocaleString('es-ES', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
      useGrouping: true,
    });
  };

  const formatDate = (date) => {
    if (!date) return $t('actual');
    return new Date(date).toLocaleDateString('es-ES', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  };

  return { formatNumber, formatDate };
}