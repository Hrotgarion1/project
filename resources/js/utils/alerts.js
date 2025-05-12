import Swal from 'sweetalert2';

export default {
  // Confirmación para eliminar algo (genérico)
  confirmDelete({ t, title = 'Are you sure?', text = 'This action cannot be undone.', confirmText = 'Yes, delete', cancelText = 'Cancel' } = {}) {
    return Swal.fire({
      title: t(title),
      text: t(text),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t(confirmText),
      cancelButtonText: t(cancelText),
    });
  },

  // Confirmación específica para eliminar documentos
  confirmDeleteDocument(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Delete this document?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, delete'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación específica para eliminar identidades
  confirmDeleteIdentity(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Delete this identity?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, delete'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Alerta de éxito genérica
  success(t, message) {
    return Swal.fire({
      title: t('Success'),
      text: t(message),
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
    });
  },

  // Alerta de error genérica
  error(t, message) {
    return Swal.fire({
      title: t('Error'),
      text: t(message),
      icon: 'error',
      confirmButtonColor: '#d33',
    });
  },

  // Confirmación para enviar una solicitud
  confirmSubmit(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to submit this identity request?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, submit'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para cancelar una solicitud
  confirmCancel(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to cancel this request?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, cancel'),
      cancelButtonText: t('No, continue'),
    });
  },

  // Confirmación para restaurar
  confirmRestore(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to restore this identity?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, restore'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para actualizar estado
  confirmUpdateStatus(t, status) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t(`Do you want to ${status} this identity?`),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: status === 'approved' ? '#3085d6' : '#d33',
      cancelButtonColor: '#d33',
      confirmButtonText: t(`Yes, ${status}`),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para suspender
  confirmSuspend(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to suspend this identity?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f59e0b',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, suspend'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para eliminación permanente
  confirmDeletePermanent(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to delete this identity? This action cannot be undone.'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, delete'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para reactivar
  confirmReactivate(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to reactivate this identity?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, reactivate'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para reasignar
  confirmReassign(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to reassign this identity?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#8b5cf6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, reassign'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para solicitar más documentos
  confirmRequestMoreDocs(t) {
    return Swal.fire({
      icon: 'question',
      title: t('Confirm Request'),
      text: t('Are you sure you want to request more documents for this identity?'),
      showCancelButton: true,
      confirmButtonText: t('Yes'),
      cancelButtonText: t('No'),
    });
  },

  // Confirmación para actualizar
  confirmUpdate(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to update this identity?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, update'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Nueva confirmación para crear un tipo de identidad
  confirmCreate(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to create this new identity type?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, create'),
      cancelButtonText: t('Cancel'),
    });
  },

  confirmCreateReason(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to create this new reason?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, create'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Nueva confirmación para añadir un rol a un usuario
  confirmAddRole(t, username, role) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t(`Do you want to add the role "${role}" to the user "${username}"?`),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, add role'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Nueva confirmación para quitar un rol a un usuario
  confirmRemoveRole(t, username, role) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t(`Do you want to remove the role "${role}" from the user "${username}"?`),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, remove role'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Confirmación para enviar una invitación
  confirmSendInvitation(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to send this invitation?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, send'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Nueva confirmación para cambiar el estado de una regla (toggle allowed)
  confirmToggleRule(t, functionName, enable) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t(enable ? 'Do you want to enable this function?' : 'Do you want to disable this function?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: enable ? '#10b981' : '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t(enable ? 'Yes, enable' : 'Yes, disable'),
      cancelButtonText: t('Cancel'),
    });
  },

  // Nueva confirmación para eliminar una regla
  confirmDeleteRule(t, functionName) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to delete this function?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: t('Yes, delete'),
      cancelButtonText: t('Cancel'),
    });
  },

  confirmCreateUser(t) {
    return Swal.fire({
      title: t('Are you sure?'),
      text: t('Do you want to create this user?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('Yes, create'),
      cancelButtonText: t('Cancel'),
    });
  },

  confirmAction({ t, title, text, confirmButtonText, cancelButtonText } = {}) {
    return Swal.fire({
      title: title || t('Are you sure?'),
      text: text || t('Do you want to perform this action?'),
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: confirmButtonText || t('Yes'),
      cancelButtonText: cancelButtonText || t('Cancel'),
    });
  },

    success(t, message) {
        Swal.fire({
            title: t('Success'),
            text: message,
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    },

    error(t, message) {
        Swal.fire({
            title: t('Error'),
            text: message,
            icon: 'error',
            confirmButtonColor: '#d33',
        });
    },
};