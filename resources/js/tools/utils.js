export function formatedValue(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

export function formatDate(dateString) {
  // Formatear la fecha eliminando las horas, minutos y segundos
  const date = new Date(dateString);
  return date.toLocaleDateString();
}
